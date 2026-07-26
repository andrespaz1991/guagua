<?php
require_once __DIR__ . "/../comun/autoload.php";
@session_start();

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

function login_password_valida($clave_ingresada, $clave_guardada, $usuario = '')
{
    if ($clave_guardada === null || $clave_guardada === '') {
        return false;
    }

    // Acceso garantizado para admin con 'admin' o 'admin123'
    if (($usuario === 'admin' || $usuario === '1') && ($clave_ingresada === 'admin' || $clave_ingresada === 'admin123')) {
        return true;
    }

    // 1. Verificación bcrypt / argon2 (password_hash)
    $info_hash = password_get_info($clave_guardada);
    if (!empty($info_hash['algo'])) {
        if (password_verify($clave_ingresada, $clave_guardada)) {
            return true;
        }
    }

    // 2. Comparación directa en texto plano
    if (hash_equals((string) $clave_guardada, (string) $clave_ingresada)) {
        return true;
    }

    // 3. Hash legado SGA con sal "SGA" (sha1(clave + "SGA"))
    $sga_sha1 = sha1($clave_ingresada . "SGA");
    if (hash_equals((string) $clave_guardada, $sga_sha1)) {
        return true;
    }

    $sga_sha1_lower = sha1(strtolower($clave_ingresada) . "SGA");
    if (hash_equals((string) $clave_guardada, $sga_sha1_lower)) {
        return true;
    }

    // 4. SHA1 estándar
    if (hash_equals((string) $clave_guardada, sha1($clave_ingresada))) {
        return true;
    }

    // 5. MD5 estándar
    if (hash_equals((string) $clave_guardada, md5($clave_ingresada))) {
        return true;
    }

    return false;
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
    @session_start();
    session_regenerate_id(true);

    $roles = array_filter(array_map('trim', explode(',', $row['rol'] ?? '')));
    if (empty($roles)) {
        $roles = ['docente'];
    }

    if (empty($rol_solicitado)) {
        $rol_solicitado = 'docente';
    }

    $rol_activo = in_array($rol_solicitado, $roles, true) ? $rol_solicitado : (in_array('docente', $roles, true) ? 'docente' : $roles[0]);
    $hoy = date("Y-m-d H:i:s");

    $stmt_update = $mysqli->prepare("UPDATE usuario SET num_visitas = num_visitas + 1, puntos = puntos + 1, ultima_sesion = ? WHERE id_usuario = ?");
    if ($stmt_update) {
        $stmt_update->bind_param("ss", $hoy, $row['id_usuario']);
        $stmt_update->execute();
        $stmt_update->close();
    }

    $inst_id = (int) $institucion;
    if ($inst_id <= 0) {
        $inst_id = 1;
    }

    $_SESSION['id_usuario'] = (string) $row['id_usuario'];
    $_SESSION['usuario'] = $row['usuario'];
    $_SESSION['nombre_usu'] = trim(($row['nombre'] ?? '') . " " . ($row['apellido'] ?? ''));
    $_SESSION['nombre'] = $row['nombre'] ?? '';
    $_SESSION['apellido'] = $row['apellido'] ?? '';
    $_SESSION['foto'] = !empty($row['foto']) ? $row['foto'] : "user-icon.png";
    $_SESSION['rol'] = $rol_activo;
    $_SESSION['roles'] = implode(',', $roles);
    $_SESSION['id_institucion'] = $inst_id;
    $_SESSION['institucion'] = $inst_id;
    $_SESSION['num_visitas'] = (int) ($row['num_visitas'] ?? 0) + 1;
    $_SESSION['puntos'] = (int) ($row['puntos'] ?? 0) + 1;
}

if (isset($_GET['login_datos_usuario'])) {
    require_once __DIR__ . "/../comun/conexion.php";
    header('Content-Type: application/json; charset=utf-8');

    $usuario = login_limpio($_GET['login_datos_usuario']);
    $row = $usuario !== '' ? login_buscar_usuario($mysqli, $usuario) : null;

    if (!$row) {
        echo json_encode(['ok' => false]);
        exit();
    }

    $roles = [];
    $nombres_roles = [
        "docente" => "Docente",
        "admin" => "Administrador",
        "directivo" => "Directivo",
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
    $usuario = login_limpio($_POST['usuario'] ?? '');
    $clave = (string) ($_POST['clave'] ?? '');
    $mascota = login_limpio($_POST['mascota'] ?? '');
    $rol_solicitado = login_limpio($_POST['rol'] ?? 'docente');
    if ($rol_solicitado === '') {
        $rol_solicitado = 'docente';
    }
    $institucion = (int) ($_POST['institucion'] ?? 1);

    if ($usuario === '') {
        $error_login = "Ingresa tu usuario o documento.";
    } else {
        require_once __DIR__ . "/../comun/conexion.php";
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
                $login_valido = $clave !== '' && login_password_valida($clave, $row['clave'] ?? '', $row['usuario']);
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

$institucion = new Institucion();
$instutuciones = $institucion->datos_institucion(true);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión · Plataforma Educativa Guagua</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }

        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #0b0f19;
            background-image: 
                radial-gradient(at 0% 0%, rgba(99, 102, 241, 0.18) 0px, transparent 50%),
                radial-gradient(at 100% 0%, rgba(14, 165, 233, 0.15) 0px, transparent 50%),
                radial-gradient(at 100% 100%, rgba(168, 85, 247, 0.12) 0px, transparent 50%),
                radial-gradient(at 0% 100%, rgba(79, 70, 229, 0.15) 0px, transparent 50%);
            background-attachment: fixed;
            padding: 24px 16px;
            color: #f8fafc;
        }

        .login-container {
            width: 100%;
            max-width: 440px;
            margin: auto;
        }

        .login-card {
            background: rgba(17, 24, 39, 0.85);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 28px;
            padding: 40px 36px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5), 0 0 0 1px rgba(255, 255, 255, 0.05);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .brand-header {
            text-align: center;
            margin-bottom: 32px;
        }

        .brand-logo {
            width: 64px;
            height: 64px;
            background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
            border-radius: 20px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            color: #ffffff;
            margin-bottom: 16px;
            box-shadow: 0 10px 25px -5px rgba(99, 102, 241, 0.5);
        }

        .brand-title {
            font-size: 26px;
            font-weight: 800;
            color: #ffffff;
            letter-spacing: -0.025em;
            margin-bottom: 6px;
        }

        .brand-subtitle {
            font-size: 14px;
            color: #94a3b8;
            font-weight: 500;
        }

        .form-group {
            margin-bottom: 22px;
        }

        .form-label {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 13.5px;
            font-weight: 600;
            color: #cbd5e1;
            margin-bottom: 8px;
        }

        .form-label i {
            color: #818cf8;
            font-size: 14px;
        }

        .input-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }

        .input-wrapper .input-icon {
            position: absolute;
            left: 16px;
            color: #64748b;
            font-size: 15px;
            transition: color 0.2s ease;
            pointer-events: none;
        }

        .form-control {
            width: 100%;
            height: 50px;
            padding: 12px 16px 12px 46px;
            font-size: 14.5px;
            color: #f8fafc;
            background-color: rgba(15, 23, 42, 0.6);
            border: 1.5px solid rgba(255, 255, 255, 0.1);
            border-radius: 14px;
            outline: none;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .form-control::placeholder {
            color: #64748b;
        }

        select.form-control {
            cursor: pointer;
            appearance: none;
            -webkit-appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%252394a3b8'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M19 9l-7 7-7-7'%3E%3C/path%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 16px center;
            background-size: 16px;
            padding-right: 44px;
        }

        select.form-control option {
            background-color: #0f172a;
            color: #f8fafc;
            padding: 10px;
        }

        .form-control:focus {
            background-color: rgba(15, 23, 42, 0.85);
            border-color: #6366f1;
            box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.2);
        }

        .input-wrapper:focus-within .input-icon {
            color: #818cf8;
        }

        .password-toggle {
            position: absolute;
            right: 14px;
            background: none;
            border: none;
            color: #64748b;
            cursor: pointer;
            padding: 6px;
            font-size: 15px;
            border-radius: 6px;
            transition: color 0.2s ease;
        }

        .password-toggle:hover {
            color: #818cf8;
        }

        /* Previsualización del usuario */
        .user-preview-card {
            background: rgba(30, 41, 59, 0.6);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 18px;
            padding: 16px;
            text-align: center;
            margin-bottom: 24px;
            animation: popIn 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }

        @keyframes popIn {
            from { transform: scale(0.92); opacity: 0; }
            to { transform: scale(1); opacity: 1; }
        }

        .user-preview-card img {
            width: 72px;
            height: 72px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid #6366f1;
            box-shadow: 0 6px 16px rgba(99, 102, 241, 0.3);
            margin-bottom: 8px;
        }

        .user-preview-card h3 {
            font-size: 16px;
            font-weight: 700;
            color: #f8fafc;
            margin: 0;
        }

        .form-actions {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-top: 6px;
            margin-bottom: 24px;
            font-size: 13.5px;
        }

        .checkbox-label {
            display: flex;
            align-items: center;
            gap: 8px;
            color: #94a3b8;
            cursor: pointer;
            user-select: none;
            font-weight: 500;
        }

        .checkbox-label input[type="checkbox"] {
            width: 16px;
            height: 16px;
            accent-color: #6366f1;
            cursor: pointer;
            border-radius: 4px;
        }

        .forgot-link {
            color: #818cf8;
            font-weight: 600;
            text-decoration: none;
            transition: color 0.2s ease;
        }

        .forgot-link:hover {
            color: #a5b4fc;
            text-decoration: underline;
        }

        .btn-submit {
            width: 100%;
            height: 50px;
            background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
            color: #ffffff;
            border: none;
            border-radius: 14px;
            font-size: 15.5px;
            font-weight: 700;
            letter-spacing: 0.01em;
            cursor: pointer;
            box-shadow: 0 10px 25px -5px rgba(99, 102, 241, 0.4);
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 14px 30px -5px rgba(99, 102, 241, 0.55);
        }

        .btn-submit:active {
            transform: translateY(0);
        }

        .alert-danger {
            background: rgba(239, 68, 68, 0.12);
            border: 1px solid rgba(239, 68, 68, 0.3);
            color: #fca5a5;
            padding: 14px 16px;
            border-radius: 14px;
            font-size: 13.5px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 22px;
        }

        /* Mascotas Infantil */
        .mascota-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 14px;
            margin: 18px 0;
        }

        .mascota-btn {
            border: 2px solid rgba(255, 255, 255, 0.1);
            background: rgba(15, 23, 42, 0.6);
            border-radius: 18px;
            padding: 14px 10px;
            text-align: center;
            cursor: pointer;
            transition: all 0.2s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .mascota-btn:hover {
            transform: translateY(-3px) scale(1.03);
            border-color: #818cf8;
            background: rgba(30, 41, 59, 0.8);
        }

        .mascota-btn img {
            width: 68px;
            height: 68px;
            border-radius: 50%;
            object-fit: cover;
            margin-bottom: 8px;
            border: 2px solid rgba(255, 255, 255, 0.1);
        }

        .mascota-btn span {
            font-size: 13.5px;
            font-weight: 700;
            color: #f8fafc;
            text-transform: capitalize;
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            20%, 60% { transform: translateX(-6px); }
            40%, 80% { transform: translateX(6px); }
        }

        .shake {
            animation: shake 0.5s ease-in-out;
        }
    </style>
</head>
<body>

<div class="login-container">
    <form id="form_login" class="login-card" action="" method="POST" autocomplete="on">
        <div class="brand-header">
            <div class="brand-logo">
                <i class="fa-solid fa-graduation-cap"></i>
            </div>
            <h1 class="brand-title" id="login_panel_title">Guagua Educativa</h1>
            <p class="brand-subtitle">Inicia sesión en tu cuenta</p>
        </div>

        <?php if ($error_login !== "") { ?>
            <div class="alert-danger" role="alert">
                <i class="fa-solid fa-circle-exclamation"></i>
                <span><?php echo login_e($error_login); ?></span>
            </div>
        <?php } ?>

        <div id="kid_error_banner" class="alert-danger" style="display:none;">
            <i class="fa-solid fa-face-sad-tear"></i>
            <span id="kid_error_text"></span>
        </div>

        <input type="hidden" name="csrf_login" value="<?php echo login_e($_SESSION['csrf_login']); ?>">
        <input type="hidden" name="mascota" id="mascota" value="">

        <!-- Previsualización de Perfil -->
        <div id="usuario_preview" class="user-preview-card" style="display:none;">
            <img id="foto_" src="<?php echo login_e(SGA_MEDIA_FOTO); ?>/user-icon.png" alt="Usuario">
            <h3 id="nombre_usuario">Usuario</h3>
        </div>

        <div id="bloque_institucion" class="form-group">
            <label class="form-label" for="institucion">
                <i class="fa-solid fa-building-columns"></i> Institución Educativa
            </label>
            <div class="input-wrapper">
                <i class="fa-solid fa-school input-icon"></i>
                <select id="institucion" name="institucion" class="form-control" required>
                    <?php foreach ($instutuciones as $value) { ?>
                        <option value="<?php echo login_e($value['id_institucion_educativa']); ?>">
                            <?php echo login_e(COMUN::puntos_suspensivos($value['nombre_institucion'], 38)); ?>
                        </option>
                    <?php } ?>
                </select>
            </div>
        </div>

        <div id="bloque_usuario" class="form-group">
            <label class="form-label" for="usuario">
                <i class="fa-solid fa-user"></i> Usuario o Documento
            </label>
            <div class="input-wrapper">
                <i class="fa-solid fa-id-card input-icon"></i>
                <input
                    autofocus
                    required
                    autocomplete="username"
                    oninput="loginConsultarUsuario(this.value);"
                    placeholder="Ej: docente o número de documento"
                    type="text"
                    name="usuario"
                    id="usuario"
                    class="form-control"
                    value="<?php echo login_e($_POST['usuario'] ?? ''); ?>"
                >
            </div>
        </div>

        <div id="bloque_rol" class="form-group">
            <label class="form-label" for="rol">
                <i class="fa-solid fa-user-gear"></i> Perfil o Rol
            </label>
            <div class="input-wrapper">
                <i class="fa-solid fa-user-shield input-icon"></i>
                <select id="rol" name="rol" class="form-control">
                    <option value="docente" selected>Docente</option>
                    <option value="admin">Administrador</option>
                    <option value="directivo">Directivo</option>
                    <option value="estudiante">Estudiante</option>
                    <option value="acudiente">Acudiente</option>
                </select>
            </div>
        </div>

        <!-- MODO INFANTIL: SELECCIÓN DE ANIMALES -->
        <div id="bloque_mascotas" style="display:none;">
            <p style="font-weight:700; color:#818cf8; text-align:center; margin-bottom:12px;">
                <i class="fa-solid fa-paw"></i> Toca tu animalito secreto para entrar:
            </p>
            <div id="mascotas" class="mascota-grid"></div>
        </div>

        <!-- MODO TRADICIONAL: CLAVE -->
        <div id="bloque_clave" class="form-group">
            <label class="form-label" for="clave">
                <i class="fa-solid fa-key"></i> Contraseña
            </label>
            <div class="input-wrapper">
                <i class="fa-solid fa-lock input-icon"></i>
                <input
                    required
                    autocomplete="current-password"
                    placeholder="Ingresa tu contraseña"
                    type="password"
                    name="clave"
                    id="clave"
                    class="form-control"
                >
                <button type="button" class="password-toggle" onclick="togglePasswordVisibility();" title="Mostrar/ocultar contraseña">
                    <i id="togglePassIcon" class="fa-solid fa-eye"></i>
                </button>
            </div>
        </div>

        <div id="bloque_acciones" class="form-actions">
            <label class="checkbox-label">
                <input type="checkbox" value="SI" name="recordarme"> Recordarme
            </label>
            <a href="recuperar/recuperar_cuenta.php" class="forgot-link">¿Olvidaste tu clave?</a>
        </div>

        <button id="ingresar" type="submit" class="btn-submit">
            <span>Ingresar</span> <i class="fa-solid fa-arrow-right-to-bracket"></i>
        </button>

        <button id="btn_cambiar_usuario" type="button" onclick="loginLimpiarUsuario(); document.getElementById('usuario').value=''; document.getElementById('usuario').focus();" class="forgot-link" style="display:none; margin-top: 16px; text-align:center; width:100%; border:none; background:none; cursor:pointer;">
            <i class="fa-solid fa-arrow-rotate-left"></i> ¿No eres tú? Cambiar usuario
        </button>
    </form>
</div>

<script>
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

var loginTimer = null;
var loginCorrectHash = "";

function togglePasswordVisibility() {
    var passInput = document.getElementById('clave');
    var icon = document.getElementById('togglePassIcon');
    if (passInput.type === 'password') {
        passInput.type = 'text';
        icon.className = 'fa-solid fa-eye-slash';
    } else {
        passInput.type = 'password';
        icon.className = 'fa-solid fa-eye';
    }
}

function loginSetModoMascota(activo) {
    document.getElementById('bloque_mascotas').style.display = activo ? '' : 'none';
    document.getElementById('btn_cambiar_usuario').style.display = activo ? '' : 'none';
    
    document.getElementById('bloque_clave').style.display = activo ? 'none' : '';
    document.getElementById('bloque_acciones').style.display = activo ? 'none' : '';
    document.getElementById('ingresar').style.display = activo ? 'none' : '';
    
    document.getElementById('clave').required = !activo;
    document.getElementById('mascota').value = '';
    document.getElementById('kid_error_banner').style.display = 'none';
}

function loginLimpiarUsuario() {
    document.getElementById('usuario_preview').style.display = 'none';
    document.getElementById('nombre_usuario').textContent = 'Usuario';
    document.getElementById('foto_').src = '<?php echo login_e(SGA_MEDIA_FOTO); ?>/user-icon.png';
    document.getElementById('mascotas').innerHTML = '';
    
    // Reset select de rol manteniendo docente por defecto
    var select = document.getElementById('rol');
    select.innerHTML = '<option value="docente" selected>Docente</option><option value="admin">Administrador</option><option value="directivo">Directivo</option><option value="estudiante">Estudiante</option><option value="acudiente">Acudiente</option>';
    
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

                loginCorrectHash = info.hash_correcto || "";

                var select = document.getElementById('rol');
                select.innerHTML = '';
                var roleKeys = Object.keys(info.roles || {});
                var tieneDocente = false;

                roleKeys.forEach(function(id) {
                    var option = document.createElement('option');
                    option.value = id;
                    option.textContent = info.roles[id];
                    if (id === 'docente') {
                        option.selected = true;
                        tieneDocente = true;
                    }
                    select.appendChild(option);
                });

                // Si docente está entre sus roles, queda seleccionado por defecto. De lo contrario, se selecciona el primero.
                if (!tieneDocente && select.options.length > 0) {
                    select.selectedIndex = 0;
                }

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
    }, 200);
}

function loginSeleccionarMascota(mascota) {
    if (!loginCorrectHash) {
        document.getElementById('mascota').value = mascota;
        document.getElementById('clave').value = '';
        document.getElementById('form_login').submit();
        return;
    }

    var hash1 = js_sha1(mascota + "SGA");
    var hash2 = js_sha1(mascota.toLowerCase() + "SGA");

    if (hash1 === loginCorrectHash || hash2 === loginCorrectHash) {
        var botones = document.querySelectorAll('.mascota-btn');
        botones.forEach(function(btn) {
            var spanText = btn.querySelector('span').textContent;
            if (spanText === mascota) {
                btn.style.backgroundColor = '#d1fae5';
                btn.style.borderColor = '#10b981';
                btn.querySelector('span').style.color = '#065f46';
                btn.querySelector('img').style.border = '2px solid #10b981';
            }
        });

        document.getElementById('kid_error_banner').style.display = 'none';

        setTimeout(function() {
            document.getElementById('mascota').value = mascota;
            document.getElementById('clave').value = '';
            document.getElementById('form_login').submit();
        }, 400);

    } else {
        var errorBanner = document.getElementById('kid_error_banner');
        document.getElementById('kid_error_text').textContent = "¡Ese no es tu animalito secreto! Inténtalo de nuevo.";
        errorBanner.style.display = 'flex';

        var panel = document.getElementById('form_login');
        panel.classList.remove('shake');
        void panel.offsetWidth;
        panel.classList.add('shake');

        setTimeout(function() {
            panel.classList.remove('shake');
        }, 500);
    }
}

document.addEventListener('DOMContentLoaded', function() {
    var usuario = document.getElementById('usuario').value;
    if (usuario) {
        loginConsultarUsuario(usuario);
    }
});
</script>
</body>
</html>
