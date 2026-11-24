from pathlib import Path
import re,json
ROOT=Path(r'D:\xampp\htdocs\guagua')
src=max((ROOT/'reportes/bd/automatico').glob('guagua_*.sql'),key=lambda p:p.stat().st_mtime)
text=src.read_text(encoding='utf-8-sig',errors='replace')
tables={}
for m in re.finditer(r'CREATE TABLE(?: IF NOT EXISTS)? `([^`]+)`\s*\((.*?)\)\s*ENGINE=([^\s;]+)([^;]*);',text,re.S):
    name,body,engine,options=m.groups()
    tables[name]={'columns':re.findall(r'^\s*`([^`]+)`\s+([^\n]+)',body,re.M),'engine':engine,'constraints':re.findall(r'^\s*((?:PRIMARY|UNIQUE|KEY|CONSTRAINT|FULLTEXT).*)',body,re.M)}
for m in re.finditer(r'ALTER TABLE `([^`]+)`\s*(.*?);',text,re.S):
    if m[1] in tables: tables[m[1]]['constraints'].append(m[2].strip())
keep=['ano_lectivo','periodo','institucion_educativa','categoria_curso','materia','materia_oficial','asignacion','curso','horario','inscripcion','usuario','acudiente_estudiante','inicio_anual_borradores','notas_auditoria','asistencias']
out={'archivo':str(src.relative_to(ROOT)),'tablas_totales':len(tables),'nombres_tablas':list(tables),'tablas':{k:tables[k] for k in keep if k in tables}}
def parse_values(s):
    rows=[];row=[];buf='';quote=False;escape=False;depth=0
    for c in s:
        if quote:
            if escape: buf+=c;escape=False
            elif c=='\\': escape=True
            elif c=="'": quote=False
            else:buf+=c
        elif c=="'":quote=True
        elif c=='(':
            depth+=1
            if depth==1:row=[];buf=''
        elif c==')':
            if depth==1:row.append(buf.strip());rows.append(row);buf=''
            depth-=1
        elif c==',' and depth==1:row.append(buf.strip());buf=''
        elif depth:buf+=c
    return rows
for table in ['menu_items2','ano_lectivo','periodo','categoria_curso']:
    vals=[]
    for m in re.finditer(r'INSERT INTO `'+table+r'`\s*(?:\((.*?)\)\s*)?VALUES\s*(.*?);\s*(?:\n|$)',text,re.S):
        cols=re.findall(r'`([^`]+)`',m[1]) if m[1] else [x[0] for x in tables[table]['columns']]
        vals.extend(dict(zip(cols,row)) for row in parse_values(m[2]))
    out[table]=vals
(Path(__file__).parent/'evidencia_respaldo.json').write_text(json.dumps(out,ensure_ascii=False,indent=2),encoding='utf-8')
print('Respaldo',out['archivo'],'tablas',len(tables),'menu',len(out['menu_items2']))
for key in ['ano_lectivo','periodo','categoria_curso','asignacion','inscripcion','acudiente_estudiante','materia','materia_oficial']:
    t=tables.get(key,{})
    print(key,t.get('engine'),'; '.join(k+' '+v for k,v in t.get('columns',[])))
    print('Constraints',t.get('constraints'))
print('MENU')
for r in out['menu_items2']:print(r.get('menu_item_id'),r.get('menu_item_name'),r.get('menu_url'))
