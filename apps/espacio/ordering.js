'use strict';
// Shared by pointer interactions and the ordering checks.
globalThis.EspacioOrder = {
 duplicateProject(pages, rootId, makeId, now=new Date()) {
  const originals=[],seen=new Set();
  const collect=id=>{
   const page=pages.find(p=>p.id===id&&!p.deleted);
   if(!page||seen.has(id))return;
   seen.add(id);originals.push(page);
   pages.filter(p=>p.parent===id&&!p.deleted).forEach(p=>collect(p.id));
  };collect(rootId);
  if(!originals.length)return [];
  const ids=new Map(originals.map(p=>[p.id,makeId()]));
  if(new Set(ids.values()).size!==ids.size||pages.some(p=>Array.from(ids.values()).includes(p.id)))throw Error('No se pudo crear una copia con identificadores nuevos. Intenta de nuevo.');
  return originals.map((page,index)=>{
   const copy=structuredClone(page);copy.id=ids.get(page.id);
   copy.parent=index===0?(page.parent||null):ids.get(page.parent);
   if(index===0){
    const name=Array.from(page.title||'Sin título');
    while(new TextEncoder().encode(name.join('')+' (copia)').length>2000)name.pop();
    copy.title=name.join('')+' (copia)';
   }
   copy.favorite=false;copy.deleted=false;copy.updated=now.toISOString();return copy;
  });
 },
 level(block) {return block?.type === 'todo' ? Math.max(0, Math.min(5, Number.isInteger(block.indent) ? block.indent : 0)) : 0;},
 normalize(blocks) {
  let previous = -1;
  for(const b of blocks){if(b.type==='todo'){b.indent=Math.min(this.level(b),previous+1);previous=b.indent}else{delete b.indent;previous=-1}}
 },
 end(blocks,index) {
  if(blocks[index]?.type!=='todo')return index+1;
  let end=index+1;
  while(end<blocks.length&&blocks[end].type==='todo'&&this.level(blocks[end])>this.level(blocks[index]))end++;
  return end;
 },
 indentTask(blocks,index,delta) {
  const b=blocks[index];if(b?.type!=='todo')return false;
  const level=this.level(b), next=level+delta;
  if(next<0||next>5||delta>0&&(index===0||blocks[index-1].type!=='todo'||this.level(blocks[index-1])<level))return false;
  const end=this.end(blocks,index);
  if(delta>0&&blocks.slice(index,end).some(x=>this.level(x)>=5))return false;
  for(let i=index;i<end;i++)blocks[i].indent=this.level(blocks[i])+delta;
  this.normalize(blocks);return true;
 },
 moveBlock(blocks, from, target, after) {
  if (!Number.isInteger(from) || !Number.isInteger(target) || from < 0 || target < 0 || from >= blocks.length || target >= blocks.length || from === target) return false;
  const end=this.end(blocks,from);
  if(target>=from&&target<end)return false;
  let index = after ? this.end(blocks,target) : target;
  if (from < index) index-=end-from;
  if (index === from) return false;
  const items = blocks.splice(from, end-from);
  if(items[0].type==='todo'){
   const previous=blocks[index-1];
   const nextLevel=Math.min(this.level(items[0]),previous?.type==='todo'?this.level(previous)+1:0);
   const delta=nextLevel-this.level(items[0]);
   items.forEach(b=>b.indent=this.level(b)+delta);
  }
  blocks.splice(index, 0, ...items);
  this.normalize(blocks);
  return true;
 },
 movePage(pages, sourceId, targetId, position, favorites = false) {
  const source = pages.find(p => p.id === sourceId && !p.deleted);
  const target = pages.find(p => p.id === targetId && !p.deleted);
  if (!source || !target || source === target || !['before','after','inside'].includes(position)) return false;
  const parent = favorites ? source.parent : position === 'inside' ? target.id : target.parent;
  const seen = new Set();
  for (let ancestor = pages.find(p => p.id === parent); ancestor; ancestor = pages.find(p => p.id === ancestor.parent)) {
   if (ancestor === source || seen.has(ancestor.id)) return false;
   seen.add(ancestor.id);
  }
  pages.splice(pages.indexOf(source), 1);
  const index = pages.indexOf(target) + (position === 'before' ? 0 : 1);
  source.parent = parent || null;
  pages.splice(index, 0, source);
  return true;
 }
};
