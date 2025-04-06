
CREATE TABLE `estandar` (
  `id_estandar` int(11) NOT NULL,
  `nombre_estandar` varchar(255) NOT NULL,
  `descripcion_estandar` text DEFAULT NULL,
  `grado` int(11) DEFAULT NULL,
  `id_periodo` int(11) DEFAULT NULL,
  `id_materia_oficial` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


CREATE TABLE `dba` (
  `id_dba` int(11) NOT NULL,
  `nombre_dba` text NOT NULL,
  `descripcion_dba` text DEFAULT NULL,
  `id_estandar` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


CREATE TABLE `evidencia_de_aprendizaje` (
  `id_evidencia_aprendizaje` int(11) NOT NULL,
  `descripcion_evidencia` text DEFAULT NULL,
  `id_dba` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


CREATE TABLE `eje_tematico` (
  `id_eje_tematico` int(11) NOT NULL,
  `nombre_eje_tematico` varchar(255) NOT NULL,
  `descripcion_eje_tematico` text DEFAULT NULL,
  `id_dba` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

Tengo un sistema de gestión de aprendizaje propio y la estructura de mi colegio tiene primero estandar, el estandar se relaciona con el dba, el dba con la evidencia de aprendizaje y este a su vez con el eje tematico 