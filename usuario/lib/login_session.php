<?php
require_once __DIR__ . '/../../comun/seguridad.php';
require_once __DIR__ . '/../../comun/sesion_contexto.php';

function login_cerrar_sesion(): void
{
    guagua_iniciar_sesion_segura();
    $_SESSION = [];
    session_regenerate_id(true);
}

function login_crear_sesion(mysqli $mysqli, array $row, string $rol_solicitado, int $institucion): void
{
    $contexto = guagua_contexto_sesion($mysqli, $row, $rol_solicitado, $institucion);
    $hoy = date('Y-m-d H:i:s');
    guagua_sql($mysqli, "UPDATE usuario SET num_visitas = COALESCE(NULLIF(num_visitas, ''), 0) + 1,
        puntos = COALESCE(NULLIF(puntos, ''), 0) + 1, ultima_sesion = ? WHERE id_usuario = ?", [$hoy, (string)$row['id_usuario']]);
    guagua_iniciar_sesion_segura();
    session_regenerate_id(true);
    // No heredar selección de hijos, cursos, tokens ni preferencias de otra cuenta.
    $_SESSION = $contexto;
    $_SESSION['num_visitas']++;
    $_SESSION['puntos']++;
}
