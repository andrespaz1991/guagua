from pathlib import Path
import json, zipfile, xml.etree.ElementTree as ET
from pypdf import PdfReader

BASE = Path(r'C:\Users\Andres\OneDrive - UNIVERSIDAD DE SANTANDER - UDES\SEDUCA\La Josefina\Vallesol\Formatos Frecuentes')
OUT = Path(__file__).parent
W = '{http://schemas.openxmlformats.org/wordprocessingml/2006/main}'
manifest = []
sources = list((BASE/'MALLAS CURRICULARES 2022').rglob('*.docx')) + list((BASE/'MALLAS CURRICULARES 2022').rglob('*.pdf'))
for area in ('Matematica', 'Ciencias Sociales'):
    sources += list((BASE/'GUIAS DE POSTPRIMARIA'/area).glob('*Grado*.pdf'))
for n,p in enumerate(sources):
    if p.name.startswith('~$'): continue
    target=OUT/'fuentes'/f'{n:03d}-{p.stem}.txt'
    target.parent.mkdir(exist_ok=True)
    if p.suffix.lower()=='.pdf':
        doc=PdfReader(p)
        content='\n'.join(f'\n=== PÁGINA PDF {i+1} ===\n'+page.extract_text() for i,page in enumerate(doc.pages))
        pages=len(doc.pages)
    else:
        if not zipfile.is_zipfile(p):
            print('DOCX no válido:',p)
            continue
        with zipfile.ZipFile(p) as z: root=ET.fromstring(z.read('word/document.xml'))
        body=root.find(W+'body')
        chunks=[]
        for node in body:
            if node.tag==W+'p': chunks.append(''.join(node.itertext()) if False else ''.join(t.text or '' for t in node.iter(W+'t')))
            elif node.tag==W+'tbl':
                chunks.append('\n[TABLA]')
                for ri,row in enumerate(node.findall(W+'tr')):
                    cells=['\n'.join(''.join(t.text or '' for t in par.iter(W+'t')) for par in cell.findall(W+'p')) for cell in row.findall(W+'tc')]
                    chunks.append(f'FILA {ri}: '+json.dumps(cells,ensure_ascii=False))
                chunks.append('[/TABLA]\n')
        content='\n'.join(chunks); pages=None
    target.write_text(content,encoding='utf-8')
    manifest.append({'source':str(p),'text':str(target),'pages':pages,'chars':len(content)})
(OUT/'manifest.json').write_text(json.dumps(manifest,ensure_ascii=False,indent=2),encoding='utf-8')
print(json.dumps([m for m in manifest if 'GUIAS' in m['source']],ensure_ascii=False,indent=2))
print('Fuentes extraídas:',len(manifest))
