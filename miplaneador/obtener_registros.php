<?php
// Establecer la cabecera para indicar que la respuesta es JSON
header('Content-Type: application/json');

// Incluir archivos de conexión y clases necesarios
require '../comun/conexion.php';
require_once("../comun/autoload.php");

// --- Configuración de la paginación ---
$registros_por_pagina = 10; // Puedes cambiar este valor

// --- Obtener la página actual ---
// Si no se especifica la página, se asume que es la primera
$pagina_actual = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;

// --- Calcular el total de registros ---
$sql_total = "SELECT COUNT(id_plan) as total FROM planeador_vallesol";
$resultado_total = $mysqli->query($sql_total);
$fila_total = $resultado_total->fetch_assoc();
$total_registros = (int)$fila_total['total'];

// --- Calcular el total de páginas ---
$total_paginas = ceil($total_registros / $registros_por_pagina);

// --- Calcular el offset para la consulta SQL ---
$offset = ($pagina_actual - 1) * $registros_por_pagina;

// --- Consulta SQL para obtener los registros de la página actual ---
$sql = "SELECT 
            p.id_plan,
            p.fecha_creacion,
            p.fecha_inicio,
            p.fecha_fin,
            p.grado,
            m.nombre_materia,
            p.dba
        FROM 
            planeador_vallesol p
        INNER JOIN 
            asignacion a ON a.id_asignacion = p.materia 
        INNER JOIN 
            materia_oficial m ON m.id_materia = a.id_asignatura 
        ORDER BY 
            p.id_plan DESC
        LIMIT ? OFFSET ?";

// --- Preparar y ejecutar la consulta ---
$stmt = $mysqli->prepare($sql);
if ($stmt === false) {
    echo json_encode(['error' => 'Error al preparar la consulta: ' . $mysqli->error]);
    exit();
}

$stmt->bind_param("ii", $registros_por_pagina, $offset);
$stmt->execute();
$result = $stmt->get_result();

$registros = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        // Formatear las fechas antes de enviarlas
        $row['fecha_creacion'] = Fecha::formato_fecha($row['fecha_creacion']);
        $row['fecha_inicio'] = Fecha::formato_fecha($row['fecha_inicio']);
        $row['fecha_fin'] = Fecha::formato_fecha($row['fecha_fin']);
        $registros[] = $row;
    }
}

// --- Cerrar la conexión ---
$stmt->close();
$mysqli->close();

// --- Crear la respuesta JSON ---
$respuesta = [
    'registros' => $registros,
    'total_paginas' => $total_paginas,
    'pagina_actual' => $pagina_actual
];

// --- Enviar la respuesta ---
echo json_encode($respuesta);
?>
