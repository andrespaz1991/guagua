<?php
/**
 * Punto de entrada compatible de la hoja de vida estudiantil.
 * El expediente integral se mantiene en un único módulo para evitar que esta
 * ruta y hoja_vida_estudiante.php muestren información distinta.
 */
if (!isset($_GET['estudiante']) && !isset($_GET['id_estudiante']) && isset($_GET['id'])) {
    $_GET['id_estudiante'] = $_GET['id'];
}

require __DIR__ . '/hoja_vida_estudiante.php';
exit;
