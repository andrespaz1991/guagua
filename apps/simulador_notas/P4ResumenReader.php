<?php
declare(strict_types=1);

/** Lee los valores guardados en los resúmenes, sin recalcular vínculos ni escribir libros. */
final class P4ResumenReader
{
    public const ROOT = 'G:/Mi unidad/PC_HANDRES/SEDUCA/La Josefina/Vallesol/2026/Valoraciones';
    public const YEAR = '2026';

    public static function normalize($value): string
    {
        return strtolower(preg_replace('/[^a-zA-Z0-9]/', '', strtr(trim((string)$value), [
            'á'=>'a','é'=>'e','í'=>'i','ó'=>'o','ú'=>'u','ü'=>'u','ñ'=>'n',
            'Á'=>'A','É'=>'E','Í'=>'I','Ó'=>'O','Ú'=>'U','Ü'=>'U','Ñ'=>'N'
        ])) ?? '');
    }

    public static function degree(array $row): ?int
    {
        $group = trim((string)($row['group'] ?? ''));
        if (preg_match('/^\d+\s+Completa\s+(\d+)\s+\d+\s+20\d{2}$/i', $group, $m)) return (int)$m[1];
        if (preg_match('/^(?:Grado\s*)?(6|7|8|9|10|11)(?:\s*[-°ºA-Za-z].*)?$/i', $group, $m)) return (int)$m[1];
        return null;
    }

    /** El usuario autorizó compartir Economía/Ciencias Sociales con Políticas en 10.º–11.º. */
    public static function subjectKeys(string $subject, int $degree): array
    {
        $name = self::normalize($subject);
        if ($name === 'economiacienciassociales') return $degree >= 10 ? ['economia','politica'] : ['sociales'];
        $aliases = [
            'cienciassociales'=>'sociales', 'sociales'=>'sociales', 'cienciassocialeshistgeoconstpolydemo'=>'sociales',
            'educacionfisica'=>'edfisica', 'educacionfisicarecreacionydeportes'=>'edfisica', 'edfisica'=>'edfisica',
            'matematicas'=>'matematicas', 'tecnologia'=>'tecnologia', 'tecnologiaeinformatica'=>'tecnologia',
            'urbanidad'=>'urbanidad', 'civicayurbanismo'=>'urbanidad', 'emprendimiento'=>'emprendimiento',
            'cienciaseconomicas'=>'economia', 'economia'=>'economia', 'cienciaspoliticas'=>'politica',
            'fisica'=>'fisica', 'geometria'=>'geometria'
        ];
        return isset($aliases[$name]) ? [$aliases[$name]] : [];
    }

    public function parseRows(array $rows, string $filename, string $sheet, array $degrees): array
    {
        $header = null; $map = []; $subjects = [];
        foreach ($rows as $index=>$row) {
            $candidate = [];
            foreach ($row as $column=>$value) $candidate[self::normalize($value)] = $column;
            if (!isset($candidate['nombre'],$candidate['documento'],$candidate['grado'])) continue;
            $header = $index; $map = $candidate;
            foreach ($row as $column=>$value) {
                if ($column === $map['nombre'] || $column === $map['documento'] || $column === $map['grado'] || trim((string)$value) === '') continue;
                $subjects[$column] = trim((string)$value);
            }
            break;
        }
        if ($header === null || !$subjects) throw new RuntimeException('No se encontraron Nombre, Documento, Grado y las materias.');
        $students = []; $invalid = []; $missing = 0;
        foreach ($rows as $index=>$row) {
            if ($index <= $header) continue;
            $name = trim((string)($row[$map['nombre']] ?? ''));
            $degreeRaw = trim((string)($row[$map['grado']] ?? ''));
            if ($name === '' && $degreeRaw === '') continue;
            if ($name === '' || !ctype_digit($degreeRaw) || !in_array((int)$degreeRaw, $degrees, true)) throw new RuntimeException('Nombre o grado no válido en la fila '.($index+1).'.');
            $degree = (int)$degreeRaw; $values = [];
            foreach ($subjects as $column=>$subject) {
                $keys = self::subjectKeys($subject, $degree);
                if (!$keys) continue;
                $raw = $row[$column] ?? null;
                $text = trim((string)$raw); $numeric = str_replace(',', '.', $text);
                $value = $text !== '' && is_numeric($numeric) && (float)$numeric >= 1 && (float)$numeric <= 5 ? (float)$numeric : null;
                $cell = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($column+1).($index+1);
                $reason = $text === '' ? 'Nota vacía o fórmula sin valor guardado' : ($value === null ? 'La celda no contiene una nota válida de 1,00 a 5,00' : '');
                if ($text === '') $missing++;
                elseif ($value === null) $invalid[] = ['cell'=>$cell, 'subject'=>$subject, 'reason'=>$reason];
                foreach ($keys as $key) {
                    if (isset($values[$key])) throw new RuntimeException('Dos columnas representan la misma materia en la fila '.($index+1).'.');
                    $values[$key] = ['value'=>$value,'cell'=>$cell,'column'=>$subject,'reason'=>$reason];
                }
            }
            $students[] = ['name'=>$name,'document'=>self::normalize($row[$map['documento']] ?? ''),'degree'=>$degree,'values'=>$values,'file'=>$filename,'sheet'=>$sheet,'row'=>$index+1];
        }
        if (!$students) throw new RuntimeException('El resumen no contiene estudiantes de los grados esperados.');
        return ['students'=>$students,'invalid_cells'=>$invalid,'empty_cells'=>$missing];
    }

    public function readFile(string $path, array $degrees): array
    {
        require_once __DIR__.'/../../asistencia/vendor/autoload.php';
        clearstatcache(true, $path);
        if (!is_file($path) || !is_readable($path)) throw new RuntimeException('No se puede leer el archivo. Comprueba que Google Drive y la unidad G: estén disponibles para Apache.');
        if (filesize($path) > 20*1024*1024) throw new RuntimeException('El resumen supera el límite de 20 MB.');
        $before = hash_file('sha256', $path);
        $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader('Xlsx');
        $reader->setReadDataOnly(true);
        $names = $reader->listWorksheetNames($path);
        $sheetName = null;
        foreach ($names as $name) if (str_starts_with(self::normalize($name), 'resumen')) {
            if ($sheetName !== null) throw new RuntimeException('Hay más de una hoja de resumen; no se eligió una automáticamente.');
            $sheetName = $name;
        }
        if ($sheetName === null) throw new RuntimeException('No se encontró una hoja de resumen.');
        $reader->setLoadSheetsOnly([$sheetName]);
        $book = $reader->load($path);
        try {
            $sheet = $book->getSheetByName($sheetName);
            if ($sheet->getHighestDataRow() > 5000 || \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($sheet->getHighestDataColumn()) > 100) throw new RuntimeException('La hoja supera 5.000 filas o 100 columnas.');
            $rows = $sheet->toArray(null, false, false, false);
            foreach ($rows as $r=>&$row) foreach ($row as $c=>&$value) {
                if (is_string($value) && str_starts_with($value, '=')) $value = $sheet->getCell([$c+1,$r+1])->getOldCalculatedValue();
            }
            unset($row, $value);
            $parsed = $this->parseRows($rows, basename($path), $sheetName, $degrees);
            clearstatcache(true, $path);
            if ($before !== hash_file('sha256', $path)) throw new RuntimeException('El archivo cambió durante la lectura. Vuelve a actualizar.');
            return $parsed + ['sheet'=>$sheetName,'modified_at'=>date(DATE_ATOM, (int)filemtime($path)),'fingerprint'=>$before];
        } finally { $book->disconnectWorksheets(); }
    }

    /** Une únicamente identidades exactas y únicas: documento o nombre normalizado + grado. */
    public function match(array $records, array $students): array
    {
        $byName = []; $byDocument = []; $baseIdentities = []; $templates = []; $seen = [];
        foreach ($students as $i=>$student) {
            $byName[$student['degree'].'|'.self::normalize($student['name'])][] = $i;
            if ($student['document'] !== '') $byDocument[$student['degree'].'|'.$student['document']][] = $i;
        }
        foreach ($records as $record) {
            $key = ($record['year'] ?? '').'|'.self::degree($record).'|'.self::normalize($record['student']);
            $baseIdentities[$key][($record['group'] ?? '').'|'.$record['code']] = true;
            $degree = self::degree($record);
            if ((string)$record['year'] === self::YEAR && $degree !== null) {
                $keys = self::subjectKeys($record['subject'], $degree);
                if (count($keys) === 1) $templates[$degree][$keys[0]] = $record['subject'];
            }
        }
        $matches = []; $unmatched = [];
        foreach ($records as $record) {
            $degree = self::degree($record); $reason = ''; $source = null; $cell = null;
            if ((string)$record['year'] !== self::YEAR) $reason = 'Los resúmenes corresponden al año '.self::YEAR;
            elseif ($degree === null) $reason = 'No se pudo identificar el grado del estudiante';
            else {
                $identity = self::YEAR.'|'.$degree.'|'.self::normalize($record['student']);
                $document = self::normalize($record['document'] ?? '');
                $candidates = $document !== '' ? ($byDocument[$degree.'|'.$document] ?? []) : ($byName[$degree.'|'.self::normalize($record['student'])] ?? []);
                // Un candidato ambiguo o con documento distinto no se anuncia como alumno nuevo.
                foreach (array_merge($candidates,$byName[$degree.'|'.self::normalize($record['student'])] ?? []) as $index) $seen[$index] = true;
                if (count($baseIdentities[$identity] ?? []) > 1) $reason = 'Identidad ambigua entre grupos del consolidado';
                elseif (count($candidates) > 1) $reason = 'El estudiante aparece repetido en los resúmenes';
                elseif (!$candidates) $reason = 'Estudiante no encontrado en el resumen de su grado';
                else {
                    $source = $students[$candidates[0]];
                    $keys = self::subjectKeys($record['subject'], $degree);
                    $cell = count($keys) === 1 ? ($source['values'][$keys[0]] ?? null) : null;
                    if ($cell === null) $reason = 'No hay una columna equivalente para esta materia';
                    elseif ($cell['value'] === null) $reason = $cell['reason'];
                }
            }
            if ($reason !== '') {
                $unmatched[] = ['id'=>$record['id'],'student'=>$record['student'],'code'=>$record['code'],'subject'=>$record['subject'],'group'=>$record['group'],'reason'=>$reason,'file'=>$source['file'] ?? '', 'sheet'=>$source['sheet'] ?? '', 'cell'=>$cell['cell'] ?? ''];
                continue;
            }
            $matches[$record['id']] = ['value'=>$cell['value'],'file'=>$source['file'],'sheet'=>$source['sheet'],'cell'=>$cell['cell'],'column'=>$cell['column'],'match_by'=>$document !== '' ? 'documento y grado' : 'nombre y grado'];
        }
        $sourceOnly = [];
        foreach ($students as $index=>$student) {
            if (isset($seen[$index])) continue;
            foreach ($templates[$student['degree']] ?? [] as $key=>$subject) {
                $value = $student['values'][$key] ?? ['value'=>null,'cell'=>'','reason'=>'No hay una columna equivalente para esta materia'];
                $id = hash('sha256','solo-excel|'.self::YEAR.'|'.$student['degree'].'|'.$student['document'].'|'.$student['name'].'|'.$student['file'].'|'.$student['row'].'|'.$key);
                $sourceOnly[] = ['id'=>$id,'year'=>self::YEAR,'group'=>'Grado '.$student['degree'].' (solo Excel)','degree'=>$student['degree'],'code'=>'','student'=>$student['name'],'subject'=>$subject,'p1'=>null,'p2'=>null,'p3'=>null,'p4'=>$value['value'],'source_only'=>true,'p4_source'=>['file'=>$student['file'],'sheet'=>$student['sheet'],'cell'=>$value['cell'],'reason'=>$value['reason']]];
            }
        }
        return ['matches'=>$matches,'unmatched'=>$unmatched,'source_only_records'=>$sourceOnly];
    }

    public function read(array $records, string $root = self::ROOT): array
    {
        $students = []; $sources = []; $warnings = [];
        foreach (['resumen_6-8.xlsx'=>[6,7,8], 'resumen_9-11.xlsx'=>[9,10,11]] as $filename=>$degrees) {
            try {
                $data = $this->readFile(rtrim($root,'/\\').'/'.$filename, $degrees);
                $students = array_merge($students, $data['students']);
                $sources[] = ['file'=>$filename,'status'=>'ready','sheet'=>$data['sheet'],'modified_at'=>$data['modified_at'],'students'=>count($data['students']),'invalid_cells'=>$data['invalid_cells'],'empty_cells'=>$data['empty_cells']];
            } catch (Throwable $error) {
                $message = $error instanceof RuntimeException ? $error->getMessage() : 'No se pudo abrir el libro Excel.';
                $sources[] = ['file'=>$filename,'status'=>'error','message'=>$message];
                $warnings[] = $filename.': '.$message;
            }
        }
        $result = $this->match($records, $students);
        $ready = count(array_filter($sources, static fn($source) => $source['status'] === 'ready'));
        return $result + ['status'=>$ready === 2 ? 'ready' : ($ready ? 'partial' : 'unavailable'),'sources'=>$sources,'warnings'=>$warnings,'checked_at'=>date(DATE_ATOM),'year'=>self::YEAR,'matched_count'=>count($result['matches']),'total_count'=>count($records)];
    }
}
