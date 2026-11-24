<?php
declare(strict_types=1);

final class SisgaIndicadores
{
    public function __construct(private PDO $db) {}

    public function instalar(): void
    {
        $this->db->exec(file_get_contents(dirname(__DIR__) . '/migrations/001_indicadores.sql'));
    }

    private static function texto(mixed $valor, int $maximo, bool $requerido = true): string
    {
        if (!is_string($valor) && !is_int($valor)) throw new InvalidArgumentException('Hay un dato de texto inválido en el perfil.');
        $texto = trim(preg_replace('/\s+/u', ' ', (string)$valor) ?? '');
        if (mb_strlen($texto) > $maximo || ($requerido && $texto === '')) throw new InvalidArgumentException('El perfil tiene campos vacíos o demasiado extensos.');
        return $texto;
    }

    private static function lista(array $valores): bool
    {
        return $valores === [] || array_keys($valores) === range(0, count($valores) - 1);
    }

    public static function validar(array $perfil): array
    {
        $c = $perfil['contexto'] ?? [];
        $origen = self::texto($c['origen'] ?? '', 200);
        $url = parse_url($origen);
        if (!is_array($url) || ($url['scheme'] ?? '') !== 'https'
            || !preg_match('/(^|\.)sisga\.com\.co$/i', $url['host'] ?? '')
            || isset($url['user']) || isset($url['pass']) || isset($url['query']) || isset($url['fragment'])
            || !empty($url['path'])) throw new InvalidArgumentException('El perfil debe proceder de SISGA mediante HTTPS.');
        $contexto = [
            'origen' => $origen,
            'institucion' => self::texto($c['institucion'] ?? '', 80),
        ];
        foreach (['materia' => 180, 'grado' => 80, 'periodo' => 40] as $campo => $longitud) {
            $contexto[$campo] = [
                'codigo' => self::texto($c[$campo]['codigo'] ?? '', 40),
                'nombre' => self::texto($c[$campo]['nombre'] ?? '', $longitud, false),
            ];
        }
        $contexto['nivel'] = self::texto($c['nivel'] ?? '0', 40);
        $contexto['grupoOrigen'] = [
            'codigo' => self::texto($c['grupoOrigen']['codigo'] ?? '', 40, false),
            'nombre' => self::texto($c['grupoOrigen']['nombre'] ?? '', 100, false),
        ];
        $fecha = $perfil['guardadoEn'] ?? '';
        if (!is_string($fecha) || !preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}(?:\.\d{1,6})?(?:Z|[+-]\d{2}:\d{2})$/D', $fecha)) {
            throw new InvalidArgumentException('La fecha de captura no es válida.');
        }
        try { $instante = new DateTimeImmutable($fecha); }
        catch (Exception $e) { throw new InvalidArgumentException('La fecha de captura no es válida.'); }
        $erroresFecha = DateTimeImmutable::getLastErrors();
        if ($erroresFecha && ($erroresFecha['warning_count'] || $erroresFecha['error_count'])) throw new InvalidArgumentException('La fecha de captura no es válida.');
        $indicadores = $perfil['indicadores'] ?? null;
        if (!is_array($indicadores) || !self::lista($indicadores) || !$indicadores || count($indicadores) > 100) {
            throw new InvalidArgumentException('Cada perfil debe contener entre 1 y 100 indicadores.');
        }
        $normalizados = []; $codigos = [];
        foreach ($indicadores as $indicador) {
            if (!is_array($indicador)) throw new InvalidArgumentException('Hay un indicador inválido.');
            $codigo = self::texto($indicador['codigo'] ?? '', 30);
            if (!ctype_digit($codigo) || isset($codigos[$codigo])) throw new InvalidArgumentException('Hay códigos de indicador inválidos o repetidos.');
            $codigos[$codigo] = true;
            $normalizados[] = [
                'codigo' => $codigo,
                'texto' => self::texto($indicador['texto'] ?? '', 3000),
                'categoria' => [
                    'codigo' => self::texto($indicador['categoria']['codigo'] ?? '', 40, false),
                    'nombre' => self::texto($indicador['categoria']['nombre'] ?? '', 120),
                ],
            ];
        }
        return ['contexto' => $contexto, 'guardadoEn' => $instante->setTimezone(new DateTimeZone('UTC'))->format('Y-m-d\TH:i:s.v\Z'), 'indicadores' => $normalizados];
    }

    /** Extrae únicamente JSON; nunca evalúa ni ejecuta los scripts importados. */
    public static function extraer(string $contenido): array
    {
        if (strlen($contenido) > 3 * 1024 * 1024) throw new InvalidArgumentException('Un archivo supera el máximo de 3 MB.');
        $contenido = trim(preg_replace('/^\xEF\xBB\xBF/', '', $contenido));
        if (!str_starts_with($contenido, '{')) {
            $inicio = strrpos($contenido, ')({');
            $fin = strrpos($contenido, '}).catch(');
            if ($inicio === false || $fin === false || $fin <= $inicio) throw new InvalidArgumentException('El archivo no contiene una captura de indicadores compatible.');
            $contenido = substr($contenido, $inicio + 2, $fin - $inicio - 1);
        }
        try { $datos = json_decode($contenido, true, 64, JSON_THROW_ON_ERROR); }
        catch (JsonException $e) { throw new InvalidArgumentException('No se pudieron leer los datos JSON del archivo.'); }
        if (!is_array($datos)) throw new InvalidArgumentException('El archivo no contiene un perfil.');
        $perfiles = isset($datos['contexto']) ? [$datos] : ($datos['perfiles'] ?? null);
        if (!is_array($perfiles) || !self::lista($perfiles) || !$perfiles || count($perfiles) > 1000) throw new InvalidArgumentException('El archivo no contiene una lista válida de perfiles.');
        return array_map(static fn($p) => is_array($p) ? self::validar($p) : throw new InvalidArgumentException('Hay un perfil inválido.'), $perfiles);
    }

    public static function clave(array $perfil): string
    {
        $c = $perfil['contexto'];
        return hash('sha256', json_encode([$c['origen'], mb_strtolower($c['institucion']), $c['materia']['codigo'],
            mb_strtolower($c['grado']['nombre']) ?: $c['grado']['codigo'], $c['periodo']['codigo'], $c['nivel']], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR));
    }

    public function listar(): array
    {
        $filas = $this->db->query('SELECT id, perfil_json, archivo_origen, actualizado_en FROM sisga_indicadores_perfiles ORDER BY materia, CAST(grado AS UNSIGNED), grado, periodo, id')->fetchAll();
        return array_map(static fn($fila) => [
            'id' => (int)$fila['id'],
            'perfil' => json_decode($fila['perfil_json'], true, 64, JSON_THROW_ON_ERROR),
            'archivoOrigen' => $fila['archivo_origen'],
            'actualizadoEn' => $fila['actualizado_en'],
        ], $filas);
    }

    public function importar(array $archivos): array
    {
        if (!$archivos || count($archivos) > 100) throw new InvalidArgumentException('Selecciona entre 1 y 100 archivos.');
        $pendientes = [];
        foreach ($archivos as $archivo) {
            $nombre = self::texto($archivo['nombre'] ?? '', 255);
            if (strpbrk($nombre, '/\\') !== false || !preg_match('/\.(js|json)$/i', $nombre)) throw new InvalidArgumentException('Selecciona archivos .js o .json de indicadores.');
            if (!is_string($archivo['contenido'] ?? null)) throw new InvalidArgumentException('El contenido del archivo no es válido.');
            try { $perfiles = self::extraer($archivo['contenido']); }
            catch (InvalidArgumentException $e) { throw new InvalidArgumentException($nombre . ': ' . $e->getMessage()); }
            foreach ($perfiles as $perfil) {
                $clave = self::clave($perfil);
                if (!isset($pendientes[$clave]) || $perfil['guardadoEn'] > $pendientes[$clave]['perfil']['guardadoEn']) {
                    $pendientes[$clave] = ['perfil' => $perfil, 'archivo' => $nombre];
                }
            }
        }
        $resumen = ['archivos' => count($archivos), 'combinaciones' => count($pendientes), 'nuevos' => 0, 'actualizados' => 0, 'sinCambios' => 0];
        $propia = !$this->db->inTransaction();
        if ($propia) $this->db->beginTransaction();
        try {
            $buscar = $this->db->prepare('SELECT id, perfil_json FROM sisga_indicadores_perfiles WHERE clave_contexto = ? FOR UPDATE');
            $insertar = $this->db->prepare('INSERT INTO sisga_indicadores_perfiles (clave_contexto, institucion_sisga, materia, grado, periodo, nivel, perfil_json, archivo_origen, capturado_en) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)');
            $actualizar = $this->db->prepare('UPDATE sisga_indicadores_perfiles SET institucion_sisga=?, materia=?, grado=?, periodo=?, nivel=?, perfil_json=?, archivo_origen=?, capturado_en=? WHERE id=?');
            foreach ($pendientes as $clave => $entrada) {
                $p = $entrada['perfil']; $c = $p['contexto'];
                $json = json_encode($p, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
                $buscar->execute([$clave]); $existente = $buscar->fetch();
                $fecha = (new DateTimeImmutable($p['guardadoEn']))->format('Y-m-d H:i:s.v');
                $valores = [$c['institucion'], $c['materia']['nombre'] ?: $c['materia']['codigo'], $c['grado']['nombre'] ?: $c['grado']['codigo'],
                    $c['periodo']['codigo'], $c['nivel'], $json, $entrada['archivo'], $fecha];
                if (!$existente) {
                    $insertar->execute(array_merge([$clave], $valores)); $resumen['nuevos']++;
                } else {
                    $anterior = json_decode($existente['perfil_json'], true, 64, JSON_THROW_ON_ERROR);
                    if ($p['guardadoEn'] > $anterior['guardadoEn']) {
                        $actualizar->execute(array_merge($valores, [$existente['id']])); $resumen['actualizados']++;
                    } else { $resumen['sinCambios']++; }
                }
            }
            if ($propia) $this->db->commit();
        } catch (Throwable $error) {
            if ($propia && $this->db->inTransaction()) $this->db->rollBack();
            throw $error;
        }
        return $resumen;
    }
}
