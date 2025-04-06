<?php
require 'mysql.class.php';
require 'persona.class.php';
$persona=new Persona();
$resultado=$persona->datalist("persona"); 
?>

<div class="col-md-6">        
 <div class="control-group" id="personas">
<label>Contenido</label>
<input autocomplete="off" list="persona" autofocus="" class="form form-control"  class="input" id="contenido1" name="contenido[]" type="text" placeholder="contenido" />
</div>