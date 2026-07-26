<?php
declare(strict_types=1);

final class EvaluationService
{
    private mysqli $database;

    public function __construct(mysqli $database)
    {
        $this->database = $database;
    }

    /** @return array<int,array<string,mixed>> */
    public function listEvaluations(): array
    {
        $result = $this->database->query(
            'SELECT evaluation.id, school_year.ano, evaluation.estado, teacher.nombre, teacher.cedula
             FROM evidencias_evaluaciones_anuales evaluation
             INNER JOIN evidencias_anos_lectivos school_year ON school_year.id = evaluation.ano_lectivo_id
             INNER JOIN evidencias_docentes teacher ON teacher.id = evaluation.docente_id
             ORDER BY school_year.ano DESC, teacher.nombre ASC'
        );

        return $result->fetch_all(MYSQLI_ASSOC);
    }

    /** @return array<string,mixed> */
    public function getDashboard(int $evaluationId): array
    {
        $detail = $this->getDetail($evaluationId);
        $summary = $this->calculateSummary($detail['evaluacion'], $detail['competencias']);
        $progress = $this->calculateEvidenceProgress($detail['competencias']);

        return [
            'evaluacion' => $detail['evaluacion'],
            'resumen' => $summary,
            'progreso_evidencias' => $progress,
            'competencias' => $detail['competencias'],
        ];
    }

    /** @return array<string,mixed> */
    public function getDetail(int $evaluationId): array
    {
        $evaluation = $this->findEvaluation($evaluationId);
        $competencyRows = $this->database->query(
            'SELECT id, tipo, area_gestion, nombre_competencia, contribucion_individual, orden, puntaje_val_1, puntaje_val_2, puntaje_final
             FROM evidencias_competencias_evaluadas
             WHERE evaluacion_id = ' . $evaluationId . '
             ORDER BY FIELD(tipo, \'Funcional\', \'Comportamental\'), FIELD(area_gestion, \'Académica\', \'Administrativa\', \'Comunitaria\'), orden'
        )->fetch_all(MYSQLI_ASSOC);

        $competencies = [];
        foreach ($competencyRows as $competencyRow) {
            $competencyRow['id'] = (int) $competencyRow['id'];
            $competencyRow['orden'] = (int) $competencyRow['orden'];
            $competencyRow['puntaje_val_1'] = $competencyRow['puntaje_val_1'] === null ? null : (float) $competencyRow['puntaje_val_1'];
            $competencyRow['puntaje_val_2'] = $competencyRow['puntaje_val_2'] === null ? null : (float) $competencyRow['puntaje_val_2'];
            $competencyRow['puntaje_final'] = $competencyRow['puntaje_final'] === null ? null : (float) $competencyRow['puntaje_final'];
            $competencyRow['criterios'] = [];
            $competencies[$competencyRow['id']] = $competencyRow;
        }

        if ($competencies !== []) {
            $competencyIds = implode(',', array_keys($competencies));
            $criteriaRows = $this->database->query(
                'SELECT id, competencia_evaluada_id, orden, descripcion
                 FROM evidencias_criterios_evaluacion
                 WHERE competencia_evaluada_id IN (' . $competencyIds . ')
                 ORDER BY orden'
            )->fetch_all(MYSQLI_ASSOC);
            $criteria = [];
            foreach ($criteriaRows as $criterionRow) {
                $criterionRow['id'] = (int) $criterionRow['id'];
                $criterionRow['competencia_evaluada_id'] = (int) $criterionRow['competencia_evaluada_id'];
                $criterionRow['orden'] = (int) $criterionRow['orden'];
                $criterionRow['evidencias'] = [];
                $criteria[$criterionRow['id']] = $criterionRow;
                $competencies[$criterionRow['competencia_evaluada_id']]['criterios'][] = &$criteria[$criterionRow['id']];
            }

            if ($criteria !== []) {
                $criteriaIds = implode(',', array_keys($criteria));
                $evidenceRows = $this->database->query(
                    'SELECT id, criterio_id, titulo, descripcion, tipo, estado, fecha_incorporacion
                     FROM evidencias_evidencias
                     WHERE criterio_id IN (' . $criteriaIds . ')
                     ORDER BY id'
                )->fetch_all(MYSQLI_ASSOC);
                $evidence = [];
                foreach ($evidenceRows as $evidenceRow) {
                    $evidenceRow['id'] = (int) $evidenceRow['id'];
                    $evidenceRow['criterio_id'] = (int) $evidenceRow['criterio_id'];
                    $evidenceRow['adjuntos'] = [];
                    $evidence[$evidenceRow['id']] = $evidenceRow;
                    $criteria[$evidenceRow['criterio_id']]['evidencias'][] = &$evidence[$evidenceRow['id']];
                }

                if ($evidence !== []) {
                    $evidenceIds = implode(',', array_keys($evidence));
                    $attachmentRows = $this->database->query(
                        'SELECT id, evidencia_id, tipo_archivo, nombre_original, url_archivo, mime_type, tamano_bytes, fecha_subida
                         FROM evidencias_adjuntos_evidencia
                         WHERE evidencia_id IN (' . $evidenceIds . ')
                         ORDER BY fecha_subida DESC'
                    )->fetch_all(MYSQLI_ASSOC);
                    foreach ($attachmentRows as $attachmentRow) {
                        $attachmentRow['id'] = (int) $attachmentRow['id'];
                        $attachmentRow['evidencia_id'] = (int) $attachmentRow['evidencia_id'];
                        $attachmentRow['tamano_bytes'] = $attachmentRow['tamano_bytes'] === null ? null : (int) $attachmentRow['tamano_bytes'];
                        $evidence[$attachmentRow['evidencia_id']]['adjuntos'][] = $attachmentRow;
                    }
                }
            }
        }

        return ['evaluacion' => $evaluation, 'competencias' => array_values($competencies)];
    }

    /** @return array<string,mixed> */
    public function updateScore(int $competencyId, mixed $scoreOne, mixed $scoreTwo): array
    {
        $competency = $this->findCompetency($competencyId);
        $this->assertMutable((int) $competency['evaluacion_id']);
        $firstScore = $this->normaliseScore($scoreOne);
        $secondScore = $this->normaliseScore($scoreTwo);
        $finalScore = $this->calculateCompetencyFinal($firstScore, $secondScore, (int) $competency['dias_valoracion_1'], (int) $competency['dias_valoracion_2']);

        $statement = $this->database->prepare(
            'UPDATE evidencias_competencias_evaluadas
             SET puntaje_val_1 = ?, puntaje_val_2 = ?, puntaje_final = ?
             WHERE id = ?'
        );
        $statement->bind_param('dddi', $firstScore, $secondScore, $finalScore, $competencyId);
        $statement->execute();
        $this->log((int) $competency['evaluacion_id'], 'Puntaje actualizado', 'Competencia: ' . $competency['nombre_competencia']);

        return $this->getDashboard((int) $competency['evaluacion_id']);
    }

    /** @param array<string,mixed> $data */
    public function updateEvaluationSettings(int $evaluationId, array $data): array
    {
        $evaluation = $this->findEvaluation($evaluationId);
        $this->assertMutable($evaluationId);
        $academicWeight = $this->normaliseWeight($data['ponderacion_academica'] ?? $evaluation['ponderacion_academica']);
        $administrativeWeight = $this->normaliseWeight($data['ponderacion_administrativa'] ?? $evaluation['ponderacion_administrativa']);
        $communityWeight = $this->normaliseWeight($data['ponderacion_comunitaria'] ?? $evaluation['ponderacion_comunitaria']);
        $this->assertFunctionalWeights($academicWeight, $administrativeWeight, $communityWeight);
        $startDate = $this->normaliseDate($data['fecha_inicio'] ?? $evaluation['fecha_inicio']);
        $city = trim((string) ($data['ciudad_concertacion'] ?? $evaluation['ciudad_concertacion']));
        $daysOne = $this->normaliseDays($data['dias_valoracion_1'] ?? $evaluation['dias_valoracion_1']);
        $daysTwo = $this->normaliseDays($data['dias_valoracion_2'] ?? $evaluation['dias_valoracion_2']);

        $statement = $this->database->prepare(
            'UPDATE evidencias_evaluaciones_anuales
             SET ponderacion_academica = ?, ponderacion_administrativa = ?, ponderacion_comunitaria = ?, fecha_inicio = ?, ciudad_concertacion = ?, dias_valoracion_1 = ?, dias_valoracion_2 = ?
             WHERE id = ?'
        );
        $statement->bind_param('dddssiii', $academicWeight, $administrativeWeight, $communityWeight, $startDate, $city, $daysOne, $daysTwo, $evaluationId);
        $statement->execute();
        $this->recalculateEvaluationScores($evaluationId);
        $this->log($evaluationId, 'Condiciones actualizadas', 'Ponderaciones y periodos de valoración actualizados.');

        return $this->getDashboard($evaluationId);
    }

    public function updateEvaluationState(int $evaluationId, string $state): array
    {
        $allowedStates = ['En Concertación', 'Valoración 1', 'Valoración 2', 'Notificado'];
        if (!in_array($state, $allowedStates, true)) {
            throw new InvalidArgumentException('El estado seleccionado no es válido.');
        }
        $this->assertMutable($evaluationId);
        $statement = $this->database->prepare('UPDATE evidencias_evaluaciones_anuales SET estado = ? WHERE id = ?');
        $statement->bind_param('si', $state, $evaluationId);
        $statement->execute();
        if ($state === 'Notificado') {
            $this->database->query(
                'UPDATE evidencias_anos_lectivos school_year
                 INNER JOIN evidencias_evaluaciones_anuales evaluation ON evaluation.ano_lectivo_id = school_year.id
                 SET school_year.estado = \'Cerrado\'
                 WHERE evaluation.id = ' . $evaluationId
            );
        }
        $this->log($evaluationId, 'Estado actualizado', $state);

        return $this->getDashboard($evaluationId);
    }

    /** @param array<string,mixed> $data */
    public function createEvaluation(array $data): array
    {
        $year = (int) ($data['ano'] ?? 0);
        if ($year < 2026 || $year > 2100) {
            throw new InvalidArgumentException('Ingrese un año lectivo válido.');
        }
        $behavioralNames = array_values(array_unique(array_map('trim', (array) ($data['competencias_comportamentales'] ?? []))));
        if (count($behavioralNames) !== 3 || array_diff($behavioralNames, $this->behavioralCatalog()) !== []) {
            throw new InvalidArgumentException('Seleccione exactamente tres competencias comportamentales del catálogo oficial.');
        }
        $academicWeight = $this->normaliseWeight($data['ponderacion_academica'] ?? 30);
        $administrativeWeight = $this->normaliseWeight($data['ponderacion_administrativa'] ?? 20);
        $communityWeight = $this->normaliseWeight($data['ponderacion_comunitaria'] ?? 20);
        $this->assertFunctionalWeights($academicWeight, $administrativeWeight, $communityWeight);
        $name = trim((string) ($data['nombre_docente'] ?? ''));
        $identification = trim((string) ($data['cedula'] ?? ''));
        if ($name === '' || $identification === '') {
            throw new InvalidArgumentException('El nombre y la cédula del docente son obligatorios.');
        }

        $this->database->begin_transaction();
        try {
            $teacherId = $this->findOrCreateTeacher($name, $identification);
            $schoolYearId = $this->findOrCreateSchoolYear($year);
            $existing = $this->database->query(
                'SELECT id FROM evidencias_evaluaciones_anuales WHERE docente_id = ' . $teacherId . ' AND ano_lectivo_id = ' . $schoolYearId
            )->fetch_assoc();
            if ($existing !== null) {
                throw new RuntimeException('El docente ya tiene una evaluación para este año lectivo.');
            }

            $startDate = $this->normaliseDate($data['fecha_inicio'] ?? ($year . '-01-01'));
            $city = trim((string) ($data['ciudad_concertacion'] ?? ''));
            $daysOne = $this->normaliseDays($data['dias_valoracion_1'] ?? 0);
            $daysTwo = $this->normaliseDays($data['dias_valoracion_2'] ?? 0);
            $statement = $this->database->prepare(
                "INSERT INTO evidencias_evaluaciones_anuales
                    (docente_id, ano_lectivo_id, ponderacion_academica, ponderacion_administrativa, ponderacion_comunitaria, fecha_inicio, ciudad_concertacion, dias_valoracion_1, dias_valoracion_2, estado)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'En Concertación')"
            );
            $statement->bind_param('iidddssii', $teacherId, $schoolYearId, $academicWeight, $administrativeWeight, $communityWeight, $startDate, $city, $daysOne, $daysTwo);
            $statement->execute();
            $evaluationId = (int) $this->database->insert_id;

            $this->cloneFunctionalCompetencies($evaluationId);
            foreach ($behavioralNames as $order => $behavioralName) {
                $this->cloneOrCreateBehavioralCompetency($evaluationId, $behavioralName, $order + 1);
            }
            $this->log($evaluationId, 'Evaluación creada', 'Año lectivo ' . $year);
            $this->database->commit();

            return $this->getDashboard($evaluationId);
        } catch (Throwable $exception) {
            $this->database->rollback();
            throw $exception;
        }
    }

    /** @param array<string,mixed> $request @param array<string,mixed> $files */
    public function addAttachment(array $request, array $files, string $applicationRoot): array
    {
        $evidenceId = (int) ($request['evidencia_id'] ?? 0);
        if ($evidenceId < 1) {
            throw new InvalidArgumentException('Seleccione la evidencia que desea respaldar.');
        }
        $evidence = $this->findEvidence($evidenceId);
        $evaluationId = (int) $evidence['evaluacion_id'];
        $this->assertMutable($evaluationId);

        $url = trim((string) ($request['url'] ?? ''));
        if ($url !== '') {
            if (filter_var($url, FILTER_VALIDATE_URL) === false || !preg_match('#^https?://#i', $url)) {
                throw new InvalidArgumentException('El enlace debe iniciar por http:// o https://.');
            }
            $this->insertAttachment($evidenceId, 'Enlace', $url, $url, null, null);
        } elseif (isset($files['archivo']) && is_array($files['archivo']) && (int) $files['archivo']['error'] !== UPLOAD_ERR_NO_FILE) {
            $file = $files['archivo'];
            if ((int) $file['error'] !== UPLOAD_ERR_OK) {
                throw new RuntimeException('No fue posible cargar el archivo.');
            }
            if ((int) $file['size'] > 15 * 1024 * 1024) {
                throw new InvalidArgumentException('El archivo supera el límite de 15 MB.');
            }
            $originalName = basename((string) $file['name']);
            $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
            $fileType = $this->fileTypeFromExtension($extension);
            if ($fileType === null) {
                throw new InvalidArgumentException('Formato no permitido. Use imágenes, PDF, Office, audio o video.');
            }
            $directory = $applicationRoot . '/uploads/' . $evaluationId . '/' . $evidenceId;
            if (!is_dir($directory) && !mkdir($directory, 0775, true) && !is_dir($directory)) {
                throw new RuntimeException('No se pudo preparar el directorio para los adjuntos.');
            }
            $storedName = bin2hex(random_bytes(16)) . '.' . $extension;
            $storedPath = $directory . '/' . $storedName;
            if (!move_uploaded_file((string) $file['tmp_name'], $storedPath)) {
                throw new RuntimeException('No se pudo guardar el archivo cargado.');
            }
            $mimeType = function_exists('mime_content_type') ? (mime_content_type($storedPath) ?: null) : null;
            $relativeUrl = 'uploads/' . $evaluationId . '/' . $evidenceId . '/' . $storedName;
            $this->insertAttachment($evidenceId, $fileType, $originalName, $relativeUrl, $mimeType, (int) $file['size']);
        } else {
            throw new InvalidArgumentException('Adjunte un archivo o proporcione un enlace.');
        }

        $this->database->query("UPDATE evidencias_evidencias SET estado = 'Registrada', fecha_incorporacion = NOW() WHERE id = " . $evidenceId);
        $this->log($evaluationId, 'Evidencia respaldada', (string) $evidence['titulo']);

        return $this->getDashboard($evaluationId);
    }

    /** @return array<string,mixed> */
    private function findEvaluation(int $evaluationId): array
    {
        $result = $this->database->query(
            'SELECT evaluation.*, school_year.ano, school_year.estado AS estado_ano, teacher.nombre AS docente_nombre, teacher.cedula AS docente_cedula, teacher.nivel, teacher.zona, teacher.institucion
             FROM evidencias_evaluaciones_anuales evaluation
             INNER JOIN evidencias_anos_lectivos school_year ON school_year.id = evaluation.ano_lectivo_id
             INNER JOIN evidencias_docentes teacher ON teacher.id = evaluation.docente_id
             WHERE evaluation.id = ' . $evaluationId
        );
        $evaluation = $result->fetch_assoc();
        if ($evaluation === null) {
            throw new RuntimeException('No se encontró la evaluación solicitada.');
        }
        foreach (['id', 'ano', 'dias_valoracion_1', 'dias_valoracion_2'] as $integerField) {
            $evaluation[$integerField] = (int) $evaluation[$integerField];
        }
        foreach (['ponderacion_academica', 'ponderacion_administrativa', 'ponderacion_comunitaria'] as $decimalField) {
            $evaluation[$decimalField] = (float) $evaluation[$decimalField];
        }

        return $evaluation;
    }

    /** @return array<string,mixed> */
    private function findCompetency(int $competencyId): array
    {
        $result = $this->database->query(
            'SELECT competency.id, competency.evaluacion_id, competency.nombre_competencia, evaluation.dias_valoracion_1, evaluation.dias_valoracion_2
             FROM evidencias_competencias_evaluadas competency
             INNER JOIN evidencias_evaluaciones_anuales evaluation ON evaluation.id = competency.evaluacion_id
             WHERE competency.id = ' . $competencyId
        );
        $competency = $result->fetch_assoc();
        if ($competency === null) {
            throw new RuntimeException('No se encontró la competencia solicitada.');
        }

        return $competency;
    }

    /** @return array<string,mixed> */
    private function findEvidence(int $evidenceId): array
    {
        $result = $this->database->query(
            'SELECT evidence.id, evidence.titulo, competency.evaluacion_id
             FROM evidencias_evidencias evidence
             INNER JOIN evidencias_criterios_evaluacion criterion ON criterion.id = evidence.criterio_id
             INNER JOIN evidencias_competencias_evaluadas competency ON competency.id = criterion.competencia_evaluada_id
             WHERE evidence.id = ' . $evidenceId
        );
        $evidence = $result->fetch_assoc();
        if ($evidence === null) {
            throw new RuntimeException('No se encontró la evidencia solicitada.');
        }

        return $evidence;
    }

    private function assertMutable(int $evaluationId): void
    {
        $evaluation = $this->findEvaluation($evaluationId);
        if ($evaluation['estado'] === 'Notificado' || $evaluation['estado_ano'] === 'Cerrado') {
            throw new RuntimeException('La evaluación ya fue notificada y está congelada como historial.');
        }
    }

    private function normaliseScore(mixed $value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }
        if (!is_numeric($value) || (float) $value < 0 || (float) $value > 100) {
            throw new InvalidArgumentException('Cada puntaje debe estar entre 0 y 100.');
        }

        return round((float) $value, 2);
    }

    private function normaliseWeight(mixed $value): float
    {
        if (!is_numeric($value) || (float) $value < 0 || (float) $value > 70) {
            throw new InvalidArgumentException('La ponderación de área debe estar entre 0 y 70.');
        }

        return round((float) $value, 2);
    }

    private function assertFunctionalWeights(float $academicWeight, float $administrativeWeight, float $communityWeight): void
    {
        if (abs(($academicWeight + $administrativeWeight + $communityWeight) - 70.0) > 0.001) {
            throw new InvalidArgumentException('Las tres ponderaciones funcionales deben sumar exactamente 70%.');
        }
    }

    private function normaliseDate(mixed $value): string
    {
        $date = DateTimeImmutable::createFromFormat('Y-m-d', (string) $value);
        if ($date === false || $date->format('Y-m-d') !== $value) {
            throw new InvalidArgumentException('La fecha de concertación no es válida.');
        }

        return $date->format('Y-m-d');
    }

    private function normaliseDays(mixed $value): int
    {
        if (!is_numeric($value) || (int) $value < 0 || (int) $value > 366) {
            throw new InvalidArgumentException('Los días de valoración deben estar entre 0 y 366.');
        }

        return (int) $value;
    }

    private function calculateCompetencyFinal(?float $scoreOne, ?float $scoreTwo, int $daysOne, int $daysTwo): ?float
    {
        if ($scoreOne === null && $scoreTwo === null) {
            return null;
        }
        if ($scoreOne === null) {
            return $scoreTwo;
        }
        if ($scoreTwo === null) {
            return $scoreOne;
        }
        if (($daysOne + $daysTwo) === 0) {
            return round(($scoreOne + $scoreTwo) / 2, 2);
        }

        return round((($scoreOne * $daysOne) + ($scoreTwo * $daysTwo)) / ($daysOne + $daysTwo), 2);
    }

    /** @param array<string,mixed> $evaluation @param array<int,array<string,mixed>> $competencies @return array<string,mixed> */
    private function calculateSummary(array $evaluation, array $competencies): array
    {
        $areaScores = ['Académica' => [], 'Administrativa' => [], 'Comunitaria' => []];
        $behavioralScores = [];
        $scoredCompetencies = 0;
        foreach ($competencies as $competency) {
            if ($competency['puntaje_final'] === null) {
                continue;
            }
            $scoredCompetencies++;
            if ($competency['tipo'] === 'Funcional') {
                $areaScores[$competency['area_gestion']][] = (float) $competency['puntaje_final'];
            } else {
                $behavioralScores[] = (float) $competency['puntaje_final'];
            }
        }

        $areas = [];
        $functionalSubtotal = 0.0;
        $weightByArea = [
            'Académica' => (float) $evaluation['ponderacion_academica'],
            'Administrativa' => (float) $evaluation['ponderacion_administrativa'],
            'Comunitaria' => (float) $evaluation['ponderacion_comunitaria'],
        ];
        foreach ($areaScores as $area => $scores) {
            $average = $scores === [] ? null : round(array_sum($scores) / count($scores), 2);
            $weighted = $average === null ? 0.0 : round($average * ($weightByArea[$area] / 100), 2);
            $functionalSubtotal += $weighted;
            $areas[$area] = ['promedio' => $average, 'ponderacion' => $weightByArea[$area], 'subtotal' => $weighted, 'competencias_calificadas' => count($scores)];
        }
        $behavioralAverage = $behavioralScores === [] ? null : round(array_sum($behavioralScores) / count($behavioralScores), 2);
        $behavioralSubtotal = $behavioralAverage === null ? 0.0 : round($behavioralAverage * 0.30, 2);
        $finalGrade = round($functionalSubtotal + $behavioralSubtotal, 2);

        return [
            'areas' => $areas,
            'promedio_comportamental' => $behavioralAverage,
            'subtotal_funcional' => round($functionalSubtotal, 2),
            'subtotal_comportamental' => $behavioralSubtotal,
            'nota_proyectada' => $finalGrade,
            'categoria' => $scoredCompetencies === 0 ? 'Sin calificar' : $this->performanceCategory($finalGrade),
            'competencias_calificadas' => $scoredCompetencies,
            'competencias_totales' => count($competencies),
        ];
    }

    /** @param array<int,array<string,mixed>> $competencies @return array<string,int|float> */
    private function calculateEvidenceProgress(array $competencies): array
    {
        $requiredCriteria = 0;
        $supportedCriteria = 0;
        $attachments = 0;
        foreach ($competencies as $competency) {
            foreach ($competency['criterios'] as $criterion) {
                $requiredCriteria++;
                $criterionSupported = false;
                foreach ($criterion['evidencias'] as $evidence) {
                    $attachmentCount = count($evidence['adjuntos']);
                    $attachments += $attachmentCount;
                    if ($evidence['estado'] === 'Registrada' || $attachmentCount > 0) {
                        $criterionSupported = true;
                    }
                }
                if ($criterionSupported) {
                    $supportedCriteria++;
                }
            }
        }
        $percentage = $requiredCriteria === 0 ? 0.0 : round(($supportedCriteria / $requiredCriteria) * 100, 1);

        return ['criterios_respaldados' => $supportedCriteria, 'criterios_totales' => $requiredCriteria, 'adjuntos' => $attachments, 'porcentaje' => $percentage];
    }

    private function performanceCategory(float $grade): string
    {
        if ($grade < 60) {
            return 'No satisfactorio';
        }
        if ($grade < 90) {
            return 'Satisfactorio';
        }

        return 'Sobresaliente';
    }

    private function recalculateEvaluationScores(int $evaluationId): void
    {
        $evaluation = $this->findEvaluation($evaluationId);
        $result = $this->database->query('SELECT id, puntaje_val_1, puntaje_val_2 FROM evidencias_competencias_evaluadas WHERE evaluacion_id = ' . $evaluationId);
        $statement = $this->database->prepare('UPDATE evidencias_competencias_evaluadas SET puntaje_final = ? WHERE id = ?');
        while ($competency = $result->fetch_assoc()) {
            $finalScore = $this->calculateCompetencyFinal(
                $competency['puntaje_val_1'] === null ? null : (float) $competency['puntaje_val_1'],
                $competency['puntaje_val_2'] === null ? null : (float) $competency['puntaje_val_2'],
                (int) $evaluation['dias_valoracion_1'],
                (int) $evaluation['dias_valoracion_2']
            );
            $competencyId = (int) $competency['id'];
            $statement->bind_param('di', $finalScore, $competencyId);
            $statement->execute();
        }
    }

    private function findOrCreateTeacher(string $name, string $identification): int
    {
        $statement = $this->database->prepare(
            'INSERT INTO evidencias_docentes (nombre, cedula) VALUES (?, ?)
             ON DUPLICATE KEY UPDATE id = LAST_INSERT_ID(id), nombre = VALUES(nombre)'
        );
        $statement->bind_param('ss', $name, $identification);
        $statement->execute();

        return (int) $this->database->insert_id;
    }

    private function findOrCreateSchoolYear(int $year): int
    {
        $statement = $this->database->prepare(
            "INSERT INTO evidencias_anos_lectivos (ano, estado) VALUES (?, 'Activo')
             ON DUPLICATE KEY UPDATE id = LAST_INSERT_ID(id)"
        );
        $statement->bind_param('i', $year);
        $statement->execute();

        return (int) $this->database->insert_id;
    }

    private function cloneFunctionalCompetencies(int $targetEvaluationId): void
    {
        $result = $this->database->query(
            "SELECT competency.id
             FROM evidencias_competencias_evaluadas competency
             INNER JOIN evidencias_evaluaciones_anuales evaluation ON evaluation.id = competency.evaluacion_id
             INNER JOIN evidencias_anos_lectivos school_year ON school_year.id = evaluation.ano_lectivo_id
             WHERE school_year.ano = 2026 AND competency.tipo = 'Funcional'
             ORDER BY competency.orden"
        );
        $sourceIds = $result->fetch_all(MYSQLI_ASSOC);
        if (count($sourceIds) !== 8) {
            throw new RuntimeException('La plantilla funcional 2026 no está disponible. Ejecute primero el instalador.');
        }
        foreach ($sourceIds as $source) {
            $this->copyCompetency((int) $source['id'], $targetEvaluationId, null);
        }
    }

    private function cloneOrCreateBehavioralCompetency(int $targetEvaluationId, string $name, int $order): void
    {
        $safeName = $this->database->real_escape_string($name);
        $source = $this->database->query(
            "SELECT competency.id
             FROM evidencias_competencias_evaluadas competency
             INNER JOIN evidencias_evaluaciones_anuales evaluation ON evaluation.id = competency.evaluacion_id
             INNER JOIN evidencias_anos_lectivos school_year ON school_year.id = evaluation.ano_lectivo_id
             WHERE school_year.ano = 2026 AND competency.tipo = 'Comportamental' AND competency.nombre_competencia = '" . $safeName . "'
             LIMIT 1"
        )->fetch_assoc();
        if ($source !== null) {
            $this->copyCompetency((int) $source['id'], $targetEvaluationId, $order);

            return;
        }
        $this->insertBehavioralTemplate($targetEvaluationId, $name, $order);
    }

    private function copyCompetency(int $sourceId, int $targetEvaluationId, ?int $overrideOrder): void
    {
        $source = $this->database->query(
            'SELECT tipo, area_gestion, nombre_competencia, contribucion_individual, orden
             FROM evidencias_competencias_evaluadas WHERE id = ' . $sourceId
        )->fetch_assoc();
        if ($source === null) {
            throw new RuntimeException('No se pudo leer la plantilla de competencia.');
        }
        $targetOrder = $overrideOrder ?? (int) $source['orden'];
        $statement = $this->database->prepare(
            'INSERT INTO evidencias_competencias_evaluadas (evaluacion_id, tipo, area_gestion, nombre_competencia, contribucion_individual, orden)
             VALUES (?, ?, ?, ?, ?, ?)'
        );
        $statement->bind_param('issssi', $targetEvaluationId, $source['tipo'], $source['area_gestion'], $source['nombre_competencia'], $source['contribucion_individual'], $targetOrder);
        $statement->execute();
        $targetCompetencyId = (int) $this->database->insert_id;

        $criteria = $this->database->query(
            'SELECT id, orden, descripcion FROM evidencias_criterios_evaluacion WHERE competencia_evaluada_id = ' . $sourceId . ' ORDER BY orden'
        );
        while ($criterion = $criteria->fetch_assoc()) {
            $criterionStatement = $this->database->prepare(
                'INSERT INTO evidencias_criterios_evaluacion (competencia_evaluada_id, orden, descripcion) VALUES (?, ?, ?)'
            );
            $criterionOrder = (int) $criterion['orden'];
            $criterionStatement->bind_param('iis', $targetCompetencyId, $criterionOrder, $criterion['descripcion']);
            $criterionStatement->execute();
            $targetCriterionId = (int) $this->database->insert_id;
            $evidence = $this->database->query(
                'SELECT titulo, descripcion, tipo FROM evidencias_evidencias WHERE criterio_id = ' . (int) $criterion['id'] . ' ORDER BY id'
            );
            while ($evidenceRow = $evidence->fetch_assoc()) {
                $evidenceStatement = $this->database->prepare(
                    'INSERT INTO evidencias_evidencias (criterio_id, titulo, descripcion, tipo) VALUES (?, ?, ?, ?)'
                );
                $evidenceStatement->bind_param('isss', $targetCriterionId, $evidenceRow['titulo'], $evidenceRow['descripcion'], $evidenceRow['tipo']);
                $evidenceStatement->execute();
            }
        }
    }

    private function insertBehavioralTemplate(int $evaluationId, string $name, int $order): void
    {
        $template = $this->fallbackBehavioralTemplates()[$name] ?? null;
        if ($template === null) {
            throw new InvalidArgumentException('No existe una plantilla para la competencia comportamental seleccionada.');
        }
        $statement = $this->database->prepare(
            "INSERT INTO evidencias_competencias_evaluadas (evaluacion_id, tipo, nombre_competencia, contribucion_individual, orden) VALUES (?, 'Comportamental', ?, ?, ?)"
        );
        $statement->bind_param('issi', $evaluationId, $name, $template['contribucion'], $order);
        $statement->execute();
        $competencyId = (int) $this->database->insert_id;
        foreach ($template['criterios'] as $criterionOrder => $criterion) {
            $criterionStatement = $this->database->prepare(
                'INSERT INTO evidencias_criterios_evaluacion (competencia_evaluada_id, orden, descripcion) VALUES (?, ?, ?)'
            );
            $number = $criterionOrder + 1;
            $criterionStatement->bind_param('iis', $competencyId, $number, $criterion);
            $criterionStatement->execute();
            $criterionId = (int) $this->database->insert_id;
            $title = 'Evidencia verificable para ' . $criterion;
            $evidenceStatement = $this->database->prepare(
                "INSERT INTO evidencias_evidencias (criterio_id, titulo, tipo) VALUES (?, ?, 'Documental')"
            );
            $evidenceStatement->bind_param('is', $criterionId, $title);
            $evidenceStatement->execute();
        }
    }

    /** @return array<int,string> */
    private function behavioralCatalog(): array
    {
        return [
            'Liderazgo',
            'Comunicación y relaciones interpersonales',
            'Trabajo en equipo',
            'Negociación y mediación',
            'Compromiso social e institucional',
            'Iniciativa',
            'Orientación al logro',
        ];
    }

    /** @return array<string,array{contribucion:string,criterios:array<int,string>}> */
    private function fallbackBehavioralTemplates(): array
    {
        return [
            'Liderazgo' => ['contribucion' => 'Orientar acciones pedagógicas e institucionales movilizando al equipo hacia resultados verificables.', 'criterios' => ['Convoca y orienta al equipo hacia metas compartidas.', 'Toma decisiones oportunas y fundamentadas.', 'Hace seguimiento a los compromisos asumidos.']],
            'Comunicación y relaciones interpersonales' => ['contribucion' => 'Establecer relaciones respetuosas y canales de comunicación claros con la comunidad educativa.', 'criterios' => ['Comunica información con claridad y oportunidad.', 'Escucha y reconoce las perspectivas de los demás.', 'Mantiene relaciones respetuosas y colaborativas.']],
            'Negociación y mediación' => ['contribucion' => 'Gestionar acuerdos y resolver situaciones de conflicto mediante el diálogo y la mediación.', 'criterios' => ['Identifica intereses y necesidades de las partes.', 'Propone alternativas de solución concertada.', 'Formaliza y hace seguimiento a los acuerdos.']],
            'Orientación al logro' => ['contribucion' => 'Planear y ejecutar acciones orientadas al cumplimiento verificable de metas institucionales.', 'criterios' => ['Define metas claras y medibles.', 'Ejecuta acciones con oportunidad y calidad.', 'Analiza resultados y aplica mejoras.']],
        ];
    }

    private function insertAttachment(int $evidenceId, string $fileType, string $originalName, string $url, ?string $mimeType, ?int $size): void
    {
        $statement = $this->database->prepare(
            'INSERT INTO evidencias_adjuntos_evidencia (evidencia_id, tipo_archivo, nombre_original, url_archivo, mime_type, tamano_bytes)
             VALUES (?, ?, ?, ?, ?, ?)'
        );
        $statement->bind_param('issssi', $evidenceId, $fileType, $originalName, $url, $mimeType, $size);
        $statement->execute();
    }

    private function fileTypeFromExtension(string $extension): ?string
    {
        if (in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp'], true)) {
            return 'Imagen';
        }
        if (in_array($extension, ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx'], true)) {
            return 'Documento';
        }
        if (in_array($extension, ['mp4', 'webm', 'ogv'], true)) {
            return 'Video';
        }
        if (in_array($extension, ['mp3', 'wav', 'ogg', 'm4a'], true)) {
            return 'Audio';
        }

        return null;
    }

    private function log(int $evaluationId, string $action, string $detail): void
    {
        $statement = $this->database->prepare('INSERT INTO evidencias_eventos_auditoria (evaluacion_id, accion, detalle) VALUES (?, ?, ?)');
        $statement->bind_param('iss', $evaluationId, $action, $detail);
        $statement->execute();
    }
}
