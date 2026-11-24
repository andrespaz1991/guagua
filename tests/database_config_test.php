<?php
require_once __DIR__ . '/../comun/database.php';
function verify_db($condition, $message) { if (!$condition) throw new RuntimeException($message); }
verify_db(session_status() === PHP_SESSION_NONE, 'El proveedor inició una sesión.');
$settings = GuaguaDatabase::settings();
verify_db(GuaguaDatabase::settings('edutask')['host'] === $settings['host'], 'El perfil no comparte servidor.');
verify_db(GuaguaDatabase::settings('edutask')['database'] !== $settings['database'], 'El perfil perdió su esquema.');
verify_db(str_contains(GuaguaDatabase::dsn(), ';port=' . $settings['port']), 'PDO omitió el puerto.');
verify_db(GuaguaDatabase::settings('default', ['host'=>'[::1]:7001'])['port'] === 7001, 'Puerto IPv6 incorrecto.');
verify_db(GuaguaDatabase::settings('default', ['host'=>'localhost:7001', 'port'=>7002])['port'] === 7002, 'Puerto explícito ignorado.');
$previous = getenv('GUAGUA_DB_PASSWORD');
try {
    putenv('GUAGUA_DB_PASSWORD=');
    verify_db(GuaguaDatabase::settings()['password'] === '', 'Contraseña vacía ignorada.');
} finally { $previous === false ? putenv('GUAGUA_DB_PASSWORD') : putenv('GUAGUA_DB_PASSWORD=' . $previous); }
foreach ([['port'=>0], ['host'=>'localhost;dbname=other'], ['time_zone'=>'+14:30'], ['timezone'=>'invalid']] as $invalid) {
    try { GuaguaDatabase::settings('default', $invalid); throw new RuntimeException('Se aceptó configuración inválida.'); }
    catch (InvalidArgumentException $expected) {}
}
echo "OK: configuración, perfiles, puerto, validación y proveedor sin sesión.\n";
