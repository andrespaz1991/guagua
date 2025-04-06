<?php 
@session_start();
ob_start();
?>
<?php
@session_start();
$_SESSION['modulo']="foros";
$_SESSION['barra_busqueda'] = "foros";
require("../comun/conexion.php");
require_once("../comun/funciones.php");
require_once("funciones.php");
#require_once("../comun/config.php");
#print_r($_POST);
if ($_POST['submit']=="Comentar"){
comentar_enforo();
}/*fin Comentar*/ 
?>
<style>
	.comentario{
		margin-left:30px;
	}
</style>
	<h1>Publicaciones del Foro</h1>
<?php
if(isset($_GET['g'])){
$gru = $_GET['g'];//Institucional,Curso,Acudientes,Docentes y Directivos, Estudiantes
$cat = $_GET['c'];//categoria_curso,general=Null o ""
$cur = $_GET['a'];//asignacion
$act = $_GET['m'];//actividad o actividad
if (isset($_GET['g'])) echo consultar_nombre_grupo($_GET['g']);
if (isset($_GET['a'])) echo " - ".consultar_nombre_asignacion($_GET['a']);
if (isset($_GET['c'])) echo " - ".consultar_nombre_categoria($_GET['c']);
if (isset($_GET['m'])) echo " - ".consultar_nombre_actividad($_GET['m']);
}
?>
	<main>
		<div class="row">
			<div class="col-md-12">
<?php if(isset($_GET['g'])){ ?>
<div role="form">
<input name="grupo" type="hidden" id="grupo" value="<?php echo $gru; ?>">
<input name="categoria" type="hidden" id="categoria" value="<?php echo $cat; ?>">
<input name="asignacion" type="hidden" id="asignacion" value="<?php echo $cur; ?>">
<input name="actividad" type="hidden" id="actividad" value="<?php echo $act; ?>">
		<div class="form-group">
        <label for="contenido">Contenido</label>
            <textarea name="contenido" id="contenido" type="text" class="form-control" placeholder="Contenido" title="Contenido" required></textarea>
		</div>
        <div class="form-group">
		    <button onclick="nueva_entrada()" name="Enviar" class="btn btn-primary">Publicar</button>
	    </div>
</div>
<span style="display:none" id="txt_foros2"></span>
<span id="txt_foros">
				<?php
				if ($cat != "" and $cat != NULL and $cur != NULL and $act != NULL )
				echo foro($gru,$cat,$cur,$act);
				elseif ($cat != "" and $cat != NULL and $cur != NULL)
				echo foro($gru,$cat,$cur);
				elseif ($cat != "" and $cat != NULL)
				echo foro($gru,$cat);
				else
				echo foro($gru);
?></span><?php
				}else{
				gruposforos();
				}
				?>
			</div>
		</div>
	</main>
	<script>
	//	$(".entradas").toggle();
	</script>
<?php $contenido = ob_get_contents();
ob_clean();
require("../comun/plantilla.php");
 ?>
