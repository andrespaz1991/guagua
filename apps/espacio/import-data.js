'use strict';
globalThis.EspacioImport = {
 bytes(value){return new TextEncoder().encode(value).length},
 read(text){
  if(this.bytes(text)>10000000)throw Error('El archivo supera el límite de 10 MB para importación.');
  let input;try{input=JSON.parse(text.replace(/^\uFEFF/,''))}catch{throw Error('El archivo no contiene JSON válido.')}
  if(!input||!Array.isArray(input.pages)||!input.pages.length||input.pages.length>500)throw Error('Selecciona un archivo exportado por Guagua Espacio que contenga páginas.');
  const ids=new Set(),types=['text','h1','h2','bullet','todo','quote','callout','divider'];
  const pages=input.pages.map((p,index)=>{
   if(!p||typeof p.id!=='string'||!(/^[a-zA-Z0-9_-]{1,80}$/).test(p.id)||ids.has(p.id)||typeof p.title!=='string'||this.bytes(p.title)>2000||!Array.isArray(p.blocks)||p.blocks.length>1000)throw Error('La página '+(index+1)+' tiene datos no válidos o identificadores repetidos.');
   ids.add(p.id);
   if(p.parent!=null&&typeof p.parent!=='string')throw Error('La estructura de páginas no es válida.');
   if(p.deleted!=null&&typeof p.deleted!=='boolean')throw Error('El estado de una página no es válido.');
   const blocks=p.blocks.map(b=>{
    if(!b||!types.includes(b.type)||typeof b.text!=='string')throw Error('Hay un bloque no compatible en «'+p.title+'».');
    const clean={type:b.type,text:b.text};
    if(b.checked!=null&&typeof b.checked!=='boolean')throw Error('El estado de una tarea no es válido.');
    clean.checked=!!b.checked;
    if(b.indent!=null){if(!Number.isInteger(b.indent)||b.indent<0||b.indent>5)throw Error('El nivel de una subtarea no es válido.');clean.indent=b.indent}
    if(b.link!=null&&b.link!==''){
     let url;try{if(typeof b.link!=='string'||this.bytes(b.link)>2048||!/^https?:\/\//i.test(b.link))throw Error();url=new URL(b.link)}catch{throw Error('Hay un enlace no válido en «'+p.title+'».')}
     if(url.username||url.password||!['http:','https:'].includes(url.protocol))throw Error('Los enlaces deben ser HTTP o HTTPS sin credenciales.');
     if(this.bytes(url.href)>2048)throw Error('Un enlace supera los 2048 bytes permitidos.');clean.link=url.href;
    }
    if(b.completedAt!=null){if(EspacioReports.timestamp(b.completedAt)===null||new Date(b.completedAt).toISOString()!==b.completedAt)throw Error('Hay una fecha de cumplimiento no válida.');if(clean.checked)clean.completedAt=b.completedAt}
    return clean;
   });
   EspacioOrder.normalize(blocks);
   return {id:p.id,title:p.title,parent:p.parent||null,icon:typeof p.icon==='string'?Array.from(p.icon).slice(0,16).join(''):'📄',cover:Number.isInteger(p.cover)&&p.cover>=0&&p.cover<4?p.cover:0,blocks,deleted:!!p.deleted};
  });
  const byId=new Map(pages.map(p=>[p.id,p]));let skipped=0,orphans=0;
  const active=pages.filter(page=>{
   const seen=new Set();let p=page,deleted=false;
   while(p){if(seen.has(p.id))throw Error('El archivo contiene páginas con referencias circulares.');seen.add(p.id);if(p.deleted)deleted=true;p=byId.get(p.parent)}
   if(deleted){skipped++;return false}return true;
  });
  const activeIds=new Set(active.map(p=>p.id));active.forEach(p=>{if(p.parent&&!activeIds.has(p.parent)){p.parent=null;orphans++}});
  if(!active.length)throw Error('El archivo no contiene proyectos activos para importar.');
  return {pages:active,roots:active.filter(p=>!p.parent),skipped,orphans};
 },
 prepare(data,selected,existing,makeId,version=0,now=new Date()){
  const originals=[],seen=new Set();
  const visit=id=>{const p=data.pages.find(p=>p.id===id);if(!p||seen.has(id))return;seen.add(id);originals.push(p);data.pages.filter(c=>c.parent===id).forEach(c=>visit(c.id))};
  data.roots.filter(p=>selected.includes(p.id)).forEach(p=>visit(p.id));
  if(!originals.length)throw Error('Selecciona al menos un proyecto.');
  if(existing.length+originals.length>500)throw Error('La importación supera el límite de 500 páginas del espacio.');
  const used=new Set(existing.map(p=>p.id)),map=new Map();
  originals.forEach(p=>{const id=makeId();if(typeof id!=='string'||!(/^[a-zA-Z0-9_-]{1,80}$/).test(id)||used.has(id))throw Error('No se pudo generar una copia independiente. Intenta de nuevo.');used.add(id);map.set(p.id,id)});
  const copies=originals.map(p=>({...structuredClone(p),id:map.get(p.id),parent:map.get(p.parent)||null,deleted:false,favorite:false,updated:now.toISOString()}));
  if(this.bytes(JSON.stringify({version,pages:[...existing,...copies]}))>2000000)throw Error('La importación supera los 2 MB disponibles por espacio. Selecciona menos proyectos.');
  return copies;
 }
};
