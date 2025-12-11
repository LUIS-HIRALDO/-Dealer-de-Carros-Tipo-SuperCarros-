-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 11-12-2025 a las 00:02:13
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
-- Base de datos: `supercar`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `categorias`
--

CREATE TABLE `categorias` (
  `id_categoria` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `descripcion` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `categorias`
--

INSERT INTO `categorias` (`id_categoria`, `nombre`, `descripcion`) VALUES
(1, 'Jeepeta', 'Vehiculo 4x4'),
(2, 'Camioneta Pickup', 'Robusta, ideal para trabajo pesado y terrenos difíciles.'),
(3, 'Todoterreno', 'Vehículo icónico para aventuras off-road.');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `estatus`
--

CREATE TABLE `estatus` (
  `id_estatus` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `estatus`
--

INSERT INTO `estatus` (`id_estatus`, `nombre`) VALUES
(1, 'Disponible'),
(2, 'Reservado'),
(3, 'Vendido'),
(4, 'En mantenimiento'),
(5, 'Apartado'),
(6, 'Dado de baja');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `marcas`
--

CREATE TABLE `marcas` (
  `id_marca` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `marcas`
--

INSERT INTO `marcas` (`id_marca`, `nombre`) VALUES
(1, 'Toyota'),
(2, 'Honda'),
(3, 'Hyundai'),
(4, 'Kia'),
(5, 'Nissan'),
(6, 'Ford');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `modelos`
--

CREATE TABLE `modelos` (
  `id_modelo` int(11) NOT NULL,
  `id_marca` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `modelos`
--

INSERT INTO `modelos` (`id_modelo`, `id_marca`, `nombre`) VALUES
(1, 1, 'Highlander'),
(2, 2, 'Civic 2023'),
(3, 6, 'Explorer 2023'),
(4, 5, 'Frontier 2022');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `reservas`
--

CREATE TABLE `reservas` (
  `id_reserva` int(11) NOT NULL,
  `id_vehiculo` int(11) NOT NULL,
  `nombre` varchar(150) NOT NULL,
  `email` varchar(150) NOT NULL,
  `telefono` varchar(50) DEFAULT NULL,
  `nota` text DEFAULT NULL,
  `ip` varchar(45) DEFAULT NULL,
  `estado` tinyint(1) NOT NULL DEFAULT 1,
  `fecha_reserva` datetime NOT NULL DEFAULT current_timestamp(),
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `reservas`
--

INSERT INTO `reservas` (`id_reserva`, `id_vehiculo`, `nombre`, `email`, `telefono`, `nota`, `ip`, `estado`, `fecha_reserva`, `creado_en`) VALUES
(1, 2, 'Juana', 'Juanaperalta2@gmail.com', '8298521718', 'Lo quiro !!', '::1', 1, '2025-12-10 23:44:46', '2025-12-10 22:44:46');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id_usuario` int(11) NOT NULL,
  `nombre` varchar(150) NOT NULL,
  `usuario` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `rol` enum('admin','vendedor','supervisor') DEFAULT 'vendedor',
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id_usuario`, `nombre`, `usuario`, `email`, `password`, `rol`, `fecha_creacion`) VALUES
(1, 'Luis', 'Alfredo', 'luishiraldo8@gmail.com', '$2y$10$3cV.3ezg8JxdfvkeycgefumXWNu5mOfEwlXkMbypjPXIrWgG.2TCO', 'admin', '2025-12-09 20:02:14'),
(4, 'admin', 'admin', 'admin@gmail.com', '$2y$10$VKOJTMxiOWpZWH4jsApCCemJ3jdv77XFvtiOCNKwiH76jNJ/kAybq', 'admin', '2025-12-10 22:54:23');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `vehiculos`
--

CREATE TABLE `vehiculos` (
  `id_vehiculo` int(11) NOT NULL,
  `id_categoria` int(11) NOT NULL,
  `id_marca` int(11) NOT NULL,
  `id_modelo` int(11) NOT NULL,
  `id_estatus` int(11) NOT NULL,
  `id_vendedor` int(11) NOT NULL,
  `año` int(11) DEFAULT NULL,
  `precio` decimal(12,2) DEFAULT NULL,
  `color` varchar(50) DEFAULT NULL,
  `vin` varchar(50) DEFAULT NULL,
  `descripcion` text DEFAULT NULL,
  `foto` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `vehiculos`
--

INSERT INTO `vehiculos` (`id_vehiculo`, `id_categoria`, `id_marca`, `id_modelo`, `id_estatus`, `id_vendedor`, `año`, `precio`, `color`, `vin`, `descripcion`, `foto`) VALUES
(2, 2, 5, 4, 2, 1, 2022, 20000.00, 'Azul', '', 'Tipo: SUV de tres filas\r\n\r\nMotor: 2.3L Turbo o 3.0L V6\r\n\r\nTransmisión: Automática\r\nEspaciosa, potente y orientada a familias grandes.', '/uploads/vehiculos/veh_69389541b432a5.45847126.jpg'),
(3, 3, 6, 3, 4, 1, 2023, 500000.00, 'Rojo', '', 'Tipo: SUV de 7 plazas (3 filas)\r\n\r\nMotores:\r\n\r\n2.3L Turbo 4 cilindros (300 hp)\r\n\r\n3.0L Turbo V6 (400 hp)\r\n\r\nHíbrido 3.3L V6 (318 hp)\r\n\r\nTransmisión: Automática de 10 velocidades\r\n\r\nConsumo: Hasta 28 mpg en carretera\r\n\r\nCapacidad de remolque: Hasta 5,600 libras', '/uploads/vehiculos/veh_6939f0d4853ae5.54933175.jpg'),
(4, 1, 1, 1, 1, 1, 2024, 2500000.00, 'Rojo', 'VIN-TEST-0001', 'Toyota Highlander 2024, excelente estado, motor 3.5L V6, paquete premium.', '/uploads/vehiculos/veh_693798ada00725.54618457.webp'),
(5, 1, 1, 1, 1, 1, 2023, 1850000.00, 'Blanco', 'VIN-TEST-0002', 'Toyota Highlander 2023, bajo kilometraje, interior en cuero.', '/uploads/vehiculos/veh_69389541b432a5.45847126.jpg'),
(6, 1, 1, 1, 1, 1, 2022, 1600000.00, 'Negro', 'VIN-TEST-0003', 'Uso familiar, mantenimiento al día, excelente rendimiento.', '/uploads/vehiculos/veh_6939f0d4853ae5.54933175.jpg'),
(7, 1, 1, 1, 1, 1, 2021, 1400000.00, 'Azul', 'VIN-TEST-0004', 'Vehículo de único dueño, historial limpio.', '/uploads/vehiculos/veh_693798ada00725.54618457.webp'),
(8, 1, 1, 1, 1, 1, 2020, 1200000.00, 'Gris', 'VIN-TEST-0005', 'Buen estado general, ideal para ciudad y viaje.', '/uploads/vehiculos/veh_69389541b432a5.45847126.jpg'),
(9, 1, 1, 1, 1, 1, 2019, 950000.00, 'Verde', 'VIN-TEST-0006', 'Ocasión, precio negociable, motor revisado.', '/uploads/vehiculos/veh_6939f0d4853ae5.54933175.jpg'),
(10, 1, 1, 1, 1, 1, 2018, 850000.00, 'Beige', 'VIN-TEST-0007', 'Kilometraje moderado, mantenimiento en taller autorizado.', '/uploads/vehiculos/veh_693798ada00725.54618457.webp'),
(11, 1, 1, 1, 1, 1, 2017, 720000.00, 'Plata', 'VIN-TEST-0008', 'Modelo confiable, revisión técnica al día.', '/uploads/vehiculos/veh_69389541b432a5.45847126.jpg'),
(12, 1, 1, 1, 1, 1, 2016, 650000.00, 'Naranja', 'VIN-TEST-0009', 'Buen estado mecánico, ideal para quien busca ahorrar.', '/uploads/vehiculos/veh_6939f0d4853ae5.54933175.jpg'),
(13, 1, 1, 1, 1, 1, 2015, 520000.00, 'Amarillo', 'VIN-TEST-0010', 'Vehículo económico, ideal para uso urbano.', '/uploads/vehiculos/veh_693798ada00725.54618457.webp');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `vendedor`
--

CREATE TABLE `vendedor` (
  `id_vendedor` int(11) NOT NULL,
  `nombre` varchar(150) NOT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `vendedor`
--

INSERT INTO `vendedor` (`id_vendedor`, `nombre`, `telefono`, `email`) VALUES
(1, 'Victor Isaac', '8097294917', 'ig.genaodeaza.j@gmail.com');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `categorias`
--
ALTER TABLE `categorias`
  ADD PRIMARY KEY (`id_categoria`);

--
-- Indices de la tabla `estatus`
--
ALTER TABLE `estatus`
  ADD PRIMARY KEY (`id_estatus`);

--
-- Indices de la tabla `marcas`
--
ALTER TABLE `marcas`
  ADD PRIMARY KEY (`id_marca`);

--
-- Indices de la tabla `modelos`
--
ALTER TABLE `modelos`
  ADD PRIMARY KEY (`id_modelo`),
  ADD KEY `id_marca` (`id_marca`);

--
-- Indices de la tabla `reservas`
--
ALTER TABLE `reservas`
  ADD PRIMARY KEY (`id_reserva`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id_usuario`),
  ADD UNIQUE KEY `usuario` (`usuario`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indices de la tabla `vehiculos`
--
ALTER TABLE `vehiculos`
  ADD PRIMARY KEY (`id_vehiculo`),
  ADD KEY `id_categoria` (`id_categoria`),
  ADD KEY `id_marca` (`id_marca`),
  ADD KEY `id_modelo` (`id_modelo`),
  ADD KEY `id_estatus` (`id_estatus`),
  ADD KEY `id_vendedor` (`id_vendedor`);

--
-- Indices de la tabla `vendedor`
--
ALTER TABLE `vendedor`
  ADD PRIMARY KEY (`id_vendedor`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `categorias`
--
ALTER TABLE `categorias`
  MODIFY `id_categoria` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `estatus`
--
ALTER TABLE `estatus`
  MODIFY `id_estatus` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `marcas`
--
ALTER TABLE `marcas`
  MODIFY `id_marca` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `modelos`
--
ALTER TABLE `modelos`
  MODIFY `id_modelo` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `reservas`
--
ALTER TABLE `reservas`
  MODIFY `id_reserva` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id_usuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `vehiculos`
--
ALTER TABLE `vehiculos`
  MODIFY `id_vehiculo` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT de la tabla `vendedor`
--
ALTER TABLE `vendedor`
  MODIFY `id_vendedor` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `modelos`
--
ALTER TABLE `modelos`
  ADD CONSTRAINT `modelos_ibfk_1` FOREIGN KEY (`id_marca`) REFERENCES `marcas` (`id_marca`);

--
-- Filtros para la tabla `vehiculos`
--
ALTER TABLE `vehiculos`
  ADD CONSTRAINT `vehiculos_ibfk_1` FOREIGN KEY (`id_categoria`) REFERENCES `categorias` (`id_categoria`),
  ADD CONSTRAINT `vehiculos_ibfk_2` FOREIGN KEY (`id_marca`) REFERENCES `marcas` (`id_marca`),
  ADD CONSTRAINT `vehiculos_ibfk_3` FOREIGN KEY (`id_modelo`) REFERENCES `modelos` (`id_modelo`),
  ADD CONSTRAINT `vehiculos_ibfk_4` FOREIGN KEY (`id_estatus`) REFERENCES `estatus` (`id_estatus`),
  ADD CONSTRAINT `vehiculos_ibfk_5` FOREIGN KEY (`id_vendedor`) REFERENCES `vendedor` (`id_vendedor`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
