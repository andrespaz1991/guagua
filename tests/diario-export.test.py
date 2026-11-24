import hashlib
import importlib.util
import io
from pathlib import Path
import unittest
from zipfile import ZipFile
from lxml import etree

spec = importlib.util.spec_from_file_location('diario_export', Path(__file__).parents[1] / 'apps/diario/export_docx.py')
export = importlib.util.module_from_spec(spec)
spec.loader.exec_module(export)


class DiaryExportTest(unittest.TestCase):
    def test_month_from_spanish_entry_not_narrative(self):
        text = '## JUEVES, 15 DE OCTUBRE DE 2026\n\n### Actividades\nRecordé las notas de septiembre de 2025.'
        self.assertEqual(export.diary_period(text), ('Octubre de 2026', 'Diario_octubre_2026.docx'))

    def test_iso_dates(self):
        self.assertEqual(export.diary_period('## 2025-08-04\n\n## 2025-08-29'), ('Agosto de 2025', 'Diario_agosto_2025.docx'))

    def test_month_range_uses_chronological_order(self):
        self.assertEqual(export.diary_period('## 2026-11-02\n\n## 2026-10-29'), ('Octubre a noviembre de 2026', 'Diario_octubre_a_noviembre_2026.docx'))

    def test_year_range(self):
        self.assertEqual(export.diary_period('## 2027-01-04\n\n## 2026-12-18'), ('Diciembre de 2026 a enero de 2027', 'Diario_diciembre_2026_a_enero_2027.docx'))

    def test_unconfirmed_dates_do_not_use_current_month(self):
        for text in ['## FECHA POR CONFIRMAR', 'Relato del 15 de octubre de 2026.', '## 31 de febrero de 2026', '### Recuerdo del 15 de octubre de 2026']:
            self.assertEqual(export.diary_period(text), ('Periodo por confirmar', 'Diario_periodo_por_confirmar.docx'))

    def test_docx_cover_filename_and_retained_template(self):
        original_hash = hashlib.sha256(export.TEMPLATE.read_bytes()).hexdigest()
        data, filename = export.build_docx('## JUEVES, 15 DE OCTUBRE DE 2026\n\n### Actividades\n\nAcompañé el trabajo de los estudiantes.\n\n**Reflexión del día:** La participación favorece el aprendizaje.')
        self.assertEqual(filename, 'Diario_octubre_2026.docx')
        with ZipFile(export.TEMPLATE) as source, ZipFile(io.BytesIO(data)) as result:
            self.assertEqual(source.namelist(), result.namelist())
            for part in source.namelist():
                if part != 'word/document.xml':
                    self.assertEqual(source.read(part), result.read(part), part)
            xml = etree.fromstring(result.read('word/document.xml'))
            paragraphs = [''.join(p.itertext()) for p in xml.findall('w:body/w:p', export.NS)]
            self.assertEqual(paragraphs[3], 'Octubre de 2026')
            self.assertIn('JUEVES, 15 DE OCTUBRE DE 2026', paragraphs)
        self.assertEqual(hashlib.sha256(export.TEMPLATE.read_bytes()).hexdigest(), original_hash)


if __name__ == '__main__':
    unittest.main()
