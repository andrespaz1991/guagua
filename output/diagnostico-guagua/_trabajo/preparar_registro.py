from pathlib import Path
import re,json
ROOT=Path(r'D:\xampp\htdocs\guagua')
source=ROOT/'reportes/bd/automatico/guagua_20261119_183002.sql'
raw=source.read_bytes()
try: text=raw.decode('utf-8-sig')
except UnicodeDecodeError: text=raw.decode('cp1252',errors='replace')
schema=re.search(r'CREATE TABLE `menu_items2`\s*\((.*?)\)\s*ENGINE=',text,re.S)[1]
cols=re.findall(r'^\s*`([^`]+)`',schema,re.M)
def parse(s):
    rows=[];row=[];buf='';quote=False;escape=False;depth=0
    for c in s:
        if quote:
            if escape:buf+=c;escape=False
            elif c=='\\':escape=True
            elif c=="'":quote=False
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
mapping={7:'institucion',32:'inscripciones',34:'planeacion',36:'malla',38:'acudientes',46:'grados',47:'grados',48:'anio',49:'periodos',50:'apariencia',51:'apariencia',52:'personas',55:'personas',59:'excel_asistencia',60:'asistencia',62:'recursos',65:'archivos',66:'archivos',67:'reportes',70:'inscripciones',71:'asistencia',72:'seguimiento',73:'personas',75:'excel_asistencia',79:'utilidades',80:'utilidades',81:'utilidades',82:'recursos',83:'ia',87:'seguimiento',88:'reportes',90:'pae',91:'comunicaciones',92:'planeacion',96:'personales',97:'recursos',98:'recursos',99:'utilidades',100:'personales',101:'utilidades',102:'actividades',103:'recursos',104:'ia',105:'malla',107:'personales',108:'utilidades',109:'actividades',110:'carga',111:'inscripciones',112:'asistencia',113:'utilidades',114:'apariencia',115:'apariencia',116:'utilidades',117:'ia',118:'utilidades',119:'inventario',120:'actividades',121:'proyeccion',122:'proyeccion'}
modules=[]
for match in re.finditer(r'INSERT INTO `menu_items2`\s*(?:\((.*?)\)\s*)?VALUES\s*(.*?);\s*(?:\n|$)',text,re.S):
    names=re.findall(r'`([^`]+)`',match[1]) if match[1] else cols
    for row in parse(match[2]):
        d=dict(zip(names,row));id=int(d['menu_item_id']);route=d['menu_url'].replace('\\','/')
        route=re.sub(r'^(?:\.\./)+','',route).lstrip('/')
        if id==107:route='comun/whatsapp_diario.php'
        if id==90:route='apps/Restaurante/index.php'
        file=ROOT/route.split('?')[0]
        if file.is_dir():
            entry=next((file/p for p in ['index.php','index.html'] if (file/p).is_file()),None)
            available=entry is not None
        else:available=file.is_file()
        note=''
        if id==58:available=False;note='Acción de sesión. No se ejecuta desde el mapa.'
        elif not available:note='Ruta sin pantalla de entrada confirmada en el proyecto local.'
        modules.append({'id':id,'name':d['menu_item_name'],'route':route,'process':mapping.get(id),'available':available,'note':note})
groups={}
for m in modules:
    groups.setdefault(m['route'].lower(),[]).append(m)
for group in groups.values():
    if len(group)>1:
        for m in group:m['note']='Comparte destino con '+', '.join(x['name'] for x in group if x['id']!=m['id'])+'.'
out=ROOT/'apps/mapa_procesos/registro.js'
out.write_text('/* Entradas de navegación del respaldo local del 19 de noviembre de 2026. */\nwindow.GUAGUA_MAPA.modules = '+json.dumps(modules,ensure_ascii=False,indent=2)+';\n',encoding='utf-8')
print(json.dumps({'entradas':len(modules),'rutas_disponibles':sum(m['available'] for m in modules),'rutas_por_revisar':[m['name'] for m in modules if not m['available']]},ensure_ascii=False))
