<?php
// Simulación de una conexión a la base de datos.
// En un entorno real, deberías usar tus credenciales y una conexión segura.
$servername = "127.0.0.1:7000";
$username = "root";
$password = "";
$dbname = "guagua";

// Crear conexión
$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar conexión
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Establecer la codificación de caracteres
$conn->set_charset("utf8");

$mensaje = "";
$estudiante_seleccionado = null;
$materias_inscritas = [];
$search_query = '';

// Procesar el retiro de materias
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['retirar_materias'])) {
    if (!empty($_POST['id_estudiante']) && !empty($_POST['materias_a_retirar'])) {
        $id_estudiante = $conn->real_escape_string($_POST['id_estudiante']);
        $materias_a_retirar = $_POST['materias_a_retirar'];

        // Usamos un array para las placeholders
        $placeholders = implode(',', array_fill(0, count($materias_a_retirar), '?'));
        $tipos = str_repeat('i', count($materias_a_retirar)); // 'i' para integer

        $sql_update = "UPDATE inscripcion SET estado_inscripcion = 'Retirado' WHERE id_estudiante = ? AND id_inscripcion IN ($placeholders)";
        
        $stmt = $conn->prepare($sql_update);

        // Creamos un array con los parámetros a enlazar
        $params = array_merge([$id_estudiante], $materias_a_retirar);
        // Creamos un array con los tipos de datos
        $tipos_param = 's' . $tipos;
        
        // Usamos el operador de "splat" para pasar los parámetros
        $stmt->bind_param($tipos_param, ...$params);

        if ($stmt->execute()) {
            $mensaje = "<div class='alert alert-success mt-4'>Se han retirado las materias seleccionadas exitosamente.</div>";
        } else {
            $mensaje = "<div class='alert alert-danger mt-4'>Error al actualizar el estado: " . $stmt->error . "</div>";
        }
        $stmt->close();
    } else {
         $mensaje = "<div class='alert alert-warning mt-4'>Por favor, selecciona un estudiante y al menos una materia para retirar.</div>";
    }
}


// Búsqueda de estudiante y sus materias
if (isset($_GET['buscar_estudiante'])) {
    $search_query = trim($_GET['search']);
    if (!empty($search_query)) {
        $search_term = "%" . $conn->real_escape_string($search_query) . "%";
        
        $sql_estudiante = "SELECT id_usuario, nombre, apellido FROM usuario WHERE (nombre LIKE ? OR apellido LIKE ? OR id_usuario LIKE ?) AND rol = 'estudiante' LIMIT 1";
        $stmt_estudiante = $conn->prepare($sql_estudiante);
        $stmt_estudiante->bind_param("sss", $search_term, $search_term, $search_term);
        $stmt_estudiante->execute();
        $result_estudiante = $stmt_estudiante->get_result();

        if ($result_estudiante->num_rows > 0) {
            $estudiante_seleccionado = $result_estudiante->fetch_assoc();
            $id_estudiante_sel = $estudiante_seleccionado['id_usuario'];

            $sql_materias = "
                SELECT 
                    i.id_inscripcion,
                    mo.nombre_materia,
                    c.nombre_categoria_curso AS grado
                FROM inscripcion i
                JOIN asignacion a ON i.id_asignacion = a.id_asignacion
                JOIN materia_oficial mo ON a.id_asignatura = mo.id_materia
                JOIN categoria_curso c ON a.id_categoria_curso = c.id_categoria_curso
                WHERE i.id_estudiante = ? AND i.estado_inscripcion = 'En curso'";
            
            $stmt_materias = $conn->prepare($sql_materias);
            $stmt_materias->bind_param("s", $id_estudiante_sel);
            $stmt_materias->execute();
            $result_materias = $stmt_materias->get_result();

            while ($row = $result_materias->fetch_assoc()) {
                $materias_inscritas[] = $row;
            }
            $stmt_materias->close();
        } else {
            $mensaje = "<div class='alert alert-warning mt-4'>No se encontró ningún estudiante con el término de búsqueda '{$search_query}'.</div>";
        }
        $stmt_estudiante->close();
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Módulo para Retirar Matrícula</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f0f2f5;
        }
        .main-container {
            max-width: 800px;
            margin: 40px auto;
            padding: 30px;
            background-color: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        }
        .header-title {
            color: #333;
            font-weight: 700;
            text-align: center;
            margin-bottom: 30px;
        }
        .search-form .form-control {
            border-radius: 8px;
        }
        .search-form .btn {
            border-radius: 8px;
        }
        .student-info {
            background-color: #e9ecef;
            padding: 15px;
            border-radius: 8px;
            margin-top: 20px;
        }
        .materias-list .form-check-label {
            display: flex;
            justify-content: space-between;
            width: 100%;
            padding: 10px 15px;
            border: 1px solid #ddd;
            border-radius: 8px;
            margin-bottom: 10px;
            cursor: pointer;
            transition: background-color 0.2s;
        }
        .materias-list .form-check-input {
            margin-top: 0.5rem;
            margin-right: 1rem;
        }
        .materias-list .form-check-label:hover {
            background-color: #f8f9fa;
        }
        .materias-list .form-check-input:checked + .form-check-label {
            background-color: #e0f2ff;
            border-color: #0d6efd;
        }
        .materia-grado {
            font-size: 0.9em;
            color: #6c757d;
        }
        .btn-retirar {
            width: 100%;
            padding: 12px;
            font-size: 1.1em;
            font-weight: 600;
        }
        #confirmacionModal .modal-content {
            border-radius: 12px;
        }
    </style>
</head>
<body>
    <div class="main-container">
        <h1 class="header-title">Retirar Materias de Estudiante</h1>

        <!-- Formulario de búsqueda -->
        <form method="GET" action="" class="search-form">
            <div class="input-group mb-3">
                <input type="text" class="form-control" name="search" placeholder="Buscar por nombre, apellido o ID del estudiante" value="<?php echo htmlspecialchars($search_query); ?>" required>
                <button class="btn btn-primary" type="submit" name="buscar_estudiante">Buscar</button>
            </div>
        </form>

        <?php echo $mensaje; ?>

        <?php if ($estudiante_seleccionado): ?>
            <!-- Información del estudiante y lista de materias -->
            <div class="student-info">
                <h5>Estudiante: <strong><?php echo htmlspecialchars($estudiante_seleccionado['nombre'] . ' ' . $estudiante_seleccionado['apellido']); ?></strong></h5>
                <p class="mb-0">ID: <?php echo htmlspecialchars($estudiante_seleccionado['id_usuario']); ?></p>
            </div>
            
            <form id="retiroForm" method="POST" action="">
                <input type="hidden" name="id_estudiante" value="<?php echo htmlspecialchars($estudiante_seleccionado['id_usuario']); ?>">
                <h4 class="mt-4 mb-3">Materias Inscritas (En Curso)</h4>
                
                <?php if (!empty($materias_inscritas)): ?>
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="selectAllCheckbox">
                        <label class="form-check-label" for="selectAllCheckbox">
                            <strong>Seleccionar Todas</strong>
                        </label>
                    </div>

                    <div class="materias-list">
                        <?php foreach ($materias_inscritas as $materia): ?>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="materias_a_retirar[]" value="<?php echo $materia['id_inscripcion']; ?>" id="materia_<?php echo $materia['id_inscripcion']; ?>">
                                <label class="form-check-label" for="materia_<?php echo $materia['id_inscripcion']; ?>">
                                    <span><?php echo htmlspecialchars($materia['nombre_materia']); ?></span>
                                    <span class="materia-grado">Grado: <?php echo htmlspecialchars($materia['grado']); ?></span>
                                </label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <button type="button" class="btn btn-danger mt-4 btn-retirar" data-bs-toggle="modal" data-bs-target="#confirmacionModal">
                        Retirar Materias Seleccionadas
                    </button>
                <?php else: ?>
                    <div class="alert alert-info">Este estudiante no tiene materias 'En curso' para retirar.</div>
                <?php endif; ?>

                 <!-- Modal de Confirmación -->
                <div class="modal fade" id="confirmacionModal" tabindex="-1" aria-labelledby="confirmacionModalLabel" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="confirmacionModalLabel">Confirmar Retiro</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <p>Estás a punto de cambiar el estado de las siguientes materias a "Retirado" para el estudiante <strong><?php echo htmlspecialchars($estudiante_seleccionado['nombre'] . ' ' . $estudiante_seleccionado['apellido']); ?></strong>.</p>
                                <ul id="listaMateriasConfirmacion">
                                    <!-- Las materias seleccionadas se insertarán aquí con JavaScript -->
                                </ul>
                                <p class="text-danger"><strong>¿Estás seguro? Esta acción no se puede deshacer fácilmente.</strong></p>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                <button type="submit" name="retirar_materias" class="btn btn-danger">Sí, Retirar</button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Lógica para mostrar las materias seleccionadas en el modal de confirmación
        const confirmacionModal = document.getElementById('confirmacionModal');
        if(confirmacionModal) {
            confirmacionModal.addEventListener('show.bs.modal', function (event) {
                const listaConfirmacion = document.getElementById('listaMateriasConfirmacion');
                listaConfirmacion.innerHTML = ''; // Limpiar lista
                
                const checkboxes = document.querySelectorAll('input[name="materias_a_retirar[]"]:checked');
                
                if (checkboxes.length === 0) {
                     // Si no hay nada seleccionado, modifica el modal para mostrar una advertencia
                    const modalBody = confirmacionModal.querySelector('.modal-body');
                    modalBody.innerHTML = '<p class="text-warning">No has seleccionado ninguna materia para retirar. Por favor, cancela y selecciona al menos una.</p>';
                    // Ocultar el botón de confirmar
                    confirmacionModal.querySelector('button[name="retirar_materias"]').style.display = 'none';

                } else {
                     // Restaura el contenido original si ya había una advertencia
                    const modalBody = confirmacionModal.querySelector('.modal-body');
                     modalBody.innerHTML = `<p>Estás a punto de cambiar el estado de las siguientes materias a "Retirado" para el estudiante <strong><?php echo htmlspecialchars($estudiante_seleccionado['nombre'] . ' ' . $estudiante_seleccionado['apellido']); ?></strong>.</p>
                                <ul id="listaMateriasConfirmacion"></ul>
                                <p class="text-danger"><strong>¿Estás seguro? Esta acción no se puede deshacer fácilmente.</strong></p>`;
                    
                    const newListaConfirmacion = document.getElementById('listaMateriasConfirmacion');
                    checkboxes.forEach(checkbox => {
                        const label = document.querySelector('label[for="' + checkbox.id + '"]');
                        const nombreMateria = label.querySelector('span:first-child').textContent;
                        const li = document.createElement('li');
                        li.textContent = nombreMateria;
                        newListaConfirmacion.appendChild(li);
                    });
                    // Muestra el botón de confirmar si estaba oculto
                    confirmacionModal.querySelector('button[name="retirar_materias"]').style.display = 'inline-block';
                }
            });
        }
        
        // Lógica para "Seleccionar Todos"
        const selectAllCheckbox = document.getElementById('selectAllCheckbox');
        const materiaCheckboxes = document.querySelectorAll('input[name="materias_a_retirar[]"]');

        if(selectAllCheckbox) {
            selectAllCheckbox.addEventListener('change', function() {
                materiaCheckboxes.forEach(checkbox => {
                    checkbox.checked = this.checked;
                });
            });

            materiaCheckboxes.forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    const allChecked = Array.from(materiaCheckboxes).every(cb => cb.checked);
                    selectAllCheckbox.checked = allChecked;
                });
            });
        }
    </script>
</body>
</html>
<?php
$conn->close();
?>


