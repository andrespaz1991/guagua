<?php
require_once __DIR__ . '/sql.php';

function catalog_identifier(string $name): string
{
    if (!preg_match('/\A[a-zA-Z_][a-zA-Z0-9_]*\z/',$name)) throw new InvalidArgumentException('Identificador de catálogo inválido.');
    return '`' . $name . '`';
}
function catalog_scope(array $config, array $session): array
{
    if (empty($session['id_usuario']) || !in_array($session['rol'] ?? '',$config['roles'],true)) throw new InvalidArgumentException('No tiene acceso a este catálogo.');
    if ($config['table'] !== 'eventos') return ['sql'=>'','values'=>[]];
    $institution = (int)($session['id_institucion'] ?? $session['institucion'] ?? 0);
    if ($institution<=0) throw new InvalidArgumentException('Seleccione una institución.');
    $scope = ['sql'=>' AND institucion = ?', 'values'=>[$institution]];
    if ($session['rol']==='docente') { $scope['sql'] .= ' AND id_usuario = ?'; $scope['values'][] = (string)$session['id_usuario']; }
    return $scope;
}
function catalog_columns(array $config): string
{
    return implode(',',array_map('catalog_identifier',array_unique([$config['key'],...array_keys($config['fields'])])));
}
function catalog_filter(array $config, array $session, string $search): array
{
    $scope = catalog_scope($config,$session);
    $where = ' WHERE 1=1' . $scope['sql']; $values = $scope['values'];
    if (trim($search)!=='') {
        $term = '%' . str_replace(['!','%','_'],['!!','!%','!_'],mb_substr(trim($search),0,150)) . '%';
        $fields = array_unique([$config['key'],...array_keys($config['fields'])]);
        $where .= ' AND (' . implode(' OR ',array_map(static fn($field) => 'LOWER(' . catalog_identifier($field) . ") LIKE LOWER(?) ESCAPE '!'",$fields)) . ')';
        $values = [...$values,...array_fill(0,count($fields),$term)];
    }
    return ['where'=>$where,'values'=>$values];
}
function catalog_list(mysqli $db, array $config, array $session, string $search, int $page): array
{
    $filter = catalog_filter($config,$session,$search);
    $where = $filter['where']; $values = $filter['values'];
    $table = catalog_identifier($config['table']);
    $total = (int)guagua_sql($db,'SELECT COUNT(*) total FROM ' . $table . $where,$values)->fetch_assoc()['total'];
    $pages = max(1,(int)ceil($total/20)); $page = max(1,min($page,$pages));
    $rows = guagua_sql($db,'SELECT ' . catalog_columns($config) . ' FROM ' . $table . $where . ' ORDER BY ' . catalog_identifier($config['key']) . ' DESC LIMIT ? OFFSET ?', [...$values,20,($page-1)*20],str_repeat('s',count($values)) . 'ii')->fetch_all(MYSQLI_ASSOC);
    return ['rows'=>$rows,'total'=>$total,'pages'=>$pages,'page'=>$page];
}
function catalog_export(mysqli $db, array $config, array $session, string $search): mysqli_result
{
    $filter = catalog_filter($config,$session,$search);
    $table = catalog_identifier($config['table']);
    $total = (int)guagua_sql($db,'SELECT COUNT(*) total FROM ' . $table . $filter['where'],$filter['values'])->fetch_assoc()['total'];
    if ($total>5000) throw new InvalidArgumentException('La exportación supera 5000 registros. Acote la búsqueda antes de exportar.');
    return guagua_sql($db,'SELECT ' . catalog_columns($config) . ' FROM ' . $table . $filter['where'] . ' ORDER BY ' . catalog_identifier($config['key']) . ' DESC',$filter['values']);
}
function catalog_csv_cell($value): string
{
    $text = (string)$value;
    return preg_match('/^[\s\x00-\x1f]*[=+@-]/u',$text) ? "'" . $text : $text;
}
function catalog_find(mysqli $db, array $config, array $session, string $key): ?array
{
    $scope = catalog_scope($config,$session);
    return guagua_sql($db,'SELECT ' . catalog_columns($config) . ' FROM ' . catalog_identifier($config['table']) . ' WHERE ' . catalog_identifier($config['key']) . ' = ?' . $scope['sql'] . ' LIMIT 1',[$key,...$scope['values']])->fetch_assoc();
}
function catalog_save(mysqli $db, array $config, array $session, array $input, ?string $original): void
{
    $scope = catalog_scope($config,$session); $data = [];
    foreach ($config['fields'] as $field=>$label) {
        if (!is_string($input[$field] ?? '')) throw new InvalidArgumentException('Valor inválido para ' . $label . '.');
        $data[$field] = trim($input[$field] ?? '');
        $limit = in_array($field,['descripcion','descripcion_recurso','permisos','icono','href','class_color'],true) ? 60000 : 255;
        if (mb_strlen($data[$field])>$limit) throw new InvalidArgumentException($label . ' supera la longitud máxima permitida.');
    }
    if ($data[array_key_first($data)] === '') throw new InvalidArgumentException('Complete ' . reset($config['fields']) . '.');
    if ($config['table']==='tarjeta') {
        $permissions = json_decode($data['permisos'],true);
        if (!is_array($permissions) || array_keys($permissions)!==array_keys(array_values($permissions))) throw new InvalidArgumentException('Permisos debe ser una lista JSON de roles válidos.');
        foreach ($permissions as $role) if (!is_string($role) || !in_array($role,['admin','directivo','docente','estudiante','acudiente','invitado'],true)) throw new InvalidArgumentException('Rol de tarjeta inválido.');
        if (!in_array($data['class_color'],['default','primary','success','info','warning','danger'],true)) throw new InvalidArgumentException('Color de tarjeta inválido.');
        foreach (['href','accion_rapida'] as $field) {
            $scheme = parse_url($data[$field],PHP_URL_SCHEME);
            if (preg_match('/[\x00-\x20]/',$data[$field]) || ($scheme && !in_array(strtolower($scheme),['http','https'],true))) throw new InvalidArgumentException('Enlace de tarjeta inválido.');
        }
        // La función PHP antigua deja de ser un campo editable/ejecutable.
        if ($original===null) $data['funcion']='';
    }
    if ($config['table']==='eventos') {
        $date = DateTimeImmutable::createFromFormat('!Y-m-d',$data['fecha']);
        if (!$date || $date->format('Y-m-d')!==$data['fecha']) throw new InvalidArgumentException('Fecha del evento inválida.');
        foreach (['hora_inicio','hora_fin'] as $field) if (!preg_match('/\A(?:[01]\d|2[0-3]):[0-5]\d(?::[0-5]\d)?\z/',$data[$field])) throw new InvalidArgumentException('Hora inválida.');
        if ($data['hora_fin']<$data['hora_inicio']) throw new InvalidArgumentException('La hora final debe ser posterior a la inicial.');
        if ($original===null) { $data['institucion']=(int)($session['id_institucion'] ?? $session['institucion']); $data['id_usuario']=(string)$session['id_usuario']; }
    }
    $table = catalog_identifier($config['table']);
    if ($original===null) {
        guagua_sql($db,'INSERT INTO ' . $table . ' (' . implode(',',array_map('catalog_identifier',array_keys($data))) . ') VALUES (' . implode(',',array_fill(0,count($data),'?')) . ')',array_values($data));
    } else {
        if (!catalog_find($db,$config,$session,$original)) throw new InvalidArgumentException('Registro no disponible para su cuenta.');
        guagua_sql($db,'UPDATE ' . $table . ' SET ' . implode(',',array_map(static fn($field)=>catalog_identifier($field) . '=?',array_keys($data))) . ' WHERE ' . catalog_identifier($config['key']) . '=?' . $scope['sql'] . ' LIMIT 1',[...array_values($data),$original,...$scope['values']]);
    }
}
function catalog_delete(mysqli $db, array $config, array $session, string $key): void
{
    $scope = catalog_scope($config,$session);
    if (!catalog_find($db,$config,$session,$key)) throw new InvalidArgumentException('Registro no disponible para su cuenta.');
    guagua_sql($db,'DELETE FROM ' . catalog_identifier($config['table']) . ' WHERE ' . catalog_identifier($config['key']) . '=?' . $scope['sql'] . ' LIMIT 1',[$key,...$scope['values']]);
}
