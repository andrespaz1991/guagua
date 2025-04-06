<?php require_once ("../comun/conexion.php");
require_once ("../comun/funciones.php"); ?>
<div style="margin-left:50px;">
<meta  charset="utf-8"/>
<script type="text/javascript" src="<?php echo  ""; ?>"></script>
<?php
/* <h1>Rendimiento acádemico por materia</h1>*/


@session_start(); 
$ano_lectivo = ano_lectivo();
$inf_ie ='select * from config';
$consulta = $mysqli ->query($inf_ie);
$resultados = $consulta -> num_rows;
if($resultados<=0 ){ ?>
    <script type="text/javascript" >
    alert2('No hay resultados');
   window.history.back();
            </script>

<?php }
$materia='';
$categoria_curso='';
$periodo='';
$ano ='';
$html ='';
$inscripcion ='';
if($rowinfosede =$consulta ->fetch_assoc()  ){
$html.='<img class="logo" height="60" src="../comun/img/'.$rowinfosede["logo_institucion"].'"></img>
    <p class="IEM"><strong>
      '.$rowinfosede["nombre_institucion"].' <br/>
      </strong>   </p>
    
                 <br/>
<h4 align="center">REPORTE VALORATIVO '.$materia.' ('.$categoria_curso.') <br/> PERIODO '.$periodo.' ('.$ano.' )</h4>';
    #echo $html;
}


echo "<h1 align='center'>ESTADISTICAS DE ".consultar_nombre_asignacion($_GET['asignacion'])."</h1>";
if(isset($_GET['asignacion'])){    $asignaciones=$_GET['asignacion'];} 
                        else {    $asignaciones="241";}
 
if(isset($_SESSION['rol']) and $_SESSION['rol']=="estudiante"){
 $inscripcion = inscripcion_actual($_SESSION['id_usuario']);
}
if(isset($_SESSION['rol']) and $_SESSION['rol']=="acudiente"){
 $inscripcion = inscripcion_actual($_SESSION['hijo']);
}
if(isset($inscripcion)){
/* Información Individual del estudiante */
      $sql='select * from usuario,inscripcion where
    inscripcion.id_estudiante = usuario.id_usuario and
    inscripcion.id_asignacion="'.$asignaciones.'"  and inscripcion.id_inscripcion="'.$inscripcion.'"  ';
echo '<table align="center" border="2"><tr>
<th>Identificación</th>
<th>Estudiante</th>
<th>Foto</th>
<th>usuario</th>
<th>Teléfono</th>
<th>Tipo de Sangre</th>
<th>Correo</th>
<th>Visitas</th>
<th>Estado</th>
<th>Ultimo ingreso</th>
</tr>';
$consulta =$mysqli -> query ($sql);
while ( $row = $consulta ->fetch_Assoc()) {
    $_SESSION['nombre'] = $row['apellido'].' '.$row['nombre'];
echo '<tr>
<td>'.$row['id_usuario'].'</td>
<td>'.$_SESSION['nombre'] .'</td>
<th><img id="foto_est_'.$row['id_estudiante'].'" title="tooltip" height="60" src="'.SGA_URL.'/sga-data/foto/'.validarfoto($row['foto']).'"></img></th>
<td>'.$row['usuario'].'</td>
<td>'.$row['telefono'].'</td>
<td>'.$row['tipo_sangre'].'</td>
<td>'.$row['correo'].'</td>
<td>'.$row['num_visitas'].'</td>
<td>'.$row['estado'].'</td>
<td>'.formatofechayhora($row['ultima_sesion']).'</td>
</tr>';
}
}
      ?>
 </table>
 <?php
 
 
  $sql_periodos_con_actividades = 'select distinct(periodo) from actividad where Year(fecha_publicacion) = "'.$ano_lectivo.'" and id_asignacion="'.$asignaciones.'"';
  
  if(empty(consultar_datos($sql_periodos_con_actividades)))?>
<script type="text/javascript">
    alert2('No hay información \n por favor intente más tarde');
window.close();
</script>
  <?php 
  
 if (!empty(consultar_datos($sql_periodos_con_actividades))) {
foreach(consultar_datos($sql_periodos_con_actividades) as $array => $informacion){
  foreach($informacion as $periodo){
    echo estadisticas ($asignaciones,$inscripcion,$periodo,$ano_lectivo);
  }
}
}
 
 

function estadisticas ($asignaciones,$inscripcion,$periodo,$ano_lectivo) {
         echo '<h3>Periodo'.$periodo.'</h3>';
require ("../comun/conexion.php");
$sql_asignaciones='select * from  inscripcion, usuario where
Year(inscripcion.fecha_inscripcion) = "'.$ano_lectivo.'"
and
inscripcion.id_estudiante = usuario.id_usuario and ';
@session_start();
$sql_asignaciones.= ' inscripcion.id_asignacion ="'.$asignaciones.'" order by apellido asc';
$mysql='';
$consulta_asignaciones=$mysqli->query($sql_asignaciones);
$cantidad_estudiantes = $consulta_asignaciones ->num_rows ;
while($row_asignaciones=$consulta_asignaciones->fetch_assoc()){
$sql_valoracion='select id_inscripcion,avg(valoracion) as promedio from seguimiento,actividad  where  seguimiento.id_actividad = actividad.id_actividad and actividad.periodo = "'.$periodo.'" and id_inscripcion="'.$row_asignaciones['id_inscripcion'].'"';
$consulta_valoracion=$mysqli->query($sql_valoracion);
$promedio_general="";

while($row_valoracion=$consulta_valoracion->fetch_assoc()  ){
   
if($row_valoracion['promedio']=="") $row_valoracion['promedio']=0;
$seguimiento[]= array(
         $row_asignaciones['apellido'].' '.$row_asignaciones['nombre'] => $row_valoracion['promedio'],
         );
}
}
$valores=0;
if (empty($seguimiento)) {
echo 'No hay información';
}
if (!empty($seguimiento)) {

 foreach($seguimiento as $estudiantes){
    foreach($estudiantes as $clave => $valor){ 
$valores  =$valores+$valor;
    }
 }

?>
 
    <meta http-equiv="content-type" content="text/html; charset=utf-8"/>
    <title>
Estadisticas de aprendizaje
</title>

<?php if(isset($_SESSION['rol']) and $_SESSION['rol']<>"estudiante" and  $_SESSION['rol']<>"acudiente"){ ?>
<?php echo '<h1> Promedio del Cursos '.round(($valores)/$cantidad_estudiantes,2).'</h1>'; } ?>
    <script type="text/javascript" src="../comun/js/jsapi.js"></script>
    <script type="text/javascript" src="../comun/js/uds_api_contents.js"></script>


<?php
### Función que determina formatos multimedia con influencia en el aprendizaje
$sql_formato='select distinct(formato) from seguimiento,red,actividad where 
actividad.periodo = "'.$periodo.'"  and 
actividad.id_red = red.id_red and seguimiento.id_actividad =actividad.id_actividad ';
$sql_inscripcion='';
$rango_superior ='';
$rango_medio ='';
$rango_medio ='';
$rango_bajo  ='';
$ano  ='';
if(isset($_SESSION['rol']) and $_SESSION['rol']=="estudiante" or $_SESSION['rol']=="acudiente"){
$sql_inscripcion.=' and seguimiento.id_inscripcion ="'.$inscripcion.'"   ';
}
else {
$sql_inscripcion.=' ';
}
$rango_superior.='and valoracion >= 4 ';
$rango_medio.=' and valoracion >= 3 and valoracion < 4 ';
$rango_bajo.=' and valoracion < 3 ';
$condicion_asignacion =' and actividad.id_asignacion ="'.$asignaciones.'" ';

$consulta_red1 =$sql_formato.$sql_inscripcion.$rango_superior.$condicion_asignacion ;
$consulta_red2 = $sql_formato.$sql_inscripcion.$rango_medio.$condicion_asignacion ;
$consulta_red3 = $sql_formato.$sql_inscripcion.$rango_bajo.$condicion_asignacion ;

for ($i = 1; $i < 4; $i++) {
if($i==1){ $consulta = $consulta_red1 ; $rendiemiento = 'Alto'; } 
if($i==2){ $consulta = $consulta_red2 ; $rendiemiento = 'Medio'; }
if($i==3){ $consulta = $consulta_red3 ; $rendiemiento = 'Bajo'; }
if (!empty(consultar_datos($consulta))) {
foreach(consultar_datos($consulta) as $formatos ){
echo 'Formatos con influencia en el rendimiento '.$rendiemiento.' en el aprendizaje: '.$formatos[0].'<br>';
}
}
  

}
#### Rendimiento individual
if(isset($inscripcion)){

   $sqlvaloraciones = 'SELECT * from `inscripcion` inner join usuario on `inscripcion`.id_estudiante = usuario.id_usuario inner join `asignacion_academica` on `inscripcion`.id_asignacion = `asignacion_academica`.id_asignacion inner join `actividad` on `asignacion_academica`.`id_asignacion` = `actividad`.`id_asignacion` left join seguimiento on actividad.id_actividad = seguimiento.id_actividad and `inscripcion`.id_inscripcion = `seguimiento`.id_inscripcion where `fecha_inscripcion` LIKE "%'.$ano.'%"  and actividad.periodo="'.$periodo.'" and  seguimiento.id_inscripcion = '.$inscripcion.' and `asignacion_academica`.id_asignacion="'.$asignaciones.'" order by usuario.apellido  ';
if (!empty(consultar_datos($sqlvaloraciones))) {
/* Grafico  */ ?>
 <script type="text/javascript">
      function Graficooffline() {
       var data = google.visualization.arrayToDataTable([
          ['Actividad', 'Valoración'],
        <?php
    foreach(consultar_datos($sqlvaloraciones) as $formatos => $valor){         ?>
          ['<?php echo $valor[33] ?>',<?php echo $valor[49] ?>], 
<?php   }       ?>
        ]);
 new google.visualization.LineChart(document.getElementById('RAI<?php echo $periodo;  ?>')).
        draw(  
          data,
          {
             title: 'Rendimiento acádemico de <?php echo $_SESSION['nombre']  ; ?>', //Titulo
            width: 700, height: 300, //Dimensiones del gráfico
          }
        );
      }
      google.setOnLoadCallback(Graficooffline); //envíamos al servicio de google la función Graficooffline
    </script>
   <?php
}

 ?>
 <!-- Fin Información Individual del estudiante  -->
     <div id="RAI<?php echo $periodo;  ?>" style="width: 500px; height: 300px;"></div>
    <!--Creamos un nuevo contenedor donde se presentará unuestro gráfico -->
<?php
  
}

 
/* Fin Grafico  */
    ?>
        <script type="text/javascript" >
      function Graficooffline() {
       var data = google.visualization.arrayToDataTable([
          ['Estudiante', 'Promedio Valorativo' ],
        <?php
foreach($seguimiento as $estudiantes){
    foreach($estudiantes as $clave => $valor){         ?>
          ['<?php echo $clave ?>',<?php echo $valor ?>], 
<?php   } }      ?>
        ]);
 new google.visualization.BarChart(document.getElementById('div<?php echo $periodo;  ?>')).
        draw(  
          data,
          {
             title: 'Rendimiento acádemico del curso', //Titulo
            width: 700, height: 300, //Dimensiones del gráfico
          }
        );
      }
      google.setOnLoadCallback(Graficooffline); //envíamos al servicio de google la función Graficooffline
    </script>

 <!-- Fin Información Individual del estudiante  -->
     <div id="div<?php echo $periodo;  ?>" style="width: 500px; height: 300px;"></div>
    <!--Creamos un nuevo contenedor donde se presentará unuestro gráfico -->

    
    <?php
}
}
#### Fin Rendimiento individual



      

#### Fin Función que determina formatos multimedia con influencia en el aprendizaje



?>
<input onclick="window.print();" type="button" name="" value="Imprimir" class="btn btn-primary"/>
</div>