CREATE TABLE IF NOT EXISTS sisga_indicadores_perfiles (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    clave_contexto CHAR(64) NOT NULL,
    institucion_sisga VARCHAR(80) NOT NULL,
    materia VARCHAR(180) NOT NULL,
    grado VARCHAR(80) NOT NULL,
    periodo VARCHAR(40) NOT NULL,
    nivel VARCHAR(40) NOT NULL,
    perfil_json LONGTEXT NOT NULL,
    archivo_origen VARCHAR(255) NOT NULL DEFAULT '',
    capturado_en DATETIME(3) NOT NULL,
    creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    actualizado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_sisga_indicadores_contexto (clave_contexto),
    KEY idx_sisga_indicadores_filtros (materia, grado, periodo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
