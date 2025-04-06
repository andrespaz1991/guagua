<!DOCTYPE html>
<html lang="es">
    <meta charset="UTF-8">
    <meta name="copyright" content="© 2016">
    <meta name="description" content="Aplicación web para realizar Evaluaci&oacute;n Docente">
    <meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
	<title>Guagua</title>
	<link rel="stylesheet" href="php/css/jquery.mobile-1.4.5.min.css">
	<link rel="stylesheet" href="php/css/bootstrap.min.css">
	<link rel="stylesheet" href="php/css/style.css">
	<link rel="stylesheet" href="php/css/estilo_tabla1.css">
	<script src="php/js/funciones.js"></script>
	<script src="php/js/ajax.js"></script>

	<script src="php/js/jquery-2.2.4.min.js"></script>
	<script src="php/js/bootstrap.min.js"></script>
	<script>
	$(document).ready(function(){
	    $('[data-toggle="tooltip"]').tooltip(); 
	});
	</script>
</head>
<body>
    <div class="container">
         <div class="page-header">
            <?php #require("header.php"); ?>
            <?php require("menu.php"); ?>
           	<main>
           	    <section>
                <?php if (isset($contenido)) echo $contenido; ?>
                </section>
            </main>
        </div>
    </div>
     <?php require("php/footer.php"); ?>
</body>
</html>