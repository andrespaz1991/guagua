<?php
/**
 * Factoría única MySQLi/PDO. No inicia sesión, no verifica permisos ni consulta
 * tablas de negocio. Cada llamada crea una conexión que su propietario puede
 * cerrar, sin invalidar las conexiones de otros módulos.
 */
final class GuaguaDatabase
{
    private static ?array $configurationValues = null;
    private static array $requestMysqliConnections = [];
    private static array $requestPdoConnections = [];
    public static function configuration(): array
    {
        if (self::$configurationValues !== null) return self::$configurationValues;
        $config = require __DIR__ . '/database.config.php';
        if (!is_array($config)) throw new RuntimeException('La configuración central de base de datos no es válida.');
        return self::$configurationValues = $config;
    }

    private static ?array $environmentValues = null;

    public static function environment(string $key)
    {
        $value = getenv($key);
        if ($value !== false) return $value;
        if (self::$environmentValues === null) {
            self::$environmentValues = [];
            $path = dirname(__DIR__) . '/.env';
            if (is_file($path)) {
                foreach (file($path, FILE_IGNORE_NEW_LINES) ?: [] as $line) {
                    $line = ltrim($line, "\xEF\xBB\xBF");
                    if (!preg_match('/^\s*(?:export\s+)?([A-Za-z_][A-Za-z0-9_]*)\s*=\s*(.*?)\s*$/D', $line, $match)) continue;
                    $raw = $match[2];
                    if (str_starts_with($raw, '"')) {
                        $decoded = json_decode($raw, true);
                        if (!is_string($decoded)) throw new RuntimeException('Hay un valor entre comillas inválido en .env.');
                        $raw = $decoded;
                    } elseif (str_starts_with($raw, "'") && str_ends_with($raw, "'")) {
                        $raw = substr($raw, 1, -1);
                    } else {
                        $raw = preg_replace('/\s+#.*$/', '', $raw);
                    }
                    self::$environmentValues[$match[1]] = $raw;
                }
            }
        }
        return self::$environmentValues[$key] ?? false;
    }

    /** Used by the legacy installer; preserves unrelated settings and remote profiles. */
    public static function saveEnvironment(array $values): void
    {
        $path = dirname(__DIR__) . '/.env';
        $lines = is_file($path) ? file($path, FILE_IGNORE_NEW_LINES) : [];
        foreach ($values as $key => $value) {
            if (!preg_match('/^GUAGUA_[A-Z0-9_]+$/D', $key)) throw new InvalidArgumentException('Nombre de opción no válido.');
            $replacement = $key . '=' . json_encode((string)$value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
            $found = false;
            foreach ($lines as &$line) {
                if (preg_match('/^\s*(?:export\s+)?' . preg_quote($key, '/') . '\s*=/', $line)) {
                    $line = $replacement; $found = true;
                }
            }
            unset($line);
            if (!$found) $lines[] = $replacement;
        }
        if (file_put_contents($path, implode(PHP_EOL, $lines) . PHP_EOL, LOCK_EX) === false) throw new RuntimeException('No se pudo guardar la configuración de conexión.');
        self::$environmentValues = null;
    }

    public static function settings(string $profile = 'default', array $overrides = []): array
    {
        $config = self::configuration();
        $profileSettings = [];
        if ($profile !== 'default') {
            if (!isset($config['profiles'][$profile])) throw new InvalidArgumentException('El perfil de base de datos no está configurado.');
            $profileSettings = $config['profiles'][$profile];
        }
        $environment = $profileSettings['environment'] ?? self::environment('GUAGUA_APP_ENV');
        $environment = $environment !== false && $environment !== '' ? $environment : ($config['environment'] ?? 'auto');
        if ($environment === 'auto') {
            $server = strtolower((string)($_SERVER['SERVER_NAME'] ?? ''));
            $environment = PHP_SAPI === 'cli' || in_array($server, ['localhost', '127.0.0.1', '::1'], true) ? 'local' : 'production';
        }
        if (!in_array($environment, ['local', 'production'], true)) throw new RuntimeException('El entorno de base de datos debe ser local o production.');
        $settings = $config[$environment] ?? [];
        $settings += ['host' => '', 'port' => 3306, 'user' => '', 'password' => '', 'database' => 'guagua'];
        // Production-only profiles do not inherit the local .env credentials.
        if ($profile === 'default' || !isset($profileSettings['environment'])) {
            foreach (['host' => 'HOST', 'port' => 'PORT', 'user' => 'USER', 'password' => 'PASSWORD', 'database' => 'NAME'] as $key => $suffix) {
                $value = self::environment('GUAGUA_DB_' . $suffix);
                if ($value !== false) $settings[$key] = $value;
            }
        }
        unset($profileSettings['environment']);
        $settings = array_replace($settings, $profileSettings);
        if ($profile !== 'default') {
            foreach (['host' => 'HOST', 'port' => 'PORT', 'user' => 'USER', 'password' => 'PASSWORD', 'database' => 'NAME'] as $key => $suffix) {
                $value = self::environment('GUAGUA_DB_' . strtoupper($profile) . '_' . $suffix);
                if ($value !== false) $settings[$key] = $value;
            }
        }
        $settings['environment'] = $environment;
        $settings['charset'] = self::environment('GUAGUA_DB_CHARSET') ?: ($config['charset'] ?? 'utf8mb4');
        $settings['timezone'] = self::environment('GUAGUA_TIMEZONE') ?: ($config['timezone'] ?? 'America/Bogota');
        $settings['time_zone'] = self::environment('GUAGUA_DB_TIME_ZONE') ?: ($config['time_zone'] ?? '-05:00');
        $settings['connect_timeout'] = (int)(self::environment('GUAGUA_DB_TIMEOUT') ?: ($config['connect_timeout'] ?? 5));
        $settings = array_replace($settings, $overrides);
        // Compatibility with the previous GUAGUA_DB_HOST=host:port syntax.
        $host = (string)$settings['host'];
        if (preg_match('/^\[([^\]]+)\]:(\d+)$/D', $host, $match) || preg_match('/^([^:]+):(\d+)$/D', $host, $match)) {
            $settings['host'] = $match[1];
            if (!array_key_exists('port', $overrides) && (array_key_exists('host', $overrides) || getenv('GUAGUA_DB_PORT') === false)) $settings['port'] = (int)$match[2];
        }
        if (str_starts_with((string)$settings['host'], '[') && str_ends_with((string)$settings['host'], ']')) $settings['host'] = substr($settings['host'], 1, -1);
        if (filter_var($settings['port'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 65535]]) === false) throw new InvalidArgumentException('El puerto de base de datos no es válido.');
        $settings['port'] = (int)$settings['port'];
        if (!preg_match('/^[a-zA-Z0-9_]+$/D', (string)$settings['charset'])) throw new InvalidArgumentException('La codificación de base de datos no es válida.');
        foreach (['host', 'database'] as $field) {
            if (preg_match('/[;\x00\r\n]/', (string)$settings[$field])) throw new InvalidArgumentException('La configuración de base de datos contiene un valor no válido.');
        }
        if (!preg_match('/^[+-](?:(?:0\d|1[0-3]):[0-5]\d|14:00)$/D', (string)$settings['time_zone'])) throw new InvalidArgumentException('La zona horaria de base de datos no es válida.');
        if (!in_array($settings['timezone'], timezone_identifiers_list(), true)) throw new InvalidArgumentException('La zona horaria de la aplicación no es válida.');
        $settings['connect_timeout'] = max(1, min(30, (int)$settings['connect_timeout']));
        return $settings;
    }

    private static function validateConnection(array $settings): void
    {
        if ($settings['host'] === '' || $settings['user'] === '') throw new RuntimeException('Configure la base de datos en .env o en las variables GUAGUA_DB_*.');
    }

    public static function dsn(string $profile = 'default', array $overrides = []): string
    {
        $settings = self::settings($profile, $overrides);
        return self::dsnFromSettings($settings);
    }

    private static function dsnFromSettings(array $settings): string
    {
        return 'mysql:host=' . $settings['host'] . ';port=' . $settings['port']
            . ($settings['database'] !== '' ? ';dbname=' . $settings['database'] : '') . ';charset=' . $settings['charset'];
    }

    public static function mysqli(string $profile = 'default', array $overrides = []): mysqli
    {
        $connection = null;
        try {
            $settings = self::settings($profile, $overrides);
            self::validateConnection($settings);
            $connection = mysqli_init();
            if (!$connection) throw new RuntimeException('No se pudo inicializar MySQLi.');
            $connection->options(MYSQLI_OPT_CONNECT_TIMEOUT, $settings['connect_timeout']);
            if (!@$connection->real_connect($settings['host'], $settings['user'], $settings['password'], $settings['database'] ?: null, $settings['port'])) throw new RuntimeException('No se pudo abrir la conexión.');
            if (!$connection->set_charset($settings['charset'])) throw new RuntimeException('No se pudo configurar la codificación.');
            if (!$connection->query("SET time_zone = '" . $settings['time_zone'] . "'")) throw new RuntimeException('No se pudo configurar la zona horaria.');
            date_default_timezone_set($settings['timezone']);
            return $connection;
        } catch (Throwable $error) {
            if ($connection instanceof mysqli) { try { $connection->close(); } catch (Throwable $ignored) {} }
            error_log('[Guagua DB/MySQLi] ' . get_class($error) . ' (' . $error->getCode() . ')');
            throw new mysqli_sql_exception('No fue posible conectar con la base de datos. Verifique la configuración central y la disponibilidad del servidor.', (int)$error->getCode());
        }
    }

    public static function pdo(string $profile = 'default', array $options = [], array $overrides = []): PDO
    {
        try {
            $settings = self::settings($profile, $overrides);
            self::validateConnection($settings);
            $defaults = [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false, PDO::ATTR_TIMEOUT => $settings['connect_timeout']];
            $connection = new PDO(self::dsnFromSettings($settings), $settings['user'], $settings['password'], array_replace($defaults, $options));
            $connection->exec("SET time_zone = '" . $settings['time_zone'] . "'");
            date_default_timezone_set($settings['timezone']);
            return $connection;
        } catch (Throwable $error) {
            error_log('[Guagua DB/PDO] ' . get_class($error) . ' (' . $error->getCode() . ')');
            throw new PDOException('No fue posible conectar con la base de datos. Verifique la configuración central y la disponibilidad del servidor.', (int)$error->getCode());
        }
    }

    /** Adaptadores del sitio: reutilización solo durante esta petición PHP. */
    public static function requestMysqli(string $profile = 'default'): mysqli
    {
        $key = hash('sha256',json_encode(self::settings($profile),JSON_THROW_ON_ERROR));
        $connection = self::$requestMysqliConnections[$key] ?? null;
        if ($connection instanceof mysqli) {
            try { if ($connection->thread_id > 0) return $connection; }
            catch (Throwable $closed) { unset(self::$requestMysqliConnections[$key]); }
        }
        return self::$requestMysqliConnections[$key] = self::mysqli($profile);
    }
    public static function requestPdo(string $profile = 'default', array $options = []): PDO
    {
        $key = hash('sha256',json_encode([self::settings($profile),$options],JSON_THROW_ON_ERROR));
        return self::$requestPdoConnections[$key] ?? (self::$requestPdoConnections[$key] = self::pdo($profile,$options));
    }
}

function guagua_db_mysqli(string $profile = 'default', array $overrides = []): mysqli
{
    return GuaguaDatabase::mysqli($profile, $overrides);
}

function guagua_db_pdo(string $profile = 'default', array $options = []): PDO
{
    return GuaguaDatabase::pdo($profile, $options);
}
function guagua_db_request(string $profile = 'default'): mysqli { return GuaguaDatabase::requestMysqli($profile); }
function guagua_db_pdo_request(string $profile = 'default', array $options = []): PDO { return GuaguaDatabase::requestPdo($profile,$options); }
