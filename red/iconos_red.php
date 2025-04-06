<script type="text/javascript" src="../comun/js/funciones.js"></script>
<script type="text/javascript" >
  function selecciona_icono(icono){
    document.getElementById("icono").className = icono.className;
     document.getElementById("icon").value = icono.className;
}
</script><link rel="stylesheet" type="text/css" href="<?php echo SGA_COMUN_URL ?>/img/png/icon-sga.php">
<div class="container">
  <!-- Modal -->
  <div class="modal modal-fullscreen fade" id="myModal" role="dialog">
    <div class="modal-dialog">
  <!-- Modal content-->
      <div class="modal-content">
        <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">Click sobre el icono para seleccionar y luego cierra</h4>
      </div>
        <div class="modal-body">
        <input onkeyup="mostrarSugerenciaiconos(this.value)" type="search" placeholder="Buscar" id="busqueda">
<div id="resultadoBusqueda">
  <ul class="bs-glyphicons">
    <?php
    require_once (dirname(__FILE__)."/../comun/config.php");
    require_once (dirname(__FILE__)."/../comun/lib/Zebra_Pagination/Zebra_Pagination.php");
    $paginacion = new Zebra_Pagination();
    $valorBusqueda = $_POST['valorBusqueda'];
    $resultados_por_página = 4; 
$cookiepage = 'page_red';//cookie para mandar el parametro de la página
$paginacion->cookie_page($cookiepage);//requerida para que se envíe el parametro en la paginacion
    $paginacion->fn_js_page('mostrarSugerenciaiconos();');//funcion para buscar despues de pasar la pagina
    $paginacion->records_per_page($resultados_por_página);
    $paginacion->padding(false);
if (isset($_COOKIE["$cookiepage"])) $_GET['page'] = $_COOKIE["$cookiepage"]; 
  $encontrados = glob("../comun/img/icono_curso/*.png");
  if ($valorBusqueda!=""){
  foreach ($encontrados as $id => $nombre){
  if (preg_match('/'.$valorBusqueda.'/',$nombre,$coincidencias)){
  
  }else{
    unset($encontrados[$id]);
  }
  }
  }
  $total_resultados = count($encontrados);
  $paginacion->records($total_resultados);
foreach(array_slice($encontrados, $paginacion->get_page() - 1, $resultados_por_página) as $archivo){ #delimitamos la parte del array que queremos mostrar con array_slice
	$nombre = str_replace(".png","",$archivo);
	$nombre = str_replace("../comun/img/icono_curso/","",$nombre);

	?>
  <li><span  style="margin-left: -5;background-size: 40px 40px;margin-top: 40px;" onclick="selecciona_icono(this);"  class="icon-sga-<?php echo $nombre; ?>"   ></span><?php echo $nombre ; ?></li>
<?php } ?>
  </ul> 
    <?php echo $paginacion->render2();
  ?>
  
  
</div>
    </div><!--div class="modal-body"-->

      </div>

    </div>
  </div>

</div>
