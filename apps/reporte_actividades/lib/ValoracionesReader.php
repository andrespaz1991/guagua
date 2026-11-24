<?php
declare(strict_types=1);

/** Lectura de la plantilla institucional. Nunca calcula ni guarda los Excel. */
final class ValoracionesReader
{
    public const DEFAULT_ROOT = 'G:/Mi unidad/PC_HANDRES/SEDUCA/La Josefina/Vallesol/2026/Valoraciones';

    public static function normalize(string $value): string
    {
        $value = strtr(trim($value), ['á'=>'a','é'=>'e','í'=>'i','ó'=>'o','ú'=>'u','ü'=>'u','ñ'=>'n','Á'=>'a','É'=>'e','Í'=>'i','Ó'=>'o','Ú'=>'u','Ü'=>'u','Ñ'=>'n']);
        return preg_replace('/[^a-z0-9]+/', '', strtolower($value)) ?? '';
    }

    public static function grade($value): array
    {
        if ($value === null || trim((string)$value) === '') return ['score'=>null,'status'=>'missing','raw'=>''];
        $raw = trim((string)$value);
        $numeric = str_replace(',', '.', $raw);
        if (!is_numeric($numeric) || (float)$numeric < 1 || (float)$numeric > 5) {
            return ['score'=>null,'status'=>'invalid','raw'=>$raw];
        }
        $score = (float)$numeric;
        return ['score'=>$score,'status'=>$score === 1.0 ? 'not_achieved' : 'graded','raw'=>$raw];
    }

    /** $rows usa números de fila y letras de columna, como Excel. */
    public function parseGrid(array $rows, string $filename, string $group, string $sheet): array
    {
        $warnings = [];
        $header = null;
        $map = [];
        foreach ($rows as $r=>$row) {
            $candidate = [];
            foreach ($row as $c=>$v) {
                $key = self::normalize((string)$v);
                if (in_array($key, ['nombre','documento','grado','talleres','examenes','valoracion'], true)) $candidate[$key]=$c;
            }
            if (isset($candidate['nombre'],$candidate['documento'],$candidate['grado'],$candidate['talleres'])) {
                $header=(int)$r; $map=$candidate; break;
            }
        }
        if ($header === null) throw new RuntimeException('No se encontró la matriz de estudiantes y talleres.');
        $columns = array_keys($rows[$header]);
        $workshopStart = array_search($map['talleres'], $columns, true);
        $examStart = isset($map['examenes']) ? array_search($map['examenes'], $columns, true) : count($columns);
        $activityColumns = [];
        foreach ($rows[$header+1] ?? [] as $c=>$v) {
            if (!is_numeric($v) || (int)$v < 1 || (float)$v !== (float)(int)$v) continue;
            $index = array_search($c, $columns, true);
            if ($index >= $workshopStart && $index < $examStart) $activityColumns['T'.(int)$v]=$c;
            elseif ($index >= $examStart && $index < $examStart+2) $activityColumns['E'.(int)$v]=$c;
        }
        $headerSubject = trim((string)($rows[$header-1][$map['documento']] ?? ''));
        $fileSubject = trim(preg_replace('/^(?:Oficial\s+)?Valoraciones\s*/iu', '', pathinfo($filename, PATHINFO_FILENAME)));
        $fileSubject = trim(preg_replace('/\s*(?:6\s*-\s*8|9\s*-\s*11)\s*$/u', '', $fileSubject));
        $subjectAliases = ['matematicas'=>'Matemáticas','geometria'=>'Geometría','fisica'=>'Física','economia'=>'Economía','edfisica'=>'Educación Física','educacionfisica'=>'Educación Física','sociales'=>'Sociales','tecnologia'=>'Tecnología','emprendimiento'=>'Emprendimiento','urbanidad'=>'Urbanidad'];
        $subject = $subjectAliases[self::normalize($fileSubject)] ?? ($fileSubject ?: $headerSubject);
        $normalizedHeader = $subjectAliases[self::normalize($headerSubject)] ?? $headerSubject;
        if ($headerSubject !== '' && self::normalize($normalizedHeader) !== self::normalize($subject)) {
            $warnings[]='La materia del encabezado ('.$headerSubject.') no coincide con el nombre del archivo. Se usa '.$subject.'.';
        }
        $subjectId = self::normalize($subject);
        $period = '';
        foreach ($rows[$header-1] ?? [] as $c=>$v) {
            if (self::normalize((string)$v) === 'periodo') {
                $index = array_search($c, $columns, true);
                $period = trim((string)($rows[$header-1][$columns[$index-1] ?? ''] ?? ''));
            }
        }
        $sourceId = substr(hash('sha256', $group.'|'.$subjectId.'|'.$period.'|'.$sheet),0,16);
        $students = [];
        foreach ($rows as $r=>$row) {
            if ($r <= $header+1) continue;
            $name=trim((string)($row[$map['nombre']] ?? ''));
            $degree=trim((string)($row[$map['grado']] ?? ''));
            if ($name === '' || !in_array($degree, explode('-', $group) === ['6','8'] ? ['6','7','8'] : ['9','10','11'], true)) continue;
            $id = self::normalize($name).'|'.$degree.'|'.$group;
            if (isset($students[$id])) {
                $warnings[]="Nombre repetido en grado $degree, fila $r. Se conservan ambas filas sin unirlas.";
                $id .= '|fila'.$r;
            }
            $students[$id]=['id'=>$id,'name'=>$name,'document'=>trim((string)($row[$map['documento']] ?? '')),'grade'=>$degree,'group'=>$group,'row'=>(int)$r];
        }
        if (!$students) throw new RuntimeException('No hay estudiantes reconocibles en la matriz.');

        $activities = [];
        $lastMatrixIndex = isset($map['valoracion']) ? array_search($map['valoracion'],$columns,true) : $examStart+7;
        // Cada columna de listados tiene su propio encabezado y alcance por grado.
        foreach ($columns as $ci=>$col) {
            if ($ci <= $lastMatrixIndex) continue;
            $scope = []; $inList=false;
            foreach ($rows as $r=>$row) {
                $value=trim((string)($row[$col] ?? ''));
                if (preg_match('/^Actividades\b(.*)$/iu',$value,$match)) {
                    preg_match_all('/\d+/', $match[1], $grades);
                    $scope=array_map('strval',$grades[0]); $inList=true; continue;
                }
                if (!$inList || $value === '' || $value === '#') continue;
                $label=trim((string)($row[$columns[$ci+1] ?? ''] ?? ''));
                if ($label === '') continue;
                $key = preg_match('/^E\s*(\d+)$/i',$value,$match) ? 'E'.(int)$match[1] : (ctype_digit($value) ? 'T'.(int)$value : '');
                if ($key === '') continue;
                if (!isset($activityColumns[$key])) { $warnings[]="Actividad $value ($label): no se encontró su columna de notas."; continue; }
                $scopeKey = $scope ? implode('-', $scope) : 'todos';
                $id=$sourceId.'|'.$scopeKey.'|'.$key;
                if (isset($activities[$id])) { $warnings[]="Actividad $value repetida para $scopeKey; se conservó la primera."; continue; }
                $dateValue=$row[$columns[$ci+2] ?? ''] ?? null;
                $date='';
                if (is_numeric($dateValue) && (float)$dateValue > 20000) $date=gmdate('Y-m-d', (int)round(((float)$dateValue-25569)*86400));
                elseif ($dateValue !== null) $date=trim((string)$dateValue);
                $activities[$id]=['id'=>$id,'sourceId'=>$sourceId,'key'=>$key,'number'=>substr($key,1),'type'=>str_starts_with($key,'E')?'Examen':'Taller','name'=>$label,'grades'=>$scope,'column'=>$activityColumns[$key],'listCell'=>$col.$r,'date'=>$date];
            }
        }
        $records=[];
        foreach ($students as $student) {
            $namedColumns=[];
            foreach ($activities as $activity) {
                if ($activity['grades'] && !in_array($student['grade'],$activity['grades'],true)) continue;
                $namedColumns[$activity['column']]=true;
                $cell=$activity['column'].$student['row'];
                $grade=self::grade($rows[$student['row']][$activity['column']] ?? null);
                $records[]=array_merge($grade,['studentId'=>$student['id'],'activityId'=>$activity['id'],'sourceId'=>$sourceId,'cell'=>$cell]);
                if ($grade['status']==='invalid') $warnings[]="Celda $cell: valor de nota no válido ({$grade['raw']}).";
            }
            $unnamed=[];
            foreach ($activityColumns as $key=>$column) {
                if (!isset($namedColumns[$column]) && trim((string)($rows[$student['row']][$column] ?? ''))!=='') $unnamed[]=$column.$student['row'];
            }
            if ($unnamed) $warnings[]='Notas sin actividad nombrada para grado '.$student['grade'].': '.implode(', ',$unnamed).'. No se incluyen en los indicadores.';
        }
        if (!$activities) $warnings[]='No hay actividades con nombre en el listado de la derecha.';
        return ['id'=>$sourceId,'file'=>$filename,'group'=>$group,'sheet'=>$sheet,'subject'=>$subject,'subjectKey'=>$subjectId,'period'=>$period,'students'=>array_values($students),'activities'=>array_values($activities),'records'=>$records,'warnings'=>array_values(array_unique($warnings))];
    }

    public function readFile(string $path, string $group): array
    {
        require_once dirname(__DIR__,3).'/asistencia/vendor/autoload.php';
        $reader=\PhpOffice\PhpSpreadsheet\IOFactory::createReader('Xlsx');
        $reader->setReadDataOnly(true);
        $sheetName=null;
        foreach ($reader->listWorksheetNames($path) as $name) if (self::normalize($name)==='valoraciones') { $sheetName=$name; break; }
        if ($sheetName===null) throw new RuntimeException('No se encontró la hoja valoraciones.');
        $reader->setLoadSheetsOnly([$sheetName]);
        $book=$reader->load($path);
        try {
            $sheet=$book->getSheetByName($sheetName);
            if ($sheet->getHighestDataRow()>5000) throw new RuntimeException('La hoja excede las 5000 filas admitidas.');
            $rows=$sheet->rangeToArray('A1:AZ'.$sheet->getHighestDataRow(),null,false,false,true);
            // Leer el valor guardado de fórmulas, sin actualizar vínculos externos.
            foreach ($rows as $r=>&$row) foreach ($row as $col=>&$value) {
                if (is_string($value) && str_starts_with($value,'=')) {
                    $cached=$sheet->getCell($col.$r)->getOldCalculatedValue();
                    $value=$cached===null ? '[Fórmula sin valor guardado]' : $cached;
                }
            }
            unset($row,$value);
            $result=$this->parseGrid($rows,basename($path),$group,$sheetName);
            $result['modified']=date(DATE_ATOM,(int)filemtime($path));
            return $result;
        } finally { $book->disconnectWorksheets(); }
    }

    public function readDirectory(string $root): array
    {
        $root=rtrim($root,'/\\');
        if (!is_dir($root) || !is_readable($root)) throw new RuntimeException('No se puede acceder a la carpeta de valoraciones. Comprueba que Google Drive esté conectado y la unidad G: esté disponible para Apache.');
        $sources=[]; $warnings=[];
        foreach (['6-8','9-11'] as $group) {
            if (!is_dir($root.'/'.$group)) { $warnings[]="No está disponible la carpeta $group."; continue; }
            $files=glob($root.'/'.$group.'/*.xlsx') ?: [];
            // Preferir el archivo oficial; después, nombres sin prefijos de copia.
            usort($files, static function($a,$b) {
                $priority=static fn($p)=>preg_match('/^Oficial\b/i',basename($p)) ? 0 : 1;
                return ($priority($a)<=>$priority($b)) ?: strnatcasecmp($a,$b);
            });
            $seen=[];
            foreach ($files as $file) {
                if (str_starts_with(basename($file),'~$')) continue;
                if (preg_match('/^(?:Copia|Copy)\b/iu',basename($file))) { $warnings[]='Copia excluida: '.$group.'/'.basename($file); continue; }
                try {
                    $source=$this->readFile($file,$group);
                    $key=$source['subjectKey'].'|'.$source['period'];
                    if (isset($seen[$key])) { $warnings[]='Duplicado excluido: '.$group.'/'.basename($file).'. Se usa '.$seen[$key].'.'; continue; }
                    $seen[$key]=basename($file); $sources[]=$source;
                } catch (Throwable $e) { $warnings[]=$group.'/'.basename($file).': '.$e->getMessage(); }
            }
        }
        if (!$sources) throw new RuntimeException('No se pudo leer ninguna valoración. Revisa las carpetas 6-8 y 9-11 y los archivos .xlsx. '.implode(' ',array_slice($warnings,0,3)));
        // La identidad usa nombre normalizado y grado. Los documentos inconsistentes
        // se avisan; jamás se corrigen ni se unen dos nombres por documento.
        $docs=[]; $identities=[];
        foreach ($sources as $source) foreach ($source['students'] as $student) {
            if ($student['document']==='') continue;
            $docs[$student['document']][$student['id']]=$student['name'];
            $identities[$student['id']][$student['document']]=true;
        }
        foreach ($docs as $document=>$names) if (count($names)>1) $warnings[]='Documento '.$document.' asociado a nombres distintos: '.implode(' / ',$names).'. Se mantienen separados.';
        foreach ($identities as $id=>$documents) if (count($documents)>1) $warnings[]='Documentos distintos entre materias para '.explode('|',$id)[0].'. Revisa los archivos de origen.';
        return ['sources'=>$sources,'warnings'=>$warnings,'readAt'=>date(DATE_ATOM),'folderLabel'=>'Valoraciones / 6-8 y 9-11','schemaVersion'=>1];
    }
}
