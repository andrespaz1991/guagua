

<?php
require 'index.php';
require '../comun/conexion.php';
/**
 * Valida nombres de tabla y campo (solo letras, números y guiones bajos).
 */
function isValidName($name) {
    return preg_match('/^[A-Za-z0-9_]+$/', $name);
}

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $idfield=$_POST['idfield'] ?? '';
    $table  = $_POST['table'] ?? '';
    $field  = $_POST['field'] ?? '';
    $apiKey = $_POST['api_key'] ?? '';

    if (!isValidName($table) || !isValidName($field) || empty($apiKey)) {
        $message = 'Nombre de tabla/campo inválido o falta API Key.';
    } else {
        // Conexión a la base de datos
            // Asumimos que la tabla tiene un campo 'id' como clave primaria
        $sql = "SELECT `$idfield` as id, `$field` FROM `$table`";
        $result = $mysqli->query($sql);
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                print_r($row);
                $id   = (int)$row['id'];
                $text = $row[$field];
                // Llamada a Gemini
                $prompt   = "Corrige caracteres dañados en este texto: " . $text. ' y deuvelve unicamente el texto corregido ';
                $response = gemini($apiKey, $prompt);
                // Procesar respuesta JSON
                $data = json_decode($response, true);
                $corrected = $data['candidates'][0]['output'] ?? '';
                if (!empty($corrected)) {
                    $clean = $mysqli->real_escape_string($corrected);
                    $updateSql = "UPDATE `$table` SET `$field` = '$clean' WHERE id = $id";
                    echo $updateSql.'<br>';
                    $mysqli->query($updateSql);
                }
            }
            $message = 'Proceso completado: registros actualizados.';
        } else {
            $message = 'No se encontraron registros o error en la consulta.';
        }
        $mysqli->close();
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Módulo Gemini Corrección</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
</head>
<body>
<div class="container mt-5">
    <h1 class="mb-4">Corrección de texto con Gemini</h1>
    <?php if (!empty($message)): ?>
        <div class="alert alert-info"><?php echo $message; ?></div>
    <?php endif; ?>
    <form method="post">
        <div class="form-group">
            <label for="api_key">API Key</label>
            <input value='AIzaSyB1ZbitpmioDkWOPWOlHJ-p_SORmxUYUrM' type="text" class="form-control" id="api_key" name="api_key" required>
        </div>
        <div class="form-group">
            <label for="table">id campo</label>
            <input type="text" class="form-control" id="table" name="idfield" placeholder="id_campo" required>
        </div>
        <div class="form-group">
            <label for="table">Nombre de la tabla</label>
            <input type="text" class="form-control" id="table" name="table" placeholder="e.g., mi_tabla" required>
        </div>
        <div class="form-group">
            <label for="field">Nombre del campo</label>
            <input type="text" class="form-control" id="field" name="field" placeholder="e.g., mi_campo_texto" required>
        </div>
        <button type="submit" class="btn btn-primary">Iniciar Corrección</button>
    </form>
</div>
<script src="https://cdn.jsdelivr.net/npm/jquery@3.5.1/dist/jquery.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
