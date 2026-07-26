<?php
// Iniciar el búfer inmediatamente para atrapar cualquier salida accidental
// de los archivos incluidos que pueda corromper el JSON de la API.
ob_start(); 

require_once("conexion.php");
require_once("funciones.php");

/**
 * ========================================================================
 * 1. BACKEND: API RESTful (Controlador asíncrono)
 * ========================================================================
 */
if (isset($_GET['api'])) {
    // Limpiar cualquier basura del búfer antes de imprimir el JSON
    ob_clean(); 
    header('Content-Type: application/json; charset=utf-8');
    
    $respuesta = ['exito' => false, 'mensaje' => 'Acción no válida', 'datos' => null];
    $accion = $_GET['accion'] ?? '';

    try {
        // Verificar conexión
        if (!isset($mysqli) || $mysqli->connect_error) {
            throw new Exception("Error de conexión a la base de datos.");
        }

        switch ($accion) {
            case 'leer':
                $busqueda = isset($_GET['buscar']) ? $mysqli->real_escape_string($_GET['buscar']) : '';
                $sql = "SELECT id_iconos, icono, imagen_icono FROM iconos";
                if ($busqueda !== '') {
                    $sql .= " WHERE icono LIKE '%$busqueda%'";
                }
                $sql .= " ORDER BY id_iconos DESC LIMIT 50";
                
                $resultado = $mysqli->query($sql);
                if (!$resultado) {
                    throw new Exception("Error en la consulta: " . $mysqli->error);
                }

                $datos = [];
                while ($fila = $resultado->fetch_assoc()) {
                    $datos[] = $fila;
                }
                $respuesta = ['exito' => true, 'datos' => $datos];
                break;

            case 'guardar':
                $id = isset($_POST['id_iconos']) ? intval($_POST['id_iconos']) : 0;
                $icono = isset($_POST['icono']) ? $mysqli->real_escape_string($_POST['icono']) : '';
                
                if (empty($icono)) {
                    throw new Exception("El nombre del icono es obligatorio.");
                }

                $tamaño_maximo = function_exists('tamaño_maximo') ? tamaño_maximo() : 2000000; 
                $formatos = function_exists('formatos') ? formatos() : ['png', 'jpg', 'jpeg', 'gif'];
                
                $archivo_subido = false;
                $nombre_archivo_final = "";
                $directorio_destino = dirname(__FILE__) . "/img/png/";

                // Procesamiento de la imagen si se envió una
                if (isset($_FILES['imagen_icono']) && $_FILES['imagen_icono']['error'] !== UPLOAD_ERR_NO_FILE) {
                    
                    if ($_FILES['imagen_icono']['error'] !== UPLOAD_ERR_OK) {
                        throw new Exception("Error en la subida del archivo. Código de error PHP: " . $_FILES['imagen_icono']['error']);
                    }

                    $nombre_original = $_FILES['imagen_icono']['name'];
                    $ruta_tmp = $_FILES['imagen_icono']['tmp_name'];
                    $ext = strtolower(pathinfo($nombre_original, PATHINFO_EXTENSION));

                    if (!in_array($ext, $formatos)) {
                        throw new Exception("Formato no permitido. Solo: " . implode(', ', $formatos));
                    }
                    if (filesize($ruta_tmp) > $tamaño_maximo) {
                        throw new Exception("El archivo es demasiado pesado.");
                    }

                    // Verificar permisos de carpeta
                    if (!is_dir($directorio_destino)) {
                        if (!@mkdir($directorio_destino, 0777, true)) {
                            throw new Exception("No existe la carpeta destino y no se pudo crear: $directorio_destino");
                        }
                    }
                    if (!is_writable($directorio_destino)) {
                        throw new Exception("La carpeta destino no tiene permisos de escritura: $directorio_destino");
                    }

                    $nombre_archivo_final = preg_replace('/[^A-Za-z0-9\-]/', '', str_replace(' ', '_', $icono)) . '_' . time() . '.' . $ext;
                    $ruta_final = $directorio_destino . $nombre_archivo_final;
                    
                    if (move_uploaded_file($ruta_tmp, $ruta_final)) {
                        $archivo_subido = true;
                    } else {
                        throw new Exception("Error al mover el archivo. Verifique permisos del servidor en: $directorio_destino");
                    }
                }

                if ($id > 0) {
                    // ACTUALIZAR
                    if ($archivo_subido) {
                        // Eliminar imagen anterior
                        $sql_img = "SELECT imagen_icono FROM iconos WHERE id_iconos = $id";
                        $res_img = $mysqli->query($sql_img);
                        if ($row = $res_img->fetch_assoc()) {
                            $ruta_vieja = $directorio_destino . $row['imagen_icono'];
                            if (!empty($row['imagen_icono']) && file_exists($ruta_vieja)) {
                                @unlink($ruta_vieja);
                            }
                        }
                        $sql = "UPDATE iconos SET icono='$icono', imagen_icono='$nombre_archivo_final' WHERE id_iconos=$id";
                    } else {
                        $sql = "UPDATE iconos SET icono='$icono' WHERE id_iconos=$id";
                    }
                } else {
                    // CREAR NUEVO
                    if (!$archivo_subido) {
                        throw new Exception("Debe seleccionar una imagen para el nuevo icono.");
                    }
                    $sql = "INSERT INTO iconos (icono, imagen_icono) VALUES ('$icono', '$nombre_archivo_final')";
                }

                if ($mysqli->query($sql)) {
                    $respuesta = ['exito' => true, 'mensaje' => 'Registro guardado correctamente.'];
                } else {
                    throw new Exception("Error en la base de datos: " . $mysqli->error . " | SQL: " . $sql);
                }
                break;

            case 'eliminar':
                $data = json_decode(file_get_contents("php://input"), true);
                $id = isset($data['id']) ? intval($data['id']) : 0;

                if ($id > 0) {
                    $directorio_destino = dirname(__FILE__) . "/img/png/";
                    $sql_img = "SELECT imagen_icono FROM iconos WHERE id_iconos = $id";
                    $res_img = $mysqli->query($sql_img);
                    if ($row = $res_img->fetch_assoc()) {
                        $ruta_vieja = $directorio_destino . $row['imagen_icono'];
                        if (!empty($row['imagen_icono']) && file_exists($ruta_vieja)) {
                            @unlink($ruta_vieja);
                        }
                    }
                    if ($mysqli->query("DELETE FROM iconos WHERE id_iconos = $id")) {
                        $respuesta = ['exito' => true, 'mensaje' => 'Registro eliminado.'];
                    } else {
                        throw new Exception("No se pudo eliminar el registro: " . $mysqli->error);
                    }
                } else {
                    throw new Exception("ID inválido.");
                }
                break;

            default:
                throw new Exception("Acción no definida.");
        }
    } catch (Exception $e) {
        $respuesta = ['exito' => false, 'mensaje' => $e->getMessage()];
    }

    echo json_encode($respuesta);
    exit; // Fin de la API RESTful
}

/**
 * ========================================================================
 * 2. FRONTEND: Interfaz de Usuario (HTML/JS)
 * ========================================================================
 */
ob_start();
?>

<div class="jumbotron">
  <div class="container text-center">
    <h1 class="fip">Gestión de Iconos (SPA)</h1>      
  </div>
</div>

<div class="container-fluid" id="app-crud-iconos">
    <div class="row">
        <!-- Formulario Dinámico -->
        <div class="col-md-4">
            <div class="panel panel-primary">
                <div class="panel-heading" id="form-titulo">Registrar Nuevo Icono</div>
                <div class="panel-body">
                    <form id="formularioIcono" onsubmit="guardarIcono(event)">
                        <input type="hidden" id="id_iconos" name="id_iconos" value="0">
                        
                        <div class="form-group">
                            <label for="icono">Nombre del Icono:</label>
                            <input type="text" class="form-control" id="icono" name="icono" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="imagen_icono">Imagen (PNG, JPG, GIF):</label>
                            <input type="file" class="form-control" id="imagen_icono" name="imagen_icono" accept="image/*">
                            <small class="text-muted" id="ayuda-imagen">Seleccione un archivo.</small>
                        </div>
                        
                        <button type="submit" class="btn btn-success btn-block" id="btn-guardar">Guardar Registro</button>
                        <button type="button" class="btn btn-default btn-block" onclick="resetFormulario()">Cancelar / Nuevo</button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Lista -->
        <div class="col-md-8">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <div class="row">
                        <div class="col-md-6"><b>Listado de Iconos</b></div>
                        <div class="col-md-6 text-right">
                            <input type="search" id="buscador" class="form-control input-sm" placeholder="Buscar por nombre..." oninput="cargarIconos()">
                        </div>
                    </div>
                </div>
                <div class="panel-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Imagen</th>
                                    <th>Nombre</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="tabla-iconos">
                                <tr><td colspan="4" class="text-center">Cargando datos...</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
/** Funciones de UI comunes **/
function mostrarAlerta(mensaje, tipo = 'success') {
    if (typeof alert2 === 'function') {
        alert2(mensaje, tipo);
    } else {
        alert((tipo === 'error' ? 'ERROR: ' : '') + mensaje);
    }
}

// Carga inicial
document.addEventListener('DOMContentLoaded', () => {
    cargarIconos();
});

// Función para Leer/Buscar
async function cargarIconos() {
    const busqueda = document.getElementById('buscador').value;
    try {
        const respuesta = await fetch(`iconos.php?api=1&accion=leer&buscar=${encodeURIComponent(busqueda)}`);
        const textRAW = await respuesta.text(); // Obtener el texto crudo para depuración
        
        let data;
        try {
            data = JSON.parse(textRAW);
        } catch (e) {
            console.error("JSON Inválido. Respuesta del servidor:", textRAW);
            throw new Error("El servidor devolvió información corrupta. Revise la consola.");
        }

        const tbody = document.getElementById('tabla-iconos');
        tbody.innerHTML = '';

        if (data.exito) {
            if (data.datos.length === 0) {
                tbody.innerHTML = '<tr><td colspan="4" class="text-center">No se encontraron iconos.</td></tr>';
                return;
            }

            data.datos.forEach(icono => {
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td>${icono.id_iconos}</td>
                    <td><img src="img/png/${icono.imagen_icono}" alt="${icono.icono}" width="40" style="border-radius:4px; border:1px solid #ddd; object-fit: cover;"></td>
                    <td>${icono.icono}</td>
                    <td>
                        <button class="btn btn-sm btn-info" onclick="editarIcono(${icono.id_iconos}, '${icono.icono.replace(/'/g, "\\'")}', '${icono.imagen_icono}')">
                            <span class="glyphicon glyphicon-pencil"></span> Editar
                        </button>
                        <button class="btn btn-sm btn-danger" onclick="eliminarIcono(${icono.id_iconos})">
                            <span class="glyphicon glyphicon-trash"></span>
                        </button>
                    </td>
                `;
                tbody.appendChild(tr);
            });
        } else {
            mostrarAlerta(data.mensaje, 'error');
        }
    } catch (error) {
        console.error('Error cargando iconos:', error);
        document.getElementById('tabla-iconos').innerHTML = `<tr><td colspan="4" class="text-center text-danger">${error.message}</td></tr>`;
    }
}

// Función para Guardar (Crear o Actualizar)
async function guardarIcono(event) {
    event.preventDefault();
    
    const form = document.getElementById('formularioIcono');
    const formData = new FormData(form);
    
    const id = formData.get('id_iconos');
    const imagen = document.getElementById('imagen_icono').files[0];

    // Validación básica
    if (id === '0' && (!imagen || imagen.size === 0)) {
        mostrarAlerta('Debe seleccionar una imagen para un nuevo icono', 'error');
        return;
    }

    try {
        document.getElementById('btn-guardar').disabled = true;
        document.getElementById('btn-guardar').innerText = 'Guardando...';

        const respuesta = await fetch('iconos.php?api=1&accion=guardar', {
            method: 'POST',
            body: formData
        });
        
        const textRAW = await respuesta.text(); // Leemos el string crudo primero
        
        let data;
        try {
            data = JSON.parse(textRAW);
        } catch (e) {
            console.error("Respuesta fallida (NO es JSON):", textRAW);
            throw new Error("Respuesta del servidor no válida. Revise la consola para detalles de depuración de PHP.");
        }

        if (data.exito) {
            mostrarAlerta(data.mensaje, 'success');
            resetFormulario();
            cargarIconos();
        } else {
            mostrarAlerta(data.mensaje, 'error');
        }
    } catch (error) {
        console.error('Error al guardar:', error);
        mostrarAlerta(error.message, 'error');
    } finally {
        document.getElementById('btn-guardar').disabled = false;
        document.getElementById('btn-guardar').innerText = id === '0' ? 'Guardar Registro' : 'Actualizar Registro';
    }
}

// Función para Eliminar
async function eliminarIcono(id) {
    if (!confirm('¿Está seguro de eliminar este icono? Esta acción no se puede deshacer.')) return;

    try {
        const respuesta = await fetch('iconos.php?api=1&accion=eliminar', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: id })
        });
        const textRAW = await respuesta.text();
        
        let data;
        try {
            data = JSON.parse(textRAW);
        } catch (e) {
            console.error("Respuesta fallida (NO es JSON):", textRAW);
            throw new Error("Error interno del servidor al eliminar.");
        }

        if (data.exito) {
            mostrarAlerta(data.mensaje, 'success');
            cargarIconos();
            if (document.getElementById('id_iconos').value == id) resetFormulario();
        } else {
            mostrarAlerta(data.mensaje, 'error');
        }
    } catch (error) {
        console.error('Error al eliminar:', error);
        mostrarAlerta(error.message, 'error');
    }
}

// Funciones de UI
function editarIcono(id, nombre, imagen) {
    document.getElementById('form-titulo').innerText = 'Modificar Icono (ID: ' + id + ')';
    document.getElementById('id_iconos').value = id;
    document.getElementById('icono').value = nombre;
    document.getElementById('ayuda-imagen').innerHTML = `Actual: <b>${imagen}</b>. Deje vacío para conservar.`;
    document.getElementById('imagen_icono').removeAttribute('required');
    document.getElementById('btn-guardar').innerText = 'Actualizar Registro';
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

function resetFormulario() {
    document.getElementById('formularioIcono').reset();
    document.getElementById('id_iconos').value = '0';
    document.getElementById('form-titulo').innerText = 'Registrar Nuevo Icono';
    document.getElementById('btn-guardar').innerText = 'Guardar Registro';
    document.getElementById('ayuda-imagen').innerText = 'Seleccione un archivo.';
}
</script>

<?php 
// Renderizado final envolviendo en la plantilla
$contenido = ob_get_contents();
ob_clean();
include ("plantilla.php");
?>