<?php
/** Valores predeterminados y perfiles; la configuración editable privada está en /.env. */
return [
    // auto: localhost/CLI usa local; el resto usa production.
    // En servidores puede fijarse GUAGUA_APP_ENV=production.
    'environment' => 'auto',
    'local' => [
        'host' => 'localhost',
        'port' => 7000,
        'user' => 'root',
        'password' => '',
        'database' => 'guagua',
    ],
    'production' => [
        // Configure el servidor en /.env o use las variables GUAGUA_DB_*.
        'host' => '',
        'port' => 3306,
        'user' => '',
        'password' => '',
        'database' => 'guagua',
    ],
    // Estas aplicaciones comparten servidor y credenciales, pero no sus tablas.
    'profiles' => [
        'edutask' => ['database' => 'edutask_db'],
        'prompt' => ['database' => 'prompt'],
        'moodle' => ['database' => 'moodle'],
        'hosting' => ['environment' => 'production'],
    ],
    'charset' => 'utf8mb4',
    'timezone' => 'America/Bogota',
    'time_zone' => '-05:00',
    'connect_timeout' => 5,
];
