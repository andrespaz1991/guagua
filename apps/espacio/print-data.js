'use strict';
globalThis.EspacioPrint = {
 project(pages,rootId) {
  const result=[],seen=new Set();
  const visit=(id,depth)=>{
   const page=pages.find(p=>p.id===id&&!p.deleted);
   if(!page||seen.has(id))return;
   seen.add(id);result.push({page,depth});
   pages.filter(p=>p.parent===id&&!p.deleted).forEach(p=>visit(p.id,depth+1));
  };visit(rootId,0);return result;
 },
 sections(blocks) {
  return blocks.flatMap((b,index)=>{
   if(b.type==='h1'||b.type==='h2')return [{index,title:b.text||'Sección sin título'}];
   if(b.type==='todo'&&EspacioOrder.level(b)===0&&EspacioOrder.end(blocks,index)>index+1)return [{index,title:'Tarea: '+(b.text||'Sin título')}];
   return [];
  });
 },
 section(blocks,index) {
  const first=blocks[index];if(!first)return [];
  if(first.type==='todo')return blocks.slice(index,EspacioOrder.end(blocks,index));
  if(!['h1','h2'].includes(first.type))return [];
  let end=index+1;
  while(end<blocks.length&&blocks[end].type!=='h1'&&!(first.type==='h2'&&blocks[end].type==='h2'))end++;
  return blocks.slice(index,end);
 }
};
