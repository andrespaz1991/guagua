<?php
/** Private bridge for the Python tools. Never available through HTTP. */
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require_once __DIR__ . '/database.php';
try {
    $profile = $argv[1] ?? 'default';
    $settings = GuaguaDatabase::settings($profile);
    if ($settings['host'] === '' || $settings['user'] === '') throw new RuntimeException('Complete la configuración del perfil de base de datos en .env.');
    // Consumed through a private subprocess pipe; callers must never log this payload.
    echo json_encode($settings, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
} catch (Throwable $error) {
    fwrite(STDERR, "No se pudo leer la configuración central de la base de datos.\n");
    exit(1);
}
