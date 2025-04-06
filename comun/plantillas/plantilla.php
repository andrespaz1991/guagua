<?php
require($_SERVER['DOCUMENT_ROOT']."/comun/autoload.php");
#require_once(dirname(__FILE__)."/comun/funciones.php");
?>
<!DOCTYPE html>
<html lang="es">
    <meta charset="UTF-8">
    <meta name="" content="">
    <meta name="description" content="Sistema de Gestión de Aprendizaje">
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
	<title>Guagua</title>
   <!--cdn textarea html -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/tinymce/5.7.0/tinymce.min.js" integrity="sha512-XaygRY58e7fVVWydN6jQsLpLMyf7qb4cKZjIi93WbKjT6+kG/x4H5Q73Tff69trL9K0YDPIswzWe6hkcyuOHlw==" crossorigin="anonymous"></script>
    <!--cdn textarea html -->
	<!--link rel="stylesheet" href="<?php echo SGA_COMUN_URL ?>/css/jquery.mobile-1.4.5.min.css"--> <!-- Pendite revisar utilidad sino borrar -->
	<link rel="shortcut icon" href="<?php echo SGA_COMUN_URL ?>/img/logo.png" type="image/x-icon" /><!--Logo de la IEM-->
	<link rel="stylesheet" href="<?php echo SGA_COMUN_URL ?>/css/bootstrap.min.css">
    <link rel="stylesheet" href="<?php echo SGA_COMUN_URL ?>/css/estilossga.css">
	<script src="<?php echo SGA_COMUN_URL ?>/js/jquery-2.2.4.min.js"></script>
	<script src="<?php echo SGA_COMUN_URL ?>/js/bootstrap.min.js"></script>
	<script src="<?php echo SGA_COMUN_URL ?>/js/funciones.js"></script>
    <script src="<?php echo SGA_COMUN_URL ?>/js/push.js-master/bin/push.min.js"></script>
	<script src="<?php echo SGA_COMUN_URL ?>/lib/sweetalert2/dist/sweetalert2.min.js"></script>
	<link rel="stylesheet" href="<?php echo SGA_COMUN_URL ?>/lib/sweetalert2/dist/sweetalert2.css">
</head>
<body>
    <div class="container">
         <div class="page-header">
            <?php
            
             #require(dirname(__FILE__)."/header.php"); ?>
            <?php require(SGA_COMUN_SERVER."/menu.php"); ?>
           	<main>
           	    <section>
                <?php if (isset($contenido)) echo $contenido; ?>
                </section>
            </main>
        </div>
    </div>
     <?php require("footer.php"); ?>
</body>
</html>