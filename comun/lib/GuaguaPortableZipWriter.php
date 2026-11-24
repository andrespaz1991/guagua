<?php
declare(strict_types=1);

/** ZIP de una pasada con la biblioteca ya usada por PhpSpreadsheet. */
final class GuaguaPortableZipWriter
{
    private ?ZipArchive $native = null;
    private ?\ZipStream\ZipStream $streaming = null;
    /** @var resource|null */
    private $output = null;

    public function __construct(string $path, bool $preferStreaming = true, bool $append = false)
    {
        if ($append) $preferStreaming = false;
        $autoload = __DIR__ . '/vendor/autoload.php';
        if ($preferStreaming && is_file($autoload)) require_once $autoload;
        if ($preferStreaming && class_exists(\ZipStream\Option\Archive::class) && function_exists('mb_check_encoding')) {
            $this->output = fopen($path, 'xb');
            if ($this->output === false) throw new RuntimeException('No se pudo crear el ZIP. Revisa el espacio libre del servidor.');
            $options = new \ZipStream\Option\Archive();
            $options->setOutputStream($this->output);
            $options->setSendHttpHeaders(false);
            $options->setZeroHeader(true);
            $options->setDeflateLevel(1);
            $this->streaming = new class(null, $options) extends \ZipStream\ZipStream {
                public function send(string $data): void
                {
                    $output = $this->opt->getOutputStream();
                    $length = strlen($data);
                    $offset = 0;
                    while ($offset < $length) {
                        $written = fwrite($output, $offset === 0 ? $data : substr($data, $offset));
                        if ($written === false || $written === 0) throw new RuntimeException('No hay espacio suficiente para completar el ZIP.');
                        $offset += $written;
                    }
                }
            };
        } else {
            $this->native = new ZipArchive();
            if ($this->native->open($path, $append ? 0 : (ZipArchive::CREATE | ZipArchive::EXCL)) !== true) {
                $this->native = null;
                throw new RuntimeException('No se pudo crear el ZIP. Revisa el espacio libre del servidor.');
            }
        }
    }

    public function addFile(string $source, string $destination): void
    {
        $extension = strtolower(pathinfo($destination, PATHINFO_EXTENSION));
        $compressed = in_array($extension, ['zip', 'gz', 'bz2', 'xz', '7z', 'rar', 'jpg', 'jpeg', 'png', 'gif', 'webp', 'avif',
            'heic', 'ico', 'pdf', 'docx', 'xlsx', 'pptx', 'odt', 'ods', 'odp', 'mp3', 'mp4', 'm4a', 'webm', 'ogg', 'woff', 'woff2'], true);
        if ($this->streaming !== null) {
            $input = fopen($source, 'rb');
            if ($input === false) throw new RuntimeException('No se pudo leer un archivo del paquete: ' . $destination);
            try {
                $options = new \ZipStream\Option\File();
                $options->setMethod($compressed ? \ZipStream\Option\Method::STORE() : \ZipStream\Option\Method::DEFLATE());
                $options->setDeflateLevel(1);
                $stat = fstat($input);
                if ($stat !== false) $options->setTime((new DateTimeImmutable())->setTimestamp($stat['mtime']));
                $this->streaming->addFileFromStream($destination, $input, $options);
            } finally { fclose($input); }
        } else {
            if (!$this->native->addFile($source, $destination)) throw new RuntimeException('No se pudo leer un archivo del paquete: ' . $destination);
            $method = $compressed ? ZipArchive::CM_STORE : ZipArchive::CM_DEFLATE;
            if (!$this->native->setCompressionIndex($this->native->numFiles - 1, $method, $compressed ? 0 : 1)) throw new RuntimeException('No se pudo preparar la compresión de un archivo.');
        }
    }

    public function addDirectory(string $name): void
    {
        if ($this->streaming !== null) $this->streaming->addFile(rtrim($name, '/') . '/', '');
        elseif (!$this->native->addEmptyDir($name)) throw new RuntimeException('No se pudo incluir una carpeta en el ZIP.');
    }

    public function addText(string $name, string $text): void
    {
        if ($this->streaming !== null) $this->streaming->addFile($name, $text);
        elseif (!$this->native->addFromString($name, $text)) throw new RuntimeException('No se pudo incluir ' . $name . ' en el ZIP.');
    }

    public function remove(string $name): void
    {
        if ($this->native === null) throw new LogicException('Solo se pueden retirar entradas al actualizar un ZIP existente.');
        if ($this->native->locateName($name) !== false && !$this->native->deleteName($name)) throw new RuntimeException('No se pudo actualizar una entrada del paquete.');
    }

    public function finish(?callable $progress = null): void
    {
        if ($progress !== null) $progress(0.0);
        if ($this->streaming !== null) {
            $this->streaming->finish();
            $this->streaming = null;
            if (!fflush($this->output)) throw new RuntimeException('No se pudo terminar el ZIP. Revisa el espacio libre del servidor.');
            fclose($this->output);
            $this->output = null;
        } elseif ($this->native !== null) {
            if ($progress !== null && method_exists($this->native, 'registerProgressCallback')) {
                $this->native->registerProgressCallback(0.01, $progress);
            }
            if (!$this->native->close()) throw new RuntimeException('No se pudo terminar el ZIP. Revisa el espacio libre del servidor.');
            $this->native = null;
        }
        if ($progress !== null) $progress(1.0);
    }

    public function abort(): void
    {
        $this->streaming = null;
        if (is_resource($this->output)) fclose($this->output);
        $this->output = null;
        if ($this->native !== null) {
            // Cancelar evita comprimir un paquete que ya ha fallado.
            try { @$this->native->unchangeAll(); @$this->native->close(); } catch (Throwable $ignored) {}
            $this->native = null;
        }
    }

    public function __destruct() { $this->abort(); }
}
