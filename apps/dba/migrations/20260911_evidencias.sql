-- Ampliación aditiva. Compatible con las pantallas antiguas del Planeador.
ALTER TABLE dba ADD COLUMN IF NOT EXISTS palabras_clave TEXT NULL;
ALTER TABLE evidencia_de_aprendizaje ADD COLUMN IF NOT EXISTS origen VARCHAR(30) NOT NULL DEFAULT 'manual';
ALTER TABLE evidencia_de_aprendizaje ADD COLUMN IF NOT EXISTS modelo VARCHAR(150) NULL;
ALTER TABLE evidencia_de_aprendizaje ADD COLUMN IF NOT EXISTS fecha_creacion TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP;
