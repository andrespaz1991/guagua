<?php
ob_start();

require("../comun/autoload.php");
require_once '../clases/Academico.Class.php';
require_once '../clases/Curso.Class.php';
$academico = new Academico();

$asignacion_id = isset($_GET['asignacion']) ? str_replace('"', "", $_GET['asignacion']) : '';

// 1. Lógica de Guardado (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['guardar_horario'])) {
    if (isset($_POST['modificar_flag'])) {
        $academico->eliminar_horario();
    }
    if (!empty($_POST['horario'])) {
        foreach ($_POST['horario'] as $value) {
            $academico->insertar_horario($value);
        }
    }
    echo "<script>alert2('Registro guardado exitosamente'); window.location='horario.php?asignacion=" . $asignacion_id . "';</script>";
    exit();
}

// 2. Consulta de Datos
$horarios = $academico->consultar_horario_simple($asignacion_id);
$modo_edicion = isset($_GET['modificar']) || empty($horarios);
?>

<style>
:root {
    --bg-main: #f4f7fe;
    --card-bg: #ffffff;
    --primary: #4318FF;
    --secondary: #A3AED0;
    --text-dark: #2B3674;
    --text-light: #707EAE;
    --shadow: 0px 18px 40px rgba(112, 144, 176, 0.12);
}
.horario-wrapper {
    background: var(--bg-main);
    padding: 30px;
    border-radius: 20px;
    min-height: 80vh;
}
.modern-card {
    background: var(--card-bg);
    border-radius: 15px;
    padding: 25px;
    box-shadow: var(--shadow);
    margin-bottom: 25px;
}
.modern-title {
    font-size: 1.5rem;
    font-weight: 700;
    color: var(--text-dark);
}
.day-pill {
    background: rgba(67, 24, 255, 0.1);
    color: var(--primary);
    padding: 8px 16px;
    border-radius: 50px;
    font-weight: 600;
    display: inline-block;
    margin: 5px;
}
.table-modern {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0 10px;
}
.table-modern th {
    color: var(--text-light);
    font-weight: 600;
    text-transform: uppercase;
    font-size: 0.85rem;
    padding: 0 15px 10px;
    border-bottom: 2px solid #f0f0f0;
}
.table-modern td {
    background: #fff;
    padding: 15px;
    color: var(--text-dark);
    border-bottom: 1px solid #f4f7fe;
}
.table-modern tr:hover td {
    background: #f8fafc;
}
.day-row-edit {
    background: #f8fafc;
    border-radius: 10px;
    padding: 15px;
    margin-bottom: 15px;
    border: 1px solid #e2e8f0;
    transition: all 0.3s ease;
}
.day-row-edit.active {
    border-color: var(--primary);
    background: rgba(67, 24, 255, 0.02);
}
.form-check-switch {
    transform: scale(1.2);
    cursor: pointer;
}
</style>

<div class="horario-wrapper">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="modern-title">Configuración de Horario</h2>
        <a href="../cursos/curso.php?asignacion=<?php echo $asignacion_id; ?>" class="btn btn-secondary shadow-sm" style="border-radius: 10px;">
            &larr; Volver al Curso
        </a>
    </div>

    <?php if (!$modo_edicion) { 
        $nombremateria = $horarios[0]['nombre_materia'];
        $diassemana = [];
    ?>
    
    <div class="modern-card">
        <div class="d-flex justify-content-between align-items-center border-bottom pb-3 mb-4">
            <div>
                <h4 class="mb-1 fw-bold text-primary"><?php echo htmlspecialchars($nombremateria); ?></h4>
                <p class="text-muted mb-0">
                    <i class="glyphicon glyphicon-calendar"></i> 
                    Inicio: <strong><?php echo Fecha::formato_fecha($horarios[0]['fecha_inicio']); ?></strong> | 
                    Fin: <strong><?php echo Fecha::formato_fecha($horarios[0]['fecha_fin']); ?></strong>
                </p>
            </div>
            <a href="horario.php?modificar=1&asignacion=<?php echo $asignacion_id; ?>" class="btn btn-primary shadow-sm" style="border-radius: 10px;">
                <i class="glyphicon glyphicon-edit"></i> Modificar
            </a>
        </div>

        <h5 class="fw-bold mb-3">Días de Clase</h5>
        <div class="row">
            <?php foreach ($horarios as $horario) { 
                $diassemana[] = $horario["dia"];
            ?>
            <div class="col-md-4 mb-3">
                <div class="p-3 border rounded shadow-sm text-center">
                    <span class="day-pill text-uppercase mb-2"><?php echo $horario["dia"]; ?></span>
                    <div class="fs-5 fw-bold text-dark">
                        <?php echo Fecha::formato_hora($horario["hora_inicio"]); ?> - <?php echo Fecha::formato_hora($horario["hora_fin"]); ?>
                    </div>
                </div>
            </div>
            <?php } ?>
        </div>
    </div>

    <!-- Fechas Generadas -->
    <div class="modern-card">
        <h4 class="fw-bold mb-4">Clases Programadas en el Rango</h4>
        <div class="table-responsive">
            <table class="table-modern">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Día</th>
                        <th>Fecha</th>
                        <th>Horas Estimadas</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                $id = 1;
                $horas1 = 0;
                $tminutos = 0;
                $dias = ["domingo","lunes","martes","miercoles","jueves","viernes","sabado"];
                $inicio = DateTime::createFromFormat('Y-m-d', $horarios[0]['fecha_inicio'], new DateTimeZone('America/Bogota'));
                $fin_dt = DateTime::createFromFormat('Y-m-d', $horarios[0]['fecha_fin'], new DateTimeZone('America/Bogota'));
                
                while ($inicio <= $fin_dt) {
                    $dia_str = mb_strtolower($dias[$inicio->format("w")], 'UTF-8');
                    if (in_array($dia_str, $diassemana)) {
                        $bg = ($inicio->format("Y-m-d") <= date('Y-m-d')) ? 'style="background-color:#e6f8f3;"' : '';
                ?>
                    <tr <?php echo $bg; ?>>
                        <td>#<?php echo $id++; ?></td>
                        <td class="text-capitalize fw-bold text-primary"><?php echo $dias[$inicio->format("w")]; ?></td>
                        <td><?php echo Fecha::formato_fecha($inicio->format("Y-m-d")) . ' <small class="text-muted">('.$inicio->format("d-m-Y").')</small>'; ?></td>
                        <td>
                            <?php 
                            foreach($horarios as $valor){
                                if($dias[$inicio->format("w")] == $valor['dia']){
                                    list($horas2,$minutos1,$segundos1) = Fecha::RestarHoras($valor["hora_fin"],$valor['hora_inicio']); 
                                    $horas1 += $horas2;
                                    $tminutos += $minutos1;
                                    echo "<span class='badge bg-info' style='background-color:#17a2b8; padding:5px 10px; border-radius:5px;'>". (($tminutos/60) + $horas1) ." h</span>";
                                }
                            }
                            ?>
                        </td>
                    </tr>
                <?php
                    }
                    $inicio->modify('+1 day');
                }
                ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Asistencia -->
    <div class="modern-card">
        <h4 class="fw-bold mb-4">Registro de Asistencia y Horas Reales</h4>
        <div class="table-responsive">
            <table class="table-modern">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Fecha</th>
                        <th>Horas Registradas</th>
                        <th>Asistentes</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                if(class_exists('Control_ingreso')){
                    $control_ingreso = new Control_ingreso();
                    $datos = $control_ingreso->control_materia($asignacion_id);
                    $contaminutos = 0;
                    $id = 1;
                    $thoras = 0;
                    
                    if(!empty($datos)){
                        foreach($datos as $asistencia){
                            $estado = $control_ingreso->verificar_asistencia($asistencia['fecha_ingreso'], $asignacion_id, 'no');
                ?>
                    <tr>
                        <td>#<?php echo $id++; ?></td>
                        <td><?php echo Fecha::formato_fecha($asistencia['fecha_ingreso']); ?></td>
                        <td>
                            <?php 
                            list($horas,$minutos,$segundos) = Fecha::RestarHoras($asistencia['hora_ingreso'], $asistencia['hora_salida']); 
                            echo "<strong>".$horas."</strong>";
                            if($estado['cantidad'] > 0){
                                $thoras += $horas;  
                            }
                            if ($minutos >= 30 || $contaminutos >= 30) {
                                if($minutos > 0) echo ' y '.$minutos.' min';
                                if($estado['cantidad'] > 0) $contaminutos += $minutos;  
                            }
                            ?>
                        </td>
                        <td><span class="badge" style="background:var(--primary); font-size:14px; padding:6px 12px; border-radius:8px;"><?php echo $estado['cantidad']; ?> estudiantes</span></td>
                    </tr>
                <?php 
                        } 
                    } else {
                        echo "<tr><td colspan='4' class='text-center text-muted py-4'>No hay registros de asistencia aún.</td></tr>";
                    }
                }
                ?>
                </tbody>
                <tfoot>
                    <tr style="background: #f8fafc; font-weight: bold; font-size: 1.1rem;">
                        <td colspan="2" class="text-right text-primary">TOTAL ACUMULADO:</td>
                        <td class="text-success"><?php echo (isset($thoras) ? $thoras + ($contaminutos/60) : 0); ?> Horas</td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <?php } else { 
        // MODO EDICION 
        $fecha_inicio = empty($horarios) ? date('Y-m-d') : $horarios[0]['fecha_inicio'];
        $fecha_fin = empty($horarios) ? date('Y-m-d') : $horarios[0]['fecha_fin'];
    ?>
    
    <div class="modern-card">
        <h3 class="modern-title mb-4 border-bottom pb-3">Configurar Horario</h3>
        <form action="" method="POST">
            <input type="hidden" name="guardar_horario" value="1">
            <?php if(isset($_GET['modificar'])) echo "<input type='hidden' name='modificar_flag' value='1'>"; ?>
            <input type="hidden" name="asignacion" value="<?php echo htmlspecialchars($asignacion_id); ?>">

            <div class="row mb-5">
                <div class="col-md-6">
                    <label class="form-label fw-bold text-muted text-uppercase">Fecha Inicio</label>
                    <input type="date" name="fecha_inicio" class="form-control input-lg shadow-sm border-0 bg-light" style="padding: 10px;" value="<?php echo $fecha_inicio; ?>" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold text-muted text-uppercase">Fecha Fin</label>
                    <input type="date" name="fecha_fin" class="form-control input-lg shadow-sm border-0 bg-light" style="padding: 10px;" value="<?php echo $fecha_fin; ?>" required>
                </div>
            </div>

            <h4 class="fw-bold mb-4">Días y Horas de Clase</h4>
            
            <?php 
            $dias_semana = ['lunes', 'martes', 'miercoles', 'jueves', 'viernes', 'sabado', 'domingo'];
            $horarios_activos = [];
            if (!empty($horarios)) {
                foreach ($horarios as $h) {
                    $horarios_activos[strtolower($h['dia'])] = $h;
                }
            }
            
            foreach ($dias_semana as $dia) {
                $activo = isset($horarios_activos[$dia]);
                $h_ini = $activo ? $horarios_activos[$dia]['hora_inicio'] : '07:00';
                $h_fin = $activo ? $horarios_activos[$dia]['hora_fin'] : '08:00';
            ?>
            <div class="day-row-edit <?php echo $activo ? 'active' : ''; ?>" id="row_<?php echo $dia; ?>">
                <div class="row align-items-center">
                    <div class="col-md-3 col-xs-12 mb-3 mb-md-0">
                        <label class="d-flex align-items-center" style="cursor: pointer;">
                            <input type="checkbox" class="form-check-switch day-toggle me-3" style="width:20px; height:20px;" name="horario[]" value="<?php echo $dia; ?>" <?php echo $activo ? 'checked' : ''; ?>>
                            <span class="fs-5 fw-bold text-uppercase ms-2"><?php echo $dia; ?></span>
                        </label>
                    </div>
                    <div class="col-md-9 col-xs-12 time-inputs" id="times_<?php echo $dia; ?>" style="display: <?php echo $activo ? 'flex' : 'none'; ?>; flex-wrap: wrap; gap: 15px;">
                        <div class="d-flex align-items-center flex-grow-1">
                            <label class="me-3 mb-0 fw-bold text-muted">Inicio:</label>
                            <input type="time" name="hora_inicio[<?php echo $dia; ?>]" class="form-control shadow-sm border-0 time-input-<?php echo $dia; ?>" value="<?php echo $h_ini; ?>">
                        </div>
                        <div class="d-flex align-items-center flex-grow-1">
                            <label class="me-3 mb-0 fw-bold text-muted">Fin:</label>
                            <input type="time" name="hora_fin[<?php echo $dia; ?>]" class="form-control shadow-sm border-0 time-input-<?php echo $dia; ?>" value="<?php echo $h_fin; ?>">
                        </div>
                    </div>
                </div>
            </div>
            <?php } ?>

            <div class="text-center" style="margin-top: 30px; padding: 25px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px;">
                <button class="btn btn-success btn-lg shadow-sm" style="border-radius: 10px; font-weight: bold; padding: 12px 40px; font-size: 1.2rem;" type="submit">
                    <i class="glyphicon glyphicon-floppy-disk"></i> Guardar Horario
                </button>
            </div>
        </form>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
    $(document).ready(function() {
        $('.day-toggle').change(function() {
            var dia = $(this).val();
            var row = $('#row_' + dia);
            if ($(this).is(':checked')) {
                $('#times_' + dia).css('display', 'flex').hide().fadeIn(300);
                row.addClass('active');
            } else {
                $('#times_' + dia).fadeOut(300);
                row.removeClass('active');
            }
        });
    });
    </script>
    <?php } ?>
</div>

<?php
$contenido = ob_get_clean();
require ("../comun/plantilla.php");
?>