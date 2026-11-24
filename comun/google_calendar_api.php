<?php
require_once __DIR__.'/seguridad.php';
require_once __DIR__.'/database.php';
require_once __DIR__.'/../apps/salud/google_connection.php';
guagua_iniciar_sesion_segura();
header('Content-Type: application/json; charset=utf-8');header('Cache-Control: no-store');
if (empty($_SESSION['id_usuario'])) guagua_error_acceso(401,'Inicia sesión en Guagua.');
if (($_SERVER['REQUEST_METHOD'] ?? 'GET')!=='GET') guagua_error_acceso(405,'Método no permitido.');
try {
    $connection=guagua_health_connection();$owner='calendar:'.(string)$_SESSION['id_usuario'];
    $params=http_build_query(['timeMin'=>gmdate('c'),'singleEvents'=>'true','orderBy'=>'startTime','maxResults'=>8,'showDeleted'=>'false','timeZone'=>GuaguaDatabase::settings()['timezone']]);
    for ($attempt=0;$attempt<2;$attempt++) {
        $token=$connection->accessToken($owner,$attempt===1);
        $ch=curl_init('https://www.googleapis.com/calendar/v3/calendars/primary/events?'.$params);
        curl_setopt_array($ch,[CURLOPT_HTTPHEADER=>['Authorization: Bearer '.$token],CURLOPT_RETURNTRANSFER=>true,
            CURLOPT_CONNECTTIMEOUT=>8,CURLOPT_TIMEOUT=>25,CURLOPT_FOLLOWLOCATION=>false,CURLOPT_PROTOCOLS=>CURLPROTO_HTTPS]);
        $raw=curl_exec($ch);$status=curl_getinfo($ch,CURLINFO_HTTP_CODE);curl_close($ch);
        if ($raw===false) throw new GuaguaGoogleHealthError('NETWORK','No se pudo contactar con Google Calendar.',502);
        if ($status===401 && $attempt===0) continue;
        if ($status===401) throw new GuaguaGoogleHealthError('RECONNECT','Google requiere volver a conectar el calendario.',401);
        if ($status>=400) throw new GuaguaGoogleHealthError('CALENDAR_ERROR','Google no permitió consultar el calendario. Revisa Calendar API y el permiso de lectura.',502);
        $data=json_decode($raw,true,32,JSON_THROW_ON_ERROR);
        echo json_encode(['items'=>$data['items'] ?? [],'timeZone'=>$data['timeZone'] ?? GuaguaDatabase::settings()['timezone']],JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
        exit;
    }
} catch (GuaguaGoogleHealthError $e) {http_response_code($e->status);echo json_encode(['reason'=>$e->reason,'message'=>$e->getMessage()],JSON_UNESCAPED_UNICODE);}
catch (Throwable $e) {error_log('[Guagua Calendar] '.get_class($e));guagua_error_acceso(500,'No se pudo acceder al calendario.');}
