<?php
function datarow_encode($array){
    $mivar = '';
    foreach ($array as $id => $valor){
    $mivar .= "data.addRows([         	
              ['$id',$valor],
               ]);
    ";
    }
    return $mivar;
}
function graficar($totales,$opciones_generales,$id,$tipo,$ntipo){
return '<script type=\'text/javascript\'>
      google.load(\'visualization\', \'1.0\', {\'packages\':[\'corechart\']});
      google.setOnLoadCallback(drawChart);
      function drawChart() {
      	var data = new google.visualization.DataTable();
        data.addColumn(\'string\', \'Topping\');
        data.addColumn(\'number\', \'Slices\');
'.datarow_encode($totales).'
var options = '.$opciones_generales.';
var chart = new google.visualization.'.$tipo[$ntipo].'(document.getElementById(\''.$id.'\'));
        chart.draw(data, options);
      }
    </script>
<div id="'.$id.'"></div>';
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Grafica</title>
<script type="text/javascript" src="../js/japi.js"></script>
</head>
<body>
<?php
$totales = array('A'=>'3','N'=>'4','S'=>'1');
$opciones_generales = "{'title':'Grafica representativa de la pregunta',
                       'width':600,
                       'height':500}";
$id = 'chart_div';
$tipo = array('ColumnChart','PieChart');
$ntipo = 1;
echo graficar($totales,$opciones_generales,$id,$tipo,$ntipo);
?>
</body>
</html>