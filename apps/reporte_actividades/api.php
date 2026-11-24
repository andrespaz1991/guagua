<?php
declare(strict_types=1);
require_once dirname(__DIR__,2).'/comun/seguridad.php';
guagua_exigir_rol(['admin','docente','directivo']);
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, private');
session_write_close();
require_once __DIR__.'/lib/ValoracionesReader.php';
try {
    $reader=new ValoracionesReader();
    $root=GuaguaDatabase::environment('GUAGUA_VALORACIONES_DIR') ?: ValoracionesReader::DEFAULT_ROOT;
    echo json_encode(['ok'=>true,'data'=>$reader->readDirectory($root)],JSON_UNESCAPED_UNICODE|JSON_INVALID_UTF8_SUBSTITUTE|JSON_THROW_ON_ERROR);
} catch (Throwable $e) {
    http_response_code(503);
    echo json_encode(['ok'=>false,'message'=>$e->getMessage()],JSON_UNESCAPED_UNICODE|JSON_INVALID_UTF8_SUBSTITUTE);
}
