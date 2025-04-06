function contarinput(numero) {
     //  var fetch_assoc = ['dato1', 'dato2', 'dato3', 'dato4']; //array que paso de PHP a JS

     var inputElms = document.getElementsByClassName(numero); 

for(var i in inputElms) {
          //pongo los valores de fetch_array en input type=text, donde se corresponden los índices.
      //    (inputElms)[i].value = fetch_assoc[i];
        var t = inputElms.length  ;
     return parseInt(t) ;
     }

}

function promediar(valor){
           var n =  parseInt(contarinput(valor));
           //var n = 4;
           //alert (contarinput(valor));
           var x = document.getElementsByClassName(valor);
           var final = document.getElementById("final_"+valor);
           final.innerHTML="";
           var arreglo = new Array();
           for (i = 0; i < n; i++){
           if (x[i].value=="") arreglo[i]=0;
           else arreglo[i] = parseFloat(x[i].value);
           }
           //alert(arreglo);
           try{
       final.innerHTML=promedio(arreglo);
           } catch(e){
       final.value=promedio(arreglo);
           }
}//fin promediar2
    function soloNumeros(e){
	var key = window.Event ? e.which : e.keyCode
	//alert (key);
	return ((key >= 48 && key <= 57) || (key >= 96 && key <= 105) || (key==190)  || (key==110) || (key==8)  || (key==9) || (key==38) || (key==40) || (key==46));
}
    function promedio(array){
    var promedio = 0;
       for (var nota in array){
        //alert(array[nota]);
        promedio = promedio + array[nota];
       }
       promedio = promedio/array.length
    return promedio;
    }