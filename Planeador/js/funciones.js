function checkDecimals(fieldName, fieldValue)
{
decallowed = 2;
if (isNaN(fieldValue) || fieldValue == "")
{
alert("No es un number.try valida de nuevo.");
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
function ctck()
{
var sds = document.getElementById("dum");
if(sds == null){alert("Esta utilizando un paquete gratuito. No esta autorizado para retirar la etiqueta");}
var sdss = document.getElementById("dumdiv");
if(sdss == null){alert("Esta utilizando un paquete gratuito. No esta autorizado para retirar la etiqueta");}
}
/*document.onLoad="ctck()";*/

function confirmeliminar(page,params,tit) {
	if (confirm("¿Esta ud seguro que quiere eliminar el registro "+tit+"?")){ 
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
			  form.submit();
		};
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
		}
	}
ajax.send("datos="+nombre);
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
function grabarcookie(id,valor){
<!--
document.cookie=id+"="+valor;
-->
}
function eliminarcookie(key) {
<!--
document.cookie = key + '=;expires=Thu, 01 Jan 1970 00:00:01 GMT;';
-->
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
	if (actual=="null") return false;
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