<?php
$archivo = "https://mailudesedu-my.sharepoint.com/:x:/g/personal/01240100237_mail_udes_edu_co/EZssonNSg8FLo3kmtAptqygBdgZ1SjBQ2u4ed2ErARl-yA?e=uC2s2l";

// Escapando y normalizando la ruta
$ruta_normalizada = realpath($archivo);

// Verificar si la ruta es válida
if ($ruta_normalizada !== false) {
    $archivo = $ruta_normalizada;
    echo "Ruta normalizada: " . $archivo . "\n";
    procesar_notas($archivo);
} else {
    echo "La ruta al archivo no es válida.";
}

function procesar_notas($archivo) {
    // ... resto del código
}

?>