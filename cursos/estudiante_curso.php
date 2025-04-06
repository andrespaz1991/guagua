<?php

ob_start();


session_start();

// Desactivar toda notificación de error
error_reporting(0);
error_reporting(E_ALL);

unset($_SESSION['barra_busqueda']);

require_once("../comun/config.php");
require_once("../comun/funciones.php");
require("../comun/conexion.php");

setcookie('miruta', $_SERVER["QUERY_STRING"]);

if (isset($_GET['asignacion'])) {
    setcookie('asignacion', $_GET['asignacion']);
}

?>

<script type="text/javascript" src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script type="text/javascript" src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"></script>

<script type="text/javascript">
    $(function() {
        $('#lista_docentes').mitoogle();
    });
</script>

<?php

if (isset($_GET['asignacion'])) {
    $_GET['asignacion'] = mysqli_real_escape_string($mysqli, $_GET['asignacion']);

    $SqlAsignacion = 'SELECT * FROM categoria_curso, asignacion, materia, usuario WHERE ' .
                     'asignacion.id_categoria_curso=categoria_curso.id_categoria_curso AND ' .
                     'asignacion.id_docente = usuario.id_usuario AND ' .
                     'asignacion.id_asignatura = materia.id_materia AND';

    if (!isset($_GET['categoria'])) {
        $SqlAsignacion .= ' asignacion.id_asignacion="'.$_GET['asignacion'].'"';
    } else {
        $SqlAsignacion .= ' categoria_curso.id_categoria_curso="'.$_GET['categoria'].'"';
    }

    $consultaAsignacion = $mysqli->query($SqlAsignacion);

    while ($RowAsignacion = $consultaAsignacion->fetch_assoc()) {
        $portada_asignacion = $RowAsignacion['portada_asignacion'];
        $nombre_cate = $RowAsignacion['nombre_categoria_curso'];
        $curso = $RowAsignacion['nombre_materia'];
        $cate = $RowAsignacion['id_categoria_curso'];

        setcookie('asignacion', $RowAsignacion['id_asignacion']);
        $_SESSION['docente'] = $RowAsignacion['foto'];
        $_SESSION['nombre_docente'] = $RowAsignacion['nombre'].' '.$RowAsignacion['apellido'];
    }
}

?>

<link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
<link rel="stylesheet" href="/resources/demos/style.css">

<script>
    $(function() {
        $("#draggable").draggable();
        $("#droppable").droppable({
            drop: function(event, ui) {
                var req = new XMLHttpRequest();
                req.open("GET", "inscribir_estudiante.php?estudiante=" + $("#draggable").attr("value") +
                          "&asignacion=<?php echo $_GET['asignacion']; ?>", false);
                req.send();
                alert($("#draggable").attr("value") + " Registro exitoso");
                buscar();
            }
        });
    });
</script>

<?php

function buscar_estudiante($datos = "", $asignacion = "", $cate = "") {
    require("../comun/conexion.php");
    require_once("../comun/funciones.php");
    require_once("../comun/lib/Zebra_Pagination/Zebra_Pagination.php");
####################
$sql_asistencia="SELECT usuario.id_usuario, usuario.nombre, usuario.apellido, SUM(CASE WHEN asistencia.asistencia = 'no' THEN 1 ELSE 0 END) AS inasistencias, SUM(CASE WHEN asistencia.asistencia = 'si' THEN 1 ELSE 0 END) AS asistencias, SUM(CASE WHEN asistencia.asistencia = 'permiso' THEN 1 ELSE 0 END) AS permisos FROM inscripcion INNER JOIN usuario ON usuario.id_usuario = inscripcion.id_estudiante INNER JOIN asistencia ON asistencia.id_estudiante = inscripcion.id_estudiante INNER JOIN asignacion ON asignacion.id_asignacion = inscripcion.id_asignacion WHERE inscripcion.id_asignacion = '".$_GET['asignacion']."' GROUP BY usuario.id_usuario, usuario.nombre, usuario.apellido ORDER BY usuario.apellido, usuario.nombre LIMIT 0, 4;";
$consultainasistencia = $mysqli->query($sql_asistencia);
$data=array();
while ($rowinasistencia = $consultainasistencia->fetch_assoc()) {
    $data[$rowinasistencia['id_usuario']]=$rowinasistencia;

}
#echo "<pre>";
#print_r($data);
#echo "</pre>";
###################
    $records_per_page =10;
    $pagination = new Zebra_Pagination();
    $pagination->records_per_page($records_per_page);
    $cookiepage = "page_motivo";
    $funcionjs = "buscar();";
    $pagination->fn_js_page("$funcionjs");
    $pagination->cookie_page($cookiepage);
    $pagination->padding(false);

    if (isset($_COOKIE["$cookiepage"])) $_GET['page'] = $_COOKIE["$cookiepage"];

    $sql = 'SELECT * FROM inscripcion, usuario ';
    $datosrecibidos = $datos;
    $datos = explode(" ", $datosrecibidos);
    $cont = 0;

    $sql .= ' WHERE inscripcion.id_asignacion= "'.$_GET['asignacion'].'" ';

    foreach ($datos as $id => $dato) {
        $sql .= ' AND usuario.id_usuario = inscripcion.id_estudiante AND ' .
                'CONCAT(usuario.id_usuario, " ", usuario.clave, " ", LOWER(usuario.nombre), " ", ' .
                'LOWER(usuario.apellido), " ", LOWER(usuario.direccion), " ", usuario.direccion, " ", ' .
                'LOWER(usuario.direccion), " ") LIKE LOWER("%'.utf8_decode($dato).'%" ) ';

        $consuta = $mysqli->query($sql);
        echo $pagination->records($consuta->num_rows);
        $cont++;

        if (count($datos) > 1 && count($datos) != $cont) {
            $sql .= "";
        }
    }

    if (isset ($_COOKIE['asignacion'])) {
        $miasignada = $_COOKIE['asignacion'];
    } else {
        $_COOKIE['asignacion'] = $_GET['asignacion'];
    }

    $sql .= '  ORDER BY usuario.apellido, usuario.nombre ASC  LIMIT ';

    if (isset($_COOKIE['numeroresultados']) && $_COOKIE['numeroresultados'] != "") {
        $sql .= $_COOKIE['numeroresultados'];
    }

    $sql .= " " . (($pagination->get_page() - 1) * $records_per_page) . ", " . $records_per_page;
   #echo $sql;
    $consulta = $mysqli->query($sql);
?>

<center><p><?php echo "Resultados de "; echo $consulta->num_rows; ?>   <?php echo  "del total de en página ".$pagination->get_page(); ?></p></center>

<div class="table-responsive">       

<script>
    $(function() {
        function createSomeMenu() {
            return {
                callback: function(key, options) {
                    if (key == "Nuevo") {
                        window.location = 'actividad.php?asignacion=<?php echo $_GET['asignacion']; ?>';
                    }
                    if (key == "Modificar") {
                        window.location = 'modificar_curso.php?asignacion=<?php echo $_GET['asignacion']; ?>';
                    }
                    if (key == "Estudiantes del curso") {
                        window.location = 'estudiante_curso.php?asignacion=<?php echo $_GET['asignacion']; ?>';
                    }
                    if (key == "Reporte Valorativo") {
                        window.location = 'ver_rep_valorativo.php?asignacion=<?php echo $_GET['asignacion']; ?>';
                    }
                    if (key == "Copiar") {
                        window.location = 'copiar_asignatura.php?asignacion=<?php echo $_GET['asignacion']; ?>';
                    }
                    if (key == "Eliminar") {
                        var con = confirm("Esta seguro que desea eliminar el registro?");
                        if (con == true) {
                            window.location = 'eliminar_asignatura.php?asignacion=<?php echo $_GET['asignacion']; ?>';
                        }
                    }
                    if (key == "Crear Nueva Categoria") {
                        window.location = 'agregar_nueva_categoria_curso.php?asignacion=<?php echo $_GET['asignacion']; ?>';
                    }
                    if (key == "Subir Archivo") {
                        window.location = 'subir_archivo.php?asignacion=<?php echo $_GET['asignacion']; ?>';
                    }
                },
                items: {
                    "Nuevo": {
                        name: "Nuevo",
                        icon: "edit"
                    },
                    "Modificar": {
                        name: "Modificar",
                        icon: "copy"
                    },
                    "Estudiantes del curso": {
                        name: "Estudiantes del curso",
                        icon: "paste"
                    },
                    "Reporte Valorativo": {
                        name: "Reporte Valorativo",
                        icon: "cut"
                    },
                    "Copiar": {
                        name: "Copiar",
                        icon: "copy"
                    },
                    "Eliminar": {
                        name: "Eliminar",
                        icon: "paste"
                    },
                    "Crear Nueva Categoria": {
                        name: "Crear Nueva Categoria",
                        icon: "copy"
                    },
                    "Subir Archivo": {
                        name: "Subir Archivo",
                        icon: "paste"
                    }
                }
            };
        }

        $(".context-menu-one").on("click", function(e) {
            console.log("clicked", this);
        }).contextMenu({
            selector: ".context-menu-one",
            callback: function(key, options) {
                var m = "clicked: " + key;
                console.log(m);
            },
            items: {
                "Nuevo": {
                    name: "Nuevo",
                    icon: "edit"
                },
                "Modificar": {
                    name: "Modificar",
                    icon: "copy"
                },
                "Estudiantes del curso": {
                    name: "Estudiantes del curso",
                    icon: "paste"
                },
                "Reporte Valorativo": {
                    name: "Reporte Valorativo",
                    icon: "cut"
                },
                "Copiar": {
                    name: "Copiar",
                    icon: "copy"
                },
                "Eliminar": {
                    name: "Eliminar",
                    icon: "paste"
                },
                "Crear Nueva Categoria": {
                    name: "Crear Nueva Categoria",
                    icon: "copy"
                },
                "Subir Archivo": {
                    name: "Subir Archivo",
                    icon: "paste"
                }
            }
        });
    });
</script>

<table id="mi_tabla" class="table table-striped">
<thead>
<tr>

<th>Documento</th>
<th>Foto</th>
<th>Nombre</th>
<th>Correo</th>
<th>Estado</th>
<th>Asistencia</th>
<th>Inasistencia</th>
<th>Permiso</th>
<!--th></th-->
</tr>
</thead>
<tbody>

<?php
    while ($row = $consulta->fetch_assoc()) {
        $id_estudiante = $row['id_usuario'];
        $estudiante = utf8_decode($row['nombre']." ".$row['apellido']);
        $correo = $row['correo'];
        $estado = $row['estado_inscripcion'];
?>

<tr>
    
<td><?php echo $row['id_usuario']; ?></td>
<td><?php echo $row['foto']; ?></td>
<td><?php echo $estudiante; ?></td>
<td><?php echo $correo; ?></td>
<td><?php echo $estado; ?></td>
<td><?php 
 if(empty($data[$id_estudiante]['inasistencias'])) {
     echo 0;
     } else {
        echo $data[$id_estudiante]['inasistencias'];
    }
         ?></td>
<td><?php 
if(empty($data[$id_estudiante]['asistencias'])) {
    echo 0;
    } else {
       echo $data[$id_estudiante]['asistencias'];
   }
?></td>
<td><?php  
if(empty($data[$id_estudiante]['permiso'])) {
    echo 0;
    } else {
       echo $data[$id_estudiante]['permiso'];
   }
?></td>

<!--td><input id="opciones_cursos2" type="button" value="Opciones" class="btn btn-warning context-menu-one" name=""/></td-->
</tr>

<?php
    }
?>

</tbody>
</table>
</div>

<?php
    echo "<center>".($pagination->render())."</center>";
   # require_once("../comun/footer.php");
}

?>
<div id="droppable">Matricular estudiante</div>
<div class="jumbotron" style="background-image: url('<?php echo SGA_CURSOS_URL.'/'.$portada_asignacion; ?>');no-repeat left center; -webkit-background-size: cover; -moz-background-size: cover; -o-background-size: cover;">
    <form method="post" action="<?php echo SGA_MENSAJE_URL ?>/redactar.php" target="_blank">
        <input type="hidden" name="responder_a" value="<?php echo $_SESSION['id_usuario']; ?>">
        <input type="hidden" name="responder_n" value="<?php echo $_SESSION['nombre_docente']; ?>">
        <input type="image" style="margin-left: 900px; width: 10%; position: absolute; border-radius: 80%;" title="Enviar Mensaje al docente <?php echo $_SESSION['nombre_docente']; ?>" src="<?php echo READFILE_URL.'/foto/'.$_SESSION['docente']; ?>">
    </form>
    <input id="opciones_cursos2" type="button" value="Opciones" class="btn btn-warning context-menu-one" name=""/>
    <div class="container text-center">
        <h1 style="font-size:45px; <?php if(isset($portada_asignacion) && $portada_asignacion<>"") echo 'opacity:0.01'; ?>" id="estudiantes_curso" class="fip"><?php echo isset($curso) ? strtoupper($curso.' ('.$nombre_cate.')') : ''; ?></h1>
    </div>
</div>

<div class="container-fluid bg-3 text-center">
    <div class="row">
        <div id="lista_docentes" class="col-md-2">
            <?php
            if ($_SESSION['rol'] == "admin" || $_SESSION['rol'] == "docente") { ?>
                <br><br>
                <input type="search" id="datos_buscar_acudiente_para_asignar" placeholder="Buscar estudiante..." onkeyup="buscar_acudiente_para_asignar(this.value)">
                <ul id="ul_buscar_acudiente_para_asignar">
                    <?php buscar_acudiente_para_asignar(); ?>
                </ul>
            <?php } ?>
        </div>
        <div id="lista_docentes" class="col-md-10">
            <span id="txtsugerencias">
                <?php buscar_estudiante(); ?>
            </span>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        setTimeout(function() {
            cargar_tooltips();
        }, 4000);
    });
    buscar();
</script>

<?php
$contenido = ob_get_clean();

require ("../comun/plantilla.php");

?>

