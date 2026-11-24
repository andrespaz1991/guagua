<?php
require_once __DIR__ . '/../comun/seguridad.php';
guagua_exigir_rol(['admin', 'directivo', 'docente']);
header('Cache-Control: private, no-store');
require_once __DIR__ . '/../comun/conexion.php';
require_once __DIR__ . '/lib/usuarios_repository.php';
require_once __DIR__ . '/lib/usuarios_page.php';

$contexto = usuarios_page_context($_GET);
try {
    $politica = usuarios_directorio_policy((string)$_SESSION['rol'], $contexto['u']);
} catch (InvalidArgumentException $exception) {
    guagua_error_acceso(403, $exception->getMessage());
}
$contexto['u'] = $politica['role'];
$titulo = usuarios_roles()[$contexto['u']] ?? 'Usuarios';
$error = ''; $mensaje = '';
$modo_edicion = false;
$original = '';
$datos_editar = array_fill_keys(['id_usuario','usuario','nombre','apellido','correo','direccion','telefono','tipo_sangre','fecha_nacimiento','fecha_retiro','observaciones'], '');
$datos_editar += ['tipo_documento'=>'CC', 'rol'=>$contexto['u'] ?: 'estudiante', 'genero'=>'f', 'estado'=>'activo', 'mascota'=>'NO'];

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    guagua_exigir_csrf();
    $accion = usuarios_text($_POST, 'action');
    $original = usuarios_text($_POST, 'id_usuario_original');
    $modo_edicion = $accion === 'save' && $original !== '';
    try {
        if (!in_array($accion, ['save','set_state','delete'], true)) throw new InvalidArgumentException('Operación no válida.');
        usuarios_directorio_validate($politica, $accion);
        $existente = null;
        $objetivo = in_array($accion, ['set_state','delete'], true) ? usuarios_text($_POST, 'id') : $original;
        if ($objetivo !== '') {
            $existente = usuarios_query($mysqli, 'SELECT id_usuario, rol, mascota FROM usuario WHERE id_usuario = ? LIMIT 1', [$objetivo])->fetch_assoc();
            if (!$existente) throw new InvalidArgumentException('El usuario no está disponible en este directorio.');
            usuarios_directorio_validate($politica, $accion, $existente);
        }
        if ($accion === 'delete') {
            if ($objetivo === '' || $objetivo === (string)$_SESSION['id_usuario']) throw new InvalidArgumentException('No puede eliminar su propia cuenta.');
            usuarios_query($mysqli, 'DELETE FROM usuario WHERE id_usuario = ?', [$objetivo]);
            $mensaje = 'El perfil fue eliminado.';
        } elseif ($accion === 'set_state') {
            $estado = usuarios_text($_POST, 'estado');
            if ($objetivo === '' || !in_array($estado, ['activo','inactivo'], true)) throw new InvalidArgumentException('Estado no válido.');
            if ($objetivo === (string)$_SESSION['id_usuario']) throw new InvalidArgumentException('No puede cambiar el estado de su propia cuenta desde aquí.');
            usuarios_query($mysqli, 'UPDATE usuario SET estado = ? WHERE id_usuario = ?' . ($politica['restricted'] ? " AND LOWER(TRIM(rol)) = 'acudiente'" : ''), [$estado, $objetivo]);
            $mensaje = $estado === 'activo' ? 'La cuenta fue activada.' : 'La cuenta fue desactivada. Sus datos y vínculos se conservan.';
        } else {
            $data = usuarios_form_data($_POST);
            usuarios_directorio_validate($politica, $accion, $existente, $data);
            usuarios_validar_cuenta_propia((string)$_SESSION['id_usuario'], (string)$_SESSION['rol'], $original, $data);
            usuarios_save($mysqli, $_POST, $original !== '' ? $original : null, $politica['restricted'] ? 'acudiente' : null);
            $mensaje = $original !== '' ? 'Los cambios fueron guardados.' : 'El perfil fue creado correctamente.';
        }
        $_SESSION['usuarios_flash'] = $mensaje;
        header('Location: ' . usuarios_page_url($contexto), true, 303);
        exit;
    } catch (InvalidArgumentException $exception) {
        $error = $exception->getMessage();
    } catch (Throwable $exception) {
        error_log('[Guagua usuarios] ' . get_class($exception) . ' (' . $exception->getCode() . ')');
        $error = (int)$exception->getCode() === 1062 ? 'El documento o el nombre de usuario ya está registrado.' : 'No se pudo guardar. Revise los datos e inténtelo de nuevo.';
    }
    if ($accion === 'save') {
        foreach ($datos_editar as $campo => $valor) {
            if (isset($_POST[$campo]) && is_string($_POST[$campo])) $datos_editar[$campo] = $_POST[$campo];
        }
    }
} elseif (usuarios_text($_GET, 'Actualizar') !== '') {
    $original = usuarios_text($_GET, 'Actualizar');
    $registro = usuarios_query($mysqli, 'SELECT id_usuario, tipo_documento, usuario, nombre, apellido, rol, genero, estado, correo, direccion, telefono, tipo_sangre, mascota, fecha_nacimiento, fecha_retiro, observaciones FROM usuario WHERE id_usuario = ? LIMIT 1', [$original])->fetch_assoc();
    if ($registro && usuarios_directorio_target((string)$registro['rol'], $politica)) {
        $datos_editar = array_merge($datos_editar, $registro);
        $modo_edicion = true;
    } else {
        http_response_code(404);
        $error = 'El perfil solicitado no existe en este directorio.';
        $original = '';
    }
}
if (isset($_SESSION['usuarios_flash'])) {
    $mensaje = (string)$_SESSION['usuarios_flash'];
    unset($_SESSION['usuarios_flash']);
}
$listado_error = false;
try {
    $listado = usuarios_list($mysqli, $contexto['buscar'], $contexto['page'], $contexto['u'], $contexto['estado_filtro'], $politica['restricted']);
    $contexto['page'] = $listado['page'];
} catch (Throwable $exception) {
    error_log('[Guagua directorio] ' . get_class($exception) . ' (' . $exception->getCode() . ')');
    $listado_error = true;
    if (isset($_GET['ajax_search'])) http_response_code(500);
}
if (isset($_GET['ajax_search'])) {
    header('Content-Type: text/html; charset=UTF-8');
    header('X-Guagua-Fragment: usuarios');
    require __DIR__ . '/partials/usuarios_tabla.php';
    exit;
}
require __DIR__ . '/partials/usuarios_pagina.php';
