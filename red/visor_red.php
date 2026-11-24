<?php
// 1. GESTIÓN DE SESIÓN Y CONFIGURACIÓN INICIAL
ob_start();
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Es una buena práctica usar require_once para evitar inclusiones múltiples.
require_once __DIR__ . '/../comun/conexion.php';
require_once __DIR__ . '/../comun/funciones.php';
require_once __DIR__ . '/../comun/autoload.php';
require_once __DIR__ . '/lib/visor.php';

// 2. OBTENCIÓN Y VALIDACIÓN DE DATOS
// Validar que el ID del recurso sea un número entero para mayor seguridad.
$id_red = filter_input(INPUT_GET, 'red', FILTER_VALIDATE_INT);

if (!$id_red || $id_red < 1) {
    http_response_code(400);
    $error_visor = 'El identificador del recurso no es válido.';
    require __DIR__ . '/views/error.php';
    $contenido = ob_get_clean();
    require __DIR__ . '/../comun/plantilla.php';
    exit;
}

// 3. LÓGICA DE BASE DE DATOS
// Unificar la obtención de toda la información del recurso en una sola consulta.
$sql_red = "SELECT r.*, u.nombre, u.apellido 
            FROM red r
            LEFT JOIN usuario u ON r.responsable = u.id_usuario
            WHERE r.id_red = ?";

$recurso = null;
if ($stmt_red = $mysqli->prepare($sql_red)) {
    $stmt_red->bind_param("i", $id_red);
    $stmt_red->execute();
    $resultado = $stmt_red->get_result();
    if ($resultado->num_rows === 1) {
        $recurso = $resultado->fetch_assoc();
    }
    $stmt_red->close();
}

// Si no se encontró el recurso, mostrar un mensaje y salir.
if (!$recurso) {
    http_response_code(404);
    $error_visor = 'El recurso solicitado no existe o ya no está disponible.';
    require __DIR__ . '/views/error.php';
    $contenido = ob_get_clean();
    require __DIR__ . '/../comun/plantilla.php';
    exit;
}

// Incrementar el contador de visitas de forma segura con una sentencia preparada.
$sql_visita = "UPDATE `red` SET `visitas` = `visitas` + 1 WHERE `id_red` = ?";
if ($stmt_visita = $mysqli->prepare($sql_visita)) {
    $stmt_visita->bind_param("i", $id_red);
    if ($stmt_visita->execute()) {
        $recurso['visitas'] = (int) $recurso['visitas'] + 1;
    }
    $stmt_visita->close();
}

// Limpieza de datos para la vista
$recurso['nivel_eductivo_formateado'] = str_replace(['[', ']', '"'], '', $recurso['nivel_eductivo']);
$recurso['fecha_formateada'] = formatofecha2($recurso['fecha']);

// Datos preparados para la vista y para detectar enlaces externos de forma segura.
$esc = static function ($valor) {
    return htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');
};
$enlace_original = trim((string) (isset($recurso['enlace']) ? $recurso['enlace'] : ''));
$url_valida = filter_var($enlace_original, FILTER_VALIDATE_URL);
$es_enlace_externo = $url_valida && in_array(strtolower((string) parse_url($enlace_original, PHP_URL_SCHEME)), ['http', 'https'], true);
$es_ruta_relativa = red_ruta_relativa_valida($enlace_original);
$enlace_accesible = $es_enlace_externo || $es_ruta_relativa;
$formato = strtolower(trim((string) (isset($recurso['formato']) ? $recurso['formato'] : '')));
$es_recurso_enlace = $es_enlace_externo && in_array($formato, ['link', 'enlace', 'url', 'web', 'website', 'html'], true);
$actividades = ($formato === 'zip' && ($recurso['scorm'] ?? '') === 'si') ? red_actividades_scorm($enlace_original) : [];
$total_estrellas = array_sum(array_map('intval', (array) json_decode($recurso['cantidad_estrellas'] ?? '[]', true)));

require __DIR__ . '/views/visor.php';
$contenido = ob_get_clean();
require __DIR__ . '/../comun/plantilla.php';
