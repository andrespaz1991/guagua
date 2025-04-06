<!DOCTYPE html>
<html lang="es">
<body>
    <div class="container"><!-- Creamos un contenedor identificable por Bootstrap -->
         <div class="page-header"> <!--Creamos un contenedor identificado como cabecera -->
            <?php include("header.html"); ?> <!--ncluimos el archivo y contenido de la cabecera-->
            <?php include("menu.html"); ?><!--ncluimos el archivo y contenido del Mnú-->
            </BR><!-- Salto de linea-->
           	<main><!-- Creamos una etiqueta de contendo-->
           	    <section><!-- Creamos una sección para presentar nuestro contendo-->
                <?php if (isset($contenido)) echo $contenido; ?><!--Presenta quí la información -->
                </section><!-- Cerramos la sección-->
            </main><!-- cerramos una etiqueta de contendo-->
        </div><!-- cerramos el contendor-->
    </div><!-- cerramos el contendor-->
     <?php include("footer.html"); ?><!-- incluimos el footer-->
</body><!-- cerramos el cuerpo-->
</html><!-- cerramos el archivo-->