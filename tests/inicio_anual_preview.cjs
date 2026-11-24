// Vista aislada para pruebas del navegador. Solo datos ficticios, sin base de datos.
const http=require('http');
const fs=require('fs');
const path=require('path');
const root=path.resolve(__dirname,'..');
const institution={id_institucion_educativa:7,nombre_institucion:'Institución de demostración · vista de prueba',direccion_institucion:'',telefono_institucion:'',correo_institucion:'',plantilla:1,autoregistrarse:'NO',formatos_no_permitidos:'exe,bat',tamano_maximo_adjunto:'2048'};
const catalog={institucion:institution,activo:{id_ano_lectivo:55,nombre_ano_lectivo:'2030'},periodo:{nombre_periodo:'4'},anos:[{id_ano_lectivo:55,nombre_ano_lectivo:'2030',estado:'Activo'}],grados:[{id:6,nombre:'Sexto',nivel:1},{id:7,nombre:'Séptimo',nivel:1}],materias:[{id:10,nombre:'Ciencias Naturales'},{id:11,nombre:'Matemáticas'},{id:12,nombre:'Tecnología'}],docentes:[{id:'101',nombre:'Docente de demostración'},{id:'102',nombre:'Docente de ciencias'}],estudiantes:[{id:'201',nombre:'Estudiante de ejemplo A',grado:'6'},{id:'202',nombre:'Estudiante de ejemplo B',grado:'7'}],borradores:[],puede_activar:true,usuario:'101',plantillas:[{id:1,nombre:'Plantilla principal de Guagua'}]};
const files={asistencia:'',reportes:[],asistencia_entorno:false,huellas:{asistencia:'',reportes:''}};
const drafts=new Map();
function blank(year){return{ano:year,descripcion:'',institucion:structuredClone(institution),periodos:[],grados:[],materias:[],asignaciones:[],horarios:[],estudiantes:[],fecha_inscripcion:'',archivos:structuredClone(files)}}
http.createServer(async(req,res)=>{
  const url=new URL(req.url,'http://127.0.0.1');
  const send=data=>{res.writeHead(200,{'Content-Type':'application/json'});res.end(JSON.stringify(data))};
  const action=url.searchParams.get('action');
  if(action){
    if(action==='bootstrap')return send({ok:true,catalogo:catalog,archivos:files});
    const year=Number(url.searchParams.get('ano'));
    if(action==='load')return send({ok:true,borrador:drafts.get(year)||{plan:blank(year),revision:0,paso:0,estado:'borrador',resultado:null}});
    if(action==='files')return send({ok:true,archivos:files});
    if(action==='copy'){
      const p=blank(year);p.periodos=[{nombre:'1',descripcion:'Primer tramo escolar',inicio:`${year}-01-13`,fin:`${year}-03-28`,activo:true},{nombre:'2',descripcion:'Segundo tramo escolar',inicio:`${year}-03-29`,fin:`${year}-06-30`,activo:false}];p.grados=[{...catalog.grados[0],key:'g6'}];p.materias=[{...catalog.materias[0],key:'m10'}];p.asignaciones=[{key:'a1',grado:'g6',materia:'m10',docente:'101',visible:true}];return send({ok:true,plan:p});
    }
    let body='';for await(const chunk of req)body+=chunk;const input=JSON.parse(body||'{}');
    if(action==='save'){
      const errors=[];
      if(input.validar && input.validar_hasta>=1 && !input.plan.periodos.length)errors.push('Agrega los períodos académicos.');
      const draft={plan:input.plan,revision:input.revision+1,paso:errors.length?input.validar_hasta:input.paso,estado:'borrador',resultado:null};drafts.set(input.plan.ano,draft);return send({ok:true,borrador:draft,errores:errors});
    }
    return send({ok:false,message:'La vista de prueba no instala datos reales.'});
  }
  const filename=url.pathname.endsWith('.css')?'inicio_anual.css':url.pathname.endsWith('.js')?'inicio_anual.js':null;
  if(filename){res.setHeader('Content-Type',filename.endsWith('.css')?'text/css':'text/javascript');return res.end(fs.readFileSync(path.join(root,'Planeador',filename)));}
  let html=fs.readFileSync(path.join(root,'Planeador/inicio_anual.php'),'utf8');html=html.slice(html.indexOf('<!doctype html>')).replace(/<\?(?:php|=)[\s\S]*?\?>/g,'');
  res.setHeader('Content-Type','text/html; charset=utf-8');res.end(html);
}).listen(0,'127.0.0.1',function(){console.log(`Preview: http://127.0.0.1:${this.address().port}/inicio_anual.php`)});
