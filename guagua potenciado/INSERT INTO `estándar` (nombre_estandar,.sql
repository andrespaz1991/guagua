INSERT INTO `estandar` (nombre_estandar, descripcion_estandar, grado, id_periodo, id_materia_oficial) VALUES
('Identifica aplicaciones de algunos conocimientos sobre la herencia y la reproducción al mejoramiento de la calidad de vida de las poblaciones', NULL, 6, 4, 2),
('Identifica aplicaciones de algunos conocimientos sobre la herencia y la reproducción al mejoramiento de la calidad de vida de las poblaciones', NULL, 7, 4, 2),
('Identifica aplicaciones de algunos conocimientos sobre la herencia y la reproducción al mejoramiento de la calidad de vida de las poblaciones', NULL, 8, 4, 2),
('Identifica aplicaciones comerciales e industriales del transporte de energía y de las interacciones de la materia.', NULL, 9, 4, 2),
('Evalúa el potencial de los recursos naturales, la forma como se han utilizado en desarrollos tecnológicos y las consecuencias de la acción del ser humano sobre ellos.', NULL, 10, 4, 2),
('Identifica aplicaciones de diferentes modelos biológicos, químicos y físicos en procesos industriales y en el desarrollo tecnológico; analizo críticamente las implicaciones de sus usos.', NULL, 11, 4, 2);


INSERT INTO `dba` (nombre_dba, descripcion_dba, id_estandar) VALUES
('Analiza la reproducción (asexual, sexual) de distintos grupos de seres vivos y su importancia para la preservación de la vida en el planeta.', NULL, (SELECT id_estandar FROM estandar WHERE grado = 6 AND id_periodo = 4 AND id_materia_oficial = 2 limit 1)),
('Comprende que en las cadenas y redes tróficas existen flujos de materia y energía, y los relaciona con procesos de nutrición, fotosíntesis y respiración celular.', NULL, (SELECT id_estandar FROM estandar WHERE grado = 7 AND id_periodo = 4 AND id_materia_oficial = 2 limit 1)),
('Comprende que en una reacción química se recombinan los átomos de las moléculas de los reactivos para generar productos nuevos, y que dichos productos se forman a partir de fuerzas intermoleculares (enlaces iónicos y covalentes)', NULL, (SELECT id_estandar FROM estandar WHERE grado = 8 AND id_periodo = 4 AND id_materia_oficial = 2 limit 1)),
('Analiza las relaciones cuantitativas entre solutos y solventes, así como los factores que afectan la formación de soluciones.', NULL, (SELECT id_estandar FROM estandar WHERE grado = 9 AND id_periodo = 4 AND id_materia_oficial = 2 limit 1)),
('Comprende que la biotecnología conlleva el uso y manipulación de la información genética a través de distintas técnicas (fertilización asistida, clonación reproductiva y terapéutica, modificación genética, terapias genéticas), y que tiene implicaciones sociales, bioéticas y ambientales.', NULL, (SELECT id_estandar FROM estandar WHERE grado = 10 AND id_periodo = 4 AND id_materia_oficial = 2 limit 1)),
('Analiza cuestiones ambientales actuales, como el calentamiento global, contaminación, tala de bosques y minería, desde una visión sistémica (económico, social, ambiental y cultural).', NULL, (SELECT id_estandar FROM estandar WHERE grado = 11 AND id_periodo = 4 AND id_materia_oficial = 2 limit 1));


INSERT INTO `evidencia_de_aprendizaje` (descripcion_evidencia, id_dba) VALUES
('Identifica riesgos y consecuencias físicas y psicológicas de un embarazo en la adolescencia.', (SELECT id_dba FROM dba WHERE nombre_dba LIKE '%Analiza la reproducción%')),
('Explica la importancia de la aplicación de medidas preventivas de patologías relacionadas con el sistema reproductor.', (SELECT id_dba FROM dba WHERE nombre_dba LIKE '%Analiza la reproducción%')),
('Explica tipos de nutrición en las cadenas y redes tróficas dentro de los ecosistemas.', (SELECT id_dba FROM dba WHERE nombre_dba LIKE '%Comprende que en las cadenas y redes tróficas%')),
('Justifica si un cambio en un material es físico o químico a partir de características observables que indiquen, para el caso de los cambios químicos, la formación de nuevas sustancias (cambio de color, desprendimiento de gas, entre otros).', (SELECT id_dba FROM dba WHERE nombre_dba LIKE '%Comprende que en una reacción química%')),
('Predice que ocurrirá con una solución si se modifica una variable como la temperatura, la presión o las cantidades de soluto y solvente.', (SELECT id_dba FROM dba WHERE nombre_dba LIKE '%Analiza las relaciones cuantitativas entre solutos y solventes%')),
('Explica los usos de la biotecnología y sus efectos en diferentes contextos (salud, agricultura, producción energética y ambiente)', (SELECT id_dba FROM dba WHERE nombre_dba LIKE '%Comprende que la biotecnología conlleva el uso y manipulación%')),
('Explica el fenómeno del calentamiento global, identificando sus causas y proponiendo acciones locales y globales para controlarlo.', (SELECT id_dba FROM dba WHERE nombre_dba LIKE '%Analiza cuestiones ambientales actuales%'));

INSERT INTO `eje_tematico` (nombre_eje_tematico, descripcion_eje_tematico, id_dba) VALUES
('Reproducción sexual y asexual', NULL, (SELECT id_dba FROM dba WHERE nombre_dba LIKE '%Analiza la reproducción%')),
('Reproducción en plantas y animales.', NULL, (SELECT id_dba FROM dba WHERE nombre_dba LIKE '%Analiza la reproducción%')),
('Desarrollo embrionario', NULL, (SELECT id_dba FROM dba WHERE nombre_dba LIKE '%Analiza la reproducción%')),
('Métodos anticonceptivos', NULL, (SELECT id_dba FROM dba WHERE nombre_dba LIKE '%Analiza la reproducción%')),
('Tipos de mezclas', NULL, (SELECT id_dba FROM dba WHERE nombre_dba LIKE '%Analiza las relaciones cuantitativas entre solutos y solventes%')),
('Componentes de una solución química', NULL, (SELECT id_dba FROM dba WHERE nombre_dba LIKE '%Analiza las relaciones cuantitativas entre solutos y solventes%')),
('Tipos de soluciones químicas', NULL, (SELECT id_dba FROM dba WHERE nombre_dba LIKE '%Analiza las relaciones cuantitativas entre solutos y solventes%')),
('Medición del pH en las soluciones', NULL, (SELECT id_dba FROM dba WHERE nombre_dba LIKE '%Analiza las relaciones cuantitativas entre solutos y solventes%')),
('pH en la industria', NULL, (SELECT id_dba FROM dba WHERE nombre_dba LIKE '%Analiza las relaciones cuantitativas entre solutos y solventes%')),
('Manipulación genética', NULL, (SELECT id_dba FROM dba WHERE nombre_dba LIKE '%Comprende que la biotecnología conlleva el uso y manipulación%')),
('Genoma humano', NULL, (SELECT id_dba FROM dba WHERE nombre_dba LIKE '%Comprende que la biotecnología conlleva el uso y manipulación%')),
('fertilización asistida, clonación reproductiva y terapéutica, modificación genética, terapias genéticas', NULL, (SELECT id_dba FROM dba WHERE nombre_dba LIKE '%Comprende que la biotecnología conlleva el uso y manipulación%')),
('Calentamiento global', NULL, (SELECT id_dba FROM dba WHERE nombre_dba LIKE '%Analiza cuestiones ambientales actuales%')),
('Contaminación y residuos solidos', NULL, (SELECT id_dba FROM dba WHERE nombre_dba LIKE '%Analiza cuestiones ambientales actuales%')),
('Deforestación', NULL, (SELECT id_dba FROM dba WHERE nombre_dba LIKE '%Analiza cuestiones ambientales actuales%'));

#####################

-- Inserción en la tabla materia_oficial
-- No es necesario ya que Urbanidad ya existe con id_materia = 8

-- Inserción en la tabla estándar
INSERT INTO `estándar` (nombre_estandar, descripcion_estandar, grado, id_periodo, id_materia_oficial) VALUES
('Identifico las necesidades y los puntos de vista de personas o grupos en una situación de conflicto, en la que no estoy involucrado.', NULL, 6, 4, 8),
('Reflexiono sobre el uso del poder y la autoridad en mi entorno y expreso pacíficamente mi desacuerdo cuando considero que hay injusticia.', NULL, 7, 4, 8),
('Preveo las consecuencias, a corto y largo plazo, de mis acciones y evito aquellas que pueden causarme sufrimiento o hacérselo a otras personas, cercanas o lejanas.', NULL, 8, 4, 8),
('Reflexiono sobre el uso del poder y la autoridad en mi entorno y expreso pacíficamente mi desacuerdo cuando considero que hay injusticia.', NULL, 9, 4, 8),
('Expreso rechazo por todas las formas de discriminación o exclusión social y hago uso de los mecanismos democráticos para la superación de la discriminación y el respeto a la diversidad.', NULL, 10, 4, 8),
('Argumento y debato sobre dilemas de la vida en los que entran en conflicto el bien general y el bien particular, reconociendo los mejores argumentos, así sean distintos a los míos.', NULL, 11, 4, 8);

-- Inserción en la tabla dba
INSERT INTO `dba` (nombre_dba, descripcion_dba, id_estandar) VALUES
('Comprende que las intenciones de la gente, muchas veces, son mejores de lo que yo inicialmente pensaba; también veo que hay situaciones en las que alguien puede hacerme daño sin intención.', NULL, (SELECT id_estandar FROM estándar WHERE grado = 6 AND id_periodo = 4 AND id_materia_oficial = 8)),
('No se especifica claramente en el documento para este grado y período.', NULL, (SELECT id_estandar FROM estándar WHERE grado = 7 AND id_periodo = 4 AND id_materia_oficial = 8)),
('Conozco y utilizo estrategias creativas para solucionar conflictos.', NULL, (SELECT id_estandar FROM estándar WHERE grado = 8 AND id_periodo = 4 AND id_materia_oficial = 8)),
('No se especifica claramente en el documento para este grado y período.', NULL, (SELECT id_estandar FROM estándar WHERE grado = 9 AND id_periodo = 4 AND id_materia_oficial = 8)),
('No se especifica claramente en el documento para este grado y período.', NULL, (SELECT id_estandar FROM estándar WHERE grado = 10 AND id_periodo = 4 AND id_materia_oficial = 8)),
('Reconoce la paz es el camino ideal para establecer una verdadera convivencia para Colombia y el mundo.', NULL, (SELECT id_estandar FROM estándar WHERE grado = 11 AND id_periodo = 4 AND id_materia_oficial = 8));

-- Inserción en la tabla evidencia_de_aprendizaje
INSERT INTO `evidencia_de_aprendizaje` (descripcion_evidencia, id_dba) VALUES
('Opina libremente sobre las ventajas de la democracia.', (SELECT id_dba FROM dba WHERE nombre_dba LIKE '%Comprende que las intenciones de la gente%')),
('Habla con propiedad sobre la importancia de las leyes.', (SELECT id_dba FROM dba WHERE nombre_dba LIKE '%Comprende que las intenciones de la gente%')),
('Reflexiona sobre los beneficios que ofrece la constitución política de Colombia.', (SELECT id_dba FROM dba WHERE nombre_dba LIKE '%Comprende que las intenciones de la gente%')),
('Conoce la importancia de las políticas públicas.', (SELECT id_dba FROM dba WHERE nombre_dba LIKE '%Comprende que las intenciones de la gente%')),
('Participa en los órganos institucionales.', (SELECT id_dba FROM dba WHERE nombre_dba LIKE '%Comprende que las intenciones de la gente%')),
('Señala la importancia de la honestidad en la participación de la política.', (SELECT id_dba FROM dba WHERE nombre_dba LIKE '%No se especifica claramente en el documento para este grado y período.%' AND id_estandar = (SELECT id_estandar FROM estándar WHERE grado = 7 AND id_periodo = 4 AND id_materia_oficial = 8))),
('Enumera las diferentes formas para desarrollar una Cultura Participativa de la Comunidad en Honestidad Democrática.', (SELECT id_dba FROM dba WHERE nombre_dba LIKE '%No se especifica claramente en el documento para este grado y período.%' AND id_estandar = (SELECT id_estandar FROM estándar WHERE grado = 7 AND id_periodo = 4 AND id_materia_oficial = 8))),
('Señala las características de la corrupción en la política y quienes al fin del proceso sufren los excesos de unos pocos.', (SELECT id_dba FROM dba WHERE nombre_dba LIKE '%No se especifica claramente en el documento para este grado y período.%' AND id_estandar = (SELECT id_estandar FROM estándar WHERE grado = 7 AND id_periodo = 4 AND id_materia_oficial = 8))),
('Determina la conformación de los líderes en cada comunidad para velar por los derechos ciudadanos.', (SELECT id_dba FROM dba WHERE nombre_dba LIKE '%No se especifica claramente en el documento para este grado y período.%' AND id_estandar = (SELECT id_estandar FROM estándar WHERE grado = 7 AND id_periodo = 4 AND id_materia_oficial = 8))),
('Reconoce la importancia de conocer las necesidades sociales de mi región.', (SELECT id_dba FROM dba WHERE nombre_dba LIKE '%Conozco y utilizo estrategias creativas para solucionar conflictos%')),
('Habla con propiedad sobre las ventajas de nuestros recursos naturales.', (SELECT id_dba FROM dba WHERE nombre_dba LIKE '%Conozco y utilizo estrategias creativas para solucionar conflictos%')),
('Identifica la importancia de alcanzar el propio proyecto de vida.', (SELECT id_dba FROM dba WHERE nombre_dba LIKE '%Conozco y utilizo estrategias creativas para solucionar conflictos%')),
('Señala la importancia de la honestidad en la participación de la política.', (SELECT id_dba FROM dba WHERE nombre_dba LIKE '%No se especifica claramente en el documento para este grado y período.%' AND id_estandar = (SELECT id_estandar FROM estándar WHERE grado = 9 AND id_periodo = 4 AND id_materia_oficial = 8))),
('Enumera las diferentes formas para desarrollar una Cultura Participativa de la Comunidad en Honestidad Democrática.', (SELECT id_dba FROM dba WHERE nombre_dba LIKE '%No se especifica claramente en el documento para este grado y período.%' AND id_estandar = (SELECT id_estandar FROM estándar WHERE grado = 9 AND id_periodo = 4 AND id_materia_oficial = 8))),
('Señala las características de la corrupción en la política y quienes al fin del proceso sufren los excesos de unos pocos.', (SELECT id_dba FROM dba WHERE nombre_dba LIKE '%No se especifica claramente en el documento para este grado y período.%' AND id_estandar = (SELECT id_estandar FROM estándar WHERE grado = 9 AND id_periodo = 4 AND id_materia_oficial = 8))),
('Determina la conformación de los líderes en cada comunidad para velar por los derechos ciudadanos.', (SELECT id_dba FROM dba WHERE nombre_dba LIKE '%No se especifica claramente en el documento para este grado y período.%' AND id_estandar = (SELECT id_estandar FROM estándar WHERE grado = 9 AND id_periodo = 4 AND id_materia_oficial = 8))),
('Diferencia los límites existentes entre la exclusión y la discriminación.', (SELECT id_dba FROM dba WHERE nombre_dba LIKE '%No se especifica claramente en el documento para este grado y período.%' AND id_estandar = (SELECT id_estandar FROM estándar WHERE grado = 10 AND id_periodo = 4 AND id_materia_oficial = 8))),
('Señala su valoración personal como el fundamento para no incurrir en actitudes de exclusión o discriminación.', (SELECT id_dba FROM dba WHERE nombre_dba LIKE '%No se especifica claramente en el documento para este grado y período.%' AND id_estandar = (SELECT id_estandar FROM estándar WHERE grado = 10 AND id_periodo = 4 AND id_materia_oficial = 8))),
('Identifica eventos en la vida diaria que generan conflicto interno por cuanto tiene prejuicios, estereotipos y emociones que le hacen dudar de su actitud.', (SELECT id_dba FROM dba WHERE nombre_dba LIKE '%No se especifica claramente en el documento para este grado y período.%' AND id_estandar = (SELECT id_estandar FROM estándar WHERE grado = 10 AND id_periodo = 4 AND id_materia_oficial = 8))),
('Reconoce la importancia de plantear propuestas.', (SELECT id_dba FROM dba WHERE nombre_dba LIKE '%Reconoce la paz es el camino ideal%')),
('Organiza planes y proyectos de beneficio escolar y comunitario.', (SELECT id_dba FROM dba WHERE nombre_dba LIKE '%Reconoce la paz es el camino ideal%')),
('Participa con grupos de trabajo en la solución de tareas educativas.', (SELECT id_dba FROM dba WHERE nombre_dba LIKE '%Reconoce la paz es el camino ideal%')),
('Identifica que es indispensable promover la paz en los lugares donde trabajamos.', (SELECT id_dba FROM dba WHERE nombre_dba LIKE '%Reconoce la paz es el camino ideal%')),
('Reconoce que la ayuda permite el logro de las propuestas de desarrollo.', (SELECT id_dba FROM dba WHERE nombre_dba LIKE '%Reconoce la paz es el camino ideal%')),
('Identifica emotivamente el propio proyecto de vida.', (SELECT id_dba FROM dba WHERE nombre_dba LIKE '%Reconoce la paz es el camino ideal%'));

-- Inserción en la tabla eje_tematico
INSERT INTO `eje_tematico` (nombre_eje_tematico, descripcion_eje_tematico, id_dba) VALUES
('La familia y la escuela', NULL, (SELECT id_dba FROM dba WHERE nombre_dba LIKE '%Comprende que las intenciones de la gente%')),
('La escuela', NULL, (SELECT id_dba FROM dba WHERE nombre_dba LIKE '%Comprende que las intenciones de la gente%')),
('Deberes del estado con la familia', NULL, (SELECT id_dba FROM dba WHERE nombre_dba LIKE '%Comprende que las intenciones de la gente%')),
('Las discusiones familiares', NULL, (SELECT id_dba FROM dba WHERE nombre_dba LIKE '%Comprende que las intenciones de la gente%')),
('Participación Política', NULL, (SELECT id_dba FROM dba WHERE nombre_dba LIKE '%No se especifica claramente en el documento para este grado y período.%' AND id_estandar = (SELECT id_estandar FROM estándar WHERE grado = 7 AND id_periodo = 4 AND id_materia_oficial = 8))),
('Participación Política y Democracia', NULL, (SELECT id_dba FROM dba WHERE nombre_dba LIKE '%No se especifica claramente en el documento para este grado y período.%' AND id_estandar = (SELECT id_estandar FROM estándar WHERE grado = 7 AND id_periodo = 4 AND id_materia_oficial = 8))),
('Tipos de Política', NULL, (SELECT id_dba FROM dba WHERE nombre_dba LIKE '%No se especifica claramente en el documento para este grado y período.%' AND id_estandar = (SELECT id_estandar FROM estándar WHERE grado = 7 AND id_periodo = 4 AND id_materia_oficial = 8))),
('Participar-Incidir-Activar y Participar', NULL, (SELECT id_dba FROM dba WHERE nombre_dba LIKE '%No se especifica claramente en el documento para este grado y período.%' AND id_estandar = (SELECT id_estandar FROM estándar WHERE grado = 7 AND id_periodo = 4 AND id_materia_oficial = 8))),
('Liderazgo Comunitario', NULL, (SELECT id_dba FROM dba WHERE nombre_dba LIKE '%No se especifica claramente en el documento para este grado y período.%' AND id_estandar = (SELECT id_estandar FROM estándar WHERE grado = 7 AND id_periodo = 4 AND id_materia_oficial = 8))),
('Comunidad y Líderes', NULL, (SELECT id_dba FROM dba WHERE nombre_dba LIKE '%No se especifica claramente en el documento para este grado y período.%' AND id_estandar = (SELECT id_estandar FROM estándar WHERE grado = 7 AND id_periodo = 4 AND id_materia_oficial = 8))),
('Tipos de Liderazgo Comunitario', NULL, (SELECT id_dba FROM dba WHERE nombre_dba LIKE '%No se especifica claramente en el documento para este grado y período.%' AND id_estandar = (SELECT id_estandar FROM estándar WHERE grado = 7 AND id_periodo = 4 AND id_materia_oficial = 8))),
('Habilidades para ser Líderes', NULL, (SELECT id_dba FROM dba WHERE nombre_dba LIKE '%No se especifica claramente en el documento para este grado y período.%' AND id_estandar = (SELECT id_estandar FROM estándar WHERE grado = 7 AND id_periodo = 4 AND id_materia_oficial = 8))),
('La conversación', NULL, (SELECT id_dba FROM dba WHERE nombre_dba LIKE '%Conozco y utilizo estrategias creativas para solucionar conflictos%')),
('El trato', NULL, (SELECT id_dba FROM dba WHERE nombre_dba LIKE '%Conozco y utilizo estrategias creativas para sol'));
#######################


INSERT INTO `estándar` (nombre_estandar, descripcion_estandar, grado, id_periodo, id_materia_oficial) VALUES
('Tiene en cuenta normas de mantenimiento y utilización de artefactos, productos, servicios, procesos y sistemas tecnológicos de su entorno para su uso eficiente y seguro.', NULL, 8, 4, 7),
('Adquiere habilidades y destrezas en el manejo de herramientas ofimáticas a través de aparatos tecnológicos.', NULL, 8, 4, 7),
('Relaciona los conocimientos científicos y tecnológicos que se han empleado en diversas culturas y regiones del mundo a través de la historia para resolver problemas y transformar el entorno.', NULL, 8, 4, 7),
('Tiene en cuenta normas de mantenimiento y utilización de artefactos, productos, servicios, procesos y sistemas tecnológicos de su entorno para su uso eficiente y seguro.', NULL, 9, 4, 7),
('Adquiere habilidades y destrezas en el manejo de herramientas ofimáticas a través de aparatos tecnológicos.', NULL, 9, 4, 7),
('Reconoce las causas y los efectos sociales, económicos y culturales de los desarrollos tecnológicos y actuar en consecuencia, de manera ética y responsable.', NULL, 9, 4, 7),
('Propone estrategias para soluciones tecnológicas a problemas, en diferentes contextos.', NULL, 9, 4, 7);

INSERT INTO `dba` (nombre_dba, descripcion_dba, id_estandar) VALUES
('Identifico principios científicos aplicados al funcionamiento de algunos artefactos, productos, servicios, procesos y sistemas tecnológicos.', NULL, (SELECT id_estandar FROM estándar WHERE grado = 8 AND id_periodo = 4 AND id_materia_oficial = 7 LIMIT 1)),
('Utiliza responsablemente productos tecnológicos, valorando su pertinencia, calidad y efectos potenciales sobre la salud y el medio ambiente.', NULL, (SELECT id_estandar FROM estándar WHERE grado = 8 AND id_periodo = 4 AND id_materia_oficial = 7 LIMIT 1)),
('Analizo el impacto de artefactos, procesos y sistemas tecnológicos en la solución de problemas y satisfacción de necesidades.', NULL, (SELECT id_estandar FROM estándar WHERE grado = 8 AND id_periodo = 4 AND id_materia_oficial = 7 LIMIT 1)),
('Hace uso de herramientas tecnológicas y recursos de la web para buscar y validar información.', NULL, (SELECT id_estandar FROM estándar WHERE grado = 9 AND id_periodo = 4 AND id_materia_oficial = 7 LIMIT 1)),
('Utiliza las tecnologías de la información y la comunicación, para apoyar mis procesos de aprendizaje y actividades personales (recolectar, seleccionar, organizar y procesar información).', NULL, (SELECT id_estandar FROM estándar WHERE grado = 9 AND id_periodo = 4 AND id_materia_oficial = 7 LIMIT 1)),
('Reconoce las principales herramientas tecnológicas de trabajo colaborativo.', NULL, (SELECT id_estandar FROM estándar WHERE grado = 9 AND id_periodo = 4 AND id_materia_oficial = 7 LIMIT 1));

INSERT INTO `evidencia_de_aprendizaje` (descripcion_evidencia, id_dba) VALUES
('Hace un uso adecuado del correo electrónico.', (SELECT id_dba FROM dba WHERE nombre_dba LIKE '%Identifico principios científicos aplicados%' AND id_estandar IN (SELECT id_estandar FROM estándar WHERE grado = 8 AND id_periodo = 4 AND id_materia_oficial = 7))),
('Comprime y descomprime archivos o carpetas.', (SELECT id_dba FROM dba WHERE nombre_dba LIKE '%Identifico principios científicos aplicados%' AND id_estandar IN (SELECT id_estandar FROM estándar WHERE grado = 8 AND id_periodo = 4 AND id_materia_oficial = 7))),
('Descarga archivos y programa de Internet.', (SELECT id_dba FROM dba WHERE nombre_dba LIKE '%Identifico principios científicos aplicados%' AND id_estandar IN (SELECT id_estandar FROM estándar WHERE grado = 8 AND id_periodo = 4 AND id_materia_oficial = 7))),
('Realiza conversaciones sincrónicas en la red de Internet.', (SELECT id_dba FROM dba WHERE nombre_dba LIKE '%Identifico principios científicos aplicados%' AND id_estandar IN (SELECT id_estandar FROM estándar WHERE grado = 8 AND id_periodo = 4 AND id_materia_oficial = 7))),
('Realiza transferencia de archivos a través del servicio FTP (file transfer protocolo).', (SELECT id_dba FROM dba WHERE nombre_dba LIKE '%Identifico principios científicos aplicados%' AND id_estandar IN (SELECT id_estandar FROM estándar WHERE grado = 8 AND id_periodo = 4 AND id_materia_oficial = 7))),
('Realiza conversaciones utilizando el servicio de programas de chat, Messenger, Skype, etc.', (SELECT id_dba FROM dba WHERE nombre_dba LIKE '%Identifico principios científicos aplicados%' AND id_estandar IN (SELECT id_estandar FROM estándar WHERE grado = 8 AND id_periodo = 4 AND id_materia_oficial = 7))),
('Usa la computadora como herramienta de comunicación e información mediante la navegación por la web. (Sexualidad)', (SELECT id_dba FROM dba WHERE nombre_dba LIKE '%Identifico principios científicos aplicados%' AND id_estandar IN (SELECT id_estandar FROM estándar WHERE grado = 8 AND id_periodo = 4 AND id_materia_oficial = 7))),
('Identifica los principales servicios que ofrece la Web 2.0.', (SELECT id_dba FROM dba WHERE nombre_dba LIKE '%Hace uso de herramientas tecnológicas%' AND id_estandar IN (SELECT id_estandar FROM estándar WHERE grado = 9 AND id_periodo = 4 AND id_materia_oficial = 7))),
('Diferencia los tipos de comunicación existente en la Web.', (SELECT id_dba FROM dba WHERE nombre_dba LIKE '%Hace uso de herramientas tecnológicas%' AND id_estandar IN (SELECT id_estandar FROM estándar WHERE grado = 9 AND id_periodo = 4 AND id_materia_oficial = 7))),
('Utiliza el Blog como herramienta de trabajo colaborativo en la publicación del proyecto de sexualidad. (Sexualidad).', (SELECT id_dba FROM dba WHERE nombre_dba LIKE '%Hace uso de herramientas tecnológicas%' AND id_estandar IN (SELECT id_estandar FROM estándar WHERE grado = 9 AND id_periodo = 4 AND id_materia_oficial = 7)));


INSERT INTO `eje_tematico` (nombre_eje_tematico, descripcion_eje_tematico, id_dba) VALUES
('Principales servicios de internet', NULL, (SELECT id_dba FROM dba WHERE nombre_dba LIKE '%Identifico principios científicos aplicados%' AND id_estandar IN (SELECT id_estandar FROM estándar WHERE grado = 8 AND id_periodo = 4 AND id_materia_oficial = 7))),
('La World Wide Web', NULL, (SELECT id_dba FROM dba WHERE nombre_dba LIKE '%Identifico principios científicos aplicados%' AND id_estandar IN (SELECT id_estandar FROM estándar WHERE grado = 8 AND id_periodo = 4 AND id_materia_oficial = 7))),
('El Correo Electrónico y sus ventajas.', NULL, (SELECT id_dba FROM dba WHERE nombre_dba LIKE '%Identifico principios científicos aplicados%' AND id_estandar IN (SELECT id_estandar FROM estándar WHERE grado = 8 AND id_periodo = 4 AND id_materia_oficial = 7))),
('Las cuentas de e-mail gratuitas.', NULL, (SELECT id_dba FROM dba WHERE nombre_dba LIKE '%Identifico principios científicos aplicados%' AND id_estandar IN (SELECT id_estandar FROM estándar WHERE grado = 8 AND id_periodo = 4 AND id_materia_oficial = 7))),
('Compresión y descompresión de archivos.', NULL, (SELECT id_dba FROM dba WHERE nombre_dba LIKE '%Identifico principios científicos aplicados%' AND id_estandar IN (SELECT id_estandar FROM estándar WHERE grado = 8 AND id_periodo = 4 AND id_materia_oficial = 7))),
('Enviar y recibir mensajes de correo electrónicos.', NULL, (SELECT id_dba FROM dba WHERE nombre_dba LIKE '%Identifico principios científicos aplicados%' AND id_estandar IN (SELECT id_estandar FROM estándar WHERE grado = 8 AND id_periodo = 4 AND id_materia_oficial = 7))),
('Adjuntar archivos al correo electrónico.', NULL, (SELECT id_dba FROM dba WHERE nombre_dba LIKE '%Identifico principios científicos aplicados%' AND id_estandar IN (SELECT id_estandar FROM estándar WHERE grado = 8 AND id_periodo = 4 AND id_materia_oficial = 7))),
('Descargar archivos adjuntos desde el correo electrónico.', NULL, (SELECT id_dba FROM dba WHERE nombre_dba LIKE '%Identifico principios científicos aplicados%' AND id_estandar IN (SELECT id_estandar FROM estándar WHERE grado = 8 AND id_periodo = 4 AND id_materia_oficial = 7))),
('Los Grupos de Noticias.', NULL, (SELECT id_dba FROM dba WHERE nombre_dba LIKE '%Identifico principios científicos aplicados%' AND id_estandar IN (SELECT id_estandar FROM estándar WHERE grado = 8 AND id_periodo = 4 AND id_materia_oficial = 7))),
('Transferencias de archivos o el FTP.', NULL, (SELECT id_dba FROM dba WHERE nombre_dba LIKE '%Identifico principios científicos aplicados%' AND id_estandar IN (SELECT id_estandar FROM estándar WHERE grado = 8 AND id_periodo = 4 AND id_materia_oficial = 7))),
('El IRC o chat.', NULL, (SELECT id_dba FROM dba WHERE nombre_dba LIKE '%Identifico principios científicos aplicados%' AND id_estandar IN (SELECT id_estandar FROM estándar WHERE grado = 8 AND id_periodo = 4 AND id_materia_oficial = 7))),
('La Web 2.0', NULL, (SELECT id_dba FROM dba WHERE nombre_dba LIKE '%Hace uso de herramientas tecnológicas%' AND id_estandar IN (SELECT id_estandar FROM estándar WHERE grado = 9 AND id_periodo = 4 AND id_materia_oficial = 7))),
('Introducción y evolución de la Web 2.0.', NULL, (SELECT id_dba FROM dba WHERE nombre_dba LIKE '%Hace uso de herramientas tecnológicas%' AND id_estandar IN (SELECT id_estandar FROM estándar WHERE grado = 9 AND id_periodo = 4 AND id_materia_oficial = 7))),
('Diferencias entre la Web 1.0 y la Web 2.0 y Web 3.0.', NULL, (SELECT id_dba FROM dba WHERE nombre_dba LIKE '%Hace uso de herramientas tecnológicas%' AND id_estandar IN (SELECT id_estandar FROM estándar WHERE grado = 9 AND id_periodo = 4 AND id_materia_oficial = 7))),
('Servicios que ofrece la Web 2.0', NULL, (SELECT id_dba FROM dba WHERE nombre_dba LIKE '%Hace uso de herramientas tecnológicas%' AND id_estandar IN (SELECT id_estandar FROM estándar WHERE grado = 9 AND id_periodo = 4 AND id_materia_oficial = 7))),
('El Blog', NULL, (SELECT id_dba FROM dba WHERE nombre_dba LIKE '%Hace uso de herramientas tecnológicas%' AND id_estandar IN (SELECT id_estandar FROM estándar WHERE grado = 9 AND id_periodo = 4 AND id_materia_oficial = 7))),
('Ventajas y desventajas', NULL, (SELECT id_dba FROM dba WHERE nombre_dba LIKE '%Hace uso de herramientas tecnológicas%' AND id_estandar IN (SELECT id_estandar FROM estándar WHERE grado = 9 AND id_periodo = 4 AND id_materia_oficial = 7))),
('La Comunicación', NULL, (SELECT id_dba FROM dba WHERE nombre_dba LIKE '%Hace uso de herramientas tecnológicas%' AND id_estandar IN (SELECT id_estandar FROM estándar WHERE grado = 9 AND id_periodo = 4 AND id_materia_oficial = 7))),
('Sincrónica', NULL, (SELECT id_dba FROM dba WHERE nombre_dba LIKE '%Hace uso de herramientas tecnológicas%' AND id_estandar IN (SELECT id_estandar FROM estándar WHERE grado = 9 AND id_periodo = 4 AND id_materia_oficial = 7))),
('Asincrónica', NULL, (SELECT id_dba FROM dba WHERE nombre_dba LIKE '%Hace uso de herramientas tecnológicas%' AND id_estandar IN (SELECT id_estandar FROM estándar WHERE grado = 9 AND id_periodo = 4 AND id_materia_oficial = 7)));


-- Para la tabla materia_oficial:
-- Ya existe un registro para Tecnología e informática con id_materia = 7, así que no es necesario insertar uno nuevo.

-- Para la tabla estándar:
INSERT INTO `estándar` (nombre_estandar, descripcion_estandar, grado, id_periodo, id_materia_oficial) VALUES
('Analiza y valora críticamente los componentes y evolución de los sistemas tecnológicos y las estrategias para su desarrollo.', NULL, 10, 4, 7),
('Tiene en cuenta principios de funcionamiento y criterios de selección, para la utilización eficiente y segura de artefactos, productos, servicios, procesos y sistemas tecnológicos de mi entorno.', NULL, 10, 4, 7),
('Resuelve problemas tecnológicos y evalúa las soluciones teniendo en cuenta las condiciones, restricciones y especificaciones del problema planteado.', NULL, 10, 4, 7),
('Reconoce principios y conceptos propios de la tecnología, así como momentos de la historia que le han permitido al hombre transformar el entorno para resolver problemas y satisfacer necesidades.', NULL, 11, 4, 7),
('Propone estrategias para soluciones tecnológicas a problemas, en diferentes contextos.', NULL, 11, 4, 7);

-- Para la tabla dba:
INSERT INTO `dba` (nombre_dba, descripcion_dba, id_estandar) VALUES
('Indaga sobre la prospectiva e incidencia de algunos desarrollos tecnológicos.', NULL, (SELECT id_estandar FROM estándar WHERE grado = 10 AND id_periodo = 4 AND id_materia_oficial = 7 LIMIT 1)),
('Integra componentes y pone en marcha sistemas informáticos personales utilizando manuales de instrucciones.', NULL, (SELECT id_estandar FROM estándar WHERE grado = 10 AND id_periodo = 4 AND id_materia_oficial = 7 LIMIT 1)),
('Identifica las condiciones, especificaciones y restricciones de diseño, utilizadas en una solución tecnológica y puede verificar su cumplimiento.', NULL, (SELECT id_estandar FROM estándar WHERE grado = 10 AND id_periodo = 4 AND id_materia_oficial = 7 LIMIT 1)),
('Hace uso de herramientas tecnológicas y recursos de la Web para buscar y validar información.', NULL, (SELECT id_estandar FROM estándar WHERE grado = 11 AND id_periodo = 4 AND id_materia_oficial = 7 LIMIT 1)),
('Utiliza las tecnologías de la información y la comunicación, para apoyar mis procesos de aprendizaje y actividades personales (recolectar, seleccionar, organizar y procesar información).', NULL, (SELECT id_estandar FROM estándar WHERE grado = 11 AND id_periodo = 4 AND id_materia_oficial = 7 LIMIT 1));

-- Para la tabla evidencia_de_aprendizaje:
INSERT INTO `evidencia_de_aprendizaje` (descripcion_evidencia, id_dba) VALUES
('Reconoce los conceptos básicos de Google drive en el tratamiento de sus diferentes aplicaciones.', (SELECT id_dba FROM dba WHERE nombre_dba LIKE '%Indaga sobre la prospectiva%' LIMIT 1)),
('Desarrolla formularios para la elaboración de encuestas y exámenes en la publicación de la información.', (SELECT id_dba FROM dba WHERE nombre_dba LIKE '%Indaga sobre la prospectiva%' LIMIT 1)),
('Administra adecuadamente las aplicaciones de Google drive.', (SELECT id_dba FROM dba WHERE nombre_dba LIKE '%Indaga sobre la prospectiva%' LIMIT 1)),
('Usa la computadora como herramienta de comunicación e información mediante la creación de encuestas dirigidas a sus compañeros y miembros de la comunidad. (Sexualidad)', (SELECT id_dba FROM dba WHERE nombre_dba LIKE '%Indaga sobre la prospectiva%' LIMIT 1)),
('Identifica como los cambios ambientales afectan la producción de alimentos.', (SELECT id_dba FROM dba WHERE nombre_dba LIKE '%Indaga sobre la prospectiva%' LIMIT 1)),
('Reconoce los factores que influyen en la producción de alimentos de origen vegetal.', (SELECT id_dba FROM dba WHERE nombre_dba LIKE '%Indaga sobre la prospectiva%' LIMIT 1)),
('Comprende la importancia de las redes sociales en la comunicación sincrónica.', (SELECT id_dba FROM dba WHERE nombre_dba LIKE '%Hace uso de herramientas tecnológicas%' LIMIT 1)),
('Maneja herramientas tecnológicas de trabajo colaborativo.', (SELECT id_dba FROM dba WHERE nombre_dba LIKE '%Hace uso de herramientas tecnológicas%' LIMIT 1)),
('Elabora una Wiki como herramienta de trabajo colaborativo en la publicación de temáticas de sexualidad. (Sexualidad).', (SELECT id_dba FROM dba WHERE nombre_dba LIKE '%Hace uso de herramientas tecnológicas%' LIMIT 1)),
('Reconoce los elementos que conforman un sistema tecnológico (entrada, proceso y salida).', (SELECT id_dba FROM dba WHERE nombre_dba LIKE '%Hace uso de herramientas tecnológicas%' LIMIT 1)),
('Identifica y diferencia cada uno de los tipos tecnológicos utilizados para satisfacer las necesidades humanas.', (SELECT id_dba FROM dba WHERE nombre_dba LIKE '%Hace uso de herramientas tecnológicas%' LIMIT 1));

-- Para la tabla eje_tematico:
INSERT INTO `eje_tematico` (nombre_eje_tematico, descripcion_eje_tematico, id_dba) VALUES
('Google Drive – creación de formularios', NULL, (SELECT id_dba FROM dba WHERE nombre_dba LIKE '%Indaga sobre la prospectiva%' LIMIT 1)),
('¿Qué es Google Drive?', NULL, (SELECT id_dba FROM dba WHERE nombre_dba LIKE '%Indaga sobre la prospectiva%' LIMIT 1)),
('Características de Google Drive.', NULL, (SELECT id_dba FROM dba WHERE nombre_dba LIKE '%Indaga sobre la prospectiva%' LIMIT 1)),
('Formularios en Google Drive.', NULL, (SELECT id_dba FROM dba WHERE nombre_dba LIKE '%Indaga sobre la prospectiva%' LIMIT 1)),
('Pasos para construir un formulario.', NULL, (SELECT id_dba FROM dba WHERE nombre_dba LIKE '%Indaga sobre la prospectiva%' LIMIT 1)),
('Tipos de preguntas', NULL, (SELECT id_dba FROM dba WHERE nombre_dba LIKE '%Indaga sobre la prospectiva%' LIMIT 1)),
('Agregar elementos a un formulario.', NULL, (SELECT id_dba FROM dba WHERE nombre_dba LIKE '%Indaga sobre la prospectiva%' LIMIT 1)),
('Producción de alimentos.', NULL, (SELECT id_dba FROM dba WHERE nombre_dba LIKE '%Indaga sobre la prospectiva%' LIMIT 1)),
('¿Qué es la producción de los alimentos?', NULL, (SELECT id_dba FROM dba WHERE nombre_dba LIKE '%Indaga sobre la prospectiva%' LIMIT 1)),
('¿Cómo se producen los alimentos?', NULL, (SELECT id_dba FROM dba WHERE nombre_dba LIKE '%Indaga sobre la prospectiva%' LIMIT 1)),
('¿Cómo afecta el medio ambiente la producción de alimentos?', NULL, (SELECT id_dba FROM dba WHERE nombre_dba LIKE '%Indaga sobre la prospectiva%' LIMIT 1)),
('¿Qué factores influyen en la producción de alimentos de origen vegetal?', NULL, (SELECT id_dba FROM dba WHERE nombre_dba LIKE '%Indaga sobre la prospectiva%' LIMIT 1)),
('Agricultura Biodinámica.', NULL, (SELECT id_dba FROM dba WHERE nombre_dba LIKE '%Indaga sobre la prospectiva%' LIMIT 1)),
('¿Qué es la agricultura Biodinámica?', NULL, (SELECT id_dba FROM dba WHERE nombre_dba LIKE '%Indaga sobre la prospectiva%' LIMIT 1)),
('Origen de la agricultura biodinámica.', NULL, (SELECT id_dba FROM dba WHERE nombre_dba LIKE '%Indaga sobre la prospectiva%' LIMIT 1)),
('¿Cómo se practica la agricultura biodinámica?', NULL, (SELECT id_dba FROM dba WHERE nombre_dba LIKE '%Indaga sobre la prospectiva%' LIMIT 1)),
('Servicios de la Web 2.0.', NULL, (SELECT id_dba FROM dba WHERE nombre_dba LIKE '%Hace uso de herramientas tecnológicas%' LIMIT 1)),
('Los Foros.', NULL, (SELECT id_dba FROM dba WHERE nombre_dba LIKE '%Hace uso de herramientas tecnológicas%' LIMIT 1)),
('Las redes sociales.', NULL, (SELECT id_dba FROM dba WHERE nombre_dba LIKE '%Hace uso de herramientas tecnológicas%' LIMIT 1)),
('Los blogs.', NULL, (SELECT id_dba FROM dba WHERE nombre_dba LIKE '%Hace uso de herramientas tecnológicas%' LIMIT 1)),
('Las Wikis.', NULL, (SELECT id_dba FROM dba WHERE nombre_dba LIKE '%Hace uso de herramientas tecnológicas%' LIMIT 1)),
('Los Smartphone.', NULL, (SELECT id_dba FROM dba WHERE nombre_dba LIKE '%Hace uso de herramientas tecnológicas%' LIMIT 1)),
('El WhatsApp.', NULL, (SELECT id_dba FROM dba WHERE nombre_dba LIKE '%Hace uso de herramientas tecnológicas%' LIMIT 1)),
('Los Sistemas Tecnológicos.', NULL, (SELECT id_dba FROM dba WHERE nombre_dba LIKE '%Hace uso de herramientas tecnológicas%' LIMIT 1)),
('¿Qué es un sistema?', NULL, (SELECT id_dba FROM dba WHERE nombre_dba LIKE '%Hace uso de herramientas tecnológicas%' LIMIT 1)),
('Elementos de un sistema.', NULL, (SELECT id_dba FROM dba WHERE nombre_dba LIKE '%Hace uso de herramientas tecnológicas%' LIMIT 1)),
('Ejemplos de sistemas.', NULL, (SELECT id_dba FROM dba WHERE nombre_dba LIKE '%Hace uso de herramientas tecnológicas%' LIMIT 1)),
('¿Qué es un sistema tecnológico?', NULL, (SELECT id_dba FROM dba WHERE nombre_dba LIKE '%Hace uso de herramientas tecnológicas%' LIMIT 1)),
('Tipos de sistemas tecnológicos.', NULL, (SELECT id_dba FROM dba WHERE nombre_dba LIKE '%Hace uso de herramientas tecnológicas%' LIMIT 1));

###########

-- Inserción para la tabla estándar
INSERT INTO `estándar` (nombre_estandar, descripcion_estandar, grado, id_periodo, id_materia_oficial) VALUES
('Conjeturo acerca del resultado de un experimento aleatorio usando proporcionalidad y nociones básicas de probabilidad.', NULL, 6, 4, 1),
('Justifico el uso de representaciones y procedimientos en situaciones de proporcionalidad directa e inversa. Analizo las propiedades de correlación positiva y negativa entre variables, de variación lineal o de proporcionalidad directa y de proporcionalidad inversa en contextos aritméticos y geométricos.', NULL, 7, 4, 1);

-- Inserción para la tabla dba
INSERT INTO `dba` (nombre_dba, descripcion_dba, id_estandar) VALUES
('A partir de la información previamente obtenida en repeticiones de experimentos aleatorios sencillos, compara las frecuencias esperadas con las frecuencias observadas.', NULL, (SELECT id_estandar FROM estándar WHERE grado = 6 AND id_periodo = 4 AND id_materia_oficial = 1)),
('Plantea preguntas para realizar estudios estadísticos en los que representa información mediante histogramas, polígonos de frecuencia, gráficos de línea entre otros; identifica variaciones, relaciones o tendencias para dar respuesta a las preguntas planteadas.', NULL, (SELECT id_estandar FROM estándar WHERE grado = 7 AND id_periodo = 4 AND id_materia_oficial = 1)),
('Usa el principio multiplicativo en situaciones aleatorias sencillas y lo representa con tablas o diagramas de árbol. Asigna probabilidades a eventos compuestos y los interpreta a partir de propiedades básicas de la probabilidad.', NULL, (SELECT id_estandar FROM estándar WHERE grado = 7 AND id_periodo = 4 AND id_materia_oficial = 1));

-- Inserción para la tabla evidencia_de_aprendizaje
INSERT INTO `evidencia_de_aprendizaje` (descripcion_evidencia, id_dba) VALUES
('Interpreta y asigna la probabilidad de ocurrencia de un evento dado, teniendo en cuenta el número de veces que ocurre el evento en relación con el número total de veces que realiza el experimento.', (SELECT id_dba FROM dba WHERE nombre_dba LIKE '%A partir de la información previamente obtenida%')),
('Compara los resultados obtenidos experimentalmente con las predicciones anticipadas', (SELECT id_dba FROM dba WHERE nombre_dba LIKE '%A partir de la información previamente obtenida%')),
('Plantea preguntas, diseña y realiza un plan para recolectar la información pertinente.', (SELECT id_dba FROM dba WHERE nombre_dba LIKE '%Plantea preguntas para realizar estudios estadísticos%')),
('Construye tablas de frecuencia y gráficos (histogramas, polígonos de frecuencia, gráficos de línea, entre otros), para datos agrupados usando, calculadoras o software adecuado.', (SELECT id_dba FROM dba WHERE nombre_dba LIKE '%Plantea preguntas para realizar estudios estadísticos%')),
('Encuentra e interpreta las medidas de tendencia central y el rango en datos agrupados, empleando herramientas tecnológicas cuando sea posible.', (SELECT id_dba FROM dba WHERE nombre_dba LIKE '%Plantea preguntas para realizar estudios estadísticos%')),
('Analiza la información presentada identificando variaciones, relaciones o tendencias y elabora conclusiones que permiten responder la pregunta planteada.', (SELECT id_dba FROM dba WHERE nombre_dba LIKE '%Plantea preguntas para realizar estudios estadísticos%')),
('Elabora tablas o diagramas de árbol para representar las distintas maneras en que un experimento aleatorio puede suceder.', (SELECT id_dba FROM dba WHERE nombre_dba LIKE '%Usa el principio multiplicativo en situaciones aleatorias%')),
('Usa el principio multiplicativo para calcular el número de resultados posibles en situaciones aleatorias sencillas.', (SELECT id_dba FROM dba WHERE nombre_dba LIKE '%Usa el principio multiplicativo en situaciones aleatorias%'));

-- Inserción para la tabla eje_tematico
INSERT INTO `eje_tematico` (nombre_eje_tematico, descripcion_eje_tematico, id_dba) VALUES
('Probabilidad simple', NULL, (SELECT id_dba FROM dba WHERE nombre_dba LIKE '%A partir de la información previamente obtenida%')),
('Propiedades de la probabilidad', NULL, (SELECT id_dba FROM dba WHERE nombre_dba LIKE '%A partir de la información previamente obtenida%')),
('Principio de multiplicación', NULL, (SELECT id_dba FROM dba WHERE nombre_dba LIKE '%A partir de la información previamente obtenida%')),
('Caracterización de datos y probabilidad', NULL, (SELECT id_dba FROM dba WHERE nombre_dba LIKE '%A partir de la información previamente obtenida%')),
('Coordenadas, gráficas y estadísticas', NULL, (SELECT id_dba FROM dba WHERE nombre_dba LIKE '%Plantea preguntas para realizar estudios estadísticos%')),
('Tablas y gráficas frecuencias', NULL, (SELECT id_dba FROM dba WHERE nombre_dba LIKE '%Plantea preguntas para realizar estudios estadísticos%')),
('Probabilidad', NULL, (SELECT id_dba FROM dba WHERE nombre_dba LIKE '%Usa el principio multiplicativo en situaciones aleatorias%')),
('Nociones de probabilidad', NULL, (SELECT id_dba FROM dba WHERE nombre_dba LIKE '%Usa el principio multiplicativo en situaciones aleatorias%')),
('Experimentos aleatorios', NULL, (SELECT id_dba FROM dba WHERE nombre_dba LIKE '%Usa el principio multiplicativo en situaciones aleatorias%')),
('Frecuencia de un suceso', NULL, (SELECT id_dba FROM dba WHERE nombre_dba LIKE '%Usa el principio multiplicativo en situaciones aleatorias%'));


INSERT INTO `estándar` (nombre_estandar, descripcion_estandar, grado, id_periodo, id_materia_oficial) VALUES
('Reconozco cómo diferentes maneras de presentación de información pueden originar distintas interpretaciones. Interpreto analítica y críticamente información estadística proveniente de diversas fuentes (prensa, revistas, televisión, experimentos, consultas, entrevistas. Interpreto y utilizo conceptos de media, mediana y moda y explicito sus diferencias en distribuciones de distinta dispersión y asimetría. Generalizo procedimientos de cálculo válidos para encontrar el área de regiones planas y el volumen de sólidos. Selecciono y uso técnicas e instrumentos para medir longitudes, áreas de superficies, volúmenes y ángulos con niveles de precisión apropiados.', NULL, 8, 4, 1),
('Conjeturo y verifico propiedades de congruencias y semejanzas entre figuras bidimensionales y entre objetos tridimensionales en la solución de problemas. Reconozco y contrasto propiedades y relaciones geométricas utilizadas en demostración de teoremas básicos (Pitágoras y Tales). Aplico y justifico criterios de congruencias y semejanza entre triángulos en la resolución y formulación de problemas. Uso representaciones geométricas para resolver y formular problemas en las matemáticas y en otras disciplinas. Selecciono y uso algunos métodos estadísticos adecuados al tipo de problema, de información y al nivel de la escala en la que esta se representa (nominal, ordinal, de intervalo o de razón). Comparo resultados de experimentos aleatorios con los resultados previstos por un modelo matemático probabilístico. Resuelvo y formulo problemas seleccionando información relevante en conjuntos de datos provenientes de fuentes diversas. (prensa, revistas, televisión, experimentos, consultas, entrevistas). Calculo probabilidad de eventos simples usando métodos diversos (listados, diagramas de árbol, técnicas de conteo). Uso conceptos básicos de probabilidad (espacio muestral, evento, independencia, etc.).', NULL, 9, 4, 1);

INSERT INTO `dba` (nombre_dba, descripcion_dba, id_estandar) VALUES
('Interpreta información presentada en tablas de frecuencia y gráficos cuyas datos están agrupados en intervalos y decide cuál es la medida de tendencia central que mejor representa el comportamiento de dicho conjunto.', NULL, (SELECT id_estandar FROM estándar WHERE grado = 8 AND id_periodo = 4 AND id_materia_oficial = 1)),
('Hace predicciones sobre la posibilidad de ocurrencia de un evento compuesto e interpreta la predicción a partir del uso de propiedades básicas de la probabilidad', NULL, (SELECT id_estandar FROM estándar WHERE grado = 8 AND id_periodo = 4 AND id_materia_oficial = 1)),
('Identifica regularidades y argumenta propiedades de figuras geométricas a partir de teoremas y las aplica en situaciones reales.', NULL, (SELECT id_estandar FROM estándar WHERE grado = 8 AND id_periodo = 4 AND id_materia_oficial = 1)),
('Identifica y utiliza relaciones entre el volumen y la capacidad de algunos cuerpos redondos (cilindro, cono y esfera) con referencia a las situaciones escolares y extraescolares', NULL, (SELECT id_estandar FROM estándar WHERE grado = 9 AND id_periodo = 4 AND id_materia_oficial = 1)),
('Utiliza teoremas, propiedades y relaciones geométricas (teorema de Thales y el teorema de Pitágoras) para proponer y justificar estrategias de medición y cálculo de longitudes', NULL, (SELECT id_estandar FROM estándar WHERE grado = 9 AND id_periodo = 4 AND id_materia_oficial = 1)),
('Conjetura acerca de las regularidades de las formas bidimensionales y tridimensionales y realiza inferencias a partir de los criterios de semejanza, congruencia y teoremas básicos.', NULL, (SELECT id_estandar FROM estándar WHERE grado = 9 AND id_periodo = 4 AND id_materia_oficial = 1)),
('Propone un diseño estadístico adecuado para resolver una pregunta que indaga por la comparación sobre las distribuciones de dos grupos de datos, para lo cual usa comprensivamente diagramas de caja, medidas de tendencia central, de variación y de localización.', NULL, (SELECT id_estandar FROM estándar WHERE grado = 9 AND id_periodo = 4 AND id_materia_oficial = 1)),
('Encuentra el número de posibles resultados de experimentos aleatorios, con reemplazo y sin reemplazo, usando técnicas de conteo adecuadas, y argumenta la selección realizada en el contexto de la situación abordada. Encuentra la probabilidad de eventos aleatorios compuestos.', NULL, (SELECT id_estandar FROM estándar WHERE grado = 9 AND id_periodo = 4 AND id_materia_oficial = 1));

INSERT INTO `evidencia_de_aprendizaje` (descripcion_evidencia, id_dba) VALUES
('Interpreta los datos representados en diferentes tablas y gráficos.', (SELECT id_dba FROM dba WHERE nombre_dba LIKE '%Interpreta información presentada en tablas de frecuencia%')),
('Usa estrategias gráficas o numéricas para encontrar las medidas de tendencia central de un conjunto de datos agrupados.', (SELECT id_dba FROM dba WHERE nombre_dba LIKE '%Interpreta información presentada en tablas de frecuencia%')),
('Describe el comportamiento de los datos empleando las medidas de tendencia central y el rango.', (SELECT id_dba FROM dba WHERE nombre_dba LIKE '%Interpreta información presentada en tablas de frecuencia%')),
('Reconoce cómo varían las medidas de tendencia central y el rango cuando varían los datos.', (SELECT id_dba FROM dba WHERE nombre_dba LIKE '%Interpreta información presentada en tablas de frecuencia%')),
('Identifica y enumera el espacio muestral de un experimento aleatorio.', (SELECT id_dba FROM dba WHERE nombre_dba LIKE '%Hace predicciones sobre la posibilidad de ocurrencia%')),
('Identifica y enumera los resultados favorables de ocurrencia de un evento indicado.', (SELECT id_dba FROM dba WHERE nombre_dba LIKE '%Hace predicciones sobre la posibilidad de ocurrencia%')),
('Asigna la probabilidad de la ocurrencia de un evento usando valores entre 0 y 1.', (SELECT id_dba FROM dba WHERE nombre_dba LIKE '%Hace predicciones sobre la posibilidad de ocurrencia%')),
('Reconoce cuando dos eventos son o no mutuamente excluyentes y les asigna la probabilidad usando la regla de la adición', (SELECT id_dba FROM dba WHERE nombre_dba LIKE '%Hace predicciones sobre la posibilidad de ocurrencia%')),
('Reconoce regularidades en formas bidimensionales y tridimensionales.', (SELECT id_dba FROM dba WHERE nombre_dba LIKE '%Conjetura acerca de las regularidades%')),
('Explica criterios de semejanza y congruencia a partir del teorema de Thales.', (SELECT id_dba FROM dba WHERE nombre_dba LIKE '%Conjetura acerca de las regularidades%')),
('Compara figuras geométricas y conjetura sobre posibles regularidades.', (SELECT id_dba FROM dba WHERE nombre_dba LIKE '%Conjetura acerca de las regularidades%')),
('Redacta y argumenta procesos llevados a cabo para resolver situaciones de semejanza y congruencia de figuras.', (SELECT id_dba FROM dba WHERE nombre_dba LIKE '%Conjetura acerca de las regularidades%')),
('Estima la capacidad de objetos con superficies redondas.', (SELECT id_dba FROM dba WHERE nombre_dba LIKE '%Identifica y utiliza relaciones entre el volumen%')),
('Construye cuerpos redondos usando diferentes estrategias.', (SELECT id_dba FROM dba WHERE nombre_dba LIKE '%Identifica y utiliza relaciones entre el volumen%')),
('Compara y representa las relaciones que encuentra de manera experimental entre el volumen y la capacidad de objetos con superficies redondas.', (SELECT id_dba FROM dba WHERE nombre_dba LIKE '%Identifica y utiliza relaciones entre el volumen%')),
('Explica la pertinencia o no de la solución de un problema de cálculo de área o de volumen, de acuerdo con las condiciones de la situación.', (SELECT id_dba FROM dba WHERE nombre_dba LIKE '%Identifica y utiliza relaciones entre el volumen%')),
('Describe y justifica procesos de medición de longitudes.', (SELECT id_dba FROM dba WHERE nombre_dba LIKE '%Utiliza teoremas, propiedades y relaciones geométricas%')),
('Explica propiedades de figuras geométricas que se involucran en los procesos de medición.', (SELECT id_dba FROM dba WHERE nombre_dba LIKE '%Utiliza teoremas, propiedades y relaciones geométricas%')),
('Justifica procedimientos de medición a partir del Teorema de Thales, Teorema de Pitágoras y relaciones intra e interfigurales.', (SELECT id_dba FROM dba WHERE nombre_dba LIKE '%Utiliza teoremas, propiedades y relaciones geométricas%')),
('Valida la precisión de instrumentos para medir longitudes.', (SELECT id_dba FROM dba WHERE nombre_dba LIKE '%Utiliza teoremas, propiedades y relaciones geométricas%')),
('Propone alternativas para estimar y medir con precisión diferentes magnitudes', (SELECT id_dba FROM dba WHERE nombre_dba LIKE '%Utiliza teoremas, propiedades y relaciones geométricas%')),
('Define el método para recolectar los datos (encuestas, observación o experimento simple) e identifica la población y el tamaño de la muestra del estudio.', (SELECT id_dba FROM dba WHERE nombre_dba LIKE '%Propone un diseño estadístico adecuado%')),
('Construye diagramas de caja y a partir de los resultados representados en ellos describe y compara la distribución de un conjunto de datos.', (SELECT id_dba FROM dba WHERE nombre_dba LIKE '%Propone un diseño estadístico adecuado%')),
('Compara las distribuciones de los conjuntos de datos a partir de las medidas de tendencia central, las de variación y las de localización.', (SELECT id_dba FROM dba WHERE nombre_dba LIKE '%Propone un diseño estadístico adecuado%')),
('Elabora conclusiones para responder el problema planteado', (SELECT id_dba FROM dba WHERE nombre_dba LIKE '%Propone un diseño estadístico adecuado%')),
('Diferencia experimentos aleatorios realizados con reemplazo, de experimentos aleatorios realizados sin reemplazo.', (SELECT id_dba FROM dba WHERE nombre_dba LIKE '%Encuentra el número de posibles resultados%')),
('Encuentra el número de posibles resultados de un experimento aleatorio, usando métodos adecuados (diagramas de árbol, combinaciones, permutaciones, regla de la multiplicación, etc.).', (SELECT id_dba FROM dba WHERE nombre_dba LIKE '%Encuentra el número de posibles resultados%')),
('Justifica la elección de un método particular de acuerdo al tipo de situación.', (SELECT id_dba FROM dba WHERE nombre_dba LIKE '%Encuentra el número de posibles resultados%')),
('Encuentra la probabilidad de eventos dados usando razón entre frecuencias', (SELECT id_dba FROM dba WHERE nombre_dba LIKE '%Encuentra el número de posibles resultados%'));


INSERT INTO `eje_tematico` (nombre_eje_tematico, descripcion_eje_tematico, id_dba) VALUES
('Estadística y probabilidad', NULL, (SELECT id_dba FROM dba WHERE nombre_dba LIKE '%Interpreta información presentada en tablas de frecuencia%')),
('Característica de variables cuantitativas por datos no agrupados', NULL, (SELECT id_dba FROM dba WHERE nombre_dba LIKE '%Interpreta información presentada en tablas de frecuencia%')),
('Medidas de dispersión y rango', NULL, (SELECT id_dba FROM dba WHERE nombre_dba LIKE '%Interpreta información presentada en tablas de frecuencia%')),
('Características de variables cuantitativas, continuas, para datos agrupados', NULL, (SELECT id_dba FROM dba WHERE nombre_dba LIKE '%Interpreta información presentada en tablas de frecuencia%')),
('Conjunto y eventos', NULL, (SELECT id_dba FROM dba WHERE nombre_dba LIKE '%Hace predicciones sobre la posibilidad de ocurrencia%')),
('Principio de multiplicación', NULL, (SELECT id_dba FROM dba WHERE nombre_dba LIKE '%Hace predicciones sobre la posibilidad de ocurrencia%')),
('Medidas de tendencia central', NULL, (SELECT id_dba FROM dba WHERE nombre_dba LIKE '%Hace predicciones sobre la posibilidad de ocurrencia%')),
('Ángulos y triángulos', NULL, (SELECT id_dba FROM dba WHERE nombre_dba LIKE '%Identifica regularidades y argumenta propiedades%')),
('Propiedades de los triángulos', NULL, (SELECT id_dba FROM dba WHERE nombre_dba LIKE '%Identifica regularidades y argumenta propiedades%')),
('Clasificación de los triángulos', NULL, (SELECT id_dba FROM dba WHERE nombre_dba LIKE '%Identifica regularidades y argumenta propiedades%')),
('Congruencia de triángulos', NULL, (SELECT id_dba FROM dba WHERE nombre_dba LIKE '%Identifica regularidades y argumenta propiedades%')),
('Longitud y área', NULL, (SELECT id_dba FROM dba WHERE nombre_dba LIKE '%Identifica regularidades y argumenta propiedades%')),
('Resolución de problemas', NULL, (SELECT id_dba FROM dba WHERE nombre_dba LIKE '%Identifica regularidades y argumenta propiedades%')),
('Teorema de Pitágoras', NULL, (SELECT id_dba FROM dba WHERE nombre_dba LIKE '%Identifica regularidades y argumenta propiedades%')),
('Cuerpos geométricos', NULL, (SELECT id_dba FROM dba WHERE nombre_dba LIKE '%Identifica y utiliza relaciones entre el volumen%')),
('Cuerpos redondos', NULL, (SELECT id_dba FROM dba WHERE nombre_dba LIKE '%Identifica y utiliza relaciones entre el volumen%')),
('Cilindro, cono, esfera', NULL, (SELECT id_dba FROM dba WHERE nombre_dba LIKE '%Identifica y utiliza relaciones entre el volumen%')),
('Poliedros', NULL, (SELECT id_dba FROM dba WHERE nombre_dba LIKE '%Identifica y utiliza relaciones entre el volumen%')),
('Prisma, pirámide', NULL, (SELECT id_dba FROM dba WHERE nombre_dba LIKE '%Identifica y utiliza relaciones entre el volumen%')),
('Tronco de cono, tronco de pirámide', NULL, (SELECT id_dba FROM dba WHERE nombre_dba LIKE '%Identifica y utiliza relaciones entre el volumen%')),
('Teorema de Thales', NULL, (SELECT id_dba FROM dba WHERE nombre_dba LIKE '%Utiliza teoremas, propiedades y relaciones geométricas%')),
('Semejanza de triángulos', NULL, (SELECT id_dba FROM dba WHERE nombre_dba LIKE '%Utiliza teoremas, propiedades y relaciones geométricas%')),
('Instrumentos de medición', NULL, (SELECT id_dba FROM dba WHERE nombre_dba LIKE '%Utiliza teoremas, propiedades y relaciones geométricas%')),
('Diagramas de caja', NULL, (SELECT id_dba FROM dba WHERE nombre_dba LIKE '%Propone un diseño estadístico adecuado%')),
('Medidas de localización', NULL, (SELECT id_dba FROM dba WHERE nombre_dba LIKE '%Propone un diseño estadístico adecuado%')),
('Diseño de estudios estadísticos', NULL, (SELECT id_dba FROM dba WHERE nombre_dba LIKE '%Propone un diseño estadístico adecuado%')),
('Técnicas de conteo', NULL, (SELECT id_dba FROM dba WHERE nombre_dba LIKE '%Encuentra el número de posibles resultados%')),
('Experimentos con y sin reemplazo', NULL, (SELECT id_dba FROM dba WHERE nombre_dba LIKE '%Encuentra el número de posibles resultados%')),
('Diagramas de árbol', NULL, (SELECT id_dba FROM dba WHERE nombre_dba LIKE '%Encuentra el número de posibles resultados%')),
('Combinaciones y permutaciones', NULL, (SELECT id_dba FROM dba WHERE nombre_dba LIKE '%Encuentra el número de posibles resultados%')),
('Probabilidad de eventos compuestos', NULL, (SELECT id_dba FROM dba WHERE nombre_dba LIKE '%Encuentra el número de posibles resultados%'));

INSERT INTO `estándar` (nombre_estandar, descripcion_estandar, grado, id_periodo, id_materia_oficial) VALUES
('Analizo las relaciones y propiedades entre las expresiones algebraicas y las gráficas de funciones polinómicas y racionales y de sus derivadas. • Modelo situaciones de variación periódica con funciones trigonométricas e interpreto y utilizo sus derivadas.', NULL, 10, 4, 1),
('Analizo las relaciones y propiedades entre las expresiones algebraicas y las gráfi cas de funciones polinómicas y racionales y de sus derivadas. • Modelo situaciones de variación periódica con funciones trigonométricas e interpreto y utilizo sus derivadas.', NULL, 11, 4, 1);

INSERT INTO `dba` (nombre_dba, descripcion_dba, id_estandar) VALUES
('Comprende y utiliza funciones para modelar fenómenos periódicos y justifica las soluciones', NULL, (SELECT id_estandar FROM estándar WHERE grado = 10 AND id_periodo = 4 AND id_materia_oficial = 1)),
('Interpreta la noción de derivada como razón de cambio y como valor de la pendiente de la tangente a una curva y desarrolla métodos para hallar las derivadas de algunas funciones básicas en contextos matemáticos y no matemáticos.', NULL, (SELECT id_estandar FROM estándar WHERE grado = 11 AND id_periodo = 4 AND id_materia_oficial = 1));


INSERT INTO `evidencia_de_aprendizaje` (descripcion_evidencia, id_dba) VALUES
('Reconoce el significado de las razones trigonométricas en un triángulo rectángulo para ángulos agudos, en particular, seno, coseno y tangente.', (SELECT id_dba FROM dba WHERE nombre_dba LIKE '%Comprende y utiliza funciones para modelar fenómenos periódicos%')),
('Explora, en una situación o fenómeno de variación periódica, valores, condiciones, relaciones o comportamientos, a través de diferentes representaciones.', (SELECT id_dba FROM dba WHERE nombre_dba LIKE '%Comprende y utiliza funciones para modelar fenómenos periódicos%')),
('Calcula algunos valores de las razones seno y coseno para ángulos no agudos, auxiliándose de ángulos de referencia inscritos en el círculo unitario.', (SELECT id_dba FROM dba WHERE nombre_dba LIKE '%Comprende y utiliza funciones para modelar fenómenos periódicos%')),
('Relaciona la noción derivada con características numéricas, geométricas y métricas.', (SELECT id_dba FROM dba WHERE nombre_dba LIKE '%Interpreta la noción de derivada como razón de cambio%')),
('Utiliza la derivada para estudiar la covariación entre dos magnitudes y relaciona características de la derivada con características de la función.', (SELECT id_dba FROM dba WHERE nombre_dba LIKE '%Interpreta la noción de derivada como razón de cambio%')),
('Halla la derivada de algunas funciones empleando métodos gráficos y numéricos.', (SELECT id_dba FROM dba WHERE nombre_dba LIKE '%Interpreta la noción de derivada como razón de cambio%'));

INSERT INTO `eje_tematico` (nombre_eje_tematico, descripcion_eje_tematico, id_dba) VALUES
('Gráficas y aplicación de las funciones trigonométricas', NULL, (SELECT id_dba FROM dba WHERE nombre_dba LIKE '%Comprende y utiliza funciones para modelar fenómenos periódicos%')),
('Graficas de la función seno, coseno, tangente, cotangente, secante y cosecante', NULL, (SELECT id_dba FROM dba WHERE nombre_dba LIKE '%Comprende y utiliza funciones para modelar fenómenos periódicos%')),
('Análisis de las funciones trigonométricas', NULL, (SELECT id_dba FROM dba WHERE nombre_dba LIKE '%Comprende y utiliza funciones para modelar fenómenos periódicos%')),
('Traslación y reflexión', NULL, (SELECT id_dba FROM dba WHERE nombre_dba LIKE '%Comprende y utiliza funciones para modelar fenómenos periódicos%')),
('Amplitud, periodo desface', NULL, (SELECT id_dba FROM dba WHERE nombre_dba LIKE '%Comprende y utiliza funciones para modelar fenómenos periódicos%')),
('Problemas de aplicación', NULL, (SELECT id_dba FROM dba WHERE nombre_dba LIKE '%Comprende y utiliza funciones para modelar fenómenos periódicos%')),
('Derivadas', NULL, (SELECT id_dba FROM dba WHERE nombre_dba LIKE '%Interpreta la noción de derivada como razón de cambio%')),
('Noción de derivada', NULL, (SELECT id_dba FROM dba WHERE nombre_dba LIKE '%Interpreta la noción de derivada como razón de cambio%')),
('Derivada de una función en un punto', NULL, (SELECT id_dba FROM dba WHERE nombre_dba LIKE '%Interpreta la noción de derivada como razón de cambio%')),
('Recta tangente', NULL, (SELECT id_dba FROM dba WHERE nombre_dba LIKE '%Interpreta la noción de derivada como razón de cambio%')),
('Recta normal', NULL, (SELECT id_dba FROM dba WHERE nombre_dba LIKE '%Interpreta la noción de derivada como razón de cambio%')),
('Reglas de derivación', NULL, (SELECT id_dba FROM dba WHERE nombre_dba LIKE '%Interpreta la noción de derivada como razón de cambio%')),
('Integrales', NULL, (SELECT id_dba FROM dba WHERE nombre_dba LIKE '%Interpreta la noción de derivada como razón de cambio%')),
('Estadística y probabilidad', NULL, (SELECT id_dba FROM dba WHERE nombre_dba LIKE '%Interpreta la noción de derivada como razón de cambio%'));