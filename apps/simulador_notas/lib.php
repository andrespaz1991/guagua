<?php
declare(strict_types=1);

function sn_number($value): ?float {
    if ($value === null || trim((string)$value) === '') return null;
    $text = str_replace(',', '.', trim((string)$value));
    if (!is_numeric($text) || (float)$text < 1 || (float)$text > 5) throw new RuntimeException('Hay una nota fuera de la escala de 1,0 a 5,0.');
    return (float)$text;
}
function sn_normalize($text): string {
    return strtolower(preg_replace('/[^a-zA-Z0-9]/', '', strtr(trim((string)$text), ['á'=>'a','é'=>'e','í'=>'i','ó'=>'o','ú'=>'u','ñ'=>'n','Á'=>'A','É'=>'E','Í'=>'I','Ó'=>'O','Ú'=>'U','Ñ'=>'N'])));
}
function sn_record(array $r): array {
    $r['id'] = hash('sha256', implode('|', [$r['year'], $r['group'], $r['code'], $r['subject']]));
    return $r;
}
function sn_load_data_for_user(string $userId): array {
    $initial = is_file(__DIR__.'/data/inicial.php') ? require __DIR__.'/data/inicial.php' : ['source'=>'Sin reporte','records'=>[]];
    // El PDF vigente es la base compartida, incluso si había un Excel personal importado.
    if (($initial['source_type'] ?? '') === 'pdf') return $initial;
    $path = __DIR__.'/data/'.hash('sha256',$userId).'.php';
    return is_file($path) ? require $path : $initial;
}
/** La identificación se usa en el servidor; no hace falta enviarla al navegador. */
function sn_public_data(array $data): array {
    foreach ($data['records'] as &$record) unset($record['document']);
    unset($record);
    return $data;
}
function sn_parse_rows(array $rows): array {
    $records=[]; $group=''; $year=''; $subject=''; $teacher=''; $map=null;
    foreach ($rows as $i=>$row) {
        // SISGA: celdas combinadas; las notas empiezan en W, Y, AB y AD.
        if (trim((string)($row[3]??''))==='Docente') $teacher=trim((string)($row[18]??''));
        if (in_array(trim((string)($row[3]??'')), ['Seccion','Sección'], true)) {
            $group=trim((string)($row[6]??''));
            if (!preg_match('/\b(20\d{2})\b/', $group, $m)) throw new RuntimeException('La sección no indica el año del reporte.');
            $year=$m[1];
        }
        if (trim((string)($row[0]??''))==='Asignatura') $subject=trim((string)($row[18]??''));
        $candidate=[];
        foreach($row as $col=>$value) $candidate[sn_normalize($value)]=$col;
        if(isset($candidate['estudiante'],$candidate['materia'],$candidate['p1'],$candidate['p2'],$candidate['p3'],$candidate['ano'],$candidate['grupo'],$candidate['codigo'])) { $map=$candidate; continue; }
        try {
            if ($map) {
                $get=static fn($key)=>trim((string)($row[$map[$key]]??''));
                if($get('estudiante')==='' || $get('materia')==='') continue;
                if(!preg_match('/^20\d{2}$/',$get('ano'))) throw new RuntimeException('Año inválido.');
                $r=['year'=>$get('ano'),'group'=>$get('grupo'),'code'=>$get('codigo'),'student'=>$get('estudiante'),'subject'=>$get('materia'),'teacher'=>'','p1'=>sn_number($get('p1')),'p2'=>sn_number($get('p2')),'p3'=>sn_number($get('p3')),'p4'=>isset($map['p4'])?sn_number($get('p4')):null];
                if(isset($map['documento'])) $r['document']=$get('documento');
            } elseif($subject!=='' && $year!=='' && ctype_digit(trim((string)($row[0]??''))) && trim((string)($row[14]??''))!=='') {
                $r=['year'=>$year,'group'=>$group,'code'=>trim((string)($row[2]??'')),'student'=>trim((string)$row[14]),'subject'=>$subject,'teacher'=>$teacher,'p1'=>sn_number($row[22]??null),'p2'=>sn_number($row[24]??null),'p3'=>sn_number($row[27]??null),'p4'=>sn_number($row[29]??null)];
                $r['document']=trim((string)($row[11]??''));
            } else continue;
            if($r['code']==='' || $r['group']==='') throw new RuntimeException('Falta código o grupo.');
            $r=sn_record($r);
            if(isset($records[$r['id']]) && $records[$r['id']]!==$r) throw new RuntimeException('Hay notas contradictorias para el mismo estudiante y materia.');
            $records[$r['id']]=$r;
        } catch (RuntimeException $e) { throw new RuntimeException('Fila '.($i+1).': '.$e->getMessage()); }
    }
    return array_values($records);
}
function sn_import(string $file, string $name): array {
    require_once dirname(__DIR__,2).'/asistencia/vendor/autoload.php';
    $type=\PhpOffice\PhpSpreadsheet\IOFactory::identify($file);
    if(!in_array($type,['Xls','Xlsx'],true)) throw new RuntimeException('El archivo debe ser un libro Excel XLS o XLSX.');
    $reader=\PhpOffice\PhpSpreadsheet\IOFactory::createReader($type);
    $reader->setReadDataOnly(true);
    $book=$reader->load($file); $records=[];
    try {
        foreach($book->getWorksheetIterator() as $sheet) {
            if($sheet->getHighestRow()>20000 || \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($sheet->getHighestColumn())>100) throw new RuntimeException('El libro excede el límite de 20.000 filas o 100 columnas por hoja.');
            foreach(sn_parse_rows($sheet->toArray(null,true,false,false)) as $r) {
                if(isset($records[$r['id']]) && $records[$r['id']]!==$r) throw new RuntimeException('El libro contiene registros contradictorios.');
                $records[$r['id']]=$r;
            }
        }
    } finally { $book->disconnectWorksheets(); }
    if(!$records) throw new RuntimeException('No se reconocieron notas. Usa el reporte Resumen Evaluación Asignatura de SISGA o las columnas Año, Grupo, Código, Estudiante, Materia, P1, P2, P3 y P4 (opcional).');
    return ['source'=>basename($name),'imported_at'=>date('c'),'records'=>array_values($records)];
}
function sn_save(string $path,array $data): void {
    $tmp=tempnam(dirname($path),'sn_');
    if($tmp===false) throw new RuntimeException('No se pudo preparar la importación.');
    try {
        if(file_put_contents($tmp,'<?php return '.var_export($data,true).';',LOCK_EX)===false || !rename($tmp,$path)) throw new RuntimeException('No se pudo guardar la importación.');
    } finally { if(is_file($tmp)) unlink($tmp); }
}
