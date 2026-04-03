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

-- Volcando estructura para procedimiento pantalla.datadashboard
DELIMITER //
CREATE PROCEDURE `datadashboard`()
BEGIN
    
    DECLARE usuarios int;
    DECLARE medicos int;
    
    SELECT COUNT(*) INTO usuarios FROM usuario WHERE estatus =1;
    SELECT COUNT(*) INTO medicos FROM medico WHERE estado =1;
    
    SELECT usuarios,medicos;
    
    END//
DELIMITER ;

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
  `pacientes` varchar(50) DEFAULT NULL,
  `paciente_actual` varchar(50) DEFAULT NULL,
  `paciente_siguiente` varchar(50) DEFAULT NULL,
  `usuario_id` int(11) NOT NULL,
  `estado` int(11) NOT NULL DEFAULT 1,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`idmed`),
  KEY `usuario_id` (`usuario_id`),
  CONSTRAINT `medico_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuario` (`idusuario`)
) ENGINE=InnoDB AUTO_INCREMENT=78 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- Volcando datos para la tabla pantalla.medico: ~1 rows (aproximadamente)
DELETE FROM `medico`;
INSERT INTO `medico` (`idmed`, `nombrem`, `especialidad`, `foto`, `fechas_consul`, `horarios`, `consultorio`, `mpps`, `comezu`, `pacientes`, `paciente_actual`, `paciente_siguiente`, `usuario_id`, `estado`, `timestamp`) VALUES
	(1, 'Dra. Alejandra Araujo', 'Internista', 'img_ac45cb190df09f6c3646634a80815478.jpg', 'Sabado y Domingo', '5:00 AM 4:00 PM', '1', '100.904', '15.047', 'VIENDO PACIENTES', 'ALEJANDRA RODRIGUEZ', 'MARIA GUZMAN', 1, 1, '2025-01-09 04:21:14');

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

-- Volcando datos para la tabla pantalla.usuario: ~2 rows (aproximadamente)
DELETE FROM `usuario`;
INSERT INTO `usuario` (`idusuario`, `nombre`, `usuario`, `clave`, `rol`, `estatus`, `timestamp`) VALUES
	(1, 'samuel', 'samuel', '81dc9bdb52d04dc20036dbd8313ed055', 1, 1, '2025-01-09 02:04:47'),
	(2, 'test', 'test1', '81dc9bdb52d04dc20036dbd8313ed055', 1, 1, '2025-02-03 08:54:09');

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
