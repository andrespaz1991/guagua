from pathlib import Path
from pypdf import PdfReader
import json
base=Path(r'C:\Users\Andres\OneDrive - UNIVERSIDAD DE SANTANDER - UDES\SEDUCA\La Josefina\Vallesol\Formatos Frecuentes\GUIAS DE POSTPRIMARIA\Proyectos productivos')
out=Path(__file__).parent/'ppp'; out.mkdir(exist_ok=True)
for p in base.glob('PPP_*.pdf'):
    if (out/(p.stem+'.txt')).exists():
        continue
    reader=PdfReader(p)
    pages=[]
    for i,page in enumerate(reader.pages):
        pages.append(f'\n=== PÁGINA PDF {i+1} ===\n'+page.extract_text())
    content='\n'.join(pages)
    (out/(p.stem+'.txt')).write_text(content,encoding='utf-8')
    print(json.dumps({'source':str(p),'pages':len(pages),'chars':len(content)},ensure_ascii=False),flush=True)
