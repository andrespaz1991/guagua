<?php
/** Copia SQL de las bases compartidas; nunca elimina ni modifica sus tablas. */
function guagua_tienda_exportar_sql(string $path, ?string $profile): void
{
    $output = fopen($path, 'xb');
    if (!$output) throw new RuntimeException('No se pudo preparar la copia de la base de datos.');
    $write = static function (string $text) use ($output): void {
        if (fwrite($output, $text) !== strlen($text)) throw new RuntimeException('No hay espacio suficiente para completar el respaldo.');
    };
    $quote = static fn(string $name): string => '`' . str_replace('`', '``', $name) . '`';
    try {
        $write("-- Respaldo Guagua. Restauración manual; incluye datos de otros módulos.\nSET NAMES utf8mb4;\nSET FOREIGN_KEY_CHECKS=0;\n");
        foreach (array_unique(array_filter(['default', $profile])) as $key) {
            $db = guagua_db_mysqli($key);
            try {
                $db->query('SET TRANSACTION ISOLATION LEVEL REPEATABLE READ');
                $db->query('START TRANSACTION WITH CONSISTENT SNAPSHOT');
                $name = (string)$db->query('SELECT DATABASE()')->fetch_row()[0];
                $write("\n-- Perfil: " . $key . "\nCREATE DATABASE IF NOT EXISTS " . $quote($name) . ";\nUSE " . $quote($name) . ";\n");
                $tables = $db->query("SHOW FULL TABLES WHERE Table_type = 'BASE TABLE'")->fetch_all(MYSQLI_NUM);
                foreach ($tables as $tableRow) {
                    $table = $quote($tableRow[0]);
                    $schema = $db->query('SHOW CREATE TABLE ' . $table)->fetch_row()[1];
                    $write(preg_replace('/^CREATE TABLE /', 'CREATE TABLE IF NOT EXISTS ', $schema) . ";\n");
                    $columns = $db->query('SHOW FULL COLUMNS FROM ' . $table)->fetch_all(MYSQLI_ASSOC);
                    $names = [];
                    foreach ($columns as $column) if (stripos($column['Extra'], 'GENERATED') === false) $names[] = $quote($column['Field']);
                    if (!$names) continue;
                    $list = implode(',', $names);
                    $rows = $db->query('SELECT ' . $list . ' FROM ' . $table, MYSQLI_USE_RESULT);
                    try {
                        while ($row = $rows->fetch_row()) {
                            $values = array_map(static fn($value) => $value === null ? 'NULL' : ($value === '' ? "''" : '0x' . bin2hex((string)$value)), $row);
                            $write('INSERT INTO ' . $table . ' (' . $list . ') VALUES (' . implode(',', $values) . ");\n");
                        }
                    } finally { $rows->free(); }
                }
                $db->commit();
            } finally { $db->close(); }
        }
        $write("SET FOREIGN_KEY_CHECKS=1;\n");
    } finally { fclose($output); }
}

