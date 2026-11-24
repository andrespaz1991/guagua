const assert=require('node:assert/strict');
require('../apps/espacio/ordering.js');
let serial=0;
const nextId=()=>`copy-${++serial}`;
const task={type:'todo',text:'Preparar',checked:true,completedAt:'2026-10-18T05:00:00.000Z',link:'https://example.com/guia',indent:1};
const pages=[{id:'root',title:'Proyecto',parent:'folder',cover:2,icon:'🌿',favorite:true,blocks:[task]},
 {id:'child',title:'Sección',parent:'root',blocks:[{type:'text',text:'Contenido'}]},
 {id:'grandchild',title:'Detalle',parent:'child',blocks:[]},
 {id:'deleted',title:'Eliminada',parent:'root',deleted:true,blocks:[]},
 {id:'hidden',title:'Hija eliminada',parent:'deleted',blocks:[]},
 {id:'outside',title:'Otro proyecto',blocks:[]}];
const before=structuredClone(pages),now=new Date('2026-10-18T06:00:00.000Z');
const copies=EspacioOrder.duplicateProject(pages,'root',nextId,now);
assert.equal(copies.length,3);assert.equal(copies[0].title,'Proyecto (copia)');
assert.equal(copies[0].parent,'folder');assert.equal(copies[1].parent,copies[0].id);assert.equal(copies[2].parent,copies[1].id);
assert.equal(copies[1].title,'Sección');assert.equal(copies[0].cover,2);assert.equal(copies[0].icon,'🌿');
assert.equal(copies[0].favorite,false);assert.equal(copies[0].updated,now.toISOString());
assert.deepEqual(copies[0].blocks[0],task,'Se conservan tareas, niveles, estados, fechas y enlaces');
copies[0].blocks[0].text='Editada';copies[1].blocks.push({type:'todo',text:'Nueva'});
assert.deepEqual(pages,before,'Editar la copia no altera el proyecto original');
assert.deepEqual(EspacioOrder.duplicateProject(pages,'missing',nextId),[]);
assert.throws(()=>EspacioOrder.duplicateProject(pages,'root',()=> 'same-id'));
assert.throws(()=>EspacioOrder.duplicateProject(pages,'outside',()=> 'root'));
const long=EspacioOrder.duplicateProject([{id:'long',title:'🌿'.repeat(500),blocks:[]}],'long',nextId);
assert(new TextEncoder().encode(long[0].title).length<=2000);
console.log('Duplicación verificada: estructura completa, identificadores, copia independiente, estados, enlaces y papelera.');
