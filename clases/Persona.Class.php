<?php

class Persona extends Clase_mysqli{



	public $id_persona ;

	public $usuario;

	public $clave;

	public $mascota;

	public $nombre;

	public $apellido;

	public $rol;

	public $foto;

	public $direccion;

	public $telefono;

	public $correo;

	public $ultima_sesion;

	public $num_visitas;

	public $puntos;

	public $estado;

	public $tipo_sangre;

	public $genero;

	public $observaciones;
  public $fecha_nacimiento;
  public $id_usuario;





  public function __SET($atributo, $valor) {
    $this->$atributo = $valor;
}

function __construct($id_persona='')  

{ 

   

    if($id_persona!=''){

    $this->id_persona=$id_persona;

   $this->datos_persona();

    }

}//Fin Constructor

public function permiso_home(){
  $tarjetas=array();
  $sql="SELECT *
FROM tarjeta
ORDER BY CASE
    WHEN id_tarjeta = 67 THEN 0  -- Coloca la tarjeta 67 primero
    ELSE 1                       -- El resto después
  END";
  $datos = json_decode($this->consultar_datos($sql,true),true);
  foreach($datos as $clave => $campo){
      if(in_array($_SESSION['rol'],json_decode($campo['permisos'],true))){
      $tarjetas[]=$campo;
    }
  }  
  return $tarjetas;
}


public function acudiente(){
  $sql='select * from acudiente_estudiante where id_estudiante="'.$this->id_persona.'" order by parentesco desc';
  return $datos = json_decode($this->consultar_datos($sql,true),true);

}
public function verificar_rol_acudiente($persona,$estudiante){
  $sql=' SELECT IF(parentesco="padre" or parentesco="madre" , "1", "0") as parentesco
  from acudiente_estudiante where id_acudiente="'.$persona.'" and id_estudiante="'.$estudiante.'" and (parentesco="padre" or parentesco="madre") ';
return $datos = json_decode($this->consultar_datos($sql,true),true);
}

public function probabilidad_sexo($nombre){

$nombre = explode(" ",$nombre); 

$nombre= $nombre[0]; 

$sql="SELECT count(genero)   FROM `usuario` WHERE lower(`nombre`) like lower('%".$nombre."%') and genero ='m' union SELECT count(genero)   FROM `usuario` WHERE lower(`nombre`) like lower('%".$nombre."%') and genero ='f'";

$datos = json_decode($this->consultar_datos($sql,true),true);

$m=0;

$f=0;

#echo $sql.'<br>';

if(empty($datos[0]['count(genero)'])){

  $datos[0]['count(genero)']=0;

}

if(empty($datos[1]['count(genero)'])){

  $datos[1]['count(genero)']=0;

}



#echo 'f'. $f.'<br>';

if($datos[0]['count(genero)']>$datos[1]['count(genero)']){

	return  'm';

}elseif($f<=$m or empty($m)){

 	return 'f';

}

}





public  function validar_acudiente(){

if($_SESSION['rol']=='acudiente' and (!isset($_SESSION['hijo']))){

header('location:'.SGA_USUARIO_URL.'/elegir_hijo.php');

}

}

public function validacion_roles(){

	$sql='';

}



public function datos_persona($todos=0){

 $sql="select * from usuario";

if(!empty($this->id_persona) or $todos==0 ){

$sql.=" where usuario='".$this->id_persona."'  or id_usuario='".$this->id_persona."'";

}



 $datos = json_decode($this->consultar_datos($sql,true),true);



 if(!empty($datos[0]) and $todos==0 ){

foreach ($datos[0] as $clave => $value) {

  $this -> __SET($clave,$value);

  #print_r($clave).'/'.print_r($value);

}  

 }else{

  return $datos;

 }





}





public function validar_rol($rol){

	$sql='SELECT * FROM `usuario` WHERE id_usuario="'.$this->id_persona.'" and `rol` like "%'.$rol.'%"';

	return $info_user = json_decode($this->consultar_datos($sql,true),true);



}

public function actualizar_visitas($ultima_sesion_fecha){

	if ($ultima_sesion_fecha != date('Y-m-d')){

$sql= "UPDATE `usuario` SET `num_visitas`=`num_visitas`+1 , `puntos`=`puntos`+1, ultima_sesion`='".date('Y-m-d')."' WHERE `id_usuario` = '".$this->id_persona."'";

return $this->query_insertar($sql);	

}

}



public function login(){

 $sql='SELECT * from usuario WHERE `clave` = "'.$this->clave.'" and `usuario` = "'.$this->usuario.'" and estado="activo" ';

return $info_user = json_decode($this->consultar_datos($sql,true),true);



}

public  function listado_rol($rol="docente"){

 $sql = 'SELECT * FROM usuario where usuario.rol like "%'.$rol.'%" order by usuario.apellido asc';

$info_docente = json_decode($this->consultar_datos($sql,true),true);

?>

<datalist id="suggestionList">

  <?php 

foreach ($info_docente as $key => $rowes) {?>

   <option label ="<?php echo $rowes['id_usuario']; ?>" data-value="<?php echo $rowes['id_usuario'] ; ?>" ><?php echo $rowes['nombre'].' '.$rowes['apellido']  ; ?></option>

<?php 

}

?>

</datalist>

<?php

}









public function consultar_asistencia($asignacion="",$fecha="") {

$sql='SELECT * FROM `asistencia`, usuario

where asistencia.id_estudiante=usuario.id_usuario and

usuario.estado="activo" ';

if (!empty($this->id_persona)) {

$sql.=' and id_estudiante= "'.$this->id_persona.'" ';

}

if (!empty($asignacion)) {

$sql.=' and id_materia= "'.$asignacion.'"';

}

if (!empty($fecha)) {

$sql.=' and fecha= "'.$fecha.'"';

}

#echo $sql;

return $datos = json_decode($this->consultar_datos($sql,true),true);

}

public static function autorregistro(){

    require("../comun/conexion.php");

    $sql ='select * from config';

    $consulta = $mysqli ->query($sql);

    $extensiones = array();

    $row=$consulta->fetch_assoc();

    if (isset($row['autoregistrarse']) and $row['autoregistrarse']!=""){

    $roles = (explode(",",$row['autoregistrarse']));

    return $roles;

    }else{

    return array();

    }

}



public static function cerrar_sessiones(){

  @session_start();

  unset($_SESSION['barra_busqueda']);

	unset($_SESSION['login']);

if (isset($_GET['logout'])){

    unset ($_SESSION['clave']);

    unset ($_SESSION['apellido']);

    unset ($_SESSION['id_usuario']);

    unset ($_SESSION['usuario']);

    unset ($_SESSION['foto']);

    unset ($_SESSION['rol']);

    unset ($_SESSION['roles']);

    unset ($_SESSION['num_visitas']);

    unset ($_SESSION['puntos']);

    session_destroy();

    header("Location: ../index.php");

}

}



#############





public function datalist($input,$datos="datos_persona"){

  ?>

<datalist id="<?php echo $input; ?>">

<?php foreach ($this->$datos(1) as $key => $campo) {

if(!empty($campo['id_usuario']) and $campo['id_usuario']<>1){ ?>

<option label="<?php echo $campo['nombre']; ?>" value="<?php echo $campo['id_usuario']; ?>">

</option> 

<?php

}

}

echo "</datalist> ";

}

public function graficar($datos,$titulo="Gráfico",$tipo="PieChart",$ancho="700",$alto="300"){ 

?> 

<div id="<?php echo $titulo ?>" style="width: <?php echo $ancho ?>px; height:<?php echo $alto ?>px;"></div>

<?php

$opciones="['Opción', '$titulo'], ";

if(count($datos)<=2 and $tipo<>"ColumnChart"){

foreach ($datos as $etiqueta => $valor) {

        $opciones.="['".$etiqueta."',".$valor.'],'; 

}	

}else{

	/*

  foreach ($datos as $etiqueta => $valor) {

	for ($i=0; $i <count($valor) ; $i++) { 

		$mikey0=(array_keys($valor)[0]);

		$mikey1=(array_keys($valor)[1]);

		$opciones.="['".$valor[$mikey0]."',".$valor[$mikey1].'],'; 

	}

	}	*/



foreach ($datos as $key => $value) {

  $a=array_keys($value)[0];

  $b=array_keys($value)[1];

  $opciones.="['".$value[$a]."',".$value[$b].'],'; 

}

}











#tipos

#LineChart

#ColumnChart

#PieChart

#ColumnChart

 ?>

    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>

    <script type="text/javascript">

      google.charts.load('current', {'packages':['corechart']});

      google.charts.setOnLoadCallback(GraficoPastel);

      function GraficoPastel() { 

        var datos = google.visualization.arrayToDataTable([

          <?php echo $opciones; ?>

        ]);

        var Opciones = { 

          title: '<?php echo $titulo; ?>',

          is3D: true,

        }; 

        var Gráfico = new google.visualization.<?php echo $tipo ?>(document.getElementById('<?php echo $titulo; ?>'));

        Gráfico.draw(datos,Opciones);

      }

    </script>

<?php } 





public function distribución_genero(){

 $sql='SELECT count(`genero`) as femenino,(select count(`genero`) FROM `usuario`,seguimiento WHERE

seguimiento.identificacion=usuario.id_usuario and ';

if ($_SESSION['rol']=="docente") {

 $sql.=' seguimiento.docente="'.$_SESSION['id_usuario'].'" and ';

}



$sql.=' `genero` = "m") as masculino FROM `usuario`,seguimiento WHERE ';

if ($_SESSION['rol']=="docente") {

 $sql.=' seguimiento.docente="'.$_SESSION['id_usuario'].'" and ';

}



$sql.=' seguimiento.identificacion=usuario.id_usuario and 

  `genero` = "f"';

$datos= $this->consultar_datos($sql,true);

$datos=json_decode($datos,true);

return($datos);

}



public function estudiantes_con_mayor_Asesoria($limite="5"){

 $sql='SELECT usuario.nombre,COUNT(seguimiento.identificacion) as maximo FROM seguimiento INNER join usuario on seguimiento.identificacion= usuario.id_usuario ';

	@session_start();

if($_SESSION['rol']=="docente"){

	$sql .= " and seguimiento.docente=".$_SESSION['id_usuario']."  ";

}

  $sql.='GROUP BY seguimiento.identificacion order by maximo desc limit '.$limite.'';

#  echo $sql;

$datos= $this->consultar_datos($sql,true);

$datos=json_decode($datos,true);

return($datos);

}



public  function cantidad_personas(){

$sql=' select count(*) as cantidad from usuario';

$datos= $this->consultar_datos($sql,true);

$datos=json_decode($datos,true);

return($datos[0]['cantidad']);

}



public function cantidad_asesorias_persona(){

	$sql=' select count(*) as cantidad from seguimiento where identificacion= "'.$this->id_persona.'"';

	$datos= $this->consultar_datos($sql,true);

	$datos=json_decode($datos,true);

		return($datos[0]['cantidad']);

}





public function datos_contenido($input=""){

	$sql='select * from contenido';

	$datos= $this->consultar_datos($sql,true);

	$datos=json_decode($datos,true);

	?><datalist id="<?php echo $input; ?>"> <?php

 foreach ($datos as $key => $campo) {?>

<option autocomplete="off" value="<?php echo $campo['contenido']; ?>"><?php echo $campo['contenido']; ?></option>

 <?php  } ?>

 </datalist> 

  <?php

}



public function consultar_datos_usuario($usuario){
  if($usuario<>""){
    $roles = "";
    $resultado="user-icon.png";
    $sql = "SELECT * FROM `usuario` WHERE `usuario` = '$usuario' or `id_usuario` = '$usuario'";
    $roles = array();
    $roles2 = array();
$array_roles = array("admin"=>"Administrador","docente"=>"Docente","estudiante"=>"Estudiante","acudiente"=>"Acudiente");
//if ($mysqli->num_rows>0){
$row = json_decode($this->consultar_datos($sql,true),true)[0];
    if ($row){
    $roles = explode(",",$row['rol']);
    if (count($roles)>0)
    foreach ($roles as $id => $rol){
    $roles2[$rol] = $array_roles[$rol];
    }
    $resultado = $row['foto'];
    if ($row['foto'] !=''){
    $resultado = $row['foto'];
    }else{
    $resultado = "user-icon.png";
    }
    } 
    $resultado = $resultado!="" ? $resultado : "user-icon.png";
    $mifoto = $resultado;
    $ruta_foto = SGA_MEDIA_FOTO.'/'.$mifoto;
 if($row['id_usuario'] != "" and $row['mascota']=='SI'){
 $sql_figura = 'select * from figuras where sha1(concat(figura,"SGA"))="'.$row['clave'].'" ' ;
#echo $sql_figura;
$row_figura = json_decode($this->consultar_datos($sql_figura,true),true)[0];
    if($row_figura){
 $array22[]    = array("id_figuras" => $row_figura['figura'] ,"figura"=>$row_figura['figura'],"imagen_figura" =>$row_figura['imagen_figura']);
     }

     $sql_mascotas ='select * from figuras ' ; 

if(isset($row['clave']) and $row['clave'] <> ""){

$sql_mascotas.='where sha1(concat(figura,"SGA"))<> "'.$row['clave'].'" ' ;

}

$sql_mascotas.='ORDER BY figura limit 4 '; 



//$consulta_mascotas = $mysqli ->query($sql_mascotas);

$consulta_mascotas = json_decode($this->consultar_datos($sql_mascotas,true),true);
//print_r($consulta_mascotas);

foreach ($consulta_mascotas as $key ) {

$array22[]=$key;

}

shuffle($array22);

$contador = 0;

foreach ($array22 as $clave =>$llave) {

$contador++;

@session_start();  
@$nombre_mascota = $row['figura'];
if($row['clave']==Comun::encriptar($llave['figura'])){
    $datainfo = $row['clave'];
}
else{
    $datainfo = sha1(uniqid());
}

$img[$contador] = " <img class='mascotas_login' title='".$llave['figura']."' style='border-radius:120px;' id='mascota_".$contador." height='100px' width='120px' name='' onclick='login_para_boy(this); ";
$img[$contador].="' src='../comun/img/figuras/".$llave['imagen_figura']."' data-info='".$datainfo."' hola></img>";

 }

 }

$datos = array('clave'=>$row['clave'],'nombre'=>$row['nombre'],'apellido'=>$row['apellido'], 'roles'=>$roles2, 'foto'=>$ruta_foto

, 'mascota'=>$row['mascota'],'usuario'=>$row['usuario']);



 if(empty($array22)) {

$array_resultante= array_merge($datos);  

 }else{

  $array_resultante= array_merge($datos,$array22,$img);

 }

 if(empty($img)) {

$array_resultante= array_merge($datos);  

 }else{

  $array_resultante= array_merge($datos,$array22,$img);

 }
     return  $array_resultante;   
}

}





}



