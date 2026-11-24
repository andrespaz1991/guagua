<?php
/** Solo usa tablas temporales de esta conexión; no cambia registros reales. */
require_once __DIR__ . '/../comun/database.php';
require_once __DIR__ . '/../comun/academico.php';
require_once __DIR__ . '/../seguimiento/contexto.php';
require_once __DIR__ . '/../apps/notas/lib/NotasAuditoriaService.php';
require_once __DIR__ . '/../seguimiento/listado.php';
$db = guagua_db_mysqli();
function verificar($condicion, $mensaje) {
    if (!$condicion) throw new RuntimeException($mensaje);
    echo "OK: $mensaje\n";
}
foreach ([
    'ano_lectivo (id_ano_lectivo INT, nombre_ano_lectivo VARCHAR(10), estado VARCHAR(20))',
    'periodo (id_periodo INT, nombre_periodo VARCHAR(20), descripcion_periodo TEXT, fecha_inicio DATE, fecha_fin DATE, ano_lectivo VARCHAR(10), estado_periodo CHAR(1))',
    'asignacion (id_asignacion INT, ano_lectivo INT, institucion_educativa INT, id_docente INT, id_categoria_curso INT)',
    'inscripcion (id_inscripcion INT, id_asignacion INT, id_estudiante VARCHAR(20))',
    'usuario (id_usuario VARCHAR(20), nombre VARCHAR(40), apellido VARCHAR(40))',
    'categoria_curso (id_categoria_curso INT, nombre_categoria_curso VARCHAR(20))',
    'seguimiento (id_seguimiento INT, id_inscripcion INT, fecha_seguimiento DATE, hora_seguimiento TIME DEFAULT "09:00:00", observaciones TEXT DEFAULT "Observación de prueba")',
] as $definition) $db->query('CREATE TEMPORARY TABLE ' . $definition);
$db->query("INSERT INTO ano_lectivo VALUES (17,'2026','Inactivo'),(18,'2027','Activo')");
$db->query("INSERT INTO periodo VALUES
    (4,'4','','2026-09-01','2026-11-30','2026','1'),
    (9,'2','','2027-04-01','2027-06-30','2027','1')");
verificar(guagua_ano_configurado($db) === '2027', 'El año configurado prevalece sobre el calendario');
verificar((int)guagua_periodo_activo($db)['id_periodo'] === 9, 'Ignora períodos activos de otros años');
verificar(count($db->query(guagua_periodos_sql())->fetch_all()) === 1, 'El selector lista períodos del año activo');
verificar((int)(new NotasAuditoriaService($db))->obtenerPeriodoActual()['id_periodo'] === 9, 'Notas utiliza el período configurado aunque hoy esté fuera de sus fechas');
$db->query("INSERT INTO asignacion VALUES (1,18,1,10,6),(2,17,1,10,6),(3,18,1,20,6),(4,18,2,10,6)");
$db->query("INSERT INTO inscripcion VALUES (100,1,'A'),(200,2,'B'),(300,3,'C'),(400,4,'D')");
$db->query("INSERT INTO usuario VALUES ('A','Ana','Prueba'),('B','Beto','Prueba'),('C','Cata','Prueba'),('D','Dani','Prueba')");
$db->query("INSERT INTO categoria_curso VALUES (6,'6')");
$db->query("INSERT INTO seguimiento (id_seguimiento,id_inscripcion,fecha_seguimiento) VALUES (1,100,'2027-04-10'),(2,100,'2027-01-10'),(3,200,'2027-04-10'),(4,300,'2027-04-10'),(5,400,'2027-04-10')");
$_SESSION = ['id_usuario'=>10,'rol'=>'docente','id_institucion'=>1];
verificar(array_column(seguimiento_inscripciones($db), 'id_inscripcion') === ['100'], 'Seguimiento limita inscripciones por año, docente e institución');
$select = 'SELECT s.id_seguimiento FROM seguimiento s JOIN inscripcion i ON i.id_inscripcion=s.id_inscripcion JOIN asignacion a ON a.id_asignacion=i.id_asignacion WHERE ';
verificar(array_column($db->query($select . seguimiento_condicion($db))->fetch_all(MYSQLI_ASSOC), 'id_seguimiento') === ['1'], 'Seguimiento excluye otros períodos, años y cursos');
$mysqli = $db;
$_SERVER['REQUEST_URI'] = '/guagua/seguimiento/citas.php';
$_SERVER['PHP_SELF'] = '/guagua/seguimiento/citas.php';
ob_start(); buscar_seguimiento('Ana'); $html = ob_get_clean();
verificar(str_contains($html, 'Prueba Ana') && !str_contains($html, 'Prueba Beto'), 'El listado y la búsqueda muestran el nombre y filtran por contexto');
$_COOKIE['numeroresultadosseguimiento'] = '0';
ob_start(); buscar_seguimiento('NoExiste'); $html = ob_get_clean();
verificar(!str_contains($html, 'Prueba Ana'), 'La búsqueda vacía y un tamaño de página cero no causan fallos');
$_SESSION['rol'] = 'admin';
verificar(count(seguimiento_inscripciones($db)) === 2, 'Administración consulta docentes de su institución');
$db->query("UPDATE periodo SET ano_lectivo='18' WHERE id_periodo=9");
verificar((int)guagua_periodo_activo($db)['id_periodo'] === 9, 'Admite períodos históricos vinculados por ID del año');
$db->query("UPDATE periodo SET estado_periodo='0'");
verificar(guagua_periodo_activo($db) === null, 'Sin período activo no inventa uno por fecha');
verificar($db->query($select . seguimiento_condicion($db))->num_rows === 0, 'Sin período activo seguimiento no mezcla registros');
$db->query("UPDATE ano_lectivo SET estado='Inactivo'");
verificar(guagua_ano_activo($db) === null, 'Sin año activo devuelve ausencia de configuración');
echo "Pruebas terminadas; las tablas temporales desaparecen al cerrar la conexión.\n";
