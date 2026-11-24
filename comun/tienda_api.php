<?php
declare(strict_types=1);
require_once __DIR__ . '/seguridad.php';
require_once __DIR__ . '/database.php';
require_once __DIR__ . '/lib/GuaguaAppStore.php';
require_once __DIR__ . '/tienda_permisos.php';
require_once __DIR__ . '/lib/GuaguaMenuRepository.php';

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
guagua_exigir_rol(guagua_tienda_roles_gestion());
$canManage = guagua_tienda_puede_gestionar((string)($_SESSION['rol'] ?? ''));
header('Cache-Control: no-store, private');
header('X-Content-Type-Options: nosniff');
if (!in_array($method, ['GET', 'POST'], true)) {
    header('Allow: GET, POST');
    guagua_error_acceso(405, 'Método no permitido.');
}
if ($method === 'POST') guagua_exigir_csrf();
$input = $method === 'POST' ? json_decode((string)file_get_contents('php://input'), true) : $_GET;
if (!is_array($input)) guagua_error_acceso(400, 'Solicitud no válida.');
$action = $input['action'] ?? 'catalog';
$id = $input['id'] ?? '';
if (!is_string($action) || !is_string($id)) guagua_error_acceso(400, 'Solicitud no válida.');
if ($method === 'GET' && !in_array($action, ['catalog', 'details', 'menu_list'], true)) guagua_error_acceso(405, 'Esta operación requiere POST.');
session_write_close();

require_once __DIR__ . '/tienda_respaldo.php';

try {
    $store = new GuaguaAppStore(dirname(__DIR__));
    $menuRepository = new GuaguaMenuRepository(guagua_db_request());
    $menu = $menuRepository->items();
    if (in_array($action, ['backup', 'install', 'uninstall'], true)) set_time_limit(0);
    switch ($action) {
        case 'catalog': $result = ['apps'=>$store->catalog($menu), 'zip'=>class_exists('ZipArchive'), 'manage'=>$canManage]; break;
        case 'details': $result = ['app'=>$store->details($id, $menu)]; break;
        case 'menu_list': $result = ['items'=>$menu, 'icons'=>$menuRepository->icons()]; break;
        case 'menu_save':
            if (!is_array($input['item'] ?? null)) throw new InvalidArgumentException('El acceso no es válido.');
            $saved = $menuRepository->save($input['item'], $id);
            $result = ['items'=>$menuRepository->items(), 'id'=>$saved, 'message'=>'Acceso guardado. El menú y las favoritas ya están actualizados.']; break;
        case 'menu_flag':
            if (!is_string($input['field'] ?? null)) throw new InvalidArgumentException('El estado no es válido.');
            $menuRepository->setFlag($id, $input['field'], $input['value'] ?? null);
            $result = ['items'=>$menuRepository->items(), 'message'=>'Menú actualizado.']; break;
        case 'menu_delete':
            $menuRepository->delete($id);
            $result = ['items'=>$menuRepository->items(), 'message'=>'Acceso retirado del menú. La aplicación sigue instalada.']; break;
        case 'menu_sync':
            if (!is_array($input['ids'] ?? null)) throw new InvalidArgumentException('La selección no es válida.');
            $added = $menuRepository->sync($input['ids'], $store);
            $result = ['items'=>$menuRepository->items(), 'message'=>$added . ' aplicaciones añadidas al menú. Puedes marcarlas como favoritas o anclarlas a la cabecera.']; break;
        case 'install': $result = $store->install($id); break;
        case 'uninstall': $result = $store->uninstall($id, $menu); break;
        case 'backup':
            $file = $store->backup($id, $menu, 'guagua_tienda_exportar_sql');
            try {
                header('Content-Type: application/zip');
                header('Content-Disposition: attachment; filename="guagua-app-' . $id . '-' . date('Ymd-His') . '.zip"');
                header('Content-Length: ' . filesize($file));
                readfile($file);
            } finally { @unlink($file); }
            exit;
        default: guagua_error_acceso(400, 'Operación no válida.');
    }
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['status'=>'success'] + $result, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
} catch (Throwable $error) {
    error_log('[Guagua tienda] ' . $error->getMessage());
    $message = ($error instanceof RuntimeException || $error instanceof InvalidArgumentException) && !($error instanceof mysqli_sql_exception)
        ? $error->getMessage() : 'No se pudo completar la operación. Revisa el almacenamiento y la conexión a la base de datos.';
    guagua_error_acceso(400, $message);
}
