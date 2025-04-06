DROP TABLE IF EXISTS `materia_oficial`;

CREATE TABLE `materia_oficial` (
  `id_materia` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(255) NOT NULL,
  `descripcion` text DEFAULT NULL,
  PRIMARY KEY (`id_materia`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `materia_oficial` VALUES("1","Matemáticas",NULL);
INSERT INTO `materia_oficial` VALUES("2","Ciencias Naturales",NULL);
INSERT INTO `materia_oficial` VALUES("3","Ciencias Sociales",NULL);
INSERT INTO `materia_oficial` VALUES("4","Educación Física",NULL);
INSERT INTO `materia_oficial` VALUES("5","Educación Artística",NULL);
INSERT INTO `materia_oficial` VALUES("6","Fisica",NULL);
INSERT INTO `materia_oficial` VALUES("7","Tecnología e informática",NULL);
INSERT INTO `materia_oficial` VALUES("8","Urbanidad",NULL);
INSERT INTO `materia_oficial` VALUES("9","Emprendimiento",NULL);


ROP TABLE IF EXISTS `estándar`;

CREATE TABLE `estándar` (
  `id_estandar` int(11) NOT NULL AUTO_INCREMENT,
  `nombre_estandar` varchar(255) NOT NULL,
  `descripcion_estandar` text DEFAULT NULL,
  `grado` int(11) DEFAULT NULL,
  `id_periodo` int(11) DEFAULT NULL,
  `id_materia_oficial` int(11) DEFAULT NULL,
  PRIMARY KEY (`id_estandar`)
) ENGINE=InnoDB AUTO_INCREMENT=54 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


DROP TABLE IF EXISTS `dba`;

CREATE TABLE `dba` (
  `id_dba` int(11) NOT NULL AUTO_INCREMENT,
  `nombre_dba` text NOT NULL,
  `descripcion_dba` text DEFAULT NULL,
  `id_estandar` int(11) NOT NULL,
  PRIMARY KEY (`id_dba`)
) ENGINE=InnoDB AUTO_INCREMENT=54 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `evidencia_de_aprendizaje`;

CREATE TABLE `evidencia_de_aprendizaje` (
  `id_evidencia_aprendizaje` int(11) NOT NULL AUTO_INCREMENT,
  `descripcion_evidencia` text DEFAULT NULL,
  `id_dba` int(11) NOT NULL,
  PRIMARY KEY (`id_evidencia_aprendizaje`),
  KEY `id_dba` (`id_dba`),
  CONSTRAINT `evidencia_de_aprendizaje_ibfk_1` FOREIGN KEY (`id_dba`) REFERENCES `dba` (`id_dba`)
) ENGINE=InnoDB AUTO_INCREMENT=59 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `eje_tematico`;

CREATE TABLE `eje_tematico` (
  `id_eje_tematico` int(11) NOT NULL AUTO_INCREMENT,
  `nombre_eje_tematico` varchar(255) NOT NULL,
  `descripcion_eje_tematico` text DEFAULT NULL,
  `id_dba` int(11) DEFAULT NULL,
  PRIMARY KEY (`id_eje_tematico`)
) ENGINE=InnoDB AUTO_INCREMENT=58 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;