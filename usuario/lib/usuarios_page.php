<?php
/** Contexto y presentación del directorio, independientes de la sesión y la BD. */
function usuarios_roles(): array {
    return ['admin'=>'Administradores', 'directivo'=>'Directivos', 'docente'=>'Docentes', 'estudiante'=>'Estudiantes', 'acudiente'=>'Acudientes'];
}
function usuarios_escape($value): string {
    return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
function usuarios_text(array $input, string $key, string $default = ''): string {
    return isset($input[$key]) && is_string($input[$key]) ? trim($input[$key]) : $default;
}
function usuarios_page_context(array $input): array {
    $role = usuarios_text($input, 'u');
    $state = usuarios_text($input, 'estado_filtro');
    return [
        'u'=>isset(usuarios_roles()[$role]) ? $role : '',
        'estado_filtro'=>in_array($state, ['activo','inactivo'], true) ? $state : '',
        'buscar'=>mb_substr(usuarios_text($input, 'buscar'), 0, 150),
        'page'=>max(1, (int)usuarios_text($input, 'page', '1')),
        'guagua_embedded'=>usuarios_text($input, 'guagua_embedded') === '1' ? '1' : '',
    ];
}
function usuarios_page_url(array $context, array $changes = []): string {
    $params = array_replace($context, $changes);
    $params = array_filter($params, static fn($v) => $v !== '' && $v !== null);
    return 'usuario.php' . ($params ? '?' . http_build_query($params) : '');
}
function usuarios_has_role(string $roles, string $role): bool {
    return $role === '' || in_array($role, array_map('trim', explode(',', $roles)), true);
}

/** El personal docente puede gestionar los directorios de la comunidad educativa. */
function usuarios_directorio_policy(string $actor, string $requestedRole): array {
    if ($requestedRole !== '' && !isset(usuarios_roles()[$requestedRole])) {
        throw new InvalidArgumentException('El directorio solicitado no es válido.');
    }
    if (in_array($actor, ['admin', 'directivo', 'docente'], true)) {
        return ['role'=>$requestedRole, 'restricted'=>false, 'can_delete'=>$actor !== 'docente'];
    }
    throw new InvalidArgumentException('La gestión de usuarios requiere un perfil docente, administrador o directivo.');
}
function usuarios_directorio_target(string $roles, array $policy): bool {
    return $policy['restricted']
        ? strtolower(trim($roles)) === 'acudiente'
        : usuarios_has_role($roles, $policy['role']);
}
function usuarios_directorio_validate(array $policy, string $action, ?array $existing = null, ?array $data = null): void {
    if ($action === 'delete' && !$policy['can_delete']) throw new InvalidArgumentException('La eliminación permanente requiere un administrador o directivo.');
    if ($existing !== null && !usuarios_directorio_target((string)$existing['rol'], $policy)) throw new InvalidArgumentException('El usuario no está disponible en este directorio.');
    if ($data !== null && !usuarios_directorio_target((string)$data['rol'], $policy)) throw new InvalidArgumentException('El perfil debe conservar el rol de este directorio.');
    if ($policy['restricted'] && $data !== null && $data['mascota'] !== ($existing['mascota'] ?? 'NO')) throw new InvalidArgumentException('El cambio del acceso como mascota institucional requiere un administrador.');
}

function usuarios_directorio_textos(string $role): array {
    $singular = ['admin'=>'administrador','directivo'=>'directivo','docente'=>'docente','estudiante'=>'estudiante','acudiente'=>'acudiente'];
    $perfil = $singular[$role] ?? 'perfil';
    $descripciones = [
        'admin'=>'Administre las cuentas responsables de la configuración y gestión de la plataforma.',
        'acudiente'=>'Organice los datos de contacto y el acceso de las familias a la plataforma.',
    ];
    return [
        'descripcion'=>$descripciones[$role] ?? 'Consulte y administre los perfiles de la comunidad educativa.',
        'nuevo'=>'Nuevo ' . $perfil,
        'crear'=>'Crear ' . $perfil,
        'editar'=>'Editar ' . $perfil,
    ];
}
function usuarios_validar_cuenta_propia(string $actorId, string $actorRole, string $original, array $data): void {
    if ($actorId !== $original) return;
    if ($data['id_usuario'] !== $actorId) throw new InvalidArgumentException('El documento de su cuenta actual no se puede cambiar desde este directorio.');
    if ($data['estado'] !== 'activo') throw new InvalidArgumentException('No puede desactivar su propia cuenta.');
    if (!usuarios_has_role($data['rol'], $actorRole)) throw new InvalidArgumentException('Su cuenta debe conservar el rol con el que inició sesión.');
}
