<?php
/**
 * Sincronización de DaVinci por cuenta de usuario.
 * Cada usuario conserva una única biblioteca de cuadernos, disponible desde
 * cualquier dispositivo en el que inicie sesión.
 */
@session_start();
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');

require_once '../../comun/conexion.php';
global $mysqli;

function respondDavinci($success, $data = null, $message = '', $status = 200) {
    http_response_code($status);
    echo json_encode([
        'success' => $success,
        'data' => $data,
        'message' => $message,
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

if (!isset($_SESSION['id_usuario'])) {
    respondDavinci(false, null, 'Tu sesión ha terminado. Inicia sesión de nuevo para sincronizar DaVinci.', 401);
}

$idUsuario = (string) $_SESSION['id_usuario'];

// La creación es idempotente y permite desplegar DaVinci sin una instalación
// manual adicional en instalaciones nuevas o anteriores de Guagua.
$tabla = "CREATE TABLE IF NOT EXISTS davinci_cuadernos (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    id_usuario VARCHAR(191) NOT NULL,
    state_json LONGTEXT NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_davinci_usuario (id_usuario)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

if (!$mysqli->query($tabla)) {
    respondDavinci(false, null, 'No fue posible preparar el almacenamiento de DaVinci.', 500);
}

$action = $_GET['action'] ?? $_POST['action'] ?? '';

if ($action === 'load') {
    $stmt = $mysqli->prepare('SELECT state_json, updated_at FROM davinci_cuadernos WHERE id_usuario = ? LIMIT 1');
    $stmt->bind_param('s', $idUsuario);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$row) {
        respondDavinci(true, ['state' => null, 'updatedAt' => null]);
    }

    $state = json_decode($row['state_json'], true);
    if (!is_array($state)) {
        respondDavinci(false, null, 'No fue posible leer tus cuadernos sincronizados.', 500);
    }

    respondDavinci(true, ['state' => $state, 'updatedAt' => $row['updated_at']]);
}

if ($action === 'save' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $raw = file_get_contents('php://input');
    $input = json_decode($raw, true);
    $state = $input['state'] ?? null;

    if (!is_array($state) || !isset($state['books']) || !is_array($state['books'])) {
        respondDavinci(false, null, 'El contenido del cuaderno no es válido.', 422);
    }

    $stateJson = json_encode($state, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    if ($stateJson === false) {
        respondDavinci(false, null, 'No fue posible preparar los datos para sincronizar.', 422);
    }

    // Mantiene margen seguro para PHP y MySQL, sin recortar los dibujos.
    if (strlen($stateJson) > 32 * 1024 * 1024) {
        respondDavinci(false, null, 'El proyecto es demasiado grande para sincronizarlo. Exporta una copia y divide el cuaderno.', 413);
    }

    $sql = 'INSERT INTO davinci_cuadernos (id_usuario, state_json) VALUES (?, ?)
            ON DUPLICATE KEY UPDATE state_json = VALUES(state_json), updated_at = CURRENT_TIMESTAMP';
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param('ss', $idUsuario, $stateJson);
    $ok = $stmt->execute();
    $stmt->close();

    if (!$ok) {
        respondDavinci(false, null, 'No fue posible sincronizar los cuadernos. Se conservaron en este dispositivo.', 500);
    }

    respondDavinci(true, ['updatedAt' => gmdate('c')], 'Cambios sincronizados.');
}

respondDavinci(false, null, 'Acción no válida.', 400);
