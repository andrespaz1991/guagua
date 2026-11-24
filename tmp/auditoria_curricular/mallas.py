from pathlib import Path
import json,re,unicodedata
ROOT=Path(__file__).parent
MAN=json.loads((ROOT/'manifest.json').read_text(encoding='utf-8'))
def norm(s): return ''.join(c for c in unicodedata.normalize('NFD',s.lower()) if unicodedata.category(c)!='Mn')
def rows(prefix):
    p=next((ROOT/'fuentes').glob(prefix+'-*'))
    return [json.loads(l.split(': ',1)[1]) for l in p.read_text(encoding='utf-8').splitlines() if l.startswith('FILA')], next(x['source'] for x in MAN if Path(x['text']).name==p.name)
def grade(s):
    s=norm(s)
    for g,n in [(6,'sexto'),(7,'septimo'),(8,'octavo'),(9,'noveno'),(10,'decimo'),(11,'undecimo')]:
        if n in s: return g
    m=re.search(r'grado\s*:?\s*(\d+)',s)
    return int(m[1]) if m else None
out=[]
sources={'artes':'012','naturales':'013','fisica_educacion':'029','lenguaje':'064','ingles':'065','geometria':'066','religion':'070','tecnologia':'071'}
for area,prefix in sources.items():
    rs,path=rows(prefix); g=None
    for cells in rs:
        hdr=[c for c in cells if re.search('grado\s*:',norm(c))]
        if hdr:
            g=grade(hdr[-1]); continue
        if g not in range(6,10) or len(cells)!=5: continue
        pm=re.match(r'\s*([1-4])\b',cells[0])
        if not pm: continue
        out.append(dict(area=area,grado=g,periodo=int(pm[1]),estandar=cells[1],dba=cells[2],evidencias=cells[3],ejes=cells[4],source=path))
for area,prefixes in {'emprendimiento':['037','038','039','040'],'etica':['054','053','049','048'],'paz':['024','025','026','027']}.items():
    for prefix in prefixes:
        rs,path=rows(prefix); g=None
        for cells in rs:
            hdr=[c for c in cells if 'grado' in norm(c)]
            if hdr and grade(hdr[-1]): g=grade(hdr[-1])
            if g not in range(6,10): continue
            if len(cells)!=5: continue
            pm=re.match(r'\s*([1-4])\b',cells[0])
            if pm: out.append(dict(area=area,grado=g,periodo=int(pm[1]),estandar=cells[1],dba=cells[2],evidencias=cells[3],ejes=cells[4],source=path))
(ROOT/'mallas-registros.json').write_text(json.dumps(out,ensure_ascii=False,indent=2),encoding='utf-8')
for a in sources|{'emprendimiento':0,'etica':0,'paz':0}:
    print(a,[(g,sorted(r['periodo'] for r in out if r['area']==a and r['grado']==g)) for g in range(6,10)])
