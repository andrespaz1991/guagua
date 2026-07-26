<?php
declare(strict_types=1);

final class EvaluationSeeder
{
    private mysqli $database;

    public function __construct(mysqli $database)
    {
        $this->database = $database;
    }

    public function seed2026(): int
    {
        $this->database->begin_transaction();

        try {
            $teacherId = $this->upsertTeacher();
            $schoolYearId = $this->upsertSchoolYear();
            $evaluationId = $this->upsertEvaluation($teacherId, $schoolYearId);

            foreach ($this->functionalCompetencies() as $order => $competency) {
                $this->upsertCompetency($evaluationId, 'Funcional', $competency['area'], $competency, $order + 1);
            }

            foreach ($this->behavioralCompetencies() as $order => $competency) {
                $this->upsertCompetency($evaluationId, 'Comportamental', null, $competency, $order + 1);
            }

            $this->log($evaluationId, 'Semilla 2026 instalada', 'Evaluación de HUGO ANDRES PAZ BURBANO cargada desde el Anexo 5.');
            $this->database->commit();

            return $evaluationId;
        } catch (Throwable $exception) {
            $this->database->rollback();
            throw $exception;
        }
    }

    private function upsertTeacher(): int
    {
        $statement = $this->database->prepare(
            'INSERT INTO evidencias_docentes (nombre, cedula, nivel, zona, institucion)
             VALUES (?, ?, ?, ?, ?)
             ON DUPLICATE KEY UPDATE id = LAST_INSERT_ID(id), nombre = VALUES(nombre)'
        );
        $name = 'HUGO ANDRES PAZ BURBANO';
        $identification = '1085290375';
        $level = 'Docente';
        $zone = 'San Luis';
        $institution = 'Institución educativa pública';
        $statement->bind_param('sssss', $name, $identification, $level, $zone, $institution);
        $statement->execute();

        return (int) $this->database->insert_id;
    }

    private function upsertSchoolYear(): int
    {
        $statement = $this->database->prepare(
            "INSERT INTO evidencias_anos_lectivos (ano, estado) VALUES (2026, 'Activo')
             ON DUPLICATE KEY UPDATE id = LAST_INSERT_ID(id)"
        );
        $statement->execute();

        return (int) $this->database->insert_id;
    }

    private function upsertEvaluation(int $teacherId, int $schoolYearId): int
    {
        $statement = $this->database->prepare(
            "INSERT INTO evidencias_evaluaciones_anuales
                (docente_id, ano_lectivo_id, ponderacion_academica, ponderacion_administrativa, ponderacion_comunitaria, fecha_inicio, ciudad_concertacion, dias_valoracion_1, dias_valoracion_2, estado)
             VALUES (?, ?, 30, 20, 20, '2026-04-19', 'San Luis', 0, 0, 'En Concertación')
             ON DUPLICATE KEY UPDATE id = LAST_INSERT_ID(id)"
        );
        $statement->bind_param('ii', $teacherId, $schoolYearId);
        $statement->execute();

        return (int) $this->database->insert_id;
    }

    /** @param array{nombre:string,contribucion:string,criterios:array<int,array{descripcion:string,evidencias:array<int,array{titulo:string,tipo:string}>}>,area?:string} $competency */
    private function upsertCompetency(int $evaluationId, string $type, ?string $area, array $competency, int $order): void
    {
        $statement = $this->database->prepare(
            'INSERT INTO evidencias_competencias_evaluadas (evaluacion_id, tipo, area_gestion, nombre_competencia, contribucion_individual, orden)
             VALUES (?, ?, ?, ?, ?, ?)
             ON DUPLICATE KEY UPDATE id = LAST_INSERT_ID(id), area_gestion = VALUES(area_gestion), contribucion_individual = VALUES(contribucion_individual), orden = VALUES(orden)'
        );
        $name = $competency['nombre'];
        $contribution = $competency['contribucion'];
        $statement->bind_param('issssi', $evaluationId, $type, $area, $name, $contribution, $order);
        $statement->execute();
        $competencyId = (int) $this->database->insert_id;

        foreach ($competency['criterios'] as $criterionOrder => $criterion) {
            $this->upsertCriterion($competencyId, $criterionOrder + 1, $criterion);
        }
    }

    /** @param array{descripcion:string,evidencias:array<int,array{titulo:string,tipo:string}>} $criterion */
    private function upsertCriterion(int $competencyId, int $order, array $criterion): void
    {
        $statement = $this->database->prepare(
            'INSERT INTO evidencias_criterios_evaluacion (competencia_evaluada_id, orden, descripcion)
             VALUES (?, ?, ?)
             ON DUPLICATE KEY UPDATE id = LAST_INSERT_ID(id), descripcion = VALUES(descripcion)'
        );
        $description = $criterion['descripcion'];
        $statement->bind_param('iis', $competencyId, $order, $description);
        $statement->execute();
        $criterionId = (int) $this->database->insert_id;

        foreach ($criterion['evidencias'] as $evidence) {
            $evidenceStatement = $this->database->prepare(
                'INSERT INTO evidencias_evidencias (criterio_id, titulo, tipo)
                 VALUES (?, ?, ?)
                 ON DUPLICATE KEY UPDATE titulo = VALUES(titulo)'
            );
            $title = $evidence['titulo'];
            $evidenceType = $evidence['tipo'];
            $evidenceStatement->bind_param('iss', $criterionId, $title, $evidenceType);
            $evidenceStatement->execute();
        }
    }

    private function log(int $evaluationId, string $action, string $detail): void
    {
        $statement = $this->database->prepare('INSERT INTO evidencias_eventos_auditoria (evaluacion_id, accion, detalle) VALUES (?, ?, ?)');
        $statement->bind_param('iss', $evaluationId, $action, $detail);
        $statement->execute();
    }

    /** @return array<int,array<string,mixed>> */
    private function functionalCompetencies(): array
    {
        return [
            [
                'area' => 'Académica',
                'nombre' => 'Dominio curricular',
                'contribucion' => 'Estructurar unidades didácticas del área de tecnología e informática integrando recursos digitales abiertos para contextualizar los contenidos de acuerdo con el Plan de Área.',
                'criterios' => [
                    ['descripcion' => 'Diseña contenidos digitales que responden a las necesidades e intereses de los estudiantes.', 'evidencias' => [
                        ['titulo' => 'Malla de tecnología actualizada', 'tipo' => 'Documental'],
                    ]],
                    ['descripcion' => 'Usa los contenidos digitales diseñados para responder a las necesidades e intereses de los estudiantes.', 'evidencias' => [
                        ['titulo' => 'Registro de la mediación tecnológica en el aula', 'tipo' => 'Documental'],
                        ['titulo' => 'Capturas o reportes de resultados de la aplicación de los RED', 'tipo' => 'Documental'],
                    ]],
                    ['descripcion' => 'Construye un repositorio de recursos educativos digitales.', 'evidencias' => [
                        ['titulo' => 'Enlace al repositorio de recursos educativos digitales', 'tipo' => 'Testimonial'],
                    ]],
                ],
            ],
            [
                'area' => 'Académica',
                'nombre' => 'Planeación y organización académica',
                'contribucion' => 'Establecer en clase reglas, normas y rutinas consistentes de convivencia en el aula de acuerdo con el Manual de Convivencia.',
                'criterios' => [
                    ['descripcion' => 'Presenta los pactos de aula construidos con sus estudiantes.', 'evidencias' => [
                        ['titulo' => 'Pacto de aula firmado o Reglamento de Sede', 'tipo' => 'Documental'],
                    ]],
                    ['descripcion' => 'Mantiene expuestas las normas y rutinas pactadas en el aula de clase.', 'evidencias' => [
                        ['titulo' => 'Cartelera de normas y rutinas expuesta en el aula', 'tipo' => 'Documental'],
                    ]],
                    ['descripcion' => 'Aplica el modelo dialógico para la resolución pacífica de conflictos.', 'evidencias' => [
                        ['titulo' => 'Diario pacificador', 'tipo' => 'Documental'],
                    ]],
                ],
            ],
            [
                'area' => 'Académica',
                'nombre' => 'Pedagógica y didáctica',
                'contribucion' => 'Ejecutar estrategias didácticas mediadas por TIC para la construcción del Proyecto de Vida de los estudiantes, de acuerdo con las orientaciones del PEI.',
                'criterios' => [
                    ['descripcion' => 'Diseña estrategias didácticas mediadas por TIC para la construcción del proyecto de vida.', 'evidencias' => [
                        ['titulo' => 'Guion técnico-pedagógico y talleres de reflexión sobre el proyecto de vida', 'tipo' => 'Documental'],
                        ['titulo' => 'Registro fotográfico de la fase de planeación y grabación en la sede', 'tipo' => 'Documental'],
                    ]],
                    ['descripcion' => 'Presenta una estrategia de evaluación de la estrategia implementada.', 'evidencias' => [
                        ['titulo' => 'Rúbrica de evaluación diligenciada por estudiantes', 'tipo' => 'Documental'],
                    ]],
                    ['descripcion' => 'Utiliza las estrategias didácticas diseñadas en el aula de clase.', 'evidencias' => [
                        ['titulo' => 'Video testimonial donde los estudiantes exponen su proyecto de vida', 'tipo' => 'Testimonial'],
                    ]],
                ],
            ],
            [
                'area' => 'Académica',
                'nombre' => 'Evaluación del aprendizaje',
                'contribucion' => 'Evaluar los aprendizajes teniendo en cuenta un enfoque integral, de acuerdo con las orientaciones y escalas de valoración del S.I.E.',
                'criterios' => [
                    ['descripcion' => 'Elabora instrumentos que permiten valorar el progreso de forma continua y participativa.', 'evidencias' => [
                        ['titulo' => 'Rúbricas de evaluación digitalizadas', 'tipo' => 'Documental'],
                    ]],
                    ['descripcion' => 'Usa instrumentos de evaluación que permiten valorar el progreso de forma continua y participativa.', 'evidencias' => [
                        ['titulo' => 'Registro de la aplicación de autoevaluación', 'tipo' => 'Documental'],
                        ['titulo' => 'Registro de evaluaciones realizadas durante el año', 'tipo' => 'Documental'],
                    ]],
                    ['descripcion' => 'Genera espacios de reflexión que permiten valorar el proceso de formación.', 'evidencias' => [
                        ['titulo' => 'Video testimonial de reflexiones sobre auto y coevaluación', 'tipo' => 'Testimonial'],
                    ]],
                ],
            ],
            [
                'area' => 'Administrativa',
                'nombre' => 'Uso de recursos',
                'contribucion' => 'Sistematizar el aprovechamiento de los recursos de la sede mediante un catálogo digital cumpliendo con los protocolos de inventario y cuidado de activos institucionales.',
                'criterios' => [
                    ['descripcion' => 'Gestiona el uso eficiente de los equipos garantizando su disponibilidad.', 'evidencias' => [
                        ['titulo' => 'Formato de control de préstamo y estado', 'tipo' => 'Documental'],
                    ]],
                    ['descripcion' => 'Promueve el uso eficiente de los equipos garantizando su disponibilidad.', 'evidencias' => [
                        ['titulo' => 'Galería del inventario tecnológico organizado', 'tipo' => 'Documental'],
                        ['titulo' => 'Registro fotográfico de material didáctico y uso eficiente de equipos', 'tipo' => 'Documental'],
                    ]],
                    ['descripcion' => 'Presenta inventario de sede actualizado y sincronizado con el almacén municipal.', 'evidencias' => [
                        ['titulo' => 'Documento con el listado de elementos institucionales', 'tipo' => 'Documental'],
                    ]],
                ],
            ],
            [
                'area' => 'Administrativa',
                'nombre' => 'Seguimiento de procesos',
                'contribucion' => 'Crear espacios de acompañamiento como estrategia de seguimiento y nivelación pedagógica utilizando la biblioteca tutorizada de acuerdo al modelo pedagógico institucional.',
                'criterios' => [
                    ['descripcion' => 'Registra de manera organizada la asistencia y participación en los espacios de acompañamiento.', 'evidencias' => [
                        ['titulo' => 'Fotografía de participación en la biblioteca tutorizada', 'tipo' => 'Documental'],
                        ['titulo' => 'Formato de asistencia de biblioteca tutorizada', 'tipo' => 'Documental'],
                    ]],
                    ['descripcion' => 'Presenta material pedagógico diseñado para los espacios de acompañamiento.', 'evidencias' => [
                        ['titulo' => 'Material pedagógico de biblioteca tutorizada', 'tipo' => 'Documental'],
                    ]],
                    ['descripcion' => 'Evidencia rúbricas de evaluación para la biblioteca tutorizada.', 'evidencias' => [
                        ['titulo' => 'Rúbricas de evaluación de biblioteca tutorizada', 'tipo' => 'Documental'],
                    ]],
                ],
            ],
            [
                'area' => 'Comunitaria',
                'nombre' => 'Comunicación institucional',
                'contribucion' => 'Establecer un protocolo de comunicación administrativa con las familias mediante canales TIC eficientes para informar sobre procesos institucionales y académicos, adaptándose a la conectividad de la zona y según el PEI.',
                'criterios' => [
                    ['descripcion' => 'Establece canales de comunicación efectivos y permanentes con las familias para el reporte de procesos.', 'evidencias' => [
                        ['titulo' => 'Registro de interacción con familias vía mensajería instantánea', 'tipo' => 'Documental'],
                    ]],
                    ['descripcion' => 'Convoca a la comunidad educativa al seguimiento de procesos académicos.', 'evidencias' => [
                        ['titulo' => 'Registro de asistencia a entrega de informes y alertas académicas', 'tipo' => 'Documental'],
                    ]],
                    ['descripcion' => 'Involucra a las familias en la realización de eventos institucionales.', 'evidencias' => [
                        ['titulo' => 'Video de red social de divulgación de actividades institucionales', 'tipo' => 'Testimonial'],
                        ['titulo' => 'Invitación o registro fotográfico de participación comunitaria en eventos', 'tipo' => 'Documental'],
                    ]],
                ],
            ],
            [
                'area' => 'Comunitaria',
                'nombre' => 'Interacción con la comunidad y el entorno',
                'contribucion' => 'Vincular la comunidad educativa en la realización de proyectos transversales, favoreciendo el sentido de pertenencia enmarcado en el PEI.',
                'criterios' => [
                    ['descripcion' => 'Construye un plan de acción para integrar a la comunidad en el proyecto comunitario.', 'evidencias' => [
                        ['titulo' => 'Plan de acción del proyecto comunitario', 'tipo' => 'Documental'],
                    ]],
                    ['descripcion' => 'Genera espacios de encuentro con líderes o padres de familia de la sede.', 'evidencias' => [
                        ['titulo' => 'Registro fotográfico de encuentro con líderes o padres de familia', 'tipo' => 'Documental'],
                    ]],
                    ['descripcion' => 'Gestiona apoyo de diversas entidades para el mejoramiento de la sede.', 'evidencias' => [
                        ['titulo' => 'Cartas de intención de gestión comunitaria', 'tipo' => 'Documental'],
                    ]],
                ],
            ],
        ];
    }

    /** @return array<int,array<string,mixed>> */
    private function behavioralCompetencies(): array
    {
        return [
            [
                'nombre' => 'Trabajo en equipo',
                'contribucion' => 'Colaborar activamente con el equipo en procesos de formación en el uso de herramientas digitales, aportando materiales y registros verificables.',
                'criterios' => [
                    ['descripcion' => 'Participa activamente en espacios de formación al equipo de trabajo.', 'evidencias' => [['titulo' => 'Registro fotográfico de espacios de formación al equipo de trabajo', 'tipo' => 'Documental']]],
                    ['descripcion' => 'Comparte material didáctico pertinente para la formación del equipo.', 'evidencias' => [['titulo' => 'Material didáctico empleado en la formación al equipo de trabajo', 'tipo' => 'Documental']]],
                    ['descripcion' => 'Aporta al desarrollo de proyectos transversales institucionales.', 'evidencias' => [['titulo' => 'Actualización del proyecto transversal institucional', 'tipo' => 'Documental']]],
                ],
            ],
            [
                'nombre' => 'Iniciativa',
                'contribucion' => 'Proponer soluciones para mejorar los procesos de gestión institucional.',
                'criterios' => [
                    ['descripcion' => 'Identifica oportunidades de mejora y propone soluciones viables para la gestión institucional.', 'evidencias' => [['titulo' => 'Propuesta de solución para mejorar la gestión institucional', 'tipo' => 'Documental']]],
                    ['descripcion' => 'Participa en la construcción de soluciones digitales de comunicación institucional.', 'evidencias' => [['titulo' => 'Registro fotográfico de construcción de la página web institucional', 'tipo' => 'Documental']]],
                    ['descripcion' => 'Promueve actividades institucionales que favorecen el bienestar y la participación.', 'evidencias' => [['titulo' => 'Registro fotográfico de encuentros deportivos', 'tipo' => 'Documental']]],
                ],
            ],
            [
                'nombre' => 'Compromiso social e institucional',
                'contribucion' => 'Fomentar la formación en valores y la participación en actividades que impactan positivamente la convivencia y el entorno comunitario.',
                'criterios' => [
                    ['descripcion' => 'Elabora materiales pedagógicos que fomentan la sana convivencia dentro de la institución.', 'evidencias' => [['titulo' => 'Elaboración de material pedagógico para la sana convivencia', 'tipo' => 'Documental']]],
                    ['descripcion' => 'Aplica estrategias de resolución pacífica de conflictos en la institución.', 'evidencias' => [['titulo' => 'Registro fotográfico de aplicación del modelo dialógico', 'tipo' => 'Documental']]],
                    ['descripcion' => 'Promueve la participación en actividades que fortalecen el entorno comunitario.', 'evidencias' => [['titulo' => 'Registro de participación en actividades de convivencia institucional', 'tipo' => 'Documental']]],
                ],
            ],
        ];
    }
}
