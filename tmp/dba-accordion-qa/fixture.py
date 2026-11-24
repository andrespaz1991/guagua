from pathlib import Path
import json,re
repo=Path(__file__).resolve().parent.parent.parent
app=repo/'apps/dba'
out=repo/'output/playwright/dba-accordion'
out.mkdir(parents=True,exist_ok=True)
snapshot=json.loads((repo/'output/auditoria-emprendimiento-ppp-2026-11-01/snapshot-aplicado.json').read_text(encoding='utf-8'))
clock=json.loads((Path(__file__).parent/'calendario.json').read_text(encoding='utf-8'))
stds=[s for s in snapshot['estandar'] if s['grado'] in [6,7] and s['id_materia_oficial'] in [1,9]]
dbas=[d for d in snapshot['dba'] if d['id_estandar'] in {s['id_estandar'] for s in stds}]
data=dict(grados=[g for g in snapshot['grado'] if g['id_grado'] in [6,7]],materias=[m for m in snapshot['materia_oficial'] if m['id_materia'] in [1,9]],estandares=stds,dbas=dbas,ejes_tematicos=[x for x in snapshot['eje_tematico'] if x['id_dba'] in {d['id_dba'] for d in dbas}],evidencias=[e for e in snapshot['evidencia_de_aprendizaje'] if e['id_dba'] in {d['id_dba'] for d in dbas}],**clock)
html=(app/'index.php').read_text(encoding='utf-8')
html=html.replace("<?php require __DIR__ . '/views/evidencias.php'; ?>",(app/'views/evidencias.php').read_text(encoding='utf-8'))
html=re.sub(r'<\?php[\s\S]*?\?>','',html)
html=re.sub(r'(href|src)="(assets/[^"?]+)(?:\?[^" ]*)?"',lambda m:f'{m[1]}="/guagua/apps/dba/{m[2]}"',html)
mock='''<script>
window.qaErrors=[]; window.addEventListener('error',e=>qaErrors.push(e.message));
window.qaData=DATA;
window.fetch=async (url,options={})=>{
  if(String(url).includes('get_data'))return {ok:true,json:async()=>({status:'success',data:structuredClone(qaData)})};
  const payload=options.body && typeof options.body==='string' ? JSON.parse(options.body) : {};
  if(String(url).includes('crud') && payload.type==='dba' && payload.action==='edit'){
    const d=qaData.dbas.find(d=>String(d.id_dba)===String(payload.id_dba));
    Object.assign(d,{nombre_dba:payload.nombre_dba,descripcion_dba:payload.descripcion_dba,palabras_clave:payload.palabras_clave});
  }
  return {ok:true,json:async()=>({status:'success',message:'Guardado de prueba'})};
};
</script>'''.replace('DATA',json.dumps(data,ensure_ascii=False).replace('</','<\\/'))
html=html.replace('<script src=',mock+'<script src=',1)
(out/'index.html').write_text(html,encoding='utf-8')
print((out/'index.html').as_uri())
