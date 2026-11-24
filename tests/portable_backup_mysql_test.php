<?php
/** Exportación y restauración reales, exclusivamente sobre una instancia MariaDB temporal de QA. */
declare(strict_types=1);
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require_once dirname(__DIR__) . '/comun/lib/GuaguaPortableBackup.php';
$expectedData = realpath((string)getenv('GUAGUA_PORTABLE_TEST_DATADIR'));
$port = (int)getenv('GUAGUA_PORTABLE_TEST_PORT');
$workspace = str_replace('\\', '/', dirname(__DIR__));
if (!$expectedData || $port < 1024 || !str_starts_with(str_replace('\\', '/', $expectedData), $workspace . '/tmp/portable-backup-qa-')) throw new RuntimeException('Indica una instancia aislada en GUAGUA_PORTABLE_TEST_DATADIR y GUAGUA_PORTABLE_TEST_PORT.');
$server = new mysqli('127.0.0.1', 'root', '', '', $port);
$actualData = realpath((string)$server->query('SELECT @@datadir')->fetch_row()[0]);
if ($actualData !== $expectedData) throw new RuntimeException('El servidor no usa el directorio temporal esperado.');
$server->set_charset('utf8mb4');
$name = 'guagua_portable_qa_' . bin2hex(random_bytes(5));
$second = $name . '_edutask';
$fixture = $workspace . '/tmp/portable-backup-qa-' . bin2hex(random_bytes(6));
mkdir($fixture, 0700);
file_put_contents($fixture . '/index.php', '<?php echo "Guagua QA";');
$backup = new GuaguaPortableBackup($fixture);
$variables = [
    'GUAGUA_APP_ENV'=>'local', 'GUAGUA_DB_HOST'=>'127.0.0.1', 'GUAGUA_DB_PORT'=>(string)$port,
    'GUAGUA_DB_USER'=>'root', 'GUAGUA_DB_PASSWORD'=>'', 'GUAGUA_DB_NAME'=>$name,
    'GUAGUA_DB_EDUTASK_NAME'=>$second, 'GUAGUA_DB_PROMPT_NAME'=>$name . '_inexistente', 'GUAGUA_DB_MOODLE_NAME'=>$name,
];
foreach ($variables as $key => $value) putenv($key . '=' . $value);
$checks = 0;
$check = static function (bool $condition, string $message) use (&$checks): void { $checks++; if (!$condition) throw new RuntimeException($message); };
try {
    $server->query('CREATE DATABASE `' . $name . '` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
    $server->query('CREATE DATABASE `' . $second . '` CHARACTER SET utf8mb4');
    $server->select_db($name);
    $server->query('CREATE TABLE datos (id int PRIMARY KEY AUTO_INCREMENT, nombre text, binario longblob, nulo text, numero int, doble int GENERATED ALWAYS AS (numero * 2) STORED) ENGINE=InnoDB');
    $server->query('CREATE TABLE auditoria (id int AUTO_INCREMENT PRIMARY KEY, nota varchar(100)) ENGINE=InnoDB');
    $server->query('CREATE TABLE hijos (id int PRIMARY KEY, padre int, FOREIGN KEY (padre) REFERENCES datos(id)) ENGINE=InnoDB');
    $server->query('CREATE VIEW vista_datos AS SELECT id, nombre, doble FROM datos');
    $server->query("CREATE TRIGGER registrar_dato AFTER INSERT ON datos FOR EACH ROW INSERT INTO auditoria(nota) VALUES ('insertado')");
    $server->query('CREATE PROCEDURE contar_datos() SELECT COUNT(*) AS total FROM datos');
    $server->query('CREATE FUNCTION doble_qa(n int) RETURNS int DETERMINISTIC RETURN n * 2');
    $server->query("CREATE EVENT evento_qa ON SCHEDULE AT CURRENT_TIMESTAMP + INTERVAL 1 YEAR DO INSERT INTO auditoria(nota) VALUES ('evento')");
    $text = "Árbol 🚌 'comillas' \\ barra\nsegunda línea";
    $binary = "\0\xff\x01\r\n'\\";
    $statement = $server->prepare('INSERT INTO datos(nombre, binario, nulo, numero) VALUES (?, ?, NULL, 7)');
    $statement->bind_param('ss', $text, $binary);
    $statement->execute(); $statement->close();
    $server->query('INSERT INTO hijos VALUES (1, 1)');
    $server->query('CREATE TABLE `' . $second . '`.tareas (titulo text)');
    $server->query("INSERT INTO `" . $second . "`.tareas VALUES ('Prueba EduTask')");
    $databases = $backup->databases(true);
    $check(count($databases['included']) === 2, 'No se deduplicó una base compartida o falta EduTask.');
    $check(count($databases['omitted']) === 1 && str_starts_with($databases['omitted'][0], 'prompt:'), 'Base opcional ausente mal identificada.');
    $path = $backup->create($databases['included'], $databases['omitted']);
    $zip = new ZipArchive();
    $check($zip->open($path, ZipArchive::CHECKCONS) === true, 'ZIP real corrupto.');
    $sql = $zip->getFromName('base_de_datos/01-guagua.sql');
    foreach (['CREATE TABLE', 'VIEW', 'TRIGGER', 'PROCEDURE', 'FUNCTION', 'EVENT'] as $object) $check(str_contains($sql, $object), 'Falta objeto SQL: ' . $object);
    foreach (['01-guagua.sql', '02-edutask.sql'] as $file) file_put_contents($fixture . '/' . $file, $zip->getFromName('base_de_datos/' . $file));
    $zip->close();
    $server->select_db('information_schema');
    // Solo se eliminan los esquemas aleatorios creados arriba, en el servidor aislado verificado.
    $server->query('DROP DATABASE `' . $name . '`');
    $server->query('DROP DATABASE `' . $second . '`');
    $client = dirname((string)$backup->dumpExecutable()) . '/mysql' . (PHP_OS_FAMILY === 'Windows' ? '.exe' : '');
    foreach (['01-guagua.sql', '02-edutask.sql'] as $file) {
        $process = proc_open([$client, '--no-defaults', '--host=127.0.0.1', '--port=' . $port, '--user=root', '--default-character-set=utf8mb4'],
            [0=>['file', $fixture . '/' . $file, 'rb'], 1=>['file', $fixture . '/import.out', 'wb'], 2=>['file', $fixture . '/import.err', 'wb']], $pipes, null, null, ['bypass_shell'=>true]);
        $check(is_resource($process) && proc_close($process) === 0, 'La importación nativa falló: ' . file_get_contents($fixture . '/import.err'));
    }
    $server->select_db($name);
    $row = $server->query('SELECT * FROM datos')->fetch_assoc();
    $check($row['nombre'] === $text && $row['binario'] === $binary && $row['nulo'] === null, 'La restauración alteró UTF-8, binarios o NULL.');
    $check((int)$row['doble'] === 14, 'No se restauró la columna calculada.');
    $check($server->query('SELECT * FROM vista_datos')->num_rows === 1, 'La vista no funciona.');
    $check((int)$server->query('SELECT doble_qa(4)')->fetch_row()[0] === 8, 'La función no funciona.');
    $check($server->query('SHOW PROCEDURE STATUS WHERE Db = ' . "'" . $name . "'")->num_rows === 1, 'No se restauró el procedimiento.');
    $check($server->query('SHOW EVENTS')->num_rows === 1, 'No se restauró el evento.');
    $server->query("INSERT INTO datos(nombre, numero) VALUES ('después', 3)");
    $check($server->query('SELECT * FROM auditoria')->num_rows === 2, 'El disparador restaurado no funciona.');
    $check($server->query('SELECT * FROM hijos')->num_rows === 1, 'Faltan datos relacionados.');
    $check($server->query('SELECT * FROM `' . $second . '`.tareas')->fetch_row()[0] === 'Prueba EduTask', 'La base adicional no se restauró.');
    echo "OK: $checks comprobaciones con exportación/restauración MariaDB reales, binarios, UTF-8, NULL, claves foráneas, columnas calculadas, vistas, rutinas, eventos y disparadores.\n";
} finally {
    $backup->cleanup();
    $server->select_db('information_schema');
    foreach ([$name, $second] as $database) $server->query('DROP DATABASE IF EXISTS `' . $database . '`');
    $server->close();
    foreach (glob($fixture . '/*') ?: [] as $file) unlink($file);
    rmdir($fixture);
}
