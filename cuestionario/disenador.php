<?php
ob_start();
require_once("../comun/funciones.php");
if (isset($_GET['enc'])){
$_POST['cod']=$_GET['enc'];
}
if (!isset($_POST['cod'])){
header ("Location: ../index.php");
}
require("../comun/conexion.php");
//eliminar_preguntabd
        $sql= "SELECT id,nombre FROM tipo_pregunta;";
        $consulta = $mysqli->query($sql);
        $array_tipo_pregunta = array();
        while($row=$consulta->fetch_assoc()){
        $array_tipo_pregunta[$row['id']] = $row['nombre'];
        }
if(isset($_GET['elim']) and isset($_GET['id_pregunta'])){
    require("../comun/conexion.php");
$id_pregunta = $_GET['id_pregunta'];
$sql_borrar_pregunta = "DELETE FROM `preguntas_cuestionario` WHERE `id_preguntas_cuestionario` = '".$id_pregunta."'";
$borrar_pregunta=$mysqli->query($sql_borrar_pregunta);
	$row_borrar_pregunta = $mysqli->affected_rows;
	if($row_borrar_pregunta > 0){
	    echo "1";
	}else{
	    echo "0";
    }
exit();
}
//listado de tipo de pregunta
$sql= "SELECT id,nombre FROM tipo_pregunta;";
$consulta = $mysqli->query($sql);
?>
<script>
function fn_tipos_pregunta(tipo){
var tipopre = { <?php
$tipos_pregunta = "";
$count=1;
$count_total = $consulta->num_rows;
while($row=$consulta->fetch_assoc()){
$tipos_pregunta .= '<option value="'.$row['id'].'">'.$row['nombre'].'</option>';
?> <?php echo $row['id'] ?>:"<?php echo $row['nombre']; ?>" <?php
if ($count<$count_total) echo ",";
$count++;
} ?> }
return tipopre[tipo];
}
</script>
<?php
?>
<?php
//fin listado de tipo de pregunta
?>
<script src="../comun/js/funciones.js"></script>
	<script>
	var nueva_tipos_pregunta;
	//proposito: permitir cambiar el tipo de pregunta
	nueva_tipos_pregunta = '<?php echo $tipos_pregunta; ?>';
</script>
<link rel="stylesheet" type="text/css" href="../comun/css/disenador.css" /><!--estilo disenador.css--->
<style>
.fixed {
	position:fixed;
	top:0;
}
</style>
<?php
$datos=$_POST['cod'];
require("../comun/conexion.php");
$sql = 'SELECT `id`, `nombre`, `fecha`, `tipo_cuestionario`, `usuario` FROM `cuestionario`  WHERE `id` = "'.$datos.'"';
$consulta = $mysqli->query($sql);
#echo $sql;
if($row=$consulta->fetch_assoc()){
?>
<title><?php echo $row['nombre']?></title>
</head>
<body>
<div id ="contenedor_disenador">
	<script>
	function validar_form(){
		var verificar = document.getElementById('verificar');
		if (verificar.checked){
			return true;
		}else{
			return false;
		}
	}
	function estado_guardar(){
		var estadoguardar = document.getElementById('estado_guardar');
		if (estadoguardar.title=="Guardado"){
			estadoguardar.title="Sin Guardar";
			estadoguardar.className='icon-sga-warning';
			var titlemic = document.querySelector('#nombre_cuestionario');
			titlemic.style.color = "orange";
		}else{
			estadoguardar.title="Guardado";
			estadoguardar.className='icon-sga-success';
			var titlemic = document.querySelector('#nombre_cuestionario');
			titlemic.style.color = "#000";
		}
	}
	function estado_sin_guardar(){
		var guardar_cuestionario_auto;
		var estadoguardar = document.getElementById('estado_guardar');
		estadoguardar.title="Sin Guardar";
		estadoguardar.className='icon-sga-warning';
		var titlemic = document.querySelector('#nombre_cuestionario');
		titlemic.style.color = "orange";
		clearTimeout(guardar_cuestionario_auto);
		guardar_cuestionario_auto = setTimeout(function(){
		guardar_cuestionario_auto_est();
		}, 10000);//guardar automaticamente despues de 10 segundos
	}
function guardar_cuestionario_auto_est(auto=1){
/*
var id_cuestionario = document.getElementById('id_cuestionario').value;
var nombre_cuestionario = document.getElementById('nombre_cuestionario').value;
var tipo_cuestionario = document.getElementById('tipo_cuestionario').value;
//alert(id_cuestionario);
var estadoguardar = document.getElementById('estado_guardar');
var titlemic = document.querySelector('#nombre_cuestionario');
estadoguardar.title="Sin Guardar";
estadoguardar.className='icon-sga-warning';
titlemic.style.color = "orange";
ajax=nuevoAjax();
ajax.open("POST","?guardar_cuestionario",true);
ajax.setRequestHeader("Content-type","application/x-www-form-urlencoded");
	ajax.onreadystatechange=function() {
		if (ajax.readyState==4) {
		var resp = ajax.responseText;
		//alert2(resp);
		
		if (resp=="1"){
			estadoguardar.title="Guardado";
			estadoguardar.className='icon-sga-success';
			titlemic.style.color = "#000";
		}else{
			estadoguardar.title="Sin Guardar";
			estadoguardar.className='icon-sga-warning';
			titlemic.style.color = "orange";
		}
		}
	}
ajax.send("id="+id_cuestionario+"&nombre="+nombre_cuestionario+"&tipo_cuestionario="+tipo_cuestionario);
enviarFormulario('guardar_cuestionario.php','form_cuestionario','contenido_ajax');
<?php if (!isset($_GET['embebido'])){ ?>
if(auto==0){
	setTimeout(function(){
parent.buscar_cuestionario();
		}, 3000);
}
<?php } ?>
*/
}
function guardar_cuestionario(e){
tecla=(document.all) ? e.keyCode : e.which; 
    if (tecla==83 && e.ctrlKey){//ctrl + s -> Guardar cuestionario
    document.activeElement.blur();//quita el foco del elemento para poder guardar
    e.preventDefault();//evita otras acciones para esta combinación
	guardar_cuestionario_auto_est();//llama a la funcion guardar
	}
}
</script>
<span class="estado_guardado">
<span onclick="guardar_cuestionario_auto_est(1)"  id="estado_guardar" class='icon-sga-success' title="Guardado"></span>
</span>
<form id="form_cuestionario" method="post" action="guardar_cuestionario.php" onsubmit="return validar_form();">
<script src="../comun/js/disenador.js"></script>
<div class="container">
	
<section id="disenador">
<center>
    		<?php if (!isset($_GET['embebido'])){ ?>
    		<!--header>
				<h1>Diseñador de Cuestionarios</span></h1>
			</header->
<?php } ?>
<!--p>Eliminar <input type="image" src="img/eliminar.png" onClick="confirmeliminar2('cuestionario.php',{'del':'<?php echo $row['id'];?>'},'<?php echo $row['id'];?>');" value="Eliminar"></p><br-->
<?php if (!isset($_GET['embebido'])){ ?>

<?php } ?>
<!--p><label>Creada por: </label><?php #echo $row['usuario']?></p-->
<p><input readonly name="id" type="hidden" id="id_cuestionario" placeholder="Id" value="<?php echo $row['id']?>"></p>
<p><label title="mic">Título del Cuestionario:</label><br>
<?php
if (isset($_GET['embebido'])){
?>
<p align="center">
<input type="button" onclick="guardar_cuestionario_auto_est(0);" value="Guardar">
</p>
<?php
}
?>
<input class="cambios" name="nombre_cuestionario" id="nombre_cuestionario" type="text" style="font-size: 34px" placeholder="Título de la cuestionario" value="<?php echo $row['nombre']?>"></p>
<br>
<p><label>Fecha:&nbsp;&nbsp;<?php echo formatofecha($row['fecha'])?></p>
<?php
$sql2= "SELECT distinct(`tipo_cuestionario`) FROM `cuestionario`;";
?>
<p><label for="tipo_cuestionario">Palabras clave:</label>
<input class="cambios" type="text" list="list_tipo_cuestionario" name="tipo_cuestionario" id="tipo_cuestionario" value="<?php 
if (isset($row['tipo_cuestionario'])) echo $row['tipo_cuestionario'];?>">
<datalist id="list_tipo_cuestionario">
<option value="">Seleccione una opci&oacute;n</option>
<?php
$consulta2 = $mysqli->query($sql2);
while($row2=$consulta2->fetch_assoc()){ ?>
<option><?php $row2['tipo_cuestionario'] ?></option>
<?php } ?>
</datalist></p>
</center>
<?php
}//fin if query
?>
</section>
<?php
$sql_pre = 'SELECT `preguntas_cuestionario`.`id_preguntas_cuestionario`, `preguntas_cuestionario`.`pregunta`, `preguntas_cuestionario`.`tipo_pregunta`, `preguntas_cuestionario`.`opciones`, `preguntas_cuestionario`.`pregunta`, `preguntas_cuestionario`.`tipo_pregunta`, `preguntas_cuestionario`.`correctas` FROM `preguntas_cuestionario` WHERE `preguntas_cuestionario`.`id_cuestionario` = "'.$datos.'"';
$consulta_pre = $mysqli->query($sql_pre);
$preguntas=array();
while($row_pre=$consulta_pre->fetch_assoc()){
$preguntas[]=$row_pre;
}
?>
<div id="preguntas" preguntas="<?php echo count($preguntas)?>" style="padding-bottom:60px">
<?php foreach ($preguntas as $id_p => $pregunta){ ?>
<section id="section_pregunta[<?php echo $id_p; ?>]" pregunta="<?php echo $id_p ?>" items="<?php echo $id_p+1 ?>" tipo="<?php $pregunta['tipo_pregunta'] ?>">
	<input type="hidden" name="id_preguntas_cuestionario[<?php echo $id_p ?>]" value="<?php echo $pregunta['id_preguntas_cuestionario'] ?>">
	<p>Tipo de Pregunta: <span><select id="cambiar_tp_<?php echo $id_p ?>" onchange="cambiar_tipo_pregunta(this.value,'<?php echo $id_p ?>');">';
<?php echo $tipos_pregunta; ?>
</select></span>
	<script>
	document.getElementById('cambiar_tp_<?php echo $id_p ?>').value = '<?php echo $pregunta['tipo_pregunta']; ?>';
	</script>
	</p>
	<input type="text" name="tipo_pregunta[<?php echo $id_p ?>]" value="<?php echo $pregunta['tipo_pregunta'] ?>" style="display: block;">
	<input class="cambios" id="texto_pregunta[nombre][<?php echo $id_p ?>]" name="texto_pregunta[nombre][<?php echo $id_p ?>]" placeholder="Título de la pregunta" style="width: 100%;" required="" value="<?php echo $pregunta['pregunta'];
	#echo "-";
	#echo htmlentities($pregunta['correctas']);
	#$correctas = json_decode($pregunta['correctas'],true);
	#print_r($correctas);
	?>"><input onclick="eliminar_opcion('section_pregunta[<?php echo $id_p ?>]','<?php echo $pregunta['id_preguntas_cuestionario'] ?>')" type="button" value="Quitar Pregunta">
	<input onclick="duplicar_pregunta(<?php echo $id_p ?>);" type="button" value="Copiar Pregunta">
	<ul id="pregunta[<?php echo $id_p ?>]">
		<?php 
		if ($pregunta['opciones']!="null"){
		$opciones = json_decode($pregunta['opciones'],true);
		$correctas = json_decode($pregunta['correctas'],true);
		if(count($opciones)>0)
		foreach ($opciones as $id_op => $opcion) { ?>
		<li id="li[<?php echo $id_p; ?>][<?php echo $id_op; ?>]" style="text-align: left;">
		<?php 
		#echo $id_op."<br>".$pregunta['correctas']; 
		#echo ($correctas) ?>
		<input value="<?php echo $id_op; ?>" type="<?php 
		if ($pregunta['tipo_pregunta']=="radio") echo "checkbox";
		elseif ($pregunta['tipo_pregunta']=="select") echo "checkbox";
		elseif ($pregunta['tipo_pregunta']=="checkbox") echo "checkbox";
		else echo "checkbox";
		?>" <?php
		if (!empty($correctas)){
		if (in_array($id_op,$correctas)) echo " checked ";
		}
		?> name="texto_pregunta[op][<?php echo $id_p ?>][]" id="texto_pregunta[op][<?php echo $id_p ?>][<?php echo $id_op ?>][<?php echo $id_op ?>]"><input class="cambios" type="text" value="<?php echo $opcion ?>" name="texto_pregunta[txtop][<?php echo $id_p ?>][<?php echo $id_op ?>]" id="texto_pregunta[txtop][<?php echo $id_p ?>][<?php echo $id_op ?>]"><input type="button" value="Quitar Opción" onclick="eliminar_opcion('li[<?php echo $id_p ?>][<?php echo $id_op ?>]')"><input type="button" value="Duplicar Opción" onclick="nueva_opcion('<?php echo $id_p ?>','<?php echo $pregunta['tipo_pregunta'] ?>','<?php echo $id_op ?>',document.getElementById('texto_pregunta[txtop][<?php echo $id_p ?>][<?php echo $id_op ?>]').value)"></li>
		<?php
		}
		#print_r($correctas);
		}
		?>
		</ul>
		<?php if ($pregunta['tipo_pregunta']=="radio" or $pregunta['tipo_pregunta']=="checkbox" or $pregunta['tipo_pregunta']=="select"){ ?>
		<input type="button" value="Nueva Opción" onclick="nueva_opcion('<?php echo $id_p ?>','<?php echo $pregunta['tipo_pregunta'] ?>','1');">
		<?php } ?>
		</section>
<?php } ?>
</div>
<?php if (count($preguntas)==0){ ?>
<script>nueva_pregunta("input");</script>
<?php } ?>
<p>
    <label for="tipo_pregunta">Nueva Pregunta:</label>
    <select id="tipo_pregunta"  required>
        <?php
        foreach ($array_tipo_pregunta as $id_te=>$nombre_te){
        echo '<option value="'.$id_te.'"';
        if($id_te=="input") echo " selected ";
        echo '>'.$nombre_te.'</option>';
        }
        ?>
    </select>
    <input onclick="nueva_pregunta(document.getElementById('tipo_pregunta').value);" type ="button" value = "Insertar">
</p><br><label><input id="verificar" type="checkbox">&nbsp;&nbsp;He terminado</label> <br><input type="submit" value="Guardar Diseño cuestionario">
<br><br><br><br>

</div>
<?php if (isset($_GET['embebido'])){ ?>
<input name="embebido" type="hidden" value="SI">
<?php }else{ ?>
<input name="embebido" type="hidden" value="NO">
<?php } ?>
</form>
<?php if (!isset($_GET['embebido'])){ 
/*
?>
<input type="button" value="Guardar Diseño cuestionario"  
                    onclick="guardar_cuestionario_auto_est(0);"/> 
<div id="contenido_ajax" style="display:none"> 
        
</div>
<?php
*/
} ?>
</div>
<script src="../comun/js/svgcheckbx.js"></script>
<script>
/*
$(function(){
var mistooltip = document.querySelectorAll('[title]');
for (i=0;i<mistooltip.length;i++){
mistooltip[i].addEventListener("mousemove", function(){
    this.setAttribute("data-toggle","tooltip");
    $('[data-toggle="tooltip"]').tooltip(); 
    
});
}
});
*/
var mis_cambios = document.querySelectorAll(".cambios");
for (i=0;i<mis_cambios.length;i++){
var menucont = mis_cambios[i];
mis_cambios[i].addEventListener("change", function(){
estado_sin_guardar();
});
}
</script>
<?php
$contenido = ob_get_clean();;
if (isset($_GET['embebido'])){
echo $contenido;
}else{
require("../comun/plantilla.php");
}
?>