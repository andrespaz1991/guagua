<?php
declare(strict_types=1);
require_once __DIR__ . '/../../comun/seguridad.php';
guagua_iniciar_sesion_segura();
if (empty($_SESSION['id_usuario'])) {
    header('Location: ../../usuario/login.php');
    exit;
}
guagua_exigir_rol(['admin', 'directivo', 'docente']);
require_once __DIR__ . '/../../comun/cabecera_modulo.php';
header('Cache-Control: private, no-store');
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Mapa de procesos | Guagua</title>
    <?php guagua_menu_estilos(); ?>
    <link rel="stylesheet" href="mapa.css">
    <script src="datos.js" defer></script>
    <script src="registro.js" defer></script>
    <script src="mapa.js" defer></script>
</head>
<body>
<?php guagua_menu_renderizar(); ?>
<?php require __DIR__ . '/vista.html'; ?>
</body>
</html>
