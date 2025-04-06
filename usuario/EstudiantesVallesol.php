<form action="" enctype="multipart/form-data" method="POST">
    <td>
        <input type="file" name="datos"/>
        <input name="enviar" type="submit" value="importar"/>
    </td>
</form>

<?php
if (isset($_POST['enviar'])) {
    require '../comun/conexion.php';
    $archivo = $_FILES['datos']['tmp_name'];
    require_once '../comun/funciones.php';

    if (!file_exists($archivo)) {
        echo "<script type='text/javascript'>
                alert('Error: El archivo no se ha subido correctamente.');
                window.location = 'index.php';
              </script>";
        exit();
    }

    if (mime_content_type($archivo) !== 'text/plain') {
        echo "<script type='text/javascript'>
                alert('Error: El archivo no es un CSV válido.');
                window.location = 'index.php';
              </script>";
        exit();
    }

    $tablaUsuarios = '`usuario`(`id_usuario`, `usuario`, `clave`, `foto`, `ultima_sesion`, `num_visitas`, `puntos`, `estado`, `tipo_sangre`, `observaciones`)';
    $tablaInscripcion = '`inscripcion`(`id_asignacion`, `id_estudiante`, `fecha_inscripcion`, `estado_inscripcion`)';

    if (($archivo_abierto = fopen($archivo, "r")) !== FALSE) {
        $valoresUsuarios = [];
        $valoresInscripcion = [];
        $esPrimeraLinea = true;

        while ($celdas = fgetcsv($archivo_abierto, 1000, ";")) {
            if ($esPrimeraLinea) {
                $esPrimeraLinea = false;
                continue;
            }

            // Insertar datos en la tabla usuarios
            $valoresUsuarios[] = '("'.$celdas[0].'","'.$celdas[1].'","'.encriptar($celdas[2]).'","'.$celdas[3].'","'.$celdas[4].'","'.$celdas[5].'","'.$celdas[6].'","'.$celdas[7].'","'.$celdas[8].'","'.$celdas[9].'")';

            // Obtener los cursos e inscribir al estudiante
            $cursos = explode(',', $celdas[18]);
            foreach ($cursos as $curso) {
                // Consultar id_asignacion de la tabla asignacion basado en id_curso
                $queryAsignacion = "SELECT id_asignacion FROM asignacion WHERE id_curso = ?";
                if ($stmt = $mysqli->prepare($queryAsignacion)) {
                    $stmt->bind_param('i', $curso);
                    $stmt->execute();
                    $stmt->bind_result($id_asignacion);
                    while ($stmt->fetch()) {
                        $valoresInscripcion[] = '("'.$id_asignacion.'","'.$celdas[0].'","'.date('Y-m-d').'","En curso")';
                    }
                    $stmt->close();
                }
            }
        }

        fclose($archivo_abierto); // Cerrar el archivo después de procesarlo

        $mysqli->begin_transaction();
        try {
            $insertarUsuariosQuery = "INSERT INTO $tablaUsuarios VALUES " . implode(",", $valoresUsuarios);
            $insertarInscripcionQuery = "INSERT INTO $tablaInscripcion VALUES " . implode(",", $valoresInscripcion);

            $insertarUsuariosResult = $mysqli->query($insertarUsuariosQuery);
            $insertarInscripcionResult = $mysqli->query($insertarInscripcionQuery);

            if ($insertarUsuariosResult && $insertarInscripcionResult) {
                $mysqli->commit();
                echo "<script type='text/javascript'>
                        alert('Datos importados correctamente');
                        window.location = 'index.php';
                      </script>";
            } else {
                throw new Exception("Error en la inserción");
            }
        } catch (Exception $e) {
            $mysqli->rollback();
            echo "<script type='text/javascript'>
                    alert('Error: " . $e->getMessage() . "');
                    window.location = 'index.php';
                  </script>";
        }
    }
}
?>
