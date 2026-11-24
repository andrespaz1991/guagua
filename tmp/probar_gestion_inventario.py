import urllib.request,http.cookiejar,json,copy
jar=http.cookiejar.CookieJar(); client=urllib.request.build_opener(urllib.request.HTTPCookieProcessor(jar))
url='http://localhost/guagua/apps/inventario_tecnologico/guardar.php'
def get(): return json.load(client.open(url))
def post(data,revision,token):
    req=urllib.request.Request(url,json.dumps({'inventario':data,'revision':revision}).encode(),{'Content-Type':'application/json','X-Inventory-Token':token})
    return json.load(client.open(req))
p=get(); data=copy.deepcopy(p['inventario'])
assert len(data['equipos'])==56
test={'id':'99999999','nombre':'Producto temporal de verificación','tipo':'Prueba','categoria':'Pruebas','cantidad':1,'estado':'BUENO','placa':'PRUEBA','imagenes':[],'registros':[{'id':'99999999','cantidad':1,'placa':'PRUEBA','estado':'BUENO'}]}
data['equipos'].append(test)
r=post(data,p['revision'],p['token']);assert len(r['inventario']['equipos'])==57
try: post(data,p['revision'],p['token']);raise AssertionError('No detectó conflicto')
except urllib.error.HTTPError as e: assert e.code==409
print('Producto temporal creado; conflicto de versiones verificado')
