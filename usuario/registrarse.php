<?php
// Alias del registro antiguo: el CRUD central valida roles, contraseñas y CSRF.
require_once __DIR__ . '/../comun/seguridad.php';
guagua_exigir_rol(['admin','directivo']);
header('Location: /guagua/usuario/usuario.php', true, 303);
exit;
