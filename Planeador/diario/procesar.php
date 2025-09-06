<?php
require_once 'dompdf/vendor/autoload.php';
use Dompdf\Dompdf;

$texto = $_POST['texto'];
$imagenes = $_FILES['imagenes'];

// Procesar imágenes subidas
$imagenesSubidas = [];
foreach ($imagenes['tmp_name'] as $i => $tmp) {
    $nombre = uniqid() . "_" . basename($imagenes['name'][$i]);
    $destino = "temp/" . $nombre;
    move_uploaded_file($tmp, $destino);
    $imagenesSubidas[] = $destino;
}

// Dividir el texto en bloques por párrafos dobles
$bloques = preg_split("/\n{2,}/", $texto);

// Generar el contenido HTML
$html = '';
foreach ($bloques as $i => $bloque) {
    $html .= '<div style="page-break-after: always; text-align: center;">';

    if (isset($imagenesSubidas[$i])) {
        $imgPath = $imagenesSubidas[$i];
        $imgBase64 = base64_encode(file_get_contents($imgPath));
        $imgType = pathinfo($imgPath, PATHINFO_EXTENSION);
        $html .= '<img src="data:image/' . $imgType . ';base64,' . $imgBase64 . '" style="width:300px;"><br><br>';
    }

    $html .= '<p style="font-family: Arial; font-size: 12pt; text-align: justify;">' . nl2br(htmlspecialchars($bloque)) . '</p>';
    $html .= '</div>';
}



// Inicializar dompdf
$dompdf = new Dompdf();
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

// Descargar
$dompdf->stream("diario_" . date("Ymd_His") . ".pdf", ["Attachment" => false]);
