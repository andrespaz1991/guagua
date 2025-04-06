<?php 
require_once (dirname(__FILE__)."/funciones.php");
require_once (dirname(__FILE__)."/config.php");
require_once (dirname(__FILE__)."/autoload.php");
#require_once ("../../comun/autoload.php");
if(isset($_GET['logout'])){
  session_destroy();
session_unset();
}

if(isset($_SESSION['id_usuario'])){
  $persona=new Persona($_SESSION['id_usuario']);
}
$array_roles = array("admin"=>"Administrador","docente"=>"Docente","estudiante"=>"Estudiante","acudiente"=>"Acudiente");
?>
<audio  id="player" src="<?php echo  SGA_COMUN_URL.'/audio/fondo.mp3' ?>"> </audio>
<nav class='navbar navbar-default' role='navigation'><div class="navbar-header">
    <button type="button" class="navbar-toggle" data-toggle="collapse"
            data-target=".navbar-ex1-collapse">
      <span class="sr-only">Desplegar navegaci&oacute;n</span>
      <span class="icon-bar"></span>
      <span class="icon-bar"></span>
      <span class="icon-bar"></span>
    </button>
<?php 
@session_start();
?>
  <a class="navbar-brand" href="<?php echo SGA_URL ?>/index.php" id="titulo"><?php echo deletrear("Guagua"); 
  if(!empty($_SESSION['id_institucion'])){
    require_once dirname(__FILE__)."/autoload.php";
    $institucion=new Institucion($_SESSION['id_institucion']);
  }

 ?>
 
<?php if(!empty($institucion) and !empty($_SESSION['id_institucion'])){
   require_once dirname(__FILE__)."/autoload.php";
   $institucion=new Institucion($_SESSION['id_institucion']);
 ?>
  
    <?php } ?>
  </a>

</div>

<div class="collapse navbar-collapse navbar-ex1-collapse">
<ul  class='nav navbar-nav'>
<li><a href="<?php echo SGA_URL ?>/index.php"><span data-text="INICIO" class="icon-sga-house"></span></a></li>
<?php
 if(isset($_SESSION['app']) and $_SESSION['app']=="seguimiento"){ ?>
<li><a href="<?php echo SGA_URL ?>/index.php"><span data-text="Seguimiento" class="icon-sga-house"></span></a></li>
<?php  } ?>
<?php if(isset($_SESSION['id_usuario'])){ ?>
<li><a href="<?php echo SGA_URL ?>/cursos"><span data-text="CURSOS" class="icon-sga-notebook"></span></a></li>
<!--li><a href="<?php echo SGA_URL ?>/foros"><span data-text="FOROS" class="icon-sga-foro"></span></a></li-->
<?php if ($_SESSION['rol']=="admin" or $_SESSION['rol']=="docente" or $_SESSION['rol']=="estudiante" or $_SESSION['rol']=="acudiente" ){ ?>
<li><a title="Recursos Educativos Digitales" href="<?php echo SGA_URL ?>/red"><span data-text="RED" class="icon-sga-app"></span></a></li>
<?php } ?>
<?php if ($_SESSION['rol']=="admin" or $_SESSION['rol']=="docente"){ ?>
<li><a href="<?php echo SGA_URL ?>/Planeador/planeador_vallesol.php"><span data-text="PLAN" class="icon-sga-archive-1"></span></a></li>
<?php } ?>
<li><a href="<?php echo SGA_REPORTES_URL ?>/informe_docente.php"><span data-text="Datos" class="icon-sga-time"></span></a></li>
<li><a href="<?php echo SGA_SEGUIMIENTO_URL ?>/"><span data-text="APPS" class="icon-sga-smartphone-7"></span></a></li>
<?php
#print_r($institucion);
} ?>
<li><a title="Copia" href="<?php echo SGA_URL ?>/reportes/copia.php?web=1"><span data-text="Copia" class="icon-sga-database-2"></span></a></li>

<li><a title="Copia" target="_blank" href="<?php echo SGA_URL ?>/ia/reporte.php"><span data-text="Alerta" class="icon-sga-statistics"></span></a></li>

<li><a title="Diario" target="_blank" href="<?php echo SGA_URL ?>/seguimiento/citas.php"><span data-text="Diario" class="icon-sga-notepad-2"></span></a></li>

<li><a title="Seguimiento" target="_blank" href="<?php echo SGA_URL ?>/seguimiento/citas.php"><span data-text="Seguimiento" class="icon-sga-list-15"></span></a></li>


<?php
if(isset($_SESSION['rol']) and $_SESSION['rol']=="admin"){ 
?>
<?php
}
if (isset($_SESSION['barra_busqueda']) and $_SESSION['barra_busqueda'] == "actividad_curso") { ?>
    <buscar class="navbar-form navbar-right" role="search" method="post">
      <div class="form-group">
    <?php
    if (isset($_GET['asignacion'])) $_SESSION['asigna']= $_GET['asignacion'];       ?>
        <select class="form-control" onchange="PlaceholderBusquedaActividades();" id="menu_actividad" name="campo_red">
         <option value="nombre_actividad">Nombre</option>
         <option value="Observaciones">Observaciones</option>
         <option value="adjunto">Adjunto</option>
         <option value="periodo">Periodo</option>
         <option value="visible">Visible</option>
         <option value="evaluable">Evaluable</option>
        <option value="fecha_entrega">Fecha Entrega</option>
        <option value="cuestionario">Cuestionario</option>
        <option value="foro">Foro</option>
         </select>      
<input type="hidden" id="asigna" value ="<?php echo $_SESSION['asigna'] ; ?>"/><input  type="search" class="form-control" id="actividad_curso" name="texto" placeholder="Eje:Taller 1" value="" onfocus="buscar_actividad_curso(this.value)" onchange="buscar_actividad_curso(this.value)" onkeyup="buscar_actividad_curso(this.value)">
      </div>
    </buscar>
<?php } 
if (isset($_SESSION['barra_busqueda']) and $_SESSION['barra_busqueda'] == "cursos") { ?>
    <buscar class="navbar-form navbar-right" role="search" method="post">
      <div class="form-group">
        <select onchange="buscar_mis_cursos();" title="Seleccione criterio de búsqueda" style="z-index:101;position:relative" class="form-control" id="campo_cursos" name="campo_cursos">
          <option value="nombre_materia" >Materia</option>
          <option value="nombre_docente" >Docente</option>
          <option value="nombre_categoria" >Categoría</option>
          <option value="anio" >Año</option>
          <option value="todos" >Todos</option>
          </select>
          </div>
      <div style="margin-right:50px;" class="form-group">
 <input autofocus type="search" class="form-control input-xs" id="buscarcurso" name="texto" placeholder="Buscar" onkeyup="buscar_mis_cursos(this.value)">
      </div>
      <!--a id="search_curso" type="submit" class="btn btn-default" onclick="buscar_mis_cursos(document.getElementById('buscarcurso').value)"><span class="glyphicon glyphicon-search"></span></a-->
    </buscar>
<?php } ?>
<?php if(isset($_SESSION['modulo']) and $_SESSION['modulo']=="red"){ ?>
<li><a href="<?php echo SGA_URL; ?>/red/index.php"><span class="icon-sga-app" data-text="RED"></span></a></li>
<?php } ?>
</ul>
    <ul class="nav navbar-nav navbar-right" >
      <?php 
		if(isset($_SESSION['id_usuario'])){
       ?>
      <li class="dropdown">
      <div class="row">
        <span   onclick="window.location='<?php echo SGA_COMUN_URL ?>/podium.php'" onmouseover="playclip('<?php echo SGA_COMUN_URL ?>/audio/monedas1.mp3')" title="Tienes <?php echo $persona->puntos; ?> Puntos" id="icono_moneda" class="icon-sga-moneda"><?php echo $persona->puntos; ?></span>
      </div>
        <script>
             $('#icono_moneda').tooltip(); 
        </script>
        <a href="#" class="dropdown-toggle" data-toggle="dropdown">
        <span id="a_badge_num_ms" onmouseover="playclip()"><span class="badge" id="badge_num_ms" num="<?php echo num_mensajes(); ?>"><?php echo num_mensajes(); ?></span></span>
        <span title="Mis Mensajes" id="icono_mensaje" onmouseover="playclip()" onclick="location.href='<?php echo SGA_MENSAJE_URL ?>/mis_mensajes.php'" class="icon-sga-mensaje"></span></a>
        <script>
               $('#icono_mensaje').tooltip(); 
      </script>
      </li>
      <script>
        notificar_mensajes();
          </script>
      <li class="dropdown" style="margin-right:130px">
        <a href="#" class="dropdown-toggle" data-toggle="dropdown" title="<?php echo $persona->nombre." ".$persona->apellido; ?>">
            <span id="icon-miperfil" class="icon-miperfil icon-sga-settings-4"></span>
        </a>
        <script>
             $('#icon-miperfil').tooltip(); 
        </script>
<ul class="dropdown-menu">
  <?php if($_SESSION['rol']=="docente"){ ?>
  <li><a href="<?php echo SGA_PLANEADOR_URL ?>/institucion_educativa.php">Ajustes de la Institución</a></li>
   <li><a href="<?php echo SGA_PLANEADOR_URL ?>/materia.php">Materias</a></li>
      <li><a href="<?php echo SGA_PLANEADOR_URL ?>/actividad.php">Actividades de planeación</a></li>
       <li><a href="<?php echo SGA_PLANEADOR_URL ?>/estrategias.php">Estrategias de planeación</a></li>
       <li><a href="<?php echo SGA_PLANEADOR_URL ?>/contenido.php">Contenido de planeación</a></li>
       <li><a href="<?php echo SGA_PLANEADOR_URL ?>/objetivos.php">Objetivos de planeación</a></li>
   <li><a href="<?php echo SGA_CURSOS_URL ?>/asignacion.php">Asignación</a></li>

  <li id="menu_area"><a href="<?php echo SGA_CURSOS_URL ?>/area.php">Área</a></li>
  <li id="menu_categoria_curso"><a href="<?php echo SGA_CURSOS_URL ?>/categoria_curso.php">Categoria</a></li>
  <li id="menu_ano_lectivo"><a href="<?php echo SGA_CURSOS_URL ?>/ano_lectivo.php">Año Lectivo</a></li>
  <li id="menu_periodo" ><a href="<?php echo SGA_COMUN_URL; ?>/periodo.php">Periodos</a></li>
  <li id="menu_Mascotas"><a href="<?php echo SGA_COMUN_URL ?>/Mascotas.php">Mascotas</a></li>
  <li id="menu_iconos"><a href="<?php echo SGA_COMUN_URL ?>/iconos.php">Iconos</a></li>
  <li id="menu_admin"><a href="<?php echo SGA_URL; ?>/usuario/usuario.php?u=admin">Administradores</a></li>
  <li id="menu_docente" ><a href="<?php echo SGA_URL; ?>/usuario/usuario.php?u=docente">Docentes</a></li>
  <li id="menu_estudiante" ><a href="<?php echo SGA_URL; ?>/usuario/usuario.php?u=estudiante">Estudiantes</a></li>
  <li id="menu_acudiente" ><a href="<?php echo SGA_URL; ?>/usuario/usuario.php?u=acudiente">Acudientes</a></li>
  <li id="menu_acudiente" ><a href="<?php echo SGA_EVAL_URL; ?>/php/matricula.php">Inscripciones</a></li>
  <?php } ?>
<?php if($_SESSION['rol']=="docente"){ ?>
<li><a href="<?php echo SGA_URL ?>/asistencia/reporte_asistencia_docente.php">Reporte de horas</a></li>
<?php } ?>
  <li><a href="<?php echo SGA_USUARIO_URL ?>/login.php?logout">Salir</a></li>
</ul>
</li>
<?php 
} ?>
<li>
  <label onclick="playclip();verificar_sonido();" style="margin-right:120px;margin-left:-110px">
    <input title="sonido" hidden id="sonidos" onchange="grabarcookie('sonidos',this.checked);
    document.getElementById('icono_sonidos').className = this.checked ? 'icon-sga-speaker-sga' : 'icon-sga-mute'" type="checkbox"    ><span style="
<?php
@session_start(); if(!isset($_SESSION['rol'])  ){
echo 'margin-top:8px;margin-left:110px;';  }	  ?>
<?php
@session_start(); if(isset($_SESSION['login']) ){
echo 'display:none;';  }	  ?>
  width: 40px;
height: 40px;
background-size: 40px 40px;" id="icono_sonidos" class="icon-sga-speaker-sga"></span></label></li><?php #echo $_SESSION['rol'] ; ?>
<script>
window.onload = function() {
  verificar_sonido();
};

</script>

<?php
if(!isset($_SESSION['id_usuario'])){
?>
<li><a href="<?php echo SGA_USUARIO_URL ?>/login.php"><span class="glyphicon glyphicon-log-in"></span> Iniciar Sesión</a></li>
<?php 
}
?>
</ul>
    <?php if(isset($_SESSION['barra_busqueda']) and $_SESSION['barra_busqueda'] == "foros"){ ?>
    <buscar class="navbar-form navbar-right" role="search" method="post">
      <div class="form-group">
        <select class="form-control" name="criterio" onchange="document.getElementById('buscar_foro').type=this.options[this.selectedIndex].getAttribute('data-fn');">
          <option data-fn="text" value="tema_foro">Tema</option>
          <option data-fn="date" value="fecha">Fecha</option>
          <option data-fn="text" value="usuario">Usuario</option>
        </select>
      </div>
      <div class="form-group">
        <input id="buscar_foro" type="text" class="form-control"  name="texto" placeholder="Buscar">
      </div>
      <!--button type="submit" class="btn btn-default"><span class="	glyphicon glyphicon-search"></span></button-->
    </buscar>
    <?php } ?>
    <?php if(isset($_SESSION['barra_busqueda']) and $_SESSION['barra_busqueda']== "mensajes"){ ?>
    <buscar class="navbar-form navbar-right" role="search" method="post">
      <div class="form-group">
<input class="form-control" type="search" id="txt_buscar_mensaje" onkeyup="this.change();" onchange ="buscar_mensaje(this.value);" placeholder="Buscar">
<input class="form-control" type="number" min="0" max="30" id="numeroresultados_mensaje" placeholder="Cantidad de resultados" title="Cantidad de resultados por página" value="<?php if (isset($_COOKIE['numeroresultados_mensaje'])) echo $_COOKIE['numeroresultados_mensaje']; else echo "5" ?>" onkeyup="this.change();" mousewheel="this.change();" onchange="grabarcookie('numeroresultados_mensaje',this.value);buscar_mensaje(this.value);" size="4" style="width: 60px;"></p>
      </div>
    </buscar>
     <script>
             $('#numeroresultados_mensaje').tooltip(); 
    </script>
    <?php } ?>
     <?php 

     if(isset($_SESSION['barra_busqueda']) and $_SESSION['barra_busqueda']== "red"){ ?>
    <buscar class="navbar-form navbar-right" role="search" method="post">
      <div class="form-group">
        <select  class="form-control" id="campo_red" name="campo_red">
          <option value="titulo_Red" >Nombre</option>
          <option value="nombre_materia" >Materia</option>
          <option value="palabras_clave" >Palabras clave</option>
          <option value="nivel_eductivo" >Nivel educativo</option>
          <option value="descripcion" >Descripción</option>
          <option value="nombre" >Responsable</option>
          <option value="scorm" >Scorm</option>
          <option value="formato" >Formato</option>
          <option value="adjunto" >Adjunto</option>
          <option value="dificultad" >Dificultad</option>
          <option value="cantidad_estrellas" >Cantidad estrellas</option>
          <option value="fecha" >Fecha</option>
          <option value="id_red" >Id</option>
          <option value="idioma_red" >Idioma red</option>
          <option value="autor" >Autor</option>
          <option value="tipo_interacción" >Tipo interacción</option>
          <option value="tipo_recurso_educativo" >Tipo recurso educativo</option>
          </select>
<input  onclick="this.value='';focus();" onfocus="buscar_red_ajax(this.value);" class="form-control" type="search" id="txt_buscar_red" onkeyup ="buscar_red_ajax(this.value);" placeholder="Buscar" autofocus >
  </div>
    </buscar>
    <?php } ?>
        <?php if(isset($_SESSION['barra_busqueda']) and $_SESSION['barra_busqueda']== "cuestionarios"){ ?>
    <buscar class="navbar-form navbar-right" role="search" method="post">
      <div class="form-group">
<input class="form-control" type="search" placeholder="Buscar...  Ejemplo: Taller" id="txt_buscar_cuestionario" onkeyup ="buscar_cuestionario_pag();"  style="margin: 15px;" value="">
<input class="form-control" type="number" min="0" max="16" id="numeroresultados_cuesionario" placeholder="Cantidad de resultados" title="Cantidad de resultados" value="<?php if (isset($_COOKIE['numeroresultados_cuesionario'])) echo $_COOKIE['numeroresultados_cuesionario']; else echo "8" ?>" onkeyup=f"grabarcookie('numeroresultados_cuesionario',this.value);buscar_cuestionario_pag();" mousewheel="grabarcookie('numeroresultados_cuesionario',this.value);buscar_cuestionario_pag();" onchange="grabarcookie('numeroresultados_cuesionario',this.value);buscar_cuestionario_pag();" size="4" style="width: 60px;">
      </div>
    </buscar>
    <script>
             $('#numeroresultados_cuesionario').tooltip(); 
    </script>
    <?php } ?>
</nav>
<!-- http://librosweb.es/libro/bootstrap_3/capitulo_6/barras_de_navegacion.html -->
<?php if(isset($_SESSION['id_usuario'])){ ?>
<div    <?php if(isset($_SESSION['hijo']) and $_SESSION['rol']=="acudiente"){ echo "style='text-align:center' " ; } ?> id="estilo_foto_usuario_menu" class="estilos_fotos">
   <?php if(isset($_SESSION['hijo']) and $_SESSION['rol']=="acudiente"){  
    $sql = 'SELECT * FROM `usuario` WHERE `id_usuario` = "'.$_SESSION["hijo"].'"';
    $consulta = $mysqli->query($sql);
    if ($row=$consulta->fetch_assoc()){
    $row['foto'];
    }


   ?>
      <img  onclick="document.location.href='<?php echo SGA_USUARIO_URL ?>/perfil.php'" title="<?php echo $persona->nombre." ".$persona->apellido; ?>" id="foto_usuario" src="<?php echo READFILE_URL."/foto/".($row['foto']);
         ?>" width="50%">
<?php   } ?>
    
        <img  onclick="document.location.href='<?php echo SGA_USUARIO_URL ?>/perfil.php'" title="<?php echo$persona->nombre." ".$persona->apellido; ?>" id="foto_usuario" src="<?php echo READFILE_URL."/foto/".($persona->foto);
         ?>" width="<?php if(isset($_SESSION['hijo']) and $_SESSION['rol']=="acudiente"){
         echo '50%'; } else { echo '100%'; } ?>">
     <span <?php if(isset($persona->rol) and $persona->rol=="acudiente"){ echo 'style="margin-left:-65px!important;"'; } ?> <?php if(count(explode(",",$persona->rol))>1){ ?> data-toggle="modal" data-target="#myModal_roles" <?php } ?> id="area_rol"><?php echo $_SESSION['rol']; ?></span>
        </div>
      <script>
               $('#estilo_foto_usuario_menu').tooltip(); 
      </script>


<div id="myModal_roles" class="modal fade" role="dialog">
  <div class="modal-dialog">

    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title"><h2><label for="roles">Roles:</label></h2></h4>
      </div>
      <div style="height:30%!important;"  class="modal-body modal-lg">
      
<?php $datosrol = array("admin" => "Administrador", "docente" => "Docente", "estudiante" => "Estudiante", "acudiente" => "Acudiente"); ?>        
          <?php
              $roles = explode(",",$persona->rol);         
              foreach($roles as $rol){ ?>
              <span>               
              <?php if ($rol == $_SESSION['rol']){
              ?>

<img title="Mi Rol actual: <?php echo $datosrol[$rol];?>" name="<?php echo $datosrol[$rol];?>" width="130px;" src="<?php echo SGA_COMUN_IMAGES.'/png/'.$datosrol[$rol].'.jpg'; ?>"></img>
     <span class="roles"><?php echo $datosrol[$rol] ?> </span>
                     <?php
              }else{

              ?>
        
       <a title="Cambiar Rol a <?php echo $datosrol[$rol];?>" href="<?php echo SGA_USUARIO_URL ?>/perfil.php?redirect=<?php echo $_SERVER['PHP_SELF']?>&cambiar_rol=<?php echo $rol;?>"> 
<img name="<?php echo $datosrol[$rol];?>" width="120px;" src="<?php echo SGA_COMUN_IMAGES.'/png/'.$datosrol[$rol].'.jpg'; ?>"/>
        </a>
              <span class="roles"><?php echo $datosrol[$rol] ?> </span>
              <?php
              }
              ?></span>
              <?php } ?>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
      </div>
    </div>

  </div>
</div>


<?php } ?>
<?php #require("plantillas/header.php")?>
<?php #require(dirname(__FILE__)."/capturar.php"); ?>