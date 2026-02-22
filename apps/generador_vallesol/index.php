<?php
/**
 * Script para generación automática de estructura de directorios académica.
 * Autor: Generado por IA para Andres Paz.
 * * Estructura:
 * 1. Carpeta "6-8" con materias básicas.
 * 2. Carpeta "9-11" con materias de media + Economía.
 * 3. Subcarpetas de periodos (1-4) dentro de cada materia.
 */

// Configuración de visualización de errores para depuración
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// ==========================================
// 1. DEFINICIÓN DE DATOS (ARRAYS)
// ==========================================

// Lista de Periodos
$periodos = [
    "Primer Periodo",
    "Segundo Periodo",
    "Tercer Periodo",
    "Cuarto Periodo"
];

// Lista de Materias para Grados 6-8 (Básica Secundaria)
// Se han actualizado las materias y corregido ortografía (tildes)
$materias_6_8 = [
    "Ciencias Sociales",
    "Educación Física",
    "Emprendimiento",
    "Geometría", // Corregido: Geometria -> Geometría
    "Matemáticas",
    "Tecnología",
    "Urbanidad"
];

// Lista de Materias para Grados 9-11 (Media)
// Se han actualizado las materias y corregido ortografía (tildes)
$materias_9_11 = [
    "Economía y Política", // Corregido: Economía-Politica -> Economía y Política
    "Educación Física",
    "Emprendimiento",
    "Física",    // Corregido: Fisica -> Física
    "Matemáticas",
    "Tecnología",
    "Geometría", // Corregido: Geometria -> Geometría
    "Urbanidad"
];

// ==========================================
// 2. LÓGICA DE CREACIÓN
// ==========================================

/**
 * Función recursiva para crear la estructura de materias y periodos.
 *
 * @param string $nombreCarpetaRaiz El nombre de la carpeta principal (ej: "6-8")
 * @param array $listaMaterias Array con los nombres de las materias
 * @param array $listaPeriodos Array con los nombres de los periodos
 */
function crearEstructura($nombreCarpetaRaiz, $listaMaterias, $listaPeriodos) {
    // Verificar/Crear carpeta raíz del grupo
    if (!file_exists($nombreCarpetaRaiz)) {
        if (mkdir($nombreCarpetaRaiz, 0777, true)) {
            echo "[OK] Carpeta principal creada: $nombreCarpetaRaiz<br>";
        } else {
            echo "[ERROR] No se pudo crear la carpeta: $nombreCarpetaRaiz (Verificar permisos)<br>";
            return;
        }
    } else {
        echo "[INFO] La carpeta principal ya existe: $nombreCarpetaRaiz<br>";
    }

    // Iterar sobre materias
    foreach ($listaMaterias as $materia) {
        // Sanear nombre de materia para evitar caracteres inválidos en rutas (opcional pero recomendado)
        // $materiaLimpia = preg_replace('/[^A-Za-z0-9 _-]/', '', $materia); 
        // Se deja original para mantener tildes si el SO lo permite (Windows moderno/Linux suelen aceptar UTF-8)
        
        $rutaMateria = $nombreCarpetaRaiz . DIRECTORY_SEPARATOR . $materia;

        if (!file_exists($rutaMateria)) {
            mkdir($rutaMateria, 0777, true);
        }

        // Crear carpetas de periodos dentro de la materia
        foreach ($listaPeriodos as $periodo) {
            $rutaPeriodo = $rutaMateria . DIRECTORY_SEPARATOR . $periodo;
            
            if (!file_exists($rutaPeriodo)) {
                mkdir($rutaPeriodo, 0777, true);
            }
        }
    }
    echo "Estructura completa generada para: $nombreCarpetaRaiz<br><br>";
}

// ==========================================
// 3. EJECUCIÓN
// ==========================================

echo "<h3>Iniciando generación de estructura de carpetas...</h3>";
echo "<pre>";

// Ejecutar para 6-8
crearEstructura("6-8", $materias_6_8, $periodos);

// Ejecutar para 9-11
crearEstructura("9-11", $materias_9_11, $periodos);

echo "</pre>";
echo "<h3>Proceso Finalizado.</h3>";

?>