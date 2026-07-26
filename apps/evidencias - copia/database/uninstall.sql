-- ADVERTENCIA: elimina solamente las tablas del módulo evidencias.
SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS evidencias_eventos_auditoria;
DROP TABLE IF EXISTS evidencias_adjuntos_evidencia;
DROP TABLE IF EXISTS evidencias_evidencias;
DROP TABLE IF EXISTS evidencias_criterios_evaluacion;
DROP TABLE IF EXISTS evidencias_competencias_evaluadas;
DROP TABLE IF EXISTS evidencias_evaluaciones_anuales;
DROP TABLE IF EXISTS evidencias_docentes;
DROP TABLE IF EXISTS evidencias_anos_lectivos;
SET FOREIGN_KEY_CHECKS = 1;
