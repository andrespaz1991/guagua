<?php
header("Content-type: text/css; charset: UTF-8");
$colores=array("#de3153","#faae16","#00aceb","#f06232","#7bba3f","#894094","#ed2b9c","#14a88d","#49489c");
$cont=0;
foreach ($colores as $color){
    ?>
    #titulo>span:nth-child(<?php echo $cont ?>n) {
    color: <?php echo $color ?>;
}
    <?php
    $cont++;
}
shuffle($colores);//colores aleatorios
$cont=0;
foreach ($colores as $color){
?>
.navbar-default .navbar-nav>li:nth-child(<?php echo $cont ?>)>a {
    color: <?php echo $color ?> !important;
}
.dropdown-menu>li:nth-child(<?php echo $cont ?>n)>a{
    color: <?php echo $color ?>;
}
.dropdown-menu>li:nth-child(<?php echo $cont ?>n) span:after{
     color: <?php echo $color ?>;
}
<?php
$cont++;
} ?>