import urllib.request,urllib.error,http.cookiejar,json,copy,base64
from pathlib import Path
jar=http.cookiejar.CookieJar();c=urllib.request.build_opener(urllib.request.HTTPCookieProcessor(jar));base='http://localhost/guagua/apps/inventario_tecnologico/'
def get():return json.load(c.open(base+'guardar.php'))
def save(d,r,t):return json.load(c.open(urllib.request.Request(base+'guardar.php',json.dumps({'inventario':d,'revision':r}).encode(),{'Content-Type':'application/json','X-Inventory-Token':t})))
def upload(rev,token,content):
    boundary='----InventoryPhotoTestBoundary';body=b''
    for name,value in [('id','99999998'),('revision',rev)]:body+=f'--{boundary}\r\nContent-Disposition: form-data; name="{name}"\r\n\r\n{value}\r\n'.encode()
    body+=f'--{boundary}\r\nContent-Disposition: form-data; name="imagenes[]"; filename="prueba.png"\r\nContent-Type: image/png\r\n\r\n'.encode()+content+f'\r\n--{boundary}--\r\n'.encode()
    return json.load(c.open(urllib.request.Request(base+'subir_imagenes.php',body,{'Content-Type':'multipart/form-data; boundary='+boundary,'X-Inventory-Token':token})))
p=get();token=p['token'];original=copy.deepcopy(p['inventario']);d=copy.deepcopy(original);d['equipos'].append({'id':'99999998','nombre':'Prueba temporal de fotos','tipo':'Prueba','categoria':'Pruebas','estado':'BUENO','cantidad':1,'imagenes':[]});saved=[]
try:
    r=save(d,p['revision'],token)
    try:upload(r['revision'],token,b'<?php echo "invalid"; ?>');raise AssertionError('Aceptó archivo inválido')
    except urllib.error.HTTPError as e:assert e.code==422
    png=base64.b64decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+jRZkAAAAASUVORK5CYII=')
    r=upload(r['revision'],token,png);saved=r['imagenes'];assert len(saved)==1
    assert any(e['id']=='99999998' and e['imagenes']==saved for e in get()['inventario']['equipos'])
    assert c.open(base+saved[0]).read()==png
    print('Subida PNG, asociación persistente, acceso de imagen y rechazo de archivo inválido: OK')
finally:
    current=get();save(original,current['revision'],token)
    for path in saved:(Path('apps/inventario_tecnologico')/path).unlink()
    folder=Path('apps/inventario_tecnologico/imagenes/99999998')
    if folder.exists() and not any(folder.iterdir()):folder.rmdir()
    print('Inventario original restaurado; fotografía de prueba retirada')
