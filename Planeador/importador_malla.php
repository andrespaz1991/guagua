<?php
function cleanString($string) {
    return mb_convert_encoding($string, 'UTF-8', 'auto'); // Convertir a UTF-8
}
function importDataFromCSV($filePath, $mysqli) {
    require 'conexion.php';

    if (($handle = fopen($filePath, "r")) !== FALSE) {
        fgetcsv($handle, 1000, ";"); // Omitir la primera fila si son encabezados

        while (($data = fgetcsv($handle, 1000, ";")) !== FALSE) {
            $estandar =cleanString($data[0]);
            $grado = $data[1];
            $periodo = $data[2];
            $id_materia_oficial = $data[3];
            $nombre_dba = $data[4];
            $evidencia_aprendizaje = $data[5];
            $eje_tematico = $data[6];

            // 1. Insertar en 'estandar'
          

            $sql = "INSERT INTO `estandar` (`nombre_estandar`, `descripcion_estandar`, `grado`, `id_periodo`, `id_materia_oficial`) 
                    VALUES ('$estandar','$estandar', '$grado','$periodo','$id_materia_oficial')";
            
            if ($mysqli->query($sql)) {
                $id_estandar = $mysqli->insert_id;
            } else {
                echo "Error en estándar: " . $mysqli->error . "<br>";
                continue;
            }

            // 2. Insertar en 'dba'
            $sql2 = "INSERT INTO `dba` (nombre_dba, id_estandar) VALUES ('$nombre_dba', $id_estandar)";
            if ($mysqli->query($sql2)) {
                $id_dba = $mysqli->insert_id;
            } else {
                echo "Error en DBA: " . $mysqli->error . "<br>";
                continue;
            }

            // 3. Insertar en 'evidencia_de_aprendizaje'
            $sql3 = "INSERT INTO `evidencia_de_aprendizaje` (descripcion_evidencia, id_dba) 
                     VALUES ('$evidencia_aprendizaje', $id_dba)";
            if (!$mysqli->query($sql3)) {
                echo "Error en evidencia de aprendizaje: " . $mysqli->error . "<br>";
                continue;
            }

            // 4. Insertar en 'eje_tematico'
            $sql4 = "INSERT INTO `eje_tematico` (nombre_eje_tematico, descripcion_eje_tematico, id_dba) 
                     VALUES ('$eje_tematico', '$eje_tematico', $id_dba)";
            if (!$mysqli->query($sql4)) {
                echo "Error en eje temático: " . $mysqli->error . "<br>";
                continue;
            }

            echo "Fila insertada correctamente: $estandar, $nombre_dba, $eje_tematico<br>";
        }
        fclose($handle);
    } else {
        echo "No se pudo abrir el archivo.";
    }
}

// Datos de conexión
error_reporting(E_ALL);
$rutaPrincipal = $_SERVER['DOCUMENT_ROOT'].'/guagua/comun/';
require_once ($rutaPrincipal."/funciones.php");
require_once ($rutaPrincipal."/config.php");
$mysqli = new mysqli(SERVIDORBD, USUARIOBD, CLAVEBD, BASEDEDATOS);

if ($mysqli->connect_error) {
    die("Error de conexión: " . $mysqli->connect_error);
}

importDataFromCSV('archivo.csv', $mysqli);
?>
