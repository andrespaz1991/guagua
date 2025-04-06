<?php 
ob_start();
echo '<center>';
require("comun/conexion.php");
#require("funciones.php");  
function buscar_tarjeta( $datos='', $reporte=''){

    require_once ("comun/lib/Zebra_Pagination/Zebra_Pagination.php");
    $resultados = (isset($_COOKIE['numeroresultadostarjeta']) ? $_COOKIE['numeroresultadostarjeta'] : 10);
    $paginacion = new Zebra_Pagination();
    $paginacion->records_per_page($resultados);
    $paginacion->records_per_page($resultados);
    $cookiepage="page_numeroresultadostarjeta";
    $funcionjs="buscar();";
    $paginacion->fn_js_page("$funcionjs");
    $paginacion->cookie_page($cookiepage);
    $paginacion->padding(true);
    if (isset($_COOKIE["$cookiepage"])) $_GET['page'] = $_COOKIE["$cookiepage"];



if ($reporte=="xls"){
header("Content-type: application/vnd.ms-excel");
header("Content-Disposition: attachment; Filename=tarjeta.xls");
}
require("comun/conexion.php");
$sql='select *  from tarjeta ';
$consulta = $mysqli->query($sql);
$paginacion->records($consulta->num_rows);

$datosrecibidos = $datos;
$datos = explode(" ",$datosrecibidos);
$datos[]='';
$cont =  0;
$sql.= ' WHERE ';
$sql.='';

foreach ($datos as $id => $dato){
    $sql .= 'concat(LOWER(tarjeta.id_tarjeta),"", LOWER(tarjeta.permisos),"", LOWER(tarjeta.titulo),"", LOWER(tarjeta.icono),"", LOWER(tarjeta.funcion),"", LOWER(tarjeta.href),"", LOWER(tarjeta.class_color),"", LOWER(tarjeta.accion_rapida),"", concat(LOWER(tarjeta.id_tarjeta),"", LOWER(tarjeta.permisos),"", LOWER(tarjeta.titulo),"", LOWER(tarjeta.icono),"", LOWER(tarjeta.funcion),"", LOWER(tarjeta.href),"", LOWER(tarjeta.class_color),"", LOWER(tarjeta.accion_rapida),"")) LIKE "%'.mb_strtolower($dato, 'UTF-8').'%"' ;
    $cont ++;
    if (count($datos)>1 and count($datos)<>$cont){
        $sql .= ' and ';
    }
    }
    $sql .=  ' ORDER BY tarjeta.id_tarjeta desc  ';
    if (!isset($_GET['xls'])){
        $sql .=  ' LIMIT ' . (($paginacion->get_page() - 1) * $resultados) . ', ' . $resultados;
        #echo $sql;
        }


    $consulta = $mysqli->query($sql);
    $numero_usuario = $consulta->num_rows;
    $minimo_usuario = (($paginacion->get_page() - 1) * $resultados)+1;
    $maximo_usuario = (($paginacion->get_page() - 1) * $resultados) + $resultados;
    if ($maximo_usuario>$numero_usuario) $maximo_usuario=$numero_usuario;
    $maximo_usuario += $minimo_usuario-1;
    echo '<p>Resultados de ';
    echo $minimo_usuario.' a '.$maximo_usuario.' del total de '.$numero_usuario.'  en página'.$paginacion->get_page().'';
        ?>
    <div align='center'>
<table border='1' id='tb'tarjeta''>
<thead>
<tr>
<th>id_tarjeta</th><th>permisos</th><th>titulo</th><th>icono</th><th>funcion</th><th>href</th><th>class_color</th><th>accion_rapida</th>
<?php if ($reporte==''){ ?>
    <th><form id='formNuevo' name='formNuevo' method='post' action=tarjeta.php>
    <input name='cod' type='hidden' id='cod' value='0'>
    <input type='submit' name='submit' id='submit' value='Nuevo'>
    </form>
    </th>
    <th><form id="formNuevo" name="formNuevo" method="post" action="tarjeta.php?xls">
    <input name="cod" type="hidden" id="cod" value="0">
    <input type="submit" name="submit" id="submit" value="XLS">
    </form>
    </th>
    <?php } ?>
    </tr>
    </thead><tbody>
    <?php 
    while($row=$consulta->fetch_assoc()){
        ?>
       <tr>
       <td><?php echo $row['id_tarjeta']?></td><td><?php echo $row['permisos']?></td><td><?php echo $row['titulo']?></td><td><?php echo $row['icono']?></td><td><?php echo $row['funcion']?></td><td><?php echo $row['href']?></td><td><?php echo $row['class_color']?></td><td><?php echo $row['accion_rapida']?></td> 
       <?php if ($reporte==''){ ?>
       <td>
       <form id='formModificar' name='formModificar' method='post' action=''tarjeta.php'>
       <input name='cod' type='hidden' id='cod' value=' <?php echo $row['id_tarjeta']?>'>
       <input type='submit' name='submit' id='submit' value='Modificar'>
       </form>
       </td>
       <td>
       <input width="50px" type="image" src="img/eliminar.png" onClick="confirmeliminar('tarjeta.php',{'del':'<?php echo $row['id_tarjeta'];?>'},'<?php echo $row['id_tarjeta'];?>');" value="Eliminar">
       </td>
       <?php } ?>
       </tr>
       <?php 
       }/*fin while*/
        ?>
       </tbody>
       </table>
       
       <div class='text-center'>
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
        buscar_tarjeta($_POST['datos']);
    exit();
    }
    if (isset($_GET['xls'])){
     buscar_tarjeta('','xls');
    exit();
    }

if (isset($_POST['del'])){
 /*InstrucciÃ³n SQL que permite eliminar en la BD*/ 
 $sql = 'DELETE FROM tarjeta WHERE id_tarjeta="'.$_POST['del'].'"';
 /*Se conecta a la BD y luego ejecuta la instrucciÃ³n SQL*/
if ($eliminar = $mysqli->query($sql)){
 /*Validamos si el registro fue eliminado con Ã©xito*/ 
echo '
Registro eliminado
<meta http-equiv="refresh" content="1; url="tarjeta.php" />
'; 
}else{
?>
EliminaciÃ³n fallida, por favor compruebe que la usuario no estÃ© en uso
<meta http-equiv="refresh" content="2; url='tarjeta.php" />
<?php 
}
}
 ?>

 <center>
 <h1>tarjeta</h1>
 </center><?php 
 if (isset($_POST['submit'])){
 if ($_POST['submit']=="Registrar"){
  /*recibo los campos del formulario proveniente con el mÃ©todo POST*/ 
  $sql = "INSERT INTO tarjeta(permisos,titulo,icono,funcion,href,class_color,accion_rapida) Values ('".$_POST["permisos"]."','".$_POST["titulo"]."','".$_POST["icono"]."','".$_POST["funcion"]."','".$_POST["href"]."','".$_POST["class_color"]."','".$_POST["accion_rapida"]."')";

  /*echo $sql;*/
  if ($insertar = $mysqli->query($sql)) {
   /*Validamos si el registro fue ingresado con Ã©xito*/ 
  echo 'Registro exitoso';
  echo '<meta http-equiv="refresh" content="1; url=tarjeta.php" />';
   }else{ 
  echo 'Registro fallido';
  echo '<meta http-equiv="refresh" content="1; url=tarjeta.php" />';
  }
  } /*fin Registrar*/ 

  if ($_POST['submit']=="Nuevo" or $_POST['submit']=="Modificar"){
    if ($_POST['submit']=="Modificar"){
     $sql = 'SELECT * FROM tarjeta WHERE id_tarjeta ="'.$_POST['cod'].'" Limit 1'; 
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
     echo '<form id="form1" name="form1" method="post" action="tarjeta.php">
     <h1>'.$textoh1.'</h1>';
     
     echo '<form id="form1" name="form1" method="post" action="tarjeta.php">';
echo '<p><input name="cod" type="hidden" id="cod" value="<?php echo $textobtn ?>" size="120" required></p>';
 echo "<input class='form-control' type='hidden' id='id_tarjeta' name='id_tarjeta' value='";if (isset($row["id_tarjeta"])){
    echo $row["id_tarjeta"];
} echo "'  ' > <br><label >permisos</label><br><input class='form-control' type='text' id='permisos' name='permisos' value='";if (isset($row["permisos"])){
    echo $row["permisos"];
} echo "'  ' > <br><label >titulo</label><br><input class='form-control' type='text' id='titulo' name='titulo' value='";if (isset($row["titulo"])){
    echo $row["titulo"];
} echo "'  ' > <br><label >icono</label><br><input class='form-control' type='text' id='icono' name='icono' value='";if (isset($row["icono"])){
    echo $row["icono"];
} echo "'  ' > <br><label >funcion</label><br><input class='form-control' type='text' id='funcion' name='funcion' value='";if (isset($row["funcion"])){
    echo $row["funcion"];
} echo "'  ' > <br><label >href</label><br><input class='form-control' type='text' id='href' name='href' value='";if (isset($row["href"])){
    echo $row["href"];
} echo "'  ' > <br><label >class color</label><br><input class='form-control' type='text' id='class_color' name='class_color' value='";if (isset($row["class_color"])){
    echo $row["class_color"];
} echo "'  ' > <br><label >accion rapida</label><br><input class='form-control' type='text' id='accion_rapida' name='accion_rapida' value='";if (isset($row["accion_rapida"])){
    echo $row["accion_rapida"];
} echo "'  ' > <br>";
#print_r($_POST);
 if ($_POST['submit']=="Nuevo"){
    echo '<p><input type="submit" name="submit" id="submit" value="Registrar"></p></form>';
 }else{
    echo '<p><input type="submit" name="submit" id="submit" value="Actualizar"></p></form>';
 }


} /*fin mixto*/ 
if ($_POST['submit']=='Actualizar'){
    /*recibo los campos del formulario proveniente con el mÃ©todo POST*/ 
    $cod = $_POST['id_tarjeta'];
    /*InstrucciÃ³n SQL que permite insertar en la BD */ 
    $sql = "UPDATE tarjeta SET permisos='".$_POST["permisos"]."',titulo='".$_POST["titulo"]."',icono='".$_POST["icono"]."',funcion='".$_POST["funcion"]."',href='".$_POST["href"]."',class_color='".$_POST["class_color"]."',accion_rapida='".$_POST["accion_rapida"]."' WHERE  id_tarjeta  = ".$cod." ;" ;
 
 /* echo $sql;*/ 
 /*Se conecta a la BD y luego ejecuta la instrucciÃ³n SQL*/ 
if ($actualizar = $mysqli->query($sql)) {
 /*Validamos si el registro fue ingresado con Ã©xito*/
echo 'ModificaciÃ³n exitosa';
echo '<meta http-equiv="refresh" content="1"; url="tarjeta.php" />';
 }else{ 
echo 'Modificacion fallida';
}
echo '<meta http-equiv="refresh" content="2"; url="tarjeta.php" />';
} /*fin Actualizar*/ 
 }else{ 
 ?>
 <center>
<b><label>Buscar: </label></b><input type="search" id="buscar" onkeyup ="buscar(this.value);" onchange="buscar(this.value);"  style="margin: 15px;">
<b><label>NÂ° de Resultados:</label></b>

<input type="number" min="0" id="numeroresultadostarjeta" placeholder="Cantidad de resultados" title="Cantidad de resultados" value="10" onkeyup="grabarcookie('numeroresultadostarjeta',this.value) ;buscar(document.getElementById('buscar').value);" mousewheel="grabarcookie('numeroresultadostarjeta',this.value);buscar(document.getElementById('buscar').value);" onchange="grabarcookie('numeroresultadostarjeta',this.value);buscar(document.getElementById('buscar').value);" size="4" style="width: 40px;">
</center>

<span id="txtsugerencias">
<?php 
buscar_tarjeta();
 ?>
</span>
<?php 
}/*fin else if isset cod*/
echo '</center>';
 ?>
<script>
document.getElementById("menu_tarjeta").className ='active '+document.getElementById("menu_tarjeta").className;
</script>
<?php $contenido = ob_get_contents();
ob_clean();
include (dirname(__FILE__)."/comun/plantilla.php");
?>
 