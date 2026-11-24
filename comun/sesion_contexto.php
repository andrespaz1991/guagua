<?php
require_once __DIR__ . '/sql.php';
require_once __DIR__ . '/academico.php';

/** El orden de los roles procede de la cuenta, sin privilegios de respaldo. */
function guagua_roles_usuario(array $usuario): array
{
    return array_values(array_unique(array_filter(array_map(
        static fn($rol) => strtolower(trim($rol)), explode(',', (string)($usuario['rol'] ?? ''))
    ))));
}

function guagua_contexto_sesion(mysqli $db, array $usuario, string $rol, int $institucion): array
{
    if (empty($usuario['id_usuario']) || strtolower(trim((string)($usuario['estado'] ?? ''))) !== 'activo') {
        throw new InvalidArgumentException('La cuenta no está activa.');
    }
    $roles = guagua_roles_usuario($usuario);
    if (!$roles) throw new InvalidArgumentException('La cuenta no tiene un rol configurado.');
    $rol = strtolower(trim($rol));
    if ($rol === '') $rol = $roles[0];
    if (!in_array($rol, $roles, true)) throw new InvalidArgumentException('El perfil seleccionado no pertenece a esta cuenta.');
    $opciones = guagua_sql($db, 'SELECT * FROM institucion_educativa WHERE id_institucion_educativa = ? LIMIT 1', [$institucion], 'i')->fetch_assoc();
    if ($institucion <= 0 || !$opciones) throw new InvalidArgumentException('Seleccione una institución válida.');
    $ano = guagua_ano_activo($db);
    $periodo = guagua_periodo_activo($db);
    return [
        'id_usuario' => (string)$usuario['id_usuario'],
        'identificacion_usu' => (string)$usuario['id_usuario'],
        'id_usu' => (string)$usuario['id_usuario'],
        'usuario' => (string)$usuario['usuario'],
        'nombre' => (string)($usuario['nombre'] ?? ''),
        'apellido' => (string)($usuario['apellido'] ?? ''),
        'nombre_usu' => trim(($usuario['nombre'] ?? '') . ' ' . ($usuario['apellido'] ?? '')),
        'foto' => (string)($usuario['foto'] ?? ''),
        'rol' => $rol,
        'tipo' => $rol,
        'roles' => implode(',', $roles),
        'id_institucion' => (int)$opciones['id_institucion_educativa'],
        'institucion' => (int)$opciones['id_institucion_educativa'],
        'configuracion_institucion' => $opciones,
        'plantilla' => $opciones['plantilla'] ?? null,
        'formatos_no_permitidos' => $opciones['formatos_no_permitidos'] ?? null,
        'tamano_maximo_adjunto' => $opciones['tamano_maximo_adjunto'] ?? null,
        'autoregistrarse' => $opciones['autoregistrarse'] ?? null,
        'id_ano_lectivo' => isset($ano['id_ano_lectivo']) ? (int)$ano['id_ano_lectivo'] : null,
        'ano_lectivo' => $ano['nombre_ano_lectivo'] ?? null,
        'id_periodo' => isset($periodo['id_periodo']) ? (int)$periodo['id_periodo'] : null,
        'periodo' => $periodo['nombre_periodo'] ?? null,
        'fecha_inicio_periodo' => $periodo['fecha_inicio'] ?? null,
        'fecha_fin_periodo' => $periodo['fecha_fin'] ?? null,
        'num_visitas' => (int)($usuario['num_visitas'] ?? 0),
        'puntos' => (int)($usuario['puntos'] ?? 0),
    ];
}

/** Actualiza las opciones guardadas en cada petición; nunca autentica anónimos. */
function guagua_refrescar_contexto_sesion(mysqli $db): void
{
    if (empty($_SESSION['id_usuario'])) return;
    $usuario = guagua_sql($db, 'SELECT id_usuario, usuario, nombre, apellido, foto, rol, estado, num_visitas, puntos FROM usuario WHERE id_usuario = ? LIMIT 1', [(string)$_SESSION['id_usuario']])->fetch_assoc();
    try {
        $contexto = guagua_contexto_sesion($db, $usuario ?: [], (string)($_SESSION['rol'] ?? ''), (int)($_SESSION['id_institucion'] ?? $_SESSION['institucion'] ?? 0));
        $_SESSION = array_replace($_SESSION, $contexto);
    } catch (InvalidArgumentException $e) {
        // No conservar una identidad desactivada, un rol retirado o una sede inexistente.
        $_SESSION = [];
    }
}
