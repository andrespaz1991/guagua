<?php

error_reporting(E_ALL);
ini_set('display_errors', '1');
ob_start();
@session_start();
?>
<script>
window.onload = function() {
    document.getElementById('nombrees').focus();
};


$(document).ready(function() {
    // Se usa delegación de eventos para que funcione con contenido cargado dinámicamente.
    $(document.body).on('click', '.control-visibilidad-btn', function(e) {
        e.preventDefault();
        e.stopPropagation();

        var button = $(this);
        var asignacionId = button.data('id');
        var currentState = button.data('visible');
        var newState = (currentState === 'si') ? 'no' : 'si';

        $.ajax({
            url: 'cursos/actualizar_visibilidad.php', // Asegúrate que esta ruta sea correcta
            type: 'POST',
            data: JSON.stringify({
                id_asignacion: asignacionId,
                visible: newState
            }),
            contentType: 'application/json; charset=utf-8',
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    // Al tener éxito, se recarga la página.
                    // Esto asegura que la lista de cursos se sincronice perfectamente
                    // con el estado actualizado en la base de datos.
                    location.reload();
                } else {
                    console.error('Error del servidor: ' + response.message);
                    alert('Hubo un error al actualizar el curso.');
                }
            },
            error: function() {
                console.error('Error de comunicación AJAX.');
                alert('Hubo un error de comunicación con el servidor.');
            }
        });
    });
});

  </script>
  
<?php
###############################
$_SESSION['rol']="docente";
$_SESSION['id_usuario']="1085290375";
$_SESSION['id_institucion']="7";
##################################
require_once("comun/autoload.php");
#unset($_SESSION['barra_busqueda']);
$academico=new Academico();
$eventos=new Eventos();
$mensajes=new Mensajes();   
$actividad=new Actividad();
$curso=new Curso();
$red=new Red(); 
$fecha=new Fecha();
$persona=new Persona();             
####### Notificar Sobre Eventos

$eventos->notificador_eventos(date('Y-m-d'));
$academico->verificarAsistenciaYRedirigir();
#exit();
####### Notificar Sobre Eventos
$academico->ano_lectivo =ano_lectivo();

if(isset($_SESSION['id_institucion'])){
  $datos_curso =$academico ->mis_cursos_otros(); 
}

if(!isset($_SESSION['rol'])) $_SESSION['rol']="invitado";
$tarjetas=$persona->permiso_home();

if(!empty($_SESSION) and $_SESSION['rol']=="docente"){
if(!isset($_SESSION["asistencia"]) or $_SESSION["asistencia"]=="si"){
$asistencia=$academico->consultar_horario(true);
}
}

?>
<style type="text/css">
body{
    background-color:#31708f!important;
}
</style>
<br>
<?php
// WIDGET DE EVOLUCIÓN DEL PERIODO
require_once(dirname(__FILE__)."/comun/conexion.php");
$widget_period_name = "Sin Periodo Activo";
$widget_start_date = 0;
$widget_end_date = 0;

$sql_periodo_widget = "SELECT nombre_periodo, fecha_inicio, fecha_fin FROM periodo WHERE estado_periodo = '1' LIMIT 1";
if (!isset($mysqli) || $mysqli === null) {
    $mysqli = new mysqli(SERVIDORBD, USUARIOBD, CLAVEBD, BASEDEDATOS);
}
$query_periodo_widget = $mysqli->query($sql_periodo_widget);
if($query_periodo_widget && $query_periodo_widget->num_rows > 0) {
    $activePeriodData = $query_periodo_widget->fetch_assoc();
    $widget_period_name = "Periodo " . $activePeriodData['nombre_periodo'];
    // Validar fechas
    if($activePeriodData['fecha_inicio'] != '0000-00-00' && $activePeriodData['fecha_fin'] != '0000-00-00'){
        $widget_start_date = strtotime($activePeriodData['fecha_inicio']) * 1000;
        $widget_end_date = strtotime($activePeriodData['fecha_fin']) * 1000;
    }
}
?>

<style type="text/css">
.period-widget-wrap {
    z-index: 9999;
}
.period-widget-container {
    position: relative;
    background: rgba(255, 255, 255, 0.1);
    backdrop-filter: blur(15px);
    -webkit-backdrop-filter: blur(15px);
    border: 1px solid rgba(255, 255, 255, 0.2);
    box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.25);
    border-radius: 15px;
    padding: 20px 20px;
    color: #fff;
    margin-bottom: 25px;
    font-family: 'Inter', 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
    transition: all 0.3s ease;
    overflow: visible;
    height: 100%;
}
.period-widget-container:hover {
    box-shadow: 0 12px 40px rgba(0, 0, 0, 0.35);
}
.pw-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}
.pw-title {
    margin: 0;
    font-weight: 600;
    font-size: 1.1rem;
    display: flex;
    align-items: center;
    gap: 8px;
    color: #fff;
}
.pw-settings-btn {
    background: rgba(255, 255, 255, 0.1);
    border: 1px solid rgba(255, 255, 255, 0.2);
    border-radius: 50%;
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    font-size: 1.1rem;
    cursor: pointer;
    transition: transform 0.3s ease, background 0.3s;
}
.pw-settings-btn:hover {
    background: rgba(255, 255, 255, 0.25);
    transform: rotate(90deg);
}
.pw-progress-info {
    display: flex;
    flex-direction: column;
    align-items: center;
    margin-bottom: 15px;
}
.pw-percentage {
    font-size: 3.5rem;
    font-weight: 800;
    line-height: 1.1;
    transition: color 0.3s, text-shadow 0.3s;
    margin: 10px 0;
}
.pw-labels {
    width: 100%;
    display: flex;
    justify-content: space-between;
    margin-bottom: 8px;
}
.pw-label-left, .pw-label-right {
    font-size: 0.8rem;
    color: rgba(255,255,255,0.9);
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}
.pw-progress-track {
    width: 100%;
    height: 16px;
    background: rgba(0, 0, 0, 0.4);
    border-radius: 20px;
    overflow: hidden;
    position: relative;
    box-shadow: inset 0 2px 6px rgba(0,0,0,0.4);
}
.pw-progress-fill {
    height: 100%;
    width: 0%;
    border-radius: 20px;
    transition: width 1.5s cubic-bezier(0.34, 1.56, 0.64, 1);
    position: relative;
}
.pw-progress-fill::after {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0; bottom: 0;
    background: linear-gradient(90deg, rgba(255,255,255,0) 0%, rgba(255,255,255,0.4) 50%, rgba(255,255,255,0) 100%);
    animation: pw-shimmer 2.5s infinite linear;
}
@keyframes pw-shimmer {
    0% { transform: translateX(-100%); }
    100% { transform: translateX(100%); }
}
.pw-time-details {
    margin-top: 15px;
    text-align: center;
    font-size: 0.9rem;
    color: rgba(255,255,255,0.9);
    font-weight: 500;
    background: rgba(0,0,0,0.2);
    padding: 8px;
    border-radius: 8px;
}
.pw-settings-popover {
    position: absolute;
    top: 60px;
    right: -10px; /* Shift a bit to avoid overflowing edge */
    background: #ffffff;
    border: none;
    border-radius: 12px;
    padding: 20px;
    width: 280px;
    box-shadow: 0 15px 45px rgba(0,0,0,0.4);
    z-index: 999999; /* Ensure it stays above everything */
    color: #333;
    display: none;
    transform-origin: top right;
    animation: pw-popin 0.2s ease-out;
}
@keyframes pw-popin {
    from { opacity: 0; transform: scale(0.9); }
    to { opacity: 1; transform: scale(1); }
}
.pw-settings-popover h5 {
    margin-top: 0;
    color: #222;
    border-bottom: 1px solid #eee;
    padding-bottom: 12px;
    font-size: 1.1rem;
    font-weight: 600;
    margin-bottom: 15px;
}
.pw-form-group {
    margin-bottom: 15px;
}
.pw-form-group label {
    display: block;
    font-size: 0.9rem;
    color: #555;
    margin-bottom: 6px;
    font-weight: 500;
}
.pw-color-wrap {
    display: flex;
    align-items: center;
    gap: 10px;
}
.pw-save-btn {
    width: 100%;
    margin-top: 10px;
    background: #31708f;
    border: none;
    padding: 10px;
    border-radius: 6px;
    color: #fff;
    font-weight: 600;
    transition: background 0.2s;
}
.pw-save-btn:hover {
    background: #245269;
}
</style>

<div class="col-xs-12 col-lg-4 widget-header-item period-widget-wrap">
    <div class="period-widget-container" id="periodWidget">
        <div class="pw-header">
            <h4 class="pw-title"><span class="pw-icon">🚀</span> <span id="pwPeriodName">Cargando...</span></h4>
            <button class="pw-settings-btn" id="pwSettingsBtn" title="Configurar Widget">⚙️</button>
        </div>
        
        <div class="pw-body">
            <div class="pw-progress-info">
                <span id="pwPercentage" class="pw-percentage">0%</span>
            </div>
            
            <div class="pw-labels">
                <span id="pwLabelLeft" class="pw-label-left">Inicio</span>
                <span id="pwLabelRight" class="pw-label-right">Fin</span>
            </div>
            <div class="pw-progress-track">
                <div class="pw-progress-fill" id="pwProgressFill"></div>
            </div>
            
            <div class="pw-time-details">
                <span id="pwTimeElapsedText">Calculando...</span>
            </div>
        </div>

        <div class="pw-settings-popover" id="pwSettingsPopover">
            <h5>Configuración del Widget</h5>
            <div class="pw-form-group">
                <label>Modo de Evolución:</label>
                <select id="pwModeSelect" class="form-control input-sm">
                    <option value="elapsed">Tiempo Transcurrido (Aumento)</option>
                    <option value="remaining">Tiempo Restante (Disminución)</option>
                </select>
            </div>
            <div class="pw-form-group">
                <label>Color Principal:</label>
                <div class="pw-color-wrap">
                    <input type="color" id="pwColorPicker" value="#00e5ff" style="width: 40px; height: 35px; padding: 0; border: none; cursor: pointer;">
                    <span id="pwColorHex" style="font-size: 0.9rem; color: #666;">#00e5ff</span>
                </div>
            </div>
            <button class="pw-save-btn" id="pwSaveSettingsBtn">Guardar Cambios</button>
        </div>
    </div>
</div>

<script>
$(function() {
    const periodName = "<?php echo $widget_period_name; ?>";
    const startDate = <?php echo $widget_start_date; ?>;
    const endDate = <?php echo $widget_end_date; ?>;
    
    let settings = {
        mode: 'elapsed',
        color: '#00e5ff' 
    };

    const savedSettings = localStorage.getItem('guagua_period_widget_settings');
    if(savedSettings) {
        try {
            settings = Object.assign(settings, JSON.parse(savedSettings));
        } catch(e){}
    }

    const $name = $('#pwPeriodName');
    const $percent = $('#pwPercentage');
    const $fill = $('#pwProgressFill');
    const $leftLabel = $('#pwLabelLeft');
    const $rightLabel = $('#pwLabelRight');
    const $timeDetails = $('#pwTimeElapsedText');
    const $settingsBtn = $('#pwSettingsBtn');
    const $popover = $('#pwSettingsPopover');
    const $modeSelect = $('#pwModeSelect');
    const $colorPicker = $('#pwColorPicker');
    const $colorHex = $('#pwColorHex');
    const $saveBtn = $('#pwSaveSettingsBtn');

    $modeSelect.val(settings.mode);
    $colorPicker.val(settings.color);
    $colorHex.text(settings.color);

    $colorPicker.on('input', function() {
        $colorHex.text($(this).val());
    });

    function applySettingsToUI() {
        $fill.css({
            'background': settings.color,
            'box-shadow': `0 0 12px ${settings.color}, inset 0 0 8px rgba(255,255,255,0.3)`
        });
        
        let rgb = hexToRgb(settings.color);
        if(rgb) {
            $percent.css('text-shadow', `0 0 15px rgba(${rgb.r}, ${rgb.g}, ${rgb.b}, 0.6)`);
        }
    }

    function hexToRgb(hex) {
        var result = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(hex);
        return result ? {
            r: parseInt(result[1], 16),
            g: parseInt(result[2], 16),
            b: parseInt(result[3], 16)
        } : null;
    }

    function calculateProgress() {
        if (startDate === 0 || endDate === 0) {
            $name.text(periodName);
            $percent.text('N/A');
            $timeDetails.text('Fechas del periodo no están definidas.');
            return;
        }

        $name.text(periodName);
        const now = new Date().getTime();
        const total = endDate - startDate;
        let elapsed = now - startDate;

        if (elapsed < 0) elapsed = 0;
        if (elapsed > total) elapsed = total;

        let pctElapsed = (elapsed / total) * 100;
        let pctRemaining = 100 - pctElapsed;

        const dateOpt = { year: 'numeric', month: 'short', day: 'numeric' };
        const dStart = new Date(startDate).toLocaleDateString('es-ES', dateOpt);
        const dEnd = new Date(endDate).toLocaleDateString('es-ES', dateOpt);
        
        let displayPct = 0;
        let progressWidth = 0;
        const totalDays = Math.ceil(total / (1000 * 60 * 60 * 24));
        const elapsedDays = Math.floor(elapsed / (1000 * 60 * 60 * 24));
        const remainingDays = Math.ceil((total - elapsed) / (1000 * 60 * 60 * 24));

        if (settings.mode === 'elapsed') {
            displayPct = pctElapsed;
            progressWidth = pctElapsed;
            $leftLabel.text('Inicio: ' + dStart);
            $rightLabel.text('Fin: ' + dEnd);
            $timeDetails.text(`Han transcurrido ${elapsedDays} días de un total de ${totalDays} días.`);
        } else {
            displayPct = pctRemaining;
            progressWidth = pctRemaining;
            $leftLabel.text('Restante');
            $rightLabel.text('Fin: ' + dEnd);
            $timeDetails.text(`Faltan ${remainingDays} días para finalizar el periodo.`);
        }

        $percent.text(displayPct.toFixed(1) + '%');
        
        setTimeout(() => {
            $fill.css('width', progressWidth + '%');
        }, 150);
    }

    $settingsBtn.on('click', function(e) {
        e.stopPropagation();
        $popover.toggle();
    });

    $popover.on('click', function(e) {
        e.stopPropagation();
    });

    $(document).on('click', function() {
        $popover.hide();
    });

    $saveBtn.on('click', function() {
        settings.mode = $modeSelect.val();
        settings.color = $colorPicker.val();
        localStorage.setItem('guagua_period_widget_settings', JSON.stringify(settings));
        $popover.hide();
        
        applySettingsToUI();
        $fill.css('width', '0%'); // Reset for animation
        setTimeout(calculateProgress, 50);
    });

    applySettingsToUI();
    calculateProgress();
});
</script>
<?php 
foreach($tarjetas as $clave => $row){ ?>
 <div class="col-xs-12 col-lg-4 widget-header-item">
 <div class="panel panel-<?php echo $row['class_color'] ?>">
                    <div class="panel-heading">
                      <h4><?php echo $row['titulo'] ?></h4>
                                <a href="<?php echo $row['accion_rapida'] ?>"><img style='margin-top:-12%' align="right" id="icono_tarjeta_home" src="<?php echo SGA_COMUN_URL ?>/img/png/<?php echo $row['icono'] ?>"></a>
                    </div>
                    <div style="overflow: scroll;"  class="panel-body tarjeta">
                              <p>
                              <?php
                            #  echo "<pre>";
                            # print_R($row['funcion']);
                            # print_R("</pre>");
                             eval($row['funcion']) ?>
                              </p>                 
                    </div>
                    <div class="panel-footer">
                                <a href="<?php echo SGA_URL.$row['href'] ?>" class="btn btn-<?php echo $row['class_color'] ?>">Ver más</a>
                    </div>
                          
                          </div>
  </div>                          
<?php } ?>

<?php $contenido = ob_get_contents();
ob_clean();
include (dirname(__FILE__)."/comun/plantilla.php");
?>