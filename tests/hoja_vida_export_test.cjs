const fs = require('fs');
const vm = require('vm');
const assert = require('assert');
const XLSX = require('../comun/vendor/xlsx.full.min.js');
const listeners = {};
const eventHandlers = {};
const elements = {};
const sample = {
 archivo:'fixture.xlsx', periodo:'Periodo 1 · 2026', inicio:'2026-01-13', fin:'2026-03-27',
 hojas:{Perfil:[['Campo','Valor'],['Documento','0012345678'],['Teléfono','03001234567'],['Nombre','=1+1']], Actividades:[['Materia','Actividad','Fecha','Nota'],['Matemáticas','Taller','2026-02-01',4.5]]}
};
for (const id of ['hv-card-dialog','hv-export-status','hv-card-close','hv-card-print','hv-export','hv-export-data']) {
 elements[id] = {addEventListener:(name,fn)=>{listeners[id+':'+name]=fn;},textContent:id==='hv-export-data'?JSON.stringify(sample):'',querySelector:()=>null};
}
const details=[{open:false}];
const context={document:{getElementById:id=>elements[id], querySelectorAll:s=>s.startsWith('details')?details:[],body:{classList:{contains:()=>false,remove:()=>{}}}},window:{addEventListener:(name,fn)=>{eventHandlers[name]=fn;}},XLSX:Object.assign({},XLSX,{writeFile:book=>{context.result=XLSX.write(book,{bookType:'xlsx',type:'buffer'});}}),Array,JSON,Error};
vm.runInNewContext(fs.readFileSync(require.resolve('../usuario/assets/hoja-vida.js'),'utf8'),context);
listeners['hv-export:click'].call(elements['hv-export']);
assert(context.result,'Workbook was not generated');
const book=XLSX.read(context.result,{type:'buffer'});
assert.deepStrictEqual(book.SheetNames,['Perfil','Actividades']);
assert.strictEqual(book.Sheets.Perfil.B6.v,'0012345678');
assert.strictEqual(book.Sheets.Perfil.B7.v,'03001234567');
assert.strictEqual(book.Sheets.Perfil.B8.t,'s');
assert.strictEqual(book.Sheets.Perfil.B8.f,undefined);
assert.strictEqual(book.Sheets.Actividades.D6.v,4.5);
assert.strictEqual(book.Sheets.Actividades.D6.t,'n');
assert.strictEqual(book.Sheets.Perfil.B2.v,'2026-01-13');
assert.strictEqual(book.Sheets.Perfil.D2.v,'2026-03-27');
eventHandlers.beforeprint();assert.strictEqual(details[0].open,true);
eventHandlers.beforeprint();
eventHandlers.afterprint();assert.strictEqual(details[0].open,false);
assert.strictEqual(elements['hv-export'].disabled,false);
console.log('OK: Excel types, dates, literal user text, export and print-state restoration.');
