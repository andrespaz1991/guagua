<?php
ini_set('display_errors', '0');
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../../comun/conexion.php';
guagua_exigir_rol(guagua_roles_gestion_curricular());
guagua_exigir_csrf();

function importar_responder(array $data, int $status = 200): void
{
    http_response_code($status);
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function importar_texto($value): string
{
    if (!is_scalar($value)) {
        return '';
    }
    return trim(str_replace("\0", '', (string) $value));
}

function importar_cabecera(string $value): string
{
    $value = preg_replace('/^\xEF\xBB\xBF/', '', $value);
    $value = function_exists('mb_strtolower') ? mb_strtolower($value, 'UTF-8') : strtolower($value);
    $value = strtr($value, ['á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u', 'ñ' => 'n']);
    return preg_replace('/[^a-z0-9]+/', '_', trim($value));
}

function importar_valor(array $row, array $keys): string
{
    foreach ($keys as $key) {
        if (array_key_exists($key, $row)) {
            return importar_texto($row[$key]);
        }
    }
    return '';
}

function importar_es_lista(array $value): bool
{
    $index = 0;
    foreach ($value as $key => $_) {
        if ($key !== $index++) {
            return false;
        }
    }
    return true;
}

function importar_preparar(mysqli $mysqli, string $sql): mysqli_stmt
{
    $statement = $mysqli->prepare($sql);
    if (!$statement) {
        throw new RuntimeException('No fue posible preparar la importación.');
    }
    return $statement;
}

function importar_registros_csv(string $file, string $extension): array
{
    $handle = fopen($file, 'rb');
    if (!$handle) {
        throw new InvalidArgumentException('No fue posible leer el archivo.');
    }

    $firstLine = (string) fgets($handle);
    rewind($handle);
    $delimiters = [',' => substr_count($firstLine, ','), ';' => substr_count($firstLine, ';'), "\t" => substr_count($firstLine, "\t"), '|' => substr_count($firstLine, '|')];
    arsort($delimiters);
    $delimiter = (string) array_key_first($delimiters);
    if ($delimiters[$delimiter] === 0) {
        fclose($handle);
        throw new InvalidArgumentException('El archivo ' . strtoupper($extension) . ' debe tener encabezados separados por coma, punto y coma, tabulación o barra vertical.');
    }

    $rawHeaders = fgetcsv($handle, 0, $delimiter);
    if (!$rawHeaders) {
        fclose($handle);
        throw new InvalidArgumentException('El archivo no contiene encabezados.');
    }
    $headers = array_map(static fn($header) => importar_cabecera((string) $header), $rawHeaders);
    $required = ['grado', 'materia', 'periodo', 'estandar'];
    foreach ($required as $field) {
        if (!in_array($field, $headers, true)) {
            fclose($handle);
            throw new InvalidArgumentException("Falta la columna obligatoria: $field.");
        }
    }

    $records = [];
    $line = 1;
    while (($values = fgetcsv($handle, 0, $delimiter)) !== false) {
        $line++;
        if (count($values) === 1 && trim((string) $values[0]) === '') {
            continue;
        }
        if (count($values) !== count($headers)) {
            fclose($handle);
            throw new InvalidArgumentException("La línea $line no tiene el mismo número de columnas que el encabezado.");
        }
        $records[] = ['line' => $line, 'data' => array_combine($headers, $values)];
    }
    fclose($handle);
    return $records;
}

function importar_registros_json(string $file): array
{
    $content = file_get_contents($file);
    $decoded = json_decode((string) $content, true);
    if (!is_array($decoded)) {
        throw new InvalidArgumentException('El archivo JSON no es válido.');
    }
    $records = $decoded['registros'] ?? $decoded;
    if (!is_array($records) || !importar_es_lista($records)) {
        throw new InvalidArgumentException('El JSON debe ser una lista de registros o contener una propiedad "registros".');
    }
    $normalized = [];
    foreach ($records as $index => $record) {
        if (!is_array($record)) {
            throw new InvalidArgumentException('El registro JSON ' . ($index + 1) . ' no es un objeto válido.');
        }
        $row = [];
        foreach ($record as $key => $value) {
            $row[importar_cabecera((string) $key)] = $value;
        }
        $normalized[] = ['line' => $index + 1, 'data' => $row];
    }
    return $normalized;
}

function importar_id_por_valor(mysqli $mysqli, string $table, string $idColumn, string $nameColumn, string $value, string $label): int
{
    if (ctype_digit($value) && (int) $value > 0) {
        $id = (int) $value;
        $statement = importar_preparar($mysqli, "SELECT `$idColumn` FROM `$table` WHERE `$idColumn` = ? LIMIT 1");
        $statement->bind_param('i', $id);
    } else {
        $statement = importar_preparar($mysqli, "SELECT `$idColumn` FROM `$table` WHERE LOWER(TRIM(`$nameColumn`)) = LOWER(TRIM(?)) LIMIT 1");
        $statement->bind_param('s', $value);
    }
    $statement->execute();
    $row = $statement->get_result()->fetch_assoc();
    $statement->close();
    if (!$row) {
        throw new InvalidArgumentException("$label no existe: $value.");
    }
    return (int) $row[$idColumn];
}

function importar_buscar_hijo(mysqli $mysqli, string $table, string $idColumn, string $nameColumn, string $parentColumn, int $parentId, string $name): ?int
{
    $statement = importar_preparar($mysqli, "SELECT `$idColumn` FROM `$table` WHERE `$parentColumn` = ? AND LOWER(TRIM(`$nameColumn`)) = LOWER(TRIM(?)) LIMIT 1");
    $statement->bind_param('is', $parentId, $name);
    $statement->execute();
    $row = $statement->get_result()->fetch_assoc();
    $statement->close();
    return $row ? (int) $row[$idColumn] : null;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    importar_responder(['status' => 'error', 'message' => 'Use una solicitud POST para importar.'], 405);
}
if (!isset($_FILES['archivo']) || !is_array($_FILES['archivo']) || $_FILES['archivo']['error'] !== UPLOAD_ERR_OK) {
    importar_responder(['status' => 'error', 'message' => 'Seleccione un archivo válido para importar.'], 400);
}

$file = $_FILES['archivo'];
if ((int) $file['size'] <= 0 || (int) $file['size'] > 5 * 1024 * 1024) {
    importar_responder(['status' => 'error', 'message' => 'El archivo debe pesar entre 1 byte y 5 MB.'], 400);
}
$extension = strtolower(pathinfo((string) $file['name'], PATHINFO_EXTENSION));
if (!in_array($extension, ['json', 'csv', 'txt'], true)) {
    importar_responder(['status' => 'error', 'message' => 'Formato no admitido. Use JSON, CSV o TXT.'], 400);
}

$transactionStarted = false;
try {
    $records = $extension === 'json'
        ? importar_registros_json((string) $file['tmp_name'])
        : importar_registros_csv((string) $file['tmp_name'], $extension);
    if (!$records) {
        throw new InvalidArgumentException('El archivo no contiene registros para importar.');
    }
    if (count($records) > 2000) {
        throw new InvalidArgumentException('El archivo supera el máximo de 2.000 registros por importación.');
    }

    $mysqli->begin_transaction();
    $transactionStarted = true;
    $created = ['estandares' => 0, 'dbas' => 0, 'ejes' => 0];
    $skipped = ['estandares' => 0, 'dbas' => 0, 'ejes' => 0];

    foreach ($records as $item) {
        $line = (int) $item['line'];
        $row = $item['data'];
        $grado = importar_valor($row, ['grado', 'id_grado']);
        $materia = importar_valor($row, ['materia', 'id_materia', 'id_materia_oficial']);
        $periodo = importar_valor($row, ['periodo', 'id_periodo']);
        $estandar = importar_valor($row, ['estandar', 'nombre_estandar']);
        $descripcionEstandar = importar_valor($row, ['descripcion_estandar', 'descripcion_del_estandar']);
        $dba = importar_valor($row, ['dba', 'nombre_dba']);
        $descripcionDba = importar_valor($row, ['descripcion_dba', 'descripcion_del_dba']);
        $eje = importar_valor($row, ['eje_tematico', 'eje', 'nombre_eje_tematico']);
        $descripcionEje = importar_valor($row, ['descripcion_eje', 'descripcion_eje_tematico', 'descripcion_del_eje']);

        if ($grado === '' || $materia === '' || $periodo === '' || $estandar === '') {
            throw new InvalidArgumentException("En el registro $line son obligatorios: grado, materia, periodo y estandar.");
        }
        if (!ctype_digit($periodo) || (int) $periodo < 1 || (int) $periodo > 4) {
            throw new InvalidArgumentException("El período del registro $line debe estar entre 1 y 4.");
        }
        if ($eje !== '' && $dba === '') {
            throw new InvalidArgumentException("El registro $line incluye un eje temático, pero no un DBA.");
        }
        if ((function_exists('mb_strlen') ? mb_strlen($eje, 'UTF-8') : strlen($eje)) > 255) {
            throw new InvalidArgumentException("El eje temático del registro $line supera 255 caracteres.");
        }

        $gradoId = importar_id_por_valor($mysqli, 'grado', 'id_grado', 'nombre', $grado, 'El grado');
        $materiaId = importar_id_por_valor($mysqli, 'materia_oficial', 'id_materia', 'nombre_materia', $materia, 'La materia');
        $periodoId = (int) $periodo;

        $statement = importar_preparar($mysqli, 'SELECT id_estandar FROM estandar WHERE grado = ? AND id_periodo = ? AND id_materia_oficial = ? AND LOWER(TRIM(nombre_estandar)) = LOWER(TRIM(?)) LIMIT 1');
        $statement->bind_param('iiis', $gradoId, $periodoId, $materiaId, $estandar);
        $statement->execute();
        $found = $statement->get_result()->fetch_assoc();
        $statement->close();
        if ($found) {
            $estandarId = (int) $found['id_estandar'];
            $skipped['estandares']++;
        } else {
            $statement = importar_preparar($mysqli, 'INSERT INTO estandar (nombre_estandar, descripcion_estandar, grado, id_periodo, id_materia_oficial) VALUES (?, ?, ?, ?, ?)');
            $statement->bind_param('ssiii', $estandar, $descripcionEstandar, $gradoId, $periodoId, $materiaId);
            $statement->execute();
            $estandarId = (int) $mysqli->insert_id;
            $statement->close();
            $created['estandares']++;
        }

        if ($dba === '') {
            continue;
        }
        $dbaId = importar_buscar_hijo($mysqli, 'dba', 'id_dba', 'nombre_dba', 'id_estandar', $estandarId, $dba);
        if ($dbaId === null) {
            $statement = importar_preparar($mysqli, 'INSERT INTO dba (nombre_dba, descripcion_dba, id_estandar) VALUES (?, ?, ?)');
            $statement->bind_param('ssi', $dba, $descripcionDba, $estandarId);
            $statement->execute();
            $dbaId = (int) $mysqli->insert_id;
            $statement->close();
            $created['dbas']++;
        } else {
            $skipped['dbas']++;
        }

        if ($eje === '') {
            continue;
        }
        $ejeId = importar_buscar_hijo($mysqli, 'eje_tematico', 'id_eje_tematico', 'nombre_eje_tematico', 'id_dba', $dbaId, $eje);
        if ($ejeId === null) {
            $statement = importar_preparar($mysqli, 'INSERT INTO eje_tematico (nombre_eje_tematico, descripcion_eje_tematico, id_dba) VALUES (?, ?, ?)');
            $statement->bind_param('ssi', $eje, $descripcionEje, $dbaId);
            $statement->execute();
            $statement->close();
            $created['ejes']++;
        } else {
            $skipped['ejes']++;
        }
    }

    $mysqli->commit();
    importar_responder([
        'status' => 'success',
        'message' => 'Importación completada correctamente.',
        'records' => count($records),
        'created' => $created,
        'skipped' => $skipped,
    ]);
} catch (InvalidArgumentException $error) {
    if ($transactionStarted) {
        $mysqli->rollback();
    }
    importar_responder(['status' => 'error', 'message' => $error->getMessage()], 422);
} catch (Throwable $error) {
    if ($transactionStarted) {
        $mysqli->rollback();
    }
    error_log('apps/dba/api/import.php: ' . $error->getMessage());
    importar_responder(['status' => 'error', 'message' => 'No fue posible importar el archivo. Verifique su estructura e inténtelo de nuevo.'], 500);
}
