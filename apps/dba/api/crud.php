<?php
ini_set('display_errors', '0');
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../../comun/conexion.php';

function dba_api_respond(array $data, int $statusCode = 200): void
{
    http_response_code($statusCode);
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function dba_api_required_id(array $data, string $field, string $label): int
{
    $value = $data[$field] ?? null;
    if (filter_var($value, FILTER_VALIDATE_INT) === false || (int)$value <= 0) {
        throw new InvalidArgumentException("$label no es válido.");
    }
    return (int)$value;
}

function dba_api_required_text(array $data, string $field, string $label, int $maxLength = 0): string
{
    $value = $data[$field] ?? '';
    if (!is_scalar($value)) {
        throw new InvalidArgumentException("$label no es válido.");
    }

    $value = trim(str_replace("\0", '', (string)$value));
    if ($value === '') {
        throw new InvalidArgumentException("$label es obligatorio.");
    }
    $length = function_exists('mb_strlen') ? mb_strlen($value, 'UTF-8') : strlen($value);
    if ($maxLength > 0 && $length > $maxLength) {
        throw new InvalidArgumentException("$label no puede superar $maxLength caracteres.");
    }
    return $value;
}

function dba_api_optional_text(array $data, string $field): string
{
    $value = $data[$field] ?? '';
    if (!is_scalar($value)) {
        throw new InvalidArgumentException('La descripción no es válida.');
    }
    return trim(str_replace("\0", '', (string)$value));
}

function dba_api_prepare(mysqli $mysqli, string $sql): mysqli_stmt
{
    $statement = $mysqli->prepare($sql);
    if (!$statement) {
        throw new RuntimeException('No fue posible preparar la operación en la base de datos.');
    }
    return $statement;
}

function dba_api_assert_exists(mysqli $mysqli, string $table, string $idColumn, int $id, string $label): void
{
    // Los nombres de tabla y columna se reciben únicamente desde este archivo.
    $statement = dba_api_prepare($mysqli, "SELECT 1 FROM `$table` WHERE `$idColumn` = ? LIMIT 1");
    $statement->bind_param('i', $id);
    $statement->execute();
    $exists = $statement->get_result()->num_rows === 1;
    $statement->close();

    if (!$exists) {
        throw new InvalidArgumentException("$label no existe o ya fue eliminado.");
    }
}

$input = file_get_contents('php://input');
$data = json_decode($input, true);
if (!is_array($data)) {
    dba_api_respond(['status' => 'error', 'message' => 'La petición debe contener JSON válido.'], 400);
}

$action = (string)($data['action'] ?? '');
$type = (string)($data['type'] ?? '');
if (!in_array($action, ['create', 'edit', 'delete'], true) || !in_array($type, ['estandar', 'dba', 'eje_tematico'], true)) {
    dba_api_respond(['status' => 'error', 'message' => 'La operación solicitada no es válida.'], 400);
}

$transactionStarted = false;
try {
    $mysqli->begin_transaction();
    $transactionStarted = true;
    $response = ['status' => 'success', 'message' => 'Cambios guardados correctamente.'];

    if ($action === 'create' && $type === 'estandar') {
        $nombre = dba_api_required_text($data, 'nombre_estandar', 'El nombre del estándar');
        $descripcion = dba_api_optional_text($data, 'descripcion_estandar');
        $grado = dba_api_required_id($data, 'grado', 'El grado');
        $periodo = dba_api_required_id($data, 'id_periodo', 'El período');
        $materia = dba_api_required_id($data, 'id_materia_oficial', 'La materia');
        dba_api_assert_exists($mysqli, 'grado', 'id_grado', $grado, 'El grado');
        dba_api_assert_exists($mysqli, 'periodo', 'id_periodo', $periodo, 'El período');
        dba_api_assert_exists($mysqli, 'materia_oficial', 'id_materia', $materia, 'La materia');

        $statement = dba_api_prepare($mysqli, 'INSERT INTO estandar (nombre_estandar, descripcion_estandar, grado, id_periodo, id_materia_oficial) VALUES (?, ?, ?, ?, ?)');
        $statement->bind_param('ssiii', $nombre, $descripcion, $grado, $periodo, $materia);
        $statement->execute();
        $response['id'] = $mysqli->insert_id;
        $response['message'] = 'Estándar creado correctamente.';
        $statement->close();
    } elseif ($action === 'create' && $type === 'dba') {
        $nombre = dba_api_required_text($data, 'nombre_dba', 'El nombre del DBA');
        $descripcion = dba_api_optional_text($data, 'descripcion_dba');
        $idEstandar = dba_api_required_id($data, 'id_estandar', 'El estándar');
        dba_api_assert_exists($mysqli, 'estandar', 'id_estandar', $idEstandar, 'El estándar');

        $statement = dba_api_prepare($mysqli, 'INSERT INTO dba (nombre_dba, descripcion_dba, id_estandar) VALUES (?, ?, ?)');
        $statement->bind_param('ssi', $nombre, $descripcion, $idEstandar);
        $statement->execute();
        $response['id'] = $mysqli->insert_id;
        $response['message'] = 'DBA creado correctamente.';
        $statement->close();
    } elseif ($action === 'create' && $type === 'eje_tematico') {
        $nombre = dba_api_required_text($data, 'nombre_eje_tematico', 'El nombre del eje temático', 255);
        $descripcion = dba_api_optional_text($data, 'descripcion_eje_tematico');
        $idDba = dba_api_required_id($data, 'id_dba', 'El DBA');
        dba_api_assert_exists($mysqli, 'dba', 'id_dba', $idDba, 'El DBA');

        $statement = dba_api_prepare($mysqli, 'INSERT INTO eje_tematico (nombre_eje_tematico, descripcion_eje_tematico, id_dba) VALUES (?, ?, ?)');
        $statement->bind_param('ssi', $nombre, $descripcion, $idDba);
        $statement->execute();
        $response['id'] = $mysqli->insert_id;
        $response['message'] = 'Eje temático creado correctamente.';
        $statement->close();
    } elseif ($action === 'edit' && $type === 'estandar') {
        $id = dba_api_required_id($data, 'id_estandar', 'El estándar');
        $nombre = dba_api_required_text($data, 'nombre_estandar', 'El nombre del estándar');
        $descripcion = dba_api_optional_text($data, 'descripcion_estandar');
        dba_api_assert_exists($mysqli, 'estandar', 'id_estandar', $id, 'El estándar');

        $statement = dba_api_prepare($mysqli, 'UPDATE estandar SET nombre_estandar = ?, descripcion_estandar = ? WHERE id_estandar = ?');
        $statement->bind_param('ssi', $nombre, $descripcion, $id);
        $statement->execute();
        $response['message'] = 'Estándar actualizado correctamente.';
        $statement->close();
    } elseif ($action === 'edit' && $type === 'dba') {
        $id = dba_api_required_id($data, 'id_dba', 'El DBA');
        $nombre = dba_api_required_text($data, 'nombre_dba', 'El nombre del DBA');
        $descripcion = dba_api_optional_text($data, 'descripcion_dba');
        dba_api_assert_exists($mysqli, 'dba', 'id_dba', $id, 'El DBA');

        $statement = dba_api_prepare($mysqli, 'UPDATE dba SET nombre_dba = ?, descripcion_dba = ? WHERE id_dba = ?');
        $statement->bind_param('ssi', $nombre, $descripcion, $id);
        $statement->execute();
        $response['message'] = 'DBA actualizado correctamente.';
        $statement->close();
    } elseif ($action === 'edit' && $type === 'eje_tematico') {
        $id = dba_api_required_id($data, 'id_eje_tematico', 'El eje temático');
        $nombre = dba_api_required_text($data, 'nombre_eje_tematico', 'El nombre del eje temático', 255);
        $descripcion = dba_api_optional_text($data, 'descripcion_eje_tematico');
        dba_api_assert_exists($mysqli, 'eje_tematico', 'id_eje_tematico', $id, 'El eje temático');

        $statement = dba_api_prepare($mysqli, 'UPDATE eje_tematico SET nombre_eje_tematico = ?, descripcion_eje_tematico = ? WHERE id_eje_tematico = ?');
        $statement->bind_param('ssi', $nombre, $descripcion, $id);
        $statement->execute();
        $response['message'] = 'Eje temático actualizado correctamente.';
        $statement->close();
    } elseif ($action === 'delete' && $type === 'estandar') {
        $id = dba_api_required_id($data, 'id_estandar', 'El estándar');
        dba_api_assert_exists($mysqli, 'estandar', 'id_estandar', $id, 'El estándar');

        $statement = dba_api_prepare($mysqli, 'DELETE FROM eje_tematico WHERE id_dba IN (SELECT id_dba FROM dba WHERE id_estandar = ?)');
        $statement->bind_param('i', $id);
        $statement->execute();
        $ejesEliminados = $statement->affected_rows;
        $statement->close();
        $statement = dba_api_prepare($mysqli, 'DELETE FROM dba WHERE id_estandar = ?');
        $statement->bind_param('i', $id);
        $statement->execute();
        $dbasEliminados = $statement->affected_rows;
        $statement->close();
        $statement = dba_api_prepare($mysqli, 'DELETE FROM estandar WHERE id_estandar = ?');
        $statement->bind_param('i', $id);
        $statement->execute();
        $statement->close();
        $response['message'] = "Estándar eliminado junto con $dbasEliminados DBA y $ejesEliminados eje(s) temático(s).";
    } elseif ($action === 'delete' && $type === 'dba') {
        $id = dba_api_required_id($data, 'id_dba', 'El DBA');
        dba_api_assert_exists($mysqli, 'dba', 'id_dba', $id, 'El DBA');

        $statement = dba_api_prepare($mysqli, 'DELETE FROM eje_tematico WHERE id_dba = ?');
        $statement->bind_param('i', $id);
        $statement->execute();
        $ejesEliminados = $statement->affected_rows;
        $statement->close();
        $statement = dba_api_prepare($mysqli, 'DELETE FROM dba WHERE id_dba = ?');
        $statement->bind_param('i', $id);
        $statement->execute();
        $statement->close();
        $response['message'] = "DBA eliminado junto con $ejesEliminados eje(s) temático(s).";
    } else { // delete eje_tematico
        $id = dba_api_required_id($data, 'id_eje_tematico', 'El eje temático');
        dba_api_assert_exists($mysqli, 'eje_tematico', 'id_eje_tematico', $id, 'El eje temático');
        $statement = dba_api_prepare($mysqli, 'DELETE FROM eje_tematico WHERE id_eje_tematico = ?');
        $statement->bind_param('i', $id);
        $statement->execute();
        $statement->close();
        $response['message'] = 'Eje temático eliminado correctamente.';
    }

    $mysqli->commit();
    dba_api_respond($response);
} catch (InvalidArgumentException $error) {
    if ($transactionStarted) {
        $mysqli->rollback();
    }
    dba_api_respond(['status' => 'error', 'message' => $error->getMessage()], 422);
} catch (Throwable $error) {
    if ($transactionStarted) {
        $mysqli->rollback();
    }
    error_log('apps/dba/api/crud.php: ' . $error->getMessage());
    dba_api_respond(['status' => 'error', 'message' => 'No fue posible completar la operación. Inténtelo nuevamente.'], 500);
}
