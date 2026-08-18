<?php
@session_start();
require_once dirname(__DIR__) . '/comun/autoload.php';

header('Content-Type: application/json; charset=utf-8');

$termino = trim((string)($_GET['q'] ?? ''));
if (function_exists('mb_substr')) {
    $termino = mb_substr($termino, 0, 100, 'UTF-8');
} else {
    $termino = substr($termino, 0, 100);
}

try {
    $curso = new Curso();
    $html = $curso->listar_cursos_home_tarjetas($termino);
    $total = substr_count($html, 'class="curso-home-card"');
    echo json_encode([
        'ok' => true,
        'html' => $html,
        'total' => $total,
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
} catch (Throwable $error) {
    http_response_code(500);
    echo json_encode([
        'ok' => false,
        'message' => 'No fue posible cargar las asignaciones.',
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
}
