<?php
/** Vista común para catálogos heredados; las rutas solo declaran su configuración. */
require_once __DIR__ . '/seguridad.php';
guagua_exigir_rol($catalog['roles']);
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') guagua_exigir_csrf();
require_once __DIR__ . '/database.php';
require_once __DIR__ . '/catalog_repository.php';
ini_set('display_errors','0');
header('Cache-Control: no-store');
function catalog_e($value): string { return htmlspecialchars((string)$value,ENT_QUOTES|ENT_SUBSTITUTE,'UTF-8'); }
$message = ''; $error = ''; $editing = null;
$listing = ['rows'=>[],'total'=>0,'page'=>1,'pages'=>1];
$search = is_string($_GET['q'] ?? '') ? mb_substr(trim($_GET['q'] ?? ''),0,150) : '';
try {
    $db = guagua_db_mysqli();
    if (isset($_GET['export']) || isset($_GET['xls'])) {
        $result = catalog_export($db,$catalog,$_SESSION,$search);
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $catalog['table'] . '.csv"');
        $output = fopen('php://output','wb');
        fwrite($output,"\xEF\xBB\xBF");
        $fields = array_unique([$catalog['key'],...array_keys($catalog['fields'])]);
        fputcsv($output,array_map(static fn($field)=>$catalog['fields'][$field] ?? $field,$fields),';');
        session_write_close();
        while ($row=$result->fetch_assoc()) fputcsv($output,array_map('catalog_csv_cell',array_map(static fn($field)=>$row[$field],$fields)),';');
        fclose($output); exit;
    }
    if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
        $original = $_POST['original'] ?? '';
        if (!is_string($original)) throw new InvalidArgumentException('Identificador inválido.');
        if (($_POST['action'] ?? '') === 'delete') {
            catalog_delete($db,$catalog,$_SESSION,$original);
            $message = 'Registro eliminado.';
        } elseif (($_POST['action'] ?? '') === 'save') {
            catalog_save($db,$catalog,$_SESSION,$_POST,$original!=='' ? $original : null);
            header('Location: ' . strtok($_SERVER['REQUEST_URI'],'?') . '?saved=1',true,303); exit;
        } else throw new InvalidArgumentException('Operación inválida.');
    }
    if (isset($_GET['edit']) && is_string($_GET['edit'])) {
        $editing = catalog_find($db,$catalog,$_SESSION,$_GET['edit']);
        if (!$editing) throw new InvalidArgumentException('Registro no disponible para su cuenta.');
    }
    if (isset($_GET['saved'])) $message='Registro guardado.';
    $page = filter_var($_GET['page'] ?? 1,FILTER_VALIDATE_INT) ?: 1;
    $listing = catalog_list($db,$catalog,$_SESSION,$search,$page);
} catch (InvalidArgumentException $exception) { $error=$exception->getMessage(); }
catch (Throwable $exception) {
    error_log('[Guagua catálogo] ' . $catalog['table'] . ' ' . get_class($exception) . ' (' . $exception->getCode() . ')');
    $error='No se pudo procesar el catálogo. Revise su disponibilidad y los datos ingresados.';
}
$token = guagua_csrf_token();
?>
<!doctype html><html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title><?= catalog_e($catalog['title']) ?> · Guagua</title>
<style>
:root{font-family:system-ui,sans-serif;color:#172033;background:#f1f5f9}body{margin:0;padding:24px}main{max-width:1180px;margin:auto}header{display:flex;align-items:center;justify-content:space-between;gap:20px;margin-bottom:24px}h1{margin:4px 0;font-size:28px}.eyebrow{font-size:12px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:.12em}.grid{display:grid;grid-template-columns:minmax(270px,340px) 1fr;gap:20px}.panel{background:white;border:1px solid #e2e8f0;border-radius:16px;padding:24px;box-shadow:0 8px 30px #17203305}label{display:block;font-size:13px;font-weight:650;margin:14px 0 7px}input,textarea,button{box-sizing:border-box;font:inherit;font-size:14px;border:1px solid #cbd5e1;border-radius:9px;padding:10px}input,textarea{width:100%;background:#fff;color:inherit}input:focus,textarea:focus{outline:2px solid #93c5fd;outline-offset:1px}textarea{min-height:96px;resize:vertical}button,.btn{cursor:pointer;border:1px solid #cbd5e1;background:#fff;color:#334155;text-decoration:none;border-radius:9px;padding:10px 14px;font:inherit;font-size:14px}.primary{background:#2563eb;color:#fff;border-color:#2563eb}.danger{color:#b91c1c}.actions{display:flex;gap:8px;align-items:center;margin-top:18px}.search{display:flex;gap:8px;align-items:center;margin-bottom:18px}.search input{min-width:0}.scroll{overflow:auto}table{width:100%;border-collapse:collapse;font-size:13px}th,td{text-align:left;padding:12px;border-bottom:1px solid #e2e8f0;vertical-align:top}th{color:#64748b;font-size:11px;text-transform:uppercase}td{max-width:260px;overflow-wrap:anywhere}td form{display:inline}.row-actions{white-space:nowrap}.notice{padding:12px 16px;border-radius:10px;margin-bottom:16px;background:#dcfce7;color:#166534}.error{background:#fee2e2;color:#991b1b}.muted{font-size:13px;color:#64748b;line-height:1.6}.pagination{display:flex;justify-content:space-between;align-items:center;margin-top:18px;gap:12px}.pagination a{color:#1d4ed8}.row-actions button,.row-actions a{padding:6px;font-size:12px}@media(max-width:850px){.grid{grid-template-columns:1fr}body{padding:16px}.panel{padding:18px}header{align-items:flex-start}h1{font-size:24px}}
</style></head><body><main><header><div><span class="eyebrow">Guagua · Administración</span><h1><?= catalog_e($catalog['title']) ?></h1><p class="muted">Gestión de registros con búsqueda y paginación.</p></div><a class="btn" href="/guagua/index.php">Inicio</a></header>
<?php if ($message): ?><p class="notice" role="status"><?= catalog_e($message) ?></p><?php endif; ?>
<?php if ($error): ?><p class="notice error" role="alert"><?= catalog_e($error) ?></p><?php endif; ?>
<div class="grid"><section class="panel"><h2><?= $editing ? 'Editar registro' : 'Nuevo registro' ?></h2>
<?php if (isset($catalog['help'])): ?><p class="muted"><?= catalog_e($catalog['help']) ?></p><?php endif; ?>
<form method="post"><input type="hidden" name="csrf_token" value="<?= catalog_e($token) ?>"><input type="hidden" name="action" value="save"><input type="hidden" name="original" value="<?= catalog_e($editing[$catalog['key']] ?? '') ?>">
<?php foreach ($catalog['fields'] as $field=>$label): $value=$editing[$field] ?? ''; $type=$field==='fecha' ? 'date' : (str_starts_with($field,'hora_') ? 'time' : 'text'); ?>
<label for="field-<?= catalog_e($field) ?>"><?= catalog_e($label) ?></label>
<?php if (in_array($field,['objetivos','contenido','descripcion','descripcion_recurso','permisos'],true)): ?><textarea id="field-<?= catalog_e($field) ?>" name="<?= catalog_e($field) ?>" <?= $field===array_key_first($catalog['fields']) ? 'required' : '' ?>><?= catalog_e($value) ?></textarea>
<?php else: ?><input id="field-<?= catalog_e($field) ?>" name="<?= catalog_e($field) ?>" type="<?= $type ?>" value="<?= catalog_e($value) ?>" <?= $field===array_key_first($catalog['fields']) || $type!=='text' ? 'required' : '' ?>><?php endif; ?>
<?php endforeach; ?>
<div class="actions"><button class="primary">Guardar</button><?php if ($editing): ?><a class="btn" href="<?= catalog_e(strtok($_SERVER['REQUEST_URI'],'?')) ?>">Cancelar</a><?php endif; ?></div></form></section>
<section class="panel"><form class="search" method="get"><input type="search" name="q" value="<?= catalog_e($search) ?>" maxlength="150" placeholder="Buscar registros" aria-label="Buscar registros"><button>Buscar</button></form>
<p><a class="btn" href="?<?= catalog_e(http_build_query(['export'=>1,'q'=>$search])) ?>">Exportar CSV para Excel</a></p>
<div class="scroll"><table><thead><tr><?php foreach ($catalog['fields'] as $label): ?><th><?= catalog_e($label) ?></th><?php endforeach; ?><th>Acciones</th></tr></thead><tbody>
<?php foreach ($listing['rows'] as $row): ?><tr><?php foreach ($catalog['fields'] as $field=>$label): ?><td title="<?= catalog_e($row[$field]) ?>"><?= catalog_e(mb_substr((string)$row[$field],0,180)) ?></td><?php endforeach; ?><td class="row-actions"><a class="btn" href="?edit=<?= rawurlencode((string)$row[$catalog['key']]) ?>">Editar</a>
<form method="post" onsubmit="return confirm('¿Eliminar este registro?');"><input type="hidden" name="action" value="delete"><input type="hidden" name="original" value="<?= catalog_e($row[$catalog['key']]) ?>"><input type="hidden" name="csrf_token" value="<?= catalog_e($token) ?>"><button class="danger">Eliminar</button></form></td></tr><?php endforeach; ?>
<?php if (!$listing['rows']): ?><tr><td colspan="<?= count($catalog['fields'])+1 ?>">No hay registros para esta búsqueda.</td></tr><?php endif; ?></tbody></table></div>
<div class="pagination"><span class="muted"><?= $listing['total'] ?> registros · página <?= $listing['page'] ?> de <?= $listing['pages'] ?></span><div><?php if ($listing['page']>1): ?><a href="?<?= catalog_e(http_build_query(['q'=>$search,'page'=>$listing['page']-1])) ?>">Anterior</a><?php endif; ?> <?php if ($listing['page']<$listing['pages']): ?><a href="?<?= catalog_e(http_build_query(['q'=>$search,'page'=>$listing['page']+1])) ?>">Siguiente</a><?php endif; ?></div></div>
</section></div></main></body></html>
