'use strict';
globalThis.EspacioReports = {
 timestamp(value) {
  if(typeof value!=='string'||!/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}\.\d{3}Z$/.test(value))return null;
  const time=Date.parse(value);return Number.isFinite(time)?time:null;
 },
 complete(block, checked, now=new Date()) {
  if(block.type!=='todo')return;
  const wasChecked=!!block.checked;block.checked=!!checked;
  if(checked&&!wasChecked)block.completedAt=now.toISOString();
  if(!checked)delete block.completedAt;
 },
 collect(pages) {
  const rows=[];
  for(const page of pages){
   if(page.deleted)continue;
   const parents=[];
   page.blocks.forEach((block,index)=>{
    if(block.type!=='todo'){parents.length=0;return}
    const level=Math.min(EspacioOrder.level(block),parents.length);
    parents.length=level;
    rows.push({page,block,index,level,ancestors:parents.map(p=>p.text||'Tarea sin título'),completedAt:block.checked?this.timestamp(block.completedAt):null});
    parents.push(block);
   });
  }
  return rows;
 },
 summary(rows) {
  const total=rows.length,done=rows.filter(row=>row.block.checked).length;
  return {total,done,pending:total-done,percent:total?Math.round(done*100/total):0};
 },
 filter(rows, {page='',status='all',query='',order='recent'}={}) {
  const search=query.trim().toLocaleLowerCase('es');
  const result=rows.filter(row=>(!page||row.page.id===page)&&(status==='all'||(status==='done'?!!row.block.checked:!row.block.checked))&&(!search||[row.block.text,row.page.title,...row.ancestors].join(' ').toLocaleLowerCase('es').includes(search)));
  return result.sort((a,b)=>{
   if(order==='pending'&&!!a.block.checked!==!!b.block.checked)return a.block.checked?1:-1;
   if(order==='recent'){
    if(!!a.block.checked!==!!b.block.checked)return a.block.checked?-1:1;
    if(a.completedAt!==b.completedAt)return (b.completedAt??-Infinity)-(a.completedAt??-Infinity);
   }
   return (a.page.title||'Sin título').localeCompare(b.page.title||'Sin título','es')||a.page.id.localeCompare(b.page.id)||a.index-b.index;
  });
 }
};
