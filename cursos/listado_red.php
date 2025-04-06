<?php
require(dirname(__FILE__)."/../comun/conexion.php");
require_once(dirname(__FILE__)."/../comun/config.php");
require_once(dirname(__FILE__)."/../comun/funciones.php");
require_once(dirname(__FILE__)."/../comun/lib/Zebra_Pagination/Zebra_Pagination.php");
$resultados_pordefecto   = 3;
$resultados = (isset($_COOKIE['numeroresultados_red']) ? $_COOKIE['numeroresultados_red'] : $resultados_pordefecto);
//se establecio un condicional para leer la cookie de numero de resultados por pagina, 
//el valor por defecto debe ser igual en el form de la vista para que inicie coherentemente
$paginacion = new Zebra_Pagination();
$paginacion->records_per_page($resultados);
$paginacion->padding(false);
$paginacion->fn_js_page('mostrarSugerenciaRed();');//funcion para buscar despues de pasar la pagina
$cookiepage = 'page_red';//cookie para mandar el parametro de la página
$paginacion->cookie_page($cookiepage);
if (isset($_COOKIE["$cookiepage"])) $_GET['page'] = $_COOKIE["$cookiepage"];
$sql = 'select * from red';
if (isset($_POST['valorBusqueda']) and $_POST['valorBusqueda']<>""){
    $sql.=' where LOWER(`red`.`titulo_red`) LIKE "%'.mb_strtolower($_POST['valorBusqueda'], 'UTF-8').'%"';
}
#echo $sql;
$consulta = $mysqli -> query ($sql);
$cantidad = $consulta -> num_rows;//Num rows se aplica despues de un query, no a mysqli
$paginacion->records($cantidad);
$sql .= " order by id_red desc LIMIT " . (($paginacion->get_page() - 1) * $resultados) . ", " .$resultados.";";
$consulta = $mysqli -> query ($sql);
if ($consulta -> num_rows ){ ?>
<div  id="container" class="container">
  <ul  class="bs-glyphicons">
<?php while ( $row = $consulta -> fetch_assoc()) { ?>
      <li style="background-color:'';" onclick="obtener_red(document.getElementById('iconred_<?php echo $row['id_red'];?>'))" id="li<?php echo  $row['id_red']?>" ondblclick="window.open('../red/visor_red.php?red=<?php echo $row['id_red'] ?>&formato=<?php echo $row['formato'] ?>&enlace=<?php echo $row['enlace']; ?>&scorm=<?php echo $row['scorm'] ?>')"  class="filas" onclick="obtener_red(document.getElementById('iconred_<?php echo $row['id_red'];?>'))">
 <?php 
 $TitleNivel = str_replace("[", "", $row['nivel_eductivo']); $TitleNivel = str_replace("]", "", $TitleNivel);$TitleNivel= str_replace('"', '', $TitleNivel); ?>
     <div  title="Asignatura: <?php echo $row['nombre_materia']; ?> , Nivel Educativo <?php echo $TitleNivel;?>" class="col-md-5">   <span onclick="obtener_red(this)" id="iconred_<?php echo $row['id_red'];?>" data-nombre="<?php echo $row['id_red'].'.'.$row['titulo_red']  ?>" data-id="<?php echo $row['id_red']  ?>">
        <span style="position: absolute;width: 40px;height: 40px;background-image: url('<?php echo SGA_COMUN_URL."/img/png/".$row['imagen_icono']; ?>');background-size: 100% 100%;margin-top: 50px;"></span> 
        <span><?php echo puntos_suspensivos($row['titulo_red'], 15); ?></span>
        </span></div>
      </li>
<?php } ?>
  </ul>
</div>
<?php $paginacion->render2();//se lanza render2 para que tome las funciones del parche con javascript
}else{
echo 'No hay resultados :(';	
}
?>
