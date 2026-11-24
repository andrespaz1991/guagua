<?php
if (PHP_SAPI !== 'cli') exit;
ob_start();
require_once __DIR__ . '/../comun/database.php';
require_once __DIR__ . '/../usuario/lib/login_session.php';
require_once __DIR__ . '/../comun/opciones.php';
$sessionPath = sys_get_temp_dir() . '/guagua-config-session-' . bin2hex(random_bytes(8));
mkdir($sessionPath, 0700);
session_save_path($sessionPath);
guagua_iniciar_sesion_segura();
$db = guagua_db_mysqli();
function comprobar_config_sesion($valor, $mensaje) {
    if (!$valor) throw new RuntimeException($mensaje);
    echo "OK: $mensaje\n";
}
try {
    foreach ([
        'usuario (id_usuario VARCHAR(20), usuario VARCHAR(30), nombre VARCHAR(30), apellido VARCHAR(30), foto VARCHAR(50), rol VARCHAR(60), estado VARCHAR(15), num_visitas VARCHAR(10), puntos VARCHAR(10), ultima_sesion VARCHAR(30))',
        'institucion_educativa (id_institucion_educativa INT, nombre_institucion VARCHAR(60), plantilla INT, formatos_no_permitidos TEXT, tamano_maximo_adjunto INT, autoregistrarse VARCHAR(5))',
        'ano_lectivo (id_ano_lectivo INT, nombre_ano_lectivo VARCHAR(10), estado VARCHAR(20))',
        'periodo (id_periodo INT, nombre_periodo VARCHAR(20), fecha_inicio DATE, fecha_fin DATE, ano_lectivo VARCHAR(10), estado_periodo CHAR(1))',
    ] as $definition) $db->query('CREATE TEMPORARY TABLE ' . $definition);
    $db->query("INSERT INTO usuario VALUES ('T1','prueba','Nombre','Prueba','foto.png','estudiante,docente','activo','2','4',NULL),('T2','otra','Otra','Cuenta','otra.png','acudiente','activo','0','0',NULL)");
    $db->query("INSERT INTO institucion_educativa VALUES (9,'Institución de prueba',1,'exe,bat',2048,'NO'),(2,'Otra institución',1,'zip',4096,'SI')");
    $db->query("INSERT INTO ano_lectivo VALUES (55,'2031','Activo')");
    $db->query("INSERT INTO periodo VALUES (22,'Segundo','2031-04-01','2031-06-30','2031','1')");
    $usuario = $db->query("SELECT * FROM usuario WHERE id_usuario='T1'")->fetch_assoc();
    $_SESSION = ['hijo'=>'viejo','asigna'=>999,'asistencia'=>'no','id_institucion'=>1];
    login_crear_sesion($db, $usuario, '', 9);
    comprobar_config_sesion($_SESSION['rol'] === 'estudiante', 'El rol inicial respeta el orden guardado y no impone docente');
    comprobar_config_sesion($_SESSION['id_institucion'] === 9 && $_SESSION['institucion'] === 9, 'La institución procede de la selección validada');
    comprobar_config_sesion($_SESSION['id_usuario'] === 'T1' && $_SESSION['identificacion_usu'] === 'T1' && $_SESSION['id_usu'] === 'T1', 'Los alias de identidad son coherentes');
    comprobar_config_sesion($_SESSION['ano_lectivo'] === '2031' && $_SESSION['id_periodo'] === 22, 'Año y período proceden de la configuración activa');
    comprobar_config_sesion((int)$_SESSION['tamano_maximo_adjunto'] === 2048 && $_SESSION['autoregistrarse'] === 'NO', 'Opciones institucionales proceden de la base de datos');
    comprobar_config_sesion(!array_intersect_key($_SESSION, array_flip(['hijo','asigna','asistencia'])), 'El inicio de sesión descarta valores de la cuenta anterior');
    comprobar_config_sesion($_SESSION['num_visitas'] === 3 && $_SESSION['puntos'] === 5, 'Los contadores parten de la cuenta y se incrementan una vez');
    foreach ([['admin',9],['',1],['',0]] as [$rol,$sede]) {
        $antes = $_SESSION;
        try { login_crear_sesion($db,$usuario,$rol,$sede); throw new RuntimeException('Se aceptó un rol o institución inválidos.'); }
        catch (InvalidArgumentException $expected) {}
        comprobar_config_sesion($_SESSION === $antes, 'Una selección inválida no modifica la sesión');
    }
    $db->query("UPDATE institucion_educativa SET tamano_maximo_adjunto=8192 WHERE id_institucion_educativa=9");
    $db->query("UPDATE periodo SET estado_periodo='0'");
    guagua_refrescar_contexto_sesion($db);
    comprobar_config_sesion((int)$_SESSION['tamano_maximo_adjunto'] === 8192 && $_SESSION['id_periodo'] === null, 'Actualizar la configuración elimina valores de sesión obsoletos');
    $_SESSION['hijo'] = 'residuo';
    $otra = $db->query("SELECT * FROM usuario WHERE id_usuario='T2'")->fetch_assoc();
    login_crear_sesion($db,$otra,'acudiente',2);
    comprobar_config_sesion($_SESSION['nombre_usu'] === 'Otra Cuenta' && $_SESSION['institucion'] === 2 && !isset($_SESSION['hijo']), 'Cambiar de cuenta renueva identidad, institución y preferencias');
    $db->query("UPDATE usuario SET rol='estudiante' WHERE id_usuario='T2'");
    guagua_refrescar_contexto_sesion($db);
    comprobar_config_sesion($_SESSION === [], 'Un rol retirado no permanece autorizado en la sesión');
    guagua_refrescar_contexto_sesion($db);
    comprobar_config_sesion($_SESSION === [], 'La carga de opciones no autentica a usuarios anónimos');
    $sinRol = $usuario; $sinRol['rol'] = '';
    try { guagua_contexto_sesion($db,$sinRol,'',9); throw new RuntimeException('Se inventó un rol.'); }
    catch (InvalidArgumentException $expected) { comprobar_config_sesion(true, 'Una cuenta sin perfiles configurados no recibe privilegios de respaldo'); }
    class Clase_mysqli { public function conectar() { return $GLOBALS['db']; } }
    require_once __DIR__ . '/../clases/Institucion.Class.php';
    $institucion = new Institucion(9);
    comprobar_config_sesion($institucion->nombre_institucion === 'Institución de prueba' && (int)$institucion->tamano_maximo_adjunto === 8192, 'La clase Institución carga cada opción en su propiedad correspondiente');
    comprobar_config_sesion(is_file(guagua_plantilla_institucion(['plantilla'=>1])), 'La plantilla se resuelve con la opción institucional');
    try { guagua_plantilla_institucion(['plantilla'=>'../../config']); throw new RuntimeException('Se aceptó una plantilla inválida.'); }
    catch (RuntimeException $e) { comprobar_config_sesion($e->getMessage() !== 'Se aceptó una plantilla inválida.', 'Las plantillas desconocidas no usan una opción impuesta'); }
    $idAnterior = session_id();
    $_SESSION = ['id_usuario'=>'T1','rol'=>'estudiante','institucion'=>9,'guagua_csrf'=>'anterior'];
    login_cerrar_sesion();
    comprobar_config_sesion($_SESSION === [] && session_id() !== $idAnterior, 'Cerrar sesión elimina identidad, configuración y tokens anteriores');
} finally {
    session_destroy();
    foreach (glob($sessionPath . '/sess_*') as $file) unlink($file);
    rmdir($sessionPath);
}
ob_end_flush();
