import sys, json, re, unicodedata
from pathlib import Path
sys.path.insert(0, str(Path('tmp/xls-reader').resolve()))
import xlrd

root = Path('apps/inventario_tecnologico')
old = json.loads((root/'inventario.json').read_text(encoding='utf-8'))
previous = {e['id']:e for e in old['equipos']}
book = xlrd.open_workbook('D:/Descargas/vallesoloct2026 (2).xls')
sheet = book.sheet_by_index(0)
def norm(s):
    return ' '.join(unicodedata.normalize('NFKD', str(s)).encode('ascii','ignore').decode().lower().split())
def category(tipo):
    t=norm(tipo)
    if 'computador' in t: return 'Computación'
    if any(w in t for w in ['proyector','televisor','cmara','camara']): return 'Audiovisual'
    if any(w in t for w in ['parlante','sonido']): return 'Sonido'
    if 'impresora' in t: return 'Impresión'
    if 'soporte' in t: return 'Accesorios'
    if any(w in t for w in ['ventilador']): return 'Ventilación'
    if 'extintor' in t: return 'Seguridad'
    if 'ecologico' in t: return 'Aseo y ambiente'
    if 'colchoneta' in t: return 'Deportes'
    if any(w in t for w in ['never','congelador','licuadora','olla','esterilizador','meson','alimentos','molino']): return 'Cocina'
    if any(w in t for w in ['silla','mesa','escritorio','archivador','estanter','biblioteca','cajonera','almacenamiento']): return 'Mobiliario'
    return 'Otros'
groups={}
source_ids=[]
for r in range(1,sheet.nrows):
    row=sheet.row_values(r)
    if not row[14] or not row[2]: continue
    ident=str(int(row[14])); source_ids.append(ident)
    tipo=str(row[2]).strip().replace('C�maras','Cámaras').replace('m�viles','móviles')
    detail=str(row[15]).strip(); location=str(row[7]).strip(); state=str(row[22]).strip()
    quantity=int(row[9]) if float(row[9]).is_integer() else float(row[9])
    date=xlrd.xldate_as_datetime(row[23],book.datemode).date().isoformat() if row[23] else None
    key=(norm(tipo),norm(detail),norm(state),norm(location))
    member={'id':ident,'placa':str(row[8]).strip(),'cantidad':quantity,'fecha_adquisicion':date,'fila_excel':r+1}
    if key not in groups:
        fields=dict(re.findall(r'(MARCA|REFERENCIA|COLOR|SERIE):\s*(.*?)(?=\s+-\s+[A-Z]+:|$)',detail,re.I))
        item=dict(previous.get(ident,{}))
        item.update(id=ident,nombre=item.get('nombre',tipo),tipo=tipo,categoria=category(tipo),detalle=detail,estado=state,ubicacion=location,cantidad=0,fecha_adquisicion=date,placa=member['placa'],marca=item.get('marca',fields.get('MARCA','')),referencia=item.get('referencia',fields.get('REFERENCIA',fields.get('SERIE',''))),color=fields.get('COLOR',''),imagenes=item.get('imagenes',[]),registros=[])
        groups[key]=item
    groups[key]['cantidad']+=quantity
    groups[key]['registros'].append(member)
items=list(groups.values())
for item in items:
    dates={m['fecha_adquisicion'] for m in item['registros']}
    if len(dates)>1: item['fecha_adquisicion']=None
    folder=root/'imagenes'/item['id']; folder.mkdir(parents=True,exist_ok=True)
    (folder/'.gitkeep').touch(exist_ok=True)
old.update(titulo='Inventario general',equipos=items,registros_origen=len(source_ids),cantidad_total=sum(i['cantidad'] for i in items),agrupacion='Mismo tipo, descripción (marca, referencia y color), estado y ubicación. IDs y placas originales conservados en registros.')
assert len(source_ids)==len(set(source_ids))==130
assert sum(len(i['registros']) for i in items)==130
assert sum(i['cantidad'] for i in items)==sum(sheet.cell_value(r,9) for r in range(1,sheet.nrows))
(root/'inventario.json').write_text(json.dumps(old,ensure_ascii=False,indent=2)+'\n',encoding='utf-8')
print(json.dumps({'registros_excel':130,'fichas':len(items),'unidades':old['cantidad_total'],'grupos':[{ 'id':i['id'],'tipo':i['tipo'],'detalle':i['detalle'],'cantidad':i['cantidad']} for i in items if len(i['registros'])>1]},ensure_ascii=False))
