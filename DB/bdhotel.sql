-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 19-11-2024 a las 02:29:09
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `bdhotel`
--

DELIMITER $$
--
-- Procedimientos
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `Asignar` (IN `ID_reserva` INT, IN `ID_hab` INT)   BEGIN
INSERT INTO Asignacion(ID_reserv_FK, ID_hab_FK)
VALUES (ID_reserva, ID_hab);
COMMIT;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `Disponibilidad` (IN `Busqueda_entrada` DATE, IN `Busqueda_salida` DATE, IN `Busqueda_adultos` INT, IN `Busqueda_ninos` INT)   BEGIN
	SELECT H.Num_hab AS 'N° Habitación', 
	       T.Tipo_hab AS 'Tipo', 
	       T.Precio_hab AS 'Precio', 
	       T.Capacidad_hab_a AS 'Adultos', 
	       T.Capacidad_hab_n AS 'Niños'
	FROM Habitacion H
	INNER JOIN Tipo_habitacion T ON H.ID_tipo_hab = T.ID_tipo
	LEFT JOIN Asignacion A ON H.ID_hab = A.ID_hab_FK
	LEFT JOIN Reservacion R ON A.ID_reserv_FK = R.ID_reserv
	WHERE H.ID_hab NOT IN (
		SELECT A2.ID_hab_FK
		FROM Asignacion A2
		JOIN Reservacion R2 ON A2.ID_reserv_FK = R2.ID_reserv
		WHERE 
			(R2.Estado = 'Pagada') AND 
			(
				(Busqueda_entrada BETWEEN R2.Fecha_ent AND R2.Fecha_sal)
				OR 
				(Busqueda_salida BETWEEN R2.Fecha_ent AND R2.Fecha_sal)
				OR 
				(R2.Fecha_ent BETWEEN Busqueda_entrada AND Busqueda_salida) 
			)
	)
	AND T.Capacidad_hab_a >= Busqueda_adultos
	AND T.Capacidad_hab_n >= Busqueda_ninos
	GROUP BY H.ID_hab;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `Habitaciones` (IN `Numero` INT, IN `Tipo` INT)   BEGIN
	INSERT INTO Habitacion(Num_hab, ID_tipo_hab)
	VALUES (Numero, Tipo);
	COMMIT;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `Insertar_cliente` (IN `Nombre` VARCHAR(50), IN `Apellido` VARCHAR(50), IN `Pais` VARCHAR(50), IN `Telefono` INT, IN `Email` VARCHAR(100))   BEGIN
	INSERT INTO Cliente
	(Nombre_cli, Apellido_cli, Pais_cli, Tel_cli, Email_cli)
	VALUES
	(Nombre, Apellido, Pais, Telefono, Email);
	
	SELECT LAST_INSERT_ID() AS id_cliente;
COMMIT;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `Insertar_hab` (IN `Tipo` VARCHAR(50), IN `Precio` DECIMAL(10,2), IN `Capacidad_a` INT, IN `Capacidad_n` INT, IN `Descrip` VARCHAR(150))   BEGIN
    INSERT INTO Tipo_habitacion
    (Tipo_hab, Precio_hab, Capacidad_hab_a, Capacidad_hab_n, Descripcion)
    VALUES (Tipo, Precio, Capacidad_a, Capacidad_n, Descrip);
    COMMIT;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `Reservar` (IN `Num_reserva` INT, IN `Fecha_entrada` DATE, IN `Fecha_salida` DATE, IN `Estado` VARCHAR(50), IN `Num_adult` INT, IN `Num_ninos` INT, IN `Precio` DECIMAL(10,2), IN `ID_cliente` INT)   BEGIN
    -- Insertar en la tabla Reservacion
    INSERT INTO Reservacion
    (Num_reserv, Fecha_ent, Fecha_sal, Estado, Num_adult, Num_ninos, Precio, ID_cliente_FK)
    VALUES
    (Num_reserva, Fecha_entrada, Fecha_salida, Estado, Num_adult, Num_ninos, Precio, ID_cliente);
    SELECT LAST_INSERT_ID() AS ID_reserva;
    COMMIT;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `Tarjeta` (IN `Titular` VARCHAR(100), IN `Numero_tar` VARCHAR(20), IN `Mes` INT, IN `Ano` INT, IN `ID_reserva` INT)   BEGIN
	INSERT INTO Pago (Titular_tar, Num_tar, Mes_ven, Ano_ven, ID_reserva_FK)
	VALUES (Titular, Numero_tar, Mes, Ano, ID_reserva);
	COMMIT;
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `asignacion`
--

CREATE TABLE `asignacion` (
  `ID_asig_PK` int(11) NOT NULL,
  `ID_reserv_FK` int(11) NOT NULL,
  `ID_hab_FK` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `asignacion`
--

INSERT INTO `asignacion` (`ID_asig_PK`, `ID_reserv_FK`, `ID_hab_FK`) VALUES
(37, 54, 15),
(38, 55, 15),
(39, 56, 15),
(40, 57, 1),
(41, 58, 6),
(42, 59, 6),
(43, 60, 11),
(44, 61, 11),
(45, 62, 11),
(46, 63, 16),
(47, 64, 16),
(48, 65, 15),
(49, 66, 17),
(50, 67, 7),
(51, 68, 12);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cliente`
--

CREATE TABLE `cliente` (
  `ID_Cliente` int(11) NOT NULL,
  `Nombre_cli` varchar(50) NOT NULL,
  `Apellido_cli` varchar(50) NOT NULL,
  `Pais_cli` varchar(50) NOT NULL,
  `Tel_cli` varchar(20) NOT NULL,
  `Email_cli` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `cliente`
--

INSERT INTO `cliente` (`ID_Cliente`, `Nombre_cli`, `Apellido_cli`, `Pais_cli`, `Tel_cli`, `Email_cli`) VALUES
(92, 'Martin', 'Romero', 'PAN', '64742775', 'sheens0graviti@gmail.com'),
(93, 'Martin', 'Romero', 'PAN', '64742775', 'sheens0graviti@gmail.com'),
(94, 'Martin', 'Romero', 'PAN', '64742775', 'sheens0graviti@gmail.com'),
(95, 'Martin', 'Romero', 'PAN', '64742775', 'sheens0graviti@gmail.com'),
(96, 'Martin', 'Romero', 'PAN', '64742775', 'sheens0graviti@gmail.com'),
(97, 'Martin', 'Romero', 'PAN', '64742775', 'sheens0graviti@gmail.com'),
(98, 'Martin', 'Romero', 'PAN', '64742775', 'sheens0graviti@gmail.com'),
(99, 'Martin', 'Romero', 'PAN', '64742775', 'sheens0graviti@gmail.com'),
(100, 'Martin', 'Romero', 'PAN', '64742775', 'sheens0graviti@gmail.com'),
(101, 'Martin', 'Romero', 'PAN', '64742775', 'sheens0graviti@gmail.com'),
(102, 'Martin', 'Romero', 'PAN', '64742775', 'sheens0graviti@gmail.com'),
(103, 'Martin', 'Romero', 'PAN', '64742775', 'sheens0graviti@gmail.com'),
(104, 'Martin', 'Romero', 'PAN', '64742775', 'sheens0graviti@gmail.com'),
(105, 'Martin', 'Romero', 'PAN', '64742775', 'sheens0graviti@gmail.com'),
(106, 'Martin', 'Romero', 'PAN', '64742775', 'sheens0graviti@gmail.com');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `habitacion`
--

CREATE TABLE `habitacion` (
  `ID_hab` int(11) NOT NULL,
  `Num_hab` int(11) NOT NULL,
  `ID_tipo_hab` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `habitacion`
--

INSERT INTO `habitacion` (`ID_hab`, `Num_hab`, `ID_tipo_hab`) VALUES
(1, 1, 1),
(2, 2, 1),
(3, 3, 1),
(4, 4, 1),
(5, 5, 1),
(6, 6, 2),
(7, 7, 2),
(8, 8, 2),
(9, 9, 2),
(10, 10, 2),
(11, 11, 3),
(12, 12, 3),
(13, 13, 3),
(14, 14, 3),
(15, 15, 3),
(16, 16, 4),
(17, 17, 4),
(18, 18, 4),
(19, 19, 4),
(20, 20, 4);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pago`
--

CREATE TABLE `pago` (
  `ID_pago` int(11) NOT NULL,
  `Titular_tar` varchar(100) NOT NULL,
  `Num_tar` varchar(20) NOT NULL,
  `Mes_ven` int(11) NOT NULL,
  `Ano_ven` int(11) NOT NULL,
  `ID_reserva_FK` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `pago`
--

INSERT INTO `pago` (`ID_pago`, `Titular_tar`, `Num_tar`, `Mes_ven`, `Ano_ven`, `ID_reserva_FK`) VALUES
(39, 'Martin', '1957 3654 8451 6587', 12, 26, 54),
(40, 'Martin', '1957 3654 8451 6587', 12, 26, 55),
(41, 'Martin', '1957 3654 8451 6587', 12, 26, 56),
(42, 'MARTIN ROMERO ', '4187941122430731', 12, 26, 57),
(43, 'MARTIN ROMERO ', '4187941122430731', 12, 26, 58),
(44, 'YAPPY', '9999999999', 0, 0, 59),
(45, 'MARTIN ROMERO ', '4187941122430731', 12, 26, 60),
(46, 'MARTIN ROMERO ', '4187941122430731', 12, 26, 61),
(47, 'MARTIN ROMERO ', '4187941122430731', 12, 26, 62),
(48, 'MARTIN ROMERO ', '4187941122430731', 12, 26, 63),
(49, 'MARTIN ROMERO ', '4187941122430731', 12, 26, 64),
(50, 'Martin', '1957 3654 8451 6587', 12, 26, 65),
(51, 'MARTIN ROMERO ', '4187941122430731', 12, 26, 66),
(52, 'MARTIN ROMERO ', '4187941122430731', 12, 26, 67),
(53, 'MARTIN ROMERO ', '4187941122430731', 12, 26, 68);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `reservacion`
--

CREATE TABLE `reservacion` (
  `ID_reserv` int(11) NOT NULL,
  `Num_reserv` int(11) NOT NULL,
  `Fecha_ent` date NOT NULL,
  `Fecha_sal` date NOT NULL,
  `Estado` enum('Cancelado','Pagada') NOT NULL,
  `Num_adult` int(11) NOT NULL,
  `Num_ninos` int(11) NOT NULL,
  `Comentarios` varchar(100) DEFAULT NULL,
  `Precio` decimal(10,2) NOT NULL,
  `ID_cliente_FK` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `reservacion`
--

INSERT INTO `reservacion` (`ID_reserv`, `Num_reserv`, `Fecha_ent`, `Fecha_sal`, `Estado`, `Num_adult`, `Num_ninos`, `Comentarios`, `Precio`, `ID_cliente_FK`) VALUES
(54, 884774, '2025-04-07', '2025-04-08', 'Pagada', 3, 1, 'Forro anti-alergico', 111.00, 92),
(55, 521669, '2025-04-07', '2025-04-08', 'Pagada', 3, 1, 'Forro anti-alergico', 111.00, 93),
(56, 154824, '2025-04-07', '2025-04-08', 'Pagada', 3, 1, 'Forro anti-alergico', 111.00, 94),
(57, 338545, '2025-04-07', '2025-04-08', 'Pagada', 3, 1, '', 100.00, 95),
(58, 478504, '2025-04-06', '2025-04-08', 'Pagada', 3, 1, 'hola', 100.00, 96),
(59, 895500, '2025-04-06', '2025-04-08', 'Pagada', 3, 1, 'hola', 100.00, 97),
(60, 923420, '2025-04-07', '2025-04-08', 'Pagada', 3, 1, '', 111.00, 98),
(61, 826657, '2025-04-07', '2025-04-08', 'Pagada', 3, 1, '', 111.00, 99),
(62, 889544, '2025-04-07', '2025-04-08', 'Pagada', 3, 1, '', 111.00, 100),
(63, 417001, '2025-04-07', '2025-04-08', 'Pagada', 3, 1, '', 111.00, 101),
(64, 353386, '2025-04-07', '2025-04-08', 'Pagada', 3, 1, 'holaaas', 111.00, 102),
(65, 445942, '2025-04-07', '2025-04-08', 'Pagada', 3, 1, 'Forro anti-alergico', 111.00, 103),
(66, 808358, '2025-04-07', '2025-04-08', 'Pagada', 3, 1, '541654', 111.00, 104),
(67, 432806, '2025-04-07', '2025-04-08', 'Pagada', 3, 1, 'dsajkgf', 111.00, 105),
(68, 127233, '2025-04-07', '2025-04-08', 'Pagada', 3, 1, '123456789', 111.00, 106);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tipo_habitacion`
--

CREATE TABLE `tipo_habitacion` (
  `ID_tipo` int(11) NOT NULL,
  `Tipo_hab` varchar(50) NOT NULL,
  `Precio_hab` decimal(10,2) NOT NULL,
  `Capacidad_hab_a` int(11) NOT NULL,
  `Capacidad_hab_n` int(11) NOT NULL,
  `Descripcion` varchar(150) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `tipo_habitacion`
--

INSERT INTO `tipo_habitacion` (`ID_tipo`, `Tipo_hab`, `Precio_hab`, `Capacidad_hab_a`, `Capacidad_hab_n`, `Descripcion`) VALUES
(1, 'Vista al jardin', 104.00, 3, 1, 'Estas cómodas habitaciones con una cama king \r\no dos camas dobles están ubicadas cerca del lobby y cerca de la piscina con toboganes.'),
(2, 'Suite de lujo', 104.00, 4, 2, 'Estas cómodas habitaciones con una cama king o \r\ndos camas queen cuentan con un balcón privado con vista a la piscina de agua salada'),
(3, 'Vista la piscina', 111.00, 3, 2, 'Modernas habitaciones con una cama king o 2 \r\ncamas queen, disfrutan de vista a la alberca y toboganes gigantes'),
(4, 'Habitación Familiar', 158.00, 5, 3, 'Estas confortables habitaciones con 3 \r\ncamas matrimoniales están ubicadas cerca de restaurantes, Alberca y bares');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuario`
--

CREATE TABLE `usuario` (
  `id` int(11) NOT NULL,
  `usua_nombre` text NOT NULL,
  `usua_correo` varchar(50) NOT NULL,
  `usua_password` varchar(100) NOT NULL,
  `usua_roll` int(1) NOT NULL,
  `usua_estado` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuario`
--

INSERT INTO `usuario` (`id`, `usua_nombre`, `usua_correo`, `usua_password`, `usua_roll`, `usua_estado`) VALUES
(1, 'gabo', 'dasvdsa@dsavc.com', '149f00036617f2c10c9457af10540bf1', 2, 'Inactivo'),
(2, 'gavo', 'dasvdsa1@dsavc.com', '149f00036617f2c10c9457af10540bf1', 2, 'Activo'),
(3, 'gavo', 'dasvdsa1@dsavc.com', '149f00036617f2c10c9457af10540bf1', 2, 'Activo'),
(4, 'gavo', 'dasvdsa1@dsavc.com', '149f00036617f2c10c9457af10540bf1', 2, 'Activo'),
(6, 'CLIENTE', 'consulta@xxxx.com', '149f00036617f2c10c9457af10540bf1', 1, 'Activo');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios_token`
--

CREATE TABLE `usuarios_token` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `token` varchar(255) NOT NULL,
  `estado` varchar(50) NOT NULL,
  `fecha` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuarios_token`
--

INSERT INTO `usuarios_token` (`id`, `usuario_id`, `token`, `estado`, `fecha`) VALUES
(19, 2, '8a53af4e2762513bc7750067110a21c3', 'Inactivo', '2024-11-06 02:04:00'),
(20, 2, 'c5140554db454081584697c8306748fd', 'Inactivo', '2024-11-06 03:41:00'),
(21, 2, 'e9d7064366ad3e2484de4a4b9f57caaf', 'Inactivo', '2024-11-08 02:03:00'),
(22, 2, '32769d5df880fcb25ee0aeb693e67204', 'Inactivo', '2024-11-08 05:10:00'),
(23, 6, '4703c896c098cef0b8fef02c7e5fb8e8', 'Inactivo', '2024-11-08 05:13:00'),
(24, 6, '4ba5c54263f08a88985fd45279dd35ab', 'Inactivo', '2024-11-08 05:16:00'),
(25, 6, 'd0fcbfc3d2abf89fcda41a892a1cf3bf', 'Activo', '2024-11-08 05:19:00');

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `vista_reservaciones`
-- (Véase abajo para la vista actual)
--
CREATE TABLE `vista_reservaciones` (
`Nombre_del_cliente` varchar(50)
,`Apellido_del_cliente` varchar(50)
,`Numero_reservacion` int(11)
,`Estado` enum('Cancelado','Pagada')
,`Fecha_llegada` date
,`Fecha_salida` date
,`Tipo_habitacion` varchar(50)
,`Numero_habitacion` int(11)
);

-- --------------------------------------------------------

--
-- Estructura para la vista `vista_reservaciones`
--
DROP TABLE IF EXISTS `vista_reservaciones`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vista_reservaciones`  AS SELECT `c`.`Nombre_cli` AS `Nombre_del_cliente`, `c`.`Apellido_cli` AS `Apellido_del_cliente`, `r`.`Num_reserv` AS `Numero_reservacion`, `r`.`Estado` AS `Estado`, `r`.`Fecha_ent` AS `Fecha_llegada`, `r`.`Fecha_sal` AS `Fecha_salida`, `t`.`Tipo_hab` AS `Tipo_habitacion`, `h`.`Num_hab` AS `Numero_habitacion` FROM ((((`cliente` `c` join `reservacion` `r` on(`c`.`ID_Cliente` = `r`.`ID_cliente_FK`)) join `asignacion` on(`asignacion`.`ID_reserv_FK` = `r`.`ID_reserv`)) join `habitacion` `h` on(`h`.`ID_hab` = `asignacion`.`ID_hab_FK`)) join `tipo_habitacion` `t` on(`t`.`ID_tipo` = `h`.`ID_tipo_hab`)) ORDER BY `c`.`Nombre_cli` ASC ;

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `asignacion`
--
ALTER TABLE `asignacion`
  ADD PRIMARY KEY (`ID_asig_PK`),
  ADD KEY `ID_reserv_FK` (`ID_reserv_FK`),
  ADD KEY `ID_hab_FK` (`ID_hab_FK`);

--
-- Indices de la tabla `cliente`
--
ALTER TABLE `cliente`
  ADD PRIMARY KEY (`ID_Cliente`);

--
-- Indices de la tabla `habitacion`
--
ALTER TABLE `habitacion`
  ADD PRIMARY KEY (`ID_hab`),
  ADD KEY `ID_tipo_hab` (`ID_tipo_hab`);

--
-- Indices de la tabla `pago`
--
ALTER TABLE `pago`
  ADD PRIMARY KEY (`ID_pago`),
  ADD KEY `ID_reserva_FK` (`ID_reserva_FK`);

--
-- Indices de la tabla `reservacion`
--
ALTER TABLE `reservacion`
  ADD PRIMARY KEY (`ID_reserv`),
  ADD KEY `ID_cliente_FK` (`ID_cliente_FK`);

--
-- Indices de la tabla `tipo_habitacion`
--
ALTER TABLE `tipo_habitacion`
  ADD PRIMARY KEY (`ID_tipo`);

--
-- Indices de la tabla `usuario`
--
ALTER TABLE `usuario`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `usuarios_token`
--
ALTER TABLE `usuarios_token`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `asignacion`
--
ALTER TABLE `asignacion`
  MODIFY `ID_asig_PK` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=52;

--
-- AUTO_INCREMENT de la tabla `cliente`
--
ALTER TABLE `cliente`
  MODIFY `ID_Cliente` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=107;

--
-- AUTO_INCREMENT de la tabla `habitacion`
--
ALTER TABLE `habitacion`
  MODIFY `ID_hab` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT de la tabla `pago`
--
ALTER TABLE `pago`
  MODIFY `ID_pago` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=54;

--
-- AUTO_INCREMENT de la tabla `reservacion`
--
ALTER TABLE `reservacion`
  MODIFY `ID_reserv` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=69;

--
-- AUTO_INCREMENT de la tabla `tipo_habitacion`
--
ALTER TABLE `tipo_habitacion`
  MODIFY `ID_tipo` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `usuario`
--
ALTER TABLE `usuario`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `usuarios_token`
--
ALTER TABLE `usuarios_token`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `asignacion`
--
ALTER TABLE `asignacion`
  ADD CONSTRAINT `asignacion_ibfk_1` FOREIGN KEY (`ID_reserv_FK`) REFERENCES `reservacion` (`ID_reserv`),
  ADD CONSTRAINT `asignacion_ibfk_2` FOREIGN KEY (`ID_hab_FK`) REFERENCES `habitacion` (`ID_hab`);

--
-- Filtros para la tabla `habitacion`
--
ALTER TABLE `habitacion`
  ADD CONSTRAINT `habitacion_ibfk_1` FOREIGN KEY (`ID_tipo_hab`) REFERENCES `tipo_habitacion` (`ID_tipo`);

--
-- Filtros para la tabla `pago`
--
ALTER TABLE `pago`
  ADD CONSTRAINT `pago_ibfk_1` FOREIGN KEY (`ID_reserva_FK`) REFERENCES `reservacion` (`ID_reserv`);

--
-- Filtros para la tabla `reservacion`
--
ALTER TABLE `reservacion`
  ADD CONSTRAINT `reservacion_ibfk_1` FOREIGN KEY (`ID_cliente_FK`) REFERENCES `cliente` (`ID_Cliente`);

--
-- Filtros para la tabla `usuarios_token`
--
ALTER TABLE `usuarios_token`
  ADD CONSTRAINT `usuarios_token_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuario` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
