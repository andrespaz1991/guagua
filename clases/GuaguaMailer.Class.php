<?php
 class GuaguaMailer 
{
	public $de = "andres.paz1991@gmail.com";
	public $para = "andres.paz1991@gmail.com";
	public $asunto     = "Orden de pedido "; 
	public $mensaje        = "<p>Orden de pedido.</p>"; 
	public $archivo =    "archivo.pdf";
	public $ruta = "pdfpersonas/archivo.pdf"; 
	public $tipo    = "application/pdf"; // type

	
public function enviar_mail(){
ini_set( 'display_errors', 1 );
error_reporting( E_ALL );  
$headers = "From:" . $this->de;
$boundary1   =rand(0,9)."-"
.rand(10000000000,9999999999)."-"
.rand(10000000000,9999999999)."=:"
.rand(10000,99999);
$eol = PHP_EOL;
$file = fopen($this->ruta, 'r');
$data = fread($file, filesize($this->ruta));
fclose($file);
$pdf = chunk_split(base64_encode($data));
#$fname="archivo.pdf";
$headers.='
MIME-Version: 1.0
Content-Type: multipart/alternative;
    boundary="'.$boundary1.'"';
$semi_rand     = md5(time());
$mime_boundary = "==Multipart_Boundary_$semi_rand";
$message= "Content-Type: $this->tipo;$eol name=\"$this->archivo\"$eol" .
    "Content-Disposition: attachment;$eol filename=\"$this->archivo\"$eol" .
    "Content-Transfer-Encoding: base64$eol$eol$pdf$eol--$mime_boundary--";
  if(mail($this->para,$this->asunto,$message, $headers)){
 echo "<script>alert('Envio realizado con exito');</script>";
  } else{
 echo "<script>alert('Envio NO realizado con exito');</script>";
  }
}

}
?>