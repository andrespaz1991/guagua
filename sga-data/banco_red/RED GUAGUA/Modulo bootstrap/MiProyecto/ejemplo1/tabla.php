<?php ob_start();?>
  <body>
    <h1>Listado de estudiantes</h1>
<div class="container">
  <div class="row">
  <div class="table-responsive"> 
     <table class="table table-bordered table-striped  table-hover table-condensed">
       <thead>  
          <tr class="success">
            <th>Identificación</th>
            <th>Nombre</th>
            <th>Apellido</th>
        <th colspan="2" ><button  type="button" class="btn btn-primary btn-md">Nuevo</button></th>
          </tr>
      </thead>
      <tbody>
        <tr>
          <td>108433</td>
          <td>Juan</td>
          <td>Santos</td>
          <td><button type="button" class="btn btn-success btn-md">Modificar</button></td>
          <td><button type="button" class="btn btn-danger btn-md">Eliminar</button></td>
        </tr>
         <tr>
          <td>108434</td>
          <td>Alvaro</td>
          <td>Uribe</td>
          <td><button type="button" class="btn btn-success btn-md">Modificar</button></td>
          <td><button type="button" class="btn btn-danger btn-md">Eliminar</button></td>
        </tr>
      </tbody>
    </table>      
  </div>
  </div>
</div>
 <a href="#" class="btn btn-primary btn-lg">
    <span class="glyphicon glyphicon-print"></span> Imprimir 
  </a>
  </body>
</html>
<?php $contenido = ob_get_contents(); ob_clean();
include ("plantilla.php");
 ?>