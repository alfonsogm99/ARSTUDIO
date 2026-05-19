-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: fdb1033.awardspace.net
-- Tiempo de generación: 18-05-2026 a las 15:34:30
-- Versión del servidor: 8.0.32
-- Versión de PHP: 8.1.34

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `4751326_arstudio`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `activacion_usuario`
--

CREATE TABLE `activacion_usuario` (
  `id` int NOT NULL,
  `id_cliente` int NOT NULL,
  `token` varchar(64) NOT NULL,
  `expiracion` datetime NOT NULL,
  `usado` tinyint(1) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Disparadores `activacion_usuario`
--
DELIMITER $$
CREATE TRIGGER `activacion_usuario_after_delete` AFTER DELETE ON `activacion_usuario` FOR EACH ROW BEGIN

    INSERT INTO audit_log (
        tabla,
        accion,
        registro_id,
        usuario,
        datos_antes
    )
    VALUES (
        'activacion_usuario',
        'DELETE',
        OLD.id,
        COALESCE(@usuario_actual, 'system'),
        JSON_OBJECT(
            'id', OLD.id,
            'id_cliente', OLD.id_cliente,
            'token', OLD.token,
            'expiracion', OLD.expiracion,
            'usado', OLD.usado
        )
    );

END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `activacion_usuario_after_insert` AFTER INSERT ON `activacion_usuario` FOR EACH ROW BEGIN

    INSERT INTO audit_log (
        tabla,
        accion,
        registro_id,
        usuario,
        datos_despues
    )
    VALUES (
        'activacion_usuario',
        'INSERT',
        NEW.id,
        COALESCE(@usuario_actual, 'system'),
        JSON_OBJECT(
            'id', NEW.id,
            'id_cliente', NEW.id_cliente,
            'token', NEW.token,
            'expiracion', NEW.expiracion,
            'usado', NEW.usado
        )
    );

END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `activacion_usuario_after_update` AFTER UPDATE ON `activacion_usuario` FOR EACH ROW BEGIN

    INSERT INTO audit_log (
        tabla,
        accion,
        registro_id,
        usuario,
        datos_antes,
        datos_despues
    )
    VALUES (
        'activacion_usuario',
        'UPDATE',
        NEW.id,
        COALESCE(@usuario_actual, 'system'),
        JSON_OBJECT(
            'id_cliente', OLD.id_cliente,
            'token', OLD.token,
            'expiracion', OLD.expiracion,
            'usado', OLD.usado
        ),
        JSON_OBJECT(
            'id_cliente', NEW.id_cliente,
            'token', NEW.token,
            'expiracion', NEW.expiracion,
            'usado', NEW.usado
        )
    );

END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `admin`
--

CREATE TABLE `admin` (
  `id_admin` int NOT NULL,
  `id_usuario` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `admin`
--

INSERT INTO `admin` (`id_admin`, `id_usuario`) VALUES
(1, 2);

--
-- Disparadores `admin`
--
DELIMITER $$
CREATE TRIGGER `admin_after_delete` AFTER DELETE ON `admin` FOR EACH ROW BEGIN

    INSERT INTO audit_log (
        tabla,
        accion,
        registro_id,
        usuario,
        datos_antes
    )
    VALUES (
        'admin',
        'DELETE',
        OLD.id_admin,
        COALESCE(@usuario_actual, 'system'),
        JSON_OBJECT(
            'id_admin', OLD.id_admin,
            'id_usuario', OLD.id_usuario
        )
    );

END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `admin_after_insert` AFTER INSERT ON `admin` FOR EACH ROW BEGIN

    INSERT INTO audit_log (
        tabla,
        accion,
        registro_id,
        usuario,
        datos_despues
    )
    VALUES (
        'admin',
        'INSERT',
        NEW.id_admin,
        COALESCE(@usuario_actual, 'system'),
        JSON_OBJECT(
            'id_admin', NEW.id_admin,
            'id_usuario', NEW.id_usuario
        )
    );

END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `admin_after_update` AFTER UPDATE ON `admin` FOR EACH ROW BEGIN

    INSERT INTO audit_log (
        tabla,
        accion,
        registro_id,
        usuario,
        datos_antes,
        datos_despues
    )
    VALUES (
        'admin',
        'UPDATE',
        NEW.id_admin,
        COALESCE(@usuario_actual, 'system'),
        JSON_OBJECT(
            'id_usuario', OLD.id_usuario
        ),
        JSON_OBJECT(
            'id_usuario', NEW.id_usuario
        )
    );

END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `audit_log`
--

CREATE TABLE `audit_log` (
  `id` int NOT NULL,
  `tabla` varchar(100) NOT NULL,
  `accion` varchar(20) NOT NULL,
  `registro_id` int DEFAULT NULL,
  `usuario` varchar(100) DEFAULT NULL,
  `datos_antes` longtext,
  `datos_despues` longtext,
  `ip` varchar(45) DEFAULT NULL,
  `fecha` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `audit_log`
--

INSERT INTO `audit_log` (`id`, `tabla`, `accion`, `registro_id`, `usuario`, `datos_antes`, `datos_despues`, `ip`, `fecha`) VALUES
(1, 'cita', 'INSERT', 22, 'alfonsogm', NULL, '{\"hora\": \"09:00:00.000000\", \"fecha\": \"2026-04-01\", \"estado\": \"pendiente\", \"id_cita\": 22, \"id_barbero\": 1, \"id_cliente\": 15, \"estado_pago\": \"pendiente\", \"duracion_total\": 30}', '188.114.111.237', '2026-04-22 10:01:47'),
(2, 'cita', 'DELETE', 22, 'alfonsogm', '{\"hora\": \"09:00:00.000000\", \"fecha\": \"2026-04-01\", \"estado\": \"pendiente\", \"id_cita\": 22, \"id_barbero\": 1, \"id_cliente\": 15, \"estado_pago\": \"pendiente\", \"duracion_total\": 30}', NULL, '188.114.111.237', '2026-04-22 10:01:50'),
(3, 'cita', 'INSERT', 23, 'Alejandro Rivilla', NULL, '{\"hora\": \"09:00:00.000000\", \"fecha\": \"2026-06-04\", \"estado\": \"pendiente\", \"id_cita\": 23, \"id_barbero\": 1, \"id_cliente\": 15, \"estado_pago\": \"pendiente\", \"duracion_total\": 30}', '188.114.111.236', '2026-04-22 10:22:15'),
(4, 'cita', 'DELETE', 23, 'Alejandro Rivilla', '{\"hora\": \"09:00:00.000000\", \"fecha\": \"2026-06-04\", \"estado\": \"pendiente\", \"id_cita\": 23, \"id_barbero\": 1, \"id_cliente\": 15, \"estado_pago\": \"pendiente\", \"duracion_total\": 30}', NULL, '188.114.111.236', '2026-04-22 10:22:24'),
(5, 'cita', 'INSERT', 24, 'alfonsogm', NULL, '{\"hora\": \"09:00:00.000000\", \"fecha\": \"2026-06-01\", \"estado\": \"pendiente\", \"id_cita\": 24, \"id_barbero\": 1, \"id_cliente\": 15, \"estado_pago\": \"pendiente\", \"duracion_total\": 30}', '188.114.111.236', '2026-04-22 10:44:54'),
(6, 'cita', 'DELETE', 24, 'alfonsogm', '{\"hora\": \"09:00:00.000000\", \"fecha\": \"2026-06-01\", \"estado\": \"pendiente\", \"id_cita\": 24, \"id_barbero\": 1, \"id_cliente\": 15, \"estado_pago\": \"pendiente\", \"duracion_total\": 30}', NULL, '188.114.111.236', '2026-04-22 10:45:01'),
(7, 'cliente', 'INSERT', 21, 'system', NULL, '{\"dni\": \"21769357H\", \"email\": \"notiene@notiene.es\", \"nombre\": \"prueba\", \"telefono\": null, \"id_cliente\": 21}', NULL, '2026-04-22 11:47:31'),
(8, 'cliente', 'DELETE', 21, 'system', '{\"dni\": \"21769357H\", \"email\": \"notiene@notiene.es\", \"nombre\": \"prueba\", \"telefono\": null}', NULL, NULL, '2026-04-22 12:00:05'),
(9, 'cliente', 'INSERT', 22, 'system', NULL, '{\"dni\": \"28924552M\", \"email\": \"notiene@notiene.es\", \"nombre\": \"prueba\", \"telefono\": null, \"id_cliente\": 22}', NULL, '2026-04-22 14:46:20'),
(10, 'cliente', 'DELETE', 22, 'system', '{\"dni\": \"28924552M\", \"email\": \"notiene@notiene.es\", \"nombre\": \"prueba\", \"telefono\": null}', NULL, NULL, '2026-04-22 14:47:15'),
(11, 'usuario', 'INSERT', 3, 'system', NULL, '{\"rol\": \"barbero\", \"activo\": 1, \"username\": \"prueba\", \"id_usuario\": 3}', NULL, '2026-04-22 15:05:38'),
(12, 'barbero', 'INSERT', 2, 'system', NULL, '{\"activo\": 1, \"nombre\": \"prueba\", \"id_barbero\": 2, \"especialidad\": \"ninguna\"}', NULL, '2026-04-22 15:05:38'),
(13, 'usuario', 'DELETE', 3, 'system', '{\"rol\": \"barbero\", \"activo\": 1, \"username\": \"prueba\"}', NULL, NULL, '2026-04-22 15:13:26'),
(14, 'usuario', 'INSERT', 4, 'alfonsogm', NULL, '{\"rol\": \"barbero\", \"activo\": 0, \"username\": \"prueba\", \"id_usuario\": 4}', NULL, '2026-04-22 15:14:01'),
(15, 'barbero', 'INSERT', 3, 'alfonsogm', NULL, '{\"activo\": 1, \"nombre\": \"prueba\", \"id_barbero\": 3, \"especialidad\": \"ninguna\"}', NULL, '2026-04-22 15:14:01'),
(16, 'usuario', 'DELETE', 4, 'alfonsogm', '{\"rol\": \"barbero\", \"activo\": 0, \"username\": \"prueba\"}', NULL, NULL, '2026-04-22 15:25:45'),
(17, 'usuario', 'INSERT', 5, 'alfonsogm', NULL, '{\"rol\": \"barbero\", \"activo\": 0, \"username\": \"prueba2\", \"id_usuario\": 5}', NULL, '2026-04-22 15:27:14'),
(18, 'barbero', 'INSERT', 4, 'alfonsogm', NULL, '{\"activo\": 1, \"nombre\": \"brueba22\", \"id_barbero\": 4, \"especialidad\": \"\"}', NULL, '2026-04-22 15:27:14'),
(19, 'usuario', 'UPDATE', 5, 'alfonsogm', '{\"rol\": \"barbero\", \"activo\": 0, \"password\": \"$2y$12$m050xOoA.GK7o/qNMoP8bOs5ijsnFZyCPAgoexJ/724o1cebWgj1K\", \"username\": \"prueba2\"}', '{\"rol\": \"barbero\", \"activo\": 0, \"password\": \"Prueaba@@@1234\", \"username\": \"prueba\"}', NULL, '2026-04-22 15:27:39'),
(20, 'barbero', 'UPDATE', 4, 'alfonsogm', '{\"activo\": 1, \"nombre\": \"brueba22\", \"especialidad\": \"\"}', '{\"activo\": 1, \"nombre\": \"prueba2\", \"especialidad\": \"\"}', NULL, '2026-04-22 15:27:39'),
(21, 'barbero', 'DELETE', 4, 'alfonsogm', '{\"activo\": 1, \"nombre\": \"prueba2\", \"especialidad\": \"\"}', NULL, NULL, '2026-04-22 15:28:24'),
(22, 'usuario', 'DELETE', 5, 'alfonsogm', '{\"rol\": \"barbero\", \"activo\": 0, \"username\": \"prueba\"}', NULL, NULL, '2026-04-22 15:28:28'),
(23, 'cita', 'INSERT', 25, 'alfonsogm', NULL, '{\"hora\": \"09:30:00.000000\", \"fecha\": \"2026-06-04\", \"estado\": \"pendiente\", \"id_cita\": 25, \"id_barbero\": 1, \"id_cliente\": 15, \"estado_pago\": \"pendiente\", \"duracion_total\": 60}', NULL, '2026-04-22 15:29:38'),
(24, 'cita', 'DELETE', 25, 'alfonsogm', '{\"hora\": \"09:30:00.000000\", \"fecha\": \"2026-06-04\", \"estado\": \"pendiente\", \"id_cita\": 25, \"id_barbero\": 1, \"id_cliente\": 15, \"estado_pago\": \"pendiente\", \"duracion_total\": 60}', NULL, NULL, '2026-04-22 15:29:44'),
(25, 'cita', 'INSERT', 26, 'Alejandro Rivilla', NULL, '{\"hora\": \"09:00:00.000000\", \"fecha\": \"2026-07-02\", \"estado\": \"pendiente\", \"id_cita\": 26, \"id_barbero\": 1, \"id_cliente\": 15, \"estado_pago\": \"pendiente\", \"duracion_total\": 60}', NULL, '2026-04-22 15:30:33'),
(26, 'cita', 'UPDATE', 26, 'Alejandro Rivilla', '{\"hora\": \"09:00:00.000000\", \"fecha\": \"2026-07-02\", \"estado\": \"pendiente\", \"id_cita\": 26, \"id_barbero\": 1, \"id_cliente\": 15, \"estado_pago\": \"pendiente\", \"duracion_total\": 60}', '{\"hora\": \"12:00:00.000000\", \"fecha\": \"2026-07-02\", \"estado\": \"pendiente\", \"id_cita\": 26, \"id_barbero\": 1, \"id_cliente\": 15, \"estado_pago\": \"pendiente\", \"duracion_total\": 60}', NULL, '2026-04-22 15:30:52'),
(27, 'cita', 'DELETE', 26, 'Alejandro Rivilla', '{\"hora\": \"12:00:00.000000\", \"fecha\": \"2026-07-02\", \"estado\": \"pendiente\", \"id_cita\": 26, \"id_barbero\": 1, \"id_cliente\": 15, \"estado_pago\": \"pendiente\", \"duracion_total\": 60}', NULL, NULL, '2026-04-22 15:31:00'),
(28, 'cliente', 'INSERT', 23, 'system', NULL, '{\"dni\": \"86038824X\", \"email\": \"notiene@notiene.es\", \"nombre\": \"prueba\", \"telefono\": null, \"id_cliente\": 23}', NULL, '2026-04-22 15:31:20'),
(29, 'cliente', 'DELETE', 23, 'alfonsogm', '{\"dni\": \"86038824X\", \"email\": \"notiene@notiene.es\", \"nombre\": \"prueba\", \"telefono\": null}', NULL, NULL, '2026-04-22 15:32:20'),
(30, 'cliente', 'INSERT', 24, 'system', NULL, '{\"dni\": \"65634161G\", \"email\": \"notiene@notiene.es\", \"nombre\": \"prueba\", \"telefono\": null, \"id_cliente\": 24}', NULL, '2026-04-22 15:32:54'),
(31, 'cliente', 'DELETE', 24, 'alfonsogm', '{\"dni\": \"65634161G\", \"email\": \"notiene@notiene.es\", \"nombre\": \"prueba\", \"telefono\": null}', NULL, NULL, '2026-04-22 15:33:03'),
(32, 'cliente', 'INSERT', 25, 'system', NULL, '{\"dni\": \"55542050V\", \"email\": \"notiene@notiene.es\", \"nombre\": \"prueba\", \"telefono\": null, \"id_cliente\": 25}', NULL, '2026-04-22 15:35:27'),
(33, 'cliente', 'DELETE', 25, 'alfonsogm', '{\"dni\": \"55542050V\", \"email\": \"notiene@notiene.es\", \"nombre\": \"prueba\", \"telefono\": null}', NULL, NULL, '2026-04-22 15:37:32'),
(34, 'cliente', 'INSERT', 26, 'alfonsogm', NULL, '{\"dni\": \"60486792N\", \"email\": \"notinee@no.es\", \"nombre\": \"prueba ultima\", \"telefono\": null, \"id_cliente\": 26}', NULL, '2026-04-22 15:37:54'),
(35, 'cliente', 'DELETE', 26, 'alfonsogm', '{\"dni\": \"60486792N\", \"email\": \"notinee@no.es\", \"nombre\": \"prueba ultima\", \"telefono\": null}', NULL, NULL, '2026-04-22 15:38:08'),
(36, 'cita', 'UPDATE', 19, 'Alejandro Rivilla', '{\"hora\": \"17:30:00.000000\", \"fecha\": \"2026-04-22\", \"estado\": \"pendiente\", \"id_cita\": 19, \"id_barbero\": 1, \"id_cliente\": 17, \"estado_pago\": \"pendiente\", \"duracion_total\": 30}', '{\"hora\": \"17:30:00.000000\", \"fecha\": \"2026-04-22\", \"estado\": \"completada\", \"id_cita\": 19, \"id_barbero\": 1, \"id_cliente\": 17, \"estado_pago\": \"pendiente\", \"duracion_total\": 30}', NULL, '2026-04-22 17:56:27'),
(37, 'cita', 'UPDATE', 20, 'Alejandro Rivilla', '{\"hora\": \"16:30:00.000000\", \"fecha\": \"2026-04-22\", \"estado\": \"pendiente\", \"id_cita\": 20, \"id_barbero\": 1, \"id_cliente\": 18, \"estado_pago\": \"pendiente\", \"duracion_total\": 30}', '{\"hora\": \"16:30:00.000000\", \"fecha\": \"2026-04-22\", \"estado\": \"completada\", \"id_cita\": 20, \"id_barbero\": 1, \"id_cliente\": 18, \"estado_pago\": \"pendiente\", \"duracion_total\": 30}', NULL, '2026-04-22 17:56:38'),
(38, 'cita', 'INSERT', 27, 'alfonsogm', NULL, '{\"hora\": \"09:15:00.000000\", \"fecha\": \"2026-07-01\", \"estado\": \"pendiente\", \"id_cita\": 27, \"id_barbero\": 1, \"id_cliente\": 15, \"estado_pago\": \"pendiente\", \"duracion_total\": 30}', NULL, '2026-04-23 08:49:57'),
(39, 'cita', 'DELETE', 27, 'alfonsogm', '{\"hora\": \"09:15:00.000000\", \"fecha\": \"2026-07-01\", \"estado\": \"pendiente\", \"id_cita\": 27, \"id_barbero\": 1, \"id_cliente\": 15, \"estado_pago\": \"pendiente\", \"duracion_total\": 30}', NULL, NULL, '2026-04-23 08:50:03'),
(40, 'cita', 'INSERT', 28, 'alfonsogm', NULL, '{\"hora\": \"11:15:00.000000\", \"fecha\": \"2026-07-01\", \"estado\": \"pendiente\", \"id_cita\": 28, \"id_barbero\": 1, \"id_cliente\": 15, \"estado_pago\": \"pendiente\", \"duracion_total\": 30}', NULL, '2026-04-23 08:57:00'),
(41, 'cita', 'DELETE', 28, 'alfonsogm', '{\"hora\": \"11:15:00.000000\", \"fecha\": \"2026-07-01\", \"estado\": \"pendiente\", \"id_cita\": 28, \"id_barbero\": 1, \"id_cliente\": 15, \"estado_pago\": \"pendiente\", \"duracion_total\": 30}', NULL, NULL, '2026-04-23 08:57:05'),
(42, 'cita', 'INSERT', 29, 'alfonsogm', NULL, '{\"hora\": \"09:00:00.000000\", \"fecha\": \"2026-07-02\", \"estado\": \"pendiente\", \"id_cita\": 29, \"id_barbero\": 1, \"id_cliente\": 15, \"estado_pago\": \"pendiente\", \"duracion_total\": 30}', NULL, '2026-04-23 10:07:21'),
(43, 'cita', 'DELETE', 29, 'alfonsogm', '{\"hora\": \"09:00:00.000000\", \"fecha\": \"2026-07-02\", \"estado\": \"pendiente\", \"id_cita\": 29, \"id_barbero\": 1, \"id_cliente\": 15, \"estado_pago\": \"pendiente\", \"duracion_total\": 30}', NULL, NULL, '2026-04-23 10:07:23'),
(44, 'cliente', 'INSERT', 27, 'Alejandro Rivilla', NULL, '{\"dni\": \"77149376Q\", \"email\": \"ahahahah@gmail.com\", \"nombre\": \"Paco suegro\", \"telefono\": null, \"id_cliente\": 27}', NULL, '2026-04-23 12:43:35'),
(45, 'cita', 'INSERT', 30, 'Alejandro Rivilla', NULL, '{\"hora\": \"17:30:00.000000\", \"fecha\": \"2026-04-23\", \"estado\": \"pendiente\", \"id_cita\": 30, \"id_barbero\": 1, \"id_cliente\": 27, \"estado_pago\": \"pendiente\", \"duracion_total\": 30}', NULL, '2026-04-23 12:44:15'),
(46, 'cita', 'INSERT', 31, 'Alejandro Rivilla', NULL, '{\"hora\": \"18:30:00.000000\", \"fecha\": \"2026-04-23\", \"estado\": \"pendiente\", \"id_cita\": 31, \"id_barbero\": 1, \"id_cliente\": 14, \"estado_pago\": \"pendiente\", \"duracion_total\": 30}', NULL, '2026-04-23 12:44:27'),
(47, 'cliente', 'INSERT', 28, 'Alejandro Rivilla', NULL, '{\"dni\": \"62302679A\", \"email\": \"shhshshs@gmail.com\", \"nombre\": \"Jefe\", \"telefono\": null, \"id_cliente\": 28}', NULL, '2026-04-23 12:44:47'),
(48, 'cita', 'INSERT', 32, 'Alejandro Rivilla', NULL, '{\"hora\": \"19:15:00.000000\", \"fecha\": \"2026-04-23\", \"estado\": \"pendiente\", \"id_cita\": 32, \"id_barbero\": 1, \"id_cliente\": 28, \"estado_pago\": \"pendiente\", \"duracion_total\": 30}', NULL, '2026-04-23 12:45:03'),
(49, 'cita', 'UPDATE', 14, 'Alejandro Rivilla', '{\"hora\": \"18:15:00.000000\", \"fecha\": \"2026-04-24\", \"estado\": \"pendiente\", \"id_cita\": 14, \"id_barbero\": 1, \"id_cliente\": 14, \"estado_pago\": \"pendiente\", \"duracion_total\": 30}', '{\"hora\": \"18:15:00.000000\", \"fecha\": \"2026-04-24\", \"estado\": \"cancelada\", \"id_cita\": 14, \"id_barbero\": 1, \"id_cliente\": 14, \"estado_pago\": \"pendiente\", \"duracion_total\": 30}', NULL, '2026-04-23 12:45:20'),
(50, 'cita', 'UPDATE', 30, '', '{\"hora\": \"17:30:00.000000\", \"fecha\": \"2026-04-23\", \"estado\": \"pendiente\", \"id_cita\": 30, \"id_barbero\": 1, \"id_cliente\": 27, \"estado_pago\": \"pendiente\", \"duracion_total\": 30}', '{\"hora\": \"17:30:00.000000\", \"fecha\": \"2026-04-23\", \"estado\": \"cancelada\", \"id_cita\": 30, \"id_barbero\": 1, \"id_cliente\": 27, \"estado_pago\": \"pendiente\", \"duracion_total\": 30}', NULL, '2026-04-23 13:01:40'),
(51, 'cita', 'UPDATE', 30, '', '{\"hora\": \"17:30:00.000000\", \"fecha\": \"2026-04-23\", \"estado\": \"cancelada\", \"id_cita\": 30, \"id_barbero\": 1, \"id_cliente\": 27, \"estado_pago\": \"pendiente\", \"duracion_total\": 30}', '{\"hora\": \"17:30:00.000000\", \"fecha\": \"2026-04-23\", \"estado\": \"cancelada\", \"id_cita\": 30, \"id_barbero\": 1, \"id_cliente\": 27, \"estado_pago\": \"pendiente\", \"duracion_total\": 30}', NULL, '2026-04-23 13:01:43'),
(52, 'cita', 'INSERT', 33, 'Alejandro Rivilla', NULL, '{\"hora\": \"17:30:00.000000\", \"fecha\": \"2026-04-24\", \"estado\": \"pendiente\", \"id_cita\": 33, \"id_barbero\": 1, \"id_cliente\": 27, \"estado_pago\": \"pendiente\", \"duracion_total\": 30}', NULL, '2026-04-23 13:02:10'),
(53, 'cliente', 'INSERT', 29, 'Alejandro Rivilla', NULL, '{\"dni\": \"69147951S\", \"email\": \"qhahhah@gmail.com\", \"nombre\": \"Yhostin\", \"telefono\": null, \"id_cliente\": 29}', NULL, '2026-04-23 15:18:34'),
(54, 'cita', 'INSERT', 34, 'Alejandro Rivilla', NULL, '{\"hora\": \"13:00:00.000000\", \"fecha\": \"2026-04-25\", \"estado\": \"pendiente\", \"id_cita\": 34, \"id_barbero\": 1, \"id_cliente\": 29, \"estado_pago\": \"pendiente\", \"duracion_total\": 30}', NULL, '2026-04-23 15:18:55'),
(55, 'cliente', 'INSERT', 30, 'Alejandro Rivilla', NULL, '{\"dni\": \"26717384D\", \"email\": \"ashhah@gmail.com\", \"nombre\": \"Guerol\", \"telefono\": null, \"id_cliente\": 30}', NULL, '2026-04-23 17:38:24'),
(56, 'cita', 'INSERT', 35, 'Alejandro Rivilla', NULL, '{\"hora\": \"18:00:00.000000\", \"fecha\": \"2026-04-23\", \"estado\": \"pendiente\", \"id_cita\": 35, \"id_barbero\": 1, \"id_cliente\": 30, \"estado_pago\": \"pendiente\", \"duracion_total\": 30}', NULL, '2026-04-23 17:38:56'),
(57, 'cliente', 'INSERT', 31, 'Alejandro Rivilla', NULL, '{\"dni\": \"61413658R\", \"email\": \"ahahhaha@gmail.com\", \"nombre\": \"Bili\", \"telefono\": null, \"id_cliente\": 31}', NULL, '2026-04-23 17:39:18'),
(58, 'cita', 'INSERT', 36, 'Alejandro Rivilla', NULL, '{\"hora\": \"16:00:00.000000\", \"fecha\": \"2026-04-24\", \"estado\": \"pendiente\", \"id_cita\": 36, \"id_barbero\": 1, \"id_cliente\": 31, \"estado_pago\": \"pendiente\", \"duracion_total\": 30}', NULL, '2026-04-23 17:39:38'),
(59, 'cliente', 'INSERT', 32, 'Alejandro Rivilla', NULL, '{\"dni\": \"30672665A\", \"email\": \"ahahahhaha@gmail.com\", \"nombre\": \"Alberto Guili\", \"telefono\": null, \"id_cliente\": 32}', NULL, '2026-04-23 17:40:01'),
(60, 'cita', 'INSERT', 37, 'Alejandro Rivilla', NULL, '{\"hora\": \"12:15:00.000000\", \"fecha\": \"2026-04-25\", \"estado\": \"pendiente\", \"id_cita\": 37, \"id_barbero\": 1, \"id_cliente\": 32, \"estado_pago\": \"pendiente\", \"duracion_total\": 30}', NULL, '2026-04-23 17:40:37'),
(61, 'cliente', 'INSERT', 33, 'Alejandro Rivilla', NULL, '{\"dni\": \"64766387C\", \"email\": \"ahahah@gmail.com\", \"nombre\": \"Abuelo\", \"telefono\": null, \"id_cliente\": 33}', NULL, '2026-04-24 10:39:10'),
(62, 'cita', 'INSERT', 38, 'Alejandro Rivilla', NULL, '{\"hora\": \"12:00:00.000000\", \"fecha\": \"2026-04-24\", \"estado\": \"pendiente\", \"id_cita\": 38, \"id_barbero\": 1, \"id_cliente\": 33, \"estado_pago\": \"pendiente\", \"duracion_total\": 30}', NULL, '2026-04-24 10:39:41'),
(63, 'cita', 'UPDATE', 38, 'Alejandro Rivilla', '{\"hora\": \"12:00:00.000000\", \"fecha\": \"2026-04-24\", \"estado\": \"pendiente\", \"id_cita\": 38, \"id_barbero\": 1, \"id_cliente\": 33, \"estado_pago\": \"pendiente\", \"duracion_total\": 30}', '{\"hora\": \"12:00:00.000000\", \"fecha\": \"2026-04-24\", \"estado\": \"completada\", \"id_cita\": 38, \"id_barbero\": 1, \"id_cliente\": 33, \"estado_pago\": \"pendiente\", \"duracion_total\": 30}', NULL, '2026-04-24 10:40:05'),
(64, 'cita', 'UPDATE', 37, 'Alejandro Rivilla', '{\"hora\": \"12:15:00.000000\", \"fecha\": \"2026-04-25\", \"estado\": \"pendiente\", \"id_cita\": 37, \"id_barbero\": 1, \"id_cliente\": 32, \"estado_pago\": \"pendiente\", \"duracion_total\": 30}', '{\"hora\": \"12:15:00.000000\", \"fecha\": \"2026-04-25\", \"estado\": \"completada\", \"id_cita\": 37, \"id_barbero\": 1, \"id_cliente\": 32, \"estado_pago\": \"pendiente\", \"duracion_total\": 30}', NULL, '2026-04-25 11:11:41'),
(65, 'cita', 'UPDATE', 34, 'Alejandro Rivilla', '{\"hora\": \"13:00:00.000000\", \"fecha\": \"2026-04-25\", \"estado\": \"pendiente\", \"id_cita\": 34, \"id_barbero\": 1, \"id_cliente\": 29, \"estado_pago\": \"pendiente\", \"duracion_total\": 30}', '{\"hora\": \"13:00:00.000000\", \"fecha\": \"2026-04-25\", \"estado\": \"completada\", \"id_cita\": 34, \"id_barbero\": 1, \"id_cliente\": 29, \"estado_pago\": \"pendiente\", \"duracion_total\": 30}', NULL, '2026-04-25 11:11:50'),
(66, 'cliente', 'INSERT', 34, 'Alejandro Rivilla', NULL, '{\"dni\": \"37560443V\", \"email\": \"ashhaha@gmail.com\", \"nombre\": \"Manuteros\", \"telefono\": null, \"id_cliente\": 34}', NULL, '2026-05-11 20:07:30'),
(67, 'cita', 'INSERT', 39, 'Alejandro Rivilla', NULL, '{\"hora\": \"13:00:00.000000\", \"fecha\": \"2026-05-12\", \"estado\": \"pendiente\", \"id_cita\": 39, \"id_barbero\": 1, \"id_cliente\": 34, \"estado_pago\": \"pendiente\", \"duracion_total\": 60}', NULL, '2026-05-11 20:07:56'),
(68, 'cliente', 'INSERT', 35, 'Alejandro Rivilla', NULL, '{\"dni\": \"45997255S\", \"email\": \"xxzd@gmail.com\", \"nombre\": \"Pedro escabias\", \"telefono\": null, \"id_cliente\": 35}', NULL, '2026-05-11 20:08:47'),
(69, 'cita', 'INSERT', 40, 'Alejandro Rivilla', NULL, '{\"hora\": \"11:30:00.000000\", \"fecha\": \"2026-05-13\", \"estado\": \"pendiente\", \"id_cita\": 40, \"id_barbero\": 1, \"id_cliente\": 35, \"estado_pago\": \"pendiente\", \"duracion_total\": 30}', NULL, '2026-05-11 20:09:15'),
(70, 'cliente', 'INSERT', 36, 'Alejandro Rivilla', NULL, '{\"dni\": \"19472523X\", \"email\": \"shahha@gmail.com\", \"nombre\": \"Alvaro compadre\", \"telefono\": null, \"id_cliente\": 36}', NULL, '2026-05-11 20:12:21'),
(71, 'cita', 'INSERT', 41, 'Alejandro Rivilla', NULL, '{\"hora\": \"12:15:00.000000\", \"fecha\": \"2026-05-13\", \"estado\": \"pendiente\", \"id_cita\": 41, \"id_barbero\": 1, \"id_cliente\": 36, \"estado_pago\": \"pendiente\", \"duracion_total\": 30}', NULL, '2026-05-11 20:12:38'),
(72, 'cita', 'INSERT', 42, 'Alejandro Rivilla', NULL, '{\"hora\": \"16:00:00.000000\", \"fecha\": \"2026-05-13\", \"estado\": \"pendiente\", \"id_cita\": 42, \"id_barbero\": 1, \"id_cliente\": 8, \"estado_pago\": \"pendiente\", \"duracion_total\": 30}', NULL, '2026-05-11 20:12:58'),
(73, 'cliente', 'INSERT', 37, 'Alejandro Rivilla', NULL, '{\"dni\": \"68724262D\", \"email\": \"qhaha@gmail.com\", \"nombre\": \"David cobo\", \"telefono\": null, \"id_cliente\": 37}', NULL, '2026-05-11 20:13:24'),
(74, 'cita', 'INSERT', 43, 'Alejandro Rivilla', NULL, '{\"hora\": \"18:15:00.000000\", \"fecha\": \"2026-05-13\", \"estado\": \"pendiente\", \"id_cita\": 43, \"id_barbero\": 1, \"id_cliente\": 37, \"estado_pago\": \"pendiente\", \"duracion_total\": 30}', NULL, '2026-05-11 20:13:58'),
(75, 'cita', 'INSERT', 44, 'Alejandro Rivilla', NULL, '{\"hora\": \"19:00:00.000000\", \"fecha\": \"2026-05-13\", \"estado\": \"pendiente\", \"id_cita\": 44, \"id_barbero\": 1, \"id_cliente\": 5, \"estado_pago\": \"pendiente\", \"duracion_total\": 30}', NULL, '2026-05-11 20:14:14'),
(76, 'cita', 'DELETE', 43, 'Alejandro Rivilla', '{\"hora\": \"18:15:00.000000\", \"fecha\": \"2026-05-13\", \"estado\": \"pendiente\", \"id_cita\": 43, \"id_barbero\": 1, \"id_cliente\": 37, \"estado_pago\": \"pendiente\", \"duracion_total\": 30}', NULL, NULL, '2026-05-11 20:21:48'),
(77, 'cliente', 'INSERT', 38, 'Alejandro Rivilla', NULL, '{\"dni\": \"46505562E\", \"email\": \"aajaj@gmail.com\", \"nombre\": \"Victor gofer\", \"telefono\": null, \"id_cliente\": 38}', NULL, '2026-05-11 20:24:18'),
(78, 'cita', 'INSERT', 45, 'Alejandro Rivilla', NULL, '{\"hora\": \"13:00:00.000000\", \"fecha\": \"2026-05-13\", \"estado\": \"pendiente\", \"id_cita\": 45, \"id_barbero\": 1, \"id_cliente\": 38, \"estado_pago\": \"pendiente\", \"duracion_total\": 60}', NULL, '2026-05-11 20:24:47'),
(79, 'cliente', 'INSERT', 39, 'Alejandro Rivilla', NULL, '{\"dni\": \"29359509D\", \"email\": \"aah@gmail.com\", \"nombre\": \"Reda\", \"telefono\": null, \"id_cliente\": 39}', NULL, '2026-05-11 20:26:00'),
(80, 'cita', 'INSERT', 46, 'Alejandro Rivilla', NULL, '{\"hora\": \"16:45:00.000000\", \"fecha\": \"2026-05-13\", \"estado\": \"pendiente\", \"id_cita\": 46, \"id_barbero\": 1, \"id_cliente\": 39, \"estado_pago\": \"pendiente\", \"duracion_total\": 30}', NULL, '2026-05-11 20:26:20'),
(81, 'cliente', 'INSERT', 40, 'Alejandro Rivilla', NULL, '{\"dni\": \"92370033D\", \"email\": \"ahahah@gmail.com\", \"nombre\": \"Sergio castro\", \"telefono\": null, \"id_cliente\": 40}', NULL, '2026-05-11 20:26:47'),
(82, 'cita', 'INSERT', 47, 'Alejandro Rivilla', NULL, '{\"hora\": \"17:30:00.000000\", \"fecha\": \"2026-05-13\", \"estado\": \"pendiente\", \"id_cita\": 47, \"id_barbero\": 1, \"id_cliente\": 40, \"estado_pago\": \"pendiente\", \"duracion_total\": 30}', NULL, '2026-05-11 20:27:07'),
(83, 'cliente', 'INSERT', 41, 'Alejandro Rivilla', NULL, '{\"dni\": \"16532287W\", \"email\": \"ahahah@gmail.com\", \"nombre\": \"Kisko capullo\", \"telefono\": null, \"id_cliente\": 41}', NULL, '2026-05-11 20:27:59'),
(84, 'cita', 'INSERT', 48, 'Alejandro Rivilla', NULL, '{\"hora\": \"09:00:00.000000\", \"fecha\": \"2026-05-14\", \"estado\": \"pendiente\", \"id_cita\": 48, \"id_barbero\": 1, \"id_cliente\": 41, \"estado_pago\": \"pendiente\", \"duracion_total\": 30}', NULL, '2026-05-11 20:28:14'),
(85, 'cliente', 'INSERT', 42, 'Alejandro Rivilla', NULL, '{\"dni\": \"47528753N\", \"email\": \"ahah@gmail.com\", \"nombre\": \"Tomas\", \"telefono\": null, \"id_cliente\": 42}', NULL, '2026-05-11 20:31:03'),
(86, 'cita', 'INSERT', 49, 'Alejandro Rivilla', NULL, '{\"hora\": \"16:00:00.000000\", \"fecha\": \"2026-05-14\", \"estado\": \"pendiente\", \"id_cita\": 49, \"id_barbero\": 1, \"id_cliente\": 42, \"estado_pago\": \"pendiente\", \"duracion_total\": 30}', NULL, '2026-05-11 20:31:21'),
(87, 'cliente', 'INSERT', 43, 'Alejandro Rivilla', NULL, '{\"dni\": \"76587686D\", \"email\": \"hh@gmail.com\", \"nombre\": \"Sergio alumno\", \"telefono\": null, \"id_cliente\": 43}', NULL, '2026-05-11 20:31:43'),
(88, 'cita', 'INSERT', 50, 'Alejandro Rivilla', NULL, '{\"hora\": \"16:45:00.000000\", \"fecha\": \"2026-05-14\", \"estado\": \"pendiente\", \"id_cita\": 50, \"id_barbero\": 1, \"id_cliente\": 43, \"estado_pago\": \"pendiente\", \"duracion_total\": 30}', NULL, '2026-05-11 20:31:59'),
(89, 'cliente', 'INSERT', 44, 'Alejandro Rivilla', NULL, '{\"dni\": \"50401657D\", \"email\": \"ahahha@gmail.com\", \"nombre\": \"Fernando alumno\", \"telefono\": null, \"id_cliente\": 44}', NULL, '2026-05-11 20:32:19'),
(90, 'cita', 'INSERT', 51, 'Alejandro Rivilla', NULL, '{\"hora\": \"17:30:00.000000\", \"fecha\": \"2026-05-14\", \"estado\": \"pendiente\", \"id_cita\": 51, \"id_barbero\": 1, \"id_cliente\": 44, \"estado_pago\": \"pendiente\", \"duracion_total\": 30}', NULL, '2026-05-11 20:32:41'),
(91, 'cliente', 'INSERT', 45, 'Alejandro Rivilla', NULL, '{\"dni\": \"33785392W\", \"email\": \"aab@gmail.com\", \"nombre\": \"Josema\", \"telefono\": null, \"id_cliente\": 45}', NULL, '2026-05-11 20:33:04'),
(92, 'cita', 'INSERT', 52, 'Alejandro Rivilla', NULL, '{\"hora\": \"18:15:00.000000\", \"fecha\": \"2026-05-14\", \"estado\": \"pendiente\", \"id_cita\": 52, \"id_barbero\": 1, \"id_cliente\": 45, \"estado_pago\": \"pendiente\", \"duracion_total\": 30}', NULL, '2026-05-11 20:33:24'),
(93, 'cita', 'INSERT', 53, 'Alejandro Rivilla', NULL, '{\"hora\": \"19:00:00.000000\", \"fecha\": \"2026-05-14\", \"estado\": \"pendiente\", \"id_cita\": 53, \"id_barbero\": 1, \"id_cliente\": 17, \"estado_pago\": \"pendiente\", \"duracion_total\": 30}', NULL, '2026-05-11 20:33:37'),
(94, 'cita', 'INSERT', 54, 'Alejandro Rivilla', NULL, '{\"hora\": \"19:45:00.000000\", \"fecha\": \"2026-05-14\", \"estado\": \"pendiente\", \"id_cita\": 54, \"id_barbero\": 1, \"id_cliente\": 7, \"estado_pago\": \"pendiente\", \"duracion_total\": 30}', NULL, '2026-05-11 20:33:49'),
(95, 'cliente', 'INSERT', 46, 'Alejandro Rivilla', NULL, '{\"dni\": \"13735970W\", \"email\": \"ahha@gmail.com\", \"nombre\": \"Jose pelaburros\", \"telefono\": null, \"id_cliente\": 46}', NULL, '2026-05-11 20:34:29'),
(96, 'cita', 'INSERT', 55, 'Alejandro Rivilla', NULL, '{\"hora\": \"10:00:00.000000\", \"fecha\": \"2026-05-15\", \"estado\": \"pendiente\", \"id_cita\": 55, \"id_barbero\": 1, \"id_cliente\": 46, \"estado_pago\": \"pendiente\", \"duracion_total\": 30}', NULL, '2026-05-11 20:34:50'),
(97, 'cita', 'INSERT', 56, 'Alejandro Rivilla', NULL, '{\"hora\": \"10:45:00.000000\", \"fecha\": \"2026-05-15\", \"estado\": \"pendiente\", \"id_cita\": 56, \"id_barbero\": 1, \"id_cliente\": 30, \"estado_pago\": \"pendiente\", \"duracion_total\": 30}', NULL, '2026-05-11 20:35:00'),
(98, 'cita', 'INSERT', 57, 'Alejandro Rivilla', NULL, '{\"hora\": \"11:30:00.000000\", \"fecha\": \"2026-05-15\", \"estado\": \"pendiente\", \"id_cita\": 57, \"id_barbero\": 1, \"id_cliente\": 12, \"estado_pago\": \"pendiente\", \"duracion_total\": 30}', NULL, '2026-05-11 20:35:09'),
(99, 'cliente', 'INSERT', 47, 'Alejandro Rivilla', NULL, '{\"dni\": \"22983377Y\", \"email\": \"ahahha@gmail.com\", \"nombre\": \"Juan escabias\", \"telefono\": null, \"id_cliente\": 47}', NULL, '2026-05-11 20:35:26'),
(100, 'cita', 'INSERT', 58, 'Alejandro Rivilla', NULL, '{\"hora\": \"12:15:00.000000\", \"fecha\": \"2026-05-15\", \"estado\": \"pendiente\", \"id_cita\": 58, \"id_barbero\": 1, \"id_cliente\": 47, \"estado_pago\": \"pendiente\", \"duracion_total\": 30}', NULL, '2026-05-11 20:35:41'),
(101, 'cliente', 'INSERT', 48, 'Alejandro Rivilla', NULL, '{\"dni\": \"52324535H\", \"email\": \"ahha@gmail.com\", \"nombre\": \"Mani\", \"telefono\": null, \"id_cliente\": 48}', NULL, '2026-05-11 20:35:55'),
(102, 'cita', 'INSERT', 59, 'Alejandro Rivilla', NULL, '{\"hora\": \"13:00:00.000000\", \"fecha\": \"2026-05-15\", \"estado\": \"pendiente\", \"id_cita\": 59, \"id_barbero\": 1, \"id_cliente\": 48, \"estado_pago\": \"pendiente\", \"duracion_total\": 30}', NULL, '2026-05-11 20:36:13'),
(103, 'cliente', 'INSERT', 49, 'Alejandro Rivilla', NULL, '{\"dni\": \"13899508X\", \"email\": \"ahah@gmail.com\", \"nombre\": \"Izan\", \"telefono\": null, \"id_cliente\": 49}', NULL, '2026-05-11 20:36:32'),
(104, 'cita', 'INSERT', 60, 'Alejandro Rivilla', NULL, '{\"hora\": \"16:00:00.000000\", \"fecha\": \"2026-05-15\", \"estado\": \"pendiente\", \"id_cita\": 60, \"id_barbero\": 1, \"id_cliente\": 49, \"estado_pago\": \"pendiente\", \"duracion_total\": 30}', NULL, '2026-05-11 20:36:50'),
(105, 'cliente', 'INSERT', 50, 'Alejandro Rivilla', NULL, '{\"dni\": \"11345831T\", \"email\": \"ahhaha@gmail.com\", \"nombre\": \"Roger nene\", \"telefono\": null, \"id_cliente\": 50}', NULL, '2026-05-11 20:37:10'),
(106, 'cita', 'INSERT', 61, 'Alejandro Rivilla', NULL, '{\"hora\": \"16:45:00.000000\", \"fecha\": \"2026-05-15\", \"estado\": \"pendiente\", \"id_cita\": 61, \"id_barbero\": 1, \"id_cliente\": 50, \"estado_pago\": \"pendiente\", \"duracion_total\": 30}', NULL, '2026-05-11 20:37:25'),
(107, 'cliente', 'INSERT', 51, 'Alejandro Rivilla', NULL, '{\"dni\": \"31707855D\", \"email\": \"ahah@gmail.com\", \"nombre\": \"Carbon\", \"telefono\": null, \"id_cliente\": 51}', NULL, '2026-05-11 20:37:41'),
(108, 'cita', 'INSERT', 62, 'Alejandro Rivilla', NULL, '{\"hora\": \"17:30:00.000000\", \"fecha\": \"2026-05-15\", \"estado\": \"pendiente\", \"id_cita\": 62, \"id_barbero\": 1, \"id_cliente\": 51, \"estado_pago\": \"pendiente\", \"duracion_total\": 30}', NULL, '2026-05-11 20:37:56'),
(109, 'cliente', 'INSERT', 52, 'Alejandro Rivilla', NULL, '{\"dni\": \"52687011Z\", \"email\": \"aaha@gmail.com\", \"nombre\": \"Guerre\", \"telefono\": null, \"id_cliente\": 52}', NULL, '2026-05-11 20:38:11'),
(110, 'cita', 'INSERT', 63, 'Alejandro Rivilla', NULL, '{\"hora\": \"18:15:00.000000\", \"fecha\": \"2026-05-15\", \"estado\": \"pendiente\", \"id_cita\": 63, \"id_barbero\": 1, \"id_cliente\": 52, \"estado_pago\": \"pendiente\", \"duracion_total\": 30}', NULL, '2026-05-11 20:38:27'),
(111, 'cita', 'INSERT', 64, 'Alejandro Rivilla', NULL, '{\"hora\": \"19:00:00.000000\", \"fecha\": \"2026-05-15\", \"estado\": \"pendiente\", \"id_cita\": 64, \"id_barbero\": 1, \"id_cliente\": 13, \"estado_pago\": \"pendiente\", \"duracion_total\": 30}', NULL, '2026-05-11 20:38:46'),
(112, 'cliente', 'INSERT', 53, 'Alejandro Rivilla', NULL, '{\"dni\": \"60856842S\", \"email\": \"ahaha@gmail.com\", \"nombre\": \"Dario pasaje\", \"telefono\": null, \"id_cliente\": 53}', NULL, '2026-05-11 20:39:42'),
(113, 'cita', 'INSERT', 65, 'Alejandro Rivilla', NULL, '{\"hora\": \"10:00:00.000000\", \"fecha\": \"2026-05-16\", \"estado\": \"pendiente\", \"id_cita\": 65, \"id_barbero\": 1, \"id_cliente\": 53, \"estado_pago\": \"pendiente\", \"duracion_total\": 30}', NULL, '2026-05-11 20:39:57'),
(114, 'cita', 'INSERT', 66, 'Alejandro Rivilla', NULL, '{\"hora\": \"10:45:00.000000\", \"fecha\": \"2026-05-16\", \"estado\": \"pendiente\", \"id_cita\": 66, \"id_barbero\": 1, \"id_cliente\": 32, \"estado_pago\": \"pendiente\", \"duracion_total\": 30}', NULL, '2026-05-11 20:40:07'),
(115, 'cliente', 'INSERT', 54, 'Alejandro Rivilla', NULL, '{\"dni\": \"76049903N\", \"email\": \"a@gmail.com\", \"nombre\": \"Sergio tomas\", \"telefono\": null, \"id_cliente\": 54}', NULL, '2026-05-11 20:40:27'),
(116, 'cita', 'INSERT', 67, 'Alejandro Rivilla', NULL, '{\"hora\": \"11:30:00.000000\", \"fecha\": \"2026-05-16\", \"estado\": \"pendiente\", \"id_cita\": 67, \"id_barbero\": 1, \"id_cliente\": 54, \"estado_pago\": \"pendiente\", \"duracion_total\": 30}', NULL, '2026-05-11 20:40:46'),
(117, 'cliente', 'INSERT', 55, 'Alejandro Rivilla', NULL, '{\"dni\": \"88717660J\", \"email\": \"ahaha@gmail.com\", \"nombre\": \"Chemari\", \"telefono\": null, \"id_cliente\": 55}', NULL, '2026-05-11 20:41:05'),
(118, 'cita', 'INSERT', 68, 'Alejandro Rivilla', NULL, '{\"hora\": \"12:15:00.000000\", \"fecha\": \"2026-05-16\", \"estado\": \"pendiente\", \"id_cita\": 68, \"id_barbero\": 1, \"id_cliente\": 55, \"estado_pago\": \"pendiente\", \"duracion_total\": 30}', NULL, '2026-05-11 20:41:25'),
(119, 'cliente', 'INSERT', 56, 'Alejandro Rivilla', NULL, '{\"dni\": \"38307540M\", \"email\": \"ahahha@gmail.com\", \"nombre\": \"Blas\", \"telefono\": null, \"id_cliente\": 56}', NULL, '2026-05-11 20:41:41'),
(120, 'cita', 'INSERT', 69, 'Alejandro Rivilla', NULL, '{\"hora\": \"13:00:00.000000\", \"fecha\": \"2026-05-16\", \"estado\": \"pendiente\", \"id_cita\": 69, \"id_barbero\": 1, \"id_cliente\": 56, \"estado_pago\": \"pendiente\", \"duracion_total\": 30}', NULL, '2026-05-11 20:41:56'),
(121, 'cliente', 'INSERT', 57, 'Alejandro Rivilla', NULL, '{\"dni\": \"31912100Z\", \"email\": \"ahha@gmail.com\", \"nombre\": \"Fran rucho\", \"telefono\": null, \"id_cliente\": 57}', NULL, '2026-05-11 20:42:17'),
(122, 'cita', 'INSERT', 70, 'Alejandro Rivilla', NULL, '{\"hora\": \"16:00:00.000000\", \"fecha\": \"2026-05-16\", \"estado\": \"pendiente\", \"id_cita\": 70, \"id_barbero\": 1, \"id_cliente\": 57, \"estado_pago\": \"pendiente\", \"duracion_total\": 30}', NULL, '2026-05-11 20:42:34'),
(123, 'cliente', 'INSERT', 58, 'Alejandro Rivilla', NULL, '{\"dni\": \"51368965Y\", \"email\": \"ahaha@gmail.com\", \"nombre\": \"Julito\", \"telefono\": null, \"id_cliente\": 58}', NULL, '2026-05-11 20:42:54'),
(124, 'cita', 'INSERT', 71, 'Alejandro Rivilla', NULL, '{\"hora\": \"16:45:00.000000\", \"fecha\": \"2026-05-16\", \"estado\": \"pendiente\", \"id_cita\": 71, \"id_barbero\": 1, \"id_cliente\": 58, \"estado_pago\": \"pendiente\", \"duracion_total\": 30}', NULL, '2026-05-11 20:43:10'),
(125, 'cita', 'INSERT', 72, 'Alejandro Rivilla', NULL, '{\"hora\": \"17:30:00.000000\", \"fecha\": \"2026-05-16\", \"estado\": \"pendiente\", \"id_cita\": 72, \"id_barbero\": 1, \"id_cliente\": 15, \"estado_pago\": \"pendiente\", \"duracion_total\": 30}', NULL, '2026-05-11 20:43:21'),
(126, 'cliente', 'INSERT', 59, 'Alejandro Rivilla', NULL, '{\"dni\": \"77704657P\", \"email\": \"ahahha@gmail.com\", \"nombre\": \"Pedro faroles\", \"telefono\": null, \"id_cliente\": 59}', NULL, '2026-05-12 07:37:48'),
(127, 'cita', 'INSERT', 73, 'Alejandro Rivilla', NULL, '{\"hora\": \"18:15:00.000000\", \"fecha\": \"2026-05-13\", \"estado\": \"pendiente\", \"id_cita\": 73, \"id_barbero\": 1, \"id_cliente\": 59, \"estado_pago\": \"pendiente\", \"duracion_total\": 30}', NULL, '2026-05-12 07:38:11');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `barbero`
--

CREATE TABLE `barbero` (
  `id_barbero` int NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `especialidad` varchar(100) DEFAULT NULL,
  `activo` tinyint(1) DEFAULT '1',
  `id_usuario` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `barbero`
--

INSERT INTO `barbero` (`id_barbero`, `nombre`, `especialidad`, `activo`, `id_usuario`) VALUES
(1, 'Alejandro Rivilla', '', 1, 1);

--
-- Disparadores `barbero`
--
DELIMITER $$
CREATE TRIGGER `barbero_after_delete` AFTER DELETE ON `barbero` FOR EACH ROW BEGIN

    INSERT INTO audit_log (
        tabla,
        accion,
        registro_id,
        usuario,
        datos_antes
    )
    VALUES (
        'barbero',
        'DELETE',
        OLD.id_barbero,
        COALESCE(@usuario_actual, 'system'),
        JSON_OBJECT(
            'nombre', OLD.nombre,
            'especialidad', OLD.especialidad,
            'activo', OLD.activo
        )
    );
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `barbero_after_insert` AFTER INSERT ON `barbero` FOR EACH ROW BEGIN

    INSERT INTO audit_log (
        tabla,
        accion,
        registro_id,
        usuario,
        datos_despues
    )
    VALUES (
        'barbero',
        'INSERT',
        NEW.id_barbero,
        COALESCE(@usuario_actual, 'system'),
        JSON_OBJECT(
            'id_barbero', NEW.id_barbero,
            'nombre', NEW.nombre,
            'especialidad', NEW.especialidad,
            'activo', NEW.activo
        )
    );
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `barbero_after_update` AFTER UPDATE ON `barbero` FOR EACH ROW BEGIN

    INSERT INTO audit_log (
        tabla,
        accion,
        registro_id,
        usuario,
        datos_antes,
        datos_despues
    )
    VALUES (
        'barbero',
        'UPDATE',
        NEW.id_barbero,
        COALESCE(@usuario_actual, 'system'),
        JSON_OBJECT(
            'nombre', OLD.nombre,
            'especialidad', OLD.especialidad,
            'activo', OLD.activo
        ),
        JSON_OBJECT(
            'nombre', NEW.nombre,
            'especialidad', NEW.especialidad,
            'activo', NEW.activo
        )
    );
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cita`
--

CREATE TABLE `cita` (
  `id_cita` int NOT NULL,
  `id_cliente` int NOT NULL,
  `id_barbero` int DEFAULT NULL,
  `fecha` date NOT NULL,
  `hora` time NOT NULL,
  `estado` enum('pendiente','confirmada','completada','cancelada','fallida') DEFAULT 'pendiente',
  `estado_pago` enum('pendiente','pagado') DEFAULT 'pendiente',
  `notas` text,
  `fecha_creacion` datetime DEFAULT CURRENT_TIMESTAMP,
  `duracion_total` int NOT NULL,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `cita`
--

INSERT INTO `cita` (`id_cita`, `id_cliente`, `id_barbero`, `fecha`, `hora`, `estado`, `estado_pago`, `notas`, `fecha_creacion`, `duracion_total`, `updated_at`) VALUES
(1, 2, 1, '2026-04-20', '13:00:00', 'completada', 'pendiente', '', '2026-04-20 06:33:40', 30, '2026-04-20 14:45:36'),
(2, 3, 1, '2026-04-20', '16:00:00', 'completada', 'pendiente', '', '2026-04-20 06:34:55', 30, '2026-04-20 14:46:19'),
(3, 4, 1, '2026-04-20', '16:45:00', 'completada', 'pendiente', '', '2026-04-20 06:35:52', 30, '2026-04-20 14:45:51'),
(4, 5, 1, '2026-04-20', '17:30:00', 'completada', 'pendiente', '', '2026-04-20 06:36:34', 30, '2026-04-20 14:46:31'),
(5, 6, 1, '2026-04-21', '11:00:00', 'completada', 'pendiente', '', '2026-04-20 07:34:36', 30, '2026-04-21 10:55:00'),
(6, 7, 1, '2026-04-21', '16:30:00', 'completada', 'pendiente', '', '2026-04-20 07:35:24', 30, '2026-04-21 18:35:29'),
(7, 8, 1, '2026-04-21', '17:15:00', 'completada', 'pendiente', '', '2026-04-20 07:36:13', 30, '2026-04-21 18:35:41'),
(9, 9, 1, '2026-04-21', '18:00:00', 'completada', 'pendiente', '', '2026-04-20 07:38:06', 60, '2026-04-21 18:35:57'),
(10, 10, 1, '2026-04-21', '19:00:00', 'completada', 'pendiente', '', '2026-04-20 07:38:52', 30, '2026-04-21 18:36:43'),
(11, 11, 1, '2026-04-22', '18:30:00', 'cancelada', 'pendiente', '', '2026-04-20 07:40:03', 30, '2026-04-20 17:24:23'),
(12, 12, 1, '2026-04-23', '16:00:00', 'pendiente', 'pendiente', NULL, '2026-04-20 07:41:43', 30, '2026-04-20 07:41:43'),
(13, 13, 1, '2026-04-24', '16:45:00', 'pendiente', 'pendiente', NULL, '2026-04-20 07:43:04', 30, '2026-04-20 07:43:04'),
(14, 14, 1, '2026-04-24', '18:15:00', 'cancelada', 'pendiente', '', '2026-04-20 07:43:51', 30, '2026-04-23 12:45:20'),
(15, 15, 1, '2026-04-25', '17:30:00', 'pendiente', 'pendiente', NULL, '2026-04-20 07:45:48', 30, '2026-04-20 07:45:48'),
(18, 16, 1, '2026-04-21', '12:00:00', 'completada', 'pendiente', '', '2026-04-20 14:44:06', 30, '2026-04-21 10:55:23'),
(19, 17, 1, '2026-04-22', '17:30:00', 'completada', 'pendiente', '', '2026-04-20 14:45:20', 30, '2026-04-22 17:56:27'),
(20, 18, 1, '2026-04-22', '16:30:00', 'completada', 'pendiente', '', '2026-04-20 17:10:06', 30, '2026-04-22 17:56:38'),
(30, 27, 1, '2026-04-23', '17:30:00', 'cancelada', 'pendiente', '', '2026-04-23 12:44:15', 30, '2026-04-23 13:01:40'),
(31, 14, 1, '2026-04-23', '18:30:00', 'pendiente', 'pendiente', NULL, '2026-04-23 12:44:27', 30, '2026-04-23 12:44:27'),
(32, 28, 1, '2026-04-23', '19:15:00', 'pendiente', 'pendiente', NULL, '2026-04-23 12:45:03', 30, '2026-04-23 12:45:03'),
(33, 27, 1, '2026-04-24', '17:30:00', 'pendiente', 'pendiente', NULL, '2026-04-23 13:02:10', 30, '2026-04-23 13:02:10'),
(34, 29, 1, '2026-04-25', '13:00:00', 'completada', 'pendiente', '', '2026-04-23 15:18:55', 30, '2026-04-25 11:11:50'),
(35, 30, 1, '2026-04-23', '18:00:00', 'pendiente', 'pendiente', NULL, '2026-04-23 17:38:56', 30, '2026-04-23 17:38:56'),
(36, 31, 1, '2026-04-24', '16:00:00', 'pendiente', 'pendiente', NULL, '2026-04-23 17:39:38', 30, '2026-04-23 17:39:38'),
(37, 32, 1, '2026-04-25', '12:15:00', 'completada', 'pendiente', '', '2026-04-23 17:40:37', 30, '2026-04-25 11:11:41'),
(38, 33, 1, '2026-04-24', '12:00:00', 'completada', 'pendiente', '', '2026-04-24 10:39:41', 30, '2026-04-24 10:40:05'),
(39, 34, 1, '2026-05-12', '13:00:00', 'pendiente', 'pendiente', NULL, '2026-05-11 20:07:56', 60, '2026-05-11 20:07:56'),
(40, 35, 1, '2026-05-13', '11:30:00', 'pendiente', 'pendiente', NULL, '2026-05-11 20:09:15', 30, '2026-05-11 20:09:15'),
(41, 36, 1, '2026-05-13', '12:15:00', 'pendiente', 'pendiente', NULL, '2026-05-11 20:12:38', 30, '2026-05-11 20:12:38'),
(42, 8, 1, '2026-05-13', '16:00:00', 'pendiente', 'pendiente', NULL, '2026-05-11 20:12:58', 30, '2026-05-11 20:12:58'),
(44, 5, 1, '2026-05-13', '19:00:00', 'pendiente', 'pendiente', NULL, '2026-05-11 20:14:14', 30, '2026-05-11 20:14:14'),
(45, 38, 1, '2026-05-13', '13:00:00', 'pendiente', 'pendiente', NULL, '2026-05-11 20:24:47', 60, '2026-05-11 20:24:47'),
(46, 39, 1, '2026-05-13', '16:45:00', 'pendiente', 'pendiente', NULL, '2026-05-11 20:26:20', 30, '2026-05-11 20:26:20'),
(47, 40, 1, '2026-05-13', '17:30:00', 'pendiente', 'pendiente', NULL, '2026-05-11 20:27:07', 30, '2026-05-11 20:27:07'),
(48, 41, 1, '2026-05-14', '09:00:00', 'pendiente', 'pendiente', NULL, '2026-05-11 20:28:14', 30, '2026-05-11 20:28:14'),
(49, 42, 1, '2026-05-14', '16:00:00', 'pendiente', 'pendiente', NULL, '2026-05-11 20:31:21', 30, '2026-05-11 20:31:21'),
(50, 43, 1, '2026-05-14', '16:45:00', 'pendiente', 'pendiente', NULL, '2026-05-11 20:31:59', 30, '2026-05-11 20:31:59'),
(51, 44, 1, '2026-05-14', '17:30:00', 'pendiente', 'pendiente', NULL, '2026-05-11 20:32:41', 30, '2026-05-11 20:32:41'),
(52, 45, 1, '2026-05-14', '18:15:00', 'pendiente', 'pendiente', NULL, '2026-05-11 20:33:24', 30, '2026-05-11 20:33:24'),
(53, 17, 1, '2026-05-14', '19:00:00', 'pendiente', 'pendiente', NULL, '2026-05-11 20:33:37', 30, '2026-05-11 20:33:37'),
(54, 7, 1, '2026-05-14', '19:45:00', 'pendiente', 'pendiente', NULL, '2026-05-11 20:33:49', 30, '2026-05-11 20:33:49'),
(55, 46, 1, '2026-05-15', '10:00:00', 'pendiente', 'pendiente', NULL, '2026-05-11 20:34:50', 30, '2026-05-11 20:34:50'),
(56, 30, 1, '2026-05-15', '10:45:00', 'pendiente', 'pendiente', NULL, '2026-05-11 20:35:00', 30, '2026-05-11 20:35:00'),
(57, 12, 1, '2026-05-15', '11:30:00', 'pendiente', 'pendiente', NULL, '2026-05-11 20:35:09', 30, '2026-05-11 20:35:09'),
(58, 47, 1, '2026-05-15', '12:15:00', 'pendiente', 'pendiente', NULL, '2026-05-11 20:35:41', 30, '2026-05-11 20:35:41'),
(59, 48, 1, '2026-05-15', '13:00:00', 'pendiente', 'pendiente', NULL, '2026-05-11 20:36:13', 30, '2026-05-11 20:36:13'),
(60, 49, 1, '2026-05-15', '16:00:00', 'pendiente', 'pendiente', NULL, '2026-05-11 20:36:50', 30, '2026-05-11 20:36:50'),
(61, 50, 1, '2026-05-15', '16:45:00', 'pendiente', 'pendiente', NULL, '2026-05-11 20:37:25', 30, '2026-05-11 20:37:25'),
(62, 51, 1, '2026-05-15', '17:30:00', 'pendiente', 'pendiente', NULL, '2026-05-11 20:37:56', 30, '2026-05-11 20:37:56'),
(63, 52, 1, '2026-05-15', '18:15:00', 'pendiente', 'pendiente', NULL, '2026-05-11 20:38:27', 30, '2026-05-11 20:38:27'),
(64, 13, 1, '2026-05-15', '19:00:00', 'pendiente', 'pendiente', NULL, '2026-05-11 20:38:46', 30, '2026-05-11 20:38:46'),
(65, 53, 1, '2026-05-16', '10:00:00', 'pendiente', 'pendiente', NULL, '2026-05-11 20:39:57', 30, '2026-05-11 20:39:57'),
(66, 32, 1, '2026-05-16', '10:45:00', 'pendiente', 'pendiente', NULL, '2026-05-11 20:40:07', 30, '2026-05-11 20:40:07'),
(67, 54, 1, '2026-05-16', '11:30:00', 'pendiente', 'pendiente', NULL, '2026-05-11 20:40:46', 30, '2026-05-11 20:40:46'),
(68, 55, 1, '2026-05-16', '12:15:00', 'pendiente', 'pendiente', NULL, '2026-05-11 20:41:25', 30, '2026-05-11 20:41:25'),
(69, 56, 1, '2026-05-16', '13:00:00', 'pendiente', 'pendiente', NULL, '2026-05-11 20:41:56', 30, '2026-05-11 20:41:56'),
(70, 57, 1, '2026-05-16', '16:00:00', 'pendiente', 'pendiente', NULL, '2026-05-11 20:42:34', 30, '2026-05-11 20:42:34'),
(71, 58, 1, '2026-05-16', '16:45:00', 'pendiente', 'pendiente', NULL, '2026-05-11 20:43:10', 30, '2026-05-11 20:43:10'),
(72, 15, 1, '2026-05-16', '17:30:00', 'pendiente', 'pendiente', NULL, '2026-05-11 20:43:21', 30, '2026-05-11 20:43:21'),
(73, 59, 1, '2026-05-13', '18:15:00', 'pendiente', 'pendiente', NULL, '2026-05-12 07:38:11', 30, '2026-05-12 07:38:11');

--
-- Disparadores `cita`
--
DELIMITER $$
CREATE TRIGGER `cita_after_delete` AFTER DELETE ON `cita` FOR EACH ROW BEGIN

    INSERT INTO audit_log (
        tabla,
        accion,
        registro_id,
        usuario,
        datos_antes
    )
    VALUES (
        'cita',
        'DELETE',
        OLD.id_cita,
        COALESCE(@usuario_actual, 'system'),

        JSON_OBJECT(
            'id_cita', OLD.id_cita,
            'id_cliente', OLD.id_cliente,
            'id_barbero', OLD.id_barbero,
            'fecha', OLD.fecha,
            'hora', OLD.hora,
            'estado', OLD.estado,
            'estado_pago', OLD.estado_pago,
            'duracion_total', OLD.duracion_total
        )
    );

END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `cita_after_insert` AFTER INSERT ON `cita` FOR EACH ROW BEGIN

    INSERT INTO audit_log (
        tabla,
        accion,
        registro_id,
        usuario,
        datos_despues
    )
    VALUES (
        'cita',
        'INSERT',
        NEW.id_cita,
        COALESCE(@usuario_actual, 'system'),
        JSON_OBJECT(
            'id_cita', NEW.id_cita,
            'id_cliente', NEW.id_cliente,
            'id_barbero', NEW.id_barbero,
            'fecha', NEW.fecha,
            'hora', NEW.hora,
            'estado', NEW.estado,
            'estado_pago', NEW.estado_pago,
            'duracion_total', NEW.duracion_total
        )
    );

END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `cita_after_update` AFTER UPDATE ON `cita` FOR EACH ROW BEGIN

    INSERT INTO audit_log (
        tabla,
        accion,
        registro_id,
        usuario,
        datos_antes,
        datos_despues
    )
    VALUES (
        'cita',
        'UPDATE',
        NEW.id_cita,
        COALESCE(@usuario_actual, 'system'),
        JSON_OBJECT(
            'id_cita', OLD.id_cita,
            'id_cliente', OLD.id_cliente,
            'id_barbero', OLD.id_barbero,
            'fecha', OLD.fecha,
            'hora', OLD.hora,
            'estado', OLD.estado,
            'estado_pago', OLD.estado_pago,
            'duracion_total', OLD.duracion_total
        ),

        JSON_OBJECT(
            'id_cita', NEW.id_cita,
            'id_cliente', NEW.id_cliente,
            'id_barbero', NEW.id_barbero,
            'fecha', NEW.fecha,
            'hora', NEW.hora,
            'estado', NEW.estado,
            'estado_pago', NEW.estado_pago,
            'duracion_total', NEW.duracion_total
        )
    );

END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cliente`
--

CREATE TABLE `cliente` (
  `id_cliente` int NOT NULL,
  `google_id` varchar(100) DEFAULT NULL,
  `nombre` varchar(100) NOT NULL,
  `dni` char(9) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `prefijo_pais` varchar(6) DEFAULT '+34',
  `telefono` varchar(20) DEFAULT NULL,
  `observaciones` text,
  `fecha_registro` datetime DEFAULT CURRENT_TIMESTAMP,
  `id_usuario` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `cliente`
--

INSERT INTO `cliente` (`id_cliente`, `google_id`, `nombre`, `dni`, `email`, `prefijo_pais`, `telefono`, `observaciones`, `fecha_registro`, `id_usuario`) VALUES
(2, NULL, 'Titi', '41278749M', 'aaaaaaaa@gmail.com', '+34', NULL, NULL, '2026-04-20 06:32:42', NULL),
(3, NULL, 'Alberto Nene', '32697143K', 'aaaaaa@gmail.com', '+34', NULL, NULL, '2026-04-20 06:34:31', NULL),
(4, NULL, 'Alejandro párraga', '14805683P', 'aaaaaa@gmail.com', '+34', NULL, NULL, '2026-04-20 06:35:34', NULL),
(5, NULL, 'Faroles', '20733412Q', 'aaaaaaa@gmail.com', '+34', NULL, NULL, '2026-04-20 06:36:16', NULL),
(6, NULL, 'Cano', '11662457P', 'ahahahahah@gmail.com', '+34', NULL, NULL, '2026-04-20 07:34:17', NULL),
(7, NULL, 'Ilyas', '78168508H', 'ahahhjshs@gmail.com', '+34', NULL, NULL, '2026-04-20 07:35:06', NULL),
(8, NULL, 'Alejandro Manoli', '76806123S', 'aaa@gmail.com', '+34', NULL, NULL, '2026-04-20 07:35:52', NULL),
(9, NULL, 'Jose Antonio', '71675154R', 'aaalala@gmail.com', '+34', NULL, NULL, '2026-04-20 07:36:57', NULL),
(10, NULL, 'Hermano Jose Antonio', '14567762E', 'ahahahha@gmail.com', '+34', NULL, NULL, '2026-04-20 07:38:31', NULL),
(11, NULL, 'Juanjo (Lendo)', '24632214L', 'qhshjshsj@gmail.com', '+34', NULL, NULL, '2026-04-20 07:39:30', NULL),
(12, NULL, 'Saad', '14204297A', 'ahahahaj@gmail.com', '+34', NULL, NULL, '2026-04-20 07:41:23', NULL),
(13, NULL, 'Kaki', '63769318R', 'ahahhaha@gmail.com', '+34', NULL, NULL, '2026-04-20 07:42:18', NULL),
(14, NULL, 'Tito Santi', '22316508E', 'ahhaha@gmail.com', '+34', NULL, NULL, '2026-04-20 07:43:26', NULL),
(15, NULL, 'Kekos', '83698119S', 'qahhah@gmail.com', '+34', NULL, NULL, '2026-04-20 07:45:21', NULL),
(16, NULL, 'Baltasar', '76467221H', 'ahahahah@gmail.com', '+34', NULL, NULL, '2026-04-20 14:43:47', NULL),
(17, NULL, 'Antonio bebes', '61351985Z', 'ahahhaha@gmail.com', '+34', NULL, NULL, '2026-04-20 14:44:44', NULL),
(18, NULL, 'Ruben Manzano', '36817826R', 'hahahhah@gmail.com', '+34', NULL, NULL, '2026-04-20 17:08:55', NULL),
(19, NULL, 'Alfonso', '26625071H', 'nono@no.es', '+34', NULL, NULL, '2026-04-20 19:56:02', NULL),
(27, NULL, 'Paco suegro', '77149376Q', 'ahahahah@gmail.com', '+34', NULL, NULL, '2026-04-23 12:43:35', NULL),
(28, NULL, 'Jefe', '62302679A', 'shhshshs@gmail.com', '+34', NULL, NULL, '2026-04-23 12:44:47', NULL),
(29, NULL, 'Yhostin', '69147951S', 'qhahhah@gmail.com', '+34', NULL, NULL, '2026-04-23 15:18:34', NULL),
(30, NULL, 'Guerol', '26717384D', 'ashhah@gmail.com', '+34', NULL, NULL, '2026-04-23 17:38:24', NULL),
(31, NULL, 'Bili', '61413658R', 'ahahhaha@gmail.com', '+34', NULL, NULL, '2026-04-23 17:39:18', NULL),
(32, NULL, 'Alberto Guili', '30672665A', 'ahahahhaha@gmail.com', '+34', NULL, NULL, '2026-04-23 17:40:01', NULL),
(33, NULL, 'Abuelo', '64766387C', 'ahahah@gmail.com', '+34', NULL, NULL, '2026-04-24 10:39:10', NULL),
(34, NULL, 'Manuteros', '37560443V', 'ashhaha@gmail.com', '+34', NULL, NULL, '2026-05-11 20:07:30', NULL),
(35, NULL, 'Pedro escabias', '45997255S', 'xxzd@gmail.com', '+34', NULL, NULL, '2026-05-11 20:08:47', NULL),
(36, NULL, 'Alvaro compadre', '19472523X', 'shahha@gmail.com', '+34', NULL, NULL, '2026-05-11 20:12:21', NULL),
(37, NULL, 'David cobo', '68724262D', 'qhaha@gmail.com', '+34', NULL, NULL, '2026-05-11 20:13:24', NULL),
(38, NULL, 'Victor gofer', '46505562E', 'aajaj@gmail.com', '+34', NULL, NULL, '2026-05-11 20:24:18', NULL),
(39, NULL, 'Reda', '29359509D', 'aah@gmail.com', '+34', NULL, NULL, '2026-05-11 20:26:00', NULL),
(40, NULL, 'Sergio castro', '92370033D', 'ahahah@gmail.com', '+34', NULL, NULL, '2026-05-11 20:26:47', NULL),
(41, NULL, 'Kisko capullo', '16532287W', 'ahahah@gmail.com', '+34', NULL, NULL, '2026-05-11 20:27:59', NULL),
(42, NULL, 'Tomas', '47528753N', 'ahah@gmail.com', '+34', NULL, NULL, '2026-05-11 20:31:03', NULL),
(43, NULL, 'Sergio alumno', '76587686D', 'hh@gmail.com', '+34', NULL, NULL, '2026-05-11 20:31:43', NULL),
(44, NULL, 'Fernando alumno', '50401657D', 'ahahha@gmail.com', '+34', NULL, NULL, '2026-05-11 20:32:19', NULL),
(45, NULL, 'Josema', '33785392W', 'aab@gmail.com', '+34', NULL, NULL, '2026-05-11 20:33:04', NULL),
(46, NULL, 'Jose pelaburros', '13735970W', 'ahha@gmail.com', '+34', NULL, NULL, '2026-05-11 20:34:29', NULL),
(47, NULL, 'Juan escabias', '22983377Y', 'ahahha@gmail.com', '+34', NULL, NULL, '2026-05-11 20:35:26', NULL),
(48, NULL, 'Mani', '52324535H', 'ahha@gmail.com', '+34', NULL, NULL, '2026-05-11 20:35:55', NULL),
(49, NULL, 'Izan', '13899508X', 'ahah@gmail.com', '+34', NULL, NULL, '2026-05-11 20:36:32', NULL),
(50, NULL, 'Roger nene', '11345831T', 'ahhaha@gmail.com', '+34', NULL, NULL, '2026-05-11 20:37:10', NULL),
(51, NULL, 'Carbon', '31707855D', 'ahah@gmail.com', '+34', NULL, NULL, '2026-05-11 20:37:41', NULL),
(52, NULL, 'Guerre', '52687011Z', 'aaha@gmail.com', '+34', NULL, NULL, '2026-05-11 20:38:11', NULL),
(53, NULL, 'Dario pasaje', '60856842S', 'ahaha@gmail.com', '+34', NULL, NULL, '2026-05-11 20:39:42', NULL),
(54, NULL, 'Sergio tomas', '76049903N', 'a@gmail.com', '+34', NULL, NULL, '2026-05-11 20:40:27', NULL),
(55, NULL, 'Chemari', '88717660J', 'ahaha@gmail.com', '+34', NULL, NULL, '2026-05-11 20:41:05', NULL),
(56, NULL, 'Blas', '38307540M', 'ahahha@gmail.com', '+34', NULL, NULL, '2026-05-11 20:41:41', NULL),
(57, NULL, 'Fran rucho', '31912100Z', 'ahha@gmail.com', '+34', NULL, NULL, '2026-05-11 20:42:17', NULL),
(58, NULL, 'Julito', '51368965Y', 'ahaha@gmail.com', '+34', NULL, NULL, '2026-05-11 20:42:54', NULL),
(59, NULL, 'Pedro faroles', '77704657P', 'ahahha@gmail.com', '+34', NULL, NULL, '2026-05-12 07:37:48', NULL);

--
-- Disparadores `cliente`
--
DELIMITER $$
CREATE TRIGGER `cliente_after_delete` AFTER DELETE ON `cliente` FOR EACH ROW BEGIN

    INSERT INTO audit_log (
        tabla,
        accion,
        registro_id,
        usuario,
        datos_antes
    )
    VALUES (
        'cliente',
        'DELETE',
        OLD.id_cliente,
        COALESCE(@usuario_actual, 'system'),
        JSON_OBJECT(
            'nombre', OLD.nombre,
            'dni', OLD.dni,
            'email', OLD.email,
            'telefono', OLD.telefono
        )
    );
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `cliente_after_insert` AFTER INSERT ON `cliente` FOR EACH ROW BEGIN

    INSERT INTO audit_log (
        tabla,
        accion,
        registro_id,
        usuario,
        datos_despues
    )
    VALUES (
        'cliente',
        'INSERT',
        NEW.id_cliente,
        COALESCE(@usuario_actual, 'system'),
        JSON_OBJECT(
            'id_cliente', NEW.id_cliente,
            'nombre', NEW.nombre,
            'dni', NEW.dni,
            'email', NEW.email,
            'telefono', NEW.telefono
        )
    );
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `cliente_after_update` AFTER UPDATE ON `cliente` FOR EACH ROW BEGIN

    INSERT INTO audit_log (
        tabla,
        accion,
        registro_id,
        usuario,
        datos_antes,
        datos_despues
    )
    VALUES (
        'cliente',
        'UPDATE',
        NEW.id_cliente,
        COALESCE(@usuario_actual, 'system'),
        JSON_OBJECT(
            'nombre', OLD.nombre,
            'dni', OLD.dni,
            'email', OLD.email,
            'telefono', OLD.telefono
        ),
        JSON_OBJECT(
            'nombre', NEW.nombre,
            'dni', NEW.dni,
            'email', NEW.email,
            'telefono', NEW.telefono
        )
    );
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detalle_cita`
--

CREATE TABLE `detalle_cita` (
  `id_detalle` int NOT NULL,
  `id_cita` int NOT NULL,
  `id_servicio` int NOT NULL,
  `precio_aplicado` decimal(6,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Disparadores `detalle_cita`
--
DELIMITER $$
CREATE TRIGGER `detalle_cita_after_delete` AFTER DELETE ON `detalle_cita` FOR EACH ROW BEGIN

    INSERT INTO audit_log (
        tabla,
        accion,
        registro_id,
        usuario,
        datos_antes
    )
    VALUES (
        'detalle_cita',
        'DELETE',
        OLD.id_detalle,
        COALESCE(@usuario_actual, 'system'),
        JSON_OBJECT(
            'id_cita', OLD.id_cita,
            'id_servicio', OLD.id_servicio,
            'precio_aplicado', OLD.precio_aplicado
        )
    );
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `detalle_cita_after_insert` AFTER INSERT ON `detalle_cita` FOR EACH ROW BEGIN

    INSERT INTO audit_log (
        tabla,
        accion,
        registro_id,
        usuario,
        datos_despues
    )
    VALUES (
        'detalle_cita',
        'INSERT',
        NEW.id_detalle,
        COALESCE(@usuario_actual, 'system'),
        JSON_OBJECT(
            'id_detalle', NEW.id_detalle,
            'id_cita', NEW.id_cita,
            'id_servicio', NEW.id_servicio,
            'precio_aplicado', NEW.precio_aplicado
        )
    );
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `detalle_cita_after_update` AFTER UPDATE ON `detalle_cita` FOR EACH ROW BEGIN

    INSERT INTO audit_log (
        tabla,
        accion,
        registro_id,
        usuario,
        datos_antes,
        datos_despues
    )
    VALUES (
        'detalle_cita',
        'UPDATE',
        NEW.id_detalle,
        COALESCE(@usuario_actual, 'system'),
        JSON_OBJECT(
            'id_cita', OLD.id_cita,
            'id_servicio', OLD.id_servicio,
            'precio_aplicado', OLD.precio_aplicado
        ),
        JSON_OBJECT(
            'id_cita', NEW.id_cita,
            'id_servicio', NEW.id_servicio,
            'precio_aplicado', NEW.precio_aplicado
        )
    );
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `historico_cliente`
--

CREATE TABLE `historico_cliente` (
  `id_historico` int NOT NULL,
  `id_cliente` int NOT NULL,
  `id_cita` int DEFAULT NULL,
  `descripcion` text,
  `fecha` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Disparadores `historico_cliente`
--
DELIMITER $$
CREATE TRIGGER `historico_cliente_after_delete` AFTER DELETE ON `historico_cliente` FOR EACH ROW BEGIN

    INSERT INTO audit_log (
        tabla,
        accion,
        registro_id,
        usuario,
        datos_antes
    )
    VALUES (
        'historico_cliente',
        'DELETE',
        OLD.id_historico,
        COALESCE(@usuario_actual, 'system'),
        JSON_OBJECT(
            'id_historico', OLD.id_historico,
            'id_cliente', OLD.id_cliente,
            'id_cita', OLD.id_cita,
            'descripcion', OLD.descripcion,
            'fecha', OLD.fecha
        )
    );

END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `historico_cliente_after_insert` AFTER INSERT ON `historico_cliente` FOR EACH ROW BEGIN

    INSERT INTO audit_log (
        tabla,
        accion,
        registro_id,
        usuario,
        datos_despues
    )
    VALUES (
        'historico_cliente',
        'INSERT',
        NEW.id_historico,
        COALESCE(@usuario_actual, 'system'),
        JSON_OBJECT(
            'id_historico', NEW.id_historico,
            'id_cliente', NEW.id_cliente,
            'id_cita', NEW.id_cita,
            'descripcion', NEW.descripcion,
            'fecha', NEW.fecha
        )
    );

END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `historico_cliente_after_update` AFTER UPDATE ON `historico_cliente` FOR EACH ROW BEGIN

    INSERT INTO audit_log (
        tabla,
        accion,
        registro_id,
        usuario,
        datos_antes,
        datos_despues
    )
    VALUES (
        'historico_cliente',
        'UPDATE',
        NEW.id_historico,
        COALESCE(@usuario_actual, 'system'),
        JSON_OBJECT(
            'id_cliente', OLD.id_cliente,
            'id_cita', OLD.id_cita,
            'descripcion', OLD.descripcion,
            'fecha', OLD.fecha
        ),
        JSON_OBJECT(
            'id_cliente', NEW.id_cliente,
            'id_cita', NEW.id_cita,
            'descripcion', NEW.descripcion,
            'fecha', NEW.fecha
        )
    );

END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pago`
--

CREATE TABLE `pago` (
  `id_pago` int NOT NULL,
  `id_cita` int NOT NULL,
  `total` decimal(8,2) NOT NULL,
  `metodo` enum('efectivo','tarjeta','bizum') DEFAULT NULL,
  `fecha_pago` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Disparadores `pago`
--
DELIMITER $$
CREATE TRIGGER `pago_after_delete` AFTER DELETE ON `pago` FOR EACH ROW BEGIN

    INSERT INTO audit_log (
        tabla,
        accion,
        registro_id,
        usuario,
        datos_antes
    )
    VALUES (
        'pago',
        'DELETE',
        OLD.id_pago,
        COALESCE(@usuario_actual, 'system'),
        JSON_OBJECT(
            'id_cita', OLD.id_cita,
            'total', OLD.total,
            'metodo', OLD.metodo
        )
    );
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `pago_after_insert` AFTER INSERT ON `pago` FOR EACH ROW BEGIN

    INSERT INTO audit_log (
        tabla,
        accion,
        registro_id,
        usuario,
        datos_despues
    )
    VALUES (
        'pago',
        'INSERT',
        NEW.id_pago,
        COALESCE(@usuario_actual, 'system'),
        JSON_OBJECT(
            'id_pago', NEW.id_pago,
            'id_cita', NEW.id_cita,
            'total', NEW.total,
            'metodo', NEW.metodo
        )
    );
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `pago_after_update` AFTER UPDATE ON `pago` FOR EACH ROW BEGIN

    INSERT INTO audit_log (
        tabla,
        accion,
        registro_id,
        usuario,
        datos_antes,
        datos_despues
    )
    VALUES (
        'pago',
        'UPDATE',
        NEW.id_pago,
        COALESCE(@usuario_actual, 'system'),
        JSON_OBJECT(
            'total', OLD.total,
            'metodo', OLD.metodo
        ),
        JSON_OBJECT(
            'total', NEW.total,
            'metodo', NEW.metodo
        )
    );
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `servicio`
--

CREATE TABLE `servicio` (
  `id_servicio` int NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `precio` decimal(6,2) NOT NULL,
  `duracion` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Disparadores `servicio`
--
DELIMITER $$
CREATE TRIGGER `servicio_after_delete` AFTER DELETE ON `servicio` FOR EACH ROW BEGIN

    INSERT INTO audit_log (
        tabla,
        accion,
        registro_id,
        usuario,
        datos_antes
    )
    VALUES (
        'servicio',
        'DELETE',
        OLD.id_servicio,
        COALESCE(@usuario_actual, 'system'),
        JSON_OBJECT(
            'nombre', OLD.nombre,
            'precio', OLD.precio,
            'duracion', OLD.duracion
        )
    );
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `servicio_after_insert` AFTER INSERT ON `servicio` FOR EACH ROW BEGIN

    INSERT INTO audit_log (
        tabla,
        accion,
        registro_id,
        usuario,
        datos_despues
    )
    VALUES (
        'servicio',
        'INSERT',
        NEW.id_servicio,
        COALESCE(@usuario_actual, 'system'),
        JSON_OBJECT(
            'id_servicio', NEW.id_servicio,
            'nombre', NEW.nombre,
            'precio', NEW.precio,
            'duracion', NEW.duracion
        )
    );
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `servicio_after_update` AFTER UPDATE ON `servicio` FOR EACH ROW BEGIN

    INSERT INTO audit_log (
        tabla,
        accion,
        registro_id,
        usuario,
        datos_antes,
        datos_despues
    )
    VALUES (
        'servicio',
        'UPDATE',
        NEW.id_servicio,
        COALESCE(@usuario_actual, 'system'),
        JSON_OBJECT(
            'nombre', OLD.nombre,
            'precio', OLD.precio,
            'duracion', OLD.duracion
        ),
        JSON_OBJECT(
            'nombre', NEW.nombre,
            'precio', NEW.precio,
            'duracion', NEW.duracion
        )
    );
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuario`
--

CREATE TABLE `usuario` (
  `id_usuario` int NOT NULL,
  `username` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `rol` enum('admin','barbero','cliente') NOT NULL,
  `activo` tinyint(1) DEFAULT '1',
  `fecha_creacion` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `usuario`
--

INSERT INTO `usuario` (`id_usuario`, `username`, `password`, `rol`, `activo`, `fecha_creacion`) VALUES
(1, 'aaleexrp', '$2y$12$N7zsey4UTACuHCm478bqdOhx5O1pGv5lWyAUyCEgEzTdZmq4lbf8m', 'barbero', 1, '2026-04-15 10:43:26'),
(2, 'alfonsogm', '$2y$12$4Z1Nl4Gg9DItlwO8jG64UOy1quhaOO2Wx6UMencVXKLA9rioj32J6', 'admin', 1, '2026-04-15 10:43:26');

--
-- Disparadores `usuario`
--
DELIMITER $$
CREATE TRIGGER `usuario_after_delete` AFTER DELETE ON `usuario` FOR EACH ROW BEGIN

    INSERT INTO audit_log (
        tabla,
        accion,
        registro_id,
        usuario,
        datos_antes
    )
    VALUES (
        'usuario',
        'DELETE',
        OLD.id_usuario,
        COALESCE(@usuario_actual, 'system'),
        JSON_OBJECT(
            'username', OLD.username,
            'rol', OLD.rol,
            'activo', OLD.activo
        )
    );
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `usuario_after_insert` AFTER INSERT ON `usuario` FOR EACH ROW BEGIN

    INSERT INTO audit_log (
        tabla,
        accion,
        registro_id,
        usuario,
        datos_despues
    )
    VALUES (
        'usuario',
        'INSERT',
        NEW.id_usuario,
        COALESCE(@usuario_actual, 'system'),
        JSON_OBJECT(
            'id_usuario', NEW.id_usuario,
            'username', NEW.username,
            'rol', NEW.rol,
            'activo', NEW.activo
        )
    );
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `usuario_after_update` AFTER UPDATE ON `usuario` FOR EACH ROW BEGIN

    INSERT INTO audit_log (
        tabla,
        accion,
        registro_id,
        usuario,
        datos_antes,
        datos_despues
    )
    VALUES (
        'usuario',
        'UPDATE',
        NEW.id_usuario,
        COALESCE(@usuario_actual, 'system'),
        JSON_OBJECT(
            'username', OLD.username,
            'password', OLD.password,
            'rol', OLD.rol,
            'activo', OLD.activo
        ),
        JSON_OBJECT(
            'username', NEW.username,
            'password', NEW.password,
            'rol', NEW.rol,
            'activo', NEW.activo
        )
    );
END
$$
DELIMITER ;

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `activacion_usuario`
--
ALTER TABLE `activacion_usuario`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_cliente` (`id_cliente`);

--
-- Indices de la tabla `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`id_admin`),
  ADD UNIQUE KEY `id_usuario` (`id_usuario`);

--
-- Indices de la tabla `audit_log`
--
ALTER TABLE `audit_log`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `barbero`
--
ALTER TABLE `barbero`
  ADD PRIMARY KEY (`id_barbero`),
  ADD UNIQUE KEY `id_usuario` (`id_usuario`);

--
-- Indices de la tabla `cita`
--
ALTER TABLE `cita`
  ADD PRIMARY KEY (`id_cita`),
  ADD UNIQUE KEY `id_barbero` (`id_barbero`,`fecha`,`hora`),
  ADD KEY `idx_cita_fecha` (`fecha`),
  ADD KEY `idx_cita_cliente` (`id_cliente`);

--
-- Indices de la tabla `cliente`
--
ALTER TABLE `cliente`
  ADD PRIMARY KEY (`id_cliente`),
  ADD UNIQUE KEY `dni` (`dni`),
  ADD UNIQUE KEY `google_id` (`google_id`),
  ADD UNIQUE KEY `id_usuario` (`id_usuario`),
  ADD KEY `idx_cliente_google` (`google_id`);

--
-- Indices de la tabla `detalle_cita`
--
ALTER TABLE `detalle_cita`
  ADD PRIMARY KEY (`id_detalle`),
  ADD KEY `id_cita` (`id_cita`),
  ADD KEY `id_servicio` (`id_servicio`);

--
-- Indices de la tabla `historico_cliente`
--
ALTER TABLE `historico_cliente`
  ADD PRIMARY KEY (`id_historico`),
  ADD KEY `id_cliente` (`id_cliente`),
  ADD KEY `id_cita` (`id_cita`);

--
-- Indices de la tabla `pago`
--
ALTER TABLE `pago`
  ADD PRIMARY KEY (`id_pago`),
  ADD KEY `id_cita` (`id_cita`);

--
-- Indices de la tabla `servicio`
--
ALTER TABLE `servicio`
  ADD PRIMARY KEY (`id_servicio`);

--
-- Indices de la tabla `usuario`
--
ALTER TABLE `usuario`
  ADD PRIMARY KEY (`id_usuario`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `activacion_usuario`
--
ALTER TABLE `activacion_usuario`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `admin`
--
ALTER TABLE `admin`
  MODIFY `id_admin` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `audit_log`
--
ALTER TABLE `audit_log`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=128;

--
-- AUTO_INCREMENT de la tabla `barbero`
--
ALTER TABLE `barbero`
  MODIFY `id_barbero` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `cita`
--
ALTER TABLE `cita`
  MODIFY `id_cita` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=74;

--
-- AUTO_INCREMENT de la tabla `cliente`
--
ALTER TABLE `cliente`
  MODIFY `id_cliente` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=60;

--
-- AUTO_INCREMENT de la tabla `detalle_cita`
--
ALTER TABLE `detalle_cita`
  MODIFY `id_detalle` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `historico_cliente`
--
ALTER TABLE `historico_cliente`
  MODIFY `id_historico` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `pago`
--
ALTER TABLE `pago`
  MODIFY `id_pago` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `servicio`
--
ALTER TABLE `servicio`
  MODIFY `id_servicio` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `usuario`
--
ALTER TABLE `usuario`
  MODIFY `id_usuario` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `activacion_usuario`
--
ALTER TABLE `activacion_usuario`
  ADD CONSTRAINT `activacion_usuario_ibfk_1` FOREIGN KEY (`id_cliente`) REFERENCES `cliente` (`id_cliente`) ON DELETE CASCADE;

--
-- Filtros para la tabla `admin`
--
ALTER TABLE `admin`
  ADD CONSTRAINT `admin_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id_usuario`) ON DELETE CASCADE;

--
-- Filtros para la tabla `barbero`
--
ALTER TABLE `barbero`
  ADD CONSTRAINT `barbero_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id_usuario`) ON DELETE CASCADE;

--
-- Filtros para la tabla `cita`
--
ALTER TABLE `cita`
  ADD CONSTRAINT `cita_ibfk_1` FOREIGN KEY (`id_cliente`) REFERENCES `cliente` (`id_cliente`) ON DELETE CASCADE,
  ADD CONSTRAINT `cita_ibfk_2` FOREIGN KEY (`id_barbero`) REFERENCES `barbero` (`id_barbero`) ON DELETE SET NULL;

--
-- Filtros para la tabla `cliente`
--
ALTER TABLE `cliente`
  ADD CONSTRAINT `cliente_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id_usuario`) ON DELETE CASCADE;

--
-- Filtros para la tabla `detalle_cita`
--
ALTER TABLE `detalle_cita`
  ADD CONSTRAINT `detalle_cita_ibfk_1` FOREIGN KEY (`id_cita`) REFERENCES `cita` (`id_cita`) ON DELETE CASCADE,
  ADD CONSTRAINT `detalle_cita_ibfk_2` FOREIGN KEY (`id_servicio`) REFERENCES `servicio` (`id_servicio`);

--
-- Filtros para la tabla `historico_cliente`
--
ALTER TABLE `historico_cliente`
  ADD CONSTRAINT `historico_cliente_ibfk_1` FOREIGN KEY (`id_cliente`) REFERENCES `cliente` (`id_cliente`) ON DELETE CASCADE,
  ADD CONSTRAINT `historico_cliente_ibfk_2` FOREIGN KEY (`id_cita`) REFERENCES `cita` (`id_cita`) ON DELETE SET NULL;

--
-- Filtros para la tabla `pago`
--
ALTER TABLE `pago`
  ADD CONSTRAINT `pago_ibfk_1` FOREIGN KEY (`id_cita`) REFERENCES `cita` (`id_cita`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
