<?php
require '../comun.class.php'; 
$menu=new Comun();
$datos_menu=$menu->consultar_menu();
foreach ($datos_menu as $key => $value) {      ?>
<a href="<?php echo $value->menu_url; ?>"><?php echo $value->menu_item_name; ?></a>
<?php } 
