const assert=require('node:assert/strict');
require('../apps/espacio/ordering.js');
require('../apps/espacio/print-data.js');
const print=globalThis.EspacioPrint;
const blocks=[{type:'h1',text:'Objetivo'},{type:'text',text:'Introducción'},
 {type:'h2',text:'Recursos'},{type:'todo',text:'Preparar',indent:0},
 {type:'todo',text:'Guía',indent:1},{type:'h2',text:'Seguimiento'},
 {type:'text',text:'Progreso'},{type:'h1',text:'Cierre'}];
assert.deepEqual(print.sections(blocks).map(s=>s.index),[0,2,3,5,7]);
assert.deepEqual(print.section(blocks,0),blocks.slice(0,7),'El título principal incluye sus subtítulos');
assert.deepEqual(print.section(blocks,2),blocks.slice(2,5),'El subtítulo termina antes del siguiente subtítulo');
assert.deepEqual(print.section(blocks,3),blocks.slice(3,5),'La tarea incluye sus subtareas');
assert.deepEqual(print.section(blocks,1),[]);assert.deepEqual(print.section(blocks,100),[]);
const pages=[{id:'root',title:'Proyecto'}, {id:'b',parent:'root'}, {id:'c',parent:'b'}, {id:'a',parent:'root'}, {id:'deleted',parent:'root',deleted:true}, {id:'hidden',parent:'deleted'}];
assert.deepEqual(print.project(pages,'root').map(({page,depth})=>[page.id,depth]),[['root',0],['b',1],['c',2],['a',1]]);
assert.deepEqual(print.project(pages,'missing'),[]);
assert.equal(print.project([{id:'a',parent:'b'},{id:'b',parent:'a'}],'a').length,2,'Los ciclos no bloquean la impresión');
console.log('Impresión verificada: proyecto completo, orden de subpáginas, papelera, secciones y grupos de subtareas.');
