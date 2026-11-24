<?php
if (PHP_SAPI !== 'cli') exit;
require_once __DIR__ . '/../comun/database.php';
require_once __DIR__ . '/../ia/lib/config_grupos.php';
$db = guagua_db_mysqli();
$db->query('CREATE TEMPORARY TABLE config_grupos (id INT AUTO_INCREMENT PRIMARY KEY, nombre VARCHAR(255), min_grado INT, max_grado INT, ruta_excel VARCHAR(512), ultimafila INT) ENGINE=InnoDB');
$db->query('CREATE TEMPORARY TABLE config_materias (id INT AUTO_INCREMENT PRIMARY KEY, grupo_id INT, nombre_materia VARCHAR(255), columna_excel VARCHAR(5)) ENGINE=InnoDB');
$archivo = tempnam(sys_get_temp_dir(), 'guagua_config_grupos_');
file_put_contents($archivo, json_encode(['mensaje_whatsapp'=>'Texto guardado']));
function comprobar_grupo($valor, $mensaje) {
    if (!$valor) throw new RuntimeException($mensaje);
    echo "OK: $mensaje\n";
}
try {
    $datos = ['nombre'=>'Grupo de prueba','min_grado'=>6,'max_grado'=>8,'ruta_excel'=>'C:/prueba.xlsx','ruta_excel_web'=>'https://example.com/prueba.xlsx','ultimafila'=>30,'materias'=>[['nombre'=>'Ciencias','columna'=>'E']]];
    $resultado = guagua_report_group_save($db,$datos,$archivo);
    comprobar_grupo($resultado['success'], 'Guardar la configuración escribe el grupo real');
    $grupo = $db->query('SELECT * FROM config_grupos')->fetch_assoc();
    $id = (int)$grupo['id'];
    comprobar_grupo($grupo['nombre'] === $datos['nombre'] && (int)$grupo['ultimafila'] === 30, 'La carga conserva las opciones enviadas');
    $materia = $db->query('SELECT * FROM config_materias')->fetch_assoc();
    comprobar_grupo($materia['nombre_materia'] === 'Ciencias' && $materia['columna_excel'] === 'E', 'Las materias y columnas se guardan sin datos de ejemplo');
    $rutas = json_decode(file_get_contents($archivo),true);
    comprobar_grupo($rutas[$id]['local'] === $datos['ruta_excel'] && $rutas['mensaje_whatsapp'] === 'Texto guardado', 'Las rutas se actualizan preservando otras opciones');
    $datos['id'] = $id; $datos['nombre'] = 'Nombre actualizado';
    comprobar_grupo(guagua_report_group_save($db,$datos,$archivo)['success'], 'La edición actualiza el mismo grupo');
    $datos['nombre'] = 'Cambio inválido'; $datos['materias'][0]['columna'] = '1;DROP';
    comprobar_grupo(!guagua_report_group_save($db,$datos,$archivo)['success'], 'Las columnas inválidas se rechazan');
    comprobar_grupo($db->query('SELECT nombre FROM config_grupos')->fetch_assoc()['nombre'] === 'Nombre actualizado', 'Un error revierte los cambios del grupo');
    comprobar_grupo(guagua_report_group_delete($db,$id)['success'] && $db->query('SELECT id FROM config_grupos')->num_rows === 0 && $db->query('SELECT id FROM config_materias')->num_rows === 0, 'Eliminar un grupo elimina también sus materias');
} finally {
    unlink($archivo);
}
