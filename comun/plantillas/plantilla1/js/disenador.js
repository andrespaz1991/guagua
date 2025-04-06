
function nueva_opcion(numero,tipo,num_p,pre=""){
        var items_section = document.getElementById("section_pregunta["+numero+"]");
        count = parseInt(items_section.getAttributeNode("items").value) + 1;
        var lista = document.getElementById("pregunta["+numero+"]");
        var li = document.createElement("li");
        li.id="li["+numero+"]["+count+"]";
        li.style="text-align:left";
        
        var input = document.createElement("input");
        if(tipo=="checkbox") {
            input.type = "checkbox";
            input.value = count;
            //input.name = "op["+numero+"]["+count+"]";
                   var att_input = document.createAttribute("name");
            att_input.value = "op["+num_p+"][]";
            input.setAttributeNode(att_input);
        }else{
         input.type = "checkbox";
         input.value = count;
         //input.name = "op["+numero+"]";   
          var att_input = document.createAttribute("name");
            att_input.value = "texto_pregunta[op]["+num_p+"][]";
            input.setAttributeNode(att_input); 
            
        }
        input.id = "texto_pregunta[op]["+num_p+"]["+numero+"]["+count+"]";
        
        var input2 = document.createElement("input");
        input2.type = "text";
         if(tipo=="checkbox") {
            //input.name = "op["+numero+"]["+count+"]";
            var att_input2 = document.createAttribute("name");
            att_input2.value = "texto_pregunta[txtop]["+num_p+"]["+count+"]";
            input2.setAttributeNode(att_input2);
        }else{
         //input.name = "op["+numero+"]";   
          var att_input2 = document.createAttribute("name");
            att_input2.value = "texto_pregunta[txtop]["+num_p+"]["+count+"]";
            input2.setAttributeNode(att_input2); 
            
        }
        input2.id = "texto_pregunta[txtop]["+num_p+"]["+count+"]";
        if(pre==""){
        input2.value = "Opcion"+count;
        }else{
        input2.value=pre;
        }
        if (count>1){
        var att_input2 = document.createAttribute("autofocus");
        input2.setAttributeNode(att_input2);
        }
        
        var a = document.createElement("input");
        a.type = "button";
        a.value = "Quitar Opción";
        var att = document.createAttribute("onclick");
        att.value = "eliminar_opcion('li["+numero+"]["+count+"]')"; 
        
        var ad = document.createElement("input");
        ad.type = "button";
        ad.value = "Duplicar Opción";
        var attd = document.createAttribute("onclick");
        attd.value = "nueva_opcion('"+numero+"','"+tipo+"','"+num_p+"',document.getElementById('"+input2.id+"').value)"; 
        //var btd = '<input type="button" value="Nueva Opción" onclick="nueva_opcion'+"('1','','1','perso')"+';">';
        
        a.setAttributeNode(att);
        ad.setAttributeNode(attd);
        li.appendChild(input);
        li.appendChild(input2);
        li.appendChild(a);
        li.appendChild(ad);
        lista.appendChild(li);
        
        var items_section = document.getElementById("section_pregunta["+numero+"]");
        items_section.getAttributeNode("items").value++;
    }
    function eliminar_opcion(id,pregunta=""){
        var nodo = document.getElementById(id);
        nodo.parentNode.removeChild(nodo);
        if (pregunta!=""){
            ejecutar("disenador.php?elim&id_pregunta="+pregunta);
        }
        estado_sin_guardar();
    }
function nueva_pregunta(tipo,num="",copia=false){
var preg = document.getElementById("preguntas");
var sectionold = document.getElementById("section_pregunta["+num+"]");
if(num=="" || copia==true){
var num_preguntas = preg.getAttributeNode("preguntas").value;
var count = parseInt(num_preguntas) + 1;
}else{
var count = num;
var sectionold = document.getElementById("section_pregunta["+num+"]");
var ulold = document.getElementById("pregunta["+num+"]");
}

var section = document.createElement("section");
section.id="section_pregunta["+count+"]";

var att_section = document.createAttribute("pregunta");
att_section.value = count; 
section.setAttributeNode(att_section);

var att2_section = document.createAttribute("items");
att2_section.value = "0"; 
section.setAttributeNode(att2_section);

var att3_section = document.createAttribute("tipo");
att3_section.value = tipo; 
section.setAttributeNode(att3_section);

var h2 = document.createElement("input");
h2.id="texto_pregunta[nombre]["+count+"]";
h2.name="texto_pregunta[nombre]["+count+"]";
h2.placeholder = "Título de la pregunta";
h2.style = "width:100%";
h2.setAttributeNode(document.createAttribute("required"));
if(num!="" || copia==true){
    h2.value = document.getElementById("texto_pregunta[nombre]["+num+"]").value;
}
//var att_h2 = document.createAttribute("contenteditable");
//att_h2.value = "true"; 
//h2.setAttributeNode(att_h2);

//var texto_h2 = document.createTextNode("Título de la pregunta");
//h2.appendChild(texto_h2);

var a = document.createElement("input");
var att = document.createAttribute("onclick");
att.value = "eliminar_opcion('section_pregunta["+count+"]')"; 
a.setAttributeNode(att); 
a.type = "button";
a.value = "Quitar Pregunta";

var a2 = document.createElement("input");
var att2 = document.createAttribute("onclick");
att2.value = "duplicar_pregunta("+count+");"; 
a2.setAttributeNode(att2); 
a2.type = "button";
a2.value = "Copiar Pregunta";

var id_preguntas_cuestionario = document.createElement("input");
/*id_preguntas_cuestionario.style="display:none";*/
id_preguntas_cuestionario.type="hidden";
id_preguntas_cuestionario.name="id_preguntas_cuestionario["+count+"]";
id_preguntas_cuestionario.value = "NULL"; 
section.appendChild(id_preguntas_cuestionario);

var tipo_pregunta = document.createElement("input");
//tipo_pregunta.style="display:none";
tipo_pregunta.type="hidden";
tipo_pregunta.name="tipo_pregunta["+count+"]";
tipo_pregunta.value = tipo; 
section.appendChild(tipo_pregunta);
var txt_tipo_ppregunta = document.createTextNode("Tipo de pregunta: ");
section.appendChild(txt_tipo_ppregunta);
var span_ntp = '<span><select id="cambiar_tp_'+count+'" onchange="cambiar_tipo_pregunta(this.value,\''+count+'\');">';
span_ntp += nueva_tipos_pregunta;
span_ntp += '</select></span>';
span_select_tipos = htmlToElement(span_ntp);
span_select_tipos.firstChild.value=tipo;
section.appendChild(span_select_tipos.firstChild);
section.appendChild(h2);
section.appendChild(a);
section.appendChild(a2);
if (tipo=="radio" || tipo=="checkbox" || tipo=="select"){
var button = document.createElement("input");
button.type="button";
button.value="Nueva Opción";

var att_button = document.createAttribute("onclick");
att_button.value = "nueva_opcion('"+count+"','"+tipo+"','"+count+"');"; 
button.setAttributeNode(att_button); 
var script = document.createElement("script");
var contenido_script = document.createTextNode("nueva_opcion('"+count+"','"+tipo+"','"+count+"');");
script.appendChild(contenido_script);
if (num!=""){
    var ulold = document.getElementById("pregunta["+num+"]");
    
    var lis_ulold = ulold.childElementCount;
    var plantilla_ul = document.createElement("span");
    plantilla_ul.appendChild(ulold.cloneNode(true));
    plantilla_ul.innerHTML = plantilla_ul.innerHTML.replace("pregunta["+num+"]","pregunta["+count+"]");
    var i=0;
    for (i=0;i<lis_ulold;i++){
    plantilla_ul.innerHTML = plantilla_ul.innerHTML.replace("li["+num+"]","li["+count+"]");
    plantilla_ul.innerHTML = plantilla_ul.innerHTML.replace("li["+num+"]","li["+count+"]");
    plantilla_ul.innerHTML = plantilla_ul.innerHTML.replace("texto_pregunta[op]["+num+"]","texto_pregunta[op]["+count+"]");
    plantilla_ul.innerHTML = plantilla_ul.innerHTML.replace("texto_pregunta[op]["+num+"]","texto_pregunta[op]["+count+"]");
    plantilla_ul.innerHTML = plantilla_ul.innerHTML.replace("texto_pregunta[txtop]["+num+"]","texto_pregunta[txtop]["+count+"]");
    plantilla_ul.innerHTML = plantilla_ul.innerHTML.replace("texto_pregunta[txtop]["+num+"]","texto_pregunta[txtop]["+count+"]");
    plantilla_ul.innerHTML = plantilla_ul.innerHTML.replace("texto_pregunta[txtop]["+num+"]","texto_pregunta[txtop]["+count+"]");
    plantilla_ul.innerHTML = plantilla_ul.innerHTML.replace("nueva_opcion('"+num+"'","nueva_opcion('"+count+"'");    
    }
    var ul = plantilla_ul.firstChild;
    console.log("antes");
    console.log(ulold);
    console.log("despues");
    console.log(ul);
}else{
var ul = document.createElement("ul");
ul.appendChild(script);
ul.id="pregunta["+count+"]";
}
section.appendChild(ul);
section.appendChild(button);
}
if(num==""){
preg.appendChild(section);
preg.getAttributeNode("preguntas").value++;
}else{
    if(copia==true){
    preg.insertBefore(section,sectionold);
    preg.getAttributeNode("preguntas").value++;
    }else{
    preg.replaceChild(section,sectionold);
    }
}
if(copia==true){
    var tipo_p= document.getElementById("cambiar_tp_"+count);
    tipo_p.value=tipo;
var obj = document.getElementById("texto_pregunta[nombre]["+num+"]");
}else{
var obj = document.getElementById("texto_pregunta[nombre]["+count+"]");
}
if(obj) obj.focus();
}
function duplicar_pregunta(id){
var preg = document.getElementById("preguntas"); 
var tipo = document.getElementById("cambiar_tp_"+id).value;
/*
var sectionold = document.getElementById("section_pregunta["+id+"]");
var nodoNuevoCopia = sectionold.cloneNode(true);
var num_preguntas = preg.getAttributeNode("preguntas").value;
preg.appendChild(nodoNuevoCopia);
var count = parseInt(num_preguntas) + 1;
if (obj = document.getElementById("texto_pregunta[nombre]["+count+"]"))
if (obj) obj.focus();
*/
if (tipo=="") tipo="input";
nueva_pregunta(tipo,id,true);
}
function insertAfter(newNode, referenceNode) {
    referenceNode.parentNode.insertBefore(newNode, referenceNode.nextSibling);
}
function htmlToElement(html) {
    var template = document.createElement('template');
    template.innerHTML = html;
    return template.content.firstChild;
}
function ejecutar(url){
var resultado = false;
ajax=nuevoAjax();
ajax.open("GET",url,true);
ajax.setRequestHeader("Content-type","application/x-www-form-urlencoded");
	ajax.onreadystatechange=function() {
		if (ajax.readyState==4) {
		    datos = ajax.responseText;
    		if(datos=="1"){
    		    //alert2("Eliminado");
    		resultado = true;
    		}
    		if(datos=="0"){
    		    //alert2("Error al Eliminar",'error');
    		resultado = false;  
	    	}
		}
	}
ajax.send();
return resultado;
}
function cambiar_tipo_pregunta(tipo,id){
    nueva_pregunta(tipo,id);
    document.getElementById('cambiar_tp_'+id).value = tipo;
}