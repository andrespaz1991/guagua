<?php
require_once __DIR__ . '/../comun/database.php';
function check_connection($condition, $message) { if (!$condition) throw new RuntimeException($message); }
$expected = GuaguaDatabase::settings();
$a = guagua_db_mysqli();
$b = guagua_db_mysqli();
$row = $a->query('SELECT DATABASE() db, @@session.time_zone tz, @@character_set_connection charset')->fetch_assoc();
check_connection($row['db'] === $expected['database'] && $row['tz'] === $expected['time_zone'] && $row['charset'] === $expected['charset'], 'Configuración MySQLi incorrecta.');
$a->close();
check_connection((int)$b->query('SELECT 1')->fetch_row()[0] === 1, 'Cerrar una conexión invalidó otra.');
$pdo = guagua_db_pdo();
$row = $pdo->query('SELECT DATABASE() db, @@session.time_zone tz, @@character_set_connection charset')->fetch();
check_connection($row['db'] === $expected['database'] && $row['tz'] === $expected['time_zone'] && $row['charset'] === $expected['charset'], 'Configuración PDO incorrecta.');
$statement = $pdo->prepare('SELECT ? value');
$statement->execute(['Guagua á 🚌']);
check_connection($statement->fetch()['value'] === 'Guagua á 🚌', 'Codificación PDO incorrecta.');
class Comun {}
require_once __DIR__ . '/../clases/Clase_mysqli.Class.php';
$legacy = new Clase_mysqli();
$legacy->conectar()->close();
check_connection((int)$legacy->conectar()->query('SELECT 1')->fetch_row()[0] === 1, 'Adaptador antiguo no recupera conexión cerrada.');
require_once __DIR__ . '/../seguimiento/mysql.class.php';
$reflection = new ReflectionClass('Conexion');
$tracking = $reflection->newInstanceWithoutConstructor()->conectar();
check_connection((int)$tracking->query('SELECT 1')->fetch_row()[0] === 1, 'Falló seguimiento.');
$tracking->close(); $b->close();
echo "OK: MySQLi, PDO, utf8mb4, zona horaria, conexiones independientes y adaptadores antiguos. Solo consultas de lectura.\n";
