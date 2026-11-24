<?php
/** Ejecutar valores como parámetros; los identificadores pertenecen al código. */
function guagua_sql(mysqli $db, string $sql, array $values = [], string $types = '')
{
    $statement = $db->prepare($sql);
    if (!$statement) throw new RuntimeException('No se pudo preparar la operación.');
    try {
        if ($values) $statement->bind_param($types ?: str_repeat('s',count($values)), ...$values);
        if (!$statement->execute()) throw new mysqli_sql_exception('No se pudo procesar la operación.', $statement->errno);
        return $statement->field_count ? $statement->get_result() : true;
    } finally { $statement->close(); }
}
