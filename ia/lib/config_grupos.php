<?php
require_once __DIR__ . '/../../comun/sql.php';
function guagua_report_group_save(mysqli $conexion, array $datos, string $archivo): array {
        try {

            $id = (int)($datos['id'] ?? 0);
            $nombre = trim((string)($datos['nombre'] ?? ''));
            $minimo = (int)($datos['min_grado'] ?? 0);
            $maximo = (int)($datos['max_grado'] ?? 0);
            $ruta = trim((string)($datos['ruta_excel'] ?? ''));
            $ultima = (int)($datos['ultimafila'] ?? 0);
            if ($nombre === '' || $minimo < 0 || $maximo < $minimo || $ultima < 1) throw new InvalidArgumentException('Complete un grupo y rango de filas válidos.');
            $conexion->begin_transaction();
            if ($id > 0) guagua_sql($conexion, 'UPDATE config_grupos SET nombre=?, min_grado=?, max_grado=?, ruta_excel=?, ultimafila=? WHERE id=?', [$nombre,$minimo,$maximo,$ruta,$ultima,$id], 'siisii');
            else {
                guagua_sql($conexion, 'INSERT INTO config_grupos (nombre,min_grado,max_grado,ruta_excel,ultimafila) VALUES (?,?,?,?,?)', [$nombre,$minimo,$maximo,$ruta,$ultima], 'siisi');
                $id = $conexion->insert_id;
            }
            guagua_sql($conexion, 'DELETE FROM config_materias WHERE grupo_id=?', [$id], 'i');
            foreach ($datos['materias'] ?? [] as $materia) {
                $nombreMateria = trim((string)($materia['nombre'] ?? ''));
                $columna = strtoupper(trim((string)($materia['columna'] ?? '')));
                if ($nombreMateria === '' || !preg_match('/^[A-Z]{1,3}$/D', $columna)) throw new InvalidArgumentException('Revise los nombres y columnas de las materias.');
                guagua_sql($conexion, 'INSERT INTO config_materias (grupo_id,nombre_materia,columna_excel) VALUES (?,?,?)', [$id,$nombreMateria,$columna], 'iss');
            }

            $rutas = is_file($archivo) ? json_decode(file_get_contents($archivo), true) : [];
            if (!is_array($rutas)) throw new RuntimeException('El archivo de rutas no es válido.');
            $rutas[$id] = ['local'=>$ruta,'web'=>trim((string)($datos['ruta_excel_web'] ?? ''))];
            if (file_put_contents($archivo,json_encode($rutas,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES),LOCK_EX) === false) throw new RuntimeException('No fue posible guardar las rutas.');
            $conexion->commit();
            return ['success'=>true,'message'=>'Configuración del grupo guardada.'];
        } catch (Throwable $e) {
            $conexion->rollback();
            return ['success'=>false,'message'=>$e instanceof InvalidArgumentException ? $e->getMessage() : 'No fue posible guardar la configuración del grupo.'];
        }
    }
    
function guagua_report_group_delete(mysqli $conexion, int $id_grupo): array {

        try {
            if ($id_grupo <= 0) throw new InvalidArgumentException('Seleccione un grupo válido.');
            $conexion->begin_transaction();
            guagua_sql($conexion, 'DELETE FROM config_materias WHERE grupo_id=?', [$id_grupo], 'i');
            guagua_sql($conexion, 'DELETE FROM config_grupos WHERE id=?', [$id_grupo], 'i');
            $conexion->commit();
            return ['success'=>true,'message'=>'Grupo eliminado.'];
        } catch (Throwable $e) {
            $conexion->rollback();
            return ['success'=>false,'message'=>'No fue posible eliminar el grupo.'];
        }
    }

