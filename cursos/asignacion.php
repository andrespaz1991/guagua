<?php 
ob_start();
echo '<center>';
require("../comun/conexion.php");
#require("funciones.php");  
function buscar_Asignacion( $datos='', $reporte=''){
require_once ("../comun/lib/Zebra_Pagination/Zebra_Pagination.php");
$resultados = (isset($_COOKIE['numeroresultadosAsignacion']) ? $_COOKIE['numeroresultadosAsignacion'] : 10);
$paginacion = new Zebra_Pagination();
$paginacion->records_per_page($resultados);
$paginacion->records_per_page($resultados);
$cookiepage="page_numeroresultadosAsignacion";
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
        header("Content-Disposition: attachment; Filename=Asignacion.xls");
    }
    
    #header("Location:Asignacion.php");
    }require("../comun/conexion.php");
$sql='select * from   Asignacion ';
$consulta = $mysqli->query($sql);
$paginacion->records($consulta->num_rows);

$datosrecibidos = $datos;
$datos = explode(" ",$datosrecibidos);
$datos[]='';
$cont =  0;
if (count($datos)>1 and count($datos)<>$cont){
$sql .= ' WHERE '; 
}
if(!empty($_GET['xls'])){
    $sql.= "  Asignacion.id_asignacion= '".$_GET['xls']."'";
}else{
    foreach ($datos as $id => $dato){
        if(!empty($dato)){
            $sql .= 'concat(LOWER(Asignacion.id_asignacion),"", LOWER(Asignacion.institucion_educativa),"", LOWER(Asignacion.id_curso),"", LOWER(Asignacion.id_asignatura),"", LOWER(Asignacion.id_docente),"", LOWER(Asignacion.ano_lectivo),"", LOWER(Asignacion.descripcion),"", LOWER(Asignacion.id_categoria_curso),"", LOWER(Asignacion.visible),"", LOWER(Asignacion.portada_asignacion),"", LOWER(Asignacion.icono_asignacion),"", LOWER(Asignacion.asistencia),"", concat(LOWER(Asignacion.id_asignacion),"", LOWER(Asignacion.institucion_educativa),"", LOWER(Asignacion.id_curso),"", LOWER(Asignacion.id_asignatura),"", LOWER(Asignacion.id_docente),"", LOWER(Asignacion.ano_lectivo),"", LOWER(Asignacion.descripcion),"", LOWER(Asignacion.id_categoria_curso),"", LOWER(Asignacion.visible),"", LOWER(Asignacion.portada_asignacion),"", LOWER(Asignacion.icono_asignacion),"", LOWER(Asignacion.asistencia),"")) LIKE "%'.mb_strtolower($dato, 'UTF-8').'%"' ;
       
        }
        $cont ++;
        if (count($datos)>1 and count($datos)<>$cont){
            $sql .= ' and ';
        }
        }
        $sql .=  ' ORDER BY Asignacion.id_asignacion desc  ';
        if (!isset($_GET['xls'])){
            $sql.=  "  LIMIT " . (($paginacion->get_page() - 1) * $resultados) . ", " .$resultados;
            echo $sql;
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
  
<table class="table" border='1' id='tbAsignacion'>
<thead class="thead-dark">
<tr>
<th>Id Asignacion</th><th>Institucion Educativa</th><th>Id Curso</th><th>Id Asignatura</th><th>Id Docente</th><th>Ano Lectivo</th><th>Descripcion</th><th>Id Categoria Curso</th><th>Visible</th><th>Portada Asignacion</th><th>Icono Asignacion</th><th>Asistencia</th>
<?php if ($reporte==''){ ?>
    <th ><form id='formNuevo' name='formNuevo' method='post' action=Asignacion.php>
    <input name='cod' type='hidden' id='cod' value='0'>
    <input class="btn btn-light" type='submit' name='submit' id='submit' value='Nuevo'>
    </form>
    </th><th  ><form id="formNuevo" name="formNuevo" method="post" action=Asignacion.php?xls>
    <input name="cod" type="hidden" id="cod" value="0"><input class="btn btn-success" type="submit" name="submit" id="submit" value="XLS"><a target="_blank" href='reporte_Asignacion.php'><button type="button" class="btn btn-danger">PDF</button>
        </a></form>
    </th><?php } ?>
    </tr>
    </thead><tbody>
    <?php 
    while($row=$consulta->fetch_assoc()){
        ?>
       <tr>
       <td><?php echo $row['id_asignacion']?></td><td><?php echo $row['institucion_educativa']?></td><td><?php echo $row['id_curso']?></td><td><?php echo $row['id_asignatura']?></td><td><?php echo $row['id_docente']?></td><td><?php echo $row['ano_lectivo']?></td><td><?php echo $row['descripcion']?></td><td><?php echo $row['id_categoria_curso']?></td><td><?php echo $row['visible']?></td><td><?php echo $row['portada_asignacion']?></td><td><?php echo $row['icono_asignacion']?></td><td><?php echo $row['asistencia']?></td> 
       <?php if ($reporte==''){ ?>
       <td>
       <form id='formModificar' name='formModificar' method='post' action=''Asignacion.php'>
       <input name='cod' type='hidden' id='cod' value=' <?php echo $row['id_asignacion']?>'>
       <input class="btn btn-outline-primary" type='submit' name='submit' id='submit' value='Modificar'>
       <button type="button" class="btn btn-outline-danger" onClick="confirmeliminar('Asignacion.php',{'del':'<?php echo $row['id_asignacion'];?>'},'<?php echo $row['id_asignacion'];?>');">Eliminar</button>
       </form>     
       </td><td>
       <a target="_blank" href='Asignacion.php?xls=<?php echo $row['id_asignacion']?>'><button type="button" class="btn btn-success">XLS</button>
       </a><a target="_blank" href="reporte_Asignacion.php?id=<?php echo $row['id_asignacion']?>"> <button type="button" class="btn btn-danger">PDF</button></a></td><?php } ?>
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
        buscar_Asignacion($_POST['datos']);
    exit();
    }
    if (isset($_GET['xls'])){
     buscar_Asignacion('','xls');
    exit();
    }

if (isset($_POST['del'])){
 /*Instrucción SQL que permite eliminar en la BD*/ 
 $sql = 'DELETE FROM Asignacion WHERE id_asignacion="'.$_POST['del'].'"';
 /*Se conecta a la BD y luego ejecuta la instrucción SQL*/
if ($eliminar = $mysqli->query($sql)){
 /*Validamos si el registro fue eliminado con Éxito*/  echo '<div class="alert alert-success alert-dismissible">
                  <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                  <h5><i class="icon fas fa-check"></i> Correcto!</h5>
                  Registro Eliminado
                  </div>' ;
?> <meta http-equiv="refresh" content="; url="Asignacion.php" />
<?php
}else{ 
 echo  '<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
              <h5><i class="icon fas fa-ban"></i> Alerta</h5>
              Eliminación Fallida
            </div>';
?> 
<meta http-equiv="refresh" content="; url='Asignacion.php" />
<?php 
}
}
 ?>

 <center>
 <h1>Asignacion</h1>
 </center><?php 
 if (isset($_POST['submit'])){
 if ($_POST['submit']=="Registrar"){
  /*recibo los campos del formulario proveniente con el método POST*/ 
  $sql = "INSERT INTO Asignacion(institucion_educativa,id_curso,id_asignatura,id_docente,ano_lectivo,descripcion,id_categoria_curso,visible,portada_asignacion,icono_asignacion,asistencia) Values ('".$_POST["institucion_educativa"]."','".$_POST["id_curso"]."','".$_POST["id_asignatura"]."','".$_POST["id_docente"]."','".$_POST["ano_lectivo"]."','".$_POST["descripcion"]."','".$_POST["id_categoria_curso"]."','".$_POST["visible"]."','".$_POST["portada_asignacion"]."','".$_POST["icono_asignacion"]."','".$_POST["asistencia"]."')";

  /*echo $sql;*/
  if ($insertar = $mysqli->query($sql)) {
   /*Validamos si el registro fue ingresado con Éxito*/ 
    echo '<div class="alert alert-success alert-dismissible">
                  <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                  <h5><i class="icon fas fa-check"></i> Correcto!</h5>
                  Registro Exitoso
                  </div>' 
   ; echo '<meta http-equiv="refresh" content="; url=Asignacion.php" />';
   }else{ 
     echo  '<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
              <h5><i class="icon fas fa-ban"></i> Alerta</h5>
              Registro fallido
            </div>'
    ; echo '<meta http-equiv="refresh" content="; url=Asignacion.php" />';
  }
  } /*fin Registrar*/ 

  if ($_POST['submit']=="Nuevo" or $_POST['submit']=="Modificar"){
    if ($_POST['submit']=="Modificar"){
     $sql = 'SELECT * FROM Asignacion WHERE id_asignacion ="'.$_POST['cod'].'" Limit 1'; 
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
     echo '<form id="form1" name="form1" method="post" action="Asignacion.php">
     <h1>'.$textoh1.'</h1>';
     
     echo '<form id="form1" name="form1" method="post" action="Asignacion.php">';
echo '<p><input name="cod" type="hidden" id="cod" value="<?php echo $textobtn ?>" size="120" required></p>';
 echo "<input class='form-control' type='hidden' id='id_asignacion' name='id_asignacion' value='";if (isset($row["id_asignacion"])){
    echo $row["id_asignacion"];
} echo "'  ' > <br><label >Institucion Educativa</label><br>
            <div class='col-'>
            <input  class='form-control' type='text' id='institucion_educativa' name='institucion_educativa' value='";if (isset($row["institucion_educativa"])){
    echo $row["institucion_educativa"];
} echo "'  ' >
            </div>
            <br><label >Id Curso</label><br>
            <div class='col-'>
            <input  class='form-control' type='text' id='id_curso' name='id_curso' value='";if (isset($row["id_curso"])){
    echo $row["id_curso"];
} echo "'  ' >
            </div>
            <br><label >Id Asignatura</label><br>
            <div class='col-'>
            <input  class='form-control' type='text' id='id_asignatura' name='id_asignatura' value='";if (isset($row["id_asignatura"])){
    echo $row["id_asignatura"];
} echo "'  ' >
            </div>
            <br><label >Id Docente</label><br>
            <div class='col-'>
            <input  class='form-control' type='text' id='id_docente' name='id_docente' value='";if (isset($row["id_docente"])){
    echo $row["id_docente"];
} echo "'  ' >
            </div>
            <br><label >Ano Lectivo</label><br>
            <div class='col-'>
            <input  class='form-control' type='text' id='ano_lectivo' name='ano_lectivo' value='";if (isset($row["ano_lectivo"])){
    echo $row["ano_lectivo"];
} echo "'  ' >
            </div>
            <br><label >Descripcion</label><br>
            <div class='col-'>
            <input  class='form-control' type='text' id='descripcion' name='descripcion' value='";if (isset($row["descripcion"])){
    echo $row["descripcion"];
} echo "'  ' >
            </div>
            <br><label >Id Categoria Curso</label><br>
            <div class='col-'>
            <input  class='form-control' type='text' id='id_categoria_curso' name='id_categoria_curso' value='";if (isset($row["id_categoria_curso"])){
    echo $row["id_categoria_curso"];
} echo "'  ' >
            </div>
            <br><label >Visible</label><br>
            <div class='col-'>
            <input  class='form-control' type='text' id='visible' name='visible' value='";if (isset($row["visible"])){
    echo $row["visible"];
} echo "'  ' >
            </div>
            <br><label >Portada Asignacion</label><br>
            <div class='col-'>
            <input  class='form-control' type='text' id='portada_asignacion' name='portada_asignacion' value='";if (isset($row["portada_asignacion"])){
    echo $row["portada_asignacion"];
} echo "'  ' >
            </div>
            <br><label >Icono Asignacion</label><br>
            <div class='col-'>
            <input  class='form-control' type='text' id='icono_asignacion' name='icono_asignacion' value='";if (isset($row["icono_asignacion"])){
    echo $row["icono_asignacion"];
} echo "'  ' >
            </div>
            <br><label >Asistencia</label><br>
            <div class='col-'>
            <input  class='form-control' type='text' id='asistencia' name='asistencia' value='";if (isset($row["asistencia"])){
    echo $row["asistencia"];
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
    $cod = $_POST['id_asignacion'];
    /*Instrucción SQL que permite insertar en la BD */ 
    $sql = "UPDATE Asignacion SET institucion_educativa='".$_POST["institucion_educativa"]."',id_curso='".$_POST["id_curso"]."',id_asignatura='".$_POST["id_asignatura"]."',id_docente='".$_POST["id_docente"]."',ano_lectivo='".$_POST["ano_lectivo"]."',descripcion='".$_POST["descripcion"]."',id_categoria_curso='".$_POST["id_categoria_curso"]."',visible='".$_POST["visible"]."',portada_asignacion='".$_POST["portada_asignacion"]."',icono_asignacion='".$_POST["icono_asignacion"]."',asistencia='".$_POST["asistencia"]."' WHERE  id_asignacion  = ".$cod." ;" ;
 
 /* echo $sql;*/ 
 /*Se conecta a la BD y luego ejecuta la instrucción SQL*/ 
if ($actualizar = $mysqli->query($sql)) {
 /*Validamos si el registro fue ingresado con Éxito*/
  echo '<div class="alert alert-success alert-dismissible">
                  <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                  <h5><i class="icon fas fa-check"></i> Correcto!</h5>
                  Modificación Exitosa
                  </div>'  ; echo '<meta http-equiv="refresh" content=""; url="Asignacion.php" />';
 }else{ 
     echo  '<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
              <h5><i class="icon fas fa-ban"></i> Alerta</h5>
              Modificación Fallida
            </div>'
; } 
echo '<meta http-equiv="refresh" content=""; url="Asignacion.php" />';
} /*fin Actualizar*/ 
 }else{ 
 ?>
 <center>
<b><label>Buscar: </label></b><input placeholder="Buscar.." type="search" id="buscar" onkeyup ="buscar(this.value);" onchange="buscar(this.value);"  style="margin: 15px;">
<b><label>N° de Resultados:</label></b>
<input type="number" min="0" id="numeroresultadosAsignacion" placeholder="Cantidad de resultados" title="Cantidad de resultados" value="10" onkeyup="grabarcookie('numeroresultadosAsignacion',this.value) ;buscar(document.getElementById('buscar').value);" mousewheel="grabarcookie('numeroresultadosAsignacion',this.value);buscar(document.getElementById('buscar').value);" onchange="grabarcookie('numeroresultadosAsignacion',this.value);buscar(document.getElementById('buscar').value);" size="4" style="width: 40px;">
</center>

<span id="txtsugerencias">
<?php 
buscar_Asignacion();
 ?>
</span>
<?php 
}/*fin else if isset cod*/
echo '</center>';
 ?>
<script>
document.getElementById("menu_Asignacion").className ='active '+document.getElementById("menu_Asignacion").className;
</script>
<?php $contenido = ob_get_contents();
ob_clean();
include ("../comun/plantilla.php");
 ?>