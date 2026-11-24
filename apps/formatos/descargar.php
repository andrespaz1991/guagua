<?php
// Se carga PHPWord antes del autoloader histórico de Guagua: aquel finaliza la
// ejecución para clases ajenas a /clases y bloquearía librerías externas.
require_once dirname(__DIR__, 2) . '/comun/config.php';
require_once dirname(__DIR__, 2) . '/Planeador/PHPWord-master/src/PhpWord/Autoloader.php';
\PhpOffice\PhpWord\Autoloader::register();
require_once dirname(__DIR__, 2) . '/comun/lib/vendor/autoload.php';
require_once SGA_COMUN_SERVER . '/conexion.php';
require_once __DIR__ . '/lib/FormatosService.php';

formatos_require_access();
formatos_ensure_schema($mysqli);
$ctx = formatos_context($mysqli);

use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory as WordIOFactory;
use PhpOffice\PhpWord\TemplateProcessor;
use PhpOffice\PhpWord\SimpleType\Jc;
use PhpOffice\PhpWord\SimpleType\JcTable;
use PhpOffice\PhpWord\SimpleType\VerticalJc;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;

function fd_value($key, $default = '') { return trim((string)($_POST[$key] ?? $default)); }
function fd_date($date): string { try { return $date ? (new DateTime($date))->format('d/m/Y') : ''; } catch (Throwable $e) { return (string)$date; } }
function fd_clean_name(string $name): string { return preg_replace('/[^A-Za-z0-9_-]+/', '_', $name); }
function fd_doc(): array {
    $word = new PhpWord();
    $word->setDefaultFontName('Arial'); $word->setDefaultFontSize(10);
    $word->addParagraphStyle('FDTitle', ['alignment'=>Jc::CENTER, 'spaceAfter'=>160]);
    $word->addParagraphStyle('FDSub', ['alignment'=>Jc::CENTER, 'spaceAfter'=>120]);
    $word->addParagraphStyle('FDSection', ['spaceBefore'=>180, 'spaceAfter'=>80]);
    $section = $word->addSection(['pageSizeW'=>12240,'pageSizeH'=>15840,'marginTop'=>680,'marginBottom'=>680,'marginLeft'=>720,'marginRight'=>720]);
    return [$word, $section];
}
function fd_header($section, string $code, string $title, array $ctx): void {
    $header = $section->addHeader();
    $table = $header->addTable(['borderBottomSize'=>8,'borderBottomColor'=>'1E3A8A','cellMargin'=>70]);
    $table->addRow();
    $table->addCell(1700)->addText('INSTITUCIÓN EDUCATIVA RURAL\nLA JOSEFINA', ['bold'=>true,'size'=>8,'color'=>'172554']);
    $table->addCell(5550)->addText($title, ['bold'=>true,'size'=>10,'color'=>'172554'], ['alignment'=>Jc::CENTER]);
    $table->addCell(1700)->addText($code . "\n" . $ctx['ano'], ['size'=>8,'color'=>'475569'], ['alignment'=>Jc::RIGHT]);
}
function fd_title($section, string $title, ?string $subtitle = null): void {
    $section->addText($title, ['bold'=>true,'size'=>16,'color'=>'172554'], 'FDTitle');
    if ($subtitle) $section->addText($subtitle, ['size'=>9,'color'=>'64748B'], 'FDSub');
}
function fd_heading($section, string $text): void { $section->addText($text, ['bold'=>true,'size'=>10,'color'=>'1E3A8A'], 'FDSection'); }
function fd_table($section, array $headers, array $rows, array $widths = [], bool $landscape = false) {
    $style = ['borderSize'=>5,'borderColor'=>'B9C5D7','cellMarginTop'=>90,'cellMarginBottom'=>90,'cellMarginLeft'=>90,'cellMarginRight'=>90,'alignment'=>JcTable::CENTER];
    $table = $section->addTable($style);
    $table->addRow();
    foreach ($headers as $index=>$header) {
        $cell = $table->addCell($widths[$index] ?? null, ['bgColor'=>'E8EEFB','valign'=>VerticalJc::CENTER]);
        $cell->addText($header, ['bold'=>true,'size'=>8,'color'=>'172554'], ['alignment'=>Jc::CENTER]);
    }
    foreach ($rows as $row) {
        $table->addRow();
        foreach ($headers as $index=>$header) {
            $value = $row[$index] ?? '';
            $cell = $table->addCell($widths[$index] ?? null, ['valign'=>VerticalJc::CENTER]);
            $cell->addText((string)$value, ['size'=>8], ['alignment'=>is_numeric($value) ? Jc::CENTER : Jc::LEFT]);
        }
    }
    return $table;
}
function fd_download_word(PhpWord $word, string $filename): void {
    while (ob_get_level()) ob_end_clean();
    header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
    header('Content-Disposition: attachment; filename="' . fd_clean_name($filename) . '.docx"');
    header('Cache-Control: max-age=0');
    WordIOFactory::createWriter($word, 'Word2007')->save('php://output');
    exit;
}
function fd_original_template(string $name): TemplateProcessor {
    $path = __DIR__ . '/plantillas/' . $name . '.docx';
    if (!is_file($path)) throw new RuntimeException('No está disponible la copia editable del formato original: ' . $name);
    return new TemplateProcessor($path);
}
function fd_download_original(TemplateProcessor $template, string $filename): void {
    $tmp = tempnam(sys_get_temp_dir(), 'guagua_formato_');
    if ($tmp === false) throw new RuntimeException('No fue posible preparar el archivo de descarga.');
    $output = $tmp . '.docx';
    @unlink($tmp);
    $template->saveAs($output);
    while (ob_get_level()) ob_end_clean();
    header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
    header('Content-Disposition: attachment; filename="' . fd_clean_name($filename) . '.docx"');
    header('Cache-Control: max-age=0');
    readfile($output);
    @unlink($output);
    exit;
}
function fd_month_es(string $date): string {
    $months = ['','enero','febrero','marzo','abril','mayo','junio','julio','agosto','septiembre','octubre','noviembre','diciembre'];
    try { return $months[(int)(new DateTime($date ?: 'today'))->format('n')]; } catch (Throwable $e) { return ''; }
}
function fd_generate_original_observer(mysqli $mysqli, array $ctx): void {
    
    $student = formatos_student($mysqli, $ctx, fd_value('estudiante'));
    if (!$student) throw new RuntimeException('Selecciona un estudiante activo para generar el observador.');
    if (isset($_POST['observador']) && is_array($_POST['observador'])) {
        formatos_save_observer($mysqli, $ctx, (string)$student['id_usuario'], $_POST['observador'], (string)($_POST['observaciones'] ?? ''), (string)$_SESSION['id_usuario']);
    }
    $data = formatos_observer_data($mysqli, $ctx, (string)$student['id_usuario']);
    $values = [
        'ESTUDIANTE' => formatos_full_name($student), 'ANO_FINAL' => substr((string)$ctx['ano'], -1),
        'GRADO' => $student['grado'] ?: '', 'OBSERVACIONES' => $data['observaciones'] ?: '',
    ];
    foreach (formatos_observer_items() as $key => $label) {
        $selected = $data['items'][$key] ?? '';
        foreach (['N','AV','S'] as $option) $values['O_' . strtoupper($key) . '_' . $option] = $selected === $option ? 'X' : '';
    }
    $template = fd_original_template('observador'); $template->setValues($values);
    fd_download_original($template, 'Observador_' . formatos_full_name($student) . '_' . $ctx['ano']);
}
function fd_observer_student(mysqli $mysqli, array $ctx): array {
    $student = formatos_student($mysqli, $ctx, fd_value('estudiante'));
    if (!$student) throw new RuntimeException('Selecciona un estudiante para generar el formato.');
    return $student;
}
function fd_generate_original_cover(mysqli $mysqli, array $ctx): void {
    $student = fd_observer_student($mysqli, $ctx);
    $template = fd_original_template('portada_observador');
    $template->setValue('ESTUDIANTE', formatos_full_name($student));
    fd_download_original($template, 'Portada_Observador_' . formatos_full_name($student) . '_' . $ctx['ano']);
}
function fd_generate_original_back_cover(mysqli $mysqli, array $ctx): void {
    $student = fd_observer_student($mysqli, $ctx);
    $template = fd_original_template('contraportada_observador');
    $template->setValues([
        'ESTUDIANTE' => formatos_full_name($student), 'ACUDIENTE' => $student['acudiente'] ?: '',
        'CONDICION_FISICA' => $student['condicion_fisica'] ?: '', 'SANGRE' => $student['tipo_sangre'] ?: '',
        'ANO_FINAL' => substr((string)$ctx['ano'], -1), 'GRADO' => $student['grado'] ?: '',
        'DIRECCION' => $student['direccion'] ?: '', 'CELULAR' => $student['telefono'] ?: '', 'EPS' => '',
    ]);
    fd_download_original($template, 'Contraportada_Observador_' . formatos_full_name($student) . '_' . $ctx['ano']);
}
function fd_generate_original_commission(array $ctx): void {
    $date = fd_value('fecha', date('Y-m-d'));
    $values = [
        'PERIODO'=>fd_value('periodo',$ctx['nombre_periodo']), 'ANO_FINAL'=>substr(fd_value('ano',$ctx['ano']), -1),
        'SEDE'=>fd_value('sede','Vallesol'), 'GRADOS'=>fd_value('grados','6 - 11'), 'HORA'=>fd_value('hora',date('H:i')),
        'DIA'=>fd_date($date), 'MES'=>fd_month_es($date), 'SITUACION'=>fd_value('situacion'),
        'PADRES'=>fd_value('padres'), 'REPRESENTANTES'=>fd_value('representantes'),
        'DIRECTOR_RECOMENDACIONES'=>fd_value('recomendaciones'), 'DIRECTOR'=>fd_value('director', (string)($_SESSION['nombre_usu'] ?? '')),
    ];
    $template = fd_original_template('comision'); $template->setValues($values);
    fd_download_original($template, 'Acta_Comision_Evaluacion_' . fd_value('ano',$ctx['ano']));
}
function fd_generate_original_permission(mysqli $mysqli, array $ctx): void {
    $teacher = formatos_row($mysqli, 'SELECT id_usuario, nombre, apellido FROM usuario WHERE id_usuario = ? LIMIT 1', 's', [fd_value('docente', (string)($_SESSION['id_usuario'] ?? ''))]);
    if (!$teacher) throw new RuntimeException('Seleccione un docente válido.');
    $name = trim($teacher['nombre'] . ' ' . $teacher['apellido']);
    $absence = fd_value('fecha_ausencia', date('Y-m-d'));
    $values = [
        'DOCENTE'=>$name, 'DOCUMENTO'=>fd_value('documento', $teacher['id_usuario']), 'SEDE'=>(string)($_SESSION['configuracion_institucion']['nombre_institucion'] ?? ''),
        'MOTIVO'=>fd_value('motivo'), 'FECHA_AUSENCIA'=>fd_date($absence), 'TIEMPO'=>fd_value('tiempo'),
        'UNIDAD'=>fd_value('unidad','Horas'), 'SOPORTE'=>fd_value('soporte'), 'FECHA_SOLICITUD'=>fd_date(date('Y-m-d')),
    ];
    $template = fd_original_template('permiso'); $template->setValues($values);
    fd_download_original($template, 'Permiso_' . $name . '_' . $absence);
}
function fd_generate_original_dropout(mysqli $mysqli, array $ctx): void {
    $rows = fd_dropout_data($mysqli, $ctx); $template = fd_original_template('desercion');
    $count = max(1, count($rows)); $template->cloneRow('NUM', $count);
    $template->setValues(['DIRECTOR'=>'Andres Paz', 'FECHA'=>fd_date(date('Y-m-d'))]);
    if (!$rows) $rows = [['id_usuario'=>'','nombre'=>'Sin registros','apellido'=>'','grado'=>'','fecha_retiro'=>'','acciones'=>'']];
    foreach ($rows as $index=>$row) {
        $n = $index + 1;
        $template->setValue('NUM#' . $n, (string)$n);
        $template->setValue('ESTUDIANTE#' . $n, formatos_full_name($row));
        $template->setValue('GRADO#' . $n, $row['grado'] ?: '');
        $template->setValue('FECHA_RETIRO#' . $n, fd_date($row['fecha_retiro']));
        $template->setValue('ACCIONES#' . $n, $row['acciones'] ?: '');
    }
    fd_download_original($template, 'Desercion_Escolar_' . $ctx['ano']);
}
function fd_generate_original_recovery(array $ctx): void {
    $selected = $_POST['seleccion'] ?? []; $registry = $_POST['registro'] ?? []; $rows = [];
    foreach ($selected as $key) if (isset($registry[$key])) $rows[] = $registry[$key];
    if (!$rows) throw new RuntimeException('Selecciona al menos un estudiante con materia a recuperar.');
    $template = fd_original_template('refuerzo');
    $template->cloneRow('ESTUDIANTE', count($rows));
    $isRecovery = fd_value('modalidad','Refuerzo') === 'Recuperación';
    $template->setValues(['FECHA'=>fd_date(fd_value('fecha',date('Y-m-d'))), 'RECUPERACION'=>$isRecovery ? 'X' : '', 'REFUERZO'=>$isRecovery ? '' : 'X']);
    foreach ($rows as $index=>$row) {
        $n = $index + 1;
        foreach (['ESTUDIANTE'=>'estudiante','GRADO'=>'grado','GRUPO'=>'grupo','JORNADA'=>'jornada','MATERIA'=>'materia','COMPETENCIA'=>'competencia'] as $marker=>$field) {
            $value = $row[$field] ?? '';
            if ($field === 'jornada' && $value === '') $value = fd_value('jornada', 'Mañana');
            $template->setValue($marker . '#' . $n, (string)$value);
        }
        $activity = !empty($_POST['aplicar_todos']) ? fd_value('actividad_todos') : trim((string)($row['actividad'] ?? ''));
        foreach (['TALLER'=>'taller', 'ORAL'=>'oral', 'ESCRITA'=>'escrita', 'OTRA'=>'otra'] as $marker => $option) {
            $template->setValue($marker . '#' . $n, $activity === $option ? 'X' : '');
        }
    }
    fd_download_original($template, 'Planilla_' . fd_value('modalidad','Refuerzo') . '_' . $ctx['ano']);
}
function fd_generate_original_academic_report(mysqli $mysqli, array $ctx): void {
    $requested = $_POST['estudiantes'] ?? [fd_value('estudiante')];
    if (!is_array($requested)) $requested = [$requested];
    $requested = array_values(array_unique(array_filter(array_map('strval', $requested))));
    $active = [];
    foreach (formatos_students($mysqli, $ctx) as $student) $active[(string)$student['id_usuario']] = $student;
    $students = [];
    foreach ($requested as $id) if (isset($active[$id])) $students[] = $active[$id];
    if (!$students) throw new RuntimeException('Selecciona al menos un estudiante activo para generar el informe académico.');

    $recoveryByStudent = [];
    foreach (formatos_recovery_rows($mysqli, $ctx) as $row) $recoveryByStudent[(string)$row['id_estudiante']][] = $row;
    $lines = [];
    foreach ($students as $student) {
        $studentRows = $recoveryByStudent[(string)$student['id_usuario']] ?? [];
        if (!$studentRows) $studentRows = [['materia' => 'Sin áreas con desempeño bajo', 'grado' => $student['grado'] ?: '', 'dba' => '']];
        foreach ($studentRows as $row) $lines[] = ['student' => $student, 'row' => $row];
    }
    $categories = $_POST['categorias'] ?? [];
    if (!is_array($categories)) $categories = [];
    $categories = array_values(array_map('strval', $categories));
    $criteriaByStudent = $_POST['criterios'] ?? [];
    if (!is_array($criteriaByStudent)) $criteriaByStudent = [];
    $template = fd_original_template('informe_academico');
    $template->cloneRow('ESTUDIANTE', count($lines));
    $template->setValues([
        'ANO_FINAL' => substr((string)$ctx['ano'], -1), 'PERIODO' => $ctx['nombre_periodo'],
        'DOCENTE' => fd_value('docente_informe', 'Andres Paz'), 'SEDE' => fd_value('sede_informe', 'Vallesol'),
    ]);
    foreach ($lines as $index => $line) {
        $n = $index + 1;
        $student = $line['student']; $row = $line['row'];
        $template->setValue('ESTUDIANTE#' . $n, formatos_full_name($student));
        $template->setValue('GRADO#' . $n, $row['grado'] ?: '');
        $template->setValue('AREA#' . $n, $row['materia'] ?: '');
        $template->setValue('DBA#' . $n, $row['dba'] ?: '');
        $studentCriteria = $criteriaByStudent[(string)$student['id_usuario']] ?? $categories;
        if (!is_array($studentCriteria)) $studentCriteria = [];
        $studentCriteria = array_flip(array_map('strval', $studentCriteria));
        foreach (['DIFICULTAD_ESTUDIO' => 'dificultad_estudio', 'COMPETENCIAS' => 'competencias', 'COMPORTAMIENTO' => 'comportamiento', 'FAMILIA' => 'familia', 'AUSENCIAS' => 'ausencias'] as $marker => $category) {
            $template->setValue($marker . '#' . $n, isset($studentCriteria[$category]) ? 'X' : '');
        }
    }
    $suffix = count($students) === 1 ? formatos_full_name($students[0]) : count($students) . '_estudiantes';
    fd_download_original($template, 'Informe_Academico_' . $suffix . '_' . $ctx['ano']);
}
function fd_generate_observer(mysqli $mysqli, array $ctx): void {
    
    $student = formatos_student($mysqli, $ctx, fd_value('estudiante'));
    if (!$student) throw new RuntimeException('Selecciona un estudiante activo para generar el observador.');
    if (isset($_POST['observador']) && is_array($_POST['observador'])) {
        formatos_save_observer($mysqli, $ctx, (string)$student['id_usuario'], $_POST['observador'], (string)($_POST['observaciones'] ?? ''), (string)$_SESSION['id_usuario']);
    }
    $data = formatos_observer_data($mysqli, $ctx, (string)$student['id_usuario']);
    [$word,$section] = fd_doc(); fd_header($section, 'FICHA OBSERVADOR', 'FICHA OBSERVADOR DEL ESTUDIANTE', $ctx);
    $section->addTextBreak(3); fd_title($section, 'FICHA OBSERVADOR', 'Acompañamiento integral y seguimiento formativo');
    $section->addText('Estudiante: ' . formatos_full_name($student), ['bold'=>true,'size'=>14,'color'=>'172554'], ['alignment'=>Jc::CENTER]);
    $section->addTextBreak(2);
    fd_table($section, ['Año lectivo','Grado','Grupo','Jornada'], [[(string)$ctx['ano'], $student['grado'] ?: 'Sin registrar', $student['grupo'] ?: 'Sin registrar', $student['jornada'] ?: 'Sin registrar']], [2200,2200,2200,2200]);
    $section->addTextBreak(4); $section->addText('Documento generado desde Guagua · ' . date('d/m/Y H:i'), ['size'=>8,'color'=>'64748B'], ['alignment'=>Jc::CENTER]);
    $section->addPageBreak(); fd_header($section, 'FICHA OBSERVADOR', 'FICHA OBSERVADOR DEL ESTUDIANTE', $ctx);
    fd_title($section, 'Ficha observador del estudiante', 'Convenciones: N: Nunca · AV: Algunas veces · S: Siempre');
    $section->addText('Estudiante: ' . formatos_full_name($student), ['bold'=>true]);
    $section->addText('Año lectivo ' . $ctx['ano'] . ' · Grado: ' . ($student['grado'] ?: 'Sin registrar') . ' · Período ' . $ctx['periodo'], ['size'=>9,'color'=>'475569']);
    $rows = [];
    foreach (formatos_observer_items() as $key=>$label) {
        $selected = $data['items'][$key] ?? '';
        $rows[] = [$label, $selected === 'N' ? 'X' : '', $selected === 'AV' ? 'X' : '', $selected === 'S' ? 'X' : ''];
    }
    fd_table($section, ['Comportamiento observado','N','AV','S'], $rows, [6800,700,700,700]);
    fd_heading($section, 'Observaciones, estímulos, compromisos y correcciones');
    $section->addText($data['observaciones'] ?: 'Sin observaciones registradas para este período.', ['size'=>9], ['spaceAfter'=>120]);
    $section->addText('Firma director(a) de grupo: ____________________________     Firma acudiente: ____________________________', ['size'=>9]);
    $section->addPageBreak(); fd_header($section, 'FICHA OBSERVADOR', 'ACTUALIZACIÓN DE LA INFORMACIÓN DEL ESTUDIANTE', $ctx);
    fd_title($section, 'Contraportada · Ficha observador');
    fd_table($section, ['Campo','Información'], [
        ['Estudiante', formatos_full_name($student)], ['Acudiente', $student['acudiente'] ?: 'Sin registrar'],
        ['Condición física o discapacidad', $student['condicion_fisica'] ?: 'Sin registrar'], ['Grupo sanguíneo y RH', $student['tipo_sangre'] ?: 'Sin registrar'],
        ['Dirección', $student['direccion'] ?: 'Sin registrar'], ['Celular', $student['telefono'] ?: 'Sin registrar'],
    ], [3000,5800]);
    fd_heading($section, 'Actualización de la información del estudiante');
    fd_table($section, ['Año','Grado','Grupo','Jornada','Dirección','Celular'], [[(string)$ctx['ano'], $student['grado'] ?: '', $student['grupo'] ?: '', $student['jornada'] ?: '', $student['direccion'] ?: '', $student['telefono'] ?: '']], [900,900,900,1200,3500,1400]);
    fd_download_word($word, 'Observador_' . formatos_full_name($student) . '_' . $ctx['ano']);
}
function fd_generate_commission(array $ctx): void {
    [$word,$section] = fd_doc(); fd_header($section, 'IERLJ_FA_24', 'ACTA DE COMISIÓN Y EVALUACIÓN', $ctx);
    fd_title($section, 'ACTA DE REUNIÓN', 'Comisión de evaluación y promoción');
    fd_table($section, ['Período académico','Año lectivo','Sede','Grado(s)','Hora','Fecha'], [[fd_value('periodo',$ctx['nombre_periodo']),fd_value('ano',$ctx['ano']),fd_value('sede'),fd_value('grados'),fd_value('hora'),fd_date(fd_value('fecha'))]], [1400,1200,1700,1300,1000,1200]);
    $director = fd_value('director');
    fd_heading($section, 'Informe de la situación académica y comportamental del grupo');
    $section->addText(fd_value('situacion') ?: 'Sin información registrada.', ['size'=>10], ['spaceAfter'=>180]);
    fd_heading($section, 'Recomendaciones para superar las insuficiencias del grupo');
    foreach (['Padres de familia'=>'padres','Representantes de grupo'=>'representantes','Director(a) de grupo'=>'recomendaciones'] as $label=>$key) {
        $section->addText($label, ['bold'=>true,'size'=>10]); $section->addText(fd_value($key) ?: 'Sin recomendaciones registradas.', ['size'=>10], ['spaceAfter'=>130]);
    }
    fd_heading($section, 'Firman los representantes');
    fd_table($section, ['Consejo de padres','Comité de evaluación y promoción','Representante de grupo','Director(a) de grupo o monodocente'], [['____________________________','____________________________','____________________________',$director ?: '____________________________']], [2200,2200,2200,2200]);
    $section->addTextBreak(); $section->addText('Rector: __________________________________________', ['size'=>10]);
    fd_download_word($word, 'Acta_Comision_Evaluacion_' . fd_value('ano',$ctx['ano']));
}
function fd_generate_permission(mysqli $mysqli, array $ctx): void {
    $teacher = formatos_row($mysqli, 'SELECT id_usuario, nombre, apellido, tipo_documento FROM usuario WHERE id_usuario = ? LIMIT 1', 's', [fd_value('docente')]);
    $name = $teacher ? trim($teacher['nombre'] . ' ' . $teacher['apellido']) : 'Docente solicitante';
    $document = fd_value('documento', $teacher['id_usuario'] ?? '');
    [$word,$section] = fd_doc(); fd_header($section, 'IERLJ_PD_03', 'FORMATO DE PERMISO', $ctx);
    fd_title($section, 'SOLICITUD DE PERMISO', 'Registro institucional de ausencia docente');
    fd_table($section, ['Nombre del docente','Documento de identidad','Fecha de ausencia'], [[$name,$document,fd_date(fd_value('fecha_ausencia'))]], [3600,2300,2900]);
    fd_heading($section, 'Causales de la ausencia');
    $section->addText(fd_value('motivo'), ['size'=>10], ['spaceAfter'=>150]);
    fd_table($section, ['Tiempo de la ausencia','Soportes de la ausencia'], [[fd_value('tiempo') . ' ' . fd_value('unidad'), fd_value('soporte') ?: 'Sin enlace de soporte']], [3000,5800]);
    if (fd_value('soporte')) $section->addLink(fd_value('soporte'), 'Abrir soporte de la ausencia', ['color'=>'2563EB','underline'=>'single'], ['spaceBefore'=>80,'spaceAfter'=>160]);
    fd_heading($section, 'Aclaraciones');
    $section->addText('El permiso se valida cuando se presenta el soporte. Si no corresponde a salud, el docente deja la actividad de clase acordada con el tiempo de ausencia.', ['size'=>9,'color'=>'475569'], ['spaceAfter'=>200]);
    fd_table($section, ['Solicitó: docente','Aprobación: rector'], [[$name . "\nFecha de solicitud: " . date('d/m/Y'), "Fecha de revisión: __________________\nFirma: __________________"]], [4400,4400]);
    fd_download_word($word, 'Permiso_' . $name . '_' . fd_value('fecha_ausencia'));
}
function fd_dropout_data(mysqli $mysqli, array $ctx): array {
    if (!empty($_POST['acciones']) && is_array($_POST['acciones'])) formatos_save_dropout_actions($mysqli, $ctx, $_POST['acciones'], (string)$_SESSION['id_usuario']);
    return formatos_dropouts($mysqli, $ctx);
}
function fd_generate_dropout_word(mysqli $mysqli, array $ctx): void {
    $rows = fd_dropout_data($mysqli, $ctx); [$word,$section] = fd_doc(); fd_header($section, 'IERLJ_DE_05', 'DESERCIÓN ESCOLAR', $ctx);
    fd_title($section, 'DESERCIÓN ESCOLAR', 'Seguimiento de estudiantes retirados durante el año lectivo ' . $ctx['ano']);
    $tableRows=[]; foreach($rows as $i=>$row) $tableRows[] = [$i+1, formatos_full_name($row), $row['grado'] ?: '—', fd_date($row['fecha_retiro']), $row['acciones'] ?: ''];
    fd_table($section, ['N°','Estudiante','Grado','Fecha deserción','Acciones realizadas'], $tableRows ?: [['','Sin registros','','','']], [500,2500,900,1600,3300]);
    $section->addTextBreak(2); $section->addText('Elaboró: _______________________________     Revisó: _______________________________', ['size'=>9]);
    fd_download_word($word, 'Desercion_Escolar_' . $ctx['ano']);
}
function fd_generate_dropout_xlsx(mysqli $mysqli, array $ctx): void {
    $rows = fd_dropout_data($mysqli, $ctx);
    $source = __DIR__ . '/5 IERLJ_DE_05 DESERCIÓN ESCOLAR V1.0.xlsx';
    if (!is_file($source)) throw new RuntimeException('No está disponible el formato Excel original de deserción.');
    $book = \PhpOffice\PhpSpreadsheet\IOFactory::load($source);
    $sheet = $book->getSheetByName('Hoja1') ?: $book->getActiveSheet();
    $sheet->setCellValue('B8', 'Andres Paz');
    $sheet->setCellValue('G8', fd_date(date('Y-m-d')));
    $start = 11; $capacity = 26;
    if (count($rows) > $capacity) $sheet->insertNewRowBefore($start + $capacity, count($rows) - $capacity);
    $last = max($start + $capacity - 1, $start + count($rows) - 1);
    for ($row = $start; $row <= $last; $row++) foreach (['A','B','C','D','E','F','G'] as $column) $sheet->setCellValue($column . $row, '');
    foreach ($rows as $index=>$row) {
        $excelRow = $start + $index;
        $sheet->setCellValue('A' . $excelRow, $index + 1);
        $sheet->setCellValue('B' . $excelRow, formatos_full_name($row));
        $sheet->setCellValue('D' . $excelRow, $row['grado'] ?: '');
        $sheet->setCellValue('E' . $excelRow, fd_date($row['fecha_retiro']));
        $sheet->setCellValue('F' . $excelRow, $row['acciones'] ?: '');
        $sheet->getStyle('F' . $excelRow)->getAlignment()->setWrapText(true)->setVertical(Alignment::VERTICAL_TOP);
    }
    while(ob_get_level()) ob_end_clean(); header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'); header('Content-Disposition: attachment; filename="Desercion_Escolar_'.$ctx['ano'].'.xlsx"'); header('Cache-Control: max-age=0'); (new Xlsx($book))->save('php://output'); exit;
}
function fd_generate_recovery(array $ctx): void {
    $selected = $_POST['seleccion'] ?? []; $registry=$_POST['registro'] ?? []; $rows=[];
    foreach ($selected as $key) { if (!isset($registry[$key])) continue; $r=$registry[$key]; $rows[] = [$r['estudiante'] ?? '', $r['grado'] ?? '', $r['grupo'] ?? '', $r['jornada'] ?? fd_value('jornada'), $r['materia'] ?? '', $r['competencia'] ?? '']; }
    if (!$rows) throw new RuntimeException('Selecciona al menos un estudiante con materia a recuperar.');
    [$word,$section]=fd_doc(); fd_header($section,'IERLJ_RR_11','PLANILLA DE REGISTRO REFUERZO Y RECUPERACIONES',$ctx);
    fd_title($section,'PLANILLA DE REFUERZO Y RECUPERACIONES',fd_value('modalidad','Refuerzo') . ' · Fecha: ' . fd_date(fd_value('fecha')));
    fd_table($section,['Apellidos y nombres del estudiante','Grado','Grupo','Jornada','Área(s) a recuperar','Logros, DBA o competencia'],$rows,[2300,700,700,900,1700,2500]);
    $section->addTextBreak(); $section->addText('Fecha de entrega en secretaría: ____________________     Firma del educador: ______________________________', ['size'=>9]);
    fd_download_word($word,'Planilla_' . fd_value('modalidad','Refuerzo') . '_' . $ctx['ano']);
}

try {
    switch (fd_value('tipo')) {
        case 'observador': fd_generate_original_observer($mysqli, $ctx); break;
        case 'portada_observador': fd_generate_original_cover($mysqli, $ctx); break;
        case 'contraportada_observador': fd_generate_original_back_cover($mysqli, $ctx); break;
        case 'informe_academico': fd_generate_original_academic_report($mysqli, $ctx); break;
        case 'comision': fd_generate_original_commission($ctx); break;
        case 'permiso': fd_generate_original_permission($mysqli, $ctx); break;
        case 'desercion': if (fd_value('salida','docx') === 'xlsx') fd_generate_dropout_xlsx($mysqli, $ctx); else fd_generate_original_dropout($mysqli, $ctx); break;
        case 'refuerzo': fd_generate_original_recovery($ctx); break;
        default: throw new RuntimeException('Formato no reconocido.');
    }
} catch (Throwable $e) {
    http_response_code(400); echo '<!doctype html><meta charset="utf-8"><title>No se pudo generar el formato</title><main style="font-family:Arial;max-width:620px;margin:70px auto;padding:30px;border:1px solid #e2e8f0;border-radius:16px"><h1 style="color:#172554">No se pudo generar el formato</h1><p>' . formatos_e($e->getMessage()) . '</p><p><a href="index.php">Volver al módulo</a></p></main>';
}
