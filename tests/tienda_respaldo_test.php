<?php
/** Exportador probado sin leer datos reales ni modificar la base de Guagua. */
declare(strict_types=1);
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require_once dirname(__DIR__) . '/comun/tienda_respaldo.php';
class StoreSqlRows {
    public function __construct(private array $rows) {}
    public function fetch_row() { return array_shift($this->rows); }
    public function fetch_all($mode) { return $this->rows; }
    public function free() {}
}
class StoreSqlConnection {
    public static array $queries = [];
    public static array $closed = [];
    public function __construct(private string $profile) {}
    public function query(string $query, $mode = null) {
        self::$queries[] = $query;
        if ($query === 'SELECT DATABASE()') return new StoreSqlRows([[$this->profile === 'default' ? 'guagua_prueba' : 'edutask_prueba']]);
        if (str_starts_with($query, 'SHOW FULL TABLES')) return new StoreSqlRows([['datos`prueba']]);
        if (str_starts_with($query, 'SHOW CREATE TABLE')) return new StoreSqlRows([['datos`prueba', 'CREATE TABLE `datos``prueba` (`nombre` text, `vacio` text, `nulo` text, `generado` int GENERATED ALWAYS AS (1))']]);
        if (str_starts_with($query, 'SHOW FULL COLUMNS')) return new StoreSqlRows([['Field'=>'nombre','Extra'=>''], ['Field'=>'vacio','Extra'=>''], ['Field'=>'nulo','Extra'=>''], ['Field'=>'generado','Extra'=>'VIRTUAL GENERATED']]);
        if (str_starts_with($query, 'SELECT `nombre`')) return new StoreSqlRows([["Árbol\0'\\", '', null]]);
        return true;
    }
    public function commit() {}
    public function close() { self::$closed[] = $this->profile; }
}
function guagua_db_mysqli(string $profile = 'default') { return new StoreSqlConnection($profile); }
$file = sys_get_temp_dir() . '/guagua-store-sql-' . bin2hex(random_bytes(8)) . '.sql';
try {
    guagua_tienda_exportar_sql($file, 'edutask');
    $sql = file_get_contents($file);
    foreach (["\n-- Perfil: default\n", 'USE `guagua_prueba`;', 'USE `edutask_prueba`;', 'CREATE TABLE IF NOT EXISTS `datos``prueba`', "0x" . bin2hex("Árbol\0'\\") . ",'',NULL", 'SET FOREIGN_KEY_CHECKS=1;'] as $expected) {
        if (!str_contains($sql, $expected)) throw new RuntimeException('El SQL perdió contenido o formato: ' . $expected);
    }
    if (str_contains($sql, '\\n-- Perfil') || str_contains($sql, 'DROP TABLE') || str_contains($sql, 'INSERT INTO `datos``prueba` (`nombre`,`vacio`,`nulo`,`generado`)')) throw new RuntimeException('SQL inválido o columna calculada en los datos.');
    foreach (StoreSqlConnection::$queries as $query) {
        if (preg_match('/^(?:INSERT|UPDATE|DELETE|DROP|ALTER|CREATE) /', $query)) throw new RuntimeException('El exportador intentó modificar la base.');
    }
    if (StoreSqlConnection::$closed !== ['default', 'edutask']) throw new RuntimeException('No se cerraron las conexiones.');
    echo "OK: SQL válido, dos perfiles, UTF-8, binarios, NULL, columnas calculadas, escape de identificadores y consultas de solo lectura.\n";
} finally { if (is_file($file)) unlink($file); }
