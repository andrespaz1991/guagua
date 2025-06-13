<?php

function gemini($apiKey, $prompt) {
    $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key=" . urlencode($apiKey);

    $data = [
        "contents" => [
            [
                "parts" => [
                    [
                        "text" => $prompt
                    ]
                ]
            ]
        ]
    ];

    $jsonData = json_encode($data);

    $ch = curl_init($url);

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json'
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);

    $response = curl_exec($ch);

    if (curl_errno($ch)) {
        return 'Curl error: ' . curl_error($ch);
    }

    curl_close($ch);

    return $response;
}

// Ejemplo de uso:


function call_lm_studio_api($messages, $temperature = 0.7, $max_tokens = -1, $stream = true) {
    // URL de la API de LM Studio
    $url = 'http://localhost:1234/v1/chat/completions';

    // Datos para enviar en la solicitud
    $data = [
        'messages' => $messages,
        'temperature' => $temperature,
        'max_tokens' => $max_tokens,
        'stream' => $stream,
    ];

    // Inicializar curl
    $ch = curl_init($url);

    // Configurar las opciones de curl
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_WRITEFUNCTION, function($curl, $data) use (&$response) {
        // Procesar cada "chunk" y acumular el contenido en $response
        $lines = explode("\n", trim($data));
        foreach ($lines as $line) {
            if (strpos($line, "data: ") === 0) {
                $chunk = json_decode(substr($line, strlen("data: ")), true);
                if (isset($chunk['choices'][0]['delta']['content'])) {
                    $response .= $chunk['choices'][0]['delta']['content'];
                }
            }
        }
        return strlen($data);
    });

    // Ejecutar la solicitud
    curl_exec($ch);

    // Manejo de errores
    if (curl_errno($ch)) {
        echo 'Error:' . curl_error($ch);
    }

    // Cerrar la sesión de curl
    curl_close($ch);

    // Devolver la respuesta completa
    return $response;
}

// Ejemplo de uso de la función#
#$prompt = 'Creame un ensayo de una pagina sobre el amor';
#$api='AIzaSyB1ZbitpmioDkWOPWOlHJ-p_SORmxUYUrM';
#$response = gemini($api, $prompt);
#echo $response;

?>
