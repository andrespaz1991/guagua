<?php
declare(strict_types=1);
require_once __DIR__.'/../../comun/seguridad.php';
guagua_iniciar_sesion_segura();
if(empty($_SESSION['id_usuario'])) { header('Location: ../../usuario/login.php'); exit; }
guagua_exigir_rol(['admin','directivo','docente']);
header('Cache-Control: no-store');
require_once __DIR__.'/lib.php';
require_once __DIR__.'/../../comun/cabecera_modulo.php';
$userKey=hash('sha256',(string)$_SESSION['id_usuario']);
$error=''; $message='';
if($_SERVER['REQUEST_METHOD']!=='GET' && $_SERVER['REQUEST_METHOD']!=='HEAD') {
    http_response_code(405);
    header('Allow: GET, HEAD');
    exit('Las notas de consulta se actualizan desde el PDF de referencia.');
}
$data=sn_load_data_for_user((string)$_SESSION['id_usuario']);
$data=sn_public_data($data);
$payload=['data'=>$data,'scope'=>$userKey,'fingerprint'=>hash('sha256',json_encode($data))];
function sn_e($v): string { return htmlspecialchars((string)$v,ENT_QUOTES,'UTF-8'); }
define('SN_VIEW_AUTHORIZED', true);
require __DIR__.'/view.php';
