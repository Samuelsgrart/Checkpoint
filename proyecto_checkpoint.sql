-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 12-04-2026 a las 17:07:14
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
-- Base de datos: `proyecto_checkpoint`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `favoritos`
--

CREATE TABLE `favoritos` (
  `id_favorito` int(11) NOT NULL,
  `id_usuario` int(11) DEFAULT NULL,
  `id_videojuego` int(11) NOT NULL,
  `nombre_videojuego` varchar(255) DEFAULT NULL,
  `id_usuario_ref` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `logros`
--

CREATE TABLE `logros` (
  `id_logro` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `clave_condicion` varchar(50) DEFAULT NULL,
  `valor_requerido` int(11) DEFAULT NULL,
  `imagen_url` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `logros`
--

INSERT INTO `logros` (`id_logro`, `nombre`, `descripcion`, `clave_condicion`, `valor_requerido`, `imagen_url`) VALUES
(1, 'Primer Checkpoint', 'Has publicado tu primera crítica.', 'total_criticas', 1, 'img/badges/bronze.png'),
(2, 'Crítico Experto', 'Más de 10 reseñas publicadas.', 'total_criticas', 10, 'img/badges/silver.png'),
(3, 'Coleccionista', 'Tienes 4 juegos en favoritos', 'total_favs', 4, 'img/badges/gold.png'),
(4, 'Escritor Novel', 'Has completado tu biografía de usuario.', 'bio_completa', 1, NULL),
(5, 'Mano Firme', 'Has publicado 5 críticas.', 'total_criticas', 5, NULL),
(6, 'El Gran Analista', 'Has publicado 25 críticas.', 'total_criticas', 25, NULL),
(7, 'Hater Profesional', 'Has calificado un juego con un 1.0 o menos.', 'nota_baja', 1, NULL),
(8, 'Fan Incondicional', 'Has calificado 3 juegos con la nota máxima.', 'multi_top', 3, NULL),
(9, 'Veterano de Guerra', 'Llevas más de un mes en la comunidad.', 'antiguedad', 30, NULL),
(10, 'Líder de Masas', 'Has conseguido tus primeros 5 seguidores.', 'total_seguidores', 5, 'img/badges/social_bronze.png'),
(11, 'Influencer', 'Tienes más de 20 seguidores.', 'total_seguidores', 20, 'img/badges/social_silver.png'),
(12, 'Ídolo de la Comunidad', 'Has alcanzado los 50 seguidores.', 'total_seguidores', 50, 'img/badges/social_gold.png'),
(13, 'Explorador de Perfiles', 'Sigues a 10 personas o más.', 'total_siguiendo', 10, NULL),
(14, 'Crítico Meticuloso', 'Has escrito una reseña de más de 200 caracteres.', 'longitud_critica', 200, NULL),
(15, 'Perfeccionista', 'Has editado tu perfil.', 'avatar_personalizado', 1, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `reseñas`
--

CREATE TABLE `reseñas` (
  `id_reseña` int(11) NOT NULL,
  `id_usuario` int(11) DEFAULT NULL,
  `id_videojuego` bigint(20) NOT NULL,
  `nombre_videojuego` varchar(255) DEFAULT NULL,
  `portada_url` varchar(255) DEFAULT NULL,
  `nota_igdb` decimal(3,1) DEFAULT 0.0,
  `comentario` text DEFAULT NULL,
  `nota_checkpoint` decimal(3,1) DEFAULT NULL,
  `id_usuario_ref` int(11) DEFAULT NULL,
  `favorito` tinyint(1) DEFAULT 0,
  `estado` enum('Jugando','Completado','Pendiente','Abandonado') DEFAULT 'Completado'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `reseñas`
--

INSERT INTO `reseñas` (`id_reseña`, `id_usuario`, `id_videojuego`, `nombre_videojuego`, `portada_url`, `nota_igdb`, `comentario`, `nota_checkpoint`, `id_usuario_ref`, `favorito`, `estado`) VALUES
(1, 1, 1020, 'Grand Theft Auto V', NULL, 96.0, 'Un clásico imbatible. El nivel de detalle de Los Santos sigue sorprendiendo hoy en día.', 4.5, NULL, 1, 'Completado'),
(2, 2, 1942, 'The Witcher 3: Wild Hunt', NULL, 92.0, 'Geralt es de mis personajes favoritos. Las misiones secundarias son mejores que la trama principal.', 5.0, NULL, 1, 'Completado'),
(3, 3, 72, 'Portal 2', NULL, 92.0, 'La curva de dificultad es perfecta. Te hace sentir inteligente sin llegar a frustrarte.', 4.5, NULL, 0, 'Completado'),
(4, 1, 472, 'Skyrim', NULL, 87.0, 'He perdido la cuenta de las veces que me lo he pasado. Los mods lo mantienen vivo siempre.', 4.0, NULL, 0, 'Completado'),
(5, 2, 1009, 'The Last of Us Part I', NULL, 89.0, 'Me ha hecho llorar como un niño. La relación entre Joel y Ellie es cine puro.', 5.0, NULL, 1, 'Completado'),
(6, 3, 119133, 'Elden Ring', NULL, 95.0, 'Hidetaka Miyazaki lo ha vuelto a hacer. El sentimiento de descubrimiento es constante.', 5.0, NULL, 1, 'Completado'),
(7, 1, 233, 'Metal Gear Solid 3', NULL, 88.0, 'Snake Eater es, para mí, el mejor de la saga. Ese final es historia de los videojuegos.', 5.0, NULL, 1, 'Completado'),
(8, 2, 1074, 'Super Mario Odyssey', NULL, 89.0, 'Colorido, divertido y técnicamente impecable. Nintendo en su máximo esplendor.', 4.5, NULL, 0, 'Completado'),
(9, 3, 115, 'League of Legends', NULL, 78.0, 'A veces es estresante por el chat, pero mecánicamente es el mejor MOBA.', 3.5, NULL, 0, 'Completado'),
(10, 2, 121, 'Minecraft', NULL, 85.0, 'Empecé una casa de madera y he acabado construyendo una ciudad entera. Adictivo.', 4.0, NULL, 0, 'Completado'),
(17, 1, 347668, 'Resident Evil Requiem', NULL, 90.0, 'Lo mejor', 5.0, NULL, 1, 'Completado'),
(22, 5, 325609, 'Dispatch', '//images.igdb.com/igdb/image/upload/t_thumb/cob5ql.jpg', 87.0, 'sadadd', 4.5, NULL, 1, 'Completado'),
(23, 5, 347668, 'Resident Evil Requiem', '//images.igdb.com/igdb/image/upload/t_thumb/cobmj0.jpg', 90.0, 'da miedo', 1.0, NULL, 0, 'Completado'),
(25, 6, 314265, 'Reanimal', '//images.igdb.com/igdb/image/upload/t_thumb/coaveu.jpg', 81.0, 'Muy bueno', 5.0, NULL, 1, 'Completado'),
(26, 6, 113360, 'Hytale', '//images.igdb.com/igdb/image/upload/t_thumb/cobc4t.jpg', 85.0, 'Basico', 1.5, NULL, 0, 'Completado'),
(28, 7, 314265, 'Reanimal', '//images.igdb.com/igdb/image/upload/t_thumb/coaveu.jpg', 81.0, 'Me encanta, loa doro', 5.0, NULL, 1, 'Completado'),
(29, 7, 113360, 'Hytale', '//images.igdb.com/igdb/image/upload/t_thumb/cobc4t.jpg', 85.0, 'Lo veo vacio', 1.0, NULL, 0, 'Completado'),
(35, 7, 288327, 'Pokémon Legends: Z-A', '//images.igdb.com/igdb/image/upload/t_thumb/co9wzc.jpg', 79.0, 'No me gustó.', 0.5, NULL, 0, 'Abandonado'),
(36, 8, 15536, 'Escape from Tarkov', '//images.igdb.com/igdb/image/upload/t_thumb/coaukr.jpg', 51.0, 'Es bueno', 3.0, NULL, 0, 'Jugando'),
(37, 8, 1026, 'The Legend of Zelda: A Link to the Past', '//images.igdb.com/igdb/image/upload/t_thumb/co3vzn.jpg', 96.0, 'Lo mejor', 5.0, NULL, 1, 'Completado'),
(38, 7, 27270, 'Payday 3', '//images.igdb.com/igdb/image/upload/t_thumb/co6m2i.jpg', 62.0, 'Le veo futuro', 3.0, NULL, 0, 'Jugando'),
(40, 7, 124, 'Left 4 Dead 2', '//images.igdb.com/igdb/image/upload/t_thumb/co1y2f.jpg', 85.0, 'Maestro', 5.0, NULL, 1, 'Completado'),
(41, 7, 72, 'Portal 2', '//images.igdb.com/igdb/image/upload/t_thumb/co1rs4.jpg', 92.0, 'EL mejor', 5.0, NULL, 1, 'Completado'),
(42, 7, 18866, 'Dead by Daylight', '//images.igdb.com/igdb/image/upload/t_thumb/co5zky.jpg', 69.0, 'Dead by Daylight es un juego multijugador asimétrico que logra capturar la tensión del cine de terror como pocos. Cuatro supervivientes deben cooperar para escapar mientras un asesino controla la partida, creando momentos intensos y llenos de adrenalina. Su jugabilidad es sencilla de entender pero difícil de dominar, especialmente por la variedad de habilidades y personajes disponibles. Además, destaca por sus colaboraciones con franquicias icónicas del horror, lo que añade valor y nossadsadasda', 4.0, NULL, 1, 'Jugando'),
(43, 7, 891, 'Team Fortress 2', '//images.igdb.com/igdb/image/upload/t_thumb/co6rzl.jpg', 81.0, 'Clasico', 4.0, NULL, 0, 'Jugando'),
(56, 7, 347668, 'Resident Evil Requiem', '//images.igdb.com/igdb/image/upload/t_thumb/cobmj0.jpg', 90.0, 'Con ganas', 5.0, NULL, 0, 'Pendiente'),
(57, 7, 7360, 'Rainbow Six Siege', '//images.igdb.com/igdb/image/upload/t_thumb/co9yqs.jpg', 76.0, 'INCREIBLE GOD', 5.0, NULL, 0, 'Jugando'),
(65, 7, 194143, 'The Seven Deadly Sins: Origin', '//images.igdb.com/igdb/image/upload/t_thumb/cobi46.jpg', 78.0, '', 0.0, NULL, 0, 'Pendiente'),
(66, 7, 125633, 'Crimson Desert', '//images.igdb.com/igdb/image/upload/t_thumb/coaqai.jpg', 83.0, 'Los puzles no me llaman', 3.5, NULL, 0, 'Jugando'),
(67, 7, 331202, 'Darwin\\\'s Paradox!', '//images.igdb.com/igdb/image/upload/t_thumb/co9e1h.jpg', 84.0, '', 0.0, NULL, 0, 'Pendiente'),
(69, 7, 279661, 'Monster Hunter Wilds', '//images.igdb.com/igdb/image/upload/t_thumb/co904o.jpg', 88.0, '', 0.0, NULL, 0, 'Pendiente'),
(71, 7, 395145, 'Exit: Echoes of Insanity', '//images.igdb.com/igdb/image/upload/t_thumb/cobs6s.jpg', 0.0, 'Lo mejor', 5.0, NULL, 0, 'Completado');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `seguidores`
--

CREATE TABLE `seguidores` (
  `id_seguimiento` int(11) NOT NULL,
  `id_seguidor` int(11) NOT NULL,
  `id_seguido` int(11) NOT NULL,
  `fecha_seguimiento` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `seguidores`
--

INSERT INTO `seguidores` (`id_seguimiento`, `id_seguidor`, `id_seguido`, `fecha_seguimiento`) VALUES
(2, 7, 3, '2026-03-19 18:54:14'),
(3, 8, 7, '2026-03-19 21:00:35'),
(4, 7, 1, '2026-03-20 16:14:16');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id_usuario` int(11) NOT NULL,
  `nombre` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `rol` varchar(20) DEFAULT 'usuario',
  `descripcion` varchar(255) DEFAULT '¡Bienvenido a mi perfil de Checkpoint!',
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp(),
  `avatar_url` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id_usuario`, `nombre`, `email`, `password`, `rol`, `descripcion`, `fecha_registro`, `avatar_url`) VALUES
(1, 'Samuel', 'samuel@test.com', '1234', 'usuario', '¡Bienvenido a mi perfil de Checkpoint!', '2026-03-16 12:12:46', NULL),
(2, 'Alex', 'alex@test.com', '1234', 'usuario', '¡Bienvenido a mi perfil de Checkpoint!', '2026-03-16 12:12:46', NULL),
(3, 'Lara', 'lara@test.com', '1234', 'usuario', '¡Bienvenido a mi perfil de Checkpoint!', '2026-03-16 12:12:46', NULL),
(7, 'Anacleto', 'prueba@prueba.com', '$2y$10$DP3Q3f3SiHj0t3CV0yGv5.3PmFGo/zqIF3M6zi66tkRlB4i3chHoW', 'usuario', 'HOLITAAAAA', '2026-03-16 12:27:46', 'img/avatares/avatar_7_1774206777.jpg'),
(8, 'Rosario', 'rosario@gmail.com', '$2y$10$NSypgvY9hQ35X9MVp65eLOZ7StEYfNJ5DBYr4JOOpuM04uQ9qGWq6', 'usuario', 'No soy muy gamer.', '2026-03-19 20:58:57', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios_logros`
--

CREATE TABLE `usuarios_logros` (
  `id_usuario` int(11) NOT NULL,
  `id_logro` int(11) NOT NULL,
  `fecha_desbloqueo` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuarios_logros`
--

INSERT INTO `usuarios_logros` (`id_usuario`, `id_logro`, `fecha_desbloqueo`) VALUES
(1, 1, '2026-03-16 12:17:07'),
(1, 4, '2026-03-16 12:17:07'),
(2, 1, '2026-03-16 12:17:07'),
(2, 4, '2026-03-16 12:17:07'),
(3, 1, '2026-03-16 12:17:07'),
(3, 4, '2026-03-16 12:17:07'),
(7, 1, '2026-03-16 12:32:15'),
(7, 2, '2026-03-20 19:15:38'),
(7, 3, '2026-03-20 15:38:34'),
(7, 4, '2026-03-16 12:32:15'),
(7, 5, '2026-03-20 15:35:08'),
(7, 7, '2026-03-16 12:32:15'),
(7, 8, '2026-03-20 15:37:22'),
(7, 14, '2026-03-20 16:28:14'),
(7, 15, '2026-03-22 19:49:30'),
(8, 1, '2026-03-19 20:59:40'),
(8, 4, '2026-03-19 20:59:40');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `favoritos`
--
ALTER TABLE `favoritos`
  ADD PRIMARY KEY (`id_favorito`),
  ADD KEY `id_usuario_ref` (`id_usuario_ref`);

--
-- Indices de la tabla `logros`
--
ALTER TABLE `logros`
  ADD PRIMARY KEY (`id_logro`);

--
-- Indices de la tabla `reseñas`
--
ALTER TABLE `reseñas`
  ADD PRIMARY KEY (`id_reseña`),
  ADD UNIQUE KEY `usuario_juego` (`id_usuario`,`id_videojuego`),
  ADD KEY `id_usuario_ref` (`id_usuario_ref`);

--
-- Indices de la tabla `seguidores`
--
ALTER TABLE `seguidores`
  ADD PRIMARY KEY (`id_seguimiento`),
  ADD KEY `id_seguidor` (`id_seguidor`),
  ADD KEY `id_seguido` (`id_seguido`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id_usuario`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indices de la tabla `usuarios_logros`
--
ALTER TABLE `usuarios_logros`
  ADD PRIMARY KEY (`id_usuario`,`id_logro`),
  ADD KEY `id_logro` (`id_logro`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `favoritos`
--
ALTER TABLE `favoritos`
  MODIFY `id_favorito` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `logros`
--
ALTER TABLE `logros`
  MODIFY `id_logro` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT de la tabla `reseñas`
--
ALTER TABLE `reseñas`
  MODIFY `id_reseña` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=72;

--
-- AUTO_INCREMENT de la tabla `seguidores`
--
ALTER TABLE `seguidores`
  MODIFY `id_seguimiento` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id_usuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `favoritos`
--
ALTER TABLE `favoritos`
  ADD CONSTRAINT `favoritos_ibfk_1` FOREIGN KEY (`id_usuario_ref`) REFERENCES `usuarios` (`id_usuario`) ON DELETE CASCADE;

--
-- Filtros para la tabla `seguidores`
--
ALTER TABLE `seguidores`
  ADD CONSTRAINT `seguidores_ibfk_1` FOREIGN KEY (`id_seguidor`) REFERENCES `usuarios` (`id_usuario`) ON DELETE CASCADE,
  ADD CONSTRAINT `seguidores_ibfk_2` FOREIGN KEY (`id_seguido`) REFERENCES `usuarios` (`id_usuario`) ON DELETE CASCADE;

--
-- Filtros para la tabla `usuarios_logros`
--
ALTER TABLE `usuarios_logros`
  ADD CONSTRAINT `usuarios_logros_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`),
  ADD CONSTRAINT `usuarios_logros_ibfk_2` FOREIGN KEY (`id_logro`) REFERENCES `logros` (`id_logro`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
