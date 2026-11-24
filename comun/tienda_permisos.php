<?php
/** El personal que administra los módulos puede gestionar su instalación. */
function guagua_tienda_roles_gestion(): array
{
    return ['admin', 'directivo', 'docente'];
}

function guagua_tienda_puede_gestionar(string $role): bool
{
    return in_array($role, guagua_tienda_roles_gestion(), true);
}
