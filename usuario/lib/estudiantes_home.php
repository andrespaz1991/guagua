<?php
require_once __DIR__ . '/../../comun/persona_imagen.php';
/** Directorio de inicio: solo estudiantes matriculados en el contexto autorizado. */
function guagua_estudiantes_home(mysqli $db, array $session, string $range = '', string $assignment = '', string $name = ''): array
{
    $role = $session['rol'] ?? '';
    $institution = (int)($session['id_institucion'] ?? $session['institucion'] ?? 0);
    if (!in_array($role, ['admin','directivo','docente'], true) || $institution <= 0) return [];
    if (!in_array($range,['','6-8','9-11'],true)) throw new InvalidArgumentException('Rango de grados inválido.');
    if ($assignment !== '' && (!ctype_digit($assignment) || (int)$assignment < 1)) throw new InvalidArgumentException('Curso inválido.');
    $sql = "SELECT DISTINCT u.id_usuario, u.nombre, u.apellido, u.foto, u.observaciones AS grado, u.genero
        FROM usuario u WHERE u.rol = 'estudiante' AND u.estado = 'activo'
        AND EXISTS (SELECT 1 FROM inscripcion i JOIN asignacion a ON a.id_asignacion = i.id_asignacion
            JOIN ano_lectivo al ON al.id_ano_lectivo = a.ano_lectivo
            WHERE i.id_estudiante = u.id_usuario AND a.institucion_educativa = ? AND al.estado = 'Activo'";
    $values = [$institution]; $types = 'i';
    if ($role === 'docente') { $sql .= ' AND a.id_docente = ?'; $values[] = (string)$session['id_usuario']; $types .= 's'; }
    if ($assignment !== '') { $sql .= ' AND a.id_asignacion = ?'; $values[] = (int)$assignment; $types .= 'i'; }
    $sql .= ')';
    if ($range === '6-8') $sql .= ' AND CAST(u.observaciones AS UNSIGNED) BETWEEN 6 AND 8';
    elseif ($range === '9-11') $sql .= ' AND CAST(u.observaciones AS UNSIGNED) BETWEEN 9 AND 11';
    if (trim($name) !== '') {
        $term = '%' . str_replace(['!','%','_'], ['!!','!%','!_'], mb_substr(trim($name),0,150)) . '%';
        $sql .= " AND (u.id_usuario LIKE ? ESCAPE '!' OR LOWER(u.nombre) LIKE LOWER(?) ESCAPE '!' OR LOWER(u.apellido) LIKE LOWER(?) ESCAPE '!' OR LOWER(CONCAT(u.nombre,' ',u.apellido)) LIKE LOWER(?) ESCAPE '!')";
        $values = [...$values, $term,$term,$term,$term]; $types .= 'ssss';
    }
    $sql .= ' ORDER BY CAST(u.observaciones AS UNSIGNED) DESC, u.apellido, u.nombre, u.id_usuario LIMIT 50';
    $statement = $db->prepare($sql);
    try {
        $statement->bind_param($types,...$values); $statement->execute();
        $students = $statement->get_result()->fetch_all(MYSQLI_ASSOC);
        foreach ($students as &$student) {
            $student['foto'] = guagua_persona_foto($student['foto'], $student['genero']);
            $student['foto_default'] = guagua_persona_foto('', $student['genero']);
        }
        unset($student);
        return $students;
    } finally { $statement->close(); }
}
