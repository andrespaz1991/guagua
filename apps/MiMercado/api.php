<?php
/**
 * MiMercado - API REST Backend
 */
@session_start();

// Habilitar reporte de errores para debug local (comentar en prod)
// error_reporting(E_ALL);
// ini_set('display_errors', 1);

header('Content-Type: application/json');

require_once '../../comun/conexion.php';
global $mysqli;

if (!isset($_SESSION['id_usuario'])) {
    echo json_encode(['success' => false, 'message' => 'Usuario no autenticado']);
    exit;
}

$id_usuario = $_SESSION['id_usuario'];
$action = $_GET['action'] ?? $_POST['action'] ?? '';

$response = ['success' => false, 'message' => 'Acción no válida', 'data' => null];

function normalizarNombreMercado($nombre) {
    $nombre = trim(preg_replace('/\s+/', ' ', (string)$nombre));
    return function_exists('mb_strtolower') ? mb_strtolower($nombre, 'UTF-8') : strtolower($nombre);
}

function buscarProductoMercadoPorNombre($mysqli, $id_usuario, $nombre, $forUpdate = false) {
    $sql = "SELECT id, nombre, precio, cantidad FROM mercado_productos
            WHERE id_usuario = ? AND LOWER(TRIM(nombre)) = LOWER(TRIM(?))
            ORDER BY id ASC LIMIT 1" . ($forUpdate ? ' FOR UPDATE' : '');
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param('ss', $id_usuario, $nombre);
    $stmt->execute();
    $producto = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return $producto ?: null;
}

switch ($action) {
    // ==========================================
    // PRODUCTOS
    // ==========================================
    case 'get_productos':
        $search = $_GET['search'] ?? '';
        $categoria = $_GET['categoria'] ?? '';
        $estado = $_GET['estado'] ?? 'todos';

        $sql = "SELECT p.*, c.nombre as categoria_nombre, c.color as categoria_color, c.icono as categoria_icono 
                FROM mercado_productos p 
                LEFT JOIN mercado_categorias c ON p.id_categoria = c.id 
                WHERE p.id_usuario = ?";
        
        $params = [$id_usuario];
        $types = "s";

        if (!empty($search)) {
            $sql .= " AND p.nombre LIKE ?";
            $params[] = "%$search%";
            $types .= "s";
        }
        
        if (!empty($categoria)) {
            $sql .= " AND p.id_categoria = ?";
            $params[] = $categoria;
            $types .= "i";
        }

        if ($estado == 'activo') {
            $sql .= " AND p.activo = 1";
        } else if ($estado == 'agotado') {
            $sql .= " AND p.activo = 0";
        }

        $sql .= " ORDER BY p.nombre ASC";

        $stmt = $mysqli->prepare($sql);
        if ($types) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_all(MYSQLI_ASSOC);
        
        // Calcular si está por agotarse basado en última compra
        foreach($data as &$row) {
            $row['por_agotar'] = false;
            $row['dias_restantes'] = 0;
            // Duración total = duración unitaria × cantidad
            $row['duracion_total'] = intval($row['duracion_dias']) * intval($row['cantidad']);
            
            // Buscar última compra
            $stmt_compra = $mysqli->prepare("SELECT fecha_compra FROM mercado_compras WHERE id_producto = ? ORDER BY fecha_compra DESC LIMIT 1");
            $stmt_compra->bind_param("i", $row['id']);
            $stmt_compra->execute();
            $res_compra = $stmt_compra->get_result();
            if ($res_compra->num_rows > 0) {
                $compra = $res_compra->fetch_assoc();
                $fecha_compra = new DateTime($compra['fecha_compra']);
                $hoy = new DateTime();
                $diff = $hoy->diff($fecha_compra)->days;
                
                // Usar duración total (unitaria × cantidad) para calcular restante
                $dias_restantes = $row['duracion_total'] - $diff;
                $row['dias_restantes'] = $dias_restantes;
                if ($dias_restantes <= 3) {
                    $row['por_agotar'] = true;
                }
            }
            $stmt_compra->close();
        }

        $response = ['success' => true, 'data' => $data];
        break;

    case 'save_producto':
        $id = intval($_POST['id'] ?? 0);
        $nombre = trim($_POST['nombre'] ?? '');
        $id_categoria = !empty($_POST['id_categoria']) ? $_POST['id_categoria'] : null;
        $precio = $_POST['precio'] ?? 0;
        $cantidad = $_POST['cantidad'] ?? 1;
        $unidad = $_POST['unidad'] ?? 'unidad';
        $duracion_dias = $_POST['duracion_dias'] ?? 15;
        $notas = $_POST['notas'] ?? '';
        $activo = isset($_POST['activo']) ? $_POST['activo'] : 1;
        
        if ($nombre === '') {
            $response = ['success' => false, 'message' => 'El nombre del producto es obligatorio'];
            break;
        }

        // Evita que se creen nuevamente registros que solo difieren en mayúsculas o espacios.
        $sql_duplicado = "SELECT id, nombre FROM mercado_productos
                          WHERE id_usuario = ? AND LOWER(TRIM(nombre)) = LOWER(TRIM(?))";
        $params_duplicado = [$id_usuario, $nombre];
        $types_duplicado = 'ss';
        if ($id > 0) {
            $sql_duplicado .= ' AND id <> ?';
            $params_duplicado[] = $id;
            $types_duplicado .= 'i';
        }
        $stmt_duplicado = $mysqli->prepare($sql_duplicado);
        $stmt_duplicado->bind_param($types_duplicado, ...$params_duplicado);
        $stmt_duplicado->execute();
        $duplicado = $stmt_duplicado->get_result()->fetch_assoc();
        $stmt_duplicado->close();
        if ($duplicado) {
            $response = ['success' => false, 'message' => "Ya existe el producto '{$duplicado['nombre']}' en tu inventario."];
            break;
        }

        $foto = null;

        // Manejar subida de foto
        if (isset($_FILES['foto']) && $_FILES['foto']['error'] == UPLOAD_ERR_OK) {
            $tmp_name = $_FILES['foto']['tmp_name'];
            $name = basename($_FILES['foto']['name']);
            $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
            if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                $new_name = uniqid() . '.' . $ext;
                $upload_dir = __DIR__ . '/uploads/';
                if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);
                if (move_uploaded_file($tmp_name, $upload_dir . $new_name)) {
                    $foto = 'uploads/' . $new_name;
                }
            }
        }

        if ($id > 0) {
            // Actualizar
            $sql = "UPDATE mercado_productos SET nombre=?, id_categoria=?, precio=?, cantidad=?, unidad=?, duracion_dias=?, notas=?, activo=? ";
            $params = [$nombre, $id_categoria, $precio, $cantidad, $unidad, $duracion_dias, $notas, $activo];
            $types = "sidisisi";
            
            if ($foto !== null) {
                $sql .= ", foto=? ";
                $params[] = $foto;
                $types .= "s";
                
                // Borrar foto vieja
                $stmt_old = $mysqli->prepare("SELECT foto FROM mercado_productos WHERE id=? AND id_usuario=?");
                $stmt_old->bind_param("is", $id, $id_usuario);
                $stmt_old->execute();
                $res_old = $stmt_old->get_result();
                if ($row_old = $res_old->fetch_assoc()) {
                    if ($row_old['foto'] && file_exists(__DIR__ . '/' . $row_old['foto'])) {
                        unlink(__DIR__ . '/' . $row_old['foto']);
                    }
                }
                $stmt_old->close();
            }
            
            $sql .= " WHERE id=? AND id_usuario=?";
            $params[] = $id;
            $params[] = $id_usuario;
            $types .= "is";

            $stmt = $mysqli->prepare($sql);
            $stmt->bind_param($types, ...$params);
            if ($stmt->execute()) {
                $response = ['success' => true, 'message' => 'Producto actualizado'];
            } else {
                $response = ['success' => false, 'message' => $stmt->error];
            }
        } else {
            // Insertar
            $sql = "INSERT INTO mercado_productos (nombre, id_categoria, precio, cantidad, unidad, duracion_dias, foto, notas, id_usuario, activo) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $mysqli->prepare($sql);
            $stmt->bind_param("sidisisssi", $nombre, $id_categoria, $precio, $cantidad, $unidad, $duracion_dias, $foto, $notas, $id_usuario, $activo);
            if ($stmt->execute()) {
                $response = ['success' => true, 'message' => 'Producto creado', 'id' => $stmt->insert_id];
            } else {
                $response = ['success' => false, 'message' => $stmt->error];
            }
        }
        break;

    // ==========================================
    // ACTUALIZAR CANTIDAD (con auditoría)
    // ==========================================
    case 'update_quantity':
        $input = json_decode(file_get_contents('php://input'), true);
        $id = intval($input['id'] ?? 0);
        $nueva_cantidad = intval($input['cantidad'] ?? 0);
        if ($nueva_cantidad < 0) $nueva_cantidad = 0;

        // Obtener nombre del producto para el log
        $stmt_name = $mysqli->prepare("SELECT nombre FROM mercado_productos WHERE id=? AND id_usuario=?");
        $stmt_name->bind_param("is", $id, $id_usuario);
        $stmt_name->execute();
        $res_name = $stmt_name->get_result();
        $producto_row = $res_name->fetch_assoc();
        $stmt_name->close();

        if (!$producto_row) {
            $response = ['success' => false, 'message' => 'Producto no encontrado'];
            break;
        }

        // Actualizar cantidad en mercado_productos
        $stmt = $mysqli->prepare("UPDATE mercado_productos SET cantidad=? WHERE id=? AND id_usuario=?");
        $stmt->bind_param("iis", $nueva_cantidad, $id, $id_usuario);
        if ($stmt->execute()) {
            // Registrar en log_mercado
            $nombre_prod = $producto_row['nombre'];
            $stmt_log = $mysqli->prepare("INSERT INTO log_mercado (id_producto, nombre_producto, cantidad, id_usuario) VALUES (?, ?, ?, ?)");
            $stmt_log->bind_param("isis", $id, $nombre_prod, $nueva_cantidad, $id_usuario);
            $stmt_log->execute();
            $stmt_log->close();

            $response = ['success' => true, 'message' => 'Cantidad actualizada', 'cantidad' => $nueva_cantidad];
        } else {
            $response = ['success' => false, 'message' => $stmt->error];
        }
        $stmt->close();
        break;

    case 'delete_producto':
        $id = json_decode(file_get_contents('php://input'), true)['id'] ?? 0;
        
        $stmt_old = $mysqli->prepare("SELECT foto FROM mercado_productos WHERE id=? AND id_usuario=?");
        $stmt_old->bind_param("is", $id, $id_usuario);
        $stmt_old->execute();
        $res_old = $stmt_old->get_result();
        if ($row_old = $res_old->fetch_assoc()) {
            if ($row_old['foto'] && file_exists(__DIR__ . '/' . $row_old['foto'])) {
                unlink(__DIR__ . '/' . $row_old['foto']);
            }
        }
        $stmt_old->close();

        $stmt = $mysqli->prepare("DELETE FROM mercado_productos WHERE id=? AND id_usuario=?");
        $stmt->bind_param("is", $id, $id_usuario);
        if ($stmt->execute()) {
            $response = ['success' => true, 'message' => 'Producto eliminado'];
        } else {
            $response = ['success' => false, 'message' => $stmt->error];
        }
        break;

    case 'eliminar_duplicados':
        $mysqli->begin_transaction();
        try {
            $stmt = $mysqli->prepare("SELECT id, nombre, cantidad, activo FROM mercado_productos WHERE id_usuario = ? ORDER BY id ASC FOR UPDATE");
            $stmt->bind_param('s', $id_usuario);
            $stmt->execute();
            $productos = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            $stmt->close();

            $conservados = [];
            $eliminados = 0;
            foreach ($productos as $producto) {
                $clave = normalizarNombreMercado($producto['nombre']);
                if ($clave === '') {
                    continue;
                }

                if (!isset($conservados[$clave])) {
                    $conservados[$clave] = $producto;
                    continue;
                }

                $principal = &$conservados[$clave];
                $id_principal = intval($principal['id']);
                $id_duplicado = intval($producto['id']);

                $stmt_compra = $mysqli->prepare("UPDATE mercado_compras SET id_producto = ? WHERE id_producto = ? AND id_usuario = ?");
                $stmt_compra->bind_param('iis', $id_principal, $id_duplicado, $id_usuario);
                $stmt_compra->execute();
                $stmt_compra->close();

                $stmt_lista = $mysqli->prepare("UPDATE mercado_lista_compras SET id_producto = ? WHERE id_producto = ? AND id_usuario = ?");
                $stmt_lista->bind_param('iis', $id_principal, $id_duplicado, $id_usuario);
                $stmt_lista->execute();
                $stmt_lista->close();

                $stmt_log = $mysqli->prepare("UPDATE log_mercado SET id_producto = ? WHERE id_producto = ? AND id_usuario = ?");
                $stmt_log->bind_param('iis', $id_principal, $id_duplicado, $id_usuario);
                $stmt_log->execute();
                $stmt_log->close();

                $cantidad_principal = intval($principal['cantidad']) + intval($producto['cantidad']);
                $activo_principal = (intval($principal['activo']) || intval($producto['activo'])) ? 1 : 0;
                $stmt_actualizar = $mysqli->prepare("UPDATE mercado_productos SET cantidad = ?, activo = ? WHERE id = ? AND id_usuario = ?");
                $stmt_actualizar->bind_param('iiis', $cantidad_principal, $activo_principal, $id_principal, $id_usuario);
                $stmt_actualizar->execute();
                $stmt_actualizar->close();
                $principal['cantidad'] = $cantidad_principal;
                $principal['activo'] = $activo_principal;

                $stmt_eliminar = $mysqli->prepare("DELETE FROM mercado_productos WHERE id = ? AND id_usuario = ?");
                $stmt_eliminar->bind_param('is', $id_duplicado, $id_usuario);
                $stmt_eliminar->execute();
                $stmt_eliminar->close();
                unset($principal);
                $eliminados++;
            }

            $mysqli->commit();
            $response = ['success' => true, 'message' => $eliminados ? "$eliminados duplicado(s) fusionado(s) en el inventario." : 'No se encontraron productos duplicados.'];
        } catch (Throwable $e) {
            $mysqli->rollback();
            $response = ['success' => false, 'message' => 'No fue posible quitar los duplicados: ' . $e->getMessage()];
        }
        break;

    // ==========================================
    // CATEGORÍAS
    // ==========================================
    case 'get_categorias':
        $stmt = $mysqli->prepare("SELECT * FROM mercado_categorias WHERE id_usuario = '0' OR id_usuario = ? ORDER BY nombre ASC");
        $stmt->bind_param("s", $id_usuario);
        $stmt->execute();
        $result = $stmt->get_result();
        $response = ['success' => true, 'data' => $result->fetch_all(MYSQLI_ASSOC)];
        break;

    // ==========================================
    // LISTA DE COMPRAS
    // ==========================================
    case 'get_lista':
        $stmt = $mysqli->prepare("SELECT l.*, p.foto, c.nombre as categoria_nombre, c.color as categoria_color 
                                 FROM mercado_lista_compras l 
                                 LEFT JOIN mercado_productos p ON l.id_producto = p.id 
                                 LEFT JOIN mercado_categorias c ON p.id_categoria = c.id 
                                 WHERE l.id_usuario = ? AND l.comprado = 0 ORDER BY l.prioridad ASC, l.created_at ASC");
        $stmt->bind_param("s", $id_usuario);
        $stmt->execute();
        $result = $stmt->get_result();
        $response = ['success' => true, 'data' => $result->fetch_all(MYSQLI_ASSOC)];
        break;

    case 'save_item_lista':
        $data = json_decode(file_get_contents('php://input'), true);
        $id_producto = !empty($data['id_producto']) ? intval($data['id_producto']) : null;
        $nombre_producto = trim($data['nombre_producto'] ?? '');
        $precio_estimado = floatval($data['precio_estimado'] ?? 0);
        $cantidad = max(1, intval($data['cantidad'] ?? 1));
        $prioridad = $data['prioridad'] ?? 'media';

        if ($id_producto) {
            $stmt_p = $mysqli->prepare("SELECT id, nombre, precio FROM mercado_productos WHERE id = ? AND id_usuario = ?");
            $stmt_p->bind_param("is", $id_producto, $id_usuario);
            $stmt_p->execute();
            $res_p = $stmt_p->get_result();
            if ($p = $res_p->fetch_assoc()) {
                $nombre_producto = $p['nombre'];
                if ($precio_estimado <= 0) $precio_estimado = floatval($p['precio']);
            } else {
                $response = ['success' => false, 'message' => 'El producto seleccionado no existe en tu inventario'];
                $stmt_p->close();
                break;
            }
            $stmt_p->close();
        }

        if ($nombre_producto === '') {
            $response = ['success' => false, 'message' => 'Escribe el nombre del producto'];
            break;
        }

        $stmt = $mysqli->prepare("INSERT INTO mercado_lista_compras (id_producto, nombre_producto, precio_estimado, cantidad, prioridad, id_usuario) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("isdiss", $id_producto, $nombre_producto, $precio_estimado, $cantidad, $prioridad, $id_usuario);
        
        if ($stmt->execute()) {
            $response = ['success' => true, 'message' => 'Agregado a la lista'];
        } else {
            $response = ['success' => false, 'message' => $stmt->error];
        }
        break;

    case 'update_cantidad_lista':
        $data = json_decode(file_get_contents('php://input'), true);
        $id = intval($data['id'] ?? 0);
        $cantidad = max(1, intval($data['cantidad'] ?? 1));
        $stmt = $mysqli->prepare("UPDATE mercado_lista_compras SET cantidad = ? WHERE id = ? AND id_usuario = ? AND comprado = 0");
        $stmt->bind_param("iis", $cantidad, $id, $id_usuario);
        if ($stmt->execute()) {
            $response = ['success' => true, 'message' => 'Cantidad actualizada'];
        }
        break;

    case 'comprar_items_lista':
        $data = json_decode(file_get_contents('php://input'), true);
        $ids = array_values(array_unique(array_filter(array_map('intval', $data['ids'] ?? []), function ($id) {
            return $id > 0;
        })));
        if (!$ids) {
            $response = ['success' => false, 'message' => 'Selecciona al menos un producto para comprar'];
            break;
        }

        $mysqli->begin_transaction();
        try {
            $marcadores = implode(',', array_fill(0, count($ids), '?'));
            $tipos = 's' . str_repeat('i', count($ids));
            $parametros = array_merge([$id_usuario], $ids);
            $stmt_lista = $mysqli->prepare("SELECT id, id_producto, nombre_producto, precio_estimado, cantidad
                                            FROM mercado_lista_compras
                                            WHERE id_usuario = ? AND comprado = 0 AND id IN ($marcadores) FOR UPDATE");
            $stmt_lista->bind_param($tipos, ...$parametros);
            $stmt_lista->execute();
            $items = $stmt_lista->get_result()->fetch_all(MYSQLI_ASSOC);
            $stmt_lista->close();

            if (!$items) {
                throw new RuntimeException('Los productos seleccionados ya no están pendientes en la lista');
            }

            $fecha_compra = date('Y-m-d H:i:s');
            $total = 0;
            foreach ($items as $item) {
                $cantidad_item = max(1, intval($item['cantidad']));
                $precio_item = floatval($item['precio_estimado']);
                $producto = null;

                if (!empty($item['id_producto'])) {
                    $stmt_producto = $mysqli->prepare("SELECT id, nombre FROM mercado_productos WHERE id = ? AND id_usuario = ? FOR UPDATE");
                    $stmt_producto->bind_param('is', $item['id_producto'], $id_usuario);
                    $stmt_producto->execute();
                    $producto = $stmt_producto->get_result()->fetch_assoc();
                    $stmt_producto->close();
                }
                if (!$producto) {
                    $producto = buscarProductoMercadoPorNombre($mysqli, $id_usuario, $item['nombre_producto'], true);
                }

                if ($producto) {
                    $id_producto_final = intval($producto['id']);
                    $stmt_actualizar = $mysqli->prepare("UPDATE mercado_productos
                        SET cantidad = cantidad + ?, activo = 1, precio = IF(? > 0, ?, precio)
                        WHERE id = ? AND id_usuario = ?");
                    $stmt_actualizar->bind_param('iddis', $cantidad_item, $precio_item, $precio_item, $id_producto_final, $id_usuario);
                    $stmt_actualizar->execute();
                    $stmt_actualizar->close();
                } else {
                    $nombre_nuevo = trim($item['nombre_producto']);
                    $stmt_nuevo = $mysqli->prepare("INSERT INTO mercado_productos (nombre, precio, cantidad, id_usuario, activo) VALUES (?, ?, ?, ?, 1)");
                    $stmt_nuevo->bind_param('sdis', $nombre_nuevo, $precio_item, $cantidad_item, $id_usuario);
                    $stmt_nuevo->execute();
                    $id_producto_final = $stmt_nuevo->insert_id;
                    $stmt_nuevo->close();
                }

                $stmt_compra = $mysqli->prepare("INSERT INTO mercado_compras (id_producto, precio_compra, cantidad, fecha_compra, id_usuario) VALUES (?, ?, ?, ?, ?)");
                $stmt_compra->bind_param('idiss', $id_producto_final, $precio_item, $cantidad_item, $fecha_compra, $id_usuario);
                $stmt_compra->execute();
                $stmt_compra->close();
                $total += $precio_item * $cantidad_item;
            }

            $stmt_eliminar = $mysqli->prepare("DELETE FROM mercado_lista_compras WHERE id_usuario = ? AND id IN ($marcadores)");
            $stmt_eliminar->bind_param($tipos, ...$parametros);
            $stmt_eliminar->execute();
            $stmt_eliminar->close();
            $mysqli->commit();
            $response = ['success' => true, 'message' => count($items) . ' producto(s) comprado(s) y guardados en gastos.', 'data' => ['cantidad' => count($items), 'total' => $total, 'fecha_compra' => $fecha_compra]];
        } catch (Throwable $e) {
            $mysqli->rollback();
            $response = ['success' => false, 'message' => 'No fue posible guardar la compra: ' . $e->getMessage()];
        }
        break;

    case 'delete_item_lista':
        $id = json_decode(file_get_contents('php://input'), true)['id'] ?? 0;
        $stmt = $mysqli->prepare("DELETE FROM mercado_lista_compras WHERE id=? AND id_usuario=?");
        $stmt->bind_param("is", $id, $id_usuario);
        if ($stmt->execute()) {
            $response = ['success' => true, 'message' => 'Item eliminado'];
        }
        break;
        
    case 'limpiar_lista':
        $stmt = $mysqli->prepare("DELETE FROM mercado_lista_compras WHERE comprado = 1 AND id_usuario=?");
        $stmt->bind_param("s", $id_usuario);
        if ($stmt->execute()) {
            $response = ['success' => true, 'message' => 'Lista limpiada'];
        }
        break;

    // ==========================================
    // STATS & DASHBOARD
    // ==========================================
    case 'get_stats_dashboard':
        $mes = date('m');
        $anio = date('Y');
        
        $data = [
            'total_gastado_mes' => 0,
            'presupuesto_mes' => 0,
            'total_productos' => 0,
            'productos_por_agotar' => 0,
            'gasto_q1' => 0,
            'gasto_q2' => 0
        ];
        
        // Total gastado mes
        $stmt = $mysqli->prepare("SELECT SUM(precio_compra * cantidad) as total FROM mercado_compras WHERE id_usuario = ? AND MONTH(fecha_compra) = ? AND YEAR(fecha_compra) = ?");
        $stmt->bind_param("sii", $id_usuario, $mes, $anio);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($row = $res->fetch_assoc()) $data['total_gastado_mes'] = $row['total'] ?: 0;
        
        // Gasto Q1 (dias 1-15)
        $stmt = $mysqli->prepare("SELECT SUM(precio_compra * cantidad) as total FROM mercado_compras WHERE id_usuario = ? AND MONTH(fecha_compra) = ? AND YEAR(fecha_compra) = ? AND DAY(fecha_compra) <= 15");
        $stmt->bind_param("sii", $id_usuario, $mes, $anio);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($row = $res->fetch_assoc()) $data['gasto_q1'] = $row['total'] ?: 0;
        
        // Gasto Q2 (dias 16-31)
        $stmt = $mysqli->prepare("SELECT SUM(precio_compra * cantidad) as total FROM mercado_compras WHERE id_usuario = ? AND MONTH(fecha_compra) = ? AND YEAR(fecha_compra) = ? AND DAY(fecha_compra) > 15");
        $stmt->bind_param("sii", $id_usuario, $mes, $anio);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($row = $res->fetch_assoc()) $data['gasto_q2'] = $row['total'] ?: 0;
        
        // Total productos
        $stmt = $mysqli->prepare("SELECT COUNT(*) as total FROM mercado_productos WHERE id_usuario = ? AND activo = 1");
        $stmt->bind_param("s", $id_usuario);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($row = $res->fetch_assoc()) $data['total_productos'] = $row['total'];
        
        // Presupuesto
        $stmt = $mysqli->prepare("SELECT presupuesto FROM mercado_presupuesto WHERE id_usuario = ? AND mes = ? AND anio = ?");
        $stmt->bind_param("sii", $id_usuario, $mes, $anio);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($row = $res->fetch_assoc()) $data['presupuesto_mes'] = $row['presupuesto'];
        
        $response = ['success' => true, 'data' => $data];
        break;

    case 'get_estadisticas_periodo':
        $mes = max(1, min(12, intval($_GET['mes'] ?? date('m'))));
        $anio = max(2000, min(2100, intval($_GET['anio'] ?? date('Y'))));
        $inicio_periodo = sprintf('%04d-%02d-01 00:00:00', $anio, $mes);
        $fin_periodo = date('Y-m-t 23:59:59', strtotime($inicio_periodo));
        $inicio_tendencia = date('Y-m-01 00:00:00', strtotime($inicio_periodo . ' -5 months'));
        $inicio_anterior = date('Y-m-01 00:00:00', strtotime($inicio_periodo . ' -1 month'));
        $fin_anterior = date('Y-m-t 23:59:59', strtotime($inicio_anterior));

        $data = [
            'periodo' => ['mes' => $mes, 'anio' => $anio],
            'resumen' => ['total' => 0, 'registros' => 0, 'unidades' => 0, 'promedio_registro' => 0, 'variacion_porcentaje' => null],
            'presupuesto' => ['valor' => 0, 'disponible' => 0, 'porcentaje_usado' => 0],
            'tendencia' => [],
            'diario' => [],
            'categorias' => [],
            'quincenas' => ['primera' => 0, 'segunda' => 0],
            'productos' => ['mas_comprados' => [], 'mayor_gasto' => []]
        ];

        $stmt = $mysqli->prepare("SELECT COALESCE(SUM(precio_compra * cantidad), 0) AS total,
                                         COUNT(*) AS registros,
                                         COALESCE(SUM(cantidad), 0) AS unidades
                                  FROM mercado_compras
                                  WHERE id_usuario = ? AND fecha_compra BETWEEN ? AND ?");
        $stmt->bind_param('sss', $id_usuario, $inicio_periodo, $fin_periodo);
        $stmt->execute();
        if ($resumen = $stmt->get_result()->fetch_assoc()) {
            $data['resumen']['total'] = floatval($resumen['total']);
            $data['resumen']['registros'] = intval($resumen['registros']);
            $data['resumen']['unidades'] = intval($resumen['unidades']);
            $data['resumen']['promedio_registro'] = $data['resumen']['registros'] > 0
                ? $data['resumen']['total'] / $data['resumen']['registros'] : 0;
        }
        $stmt->close();

        $stmt = $mysqli->prepare("SELECT COALESCE(SUM(precio_compra * cantidad), 0) AS total
                                  FROM mercado_compras
                                  WHERE id_usuario = ? AND fecha_compra BETWEEN ? AND ?");
        $stmt->bind_param('sss', $id_usuario, $inicio_anterior, $fin_anterior);
        $stmt->execute();
        $anterior = $stmt->get_result()->fetch_assoc();
        $total_anterior = floatval($anterior['total'] ?? 0);
        $stmt->close();
        if ($total_anterior > 0) {
            $data['resumen']['variacion_porcentaje'] = round((($data['resumen']['total'] - $total_anterior) / $total_anterior) * 100, 1);
        }

        $stmt = $mysqli->prepare("SELECT presupuesto FROM mercado_presupuesto WHERE id_usuario = ? AND mes = ? AND anio = ?");
        $stmt->bind_param('sii', $id_usuario, $mes, $anio);
        $stmt->execute();
        if ($presupuesto = $stmt->get_result()->fetch_assoc()) {
            $data['presupuesto']['valor'] = floatval($presupuesto['presupuesto']);
            $data['presupuesto']['disponible'] = $data['presupuesto']['valor'] - $data['resumen']['total'];
            $data['presupuesto']['porcentaje_usado'] = $data['presupuesto']['valor'] > 0
                ? round(($data['resumen']['total'] / $data['presupuesto']['valor']) * 100, 1) : 0;
        }
        $stmt->close();

        $stmt = $mysqli->prepare("SELECT DATE_FORMAT(fecha_compra, '%Y-%m') AS mes, COALESCE(SUM(precio_compra * cantidad), 0) AS total
                                  FROM mercado_compras
                                  WHERE id_usuario = ? AND fecha_compra BETWEEN ? AND ?
                                  GROUP BY DATE_FORMAT(fecha_compra, '%Y-%m')
                                  ORDER BY mes ASC");
        $stmt->bind_param('sss', $id_usuario, $inicio_tendencia, $fin_periodo);
        $stmt->execute();
        $data['tendencia'] = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        $stmt = $mysqli->prepare("SELECT DAY(fecha_compra) AS dia, COALESCE(SUM(precio_compra * cantidad), 0) AS total
                                  FROM mercado_compras
                                  WHERE id_usuario = ? AND fecha_compra BETWEEN ? AND ?
                                  GROUP BY DAY(fecha_compra)
                                  ORDER BY dia ASC");
        $stmt->bind_param('sss', $id_usuario, $inicio_periodo, $fin_periodo);
        $stmt->execute();
        $data['diario'] = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        $stmt = $mysqli->prepare("SELECT COALESCE(c.nombre, 'Sin categoría') AS nombre,
                                         COALESCE(c.color, '#64748B') AS color,
                                         COALESCE(SUM(mc.precio_compra * mc.cantidad), 0) AS total
                                  FROM mercado_compras mc
                                  LEFT JOIN mercado_productos p ON mc.id_producto = p.id
                                  LEFT JOIN mercado_categorias c ON p.id_categoria = c.id
                                  WHERE mc.id_usuario = ? AND mc.fecha_compra BETWEEN ? AND ?
                                  GROUP BY COALESCE(c.id, 0), COALESCE(c.nombre, 'Sin categoría'), COALESCE(c.color, '#64748B')
                                  ORDER BY total DESC");
        $stmt->bind_param('sss', $id_usuario, $inicio_periodo, $fin_periodo);
        $stmt->execute();
        $data['categorias'] = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        $stmt = $mysqli->prepare("SELECT
                COALESCE(SUM(CASE WHEN DAY(fecha_compra) <= 15 THEN precio_compra * cantidad ELSE 0 END), 0) AS primera,
                COALESCE(SUM(CASE WHEN DAY(fecha_compra) > 15 THEN precio_compra * cantidad ELSE 0 END), 0) AS segunda
            FROM mercado_compras
            WHERE id_usuario = ? AND fecha_compra BETWEEN ? AND ?");
        $stmt->bind_param('sss', $id_usuario, $inicio_periodo, $fin_periodo);
        $stmt->execute();
        if ($quincenas = $stmt->get_result()->fetch_assoc()) {
            $data['quincenas'] = ['primera' => floatval($quincenas['primera']), 'segunda' => floatval($quincenas['segunda'])];
        }
        $stmt->close();

        $stmt = $mysqli->prepare("SELECT COALESCE(p.nombre, 'Producto eliminado') AS nombre, SUM(mc.cantidad) AS unidades,
                                         SUM(mc.precio_compra * mc.cantidad) AS total
                                  FROM mercado_compras mc
                                  LEFT JOIN mercado_productos p ON mc.id_producto = p.id
                                  WHERE mc.id_usuario = ? AND mc.fecha_compra BETWEEN ? AND ?
                                  GROUP BY mc.id_producto, COALESCE(p.nombre, 'Producto eliminado')
                                  ORDER BY unidades DESC, total DESC LIMIT 5");
        $stmt->bind_param('sss', $id_usuario, $inicio_periodo, $fin_periodo);
        $stmt->execute();
        $data['productos']['mas_comprados'] = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        $stmt = $mysqli->prepare("SELECT COALESCE(p.nombre, 'Producto eliminado') AS nombre, SUM(mc.cantidad) AS unidades,
                                         SUM(mc.precio_compra * mc.cantidad) AS total
                                  FROM mercado_compras mc
                                  LEFT JOIN mercado_productos p ON mc.id_producto = p.id
                                  WHERE mc.id_usuario = ? AND mc.fecha_compra BETWEEN ? AND ?
                                  GROUP BY mc.id_producto, COALESCE(p.nombre, 'Producto eliminado')
                                  ORDER BY total DESC, unidades DESC LIMIT 5");
        $stmt->bind_param('sss', $id_usuario, $inicio_periodo, $fin_periodo);
        $stmt->execute();
        $data['productos']['mayor_gasto'] = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        $response = ['success' => true, 'data' => $data];
        break;

    case 'get_stats_por_categoria':
        $mes = $_GET['mes'] ?? date('m');
        $anio = $_GET['anio'] ?? date('Y');
        
        $stmt = $mysqli->prepare("SELECT c.nombre, c.color, SUM(mc.precio_compra * mc.cantidad) as total 
                                 FROM mercado_compras mc 
                                 JOIN mercado_productos p ON mc.id_producto = p.id 
                                 JOIN mercado_categorias c ON p.id_categoria = c.id 
                                 WHERE mc.id_usuario = ? AND MONTH(mc.fecha_compra) = ? AND YEAR(mc.fecha_compra) = ? 
                                 GROUP BY c.id ORDER BY total DESC");
        $stmt->bind_param("sii", $id_usuario, $mes, $anio);
        $stmt->execute();
        $result = $stmt->get_result();
        $response = ['success' => true, 'data' => $result->fetch_all(MYSQLI_ASSOC)];
        break;

    case 'get_stats_gastos_mensuales':
        $stmt = $mysqli->prepare("
            SELECT DATE_FORMAT(fecha_compra, '%Y-%m') as mes, SUM(precio_compra * cantidad) as total 
            FROM mercado_compras 
            WHERE id_usuario = ? 
            GROUP BY mes 
            ORDER BY mes DESC 
            LIMIT 6
        ");
        $stmt->bind_param("s", $id_usuario);
        $stmt->execute();
        $res = $stmt->get_result();
        $data = $res->fetch_all(MYSQLI_ASSOC);
        // Invertir para que el orden cronológico sea ascendente de cara al chart
        $response = ['success' => true, 'data' => array_reverse($data)];
        break;

    case 'get_stats_top_productos':
        $mes = $_GET['mes'] ?? date('m');
        $anio = $_GET['anio'] ?? date('Y');
        
        $data = ['comprados' => [], 'costosos' => []];
        
        // Más comprados (por cantidad de unidades)
        $stmt = $mysqli->prepare("
            SELECT p.nombre, SUM(mc.cantidad) as total_unidades 
            FROM mercado_compras mc 
            JOIN mercado_productos p ON mc.id_producto = p.id 
            WHERE mc.id_usuario = ? AND MONTH(mc.fecha_compra) = ? AND YEAR(mc.fecha_compra) = ? 
            GROUP BY p.id 
            ORDER BY total_unidades DESC 
            LIMIT 5
        ");
        $stmt->bind_param("sii", $id_usuario, $mes, $anio);
        $stmt->execute();
        $data['comprados'] = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        
        // Más costosos (por dinero total)
        $stmt2 = $mysqli->prepare("
            SELECT p.nombre, SUM(mc.precio_compra * mc.cantidad) as total_gastado 
            FROM mercado_compras mc 
            JOIN mercado_productos p ON mc.id_producto = p.id 
            WHERE mc.id_usuario = ? AND MONTH(mc.fecha_compra) = ? AND YEAR(mc.fecha_compra) = ? 
            GROUP BY p.id 
            ORDER BY total_gastado DESC 
            LIMIT 5
        ");
        $stmt2->bind_param("sii", $id_usuario, $mes, $anio);
        $stmt2->execute();
        $data['costosos'] = $stmt2->get_result()->fetch_all(MYSQLI_ASSOC);
        
        $response = ['success' => true, 'data' => $data];
        break;

    case 'get_stats_comparativa':
        $mes = $_GET['mes'] ?? date('m');
        $anio = $_GET['anio'] ?? date('Y');
        
        $q1 = 0;
        $q2 = 0;
        
        $stmt = $mysqli->prepare("
            SELECT 
                SUM(CASE WHEN DAY(fecha_compra) <= 15 THEN precio_compra * cantidad ELSE 0 END) as q1,
                SUM(CASE WHEN DAY(fecha_compra) > 15 THEN precio_compra * cantidad ELSE 0 END) as q2
            FROM mercado_compras 
            WHERE id_usuario = ? AND MONTH(fecha_compra) = ? AND YEAR(fecha_compra) = ?
        ");
        $stmt->bind_param("sii", $id_usuario, $mes, $anio);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($row = $res->fetch_assoc()) {
            $q1 = $row['q1'] ?: 0;
            $q2 = $row['q2'] ?: 0;
        }
        
        $response = ['success' => true, 'data' => ['q1' => $q1, 'q2' => $q2]];
        break;

    // ==========================================
    // IMPORTACIÓN CSV
    // ==========================================
    case 'importar_csv':
        if (!isset($_FILES['file']) || $_FILES['file']['error'] != UPLOAD_ERR_OK) {
            $response = ['success' => false, 'message' => 'Error al subir archivo'];
            break;
        }
        
        $file = $_FILES['file']['tmp_name'];
        $handle = fopen($file, "r");
        
        $delimitador = ',';
        $primera_linea = fgets($handle);
        if (strpos($primera_linea, ';') !== false) {
            $delimitador = ';';
        }
        rewind($handle);
        
        $headers = fgetcsv($handle, 1000, $delimitador);
        if ($headers === false) {
            fclose($handle);
            $response = ['success' => false, 'message' => 'El archivo CSV está vacío o no se puede leer'];
            break;
        }
        $headers = array_map(function ($header) {
            return normalizarNombreMercado(preg_replace('/^\xEF\xBB\xBF/', '', $header));
        }, $headers);
        
        // Encontrar indices
        $idx_nombre = array_search('nombre', $headers);
        $idx_precio = array_search('precio', $headers);
        $idx_categoria = array_search('categoria', $headers);
        $idx_duracion = array_search('duracion_dias', $headers);
        
        if ($idx_nombre === false) {
            fclose($handle);
            $response = ['success' => false, 'message' => 'Formato incorrecto. Falta columna nombre.'];
            break;
        }
        
        $importados = 0;
        $omitidos = 0;
        $errores = 0;

        // Se carga una sola vez para detectar también duplicados dentro del mismo CSV.
        $nombres_existentes = [];
        $stmt_existentes = $mysqli->prepare("SELECT nombre FROM mercado_productos WHERE id_usuario = ?");
        $stmt_existentes->bind_param('s', $id_usuario);
        $stmt_existentes->execute();
        $result_existentes = $stmt_existentes->get_result();
        while ($producto_existente = $result_existentes->fetch_assoc()) {
            $nombres_existentes[normalizarNombreMercado($producto_existente['nombre'])] = true;
        }
        $stmt_existentes->close();
        
        while (($data = fgetcsv($handle, 1000, $delimitador)) !== FALSE) {
            $nombre = trim($data[$idx_nombre] ?? '');
            if (empty($nombre)) continue;
            $nombre_normalizado = normalizarNombreMercado($nombre);
            if (isset($nombres_existentes[$nombre_normalizado])) {
                $omitidos++;
                continue;
            }
            
            $precio = $idx_precio !== false ? floatval($data[$idx_precio]) : 0;
            $duracion = max(1, $idx_duracion !== false ? intval($data[$idx_duracion]) : 15);
            $categoria_nombre = trim($idx_categoria !== false ? $data[$idx_categoria] : '');
            
            $id_cat = null;
            if (!empty($categoria_nombre)) {
                $stmt_c = $mysqli->prepare("SELECT id FROM mercado_categorias WHERE nombre LIKE ? AND (id_usuario = '0' OR id_usuario = ?)");
                $search_cat = "%$categoria_nombre%";
                $stmt_c->bind_param("ss", $search_cat, $id_usuario);
                $stmt_c->execute();
                $res_c = $stmt_c->get_result();
                if ($c = $res_c->fetch_assoc()) {
                    $id_cat = $c['id'];
                }
                $stmt_c->close();
            }
            
            $stmt = $mysqli->prepare("INSERT INTO mercado_productos (nombre, precio, id_categoria, duracion_dias, id_usuario) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("sdiis", $nombre, $precio, $id_cat, $duracion, $id_usuario);
            if ($stmt->execute()) {
                $importados++;
                $nombres_existentes[$nombre_normalizado] = true;
            } else {
                $errores++;
            }
            $stmt->close();
        }
        
        fclose($handle);
        
        $response = ['success' => true, 'message' => "Importación finalizada. $importados importados, $omitidos omitidos porque ya existen y $errores errores.", 'data' => ['importados' => $importados, 'omitidos' => $omitidos, 'errores' => $errores]];
        break;
}

echo json_encode($response);
exit;
