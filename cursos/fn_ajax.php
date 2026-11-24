<?php
require_once __DIR__ . '/../comun/seguridad.php';
guagua_exigir_rol(['admin','directivo','docente']);
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
if (!is_string($_POST['texto'] ?? '')) guagua_error_acceso(422, 'Búsqueda inválida.');
require_once __DIR__ . '/../comun/database.php';
try {
    $db = guagua_db_mysqli();
    $term = '%' . str_replace(['!','%','_'], ['!!','!%','!_'], mb_substr(trim($_POST['texto'] ?? ''),0,150)) . '%';
    $sql = "SELECT DISTINCT a.ano_lectivo, a.id_categoria_curso FROM asignacion a
        JOIN materia_oficial m ON m.id_materia = a.id_asignatura
        JOIN usuario u ON u.id_usuario = a.id_docente
        WHERE (LOWER(m.nombre_materia) LIKE LOWER(?) ESCAPE '!' OR LOWER(u.nombre) LIKE LOWER(?) ESCAPE '!' OR LOWER(u.apellido) LIKE LOWER(?) ESCAPE '!')
        AND a.institucion_educativa = ?";
    $institution = (int)($_SESSION['id_institucion'] ?? $_SESSION['institucion'] ?? 0);
    if ($institution <= 0) guagua_error_acceso(403, 'Seleccione una institución.');
    $params = [$term,$term,$term,$institution]; $types = 'sssi';
    if ($_SESSION['rol'] === 'docente') { $sql .= ' AND a.id_docente = ?'; $params[] = (string)$_SESSION['id_usuario']; $types .= 's'; }
    $statement = $db->prepare($sql); $statement->bind_param($types,...$params); $statement->execute();
    $years = []; $categories = [];
    foreach ($statement->get_result()->fetch_all(MYSQLI_ASSOC) as $row) {
        $year = (int)$row['ano_lectivo']; $years[$year] = $year;
        $category = (string)$year . (int)$row['id_categoria_curso']; $categories[$category] = $category;
    }
    $statement->close();
    echo json_encode(['anos'=>array_values($years),'categorias'=>array_values($categories)]);
} catch (Throwable $error) {
    error_log('[Guagua búsqueda cursos] ' . get_class($error) . ' (' . $error->getCode() . ')');
    guagua_error_acceso(500, 'No se pudo consultar los cursos.');
}
