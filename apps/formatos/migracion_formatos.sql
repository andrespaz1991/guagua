-- Migración del módulo Formatos institucionales.
-- Puede ejecutarse manualmente; el módulo también valida estos cambios al abrirse.
ALTER TABLE usuario ADD COLUMN condicion_fisica VARCHAR(255) NULL AFTER tipo_sangre;
ALTER TABLE usuario ADD COLUMN grupo VARCHAR(50) NULL AFTER condicion_fisica;
ALTER TABLE usuario ADD COLUMN jornada VARCHAR(80) NULL AFTER grupo;

CREATE TABLE IF NOT EXISTS formatos_observador (
  id_formato_observador BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  id_estudiante VARCHAR(255) NOT NULL,
  ano_lectivo YEAR NOT NULL,
  periodo TINYINT UNSIGNED NOT NULL DEFAULT 1,
  comportamiento VARCHAR(80) NOT NULL,
  valor ENUM('N','AV','S') NULL,
  observaciones TEXT NULL,
  actualizado_por VARCHAR(255) NULL,
  fecha_actualizacion TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_observador_item (id_estudiante, ano_lectivo, periodo, comportamiento)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS formatos_desercion_acciones (
  id_formato_desercion BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  id_estudiante VARCHAR(255) NOT NULL,
  ano_lectivo YEAR NOT NULL,
  acciones TEXT NULL,
  actualizado_por VARCHAR(255) NULL,
  fecha_actualizacion TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_desercion_estudiante_ano (id_estudiante, ano_lectivo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
