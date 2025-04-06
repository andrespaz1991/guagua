<?php
ob_clean();
ob_start();
?>
<ul>
<li class="lista_en_linea"><a href="config.php"><span class="glyphicon glyphicon-user"></span> Configruación</a></li>
            <li class="lista_en_linea"><a href="categoria.php"><span class="glyphicon glyphicon-folder-open"></span> Categoria</a></li>
      <li class="lista_en_linea"><a href="pregunta.php"><span class="glyphicon glyphicon-folder-open"></span> Preguntas</a></li>
      <li class="lista_en_linea"><a href="escala.php"><span class="glyphicon glyphicon-folder-open"></span> Escala</a></li>

</ul>
<form action="resultados.php">
    <label for="id_docente" >Identificacion del Docente</label><input type="text" name="id_docente"/>
    <input type="submit" value="Consultar" placeholder="Documento del docente"/>
</form>
<?php
$contenido = ob_get_contents();
ob_clean();
include ("../../comun/plantilla.php");
?>
