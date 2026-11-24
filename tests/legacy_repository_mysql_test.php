<?php
require_once __DIR__ . '/../comun/database.php';
require_once __DIR__ . '/../usuario/lib/usuarios_repository.php';
require_once __DIR__ . '/../usuario/lib/estudiantes_home.php';
require_once __DIR__ . '/../usuario/lib/password_reset.php';
require_once __DIR__ . '/../comun/catalog_repository.php';
function assert_repo($condition,$message) { if (!$condition) throw new RuntimeException($message); }
$db = guagua_db_mysqli();
mysqli_report(MYSQLI_REPORT_ERROR|MYSQLI_REPORT_STRICT);
// Tablas TEMPORARY de esta conexión ocultan las reales y desaparecen al cerrar.
$fields = ['id_usuario','tipo_documento','usuario','nombre','apellido','rol','genero','estado','correo','direccion','telefono','tipo_sangre','mascota','fecha_nacimiento','fecha_retiro','observaciones','clave','foto'];
$schema = array_map(static fn($field) => '`'.$field.'` VARCHAR(255) NULL',$fields);
$db->query('CREATE TEMPORARY TABLE usuario ('.implode(',',$schema).', fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP, PRIMARY KEY(id_usuario))');
$data = ['id_usuario'=>'12345','usuario'=>'persona','nombre'=>"O'Neil <script>",'apellido'=>'Paz','rol'=>'estudiante','estado'=>'activo','genero'=>'f','mascota'=>'NO','tipo_documento'=>'TI','observaciones'=>'7','password'=>'UnaClaveSegura123'];
usuarios_save($db,$data);
$stored = usuarios_query($db,'SELECT clave FROM usuario WHERE id_usuario = ?',['12345'])->fetch_assoc()['clave'];
assert_repo(password_verify('UnaClaveSegura123',$stored),'Nueva contraseña no es moderna.');
assert_repo(usuarios_list($db,"O'Neil",1)['total']===1,'Apóstrofe no se conserva.');
assert_repo(usuarios_list($db,"o'neil",1)['total']===1,'Búsqueda perdió compatibilidad sin distinguir mayúsculas.');
assert_repo(usuarios_list($db,"' OR 1=1 --",1)['total']===0,'Búsqueda vulnerable a SQL.');
assert_repo(usuarios_list($db,'%',1)['total']===0,'Comodín no se trata literalmente.');
assert_repo(usuarios_list($db,'',PHP_INT_MAX)['page']===1,'Página fuera de rango.');
usuarios_save($db,array_replace($data,['id_usuario'=>'23456','usuario'=>'familia','rol'=>'docente,acudiente','nombre'=>'Ana','apellido'=>'Pérez','estado'=>'activo']));
assert_repo(usuarios_list($db,'',1,'acudiente')['total']===1,'El rol de acudiente no filtra cuentas con múltiples roles.');
assert_repo(usuarios_list($db,'',1,'acudiente','',true)['total']===0,'El listado docente incluye cuentas con otros roles.');
assert_repo(usuarios_list($db,"O'Neil",1,'acudiente')['total']===0,'La búsqueda ignora el filtro de rol.');
assert_repo(usuarios_list($db,'Ana Pérez',1,'acudiente','activo')['total']===1,'No busca nombre completo con rol y estado.');
assert_repo(usuarios_list($db,'Ana',1,'acudiente','inactivo')['total']===0,'La búsqueda ignora el estado.');
usuarios_query($db,'DELETE FROM usuario WHERE id_usuario = ?',['23456']);

usuarios_save($db,array_replace($data,['telefono'=>"300'123"]),'12345');
assert_repo(usuarios_query($db,'SELECT clave FROM usuario WHERE id_usuario = ?',['12345'])->fetch_assoc()['clave']===$stored,'Editar cambió la contraseña.');
$db->query('CREATE TEMPORARY TABLE ano_lectivo (id_ano_lectivo INT, estado VARCHAR(20))');
$db->query('CREATE TEMPORARY TABLE asignacion (id_asignacion INT, institucion_educativa INT, id_docente VARCHAR(20), ano_lectivo INT)');
$db->query('CREATE TEMPORARY TABLE inscripcion (id_asignacion INT, id_estudiante VARCHAR(20))');
$db->query("INSERT INTO ano_lectivo VALUES (17,'Activo'),(16,'Inactivo')");
$db->query("INSERT INTO asignacion VALUES (10,7,'99',17),(11,8,'99',17)");
$db->query("INSERT INTO inscripcion VALUES (10,'12345')");
$session = ['id_usuario'=>'99','rol'=>'docente','id_institucion'=>7];
assert_repo(count(guagua_estudiantes_home($db,$session,'6-8','10',"O'Neil"))===1,'Curso/grado activo no funciona.');
assert_repo(guagua_estudiantes_home($db,array_replace($session,['id_usuario'=>'88']))===[],'Docente ve estudiantes ajenos.');
assert_repo(guagua_estudiantes_home($db,array_replace($session,['id_institucion'=>8]))===[],'Institución ve estudiantes ajenos.');
assert_repo(guagua_estudiantes_home($db,$session,'','',"' OR 1=1 --")===[],'Directorio vulnerable a SQL.');
$db->query("UPDATE ano_lectivo SET estado='Inactivo'");
assert_repo(guagua_estudiantes_home($db,$session)===[],'Se ignora año configurado.');
$secret = bin2hex(random_bytes(32)); $token = reset_issue('12345',$stored,$secret,time());
assert_repo(reset_apply($db,$token,'NuevaClaveSegura123',$secret),'Restablecimiento válido falló.');
assert_repo(!reset_apply($db,$token,'OtraClaveSegura123',$secret),'Se reutilizó un enlace de recuperación.');
assert_repo(password_verify('NuevaClaveSegura123',usuarios_query($db,'SELECT clave FROM usuario WHERE id_usuario=?',['12345'])->fetch_assoc()['clave']),'Contraseña nueva no se guardó.');
class Comun {}
require_once __DIR__ . '/../clases/Clase_mysqli.Class.php';
require_once __DIR__ . '/../seguimiento/mysql.class.php';
$legacy = new Clase_mysqli();
$literal = json_decode($legacy->consultar_datos('SELECT ? AS value',true,['CREATE UPDATE DROP']),true);
assert_repo($literal[0]['value']==='CREATE UPDATE DROP','El lector altera datos que contienen palabras SQL.');
$_POST['consulta'] = 'SELECT 1';
try { $legacy->consultar_datos(); throw new RuntimeException('Se aceptó SQL del cuerpo HTTP.'); }
catch (InvalidArgumentException $expected) {}
class AuditTracking extends Conexion {
    private mysqli $connection;
    public function __construct(mysqli $connection) { $this->connection=$connection; }
    public function conectar() { return $this->connection; }
}
$tracking = new AuditTracking($db);
$before = (int)$db->query("SHOW SESSION STATUS LIKE 'Com_stmt_prepare'")->fetch_row()[1];
assert_repo(json_decode($tracking->consultar_datos('SELECT ? AS value',true,['CREATE UPDATE DROP']),true)[0]['value']==='CREATE UPDATE DROP','Lector de seguimiento incompatible.');
$after = (int)$db->query("SHOW SESSION STATUS LIKE 'Com_stmt_prepare'")->fetch_row()[1];
assert_repo($after-$before===1,'La consulta se prepara más de una vez.');
$freshTracking = new Conexion();
assert_repo(json_decode($freshTracking->consultar_datos('SELECT 1 AS value',true),true)[0]['value']===1,'La conexión se cerró antes de ejecutar.');
$db->query('CREATE TEMPORARY TABLE contenido (contenido VARCHAR(255) PRIMARY KEY)');
$definitions = require __DIR__ . '/../comun/catalog_definitions.php';
$catalog = $definitions['contenido'];
catalog_save($db,$catalog,$session,['contenido'=>"O'Neil <script>"],null);
assert_repo(catalog_list($db,$catalog,$session,"o'neil",1)['total']===1,'Catálogo no preserva texto/apóstrofes.');
assert_repo(catalog_list($db,$catalog,$session,"' OR 1=1 --",1)['total']===0,'SQL concatenado en catálogo.');
assert_repo(catalog_export($db,$catalog,$session,"o'neil")->num_rows===1,'Exportación ignora búsqueda/permisos.');
catalog_save($db,$catalog,$session,['contenido'=>'Actualizado'],"O'Neil <script>");
assert_repo(catalog_find($db,$catalog,$session,'Actualizado')!==null,'Actualización de catálogo falló.');
catalog_delete($db,$catalog,$session,'Actualizado');
assert_repo(catalog_list($db,$catalog,$session,'',1)['total']===0,'Eliminación de catálogo falló.');
$db->query('CREATE TEMPORARY TABLE eventos (id INT AUTO_INCREMENT PRIMARY KEY, nom VARCHAR(255), descripcion TEXT, fecha DATE, hora_inicio TIME, hora_fin TIME, id_usuario VARCHAR(20), institucion INT)');
$db->query('CREATE TEMPORARY TABLE recursos (id_recurso INT AUTO_INCREMENT PRIMARY KEY, nombre_recurso VARCHAR(255), descripcion_recurso TEXT)');
catalog_save($db,$definitions['recursos'],$session,['nombre_recurso'=>"Recurso O'Neil",'descripcion_recurso'=>'Descripción'],null);
$resource=catalog_list($db,$definitions['recursos'],$session,'',1)['rows'][0];
catalog_delete($db,$definitions['recursos'],$session,(string)$resource['id_recurso']);
assert_repo(catalog_list($db,$definitions['recursos'],$session,'',1)['total']===0,'Recursos no usa su ID real.');
$events = $definitions['eventos'];
catalog_save($db,$events,$session,['nom'=>'Evento','descripcion'=>'Texto','fecha'=>'2026-10-12','hora_inicio'=>'08:00','hora_fin'=>'09:00','id_usuario'=>'88','institucion'=>8],null);
$event = $db->query('SELECT * FROM eventos')->fetch_assoc();
assert_repo($event['id_usuario']==='99' && (int)$event['institucion']===7,'Evento permite suplantar dueño/institución.');
$other = array_replace($session,['id_usuario'=>'88']);
assert_repo(catalog_find($db,$events,$other,(string)$event['id'])===null,'Docente consulta evento ajeno.');
try { catalog_delete($db,$events,$other,(string)$event['id']); throw new RuntimeException('Docente borró evento ajeno.'); }
catch (InvalidArgumentException $expected) {}
assert_repo(catalog_list($db,$events,array_replace($session,['rol'=>'admin','id_institucion'=>8]),'',1)['total']===0,'Eventos no separan instituciones.');
usuarios_query($db,'DELETE FROM usuario WHERE id_usuario=?',['12345']);
assert_repo(usuarios_list($db,'',1)['total']===0,'Eliminación preparada falló.');
$db->close();
echo "OK: CRUD, catálogos, exportación, recuperación, inyección y aislamiento de institución/docente/año. Solo tablas temporales; ningún registro real modificado.\n";
