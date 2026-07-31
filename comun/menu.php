<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once(dirname(__FILE__) . "/funciones.php");
require_once(dirname(__FILE__) . "/config.php");
require_once(dirname(__FILE__) . "/autoload.php");

if (isset($_GET['logout'])) {
    session_destroy();
    session_unset();
}

$persona = null;
if (isset($_SESSION['id_usuario'])) {
    $persona = new Persona($_SESSION['id_usuario']);
}

$array_roles = array(
    "admin" => "Administrador",
    "docente" => "Docente",
    "estudiante" => "Estudiante",
    "acudiente" => "Acudiente"
);

$institucion = null;
if (!empty($_SESSION['id_institucion'])) {
    $institucion = new Institucion($_SESSION['id_institucion']);
}

$rol_actual = isset($_SESSION['rol']) ? $_SESSION['rol'] : '';
$barra_busqueda = isset($_SESSION['barra_busqueda']) ? $_SESSION['barra_busqueda'] : '';
$archivo_actual = basename($_SERVER['SCRIPT_NAME']);
?>
<link rel="stylesheet" href="<?php echo SGA_COMUN_URL; ?>/css/menu_custom.css?v=<?php echo file_exists(dirname(__FILE__).'/css/menu_custom.css') ? filemtime(dirname(__FILE__).'/css/menu_custom.css') : time(); ?>">
<style>
/* =========================================================
   BARRA PRINCIPAL - 100% ANCHO - 90px ALTO - SIN OVERFLOW
   ========================================================= */
.navbar.navbar-default {
    background-color: #ffffff !important;
    border: none !important;
    border-bottom: 2px solid #e8edf3 !important;
    box-shadow: 0 3px 12px rgba(0,0,0,0.08) !important;
    width: 100% !important;
    max-width: 100% !important;
    min-height: 90px;
    margin: 0 !important;
    padding: 0 12px 0 4px !important;
    box-sizing: border-box !important;
    border-radius: 0 !important;
    /* LAYOUT: flexbox de una sola fila */
    display: flex !important;
    flex-direction: row !important;
    align-items: center !important;
    justify-content: flex-start !important;
    overflow-x: hidden !important;
    overflow-y: visible !important;
    z-index: 1000 !important;
    position: relative !important;
}

/* Ocultar el header clásico de Bootstrap (logo "Guagua") - los iconos van a la izquierda */
.navbar.navbar-default .navbar-header {
    display: none !important;
}

/* El collapse ocupa todo el espacio disponible y organiza todo en una fila */
.navbar.navbar-default .navbar-collapse.navbar-ex1-collapse {
    display: flex !important;
    flex-direction: row !important;
    align-items: center !important;
    justify-content: space-between !important;
    width: 100% !important;
    height: 100% !important;
    padding: 0 !important;
    margin: 0 !important;
    border: none !important;
    box-shadow: none !important;
    overflow-x: hidden !important;
    overflow-y: visible !important;
}

/* CONTENEDOR DEL SLIDER - OCUPA TODO EL ESPACIO CENTRAL DESDE LA IZQUIERDA */
.nav-slider-container {
    display: flex !important;
    flex-direction: row !important;
    align-items: center !important;
    flex: 1 !important;
    min-width: 0 !important;
    height: 100% !important;
    margin: 0 !important;
    padding: 0 !important;
    float: none !important;
    overflow-x: hidden !important;
    overflow-y: visible !important;
}

.nav-slider-wrapper {
    overflow-x: auto !important;
    overflow-y: visible !important;
    width: 100% !important;
    height: 100% !important;
    max-height: 100% !important;
    display: flex !important;
    align-items: center !important;
    scroll-behavior: smooth !important;
    -ms-overflow-style: none !important;
    scrollbar-width: none !important;
    margin: 0 !important;
    padding: 0 !important;
}

.nav-slider-wrapper::-webkit-scrollbar { display: none !important; }

.slider-track {
    display: flex !important;
    flex-direction: row !important;
    align-items: center !important;
    flex-wrap: nowrap !important;
    height: 100% !important;
    margin: 0 !important;
    padding: 0 !important;
    float: none !important;
    list-style: none !important;
}

.slider-track > li {
    display: inline-flex !important;
    flex-direction: column !important;
    align-items: center !important;
    justify-content: center !important;
    height: 100% !important;
    float: none !important;
    flex-shrink: 0 !important;
}

/* Botones de desplazamiento del slider */
.nav-slider-btn {
    background: rgba(241,245,249,0.95) !important;
    border: 1px solid #cbd5e1 !important;
    border-radius: 50% !important;
    width: 36px !important;
    height: 36px !important;
    min-width: 36px !important;
    font-size: 18px !important;
    cursor: pointer !important;
    padding: 0 !important;
    color: #64748b !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    transition: all 0.2s ease !important;
    margin: 0 4px !important;
    flex-shrink: 0 !important;
}
.nav-slider-btn:hover {
    color: #0f172a !important;
    background: #fff !important;
    border-color: #94a3b8 !important;
    box-shadow: 0 2px 6px rgba(0,0,0,0.12) !important;
}

/* ZONA DERECHA: dropdown engranaje + foto perfil */
.navbar-right-zone {
    display: flex !important;
    flex-direction: row !important;
    align-items: center !important;
    gap: 6px !important;
    flex-shrink: 0 !important;
    height: 100% !important;
    padding: 0 4px !important;
}

/* BootStrap .navbar-right heredado */
.nav.navbar-nav.navbar-right {
    display: flex !important;
    flex-direction: row !important;
    align-items: center !important;
    float: none !important;
    margin: 0 !important;
    padding: 0 !important;
    height: 100% !important;
    flex-shrink: 0 !important;
}

.nav.navbar-nav.navbar-right > li {
    display: inline-flex !important;
    align-items: center !important;
    height: 100% !important;
    float: none !important;
}

/* RESPONSIVO MOVIL */
@media (max-width: 768px) {
    .navbar.navbar-default {
        height: auto !important;
        min-height: 60px !important;
        max-height: none !important;
        flex-wrap: wrap !important;
        overflow: visible !important;
        padding: 6px 8px !important;
    }
    .navbar.navbar-default .navbar-collapse.navbar-ex1-collapse {
        flex-wrap: wrap !important;
        max-height: none !important;
        overflow-y: auto !important;
    }
    .nav-slider-container {
        width: 100% !important;
        height: 80px !important;
        flex: none !important;
    }
    .nav.navbar-nav.navbar-right {
        width: 100% !important;
        justify-content: center !important;
    }
}
</style>

<audio id="player" src="<?php echo SGA_COMUN_URL . '/audio/fondo.mp3'; ?>"></audio>

<nav class='navbar navbar-default' role='navigation'>
    <!-- .navbar-header oculto por CSS; el toggle móvil sigue funcionando -->
    <div class="navbar-header">
        <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-ex1-collapse">
            <span class="sr-only">Desplegar navegación</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
        </button>
    </div>

    <div class="collapse navbar-collapse navbar-ex1-collapse">
        
        <!-- Contenedor del Slider -->
        <div class="nav-slider-container">
            <button class="nav-slider-btn" id="slider-prev" title="Desplazar izquierda">&#10094;</button>
            
            <div class="nav-slider-wrapper" id="nav-slider-wrapper">
                <ul class='nav navbar-nav slider-track' id="sortable-menu">
                    <li data-id="inicio"><a href="<?php echo SGA_URL; ?>/index.php"><span data-text="INICIO" class="icon-sga-house"></span></a></li>
                    
                    <?php if (isset($_SESSION['app']) && $_SESSION['app'] == "seguimiento"): ?>
                        <li data-id="seguimiento"><a href="<?php echo SGA_URL; ?>/index.php"><span data-text="Seguimiento" class="icon-sga-house"></span></a></li>
                    <?php endif; ?>

                    <?php if (isset($_SESSION['id_usuario'])): ?>
                        <li data-id="cursos"><a href="<?php echo SGA_URL; ?>/cursos"><span data-text="CURSOS" class="icon-sga-notebook"></span></a></li>
                        
                        <?php if (in_array($rol_actual, array("admin", "docente", "estudiante", "acudiente"))): ?>
                            <li data-id="red"><a title="Recursos Educativos Digitales" href="<?php echo SGA_URL; ?>/red"><span data-text="RED" class="icon-sga-app"></span></a></li>
                        <?php endif; ?>
                        
                        <?php if (in_array($rol_actual, array("admin", "docente"))): ?>
                            <li data-id="apps"><a  target="_blank" href="<?php echo SGA_URL; ?>/comun/gestor_menu.php"><span data-text="APPS" class="icon-sga-smartphone-7"></span></a></li>
                        <?php endif; ?>
                        
                        <li data-id="datos"><a href="<?php echo SGA_REPORTES_URL; ?>/informe_docente.php"><span data-text="Datos" class="icon-sga-time"></span></a></li>
                    <?php endif; ?>

                    <!-- Icono Configuración dentro del slider -->
                    <li data-id="config" class="dropdown">
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown" title="Configuración">
                            <span data-text="CONFIG" class="icon-sga-settings-4"></span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-right">
                            <?php if ($rol_actual == "docente" || $rol_actual == "admin" || empty($rol_actual)): ?>
                                <li><a target='_blank' href="<?php echo SGA_URL; ?>/apps/customizador_menu/index.php"><i class="fa-solid fa-palette"></i> Personalizar Menú (Live)</a></li>
                                <li><a target='_blank' href="<?php echo SGA_URL; ?>/comun/gestor_menu.php">Menú</a></li>
                                <li><a href="<?php echo SGA_URL; ?>/comun/dir_menu.php">Dir Menú</a></li>
                            <?php endif; ?>
                            <li><a href="#" id="btn-restaurar-menu"><i class="fa-solid fa-rotate-left"></i> Restaurar Menú</a></li>
                            <li><a href="<?php echo SGA_USUARIO_URL; ?>/login.php?logout">Salir</a></li>
                        </ul>
                    </li>

                    <?php
                    // Obtener ítems de cabecera dinámicamente
                    $sql_cabecera = "SELECT * FROM menu_items2 WHERE is_cabecera = 1 AND fav = 1 ORDER BY menu_item_id ASC";
                    $res_cabecera = $mysqli->query($sql_cabecera);
                    if ($res_cabecera && $res_cabecera->num_rows > 0) {
                        while ($item = $res_cabecera->fetch_assoc()) {
                            $url = (strpos($item['menu_url'], 'http') === 0) ? $item['menu_url'] : SGA_URL . $item['menu_url'];
                            ?>
                            <li data-id="dynamic_<?php echo $item['menu_item_id']; ?>"><a title="<?php echo htmlspecialchars($item['menu_item_name']); ?>" target="<?php echo htmlspecialchars($item['url_target']); ?>" href="<?php echo $url; ?>"><span data-text="<?php echo htmlspecialchars($item['menu_item_name']); ?>" class="<?php echo htmlspecialchars($item['icono']); ?>"></span></a></li>
                            <?php
                        }
                    }
                    ?>

                    <?php if (isset($_SESSION['modulo']) && $_SESSION['modulo'] == "red"): ?>
                        <li data-id="red_modulo"><a href="<?php echo SGA_URL; ?>/red/index.php"><span class="icon-sga-app" data-text="RED"></span></a></li>
                    <?php endif; ?>
                </ul>
            </div>
            
            <button class="nav-slider-btn" id="slider-next" title="Desplazar derecha">&#10095;</button>
        </div>
        <!-- Fin del Slider -->

        <?php if ($barra_busqueda == "actividad_curso"): ?>
            <buscar class="navbar-form navbar-right" role="search" method="post">
                <div class="form-group">
                    <?php if (isset($_GET['asignacion'])) $_SESSION['asigna'] = $_GET['asignacion']; ?>
                    <select class="form-control" onchange="PlaceholderBusquedaActividades();" id="menu_actividad" name="campo_red">
                        <option value="nombre_actividad">Nombre</option>
                        <option value="Observaciones">Observaciones</option>
                        <option value="adjunto">Adjunto</option>
                        <option value="periodo">Periodo</option>
                        <option value="visible">Visible</option>
                        <option value="evaluable">Evaluable</option>
                        <option value="fecha_entrega">Fecha Entrega</option>
                        <option value="cuestionario">Cuestionario</option>
                        <option value="foro">Foro</option>
                    </select>
                    <input type="hidden" id="asigna" value="<?php echo isset($_SESSION['asigna']) ? $_SESSION['asigna'] : ''; ?>" />
                    <input type="search" class="form-control" id="actividad_curso" name="texto" placeholder="Eje:Taller 1" onfocus="buscar_actividad_curso(this.value)" onchange="buscar_actividad_curso(this.value)" onkeyup="buscar_actividad_curso(this.value)">
                </div>
            </buscar>
        <?php endif; ?>

        <?php if ($archivo_actual === 'mis_cursos.php' && $barra_busqueda === "cursos"): ?>
            <buscar style='margin-top: 4%;margin-right: 2%;' class="navbar-form navbar-right" role="search" method="post">
                <div class="form-group">
                    <select onchange="buscar_mis_cursos();" title="Seleccione criterio de búsqueda" style="z-index:101;position:relative" class="form-control" id="campo_cursos" name="campo_cursos">
                        <option value="nombre_materia">Materia</option>
                        <option value="nombre_docente">Docente</option>
                        <option value="nombre_categoria">Categoría</option>
                        <option value="anio">Año</option>
                        <option value="todos">Todos</option>
                    </select>
                </div>
                <div style="margin-right:50px;" class="form-group">
                    <input autofocus type="search" class="form-control input-xs" id="buscarcurso" name="texto" placeholder="Buscar" onkeyup="buscar_mis_cursos(this.value)">
                </div>
            </buscar>
        <?php endif; ?>

        <!-- ZONA DERECHA: engranaje config + foto usuario -->
        <ul class="nav navbar-nav navbar-right">
            <?php if (isset($_SESSION['id_usuario'])): ?>
                <script>
                    if (typeof notificar_mensajes === 'function') {
                        notificar_mensajes();
                    }
                </script>
                <!-- Foto de usuario circular -->
                <?php if ($persona): ?>
                <li style="display:flex;align-items:center;justify-content:center;height:100%;padding:0 8px;">
                    <div id="estilo_foto_usuario_menu" class="estilos_fotos"
                         <?php if (isset($_SESSION['hijo']) && $rol_actual == "acudiente") { echo "style='text-align:center'"; } ?>>
                        <?php
                        if (isset($_SESSION['hijo']) && $rol_actual == "acudiente") {
                            $sql = 'SELECT * FROM `usuario` WHERE `id_usuario` = "' . $_SESSION["hijo"] . '"';
                            $consulta = $mysqli->query($sql);
                            if ($row = $consulta->fetch_assoc()) { ?>
                                <img onclick="document.location.href='<?php echo SGA_USUARIO_URL; ?>/perfil.php'"
                                     title="<?php echo $persona->nombre . " " . $persona->apellido; ?>"
                                     id="foto_usuario_hijo" src="<?php echo READFILE_URL . "/foto/" . ($row['foto']); ?>" width="50%">
                            <?php }
                        }
                        ?>
                        <img onclick="document.location.href='<?php echo SGA_USUARIO_URL; ?>/perfil.php'"
                             title="<?php echo $persona->nombre . " " . $persona->apellido; ?>"
                             id="foto_usuario" src="<?php echo READFILE_URL . "/foto/" . ($persona->foto); ?>"
                             width="<?php echo (isset($_SESSION['hijo']) && $rol_actual == "acudiente") ? '50%' : '100%'; ?>">
                        <span <?php if (isset($persona->rol) && $persona->rol == "acudiente") { echo 'style="margin-left:-65px!important;"'; } ?>
                              <?php if (count(explode(",", $persona->rol)) > 1): ?> data-toggle="modal" data-target="#myModal_roles" <?php endif; ?>
                              id="area_rol"><?php echo $rol_actual; ?></span>
                    </div>
                </li>
                <?php endif; ?>
            <?php else: ?>
                <li style="display:flex;align-items:center;">
                    <a href="<?php echo SGA_USUARIO_URL; ?>/login.php" style="display:flex;align-items:center;gap:6px;">
                        <span class="glyphicon glyphicon-log-in"></span> Iniciar Sesión
                    </a>
                </li>
            <?php endif; ?>
        </ul>

        <?php if ($barra_busqueda == "foros"): ?>
            <buscar class="navbar-form navbar-right" role="search" method="post">
                <div class="form-group">
                    <select class="form-control" name="criterio" onchange="document.getElementById('buscar_foro').type=this.options[this.selectedIndex].getAttribute('data-fn');">
                        <option data-fn="text" value="tema_foro">Tema</option>
                        <option data-fn="date" value="fecha">Fecha</option>
                        <option data-fn="text" value="usuario">Usuario</option>
                    </select>
                </div>
                <div class="form-group">
                    <input id="buscar_foro" type="text" class="form-control" name="texto" placeholder="Buscar">
                </div>
            </buscar>
        <?php endif; ?>

        <?php if ($barra_busqueda == "mensajes"): ?>
            <buscar class="navbar-form navbar-right" role="search" method="post">
                <div class="form-group">
                    <input class="form-control" type="search" id="txt_buscar_mensaje" onkeyup="this.change();" onchange="buscar_mensaje(this.value);" placeholder="Buscar">
                    <input class="form-control" type="number" min="0" max="30" id="numeroresultados_mensaje" placeholder="Cantidad de resultados" title="Cantidad de resultados por página" value="<?php echo isset($_COOKIE['numeroresultados_mensaje']) ? $_COOKIE['numeroresultados_mensaje'] : '5'; ?>" onkeyup="this.change();" mousewheel="this.change();" onchange="grabarcookie('numeroresultados_mensaje',this.value);buscar_mensaje(this.value);" size="4" style="width: 60px;">
                </div>
            </buscar>
            <script>
                $('#numeroresultados_mensaje').tooltip();
            </script>
        <?php endif; ?>

        <?php if ($barra_busqueda == "red"): ?>
            <buscar class="navbar-form navbar-right" role="search" method="post">
                <div class="form-group">
                    <select class="form-control" id="campo_red" name="campo_red">
                        <option value="titulo_Red">Nombre</option>
                        <option value="nombre_materia">Materia</option>
                        <option value="palabras_clave">Palabras clave</option>
                        <option value="nivel_eductivo">Nivel educativo</option>
                        <option value="descripcion">Descripción</option>
                        <option value="nombre">Responsable</option>
                        <option value="scorm">Scorm</option>
                        <option value="formato">Formato</option>
                        <option value="adjunto">Adjunto</option>
                        <option value="dificultad">Dificultad</option>
                        <option value="cantidad_estrellas">Cantidad estrellas</option>
                        <option value="fecha">Fecha</option>
                        <option value="id_red">Id</option>
                        <option value="idioma_red">Idioma red</option>
                        <option value="autor">Autor</option>
                        <option value="tipo_interacción">Tipo interacción</option>
                        <option value="tipo_recurso_educativo">Tipo recurso educativo</option>
                    </select>
                    <input onclick="this.value='';focus();" onfocus="buscar_red_ajax(this.value);" class="form-control" type="search" id="txt_buscar_red" onkeyup="buscar_red_ajax(this.value);" placeholder="Buscar" autofocus>
                </div>
            </buscar>
        <?php endif; ?>

        <?php if ($barra_busqueda == "cuestionarios"): ?>
            <buscar class="navbar-form navbar-right" role="search" method="post">
                <div class="form-group">
                    <input class="form-control" type="search" placeholder="Buscar... Ejemplo: Taller" id="txt_buscar_cuestionario" onkeyup="buscar_cuestionario_pag();" style="margin: 15px;" value="">
                    <input class="form-control" type="number" min="0" max="16" id="numeroresultados_cuesionario" placeholder="Cantidad de resultados" title="Cantidad de resultados" value="<?php echo isset($_COOKIE['numeroresultados_cuesionario']) ? $_COOKIE['numeroresultados_cuesionario'] : '8'; ?>" onkeyup="grabarcookie('numeroresultados_cuesionario',this.value);buscar_cuestionario_pag();" mousewheel="grabarcookie('numeroresultados_cuesionario',this.value);buscar_cuestionario_pag();" onchange="grabarcookie('numeroresultados_cuesionario',this.value);buscar_cuestionario_pag();" size="4" style="width: 60px;">
                </div>
            </buscar>
            <script>
                $('#numeroresultados_cuesionario').tooltip();
            </script>
        <?php endif; ?>
    </div>
</nav>

<script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
<script>
    window.onload = function() {
        if (typeof verificar_sonido === 'function') {
            verificar_sonido();
        }
    };

    document.addEventListener('DOMContentLoaded', function() {
        const wrapper = document.getElementById('nav-slider-wrapper');
        const btnPrev = document.getElementById('slider-prev');
        const btnNext = document.getElementById('slider-next');
        const scrollAmount = 150; 

        if (wrapper && btnPrev && btnNext) {
            btnPrev.addEventListener('click', function() {
                wrapper.scrollBy({ left: -scrollAmount, behavior: 'smooth' });
            });

            btnNext.addEventListener('click', function() {
                wrapper.scrollBy({ left: scrollAmount, behavior: 'smooth' });
            });
        }

        // Configuración de SortableJS para Drag and Drop
        const menuEl = document.getElementById('sortable-menu');
        if (menuEl) {
            // 0. Ocultar elementos desfijados
            const hiddenItems = JSON.parse(localStorage.getItem('sgaMenuHidden')) || [];
            
            // 1. Restaurar el orden guardado
            const savedOrder = localStorage.getItem('sgaMenuOrder');
            const items = Array.from(menuEl.children);
            
            if (savedOrder) {
                const orderArray = JSON.parse(savedOrder);
                orderArray.forEach(id => {
                    const item = items.find(el => el.getAttribute('data-id') === id);
                    if (item) {
                        menuEl.appendChild(item); // Lo mueve al final, respetando el orden guardado
                    }
                });
            }

            // Aplicar ocultamiento
            Array.from(menuEl.children).forEach(item => {
                const id = item.getAttribute('data-id');
                if (hiddenItems.includes(id)) {
                    item.style.display = 'none';
                }

                // Lógica de "Long Press" (sostenido) para desfijar
                let pressTimer;
                item.addEventListener('pointerdown', (e) => {
                    if(e.button !== 0) return; // solo clic izquierdo o touch
                    pressTimer = window.setTimeout(() => {
                        if(confirm('¿Deseas desfijar esta opción del menú? Puedes restaurarla desde tu perfil.')) {
                            let currentHidden = JSON.parse(localStorage.getItem('sgaMenuHidden')) || [];
                            if(!currentHidden.includes(id)) {
                                currentHidden.push(id);
                                localStorage.setItem('sgaMenuHidden', JSON.stringify(currentHidden));
                            }
                            item.style.display = 'none';
                        }
                    }, 800); // 800ms = long press
                });
                
                const clearTimer = () => clearTimeout(pressTimer);
                item.addEventListener('pointerup', clearTimer);
                item.addEventListener('pointerleave', clearTimer);
                item.addEventListener('pointermove', clearTimer); // Al arrastrar se cancela el long press
            });

            // 2. Inicializar Sortable
            new Sortable(menuEl, {
                animation: 150,
                delay: 150, // Pequeño retraso para que los clics normales funcionen en móviles y no cruce con el long press
                delayOnTouchOnly: true, // Sólo aplica el retraso en dispositivos táctiles
                ghostClass: 'sortable-ghost',
                onEnd: function (evt) {
                    // Guardar nuevo orden al soltar
                    const newOrder = Array.from(menuEl.children).map(el => el.getAttribute('data-id')).filter(id => id);
                    localStorage.setItem('sgaMenuOrder', JSON.stringify(newOrder));
                }
            });
        }
        
        // Botón Restaurar
        const btnRestaurar = document.getElementById('btn-restaurar-menu');
        if(btnRestaurar) {
            btnRestaurar.addEventListener('click', (e) => {
                e.preventDefault();
                if(confirm('¿Restaurar el orden y los íconos ocultos del menú a su estado original?')) {
                    localStorage.removeItem('sgaMenuOrder');
                    localStorage.removeItem('sgaMenuHidden');
                    location.reload();
                }
            });
        }
    });
</script>

<?php if (isset($_SESSION['id_usuario']) && $persona): ?>
    <?php if (count(explode(",", $persona->rol)) > 1): ?>
        <div id="myModal_roles" class="modal fade" role="dialog">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <h4 class="modal-title"><label for="roles">Roles:</label></h4>
                    </div>
                    <div style="height:30%!important;" class="modal-body modal-lg">
                        <?php
                        $roles = explode(",", $persona->rol);
                        foreach ($roles as $rol):
                        ?>
                            <span>
                                <?php if ($rol == $rol_actual): ?>
                                    <img title="Mi Rol actual: <?php echo isset($array_roles[$rol]) ? $array_roles[$rol] : $rol; ?>" name="<?php echo isset($array_roles[$rol]) ? $array_roles[$rol] : $rol; ?>" width="130px;" src="<?php echo SGA_COMUN_IMAGES . '/png/' . (isset($array_roles[$rol]) ? $array_roles[$rol] : $rol) . '.jpg'; ?>">
                                    <span class="roles"><?php echo isset($array_roles[$rol]) ? $array_roles[$rol] : $rol; ?></span>
                                <?php else: ?>
                                    <a title="Cambiar Rol a <?php echo isset($array_roles[$rol]) ? $array_roles[$rol] : $rol; ?>" href="<?php echo SGA_USUARIO_URL; ?>/perfil.php?redirect=<?php echo $_SERVER['PHP_SELF']; ?>&cambiar_rol=<?php echo $rol; ?>">
                                        <img name="<?php echo isset($array_roles[$rol]) ? $array_roles[$rol] : $rol; ?>" width="120px;" src="<?php echo SGA_COMUN_IMAGES . '/png/' . (isset($array_roles[$rol]) ? $array_roles[$rol] : $rol) . '.jpg'; ?>" />
                                    </a>
                                    <span class="roles"><?php echo isset($array_roles[$rol]) ? $array_roles[$rol] : $rol; ?></span>
                                <?php endif; ?>
                            </span>
                        <?php endforeach; ?>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
<?php endif; ?>