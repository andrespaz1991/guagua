
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subir Archivo CSV</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f2f2f2;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .container {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            width: 400px;
            text-align: center;
        }
        h1 {
            margin-bottom: 20px;
            color: #333;
        }
        input[type="file"] {
            margin-bottom: 20px;
        }
        button {
            background-color: #4CAF50;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }
        button:hover {
            background-color: #45a049;
        }
        .message {
            margin-top: 20px;
            color: #333;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Subir Archivo CSV</h1>
        <form action="" method="post" enctype="multipart/form-data">
            <input type="file" name="fileToUpload" id="fileToUpload" required>
            <button type="submit" name="submit">Subir Archivo</button>
        </form>
        <?php
        if (isset($_GET['message'])) {
            echo '<div class="message">' . htmlspecialchars($_GET['message']) . '</div>';
        }
        ?>
    </div>
</body>
</html>

<?php
function importDataFromCSV($filePath, $mysqli) {
    // Abrir el archivo CSV
    if (($handle = fopen($filePath, "r")) !== FALSE) {
        // Omitir la primera fila (encabezados)
        fgetcsv($handle, 1000, ",");

        // Leer cada fila del archivo CSV
        while (($data = fgetcsv($handle, 1000, ";")) !== FALSE) {
        
            $estandar = $data[0];
            $grado = $data[1];
            $periodo = $data[2];
            $id_materia_oficial = $data[3];
            $nombre_dba = $data[4];
            $evidencia_aprendizaje = $data[5];
            $eje_tematico = $data[6];

            // Inserción en la tabla 'estándar'
            $sql = "INSERT INTO `estándar` (nombre_estandar, `descripcion_estandar`, `grado`, `id_periodo`, `id_materia_oficial`) VALUES ('$estandar','$estandar', '$grado','$periodo','$id_materia_oficial')";
            echo $sql.'<br>';
            if ($mysqli->query($sql) === TRUE) {
                $id_estandar = $mysqli->insert_id;
            } else {
                #echo "Error: " . $sql . "<br>" . $mysqli->error;
                continue;
            }

            // Inserción en la tabla 'dba'
            $sql = "INSERT INTO `dba` (nombre_dba, id_estandar) VALUES ('$nombre_dba', $id_estandar)";
            echo $sql.'<br>';
            if ($mysqli->query($sql) === TRUE) {
                $id_dba = $mysqli->insert_id;
            } else {
               # echo "Error: " . $sql . "<br>" . $mysqli->error;
                continue;
            }

            // Inserción en la tabla 'evidencia_de_aprendizaje'
            $sql = "INSERT INTO `evidencia_de_aprendizaje` (descripcion_evidencia, id_dba) VALUES ('$evidencia_aprendizaje', $id_dba)";
            echo $sql.'<br>';
            if ($mysqli->query($sql) !== TRUE) {
               # echo "Error: " . $sql . "<br>" . $mysqli->error;
                continue;
            }

            // Inserción en la tabla 'eje_tematico'
            $sql = "INSERT INTO `eje_tematico` (nombre_eje_tematico, descripcion_eje_tematico,id_dba) VALUES ('$eje_tematico','$eje_tematico',  $id_dba)";
            echo $sql.'<br>';
            if ($mysqli->query($sql) !== TRUE) {
               # echo "Error: " . $sql . "<br>" . $mysqli->error;
                continue;
            }
        }

        // Cerrar el archivo CSV
        fclose($handle);
    } else {
        echo "No se pudo abrir el archivo.";
    }
}

// Datos de conexión
$host = 'localhost';
$db = 'guagua';
$user = 'root';
$pass = '';

$mysqli = new mysqli($host, $user, $pass, $db);
if ($mysqli->connect_error) {
    die("Error de conexión: " . $mysqli->connect_error);
}

importDataFromCSV('archivo.csv', $mysqli);

$mysqli->close();
?>
