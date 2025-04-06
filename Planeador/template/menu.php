<style type="text/css">
	.vertical-menu {
  width: 1200px; /* Set a width if you like */
margin-left:-5%;
margin-top:-10%;
    position: absolute;
}

.vertical-menu a {

  color: black; /* Black text color */

  display: inline-block; /* Make the links appear below each other */

  padding: 12px; /* Add some padding */

  text-decoration: none; /* Remove underline from links */

}

.botones {

      background-color: white; 

      color: black; 

      border: 2px solid #008CBA;

}

</style>
<div class="vertical-menu">
<?php 
##########
require_once($_SERVER['DOCUMENT_ROOT'].'/'.'guagua'.'/'."/comun/autoload.php");

$menu=new Planeacion();
$datos_menu=$menu->consultar_menu("Planeacion");
foreach ($datos_menu as $key => $value) {      ?>
<a target="_blank" class="botones" href="<?php echo $value->menu_url; ?>"><?php echo $value->menu_item_name; ?></a>       <?php } 
  ?>
	<?php if (isset($_SESSION['user'])) { ?>  	     	      
<?php }?>
</div>	

