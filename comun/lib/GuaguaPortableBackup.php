<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/database.php';
require_once __DIR__ . '/GuaguaPortableZipWriter.php';

/** Paquete de traslado. Solo lee el sitio y las bases; trabaja fuera del directorio público. */
final class GuaguaPortableBackup
{
    private string $root;
    private string $cacheDirectory;
    private ?string $temporary = null;
    /** @var callable|null */
    private $progress = null;

    public function __construct(string $root, ?string $cacheDirectory = null)
    {
        $resolved = realpath($root);
        if ($resolved === false || !is_dir($resolved)) throw new RuntimeException('No se encontró la carpeta de Guagua.');
        $this->root = str_replace('\\', '/', $resolved);
        $this->cacheDirectory = $cacheDirectory ?? rtrim(sys_get_temp_dir(), '/\\') . '/guagua-source-cache-' . substr(hash('sha256', $this->root), 0, 24);
    }

    public function dumpExecutable(): ?string
    {
        $names = PHP_OS_FAMILY === 'Windows' ? ['mysqldump.exe', 'mariadb-dump.exe'] : ['mysqldump', 'mariadb-dump'];
        $directories = [dirname(PHP_BINDIR) . '/mysql/bin', dirname($this->root, 2) . '/mysql/bin', PHP_BINDIR];
        $directories = array_merge($directories, explode(PATH_SEPARATOR, (string)getenv('PATH')));
        foreach ($directories as $directory) {
            foreach ($names as $name) {
                $path = rtrim($directory, '/\\') . '/' . $name;
                if (is_file($path) && is_executable($path)) return $path;
            }
        }
        return null;
    }

    public function requirements(): array
    {
        $errors = [];
        if (!class_exists('ZipArchive')) $errors[] = 'Activa la extensión zip de PHP y reinicia Apache.';
        if (!function_exists('proc_open')) $errors[] = 'El servidor debe permitir proc_open para exportar MySQL.';
        if ($this->dumpExecutable() === null) $errors[] = 'No se encontró mysqldump. Instala las herramientas de MySQL/MariaDB o agrega su carpeta bin al PATH del servidor.';
        return $errors;
    }

    /** Las bases adicionales ausentes quedan identificadas en la guía, nunca se confunden con un fallo de exportación. */
    public function databases(bool $additional, ?callable $progress = null): array
    {
        $result = ['included'=>[], 'omitted'=>[]];
        $seen = [];
        $profiles = $additional ? ['default', 'edutask', 'prompt', 'moodle'] : ['default'];
        foreach ($profiles as $index => $profile) {
            if ($progress !== null) $progress(5 * $index / count($profiles), 'Comprobando las bases de datos (' . ($index + 1) . '/' . count($profiles) . ')…');
            $settings = GuaguaDatabase::settings($profile);
            $identity = hash('sha256', json_encode(array_intersect_key($settings, array_flip(['host', 'port', 'database'])), JSON_THROW_ON_ERROR));
            if (isset($seen[$identity])) continue;
            $seen[$identity] = true;
            $db = guagua_db_mysqli($profile, ['database'=>'']);
            try {
                $query = $db->prepare('SELECT s.SCHEMA_NAME, COALESCE(SUM(t.DATA_LENGTH), 0) AS estimated_bytes FROM information_schema.SCHEMATA s LEFT JOIN information_schema.TABLES t ON t.TABLE_SCHEMA = s.SCHEMA_NAME WHERE s.SCHEMA_NAME = ? GROUP BY s.SCHEMA_NAME');
                $query->bind_param('s', $settings['database']);
                $query->execute();
                $schema = $query->get_result()->fetch_assoc();
                $exists = $schema !== null;
                $query->close();
            } finally { $db->close(); }
            if (!$exists) {
                if ($profile === 'default') throw new RuntimeException('La base principal no existe o el usuario de MySQL no tiene permiso para verla.');
                $result['omitted'][] = $profile . ': no existe o no es visible para el usuario de MySQL';
                continue;
            }
            $result['included'][] = ['profile'=>$profile, 'settings'=>$settings, 'estimated_bytes'=>(int)$schema['estimated_bytes']];
        }
        if ($progress !== null) $progress(5, 'Bases de datos comprobadas.');
        return $result;
    }

    /** El exportador inyectable permite verificar el ZIP sin leer información real. */
    public function create(array $databases, array $omitted = [], ?callable $export = null, ?callable $progress = null): string
    {
        if ($this->temporary !== null) throw new LogicException('Este paquete ya se ha preparado.');
        if (!$databases || ($databases[0]['profile'] ?? '') !== 'default') throw new RuntimeException('El paquete requiere la base principal.');
        if (!class_exists('ZipArchive')) throw new RuntimeException('Activa la extensión zip de PHP y reinicia Apache.');
        $this->progress = $progress;
        $this->temporary = rtrim(sys_get_temp_dir(), '/\\') . '/guagua-portable-' . bin2hex(random_bytes(16));
        if (!mkdir($this->temporary, 0700)) throw new RuntimeException('No se pudo crear la carpeta temporal.');
        register_shutdown_function([$this, 'cleanup']);
        $zip = null;
        try {
            $path = $this->temporary . '/guagua-xampp.zip';
            $manifest = [];
            $sqlFiles = [];
            $exports = [];
            foreach ($databases as $index => $database) {
                $profile = $database['profile'];
                if (!in_array($profile, ['default', 'edutask', 'prompt', 'moodle'], true)) throw new InvalidArgumentException('Perfil de exportación no válido.');
                $name = sprintf('%02d-%s.sql', $index + 1, $profile === 'default' ? 'guagua' : $profile);
                $sqlPath = $this->temporary . '/' . $name;
                if ($export !== null) {
                    $this->report(5 + 50 * $index / count($databases), 'Exportando las bases de datos (' . ($index + 1) . '/' . count($databases) . ')…');
                    $export($sqlPath, $database['settings']);
                } else $exports[] = ['path'=>$sqlPath, 'settings'=>$database['settings'], 'estimated_bytes'=>$database['estimated_bytes'] ?? 0];
                $sqlFiles[$sqlPath] = 'base_de_datos/' . $name;
                $manifest[] = ['perfil'=>$profile, 'base'=>$database['settings']['database'], 'archivo'=>'base_de_datos/' . $name];
            }
            if ($exports) $this->exportDatabases($exports);
            foreach ($sqlFiles as $sqlPath => $name) {
                clearstatcache(true, $sqlPath);
                if (!is_file($sqlPath) || filesize($sqlPath) === 0) throw new RuntimeException('La exportación SQL está vacía. No se generó el paquete.');
            }
            $this->report(55, 'Bases de datos exportadas. Revisando los archivos…');
            $count = $this->copySourceArchive($path);
            // Solo las bases recién exportadas y los textos cambian en cada copia.
            // Las entradas de archivos ya comprimidas se copian secuencialmente.
            $zip = new GuaguaPortableZipWriter($path, false, true);
            $this->report(95, 'Añadiendo las bases de datos y la guía al ZIP…');
            foreach ($sqlFiles as $sqlPath => $name) $zip->addFile($sqlPath, $name);
            $zip->addText('guagua/.env', $this->portableEnvironment());
            $zip->addText('LEEME-XAMPP.txt', $this->instructions($manifest, $omitted));
            $batch = file_get_contents(__DIR__ . '/portable/IMPORTAR_BD.bat');
            if ($batch === false) throw new RuntimeException('No se encontró el asistente de importación.');
            $zip->addText('IMPORTAR_BD.bat', str_replace("\n", "\r\n", str_replace("\r\n", "\n", $batch)));
            $zip->addText('contenido.json', json_encode([
                'formato'=>1, 'creado'=>date(DATE_ATOM), 'archivos'=>$count + 1, 'bases'=>$manifest, 'bases_omitidas'=>$omitted,
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR) . "\n");
            $zip->finish(function (float $fraction): void { $this->report(96 + 3 * $fraction, 'Terminando el ZIP…'); });
            $this->report(99, 'ZIP preparado. Iniciando la descarga…');
            return $path;
        } catch (Throwable $error) {
            if ($zip !== null) $zip->abort();
            $this->cleanup();
            throw $error;
        } finally {
            $this->progress = null;
        }
    }

    private function report(float $percent, string $stage): void
    {
        if ($this->progress !== null) ($this->progress)($percent, $stage);
    }

    /** El SQL y el .env generado nunca se guardan en esta caché privada. */
    private function copySourceArchive(string $destination): int
    {
        $directory = new RecursiveDirectoryIterator($this->root, FilesystemIterator::SKIP_DOTS);
        $filtered = new RecursiveCallbackFilterIterator($directory, function (SplFileInfo $file): bool {
            $relative = substr(str_replace('\\', '/', $file->getPathname()), strlen($this->root) + 1);
            $isDirectory = $file->isDir();
            if ($file->isLink() || $this->excluded($relative, $isDirectory)) return false;
            if (!$isDirectory) return true;
            $resolved = realpath($file->getPathname());
            if ($resolved === false) throw new RuntimeException('No se pudo acceder al archivo: ' . $relative);
            $resolved = str_replace('\\', '/', $resolved);
            return PHP_OS_FAMILY === 'Windows'
                ? str_starts_with(strtolower($resolved), strtolower($this->root) . '/')
                : str_starts_with($resolved, $this->root . '/');
        });
        $hash = hash_init('sha256');
        hash_update($hash, 'guagua-source-v1');
        $entries = [];
        $state = [];
        $count = 0;
        foreach (new RecursiveIteratorIterator($filtered, RecursiveIteratorIterator::SELF_FIRST) as $file) {
            if (!$file->isDir() && !$file->isFile()) continue;
            $relative = substr(str_replace('\\', '/', $file->getPathname()), strlen($this->root) + 1);
            $isDirectory = $file->isDir();
            $metadata = [$isDirectory, $file->getMTime(), $isDirectory ? 0 : $file->getSize()];
            hash_update($hash, serialize([$relative, $metadata]));
            $name = 'guagua/' . $relative . ($isDirectory ? '/' : '');
            $state[$name] = $metadata;
            $entries[] = [$file->getPathname(), $name, $isDirectory];
            if (!$isDirectory) $count++;
            if (count($entries) % 128 === 0) $this->report(55, 'Revisando los archivos: ' . $count . ' encontrados…');
        }
        $signature = hash_final($hash);
        if (is_link($this->cacheDirectory)) throw new RuntimeException('La carpeta de caché de la copia no es válida.');
        if (!is_dir($this->cacheDirectory) && !mkdir($this->cacheDirectory, 0700, true) && !is_dir($this->cacheDirectory)) throw new RuntimeException('No se pudo preparar la caché privada de archivos.');
        $lock = fopen($this->cacheDirectory . '/preparacion.lock', 'c');
        if ($lock === false) throw new RuntimeException('No se pudo abrir el control de preparación.');
        $writer = null;
        $partial = null;
        try {
            if (!flock($lock, LOCK_EX | LOCK_NB)) throw new RuntimeException('Otra copia está preparando los archivos. Vuelve a intentarlo cuando termine.');
            $this->report(60, 'Preparando los archivos del sitio…');
            $cached = $this->cacheDirectory . '/archivos-' . $signature . '.zip';
            if (!is_file($cached)) {
                $partial = $this->cacheDirectory . '/parcial-' . bin2hex(random_bytes(16));
                $previousState = is_file($this->cacheDirectory . '/estado.json')
                    ? json_decode((string)file_get_contents($this->cacheDirectory . '/estado.json'), true) : null;
                $previousName = is_array($previousState) ? ($previousState['archivo'] ?? '') : '';
                $previousEntries = is_array($previousState) ? ($previousState['entradas'] ?? null) : null;
                $incremental = is_string($previousName) && preg_match('/^archivos-[a-f0-9]{64}\.zip$/D', $previousName)
                    && is_array($previousEntries) && is_file($this->cacheDirectory . '/' . $previousName);
                if ($incremental && !copy($this->cacheDirectory . '/' . $previousName, $partial)) throw new RuntimeException('No se pudo actualizar la copia de archivos.');
                $writer = new GuaguaPortableZipWriter($partial, !$incremental, (bool)$incremental);
                if ($incremental) {
                    foreach ($previousEntries as $name => $metadata) if (!isset($state[$name])) $writer->remove($name);
                }
                $total = 0;
                foreach ($entries as [$source, $name, $isDirectory]) {
                    if (!$incremental || ($previousEntries[$name] ?? null) !== $state[$name]) $total += max(4096, $state[$name][2]);
                }
                $done = 0;
                foreach ($entries as [$source, $name, $isDirectory]) {
                    if ($incremental && ($previousEntries[$name] ?? null) === $state[$name]) continue;
                    if ($incremental) $writer->remove($name);
                    if ($isDirectory) $writer->addDirectory($name);
                    else $writer->addFile($source, $name);
                    $done += max(4096, $state[$name][2]);
                    $this->report(60 + 20 * $done / max(1, $total), 'Comprimiendo los archivos del sitio…');
                }
                $writer->finish(function (float $fraction): void { $this->report(80 + 10 * $fraction, 'Guardando los archivos comprimidos…'); });
                if (!rename($partial, $cached)) throw new RuntimeException('No se pudo terminar el paquete de archivos.');
                $partial = null;
            }
            $metadata = json_encode(['archivo'=>basename($cached), 'entradas'=>$state], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
            if (file_put_contents($this->cacheDirectory . '/estado.json', $metadata, LOCK_EX) === false) throw new RuntimeException('No se pudo guardar el estado de los archivos.');
            $this->report(90, 'Preparando la copia de los archivos…');
            $this->copyArchive($cached, $destination);
            // La copia al temporal ya terminó; ninguna petición usa estas bases antiguas.
            foreach (glob($this->cacheDirectory . '/archivos-*.zip') ?: [] as $previous) {
                if ($previous !== $cached) @unlink($previous);
            }
            foreach (glob($this->cacheDirectory . '/parcial-*') ?: [] as $previous) @unlink($previous);
        } finally {
            if ($writer !== null) $writer->abort();
            if ($partial !== null) @unlink($partial);
            flock($lock, LOCK_UN);
            fclose($lock);
        }
        return $count;
    }

    private function copyArchive(string $source, string $destination): void
    {
        $input = fopen($source, 'rb');
        if ($input === false) throw new RuntimeException('No se pudo leer la copia de archivos.');
        $output = null;
        try {
            $output = fopen($destination, 'xb');
            if ($output === false) throw new RuntimeException('No se pudo preparar el ZIP para descargar. Revisa el espacio libre.');
            $size = max(1, (int)fstat($input)['size']);
            $done = 0;
            while ($done < $size) {
                $copied = stream_copy_to_stream($input, $output, min(4 * 1024 * 1024, $size - $done));
                if ($copied === false || $copied === 0) throw new RuntimeException('No se pudo copiar el ZIP. Revisa el espacio libre.');
                $done += $copied;
                $this->report(90 + 5 * $done / $size, 'Preparando la copia de los archivos…');
            }
            if (!fflush($output)) throw new RuntimeException('No se pudo guardar el ZIP. Revisa el espacio libre.');
        } finally {
            fclose($input);
            if (is_resource($output)) fclose($output);
        }
    }

    /** Una instantánea independiente por base, sin competir por metadatos de MySQL. */
    private function exportDatabases(array $jobs): void
    {
        $executable = $this->dumpExecutable();
        if ($executable === null || !function_exists('proc_open')) throw new RuntimeException('El servidor necesita mysqldump y proc_open para exportar la base de datos.');
        // DATA_LENGTH es aproximado; el SQL puede crecer por las sentencias y los datos binarios.
        $weights = array_map(static fn(array $job): int => max(65536, (int)$job['estimated_bytes']), $jobs);
        $total = array_sum($weights);
        $done = 0;
        foreach ($jobs as $index => $job) {
            $settings = $job['settings'];
            $command = [$executable, '--no-defaults', '--protocol=TCP', '--host=' . $settings['host'], '--port=' . $settings['port'],
                '--user=' . $settings['user'], '--default-character-set=' . $settings['charset'], '--single-transaction', '--quick',
                '--skip-lock-tables', '--hex-blob', '--routines', '--events', '--triggers', '--no-tablespaces', '--databases', '--', $settings['database']];
            $environment = getenv();
            $environment['MYSQL_PWD'] = $settings['password'];
            $log = $job['path'] . '.error';
            $process = proc_open($command, [0=>['pipe', 'r'], 1=>['file', $job['path'], 'wb'], 2=>['file', $log, 'wb']], $pipes, null, $environment, ['bypass_shell'=>true]);
            if (!is_resource($process)) throw new RuntimeException('No se pudo iniciar la exportación MySQL.');
            fclose($pipes[0]);
            $stage = 'Exportando las bases de datos (' . ($index + 1) . '/' . count($jobs) . ')…';
            $exit = $this->waitForProcess($process, function () use ($job, $weights, $index, $done, $total, $stage): void {
                clearstatcache(true, $job['path']);
                $bytes = is_file($job['path']) ? (int)filesize($job['path']) : 0;
                $fraction = min(0.95, $bytes / ($weights[$index] * 1.25));
                $this->report(5 + 50 * ($done + $weights[$index] * $fraction) / $total, $stage);
            });
            if ($exit !== 0) throw new RuntimeException('No se pudo exportar la base ' . $settings['database'] . '. Revisa la conexión y los permisos de lectura, vistas, rutinas, eventos y disparadores de MySQL.');
            $done += $weights[$index];
            $this->report(5 + 50 * $done / $total, $stage);
            @unlink($log);
        }
    }

    /** PHP 8.0 puede devolver -1 en proc_close después de consultar el estado final. */
    private function waitForProcess($process, callable $tick): int
    {
        try {
            do {
                $status = proc_get_status($process);
                $tick();
                if (!$status['running']) break;
                usleep(200000);
            } while (true);
            $closed = proc_close($process);
            return $status['exitcode'] >= 0 ? $status['exitcode'] : $closed;
        } finally {
            if (is_resource($process)) { proc_terminate($process); proc_close($process); }
        }
    }

    private function excluded(string $path, bool $directory): bool
    {
        $parts = explode('/', strtolower($path));
        if (array_intersect($parts, ['.git', '.agents', '.codex', '.aws', '.ssh', '.playwright-cli', '.vscode', '.idea', 'node_modules', '__pycache__'])) return true;
        if (in_array($parts[0], ['tmp', 'output'], true)) return true;
        if ($directory) return false;
        if ($path === '.env' || (str_starts_with(strtolower($path), '.env.') && $path !== '.env.example')) return true;
        if (preg_match('/(?:^|\/)(?:desktop\.ini|thumbs\.db)$|\.(?:log|pyc)$/i', $path)) return true;
        // SQL de instalación/migraciones y paquetes de la tienda sí se conservan.
        if (count($parts) === 1) return (bool)preg_match('/\.(?:sql(?:\.gz)?|bak|backup)$|^guagua-xampp-.*\.zip$/i', $path);
        return str_starts_with(strtolower($path), 'reportes/bd/') && (bool)preg_match('/\.(?:sql(?:\.gz)?|zip|bak|backup)$/i', $path);
    }

    private function portableEnvironment(): string
    {
        $values = [];
        // Conservar opciones y claves de cifrado: las conexiones de origen se sustituyen.
        $keys = array_keys(getenv());
        foreach (['.env', '.env.example'] as $name) {
            $path = $this->root . '/' . $name;
            if (is_file($path) && preg_match_all('/^\s*(?:export\s+)?(GUAGUA_[A-Z0-9_]+)\s*=/m', (string)file_get_contents($path), $matches)) $keys = array_merge($keys, $matches[1]);
        }
        foreach (array_unique($keys) as $key) {
            if (!str_starts_with($key, 'GUAGUA_') || str_starts_with($key, 'GUAGUA_DB_')) continue;
            $value = GuaguaDatabase::environment($key);
            if ($value !== false) $values[$key] = $value;
        }
        $settings = GuaguaDatabase::settings();
        $values = array_replace($values, [
            'GUAGUA_APP_ENV'=>'local', 'GUAGUA_DB_HOST'=>'localhost', 'GUAGUA_DB_PORT'=>'3306', 'GUAGUA_DB_USER'=>'root',
            'GUAGUA_DB_PASSWORD'=>'', 'GUAGUA_DB_NAME'=>$settings['database'], 'GUAGUA_DB_CHARSET'=>$settings['charset'],
            'GUAGUA_TIMEZONE'=>$settings['timezone'], 'GUAGUA_DB_TIME_ZONE'=>$settings['time_zone'],
            'GUAGUA_DB_TIMEOUT'=>'5', 'GUAGUA_PUBLIC_URL'=>'http://localhost/guagua', 'GUAGUA_ATTENDANCE_FILE'=>'',
        ]);
        foreach (['edutask', 'prompt', 'moodle'] as $profile) $values['GUAGUA_DB_' . strtoupper($profile) . '_NAME'] = GuaguaDatabase::settings($profile)['database'];
        $text = "# Configuración para XAMPP. Si MySQL usa otro puerto o contraseña, ajústelos aquí.\n";
        foreach ($values as $key => $value) $text .= $key . '=' . json_encode((string)$value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR) . "\n";
        return $text;
    }

    private function instructions(array $databases, array $omitted): string
    {
        $listing = '';
        foreach ($databases as $database) $listing .= '  - ' . $database['archivo'] . ' => ' . $database['base'] . "\n";
        $missing = $omitted ? implode("\n", $omitted) : 'Ninguna de las bases seleccionadas.';
        return "GUAGUA - TRASLADO A XAMPP\nGenerado: " . date('Y-m-d H:i:s T') . "\n\n"
            . "1. Instala XAMPP con PHP 8 o superior. Inicia Apache y MySQL.\n"
            . "2. Extrae TODO el ZIP en una carpeta de tu equipo.\n"
            . "3. Copia la carpeta guagua a C:\\xampp\\htdocs\\guagua (o al htdocs de tu XAMPP).\n"
            . "4. Ejecuta IMPORTAR_BD.bat desde la carpeta extraída e indica la ruta de XAMPP y el puerto de MySQL.\n"
            . "   El asistente usa root y pide la contraseña. En XAMPP estándar basta pulsar Enter.\n"
            . "   También puedes abrir http://localhost/phpmyadmin e importar cada SQL de base_de_datos.\n"
            . "   Los SQL crean y seleccionan sus bases automáticamente. Para archivos grandes usa el asistente.\n"
            . "5. Abre http://localhost/guagua e inicia sesión con las mismas cuentas de esta copia.\n\n"
            . "CONEXIÓN\nLa carpeta ya incluye .env para localhost:3306, usuario root, sin contraseña.\n"
            . "Si tu MySQL tiene otra contraseña, usuario o puerto (por ejemplo 7000), edita guagua/.env.\n"
            . "Si Apache usa otro puerto, inclúyelo en la URL y en GUAGUA_PUBLIC_URL.\n"
            . "No ejecutes el instalador: la estructura y las cuentas están en los SQL.\n\n"
            . "BASES INCLUIDAS\n" . $listing . "\nBASES OMITIDAS\n" . $missing . "\n\n"
            . "RESTAURACIÓN\nImporta en una instalación nueva. Los SQL reemplazan tablas con los mismos nombres.\n"
            . "Si el destino ya tiene datos, respáldalos antes de importar.\n"
            . "Se incluyen tablas, datos, vistas, disparadores, procedimientos, funciones y eventos.\n"
            . "Se recomienda la misma versión de MySQL/MariaDB o una compatible con el origen.\n\n"
            . "ALCANCE\nIncluye los archivos locales, cargas, dependencias PHP instaladas y paquetes de la tienda.\n"
            . "Excluye Git, herramientas de desarrollo, node_modules, cachés, temporales, registros y copias SQL antiguas.\n"
            . "No incluye archivos fuera de Guagua, enlaces simbólicos, servicios externos ni datos guardados solo en el navegador.\n"
            . "Las integraciones externas pueden necesitar nueva autorización y sus rutas deben revisarse en el otro equipo.\n"
            . "El ZIP contiene datos y claves privadas de la instalación. Guárdalo en un lugar privado.\n";
    }

    public function cleanup(): void
    {
        if ($this->temporary === null) return;
        foreach (glob($this->temporary . '/*') ?: [] as $file) if (is_file($file) || is_link($file)) @unlink($file);
        @rmdir($this->temporary);
        $this->temporary = null;
    }
}
