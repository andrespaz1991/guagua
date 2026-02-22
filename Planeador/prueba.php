<?php
// Cargar el autoloader de Composer para las dependencias

// Importar la clase TemplateProcessor de PHPWord
use PhpOffice\PhpWord\TemplateProcessor;

// Cargar el archivo de plantilla .docx
$template = new TemplateProcessor('../plantilla2026/PLANEADOR2026.docx');

// Obtener los datos enviados por el formulario mediante el método POST
// Se utiliza el operador null coalescing (??) para evitar errores si el campo está vacío
$docente = 'Andres Paz' ?? '';
$grado = '6' ?? '';

// Reemplazar las etiquetas en el documento Word con los valores obtenidos
$template->setValue('docente', $nombre);
$template->setValue('grado', $grado);

// Definir el nombre del archivo de salida
$nombreArchivo = 'Registro - ' . $nombre . '.docx';

// Configuración de las cabeceras HTTP para forzar la descarga del archivo
header("Content-Description: File Transfer");
header('Content-Disposition: attachment; filename="' . $nombreArchivo . '"');
header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
header('Cache-Control: must-revalidate');
header('Pragma: public');

// Guardar el documento directamente en el flujo de salida del navegador
$template->saveAs("php://output");

// Finalizar la ejecución del script
exit;