<?php

class Curso extends Academico{

            public $id_curso;

            public $id_asignatura;

            public $id_docente;

            public $ano_lectivo;

            public $id_asignacion;

            public $descripcion;

            public $id_categoria_curso;

            public $visible;

            public $portada_asignacion;

            public $institucion_educativa;

            public $id_materia;

            public $nombre_materia;

            public $descripcion_materia;

            public $obligatoria;

            public $area;

            public $icono_materia;

            public $id_usuario;

            public $usuario;

            public $clave;

            public $mascota;

            public $nombre;

            public $apellido;

            public $rol;

            public $foto;

            public $direccion;

            public $telefono;

            public $correo;

            public $ultima_sesion;

            public $num_visitas;

            public $puntos;

            public $estado;

            public $tipo_sangre;

            public $genero;

            public $observaciones;
            public $icono_asignacion;
            public $asistencia ;
            public $institucion ;
            public $fecha_nacimiento;

public function __SET($atributo,$valor){

  return  $this-> $atributo= $valor ;

}

public function __construct($id_asignatura=""){

    if ($id_asignatura!=""){

        $this->id_asignatura = $id_asignatura;

    $this->informacion_curso();

  }

}

public function informacion_curso($todos=0){

	$sql='select * from asignacion,materia,usuario where 

asignacion.id_asignatura =materia.id_materia and

asignacion.id_docente = usuario.id_usuario 

and asignacion.id_asignacion ="'.$this->id_asignatura.'"';
$datos = json_decode($this->consultar_datos($sql,true),true);

if($todos==1)
{
return $datos;
}else{
foreach ($datos as $clave => $value) {
foreach($value as $clave2 => $value2){
  $this->__SET($clave2,$value2); 
  
}
  
   
}

}

}



public function todas_categoria_curso(){

  $sql='select * from categoria_curso ';

 return $datos = json_decode($this->consultar_datos($sql,true),true);

}



public function categoria_curso(){

  $sql='select * from categoria_curso ';

if(!empty($this->id_categoria_curso)){

$sql.=' where id_categoria_curso="'.$this->id_categoria_curso.'" ';

}

 $datos = json_decode($this->consultar_datos($sql,true),true);

  return $datos[0]['nombre_categoria_curso'];

}

public function deadeline_curso($asignacion){

$hoy =date('Y-m-d');

#$hoy =date('2020-05-06');

$horarios= ($this->consultar_horario_simple($asignacion));
if(!empty($horarios[0]['fecha_inicio'])){
  $fecha_inicio=$horarios[0]['fecha_inicio'];
 $fecha_fin=$horarios[0]['fecha_fin'];
if($hoy<=$fecha_fin){
 $fecha_inicio;
 $hoy;
$a = Fecha::diferencia_fecha($fecha_inicio,$fecha_fin);
$b = Fecha::diferencia_fecha($fecha_inicio,$hoy);
$resultado=round((($b)*100)/($a),2);
}else{
  $resultado="100";
}
return $resultado;
}
}

 





public function consultar_link_icono($icono){

$sql="SELECT imagen_icono FROM `iconos` WHERE id_iconos=$icono";

   $datos = json_decode($this->consultar_datos($sql,true),true);



    if (!empty($datos)){

    foreach ($datos as $key => $row ){

        return SGA_COMUN_URL."/img/png/".$row['imagen_icono'];

    }        

    }else{

        return SGA_COMUN_URL."/img/png/folder-10.png";

    }

}

public function listar_cursos_home_tarjetas($termino = '') {
    $datos_curso = $this->mis_cursos_otros();
    $rol = $_SESSION['rol'] ?? 'invitado';
    $termino = trim((string)$termino);
    $termino_busqueda = function_exists('mb_strtolower')
        ? mb_strtolower($termino, 'UTF-8')
        : strtolower($termino);
    $html = '';

    foreach ($datos_curso as $datos_materia) {
        $nombre_materia_raw = (string)($datos_materia['nombre_materia'] ?? 'Sin materia');
        $categoria_raw = (string)($datos_materia['mid_categoria_curso'] ?? 'Sin grado');
        $descripcion_raw = (string)($datos_materia['descripcion'] ?? '');
        $texto = $nombre_materia_raw . ' ' . $categoria_raw . ' ' . $descripcion_raw;
        $texto_busqueda = function_exists('mb_strtolower') ? mb_strtolower($texto, 'UTF-8') : strtolower($texto);
        if ($termino_busqueda !== '' && strpos($texto_busqueda, $termino_busqueda) === false) {
            continue;
        }

        $id_asignacion = (int)($datos_materia['id_asignacion'] ?? 0);
        if ($id_asignacion <= 0) {
            continue;
        }

        $nombre_materia = htmlspecialchars($nombre_materia_raw, ENT_QUOTES, 'UTF-8');
        $descripcion = htmlspecialchars($descripcion_raw, ENT_QUOTES, 'UTF-8');
        $categoria = htmlspecialchars($categoria_raw, ENT_QUOTES, 'UTF-8');
        $icono_asignacion = trim((string)($datos_materia['icono_asignacion'] ?? ''));
        if (preg_match('/^[a-z0-9_-]+\.(?:png|jpe?g|gif|webp|svg)$/i', $icono_asignacion)) {
            $icono = 'comun/img/png/' . $icono_asignacion;
        } else {
            $icono = 'comun/img/png/folder-10.png';
        }
        $icono = htmlspecialchars($icono, ENT_QUOTES, 'UTF-8');
        // La tarjeta se pinta en /index.php, por lo que las rutas relativas
        // evitan URL malformadas cuando la instalación vive en un subdirectorio.
        $url_curso = htmlspecialchars('cursos/curso.php?asignacion=' . $id_asignacion, ENT_QUOTES, 'UTF-8');
        $url_planeacion = htmlspecialchars('apps/PlanMind/index2.php?asignacion=' . $id_asignacion, ENT_QUOTES, 'UTF-8');
        $url_horario = htmlspecialchars('asistencia/horario.php?asignacion=' . $id_asignacion . '&modificar=1', ENT_QUOTES, 'UTF-8');
        $url_modificar = htmlspecialchars('cursos/modificar_curso.php?asignacion=' . $id_asignacion, ENT_QUOTES, 'UTF-8');
        $visible = strtolower(trim((string)($datos_materia['visible'] ?? 'si'))) === 'no' ? 'no' : 'si';
        $texto_visibilidad = $visible === 'si' ? 'Ocultar curso' : 'Mostrar curso';

        $html .= '<article class="curso-home-card" data-asignacion="' . $id_asignacion . '">';
        $html .= '<div class="dropdown-context-menu">';
        $html .= '<button type="button" class="btn-contextual" data-menu="menu-curso-home-' . $id_asignacion . '" aria-label="Opciones de ' . $nombre_materia . '" aria-expanded="false">&#8942;</button>';
        $html .= '<ul id="menu-curso-home-' . $id_asignacion . '" class="context-menu-list" role="menu">';
        $html .= '<li role="none"><a role="menuitem" href="' . $url_curso . '">Entrar al curso</a></li>';
        $html .= '<li role="none"><a role="menuitem" href="' . $url_planeacion . '">Nueva planeación</a></li>';
        $html .= '<li role="none"><a role="menuitem" href="' . $url_horario . '">Configurar horario</a></li>';
        if ($rol === 'admin' || $rol === 'docente') {
            $html .= '<li role="none" class="context-menu-separator"><button type="button" role="menuitem" class="control-visibilidad-btn" data-id="' . $id_asignacion . '" data-visible="' . $visible . '">' . $texto_visibilidad . '</button></li>';
            $html .= '<li role="none"><a role="menuitem" href="' . $url_modificar . '">Modificar curso</a></li>';
        }
        $html .= '</ul></div>';
        $html .= '<a class="curso-home-link enlace_sin_estilo" title="' . $descripcion . '" href="' . $url_curso . '">';
        $html .= '<strong>' . $nombre_materia . '</strong><span class="curso-home-grado">' . $categoria . '</span>';
        $html .= '<img title="Imagen del curso" class="imagen_tarjeta" src="' . $icono . '" alt="Icono de ' . $nombre_materia . '">';
        $html .= '</a></article>';
    }

    if ($html === '') {
        $mensaje = $termino === '' ? 'No tienes asignaciones activas para mostrar.' : 'No se encontraron asignaciones con esa búsqueda.';
        return '<p class="curso-home-empty">' . htmlspecialchars($mensaje, ENT_QUOTES, 'UTF-8') . '</p>';
    }

    return $html;
}

public function listar_cursos_home() {
    $endpoint = 'cursos/mis_cursos_home.php';
    echo '<style>
        .mis-cursos-home-search { margin-bottom: 12px; }
        .mis-cursos-home-search label { display:block; margin-bottom:5px; font-size:12px; font-weight:600; }
        .mis-cursos-home-search input { width:100%; padding:9px 10px; border:1px solid #ccd6e0; border-radius:6px; font-size:14px; box-sizing:border-box; }
        .mis-cursos-home-status { display:block; min-height:18px; margin-top:4px; font-size:12px; color:#667085; }
        .mis-cursos-home { position:relative; z-index:1; }
        .curso-home-card { position:relative; padding:12px; border:1px solid #dbe3ea; border-radius:8px; margin-bottom:10px; background:#fff; transition:box-shadow .2s ease, transform .2s ease; }
        .curso-home-card.context-open { z-index:2100; }
        .curso-home-card:hover { box-shadow:0 4px 12px rgba(15, 23, 42, .12); transform:translateY(-1px); }
        .curso-home-link { display:grid; grid-template-columns:minmax(0,1fr) auto; align-items:center; gap:4px 10px; color:#263238; text-decoration:none; padding-right:28px; }
        .curso-home-link strong { overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
        .curso-home-grado { font-size:12px; color:#667085; }
        .curso-home-link .imagen_tarjeta { grid-column:1 / -1; max-width:72px; max-height:55px; height:auto; object-fit:contain; margin-top:4px; }
        .dropdown-context-menu { position:absolute; top:7px; right:5px; }
        .btn-contextual { background:none; border:0; border-radius:4px; color:#475467; font-size:22px; line-height:1; cursor:pointer; padding:3px 7px; }
        .btn-contextual:hover, .btn-contextual:focus { background:#eef2f6; outline:none; }
        .context-menu-list { display:none; position:absolute; right:0; top:30px; min-width:175px; z-index:2200; margin:0; padding:5px 0; list-style:none; background:#fff; border:1px solid #d0d5dd; border-radius:7px; box-shadow:0 8px 18px rgba(15,23,42,.16); }
        .context-menu-list.is-open { display:block; }
        .context-menu-list.context-menu-floating { display:block !important; position:fixed; right:auto; top:auto; z-index:9999; }
        .context-menu-list a, .context-menu-list button { display:block; width:100%; padding:8px 12px; border:0; background:transparent; color:#344054; text-align:left; text-decoration:none; font-size:13px; cursor:pointer; }
        .context-menu-list a:hover, .context-menu-list button:hover { background:#f2f4f7; color:#1d4ed8; }
        .context-menu-separator { border-top:1px solid #eaecf0; margin-top:4px; padding-top:4px; }
        .curso-home-empty { margin:12px 0 0; color:#667085; font-size:13px; }
        .home-card-widget.course-context-open { position:relative; z-index:2100 !important; }
    </style>';
    echo '<section class="mis-cursos-home" data-search-endpoint="' . $endpoint . '">';
    echo '<div class="mis-cursos-home-search"><label for="buscador-cursos-home">Buscar asignación</label><input type="search" id="buscador-cursos-home" autocomplete="off" placeholder="Materia, grado o descripción"><small class="mis-cursos-home-status" aria-live="polite"></small></div>';
    echo '<div class="contenedor-lista-cursos-home">' . $this->listar_cursos_home_tarjetas() . '</div>';
    echo '</section>';
    ?>
    <script>
    (() => {
        const root = document.querySelector('.mis-cursos-home');
        if (!root || root.dataset.initialized === 'true') return;
        root.dataset.initialized = 'true';
        const input = root.querySelector('#buscador-cursos-home');
        const results = root.querySelector('.contenedor-lista-cursos-home');
        const status = root.querySelector('.mis-cursos-home-status');
        const panelBody = root.closest('.panel-body.tarjeta');
        const homeWidget = root.closest('.home-card-widget');
        let timer = null;
        let controller = null;
        const menusFlotantes = new Set();

        const closeMenus = () => {
            const menus = new Set([
                ...root.querySelectorAll('.context-menu-list.is-open'),
                ...menusFlotantes
            ]);
            menus.forEach(menu => {
                menu.classList.remove('is-open', 'context-menu-floating');
                menu.style.removeProperty('left');
                menu.style.removeProperty('top');
                if (menu.__menuHomeMarca && menu.__menuHomeMarca.parentNode) {
                    menu.__menuHomeMarca.parentNode.replaceChild(menu, menu.__menuHomeMarca);
                }
                delete menu.__menuHomeMarca;
            });
            menusFlotantes.clear();
            root.querySelectorAll('.btn-contextual[aria-expanded="true"]').forEach(button => button.setAttribute('aria-expanded', 'false'));
            root.querySelectorAll('.curso-home-card.context-open').forEach(card => card.classList.remove('context-open'));
            if (homeWidget) homeWidget.classList.remove('course-context-open');
        };

        const posicionarMenu = (menu, button) => {
            const contenedorOriginal = menu.parentNode;
            const marca = document.createComment('menu-contextual-curso');
            contenedorOriginal.insertBefore(marca, menu);
            menu.__menuHomeMarca = marca;
            document.body.appendChild(menu);
            menusFlotantes.add(menu);
            menu.classList.add('is-open', 'context-menu-floating');
            const boton = button.getBoundingClientRect();
            const anchoMenu = menu.offsetWidth;
            const altoMenu = menu.offsetHeight;
            const margen = 8;
            const izquierda = Math.max(margen, Math.min(boton.right - anchoMenu, window.innerWidth - anchoMenu - margen));
            const arriba = Math.max(margen, Math.min(boton.bottom + 4, window.innerHeight - altoMenu - margen));
            menu.classList.add('context-menu-floating');
            menu.style.left = `${izquierda}px`;
            menu.style.top = `${arriba}px`;
        };
        const search = async () => {
            if (controller) controller.abort();
            controller = new AbortController();
            status.textContent = 'Buscando asignaciones…';
            try {
                const url = new URL(root.dataset.searchEndpoint, window.location.href);
                url.searchParams.set('q', input.value.trim());
                const response = await fetch(url, { headers: { Accept: 'application/json' }, signal: controller.signal, cache: 'no-store' });
                const data = await response.json();
                if (!response.ok || !data.ok) throw new Error(data.message || 'No fue posible buscar las asignaciones.');
                results.innerHTML = data.html;
                status.textContent = data.total === 1 ? '1 asignación encontrada.' : `${data.total} asignaciones encontradas.`;
            } catch (error) {
                if (error.name === 'AbortError') return;
                status.textContent = error.message || 'No fue posible buscar las asignaciones.';
            }
        };

        input.addEventListener('input', () => {
            window.clearTimeout(timer);
            timer = window.setTimeout(search, 220);
        });
        root.addEventListener('click', event => {
            const button = event.target.closest('.btn-contextual');
            if (!button) return;
            event.preventDefault();
            const menu = document.getElementById(button.dataset.menu);
            const isOpen = menu && menu.classList.contains('is-open');
            closeMenus();
            if (menu && !isOpen) {
                posicionarMenu(menu, button);
                button.setAttribute('aria-expanded', 'true');
                const card = button.closest('.curso-home-card');
                if (card) card.classList.add('context-open');
                if (homeWidget) homeWidget.classList.add('course-context-open');
            }
        });
        root.addEventListener('contextmenu', event => {
            const card = event.target.closest('.curso-home-card');
            if (!card) return;
            event.preventDefault();
            const button = card.querySelector('.btn-contextual');
            if (button) button.click();
        });
        document.addEventListener('click', event => {
            if (!root.contains(event.target) && !event.target.closest('.context-menu-list.context-menu-floating')) closeMenus();
        });
        if (panelBody) panelBody.addEventListener('scroll', closeMenus);
        window.addEventListener('resize', closeMenus);
    })();
    </script>
    <?php
}
}



?>
