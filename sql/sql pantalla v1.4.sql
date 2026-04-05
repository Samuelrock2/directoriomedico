-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Versión del servidor:         10.4.32-MariaDB - mariadb.org binary distribution
-- SO del servidor:              Win64
-- HeidiSQL Versión:             12.5.0.6739
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


-- Volcando estructura de base de datos para pantalla
CREATE DATABASE IF NOT EXISTS `pantalla` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci */;
USE `pantalla`;

-- Volcando estructura para tabla pantalla.consultory
CREATE TABLE IF NOT EXISTS `consultory` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `consultory_number` varchar(10) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `status` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `consultory_number` (`consultory_number`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla pantalla.consultory: ~5 rows (aproximadamente)
DELETE FROM `consultory`;
INSERT INTO `consultory` (`id`, `consultory_number`, `name`, `location`, `status`) VALUES
	(1, 'P3-01', 'Consultorio Doble P3-01', 'P3-01 entrada a la derecha', 1),
	(2, '2', 'Consultorio Especialidades', NULL, 1),
	(3, '3', 'Consultorio Pediátrico', NULL, 1),
	(4, '4', 'Consultorio Ginecológico', NULL, 1),
	(5, 'P3-02', 'Consultorio Doble P3-02', 'P3-02 entrada a la izquierda', 1);

-- Volcando estructura para tabla pantalla.medico
CREATE TABLE IF NOT EXISTS `medico` (
  `idmed` int(11) NOT NULL AUTO_INCREMENT,
  `nombrem` varchar(100) NOT NULL,
  `especialidad` varchar(100) NOT NULL,
  `foto` varchar(50) NOT NULL DEFAULT '',
  `fechas_consul` varchar(50) DEFAULT NULL,
  `horarios` varchar(50) DEFAULT NULL,
  `consultorio` varchar(50) DEFAULT NULL,
  `mpps` varchar(50) DEFAULT 'SIN REGISTRAR',
  `comezu` varchar(50) DEFAULT 'SIN REGISTRAR',
  `pacientes` varchar(300) DEFAULT NULL,
  `paciente_actual` varchar(50) DEFAULT NULL,
  `paciente_siguiente` varchar(50) DEFAULT NULL,
  `usuario_id` int(11) NOT NULL,
  `estado` int(11) NOT NULL DEFAULT 1,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp(),
  `consultory_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`idmed`),
  KEY `usuario_id` (`usuario_id`),
  KEY `consultory_id` (`consultory_id`),
  CONSTRAINT `medico_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuario` (`idusuario`),
  CONSTRAINT `medico_ibfk_2` FOREIGN KEY (`consultory_id`) REFERENCES `consultory` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=81 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- Volcando datos para la tabla pantalla.medico: ~4 rows (aproximadamente)
DELETE FROM `medico`;
INSERT INTO `medico` (`idmed`, `nombrem`, `especialidad`, `foto`, `fechas_consul`, `horarios`, `consultorio`, `mpps`, `comezu`, `pacientes`, `paciente_actual`, `paciente_siguiente`, `usuario_id`, `estado`, `timestamp`, `consultory_id`) VALUES
	(1, 'Dra. Alejandra Araujo', 'Internista', 'doctor_1_1775248949.jpeg', 'Sabado y Domingo', '5:00 AM 4:00 PM', '1', '100.904', '15.047', 'VIENDO PACIENTES', NULL, NULL, 1, 1, '2025-01-09 04:21:14', NULL),
	(78, 'Dra. Maribet Cano', 'Nefrologia', 'doctor_78_1775248356.jpeg', 'Lunes', '8:00 AM', '2', '1234', '5678', NULL, NULL, NULL, 1, 1, '2026-04-03 19:33:22', NULL),
	(79, 'Dr. Miguel Guevara', 'Traumatologia', 'doctor_79_1775249162.jpeg', 'Lunes', '8:00 AM', '3', '7778', '888', 'huesos rotos', NULL, NULL, 2, 1, '2026-04-03 20:46:02', 1),
	(80, 'Dr. Atilio Rodriguez', 'Neurologia   Cirujano', 'doctor_80_1775295724.png', 'Lunes a Viernes', '7 AM - 12 PM', 'P1-03', '777', '888', 'cirugia de columna vertebral. hernia discal. escoliosis. microcirugia. tumores de cerebro y medula espinal. estudio y tratamiento de las lesiones y enfermedades del cerebro', NULL, NULL, 3, 1, '2026-04-04 09:42:04', 1);

-- Volcando estructura para tabla pantalla.rol
CREATE TABLE IF NOT EXISTS `rol` (
  `idrol` int(11) NOT NULL AUTO_INCREMENT,
  `rol` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`idrol`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla pantalla.rol: ~3 rows (aproximadamente)
DELETE FROM `rol`;
INSERT INTO `rol` (`idrol`, `rol`) VALUES
	(1, 'admin'),
	(2, 'supervisor'),
	(3, 'usuario');

-- Volcando estructura para tabla pantalla.user_sessions
CREATE TABLE IF NOT EXISTS `user_sessions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `session_token` varchar(255) NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `expires_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `user_sessions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `usuario` (`idusuario`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla pantalla.user_sessions: ~0 rows (aproximadamente)
DELETE FROM `user_sessions`;

-- Volcando estructura para tabla pantalla.usuario
CREATE TABLE IF NOT EXISTS `usuario` (
  `idusuario` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(50) DEFAULT NULL,
  `usuario` varchar(15) DEFAULT NULL,
  `clave` varchar(100) DEFAULT NULL,
  `rol` int(11) DEFAULT NULL,
  `estatus` int(11) NOT NULL DEFAULT 1,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`idusuario`),
  KEY `rol` (`rol`),
  CONSTRAINT `usuario_ibfk_1` FOREIGN KEY (`rol`) REFERENCES `rol` (`idrol`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- Volcando datos para la tabla pantalla.usuario: ~4 rows (aproximadamente)
DELETE FROM `usuario`;
INSERT INTO `usuario` (`idusuario`, `nombre`, `usuario`, `clave`, `rol`, `estatus`, `timestamp`) VALUES
	(1, 'samuel', 'samuel', '81dc9bdb52d04dc20036dbd8313ed055', 1, 1, '2025-01-09 02:04:47'),
	(2, 'test', 'test1', '81dc9bdb52d04dc20036dbd8313ed055', 3, 1, '2026-04-03 20:45:17'),
	(3, 'ATILIO', 'atilio', '81dc9bdb52d04dc20036dbd8313ed055', 3, 1, '2026-04-04 09:40:11'),
	(4, 'admin', 'admin', '81dc9bdb52d04dc20036dbd8313ed055', 1, 1, '2026-04-04 10:10:46');

-- Volcando estructura para tabla pantalla.waiting_patients
CREATE TABLE IF NOT EXISTS `waiting_patients` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `medico_id` int(11) NOT NULL,
  `patient_name` varchar(100) NOT NULL,
  `patient_id_number` varchar(50) DEFAULT NULL,
  `position` int(11) NOT NULL,
  `status` enum('waiting','in_consultation','completed','cancelled') DEFAULT 'waiting',
  `estimated_time` varchar(50) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `medico_id` (`medico_id`),
  CONSTRAINT `waiting_patients_ibfk_1` FOREIGN KEY (`medico_id`) REFERENCES `medico` (`idmed`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla pantalla.waiting_patients: ~0 rows (aproximadamente)
DELETE FROM `waiting_patients`;

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
