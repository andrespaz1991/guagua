const http=require('http');
const fs=require('fs');
const path=require('path');
const file=path.resolve(__dirname,'../Mapa_interactivo_Guagua.html');
http.createServer((req,res)=>{
  if(req.url==='/favicon.ico'){res.writeHead(204);return res.end();}
  if(req.url.split('?')[0]!=='/'){res.writeHead(404);return res.end('Not found');}
  res.writeHead(200,{'Content-Type':'text/html; charset=utf-8','Cache-Control':'no-store'});
  res.end(fs.readFileSync(file));
}).listen(8764,'127.0.0.1',function(){console.log('Preview: http://127.0.0.1:'+this.address().port+'/');});
