const fs=require('fs');
const path=require('path');
const vm=require('vm');
const root=path.resolve(__dirname,'../../..');
const app=path.join(root,'apps/mapa_procesos');
const sandbox={window:{}};
vm.runInNewContext(fs.readFileSync(path.join(app,'datos.js'),'utf8'),sandbox);
vm.runInNewContext(fs.readFileSync(path.join(app,'registro.js'),'utf8'),sandbox);
const model=sandbox.window.GUAGUA_MAPA;
const ids=new Set(model.processes.map(p=>p.id));
const missing=[];
for(const p of model.processes)for(const l of p.links){
  const target=path.resolve(root,l[1].split('?')[0]);
  if(!target.startsWith(root+path.sep))throw Error('Ruta fuera de Guagua');
  if(!fs.existsSync(target))missing.push(l[1]);
}
for(const e of [...model.dependencies,...model.flowEdges])if(!ids.has(e[0])||!ids.has(e[1]))throw Error('Conexión inválida '+e);
for(const m of model.modules)if(m.process&&!ids.has(m.process))throw Error('Proceso de registro inválido');
if(missing.length)throw Error('Pantallas inexistentes: '+missing.join(', '));
if(new Set(model.processes.map(p=>p.id)).size!==model.processes.length)throw Error('Identificadores repetidos');
const html=fs.readFileSync(path.join(app,'vista.html'),'utf8').replace('data-base="/guagua/"','data-base="http://localhost/guagua/"');
const css=fs.readFileSync(path.join(app,'mapa.css'),'utf8');
const js=['datos.js','registro.js','mapa.js'].map(n=>fs.readFileSync(path.join(app,n),'utf8')).join('\n');
const output=path.join(root,'output/diagnostico-guagua/Mapa_interactivo_Guagua.html');
fs.writeFileSync(output,'<!doctype html><html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Mapa interactivo de Guagua</title><style>body{margin:0}'+css+'</style></head><body>'+html+'<script>'+js.replace(/<\/script/gi,'<\\/script')+'</script></body></html>');
console.log(JSON.stringify({procesos:model.processes.length,ramas:model.branches.length,entradas:model.modules.length,conexiones:model.dependencies.length,salida:output}));
