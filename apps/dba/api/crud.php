<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . "/../../../comun/conexion.php";

$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!$data || !isset($data['action']) || !isset($data['type'])) {
    echo json_encode(['status' => 'error', 'message' => 'Petición inválida']);
    exit;
}

$action = $data['action']; // 'create', 'delete'
$type = $data['type'];     // 'estandar', 'dba', 'eje_tematico'

$response = ['status' => 'success', 'message' => 'Operación exitosa'];

try {
    $mysqli->begin_transaction();

    if ($action === 'create') {
        if ($type === 'estandar') {
            $nombre = trim($data['nombre_estandar']);
            $grado = (int)$data['grado'];
            $id_periodo = (int)$data['id_periodo'];
            $id_materia = (int)$data['id_materia_oficial'];
            
            if (empty($nombre)) throw new Exception("El nombre no puede estar vacío");
            
            $stmt = $mysqli->prepare("INSERT INTO estandar (nombre_estandar, grado, id_periodo, id_materia_oficial) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("siii", $nombre, $grado, $id_periodo, $id_materia);
            $stmt->execute();
            $response['id'] = $mysqli->insert_id;
            $stmt->close();
        } 
        elseif ($type === 'dba') {
            $nombre = trim($data['nombre_dba']);
            $id_estandar = (int)$data['id_estandar'];
            
            if (empty($nombre)) throw new Exception("El nombre no puede estar vacío");
            
            $stmt = $mysqli->prepare("INSERT INTO dba (nombre_dba, id_estandar) VALUES (?, ?)");
            $stmt->bind_param("si", $nombre, $id_estandar);
            $stmt->execute();
            $response['id'] = $mysqli->insert_id;
            $stmt->close();
        }
        elseif ($type === 'eje_tematico') {
            $nombre = trim($data['nombre_eje_tematico']);
            $id_dba = (int)$data['id_dba'];
            
            if (empty($nombre)) throw new Exception("El nombre no puede estar vacío");
            
            $stmt = $mysqli->prepare("INSERT INTO eje_tematico (nombre_eje_tematico, id_dba) VALUES (?, ?)");
            $stmt->bind_param("si", $nombre, $id_dba);
            $stmt->execute();
            $response['id'] = $mysqli->insert_id;
            $stmt->close();
        }
    } 
    elseif ($action === 'delete') {
        if ($type === 'estandar') {
            $id = (int)$data['id_estandar'];
            
            // Borrado en cascada (Ejes Tematicos de los DBAs de este estandar)
            $stmt1 = $mysqli->prepare("DELETE FROM eje_tematico WHERE id_dba IN (SELECT id_dba FROM dba WHERE id_estandar = ?)");
            $stmt1->bind_param("i", $id);
            $stmt1->execute();
            $stmt1->close();
            
            // Borrado de DBAs de este estandar
            $stmt2 = $mysqli->prepare("DELETE FROM dba WHERE id_estandar = ?");
            $stmt2->bind_param("i", $id);
            $stmt2->execute();
            $stmt2->close();
            
            // Borrado del estandar
            $stmt3 = $mysqli->prepare("DELETE FROM estandar WHERE id_estandar = ?");
            $stmt3->bind_param("i", $id);
            $stmt3->execute();
            $stmt3->close();
        } 
        elseif ($type === 'dba') {
            $id = (int)$data['id_dba'];
            
            // Borrado de ejes tematicos
            $stmt1 = $mysqli->prepare("DELETE FROM eje_tematico WHERE id_dba = ?");
            $stmt1->bind_param("i", $id);
            $stmt1->execute();
            $stmt1->close();
            
            // Borrado del dba
            $stmt2 = $mysqli->prepare("DELETE FROM dba WHERE id_dba = ?");
            $stmt2->bind_param("i", $id);
            $stmt2->execute();
            $stmt2->close();
        }
        elseif ($type === 'eje_tematico') {
            $id = (int)$data['id_eje_tematico'];
            
            $stmt = $mysqli->prepare("DELETE FROM eje_tematico WHERE id_eje_tematico = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $stmt->close();
        }
    }
    elseif ($action === 'edit') {
        if ($type === 'estandar') {
            $id = (int)$data['id_estandar'];
            $nombre = trim($data['nombre_estandar']);
            if (empty($nombre)) throw new Exception("El nombre no puede estar vacío");
            
            $stmt = $mysqli->prepare("UPDATE estandar SET nombre_estandar = ? WHERE id_estandar = ?");
            $stmt->bind_param("si", $nombre, $id);
            $stmt->execute();
            $stmt->close();
        }
        elseif ($type === 'dba') {
            $id = (int)$data['id_dba'];
            $nombre = trim($data['nombre_dba']);
            if (empty($nombre)) throw new Exception("El nombre no puede estar vacío");
            
            $stmt = $mysqli->prepare("UPDATE dba SET nombre_dba = ? WHERE id_dba = ?");
            $stmt->bind_param("si", $nombre, $id);
            $stmt->execute();
            $stmt->close();
        }
        elseif ($type === 'eje_tematico') {
            $id = (int)$data['id_eje_tematico'];
            $nombre = trim($data['nombre_eje_tematico']);
            if (empty($nombre)) throw new Exception("El nombre no puede estar vacío");
            
            $stmt = $mysqli->prepare("UPDATE eje_tematico SET nombre_eje_tematico = ? WHERE id_eje_tematico = ?");
            $stmt->bind_param("si", $nombre, $id);
            $stmt->execute();
            $stmt->close();
        }
    }

    $mysqli->commit();
} catch (Exception $e) {
    $mysqli->rollback();
    $response = ['status' => 'error', 'message' => $e->getMessage()];
}

echo json_encode($response, JSON_UNESCAPED_UNICODE);
?>
