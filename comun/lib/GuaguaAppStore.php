<?php
declare(strict_types=1);

/** Ciclo local de aplicaciones: paquete verificado, retirada real y restauración. */
final class GuaguaAppStore
{
    private string $root;
    private string $storage;
    private array $definitions;

    public function __construct(string $root)
    {
        $real = realpath($root);
        if ($real === false || !is_dir($real . '/apps')) throw new RuntimeException('La raíz de Guagua no es válida.');
        $this->root = str_replace('\\', '/', $real);
        $this->storage = $this->root . '/sga-data/tienda';
        $this->definitions = require dirname(__DIR__) . '/tienda_catalogo.php';
    }

    private function state(): array
    {
        $file = $this->storage . '/catalogo.json';
        if (!is_file($file)) return ['version'=>1, 'apps'=>[]];
        $state = json_decode((string)file_get_contents($file), true);
        if (!is_array($state) || ($state['version'] ?? null) !== 1 || !is_array($state['apps'] ?? null)) {
            throw new RuntimeException('El catálogo no se puede leer. No se modificarán aplicaciones.');
        }
        return $state;
    }

    private function save(array $state): void
    {
        $temp = $this->storage . '/catalogo-' . bin2hex(random_bytes(8)) . '.tmp';
        if (file_put_contents($temp, json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR), LOCK_EX) === false) {
            throw new RuntimeException('No se pudo guardar el catálogo.');
        }
        if (!rename($temp, $this->storage . '/catalogo.json')) {
            @unlink($temp);
            throw new RuntimeException('No se pudo actualizar el catálogo.');
        }
    }

    private function prepareStorage(): void
    {
        foreach ([$this->root . '/sga-data', $this->storage, $this->storage . '/paquetes', $this->storage . '/trabajo'] as $dir) {
            if (is_link($dir)) throw new RuntimeException('El almacenamiento no puede ser un enlace.');
            if (!is_dir($dir) && !mkdir($dir, 0700)) throw new RuntimeException('No se pudo preparar el almacenamiento privado.');
        }
        $deny = "Require all denied\n";
        if (file_put_contents($this->storage . '/.htaccess', $deny, LOCK_EX) === false) throw new RuntimeException('No se pudo proteger el almacenamiento.');
    }

    private function locked(callable $action)
    {
        if (!class_exists('ZipArchive')) throw new RuntimeException('Activa la extensión ZIP de PHP para administrar las apps.');
        $this->prepareStorage();
        $handle = fopen($this->storage . '/tienda.lock', 'c');
        if (!$handle || !flock($handle, LOCK_EX | LOCK_NB)) {
            if ($handle) fclose($handle);
            throw new RuntimeException('Hay otra operación en curso. Espera y vuelve a intentarlo.');
        }
        try { return $action(); }
        finally { flock($handle, LOCK_UN); fclose($handle); }
    }

    private function folderPath(string $folder): string
    {
        if ($folder === '' || $folder === '.' || $folder === '..' || preg_match('~[/\\\\:\x00-\x1f]~', $folder)) {
            throw new RuntimeException('Carpeta de aplicación no válida.');
        }
        $path = $this->root . '/apps/' . $folder;
        if (is_link($this->root . '/apps') || is_link($path)) throw new RuntimeException('No se admiten aplicaciones enlazadas.');
        return $path;
    }

    private function menuMetadata(string $folder, array $menu): array
    {
        $item = [];
        $favorite = false;
        foreach ($menu as $row) {
            $url = rawurldecode(str_replace('\\', '/', (string)($row['menu_url'] ?? '')));
            if (!preg_match('~(?:^|/)apps/' . preg_quote($folder, '~') . '(?:/|[?#]|$)~i', $url)) continue;
            if (!$item) $item = $row;
            $favorite = $favorite || (string)($row['fav'] ?? '0') === '1';
        }
        if ($item) $item['favorita'] = $favorite;
        return $item;
    }

    public function catalog(array $menu = []): array
    {
        $state = $this->state();
        $apps = [];
        foreach (scandir($this->root . '/apps') ?: [] as $folder) {
            if ($folder[0] === '.' || preg_match('/ - copia(?: \(\d+\))?$/i', $folder)) continue;
            $path = $this->folderPath($folder);
            if (!is_dir($path)) continue;
            $entry = '';
            foreach (['index.php', 'index.html', 'index2.php', 'static/index.html'] as $candidate) {
                if (is_file($path . '/' . $candidate)) { $entry = $candidate; break; }
            }
            if ($entry === '') continue;
            $id = substr(hash('sha256', strtolower($folder)), 0, 24);
            $definition = $this->definitions[strtolower($folder)] ?? [];
            $item = $this->menuMetadata($folder, $menu);
            $apps[$id] = [
                'id'=>$id, 'carpeta'=>$folder,
                'nombre'=>$definition['nombre'] ?? ($item['menu_item_name'] ?? ucwords(str_replace(['_', '-'], ' ', $folder))),
                'descripcion'=>$definition['descripcion'] ?? (trim((string)($item['menu_description'] ?? '')) ?: 'Una herramienta de Guagua para tu día a día.'),
                'categoria'=>$definition['categoria'] ?? 'Educación', 'icono'=>$definition['icono'] ?? '🧩',
                'protegida'=>$definition['protegida'] ?? '', 'estado'=>'instalada',
                'favorita'=>$item['favorita'] ?? false,
                'menu_id'=>isset($item['menu_item_id']) ? (int)$item['menu_item_id'] : null,
                'ruta'=>'apps/' . rawurlencode($folder) . '/' . $entry,
                'bytes'=>$state['apps'][$id]['bytes'] ?? null, 'paquete_bytes'=>0,
            ];
        }
        foreach ($state['apps'] as $id => $app) {
            if ($app['estado'] === 'disponible' && !isset($apps[$id])) {
                $item = $this->menuMetadata($app['carpeta'], $menu);
                $app['favorita'] = $item['favorita'] ?? ($app['favorita'] ?? false);
                $app['menu_id'] = isset($item['menu_item_id']) ? (int)$item['menu_item_id'] : null;
                $app['reinstalable'] = is_file($this->storage . '/paquetes/' . basename($app['paquete']));
                unset($app['sha256'], $app['paquete'], $app['pendiente']);
                $apps[$id] = $app;
            }
        }
        uasort($apps, static fn($a, $b) => strnatcasecmp($a['nombre'], $b['nombre']));
        return array_values($apps);
    }

    private function find(string $id, array $menu = []): array
    {
        if (!preg_match('/^[a-f0-9]{24}$/D', $id)) throw new RuntimeException('Aplicación no válida.');
        foreach ($this->catalog($menu) as $app) if ($app['id'] === $id) return $app;
        throw new RuntimeException('La aplicación no está en el catálogo.');
    }

    /** No sigue enlaces ni puntos de montaje externos al árbol que se retira. */
    private function files(string $directory): array
    {
        $base = realpath($directory);
        if ($base === false || is_link($directory)) throw new RuntimeException('La carpeta ya no está disponible.');
        $base = str_replace('\\', '/', $base);
        $files = [];
        $walk = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::SELF_FIRST);
        foreach ($walk as $file) {
            $real = $file->getRealPath();
            if ($file->isLink() || $real === false || strncasecmp(str_replace('\\', '/', $real), $base . '/', strlen($base) + 1) !== 0) {
                throw new RuntimeException('La app contiene enlaces o rutas externas; no se puede retirar automáticamente.');
            }
            $relative = substr(str_replace('\\', '/', $file->getPathname()), strlen(str_replace('\\', '/', $directory)) + 1);
            if (!$file->isDir() && !$file->isFile()) throw new RuntimeException('La app contiene un archivo especial.');
            $files[$relative] = $file->isDir() ? null : $file->getSize();
        }
        return $files;
    }

    public function details(string $id, array $menu = []): array
    {
        $app = $this->find($id, $menu);
        if ($app['estado'] === 'instalada') {
            $files = $this->files($this->folderPath($app['carpeta']));
            $app['bytes'] = array_sum($files);
            $app['archivos'] = count(array_filter($files, static fn($size) => $size !== null));
        }
        return $app;
    }

    private function archive(string $directory, string $output, array $app, ?string $sql = null): array
    {
        $files = $this->files($directory);
        $zip = new ZipArchive();
        if ($zip->open($output, ZipArchive::CREATE | ZipArchive::EXCL) !== true) throw new RuntimeException('No se pudo crear el paquete. Revisa el espacio disponible.');
        $hashes = [];
        try {
            foreach ($files as $relative => $size) {
                if ($size === null) {
                    if (!$zip->addEmptyDir('app/' . $relative)) throw new RuntimeException('No se pudo archivar una carpeta.');
                } else {
                    $hashes[$relative] = hash_file('sha256', $directory . '/' . $relative);
                    if ($hashes[$relative] === false || !$zip->addFile($directory . '/' . $relative, 'app/' . $relative)) throw new RuntimeException('No se pudo leer un archivo de la app.');
                }
            }
            $manifest = ['version'=>1, 'app'=>$app['id'], 'carpeta'=>$app['carpeta'], 'fecha'=>gmdate('c'), 'sha256'=>$hashes];
            if (!$zip->addFromString('guagua-package.json', json_encode($manifest, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE))) throw new RuntimeException('No se pudo escribir el manifiesto.');
            if ($sql !== null && !$zip->addFile($sql, 'datos/base-de-datos.sql')) throw new RuntimeException('No se pudo incluir la base de datos.');
            if (!$zip->addFromString('LEEME.txt', "Copia local de " . $app['nombre'] . ".\napp/: archivos completos de la aplicación.\nLa base compartida y los datos de otros servicios permanecen en Guagua.\nEl respaldo SQL, si existe, incluye la base Guagua completa y el perfil propio de la app. Su restauración es manual y debe revisarse antes de importar.\nNo incluye archivos externos a la carpeta de la app, servicios remotos ni datos guardados solo en el navegador.\n")) throw new RuntimeException('No se pudo incluir la descripción.');
            if (!$zip->close()) throw new RuntimeException('No se pudo completar el ZIP.');
        } catch (Throwable $error) {
            try { $zip->close(); } catch (Throwable $ignored) {}
            @unlink($output); throw $error;
        }
        // Comprobar cada archivo dentro del ZIP antes de retirar su original.
        if ($zip->open($output, ZipArchive::CHECKCONS) !== true) throw new RuntimeException('La verificación del paquete falló.');
        try {
            foreach ($hashes as $relative => $expected) {
                $stream = $zip->getStream('app/' . $relative);
                if (!$stream) throw new RuntimeException('El paquete está incompleto.');
                $hash = hash_init('sha256'); hash_update_stream($hash, $stream); fclose($stream);
                if (!hash_equals($expected, hash_final($hash))) throw new RuntimeException('Un archivo cambió durante la copia. Inténtalo de nuevo.');
            }
        } finally { $zip->close(); }
        return ['bytes'=>array_sum($files), 'archivos'=>count($hashes), 'paquete_bytes'=>filesize($output)];
    }

    private function removeTree(string $path): void
    {
        // Solo carpetas temporales generadas dentro del almacén privado.
        $base = realpath($this->storage . '/trabajo');
        $real = realpath($path);
        if ($base === false || $real === false || is_link($path) || strncasecmp($real, $base . DIRECTORY_SEPARATOR, strlen($base) + 1) !== 0) throw new RuntimeException('Ruta de limpieza no válida.');
        $this->files($path);
        $walk = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST);
        foreach ($walk as $file) {
            if (!($file->isDir() ? rmdir($file->getPathname()) : unlink($file->getPathname()))) throw new RuntimeException('No se pudo completar la limpieza de archivos.');
        }
        if (!rmdir($path)) throw new RuntimeException('No se pudo limpiar la carpeta temporal.');
    }

    public function uninstall(string $id, array $menu = []): array
    {
        return $this->locked(function () use ($id, $menu) {
            $app = $this->find($id, $menu);
            if ($app['estado'] !== 'instalada') throw new RuntimeException('La app ya está desinstalada.');
            if ($app['protegida'] !== '') throw new RuntimeException($app['protegida']);
            $path = $this->folderPath($app['carpeta']);
            $this->files($path); // Validar completamente antes de mover.
            $suffix = $id . '-' . bin2hex(random_bytes(8));
            $stage = $this->storage . '/trabajo/' . $suffix;
            $package = $suffix . '.zip';
            $archive = $this->storage . '/paquetes/' . $package;
            if (!rename($path, $stage)) throw new RuntimeException('No se puede retirar la app. Revisa permisos o archivos en uso.');
            try {
                $stats = $this->archive($stage, $archive, $app);
                $state = $this->state();
                $record = array_merge($app, $stats, ['estado'=>'disponible', 'paquete'=>$package, 'sha256'=>hash_file('sha256', $archive), 'pendiente'=>$suffix, 'limpieza_pendiente'=>true, 'fecha'=>gmdate('c')]);
                $state['apps'][$id] = $record;
                $this->save($state);
            } catch (Throwable $error) {
                if (!rename($stage, $path)) throw new RuntimeException('La app quedó resguardada en el almacén privado; requiere recuperar su carpeta temporal.');
                @unlink($archive);
                throw $error;
            }
            $pending = false;
            try {
                $this->removeTree($stage);
            } catch (Throwable $error) { $pending = true; }
            if (!$pending) {
                $state['apps'][$id]['limpieza_pendiente'] = false;
                try { $this->save($state); } catch (Throwable $error) { error_log('[Guagua tienda] No se pudo actualizar el estado de limpieza.'); }
            }
            return ['message'=>$pending ? 'App desinstalada y paquete verificado. Algunos archivos en uso siguen en el almacén temporal; se intentarán limpiar al reinstalar.' : 'App desinstalada. Puedes reinstalarla desde la tienda con sus archivos y datos locales.', 'limpieza_pendiente'=>$pending, 'bytes'=>$stats['bytes'], 'paquete_bytes'=>$stats['paquete_bytes']];
        });
    }

    public function install(string $id): array
    {
        return $this->locked(function () use ($id) {
            $app = $this->find($id);
            if ($app['estado'] !== 'disponible') throw new RuntimeException('La app ya está instalada.');
            $state = $this->state(); $record = $state['apps'][$id];
            $archive = $this->storage . '/paquetes/' . basename($record['paquete']);
            if (!is_file($archive) || !hash_equals($record['sha256'], hash_file('sha256', $archive))) throw new RuntimeException('El paquete no está disponible o fue modificado. No se instalará.');
            $target = $this->folderPath($record['carpeta']);
            if (file_exists($target)) throw new RuntimeException('La carpeta de destino ya existe. No se sobrescribirá.');
            $stage = $this->storage . '/trabajo/instalar-' . bin2hex(random_bytes(12));
            if (!mkdir($stage, 0700)) throw new RuntimeException('No se pudo preparar la instalación.');
            $zip = new ZipArchive();
            try {
                if ($zip->open($archive, ZipArchive::CHECKCONS) !== true) throw new RuntimeException('Paquete dañado.');
                $manifest = json_decode((string)$zip->getFromName('guagua-package.json'), true);
                if (!is_array($manifest) || ($manifest['app'] ?? '') !== $id || ($manifest['carpeta'] ?? '') !== $record['carpeta']) throw new RuntimeException('El paquete no corresponde a la app.');
                for ($i = 0; $i < $zip->numFiles; $i++) {
                    $name = $zip->getNameIndex($i);
                    if (!is_string($name) || str_contains($name, '\\') || str_contains($name, ':') || str_contains($name, "\0") || preg_match('~(?:^|/)\.{1,2}(?:/|$)|^/~', $name)) throw new RuntimeException('El paquete contiene rutas no válidas.');
                    $opsys = 0; $attributes = 0;
                    $zip->getExternalAttributesIndex($i, $opsys, $attributes);
                    if ((($attributes >> 16) & 0170000) === 0120000) throw new RuntimeException('El paquete contiene enlaces.');
                }
                if (!$zip->extractTo($stage)) throw new RuntimeException('No se pudo extraer la app. Revisa el espacio disponible.');
                $zip->close();
                foreach ($manifest['sha256'] ?? [] as $relative => $hash) {
                    $file = $stage . '/app/' . $relative;
                    if (!is_file($file) || !hash_equals($hash, hash_file('sha256', $file))) throw new RuntimeException('No se pudieron verificar los archivos extraídos.');
                }
                if (!is_dir($stage . '/app') || !rename($stage . '/app', $target)) throw new RuntimeException('No se pudo activar la app.');
                try {
                    $state['apps'][$id]['estado'] = 'instalada';
                    $this->save($state);
                } catch (Throwable $error) { rename($target, $stage . '/app'); throw $error; }
            } catch (Throwable $error) {
                try { $zip->close(); } catch (Throwable $ignored) {}
                try { $this->removeTree($stage); } catch (Throwable $ignored) {}
                throw $error;
            }
            try { $this->removeTree($stage); } catch (Throwable $ignored) {}
            if (!empty($record['pendiente']) && preg_match('/^[a-f0-9-]+$/D', $record['pendiente'])) {
                $old = $this->storage . '/trabajo/' . $record['pendiente'];
                if (is_dir($old)) { try { $this->removeTree($old); } catch (Throwable $ignored) {} }
            }
            @unlink($archive); // La carpeta restaurada es ahora la copia activa.
            return ['message'=>'App reinstalada. Sus archivos y datos locales están disponibles de nuevo.'];
        });
    }

    /** El exportador recibe una ruta privada: nunca pasa credenciales al shell. */
    public function backup(string $id, array $menu, callable $export): string
    {
        return $this->locked(function () use ($id, $menu, $export) {
            $app = $this->find($id, $menu);
            if ($app['estado'] !== 'instalada') throw new RuntimeException('Reinstala la app para generar una copia nueva.');
            $prefix = $this->storage . '/trabajo/copia-' . bin2hex(random_bytes(16));
            try {
                $export($prefix . '.sql', $this->definitions[strtolower($app['carpeta'])]['perfil'] ?? null);
                $this->archive($this->folderPath($app['carpeta']), $prefix . '.zip', $app, $prefix . '.sql');
                return $prefix . '.zip';
            } catch (Throwable $error) { @unlink($prefix . '.zip'); throw $error; }
            finally { @unlink($prefix . '.sql'); }
        });
    }
}
