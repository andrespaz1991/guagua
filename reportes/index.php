<?php 
ob_start();
require("../comun/config.php")
 ?>
 <div class="jumbotron">
  <div class="container text-center">
    <h1 class="fip"> REPORTES </h1>      
  </div>
</div>
<div style="background-color:#7bba3f;width:97%;height:5px;align:center"></div>
 <div class="collapse navbar-collapse navbar-ex1-collapse">
<ul  class='nav navbar-nav'>
<!--li><a href="<?php echo SGA_URL ?>/listarEstudiantes.php"><span data-text="MSG" class="icon-sga-house"></span></a></li-->
<li><a href="cursos/index.php"><span data-text="CURSOS" class="icon-sga-notebook"></span></a></li>
<li><a href="<?php echo SGA_URL ?>/foros"><span data-text="FOROS" class="icon-sga-foro"></span></a></li>
<li><a title="Recursos Educativos Digitales" href="<?php echo SGA_URL ?>/reportes/red/estadisticas_red.php"><span data-text="RED" class="icon-sga-app"></span></a></li>
<li><a href="<?php echo SGA_URL ?>/cuestionario"><span data-text="Cuestionarios" class="icon-sga-list"></span></a></li>

 <?php $contenido = ob_get_contents();
ob_clean();
include ("../comun/plantilla.php");
 ?>