from pathlib import Path
from urllib.request import urlopen
from pypdf import PdfReader
out=Path(__file__).parent/'oficiales'; out.mkdir(exist_ok=True)
links={
'matematicas':'https://www.colombiaaprende.edu.co/sites/default/files/files_public/2022-06/DBA_Matematicas-min.pdf',
'sociales':'https://www.colombiaaprende.edu.co/sites/default/files/files_public/2022-06/DBA_C.Sociales-V2.pdf'
}
for name,url in links.items():
    p=out/(name+'.pdf')
    if not p.exists(): p.write_bytes(urlopen(url,timeout=45).read())
    r=PdfReader(p)
    texts=[]
    for i,page in enumerate(r.pages):
        s=page.extract_text()
        if any(f'Grado {g}°' in s or f'Grado {g}º' in s for g in range(6,10)):
            texts.append(f'\n=== PÁGINA PDF {i+1} ===\n'+s)
    (out/(name+'.txt')).write_text('\n'.join(texts),encoding='utf-8')
    print(name,len(r.pages),'páginas;',len(texts),'relevantes')
