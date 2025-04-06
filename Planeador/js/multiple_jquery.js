function adicionar(grupo,info="",actualizar="no"){
       var next = parseInt(  $("#count"+grupo).val());
        var addto = "#"+grupo + next;
        var addRemove = "#"+grupo + (next);
        next = next + 1;
        var newIn = '<input value="'+info+'" autocomplete="off" class="input form-control" id="'+grupo+ next + '" name="'+grupo+'[]" type="text">';
        var newInput = $(newIn);
        var removeBtn = '<button  id="remove'+grupo+ (next-1) + '" class="btn btn-danger" onclick="remover(this.id);" >-</button ></div><div id="'+grupo+'">';
        var removeButton = $(removeBtn);
        $(addto).after(newInput);
        $(addRemove).after(removeButton);
        $("#"+grupo+ next).attr('data-source',$(addto).attr('data-source'));
          $("#count"+grupo).val(next);     
          if(actualizar=="si"){
var input = ["contenido1", "objetivos1", "estrategia1","Recurso1","Actividad1","estrategiaplanb1","Actividadplanb1","Recursosplanb1"];
input.forEach( function(valor, indice, array) {
$("#"+valor).remove();  
});
          }

         }
function remover(parametroesperado){
$("#"+parametroesperado).remove();  
parametroesperado = parametroesperado.replace('remove', '');
$("#"+parametroesperado).remove();  
}
