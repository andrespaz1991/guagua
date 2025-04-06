
   function adicionar_dias(dias) {
 var isChecked = document.getElementById(dias).checked;
if(isChecked==false){
var elem1 = document.getElementById ('hr'+dias);
var elem2 = document.getElementById ('labeldia'+dias);
var elem3 = document.getElementById ('lb_hora_inicio'+dias);
var elem4 = document.getElementById ('hora_inicio'+dias);
var elem5 = document.getElementById ('lb_hora_fin'+dias);
var elem6 = document.getElementById ('hora_fin'+dias);
var elem7 = document.getElementById ('espacio'+dias);
elem1.parentNode.removeChild (elem1);
elem2.parentNode.removeChild (elem2);
elem3.parentNode.removeChild (elem3);
elem4.parentNode.removeChild (elem4);
elem5.parentNode.removeChild (elem5);
elem6.parentNode.removeChild (elem6);
elem7.parentNode.removeChild (elem7);
}

if(isChecked == true)
{
info=1;
 var hr = document.createElement("hr");
 document.getElementById('horario').appendChild(hr);
hr.setAttribute("style", "height: 1px;background-color: red;width:60%;margin-left:0%");
hr.setAttribute("id", "hr"+dias);

 var label_dia = document.createElement("label");
label_dia.innerHTML =dias;
  document.getElementById('horario').appendChild(label_dia);
label_dia.setAttribute("id", "labeldia"+dias);

 var espacio = document.createElement("br");
 espacio.setAttribute("id", "espacio"+dias);
  document.getElementById('horario').appendChild(espacio);
 var label_hora_inicio = document.createElement("label");
label_hora_inicio.innerHTML ="Hora inicio";
  label_hora_inicio.setAttribute("id", "lb_hora_inicio"+dias);
  document.getElementById('horario').appendChild(label_hora_inicio);
 var hora_inicio = document.createElement("INPUT");
  hora_inicio.setAttribute("type", "time");
hora_inicio.setAttribute("id", "hora_inicio"+dias);
hora_inicio.setAttribute("name", "hora_inicio[]");
  document.getElementById('horario').appendChild(hora_inicio);
 var label_hora_inicio = document.createElement("label");
label_hora_inicio.innerHTML ="Hora Fin";
  document.getElementById('horario').appendChild(label_hora_inicio);
label_hora_inicio.setAttribute("id", "lb_hora_fin"+dias);
 var hora_fin = document.createElement("INPUT");
  hora_fin.setAttribute("type", "time");
  hora_fin.setAttribute("id", "hora_fin"+dias);
  hora_fin.setAttribute("name", "hora_fin[]");
  document.getElementById('horario').appendChild(hora_fin);

}
}

function contexmenu(materia){
  $.contextMenu({
        selector: '.menu_contextual'+materia, 
        items: $.contextMenu.fromMenu($('#menu_curso'+materia))
    });
}
function ocultar_anios_no_vigentes(datos){
for (var i = 0; i <= datos.length; i++) {
d=datos[i];
info=JSON.stringify(d);
if(info !=undefined){
arreglo =JSON.parse(info);
arreglo2 =JSON.parse(JSON.stringify(arreglo));
mitoogle('#id_'+arreglo2['id_ano_lectivo']);
}

}

}


function adicionar(grupo,info="",actualizar="no"){
       var next = parseInt(  $("#count"+grupo).val());
        var addto = "#"+grupo + next;
        var addRemove = "#"+grupo + (next);
        next = next + 1;
        var newIn = '<input value="'+info+'" autocomplete="off" class="input form-control" id="'+grupo+ next + '" name="'+grupo+'[]" type="text">';
        var newInput = $(newIn);
        var removeBtn = '<button  id="remove'+grupo+ (next-1) + '" class="btn btn-danger" onclick="remover(this.id);" >-</button ></div><div id="'+grupo+'">';
        var removeButton = $(removeBtn);
        $(addto).after(newInput);
        $(addRemove).after(removeButton);
        $("#"+grupo+ next).attr('data-source',$(addto).attr('data-source'));
          $("#count"+grupo).val(next);     
          if(actualizar=="si"){
var input = ["contenido1", "objetivos1", "estrategia1","Recurso1","Actividad1","estrategiaplanb1","Actividadplanb1","Recursosplanb1"];
input.forEach( function(valor, indice, array) {
$("#"+valor).remove();  
});
          }

         }
function remover(parametroesperado){
$("#"+parametroesperado).remove();  
parametroesperado = parametroesperado.replace('remove', '');
$("#"+parametroesperado).remove();  
}


function validar_intentos_fallidos(tiempo_en_segundos){
alert2('Muchos intentos fallidos :( \n Debes esperar durante'+tiempo_en_segundos+ ' segundos ','');
 setTimeout("location.href='../index.php'",tiempo_redireccionamiento)
}



function limpiar_mascota(){
    var marcado2 = $("#mascota").prop("checked") ? true : false;
if (marcado2==false){
       document.getElementById('clave').value='';
}
}
function verificar_requerido (chk,input){
var marcado = $("#"+chk).prop("checked") ? true : false;
if(marcado==true){$('#'+input).attr('required','');}
else { $('#'+input).removeAttr("required");
    $('#mascota').click();
}   
}

function span_img(){
document.getElementById('span_img').innerHTML='<input class="form-control"name="foto"type="file" id="foto" required >';
}
function consultar_asignaciones (){
var formData = $("#ano_lectivo").val();

     $.ajax({
	type:'POST',
	 dataType: "json",
	url: 'prueba.php',
	data:{
	datosw: formData,	
	 complete  : function(json) {
      console.log('Completa'+json);
    },
		}
})
.done(function(respuesta){
alert(respuesta);
//	$('#txtsugerencias').text(respuesta);
});	
}

  $(document).ready(function(){
        $("#frmRestablecer").submit(function(event){
          event.preventDefault();
          $.ajax({
            url:'validaremail.php',
            type:'post',
            dataType:'json',
            data:$("#frmRestablecer").serializeArray()
          }).done(function(respuesta){
            $("#mensaje").html(respuesta.mensaje);
            $("#email").val('');
          });
        });
      });


function launchFullscreen(elem){
    if (elem.requestFullscreen) {
  elem.requestFullscreen();
} else if (elem.msRequestFullscreen) {
  elem.msRequestFullscreen();
} else if (elem.mozRequestFullScreen) {
  elem.mozRequestFullScreen();
} else if (elem.webkitRequestFullscreen) {
  elem.webkitRequestFullscreen();
}

}


function PlaceholderBusquedaActividades (){
var combo = document.getElementById("menu_actividad");
var selected = combo.options[combo.selectedIndex].text;        
        console.log(selected);
if(selected!='Fecha Entrega'){document.getElementById('actividad_curso').type='text'; document.getElementById('actividad_curso').value='';  }
if(selected=='Observaciones'){document.getElementById('actividad_curso').placeholder='Los Estudiantes...';   }
if(selected=='Periodo'){document.getElementById('actividad_curso').placeholder='1';   }
if(selected=='adjunto'){document.getElementById('actividad_curso').placeholder='SI/NO';   }
if(selected=='Visible'){document.getElementById('actividad_curso').placeholder='SI/NO';   }
if(selected=='evaluable'){document.getElementById('actividad_curso').placeholder='SI/NO';  }
if(selected=='Fecha Entrega'){document.getElementById('actividad_curso').type='date';  }
if(selected=='Cuestionario'){document.getElementById('actividad_curso').placeholder='SI/NO';  }
if(selected=='Foro'){document.getElementById('actividad_curso').placeholder='SI/NO';  }


    
    
}




function verificar_evaluable(chek){
  var marcado = $("#checkbox").prop("checked") ? true : false;
  if(marcado == true){
 document.getElementById("fecha_entrega").setAttribute("required", true);
 document.getElementById("hora_entrega").setAttribute("required", true);

       $("#cuestionario").click();

    $("#adjunto").click();
document.getElementById('eval').style.display='';

  }
  else {
 document.getElementById('label-tab-3').style.display = "none"; 
$("#cuestionario").prop('checked', false); 

       $("#adjunto").click();
      $('#fecha_entrega').val('0000-00-00');
$('#id_cuestionario').val('');
   $('#hora_entrega').val('--:-- -----');
document.getElementById("fecha_entrega").removeAttribute("required");
document.getElementById("hora_entrega").removeAttribute("required");

  }
   
}


function required_en_formulario(id_formulario,color,elemento){
$("#"+id_formulario).find(':input').each(function() {
if(this.type!="hidden" && this.type!="button" ){
var estado = document.getElementById(this.id);
if(estado && estado.required==true){ $("#"+this.id).before("<font color='"+color+"'>"+elemento+"</font>");  }  
}
  
  });  

}
function password_en_formulario(id_formulario){
$("#"+id_formulario).find(':input').each(function() {
if(this.type=="password"){
  $("#"+this.id).after("<label><input type='checkbox' onclick=\"document.getElementById('"+this.id+"').type = document.getElementById('"+this.id+"').type == 'text' ? 'password' : 'text'\"> Ver</label>");
}
});
}
function imprSelec(nombreDiv) {
     var contenido= document.getElementById(nombreDiv).innerHTML;
     var contenidoOriginal= document.body.innerHTML;

     document.body.innerHTML = contenido;

     window.print();

     document.body.innerHTML = contenidoOriginal;
}
function dimension_portada(){
    $(document).ready(function($){
	var div_ancho = $("#portada").width();
	var div_alto = $("#portada").height();
});  
return 
}


function ObtenerGetJavascript(variable) {
   var query = window.location.search.substring(1);
   var vars = query.split("&");
   for (var i=0; i < vars.length; i++) {
       var pair = vars[i].split("=");
       if(pair[0] == variable) {
           return pair[1];
       }
   }
   return false;
}

function colorvaloracion(nota){
    if(nota>0 & nota <3){
        return color = "#FF0000" ;
    }
    
    if(nota >= 3 && nota <4){
        return color = "#F57A2D" ;
    }
    
    if(nota >= 4 && nota <=5){
        return color = "#01DF01" ;
    }
    
}

function ColorAleatorio() {
    var letras = '0123456789ABCDEF'; 
    var color = '#'; 
    for (var i = 0; i < 6; i++ ) {
        color += letras[Math.floor(Math.random() * 16)]; 
    }
    return color; 
}
function focoared(){
    $("#busqueda").focus();
} 
function ocultar_quitar_seleccion(){
document.getElementById('quitar_Seleccion').style.visibility='hidden';
}

function limpiar_red (){
        document.getElementById('id_red').value="";
          document.getElementById('busqueda').value=""; 
          document.getElementById('rednombre').innerHTML ='';
          
  }
function ver_curso(curso){
var estado_actual = curso.getAttribute("visible");
var idcurso= curso.getAttribute("id_curso");
var texto = document.getElementById("texto_curso_"+idcurso);
var textodocente = document.getElementById("texto_docentecurso_"+idcurso);
var iconomateria = document.getElementById("iconomateria_"+idcurso);

if(estado_actual=="SI"){
  var  estado_enviado="NO";
}
if(estado_actual=="NO"){
  var  estado_enviado="SI";
}
//var estado_enviado = estado_actual=="SI" ? "NO" : "SI";
ajax=nuevoAjax();
ajax.open("POST","?estado_curso",true);
ajax.setRequestHeader("Content-type","application/x-www-form-urlencoded");
	ajax.onreadystatechange=function() {
		if (ajax.readyState==4) {
		var respajax = ajax.responseText;
curso.setAttribute("visible",respajax);
 if(respajax=="SI"){
     curso.className = "icon-sga-view";
     curso.title ="Ocultar";
     texto.style.color="black";
      textodocente.style.color="black";
iconomateria.style='-webkit-filter: grayscale(0);filter:gray;margin-left: auto;      margin-right: auto;border:none';
     
 }
 if(respajax=="NO"){
 curso.className = "icon-sga-view-line";
curso.title ="Mostrar";
texto.style.color="gray";
      textodocente.style.color="gray";
iconomateria.style='-webkit-filter: grayscale(1);filter:gray; display: block;margin-left: auto;      margin-right: auto;border:none;';

 }
    //    curso.className = respajax=="SI" ? "icon-sga-view" : "icon-sga-view-line";
     //   curso.title = respajax=="SI" ? "Ocultar" : "Mostrar";
     //   if (respajax=="SI") texto.style.color="black";
      //  if (respajax=="NO") texto.style.color="gray";
		}
	}
ajax.send("id_curso="+idcurso+"&estado_curso="+estado_enviado);
}
function ver_tema(tema){
var estado_actual = tema.getAttribute("visible");
var idtema= tema.getAttribute("id_tema");

if(estado_actual=="Publicado"){
  var  estado_enviado="Desactivado";
}
if(estado_actual=="Desactivado"){
  var  estado_enviado="Publicado";
}

ajax=nuevoAjax();
ajax.open("POST","?estado_tema",true);
ajax.setRequestHeader("Content-type","application/x-www-form-urlencoded");
	ajax.onreadystatechange=function() {
		if (ajax.readyState==4) {
		var respajax = ajax.responseText;
tema.setAttribute("visible",respajax);
 if(respajax=="Publicado"){
     tema.className = "icon-sga-view";
     tema.title ="Ocultar";
     $("#areatema_"+idtema).removeClass("areatema_oculta");
 }
if(respajax=="Desactivado"){
    tema.className = "icon-sga-view-line";
    tema.title ="Mostrar";
    $("#areatema_"+idtema).addClass("areatema_oculta");
}
		}
	}
ajax.send("id_tema="+idtema+"&estado_tema="+estado_enviado);
}

function ocultar_descripcion_curso(){
if( $('#parrafo_descripcion_curso').is(":visible") ){
            $("#parrafo_descripcion_curso").toggle(800);
}
else{
                $("#parrafo_descripcion_curso").toggle(800);

}

}
function actividades_por_periodo(chk){
var periodo = chk.getAttribute("class") ; 
    $("[id*=periodo"+periodo+']').toggle(800);
}

  
  
function ver_actividad(actividad){
var estado_actual = actividad.getAttribute("visible");
var idactividad = actividad.getAttribute("id_actividad");
var texto = document.getElementById("texto_activiad_"+idactividad);
var imagen_actividad = document.getElementById("imagen_actividad"+idactividad);


var estado_enviado = estado_actual=="SI" ? "NO" : "SI";
ajax=nuevoAjax();
ajax.open("POST","?estado_visible",true);
ajax.setRequestHeader("Content-type","application/x-www-form-urlencoded");
	ajax.onreadystatechange=function() {
		if (ajax.readyState==4) {
		var respajax = ajax.responseText;
  		actividad.setAttribute("visible",respajax);
        actividad.className = respajax=="SI" ? "icon-sga-view" : "icon-sga-view-line";
        actividad.title = respajax=="SI" ? "Ocultar" : "Mostrar";
        if (respajax=="NO") {
            document.getElementById('ver_actividad_'+idactividad).style='margin-left:20px;margin-top:0px;';
                texto.style.color="gray";imagen_actividad.style='-webkit-filter: grayscale(100%);filter:gray;margin-left:auto;margin-right:auto;border:none;';

        }
        if (respajax=="SI"){ 
        texto.style.color="black";imagen_actividad.style='-webkit-filter: grayscale(15%);filter:gray;margin-left:auto;margin-right:auto;border:none;';
            document.getElementById('ver_actividad_'+idactividad).style='margin-left:20px;margin-top:0px;';

            
        } 
		}
	}
ajax.send("id_actividad="+idactividad+"&estado_visible="+estado_enviado);
}
function ocultar_descripcion_curso(){
if( $('#parrafo_descripcion_curso').is(":visible") ){
            $("#parrafo_descripcion_curso").toggle(800);
}
else{
                $("#parrafo_descripcion_curso").toggle(800);

}

}
function actividades_por_periodo(chk){
var periodo = chk.getAttribute("class") ; 
    $("[id*=periodo"+periodo+']').toggle(800);
}


function mostrarcuestionar(){
     if( $('#checkbox_cuestionario').prop('checked') ) {

 document.getElementById('label-tab-3').style.display = "none"; 
}
}
function verificar_red(estado){
    if( $('#checkbox2').prop('checked') ) {
        document.getElementById('label-tab-2').style.display = "none"; 
        
}
else {
    document.getElementById('id_red').value ='';
}
}
function obtenerCookie(cname) {
    var name = cname + "=";
    var decodedCookie = decodeURIComponent(document.cookie);
    var ca = decodedCookie.split(';');
    for(var i = 0; i <ca.length; i++) {
        var c = ca[i];
        while (c.charAt(0) == ' ') {
            c = c.substring(1);
        }
        if (c.indexOf(name) == 0) {
            return c.substring(name.length, c.length);
        }
    }
    return "";
}
function getFileExtension(filename) {
  return filename.split('.').pop();
}
function ValidarArchivo(objeto){
if (objeto.type=="file"){
formatos = obtenerCookie('tipos');
 tamano = obtenerCookie('tamano');
    var archivo = objeto.files[0];
    var nombrearchivo = archivo.name;
    extension = getFileExtension(nombrearchivo);
var comprobacion = formatos.indexOf(extension);
    if (comprobacion>0) {
alert2('Formato '+extension+' por seguridad no permitido, igual que '+formatos);
     objeto.value ='';
    }
    var tamano = (archivo.size/1000000); 
                 if (tamano> tamano)
            {
    alert2('Su archivo tiene un tamaño de '+tamano +' MB \n y no puede exceder ' +(tamano/1024) +'  MB ');
    objeto.value ='';
             }
}
}
function validar_resolucion(objeto,ancho='1020',alto='124'){
if (objeto.type=="file"){
extension = getFileExtension(objeto.files[0].name) ;
var formato_imagen = ["jpg", "png", "BMP", "BMP","GIF","JPEG","TIFF","TIF"];
var comprobacion =formato_imagen.indexOf(extension);
    var archivo = objeto.files[0];
var arc = archivo.width;
    if(comprobacion==0){
  var div_ancho = ancho;
	var div_alto = alto;
  var div_ancho_portada = $("#subirportada").width();
  var div_alto_portada = $("#subirportada").height();
         if (objeto.width.toFixed(0) > div_ancho && objeto.height.toFixed(0) > div_alto) {
                alert2('La imagen debe ser menores a: '+div_ancho+' * '+div_alto +'');
                     objeto.value ='';
            }
    }
    
}
}
//revisar
/*
function checkSize(input){
    var max_img_size_input = document.getElementById("MAX_FILE_SIZE");
    if(max_img_size_input){
    var max_img_size = max_img_size_input.value;
    // check for browser support (may need to be modified)
    //console.log(input.files[0].size);
    if(input.files && input.files.length == 1)
    {           
        if (input.files[0].size > max_img_size) 
        {
            alert2("El archivo deberia ser menor a " + (max_img_size/1024/1024) + "MB");
            input.value= "";
            return false;
        }
    }

    return true;
    }else{
    	alert2("Error, el formulario no contiene un campo MAX_FILE_SIZE que indique el tamaño máximo del adjunto");
    return false
    }
}
*/
function mostrarImagen(input) {
//Objetivo: Vista previa de la imágen antes de subirla
//Parametro: el objeto input
//require un objeto con la id "img_"+input.id
 if (input.files && input.files[0]) {
  var reader = new FileReader();
  reader.onload = function (e) {
   $('#img_'+input.id).attr('src', e.target.result);
  }
  reader.readAsDataURL(input.files[0]);
 }
}
function toDataURL(url, callback) {
  var xhr = new XMLHttpRequest();
  xhr.onload = function() {
    var reader = new FileReader();
    reader.onloadend = function() {
      callback(reader.result);
    }
    reader.readAsDataURL(xhr.response);
  };
  xhr.open('GET', url);
  xhr.responseType = 'blob';
  xhr.send();
} 
function sinfoto(){
	var text = '<input style="display:none" name="foto_old" type="file" id="foto_old">';
	text += '<br><input style=\'color:#fff\' class=\'btn btn-info\' type="button" value="Agregar Foto" onclick="document.getElementById(\'span_img_foto\').innerHTML=\'<input style=\\\'width: 90%;display: inline;\\\'name=foto type=file onchange=\\\'ValidarArchivo(this);validar_resolucion(this);mostrarImagen(this)\\\' id=foto required ><button class=\\\'btn-danger badge\\\' style=background-color:#d9534f; onclick=sinfoto(); type=button title=\\\'Sin Foto\\\' class=close >-</button>\';">';
	document.getElementById('span_img_foto').innerHTML=text;
	document.getElementById('img_foto').src="";
	$("div[role=tooltip]").remove();
}
//fin revisar
function elegir_cuenta(usuario){
document.getElementById('usuario').value=usuario;
$('.mascotas_login').hide();
console.log();
document.getElementById('clave');
clave.type="password";
document.getElementById('ingresar').style.visibility='';
document.getElementById('clave').value="";
document.getElementById('usuario').focus();
document.getElementById('usuarios_recordados').style.display="block";
document.getElementById('imgs_login').style.display="none";
}

function verificar_sonido(){
var mis_sonidos = obtenerCookie('sonidos');
//alert(mis_sonidos);
if (mis_sonidos=="true"){
document.getElementById('sonidos').setAttribute("checked","true");
document.getElementById('icono_sonidos').className = 'icon-sga-speaker-sga';
}else{
    document.getElementById('icono_sonidos').className = 'icon-sga-mute';
}

}

function grabarcookie(id,valor,tiempo=0,tipo=false){

if (tiempo!=0){
    if (tipo==true){
    var d = new Date();
    d.setTime(d.getTime() + (tiempo*24*60*60*1000));
    var expires = "expires="+ d.toUTCString();
document.cookie = id + "=" + valor + ";" + expires + ";path=/";
}else if (tipo==false){
document.cookie = id + '='+valor+';max-age='+tiempo+';';
}
}else{
document.cookie=id+"="+valor;
}
}
function selecciona_icono(icono){
    document.getElementById("icono").className = icono.className;
    document.getElementById("icon").value = icono.className;
}
  function loadframe(url){
        document.getElementById('miframe').src=url;
    }
 function selecciona_icono(icono){
    document.getElementById("icono").className = icono.className;
     document.getElementById("icon").value = icono.className;
}
function obtener_icono2(icono){

    document.getElementById("icono").className = icono.className;
     document.getElementById("icon").value = icono.className;
}
function obtener_icono_mascota(icono){
	var datasrc = icono.getAttribute('data-src');
	var dataid = icono.getAttribute('data-id');
    document.getElementById("clave").value = dataid;
    document.getElementById("icon-img").src = datasrc;
    document.getElementById("cerrar_modal_avatar").click();
}
function Insertarredconajax(){
       $(function(){
     //   $("#guardarred").on("click", function(){
        
            var formData = new FormData($("#form_nuevo_red")[0]);
           var ruta = "../comun/prueba_query.php";
            $.ajax({
                url: ruta,
                type: "POST",
                data: formData,
                contentType: false,
                processData: false,
                beforeSend: function() {
document.getElementById('mensajedeespera').innerHTML = 'guardando...';
document.getElementById('guardarred').style.display='none';
                },success: function(datos)
                {
document.getElementById('guardarred').style.display='';

    //$("#micheckbox").prop("checked", "");
	$("#uni").show(800);
   $("#nuevo").hide(800);
   document.getElementById('busqueda').focus();

   if (datos.indexOf("Correcto") != -1) {
	alert2('Recurso Educativo Digital subido con exito');
	$("#micheckbox_no").click();
	$("#uni").show(800);
   $("#nuevo").hide(800);
} else{
	alert2('Verificar su información','danger');
}

					//console.log(datos);
                 }
            });
        });
   }
   
  
    
function hola_mundo(){
            var formData = new FormData($("#formulario")[0]);
            var ruta = "../../cursos/imagen-ajax.php";
            $.ajax({
                url: ruta,
                type: "POST",
                data: formData,
                contentType: false,
                processData: false,
                beforeSend: function() {
                $("#guardarimage").hide();
                $("#respuesta").html('Cargando ...');
                },success: function(datos)
                {
//                    $("#respuesta").html(datos);
					console.log(datos);
                    if(datos=='1'){
                        alert3('Su archivo ha sido guardado correctamente');

                    }else{
                        alert3('Por favor verifique información y vuelva a intentar','warning');
                    }
                $("#guardarimage").show();
                $("#respuesta").html('');
                }
     
     });
   }
     




/* Funciones con Red  */


function mostrarSugerenciaiconos(textoBusqueda=""){
//console.log(textoBusqueda);
if (textoBusqueda=="") textoBusqueda = $("#busqueda").val();
        $.post("?mostrarSugerenciaiconos", {valorBusqueda: textoBusqueda}, function(mensaje) {
            $("#resultadoBusqueda").html(mensaje);
        }); 
     ;
};


 function mostrarSugerenciaRed(textoBusqueda=""){
var textoBusqueda = $("input#busqueda").val();
        $.post("listado_red.php", {valorBusqueda: textoBusqueda}, function(mensaje) {
            $("#resultadoBusqueda").html(mensaje);
        }); 
     ;
};



function  tipoinput(valor){
if (valor == 'si'){document.getElementById('enlace').type = 'file';  }
else{document.getElementById('enlace').type = 'text';}
}
function revisar_vermas(){
   if($("#vermas").is(':checked')) { 
        $("#toogle").show(800);
      } 
                else{
      $("#toogle").hide(800);
                }
}  

function rrecorrer_periodos(){
    var selected = [];
$('input:checked').each(function() {

if (this.id=="checkbox1"){
   this.checked=1 ;
   actividades_por_periodo(this);
}
else{
    this.checked=0 ;
       actividades_por_periodo(this);

}




    
});
//alert(selected);

}


function  tipoinput(valor){
if (valor == 'si'){
document.getElementById('enlace').type = 'file';
  }
else{
document.getElementById('enlace').type = 'text';

}
  
}
/* Fin Funciones con Red  */


function ocultar_toogle(){
   $("#toogle").hide(800);
}
function  tipoinput(valor){
if (valor == 'si'){
document.getElementById('enlace').type = 'file';
  }
else{
document.getElementById('enlace').type = 'text';

}
  
}
/* 22/02/2017 */
function obtener_red(icono){
var color_Contenedor_red="grey";   
    var $li = $('#container li').click(function() {
    $li.removeClass('selected');
    $(this).addClass('selected');
});
var Contenedor_red =icono.id.toString().substring(8);
$('li').css('background-color', '')
document.getElementById('li'+Contenedor_red).style.backgroundColor = color_Contenedor_red;
document.getElementById('rednombre').innerHTML="<strong>Seleccionó</strong> "+icono.getAttribute('data-nombre');
document.getElementById("id_red").value = icono.getAttribute('data-id');
document.getElementById('quitar_Seleccion').style.visibility='visible';
var mired = document.getElementById("red");
if(mired) mired.className = icono.className;
}
function buscar_cuestionario(nombre=""){
if (nombre == ""){
	nombre = document.getElementById('txt_buscar_cuestionario').value;
}
ajax=nuevoAjax();
ajax.open("POST","?buscar_cuestionario",true);
ajax.setRequestHeader("Content-type","application/x-www-form-urlencoded");
	ajax.onreadystatechange=function() {
		if (ajax.readyState==4) {
		document.getElementById('txtbuscar_cuestionario').innerHTML = ajax.responseText;
		}
	}
ajax.send("datos="+nombre);
elegirframe("");
setTimeout(function(){ refrescar_id_cuestionario(); }, 3000);
}
function revisar_tareas(id_estudiante,nombre="",id_cuestionario=""){
revisar_cuestionario(id_cuestionario, id_estudiante);
revisar_adjunto(id_estudiante,nombre);
}

function revisar_adjunto(id_estudiante,nombre=""){
if (id_estudiante != ""){

ajax_adj=nuevoAjax();
ajax_adj.open("POST","?revisar_adjunto",true);
ajax_adj.setRequestHeader("Content-type","application/x-www-form-urlencoded");
	ajax_adj.onreadystatechange=function() {
		if (ajax_adj.readyState==4) {
		    var resp_adj = ajax_adj.responseText;
		document.getElementById('txtrevisar_adjunto').innerHTML = resp_adj;
		}
	}
ajax_adj.send("id_estudiante="+id_estudiante+"&nombre="+nombre);
}
}
function revisar_cuestionario(id_cuestionario, id_estudiante){
if (id_estudiante != ""){
ajax_cue=nuevoAjax();
ajax_cue.open("POST","?revisar_cuestionario",true);
ajax_cue.setRequestHeader("Content-type","application/x-www-form-urlencoded");
	ajax_cue.onreadystatechange=function() {
		if (ajax_cue.readyState==4) {
		var respuesta_cue = ajax_cue.responseText;
		//alert (respuesta_cue);
		document.getElementById('txtrevisar_cuestionario').innerHTML = respuesta_cue;
		}
	}
ajax_cue.send("id_cuestionario="+id_cuestionario+"&id_estudiante="+id_estudiante);
}
}
function revisar_chk(obj,id){
//alert($("#"+obj).is(':checked'));
  if($("#"+obj).is(':checked')) { 
  //var an = $('#fechas').toggle(showOrHide)
  	//alert (an);
      //     $("#fechas").toggle();
        $("#"+id).show(800);
      } 
                else{
         //  $("#fechas").toggle();
                  $("#"+id).hide(800);
                }
}/* 22/02/2017 */


function checkDecimals(fieldName, fieldValue)
{
decallowed = 2;
if (isNaN(fieldValue) || fieldValue == "")
{
alert2("No es un number.try valida de nuevo.");
fieldName.select();
fieldName.focus();
}
else
{
if (fieldValue.indexOf('.') == -1) fieldValue += ".";
dectext = fieldValue.substring(fieldValue.indexOf('.')+1, fieldValue.length);
if (dectext.length > decallowed)
{
alert ("Introduzca un numero con un maximo de " + decallowed + "decimales. vuelve a intentarlo.");
fieldName.select();
fieldName.focus();
}
else
{
alert ("Numero validado con exito.");
}
}
}
//inicio confirmar eliminar
function confirmeliminar(page,params,tit,motivo="Esta ud seguro que quiere eliminar el registro") {
	if (confirm("¿"+motivo+" "+tit+"?")){ var body = document.body;
  form=document.createElement('form'); 
  form.method = 'POST'; 
  form.action = page;
  form.name = 'jsform';
  for (index in params)
  {
		var input = document.createElement('input');
		input.type='hidden';
		input.name=index;
		input.id=index;
		input.value=params[index];
		form.appendChild(input);
  }	  		  			  
  body.appendChild(form);
  form.submit();
	}
}
function if_confirm_swal(mensaje,fn_true = 'true',fn_false = 'false',boton_verdadero = "Aceptar",boton_falso = "Cancelar",tipo='warning',titulo = "Guagua",params=''){
    //console.log(fn_true);
  swal({
  title: titulo,
  text: mensaje,
  type: tipo,
  showCancelButton: true,
  confirmButtonColor: "#3c763d",
  confirmButtonText: boton_verdadero,
  cancelButtonText: boton_falso,
  closeOnConfirm: true,
  closeOnCancel: true,
  showLoaderOnConfirm: true
},function(isConfirm){
    if(isConfirm){
      eval (fn_true);
    }else{
      eval (fn_false);
    }
});
}

function confirmeliminar2(page,params,tit,motivo="Esta ud seguro que quiere eliminar el registro") {
    var params2 = JSON.stringify(params);
    if_confirm_swal("¿"+motivo+" "+tit+"?",'enviar_formulario_post(\''+page+'\','+params2+');','false',boton_verdadero = "Confirmar",boton_falso = "Cancelar",tipo='error',titulo = "Guagua");
}
function enviar_formulario_post(page,params){
  var body = document.body;
  form=document.createElement('form'); 
  form.method = 'POST'; 
  form.action = page;
  form.name = 'jsform';
  for (index in params)
  {
	var input = document.createElement('input');
	input.type='hidden';
	input.name=index;
	input.id=index;
	input.value=params[index];
	form.appendChild(input);
  }
  body.appendChild(form);
  //console.log(form);
  form.submit();
}

function objeto_arrastrarysoltar(class_envia,class_recibe){
   $( function() {
    $( "."+class_envia).draggable({
        helper: 'clone'
    });
    $( "."+class_recibe ).droppable({
     drop: function( event, ui ) {
        var id_docente = ui.draggable.find('.id_docente_draggable').html();
        var actual = $( this ).find('.docente_droppable').html();
        var id_asignacion = $( this ).attr('id_asignacion');
        obj_drop = $( this );
        swal({
  title: "Asignar Docente",
  text: "Cambiar a "+actual+" por "+ui.draggable.find('.docente_draggable').html()+"("+id_docente+") en "+$( this ).find('.materia_droppable').html(),
  type: "warning",
  showCancelButton: true,
  confirmButtonColor: "#3c763d",
  confirmButtonText: "Si, Asignar docente!",
  cancelButtonText: "No, No hacer cambios",
  closeOnConfirm: true,
  closeOnCancel: true,
  showLoaderOnConfirm: true
},
function(isConfirm){
  if(isConfirm){
      obj_drop
         .find('.docente_droppable')
         .html(ui.draggable.find('.docente_draggable').html());
         var id_docente = ui.draggable.find('.id_docente_draggable').html();
         cambiar_docente_asignacion(id_asignacion,id_docente);
        
  }
});
  }
    });
  } );
}
//fin confirmar eliminar
function nuevoAjax(){
var xmlhttp=false;
try {

htmlhttp=new activeXObject("Msxml2.XMLHTTP");
}
catch (e) {

 try {htmlhttp=new activeXObject("Microsoft.XMLHTTP");
}
catch (e) {

xhtmlhttp=false;
}
}
if (!xmlhttp && typeof XMLHttpRequest!='undefineded'){
xmlhttp=new XMLHttpRequest();
}
return xmlhttp;
}
function enviarFormulario(url, formid, respuesta="contenido_ajax"){
    var peticion = nuevoAjax();
        var Formulario = document.getElementById(formid); 
        var longitudFormulario = Formulario.elements.length; 
        var cadenaFormulario = ""; 
        var sepCampos; 
        sepCampos = ""; 
        for (var i=0; i <= Formulario.elements.length-1;i++) { 
            cadenaFormulario += sepCampos+Formulario.elements[i].name+'='+encodeURI(Formulario.elements[i].value); 
            sepCampos="&"; 
    } 
    peticion.open("POST", url, true); 
    peticion.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded; charset=ISO-8859-1'); 
    peticion.send(cadenaFormulario); 
    peticion.onreadystatechange = function() { 
          if (peticion.readyState == 4 && (peticion.status == 200 || window.location.href.indexOf ("http") == - 1)){ 
                //alert('2'); 
                document.getElementById(respuesta).innerHTML = peticion.responseText; 
          } 
    } 
}
function buscarred(nombre=""){
alert3('hola mundo');
}
// Inicio función buscarred
function buscar_red_ajax(nombre){
ajax=nuevoAjax();
var campo=$("#campo_red").val();
if(campo!="fecha"){
$('#txt_buscar_red').attr("type", "text");
$('#txt_buscar_red').attr("val", "");
$('#txt_buscar_red').click;

}

if(campo=="titulo_Red"){
$('#txt_buscar_red').attr("type", "text");
$('#txt_buscar_red').attr("placeholder", "Eje:Naturaleza");
     }
if(campo=="nombre_materia"){
    $('#txt_buscar_red').attr("type", "text");
$('#txt_buscar_red').attr("placeholder", "Eje:Matemáticas");
     }
if(campo=="palabras_clave"){
        $('#txt_buscar_red').attr("type", "text");

$('#txt_buscar_red').attr("placeholder", "Eje:Libertad");
     }
if(campo=="nivel_eductivo"){
      $('#txt_buscar_red').attr("type", "number");
$('#txt_buscar_red').attr("placeholder", "Eje:1");



     }
if(campo=="descripcion"){
            $('#txt_buscar_red').attr("type", "text");
$('#txt_buscar_red').attr("placeholder", "Eje:Lectura comprensiva..");
     }
if(campo=="nombre"){
            $('#txt_buscar_red').attr("type", "text");

$('#txt_buscar_red').attr("placeholder", "Eje: Martin Dougiamas");
     }
if(campo=="scorm"){
$('#txt_buscar_red').attr("placeholder", "Eje: SI");
     }
if(campo=="formato"){
$('#txt_buscar_red').attr("type", "text");
$('#txt_buscar_red').attr("placeholder", "Eje:pdf");
     }
if(campo=="adjunto"){
$('#txt_buscar_red').attr("type", "text");
$('#txt_buscar_red').attr("placeholder", "Eje: SI");
     }
if(campo=="dificultad"){
$('#txt_buscar_red').attr("type", "number");

$('#txt_buscar_red').attr("placeholder", "Eje:1-5");
     }
if(campo=="cantidad_estrellas"){
$('#txt_buscar_red').attr("type", "number");
$('#txt_buscar_red').attr("placeholder", "Eje:5");
     }
if(campo=="fecha"){
$('#txt_buscar_red').attr("type", "date");
$('#txt_buscar_red').attr("placeholder", "Eje:24-12-2017");
     }
if(campo=="id_red"){
    $('#txt_buscar_red').attr("type", "number");

$('#txt_buscar_red').attr("placeholder", "Eje:12");
     }
if(campo=="idioma_red"){
    $('#txt_buscar_red').attr("type", "text");

$('#txt_buscar_red').attr("placeholder", "Eje:Español");
     }
if(campo=="autor"){
    $('#txt_buscar_red').attr("type", "text");

$('#txt_buscar_red').attr("placeholder", "Eje:Martin Dougiamas");
     }
if(campo=="tipo_interacción"){
        $('#txt_buscar_red').attr("type", "text");

$('#txt_buscar_red').attr("placeholder", "Eje:active(interactivo),expositive(pasivos),mixed(ambos),undefined(no definido)");

     }
if(campo=="tipo_recurso_educativo"){

        $('#txt_buscar_red').attr("type", "text");

$('#txt_buscar_red').attr("placeholder", "Eje:Tabla,figura,diapositiva");
     }
     
//$("#campo_red option:selected").text();
ajax.open("POST","?buscar_red="+nombre+"&campo="+campo,true);
ajax.setRequestHeader("Content-type","application/x-www-form-urlencoded");   
	ajax.onreadystatechange=function() {
		if (ajax.readyState==4) {
var respuesta = ajax.responseText;
var span_red = document.getElementById('span_buscar_red');
if (span_red){
span_red.innerHTML='';
span_red.innerHTML = respuesta;
}
/*
$("#txt_buscar_red").click();
console.log(respuesta);
*/
		}
	}
ajax.send("datos="+nombre);
}
function getParameterByName(name) {
    name = name.replace(/[\[]/, "\\[").replace(/[\]]/, "\\]");
    var regex = new RegExp("[\\?&]" + name + "=([^&#]*)"),
    results = regex.exec(location.search);
    return results === null ? "" : decodeURIComponent(results[1].replace(/\+/g, " "));
}
function buscar_actividad_curso (nombre){
    ajax=nuevoAjax();
var campo=$("#menu_actividad").val();
if(campo=="nombre_actividad"){
            $('#actividad_curso').attr("type", "text");
$('#actividad_curso').attr("placeholder", "Eje:Taller 1");
     }
if(campo=="adjunto"){
            $('#actividad_curso').attr("type", "text");
$('#actividad_curso').attr("placeholder", "Eje:SI/NO");
     }    
var asignacion = getParameterByName("asignacion");

ajax.open("POST","?actividad_curso="+nombre+"&campo="+campo+"&asignacion="+asignacion,true);
ajax.setRequestHeader("Content-type","application/x-www-form-urlencoded");   
	ajax.onreadystatechange=function() {
		if (ajax.readyState==4) {
var respuesta = ajax.responseText;
document.getElementById('actividad_curso').focus();
document.getElementById('span_actividad_curso').innerHTML = respuesta;
	//console.log(respuesta);
		}
	}
ajax.send("datos="+nombre);
}


function buscar_mis_cursos(nombre=""){
if (nombre=="") nombre = document.getElementById('buscarcurso').value;
ajax=nuevoAjax();
ajax.open("POST","?buscar_mis_cursos="+nombre,true);
ajax.setRequestHeader("Content-type","application/x-www-form-urlencoded");   
	ajax.onreadystatechange=function() {
		if (ajax.readyState==4) {
var respuesta = ajax.responseText;

document.getElementById('span_buscar_mis_cursos').innerHTML = respuesta;
	//console.log(respuesta);
	cargar_drag_and_drop_asignacion();
		}
	}
var campo = document.getElementById('campo_cursos').value;
ajax.send("datos="+nombre+"&campo="+campo);
}




function buscar_red(nombre=""){
  
if (nombre == ""){
	nombre = document.getElementById('txt_buscar_red').value;
//alert(nombre);
}
ajax=nuevoAjax();
ajax.open("POST","?buscarred",true);
ajax.setRequestHeader("Content-type","application/x-www-form-urlencoded");
	ajax.onreadystatechange=function() {
		if (ajax.readyState==4) {
document.getElementById('txt_buscar_red').innerHTML = ajax.responseText;
		}
	}
ajax.send("datos="+nombre);
//elegirframe("");
//setTimeout(function(){ refrescar_id_cuestionario(); }, 3000);
}

// Fin buscar red
function buscar_cuestionario_pag(nombre=""){

if (nombre == ""){
	nombre = document.getElementById('txt_buscar_cuestionario').value;
}
ajax=nuevoAjax();
ajax.open("POST","?buscar_cuestionario_pag",true);
ajax.setRequestHeader("Content-type","application/x-www-form-urlencoded");
	ajax.onreadystatechange=function() {
		if (ajax.readyState==4) {
		document.getElementById('txtbuscar_cuestionario').innerHTML = ajax.responseText;
		}
	}
ajax.send("datos="+nombre);
elegirframe("");
setTimeout(function(){ 
refrescar_id_cuestionario();
$(function(fn){
    //contextmenu(fn);
});
}, 3000);
}
function marcar_leido(id){
$( "#"+id ).removeClass( "NO_leido" );
}
function eliminar_asignacion_acudiente(id){
$("div[role=tooltip]").remove();
ajax=nuevoAjax();
ajax.open("POST","?eliminar_asignacion_acudiente",true);
ajax.setRequestHeader("Content-type","application/x-www-form-urlencoded");
	ajax.onreadystatechange=function() {
		if (ajax.readyState==4) {
		var respuesta = ajax.responseText;
		//console.log(respuesta);
		if (respuesta==0){
		    alert3("Error al quitar asignación","error");
		}else if (respuesta==1){
		    alert3("Se ha quitado la asignación satisfactoriamente","success");
		}
		buscar();
		setTimeout(function(){
		    $("div[role=tooltip]").remove();
		},3000);
		}
	}
var parametros_send = "id_acudiente_estudiante="+id;
ajax.send(parametros_send);
}
function asignar_acudiente(parametros){
$("div[role=tooltip]").remove();
ajax=nuevoAjax();
ajax.open("POST","?asignar_acudiente",true);
ajax.setRequestHeader("Content-type","application/x-www-form-urlencoded");
	ajax.onreadystatechange=function() {
		if (ajax.readyState==4) {
		var respuesta = ajax.responseText;
		if (respuesta==0){
		    alert3("Error al asignar el acudiente "+parametros.nombre_acudiente+" al estudiante "+parametros.nombre_estudiante,"error");
		}else if (respuesta==1){
		    alert3("Se ha asignado el acudiente "+parametros.nombre_acudiente+" al estudiante "+parametros.nombre_estudiante+" satisfactoriamente","success");
		}else if (respuesta==1062){
		    alert3("Advertencia, el acudiente "+parametros.nombre_acudiente+" ya se encuentra asociado con el estudiante "+parametros.nombre_estudiante,"warning");
		}
		buscar();
		}
	}
var parametros_send = "id_acudiente="+parametros.id_acudiente+"&id_estudiante="+parametros.id_estudiante;
ajax.send(parametros_send);
}
function leer_mensaje(id_mensaje){
ajax=nuevoAjax();
ajax.open("POST","?leer_mensaje",true);
ajax.setRequestHeader("Content-type","application/x-www-form-urlencoded");
	ajax.onreadystatechange=function() {
		if (ajax.readyState==4) {
		document.getElementById('span_leer_mensaje').innerHTML = ajax.responseText;
		marcar_leido(id_mensaje);
		num_mensajes();
		document.getElementById('span_leer_mensaje').focus();
		}
	}
ajax.send("id_mensaje="+id_mensaje);
}
function buscar_mensaje(datos = ""){
    if (datos == ""){
        datos = document.getElementById('txt_buscar_mensaje').value;
    }
ajax=nuevoAjax();
ajax.open("POST","?buscar_mensaje",true);
ajax.setRequestHeader("Content-type","application/x-www-form-urlencoded");
	ajax.onreadystatechange=function() {
		if (ajax.readyState==4) {
		document.getElementById('span_buscar_mensaje').innerHTML = ajax.responseText;
		}
	}
ajax.send("datos="+datos);
    }
function buscar_mensaje_favoritos(datos = ""){
    if (datos == ""){
        datos = document.getElementById('txt_buscar_mensaje').value;
    }
ajax=nuevoAjax();
ajax.open("POST","?buscar_mensaje_favoritos",true);
ajax.setRequestHeader("Content-type","application/x-www-form-urlencoded");
	ajax.onreadystatechange=function() {
		if (ajax.readyState==4) {
		document.getElementById('span_buscar_favoritos').innerHTML = ajax.responseText;
		}
	}
ajax.send("datos="+datos);
    }
function buscar_mensaje_denuncias(datos = ""){
    if (datos == ""){
        datos = document.getElementById('txt_buscar_mensaje').value;
    }
ajax=nuevoAjax();
ajax.open("POST","?buscar_mensaje_denuncias",true);
ajax.setRequestHeader("Content-type","application/x-www-form-urlencoded");
	ajax.onreadystatechange=function() {
		if (ajax.readyState==4) {
		document.getElementById('span_buscar_denuncias').innerHTML = ajax.responseText;
		}
	}
ajax.send("datos="+datos);
    }
function buscar_mensaje_suscripciones(datos = ""){
    if (datos == ""){
        datos = document.getElementById('txt_buscar_mensaje').value;
    }
ajax=nuevoAjax();
ajax.open("POST","?buscar_mensaje_suscripciones",true);
ajax.setRequestHeader("Content-type","application/x-www-form-urlencoded");
	ajax.onreadystatechange=function() {
		if (ajax.readyState==4) {
		document.getElementById('span_buscar_suscripciones').innerHTML = ajax.responseText;
		}
	}
ajax.send("datos="+datos);
    }
function buscar_mensaje_papelera(datos = ""){
    if (datos == ""){
        datos = document.getElementById('txt_buscar_mensaje').value;
    }
ajax=nuevoAjax();
ajax.open("POST","?buscar_mensaje_papelera",true);
ajax.setRequestHeader("Content-type","application/x-www-form-urlencoded");
	ajax.onreadystatechange=function() {
		if (ajax.readyState==4) {
		    var respuesta = ajax.responseText;
		document.getElementById('span_buscar_papelera').innerHTML = respuesta;
		}
	}
ajax.send("datos="+datos);
    }
function num_mensajes(datos = ""){
ajax=nuevoAjax();
ajax.open("POST","?num_mensajes",true);
ajax.setRequestHeader("Content-type","application/x-www-form-urlencoded");
	ajax.onreadystatechange=function() {
		if (ajax.readyState==4) {
		    var num = ajax.responseText;
		document.getElementById('badge_num_ms').innerHTML = num;
		document.getElementById('badge_num_ms').setAttribute("num",num)
		}
	}
ajax.send("datos="+datos);
    }
function buscar(nombre=""){
if (nombre == ""){
	nombre = document.getElementById('buscar').value;
}
//alert("esta recibiendo datos"+funcion+" "+nombre);
ajax=nuevoAjax();
ajax.open("POST","?buscar",true);
ajax.setRequestHeader("Content-type","application/x-www-form-urlencoded");
	ajax.onreadystatechange=function() {
		if (ajax.readyState==4) {
		document.getElementById('txtsugerencias').innerHTML = ajax.responseText;
		cargar_dranganddrop();
		cargar_tooltips();
		$("div[role=tooltip]").remove();
		}
	}
ajax.send("datos="+nombre);
}
function buscar_iconos(nombre="",destino=''){
if (nombre == ""){
	nombre = document.getElementById('buscar_iconos').value;
}
//alert("esta recibiendo datos"+funcion+" "+nombre);
ajax=nuevoAjax();
ajax.open("POST","?buscar_iconos",true);
ajax.setRequestHeader("Content-type","application/x-www-form-urlencoded");
	ajax.onreadystatechange=function() {
		if (ajax.readyState==4) {
		document.getElementById('txtresultadosicono'+destino).innerHTML = ajax.responseText;
		}
	}
ajax.send("datos="+nombre+"&destino="+destino);
}
/*asignaturas mediante curso*/
function asignatura(cur){
//alert("esta recibiendo datos"+funcion+" "+nombre);
ajax=nuevoAjax();
ajax.open("POST","../php/ajax_asignatura.php",true);
ajax.setRequestHeader("Content-type","application/x-www-form-urlencoded");
	ajax.onreadystatechange=function() {
		if (ajax.readyState==4) {
		document.getElementById('span_asignatura').innerHTML = ajax.responseText;
		}else{
		document.getElementById('span_asignatura').innerHTML ="<select><option>Primero Seleccione Curso</option></select>";
		}
	}
ajax.send("cur="+cur);
}
/*asignaturas mediante curso*/
/*cookies*/

function leercookie(cname) {
<!--
	var name = cname + "=";
    var ca = document.cookie.split(';');
    for(var i=0; i<ca.length; i++) {
        var c = ca[i];
        while (c.charAt(0)==' ') c = c.substring(1);
        if (c.indexOf(name) == 0) return c.substring(name.length,c.length);
    }
    return "";
	-->
}

function eliminarcookie(key) {

document.cookie = key + '=;max-age=3600;';
}
function grabarcookieinput(id){
<!--
var valor = document.getElementById(id).value;
document.cookie=id+"="+valor;
//alert(document.cookie);
//window.open('index.php','_parent');
-->
}
function leercookieinput(id){
	<!--
	var valor=getCookie(id);
    if (valor!="") {
		document.getElementById(id).value = valor;
    }
	-->
}
function existecookie(id){
	var actual=leercookie(id);
	if (!actual) return false;
	else if (actual=="null") return false;
	else if (actual=="") return false;
	else return true;
}
function limpiar(id){
	<!--
var valor = document.getElementById(id);
valor.value="";
-->
}
/*fin cookies*/
/*
function valida_existe(campo,dato){
//alert("esta recibiendo datos"+campo+" "+dato);
ajax=nuevoAjax();
var url = "funciones.php"+"?campo="+campo+"&valida_dato="+dato;
ajax.open("GET",url,true);
ajax.setRequestHeader("Content-type","application/x-www-form-urlencoded");
	ajax.onreadystatechange=function() {
		if (ajax.readyState==4) {
			if(ajax.responseText==1){
			document.getElementById("txt"+campo).innerHTML = "<b>Ya esta registrado</b>";	
			}else if(ajax.responseText==0){
			document.getElementById("txt"+campo).innerHTML = "";
			//document.getElementById("txt"+campo).innerHTML = "<b>Disponible</b>";
			}
		
		}
	}
ajax.send();
}
*/
function contarinput(numero) {
     //  var fetch_assoc = ['dato1', 'dato2', 'dato3', 'dato4']; //array que paso de PHP a JS

     var inputElms = document.getElementsByClassName(numero); 

for(var i in inputElms) {
          //pongo los valores de fetch_array en input type=text, donde se corresponden los índices.
      //    (inputElms)[i].value = fetch_assoc[i];
        var t = inputElms.length  ;
     return parseInt(t) ;
     }

}

function promediar(valor){
           var n =  parseInt(contarinput(valor));
           //var n = 4;
           //alert (contarinput(valor));
           var x = document.getElementsByClassName(valor);
           var final = document.getElementById("final_"+valor);
           final.innerHTML="";
           var arreglo = new Array();
           for (i = 0; i < n; i++){
           if (x[i].value=="") arreglo[i]=0;
           else arreglo[i] = parseFloat(x[i].value);
           }
           //alert(arreglo);
       final.innerHTML=promedio(arreglo);
}//fin promediar2
function soloNumeros(e){
	var key = window.Event ? e.which : e.keyCode
	//alert (key);
	return ((key >= 48 && key <= 57) || (key >= 96 && key <= 105) || (key==190)  || (key==110) || (key==8)  || (key==9) || (key==38) || (key==40) || (key==46));
}
function promedio(array){
var promedio = 0;
   for (var nota in array){
    //alert(array[nota]);
    promedio = promedio + array[nota];
   }
   promedio = promedio/array.length
return promedio;
}
function alert2(mensaje='',t='success'){
	//alert(mensaje);
	/*
	* t='error'
	* t='success'
	* t='warning'
	* t='info'
	<a onclick="alert2('No se','info')">Prueba</a>
	*/
	swal(
    mensaje,
    '',
    t
  )
}
function alert3(texto,tipo="success",tiempo="5000"){
	document.getElementById("txt_alertas").innerHTML='<div class="alert alert-'+tipo+'"><strong>'+texto+'</strong></div>';
	setTimeout(function(){
		document.getElementById("txt_alertas").innerHTML='';
		}, tiempo);
}
function buscarcurso(texto=""){
/*
* Objetivo: permitir realizar la búsqueda de los cursos dentro del modulo de cursos
* Uso: Se utiliza en la barra de menu en el archivo comun/menu.php
*/

ajax=nuevoAjax();
ajax.open("POST","../../cursos/fn_ajax.php?",true);
ajax.setRequestHeader("Content-type","application/x-www-form-urlencoded");
	ajax.onreadystatechange=function() {
		if (ajax.readyState==4) {
		    $('.anos').show();
            $('.cats').show();
		//document.getElementById('txtresultados').innerHTML = "";
alert2(ajax.responseText);
		eval(ajax.responseText);
		}
	}
ajax.send("texto="+texto);
}
function MostrarTodosLosRed(estado){
if(estado=="false"){
     $('.cats').show();
        	$("#false").attr("id","true");
} else {
     $('.cats').hide();
        	$("#true").attr("id","false");
}
}



function mitoogle(id){
  var esVisible = $(id).is(":visible");
if (esVisible==true) {
$(id).hide(1000);
}else{
$(id).show(1000);
}
//$('.cats').hide();

}
function ocultar_ano_cat(id){
var estado = $('#'+id).css('display');
$('.cats').hide(1000);
if (estado=="none")
$('#'+id).show(1000);
else
$('#'+id).hide(1000);
}
function obtener_icono(icono){
	var datasrc = icono.getAttribute('data-src');
	var dataid = icono.getAttribute('data-id');
	//alert(" src:"+datasrc+" id:"+dataid);
    document.getElementById("hijo").value = dataid;
    document.getElementById("hijo-img").src = datasrc;
    document.getElementById("foto_hijo").value = datasrc;
     //document.querySelector(".modal").style.display="none";
     //quitarclase("#myModal","in");
     //$('#myModal').modal('hide');
     $('#myModal').hide();
     $('.modal-backdrop').hide();
}
 if(Notification.permission !== "granted"){
 Notification.requestPermission();
 }
 
 function notificar(titulo,icono,texto,url){
 if(Notification.permission !== "granted"){
 Notification.requestPermission();
 }else{
 var notificacion = new Notification(titulo,
 {
 icon: icono,
 body: texto
 }
 );
 
 notificacion.onclick = function(){
 window.open(url);
 }
 }
 }
 /*
$(function(fn){
    contextmenu(fn)
});
$(function(fn){
    fn.contextMenu({
    selector: '.menu_curso<?php echo $rowa['id_asignacion'] ?>', 
    items: fn.contextMenu.fromMenu($('#menu_curso<?php echo $rowa['id_asignacion'] ?>'))
});
});
*/
function contextmenu(fn){
//menu contextual
//Manuel Cerón 6 de Abril de 2017
//importación automatica de menu nativo html
//mediante selectores de atributo contextmenu
var menu_contextuals = document.querySelectorAll("[contextmenu]");
for (i=0;i<menu_contextuals.length;i++){
var menucont = menu_contextuals[i];
menu_contextuals[i].addEventListener("mouseover", function(){
    var selector_menu = this.getAttribute("contextmenu");
    fn.contextMenu({
        selector: '.'+selector_menu, 
        items: fn.contextMenu.fromMenu($('#'+selector_menu))
    });
});
}
}
$(function(){
var mostrarocultars = document.querySelectorAll("[mostrarocultar]");
for (i=0;i<mostrarocultars.length;i++){
mostrarocultars[i].addEventListener("click", function(){
    var selector_mostrarocultars = this.getAttribute("mostrarocultar");
    //revisar_chk(this.id,selector_mostrarocultars);"
    if($("#"+this.id).is(':checked')) {
    $("#"+selector_mostrarocultars).show(800);
    }else{
        $("#"+selector_mostrarocultars).hide(800);
    }
});
}
});
function seleccionar_cuestionario(id){
	var fila = document.getElementById('fila_'+id);
	if (fila)
	fila.click();
}
function elegirfila_est(id){
$('.filas').removeClass('active');
$('#fila_'+id).addClass('active');
}
function elegirfila(id,nombre){
    /*
    var misfilas = document.querySelectorAll('.filas');
    for(i=0;i<misfilas.length;i++){
        misfilas[i].className="filas";
    }
    */
    $('.filas').removeClass('active');
    /*
     if ($(window).scrollTop() > num) {
        $('.estado_guardado').addClass('fixed');
    } else {
        $('.estado_guardado').removeClass('fixed');
    }
    */
    document.getElementById('id_cuestionario').value=id;
    document.querySelector('[for=id_cuestionario]').innerHTML = nombre;
    $('#fila_'+id).addClass('active');
    //document.getElementById('fila_'+id).className +=' active';
}
function dejardeelegirfilas(){
    $('.filas').removeClass('active');
    document.getElementById('id_cuestionario').value="";
    document.querySelector('[for=id_cuestionario]').innerHTML = "Seleccione un cuestionario";
}
function elegirframe(ruta=""){
var framev = document.getElementById('frame_vistaprevia');
try {
    if (framev){
    framev.removeAttribute("src");
    framev.setAttribute("src",ruta);
    }
}
catch(err) {
    console.log(err.message);
}
}
function nuevocuestionario(){
    		swal({   title: "Crear Nuevo Cuestionario",
							text: "Por favor ingrese los detalles de su cuestionario",
							type: "input",
							showCancelButton: true,
                            confirmButtonText: "Registrar",
                            cancelButtonText: "Cancelar",
							closeOnConfirm: false},

								function(inputValue)
								{
									SweetAlertMultiInputReset(); // make sure you call this
									if (inputValue!=false)
									{
										
										swal({   title: "JSON",
										text: inputValue,
										type:"success",
										closeOnConfirm: true,
										},function(){SweetAlertMultiInputFix()}); // fix used if you want to display another box immediately
										if (inputValue!=false)
										{
											var checkResults = JSON.parse(inputValue);
											//alert(checkResults);
											console.log(checkResults);
											//do stuff
											alert2('Enviando','info')
											registrar_cuestionario(checkResults);
										}
									}
								}
							);
							
			//set up the fields: labels
			var tooltipsArray = ["Nombre","Palabras clave"];
			//set up the fields: defaults
			var defaultsArray = ["",""];
			SweetAlertMultiInput(tooltipsArray,defaultsArray);

    }
function registrar_cuestionario(checkResults){
var nombre = checkResults[0];
var tipo = checkResults[1];
ajax=nuevoAjax();
ajax.open("POST","?nuevo_cuestionario",true);
ajax.setRequestHeader("Content-type","application/x-www-form-urlencoded");
	ajax.onreadystatechange=function() {
		if (ajax.readyState==4) {
		var resp = ajax.responseText;
		if(resp=="1"){
		    alert2("Exito");
		    buscar_cuestionario();
		}else{
		    alert2("Error, "+resp,'error');
		}
		}
	}
ajax.send("nombre="+nombre+"&tipo="+tipo);
}//fin registrar_cuestionario
function actualizar_cuestionario(array_datos_cuestionario){
var id = array_datos_cuestionario[0];
var nombre = array_datos_cuestionario[1];
var tipo = array_datos_cuestionario[2];
ajax=nuevoAjax(); 
ajax.open("POST","?nuevo_cuestionario",true);
ajax.setRequestHeader("Content-type","application/x-www-form-urlencoded");
	ajax.onreadystatechange=function() {
		if (ajax.readyState==4) {
		var resp = ajax.responseText;
		if(resp=="1"){
		    alert2("Guardado ");
		}else{
		    alert2("Sin Guardar");
		}
		}
	}
ajax.send("id="+id+"&nombre="+nombre+"&tipo="+tipo);
}//fin actualizar_cuestionario
function duplicar_cuestionario(id){
ajax=nuevoAjax(); 
ajax.open("POST","?duplicar_cuestionario",true);
ajax.setRequestHeader("Content-type","application/x-www-form-urlencoded");
	ajax.onreadystatechange=function() {
		if (ajax.readyState==4) {
		var resp = ajax.responseText;
		if(resp=="1"){
		    alert3("Cuestionario duplicado");
		    buscar_cuestionario_pag();
		}else{
		    alert3("El cuestionario no fué duplicado","warning");
		}
		}
	}
ajax.send("id="+id);
}//fin duplicar_cuestionario
$(document).bind('keydown', function(e) {
cambioplantilla(e);
if(typeof guardar_cuestionario === 'function') {
    //Es seguro ejecutar la función
    //guardar_cuestionario(e);
}
});
function refrescar_id_cuestionario(){
var id_cuest = document.getElementById('id_cuestionario').value;
if (id_cuest!="") seleccionar_cuestionario(id_cuest);
}
var num = 150; //number of pixels before modifying styles
$(window).bind('scroll', function () {
	//alert($(window).scrollTop());
    if ($(window).scrollTop() > num) {
        $('.estado_guardado').addClass('fixed');
    } else {
        $('.estado_guardado').removeClass('fixed');
    }
});
//ajax favorito
function favorito(fav_mensaje,fn_js_page='buscar_mensaje();'){
    var a_favorito = $("#span_ev_"+fav_mensaje);
    var fav_estado = $("#span_ev_"+fav_mensaje).attr("estado");
ajax=nuevoAjax();
var url = "?fav_mensaje";
ajax.open("POST",url,true);
ajax.setRequestHeader("Content-type","application/x-www-form-urlencoded");
	ajax.onreadystatechange=function() {
		if (ajax.readyState==4) {
		var resp = ajax.responseText;
  		 document.getElementById("area_mensaje_"+fav_mensaje).innerHTML=resp;
		if(fn_js_page!='buscar_mensaje();'){
		    eval(fn_js_page);
		}
		}
	}
ajax.send("fav_mensaje="+fav_mensaje+"&fav_estado="+fav_estado);
    }
    
//fin ajax favorito

function mostrar_pantalla_completa(id,full=false) {
var docElm = document.getElementById(id);
if (docElm.requestFullscreen) {
docElm.requestFullscreen();
}
else if (docElm.mozRequestFullScreen) {
docElm.mozRequestFullScreen();
}
else if (docElm.webkitRequestFullScreen) {
docElm.webkitRequestFullScreen();
}
if (full){
docElm.style.width="100%";
docElm.style.height="100%";
}
}

function cancelar_pantalla_completa(){
midoc = document;
if (midoc.exitFullscreen) {
midoc.exitFullscreen();
}
else if (midoc.mozCancelFullScreen) {
midoc.mozCancelFullScreen();
}
else if (midoc.webkitCancelFullScreen) {
midoc.webkitCancelFullScreen();
}
}
function guardar_entrada(grupo){
var contenido = document.getElementById("contenido_"+grupo).value;
//alert3(contenido);
if (contenido!=""){
ajax=nuevoAjax();
ajax.open("POST","?guardar_entrada",true);
ajax.setRequestHeader("Content-type","application/x-www-form-urlencoded");
	ajax.onreadystatechange=function() {
		if (ajax.readyState==4) {
		var resp = ajax.responseText;
		if (resp=="1"){
		document.getElementById("txt_alertas").innerHTML='<div class="alert alert-success"><strong>Publicación Registrada!</strong></div>';
		}else{
		//console.log(resp);
		document.getElementById("txt_alertas").innerHTML='<div class="alert alert-warning"><strong>Publicación no Registrada!</strong></div>';
		}
		document.getElementById("contenido_"+grupo).value="";
		setTimeout(function(){
		document.getElementById("txt_alertas").innerHTML='';
		}, 3000);
		mis_foros();
		listar_entradas(grupo);
		}
	}
ajax.send("contenido="+contenido+"&grupo="+grupo);
}else{
    document.getElementById("txt_alertas").innerHTML='<div class="alert alert-warning"><strong>Aún no ha escrito ningun tema!</strong></div>';
    document.getElementById("contenido_"+grupo).focus();
    	setTimeout(function(){
		document.getElementById("txt_alertas").innerHTML='';
		}, 5000);
}
}
function guardar_comentario(entrada){
var contenido = document.getElementById("comentario_"+entrada).value;
if (contenido!=""){
ajax=nuevoAjax();
ajax.open("POST","?guardar_comentario",true);
ajax.setRequestHeader("Content-type","application/x-www-form-urlencoded");
	ajax.onreadystatechange=function() {
		if (ajax.readyState==4) {
		var resp = ajax.responseText;
		if (resp=="1"){
		document.getElementById("txt_alertas").innerHTML='<div class="alert alert-success"><strong>Publicación Registrada!</strong></div>';
		}else{
		//console.log(resp);
		document.getElementById("txt_alertas").innerHTML='<div class="alert alert-warning"><strong>Publicación no Registrada!</strong></div>';
		}
		document.getElementById("comentario_"+entrada).value="";
		setTimeout(function(){
		document.getElementById("txt_alertas").innerHTML='';
		}, 3000);
		listar_comentarios(entrada);
		}
	}
ajax.send("contenido="+contenido+"&entrada="+entrada);
}else{
    document.getElementById("txt_alertas").innerHTML='<div class="alert alert-warning"><strong>Aún no ha escrito ningun comentario!</strong></div>';
    document.getElementById("comentario_"+grupo).focus();
    	setTimeout(function(){
		document.getElementById("txt_alertas").innerHTML='';
		}, 5000);
}
}
function nueva_entrada(id_span=""){
var grupo = document.getElementById("grupo"+id_span).value;
var categoria1 = document.getElementById("categoria"+id_span);
if (categoria1) var categoria = categoria1.value;
var asignacion1 = document.getElementById("asignacion"+id_span);
if (asignacion1) var asignacion = asignacion1.value;
var actividad1 = document.getElementById("actividad"+id_span);
if (actividad1) var actividad = actividad1.value;
var contenido = document.getElementById("contenido"+id_span).value;
var token = document.getElementById("token"+id_span).value;
ajax=nuevoAjax();
ajax.open("POST","?nueva_entrada",true);
ajax.setRequestHeader("Content-type","application/x-www-form-urlencoded");
	ajax.onreadystatechange=function() {
		if (ajax.readyState==4) {
		var resp = ajax.responseText;
		if (resp=="1"){
		document.getElementById("txt_alertas").innerHTML='<div class="alert alert-success"><strong>Comentario Registrado!</strong></div>';
		//alert2('Registro exitoso');
		grabarcookie('page_foro'+id_span,'1');
		leer_foros(token,grupo,categoria,asignacion,actividad,id_span);
		}else{
		console.log(resp);
		document.getElementById("txt_alertas").innerHTML='<div class="alert alert-warning"><strong>Comentario no Registrado!</strong></div>';
		//alert2('Error al Registrar Entrada','error');
		leer_foros(token,grupo,categoria,asignacion,actividad,id_span);
		}
		document.getElementById("contenido"+id_span).value="";
		setTimeout(function(){
		document.getElementById("txt_alertas").innerHTML='';
		}, 5000);
		}
	}
var parametros = "";
if (categoria1) parametros += "&categoria="+categoria;
if (asignacion1) parametros += "&asignacion="+asignacion;
if (actividad1) parametros += "&actividad="+actividad;

ajax.send("token="+token+"&grupo="+grupo+"&contenido="+contenido+parametros);
}
function nuevo_comentario(id,id_span=""){
var grupo = document.getElementById("grupo"+id_span).value;
var categoria1 = document.getElementById("categoria"+id_span);
if (categoria1) var categoria = categoria1.value;
var asignacion1 = document.getElementById("asignacion"+id_span);
if (asignacion1) var asignacion = asignacion1.value;
var actividad1 = document.getElementById("actividad"+id_span);
if (actividad1) var actividad = actividad1.value;
var contenido = document.getElementById('contenido'+id).value;
var token = document.getElementById("token"+id_span).value;
ajax=nuevoAjax();
ajax.open("POST","?nuevo_comentario",true);
ajax.setRequestHeader("Content-type","application/x-www-form-urlencoded");
	ajax.onreadystatechange=function() {
		if (ajax.readyState==4) {
		var resp = ajax.responseText;
		if (resp=="1"){
		document.getElementById("txt_alertas").innerHTML='<div class="alert alert-success"><strong>Comentario Registrado!</strong></div>';
		//alert2('Registro exitoso');
		leer_foros(token,grupo,categoria,asignacion,actividad,id_span);
		}else{
		console.log(resp);
		document.getElementById("txt_alertas").innerHTML='<div class="alert alert-warning"><strong>Comentario no Registrado!</strong></div>';
		//alert2('Error al Registrar Entrada','error');
		leer_foros(token,grupo,categoria,asignacion,actividad,id_span);
		}
		document.getElementById('contenido'+id_span).value="";
		setTimeout(function(){
		document.getElementById("txt_alertas").innerHTML='';
		}, 5000);
		}
	}
var parametros = "";
if (categoria1) parametros += "&categoria="+categoria;
if (asignacion1) parametros += "&asignacion="+asignacion;
if (actividad1) parametros += "&actividad="+actividad;

ajax.send("token="+token+"&grupo="+grupo+"&id_entrada="+id+"&contenido="+contenido+parametros);
}
function leer_foros(token,grupo,categoria,asignacion,actividad,id_span=""){
ajax=nuevoAjax();
ajax.open("POST","?leer_foros",true);
ajax.setRequestHeader("Content-type","application/x-www-form-urlencoded");
	ajax.onreadystatechange=function() {
		if (ajax.readyState==4) {
		var resp = ajax.responseText;
  		document.getElementById("txt_foros"+id_span).innerHTML=resp;
		}
	}

ajax.send("token="+token+"&grupo="+grupo+"&categoria="+categoria+"&asignacion="+asignacion+"&actividad="+actividad+"&id_span="+id_span);
}
function buscar_estudiantes_actividad(id_actividad,datos=''){
  
if (datos=='') datos=document.getElementById("buscar_estudiante_actividad").value;
ajax=nuevoAjax();
ajax.open("POST","?buscar_estudiantes_actividad",true);
ajax.setRequestHeader("Content-type","application/x-www-form-urlencoded");
	ajax.onreadystatechange=function() {
		if (ajax.readyState==4) {
		var resp = ajax.responseText;
  		document.getElementById("txt_val_act").innerHTML=resp;
		}
	}
ajax.send("id_actividad="+id_actividad+"&datos="+datos);
}
function actualizar_posicion_portada(){
var x = document.getElementById("jumbotronbgPosX").value;
var y = document.getElementById("jumbotronbgPosY").value;
//console.log("x:"+x+" y:"+y);
ajax=nuevoAjax();
ajax.open("POST","?actualizar_posicion_portada",true);
ajax.setRequestHeader("Content-type","application/x-www-form-urlencoded");
	ajax.onreadystatechange=function() {
		if (ajax.readyState==4) {
		var resp = ajax.responseText;
		console.log(resp);
		//mostrar un estado sutil
		}
	}
ajax.send("x="+x+"&y="+y);
}
function validar_datalist(id,list){
		var val=$("#"+id).val();
		var obj=$("#"+list).find("option[value='"+val+"']")
			if(obj !=null && obj.length>0)
				return true;  // allow form submission
			else
				alert2('Conteido no vÃ¡lido, debe seleccionar un elemeto de la lista');
				return false;
}
function guardar_valoracion(id=""){
var id_act_val = document.getElementById('id_act_val'+id).value;
var id_seguimiento = document.getElementById('id_seguimiento'+id).value;
var id_inscripcion = document.getElementById('id_inscripcion'+id).value;
var valoracion = document.getElementById('valoracion'+id).value;
var observacion = document.getElementById('observacion'+id).value;
if (valoracion!=""){
ajax=nuevoAjax();
ajax.open("POST","?guardar_valoracion",true);
ajax.setRequestHeader("Content-type","application/x-www-form-urlencoded");
	ajax.onreadystatechange=function() {
		if (ajax.readyState==4) {
		var resp = ajax.responseText;
		console.log(resp);
		if (resp=="1"){
		alert3('Registro Exitoso!','success');
		}else{
		//console.log(resp);
		alert3('No se realizó el registro!','warning');
		}
		//alert(resp);
		buscar_estudiantes_actividad(id_act_val);
		}
	}
var parametros = "id_act_val="+id_act_val+"&id_seguimiento="+id_seguimiento+"&id_inscripcion="+id_inscripcion+"&valoracion="+valoracion+"&observacion="+observacion;
//alert(parametros);
ajax.send(parametros);
}else{
	alert2('Cuidado, no ha escrito su valoración','warning');
	document.getElementById('valoracion'+id).focus();
}
}
 function modificar_nota(id){
    var est = document.getElementById('seguimiento_'+id);
    document.getElementById('id_seguimiento').value = id;
    document.getElementById('valoracion').value = est.getAttribute('valoracion');
    document.getElementById('observacion').value = est.getAttribute('observacion');
   document.getElementById('valoracion').focus();
   }
    function nueva_nota(){
    document.getElementById('id_seguimiento').value ="";
    document.getElementById('valoracion').value = "";
    document.getElementById('observacion').value = "";
   document.getElementById('valoracion').focus();
   }
function notificar_mensajes(){
  /*
ajax=nuevoAjax();
ajax.open("GET","?notificacion_mensajes",true);
ajax.setRequestHeader("Content-type","application/x-www-form-urlencoded");
	ajax.onreadystatechange=function() {
		if (ajax.readyState==4) {
		var resp = ajax.responseText;
		//console.log(resp);
		if (resp!=""){
		var obj;
    res="hola";
		if (obj = JSON.parse(resp))
if (obj.num>0){
var titulo = "SGA"
var icono = "../comun/img/logo.png";
var texto = obj.mensaje;
var url = obj.url;//"../../mensajes/mis_mensajes.php";
playclip('../comun/audio/notificacion_mensaje.mp3');
num_mensajes();
if (texto!="")
notificar(titulo,icono,texto,url);
}
		}
		}
	}
ajax.send();
}
 function obtener_figuras() {
 var nombre = 'listo' ;
      $.ajax({
	type:'POST',
	 dataType: "json",
	url: '../comun/funciones.php',
	data:{
	datosw: nombre	
		}
})
.done(function(respuesta){
alert(respuesta);
//	$('#txtsugerencias').text(respuesta);
});	
*/
  }
  
 function login_para_boy(datos){
clave=(datos.getAttribute('data-info'));
document.getElementById('clave').value = datos.dataset.info;
var formData =$('#form_login').serialize();
 var select = document.getElementById("i");
  var options=document.getElementsByTagName("option");
var institucion=($("#institucion").val());
var rol=($("#rol").val());

formData={
  "valora":document.getElementById('usuario').value,
  "valorb":clave,
   "rol":rol,
   "institucion":institucion,
}
$.ajax({
    url : '../comun/funciones.php',
    data : formData,
    type : 'POST',
    success : function(respuesta) {
     var tiempo_cookie = 60;
     if(respuesta==1){
    grabarcookie("repetidas",'',tiempo_cookie); 
    window.location= '../index.php';         
     }
     else {
    var intentos = 2;     
    var trepetidas =parseInt(obtenerCookie('repetidas')); 
     if(isNaN(trepetidas)){
        trepetidas = 1; 
   grabarcookie("repetidas",trepetidas,tiempo_cookie); 
    } else{
     trepetidas = (parseInt(trepetidas)) + 1;
   grabarcookie("repetidas",trepetidas,tiempo_cookie); 
 }
   
    if(obtenerCookie('repetidas')>=intentos){ 
      alert2('No podrás ingresar durante '+tiempo_cookie+' Segundos');
 setTimeout("location.href='../index.php'",3000);
 }
 if(obtenerCookie('repetidas')<intentos){ 
 alert2(obtenerCookie('repetidas') +' intentos de '+intentos,'error');
} 
   
     }

    },
     error : function(xhr, status) {
        alert2('Disculpe, existió un problema');
    },
 
    // código a ejecutar sin importar si la petición falló o no
    complete : function(xhr, status) {
      //  alert('Petición realizada');
    }
});



}

function cambiar_cuenta(){
//  var mascotas = document.getElementsByClassName('mascotas_login');
  //    mascotas.remove(); 
$('.mascotas_login').hide();
      document.getElementById('clave');
      clave.type="password";
      document.getElementById('ingresar').style.visibility='';
    document.getElementById('usuario').value="";
    document.getElementById('clave').value="";
    document.getElementById('usuario').focus();
    document.getElementById('usuarios_recordados').style.display="block";
    document.getElementById('imgs_login').style.display="none";
}

function fav_cuestionario(cue,id_span){
//alert(cue+" - "+estado);
//id_span
var mi_span = document.getElementById(id_span);
var estado = mi_span.getAttribute("estado");
ajax_fav_cue=nuevoAjax();
ajax_fav_cue.open("POST","?fav_cuestionario",true);
ajax_fav_cue.setRequestHeader("Content-type","application/x-www-form-urlencoded");
	ajax_fav_cue.onreadystatechange=function() {
		if (ajax_fav_cue.readyState==4) {
		var resp_fav_cue = ajax_fav_cue.responseText;
		//console.log(resp_fav_cue);
		var mi_span = document.getElementById(id_span);
		var resp_json = JSON.parse(resp_fav_cue);
        mi_span.setAttribute("estado",resp_json.estado);
        mi_span.title = "Estrellas "+resp_json.total;
            if (resp_json.estado=="NO"){
                $("#"+id_span).removeClass('glyphicon-star-empty');
                $("#"+id_span).addClass('glyphicon-star');
            }else if (resp_json.estado=="SI"){
                $("#"+id_span).removeClass('glyphicon-star');
                $("#"+id_span).addClass('glyphicon-star-empty');
            }
		}
	}
ajax_fav_cue.send("id_cue="+cue+"&estado="+estado);
}
function fav_red(red,id_span){
//alert(cue+" - "+estado);
//id_span
var mi_span = document.getElementById(id_span);
var estado = mi_span.getAttribute("estado");
ajax_fav_red=nuevoAjax();
ajax_fav_red.open("POST","?fav_red",true);
ajax_fav_red.setRequestHeader("Content-type","application/x-www-form-urlencoded");
	ajax_fav_red.onreadystatechange=function() {
		if (ajax_fav_red.readyState==4) {
		var resp_fav_red = ajax_fav_red.responseText;
		console.log(resp_fav_red);
		var mi_span = document.getElementById(id_span);
		var resp_json = JSON.parse(resp_fav_red);
        mi_span.setAttribute("estado",resp_json.estado);
        mi_span.title = "Estrellas "+resp_json.total;
        var num_fav_red = document.getElementById('num_fav_red');
        if(num_fav_red) num_fav_red.innerHTML = resp_json.total;
            if (resp_json.estado=="NO"){
                $("#"+id_span).removeClass('glyphicon-star-empty');
                $("#"+id_span).addClass('glyphicon-star');
            }else if (resp_json.estado=="SI"){
                $("#"+id_span).removeClass('glyphicon-star');
                $("#"+id_span).addClass('glyphicon-star-empty');
            }
		}
	}
ajax_fav_red.send("id_red="+red+"&estado="+estado);
}
function fav_eve(eve,id_span){
var mi_span = document.getElementById(id_span);
var estado = mi_span.getAttribute("estado");
ajax_fav_eve=nuevoAjax();
ajax_fav_eve.open("POST","?fav_eve",true);
ajax_fav_eve.setRequestHeader("Content-type","application/x-www-form-urlencoded");
	ajax_fav_eve.onreadystatechange=function() {
		if (ajax_fav_eve.readyState==4) {
		var resp_fav_cue = ajax_fav_eve.responseText;
		console.log(resp_fav_cue);
		var mi_span = document.getElementById(id_span);
		var resp_json = JSON.parse(resp_fav_cue);
        mi_span.setAttribute("estado",resp_json.estado);
        mi_span.title = "Favoritos "+resp_json.total;
            if (resp_json.estado=="NO"){
                $("#"+id_span).removeClass('glyphicon-star-empty');
                $("#"+id_span).addClass('glyphicon-star');
            }else if (resp_json.estado=="SI"){
                $("#"+id_span).removeClass('glyphicon-star');
                $("#"+id_span).addClass('glyphicon-star-empty');
            }
		}
	}
ajax_fav_eve.send("id_evento="+eve+"&estado="+estado);
}
function fav_ent(ent,id_span){
var mi_span = document.getElementById(id_span);
var estado = mi_span.getAttribute("estado");
//*alert(ent+" "+id_span+" "+estado);
ajax_fav_ent=nuevoAjax();
ajax_fav_ent.open("POST","?fav_ent",true);
ajax_fav_ent.setRequestHeader("Content-type","application/x-www-form-urlencoded");
	ajax_fav_ent.onreadystatechange=function() {
		if (ajax_fav_ent.readyState==4) {
		var resp_fav_ent = ajax_fav_ent.responseText;
		console.log(resp_fav_ent);
		var mi_span = document.getElementById(id_span);
		var resp_json_ent = JSON.parse(resp_fav_ent);
        var s = "s";
        if (resp_json_ent.total=="1") s = "";
        //if (resp_json_ent.total!="0")
        mi_span.title = "A "+resp_json_ent.total+" le"+s+" gusta este tema";
        var total_likes = document.getElementById('total_likes_'+ent);
        total_likes.innerHTML = "";
        total_likes.title = "";
        //"A "+resp_json_ent.total+" persona"+s+" le"+s+" gusta este tema";
        //total_likes.title = resp_json_ent.quienes;
        //else
        //mi_span.title = "";
            if (resp_json_ent.estado=="SI"){
                $("#"+id_span).removeClass('me_gusta');
                $("#txt_span_entrada_"+ent).html(' Me Gusta <span class=\'badge\'>'+resp_json_ent.total+'</span>');
                mi_span.setAttribute("estado","SI");
            }else if (resp_json_ent.estado=="NO"){
                $("#"+id_span).addClass('me_gusta');
                $("#txt_span_entrada_"+ent).html(' Ya no Me Gusta <span class=\'badge\'>'+resp_json_ent.total+'</span>');
                mi_span.setAttribute("estado","NO");
                playclip();
            }
		}
	}
ajax_fav_ent.send("id="+ent+"&estado="+estado);
/* */
}


function playclip(ruta="../comun/audio/click.mp3") {
var sonidos = document.getElementById('sonidos');
var player = document.getElementById('player');
if(sonidos.checked == true){
    if (player) player.play();
    if (audio) audio.stop();
    var audio = new Audio(ruta);
    audio.play();
}else{
    if (player) player.pause();
    if (audio) audio.stop();
    //if (sound) sound.pause();
}
}
function guardar_evento(){
var id_eve = document.getElementById('id_eve');
var nombre_eve = document.getElementById('nombre_eve');
var descripcion_eve = document.getElementById('descripcion_eve');
var fecha_eve = document.getElementById('fecha_eve');
var hora_inicio_eve = document.getElementById('hora_inicio_eve');
var hora_fin_eve = document.getElementById('hora_fin_eve');
if (nombre_eve.value=="" || descripcion_eve.value=="" || fecha_eve.value=="" || hora_inicio_eve.value=="" || hora_fin_eve.value==""){
    alert3("Por favor verifique sus datos","warning");
}else{
ajax=nuevoAjax();
ajax.open("POST","?guardar_evento",true);
ajax.setRequestHeader("Content-type","application/x-www-form-urlencoded");
	ajax.onreadystatechange=function() {
		if (ajax.readyState==4) {
		var resp_evento = ajax.responseText;
  		if(resp_evento=="1"){
  		alert3("Se ha creado el evento "+nombre_eve.value);
        id_eve.value="";
        nombre_eve.value="";
        descripcion_eve.value="";
        fecha_eve.value="";
        hora_inicio_eve.value="";
        hora_fin_eve.value="";
        document.getElementById('cerrar_modal_evento').click();
  		}else{
  		alert3("No se guardó el evento "+nombre_eve.value+", Verifique su información e intente de nuevo","danger"); 
  		}
		}
	}
var parametros = "nombre_eve="+nombre_eve.value+"&descripcion_eve="+descripcion_eve.value+"&fecha_eve="+fecha_eve.value+"&hora_inicio_eve="+hora_inicio_eve.value+"&hora_fin_eve="+hora_fin_eve.value;
ajax.send(parametros);
}
}
function listar_comentarios(entrada,visita=true){
ajax=nuevoAjax();
ajax.open("POST","?listar_comentarios="+entrada,true);
ajax.setRequestHeader("Content-type","application/x-www-form-urlencoded");
	ajax.onreadystatechange=function() {
		if (ajax.readyState==4) {
		var resp = ajax.responseText;
  		 document.getElementById("txt_comentarios_"+entrada).innerHTML=resp;
  		 if (visita)
  		 visita_foro(entrada);
		}
	}
ajax.send();
}
/*
*/
function visita_foro(entrada){
ajax=nuevoAjax();
ajax.open("POST","?visita_foro",true);
ajax.setRequestHeader("Content-type","application/x-www-form-urlencoded");
	ajax.onreadystatechange=function() {
		if (ajax.readyState==4) {
		var resp = ajax.responseText;
		var resp_json = JSON.parse(resp);
		if (resp_json){
        console.log("Total:"+resp_json.total);
		}
		}
	}
ajax.send("id="+entrada);
}

function listar_temas(categoria){
//alert(categoria);
ajax=nuevoAjax();
ajax.open("POST","?listar_entradas="+categoria,true);
ajax.setRequestHeader("Content-type","application/x-www-form-urlencoded");
	ajax.onreadystatechange=function() {
		if (ajax.readyState==4) {
		var resp = ajax.responseText;
		var txt_entradas = document.getElementById("txt_entradas");
		if(txt_entradas){
		 txt_entradas.innerHTML=resp;
  		 fnmitop5(categoria);
		}
		}
	}
ajax.send();
}
function listar_entradas(categoria){
//alert(categoria);
ajax=nuevoAjax();
ajax.open("POST","?listar_entradas="+categoria,true);
ajax.setRequestHeader("Content-type","application/x-www-form-urlencoded");
	ajax.onreadystatechange=function() {
		if (ajax.readyState==4) {
		var resp = ajax.responseText;
  		 document.getElementById("txt_entradas").innerHTML=resp;
  		 fnmitop5(categoria);
		}
	}
ajax.send();
}
function denunciar_mensaje(id_mensaje){
ajax=nuevoAjax();
ajax.open("POST","?denunciar_mensaje="+id_mensaje,true);
ajax.setRequestHeader("Content-type","application/x-www-form-urlencoded");
	ajax.onreadystatechange=function() {
		if (ajax.readyState==4) {
		var resp_denuncia = ajax.responseText;
  		 if (resp_denuncia=="1"){
  		     alert3("Denuncia enviada");
  		 }else{
  		     alert3("Denuncia no enviada, por favor intente de nuevo","warning");
  		 }
		}
	}
ajax.send();
}
function datalist_required(id_input){
var input = document.getElementById(id_input);
if (input.value !="" ){
var inputh  = document.getElementById(id_input+'-hidden');
if (inputh.value==""){
		input.value = "";
		inputh.value = "";
		input.style="border-color:red";
	}else{
		input.style="";
	}
}
}
function enviar_mensaje(usuario,mensaje){
if (usuario!="" && mensaje!=""){
ajax=nuevoAjax();
ajax.open("POST","?enviar_mensaje=",true);
ajax.setRequestHeader("Content-type","application/x-www-form-urlencoded");
	ajax.onreadystatechange=function() {
		if (ajax.readyState==4) {
		   //console.log(resp_env_mensaje);
		var resp_env_mensaje = ajax.responseText;
  		 if (resp_env_mensaje=="1"){
  		     alert3("mensaje enviado");
  		      document.getElementById('cerrar_modal_mensaje').click();
  		 }else{
  		     alert3("Mensaje no enviado, por favor intente de nuevo","warning");
  		 }
		}
	}
ajax.send("usuario="+usuario+"&mensaje="+mensaje);
}else{
    alert3("Mensaje no enviado, debe escribir un usuario y un mensaje","info");
}
}
function papelera_mensaje(id_mensaje){
ajax=nuevoAjax();
ajax.open("POST","?papelera_mensaje="+id_mensaje,true);
ajax.setRequestHeader("Content-type","application/x-www-form-urlencoded");
	ajax.onreadystatechange=function() {
		if (ajax.readyState==4) {
		var resp_denuncia = ajax.responseText;
  		 if (resp_denuncia=="1"){
  		     alert3("Mensaje enviado a papelera");
  		     buscar_mensaje();
  		 }else{
  		     alert3("Mensaje no enviado a papelera, por favor intente de nuevo","warning");
  		 }
		}
	}
ajax.send();
}
function cambiar_docente_asignacion(id_asignacion,id_docente){
//alert(id_asignacion+" - "+id_docente);
ajax=nuevoAjax();
ajax.open("POST","?cambiar_docente_asignacion",true);
ajax.setRequestHeader("Content-type","application/x-www-form-urlencoded");
	ajax.onreadystatechange=function() {
		if (ajax.readyState==4) {
		var resp_cambio_doc = ajax.responseText;
		console.log(resp_cambio_doc);
  		 if (resp_cambio_doc=="1"){
  		     alert3("Docente Asignado");
  		     //buscar_cursos();
  		 }else{
  		     alert3("Docente no asignado, por favor intente de nuevo","warning");
  		 }
  		 setTimeout(function(){
		    $("div[role=tooltip]").remove();
		},3000);
		}
	}
ajax.send("id_asignacion="+id_asignacion+"&id_docente="+id_docente);
}
function olvidar_usuario(nombre_cookie){
ajax=nuevoAjax();
ajax.open("POST","?olvidar_usuario="+nombre_cookie,true);
ajax.setRequestHeader("Content-type","application/x-www-form-urlencoded");
	ajax.onreadystatechange=function() {
		if (ajax.readyState==4) {
		var resp_denuncia = ajax.responseText;
		console.log(resp_denuncia);
  		 if (resp_denuncia=="1"){
  		     alert3("Su Usuario ha sido olvidado");
  		 }else{
  		     alert3("No se realizaron cambios, por favor intente de nuevo","warning");
  		 }
		}
	}
ajax.send();
}
function bandeja_salida(datos=""){
if (datos == ""){
    datos = document.getElementById('txt_buscar_mensaje').value;
}
ajax=nuevoAjax();
ajax.open("POST","?bandeja_salida",true);
ajax.setRequestHeader("Content-type","application/x-www-form-urlencoded");
	ajax.onreadystatechange=function() {
		if (ajax.readyState==4) {
		var resp_bandeja_salida = ajax.responseText;
		document.getElementById('txt_bandeja_salida').innerHTML = resp_bandeja_salida;
		}
	}
ajax.send("datos="+datos);
}
function mis_foros(buscar=""){
if(buscar==""){
    buscar = document.getElementById('buscar_foro').value;
}
contexto = document.getElementById('contexto_foro').value;
ajax=nuevoAjax();
ajax.open("POST","?mis_foros",true);
ajax.setRequestHeader("Content-type","application/x-www-form-urlencoded");
	ajax.onreadystatechange=function() {
		if (ajax.readyState==4) {
		var resp_clonar_curso = ajax.responseText;
  		 document.getElementById('mis_foros').innerHTML = resp_clonar_curso;
		}
	}
ajax.send("buscar="+buscar+"&contexto="+contexto);
}
function fnmitop5(grupo){
ajax=nuevoAjax();
ajax.open("POST","?mitop5",true);
ajax.setRequestHeader("Content-type","application/x-www-form-urlencoded");
	ajax.onreadystatechange=function() {
		if (ajax.readyState==4) {
		var respuesta = ajax.responseText;
  		 var mitop5 = document.getElementById('mitop5');
  		 if(mitop5) mitop5.innerHTML = respuesta;
		}
	}
ajax.send("grupo="+grupo);
}
function clonar_curso(asignacion){
ajax=nuevoAjax();
ajax.open("POST","?clonar_curso",true);
ajax.setRequestHeader("Content-type","application/x-www-form-urlencoded");
	ajax.onreadystatechange=function() {
		if (ajax.readyState==4) {
		var resp_clonar_curso = ajax.responseText;
		console.log( ajax.responseText);
  		 if (resp_clonar_curso=="0"){
  		     alert3("Curso Duplicado");
            buscarcurso(asignacion);
  		 }else{
  		     alert3("No se duplicó el curso, por favor intente de nuevo","warning");
  		 }
		    
		}
	}
ajax.send("clonar_curso="+asignacion);
}
function elegir_fn_pestana_mens(obj){
document.getElementById('txt_buscar_mensaje').setAttribute('onkeyup',obj.getAttribute('funcion'));
document.getElementById('numeroresultados_mensaje').setAttribute('onchange',"grabarcookie('numeroresultados_mensaje',this.value);"+obj.getAttribute('funcion'));
var funcion = obj.getAttribute('funcion');
eval (funcion);
}
function confirm2(texto="Esta seguro?",tipo='warning',titulo="Guagua"){
var retornar = false;
swal({
  title: titulo,
  text: texto,
  type: tipo,
  showCancelButton: true,
  confirmButtonColor: "#DD6B55",
  confirmButtonText: "Aceptar",
  cancelButtonText: "Cancelar",
  closeOnConfirm: true,
  closeOnCancel: true,
  showLoaderOnConfirm: true
},
function(isConfirm){
  if (isConfirm){
      retornar = true;
  }else{
      retornar = false;
  }
});
return retornar;
}
function espacio_cursos(chk_lst_doc){
     /*
      Se utiliza para mostrar/ocultar
      la lista de docentes para asignar
      */
      if (chk_lst_doc.checked==true){
            $(".espacio_curso").removeClass('col-md-12');
            $(".espacio_curso").addClass('col-md-10');
        }else{
            $(".espacio_curso").removeClass('col-md-10');
            $(".espacio_curso").addClass('col-md-12');
        }
        
    }
function buscar_docente_para_asignar(datos=""){
if (datos == ""){
    datos = document.getElementById('datos_buscar_docente_para_asignar').value;
}
ajax=nuevoAjax();
ajax.open("POST","?buscar_docente_para_asignar",true);
ajax.setRequestHeader("Content-type","application/x-www-form-urlencoded");
	ajax.onreadystatechange=function() {
		if (ajax.readyState==4) {
		var resp_bandeja_salida = ajax.responseText;
		document.getElementById('ul_buscar_docente_para_asignar').innerHTML = resp_bandeja_salida;
		cargar_drag_and_drop_asignacion();
		}
	}
ajax.send("datos="+datos);
}
function buscar_acudiente_para_asignar(datos=""){
if (datos == ""){
    datos = document.getElementById('datos_buscar_acudiente_para_asignar').value;
}
ajax=nuevoAjax();
ajax.open("POST","?buscar_acudiente_para_asignar",true);
ajax.setRequestHeader("Content-type","application/x-www-form-urlencoded");
	ajax.onreadystatechange=function() {
		if (ajax.readyState==4) {
		var resp_bandeja_salida = ajax.responseText;
		document.getElementById('ul_buscar_acudiente_para_asignar').innerHTML = resp_bandeja_salida;
		cargar_dranganddrop();
		}
	}
ajax.send("datos="+datos);
}
function cargar_drag_and_drop_asignacion(){
   $( function() {
    $( ".draggable" ).draggable({
        helper: 'clone'
    });
    $( ".droppable" ).droppable({
     drop: function( event, ui ) {
        var id_docente = ui.draggable.find('.id_docente_draggable').html();
        var actual = $( this ).find('.docente_droppable').html();
        var id_asignacion = $( this ).attr('id_asignacion');
        obj_drop = $( this );
        swal({
  title: "Asignar Docente",
  text: "Cambiar a "+actual+" por "+ui.draggable.find('.docente_draggable').html()+"("+id_docente+") en "+$( this ).find('.materia_droppable').html(),
  type: "warning",
  showCancelButton: true,
  confirmButtonColor: "#3c763d",
  confirmButtonText: "Si, Asignar docente!",
  cancelButtonText: "No, No hacer cambios",
  closeOnConfirm: true,
  closeOnCancel: true,
  showLoaderOnConfirm: true
},
function(isConfirm){
  if(isConfirm){
      obj_drop
         .find('.docente_droppable')
         .html(ui.draggable.find('.docente_draggable').html());
         var id_docente = ui.draggable.find('.id_docente_draggable').html();
         cambiar_docente_asignacion(id_asignacion,id_docente);
         $("div[role=tooltip]").remove();
  }
});
  }
    });
  } );
}
function cargar_drag_and_drop_asignacion_padre(){
   $( function() {
    $( ".draggable" ).draggable({
        helper: 'original'
    });
    $( ".droppable" ).droppable({
     drop: function( event, ui ) {
         var padre = ui.draggable.find('.id_acudiente_draggable').html();
         var hijo = $( this ).attr('id_estudiante');
         alert2(padre+" "+hijo);
         $("div[role=tooltip]").remove();
         /*
         
        alert($( this ).find('.estudiante_droppable').html());
        var id_docente = ui.draggable.find('.id_acudiente_draggable').html();
        var actual = $( this ).find('.estudiante_droppable').html();
        var id_asignacion = $( this ).attr('id_estudiante');
        obj_drop = $( this );
        swal({
  title: "Asignar Docente",
  text: "Cambiar a "+actual+" por "+ui.draggable.find('.docente_draggable').html()+"("+id_docente+") en "+$( this ).find('.materia_droppable').html(),
  type: "warning",
  showCancelButton: true,
  confirmButtonColor: "#3c763d",
  confirmButtonText: "Si, Asignar docente!",
  cancelButtonText: "No, No hacer cambios",
  closeOnConfirm: true,
  closeOnCancel: true,
  showLoaderOnConfirm: true
},
function(isConfirm){
  if(isConfirm){
      obj_drop
         .find('.docente_droppable')
         .html(ui.draggable.find('.docente_draggable').html());
         var id_docente = ui.draggable.find('.id_docente_draggable').html();
         console.log(id_asignacion,id_docente);
        
  }
});
         */
  }
    });
  } );
}

function datosparalogin(id_usuario){
var select = document.getElementById('rol');
var nombre_usuario=document.getElementById('nombre_usuario').innerHTML;
var imagen_usuario=document.getElementById('imgusuario').src;
grabarcookie("repetidas",''); 
ajax_login3=nuevoAjax();
id_usuarios = document.getElementById('usuario').value ;
ajax_login3.open("POST","?consultar_datos_usuario="+id_usuarios,true);
ajax_login3.setRequestHeader("Content-type","application/x-www-form-urlencoded");
ajax_login3.onreadystatechange=function() {
select.remove(select.selectedIndex);
if (ajax_login3.readyState==4) {
var datos_usuario =  ajax_login3.responseText;
var info_usuario = JSON.parse(datos_usuario);
if(typeof(info_usuario.nombre)!= null || typeof(info_usuario.apellido)!= null ){
nombre_usuario.innerHTML =info_usuario.nombre+" "+info_usuario.apellido;
imagen_usuario=info_usuario.foto;
var option = document.createElement("option");
  var roles = String(info_usuario.roles);
 var roles_comas = info_usuario.roles;
		for (item in roles_comas){
//if (info_usuario.mascota==undefined) {
         option = document.createElement("option");
        option.setAttribute('Value',item);
        option.innerHTML = roles_comas[item];
        select.appendChild(option);
if (info_usuario.mascota== "NO") {
$('.mascotas_login').hide();
console.log();
document.getElementById('clave');
clave.type="password";
document.getElementById('ingresar').style.visibility='';
document.getElementById('clave').value="";
//document.getElementById('clave').focus();
document.getElementById('usuarios_recordados').style.display="block";
document.getElementById('imgs_login').style.display="none";
}

if (info_usuario.mascota== "SI") {
document.getElementById('mascotas').innerHTML = info_usuario[5]+info_usuario[6]+info_usuario[7]+info_usuario[8]+info_usuario[9]
      clave.type="hidden";
document.getElementById('ingresar').style.visibility='hidden';
document.getElementById('mascotas').style.display='';
}          
		}
		}else{
		document.getElementById('nombre_usuario').innerHTML ="Usuario";
		} 
		}
	}
ajax_login3.send("consultar_datos_usuario="+id_usuario);

}

function desabilitar_red (){
  var red = $("#checkbox2").prop("checked") ? true : false;
  if(marcado == false){
          limpiar_red();
}
}

function bajar_pregunta(n) {
var este_obj = document.getElementById(n);
var padre = este_obj.parentNode;
var hermanos = este_obj.parentNode.children;
//console.log(hermanos);
var resultado = 0;
var ultimo = 0;
var i = 0;
for (i=0; i<hermanos.length;i++){
  //console.log(hermanos[i].id);
  //console.log(este_obj.id)
    if (hermanos[i].id==este_obj.id){
    resultado=i;
}
ultimo=i;
}//fin for
//console.log("lastChild");
//console.log(hermanos[ultimo]);
//console.log("Actual");
if (este_obj != hermanos[ultimo] && hermanos.length>1){
//console.log(hermanos[resultado]);
var actual = hermanos[resultado];
var proximo = hermanos[resultado+2];
padre.removeChild(hermanos[resultado]);
padre.insertBefore(actual,proximo);
}else{
  //console.log("No se puede mover");
}
}
function subir_pregunta(n) {
var este_obj = document.getElementById(n);
var padre = este_obj.parentNode;
var hermanos = este_obj.parentNode.children;
//console.log(hermanos);
var resultado = 0;
var ultimo = 0;
var i = 0;
for (i=0; i<hermanos.length;i++){
  //console.log(hermanos[i].id);
  //console.log(este_obj.id)
    if (hermanos[i].id==este_obj.id){
    resultado=i;
}
ultimo=i;
}//fin for
//console.log("lastChild");
//console.log(hermanos[ultimo]);
//console.log("Actual");
if (este_obj != hermanos[0] && hermanos.length>1){
//console.log(hermanos[resultado]);
var actual = hermanos[resultado];
var anterior = hermanos[resultado-1];
padre.removeChild(hermanos[resultado]);
padre.insertBefore(actual,anterior);
}else{
  //console.log("No se puede mover");
}
}
 
function cargar_dranganddrop(){
    // Inicio Drag and drop
$("[fn-dranganddrop]").ready(function($){
    var datahelper = $("[fn-dranganddrop]").attr("dranganddrop-helper");
    var destino = $("[fn-dranganddrop]").attr("fn-dranganddrop");
    $("[fn-dranganddrop]").draggable({
        helper: datahelper
    });
    

    $("."+destino).droppable({
     drop: function( event, ui ) {

    var fnconfirm =  $(this).attr("fn-confirm");
    var fndroppable =  $(this).attr("fn-droppable");
    
    var datos_drag = ui.draggable.attr("dranganddrop-datos");
    var datos_drop = $(this).attr("dranganddrop-datos");

    var obj_datos_drag = JSON.parse(datos_drag);
    var obj_datos_drop = JSON.parse(datos_drop);
    
    var parametros_json = $.extend(obj_datos_drag, obj_datos_drop);
    var parametros_string = JSON.stringify(parametros_json);
    
    for (id in parametros_json){
        fnconfirm = fnconfirm.replace("["+id+"]",parametros_json[id]);
    }
    
    if_confirm_swal("¿"+fnconfirm+"?",fndroppable+'('+parametros_string+');','false',boton_verdadero = "Confirmar",boton_falso = "Cancelar",tipo='warning',titulo = "Guagua");
     }
    });
    
    /*
    */
});
// Fin Drag and drop
}
function suscribir_tema_foro(entrada,valor){
//console.log(entrada);
ajax=nuevoAjax();
ajax.open("POST","?suscribir_tema_foro",true);
ajax.setRequestHeader("Content-type","application/x-www-form-urlencoded");
	ajax.onreadystatechange=function() {
		if (ajax.readyState==4) {
		    var respuesta = ajax.responseText;
		    if(respuesta=="1"){
            listar_comentarios(entrada,false);
            if (valor=="SI") alert3("Suscipción exitosa");
            if (valor=="NO") alert3("Suscipción Cancelada Satisfactoriamente");
		    }else{
		    if (valor=="SI") alert3("No se realizó la suscipción, por favor intente de nuevo","warning");    
		   if (valor=="NO") alert3("No se realizó la cancelación, por favor intente de nuevo","warning");
		    }
		}
	}
ajax.send("id_entrada="+entrada+"&valor="+valor);
}
function denunciar_tema(entrada){
var contenido_denuncia= document.getElementById('contenido_denuncia'+entrada);
var tipo_denuncia= document.querySelectorAll('.tipo_denuncia'+entrada);
var tipos = Array();
//console.log(tipos);
var continuar = false;
if (tipo_denuncia){
    if (contenido_denuncia && contenido_denuncia.value!=""){
        //tipo_denuncia = tipo_denuncia.value;
        contenido_denuncia = contenido_denuncia.value;
        continuar = true;
        for (item in tipo_denuncia){
            tipos += tipo_denuncia[item].value;
            if (item!=tipo_denuncia.length)
            tipos += ", ";
        }
    }else{
        //tipo_denuncia = tipo_denuncia.value;
        contenido_denuncia = "";
        continuar = true;
        for (item in tipo_denuncia){
            tipos += tipo_denuncia[item].value;
            if (item!=tipo_denuncia.length)
            tipos += ", ";
        }
    }
}else{
    if (contenido_denuncia){
        contenido_denuncia = contenido_denuncia.value;
        continuar = true;
    }
}
if (continuar){
ajax=nuevoAjax();
ajax.open("POST","?denunciar_tema",true);
ajax.setRequestHeader("Content-type","application/x-www-form-urlencoded");
	ajax.onreadystatechange=function() {
		if (ajax.readyState==4) {
		    var respuesta = ajax.responseText;
		    if(respuesta=="1"){
            alert3("Denuncia exitosa, el mensaje será enviado para revisión");
    document.getElementById('cerrar_modal_denuncia'+entrada).click();
		    }else{
		    alert3("No se realizó la denuncia, por favor intente de nuevo","warning");
	document.getElementById('cerrar_modal_denuncia'+entrada).click();
		    }
		}
	}
var parametros = "id_tema="+entrada+"&contenido_denuncia="+contenido_denuncia+"&tipo_denuncia="+tipos;
ajax.send(parametros);
}else{
    alert3("No se realizó la denuncia, por favor indique o escriba un motivo de denuncia intente de nuevo","warning");
}
}
function toogle_entradas(entrada){
    //$("#"+entrada).toogle();
}
function valida_existe_id_usuario(nombre){
    valida_existe('usuario','id_usuario',nombre);
}
function valida_existe_nombre_usuario(nombre){
    valida_existe('usuario','usuario',nombre);
}
function valida_existe(tabla,campo,valor){
if (valor!=""){
ajax=nuevoAjax();
var url = "?valida_existe";
ajax.open("POST",url,true);
ajax.setRequestHeader("Content-type","application/x-www-form-urlencoded");
	ajax.onreadystatechange=function() {
		if (ajax.readyState==4) {
			var respuesta = ajax.responseText;
			//console.log(respuesta);
			if(respuesta=="1"){
			//console.log("Ya esta registrado");
			document.getElementById("txte"+campo).innerHTML = "<b>Ya esta registrado</b>";
			document.getElementById("txte"+campo).style.color="red";
			}else if(respuesta=="0"){
			//console.log("Disponible");
			//document.getElementById("txt"+campo).innerHTML = "";
			document.getElementById("txte"+campo).innerHTML = "<b>Disponible</b>";
			document.getElementById("txte"+campo).style.color="green";
			
			}
		
		}
	}
var parametros = "tabla="+tabla+"&campo="+campo+"&valor="+valor;
//alert (parametros);
ajax.send(parametros);
}
}
function valida_existe_par(tabla,campo,valor,campo2,valor2){
//alert (tabla+campo+valor);
if (valor.length>0 && valor2.length>0){
ajax=nuevoAjax();
var url = "?valida_existe_par";
ajax.open("POST",url,true);
ajax.setRequestHeader("Content-type","application/x-www-form-urlencoded");
	ajax.onreadystatechange=function() {
		if (ajax.readyState==4) {
			var respuesta = ajax.responseText;
			//console.log("txt"+valor+valor2);
			//console.log(respuesta);
			if(respuesta=="1"){
			//console.log("Ya esta registrado");
			document.getElementById("txt"+campo+campo2).innerHTML = "<b>Ya esta registrado</b>";
			}else if(respuesta=="0"){
			//console.log("Disponible");
			//document.getElementById("txt"+campo).innerHTML = "";
			document.getElementById("txt"+campo+campo2).innerHTML = "<b>Disponible</b>";
			
			}
		
		}
	}
var parametros = "tabla="+tabla+"&campo="+campo+"&valor="+valor+"&campo2="+campo2+"&valor2="+valor2;
//alert (parametros);
ajax.send(parametros);
}else{
	document.getElementById("txt"+campo+campo2).innerHTML = "";
}
}
function soloNumeros(e){
	//uso en input  onKeyPress="return soloNumeros(event)"
	var key = window.Event ? e.which : e.keyCode
	//alert (key);
	return (key >= 48 && key <= 57)
	//return ((key >= 48 && key <= 57) || (key >= 96 && key <= 105) || (key==190)  || (key==110) || (key==8)  || (key==9) || (key==38) || (key==40) || (key==46));
}

function Graficooffline(titulo,cualitativa,cuantitativa,contenedor,tipo_grafica,ancho,alto,datos) {
var objeto = [[cualitativa,cuantitativa]];
for (id in datos.elementos){objeto[objeto.length]=[datos.elementos[id].cualitativo, parseInt(datos.elementos[id].cuantitativo)];}
var data = google.visualization.arrayToDataTable(objeto);//Cerramos la creación de la variable datodocument.write();s
eval('new google.visualization.'+tipo_grafica+'(document.getElementById("'+contenedor+'")).       draw(          data,{title:"'+titulo+'",width: "'+ancho+'",height:"'+alto+'",});');
      }