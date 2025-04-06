-- Insertando datos en la tabla estandar
INSERT INTO estandar (id_estandar, nombre_estandar, descripcion_estandar, grado, id_periodo, id_materia_oficial)
VALUES 
(1, 'Democracia y Seguridad', 'Reconoce algunos de los sistemas políticos que se establecieron en diferentes épocas y culturas y las principales ideas que buscan legitimarlos.', 6, 1, NULL);

-- Insertando datos en la tabla dba
INSERT INTO dba (id_dba, nombre_dba, descripcion_dba, id_estandar)
VALUES 
(1, 'Análisis de sociedades antiguas',
    '• Analiza como en las sociedades antiguas surgieron las primeras ciudades y el papel de la agricultura y el comercio para la expansión de estas.\n• Analiza las distintas formas de gobierno ejercidas en la antigüedad y las compara con el ejercicio del poder político en el mundo contemporáneo.\n• Analiza cómo en el escenario político democrático entran en juego intereses desde diferentes sectores sociales, políticos y económicos, los cuales deben ser dirimidos por los ciudadanos.',
    1);

-- Insertando datos en la tabla evidencia_de_aprendizaje
INSERT INTO evidencia_de_aprendizaje (id_evidencia_aprendizaje, descripcion_evidencia, id_dba)
VALUES 
(1, '• Relaciona el origen de la agricultura con el desarrollo de las sociedades antiguas y la aparición de elementos que permanecen en la actualidad (canales de riego, la escritura, el ladrillo).\n• Explica el papel de los ríos Nilo, Tigris, Éufrates, Indo, Ganges, Huang He y Yangtsé Kiang, en la construcción de las primeras ciudades y el origen de las civilizaciones antiguas y los ubica en un mapa actual de África y Asia.\n• Describe semejanzas y diferencias que se observan entre la democracia ateniense y las democracias actuales, en especial la colombiana, para señalar fortalezas, debilidades y alternativas que conduzcan a una mayor democratización.\n• Describe el origen de la ciudadanía, los cambios que ha tenido en el tiempo y su significado actual.\n• Argumenta la importancia de participar activamente en la toma de decisiones para el bienestar colectivo en la sociedad, en el contexto de una democracia.\n• Participa activamente en la construcción de los protocolos de seguridad de la Institución Educativa.',
    1);

-- Insertando datos en la tabla eje_tematico
INSERT INTO eje_tematico (id_eje_tematico, nombre_eje_tematico, descripcion_eje_tematico, id_dba)
VALUES 
(1, 'Democracia, Gobierno escolar, Legado cultural, social, económico', '• Democracia\n• Gobierno escolar\n• Legado cultural, social, económico de las diferentes civilizaciones mundiales (Mesopotamia, Egipto, India, China, Grecia, Roma)',
    1);