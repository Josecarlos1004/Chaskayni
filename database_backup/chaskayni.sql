-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: localhost
-- Tiempo de generación: 18-11-2026 a las 06:04:35
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
-- Base de datos: `chaskayni`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `alertas_heladas`
--

CREATE TABLE `alertas_heladas` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `creado_por` bigint(20) UNSIGNED NOT NULL,
  `comunidad_id` bigint(20) UNSIGNED DEFAULT NULL,
  `titulo` varchar(160) NOT NULL,
  `mensaje` text NOT NULL,
  `nivel` varchar(24) NOT NULL DEFAULT '''precaucion''',
  `temperatura_c` decimal(5,2) DEFAULT NULL,
  `nivel_riesgo` varchar(12) DEFAULT NULL,
  `fecha_inicio` timestamp NULL DEFAULT NULL,
  `fecha_fin` timestamp NULL DEFAULT NULL,
  `cerrado_en` timestamp NULL DEFAULT NULL,
  `estado` varchar(24) NOT NULL DEFAULT '''publicada''',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `reporte_riesgo_id` bigint(20) UNSIGNED DEFAULT NULL,
  `tipo` varchar(20) NOT NULL DEFAULT 'clima'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `cache`
--

INSERT INTO `cache` (`key`, `value`, `expiration`) VALUES
('chaskayni_cache_5c785c036466adea360111aa28563bfd556b5fba', 'i:1;', 1794976319),
('chaskayni_cache_5c785c036466adea360111aa28563bfd556b5fba:timer', 'i:1794976319;', 1794976319);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `comunidades`
--

CREATE TABLE `comunidades` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `nombre` varchar(120) NOT NULL,
  `distrito` varchar(120) DEFAULT NULL,
  `provincia` varchar(120) NOT NULL DEFAULT '''Cusco''',
  `altitud_msnm` smallint(5) UNSIGNED DEFAULT NULL,
  `descripcion` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `comunidades`
--

INSERT INTO `comunidades` (`id`, `nombre`, `distrito`, `provincia`, `altitud_msnm`, `descripcion`, `created_at`, `updated_at`, `activo`) VALUES
(1, 'Chinchero', NULL, '\'Cusco\'', NULL, NULL, '2026-11-18 09:25:35', '2026-11-18 09:25:35', 1),
(2, 'Maras', NULL, '\'Cusco\'', NULL, NULL, '2026-11-18 09:25:35', '2026-11-18 09:25:35', 1),
(3, 'Ollantaytambo', NULL, '\'Cusco\'', NULL, NULL, '2026-11-18 09:25:35', '2026-11-18 09:25:35', 1),
(4, 'Pisac', NULL, '\'Cusco\'', NULL, NULL, '2026-11-18 09:25:35', '2026-11-18 09:25:35', 1),
(5, 'Urubamba', NULL, '\'Cusco\'', NULL, NULL, '2026-11-18 09:25:35', '2026-11-18 09:25:35', 1),
(6, 'Ccorca', NULL, '\'Cusco\'', NULL, NULL, '2026-11-18 09:25:35', '2026-11-18 09:25:35', 1),
(7, 'Poroy', NULL, '\'Cusco\'', NULL, NULL, '2026-11-18 09:25:35', '2026-11-18 09:25:35', 1),
(8, 'Anta', NULL, '\'Cusco\'', NULL, NULL, '2026-11-18 09:25:36', '2026-11-18 09:25:36', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `consultas_contacto`
--

CREATE TABLE `consultas_contacto` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `nombre` varchar(120) NOT NULL,
  `correo` varchar(190) DEFAULT NULL,
  `telefono` varchar(30) DEFAULT NULL,
  `asunto` varchar(160) NOT NULL,
  `mensaje` text NOT NULL,
  `leido_en` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detalles_pedido`
--

CREATE TABLE `detalles_pedido` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `pedido_id` bigint(20) UNSIGNED NOT NULL,
  `producto_id` bigint(20) UNSIGNED NOT NULL,
  `cantidad` decimal(10,2) NOT NULL,
  `precio_unitario` decimal(10,2) NOT NULL,
  `subtotal` decimal(12,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) UNSIGNED NOT NULL,
  `reserved_at` int(10) UNSIGNED DEFAULT NULL,
  `available_at` int(10) UNSIGNED NOT NULL,
  `created_at` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `job_batches`
--

CREATE TABLE `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '0001_01_01_000000_create_users_table', 1),
(2, '0001_01_01_000001_create_cache_table', 1),
(3, '0001_01_01_000002_create_jobs_table', 1),
(4, '2026_10_22_000001_create_communities_and_extend_users', 1),
(5, '2026_10_22_000002_create_products_and_orders', 1),
(6, '2026_10_22_000003_create_weather_and_safety_tables', 1),
(7, '2026_10_28_000004_add_saved_basket_to_products_table', 1),
(8, '2026_10_29_000005_renombrar_cesta_salvada_en_productos', 1),
(9, '2026_10_29_000006_renombrar_tablas_del_sistema_al_espanol', 1),
(10, '2026_10_29_000007_traducir_columnas_del_sistema_al_espanol', 1),
(11, '2026_10_30_000008_agregar_altitud_y_datos_de_temperatura', 1),
(12, '2026_10_30_000009_agregar_coordenadas_a_refugios', 1),
(13, '2026_10_31_000010_agregar_estado_activo_a_usuarios', 1),
(14, '2026_11_09_000011_corregir_estado_predeterminado_de_reportes', 1),
(15, '2026_11_11_000012_agregar_estado_activo_comunidades', 1),
(16, '2026_11_16_000013_relacionar_alertas_con_reportes', 1),
(17, '2026_11_16_000014_agregar_tipo_a_reportes_y_alertas', 1),
(18, '2026_11_16_000015_crear_consultas_contacto', 1),
(19, '2026_11_17_000001_agregar_foto_evidencia_a_reportes_riesgo', 1),
(20, '2026_11_17_000016_agregar_datos_y_aprobacion_de_cuentas', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pedidos`
--

CREATE TABLE `pedidos` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `comprador_id` bigint(20) UNSIGNED NOT NULL,
  `estado` varchar(24) NOT NULL DEFAULT '''pendiente''',
  `total` decimal(12,2) NOT NULL DEFAULT 0.00,
  `notas` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `productos`
--

CREATE TABLE `productos` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `usuario_id` bigint(20) UNSIGNED NOT NULL,
  `comunidad_id` bigint(20) UNSIGNED DEFAULT NULL,
  `nombre` varchar(140) NOT NULL,
  `categoria` varchar(80) DEFAULT NULL,
  `descripcion` text DEFAULT NULL,
  `cantidad` decimal(10,2) NOT NULL DEFAULT 0.00,
  `unidad` varchar(24) NOT NULL DEFAULT '''kg''',
  `precio_unitario` decimal(10,2) NOT NULL,
  `imagen_url` varchar(2048) DEFAULT NULL,
  `estado` varchar(24) NOT NULL DEFAULT '''disponible''',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `cesta_salvada` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `productos`
--

INSERT INTO `productos` (`id`, `usuario_id`, `comunidad_id`, `nombre`, `categoria`, `descripcion`, `cantidad`, `unidad`, `precio_unitario`, `imagen_url`, `estado`, `created_at`, `updated_at`, `cesta_salvada`) VALUES
(1, 1, 5, 'papa', 'tuberculos', NULL, 20.00, 'saco', 400.00, 'productos/F62KvgZKWiPnL7KXOagwwR7tMBosTGmS155qPWPb.jpg', 'disponible', '2026-11-18 09:32:19', '2026-11-18 09:32:19', 0),
(2, 1, 5, 'zanahoria', 'verdura', 's', 90.00, 'kg', 5.00, 'productos/oyQUTtRozmpXkS1wZZ9hePyLnsFx8nAEoHAsvKVP.jpg', 'disponible', '2026-11-18 09:35:09', '2026-11-18 09:35:09', 0),
(3, 1, 5, 'cebolla', 'verdura', 's', 20.00, 'saco', 320.00, 'productos/9wC7mbhPHIjvGdRsOimLB2nAMQczcDsjx3sWW5tP.jpg', 'disponible', '2026-11-18 09:42:13', '2026-11-18 09:42:13', 0),
(4, 1, 5, 'leche de vaca', NULL, 's', 80.00, 'litro', 2.90, 'productos/UcGdGcDeWZBuvEgUMj7IJSwnMipVMzbl5uAtrf2Y.jpg', 'disponible', '2026-11-18 09:43:24', '2026-11-18 09:43:24', 0),
(5, 1, 5, 'ajo', NULL, NULL, 15.00, 'saco', 300.00, 'https://sacosymallasperu.com/sacos-para-ajo', 'disponible', '2026-11-18 09:58:57', '2026-11-18 09:58:57', 0),
(6, 1, 5, 'huevo', NULL, 'huevo', 60.00, 'kg', 13.00, 'productos/eDeV0di8TCmwORxGk0HvCF0fId9HItK21soHS4et.jpg', 'disponible', '2026-11-18 10:00:12', '2026-11-18 10:00:12', 0);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `refugios`
--

CREATE TABLE `refugios` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `comunidad_id` bigint(20) UNSIGNED DEFAULT NULL,
  `nombre` varchar(160) NOT NULL,
  `direccion` varchar(255) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `capacidad` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `latitud` decimal(10,7) DEFAULT NULL,
  `longitud` decimal(10,7) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `reportes_riesgo`
--

CREATE TABLE `reportes_riesgo` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `reportante_id` bigint(20) UNSIGNED NOT NULL,
  `comunidad_id` bigint(20) UNSIGNED DEFAULT NULL,
  `titulo` varchar(160) NOT NULL,
  `descripcion` text NOT NULL,
  `foto_evidencia` varchar(255) DEFAULT NULL,
  `latitud` decimal(10,7) DEFAULT NULL,
  `longitud` decimal(10,7) DEFAULT NULL,
  `estado` varchar(24) NOT NULL DEFAULT 'pendiente',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `tipo` varchar(20) NOT NULL DEFAULT 'riesgo'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `reportes_riesgo`
--

INSERT INTO `reportes_riesgo` (`id`, `reportante_id`, `comunidad_id`, `titulo`, `descripcion`, `foto_evidencia`, `latitud`, `longitud`, `estado`, `created_at`, `updated_at`, `tipo`) VALUES
(1, 1, 5, 'si', 's', 'evidencias-reportes/3rxvrdPsZh1GaTxvzTQXOVH2oXxo24zxK23t9WZO.jpg', -13.3190459, -72.1173048, 'pendiente', '2026-11-18 09:33:49', '2026-11-18 09:33:49', 'clima'),
(2, 1, 5, 'si', 'Urgente. Derrumbe via Cusco - Urubamba sector mirador de Taray.', 'evidencias-reportes/wKfc3eVwxr7pYF8lbZHZ4HXIBzGujqTfOYXW3Gqv.jpg', -13.3114218, -72.0244789, 'pendiente', '2026-11-18 10:02:13', '2026-11-18 10:02:13', 'clima');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
('GjbxQw8kcgwnMpqQvPtlohEmqrHAwCtqg9p1IdOu', 1, '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36', 'YTo1OntzOjY6Il90b2tlbiI7czo0MDoiVWxSRjFtOWw3SlU3VU91UllqYkJyN09Ha2xyWnJySXl3Zml5RXVSSSI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mjk6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9tZXJjYWRvIjtzOjU6InJvdXRlIjtzOjE0OiJtZXJjYWRvLmluZGljZSI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fXM6NTA6ImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjtpOjE7czoxNzoicGFzc3dvcmRfaGFzaF93ZWIiO3M6NjQ6IjQ0MzdmNWRhOWU0YzVhODZhMTYyMjQyNGE1ZTQ0MTFmMDZhNjA3NDkxNzlkMjhiZjgxMWY4YTcwYzNjNmNlZjkiO30=', 1794978172);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `rol` varchar(24) NOT NULL DEFAULT '''comprador''',
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `comunidad_id` bigint(20) UNSIGNED DEFAULT NULL,
  `dni` varchar(8) DEFAULT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `aprobado_en` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `email_verified_at`, `password`, `remember_token`, `created_at`, `updated_at`, `rol`, `activo`, `comunidad_id`, `dni`, `telefono`, `aprobado_en`) VALUES
(1, 'alejandro', '61057711@continental.edu.pe', NULL, '$2y$12$h9.WXqfo89cVX/.hcJJ/QusRcf2DNqcSF7d2/uRoluIy/rrVm4UEi', NULL, '2026-11-18 09:26:46', '2026-11-18 09:26:46', 'productor', 1, 5, '61057711', '916839799', '2026-11-19 04:30:34');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `alertas_heladas`
--
ALTER TABLE `alertas_heladas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `alertas_heladas_reporte_riesgo_id_unique` (`reporte_riesgo_id`),
  ADD KEY `frost_alerts_created_by_foreign` (`creado_por`),
  ADD KEY `frost_alerts_community_id_foreign` (`comunidad_id`),
  ADD KEY `frost_alerts_level_index` (`nivel`),
  ADD KEY `frost_alerts_status_index` (`estado`);

--
-- Indices de la tabla `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`);

--
-- Indices de la tabla `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`);

--
-- Indices de la tabla `comunidades`
--
ALTER TABLE `comunidades`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `communities_name_unique` (`nombre`),
  ADD KEY `comunidades_activo_index` (`activo`);

--
-- Indices de la tabla `consultas_contacto`
--
ALTER TABLE `consultas_contacto`
  ADD PRIMARY KEY (`id`),
  ADD KEY `consultas_contacto_leido_en_index` (`leido_en`);

--
-- Indices de la tabla `detalles_pedido`
--
ALTER TABLE `detalles_pedido`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_items_order_id_foreign` (`pedido_id`),
  ADD KEY `order_items_product_id_foreign` (`producto_id`);

--
-- Indices de la tabla `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indices de la tabla `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

--
-- Indices de la tabla `job_batches`
--
ALTER TABLE `job_batches`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indices de la tabla `pedidos`
--
ALTER TABLE `pedidos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `orders_buyer_id_foreign` (`comprador_id`),
  ADD KEY `orders_status_index` (`estado`);

--
-- Indices de la tabla `productos`
--
ALTER TABLE `productos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `products_user_id_foreign` (`usuario_id`),
  ADD KEY `products_community_id_foreign` (`comunidad_id`),
  ADD KEY `products_status_created_at_index` (`estado`,`created_at`),
  ADD KEY `products_category_index` (`categoria`),
  ADD KEY `products_status_index` (`estado`);

--
-- Indices de la tabla `refugios`
--
ALTER TABLE `refugios`
  ADD PRIMARY KEY (`id`),
  ADD KEY `shelters_community_id_foreign` (`comunidad_id`),
  ADD KEY `shelters_active_index` (`activo`);

--
-- Indices de la tabla `reportes_riesgo`
--
ALTER TABLE `reportes_riesgo`
  ADD PRIMARY KEY (`id`),
  ADD KEY `risk_reports_reporter_id_foreign` (`reportante_id`),
  ADD KEY `risk_reports_community_id_foreign` (`comunidad_id`),
  ADD KEY `risk_reports_status_index` (`estado`);

--
-- Indices de la tabla `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indices de la tabla `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`),
  ADD UNIQUE KEY `users_dni_unique` (`dni`),
  ADD KEY `users_community_id_foreign` (`comunidad_id`),
  ADD KEY `users_role_index` (`rol`),
  ADD KEY `users_activo_index` (`activo`),
  ADD KEY `users_aprobado_en_index` (`aprobado_en`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `alertas_heladas`
--
ALTER TABLE `alertas_heladas`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `comunidades`
--
ALTER TABLE `comunidades`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `consultas_contacto`
--
ALTER TABLE `consultas_contacto`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `detalles_pedido`
--
ALTER TABLE `detalles_pedido`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT de la tabla `pedidos`
--
ALTER TABLE `pedidos`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `productos`
--
ALTER TABLE `productos`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `refugios`
--
ALTER TABLE `refugios`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `reportes_riesgo`
--
ALTER TABLE `reportes_riesgo`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `alertas_heladas`
--
ALTER TABLE `alertas_heladas`
  ADD CONSTRAINT `alertas_heladas_reporte_riesgo_id_foreign` FOREIGN KEY (`reporte_riesgo_id`) REFERENCES `reportes_riesgo` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `frost_alerts_community_id_foreign` FOREIGN KEY (`comunidad_id`) REFERENCES `comunidades` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `frost_alerts_created_by_foreign` FOREIGN KEY (`creado_por`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `detalles_pedido`
--
ALTER TABLE `detalles_pedido`
  ADD CONSTRAINT `order_items_order_id_foreign` FOREIGN KEY (`pedido_id`) REFERENCES `pedidos` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_product_id_foreign` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`);

--
-- Filtros para la tabla `pedidos`
--
ALTER TABLE `pedidos`
  ADD CONSTRAINT `orders_buyer_id_foreign` FOREIGN KEY (`comprador_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `productos`
--
ALTER TABLE `productos`
  ADD CONSTRAINT `products_community_id_foreign` FOREIGN KEY (`comunidad_id`) REFERENCES `comunidades` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `products_user_id_foreign` FOREIGN KEY (`usuario_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `refugios`
--
ALTER TABLE `refugios`
  ADD CONSTRAINT `shelters_community_id_foreign` FOREIGN KEY (`comunidad_id`) REFERENCES `comunidades` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `reportes_riesgo`
--
ALTER TABLE `reportes_riesgo`
  ADD CONSTRAINT `risk_reports_community_id_foreign` FOREIGN KEY (`comunidad_id`) REFERENCES `comunidades` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `risk_reports_reporter_id_foreign` FOREIGN KEY (`reportante_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_community_id_foreign` FOREIGN KEY (`comunidad_id`) REFERENCES `comunidades` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
