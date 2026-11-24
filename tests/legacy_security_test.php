<?php
require_once __DIR__ . '/../usuario/lib/passwords.php';
require_once __DIR__ . '/../usuario/lib/usuarios_repository.php';
require_once __DIR__ . '/../comun/home_cards.php';
require_once __DIR__ . '/../comun/files.php';
require_once __DIR__ . '/../usuario/lib/password_reset.php';
require_once __DIR__ . '/../comun/seguridad.php';
require_once __DIR__ . '/../comun/catalog_repository.php';
function audit_assert($condition, $message): void { if (!$condition) throw new RuntimeException($message); }
foreach ([sha1('secret'), sha1('secretSGA'), md5('secret'), password_hash('secret', PASSWORD_DEFAULT)] as $stored) {
    audit_assert(login_password_valida('secret',$stored), 'Se perdió una contraseña válida.');
    audit_assert(!login_password_valida($stored,$stored), 'Se aceptó el hash como contraseña.');
    audit_assert(!login_password_valida('incorrecta',$stored), 'Se aceptó una contraseña incorrecta.');
}
audit_assert(login_password_valida('texto antiguo','texto antiguo'), 'Se perdió compatibilidad antigua.');
$valid = ['id_usuario'=>'12345','usuario'=>'persona','nombre'=>"O'Neil",'apellido'=>'Paz','rol'=>'estudiante','estado'=>'activo','genero'=>'f','mascota'=>'NO','tipo_documento'=>'TI'];
audit_assert(usuarios_form_data($valid)['correo'] === null, 'Campos vacíos no son NULL.');
foreach ([['rol'=>'superadmin'],['fecha_nacimiento'=>'2026-02-30'],['nombre'=>['invalid']],['correo'=>'invalid']] as $invalid) {
    try { usuarios_form_data(array_replace($valid,$invalid)); throw new RuntimeException('Validación falló.'); }
    catch (InvalidArgumentException $expected) {}
}
class AuditCardService {
    public array $calls = [];
    public function __call($name,$args) { $this->calls[] = $name; }
}
$service = new AuditCardService();
$services = array_fill_keys(['academico','actividad','curso','red','mensajes'],$service);
audit_assert(guagua_home_link('javascript:alert(1)')==='#','Enlace ejecutable aceptado.');
audit_assert(!str_contains(guagua_home_escape('<img onerror="alert(1)">'),'<img'),'Título permite HTML.');
guagua_render_home_card(69,$services);
audit_assert($service->calls === ['leer_mensajes','mensajes_favoritos'], 'Tarjeta mensajes incompatible.');
ob_start(); guagua_render_home_card(999,$services); $result = ob_get_clean();
audit_assert(str_contains($result,'renderizador registrado') && count($service->calls)===2,'Se ejecutó una tarjeta no registrada.');
echo "OK: contraseñas, rechazo de hashes, validación de formularios y tarjetas sin eval.\n";
$secret = bin2hex(random_bytes(32)); $now = time();
$token = reset_issue('12345',sha1('old'),$secret,$now);
audit_assert(reset_valid($token,sha1('old'),$secret,$now+10),'Token válido rechazado.');
audit_assert(!reset_valid($token,sha1('changed'),$secret,$now+10),'Token reutilizable después del cambio.');
audit_assert(!reset_valid($token,sha1('old'),$secret,$now+1800),'Token expirado aceptado.');
audit_assert(!reset_valid($token,sha1('old'),str_repeat('x',64),$now+10),'Firma ajena aceptada.');
audit_assert(!reset_valid(str_replace('12345','99999',$token) . 'a',sha1('old'),$secret,$now),'Token manipulado aceptado.');
$fixture = dirname(__DIR__) . '/tmp/media-test-' . bin2hex(random_bytes(6));
mkdir($fixture);
try {
    file_put_contents($fixture . '/document.txt','fixture');
    file_put_contents($fixture . '/script.php','fixture');
    audit_assert(guagua_media_path($fixture,'document.txt')!==null,'Archivo permitido rechazado.');
    foreach (['../document.txt','..\\document.txt','/document.txt','script.php','.env','C:/document.txt',"document.txt\0"] as $path) audit_assert(guagua_media_path($fixture,$path)===null,'Ruta peligrosa aceptada.');
} finally {
    unlink($fixture . '/document.txt'); unlink($fixture . '/script.php'); rmdir($fixture);
}
echo "OK: tokens firmados, caducidad, invalidación y rutas de medios.\n";
audit_assert(guagua_origen_coincide('http://localhost/guagua/index.php','localhost',false),'Procedencia legítima rechazada.');
foreach (['http://localhost.attacker.test','https://localhost','http://localhost:8080','http://user@localhost','null',''] as $source) audit_assert(!guagua_origen_coincide($source,'localhost',false),'Procedencia externa aceptada.');
audit_assert(guagua_validar_nombres_archivos(['file'=>['name'=>['document.pdf','package.zip']]]),'Archivos legítimos rechazados.');
foreach (['shell.php','image.php.jpg','../icon.png','..\\icon.png','.htaccess','settings.env','C:icon.png'] as $name) audit_assert(!guagua_validar_nombres_archivos(['file'=>['name'=>$name]]),'Nombre peligroso aceptado.');
echo "OK: procedencia de peticiones y nombres de cargas.\n";
audit_assert(catalog_csv_cell('=1+1')==="'=1+1",'CSV permite fórmulas de datos.');
audit_assert(catalog_csv_cell('  @SUM(1,2)')==="'  @SUM(1,2)",'CSV permite fórmulas con espacios.');
audit_assert(catalog_csv_cell("O'Neil")==="O'Neil",'CSV altera texto literal.');
