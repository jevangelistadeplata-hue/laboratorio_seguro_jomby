-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 29-09-2026 a las 00:09:05
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
-- Base de datos: `laboratorio_seguro_jomby`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tareas`
--

CREATE TABLE `tareas` (
  `id` int(11) NOT NULL,
  `titulo` varchar(255) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `fecha` date NOT NULL,
  `prioridad` tinyint(4) NOT NULL,
  `categoria` enum('Trabajo','Estudio','Personal') NOT NULL,
  `completada` tinyint(1) NOT NULL DEFAULT 0,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `tareas`
--

INSERT INTO `tareas` (`id`, `titulo`, `descripcion`, `fecha`, `prioridad`, `categoria`, `completada`, `fecha_creacion`) VALUES
(1, 'Estudiar SQL Injection', 'Repasar la práctica 2 del módulo', '2026-10-01', 3, 'Estudio', 0, '2026-09-28 20:17:02'),
(2, 'Configurar WireGuard', 'Probar túnel completo y dividido', '2026-10-03', 2, 'Personal', 0, '2026-09-28 20:17:02'),
(3, 'Entregar informe Nmap', 'Incluir capturas de Zenmap', '2026-09-30', 3, 'Trabajo', 1, '2026-09-28 20:17:02'),
(4, 'Aprender Laravel', 'aprender laravel', '2026-09-28', 3, 'Personal', 0, '2026-09-28 20:40:56');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id`, `nombre`, `email`, `password`) VALUES
(2, 'JOSE ANGEL EVANGELISTA', 'jevangelista.caredom@gmail.com', '$2y$10$qxNZGbv6TrbOmGPdSJhWF.xSfuGrwbCOhiFM09gk4dXldwoN0Nqoa'),
(3, 'NARDA DESIREE', 'desireeguante@gmail.com', '$2y$10$5tu6xlRLa.Y1x11NkGMiIej8nnwOmdy4iQKNTQ1ltsfDVLDGlF/t2');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `tareas`
--
ALTER TABLE `tareas`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `tareas`
--
ALTER TABLE `tareas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
