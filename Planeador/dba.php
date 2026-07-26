<?php
ob_start();
require("../comun/conexion.php");
require_once("../comun/lib/Zebra_Pagination/Zebra_Pagination.php");

// Función para limpiar y escapar datos de entrada
function limpiar_dato($dato) {
    global $mysqli;
    $dato = trim($dato);
    $dato = stripslashes($dato);
    $dato = htmlspecialchars($dato);
    return $mysqli->real_escape_string($dato);
}

// Función para manejar errores
function manejar_error($mensaje, $redireccion = 'malla.php') {
    echo "<div class='error'>Error: $mensaje</div>";
    echo "<meta http-equiv='refresh' content='2; url=$redireccion' />";
    exit();
}

// Función para buscar evidencias con paginación
function buscar_evidencias($datos = "", $reporte = "") {
    global $mysqli;
    
    $resultados = (isset($_COOKIE['numeroresultados_evidencias']) ? $_COOKIE['numeroresultados_evidencias'] : 10);
    $paginacion = new Zebra_Pagination();
    $paginacion->records_per_page($resultados);
    
    $cookiepage = "page_evidencias";
    $funcionjs = "buscar();";
    $paginacion->fn_js_page("$funcionjs");
    $paginacion->cookie_page($cookiepage);
    $paginacion->padding(false);
    
    if (isset($_COOKIE["$cookiepage"])) $_GET['page'] = $_COOKIE["$cookiepage"];
    
    $sql = "SELECT e.id_evidencia_aprendizaje, e.descripcion_evidencia, e.id_dba, d.nombre_dba 
            FROM evidencia_de_aprendizaje e 
            LEFT JOIN dba d ON e.id_dba = d.id_dba 
            WHERE LOWER(e.descripcion_evidencia) LIKE ? OR LOWER(d.nombre_dba) LIKE ?";
    
    $busqueda = "%" . mb_strtolower($datos, 'UTF-8') . "%";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("ss", $busqueda, $busqueda);
    $stmt->execute();
    $resultado = $stmt->get_result();
    
    $paginacion->records($resultado->num_rows);
    
    $sql .= " LIMIT " . (($paginacion->get_page() - 1) * $resultados) . ", " . $resultados;
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("ss", $busqueda, $busqueda);
    $stmt->execute();
    $resultado = $stmt->get_result();
    
    // Mostrar resultados en una tabla
    echo "<table border='1' id='tbevidencias' align='center'>";
    echo "<thead><tr><th>ID</th><th>Descripción</th><th>DBA</th>";
    if ($reporte == "") {
        echo "<th colspan='2'>Acciones</th>";
    }
    echo "</tr></thead><tbody>";
    
    while ($row = $resultado->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['id_evidencia_aprendizaje']) . "</td>";
        echo "<td>" . htmlspecialchars($row['descripcion_evidencia']) . "</td>";
        echo "<td>" . htmlspecialchars($row['nombre_dba']) . "</td>";
        if ($reporte == "") {
            echo "<td>
                <form id='formModificar' name='formModificar' method='post' action='malla.php'>
                    <input name='cod' type='hidden' value='" . $row['id_evidencia_aprendizaje'] . "'>
                    <input type='submit' name='submit' value='Modificar'>
                </form>
            </td>";
            echo "<td>
                <input type='image' src='../../comun/img/eliminar.png' onclick='confirmeliminar(\"evidencias.php\", {\"del\":\"" . $row['id_evidencia_aprendizaje'] . "\"}, \"" . $row['id_evidencia_aprendizaje'] . "\");' value='Eliminar'>
            </td>";
        }
        echo "</tr>";
    }
    echo "</tbody></table>";
    
    echo "<div class='text-center'>";
    $paginacion->render();
    echo "</div>";
}

// Procesamiento de formularios
if (isset($_POST['submit'])) {
    $descripcion_evidencia = limpiar_dato($_POST['descripcion_evidencia']);
    $id_dba = limpiar_dato($_POST['id_dba']);
    
    if ($_POST['submit'] == "Registrar") {
        $stmt = $mysqli->prepare("INSERT INTO evidencia_de_aprendizaje (descripcion_evidencia, id_dba) VALUES (?, ?)");
        $stmt->bind_param("si", $descripcion_evidencia, $id_dba);
        if ($stmt->execute()) {
            echo "Registro exitoso";
            echo '<meta http-equiv="refresh" content="1; url=malla.php" />';
        } else {
            manejar_error("Registro fallido");
        }
    } elseif ($_POST['submit'] == "Actualizar") {
        $id_evidencia = limpiar_dato($_POST['cod']);
        $stmt = $mysqli->prepare("UPDATE evidencia_de_aprendizaje SET descripcion_evidencia = ?, id_dba = ? WHERE id_evidencia_aprendizaje = ?");
        $stmt->bind_param("sii", $descripcion_evidencia, $id_dba, $id_evidencia);
        if ($stmt->execute()) {
            echo "Modificación exitosa";
            echo '<meta http-equiv="refresh" content="1; url=malla.php" />';
        } else {
            manejar_error("Modificación fallida");
        }
    }
} elseif (isset($_POST['del'])) {
    $id_evidencia = limpiar_dato($_POST['del']);
    $stmt = $mysqli->prepare("DELETE FROM evidencia_de_aprendizaje WHERE id_evidencia_aprendizaje = ?");
    $stmt->bind_param("i", $id_evidencia);
    if ($stmt->execute()) {
        echo "Registro eliminado";
        echo '<meta http-equiv="refresh" content="1; url=malla.php" />';
    } else {
        manejar_error("Eliminación fallida, por favor compruebe que la evidencia no esté en uso");
    }
} else {
    // Mostrar formulario de búsqueda y resultados
    echo "<center><h1>Evidencias de Aprendizaje</h1></center>";
    echo "<center>
        <b><label>Buscar: </label></b><input type='search' id='buscar' onkeyup='buscar(this.value);' onchange='buscar(this.value);' style='margin: 15px;'>
        <b><label>N° de Resultados:</label></b>
        <input type='number' min='0' id='numeroresultados_evidencias' placeholder='Cantidad de resultados' title='Cantidad de resultados' value='10' onkeyup='grabarcookie(\"numeroresultados_evidencias\",this.value);buscar(document.getElementById(\"buscar\").value);' onchange='grabarcookie(\"numeroresultados_evidencias\",this.value);buscar(document.getElementById(\"buscar\").value);' size='4' style='width: 40px;'>
    </center>";
    echo "<span id='txtsugerencias'>";
    buscar_evidencias();
    echo "</span>";
}

$contenido = ob_get_contents();
ob_end_clean();
include("../comun/plantilla.php");
?>