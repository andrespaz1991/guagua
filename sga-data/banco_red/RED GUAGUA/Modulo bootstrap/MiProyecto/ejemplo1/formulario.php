<?php ob_start();?>
  <body>
        <H1>Estudiante</H1></br>
    <div class="container">      
        <div class="row">
          <div class="col-xs-12">          
            
            <form action="">
                <label for="nombre_usuario">Nombre de usuario</label>
                <input type="text" class="form-control" name="nombre_usuario">
                <label for="apellido_usuario">Apellido de usuario</label>
                <input id="text" type="text" class="form-control"></br>
            <button type="submit" class="btn btn-success btn-md">Guardar</button>
           </form>

          </div>
        </div>
      </div>    
  </body>
</html>
<?php $contenido = ob_get_contents(); ob_clean();
include ("plantilla.php");
 ?>