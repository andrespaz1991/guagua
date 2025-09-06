<?php
/**
 * Módulo de Gestión de Relaciones Estudiante-Acudiente
 *
 * Permite asignar y desasignar acudientes a estudiantes mediante una
 * interfaz interactiva de arrastrar y soltar, e importar masivamente
 * desde un archivo CSV.
 *
 * Mejoras:
 * - Interfaz Drag & Drop moderna y fácil de usar.
 * - Módulo de importación masiva desde CSV con validación.
 * - Búsqueda en tiempo real (AJAX) en las tres columnas.
 * - Operaciones AJAX para no recargar la página.
 * - Seguridad: Uso de sentencias preparadas para todas las operaciones de BD.
 * - Código organizado y comentado.
 */

ob_start();
session_start();

// --- 1. CONFIGURACIÓN E INCLUDES ---
require_once "../comun/conexion.php";
require_once "../comun/config.php";
require_once "../comun/funciones.php";

// --- 2. CONTROLADOR AJAX (BACKEND) ---
// Esta sección maneja las peticiones asíncronas desde el JavaScript.
if (isset($_POST['accion'])) {
    header('Content-Type: application/json');
    $response = ['status' => 'error', 'message' => 'Acción no reconocida.'];

    switch ($_POST['accion']) {
        
        case 'asignar':
            if (!empty($_POST['id_estudiante']) && !empty($_POST['id_acudiente']) && !empty($_POST['parentesco'])) {
                $stmt = $mysqli->prepare("INSERT INTO acudiente_estudiante (id_estudiante, id_acudiente, parentesco) VALUES (?, ?, ?)");
                $stmt->bind_param("sss", $_POST['id_estudiante'], $_POST['id_acudiente'], $_POST['parentesco']);
                if ($stmt->execute()) {
                    $response = ['status' => 'success', 'message' => 'Relación asignada correctamente.', 'new_id' => $mysqli->insert_id];
                } else {
                    $response['message'] = 'Error al guardar en la base de datos: ' . $stmt->error;
                }
                $stmt->close();
            } else {
                $response['message'] = 'Faltan datos para asignar la relación.';
            }
            break;
        
        case 'buscar':
            if (!empty($_POST['rol']) && isset($_POST['termino'])) {
                $rol = $_POST['rol'];
                $termino = $_POST['termino'];
                $result = getUsuariosPorRol($rol, $mysqli, $termino);
                $usuarios = [];
                while ($row = $result->fetch_assoc()) {
                    $usuarios[] = $row;
                }
                $response = ['status' => 'success', 'usuarios' => $usuarios];
            } else {
                $response['message'] = 'Faltan datos para la búsqueda.';
            }
            break;

        case 'eliminar':
            if (!empty($_POST['id_relacion'])) {
                $stmt = $mysqli->prepare("DELETE FROM acudiente_estudiante WHERE id_acudiente_estudiante = ?");
                $stmt->bind_param("i", $_POST['id_relacion']);
                if ($stmt->execute()) {
                    $response = ['status' => 'success', 'message' => 'Relación eliminada correctamente.'];
                } else {
                    $response['message'] = 'Error al eliminar de la base de datos: ' . $stmt->error;
                }
                $stmt->close();
            } else {
                $response['message'] = 'No se proporcionó el ID de la relación a eliminar.';
            }
            break;
            
        case 'preview_import':
            if (isset($_FILES['archivo_csv']) && $_FILES['archivo_csv']['error'] == 0) {
                $nuevos = [];
                $existentes = [];
                $archivo = fopen($_FILES['archivo_csv']['tmp_name'], 'r');
                
                // Omitir encabezado si existe
                fgetcsv($archivo); 
                
                $stmt_check = $mysqli->prepare("SELECT ae.id_acudiente_estudiante, u.nombre, u.apellido FROM acudiente_estudiante ae JOIN usuario u ON ae.id_acudiente = u.id_usuario WHERE ae.id_estudiante = ?");

                while (($linea = fgetcsv($archivo)) !== FALSE) {
                    // Validar que la línea no esté vacía y tenga los 3 campos
                    if (count($linea) >= 3 && !empty($linea[0]) && !empty($linea[1]) && !empty($linea[2])) {
                        $relacion = [
                            'id_estudiante' => $linea[0], 
                            'id_acudiente' => $linea[1], 
                            'parentesco' => $linea[2]
                        ];

                        $stmt_check->bind_param("s", $relacion['id_estudiante']);
                        $stmt_check->execute();
                        $result = $stmt_check->get_result();

                        if ($result->num_rows > 0) {
                            $existente = $result->fetch_assoc();
                            $relacion['acudiente_actual'] = $existente['nombre'] . ' ' . $existente['apellido'];
                            $existentes[] = $relacion;
                        } else {
                            $nuevos[] = $relacion;
                        }
                    }
                }
                fclose($archivo);
                $stmt_check->close();
                $response = ['status' => 'success', 'nuevos' => $nuevos, 'existentes' => $existentes];
            } else {
                $response['message'] = 'Error al subir el archivo o archivo no válido.';
            }
            break;
        
        case 'process_import':
            $nuevos = json_decode($_POST['nuevos'] ?? '[]', true);
            $actualizar = json_decode($_POST['actualizar'] ?? '[]', true);
            $inserted_count = 0;
            $updated_count = 0;

            $mysqli->begin_transaction();
            try {
                // Actualizar existentes seleccionados
                if (!empty($actualizar)) {
                    $stmt_delete = $mysqli->prepare("DELETE FROM acudiente_estudiante WHERE id_estudiante = ?");
                    foreach ($actualizar as $rel) {
                        $stmt_delete->bind_param("s", $rel['id_estudiante']);
                        $stmt_delete->execute();
                    }
                    $stmt_delete->close();
                    
                    $stmt_update_insert = $mysqli->prepare("INSERT INTO acudiente_estudiante (id_estudiante, id_acudiente, parentesco) VALUES (?, ?, ?)");
                    foreach ($actualizar as $rel) {
                        $stmt_update_insert->bind_param("sss", $rel['id_estudiante'], $rel['id_acudiente'], $rel['parentesco']);
                        $stmt_update_insert->execute();
                        $updated_count++;
                    }
                    $stmt_update_insert->close();
                }

                // Insertar nuevos
                if (!empty($nuevos)) {
                    $stmt_insert_new = $mysqli->prepare("INSERT INTO acudiente_estudiante (id_estudiante, id_acudiente, parentesco) VALUES (?, ?, ?)");
                    foreach ($nuevos as $rel) {
                        $stmt_insert_new->bind_param("sss", $rel['id_estudiante'], $rel['id_acudiente'], $rel['parentesco']);
                        $stmt_insert_new->execute();
                        $inserted_count++;
                    }
                    $stmt_insert_new->close();
                }
                
                $mysqli->commit();
                $response = ['status' => 'success', 'message' => "Importación completada. Nuevos: $inserted_count, Actualizados: $updated_count."];
            } catch (Exception $e) {
                $mysqli->rollback();
                $response['message'] = 'Error en la transacción: ' . $e->getMessage();
            }
            break;
    }
    echo json_encode($response);
    exit(); // Termina la ejecución para no renderizar el HTML en peticiones AJAX.
}


// --- 3. LÓGICA DE NEGOCIO Y FUNCIONES (PARA CARGA INICIAL) ---
function getUsuariosPorRol($rol, mysqli $db, $termino = '') {
    $params = [];
    $types = '';
    $searchTerm = "%" . strtolower($termino) . "%";

    if ($rol === 'estudiante') {
        $sql = "SELECT id_usuario, nombre, apellido, foto, telefono FROM usuario 
                WHERE rol = ? AND id_usuario NOT IN (SELECT id_estudiante FROM acudiente_estudiante WHERE id_estudiante IS NOT NULL)";
        $params[] = $rol;
        $types .= 's';
    } else {
        $sql = "SELECT id_usuario, nombre, apellido, foto, telefono FROM usuario WHERE rol = ?";
        $params[] = $rol;
        $types .= 's';
    }

    if (!empty($termino)) {
        $sql .= " AND (LOWER(nombre) LIKE ? OR LOWER(apellido) LIKE ?)";
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $types .= 'ss';
    }
    
    $stmt = $db->prepare($sql);
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    return $stmt->get_result();
}

function getEstudiantesConAcudientes(mysqli $db) {
    $sql = "SELECT 
                ae.id_acudiente_estudiante,
                e.nombre AS nombre_estudiante, e.apellido AS apellido_estudiante, e.id_usuario AS id_estudiante, e.telefono as telefono_estudiante,
                a.nombre AS nombre_acudiente, a.apellido AS apellido_acudiente, a.id_usuario AS id_acudiente, a.telefono as telefono_acudiente,
                ae.parentesco
            FROM acudiente_estudiante ae
            JOIN usuario e ON ae.id_estudiante = e.id_usuario
            JOIN usuario a ON ae.id_acudiente = a.id_usuario
            ORDER BY apellido_estudiante, nombre_estudiante";
    return $db->query($sql);
}

// --- Carga de datos inicial para la vista ---
$acudientes = getUsuariosPorRol('acudiente', $mysqli);
$estudiantesSinAcudiente = getUsuariosPorRol('estudiante', $mysqli);
$estudiantesAsignados = getEstudiantesConAcudientes($mysqli);
?>

<!-- --- 4. VISTA (HTML + CSS) --- -->
<style>
    body { font-family: 'Arial', sans-serif; }
    .drag-container { display: flex; justify-content: space-between; gap: 20px; padding: 20px; }
    .drag-column {
        width: 32%;
        background-color: #f4f4f4;
        border-radius: 8px;
        padding: 15px;
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        display: flex;
        flex-direction: column;
    }
    .drag-column h3 { text-align: center; margin-top: 0; color: #333; border-bottom: 2px solid #ddd; padding-bottom: 10px; }
    .user-card { background-color: #fff; padding: 10px; margin-bottom: 10px; border-radius: 5px; border: 1px solid #ddd; cursor: grab; display: flex; align-items: center; gap: 10px; }
    #acudientes-list, #asignados-list, #estudiantes-list {
        max-height: 65vh; /* Altura máxima antes de que aparezca el scroll */
        overflow-y: auto; /* Scroll vertical automático */
        padding-right: 5px; /* Espacio para el scrollbar */
        flex-grow: 1; /* Permite que la lista crezca para llenar el espacio */
    }
    .user-card > div { display: flex; flex-direction: column; }
    .user-phone { font-size: 0.85em; color: #6c757d; }
    .user-card:active { cursor: grabbing; background-color: #f8f9fa; }
    .user-card img { width: 40px; height: 40px; border-radius: 50%; object-fit: cover; }
    .drop-zone { background-color: #e9f5ff; border: 2px dashed #007bff; }
    .assigned-card { background-color: #fff; padding: 15px; margin-bottom: 10px; border-radius: 5px; border: 1px solid #ddd; box-shadow: 0 1px 3px rgba(0,0,0,0.05); position: relative; font-size: 0.95em; }
    .assigned-card strong { display: block; margin-bottom: 5px; }
    .assigned-card small { color: #6c757d; }
    .remove-relation-btn { position: absolute; top: 5px; right: 5px; cursor: pointer; color: #dc3545; background: none; border: none; font-size: 1.2rem; }
    .search-input { margin-bottom: 10px; }
    .assigned-card.hidden { display: none; }
    .drop-zone.drag-over { background-color: #d1e7dd; }
    #import-modal .modal-body { max-height: 60vh; overflow-y: auto; }
    #import-results table { margin-top: 15px; }
</style>

<div class="jumbotron">
    <div class="container text-center">
        <h1 class="fip">Asignar Acudientes a Estudiantes</h1>
        <p>Arrastre un estudiante y un acudiente a la columna central para crear una relación.</p>
        <button class="btn btn-primary" data-toggle="modal" data-target="#import-modal">
            <span class="glyphicon glyphicon-import"></span> Importar desde CSV
        </button>
    </div>
</div>

<!-- Contenedor principal de las columnas -->
<div class="drag-container">
    <div class="drag-column" id="col-acudientes">
        <h3><span class="glyphicon glyphicon-user"></span> Acudientes</h3>
        <input type="text" class="form-control search-input" id="search-acudientes" placeholder="Buscar acudiente...">
        <div id="acudientes-list">
            <?php while ($acudiente = $acudientes->fetch_assoc()): ?>
                <div class="user-card" draggable="true" data-id="<?= htmlspecialchars($acudiente['id_usuario']) ?>" data-type="acudiente" data-telefono="<?= htmlspecialchars($acudiente['telefono'] ?? '') ?>">
                    <img src="<?= htmlspecialchars(rtrim(READFILE_URL, '/') . '/foto/' . $acudiente['foto']) ?>" alt="Foto">
                    <div>
                        <span><?= htmlspecialchars($acudiente['nombre'] . ' ' . $acudiente['apellido']) ?></span>
                        <span class="user-phone"><span class="glyphicon glyphicon-phone-alt"></span> <?= htmlspecialchars($acudiente['telefono'] ?: 'sin telefono') ?></span>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </div>

    <div class="drag-column drop-zone" id="col-asignados">
        <h3><span class="glyphicon glyphicon-link"></span> Estudiantes Asignados</h3>
        <input type="text" class="form-control search-input" id="search-asignados" placeholder="Buscar asignación...">
        <div id="asignados-list">
            <?php while ($asignacion = $estudiantesAsignados->fetch_assoc()): ?>
                <div class="assigned-card" id="relacion-<?= htmlspecialchars($asignacion['id_acudiente_estudiante']) ?>">
                    <strong>Estudiante:</strong> <span class="nombre-estudiante"><?= htmlspecialchars($asignacion['nombre_estudiante'] . ' ' . $asignacion['apellido_estudiante']) ?></span> <small>(Tel: <?= htmlspecialchars($asignacion['telefono_estudiante'] ?: 'sin telefono') ?>)</small><br>
                    <strong>Acudiente:</strong> <span class="nombre-acudiente"><?= htmlspecialchars($asignacion['nombre_acudiente'] . ' ' . $asignacion['apellido_acudiente']) ?></span> <small>(Tel: <?= htmlspecialchars($asignacion['telefono_acudiente'] ?: 'sin telefono') ?>)</small><br>
                    <em>Parentesco: <span class="parentesco"><?= htmlspecialchars($asignacion['parentesco']) ?></span></em>
                    <button class="remove-relation-btn" data-relacion-id="<?= htmlspecialchars($asignacion['id_acudiente_estudiante']) ?>" title="Eliminar relación">
                        &times;
                    </button>
                </div>
            <?php endwhile; ?>
        </div>
    </div>

    <div class="drag-column" id="col-estudiantes">
        <h3><span class="glyphicon glyphicon-education"></span> Estudiantes sin Acudiente</h3>
        <input type="text" class="form-control search-input" id="search-estudiantes" placeholder="Buscar estudiante...">
        <div id="estudiantes-list">
            <?php while ($estudiante = $estudiantesSinAcudiente->fetch_assoc()): ?>
                <div class="user-card" draggable="true" data-id="<?= htmlspecialchars($estudiante['id_usuario']) ?>" data-type="estudiante" data-telefono="<?= htmlspecialchars($estudiante['telefono'] ?? '') ?>">
                    <img src="<?= htmlspecialchars(rtrim(READFILE_URL, '/') . '/foto/' . $estudiante['foto']) ?>" alt="Foto">
                    <div>
                        <span><?= htmlspecialchars($estudiante['nombre'] . ' ' . $estudiante['apellido']) ?></span>
                        <span class="user-phone"><span class="glyphicon glyphicon-phone-alt"></span> <?= htmlspecialchars($estudiante['telefono'] ?: 'sin telefono') ?></span>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </div>
</div>

<!-- Modal para la importación de CSV -->
<div class="modal fade" id="import-modal" tabindex="-1" role="dialog" aria-labelledby="importModalLabel">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="importModalLabel">Importar Relaciones desde CSV</h4>
            </div>
            <div class="modal-body">
                <p>Seleccione un archivo CSV con las columnas: <strong>id_estudiante, id_acudiente, parentesco</strong> (en ese orden y con un encabezado en la primera fila que será ignorado).</p>
                <form id="form-import" enctype="multipart/form-data">
                    <div class="form-group">
                        <input type="file" name="archivo_csv" id="archivo-csv" class="form-control" accept=".csv" required>
                    </div>
                    <button type="submit" class="btn btn-info">Validar Archivo</button>
                </form>
                <hr>
                <div id="import-results" style="display:none;">
                    <h4>Resultados de la Validación</h4>
                    <form id="form-process-import">
                        <div id="existentes-container"></div>
                        <div id="nuevos-container" style="display:none;"></div>
                        <button type="submit" class="btn btn-success">Procesar Importación</button>
                    </form>
                </div>
                 <div id="import-spinner" style="display:none; text-align:center;"><span class="glyphicon glyphicon-refresh gly-spin"></span> Procesando...</div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<!-- --- 5. JAVASCRIPT --- -->
<script>
document.addEventListener('DOMContentLoaded', () => {
    const dropZone = document.getElementById('col-asignados');
    let draggedStudent = null;
    let draggedAcudiente = null;

    function addDragEventsToCard(card) {
        card.addEventListener('dragstart', handleDragStart);
    }

    document.querySelectorAll('.user-card').forEach(addDragEventsToCard);

    dropZone.addEventListener('dragover', handleDragOver);
    dropZone.addEventListener('dragleave', handleDragLeave);
    dropZone.addEventListener('drop', handleDrop);

    function handleDragStart(e) {
        const id = e.target.dataset.id;
        const type = e.target.dataset.type;
        const name = e.target.querySelector('span').textContent;
        const telefono = e.target.dataset.telefono || '';
        e.dataTransfer.setData('text/plain', JSON.stringify({ id, type, name, telefono }));
        e.dataTransfer.effectAllowed = 'move';
    }

    function handleDragOver(e) {
        e.preventDefault();
        e.target.closest('.drop-zone').classList.add('drag-over');
    }

    function handleDragLeave(e) {
        e.target.closest('.drop-zone').classList.remove('drag-over');
    }

    function handleDrop(e) {
        e.preventDefault();
        e.target.closest('.drop-zone').classList.remove('drag-over');
        
        const data = JSON.parse(e.dataTransfer.getData('text/plain'));

        if (data.type === 'estudiante') {
            draggedStudent = data;
        } else if (data.type === 'acudiente') {
            draggedAcudiente = data;
        }

        if (draggedStudent && draggedAcudiente) {
            const parentesco = prompt(`Asignando a ${draggedAcudiente.name} como acudiente de ${draggedStudent.name}.\n\nPor favor, ingrese el parentesco:`);
            if (parentesco && parentesco.trim() !== '') {
                asignarRelacion(draggedStudent.id, draggedAcudiente.id, parentesco.trim(), draggedStudent.name, draggedAcudiente.name, draggedStudent.telefono, draggedAcudiente.telefono);
            }
            draggedStudent = null;
            draggedAcudiente = null;
        }
    }

    document.getElementById('asignados-list').addEventListener('click', (e) => {
        if (e.target.classList.contains('remove-relation-btn')) {
            const idRelacion = e.target.dataset.relacionId;
            if (confirm('¿Está seguro de que desea eliminar esta relación?')) {
                eliminarRelacion(idRelacion);
            }
        }
    });
    
    document.getElementById('search-acudientes').addEventListener('keyup', handleSearch);
    document.getElementById('search-estudiantes').addEventListener('keyup', handleSearch);
    document.getElementById('search-asignados').addEventListener('keyup', handleSearchAsignados);

    function handleSearch(e) {
        const termino = e.target.value.trim();
        const rol = e.target.id === 'search-acudientes' ? 'acudiente' : 'estudiante';
        buscarUsuarios(rol, termino);
    }

    function handleSearchAsignados(e) {
        const termino = e.target.value.trim().toLowerCase();
        const cards = document.querySelectorAll('#asignados-list .assigned-card');
        cards.forEach(card => {
            const nombreEstudiante = card.querySelector('.nombre-estudiante').textContent.toLowerCase();
            const nombreAcudiente = card.querySelector('.nombre-acudiente').textContent.toLowerCase();
            const parentesco = card.querySelector('.parentesco').textContent.toLowerCase();
            const isVisible = nombreEstudiante.includes(termino) || nombreAcudiente.includes(termino) || parentesco.includes(termino);
            card.style.display = isVisible ? '' : 'none';
        });
    }

    async function buscarUsuarios(rol, termino) {
        const listContainerId = rol === 'acudiente' ? 'acudientes-list' : 'estudiantes-list';
        const listContainer = document.getElementById(listContainerId);
        listContainer.innerHTML = '<p class="text-center text-muted">Buscando...</p>';

        const formData = new FormData();
        formData.append('accion', 'buscar');
        formData.append('rol', rol);
        formData.append('termino', termino);

        try {
            const response = await fetch('<?= basename($_SERVER['PHP_SELF']) ?>', { method: 'POST', body: formData });
            const result = await response.json();
            if (result.status === 'success') {
                actualizarListaUsuarios(rol, result.usuarios);
            } else {
                listContainer.innerHTML = `<p class="text-center text-danger">${result.message}</p>`;
            }
        } catch (error) {
            console.error('Error de conexión en búsqueda:', error);
            listContainer.innerHTML = '<p class="text-center text-danger">Error de conexión.</p>';
        }
    }

    function actualizarListaUsuarios(rol, usuarios) {
        const listContainerId = rol === 'acudiente' ? 'acudientes-list' : 'estudiantes-list';
        const listContainer = document.getElementById(listContainerId);
        listContainer.innerHTML = ''; 

        if (usuarios.length === 0) {
            listContainer.innerHTML = '<p class="text-center text-muted">No se encontraron resultados.</p>';
            return;
        }

        const readfileUrl = "<?= htmlspecialchars(rtrim(READFILE_URL, '/') . '/foto/') ?>";
        usuarios.forEach(usuario => {
            const card = document.createElement('div');
            card.className = 'user-card';
            card.draggable = true;
            card.dataset.id = usuario.id_usuario;
            card.dataset.type = rol;
            card.dataset.telefono = usuario.telefono || '';
            card.innerHTML = `
                <img src="${readfileUrl}${usuario.foto}" alt="Foto">
                <div>
                    <span>${usuario.nombre} ${usuario.apellido}</span>
                    <span class="user-phone"><span class="glyphicon glyphicon-phone-alt"></span> ${usuario.telefono || 'sin telefono'}</span>
                </div>
            `;
            addDragEventsToCard(card); 
            listContainer.appendChild(card);
        });
    }

    async function asignarRelacion(idEstudiante, idAcudiente, parentesco, nombreEstudiante, nombreAcudiente, telefonoEstudiante, telefonoAcudiente) {
        const formData = new FormData();
        formData.append('accion', 'asignar');
        formData.append('id_estudiante', idEstudiante);
        formData.append('id_acudiente', idAcudiente);
        formData.append('parentesco', parentesco);

        try {
            const response = await fetch('<?= basename($_SERVER['PHP_SELF']) ?>', { method: 'POST', body: formData });
            const result = await response.json();
            if (result.status === 'success') {
                const cardToRemove = document.querySelector(`#estudiantes-list .user-card[data-id="${idEstudiante}"]`);
                if(cardToRemove) cardToRemove.remove();
                
                const newCard = document.createElement('div');
                newCard.className = 'assigned-card';
                newCard.id = `relacion-${result.new_id}`;
                newCard.innerHTML = `
                    <strong>Estudiante:</strong> <span class="nombre-estudiante">${nombreEstudiante}</span> <small>(Tel: ${telefonoEstudiante || 'sin telefono'})</small><br>
                    <strong>Acudiente:</strong> <span class="nombre-acudiente">${nombreAcudiente}</span> <small>(Tel: ${telefonoAcudiente || 'sin telefono'})</small><br>
                    <em>Parentesco: <span class="parentesco">${parentesco}</span></em>
                    <button class="remove-relation-btn" data-relacion-id="${result.new_id}" title="Eliminar relación">&times;</button>
                `;
                document.getElementById('asignados-list').appendChild(newCard);
                alert(result.message);
            } else {
                alert('Error: ' + result.message);
            }
        } catch (error) {
            console.error('Error de conexión al asignar:', error);
            alert('Error de conexión al asignar la relación.');
        }
    }

    async function eliminarRelacion(idRelacion) {
        const formData = new FormData();
        formData.append('accion', 'eliminar');
        formData.append('id_relacion', idRelacion);
        
        try {
            const response = await fetch('<?= basename($_SERVER['PHP_SELF']) ?>', { method: 'POST', body: formData });
            const result = await response.json();
            if (result.status === 'success') {
                const cardToRemove = document.getElementById(`relacion-${idRelacion}`);
                if(cardToRemove) cardToRemove.remove();
                alert(result.message);
                // Opcional: Recargar la lista de estudiantes sin acudiente
                buscarUsuarios('estudiante', '');
            } else {
                alert('Error: ' + result.message);
            }
        } catch (error) {
            console.error('Error de conexión al eliminar:', error);
            alert('Error de conexión al eliminar la relación.');
        }
    }
    
    // --- MANEJO DE IMPORTACIÓN DE CSV ---
    const formImport = document.getElementById('form-import');
    const formProcessImport = document.getElementById('form-process-import');
    const importResultsContainer = document.getElementById('import-results');
    const existentesContainer = document.getElementById('existentes-container');
    const nuevosContainer = document.getElementById('nuevos-container');
    const importSpinner = document.getElementById('import-spinner');

    formImport.addEventListener('submit', async function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        formData.append('accion', 'preview_import');
        
        importSpinner.style.display = 'block';
        importResultsContainer.style.display = 'none';

        try {
            const response = await fetch('<?= basename($_SERVER['PHP_SELF']) ?>', { method: 'POST', body: formData });
            const result = await response.json();
            
            importSpinner.style.display = 'none';

            if (result.status === 'success') {
                mostrarResultadosImportacion(result.nuevos, result.existentes);
            } else {
                alert('Error: ' + result.message);
            }
        } catch (error) {
            importSpinner.style.display = 'none';
            console.error('Error al validar CSV:', error);
            alert('Error de conexión al validar el archivo.');
        }
    });
    
    function mostrarResultadosImportacion(nuevos, existentes) {
        nuevosContainer.innerHTML = `<input type="hidden" name="nuevos" value='${JSON.stringify(nuevos)}'>`;
        
        if (existentes.length > 0) {
            let tablaHTML = `
                <h5><span class="glyphicon glyphicon-warning-sign"></span> Relaciones Existentes</h5>
                <p>Los siguientes estudiantes ya tienen un acudiente. Marque la casilla si desea <strong>reemplazar</strong> la relación actual con la del archivo.</p>
                <table class="table table-bordered">
                    <thead><tr><th>Actualizar</th><th>ID Estudiante</th><th>Acudiente Actual</th><th>Nuevo Acudiente (del archivo)</th><th>Nuevo Parentesco</th></tr></thead>
                    <tbody>`;
            existentes.forEach(rel => {
                tablaHTML += `
                    <tr>
                        <td><input type="checkbox" name="actualizar[]" value='${JSON.stringify(rel)}'></td>
                        <td>${rel.id_estudiante}</td>
                        <td>${rel.acudiente_actual}</td>
                        <td>${rel.id_acudiente}</td>
                        <td>${rel.parentesco}</td>
                    </tr>`;
            });
            tablaHTML += '</tbody></table>';
            existentesContainer.innerHTML = tablaHTML;
        } else {
            existentesContainer.innerHTML = '<p class="text-info">No se encontraron relaciones existentes para actualizar. Todas las relaciones del archivo son nuevas.</p>';
        }
        
        importResultsContainer.style.display = 'block';
    }

    formProcessImport.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const nuevosJSON = this.querySelector('input[name="nuevos"]').value;
        const actualizarCheckboxes = this.querySelectorAll('input[name="actualizar[]"]:checked');
        
        let actualizar = [];
        actualizarCheckboxes.forEach(cb => {
            actualizar.push(JSON.parse(cb.value));
        });
        
        const formData = new FormData();
        formData.append('accion', 'process_import');
        formData.append('nuevos', nuevosJSON);
        formData.append('actualizar', JSON.stringify(actualizar));

        importSpinner.style.display = 'block';

        try {
            const response = await fetch('<?= basename($_SERVER['PHP_SELF']) ?>', { method: 'POST', body: formData });
            const result = await response.json();
            
            importSpinner.style.display = 'none';

            if (result.status === 'success') {
                alert(result.message);
                location.reload(); // Recargar la página para ver los cambios
            } else {
                alert('Error: ' + result.message);
            }
        } catch (error) {
            importSpinner.style.display = 'none';
            console.error('Error al procesar importación:', error);
            alert('Error de conexión al procesar la importación.');
        }
    });
});
</script>
<style>
.gly-spin {
    -webkit-animation: spin 2s infinite linear;
    -moz-animation: spin 2s infinite linear;
    -o-animation: spin 2s infinite linear;
    animation: spin 2s infinite linear;
}
@-moz-keyframes spin {
    0% {
        -moz-transform: rotate(0deg);
    }
    100% {
        -moz-transform: rotate(359deg);
    }
}
@-webkit-keyframes spin {
    0% {
        -webkit-transform: rotate(0deg);
    }
    100% {
        -webkit-transform: rotate(359deg);
    }
}
@-o-keyframes spin {
    0% {
        -o-transform: rotate(0deg);
    }
    100% {
        -o-transform: rotate(359deg);
    }
}
@keyframes spin {
    0% {
        -webkit-transform: rotate(0deg);
        transform: rotate(0deg);
    }
    100% {
        -webkit-transform: rotate(359deg);
        transform: rotate(359deg);
    }
}
</style>
<?php
// --- FIN DEL CONTENIDO ---
$contenido = ob_get_clean();
include "../comun/plantilla.php";
?>


