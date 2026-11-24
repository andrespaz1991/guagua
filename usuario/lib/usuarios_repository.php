<?php
require_once __DIR__ . '/../../comun/persona_imagen.php';
require_once __DIR__ . '/../../comun/sql.php';
/** Consultas del CRUD separadas de la vista. Los valores siempre son parámetros. */
function usuarios_query(mysqli $db, string $sql, array $values = [], string $types = '')
{
    return guagua_sql($db,$sql,$values,$types);
}

function usuarios_form_data(array $input): array
{
    $required = ['id_usuario', 'usuario', 'nombre', 'apellido'];
    $fields = ['id_usuario', 'tipo_documento', 'usuario', 'nombre', 'apellido', 'rol', 'genero', 'estado', 'correo', 'direccion', 'telefono', 'tipo_sangre', 'mascota', 'fecha_nacimiento', 'fecha_retiro', 'observaciones'];
    $data = [];
    foreach ($fields as $field) {
        if (isset($input[$field]) && !is_string($input[$field])) throw new InvalidArgumentException('Datos de usuario inválidos.');
        $data[$field] = trim($input[$field] ?? '');
        if (in_array($field, $required, true) && $data[$field] === '') throw new InvalidArgumentException('Complete documento, usuario, nombre y apellido.');
    }
    $data['genero'] = guagua_genero_normalizado($data['genero']);
    if (!preg_match('/\A[0-9]{1,20}\z/', $data['id_usuario'])) throw new InvalidArgumentException('Documento inválido.');
    $roles = array_map('trim', explode(',', $data['rol']));
    if (!$roles || array_diff($roles, ['admin','directivo','docente','estudiante','acudiente'])) throw new InvalidArgumentException('Seleccione un rol válido.');
    $data['rol'] = implode(',', array_unique($roles));
    foreach ([ 'estado'=>['activo','inactivo'], 'genero'=>['m','f'], 'mascota'=>['SI','NO'], 'tipo_documento'=>['CC','TI','CE','RC']] as $field=>$allowed) {
        if (!in_array($data[$field], $allowed, true)) throw new InvalidArgumentException('Seleccione un valor válido para ' . $field . '.');
    }
    if ($data['correo'] !== '' && !filter_var($data['correo'], FILTER_VALIDATE_EMAIL)) throw new InvalidArgumentException('Correo electrónico inválido.');
    foreach (['fecha_nacimiento','fecha_retiro'] as $field) {
        if ($data[$field] === '') continue;
        $date = DateTimeImmutable::createFromFormat('!Y-m-d', $data[$field]);
        if (!$date || $date->format('Y-m-d') !== $data[$field]) throw new InvalidArgumentException('Fecha inválida.');
    }
    foreach (['correo','direccion','telefono','tipo_sangre','fecha_nacimiento','fecha_retiro','observaciones'] as $field) {
        if ($data[$field] === '') $data[$field] = null;
    }
    return $data;
}

function usuarios_save(mysqli $db, array $input, ?string $original = null, ?string $restrictedRole = null): void
{
    $data = usuarios_form_data($input);
    if ($restrictedRole !== null && $data['rol'] !== $restrictedRole) throw new InvalidArgumentException('El perfil debe conservar el rol autorizado.');
    $columns = array_keys($data);
    if ($original === null) {
        $password = $input['password'] ?? '';
        if (!is_string($password) || strlen($password)<8 || strlen($password)>72 || str_contains($password,"\0")) throw new InvalidArgumentException('La contraseña inicial debe tener entre 8 y 72 bytes.');
        if ($data['mascota'] === 'SI') throw new InvalidArgumentException('Las cuentas nuevas deben usar una contraseña.');
        $data['clave'] = password_hash($password, PASSWORD_DEFAULT);
        $columns[] = 'clave';
        usuarios_query($db, 'INSERT INTO usuario (`' . implode('`,`', $columns) . '`) VALUES (' . implode(',', array_fill(0,count($columns),'?')) . ')', array_values($data));
    } else {
        $assignments = array_map(static fn($column) => '`' . $column . '` = ?', $columns);
        usuarios_query($db, 'UPDATE usuario SET ' . implode(',', $assignments) . ' WHERE id_usuario = ?' . ($restrictedRole !== null ? ' AND LOWER(TRIM(rol)) = ?' : ''), [...array_values($data), $original, ...($restrictedRole !== null ? [$restrictedRole] : [])]);
    }
}

function usuarios_list(mysqli $db, string $search, int $page, string $role = '', string $state = '', bool $onlyRole = false): array
{
    $filter = usuarios_list_filter($search, $role, $state, $onlyRole);
    $where = $filter['where'];
    $values = $filter['values'];
    $counts = usuarios_query($db, "SELECT COUNT(*) total, SUM(estado = 'activo') activos, SUM(estado = 'inactivo') inactivos FROM usuario" . $where, $values)->fetch_assoc();
    $total = (int)$counts['total'];
    $pages = max(1, (int)ceil($total / 10));
    $page = max(1, min($pages, $page));
    $result = usuarios_query($db, 'SELECT id_usuario, tipo_documento, usuario, nombre, apellido, rol, estado, correo, telefono FROM usuario' . $where . ' ORDER BY nombre ASC, apellido ASC, id_usuario ASC LIMIT ? OFFSET ?', [...$values, 10, ($page-1)*10], str_repeat('s',count($values)) . 'ii');
    return ['result'=>$result, 'total'=>$total, 'pages'=>$pages, 'page'=>$page, 'active'=>(int)$counts['activos'], 'inactive'=>(int)$counts['inactivos']];
}

/** Comparte los filtros entre el conteo y los resultados paginados. */
function usuarios_list_filter(string $search, string $role = '', string $state = '', bool $onlyRole = false): array
{
    $conditions = []; $values = [];
    if ($role !== '') {
        if (!in_array($role, ['admin','directivo','docente','estudiante','acudiente'], true)) throw new InvalidArgumentException('Filtro de rol inválido.');
        $conditions[] = $onlyRole ? "LOWER(TRIM(rol)) = ?" : "FIND_IN_SET(?, REPLACE(rol, ' ', '')) > 0";
        $values[] = $role;
    }
    if ($state !== '') {
        if (!in_array($state, ['activo','inactivo'], true)) throw new InvalidArgumentException('Filtro de estado inválido.');
        $conditions[] = 'estado = ?'; $values[] = $state;
    }
    $search = mb_substr(trim($search), 0, 150);
    if ($search !== '') {
        $conditions[] = "(LOWER(CONCAT_WS(' ', nombre, apellido)) LIKE LOWER(?) ESCAPE '!' OR id_usuario LIKE ? ESCAPE '!' OR LOWER(usuario) LIKE LOWER(?) ESCAPE '!' OR correo LIKE ? ESCAPE '!' OR telefono LIKE ? ESCAPE '!')";
        $term = '%' . str_replace(['!','%','_'], ['!!','!%','!_'], $search) . '%';
        $values = [...$values, ...array_fill(0, 5, $term)];
    }
    return ['where' => $conditions ? ' WHERE ' . implode(' AND ', $conditions) : '', 'values' => $values];
}
