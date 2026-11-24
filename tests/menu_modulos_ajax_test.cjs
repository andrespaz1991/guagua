const assert = require('node:assert/strict');
const fs = require('node:fs');
const vm = require('node:vm');
const path = require('node:path');

const source = fs.readFileSync(path.join(__dirname, '../comun/iconos.php'), 'utf8');
const script = source.match(/<script>([\s\S]*?)<\/script>/)[1];
const calls = [];
const nodes = {
  buscador: {value: 'Niñez & Educación; azul'},
  'tabla-iconos': {innerHTML: ''},
  'id_iconos': {value: '0'},
};
const context = vm.createContext({
  URL, console,
  window: {location: {href: 'http://localhost/guagua/comun/iconos.php?institucion=17&guagua_embedded=1#lista'}},
  document: {addEventListener() {}, getElementById(id) { return nodes[id]; }},
  fetch: async (url, options) => {
    calls.push({url: new URL(url), options});
    return {text: async () => JSON.stringify({exito: true, datos: [], mensaje: 'OK'})};
  },
  confirm: () => true,
  alert() {},
});

(async () => {
  vm.runInContext(script, context);
  await context.cargarIconos();
  const request = calls[0];
  assert.equal(request.url.pathname, '/guagua/comun/iconos.php');
  assert.equal(request.url.searchParams.get('guagua_embedded'), '1');
  assert.equal(request.url.searchParams.get('institucion'), '17');
  assert.equal(request.url.searchParams.get('api'), '1');
  assert.equal(request.url.searchParams.get('accion'), 'leer');
  assert.equal(request.url.searchParams.get('buscar'), nodes.buscador.value);
  assert.equal(request.url.hash, '');
  assert.match(nodes['tabla-iconos'].innerHTML, /No se encontraron iconos/);
  await context.eliminarIcono(999999999);
  assert.equal(calls[1].url.searchParams.get('accion'), 'eliminar');
  assert.equal(calls[1].url.searchParams.get('guagua_embedded'), '1');
  assert.equal(calls[1].options.method, 'POST');
  assert.equal(calls[1].options.body, '{"id":999999999}');
  const saveUrl = new URL(context.iconosApiUrl('guardar'));
  assert.equal(saveUrl.searchParams.get('accion'), 'guardar');
  assert.equal(saveUrl.searchParams.get('guagua_embedded'), '1');
  context.window.location.href = 'http://localhost/guagua/comun/iconos.php?institucion=2';
  assert.equal(new URL(context.iconosApiUrl('leer')).searchParams.get('guagua_embedded'), '1');

  const grades = fs.readFileSync(path.join(__dirname, '../cursos/categoria_curso.php'), 'utf8');
  const activeScript = grades.match(/<script>\s*(var menuCategoriaCurso[\s\S]*?)<\/script>/)[1];
  assert.doesNotThrow(() => vm.runInNewContext(activeScript, {document: {getElementById() {return null;}}}));
  console.log('OK: API de iconos conserva contexto y modo integrado; grados tolera menú externo.');
})().catch(error => { console.error(error); process.exitCode = 1; });
