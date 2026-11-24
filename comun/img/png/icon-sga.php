<?php
// Recurso público de la aplicación: no requiere sesión ni conexión a la BD.
header('Content-Type: text/css; charset=utf-8');
header('Cache-Control: public, max-age=300, must-revalidate');
$files = glob(__DIR__ . '/*.png') ?: [];
$signature = hash_init('sha256');
hash_update($signature,(string)filemtime(__FILE__));
foreach ($files as $file) hash_update($signature, basename($file));
$etag = '"' . hash_final($signature) . '"';
header('ETag: ' . $etag);
if (($_SERVER['HTTP_IF_NONE_MATCH'] ?? '') === $etag) { http_response_code(304); exit; }
foreach ($files as $file) {
    $name = pathinfo($file, PATHINFO_FILENAME);
    // Los nombres que no son sufijos CSS válidos no se convierten en reglas.
    if (!preg_match('/\A[a-zA-Z0-9_-]+\z/',$name)) continue;
    echo '.icon-sga-' . $name . '{background-image:url("' . rawurlencode(basename($file)) . '");}' . "\n";
}
