<?php
$atajo="CTRL + C";
$descripcion="Copia contenido de la celda";
$html="";

$mysqli = new mysqli ('localhost','root','','guagua');
$sql=' select * from atajos ';
$consulta=$mysqli->query($sql);
while($array=$consulta->fetch_assoc()) 
					{	
$max=rand(1,30);
$color = substr(md5(time()), 0,6);
$colorpagina= '#'.$color; // imprime por ej: #fc443a
$html.=atajo($array['atajo'],$array['descripcion'],$colorpagina);
					}

function atajo($atajo,$descripción,$colorpagina){
$html="<body style='background-color:".$colorpagina."'>";
require_once '../comun/lib/dompdf/dompdf_config.inc.php';
$html.="<h1  style='font-size:100px;color:white;margin-top:40%;margin-left:25%;'>".$atajo."<h1>";
$html.="<h4 style='color:white;font-size:50px;margin-top:40%;margin-left:20%;'>".$descripción."<h4>";
$html.="<h3 style='color:white;font-size:50px;margin-left:40%;'>Excel<h3>";
$html.="</body>";
return $html;
}
$mipdf = new DOMPDF();
$mipdf ->set_paper('A4', "Landscape"); 
$mipdf ->load_html($html,'UTF-8');
$mipdf ->render();
$mipdf ->stream('Acta.pdf', array("Attachment" => 0));    
?>