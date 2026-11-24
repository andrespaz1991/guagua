<?php
require_once __DIR__ . '/../usuario/lib/usuarios_repository.php';
require_once __DIR__ . '/../usuario/lib/usuarios_page.php';
function users_check($condition, $message) { if (!$condition) throw new RuntimeException($message); }
set_error_handler(function ($severity, $message, $file, $line) { throw new ErrorException($message, 0, $severity, $file, $line); });
$contexto = usuarios_page_context(['u'=>'acudiente','guagua_embedded'=>'1','buscar'=>'Ana Pérez','page'=>'2','estado_filtro'=>'activo']);
$url = usuarios_page_url($contexto, ['Actualizar'=>'123']);
parse_str(parse_url($url, PHP_URL_QUERY), $params);
users_check($params['u'] === 'acudiente' && $params['guagua_embedded'] === '1' && $params['buscar'] === 'Ana Pérez' && $params['Actualizar'] === '123', 'La navegación pierde contexto');
users_check(usuarios_page_context(['u'=>[], 'buscar'=>[], 'page'=>[]])['u'] === '', 'Parámetros de tipo incorrecto');
users_check(usuarios_page_context(['estado_filtro'=>'otro', 'page'=>'-2'])['page'] === 1, 'Página negativa');
$filter = usuarios_list_filter("Ana %_!", 'acudiente', 'activo');
users_check(strpos($filter['where'], ' AND (') !== false, 'La búsqueda no está agrupada respecto al rol y estado');
users_check($filter['values'][0] === 'acudiente' && $filter['values'][1] === 'activo', 'Valores de filtros incorrectos');
users_check($filter['values'][2] === '%Ana !%!_!!%', 'Comodines de búsqueda sin escapar');
users_check(usuarios_has_role('docente, acudiente', 'acudiente'), 'Cuenta con múltiples roles no reconocida');
users_check(!usuarios_has_role('docente', 'acudiente'), 'Cuenta de otro rol incluida');
$valid = ['id_usuario'=>'123', 'tipo_documento'=>'CC', 'usuario'=>'ana', 'nombre'=>'Ana', 'apellido'=>'Pérez', 'rol'=>'docente, acudiente', 'genero'=>'f', 'estado'=>'activo', 'mascota'=>'NO'];
users_check(usuarios_form_data($valid)['rol'] === 'docente,acudiente', 'Se pierden roles existentes');
try { usuarios_form_data(array_replace($valid, ['rol'=>'acudiente,superadmin'])); throw new RuntimeException('Rol no autorizado aceptado'); } catch (InvalidArgumentException $expected) {}
// Renderizar sin conexión ni datos reales permite detectar errores y escapes.
class UsersFixtureResult {
    private array $rows;
    public function __construct(array $rows) { $this->rows = $rows; }
    public function fetch_assoc() { return array_shift($this->rows); }
}
function guagua_csrf_token() { return 'token-de-prueba'; }
$_SESSION = ['id_usuario'=>'999'];
$titulo = 'Acudientes'; $listado_error = false;
$listado = ['total'=>21, 'active'=>20, 'inactive'=>1, 'page'=>2, 'pages'=>3, 'result'=>new UsersFixtureResult([array_replace($valid, ['nombre'=>'<script>alert(1)</script>', 'telefono'=>'3001234567', 'correo'=>'ana@example.test'])])];
ob_start(); require __DIR__ . '/../usuario/partials/usuarios_tabla.php'; $html = ob_get_clean();
users_check(strpos($html, '<script>') === false && strpos($html, '&lt;script&gt;') !== false, 'HTML de nombre sin escapar');
users_check(strpos($html, 'u=acudiente') !== false && strpos($html, 'guagua_embedded=1') !== false, 'Tabla pierde filtros');
users_check(strpos($html, 'aria-current="page"') !== false, 'Página activa sin identificar');
users_check(strpos($html, 'Desactivar') !== false && strpos($html, 'csrf_token') !== false, 'Cambio de estado no protegido');
$modo_edicion = true; $original = '123'; $error = 'Revise los datos'; $mensaje = '';
$datos_editar = array_fill_keys(['correo','telefono','direccion','fecha_nacimiento','fecha_retiro','tipo_sangre','observaciones'], '') + $valid;
$listado['result'] = new UsersFixtureResult([]);
ob_start(); require __DIR__ . '/../usuario/partials/usuarios_pagina.php'; $page = ob_get_clean();
users_check(strpos($page, 'name="password"') === false, 'Editar solicita cambiar contraseña');
users_check(strpos($page, 'docente, acudiente') !== false, 'Vista pierde roles múltiples');
users_check(strpos($page, 'name="id_usuario_original"') !== false, 'Edición sin identificador original');
echo "OK: contexto, filtros combinados, roles múltiples, escape HTML, edición y paginación.\n";

$docente = usuarios_directorio_policy('docente', 'acudiente');
users_check(!$docente['restricted'] && !$docente['can_delete'], 'El docente no puede gestionar usuarios o recibe eliminación permanente');
users_check(usuarios_directorio_policy('docente', '')['role'] === '', 'El directorio por defecto del docente no muestra todos los usuarios');
foreach (['admin','directivo','docente','estudiante'] as $role) {
    $policy = usuarios_directorio_policy('docente', $role);
    users_check($policy['role'] === $role && !$policy['restricted'], 'El docente no puede abrir el directorio de ' . $role);
}
foreach (['admin,acudiente', 'docente,acudiente'] as $role) {
    users_check(usuarios_directorio_target($role, $docente), 'El directorio excluye cuentas con múltiples roles');
}
foreach (['estudiante','acudiente','sinrol',''] as $actor) {
    try { usuarios_directorio_policy($actor, ''); throw new RuntimeException('Un perfil ajeno al personal puede gestionar usuarios'); } catch (InvalidArgumentException $expected) {}
}
users_check(usuarios_directorio_target('acudiente', $docente), 'El docente no puede administrar acudientes');
users_check(!usuarios_directorio_target('admin', $docente), 'El filtro de acudientes incluye otros perfiles');
users_check(usuarios_directorio_policy('admin', '')['can_delete'], 'Administrador perdió eliminación');
users_check(usuarios_directorio_policy('directivo', 'acudiente')['can_delete'], 'Directivo perdió eliminación');
$acudiente = array_replace($valid, ['rol'=>'acudiente']);
usuarios_directorio_validate($docente, 'save', null, usuarios_form_data($acudiente));
usuarios_directorio_validate($docente, 'save', null, usuarios_form_data(array_replace($acudiente, ['rol'=>'admin,acudiente'])));
usuarios_directorio_validate($docente, 'save', null, usuarios_form_data(array_replace($acudiente, ['mascota'=>'SI'])));
foreach ([['delete', null, null], ['save', ['rol'=>'admin','mascota'=>'NO'], usuarios_form_data($acudiente)], ['save', null, usuarios_form_data(array_replace($acudiente, ['rol'=>'admin']))]] as [$action,$existing,$data]) {
    try { usuarios_directorio_validate($docente, $action, $existing, $data); throw new RuntimeException('Operación docente fuera de alcance aceptada'); } catch (InvalidArgumentException $expected) {}
}
$filter = usuarios_list_filter('', 'acudiente', '', true);
users_check(strpos($filter['where'], 'LOWER(TRIM(rol)) = ?') !== false && $filter['values'] === ['acudiente'], 'El listado docente incluye cuentas de roles privilegiados');
$politica = $docente; $datos_editar = array_replace($datos_editar, ['rol'=>'acudiente', 'mascota'=>'NO']);
$listado['result'] = new UsersFixtureResult([]);
ob_start(); require __DIR__ . '/../usuario/partials/usuarios_pagina.php'; $page = ob_get_clean();
users_check(strpos($page, 'data-delete-form') === false, 'El docente ve el formulario de eliminación');
users_check(strpos($page, 'name="mascota"') !== false, 'El formulario no conserva el acceso como mascota');
users_check(strpos($page, 'aria-label="Directorios de usuarios"') !== false, 'El docente no tiene navegación entre directorios');
echo "OK: acceso docente a todos los directorios, filtros por perfil y eliminación reservada.\n";

// Administradores: contexto de navegación, formulario y protección de la sesión propia.
$contexto = usuarios_page_context(['u'=>'admin','guagua_embedded'=>'1','buscar'=>'Gestión','page'=>'2']);
$politica = usuarios_directorio_policy('docente', 'admin');
$titulo = 'Administradores'; $modo_edicion = false; $original = ''; $error = ''; $mensaje = '';
$datos_editar = array_replace($datos_editar, ['rol'=>'admin', 'id_usuario'=>'', 'nombre'=>'', 'apellido'=>'']);
$listado['result'] = new UsersFixtureResult([]);
ob_start(); require __DIR__ . '/../usuario/partials/usuarios_pagina.php'; $page = ob_get_clean();
users_check(strpos($page, '+ Nuevo administrador') !== false && strpos($page, 'Crear administrador') !== false, 'Formulario sin contexto de administrador');
users_check(strpos($page, 'name="u" value="admin"') !== false && strpos($page, 'value="admin" selected') !== false, 'El formulario pierde el rol admin');
users_check(strpos($page, 'aria-label="Directorios de usuarios"') !== false && strpos($page, 'aria-current="page">Administradores') !== false, 'Navegación administrativa sin directorio activo');
$filter = usuarios_list_filter('Gestión', 'admin', 'activo');
users_check($filter['values'][0] === 'admin' && $filter['values'][1] === 'activo', 'Listado admin pierde filtros');
$cuenta_propia = usuarios_form_data(array_replace($valid, ['id_usuario'=>'999','rol'=>'admin']));
usuarios_validar_cuenta_propia('999', 'admin', '999', $cuenta_propia);
foreach ([['id_usuario'=>'888'], ['estado'=>'inactivo'], ['rol'=>'acudiente']] as $cambio) {
    try { usuarios_validar_cuenta_propia('999', 'admin', '999', array_replace($cuenta_propia, $cambio)); throw new RuntimeException('Se permite perder el acceso de la cuenta propia'); } catch (InvalidArgumentException $expected) {}
}
usuarios_validar_cuenta_propia('999', 'admin', '123', array_replace($cuenta_propia, ['id_usuario'=>'123','estado'=>'inactivo']));
$listado['result'] = new UsersFixtureResult([array_replace($valid, ['id_usuario'=>'999', 'rol'=>'admin', 'correo'=>'','telefono'=>''])]);
ob_start(); require __DIR__ . '/../usuario/partials/usuarios_tabla.php'; $table = ob_get_clean();
users_check(strpos($table, 'Tu cuenta') !== false && strpos($table, 'data-state-form') === false, 'La cuenta propia permite cambio de estado desde la tabla');
echo "OK: directorio admin, rol inicial, navegación y protección de la cuenta propia.\n";
