
<?php 
ob_start();
echo '<center>';
require_once("conexion.php");
require_once("../clases/Fecha.Class.php");
#require("funciones.php");  
?>

<?php
function buscar_planeador_vallesol( $datos='', $reporte=''){
?>

<?php
  require_once ("../comun/lib/Zebra_Pagination/Zebra_Pagination.php");
$resultados = (isset($_COOKIE['numeroresultadosplaneador_vallesol']) ? $_COOKIE['numeroresultadosplaneador_vallesol'] : 10);
$paginacion = new Zebra_Pagination();
$paginacion->records_per_page($resultados);
$paginacion->records_per_page($resultados);
$cookiepage="page_numeroresultadosplaneador_vallesol";
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
        header("Content-Disposition: attachment; Filename=planeador_vallesol.xls");
    }
    
    #header("Location:planeador_vallesol.php");
    }require("conexion.php");
$sql='select * from planeador_vallesol
inner join asignacion on planeador_vallesol.materia= asignacion.id_asignacion
inner join materia_oficial on materia_oficial.id_materia=asignacion.id_asignatura';
$consulta = $mysqli->query($sql);
$paginacion->records($consulta->num_rows);

$datosrecibidos = $datos;
$datos = explode(" ",$datosrecibidos);
$datos[]='';
$cont =  0;
$sql .= ' WHERE ';
if(!empty($_GET['xls'])){
    $sql.= "  planeador_vallesol.id_plan= '".$_GET['xls']."'";
}else{
    foreach ($datos as $id => $dato){
        $sql .= 'concat(LOWER(planeador_vallesol.id_plan),"", LOWER(planeador_vallesol.fecha_creacion),"", LOWER(planeador_vallesol.fecha_inicio),"", LOWER(planeador_vallesol.fecha_fin),"", LOWER(planeador_vallesol.grado),"", LOWER(planeador_vallesol.grado),"", LOWER(planeador_vallesol.periodo),"", LOWER(planeador_vallesol.tiempo_plan),"", LOWER(materia_oficial.nombre_materia),"", LOWER(planeador_vallesol.estrategias),"", LOWER(planeador_vallesol.evidencias),"", LOWER(planeador_vallesol.observaciones),"", LOWER(planeador_vallesol.recursos),"", LOWER(planeador_vallesol.reflexion),"", LOWER(planeador_vallesol.objetivo),"", concat(LOWER(planeador_vallesol.id_plan),"", LOWER(planeador_vallesol.fecha_creacion),"", LOWER(planeador_vallesol.fecha_inicio),"", LOWER(planeador_vallesol.fecha_fin),"", LOWER(planeador_vallesol.grado),"", LOWER(planeador_vallesol.materia),"", LOWER(planeador_vallesol.periodo),"", LOWER(planeador_vallesol.tiempo_plan),"", LOWER(planeador_vallesol.dba),"", LOWER(planeador_vallesol.estrategias),"", LOWER(planeador_vallesol.evidencias),"", LOWER(planeador_vallesol.observaciones),"", LOWER(planeador_vallesol.recursos),"", LOWER(planeador_vallesol.reflexion),"", LOWER(planeador_vallesol.objetivo),"")) LIKE "%'.mb_strtolower($dato, 'UTF-8').'%"' ;
        $cont ++;
        if (count($datos)>1 and count($datos)<>$cont){
            $sql .= ' and ';
        }
        }
        $sql .=  ' ORDER BY planeador_vallesol.id_plan desc  ';
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
  
<table class="table table-striped" border='1' id='tbplaneador_vallesol'>
<thead class="thead-dark">
<tr>
<th>Id Plan</th><th>Fecha Creacion</th><th>Fecha Inicio</th><th>Fecha Fin</th><th>Grado</th><th>Materia</th><th>Periodo</th><th>Tiempo Plan</th><th>Dba</th><th>Estrategias</th><th>Evidencias</th><th>Observaciones</th><th>Recursos</th><th>Reflexion</th><th>Objetivo</th>
<?php if ($reporte==''){ ?>
    <th ><form id='formNuevo' name='formNuevo' method='post' action=planeador_vallesol.php>
    <input name='cod' type='hidden' id='cod' value='0'>
    <input class="btn btn-light" type='submit' name='submit' id='submit' value='Nuevo'>
    </form>
    </th><th  ><form id="formNuevo" name="formNuevo" method="post" action=planeador_vallesol.php?xls>
    <input name="cod" type="hidden" id="cod" value="0"><input class="btn btn-success" type="submit" name="submit" id="submit" value="XLS"><a target="_blank" href='reporte_planeador_vallesol.php'><button type="button" class="btn btn-danger">PDF</button>
        </a></form>
    </th><?php } ?>
    </tr>
    </thead><tbody>
    <?php 
    while($row=$consulta->fetch_assoc()){
        ?>
       <tr>
       <td><?php echo $row['id_plan']?></td><td><?php 
       echo Fecha::formato_Fecha($row['fecha_creacion'])?></td><td><?php echo 
       Fecha::formato_Fecha($row['fecha_inicio'])?></td><td><?php echo  Fecha::formato_Fecha($row['fecha_fin']) ?></td><td><?php echo $row['grado']?></td><td><?php echo $row['nombre_materia']?></td><td><?php echo $row['periodo']?></td><td><?php
       echo $row['tiempo_plan'];
       if($row['tiempo_plan']>1){
          echo ' horas';
       }else{
        echo ' hora';
       }
        ?></td><td>
       <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#dba<?php echo $row["id_plan"]; ?>">
  Dba
</button>

<!-- Modal -->
<div class="modal fade" id="dba<?php echo $row["id_plan"]; ?>" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel"><?php  echo $row['materia'] ?></h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
       <?php  if (isset($row["dba"])){
    echo $row["dba"];
} ?>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
      </div>
    </div>
  </div>
</div> 
       
        
       
       
       
       
       
       
       
       
       
       
       <?php #echo $row['dba']?></td><td>
        
       <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#es<?php echo $row["id_plan"]; ?>">
  Estrategias
</button>

<!-- Modal -->
<div class="modal fade" id="es<?php echo $row["id_plan"]; ?>" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel"><?php  echo $row['materia'] ?></h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
       <?php  if (isset($row["estrategias"])){
    echo $row["estrategias"];
} ?>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
      </div>
    </div>
  </div>
</div> 
       
       
       
       
       
       
       
       
       
       
       
       
       
       
       <?php #echo $row['estrategias']?></td><td>
       <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#ev<?php echo $row["id_plan"]; ?>">
  Evidencias
</button>

<!-- Modal -->
<div class="modal fade" id="ev<?php echo $row["id_plan"]; ?>" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel"><?php  echo $row['materia'] ?></h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
       <?php  if (isset($row["evidencias"])){
    echo $row["evidencias"];
} ?>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
      </div>
    </div>
  </div>
</div> 
       
       
       
       
       
       
       
       
       
       
       
       
       <?php #echo $row['evidencias']?></td><td>       <!-- Button trigger modal -->
<button type="button" class="btn btn-primary" data-toggle="modal" data-target="#obs<?php echo $row["id_plan"]; ?>">
  Observación
</button>

<!-- Modal -->
<div class="modal fade" id="obs<?php echo $row["id_plan"]; ?>" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel"><?php  echo $row['materia'] ?></h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
       <?php  if (isset($row["observaciones"])){
    echo $row["observaciones"];
} ?>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
      </div>
    </div>
  </div>
</div>
            
            <?php
             
       
       
       #echo $row['observaciones']?></td><td><?php echo $row['recursos']?></td><td>
        
        
        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#ref<?php echo $row["id_plan"]; ?>">
  Reflexion
</button>

<!-- Modal -->
<div class="modal fade" id="ref<?php echo $row["id_plan"]; ?>" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel"><?php  echo $row['materia'] ?></h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
       <?php  if (isset($row["reflexion"])){
    echo $row["reflexion"];
} ?>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
      </div>
    </div>
  </div>
</div> 
        
        
        
        <?php #echo $row['reflexion']?></td><td><?php echo $row['objetivo']?></td> 
       <?php if ($reporte==''){ ?>
       <td>
       <form id='formModificar' name='formModificar' method='post' action='index.php?idplan=<?php echo $row['id_plan'];?>'>
       <input name='cod' type='hidden' id='cod' value=' <?php echo $row['id_plan']?>'>
       <input class="btn btn-outline-primary" type='submit' name='submit' id='submit' value='Modificar'>
       <button type="button" class="btn btn-outline-danger" onClick="confirmeliminar('planeador_vallesol.php',{'del':'<?php echo $row['id_plan'];?>'},'<?php echo $row['id_plan'];?>');">Eliminar</button>
       </form>     
       </td><td>
       <a target="_blank" href='planeador.php?xls=<?php echo $row['id_plan']?>'><button type="button" class="btn btn-success">XLS</button>
       </a><a target="_blank" href="planeador.php?pdf=1&idplan=<?php echo $row['id_plan']?>"> <button type="button" class="btn btn-danger">PDF</button></a></td><?php } ?>
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
        buscar_planeador_vallesol($_POST['datos']);
    exit();
    }
    if (isset($_GET['xls'])){
     buscar_planeador_vallesol('','xls');
    exit();
    }

if (isset($_POST['del'])){
 /*Instrucción SQL que permite eliminar en la BD*/ 
 $sql = 'DELETE FROM planeador_vallesol WHERE id_plan="'.$_POST['del'].'"';
 /*Se conecta a la BD y luego ejecuta la instrucción SQL*/
if ($eliminar = $mysqli->query($sql)){
 /*Validamos si el registro fue eliminado con Éxito*/  echo '<div class="alert alert-success alert-dismissible">
                  <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                  <h5><i class="icon fas fa-check"></i> Correcto!</h5>
                  Registro Eliminado
                  </div>' ;
?> <meta http-equiv="refresh" content="; url="planeador_vallesol.php" />
<?php
}else{ 
 echo  '<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
              <h5><i class="icon fas fa-ban"></i> Alerta</h5>
              Eliminación Fallida
            </div>';
?> 
<meta http-equiv="refresh" content="; url='planeador_vallesol.php" />
<?php 
}
}
 ?>
 <div class="col-md-3" style="margin-top:4%;margin-bottom:4%">
     <?php require_once 'template/menu.php'; 
     ?>
     </div>
<br><br><br>
 <center>
 <h1>Planeador_vallesol</h1>
 </center><?php 
 if (isset($_POST['submit'])){
 if ($_POST['submit']=="Registrar"){
  /*recibo los campos del formulario proveniente con el método POST*/ 
  $sql = "INSERT INTO planeador_vallesol(fecha_creacion,fecha_inicio,fecha_fin,grado,materia,periodo,tiempo_plan,dba,estrategias,evidencias,observaciones,recursos,reflexion,objetivo) Values ('".$_POST["fecha_creacion"]."','".$_POST["fecha_inicio"]."','".$_POST["fecha_fin"]."','".$_POST["grado"]."','".$_POST["materia"]."','".$_POST["periodo"]."','".$_POST["tiempo_plan"]."','".$_POST["dba"]."','".$_POST["estrategias"]."','".$_POST["evidencias"]."','".($_POST["observaciones"])."','".$_POST["recursos"]."','".$_POST["reflexion"]."','".$_POST["objetivo"]."')";


  if ($insertar = $mysqli->query($sql)) {
   /*Validamos si el registro fue ingresado con Éxito*/ 
    echo '<div class="alert alert-success alert-dismissible">
                  <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                  <h5><i class="icon fas fa-check"></i> Correcto!</h5>
                  Registro Exitoso
                  </div>' 
   ; echo '<meta http-equiv="refresh" content="; url=planeador_vallesol.php" />';
   }else{ 
     echo  '<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
              <h5><i class="icon fas fa-ban"></i> Alerta</h5>
              Registro fallido
            </div>'
    ; echo '<meta http-equiv="refresh" content="; url=planeador_vallesol.php" />';
  }
  } /*fin Registrar*/ 

  if ($_POST['submit']=="Nuevo" or $_POST['submit']=="Modificar"){
    if ($_POST['submit']=="Modificar"){
     $sql = 'SELECT * FROM planeador_vallesol WHERE id_plan ="'.$_POST['cod'].'" Limit 1'; 
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
     echo '<form id="form1" name="form1" method="post" action="planeador_vallesol.php">
     <h1>'.$textoh1.'</h1>';
     
     echo '<form id="form1" name="form1" method="post" action="planeador_vallesol.php">';
echo '<p><input name="cod" type="hidden" id="cod" value="<?php echo $textobtn ?>" size="120" required></p>';
 echo "<input class='form-control' type='hidden' id='id_plan' name='id_plan' value='";if (isset($row["id_plan"])){
    echo $row["id_plan"];
} echo "'  ' > <br><label >Fecha Creacion</label><br>
            <div class='col-'>
            <input  class='form-control' type='date' id='fecha_creacion' name='fecha_creacion' value='";if (isset($row["fecha_creacion"])){
    echo $row["fecha_creacion"];
} echo "'  ' >
            </div>
            <br><label >Fecha Inicio</label><br>
            <div class='col-'>
            <input  class='form-control' type='date' id='fecha_inicio' name='fecha_inicio' value='";if (isset($row["fecha_inicio"])){
    echo $row["fecha_inicio"];
} echo "'  ' >
            </div>
            <br><label >Fecha Fin</label><br>
            <div class='col-'>
            <input  class='form-control' type='date' id='fecha_fin' name='fecha_fin' value='";if (isset($row["fecha_fin"])){
    echo $row["fecha_fin"];
} echo "'  ' >
            </div>
            <br><label >Grado</label><br>
            <div class='col-'>
            <input  class='form-control' type='number' id='grado' name='grado' value='";if (isset($row["grado"])){
    echo $row["grado"];
} echo "'  ' >
            </div>
            <br><label >Materia</label><br>
            <div class='col-'>
            <input  class='form-control' type='number' id='materia' name='materia' value='";if (isset($row["materia"])){
    echo $row["materia"];
} echo "'  ' >
            </div>
            <br><label >Periodo</label><br>
            <div class='col-'>
            <input  class='form-control' type='number' id='periodo' name='periodo' value='";if (isset($row["periodo"])){
    echo $row["periodo"];
} echo "'  ' >
            </div>
            <br><label >Tiempo Plan</label><br>
            <div class='col-'>
            <input  class='form-control' type='number' id='tiempo_plan' name='tiempo_plan' value='";if (isset($row["tiempo_plan"])){
    echo $row["tiempo_plan"];
} echo "'  ' >
            </div>
            <br><label >Dba</label><br>
            <div class='col-'>
            <input  class='form-control' type='text' id='dba' name='dba' value='";if (isset($row["dba"])){
    echo $row["dba"];
} echo "'  ' >
            </div>
            <br><label >Estrategias</label><br>
            <div class='col-'>
            <input  class='form-control' type='text' id='estrategias' name='estrategias' value='";if (isset($row["estrategias"])){
    echo $row["estrategias"];
} echo "'  ' >
            </div>
            <br><label >Evidencias</label><br>
            <div class='col-'>
            <input  class='form-control' type='text' id='evidencias' name='evidencias' value='";if (isset($row["evidencias"])){
    echo $row["evidencias"];
} echo "'  ' >
            </div>
            <br><label >Observaciones</label><br>
            <div class='col-'>
            <input  class='form-control' type='text' id='observaciones' name='observaciones' value='";if (isset($row["observaciones"])){
    echo $row["observaciones"];
} echo "'  ' >
            </div>
            <br><label >Recursos</label><br>
            <div class='col-'>
            <input  class='form-control' type='text' id='recursos' name='recursos' value='";if (isset($row["recursos"])){
    echo $row["recursos"];
} echo "'  ' >
            </div>
            <br><label >Reflexion</label><br>
            <div class='col-'>
            <input  class='form-control' type='text' id='reflexion' name='reflexion' value='";if (isset($row["reflexion"])){
    echo $row["reflexion"];
} echo "'  ' >
            </div>
            <br><label >Objetivo</label><br>
            <div class='col-'>
            <input  class='form-control' type='text' id='objetivo' name='objetivo' value='";if (isset($row["objetivo"])){
    echo $row["objetivo"];
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
    $cod = $_POST['id_plan'];
    /*Instrucción SQL que permite insertar en la BD */ 
    $sql = "UPDATE planeador_vallesol SET fecha_creacion='".$_POST["fecha_creacion"]."',fecha_inicio='".$_POST["fecha_inicio"]."',fecha_fin='".$_POST["fecha_fin"]."',grado='".$_POST["grado"]."',materia='".$_POST["materia"]."',periodo='".$_POST["periodo"]."',tiempo_plan='".$_POST["tiempo_plan"]."',dba='".$_POST["dba"]."',estrategias='".$_POST["estrategias"]."',evidencias='".$_POST["evidencias"]."',observaciones='".$_POST["observaciones"]."',recursos='".$_POST["recursos"]."',reflexion='".$_POST["reflexion"]."',objetivo='".$_POST["objetivo"]."' WHERE  id_plan  = ".$cod." ;" ;
 
 /* echo $sql;*/ 
 /*Se conecta a la BD y luego ejecuta la instrucción SQL*/ 
if ($actualizar = $mysqli->query($sql)) {
 /*Validamos si el registro fue ingresado con Éxito*/
  echo '<div class="alert alert-success alert-dismissible">
                  <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                  <h5><i class="icon fas fa-check"></i> Correcto!</h5>
                  Modificación Exitosa
                  </div>'  ; echo '<meta http-equiv="refresh" content=""; url="planeador_vallesol.php" />';
 }else{ 
     echo  '<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
              <h5><i class="icon fas fa-ban"></i> Alerta</h5>
              Modificación Fallida
            </div>'
; } 
echo '<meta http-equiv="refresh" content=""; url="planeador_vallesol.php" />';
} /*fin Actualizar*/ 
 }else{ 
 ?>
 <center>
<b><label>Buscar: </label></b><input placeholder="Buscar.." type="search" id="buscar" onkeyup ="buscar(this.value);" onchange="buscar(this.value);"  style="margin: 15px;">
<b><label>N° de Resultados:</label></b>
<input type="number" min="0" id="numeroresultadosplaneador_vallesol" placeholder="Cantidad de resultados" title="Cantidad de resultados" value="10" onkeyup="grabarcookie('numeroresultadosplaneador_vallesol',this.value) ;buscar(document.getElementById('buscar').value);" mousewheel="grabarcookie('numeroresultadosplaneador_vallesol',this.value);buscar(document.getElementById('buscar').value);" onchange="grabarcookie('numeroresultadosplaneador_vallesol',this.value);buscar(document.getElementById('buscar').value);" size="4" style="width: 40px;">
</center>

<span id="txtsugerencias">
<?php 
buscar_planeador_vallesol();
 ?>
</span>
<?php 
}/*fin else if isset cod*/
echo '</center>';
 ?>
<script>
document.getElementById('menu_objetivos').className ='active '+document.getElementById('menu_objetivos').className;
</script>
<?php 
$contenido = ob_get_clean();
require ("../comun/plantilla.php");
 ?>
