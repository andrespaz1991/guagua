<?php
require_once __DIR__.'/../apps/salud/google_connection.php';
function verifyConnection($ok,$message) {if(!$ok) throw new RuntimeException($message);}
$db=new PDO('sqlite::memory:');$requests=[];
$scope='https://www.googleapis.com/auth/calendar.events.readonly';
$transport=function($url,$fields) use (&$requests,$scope) {
    $requests[]=[$url,$fields['grant_type'] ?? 'revoke'];
    if(str_ends_with($url,'revoke')) return [];
    if($fields['grant_type']==='refresh_token') return ['access_token'=>'renewed-access','expires_in'=>3600];
    return ['access_token'=>'first-access','refresh_token'=>'private-refresh','scope'=>$scope,'expires_in'=>0];
};
$connection=new GuaguaHealthConnection($db,'client','secret',str_repeat('a',64),$transport);
verifyConnection(!$connection->status('calendar:1')['connected'],'Cuenta inicialmente desconectada');
$connection->connect('calendar:1','test-code','http://localhost');
$cipher=$db->query('SELECT encrypted FROM google_connections')->fetchColumn();
verifyConnection(strpos($cipher,'private-refresh')===false && strpos(base64_decode($cipher),'private-refresh')===false,'Token cifrado en almacenamiento');
$reload=new GuaguaHealthConnection($db,'client','secret',str_repeat('a',64),$transport);
verifyConnection($reload->status('calendar:1')['connected'],'Nueva petición recuerda autorización');
verifyConnection(!$reload->status('calendar:2')['connected'] && !$reload->status('1')['connected'],'Aislamiento de usuarios y servicios');
verifyConnection($reload->accessToken('calendar:1')==='renewed-access','Renueva sin intervención del navegador');
$count=count($requests);
verifyConnection($reload->accessToken('calendar:1')==='renewed-access' && count($requests)===$count,'Reutiliza acceso vigente');
$status=$reload->status('calendar:1');
verifyConnection(!isset($status['refresh_token']) && !isset($status['access_token']),'Estado público no entrega tokens');
$bad=new GuaguaHealthConnection($db,'client','secret',str_repeat('b',64),$transport);
try {$bad->status('calendar:1');throw new LogicException('Aceptó clave errónea');}catch(RuntimeException $expected) {verifyConnection(!($expected instanceof LogicException),'Detecta cifrado alterado');}
$reload->disconnect('calendar:1');
verifyConnection(!$reload->status('calendar:1')['connected'],'Desconectar borra autorización');
// Una revocación requiere nueva conexión; una caída de red conserva el permiso.
$connection->connect('calendar:1','test-code','http://localhost');
$offline=new GuaguaHealthConnection($db,'client','secret',str_repeat('a',64),function(){throw new GuaguaGoogleHealthError('NETWORK','offline',502);});
try {$offline->accessToken('calendar:1');}catch(GuaguaGoogleHealthError $expected){}
verifyConnection($offline->status('calendar:1')['connected'],'Error temporal no borra la conexión');
$revoked=new GuaguaHealthConnection($db,'client','secret',str_repeat('a',64),function(){throw new GuaguaGoogleHealthError('RECONNECT','revoked',401);});
try {$revoked->accessToken('calendar:1');}catch(GuaguaGoogleHealthError $expected){}
verifyConnection(!$revoked->status('calendar:1')['connected'],'Revocación elimina credencial inválida');
echo "Conexión Google: persistencia, cifrado, aislamiento, renovación, revocación y fallos de red OK.\n";
