<?php @ob_start(); @session_start(); ?>
  <style>
  * {
    font-family: "CurseCasual";
    font-size: 18px;
}
:root {
    --color_barra_curso: #f2721d;
    --color_portada: lightseagreen;
    --color_fuente_portada: white;
    --tamaño_fuente_portada: 55px;
}
  h1, h2, h3, h4, h5, h6, .h1, .h2, .h3, .h4, .h5, .h6 {
    font-family: inherit;
    font-weight: 500;
    line-height: 1.1;
    color: inherit;
}
.container {
    margin: -12px auto;
    padding: 0px;
    position: relative;
    width: 94%;
    height: -15%;
    font-size: 20px;
}
.Style-one-clr-two {
    background-color: #2bbeb5;
}
.style-box-one {
    text-align: center;
    padding: 20px;
    border-radius: 5px;
    -moz-border-radius: 5px;
    -webkit-border-radius: 5px;
    margin-bottom: 40px;
}
  </style>
    
    <link href="assets/css/style.css" rel="stylesheet" /> 
        <div class="container" style="margin-top:10%">
            <div class="navbar-header">

                <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">

                    <span class="icon-bar"></span>

                    <span class="icon-bar"></span>

                    <span class="icon-bar"></span>

                </button>

                </div>

    <!-- NAVBAR CODE END -->

    <div class="container">

        <div class="row text-center">

             </div>

        <!-- ROW END -->

        <div class="row">

            <!--div class="col-lg-3 col-md-3 col-sm-3">

                <div class="style-box-one Style-one-clr-two">

                            <a href="<?php echo SGA_CURSOS_URL?>">

                                <span class="glyphicon glyphicon-briefcase"></span>

                                 <h5>Cursos</h5>

                            </a>

                        </div>

                        </div-->



           

<?php if(isset($_SESSION['rol']) and $_SESSION['rol']=="admin" or ($_SESSION['rol']=="docente")){ ?>

              <div class="col-lg-3 col-md-3 col-sm-3">

                <div class="style-box-one Style-one-clr-two">

                            <a href="../asistencia/reporte_asistencia_docente.php">
                            
                                <span class="glyphicon glyphicon-briefcase"></span>

                                 <h5>Control de ingreso</h5>

                            </a>

                        </div>

                        </div>



 <div class="col-lg-3 col-md-3 col-sm-3">

                <div class="style-box-one Style-one-clr-two">

                            <a href="../cursos/edunotas.php">

                                <span class="glyphicon glyphicon-briefcase"></span>

                                 <h5>Edunotas</h5>

                            </a>

                        </div>

                        </div>







            <div class="col-lg-3 col-md-3 col-sm-3">

                <div class="style-box-one Style-one-clr-one">

                            <a href="seguimiento.php">

                                <span class="glyphicon glyphicon-briefcase"></span>

                                 <h5>Seguimiento</h5>

                            </a>

                        </div>

                        </div>

              <?php } ?>

              <!--div class="col-lg-3 col-md-3 col-sm-3">

                <div class="style-box-one Style-one-clr-two">

                            <a href="../planeador/index.php">

                                <span class="glyphicon glyphicon-book"></span>

                                 <h5>Planeador</h5>

                            </a>

                        </div>

                        </div-->

           <?php

            if(isset($_SESSION['rol']) and $_SESSION['rol']=="admin"){ ?>

             <div class="col-lg-3 col-md-3 col-sm-3">

                <div class="style-box-one Style-one-clr-three">

              <a href="../evaldocente/php/ver_resultados.php">

                                <span class="glyphicon glyphicon-stats"></span>

                                 <h5>Evaluación docente</h5>

                            </a>

                        </div>

                        </div>

                        <?php } ?>

              <div class="col-lg-3 col-md-3 col-sm-3">

                <div class="style-box-one Style-one-clr-four">

                            <a href="../reportes/informe_valorativo.php">

                                <span class="glyphicon glyphicon-pencil"></span>

                                <h5>Reportes</h5>

                            </a>

                        </div>

                        </div>      

            </div>

        <!-- ROW END FOR STYLE ONE -->

        <!--div class="row">

            

                 <div class="col-lg-3 col-md-3 col-sm-3">

                      <div class="alert alert-danger text-center style-box-two">

                            <span class="glyphicon glyphicon-tags"></span>

                            <h3>30+ Samples </h3>

                            Sample Data To Replace

                        </div>

                    </div>

             <div class="col-lg-3 col-md-3 col-sm-3">

                      <div class="alert alert-info text-center style-box-two">

                            <span class="glyphicon glyphicon-home"></span>

                            <h3>30+ Samples </h3>

                            Sample Data To Replace

                        </div>

                    </div>

               <div class="col-lg-3 col-md-3 col-sm-3">

                      <div class="alert alert-warning text-center style-box-two">

                            <span class="glyphicon glyphicon-download-alt"></span>

                            <h3>30+ Samples </h3>

                            Sample Data To Replace

                        </div>

                    </div>

               <div class="col-lg-3 col-md-3 col-sm-3">

                      <div class="alert alert-danger text-center style-box-two">

                            <span class="glyphicon glyphicon-qrcode"></span>

                            <h3>30+ Samples </h3>

                            Sample Data To Replace

                        </div>

                    </div>



        </div-->

         <!-- ROW END FOR STYLE TWO -->

        <!-- ROW END -->

        </div>

    <script src="assets/js/jquery-1.11.1.js"></script>

    <script src="assets/js/bootstrap.js"></script>   

<?php $contenido = ob_get_contents();

ob_clean();

include ("../comun/plantilla.php");

?>