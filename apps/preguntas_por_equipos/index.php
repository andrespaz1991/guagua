<?php
require_once __DIR__ . '/../../comun/seguridad.php';
guagua_iniciar_sesion_segura();
if (empty($_SESSION['id_usuario'])) {
    header('Location: ../../usuario/login.php');
    exit;
}
guagua_exigir_rol(['admin', 'directivo', 'docente']);
header('Cache-Control: private, no-store');
$storageScope = hash('sha256', (string)$_SESSION['id_usuario'] . ':' . (string)($_SESSION['id_institucion'] ?? ''));
require __DIR__ . '/view.php';
