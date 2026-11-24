"""Pruebas de extracción con documentos sintéticos, sin información personal."""
import importlib.util
import json
import unittest
from pathlib import Path
import fitz

ROOT = Path(__file__).resolve().parents[1]
spec = importlib.util.spec_from_file_location('finance_server', ROOT/'server.py')
server = importlib.util.module_from_spec(spec)
spec.loader.exec_module(server)
FIXTURES = ROOT/'tests'/'fixtures'


def fixture(name, lines):
    FIXTURES.mkdir(exist_ok=True)
    doc = fitz.open(); page = doc.new_page()
    for i, line in enumerate(lines):
        page.insert_text((40, 45+i*22), line, fontsize=10)
    path = FIXTURES/name
    doc.save(path); doc.close()
    return path


def create_fixtures():
    fixture('extracto-demo.pdf', [
        'HORIZONTE - EXTRACTO SINTETICO DE PRUEBA - NO ES UN BANCO',
        'Periodo: noviembre 2026',
        'Saldo inicial: $ 1.000.000,00',
        '01/11/2026 Abono nomina 5.500.000,00',
        '02/11/2026 Pago arriendo -1.800.000,00',
        '03/11/2026 Compra supermercado -450.000,00',
        '04/11/2026 Pago cuota tarjeta -300.000,00',
        '05/11/2026 Transferencia a mi otra cuenta -200.000,00',
        '06/11/2026 Aporte CDT -400.000,00',
        'Saldo final: $ 3.350.000,00',
    ])
    fixture('otro-extracto-demo.pdf', [
        'HORIZONTE - SEGUNDO DOCUMENTO SINTETICO',
        '07/11/2026 Abono freelance 500.000,00',
        '08/11/2026 Pago gasolina -100.000,00',
        'Saldo final: $ 3.750.000,00',
    ])
    fixture('ambiguo-demo.pdf', [
        'EXTRACTO SINTETICO CON COLUMNAS',
        '01/11/2026 Compra supermercado 450.000,00 550.000,00',
        '01/10/2026 Pago arriendo -800.000,00',
    ])
    doc=fitz.open(); doc.new_page(); doc.save(FIXTURES/'sin-texto-demo.pdf'); doc.close()
    (FIXTURES/'no-es-pdf.pdf').write_bytes(b'Esto no es un PDF')


class ParserTest(unittest.TestCase):
    @classmethod
    def setUpClass(cls): create_fixtures()

    def test_money_locales(self):
        for raw, expected in [('1.250.000,50',1250000.5),('1,250,000.50',1250000.5),('-400.000',-400000),('1234.50',1234.5),('1234,50',1234.5)]:
            self.assertEqual(server.money(raw), expected)

    def test_extract_balances_and_classification(self):
        result=server.analyze(FIXTURES/'extracto-demo.pdf','2026-11')
        self.assertEqual(result['opening'],1000000)
        self.assertEqual(result['closing'],3350000)
        self.assertEqual(len(result['rows']),6)
        self.assertEqual(result['rows'][0]['kind'],'ingreso')
        self.assertEqual(result['rows'][0]['category'],'salario')
        self.assertEqual(result['rows'][1]['amount'],1800000)
        self.assertEqual(result['rows'][2]['category'],'alimentacion')
        self.assertEqual(result['rows'][4]['kind'],'pendiente')
        self.assertTrue(all(r['include'] for r in result['rows']))

    def test_ambiguous_columns_require_review(self):
        result=server.analyze(FIXTURES/'ambiguo-demo.pdf','2026-11')
        self.assertEqual(result['rows'][0]['kind'],'pendiente')
        self.assertFalse(result['rows'][1]['include'])
        self.assertGreaterEqual(len(result['warnings']),2)

    def test_no_text_not_fabricated(self):
        result=server.analyze(FIXTURES/'sin-texto-demo.pdf','2026-11')
        self.assertEqual(result['rows'],[])
        self.assertTrue(any('OCR' in w for w in result['warnings']))

    def test_reanalysis_ids_stable(self):
        a=server.analyze(FIXTURES/'extracto-demo.pdf','2026-11')
        b=server.analyze(FIXTURES/'extracto-demo.pdf','2026-11')
        self.assertEqual([r['id'] for r in a['rows']],[r['id'] for r in b['rows']])


if __name__=='__main__': unittest.main(verbosity=2)
