"""Inventario estático reproducible. No decide eliminaciones ni ejecuta módulos."""
from pathlib import Path
import csv
import json
import re
import subprocess

ROOT = Path(__file__).resolve().parents[1]
EXCLUDED = ('comun/lib/', 'comun/vendor/', 'Planeador/PHPWord-master/', 'tmp/', 'output/', 'tests/', 'sga-data/')
THIRD_PARTY_NAMES = {'tbs_class.php','html2canvas.js','quill.js','docxtemplater.js','pizzip.js'}
EXTENSIONS = {'.php', '.js', '.html', '.py', '.ps1', '.bat'}

def inventory(metadata):
    names = subprocess.run(['rg', '--files'], cwd=ROOT, capture_output=True, text=True, check=True).stdout.splitlines()
    files = {}
    for name in names:
        path = name.replace('\\', '/')
        if path.startswith(EXCLUDED) or '/tests/' in path or path.startswith('miplaneador/plugins/') or Path(path).suffix.lower() not in EXTENSIONS: continue
        if Path(path).name.lower() in THIRD_PARTY_NAMES: continue
        if re.search(r'(?:\.min\.|jquery|bootstrap|tailwind|sweetalert|xlsx|sheetjs|highcharts|plotly)', Path(path).name, re.I): continue
        files[path] = (ROOT/path).read_text(encoding='utf-8-sig', errors='replace')
    urls = [str(item.get('menu_url', '')).lower().split('?')[0].strip('./') for item in metadata['menu']]
    rows = []
    for name, text in files.items():
        basename = Path(name).name
        full = name.lower()
        references = []
        for other, source in files.items():
            if other == name: continue
            # A basename is evidence of a reference, not proof of runtime use.
            if basename.lower() in source.lower(): references.append(other)
        menu = any(url == full or (url.rstrip('/') == str(Path(name).parent).replace('\\','/').lower() and basename.lower() in ['index.php','index.html']) for url in urls if url)
        autoload = name.startswith('clases/') and basename.lower().endswith('.class.php')
        status = 'con referencia' if references or menu else 'sin referencia estática'
        if autoload: status = 'autoload dinámico: conservar'
        if name.startswith('comun/plantillas/'): status = 'plantilla/recursos dinámicos: revisar'
        findings = []
        patterns = {
            'SQL y entrada HTTP en misma línea (revisar flujo)': r'(?im)^.*(?:SELECT|UPDATE|INSERT|DELETE).*\$_(?:GET|POST|REQUEST).*$',
            'eval presente (revisar PHP/JS/comentarios)': r'\beval\s*\(',
            'API mysql antigua': r'\bmysql_(?:query|connect|fetch_array|fetch_assoc)\s*\(',
            'display_errors activo': r'display_errors[\s\S]{0,8}[\x27\x22]1[\x27\x22]',
            'SELECT * (revisar volumen)': r'(?i)SELECT\s+\*',
        }
        for label, pattern in patterns.items():
            count = len(re.findall(pattern,text))
            if count: findings.append(f'{label}: {count}')
        rows.append({'archivo':name, 'lineas':len(text.splitlines()), 'bytes':len(text.encode()), 'estado':status,
                     'en_menu': 'sí' if menu else 'no', 'referencias':len(references),
                     'referenciado_por':'; '.join(references[:8]), 'indicadores':'; '.join(findings)})
    output = ROOT/'output'; output.mkdir(exist_ok=True)
    with (output/'inventario-codigo-guagua.csv').open('w', encoding='utf-8-sig', newline='') as handle:
        writer=csv.DictWriter(handle, fieldnames=list(rows[0])); writer.writeheader(); writer.writerows(rows)
    suspects=[row for row in rows if row['estado']=='sin referencia estática' and row['archivo'].endswith('.php')]
    with (output/'archivos-sin-referencias.md').open('w',encoding='utf-8') as handle:
        handle.write('# Archivos sin referencias estáticas\n\n')
        handle.write(f'Se revisaron {len(rows)} archivos propios PHP/JS/HTML y herramientas de consola, y {len(metadata["menu"])} entradas de menú. Se excluyeron librerías de terceros, datos de usuarios, pruebas y salidas.\n\n')
        handle.write('Estos candidatos no tienen referencias por nombre en el código examinado ni una entrada directa en el menú. Esto **no demuestra que estén sin uso**: faltan registros históricos de acceso, cron externo, favoritos y llamadas dinámicas. No se eliminó ningún archivo. Las clases cargadas por autoload se conservan.\n\n')
        handle.write('| Archivo candidato | Líneas | Observación |\n|---|---:|---|\n')
        for row in suspects:
            handle.write(f'| `{row["archivo"]}` | {row["lineas"]} | {row["indicadores"] or "Sin referencia detectada; verificar acceso directo y tareas externas."} |\n')
        handle.write('\nInventario completo: `output/inventario-codigo-guagua.csv`. Referencias detectadas pueden estar en comentarios o archivos igualmente huérfanos; no equivalen a uso confirmado.\n')
    print(f'OK: {len(rows)} archivos propios, {len(metadata["menu"])} entradas de menú, {len(suspects)} candidatos PHP sin referencias. No se borraron archivos.')

if __name__ == '__main__':
    import sys
    inventory(json.loads(Path(sys.argv[1]).read_text(encoding='utf-8-sig')))
