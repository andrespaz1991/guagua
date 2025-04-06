<?php 
ob_start();
echo '<center>';
require("conexion.php");
require("../comun/autoload.php");

function buscar_seguimiento( $datos='', $reporte=''){
require_once ("../comun/lib/Zebra_Pagination/Zebra_Pagination.php");
$resultados = (isset($_COOKIE['numeroresultadosseguimiento']) ? $_COOKIE['numeroresultadosseguimiento'] : 10);
$paginacion = new Zebra_Pagination();
$paginacion->records_per_page($resultados);
$paginacion->records_per_page($resultados);
$cookiepage="page_numeroresultadosseguimiento";
$funcionjs="buscar();";
$paginacion->fn_js_page("$funcionjs");
$paginacion->cookie_page($cookiepage);
$paginacion->padding(true);
if (isset($_COOKIE["$cookiepage"])) $_GET['page'] = $_COOKIE["$cookiepage"];



if ($reporte=="xls" or  isset($_GET['xls'])){
    header("Content-type: application/vnd.ms-excel");
    if(!empty($_GET['xls'])){
        header("Content-Disposition: attachment; Filename=".$_GET['xls'].".xls");   
    }else{
        header("Content-Disposition: attachment; Filename=seguimiento.xls");
    }
    
    #header("Location:seguimiento.php");
    }require("conexion.php");
$sql='select * from   seguimiento ';
$consulta = $mysqli->query($sql);
$paginacion->records($consulta->num_rows);

$datosrecibidos = $datos;
$datos = explode(" ",$datosrecibidos);
$datos[]='';
$cont =  0;
$sql .= ' WHERE ';
if(!empty($_GET['xls'])){
    $sql.= "  seguimiento.id_seguimiento= '".$_GET['xls']."'";
}else{
    foreach ($datos as $id => $dato){
        $sql .= 'concat(LOWER(seguimiento.id_seguimiento),"", LOWER(seguimiento.id_inscripcion),"", LOWER(seguimiento.fecha_seguimiento),"", LOWER(seguimiento.hora_seguimiento),"", LOWER(seguimiento.observaciones),"", concat(LOWER(seguimiento.id_seguimiento),"", LOWER(seguimiento.id_inscripcion),"", LOWER(seguimiento.fecha_seguimiento),"", LOWER(seguimiento.hora_seguimiento),"", LOWER(seguimiento.observaciones),"")) LIKE "%'.mb_strtolower($dato, 'UTF-8').'%"' ;
        $cont ++;
        if (count($datos)>1 and count($datos)<>$cont){
            $sql .= ' and ';
        }
        }
        $sql .=  ' ORDER BY seguimiento.id_seguimiento desc  ';
        if (!isset($_GET['xls'])){
            $sql.=  "  LIMIT " . (($paginacion->get_page() - 1) * $resultados) . ", " .$resultados;
            #echo $sql;
            }
}

    /*echo $sql;*/ 
    $consulta = $mysqli->query($sql);
    $numero_usuario = $consulta->num_rows;
    $minimo_usuario = (($paginacion->get_page() - 1) * $resultados)+1;
    $maximo_usuario = (($paginacion->get_page() - 1) * $resultados) + $resultados;
    if ($maximo_usuario>$numero_usuario) $maximo_usuario=$numero_usuario;
    $maximo_usuario += $minimo_usuario-1;
    echo "<p>Resultados de $minimo_usuario a $maximo_usuario del total de ".$numero_usuario." en página ".$paginacion->get_page()."</p>";

    ?>
    <div align="center">
  
<table class="table" border='1' id='tbseguimiento'>
<thead class="thead-dark">
<tr>
<th>Id Seguimiento</th><th>Id Inscripcion</th><th>Fecha Seguimiento</th><th>Hora Seguimiento</th><th>Observaciones</th>
<?php if ($reporte==''){ ?>
    <th ><form id='formNuevo' name='formNuevo' method='post' action=citas.php>
    <input name='cod' type='hidden' id='cod' value='0'>
    <input class="btn btn-light" type='submit' name='submit' id='submit' value='Nuevo'>
    </form>
    </th><th  ><form id="formNuevo" name="formNuevo" method="post" action=citas.php?xls>
    <input name="cod" type="hidden" id="cod" value="0"><input class="btn btn-success" type="submit" name="submit" id="submit" value="XLS"><a target="_blank" href='reporte_seguimiento.php'><button type="button" class="btn btn-danger">PDF</button>
        </a></form>
    </th><?php } ?>
    </tr>
    </thead><tbody>
    <?php 
    while($row=$consulta->fetch_assoc()){
        ?>
       <tr>
       <td><?php echo $row['id_seguimiento']?></td><td><?php echo $row['id_inscripcion']?></td><td><?php echo $row['fecha_seguimiento']?></td><td><?php echo $row['hora_seguimiento']?></td><td><?php echo $row['observaciones']?></td> 
       <?php if ($reporte==''){ ?>
       <td>
       <form id='formModificar' name='formModificar' method='post' action=''citas.php'>
       <input name='cod' type='hidden' id='cod' value=' <?php echo $row['id_seguimiento']?>'>
       <input class="btn btn-outline-primary" type='submit' name='submit' id='submit' value='Modificar'>
       <button type="button" class="btn btn-outline-danger" onClick="confirmeliminar('citas.php',{'del':'<?php echo $row['id_seguimiento'];?>'},'<?php echo $row['id_seguimiento'];?>');">Eliminar</button>
       </form>     
       </td><td>
       <a target="_blank" href='citas.php?xls=<?php echo $row['id_seguimiento']?>'><button type="button" class="btn btn-success">XLS</button>
       </a><a target="_blank" href="citas.php?id=<?php echo $row['id_seguimiento']?>"> <button type="button" class="btn btn-danger">PDF</button></a></td><?php } ?>
       </tr>
       <?php 
       }/*fin while*/
        ?>
       </tbody>
       </table>
       <div class="text-center">
       <?php
       if (!isset($_GET['xls'])){
       echo $paginacion->render2();
       }
       ?>
       </div>
       
       </div>
       <?php 
    }/*fin function buscar*/
    if (isset($_GET['buscar'])){
        buscar_seguimiento($_POST['datos']);
    exit();
    }
    if (isset($_GET['xls'])){
     buscar_seguimiento('','xls');
    exit();
    }

if (isset($_POST['del'])){
 /*Instrucción SQL que permite eliminar en la BD*/ 
 $sql = 'DELETE FROM seguimiento WHERE id_seguimiento="'.$_POST['del'].'"';
 /*Se conecta a la BD y luego ejecuta la instrucción SQL*/
if ($eliminar = $mysqli->query($sql)){
 /*Validamos si el registro fue eliminado con Éxito*/  echo '<div class="alert alert-success alert-dismissible">
                  <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                  <h5><i class="icon fas fa-check"></i> Correcto!</h5>
                  Registro Eliminado
                  </div>' ;
?> <meta http-equiv="refresh" content="; url="citas.php" />
<?php
}else{ 
 echo  '<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
              <h5><i class="icon fas fa-ban"></i> Alerta</h5>
              Eliminación Fallida
            </div>';
?> 
<meta http-equiv="refresh" content="; url='citas.php" />
<?php 
}
}
 ?>

 <center>
 <h1>Seguimiento Estudiante</h1>
 </center><?php 
 if (isset($_POST['submit'])){
 if ($_POST['submit']=="Registrar"){
  /*recibo los campos del formulario proveniente con el método POST*/ 
  $sql = "INSERT INTO seguimiento(id_inscripcion,fecha_seguimiento,hora_seguimiento,observaciones) Values ('".$_POST["id_inscripcion"]."','".$_POST["fecha_seguimiento"]."','".$_POST["hora_seguimiento"]."','".$_POST["observaciones"]."')";

  /*echo $sql;*/
  if ($insertar = $mysqli->query($sql)) {
   /*Validamos si el registro fue ingresado con Éxito*/ 
    echo '<div class="alert alert-success alert-dismissible">
                  <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                  <h5><i class="icon fas fa-check"></i> Correcto!</h5>
                  Registro Exitoso
                  </div>' 
   ; echo '<meta http-equiv="refresh" content="; url=citas.php" />';
   }else{ 
     echo  '<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
              <h5><i class="icon fas fa-ban"></i> Alerta</h5>
              Registro fallido
            </div>'
    ; echo '<meta http-equiv="refresh" content="; url=citas.php" />';
  }
  } /*fin Registrar*/ 

  if ($_POST['submit']=="Nuevo" or $_POST['submit']=="Modificar"){
    if ($_POST['submit']=="Modificar"){
     $sql = 'SELECT * FROM seguimiento WHERE id_seguimiento ="'.$_POST['cod'].'" Limit 1'; 
        $consulta = $mysqli->query($sql);
     /*echo $sql;*/ 
     $row=$consulta->fetch_assoc();
     $textoh1 ="Modificar";
     $textobtn ="Actualizar";
     }
     if ($_POST['submit']=="Nuevo"){
        $textoh1 ="Registrar";
        $textobtn ="Registrar";
     }
 # print_r($_POST);
 if(!empty($_POST['estudiante'])){
  $persona = new Persona($_POST['estudiante']);
  $nombre=$persona->nombre;
 }else{
  $nombre='Registrar';
 }
     echo '<form id="form1" name="form1" method="post" action="citas.php">
     <h1>'.$nombre.'</h1>';
     
     echo '<form id="form1" name="form1" method="post" action="citas.php">';
echo '<p><input name="cod" type="hidden" id="cod" value="<?php echo $textobtn ?>" size="120" required></p>';
 echo "<input class='form-control' type='hidden' id='id_seguimiento' name='id_seguimiento' value='";if (isset($row["id_seguimiento"])){
    echo $row["id_seguimiento"];
} echo "'  ' > <br><input class='form-control' type='hidden' id='id_inscripcion' name='id_inscripcion' value='";if (isset($row["id_inscripcion"])){
    echo $row["id_inscripcion"];
} 

echo "' ' > <br><label>Fecha Seguimiento</label><br>";
echo "<div class='col-'>";
echo "<input class='form-control' type='date' id='fecha_seguimiento' name='fecha_seguimiento' value='";

if (isset($row["fecha_seguimiento"]) && !empty($row["fecha_seguimiento"])) {
    echo $row["fecha_seguimiento"];
} else {
    echo date('Y-m-d'); // Coloca la fecha actual si no hay valor en $row["fecha_seguimiento"]
}

echo "'  >";
echo "</div>";

echo "</div>";
echo "<br><label>Hora Seguimiento</label><br>";
echo "<div class='col-'>";
echo "<input class='form-control' type='time' id='hora_seguimiento' name='hora_seguimiento' value='";

if (isset($row["hora_seguimiento"]) && !empty($row["hora_seguimiento"])) {
    echo $row["hora_seguimiento"];
} else {
    echo date('H:i'); // Coloca la hora actual si no hay valor en $row["hora_seguimiento"]
}

echo "' ' >";
echo "</div>";
            echo "<br><label >Observaciones</label><br>
            <div class='col-'>
            <input autofocus class='form-control' type='text' id='observaciones' name='observaciones' value='";if (isset($row["observaciones"])){
    echo $row["observaciones"];
} echo "'  ' >
            </div>
            <br>";
#print_r($_POST);
 if ($_POST['submit']=="Nuevo"){
    echo '<p><input class="btn btn-outline-secondary" type="submit" name="submit" id="submit" value="Registrar"></p></form>';
 }else{
    echo '<p><input class="btn btn-outline-secondary" type="submit" name="submit" id="submit" value="Actualizar"></p></form>';
 }


} /*fin mixto*/ 
if ($_POST['submit']=='Actualizar'){
    /*recibo los campos del formulario proveniente con el método POST*/ 
    $cod = $_POST['id_seguimiento'];
    /*Instrucción SQL que permite insertar en la BD */ 
    $sql = "UPDATE seguimiento SET id_inscripcion='".$_POST["id_inscripcion"]."',fecha_seguimiento='".$_POST["fecha_seguimiento"]."',hora_seguimiento='".$_POST["hora_seguimiento"]."',observaciones='".$_POST["observaciones"]."' WHERE  id_seguimiento  = ".$cod." ;" ;
 
 /* echo $sql;*/ 
 /*Se conecta a la BD y luego ejecuta la instrucción SQL*/ 
if ($actualizar = $mysqli->query($sql)) {
 /*Validamos si el registro fue ingresado con Éxito*/
  echo '<div class="alert alert-success alert-dismissible">
                  <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                  <h5><i class="icon fas fa-check"></i> Correcto!</h5>
                  Modificación Exitosa
                  </div>'  ; echo '<meta http-equiv="refresh" content=""; url="citas.php" />';
 }else{ 
     echo  '<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
              <h5><i class="icon fas fa-ban"></i> Alerta</h5>
              Modificación Fallida
            </div>'
; } 
echo '<meta http-equiv="refresh" content=""; url="citas.php" />';
} /*fin Actualizar*/ 
 }else{ 
 ?>
 <center>
<b><label>Buscar: </label></b><input placeholder="Buscar.." type="search" id="buscar" onkeyup ="buscar(this.value);" onchange="buscar(this.value);"  style="margin: 15px;">
<b><label>N° de Resultados:</label></b>
<input type="number" min="0" id="numeroresultadosseguimiento" placeholder="Cantidad de resultados" title="Cantidad de resultados" value="10" onkeyup="grabarcookie('numeroresultadosseguimiento',this.value) ;buscar(document.getElementById('buscar').value);" mousewheel="grabarcookie('numeroresultadosseguimiento',this.value);buscar(document.getElementById('buscar').value);" onchange="grabarcookie('numeroresultadosseguimiento',this.value);buscar(document.getElementById('buscar').value);" size="4" style="width: 40px;">
</center>

<span id="txtsugerencias">
<?php 
buscar_seguimiento();
 ?>
</span>
<?php 
}/*fin else if isset cod*/
echo '</center>';
 ?>
<script>
document.getElementById("menu_seguimiento").className ='active '+document.getElementById("menu_seguimiento").className;
</script>
<?php $contenido = ob_get_contents();
ob_clean();
include ("../comun/plantilla.php");
 ?>
