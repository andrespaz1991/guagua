<?php
// Ruta de la carpeta donde están los .txt (puedes modificarla)
$directorio = "C:\Windows\System32\output\parrafo"; // ejemplo: "./textos"
$archivoSalida = $directorio . DIRECTORY_SEPARATOR . "unido.txt"; // nombre del archivo de salida

// Abrimos el archivo de salida en modo escritura (se sobrescribe si existe)
$salida = fopen($archivoSalida, "w");

if (!$salida) {
    die("No se pudo crear el archivo de salida en: " . $archivoSalida);
}

// Abrimos el directorio
if (is_dir($directorio)) {
    if ($gestor = opendir($directorio)) {
        while (($archivo = readdir($gestor)) !== false) {
            // Filtramos solo archivos .txt
            if (pathinfo($archivo, PATHINFO_EXTENSION) === 'txt') {
                $rutaArchivo = $directorio . DIRECTORY_SEPARATOR . $archivo;
                
                // Leemos el contenido del archivo y lo escribimos en el archivo final
                $contenido = file_get_contents($rutaArchivo);
                fwrite($salida, "----- Inicio de $archivo -----\n");
                fwrite($salida, $contenido . "\n");
                fwrite($salida, "----- Fin de $archivo -----\n\n");
            }
        }
        closedir($gestor);
    } else {
        echo "No se pudo abrir el directorio.";
    }
} else {
    echo "La ruta especificada no es un directorio válido.";
}

fclose($salida);

echo "Archivos .txt combinados correctamente en " . $archivoSalida;
?>