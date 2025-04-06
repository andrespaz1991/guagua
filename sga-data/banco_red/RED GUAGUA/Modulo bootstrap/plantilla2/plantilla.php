<?php 
$proyecto="prueba";
define('PLANTILLA',$_SERVER["DOCUMENT_ROOT"].'/'.$proyecto."/plantilla2/"); 
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>NOMBRE PROYECTO</title>
  <!-- Llamado de librerias que facilitan el uso y estilo-->
  <!-- Google Font: Source Sans Pro -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="plugins/fontawesome-free/css/all.min.css">
  <!-- Ionicons -->
  <link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">
  <!-- Tempusdominus Bootstrap 4 -->
  <link rel="stylesheet" href="plantilla2/plugins/tempusdominus-bootstrap-4/css/tempusdominus-bootstrap-4.min.css">
  <!-- iCheck -->
  <link rel="stylesheet" href="plantilla2/plugins/icheck-bootstrap/icheck-bootstrap.min.css">
  <!-- JQVMap -->
  <link rel="stylesheet" href="plantilla2/plugins/jqvmap/jqvmap.min.css">
  <!-- Theme style -->
  <link rel="stylesheet" href="plantilla2/dist/css/adminlte.min.css">
  <!-- overlayScrollbars -->
  <link rel="stylesheet" href="plantilla2/plugins/overlayScrollbars/css/OverlayScrollbars.min.css">
  <!-- Daterange picker -->
  <link rel="stylesheet" href="plantilla2/plugins/daterangepicker/daterangepicker.css">
  <!-- summernote -->
  <link rel="stylesheet" href="plantilla2/plugins/summernote/summernote-bs4.min.css">
  <link rel="stylesheet" href="https://pro.fontawesome.com/releases/v5.10.0/css/all.css" integrity="sha384-AYmEC3Yw5cVb3ZcuHtOA93w35dYTsvhLPVnYs9eStHfGJvOvKxVfELGroGkvsg+p" crossorigin="anonymous"/>

</head>
<body class="hold-transition sidebar-mini layout-fixed">
  <!-- Preloader -->
 

  <?php require PLANTILLA.'header.php';?>
<!-- Llamado de librerias que facilitan el uso y estilo-->
      <!-- Sidebar Menu -->
      <?php require PLANTILLA.'menu.php';?>
      <!-- /.sidebar-menu -->
    

  <!-- Contenido -->
  
    <div class="content-wrapper" style="padding:5%">
    <?php if (isset($contenido)) echo $contenido; ?>
  </div>
<!-- Contenido -->


  <!-- /.pie de página -->
  <?php require PLANTILLA.'footer.php';?>
 <!-- /.pie de página -->


 <!-- Panel de control y personalización derecho -->
  <aside class="control-sidebar control-sidebar-dark">
  </aside>
  <!-- Panel de control y personalización derecho -->
</div>


<!--- Llamado de librerias de panel derecho-->
<!-- ./wrapper -->
<!-- jQuery -->
<script src="plantilla2/plugins/jquery/jquery.min.js"></script>
<!-- jQuery UI 1.11.4 -->
<script src="plantilla2/plugins/jquery-ui/jquery-ui.min.js"></script>
<!-- Resolve conflict in jQuery UI tooltip with Bootstrap tooltip -->
<script>
  $.widget.bridge('uibutton', $.ui.button)
</script>
<!-- Bootstrap 4 -->
<script src="plantilla2/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<!-- ChartJS -->
<script src="plantilla2/plugins/chart.js/Chart.min.js"></script>
<!-- Sparkline -->
<script src="plantilla2/plugins/sparklines/sparkline.js"></script>
<!-- JQVMap -->
<script src="plantilla2/plugins/jqvmap/jquery.vmap.min.js"></script>
<script src="plantilla2/plugins/jqvmap/maps/jquery.vmap.usa.js"></script>
<!-- jQuery Knob Chart -->
<script src="plantilla2/plugins/jquery-knob/jquery.knob.min.js"></script>
<!-- daterangepicker -->
<script src="plantilla2/plugins/moment/moment.min.js"></script>
<script src="plantilla2/plugins/daterangepicker/daterangepicker.js"></script>
<!-- Tempusdominus Bootstrap 4 -->
<script src="plantilla2/plugins/tempusdominus-bootstrap-4/js/tempusdominus-bootstrap-4.min.js"></script>
<!-- Summernote -->
<script src="plantilla2/plugins/summernote/summernote-bs4.min.js"></script>
<!-- overlayScrollbars -->
<script src="plantilla2/plugins/overlayScrollbars/js/jquery.overlayScrollbars.min.js"></script>
<!-- AdminLTE App -->
<script src="plantilla2/dist/js/adminlte.js"></script>
<!-- AdminLTE for demo purposes -->
<script src="plantilla2/dist/js/demo.js"></script>
<!-- AdminLTE dashboard demo (This is only for demo purposes) -->
<script src="plantilla2/dist/js/pages/dashboard.js"></script>
<!--- Llamado de librerias de panel derecho-->
</body>
</html>
