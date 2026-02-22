<?php
class Academico extends Clase_mysqli{
public $id_estudiante;
public $ano_lectivo;
public $id_docente;
public $id_asignacion;

public function __SET($atributo,$valor){
	return	$this-> $atributo= $valor ;
}
public function __construct($identificacion=""){
		if ($identificacion!=""){
				$this->id_asignacion = $identificacion;
		#$this->listar_persona();
	}
}


public function periodo_academico(){
  $sql="SELECT *
            FROM periodo
            WHERE fecha_inicio IS NOT NULL AND fecha_inicio != '0000-00-00'
            ORDER BY
                CASE
                    WHEN CURDATE() BETWEEN fecha_inicio AND fecha_fin THEN 1
                    ELSE 2
                END ASC,
                fecha_fin DESC
            LIMIT 1";
  return $datos = json_decode($this->consultar_datos($sql,true),true);
}

public function home_recursos(){
return '';
}
  public function misestudiantes() {
        $datos_curso = $this->mis_cursos_otros(); 
        $asignacion_hoy = $this->consultar_horario(1);

        if (!empty($datos_curso)) {
            echo "<select onchange='listarEstudiantes();' class='form-control mb-2' name='asignaciones' id='asignaciones'>";
            echo '<option value=""> Todos los cursos </option>';
            foreach ($datos_curso as $clave => $valor) {
                echo '<option value="'.$valor['id_asignacion'].'" style="width: 150px; white-space: pre-wrap; word-wrap: break-word;"';
                if (!empty($asignacion_hoy[0]['id_asignacion']) && $asignacion_hoy[0]['id_asignacion'] == $valor['id_asignacion']) {
                    echo ' selected';
                }
                echo '>'.$valor['nombre_materia'].' ('.$valor['mid_categoria_curso'].')</option>';
            }
            echo '</select>';
        } else {
            echo "<p>No hay cursos disponibles.</p>";
        }

        // Nuevo bloque añadido: Selector de rango de grados
        echo '<select onchange="listarEstudiantes();" class="form-control mb-2" name="filtro_grados" id="filtro_grados">';
        echo '<option value="">Todos los grados</option>';
        echo '<option value="6-8">Grados 6 a 8</option>';
        echo '<option value="9-11">Grados 9 a 11</option>';
        echo '</select>';

        echo '
        <input autofocus onfocus="listarEstudiantes();" onkeyup="listarEstudiantes();" type="text" class="form-control mb-2" placeholder="Buscar" id="nombrees" value="">
        <br>
        <div style="height:250px;" id="contenedor"></div>';
    }

public function inscripcion($asignacion){
  $sql='select  * from inscripcion where id_inscripcion="'.$asignacion.'"';
  return $datos = json_decode($this->consultar_datos($sql,true),true);
}

public function NivelEducativo($nivel=0){
  $sql ='select * from categoria_curso ';
  if($nivel<>0){
   $sql.=' where id_categoria_curso="'.$nivel.'"'; 
   $datos = json_decode($this->consultar_datos($sql,true),true);
 return json_decode($datos,true);
  }else{
return $datos = json_decode($this->consultar_datos($sql,true),true);
  }
 
}

public function notasdeclase($asignacion=1,$fechainicial='',$fechafinal=''){
  $sql='select * from edunotas where  id_asignacion="'.$asignacion.'" ';
if($fechainicial<>"" and $fechafinal<>""){
$sql.=' and fecha_nota>="'.$fechainicial.'" and fecha_nota<="'.$fechafinal.'" ';
}
  $sql.='order by fecha_nota,hora_nota desc ';
  return $datos = json_decode($this->consultar_datos($sql,true),true);
}
public function notaspendientes($estado=1){
  $sql='select * from edunotas where  fijar="'.$estado.'" order by fecha_nota,hora_nota asc ';
  return $datos = json_decode($this->consultar_datos($sql,true),true);
}


public function reporte_notas(){
  if(!empty($_POST['id_inscripcion'])){
  $sql='select * from seguimiento_es,actividad,inscripcion where
seguimiento_es.id_actividad=actividad.id_actividad and
seguimiento_es.id_inscripcion="'.$_POST['id_inscripcion'].'" and 
actividad.id_asignacion="'.$_POST['id_asignacion'].'" group by seguimiento_es.id_seguimiento';
return $datos = json_decode($this->consultar_datos($sql,true),true);
  
  }
  
}




public function componente_context_menu($id_asignacion,$nombre_materia){
$rowa['id_asignacion']=$id_asignacion;
$rowa['nombre_materia']=$nombre_materia;
$mat = $this->consultar_materia($id_asignacion);
$curso=($mat[0]->id_curso);
$categoria_curso=($mat[0]->nombre_categoria_curso);

?>
<menu id="menu_curso<?php echo $rowa['id_asignacion'] ?>" style="display:none" class="showcase">
  <command label="<?php echo $rowa['nombre_materia']; ?>" onclick="
  document.location='<?php echo SGA_CURSOS_URL ?>/curso.php?asignacion=<?php echo $rowa['id_asignacion'] ?>'">
  <hr>
  <?php @session_start(); if ($_SESSION['rol']<>"estudiante" and $_SESSION['rol']<>"acudiente" ){ ?>
  
   <command  label="Nuevo RED"  onclick="
  document.location='<?php echo SGA_RED_URL ?>/nuevo_red.php?asignacion=<?php echo $rowa['id_asignacion'] ?>'">


  <command  label="Nueva Planeación"  onclick="
  document.location='<?php echo SGA_PLANEADOR_URL ?>/index.php?asignacion=<?php echo $rowa['id_asignacion'] ?>'">
 
 <command  label="Planeaciones"  onclick="
  document.location='<?php echo SGA_PLANEADOR_URL ?>/planeador.php?idasignacion=<?php echo $rowa['id_asignacion'] ?>'">
  
    <command  label="Nueva Actividad"  onclick="
  document.location='<?php echo SGA_CURSOS_URL ?>/actividad.php?asignacion=<?php echo $rowa['id_asignacion'] ?>'">
  <command label="Nueva Edunota"  onclick="
  document.location='<?php echo SGA_CURSOS_URL ?>/../cursos/edunotas.php?asignacion=<?php echo $rowa['id_asignacion'] ?>'">
  <command label="horario"  onclick="
  document.location='<?php echo SGA_CURSOS_URL ?>/../asistencia/horario.php?asignacion=<?php echo $rowa['id_asignacion'] ?>'">
  <command label="asistencia"  onclick="
  document.location='<?php echo SGA_CURSOS_URL ?>/../asistencia/index.php?asignacion=<?php echo $rowa['id_asignacion'] ?>'">
  <command label="Estudiantes del curso"  onclick="
  document.location='<?php echo SGA_CURSOS_URL ?>/estudiante_curso.php?asignacion=<?php echo $rowa['id_asignacion'] ?>'">
  <command label="Modificar curso"  onclick="
  document.location='<?php echo SGA_CURSOS_URL ?>/modificar_curso.php?asignacion=<?php echo $rowa['id_asignacion'] ?>'">
  <command label="Duplicar curso"  onclick="
  document.location='<?php echo SGA_COMUN_URL ?>/funciones.rutas.php?clonar_curso=<?php echo $rowa['id_asignacion'] ?>'">
     <?php } ?>    
<command target="_BLANK" label="Reporte Valorativo"  onclick="window.open('<?php echo SGA_REPORTES_URL ?>/informe_valorativo.php?asignacion=<?php echo $rowa['id_asignacion'] ?>','_blank')">
<command target="_BLANK" label="Estadisticas"  onclick="window.open('<?php echo SGA_REPORTES_URL ?>/cursos/usuarios.php?id_asignacion=<?php echo $rowa['id_asignacion'] ?>','_blank');">
  <command target="_BLANK" label="Salir"  onclick="return 'context-menu-icon context-menu-icon-quit'"> 
</menu>
<?php
}

public function consultar_anios($estado="Inactivo"){
$sql='SELECT id_ano_lectivo FROM `ano_lectivo` where estado="'.$estado.'"';
return $datos = json_decode($this->consultar_datos($sql,true),true);

}


public static function eventos_favoritos($id,$estrellas_array){
$estrellas = json_decode(stripslashes($estrellas_array),true);
$estrellasmias=$estrellas[$_SESSION['id_usuario']];
if ($estrellasmias>="1"){
?>
<span id="span_eve_<?php echo $id ?>" onclick="fav_eve('<?php echo $id ?>','span_eve_<?php echo $id ?>')" estado="NO" title="Favoritos <?php echo count($estrellas) ?>" class="glyphicon glyphicon-star estrella_eve"></span>
<?php }else{ ?>
<span id="span_eve_<?php echo $id ?>" onclick="fav_eve('<?php echo $id ?>','span_eve_<?php echo $id ?>')" estado="SI" title="Favoritos <?php echo count($estrellas) ?>" class="glyphicon glyphicon-star-empty estrella_eve"></span>
<?php }
}//fin function



public function consultar_docente($asignacion){
 $sql='SELECT * FROM asignacion,usuario where 
asignacion.id_docente = usuario.id_usuario and id_Asignacion= "'.$asignacion.'"';
return $datos = json_decode($this->consultar_datos($sql,true),true);
}


public function consultar_registro_asistencia($asignacion,$mifecha=""){
$sql='select * from asistencia where fecha="'.$mifecha.'" and id_materia= "'.$asignacion.'"';
return $datos = json_decode($this->consultar_datos($sql,true),true);	
}

public function consultar_horas_horario(){

$mifecha=date('Y-m-d');
$sql='select distinct  hora_inicio,hora_fin from horario,asignacion, materia where horario.id_asignacion = asignacion.id_asignacion AND asignacion.id_asignatura = materia.id_materia  ';
$sql.='and 
"'.$mifecha.'" >= fecha_inicio and  "'.$mifecha.'"<= fecha_fin';
 #$sql.=' and id_asignacion='.$asignacion;
$sql.=' order by hora_inicio asc'; 
  return $datos = json_decode($this->consultar_datos($sql,true),true); 
 }


public function consultar_horario_completo($horario=false){
$mifecha=date('Y-m-d');
  $horario=1;
#$mifecha=date('2019-09-25');
$sql='select * from horario,asignacion, materia_oficial where horario.id_asignacion = asignacion.id_asignacion AND asignacion.id_asignatura = materia_oficial.id_materia and asignacion.visible="SI" ';
$sql.='and 
"'.$mifecha.'" >= fecha_inicio and  "'.$mifecha.'"<= fecha_fin';
 #$sql.=' and id_asignacion='.$asignacion;
$sql.=' order by hora_inicio asc';
#echo $sql;
if($horario==true){
 $asistencia = json_decode($this->consultar_datos($sql,true),true); 
  if(!empty($asistencia[0]['id_asignacion'])){
$info= $this->consultar_registro_asistencia($asistencia[0]['id_asignacion'],$mifecha);

if(empty($info)){
if(!empty($asistencia)) {
  $id =$asistencia[0]['id_asignacion'];
  //header("location:asistencia/index.php?asignacion=$id");
} 
}  
}

}

  return $datos = json_decode($this->consultar_datos($sql,true),true); 
 
}

public function consultar_horario_Asignacionold($asignacion="", $todos=0,$dia=1){
  $mifecha=date('Y-m-d');
  $sql='select * from horario,asignacion, 	materia_oficial  where horario.id_asignacion = asignacion.id_asignacion AND asignacion.id_asignatura = 	materia_oficial.id_materia and 
  asignacion.visible = "SI" and asignacion.institucion_educativa =  "'.$_SESSION['id_institucion'].'" ';
  $sql.=' and "'.$mifecha.'" >= fecha_inicio and  "'.$mifecha.'"<= fecha_fin';
  if($dia==1){
  $sql.=' and horario.dia =lower ("'.FECHA::saber_dia().'")';
  }
  if($todos==0 and $asignacion<>""){
   $sql.=' and asignacion.id_asignacion='.$asignacion;
  }
  $sql.=' order by hora_inicio asc';
#  echo $sql;
  #print_r($datos);
    return $datos = json_decode($this->consultar_datos($sql,true),true);  
  }



public function consultar_horario_Asignacion($asignacion="", $todos=0,$dia=1,$mifecha='', $min='', $max=''){
if($mifecha==""){
  $mifecha=date('Y-m-d');
}
/*
$sql='select * from horario,asignacion, materia where horario.id_asignacion = asignacion.id_asignacion AND asignacion.id_asignatura = materia.id_materia and 
asignacion.visible = "SI" and asignacion.institucion_educativa =  "'.$_SESSION['id_institucion'].'" ';
$sql.=' and "'.$mifecha.'" >= fecha_inicio and  "'.$mifecha.'"<= fecha_fin';

if($dia==1){
$sql.=' and horario.dia =lower ("'.FECHA::saber_dia().'")';
}
*/
$sql=" SELECT a.*, h.*, m.*
FROM asignacion a
JOIN horario h ON a.id_asignacion = h.id_asignacion
JOIN materia m ON a.id_asignatura = m.id_materia
WHERE h.dia = 
    IF(DAYOFWEEK('".$mifecha."') = 1, 'Domingo',
    IF(DAYOFWEEK('".$mifecha."') = 2, 'Lunes',
    IF(DAYOFWEEK('".$mifecha."') = 3, 'Martes',
    IF(DAYOFWEEK('".$mifecha."') = 4, 'Miércoles',
    IF(DAYOFWEEK('".$mifecha."') = 5, 'Jueves',
    IF(DAYOFWEEK('".$mifecha."') = 6, 'Viernes',
    IF(DAYOFWEEK('".$mifecha."') = 7, 'Sábado', '')))))))
AND '".$mifecha."' BETWEEN h.fecha_inicio AND h.fecha_fin
AND (
  CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(m.nombre_materia, '(', -1), '-', 1) AS UNSIGNED) <= ".$max."
  AND CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(m.nombre_materia, ')', 1), '-', -1) AS UNSIGNED) >= ".$min."
)
";

if($todos==0 and $asignacion<>"" and $asignacion<>0){
 $sql.=' and asignacion.id_asignacion='.$asignacion;
}
$sql.=' order by h.hora_inicio asc';
#echo $sql;
$datos = json_decode($this->consultar_datos($sql,true),true);  
#print_r($datos);
  return $datos;
}
public function consultar_horario_simple($asignacion){
$sql='select * from horario,asignacion, materia_oficial where horario.id_asignacion = asignacion.id_asignacion AND asignacion.id_asignatura = materia_oficial.id_materia';
 $sql.=' and asignacion.id_asignacion='.$asignacion;
    return $datos = json_decode($this->consultar_datos($sql,true),true);  

}

public function consultar_horario($asistencia=0){
	$hora = date('H:i');
	$mifecha=date('Y-m-d');
   $dias =Fecha::saber_dia($mifecha);

	$sql='select * from horario,asignacion where 
  horario.id_asignacion=asignacion.id_asignacion and
"'.$mifecha.'" >= horario.fecha_inicio and  "'.$mifecha.'"<= horario.fecha_fin and 	 "'.$hora.'" >= horario.hora_inicio  and  "'.$hora.'" <= horario.hora_fin and asignacion.id_docente="'.$_SESSION['id_usuario'].'" ';
   $sql.=' and horario.dia =lower ("martes")';
        #if($horario==true){
 $asistencia = json_decode($this->consultar_datos($sql,true),true); 
 if(!empty($asistencia[0]['id_asignacion'])){
$info= $this->consultar_registro_asistencia($asistencia[0]['id_asignacion'],date('Y-m-d'));
#print_r($info);
if(empty($info)){
if(!empty($asistencia) and $asistencia==0) {
  $id =$asistencia[0]['id_asignacion'];
  header("location:asistencia/index.php?asignacion=$id");
} 
}  
}

#}
           	return $datos = json_decode($this->consultar_datos($sql,true),true);	
}

public function obtener_estructura_directorios($ruta,$ruta_estudiante,$usuario="guagua",$ver=1,$insertar=1){
$ver=1;
$academico= new academico();
    $html="";
    if (is_dir($ruta)){
        $gestor = opendir($ruta);
        $html.= "<ul>";
if ($ver==1) {         echo "<ul>";  }

$ficheros  = scandir($ruta);
if((count($ficheros))<=2){
 if(is_dir($ruta)){
  #echo $ruta;
}
}else{
  
}



        while (($archivo = readdir($gestor)) !== false)  {       
            $ruta_completa = $ruta . "/" . $archivo;

            if ($archivo != "." && $archivo != "..") {
                if (is_dir($ruta_completa)) {
$this->guardar_estructura($ruta_estudiante,$ruta_completa,$insertar,$usuario);
                        $html.= "<li>($archivo)</li>";
        if ($ver==1) {
                echo "<li>" .($archivo). "</li>";
            }  
                   $this-> obtener_estructura_directorios($ruta_completa,$usuario,$ver,$insertar,$ruta_estudiante);
                } else {
                      if ($ver==1) {  
                    echo "<li>" .($archivo)  . "</li>"; #archivp
                  }
        $this->guardar_estructura($ruta_estudiante,$ruta_completa,$insertar,$usuario);

                    $html.= "<li>($archivo)</li>";
                }
            }
        }       
        // Cierra el gestor de directorios
        $html.="</ul>";
        closedir($gestor);
                     if ($ver==1) {  
        echo "</ul>";
                  }
    } else {
        echo "No es una ruta de directorio valida<br/>";
    }
    return $html;
}

public function guardar_estructura($ruta_estudiante,$ruta_completa,$insertar,$usuario){
$newstr = str_replace($ruta_estudiante," ",$ruta_completa);
#$newstr=substr($newstr,1);
if (!empty($newstr)) {
  $newstr= mb_strtoupper($newstr,'UTF-8');
  if($insertar==1){
    if ($usuario=="guagua") {
      if($newstr<>"desktop.ini"){
  $this->insertar_evaluador(($newstr),"4",$usuario,"4");
      }
    }else{
      if($newstr<>"desktop.ini"){
      $this->insertar_evaluador_es($newstr,$usuario);
      }
    }
  
  }
}
}

public function calificador($estudiante="guagua",$punto=1,$valoracion_maxima="4"){
$arbol_bien= $this->valorar($punto);
$cada_punto = $valoracion_maxima/count($arbol_bien);
$podium = 0;
$contador=0;
foreach ($arbol_bien as $key => $carpetas) {
$datos = $this->valorar_es($estudiante,$carpetas['estructura']);

$contador+=1;
if (!empty($datos)) {
$podium =$podium +$cada_punto ;
}
else{
   // echo $contador.".No Existe ".$carpetas['estructura'].'<br>';
}
}
return $podium;
}


public function valorar($actividad,$criterio=""){
$sql='select * from evaluador_guagua where usuario="guagua" and version="4" ';
if($criterio<>""){
$sql.=' and estructura=REPLACE("'.mb_strtoupper($criterio,'UTF-8').'", " ", "")';
}else{
   $sql.=' and punto="'.$actividad.'"';
}  
$datos = json_decode($this->consultar_datos($sql,true),true);
#echo $sql;
return $datos;
}

public function valorar_es($estudiante,$criterio){
$criterio = str_replace(' ', '', $criterio);

  $sql='select * from evaluadorestudiante where  usuario="'.$estudiante.'" and REPLACE(estructura, " ", "")="'.(mb_strtoupper($criterio, 'UTF-8')).'"';
#print_r( $datos = json_decode($this->consultar_datos($sql,true),true)[0]['id_actividad']);
#echo "<pre>" ;
#echo $sql.'<br>';
$datos = json_decode($this->consultar_datos($sql,true),true);
#print_r($datos);
#echo "</pre>" ;



return $datos = json_decode($this->consultar_datos($sql,true),true);
}


public function insertar_evaluador($cadena,$punto,$usuario,$version){
$cadena=substr($cadena,1);
$cadena=substr($cadena,1);
$datos = $this->valorar($usuario,mb_strtoupper($cadena,'UTF-8'));
if (empty($datos)) {
	 $sql='INSERT INTO `evaluador_guagua`(`version`, `estructura`,`punto`,usuario) VALUES ("'.$version.'","'.mb_strtoupper($cadena,'UTF-8').'","'.$punto.'","'.$usuario.'")';
		$this->query_insertar($sql);	
  }
}

public function insertar_evaluador_es($cadena,$usuario){
  $cadena=substr($cadena,1);
  $cadena=substr($cadena,1); 
  $datos = $this->valorar_es($usuario,mb_strtoupper($cadena, 'UTF-8'));  
if (empty($datos)) {
   $sql='INSERT INTO `evaluadorestudiante`( `estructura`,usuario) VALUES ("'.mb_strtoupper($cadena, 'UTF-8').'","'.$usuario.'")';
    $this->query_insertar($sql);  
}
}

public function eliminar_horario(){
    $sql='DELETE FROM `horario` WHERE `id_asignacion`="'.$_POST['asignacion'].'"';
    $this->query_insertar($sql);
}

public function insertar_horario($dia,$key){
	$sql='INSERT INTO `horario`( `id_asignacion`, `fecha_inicio`, `hora_inicio`, `hora_fin`, `fecha_fin`,dia) VALUES ("'.$_POST['asignacion'].'","'.$_POST['fecha_inicio'].'","'.$_POST['hora_inicio'][$key].'","'.$_POST['hora_fin'][$key].'","'.$_POST['fecha_fin'].'","'.$dia.'")';
		$this->query_insertar($sql);
	}



public function fechas_asistencia($materia,$fechasol=""){
#print_r($fechasol);
	$sql='SELECT distinct(`fecha`) FROM `asistencia` WHERE `id_materia`="'.$materia.'"';
	if(!empty($fechasol)){
$sql.=" and fecha='".$fechasol."'";
	}
	#echo $sql;
	return $datos = json_decode($this->consultar_datos($sql,true),true);	
}

 public function inscripcion_estudiante2($cur){
   $sql_inscripcion ='SELECT * FROM `inscripcion` where id_estudiante = "'.$_SESSION['id_usuario'].'" and fecha_inscripcion like "%'.date("Y").'%" and id_asignacion="'.$cur.'"';
 $datos = json_decode($this->consultar_datos($sql_inscripcion,true),true);
 return $datos[0]['id_inscripcion'];
 }
public function inscripcion_estudiante(){
  $sql_estudiante = "SELECT * from inscripcion,asignacion
         WHERE id_estudiante =";
if ($_SESSION['rol']=="acudiente"){
    $sql_estudiante.="'".$_SESSION['hijo']."'";
}
if ($_SESSION['rol']=="estudiante"){
 $sql_estudiante.= "'".$_SESSION['id_usuario']."'";
}
$sql_estudiante.=" and           asignacion.id_asignacion=inscripcion.id_asignacion 
              order by id_inscripcion desc limit 1  ";
              
return $datos = json_decode($this->consultar_datos($sql_estudiante,true),true);

}

function ano_estudiante(){
 $sqlp= "SELECT * FROM `ano_lectivo`";
     if ($_SESSION['rol']=="acudiente" or $_SESSION['rol']=="estudiante"){ 
        $sqlp.= ' where nombre_ano_lectivo like "'.date('Y').'" '  ; }
        $sqlp.=" order by id_ano_lectivo desc";        
return    $sqlp;
}




public function estudiantes_de_una_asignacion($nombre = "",$id_asignacion='') {
 
$parametro_grado = $nombre ; 
$ano_lectivo=17;
$sql_curso = "
SELECT DISTINCT id_usuario, u.* FROM usuario u where u.estado='activo' ";
#$sql_curso .= " and i.id_asignacion=".$id_asignacion." ";

// Evaluación del parámetro que viene del frontend
if ($parametro_grado === '6-8') {
    $sql_curso .= " AND  u.observaciones in (6,7,8)";
    
} 
if ($parametro_grado === '9-11') {
    $sql_curso .= "AND  u.observaciones in (9,10,11)";
} 
#echo "<pre>";
#print_r($sql_curso);
#echo "</pre>";

  $datos = json_decode($this->consultar_datos($sql_curso, true), true);

  return(($datos));

  #elseif ($id_asignacion > 0) {
    // Comportamiento original si se selecciona un curso específico en lugar de un rango
    #$sql_curso .= " AND a.id_asignacion = " . (int)$id_asignacion;
#}
#echo $sql_curso;
// echo $sql; // Descomenta esta línea para hacer debug en caso de fallos.
// Imprimir para depurar (opcional, bórralo en producción)
// echo $sql;
// Imprimir para depurar (opcional, bórralo en producción)
// echo $sql;
  // Ejecutar consulta

   /*
  $sql = "SELECT DISTINCT 
              usuario.id_usuario, 
              usuario.nombre, 
                           usuario.foto, 
 
              usuario.apellido, 
              usuario.correo, 
              usuario.telefono, 
              usuario.estado
          FROM inscripcion
          INNER JOIN asignacion ON inscripcion.id_asignacion = asignacion.id_asignacion
          INNER JOIN usuario ON inscripcion.id_estudiante = usuario.id_usuario
          INNER JOIN materia_oficial ON asignacion.id_asignatura = materia_oficial.id_materia
          INNER JOIN categoria_curso ON asignacion.id_categoria_curso = categoria_curso.id_categoria_curso
          INNER JOIN ano_lectivo ON asignacion.ano_lectivo = ano_lectivo.id_ano_lectivo
          WHERE
           usuario.estado = 'activo' 
          AND (inscripcion.estado_inscripcion = 'Aprobado' or inscripcion.estado_inscripcion = 'En curso') ";

  // Filtro por nombre si se proporciona un valor
  if ($id_asignacion != "") {
    $sql.=" AND inscripcion.id_asignacion = ".$id_asignacion." ";

  }
  if ($nombre != "") {
    $sql .= " AND LOWER(usuario.nombre) LIKE '%" . mb_strtolower($nombre, 'UTF-8') . "%'";
  }
  // Ordenar por apellido
  $sql .= " ORDER BY usuario.apellido ASC";

  // Depuración: Ver la consulta generada (puedes comentarla después de probar)
 */
// Iniciamos la consulta base
// Iniciamos la consulta base

// Recepción de parámetros enviados por la función listarEstudiantes()

}




public function consultar_materia($asignacion="") {
$sql='SELECT categoria_curso.nombre_categoria_curso, materia_oficial.id_materia, materia_oficial.nombre_materia,asignacion.id_curso,asignacion.id_asignatura,categoria_curso.nombre_categoria_curso FROM `asignacion`,
materia_oficial,categoria_curso where asignacion.id_asignatura = materia_oficial.id_materia and
 asignacion.id_categoria_curso=categoria_curso.id_categoria_curso';
if (!empty($asignacion)) {
$sql.=' and asignacion.id_Asignacion= "'.$asignacion.'"';
}
#echo $sql;
$datos = json_decode($this->consultar_datos($sql,true));
return $datos;
}

public function consultar_asistencia() {
$sql='SELECT * FROM `asistencia` ';
if (!empty($this->id_persona)) {
$sql.=' where id_estudiante= "'.$this->id_persona.'"';
}
return $datos = json_decode($this->consultar_datos($sql,true),true);

	}

  public function registrar_asistencia($control_ingreso) {
    $docente = $_POST['docente'];
    $asignacion = $control_ingreso->grupo;
    $fecha = $control_ingreso->fecha_ingreso;

    // Construir la consulta de inserción o actualización
    $sql = "INSERT INTO `asistencia` (`id_docente`, `id_materia`, `id_estudiante`, `asistencia`, `fecha`) VALUES ";
    $values = [];

    foreach ($_POST['asistencia'] as $estudiante => $asistencia) {
        $values[] = '("'.$docente.'", "'.$asignacion.'", "'.$estudiante.'", "'.$asistencia.'", "'.$fecha.'")';
    }

    // Preparamos la parte de actualización
    $sql .= implode(',', $values);
    $sql .= " ON DUPLICATE KEY UPDATE asistencia = VALUES(asistencia)";

    // Depuración: imprimir la consulta SQL generada
    #echo $sql;

    // Ejecutar la consulta
    $insercion = $this->query_insertar($sql);
    return $insercion;
}






public function leer_mensajes(){
	 $sql=' SELECT * FROM `mensaje` where leido="NO" and usuario="'.$_SESSION['id_usuario'].'" order by fecha desc';
 return $datos = json_decode($this->consultar_datos($sql,true),true);

}
public function cantidad_estudiantes_asignacion ($asignacion){
  $sql=' SELECT count(*) FROM `inscripcion` where id_asignacion="'.$asignacion.'"  and estado_inscripcion="Aprobado" GROUP by id_estudiante ';
  return $datos = json_decode($this->consultar_datos($sql,true),true);
 
}

public function listar_grados_estudiantes_asignacion ($asignacion){
  $sql="SELECT 
  i.id_asignacion,
  MIN(CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(u.observaciones, '(', -1), ')', 1) AS UNSIGNED)) AS grado_minimo,
  MAX(CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(u.observaciones, '(', -1), ')', 1) AS UNSIGNED)) AS grado_maximo
FROM 
  inscripcion i
INNER JOIN 
  usuario u ON i.id_estudiante = u.id_usuario
WHERE 
  i.id_asignacion = '".$asignacion."' 
  AND (i.estado_inscripcion='Aprobado' or i.estado_inscripcion= 'En curso' ) 
GROUP BY 
  i.id_asignacion
ORDER BY 
  u.nombre ASC;
";

return $datos = json_decode($this->consultar_datos($sql,true),true);

}

public function listar_estudiantes_asignacion($asignacion){
/*
$sql=' SELECT * FROM inscripcion
  inner join usuario on inscripcion.id_estudiante= usuario.id_usuario
   where inscripcion.id_asignacion="'.$asignacion.'"  and (inscripcion.estado_inscripcion="Aprobado" or inscripcion.estado_inscripcion= "En curso" ) GROUP by inscripcion.id_estudiante order by usuario.nombre asc';
   */

   $sql='SELECT *
FROM inscripcion
INNER JOIN usuario ON inscripcion.id_estudiante = usuario.id_usuario
WHERE
   inscripcion.id_asignacion IN (
        SELECT a.id_asignacion
        FROM asignacion a
        JOIN materia m ON a.id_asignatura = m.id_materia
        JOIN categoria_curso cc ON a.id_categoria_curso = cc.id_categoria_curso
        WHERE a.id_asignacion = "'.$asignacion.'"
    )
    -- Se mantienen tus filtros originales para el estado de la inscripción.
    AND (inscripcion.estado_inscripcion = "Aprobado" OR inscripcion.estado_inscripcion = "En curso")
GROUP BY
    inscripcion.id_estudiante
ORDER BY
    usuario.nombre ASC;';

  #echo $sql;
return $datos = json_decode($this->consultar_datos($sql,true),true);

}

public function valoraciones(){
	 $sql=' select * from seguimiento_es,inscripcion,actividad where
 actividad.id_actividad = seguimiento_es.id_actividad and
 seguimiento_es.id_inscripcion =inscripcion.id_inscripcion and  inscripcion.id_estudiante = "'.$_SESSION['id_usuario'].'" and Year(inscripcion.fecha_inscripcion) = "'.$this->ano_lectivo.'" and seguimiento_es.valoracion <> "" order by seguimiento_es.fechayhora_valoracion desc';
  $datos = json_decode($this->consultar_datos($sql,true),true);
 if (!empty($datos_valoraciones)){
            foreach ($datos_valoraciones as $key => $row) {
              echo '<a  href="'.SGA_CURSOS_URL.'/visor_actividad.php?a='.$row['id_actividad'].'"><strong> Actividad :'.$row['nombre_actividad'].', Valoración =  </strong> '.$row['valoracion'].'</a> <br/>';
            }  

                       }else{
                        echo "No hay Valoraciones";
                       }
                       
            


}
public function categoria_estudiante(){
  $sql_estudiante = "SELECT * from inscripcion,asignacion
 WHERE id_estudiante = '".$_SESSION['id_usuario']."' and
      asignacion.id_asignacion=inscripcion.id_asignacion 
      order by id_inscripcion desc limit 1  ";
    $consulta_estudiante = json_decode($this->consultar_datos($sql,true),true);
 $consulta_estudiante = $mysqli->query($sql_estudiante);
    if ($consulta_estudiante[0]['id_categoria_curso']){
       $_SESSION['id_categoria_curso'] = $row_estudiante['id_categoria_curso'];   

}
}
public function mis_cursos_estudiante(){
 $sql='select *,usuario.apellido as apellido_docente ,usuario.nombre as nombre_docente from inscripcion,asignacion,ano_lectivo,materia,usuario, categoria_curso where inscripcion.id_asignacion = asignacion.id_asignacion and asignacion.id_asignatura = materia.id_materia and asignacion.id_categoria_curso=categoria_curso.id_categoria_curso and asignacion.id_docente=usuario.id_usuario and asignacion.ano_lectivo = ano_lectivo.id_ano_lectivo
and asignacion.institucion_educativa ="'.$_SESSION['institucion'].'" and id_estudiante="'.$_SESSION['id_usuario'].'" AND ano_lectivo.nombre_ano_lectivo="'.$this->ano_lectivo.'"';
  
return $datos = json_decode($this->consultar_datos($sql,true),true);
}


public function seguimiento_actividades($actividad,$inscripcion){
  $sql ='SELECT * FROM `actividad` left join seguimiento_es on actividad.id_actividad = seguimiento_es.id_actividad where seguimiento_es.id_inscripcion="'.$inscripcion.'" and valoracion <>"" '; 
  return $datos = json_decode($this->consultar_datos($sql,true),true);
}


public function verificarAsistenciaYRedirigir() {
    
    // Instanciar la clase Academico para usar sus métodos de consulta
    $academico = new Academico();

    // 2. Obtener la fecha y hora actuales
    $fecha_actual_db = date('Y-m-d');
    $hora_actual = date('H:i:s');
    $dia_actual = strtolower(date('l')); // 'l' devuelve el día de la semana en inglés, por ejemplo, 'monday'

    // Mapear días de la semana de inglés a español para la tabla `horario`
    $dias_semana = [
        'monday' => 'lunes',
        'tuesday' => 'martes',
        'wednesday' => 'miercoles',
        'thursday' => 'jueves',
        'friday' => 'viernes',
        'saturday' => 'sabado',
        'sunday' => 'domingo'
    ];
    $dia_db = $dias_semana[$dia_actual];

    // 3. Consultar la asignatura programada usando la función `consultar_datos`
    $sql_horario = "SELECT id_asignacion FROM horario 
                    WHERE dia = '{$dia_db}' 
                    AND hora_inicio <= '{$hora_actual}' 
                    AND hora_fin >= '{$hora_actual}' 
                    AND '{$fecha_actual_db}' BETWEEN fecha_inicio AND fecha_fin 
                    LIMIT 1";
    $result_horario_json = $academico->consultar_datos($sql_horario, true);
    $asignacion_actual = json_decode($result_horario_json, true);

    if (empty($asignacion_actual)) {
        return "No hay asignaturas programadas para este momento.";
    }

    $id_asignacion = $asignacion_actual[0]['id_asignacion'];
    
    // Obtener el nombre de la materia para la tabla `asistencias`
    $info_asignatura_temp = $academico->consultar_materia($id_asignacion);
    $nombre_materia = !empty($info_asignatura_temp) ? $info_asignatura_temp[0]->nombre_materia : 'Materia Desconocida';
    $fecha_asistencia_formato = date('d/m/Y', strtotime($fecha_actual_db));

    // 4. Verificar si la asistencia ya está registrada usando la función `consultar_datos`
    $sql_asistencia = "SELECT id FROM asistencias 
                       WHERE materia = '{$nombre_materia}' 
                       AND fechas_clase = '{$fecha_asistencia_formato}' 
                       LIMIT 1";
    
    $result_asistencia_json = $academico->consultar_datos($sql_asistencia, true);
    $registro_existente = json_decode($result_asistencia_json, true);

    // 5. Devolver mensaje o redirigir
    if (!empty($registro_existente)) {
        return "La asistencia para la asignatura '{$nombre_materia}' de hoy ya ha sido registrada.";
    } else {
        header("Location: index.php?asignacion={$id_asignacion}");
        exit;
    }
}


public function mis_cursos_otros() {
        // Obtener datos del usuario desde la sesión.
        $rol = $_SESSION['rol'] ?? 'invitado';
        $id_usuario = $_SESSION['id_usuario'] ?? null;

        if (!$id_usuario) {
            return []; // Si no hay usuario, no se pueden mostrar cursos.
        }
        
        // --- INICIO DE LA CONSULTA AJUSTADA ---
        // Se utiliza la estructura de la consulta que proporcionaste como base.
        // Se ha simplificado el JOIN, uniendo 'asignacion' directamente con 'materia_oficial'.
        $sql_base = "
            SELECT
                a.id_asignacion,
                a.icono_asignacion,
                a.descripcion,
                mo.nombre_materia,
                cg.nombre_categoria_curso AS mid_categoria_curso
            FROM
                asignacion a
            INNER JOIN
                materia_oficial mo ON a.id_asignatura = mo.id_materia
            INNER JOIN
                ano_lectivo al ON a.ano_lectivo = al.id_ano_lectivo
            LEFT JOIN
                categoria_curso cg ON a.id_categoria_curso = cg.id_categoria_curso
           
               
        ";
        // Se mantiene la lógica PHP para añadir condiciones dinámicas.
        // 1. Condición CRÍTICA para filtrar solo por el año lectivo activo.
        $sql_conditions = " WHERE al.estado = 'Activo' 
        ";

        // 2. Se construye el resto de condiciones según el ROL.
        // ADVERTENCIA: Se recomienda usar sentencias preparadas para evitar inyección SQL.
        if (in_array($rol, ['admin', 'docente'])) {
            $sql_conditions .= " AND a.id_docente = '" . $id_usuario . "'";
        } else {
            // Para estudiantes y acudientes, es necesario unir con la tabla de inscripciones.
            $sql_base .= " JOIN inscripcion i ON a.id_asignacion = i.id_asignacion ";
            if ($rol == 'estudiante') {
                $sql_conditions .= " AND i.id_estudiante = '" . $id_usuario . "'";
            } elseif ($rol == 'acudiente') {
                $id_hijo = $_SESSION['id_hijo_seleccionado'] ?? '';
                if (empty($id_hijo)) return [];
                $sql_conditions .= " AND i.id_estudiante = '" . $id_hijo . "'";
            } else {
                return []; // Rol no reconocido para esta consulta.
            }
        }
        $sql_conditions.="  and       a.visible ='Si' ";
        // 3. Se une toda la consulta, se agrupa y se limita a 5 para la vista "home".
        $sql = $sql_base . $sql_conditions . " GROUP BY a.id_asignacion ORDER BY a.id_asignacion DESC LIMIT 100";
        // 4. Se llama al método para ejecutar la consulta.
        $resultado_json = $this->consultar_datos($sql, true);
        
        // El método devuelve un JSON, así que lo decodificamos.
        $datos = json_decode($resultado_json, true);

        // Se añade una verificación por si el JSON está mal formado o vacío.
        return is_array($datos) ? $datos : [];
    }


public function area(){
$sql= "SELECT * FROM area;";
return $datos = json_decode($this->consultar_datos($sql,true),true);

}

public function ano_lectivo(){
$sql ='select * from ano_lectivo'; 
return $datos = json_decode($this->consultar_datos($sql,true),true);


}

public function asistencia_genero($asignacion=false){
  $sql='
SELECT count(`genero`) as femenino,(select count(`genero`) FROM usuario,inscripcion WHERE
inscripcion.id_estudiante=usuario.id_usuario and 
 `genero` = "m"';
if($asignacion<>false){
  $sql.=' and inscripcion.id_asignacion ="'.$asignacion.'"';
}
 $sql.=' ) as masculino  FROM usuario,inscripcion WHERE inscripcion.id_estudiante=usuario.id_usuario and  `genero` = "f" ';
if($asignacion<>false){
  $sql.=' and inscripcion.id_asignacion ="'.$asignacion.'"';
}
#echo $sql;
return $datos = json_decode($this->consultar_datos($sql,true),true);

}
public function informacion_horario_Asignacion(){
  $sql='select * from horario where id_asignacion='.$this->id_asignacion;
  return $datos = json_decode($this->consultar_datos($sql,true),true);
}

public function pendientes(){
  $mismateriashoy= $this->consultar_horario_Asignacion();
$cantidad_materias_hoy=count($mismateriashoy);
#require_once 'clases/fecha.Class.php';
$fecha=new Fecha();
foreach ($mismateriashoy  as $key => $value) {
echo "<pre>";
echo "<a style='color: black;text-decoration:none;' href="
.SGA_CURSOS_URL.'/curso.php?asignacion='.$value["id_asignacion"]." >";
echo $value['nombre_materia'].' (Hoy) <br> desde '.$fecha->formato_hora_corta($value['hora_inicio']).' hasta '.$fecha->formato_hora_corta($value['hora_fin']);
echo "</a>";
echo "</pre>";
}
#require_once 'clases/Actividad.Class.php';
$actividad=new actividad();  
$mismateriashoy= $actividad->actividades_pendientes_por_fecha();
if(!empty($mismateriashoy)){
foreach ($mismateriashoy  as $key => $row) {
echo "<pre>";
$persona =  new Persona($row['identificacion']);
print_r($persona->nombre);
echo "<br>";
print_r($row['contenido']);
echo "<br>";
$fecha= new Fecha();
print_r($fecha->formato_hora_corta($row['hora_inicio']));
echo " a ";
print_r($fecha->formato_hora_corta($row['hora_fin']));
echo "</pre>";
}
}
$notaspendientes= $this->notaspendientes();
foreach ($notaspendientes  as $keynota => $nota) {
  $datos_materia=$academico->consultar_materia($nota['id_asignacion']);
?>
<div class="modal fade" id="exampleModal<?php echo $nota['id_nota']; ?>" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel"><?php echo $datos_materia[0]->nombre_materia.'('.Fecha::formato_fecha($nota['fecha_nota']).'/'.Fecha::formato_hora($nota['hora_nota']) ?></h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">

         <div class="form-group">
            <label for="message-text" class="col-form-label"><?php echo $nota['nota'] ?>:</label>
          </div>
     
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
 
      </div>
    </div>
  </div>
</div>
<?php
$idnota=$nota['id_nota'];
echo "<pre>";
echo '<p  data-toggle="modal" data-target="#exampleModal'.$idnota.'" data-whatever="@mdo" title='.$nota['nota'].'>'.(Comun::puntos_suspensivos($nota['nota'],20)).'('.$datos_materia[0]->nombre_materia.')</p>';
echo(Fecha::formato_fecha($nota['fecha_nota']));
echo "<a href=".SGA_CURSOS_URL.'/edunotas.php?estado='.$idnota."><img title='Desfijar' width='7%' src='".SGA_COMUN_URL."/img/negativo.png' ></img></a>";
echo "</pre>";
}
}


public function valoraciones_pendientes($fecha="",$limite=10){
if($fecha<>""){ $fecha=date('Y-m-d'); }
 $sql ='select * from asignacion,actividad,materia  where 
materia.id_materia = asignacion.id_asignacion and
asignacion.id_asignacion = actividad.id_asignacion and evaluable ="SI" and actividad.visible="SI" and actividad.fecha_entrega >= "'.date('Y-m-d').'" and actividad.id_asignacion in (Select asignacion.id_asignacion from inscripcion,asignacion,materia where inscripcion.id_asignacion = asignacion.id_asignacion and materia.id_materia = asignacion.id_asignacion and inscripcion.id_estudiante ="'.$_SESSION['id_usuario'].'" ';
$datos = json_decode($this->consultar_datos($sql,true),true);
if(!empty($datos)){
  return $datos ;
}else{
  return "";
}

}


}

?>