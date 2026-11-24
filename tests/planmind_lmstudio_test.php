<?php
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require_once __DIR__ . '/../apps/PlanMind/lib/lmstudio.php';

function checkLmStudio(bool $valid, string $message): void
{
    if (!$valid) throw new RuntimeException($message);
}

checkLmStudio(planmind_lmstudio_url('http://localhost:1234/v1/chat/completions', 'models')
    === 'http://127.0.0.1:1234/v1/models', 'Consultar modelos desde la URL de chat.');
checkLmStudio(planmind_lmstudio_url('http://127.0.0.1:4321/v1/', 'chat')
    === 'http://127.0.0.1:4321/v1/chat/completions', 'Conservar puerto personalizado.');
checkLmStudio(planmind_lmstudio_url('http://[::1]:1234/v1/models', 'chat')
    === 'http://[::1]:1234/v1/chat/completions', 'Aceptar loopback IPv6.');

foreach ([
    'http://example.com:1234/v1/chat/completions',
    'http://127.0.0.1.example.com/v1/models',
    'http://localhost@evil.example/v1/models',
    'http://user:password@localhost:1234/v1/models',
    'file:///etc/passwd',
    'http://localhost:1234/other-service',
    'http://localhost:1234/v1/models?redirect=http://example.com',
    'http://localhost:1234/v1/models#fragment',
    'http://localhost:99999/v1/models',
] as $url) {
    try {
        planmind_lmstudio_url($url, 'models');
        throw new LogicException('Se aceptó una URL no permitida: ' . $url);
    } catch (InvalidArgumentException $expected) {}
}
try {
    planmind_lmstudio_request('http://localhost:1234/v1/chat/completions', 'chat', []);
    throw new LogicException('Se aceptó una generación sin modelo ni mensajes.');
} catch (InvalidArgumentException $expected) {}

echo "OK: URL local, puertos, IPv6, rechazo de destinos/rutas inválidos y validación de generación.\n";
