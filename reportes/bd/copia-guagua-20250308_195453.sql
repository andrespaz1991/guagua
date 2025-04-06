CREATE DATABASE IF NOT EXISTS `guagua`;

USE `guagua`;

DROP TABLE IF EXISTS `actividad`;

CREATE TABLE `actividad` (
  `id_actividad` int(11) NOT NULL AUTO_INCREMENT,
  `id_asignacion` int(10) NOT NULL,
  `fecha_publicacion` date DEFAULT NULL,
  `hora_publicacion` time NOT NULL,
  `id_red` int(11) DEFAULT NULL,
  `nombre_actividad` varchar(120) DEFAULT NULL,
  `Observaciones` text DEFAULT NULL,
  `adjunto` varchar(2) DEFAULT NULL,
  `evaluable` varchar(2) DEFAULT NULL,
  `fecha_entrega` date DEFAULT NULL,
  `hora_entrega` varchar(30) DEFAULT NULL,
  `periodo` int(1) NOT NULL,
  `visible` varchar(2) DEFAULT NULL,
  `cuestionario` varchar(2) DEFAULT NULL,
  `id_cuestionario` int(11) DEFAULT NULL,
  `foro` varchar(2) DEFAULT NULL,
  `id_foro` int(11) DEFAULT NULL,
  PRIMARY KEY (`id_actividad`),
  KEY `id_asignacion_academica` (`id_asignacion`),
  KEY `id_red` (`id_red`),
  KEY `id_cuestionario` (`id_cuestionario`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

INSERT INTO `actividad` VALUES("1","61","2020-08-16","12:23:22",NULL,"Cuadernillo de ejercicios","","SI","SI","2020-08-16","16:27","1","SI","NO",NULL,"NO",NULL);
INSERT INTO `actividad` VALUES("9","61","2020-08-16","12:23:22",NULL,"Cuadernillo de ejercicios","","SI","SI","2020-08-16","16:27","1","SI","NO",NULL,"NO",NULL);
INSERT INTO `actividad` VALUES("10","66","2020-11-03","20:44:07",NULL,"Foro de transformaci├ö├Â┬ú├ö├Â├®n digftal","","NO","SI","2020-11-04","21:44","1","SI","NO",NULL,NULL,NULL);
INSERT INTO `actividad` VALUES("11","69","2021-02-22","19:33:14","273","Crucigrama de privacidad de informaci├ö├Â┬ú├ö├Â├®n","","NO","SI","2021-02-27","12:00","1","SI","NO",NULL,"NO",NULL);
INSERT INTO `actividad` VALUES("12","76","2021-03-12","10:23:05","273","Taller 1  Crucigrama","Desarrollar el crucigrama teniendo en cuenta la presentaci├ö├Â┬ú├ö├Â├®n","NO","SI","2021-03-13","10:30","1","SI","NO",NULL,"NO",NULL);



DROP TABLE IF EXISTS `actividades`;

CREATE TABLE `actividades` (
  `actividad` varchar(255) NOT NULL,
  `descripcion_actividad` text DEFAULT NULL,
  PRIMARY KEY (`actividad`),
  UNIQUE KEY `actividad` (`actividad`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

INSERT INTO `actividades` VALUES("manejar teclado y mouse","Ejercicios b├ísicos de control");
INSERT INTO `actividades` VALUES("Debates","Se trata de un argumento y la sociedad civil organizada, que requiere de un buen facilitador. Tras el debate, que emanan del sitio de origen, los estudiantes a distancia se hacen preguntas");
INSERT INTO `actividades` VALUES("Mapas conceptuales","");
INSERT INTO `actividades` VALUES("simulaci├│n/representaci├│n de roles","Presentes asuntos, problemas, situaciones, etc en los que los miembros deben papel que desempe├▒an. Una situaci├│n cr├¡tica se discute y analiza y las decisiones del grupo hizo acerca de c├│mo resolver situaciones. Buen desarrollo de habilidades del equipo");
INSERT INTO `actividades` VALUES("Resoluci├│n de problemas","");
INSERT INTO `actividades` VALUES("B├║squeda de informaci├│n","");
INSERT INTO `actividades` VALUES("elaborar un oficio de solicitud","");
INSERT INTO `actividades` VALUES("canci├│n","");
INSERT INTO `actividades` VALUES("Posters","");
INSERT INTO `actividades` VALUES("Creaci├│n de un perfil profesional","Crear perfil e invitar al docente en su red");
INSERT INTO `actividades` VALUES("Inscripci├│n a ofertas laborales","Como t├®cnico en sistemas");
INSERT INTO `actividades` VALUES("Clasificaci├│n de componentes del computador",NULL);
INSERT INTO `actividades` VALUES("Ejercicios pr├ícticos de creaci├│n de carpetas",NULL);
INSERT INTO `actividades` VALUES("motricidad con mouse y teclado",NULL);
INSERT INTO `actividades` VALUES("gu├¡as de estudio","");
INSERT INTO `actividades` VALUES("Dibujar en digital representaciones f├¡sicas",NULL);
INSERT INTO `actividades` VALUES("Representaci├│n grupal",NULL);
INSERT INTO `actividades` VALUES("Sopa de Letras",NULL);
INSERT INTO `actividades` VALUES("proyecto",NULL);
INSERT INTO `actividades` VALUES("Migraci├│n de un proyecto personal",NULL);
INSERT INTO `actividades` VALUES("Elaboraci├│n de ejercicios de administraci├│n","Documentos de texto plano");
INSERT INTO `actividades` VALUES("elaboraci├│n de gu├¡a de administraci├│n","Documentos de texto");
INSERT INTO `actividades` VALUES("Creaci├│n de documentos y carpetas",NULL);
INSERT INTO `actividades` VALUES("Creaci├│n de una presentaci├│n espec├¡fica",NULL);
INSERT INTO `actividades` VALUES("Grabaci├│n de cd",NULL);
INSERT INTO `actividades` VALUES("respaldar actividades del aula",NULL);
INSERT INTO `actividades` VALUES("Elaboraci├│n de un horario",NULL);
INSERT INTO `actividades` VALUES("Desarrollo de ejercicio de n├│mina","Con apoyo financiado y operaciones matem├íticas b├ísicas");
INSERT INTO `actividades` VALUES("Producir certificados","Mediante combinaci├│n de correspondencia");
INSERT INTO `actividades` VALUES("Dise├▒o de una calculadora b├ísica",NULL);
INSERT INTO `actividades` VALUES("Dise├▒o de base de datos","Para proyecto brindado por el docente");
INSERT INTO `actividades` VALUES("Desarrollar un Paisaje","Amanecer, Atardecer o Desierto");
INSERT INTO `actividades` VALUES("Desarrollo de gu├¡a",NULL);
INSERT INTO `actividades` VALUES("Consulta de herramientas alternativas",NULL);
INSERT INTO `actividades` VALUES("Desarrollar examen final",NULL);
INSERT INTO `actividades` VALUES("Desarrollar juego piedra, papel o tijera",NULL);
INSERT INTO `actividades` VALUES("Examen",NULL);
INSERT INTO `actividades` VALUES("Taller",NULL);
INSERT INTO `actividades` VALUES("Telelecture","Presentaci├│n por el profesor durante un per├¡odo clase con m├¡nima interacci├│n");
INSERT INTO `actividades` VALUES("Charla corta","Presentaciones de 10-15 minutos seguidas por actividades de aprendizaje");
INSERT INTO `actividades` VALUES("Entrevista a expertos","El maestro o estudiante entrevista a un invitado experto en tema elegido");
INSERT INTO `actividades` VALUES("Entrevistas entre estudiantes","Pares de estudiantes se entrevistan sobre tema preseleccionado");
INSERT INTO `actividades` VALUES("Sesiones Buzz","Grupos peque├▒os trabajando en tiempo limitado sin l├¡der");
INSERT INTO `actividades` VALUES("Intercambio Docente","Un estudiante realiza una clase corta basada en experiencia personal");
INSERT INTO `actividades` VALUES("Ejercicios individuales","Pr├íctica de habilidades con ejercicios estructurados");
INSERT INTO `actividades` VALUES("Juego de rol","Representaci├│n de situaciones y personajes");
INSERT INTO `actividades` VALUES("Juegos","Actividades competitivas con ganadores");
INSERT INTO `actividades` VALUES("video","Escenarios visuales cortos para an├ílisis");
INSERT INTO `actividades` VALUES("Excursiones","Visitas previas discutidas en clase");
INSERT INTO `actividades` VALUES("Teatralidad","Uso de recursos teatrales para reforzar aprendizaje");
INSERT INTO `actividades` VALUES("Pr├íctica","Demostraci├│n y ejercicios con retroalimentaci├│n");
INSERT INTO `actividades` VALUES("Puzzles","Rompecabezas educativos");
INSERT INTO `actividades` VALUES("Pantomima","Representaci├│n no verbal");
INSERT INTO `actividades` VALUES("Simposio","Compendio de expertos exponiendo un tema");
INSERT INTO `actividades` VALUES("Foto montaje",NULL);
INSERT INTO `actividades` VALUES("Elaboraci├│n de meme",NULL);
INSERT INTO `actividades` VALUES("retoque fotogr├ífico",NULL);
INSERT INTO `actividades` VALUES("Transcripci├│n y formato de p├írrafo",NULL);
INSERT INTO `actividades` VALUES("Elaboraci├│n de publicidad animada",NULL);
INSERT INTO `actividades` VALUES("Creaci├│n de formulario de notas",NULL);
INSERT INTO `actividades` VALUES("Creaci├│n de m├│dulo de sistema seguro",NULL);
INSERT INTO `actividades` VALUES("Elaboraci├│n de p├ígina web",NULL);
INSERT INTO `actividades` VALUES("maquetaci├│n de p├ígina web",NULL);
INSERT INTO `actividades` VALUES("Elaborar portaretrato infantil",NULL);
INSERT INTO `actividades` VALUES("fotograf├¡a tipo c├®dula","Con fondo blanco o azul");
INSERT INTO `actividades` VALUES("Elaborar libreta de notas","En Evernote y compartir con docente");
INSERT INTO `actividades` VALUES("Elaborar escena CINEMAGRAPH",NULL);
INSERT INTO `actividades` VALUES("animaci├│n publicitaria",NULL);
INSERT INTO `actividades` VALUES("Elaboraci├│n de logo empresarial",NULL);
INSERT INTO `actividades` VALUES("Elaboraci├│n de identidad corporativa",NULL);
INSERT INTO `actividades` VALUES("Elaboraci├│n de calendario",NULL);
INSERT INTO `actividades` VALUES("Creaci├│n de base de datos","En phpMyAdmin");
INSERT INTO `actividades` VALUES("elaboraci├│n de sem├íforo",NULL);
INSERT INTO `actividades` VALUES("Crear curso multimedia","De una asignatura y compartir con compa├▒eros");
INSERT INTO `actividades` VALUES("Desarrollo de ejercicios de aplicaci├│n",NULL);
INSERT INTO `actividades` VALUES("instalaci├│n de proyecto web",NULL);
INSERT INTO `actividades` VALUES("Crear presentaci├│n empresarial","De producto o servicio");
INSERT INTO `actividades` VALUES("Dise├▒ar sistema de control","Para ingreso de personas");
INSERT INTO `actividades` VALUES("Administraci├│n de personal","Usando Salesforce");
INSERT INTO `actividades` VALUES("An├ílisis de datos",NULL);
INSERT INTO `actividades` VALUES("Realizar b├║squedas asignadas",NULL);
INSERT INTO `actividades` VALUES("Elaborar documento formal",NULL);
INSERT INTO `actividades` VALUES("Montaje de punto de red",NULL);
INSERT INTO `actividades` VALUES("Validar sitio web y MAC",NULL);
INSERT INTO `actividades` VALUES("Crucigrama de conceptos",NULL);
INSERT INTO `actividades` VALUES("Crear cuestionario","De cultura general");
INSERT INTO `actividades` VALUES("limpiar y transformar datos",NULL);
INSERT INTO `actividades` VALUES("Desarrollar PQR con condicional",NULL);
INSERT INTO `actividades` VALUES("Configurar perfil y privacidad",NULL);
INSERT INTO `actividades` VALUES("Desarrollar algoritmo","Para cobro de llamadas telef├│nicas");
INSERT INTO `actividades` VALUES("implementar ocultamiento multimedia",NULL);
INSERT INTO `actividades` VALUES("Dise├▒ar poster tem├ítico",NULL);
INSERT INTO `actividades` VALUES("generar informes de consultas",NULL);
INSERT INTO `actividades` VALUES("Realizar an├ílisis de redes sociales",NULL);
INSERT INTO `actividades` VALUES("Desarrollar ejercicios de API",NULL);
INSERT INTO `actividades` VALUES("Entrenamiento de datos",NULL);
INSERT INTO `actividades` VALUES("infograf├¡a de sistemas",NULL);
INSERT INTO `actividades` VALUES("Ensamblaje de computador",NULL);
INSERT INTO `actividades` VALUES("Diagn├│stico de hardware","De disco duro y memoria RAM");
INSERT INTO `actividades` VALUES("Particionamiento de disco",NULL);
INSERT INTO `actividades` VALUES("Instalar WordPress",NULL);



DROP TABLE IF EXISTS `acudiente_estudiante`;

CREATE TABLE `acudiente_estudiante` (
  `id_acudiente_estudiante` int(11) NOT NULL AUTO_INCREMENT,
  `id_acudiente` varchar(255) DEFAULT NULL,
  `id_estudiante` varchar(255) DEFAULT NULL,
  `parentesco` varchar(255) NOT NULL,
  UNIQUE KEY `id_acudiente_estudiante` (`id_acudiente_estudiante`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci COMMENT='Asociaci├ö├Â┬ú├ö├Â├®n de acudiente con estudiante';




DROP TABLE IF EXISTS `ano_lectivo`;

CREATE TABLE `ano_lectivo` (
  `id_ano_lectivo` int(11) NOT NULL AUTO_INCREMENT,
  `nombre_ano_lectivo` year(4) DEFAULT NULL,
  `descripcion_ano` text DEFAULT NULL,
  `estado` enum('Activo','Inactivo') CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT 'Inactivo',
  PRIMARY KEY (`id_ano_lectivo`),
  UNIQUE KEY `ano_lectivo` (`nombre_ano_lectivo`)
) ENGINE=MyISAM AUTO_INCREMENT=17 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

INSERT INTO `ano_lectivo` VALUES("3","2018","2018b ","Inactivo");
INSERT INTO `ano_lectivo` VALUES("11","2019","","Inactivo");
INSERT INTO `ano_lectivo` VALUES("12","2020",NULL,"Inactivo");
INSERT INTO `ano_lectivo` VALUES("13","2021",NULL,"Inactivo");
INSERT INTO `ano_lectivo` VALUES("14","2023",NULL,"Inactivo");
INSERT INTO `ano_lectivo` VALUES("15","2024","..","Inactivo");
INSERT INTO `ano_lectivo` VALUES("16","2025","..","Activo");



DROP TABLE IF EXISTS `area`;

CREATE TABLE `area` (
  `id_area` int(11) NOT NULL AUTO_INCREMENT,
  `nombre_area` varchar(90) DEFAULT NULL,
  PRIMARY KEY (`id_area`)
) ENGINE=MyISAM AUTO_INCREMENT=11 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

INSERT INTO `area` VALUES("1","Ciencias naturales y educación ambiental");
INSERT INTO `area` VALUES("2","Ciencias sociales, historia, geografía, constitución política y democracia");
INSERT INTO `area` VALUES("3","Educación artística");
INSERT INTO `area` VALUES("4","Educación ética y en valores humanos");
INSERT INTO `area` VALUES("5","Educación física, recreación y deportes");
INSERT INTO `area` VALUES("6","Educación religiosa");
INSERT INTO `area` VALUES("7","Humanidades, lengua castellana e idiomas extranjeros");
INSERT INTO `area` VALUES("8","Matemáticas");
INSERT INTO `area` VALUES("9","Tecnología e informática");
INSERT INTO `area` VALUES("10","Transición");



DROP TABLE IF EXISTS `asignacion`;

CREATE TABLE `asignacion` (
  `id_asignacion` int(255) NOT NULL AUTO_INCREMENT,
  `institucion_educativa` int(12) NOT NULL DEFAULT 1,
  `id_curso` int(11) NOT NULL,
  `id_asignatura` int(11) NOT NULL,
  `id_docente` int(12) NOT NULL DEFAULT 1085290375,
  `ano_lectivo` int(8) NOT NULL,
  `descripcion` varchar(240) DEFAULT NULL,
  `id_categoria_curso` int(11) NOT NULL,
  `visible` varchar(2) DEFAULT NULL,
  `portada_asignacion` varchar(255) DEFAULT NULL,
  `icono_asignacion` varchar(255) NOT NULL DEFAULT 'agenda.png',
  `asistencia` int(1) NOT NULL DEFAULT 0,
  UNIQUE KEY `id_asignacion` (`id_asignacion`),
  KEY `asignacion_ibfk_5` (`id_docente`)
) ENGINE=MyISAM AUTO_INCREMENT=17 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

INSERT INTO `asignacion` VALUES("1","7","1","1","1085290375","16",NULL,"6","si",NULL,"agenda.png","0");
INSERT INTO `asignacion` VALUES("2","7","1","1","1085290375","16",NULL,"9","si",NULL,"agenda.png","0");
INSERT INTO `asignacion` VALUES("3","7","3","3","1085290375","16",NULL,"6","si",NULL,"agenda.png","0");
INSERT INTO `asignacion` VALUES("4","7","3","3","1085290375","16",NULL,"9","si",NULL,"agenda.png","0");
INSERT INTO `asignacion` VALUES("5","7","5","5","1085290375","16",NULL,"6","si",NULL,"agenda.png","0");
INSERT INTO `asignacion` VALUES("6","7","5","5","1085290375","16",NULL,"9","si",NULL,"agenda.png","0");
INSERT INTO `asignacion` VALUES("7","7","8","8","1085290375","16",NULL,"6","si",NULL,"agenda.png","0");
INSERT INTO `asignacion` VALUES("8","7","8","8","1085290375","16",NULL,"9","si",NULL,"agenda.png","0");
INSERT INTO `asignacion` VALUES("9","7","4","4","1085290375","16",NULL,"6","si",NULL,"agenda.png","0");
INSERT INTO `asignacion` VALUES("10","7","4","4","1085290375","16",NULL,"9","si",NULL,"agenda.png","0");
INSERT INTO `asignacion` VALUES("11","7","7","7","1085290375","16",NULL,"6","si",NULL,"agenda.png","0");
INSERT INTO `asignacion` VALUES("12","7","7","7","1085290375","16",NULL,"9","si",NULL,"agenda.png","0");
INSERT INTO `asignacion` VALUES("13","7","9","9","1085290375","16",NULL,"6","si",NULL,"agenda.png","0");
INSERT INTO `asignacion` VALUES("14","7","9","9","1085290375","16",NULL,"9","si",NULL,"agenda.png","0");
INSERT INTO `asignacion` VALUES("15","7","6","6","1085290375","16",NULL,"10","si",NULL,"agenda.png","0");
INSERT INTO `asignacion` VALUES("16","7","2","2","1085290375","16",NULL,"10","si",NULL,"agenda.png","0");



DROP TABLE IF EXISTS `asistencia`;

CREATE TABLE `asistencia` (
  `id_asistencia` int(11) NOT NULL AUTO_INCREMENT,
  `id_docente` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `id_materia` int(11) NOT NULL,
  `id_estudiante` varchar(255) DEFAULT NULL,
  `asistencia` varchar(7) NOT NULL DEFAULT 'no',
  `fecha` date NOT NULL,
  PRIMARY KEY (`id_asistencia`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;




DROP TABLE IF EXISTS `asistencias`;

CREATE TABLE `asistencias` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `estudiante` varchar(100) DEFAULT NULL,
  `materia` varchar(50) DEFAULT NULL,
  `asistencias` varchar(255) DEFAULT NULL,
  `fechas_clase` text DEFAULT NULL,
  `documento` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uidx_asistencias` (`documento`,`materia`,`fechas_clase`) USING HASH
) ENGINE=InnoDB AUTO_INCREMENT=599 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

INSERT INTO `asistencias` VALUES("1","Diaz Rosso Freddy De Jesus","C. NATURALES","SI","21/10/2024","1058198933");
INSERT INTO `asistencias` VALUES("2","Diaz Rosso Freddy De Jesus","ARTISTICA","SI","21/10/2024","1058198933");
INSERT INTO `asistencias` VALUES("3","Diaz Rosso Freddy De Jesus","MATEMATICAS","SI","22/10/2024","1058198933");
INSERT INTO `asistencias` VALUES("4","Diaz Rosso Freddy De Jesus","ED. FISICA","SI","22/10/2024","1058198933");
INSERT INTO `asistencias` VALUES("5","Diaz Rosso Freddy De Jesus","TECNOLOGIA","SI","23/10/2024","1058198933");
INSERT INTO `asistencias` VALUES("6","Diaz Rosso Freddy De Jesus","URBANIDAD","SI","23/10/2024","1058198933");
INSERT INTO `asistencias` VALUES("7","Diaz Rosso Freddy De Jesus","C. NATURALES","NR","24/10/2024","1058198933");
INSERT INTO `asistencias` VALUES("8","Diaz Rosso Freddy De Jesus","C. SOCIALES","NR","24/10/2024","1058198933");
INSERT INTO `asistencias` VALUES("9","Diaz Rosso Freddy De Jesus","ED. FISICA","NR","24/10/2024","1058198933");
INSERT INTO `asistencias` VALUES("10","Diaz Rosso Freddy De Jesus","C. NATURALES","SI","28/10/2024","1058198933");
INSERT INTO `asistencias` VALUES("11","Diaz Rosso Freddy De Jesus","ARTISTICA","SI","28/10/2024","1058198933");
INSERT INTO `asistencias` VALUES("12","Diaz Rosso Freddy De Jesus","MATEMATICAS","SI","29/10/2024","1058198933");
INSERT INTO `asistencias` VALUES("13","Diaz Rosso Freddy De Jesus","ED. FISICA","SI","29/10/2024","1058198933");
INSERT INTO `asistencias` VALUES("14","Diaz Rosso Freddy De Jesus","TECNOLOGIA","SI","30/10/2024","1058198933");
INSERT INTO `asistencias` VALUES("15","Diaz Rosso Freddy De Jesus","URBANIDAD","SI","30/10/2024","1058198933");
INSERT INTO `asistencias` VALUES("16","Diaz Rosso Freddy De Jesus","C. NATURALES","SI","31/10/2024","1058198933");
INSERT INTO `asistencias` VALUES("17","Diaz Rosso Freddy De Jesus","C. SOCIALES","SI","31/10/2024","1058198933");
INSERT INTO `asistencias` VALUES("18","Diaz Rosso Freddy De Jesus","ED. FISICA","SI","31/10/2024","1058198933");
INSERT INTO `asistencias` VALUES("19","Diaz Rosso Freddy De Jesus","C. NATURALES","=COUNTIFS(F13:O13,$V$9)","00/10/2024","1058198933");
INSERT INTO `asistencias` VALUES("20","Diaz Rosso Freddy De Jesus","ARTISTICA","=COUNTIFS(F13:O13,$V$9)","00/10/2024","1058198933");
INSERT INTO `asistencias` VALUES("21","Diaz Rosso Freddy De Jesus","C. NATURALES","SI","07/10/2024","1058198933");
INSERT INTO `asistencias` VALUES("22","Diaz Rosso Freddy De Jesus","ARTISTICA","SI","07/10/2024","1058198933");
INSERT INTO `asistencias` VALUES("23","Giraldo Mazo Santiago","C. NATURALES","SI","21/10/2024","1036258563");
INSERT INTO `asistencias` VALUES("24","Giraldo Mazo Santiago","ARTISTICA","SI","21/10/2024","1036258563");
INSERT INTO `asistencias` VALUES("25","Giraldo Mazo Santiago","MATEMATICAS","SI","22/10/2024","1036258563");
INSERT INTO `asistencias` VALUES("26","Giraldo Mazo Santiago","ED. FISICA","SI","22/10/2024","1036258563");
INSERT INTO `asistencias` VALUES("27","Giraldo Mazo Santiago","TECNOLOGIA","SI","23/10/2024","1036258563");
INSERT INTO `asistencias` VALUES("28","Giraldo Mazo Santiago","URBANIDAD","SI","23/10/2024","1036258563");
INSERT INTO `asistencias` VALUES("29","Giraldo Mazo Santiago","C. NATURALES","NR","24/10/2024","1036258563");
INSERT INTO `asistencias` VALUES("30","Giraldo Mazo Santiago","C. SOCIALES","NR","24/10/2024","1036258563");
INSERT INTO `asistencias` VALUES("31","Giraldo Mazo Santiago","ED. FISICA","NR","24/10/2024","1036258563");
INSERT INTO `asistencias` VALUES("32","Giraldo Mazo Santiago","C. NATURALES","SI","28/10/2024","1036258563");
INSERT INTO `asistencias` VALUES("33","Giraldo Mazo Santiago","ARTISTICA","SI","28/10/2024","1036258563");
INSERT INTO `asistencias` VALUES("34","Giraldo Mazo Santiago","MATEMATICAS","SI","29/10/2024","1036258563");
INSERT INTO `asistencias` VALUES("35","Giraldo Mazo Santiago","ED. FISICA","SI","29/10/2024","1036258563");
INSERT INTO `asistencias` VALUES("36","Giraldo Mazo Santiago","TECNOLOGIA","SI","30/10/2024","1036258563");
INSERT INTO `asistencias` VALUES("37","Giraldo Mazo Santiago","URBANIDAD","SI","30/10/2024","1036258563");
INSERT INTO `asistencias` VALUES("38","Giraldo Mazo Santiago","C. NATURALES","SI","31/10/2024","1036258563");
INSERT INTO `asistencias` VALUES("39","Giraldo Mazo Santiago","C. SOCIALES","SI","31/10/2024","1036258563");
INSERT INTO `asistencias` VALUES("40","Giraldo Mazo Santiago","ED. FISICA","SI","31/10/2024","1036258563");
INSERT INTO `asistencias` VALUES("41","Giraldo Mazo Santiago","C. NATURALES","=COUNTIFS(F16:O16,$V$9)","00/10/2024","1036258563");
INSERT INTO `asistencias` VALUES("42","Giraldo Mazo Santiago","ARTISTICA","=COUNTIFS(F16:O16,$V$9)","00/10/2024","1036258563");
INSERT INTO `asistencias` VALUES("43","Giraldo Mazo Santiago","C. NATURALES","SI","07/10/2024","1036258563");
INSERT INTO `asistencias` VALUES("44","Giraldo Mazo Santiago","ARTISTICA","SI","07/10/2024","1036258563");
INSERT INTO `asistencias` VALUES("45","Herazo Valeria Areiza","C. NATURALES","SI","21/10/2024","1115576678");
INSERT INTO `asistencias` VALUES("46","Herazo Valeria Areiza","ARTISTICA","SI","21/10/2024","1115576678");
INSERT INTO `asistencias` VALUES("47","Herazo Valeria Areiza","MATEMATICAS","SI","22/10/2024","1115576678");
INSERT INTO `asistencias` VALUES("48","Herazo Valeria Areiza","ED. FISICA","SI","22/10/2024","1115576678");
INSERT INTO `asistencias` VALUES("49","Herazo Valeria Areiza","TECNOLOGIA","SI","23/10/2024","1115576678");
INSERT INTO `asistencias` VALUES("50","Herazo Valeria Areiza","URBANIDAD","SI","23/10/2024","1115576678");
INSERT INTO `asistencias` VALUES("51","Herazo Valeria Areiza","C. NATURALES","NR","24/10/2024","1115576678");
INSERT INTO `asistencias` VALUES("52","Herazo Valeria Areiza","C. SOCIALES","NR","24/10/2024","1115576678");
INSERT INTO `asistencias` VALUES("53","Herazo Valeria Areiza","ED. FISICA","NR","24/10/2024","1115576678");
INSERT INTO `asistencias` VALUES("54","Herazo Valeria Areiza","C. NATURALES","SI","28/10/2024","1115576678");
INSERT INTO `asistencias` VALUES("55","Herazo Valeria Areiza","ARTISTICA","SI","28/10/2024","1115576678");
INSERT INTO `asistencias` VALUES("56","Herazo Valeria Areiza","MATEMATICAS","SI","29/10/2024","1115576678");
INSERT INTO `asistencias` VALUES("57","Herazo Valeria Areiza","ED. FISICA","SI","29/10/2024","1115576678");
INSERT INTO `asistencias` VALUES("58","Herazo Valeria Areiza","TECNOLOGIA","SI","30/10/2024","1115576678");
INSERT INTO `asistencias` VALUES("59","Herazo Valeria Areiza","URBANIDAD","SI","30/10/2024","1115576678");
INSERT INTO `asistencias` VALUES("60","Herazo Valeria Areiza","C. NATURALES","SI","31/10/2024","1115576678");
INSERT INTO `asistencias` VALUES("61","Herazo Valeria Areiza","C. SOCIALES","SI","31/10/2024","1115576678");
INSERT INTO `asistencias` VALUES("62","Herazo Valeria Areiza","ED. FISICA","SI","31/10/2024","1115576678");
INSERT INTO `asistencias` VALUES("63","Herazo Valeria Areiza","C. NATURALES","=COUNTIFS(F19:O19,$V$9)","00/10/2024","1115576678");
INSERT INTO `asistencias` VALUES("64","Herazo Valeria Areiza","ARTISTICA","=COUNTIFS(F19:O19,$V$9)","00/10/2024","1115576678");
INSERT INTO `asistencias` VALUES("65","Herazo Valeria Areiza","C. NATURALES","SI","07/10/2024","1115576678");
INSERT INTO `asistencias` VALUES("66","Herazo Valeria Areiza","ARTISTICA","SI","07/10/2024","1115576678");
INSERT INTO `asistencias` VALUES("67","Orozco Chavarria Cristobal","C. NATURALES","SI","21/10/2024","1036945161");
INSERT INTO `asistencias` VALUES("68","Orozco Chavarria Cristobal","ARTISTICA","SI","21/10/2024","1036945161");
INSERT INTO `asistencias` VALUES("69","Orozco Chavarria Cristobal","MATEMATICAS","NO","22/10/2024","1036945161");
INSERT INTO `asistencias` VALUES("70","Orozco Chavarria Cristobal","ED. FISICA","NO","22/10/2024","1036945161");
INSERT INTO `asistencias` VALUES("71","Orozco Chavarria Cristobal","TECNOLOGIA","NO","23/10/2024","1036945161");
INSERT INTO `asistencias` VALUES("72","Orozco Chavarria Cristobal","URBANIDAD","NO","23/10/2024","1036945161");
INSERT INTO `asistencias` VALUES("73","Orozco Chavarria Cristobal","C. NATURALES","SI","24/10/2024","1036945161");
INSERT INTO `asistencias` VALUES("74","Orozco Chavarria Cristobal","C. SOCIALES","SI","24/10/2024","1036945161");
INSERT INTO `asistencias` VALUES("75","Orozco Chavarria Cristobal","ED. FISICA","SI","24/10/2024","1036945161");
INSERT INTO `asistencias` VALUES("76","Orozco Chavarria Cristobal","C. NATURALES","SI","28/10/2024","1036945161");
INSERT INTO `asistencias` VALUES("77","Orozco Chavarria Cristobal","ARTISTICA","SI","28/10/2024","1036945161");
INSERT INTO `asistencias` VALUES("78","Orozco Chavarria Cristobal","MATEMATICAS","SI","29/10/2024","1036945161");
INSERT INTO `asistencias` VALUES("79","Orozco Chavarria Cristobal","ED. FISICA","SI","29/10/2024","1036945161");
INSERT INTO `asistencias` VALUES("80","Orozco Chavarria Cristobal","TECNOLOGIA","SI","30/10/2024","1036945161");
INSERT INTO `asistencias` VALUES("81","Orozco Chavarria Cristobal","URBANIDAD","SI","30/10/2024","1036945161");
INSERT INTO `asistencias` VALUES("82","Orozco Chavarria Cristobal","C. NATURALES","SI","31/10/2024","1036945161");
INSERT INTO `asistencias` VALUES("83","Orozco Chavarria Cristobal","C. SOCIALES","SI","31/10/2024","1036945161");
INSERT INTO `asistencias` VALUES("84","Orozco Chavarria Cristobal","ED. FISICA","SI","31/10/2024","1036945161");
INSERT INTO `asistencias` VALUES("85","Orozco Chavarria Cristobal","C. NATURALES","=COUNTIFS(F20:O20,$V$9)","00/10/2024","1036945161");
INSERT INTO `asistencias` VALUES("86","Orozco Chavarria Cristobal","ARTISTICA","=COUNTIFS(F20:O20,$V$9)","00/10/2024","1036945161");
INSERT INTO `asistencias` VALUES("87","Orozco Chavarria Cristobal","C. NATURALES","SI","07/10/2024","1036945161");
INSERT INTO `asistencias` VALUES("88","Orozco Chavarria Cristobal","ARTISTICA","SI","07/10/2024","1036945161");
INSERT INTO `asistencias` VALUES("89","Velazques Muñoz Anyeli Tatiana","C. NATURALES","SI","21/10/2024","1037975509");
INSERT INTO `asistencias` VALUES("90","Velazques Muñoz Anyeli Tatiana","ARTISTICA","SI","21/10/2024","1037975509");
INSERT INTO `asistencias` VALUES("91","Velazques Muñoz Anyeli Tatiana","MATEMATICAS","SI","22/10/2024","1037975509");
INSERT INTO `asistencias` VALUES("92","Velazques Muñoz Anyeli Tatiana","ED. FISICA","SI","22/10/2024","1037975509");
INSERT INTO `asistencias` VALUES("93","Velazques Muñoz Anyeli Tatiana","TECNOLOGIA","SI","23/10/2024","1037975509");
INSERT INTO `asistencias` VALUES("94","Velazques Muñoz Anyeli Tatiana","URBANIDAD","SI","23/10/2024","1037975509");
INSERT INTO `asistencias` VALUES("95","Velazques Muñoz Anyeli Tatiana","C. NATURALES","SI","24/10/2024","1037975509");
INSERT INTO `asistencias` VALUES("96","Velazques Muñoz Anyeli Tatiana","C. SOCIALES","SI","24/10/2024","1037975509");
INSERT INTO `asistencias` VALUES("97","Velazques Muñoz Anyeli Tatiana","ED. FISICA","SI","24/10/2024","1037975509");
INSERT INTO `asistencias` VALUES("98","Velazques Muñoz Anyeli Tatiana","C. NATURALES","SI","28/10/2024","1037975509");
INSERT INTO `asistencias` VALUES("99","Velazques Muñoz Anyeli Tatiana","ARTISTICA","SI","28/10/2024","1037975509");
INSERT INTO `asistencias` VALUES("100","Velazques Muñoz Anyeli Tatiana","MATEMATICAS","SI","29/10/2024","1037975509");
INSERT INTO `asistencias` VALUES("101","Velazques Muñoz Anyeli Tatiana","ED. FISICA","SI","29/10/2024","1037975509");
INSERT INTO `asistencias` VALUES("102","Velazques Muñoz Anyeli Tatiana","TECNOLOGIA","SI","30/10/2024","1037975509");
INSERT INTO `asistencias` VALUES("103","Velazques Muñoz Anyeli Tatiana","URBANIDAD","SI","30/10/2024","1037975509");
INSERT INTO `asistencias` VALUES("104","Velazques Muñoz Anyeli Tatiana","C. NATURALES","SI","31/10/2024","1037975509");
INSERT INTO `asistencias` VALUES("105","Velazques Muñoz Anyeli Tatiana","C. SOCIALES","SI","31/10/2024","1037975509");
INSERT INTO `asistencias` VALUES("106","Velazques Muñoz Anyeli Tatiana","ED. FISICA","SI","31/10/2024","1037975509");
INSERT INTO `asistencias` VALUES("107","Velazques Muñoz Anyeli Tatiana","C. NATURALES","=COUNTIFS(F21:O21,$V$9)","00/10/2024","1037975509");
INSERT INTO `asistencias` VALUES("108","Velazques Muñoz Anyeli Tatiana","ARTISTICA","=COUNTIFS(F21:O21,$V$9)","00/10/2024","1037975509");
INSERT INTO `asistencias` VALUES("109","Velazques Muñoz Anyeli Tatiana","C. NATURALES","SI","07/10/2024","1037975509");
INSERT INTO `asistencias` VALUES("110","Velazques Muñoz Anyeli Tatiana","ARTISTICA","SI","07/10/2024","1037975509");
INSERT INTO `asistencias` VALUES("111","Duque Aristizabal Angel  Gabriel","FISICA (MATEMÁTICA)","SI","21/10/2024","1127955146");
INSERT INTO `asistencias` VALUES("112","Duque Aristizabal Angel  Gabriel","ARTISTICA","SI","21/10/2024","1127955146");
INSERT INTO `asistencias` VALUES("113","Duque Aristizabal Angel  Gabriel","MATEMATICAS","SI","22/10/2024","1127955146");
INSERT INTO `asistencias` VALUES("114","Duque Aristizabal Angel  Gabriel","ED. FISICA","SI","22/10/2024","1127955146");
INSERT INTO `asistencias` VALUES("115","Duque Aristizabal Angel  Gabriel","TECNOLOGIA","SI","23/10/2024","1127955146");
INSERT INTO `asistencias` VALUES("116","Duque Aristizabal Angel  Gabriel","URBANIDAD","SI","23/10/2024","1127955146");
INSERT INTO `asistencias` VALUES("117","Duque Aristizabal Angel  Gabriel","ED. FISICA","SI","24/10/2024","1127955146");
INSERT INTO `asistencias` VALUES("118","Duque Aristizabal Angel  Gabriel","FISICA (MATEMÁTICA)","SI","28/10/2024","1127955146");
INSERT INTO `asistencias` VALUES("119","Duque Aristizabal Angel  Gabriel","ARTISTICA","SI","28/10/2024","1127955146");
INSERT INTO `asistencias` VALUES("120","Duque Aristizabal Angel  Gabriel","MATEMATICAS","SI","29/10/2024","1127955146");
INSERT INTO `asistencias` VALUES("121","Duque Aristizabal Angel  Gabriel","ED. FISICA","SI","29/10/2024","1127955146");
INSERT INTO `asistencias` VALUES("122","Duque Aristizabal Angel  Gabriel","TECNOLOGIA","SI","30/10/2024","1127955146");
INSERT INTO `asistencias` VALUES("123","Duque Aristizabal Angel  Gabriel","URBANIDAD","SI","30/10/2024","1127955146");
INSERT INTO `asistencias` VALUES("124","Duque Aristizabal Angel  Gabriel","ED. FISICA","SI","31/10/2024","1127955146");
INSERT INTO `asistencias` VALUES("125","Duque Aristizabal Angel  Gabriel","FISICA (MATEMÁTICA)","=COUNTIFS(F26:O26,$V$9)","00/10/2024","1127955146");
INSERT INTO `asistencias` VALUES("126","Duque Aristizabal Angel  Gabriel","ARTISTICA","=COUNTIFS(F26:O26,$V$9)","00/10/2024","1127955146");
INSERT INTO `asistencias` VALUES("127","Duque Aristizabal Angel  Gabriel","FISICA (MATEMÁTICA)","SI","07/10/2024","1127955146");
INSERT INTO `asistencias` VALUES("128","Duque Aristizabal Angel  Gabriel","ARTISTICA","SI","07/10/2024","1127955146");
INSERT INTO `asistencias` VALUES("129","Giraldo Giraldo Juan Camilo","FISICA (MATEMÁTICA)","SI","21/10/2024","1050038422");
INSERT INTO `asistencias` VALUES("130","Giraldo Giraldo Juan Camilo","ARTISTICA","SI","21/10/2024","1050038422");
INSERT INTO `asistencias` VALUES("131","Giraldo Giraldo Juan Camilo","MATEMATICAS","SI","22/10/2024","1050038422");
INSERT INTO `asistencias` VALUES("132","Giraldo Giraldo Juan Camilo","ED. FISICA","SI","22/10/2024","1050038422");
INSERT INTO `asistencias` VALUES("133","Giraldo Giraldo Juan Camilo","TECNOLOGIA","SI","23/10/2024","1050038422");
INSERT INTO `asistencias` VALUES("134","Giraldo Giraldo Juan Camilo","URBANIDAD","SI","23/10/2024","1050038422");
INSERT INTO `asistencias` VALUES("135","Giraldo Giraldo Juan Camilo","ED. FISICA","SI","24/10/2024","1050038422");
INSERT INTO `asistencias` VALUES("136","Giraldo Giraldo Juan Camilo","FISICA (MATEMÁTICA)","SI","28/10/2024","1050038422");
INSERT INTO `asistencias` VALUES("137","Giraldo Giraldo Juan Camilo","ARTISTICA","SI","28/10/2024","1050038422");
INSERT INTO `asistencias` VALUES("138","Giraldo Giraldo Juan Camilo","MATEMATICAS","SI","29/10/2024","1050038422");
INSERT INTO `asistencias` VALUES("139","Giraldo Giraldo Juan Camilo","ED. FISICA","SI","29/10/2024","1050038422");
INSERT INTO `asistencias` VALUES("140","Giraldo Giraldo Juan Camilo","TECNOLOGIA","SI","30/10/2024","1050038422");
INSERT INTO `asistencias` VALUES("141","Giraldo Giraldo Juan Camilo","URBANIDAD","SI","30/10/2024","1050038422");
INSERT INTO `asistencias` VALUES("142","Giraldo Giraldo Juan Camilo","ED. FISICA","SI","31/10/2024","1050038422");
INSERT INTO `asistencias` VALUES("143","Giraldo Giraldo Juan Camilo","FISICA (MATEMÁTICA)","=COUNTIFS(F24:O24,$V$9)","00/10/2024","1050038422");
INSERT INTO `asistencias` VALUES("144","Giraldo Giraldo Juan Camilo","ARTISTICA","=COUNTIFS(F24:O24,$V$9)","00/10/2024","1050038422");
INSERT INTO `asistencias` VALUES("145","Giraldo Giraldo Juan Camilo","FISICA (MATEMÁTICA)","SI","07/10/2024","1050038422");
INSERT INTO `asistencias` VALUES("146","Giraldo Giraldo Juan Camilo","ARTISTICA","SI","07/10/2024","1050038422");
INSERT INTO `asistencias` VALUES("147","Lopez Aguirre Alexis","FISICA (MATEMÁTICA)","SI","21/10/2024","1037974417");
INSERT INTO `asistencias` VALUES("148","Lopez Aguirre Alexis","ARTISTICA","SI","21/10/2024","1037974417");
INSERT INTO `asistencias` VALUES("149","Lopez Aguirre Alexis","MATEMATICAS","SI","22/10/2024","1037974417");
INSERT INTO `asistencias` VALUES("150","Lopez Aguirre Alexis","ED. FISICA","SI","22/10/2024","1037974417");
INSERT INTO `asistencias` VALUES("151","Lopez Aguirre Alexis","TECNOLOGIA","SI","23/10/2024","1037974417");
INSERT INTO `asistencias` VALUES("152","Lopez Aguirre Alexis","URBANIDAD","SI","23/10/2024","1037974417");
INSERT INTO `asistencias` VALUES("153","Lopez Aguirre Alexis","ED. FISICA","SI","24/10/2024","1037974417");
INSERT INTO `asistencias` VALUES("154","Lopez Aguirre Alexis","FISICA (MATEMÁTICA)","SI","28/10/2024","1037974417");
INSERT INTO `asistencias` VALUES("155","Lopez Aguirre Alexis","ARTISTICA","SI","28/10/2024","1037974417");
INSERT INTO `asistencias` VALUES("156","Lopez Aguirre Alexis","MATEMATICAS","SI","29/10/2024","1037974417");
INSERT INTO `asistencias` VALUES("157","Lopez Aguirre Alexis","ED. FISICA","SI","29/10/2024","1037974417");
INSERT INTO `asistencias` VALUES("158","Lopez Aguirre Alexis","TECNOLOGIA","SI","30/10/2024","1037974417");
INSERT INTO `asistencias` VALUES("159","Lopez Aguirre Alexis","URBANIDAD","SI","30/10/2024","1037974417");
INSERT INTO `asistencias` VALUES("160","Lopez Aguirre Alexis","ED. FISICA","SI","31/10/2024","1037974417");
INSERT INTO `asistencias` VALUES("161","Lopez Aguirre Alexis","FISICA (MATEMÁTICA)","=COUNTIFS(F25:O25,$V$9)","00/10/2024","1037974417");
INSERT INTO `asistencias` VALUES("162","Lopez Aguirre Alexis","ARTISTICA","=COUNTIFS(F25:O25,$V$9)","00/10/2024","1037974417");
INSERT INTO `asistencias` VALUES("163","Lopez Aguirre Alexis","FISICA (MATEMÁTICA)","SI","07/10/2024","1037974417");
INSERT INTO `asistencias` VALUES("164","Lopez Aguirre Alexis","ARTISTICA","SI","07/10/2024","1037974417");
INSERT INTO `asistencias` VALUES("165","Duque Aristizabal Jennyfer Tatiana","FISICA (MATEMÁTICA)","SI","21/10/2024","1127955147");
INSERT INTO `asistencias` VALUES("166","Duque Aristizabal Jennyfer Tatiana","ARTISTICA","SI","21/10/2024","1127955147");
INSERT INTO `asistencias` VALUES("167","Duque Aristizabal Jennyfer Tatiana","MATEMATICAS","SI","22/10/2024","1127955147");
INSERT INTO `asistencias` VALUES("168","Duque Aristizabal Jennyfer Tatiana","ED. FISICA","SI","22/10/2024","1127955147");
INSERT INTO `asistencias` VALUES("169","Duque Aristizabal Jennyfer Tatiana","TECNOLOGIA","SI","23/10/2024","1127955147");
INSERT INTO `asistencias` VALUES("170","Duque Aristizabal Jennyfer Tatiana","URBANIDAD","SI","23/10/2024","1127955147");
INSERT INTO `asistencias` VALUES("171","Duque Aristizabal Jennyfer Tatiana","ED. FISICA","SI","24/10/2024","1127955147");
INSERT INTO `asistencias` VALUES("172","Duque Aristizabal Jennyfer Tatiana","FISICA (MATEMÁTICA)","SI","28/10/2024","1127955147");
INSERT INTO `asistencias` VALUES("173","Duque Aristizabal Jennyfer Tatiana","ARTISTICA","SI","28/10/2024","1127955147");
INSERT INTO `asistencias` VALUES("174","Duque Aristizabal Jennyfer Tatiana","MATEMATICAS","SI","29/10/2024","1127955147");
INSERT INTO `asistencias` VALUES("175","Duque Aristizabal Jennyfer Tatiana","ED. FISICA","SI","29/10/2024","1127955147");
INSERT INTO `asistencias` VALUES("176","Duque Aristizabal Jennyfer Tatiana","TECNOLOGIA","SI","30/10/2024","1127955147");
INSERT INTO `asistencias` VALUES("177","Duque Aristizabal Jennyfer Tatiana","URBANIDAD","SI","30/10/2024","1127955147");
INSERT INTO `asistencias` VALUES("178","Duque Aristizabal Jennyfer Tatiana","ED. FISICA","SI","31/10/2024","1127955147");
INSERT INTO `asistencias` VALUES("179","Duque Aristizabal Jennyfer Tatiana","FISICA (MATEMÁTICA)","SI","00/10/2024","1127955147");
INSERT INTO `asistencias` VALUES("180","Duque Aristizabal Jennyfer Tatiana","ARTISTICA","SI","00/10/2024","1127955147");
INSERT INTO `asistencias` VALUES("181","Duque Aristizabal Jennyfer Tatiana","FISICA (MATEMÁTICA)","SI","07/10/2024","1127955147");
INSERT INTO `asistencias` VALUES("182","Duque Aristizabal Jennyfer Tatiana","ARTISTICA","SI","07/10/2024","1127955147");
INSERT INTO `asistencias` VALUES("183","Gallego Giraldo Marlon","FISICA (MATEMÁTICA)","SI","21/10/2024","1037974770");
INSERT INTO `asistencias` VALUES("184","Gallego Giraldo Marlon","ARTISTICA","SI","21/10/2024","1037974770");
INSERT INTO `asistencias` VALUES("185","Gallego Giraldo Marlon","MATEMATICAS","SI","22/10/2024","1037974770");
INSERT INTO `asistencias` VALUES("186","Gallego Giraldo Marlon","ED. FISICA","SI","22/10/2024","1037974770");
INSERT INTO `asistencias` VALUES("187","Gallego Giraldo Marlon","TECNOLOGIA","SI","23/10/2024","1037974770");
INSERT INTO `asistencias` VALUES("188","Gallego Giraldo Marlon","URBANIDAD","SI","23/10/2024","1037974770");
INSERT INTO `asistencias` VALUES("189","Gallego Giraldo Marlon","ED. FISICA","SI","24/10/2024","1037974770");
INSERT INTO `asistencias` VALUES("190","Gallego Giraldo Marlon","FISICA (MATEMÁTICA)","SI","28/10/2024","1037974770");
INSERT INTO `asistencias` VALUES("191","Gallego Giraldo Marlon","ARTISTICA","SI","28/10/2024","1037974770");
INSERT INTO `asistencias` VALUES("192","Gallego Giraldo Marlon","MATEMATICAS","SI","29/10/2024","1037974770");
INSERT INTO `asistencias` VALUES("193","Gallego Giraldo Marlon","ED. FISICA","SI","29/10/2024","1037974770");
INSERT INTO `asistencias` VALUES("194","Gallego Giraldo Marlon","TECNOLOGIA","SI","30/10/2024","1037974770");
INSERT INTO `asistencias` VALUES("195","Gallego Giraldo Marlon","URBANIDAD","SI","30/10/2024","1037974770");
INSERT INTO `asistencias` VALUES("196","Gallego Giraldo Marlon","ED. FISICA","SI","31/10/2024","1037974770");
INSERT INTO `asistencias` VALUES("197","Gallego Giraldo Marlon","FISICA (MATEMÁTICA)","=COUNTIFS(F27:O27,$V$9)","00/10/2024","1037974770");
INSERT INTO `asistencias` VALUES("198","Gallego Giraldo Marlon","ARTISTICA","=COUNTIFS(F27:O27,$V$9)","00/10/2024","1037974770");
INSERT INTO `asistencias` VALUES("199","Gallego Giraldo Marlon","FISICA (MATEMÁTICA)","SI","07/10/2024","1037974770");
INSERT INTO `asistencias` VALUES("200","Gallego Giraldo Marlon","ARTISTICA","SI","07/10/2024","1037974770");
INSERT INTO `asistencias` VALUES("201","Garcia Navarro Sebastian Camilo","FISICA (MATEMÁTICA)","SI","21/10/2024","1131284071");
INSERT INTO `asistencias` VALUES("202","Garcia Navarro Sebastian Camilo","ARTISTICA","SI","21/10/2024","1131284071");
INSERT INTO `asistencias` VALUES("203","Garcia Navarro Sebastian Camilo","MATEMATICAS","SI","22/10/2024","1131284071");
INSERT INTO `asistencias` VALUES("204","Garcia Navarro Sebastian Camilo","ED. FISICA","SI","22/10/2024","1131284071");
INSERT INTO `asistencias` VALUES("205","Garcia Navarro Sebastian Camilo","TECNOLOGIA","SI","23/10/2024","1131284071");
INSERT INTO `asistencias` VALUES("206","Garcia Navarro Sebastian Camilo","URBANIDAD","SI","23/10/2024","1131284071");
INSERT INTO `asistencias` VALUES("207","Garcia Navarro Sebastian Camilo","ED. FISICA","SI","24/10/2024","1131284071");
INSERT INTO `asistencias` VALUES("208","Garcia Navarro Sebastian Camilo","FISICA (MATEMÁTICA)","SI","28/10/2024","1131284071");
INSERT INTO `asistencias` VALUES("209","Garcia Navarro Sebastian Camilo","ARTISTICA","SI","28/10/2024","1131284071");
INSERT INTO `asistencias` VALUES("210","Garcia Navarro Sebastian Camilo","MATEMATICAS","SI","29/10/2024","1131284071");
INSERT INTO `asistencias` VALUES("211","Garcia Navarro Sebastian Camilo","ED. FISICA","SI","29/10/2024","1131284071");
INSERT INTO `asistencias` VALUES("212","Garcia Navarro Sebastian Camilo","TECNOLOGIA","SI","30/10/2024","1131284071");
INSERT INTO `asistencias` VALUES("213","Garcia Navarro Sebastian Camilo","URBANIDAD","SI","30/10/2024","1131284071");
INSERT INTO `asistencias` VALUES("214","Garcia Navarro Sebastian Camilo","ED. FISICA","SI","31/10/2024","1131284071");
INSERT INTO `asistencias` VALUES("215","Garcia Navarro Sebastian Camilo","FISICA (MATEMÁTICA)","=COUNTIFS(F28:O28,$V$9)","00/10/2024","1131284071");
INSERT INTO `asistencias` VALUES("216","Garcia Navarro Sebastian Camilo","ARTISTICA","=COUNTIFS(F28:O28,$V$9)","00/10/2024","1131284071");
INSERT INTO `asistencias` VALUES("217","Garcia Navarro Sebastian Camilo","FISICA (MATEMÁTICA)","SI","07/10/2024","1131284071");
INSERT INTO `asistencias` VALUES("218","Garcia Navarro Sebastian Camilo","ARTISTICA","SI","07/10/2024","1131284071");
INSERT INTO `asistencias` VALUES("219","Giraldo Mazo Sara Paulina ","FISICA (MATEMÁTICA)","nr","21/10/2024","135937935");
INSERT INTO `asistencias` VALUES("220","Giraldo Mazo Sara Paulina ","ARTISTICA","nr","21/10/2024","135937935");
INSERT INTO `asistencias` VALUES("221","Giraldo Mazo Sara Paulina ","MATEMATICAS","SI","22/10/2024","135937935");
INSERT INTO `asistencias` VALUES("222","Giraldo Mazo Sara Paulina ","ED. FISICA","SI","22/10/2024","135937935");
INSERT INTO `asistencias` VALUES("223","Giraldo Mazo Sara Paulina ","TECNOLOGIA","SI","23/10/2024","135937935");
INSERT INTO `asistencias` VALUES("224","Giraldo Mazo Sara Paulina ","URBANIDAD","SI","23/10/2024","135937935");
INSERT INTO `asistencias` VALUES("225","Giraldo Mazo Sara Paulina ","ED. FISICA","NO","24/10/2024","135937935");
INSERT INTO `asistencias` VALUES("226","Giraldo Mazo Sara Paulina ","FISICA (MATEMÁTICA)","SI","28/10/2024","135937935");
INSERT INTO `asistencias` VALUES("227","Giraldo Mazo Sara Paulina ","ARTISTICA","SI","28/10/2024","135937935");
INSERT INTO `asistencias` VALUES("228","Giraldo Mazo Sara Paulina ","MATEMATICAS","SI","29/10/2024","135937935");
INSERT INTO `asistencias` VALUES("229","Giraldo Mazo Sara Paulina ","ED. FISICA","SI","29/10/2024","135937935");
INSERT INTO `asistencias` VALUES("230","Giraldo Mazo Sara Paulina ","TECNOLOGIA","SI","30/10/2024","135937935");
INSERT INTO `asistencias` VALUES("231","Giraldo Mazo Sara Paulina ","URBANIDAD","SI","30/10/2024","135937935");
INSERT INTO `asistencias` VALUES("232","Giraldo Mazo Sara Paulina ","ED. FISICA","SI","31/10/2024","135937935");
INSERT INTO `asistencias` VALUES("233","Giraldo Mazo Sara Paulina ","FISICA (MATEMÁTICA)","=COUNTIFS(F29:O29,$V$9)","00/10/2024","135937935");
INSERT INTO `asistencias` VALUES("234","Giraldo Mazo Sara Paulina ","ARTISTICA","=COUNTIFS(F29:O29,$V$9)","00/10/2024","135937935");
INSERT INTO `asistencias` VALUES("235","Giraldo Mazo Sara Paulina ","FISICA (MATEMÁTICA)","SI","07/10/2024","135937935");
INSERT INTO `asistencias` VALUES("236","Giraldo Mazo Sara Paulina ","ARTISTICA","SI","07/10/2024","135937935");
INSERT INTO `asistencias` VALUES("237","Gomez Pamplona Julieta","FISICA (MATEMÁTICA)","nr","21/10/2024","1037974896");
INSERT INTO `asistencias` VALUES("238","Gomez Pamplona Julieta","ARTISTICA","nr","21/10/2024","1037974896");
INSERT INTO `asistencias` VALUES("239","Gomez Pamplona Julieta","MATEMATICAS","SI","22/10/2024","1037974896");
INSERT INTO `asistencias` VALUES("240","Gomez Pamplona Julieta","ED. FISICA","SI","22/10/2024","1037974896");
INSERT INTO `asistencias` VALUES("241","Gomez Pamplona Julieta","TECNOLOGIA","SI","23/10/2024","1037974896");
INSERT INTO `asistencias` VALUES("242","Gomez Pamplona Julieta","URBANIDAD","SI","23/10/2024","1037974896");
INSERT INTO `asistencias` VALUES("243","Gomez Pamplona Julieta","ED. FISICA","SI","24/10/2024","1037974896");
INSERT INTO `asistencias` VALUES("244","Gomez Pamplona Julieta","FISICA (MATEMÁTICA)","SI","28/10/2024","1037974896");
INSERT INTO `asistencias` VALUES("245","Gomez Pamplona Julieta","ARTISTICA","SI","28/10/2024","1037974896");
INSERT INTO `asistencias` VALUES("246","Gomez Pamplona Julieta","MATEMATICAS","NR","29/10/2024","1037974896");
INSERT INTO `asistencias` VALUES("247","Gomez Pamplona Julieta","ED. FISICA","NR","29/10/2024","1037974896");
INSERT INTO `asistencias` VALUES("248","Gomez Pamplona Julieta","TECNOLOGIA","SI","30/10/2024","1037974896");
INSERT INTO `asistencias` VALUES("249","Gomez Pamplona Julieta","URBANIDAD","SI","30/10/2024","1037974896");
INSERT INTO `asistencias` VALUES("250","Gomez Pamplona Julieta","ED. FISICA","SI","31/10/2024","1037974896");
INSERT INTO `asistencias` VALUES("251","Gomez Pamplona Julieta","FISICA (MATEMÁTICA)","=COUNTIFS(F30:O30,$V$9)","00/10/2024","1037974896");
INSERT INTO `asistencias` VALUES("252","Gomez Pamplona Julieta","ARTISTICA","=COUNTIFS(F30:O30,$V$9)","00/10/2024","1037974896");
INSERT INTO `asistencias` VALUES("253","Gomez Pamplona Julieta","FISICA (MATEMÁTICA)","SI","07/10/2024","1037974896");
INSERT INTO `asistencias` VALUES("254","Gomez Pamplona Julieta","ARTISTICA","SI","07/10/2024","1037974896");
INSERT INTO `asistencias` VALUES("255","Gonzalez Martinez Salome","FISICA (MATEMÁTICA)","SI","21/10/2024","1022006639");
INSERT INTO `asistencias` VALUES("256","Gonzalez Martinez Salome","ARTISTICA","SI","21/10/2024","1022006639");
INSERT INTO `asistencias` VALUES("257","Gonzalez Martinez Salome","MATEMATICAS","P","22/10/2024","1022006639");
INSERT INTO `asistencias` VALUES("258","Gonzalez Martinez Salome","ED. FISICA","P","22/10/2024","1022006639");
INSERT INTO `asistencias` VALUES("259","Gonzalez Martinez Salome","TECNOLOGIA","P","23/10/2024","1022006639");
INSERT INTO `asistencias` VALUES("260","Gonzalez Martinez Salome","URBANIDAD","P","23/10/2024","1022006639");
INSERT INTO `asistencias` VALUES("261","Gonzalez Martinez Salome","ED. FISICA","SI","24/10/2024","1022006639");
INSERT INTO `asistencias` VALUES("262","Gonzalez Martinez Salome","FISICA (MATEMÁTICA)","SI","28/10/2024","1022006639");
INSERT INTO `asistencias` VALUES("263","Gonzalez Martinez Salome","ARTISTICA","SI","28/10/2024","1022006639");
INSERT INTO `asistencias` VALUES("264","Gonzalez Martinez Salome","MATEMATICAS","SI","29/10/2024","1022006639");
INSERT INTO `asistencias` VALUES("265","Gonzalez Martinez Salome","ED. FISICA","SI","29/10/2024","1022006639");
INSERT INTO `asistencias` VALUES("266","Gonzalez Martinez Salome","TECNOLOGIA","SI","30/10/2024","1022006639");
INSERT INTO `asistencias` VALUES("267","Gonzalez Martinez Salome","URBANIDAD","SI","30/10/2024","1022006639");
INSERT INTO `asistencias` VALUES("268","Gonzalez Martinez Salome","ED. FISICA","SI","31/10/2024","1022006639");
INSERT INTO `asistencias` VALUES("269","Gonzalez Martinez Salome","FISICA (MATEMÁTICA)","=COUNTIFS(F31:O31,$V$9)","00/10/2024","1022006639");
INSERT INTO `asistencias` VALUES("270","Gonzalez Martinez Salome","ARTISTICA","=COUNTIFS(F31:O31,$V$9)","00/10/2024","1022006639");
INSERT INTO `asistencias` VALUES("271","Gonzalez Martinez Salome","FISICA (MATEMÁTICA)","P","07/10/2024","1022006639");
INSERT INTO `asistencias` VALUES("272","Gonzalez Martinez Salome","ARTISTICA","P","07/10/2024","1022006639");
INSERT INTO `asistencias` VALUES("273","Valencia  Vasquez Anderson ","FISICA (MATEMÁTICA)","SI","21/10/2024","1045396829");
INSERT INTO `asistencias` VALUES("274","Valencia  Vasquez Anderson ","ARTISTICA","SI","21/10/2024","1045396829");
INSERT INTO `asistencias` VALUES("275","Valencia  Vasquez Anderson ","MATEMATICAS","SI","22/10/2024","1045396829");
INSERT INTO `asistencias` VALUES("276","Valencia  Vasquez Anderson ","ED. FISICA","SI","22/10/2024","1045396829");
INSERT INTO `asistencias` VALUES("277","Valencia  Vasquez Anderson ","TECNOLOGIA","SI","23/10/2024","1045396829");
INSERT INTO `asistencias` VALUES("278","Valencia  Vasquez Anderson ","URBANIDAD","SI","23/10/2024","1045396829");
INSERT INTO `asistencias` VALUES("279","Valencia  Vasquez Anderson ","ED. FISICA","SI","24/10/2024","1045396829");
INSERT INTO `asistencias` VALUES("280","Valencia  Vasquez Anderson ","FISICA (MATEMÁTICA)","SI","28/10/2024","1045396829");
INSERT INTO `asistencias` VALUES("281","Valencia  Vasquez Anderson ","ARTISTICA","SI","28/10/2024","1045396829");
INSERT INTO `asistencias` VALUES("282","Valencia  Vasquez Anderson ","MATEMATICAS","SI","29/10/2024","1045396829");
INSERT INTO `asistencias` VALUES("283","Valencia  Vasquez Anderson ","ED. FISICA","SI","29/10/2024","1045396829");
INSERT INTO `asistencias` VALUES("284","Valencia  Vasquez Anderson ","TECNOLOGIA","SI","30/10/2024","1045396829");
INSERT INTO `asistencias` VALUES("285","Valencia  Vasquez Anderson ","URBANIDAD","SI","30/10/2024","1045396829");
INSERT INTO `asistencias` VALUES("286","Valencia  Vasquez Anderson ","ED. FISICA","SI","31/10/2024","1045396829");
INSERT INTO `asistencias` VALUES("287","Valencia  Vasquez Anderson ","FISICA (MATEMÁTICA)","=COUNTIFS(F33:O33,$V$9)","00/10/2024","1045396829");
INSERT INTO `asistencias` VALUES("288","Valencia  Vasquez Anderson ","ARTISTICA","=COUNTIFS(F33:O33,$V$9)","00/10/2024","1045396829");
INSERT INTO `asistencias` VALUES("289","Valencia  Vasquez Anderson ","FISICA (MATEMÁTICA)","SI","07/10/2024","1045396829");
INSERT INTO `asistencias` VALUES("290","Valencia  Vasquez Anderson ","ARTISTICA","SI","07/10/2024","1045396829");
INSERT INTO `asistencias` VALUES("291","Zapata Amaya Manuel Salvador","FISICA (MATEMÁTICA)","SI","21/10/2024","1045626153");
INSERT INTO `asistencias` VALUES("292","Zapata Amaya Manuel Salvador","ARTISTICA","SI","21/10/2024","1045626153");
INSERT INTO `asistencias` VALUES("293","Zapata Amaya Manuel Salvador","MATEMATICAS","SI","22/10/2024","1045626153");
INSERT INTO `asistencias` VALUES("294","Zapata Amaya Manuel Salvador","ED. FISICA","SI","22/10/2024","1045626153");
INSERT INTO `asistencias` VALUES("295","Zapata Amaya Manuel Salvador","TECNOLOGIA","SI","23/10/2024","1045626153");
INSERT INTO `asistencias` VALUES("296","Zapata Amaya Manuel Salvador","URBANIDAD","SI","23/10/2024","1045626153");
INSERT INTO `asistencias` VALUES("297","Zapata Amaya Manuel Salvador","ED. FISICA","SI","24/10/2024","1045626153");
INSERT INTO `asistencias` VALUES("298","Zapata Amaya Manuel Salvador","FISICA (MATEMÁTICA)","SI","28/10/2024","1045626153");
INSERT INTO `asistencias` VALUES("299","Zapata Amaya Manuel Salvador","ARTISTICA","SI","28/10/2024","1045626153");
INSERT INTO `asistencias` VALUES("300","Zapata Amaya Manuel Salvador","MATEMATICAS","SI","29/10/2024","1045626153");
INSERT INTO `asistencias` VALUES("301","Zapata Amaya Manuel Salvador","ED. FISICA","SI","29/10/2024","1045626153");
INSERT INTO `asistencias` VALUES("302","Zapata Amaya Manuel Salvador","TECNOLOGIA","SI","30/10/2024","1045626153");
INSERT INTO `asistencias` VALUES("303","Zapata Amaya Manuel Salvador","URBANIDAD","SI","30/10/2024","1045626153");
INSERT INTO `asistencias` VALUES("304","Zapata Amaya Manuel Salvador","ED. FISICA","SI","31/10/2024","1045626153");
INSERT INTO `asistencias` VALUES("305","Zapata Amaya Manuel Salvador","FISICA (MATEMÁTICA)","=COUNTIFS(F35:O35,$V$9)","00/10/2024","1045626153");
INSERT INTO `asistencias` VALUES("306","Zapata Amaya Manuel Salvador","ARTISTICA","=COUNTIFS(F35:O35,$V$9)","00/10/2024","1045626153");
INSERT INTO `asistencias` VALUES("307","Zapata Amaya Manuel Salvador","FISICA (MATEMÁTICA)","SI","07/10/2024","1045626153");
INSERT INTO `asistencias` VALUES("308","Zapata Amaya Manuel Salvador","ARTISTICA","SI","07/10/2024","1045626153");
INSERT INTO `asistencias` VALUES("309","Duque Gil Julio Cesar","FISICA (MATEMÁTICA)","nr","21/10/2024","1018236286");
INSERT INTO `asistencias` VALUES("310","Duque Gil Julio Cesar","ARTISTICA","nr","21/10/2024","1018236286");
INSERT INTO `asistencias` VALUES("311","Duque Gil Julio Cesar","MATEMATICAS","NO","22/10/2024","1018236286");
INSERT INTO `asistencias` VALUES("312","Duque Gil Julio Cesar","ED. FISICA","NO","22/10/2024","1018236286");
INSERT INTO `asistencias` VALUES("313","Duque Gil Julio Cesar","TECNOLOGIA","SI","23/10/2024","1018236286");
INSERT INTO `asistencias` VALUES("314","Duque Gil Julio Cesar","URBANIDAD","SI","23/10/2024","1018236286");
INSERT INTO `asistencias` VALUES("315","Duque Gil Julio Cesar","ED. FISICA","no","24/10/2024","1018236286");
INSERT INTO `asistencias` VALUES("316","Duque Gil Julio Cesar","FISICA (MATEMÁTICA)","NO","28/10/2024","1018236286");
INSERT INTO `asistencias` VALUES("317","Duque Gil Julio Cesar","ARTISTICA","NO","28/10/2024","1018236286");
INSERT INTO `asistencias` VALUES("318","Duque Gil Julio Cesar","MATEMATICAS","NO","29/10/2024","1018236286");
INSERT INTO `asistencias` VALUES("319","Duque Gil Julio Cesar","ED. FISICA","NO","29/10/2024","1018236286");
INSERT INTO `asistencias` VALUES("320","Duque Gil Julio Cesar","TECNOLOGIA","NO","30/10/2024","1018236286");
INSERT INTO `asistencias` VALUES("321","Duque Gil Julio Cesar","URBANIDAD","NO","30/10/2024","1018236286");
INSERT INTO `asistencias` VALUES("322","Duque Gil Julio Cesar","ED. FISICA","NO","31/10/2024","1018236286");
INSERT INTO `asistencias` VALUES("323","Duque Gil Julio Cesar","FISICA (MATEMÁTICA)","=COUNTIFS(F37:O37,$V$9)","00/10/2024","1018236286");
INSERT INTO `asistencias` VALUES("324","Duque Gil Julio Cesar","ARTISTICA","=COUNTIFS(F37:O37,$V$9)","00/10/2024","1018236286");
INSERT INTO `asistencias` VALUES("325","Duque Gil Julio Cesar","FISICA (MATEMÁTICA)","NO","07/10/2024","1018236286");
INSERT INTO `asistencias` VALUES("326","Duque Gil Julio Cesar","ARTISTICA","NO","07/10/2024","1018236286");
INSERT INTO `asistencias` VALUES("327","Garcia Navarro Sharith Michell","FISICA (MATEMÁTICA)","SI","21/10/2024","1045231554");
INSERT INTO `asistencias` VALUES("328","Garcia Navarro Sharith Michell","ARTISTICA","SI","21/10/2024","1045231554");
INSERT INTO `asistencias` VALUES("329","Garcia Navarro Sharith Michell","MATEMATICAS","SI","22/10/2024","1045231554");
INSERT INTO `asistencias` VALUES("330","Garcia Navarro Sharith Michell","ED. FISICA","SI","22/10/2024","1045231554");
INSERT INTO `asistencias` VALUES("331","Garcia Navarro Sharith Michell","TECNOLOGIA","SI","23/10/2024","1045231554");
INSERT INTO `asistencias` VALUES("332","Garcia Navarro Sharith Michell","URBANIDAD","SI","23/10/2024","1045231554");
INSERT INTO `asistencias` VALUES("333","Garcia Navarro Sharith Michell","ED. FISICA","SI","24/10/2024","1045231554");
INSERT INTO `asistencias` VALUES("334","Garcia Navarro Sharith Michell","FISICA (MATEMÁTICA)","SI","28/10/2024","1045231554");
INSERT INTO `asistencias` VALUES("335","Garcia Navarro Sharith Michell","ARTISTICA","SI","28/10/2024","1045231554");
INSERT INTO `asistencias` VALUES("336","Garcia Navarro Sharith Michell","MATEMATICAS","SI","29/10/2024","1045231554");
INSERT INTO `asistencias` VALUES("337","Garcia Navarro Sharith Michell","ED. FISICA","SI","29/10/2024","1045231554");
INSERT INTO `asistencias` VALUES("338","Garcia Navarro Sharith Michell","TECNOLOGIA","SI","30/10/2024","1045231554");
INSERT INTO `asistencias` VALUES("339","Garcia Navarro Sharith Michell","URBANIDAD","SI","30/10/2024","1045231554");
INSERT INTO `asistencias` VALUES("340","Garcia Navarro Sharith Michell","ED. FISICA","SI","31/10/2024","1045231554");
INSERT INTO `asistencias` VALUES("341","Garcia Navarro Sharith Michell","FISICA (MATEMÁTICA)","=COUNTIFS(F38:O38,$V$9)","00/10/2024","1045231554");
INSERT INTO `asistencias` VALUES("342","Garcia Navarro Sharith Michell","ARTISTICA","=COUNTIFS(F38:O38,$V$9)","00/10/2024","1045231554");
INSERT INTO `asistencias` VALUES("343","Garcia Navarro Sharith Michell","FISICA (MATEMÁTICA)","SI","07/10/2024","1045231554");
INSERT INTO `asistencias` VALUES("344","Garcia Navarro Sharith Michell","ARTISTICA","SI","07/10/2024","1045231554");
INSERT INTO `asistencias` VALUES("345","Perez Ana Sofia","FISICA (MATEMÁTICA)","SI","21/10/2024","1022149324");
INSERT INTO `asistencias` VALUES("346","Perez Ana Sofia","ARTISTICA","SI","21/10/2024","1022149324");
INSERT INTO `asistencias` VALUES("347","Perez Ana Sofia","MATEMATICAS","SI","22/10/2024","1022149324");
INSERT INTO `asistencias` VALUES("348","Perez Ana Sofia","ED. FISICA","SI","22/10/2024","1022149324");
INSERT INTO `asistencias` VALUES("349","Perez Ana Sofia","TECNOLOGIA","SI","23/10/2024","1022149324");
INSERT INTO `asistencias` VALUES("350","Perez Ana Sofia","URBANIDAD","SI","23/10/2024","1022149324");
INSERT INTO `asistencias` VALUES("351","Perez Ana Sofia","ED. FISICA","P","24/10/2024","1022149324");
INSERT INTO `asistencias` VALUES("352","Perez Ana Sofia","FISICA (MATEMÁTICA)","SI","28/10/2024","1022149324");
INSERT INTO `asistencias` VALUES("353","Perez Ana Sofia","ARTISTICA","SI","28/10/2024","1022149324");
INSERT INTO `asistencias` VALUES("354","Perez Ana Sofia","MATEMATICAS","SI","29/10/2024","1022149324");
INSERT INTO `asistencias` VALUES("355","Perez Ana Sofia","ED. FISICA","SI","29/10/2024","1022149324");
INSERT INTO `asistencias` VALUES("356","Perez Ana Sofia","TECNOLOGIA","SI","30/10/2024","1022149324");
INSERT INTO `asistencias` VALUES("357","Perez Ana Sofia","URBANIDAD","SI","30/10/2024","1022149324");
INSERT INTO `asistencias` VALUES("358","Perez Ana Sofia","ED. FISICA","SI","31/10/2024","1022149324");
INSERT INTO `asistencias` VALUES("359","Perez Ana Sofia","FISICA (MATEMÁTICA)","=COUNTIFS(F39:O39,$V$9)","00/10/2024","1022149324");
INSERT INTO `asistencias` VALUES("360","Perez Ana Sofia","ARTISTICA","=COUNTIFS(F39:O39,$V$9)","00/10/2024","1022149324");
INSERT INTO `asistencias` VALUES("361","Perez Ana Sofia","FISICA (MATEMÁTICA)","SI","07/10/2024","1022149324");
INSERT INTO `asistencias` VALUES("362","Perez Ana Sofia","ARTISTICA","SI","07/10/2024","1022149324");
INSERT INTO `asistencias` VALUES("363","Diaz Rosso Freddy De Jesus","MATEMATICAS","SI","04/10/2024","1058198933");
INSERT INTO `asistencias` VALUES("364","Diaz Rosso Freddy De Jesus","EMPRENDIMIENTO","SI","04/10/2024","1058198933");
INSERT INTO `asistencias` VALUES("365","Diaz Rosso Freddy De Jesus","C. NATURALES","SI","10/10/2024","1058198933");
INSERT INTO `asistencias` VALUES("366","Diaz Rosso Freddy De Jesus","C. SOCIALES","SI","10/10/2024","1058198933");
INSERT INTO `asistencias` VALUES("367","Diaz Rosso Freddy De Jesus","ED. FISICA","SI","10/10/2024","1058198933");
INSERT INTO `asistencias` VALUES("368","Diaz Rosso Freddy De Jesus","MATEMATICAS","SI","11/10/2024","1058198933");
INSERT INTO `asistencias` VALUES("369","Diaz Rosso Freddy De Jesus","EMPRENDIMIENTO","SI","11/10/2024","1058198933");
INSERT INTO `asistencias` VALUES("370","Diaz Rosso Freddy De Jesus","C. NATURALES","SI","14/10/2024","1058198933");
INSERT INTO `asistencias` VALUES("371","Diaz Rosso Freddy De Jesus","ARTISTICA","SI","14/10/2024","1058198933");
INSERT INTO `asistencias` VALUES("372","Diaz Rosso Freddy De Jesus","C. NATURALES","SI","17/10/2024","1058198933");
INSERT INTO `asistencias` VALUES("373","Diaz Rosso Freddy De Jesus","C. SOCIALES","SI","17/10/2024","1058198933");
INSERT INTO `asistencias` VALUES("374","Diaz Rosso Freddy De Jesus","ED. FISICA","SI","17/10/2024","1058198933");
INSERT INTO `asistencias` VALUES("375","Diaz Rosso Freddy De Jesus","MATEMATICAS","NR","18/10/2024","1058198933");
INSERT INTO `asistencias` VALUES("376","Diaz Rosso Freddy De Jesus","EMPRENDIMIENTO","NR","18/10/2024","1058198933");
INSERT INTO `asistencias` VALUES("377","Diaz Rosso Freddy De Jesus","MATEMATICAS","SI","25/10/2024","1058198933");
INSERT INTO `asistencias` VALUES("378","Diaz Rosso Freddy De Jesus","EMPRENDIMIENTO","SI","25/10/2024","1058198933");
INSERT INTO `asistencias` VALUES("379","Giraldo Mazo Santiago","MATEMATICAS","SI","04/10/2024","1036258563");
INSERT INTO `asistencias` VALUES("380","Giraldo Mazo Santiago","EMPRENDIMIENTO","SI","04/10/2024","1036258563");
INSERT INTO `asistencias` VALUES("381","Giraldo Mazo Santiago","C. NATURALES","NR","10/10/2024","1036258563");
INSERT INTO `asistencias` VALUES("382","Giraldo Mazo Santiago","C. SOCIALES","NR","10/10/2024","1036258563");
INSERT INTO `asistencias` VALUES("383","Giraldo Mazo Santiago","ED. FISICA","NR","10/10/2024","1036258563");
INSERT INTO `asistencias` VALUES("384","Giraldo Mazo Santiago","MATEMATICAS","SI","11/10/2024","1036258563");
INSERT INTO `asistencias` VALUES("385","Giraldo Mazo Santiago","EMPRENDIMIENTO","SI","11/10/2024","1036258563");
INSERT INTO `asistencias` VALUES("386","Giraldo Mazo Santiago","C. NATURALES","SI","14/10/2024","1036258563");
INSERT INTO `asistencias` VALUES("387","Giraldo Mazo Santiago","ARTISTICA","SI","14/10/2024","1036258563");
INSERT INTO `asistencias` VALUES("388","Giraldo Mazo Santiago","C. NATURALES","SI","17/10/2024","1036258563");
INSERT INTO `asistencias` VALUES("389","Giraldo Mazo Santiago","C. SOCIALES","SI","17/10/2024","1036258563");
INSERT INTO `asistencias` VALUES("390","Giraldo Mazo Santiago","ED. FISICA","SI","17/10/2024","1036258563");
INSERT INTO `asistencias` VALUES("391","Giraldo Mazo Santiago","MATEMATICAS","NR","18/10/2024","1036258563");
INSERT INTO `asistencias` VALUES("392","Giraldo Mazo Santiago","EMPRENDIMIENTO","NR","18/10/2024","1036258563");
INSERT INTO `asistencias` VALUES("393","Giraldo Mazo Santiago","MATEMATICAS","SI","25/10/2024","1036258563");
INSERT INTO `asistencias` VALUES("394","Giraldo Mazo Santiago","EMPRENDIMIENTO","SI","25/10/2024","1036258563");
INSERT INTO `asistencias` VALUES("395","Herazo Valeria Areiza","MATEMATICAS","NR","04/10/2024","1115576678");
INSERT INTO `asistencias` VALUES("396","Herazo Valeria Areiza","EMPRENDIMIENTO","NR","04/10/2024","1115576678");
INSERT INTO `asistencias` VALUES("397","Herazo Valeria Areiza","C. NATURALES","SI","10/10/2024","1115576678");
INSERT INTO `asistencias` VALUES("398","Herazo Valeria Areiza","C. SOCIALES","SI","10/10/2024","1115576678");
INSERT INTO `asistencias` VALUES("399","Herazo Valeria Areiza","ED. FISICA","SI","10/10/2024","1115576678");
INSERT INTO `asistencias` VALUES("400","Herazo Valeria Areiza","MATEMATICAS","SI","11/10/2024","1115576678");
INSERT INTO `asistencias` VALUES("401","Herazo Valeria Areiza","EMPRENDIMIENTO","SI","11/10/2024","1115576678");
INSERT INTO `asistencias` VALUES("402","Herazo Valeria Areiza","C. NATURALES","SI","14/10/2024","1115576678");
INSERT INTO `asistencias` VALUES("403","Herazo Valeria Areiza","ARTISTICA","SI","14/10/2024","1115576678");
INSERT INTO `asistencias` VALUES("404","Herazo Valeria Areiza","C. NATURALES","SI","17/10/2024","1115576678");
INSERT INTO `asistencias` VALUES("405","Herazo Valeria Areiza","C. SOCIALES","SI","17/10/2024","1115576678");
INSERT INTO `asistencias` VALUES("406","Herazo Valeria Areiza","ED. FISICA","SI","17/10/2024","1115576678");
INSERT INTO `asistencias` VALUES("407","Herazo Valeria Areiza","MATEMATICAS","SI","18/10/2024","1115576678");
INSERT INTO `asistencias` VALUES("408","Herazo Valeria Areiza","EMPRENDIMIENTO","SI","18/10/2024","1115576678");
INSERT INTO `asistencias` VALUES("409","Herazo Valeria Areiza","MATEMATICAS","SI","25/10/2024","1115576678");
INSERT INTO `asistencias` VALUES("410","Herazo Valeria Areiza","EMPRENDIMIENTO","SI","25/10/2024","1115576678");
INSERT INTO `asistencias` VALUES("411","Orozco Chavarria Cristobal","MATEMATICAS","SI","04/10/2024","1036945161");
INSERT INTO `asistencias` VALUES("412","Orozco Chavarria Cristobal","EMPRENDIMIENTO","SI","04/10/2024","1036945161");
INSERT INTO `asistencias` VALUES("413","Orozco Chavarria Cristobal","C. NATURALES","SI","10/10/2024","1036945161");
INSERT INTO `asistencias` VALUES("414","Orozco Chavarria Cristobal","C. SOCIALES","SI","10/10/2024","1036945161");
INSERT INTO `asistencias` VALUES("415","Orozco Chavarria Cristobal","ED. FISICA","SI","10/10/2024","1036945161");
INSERT INTO `asistencias` VALUES("416","Orozco Chavarria Cristobal","MATEMATICAS","SI","11/10/2024","1036945161");
INSERT INTO `asistencias` VALUES("417","Orozco Chavarria Cristobal","EMPRENDIMIENTO","SI","11/10/2024","1036945161");
INSERT INTO `asistencias` VALUES("418","Orozco Chavarria Cristobal","C. NATURALES","no","14/10/2024","1036945161");
INSERT INTO `asistencias` VALUES("419","Orozco Chavarria Cristobal","ARTISTICA","no","14/10/2024","1036945161");
INSERT INTO `asistencias` VALUES("420","Orozco Chavarria Cristobal","C. NATURALES","NO","17/10/2024","1036945161");
INSERT INTO `asistencias` VALUES("421","Orozco Chavarria Cristobal","C. SOCIALES","NO","17/10/2024","1036945161");
INSERT INTO `asistencias` VALUES("422","Orozco Chavarria Cristobal","ED. FISICA","NO","17/10/2024","1036945161");
INSERT INTO `asistencias` VALUES("423","Orozco Chavarria Cristobal","MATEMATICAS","NR","18/10/2024","1036945161");
INSERT INTO `asistencias` VALUES("424","Orozco Chavarria Cristobal","EMPRENDIMIENTO","NR","18/10/2024","1036945161");
INSERT INTO `asistencias` VALUES("425","Orozco Chavarria Cristobal","MATEMATICAS","SI","25/10/2024","1036945161");
INSERT INTO `asistencias` VALUES("426","Orozco Chavarria Cristobal","EMPRENDIMIENTO","SI","25/10/2024","1036945161");
INSERT INTO `asistencias` VALUES("427","Velazques Muñoz Anyeli Tatiana","MATEMATICAS","SI","04/10/2024","1037975509");
INSERT INTO `asistencias` VALUES("428","Velazques Muñoz Anyeli Tatiana","EMPRENDIMIENTO","SI","04/10/2024","1037975509");
INSERT INTO `asistencias` VALUES("429","Velazques Muñoz Anyeli Tatiana","C. NATURALES","NR","10/10/2024","1037975509");
INSERT INTO `asistencias` VALUES("430","Velazques Muñoz Anyeli Tatiana","C. SOCIALES","NR","10/10/2024","1037975509");
INSERT INTO `asistencias` VALUES("431","Velazques Muñoz Anyeli Tatiana","ED. FISICA","NR","10/10/2024","1037975509");
INSERT INTO `asistencias` VALUES("432","Velazques Muñoz Anyeli Tatiana","MATEMATICAS","SI","11/10/2024","1037975509");
INSERT INTO `asistencias` VALUES("433","Velazques Muñoz Anyeli Tatiana","EMPRENDIMIENTO","SI","11/10/2024","1037975509");
INSERT INTO `asistencias` VALUES("434","Velazques Muñoz Anyeli Tatiana","C. NATURALES","SI","14/10/2024","1037975509");
INSERT INTO `asistencias` VALUES("435","Velazques Muñoz Anyeli Tatiana","ARTISTICA","SI","14/10/2024","1037975509");
INSERT INTO `asistencias` VALUES("436","Velazques Muñoz Anyeli Tatiana","C. NATURALES","SI","17/10/2024","1037975509");
INSERT INTO `asistencias` VALUES("437","Velazques Muñoz Anyeli Tatiana","C. SOCIALES","SI","17/10/2024","1037975509");
INSERT INTO `asistencias` VALUES("438","Velazques Muñoz Anyeli Tatiana","ED. FISICA","SI","17/10/2024","1037975509");
INSERT INTO `asistencias` VALUES("439","Velazques Muñoz Anyeli Tatiana","MATEMATICAS","NR","18/10/2024","1037975509");
INSERT INTO `asistencias` VALUES("440","Velazques Muñoz Anyeli Tatiana","EMPRENDIMIENTO","NR","18/10/2024","1037975509");
INSERT INTO `asistencias` VALUES("441","Velazques Muñoz Anyeli Tatiana","MATEMATICAS","SI","25/10/2024","1037975509");
INSERT INTO `asistencias` VALUES("442","Velazques Muñoz Anyeli Tatiana","EMPRENDIMIENTO","SI","25/10/2024","1037975509");
INSERT INTO `asistencias` VALUES("443","Duque Aristizabal Angel  Gabriel","MATEMATICAS","SI","04/10/2024","1127955146");
INSERT INTO `asistencias` VALUES("444","Duque Aristizabal Angel  Gabriel","EMPRENDIMIENTO","SI","04/10/2024","1127955146");
INSERT INTO `asistencias` VALUES("445","Duque Aristizabal Angel  Gabriel","ED. FISICA","SI","10/10/2024","1127955146");
INSERT INTO `asistencias` VALUES("446","Duque Aristizabal Angel  Gabriel","MATEMATICAS","SI","11/10/2024","1127955146");
INSERT INTO `asistencias` VALUES("447","Duque Aristizabal Angel  Gabriel","EMPRENDIMIENTO","SI","11/10/2024","1127955146");
INSERT INTO `asistencias` VALUES("448","Duque Aristizabal Angel  Gabriel","FISICA (MATEMÁTICA)","SI","14/10/2024","1127955146");
INSERT INTO `asistencias` VALUES("449","Duque Aristizabal Angel  Gabriel","ARTISTICA","SI","14/10/2024","1127955146");
INSERT INTO `asistencias` VALUES("450","Duque Aristizabal Angel  Gabriel","ED. FISICA","SI","17/10/2024","1127955146");
INSERT INTO `asistencias` VALUES("451","Duque Aristizabal Angel  Gabriel","MATEMATICAS","SI","18/10/2024","1127955146");
INSERT INTO `asistencias` VALUES("452","Duque Aristizabal Angel  Gabriel","EMPRENDIMIENTO","SI","18/10/2024","1127955146");
INSERT INTO `asistencias` VALUES("453","Duque Aristizabal Angel  Gabriel","MATEMATICAS","SI","25/10/2024","1127955146");
INSERT INTO `asistencias` VALUES("454","Duque Aristizabal Angel  Gabriel","EMPRENDIMIENTO","SI","25/10/2024","1127955146");
INSERT INTO `asistencias` VALUES("455","Giraldo Giraldo Juan Camilo","MATEMATICAS","SI","04/10/2024","1050038422");
INSERT INTO `asistencias` VALUES("456","Giraldo Giraldo Juan Camilo","EMPRENDIMIENTO","SI","04/10/2024","1050038422");
INSERT INTO `asistencias` VALUES("457","Giraldo Giraldo Juan Camilo","ED. FISICA","SI","10/10/2024","1050038422");
INSERT INTO `asistencias` VALUES("458","Giraldo Giraldo Juan Camilo","MATEMATICAS","SI","11/10/2024","1050038422");
INSERT INTO `asistencias` VALUES("459","Giraldo Giraldo Juan Camilo","EMPRENDIMIENTO","SI","11/10/2024","1050038422");
INSERT INTO `asistencias` VALUES("460","Giraldo Giraldo Juan Camilo","FISICA (MATEMÁTICA)","SI","14/10/2024","1050038422");
INSERT INTO `asistencias` VALUES("461","Giraldo Giraldo Juan Camilo","ARTISTICA","SI","14/10/2024","1050038422");
INSERT INTO `asistencias` VALUES("462","Giraldo Giraldo Juan Camilo","ED. FISICA","SI","17/10/2024","1050038422");
INSERT INTO `asistencias` VALUES("463","Giraldo Giraldo Juan Camilo","MATEMATICAS","SI","18/10/2024","1050038422");
INSERT INTO `asistencias` VALUES("464","Giraldo Giraldo Juan Camilo","EMPRENDIMIENTO","SI","18/10/2024","1050038422");
INSERT INTO `asistencias` VALUES("465","Giraldo Giraldo Juan Camilo","MATEMATICAS","SI","25/10/2024","1050038422");
INSERT INTO `asistencias` VALUES("466","Giraldo Giraldo Juan Camilo","EMPRENDIMIENTO","SI","25/10/2024","1050038422");
INSERT INTO `asistencias` VALUES("467","Lopez Aguirre Alexis","MATEMATICAS","SI","04/10/2024","1037974417");
INSERT INTO `asistencias` VALUES("468","Lopez Aguirre Alexis","EMPRENDIMIENTO","SI","04/10/2024","1037974417");
INSERT INTO `asistencias` VALUES("469","Lopez Aguirre Alexis","ED. FISICA","SI","10/10/2024","1037974417");
INSERT INTO `asistencias` VALUES("470","Lopez Aguirre Alexis","MATEMATICAS","SI","11/10/2024","1037974417");
INSERT INTO `asistencias` VALUES("471","Lopez Aguirre Alexis","EMPRENDIMIENTO","SI","11/10/2024","1037974417");
INSERT INTO `asistencias` VALUES("472","Lopez Aguirre Alexis","FISICA (MATEMÁTICA)","SI","14/10/2024","1037974417");
INSERT INTO `asistencias` VALUES("473","Lopez Aguirre Alexis","ARTISTICA","SI","14/10/2024","1037974417");
INSERT INTO `asistencias` VALUES("474","Lopez Aguirre Alexis","ED. FISICA","SI","17/10/2024","1037974417");
INSERT INTO `asistencias` VALUES("475","Lopez Aguirre Alexis","MATEMATICAS","SI","18/10/2024","1037974417");
INSERT INTO `asistencias` VALUES("476","Lopez Aguirre Alexis","EMPRENDIMIENTO","SI","18/10/2024","1037974417");
INSERT INTO `asistencias` VALUES("477","Lopez Aguirre Alexis","MATEMATICAS","SI","25/10/2024","1037974417");
INSERT INTO `asistencias` VALUES("478","Lopez Aguirre Alexis","EMPRENDIMIENTO","SI","25/10/2024","1037974417");
INSERT INTO `asistencias` VALUES("479","Duque Aristizabal Jennyfer Tatiana","MATEMATICAS","SI","04/10/2024","1127955147");
INSERT INTO `asistencias` VALUES("480","Duque Aristizabal Jennyfer Tatiana","EMPRENDIMIENTO","SI","04/10/2024","1127955147");
INSERT INTO `asistencias` VALUES("481","Duque Aristizabal Jennyfer Tatiana","ED. FISICA","SI","10/10/2024","1127955147");
INSERT INTO `asistencias` VALUES("482","Duque Aristizabal Jennyfer Tatiana","MATEMATICAS","SI","11/10/2024","1127955147");
INSERT INTO `asistencias` VALUES("483","Duque Aristizabal Jennyfer Tatiana","EMPRENDIMIENTO","SI","11/10/2024","1127955147");
INSERT INTO `asistencias` VALUES("484","Duque Aristizabal Jennyfer Tatiana","FISICA (MATEMÁTICA)","nr","14/10/2024","1127955147");
INSERT INTO `asistencias` VALUES("485","Duque Aristizabal Jennyfer Tatiana","ARTISTICA","nr","14/10/2024","1127955147");
INSERT INTO `asistencias` VALUES("486","Duque Aristizabal Jennyfer Tatiana","ED. FISICA","SI","17/10/2024","1127955147");
INSERT INTO `asistencias` VALUES("487","Duque Aristizabal Jennyfer Tatiana","MATEMATICAS","SI","18/10/2024","1127955147");
INSERT INTO `asistencias` VALUES("488","Duque Aristizabal Jennyfer Tatiana","EMPRENDIMIENTO","SI","18/10/2024","1127955147");
INSERT INTO `asistencias` VALUES("489","Duque Aristizabal Jennyfer Tatiana","MATEMATICAS","SI","25/10/2024","1127955147");
INSERT INTO `asistencias` VALUES("490","Duque Aristizabal Jennyfer Tatiana","EMPRENDIMIENTO","SI","25/10/2024","1127955147");
INSERT INTO `asistencias` VALUES("491","Gallego Giraldo Marlon","MATEMATICAS","SI","04/10/2024","1037974770");
INSERT INTO `asistencias` VALUES("492","Gallego Giraldo Marlon","EMPRENDIMIENTO","SI","04/10/2024","1037974770");
INSERT INTO `asistencias` VALUES("493","Gallego Giraldo Marlon","ED. FISICA","SI","10/10/2024","1037974770");
INSERT INTO `asistencias` VALUES("494","Gallego Giraldo Marlon","MATEMATICAS","SI","11/10/2024","1037974770");
INSERT INTO `asistencias` VALUES("495","Gallego Giraldo Marlon","EMPRENDIMIENTO","SI","11/10/2024","1037974770");
INSERT INTO `asistencias` VALUES("496","Gallego Giraldo Marlon","FISICA (MATEMÁTICA)","SI","14/10/2024","1037974770");
INSERT INTO `asistencias` VALUES("497","Gallego Giraldo Marlon","ARTISTICA","SI","14/10/2024","1037974770");
INSERT INTO `asistencias` VALUES("498","Gallego Giraldo Marlon","ED. FISICA","SI","17/10/2024","1037974770");
INSERT INTO `asistencias` VALUES("499","Gallego Giraldo Marlon","MATEMATICAS","SI","18/10/2024","1037974770");
INSERT INTO `asistencias` VALUES("500","Gallego Giraldo Marlon","EMPRENDIMIENTO","SI","18/10/2024","1037974770");
INSERT INTO `asistencias` VALUES("501","Gallego Giraldo Marlon","MATEMATICAS","SI","25/10/2024","1037974770");
INSERT INTO `asistencias` VALUES("502","Gallego Giraldo Marlon","EMPRENDIMIENTO","SI","25/10/2024","1037974770");
INSERT INTO `asistencias` VALUES("503","Garcia Navarro Sebastian Camilo","MATEMATICAS","SI","04/10/2024","1131284071");
INSERT INTO `asistencias` VALUES("504","Garcia Navarro Sebastian Camilo","EMPRENDIMIENTO","SI","04/10/2024","1131284071");
INSERT INTO `asistencias` VALUES("505","Garcia Navarro Sebastian Camilo","ED. FISICA","SI","10/10/2024","1131284071");
INSERT INTO `asistencias` VALUES("506","Garcia Navarro Sebastian Camilo","MATEMATICAS","SI","11/10/2024","1131284071");
INSERT INTO `asistencias` VALUES("507","Garcia Navarro Sebastian Camilo","EMPRENDIMIENTO","SI","11/10/2024","1131284071");
INSERT INTO `asistencias` VALUES("508","Garcia Navarro Sebastian Camilo","FISICA (MATEMÁTICA)","nr","14/10/2024","1131284071");
INSERT INTO `asistencias` VALUES("509","Garcia Navarro Sebastian Camilo","ARTISTICA","nr","14/10/2024","1131284071");
INSERT INTO `asistencias` VALUES("510","Garcia Navarro Sebastian Camilo","ED. FISICA","SI","17/10/2024","1131284071");
INSERT INTO `asistencias` VALUES("511","Garcia Navarro Sebastian Camilo","MATEMATICAS","SI","18/10/2024","1131284071");
INSERT INTO `asistencias` VALUES("512","Garcia Navarro Sebastian Camilo","EMPRENDIMIENTO","SI","18/10/2024","1131284071");
INSERT INTO `asistencias` VALUES("513","Garcia Navarro Sebastian Camilo","MATEMATICAS","SI","25/10/2024","1131284071");
INSERT INTO `asistencias` VALUES("514","Garcia Navarro Sebastian Camilo","EMPRENDIMIENTO","SI","25/10/2024","1131284071");
INSERT INTO `asistencias` VALUES("515","Giraldo Mazo Sara Paulina ","MATEMATICAS","SI","04/10/2024","135937935");
INSERT INTO `asistencias` VALUES("516","Giraldo Mazo Sara Paulina ","EMPRENDIMIENTO","SI","04/10/2024","135937935");
INSERT INTO `asistencias` VALUES("517","Giraldo Mazo Sara Paulina ","ED. FISICA","SI","10/10/2024","135937935");
INSERT INTO `asistencias` VALUES("518","Giraldo Mazo Sara Paulina ","MATEMATICAS","NO","11/10/2024","135937935");
INSERT INTO `asistencias` VALUES("519","Giraldo Mazo Sara Paulina ","EMPRENDIMIENTO","NO","11/10/2024","135937935");
INSERT INTO `asistencias` VALUES("520","Giraldo Mazo Sara Paulina ","FISICA (MATEMÁTICA)","nr","14/10/2024","135937935");
INSERT INTO `asistencias` VALUES("521","Giraldo Mazo Sara Paulina ","ARTISTICA","nr","14/10/2024","135937935");
INSERT INTO `asistencias` VALUES("522","Giraldo Mazo Sara Paulina ","ED. FISICA","NO","17/10/2024","135937935");
INSERT INTO `asistencias` VALUES("523","Giraldo Mazo Sara Paulina ","MATEMATICAS","SI","18/10/2024","135937935");
INSERT INTO `asistencias` VALUES("524","Giraldo Mazo Sara Paulina ","EMPRENDIMIENTO","SI","18/10/2024","135937935");
INSERT INTO `asistencias` VALUES("525","Giraldo Mazo Sara Paulina ","MATEMATICAS","SI","25/10/2024","135937935");
INSERT INTO `asistencias` VALUES("526","Giraldo Mazo Sara Paulina ","EMPRENDIMIENTO","SI","25/10/2024","135937935");
INSERT INTO `asistencias` VALUES("527","Gomez Pamplona Julieta","MATEMATICAS","SI","04/10/2024","1037974896");
INSERT INTO `asistencias` VALUES("528","Gomez Pamplona Julieta","EMPRENDIMIENTO","SI","04/10/2024","1037974896");
INSERT INTO `asistencias` VALUES("529","Gomez Pamplona Julieta","ED. FISICA","SI","10/10/2024","1037974896");
INSERT INTO `asistencias` VALUES("530","Gomez Pamplona Julieta","MATEMATICAS","SI","11/10/2024","1037974896");
INSERT INTO `asistencias` VALUES("531","Gomez Pamplona Julieta","EMPRENDIMIENTO","SI","11/10/2024","1037974896");
INSERT INTO `asistencias` VALUES("532","Gomez Pamplona Julieta","FISICA (MATEMÁTICA)","SI","14/10/2024","1037974896");
INSERT INTO `asistencias` VALUES("533","Gomez Pamplona Julieta","ARTISTICA","SI","14/10/2024","1037974896");
INSERT INTO `asistencias` VALUES("534","Gomez Pamplona Julieta","ED. FISICA","SI","17/10/2024","1037974896");
INSERT INTO `asistencias` VALUES("535","Gomez Pamplona Julieta","MATEMATICAS","SI","18/10/2024","1037974896");
INSERT INTO `asistencias` VALUES("536","Gomez Pamplona Julieta","EMPRENDIMIENTO","SI","18/10/2024","1037974896");
INSERT INTO `asistencias` VALUES("537","Gomez Pamplona Julieta","MATEMATICAS","SI","25/10/2024","1037974896");
INSERT INTO `asistencias` VALUES("538","Gomez Pamplona Julieta","EMPRENDIMIENTO","SI","25/10/2024","1037974896");
INSERT INTO `asistencias` VALUES("539","Gonzalez Martinez Salome","MATEMATICAS","SI","04/10/2024","1022006639");
INSERT INTO `asistencias` VALUES("540","Gonzalez Martinez Salome","EMPRENDIMIENTO","SI","04/10/2024","1022006639");
INSERT INTO `asistencias` VALUES("541","Gonzalez Martinez Salome","ED. FISICA","P","10/10/2024","1022006639");
INSERT INTO `asistencias` VALUES("542","Gonzalez Martinez Salome","MATEMATICAS","P","11/10/2024","1022006639");
INSERT INTO `asistencias` VALUES("543","Gonzalez Martinez Salome","EMPRENDIMIENTO","P","11/10/2024","1022006639");
INSERT INTO `asistencias` VALUES("544","Gonzalez Martinez Salome","FISICA (MATEMÁTICA)","SI","14/10/2024","1022006639");
INSERT INTO `asistencias` VALUES("545","Gonzalez Martinez Salome","ARTISTICA","SI","14/10/2024","1022006639");
INSERT INTO `asistencias` VALUES("546","Gonzalez Martinez Salome","ED. FISICA","SI","17/10/2024","1022006639");
INSERT INTO `asistencias` VALUES("547","Gonzalez Martinez Salome","MATEMATICAS","SI","18/10/2024","1022006639");
INSERT INTO `asistencias` VALUES("548","Gonzalez Martinez Salome","EMPRENDIMIENTO","SI","18/10/2024","1022006639");
INSERT INTO `asistencias` VALUES("549","Gonzalez Martinez Salome","MATEMATICAS","SI","25/10/2024","1022006639");
INSERT INTO `asistencias` VALUES("550","Gonzalez Martinez Salome","EMPRENDIMIENTO","SI","25/10/2024","1022006639");
INSERT INTO `asistencias` VALUES("551","Valencia  Vasquez Anderson ","MATEMATICAS","SI","04/10/2024","1045396829");
INSERT INTO `asistencias` VALUES("552","Valencia  Vasquez Anderson ","EMPRENDIMIENTO","SI","04/10/2024","1045396829");
INSERT INTO `asistencias` VALUES("553","Valencia  Vasquez Anderson ","ED. FISICA","SI","10/10/2024","1045396829");
INSERT INTO `asistencias` VALUES("554","Valencia  Vasquez Anderson ","MATEMATICAS","SI","11/10/2024","1045396829");
INSERT INTO `asistencias` VALUES("555","Valencia  Vasquez Anderson ","EMPRENDIMIENTO","SI","11/10/2024","1045396829");
INSERT INTO `asistencias` VALUES("556","Valencia  Vasquez Anderson ","FISICA (MATEMÁTICA)","SI","14/10/2024","1045396829");
INSERT INTO `asistencias` VALUES("557","Valencia  Vasquez Anderson ","ARTISTICA","SI","14/10/2024","1045396829");
INSERT INTO `asistencias` VALUES("558","Valencia  Vasquez Anderson ","ED. FISICA","SI","17/10/2024","1045396829");
INSERT INTO `asistencias` VALUES("559","Valencia  Vasquez Anderson ","MATEMATICAS","SI","18/10/2024","1045396829");
INSERT INTO `asistencias` VALUES("560","Valencia  Vasquez Anderson ","EMPRENDIMIENTO","SI","18/10/2024","1045396829");
INSERT INTO `asistencias` VALUES("561","Valencia  Vasquez Anderson ","MATEMATICAS","SI","25/10/2024","1045396829");
INSERT INTO `asistencias` VALUES("562","Valencia  Vasquez Anderson ","EMPRENDIMIENTO","SI","25/10/2024","1045396829");
INSERT INTO `asistencias` VALUES("563","Zapata Amaya Manuel Salvador","MATEMATICAS","SI","04/10/2024","1045626153");
INSERT INTO `asistencias` VALUES("564","Zapata Amaya Manuel Salvador","EMPRENDIMIENTO","SI","04/10/2024","1045626153");
INSERT INTO `asistencias` VALUES("565","Zapata Amaya Manuel Salvador","ED. FISICA","SI","10/10/2024","1045626153");
INSERT INTO `asistencias` VALUES("566","Zapata Amaya Manuel Salvador","MATEMATICAS","SI","11/10/2024","1045626153");
INSERT INTO `asistencias` VALUES("567","Zapata Amaya Manuel Salvador","EMPRENDIMIENTO","SI","11/10/2024","1045626153");
INSERT INTO `asistencias` VALUES("568","Zapata Amaya Manuel Salvador","FISICA (MATEMÁTICA)","SI","14/10/2024","1045626153");
INSERT INTO `asistencias` VALUES("569","Zapata Amaya Manuel Salvador","ARTISTICA","SI","14/10/2024","1045626153");
INSERT INTO `asistencias` VALUES("570","Zapata Amaya Manuel Salvador","ED. FISICA","SI","17/10/2024","1045626153");
INSERT INTO `asistencias` VALUES("571","Zapata Amaya Manuel Salvador","MATEMATICAS","SI","18/10/2024","1045626153");
INSERT INTO `asistencias` VALUES("572","Zapata Amaya Manuel Salvador","EMPRENDIMIENTO","SI","18/10/2024","1045626153");
INSERT INTO `asistencias` VALUES("573","Zapata Amaya Manuel Salvador","MATEMATICAS","SI","25/10/2024","1045626153");
INSERT INTO `asistencias` VALUES("574","Zapata Amaya Manuel Salvador","EMPRENDIMIENTO","SI","25/10/2024","1045626153");
INSERT INTO `asistencias` VALUES("575","Duque Gil Julio Cesar","MATEMATICAS","NO","04/10/2024","1018236286");
INSERT INTO `asistencias` VALUES("576","Duque Gil Julio Cesar","EMPRENDIMIENTO","NO","04/10/2024","1018236286");
INSERT INTO `asistencias` VALUES("577","Duque Gil Julio Cesar","ED. FISICA","NO","10/10/2024","1018236286");
INSERT INTO `asistencias` VALUES("578","Duque Gil Julio Cesar","MATEMATICAS","NO","11/10/2024","1018236286");
INSERT INTO `asistencias` VALUES("579","Duque Gil Julio Cesar","EMPRENDIMIENTO","NO","11/10/2024","1018236286");
INSERT INTO `asistencias` VALUES("580","Duque Gil Julio Cesar","FISICA (MATEMÁTICA)","nr","14/10/2024","1018236286");
INSERT INTO `asistencias` VALUES("581","Duque Gil Julio Cesar","ARTISTICA","nr","14/10/2024","1018236286");
INSERT INTO `asistencias` VALUES("582","Duque Gil Julio Cesar","ED. FISICA","NO","17/10/2024","1018236286");
INSERT INTO `asistencias` VALUES("583","Duque Gil Julio Cesar","MATEMATICAS","NO","18/10/2024","1018236286");
INSERT INTO `asistencias` VALUES("584","Duque Gil Julio Cesar","EMPRENDIMIENTO","NO","18/10/2024","1018236286");
INSERT INTO `asistencias` VALUES("585","Duque Gil Julio Cesar","MATEMATICAS","no","25/10/2024","1018236286");
INSERT INTO `asistencias` VALUES("586","Duque Gil Julio Cesar","EMPRENDIMIENTO","no","25/10/2024","1018236286");
INSERT INTO `asistencias` VALUES("587","Garcia Navarro Sharith Michell","MATEMATICAS","SI","04/10/2024","1045231554");
INSERT INTO `asistencias` VALUES("588","Garcia Navarro Sharith Michell","EMPRENDIMIENTO","SI","04/10/2024","1045231554");
INSERT INTO `asistencias` VALUES("589","Garcia Navarro Sharith Michell","ED. FISICA","SI","10/10/2024","1045231554");
INSERT INTO `asistencias` VALUES("590","Garcia Navarro Sharith Michell","MATEMATICAS","SI","11/10/2024","1045231554");
INSERT INTO `asistencias` VALUES("591","Garcia Navarro Sharith Michell","EMPRENDIMIENTO","SI","11/10/2024","1045231554");
INSERT INTO `asistencias` VALUES("592","Garcia Navarro Sharith Michell","FISICA (MATEMÁTICA)","SI","14/10/2024","1045231554");
INSERT INTO `asistencias` VALUES("593","Garcia Navarro Sharith Michell","ARTISTICA","SI","14/10/2024","1045231554");
INSERT INTO `asistencias` VALUES("594","Garcia Navarro Sharith Michell","ED. FISICA","SI","17/10/2024","1045231554");
INSERT INTO `asistencias` VALUES("595","Garcia Navarro Sharith Michell","MATEMATICAS","SI","18/10/2024","1045231554");
INSERT INTO `asistencias` VALUES("596","Garcia Navarro Sharith Michell","EMPRENDIMIENTO","SI","18/10/2024","1045231554");
INSERT INTO `asistencias` VALUES("597","Garcia Navarro Sharith Michell","MATEMATICAS","SI","25/10/2024","1045231554");
INSERT INTO `asistencias` VALUES("598","Garcia Navarro Sharith Michell","EMPRENDIMIENTO","SI","25/10/2024","1045231554");



DROP TABLE IF EXISTS `atajos`;

CREATE TABLE `atajos` (
  `id_atajo` int(11) NOT NULL AUTO_INCREMENT,
  `atajo` varchar(255) NOT NULL,
  `descripcion` varchar(255) NOT NULL,
  `programa` varchar(255) NOT NULL DEFAULT 'excel',
  PRIMARY KEY (`id_atajo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;




DROP TABLE IF EXISTS `auditoria_planeador_vallesol`;

CREATE TABLE `auditoria_planeador_vallesol` (
  `id_audtioria` int(12) NOT NULL AUTO_INCREMENT,
  `texo_sql` text NOT NULL,
  `fecha` timestamp NOT NULL DEFAULT current_timestamp(),
  `materia` varchar(255) NOT NULL,
  `grado` varchar(255) NOT NULL,
  `observaciones` text NOT NULL,
  PRIMARY KEY (`id_audtioria`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;




DROP TABLE IF EXISTS `categoria`;

CREATE TABLE `categoria` (
  `id_categoria` int(11) NOT NULL AUTO_INCREMENT,
  `nombre_categoria` varchar(120) NOT NULL,
  PRIMARY KEY (`id_categoria`),
  UNIQUE KEY `nombre_categoria` (`nombre_categoria`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;




DROP TABLE IF EXISTS `categoria_curso`;

CREATE TABLE `categoria_curso` (
  `id_categoria_curso` int(11) NOT NULL AUTO_INCREMENT,
  `nombre_categoria_curso` varchar(120) NOT NULL,
  `descripcion_categoria_curso` varchar(255) NOT NULL,
  `nivel_educativo` int(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id_categoria_curso`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

INSERT INTO `categoria_curso` VALUES("1","1","","1");
INSERT INTO `categoria_curso` VALUES("2","2","","1");
INSERT INTO `categoria_curso` VALUES("3","3","","1");
INSERT INTO `categoria_curso` VALUES("4","4","","1");
INSERT INTO `categoria_curso` VALUES("5","5","","1");
INSERT INTO `categoria_curso` VALUES("6","6","","1");
INSERT INTO `categoria_curso` VALUES("7","7","","1");
INSERT INTO `categoria_curso` VALUES("8","8","","1");
INSERT INTO `categoria_curso` VALUES("9","9","","1");
INSERT INTO `categoria_curso` VALUES("10","10","","1");
INSERT INTO `categoria_curso` VALUES("11","11","","1");
INSERT INTO `categoria_curso` VALUES("12","tecnico1","","1");
INSERT INTO `categoria_curso` VALUES("13","tecnico2","","1");
INSERT INTO `categoria_curso` VALUES("14","tecnico3","","1");
INSERT INTO `categoria_curso` VALUES("15","tecnico4","","1");
INSERT INTO `categoria_curso` VALUES("16","Transición","","1");



DROP TABLE IF EXISTS `citas`;

CREATE TABLE `citas` (
  `id_citas` int(11) NOT NULL AUTO_INCREMENT,
  `estudiante` int(12) NOT NULL,
  `fecha_inicio` date NOT NULL,
  `hora_inicio` time NOT NULL,
  `fecha_fin` varchar(255) NOT NULL,
  `hora_fin` varchar(255) NOT NULL,
  `docente` varchar(255) NOT NULL,
  `observaciones` varchar(255) NOT NULL,
  PRIMARY KEY (`id_citas`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;




DROP TABLE IF EXISTS `comentario`;

CREATE TABLE `comentario` (
  `id_comentario` int(11) NOT NULL,
  `id_entrada` int(11) NOT NULL,
  `fecha` timestamp NOT NULL DEFAULT current_timestamp(),
  `contenido` text NOT NULL,
  `usuario` varchar(12) NOT NULL,
  `estrellas` text NOT NULL,
  `rol_quien_comenta` varchar(255) NOT NULL,
  `estado` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;




DROP TABLE IF EXISTS `config`;

CREATE TABLE `config` (
  `id_config` int(11) NOT NULL AUTO_INCREMENT,
  `fecha_inicio` date NOT NULL,
  `hora_inicio` time NOT NULL,
  `ano` year(4) NOT NULL,
  `fecha_fin` date NOT NULL,
  `hora_fin` time NOT NULL,
  `docente` varchar(12) NOT NULL,
  `estado` int(1) NOT NULL,
  PRIMARY KEY (`id_config`)
) ENGINE=MyISAM AUTO_INCREMENT=12 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

INSERT INTO `config` VALUES("1","2018-11-05","04:47:10","2018","0000-00-00","00:00:00","1085290375","0");
INSERT INTO `config` VALUES("2","2018-11-05","04:47:17","2018","0000-00-00","00:00:00","1085290375","0");
INSERT INTO `config` VALUES("3","2018-11-05","04:47:19","2018","0000-00-00","00:00:00","1085290375","0");
INSERT INTO `config` VALUES("11","2019-02-17","05:14:09","2019","2019-02-18","05:53:58","1085290375","0");



DROP TABLE IF EXISTS `contenido`;

CREATE TABLE `contenido` (
  `contenido` varchar(255) NOT NULL,
  PRIMARY KEY (`contenido`),
  UNIQUE KEY `contenido` (`contenido`),
  UNIQUE KEY `contenido_2` (`contenido`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

INSERT INTO `contenido` VALUES("Acta");
INSERT INTO `contenido` VALUES("Actualización de datos personales");
INSERT INTO `contenido` VALUES("Actualizar datos en App Inventor");
INSERT INTO `contenido` VALUES("Alarma de Windows");
INSERT INTO `contenido` VALUES("Algoritmos");
INSERT INTO `contenido` VALUES("Análisis de datos");
INSERT INTO `contenido` VALUES("Análisis de datos SQL");
INSERT INTO `contenido` VALUES("Análisis de sentimientos");
INSERT INTO `contenido` VALUES("Apariencia");
INSERT INTO `contenido` VALUES("Arreglos");
INSERT INTO `contenido` VALUES("Búsquedas en Google");
INSERT INTO `contenido` VALUES("Calculadora de Windows");
INSERT INTO `contenido` VALUES("Calendario en Corel");
INSERT INTO `contenido` VALUES("Cambio de color de cabello");
INSERT INTO `contenido` VALUES("Cambio de color de ojos");
INSERT INTO `contenido` VALUES("Canción");
INSERT INTO `contenido` VALUES("Carpetas");
INSERT INTO `contenido` VALUES("Ciclo mientras");
INSERT INTO `contenido` VALUES("CINEMAGRAPH");
INSERT INTO `contenido` VALUES("Clasificación");
INSERT INTO `contenido` VALUES("Cluster");
INSERT INTO `contenido` VALUES("Columnas");
INSERT INTO `contenido` VALUES("Combinación de correspondencia con Google Docs");
INSERT INTO `contenido` VALUES("Computrabajo");
INSERT INTO `contenido` VALUES("Comunicación y Diseño");
INSERT INTO `contenido` VALUES("Conceptos de bases de datos");
INSERT INTO `contenido` VALUES("Conceptos de diseño gráfico");
INSERT INTO `contenido` VALUES("Conceptos generales de CMS");
INSERT INTO `contenido` VALUES("Condicional");
INSERT INTO `contenido` VALUES("Condicional Si");
INSERT INTO `contenido` VALUES("Configuración de privacidad en la nube");
INSERT INTO `contenido` VALUES("Consola de comandos de Windows");
INSERT INTO `contenido` VALUES("Constancia y Certificado");
INSERT INTO `contenido` VALUES("Consultas CRUD en MySQL");
INSERT INTO `contenido` VALUES("Corel Draw");
INSERT INTO `contenido` VALUES("Creación de base de datos");
INSERT INTO `contenido` VALUES("Creación de diseño de base de datos en MySQL");
INSERT INTO `contenido` VALUES("Creación de documentos de texto");
INSERT INTO `contenido` VALUES("Creación de logos");
INSERT INTO `contenido` VALUES("CRM Salesforce");
INSERT INTO `contenido` VALUES("Dark web");
INSERT INTO `contenido` VALUES("Deep web");
INSERT INTO `contenido` VALUES("Diseño de base de datos");
INSERT INTO `contenido` VALUES("Documentos de Texto");
INSERT INTO `contenido` VALUES("Ejercicio de nómina");
INSERT INTO `contenido` VALUES("Eliminar datos en App Inventor");
INSERT INTO `contenido` VALUES("Ergonomía");
INSERT INTO `contenido` VALUES("Estenografía");
INSERT INTO `contenido` VALUES("Estructura de HTML");
INSERT INTO `contenido` VALUES("Evernote");
INSERT INTO `contenido` VALUES("Examen");
INSERT INTO `contenido` VALUES("Examen CMS");
INSERT INTO `contenido` VALUES("Examen Photoshop");
INSERT INTO `contenido` VALUES("Excel intermedio");
INSERT INTO `contenido` VALUES("Filtrado DNS y MAC");
INSERT INTO `contenido` VALUES("Filtros");
INSERT INTO `contenido` VALUES("Filtros y tablas");
INSERT INTO `contenido` VALUES("Formas");
INSERT INTO `contenido` VALUES("Formato de celdas");
INSERT INTO `contenido` VALUES("Formato en el procesador de texto");
INSERT INTO `contenido` VALUES("Formularios de Excel");
INSERT INTO `contenido` VALUES("Formularios de Google Forms");
INSERT INTO `contenido` VALUES("Funciones de Excel");
INSERT INTO `contenido` VALUES("Funciones de fecha");
INSERT INTO `contenido` VALUES("Funciones de texto");
INSERT INTO `contenido` VALUES("Funciones estadísticas");
INSERT INTO `contenido` VALUES("Funciones financieras");
INSERT INTO `contenido` VALUES("Fundamentos de bases de datos");
INSERT INTO `contenido` VALUES("Fundamentos de hardware y software");
INSERT INTO `contenido` VALUES("Fundamentos de Windows");
INSERT INTO `contenido` VALUES("Fusión de impresión");
INSERT INTO `contenido` VALUES("Generación de informes SQL");
INSERT INTO `contenido` VALUES("Gestión de base de datos");
INSERT INTO `contenido` VALUES("GIF");
INSERT INTO `contenido` VALUES("Google Classroom");
INSERT INTO `contenido` VALUES("Google Forms");
INSERT INTO `contenido` VALUES("Google Presentation");
INSERT INTO `contenido` VALUES("Grabadora de Macros");
INSERT INTO `contenido` VALUES("Grabar CD");
INSERT INTO `contenido` VALUES("Gráficas");
INSERT INTO `contenido` VALUES("Hacker y cracker");
INSERT INTO `contenido` VALUES("Hardware y software");
INSERT INTO `contenido` VALUES("Herramienta ojos rojos");
INSERT INTO `contenido` VALUES("Insertar datos en App Inventor");
INSERT INTO `contenido` VALUES("Instalación de WordPress");
INSERT INTO `contenido` VALUES("Interlineado");
INSERT INTO `contenido` VALUES("Introducción");
INSERT INTO `contenido` VALUES("Introducción a App Inventor");
INSERT INTO `contenido` VALUES("Introducción a apps móviles");
INSERT INTO `contenido` VALUES("Introducción a Excel");
INSERT INTO `contenido` VALUES("Introducción a Photoshop");
INSERT INTO `contenido` VALUES("Inyección SQL");
INSERT INTO `contenido` VALUES("Kahoot");
INSERT INTO `contenido` VALUES("Keylogger");
INSERT INTO `contenido` VALUES("La Ley 1273 de 2009");
INSERT INTO `contenido` VALUES("Lazo poligonal");
INSERT INTO `contenido` VALUES("Levantamiento de requerimientos");
INSERT INTO `contenido` VALUES("Lienzo");
INSERT INTO `contenido` VALUES("Línea de tiempo");
INSERT INTO `contenido` VALUES("Líneas");
INSERT INTO `contenido` VALUES("LinkedIn");
INSERT INTO `contenido` VALUES("Login");
INSERT INTO `contenido` VALUES("Lupa de Windows");
INSERT INTO `contenido` VALUES("Macros");
INSERT INTO `contenido` VALUES("Mail merge");
INSERT INTO `contenido` VALUES("Mantenimiento correctivo de software");
INSERT INTO `contenido` VALUES("Mantenimiento preventivo");
INSERT INTO `contenido` VALUES("Maquetación de sitio web");
INSERT INTO `contenido` VALUES("Menú");
INSERT INTO `contenido` VALUES("Metodología de proyecto de analítica de datos");
INSERT INTO `contenido` VALUES("Métodos abreviados del teclado");
INSERT INTO `contenido` VALUES("Migración de proyecto web a hosting");
INSERT INTO `contenido` VALUES("Montaje de punto de red");
INSERT INTO `contenido` VALUES("Mouse y teclado");
INSERT INTO `contenido` VALUES("Objetos");
INSERT INTO `contenido` VALUES("Operaciones matemáticas");
INSERT INTO `contenido` VALUES("Páginas");
INSERT INTO `contenido` VALUES("Paint");
INSERT INTO `contenido` VALUES("Parche");
INSERT INTO `contenido` VALUES("Pincel corrector");
INSERT INTO `contenido` VALUES("Pincel de ojos rojos");
INSERT INTO `contenido` VALUES("Pinceles");
INSERT INTO `contenido` VALUES("Plan de asignatura");
INSERT INTO `contenido` VALUES("Plataforma HST");
INSERT INTO `contenido` VALUES("Plataforma virtual");
INSERT INTO `contenido` VALUES("Power BI");
INSERT INTO `contenido` VALUES("Power Query");
INSERT INTO `contenido` VALUES("Presentación del curso");
INSERT INTO `contenido` VALUES("Presentador de ideas");
INSERT INTO `contenido` VALUES("Procesador de texto");
INSERT INTO `contenido` VALUES("Producción documental");
INSERT INTO `contenido` VALUES("Proyecto final");
INSERT INTO `contenido` VALUES("Ransomware");
INSERT INTO `contenido` VALUES("Reglas de asociación");
INSERT INTO `contenido` VALUES("Repetidor");
INSERT INTO `contenido` VALUES("Resumen de informática básica");
INSERT INTO `contenido` VALUES("Seguridad en redes sociales");
INSERT INTO `contenido` VALUES("Socialización del plan de clase");
INSERT INTO `contenido` VALUES("Solver");
INSERT INTO `contenido` VALUES("SQL");
INSERT INTO `contenido` VALUES("SQL JOIN");
INSERT INTO `contenido` VALUES("Subir espacio en la nube");
INSERT INTO `contenido` VALUES("Tabla dinámica");
INSERT INTO `contenido` VALUES("Tablas");
INSERT INTO `contenido` VALUES("Taller");
INSERT INTO `contenido` VALUES("Taller de creación de carpetas");
INSERT INTO `contenido` VALUES("Tampón clonar");
INSERT INTO `contenido` VALUES("Teclado en pantalla");
INSERT INTO `contenido` VALUES("Template");
INSERT INTO `contenido` VALUES("Temporizador");
INSERT INTO `contenido` VALUES("Test de diagnóstico");
INSERT INTO `contenido` VALUES("Texto");
INSERT INTO `contenido` VALUES("Tipos de datos MySQL");
INSERT INTO `contenido` VALUES("Tor");
INSERT INTO `contenido` VALUES("Transcripción de audio");
INSERT INTO `contenido` VALUES("Transformación de fechas");
INSERT INTO `contenido` VALUES("Usuarios");
INSERT INTO `contenido` VALUES("Valoración");
INSERT INTO `contenido` VALUES("Variables");
INSERT INTO `contenido` VALUES("Visualización");
INSERT INTO `contenido` VALUES("Visualizaciones y formato");
INSERT INTO `contenido` VALUES("Weka");
INSERT INTO `contenido` VALUES("Wireshark");
INSERT INTO `contenido` VALUES("Wix");



DROP TABLE IF EXISTS `control_ingreso`;

CREATE TABLE `control_ingreso` (
  `fecha_ingreso` date NOT NULL,
  `grupo` text NOT NULL,
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `fecha_salida` date NOT NULL,
  `hora_ingreso` varchar(255) NOT NULL,
  `hora_salida` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;




DROP TABLE IF EXISTS `cuestionario`;

CREATE TABLE `cuestionario` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(255) NOT NULL,
  `fecha` date NOT NULL,
  `tipo_cuestionario` varchar(255) NOT NULL,
  `usuario` varchar(12) NOT NULL,
  `estrellas` text DEFAULT NULL,
  `visitas` bigint(20) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

INSERT INTO `cuestionario` VALUES("1","Examen de informática empresarial\nExamen de informática empresarial\nExamen de informática empresarial\n","2019-04-08","informÔö£├¡tica empresarial","1085290375",NULL,"24");



DROP TABLE IF EXISTS `curso`;

CREATE TABLE `curso` (
  `id_curso` int(11) NOT NULL AUTO_INCREMENT,
  `nombre_curso` varchar(90) NOT NULL,
  `id_grado` int(11) NOT NULL,
  PRIMARY KEY (`id_curso`),
  KEY `id_grado` (`id_grado`)
) ENGINE=MyISAM AUTO_INCREMENT=15 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;




DROP TABLE IF EXISTS `dba`;

CREATE TABLE `dba` (
  `id_dba` int(11) NOT NULL AUTO_INCREMENT,
  `nombre_dba` text NOT NULL,
  `descripcion_dba` text DEFAULT NULL,
  `id_estandar` int(11) NOT NULL,
  PRIMARY KEY (`id_dba`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `dba` VALUES("1","Análisis de sociedades antiguas","• Analiza como en las sociedades antiguas surgieron las primeras ciudades y el papel de la agricultura y el comercio para la expansión de estas.\n• Analiza las distintas formas de gobierno ejercidas en la antigüedad y las compara con el ejercicio del poder político en el mundo contemporáneo.\n• Analiza cómo en el escenario político democrático entran en juego intereses desde diferentes sectores sociales, políticos y económicos, los cuales deben ser dirimidos por los ciudadanos.","1");
INSERT INTO `dba` VALUES("2","Solución negociada de conflictos","Evalúa la importancia de la solución negociada de los conflictos armados para la búsqueda de la paz.","2");
INSERT INTO `dba` VALUES("3","Mentalidad emprendedora","Comprende el emprendimiento con sus mitos y realidades. Reconoce en su entorno cercano actividades contables y analiza la diferencia entre decisiones democráticas y autocráticas que pueden tomarse dentro de una empresa.","3");
INSERT INTO `dba` VALUES("4","Propiedades y estrategias numéricas","Utiliza las propiedades de los números enteros y racionales y las propiedades de sus operaciones para proponer estrategias y procedimientos de cálculo en la solución de problemas. Reconoce y establece diferentes relaciones (orden y equivalencia) entre elementos de diversos dominios numéricos y los utiliza para argumentar procedimientos sencillos.","4");
INSERT INTO `dba` VALUES("5","Resolución de problemas con números reales y expresiones polinómicas","Utiliza los números reales (sus operaciones, relaciones y propiedades) para resolver problemas con expresiones polinómicas.","5");
INSERT INTO `dba` VALUES("6","Reconocimiento del cuerpo y hábitos saludables","Identifica la buena alimentación y cómo debe nutrirse un deportista. Reflexiona sobre sus actos y las consecuencias de estos como oportunidad para una mejor convivencia escolar. Conoce los posibles riesgos a su integridad física al realizar una actividad física.","6");
INSERT INTO `dba` VALUES("7","Aplicación de pruebas físicas y criterios para el desarrollo personal","Comprende la importancia de aplicar los criterios de trabajo para el desarrollo de personalidad. Participa en la aplicación de pruebas físicas como herramienta para conocer sus posibilidades corporales y motrices.","7");
INSERT INTO `dba` VALUES("8","Innovaciones tecnológicas y normas de seguridad","Identifica innovaciones e inventos trascendentales para la sociedad; los ubica y explica en su contexto histórico. Analiza y aplica las normas de seguridad que se deben tener en cuenta para el uso de artefactos, productos y sistemas tecnológicos.","8");
INSERT INTO `dba` VALUES("9","Uso responsable de herramientas tecnológicas y hojas de cálculo","Hace uso de herramientas informáticas como las hojas de cálculo, en especial el programa de Microsoft Excel. Reconoce la importancia de las hojas de cálculo en el proceso contable de las empresas y en la elaboración de su presupuesto familiar. Interpreta el contenido de una factura de servicios públicos. Ejerce su papel de ciudadano responsable con el uso adecuado de sistemas tecnológicos.","9");
INSERT INTO `dba` VALUES("10","Comprensión del movimiento y leyes de Newton","Comprende que el reposo o el movimiento rectilíneo uniforme ocurre cuando las fuerzas aplicadas sobre el sistema se anulan entre sí, y que en presencia de fuerzas resultantes no nulas se producen cambios de velocidad. Predice el equilibrio de un cuerpo a partir del análisis de las fuerzas (primera ley de Newton). Estima cambios de velocidad mediante la relación entre fuerza y masa (segunda ley de Newton). Identifica las fuerzas de acción y reacción en diferentes situaciones de interacción (tercera ley de Newton). Evalúa prototipos sobre tipos de movimiento y analiza estructuras con posibles riesgos.","10");
INSERT INTO `dba` VALUES("11","Negociación y resolución de conflictos","Aporta en la negociación y resolución de conflictos mediante la identificación de valores humanos, la aplicación de derechos y deberes, y la promoción del bienestar colectivo.","11");
INSERT INTO `dba` VALUES("12","Rechazo a la discriminación y respeto a la diversidad","Expreso rechazo por todas las formas de discriminación o exclusión social y hago uso de los mecanismos democráticos para la superación de la discriminación y el respeto a la diversidad.","12");
INSERT INTO `dba` VALUES("13","Comprender y vivenciar la mentalidad emprendedora","Comprende, vivencia y valora la mentalidad emprendedora como estrategia para mejorar su calidad de vida, la de su familia e impactar positivamente su entorno lejano y cercano.","13");



DROP TABLE IF EXISTS `digitacion`;

CREATE TABLE `digitacion` (
  `id_dijitacion` int(11) NOT NULL AUTO_INCREMENT,
  `estudiante` varchar(255) NOT NULL,
  `fecha_registro` date NOT NULL,
  `hora_registro` time NOT NULL,
  `aciertos` int(11) NOT NULL DEFAULT 0,
  `errores` int(11) NOT NULL DEFAULT 0,
  `obseraciones` text NOT NULL,
  `ejercicio` int(1) NOT NULL,
  PRIMARY KEY (`id_dijitacion`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;




DROP TABLE IF EXISTS `digitacion_lecturas`;

CREATE TABLE `digitacion_lecturas` (
  `id_lectura` int(11) NOT NULL AUTO_INCREMENT,
  `lectura` text NOT NULL,
  `descripcion` text NOT NULL,
  `nombre_lectura` varchar(255) NOT NULL,
  PRIMARY KEY (`id_lectura`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;




DROP TABLE IF EXISTS `docente`;

CREATE TABLE `docente` (
  `id_docente` int(12) NOT NULL,
  `nombre_docente` varchar(255) NOT NULL,
  `apellido_docente` varchar(255) NOT NULL,
  `clave` text NOT NULL,
  `usuario` varchar(255) NOT NULL,
  `correo_docente` varchar(255) NOT NULL,
  `telefono` varchar(255) NOT NULL,
  PRIMARY KEY (`id_docente`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

INSERT INTO `docente` VALUES("1085290375","Andres","Paz","abc","apaz","","");



DROP TABLE IF EXISTS `edunotas`;

CREATE TABLE `edunotas` (
  `id_nota` int(12) NOT NULL AUTO_INCREMENT,
  `nota` text NOT NULL,
  `id_asignacion` int(255) NOT NULL,
  `fecha_nota` date NOT NULL,
  `hora_nota` time NOT NULL,
  `fijar` int(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id_nota`)
) ENGINE=InnoDB AUTO_INCREMENT=58 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `edunotas` VALUES("2","Quedaron en trabajar con linea de tiempo y manejo de gif","50","2020-03-26","00:45:03","0");
INSERT INTO `edunotas` VALUES("3","Quedaron los estudiantes en pasar un diseño de base de datos al computador usando mysql y phpmyadmin...","57","2020-03-26","00:57:52","0");
INSERT INTO `edunotas` VALUES("4","La estudiante Judy Vaneesa Lopez necesita de actividades sin computador ","58","2020-04-14","17:57:06","0");
INSERT INTO `edunotas` VALUES("5","Se cumple con el objetivo de clase y los estudiantes de lógica programación manifiestan que desean clases en vivo, debido a que se encuentran acostumbrados a la metodología presencial.","56","2020-04-14","21:28:42","0");
INSERT INTO `edunotas` VALUES("6","La estudiante Camila Timaran contaba con un número de contacto diferente por la cual solicita se agregue al grupo, y los estudiantes solicitan tiempos de entrega de clase.","52","2020-04-13","21:44:05","0");
INSERT INTO `edunotas` VALUES("7","Estudiantes como Sebastian Cifuentes y lesly Jhoana Paz que no habían podido asistir de forma presencial a clase solicitan acceso a registro en la plataforma virtual para continuar con la capacitación virtual.","50","2020-04-14","21:59:56","0");
INSERT INTO `edunotas` VALUES("8","Notas de clase base de datos(15/04/2020) *Teniendo en cuenta que la conectividad y capacidad de los computadores de cada estudiante se debe revisar los vídeos de clase subidos en el curso de la plataforma para que cada uno aprenda a su ritmo y en el horario de clase se debe ingresar a la sala en vivo para resolver dudas e inconvenientes *El docente subirá un vídeo de la explicación del día 15/04/2020 y el 22/04/2020 se resolveran dudas acerca del proceso en el horario de 6:30 a 8:30 p.m * el 16/04/2020 se resolveran dudas sobre los ejercicios de lógica de programación en el espacio en vivo de dicho curso en el horario de 6:30 pm a 8:30 p.m","57","2020-04-15","21:08:25","0");
INSERT INTO `edunotas` VALUES("9","JOSE LUIS RODRIGUEZ no se encuentra registrado en la plataforma virtual","58","2020-04-19","18:18:07","0");
INSERT INTO `edunotas` VALUES("10","Conclusiones de la sesión del día (18/04/2020) Deben ingresar durante la semana al curso de informática básica en virtual.hst.edu.co donde encontrarán el vídeo de clase y en su ritmo y en su tiempo podrán practicarlo Los días sábados de 11:30 a.m a 1:30 p.m estaré en sesión virtual en el espacio de vídeo llamada de la plataforma resolviendo dudas del vídeo no es obligatorio ingresar a la sesión en vivo pero si es necesario para despejar sus dudas. Para las personas que no puedan puedan estar en la sesión en vivo quedará también colgado el vídeo de resolución de dudas con los compañeros. Algunas estudiantes tienen inconveniente con conocimientos básicos como ortografía, conexión a Internet,rendimiento del ordenador que les dificulta la inscripción , búsqueda y sesiones en vivo del curso.","59","2020-04-20","12:49:47","0");
INSERT INTO `edunotas` VALUES("11","Los estudiantes tienen sesión en vivo en la cual evidencias el desequilibrio en recursos entre compañeros por lo cual consideran correcto acudir a la metodología de vídeos previos de clase y sesiones en vivo para responder preguntas ya que ellos están acostumbrados a las clases presenciales y conversar con sus compañeros.","56","2020-04-16","20:30:32","0");
INSERT INTO `edunotas` VALUES("12","La estudiante Zully Camila presenta un número diferente al que se registra razón por la cual el día 20/04/2020 se vincular con el proceso, generalmente los estudiantes jóvenes son los que tiene menos dificultad en adoptar el método virtual.","51","2020-04-17","20:30:02","0");
INSERT INTO `edunotas` VALUES("13","Amanda Díaz manifiesta tener inconvenientes laborales pero manifiesta establecer espacios para continuar la capacitación.","54","2020-04-20","13:21:56","0");
INSERT INTO `edunotas` VALUES("14","Digo Diaz, Esmeralda Toro y siria rodriguez no han ingresado al curso de ofimÔö£├¡tica","55","2020-04-23","14:58:36","0");
INSERT INTO `edunotas` VALUES("15","Camila Rodriguez sale del grupo","51","2020-04-24","23:33:22","0");
INSERT INTO `edunotas` VALUES("16","Amanda días manifiesta que debido a sus ocupaciones y falta de energía en su sector no ha podido completar las actividades pero manifiesta ponerse al día, de igual forma la estudiante Siria manifiesta que no cuenta con conexión a internet pero si con computador para que se le envíen las actividades vía Celular, Las estudiantes Maria Fernanda Yucta y Esmeralda Toro no responden a las actividades planteadas. De igual forma se implementa plan b teniendo en cuenta que la sesión del día el servidor de HST presenta una caída por lo cual no se puede acceder a la plataforma.","54","2020-04-25","10:48:15","0");
INSERT INTO `edunotas` VALUES("17","*Teniendo en cuenta el vídeo clase 2(asistente administrativo) deben desarrollar los ejercicios presentes en la plataforma con plazo hasta (2/05/2020) *Exclusivamente Las personas que no pudieron tomar captura de pantalla podrán enviar las fotos por otro medio, el resto de estudiantes por la plataforma por seguimiento y control *Habilitare un espacio Adicional para que puedan subir el ejercicio de mouse de la misma forma como lo hicieron con los de teclado teniendo en cuenta las recomendaciones del vídeo","59","2020-04-26","17:34:06","0");
INSERT INTO `edunotas` VALUES("18","Se presenta caída de la plataforma y a pesar que se implementa plan b para que los estudiantes puedan acceder manifiestan su incomodidad por no tener disponibilidad de los recursos, de igual forma se cumple con el objetivo de clase","60","2020-04-27","17:50:18","0");
INSERT INTO `edunotas` VALUES("19","Se desarrolla el examen 1, en el cual se realiza un llamado de atención por falta de responsabilidad de algunos estudiantes teniendo en cuenta que se anticipo con 8 días del examen y de igual forma el día anterior que el horario de evaluación sería de 8 a.m a 10 a.m , se presentan estudiantes a las 10 a.m solicitando que se les asigne los puntos para hacer el taller,","52","2020-04-27","17:52:01","0");
INSERT INTO `edunotas` VALUES("20","La mitad del grupo presenta inconvenientes de horario  debido a que manifiestan dificultades por razones laborales, de recursos y espacio.","60","2020-04-28","16:34:54","0");
INSERT INTO `edunotas` VALUES("21","La señora Pastora Helena manifiesta tener problemas de conectividad razón por la cual , decide retirarse","59","2020-05-04","10:08:20","0");
INSERT INTO `edunotas` VALUES("22","CELSO HUMBERTO QUIÑONES MURILLO menciona \"me es difícil porque mi pc se daño y por el movil yo uso datos WiFi solo cuando estoy en la casa Alexander trato de ayudarme pero igual el tiempo\". Se trata de brindar flexibilidad para que pueda entregar a su ritmo en su tiempo","60","2020-05-04","10:26:31","0");
INSERT INTO `edunotas` VALUES("23","La estudiante Maria Cristina Manifiesta tener problemas de conectividad razón por la cual no se ha podido conectar e inicia revisión de documentación y material de clase el día de hoy.","60","2020-05-04","13:23:12","0");
INSERT INTO `edunotas` VALUES("24","Se realiza una sesión de retroalimentación, Revisar correo de Mauricio con los talleres y revisar los taller de Yonni","56","2020-05-05","19:55:23","0");
INSERT INTO `edunotas` VALUES("25","Carolina Hoyos manifiesta \"[4:04 p.m., 5/8/2020] +57 312 7625990: Quería comentarle que mi computador tuvo un problema en la revisión que le mandé a realzar y aún no me lo entregan???? [4:05 p.m., 5/8/2020] +57 312 7625990: Quedaron en entregármelo entre mañana y el viernes\"","61","2020-08-05","19:15:32","0");
INSERT INTO `edunotas` VALUES("26","[6:55 p.m., 5/8/2020] Daniela Batidas: Buenas noches profe [6:56 p.m., 5/8/2020] Daniela Batidas: Para solicitarle permiso durante esta semana [6:56 p.m., 5/8/2020] Daniela Batidas: Debido a actividades de cierre , el permiso es para [7:10 p.m., 5/8/2020] Daniela Batidas: Daira Galindez [7:10 p.m., 5/8/2020] Daniela Batidas: Y Dania Bastida","63","2020-08-05","19:19:27","0");
INSERT INTO `edunotas` VALUES("27","Diana Vmiareal menciona \"Profesor buenas tardes. quería pedirle un plazo para la entrega de actividades desde las 7.. esto debido a que estoy pasando por una calamidad doméstica\"","62","2020-08-10","18:45:55","0");
INSERT INTO `edunotas` VALUES("28","Constanza Ponce comenta que necesita tiempo para poder completar sus actividades","62","2020-07-22","18:39:44","0");
INSERT INTO `edunotas` VALUES("29","Andrés Tovar se compromete a nivelar sus actividades","62","2020-07-23","18:48:11","0");
INSERT INTO `edunotas` VALUES("30","La estudiante Carolina Mercedes presenta dolor de cabeza y pide tener en cuenta para sus actividades","63","2020-07-18","18:49:42","0");
INSERT INTO `edunotas` VALUES("31","Carolina Hoyos manifiesta tener dificultades con su computadora y se encuentra en reparación.","61","2020-08-05","18:56:38","0");
INSERT INTO `edunotas` VALUES("32","Daniela Bastidas y  Daira Galindez Solicitan permiso debido a cierres de empresa","62","2020-08-05","18:59:12","0");
INSERT INTO `edunotas` VALUES("33","Cristina Polo tiene problemas de vista por lo cual no ha podido completar actividades","62","2020-08-03","19:01:50","0");
INSERT INTO `edunotas` VALUES("34","Fernanda Bolaños Junto a Harold se encuentra trabajando","63","2020-08-17","19:05:43","0");
INSERT INTO `edunotas` VALUES("35","Angela Chamorro manifiesta un poco de atraso en las actividades debido a bastante carga laboral","62","2020-07-13","19:07:43","0");
INSERT INTO `edunotas` VALUES("36","Diana Villareal presenta una calamidad domÔö£┬«stica","62","2020-08-10","19:13:24","0");
INSERT INTO `edunotas` VALUES("37","llamar asistencia de estudiantes","67","2021-02-08","22:19:11","0");
INSERT INTO `edunotas` VALUES("38","*Presentar Guía de crucigrama y recibir crucigrama *Se debe realizar la instalación de keylogger en el computador de Angela y Camila *Se debe realizar un resumen de la normatividad de seguridad informática *Se revisa claves de Camila y Angela *Explicar proceso de instalación de Keylogger * Desarrollar una guía paso a paso de instalación y uso de keylogger * Desarrollar plan de Deep Web","69","2021-03-05","18:45:18","0");
INSERT INTO `edunotas` VALUES("39","Se debe recordar al finalizar el proceso que el 8 de Marzo de 2021 se uso para base de datos, razón por la cual se necesita una clase adicional de base de datos","77","2021-03-10","08:56:34","0");
INSERT INTO `edunotas` VALUES("40","1) Pendiente confirmación de Lorena con cursos no completados de Q10 y asignaciones nuevas de q10 2)Pendiente registrar notas de Cristian Javier Ortega en desarrollo web brindada por Dario Alfaro 3) Hacer un Vídeo de Zuly Camila para Desarrollo Web Ejercito 7) Salud 8) preparar examen final de Jackeline 9) Diaglogflow preguntas","77","2021-03-14","22:12:20","0");
INSERT INTO `edunotas` VALUES("41","Pendiente realizar clase de brindar pautas para sobre como realizar un inventario y pendiente desarrollar una guía de solver y buscar objetivo","78","2021-04-29","10:10:05","0");
INSERT INTO `edunotas` VALUES("42","Se termino ejercicio de factura en excel queda pendiente funciones y formato condicional del cuadernillo de ejercicios.","80","2021-04-29","10:22:06","0");
INSERT INTO `edunotas` VALUES("43","Se termina hasta el ejercicio 9 y se debe explicar para la siguiente clase carpetas comprimidas y finalizar el cuadernillo de ejercicios para grabar el cd.","79","2021-04-29","10:23:58","0");
INSERT INTO `edunotas` VALUES("44","Queda pendiente subir ejercicios de diseño desarrollados en clase y recibir el taller de fotografía 3x4 y para la siguiente clase revisar que hace falta para prepararlos para el examen de avengers.","77","2021-04-29","10:41:42","0");
INSERT INTO `edunotas` VALUES("45","Evaluar actividades para subir a q10 y la prÔö£Ôöéxima clase explicar phising.","76","2021-04-29","10:47:36","0");
INSERT INTO `edunotas` VALUES("46","Entregar valoraciones as los estudiantes. Angela y zully tienen que nivelar actividades y para la próxima clase tiene pendiente clase de consola de comandos.","69","2021-04-29","10:50:49","0");
INSERT INTO `edunotas` VALUES("47","Hoy 30/04/2021 no hubo clase de dos horas","78","2021-04-30","15:31:33","0");
INSERT INTO `edunotas` VALUES("48","Hoy 30/04/2021 no hubo clase de dos horas	","80","2021-04-30","15:32:34","0");
INSERT INTO `edunotas` VALUES("49","Hoy 30/04/2021 no hubo clase de dos horas	","79","2021-04-30","15:32:56","0");
INSERT INTO `edunotas` VALUES("50","Imprimir I love pdf Descargar vídeos de youtobe Crear reunión de google meet Airmore true caller Control de versiones Personas en la nube Seguridad en facebook plataforma virtual hst Wix LinkedIn","79","2021-05-23","17:44:50","0");
INSERT INTO `edunotas` VALUES("51","Crear una reunión de meet Registro en la plataforma virtual Control de versiones Imágenes libres Educaplay Personas en la nube Seguridad y privacidad en las redes sociales Wix LinkedIn","79","2021-05-26","16:11:22","0");
INSERT INTO `edunotas` VALUES("52","Pendiente texto de de alineación , pendiente ejercicios de excel de la madre de ella y pendiente tabla de horario de word","80","2021-05-31","17:30:13","0");
INSERT INTO `edunotas` VALUES("53","Jessica Fernanda Uscategui Tobar tiene habilitado logueo con google en la plataforma","82","2021-06-07","19:58:08","0");
INSERT INTO `edunotas` VALUES("54","Explicar como subir a la plataforma actividades\n\ngaby tube verificar el nombre de usuario","82","2021-06-11","08:51:32","0");
INSERT INTO `edunotas` VALUES("55","Andres Felipe Rosero tiene pc coorporativo","83","2021-07-26","22:36:12","0");
INSERT INTO `edunotas` VALUES("56","*Programar con Consultores y asesores TIC a las 8 am de 08/09/2021 * Completar convenio de Alinos la Garza *Revisar seguimiento 07/09/2021 *Revisar Brayan Díaz si ya presentó convenio * Hacer firmar convenio de Nicolas Chana por parte de don Luis * Don Luis debe hacer firmar documentos de Luiseg y definir fecha de entrega (establecer actividades para los practicantes en mutis) *Pasar seguimiento individual que esta en el documento de word a guagua para cada estudiante *Registrar en guagua el seguimiento de Brayan Chasoy","85","2021-09-07","23:32:15","0");
INSERT INTO `edunotas` VALUES("57","Hacer actividad de la llamada","113","2024-08-19","21:56:59","0");



DROP TABLE IF EXISTS `eje_tematico`;

CREATE TABLE `eje_tematico` (
  `id_eje_tematico` int(11) NOT NULL AUTO_INCREMENT,
  `nombre_eje_tematico` varchar(255) NOT NULL,
  `descripcion_eje_tematico` text DEFAULT NULL,
  `id_dba` int(11) DEFAULT NULL,
  PRIMARY KEY (`id_eje_tematico`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `eje_tematico` VALUES("1","Democracia, Gobierno escolar, Legado cultural, social, económico","• Democracia\n• Gobierno escolar\n• Legado cultural, social, económico de las diferentes civilizaciones mundiales (Mesopotamia, Egipto, India, China, Grecia, Roma)","1");
INSERT INTO `eje_tematico` VALUES("2","Sociedad civil y conflicto armado","La sociedad civil y el conflicto armado en el contexto de la búsqueda de la paz.","2");
INSERT INTO `eje_tematico` VALUES("3","Emprendimiento","Emprendimiento, contabilidad y administración de empresas como herramientas para el desarrollo de proyectos sostenibles.","3");
INSERT INTO `eje_tematico` VALUES("4","Números y operaciones","Números naturales, operaciones con números naturales, combinación de suma y multiplicación, criterios de divisibilidad, mcm y mcd, resolución de problemas, múltiplos y divisores, descomposición en factores primos, potenciación y radicación.","4");
INSERT INTO `eje_tematico` VALUES("5","Números reales y expresiones algebraicas","Números reales, conjuntos numéricos, expresiones algebraicas, operaciones con polinomios, factorización, representación con la regleta, fracciones algebraicas, y operaciones con fracciones algebraicas.","5");
INSERT INTO `eje_tematico` VALUES("6","Terminología básica y hábitos saludables","Terminología básica, el cuerpo humano (sistema óseo o esquelético), cultura física, hábitos de higiene, alimentación, nutrición e hidratación, anamnesis (diagnóstico físico), calentamiento general, lesiones en el atletismo y primeros auxilios.","6");
INSERT INTO `eje_tematico` VALUES("7","Sistemas del cuerpo humano y fortalecimiento físico","Sistemas del cuerpo humano: respiratorio y circulatorio, articulaciones del cuerpo humano, fortalecimiento de la condición física, actividad física y estilos de vida saludable, manejo de lesiones deportivas.","7");
INSERT INTO `eje_tematico` VALUES("8","Historia, hardware, software y sistemas informáticos","1. ¿Qué es un computador?: utilidad del computador, proceso histórico de la computación, generaciones de las computadoras, clasificación de las computadoras. 2. Definición de hardware: estructura física, esquema funcional del computador, dispositivos de entrada y salida, dispositivos de almacenamiento, memoria principal, microprocesador y tarjeta principal, tarjetas de interfaz. 3. Definición de software: unidades de medida para la información, sistemas operativos, programas de aplicación, lenguajes de programación, programas de comunicaciones, aplicaciones comerciales.","8");
INSERT INTO `eje_tematico` VALUES("9","Microsoft Excel y máquinas monofuncionales","1. ¿Qué es Microsoft Excel?: descripción, características, y tipos de hojas de cálculo. Operaciones básicas como abrir, guardar, y editar libros de trabajo. Manejo de la barra de herramientas, menús, comandos, y rangos de celdas. Introducción de datos y edición. 2. Operaciones con celdas: insertar y eliminar celdas, filas, columnas, y hojas de cálculo. 3. Máquinas monofuncionales: definición, clasificación, representación gráfica, y evolución histórica.","9");
INSERT INTO `eje_tematico` VALUES("10","Fuerza y movimiento: Leyes de Newton","1. Concepto de fuerza y movimiento. 2. Leyes de Newton: Primera ley (equilibrio y movimiento rectilíneo uniforme), Segunda ley (fuerza y aceleración), Tercera ley (acción y reacción). 3. Aplicaciones: Evaluación de prototipos en clase y análisis de estructuras para identificar riesgos de desestabilidad.","10");
INSERT INTO `eje_tematico` VALUES("11","Resolución de conflictos y construcción de paz","3.1. Ventajas y desventajas de la aplicación de valores humanos. 3.2. Mediación de conflictos como estrategia para la convivencia pacífica. 3.3. Relación entre conflicto y construcción de paz en la sociedad.","11");
INSERT INTO `eje_tematico` VALUES("12","Discriminación y exclusión en la sociedad","4.1. Exclusión. 4.2. Discriminación. 4.3. Diferenciar exclusión y discriminación. 4.4. La Discriminación y la Exclusión HOY. 4.5. La Discriminación y la Exclusión son PROHIBIDAS. 4.6. Alternativas frente a la Discriminación y la Exclusión.","12");
INSERT INTO `eje_tematico` VALUES("17","Conceptos clave de emprendimiento","Caracterización económica. Aptitudes, habilidades y competencias. Riesgo. Empleabilidad.","13");



DROP TABLE IF EXISTS `entrada`;

CREATE TABLE `entrada` (
  `id` int(11) NOT NULL,
  `contenido` text CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `rol_quien_comenta` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `fecha` datetime DEFAULT NULL,
  `grupo` int(11) NOT NULL,
  `usuario` varchar(12) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `estrellas` text CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `visitas` text CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `suscribirse` text CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `estado` enum('Publicado','Desactivado') CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `permitir_comentarios` varchar(2) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT 'SI',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

INSERT INTO `entrada` VALUES("1","Foro actividad ","admin","2019-04-02 19:39:44","0","1085290375","","","","Publicado","SI");



DROP TABLE IF EXISTS `escala`;

CREATE TABLE `escala` (
  `id_escala` int(11) NOT NULL AUTO_INCREMENT,
  `valor` varchar(255) NOT NULL,
  `descripcion` text NOT NULL,
  PRIMARY KEY (`id_escala`),
  UNIQUE KEY `valor` (`valor`)
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

INSERT INTO `escala` VALUES("1","S","Siempre");
INSERT INTO `escala` VALUES("2","A","Aveces");
INSERT INTO `escala` VALUES("3","N","Nunca");
INSERT INTO `escala` VALUES("4","NM","Necesita Mejorar");
INSERT INTO `escala` VALUES("6","NS","No sabe");



DROP TABLE IF EXISTS `estandar`;

CREATE TABLE `estandar` (
  `id_estandar` int(11) NOT NULL AUTO_INCREMENT,
  `nombre_estandar` varchar(255) NOT NULL,
  `descripcion_estandar` text DEFAULT NULL,
  `grado` int(11) DEFAULT NULL,
  `id_periodo` int(11) DEFAULT NULL,
  `id_materia_oficial` int(11) DEFAULT NULL,
  PRIMARY KEY (`id_estandar`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `estandar` VALUES("1","Democracia y Seguridad","Reconoce algunos de los sistemas políticos que se establecieron en diferentes épocas y culturas y las principales ideas que buscan legitimarlos.","6","1","3");
INSERT INTO `estandar` VALUES("2","Democracia y Conflicto","Comprende que el ejercicio político es el resultado de esfuerzos por resolver conflictos y tensiones que surgen en las relaciones de poder entre los Estados y en el interior de ellos mismos.","10","1","2");
INSERT INTO `estandar` VALUES("3","Vivencia la mentalidad emprendedora","Comprende, vivencia y valora la mentalidad emprendedora como estrategia para mejorar su calidad de vida, la de su familia e impactar positivamente su entorno lejano y cercano.","6","1","9");
INSERT INTO `estandar` VALUES("4","Justifico procedimientos aritméticos","Justifico procedimientos aritméticos utilizando las relaciones y propiedades de las operaciones. Formulo y resuelvo problemas en situaciones aditivas y multiplicativas en diferentes contextos y dominios numéricos.","6","1","1");
INSERT INTO `estandar` VALUES("5","Construyo expresiones algebraicas y modelo variaciones","Construyo expresiones algebraicas equivalentes a una expresión algebraica dada. Modelo situaciones de variación con funciones polinómicas.","9","1","1");
INSERT INTO `estandar` VALUES("6","Capacidades físicas y diseño de movimientos corporales","Incrementa sus capacidades y posibilidades físicas en el contexto del respeto a la vida y del cuerpo humano. Calidad y eficiencia en el diseño de movimientos corporales. Muestra, reconoce y valora su potencial biológico y psicológico para realizar tareas motrices.","6","1","4");
INSERT INTO `estandar` VALUES("7","Desarrollo físico y respeto al cuerpo humano","Reconoce y valora su potencial biológico y psicológico para realizar tareas motrices. Incrementa las capacidades y posibilidades físicas en el contexto del respeto a la vida y del cuerpo humano cumpliendo normas de seguridad e higiene.","9","1","4");
INSERT INTO `estandar` VALUES("8","Reconocimiento de tecnología y transformaciones históricas","Reconoce los principios y conceptos propios de la tecnología, así como momentos de la historia que le han permitido al hombre transformar el entorno para resolver problemas y satisfacer necesidades. Relaciona el funcionamiento de artefactos, productos, procesos y sistemas tecnológicos con su utilización segura.","6","1","7");
INSERT INTO `estandar` VALUES("9","Reconocimiento de tecnología y su aplicación práctica","Reconoce principios y conceptos propios de la tecnología, así como momentos de la historia que le han permitido al hombre transformar el entorno para resolver problemas y satisfacer necesidades. Relaciona el funcionamiento de artefactos, productos, procesos y sistemas tecnológicos con su utilización segura. Adquiere habilidades y destrezas en el manejo de herramientas ofimáticas a través de aparatos tecnológicos.","9","1","7");
INSERT INTO `estandar` VALUES("10","Transformación y conservación de la energía en modelos físicos","Utiliza modelos físicos para explicar la transformación y conservación de la energía.","10","1","6");
INSERT INTO `estandar` VALUES("11","Principios y valores sociales en la convivencia","Actúa basada en principios y valores sociales conservados en los grupos donde interactúa.","6","1","8");
INSERT INTO `estandar` VALUES("12","Principios y valores sociales en la convivencia","Actúa basada en principios y valores sociales conservados en los grupos donde interactúa.","9","1","8");
INSERT INTO `estandar` VALUES("13","Mentalidad emprendedora","Vivencia la mentalidad emprendedora.","9","1","9");



DROP TABLE IF EXISTS `estrategias`;

CREATE TABLE `estrategias` (
  `estrategia` varchar(255) NOT NULL,
  `descripcion_estrategia` text DEFAULT NULL,
  PRIMARY KEY (`estrategia`),
  UNIQUE KEY `estrategia` (`estrategia`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

INSERT INTO `estrategias` VALUES("presentación a través de documento sobre los conceptos básicos de windows",NULL);
INSERT INTO `estrategias` VALUES("contextualización de los temas a tratar en la clase",NULL);
INSERT INTO `estrategias` VALUES("exploración de los componentes de mouse y teclado",NULL);
INSERT INTO `estrategias` VALUES("brindar guia de apoyo para ",NULL);
INSERT INTO `estrategias` VALUES("DISCUSIÓN DIRIGIDA","Consiste en discutir un tema, bajo la dirección del educador. Su principal uso consiste en dirigir el diálogo mediante preguntas específicas hacia un objetivo común. Después de la discusión se aceptarán las conclusiones de la mayoría por medio de un trabajo de colaboración intelectual.");
INSERT INTO `estrategias` VALUES("explicación de la prueba",NULL);
INSERT INTO `estrategias` VALUES("desarrollo de la prueba",NULL);
INSERT INTO `estrategias` VALUES("recepción de la prueba",NULL);
INSERT INTO `estrategias` VALUES("subrayar palabras claves",NULL);
INSERT INTO `estrategias` VALUES("subrayar colores",NULL);
INSERT INTO `estrategias` VALUES("resolver problemas usando el ciclo de repetición mientras",NULL);
INSERT INTO `estrategias` VALUES("resolver problemas de contexto",NULL);
INSERT INTO `estrategias` VALUES("usando una matriz digital identificar los componentes de una hoja de cálculo",NULL);
INSERT INTO `estrategias` VALUES("canción",NULL);
INSERT INTO `estrategias` VALUES("Resumen","");
INSERT INTO `estrategias` VALUES("memoria","..");
INSERT INTO `estrategias` VALUES("Paso a paso",NULL);
INSERT INTO `estrategias` VALUES("Exposición",NULL);
INSERT INTO `estrategias` VALUES("Asistencia guiada",NULL);
INSERT INTO `estrategias` VALUES("Lluvia de ideas","..");
INSERT INTO `estrategias` VALUES("Argumentación",".");
INSERT INTO `estrategias` VALUES("Foro","");
INSERT INTO `estrategias` VALUES("Aprendizaje basado en problemas","En la guía \"Aprendizaje basado en problema\" de la Universidad Politécnica de Madrid definen el ABP como \"una metodología centrada en el aprendizaje, en la investigación y reflexión que siguen los alumnos para llegar a una solución ante un problema planteado por el profesor\". El maestro actúa como guía de los alumnos a quienes les ha planteado una pregunta o problema, y estos de manera individual o en grupo deben encontrar la solución. En el portal Universia explican que \"los alumnos deben buscar la solución entendiendo los conceptos y aplicándolos al mismo\". Señalan que la solución de problemas se hace mediante la aplicación de conocimientos que son utilizados de manera crítica y no memorística. Cuando el alumno aplica a diferentes contextos la información que le ha sido proporcionada, a través de un proceso de reflexión y análisis estará desarrollando el razonamiento y la creatividad.");
INSERT INTO `estrategias` VALUES("Proyecto","");
INSERT INTO `estrategias` VALUES("Simulaciones","Se organiza para que los alumnos aprendan participando en una situación similar a la real conscientes que es una participación ficcional");
INSERT INTO `estrategias` VALUES("Mesa redonda","");
INSERT INTO `estrategias` VALUES("Investigaciones","");
INSERT INTO `estrategias` VALUES("Mapa conceptual","");
INSERT INTO `estrategias` VALUES("Exposición oral","implica una planificación y ejecución cuidadosas para transmitir información de manera efectiva.");
INSERT INTO `estrategias` VALUES("Repetición",NULL);
INSERT INTO `estrategias` VALUES("Enseñanza por exposición","Es la presentación expositiva de una serie de conceptos (Ausubel)");
INSERT INTO `estrategias` VALUES("Enseñanza por descubrimiento","");
INSERT INTO `estrategias` VALUES("Enseñanza directa","En ella el profesor guía la práctica de los alumnos");
INSERT INTO `estrategias` VALUES("Trabajo en grupo","Orientación de los docentes a los grupos para que las preguntas, reflexiones para el conocimiento sea conjunto");
INSERT INTO `estrategias` VALUES("El dialogo","");
INSERT INTO `estrategias` VALUES("ResoluciÔö£Ôöén de guÔö£┬ías","");
INSERT INTO `estrategias` VALUES("TALLER","Implica como su nombre lo dice, un lugar donde se trabaja y labora. Es una forma de enseñar y aprender mediante la realización de algo, es decir aprender haciendo. En esta estrategia predomina y se privilegia, el aprendizaje sobre la enseñanza. Se trata entonces de aprender haciendo, desarrollando habilidades donde los conocimientos se adquieren a través de una práctica concreta; ejecutando algo relacionado con el objetivo que se pretende alcanzar, en un contexto particular de aprendizaje. Es una metodología participativa en la que se enseña y se aprende a través de una tarea conjunta. Su metodología descansa en la actividad del estudiante y en la organización basada en pequeños grupos. La utilización de este método tiene como cometido dar respuesta a preguntas planteadas en las consignas de trabajo, teniendo en cuenta la opinión de todos los miembros del grupo, para llegar a una toma de decisiones colectiva. Desarrollando el taller en la práctica de conocimientos para despertar el interés en los estudiantes quienes observan la aplicación de los conocimientos.");
INSERT INTO `estrategias` VALUES("CLASES PRÁCTICAS","Se desarrollan actividades de aplicación de los conocimientos a situaciones concretas y de adquisición de habilidades básicas y procedimentales relacionadas con la materia objeto de estudio. Esta denominación engloba a diversos tipos de organización, como pueden ser las prácticas de laboratorio, prácticas de campo, clases de problemas, prácticas de informática, etc., puesto que, aunque presentan en algunos casos matices importantes, todas ellas tienen como característica común que su finalidad es mostrar a los estudiantes cómo deben actuar.");
INSERT INTO `estrategias` VALUES("RESOLUCIÓN DE EJERCICIOS Y PROBLEMAS","La estrategia didáctica de resolución de ejercicios y problemas, está fundamentada en ejercitar, ensayar y poner en práctica los conocimientos previos, en la que se solicita a los estudiantes que desarrollen soluciones adecuadas o correctas mediante la ejercitación de rutinas, la aplicación de fórmulas o algoritmos, la aplicación de procedimientos de transformación de la información disponible y la interpretación de los resultados. Es importante destacar que se despierta el interés de los estudiantes al observar las posibles aplicaciones prácticas del conocimiento, asimismo posibilita la participación de todos los alumnos, independientemente de su grado de competencia y pericia inicial para la tarea, brindando una gama amplia de actividades, con distintos tipos de exigencias y niveles de logros finales.");
INSERT INTO `estrategias` VALUES("APRENDIZAJE COOPERATIVO","El aprendizaje cooperativo es una forma de organización de la enseñanza en pequeños grupos, para potenciar el desarrollo de cada uno con la colaboración de los demás miembros del equipo. El aprendizaje \"entre iguales\", como también se le denomina, intensifica la interacción entre los estudiantes de un grupo, de manera que cada uno aprende el contenido asignado, y a su vez se aseguren que todos los miembros del grupo lo hacen, esta estrategia incide también en el desarrollo de todo un conjunto de habilidades socioafectivas e intelectuales, así como en las actitudes y valores en el proceso de formación de las nuevas generaciones.");
INSERT INTO `estrategias` VALUES("SIMULACIÓN PEDAGÓGICA","Es la representación de una situación de aprendizaje grupal cooperativa mediante la cual se reduce y simplifica en un modelo pedagógico la realidad, existen diferentes tipos de simulación didáctica, pero todas tienen en común ser alternativas dinámicas que implican la activa y emotiva participación del sujeto que aprende en una experiencia de aprendizaje que le va a proporcionar vivencias muy positivas en la construcción, bien de una noción teórica, bien de una habilidad relacionada con el saber hacer.");
INSERT INTO `estrategias` VALUES("La Enseñanza por descubrimiento","En este caso, el docente no expone los contenidos de un modo acabado y con valor total o completo, sino que es el propio alumno (aprendiente) el que adquiere una gran parte de los conocimientos por sí mismo, a través de su experiencia personal de descubrimiento o recepción de información.");
INSERT INTO `estrategias` VALUES("Enseñanza Programada","Enseñanza individualizada");
INSERT INTO `estrategias` VALUES("Enseñanza Magistral","El modelo de enseñanza expositivo (magistral) comporta cuatro fases generalmente:\n1. La descripción o explicación teórica de la noción (definición, regla, fórmula, etc.);\n2. La demostración práctica del profesor o profesora;\n3. Ejercicios de aplicación por parte de los alumnos y alumnas (en el cuaderno, las guías de completación, etc.);\n4. La aplicación de pruebas de evaluación (formativas o sumativas).");
INSERT INTO `estrategias` VALUES("Usando diseÔö£ÔûÆo institucional presentar los componentes de una hoja de cÔö£├¡lculo","");
INSERT INTO `estrategias` VALUES("aprendizaje basado en proyectos","El portal EdufÔö£Ôöérics lo define como ├ö├ç┬úuna metodologÔö£┬ía de aprendizaje en la que los estudiantes adquieren un rol activo y se favorece la motivaciÔö£Ôöén acadÔö£┬«mica├ö├ç├ÿ. En aulaPlaneta destacan que los alumnos se convierten en protagonistas de su aprendizaje y son los encargados de estructurar el trabajo para resolver la cuestiÔö£Ôöén que se ha planteado. AdemÔö£├¡s, desde este portal educativo seÔö£ÔûÆalan 10 pasos para aplicar esta metodologÔö£┬ía educativa: la selecciÔö£Ôöén del tema, la formaciÔö£Ôöén de equipos, la definiciÔö£Ôöén del reto final, la planificaciÔö£Ôöén, la investigaciÔö£Ôöén, el anÔö£├¡lisis, la elaboraciÔö£Ôöén del producto, la presentaciÔö£Ôöén, la respuesta colectiva y, por Ôö£Ôòæltimo, la evaluaciÔö£Ôöén.");
INSERT INTO `estrategias` VALUES("autoaprendizaje","Este modelo de enseÔö£ÔûÆanza pone el foco en el alumno, es decir, es mÔö£├¡s individualizado. La autogestiÔö£Ôöén lo que pretende es que el estudiante adquiera una mayor iniciativa y sea mÔö£├¡s independiente. De esta forma, participa mÔö£├¡s activamente en el proceso de aprendizaje adquiriendo continuamente nuevas capacidades y habilidades a travÔö£┬«s de su desempeÔö£ÔûÆo personal y profesional. La principal cuestiÔö£Ôöén que implica esta metodologÔö£┬ía es que el alumno debe estar preparado para asumir dicha responsabilidad. Es decir, tiene que haber una determinaciÔö£Ôöén, esfuerzo y motivaciÔö£Ôöén por parte del niÔö£ÔûÆo. En el autoaprendizaje el docente ejerce un papel de mediador o tutor que tiene que guiar al alumno facilitÔö£├¡ndole las herramientas o tÔö£┬«cnicas necesarias, pero sin entrar en el proceso de enseÔö£ÔûÆanza.\n\n\n\n");
INSERT INTO `estrategias` VALUES("Usando diseÔö£ÔûÆo instruccional presentar los componentes de una hoja de cu00e1lculo",NULL);
INSERT INTO `estrategias` VALUES("usando  diseÔö£ÔûÆo instruccional presentar los componentes de una hoja de cÔö£├¡lculo","");
INSERT INTO `estrategias` VALUES("examen",NULL);
INSERT INTO `estrategias` VALUES("practica",NULL);
INSERT INTO `estrategias` VALUES("EnseÔö£ÔûÆanza por exposiciÔö£Ôöén",NULL);
INSERT INTO `estrategias` VALUES("ExÔö£├¡menes de prÔö£├¡ctica libre","Repasar con ejercicios haciendo esfuerzo de memoria");
INSERT INTO `estrategias` VALUES("Correlaciones mnotÔö£┬«cnicas","correlacionar conocimientos ");
INSERT INTO `estrategias` VALUES("Repaso espaciado","Lo que aprendes si no repasas 90% de probabilidad que se pierda\n\nRepasas 24 horas 70%\n\nRepasas 1 semana 50%\n\nRepasas 1 mes 10%");
INSERT INTO `estrategias` VALUES("examen por relevos",NULL);
INSERT INTO `estrategias` VALUES("Flipped Classroom (Aula Invertida)","Una de las metodologías modernas que ha ganado más popularidad en los últimos años, el Flipped Classroom es un modelo pedagógico en el que los elementos tradicionales de la lección impartida por el profesor se invierten - los materiales educativos primarios son estudiados por los alumnos en casa y, luego, se trabajan en el aula. El principal objetivo de esta metodología es optimizar el tiempo en clase dedicándolo, por ejemplo, a atender las necesidades especiales de cada alumno, desarrollar proyectos cooperativos o trabajar por proyectos.");
INSERT INTO `estrategias` VALUES("Aprendizaje_","");
INSERT INTO `estrategias` VALUES("Aprendizaje Basado en Pensamiento","Aprendizaje Basado en pensamiento Busca Más allá del proceso de Memoria");
INSERT INTO `estrategias` VALUES("Asitencia guÔö£┬íada",NULL);
INSERT INTO `estrategias` VALUES("guia de apoyo",NULL);
INSERT INTO `estrategias` VALUES("GuÔö£┬ía",NULL);
INSERT INTO `estrategias` VALUES("Aprendizaje Basado en Proyectos (ABP)","Los estudiantes trabajan en proyectos a largo plazo que integran mÔö£Ôòæltiples disciplinas, resolviendo problemas reales y relevantes.");
INSERT INTO `estrategias` VALUES("Aprendizaje por Descubrimiento","Se motiva a los estudiantes a explorar, experimentar y descubrir por sÔö£┬í mismos conceptos y principios.");
INSERT INTO `estrategias` VALUES("Estudio de Caso","Los estudiantes analizan situaciones reales o ficticias para desarrollar habilidades de resoluciÔö£Ôöén de problemas y toma de decisiones.");
INSERT INTO `estrategias` VALUES("GamificaciÔö£Ôöén","Uso de elementos de juego (puntos, niveles, premios) para motivar y enganchar a los estudiantes en el proceso de aprendizaje.");
INSERT INTO `estrategias` VALUES("Aprendizaje Basado en Problemas (ABP)","Los estudiantes aprenden sobre un tema o concepto a travÔö£┬«s de la resoluciÔö£Ôöén de un problema complejo y abierto.");
INSERT INTO `estrategias` VALUES("Mapas Mentales y Conceptuales","Los estudiantes organizan y representan visualmente la informaciÔö£Ôöén para entender y recordar conceptos clave.");
INSERT INTO `estrategias` VALUES("SimulaciÔö£Ôöén y Juegos de Rol","Los estudiantes asumen roles especÔö£┬íficos en situaciones simuladas para explorar temas y problemas complejos.");
INSERT INTO `estrategias` VALUES("Aprendizaje Invertido (Flipped Classroom)","Los estudiantes primero se familiarizan con el contenido en casa y luego aplican lo aprendido en clase mediante actividades prÔö£├¡cticas.");
INSERT INTO `estrategias` VALUES("Debates y Discusiones","Los estudiantes discuten sobre un tema especÔö£┬ífico, argumentando diferentes puntos de vista.");
INSERT INTO `estrategias` VALUES("Aprendizaje Colaborativo","Los estudiantes trabajan juntos para resolver problemas, realizar tareas o completar proyectos, compartiendo conocimientos y habilidades.");
INSERT INTO `estrategias` VALUES("Aprendizaje Basado en Competencias","Enfocado en el desarrollo de competencias especÔö£┬íficas, donde el progreso del estudiante se mide por su capacidad para aplicar lo aprendido en contextos reales.");
INSERT INTO `estrategias` VALUES("Estaciones de Aprendizaje","Los estudiantes rotan por diferentes estaciones de trabajo donde realizan actividades diversas relacionadas con un mismo tema.");
INSERT INTO `estrategias` VALUES("Aprendizaje por IndagaciÔö£Ôöén","Los estudiantes hacen preguntas, investigan y exploran temas en profundidad para construir su propio entendimiento.");
INSERT INTO `estrategias` VALUES("Lectura CrÔö£┬ítica","Los estudiantes analizan y evalÔö£Ôòæan textos desde una perspectiva crÔö£┬ítica, cuestionando argumentos y identificando sesgos.");
INSERT INTO `estrategias` VALUES("Aprendizaje Experiencial","Los estudiantes aprenden a travÔö£┬«s de la experiencia directa, aplicando conocimientos en contextos prÔö£├¡cticos o simulados.");
INSERT INTO `estrategias` VALUES("MÔö£┬«todo SocrÔö£├¡tico","Utiliza preguntas abiertas y diÔö£├¡logo para que los estudiantes exploren ideas y lleguen a conclusiones a travÔö£┬«s de la reflexiÔö£Ôöén crÔö£┬ítica.");
INSERT INTO `estrategias` VALUES("EvaluaciÔö£Ôöén Formativa","Estrategia de evaluaciÔö£Ôöén continua que se utiliza para monitorear el aprendizaje de los estudiantes y proporcionar retroalimentaciÔö£Ôöén constante.");
INSERT INTO `estrategias` VALUES("Aprendizaje Auto-dirigido","Los estudiantes toman control de su propio proceso de aprendizaje, estableciendo objetivos, eligiendo mÔö£┬«todos y evaluando su progreso.");
INSERT INTO `estrategias` VALUES("Aprendizaje Ubicuo","Utiliza la tecnologÔö£┬ía para permitir que los estudiantes aprendan en cualquier momento y lugar, accediendo a recursos educativos digitales.");
INSERT INTO `estrategias` VALUES("Manipulativos matemáticos","Uso de objetos físicos o virtuales para representar conceptos matemáticos abstractos y facilitar la comprensión.");
INSERT INTO `estrategias` VALUES("Gamificación matemática","Incorporación de elementos de juegos como puntos, niveles y recompensas para motivar el aprendizaje de conceptos matemáticos.");
INSERT INTO `estrategias` VALUES("Flipped Classroom en matemáticas","Los estudiantes estudian la teoría en casa mediante videos o lecturas y utilizan el tiempo de clase para ejercicios prácticos y resolución de dudas.");
INSERT INTO `estrategias` VALUES("Método Singapur","Enfoque que desarrolla la comprensión matemática a través de lo concreto, pictórico y abstracto.");
INSERT INTO `estrategias` VALUES("Clubes de lectura","Grupos de estudiantes que leen y discuten libros, desarrollando habilidades de comprensión y análisis literario.");
INSERT INTO `estrategias` VALUES("Escritura creativa guiada","Ejercicios estructurados que fomentan la creación literaria mediante pautas y estímulos específicos.");
INSERT INTO `estrategias` VALUES("Análisis de textos multimodales","Estudio de textos que combinan diferentes modos de comunicación (visual, auditivo, lingüístico) para desarrollar alfabetización mediática.");
INSERT INTO `estrategias` VALUES("Debates estructurados","Discusiones formales sobre temas específicos que desarrollan la expresión oral, argumentación y pensamiento crítico.");
INSERT INTO `estrategias` VALUES("Teatro en el aula","Representación de obras literarias para profundizar en su comprensión y desarrollar habilidades comunicativas.");
INSERT INTO `estrategias` VALUES("Aprendizaje por indagación","Los estudiantes formulan preguntas, diseñan investigaciones y descubren conceptos científicos mediante la experimentación.");
INSERT INTO `estrategias` VALUES("Laboratorios virtuales","Simulaciones digitales que permiten realizar experimentos científicos cuando los recursos físicos son limitados.");
INSERT INTO `estrategias` VALUES("Proyectos de ciencia ciudadana","Participación en proyectos científicos reales que recolectan datos para investigaciones auténticas.");
INSERT INTO `estrategias` VALUES("Rutinas de pensamiento científico","Estructuras que guían a los estudiantes a observar, preguntar, predecir y explicar fenómenos naturales.");
INSERT INTO `estrategias` VALUES("Salidas de campo guiadas","Exploración del entorno natural con objetivos específicos de aprendizaje y recolección de datos.");
INSERT INTO `estrategias` VALUES("Análisis de fuentes primarias","Estudio directo de documentos históricos originales para desarrollar pensamiento histórico crítico.");
INSERT INTO `estrategias` VALUES("Juegos de rol históricos","Simulación de eventos o períodos históricos donde los estudiantes asumen roles específicos y resuelven problemas contextualizados.");
INSERT INTO `estrategias` VALUES("Líneas de tiempo interactivas","Representaciones visuales cronológicas que permiten comprender la secuencia y simultaneidad de eventos históricos.");
INSERT INTO `estrategias` VALUES("Estudios de caso geográficos","Análisis detallado de regiones específicas para comprender fenómenos geográficos, sociales y culturales.");
INSERT INTO `estrategias` VALUES("Proyectos de historia local","Investigaciones sobre la historia de la comunidad que conectan el aprendizaje con el contexto inmediato de los estudiantes.");
INSERT INTO `estrategias` VALUES("Aprendizaje cooperativo motriz","Actividades físicas que requieren colaboración grupal para alcanzar objetivos comunes.");
INSERT INTO `estrategias` VALUES("Enseñanza comprensiva de los deportes","Enfoque que prioriza la comprensión táctica por encima de la técnica en la enseñanza deportiva.");
INSERT INTO `estrategias` VALUES("Evaluación formativa motriz","Retroalimentación continua sobre el desempeño físico para identificar áreas de mejora y establecer metas personales.");
INSERT INTO `estrategias` VALUES("Gamificación deportiva","Incorporación de elementos lúdicos y sistema de puntos para motivar la participación en actividades físicas.");
INSERT INTO `estrategias` VALUES("Expresión corporal y danza","Actividades que desarrollan la conciencia corporal, creatividad y comunicación no verbal a través del movimiento.");
INSERT INTO `estrategias` VALUES("Aprendizaje basado en proyectos artísticos","Creación de obras que integran distintas técnicas y conocimientos para resolver un desafío creativo.");
INSERT INTO `estrategias` VALUES("Análisis visual crítico","Estudio estructurado de obras de arte para desarrollar alfabetización visual y pensamiento crítico.");
INSERT INTO `estrategias` VALUES("Portfolio artístico","Recopilación sistemática de trabajos que evidencia el proceso creativo y permite la autoevaluación.");
INSERT INTO `estrategias` VALUES("Visitas virtuales a museos","Exploración digital de colecciones artísticas que amplía el acceso a referentes culturales diversos.");
INSERT INTO `estrategias` VALUES("Técnica del artista invitado","Aprendizaje directo mediante la interacción con artistas profesionales que comparten sus procesos creativos.");
INSERT INTO `estrategias` VALUES("Metodología Kodály","Enfoque que utiliza el canto y la música folclórica como base para el desarrollo de la alfabetización musical.");
INSERT INTO `estrategias` VALUES("Método Orff","Aproximación que integra música, movimiento, drama y expresión verbal en un proceso de enseñanza activo.");
INSERT INTO `estrategias` VALUES("Creación colaborativa musical","Composición grupal que desarrolla habilidades de trabajo en equipo y expresión musical.");
INSERT INTO `estrategias` VALUES("Aprendizaje por imitación rítmica","Técnica basada en la repetición de patrones rítmicos cada vez más complejos.");
INSERT INTO `estrategias` VALUES("Improvisación guiada","Ejercicios estructurados que fomentan la creación espontánea dentro de parámetros musicales definidos.");
INSERT INTO `estrategias` VALUES("Programación por pares","Técnica donde dos estudiantes trabajan juntos en un mismo computador, alternando roles de programador y revisor.");
INSERT INTO `estrategias` VALUES("Resolución de problemas tecnológicos","Desafíos que requieren aplicar conocimientos tecnológicos para crear soluciones innovadoras.");
INSERT INTO `estrategias` VALUES("Proyectos maker","Actividades de construcción y creación que combinan tecnología, diseño y habilidades manuales.");
INSERT INTO `estrategias` VALUES("Aprendizaje basado en juegos digitales","Uso de videojuegos educativos o comerciales para desarrollar competencias específicas.");
INSERT INTO `estrategias` VALUES("Evaluación con rúbricas tecnológicas","Instrumentos que describen niveles de desempeño en competencias digitales específicas.");
INSERT INTO `estrategias` VALUES("Método comunicativo","Enfoque que prioriza la interacción significativa en el idioma meta para desarrollar competencia comunicativa.");
INSERT INTO `estrategias` VALUES("Aprendizaje basado en tareas","Actividades prácticas que requieren uso auténtico del idioma para resolver problemas específicos.");
INSERT INTO `estrategias` VALUES("Inmersión lingüística simulada","Creación de ambientes que maximizan la exposición al idioma meta dentro del aula.");
INSERT INTO `estrategias` VALUES("Intercambios virtuales","Comunicación con hablantes nativos mediante plataformas digitales para practicar el idioma en contextos auténticos.");
INSERT INTO `estrategias` VALUES("Storytelling interactivo","Narración de historias que involucra activamente a los estudiantes y contextualiza el vocabulario nuevo.");
INSERT INTO `estrategias` VALUES("Laboratorios guiados de indagación","Experimentos donde los estudiantes siguen procedimientos establecidos para descubrir principios químicos.");
INSERT INTO `estrategias` VALUES("Modelado molecular","Uso de representaciones físicas o digitales para visualizar y comprender estructuras químicas complejas.");
INSERT INTO `estrategias` VALUES("Aprendizaje basado en fenómenos","Estudio de reacciones químicas cotidianas como punto de partida para construir conocimiento teórico.");
INSERT INTO `estrategias` VALUES("Análisis de casos químicos","Estudio de situaciones reales donde la química juega un papel fundamental en la resolución de problemas.");
INSERT INTO `estrategias` VALUES("Proyectos de química verde","Investigaciones que aplican principios de sustentabilidad y reducción de impacto ambiental.");
INSERT INTO `estrategias` VALUES("Demostraciones interactivas","Experimentos realizados por el docente con participación activa de los estudiantes para ilustrar principios físicos.");
INSERT INTO `estrategias` VALUES("Resolución de problemas contextualizados","Ejercicios basados en situaciones reales que requieren aplicar leyes y principios de la física.");
INSERT INTO `estrategias` VALUES("Laboratorios virtuales de física","Simulaciones digitales que permiten manipular variables y observar fenómenos físicos complejos.");
INSERT INTO `estrategias` VALUES("Proyectos de ingeniería","Diseño y construcción de dispositivos que aplican principios físicos para resolver problemas prácticos.");
INSERT INTO `estrategias` VALUES("Análisis de video-movimiento","Estudio de grabaciones para analizar y cuantificar fenómenos mecánicos como trayectorias y velocidades.");
INSERT INTO `estrategias` VALUES("Disecciones virtuales","Simulaciones que permiten estudiar la anatomía de organismos sin necesidad de especímenes reales.");
INSERT INTO `estrategias` VALUES("Proyectos de biotecnología escolar","Actividades prácticas que aplican técnicas biotecnológicas adaptadas al contexto educativo.");
INSERT INTO `estrategias` VALUES("Observación sistemática","Registro detallado y análisis de características y comportamientos de seres vivos en su entorno natural.");
INSERT INTO `estrategias` VALUES("Modelado de sistemas biológicos","Creación de representaciones físicas o digitales que ilustran procesos y estructuras biológicas complejas.");
INSERT INTO `estrategias` VALUES("Estudios de campo ecológicos","Investigaciones en ecosistemas locales para comprender interacciones biológicas y factores ambientales.");
INSERT INTO `estrategias` VALUES("Proyectos de intervención ambiental","Iniciativas que identifican y abordan problemas ambientales locales con acciones concretas.");
INSERT INTO `estrategias` VALUES("Análisis de huella ecológica","Evaluación del impacto ambiental personal o institucional para promover cambios de comportamiento.");
INSERT INTO `estrategias` VALUES("Huertos escolares","Espacios de cultivo gestionados por estudiantes que sirven como laboratorios vivos para múltiples aprendizajes.");
INSERT INTO `estrategias` VALUES("Campañas de sensibilización","Diseño e implementación de acciones comunicativas que promueven conciencia ambiental en la comunidad.");
INSERT INTO `estrategias` VALUES("Itinerarios ambientales","Recorridos estructurados por entornos naturales con objetivos pedagógicos específicos.");
INSERT INTO `estrategias` VALUES("Comunidad de indagación","Diálogos estructurados donde los estudiantes exploran colectivamente preguntas filosóficas fundamentales.");
INSERT INTO `estrategias` VALUES("Análisis de dilemas éticos","Estudio de situaciones que presentan conflictos de valores para desarrollar razonamiento ético.");
INSERT INTO `estrategias` VALUES("Escritura filosófica","Elaboración de ensayos que articulan posiciones personales sobre cuestiones filosóficas fundamentales.");
INSERT INTO `estrategias` VALUES("Socratización","Método dialógico basado en preguntas que profundizan progresivamente en un tema filosófico.");
INSERT INTO `estrategias` VALUES("Filosofía con niños","Adaptación de la indagación filosófica a edades tempranas mediante preguntas generadoras y literatura infantil.");
INSERT INTO `estrategias` VALUES("Estudio comparado de religiones","Análisis de similitudes y diferencias entre distintas tradiciones religiosas con enfoque respetuoso.");
INSERT INTO `estrategias` VALUES("Dilemas morales progresivos","Presentación secuenciada de situaciones éticas cada vez más complejas para desarrollar juicio moral.");
INSERT INTO `estrategias` VALUES("Proyectos de servicio comunitario","Actividades de ayuda social que conectan valores éticos con acciones concretas.");
INSERT INTO `estrategias` VALUES("Mindfulness en el aula","Prácticas de atención plena que desarrollan autoconocimiento y regulación emocional.");
INSERT INTO `estrategias` VALUES("Círculos de diálogo","Espacios estructurados para compartir perspectivas personales sobre temas éticos en un ambiente seguro.");
INSERT INTO `estrategias` VALUES("Simulación de mercados","Actividades que recrean dinámicas económicas para comprender principios de oferta y demanda.");
INSERT INTO `estrategias` VALUES("Estudios de caso empresariales","Análisis de situaciones reales de negocios para aplicar conceptos económicos y financieros.");
INSERT INTO `estrategias` VALUES("Proyectos de emprendimiento escolar","Creación y gestión de pequeñas iniciativas empresariales por parte de los estudiantes.");
INSERT INTO `estrategias` VALUES("Debates económicos estructurados","Discusiones formales sobre políticas económicas que desarrollan argumentación basada en evidencia.");
INSERT INTO `estrategias` VALUES("Análisis de presupuestos personales","Ejercicios prácticos de planificación financiera adaptados a la realidad de los estudiantes.");
INSERT INTO `estrategias` VALUES("Aprendizaje-servicio","Metodología que combina el aprendizaje académico con el servicio comunitario para desarrollar competencias ciudadanas.");
INSERT INTO `estrategias` VALUES("Design thinking educativo","Proceso de diseño centrado en el usuario aplicado a la resolución de problemas pedagógicos.");
INSERT INTO `estrategias` VALUES("Rutinas de pensamiento visible","Estructuras que hacen visible el proceso cognitivo y promueven la metacognición.");
INSERT INTO `estrategias` VALUES("Aprendizaje basado en retos","Desafíos complejos y abiertos que requieren aplicar conocimientos de diversas disciplinas.");
INSERT INTO `estrategias` VALUES("Evaluación auténtica","Valoración del desempeño en tareas que reproducen situaciones reales y significativas.");
INSERT INTO `estrategias` VALUES("Trabajo por estaciones","Organización del aula en centros de actividad donde los estudiantes rotan y trabajan autónomamente.");
INSERT INTO `estrategias` VALUES("Metodología de proyectos integrados","Proyectos que conectan aprendizajes de diferentes asignaturas en torno a un tema común.");
INSERT INTO `estrategias` VALUES("Resolución colaborativa de problemas","Actividades en grupo donde los estudiantes comparten y discuten diferentes estrategias para resolver problemas matemáticos complejos.");
INSERT INTO `estrategias` VALUES("Matemáticas manipulativas","Uso de objetos físicos como bloques, regletas o ábacos para hacer tangibles los conceptos abstractos.");
INSERT INTO `estrategias` VALUES("Modelado matemático","Proceso de representar situaciones del mundo real mediante lenguaje matemático para analizar problemas complejos.");
INSERT INTO `estrategias` VALUES("Instrucción entre pares en matemáticas","Estudiantes explican conceptos a sus compañeros, reforzando su propio entendimiento y ofreciendo perspectivas alternativas.");
INSERT INTO `estrategias` VALUES("Historias matemáticas","Narrativas que contextualizan problemas matemáticos, haciéndolos más accesibles y significativos.");
INSERT INTO `estrategias` VALUES("Estaciones matemáticas rotativas","Organización del aula en centros temáticos donde los estudiantes practican diferentes habilidades matemáticas.");
INSERT INTO `estrategias` VALUES("Diarios matemáticos","Registros reflexivos donde los estudiantes documentan su pensamiento, estrategias y descubrimientos matemáticos.");
INSERT INTO `estrategias` VALUES("Análisis crítico del discurso","Examen detallado de textos para identificar perspectivas, sesgos e ideologías subyacentes.");
INSERT INTO `estrategias` VALUES("Talleres de escritura","Espacios estructurados donde los estudiantes desarrollan textos con acompañamiento docente y retroalimentación de pares.");
INSERT INTO `estrategias` VALUES("Método de los círculos literarios","Grupos pequeños que leen la misma obra asumiendo roles específicos para el análisis (director, conector, ilustrador, etc.).");
INSERT INTO `estrategias` VALUES("Documentación de proceso escritural","Registro sistemático de las etapas de planificación, borrador, revisión y edición de textos.");
INSERT INTO `estrategias` VALUES("Lectura dialógica","Método que promueve interacciones profundas con textos mediante diálogos guiados antes, durante y después de la lectura.");
INSERT INTO `estrategias` VALUES("Gamificación lectora","Incorporación de desafíos, niveles y recompensas para motivar la lectura y comprensión de textos.");
INSERT INTO `estrategias` VALUES("Podcast literarios","Creación de contenidos auditivos donde los estudiantes analizan, debaten o recrean obras literarias.");
INSERT INTO `estrategias` VALUES("Controversias sociocientíficas","Debate sobre cuestiones científicas con implicaciones sociales y éticas para desarrollar argumentación basada en evidencia.");
INSERT INTO `estrategias` VALUES("Modelado de conceptos científicos","Creación de representaciones físicas o digitales de fenómenos naturales para visualizar procesos complejos.");
INSERT INTO `estrategias` VALUES("Experimentos de demostración interactivos","Actividades prácticas donde el docente guía un experimento con participación activa de los estudiantes.");
INSERT INTO `estrategias` VALUES("Cuadernos de ciencias","Registros que documentan observaciones, preguntas, predicciones y conclusiones del proceso científico.");
INSERT INTO `estrategias` VALUES("Aprendizaje por diagnóstico","Método que plantea situaciones problemáticas donde los estudiantes deben identificar causas a partir de síntomas o efectos.");
INSERT INTO `estrategias` VALUES("Ferias de ciencias guiadas","Proyectos de investigación estructurados que culminan en presentaciones formales ante la comunidad educativa.");
INSERT INTO `estrategias` VALUES("Método POE (Predecir-Observar-Explicar)","Secuencia que contrasta predicciones iniciales con observaciones experimentales para generar explicaciones científicas.");
INSERT INTO `estrategias` VALUES("Historias orales","Recopilación y análisis de testimonios de personas que vivieron acontecimientos históricos relevantes.");
INSERT INTO `estrategias` VALUES("Simulaciones históricas","Recreación de situaciones históricas donde los estudiantes asumen roles y toman decisiones en contextos específicos.");
INSERT INTO `estrategias` VALUES("Debate historiográfico","Discusión sobre diferentes interpretaciones de eventos históricos basadas en distintas fuentes o perspectivas.");
INSERT INTO `estrategias` VALUES("Arqueología simulada","Actividades que reproducen métodos arqueológicos para inferir información histórica a partir de evidencias materiales.");
INSERT INTO `estrategias` VALUES("Análisis de cartografía histórica","Estudio comparativo de mapas de diferentes épocas para comprender cambios territoriales y geopolíticos.");
INSERT INTO `estrategias` VALUES("Biografías contextualizadas","Estudio de vidas individuales como ventanas para comprender procesos históricos más amplios.");
INSERT INTO `estrategias` VALUES("Microhistoria local","Investigaciones sobre aspectos cotidianos del pasado local que conectan la macrohistoria con experiencias cercanas.");
INSERT INTO `estrategias` VALUES("Evaluación entre pares motriz","Estudiantes observan y retroalimentan el desempeño de sus compañeros según criterios específicos.");
INSERT INTO `estrategias` VALUES("Desafíos físicos progresivos","Secuencia de retos corporales con dificultad creciente adaptados a diferentes niveles de habilidad.");
INSERT INTO `estrategias` VALUES("Estaciones de desarrollo motor","Circuitos con actividades específicas para trabajar diferentes habilidades motrices fundamentales.");
INSERT INTO `estrategias` VALUES("Proyectos de vida activa","Planificación e implementación de rutinas personalizadas de actividad física según intereses individuales.");
INSERT INTO `estrategias` VALUES("Diarios de actividad física","Registros reflexivos que documentan progresos, sensaciones y aprendizajes relacionados con la actividad física.");
INSERT INTO `estrategias` VALUES("Deportes alternativos inclusivos","Práctica de disciplinas no convencionales que facilitan la participación de estudiantes con diferentes habilidades.");
INSERT INTO `estrategias` VALUES("Coreografías colaborativas","Creación grupal de secuencias de movimiento que integran elementos rítmicos, expresivos y gimnásticos.");
INSERT INTO `estrategias` VALUES("Estudios visuales culturales","Análisis de imágenes de diversas culturas para comprender sus significados en diferentes contextos.");
INSERT INTO `estrategias` VALUES("Experimentación material sistemática","Exploración metódica de las posibilidades expresivas de diferentes materiales y técnicas artísticas.");
INSERT INTO `estrategias` VALUES("Crítica artística entre pares","Sesiones estructuradas donde los estudiantes comentan constructivamente los trabajos de sus compañeros.");
INSERT INTO `estrategias` VALUES("Documentación visual de procesos","Registro fotográfico o audiovisual de las etapas del proceso creativo para análisis y reflexión.");
INSERT INTO `estrategias` VALUES("Estudios de artistas contemporáneos","Investigación sobre creadores actuales como referentes para proyectos artísticos personales.");
INSERT INTO `estrategias` VALUES("Proyecto de intervención espacial","Transformación estética de espacios escolares aplicando principios de diseño y composición.");
INSERT INTO `estrategias` VALUES("Narrativas visuales secuenciales","Creación de historias contadas a través de imágenes consecutivas que desarrollan habilidades narrativas.");
INSERT INTO `estrategias` VALUES("Composición colaborativa digital","Creación musical utilizando aplicaciones o software que facilitan la producción sonora sin conocimientos técnicos previos.");
INSERT INTO `estrategias` VALUES("Método Dalcroze","Enfoque que utiliza el movimiento corporal como medio para interiorizar elementos rítmicos y musicales.");
INSERT INTO `estrategias` VALUES("Paisajes sonoros","Exploración, registro y recreación de entornos acústicos para desarrollar escucha consciente y expresión sonora.");
INSERT INTO `estrategias` VALUES("Ensambles inclusivos","Agrupaciones musicales que adaptan arreglos y roles para permitir la participación de estudiantes con diferentes habilidades.");
INSERT INTO `estrategias` VALUES("Análisis auditivo guiado","Escucha dirigida que focaliza la atención en elementos específicos de piezas musicales.");
INSERT INTO `estrategias` VALUES("Construcción de instrumentos alternativos","Fabricación de instrumentos con materiales reciclados que exploran principios acústicos básicos.");
INSERT INTO `estrategias` VALUES("Cartografía musical","Exploración de tradiciones musicales de diferentes regiones para comprender su contexto cultural y características.");
INSERT INTO `estrategias` VALUES("Pensamiento computacional desconectado","Actividades sin dispositivos electrónicos que desarrollan conceptos fundamentales de programación y lógica.");
INSERT INTO `estrategias` VALUES("Robótica educativa","Diseño, construcción y programación de robots para resolver problemas específicos.");
INSERT INTO `estrategias` VALUES("Narrativas digitales","Creación de historias que combinan diferentes medios digitales (texto, imagen, audio, video) con fines expresivos.");
INSERT INTO `estrategias` VALUES("Desarrollo de aplicaciones escolares","Diseño y programación de apps sencillas que resuelven necesidades específicas del entorno educativo.");
INSERT INTO `estrategias` VALUES("Análisis crítico de tecnologías","Estudio de impactos sociales, éticos y ambientales de innovaciones tecnológicas contemporáneas.");
INSERT INTO `estrategias` VALUES("Hackathons educativos","Eventos intensivos donde los estudiantes desarrollan soluciones tecnológicas a problemas específicos en tiempo limitado.");
INSERT INTO `estrategias` VALUES("Comunidades de práctica virtual","Grupos de aprendizaje en línea donde estudiantes comparten recursos y colaboran en proyectos tecnológicos.");
INSERT INTO `estrategias` VALUES("Tandem lingüístico","Parejas de estudiantes que practican mutuamente sus idiomas nativos, beneficiándose ambos del intercambio.");
INSERT INTO `estrategias` VALUES("Dramatización de situaciones cotidianas","Representación de escenarios reales que contextualizan el uso práctico del idioma.");
INSERT INTO `estrategias` VALUES("Método de respuesta física total","Técnica que asocia comandos verbales con movimientos corporales para facilitar la comprensión y retención.");
INSERT INTO `estrategias` VALUES("Aprendizaje léxico por campos semánticos","Organización del vocabulario en categorías conceptuales que facilitan su memorización y uso.");
INSERT INTO `estrategias` VALUES("Comprensión audiovisual scaffolded","Técnica que proporciona apoyos graduales para la comprensión de materiales auténticos en el idioma meta.");
INSERT INTO `estrategias` VALUES("Tertulias dialógicas en lengua extranjera","Conversaciones sobre temas de interés que priorizan la fluidez comunicativa sobre la corrección formal.");
INSERT INTO `estrategias` VALUES("Proyectos de traducción colaborativa","Actividades donde los estudiantes traducen textos auténticos aplicando estrategias de mediación lingüística.");
INSERT INTO `estrategias` VALUES("Cocina molecular educativa","Exploración de principios químicos a través de transformaciones culinarias controladas.");
INSERT INTO `estrategias` VALUES("Química cotidiana documentada","Identificación y análisis de reacciones químicas en productos y procesos domésticos habituales.");
INSERT INTO `estrategias` VALUES("Estudios de impacto químico ambiental","Investigaciones sobre efectos de sustancias específicas en ecosistemas locales.");
INSERT INTO `estrategias` VALUES("Modelado molecular computacional","Uso de software específico para visualizar y manipular estructuras moleculares tridimensionales.");
INSERT INTO `estrategias` VALUES("Historia contextualizada de la química","Estudio de descubrimientos químicos importantes en su contexto histórico, social y tecnológico.");
INSERT INTO `estrategias` VALUES("Resolución de incógnitas químicas","Desafíos donde los estudiantes deben identificar sustancias o reacciones mediante procedimientos analíticos.");
INSERT INTO `estrategias` VALUES("Design thinking aplicado a la química","Metodología que aplica principios de diseño para crear soluciones a problemas químicos específicos.");
INSERT INTO `estrategias` VALUES("Construcción de instrumentos de medición","Fabricación de dispositivos sencillos para cuantificar magnitudes físicas y comprender principios de medición.");
INSERT INTO `estrategias` VALUES("Física aplicada al deporte","Análisis de principios físicos involucrados en actividades deportivas para optimizar técnicas y rendimiento.");
INSERT INTO `estrategias` VALUES("Predicción-experimentación-explicación","Ciclo donde los estudiantes formulan hipótesis, diseñan pruebas y elaboran explicaciones sobre fenómenos físicos.");
INSERT INTO `estrategias` VALUES("Retos de ingenio físico","Desafíos que requieren aplicar leyes físicas para resolver problemas prácticos con materiales limitados.");
INSERT INTO `estrategias` VALUES("Análisis físico de máquinas cotidianas","Estudio de dispositivos habituales para identificar principios físicos en su funcionamiento.");
INSERT INTO `estrategias` VALUES("Modelización matemática de fenómenos","Representación mediante ecuaciones de relaciones entre variables físicas observadas experimentalmente.");
INSERT INTO `estrategias` VALUES("Física conceptual con analogías","Uso sistemático de comparaciones con experiencias familiares para comprender conceptos físicos abstractos.");
INSERT INTO `estrategias` VALUES("Análisis de ciclos biológicos","Estudio de procesos cíclicos naturales mediante observación sistemática y registro de cambios temporales.");
INSERT INTO `estrategias` VALUES("Microproyectos de biología sintética","Actividades simplificadas que introducen conceptos básicos de modificación biológica con fines específicos.");
INSERT INTO `estrategias` VALUES("Biología basada en problemas","Aprendizaje a partir de situaciones reales que requieren conocimientos biológicos para su comprensión y resolución.");
INSERT INTO `estrategias` VALUES("Dibujo naturalista científico","Representación detallada de especímenes que desarrolla observación sistemática y comprensión morfológica.");
INSERT INTO `estrategias` VALUES("Seguimiento fenológico","Registro periódico de eventos biológicos estacionales para comprender ciclos naturales y cambios ambientales.");
INSERT INTO `estrategias` VALUES("Reconstrucción filogenética simplificada","Actividades que permiten comprender relaciones evolutivas entre organismos mediante análisis de características.");
INSERT INTO `estrategias` VALUES("Bioinformática educativa","Uso de herramientas digitales adaptadas para analizar información biológica y comprender patrones genéticos básicos.");
INSERT INTO `estrategias` VALUES("Auditorías ambientales escolares","Evaluación sistemática de prácticas institucionales para identificar áreas de mejora en sostenibilidad.");
INSERT INTO `estrategias` VALUES("Recuperación de saberes ambientales tradicionales","Investigación sobre conocimientos ecológicos locales como fuente de prácticas sostenibles.");
INSERT INTO `estrategias` VALUES("Proyectos de restauración ecológica","Intervenciones en ecosistemas degradados que aplican principios de sucesión ecológica y conservación.");
INSERT INTO `estrategias` VALUES("Consumo crítico documentado","Análisis del ciclo de vida de productos cotidianos para tomar decisiones de consumo ambientalmente responsables.");
INSERT INTO `estrategias` VALUES("Estudio de controversias ambientales","Análisis de conflictos socioambientales desde múltiples perspectivas para desarrollar pensamiento sistémico.");
INSERT INTO `estrategias` VALUES("Ecología urbana aplicada","Investigación de interacciones ecológicas en entornos urbanos para proponer mejoras en habitabilidad y biodiversidad.");
INSERT INTO `estrategias` VALUES("Bioconstrucción educativa","Diseño y creación de estructuras escolares con técnicas y materiales de bajo impacto ambiental.");
INSERT INTO `estrategias` VALUES("Método socrático","Diálogo basado en preguntas que profundizan progresivamente en un tema y revelan contradicciones en el pensamiento.");
INSERT INTO `estrategias` VALUES("Filosofía para niños","Adaptación de la indagación filosófica a edades tempranas mediante preguntas generadoras y literatura infantil.");
INSERT INTO `estrategias` VALUES("Debates filosóficos estructurados","Discusiones formalizadas sobre cuestiones filosóficas fundamentales con roles y procedimientos definidos.");
INSERT INTO `estrategias` VALUES("Análisis conceptual","Examen detallado de conceptos filosóficos clave para clarificar significados y relaciones entre ideas.");
INSERT INTO `estrategias` VALUES("Experimentos mentales","Escenarios hipotéticos diseñados para explorar implicaciones lógicas de posiciones filosóficas.");
INSERT INTO `estrategias` VALUES("Filosofía aplicada a dilemas contemporáneos","Uso de perspectivas filosóficas clásicas para analizar problemas actuales complejos.");
INSERT INTO `estrategias` VALUES("Café filosófico","Conversaciones informales pero estructuradas sobre temas filosóficos en un ambiente relajado que favorece la participación.");
INSERT INTO `estrategias` VALUES("Análisis de narrativas morales","Estudio de relatos que plantean dilemas éticos para desarrollar razonamiento moral contextualizado.");
INSERT INTO `estrategias` VALUES("Cartografía de valores","Representación visual de principios éticos personales y colectivos que facilita su análisis y comparación.");
INSERT INTO `estrategias` VALUES("Diálogo interreligioso","Interacción respetuosa entre diferentes tradiciones religiosas para identificar valores compartidos y comprender diferencias.");
INSERT INTO `estrategias` VALUES("Proyectos de transformación ética","Iniciativas que aplican principios morales para mejorar aspectos específicos del entorno escolar o comunitario.");
INSERT INTO `estrategias` VALUES("Estudios de caso ético-profesionales","Análisis de situaciones reales que plantean dilemas morales en contextos laborales específicos.");
INSERT INTO `estrategias` VALUES("Meditación reflexiva","Prácticas de contemplación guiada que desarrollan autoconocimiento y conciencia de valores personales.");
INSERT INTO `estrategias` VALUES("Antropología simbólica educativa","Estudio de rituales, símbolos y prácticas culturales para comprender su significado y función social.");
INSERT INTO `estrategias` VALUES("Microemprendimientos escolares","Creación y gestión de pequeños negocios educativos que aplican principios económicos básicos.");
INSERT INTO `estrategias` VALUES("Análisis de publicidad y consumo","Estudio crítico de estrategias publicitarias y su influencia en decisiones económicas.");
INSERT INTO `estrategias` VALUES("Gamificación económica","Simulaciones y juegos que recrean principios económicos fundamentales en entornos controlados.");
INSERT INTO `estrategias` VALUES("Finanzas personales aplicadas","Actividades prácticas de planificación financiera adaptadas a la realidad económica de los estudiantes.");
INSERT INTO `estrategias` VALUES("Economía circular escolar","Proyectos que implementan sistemas de aprovechamiento de recursos basados en principios de sostenibilidad.");
INSERT INTO `estrategias` VALUES("Estudios de comercio justo","Investigaciones sobre cadenas de producción y comercialización con énfasis en condiciones laborales y ambientales.");
INSERT INTO `estrategias` VALUES("Análisis de datos económicos","Interpretación de indicadores y estadísticas económicas básicas para comprender realidades socioeconómicas.");
INSERT INTO `estrategias` VALUES("Performance interdisciplinar","Creaciones que combinan diferentes lenguajes artísticos para expresar ideas o conceptos complejos.");
INSERT INTO `estrategias` VALUES("Instalaciones artísticas conceptuales","Obras tridimensionales que transforman espacios y generan experiencias sensoriales significativas.");
INSERT INTO `estrategias` VALUES("Land art educativo","Intervenciones artísticas en entornos naturales que exploran la relación entre arte, naturaleza y sostenibilidad.");
INSERT INTO `estrategias` VALUES("Videoarte documental","Creación de piezas audiovisuales que documentan realidades sociales desde perspectivas artísticas.");
INSERT INTO `estrategias` VALUES("Arte digital interactivo","Obras que incorporan tecnología y requieren participación activa del espectador para completarse.");
INSERT INTO `estrategias` VALUES("Proyectos artísticos comunitarios","Creaciones colectivas que involucran a la comunidad educativa y abordan temáticas socialmente relevantes.");
INSERT INTO `estrategias` VALUES("Libro-arte","Creación de libros como objetos artísticos que exploran narrativas visuales y táctiles más allá del texto.");
INSERT INTO `estrategias` VALUES("Diarios de emociones","Registros sistemáticos que documentan experiencias emocionales para desarrollar autoconocimiento y regulación.");
INSERT INTO `estrategias` VALUES("Mindfulness educativo","Prácticas de atención plena adaptadas al contexto escolar que desarrollan conciencia emocional y concentración.");
INSERT INTO `estrategias` VALUES("Teatro del oprimido","Técnicas teatrales que abordan situaciones de conflicto y promueven la búsqueda colectiva de soluciones.");
INSERT INTO `estrategias` VALUES("Círculos restaurativos","Metodología que facilita la expresión emocional, resolución de conflictos y reconstrucción de relaciones dañadas.");
INSERT INTO `estrategias` VALUES("Proyectos de bienestar comunitario","Iniciativas que promueven entornos escolares emocionalmente saludables a través de acciones específicas.");
INSERT INTO `estrategias` VALUES("Coaching entre pares","Sistema donde estudiantes se acompañan mutuamente en procesos de desarrollo personal con técnicas específicas.");
INSERT INTO `estrategias` VALUES("Análisis de inteligencia emocional en literatura","Estudio de personajes literarios como modelos para comprender dinámicas emocionales complejas.");
INSERT INTO `estrategias` VALUES("Aprendizaje móvil","Uso de dispositivos móviles para acceder a recursos educativos y realizar actividades de aprendizaje en cualquier momento y lugar.");
INSERT INTO `estrategias` VALUES("Aprendizaje adaptativo","Uso de tecnologías que ajustan automáticamente contenidos y niveles de dificultad según el desempeño individual.");
INSERT INTO `estrategias` VALUES("Método de casos","Estudio de situaciones reales complejas que requieren análisis multidisciplinar y toma de decisiones fundamentadas.");



DROP TABLE IF EXISTS `evaluacion`;

CREATE TABLE `evaluacion` (
  `asignacion` varchar(255) NOT NULL,
  `respuestas` text NOT NULL,
  `session` int(11) NOT NULL,
  PRIMARY KEY (`asignacion`,`session`),
  KEY `session` (`session`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

INSERT INTO `evaluacion` VALUES("1","{\"3\":{\"S\":5,\"A\":\"0\",\"N\":\"0\",\"NM\":\"0\",\"NS\":1},\"4\":{\"S\":5,\"A\":\"0\",\"N\":\"0\",\"NM\":\"0\",\"NS\":1},\"5\":{\"S\":5,\"A\":\"0\",\"N\":\"0\",\"NM\":\"0\",\"NS\":1},\"7\":{\"S\":5,\"A\":\"0\",\"N\":\"0\",\"NM\":\"0\",\"NS\":1},\"8\":{\"S\":5,\"A\":\"0\",\"N\":\"0\",\"NM\":\"0\",\"NS\":1},\"9\":{\"S\":5,\"A\":\"0\",\"N\":\"0\",\"NM\":\"0\",\"NS\":1},\"11\":{\"S\":5,\"A\":\"0\",\"N\":\"0\",\"NM\":\"0\",\"NS\":1},\"12\":{\"S\":5,\"A\":\"0\",\"N\":\"0\",\"NM\":\"0\",\"NS\":1},\"13\":{\"S\":5,\"A\":\"0\",\"N\":\"0\",\"NM\":\"0\",\"NS\":1},\"14\":{\"S\":5,\"A\":\"0\",\"N\":\"0\",\"NM\":\"0\",\"NS\":1},\"15\":{\"S\":5,\"A\":\"0\",\"N\":\"0\",\"NM\":\"0\",\"NS\":1},\"16\":{\"S\":5,\"A\":\"0\",\"N\":\"0\",\"NM\":\"0\",\"NS\":1},\"17\":{\"S\":5,\"A\":\"0\",\"N\":\"0\",\"NM\":\"0\",\"NS\":1},\"41\":{\"S\":5,\"A\":\"0\",\"N\":\"0\",\"NM\":\"0\",\"NS\":1},\"40\":{\"S\":5,\"A\":\"0\",\"N\":\"0\",\"NM\":\"0\",\"NS\":1},\"44\":{\"S\":5,\"A\":\"0\",\"N\":\"0\",\"NM\":\"0\",\"NS\":1},\"18\":{\"S\":5,\"A\":\"0\",\"N\":\"0\",\"NM\":\"0\",\"NS\":1},\"19\":{\"S\":5,\"A\":\"0\",\"N\":\"0\",\"NM\":\"0\",\"NS\":1},\"20\":{\"S\":5,\"A\":\"0\",\"N\":\"0\",\"NM\":\"0\",\"NS\":1},\"21\":{\"S\":5,\"A\":\"0\",\"N\":\"0\",\"NM\":\"0\",\"NS\":1},\"22\":{\"S\":5,\"A\":\"0\",\"N\":\"0\",\"NM\":\"0\",\"NS\":1},\"23\":{\"S\":5,\"A\":\"0\",\"N\":\"0\",\"NM\":\"0\",\"NS\":1},\"24\":{\"S\":5,\"A\":\"0\",\"N\":\"0\",\"NM\":\"0\",\"NS\":1},\"25\":{\"S\":5,\"A\":\"0\",\"N\":\"0\",\"NM\":\"0\",\"NS\":1},\"43\":{\"S\":5,\"A\":\"0\",\"N\":\"0\",\"NM\":\"0\",\"NS\":1},\"26\":{\"S\":5,\"A\":\"0\",\"N\":\"0\",\"NM\":\"0\",\"NS\":1},\"27\":{\"S\":5,\"A\":\"0\",\"N\":\"0\",\"NM\":\"0\",\"NS\":1},\"28\":{\"S\":5,\"A\":\"0\",\"N\":\"0\",\"NM\":\"0\",\"NS\":1},\"29\":{\"S\":5,\"A\":\"0\",\"N\":\"0\",\"NM\":\"0\",\"NS\":1},\"30\":{\"S\":5,\"A\":\"0\",\"N\":\"0\",\"NM\":\"0\",\"NS\":1},\"31\":{\"S\":5,\"A\":\"0\",\"N\":\"0\",\"NM\":\"0\",\"NS\":1},\"2\":{\"S\":5,\"A\":\"0\",\"N\":\"0\",\"NM\":\"0\",\"NS\":1},\"10\":{\"S\":5,\"A\":\"0\",\"N\":\"0\",\"NM\":\"0\",\"NS\":1},\"32\":{\"S\":5,\"A\":\"0\",\"N\":\"0\",\"NM\":\"0\",\"NS\":1},\"33\":{\"S\":5,\"A\":\"0\",\"N\":\"0\",\"NM\":\"0\",\"NS\":1},\"34\":{\"S\":5,\"A\":\"0\",\"N\":\"0\",\"NM\":\"0\",\"NS\":1},\"35\":{\"S\":5,\"A\":\"0\",\"N\":\"0\",\"NM\":\"0\",\"NS\":1},\"36\":{\"S\":5,\"A\":\"0\",\"N\":\"0\",\"NM\":\"0\",\"NS\":1},\"1\":{\"S\":5,\"A\":\"0\",\"N\":\"0\",\"NM\":\"0\",\"NS\":1},\"6\":{\"S\":5,\"A\":\"0\",\"N\":\"0\",\"NM\":\"0\",\"NS\":1},\"45\":{\"S\":5,\"A\":\"0\",\"N\":\"0\",\"NM\":\"0\",\"NS\":1}}","4");
INSERT INTO `evaluacion` VALUES("1","{\"3\":{\"S\":\"0\",\"A\":\"0\",\"N\":\"0\",\"NM\":\"0\",\"NS\":\"0\"},\"4\":{\"S\":\"0\",\"A\":\"0\",\"N\":\"0\",\"NM\":\"0\",\"NS\":\"0\"},\"5\":{\"S\":\"0\",\"A\":\"0\",\"N\":\"0\",\"NM\":\"0\",\"NS\":\"0\"},\"7\":{\"S\":\"0\",\"A\":\"0\",\"N\":\"0\",\"NM\":\"0\",\"NS\":\"0\"},\"8\":{\"S\":\"0\",\"A\":\"0\",\"N\":\"0\",\"NM\":\"0\",\"NS\":\"0\"},\"9\":{\"S\":\"0\",\"A\":\"0\",\"N\":\"0\",\"NM\":\"0\",\"NS\":\"0\"},\"11\":{\"S\":\"0\",\"A\":\"0\",\"N\":\"0\",\"NM\":\"0\",\"NS\":\"0\"},\"12\":{\"S\":\"0\",\"A\":\"0\",\"N\":\"0\",\"NM\":\"0\",\"NS\":\"0\"},\"13\":{\"S\":\"0\",\"A\":\"0\",\"N\":\"0\",\"NM\":\"0\",\"NS\":\"0\"},\"14\":{\"S\":\"0\",\"A\":\"0\",\"N\":\"0\",\"NM\":\"0\",\"NS\":\"0\"},\"15\":{\"S\":\"0\",\"A\":\"0\",\"N\":\"0\",\"NM\":\"0\",\"NS\":\"0\"},\"16\":{\"S\":\"0\",\"A\":\"0\",\"N\":\"0\",\"NM\":\"0\",\"NS\":\"0\"},\"17\":{\"S\":\"0\",\"A\":\"0\",\"N\":\"0\",\"NM\":\"0\",\"NS\":\"0\"},\"41\":{\"S\":\"0\",\"A\":\"0\",\"N\":\"0\",\"NM\":\"0\",\"NS\":\"0\"},\"40\":{\"S\":\"0\",\"A\":\"0\",\"N\":\"0\",\"NM\":\"0\",\"NS\":\"0\"},\"44\":{\"S\":\"0\",\"A\":\"0\",\"N\":\"0\",\"NM\":\"0\",\"NS\":\"0\"},\"18\":{\"S\":\"0\",\"A\":\"0\",\"N\":\"0\",\"NM\":\"0\",\"NS\":\"0\"},\"19\":{\"S\":\"0\",\"A\":\"0\",\"N\":\"0\",\"NM\":\"0\",\"NS\":\"0\"},\"20\":{\"S\":\"0\",\"A\":\"0\",\"N\":\"0\",\"NM\":\"0\",\"NS\":\"0\"},\"21\":{\"S\":\"0\",\"A\":\"0\",\"N\":\"0\",\"NM\":\"0\",\"NS\":\"0\"},\"22\":{\"S\":\"0\",\"A\":\"0\",\"N\":\"0\",\"NM\":\"0\",\"NS\":\"0\"},\"23\":{\"S\":\"0\",\"A\":\"0\",\"N\":\"0\",\"NM\":\"0\",\"NS\":\"0\"},\"24\":{\"S\":\"0\",\"A\":\"0\",\"N\":\"0\",\"NM\":\"0\",\"NS\":\"0\"},\"25\":{\"S\":\"0\",\"A\":\"0\",\"N\":\"0\",\"NM\":\"0\",\"NS\":\"0\"},\"43\":{\"S\":\"0\",\"A\":\"0\",\"N\":\"0\",\"NM\":\"0\",\"NS\":\"0\"},\"26\":{\"S\":\"0\",\"A\":\"0\",\"N\":\"0\",\"NM\":\"0\",\"NS\":\"0\"},\"27\":{\"S\":\"0\",\"A\":\"0\",\"N\":\"0\",\"NM\":\"0\",\"NS\":\"0\"},\"28\":{\"S\":\"0\",\"A\":\"0\",\"N\":\"0\",\"NM\":\"0\",\"NS\":\"0\"},\"29\":{\"S\":\"0\",\"A\":\"0\",\"N\":\"0\",\"NM\":\"0\",\"NS\":\"0\"},\"30\":{\"S\":\"0\",\"A\":\"0\",\"N\":\"0\",\"NM\":\"0\",\"NS\":\"0\"},\"31\":{\"S\":\"0\",\"A\":\"0\",\"N\":\"0\",\"NM\":\"0\",\"NS\":\"0\"},\"2\":{\"S\":\"0\",\"A\":\"0\",\"N\":\"0\",\"NM\":\"0\",\"NS\":\"0\"},\"10\":{\"S\":\"0\",\"A\":\"0\",\"N\":\"0\",\"NM\":\"0\",\"NS\":\"0\"},\"32\":{\"S\":\"0\",\"A\":\"0\",\"N\":\"0\",\"NM\":\"0\",\"NS\":\"0\"},\"33\":{\"S\":\"0\",\"A\":\"0\",\"N\":\"0\",\"NM\":\"0\",\"NS\":\"0\"},\"34\":{\"S\":\"0\",\"A\":\"0\",\"N\":\"0\",\"NM\":\"0\",\"NS\":\"0\"},\"35\":{\"S\":\"0\",\"A\":\"0\",\"N\":\"0\",\"NM\":\"0\",\"NS\":\"0\"},\"36\":{\"S\":\"0\",\"A\":\"0\",\"N\":\"0\",\"NM\":\"0\",\"NS\":\"0\"},\"1\":{\"S\":\"0\",\"A\":\"0\",\"N\":\"0\",\"NM\":\"0\",\"NS\":\"0\"},\"6\":{\"S\":\"0\",\"A\":\"0\",\"N\":\"0\",\"NM\":\"0\",\"NS\":\"0\"},\"45\":{\"S\":\"0\",\"A\":\"0\",\"N\":\"0\",\"NM\":\"0\",\"NS\":\"0\"}}","11");
INSERT INTO `evaluacion` VALUES("12","{\"3\":{\"NS\":2,\"S\":2},\"4\":{\"NS\":2,\"S\":2},\"5\":{\"NS\":2,\"S\":2},\"7\":{\"NS\":2,\"S\":2},\"8\":{\"NS\":2,\"S\":2},\"9\":{\"NS\":2,\"S\":2},\"11\":{\"NS\":2,\"S\":2},\"12\":{\"NS\":2,\"S\":2},\"13\":{\"NS\":2,\"S\":2},\"14\":{\"NS\":2,\"S\":2},\"15\":{\"NS\":2,\"S\":2},\"16\":{\"NS\":2,\"S\":2},\"17\":{\"NS\":2,\"S\":2},\"41\":{\"NS\":2,\"S\":2},\"40\":{\"NS\":2,\"S\":2},\"44\":{\"NS\":2,\"S\":2},\"18\":{\"NS\":2,\"S\":2},\"19\":{\"NS\":2,\"S\":2},\"20\":{\"NS\":2,\"S\":2},\"21\":{\"NS\":2,\"S\":2},\"22\":{\"NS\":2,\"S\":2},\"23\":{\"NS\":2,\"S\":2},\"24\":{\"NS\":2,\"S\":2},\"25\":{\"NS\":2,\"S\":2},\"43\":{\"NS\":2,\"S\":2},\"26\":{\"NS\":2,\"S\":2},\"27\":{\"NS\":2,\"S\":2},\"28\":{\"NS\":2,\"S\":2},\"29\":{\"NS\":2,\"S\":2},\"30\":{\"NS\":2,\"S\":2},\"31\":{\"NS\":2,\"S\":2},\"2\":{\"NS\":2,\"S\":2},\"10\":{\"NS\":2,\"S\":2},\"32\":{\"NS\":2,\"S\":2},\"33\":{\"NS\":2,\"S\":2},\"34\":{\"NS\":2,\"S\":2},\"35\":{\"NS\":2,\"S\":2},\"36\":{\"NS\":2,\"S\":2},\"1\":{\"NS\":2,\"S\":2},\"6\":{\"NS\":2,\"S\":2},\"45\":{\"NS\":2,\"S\":2}}","4");
INSERT INTO `evaluacion` VALUES("12","{\"3\":{\"S\":\"0\",\"A\":\"0\",\"N\":\"0\",\"NM\":\"0\",\"NS\":1},\"4\":{\"S\":\"0\",\"A\":\"0\",\"N\":\"0\",\"NM\":\"0\",\"NS\":1},\"5\":{\"S\":\"0\",\"A\":\"0\",\"N\":\"0\",\"NM\":\"0\",\"NS\":1},\"7\":{\"S\":\"0\",\"A\":\"0\",\"N\":\"0\",\"NM\":\"0\",\"NS\":1},\"8\":{\"S\":\"0\",\"A\":\"0\",\"N\":\"0\",\"NM\":\"0\",\"NS\":1},\"9\":{\"S\":\"0\",\"A\":\"0\",\"N\":\"0\",\"NM\":\"0\",\"NS\":1},\"11\":{\"S\":\"0\",\"A\":\"0\",\"N\":\"0\",\"NM\":\"0\",\"NS\":1},\"12\":{\"S\":\"0\",\"A\":\"0\",\"N\":\"0\",\"NM\":\"0\",\"NS\":1},\"13\":{\"S\":\"0\",\"A\":\"0\",\"N\":\"0\",\"NM\":\"0\",\"NS\":1},\"14\":{\"S\":\"0\",\"A\":\"0\",\"N\":\"0\",\"NM\":\"0\",\"NS\":1},\"15\":{\"S\":\"0\",\"A\":\"0\",\"N\":\"0\",\"NM\":\"0\",\"NS\":1},\"16\":{\"S\":\"0\",\"A\":\"0\",\"N\":\"0\",\"NM\":\"0\",\"NS\":1},\"17\":{\"S\":\"0\",\"A\":\"0\",\"N\":\"0\",\"NM\":\"0\",\"NS\":1},\"41\":{\"S\":\"0\",\"A\":\"0\",\"N\":\"0\",\"NM\":\"0\",\"NS\":1},\"40\":{\"S\":\"0\",\"A\":\"0\",\"N\":\"0\",\"NM\":\"0\",\"NS\":1},\"44\":{\"S\":\"0\",\"A\":\"0\",\"N\":\"0\",\"NM\":\"0\",\"NS\":1},\"18\":{\"S\":\"0\",\"A\":\"0\",\"N\":\"0\",\"NM\":\"0\",\"NS\":1},\"19\":{\"S\":\"0\",\"A\":\"0\",\"N\":\"0\",\"NM\":\"0\",\"NS\":1},\"20\":{\"S\":\"0\",\"A\":\"0\",\"N\":\"0\",\"NM\":\"0\",\"NS\":1},\"21\":{\"S\":\"0\",\"A\":\"0\",\"N\":\"0\",\"NM\":\"0\",\"NS\":1},\"22\":{\"S\":\"0\",\"A\":\"0\",\"N\":\"0\",\"NM\":\"0\",\"NS\":1},\"23\":{\"S\":\"0\",\"A\":\"0\",\"N\":\"0\",\"NM\":\"0\",\"NS\":1},\"24\":{\"S\":\"0\",\"A\":\"0\",\"N\":\"0\",\"NM\":\"0\",\"NS\":1},\"25\":{\"S\":\"0\",\"A\":\"0\",\"N\":\"0\",\"NM\":\"0\",\"NS\":1},\"43\":{\"S\":\"0\",\"A\":\"0\",\"N\":\"0\",\"NM\":\"0\",\"NS\":1},\"26\":{\"S\":\"0\",\"A\":\"0\",\"N\":\"0\",\"NM\":\"0\",\"NS\":1},\"27\":{\"S\":\"0\",\"A\":\"0\",\"N\":\"0\",\"NM\":\"0\",\"NS\":1},\"28\":{\"S\":\"0\",\"A\":\"0\",\"N\":\"0\",\"NM\":\"0\",\"NS\":1},\"29\":{\"S\":\"0\",\"A\":\"0\",\"N\":\"0\",\"NM\":\"0\",\"NS\":1},\"30\":{\"S\":\"0\",\"A\":\"0\",\"N\":\"0\",\"NM\":\"0\",\"NS\":1},\"31\":{\"S\":\"0\",\"A\":\"0\",\"N\":\"0\",\"NM\":\"0\",\"NS\":1},\"2\":{\"S\":\"0\",\"A\":\"0\",\"N\":\"0\",\"NM\":\"0\",\"NS\":1},\"10\":{\"S\":\"0\",\"A\":\"0\",\"N\":\"0\",\"NM\":\"0\",\"NS\":1},\"32\":{\"S\":\"0\",\"A\":\"0\",\"N\":\"0\",\"NM\":\"0\",\"NS\":1},\"33\":{\"S\":\"0\",\"A\":\"0\",\"N\":\"0\",\"NM\":\"0\",\"NS\":1},\"34\":{\"S\":\"0\",\"A\":\"0\",\"N\":\"0\",\"NM\":\"0\",\"NS\":1},\"35\":{\"S\":\"0\",\"A\":\"0\",\"N\":\"0\",\"NM\":\"0\",\"NS\":1},\"36\":{\"S\":\"0\",\"A\":\"0\",\"N\":\"0\",\"NM\":\"0\",\"NS\":1},\"1\":{\"S\":\"0\",\"A\":\"0\",\"N\":\"0\",\"NM\":\"0\",\"NS\":1},\"6\":{\"S\":\"0\",\"A\":\"0\",\"N\":\"0\",\"NM\":\"0\",\"NS\":1},\"45\":{\"S\":\"0\",\"A\":\"0\",\"N\":\"0\",\"NM\":\"0\",\"NS\":1}}","11");
INSERT INTO `evaluacion` VALUES("20","{\"3\":{\"S\":\"0\",\"A\":\"0\",\"N\":\"0\",\"NM\":\"0\",\"NS\":\"0\"},\"4\":{\"S\":\"0\",\"A\":\"0\",\"N\":\"0\",\"NM\":\"0\",\"NS\":\"0\"},\"5\":{\"S\":\"0\",\"A\":\"0\",\"N\":\"0\",\"NM\":\"0\",\"NS\":\"0\"},\"7\":{\"S\":\"0\",\"A\":\"0\",\"N\":\"0\",\"NM\":\"0\",\"NS\":\"0\"},\"8\":{\"S\":\"0\",\"A\":\"0\",\"N\":\"0\",\"NM\":\"0\",\"NS\":\"0\"},\"9\":{\"S\":\"0\",\"A\":\"0\",\"N\":\"0\",\"NM\":\"0\",\"NS\":\"0\"},\"11\":{\"S\":\"0\",\"A\":\"0\",\"N\":\"0\",\"NM\":\"0\",\"NS\":\"0\"},\"12\":{\"S\":\"0\",\"A\":\"0\",\"N\":\"0\",\"NM\":\"0\",\"NS\":\"0\"},\"13\":{\"S\":\"0\",\"A\":\"0\",\"N\":\"0\",\"NM\":\"0\",\"NS\":\"0\"},\"14\":{\"S\":\"0\",\"A\":\"0\",\"N\":\"0\",\"NM\":\"0\",\"NS\":\"0\"},\"15\":{\"S\":\"0\",\"A\":\"0\",\"N\":\"0\",\"NM\":\"0\",\"NS\":\"0\"},\"16\":{\"S\":\"0\",\"A\":\"0\",\"N\":\"0\",\"NM\":\"0\",\"NS\":\"0\"},\"17\":{\"S\":\"0\",\"A\":\"0\",\"N\":\"0\",\"NM\":\"0\",\"NS\":\"0\"},\"41\":{\"S\":\"0\",\"A\":\"0\",\"N\":\"0\",\"NM\":\"0\",\"NS\":\"0\"},\"40\":{\"S\":\"0\",\"A\":\"0\",\"N\":\"0\",\"NM\":\"0\",\"NS\":\"0\"},\"44\":{\"S\":\"0\",\"A\":\"0\",\"N\":\"0\",\"NM\":\"0\",\"NS\":\"0\"},\"18\":{\"S\":\"0\",\"A\":\"0\",\"N\":\"0\",\"NM\":\"0\",\"NS\":\"0\"},\"19\":{\"S\":\"0\",\"A\":\"0\",\"N\":\"0\",\"NM\":\"0\",\"NS\":\"0\"},\"20\":{\"S\":\"0\",\"A\":\"0\",\"N\":\"0\",\"NM\":\"0\",\"NS\":\"0\"},\"21\":{\"S\":\"0\",\"A\":\"0\",\"N\":\"0\",\"NM\":\"0\",\"NS\":\"0\"},\"22\":{\"S\":\"0\",\"A\":\"0\",\"N\":\"0\",\"NM\":\"0\",\"NS\":\"0\"},\"23\":{\"S\":\"0\",\"A\":\"0\",\"N\":\"0\",\"NM\":\"0\",\"NS\":\"0\"},\"24\":{\"S\":\"0\",\"A\":\"0\",\"N\":\"0\",\"NM\":\"0\",\"NS\":\"0\"},\"25\":{\"S\":\"0\",\"A\":\"0\",\"N\":\"0\",\"NM\":\"0\",\"NS\":\"0\"},\"26\":{\"S\":\"0\",\"A\":\"0\",\"N\":\"0\",\"NM\":\"0\",\"NS\":\"0\"},\"27\":{\"S\":\"0\",\"A\":\"0\",\"N\":\"0\",\"NM\":\"0\",\"NS\":\"0\"},\"28\":{\"S\":\"0\",\"A\":\"0\",\"N\":\"0\",\"NM\":\"0\",\"NS\":\"0\"},\"29\":{\"S\":\"0\",\"A\":\"0\",\"N\":\"0\",\"NM\":\"0\",\"NS\":\"0\"},\"30\":{\"S\":\"0\",\"A\":\"0\",\"N\":\"0\",\"NM\":\"0\",\"NS\":\"0\"},\"31\":{\"S\":\"0\",\"A\":\"0\",\"N\":\"0\",\"NM\":\"0\",\"NS\":\"0\"},\"2\":{\"S\":\"0\",\"A\":\"0\",\"N\":\"0\",\"NM\":\"0\",\"NS\":\"0\"},\"10\":{\"S\":\"0\",\"A\":\"0\",\"N\":\"0\",\"NM\":\"0\",\"NS\":\"0\"},\"32\":{\"S\":\"0\",\"A\":\"0\",\"N\":\"0\",\"NM\":\"0\",\"NS\":\"0\"},\"33\":{\"S\":\"0\",\"A\":\"0\",\"N\":\"0\",\"NM\":\"0\",\"NS\":\"0\"},\"34\":{\"S\":\"0\",\"A\":\"0\",\"N\":\"0\",\"NM\":\"0\",\"NS\":\"0\"},\"35\":{\"S\":\"0\",\"A\":\"0\",\"N\":\"0\",\"NM\":\"0\",\"NS\":\"0\"},\"36\":{\"S\":\"0\",\"A\":\"0\",\"N\":\"0\",\"NM\":\"0\",\"NS\":\"0\"},\"1\":{\"S\":\"0\",\"A\":\"0\",\"N\":\"0\",\"NM\":\"0\",\"NS\":\"0\"},\"6\":{\"S\":\"0\",\"A\":\"0\",\"N\":\"0\",\"NM\":\"0\",\"NS\":\"0\"},\"45\":{\"S\":\"0\",\"A\":\"0\",\"N\":\"0\",\"NM\":\"0\",\"NS\":\"0\"}}","11");



DROP TABLE IF EXISTS `evaluador_guagua`;

CREATE TABLE `evaluador_guagua` (
  `id_actividad` int(11) NOT NULL AUTO_INCREMENT,
  `estructura` text NOT NULL,
  `punto` varchar(2) NOT NULL,
  `usuario` varchar(255) NOT NULL,
  `version` int(4) NOT NULL DEFAULT 3,
  PRIMARY KEY (`id_actividad`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;




DROP TABLE IF EXISTS `evaluadorestudiante`;

CREATE TABLE `evaluadorestudiante` (
  `id_actividad` int(11) NOT NULL AUTO_INCREMENT,
  `estructura` text NOT NULL,
  `usuario` varchar(255) NOT NULL,
  PRIMARY KEY (`id_actividad`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;




DROP TABLE IF EXISTS `eventos`;

CREATE TABLE `eventos` (
  `nom` varchar(255) NOT NULL,
  `descripcion` text NOT NULL,
  `fecha` date NOT NULL,
  `hora_inicio` time NOT NULL,
  `hora_fin` time NOT NULL,
  `id_usuario` varchar(12) NOT NULL,
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `institucion` int(11) NOT NULL DEFAULT 1,
  `revisado` int(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

INSERT INTO `eventos` VALUES("Recibir lecciones de técnicas","Recibir lecciones de técnicas a Daniela Ahumada y Camila","2019-03-16","12:00:00","13:00:00","1085290375","1","1","0");
INSERT INTO `eventos` VALUES("Vídeo de TIC en educación","Vídeo de TIC en educación para Alejandra","2019-04-12","08:00:00","21:00:00","1085290375","2","1","0");
INSERT INTO `eventos` VALUES("Revisar estudiantes nuevos y pendientes digitación","Los estudiantes Daniela, Mauricio, Angie, Alexa, Manuel revisar","2019-04-06","20:00:00","22:00:00","1085290375","3","1","0");
INSERT INTO `eventos` VALUES("Recibir talleres a Alejandra de técnicas de digitación","Recibir talleres a Alejandra de técnicas de digitación","2019-04-12","08:00:00","22:00:00","1085290375","4","1","0");
INSERT INTO `eventos` VALUES("Verificar audios transcritos de técnicas de digitación","Verificar audios transcritos de técnicas de digitación","2019-04-20","10:00:00","12:00:00","1085290375","5","1","0");
INSERT INTO `eventos` VALUES("Recibir taller final de Ofimática 1","Recibir taller final de Ofimática 1","2019-06-08","12:00:00","18:00:00","1","6","1","0");
INSERT INTO `eventos` VALUES("Examen de base de datos","hacer examen de diseño, creación y crud de base de datos","2019-09-04","08:00:00","10:00:00","1085290375","7","1","0");
INSERT INTO `eventos` VALUES("Examen Join","Examen de base de datos","2019-10-15","08:00:00","10:00:00","1085290375","8","1","0");
INSERT INTO `eventos` VALUES("Documento para técnicos","Elaborar documentos para técnicos","2020-03-27","11:00:00","17:00:00","1085290375","9","1","1");
INSERT INTO `eventos` VALUES("Crear grupos de watsapp","Crear grupos de watsapp de técnicos en sistemas","2020-04-05","08:00:00","18:00:00","1085290375","10","1","0");
INSERT INTO `eventos` VALUES("Revisar actividades info básica mañana","Revisar actividades info básica mañana","2020-04-19","08:00:00","18:00:00","1","11","1","0");
INSERT INTO `eventos` VALUES("Examen Asistente administrativo","Examen Asistente administrativo semana","2020-05-12","08:00:00","10:00:00","1085290375","12","1","0");
INSERT INTO `eventos` VALUES("Seguridad informática plan","Seguridad informática plan desarrollar","2021-02-20","08:00:00","10:00:00","1085290375","13","1","0");



DROP TABLE IF EXISTS `evidencia_de_aprendizaje`;

CREATE TABLE `evidencia_de_aprendizaje` (
  `id_evidencia_aprendizaje` int(11) NOT NULL AUTO_INCREMENT,
  `descripcion_evidencia` text DEFAULT NULL,
  `id_dba` int(11) NOT NULL,
  PRIMARY KEY (`id_evidencia_aprendizaje`),
  KEY `id_dba` (`id_dba`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `evidencia_de_aprendizaje` VALUES("1","• Relaciona el origen de la agricultura con el desarrollo de las sociedades antiguas y la aparición de elementos que permanecen en la actualidad (canales de riego, la escritura, el ladrillo).\n• Explica el papel de los ríos Nilo, Tigris, Éufrates, Indo, Ganges, Huang He y Yangtsé Kiang, en la construcción de las primeras ciudades y el origen de las civilizaciones antiguas y los ubica en un mapa actual de África y Asia.\n• Describe semejanzas y diferencias que se observan entre la democracia ateniense y las democracias actuales, en especial la colombiana, para señalar fortalezas, debilidades y alternativas que conduzcan a una mayor democratización.\n• Describe el origen de la ciudadanía, los cambios que ha tenido en el tiempo y su significado actual.\n• Argumenta la importancia de participar activamente en la toma de decisiones para el bienestar colectivo en la sociedad, en el contexto de una democracia.\n• Participa activamente en la construcción de los protocolos de seguridad de la Institución Educativa.","1");
INSERT INTO `evidencia_de_aprendizaje` VALUES("2","Explica la importancia que tiene para una sociedad la resolución pacífica de sus conflictos y el respeto por las diferencias políticas, ideológicas, de género, religiosas, étnicas o intereses económicos.","2");
INSERT INTO `evidencia_de_aprendizaje` VALUES("3","Analiza la importancia de la mentalidad emprendedora como estrategia para mejorar su calidad de vida y la de su entorno cercano y lejano. Propone normas de seguridad en los proyectos emprendedores de la vereda y tiene en cuenta su entorno para beneficiarse del emprendimiento.","3");
INSERT INTO `evidencia_de_aprendizaje` VALUES("4","Propone y justifica diferentes estrategias para resolver problemas con números enteros y racionales en contextos escolares y extraescolares. Determina criterios de comparación para establecer relaciones de orden entre dos o más números. Representa en la recta numérica la posición de un número utilizando diferentes estrategias.","4");
INSERT INTO `evidencia_de_aprendizaje` VALUES("5","Considera el error que genera la aproximación de un número real a partir de números racionales. Identifica la diferencia entre exactitud y aproximación en las diferentes representaciones de los números reales. Construye representaciones geométricas y numéricas de los números reales (con decimales, raíces, razones, y otros símbolos) y realiza conversiones entre ellas.","5");
INSERT INTO `evidencia_de_aprendizaje` VALUES("6","Asimila y emplea una terminología específica del área. Comprende la importancia del diagnóstico físico. Incluye el calentamiento como hábito de salud y prevención física. Valora la importancia del sistema esquelético para el desempeño personal. Identifica las lesiones más comunes en los atletas y la forma de prevenirlas.","6");
INSERT INTO `evidencia_de_aprendizaje` VALUES("7","Reconoce las funciones específicas del sistema respiratorio y circulatorio y su influencia en la actividad física. Identifica los movimientos articulares del cuerpo, reconoce lesiones deportivas y sabe manejarlas adecuadamente en la práctica deportiva. Respeta las normas que propicien el juego limpio.","7");
INSERT INTO `evidencia_de_aprendizaje` VALUES("8","Ejemplifica cómo en el uso de artefactos, procesos o sistemas tecnológicos, existen principios de funcionamiento que los sustentan. Participa en actividades colaborativas para fortalecer el aprendizaje mediante interacción, respeto y tolerancia.","8");
INSERT INTO `evidencia_de_aprendizaje` VALUES("9","Maneja conceptos básicos y partes de la ventana principal en Microsoft Excel. Practica combinaciones de teclas para desplazarse en el programa. Elabora correctamente fórmulas utilizando operadores matemáticos básicos. Realiza tablas y gráficos sobre el proceso de elección del representante estudiantil y encuestas relacionadas con el uso adecuado de sistemas tecnológicos. Destaca el aporte de máquinas simples a las actividades cotidianas, comprendiendo su utilidad e impacto ambiental.","9");
INSERT INTO `evidencia_de_aprendizaje` VALUES("10","Predice el equilibrio de un cuerpo aplicando la primera ley de Newton. Calcula la aceleración usando la segunda ley de Newton. Identifica pares de acción y reacción en situaciones cotidianas. Evalúa prototipos experimentales sobre el movimiento. Analiza el diseño de viviendas o estructuras considerando estabilidad y seguridad.","10");
INSERT INTO `evidencia_de_aprendizaje` VALUES("11","Identifica la importancia de los valores humanos. Conoce los beneficios de una persona comprometida con el bienestar común. Describe cómo aplicar derechos y deberes para convivir en paz. Analiza las ventajas de conocer y aplicar valores humanos. Opina sobre problemas de convivencia y propone soluciones.","11");
INSERT INTO `evidencia_de_aprendizaje` VALUES("12","Identifico eventos en la vida diaria que generan conflicto interno por cuanto tengo prejuicios, estereotipos y emociones que me hacen dudar de mi actitud.","12");
INSERT INTO `evidencia_de_aprendizaje` VALUES("18","Entiende la caracterización económica y social del entorno inmediato. Identifica su manera de vivenciar dicha caracterización económica y social de su entorno inmediato. \nAnaliza el mercado laboral y empleabilidad a nivel regional y nacional. Realiza una caracterización de sus aptitudes, habilidades y competencias que le permitan proyectarse como representante de grupo, contralor(a) y personero(a). \nEmprende la caracterización de señales de riesgo en la institución educativa.","13");



DROP TABLE IF EXISTS `facebook`;

CREATE TABLE `facebook` (
  `id_fb` int(12) NOT NULL AUTO_INCREMENT,
  `usuario` varchar(255) NOT NULL,
  `clave` varchar(255) NOT NULL,
  `detalle` text NOT NULL,
  PRIMARY KEY (`id_fb`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;




DROP TABLE IF EXISTS `figuras`;

CREATE TABLE `figuras` (
  `id_figuras` int(8) NOT NULL,
  `figura` varchar(120) NOT NULL,
  `imagen_figura` varchar(120) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

INSERT INTO `figuras` VALUES("1","perro","perro.PNG");
INSERT INTO `figuras` VALUES("2","gato","gato.PNG");
INSERT INTO `figuras` VALUES("3","caballo","caballo.PNG");
INSERT INTO `figuras` VALUES("4","conejo","conejo.PNG");
INSERT INTO `figuras` VALUES("5","pato","pato.PNG");



DROP TABLE IF EXISTS `grado`;

CREATE TABLE `grado` (
  `id_grado` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(60) DEFAULT NULL,
  PRIMARY KEY (`id_grado`)
) ENGINE=MyISAM AUTO_INCREMENT=36 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

INSERT INTO `grado` VALUES("35","1");
INSERT INTO `grado` VALUES("2","2");
INSERT INTO `grado` VALUES("3","3");
INSERT INTO `grado` VALUES("4","4");
INSERT INTO `grado` VALUES("5","5");
INSERT INTO `grado` VALUES("6","6");
INSERT INTO `grado` VALUES("7","7");
INSERT INTO `grado` VALUES("8","8");
INSERT INTO `grado` VALUES("9","9");
INSERT INTO `grado` VALUES("10","10");
INSERT INTO `grado` VALUES("11","11");
INSERT INTO `grado` VALUES("28","12");
INSERT INTO `grado` VALUES("29","tecnico1");
INSERT INTO `grado` VALUES("30","tecnico2");
INSERT INTO `grado` VALUES("31","tecnico3");
INSERT INTO `grado` VALUES("32","tecnico 4");
INSERT INTO `grado` VALUES("33","Asistente administrativo");
INSERT INTO `grado` VALUES("34","Cursos libres");



DROP TABLE IF EXISTS `grupo_foro`;

CREATE TABLE `grupo_foro` (
  `id_grupo_foro` int(11) NOT NULL,
  `nombre_grupo` varchar(255) NOT NULL,
  `roles_grupo` varchar(255) NOT NULL,
  `contexto` varchar(255) NOT NULL,
  `valor` int(11) DEFAULT NULL,
  `permitir_temas` varchar(2) NOT NULL DEFAULT 'SI',
  `icono` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

INSERT INTO `grupo_foro` VALUES("1","Institucional","admin,docente,acudiente,estudiante","general","0","SI","513");
INSERT INTO `grupo_foro` VALUES("2","Docentes","admin,docente","general","0","SI","513");
INSERT INTO `grupo_foro` VALUES("3","Estudiantes","admin,docente,estudiante","general","0","SI","513");
INSERT INTO `grupo_foro` VALUES("4","Acudientes","admin,docente,estudiante","general","0","SI","498");
INSERT INTO `grupo_foro` VALUES("0","Foro actividad ","admin,docente,estudiante","actividad","0","NO","378");



DROP TABLE IF EXISTS `horario`;

CREATE TABLE `horario` (
  `id_horario` int(11) NOT NULL AUTO_INCREMENT,
  `id_asignacion` int(11) NOT NULL,
  `fecha_inicio` date NOT NULL,
  `hora_inicio` time NOT NULL,
  `hora_fin` time NOT NULL,
  `fecha_fin` date NOT NULL,
  `dia` varchar(20) NOT NULL,
  PRIMARY KEY (`id_horario`)
) ENGINE=InnoDB AUTO_INCREMENT=503 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

INSERT INTO `horario` VALUES("13","18","2019-02-05","12:00:00","13:00:00","2019-04-05","sabado");
INSERT INTO `horario` VALUES("51","17","2019-03-19","07:00:00","13:00:00","2019-03-20","martes");
INSERT INTO `horario` VALUES("52","17","2019-03-19","07:00:00","13:00:00","2019-03-20","miercoles");
INSERT INTO `horario` VALUES("60","19","2019-02-02","12:00:00","13:00:00","2019-06-08","sabado");
INSERT INTO `horario` VALUES("61","25","2019-04-15","10:00:00","12:00:00","2019-08-15","sabado");
INSERT INTO `horario` VALUES("62","20","2019-02-09","18:30:00","18:30:00","2019-06-21","miercoles");
INSERT INTO `horario` VALUES("63","20","2019-02-09","18:30:00","20:30:00","2019-06-21","viernes");
INSERT INTO `horario` VALUES("64","29","2019-07-08","10:00:00","00:00:00","2019-10-28","lunes");
INSERT INTO `horario` VALUES("66","30","2020-07-14","10:00:00","12:00:00","2020-09-14","lunes");
INSERT INTO `horario` VALUES("67","32","2019-07-08","10:00:00","12:00:00","2019-10-28","lunes");
INSERT INTO `horario` VALUES("68","34","2019-07-03","10:00:00","12:00:00","2019-10-30","miercoles");
INSERT INTO `horario` VALUES("70","36","2019-07-22","19:00:00","21:00:00","2019-10-30","lunes");
INSERT INTO `horario` VALUES("71","36","2019-07-22","19:00:00","21:00:00","2019-10-30","martes");
INSERT INTO `horario` VALUES("72","36","2019-07-22","19:00:00","21:00:00","2019-10-30","jueves");
INSERT INTO `horario` VALUES("73","27","2019-07-27","08:00:00","13:30:00","2019-10-30","sabado");
INSERT INTO `horario` VALUES("75","37","2019-07-27","14:00:00","19:00:00","2019-10-30","sabado");
INSERT INTO `horario` VALUES("76","39","2019-07-22","18:00:00","19:00:00","2019-10-31","lunes");
INSERT INTO `horario` VALUES("77","39","2019-07-22","18:00:00","20:00:00","2019-10-31","miercoles");
INSERT INTO `horario` VALUES("78","39","2019-07-22","18:00:00","20:00:00","2019-10-31","viernes");
INSERT INTO `horario` VALUES("80","35","2019-07-03","08:00:00","10:00:00","2019-10-30","martes");
INSERT INTO `horario` VALUES("87","43","2019-09-06","15:00:00","17:00:00","2019-10-05","martes");
INSERT INTO `horario` VALUES("88","43","2019-09-06","15:00:00","17:00:00","2019-10-05","miercoles");
INSERT INTO `horario` VALUES("89","43","2019-09-06","15:00:00","17:00:00","2019-10-05","jueves");
INSERT INTO `horario` VALUES("91","41","2019-09-09","08:00:00","10:00:00","2019-12-05","miercoles");
INSERT INTO `horario` VALUES("96","46","2019-11-02","08:00:00","10:00:00","2019-12-07","sabado");
INSERT INTO `horario` VALUES("119","44","2020-02-02","18:30:00","20:30:00","2020-02-12","lunes");
INSERT INTO `horario` VALUES("120","44","2020-02-02","18:30:00","20:30:00","2020-02-12","martes");
INSERT INTO `horario` VALUES("121","44","2020-02-02","18:30:00","20:30:00","2020-02-12","miercoles");
INSERT INTO `horario` VALUES("122","44","2020-02-02","18:30:00","20:30:00","2020-02-12","jueves");
INSERT INTO `horario` VALUES("123","44","2020-02-02","18:30:00","20:30:00","2020-02-12","viernes");
INSERT INTO `horario` VALUES("128","55","2020-02-22","10:00:00","11:30:00","2020-06-23","sabado");
INSERT INTO `horario` VALUES("134","58","2020-02-18","10:00:00","12:00:00","2020-06-16","martes");
INSERT INTO `horario` VALUES("136","48","2019-12-02","15:00:00","18:00:00","2020-03-20","lunes");
INSERT INTO `horario` VALUES("137","48","2019-12-02","15:00:00","18:00:00","2020-03-20","martes");
INSERT INTO `horario` VALUES("138","48","2019-12-02","15:00:00","18:00:00","2020-03-20","miercoles");
INSERT INTO `horario` VALUES("139","48","2019-12-02","15:00:00","18:00:00","2020-03-20","jueves");
INSERT INTO `horario` VALUES("140","48","2019-12-02","15:00:00","18:00:00","2020-03-20","viernes");
INSERT INTO `horario` VALUES("141","56","2020-02-18","18:30:00","20:30:00","2020-06-17","martes");
INSERT INTO `horario` VALUES("142","56","2020-02-18","18:30:00","20:30:00","2020-06-17","jueves");
INSERT INTO `horario` VALUES("166","57","2020-02-19","18:30:00","20:30:00","2020-06-17","miercoles");
INSERT INTO `horario` VALUES("167","52","2020-02-10","08:00:00","10:00:00","2020-07-08","lunes");
INSERT INTO `horario` VALUES("168","50","2020-02-04","08:00:00","10:00:00","2020-07-02","martes");
INSERT INTO `horario` VALUES("169","59","2020-03-14","11:30:00","13:30:00","2020-06-14","sabado");
INSERT INTO `horario` VALUES("170","51","2020-02-07","08:00:00","10:00:00","2020-07-05","viernes");
INSERT INTO `horario` VALUES("171","54","2020-02-22","08:00:00","09:30:00","2020-07-23","sabado");
INSERT INTO `horario` VALUES("172","53","2020-02-10","08:00:00","10:00:00","2020-07-11","jueves");
INSERT INTO `horario` VALUES("173","60","2020-04-24","16:00:00","18:00:00","2020-06-01","lunes");
INSERT INTO `horario` VALUES("174","60","2020-04-24","16:00:00","18:00:00","2020-06-01","martes");
INSERT INTO `horario` VALUES("175","60","2020-04-24","16:00:00","18:00:00","2020-06-01","miercoles");
INSERT INTO `horario` VALUES("176","60","2020-04-24","16:00:00","18:00:00","2020-06-01","viernes");
INSERT INTO `horario` VALUES("192","63","2020-07-01","18:30:00","20:30:00","2020-08-31","martes");
INSERT INTO `horario` VALUES("193","63","2020-07-01","18:30:00","20:30:00","2020-08-31","jueves");
INSERT INTO `horario` VALUES("194","63","2020-07-01","18:30:00","20:30:00","2020-08-31","sabado");
INSERT INTO `horario` VALUES("198","64","2020-01-13","07:00:00","12:00:00","2020-12-13","lunes");
INSERT INTO `horario` VALUES("199","64","2020-01-13","07:00:00","12:00:00","2020-12-13","martes");
INSERT INTO `horario` VALUES("200","64","2020-01-13","07:00:00","12:00:00","2020-12-13","miercoles");
INSERT INTO `horario` VALUES("201","64","2020-01-13","19:00:00","12:00:00","2020-12-13","jueves");
INSERT INTO `horario` VALUES("202","64","2020-01-13","19:00:00","12:00:00","2020-12-13","viernes");
INSERT INTO `horario` VALUES("206","61","2020-07-01","16:00:00","18:00:00","2020-09-30","lunes");
INSERT INTO `horario` VALUES("207","61","2020-07-01","16:00:00","18:00:00","2020-09-30","miercoles");
INSERT INTO `horario` VALUES("208","61","2020-07-01","16:00:00","18:00:00","2020-09-30","viernes");
INSERT INTO `horario` VALUES("209","62","2020-06-29","18:30:00","20:30:00","2020-09-30","lunes");
INSERT INTO `horario` VALUES("210","62","2020-06-29","18:30:00","20:30:00","2020-09-30","miercoles");
INSERT INTO `horario` VALUES("211","62","2020-06-29","16:00:00","18:00:00","2020-09-30","sabado");
INSERT INTO `horario` VALUES("212","65","2020-10-16","16:30:00","18:30:00","2020-12-04","lunes");
INSERT INTO `horario` VALUES("213","65","2020-10-16","16:30:00","18:30:00","2020-12-04","miercoles");
INSERT INTO `horario` VALUES("214","65","2020-10-16","16:30:00","18:30:00","2020-12-04","viernes");
INSERT INTO `horario` VALUES("215","66","2020-10-16","18:30:00","20:30:00","2020-12-04","lunes");
INSERT INTO `horario` VALUES("216","66","2020-10-16","18:30:00","20:30:00","2020-12-04","miercoles");
INSERT INTO `horario` VALUES("217","66","2020-10-16","18:30:00","20:30:00","2020-12-04","viernes");
INSERT INTO `horario` VALUES("220","71","2021-02-10","18:30:00","20:30:00","2021-03-03","miercoles");
INSERT INTO `horario` VALUES("222","70","2021-02-06","18:30:00","20:30:00","2021-03-02","martes");
INSERT INTO `horario` VALUES("223","70","2021-02-06","18:30:00","20:30:00","2021-03-02","viernes");
INSERT INTO `horario` VALUES("246","78","2021-04-10","10:00:00","12:00:00","2021-06-10","lunes");
INSERT INTO `horario` VALUES("247","78","2021-04-10","10:00:00","12:00:00","2021-06-10","miercoles");
INSERT INTO `horario` VALUES("248","78","2021-04-10","10:00:00","12:00:00","2021-06-10","viernes");
INSERT INTO `horario` VALUES("249","67","2021-02-08","08:00:00","10:00:00","2021-04-13","martes");
INSERT INTO `horario` VALUES("291","76","2021-03-10","18:30:00","20:30:00","2021-07-07","miercoles");
INSERT INTO `horario` VALUES("292","77","2021-03-08","18:30:00","20:30:00","2021-07-21","lunes");
INSERT INTO `horario` VALUES("299","75","2021-03-01","10:00:00","12:00:00","2021-05-23","martes");
INSERT INTO `horario` VALUES("300","75","2021-03-01","10:00:00","00:00:00","2021-05-23","jueves");
INSERT INTO `horario` VALUES("301","75","2021-03-01","08:00:00","10:00:00","2021-05-23","sabado");
INSERT INTO `horario` VALUES("305","79","2021-04-21","16:30:00","18:30:00","2021-05-31","lunes");
INSERT INTO `horario` VALUES("306","79","2021-04-21","16:30:00","18:30:00","2021-05-31","viernes");
INSERT INTO `horario` VALUES("307","79","2021-04-21","09:30:00","13:30:00","2021-05-31","sabado");
INSERT INTO `horario` VALUES("311","80","2021-04-26","14:30:00","16:30:00","2021-06-10","lunes");
INSERT INTO `horario` VALUES("312","80","2021-04-26","14:30:00","16:30:00","2021-06-10","miercoles");
INSERT INTO `horario` VALUES("313","80","2021-04-26","14:30:00","16:30:00","2021-06-10","viernes");
INSERT INTO `horario` VALUES("314","69","2021-02-11","08:00:00","09:30:00","2021-08-12","sabado");
INSERT INTO `horario` VALUES("330","84","2021-07-11","17:00:00","19:00:00","2021-07-12","lunes");
INSERT INTO `horario` VALUES("331","84","2021-07-11","17:00:00","19:00:00","2021-07-12","martes");
INSERT INTO `horario` VALUES("332","84","2021-07-11","17:00:00","19:00:00","2021-07-12","miercoles");
INSERT INTO `horario` VALUES("333","84","2021-07-11","17:00:00","19:00:00","2021-07-12","jueves");
INSERT INTO `horario` VALUES("334","84","2021-07-11","17:00:00","19:00:00","2021-07-12","viernes");
INSERT INTO `horario` VALUES("346","83","2021-07-12","17:00:00","19:00:00","2021-08-13","lunes");
INSERT INTO `horario` VALUES("347","83","2021-07-12","17:00:00","19:00:00","2021-08-13","martes");
INSERT INTO `horario` VALUES("348","83","2021-07-12","17:00:00","19:00:00","2021-08-13","miercoles");
INSERT INTO `horario` VALUES("349","83","2021-07-12","17:00:00","19:00:00","2021-08-13","jueves");
INSERT INTO `horario` VALUES("350","83","2021-07-12","17:00:00","19:00:00","2021-08-13","viernes");
INSERT INTO `horario` VALUES("399","81","2021-06-08","18:30:00","20:30:00","2021-08-13","martes");
INSERT INTO `horario` VALUES("400","81","2021-06-08","18:30:00","20:30:00","2021-08-13","jueves");
INSERT INTO `horario` VALUES("401","81","2021-06-08","18:30:00","20:30:00","2021-08-13","viernes");
INSERT INTO `horario` VALUES("405","82","2021-06-08","18:30:00","20:30:00","2021-09-13","martes");
INSERT INTO `horario` VALUES("406","82","2021-06-08","18:30:00","20:30:00","2021-09-13","jueves");
INSERT INTO `horario` VALUES("407","82","2021-06-08","18:30:00","20:30:00","2021-09-13","viernes");
INSERT INTO `horario` VALUES("414","85","2021-09-02","08:00:00","18:00:00","2021-12-04","lunes");
INSERT INTO `horario` VALUES("415","85","2021-09-02","08:00:00","18:00:00","2021-12-04","martes");
INSERT INTO `horario` VALUES("416","85","2021-09-02","08:00:00","18:00:00","2021-12-04","miercoles");
INSERT INTO `horario` VALUES("417","85","2021-09-02","08:00:00","18:00:00","2021-12-04","jueves");
INSERT INTO `horario` VALUES("418","85","2021-09-02","08:00:00","18:00:00","2021-12-04","viernes");
INSERT INTO `horario` VALUES("419","85","2021-09-02","08:00:00","12:00:00","2021-12-04","sabado");
INSERT INTO `horario` VALUES("420","86","2023-05-20","08:00:00","13:00:00","2023-07-08","sabado");
INSERT INTO `horario` VALUES("421","87","2023-07-15","08:00:00","13:00:00","2023-08-12","sabado");
INSERT INTO `horario` VALUES("424","88","2023-08-26","08:00:00","13:00:00","2023-09-09","sabado");
INSERT INTO `horario` VALUES("425","90","2023-08-05","15:00:00","17:00:00","2023-09-30","sabado");
INSERT INTO `horario` VALUES("426","91","2023-09-04","08:00:00","10:00:00","2023-11-28","lunes");
INSERT INTO `horario` VALUES("427","92","2023-09-04","18:30:00","20:30:00","2023-11-28","martes");
INSERT INTO `horario` VALUES("428","93","2023-09-04","18:30:00","20:30:00","2023-11-28","miercoles");
INSERT INTO `horario` VALUES("430","95","2023-09-04","08:00:00","10:00:00","2023-11-28","viernes");
INSERT INTO `horario` VALUES("432","89","2023-08-28","08:00:00","12:00:00","2023-10-20","martes");
INSERT INTO `horario` VALUES("433","89","2023-08-28","10:00:00","12:00:00","2023-10-20","viernes");
INSERT INTO `horario` VALUES("435","96","2023-09-04","08:00:00","10:00:00","2023-11-28","jueves");
INSERT INTO `horario` VALUES("436","97","2023-09-16","08:00:00","13:00:00","2023-10-07","sabado");
INSERT INTO `horario` VALUES("437","98","2024-01-29","08:00:00","10:00:00","2024-04-20","lunes");
INSERT INTO `horario` VALUES("438","101","2024-01-29","18:30:00","20:30:00","2024-04-20","lunes");
INSERT INTO `horario` VALUES("442","105","2024-07-02","11:30:00","00:30:00","2024-09-13","lunes");
INSERT INTO `horario` VALUES("443","106","2024-07-02","12:30:00","13:30:00","2024-09-13","lunes");
INSERT INTO `horario` VALUES("448","111","2024-07-02","07:00:00","09:00:00","2024-09-13","miercoles");
INSERT INTO `horario` VALUES("449","112","2024-07-02","09:00:00","11:00:00","2024-09-13","miercoles");
INSERT INTO `horario` VALUES("450","113","2024-07-02","11:30:00","12:30:00","2024-09-13","miercoles");
INSERT INTO `horario` VALUES("451","114","2024-07-02","12:30:00","13:30:00","2024-09-13","miercoles");
INSERT INTO `horario` VALUES("452","115","2024-07-02","09:15:00","11:15:00","2024-09-13","jueves");
INSERT INTO `horario` VALUES("453","109","2024-07-02","11:30:00","12:30:00","2024-09-13","martes");
INSERT INTO `horario` VALUES("454","109","2024-07-02","11:30:00","12:30:00","2024-09-13","jueves");
INSERT INTO `horario` VALUES("455","110","2024-07-02","12:30:00","13:30:00","2024-09-13","martes");
INSERT INTO `horario` VALUES("456","110","2024-07-02","12:30:00","13:30:00","2024-09-13","jueves");
INSERT INTO `horario` VALUES("457","108","2024-07-02","09:15:00","11:15:00","2024-09-13","martes");
INSERT INTO `horario` VALUES("458","108","2024-07-02","09:15:00","11:15:00","2024-09-13","viernes");
INSERT INTO `horario` VALUES("459","107","2024-07-02","07:00:00","09:00:00","2024-09-13","martes");
INSERT INTO `horario` VALUES("460","107","2024-07-02","07:00:00","09:00:00","2024-09-13","viernes");
INSERT INTO `horario` VALUES("461","116","2024-07-02","11:30:00","12:30:00","2024-09-13","viernes");
INSERT INTO `horario` VALUES("462","117","2024-07-02","12:30:00","13:30:00","2024-09-13","viernes");
INSERT INTO `horario` VALUES("463","103","2024-06-02","07:00:00","09:00:00","2024-09-13","lunes");
INSERT INTO `horario` VALUES("464","103","2024-06-02","07:00:00","09:00:00","2024-09-13","jueves");
INSERT INTO `horario` VALUES("465","16","2025-01-20","07:00:00","09:00:00","2025-03-31","lunes");
INSERT INTO `horario` VALUES("471","14","2025-01-20","11:30:00","12:30:00","2025-03-31","lunes");
INSERT INTO `horario` VALUES("474","10","2025-01-20","08:00:00","09:00:00","2025-03-31","martes");
INSERT INTO `horario` VALUES("475","10","2025-01-20","07:00:00","08:00:00","2025-03-31","jueves");
INSERT INTO `horario` VALUES("476","9","2025-01-20","07:00:00","09:00:00","2025-03-31","martes");
INSERT INTO `horario` VALUES("477","9","2025-01-20","08:00:00","09:00:00","2025-03-31","jueves");
INSERT INTO `horario` VALUES("484","2","2025-01-20","11:30:00","13:30:00","2025-03-31","martes");
INSERT INTO `horario` VALUES("485","2","2025-01-20","11:30:00","13:30:00","2025-03-31","jueves");
INSERT INTO `horario` VALUES("488","12","2025-01-20","07:00:00","09:00:00","2025-03-03","miercoles");
INSERT INTO `horario` VALUES("491","6","2025-01-20","11:15:00","00:15:00","2025-03-31","miercoles");
INSERT INTO `horario` VALUES("492","15","2025-01-20","07:00:00","09:00:00","2025-03-31","viernes");
INSERT INTO `horario` VALUES("493","7","2025-01-20","09:30:00","10:30:00","2025-03-31","viernes");
INSERT INTO `horario` VALUES("494","8","2025-01-20","10:15:00","11:15:00","2025-03-31","viernes");
INSERT INTO `horario` VALUES("495","11","2025-01-20","09:15:00","11:30:00","2025-03-31","miercoles");
INSERT INTO `horario` VALUES("497","1","2025-01-20","09:30:00","11:30:00","2025-03-31","martes");
INSERT INTO `horario` VALUES("498","1","2025-01-20","09:30:00","11:30:00","2025-03-31","jueves");
INSERT INTO `horario` VALUES("499","13","2025-01-20","12:30:00","13:30:00","2025-03-31","lunes");
INSERT INTO `horario` VALUES("500","3","2025-01-20","09:15:00","11:15:00","2025-03-31","lunes");
INSERT INTO `horario` VALUES("501","3","2025-01-20","11:30:00","13:30:00","2025-03-31","viernes");
INSERT INTO `horario` VALUES("502","5","2025-01-20","12:30:00","13:30:00","2025-03-31","miercoles");



DROP TABLE IF EXISTS `iconos`;

CREATE TABLE `iconos` (
  `id_iconos` int(11) NOT NULL,
  `icono` varchar(120) NOT NULL,
  `imagen_icono` varchar(120) NOT NULL,
  PRIMARY KEY (`id_iconos`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

INSERT INTO `iconos` VALUES("0","Estadisticas","45-451601_pie-chart-computer-icons-graph-of-a-function.png");
INSERT INTO `iconos` VALUES("1","add-2","add-2.png");
INSERT INTO `iconos` VALUES("2","add-3","add-3.png");
INSERT INTO `iconos` VALUES("3","add","add.png");
INSERT INTO `iconos` VALUES("4","adicionar","adicionar.png");
INSERT INTO `iconos` VALUES("5","agenda","agenda.png");
INSERT INTO `iconos` VALUES("6","alarm-1","alarm-1.png");
INSERT INTO `iconos` VALUES("7","alarm-clock-1","alarm-clock-1.png");
INSERT INTO `iconos` VALUES("8","alarm-clock","alarm-clock.png");
INSERT INTO `iconos` VALUES("9","alarm","alarm.png");
INSERT INTO `iconos` VALUES("10","albums","albums.png");
INSERT INTO `iconos` VALUES("11","app","app.png");
INSERT INTO `iconos` VALUES("12","apple","apple.png");
INSERT INTO `iconos` VALUES("13","archive-1","archive-1.png");
INSERT INTO `iconos` VALUES("14","archive-2","archive-2.png");
INSERT INTO `iconos` VALUES("15","archive-3","archive-3.png");
INSERT INTO `iconos` VALUES("16","archive","archive.png");
INSERT INTO `iconos` VALUES("17","attachment","attachment.png");
INSERT INTO `iconos` VALUES("18","audio","audio.png");
INSERT INTO `iconos` VALUES("19","audiobook-1","audiobook-1.png");
INSERT INTO `iconos` VALUES("20","audiobook","audiobook.png");
INSERT INTO `iconos` VALUES("21","back","back.png");
INSERT INTO `iconos` VALUES("22","battery-1","battery-1.png");
INSERT INTO `iconos` VALUES("23","battery-2","battery-2.png");
INSERT INTO `iconos` VALUES("24","battery-3","battery-3.png");
INSERT INTO `iconos` VALUES("25","battery-4","battery-4.png");
INSERT INTO `iconos` VALUES("26","battery-5","battery-5.png");
INSERT INTO `iconos` VALUES("27","battery-6","battery-6.png");
INSERT INTO `iconos` VALUES("28","battery-7","battery-7.png");
INSERT INTO `iconos` VALUES("29","battery-8","battery-8.png");
INSERT INTO `iconos` VALUES("30","battery-9","battery-9.png");
INSERT INTO `iconos` VALUES("31","battery","battery.png");
INSERT INTO `iconos` VALUES("32","binoculars","binoculars.png");
INSERT INTO `iconos` VALUES("33","blueprint","blueprint.png");
INSERT INTO `iconos` VALUES("34","bluetooth-1","bluetooth-1.png");
INSERT INTO `iconos` VALUES("35","bluetooth","bluetooth.png");
INSERT INTO `iconos` VALUES("36","book","book.png");
INSERT INTO `iconos` VALUES("37","bookmark-1","bookmark-1.png");
INSERT INTO `iconos` VALUES("38","bookmark","bookmark.png");
INSERT INTO `iconos` VALUES("39","briefcase","briefcase.png");
INSERT INTO `iconos` VALUES("40","broken-link","broken-link.png");
INSERT INTO `iconos` VALUES("41","browser-1","browser-1.png");
INSERT INTO `iconos` VALUES("42","browser-2","browser-2.png");
INSERT INTO `iconos` VALUES("43","browser","browser.png");
INSERT INTO `iconos` VALUES("44","calculator-1","calculator-1.png");
INSERT INTO `iconos` VALUES("45","calculator","calculator.png");
INSERT INTO `iconos` VALUES("46","calendar-1","calendar-1.png");
INSERT INTO `iconos` VALUES("47","calendar-2","calendar-2.png");
INSERT INTO `iconos` VALUES("48","calendar-3","calendar-3.png");
INSERT INTO `iconos` VALUES("49","calendar-4","calendar-4.png");
INSERT INTO `iconos` VALUES("50","calendar-5","calendar-5.png");
INSERT INTO `iconos` VALUES("51","calendar-6","calendar-6.png");
INSERT INTO `iconos` VALUES("52","calendar-7","calendar-7.png");
INSERT INTO `iconos` VALUES("53","calendar","calendar.png");
INSERT INTO `iconos` VALUES("54","certificate","certificate.png");
INSERT INTO `iconos` VALUES("55","chat-7","chat-7.png");
INSERT INTO `iconos` VALUES("56","chat-8","chat-8.png");
INSERT INTO `iconos` VALUES("57","chat","chat.png");
INSERT INTO `iconos` VALUES("58","checked-1","checked-1.png");
INSERT INTO `iconos` VALUES("59","checked","checked.png");
INSERT INTO `iconos` VALUES("60","chemistry","chemistry.png");
INSERT INTO `iconos` VALUES("61","chip","chip.png");
INSERT INTO `iconos` VALUES("62","clock-1","clock-1.png");
INSERT INTO `iconos` VALUES("63","clock","clock.png");
INSERT INTO `iconos` VALUES("64","close","close.png");
INSERT INTO `iconos` VALUES("65","cloud-computing-1","cloud-computing-1.png");
INSERT INTO `iconos` VALUES("66","cloud-computing-2","cloud-computing-2.png");
INSERT INTO `iconos` VALUES("67","cloud-computing-3","cloud-computing-3.png");
INSERT INTO `iconos` VALUES("68","cloud-computing-4","cloud-computing-4.png");
INSERT INTO `iconos` VALUES("69","cloud-computing-5","cloud-computing-5.png");
INSERT INTO `iconos` VALUES("70","cloud-computing","cloud-computing.png");
INSERT INTO `iconos` VALUES("71","cloud","cloud.png");
INSERT INTO `iconos` VALUES("72","code","code.png");
INSERT INTO `iconos` VALUES("73","command","command.png");
INSERT INTO `iconos` VALUES("74","compact-disc-1","compact-disc-1.png");
INSERT INTO `iconos` VALUES("75","compact-disc-2","compact-disc-2.png");
INSERT INTO `iconos` VALUES("76","compact-disc","compact-disc.png");
INSERT INTO `iconos` VALUES("77","compass","compass.png");
INSERT INTO `iconos` VALUES("78","compose","compose.png");
INSERT INTO `iconos` VALUES("79","controls-1","controls-1.png");
INSERT INTO `iconos` VALUES("80","controls-2","controls-2.png");
INSERT INTO `iconos` VALUES("81","controls-3","controls-3.png");
INSERT INTO `iconos` VALUES("82","controls-4","controls-4.png");
INSERT INTO `iconos` VALUES("83","controls-5","controls-5.png");
INSERT INTO `iconos` VALUES("84","controls-6","controls-6.png");
INSERT INTO `iconos` VALUES("85","controls-7","controls-7.png");
INSERT INTO `iconos` VALUES("86","controls-8","controls-8.png");
INSERT INTO `iconos` VALUES("87","controls-9","controls-9.png");
INSERT INTO `iconos` VALUES("88","controls","controls.png");
INSERT INTO `iconos` VALUES("89","crm","crm.png");
INSERT INTO `iconos` VALUES("90","database-1","database-1.png");
INSERT INTO `iconos` VALUES("91","database-2","database-2.png");
INSERT INTO `iconos` VALUES("92","database-3","database-3.png");
INSERT INTO `iconos` VALUES("93","database","database.png");
INSERT INTO `iconos` VALUES("94","design","design.png");
INSERT INTO `iconos` VALUES("95","desk","desk.png");
INSERT INTO `iconos` VALUES("96","desktop","desktop.png");
INSERT INTO `iconos` VALUES("97","diamond","diamond.png");
INSERT INTO `iconos` VALUES("98","diploma","diploma.png");
INSERT INTO `iconos` VALUES("99","dislike-1","dislike-1.png");
INSERT INTO `iconos` VALUES("100","dislike","dislike.png");
INSERT INTO `iconos` VALUES("101","divide-1","divide-1.png");
INSERT INTO `iconos` VALUES("102","divide","divide.png");
INSERT INTO `iconos` VALUES("103","division","division.png");
INSERT INTO `iconos` VALUES("104","doc","doc.png");
INSERT INTO `iconos` VALUES("105","document","document.png");
INSERT INTO `iconos` VALUES("106","download","download.png");
INSERT INTO `iconos` VALUES("107","earth-globe","earth-globe.png");
INSERT INTO `iconos` VALUES("108","ebook-1","ebook-1.png");
INSERT INTO `iconos` VALUES("109","ebook-2","ebook-2.png");
INSERT INTO `iconos` VALUES("110","ebook-3","ebook-3.png");
INSERT INTO `iconos` VALUES("111","ebook","ebook.png");
INSERT INTO `iconos` VALUES("112","edit-1","edit-1.png");
INSERT INTO `iconos` VALUES("113","edit","edit.png");
INSERT INTO `iconos` VALUES("114","eject-1","eject-1.png");
INSERT INTO `iconos` VALUES("115","eject","eject.png");
INSERT INTO `iconos` VALUES("117","equal-1","equal-1.png");
INSERT INTO `iconos` VALUES("118","equal-2","equal-2.png");
INSERT INTO `iconos` VALUES("119","equal","equal.png");
INSERT INTO `iconos` VALUES("120","ereader","ereader.png");
INSERT INTO `iconos` VALUES("121","error","error.png");
INSERT INTO `iconos` VALUES("122","exam","exam.png");
INSERT INTO `iconos` VALUES("123","exit-1","exit-1.png");
INSERT INTO `iconos` VALUES("124","exit-2","exit-2.png");
INSERT INTO `iconos` VALUES("125","exit","exit.png");
INSERT INTO `iconos` VALUES("126","eyeglasses","eyeglasses.png");
INSERT INTO `iconos` VALUES("127","fast-forward-1","fast-forward-1.png");
INSERT INTO `iconos` VALUES("128","fast-forward","fast-forward.png");
INSERT INTO `iconos` VALUES("129","fax","fax.png");
INSERT INTO `iconos` VALUES("130","file-1","file-1.png");
INSERT INTO `iconos` VALUES("131","file-2","file-2.png");
INSERT INTO `iconos` VALUES("132","file","file.png");
INSERT INTO `iconos` VALUES("133","film","film.png");
INSERT INTO `iconos` VALUES("134","fingerprint","fingerprint.png");
INSERT INTO `iconos` VALUES("135","flag-1","flag-1.png");
INSERT INTO `iconos` VALUES("136","flag-2","flag-2.png");
INSERT INTO `iconos` VALUES("137","flag-3","flag-3.png");
INSERT INTO `iconos` VALUES("138","flag-4","flag-4.png");
INSERT INTO `iconos` VALUES("139","flag","flag.png");
INSERT INTO `iconos` VALUES("140","focus","focus.png");
INSERT INTO `iconos` VALUES("141","folder-1","folder-1.png");
INSERT INTO `iconos` VALUES("142","folder-10","folder-10.png");
INSERT INTO `iconos` VALUES("143","folder-11","folder-11.png");
INSERT INTO `iconos` VALUES("144","folder-12","folder-12.png");
INSERT INTO `iconos` VALUES("145","folder-13","folder-13.png");
INSERT INTO `iconos` VALUES("146","folder-14","folder-14.png");
INSERT INTO `iconos` VALUES("147","folder-15","folder-15.png");
INSERT INTO `iconos` VALUES("148","folder-16","folder-16.png");
INSERT INTO `iconos` VALUES("149","folder-17","folder-17.png");
INSERT INTO `iconos` VALUES("150","folder-18","folder-18.png");
INSERT INTO `iconos` VALUES("151","folder-19","folder-19.png");
INSERT INTO `iconos` VALUES("152","folder-2","folder-2.png");
INSERT INTO `iconos` VALUES("153","folder-3","folder-3.png");
INSERT INTO `iconos` VALUES("154","folder-4","folder-4.png");
INSERT INTO `iconos` VALUES("155","folder-5","folder-5.png");
INSERT INTO `iconos` VALUES("156","folder-6","folder-6.png");
INSERT INTO `iconos` VALUES("157","folder-7","folder-7.png");
INSERT INTO `iconos` VALUES("158","folder-8","folder-8.png");
INSERT INTO `iconos` VALUES("159","folder-9","folder-9.png");
INSERT INTO `iconos` VALUES("160","folder","folder.png");
INSERT INTO `iconos` VALUES("161","forbidden","forbidden.png");
INSERT INTO `iconos` VALUES("162","foro","foro.png");
INSERT INTO `iconos` VALUES("163","funnel","funnel.png");
INSERT INTO `iconos` VALUES("164","garbage-1","garbage-1.png");
INSERT INTO `iconos` VALUES("165","garbage-2","garbage-2.png");
INSERT INTO `iconos` VALUES("166","garbage","garbage.png");
INSERT INTO `iconos` VALUES("167","geography","geography.png");
INSERT INTO `iconos` VALUES("168","gift","gift.png");
INSERT INTO `iconos` VALUES("169","google-glasses","google-glasses.png");
INSERT INTO `iconos` VALUES("170","help","help.png");
INSERT INTO `iconos` VALUES("171","hide","hide.png");
INSERT INTO `iconos` VALUES("172","hold","hold.png");
INSERT INTO `iconos` VALUES("173","home-1","home-1.png");
INSERT INTO `iconos` VALUES("174","home-2","home-2.png");
INSERT INTO `iconos` VALUES("175","home","home.png");
INSERT INTO `iconos` VALUES("176","homework","homework.png");
INSERT INTO `iconos` VALUES("177","hourglass-1","hourglass-1.png");
INSERT INTO `iconos` VALUES("178","hourglass-2","hourglass-2.png");
INSERT INTO `iconos` VALUES("179","hourglass-3","hourglass-3.png");
INSERT INTO `iconos` VALUES("180","hourglass","hourglass.png");
INSERT INTO `iconos` VALUES("181","house","house.png");
INSERT INTO `iconos` VALUES("182","id-card-1","id-card-1.png");
INSERT INTO `iconos` VALUES("183","id-card-2","id-card-2.png");
INSERT INTO `iconos` VALUES("184","id-card-3","id-card-3.png");
INSERT INTO `iconos` VALUES("185","id-card-4","id-card-4.png");
INSERT INTO `iconos` VALUES("186","id-card-5","id-card-5.png");
INSERT INTO `iconos` VALUES("187","id-card","id-card.png");
INSERT INTO `iconos` VALUES("188","idea","idea.png");
INSERT INTO `iconos` VALUES("189","image","image.png");
INSERT INTO `iconos` VALUES("190","incoming","incoming.png");
INSERT INTO `iconos` VALUES("191","infinity","infinity.png");
INSERT INTO `iconos` VALUES("192","info","info.png");
INSERT INTO `iconos` VALUES("193","information","information.png");
INSERT INTO `iconos` VALUES("194","internet","internet.png");
INSERT INTO `iconos` VALUES("195","key","key.png");
INSERT INTO `iconos` VALUES("196","keyboard","keyboard.png");
INSERT INTO `iconos` VALUES("197","lamp","lamp.png");
INSERT INTO `iconos` VALUES("198","laptop","laptop.png");
INSERT INTO `iconos` VALUES("199","layers-1","layers-1.png");
INSERT INTO `iconos` VALUES("200","layers","layers.png");
INSERT INTO `iconos` VALUES("201","learning-1","learning-1.png");
INSERT INTO `iconos` VALUES("202","learning-2","learning-2.png");
INSERT INTO `iconos` VALUES("203","learning","learning.png");
INSERT INTO `iconos` VALUES("204","lecture","lecture.png");
INSERT INTO `iconos` VALUES("205","library","library.png");
INSERT INTO `iconos` VALUES("206","like-1","like-1.png");
INSERT INTO `iconos` VALUES("207","like-2","like-2.png");
INSERT INTO `iconos` VALUES("208","like","like.png");
INSERT INTO `iconos` VALUES("209","line-15-icons","line-15-icons.png");
INSERT INTO `iconos` VALUES("210","link","link.png");
INSERT INTO `iconos` VALUES("211","list-1","list-1.png");
INSERT INTO `iconos` VALUES("212","list-15","list-15.png");
INSERT INTO `iconos` VALUES("213","list","list.png");
INSERT INTO `iconos` VALUES("214","listening","listening.png");
INSERT INTO `iconos` VALUES("215","lock-1","lock-1.png");
INSERT INTO `iconos` VALUES("216","lock","lock.png");
INSERT INTO `iconos` VALUES("217","locked-1","locked-1.png");
INSERT INTO `iconos` VALUES("218","locked-2","locked-2.png");
INSERT INTO `iconos` VALUES("219","locked-3","locked-3.png");
INSERT INTO `iconos` VALUES("220","locked-4","locked-4.png");
INSERT INTO `iconos` VALUES("221","locked-5","locked-5.png");
INSERT INTO `iconos` VALUES("222","locked-6","locked-6.png");
INSERT INTO `iconos` VALUES("223","locked","locked.png");
INSERT INTO `iconos` VALUES("224","login","login.png");
INSERT INTO `iconos` VALUES("225","magic-wand","magic-wand.png");
INSERT INTO `iconos` VALUES("226","magnet-1","magnet-1.png");
INSERT INTO `iconos` VALUES("227","magnet-2","magnet-2.png");
INSERT INTO `iconos` VALUES("228","magnet","magnet.png");
INSERT INTO `iconos` VALUES("229","map-1","map-1.png");
INSERT INTO `iconos` VALUES("230","map-2","map-2.png");
INSERT INTO `iconos` VALUES("231","map-location","map-location.png");
INSERT INTO `iconos` VALUES("232","map","map.png");
INSERT INTO `iconos` VALUES("233","medal","medal.png");
INSERT INTO `iconos` VALUES("234","megaphone-1","megaphone-1.png");
INSERT INTO `iconos` VALUES("235","megaphone","megaphone.png");
INSERT INTO `iconos` VALUES("236","mensaje","mensaje.png");
INSERT INTO `iconos` VALUES("237","menu-1","menu-1.png");
INSERT INTO `iconos` VALUES("238","menu-2","menu-2.png");
INSERT INTO `iconos` VALUES("239","menu-3","menu-3.png");
INSERT INTO `iconos` VALUES("240","menu-4","menu-4.png");
INSERT INTO `iconos` VALUES("241","menu","menu.png");
INSERT INTO `iconos` VALUES("242","microphone-1","microphone-1.png");
INSERT INTO `iconos` VALUES("243","microphone","microphone.png");
INSERT INTO `iconos` VALUES("244","minus-1","minus-1.png");
INSERT INTO `iconos` VALUES("245","minus","minus.png");
INSERT INTO `iconos` VALUES("246","mkv","mkv.png");
INSERT INTO `iconos` VALUES("247","moneda","moneda.png");
INSERT INTO `iconos` VALUES("248","more-1","more-1.png");
INSERT INTO `iconos` VALUES("249","more-2","more-2.png");
INSERT INTO `iconos` VALUES("250","more","more.png");
INSERT INTO `iconos` VALUES("251","mortarboard","mortarboard.png");
INSERT INTO `iconos` VALUES("252","mouse","mouse.png");
INSERT INTO `iconos` VALUES("253","mp3","mp3.png");
INSERT INTO `iconos` VALUES("254","multiply-1","multiply-1.png");
INSERT INTO `iconos` VALUES("255","multiply","multiply.png");
INSERT INTO `iconos` VALUES("256","music-player-1","music-player-1.png");
INSERT INTO `iconos` VALUES("257","music-player-2","music-player-2.png");
INSERT INTO `iconos` VALUES("258","music-player-3","music-player-3.png");
INSERT INTO `iconos` VALUES("259","music-player","music-player.png");
INSERT INTO `iconos` VALUES("260","mute","mute.png");
INSERT INTO `iconos` VALUES("261","muted","muted.png");
INSERT INTO `iconos` VALUES("262","navigation-1","navigation-1.png");
INSERT INTO `iconos` VALUES("263","navigation","navigation.png");
INSERT INTO `iconos` VALUES("264","network","network.png");
INSERT INTO `iconos` VALUES("265","newspaper","newspaper.png");
INSERT INTO `iconos` VALUES("266","next","next.png");
INSERT INTO `iconos` VALUES("267","note","note.png");
INSERT INTO `iconos` VALUES("268","notebook-1","notebook-1.png");
INSERT INTO `iconos` VALUES("269","notebook-2","notebook-2.png");
INSERT INTO `iconos` VALUES("270","notebook-3","notebook-3.png");
INSERT INTO `iconos` VALUES("271","notebook-4","notebook-4.png");
INSERT INTO `iconos` VALUES("272","notebook-5","notebook-5.png");
INSERT INTO `iconos` VALUES("273","notebook","notebook.png");
INSERT INTO `iconos` VALUES("274","notepad-1","notepad-1.png");
INSERT INTO `iconos` VALUES("275","notepad-2","notepad-2.png");
INSERT INTO `iconos` VALUES("276","notepad","notepad.png");
INSERT INTO `iconos` VALUES("277","notes","notes.png");
INSERT INTO `iconos` VALUES("278","notification","notification.png");
INSERT INTO `iconos` VALUES("279","open-book","open-book.png");
INSERT INTO `iconos` VALUES("280","paper-plane-1","paper-plane-1.png");
INSERT INTO `iconos` VALUES("281","paper-plane","paper-plane.png");
INSERT INTO `iconos` VALUES("282","pause-1","pause-1.png");
INSERT INTO `iconos` VALUES("283","pause","pause.png");
INSERT INTO `iconos` VALUES("284","pdf","pdf.png");
INSERT INTO `iconos` VALUES("285","pendrive","pendrive.png");
INSERT INTO `iconos` VALUES("286","percent-1","percent-1.png");
INSERT INTO `iconos` VALUES("287","percent","percent.png");
INSERT INTO `iconos` VALUES("288","perspective","perspective.png");
INSERT INTO `iconos` VALUES("289","photo-camera-1","photo-camera-1.png");
INSERT INTO `iconos` VALUES("290","photo-camera","photo-camera.png");
INSERT INTO `iconos` VALUES("291","photos","photos.png");
INSERT INTO `iconos` VALUES("292","picture-1","picture-1.png");
INSERT INTO `iconos` VALUES("293","picture-2","picture-2.png");
INSERT INTO `iconos` VALUES("294","picture","picture.png");
INSERT INTO `iconos` VALUES("295","pin","pin.png");
INSERT INTO `iconos` VALUES("296","placeholder-1","placeholder-1.png");
INSERT INTO `iconos` VALUES("297","placeholder-2","placeholder-2.png");
INSERT INTO `iconos` VALUES("298","placeholder-3","placeholder-3.png");
INSERT INTO `iconos` VALUES("299","placeholder","placeholder.png");
INSERT INTO `iconos` VALUES("300","placeholders","placeholders.png");
INSERT INTO `iconos` VALUES("301","play-button-1","play-button-1.png");
INSERT INTO `iconos` VALUES("302","play-button","play-button.png");
INSERT INTO `iconos` VALUES("303","plus","plus.png");
INSERT INTO `iconos` VALUES("304","power","power.png");
INSERT INTO `iconos` VALUES("305","previous","previous.png");
INSERT INTO `iconos` VALUES("306","price-tag","price-tag.png");
INSERT INTO `iconos` VALUES("307","print","print.png");
INSERT INTO `iconos` VALUES("308","professor","professor.png");
INSERT INTO `iconos` VALUES("309","projector","projector.png");
INSERT INTO `iconos` VALUES("310","push-pin","push-pin.png");
INSERT INTO `iconos` VALUES("311","question-1","question-1.png");
INSERT INTO `iconos` VALUES("312","question","question.png");
INSERT INTO `iconos` VALUES("313","questions","questions.png");
INSERT INTO `iconos` VALUES("314","radar","radar.png");
INSERT INTO `iconos` VALUES("315","reading","reading.png");
INSERT INTO `iconos` VALUES("316","record","record.png");
INSERT INTO `iconos` VALUES("317","repeat-1","repeat-1.png");
INSERT INTO `iconos` VALUES("318","repeat","repeat.png");
INSERT INTO `iconos` VALUES("319","restart","restart.png");
INSERT INTO `iconos` VALUES("320","resume","resume.png");
INSERT INTO `iconos` VALUES("321","rewind-1","rewind-1.png");
INSERT INTO `iconos` VALUES("322","rewind","rewind.png");
INSERT INTO `iconos` VALUES("323","route","route.png");
INSERT INTO `iconos` VALUES("324","save","save.png");
INSERT INTO `iconos` VALUES("325","science-book","science-book.png");
INSERT INTO `iconos` VALUES("326","search-1","search-1.png");
INSERT INTO `iconos` VALUES("327","search-engine","search-engine.png");
INSERT INTO `iconos` VALUES("328","search","search.png");
INSERT INTO `iconos` VALUES("329","send","send.png");
INSERT INTO `iconos` VALUES("330","server-1","server-1.png");
INSERT INTO `iconos` VALUES("331","server-2","server-2.png");
INSERT INTO `iconos` VALUES("332","server-3","server-3.png");
INSERT INTO `iconos` VALUES("333","server","server.png");
INSERT INTO `iconos` VALUES("334","settings-1","settings-1.png");
INSERT INTO `iconos` VALUES("335","settings-10","settings-10.png");
INSERT INTO `iconos` VALUES("336","settings-2","settings-2.png");
INSERT INTO `iconos` VALUES("337","settings-3","settings-3.png");
INSERT INTO `iconos` VALUES("338","settings-4","settings-4.png");
INSERT INTO `iconos` VALUES("339","settings-5","settings-5.png");
INSERT INTO `iconos` VALUES("340","settings-6","settings-6.png");
INSERT INTO `iconos` VALUES("341","settings-7","settings-7.png");
INSERT INTO `iconos` VALUES("342","settings-8","settings-8.png");
INSERT INTO `iconos` VALUES("343","settings-9","settings-9.png");
INSERT INTO `iconos` VALUES("344","settings","settings.png");
INSERT INTO `iconos` VALUES("345","share-1","share-1.png");
INSERT INTO `iconos` VALUES("346","share-2","share-2.png");
INSERT INTO `iconos` VALUES("347","share","share.png");
INSERT INTO `iconos` VALUES("348","shopping-cart","shopping-cart.png");
INSERT INTO `iconos` VALUES("349","shuffle-1","shuffle-1.png");
INSERT INTO `iconos` VALUES("350","shuffle","shuffle.png");
INSERT INTO `iconos` VALUES("351","shutdown","shutdown.png");
INSERT INTO `iconos` VALUES("352","sign-1","sign-1.png");
INSERT INTO `iconos` VALUES("353","sign","sign.png");
INSERT INTO `iconos` VALUES("354","skip","skip.png");
INSERT INTO `iconos` VALUES("355","smartphone-1","smartphone-1.png");
INSERT INTO `iconos` VALUES("356","smartphone-10","smartphone-10.png");
INSERT INTO `iconos` VALUES("357","smartphone-11","smartphone-11.png");
INSERT INTO `iconos` VALUES("358","smartphone-2","smartphone-2.png");
INSERT INTO `iconos` VALUES("359","smartphone-3","smartphone-3.png");
INSERT INTO `iconos` VALUES("360","smartphone-4","smartphone-4.png");
INSERT INTO `iconos` VALUES("361","smartphone-5","smartphone-5.png");
INSERT INTO `iconos` VALUES("362","smartphone-6","smartphone-6.png");
INSERT INTO `iconos` VALUES("363","smartphone-7","smartphone-7.png");
INSERT INTO `iconos` VALUES("364","smartphone-8","smartphone-8.png");
INSERT INTO `iconos` VALUES("365","smartphone-9","smartphone-9.png");
INSERT INTO `iconos` VALUES("366","smartphone","smartphone.png");
INSERT INTO `iconos` VALUES("367","speaker-1","speaker-1.png");
INSERT INTO `iconos` VALUES("368","speaker-2","speaker-2.png");
INSERT INTO `iconos` VALUES("369","speaker-3","speaker-3.png");
INSERT INTO `iconos` VALUES("370","speaker-4","speaker-4.png");
INSERT INTO `iconos` VALUES("371","speaker-5","speaker-5.png");
INSERT INTO `iconos` VALUES("372","speaker-6","speaker-6.png");
INSERT INTO `iconos` VALUES("373","speaker-7","speaker-7.png");
INSERT INTO `iconos` VALUES("374","speaker-8","speaker-8.png");
INSERT INTO `iconos` VALUES("375","speaker-sga","speaker-sga.png");
INSERT INTO `iconos` VALUES("376","speaker","speaker.png");
INSERT INTO `iconos` VALUES("377","speech-bubble-2","speech-bubble-2.png");
INSERT INTO `iconos` VALUES("378","speech-bubble","speech-bubble.png");
INSERT INTO `iconos` VALUES("379","spotlight","spotlight.png");
INSERT INTO `iconos` VALUES("380","star-1","star-1.png");
INSERT INTO `iconos` VALUES("381","star","star.png");
INSERT INTO `iconos` VALUES("382","statistics","statistics.png");
INSERT INTO `iconos` VALUES("383","stop-1","stop-1.png");
INSERT INTO `iconos` VALUES("384","stop","stop.png");
INSERT INTO `iconos` VALUES("385","stopwatch-1","stopwatch-1.png");
INSERT INTO `iconos` VALUES("386","stopwatch-2","stopwatch-2.png");
INSERT INTO `iconos` VALUES("387","stopwatch-3","stopwatch-3.png");
INSERT INTO `iconos` VALUES("388","stopwatch-4","stopwatch-4.png");
INSERT INTO `iconos` VALUES("389","stopwatch","stopwatch.png");
INSERT INTO `iconos` VALUES("390","street-1","street-1.png");
INSERT INTO `iconos` VALUES("391","street","street.png");
INSERT INTO `iconos` VALUES("392","student-1","student-1.png");
INSERT INTO `iconos` VALUES("393","student-2","student-2.png");
INSERT INTO `iconos` VALUES("394","Estudiante ","student-3.png");
INSERT INTO `iconos` VALUES("395","student","student.png");
INSERT INTO `iconos` VALUES("396","substract-1","substract-1.png");
INSERT INTO `iconos` VALUES("397","substract","substract.png");
INSERT INTO `iconos` VALUES("398","success","success.png");
INSERT INTO `iconos` VALUES("399","switch-1","switch-1.png");
INSERT INTO `iconos` VALUES("400","switch-2","switch-2.png");
INSERT INTO `iconos` VALUES("401","switch-3","switch-3.png");
INSERT INTO `iconos` VALUES("402","switch-4","switch-4.png");
INSERT INTO `iconos` VALUES("403","switch-5","switch-5.png");
INSERT INTO `iconos` VALUES("404","switch-6","switch-6.png");
INSERT INTO `iconos` VALUES("405","switch-7","switch-7.png");
INSERT INTO `iconos` VALUES("406","switch","switch.png");
INSERT INTO `iconos` VALUES("407","tablet","tablet.png");
INSERT INTO `iconos` VALUES("408","tabs-1","tabs-1.png");
INSERT INTO `iconos` VALUES("409","tabs","tabs.png");
INSERT INTO `iconos` VALUES("410","target","target.png");
INSERT INTO `iconos` VALUES("411","television-1","television-1.png");
INSERT INTO `iconos` VALUES("412","television","television.png");
INSERT INTO `iconos` VALUES("413","test-1","test-1.png");
INSERT INTO `iconos` VALUES("414","test-2","test-2.png");
INSERT INTO `iconos` VALUES("415","test-3","test-3.png");
INSERT INTO `iconos` VALUES("416","test","test.png");
INSERT INTO `iconos` VALUES("417","time","time.png");
INSERT INTO `iconos` VALUES("418","touchscreen","touchscreen.png");
INSERT INTO `iconos` VALUES("419","translator","translator.png");
INSERT INTO `iconos` VALUES("420","trash","trash.png");
INSERT INTO `iconos` VALUES("421","tutorial","tutorial.png");
INSERT INTO `iconos` VALUES("422","txt","txt.png");
INSERT INTO `iconos` VALUES("423","umbrella","umbrella.png");
INSERT INTO `iconos` VALUES("424","university-1","university-1.png");
INSERT INTO `iconos` VALUES("425","university","university.png");
INSERT INTO `iconos` VALUES("426","unlink","unlink.png");
INSERT INTO `iconos` VALUES("427","unlocked-1","unlocked-1.png");
INSERT INTO `iconos` VALUES("428","unlocked-2","unlocked-2.png");
INSERT INTO `iconos` VALUES("429","unlocked","unlocked.png");
INSERT INTO `iconos` VALUES("430","upload","upload.png");
INSERT INTO `iconos` VALUES("431","user-1","user-1.png");
INSERT INTO `iconos` VALUES("432","user-2","user-2.png");
INSERT INTO `iconos` VALUES("433","user-3","user-3.png");
INSERT INTO `iconos` VALUES("434","user-4","user-4.png");
INSERT INTO `iconos` VALUES("435","user-5","user-5.png");
INSERT INTO `iconos` VALUES("436","user-6","user-6.png");
INSERT INTO `iconos` VALUES("437","user-7","user-7.png");
INSERT INTO `iconos` VALUES("438","user","user.png");
INSERT INTO `iconos` VALUES("439","users-1","users-1.png");
INSERT INTO `iconos` VALUES("440","users","users.png");
INSERT INTO `iconos` VALUES("441","verification","verification.png");
INSERT INTO `iconos` VALUES("442","video-call","video-call.png");
INSERT INTO `iconos` VALUES("443","video-camera-1","video-camera-1.png");
INSERT INTO `iconos` VALUES("444","video-camera","video-camera.png");
INSERT INTO `iconos` VALUES("445","video-player-1","video-player-1.png");
INSERT INTO `iconos` VALUES("446","video-player-2","video-player-2.png");
INSERT INTO `iconos` VALUES("447","video-player","video-player.png");
INSERT INTO `iconos` VALUES("448","view-1","view-1.png");
INSERT INTO `iconos` VALUES("449","view-2","view-2.png");
INSERT INTO `iconos` VALUES("450","view-line","view-line.png");
INSERT INTO `iconos` VALUES("451","view","view.png");
INSERT INTO `iconos` VALUES("452","volume-control-1","volume-control-1.png");
INSERT INTO `iconos` VALUES("453","volume-control","volume-control.png");
INSERT INTO `iconos` VALUES("454","warning","warning.png");
INSERT INTO `iconos` VALUES("455","wifi-1","wifi-1.png");
INSERT INTO `iconos` VALUES("456","wifi","wifi.png");
INSERT INTO `iconos` VALUES("457","windows-1","windows-1.png");
INSERT INTO `iconos` VALUES("458","windows-2","windows-2.png");
INSERT INTO `iconos` VALUES("459","windows-3","windows-3.png");
INSERT INTO `iconos` VALUES("460","windows-4","windows-4.png");
INSERT INTO `iconos` VALUES("461","windows","windows.png");
INSERT INTO `iconos` VALUES("462","wireless-internet","wireless-internet.png");
INSERT INTO `iconos` VALUES("463","worldwide-1","worldwide-1.png");
INSERT INTO `iconos` VALUES("464","worldwide","worldwide.png");
INSERT INTO `iconos` VALUES("465","zoom-in","zoom-in.png");
INSERT INTO `iconos` VALUES("466","zoom-out","zoom-out.png");
INSERT INTO `iconos` VALUES("467","abacus","abacus.png");
INSERT INTO `iconos` VALUES("468","american-football","american-football.png");
INSERT INTO `iconos` VALUES("469","apple.1","apple.1.png");
INSERT INTO `iconos` VALUES("470","baseball","baseball.png");
INSERT INTO `iconos` VALUES("471","basketball","basketball.png");
INSERT INTO `iconos` VALUES("472","bell","bell.png");
INSERT INTO `iconos` VALUES("473","books","books.png");
INSERT INTO `iconos` VALUES("474","bookshelf","bookshelf.png");
INSERT INTO `iconos` VALUES("475","calculator-1.1","calculator-1.1.png");
INSERT INTO `iconos` VALUES("476","calculator.1","calculator.1.png");
INSERT INTO `iconos` VALUES("477","calendar.1","calendar.1.png");
INSERT INTO `iconos` VALUES("478","calendario","calendario.png");
INSERT INTO `iconos` VALUES("479","chalkboard-1","chalkboard-1.png");
INSERT INTO `iconos` VALUES("480","chalkboard","chalkboard.png");
INSERT INTO `iconos` VALUES("481","chemistry-1","chemistry-1.png");
INSERT INTO `iconos` VALUES("482","chemistry.1","chemistry.1.png");
INSERT INTO `iconos` VALUES("483","Reloj","clock.1.png");
INSERT INTO `iconos` VALUES("484","college","college.png");
INSERT INTO `iconos` VALUES("485","compact-disc.1","compact-disc.1.png");
INSERT INTO `iconos` VALUES("486","compass.1","compass.1.png");
INSERT INTO `iconos` VALUES("487","computer","computer.png");
INSERT INTO `iconos` VALUES("488","crayon","crayon.png");
INSERT INTO `iconos` VALUES("489","cup","cup.png");
INSERT INTO `iconos` VALUES("490","diploma-1","diploma-1.png");
INSERT INTO `iconos` VALUES("491","diploma-2","diploma-2.png");
INSERT INTO `iconos` VALUES("492","diploma-3","diploma-3.png");
INSERT INTO `iconos` VALUES("493","diploma.1","diploma.1.png");
INSERT INTO `iconos` VALUES("494","earth-globe-1","earth-globe-1.png");
INSERT INTO `iconos` VALUES("495","earth-globe.1","earth-globe.1.png");
INSERT INTO `iconos` VALUES("496","foro.1","foro.1.png");
INSERT INTO `iconos` VALUES("497","glue","glue.png");
INSERT INTO `iconos` VALUES("498","marker","marker.png");
INSERT INTO `iconos` VALUES("499","mensaje","mensaje.1.png");
INSERT INTO `iconos` VALUES("500","microscope","microscope.png");
INSERT INTO `iconos` VALUES("501","Grado","mortarboard.1.png");
INSERT INTO `iconos` VALUES("502","notebook-1.1","notebook-1.1.png");
INSERT INTO `iconos` VALUES("503","Agenda","notebooka.png");
INSERT INTO `iconos` VALUES("504","open-book.1","open-book.1.png");
INSERT INTO `iconos` VALUES("505","paint-palette","paint-palette.png");
INSERT INTO `iconos` VALUES("506","pen","pen.png");
INSERT INTO `iconos` VALUES("507","pencil","pencil.png");
INSERT INTO `iconos` VALUES("508","Usb","pendrive.1.png");
INSERT INTO `iconos` VALUES("509","regalo","regalo.png");
INSERT INTO `iconos` VALUES("510","research-1","research-1.png");
INSERT INTO `iconos` VALUES("511","research","research.png");
INSERT INTO `iconos` VALUES("512","ruler","ruler.png");
INSERT INTO `iconos` VALUES("513","school-bus","school-bus.png");
INSERT INTO `iconos` VALUES("514","school-material","school-material.png");
INSERT INTO `iconos` VALUES("515","science","science.png");
INSERT INTO `iconos` VALUES("516","Tijera","scissors.png");
INSERT INTO `iconos` VALUES("517","set-square","set-square.png");
INSERT INTO `iconos` VALUES("518","sharpener","sharpener.png");
INSERT INTO `iconos` VALUES("519","Estadisticas","statistics.1.png");
INSERT INTO `iconos` VALUES("520","telescope","telescope.png");



DROP TABLE IF EXISTS `inscripcion`;

CREATE TABLE `inscripcion` (
  `id_inscripcion` int(16) NOT NULL AUTO_INCREMENT,
  `id_asignacion` int(10) NOT NULL,
  `id_estudiante` varchar(255) NOT NULL,
  `fecha_inscripcion` date NOT NULL,
  `estado_inscripcion` enum('Aprobado','No aprobado','En curso','Retirado') NOT NULL,
  PRIMARY KEY (`id_inscripcion`)
) ENGINE=InnoDB AUTO_INCREMENT=286 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

INSERT INTO `inscripcion` VALUES("1","1","N1","2025-02-07","En curso");
INSERT INTO `inscripcion` VALUES("2","3","N1","2025-02-07","En curso");
INSERT INTO `inscripcion` VALUES("3","5","N1","2025-02-07","En curso");
INSERT INTO `inscripcion` VALUES("4","7","N1","2025-02-07","En curso");
INSERT INTO `inscripcion` VALUES("5","9","N1","2025-02-07","En curso");
INSERT INTO `inscripcion` VALUES("6","11","N1","2025-02-07","En curso");
INSERT INTO `inscripcion` VALUES("7","13","N1","2025-02-07","En curso");
INSERT INTO `inscripcion` VALUES("8","1","2","2025-02-07","En curso");
INSERT INTO `inscripcion` VALUES("9","3","2","2025-02-07","En curso");
INSERT INTO `inscripcion` VALUES("10","5","2","2025-02-07","En curso");
INSERT INTO `inscripcion` VALUES("11","7","2","2025-02-07","En curso");
INSERT INTO `inscripcion` VALUES("12","9","2","2025-02-07","En curso");
INSERT INTO `inscripcion` VALUES("13","11","2","2025-02-07","En curso");
INSERT INTO `inscripcion` VALUES("14","13","2","2025-02-07","En curso");
INSERT INTO `inscripcion` VALUES("15","1","3","2025-02-07","En curso");
INSERT INTO `inscripcion` VALUES("16","3","3","2025-02-07","En curso");
INSERT INTO `inscripcion` VALUES("17","5","3","2025-02-07","En curso");
INSERT INTO `inscripcion` VALUES("18","7","3","2025-02-07","En curso");
INSERT INTO `inscripcion` VALUES("19","9","3","2025-02-07","En curso");
INSERT INTO `inscripcion` VALUES("20","11","3","2025-02-07","En curso");
INSERT INTO `inscripcion` VALUES("21","13","3","2025-02-07","En curso");
INSERT INTO `inscripcion` VALUES("22","1","1058198933","2025-02-07","En curso");
INSERT INTO `inscripcion` VALUES("23","3","1058198933","2025-02-07","En curso");
INSERT INTO `inscripcion` VALUES("24","5","1058198933","2025-02-07","En curso");
INSERT INTO `inscripcion` VALUES("25","7","1058198933","2025-02-07","En curso");
INSERT INTO `inscripcion` VALUES("26","9","1058198933","2025-02-07","En curso");
INSERT INTO `inscripcion` VALUES("27","11","1058198933","2025-02-07","En curso");
INSERT INTO `inscripcion` VALUES("28","13","1058198933","2025-02-07","En curso");
INSERT INTO `inscripcion` VALUES("29","1","5","2025-02-07","En curso");
INSERT INTO `inscripcion` VALUES("30","3","5","2025-02-07","En curso");
INSERT INTO `inscripcion` VALUES("31","5","5","2025-02-07","En curso");
INSERT INTO `inscripcion` VALUES("32","7","5","2025-02-07","En curso");
INSERT INTO `inscripcion` VALUES("33","9","5","2025-02-07","En curso");
INSERT INTO `inscripcion` VALUES("34","11","5","2025-02-07","En curso");
INSERT INTO `inscripcion` VALUES("35","13","5","2025-02-07","En curso");
INSERT INTO `inscripcion` VALUES("36","1","6","2025-02-07","En curso");
INSERT INTO `inscripcion` VALUES("37","3","6","2025-02-07","En curso");
INSERT INTO `inscripcion` VALUES("38","5","6","2025-02-07","En curso");
INSERT INTO `inscripcion` VALUES("39","7","6","2025-02-07","En curso");
INSERT INTO `inscripcion` VALUES("40","9","6","2025-02-07","En curso");
INSERT INTO `inscripcion` VALUES("41","11","6","2025-02-07","En curso");
INSERT INTO `inscripcion` VALUES("42","13","6","2025-02-07","En curso");
INSERT INTO `inscripcion` VALUES("43","1","1036258563","2025-02-07","En curso");
INSERT INTO `inscripcion` VALUES("44","3","1036258563","2025-02-07","En curso");
INSERT INTO `inscripcion` VALUES("45","5","1036258563","2025-02-07","En curso");
INSERT INTO `inscripcion` VALUES("46","7","1036258563","2025-02-07","En curso");
INSERT INTO `inscripcion` VALUES("47","9","1036258563","2025-02-07","En curso");
INSERT INTO `inscripcion` VALUES("48","11","1036258563","2025-02-07","En curso");
INSERT INTO `inscripcion` VALUES("49","13","1036258563","2025-02-07","En curso");
INSERT INTO `inscripcion` VALUES("50","1","8","2025-02-07","En curso");
INSERT INTO `inscripcion` VALUES("51","3","8","2025-02-07","En curso");
INSERT INTO `inscripcion` VALUES("52","5","8","2025-02-07","En curso");
INSERT INTO `inscripcion` VALUES("53","7","8","2025-02-07","En curso");
INSERT INTO `inscripcion` VALUES("54","9","8","2025-02-07","En curso");
INSERT INTO `inscripcion` VALUES("55","11","8","2025-02-07","En curso");
INSERT INTO `inscripcion` VALUES("56","13","8","2025-02-07","En curso");
INSERT INTO `inscripcion` VALUES("57","1","9","2025-02-07","En curso");
INSERT INTO `inscripcion` VALUES("58","3","9","2025-02-07","En curso");
INSERT INTO `inscripcion` VALUES("59","5","9","2025-02-07","En curso");
INSERT INTO `inscripcion` VALUES("60","7","9","2025-02-07","En curso");
INSERT INTO `inscripcion` VALUES("61","9","9","2025-02-07","En curso");
INSERT INTO `inscripcion` VALUES("62","11","9","2025-02-07","En curso");
INSERT INTO `inscripcion` VALUES("63","13","9","2025-02-07","En curso");
INSERT INTO `inscripcion` VALUES("64","1","1115576678","2025-02-07","En curso");
INSERT INTO `inscripcion` VALUES("65","3","1115576678","2025-02-07","En curso");
INSERT INTO `inscripcion` VALUES("66","5","1115576678","2025-02-07","En curso");
INSERT INTO `inscripcion` VALUES("67","7","1115576678","2025-02-07","En curso");
INSERT INTO `inscripcion` VALUES("68","9","1115576678","2025-02-07","En curso");
INSERT INTO `inscripcion` VALUES("69","11","1115576678","2025-02-07","En curso");
INSERT INTO `inscripcion` VALUES("70","13","1115576678","2025-02-07","En curso");
INSERT INTO `inscripcion` VALUES("71","1","1066845161","2025-02-07","En curso");
INSERT INTO `inscripcion` VALUES("72","3","1066845161","2025-02-07","En curso");
INSERT INTO `inscripcion` VALUES("73","5","1066845161","2025-02-07","En curso");
INSERT INTO `inscripcion` VALUES("74","7","1066845161","2025-02-07","En curso");
INSERT INTO `inscripcion` VALUES("75","9","1066845161","2025-02-07","En curso");
INSERT INTO `inscripcion` VALUES("76","11","1066845161","2025-02-07","En curso");
INSERT INTO `inscripcion` VALUES("77","13","1066845161","2025-02-07","En curso");
INSERT INTO `inscripcion` VALUES("78","1","1037975509","2025-02-07","En curso");
INSERT INTO `inscripcion` VALUES("79","3","1037975509","2025-02-07","En curso");
INSERT INTO `inscripcion` VALUES("80","5","1037975509","2025-02-07","En curso");
INSERT INTO `inscripcion` VALUES("81","7","1037975509","2025-02-07","En curso");
INSERT INTO `inscripcion` VALUES("82","9","1037975509","2025-02-07","En curso");
INSERT INTO `inscripcion` VALUES("83","11","1037975509","2025-02-07","En curso");
INSERT INTO `inscripcion` VALUES("84","13","1037975509","2025-02-07","En curso");
INSERT INTO `inscripcion` VALUES("85","1","1127955146","2025-02-07","En curso");
INSERT INTO `inscripcion` VALUES("86","3","1127955146","2025-02-07","En curso");
INSERT INTO `inscripcion` VALUES("87","5","1127955146","2025-02-07","En curso");
INSERT INTO `inscripcion` VALUES("88","7","1127955146","2025-02-07","En curso");
INSERT INTO `inscripcion` VALUES("89","9","1127955146","2025-02-07","En curso");
INSERT INTO `inscripcion` VALUES("90","11","1127955146","2025-02-07","En curso");
INSERT INTO `inscripcion` VALUES("91","13","1127955146","2025-02-07","En curso");
INSERT INTO `inscripcion` VALUES("92","1","14","2025-02-07","En curso");
INSERT INTO `inscripcion` VALUES("93","3","14","2025-02-07","En curso");
INSERT INTO `inscripcion` VALUES("94","5","14","2025-02-07","En curso");
INSERT INTO `inscripcion` VALUES("95","7","14","2025-02-07","En curso");
INSERT INTO `inscripcion` VALUES("96","9","14","2025-02-07","En curso");
INSERT INTO `inscripcion` VALUES("97","11","14","2025-02-07","En curso");
INSERT INTO `inscripcion` VALUES("98","13","14","2025-02-07","En curso");
INSERT INTO `inscripcion` VALUES("99","1","1050038422","2025-02-07","En curso");
INSERT INTO `inscripcion` VALUES("100","3","1050038422","2025-02-07","En curso");
INSERT INTO `inscripcion` VALUES("101","5","1050038422","2025-02-07","En curso");
INSERT INTO `inscripcion` VALUES("102","7","1050038422","2025-02-07","En curso");
INSERT INTO `inscripcion` VALUES("103","9","1050038422","2025-02-07","En curso");
INSERT INTO `inscripcion` VALUES("104","11","1050038422","2025-02-07","En curso");
INSERT INTO `inscripcion` VALUES("105","13","1050038422","2025-02-07","En curso");
INSERT INTO `inscripcion` VALUES("106","1","1037974417","2025-02-07","En curso");
INSERT INTO `inscripcion` VALUES("107","3","1037974417","2025-02-07","En curso");
INSERT INTO `inscripcion` VALUES("108","5","1037974417","2025-02-07","En curso");
INSERT INTO `inscripcion` VALUES("109","7","1037974417","2025-02-07","En curso");
INSERT INTO `inscripcion` VALUES("110","9","1037974417","2025-02-07","En curso");
INSERT INTO `inscripcion` VALUES("111","11","1037974417","2025-02-07","En curso");
INSERT INTO `inscripcion` VALUES("112","13","1037974417","2025-02-07","En curso");
INSERT INTO `inscripcion` VALUES("128","2","1127955147","2025-02-07","En curso");
INSERT INTO `inscripcion` VALUES("129","4","1127955147","2025-02-07","En curso");
INSERT INTO `inscripcion` VALUES("130","6","1127955147","2025-02-07","En curso");
INSERT INTO `inscripcion` VALUES("131","8","1127955147","2025-02-07","En curso");
INSERT INTO `inscripcion` VALUES("132","10","1127955147","2025-02-07","En curso");
INSERT INTO `inscripcion` VALUES("133","12","1127955147","2025-02-07","En curso");
INSERT INTO `inscripcion` VALUES("134","14","1127955147","2025-02-07","En curso");
INSERT INTO `inscripcion` VALUES("135","2","1037974770","2025-02-07","En curso");
INSERT INTO `inscripcion` VALUES("136","4","1037974770","2025-02-07","En curso");
INSERT INTO `inscripcion` VALUES("137","6","1037974770","2025-02-07","En curso");
INSERT INTO `inscripcion` VALUES("138","8","1037974770","2025-02-07","En curso");
INSERT INTO `inscripcion` VALUES("139","10","1037974770","2025-02-07","En curso");
INSERT INTO `inscripcion` VALUES("140","12","1037974770","2025-02-07","En curso");
INSERT INTO `inscripcion` VALUES("141","14","1037974770","2025-02-07","En curso");
INSERT INTO `inscripcion` VALUES("142","2","1131284071","2025-02-07","En curso");
INSERT INTO `inscripcion` VALUES("143","4","1131284071","2025-02-07","En curso");
INSERT INTO `inscripcion` VALUES("144","6","1131284071","2025-02-07","En curso");
INSERT INTO `inscripcion` VALUES("145","8","1131284071","2025-02-07","En curso");
INSERT INTO `inscripcion` VALUES("146","10","1131284071","2025-02-07","En curso");
INSERT INTO `inscripcion` VALUES("147","12","1131284071","2025-02-07","En curso");
INSERT INTO `inscripcion` VALUES("148","14","1131284071","2025-02-07","En curso");
INSERT INTO `inscripcion` VALUES("149","2","1036937985","2025-02-07","En curso");
INSERT INTO `inscripcion` VALUES("150","4","1036937985","2025-02-07","En curso");
INSERT INTO `inscripcion` VALUES("151","6","1036937985","2025-02-07","En curso");
INSERT INTO `inscripcion` VALUES("152","8","1036937985","2025-02-07","En curso");
INSERT INTO `inscripcion` VALUES("153","10","1036937985","2025-02-07","En curso");
INSERT INTO `inscripcion` VALUES("154","12","1036937985","2025-02-07","En curso");
INSERT INTO `inscripcion` VALUES("155","14","1036937985","2025-02-07","En curso");
INSERT INTO `inscripcion` VALUES("156","2","1037974896","2025-02-07","En curso");
INSERT INTO `inscripcion` VALUES("157","4","1037974896","2025-02-07","En curso");
INSERT INTO `inscripcion` VALUES("158","6","1037974896","2025-02-07","En curso");
INSERT INTO `inscripcion` VALUES("159","8","1037974896","2025-02-07","En curso");
INSERT INTO `inscripcion` VALUES("160","10","1037974896","2025-02-07","En curso");
INSERT INTO `inscripcion` VALUES("161","12","1037974896","2025-02-07","En curso");
INSERT INTO `inscripcion` VALUES("162","14","1037974896","2025-02-07","En curso");
INSERT INTO `inscripcion` VALUES("163","2","1022006639","2025-02-07","En curso");
INSERT INTO `inscripcion` VALUES("164","4","1022006639","2025-02-07","En curso");
INSERT INTO `inscripcion` VALUES("165","6","1022006639","2025-02-07","En curso");
INSERT INTO `inscripcion` VALUES("166","8","1022006639","2025-02-07","En curso");
INSERT INTO `inscripcion` VALUES("167","10","1022006639","2025-02-07","En curso");
INSERT INTO `inscripcion` VALUES("168","12","1022006639","2025-02-07","En curso");
INSERT INTO `inscripcion` VALUES("169","14","1022006639","2025-02-07","En curso");
INSERT INTO `inscripcion` VALUES("170","2","23","2025-02-07","En curso");
INSERT INTO `inscripcion` VALUES("171","4","23","2025-02-07","En curso");
INSERT INTO `inscripcion` VALUES("172","6","23","2025-02-07","En curso");
INSERT INTO `inscripcion` VALUES("173","8","23","2025-02-07","En curso");
INSERT INTO `inscripcion` VALUES("174","10","23","2025-02-07","En curso");
INSERT INTO `inscripcion` VALUES("175","12","23","2025-02-07","En curso");
INSERT INTO `inscripcion` VALUES("176","14","23","2025-02-07","En curso");
INSERT INTO `inscripcion` VALUES("177","2","1045396829","2025-02-07","En curso");
INSERT INTO `inscripcion` VALUES("178","4","1045396829","2025-02-07","En curso");
INSERT INTO `inscripcion` VALUES("179","6","1045396829","2025-02-07","En curso");
INSERT INTO `inscripcion` VALUES("180","8","1045396829","2025-02-07","En curso");
INSERT INTO `inscripcion` VALUES("181","10","1045396829","2025-02-07","En curso");
INSERT INTO `inscripcion` VALUES("182","12","1045396829","2025-02-07","En curso");
INSERT INTO `inscripcion` VALUES("183","14","1045396829","2025-02-07","En curso");
INSERT INTO `inscripcion` VALUES("184","2","25","2025-02-07","En curso");
INSERT INTO `inscripcion` VALUES("185","4","25","2025-02-07","En curso");
INSERT INTO `inscripcion` VALUES("186","6","25","2025-02-07","En curso");
INSERT INTO `inscripcion` VALUES("187","8","25","2025-02-07","En curso");
INSERT INTO `inscripcion` VALUES("188","10","25","2025-02-07","En curso");
INSERT INTO `inscripcion` VALUES("189","12","25","2025-02-07","En curso");
INSERT INTO `inscripcion` VALUES("190","14","25","2025-02-07","En curso");
INSERT INTO `inscripcion` VALUES("191","2","1045626133","2025-02-07","En curso");
INSERT INTO `inscripcion` VALUES("192","4","1045626133","2025-02-07","En curso");
INSERT INTO `inscripcion` VALUES("193","6","1045626133","2025-02-07","En curso");
INSERT INTO `inscripcion` VALUES("194","8","1045626133","2025-02-07","En curso");
INSERT INTO `inscripcion` VALUES("195","10","1045626133","2025-02-07","En curso");
INSERT INTO `inscripcion` VALUES("196","12","1045626133","2025-02-07","En curso");
INSERT INTO `inscripcion` VALUES("197","14","1045626133","2025-02-07","En curso");
INSERT INTO `inscripcion` VALUES("198","2","1018236286","2025-02-07","En curso");
INSERT INTO `inscripcion` VALUES("199","4","1018236286","2025-02-07","En curso");
INSERT INTO `inscripcion` VALUES("200","6","1018236286","2025-02-07","En curso");
INSERT INTO `inscripcion` VALUES("201","8","1018236286","2025-02-07","En curso");
INSERT INTO `inscripcion` VALUES("202","10","1018236286","2025-02-07","En curso");
INSERT INTO `inscripcion` VALUES("203","12","1018236286","2025-02-07","En curso");
INSERT INTO `inscripcion` VALUES("204","14","1018236286","2025-02-07","En curso");
INSERT INTO `inscripcion` VALUES("205","2","1045231584","2025-02-07","En curso");
INSERT INTO `inscripcion` VALUES("206","4","1045231584","2025-02-07","En curso");
INSERT INTO `inscripcion` VALUES("207","6","1045231584","2025-02-07","En curso");
INSERT INTO `inscripcion` VALUES("208","8","1045231584","2025-02-07","En curso");
INSERT INTO `inscripcion` VALUES("209","10","1045231584","2025-02-07","En curso");
INSERT INTO `inscripcion` VALUES("210","12","1045231584","2025-02-07","En curso");
INSERT INTO `inscripcion` VALUES("211","14","1045231584","2025-02-07","En curso");
INSERT INTO `inscripcion` VALUES("212","2","1022149324","2025-02-07","En curso");
INSERT INTO `inscripcion` VALUES("213","4","1022149324","2025-02-07","En curso");
INSERT INTO `inscripcion` VALUES("214","6","1022149324","2025-02-07","En curso");
INSERT INTO `inscripcion` VALUES("215","8","1022149324","2025-02-07","En curso");
INSERT INTO `inscripcion` VALUES("216","10","1022149324","2025-02-07","En curso");
INSERT INTO `inscripcion` VALUES("217","12","1022149324","2025-02-07","En curso");
INSERT INTO `inscripcion` VALUES("218","14","1022149324","2025-02-07","En curso");
INSERT INTO `inscripcion` VALUES("219","2","1026565029","2025-02-07","En curso");
INSERT INTO `inscripcion` VALUES("220","4","1026565029","2025-02-07","En curso");
INSERT INTO `inscripcion` VALUES("221","6","1026565029","2025-02-07","En curso");
INSERT INTO `inscripcion` VALUES("222","8","1026565029","2025-02-07","En curso");
INSERT INTO `inscripcion` VALUES("223","10","1026565029","2025-02-07","En curso");
INSERT INTO `inscripcion` VALUES("224","12","1026565029","2025-02-07","En curso");
INSERT INTO `inscripcion` VALUES("225","14","1026565029","2025-02-07","En curso");
INSERT INTO `inscripcion` VALUES("226","2","1036941865","2025-02-07","En curso");
INSERT INTO `inscripcion` VALUES("227","4","1036941865","2025-02-07","En curso");
INSERT INTO `inscripcion` VALUES("228","6","1036941865","2025-02-07","En curso");
INSERT INTO `inscripcion` VALUES("229","8","1036941865","2025-02-07","En curso");
INSERT INTO `inscripcion` VALUES("230","10","1036941865","2025-02-07","En curso");
INSERT INTO `inscripcion` VALUES("231","12","1036941865","2025-02-07","En curso");
INSERT INTO `inscripcion` VALUES("232","14","1036941865","2025-02-07","En curso");
INSERT INTO `inscripcion` VALUES("233","2","1035770602","2025-02-07","En curso");
INSERT INTO `inscripcion` VALUES("234","4","1035770602","2025-02-07","En curso");
INSERT INTO `inscripcion` VALUES("235","6","1035770602","2025-02-07","En curso");
INSERT INTO `inscripcion` VALUES("236","8","1035770602","2025-02-07","En curso");
INSERT INTO `inscripcion` VALUES("237","10","1035770602","2025-02-07","En curso");
INSERT INTO `inscripcion` VALUES("238","12","1035770602","2025-02-07","En curso");
INSERT INTO `inscripcion` VALUES("239","14","1035770602","2025-02-07","En curso");
INSERT INTO `inscripcion` VALUES("255","15","1045396829","2025-02-07","En curso");
INSERT INTO `inscripcion` VALUES("256","16","1045396829","2025-02-07","En curso");
INSERT INTO `inscripcion` VALUES("257","15","25","2025-02-07","En curso");
INSERT INTO `inscripcion` VALUES("258","16","25","2025-02-07","En curso");
INSERT INTO `inscripcion` VALUES("259","15","1045626133","2025-02-07","En curso");
INSERT INTO `inscripcion` VALUES("260","16","1045626133","2025-02-07","En curso");
INSERT INTO `inscripcion` VALUES("261","15","1018236286","2025-02-07","En curso");
INSERT INTO `inscripcion` VALUES("262","16","1018236286","2025-02-07","En curso");
INSERT INTO `inscripcion` VALUES("263","15","1045231584","2025-02-07","En curso");
INSERT INTO `inscripcion` VALUES("264","16","1045231584","2025-02-07","En curso");
INSERT INTO `inscripcion` VALUES("265","15","1022149324","2025-02-07","En curso");
INSERT INTO `inscripcion` VALUES("266","16","1022149324","2025-02-07","En curso");
INSERT INTO `inscripcion` VALUES("267","15","1026565029","2025-02-07","En curso");
INSERT INTO `inscripcion` VALUES("268","16","1026565029","2025-02-07","En curso");
INSERT INTO `inscripcion` VALUES("269","15","1036941865","2025-02-07","En curso");
INSERT INTO `inscripcion` VALUES("270","16","1036941865","2025-02-07","En curso");
INSERT INTO `inscripcion` VALUES("271","15","1035770602","2025-02-07","En curso");
INSERT INTO `inscripcion` VALUES("272","16","1035770602","2025-02-07","En curso");



DROP TABLE IF EXISTS `institucion_educativa`;

CREATE TABLE `institucion_educativa` (
  `id_institucion_educativa` int(11) NOT NULL AUTO_INCREMENT,
  `nombre_institucion` varchar(255) NOT NULL,
  `logo_institucion` varchar(255) DEFAULT NULL,
  `direccion_institucion` text DEFAULT NULL,
  `telefono_institucion` int(11) DEFAULT NULL,
  `correo_institucion` varchar(255) DEFAULT NULL,
  `BANNER_INSTITUCION` varchar(255) DEFAULT NULL,
  `formatos_no_permitidos` varchar(255) DEFAULT NULL,
  `tamano_maximo_adjunto` varchar(255) DEFAULT NULL,
  `autoregistrarse` varchar(255) DEFAULT NULL,
  `plantilla` int(11) DEFAULT 1,
  PRIMARY KEY (`id_institucion_educativa`)
) ENGINE=MyISAM AUTO_INCREMENT=8 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

INSERT INTO `institucion_educativa` VALUES("1","Fundación Educativa HST","https://campus.hst.edu.co/pluginfile.php/1/theme_moove/logo/1680146469/hst.png","Calle 19 # 27 - 89 Centro\n\n","7220340","webmaster@hst.edu.co",NULL,"exe","200000","si","1");
INSERT INTO `institucion_educativa` VALUES("2","Consorcio S&P EducatiON LINE","","","310","lorena@educati.online","","","","","1");
INSERT INTO `institucion_educativa` VALUES("3","Cubos de formación","","Casa taminango","0","","","","","","1");
INSERT INTO `institucion_educativa` VALUES("4","INSTITUCION EDUCATIVA MUNICIPAL LICEO CENTRAL DE NARIÑO","licenar.png","Calle 12 #22b-1 a 22b-147 Barrio Santiago, Pasto, Nariño","7235363","iemlicenar@gmail.com",NULL,NULL,NULL,NULL,"1");
INSERT INTO `institucion_educativa` VALUES("5","Instituto José Mutis","https://www.josemutis.edu.co/wp-content/uploads/2021/03/logo-mutis-may-2020-80x80.png","Calle 16 No 27-73","7233974","info@josemutis.edu.co","","","","","1");
INSERT INTO `institucion_educativa` VALUES("6","Cinar Sistemas","https://cinarsistemas.edu.co/wp-content/uploads/2019/09/cinar-x1-sistemas-logo-programas-tecnicos-carreras-pasto-16.png","Calle 13 No 24 - 83, Barrio Santiago Pasto, Nariño, Colombia","2147483647","mercadeo@cinar.edu.co",NULL,NULL,NULL,NULL,"1");
INSERT INTO `institucion_educativa` VALUES("7","Institución Educativa Rural La Josefina","https://blogger.googleusercontent.com/img/b/R29vZ2xl/AVvXsEh9e2HiIb5oAuHY79X78q-F1ed_h3wF9Hmd6m_r6dre6PAZnH8kg6GRGDZhJyI24K0Xn5KnyEsuoSfYHbXOM5ibrMFh1VeSTE6MGtgiNDNFEprcPa-dzQxa_cX-AMsNQ-sWcm444aU5aw/s320/escudo+la+josefina.jpg","Autopista Medellín Bogotá, Km 134",NULL,"ierlajosefina@hotmail.com",NULL,NULL,NULL,NULL,"1");



DROP TABLE IF EXISTS `kanban`;

CREATE TABLE `kanban` (
  `id_tarea` int(11) NOT NULL AUTO_INCREMENT,
  `tarea` text DEFAULT NULL,
  `fecha` datetime DEFAULT current_timestamp(),
  `estado` varchar(255) DEFAULT 'tarea',
  `fecha_actualizacion` datetime NOT NULL DEFAULT current_timestamp(),
  `categoria` varchar(255) NOT NULL DEFAULT 'laboral',
  PRIMARY KEY (`id_tarea`)
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `kanban` VALUES("1","Diploma","2023-03-23 10:58:37","archivado","2023-04-03 14:20:31","laboral");
INSERT INTO `kanban` VALUES("2","chatbot","2023-03-24 13:22:49","proceso","2023-04-03 14:20:43","laboral");
INSERT INTO `kanban` VALUES("5","Promociones","2023-03-24 16:18:52","tarea","2023-03-24 16:18:52","laboral");
INSERT INTO `kanban` VALUES("6","gmys","2023-03-24 16:20:29","proceso","2023-04-03 09:37:13","laboral");
INSERT INTO `kanban` VALUES("8","diploma","2023-03-24 16:31:39","tarea","2023-03-24 16:31:39","");
INSERT INTO `kanban` VALUES("9","klÔö£ÔûÆÔö£ÔûÆlk","2023-03-24 16:32:10","tarea","2023-03-24 16:32:10","");
INSERT INTO `kanban` VALUES("10","diploma","2023-03-24 16:34:08","tarea","2023-03-24 16:34:08","");
INSERT INTO `kanban` VALUES("11","diploma","2023-03-24 16:39:03","tarea","2023-03-24 16:39:03","");
INSERT INTO `kanban` VALUES("12","diploma","2023-03-24 16:39:20","tarea","2023-03-24 16:39:20","");
INSERT INTO `kanban` VALUES("13","diploma","2023-03-24 16:41:14","tarea","2023-03-24 16:41:14","");
INSERT INTO `kanban` VALUES("14","diploma","2023-03-24 16:43:05","tarea","2023-03-24 16:43:05","");
INSERT INTO `kanban` VALUES("15","diploma","2023-03-24 16:43:28","proceso","2023-03-24 15:50:48","personal");
INSERT INTO `kanban` VALUES("16","Universidad online o en antioquia plan B","2023-03-24 16:45:52","proceso","2023-03-27 15:18:15","personal");
INSERT INTO `kanban` VALUES("17","Buscar para el concurso de la Dian los dos ","2023-03-24 16:46:05","tarea","2023-03-24 16:46:05","personal");
INSERT INTO `kanban` VALUES("18","Buscar vacantes concurso de la fiscalia (No Simo) Los dos","2023-03-24 16:46:20","proceso","2023-04-03 09:33:08","personal");
INSERT INTO `kanban` VALUES("19","Claro (los dos )","2023-03-24 16:46:32","tarea","2023-03-24 16:46:32","personal");
INSERT INTO `kanban` VALUES("20","Cedula digital","2023-03-24 16:46:41","tarea","2023-03-24 16:46:41","personal");
INSERT INTO `kanban` VALUES("21","Revisar vÔö£┬ídeo https://www.youtube.com/watch?v=1E55bM-jb6Y","2023-03-27 16:18:38","tarea","2023-03-27 16:18:38","personal");
INSERT INTO `kanban` VALUES("22","Ir por relojera del carro","2023-04-17 09:05:43","tarea","2023-04-17 09:05:43","personal");



DROP TABLE IF EXISTS `log`;

CREATE TABLE `log` (
  `id_log` int(11) NOT NULL AUTO_INCREMENT,
  `fechayhoralog` datetime NOT NULL,
  `ipclientelog` varchar(255) NOT NULL,
  `valor` decimal(12,4) NOT NULL,
  `usuario` int(11) NOT NULL,
  `motivo` text NOT NULL,
  `categoria` varchar(255) NOT NULL DEFAULT 'sistema',
  PRIMARY KEY (`id_log`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;




DROP TABLE IF EXISTS `materia`;

CREATE TABLE `materia` (
  `id_materia` int(11) NOT NULL AUTO_INCREMENT,
  `nombre_materia` varchar(255) NOT NULL,
  `descripcion_materia` text NOT NULL,
  `obligatoria` varchar(2) NOT NULL DEFAULT 'no',
  `area` int(11) NOT NULL DEFAULT 9,
  `icono_materia` varchar(255) NOT NULL DEFAULT 'agenda.png',
  `institucion` int(11) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id_materia`),
  UNIQUE KEY `nombre_materia` (`nombre_materia`),
  KEY `nombre_materia_2` (`nombre_materia`)
) ENGINE=MyISAM AUTO_INCREMENT=77 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

INSERT INTO `materia` VALUES("1","Informática básica","..","no","9","324","1");
INSERT INTO `materia` VALUES("2","Ofimatica 1","","no","9","104","1");
INSERT INTO `materia` VALUES("3","Lógica de programación","Asignatura que tiene como objetivo desarrollar la lógica matematica necesaria para programar","no","9","72","1");
INSERT INTO `materia` VALUES("4","Internet y redes sociales","","no","9","194","1");
INSERT INTO `materia` VALUES("5","Ofimatica 2","","no","9","agenda.png","1");
INSERT INTO `materia` VALUES("6","Base de datos","","no","9","13","1");
INSERT INTO `materia` VALUES("7","Producción Documental","referentes :http://www.alcaldiabogota.gov.co/sisjur/normas/Norma1.jsp?i=10551","no","9","agenda.png","1");
INSERT INTO `materia` VALUES("8","CMS (gestores de contenido)","","no","9","464","1");
INSERT INTO `materia` VALUES("9","Técnicas de digitación","Asignatura que busca mejorar la calidad y agilidad del proceso de transcripción y mecanografía en el uso del computador.","no","9","196","1");
INSERT INTO `materia` VALUES("10","Excel Avanzado","","no","9","45","1");
INSERT INTO `materia` VALUES("11","Html","","no","9","agenda.png","1");
INSERT INTO `materia` VALUES("12","Desarrollo web-1","","no","9","464","1");
INSERT INTO `materia` VALUES("13","Diseño Grafico","","no","9","507","1");
INSERT INTO `materia` VALUES("14","Aplicativos Moviles","","no","9","364","1");
INSERT INTO `materia` VALUES("15","Bases de datos Access","","no","9","agenda.png","1");
INSERT INTO `materia` VALUES("16","Seguridad Informática","","no","9","agenda.png","1");
INSERT INTO `materia` VALUES("29","informática empresarial","","no","9","176","1");
INSERT INTO `materia` VALUES("30","EVA","Entorno virtual de Aprendizaje","no","9","11","1");
INSERT INTO `materia` VALUES("32","Mantenimiento de computadores","","no","9","487","1");
INSERT INTO `materia` VALUES("33","Copia de Ofimatica 1","","NO","9","104","1");
INSERT INTO `materia` VALUES("34","software de animación","","no","9","agenda.png","1");
INSERT INTO `materia` VALUES("35","Computación en la nube","","no","9","71","1");
INSERT INTO `materia` VALUES("36","Análisis de datos","","no","9","382","1");
INSERT INTO `materia` VALUES("37","Php","","no","9","agenda.png","1");
INSERT INTO `materia` VALUES("38","Proyectos informáticos","","no","9","agenda.png","1");
INSERT INTO `materia` VALUES("39","Asesorias de talento digital","","no","9","11","1");
INSERT INTO `materia` VALUES("40","Curso de moodle","","no","9","agenda.png","1");
INSERT INTO `materia` VALUES("41","gestión de la información","","no","9","agenda.png","1");
INSERT INTO `materia` VALUES("42","Redes","","no","9","462","1");
INSERT INTO `materia` VALUES("43","Mujer en TIC","","no","9","437","1");
INSERT INTO `materia` VALUES("44","Desarrollo web 3","","no","9","agenda.png","1");
INSERT INTO `materia` VALUES("45","Producción multimedia","","no","9","agenda.png","1");
INSERT INTO `materia` VALUES("46","Transición","","no","9","agenda.png","1");
INSERT INTO `materia` VALUES("47","Análisis de datos e inteligencia de negocios","","no","9","agenda.png","1");
INSERT INTO `materia` VALUES("48","Análisis de datos 2","Curso de transformación digital para empresas","no","9","agenda.png","1");
INSERT INTO `materia` VALUES("49","práctica","","no","9","agenda.png","1");
INSERT INTO `materia` VALUES("50","Diseño Web","Diseño web es una materia orientada a técnicos en sistemas","no","9","agenda.png","1");
INSERT INTO `materia` VALUES("51","Informática","Informática bachillerato","no","9","agenda.png","1");
INSERT INTO `materia` VALUES("52","Marketing digital","Marketing digital","no","9","agenda.png","1");
INSERT INTO `materia` VALUES("53","Javascript2","","no","9","agenda.png","1");
INSERT INTO `materia` VALUES("54","Algoritmos","","no","9","agenda.png","1");
INSERT INTO `materia` VALUES("55","Diseño de paginas web-II","","no","9","agenda.png","1");
INSERT INTO `materia` VALUES("56","Base de datos-II(NoSql)","","no","9","agenda.png","1");
INSERT INTO `materia` VALUES("57","Gestor de contenidos-II","","no","9","agenda.png","1");
INSERT INTO `materia` VALUES("58","Herramientas graficas","","no","9","agenda.png","1");
INSERT INTO `materia` VALUES("59","Edición de sonido y vídeo","","no","9","agenda.png","1");
INSERT INTO `materia` VALUES("60","Animación 2D","","no","9","agenda.png","1");
INSERT INTO `materia` VALUES("61","ofimática-II","","no","9","agenda.png","1");
INSERT INTO `materia` VALUES("62","Ciencias Naturales (6-7)","Ciencias Naturales (6-7)","no","9","agenda.png","1");
INSERT INTO `materia` VALUES("63","Artística (6-7)","Artística (6-7)","no","9","agenda.png","1");
INSERT INTO `materia` VALUES("64","Artística (8-11)","Artística (8-11)","no","9","agenda.png","1");
INSERT INTO `materia` VALUES("65","Matematicas(6-7)","Matemáticas(6-7)","no","9","agenda.png","1");
INSERT INTO `materia` VALUES("66","Matematicas(8-11)","Matematicas(8-11)","no","9","agenda.png","1");
INSERT INTO `materia` VALUES("67","ED. FISICA(6-7)","ED. FISICA(6-7)","no","9","agenda.png","1");
INSERT INTO `materia` VALUES("68","ED. FISICA(8-11)","ED. FISICA(8-11)","no","9","agenda.png","1");
INSERT INTO `materia` VALUES("69","TECNOLOGIA(6-7)","TECNOLOGIA(6-7)","no","9","agenda.png","1");
INSERT INTO `materia` VALUES("70","TECNOLOGIA(8-11)","TECNOLOGIA(8-11)","no","9","agenda.png","1");
INSERT INTO `materia` VALUES("71","URBANIDAD(6-7)","URBANIDAD(6-7)","no","9","agenda.png","1");
INSERT INTO `materia` VALUES("72","URBANIDAD(8-11)","URBANIDAD(8-11)","no","9","agenda.png","1");
INSERT INTO `materia` VALUES("73","C. SOCIALES(6-7)","C. SOCIALES(6-7)","no","9","agenda.png","1");
INSERT INTO `materia` VALUES("74","EMPRENDIMIENTO (6-7)","EMPRENDIMIENTO (6-7)","no","9","agenda.png","1");
INSERT INTO `materia` VALUES("75","EMPRENDIMIENTO (8-11)","EMPRENDIMIENTO (8-11)","no","9","agenda.png","1");
INSERT INTO `materia` VALUES("76","FISICA(10-11)","FISICA(10-11)","no","9","agenda.png","1");



DROP TABLE IF EXISTS `materia_oficial`;

CREATE TABLE `materia_oficial` (
  `id_materia` int(11) NOT NULL AUTO_INCREMENT,
  `nombre_materia` varchar(255) NOT NULL,
  `descripcion` text DEFAULT NULL,
  PRIMARY KEY (`id_materia`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `materia_oficial` VALUES("1","Matemáticas",NULL);
INSERT INTO `materia_oficial` VALUES("2","Economia/politica",NULL);
INSERT INTO `materia_oficial` VALUES("3","Ciencias Sociales",NULL);
INSERT INTO `materia_oficial` VALUES("4","Educación Física",NULL);
INSERT INTO `materia_oficial` VALUES("5","Geometria",NULL);
INSERT INTO `materia_oficial` VALUES("6","Fisica",NULL);
INSERT INTO `materia_oficial` VALUES("7","Tecnología e informática",NULL);
INSERT INTO `materia_oficial` VALUES("8","Urbanidad",NULL);
INSERT INTO `materia_oficial` VALUES("9","Emprendimiento",NULL);



DROP TABLE IF EXISTS `matricula`;

CREATE TABLE `matricula` (
  `id_matricula` int(11) NOT NULL AUTO_INCREMENT,
  `id_estudiante` varchar(12) NOT NULL,
  `id_asignacion` int(11) NOT NULL,
  `fecha_matricula` date NOT NULL,
  `obserbaciones_matricula` text NOT NULL,
  PRIMARY KEY (`id_matricula`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;




DROP TABLE IF EXISTS `mensaje`;

CREATE TABLE `mensaje` (
  `id_mensaje` int(11) NOT NULL AUTO_INCREMENT,
  `usuario` varchar(12) NOT NULL DEFAULT '1',
  `mensaje` text NOT NULL,
  `fecha` timestamp NOT NULL DEFAULT current_timestamp(),
  `leido` varchar(2) NOT NULL DEFAULT 'NO',
  `remite` varchar(12) NOT NULL,
  `estado_usuario` varchar(255) NOT NULL DEFAULT 'entrada',
  `estado_remite` varchar(255) NOT NULL,
  `favorito` varchar(2) NOT NULL,
  PRIMARY KEY (`id_mensaje`)
) ENGINE=InnoDB AUTO_INCREMENT=43 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

INSERT INTO `mensaje` VALUES("1","1","Entregar memoria a Alejandra","2018-10-29 02:35:24","SI","1","entrada","","");
INSERT INTO `mensaje` VALUES("2","1085290375","terminar roles","2018-11-01 21:39:55","SI","1085290375","papelera","","");
INSERT INTO `mensaje` VALUES("3","1085290375","revisar el usuario que envia los mensajes en guagua","2018-11-01 21:40:54","SI","1085290375","entrada","","");
INSERT INTO `mensaje` VALUES("4","1085290375","Solciitar al profe William apertura del curso de ofimatica 1 en la noche","2018-11-01 21:54:28","SI","1085290375","papelera","","");
INSERT INTO `mensaje` VALUES("5","1085290375","Hablar con Ledidy y Santiago de los examenes de producciÔö£Ôöén documental","2018-11-01 22:37:03","SI","1085290375","papelera","","");
INSERT INTO `mensaje` VALUES("6","1","Escribirle a Dario por el problema de los cursos en la paltaforma hst","2018-11-02 04:05:14","SI","1","entrada","","");
INSERT INTO `mensaje` VALUES("7","1085290375","Solicitar informÔö£├¡ciÔö£Ôöén de nuevos estudiantes a Lorena de informÔö£├¡tica bÔö£├¡sica de la jornada de la noche","2018-11-02 04:08:25","SI","1085290375","papelera","","");
INSERT INTO `mensaje` VALUES("8","1085290375","pendiente plan de app inventor Ôö£Ôòæltima clase 2/11/2018","2018-11-03 00:19:56","SI","1085290375","entrada","","");
INSERT INTO `mensaje` VALUES("9","1085290375","Revisar seguimiento del dÔö£┬ía 2/11/2018 de Yuri y Yolanda","2018-11-03 02:27:48","SI","1085290375","entrada","","");
INSERT INTO `mensaje` VALUES("10","1085290375","Revisar proyctos de chicos de los sÔö£├¡bados ","2018-11-03 16:06:04","SI","1085290375","entrada","","");
INSERT INTO `mensaje` VALUES("11","1085290375","Pendiente calificar cuadernillo de ejercicios de informÔö£├¡tica empresarial y subir notas a la paltaforma, recordando que se debe subir copia en la plataforma.","2018-11-06 21:11:22","SI","1085290375","papelera","","");
INSERT INTO `mensaje` VALUES("12","1085290375","Prueba","2019-01-18 20:44:52","SI","1085290375","papelera","","");
INSERT INTO `mensaje` VALUES("13","1085290375","Saludo","2019-03-06 04:27:52","SI","1085290375","papelera","","");
INSERT INTO `mensaje` VALUES("14","1085290375","Saludo","2019-03-06 04:31:27","SI","1085290375","papelera","","NO");
INSERT INTO `mensaje` VALUES("15","1085290375","hola mundo","2019-03-10 01:14:01","SI","1085290375","papelera","","");
INSERT INTO `mensaje` VALUES("16","1085290375","Mensaje","2019-03-10 15:53:43","SI","1085290375","papelera","","");
INSERT INTO `mensaje` VALUES("17","1085290375","mensaje2","2019-03-10 16:10:44","SI","1085290375","papelera","","");
INSERT INTO `mensaje` VALUES("18","1085290375","Mensaje 3","2019-03-10 16:17:18","SI","1085290375","papelera","","");
INSERT INTO `mensaje` VALUES("19","1085290375","mensaje x","2019-03-10 16:22:35","SI","1085290375","papelera","","");
INSERT INTO `mensaje` VALUES("20","1085290375","Cordial saludo","2019-03-10 16:24:59","SI","1085290375","papelera","","");
INSERT INTO `mensaje` VALUES("21","1085290375","Mensaje para persona","2019-03-10 16:27:08","SI","1085290375","papelera","","");
INSERT INTO `mensaje` VALUES("22","1085290375","mensaje","2019-03-10 16:28:41","SI","1085290375","papelera","","");
INSERT INTO `mensaje` VALUES("23","1085290375","escribir a profe Omar por reporte","2019-03-18 23:04:40","SI","1085290375","entrada","","");
INSERT INTO `mensaje` VALUES("24","1085290375","3108608863\n\n3164019794\n\nLlamar a Carlos Salazar\n\n","2019-03-25 00:30:09","SI","1085290375","entrada","","");
INSERT INTO `mensaje` VALUES("25","1085290375","revisar hoja de vida estudiante de ofimatica\n\n","2019-03-25 00:30:24","SI","1085290375","entrada","","");
INSERT INTO `mensaje` VALUES("26","1085290375","Enviar correo de hoja de vida  a info@kommit.co","2019-03-25 01:35:14","SI","1085290375","entrada","","");
INSERT INTO `mensaje` VALUES("27","1085290375","hacer correciones de digitaciÔö£Ôöén ","2019-03-30 21:36:55","SI","1085290375","entrada","","");
INSERT INTO `mensaje` VALUES("28","1085290375","VÔö£┬ídeo de la instituciÔö£Ôöén para el profe de EducaciÔö£Ôöén fÔö£┬ísica.","2019-04-03 17:44:54","SI","1085290375","entrada","","");
INSERT INTO `mensaje` VALUES("29","1085290375","Tareas para el sistema de digitaciÔö£Ôöén\n\nValidar al finalizar el tiempo\n\nValidar el tiempo asignado para el plan \n\nCorregir a los estudiantes de tÔö£┬«cnicas de digitaciÔö£Ôöén\n\n","2019-04-10 15:23:53","SI","1085290375","entrada","","");
INSERT INTO `mensaje` VALUES("30","1085290375","ir a dejar hoja de vida LA CRA 20 A NÔö¼Ôûæ17-59 ANTIGUO TEATRO ALCÔö£├╝ZAR INF: 3166706013","2019-04-15 15:34:50","SI","1085290375","entrada","","");
INSERT INTO `mensaje` VALUES("31","1085290375","reparar al insertar un nuevo plan el orden de la planeaciÔö£Ôöén ","2019-07-24 20:34:43","SI","1085290375","entrada","","");
INSERT INTO `mensaje` VALUES("32","1085290375","Aprender backtracking\n\n","2019-08-06 14:30:10","SI","1085290375","entrada","","");
INSERT INTO `mensaje` VALUES("33","1085290375","Colegios \n\nPre inscripciÔö£Ôöén\n\n\n\n","2019-08-06 14:34:00","SI","1085290375","entrada","","");
INSERT INTO `mensaje` VALUES("34","1085290375","proyecto de hotel en analitica de datos","2019-08-09 16:31:08","SI","1085290375","entrada","","");
INSERT INTO `mensaje` VALUES("35","1085290375","Calificar tÔö£┬«cnicas de digitaciÔö£Ôöén","2019-09-03 15:29:09","SI","1085290375","entrada","","");
INSERT INTO `mensaje` VALUES("36","1085290375","Formularios Pasto participa  para jueces","2019-09-13 00:44:35","SI","1085290375","entrada","","");
INSERT INTO `mensaje` VALUES("37","1085290375","Calificar taller join estudiantes de base de datos ","2019-10-08 15:37:41","SI","1085290375","entrada","","");
INSERT INTO `mensaje` VALUES("38","1085290375","Calificar examen 1 de base de datos ","2019-10-08 15:40:10","SI","1085290375","entrada","","");
INSERT INTO `mensaje` VALUES("39","1085290375","Calificar aplicativos mÔö£Ôöéviles, ","2019-11-12 23:36:09","SI","1085290375","entrada","","");
INSERT INTO `mensaje` VALUES("40","1085290375","Temas de examen de internet y redes sociales:\n\n*Formularios de google form\n\n*EnvÔö£┬ío de correo elÔö£┬«ctronico\n\n*Mail Merge con google documentos\n\n*Busquedas y caracterizaciÔö£Ôöén personal con google\n\n","2019-11-13 15:14:35","SI","1085290375","entrada","","");
INSERT INTO `mensaje` VALUES("41","1085290375","Revisar examen 1 de internet y redes sociales","2019-11-20 16:27:38","SI","1085290375","entrada","","");
INSERT INTO `mensaje` VALUES("42","1085290375","Revisar calificaciones de redes 25/11/2019","2019-11-25 14:22:43","SI","1085290375","entrada","","");



DROP TABLE IF EXISTS `menu_items`;

CREATE TABLE `menu_items` (
  `menu_item_id` int(11) NOT NULL AUTO_INCREMENT,
  `menu_item_name` varchar(255) NOT NULL,
  `menu_description` text NOT NULL,
  `menu_url` varchar(255) NOT NULL,
  `menu_parent_id` int(11) NOT NULL,
  `url_target` varchar(255) NOT NULL,
  `class` varchar(255) NOT NULL,
  `icono` varchar(255) NOT NULL,
  `ubicacion` varchar(255) NOT NULL,
  `tipo` varchar(255) NOT NULL,
  `color` varchar(255) NOT NULL,
  `permisos_menu` text NOT NULL,
  PRIMARY KEY (`menu_item_id`),
  UNIQUE KEY `menu_item_name` (`menu_item_name`),
  KEY `ubicacion` (`ubicacion`),
  KEY `tipo` (`tipo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;




DROP TABLE IF EXISTS `menu_items2`;

CREATE TABLE `menu_items2` (
  `menu_item_id` int(11) NOT NULL AUTO_INCREMENT,
  `menu_item_name` varchar(255) NOT NULL,
  `menu_description` text NOT NULL,
  `menu_url` varchar(255) NOT NULL,
  `menu_parent_id` int(11) NOT NULL,
  `url_target` varchar(255) NOT NULL,
  `categoria` varchar(255) NOT NULL,
  PRIMARY KEY (`menu_item_id`),
  UNIQUE KEY `menu_item_name` (`menu_item_name`)
) ENGINE=InnoDB AUTO_INCREMENT=37 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

INSERT INTO `menu_items2` VALUES("1","Estudiante","","../seguimiento_andres/estudiante.php","0","_self","general");
INSERT INTO `menu_items2` VALUES("3","Citas","","citas.php","0","_self","x");
INSERT INTO `menu_items2` VALUES("4","Cita","","cita.php","0","_self","x");
INSERT INTO `menu_items2` VALUES("6","Materia","","Planeador/materia.php","0","_self","");
INSERT INTO `menu_items2` VALUES("7","Institucion_educativa","","institucion_educativa.php","0","_self","");
INSERT INTO `menu_items2` VALUES("11","Recursos","","recursos.php","0","_self","Planeacion");
INSERT INTO `menu_items2` VALUES("13","Actividad","","actividad.php","0","_self","Planeacion");
INSERT INTO `menu_items2` VALUES("16","Estrategias","","estrategias.php","0","_self","Planeacion");
INSERT INTO `menu_items2` VALUES("20","Contenido","","contenido.php","0","_self","Planeacion");
INSERT INTO `menu_items2` VALUES("21","Objetivos","","objetivos.php","0","_self","Planeacion");
INSERT INTO `menu_items2` VALUES("22","Consultas","","consultas.php","0","_self","");
INSERT INTO `menu_items2` VALUES("23","nuevo plan","","index.php","9","","");
INSERT INTO `menu_items2` VALUES("24","Asignacion","","../evaldocente/php/asignacion.php","0","_self","");
INSERT INTO `menu_items2` VALUES("25","Docente","","../evaldocente/php/docente.php","0","_self","");
INSERT INTO `menu_items2` VALUES("30","Ano_lectivo","","../evaldocente/php/ano_lectivo.php","0","_self","");
INSERT INTO `menu_items2` VALUES("32","Matricula","","../evaldocente/php/matricula.php","0","_self","");
INSERT INTO `menu_items2` VALUES("33","Planes","","../miplaneador/reporte_planeador.php","0","_blank","Planeacion");
INSERT INTO `menu_items2` VALUES("34","Todos","Todos los planes","planeador.php","0","_blank","Planeacion");
INSERT INTO `menu_items2` VALUES("35","CRUD","CRUD DE PLANES","planeador_vallesol.php","0","_blank","Planeacion");
INSERT INTO `menu_items2` VALUES("36","Importar","Importador de malla","importador_malla.php","0","_blank","Planeacion");
