<?php
require_once("../comun/autoload.php");
$inputRaw = file_get_contents('php://input');
file_put_contents("log.txt", $inputRaw . PHP_EOL, FILE_APPEND); // Guarda datos recibidos en un archivo

$input = json_decode($inputRaw, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    handleError('Error al decodificar JSON: ' . json_last_error_msg());
}

processRequest($input);


function sendJsonResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

function handleError($message, $statusCode = 400) {
    sendJsonResponse(['error' => $message], $statusCode);
}

function extractJson($texto) {
    if (preg_match('/\{.*\}/s', $texto, $matches)) {
        $jsonStr = $matches[0];
        $json = json_decode($jsonStr, true);
        return (json_last_error() === JSON_ERROR_NONE) ? $json : false;
    }
    return false;
}

function processRequest($input) {
    if (!isset($input['action']) || !isset($input['guia']) || !isset($input['instruccion'])) {
        handleError('Faltan parámetros requeridos');
    }

    if ($input['action'] !== 'getData') {
        handleError('Acción no reconocida');
    }

    $guia = $input['guia'];
    $instruccion = $input['instruccion'];

    // Concatenamos la guía con la instrucción antes de enviarla a la IA
    $mensajeIA = $guia . "\n\n" . $instruccion;

    $respuesta_ia = extractJson(Comun::ia_guagua($mensajeIA));

    if ($respuesta_ia === false) {
        handleError('No se pudo extraer un JSON válido de la respuesta');
    }

    sendJsonResponse(['nombre' => $respuesta_ia]);
}


// Inicio del script
$inputRaw = file_get_contents('php://input');
$input = json_decode($inputRaw, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    handleError('Error al decodificar JSON: ' . json_last_error_msg());
}

processRequest($input);
?>