const assert = require('node:assert/strict');
require('../apps/espacio/ordering.js');
const order = globalThis.EspacioOrder;
const task = (text, indent=0) => ({type:'todo',text,indent});

let blocks=[task('Padre'),task('Hija'),task('Nieta',1),task('Otra')];
assert.equal(order.indentTask(blocks,0,1),false,'La primera tarea necesita un padre');
assert(order.indentTask(blocks,1,1));
assert.deepEqual(blocks.map(order.level),[0,1,2,0],'Se indenta el grupo completo');
assert(order.indentTask(blocks,1,-1));
assert.deepEqual(blocks.map(order.level),[0,0,1,0]);
assert.equal(order.end(blocks,1),3);

blocks=[task('Padre'),{...task('Hija',1),link:'https://example.com',checked:true},task('Otra')];
assert.equal(order.moveBlock(blocks,0,1,true),false,'No mover padre dentro de sus descendientes');
assert(order.moveBlock(blocks,0,2,true));
assert.deepEqual(blocks.map(b=>b.text),['Otra','Padre','Hija']);
assert.deepEqual(blocks.map(order.level),[0,0,1]);
assert.equal(blocks[2].link,'https://example.com');assert.equal(blocks[2].checked,true);
assert(order.moveBlock(blocks,1,0,false));
assert.deepEqual(blocks.map(b=>b.text),['Padre','Hija','Otra']);

blocks=[task('Inicio'),...Array.from({length:5},(_,i)=>task('Nivel '+(i+1),i+1))];
assert.equal(order.indentTask(blocks,1,1),false,'El grupo no supera cinco niveles');
assert.equal(order.indentTask(blocks,5,1),false);
blocks=[task('Huérfana',3),{type:'text',text:'Separador'},task('Otra huérfana',4),task('Descendiente',5)];
order.normalize(blocks);
assert.deepEqual(blocks.map(order.level),[0,0,0,1],'Normaliza tareas sin padre tras otros bloques');
console.log('Subtareas: niveles, límites, grupos, arrastre, enlaces y tareas completadas verificados.');
