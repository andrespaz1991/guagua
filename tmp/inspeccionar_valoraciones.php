<?php
require_once dirname(__DIR__) . '/asistencia/vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\IOFactory;
$base = 'G:/Mi unidad/PC_HANDRES/SEDUCA/La Josefina/Vallesol/2026/Valoraciones';
foreach (['6-8','9-11'] as $group) {
    foreach (glob($base . '/' . $group . '/*.xlsx') as $path) {
        if (str_starts_with(basename($path), '~$')) continue;
        $reader = IOFactory::createReader('Xlsx');
        $reader->setReadDataOnly(true);
        $book = $reader->load($path);
        echo json_encode(['file'=>basename($path),'sheets'=>$book->getSheetNames()], JSON_UNESCAPED_UNICODE) . "\n";
        foreach ($book->getWorksheetIterator() as $sheet) {
            if (strtolower($sheet->getTitle()) !== 'valoraciones') continue;
            $rows = $sheet->rangeToArray('A1:AA35', null, false, false, true);
            $out = [];
            foreach ($rows as $i=>$row) {
                $filled = array_filter($row, static fn($v)=>$v!==null && $v!=='');
                if ($filled) {
                    $side = array_filter(array_intersect_key($row, array_flip(['V','W','X','Y'])), static fn($v)=>$v!==null && $v!=='');
                    if ($side) $out[$i]=$side;
                }
            }
            echo json_encode(['sheet'=>$sheet->getTitle(),'rows'=>$out,'dimension'=>$sheet->getHighestDataColumn().$sheet->getHighestDataRow()], JSON_UNESCAPED_UNICODE) . "\n";
        }
        $book->disconnectWorksheets();
    }
}
