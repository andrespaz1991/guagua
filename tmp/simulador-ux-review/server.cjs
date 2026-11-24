const http = require('node:http');
const fs = require('node:fs');
const path = require('node:path');
const root = path.resolve(__dirname, '../..');
const files = new Map([
  ['/', [path.join(__dirname,'index.html'), 'text/html; charset=utf-8']],
  ['/empty', [path.join(__dirname,'empty.html'), 'text/html; charset=utf-8']],
  ['/p4_actual.php', [path.join(__dirname,'p4-fixture.json'), 'application/json; charset=utf-8']],
  ...['style.css','reports.css','app.js','model.js'].map(file=>['/'+file,[path.join(root,'apps/simulador_notas',file),file.endsWith('.css')?'text/css':'text/javascript']]),
  ...['bootstrap.min.css','estilos_guagua.css'].map(file=>['/comun/css/'+file,[path.join(root,'comun/css',file),'text/css']]),
  ['/comun/fonts/CurseCasual.ttf',[path.join(root,'comun/fonts/CurseCasual.ttf'),'font/ttf']]
]);
http.createServer((request, response) => {
  const url = new URL(request.url, 'http://127.0.0.1:8769');
  if(url.pathname==='/favicon.ico'){response.writeHead(204);response.end();return;}
  const file = files.get(url.pathname);
  if (!file) { response.writeHead(404); response.end(); return; }
  response.writeHead(200, { 'Content-Type': file[1], 'Cache-Control': 'no-store' });
  response.end(fs.readFileSync(file[0]));
}).listen(8769, '127.0.0.1', () => console.log('QA con datos ficticios: http://127.0.0.1:8769'));
