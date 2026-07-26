<?php
/**
 * ============================================================
 * MiMercado - Script de Instalación
 * ============================================================
 * Este archivo crea las tablas necesarias en la base de datos
 * e inserta las categorías predeterminadas para la aplicación
 * MiMercado del proyecto Guagua.
 * 
 * Tablas creadas:
 *   - mercado_categorias
 *   - mercado_productos
 *   - mercado_compras
 *   - mercado_lista_compras
 *   - mercado_presupuesto
 * ============================================================
 */

// Incluir archivo de conexión a la base de datos
require_once '../../comun/conexion.php';

global $mysqli;

// Array para almacenar los resultados de cada operación
$resultados = [];

// ============================================================
// PASO 1: Crear tabla de categorías
// ============================================================
$sql_categorias = "CREATE TABLE IF NOT EXISTS mercado_categorias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    icono VARCHAR(50) DEFAULT 'fa-tag',
    color VARCHAR(7) DEFAULT '#10B981',
    id_usuario VARCHAR(20) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8";

if ($mysqli->query($sql_categorias)) {
    $resultados[] = ['exito' => true, 'mensaje' => 'Tabla <strong>mercado_categorias</strong> creada correctamente.'];
} else {
    $resultados[] = ['exito' => false, 'mensaje' => 'Error al crear tabla mercado_categorias: ' . $mysqli->error];
}

// ============================================================
// PASO 2: Crear tabla de productos
// ============================================================
$sql_productos = "CREATE TABLE IF NOT EXISTS mercado_productos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(200) NOT NULL,
    id_categoria INT DEFAULT NULL,
    precio DECIMAL(12,2) DEFAULT 0,
    cantidad INT DEFAULT 1,
    unidad VARCHAR(50) DEFAULT 'unidad',
    duracion_dias INT DEFAULT 15,
    foto VARCHAR(500) DEFAULT NULL,
    notas TEXT DEFAULT NULL,
    id_usuario VARCHAR(20) NOT NULL,
    activo TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_categoria) REFERENCES mercado_categorias(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8";

if ($mysqli->query($sql_productos)) {
    $resultados[] = ['exito' => true, 'mensaje' => 'Tabla <strong>mercado_productos</strong> creada correctamente.'];
} else {
    $resultados[] = ['exito' => false, 'mensaje' => 'Error al crear tabla mercado_productos: ' . $mysqli->error];
}

// ============================================================
// PASO 3: Crear tabla de compras (historial)
// ============================================================
$sql_compras = "CREATE TABLE IF NOT EXISTS mercado_compras (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_producto INT NOT NULL,
    precio_compra DECIMAL(12,2) NOT NULL,
    cantidad INT DEFAULT 1,
    fecha_compra DATE NOT NULL,
    tipo_periodo ENUM('quincenal','mensual') DEFAULT 'quincenal',
    id_usuario VARCHAR(20) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_producto) REFERENCES mercado_productos(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8";

if ($mysqli->query($sql_compras)) {
    $resultados[] = ['exito' => true, 'mensaje' => 'Tabla <strong>mercado_compras</strong> creada correctamente.'];
} else {
    $resultados[] = ['exito' => false, 'mensaje' => 'Error al crear tabla mercado_compras: ' . $mysqli->error];
}

// ============================================================
// PASO 4: Crear tabla de lista de compras
// ============================================================
$sql_lista = "CREATE TABLE IF NOT EXISTS mercado_lista_compras (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_producto INT DEFAULT NULL,
    nombre_producto VARCHAR(200) NOT NULL,
    precio_estimado DECIMAL(12,2) DEFAULT 0,
    cantidad INT DEFAULT 1,
    prioridad ENUM('alta','media','baja') DEFAULT 'media',
    comprado TINYINT(1) DEFAULT 0,
    id_usuario VARCHAR(20) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_producto) REFERENCES mercado_productos(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8";

if ($mysqli->query($sql_lista)) {
    $resultados[] = ['exito' => true, 'mensaje' => 'Tabla <strong>mercado_lista_compras</strong> creada correctamente.'];
} else {
    $resultados[] = ['exito' => false, 'mensaje' => 'Error al crear tabla mercado_lista_compras: ' . $mysqli->error];
}

// ============================================================
// PASO 5: Crear tabla de presupuesto mensual
// ============================================================
$sql_presupuesto = "CREATE TABLE IF NOT EXISTS mercado_presupuesto (
    id INT AUTO_INCREMENT PRIMARY KEY,
    mes INT NOT NULL,
    anio INT NOT NULL,
    presupuesto DECIMAL(12,2) NOT NULL,
    id_usuario VARCHAR(20) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_mes_usuario (mes, anio, id_usuario)
) ENGINE=InnoDB DEFAULT CHARSET=utf8";

if ($mysqli->query($sql_presupuesto)) {
    $resultados[] = ['exito' => true, 'mensaje' => 'Tabla <strong>mercado_presupuesto</strong> creada correctamente.'];
} else {
    $resultados[] = ['exito' => false, 'mensaje' => 'Error al crear tabla mercado_presupuesto: ' . $mysqli->error];
}

// ============================================================
// PASO 6: Insertar categorías predeterminadas
// ============================================================
// Categorías por defecto con id_usuario='0' (disponibles para todos)
$categorias_default = [
    ['nombre' => 'Frutas y Verduras',  'icono' => 'fa-apple-alt',     'color' => '#22C55E'],
    ['nombre' => 'Carnes y Proteínas', 'icono' => 'fa-drumstick-bite','color' => '#EF4444'],
    ['nombre' => 'Lácteos',            'icono' => 'fa-tint',          'color' => '#3B82F6'],
    ['nombre' => 'Granos y Cereales',  'icono' => 'fa-leaf',          'color' => '#F59E0B'],
    ['nombre' => 'Bebidas',            'icono' => 'fa-glass-martini', 'color' => '#8B5CF6'],
    ['nombre' => 'Limpieza',           'icono' => 'fa-home',          'color' => '#06B6D4'],
    ['nombre' => 'Aseo Personal',      'icono' => 'fa-user',          'color' => '#EC4899'],
    ['nombre' => 'Snacks',             'icono' => 'fa-cookie-bite',   'color' => '#F97316'],
    ['nombre' => 'Panadería',          'icono' => 'fa-birthday-cake', 'color' => '#A855F7'],
    ['nombre' => 'Otros',              'icono' => 'fa-ellipsis-h',    'color' => '#6B7280'],
];

$categorias_insertadas = 0;
$categorias_existentes = 0;
$categorias_error = 0;

foreach ($categorias_default as $cat) {
    // Verificar si la categoría ya existe para evitar duplicados
    $stmt_check = $mysqli->prepare("SELECT id FROM mercado_categorias WHERE nombre = ? AND id_usuario = '0'");
    $stmt_check->bind_param('s', $cat['nombre']);
    $stmt_check->execute();
    $stmt_check->store_result();

    if ($stmt_check->num_rows > 0) {
        // La categoría ya existe, no la insertamos de nuevo
        $categorias_existentes++;
        $stmt_check->close();
        continue;
    }
    $stmt_check->close();

    // Insertar la categoría usando prepared statement
    $stmt_insert = $mysqli->prepare("INSERT INTO mercado_categorias (nombre, icono, color, id_usuario) VALUES (?, ?, ?, '0')");
    $stmt_insert->bind_param('sss', $cat['nombre'], $cat['icono'], $cat['color']);

    if ($stmt_insert->execute()) {
        $categorias_insertadas++;
    } else {
        $categorias_error++;
    }
    $stmt_insert->close();
}

// Registrar resultado de categorías
if ($categorias_insertadas > 0) {
    $resultados[] = ['exito' => true, 'mensaje' => "Se insertaron <strong>{$categorias_insertadas}</strong> categorías predeterminadas."];
}
if ($categorias_existentes > 0) {
    $resultados[] = ['exito' => true, 'mensaje' => "<strong>{$categorias_existentes}</strong> categorías ya existían (no se duplicaron)."];
}
if ($categorias_error > 0) {
    $resultados[] = ['exito' => false, 'mensaje' => "Error al insertar <strong>{$categorias_error}</strong> categorías."];
}

// ============================================================
// PASO 7: Crear directorio de uploads si no existe
// ============================================================
$uploads_dir = __DIR__ . '/uploads';
if (!is_dir($uploads_dir)) {
    if (mkdir($uploads_dir, 0755, true)) {
        $resultados[] = ['exito' => true, 'mensaje' => 'Directorio <strong>uploads/</strong> creado correctamente.'];
    } else {
        $resultados[] = ['exito' => false, 'mensaje' => 'No se pudo crear el directorio uploads/. Créalo manualmente.'];
    }
} else {
    $resultados[] = ['exito' => true, 'mensaje' => 'Directorio <strong>uploads/</strong> ya existe.'];
}

// Contar totales para el resumen
$total_exitos = count(array_filter($resultados, function ($r) { return $r['exito']; }));
$total_errores = count(array_filter($resultados, function ($r) { return !$r['exito']; }));
$todo_bien = $total_errores === 0;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MiMercado - Instalación</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            max-width: 700px;
            width: 100%;
            overflow: hidden;
        }
        .header {
            background: linear-gradient(135deg, #10B981, #059669);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .header h1 {
            font-size: 28px;
            margin-bottom: 5px;
        }
        .header p {
            opacity: 0.9;
            font-size: 14px;
        }
        .header .icon {
            font-size: 50px;
            margin-bottom: 15px;
        }
        .content {
            padding: 30px;
        }
        .resultado-item {
            display: flex;
            align-items: center;
            padding: 12px 15px;
            margin-bottom: 8px;
            border-radius: 10px;
            background: #f8fafc;
            border-left: 4px solid;
            transition: transform 0.2s;
        }
        .resultado-item:hover {
            transform: translateX(5px);
        }
        .resultado-item.exito {
            border-color: #10B981;
        }
        .resultado-item.error {
            border-color: #EF4444;
        }
        .resultado-item .icono {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 12px;
            flex-shrink: 0;
            font-size: 14px;
            color: white;
        }
        .resultado-item.exito .icono {
            background: #10B981;
        }
        .resultado-item.error .icono {
            background: #EF4444;
        }
        .resultado-item .texto {
            font-size: 14px;
            color: #374151;
        }
        .resumen {
            margin-top: 25px;
            padding: 20px;
            border-radius: 12px;
            text-align: center;
        }
        .resumen.todo-bien {
            background: linear-gradient(135deg, #d1fae5, #a7f3d0);
            border: 2px solid #10B981;
        }
        .resumen.con-errores {
            background: linear-gradient(135deg, #fee2e2, #fecaca);
            border: 2px solid #EF4444;
        }
        .resumen h3 {
            font-size: 20px;
            margin-bottom: 8px;
        }
        .resumen.todo-bien h3 {
            color: #065f46;
        }
        .resumen.con-errores h3 {
            color: #991b1b;
        }
        .resumen p {
            font-size: 14px;
            color: #6B7280;
        }
        .btn-ir {
            display: inline-block;
            margin-top: 20px;
            padding: 14px 35px;
            background: linear-gradient(135deg, #10B981, #059669);
            color: white;
            text-decoration: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            transition: all 0.3s;
            box-shadow: 0 4px 15px rgba(16, 185, 129, 0.4);
        }
        .btn-ir:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(16, 185, 129, 0.5);
        }
        .footer {
            text-align: center;
            padding: 20px 30px 30px;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Encabezado -->
        <div class="header">
            <div class="icon"><i class="fas fa-shopping-cart"></i></div>
            <h1>MiMercado - Instalación</h1>
            <p>Configurando las tablas y datos iniciales de la aplicación</p>
        </div>

        <!-- Resultados de la instalación -->
        <div class="content">
            <?php foreach ($resultados as $res): ?>
                <div class="resultado-item <?php echo $res['exito'] ? 'exito' : 'error'; ?>">
                    <div class="icono">
                        <i class="fas <?php echo $res['exito'] ? 'fa-check' : 'fa-times'; ?>"></i>
                    </div>
                    <div class="texto"><?php echo $res['mensaje']; ?></div>
                </div>
            <?php endforeach; ?>

            <!-- Resumen final -->
            <div class="resumen <?php echo $todo_bien ? 'todo-bien' : 'con-errores'; ?>">
                <?php if ($todo_bien): ?>
                    <h3><i class="fas fa-check-circle"></i> ¡Instalación completada!</h3>
                    <p><?php echo $total_exitos; ?> operaciones ejecutadas exitosamente. La aplicación está lista para usarse.</p>
                <?php else: ?>
                    <h3><i class="fas fa-exclamation-triangle"></i> Instalación con advertencias</h3>
                    <p><?php echo $total_exitos; ?> operaciones exitosas, <?php echo $total_errores; ?> con errores. Revisa los mensajes anteriores.</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Botón para ir a la app -->
        <div class="footer">
            <a href="index.php" class="btn-ir">
                <i class="fas fa-arrow-right"></i> Ir a MiMercado
            </a>
        </div>
    </div>
</body>
</html>
