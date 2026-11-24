<?php
declare(strict_types=1);

/** Estado pequeño y privado; no guarda SQL, rutas del paquete ni credenciales. */
final class GuaguaPortableProgress
{
    private string $directory;
    private string $path;
    private float $startedAt = 0;
    private float $lastWrite = 0;
    private array $state = [];
    private bool $finished = false;

    public function __construct(string $root, string $id, string $owner)
    {
        if (!preg_match('/^[a-f0-9]{32}$/D', $id) || $owner === '') throw new InvalidArgumentException('Identificador de preparación no válido.');
        $root = realpath($root);
        if ($root === false) throw new InvalidArgumentException('Carpeta de preparación no válida.');
        $this->directory = rtrim(sys_get_temp_dir(), '/\\') . '/guagua-progress-' . substr(hash('sha256', $root), 0, 24);
        $this->path = $this->directory . '/' . hash_hmac('sha256', $id, $owner) . '.json';
    }

    public function begin(): void
    {
        if (is_link($this->directory)) throw new RuntimeException('No se pudo iniciar el seguimiento de la copia.');
        if (!is_dir($this->directory) && !mkdir($this->directory, 0700) && !is_dir($this->directory)) throw new RuntimeException('No se pudo iniciar el seguimiento de la copia.');
        // Solo se eliminan estados antiguos de este sitio, nunca los paquetes.
        foreach (glob($this->directory . '/*.json') ?: [] as $old) {
            if (filemtime($old) < time() - 86400) @unlink($old);
        }
        $this->startedAt = microtime(true);
        $this->state = ['status'=>'preparing', 'percent'=>0, 'stage'=>'Comprobando las bases de datos…'];
        $this->write();
        register_shutdown_function(function (): void {
            if (!$this->finished) $this->fail();
        });
    }

    public function report(float $percent, string $stage): void
    {
        if ($this->finished) return;
        $percent = max($this->state['percent'], min(99, (int)floor($percent)));
        $changedStage = $stage !== $this->state['stage'];
        $this->state['percent'] = $percent;
        $this->state['stage'] = $stage;
        if ($changedStage || microtime(true) - $this->lastWrite >= 0.5) $this->write();
    }

    public function complete(): void
    {
        $this->finished = true;
        $this->state = ['status'=>'ready', 'percent'=>100, 'stage'=>'ZIP listo. Iniciando la descarga…'];
        $this->write();
    }

    public function fail(): void
    {
        if ($this->finished || $this->startedAt === 0.0) return;
        $this->finished = true;
        $this->state['status'] = 'error';
        $this->state['stage'] = 'No se pudo preparar el ZIP. Vuelve a intentarlo.';
        try { $this->write(); } catch (Throwable $ignored) { error_log('[Guagua portátil] No se pudo guardar el estado de error.'); }
    }

    public function read(): ?array
    {
        if (!is_file($this->path) || filemtime($this->path) < time() - 86400) return null;
        $file = fopen($this->path, 'rb');
        if ($file === false) return null;
        try {
            if (!flock($file, LOCK_SH)) return null;
            $data = stream_get_contents($file);
            flock($file, LOCK_UN);
            $state = json_decode((string)$data, true);
            return is_array($state) ? $state : null;
        } finally { fclose($file); }
    }

    private function write(): void
    {
        $now = microtime(true);
        $elapsed = max(0, $now - $this->startedAt);
        $percent = $this->state['percent'];
        // Es una estimación por trabajo realizado, nunca un contador que avance solo.
        $remaining = $elapsed >= 3 && $percent >= 2 ? (int)ceil($elapsed * (100 - $percent) / $percent) : null;
        if ($this->state['status'] === 'ready') $remaining = 0;
        if ($this->state['status'] === 'error') $remaining = null;
        $state = $this->state + ['elapsed_seconds'=>(int)$elapsed, 'remaining_seconds'=>$remaining, 'updated_at'=>time()];
        $file = fopen($this->path, 'c+b');
        if ($file === false) throw new RuntimeException('No se pudo guardar el avance de la copia.');
        try {
            $json = json_encode($state, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
            if (!flock($file, LOCK_EX)) throw new RuntimeException('No se pudo guardar el avance de la copia.');
            rewind($file);
            if (!ftruncate($file, 0) || fwrite($file, $json) !== strlen($json) || !fflush($file)) throw new RuntimeException('No se pudo guardar el avance de la copia.');
            flock($file, LOCK_UN);
            $this->lastWrite = $now;
        } finally { fclose($file); }
    }
}
