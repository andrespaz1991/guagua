<!DOCTYPE html>
<html>
    <head>            
        <title>Ejemplo LocalStorage</title>
        <?php require_once("../comun/config.php")?>
	<script src="<?php echo SGA_COMUN_URL ?>/js/jquery-2.2.4.min.js"></script>
    <link rel="stylesheet" type="text/css" href="<?php echo SGA_COMUN_URL ?>/img/png/icon-sga.php">
    
        <script>
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
/*Funcion de Capturar, Almacenar datos y Limpiar campos*/
$(document).ready(function(){    
    $('#boton-guardar').click(function(){
        /*Captura de datos escrito en los inputs*/        
        var cod= document.getElementById("cod").value;
        var nom = document.getElementById("nombretxt").value;
        var apel = document.getElementById("apellidotxt").value;
        var foto = document.getElementById("foto").value;
     toDataURL(foto, function(dataUrl) {
  localStorage.setItem("Foto['"+cod+"']", dataUrl);
})
        /*Guardando los datos en el LocalStorage*/
        localStorage.setItem("Nombre['"+cod+"']", nom);
        localStorage.setItem("Apellido['"+cod+"']", apel);
        /*Limpiando los campos o inputs*/
        document.getElementById("nombretxt").value = "";
        document.getElementById("apellidotxt").value = "";
        //document.getElementById("foto").value = "";
    });   
});

/*Funcion Cargar y Mostrar datos*/
$(document).ready(function(){    
    $('#boton-cargar').click(function(){                 
        /*Obtener datos almacenados*/
        var cod = document.getElementById("cod").value;
        var nombres = localStorage.getItem("Nombre");
        var nombre = localStorage.getItem("Nombre['"+cod+"']");
        var apellido = localStorage.getItem("Apellido['"+cod+"']");
        /*Mostrar datos almacenados*/      
        document.getElementById("nombre").innerHTML = nombre;
        document.getElementById("apellido").innerHTML = apellido;
        salida = document.getElementById("salida");
        var i;
        var ides = ["1","12"];
        salida.innerHTML ="";
        for (i=0;i<ides.length;i++){
        salida.innerHTML += ides[i];
        salida.innerHTML += "<br>";
        salida.innerHTML += localStorage.getItem("Nombre['"+ides[i]+"']");
        salida.innerHTML += "<br>";
        salida.innerHTML += localStorage.getItem("Apellido['"+ides[i]+"']");
        salida.innerHTML += "<br>";
        salida.innerHTML += "<img width='40' src='"+localStorage.getItem("Foto['"+ides[i]+"']")+"'>";
        salida.innerHTML += "<br>";
        salida.innerHTML += "<br>";
        }
    });   
});

</script>
</head>
        
<center><h1>Ejemplo - localStorage</h1>
              
<input type="text" placeholder="Codigo" id="cod"> <br>  <br>   
<input type="text" placeholder="Nombre" id="nombretxt"> <br>  <br>   
<input type="text" placeholder="Apellido" id="apellidotxt"><br>  <br>
<input type="text" placeholder="Foto" id="foto">
<img id="img" width="40">
<br>  <br>
<button id="boton-guardar">Guardar</button><br>
<span id="salida"></span>           
<hr />
Nombre almacenado:
<label type="text" id="nombre"></label><br>                          
Apellido almacenado:
<label "text" id="apellido"></label><br>
<button id="boton-cargar">
Cargar elementos
</button>
</center>

<hr />

</body> 
</html>
