<?php
/** Cliente del servidor local de LM Studio. No depende de CORS del navegador. */
function planmind_lmstudio_url(string $endpoint, string $operation): string
{
    $parts = parse_url(trim($endpoint));
    $host = strtolower($parts['host'] ?? '');
    if (!is_array($parts) || ($parts['scheme'] ?? '') !== 'http'
        || !in_array($host, ['localhost', '127.0.0.1', '[::1]'], true)
        || isset($parts['user']) || isset($parts['pass'])
        || isset($parts['query']) || isset($parts['fragment'])
        || !preg_match('#^/v1(?:/(?:chat/completions|models))?/?$#', $parts['path'] ?? '')
        || !in_array($operation, ['models', 'chat'], true)) {
        throw new InvalidArgumentException('Usa una URL local de LM Studio, por ejemplo http://localhost:1234/v1/chat/completions.');
    }

    // Evita resolver localhost como IPv6 cuando LM Studio escucha solo en IPv4.
    $host = $host === 'localhost' ? '127.0.0.1' : $host;
    return 'http://' . $host . ':' . ($parts['port'] ?? 80)
        . ($operation === 'models' ? '/v1/models' : '/v1/chat/completions');
}

function planmind_lmstudio_request(string $endpoint, string $operation, array $payload = []): array
{
    $url = planmind_lmstudio_url($endpoint, $operation);
    if (!function_exists('curl_init')) {
        throw new RuntimeException('PHP necesita la extensión cURL para conectar con LM Studio.');
    }
    if ($operation === 'chat' && (empty($payload['model']) || empty($payload['messages'])
        || !is_string($payload['model']) || !is_array($payload['messages']))) {
        throw new InvalidArgumentException('Selecciona un modelo de LM Studio y envía los mensajes.');
    }

    $curl = curl_init($url);
    $options = [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CONNECTTIMEOUT => 5,
        CURLOPT_TIMEOUT => $operation === 'models' ? 15 : 300,
        CURLOPT_FOLLOWLOCATION => false,
        CURLOPT_PROXY => '',
        CURLOPT_HTTPHEADER => ['Content-Type: application/json', 'Accept: application/json'],
    ];
    if ($operation === 'chat') {
        $options[CURLOPT_POST] = true;
        // Mantener la respuesta completa para el parser de JSON del planeador.
        $payload['stream'] = false;
        $options[CURLOPT_POSTFIELDS] = json_encode($payload, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
    }
    curl_setopt_array($curl, $options);
    $body = curl_exec($curl);
    $error = curl_errno($curl);
    $status = (int)curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);

    if ($body === false) {
        if ($error === CURLE_OPERATION_TIMEDOUT) {
            throw new RuntimeException('LM Studio tardó demasiado en responder. Comprueba el modelo cargado e inténtalo de nuevo.');
        }
        throw new RuntimeException('No se pudo conectar con LM Studio en ' . $endpoint
            . '. Inicia su servidor en Developer y comprueba el puerto configurado.');
    }
    $data = json_decode($body, true);
    if ($status < 200 || $status >= 300 || (is_array($data) && isset($data['error']))) {
        $detail = is_array($data) ? ($data['error'] ?? '') : '';
        if (is_array($detail)) $detail = $detail['message'] ?? '';
        throw new RuntimeException('LM Studio (HTTP ' . $status . '): '
            . (is_string($detail) && $detail !== '' ? $detail : 'La solicitud fue rechazada. Comprueba el modelo y la configuración del servidor.'));
    }
    if (!is_array($data)) {
        throw new RuntimeException('LM Studio no devolvió una respuesta JSON válida. Comprueba la URL configurada.');
    }
    return $data;
}
