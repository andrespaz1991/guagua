<?php

/** Historial inmutable de las notas importadas desde los reportes académicos. */
class NotasAuditoriaService
{
    private $mysqli;

    public function __construct(mysqli $mysqli)
    {
        $this->mysqli = $mysqli;
    }

    public function asegurarTabla()
    {
        $sql = "CREATE TABLE IF NOT EXISTS notas_auditoria (
            id_nota_auditoria BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            id_estudiante VARCHAR(255) NOT NULL,
            id_asignacion INT NULL,
            id_materia INT NULL,
            id_periodo INT NOT NULL,
            nombre_periodo VARCHAR(60) NOT NULL,
            grado VARCHAR(20) NOT NULL,
            materia VARCHAR(120) NOT NULL,
            nota DECIMAL(5,2) NOT NULL,
            fecha_nota DATE NOT NULL,
            fecha_registro DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            origen VARCHAR(60) NOT NULL DEFAULT 'ia_reporte_excel',
            hash_registro CHAR(64) NOT NULL,
            PRIMARY KEY (id_nota_auditoria),
            UNIQUE KEY uq_notas_auditoria_hash (hash_registro),
            KEY idx_notas_auditoria_periodo (id_periodo, grado, materia),
            KEY idx_notas_auditoria_estudiante (id_estudiante, fecha_nota),
            KEY idx_notas_auditoria_asignacion (id_asignacion, fecha_nota)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

        if (!$this->mysqli->query($sql)) {
            throw new RuntimeException('No fue posible preparar el historial de notas: ' . $this->mysqli->error);
        }
    }

    /** Solo devuelve el período marcado como actual y vigente en la fecha actual. */
    public function obtenerPeriodoActual()
    {
        $sql = "SELECT id_periodo, nombre_periodo, fecha_inicio, fecha_fin, ano_lectivo
                FROM periodo
                WHERE estado_periodo = '1'
                  AND fecha_inicio <> '0000-00-00'
                  AND fecha_fin <> '0000-00-00'
                  AND CURDATE() BETWEEN fecha_inicio AND fecha_fin
                ORDER BY id_periodo DESC LIMIT 1";
        $resultado = $this->mysqli->query($sql);
        return $resultado ? $resultado->fetch_assoc() : null;
    }

    public function guardarLote(array $notasPorEstudiante)
    {
        $this->asegurarTabla();
        $periodo = $this->obtenerPeriodoActual();
        if (!$periodo) {
            return ['ok' => false, 'guardadas' => 0, 'omitidas' => 0, 'mensaje' => 'No hay un período activo cuya fecha incluya hoy; no se guardaron notas.'];
        }

        $mapaAsignaciones = $this->obtenerMapaAsignaciones((string)$periodo['ano_lectivo']);
        $mapaEstudiantes = $this->obtenerMapaEstudiantesActivos();
        $sql = "INSERT IGNORE INTO notas_auditoria
                    (id_estudiante, id_asignacion, id_materia, id_periodo, nombre_periodo, grado, materia, nota, fecha_nota, origen, hash_registro)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, CURDATE(), 'ia_reporte_excel', ?)";
        $stmt = $this->mysqli->prepare($sql);
        if (!$stmt) throw new RuntimeException('No fue posible preparar el guardado de notas: ' . $this->mysqli->error);

        $guardadas = 0;
        $omitidas = 0;
        $documentosCorregidos = 0;
        $this->mysqli->begin_transaction();
        try {
            foreach ($notasPorEstudiante as $documento => $datos) {
                $grado = trim((string)($datos['grado'] ?? ''));
                if (!in_array($grado, ['6', '7', '8', '9', '10', '11'], true)) continue;
                $documentoExcel = trim((string)$documento);
                $nombreClave = $this->normalizarNombre($datos['nombre'] ?? '');
                $idEstudiante = $mapaEstudiantes[$nombreClave] ?? $documentoExcel;
                if ($idEstudiante !== $documentoExcel) $documentosCorregidos++;

                foreach (($datos['notas'] ?? []) as $materiaOrigen => $nota) {
                    if (!is_numeric($nota)) continue;
                    $clave = $grado . '|' . $this->normalizarMateria($materiaOrigen);
                    $asignacion = $mapaAsignaciones[$clave] ?? null;
                    $materia = $asignacion['materia'] ?? trim((string)$materiaOrigen);
                    $idAsignacion = $asignacion['id_asignacion'] ?? null;
                    $idMateria = $asignacion['id_materia'] ?? null;
                    $notaNumerica = round((float)$nota, 2);
                    $hash = hash('sha256', implode('|', [
                        $idEstudiante, $idAsignacion ?: 0, $periodo['id_periodo'],
                        $grado, $materia, number_format($notaNumerica, 2, '.', ''), date('Y-m-d'), 'ia_reporte_excel'
                    ]));
                    $idPeriodo = (int)$periodo['id_periodo'];
                    $nombrePeriodo = (string)$periodo['nombre_periodo'];
                    $stmt->bind_param('siiisssds', $idEstudiante, $idAsignacion, $idMateria, $idPeriodo, $nombrePeriodo, $grado, $materia, $notaNumerica, $hash);
                    if (!$stmt->execute()) throw new RuntimeException('Error al guardar una nota: ' . $stmt->error);
                    if ($stmt->affected_rows > 0) $guardadas++; else $omitidas++;
                }
            }
            $this->mysqli->commit();
        } catch (Throwable $e) {
            $this->mysqli->rollback();
            $stmt->close();
            throw $e;
        }
        $stmt->close();

        return [
            'ok' => true,
            'guardadas' => $guardadas,
            'omitidas' => $omitidas,
            'periodo' => $periodo,
            'mensaje' => "Historial sincronizado: {$guardadas} nuevas y {$omitidas} ya registradas." . ($documentosCorregidos ? " {$documentosCorregidos} documentos fueron vinculados por nombre al estudiante activo." : '')
        ];
    }

    private function obtenerMapaAsignaciones($anoLectivo)
    {
        $sql = "SELECT a.id_asignacion, a.id_asignatura AS id_materia, cc.nombre_categoria_curso AS grado, mo.nombre_materia
                FROM asignacion a
                INNER JOIN categoria_curso cc ON cc.id_categoria_curso = a.id_categoria_curso
                INNER JOIN materia_oficial mo ON mo.id_materia = a.id_asignatura
                WHERE a.ano_lectivo = ? AND cc.nombre_categoria_curso IN ('6','7','8','9','10','11')
                ORDER BY a.id_asignacion DESC";
        $stmt = $this->mysqli->prepare($sql);
        $stmt->bind_param('s', $anoLectivo);
        $stmt->execute();
        $resultado = $stmt->get_result();
        $mapa = [];
        while ($fila = $resultado->fetch_assoc()) {
            $clave = $fila['grado'] . '|' . $this->normalizarMateria($fila['nombre_materia']);
            // Una instalación puede tener duplicadas asignaciones históricas. La más
            // reciente es la representación de la asignación actual en la auditoría.
            if (!isset($mapa[$clave])) $mapa[$clave] = ['id_asignacion' => (int)$fila['id_asignacion'], 'id_materia' => (int)$fila['id_materia'], 'materia' => $fila['nombre_materia']];
        }
        $stmt->close();
        return $mapa;
    }

    private function normalizarMateria($valor)
    {
        $valor = trim((string)$valor);
        $valor = strtr($valor, ['á'=>'a','é'=>'e','í'=>'i','ó'=>'o','ú'=>'u','ü'=>'u','ñ'=>'n','Á'=>'a','É'=>'e','Í'=>'i','Ó'=>'o','Ú'=>'u','Ü'=>'u','Ñ'=>'n']);
        $valor = function_exists('mb_strtolower') ? mb_strtolower($valor, 'UTF-8') : strtolower($valor);
        $valor = preg_replace('/[^a-z0-9]+/', '', $valor);
        $alias = [
            'tecnologia' => 'tecnologiaeinformatica',
            'cienciassocialeseconomia' => 'economiapolitica',
            'economia' => 'economiapolitica',
        ];
        return $alias[$valor] ?? $valor;
    }

    private function obtenerMapaEstudiantesActivos()
    {
        $resultado = $this->mysqli->query("SELECT id_usuario, nombre, apellido FROM usuario WHERE rol LIKE '%estudiante%' AND LOWER(TRIM(COALESCE(estado, 'activo'))) = 'activo'");
        $mapa = [];
        while ($fila = $resultado->fetch_assoc()) {
            foreach ([trim($fila['nombre'] . ' ' . $fila['apellido']), trim($fila['apellido'] . ' ' . $fila['nombre'])] as $nombre) {
                $clave = $this->normalizarNombre($nombre);
                if ($clave === '') continue;
                // Ante homónimos se desactiva la corrección automática para no
                // asociar una calificación al documento de otra persona.
                if (!array_key_exists($clave, $mapa)) $mapa[$clave] = (string)$fila['id_usuario'];
                elseif ($mapa[$clave] !== (string)$fila['id_usuario']) $mapa[$clave] = null;
            }
        }
        return array_filter($mapa, static function ($documento) { return $documento !== null; });
    }

    private function normalizarNombre($valor)
    {
        $valor = trim((string)$valor);
        $valor = strtr($valor, ['á'=>'a','é'=>'e','í'=>'i','ó'=>'o','ú'=>'u','ü'=>'u','ñ'=>'n','Á'=>'a','É'=>'e','Í'=>'i','Ó'=>'o','Ú'=>'u','Ü'=>'u','Ñ'=>'n']);
        $valor = function_exists('mb_strtolower') ? mb_strtolower($valor, 'UTF-8') : strtolower($valor);
        return preg_replace('/[^a-z0-9]+/', '', $valor);
    }
}
