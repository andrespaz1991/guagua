<?php
require_once __DIR__ . '/../comun/seguridad.php';
guagua_exigir_rol(['admin', 'directivo', 'docente']);
require_once __DIR__ . '/lib/usuarios_page.php';

// Conservar el directorio y el modo integrado en los enlaces antiguos a Usuarios.
header('Location: ' . usuarios_page_url(usuarios_page_context($_GET)), true, 302);
exit;
