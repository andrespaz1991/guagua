<?php
require __DIR__ . '/../../comun/database.php';
$s=GuaguaDatabase::settings();
echo json_encode(array_intersect_key($s,array_flip(['host','port','database','environment'])),JSON_PRETTY_PRINT);
