<?php
/**
 * Módulo: Hoja de Vida / Perfil del Estudiante
 * Desarrollado para entorno PHP, MySQL, HTML, CSS (Tailwind) y JS.
 */

// 1. Configuración de la Base de Datos
$host     = '127.0.0.1';
$port     = '7000'; // Puerto indicado en tu archivo SQL
$dbname   = 'guagua';
$username = 'root'; // Cambiar si es necesario
$password = '';     // Cambiar si es necesario

// ID del estudiante a consultar (Simulado vía GET, por defecto '1045231554' para pruebas)
$estudiante_id = $_GET['id'] ?? '1045231554'; 

$estudiante = null;
$acudientes = [];
$error_db   = null;

// 2. Lógica de Conexión y Consultas (Backend PHP)
try {
    $dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";
    $pdo = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);

    // Consulta principal: Obtener datos del estudiante
    $sql_estudiante = "SELECT * FROM usuario WHERE id_usuario = :id_estudiante AND rol LIKE '%estudiante%' LIMIT 1";
    $stmt_est = $pdo->prepare($sql_estudiante);
    $stmt_est->execute(['id_estudiante' => $estudiante_id]);
    $estudiante = $stmt_est->fetch();

    if ($estudiante) {
        // Consulta JOIN: Obtener los acudientes asociados a este estudiante
        $sql_acudientes = "
            SELECT u.*, ae.parentesco 
            FROM acudiente_estudiante ae
            INNER JOIN usuario u ON ae.id_acudiente = u.id_usuario
            WHERE ae.id_estudiante = :id_estudiante
        ";
        $stmt_acu = $pdo->prepare($sql_acudientes);
        $stmt_acu->execute(['id_estudiante' => $estudiante_id]);
        $acudientes = $stmt_acu->fetchAll();
    }

} catch (PDOException $e) {
    // Para entornos de producción, no mostrar el error crudo.
    $error_db = "No se pudo conectar a la base de datos o ejecutar la consulta. Verifica las credenciales. " . $e->getMessage();
}

/**
 * Función auxiliar para formatear texto
 */
function formatoTexto($texto) {
    return htmlspecialchars(trim($texto) !== '' ? trim($texto) : 'No registrado');
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil del Estudiante</title>
    
    <!-- CSS: Tailwind vía CDN para diseño rápido y profesional -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Íconos: FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- CSS Personalizado y reglas de impresión -->
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        body { font-family: 'Inter', sans-serif; }
        
        /* Estilos específicos para al exportar a PDF / Imprimir */
        @media print {
            body { background-color: #ffffff !important; }
            .no-print { display: none !important; }
            .print-shadow-none { box-shadow: none !important; border: 1px solid #e5e7eb; }
            .print-break-inside-avoid { break-inside: avoid; }
        }
    </style>
</head>
<body class="bg-slate-100 text-slate-800 min-h-screen p-4 md:p-8">

    <div class="max-w-5xl mx-auto">
        
        <!-- Alertas de Error -->
        <?php if ($error_db): ?>
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded mb-6 shadow-sm" role="alert">
                <p class="font-bold">Error del Sistema</p>
                <p><?= $error_db ?></p>
            </div>
        <?php endif; ?>

        <?php if (!$estudiante && !$error_db): ?>
            <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-6 rounded-lg text-center shadow-sm">
                <i class="fa-solid fa-user-xmark text-4xl mb-3"></i>
                <h2 class="text-xl font-bold">Estudiante no encontrado</h2>
                <p>No se hallaron registros en la tabla <code>usuario</code> para el documento proporcionado.</p>
            </div>
        <?php elseif ($estudiante): ?>
            
            <?php 
                $nombre_completo = formatoTexto($estudiante['nombre'] . ' ' . $estudiante['apellido']);
                $estado_color = strtolower($estudiante['estado']) === 'activo' ? 'bg-emerald-100 text-emerald-800' : 'bg-red-100 text-red-800';
            ?>

            <!-- Cabecera / Barra superior (Controles JS) -->
            <div class="flex justify-between items-center mb-6 no-print">
                <h1 class="text-2xl font-bold text-slate-700">Expediente Académico</h1>
                <button onclick="imprimirPerfil()" class="bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-2.5 rounded-lg font-medium transition-colors shadow-sm flex items-center gap-2">
                    <i class="fa-solid fa-print"></i> Imprimir Hoja de Vida
                </button>
            </div>

            <!-- Contenedor Principal (Tarjeta CV) -->
            <div class="bg-white rounded-2xl print-shadow-none shadow-xl overflow-hidden">
                
                <!-- Encabezado del Perfil (Hero) -->
                <div class="bg-gradient-to-r from-blue-700 to-indigo-800 px-8 py-10 text-white relative">
                    <div class="flex flex-col md:flex-row items-center md:items-start gap-6 relative z-10">
                        <!-- Foto de perfil -->
                        <div class="w-32 h-32 rounded-full border-4 border-white shadow-lg overflow-hidden bg-white flex-shrink-0 flex items-center justify-center">
                            <?php if(!empty($estudiante['foto']) && $estudiante['foto'] !== 'user-icon.png'): ?>
                                <img src="<?= formatoTexto($estudiante['foto']) ?>" alt="Foto" class="w-full h-full object-cover">
                            <?php else: ?>
                                <i class="fa-solid fa-user-graduate text-5xl text-slate-300"></i>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Info Principal -->
                        <div class="text-center md:text-left pt-2">
                            <h2 class="text-3xl font-bold mb-1"><?= $nombre_completo ?></h2>
                            <p class="text-indigo-200 text-lg mb-3 flex items-center justify-center md:justify-start gap-2">
                                <i class="fa-regular fa-id-card"></i> Doc: <?= formatoTexto($estudiante['id_usuario']) ?>
                            </p>
                            <div class="flex flex-wrap justify-center md:justify-start gap-3 mt-2">
                                <span class="px-3 py-1 rounded-full text-sm font-semibold tracking-wide <?= $estado_color ?> bg-opacity-90">
                                    <i class="fa-solid fa-circle-check mr-1"></i> Estado: <?= ucfirst(formatoTexto($estudiante['estado'])) ?>
                                </span>
                                <?php if(!empty($estudiante['observaciones'])): ?>
                                <span class="px-3 py-1 rounded-full text-sm font-semibold tracking-wide bg-blue-100 text-blue-800 bg-opacity-90">
                                    <i class="fa-solid fa-chalkboard-user mr-1"></i> Grado: <?= formatoTexto($estudiante['observaciones']) ?>
                                </span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <!-- Elemento decorativo CSS -->
                    <div class="absolute top-0 right-0 -mt-10 -mr-10 w-64 h-64 bg-white opacity-5 rounded-full blur-3xl"></div>
                </div>

                <!-- Cuerpo del CV: Cuadrícula -->
                <div class="p-8 grid grid-cols-1 lg:grid-cols-3 gap-8">
                    
                    <!-- Columna Izquierda: Detalles del Estudiante -->
                    <div class="lg:col-span-1 space-y-6">
                        <div>
                            <h3 class="text-lg font-bold text-slate-800 border-b border-slate-200 pb-2 mb-4 flex items-center gap-2">
                                <i class="fa-solid fa-circle-info text-indigo-600"></i> Información Personal
                            </h3>
                            <ul class="space-y-4">
                                <li class="flex items-start gap-3">
                                    <div class="w-8 h-8 rounded bg-slate-100 flex items-center justify-center text-slate-500 shrink-0">
                                        <i class="fa-solid fa-calendar-day"></i>
                                    </div>
                                    <div>
                                        <p class="text-xs text-slate-400 font-semibold uppercase">Fec. Nacimiento</p>
                                        <p class="text-slate-700 font-medium"><?= formatoTexto($estudiante['fecha_nacimiento']) ?></p>
                                    </div>
                                </li>
                                <li class="flex items-start gap-3">
                                    <div class="w-8 h-8 rounded bg-slate-100 flex items-center justify-center text-slate-500 shrink-0">
                                        <i class="fa-solid fa-venus-mars"></i>
                                    </div>
                                    <div>
                                        <p class="text-xs text-slate-400 font-semibold uppercase">Género</p>
                                        <p class="text-slate-700 font-medium"><?= strtoupper(formatoTexto($estudiante['genero'])) === 'M' ? 'Masculino' : (strtoupper(formatoTexto($estudiante['genero'])) === 'F' ? 'Femenino' : 'Otro') ?></p>
                                    </div>
                                </li>
                                <li class="flex items-start gap-3">
                                    <div class="w-8 h-8 rounded bg-red-50 flex items-center justify-center text-red-500 shrink-0">
                                        <i class="fa-solid fa-droplet"></i>
                                    </div>
                                    <div>
                                        <p class="text-xs text-slate-400 font-semibold uppercase">Tipo de Sangre</p>
                                        <p class="text-slate-700 font-medium"><?= formatoTexto($estudiante['tipo_sangre']) ?></p>
                                    </div>
                                </li>
                            </ul>
                        </div>

                        <div>
                            <h3 class="text-lg font-bold text-slate-800 border-b border-slate-200 pb-2 mb-4 flex items-center gap-2">
                                <i class="fa-solid fa-address-book text-indigo-600"></i> Contacto
                            </h3>
                            <ul class="space-y-4">
                                <li class="flex items-start gap-3">
                                    <div class="w-8 h-8 rounded bg-slate-100 flex items-center justify-center text-slate-500 shrink-0">
                                        <i class="fa-solid fa-map-location-dot"></i>
                                    </div>
                                    <div>
                                        <p class="text-xs text-slate-400 font-semibold uppercase">Dirección</p>
                                        <p class="text-slate-700 font-medium"><?= formatoTexto($estudiante['direccion']) ?></p>
                                    </div>
                                </li>
                                <li class="flex items-start gap-3">
                                    <div class="w-8 h-8 rounded bg-slate-100 flex items-center justify-center text-slate-500 shrink-0">
                                        <i class="fa-solid fa-phone"></i>
                                    </div>
                                    <div>
                                        <p class="text-xs text-slate-400 font-semibold uppercase">Teléfono</p>
                                        <p class="text-slate-700 font-medium"><?= formatoTexto($estudiante['telefono']) ?></p>
                                    </div>
                                </li>
                                <li class="flex items-start gap-3">
                                    <div class="w-8 h-8 rounded bg-slate-100 flex items-center justify-center text-slate-500 shrink-0">
                                        <i class="fa-solid fa-envelope"></i>
                                    </div>
                                    <div class="break-all">
                                        <p class="text-xs text-slate-400 font-semibold uppercase">Correo</p>
                                        <p class="text-slate-700 font-medium"><?= formatoTexto($estudiante['correo']) ?></p>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>

                    <!-- Columna Derecha: Acudientes -->
                    <div class="lg:col-span-2">
                        <h3 class="text-xl font-bold text-slate-800 border-b-2 border-indigo-100 pb-3 mb-6 flex items-center gap-2">
                            <i class="fa-solid fa-users-line text-indigo-600"></i> Núcleo Familiar / Acudientes
                        </h3>
                        
                        <?php if (count($acudientes) > 0): ?>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <?php foreach ($acudientes as $acu): ?>
                                    <div class="bg-white border border-slate-200 rounded-xl p-5 hover:border-indigo-300 hover:shadow-md transition-all print-break-inside-avoid">
                                        <div class="flex items-center gap-4 mb-4">
                                            <div class="w-12 h-12 bg-indigo-50 text-indigo-600 rounded-full flex items-center justify-center text-xl shrink-0">
                                                <i class="fa-solid <?= strtolower($acu['genero']) === 'm' ? 'fa-user-tie' : 'fa-user-nurse' ?>"></i>
                                            </div>
                                            <div>
                                                <h4 class="font-bold text-slate-800 leading-tight">
                                                    <?= formatoTexto($acu['nombre'] . ' ' . $acu['apellido']) ?>
                                                </h4>
                                                <span class="inline-block mt-1 px-2 py-0.5 bg-slate-100 text-slate-600 text-xs rounded font-medium border border-slate-200">
                                                    <?= ucfirst(formatoTexto($acu['parentesco'])) ?>
                                                </span>
                                            </div>
                                        </div>
                                        
                                        <div class="space-y-2 text-sm">
                                            <div class="flex items-center gap-2 text-slate-600">
                                                <i class="fa-regular fa-id-badge w-4 text-center text-slate-400"></i> 
                                                <span>CC. <?= formatoTexto($acu['id_usuario']) ?></span>
                                            </div>
                                            <div class="flex items-center gap-2 text-slate-600">
                                                <i class="fa-solid fa-mobile-screen w-4 text-center text-slate-400"></i> 
                                                <span><?= formatoTexto($acu['telefono']) ?></span>
                                            </div>
                                            <div class="flex items-center gap-2 text-slate-600 truncate" title="<?= formatoTexto($acu['correo']) ?>">
                                                <i class="fa-regular fa-envelope w-4 text-center text-slate-400"></i> 
                                                <span><?= formatoTexto($acu['correo']) ?></span>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="bg-slate-50 border border-dashed border-slate-300 rounded-xl p-8 text-center text-slate-500 print-break-inside-avoid">
                                <i class="fa-solid fa-users-slash text-4xl mb-3 text-slate-300"></i>
                                <p>No hay registros de acudientes vinculados a este estudiante en el sistema.</p>
                            </div>
                        <?php endif; ?>
                    </div>

                </div>
            </div>

            <!-- JavaScript Lógica (Frontend) -->
            <script>
                /**
                 * Función para ejecutar el cuadro de diálogo de impresión del navegador.
                 */
                function imprimirPerfil() {
                    window.print();
                }
            </script>
            
        <?php endif; ?>
    </div>
</body>
</html>