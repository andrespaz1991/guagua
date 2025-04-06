<?php 
@session_start();
ob_start();
echo "<hr>";
require dirname(__FILE__).'/../comun/funciones.php';
$ano_lectivo=consultar_id_ano_lectivo();
$datos_materia = consultar_materia_obligatorias();
generar_cursos_DeLey($ano_lectivo);
inscribir_estudiante($ano_lectivo);
#foreach ($datos_materia as $campo => $materia){
#echo $materia[0]." ".$materia[1]."<br>";
#}
#echo "<pre>";
#print_r($datos_materia);
#echo "</pre>";
if (isset($_POST['campo'])){
    if ($_POST['campo']=="uno"){
        echo "1";
    }elseif ($_POST['campo']=="cero"){
        echo "0";
    }else{
    print_r($_POST);
    print_r($_FILES);
    }
    exit();
}
?>
<form id="form" class="form_ajax" action="ejemplo.php" method="post" resp_1="Foro creado" resp_0="Foro no creado, revise su información e intentete nuevo" callback_1="alert3('SI')" callback_0="alert3('NO')" callback="alert3('T')">
    <input type="text" placeholder="Pon algo..." name="campo" /><br />
    <input type="text" placeholder="También aquí" name="otro" /><br />
    <input type="file" name="files" multiple /><br />
    <input type="submit" value="Enviar formulario" />
</form>
<section class="section"></section>
<script>
    $(document).ready(function() {
    $('.form_ajax').submit(function(event) {
        event.stopPropagation();
        event.preventDefault();
        var resp_1 = $(this).attr('resp_1');
        var resp_0 = $(this).attr('resp_0');
        var callback_1 = $(this).attr('callback_1');
        var callback_0 = $(this).attr('callback_0');
        var callback = $(this).attr('callback');
        
        var type = "post";
        var url = $(this).prop("action");
        /* Cuando mandamos sólo texto (sin archivos)
             configuramos estos valores */
        var contentType = 'application/x-www-form-urlencoded';
        var processData = true;

        /* Para que PHP reciba todos los archivos hay que definir
             el <input> con corchetes, de modo que aquí tenemos que
             indicarlo igual */
        if (this['files'].files.length) {
            var data = new FormData(this);
            /* En este caso sí que hay que cambiar estos parámetros,
                 en particular contentType=false provoca una cabecera HTTP
                 'multipart/form-data; boundary=----...' */
            contentType = false;
            processData = false;
        } else {
            var data = $(this).serialize();
        }
        $.ajax({
            url: url,
            data: data,
            type: type,
            contentType: contentType,
            processData: processData
        }).done(function(data) {
            if (data=="1"){
            alert3(resp_1);
            eval(callback_1);
            }else if (data=="0"){
            alert3(resp_0,'danger');
            eval(callback_0);
            }else{
             alert3(data,'warning');   
            }
            setTimeout(function(){
                eval(callback);
            },2000);
        });
    });
});
</script>

<?php $contenido = ob_get_contents();
ob_clean();
require("../comun/plantilla.php");
 ?>
