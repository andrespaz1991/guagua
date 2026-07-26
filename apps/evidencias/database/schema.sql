-- Sistema de Evidencias Docente - Decreto Ley 1278 de 2002
-- Esquema aislado con prefijo evidencias_ para no afectar tablas existentes.
SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS evidencias_anos_lectivos (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    ano SMALLINT UNSIGNED NOT NULL,
    estado ENUM('Activo', 'Cerrado') NOT NULL DEFAULT 'Activo',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_evidencias_ano (ano)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS evidencias_docentes (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    nombre VARCHAR(180) NOT NULL,
    cedula VARCHAR(20) NOT NULL,
    nivel VARCHAR(120) DEFAULT NULL,
    zona VARCHAR(120) DEFAULT NULL,
    institucion VARCHAR(180) DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_evidencias_docente_cedula (cedula)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS evidencias_evaluaciones_anuales (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    docente_id BIGINT UNSIGNED NOT NULL,
    ano_lectivo_id BIGINT UNSIGNED NOT NULL,
    ponderacion_academica DECIMAL(5,2) NOT NULL,
    ponderacion_administrativa DECIMAL(5,2) NOT NULL,
    ponderacion_comunitaria DECIMAL(5,2) NOT NULL,
    fecha_inicio DATE NOT NULL,
    ciudad_concertacion VARCHAR(120) DEFAULT NULL,
    dias_valoracion_1 SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    dias_valoracion_2 SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    estado ENUM('En Concertación', 'Valoración 1', 'Valoración 2', 'Notificado') NOT NULL DEFAULT 'En Concertación',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_evidencias_evaluacion_docente_ano (docente_id, ano_lectivo_id),
    CONSTRAINT fk_evaluaciones_docente FOREIGN KEY (docente_id) REFERENCES evidencias_docentes (id),
    CONSTRAINT fk_evaluaciones_ano FOREIGN KEY (ano_lectivo_id) REFERENCES evidencias_anos_lectivos (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS evidencias_competencias_evaluadas (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    evaluacion_id BIGINT UNSIGNED NOT NULL,
    tipo ENUM('Funcional', 'Comportamental') NOT NULL,
    area_gestion ENUM('Académica', 'Administrativa', 'Comunitaria') DEFAULT NULL,
    nombre_competencia VARCHAR(180) NOT NULL,
    contribucion_individual TEXT NOT NULL,
    orden TINYINT UNSIGNED NOT NULL,
    puntaje_val_1 DECIMAL(5,2) DEFAULT NULL,
    puntaje_val_2 DECIMAL(5,2) DEFAULT NULL,
    puntaje_final DECIMAL(5,2) DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_evidencias_competencia (evaluacion_id, tipo, nombre_competencia),
    KEY idx_evidencias_competencia_area (evaluacion_id, area_gestion),
    CONSTRAINT fk_competencias_evaluacion FOREIGN KEY (evaluacion_id) REFERENCES evidencias_evaluaciones_anuales (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS evidencias_criterios_evaluacion (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    competencia_evaluada_id BIGINT UNSIGNED NOT NULL,
    orden TINYINT UNSIGNED NOT NULL,
    descripcion TEXT NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_evidencias_criterio (competencia_evaluada_id, orden),
    CONSTRAINT fk_criterios_competencia FOREIGN KEY (competencia_evaluada_id) REFERENCES evidencias_competencias_evaluadas (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS evidencias_evidencias (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    criterio_id BIGINT UNSIGNED NOT NULL,
    titulo VARCHAR(255) NOT NULL,
    descripcion TEXT DEFAULT NULL,
    tipo ENUM('Documental', 'Testimonial') NOT NULL,
    estado ENUM('Pendiente', 'Registrada') NOT NULL DEFAULT 'Pendiente',
    fecha_incorporacion DATETIME DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_evidencias_evidencia (criterio_id, titulo),
    KEY idx_evidencias_evidencia_estado (estado),
    CONSTRAINT fk_evidencias_criterio FOREIGN KEY (criterio_id) REFERENCES evidencias_criterios_evaluacion (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS evidencias_adjuntos_evidencia (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    evidencia_id BIGINT UNSIGNED NOT NULL,
    tipo_archivo ENUM('Imagen', 'Documento', 'Video', 'Audio', 'Enlace') NOT NULL,
    nombre_original VARCHAR(255) NOT NULL,
    url_archivo VARCHAR(500) NOT NULL,
    mime_type VARCHAR(120) DEFAULT NULL,
    tamano_bytes BIGINT UNSIGNED DEFAULT NULL,
    fecha_subida DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_evidencias_adjunto_evidencia (evidencia_id),
    CONSTRAINT fk_adjuntos_evidencia FOREIGN KEY (evidencia_id) REFERENCES evidencias_evidencias (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS evidencias_eventos_auditoria (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    evaluacion_id BIGINT UNSIGNED NOT NULL,
    accion VARCHAR(100) NOT NULL,
    detalle TEXT DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_evidencias_auditoria_evaluacion (evaluacion_id),
    CONSTRAINT fk_auditoria_evaluacion FOREIGN KEY (evaluacion_id) REFERENCES evidencias_evaluaciones_anuales (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
