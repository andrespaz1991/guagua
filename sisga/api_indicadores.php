<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/comun/seguridad.php';
require_once dirname(__DIR__) . '/comun/database.php';
require_once __DIR__ . '/lib/Indicadores.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
header('X-Content-Type-Options: nosniff');

try {
    guagua_exigir_rol(['admin', 'directivo', 'docente']);
    $metodo = $_SERVER['REQUEST_METHOD'] ?? 'GET';
    $repositorio = new SisgaIndicadores(guagua_db_pdo());
    if ($metodo === 'GET') {
        $respuesta = ['ok' => true, 'perfiles' => $repositorio->listar(), 'csrf' => guagua_csrf_token()];
    } elseif ($metodo === 'POST') {
        guagua_exigir_csrf();
        $contenido = file_get_contents('php://input', false, null, 0, 10 * 1024 * 1024 + 1);
        if (strlen($contenido) > 10 * 1024 * 1024) throw new InvalidArgumentException('La importación supera el límite de 10 MB. Importa menos archivos a la vez.');
        try { $datos = json_decode($contenido, true, 64, JSON_THROW_ON_ERROR); }
        catch (JsonException $e) { throw new InvalidArgumentException('La solicitud no contiene JSON válido.'); }
        if (!is_array($datos['archivos'] ?? null)) throw new InvalidArgumentException('Selecciona los scripts que deseas importar.');
        $resumen = $repositorio->importar($datos['archivos']);
        $respuesta = ['ok' => true, 'resumen' => $resumen, 'perfiles' => $repositorio->listar()];
    } else {
        http_response_code(405); header('Allow: GET, POST');
        $respuesta = ['ok' => false, 'mensaje' => 'Método no permitido.'];
    }
    echo json_encode($respuesta, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE | JSON_THROW_ON_ERROR);
} catch (InvalidArgumentException $error) {
    http_response_code(422);
    echo json_encode(['ok' => false, 'mensaje' => $error->getMessage()], JSON_UNESCAPED_UNICODE);
} catch (Throwable $error) {
    error_log('Indicadores SISGA: ' . $error->getMessage());
    http_response_code(500);
    echo json_encode(['ok' => false, 'mensaje' => 'No fue posible consultar o guardar los indicadores en la base de datos.'], JSON_UNESCAPED_UNICODE);
}
