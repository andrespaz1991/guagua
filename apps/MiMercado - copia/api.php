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
                
                $dias_restantes = $row['duracion_dias'] - $diff;
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
        $id = $_POST['id'] ?? 0;
        $nombre = $_POST['nombre'] ?? '';
        $id_categoria = !empty($_POST['id_categoria']) ? $_POST['id_categoria'] : null;
        $precio = $_POST['precio'] ?? 0;
        $cantidad = $_POST['cantidad'] ?? 1;
        $unidad = $_POST['unidad'] ?? 'unidad';
        $duracion_dias = $_POST['duracion_dias'] ?? 15;
        $notas = $_POST['notas'] ?? '';
        $activo = isset($_POST['activo']) ? $_POST['activo'] : 1;
        
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
            $types = "sidissii";
            
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
            $stmt->bind_param("sidississi", $nombre, $id_categoria, $precio, $cantidad, $unidad, $duracion_dias, $foto, $notas, $id_usuario, $activo);
            if ($stmt->execute()) {
                $response = ['success' => true, 'message' => 'Producto creado', 'id' => $stmt->insert_id];
            } else {
                $response = ['success' => false, 'message' => $stmt->error];
            }
        }
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
                                 WHERE l.id_usuario = ? ORDER BY l.comprado ASC, l.prioridad ASC");
        $stmt->bind_param("s", $id_usuario);
        $stmt->execute();
        $result = $stmt->get_result();
        $response = ['success' => true, 'data' => $result->fetch_all(MYSQLI_ASSOC)];
        break;

    case 'save_item_lista':
        $data = json_decode(file_get_contents('php://input'), true);
        $id_producto = !empty($data['id_producto']) ? $data['id_producto'] : null;
        $nombre_producto = $data['nombre_producto'] ?? '';
        $precio_estimado = $data['precio_estimado'] ?? 0;
        $cantidad = $data['cantidad'] ?? 1;
        $prioridad = $data['prioridad'] ?? 'media';

        if ($id_producto && empty($nombre_producto)) {
            $stmt_p = $mysqli->prepare("SELECT nombre, precio FROM mercado_productos WHERE id = ?");
            $stmt_p->bind_param("i", $id_producto);
            $stmt_p->execute();
            $res_p = $stmt_p->get_result();
            if ($p = $res_p->fetch_assoc()) {
                $nombre_producto = $p['nombre'];
                if (!$precio_estimado) $precio_estimado = $p['precio'];
            }
        }

        $stmt = $mysqli->prepare("INSERT INTO mercado_lista_compras (id_producto, nombre_producto, precio_estimado, cantidad, prioridad, id_usuario) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("isdiss", $id_producto, $nombre_producto, $precio_estimado, $cantidad, $prioridad, $id_usuario);
        
        if ($stmt->execute()) {
            $response = ['success' => true, 'message' => 'Agregado a la lista'];
        } else {
            $response = ['success' => false, 'message' => $stmt->error];
        }
        break;

    case 'toggle_comprado':
        $data = json_decode(file_get_contents('php://input'), true);
        $id = $data['id'];
        $comprado = $data['comprado'] ? 1 : 0;
        
        $stmt = $mysqli->prepare("UPDATE mercado_lista_compras SET comprado = ? WHERE id = ? AND id_usuario = ?");
        $stmt->bind_param("iis", $comprado, $id, $id_usuario);
        if ($stmt->execute()) {
            
            // Si se marcó como comprado, registrar en mercado_compras y actualizar producto
            if ($comprado == 1) {
                $stmt_l = $mysqli->prepare("SELECT id_producto, precio_estimado, cantidad FROM mercado_lista_compras WHERE id = ?");
                $stmt_l->bind_param("i", $id);
                $stmt_l->execute();
                $res_l = $stmt_l->get_result();
                if ($item = $res_l->fetch_assoc()) {
                    if ($item['id_producto']) {
                        $fecha = date('Y-m-d');
                        $dia = date('d');
                        $periodo = $dia <= 15 ? 'quincenal' : 'quincenal'; // TODO logic
                        
                        $stmt_c = $mysqli->prepare("INSERT INTO mercado_compras (id_producto, precio_compra, cantidad, fecha_compra, id_usuario) VALUES (?, ?, ?, ?, ?)");
                        $stmt_c->bind_param("idiss", $item['id_producto'], $item['precio_estimado'], $item['cantidad'], $fecha, $id_usuario);
                        $stmt_c->execute();
                        
                        // Set activo = 1 in case it was 0
                        $stmt_u = $mysqli->prepare("UPDATE mercado_productos SET activo = 1 WHERE id = ?");
                        $stmt_u->bind_param("i", $item['id_producto']);
                        $stmt_u->execute();
                    }
                }
            }
            
            $response = ['success' => true, 'message' => 'Estado actualizado'];
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
        
        // Encontrar indices
        $idx_nombre = array_search('nombre', $headers);
        $idx_precio = array_search('precio', $headers);
        $idx_categoria = array_search('categoria', $headers);
        $idx_duracion = array_search('duracion_dias', $headers);
        
        if ($idx_nombre === false) {
            $response = ['success' => false, 'message' => 'Formato incorrecto. Falta columna nombre.'];
            break;
        }
        
        $importados = 0;
        $errores = 0;
        
        while (($data = fgetcsv($handle, 1000, $delimitador)) !== FALSE) {
            $nombre = $data[$idx_nombre] ?? '';
            if (empty($nombre)) continue;
            
            $precio = $idx_precio !== false ? floatval($data[$idx_precio]) : 0;
            $duracion = $idx_duracion !== false ? intval($data[$idx_duracion]) : 15;
            $categoria_nombre = $idx_categoria !== false ? $data[$idx_categoria] : '';
            
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
            } else {
                $errores++;
            }
            $stmt->close();
        }
        
        fclose($handle);
        
        $response = ['success' => true, 'message' => "Importación finalizada. $importados importados, $errores errores."];
        break;
}

echo json_encode($response);
exit;
