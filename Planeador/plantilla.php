<?php require ("conexion.php"); ?>
<!DOCTYPE html>
<html lang="es">
    <meta charset="UTF-8">
    <meta name="copyright" content="© 2016">
    <meta name="description" content="">
    <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
  <title>Guagua</title>
  <link rel="stylesheet" href="css/jquery.mobile-1.4.5.min.css">
  <link rel="stylesheet" href="css/bootstrap.min.css">
  <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/estilo_tabla.css">
  <link rel="shortcut icon" href="img/logo.png" type="image/x-icon" />
  <script src="js/funciones.js"></script>
  <script src="js/jquery-2.2.4.min.js"></script>
  <script src="js/bootstrap.min.js"></script>
</head>
<body>
    <div class="wrapper container">
            <main>
            <?php require("template/menu.php"); ?>
                <section>
                <?php if (isset($contenido)) echo $contenido; ?>
                </section>
            </main>
    </div>
     <?php #require("footer.php"); ?>
</body>
</html>