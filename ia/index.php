<?php

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

// Ejemplo de uso de la función
$messages = [
    [
        "role" => "system",
        "content" => "You are a helpful coding assistant."
    ],
    [
        "role" => "user",
        "content" => "responde en español. Eres un doctor en educación, necesito que me ayudes a completar los espacios donde diga Completa IA segun esta información y ordena cada elemento en una lista

plan de clase en los aspectos que no estan completos.
Ten en cuenta para completar los elementos los aspectos presentes y que el modelo de la institución educativa es el modelo dialogico,
de igual forma quiero que seas muy detallado y profesional en cada momento de clase.
  
1) Grado: 6
2) Periodo: 3 
3) Nombre materia: TECNOLOGIA(6-7) 
4) DBA (Derecho básico de aprendizaje): •	Utiliza las tecnologías de la información y la comunicación, para apoyar los procesos de aprendizaje y actividades personales (recolectar, seleccionar, organizar y procesar información).
•	Detecta fallas en artefactos, procesos y sistemas tecnológicos,  
5) Nombre_estandar: •	Relaciona el funcionamiento de algunos artefactos, productos, procesos y sistemas tecnológicos con su utilización segura.
•	Propone estrategias para soluciones tecnológicas a problemas en diferentes contextos.
•	Relaciona la transformación de los recurs 
6) Eje tematico: •	Manteni¬miento y personali¬zación de Windows.
	Formatear y copiar un disco.
	El Scandisk.
	El Desfragmentador de disco.
	Información del sistema.
	Liberador de espacio en disco.
	Papelera de reciclaje.
	Creación de accesos directos.
	Configurar  
7) Objetivo de Clase:  Completa IA 
8) Nombre corto de la Estrategia de clase: Completa IA  
9) Momentos de la clase: Completa IA (Inicio,Desarrollo,Valoración,Cierre) 
10) Recursos: Completa IA 
11) Reflexión pedagogica: Completa IA ,
12) Estrategia pedagogica: Completa IA  "
    ]
];

$response = call_lm_studio_api($messages);
echo $response;

?>
