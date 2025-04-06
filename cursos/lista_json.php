<?php




function listarcursoasignatura2 ($curso,$asignatura,$periodo) {
require ("../comun/conexion.php");
$sql_periodos = "SELECT count(`periodo`) as num_per FROM `periodo` WHERE `ano_lectivo` = '".date("Y")."'";
if($consulta_periodos = $mysqli->query($sql_periodos)){
if ($resultado_periodos = $consulta_periodos->fetch_assoc()){
 $numperiodos = $resultado_periodos['num_per'];
   setcookie('numperiodos',$numperiodos);                                                        }
}
require ("../comun/conexion.php");
 $sql = 'SELECT `id_estudiante`,`nombre`, `apellido` from estudiante where id_curso = "'.$curso.'" '; // and $id_año_lectivo = $año; 
 
if($consulta = $mysqli->query($sql)){//ok
$estudiantes = array();
while ($resultado = $consulta->fetch_assoc()){
$cedula = $resultado['id_estudiante'];
$estudiante = array("Identificación"=>$resultado['id_estudiante'],"Nombre"=>$resultado['nombre'],"Apellido"=>$resultado['apellido']);
for ($i=1;$i<=$_COOKIE['numperiodos'];$i++){
$periodo = $i;
$estudiante ["Periodo".$i] = '<input type="text" size="4" onpaste="return soloNumeros(event)" onchange="promediar(\''.$cedula.'\');" onkeydown="return soloNumeros(event)" onkeyup="promediar(\''.$cedula.'\');" class="'.$cedula.'" name="'.$cedula.'['.$periodo.']" id="'.$cedula.'['.$periodo.']" value = ""/ >';
//'<input value = "" type = "number" />';
}
$estudiante ['Final'] = '<span id="final_'.$cedula.'"></span>';
$estudiantes [] = $estudiante;
}//fin while
if (isset($_GET['json'])){
  json_encode($estudiantes);   
}
}else{//fin if($consulta = $mysqli->query($sql))
//echo "no hay consulta";
}
}		
if(isset($_POST['user'],$_POST['pass'])){
  //print_r($_POST);
   login($_POST['user'],$_POST['pass']);
}
function listarcursoasignatura ($curso,$asignatura,$periodo) { ?>
<meta charset = "utf8"/>
<?php
//print 'Asignatura'.$asignatura.' Curso'.$curso."<BR>";
$sql = 'SELECT `estudiante`.`id_estudiante`,`estudiante`.`nombre`, `estudiante`.`apellido`, `notas`.notas, `notas`.`nota_final` FROM estudiante LEFT JOIN notas ON `estudiante`.`id_estudiante` = `notas`.`id_estudiante` '; // and $id_año_lectivo = $año; 
require 'connexion.php';
if($consulta = $mysqli->query($sql)){//ok
//    echo $_COOKIE['numperiodos'];
$estudiantes = array();
while ($resultado = $consulta->fetch_assoc()){
$notas[$resultado['id_estudiante']] =  json_decode($resultado['notas'], true);
//echo array2inputs($notas);
//print_r($notas);
?>

<?php
for ($i=1;$i<=$_COOKIE['numperiodos'];$i++){
    ?>
<input type="text" size="4" onpaste="return soloNumeros(event)" onchange="promediar('<?php echo $cedula; ?>');" onkeydown="return soloNumeros(event)" onkeyup="promediar('<?php echo $cedula; ?>');" class="<?php echo $cedula;?>" name="<?php echo $cedula."[".$i."]"; ?>" id="<?php echo $cedula."[".$i."]"; ?>" value = "<?php echo $nota; ?>"/ >
    <?php
                                            }
$estudiante = array("Identificación"=>$resultado['id_estudiante'],"Nombre"=>$resultado['nombre'],"Apellido"=>$resultado['apellido']);//'<input value = "" type = "number" />');
foreach ($notas[$resultado['id_estudiante']] as $periodo => $nota) {
//echo $numperiodos;
$estudiante[$periodo] = '<input placeholder ="h" name= "'.$resultado['id_estudiante']."[".$periodo."]".'"  value = '.$nota.' type = "number" />';
}
$promedio = ('<input disabled  id="final_'.$cedula.'" value = "'.$promedio.'">');
$estudiante['promedio'] =$promedio;
 $estudiantes [] = $estudiante;
}//fin while
if (isset($_GET['json'])){
  json_encode($estudiantes); ?> <table align ="center" border = "2" >
                 <tr> <?php
     
foreach ($estudiantes as $indice => $valor) { 
                foreach ($valor as $Nombre1 => $valor1) {
                                  echo $Nombre1 ;?>
                                  <td> <?php
                                echo $valor1 ;
                            ?> </td><?php
                                                                }
                                                               
                                               
                                            }                           

?></table><?php


}
}else{//fin if($consulta = $mysqli->query($sql))
//echo "no hay consulta";
}
}
function logout () {
//sesion start
if (version_compare(phpversion(), '5.4.0', '<')) {//valida la instancia de función session start
     if(session_id() == '') {
        session_start();
     }
 }
 else
 {
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
 }
//sesion start
unset ($_SESSION['nombre_usu']);
unset ($_SESSION['identificacion_usu']);
unset ($_SESSION['id_usu']);
session_destroy();
header("location: "."//".$_SERVER['SERVER_NAME']."/index.php");
}
function login ($user,$pass) {
 require '../comun/conexion.php';
    $sql0 = 'select * from admin where usuario = "'.$user.'" and clave = "'.$pass.'"' ;
$consulta0 = $mysqli -> query ($sql0);
if ($row0 = $consulta0 -> fetch_assoc()) {
  session_start();
    $_SESSION['nombre_usu'] =$row0['usuario'] ;
      echo $_SESSION['rol']  = $row0['rol'];
         if ($_SESSION['rol']=='superadmin') { 
        	header('location:../php/mis_cursos.php');
                                            }
                  elseif ($_SESSION['rol']=='admin') {
         	header('location:../php/mis_cursos.php');

                
                                                    }
                    
                                        }
    else {
 $sql = 'SELECT `identificacion`,`nombre`,apellido,REPLACE(`identificacion`, `identificacion`,"estudiante")
 as `rol` FROM `estudiante` where (identificacion = "'.$user.'" and clave = "'.$pass.'") 
 union SELECT `id_docente`, `nombre_docente`,apellido_docente,REPLACE
 (`id_docente`, `id_docente`, "docente") as `rol` FROM  `docente` where 
 (id_docente = "'.$user.'" and clave = "'.$pass.'") ';
require("../comun/conexion.php");
$consulta =$mysqli -> query ($sql);
if ($row =  $consulta -> fetch_assoc())
{
//sesion start
if (version_compare(phpversion(), '5.4.0', '<')) {//valida la instancia de función session start
     if(session_id() == '') {
        session_start();
     }
 }
 else
 {
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
 }
//sesion start
	$_SESSION['nombre_usu'] = $row['nombre']." ".$row['apellido'];
 $_SESSION['identificacion_usu'] = $row['identificacion'];
   echo  $_SESSION['rol']  = $row['rol'];
	#header('location:../modulo/seleccionar_listas.php');
 session_start(); if ($_SESSION['rol']=='docente') { 
	header('location:../php/mis_cursosd.php');
                                                    }
else                                                  {
    	header('location:../php/mis_cursos.php');

                                                        
                                                    }
}
else{ ?>
 <script type="text/javascript">
alert('verifique sus datos y vuelva a intentar');
window.location = "../index.php" ;
 </script>
<?php 
}
    }//fin función de si no encontró admin
}
function login_est ($user,$pass) {
echo $sql = 'SELECT `identificacion`,`nombre`,apellido,REPLACE(`identificacion`, `identificacion`,"estudiante") as `rol` FROM `estudiante` where (identificacion = "'.$user.'" and clave = "'.$pass.'") union SELECT `id_docente`, `nombre_docente`,apellido_docente,REPLACE(`id_docente`, `id_docente`, "docente") as `rol` FROM  `docente` where (id_docente = "'.$user.'" and clave = "'.$pass.'") ';
require("../comun/conexion.php");
$consulta =$mysqli -> query ($sql);
if ($row =  $consulta -> fetch_assoc())
{
//sesion start
if (version_compare(phpversion(), '5.4.0', '<')) {//valida la instancia de función session start
     if(session_id() == '') {
        session_start();
     }
 }
 else
 {
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
 }
//sesion start
	$_SESSION['nombre_usu'] = $row['nombre']." ".$row['apellido'];
 $_SESSION['identificacion_usu'] = $row['identificacion'];
    $_SESSION['rol']  = $row['rol'];
	#header('location:../modulo/seleccionar_listas.php');
//	header('location:../php/notas.php');
}
else{
 // header('location:../index.php');
}
    
}

//funcion listar cursos
function curso($pre = ""){
    if (isset($_SESSION['id_usu'])){
$condicionsql =  "and asignacion.id_curso =".$_SESSION['id_usu'];
}
else {
 $condicionsql = '';
}
	require ("../comun/conexion.php");
	
$sql = 'select distinct  curso.id_curso,curso.nombre_curso 
	from asignacion, curso  where
	curso.id_curso = asignacion.id_curso and id_docente = "'.$_SESSION['identificacion_usu'].'" '.$condicionsql;
	if($consulta = $mysqli->query($sql))
	{
	?>
	<select name='curso' id='curso' onchange="asignatura(this.value); grabarcookie('curso',this.value);asignacion();document.getElementById('txtsugerencias').innerHTML=''" required>
    <?php
echo "<option value=''>Seleccione un curso</option>";
		$departamento=array();
		while($row=$consulta->fetch_assoc())
		{
		$id=$row["id_curso"];
		$departamento=$row["nombre_curso"];
		echo "<option value='".$id."' ";
		if ($pre == $id) echo "selected";
		echo">".$departamento."</option>";
		}//fin while
	echo '</select>';
	}//fin if
}//fin function departamento
function asignatura($cur,$pre=""){
    session_start();
	require ("../comun/conexion.php");
	$sql = "select distinct asignatura.id, asignatura.nombre_asignatura from asignacion, asignatura where asignacion.id_docente = ".$_SESSION['identificacion_usu']." and asignatura.id = asignacion.id_asignatura and asignacion.id_curso = ".$cur." ";
//	echo $sql."<br>";
	?>
	<select name='asignatura' id='asignatura' onchange="grabarcookie('asignatura',this.value);asignacion();buscar(document.getElementById('buscar').value);" required>
	<option value=''>Seleccione una asignatua</option>
	<?php
	if($consulta = $mysqli->query($sql))
	{
		$municipio=array();
		while($row=$consulta->fetch_assoc())
		{
		$id=$row["id"];
		$municipio=$row["nombre_asignatura"];
		echo "<option value='".$id."'";
		if ($pre == $id) echo " selected ";
		echo ">".$municipio."</option>";
		}//fin while
	}
	echo '</select>';
	
}//fin function municipio


function listarañoslectivos(){
$sql = 'select * from año_lectivo ';
require '../comun/conexion.php' ; ?>
<select name = "id_año_lectivo"  >
<?php 
$consultar = $mysqli -> query ($sql);
while ($row = $consultar -> fetch_Assoc()){?>
<option value = "<?php print $row['id_año_lectivo']; ?>"> <?php echo $row['año_lectivo']; ?></option>
	  <?php }  ?>
</select>
<!-- función insertar -->

<?php
function insertar () {
if (isset($_POST['nombre'])){
$concatenakey = "";
$concatenavalue="";
 foreach($_POST as $key => $value) { //recorre todos los elementos del form
          $concatenakey =$concatenakey.$key."," ; // concatena todos los nombres de los campos de las  cajas de texto
          $concatenavalue = $concatenavalue."'".$value."'"."," ;// concatena todos los valoes s de los campos de las  cajas de texto
          $dec = substr ($concatenakey, 0, strlen($concatenakey) - 1); // eliminamos la ultima coma de de la linea concatenada de la variable $consultakey
          $valores = substr ($concatenavalue, 0, strlen($concatenavalue) - 1); 
          // eliminamos la ultima coma de de la linea concatenada de la variable $consultavalue    
                                       }
 if ( $key !=  "enviar" )   {
 print    $sql ='INSERT INTO estudiante ('.$dec.') VALUES ( '.$valores.')' ;
    require '../comun/conexion.php' ;
   $insertar = $mysqli -> query ($sql);
   require '../js/funciones.js' ; 
   header('location:../php/mis_cursosd.php');
                          }
                                }
}         
}
//funciones para guardar las notas
function notafinal($array){
    $promedio=array_sum($array)/count($array);  
    return $promedio;
}
function array2inputs($estudiantes){//recibe un array de php y genera inputs para ingresar o modificar
//print_r ($array);
?>
<script src="../js/funciones.js"></script>
<!--form method = "POST" action=""--> <?php
foreach ($estudiantes as $cedula => $nota) {
$array = json_decode($nota);
$num = 0;
?><!--p--><?php //contenteditable="true"
foreach ($array as $periodo => $nota) {
?>
    <?php    $periodo1 = substr( $periodo, -1); //Corta la cadena para mostrar la palabra en label  ?>
    <!--label ><?php echo 'Periodo '.$periodo1; ?></label-->
    <input type="text" size="4" onpaste="return soloNumeros(event)" onchange="promediar('<?php echo $cedula; ?>');" onkeydown="return soloNumeros(event)" onkeyup="promediar('<?php echo $cedula; ?>');" class="<?php echo $cedula;?>" name="<?php echo $cedula."[".$periodo."]"; ?>" id="<?php echo $cedula."[".$periodo."]"; ?>" value = "<?php echo $nota; ?>"/ >
<?php
$num++;
} //fin foreach
?>
<!--a onclick="promediar('<?php echo $cedula; ?>');">Promedio</a-->
<span id="final_<?php echo $cedula; ?>"></span>
<!--/p-->
<script>
promediar('<?php echo $cedula; ?>');
</script>
<?php
} //fin foreach
?>
<!--input type="submit" value= "guardar"/>
</form-->
<?php
}//array2inputs()
function inputs2array($estudiantes){//recibe los datos post
$datos = array();
foreach ($estudiantes as $cedula => $notas) {
$datos[$cedula] = $_POST[$cedula];
$datos[$cedula]["Nota_Final"] = notafinal($notas);
//aqui obtengo las n notas de cada estudiante
/*
foreach ($_POST[$cedula] as $periodo => $nota) {
$datos = array();
*/
}
}//inputs2array
function fn_nota2escalanal(){
require ("../comun/conexion.php");
$sql ="SELECT `escala_nal`, `cualitativa`,  `cuantitativai`, `cuantitativaf` FROM `escala`";
$consulta = $mysqli->query($sql);
echo '<script type="text/javascript">
function nota2escalanalfn(input){
var i = parseFloat(input);
var nota;
';
while($row=$consulta->fetch_assoc()){
echo ' if (i<='.$row['cuantitativaf'].' & i>='.$row['cuantitativai'].') nota = "'.$row['escala_nal'].'";
else';
}
echo '
nota = "";
return nota;
}
</script>
';
}
function fn_nota2escalanal_php($nota){
require ("../comun/conexion.php");
$sql ="SELECT  `escala_nal`  FROM `escala` WHERE `cuantitativai` <= ".$nota." and `cuantitativaf` >= ".$nota.";";
$consulta = $mysqli->query($sql);
if($row=$consulta->fetch_assoc()){
return $row['escala_nal'];
}
}
function nota_periodo($periodo,$notas){
$notas_array = json_decode($notas,true);
return $notas_array[date("Y").$periodo];
}
?>
<!-- fin función insertar -->

