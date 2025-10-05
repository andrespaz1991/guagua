<?php 
ob_start();
echo '<center>';
require("conexion.php");
#require("funciones.php");  
function buscar_menu_items2( $datos='', $reporte=''){
require_once ("lib/Zebra_Pagination/Zebra_Pagination.php");
$resultados = (isset($_COOKIE['numeroresultadosmenu_items2']) ? $_COOKIE['numeroresultadosmenu_items2'] : 10);
$paginacion = new Zebra_Pagination();
$paginacion->records_per_page($resultados);
$paginacion->records_per_page($resultados);
$cookiepage="page_numeroresultadosmenu_items2";
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
        header("Content-Disposition: attachment; Filename=menu_items2.xls");
    }
    
    #header("Location:menu_items2.php");
    }require("conexion.php");
$sql='select * from   menu_items2 ';
$consulta = $mysqli->query($sql);
$paginacion->records($consulta->num_rows);

$datosrecibidos = $datos;
$datos = explode(" ",$datosrecibidos);
$datos[]='';
$cont =  0;
$sql .= ' WHERE ';
if(!empty($_GET['xls'])){
    $sql.= "  menu_items2.menu_item_id= '".$_GET['xls']."'";
}else{
    foreach ($datos as $id => $dato){
        $sql .= 'concat(LOWER(menu_items2.menu_item_id),"", LOWER(menu_items2.menu_item_name),"", LOWER(menu_items2.menu_description),"", LOWER(menu_items2.menu_url),"", LOWER(menu_items2.menu_parent_id),"", LOWER(menu_items2.url_target),"", LOWER(menu_items2.categoria),"", LOWER(menu_items2.icono),"", concat(LOWER(menu_items2.menu_item_id),"", LOWER(menu_items2.menu_item_name),"", LOWER(menu_items2.menu_description),"", LOWER(menu_items2.menu_url),"", LOWER(menu_items2.menu_parent_id),"", LOWER(menu_items2.url_target),"", LOWER(menu_items2.categoria),"", LOWER(menu_items2.icono),"")) LIKE "%'.mb_strtolower($dato, 'UTF-8').'%"' ;
        $cont ++;
        if (count($datos)>1 and count($datos)<>$cont){
            $sql .= ' and ';
        }
        }
        $sql .=  ' ORDER BY menu_items2.menu_item_id desc  ';
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
  
<table class="table" border='1' id='tbmenu_items2'>
<thead class="thead-dark">
<tr>
<th>Menu Item Id</th><th>Menu Item Name</th><th>Menu Description</th><th>Menu Url</th><th>Menu Parent Id</th><th>Url Target</th><th>Categoria</th><th>Icono</th>
<?php if ($reporte==''){ ?>
    <th ><form id='formNuevo' name='formNuevo' method='post' action=menu_items2.php>
    <input name='cod' type='hidden' id='cod' value='0'>
    <input class="btn btn-light" type='submit' name='submit' id='submit' value='Nuevo'>
    </form>
    </th><th  ><form id="formNuevo" name="formNuevo" method="post" action=menu_items2.php?xls>
    <input name="cod" type="hidden" id="cod" value="0"><input class="btn btn-success" type="submit" name="submit" id="submit" value="XLS"><a target="_blank" href='reporte_menu_items2.php'><button type="button" class="btn btn-danger">PDF</button>
        </a></form>
    </th><?php } ?>
    </tr>
    </thead><tbody>
    <?php 
    while($row=$consulta->fetch_assoc()){
        ?>
       <tr>
       <td><?php echo $row['menu_item_id']?></td><td><?php echo $row['menu_item_name']?></td><td><?php echo $row['menu_description']?></td><td><?php echo $row['menu_url']?></td><td><?php echo $row['menu_parent_id']?></td><td><?php echo $row['url_target']?></td><td><?php echo $row['categoria']?></td><td><?php echo $row['icono']?></td> 
       <?php if ($reporte==''){ ?>
       <td>
       <form id='formModificar' name='formModificar' method='post' action=''menu_items2.php'>
       <input name='cod' type='hidden' id='cod' value=' <?php echo $row['menu_item_id']?>'>
       <input class="btn btn-outline-primary" type='submit' name='submit' id='submit' value='Modificar'>
       <button type="button" class="btn btn-outline-danger" onClick="confirmeliminar('menu_items2.php',{'del':'<?php echo $row['menu_item_id'];?>'},'<?php echo $row['menu_item_id'];?>');">Eliminar</button>
       </form>     
       </td><td>
       <a target="_blank" href='menu_items2.php?xls=<?php echo $row['menu_item_id']?>'><button type="button" class="btn btn-success">XLS</button>
       </a><a target="_blank" href="reporte_menu_items2.php?id=<?php echo $row['menu_item_id']?>"> <button type="button" class="btn btn-danger">PDF</button></a></td><?php } ?>
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
        buscar_menu_items2($_POST['datos']);
    exit();
    }
    if (isset($_GET['xls'])){
     buscar_menu_items2('','xls');
    exit();
    }

if (isset($_POST['del'])){
 /*Instrucción SQL que permite eliminar en la BD*/ 
 $sql = 'DELETE FROM menu_items2 WHERE menu_item_id="'.$_POST['del'].'"';
 /*Se conecta a la BD y luego ejecuta la instrucción SQL*/
if ($eliminar = $mysqli->query($sql)){
 /*Validamos si el registro fue eliminado con Éxito*/  echo '<div class="alert alert-success alert-dismissible">
                  <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                  <h5><i class="icon fas fa-check"></i> Correcto!</h5>
                  Registro Eliminado
                  </div>' ;
?> <meta http-equiv="refresh" content="; url="menu_items2.php" />
<?php
}else{ 
 echo  '<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
              <h5><i class="icon fas fa-ban"></i> Alerta</h5>
              Eliminación Fallida
            </div>';
?> 
<meta http-equiv="refresh" content="; url='menu_items2.php" />
<?php 
}
}
 ?>

 <center>
 <h1>Menu_items2</h1>
 </center><?php 
 if (isset($_POST['submit'])){
 if ($_POST['submit']=="Registrar"){
  /*recibo los campos del formulario proveniente con el método POST*/ 
  $sql = "INSERT INTO menu_items2(menu_item_name,menu_description,menu_url,menu_parent_id,url_target,categoria,icono) Values ('".$_POST["menu_item_name"]."','".$_POST["menu_description"]."','".$_POST["menu_url"]."','".$_POST["menu_parent_id"]."','".$_POST["url_target"]."','".$_POST["categoria"]."','".$_POST["icono"]."')";

  /*echo $sql;*/
  if ($insertar = $mysqli->query($sql)) {
   /*Validamos si el registro fue ingresado con Éxito*/ 
    echo '<div class="alert alert-success alert-dismissible">
                  <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                  <h5><i class="icon fas fa-check"></i> Correcto!</h5>
                  Registro Exitoso
                  </div>' 
   ; echo '<meta http-equiv="refresh" content="; url=menu_items2.php" />';
   }else{ 
     echo  '<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
              <h5><i class="icon fas fa-ban"></i> Alerta</h5>
              Registro fallido
            </div>'
    ; echo '<meta http-equiv="refresh" content="; url=menu_items2.php" />';
  }
  } /*fin Registrar*/ 

  if ($_POST['submit']=="Nuevo" or $_POST['submit']=="Modificar"){
    if ($_POST['submit']=="Modificar"){
     $sql = 'SELECT * FROM menu_items2 WHERE menu_item_id ="'.$_POST['cod'].'" Limit 1'; 
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
     echo '<form id="form1" name="form1" method="post" action="menu_items2.php">
     <h1>'.$textoh1.'</h1>';
     
     echo '<form id="form1" name="form1" method="post" action="menu_items2.php">';
echo '<p><input name="cod" type="hidden" id="cod" value="<?php echo $textobtn ?>" size="120" required></p>';
 echo "<input class='form-control' type='hidden' id='menu_item_id' name='menu_item_id' value='";if (isset($row["menu_item_id"])){
    echo $row["menu_item_id"];
} echo "'  ' > <br><label >Menu Item Name</label><br>
            <div class='col-'>
            <input  class='form-control' type='text' id='menu_item_name' name='menu_item_name' value='";if (isset($row["menu_item_name"])){
    echo $row["menu_item_name"];
} echo "'  ' >
            </div>
            <br><label >Menu Description</label><br>
            <div class='col-'>
            <input  class='form-control' type='text' id='menu_description' name='menu_description' value='";if (isset($row["menu_description"])){
    echo $row["menu_description"];
} echo "'  ' >
            </div>
            <br><label >Menu Url</label><br>
            <div class='col-'>
            <input  class='form-control' type='text' id='menu_url' name='menu_url' value='";if (isset($row["menu_url"])){
    echo $row["menu_url"];
} echo "'  ' >
            </div>
            <br><label >Menu Parent Id</label><br>
            <div class='col-'>
            <input  class='form-control' type='text' id='menu_parent_id' name='menu_parent_id' value='";if (isset($row["menu_parent_id"])){
    echo $row["menu_parent_id"];
} echo "'  ' >
            </div>
            <br><label >Url Target</label><br>
            <div class='col-'>
            <input  class='form-control' type='text' id='url_target' name='url_target' value='";if (isset($row["url_target"])){
    echo $row["url_target"];
} echo "'  ' >
            </div>
            <br><label >Categoria</label><br>
            <div class='col-'>
            <input  class='form-control' type='text' id='categoria' name='categoria' value='";if (isset($row["categoria"])){
    echo $row["categoria"];
} echo "'  ' >
            </div>
            <br><label >Icono</label><br>
            <div class='col-'>
            <input  class='form-control' type='text' id='icono' name='icono' value='";if (isset($row["icono"])){
    echo $row["icono"];
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
    $cod = $_POST['menu_item_id'];
    /*Instrucción SQL que permite insertar en la BD */ 
    $sql = "UPDATE menu_items2 SET menu_item_name='".$_POST["menu_item_name"]."',menu_description='".$_POST["menu_description"]."',menu_url='".$_POST["menu_url"]."',menu_parent_id='".$_POST["menu_parent_id"]."',url_target='".$_POST["url_target"]."',categoria='".$_POST["categoria"]."',icono='".$_POST["icono"]."' WHERE  menu_item_id  = ".$cod." ;" ;
 
 /* echo $sql;*/ 
 /*Se conecta a la BD y luego ejecuta la instrucción SQL*/ 
if ($actualizar = $mysqli->query($sql)) {
 /*Validamos si el registro fue ingresado con Éxito*/
  echo '<div class="alert alert-success alert-dismissible">
                  <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                  <h5><i class="icon fas fa-check"></i> Correcto!</h5>
                  Modificación Exitosa
                  </div>'  ; echo '<meta http-equiv="refresh" content=""; url="menu_items2.php" />';
 }else{ 
     echo  '<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
              <h5><i class="icon fas fa-ban"></i> Alerta</h5>
              Modificación Fallida
            </div>'
; } 
echo '<meta http-equiv="refresh" content=""; url="menu_items2.php" />';
} /*fin Actualizar*/ 
 }else{ 
 ?>
 <center>
<b><label>Buscar: </label></b><input placeholder="Buscar.." type="search" id="buscar" onkeyup ="buscar(this.value);" onchange="buscar(this.value);"  style="margin: 15px;">
<b><label>N° de Resultados:</label></b>
<input type="number" min="0" id="numeroresultadosmenu_items2" placeholder="Cantidad de resultados" title="Cantidad de resultados" value="10" onkeyup="grabarcookie('numeroresultadosmenu_items2',this.value) ;buscar(document.getElementById('buscar').value);" mousewheel="grabarcookie('numeroresultadosmenu_items2',this.value);buscar(document.getElementById('buscar').value);" onchange="grabarcookie('numeroresultadosmenu_items2',this.value);buscar(document.getElementById('buscar').value);" size="4" style="width: 40px;">
</center>

<span id="txtsugerencias">
<?php 
buscar_menu_items2();
 ?>
</span>
<?php 
}/*fin else if isset cod*/
echo '</center>';
 ?>
<script>
document.getElementById("menu_menu_items2").className ='active '+document.getElementById("menu_menu_items2").className;
</script>
<?php $contenido = ob_get_contents();
ob_clean();
require ("../comun/plantilla.php");
?>
 