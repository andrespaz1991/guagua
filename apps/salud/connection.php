<?php
require_once __DIR__.'/../../comun/seguridad.php';
require_once __DIR__.'/../../comun/database.php';
require_once __DIR__.'/google_connection.php';
guagua_iniciar_sesion_segura();
header('Content-Type: application/json; charset=utf-8');header('Cache-Control: no-store');
if (empty($_SESSION['id_usuario'])) guagua_error_acceso(401,'Inicia sesión en Guagua.');
$owner=(string)$_SESSION['id_usuario'];$action=$_GET['action'] ?? 'status';$method=$_SERVER['REQUEST_METHOD'] ?? 'GET';
if (($_GET['service'] ?? '')==='calendar') $owner='calendar:'.$owner;
if (!(($action==='status' && $method==='GET') || (in_array($action,['connect','disconnect'],true) && $method==='POST'))) guagua_error_acceso(405,'Método no permitido.');
if ($method==='POST') {
    if (!guagua_validar_csrf()) guagua_error_acceso(403,'Recarga Guagua para continuar.');
    $source=$_SERVER['HTTP_ORIGIN'] ?? '';
    if (($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '')!=='XMLHttpRequest' || !guagua_origen_coincide($source,$_SERVER['HTTP_HOST'] ?? '',guagua_es_https())) guagua_error_acceso(403,'Origen no permitido.');
}
try {
    $connection=guagua_health_connection();
    if ($action==='connect') {
        $raw=file_get_contents('php://input',false,null,0,12001);
        if (strlen($raw)>12000) guagua_error_acceso(413,'Solicitud demasiado grande.');
        $input=json_decode($raw,true,8,JSON_THROW_ON_ERROR);$code=$input['code'] ?? '';
        if (!is_string($code) || strlen($code)<10 || strlen($code)>10000) throw new InvalidArgumentException('Código de Google no válido.');
        $origin=(guagua_es_https() ? 'https://' : 'http://').$_SERVER['HTTP_HOST'];
        $result=$connection->connect($owner,$code,$origin);
    } elseif ($action==='disconnect') {
        $connection->disconnect($owner);$result=$connection->status($owner);
    } else $result=$connection->status($owner);
    echo json_encode($result,JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
} catch (GuaguaGoogleHealthError $e) {
    http_response_code($e->status);echo json_encode(['reason'=>$e->reason,'message'=>$e->getMessage()],JSON_UNESCAPED_UNICODE);
} catch (InvalidArgumentException | JsonException $e) {guagua_error_acceso(422,'Solicitud no válida.');}
catch (Throwable $e) {error_log('[Google Health conexión] '.get_class($e));guagua_error_acceso(500,'No se pudo acceder a la conexión guardada de Google.');}
