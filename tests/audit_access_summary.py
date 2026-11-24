"""Contraste opcional con acceso Apache; solo guarda recuentos sin IP ni consultas."""
from pathlib import Path
from urllib.parse import urlsplit, unquote, parse_qs
import collections, csv, json, re, sys
root = Path(__file__).resolve().parents[1]
log = Path(sys.argv[1])
with log.open('rb') as handle:
    handle.seek(max(0, log.stat().st_size - 10*1024*1024))
    text = handle.read().decode('utf-8',errors='replace')
counter = collections.Counter(); dates = []; requests=0
metadata = json.loads((root/'output/auditoria-menu-snapshot.json').read_text(encoding='utf-8'))
menu = {str(item['menu_item_id']):str(item['menu_url']).strip('./') for item in metadata['menu']}
for line in text.splitlines():
    match=re.search(r'\[([^\]]+)\] "(?:GET|POST|HEAD) ([^ ]+) HTTP/', line)
    if not match: continue
    url=urlsplit(match[2]); path=unquote(url.path)
    if not path.lower().startswith('/guagua/'): continue
    dates.append(match[1]); requests+=1
    relative=path[len('/guagua/'):].lower(); counter[relative]+=1
    if relative=='comun/modulo.php':
        query=parse_qs(url.query)
        target=query.get('path',[''])[0].strip('./') or menu.get(query.get('id',[''])[0],'')
        if target: counter[target.lower()]+=1
with (root/'output/inventario-codigo-guagua.csv').open(encoding='utf-8-sig') as handle: rows=list(csv.DictReader(handle))
summary={'limite_bytes':10*1024*1024,'peticiones_guagua':requests,'primera_fecha':dates[0] if dates else None,'ultima_fecha':dates[-1] if dates else None,
         'candidatos': [{'archivo':row['archivo'],'accesos_en_muestra':counter[row['archivo'].lower()]} for row in rows if row['estado']=='sin referencia estática' and row['archivo'].endswith('.php')]}
(root/'output/auditoria-accesos-resumen.json').write_text(json.dumps(summary,ensure_ascii=False,indent=2),encoding='utf-8')
print(json.dumps(summary,ensure_ascii=False))
