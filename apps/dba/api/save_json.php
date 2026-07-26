<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json; charset=utf-8');

// Leemos el payload del frontend
$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!$data) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid JSON data received']);
    exit;
}

$jsonFilePath = __DIR__ . "/../dba.json";

// Guardamos en el archivo
$result = file_put_contents($jsonFilePath, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

if ($result !== false) {
    echo json_encode(['status' => 'success', 'message' => 'JSON guardado correctamente']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'No se pudo guardar el archivo JSON']);
}
?>
