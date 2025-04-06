<nav class="navbar navbar-default" permisoe="navigation">
  <!-- El logotipo y el icono que despliega el menú se agrupan
       para mostrarlos mejor en los dispositivos móviles -->
  <div class="navbar-header">
    <button type="button" class="navbar-toggle" data-toggle="collapse"
            data-target=".navbar-ex1-collapse">
      <span class="sr-only">Desplegar navegaci&oacute;n</span>
      <span class="icon-bar"></span>
      <span class="icon-bar"></span>
      <span class="icon-bar"></span>
    </button>
    <!--a class="navbar-brand" href="#">Evaluaci&oacute;n</a-->
  </div>
  <!-- Agrupar los enlaces de navegación, los formularios y cualquier
       otro elemento que se pueda ocultar al minimizar la barra -->
  <div class="collapse navbar-collapse navbar-ex1-collapse">
    <ul class="nav navbar-nav">
    
     <?php 
     @session_start(); if (!isset($_SESSION['tipo']))  { 
 @session_start();
  $_SESSION['tipo'] ='';
}
@session_start(); if (isset($_SESSION['tipo']) and $_SESSION['tipo']=="estudiante"){ 
@session_start(); if (isset($_SESSION['tipo'])) { 

?>
    <li><a href="evaluacion.php"><span class="glyphicon glyphicon-home"></span>&nbsp;&nbsp;Inicio</a></li>
        <?php 
}
}
@session_start(); if (isset($_SESSION['tipo']) and $_SESSION['tipo']<>"docente" and $_SESSION['tipo']<>"estudiante") { 

		if(isset($_SESSION['rol']) and $_SESSION['rol']<>"estudiante"  ){
      ?>
      <li class="divider"></li>
        <li class="dropdown">
        <a href="#" class="dropdown-toggle" data-toggle="dropdown">
         <span class="glyphicon glyphicon-wrench"></span>&nbsp;&nbsp;Administrar <b class="caret"></b>
        </a>
        <ul class="dropdown-menu">
            
              <li><a href="docente.php"><span class="glyphicon glyphicon-user"></span> Usuarios</a></li>
              <li><a href="../../usuario/usuario.php?u=estudiante"><span class="glyphicon glyphicon-user"></span> Estudiante</a></li>
              <li><a href="grado.php"><span class="glyphicon glyphicon-list-alt"></span> Grado</a></li>
              <li><a href="area.php"><span class="glyphicon glyphicon-list-alt"></span> Area</a></li>
              <li><a href="escala.php"><span class="glyphicon glyphicon-list"></span>&nbsp;&nbsp;Escala</a></li>
              <li><a href="ano_lectivo.php"><span class="glyphicon glyphicon-calendar"></span> Año Lectivo</a></li>
              <!--li><a href="//<?php // echo $_SERVER['SERVER_NAME'] ;?>/php/periodo.php"><span class="glyphicon glyphicon-calendar"></span> Periodo</a></li-->
        </ul>
      </li>
      <li class="divider"></li>
      <li class="dropdown">
        <a href="#" class="dropdown-toggle" data-toggle="dropdown"><span class="glyphicon glyphicon-transfer"></span> Asignaci&oacute;n <b class="caret"></b>
        </a>
      <ul class="dropdown-menu">
      <li><a href="../../planeador/materia.php"><span class="	glyphicon glyphicon-book"></span> Asignatura</a></li>
      
      <li><a href="curso.php"><span class="glyphicon glyphicon-folder-open"></span> Curso</a></li>
      <li><a href="asignacion.php"><span class="glyphicon glyphicon-transfer"></span> Asignaci&oacute;n Acad&eacute;mica</a></li>
         <li><a href="config.php"><span class="glyphicon glyphicon-user"></span> Configruación</a></li>
            <li><a href="categoria.php"><span class="glyphicon glyphicon-folder-open"></span> Categoria</a></li>
      <li><a href="pregunta.php"><span class="glyphicon glyphicon-folder-open"></span> Preguntas</a></li>

      </ul>
      </li>
      <!--li><a href="//<?php //echo $_SERVER['SERVER_NAME'] ;?>/php/evaluacion.php"><span class="glyphicon glyphicon-ok-circle"></span> Evaluar</a></li-->
      <li><a href="ver_resultados.php"><span class="glyphicon glyphicon-stats"></span> Resultados</a></li>
    
  
      
      <?php } ?>
    </ul>
    <ul class="nav navbar-nav navbar-right">
        <!--li><a href="//<?php echo $_SERVER['SERVER_NAME'] ;?>/manual.pdf"><span class="glyphicon glyphicon-question-sign"></span>&nbsp;&nbsp;Ayuda</a></li-->
      <?php 
		if(isset($_SESSION['tipo'])){
      ?>
      
      <li class="dropdown">
        <a href="#" class="dropdown-toggle" data-toggle="dropdown"><span class="glyphicon glyphicon-user"></span>&nbsp;&nbsp;
          <?php echo $_SESSION['nombre_usu']; ?> <b class="caret"></b>
        </a>
        <ul class="dropdown-menu">
          <li><a href="mi_cuenta.php"><span class="glyphicon glyphicon-user"></span>&nbsp;&nbsp;Mi Perfil</a></li>
          <li><a href="../index.php"><span class="glyphicon glyphicon-log-out"></span>&nbsp;&nbsp;Salir</a></li>
        </ul>
      </li>
      <?php 
		}
		}
		if(!isset($_SESSION['tipo'])){
      ?>
       <!--li><a href="sesion.php"><span class="glyphicon glyphicon-log-in"></span>&nbsp;&nbsp;Iniciar Sesión</a></li-->
       <?php 
		}
		
		@session_start(); if (isset($_SESSION['tipo']) and $_SESSION['tipo']=="docente") { 

      ?>
      <!--li><a href="//<?php echo $_SERVER['SERVER_NAME'] ;?>/php/ver_resultados.php"><span class="glyphicon glyphicon-stats"></span> Resultados</a></li-->
<?php } 


@session_start(); if (isset($_SESSION['tipo']) and $_SESSION['tipo']=="estudiante" or $_SESSION['tipo']=="docente") { 
@session_start(); if (isset($_SESSION['tipo'])) { 

      ?>
      <?php @session_start();
      if ($_SESSION['tipo']=="docente") {
        ?>
      <li><a href="resultados.php"><span class="glyphicon glyphicon-home"></span>&nbsp;&nbsp;Inicio </a></li>
      <?php } ?>
      
    <li>  <a><span class="glyphicon glyphicon-user"></span>&nbsp;&nbsp;
          <?php echo "Bienvenido(a) ".$_SESSION['nombre_usu']; ?>
        </a></li>
        
        <li><a href="cclave.php"><span class="glyphicon glyphicon-wrench"></span>&nbsp;&nbsp;Cambiar Contraseña</a></li>
        
          <li><a href="../index.php"><span class="glyphicon glyphicon-log-out"></span>&nbsp;&nbsp;Salir</a></li>
<?php } } ?>
              <!--li><a href="../Manual.pdf"><span class="glyphicon glyphicon-log-out"></span>&nbsp;&nbsp;Ayuda</a></li-->

    </ul>
  </div>
</nav>
<!-- http://librosweb.es/libro/bootstrap_3/capitulo_6/barras_de_navegacion.html -->