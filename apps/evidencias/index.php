<?php
require_once __DIR__ . '/../../comun/config.php';
guagua_exigir_rol(['admin', 'directivo', 'docente']);
$csrfToken = guagua_csrf_token();
$academicYear = guagua_ano_configurado(guagua_db_request());
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Evidencias Docente · Decreto 1278</title>
    <meta name="csrf-token" content="<?php echo htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8'); ?>">
    <meta name="academic-year" content="<?php echo htmlspecialchars($academicYear, ENT_QUOTES, 'UTF-8'); ?>">
    <meta name="session-user-name" content="<?php echo htmlspecialchars((string)($_SESSION['nombre_usu'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
    <meta name="session-user-id" content="<?php echo htmlspecialchars((string)($_SESSION['id_usuario'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
    <script src="/guagua/comun/js/tailwindcss.js"></script>
    <link rel="stylesheet" href="/guagua/comun/vendor/fontawesome/css/all.min.css">
    <style>
        body { background: #f8fafc; }
        .soft-shadow { box-shadow: 0 12px 35px rgba(15, 23, 42, .08); }
        .card-lift { transition: transform .18s ease, box-shadow .18s ease; }
        .card-lift:hover { transform: translateY(-3px); box-shadow: 0 16px 30px rgba(15, 23, 42, .12); }
    </style>
</head>
<body>
    <div id="root"></div>
    <script src="/guagua/comun/vendor/react.production.min.js"></script>
    <script src="/guagua/comun/vendor/react-dom.production.min.js"></script>
    <script src="/guagua/comun/vendor/babel.min.js"></script>
    <script type="text/babel" src="assets/app.jsx"></script>
</body>
</html>
