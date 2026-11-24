<?php
/** php usuario/auditar_generos.cli.php [--normalizar] */
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require_once __DIR__ . '/../comun/database.php';
require_once __DIR__ . '/../comun/persona_imagen.php';
$apply = in_array('--normalizar', $argv, true);
$db = guagua_db_mysqli();
$summary = ['estudiantes' => 0, 'femenino' => 0, 'masculino' => 0, 'pendientes' => [], 'generos_normalizados' => 0, 'iconos_corregidos' => 0, 'aplicado' => $apply];
$db->begin_transaction();
try {
    $rows = $db->query("SELECT id_usuario, nombre, apellido, genero, foto FROM usuario WHERE FIND_IN_SET('estudiante', REPLACE(rol, ' ', '')) ORDER BY apellido, nombre");
    $update = $db->prepare('UPDATE usuario SET genero=?, foto=? WHERE id_usuario=?');
    foreach ($rows as $row) {
        $summary['estudiantes']++;
        $gender = guagua_genero_normalizado($row['genero']);
        if ($gender === '') {
            $summary['pendientes'][] = ['id_usuario' => $row['id_usuario'], 'nombre' => trim($row['nombre'] . ' ' . $row['apellido']), 'genero_registrado' => $row['genero']];
        } else $summary[$gender === 'f' ? 'femenino' : 'masculino']++;
        // Mantener fotos personales y valores ambiguos para revisión humana.
        $canonical = $gender !== '' ? $gender : $row['genero'];
        $photo = guagua_foto_predeterminada($row['foto']) ? guagua_persona_foto('', $gender) : $row['foto'];
        if ($canonical !== $row['genero']) $summary['generos_normalizados']++;
        if ($photo !== $row['foto']) $summary['iconos_corregidos']++;
        if ($apply && ($canonical !== $row['genero'] || $photo !== $row['foto'])) {
            $update->bind_param('sss', $canonical, $photo, $row['id_usuario']); $update->execute();
        }
    }
    if ($apply) $db->commit(); else $db->rollback();
    echo json_encode($summary, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_INVALID_UTF8_SUBSTITUTE) . PHP_EOL;
} catch (Throwable $error) { $db->rollback(); throw $error; }
