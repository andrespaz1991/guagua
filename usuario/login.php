<?php
require_once("../comun/autoload.php");

if ($_SERVER['REQUEST_METHOD'] !== 'POST' && !isset($_GET['login_datos_usuario'])) {
    $_SESSION = [];
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params["path"], $params["domain"], $params["secure"], $params["httponly"]);
    }
    session_destroy();
    session_start();
}

$error_login = "";

if (empty($_SESSION['csrf_login'])) {
    $_SESSION['csrf_login'] = bin2hex(random_bytes(32));
}

function login_limpio($valor)
{
    return trim((string) $valor);
}

function login_e($valor)
{
    return htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');
}

function login_hash_mascota($mascota)
{
    return sha1($mascota . "SGA");
}

function login_mascota_valida($mascota, $clave_guardada)
{
    if ($mascota === '' || $clave_guardada === null || $clave_guardada === '') {
        return false;
    }

    $hashes_validos = [
        sha1($mascota . "SGA"),
        sha1(strtolower($mascota) . "SGA"),
    ];

    foreach ($hashes_validos as $hash) {
        if (hash_equals((string) $clave_guardada, $hash)) {
            return true;
        }
    }

    return false;
}

function login_password_valida($clave_ingresada, $clave_guardada)
{
    if ($clave_guardada === null || $clave_guardada === '') {
        return false;
    }

    $info_hash = password_get_info($clave_guardada);
    if (!empty($info_hash['algo'])) {
        return password_verify($clave_ingresada, $clave_guardada);
    }

    return hash_equals((string) $clave_guardada, (string) $clave_ingresada);
}

function login_buscar_usuario($mysqli, $usuario)
{
    $stmt = $mysqli->prepare("SELECT * FROM usuario WHERE (usuario = ? OR id_usuario = ?) AND estado = 'activo' LIMIT 1");
    $stmt->bind_param("ss", $usuario, $usuario);
    $stmt->execute();
    $resultado = $stmt->get_result();
    $row = $resultado ? $resultado->fetch_assoc() : null;
    $stmt->close();

    return $row;
}

function login_crear_sesion($mysqli, $row, $rol_solicitado, $institucion)
{
    session_regenerate_id(true);

    $roles = array_filter(array_map('trim', explode(',', $row['rol'] ?? '')));
    $rol_activo = in_array($rol_solicitado, $roles, true) ? $rol_solicitado : ($roles[0] ?? 'invitado');
    $hoy = date("Y-m-d H:i:s");

    $stmt_update = $mysqli->prepare("UPDATE usuario SET num_visitas = num_visitas + 1, puntos = puntos + 1, ultima_sesion = ? WHERE id_usuario = ?");
    $stmt_update->bind_param("ss", $hoy, $row['id_usuario']);
    $stmt_update->execute();
    $stmt_update->close();

    $_SESSION['id_usuario'] = $row['id_usuario'];
    $_SESSION['usuario'] = $row['usuario'];
    $_SESSION['nombre_usu'] = trim(($row['nombre'] ?? '') . " " . ($row['apellido'] ?? ''));
    $_SESSION['nombre'] = $row['nombre'] ?? '';
    $_SESSION['apellido'] = $row['apellido'] ?? '';
    $_SESSION['foto'] = $row['foto'] ?: "user-icon.png";
    $_SESSION['rol'] = $rol_activo;
    $_SESSION['roles'] = implode(',', $roles);
    $_SESSION['id_institucion'] = (int) $institucion;
    $_SESSION['num_visitas'] = (int) ($row['num_visitas'] ?? 0) + 1;
    $_SESSION['puntos'] = (int) ($row['puntos'] ?? 0) + 1;
}

if (isset($_GET['login_datos_usuario'])) {
    require("../comun/conexion.php");
    header('Content-Type: application/json; charset=utf-8');

    $usuario = login_limpio($_GET['login_datos_usuario']);
    $row = $usuario !== '' ? login_buscar_usuario($mysqli, $usuario) : null;

    if (!$row) {
        echo json_encode(['ok' => false]);
        exit();
    }

    $roles = [];
    $nombres_roles = [
        "admin" => "Administrador",
        "directivo" => "Directivo",
        "docente" => "Docente",
        "estudiante" => "Estudiante",
        "acudiente" => "Acudiente",
        "invitado" => "Invitado",
    ];

    foreach (array_filter(array_map('trim', explode(',', $row['rol'] ?? ''))) as $rol) {
        $roles[$rol] = $nombres_roles[$rol] ?? ucfirst($rol);
    }

    $mascotas = [];
    if (($row['mascota'] ?? '') === 'SI' && !empty($row['clave'])) {
        $stmt = $mysqli->prepare("SELECT figura, imagen_figura FROM figuras WHERE SHA1(CONCAT(figura, 'SGA')) = ? OR SHA1(CONCAT(LOWER(figura), 'SGA')) = ? LIMIT 1");
        $stmt->bind_param("ss", $row['clave'], $row['clave']);
        $stmt->execute();
        $correcta = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($correcta) {
            $mascotas[] = $correcta;
        }

        // Modificado LIMIT 4 a LIMIT 3 para mostrar exactamente el correcto + 3 distractores (Total 4)
        $stmt = $mysqli->prepare("SELECT figura, imagen_figura FROM figuras WHERE SHA1(CONCAT(figura, 'SGA')) <> ? AND SHA1(CONCAT(LOWER(figura), 'SGA')) <> ? ORDER BY figura LIMIT 3");
        $stmt->bind_param("ss", $row['clave'], $row['clave']);
        $stmt->execute();
        $resultado = $stmt->get_result();
        while ($fila = $resultado->fetch_assoc()) {
            $mascotas[] = $fila;
        }
        $stmt->close();

        shuffle($mascotas);
    }

    // Retorna el hash cifrado de la clave para validación directa segura en JS
    echo json_encode([
        'ok' => true,
        'nombre' => $row['nombre'] ?? '',
        'apellido' => $row['apellido'] ?? '',
        'foto' => SGA_MEDIA_FOTO . '/' . ($row['foto'] ?: 'user-icon.png'),
        'roles' => $roles,
        'mascota' => $row['mascota'] ?? 'NO',
        'mascotas' => $mascotas,
        'hash_correcto' => $row['clave'] ?? '',
    ], JSON_UNESCAPED_UNICODE);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf_form = $_POST['csrf_login'] ?? '';

    if (!hash_equals($_SESSION['csrf_login'] ?? '', $csrf_form)) {
        $error_login = "La sesion del formulario venció. Intenta nuevamente.";
    } else {
        $usuario = login_limpio($_POST['usuario'] ?? '');
        $clave = (string) ($_POST['clave'] ?? '');
        $mascota = login_limpio($_POST['mascota'] ?? '');
        $rol_solicitado = login_limpio($_POST['rol'] ?? '');
        $institucion = (int) ($_POST['institucion'] ?? 0);

        if ($usuario === '') {
            $error_login = "Ingresa tu usuario.";
        } else {
            require("../comun/conexion.php");
            $row = login_buscar_usuario($mysqli, $usuario);

            if (!$row) {
                $error_login = "Usuario o contraseña incorrecta.";
            } else {
                $login_valido = false;

                if (($row['mascota'] ?? '') === 'SI') {
                    $login_valido = login_mascota_valida($mascota, $row['clave'] ?? '');
                    if (!$login_valido) {
                        $error_login = $mascota === '' ? "Selecciona tu mascota clave." : "Mascota clave incorrecta.";
                    }
                } else {
                    $login_valido = $clave !== '' && login_password_valida($clave, $row['clave'] ?? '');
                    if (!$login_valido) {
                        $error_login = "Usuario o contraseña incorrecta.";
                    }
                }

                if ($login_valido) {
                    login_crear_sesion($mysqli, $row, $rol_solicitado, $institucion);

                    if (isset($_POST['recordarme']) && $_POST['recordarme'] === "SI") {
                        setcookie("usuarios[" . $row['id_usuario'] . "]", $row['usuario'], [
                            'expires' => time() + (86400 * 365),
                            'path' => '/',
                            'httponly' => true,
                            'samesite' => 'Lax',
                        ]);
                    }

                    header("Location: ../index.php");
                    exit();
                }
            }
        }
    }
}

$institucion = new Institucion();
$instutuciones = $institucion->datos_institucion(true);

ob_start();
?>
<style>
    .login-page {
        min-height: 70vh;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 32px 12px;
        background: #f4f7fb url('../comun/img/fondo_login.jpg') center/cover no-repeat;
    }
    .login-panel {
        width: min(520px, 100%);
        padding: 28px;
        background: rgba(255, 255, 255, 0.97);
        border: 1px solid #dde5ef;
        border-radius: 16px;
        box-shadow: 0 12px 35px rgba(15, 23, 42, 0.16);
        text-align: left;
        transition: all 0.3s ease;
    }
    
    /* Animación de vibración para errores de los niños */
    @keyframes shake {
        0%, 100% { transform: translateX(0); }
        10%, 30%, 50%, 70%, 90% { transform: translateX(-8px); }
        20%, 40%, 60%, 80% { transform: translateX(8px); }
    }
    .shake {
        animation: shake 0.6s cubic-bezier(.36,.07,.19,.97) both;
    }

    .login-panel h1 {
        margin: 0 0 18px;
        font-size: 26px;
        text-align: center;
        color: #1e293b;
    }
    .login-panel label {
        display: block;
        margin: 12px 0 6px;
        font-weight: 600;
        color: #475569;
    }
    .login-panel input,
    .login-panel select {
        width: 100%;
        border-radius: 8px;
    }
    
    /* Previsualización del estudiante */
    .login-user-preview {
        text-align: center;
        margin-bottom: 18px;
        animation: pop-in 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    }
    @keyframes pop-in {
        0% { transform: scale(0.8); opacity: 0; }
        100% { transform: scale(1); opacity: 1; }
    }
    
    .login-user-preview img {
        width: 100px;
        height: 100px;
        border-radius: 50%;
        object-fit: cover;
        border: 4px solid #6366f1;
        box-shadow: 0 4px 15px rgba(99, 102, 241, 0.25);
        display: inline-block;
        margin-bottom: 10px;
    }
    .login-user-preview h2 {
        font-size: 20px;
        font-weight: 800;
        color: #1e293b;
        margin: 0;
    }

    /* Grilla de animales lúdica */
    .mascota-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 16px;
        margin: 18px 0;
    }
    .mascota-btn {
        border: 2px solid #e2e8f0;
        background: #fff;
        border-radius: 16px;
        padding: 16px 12px;
        text-align: center;
        cursor: pointer;
        transition: all 0.2s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        display: flex;
        flex-direction: column;
        align-items: center;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
    }
    .mascota-btn:hover,
    .mascota-btn:focus {
        transform: translateY(-4px) scale(1.03);
        box-shadow: 0 12px 20px rgba(99, 102, 241, 0.15);
        border-color: #a5b4fc;
        outline: none;
    }
    .mascota-btn img {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        object-fit: cover;
        display: block;
        margin-bottom: 8px;
        border: 2px solid #f1f5f9;
        transition: transform 0.2s ease;
    }
    .mascota-btn:hover img {
        transform: rotate(5deg) scale(1.05);
    }
    .mascota-btn span {
        font-size: 14px;
        font-weight: 700;
        color: #475569;
        text-transform: capitalize;
    }
    
    /* Cartel lúdico de error infantil */
    .kid-error-banner {
        background-color: #fee2e2;
        border: 2px dashed #fecaca;
        color: #b91c1c;
        padding: 12px;
        border-radius: 12px;
        font-size: 13.5px;
        font-weight: 700;
        text-align: center;
        margin: 10px 0;
        display: none;
        animation: pop-in 0.3s ease-out;
    }
    
    .login-actions {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        margin-top: 18px;
    }
    .login-actions label {
        margin: 0;
        font-weight: normal;
    }
    .login-actions input {
        width: auto;
    }
    .login-help {
        color: #4f46e5;
        font-size: 14px;
        font-weight: 700;
        text-align: center;
        margin: 8px 0;
    }
    .login-error {
        margin-bottom: 14px;
    }
</style>

<div class="login-page">
    <form id="form_login" class="login-panel" action="" method="POST" autocomplete="on">
        <h1 class="Abckids" id="login_panel_title">Ingreso</h1>

        <?php if ($error_login !== "") { ?>
            <div class="alert alert-danger login-error" role="alert"><?php echo login_e($error_login); ?></div>
        <?php } ?>

        <!-- Cartelera lúdica infantil en Javascript -->
        <div id="kid_error_banner" class="kid-error-banner"></div>

        <input type="hidden" name="csrf_login" value="<?php echo login_e($_SESSION['csrf_login']); ?>">
        <input type="hidden" name="mascota" id="mascota" value="">

        <!-- Previsualización del usuario -->
        <div id="usuario_preview" class="login-user-preview" style="display:none">
            <img id="foto_" src="<?php echo login_e(SGA_MEDIA_FOTO); ?>/user-icon.png" alt="Usuario">
            <h2 id="nombre_usuario">Usuario</h2>
        </div>

        <div id="bloque_institucion">
            <label id="lb_institucion" for="institucion">Institución</label>
            <select id="institucion" name="institucion" required>
                <?php foreach ($instutuciones as $value) { ?>
                    <option value="<?php echo login_e($value['id_institucion_educativa']); ?>">
                        <?php echo login_e(COMUN::puntos_suspensivos($value['nombre_institucion'], 35)); ?>
                    </option>
                <?php } ?>
            </select>
        </div>

        <div id="bloque_usuario">
            <label id="user" for="usuario">Usuario</label>
            <input
                autofocus
                required
                autocomplete="username"
                oninput="loginConsultarUsuario(this.value);"
                placeholder="Escribe tu usuario"
                type="text"
                name="usuario"
                id="usuario"
                value="<?php echo login_e($_POST['usuario'] ?? ''); ?>"
            >
        </div>

        <div id="bloque_rol">
            <label id="ingresare" for="rol">Ingresaré como</label>
            <select id="rol" name="rol"></select>
        </div>

        <!-- MODO INFANTIL: SELECCIÓN DE ANIMALES -->
        <div id="bloque_mascotas" style="display:none">
            <p class="login-help">Toca tu animalito secreto para entrar:</p>
            <div id="mascotas" class="mascota-grid"></div>
        </div>

        <!-- MODO TRADICIONAL: CLAVE -->
        <div id="bloque_clave">
            <label id="labelclave" for="clave">Contraseña</label>
            <input required autocomplete="current-password" placeholder="Ingresa contraseña" type="password" name="clave" id="clave">
        </div>

        <!-- Acciones auxiliares -->
        <div id="bloque_acciones" class="login-actions">
            <label><input type="checkbox" value="SI" name="recordarme"> Recordarme</label>
            <a href="recuperar/recuperar_cuenta.php">Recuperar contraseña</a>
        </div>

        <button id="ingresar" type="submit" class="btn btn-success btn-block" style="margin-top:18px; border-radius: 8px; font-weight: bold;">Ingresar</button>

        <!-- Botón para retornar al modo clásico en el panel infantil -->
        <button id="btn_cambiar_usuario" type="button" onclick="loginLimpiarUsuario(); document.getElementById('usuario').value=''; document.getElementById('usuario').focus();" class="btn btn-link btn-block text-slate-500 font-bold" style="display:none; margin-top: 14px; text-decoration: none; font-size: 13px;">
            <i class="fa-solid fa-arrow-rotate-left"></i> ¿No eres tú? Cambiar de usuario
        </button>
    </form>
</div>

<script>
// ==========================================
// FUNCIÓN SHA1 EN JAVASCRIPT PURO Y SEGURO
// ==========================================
function js_sha1(str) {
    var hex_chr = "0123456789abcdef";
    function hex(num) {
        var str = "";
        for (var j = 7; j >= 0; j--)
            str += hex_chr.charAt((num >> (j * 4)) & 0x0F);
        return str;
    }
    function str2blks_SHA1(str) {
        var nblk = ((str.length + 8) >> 6) + 1, blks = new Array(nblk * 16);
        for (var i = 0; i < nblk * 16; i++) blks[i] = 0;
        for (var i = 0; i < str.length; i++)
            blks[i >> 2] |= str.charCodeAt(i) << (24 - (i % 4) * 8);
        blks[str.length >> 2] |= 0x80 << (24 - (str.length % 4) * 8);
        blks[nblk * 16 - 1] = str.length * 8;
        return blks;
    }
    function add(x, y) {
        var lsw = (x & 0xFFFF) + (y & 0xFFFF);
        var msw = (x >> 16) + (y >> 16) + (lsw >> 16);
        return (msw << 16) | (lsw & 0xFFFF);
    }
    function rol(num, cnt) {
        return (num << cnt) | (num >>> (32 - cnt));
    }
    function ft(t, b, c, d) {
        if (t < 20) return (b & c) | ((~b) & d);
        if (t < 40) return b ^ c ^ d;
        if (t < 60) return (b & c) | (b & d) | (c & d);
        return b ^ c ^ d;
    }
    function kt(t) {
        return (t < 20) ? 1518500249 : (t < 40) ? 1859775393 :
               (t < 60) ? -1894007588 : -899497514;
    }
    var x = str2blks_SHA1(str);
    var w = new Array(80);
    var a =  1732584193;
    var b = -271733879;
    var c = -1732584194;
    var d =  271733878;
    var e = -1009589776;
    for (var i = 0; i < x.length; i += 16) {
        var olda = a; var oldb = b; var oldc = c; var oldd = d; var olde = e;
        for (var j = 0; j < 80; j++) {
            if (j < 16) w[j] = x[i + j];
            else w[j] = rol(w[j-3] ^ w[j-8] ^ w[j-14] ^ w[j-16], 1);
            var t = add(add(rol(a, 5), ft(j, b, c, d)), add(add(e, w[j]), kt(j)));
            e = d; d = c; c = rol(b, 30); b = a; a = t;
        }
        a = add(a, olda); b = add(b, oldb); c = add(c, oldc); d = add(d, oldd); e = add(e, olde);
    }
    return hex(a) + hex(b) + hex(c) + hex(d) + hex(e);
}

// Variables globales de estado del login
var loginTimer = null;
var loginCorrectHash = "";

function loginSetModoMascota(activo) {
    document.getElementById('bloque_mascotas').style.display = activo ? '' : 'none';
    document.getElementById('btn_cambiar_usuario').style.display = activo ? '' : 'none';
    
    // Ocultar elementos clásicos si está activo el modo mascota infantil
    document.getElementById('bloque_usuario').style.display = activo ? 'none' : '';
    document.getElementById('bloque_institucion').style.display = activo ? 'none' : '';
    document.getElementById('bloque_rol').style.display = activo ? 'none' : '';
    document.getElementById('bloque_clave').style.display = activo ? 'none' : '';
    document.getElementById('bloque_acciones').style.display = activo ? 'none' : '';
    document.getElementById('ingresar').style.display = activo ? 'none' : '';
    document.getElementById('login_panel_title').style.display = activo ? 'none' : '';
    
    document.getElementById('clave').required = !activo;
    document.getElementById('mascota').value = '';
    document.getElementById('kid_error_banner').style.display = 'none';
}

function loginLimpiarUsuario() {
    document.getElementById('usuario_preview').style.display = 'none';
    document.getElementById('nombre_usuario').textContent = 'Usuario';
    document.getElementById('foto_').src = '<?php echo login_e(SGA_MEDIA_FOTO); ?>/user-icon.png';
    document.getElementById('rol').innerHTML = '';
    document.getElementById('mascotas').innerHTML = '';
    loginCorrectHash = "";
    loginSetModoMascota(false);
}

function loginConsultarUsuario(valor) {
    clearTimeout(loginTimer);
    valor = String(valor || '').trim();

    if (valor.length < 2) {
        loginLimpiarUsuario();
        return;
    }

    loginTimer = setTimeout(function() {
        fetch('login.php?login_datos_usuario=' + encodeURIComponent(valor), {
            credentials: 'same-origin'
        })
            .then(function(resp) { return resp.json(); })
            .then(function(info) {
                if (!info || !info.ok) {
                    loginLimpiarUsuario();
                    return;
                }

                document.getElementById('usuario_preview').style.display = '';
                document.getElementById('nombre_usuario').textContent = (info.nombre + ' ' + info.apellido).trim() || 'Usuario';
                document.getElementById('foto_').src = info.foto;

                // Almacenar el hash correcto
                loginCorrectHash = info.hash_correcto || "";

                var select = document.getElementById('rol');
                select.innerHTML = '';
                Object.keys(info.roles || {}).forEach(function(id) {
                    var option = document.createElement('option');
                    option.value = id;
                    option.textContent = info.roles[id];
                    select.appendChild(option);
                });

                var usarMascotas = info.mascota === 'SI' && Array.isArray(info.mascotas) && info.mascotas.length > 0;
                loginSetModoMascota(usarMascotas);

                var contenedor = document.getElementById('mascotas');
                contenedor.innerHTML = '';

                if (usarMascotas) {
                    info.mascotas.forEach(function(mascota) {
                        var boton = document.createElement('button');
                        boton.type = 'button';
                        boton.className = 'mascota-btn';
                        boton.onclick = function() {
                            loginSeleccionarMascota(mascota.figura);
                        };

                        var img = document.createElement('img');
                        img.src = '../comun/img/figuras/' + mascota.imagen_figura;
                        img.alt = mascota.figura;

                        var nombre = document.createElement('span');
                        nombre.textContent = mascota.figura;

                        boton.appendChild(img);
                        boton.appendChild(nombre);
                        contenedor.appendChild(boton);
                    });
                }
            })
            .catch(function() {
                loginLimpiarUsuario();
            });
    }, 220);
}

function loginSeleccionarMascota(mascota) {
    // Si no obtuvimos el hash por alguna razón, se envía de forma tradicional al servidor
    if (!loginCorrectHash) {
        document.getElementById('mascota').value = mascota;
        document.getElementById('clave').value = '';
        document.getElementById('form_login').submit();
        return;
    }

    // Hashear localmente con la sal "SGA"
    var hash1 = js_sha1(mascota + "SGA");
    var hash2 = js_sha1(mascota.toLowerCase() + "SGA");

    if (hash1 === loginCorrectHash || hash2 === loginCorrectHash) {
        // ¡EXCELENTE! Es el animal correcto
        // Mostrar animación de éxito en el botón clickeado
        var botones = document.querySelectorAll('.mascota-btn');
        botones.forEach(function(btn) {
            var spanText = btn.querySelector('span').textContent;
            if (spanText === mascota) {
                btn.style.backgroundColor = '#d1fae5'; // Verde suave
                btn.style.borderColor = '#10b981';
                btn.style.color = '#065f46';
                btn.querySelector('span').style.color = '#065f46';
                btn.querySelector('img').style.border = '2px solid #10b981';
            }
        });

        // Ocultar banner de error
        document.getElementById('kid_error_banner').style.display = 'none';

        // Pequeño delay lúdico para dar sensación de acierto y enviar
        setTimeout(function() {
            document.getElementById('mascota').value = mascota;
            document.getElementById('clave').value = '';
            document.getElementById('form_login').submit();
        }, 550);

    } else {
        // ¡EQUIVOCADO! Mostrar alerta infantil y vibrar
        var errorBanner = document.getElementById('kid_error_banner');
        errorBanner.textContent = "¡Equivocado! 🦁 Ese no es tu animalito secreto, ¡inténtalo otra vez!";
        errorBanner.style.display = 'block';

        // Vibrar el panel del formulario
        var panel = document.getElementById('form_login');
        panel.classList.remove('shake');
        void panel.offsetWidth; // Forzar reflow para reiniciar animación
        panel.classList.add('shake');

        // Quitar la clase de vibración al terminar
        setTimeout(function() {
            panel.classList.remove('shake');
        }, 600);
    }
}

function elegir_cuenta(usuario) {
    document.getElementById('usuario').value = usuario;
    loginConsultarUsuario(usuario);
}

function login_para_boy(datos) {
    var mascota = datos.getAttribute('data-figura') || datos.getAttribute('title') || '';
    if (mascota) {
        loginSeleccionarMascota(mascota);
    }
}

document.addEventListener('DOMContentLoaded', function() {
    var usuario = document.getElementById('usuario').value;
    if (usuario) {
        loginConsultarUsuario(usuario);
    }
});
</script>
<?php
$contenido = ob_get_contents();
ob_clean();
require("../comun/plantilla.php");
?>
