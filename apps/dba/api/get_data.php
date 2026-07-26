<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . "/../../../comun/conexion.php";

header('Content-Type: application/json; charset=utf-8');

$response = [
    'status' => 'success',
    'data' => [
        'grados' => [],
        'materias' => [],
        'estandares' => [],
        'dbas' => [],
        'ejes_tematicos' => []
    ]
];

try {
    // 1. Grados
    $resGrados = $mysqli->query("SELECT id_grado, nombre FROM grado ORDER BY CAST(nombre AS UNSIGNED) ASC");
    if ($resGrados) {
        while ($row = $resGrados->fetch_assoc()) {
            $response['data']['grados'][] = $row;
        }
    }

    // 2. Materias
    $resMaterias = $mysqli->query("SELECT id_materia, nombre_materia FROM materia_oficial ORDER BY nombre_materia ASC");
    if ($resMaterias) {
        while ($row = $resMaterias->fetch_assoc()) {
            $response['data']['materias'][] = $row;
        }
    }

    // 3. Estándares
    $resEstandares = $mysqli->query("SELECT id_estandar, nombre_estandar, grado, id_periodo, id_materia_oficial FROM estandar");
    if ($resEstandares) {
        while ($row = $resEstandares->fetch_assoc()) {
            $response['data']['estandares'][] = $row;
        }
    }

    // 4. DBAs
    $resDbas = $mysqli->query("SELECT id_dba, nombre_dba, id_estandar FROM dba");
    if ($resDbas) {
        while ($row = $resDbas->fetch_assoc()) {
            $response['data']['dbas'][] = $row;
        }
    }

    // 5. Ejes Temáticos
    $resEjes = $mysqli->query("SELECT id_eje_tematico, nombre_eje_tematico, id_dba FROM eje_tematico");
    if ($resEjes) {
        while ($row = $resEjes->fetch_assoc()) {
            $response['data']['ejes_tematicos'][] = $row;
        }
    }
} catch (Exception $e) {
    $response['status'] = 'error';
    $response['message'] = $e->getMessage();
}

echo json_encode($response, JSON_UNESCAPED_UNICODE);
?>
