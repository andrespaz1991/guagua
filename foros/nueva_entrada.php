<?php 
ob_start();
@session_start();
if(!isset($_SESSION['usuario'])){
header ("Location: ../usuario/login.php");
exit();
}
if(!isset($_POST['grupo']) or !isset($_POST['categoria'])){
	$contenido = ob_get_contents();
ob_clean();
include ("../comun/plantilla.php");
exit();	
}
require ("../comun/conexion.php");
require_once ("funciones.php");
nueva_entrada();
?>
	<h1>Nueva Entrada</h1>
	<main>
		<div class="row">
			<div class="col-md-12">
			     <form role="form"  action="nueva_entrada.php" method="post">
<input name="grupo" type="hidden" id="grupo" value="<?php echo $_POST['grupo']; ?>">
<input name="categoria" type="hidden" id="categoria" value="<?php echo $_POST['categoria']; ?>">
								<div class="form-group">
									<label for="titulo">Título</label>
									<input name="titulo" type="text" class="form-control" placeholder="Título" title="Título" value="" size="50" required>
								</div>
								<div class="form-group">
                                <label for="contenido">Contenido</label>
                                    <textarea name="contenido" type="text" class="form-control" placeholder="Contenido" title="Contenido" required></textarea>
								</div>
								<div class="form-group">
								    <select class="form-control" name="tema_foro" title="tema_foro" required>
                        					<option value="">
                        					Seleccione un Tema
                        					</option>
                        					<?php
                        					
                        					$sql = "SELECT `id`, `nombre` FROM `tema_foro`";
                        					$resultado=$mysqli->query($sql);
                        	                while ($row=$resultado->fetch_assoc()){
                        	                extract($row);
                        					?>
                        					<option value="<?php echo $nombre ?>"><?php echo $nombre ?></option>
                        					<?php 
                        	                }
                        	                ?>
                        			</select>
								</div>
                                <div class="form-group">
								    <button type="submit" name="Enviar" class="btn btn-primary">Publicar</button>
							    </div>
						</form>
			</div>
		</div>
	</main>
<?php $contenido = ob_get_contents();
ob_clean();
include ("../comun/plantilla.php");
?>
