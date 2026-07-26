<?php
/**
 * =================================================================
 * MÓDULO CRUD - GESTIÓN DE USUARIOS (Sede Vallesol)
 * =================================================================
 * Arquitectura:
 * - Búsqueda Asíncrona (Fetch API + Debounce) Case-Insensitive.
 * - Paginación dinámica (Backend + Frontend).
 * - Persistencia: $mysqli->query() con validación real_escape_string.
 * - UX/UI: Layout Stacked (Formulario Superior / Tabla Inferior).
 */

ob_start();
session_start();

// Inclusión de dependencias (Asegúrate de que este archivo exista y configure $mysqli)
require_once("../comun/conexion.php");

// =================================================================
// 1. ENDPOINT PARA BÚSQUEDA ASÍNCRONA Y PAGINACIÓN (AJAX)
// =================================================================
if (isset($_GET['ajax_search'])) {
    $busqueda = trim($_GET['ajax_search']);
    $pagina = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    renderizar_tabla_usuarios($busqueda, $pagina, $mysqli);
    exit;
}

// =================================================================
// 2. FUNCIÓN DE RENDERIZADO DE TABLA Y PAGINACIÓN
// =================================================================
function renderizar_tabla_usuarios($busqueda, $pagina, $mysqli) {
    $limite = 10; // Cantidad de registros por página
    if ($pagina < 1) $pagina = 1;
    $offset = ($pagina - 1) * $limite;

    $where = "WHERE 1=1";
    if ($busqueda !== '') {
        $busqueda_esc = strtolower($mysqli->real_escape_string($busqueda));
        $where .= " AND (LOWER(nombre) LIKE '%$busqueda_esc%' 
                     OR LOWER(apellido) LIKE '%$busqueda_esc%' 
                     OR id_usuario LIKE '%$busqueda_esc%' 
                     OR LOWER(usuario) LIKE '%$busqueda_esc%')";
    }

    // Contar total de registros
    $query_count = $mysqli->query("SELECT COUNT(*) as total FROM usuario $where");
    $total_registros = $query_count->fetch_assoc()['total'];
    $total_paginas = ceil($total_registros / $limite);

    // Obtener registros de la página actual
    $query = $mysqli->query("SELECT * FROM usuario $where ORDER BY fecha_creacion DESC LIMIT $limite OFFSET $offset");

    if ($query->num_rows > 0) {
        echo '<div class="overflow-x-auto rounded-xl border border-slate-200 shadow-sm">';
        echo '<table class="w-full text-left text-sm text-slate-600">';
        echo '<thead class="bg-slate-50 text-xs uppercase text-slate-500 font-semibold border-b border-slate-200">';
        echo '<tr>
                <th class="px-6 py-4">Documento</th>
                <th class="px-6 py-4">Usuario</th>
                <th class="px-6 py-4">Nombre Completo</th>
                <th class="px-6 py-4">Rol</th>
                <th class="px-6 py-4">Estado</th>
                <th class="px-6 py-4 text-center">Acciones</th>
              </tr>';
        echo '</thead>';
        echo '<tbody class="divide-y divide-slate-100 bg-white">';
        
        while ($row = $query->fetch_assoc()) {
            $estado_class = $row['estado'] == 'activo' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700';
            $nombre_completo = htmlspecialchars($row['nombre'] . ' ' . $row['apellido']);
            
            echo '<tr class="hover:bg-slate-50 transition-colors">';
            echo '<td class="px-6 py-4 font-medium text-slate-900">' . htmlspecialchars($row['id_usuario']) . '</td>';
            echo '<td class="px-6 py-4">' . htmlspecialchars($row['usuario']) . '</td>';
            echo '<td class="px-6 py-4">' . $nombre_completo . '</td>';
            echo '<td class="px-6 py-4 capitalize">' . htmlspecialchars($row['rol']) . '</td>';
            echo '<td class="px-6 py-4"><span class="px-3 py-1 rounded-full text-xs font-bold ' . $estado_class . '">' . htmlspecialchars($row['estado']) . '</span></td>';
            echo '<td class="px-6 py-4 text-center">
                    <div class="flex items-center justify-center gap-2">
                        <a href="usuario.php?Actualizar=' . htmlspecialchars($row['id_usuario']) . '" class="text-blue-500 hover:text-blue-700 p-2 hover:bg-blue-50 rounded-lg transition-colors" title="Editar">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                        </a>
                        <a href="#" onclick="confirmarEliminacion(\'' . htmlspecialchars($row['id_usuario']) . '\')" class="text-red-500 hover:text-red-700 p-2 hover:bg-red-50 rounded-lg transition-colors" title="Eliminar">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                        </a>
                    </div>
                  </td>';
            echo '</tr>';
        }
        echo '</tbody></table></div>';

        // Paginación
        if ($total_paginas > 1) {
            echo '<div class="flex items-center justify-between mt-6">';
            echo '<span class="text-sm text-slate-500">Mostrando página ' . $pagina . ' de ' . $total_paginas . '</span>';
            echo '<div class="flex gap-1">';
            
            if ($pagina > 1) {
                echo '<button class="page-link px-3 py-1 rounded-md border border-slate-200 text-slate-600 hover:bg-slate-50 transition-colors" data-page="' . ($pagina - 1) . '">Anterior</button>';
            }
            
            for ($i = max(1, $pagina - 2); $i <= min($total_paginas, $pagina + 2); $i++) {
                $active = $i === $pagina ? 'bg-blue-600 text-white border-blue-600' : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50';
                echo '<button class="page-link px-3 py-1 rounded-md border transition-colors ' . $active . '" data-page="' . $i . '">' . $i . '</button>';
            }

            if ($pagina < $total_paginas) {
                echo '<button class="page-link px-3 py-1 rounded-md border border-slate-200 text-slate-600 hover:bg-slate-50 transition-colors" data-page="' . ($pagina + 1) . '">Siguiente</button>';
            }
            echo '</div></div>';
        }
    } else {
        echo '<div class="p-8 text-center bg-slate-50 rounded-xl border border-slate-200 border-dashed">
                <svg class="w-12 h-12 text-slate-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <p class="text-slate-500 font-medium">No se encontraron usuarios que coincidan con la búsqueda.</p>
              </div>';
    }
}

// =================================================================
// 3. LÓGICA DE TRANSACCIONES CRUD (Con $mysqli->query)
// =================================================================
$alerta_js = '';

// Funciones auxiliares para manejar campos vacíos o nulos en SQL
function nullify($value, $mysqli) {
    $val = trim($value);
    return $val === '' ? "NULL" : "'" . $mysqli->real_escape_string($val) . "'";
}

// A. CREAR
if (isset($_POST['Ingresar']) && !empty($_POST['id_usuario'])) {
    $id_usuario       = $mysqli->real_escape_string(trim($_POST['id_usuario']));
    $tipo_documento   = $mysqli->real_escape_string(trim($_POST['tipo_documento'] ?? ''));
    $usuario          = $mysqli->real_escape_string(trim($_POST['usuario']));
    $nombre           = $mysqli->real_escape_string(trim($_POST['nombre']));
    $apellido         = $mysqli->real_escape_string(trim($_POST['apellido']));
    $rol              = $mysqli->real_escape_string(trim($_POST['rol']));
    $genero           = $mysqli->real_escape_string(trim($_POST['genero'] ?? 'f'));
    $estado           = $mysqli->real_escape_string(trim($_POST['estado']));
    $correo           = nullify($_POST['correo'] ?? '', $mysqli);
    $direccion        = nullify($_POST['direccion'] ?? '', $mysqli);
    $telefono         = nullify($_POST['telefono'] ?? '', $mysqli);
    $tipo_sangre      = nullify($_POST['tipo_sangre'] ?? '', $mysqli);
    $mascota          = $mysqli->real_escape_string(trim($_POST['mascota'] ?? 'NO'));
    $fecha_nacimiento = nullify($_POST['fecha_nacimiento'] ?? '', $mysqli);
    $fecha_retiro     = nullify($_POST['fecha_retiro'] ?? '', $mysqli);
    $observaciones    = nullify($_POST['observaciones'] ?? '', $mysqli);
    $clave            = sha1($id_usuario); 

    $sql = "INSERT INTO usuario (id_usuario, tipo_documento, usuario, clave, nombre, apellido, rol, genero, estado, correo, direccion, telefono, tipo_sangre, mascota, fecha_nacimiento, fecha_retiro, observaciones) 
            VALUES ('$id_usuario', '$tipo_documento', '$usuario', '$clave', '$nombre', '$apellido', '$rol', '$genero', '$estado', $correo, $direccion, $telefono, $tipo_sangre, '$mascota', $fecha_nacimiento, $fecha_retiro, $observaciones)";
            
    if ($mysqli->query($sql)) {
        $alerta_js = "Swal.fire('¡Registrado!', 'El usuario ha sido creado con éxito.', 'success');";
    } else {
        if ($mysqli->errno == 1062) {
            $alerta_js = "Swal.fire('¡Error!', 'El documento de identidad ya está registrado.', 'error');";
        } else {
            $alerta_js = "Swal.fire('¡Error!', 'Ocurrió un error al procesar la solicitud: ".$mysqli->error."', 'error');";
        }
    }
}

// B. ACTUALIZAR
if (isset($_POST['Actualizar_Registro']) && !empty($_POST['id_usuario_original'])) {
    $id_original      = $mysqli->real_escape_string(trim($_POST['id_usuario_original']));
    $id_usuario       = $mysqli->real_escape_string(trim($_POST['id_usuario']));
    $tipo_documento   = $mysqli->real_escape_string(trim($_POST['tipo_documento'] ?? ''));
    $usuario          = $mysqli->real_escape_string(trim($_POST['usuario']));
    $nombre           = $mysqli->real_escape_string(trim($_POST['nombre']));
    $apellido         = $mysqli->real_escape_string(trim($_POST['apellido']));
    $rol              = $mysqli->real_escape_string(trim($_POST['rol']));
    $genero           = $mysqli->real_escape_string(trim($_POST['genero'] ?? 'f'));
    $estado           = $mysqli->real_escape_string(trim($_POST['estado']));
    $correo           = nullify($_POST['correo'] ?? '', $mysqli);
    $direccion        = nullify($_POST['direccion'] ?? '', $mysqli);
    $telefono         = nullify($_POST['telefono'] ?? '', $mysqli);
    $tipo_sangre      = nullify($_POST['tipo_sangre'] ?? '', $mysqli);
    $mascota          = $mysqli->real_escape_string(trim($_POST['mascota'] ?? 'NO'));
    $fecha_nacimiento = nullify($_POST['fecha_nacimiento'] ?? '', $mysqli);
    $fecha_retiro     = nullify($_POST['fecha_retiro'] ?? '', $mysqli);
    $observaciones    = nullify($_POST['observaciones'] ?? '', $mysqli);
    
    $sql = "UPDATE usuario 
            SET id_usuario='$id_usuario', 
                tipo_documento='$tipo_documento',
                usuario='$usuario', 
                nombre='$nombre', 
                apellido='$apellido', 
                rol='$rol', 
                genero='$genero', 
                estado='$estado', 
                correo=$correo,
                direccion=$direccion,
                telefono=$telefono,
                tipo_sangre=$tipo_sangre,
                mascota='$mascota',
                fecha_nacimiento=$fecha_nacimiento,
                fecha_retiro=$fecha_retiro,
                observaciones=$observaciones
            WHERE id_usuario='$id_original'";
            
    if ($mysqli->query($sql)) {
        $alerta_js = "Swal.fire('¡Actualizado!', 'Los datos se modificaron correctamente.', 'success');";
    } else {
        $alerta_js = "Swal.fire('¡Error!', 'No se pudo actualizar la información.', 'error');";
    }
}

// C. ELIMINAR
if (isset($_GET['action']) && $_GET['action'] == 'delete' && !empty($_GET['id'])) {
    $id = $mysqli->real_escape_string($_GET['id']);
    $sql = "DELETE FROM usuario WHERE id_usuario = '$id'";
    
    if ($mysqli->query($sql)) {
        $alerta_js = "Swal.fire('¡Eliminado!', 'El usuario ha sido retirado del sistema.', 'success');";
    }
}

// =================================================================
// 4. RECUPERAR DATOS PARA EDICIÓN
// =================================================================
$modo_edicion = false;
$datos_editar = [
    'id_usuario' => '', 'tipo_documento' => 'CC', 'usuario' => '', 'nombre' => '', 'apellido' => '', 
    'rol' => 'estudiante', 'genero' => 'f', 'estado' => 'activo', 'correo' => '',
    'direccion' => '', 'telefono' => '', 'tipo_sangre' => '', 'mascota' => 'NO', 
    'fecha_nacimiento' => '', 'fecha_retiro' => '', 'observaciones' => ''
];

if (isset($_GET['Actualizar']) && !empty($_GET['Actualizar'])) {
    $modo_edicion = true;
    $id_editar = $mysqli->real_escape_string($_GET['Actualizar']);
    
    $sql_edit = "SELECT * FROM usuario WHERE id_usuario = '$id_editar'";
    $res_edit = $mysqli->query($sql_edit);
    
    if ($res_edit && $res_edit->num_rows > 0) {
        $datos_editar = array_merge($datos_editar, $res_edit->fetch_assoc());
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Usuarios - Sede Vallesol</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        /* Loader Animation */
        .loader-line {
            width: 100%;
            height: 3px;
            background-color: #e2e8f0;
            position: relative;
            overflow: hidden;
        }
        .loader-line::after {
            content: "";
            position: absolute;
            left: -50%;
            width: 50%;
            height: 100%;
            background-color: #3b82f6;
            animation: loading 1s infinite linear;
        }
        @keyframes loading {
            0% { left: -50%; }
            100% { left: 100%; }
        }
    </style>
</head>
<body class="bg-slate-50 min-h-screen p-4 md:p-8 font-sans">

    <div class="max-w-7xl mx-auto space-y-8">
        
        <!-- CABECERA -->
        <header class="flex justify-between items-center bg-white p-6 rounded-2xl shadow-sm border border-slate-100">
            <div>
                <h1 class="text-2xl font-black text-slate-800">Gestión de Usuarios</h1>
                <p class="text-slate-500 text-sm mt-1">Administración de perfiles y accesos de la Sede Vallesol</p>
            </div>
            <div class="hidden sm:block">
                <svg class="w-12 h-12 text-blue-500 opacity-20" fill="currentColor" viewBox="0 0 24 24"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg>
            </div>
        </header>

        <!-- FORMULARIO DE REGISTRO / EDICIÓN -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden transition-all duration-300">
            <div class="p-6 md:p-8">
                <div class="flex items-center gap-3 mb-6 border-b border-slate-100 pb-4">
                    <div class="p-2 <?php echo $modo_edicion ? 'bg-amber-100 text-amber-600' : 'bg-blue-100 text-blue-600'; ?> rounded-lg">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="<?php echo $modo_edicion ? 'M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z' : 'M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z'; ?>"></path></svg>
                    </div>
                    <h2 class="text-lg font-bold text-slate-800">
                        <?php echo $modo_edicion ? 'Editar Perfil de Usuario' : 'Nuevo Registro de Usuario'; ?>
                    </h2>
                </div>
                
                <form method="POST" action="usuario.php" class="space-y-8">
                    <?php if($modo_edicion): ?>
                        <input type="hidden" name="id_usuario_original" value="<?php echo htmlspecialchars($datos_editar['id_usuario']); ?>">
                    <?php endif; ?>

                    <!-- SECCIÓN: Información Personal -->
                    <div>
                        <h3 class="text-sm font-semibold text-slate-700 mb-4 border-b border-slate-100 pb-2">Información Personal</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                            
                            <div>
                                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Tipo de Doc.</label>
                                <select name="tipo_documento" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 text-slate-900 text-sm rounded-xl focus:ring-2 focus:ring-blue-500 outline-none transition-all">
                                    <option value="CC" <?php echo ($datos_editar['tipo_documento'] == 'CC') ? 'selected' : ''; ?>>Cédula (CC)</option>
                                    <option value="TI" <?php echo ($datos_editar['tipo_documento'] == 'TI') ? 'selected' : ''; ?>>Tarjeta Identidad (TI)</option>
                                    <option value="CE" <?php echo ($datos_editar['tipo_documento'] == 'CE') ? 'selected' : ''; ?>>Cédula Extranjería (CE)</option>
                                    <option value="RC" <?php echo ($datos_editar['tipo_documento'] == 'RC') ? 'selected' : ''; ?>>Reg. Civil (RC)</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Documento <span class="text-red-500">*</span></label>
                                <input type="text" name="id_usuario" required 
                                       value="<?php echo htmlspecialchars($datos_editar['id_usuario']); ?>"
                                       class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 text-slate-900 text-sm rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all">
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Nombres <span class="text-red-500">*</span></label>
                                <input type="text" name="nombre" required 
                                       value="<?php echo htmlspecialchars($datos_editar['nombre']); ?>"
                                       class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 text-slate-900 text-sm rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all">
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Apellidos <span class="text-red-500">*</span></label>
                                <input type="text" name="apellido" required 
                                       value="<?php echo htmlspecialchars($datos_editar['apellido']); ?>"
                                       class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 text-slate-900 text-sm rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all">
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Género</label>
                                <select name="genero" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 text-slate-900 text-sm rounded-xl focus:ring-2 focus:ring-blue-500 outline-none transition-all">
                                    <option value="m" <?php echo ($datos_editar['genero'] == 'm') ? 'selected' : ''; ?>>Masculino</option>
                                    <option value="f" <?php echo ($datos_editar['genero'] == 'f') ? 'selected' : ''; ?>>Femenino</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Fecha Nacimiento</label>
                                <input type="date" name="fecha_nacimiento" 
                                       value="<?php echo htmlspecialchars($datos_editar['fecha_nacimiento']); ?>"
                                       class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 text-slate-900 text-sm rounded-xl focus:ring-2 focus:ring-blue-500 outline-none transition-all">
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Tipo Sangre</label>
                                <select name="tipo_sangre" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 text-slate-900 text-sm rounded-xl focus:ring-2 focus:ring-blue-500 outline-none transition-all">
                                    <option value="">Seleccione...</option>
                                    <?php 
                                    $tipos = ['O+','O-','A+','A-','B+','B-','AB+','AB-'];
                                    foreach($tipos as $t) {
                                        $sel = ($datos_editar['tipo_sangre'] == $t) ? 'selected' : '';
                                        echo "<option value=\"$t\" $sel>$t</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- SECCIÓN: Contacto -->
                    <div>
                        <h3 class="text-sm font-semibold text-slate-700 mb-4 border-b border-slate-100 pb-2">Datos de Contacto</h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div>
                                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Correo Electrónico</label>
                                <input type="email" name="correo" 
                                       value="<?php echo htmlspecialchars($datos_editar['correo']); ?>"
                                       class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 text-slate-900 text-sm rounded-xl focus:ring-2 focus:ring-blue-500 outline-none transition-all">
                            </div>
                            
                            <div>
                                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Teléfono</label>
                                <input type="text" name="telefono" 
                                       value="<?php echo htmlspecialchars($datos_editar['telefono']); ?>"
                                       class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 text-slate-900 text-sm rounded-xl focus:ring-2 focus:ring-blue-500 outline-none transition-all">
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Dirección</label>
                                <input type="text" name="direccion" 
                                       value="<?php echo htmlspecialchars($datos_editar['direccion']); ?>"
                                       class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 text-slate-900 text-sm rounded-xl focus:ring-2 focus:ring-blue-500 outline-none transition-all">
                            </div>
                        </div>
                    </div>

                    <!-- SECCIÓN: Sistema -->
                    <div>
                        <h3 class="text-sm font-semibold text-slate-700 mb-4 border-b border-slate-100 pb-2">Sistema y Cuenta</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6">
                            
                            <div>
                                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Usuario <span class="text-red-500">*</span></label>
                                <input type="text" name="usuario" required 
                                       value="<?php echo htmlspecialchars($datos_editar['usuario']); ?>"
                                       class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 text-slate-900 text-sm rounded-xl focus:ring-2 focus:ring-blue-500 outline-none transition-all">
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Rol</label>
                                <select name="rol" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 text-slate-900 text-sm rounded-xl focus:ring-2 focus:ring-blue-500 outline-none transition-all">
                                    <option value="estudiante" <?php echo ($datos_editar['rol'] == 'estudiante') ? 'selected' : ''; ?>>Estudiante</option>
                                    <option value="docente" <?php echo ($datos_editar['rol'] == 'docente') ? 'selected' : ''; ?>>Docente</option>
                                    <option value="admin" <?php echo ($datos_editar['rol'] == 'admin') ? 'selected' : ''; ?>>Administrador</option>
                                    <option value="acudiente" <?php echo ($datos_editar['rol'] == 'acudiente') ? 'selected' : ''; ?>>Acudiente</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Estado</label>
                                <select name="estado" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 text-slate-900 text-sm rounded-xl focus:ring-2 focus:ring-blue-500 outline-none transition-all">
                                    <option value="activo" <?php echo ($datos_editar['estado'] == 'activo') ? 'selected' : ''; ?>>Activo</option>
                                    <option value="inactivo" <?php echo ($datos_editar['estado'] == 'inactivo') ? 'selected' : ''; ?>>Inactivo</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Mascota Institucional</label>
                                <select name="mascota" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 text-slate-900 text-sm rounded-xl focus:ring-2 focus:ring-blue-500 outline-none transition-all">
                                    <option value="NO" <?php echo ($datos_editar['mascota'] == 'NO') ? 'selected' : ''; ?>>NO</option>
                                    <option value="SI" <?php echo ($datos_editar['mascota'] == 'SI') ? 'selected' : ''; ?>>SÍ</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Fecha Retiro</label>
                                <input type="date" name="fecha_retiro" 
                                       value="<?php echo htmlspecialchars($datos_editar['fecha_retiro']); ?>"
                                       class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 text-slate-900 text-sm rounded-xl focus:ring-2 focus:ring-blue-500 outline-none transition-all text-slate-500">
                            </div>
                        </div>
                    </div>
                    
                    <!-- SECCIÓN: Observaciones -->
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Observaciones Médicas / Generales</label>
                        <textarea name="observaciones" rows="3" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 text-slate-900 text-sm rounded-xl focus:ring-2 focus:ring-blue-500 outline-none transition-all resize-none"><?php echo htmlspecialchars($datos_editar['observaciones']); ?></textarea>
                    </div>

                    <div class="flex justify-end gap-3 pt-6 border-t border-slate-100">
                        <?php if($modo_edicion): ?>
                            <a href="usuario.php" class="text-slate-700 bg-slate-100 hover:bg-slate-200 font-semibold rounded-xl text-sm px-6 py-3 transition-all">Cancelar</a>
                            <button type="submit" name="Actualizar_Registro" class="text-white bg-blue-600 hover:bg-blue-700 font-semibold rounded-xl text-sm px-8 py-3 shadow-sm hover:shadow transition-all">Guardar Cambios</button>
                        <?php else: ?>
                            <button type="submit" name="Ingresar" class="text-white bg-slate-900 hover:bg-slate-800 font-semibold rounded-xl text-sm px-8 py-3 shadow-sm hover:shadow transition-all">Crear Perfil</button>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>

        <!-- BLOQUE INFERIOR: Listado y Búsqueda -->
        <div class="bg-white p-6 md:p-8 rounded-2xl shadow-sm border border-slate-100">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6 border-b border-slate-100 pb-4">
                <h3 class="text-lg font-bold text-slate-800 flex items-center gap-2">
                    <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                    Directorio de Usuarios
                </h3>
                
                <div class="relative w-full md:w-96">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    </div>
                    <input type="text" id="searchInput" placeholder="Buscar por documento, nombre o usuario..." class="block w-full pl-10 pr-10 py-2.5 bg-slate-50 border border-slate-200 text-sm rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all placeholder-slate-400">
                    <div id="searchSpinner" class="hidden absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                        <svg class="animate-spin h-5 w-5 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                    </div>
                </div>
            </div>

            <!-- Loader superior de la tabla -->
            <div id="loaderLine" class="loader-line hidden mb-4 rounded-full"></div>

            <!-- Contenedor dinámico de la tabla -->
            <div id="tableContainer" class="transition-opacity duration-300">
                <!-- Se cargará por AJAX o inicialmente en PHP -->
                <?php renderizar_tabla_usuarios('', 1, $mysqli); ?>
            </div>
        </div>

    </div>

    <!-- SCRIPTS -->
    <script>
        // Alertas de PHP a JS usando SweetAlert2
        <?php if (!empty($alerta_js)) echo $alerta_js; ?>

        // Confirmación de eliminación
        function confirmarEliminacion(id) {
            Swal.fire({
                title: '¿Estás seguro?',
                text: "Esta acción no se puede deshacer y el usuario será eliminado.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar',
                customClass: {
                    confirmButton: 'rounded-xl',
                    cancelButton: 'rounded-xl'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = `usuario.php?action=delete&id=${id}`;
                }
            })
        }

        // Lógica de Búsqueda Asíncrona y Paginación (Debounce + Fetch)
        document.addEventListener('DOMContentLoaded', () => {
            const searchInput = document.getElementById('searchInput');
            const tableContainer = document.getElementById('tableContainer');
            const searchSpinner = document.getElementById('searchSpinner');
            const loaderLine = document.getElementById('loaderLine');
            let debounceTimer;
            let currentPage = 1;

            const fetchData = async (query, page) => {
                // UI Feedback
                searchSpinner.classList.remove('hidden');
                loaderLine.classList.remove('hidden');
                tableContainer.style.opacity = '0.5';

                try {
                    const response = await fetch(`usuario.php?ajax_search=${encodeURIComponent(query)}&page=${page}`);
                    if (!response.ok) throw new Error('Error en red');
                    const html = await response.text();
                    
                    tableContainer.innerHTML = html;
                } catch (error) {
                    console.error('Error fetching data:', error);
                    Swal.fire({
                        toast: true, position: 'top-end', showConfirmButton: false, 
                        timer: 3000, icon: 'error', title: 'Error de conexión'
                    });
                } finally {
                    // Restaurar UI
                    searchSpinner.classList.add('hidden');
                    loaderLine.classList.add('hidden');
                    tableContainer.style.opacity = '1';
                }
            };

            // Evento de escritura en el input (Debounce)
            searchInput.addEventListener('input', function() {
                const query = this.value.trim();
                currentPage = 1; // Reiniciar a la página 1 al buscar
                
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(() => {
                    fetchData(query, currentPage);
                }, 400); 
            });

            // Delegación de eventos para la paginación
            tableContainer.addEventListener('click', function(e) {
                const btn = e.target.closest('.page-link');
                if (btn) {
                    e.preventDefault();
                    const targetPage = btn.getAttribute('data-page');
                    if (targetPage) {
                        currentPage = targetPage;
                        fetchData(searchInput.value.trim(), currentPage);
                    }
                }
            });
        });
    </script>
</body>
</html>