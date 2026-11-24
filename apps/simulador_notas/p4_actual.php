<?php
declare(strict_types=1);
require_once __DIR__.'/../../comun/seguridad.php';
guagua_exigir_rol(['admin','directivo','docente']);
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, private');
$userId = (string)$_SESSION['id_usuario'];
session_write_close();
require_once __DIR__.'/lib.php';
try {
    $data = sn_load_data_for_user($userId);
    // Compatibilidad con pestañas antiguas: responder desde el PDF, sin consultar Excel ni Drive.
    $matches = [];
    foreach ($data['records'] as $row) {
        if ($row['p4'] !== null) $matches[$row['id']] = ['value'=>$row['p4'], 'file'=>$data['source'], 'sheet'=>'PDF', 'cell'=>'Página '.($row['source_page'] ?? '')];
    }
    $result = ['status'=>'ready','matches'=>$matches,'sources'=>[['file'=>$data['source'],'status'=>'ready']], 'unmatched'=>[], 'matched_count'=>count($matches),'total_count'=>count($data['records']),'checked_at'=>$data['imported_at']];
    echo json_encode(['ok'=>true,'fingerprint'=>hash('sha256',json_encode(sn_public_data($data))),'data'=>$result],JSON_UNESCAPED_UNICODE|JSON_HEX_TAG|JSON_INVALID_UTF8_SUBSTITUTE|JSON_THROW_ON_ERROR);
} catch (Throwable $error) {
    http_response_code(503);
    echo json_encode(['ok'=>false,'message'=>'No se pudo consultar el PDF de referencia. Recarga el módulo.'],JSON_UNESCAPED_UNICODE);
    error_log('[Simulador P4] '.get_class($error).': '.$error->getMessage());
}
