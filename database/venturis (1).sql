-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Sep 25, 2024 at 03:44 AM
-- Server version: 8.0.30
-- PHP Version: 8.2.22

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `venturis`
--

-- --------------------------------------------------------

--
-- Table structure for table `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `cache`
--

INSERT INTO `cache` (`key`, `value`, `expiration`) VALUES
('356a192b7913b04c54574d18c28d46e6395428ab', 'i:1;', 1727148368),
('356a192b7913b04c54574d18c28d46e6395428ab:timer', 'i:1727148368;', 1727148368),
('admin|127.0.0.1', 'i:1;', 1726366670),
('admin|127.0.0.1:timer', 'i:1726366670;', 1726366670),
('sueradmin|127.0.0.1', 'i:1;', 1726577381),
('sueradmin|127.0.0.1:timer', 'i:1726577381;', 1726577381),
('superadmin|127.0.0.1:timer', 'i:1726453471;', 1726453471);

-- --------------------------------------------------------

--
-- Table structure for table `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `empresas`
--

CREATE TABLE `empresas` (
  `id_empresa` bigint UNSIGNED NOT NULL,
  `empresa_ruc` varchar(11) COLLATE utf8mb4_unicode_ci NOT NULL,
  `id_ubigeo` bigint UNSIGNED NOT NULL,
  `empresa_razon_social` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `empresa_domicilio_fiscal` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `empresa_nombre_comercial` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `empresa_telefono_uno` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `empresa_telefono_dos` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `empresa_email_uno` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `empresa_email_dos` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `empresa_descricion` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `empresa_usuario` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `empresa_clave` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `empresa_archivo` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `empresa_clave_certificado` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `empresa_logo` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `empresa_estado_produccion` tinyint NOT NULL COMMENT '0 test, 1 produccion',
  `empresa_estado_boleta` tinyint NOT NULL COMMENT '1 Resumen Diario, 2 Envío Directo',
  `empresa_estado` tinyint NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `empresas`
--

INSERT INTO `empresas` (`id_empresa`, `empresa_ruc`, `id_ubigeo`, `empresa_razon_social`, `empresa_domicilio_fiscal`, `empresa_nombre_comercial`, `empresa_telefono_uno`, `empresa_telefono_dos`, `empresa_email_uno`, `empresa_email_dos`, `empresa_descricion`, `empresa_usuario`, `empresa_clave`, `empresa_archivo`, `empresa_clave_certificado`, `empresa_logo`, `empresa_estado_produccion`, `empresa_estado_boleta`, `empresa_estado`, `created_at`, `updated_at`) VALUES
(5, '10740779759', 1460, 'APAGUEÑO REYNA EDER ALFREDO', 'Calle las Americas 457', 'alfredev', '956449198', '956449198', 'reynaalfredo421@gmail.com', 'puntostyle@gmail.com', 'Desarrollo de software', NULL, NULL, NULL, NULL, NULL, 0, 1, 1, '2024-09-24 12:35:29', '2024-09-25 03:13:02');

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint UNSIGNED NOT NULL,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint UNSIGNED NOT NULL,
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` tinyint UNSIGNED NOT NULL,
  `reserved_at` int UNSIGNED DEFAULT NULL,
  `available_at` int UNSIGNED NOT NULL,
  `created_at` int UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `job_batches`
--

CREATE TABLE `job_batches` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_jobs` int NOT NULL,
  `pending_jobs` int NOT NULL,
  `failed_jobs` int NOT NULL,
  `failed_job_ids` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` mediumtext COLLATE utf8mb4_unicode_ci,
  `cancelled_at` int DEFAULT NULL,
  `created_at` int NOT NULL,
  `finished_at` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `menus`
--

CREATE TABLE `menus` (
  `id_menu` bigint UNSIGNED NOT NULL,
  `menu_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Nombre del menú',
  `menu_controller` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Nombre del controlador asociado al menú',
  `menu_icons` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Clase CSS del icono del menú',
  `menu_order` int NOT NULL COMMENT 'Posición de orden del menú en la interfaz',
  `menu_show` tinyint NOT NULL COMMENT 'Indica si el menú se muestra (1) o no se muestra (0)',
  `menu_status` tinyint NOT NULL COMMENT 'Estado del menú: 1 para activo, 0 para inactivo',
  `menu_microtime` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Marca de tiempo única para el menú',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `menus`
--

INSERT INTO `menus` (`id_menu`, `menu_name`, `menu_controller`, `menu_icons`, `menu_order`, `menu_show`, `menu_status`, `menu_microtime`, `created_at`, `updated_at`) VALUES
(26, 'Configuración', 'configuracion', 'fa-solid fa-gear', 1, 1, 1, '1725331940.4755', '2024-09-03 02:52:20', '2024-09-17 16:57:54');

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int UNSIGNED NOT NULL,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '0001_01_01_000000_create_users_table', 1),
(2, '0001_01_01_000001_create_cache_table', 1),
(3, '0001_01_01_000002_create_jobs_table', 1),
(4, '2024_08_26_121343_add_two_factor_columns_to_users_table', 1),
(5, '2024_08_26_122030_create_permission_tables', 2),
(7, '2024_08_30_081254_add_last_name_to_users', 3),
(8, '2024_08_30_140838_create_menus_table', 4),
(9, '2024_08_30_140847_create_submenus_table', 4),
(10, '2024_08_30_142411_add_id_menu_to_permissions', 5),
(11, '2024_09_01_220627_add_roles_status_to_roles', 6),
(12, '2024_09_03_083232_add_submenu_microtime_to_submenus', 7),
(13, '2024_09_10_213617_add_users_dni_to_users', 8),
(15, '2024_09_13_214804_create_notifications_table', 9),
(16, '2024_09_22_140422_create_ubigeos_table', 10),
(17, '2024_09_22_140504_create_empresas_table', 10);

-- --------------------------------------------------------

--
-- Table structure for table `model_has_permissions`
--

CREATE TABLE `model_has_permissions` (
  `permission_id` bigint UNSIGNED NOT NULL,
  `model_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` bigint UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `model_has_roles`
--

CREATE TABLE `model_has_roles` (
  `role_id` bigint UNSIGNED NOT NULL,
  `model_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` bigint UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `model_has_roles`
--

INSERT INTO `model_has_roles` (`role_id`, `model_type`, `model_id`) VALUES
(1, 'App\\Models\\User', 1),
(2, 'App\\Models\\User', 4);

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `notifiable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `notifiable_id` bigint UNSIGNED NOT NULL,
  `data` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `read_at` timestamp NULL DEFAULT NULL,
  `notification_status` tinyint NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `password_reset_tokens`
--

INSERT INTO `password_reset_tokens` (`email`, `token`, `created_at`) VALUES
('puntostyle05@gmail.com', '$2y$12$dujuFL6bATFCFD3qdy5O8.U83VJgRy5n7qUa987yn4hEOCCRb24MG', '2024-09-17 02:09:28');

-- --------------------------------------------------------

--
-- Table structure for table `permissions`
--

CREATE TABLE `permissions` (
  `id` bigint UNSIGNED NOT NULL,
  `id_menu` bigint UNSIGNED DEFAULT NULL,
  `id_submenu` bigint UNSIGNED DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `guard_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `permissions_group` tinyint NOT NULL COMMENT '1 Menu -  2 Submenu - 3 Functions',
  `permissions_group_id` bigint NOT NULL COMMENT 'ID DE MENU - SUBMENU - FUNCTION',
  `permission_status` tinyint NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `permissions`
--

INSERT INTO `permissions` (`id`, `id_menu`, `id_submenu`, `name`, `guard_name`, `permissions_group`, `permissions_group_id`, `permission_status`, `created_at`, `updated_at`) VALUES
(24, 26, NULL, 'configuracion', 'web', 1, 26, 1, '2024-09-03 02:52:20', '2024-09-07 04:21:59'),
(27, NULL, 2, 'menus', 'web', 2, 2, 1, '2024-09-03 13:50:25', '2024-09-03 13:53:56'),
(28, NULL, 3, 'submenu', 'web', 2, 3, 1, '2024-09-03 13:51:51', '2024-09-03 13:51:51'),
(29, NULL, 4, 'usuarios', 'web', 2, 4, 1, '2024-09-03 13:54:48', '2024-09-03 13:54:48'),
(30, NULL, 5, 'roles', 'web', 2, 5, 1, '2024-09-03 13:55:11', '2024-09-03 13:55:11'),
(31, NULL, 6, 'iconos', 'web', 2, 6, 1, '2024-09-03 13:56:03', '2024-09-09 03:19:33'),
(40, NULL, NULL, 'create_menus', 'web', 3, 2, 1, '2024-09-05 03:56:25', '2024-09-05 03:56:25'),
(42, NULL, NULL, 'update_menus', 'web', 3, 2, 1, '2024-09-05 04:07:16', '2024-09-05 04:07:16'),
(43, NULL, NULL, 'disable_menus', 'web', 3, 2, 1, '2024-09-05 04:07:36', '2024-09-05 04:07:36'),
(44, NULL, NULL, 'crear_submenu', 'web', 3, 3, 1, '2024-09-05 05:03:22', '2024-09-05 05:03:22'),
(45, NULL, NULL, 'update_submenu', 'web', 3, 3, 1, '2024-09-05 05:03:54', '2024-09-05 05:03:54'),
(46, NULL, NULL, 'disable_submenu', 'web', 3, 3, 1, '2024-09-05 05:04:05', '2024-09-05 05:04:05'),
(47, NULL, NULL, 'create_permissions_submenus', 'web', 3, 3, 1, '2024-09-05 05:04:51', '2024-09-05 05:04:51'),
(48, NULL, NULL, 'delete_permissions_submenus', 'web', 3, 3, 1, '2024-09-05 05:05:07', '2024-09-05 05:05:07'),
(49, NULL, NULL, 'create_users', 'web', 3, 4, 1, '2024-09-08 04:55:52', '2024-09-08 04:55:52'),
(50, NULL, NULL, 'update_users', 'web', 3, 4, 1, '2024-09-08 04:56:00', '2024-09-08 04:56:00'),
(51, NULL, NULL, 'disable_users', 'web', 3, 4, 1, '2024-09-08 04:58:40', '2024-09-08 04:58:40'),
(53, NULL, NULL, 'create_roles', 'web', 3, 5, 1, '2024-09-08 14:44:37', '2024-09-08 14:44:37'),
(54, NULL, NULL, 'update_roles', 'web', 3, 5, 1, '2024-09-08 14:44:46', '2024-09-08 14:44:46'),
(55, NULL, NULL, 'disable_roles', 'web', 3, 5, 1, '2024-09-08 14:45:05', '2024-09-08 14:45:05'),
(56, NULL, NULL, 'assign_permissions_to_role', 'web', 3, 5, 1, '2024-09-08 20:32:52', '2024-09-08 20:32:52'),
(57, NULL, 7, 'empresas', 'web', 2, 7, 1, '2024-09-18 13:31:28', '2024-09-18 13:31:28'),
(58, NULL, NULL, 'create_company', 'web', 3, 7, 1, '2024-09-23 03:17:30', '2024-09-23 03:17:30'),
(59, NULL, NULL, 'update_company', 'web', 3, 7, 1, '2024-09-23 03:17:39', '2024-09-23 03:17:39'),
(60, NULL, NULL, 'disable_company', 'web', 3, 7, 1, '2024-09-23 03:18:18', '2024-09-23 03:18:18');

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `guard_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `roles_status` tinyint NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `name`, `guard_name`, `roles_status`, `created_at`, `updated_at`) VALUES
(1, 'Superadmin', 'web', 1, '2024-09-02 03:08:24', '2024-09-12 04:44:53'),
(2, 'Administrador', 'web', 1, NULL, '2024-09-08 15:59:37'),
(3, 'Colaborador', 'web', 1, '2024-09-09 02:58:05', '2024-09-09 02:58:05');

-- --------------------------------------------------------

--
-- Table structure for table `role_has_permissions`
--

CREATE TABLE `role_has_permissions` (
  `permission_id` bigint UNSIGNED NOT NULL,
  `role_id` bigint UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `role_has_permissions`
--

INSERT INTO `role_has_permissions` (`permission_id`, `role_id`) VALUES
(24, 1),
(27, 1),
(28, 1),
(29, 1),
(30, 1),
(31, 1),
(40, 1),
(42, 1),
(43, 1),
(44, 1),
(45, 1),
(46, 1),
(47, 1),
(48, 1),
(49, 1),
(50, 1),
(51, 1),
(53, 1),
(54, 1),
(55, 1),
(56, 1),
(57, 1),
(58, 1),
(59, 1),
(60, 1);

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
('0WsMnJExNIBYYhrqqHIqyuYtrTpJY1KiiUnz43vX', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/128.0.0.0 Safari/537.36', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoicXRCVHJPMk1UNFZMMDFxSm9NcTRQYkRkYzhoTWc5SW9VT1FXYmdXSSI7czo1MDoibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6MTtzOjk6Il9wcmV2aW91cyI7YToxOntzOjM6InVybCI7czoyNToiaHR0cDovL3ZlbnRhZ2VuZXJhbC5sb2NhbCI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1726350602),
('BdkykeKNI8yMyzz16plMV0GiuzvevfiMkIJ3peQt', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/128.0.0.0 Safari/537.36', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiVG1kVkl6QlIxVU9rTUt1Z20xSkNiWDRjeEp4TFFTNENETlI3TUhPTCI7czo1MDoibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6MTtzOjk6Il9wcmV2aW91cyI7YToxOntzOjM6InVybCI7czo0NToiaHR0cDovL3ZlbnRhZ2VuZXJhbC5sb2NhbC9jb25maWd1cmFjaW9uL3JvbGVzIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1726972124),
('K789ZURV4PDxPimxRzZ3CMu93csPf20aMpPq16pW', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/128.0.0.0 Safari/537.36', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiZkdMMUlWMTlsYmNHb2ZJa1FCcWIyeHRETnRhSmRkRzVmNkRkcnA2dCI7czo1MDoibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6MTtzOjk6Il9wcmV2aW91cyI7YToxOntzOjM6InVybCI7czo1OToiaHR0cDovL3ZlbnRhZ2VuZXJhbC5sb2NhbC9jb25maWd1cmFjaW9uL3N1Ym1lbnU/ZGF0YT1NalklM0QiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19', 1725631449);

-- --------------------------------------------------------

--
-- Table structure for table `submenus`
--

CREATE TABLE `submenus` (
  `id_submenu` bigint UNSIGNED NOT NULL,
  `id_menu` bigint UNSIGNED NOT NULL COMMENT 'Clave foránea que refiere al ID del menú padre',
  `submenu_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Nombre del submenú',
  `submenu_function` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Función o ruta asociada al submenú',
  `submenu_order` int NOT NULL COMMENT 'Posición de orden del submenú en la interfaz',
  `submenu_show` tinyint NOT NULL COMMENT 'Indica si el submenú se muestra (1) o no se muestra (0)',
  `submenu_status` tinyint NOT NULL COMMENT 'Estado del submenú: 1 para activo, 0 para inactivo',
  `submenu_microtime` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `submenus`
--

INSERT INTO `submenus` (`id_submenu`, `id_menu`, `submenu_name`, `submenu_function`, `submenu_order`, `submenu_show`, `submenu_status`, `submenu_microtime`, `created_at`, `updated_at`) VALUES
(2, 26, 'Menús', 'menus', 1, 1, 1, '1725371425.5094', '2024-09-03 13:50:25', '2024-09-03 13:53:56'),
(3, 26, 'Submenús', 'submenu', 5, 0, 1, '1725371511.4044', '2024-09-03 13:51:51', '2024-09-08 13:51:35'),
(4, 26, 'Usuarios', 'usuarios', 2, 1, 1, '1725371688.6191', '2024-09-03 13:54:48', '2024-09-03 13:54:48'),
(5, 26, 'Roles', 'roles', 3, 1, 1, '1725371711.8208', '2024-09-03 13:55:11', '2024-09-03 13:58:04'),
(6, 26, 'Iconos', 'iconos', 4, 1, 1, '1725371763.1496', '2024-09-03 13:56:03', '2024-09-09 03:19:33'),
(7, 26, 'Empresas', 'empresas', 5, 1, 1, '1726666288.2756', '2024-09-18 13:31:28', '2024-09-18 13:31:28');

-- --------------------------------------------------------

--
-- Table structure for table `ubigeos`
--

CREATE TABLE `ubigeos` (
  `id_ubigeo` bigint UNSIGNED NOT NULL,
  `ubigeo_cod` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ubigeo_departamento` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ubigeo_provincia` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ubigeo_distrito` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ubigeo_capital` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `ubigeos`
--

INSERT INTO `ubigeos` (`id_ubigeo`, `ubigeo_cod`, `ubigeo_departamento`, `ubigeo_provincia`, `ubigeo_distrito`, `ubigeo_capital`, `created_at`, `updated_at`) VALUES
(1, '10102', 'AMAZONAS', 'CHACHAPOYAS', 'ASUNCION', 'ASUNCION', NULL, NULL),
(2, '10103', 'AMAZONAS', 'CHACHAPOYAS', 'BALSAS', 'BALSAS', NULL, NULL),
(3, '10104', 'AMAZONAS', 'CHACHAPOYAS', 'CHETO', 'CHETO', NULL, NULL),
(4, '10105', 'AMAZONAS', 'CHACHAPOYAS', 'CHILIQUIN', 'CHILIQUIN', NULL, NULL),
(5, '10106', 'AMAZONAS', 'CHACHAPOYAS', 'CHUQUIBAMBA', 'CHUQUIBAMBA', NULL, NULL),
(6, '10107', 'AMAZONAS', 'CHACHAPOYAS', 'GRANADA', 'GRANADA', NULL, NULL),
(7, '10108', 'AMAZONAS', 'CHACHAPOYAS', 'HUANCAS', 'HUANCAS', NULL, NULL),
(8, '10109', 'AMAZONAS', 'CHACHAPOYAS', 'LA JALCA', 'LA JALCA', NULL, NULL),
(9, '10110', 'AMAZONAS', 'CHACHAPOYAS', 'LEIMEBAMBA', 'LEIMEBAMBA', NULL, NULL),
(10, '10111', 'AMAZONAS', 'CHACHAPOYAS', 'LEVANTO', 'LEVANTO', NULL, NULL),
(11, '10112', 'AMAZONAS', 'CHACHAPOYAS', 'MAGDALENA', 'MAGDALENA', NULL, NULL),
(12, '10113', 'AMAZONAS', 'CHACHAPOYAS', 'MARISCAL CASTILLA', 'DURAZNOPAMPA', NULL, NULL),
(13, '10114', 'AMAZONAS', 'CHACHAPOYAS', 'MOLINOPAMPA', 'MOLINOPAMPA', NULL, NULL),
(14, '10115', 'AMAZONAS', 'CHACHAPOYAS', 'MONTEVIDEO', 'MONTEVIDEO', NULL, NULL),
(15, '10116', 'AMAZONAS', 'CHACHAPOYAS', 'OLLEROS', 'OLLEROS', NULL, NULL),
(16, '10117', 'AMAZONAS', 'CHACHAPOYAS', 'QUINJALCA', 'QUINJALCA', NULL, NULL),
(17, '10118', 'AMAZONAS', 'CHACHAPOYAS', 'SAN FRANCISCO DE DAGUAS', 'DAGUAS', NULL, NULL),
(18, '10119', 'AMAZONAS', 'CHACHAPOYAS', 'SAN ISIDRO DE MAINO', 'MAINO', NULL, NULL),
(19, '10120', 'AMAZONAS', 'CHACHAPOYAS', 'SOLOCO', 'SOLOCO', NULL, NULL),
(20, '10121', 'AMAZONAS', 'CHACHAPOYAS', 'SONCHE', 'SAN JUAN DE SONCHE', NULL, NULL),
(21, '10201', 'AMAZONAS', 'BAGUA', 'BAGUA', 'BAGUA', NULL, NULL),
(22, '10202', 'AMAZONAS', 'BAGUA', 'ARAMANGO', 'ARAMANGO', NULL, NULL),
(23, '10203', 'AMAZONAS', 'BAGUA', 'COPALLIN', 'COPALLIN', NULL, NULL),
(24, '10204', 'AMAZONAS', 'BAGUA', 'EL PARCO', 'EL PARCO', NULL, NULL),
(25, '10205', 'AMAZONAS', 'BAGUA', 'IMAZA', 'CHIRIACO', NULL, NULL),
(26, '10206', 'AMAZONAS', 'BAGUA', 'LA PECA', 'LA PECA', NULL, NULL),
(27, '10301', 'AMAZONAS', 'BONGARA', 'JUMBILLA', 'JUMBILLA', NULL, NULL),
(28, '10302', 'AMAZONAS', 'BONGARA', 'CHISQUILLA', 'CHISQUILLA', NULL, NULL),
(29, '10303', 'AMAZONAS', 'BONGARA', 'CHURUJA', 'CHURUJA', NULL, NULL),
(30, '10304', 'AMAZONAS', 'BONGARA', 'COROSHA', 'COROSHA', NULL, NULL),
(31, '10305', 'AMAZONAS', 'BONGARA', 'CUISPES', 'CUISPES', NULL, NULL),
(32, '10306', 'AMAZONAS', 'BONGARA', 'FLORIDA', 'FLORIDA (POMACOCHAS)', NULL, NULL),
(33, '10307', 'AMAZONAS', 'BONGARA', 'JAZAN', 'PEDRO RUIZ GALLO', NULL, NULL),
(34, '10308', 'AMAZONAS', 'BONGARA', 'RECTA', 'RECTA', NULL, NULL),
(35, '10309', 'AMAZONAS', 'BONGARA', 'SAN CARLOS', 'SAN CARLOS', NULL, NULL),
(36, '10310', 'AMAZONAS', 'BONGARA', 'SHIPASBAMBA', 'SHIPASBAMBA', NULL, NULL),
(37, '10311', 'AMAZONAS', 'BONGARA', 'VALERA', 'VALERA (SAN PABLO)', NULL, NULL),
(38, '10312', 'AMAZONAS', 'BONGARA', 'YAMBRASBAMBA', 'YAMBRASBAMBA', NULL, NULL),
(39, '10401', 'AMAZONAS', 'CONDORCANQUI', 'NIEVA', 'SANTA MARIA DE NIEVA', NULL, NULL),
(40, '10402', 'AMAZONAS', 'CONDORCANQUI', 'EL CENEPA', 'HUAMPAMI', NULL, NULL),
(41, '10403', 'AMAZONAS', 'CONDORCANQUI', 'RIO SANTIAGO', 'PUERTO GALILEA', NULL, NULL),
(42, '10501', 'AMAZONAS', 'LUYA', 'LAMUD', 'LAMUD', NULL, NULL),
(43, '10502', 'AMAZONAS', 'LUYA', 'CAMPORREDONDO', 'CAMPORREDONDO', NULL, NULL),
(44, '10503', 'AMAZONAS', 'LUYA', 'COCABAMBA', 'COCABAMBA', NULL, NULL),
(45, '10504', 'AMAZONAS', 'LUYA', 'COLCAMAR', 'COLCAMAR', NULL, NULL),
(46, '10505', 'AMAZONAS', 'LUYA', 'CONILA', 'COHECHAN', NULL, NULL),
(47, '10506', 'AMAZONAS', 'LUYA', 'INGUILPATA', 'INGUILPATA', NULL, NULL),
(48, '10507', 'AMAZONAS', 'LUYA', 'LONGUITA', 'LONGUITA', NULL, NULL),
(49, '10508', 'AMAZONAS', 'LUYA', 'LONYA CHICO', 'LONYA CHICO', NULL, NULL),
(50, '10509', 'AMAZONAS', 'LUYA', 'LUYA', 'LUYA', NULL, NULL),
(51, '10510', 'AMAZONAS', 'LUYA', 'LUYA VIEJO', 'LUYA VIEJO', NULL, NULL),
(52, '10511', 'AMAZONAS', 'LUYA', 'MARIA', 'MARIA', NULL, NULL),
(53, '10512', 'AMAZONAS', 'LUYA', 'OCALLI', 'OCALLI', NULL, NULL),
(54, '10513', 'AMAZONAS', 'LUYA', 'OCUMAL', 'COLLONCE', NULL, NULL),
(55, '10514', 'AMAZONAS', 'LUYA', 'PISUQUIA', 'YOMBLON', NULL, NULL),
(56, '10515', 'AMAZONAS', 'LUYA', 'PROVIDENCIA', 'PROVIDENCIA', NULL, NULL),
(57, '10516', 'AMAZONAS', 'LUYA', 'SAN CRISTOBAL', 'OLTO', NULL, NULL),
(58, '10517', 'AMAZONAS', 'LUYA', 'SAN FRANCISCO DEL YESO', 'SAN FRANCISCO DEL YESO', NULL, NULL),
(59, '10518', 'AMAZONAS', 'LUYA', 'SAN JERONIMO', 'PACLAS', NULL, NULL),
(60, '10519', 'AMAZONAS', 'LUYA', 'SAN JUAN DE LOPECANCHA', 'SAN JUAN DE LOPECANCHA', NULL, NULL),
(61, '10520', 'AMAZONAS', 'LUYA', 'SANTA CATALINA', 'SANTA CATALINA', NULL, NULL),
(62, '10521', 'AMAZONAS', 'LUYA', 'SANTO TOMAS', 'SANTO TOMAS', NULL, NULL),
(63, '10522', 'AMAZONAS', 'LUYA', 'TINGO', 'TINGO', NULL, NULL),
(64, '10523', 'AMAZONAS', 'LUYA', 'TRITA', 'TRITA', NULL, NULL),
(65, '10601', 'AMAZONAS', 'RODRIGUEZ DE MENDOZA', 'SAN NICOLAS', 'MENDOZA', NULL, NULL),
(66, '10602', 'AMAZONAS', 'RODRIGUEZ DE MENDOZA', 'CHIRIMOTO', 'CHIRIMOTO', NULL, NULL),
(67, '10603', 'AMAZONAS', 'RODRIGUEZ DE MENDOZA', 'COCHAMAL', 'COCHAMAL', NULL, NULL),
(68, '10604', 'AMAZONAS', 'RODRIGUEZ DE MENDOZA', 'HUAMBO', 'HUAMBO', NULL, NULL),
(69, '10605', 'AMAZONAS', 'RODRIGUEZ DE MENDOZA', 'LIMABAMBA', 'LIMABAMBA', NULL, NULL),
(70, '10606', 'AMAZONAS', 'RODRIGUEZ DE MENDOZA', 'LONGAR', 'LONGAR', NULL, NULL),
(71, '10607', 'AMAZONAS', 'RODRIGUEZ DE MENDOZA', 'MARISCAL BENAVIDES', 'MARISCAL BENAVIDES', NULL, NULL),
(72, '10608', 'AMAZONAS', 'RODRIGUEZ DE MENDOZA', 'MILPUC', 'MILPUC', NULL, NULL),
(73, '10609', 'AMAZONAS', 'RODRIGUEZ DE MENDOZA', 'OMIA', 'OMIA', NULL, NULL),
(74, '10610', 'AMAZONAS', 'RODRIGUEZ DE MENDOZA', 'SANTA ROSA', 'SANTA ROSA DE HUAYABAMBA', NULL, NULL),
(75, '10611', 'AMAZONAS', 'RODRIGUEZ DE MENDOZA', 'TOTORA', 'TOTORA', NULL, NULL),
(76, '10612', 'AMAZONAS', 'RODRIGUEZ DE MENDOZA', 'VISTA ALEGRE', 'VISTA ALEGRE', NULL, NULL),
(77, '10701', 'AMAZONAS', 'UTCUBAMBA', 'BAGUA GRANDE', 'BAGUA GRANDE', NULL, NULL),
(78, '10702', 'AMAZONAS', 'UTCUBAMBA', 'CAJARURO', 'CAJARURO', NULL, NULL),
(79, '10703', 'AMAZONAS', 'UTCUBAMBA', 'CUMBA', 'CUMBA', NULL, NULL),
(80, '10704', 'AMAZONAS', 'UTCUBAMBA', 'EL MILAGRO', 'EL MILAGRO', NULL, NULL),
(81, '10705', 'AMAZONAS', 'UTCUBAMBA', 'JAMALCA', 'JAMALCA', NULL, NULL),
(82, '10706', 'AMAZONAS', 'UTCUBAMBA', 'LONYA GRANDE', 'LONYA GRANDE', NULL, NULL),
(83, '10707', 'AMAZONAS', 'UTCUBAMBA', 'YAMON', 'YAMON', NULL, NULL),
(84, '20101', 'ANCASH', 'HUARAZ', 'HUARAZ', 'HUARAZ', NULL, NULL),
(85, '20102', 'ANCASH', 'HUARAZ', 'COCHABAMBA', 'COCHABAMBA', NULL, NULL),
(86, '20103', 'ANCASH', 'HUARAZ', 'COLCABAMBA', 'COLCABAMBA', NULL, NULL),
(87, '20104', 'ANCASH', 'HUARAZ', 'HUANCHAY', 'HUANCHAY', NULL, NULL),
(88, '20105', 'ANCASH', 'HUARAZ', 'INDEPENDENCIA', 'CENTENARIO', NULL, NULL),
(89, '20106', 'ANCASH', 'HUARAZ', 'JANGAS', 'JANGAS', NULL, NULL),
(90, '20107', 'ANCASH', 'HUARAZ', 'LA LIBERTAD', 'CAJAMARQUILLA', NULL, NULL),
(91, '20108', 'ANCASH', 'HUARAZ', 'OLLEROS', 'OLLEROS', NULL, NULL),
(92, '20109', 'ANCASH', 'HUARAZ', 'PAMPAS GRANDE', 'PAMPAS GRANDE', NULL, NULL),
(93, '20110', 'ANCASH', 'HUARAZ', 'PARIACOTO', 'PARIACOTO', NULL, NULL),
(94, '20111', 'ANCASH', 'HUARAZ', 'PIRA', 'PIRA', NULL, NULL),
(95, '20112', 'ANCASH', 'HUARAZ', 'TARICA', 'TARICA', NULL, NULL),
(96, '20201', 'ANCASH', 'AIJA', 'AIJA', 'AIJA', NULL, NULL),
(97, '20202', 'ANCASH', 'AIJA', 'CORIS', 'CORIS', NULL, NULL),
(98, '20203', 'ANCASH', 'AIJA', 'HUACLLAN', 'HUACLLAN', NULL, NULL),
(99, '20204', 'ANCASH', 'AIJA', 'LA MERCED', 'LA MERCED', NULL, NULL),
(100, '20205', 'ANCASH', 'AIJA', 'SUCCHA', 'SUCCHA', NULL, NULL),
(101, '20301', 'ANCASH', 'ANTONIO RAYMONDI', 'LLAMELLIN', 'LLAMELLIN', NULL, NULL),
(102, '20302', 'ANCASH', 'ANTONIO RAYMONDI', 'ACZO', 'ACZO', NULL, NULL),
(103, '20303', 'ANCASH', 'ANTONIO RAYMONDI', 'CHACCHO', 'CHACCHO', NULL, NULL),
(104, '20304', 'ANCASH', 'ANTONIO RAYMONDI', 'CHINGAS', 'CHINGAS', NULL, NULL),
(105, '20305', 'ANCASH', 'ANTONIO RAYMONDI', 'MIRGAS', 'MIRGAS', NULL, NULL),
(106, '20306', 'ANCASH', 'ANTONIO RAYMONDI', 'SAN JUAN DE RONTOY', 'SAN JUAN DE RONTOY', NULL, NULL),
(107, '20401', 'ANCASH', 'ASUNCION', 'CHACAS', 'CHACAS', NULL, NULL),
(108, '20402', 'ANCASH', 'ASUNCION', 'ACOCHACA', 'ACOCHACA', NULL, NULL),
(109, '20501', 'ANCASH', 'BOLOGNESI', 'CHIQUIAN', 'CHIQUIAN', NULL, NULL),
(110, '20502', 'ANCASH', 'BOLOGNESI', 'ABELARDO PARDO LEZAMETA', 'LLACLLA', NULL, NULL),
(111, '20503', 'ANCASH', 'BOLOGNESI', 'ANTONIO RAYMONDI', 'RAQUIA', NULL, NULL),
(112, '20504', 'ANCASH', 'BOLOGNESI', 'AQUIA', 'AQUIA', NULL, NULL),
(113, '20505', 'ANCASH', 'BOLOGNESI', 'CAJACAY', 'CAJACAY', NULL, NULL),
(114, '20506', 'ANCASH', 'BOLOGNESI', 'CANIS', 'CANIS', NULL, NULL),
(115, '20507', 'ANCASH', 'BOLOGNESI', 'COLQUIOC', 'CHASQUITAMBO', NULL, NULL),
(116, '20508', 'ANCASH', 'BOLOGNESI', 'HUALLANCA', 'HUALLANCA', NULL, NULL),
(117, '20509', 'ANCASH', 'BOLOGNESI', 'HUASTA', 'HUASTA', NULL, NULL),
(118, '20510', 'ANCASH', 'BOLOGNESI', 'HUAYLLACAYAN', 'HUAYLLACAYAN', NULL, NULL),
(119, '20511', 'ANCASH', 'BOLOGNESI', 'LA PRIMAVERA', 'GORGORILLO', NULL, NULL),
(120, '20512', 'ANCASH', 'BOLOGNESI', 'MANGAS', 'MANGAS', NULL, NULL),
(121, '20513', 'ANCASH', 'BOLOGNESI', 'PACLLON', 'PACLLON', NULL, NULL),
(122, '20514', 'ANCASH', 'BOLOGNESI', 'SAN MIGUEL DE CORPANQUI', 'CORPANQUI', NULL, NULL),
(123, '20515', 'ANCASH', 'BOLOGNESI', 'TICLLOS', 'TICLLOS', NULL, NULL),
(124, '20601', 'ANCASH', 'CARHUAZ', 'CARHUAZ', 'CARHUAZ', NULL, NULL),
(125, '20602', 'ANCASH', 'CARHUAZ', 'ACOPAMPA', 'ACOPAMPA', NULL, NULL),
(126, '20603', 'ANCASH', 'CARHUAZ', 'AMASHCA', 'AMASHCA', NULL, NULL),
(127, '20604', 'ANCASH', 'CARHUAZ', 'ANTA', 'ANTA', NULL, NULL),
(128, '20605', 'ANCASH', 'CARHUAZ', 'ATAQUERO', 'CARHUAC', NULL, NULL),
(129, '20606', 'ANCASH', 'CARHUAZ', 'MARCARA', 'MARCARA', NULL, NULL),
(130, '20607', 'ANCASH', 'CARHUAZ', 'PARIAHUANCA', 'PARIAHUANCA', NULL, NULL),
(131, '20608', 'ANCASH', 'CARHUAZ', 'SAN MIGUEL DE ACO', 'ACO', NULL, NULL),
(132, '20609', 'ANCASH', 'CARHUAZ', 'SHILLA', 'SHILLA', NULL, NULL),
(133, '20610', 'ANCASH', 'CARHUAZ', 'TINCO', 'TINCO', NULL, NULL),
(134, '20611', 'ANCASH', 'CARHUAZ', 'YUNGAR', 'YUNGAR', NULL, NULL),
(135, '20701', 'ANCASH', 'CARLOS FERMIN FITZCARRALD', 'SAN LUIS', 'SAN LUIS', NULL, NULL),
(136, '20702', 'ANCASH', 'CARLOS FERMIN FITZCARRALD', 'SAN NICOLAS', 'SAN NICOLAS', NULL, NULL),
(137, '20703', 'ANCASH', 'CARLOS FERMIN FITZCARRALD', 'YAUYA', 'YAUYA', NULL, NULL),
(138, '20801', 'ANCASH', 'CASMA', 'CASMA', 'CASMA', NULL, NULL),
(139, '20802', 'ANCASH', 'CASMA', 'BUENA VISTA ALTA', 'BUENA VISTA ALTA', NULL, NULL),
(140, '20803', 'ANCASH', 'CASMA', 'COMANDANTE NOEL', 'PUERTO CASMA', NULL, NULL),
(141, '20804', 'ANCASH', 'CASMA', 'YAUTAN', 'YAUTAN', NULL, NULL),
(142, '20901', 'ANCASH', 'CORONGO', 'CORONGO', 'CORONGO', NULL, NULL),
(143, '20902', 'ANCASH', 'CORONGO', 'ACO', 'ACO', NULL, NULL),
(144, '20903', 'ANCASH', 'CORONGO', 'BAMBAS', 'BAMBAS', NULL, NULL),
(145, '20904', 'ANCASH', 'CORONGO', 'CUSCA', 'CUSCA', NULL, NULL),
(146, '20905', 'ANCASH', 'CORONGO', 'LA PAMPA', 'LA PAMPA', NULL, NULL),
(147, '20906', 'ANCASH', 'CORONGO', 'YANAC', 'YANAC', NULL, NULL),
(148, '20907', 'ANCASH', 'CORONGO', 'YUPAN', 'YUPAN', NULL, NULL),
(149, '21001', 'ANCASH', 'HUARI', 'HUARI', 'HUARI', NULL, NULL),
(150, '21002', 'ANCASH', 'HUARI', 'ANRA', 'ANRA', NULL, NULL),
(151, '21003', 'ANCASH', 'HUARI', 'CAJAY', 'CAJAY', NULL, NULL),
(152, '21004', 'ANCASH', 'HUARI', 'CHAVIN DE HUANTAR', 'CHAVIN DE HUANTAR', NULL, NULL),
(153, '21005', 'ANCASH', 'HUARI', 'HUACACHI', 'HUACACHI', NULL, NULL),
(154, '21006', 'ANCASH', 'HUARI', 'HUACCHIS', 'HUACCHIS', NULL, NULL),
(155, '21007', 'ANCASH', 'HUARI', 'HUACHIS', 'HUACHIS', NULL, NULL),
(156, '21008', 'ANCASH', 'HUARI', 'HUANTAR', 'HUANTAR', NULL, NULL),
(157, '21009', 'ANCASH', 'HUARI', 'MASIN', 'MASIN', NULL, NULL),
(158, '21010', 'ANCASH', 'HUARI', 'PAUCAS', 'PAUCAS', NULL, NULL),
(159, '21011', 'ANCASH', 'HUARI', 'PONTO', 'PONTO', NULL, NULL),
(160, '21012', 'ANCASH', 'HUARI', 'RAHUAPAMPA', 'RAHUAPAMPA', NULL, NULL),
(161, '21013', 'ANCASH', 'HUARI', 'RAPAYAN', 'RAPAYAN', NULL, NULL),
(162, '21014', 'ANCASH', 'HUARI', 'SAN MARCOS', 'SAN MARCOS', NULL, NULL),
(163, '21015', 'ANCASH', 'HUARI', 'SAN PEDRO DE CHANA', 'CHANA', NULL, NULL),
(164, '21016', 'ANCASH', 'HUARI', 'UCO', 'UCO', NULL, NULL),
(165, '21101', 'ANCASH', 'HUARMEY', 'HUARMEY', 'HUARMEY', NULL, NULL),
(166, '21102', 'ANCASH', 'HUARMEY', 'COCHAPETI', 'COCHAPETI', NULL, NULL),
(167, '21103', 'ANCASH', 'HUARMEY', 'CULEBRAS', 'LA CALETA CULEBRAS', NULL, NULL),
(168, '21104', 'ANCASH', 'HUARMEY', 'HUAYAN', 'HUAYAN', NULL, NULL),
(169, '21105', 'ANCASH', 'HUARMEY', 'MALVAS', 'MALVAS', NULL, NULL),
(170, '21201', 'ANCASH', 'HUAYLAS', 'CARAZ', 'CARAZ', NULL, NULL),
(171, '21202', 'ANCASH', 'HUAYLAS', 'HUALLANCA', 'HUALLANCA', NULL, NULL),
(172, '21203', 'ANCASH', 'HUAYLAS', 'HUATA', 'HUATA', NULL, NULL),
(173, '21204', 'ANCASH', 'HUAYLAS', 'HUAYLAS', 'HUAYLAS', NULL, NULL),
(174, '21205', 'ANCASH', 'HUAYLAS', 'MATO', 'SUCRE', NULL, NULL),
(175, '21206', 'ANCASH', 'HUAYLAS', 'PAMPAROMAS', 'PAMPAROMAS', NULL, NULL),
(176, '21207', 'ANCASH', 'HUAYLAS', 'PUEBLO LIBRE', 'PUEBLO LIBRE /1', NULL, NULL),
(177, '21208', 'ANCASH', 'HUAYLAS', 'SANTA CRUZ', 'HUARIPAMPA', NULL, NULL),
(178, '21209', 'ANCASH', 'HUAYLAS', 'SANTO TORIBIO', 'SANTO TORIBIO', NULL, NULL),
(179, '21210', 'ANCASH', 'HUAYLAS', 'YURACMARCA', 'YURACMARCA', NULL, NULL),
(180, '21301', 'ANCASH', 'MARISCAL LUZURIAGA', 'PISCOBAMBA', 'PISCOBAMBA', NULL, NULL),
(181, '21302', 'ANCASH', 'MARISCAL LUZURIAGA', 'CASCA', 'CASCA', NULL, NULL),
(182, '21303', 'ANCASH', 'MARISCAL LUZURIAGA', 'ELEAZAR GUZMAN BARRON', 'PAMPACHACRA', NULL, NULL),
(183, '21304', 'ANCASH', 'MARISCAL LUZURIAGA', 'FIDEL OLIVAS ESCUDERO', 'SANACHGAN', NULL, NULL),
(184, '21305', 'ANCASH', 'MARISCAL LUZURIAGA', 'LLAMA', 'LLAMA', NULL, NULL),
(185, '21306', 'ANCASH', 'MARISCAL LUZURIAGA', 'LLUMPA', 'LLUMPA', NULL, NULL),
(186, '21307', 'ANCASH', 'MARISCAL LUZURIAGA', 'LUCMA', 'LUCMA', NULL, NULL),
(187, '21308', 'ANCASH', 'MARISCAL LUZURIAGA', 'MUSGA', 'MUSGA', NULL, NULL),
(188, '21401', 'ANCASH', 'OCROS', 'OCROS', 'OCROS', NULL, NULL),
(189, '21402', 'ANCASH', 'OCROS', 'ACAS', 'ACAS', NULL, NULL),
(190, '21403', 'ANCASH', 'OCROS', 'CAJAMARQUILLA', 'CAJAMARQUILLA', NULL, NULL),
(191, '21404', 'ANCASH', 'OCROS', 'CARHUAPAMPA', 'ACO', NULL, NULL),
(192, '21405', 'ANCASH', 'OCROS', 'COCHAS', 'HUANCHAY', NULL, NULL),
(193, '21406', 'ANCASH', 'OCROS', 'CONGAS', 'CONGAS', NULL, NULL),
(194, '21407', 'ANCASH', 'OCROS', 'LLIPA', 'LLIPA', NULL, NULL),
(195, '21408', 'ANCASH', 'OCROS', 'SAN CRISTOBAL DE RAJAN', 'RAJAN', NULL, NULL),
(196, '21409', 'ANCASH', 'OCROS', 'SAN PEDRO', 'COPA', NULL, NULL),
(197, '21410', 'ANCASH', 'OCROS', 'SANTIAGO DE CHILCAS', 'SANTIAGO DE CHILCAS', NULL, NULL),
(198, '21501', 'ANCASH', 'PALLASCA', 'CABANA', 'CABANA', NULL, NULL),
(199, '21502', 'ANCASH', 'PALLASCA', 'BOLOGNESI', 'BOLOGNESI', NULL, NULL),
(200, '21503', 'ANCASH', 'PALLASCA', 'CONCHUCOS', 'CONCHUCOS', NULL, NULL),
(201, '21504', 'ANCASH', 'PALLASCA', 'HUACASCHUQUE', 'HUACASCHUQUE', NULL, NULL),
(202, '21505', 'ANCASH', 'PALLASCA', 'HUANDOVAL', 'HUANDOVAL', NULL, NULL),
(203, '21506', 'ANCASH', 'PALLASCA', 'LACABAMBA', 'LACABAMBA', NULL, NULL),
(204, '21507', 'ANCASH', 'PALLASCA', 'LLAPO', 'LLAPO', NULL, NULL),
(205, '21508', 'ANCASH', 'PALLASCA', 'PALLASCA', 'PALLASCA', NULL, NULL),
(206, '21509', 'ANCASH', 'PALLASCA', 'PAMPAS', 'PAMPAS', NULL, NULL),
(207, '21510', 'ANCASH', 'PALLASCA', 'SANTA ROSA', 'SANTA ROSA', NULL, NULL),
(208, '21511', 'ANCASH', 'PALLASCA', 'TAUCA', 'TAUCA', NULL, NULL),
(209, '21601', 'ANCASH', 'POMABAMBA', 'POMABAMBA', 'POMABAMBA', NULL, NULL),
(210, '21602', 'ANCASH', 'POMABAMBA', 'HUAYLLAN', 'HUAYLLAN', NULL, NULL),
(211, '21603', 'ANCASH', 'POMABAMBA', 'PAROBAMBA', 'PAROBAMBA', NULL, NULL),
(212, '21604', 'ANCASH', 'POMABAMBA', 'QUINUABAMBA', 'QUINUABAMBA', NULL, NULL),
(213, '21701', 'ANCASH', 'RECUAY', 'RECUAY', 'RECUAY', NULL, NULL),
(214, '21702', 'ANCASH', 'RECUAY', 'CATAC', 'CATAC', NULL, NULL),
(215, '21703', 'ANCASH', 'RECUAY', 'COTAPARACO', 'COTAPARACO', NULL, NULL),
(216, '21704', 'ANCASH', 'RECUAY', 'HUAYLLAPAMPA', 'HUAYLLAPAMPA', NULL, NULL),
(217, '21705', 'ANCASH', 'RECUAY', 'LLACLLIN', 'LLACLLIN', NULL, NULL),
(218, '21706', 'ANCASH', 'RECUAY', 'MARCA', 'MARCA', NULL, NULL),
(219, '21707', 'ANCASH', 'RECUAY', 'PAMPAS CHICO', 'PAMPAS CHICO', NULL, NULL),
(220, '21708', 'ANCASH', 'RECUAY', 'PARARIN', 'PARARIN', NULL, NULL),
(221, '21709', 'ANCASH', 'RECUAY', 'TAPACOCHA', 'TAPACOCHA', NULL, NULL),
(222, '21710', 'ANCASH', 'RECUAY', 'TICAPAMPA', 'TICAPAMPA', NULL, NULL),
(223, '21801', 'ANCASH', 'SANTA', 'CHIMBOTE', 'CHIMBOTE', NULL, NULL),
(224, '21802', 'ANCASH', 'SANTA', 'CACERES DEL PERU', 'JIMBE', NULL, NULL),
(225, '21803', 'ANCASH', 'SANTA', 'COISHCO', 'COISHCO', NULL, NULL),
(226, '21804', 'ANCASH', 'SANTA', 'MACATE', 'MACATE', NULL, NULL),
(227, '21805', 'ANCASH', 'SANTA', 'MORO', 'MORO', NULL, NULL),
(228, '21806', 'ANCASH', 'SANTA', 'NEPEÑA', 'NEPEÑA', NULL, NULL),
(229, '21807', 'ANCASH', 'SANTA', 'SAMANCO', 'SAMANCO', NULL, NULL),
(230, '21808', 'ANCASH', 'SANTA', 'SANTA', 'SANTA', NULL, NULL),
(231, '21809', 'ANCASH', 'SANTA', 'NUEVO CHIMBOTE', 'BUENOS AIRES', NULL, NULL),
(232, '21901', 'ANCASH', 'SIHUAS', 'SIHUAS', 'SIHUAS', NULL, NULL),
(233, '21902', 'ANCASH', 'SIHUAS', 'ACOBAMBA', 'ACOBAMBA', NULL, NULL),
(234, '21903', 'ANCASH', 'SIHUAS', 'ALFONSO UGARTE', 'ULLULLUCO', NULL, NULL),
(235, '21904', 'ANCASH', 'SIHUAS', 'CASHAPAMPA', 'CASHAPAMPA', NULL, NULL),
(236, '21905', 'ANCASH', 'SIHUAS', 'CHINGALPO', 'CHINGALPO', NULL, NULL),
(237, '21906', 'ANCASH', 'SIHUAS', 'HUAYLLABAMBA', 'HUAYLLABAMBA', NULL, NULL),
(238, '21907', 'ANCASH', 'SIHUAS', 'QUICHES', 'QUICHES', NULL, NULL),
(239, '21908', 'ANCASH', 'SIHUAS', 'RAGASH', 'RAGASH', NULL, NULL),
(240, '21909', 'ANCASH', 'SIHUAS', 'SAN JUAN', 'CHULLIN', NULL, NULL),
(241, '21910', 'ANCASH', 'SIHUAS', 'SICSIBAMBA', 'UMBE', NULL, NULL),
(242, '22001', 'ANCASH', 'YUNGAY', 'YUNGAY', 'YUNGAY', NULL, NULL),
(243, '22002', 'ANCASH', 'YUNGAY', 'CASCAPARA', 'CASCAPARA', NULL, NULL),
(244, '22003', 'ANCASH', 'YUNGAY', 'MANCOS', 'MANCOS', NULL, NULL),
(245, '22004', 'ANCASH', 'YUNGAY', 'MATACOTO', 'MATACOTO', NULL, NULL),
(246, '22005', 'ANCASH', 'YUNGAY', 'QUILLO', 'QUILLO', NULL, NULL),
(247, '22006', 'ANCASH', 'YUNGAY', 'RANRAHIRCA', 'RANRAHIRCA', NULL, NULL),
(248, '22007', 'ANCASH', 'YUNGAY', 'SHUPLUY', 'SHUPLUY', NULL, NULL),
(249, '22008', 'ANCASH', 'YUNGAY', 'YANAMA', 'YANAMA', NULL, NULL),
(250, '30101', 'APURIMAC', 'ABANCAY', 'ABANCAY', 'ABANCAY', NULL, NULL),
(251, '30102', 'APURIMAC', 'ABANCAY', 'CHACOCHE', 'CHACOCHE', NULL, NULL),
(252, '30103', 'APURIMAC', 'ABANCAY', 'CIRCA', 'CIRCA', NULL, NULL),
(253, '30104', 'APURIMAC', 'ABANCAY', 'CURAHUASI', 'CURAHUASI', NULL, NULL),
(254, '30105', 'APURIMAC', 'ABANCAY', 'HUANIPACA', 'HUANIPACA', NULL, NULL),
(255, '30106', 'APURIMAC', 'ABANCAY', 'LAMBRAMA', 'LAMBRAMA', NULL, NULL),
(256, '30107', 'APURIMAC', 'ABANCAY', 'PICHIRHUA', 'PICHIRHUA', NULL, NULL),
(257, '30108', 'APURIMAC', 'ABANCAY', 'SAN PEDRO DE CACHORA', 'CACHORA', NULL, NULL),
(258, '30109', 'APURIMAC', 'ABANCAY', 'TAMBURCO', 'TAMBURCO', NULL, NULL),
(259, '30201', 'APURIMAC', 'ANDAHUAYLAS', 'ANDAHUAYLAS', 'ANDAHUAYLAS', NULL, NULL),
(260, '30202', 'APURIMAC', 'ANDAHUAYLAS', 'ANDARAPA', 'ANDARAPA', NULL, NULL),
(261, '30203', 'APURIMAC', 'ANDAHUAYLAS', 'CHIARA', 'CHIARA', NULL, NULL),
(262, '30204', 'APURIMAC', 'ANDAHUAYLAS', 'HUANCARAMA', 'HUANCARAMA', NULL, NULL),
(263, '30205', 'APURIMAC', 'ANDAHUAYLAS', 'HUANCARAY', 'HUANCARAY', NULL, NULL),
(264, '30206', 'APURIMAC', 'ANDAHUAYLAS', 'HUAYANA', 'HUAYANA', NULL, NULL),
(265, '30207', 'APURIMAC', 'ANDAHUAYLAS', 'KISHUARA', 'KISHUARA', NULL, NULL),
(266, '30208', 'APURIMAC', 'ANDAHUAYLAS', 'PACOBAMBA', 'PACOBAMBA', NULL, NULL),
(267, '30209', 'APURIMAC', 'ANDAHUAYLAS', 'PACUCHA', 'PACUCHA', NULL, NULL),
(268, '30210', 'APURIMAC', 'ANDAHUAYLAS', 'PAMPACHIRI', 'PAMPACHIRI', NULL, NULL),
(269, '30211', 'APURIMAC', 'ANDAHUAYLAS', 'POMACOCHA', 'POMACOCHA', NULL, NULL),
(270, '30212', 'APURIMAC', 'ANDAHUAYLAS', 'SAN ANTONIO DE CACHI', 'SAN ANTONIO DE CACHI', NULL, NULL),
(271, '30213', 'APURIMAC', 'ANDAHUAYLAS', 'SAN JERONIMO', 'SAN JERONIMO', NULL, NULL),
(272, '30214', 'APURIMAC', 'ANDAHUAYLAS', 'SAN MIGUEL DE CHACCRAMPA', 'CHACCRAMPA', NULL, NULL),
(273, '30215', 'APURIMAC', 'ANDAHUAYLAS', 'SANTA MARIA DE CHICMO', 'SANTA MARIA DE CHICMO', NULL, NULL),
(274, '30216', 'APURIMAC', 'ANDAHUAYLAS', 'TALAVERA', 'TALAVERA', NULL, NULL),
(275, '30217', 'APURIMAC', 'ANDAHUAYLAS', 'TUMAY HUARACA', 'UMAMARCA', NULL, NULL),
(276, '30218', 'APURIMAC', 'ANDAHUAYLAS', 'TURPO', 'TURPO', NULL, NULL),
(277, '30219', 'APURIMAC', 'ANDAHUAYLAS', 'KAQUIABAMBA', 'KAQUIABAMBA', NULL, NULL),
(278, '30220', 'APURIMAC', 'ANDAHUAYLAS', 'JOSE MARIA ARGUEDAS', 'HUANCABAMBA', NULL, NULL),
(279, '30301', 'APURIMAC', 'ANTABAMBA', 'ANTABAMBA', 'ANTABAMBA', NULL, NULL),
(280, '30302', 'APURIMAC', 'ANTABAMBA', 'EL ORO', 'AYAHUAY', NULL, NULL),
(281, '30303', 'APURIMAC', 'ANTABAMBA', 'HUAQUIRCA', 'HUAQUIRCA', NULL, NULL),
(282, '30304', 'APURIMAC', 'ANTABAMBA', 'JUAN ESPINOZA MEDRANO', 'MOLLEBAMBA', NULL, NULL),
(283, '30305', 'APURIMAC', 'ANTABAMBA', 'OROPESA', 'OROPESA', NULL, NULL),
(284, '30306', 'APURIMAC', 'ANTABAMBA', 'PACHACONAS', 'PACHACONAS', NULL, NULL),
(285, '30307', 'APURIMAC', 'ANTABAMBA', 'SABAINO', 'SABAINO', NULL, NULL),
(286, '30401', 'APURIMAC', 'AYMARAES', 'CHALHUANCA', 'CHALHUANCA', NULL, NULL),
(287, '30402', 'APURIMAC', 'AYMARAES', 'CAPAYA', 'CAPAYA', NULL, NULL),
(288, '30403', 'APURIMAC', 'AYMARAES', 'CARAYBAMBA', 'CARAYBAMBA', NULL, NULL),
(289, '30404', 'APURIMAC', 'AYMARAES', 'CHAPIMARCA', 'CHAPIMARCA', NULL, NULL),
(290, '30405', 'APURIMAC', 'AYMARAES', 'COLCABAMBA', 'COLCABAMBA', NULL, NULL),
(291, '30406', 'APURIMAC', 'AYMARAES', 'COTARUSE', 'COTARUSE', NULL, NULL),
(292, '30407', 'APURIMAC', 'AYMARAES', 'IHUAYLLO', 'IHUAYLLO', NULL, NULL),
(293, '30408', 'APURIMAC', 'AYMARAES', 'JUSTO APU SAHUARAURA', 'PICHIHUA', NULL, NULL),
(294, '30409', 'APURIMAC', 'AYMARAES', 'LUCRE', 'LUCRE', NULL, NULL),
(295, '30410', 'APURIMAC', 'AYMARAES', 'POCOHUANCA', 'POCOHUANCA', NULL, NULL),
(296, '30411', 'APURIMAC', 'AYMARAES', 'SAN JUAN DE CHACÑA', 'SAN JUAN DE CHACÑA', NULL, NULL),
(297, '30412', 'APURIMAC', 'AYMARAES', 'SAÑAYCA', 'SAÑAYCA', NULL, NULL),
(298, '30413', 'APURIMAC', 'AYMARAES', 'SORAYA', 'SORAYA', NULL, NULL),
(299, '30414', 'APURIMAC', 'AYMARAES', 'TAPAIRIHUA', 'TAPAIRIHUA', NULL, NULL),
(300, '30415', 'APURIMAC', 'AYMARAES', 'TINTAY', 'TINTAY', NULL, NULL),
(301, '30416', 'APURIMAC', 'AYMARAES', 'TORAYA', 'TORAYA', NULL, NULL),
(302, '30417', 'APURIMAC', 'AYMARAES', 'YANACA', 'YANACA', NULL, NULL),
(303, '30501', 'APURIMAC', 'COTABAMBAS', 'TAMBOBAMBA', 'TAMBOBAMBA', NULL, NULL),
(304, '30502', 'APURIMAC', 'COTABAMBAS', 'COTABAMBAS', 'COTABAMBAS', NULL, NULL),
(305, '30503', 'APURIMAC', 'COTABAMBAS', 'COYLLURQUI', 'COYLLURQUI', NULL, NULL),
(306, '30504', 'APURIMAC', 'COTABAMBAS', 'HAQUIRA', 'HAQUIRA', NULL, NULL),
(307, '30505', 'APURIMAC', 'COTABAMBAS', 'MARA', 'MARA', NULL, NULL),
(308, '30506', 'APURIMAC', 'COTABAMBAS', 'CHALLHUAHUACHO', 'CHALLHUAHUACHO', NULL, NULL),
(309, '30601', 'APURIMAC', 'CHINCHEROS', 'CHINCHEROS', 'CHINCHEROS', NULL, NULL),
(310, '30602', 'APURIMAC', 'CHINCHEROS', 'ANCO_HUALLO', 'URIPA', NULL, NULL),
(311, '30603', 'APURIMAC', 'CHINCHEROS', 'COCHARCAS', 'COCHARCAS', NULL, NULL),
(312, '30604', 'APURIMAC', 'CHINCHEROS', 'HUACCANA', 'HUACCANA', NULL, NULL),
(313, '30605', 'APURIMAC', 'CHINCHEROS', 'OCOBAMBA', 'OCOBAMBA', NULL, NULL),
(314, '30606', 'APURIMAC', 'CHINCHEROS', 'ONGOY', 'ONGOY', NULL, NULL),
(315, '30607', 'APURIMAC', 'CHINCHEROS', 'URANMARCA', 'URANMARCA', NULL, NULL),
(316, '30608', 'APURIMAC', 'CHINCHEROS', 'RANRACANCHA', 'RANRACANCHA', NULL, NULL),
(317, '30609', 'APURIMAC', 'CHINCHEROS', 'ROCCHACC', 'ROCCHACC', NULL, NULL),
(318, '30610', 'APURIMAC', 'CHINCHEROS', 'EL PORVENIR', 'SAN PEDRO HUAMBURQUE', NULL, NULL),
(319, '30611', 'APURIMAC', 'CHINCHEROS', 'LOS CHANKAS', 'RIO BLANCO', NULL, NULL),
(320, '30701', 'APURIMAC', 'GRAU', 'CHUQUIBAMBILLA', 'CHUQUIBAMBILLA', NULL, NULL),
(321, '30702', 'APURIMAC', 'GRAU', 'CURPAHUASI', 'CURPAHUASI', NULL, NULL),
(322, '30703', 'APURIMAC', 'GRAU', 'GAMARRA', 'PALPACACHI', NULL, NULL),
(323, '30704', 'APURIMAC', 'GRAU', 'HUAYLLATI', 'HUAYLLATI', NULL, NULL),
(324, '30705', 'APURIMAC', 'GRAU', 'MAMARA', 'MAMARA', NULL, NULL),
(325, '30706', 'APURIMAC', 'GRAU', 'MICAELA BASTIDAS', 'AYRIHUANCA', NULL, NULL),
(326, '30707', 'APURIMAC', 'GRAU', 'PATAYPAMPA', 'PATAYPAMPA', NULL, NULL),
(327, '30708', 'APURIMAC', 'GRAU', 'PROGRESO', 'PROGRESO', NULL, NULL),
(328, '30709', 'APURIMAC', 'GRAU', 'SAN ANTONIO', 'SAN ANTONIO', NULL, NULL),
(329, '30710', 'APURIMAC', 'GRAU', 'SANTA ROSA', 'SANTA ROSA', NULL, NULL),
(330, '30711', 'APURIMAC', 'GRAU', 'TURPAY', 'TURPAY', NULL, NULL),
(331, '30712', 'APURIMAC', 'GRAU', 'VILCABAMBA', 'VILCABAMBA', NULL, NULL),
(332, '30713', 'APURIMAC', 'GRAU', 'VIRUNDO', 'SAN JUAN DE VIRUNDO', NULL, NULL),
(333, '30714', 'APURIMAC', 'GRAU', 'CURASCO', 'CURASCO', NULL, NULL),
(334, '40101', 'AREQUIPA', 'AREQUIPA', 'AREQUIPA', 'AREQUIPA', NULL, NULL),
(335, '40102', 'AREQUIPA', 'AREQUIPA', 'ALTO SELVA ALEGRE', 'SELVA ALEGRE', NULL, NULL),
(336, '40103', 'AREQUIPA', 'AREQUIPA', 'CAYMA', 'CAYMA', NULL, NULL),
(337, '40104', 'AREQUIPA', 'AREQUIPA', 'CERRO COLORADO', 'LA LIBERTAD', NULL, NULL),
(338, '40105', 'AREQUIPA', 'AREQUIPA', 'CHARACATO', 'CHARACATO', NULL, NULL),
(339, '40106', 'AREQUIPA', 'AREQUIPA', 'CHIGUATA', 'CHIGUATA', NULL, NULL),
(340, '40107', 'AREQUIPA', 'AREQUIPA', 'JACOBO HUNTER', 'JACOBO HUNTER', NULL, NULL),
(341, '40108', 'AREQUIPA', 'AREQUIPA', 'LA JOYA', 'LA JOYA', NULL, NULL),
(342, '40109', 'AREQUIPA', 'AREQUIPA', 'MARIANO MELGAR', 'MARIANO MELGAR', NULL, NULL),
(343, '40110', 'AREQUIPA', 'AREQUIPA', 'MIRAFLORES', 'MIRAFLORES', NULL, NULL),
(344, '40111', 'AREQUIPA', 'AREQUIPA', 'MOLLEBAYA', 'MOLLEBAYA', NULL, NULL),
(345, '40112', 'AREQUIPA', 'AREQUIPA', 'PAUCARPATA', 'PAUCARPATA', NULL, NULL),
(346, '40113', 'AREQUIPA', 'AREQUIPA', 'POCSI', 'POCSI', NULL, NULL),
(347, '40114', 'AREQUIPA', 'AREQUIPA', 'POLOBAYA', 'POLOBAYA GRANDE', NULL, NULL),
(348, '40115', 'AREQUIPA', 'AREQUIPA', 'QUEQUEÑA', 'QUEQUEÑA', NULL, NULL),
(349, '40116', 'AREQUIPA', 'AREQUIPA', 'SABANDIA', 'SABANDIA', NULL, NULL),
(350, '40117', 'AREQUIPA', 'AREQUIPA', 'SACHACA', 'SACHACA', NULL, NULL),
(351, '40118', 'AREQUIPA', 'AREQUIPA', 'SAN JUAN DE SIGUAS', 'SAN JUAN DE SIGUAS /2', NULL, NULL),
(352, '40119', 'AREQUIPA', 'AREQUIPA', 'SAN JUAN DE TARUCANI', 'TARUCANI', NULL, NULL),
(353, '40120', 'AREQUIPA', 'AREQUIPA', 'SANTA ISABEL DE SIGUAS', 'SANTA ISABEL DE SIGUAS', NULL, NULL),
(354, '40121', 'AREQUIPA', 'AREQUIPA', 'SANTA RITA DE SIGUAS', 'SANTA RITA DE SIGUAS', NULL, NULL),
(355, '40122', 'AREQUIPA', 'AREQUIPA', 'SOCABAYA', 'SOCABAYA', NULL, NULL),
(356, '40123', 'AREQUIPA', 'AREQUIPA', 'TIABAYA', 'TIABAYA', NULL, NULL),
(357, '40124', 'AREQUIPA', 'AREQUIPA', 'UCHUMAYO', 'UCHUMAYO', NULL, NULL),
(358, '40125', 'AREQUIPA', 'AREQUIPA', 'VITOR', 'VITOR', NULL, NULL),
(359, '40126', 'AREQUIPA', 'AREQUIPA', 'YANAHUARA', 'YANAHUARA', NULL, NULL),
(360, '40127', 'AREQUIPA', 'AREQUIPA', 'YARABAMBA', 'YARABAMBA', NULL, NULL),
(361, '40128', 'AREQUIPA', 'AREQUIPA', 'YURA', 'YURA', NULL, NULL),
(362, '40129', 'AREQUIPA', 'AREQUIPA', 'JOSE LUIS BUSTAMANTE Y RIVERO', 'CIUDAD SATELITE', NULL, NULL),
(363, '40201', 'AREQUIPA', 'CAMANA', 'CAMANA', 'CAMANA', NULL, NULL),
(364, '40202', 'AREQUIPA', 'CAMANA', 'JOSE MARIA QUIMPER', 'EL CARDO', NULL, NULL),
(365, '40203', 'AREQUIPA', 'CAMANA', 'MARIANO NICOLAS VALCARCEL', 'URASQUI', NULL, NULL),
(366, '40204', 'AREQUIPA', 'CAMANA', 'MARISCAL CACERES', 'SAN JOSE', NULL, NULL),
(367, '40205', 'AREQUIPA', 'CAMANA', 'NICOLAS DE PIEROLA', 'SAN GREGORIO', NULL, NULL),
(368, '40206', 'AREQUIPA', 'CAMANA', 'OCOÑA', 'OCOÑA', NULL, NULL),
(369, '40207', 'AREQUIPA', 'CAMANA', 'QUILCA', 'QUILCA', NULL, NULL),
(370, '40208', 'AREQUIPA', 'CAMANA', 'SAMUEL PASTOR', 'LA PAMPA', NULL, NULL),
(371, '40301', 'AREQUIPA', 'CARAVELI', 'CARAVELI', 'CARAVELI', NULL, NULL),
(372, '40302', 'AREQUIPA', 'CARAVELI', 'ACARI', 'ACARI', NULL, NULL),
(373, '40303', 'AREQUIPA', 'CARAVELI', 'ATICO', 'ATICO', NULL, NULL),
(374, '40304', 'AREQUIPA', 'CARAVELI', 'ATIQUIPA', 'ATIQUIPA', NULL, NULL),
(375, '40305', 'AREQUIPA', 'CARAVELI', 'BELLA UNION', 'BELLA UNION', NULL, NULL),
(376, '40306', 'AREQUIPA', 'CARAVELI', 'CAHUACHO', 'CAHUACHO', NULL, NULL),
(377, '40307', 'AREQUIPA', 'CARAVELI', 'CHALA', 'CHALA', NULL, NULL),
(378, '40308', 'AREQUIPA', 'CARAVELI', 'CHAPARRA', 'ACHANIZO', NULL, NULL),
(379, '40309', 'AREQUIPA', 'CARAVELI', 'HUANUHUANU', 'TOCOTA', NULL, NULL),
(380, '40310', 'AREQUIPA', 'CARAVELI', 'JAQUI', 'JAQUI', NULL, NULL),
(381, '40311', 'AREQUIPA', 'CARAVELI', 'LOMAS', 'LOMAS', NULL, NULL),
(382, '40312', 'AREQUIPA', 'CARAVELI', 'QUICACHA', 'QUICACHA', NULL, NULL),
(383, '40313', 'AREQUIPA', 'CARAVELI', 'YAUCA', 'YAUCA', NULL, NULL),
(384, '40401', 'AREQUIPA', 'CASTILLA', 'APLAO', 'APLAO', NULL, NULL),
(385, '40402', 'AREQUIPA', 'CASTILLA', 'ANDAGUA', 'ANDAGUA', NULL, NULL),
(386, '40403', 'AREQUIPA', 'CASTILLA', 'AYO', 'AYO', NULL, NULL),
(387, '40404', 'AREQUIPA', 'CASTILLA', 'CHACHAS', 'CHACHAS', NULL, NULL),
(388, '40405', 'AREQUIPA', 'CASTILLA', 'CHILCAYMARCA', 'CHILCAYMARCA', NULL, NULL),
(389, '40406', 'AREQUIPA', 'CASTILLA', 'CHOCO', 'CHOCO', NULL, NULL),
(390, '40407', 'AREQUIPA', 'CASTILLA', 'HUANCARQUI', 'HUANCARQUI', NULL, NULL),
(391, '40408', 'AREQUIPA', 'CASTILLA', 'MACHAGUAY', 'MACHAGUAY', NULL, NULL),
(392, '40409', 'AREQUIPA', 'CASTILLA', 'ORCOPAMPA', 'ORCOPAMPA', NULL, NULL),
(393, '40410', 'AREQUIPA', 'CASTILLA', 'PAMPACOLCA', 'PAMPACOLCA', NULL, NULL),
(394, '40411', 'AREQUIPA', 'CASTILLA', 'TIPAN', 'TIPAN', NULL, NULL),
(395, '40412', 'AREQUIPA', 'CASTILLA', 'UÑON', 'UÑON', NULL, NULL),
(396, '40413', 'AREQUIPA', 'CASTILLA', 'URACA', 'CORIRI', NULL, NULL),
(397, '40414', 'AREQUIPA', 'CASTILLA', 'VIRACO', 'VIRACO', NULL, NULL),
(398, '40501', 'AREQUIPA', 'CAYLLOMA', 'CHIVAY', 'CHIVAY', NULL, NULL),
(399, '40502', 'AREQUIPA', 'CAYLLOMA', 'ACHOMA', 'ACHOMA', NULL, NULL),
(400, '40503', 'AREQUIPA', 'CAYLLOMA', 'CABANACONDE', 'CABANACONDE', NULL, NULL),
(401, '40504', 'AREQUIPA', 'CAYLLOMA', 'CALLALLI', 'CALLALLI', NULL, NULL),
(402, '40505', 'AREQUIPA', 'CAYLLOMA', 'CAYLLOMA', 'CAYLLOMA', NULL, NULL),
(403, '40506', 'AREQUIPA', 'CAYLLOMA', 'COPORAQUE', 'COPORAQUE', NULL, NULL),
(404, '40507', 'AREQUIPA', 'CAYLLOMA', 'HUAMBO', 'HUAMBO', NULL, NULL),
(405, '40508', 'AREQUIPA', 'CAYLLOMA', 'HUANCA', 'HUANCA', NULL, NULL),
(406, '40509', 'AREQUIPA', 'CAYLLOMA', 'ICHUPAMPA', 'ICHUPAMPA', NULL, NULL),
(407, '40510', 'AREQUIPA', 'CAYLLOMA', 'LARI', 'LARI', NULL, NULL),
(408, '40511', 'AREQUIPA', 'CAYLLOMA', 'LLUTA', 'LLUTA', NULL, NULL),
(409, '40512', 'AREQUIPA', 'CAYLLOMA', 'MACA', 'MACA', NULL, NULL),
(410, '40513', 'AREQUIPA', 'CAYLLOMA', 'MADRIGAL', 'MADRIGAL', NULL, NULL),
(411, '40514', 'AREQUIPA', 'CAYLLOMA', 'SAN ANTONIO DE CHUCA', 'SAN ANTONIO DE CHUCA /3', NULL, NULL),
(412, '40515', 'AREQUIPA', 'CAYLLOMA', 'SIBAYO', 'SIBAYO', NULL, NULL),
(413, '40516', 'AREQUIPA', 'CAYLLOMA', 'TAPAY', 'TAPAY', NULL, NULL),
(414, '40517', 'AREQUIPA', 'CAYLLOMA', 'TISCO', 'TISCO', NULL, NULL),
(415, '40518', 'AREQUIPA', 'CAYLLOMA', 'TUTI', 'TUTI', NULL, NULL),
(416, '40519', 'AREQUIPA', 'CAYLLOMA', 'YANQUE', 'YANQUE', NULL, NULL),
(417, '40520', 'AREQUIPA', 'CAYLLOMA', 'MAJES', 'EL PEDREGAL', NULL, NULL),
(418, '40601', 'AREQUIPA', 'CONDESUYOS', 'CHUQUIBAMBA', 'CHUQUIBAMBA', NULL, NULL),
(419, '40602', 'AREQUIPA', 'CONDESUYOS', 'ANDARAY', 'ANDARAY', NULL, NULL),
(420, '40603', 'AREQUIPA', 'CONDESUYOS', 'CAYARANI', 'CAYARANI', NULL, NULL),
(421, '40604', 'AREQUIPA', 'CONDESUYOS', 'CHICHAS', 'CHICHAS', NULL, NULL),
(422, '40605', 'AREQUIPA', 'CONDESUYOS', 'IRAY', 'IRAY', NULL, NULL),
(423, '40606', 'AREQUIPA', 'CONDESUYOS', 'RIO GRANDE', 'IQUIPI', NULL, NULL),
(424, '40607', 'AREQUIPA', 'CONDESUYOS', 'SALAMANCA', 'SALAMANCA', NULL, NULL),
(425, '40608', 'AREQUIPA', 'CONDESUYOS', 'YANAQUIHUA', 'YANAQUIHUA', NULL, NULL),
(426, '40701', 'AREQUIPA', 'ISLAY', 'MOLLENDO', 'MOLLENDO', NULL, NULL),
(427, '40702', 'AREQUIPA', 'ISLAY', 'COCACHACRA', 'COCACHACRA', NULL, NULL),
(428, '40703', 'AREQUIPA', 'ISLAY', 'DEAN VALDIVIA', 'LA CURVA', NULL, NULL),
(429, '40704', 'AREQUIPA', 'ISLAY', 'ISLAY', 'ISLAY (MATARANI)', NULL, NULL),
(430, '40705', 'AREQUIPA', 'ISLAY', 'MEJIA', 'MEJIA', NULL, NULL),
(431, '40706', 'AREQUIPA', 'ISLAY', 'PUNTA DE BOMBON', 'PUNTA DE BOMBON', NULL, NULL),
(432, '40801', 'AREQUIPA', 'LA UNION', 'COTAHUASI', 'COTAHUASI', NULL, NULL),
(433, '40802', 'AREQUIPA', 'LA UNION', 'ALCA', 'ALCA', NULL, NULL),
(434, '40803', 'AREQUIPA', 'LA UNION', 'CHARCANA', 'CHARCANA', NULL, NULL),
(435, '40804', 'AREQUIPA', 'LA UNION', 'HUAYNACOTAS', 'TAURISMA', NULL, NULL),
(436, '40805', 'AREQUIPA', 'LA UNION', 'PAMPAMARCA', 'MUNGUI', NULL, NULL),
(437, '40806', 'AREQUIPA', 'LA UNION', 'PUYCA', 'PUYCA', NULL, NULL),
(438, '40807', 'AREQUIPA', 'LA UNION', 'QUECHUALLA', 'VELINGA', NULL, NULL),
(439, '40808', 'AREQUIPA', 'LA UNION', 'SAYLA', 'SAYLA', NULL, NULL),
(440, '40809', 'AREQUIPA', 'LA UNION', 'TAURIA', 'TAURIA', NULL, NULL),
(441, '40810', 'AREQUIPA', 'LA UNION', 'TOMEPAMPA', 'TOMEPAMPA', NULL, NULL),
(442, '40811', 'AREQUIPA', 'LA UNION', 'TORO', 'TORO', NULL, NULL),
(443, '50101', 'AYACUCHO', 'HUAMANGA', 'AYACUCHO', 'AYACUCHO', NULL, NULL),
(444, '50102', 'AYACUCHO', 'HUAMANGA', 'ACOCRO', 'ACOCRO', NULL, NULL),
(445, '50103', 'AYACUCHO', 'HUAMANGA', 'ACOS VINCHOS', 'ACOS VINCHOS', NULL, NULL),
(446, '50104', 'AYACUCHO', 'HUAMANGA', 'CARMEN ALTO', 'CARMEN ALTO', NULL, NULL),
(447, '50105', 'AYACUCHO', 'HUAMANGA', 'CHIARA', 'CHIARA', NULL, NULL),
(448, '50106', 'AYACUCHO', 'HUAMANGA', 'OCROS', 'OCROS', NULL, NULL),
(449, '50107', 'AYACUCHO', 'HUAMANGA', 'PACAYCASA', 'PACAYCASA', NULL, NULL),
(450, '50108', 'AYACUCHO', 'HUAMANGA', 'QUINUA', 'QUINUA', NULL, NULL),
(451, '50109', 'AYACUCHO', 'HUAMANGA', 'SAN JOSE DE TICLLAS', 'TICLLAS', NULL, NULL),
(452, '50110', 'AYACUCHO', 'HUAMANGA', 'SAN JUAN BAUTISTA', 'SAN JUAN BAUTISTA', NULL, NULL),
(453, '50111', 'AYACUCHO', 'HUAMANGA', 'SANTIAGO DE PISCHA', 'SAN PEDRO DE CACHI', NULL, NULL),
(454, '50112', 'AYACUCHO', 'HUAMANGA', 'SOCOS', 'SOCOS', NULL, NULL),
(455, '50113', 'AYACUCHO', 'HUAMANGA', 'TAMBILLO', 'TAMBILLO', NULL, NULL),
(456, '50114', 'AYACUCHO', 'HUAMANGA', 'VINCHOS', 'VINCHOS', NULL, NULL),
(457, '50115', 'AYACUCHO', 'HUAMANGA', 'JESUS NAZARENO', 'LAS NAZARENAS', NULL, NULL),
(458, '50116', 'AYACUCHO', 'HUAMANGA', 'ANDRES AVELINO CACERES DORREGARAY', 'JARDIN', NULL, NULL),
(459, '50201', 'AYACUCHO', 'CANGALLO', 'CANGALLO', 'CANGALLO', NULL, NULL),
(460, '50202', 'AYACUCHO', 'CANGALLO', 'CHUSCHI', 'CHUSCHI', NULL, NULL),
(461, '50203', 'AYACUCHO', 'CANGALLO', 'LOS MOROCHUCOS', 'PAMPA-CANGALLO', NULL, NULL),
(462, '50204', 'AYACUCHO', 'CANGALLO', 'MARIA PARADO DE BELLIDO', 'POMABAMBA', NULL, NULL),
(463, '50205', 'AYACUCHO', 'CANGALLO', 'PARAS', 'PARAS', NULL, NULL),
(464, '50206', 'AYACUCHO', 'CANGALLO', 'TOTOS', 'TOTOS', NULL, NULL),
(465, '50301', 'AYACUCHO', 'HUANCA SANCOS', 'SANCOS', 'HUANCA SANCOS', NULL, NULL),
(466, '50302', 'AYACUCHO', 'HUANCA SANCOS', 'CARAPO', 'CARAPO', NULL, NULL),
(467, '50303', 'AYACUCHO', 'HUANCA SANCOS', 'SACSAMARCA', 'SACSAMARCA', NULL, NULL),
(468, '50304', 'AYACUCHO', 'HUANCA SANCOS', 'SANTIAGO DE LUCANAMARCA', 'SANTIAGO DE LUCANAMARCA', NULL, NULL),
(469, '50401', 'AYACUCHO', 'HUANTA', 'HUANTA', 'HUANTA', NULL, NULL),
(470, '50402', 'AYACUCHO', 'HUANTA', 'AYAHUANCO', 'VIRACOCHAN', NULL, NULL),
(471, '50403', 'AYACUCHO', 'HUANTA', 'HUAMANGUILLA', 'HUAMANGUILLA', NULL, NULL),
(472, '50404', 'AYACUCHO', 'HUANTA', 'IGUAIN', 'MACACHACRA', NULL, NULL),
(473, '50405', 'AYACUCHO', 'HUANTA', 'LURICOCHA', 'LURICOCHA', NULL, NULL),
(474, '50406', 'AYACUCHO', 'HUANTA', 'SANTILLANA', 'SAN JOSE DE SECCE', NULL, NULL),
(475, '50407', 'AYACUCHO', 'HUANTA', 'SIVIA', 'SIVIA', NULL, NULL),
(476, '50408', 'AYACUCHO', 'HUANTA', 'LLOCHEGUA', 'LLOCHEGUA', NULL, NULL),
(477, '50409', 'AYACUCHO', 'HUANTA', 'CANAYRE', 'CANAYRE', NULL, NULL),
(478, '50410', 'AYACUCHO', 'HUANTA', 'UCHURACCAY', 'HUAYNACANCHA', NULL, NULL),
(479, '50411', 'AYACUCHO', 'HUANTA', 'PUCACOLPA', 'HUALLHUA', NULL, NULL),
(480, '50412', 'AYACUCHO', 'HUANTA', 'CHACA', 'CHACA', NULL, NULL),
(481, '50501', 'AYACUCHO', 'LA MAR', 'SAN MIGUEL', 'SAN MIGUEL', NULL, NULL),
(482, '50502', 'AYACUCHO', 'LA MAR', 'ANCO', 'CHIQUINTIRCA', NULL, NULL),
(483, '50503', 'AYACUCHO', 'LA MAR', 'AYNA', 'SAN FRANCISCO', NULL, NULL),
(484, '50504', 'AYACUCHO', 'LA MAR', 'CHILCAS', 'CHILCAS', NULL, NULL),
(485, '50505', 'AYACUCHO', 'LA MAR', 'CHUNGUI', 'CHUNGUI', NULL, NULL),
(486, '50506', 'AYACUCHO', 'LA MAR', 'LUIS CARRANZA', 'PAMPAS', NULL, NULL),
(487, '50507', 'AYACUCHO', 'LA MAR', 'SANTA ROSA', 'SANTA ROSA', NULL, NULL),
(488, '50508', 'AYACUCHO', 'LA MAR', 'TAMBO', 'TAMBO', NULL, NULL),
(489, '50509', 'AYACUCHO', 'LA MAR', 'SAMUGARI', 'PALMAPAMPA', NULL, NULL),
(490, '50510', 'AYACUCHO', 'LA MAR', 'ANCHIHUAY', 'ANCHIHUAY', NULL, NULL),
(491, '50511', 'AYACUCHO', 'LA MAR', 'ORONCCOY', 'ORONCCOY', NULL, NULL),
(492, '50601', 'AYACUCHO', 'LUCANAS', 'PUQUIO', 'PUQUIO', NULL, NULL),
(493, '50602', 'AYACUCHO', 'LUCANAS', 'AUCARA', 'AUCARA', NULL, NULL),
(494, '50603', 'AYACUCHO', 'LUCANAS', 'CABANA', 'CABANA', NULL, NULL),
(495, '50604', 'AYACUCHO', 'LUCANAS', 'CARMEN SALCEDO', 'ANDAMARCA', NULL, NULL),
(496, '50605', 'AYACUCHO', 'LUCANAS', 'CHAVIÑA', 'CHAVIÑA', NULL, NULL),
(497, '50606', 'AYACUCHO', 'LUCANAS', 'CHIPAO', 'CHIPAO', NULL, NULL),
(498, '50607', 'AYACUCHO', 'LUCANAS', 'HUAC-HUAS', 'HUAC-HUAS', NULL, NULL),
(499, '50608', 'AYACUCHO', 'LUCANAS', 'LARAMATE', 'LARAMATE', NULL, NULL),
(500, '50609', 'AYACUCHO', 'LUCANAS', 'LEONCIO PRADO', 'TAMBO QUEMADO', NULL, NULL),
(501, '50610', 'AYACUCHO', 'LUCANAS', 'LLAUTA', 'LLAUTA', NULL, NULL),
(502, '50611', 'AYACUCHO', 'LUCANAS', 'LUCANAS', 'LUCANAS', NULL, NULL),
(503, '50612', 'AYACUCHO', 'LUCANAS', 'OCAÑA', 'OCAÑA', NULL, NULL),
(504, '50613', 'AYACUCHO', 'LUCANAS', 'OTOCA', 'OTOCA', NULL, NULL),
(505, '50614', 'AYACUCHO', 'LUCANAS', 'SAISA', 'SAISA', NULL, NULL),
(506, '50615', 'AYACUCHO', 'LUCANAS', 'SAN CRISTOBAL', 'SAN CRISTOBAL', NULL, NULL),
(507, '50616', 'AYACUCHO', 'LUCANAS', 'SAN JUAN', 'SAN JUAN', NULL, NULL),
(508, '50617', 'AYACUCHO', 'LUCANAS', 'SAN PEDRO', 'SAN PEDRO', NULL, NULL),
(509, '50618', 'AYACUCHO', 'LUCANAS', 'SAN PEDRO DE PALCO', 'SAN PEDRO DE PALCO', NULL, NULL),
(510, '50619', 'AYACUCHO', 'LUCANAS', 'SANCOS', 'SANCOS', NULL, NULL),
(511, '50620', 'AYACUCHO', 'LUCANAS', 'SANTA ANA DE HUAYCAHUACHO', 'SANTA ANA DE HUAYCAHUACHO', NULL, NULL),
(512, '50621', 'AYACUCHO', 'LUCANAS', 'SANTA LUCIA', 'SANTA LUCIA', NULL, NULL),
(513, '50701', 'AYACUCHO', 'PARINACOCHAS', 'CORACORA', 'CORACORA', NULL, NULL),
(514, '50702', 'AYACUCHO', 'PARINACOCHAS', 'CHUMPI', 'CHUMPI', NULL, NULL),
(515, '50703', 'AYACUCHO', 'PARINACOCHAS', 'CORONEL CASTAÑEDA', 'ANISO', NULL, NULL),
(516, '50704', 'AYACUCHO', 'PARINACOCHAS', 'PACAPAUSA', 'PACAPAUSA', NULL, NULL),
(517, '50705', 'AYACUCHO', 'PARINACOCHAS', 'PULLO', 'PULLO', NULL, NULL),
(518, '50706', 'AYACUCHO', 'PARINACOCHAS', 'PUYUSCA', 'INCUYO', NULL, NULL),
(519, '50707', 'AYACUCHO', 'PARINACOCHAS', 'SAN FRANCISCO DE RAVACAYCO', 'SAN FRANCISCO DE RAVACAYCO', NULL, NULL),
(520, '50708', 'AYACUCHO', 'PARINACOCHAS', 'UPAHUACHO', 'UPAHUACHO', NULL, NULL),
(521, '50801', 'AYACUCHO', 'PAUCAR DEL SARA SARA', 'PAUSA', 'PAUSA', NULL, NULL),
(522, '50802', 'AYACUCHO', 'PAUCAR DEL SARA SARA', 'COLTA', 'COLTA', NULL, NULL),
(523, '50803', 'AYACUCHO', 'PAUCAR DEL SARA SARA', 'CORCULLA', 'CORCULLA', NULL, NULL),
(524, '50804', 'AYACUCHO', 'PAUCAR DEL SARA SARA', 'LAMPA', 'LAMPA', NULL, NULL),
(525, '50805', 'AYACUCHO', 'PAUCAR DEL SARA SARA', 'MARCABAMBA', 'MARCABAMBA', NULL, NULL),
(526, '50806', 'AYACUCHO', 'PAUCAR DEL SARA SARA', 'OYOLO', 'OYOLO', NULL, NULL),
(527, '50807', 'AYACUCHO', 'PAUCAR DEL SARA SARA', 'PARARCA', 'PARARCA', NULL, NULL),
(528, '50808', 'AYACUCHO', 'PAUCAR DEL SARA SARA', 'SAN JAVIER DE ALPABAMBA', 'SAN JAVIER DE ALPABAMBA', NULL, NULL),
(529, '50809', 'AYACUCHO', 'PAUCAR DEL SARA SARA', 'SAN JOSE DE USHUA', 'SAN JOSE DE USHUA', NULL, NULL),
(530, '50810', 'AYACUCHO', 'PAUCAR DEL SARA SARA', 'SARA SARA', 'QUILCATA', NULL, NULL),
(531, '50901', 'AYACUCHO', 'SUCRE', 'QUEROBAMBA', 'QUEROBAMBA', NULL, NULL),
(532, '50902', 'AYACUCHO', 'SUCRE', 'BELEN', 'BELEN', NULL, NULL),
(533, '50903', 'AYACUCHO', 'SUCRE', 'CHALCOS', 'CHALCOS', NULL, NULL),
(534, '50904', 'AYACUCHO', 'SUCRE', 'CHILCAYOC', 'CHILCAYOC', NULL, NULL),
(535, '50905', 'AYACUCHO', 'SUCRE', 'HUACAÑA', 'HUACAÑA', NULL, NULL),
(536, '50906', 'AYACUCHO', 'SUCRE', 'MORCOLLA', 'MORCOLLA', NULL, NULL),
(537, '50907', 'AYACUCHO', 'SUCRE', 'PAICO', 'PAICO', NULL, NULL),
(538, '50908', 'AYACUCHO', 'SUCRE', 'SAN PEDRO DE LARCAY', 'SAN PEDRO DE LARCAY', NULL, NULL),
(539, '50909', 'AYACUCHO', 'SUCRE', 'SAN SALVADOR DE QUIJE', 'SAN SALVADOR DE QUIJE', NULL, NULL),
(540, '50910', 'AYACUCHO', 'SUCRE', 'SANTIAGO DE PAUCARAY', 'SANTIAGO DE PAUCARAY', NULL, NULL),
(541, '50911', 'AYACUCHO', 'SUCRE', 'SORAS', 'SORAS', NULL, NULL),
(542, '51001', 'AYACUCHO', 'VICTOR FAJARDO', 'HUANCAPI', 'HUANCAPI', NULL, NULL),
(543, '51002', 'AYACUCHO', 'VICTOR FAJARDO', 'ALCAMENCA', 'ALCAMENCA', NULL, NULL),
(544, '51003', 'AYACUCHO', 'VICTOR FAJARDO', 'APONGO', 'APONGO', NULL, NULL),
(545, '51004', 'AYACUCHO', 'VICTOR FAJARDO', 'ASQUIPATA', 'ASQUIPATA', NULL, NULL),
(546, '51005', 'AYACUCHO', 'VICTOR FAJARDO', 'CANARIA', 'CANARIA', NULL, NULL),
(547, '51006', 'AYACUCHO', 'VICTOR FAJARDO', 'CAYARA', 'CAYARA', NULL, NULL),
(548, '51007', 'AYACUCHO', 'VICTOR FAJARDO', 'COLCA', 'COLCA', NULL, NULL),
(549, '51008', 'AYACUCHO', 'VICTOR FAJARDO', 'HUAMANQUIQUIA', 'HUAMANQUIQUIA', NULL, NULL),
(550, '51009', 'AYACUCHO', 'VICTOR FAJARDO', 'HUANCARAYLLA', 'HUANCARAYLLA', NULL, NULL),
(551, '51010', 'AYACUCHO', 'VICTOR FAJARDO', 'HUALLA', 'SAN PEDRO DE HUALLA', NULL, NULL),
(552, '51011', 'AYACUCHO', 'VICTOR FAJARDO', 'SARHUA', 'SARHUA', NULL, NULL),
(553, '51012', 'AYACUCHO', 'VICTOR FAJARDO', 'VILCANCHOS', 'VILCANCHOS', NULL, NULL),
(554, '51101', 'AYACUCHO', 'VILCAS HUAMAN', 'VILCAS HUAMAN', 'VILCAS HUAMAN', NULL, NULL),
(555, '51102', 'AYACUCHO', 'VILCAS HUAMAN', 'ACCOMARCA', 'ACCOMARCA', NULL, NULL),
(556, '51103', 'AYACUCHO', 'VILCAS HUAMAN', 'CARHUANCA', 'CARHUANCA', NULL, NULL),
(557, '51104', 'AYACUCHO', 'VILCAS HUAMAN', 'CONCEPCION', 'CONCEPCION', NULL, NULL),
(558, '51105', 'AYACUCHO', 'VILCAS HUAMAN', 'HUAMBALPA', 'HUAMBALPA', NULL, NULL),
(559, '51106', 'AYACUCHO', 'VILCAS HUAMAN', 'INDEPENDENCIA', 'PACCHA HUALLHUA /4', NULL, NULL),
(560, '51107', 'AYACUCHO', 'VILCAS HUAMAN', 'SAURAMA', 'SAURAMA', NULL, NULL),
(561, '51108', 'AYACUCHO', 'VILCAS HUAMAN', 'VISCHONGO', 'VISCHONGO', NULL, NULL),
(562, '60101', 'CAJAMARCA', 'CAJAMARCA', 'CAJAMARCA', 'CAJAMARCA', NULL, NULL),
(563, '60102', 'CAJAMARCA', 'CAJAMARCA', 'ASUNCION', 'ASUNCION', NULL, NULL),
(564, '60103', 'CAJAMARCA', 'CAJAMARCA', 'CHETILLA', 'CHETILLA', NULL, NULL),
(565, '60104', 'CAJAMARCA', 'CAJAMARCA', 'COSPAN', 'COSPAN', NULL, NULL),
(566, '60105', 'CAJAMARCA', 'CAJAMARCA', 'ENCAÑADA', 'ENCAÑADA', NULL, NULL),
(567, '60106', 'CAJAMARCA', 'CAJAMARCA', 'JESUS', 'JESUS', NULL, NULL),
(568, '60107', 'CAJAMARCA', 'CAJAMARCA', 'LLACANORA', 'LLACANORA', NULL, NULL),
(569, '60108', 'CAJAMARCA', 'CAJAMARCA', 'LOS BAÑOS DEL INCA', 'LOS BAÑOS DEL INCA', NULL, NULL),
(570, '60109', 'CAJAMARCA', 'CAJAMARCA', 'MAGDALENA', 'MAGDALENA', NULL, NULL),
(571, '60110', 'CAJAMARCA', 'CAJAMARCA', 'MATARA', 'MATARA', NULL, NULL),
(572, '60111', 'CAJAMARCA', 'CAJAMARCA', 'NAMORA', 'NAMORA', NULL, NULL),
(573, '60112', 'CAJAMARCA', 'CAJAMARCA', 'SAN JUAN', 'SAN JUAN', NULL, NULL),
(574, '60201', 'CAJAMARCA', 'CAJABAMBA', 'CAJABAMBA', 'CAJABAMBA', NULL, NULL),
(575, '60202', 'CAJAMARCA', 'CAJABAMBA', 'CACHACHI', 'CACHACHI', NULL, NULL),
(576, '60203', 'CAJAMARCA', 'CAJABAMBA', 'CONDEBAMBA', 'CAUDAY', NULL, NULL),
(577, '60204', 'CAJAMARCA', 'CAJABAMBA', 'SITACOCHA', 'LLUCHUBAMBA', NULL, NULL),
(578, '60301', 'CAJAMARCA', 'CELENDIN', 'CELENDIN', 'CELENDIN', NULL, NULL),
(579, '60302', 'CAJAMARCA', 'CELENDIN', 'CHUMUCH', 'CHUMUCH', NULL, NULL),
(580, '60303', 'CAJAMARCA', 'CELENDIN', 'CORTEGANA', 'CHIMUCH (CORTEGANA)', NULL, NULL),
(581, '60304', 'CAJAMARCA', 'CELENDIN', 'HUASMIN', 'HUASMIN', NULL, NULL),
(582, '60305', 'CAJAMARCA', 'CELENDIN', 'JORGE CHAVEZ', 'LUCMAPAMPA', NULL, NULL),
(583, '60306', 'CAJAMARCA', 'CELENDIN', 'JOSE GALVEZ', 'HUACAPAMPA', NULL, NULL),
(584, '60307', 'CAJAMARCA', 'CELENDIN', 'MIGUEL IGLESIAS', 'CHALAN', NULL, NULL),
(585, '60308', 'CAJAMARCA', 'CELENDIN', 'OXAMARCA', 'OXAMARCA', NULL, NULL),
(586, '60309', 'CAJAMARCA', 'CELENDIN', 'SOROCHUCO', 'SOROCHUCO', NULL, NULL),
(587, '60310', 'CAJAMARCA', 'CELENDIN', 'SUCRE', 'SUCRE', NULL, NULL),
(588, '60311', 'CAJAMARCA', 'CELENDIN', 'UTCO', 'UTCO', NULL, NULL),
(589, '60312', 'CAJAMARCA', 'CELENDIN', 'LA LIBERTAD DE PALLAN', 'LA LIBERTAD DE PALLAN', NULL, NULL),
(590, '60401', 'CAJAMARCA', 'CHOTA', 'CHOTA', 'CHOTA', NULL, NULL),
(591, '60402', 'CAJAMARCA', 'CHOTA', 'ANGUIA', 'ANGUIA', NULL, NULL),
(592, '60403', 'CAJAMARCA', 'CHOTA', 'CHADIN', 'CHADIN', NULL, NULL),
(593, '60404', 'CAJAMARCA', 'CHOTA', 'CHIGUIRIP', 'CHIGUIRIP', NULL, NULL),
(594, '60405', 'CAJAMARCA', 'CHOTA', 'CHIMBAN', 'CHIMBAN', NULL, NULL),
(595, '60406', 'CAJAMARCA', 'CHOTA', 'CHOROPAMPA', 'CHOROPAMPA', NULL, NULL),
(596, '60407', 'CAJAMARCA', 'CHOTA', 'COCHABAMBA', 'COCHABAMBA', NULL, NULL),
(597, '60408', 'CAJAMARCA', 'CHOTA', 'CONCHAN', 'CONCHAN', NULL, NULL),
(598, '60409', 'CAJAMARCA', 'CHOTA', 'HUAMBOS', 'HUAMBOS', NULL, NULL),
(599, '60410', 'CAJAMARCA', 'CHOTA', 'LAJAS', 'LAJAS', NULL, NULL),
(600, '60411', 'CAJAMARCA', 'CHOTA', 'LLAMA', 'LLAMA', NULL, NULL),
(601, '60412', 'CAJAMARCA', 'CHOTA', 'MIRACOSTA', 'MIRACOSTA', NULL, NULL),
(602, '60413', 'CAJAMARCA', 'CHOTA', 'PACCHA', 'PACCHA', NULL, NULL),
(603, '60414', 'CAJAMARCA', 'CHOTA', 'PION', 'PION', NULL, NULL),
(604, '60415', 'CAJAMARCA', 'CHOTA', 'QUEROCOTO', 'QUEROCOTO', NULL, NULL),
(605, '60416', 'CAJAMARCA', 'CHOTA', 'SAN JUAN DE LICUPIS', 'LICUPIS', NULL, NULL),
(606, '60417', 'CAJAMARCA', 'CHOTA', 'TACABAMBA', 'TACABAMBA', NULL, NULL),
(607, '60418', 'CAJAMARCA', 'CHOTA', 'TOCMOCHE', 'TOCMOCHE', NULL, NULL),
(608, '60419', 'CAJAMARCA', 'CHOTA', 'CHALAMARCA', 'CHALAMARCA', NULL, NULL),
(609, '60501', 'CAJAMARCA', 'CONTUMAZA', 'CONTUMAZA', 'CONTUMAZA', NULL, NULL),
(610, '60502', 'CAJAMARCA', 'CONTUMAZA', 'CHILETE', 'CHILETE', NULL, NULL),
(611, '60503', 'CAJAMARCA', 'CONTUMAZA', 'CUPISNIQUE', 'TRINIDAD', NULL, NULL),
(612, '60504', 'CAJAMARCA', 'CONTUMAZA', 'GUZMANGO', 'GUZMANGO', NULL, NULL),
(613, '60505', 'CAJAMARCA', 'CONTUMAZA', 'SAN BENITO', 'SAN BENITO', NULL, NULL),
(614, '60506', 'CAJAMARCA', 'CONTUMAZA', 'SANTA CRUZ DE TOLED', 'SANTA CRUZ DE TOLED', NULL, NULL),
(615, '60507', 'CAJAMARCA', 'CONTUMAZA', 'TANTARICA', 'CATAN', NULL, NULL),
(616, '60508', 'CAJAMARCA', 'CONTUMAZA', 'YONAN', 'TEMBLADERA', NULL, NULL),
(617, '60601', 'CAJAMARCA', 'CUTERVO', 'CUTERVO', 'CUTERVO', NULL, NULL),
(618, '60602', 'CAJAMARCA', 'CUTERVO', 'CALLAYUC', 'CALLAYUC', NULL, NULL),
(619, '60603', 'CAJAMARCA', 'CUTERVO', 'CHOROS', 'CHOROS', NULL, NULL),
(620, '60604', 'CAJAMARCA', 'CUTERVO', 'CUJILLO', 'CUJILLO', NULL, NULL),
(621, '60605', 'CAJAMARCA', 'CUTERVO', 'LA RAMADA', 'LA RAMADA', NULL, NULL),
(622, '60606', 'CAJAMARCA', 'CUTERVO', 'PIMPINGOS', 'PIMPINGOS', NULL, NULL),
(623, '60607', 'CAJAMARCA', 'CUTERVO', 'QUEROCOTILLO', 'QUEROCOTILLO', NULL, NULL),
(624, '60608', 'CAJAMARCA', 'CUTERVO', 'SAN ANDRES DE CUTERVO', 'SAN ANDRES DE CUTERVO', NULL, NULL),
(625, '60609', 'CAJAMARCA', 'CUTERVO', 'SAN JUAN DE CUTERVO', 'SAN JUAN DE CUTERVO', NULL, NULL),
(626, '60610', 'CAJAMARCA', 'CUTERVO', 'SAN LUIS DE LUCMA', 'SAN LUIS DE LUCMA', NULL, NULL),
(627, '60611', 'CAJAMARCA', 'CUTERVO', 'SANTA CRUZ', 'SANTA CRUZ', NULL, NULL),
(628, '60612', 'CAJAMARCA', 'CUTERVO', 'SANTO DOMINGO DE LA CAPILLA', 'SANTO DOMINGO DE LA CAPILLA', NULL, NULL),
(629, '60613', 'CAJAMARCA', 'CUTERVO', 'SANTO TOMAS', 'SANTO TOMAS', NULL, NULL),
(630, '60614', 'CAJAMARCA', 'CUTERVO', 'SOCOTA', 'SOCOTA', NULL, NULL),
(631, '60615', 'CAJAMARCA', 'CUTERVO', 'TORIBIO CASANOVA', 'LA SACILIA', NULL, NULL),
(632, '60701', 'CAJAMARCA', 'HUALGAYOC', 'BAMBAMARCA', 'BAMBAMARCA', NULL, NULL),
(633, '60702', 'CAJAMARCA', 'HUALGAYOC', 'CHUGUR', 'CHUGUR', NULL, NULL),
(634, '60703', 'CAJAMARCA', 'HUALGAYOC', 'HUALGAYOC', 'HUALGAYOC', NULL, NULL),
(635, '60801', 'CAJAMARCA', 'JAEN', 'JAEN', 'JAEN', NULL, NULL),
(636, '60802', 'CAJAMARCA', 'JAEN', 'BELLAVISTA', 'BELLAVISTA', NULL, NULL),
(637, '60803', 'CAJAMARCA', 'JAEN', 'CHONTALI', 'CHONTALI', NULL, NULL),
(638, '60804', 'CAJAMARCA', 'JAEN', 'COLASAY', 'COLASAY', NULL, NULL),
(639, '60805', 'CAJAMARCA', 'JAEN', 'HUABAL', 'HUABAL', NULL, NULL),
(640, '60806', 'CAJAMARCA', 'JAEN', 'LAS PIRIAS', 'LAS PIRIAS', NULL, NULL),
(641, '60807', 'CAJAMARCA', 'JAEN', 'POMAHUACA', 'POMAHUACA', NULL, NULL),
(642, '60808', 'CAJAMARCA', 'JAEN', 'PUCARA', 'PUCARA', NULL, NULL),
(643, '60809', 'CAJAMARCA', 'JAEN', 'SALLIQUE', 'SALLIQUE', NULL, NULL),
(644, '60810', 'CAJAMARCA', 'JAEN', 'SAN FELIPE', 'SAN FELIPE', NULL, NULL),
(645, '60811', 'CAJAMARCA', 'JAEN', 'SAN JOSE DEL ALTO', 'SAN JOSE DEL ALTO', NULL, NULL),
(646, '60812', 'CAJAMARCA', 'JAEN', 'SANTA ROSA', 'SANTA ROSA', NULL, NULL),
(647, '60901', 'CAJAMARCA', 'SAN IGNACIO', 'SAN IGNACIO', 'SAN IGNACIO', NULL, NULL),
(648, '60902', 'CAJAMARCA', 'SAN IGNACIO', 'CHIRINOS', 'CHIRINOS', NULL, NULL),
(649, '60903', 'CAJAMARCA', 'SAN IGNACIO', 'HUARANGO', 'HUARANGO', NULL, NULL),
(650, '60904', 'CAJAMARCA', 'SAN IGNACIO', 'LA COIPA', 'LA COIPA', NULL, NULL),
(651, '60905', 'CAJAMARCA', 'SAN IGNACIO', 'NAMBALLE', 'NAMBALLE', NULL, NULL),
(652, '60906', 'CAJAMARCA', 'SAN IGNACIO', 'SAN JOSE DE LOURDES', 'SAN JOSE DE LOURDES', NULL, NULL),
(653, '60907', 'CAJAMARCA', 'SAN IGNACIO', 'TABACONAS', 'TABACONAS', NULL, NULL),
(654, '61001', 'CAJAMARCA', 'SAN MARCOS', 'PEDRO GALVEZ', 'SAN MARCOS', NULL, NULL),
(655, '61002', 'CAJAMARCA', 'SAN MARCOS', 'CHANCAY', 'CHANCAY', NULL, NULL),
(656, '61003', 'CAJAMARCA', 'SAN MARCOS', 'EDUARDO VILLANUEVA', 'LA GRAMA', NULL, NULL),
(657, '61004', 'CAJAMARCA', 'SAN MARCOS', 'GREGORIO PITA', 'PAUCAMARCA', NULL, NULL),
(658, '61005', 'CAJAMARCA', 'SAN MARCOS', 'ICHOCAN', 'ICHOCAN', NULL, NULL),
(659, '61006', 'CAJAMARCA', 'SAN MARCOS', 'JOSE MANUEL QUIROZ', 'SHIRAC', NULL, NULL);
INSERT INTO `ubigeos` (`id_ubigeo`, `ubigeo_cod`, `ubigeo_departamento`, `ubigeo_provincia`, `ubigeo_distrito`, `ubigeo_capital`, `created_at`, `updated_at`) VALUES
(660, '61007', 'CAJAMARCA', 'SAN MARCOS', 'JOSE SABOGAL', 'VENECIA', NULL, NULL),
(661, '61101', 'CAJAMARCA', 'SAN MIGUEL', 'SAN MIGUEL', 'SAN MIGUEL DE PALLAQUES', NULL, NULL),
(662, '61102', 'CAJAMARCA', 'SAN MIGUEL', 'BOLIVAR', 'BOLIVAR', NULL, NULL),
(663, '61103', 'CAJAMARCA', 'SAN MIGUEL', 'CALQUIS', 'CALQUIS', NULL, NULL),
(664, '61104', 'CAJAMARCA', 'SAN MIGUEL', 'CATILLUC', 'CATILLUC', NULL, NULL),
(665, '61105', 'CAJAMARCA', 'SAN MIGUEL', 'EL PRADO', 'EL PRADO', NULL, NULL),
(666, '61106', 'CAJAMARCA', 'SAN MIGUEL', 'LA FLORIDA', 'LA FLORIDA', NULL, NULL),
(667, '61107', 'CAJAMARCA', 'SAN MIGUEL', 'LLAPA', 'LLAPA', NULL, NULL),
(668, '61108', 'CAJAMARCA', 'SAN MIGUEL', 'NANCHOC', 'NANCHOC', NULL, NULL),
(669, '61109', 'CAJAMARCA', 'SAN MIGUEL', 'NIEPOS', 'NIEPOS', NULL, NULL),
(670, '61110', 'CAJAMARCA', 'SAN MIGUEL', 'SAN GREGORIO', 'SAN GREGORIO', NULL, NULL),
(671, '61111', 'CAJAMARCA', 'SAN MIGUEL', 'SAN SILVESTRE DE COCHAN', 'SAN SILVESTRE DE COCHAN', NULL, NULL),
(672, '61112', 'CAJAMARCA', 'SAN MIGUEL', 'TONGOD', 'TONGOD', NULL, NULL),
(673, '61113', 'CAJAMARCA', 'SAN MIGUEL', 'UNION AGUA BLANCA', 'AGUA BLANCA', NULL, NULL),
(674, '61201', 'CAJAMARCA', 'SAN PABLO', 'SAN PABLO', 'SAN PABLO', NULL, NULL),
(675, '61202', 'CAJAMARCA', 'SAN PABLO', 'SAN BERNARDINO', 'SAN BERNARDINO', NULL, NULL),
(676, '61203', 'CAJAMARCA', 'SAN PABLO', 'SAN LUIS', 'SAN LUIS GRANDE', NULL, NULL),
(677, '61204', 'CAJAMARCA', 'SAN PABLO', 'TUMBADEN', 'TUMBADEN', NULL, NULL),
(678, '61301', 'CAJAMARCA', 'SANTA CRUZ', 'SANTA CRUZ', 'SANTA CRUZ DE SUCCHABAMBA', NULL, NULL),
(679, '61302', 'CAJAMARCA', 'SANTA CRUZ', 'ANDABAMBA', 'ANDABAMBA', NULL, NULL),
(680, '61303', 'CAJAMARCA', 'SANTA CRUZ', 'CATACHE', 'CATACHE', NULL, NULL),
(681, '61304', 'CAJAMARCA', 'SANTA CRUZ', 'CHANCAYBAÑOS', 'CHANCAYBAÑOS', NULL, NULL),
(682, '61305', 'CAJAMARCA', 'SANTA CRUZ', 'LA ESPERANZA', 'LA ESPERANZA', NULL, NULL),
(683, '61306', 'CAJAMARCA', 'SANTA CRUZ', 'NINABAMBA', 'NINABAMBA', NULL, NULL),
(684, '61307', 'CAJAMARCA', 'SANTA CRUZ', 'PULAN', 'PULAN', NULL, NULL),
(685, '61308', 'CAJAMARCA', 'SANTA CRUZ', 'SAUCEPAMPA', 'SAUCEPAMPA', NULL, NULL),
(686, '61309', 'CAJAMARCA', 'SANTA CRUZ', 'SEXI', 'SEXI', NULL, NULL),
(687, '61310', 'CAJAMARCA', 'SANTA CRUZ', 'UTICYACU', 'UTICYACU', NULL, NULL),
(688, '61311', 'CAJAMARCA', 'SANTA CRUZ', 'YAUYUCAN', 'YAUYUCAN', NULL, NULL),
(689, '70101', 'CALLAO', 'CALLAO', 'CALLAO', 'CALLAO', NULL, NULL),
(690, '70102', 'CALLAO', 'CALLAO', 'BELLAVISTA', 'BELLAVISTA', NULL, NULL),
(691, '70103', 'CALLAO', 'CALLAO', 'CARMEN DE LA LEGUA REYNOSO', 'CARMEN DE LA LEGUA REYNOSO', NULL, NULL),
(692, '70104', 'CALLAO', 'CALLAO', 'LA PERLA', 'LA PERLA', NULL, NULL),
(693, '70105', 'CALLAO', 'CALLAO', 'LA PUNTA', 'LA PUNTA', NULL, NULL),
(694, '70106', 'CALLAO', 'CALLAO', 'VENTANILLA', 'VENTANILLA', NULL, NULL),
(695, '70107', 'CALLAO', 'CALLAO', 'MI PERU', 'MI PERU', NULL, NULL),
(696, '80101', 'CUSCO', 'CUSCO', 'CUSCO', 'CUSCO', NULL, NULL),
(697, '80102', 'CUSCO', 'CUSCO', 'CCORCA', 'CCORCA', NULL, NULL),
(698, '80103', 'CUSCO', 'CUSCO', 'POROY', 'POROY', NULL, NULL),
(699, '80104', 'CUSCO', 'CUSCO', 'SAN JERONIMO', 'SAN JERONIMO', NULL, NULL),
(700, '80105', 'CUSCO', 'CUSCO', 'SAN SEBASTIAN', 'SAN SEBASTIAN', NULL, NULL),
(701, '80106', 'CUSCO', 'CUSCO', 'SANTIAGO', 'SANTIAGO', NULL, NULL),
(702, '80107', 'CUSCO', 'CUSCO', 'SAYLLA', 'SAYLLA', NULL, NULL),
(703, '80108', 'CUSCO', 'CUSCO', 'WANCHAQ', 'WANCHAQ', NULL, NULL),
(704, '80201', 'CUSCO', 'ACOMAYO', 'ACOMAYO', 'ACOMAYO', NULL, NULL),
(705, '80202', 'CUSCO', 'ACOMAYO', 'ACOPIA', 'ACOPIA', NULL, NULL),
(706, '80203', 'CUSCO', 'ACOMAYO', 'ACOS', 'ACOS', NULL, NULL),
(707, '80204', 'CUSCO', 'ACOMAYO', 'MOSOC LLACTA', 'MOSOC LLACTA', NULL, NULL),
(708, '80205', 'CUSCO', 'ACOMAYO', 'POMACANCHI', 'POMACANCHI', NULL, NULL),
(709, '80206', 'CUSCO', 'ACOMAYO', 'RONDOCAN', 'RONDOCAN', NULL, NULL),
(710, '80207', 'CUSCO', 'ACOMAYO', 'SANGARARA', 'SANGARARA', NULL, NULL),
(711, '80301', 'CUSCO', 'ANTA', 'ANTA', 'ANTA', NULL, NULL),
(712, '80302', 'CUSCO', 'ANTA', 'ANCAHUASI', 'ANCAHUASI', NULL, NULL),
(713, '80303', 'CUSCO', 'ANTA', 'CACHIMAYO', 'CACHIMAYO', NULL, NULL),
(714, '80304', 'CUSCO', 'ANTA', 'CHINCHAYPUJIO', 'CHINCHAYPUJIO', NULL, NULL),
(715, '80305', 'CUSCO', 'ANTA', 'HUAROCONDO', 'HUAROCONDO', NULL, NULL),
(716, '80306', 'CUSCO', 'ANTA', 'LIMATAMBO', 'LIMATAMBO', NULL, NULL),
(717, '80307', 'CUSCO', 'ANTA', 'MOLLEPATA', 'MOLLEPATA', NULL, NULL),
(718, '80308', 'CUSCO', 'ANTA', 'PUCYURA', 'PUCYURA', NULL, NULL),
(719, '80309', 'CUSCO', 'ANTA', 'ZURITE', 'ZURITE', NULL, NULL),
(720, '80401', 'CUSCO', 'CALCA', 'CALCA', 'CALCA', NULL, NULL),
(721, '80402', 'CUSCO', 'CALCA', 'COYA', 'COYA', NULL, NULL),
(722, '80403', 'CUSCO', 'CALCA', 'LAMAY', 'LAMAY', NULL, NULL),
(723, '80404', 'CUSCO', 'CALCA', 'LARES', 'LARES', NULL, NULL),
(724, '80405', 'CUSCO', 'CALCA', 'PISAC', 'PISAC', NULL, NULL),
(725, '80406', 'CUSCO', 'CALCA', 'SAN SALVADOR', 'SAN SALVADOR', NULL, NULL),
(726, '80407', 'CUSCO', 'CALCA', 'TARAY', 'TARAY', NULL, NULL),
(727, '80408', 'CUSCO', 'CALCA', 'YANATILE', 'QUEBRADA HONDA', NULL, NULL),
(728, '80501', 'CUSCO', 'CANAS', 'YANAOCA', 'YANAOCA', NULL, NULL),
(729, '80502', 'CUSCO', 'CANAS', 'CHECCA', 'CHECCA', NULL, NULL),
(730, '80503', 'CUSCO', 'CANAS', 'KUNTURKANKI', 'EL DESCANSO', NULL, NULL),
(731, '80504', 'CUSCO', 'CANAS', 'LANGUI', 'LANGUI', NULL, NULL),
(732, '80505', 'CUSCO', 'CANAS', 'LAYO', 'LAYO', NULL, NULL),
(733, '80506', 'CUSCO', 'CANAS', 'PAMPAMARCA', 'PAMPAMARCA', NULL, NULL),
(734, '80507', 'CUSCO', 'CANAS', 'QUEHUE', 'QUEHUE', NULL, NULL),
(735, '80508', 'CUSCO', 'CANAS', 'TUPAC AMARU', 'TUNGASUCA', NULL, NULL),
(736, '80601', 'CUSCO', 'CANCHIS', 'SICUANI', 'SICUANI', NULL, NULL),
(737, '80602', 'CUSCO', 'CANCHIS', 'CHECACUPE', 'CHECACUPE', NULL, NULL),
(738, '80603', 'CUSCO', 'CANCHIS', 'COMBAPATA', 'COMBAPATA', NULL, NULL),
(739, '80604', 'CUSCO', 'CANCHIS', 'MARANGANI', 'MARANGANI', NULL, NULL),
(740, '80605', 'CUSCO', 'CANCHIS', 'PITUMARCA', 'PITUMARCA', NULL, NULL),
(741, '80606', 'CUSCO', 'CANCHIS', 'SAN PABLO', 'SAN PABLO', NULL, NULL),
(742, '80607', 'CUSCO', 'CANCHIS', 'SAN PEDRO', 'SAN PEDRO', NULL, NULL),
(743, '80608', 'CUSCO', 'CANCHIS', 'TINTA', 'TINTA', NULL, NULL),
(744, '80701', 'CUSCO', 'CHUMBIVILCAS', 'SANTO TOMAS', 'SANTO TOMAS', NULL, NULL),
(745, '80702', 'CUSCO', 'CHUMBIVILCAS', 'CAPACMARCA', 'CAPACMARCA', NULL, NULL),
(746, '80703', 'CUSCO', 'CHUMBIVILCAS', 'CHAMACA', 'CHAMACA', NULL, NULL),
(747, '80704', 'CUSCO', 'CHUMBIVILCAS', 'COLQUEMARCA', 'COLQUEMARCA', NULL, NULL),
(748, '80705', 'CUSCO', 'CHUMBIVILCAS', 'LIVITACA', 'LIVITACA', NULL, NULL),
(749, '80706', 'CUSCO', 'CHUMBIVILCAS', 'LLUSCO', 'LLUSCO', NULL, NULL),
(750, '80707', 'CUSCO', 'CHUMBIVILCAS', 'QUIÑOTA', 'QUIÑOTA', NULL, NULL),
(751, '80708', 'CUSCO', 'CHUMBIVILCAS', 'VELILLE', 'VELILLE', NULL, NULL),
(752, '80801', 'CUSCO', 'ESPINAR', 'ESPINAR', 'YAURI', NULL, NULL),
(753, '80802', 'CUSCO', 'ESPINAR', 'CONDOROMA', 'CONDOROMA', NULL, NULL),
(754, '80803', 'CUSCO', 'ESPINAR', 'COPORAQUE', 'COPORAQUE', NULL, NULL),
(755, '80804', 'CUSCO', 'ESPINAR', 'OCORURO', 'OCORURO', NULL, NULL),
(756, '80805', 'CUSCO', 'ESPINAR', 'PALLPATA', 'HECTOR TEJADA', NULL, NULL),
(757, '80806', 'CUSCO', 'ESPINAR', 'PICHIGUA', 'PICHIGUA', NULL, NULL),
(758, '80807', 'CUSCO', 'ESPINAR', 'SUYCKUTAMBO', 'SUYCKUTAMBO /5', NULL, NULL),
(759, '80808', 'CUSCO', 'ESPINAR', 'ALTO PICHIGUA', 'ACCOCUNCA', NULL, NULL),
(760, '80901', 'CUSCO', 'LA CONVENCION', 'SANTA ANA', 'QUILLABAMBA', NULL, NULL),
(761, '80902', 'CUSCO', 'LA CONVENCION', 'ECHARATE', 'ECHARATE', NULL, NULL),
(762, '80903', 'CUSCO', 'LA CONVENCION', 'HUAYOPATA', 'IPAL /6', NULL, NULL),
(763, '80904', 'CUSCO', 'LA CONVENCION', 'MARANURA', 'MARANURA', NULL, NULL),
(764, '80905', 'CUSCO', 'LA CONVENCION', 'OCOBAMBA', 'OCOBAMBA /7', NULL, NULL),
(765, '80906', 'CUSCO', 'LA CONVENCION', 'QUELLOUNO', 'QUELLOUNO', NULL, NULL),
(766, '80907', 'CUSCO', 'LA CONVENCION', 'KIMBIRI', 'KIMBIRI', NULL, NULL),
(767, '80908', 'CUSCO', 'LA CONVENCION', 'SANTA TERESA', 'SANTA TERESA', NULL, NULL),
(768, '80909', 'CUSCO', 'LA CONVENCION', 'VILCABAMBA', 'LUCMA', NULL, NULL),
(769, '80910', 'CUSCO', 'LA CONVENCION', 'PICHARI', 'PICHARI', NULL, NULL),
(770, '80911', 'CUSCO', 'LA CONVENCION', 'INKAWASI', 'AMAYBAMBA', NULL, NULL),
(771, '80912', 'CUSCO', 'LA CONVENCION', 'VILLA VIRGEN', 'VILLA VIRGEN', NULL, NULL),
(772, '80913', 'CUSCO', 'LA CONVENCION', 'VILLA KINTIARINA', 'VILLA KINTIARINA', NULL, NULL),
(773, '80914', 'CUSCO', 'LA CONVENCION', 'MEGANTONI', 'CAMISEA', NULL, NULL),
(774, '81001', 'CUSCO', 'PARURO', 'PARURO', 'PARURO', NULL, NULL),
(775, '81002', 'CUSCO', 'PARURO', 'ACCHA', 'ACCHA', NULL, NULL),
(776, '81003', 'CUSCO', 'PARURO', 'CCAPI', 'CCAPI', NULL, NULL),
(777, '81004', 'CUSCO', 'PARURO', 'COLCHA', 'COLCHA', NULL, NULL),
(778, '81005', 'CUSCO', 'PARURO', 'HUANOQUITE', 'HUANOQUITE', NULL, NULL),
(779, '81006', 'CUSCO', 'PARURO', 'OMACHA', 'OMACHA', NULL, NULL),
(780, '81007', 'CUSCO', 'PARURO', 'PACCARITAMBO', 'PACCARITAMBO', NULL, NULL),
(781, '81008', 'CUSCO', 'PARURO', 'PILLPINTO', 'PILLPINTO', NULL, NULL),
(782, '81009', 'CUSCO', 'PARURO', 'YAURISQUE', 'YAURISQUE', NULL, NULL),
(783, '81101', 'CUSCO', 'PAUCARTAMBO', 'PAUCARTAMBO', 'PAUCARTAMBO', NULL, NULL),
(784, '81102', 'CUSCO', 'PAUCARTAMBO', 'CAICAY', 'CAICAY', NULL, NULL),
(785, '81103', 'CUSCO', 'PAUCARTAMBO', 'CHALLABAMBA', 'CHALLABAMBA', NULL, NULL),
(786, '81104', 'CUSCO', 'PAUCARTAMBO', 'COLQUEPATA', 'COLQUEPATA', NULL, NULL),
(787, '81105', 'CUSCO', 'PAUCARTAMBO', 'HUANCARANI', 'HUANCARANI', NULL, NULL),
(788, '81106', 'CUSCO', 'PAUCARTAMBO', 'KOSÑIPATA', 'PILLCOPATA', NULL, NULL),
(789, '81201', 'CUSCO', 'QUISPICANCHI', 'URCOS', 'URCOS', NULL, NULL),
(790, '81202', 'CUSCO', 'QUISPICANCHI', 'ANDAHUAYLILLAS', 'ANDAHUAYLILLAS', NULL, NULL),
(791, '81203', 'CUSCO', 'QUISPICANCHI', 'CAMANTI', 'QUINCE MIL', NULL, NULL),
(792, '81204', 'CUSCO', 'QUISPICANCHI', 'CCARHUAYO', 'CCARHUAYO', NULL, NULL),
(793, '81205', 'CUSCO', 'QUISPICANCHI', 'CCATCA', 'CCATCA', NULL, NULL),
(794, '81206', 'CUSCO', 'QUISPICANCHI', 'CUSIPATA', 'CUSIPATA', NULL, NULL),
(795, '81207', 'CUSCO', 'QUISPICANCHI', 'HUARO', 'HUARO', NULL, NULL),
(796, '81208', 'CUSCO', 'QUISPICANCHI', 'LUCRE', 'LUCRE', NULL, NULL),
(797, '81209', 'CUSCO', 'QUISPICANCHI', 'MARCAPATA', 'MARCAPATA', NULL, NULL),
(798, '81210', 'CUSCO', 'QUISPICANCHI', 'OCONGATE', 'OCONGATE', NULL, NULL),
(799, '81211', 'CUSCO', 'QUISPICANCHI', 'OROPESA', 'OROPESA', NULL, NULL),
(800, '81212', 'CUSCO', 'QUISPICANCHI', 'QUIQUIJANA', 'QUIQUIJANA', NULL, NULL),
(801, '81301', 'CUSCO', 'URUBAMBA', 'URUBAMBA', 'URUBAMBA', NULL, NULL),
(802, '81302', 'CUSCO', 'URUBAMBA', 'CHINCHERO', 'CHINCHERO', NULL, NULL),
(803, '81303', 'CUSCO', 'URUBAMBA', 'HUAYLLABAMBA', 'HUAYLLABAMBA', NULL, NULL),
(804, '81304', 'CUSCO', 'URUBAMBA', 'MACHUPICCHU', 'MACHUPICCHU', NULL, NULL),
(805, '81305', 'CUSCO', 'URUBAMBA', 'MARAS', 'MARAS', NULL, NULL),
(806, '81306', 'CUSCO', 'URUBAMBA', 'OLLANTAYTAMBO', 'OLLANTAYTAMBO', NULL, NULL),
(807, '81307', 'CUSCO', 'URUBAMBA', 'YUCAY', 'YUCAY', NULL, NULL),
(808, '90101', 'HUANCAVELICA', 'HUANCAVELICA', 'HUANCAVELICA', 'HUANCAVELICA', NULL, NULL),
(809, '90102', 'HUANCAVELICA', 'HUANCAVELICA', 'ACOBAMBILLA', 'ACOBAMBILLA', NULL, NULL),
(810, '90103', 'HUANCAVELICA', 'HUANCAVELICA', 'ACORIA', 'ACORIA', NULL, NULL),
(811, '90104', 'HUANCAVELICA', 'HUANCAVELICA', 'CONAYCA', 'CONAYCA', NULL, NULL),
(812, '90105', 'HUANCAVELICA', 'HUANCAVELICA', 'CUENCA', 'CUENCA', NULL, NULL),
(813, '90106', 'HUANCAVELICA', 'HUANCAVELICA', 'HUACHOCOLPA', 'HUACHOCOLPA', NULL, NULL),
(814, '90107', 'HUANCAVELICA', 'HUANCAVELICA', 'HUAYLLAHUARA', 'HUAYLLAHUARA', NULL, NULL),
(815, '90108', 'HUANCAVELICA', 'HUANCAVELICA', 'IZCUCHACA', 'IZCUCHACA', NULL, NULL),
(816, '90109', 'HUANCAVELICA', 'HUANCAVELICA', 'LARIA', 'LARIA', NULL, NULL),
(817, '90110', 'HUANCAVELICA', 'HUANCAVELICA', 'MANTA', 'MANTA', NULL, NULL),
(818, '90111', 'HUANCAVELICA', 'HUANCAVELICA', 'MARISCAL CACERES', 'MARISCAL CACERES', NULL, NULL),
(819, '90112', 'HUANCAVELICA', 'HUANCAVELICA', 'MOYA', 'MOYA', NULL, NULL),
(820, '90113', 'HUANCAVELICA', 'HUANCAVELICA', 'NUEVO OCCORO', 'OCCORO', NULL, NULL),
(821, '90114', 'HUANCAVELICA', 'HUANCAVELICA', 'PALCA', 'PALCA', NULL, NULL),
(822, '90115', 'HUANCAVELICA', 'HUANCAVELICA', 'PILCHACA', 'PILCHACA', NULL, NULL),
(823, '90116', 'HUANCAVELICA', 'HUANCAVELICA', 'VILCA', 'VILCA', NULL, NULL),
(824, '90117', 'HUANCAVELICA', 'HUANCAVELICA', 'YAULI', 'YAULI', NULL, NULL),
(825, '90118', 'HUANCAVELICA', 'HUANCAVELICA', 'ASCENSION', 'ASCENSION', NULL, NULL),
(826, '90119', 'HUANCAVELICA', 'HUANCAVELICA', 'HUANDO', 'HUANDO', NULL, NULL),
(827, '90201', 'HUANCAVELICA', 'ACOBAMBA', 'ACOBAMBA', 'ACOBAMBA', NULL, NULL),
(828, '90202', 'HUANCAVELICA', 'ACOBAMBA', 'ANDABAMBA', 'ANDABAMBA', NULL, NULL),
(829, '90203', 'HUANCAVELICA', 'ACOBAMBA', 'ANTA', 'ANTA', NULL, NULL),
(830, '90204', 'HUANCAVELICA', 'ACOBAMBA', 'CAJA', 'CAJA', NULL, NULL),
(831, '90205', 'HUANCAVELICA', 'ACOBAMBA', 'MARCAS', 'MARCAS', NULL, NULL),
(832, '90206', 'HUANCAVELICA', 'ACOBAMBA', 'PAUCARA', 'PAUCARA', NULL, NULL),
(833, '90207', 'HUANCAVELICA', 'ACOBAMBA', 'POMACOCHA', 'POMACOCHA', NULL, NULL),
(834, '90208', 'HUANCAVELICA', 'ACOBAMBA', 'ROSARIO', 'ROSARIO', NULL, NULL),
(835, '90301', 'HUANCAVELICA', 'ANGARAES', 'LIRCAY', 'LIRCAY', NULL, NULL),
(836, '90302', 'HUANCAVELICA', 'ANGARAES', 'ANCHONGA', 'ANCHONGA', NULL, NULL),
(837, '90303', 'HUANCAVELICA', 'ANGARAES', 'CALLANMARCA', 'CALLANMARCA', NULL, NULL),
(838, '90304', 'HUANCAVELICA', 'ANGARAES', 'CCOCHACCASA', 'CCOCHACCASA', NULL, NULL),
(839, '90305', 'HUANCAVELICA', 'ANGARAES', 'CHINCHO', 'CHINCHO', NULL, NULL),
(840, '90306', 'HUANCAVELICA', 'ANGARAES', 'CONGALLA', 'CONGALLA', NULL, NULL),
(841, '90307', 'HUANCAVELICA', 'ANGARAES', 'HUANCA-HUANCA', 'HUANCA-HUANCA', NULL, NULL),
(842, '90308', 'HUANCAVELICA', 'ANGARAES', 'HUAYLLAY GRANDE', 'HUAYLLAY GRANDE', NULL, NULL),
(843, '90309', 'HUANCAVELICA', 'ANGARAES', 'JULCAMARCA', 'JULCAMARCA', NULL, NULL),
(844, '90310', 'HUANCAVELICA', 'ANGARAES', 'SAN ANTONIO DE ANTAPARCO', 'ANTAPARCO', NULL, NULL),
(845, '90311', 'HUANCAVELICA', 'ANGARAES', 'SANTO TOMAS DE PATA', 'SANTO TOMAS DE PATA', NULL, NULL),
(846, '90312', 'HUANCAVELICA', 'ANGARAES', 'SECCLLA', 'SECCLLA', NULL, NULL),
(847, '90401', 'HUANCAVELICA', 'CASTROVIRREYNA', 'CASTROVIRREYNA', 'CASTROVIRREYNA', NULL, NULL),
(848, '90402', 'HUANCAVELICA', 'CASTROVIRREYNA', 'ARMA', 'ARMA', NULL, NULL),
(849, '90403', 'HUANCAVELICA', 'CASTROVIRREYNA', 'AURAHUA', 'AURAHUA', NULL, NULL),
(850, '90404', 'HUANCAVELICA', 'CASTROVIRREYNA', 'CAPILLAS', 'CAPILLAS', NULL, NULL),
(851, '90405', 'HUANCAVELICA', 'CASTROVIRREYNA', 'CHUPAMARCA', 'CHUPAMARCA', NULL, NULL),
(852, '90406', 'HUANCAVELICA', 'CASTROVIRREYNA', 'COCAS', 'COCAS', NULL, NULL),
(853, '90407', 'HUANCAVELICA', 'CASTROVIRREYNA', 'HUACHOS', 'HUACHOS', NULL, NULL),
(854, '90408', 'HUANCAVELICA', 'CASTROVIRREYNA', 'HUAMATAMBO', 'HUAMATAMBO', NULL, NULL),
(855, '90409', 'HUANCAVELICA', 'CASTROVIRREYNA', 'MOLLEPAMPA', 'MOLLEPAMPA', NULL, NULL),
(856, '90410', 'HUANCAVELICA', 'CASTROVIRREYNA', 'SAN JUAN', 'SAN JUAN', NULL, NULL),
(857, '90411', 'HUANCAVELICA', 'CASTROVIRREYNA', 'SANTA ANA', 'SANTA ANA', NULL, NULL),
(858, '90412', 'HUANCAVELICA', 'CASTROVIRREYNA', 'TANTARA', 'TANTARA', NULL, NULL),
(859, '90413', 'HUANCAVELICA', 'CASTROVIRREYNA', 'TICRAPO', 'TICRAPO', NULL, NULL),
(860, '90501', 'HUANCAVELICA', 'CHURCAMPA', 'CHURCAMPA', 'CHURCAMPA', NULL, NULL),
(861, '90502', 'HUANCAVELICA', 'CHURCAMPA', 'ANCO', 'LA ESMERALDA', NULL, NULL),
(862, '90503', 'HUANCAVELICA', 'CHURCAMPA', 'CHINCHIHUASI', 'CHINCHIHUASI', NULL, NULL),
(863, '90504', 'HUANCAVELICA', 'CHURCAMPA', 'EL CARMEN', 'PAUCARBAMBILLA', NULL, NULL),
(864, '90505', 'HUANCAVELICA', 'CHURCAMPA', 'LA MERCED', 'LA MERCED', NULL, NULL),
(865, '90506', 'HUANCAVELICA', 'CHURCAMPA', 'LOCROJA', 'LOCROJA', NULL, NULL),
(866, '90507', 'HUANCAVELICA', 'CHURCAMPA', 'PAUCARBAMBA', 'PAUCARBAMBA', NULL, NULL),
(867, '90508', 'HUANCAVELICA', 'CHURCAMPA', 'SAN MIGUEL DE MAYOCC', 'MAYOCC', NULL, NULL),
(868, '90509', 'HUANCAVELICA', 'CHURCAMPA', 'SAN PEDRO DE CORIS', 'SAN PEDRO DE CORIS', NULL, NULL),
(869, '90510', 'HUANCAVELICA', 'CHURCAMPA', 'PACHAMARCA', 'PACHAMARCA', NULL, NULL),
(870, '90511', 'HUANCAVELICA', 'CHURCAMPA', 'COSME', 'SANTA CLARA DE COSME', NULL, NULL),
(871, '90601', 'HUANCAVELICA', 'HUAYTARA', 'HUAYTARA', 'HUAYTARA', NULL, NULL),
(872, '90602', 'HUANCAVELICA', 'HUAYTARA', 'AYAVI', 'AYAVI', NULL, NULL),
(873, '90603', 'HUANCAVELICA', 'HUAYTARA', 'CORDOVA', 'CORDOVA', NULL, NULL),
(874, '90604', 'HUANCAVELICA', 'HUAYTARA', 'HUAYACUNDO ARMA', 'HUAYACUNDO ARMA', NULL, NULL),
(875, '90605', 'HUANCAVELICA', 'HUAYTARA', 'LARAMARCA', 'LARAMARCA', NULL, NULL),
(876, '90606', 'HUANCAVELICA', 'HUAYTARA', 'OCOYO', 'OCOYO', NULL, NULL),
(877, '90607', 'HUANCAVELICA', 'HUAYTARA', 'PILPICHACA', 'PILPICHACA', NULL, NULL),
(878, '90608', 'HUANCAVELICA', 'HUAYTARA', 'QUERCO', 'QUERCO', NULL, NULL),
(879, '90609', 'HUANCAVELICA', 'HUAYTARA', 'QUITO-ARMA', 'QUITO-ARMA', NULL, NULL),
(880, '90610', 'HUANCAVELICA', 'HUAYTARA', 'SAN ANTONIO DE CUSICANCHA', 'CUSICANCHA', NULL, NULL),
(881, '90611', 'HUANCAVELICA', 'HUAYTARA', 'SAN FRANCISCO DE SANGAYAICO', 'SAN FRANCISCO DE SANGAYAICO', NULL, NULL),
(882, '90612', 'HUANCAVELICA', 'HUAYTARA', 'SAN ISIDRO', 'SAN JUAN DE HUIRPACANCHA', NULL, NULL),
(883, '90613', 'HUANCAVELICA', 'HUAYTARA', 'SANTIAGO DE CHOCORVOS', 'SANTIAGO DE CHOCORVOS', NULL, NULL),
(884, '90614', 'HUANCAVELICA', 'HUAYTARA', 'SANTIAGO DE QUIRAHUARA', 'SANTIAGO DE QUIRAHUARA', NULL, NULL),
(885, '90615', 'HUANCAVELICA', 'HUAYTARA', 'SANTO DOMINGO DE CAPILLAS', 'SANTO DOMINGO DE CAPILLAS', NULL, NULL),
(886, '90616', 'HUANCAVELICA', 'HUAYTARA', 'TAMBO', 'TAMBO', NULL, NULL),
(887, '90701', 'HUANCAVELICA', 'TAYACAJA', 'PAMPAS', 'PAMPAS', NULL, NULL),
(888, '90702', 'HUANCAVELICA', 'TAYACAJA', 'ACOSTAMBO', 'ACOSTAMBO', NULL, NULL),
(889, '90703', 'HUANCAVELICA', 'TAYACAJA', 'ACRAQUIA', 'ACRAQUIA', NULL, NULL),
(890, '90704', 'HUANCAVELICA', 'TAYACAJA', 'AHUAYCHA', 'AHUAYCHA', NULL, NULL),
(891, '90705', 'HUANCAVELICA', 'TAYACAJA', 'COLCABAMBA', 'COLCABAMBA', NULL, NULL),
(892, '90706', 'HUANCAVELICA', 'TAYACAJA', 'DANIEL HERNANDEZ', 'MARISCAL CACERES', NULL, NULL),
(893, '90707', 'HUANCAVELICA', 'TAYACAJA', 'HUACHOCOLPA', 'HUACHOCOLPA', NULL, NULL),
(894, '90709', 'HUANCAVELICA', 'TAYACAJA', 'HUARIBAMBA', 'HUARIBAMBA', NULL, NULL),
(895, '90710', 'HUANCAVELICA', 'TAYACAJA', 'ÑAHUIMPUQUIO', 'ÑAHUIMPUQUIO', NULL, NULL),
(896, '90711', 'HUANCAVELICA', 'TAYACAJA', 'PAZOS', 'PAZOS', NULL, NULL),
(897, '90713', 'HUANCAVELICA', 'TAYACAJA', 'QUISHUAR', 'QUISHUAR', NULL, NULL),
(898, '90714', 'HUANCAVELICA', 'TAYACAJA', 'SALCABAMBA', 'SALCABAMBA', NULL, NULL),
(899, '90715', 'HUANCAVELICA', 'TAYACAJA', 'SALCAHUASI', 'SALCAHUASI', NULL, NULL),
(900, '90716', 'HUANCAVELICA', 'TAYACAJA', 'SAN MARCOS DE ROCCHAC', 'SAN MARCOS DE ROCCHAC', NULL, NULL),
(901, '90717', 'HUANCAVELICA', 'TAYACAJA', 'SURCUBAMBA', 'SURCUBAMBA', NULL, NULL),
(902, '90718', 'HUANCAVELICA', 'TAYACAJA', 'TINTAY PUNCU', 'TINTAY', NULL, NULL),
(903, '90719', 'HUANCAVELICA', 'TAYACAJA', 'QUICHUAS', 'QUICHUAS', NULL, NULL),
(904, '90720', 'HUANCAVELICA', 'TAYACAJA', 'ANDAYMARCA', 'ANDAYMARCA', NULL, NULL),
(905, '90721', 'HUANCAVELICA', 'TAYACAJA', 'ROBLE', 'PUERTO SAN ANTONIO', NULL, NULL),
(906, '90722', 'HUANCAVELICA', 'TAYACAJA', 'PICHOS', 'PICHOS', NULL, NULL),
(907, '90723', 'HUANCAVELICA', 'TAYACAJA', 'SANTIAGO DE TUCUMA', 'SANTIAGO DE TUCUMA', NULL, NULL),
(908, '100101', 'HUANUCO', 'HUANUCO', 'HUANUCO', 'HUANUCO', NULL, NULL),
(909, '100102', 'HUANUCO', 'HUANUCO', 'AMARILIS', 'PAUCARBAMBA', NULL, NULL),
(910, '100103', 'HUANUCO', 'HUANUCO', 'CHINCHAO', 'ACOMAYO', NULL, NULL),
(911, '100104', 'HUANUCO', 'HUANUCO', 'CHURUBAMBA', 'CHURUBAMBA', NULL, NULL),
(912, '100105', 'HUANUCO', 'HUANUCO', 'MARGOS', 'MARGOS', NULL, NULL),
(913, '100106', 'HUANUCO', 'HUANUCO', 'QUISQUI (KICHKI)', 'HUANCAPALLAC', NULL, NULL),
(914, '100107', 'HUANUCO', 'HUANUCO', 'SAN FRANCISCO DE CAYRAN', 'CAYRAN', NULL, NULL),
(915, '100108', 'HUANUCO', 'HUANUCO', 'SAN PEDRO DE CHAULAN', 'CHAULAN', NULL, NULL),
(916, '100109', 'HUANUCO', 'HUANUCO', 'SANTA MARIA DEL VALLE', 'SANTA MARIA DEL VALLE', NULL, NULL),
(917, '100110', 'HUANUCO', 'HUANUCO', 'YARUMAYO', 'YARUMAYO', NULL, NULL),
(918, '100111', 'HUANUCO', 'HUANUCO', 'PILLCO MARCA', 'CAYHUAYNA', NULL, NULL),
(919, '100112', 'HUANUCO', 'HUANUCO', 'YACUS', 'YACUS', NULL, NULL),
(920, '100113', 'HUANUCO', 'HUANUCO', 'SAN PABLO DE PILLAO', 'SAN PABLO DE PILLAO', NULL, NULL),
(921, '100201', 'HUANUCO', 'AMBO', 'AMBO', 'AMBO', NULL, NULL),
(922, '100202', 'HUANUCO', 'AMBO', 'CAYNA', 'CAYNA', NULL, NULL),
(923, '100203', 'HUANUCO', 'AMBO', 'COLPAS', 'COLPAS', NULL, NULL),
(924, '100204', 'HUANUCO', 'AMBO', 'CONCHAMARCA', 'CONCHAMARCA', NULL, NULL),
(925, '100205', 'HUANUCO', 'AMBO', 'HUACAR', 'HUACAR', NULL, NULL),
(926, '100206', 'HUANUCO', 'AMBO', 'SAN FRANCISCO', 'MOSCA', NULL, NULL),
(927, '100207', 'HUANUCO', 'AMBO', 'SAN RAFAEL', 'SAN RAFAEL', NULL, NULL),
(928, '100208', 'HUANUCO', 'AMBO', 'TOMAY KICHWA', 'TOMAY KICHWA', NULL, NULL),
(929, '100301', 'HUANUCO', 'DOS DE MAYO', 'LA UNION', 'LA UNION', NULL, NULL),
(930, '100307', 'HUANUCO', 'DOS DE MAYO', 'CHUQUIS', 'CHUQUIS', NULL, NULL),
(931, '100311', 'HUANUCO', 'DOS DE MAYO', 'MARIAS', 'MARIAS', NULL, NULL),
(932, '100313', 'HUANUCO', 'DOS DE MAYO', 'PACHAS', 'PACHAS', NULL, NULL),
(933, '100316', 'HUANUCO', 'DOS DE MAYO', 'QUIVILLA', 'QUIVILLA', NULL, NULL),
(934, '100317', 'HUANUCO', 'DOS DE MAYO', 'RIPAN', 'RIPAN', NULL, NULL),
(935, '100321', 'HUANUCO', 'DOS DE MAYO', 'SHUNQUI', 'SHUNQUI', NULL, NULL),
(936, '100322', 'HUANUCO', 'DOS DE MAYO', 'SILLAPATA', 'SILLAPATA', NULL, NULL),
(937, '100323', 'HUANUCO', 'DOS DE MAYO', 'YANAS', 'YANAS', NULL, NULL),
(938, '100401', 'HUANUCO', 'HUACAYBAMBA', 'HUACAYBAMBA', 'HUACAYBAMBA', NULL, NULL),
(939, '100402', 'HUANUCO', 'HUACAYBAMBA', 'CANCHABAMBA', 'CANCHABAMBA', NULL, NULL),
(940, '100403', 'HUANUCO', 'HUACAYBAMBA', 'COCHABAMBA', 'COCHABAMBA', NULL, NULL),
(941, '100404', 'HUANUCO', 'HUACAYBAMBA', 'PINRA', 'PINRA', NULL, NULL),
(942, '100501', 'HUANUCO', 'HUAMALIES', 'LLATA', 'LLATA', NULL, NULL),
(943, '100502', 'HUANUCO', 'HUAMALIES', 'ARANCAY', 'ARANCAY', NULL, NULL),
(944, '100503', 'HUANUCO', 'HUAMALIES', 'CHAVIN DE PARIARCA', 'CHAVIN DE PARIARCA', NULL, NULL),
(945, '100504', 'HUANUCO', 'HUAMALIES', 'JACAS GRANDE', 'JACAS GRANDE', NULL, NULL),
(946, '100505', 'HUANUCO', 'HUAMALIES', 'JIRCAN', 'JIRCAN', NULL, NULL),
(947, '100506', 'HUANUCO', 'HUAMALIES', 'MIRAFLORES', 'MIRAFLORES', NULL, NULL),
(948, '100507', 'HUANUCO', 'HUAMALIES', 'MONZON', 'MONZON', NULL, NULL),
(949, '100508', 'HUANUCO', 'HUAMALIES', 'PUNCHAO', 'PUNCHAO', NULL, NULL),
(950, '100509', 'HUANUCO', 'HUAMALIES', 'PUÑOS', 'PUÑOS', NULL, NULL),
(951, '100510', 'HUANUCO', 'HUAMALIES', 'SINGA', 'SINGA', NULL, NULL),
(952, '100511', 'HUANUCO', 'HUAMALIES', 'TANTAMAYO', 'TANTAMAYO', NULL, NULL),
(953, '100601', 'HUANUCO', 'LEONCIO PRADO', 'RUPA-RUPA', 'TINGO MARIA', NULL, NULL),
(954, '100602', 'HUANUCO', 'LEONCIO PRADO', 'DANIEL ALOMIA ROBLES', 'DANIEL ALOMIA ROBLES', NULL, NULL),
(955, '100603', 'HUANUCO', 'LEONCIO PRADO', 'HERMILIO VALDIZAN', 'HERMILIO VALDIZAN', NULL, NULL),
(956, '100604', 'HUANUCO', 'LEONCIO PRADO', 'JOSE CRESPO Y CASTILLO', 'AUCAYACU', NULL, NULL),
(957, '100605', 'HUANUCO', 'LEONCIO PRADO', 'LUYANDO', 'LUYANDO /8', NULL, NULL),
(958, '100606', 'HUANUCO', 'LEONCIO PRADO', 'MARIANO DAMASO BERAUN', 'LAS PALMAS', NULL, NULL),
(959, '100607', 'HUANUCO', 'LEONCIO PRADO', 'PUCAYACU', 'PUCAYACU', NULL, NULL),
(960, '100608', 'HUANUCO', 'LEONCIO PRADO', 'CASTILLO GRANDE', 'CASTILLO GRANDE', NULL, NULL),
(961, '100609', 'HUANUCO', 'LEONCIO PRADO', 'PUEBLO NUEVO', 'PUEBLO NUEVO', NULL, NULL),
(962, '100610', 'HUANUCO', 'LEONCIO PRADO', 'SANTO DOMINGO DE ANDIA', 'PACAE', NULL, NULL),
(963, '100701', 'HUANUCO', 'MARAÑON', 'HUACRACHUCO', 'HUACRACHUCO', NULL, NULL),
(964, '100702', 'HUANUCO', 'MARAÑON', 'CHOLON', 'SAN PEDRO DE CHONTA', NULL, NULL),
(965, '100703', 'HUANUCO', 'MARAÑON', 'SAN BUENAVENTURA', 'SAN BUENAVENTURA', NULL, NULL),
(966, '100704', 'HUANUCO', 'MARAÑON', 'LA MORADA', 'LA MORADA', NULL, NULL),
(967, '100705', 'HUANUCO', 'MARAÑON', 'SANTA ROSA DE ALTO YANAJANCA', 'SANTA ROSA DE ALTO YANAJANCA', NULL, NULL),
(968, '100801', 'HUANUCO', 'PACHITEA', 'PANAO', 'PANAO', NULL, NULL),
(969, '100802', 'HUANUCO', 'PACHITEA', 'CHAGLLA', 'CHAGLLA', NULL, NULL),
(970, '100803', 'HUANUCO', 'PACHITEA', 'MOLINO', 'MOLINO', NULL, NULL),
(971, '100804', 'HUANUCO', 'PACHITEA', 'UMARI', 'UMARI (TAMBILLO)', NULL, NULL),
(972, '100901', 'HUANUCO', 'PUERTO INCA', 'PUERTO INCA', 'PUERTO INCA', NULL, NULL),
(973, '100902', 'HUANUCO', 'PUERTO INCA', 'CODO DEL POZUZO', 'CODO DEL POZUZO', NULL, NULL),
(974, '100903', 'HUANUCO', 'PUERTO INCA', 'HONORIA', 'HONORIA', NULL, NULL),
(975, '100904', 'HUANUCO', 'PUERTO INCA', 'TOURNAVISTA', 'TOURNAVISTA', NULL, NULL),
(976, '100905', 'HUANUCO', 'PUERTO INCA', 'YUYAPICHIS', 'YUYAPICHIS', NULL, NULL),
(977, '101001', 'HUANUCO', 'LAURICOCHA', 'JESUS', 'JESUS', NULL, NULL),
(978, '101002', 'HUANUCO', 'LAURICOCHA', 'BAÑOS', 'BAÑOS', NULL, NULL),
(979, '101003', 'HUANUCO', 'LAURICOCHA', 'JIVIA', 'JIVIA', NULL, NULL),
(980, '101004', 'HUANUCO', 'LAURICOCHA', 'QUEROPALCA', 'QUEROPALCA', NULL, NULL),
(981, '101005', 'HUANUCO', 'LAURICOCHA', 'RONDOS', 'RONDOS', NULL, NULL),
(982, '101006', 'HUANUCO', 'LAURICOCHA', 'SAN FRANCISCO DE ASIS', 'HUARIN', NULL, NULL),
(983, '101007', 'HUANUCO', 'LAURICOCHA', 'SAN MIGUEL DE CAURI', 'CAURI', NULL, NULL),
(984, '101101', 'HUANUCO', 'YAROWILCA', 'CHAVINILLO', 'CHAVINILLO', NULL, NULL),
(985, '101102', 'HUANUCO', 'YAROWILCA', 'CAHUAC', 'CAHUAC', NULL, NULL),
(986, '101103', 'HUANUCO', 'YAROWILCA', 'CHACABAMBA', 'CHACABAMBA', NULL, NULL),
(987, '101104', 'HUANUCO', 'YAROWILCA', 'APARICIO POMARES', 'CHUPAN', NULL, NULL),
(988, '101105', 'HUANUCO', 'YAROWILCA', 'JACAS CHICO', 'SAN CRISTOBAL DE JACAS CHICO', NULL, NULL),
(989, '101106', 'HUANUCO', 'YAROWILCA', 'OBAS', 'OBAS', NULL, NULL),
(990, '101107', 'HUANUCO', 'YAROWILCA', 'PAMPAMARCA', 'PAMPAMARCA', NULL, NULL),
(991, '101108', 'HUANUCO', 'YAROWILCA', 'CHORAS', 'CHORAS', NULL, NULL),
(992, '110101', 'ICA', 'ICA', 'ICA', 'ICA', NULL, NULL),
(993, '110102', 'ICA', 'ICA', 'LA TINGUIÑA', 'LA TINGUIÑA', NULL, NULL),
(994, '110103', 'ICA', 'ICA', 'LOS AQUIJES', 'LOS AQUIJES', NULL, NULL),
(995, '110104', 'ICA', 'ICA', 'OCUCAJE', 'OCUCAJE', NULL, NULL),
(996, '110105', 'ICA', 'ICA', 'PACHACUTEC', 'PAMPA DE TATE', NULL, NULL),
(997, '110106', 'ICA', 'ICA', 'PARCONA', 'PARCONA', NULL, NULL),
(998, '110107', 'ICA', 'ICA', 'PUEBLO NUEVO', 'PUEBLO NUEVO', NULL, NULL),
(999, '110108', 'ICA', 'ICA', 'SALAS', 'GUADALUPE', NULL, NULL),
(1000, '110109', 'ICA', 'ICA', 'SAN JOSE DE LOS MOLINOS', 'SAN JOSE DE LOS MOLINOS', NULL, NULL),
(1001, '110110', 'ICA', 'ICA', 'SAN JUAN BAUTISTA', 'SAN JUAN BAUTISTA', NULL, NULL),
(1002, '110111', 'ICA', 'ICA', 'SANTIAGO', 'SANTIAGO', NULL, NULL),
(1003, '110112', 'ICA', 'ICA', 'SUBTANJALLA', 'SUBTANJALLA', NULL, NULL),
(1004, '110113', 'ICA', 'ICA', 'TATE', 'TATE DE LA CAPILLA', NULL, NULL),
(1005, '110114', 'ICA', 'ICA', 'YAUCA DEL ROSARIO', 'CURIS /9', NULL, NULL),
(1006, '110201', 'ICA', 'CHINCHA', 'CHINCHA ALTA', 'CHINCHA ALTA', NULL, NULL),
(1007, '110202', 'ICA', 'CHINCHA', 'ALTO LARAN', 'ALTO LARAN', NULL, NULL),
(1008, '110203', 'ICA', 'CHINCHA', 'CHAVIN', 'CHAVIN', NULL, NULL),
(1009, '110204', 'ICA', 'CHINCHA', 'CHINCHA BAJA', 'CHINCHA BAJA', NULL, NULL),
(1010, '110205', 'ICA', 'CHINCHA', 'EL CARMEN', 'EL CARMEN', NULL, NULL),
(1011, '110206', 'ICA', 'CHINCHA', 'GROCIO PRADO', 'SAN PEDRO', NULL, NULL),
(1012, '110207', 'ICA', 'CHINCHA', 'PUEBLO NUEVO', 'PUEBLO NUEVO', NULL, NULL),
(1013, '110208', 'ICA', 'CHINCHA', 'SAN JUAN DE YANAC', 'SAN JUAN DE YANAC', NULL, NULL),
(1014, '110209', 'ICA', 'CHINCHA', 'SAN PEDRO DE HUACARPANA', 'SAN PEDRO DE HUACARPANA', NULL, NULL),
(1015, '110210', 'ICA', 'CHINCHA', 'SUNAMPE', 'SUNAMPE', NULL, NULL),
(1016, '110211', 'ICA', 'CHINCHA', 'TAMBO DE MORA', 'TAMBO DE MORA', NULL, NULL),
(1017, '110301', 'ICA', 'NASCA', 'NASCA', 'NASCA', NULL, NULL),
(1018, '110302', 'ICA', 'NASCA', 'CHANGUILLO', 'CHANGUILLO', NULL, NULL),
(1019, '110303', 'ICA', 'NASCA', 'EL INGENIO', 'EL INGENIO', NULL, NULL),
(1020, '110304', 'ICA', 'NASCA', 'MARCONA', 'SAN JUAN', NULL, NULL),
(1021, '110305', 'ICA', 'NASCA', 'VISTA ALEGRE', 'VISTA ALEGRE', NULL, NULL),
(1022, '110401', 'ICA', 'PALPA', 'PALPA', 'PALPA', NULL, NULL),
(1023, '110402', 'ICA', 'PALPA', 'LLIPATA', 'LLIPATA', NULL, NULL),
(1024, '110403', 'ICA', 'PALPA', 'RIO GRANDE', 'RIO GRANDE', NULL, NULL),
(1025, '110404', 'ICA', 'PALPA', 'SANTA CRUZ', 'SANTA CRUZ', NULL, NULL),
(1026, '110405', 'ICA', 'PALPA', 'TIBILLO', 'TIBILLO', NULL, NULL),
(1027, '110501', 'ICA', 'PISCO', 'PISCO', 'PISCO', NULL, NULL),
(1028, '110502', 'ICA', 'PISCO', 'HUANCANO', 'HUANCANO', NULL, NULL),
(1029, '110503', 'ICA', 'PISCO', 'HUMAY', 'HUMAY', NULL, NULL),
(1030, '110504', 'ICA', 'PISCO', 'INDEPENDENCIA', 'INDEPENDENCIA', NULL, NULL),
(1031, '110505', 'ICA', 'PISCO', 'PARACAS', 'PARACAS', NULL, NULL),
(1032, '110506', 'ICA', 'PISCO', 'SAN ANDRES', 'SAN ANDRES', NULL, NULL),
(1033, '110507', 'ICA', 'PISCO', 'SAN CLEMENTE', 'SAN CLEMENTE', NULL, NULL),
(1034, '110508', 'ICA', 'PISCO', 'TUPAC AMARU INCA', 'TUPAC AMARU', NULL, NULL),
(1035, '120101', 'JUNIN', 'HUANCAYO', 'HUANCAYO', 'HUANCAYO', NULL, NULL),
(1036, '120104', 'JUNIN', 'HUANCAYO', 'CARHUACALLANGA', 'CARHUACALLANGA', NULL, NULL),
(1037, '120105', 'JUNIN', 'HUANCAYO', 'CHACAPAMPA', 'CHACAPAMPA', NULL, NULL),
(1038, '120106', 'JUNIN', 'HUANCAYO', 'CHICCHE', 'CHICCHE', NULL, NULL),
(1039, '120107', 'JUNIN', 'HUANCAYO', 'CHILCA', 'CHILCA', NULL, NULL),
(1040, '120108', 'JUNIN', 'HUANCAYO', 'CHONGOS ALTO', 'CHONGOS ALTO', NULL, NULL),
(1041, '120111', 'JUNIN', 'HUANCAYO', 'CHUPURO', 'CHUPURO', NULL, NULL),
(1042, '120112', 'JUNIN', 'HUANCAYO', 'COLCA', 'COLCA', NULL, NULL),
(1043, '120113', 'JUNIN', 'HUANCAYO', 'CULLHUAS', 'CULLHUAS', NULL, NULL),
(1044, '120114', 'JUNIN', 'HUANCAYO', 'EL TAMBO', 'EL TAMBO', NULL, NULL),
(1045, '120116', 'JUNIN', 'HUANCAYO', 'HUACRAPUQUIO', 'HUACRAPUQUIO', NULL, NULL),
(1046, '120117', 'JUNIN', 'HUANCAYO', 'HUALHUAS', 'HUALHUAS', NULL, NULL),
(1047, '120119', 'JUNIN', 'HUANCAYO', 'HUANCAN', 'HUANCAN', NULL, NULL),
(1048, '120120', 'JUNIN', 'HUANCAYO', 'HUASICANCHA', 'HUASICANCHA', NULL, NULL),
(1049, '120121', 'JUNIN', 'HUANCAYO', 'HUAYUCACHI', 'HUAYUCACHI', NULL, NULL),
(1050, '120122', 'JUNIN', 'HUANCAYO', 'INGENIO', 'INGENIO', NULL, NULL),
(1051, '120124', 'JUNIN', 'HUANCAYO', 'PARIAHUANCA', 'PARIAHUANCA /10', NULL, NULL),
(1052, '120125', 'JUNIN', 'HUANCAYO', 'PILCOMAYO', 'PILCOMAYO', NULL, NULL),
(1053, '120126', 'JUNIN', 'HUANCAYO', 'PUCARA', 'PUCARA', NULL, NULL),
(1054, '120127', 'JUNIN', 'HUANCAYO', 'QUICHUAY', 'QUICHUAY', NULL, NULL),
(1055, '120128', 'JUNIN', 'HUANCAYO', 'QUILCAS', 'QUILCAS', NULL, NULL),
(1056, '120129', 'JUNIN', 'HUANCAYO', 'SAN AGUSTIN', 'SAN AGUSTIN', NULL, NULL),
(1057, '120130', 'JUNIN', 'HUANCAYO', 'SAN JERONIMO DE TUNAN', 'SAN JERONIMO DE TUNAN', NULL, NULL),
(1058, '120132', 'JUNIN', 'HUANCAYO', 'SAÑO', 'SAÑO', NULL, NULL),
(1059, '120133', 'JUNIN', 'HUANCAYO', 'SAPALLANGA', 'SAPALLANGA', NULL, NULL),
(1060, '120134', 'JUNIN', 'HUANCAYO', 'SICAYA', 'SICAYA', NULL, NULL),
(1061, '120135', 'JUNIN', 'HUANCAYO', 'SANTO DOMINGO DE ACOBAMBA', 'SANTO DOMINGO DE ACOBAMBA', NULL, NULL),
(1062, '120136', 'JUNIN', 'HUANCAYO', 'VIQUES', 'VIQUES', NULL, NULL),
(1063, '120201', 'JUNIN', 'CONCEPCION', 'CONCEPCION', 'CONCEPCION', NULL, NULL),
(1064, '120202', 'JUNIN', 'CONCEPCION', 'ACO', 'ACO', NULL, NULL),
(1065, '120203', 'JUNIN', 'CONCEPCION', 'ANDAMARCA', 'ANDAMARCA', NULL, NULL),
(1066, '120204', 'JUNIN', 'CONCEPCION', 'CHAMBARA', 'CHAMBARA', NULL, NULL),
(1067, '120205', 'JUNIN', 'CONCEPCION', 'COCHAS', 'COCHAS', NULL, NULL),
(1068, '120206', 'JUNIN', 'CONCEPCION', 'COMAS', 'COMAS', NULL, NULL),
(1069, '120207', 'JUNIN', 'CONCEPCION', 'HEROINAS TOLEDO', 'SAN ANTONIO DE OCOPA', NULL, NULL),
(1070, '120208', 'JUNIN', 'CONCEPCION', 'MANZANARES', 'SAN MIGUEL', NULL, NULL),
(1071, '120209', 'JUNIN', 'CONCEPCION', 'MARISCAL CASTILLA', 'MUCLLO', NULL, NULL),
(1072, '120210', 'JUNIN', 'CONCEPCION', 'MATAHUASI', 'MATAHUASI', NULL, NULL),
(1073, '120211', 'JUNIN', 'CONCEPCION', 'MITO', 'MITO', NULL, NULL),
(1074, '120212', 'JUNIN', 'CONCEPCION', 'NUEVE DE JULIO', 'SANTO DOMINGO DEL PRADO', NULL, NULL),
(1075, '120213', 'JUNIN', 'CONCEPCION', 'ORCOTUNA', 'ORCOTUNA', NULL, NULL),
(1076, '120214', 'JUNIN', 'CONCEPCION', 'SAN JOSE DE QUERO', 'SAN JOSE DE QUERO', NULL, NULL),
(1077, '120215', 'JUNIN', 'CONCEPCION', 'SANTA ROSA DE OCOPA', 'SANTA ROSA', NULL, NULL),
(1078, '120301', 'JUNIN', 'CHANCHAMAYO', 'CHANCHAMAYO', 'LA MERCED', NULL, NULL),
(1079, '120302', 'JUNIN', 'CHANCHAMAYO', 'PERENE', 'PERENE', NULL, NULL),
(1080, '120303', 'JUNIN', 'CHANCHAMAYO', 'PICHANAQUI', 'BAJO PICHANAQUI', NULL, NULL),
(1081, '120304', 'JUNIN', 'CHANCHAMAYO', 'SAN LUIS DE SHUARO', 'SAN LUIS DE SHUARO', NULL, NULL),
(1082, '120305', 'JUNIN', 'CHANCHAMAYO', 'SAN RAMON', 'SAN RAMON', NULL, NULL),
(1083, '120306', 'JUNIN', 'CHANCHAMAYO', 'VITOC', 'PUCARA', NULL, NULL),
(1084, '120401', 'JUNIN', 'JAUJA', 'JAUJA', 'JAUJA', NULL, NULL),
(1085, '120402', 'JUNIN', 'JAUJA', 'ACOLLA', 'ACOLLA', NULL, NULL),
(1086, '120403', 'JUNIN', 'JAUJA', 'APATA', 'APATA', NULL, NULL),
(1087, '120404', 'JUNIN', 'JAUJA', 'ATAURA', 'ATAURA', NULL, NULL),
(1088, '120405', 'JUNIN', 'JAUJA', 'CANCHAYLLO', 'CANCHAYLLO', NULL, NULL),
(1089, '120406', 'JUNIN', 'JAUJA', 'CURICACA', 'EL ROSARIO', NULL, NULL),
(1090, '120407', 'JUNIN', 'JAUJA', 'EL MANTARO', 'PUCUCHO', NULL, NULL),
(1091, '120408', 'JUNIN', 'JAUJA', 'HUAMALI', 'HUAMALI', NULL, NULL),
(1092, '120409', 'JUNIN', 'JAUJA', 'HUARIPAMPA', 'HUARIPAMPA', NULL, NULL),
(1093, '120410', 'JUNIN', 'JAUJA', 'HUERTAS', 'HUERTAS', NULL, NULL),
(1094, '120411', 'JUNIN', 'JAUJA', 'JANJAILLO', 'JANJAILLO /11', NULL, NULL),
(1095, '120412', 'JUNIN', 'JAUJA', 'JULCAN', 'JULCAN', NULL, NULL),
(1096, '120413', 'JUNIN', 'JAUJA', 'LEONOR ORDOÑEZ', 'HUANCANI', NULL, NULL),
(1097, '120414', 'JUNIN', 'JAUJA', 'LLOCLLAPAMPA', 'LLOCLLAPAMPA', NULL, NULL),
(1098, '120415', 'JUNIN', 'JAUJA', 'MARCO', 'MARCO', NULL, NULL),
(1099, '120416', 'JUNIN', 'JAUJA', 'MASMA', 'MASMA', NULL, NULL),
(1100, '120417', 'JUNIN', 'JAUJA', 'MASMA CHICCHE', 'MASMA CHICCHE', NULL, NULL),
(1101, '120418', 'JUNIN', 'JAUJA', 'MOLINOS', 'MOLINOS', NULL, NULL),
(1102, '120419', 'JUNIN', 'JAUJA', 'MONOBAMBA', 'MONOBAMBA', NULL, NULL),
(1103, '120420', 'JUNIN', 'JAUJA', 'MUQUI', 'MUQUI', NULL, NULL),
(1104, '120421', 'JUNIN', 'JAUJA', 'MUQUIYAUYO', 'MUQUIYAUYO', NULL, NULL),
(1105, '120422', 'JUNIN', 'JAUJA', 'PACA', 'PACA', NULL, NULL),
(1106, '120423', 'JUNIN', 'JAUJA', 'PACCHA', 'PACCHA', NULL, NULL),
(1107, '120424', 'JUNIN', 'JAUJA', 'PANCAN', 'PANCAN', NULL, NULL),
(1108, '120425', 'JUNIN', 'JAUJA', 'PARCO', 'PARCO', NULL, NULL),
(1109, '120426', 'JUNIN', 'JAUJA', 'POMACANCHA', 'POMACANCHA', NULL, NULL),
(1110, '120427', 'JUNIN', 'JAUJA', 'RICRAN', 'RICRAN', NULL, NULL),
(1111, '120428', 'JUNIN', 'JAUJA', 'SAN LORENZO', 'SAN LORENZO', NULL, NULL),
(1112, '120429', 'JUNIN', 'JAUJA', 'SAN PEDRO DE CHUNAN', 'SAN PEDRO DE CHUNAN', NULL, NULL),
(1113, '120430', 'JUNIN', 'JAUJA', 'SAUSA', 'SAUSA', NULL, NULL),
(1114, '120431', 'JUNIN', 'JAUJA', 'SINCOS', 'SINCOS', NULL, NULL),
(1115, '120432', 'JUNIN', 'JAUJA', 'TUNAN MARCA', 'CONCHO', NULL, NULL),
(1116, '120433', 'JUNIN', 'JAUJA', 'YAULI', 'YAULI', NULL, NULL),
(1117, '120434', 'JUNIN', 'JAUJA', 'YAUYOS', 'YAUYOS', NULL, NULL),
(1118, '120501', 'JUNIN', 'JUNIN', 'JUNIN', 'JUNIN', NULL, NULL),
(1119, '120502', 'JUNIN', 'JUNIN', 'CARHUAMAYO', 'CARHUAMAYO', NULL, NULL),
(1120, '120503', 'JUNIN', 'JUNIN', 'ONDORES', 'ONDORES', NULL, NULL),
(1121, '120504', 'JUNIN', 'JUNIN', 'ULCUMAYO', 'ULCUMAYO', NULL, NULL),
(1122, '120601', 'JUNIN', 'SATIPO', 'SATIPO', 'SATIPO', NULL, NULL),
(1123, '120602', 'JUNIN', 'SATIPO', 'COVIRIALI', 'COVIRIALI', NULL, NULL),
(1124, '120603', 'JUNIN', 'SATIPO', 'LLAYLLA', 'LLAYLLA', NULL, NULL),
(1125, '120604', 'JUNIN', 'SATIPO', 'MAZAMARI', 'MAZAMARI', NULL, NULL),
(1126, '120605', 'JUNIN', 'SATIPO', 'PAMPA HERMOSA', 'MARIPOSA', NULL, NULL),
(1127, '120606', 'JUNIN', 'SATIPO', 'PANGOA', 'SAN MARTIN DE PANGOA', NULL, NULL),
(1128, '120607', 'JUNIN', 'SATIPO', 'RIO NEGRO', 'RIO NEGRO', NULL, NULL),
(1129, '120608', 'JUNIN', 'SATIPO', 'RIO TAMBO', 'PUERTO OCOPA', NULL, NULL),
(1130, '120609', 'JUNIN', 'SATIPO', 'VIZCATAN DEL ENE', 'SAN MIGUEL DEL ENE', NULL, NULL),
(1131, '120701', 'JUNIN', 'TARMA', 'TARMA', 'TARMA', NULL, NULL),
(1132, '120702', 'JUNIN', 'TARMA', 'ACOBAMBA', 'ACOBAMBA', NULL, NULL),
(1133, '120703', 'JUNIN', 'TARMA', 'HUARICOLCA', 'HUARICOLCA', NULL, NULL),
(1134, '120704', 'JUNIN', 'TARMA', 'HUASAHUASI', 'HUASAHUASI', NULL, NULL),
(1135, '120705', 'JUNIN', 'TARMA', 'LA UNION', 'LETICIA', NULL, NULL),
(1136, '120706', 'JUNIN', 'TARMA', 'PALCA', 'PALCA', NULL, NULL),
(1137, '120707', 'JUNIN', 'TARMA', 'PALCAMAYO', 'PALCAMAYO', NULL, NULL),
(1138, '120708', 'JUNIN', 'TARMA', 'SAN PEDRO DE CAJAS', 'SAN PEDRO DE CAJAS', NULL, NULL),
(1139, '120709', 'JUNIN', 'TARMA', 'TAPO', 'TAPO', NULL, NULL),
(1140, '120801', 'JUNIN', 'YAULI', 'LA OROYA', 'LA OROYA', NULL, NULL),
(1141, '120802', 'JUNIN', 'YAULI', 'CHACAPALPA', 'CHACAPALPA', NULL, NULL),
(1142, '120803', 'JUNIN', 'YAULI', 'HUAY-HUAY', 'HUAY-HUAY', NULL, NULL),
(1143, '120804', 'JUNIN', 'YAULI', 'MARCAPOMACOCHA', 'MARCAPOMACOCHA', NULL, NULL),
(1144, '120805', 'JUNIN', 'YAULI', 'MOROCOCHA', 'NUEVA MOROCOCHA', NULL, NULL),
(1145, '120806', 'JUNIN', 'YAULI', 'PACCHA', 'PACCHA', NULL, NULL),
(1146, '120807', 'JUNIN', 'YAULI', 'SANTA BARBARA DE CARHUACAYAN', 'SANTA BARBARA DE CARHUACAYAN', NULL, NULL),
(1147, '120808', 'JUNIN', 'YAULI', 'SANTA ROSA DE SACCO', 'SANTA ROSA DE SACCO', NULL, NULL),
(1148, '120809', 'JUNIN', 'YAULI', 'SUITUCANCHA', 'SUITUCANCHA', NULL, NULL),
(1149, '120810', 'JUNIN', 'YAULI', 'YAULI', 'YAULI', NULL, NULL),
(1150, '120901', 'JUNIN', 'CHUPACA', 'CHUPACA', 'CHUPACA', NULL, NULL),
(1151, '120902', 'JUNIN', 'CHUPACA', 'AHUAC', 'AHUAC', NULL, NULL),
(1152, '120903', 'JUNIN', 'CHUPACA', 'CHONGOS BAJO', 'CHONGOS BAJO', NULL, NULL),
(1153, '120904', 'JUNIN', 'CHUPACA', 'HUACHAC', 'HUACHAC', NULL, NULL),
(1154, '120905', 'JUNIN', 'CHUPACA', 'HUAMANCACA CHICO', 'HUAMANCACA CHICO', NULL, NULL),
(1155, '120906', 'JUNIN', 'CHUPACA', 'SAN JUAN DE ISCOS', 'ISCOS', NULL, NULL),
(1156, '120907', 'JUNIN', 'CHUPACA', 'SAN JUAN DE JARPA', 'JARPA', NULL, NULL),
(1157, '120908', 'JUNIN', 'CHUPACA', 'TRES DE DICIEMBRE', 'TRES DE DICIEMBRE', NULL, NULL),
(1158, '120909', 'JUNIN', 'CHUPACA', 'YANACANCHA', 'YANACANCHA', NULL, NULL),
(1159, '130101', 'LA LIBERTAD', 'TRUJILLO', 'TRUJILLO', 'TRUJILLO', NULL, NULL),
(1160, '130102', 'LA LIBERTAD', 'TRUJILLO', 'EL PORVENIR', 'EL PORVENIR', NULL, NULL),
(1161, '130103', 'LA LIBERTAD', 'TRUJILLO', 'FLORENCIA DE MORA', 'FLORENCIA DE MORA', NULL, NULL),
(1162, '130104', 'LA LIBERTAD', 'TRUJILLO', 'HUANCHACO', 'HUANCHACO', NULL, NULL),
(1163, '130105', 'LA LIBERTAD', 'TRUJILLO', 'LA ESPERANZA', 'LA ESPERANZA', NULL, NULL),
(1164, '130106', 'LA LIBERTAD', 'TRUJILLO', 'LAREDO', 'LAREDO', NULL, NULL),
(1165, '130107', 'LA LIBERTAD', 'TRUJILLO', 'MOCHE', 'MOCHE', NULL, NULL),
(1166, '130108', 'LA LIBERTAD', 'TRUJILLO', 'POROTO', 'POROTO', NULL, NULL),
(1167, '130109', 'LA LIBERTAD', 'TRUJILLO', 'SALAVERRY', 'SALAVERRY', NULL, NULL),
(1168, '130110', 'LA LIBERTAD', 'TRUJILLO', 'SIMBAL', 'SIMBAL', NULL, NULL),
(1169, '130111', 'LA LIBERTAD', 'TRUJILLO', 'VICTOR LARCO HERRERA', 'BUENOS AIRES', NULL, NULL),
(1170, '130201', 'LA LIBERTAD', 'ASCOPE', 'ASCOPE', 'ASCOPE', NULL, NULL),
(1171, '130202', 'LA LIBERTAD', 'ASCOPE', 'CHICAMA', 'CHICAMA', NULL, NULL),
(1172, '130203', 'LA LIBERTAD', 'ASCOPE', 'CHOCOPE', 'CHOCOPE', NULL, NULL),
(1173, '130204', 'LA LIBERTAD', 'ASCOPE', 'MAGDALENA DE CAO', 'MAGDALENA DE CAO', NULL, NULL),
(1174, '130205', 'LA LIBERTAD', 'ASCOPE', 'PAIJAN', 'PAIJAN', NULL, NULL),
(1175, '130206', 'LA LIBERTAD', 'ASCOPE', 'RAZURI', 'PUERTO DE MALABRIGO', NULL, NULL),
(1176, '130207', 'LA LIBERTAD', 'ASCOPE', 'SANTIAGO DE CAO', 'SANTIAGO DE CAO', NULL, NULL),
(1177, '130208', 'LA LIBERTAD', 'ASCOPE', 'CASA GRANDE', 'CASA GRANDE', NULL, NULL),
(1178, '130301', 'LA LIBERTAD', 'BOLIVAR', 'BOLIVAR', 'BOLIVAR', NULL, NULL),
(1179, '130302', 'LA LIBERTAD', 'BOLIVAR', 'BAMBAMARCA', 'BAMBAMARCA', NULL, NULL),
(1180, '130303', 'LA LIBERTAD', 'BOLIVAR', 'CONDORMARCA', 'CONDORMARCA /12', NULL, NULL),
(1181, '130304', 'LA LIBERTAD', 'BOLIVAR', 'LONGOTEA', 'LONGOTEA', NULL, NULL),
(1182, '130305', 'LA LIBERTAD', 'BOLIVAR', 'UCHUMARCA', 'UCHUMARCA', NULL, NULL),
(1183, '130306', 'LA LIBERTAD', 'BOLIVAR', 'UCUNCHA', 'UCUNCHA', NULL, NULL),
(1184, '130401', 'LA LIBERTAD', 'CHEPEN', 'CHEPEN', 'CHEPEN', NULL, NULL),
(1185, '130402', 'LA LIBERTAD', 'CHEPEN', 'PACANGA', 'PACANGA', NULL, NULL),
(1186, '130403', 'LA LIBERTAD', 'CHEPEN', 'PUEBLO NUEVO', 'PUEBLO NUEVO', NULL, NULL),
(1187, '130501', 'LA LIBERTAD', 'JULCAN', 'JULCAN', 'JULCAN', NULL, NULL),
(1188, '130502', 'LA LIBERTAD', 'JULCAN', 'CALAMARCA', 'CALAMARCA', NULL, NULL),
(1189, '130503', 'LA LIBERTAD', 'JULCAN', 'CARABAMBA', 'CARABAMBA', NULL, NULL),
(1190, '130504', 'LA LIBERTAD', 'JULCAN', 'HUASO', 'HUASO', NULL, NULL),
(1191, '130601', 'LA LIBERTAD', 'OTUZCO', 'OTUZCO', 'OTUZCO', NULL, NULL),
(1192, '130602', 'LA LIBERTAD', 'OTUZCO', 'AGALLPAMPA', 'AGALLPAMPA', NULL, NULL),
(1193, '130604', 'LA LIBERTAD', 'OTUZCO', 'CHARAT', 'CHARAT', NULL, NULL),
(1194, '130605', 'LA LIBERTAD', 'OTUZCO', 'HUARANCHAL', 'HUARANCHAL', NULL, NULL),
(1195, '130606', 'LA LIBERTAD', 'OTUZCO', 'LA CUESTA', 'LA CUESTA', NULL, NULL),
(1196, '130608', 'LA LIBERTAD', 'OTUZCO', 'MACHE', 'MACHE', NULL, NULL),
(1197, '130610', 'LA LIBERTAD', 'OTUZCO', 'PARANDAY', 'PARANDAY', NULL, NULL),
(1198, '130611', 'LA LIBERTAD', 'OTUZCO', 'SALPO', 'SALPO', NULL, NULL),
(1199, '130613', 'LA LIBERTAD', 'OTUZCO', 'SINSICAP', 'SINSICAP', NULL, NULL),
(1200, '130614', 'LA LIBERTAD', 'OTUZCO', 'USQUIL', 'USQUIL', NULL, NULL),
(1201, '130701', 'LA LIBERTAD', 'PACASMAYO', 'SAN PEDRO DE LLOC', 'SAN PEDRO DE LLOC', NULL, NULL),
(1202, '130702', 'LA LIBERTAD', 'PACASMAYO', 'GUADALUPE', 'GUADALUPE', NULL, NULL),
(1203, '130703', 'LA LIBERTAD', 'PACASMAYO', 'JEQUETEPEQUE', 'JEQUETEPEQUE', NULL, NULL),
(1204, '130704', 'LA LIBERTAD', 'PACASMAYO', 'PACASMAYO', 'PACASMAYO', NULL, NULL),
(1205, '130705', 'LA LIBERTAD', 'PACASMAYO', 'SAN JOSE', 'SAN JOSE', NULL, NULL),
(1206, '130801', 'LA LIBERTAD', 'PATAZ', 'TAYABAMBA', 'TAYABAMBA', NULL, NULL),
(1207, '130802', 'LA LIBERTAD', 'PATAZ', 'BULDIBUYO', 'BULDIBUYO', NULL, NULL),
(1208, '130803', 'LA LIBERTAD', 'PATAZ', 'CHILLIA', 'CHILLIA', NULL, NULL),
(1209, '130804', 'LA LIBERTAD', 'PATAZ', 'HUANCASPATA', 'HUANCASPATA', NULL, NULL),
(1210, '130805', 'LA LIBERTAD', 'PATAZ', 'HUAYLILLAS', 'HUAYLILLAS', NULL, NULL),
(1211, '130806', 'LA LIBERTAD', 'PATAZ', 'HUAYO', 'HUAYO', NULL, NULL),
(1212, '130807', 'LA LIBERTAD', 'PATAZ', 'ONGON', 'ONGON', NULL, NULL),
(1213, '130808', 'LA LIBERTAD', 'PATAZ', 'PARCOY', 'PARCOY', NULL, NULL),
(1214, '130809', 'LA LIBERTAD', 'PATAZ', 'PATAZ', 'PATAZ', NULL, NULL),
(1215, '130810', 'LA LIBERTAD', 'PATAZ', 'PIAS', 'PIAS', NULL, NULL),
(1216, '130811', 'LA LIBERTAD', 'PATAZ', 'SANTIAGO DE CHALLAS', 'CHALLAS', NULL, NULL),
(1217, '130812', 'LA LIBERTAD', 'PATAZ', 'TAURIJA', 'TAURIJA', NULL, NULL),
(1218, '130813', 'LA LIBERTAD', 'PATAZ', 'URPAY', 'URPAY', NULL, NULL),
(1219, '130901', 'LA LIBERTAD', 'SANCHEZ CARRION', 'HUAMACHUCO', 'HUAMACHUCO', NULL, NULL),
(1220, '130902', 'LA LIBERTAD', 'SANCHEZ CARRION', 'CHUGAY', 'CHUGAY', NULL, NULL),
(1221, '130903', 'LA LIBERTAD', 'SANCHEZ CARRION', 'COCHORCO', 'ARICAPAMPA', NULL, NULL),
(1222, '130904', 'LA LIBERTAD', 'SANCHEZ CARRION', 'CURGOS', 'CURGOS', NULL, NULL),
(1223, '130905', 'LA LIBERTAD', 'SANCHEZ CARRION', 'MARCABAL', 'MARCABAL', NULL, NULL),
(1224, '130906', 'LA LIBERTAD', 'SANCHEZ CARRION', 'SANAGORAN', 'SANAGORAN', NULL, NULL),
(1225, '130907', 'LA LIBERTAD', 'SANCHEZ CARRION', 'SARIN', 'SARIN', NULL, NULL),
(1226, '130908', 'LA LIBERTAD', 'SANCHEZ CARRION', 'SARTIMBAMBA', 'SARTIMBAMBA', NULL, NULL),
(1227, '131001', 'LA LIBERTAD', 'SANTIAGO DE CHUCO', 'SANTIAGO DE CHUCO', 'SANTIAGO DE CHUCO', NULL, NULL),
(1228, '131002', 'LA LIBERTAD', 'SANTIAGO DE CHUCO', 'ANGASMARCA', 'ANGASMARCA', NULL, NULL),
(1229, '131003', 'LA LIBERTAD', 'SANTIAGO DE CHUCO', 'CACHICADAN', 'CACHICADAN', NULL, NULL),
(1230, '131004', 'LA LIBERTAD', 'SANTIAGO DE CHUCO', 'MOLLEBAMBA', 'MOLLEBAMBA', NULL, NULL),
(1231, '131005', 'LA LIBERTAD', 'SANTIAGO DE CHUCO', 'MOLLEPATA', 'MOLLEPATA', NULL, NULL),
(1232, '131006', 'LA LIBERTAD', 'SANTIAGO DE CHUCO', 'QUIRUVILCA', 'QUIRUVILCA', NULL, NULL),
(1233, '131007', 'LA LIBERTAD', 'SANTIAGO DE CHUCO', 'SANTA CRUZ DE CHUCA', 'SANTA CRUZ DE CHUCA', NULL, NULL),
(1234, '131008', 'LA LIBERTAD', 'SANTIAGO DE CHUCO', 'SITABAMBA', 'SITABAMBA', NULL, NULL),
(1235, '131101', 'LA LIBERTAD', 'GRAN CHIMU', 'CASCAS', 'CASCAS', NULL, NULL),
(1236, '131102', 'LA LIBERTAD', 'GRAN CHIMU', 'LUCMA', 'LUCMA', NULL, NULL),
(1237, '131103', 'LA LIBERTAD', 'GRAN CHIMU', 'MARMOT', 'MARMOT /13', NULL, NULL),
(1238, '131104', 'LA LIBERTAD', 'GRAN CHIMU', 'SAYAPULLO', 'SAYAPULLO', NULL, NULL),
(1239, '131201', 'LA LIBERTAD', 'VIRU', 'VIRU', 'VIRU', NULL, NULL),
(1240, '131202', 'LA LIBERTAD', 'VIRU', 'CHAO', 'CHAO', NULL, NULL),
(1241, '131203', 'LA LIBERTAD', 'VIRU', 'GUADALUPITO', 'GUADALUPITO', NULL, NULL),
(1242, '140101', 'LAMBAYEQUE', 'CHICLAYO', 'CHICLAYO', 'CHICLAYO', NULL, NULL),
(1243, '140102', 'LAMBAYEQUE', 'CHICLAYO', 'CHONGOYAPE', 'CHONGOYAPE', NULL, NULL),
(1244, '140103', 'LAMBAYEQUE', 'CHICLAYO', 'ETEN', 'ETEN', NULL, NULL),
(1245, '140104', 'LAMBAYEQUE', 'CHICLAYO', 'ETEN PUERTO', 'ETEN PUERTO', NULL, NULL),
(1246, '140105', 'LAMBAYEQUE', 'CHICLAYO', 'JOSE LEONARDO ORTIZ', 'JOSE LEONARDO ORTIZ', NULL, NULL),
(1247, '140106', 'LAMBAYEQUE', 'CHICLAYO', 'LA VICTORIA', 'LA VICTORIA', NULL, NULL),
(1248, '140107', 'LAMBAYEQUE', 'CHICLAYO', 'LAGUNAS', 'MOCUPE', NULL, NULL),
(1249, '140108', 'LAMBAYEQUE', 'CHICLAYO', 'MONSEFU', 'MONSEFU', NULL, NULL),
(1250, '140109', 'LAMBAYEQUE', 'CHICLAYO', 'NUEVA ARICA', 'NUEVA ARICA', NULL, NULL),
(1251, '140110', 'LAMBAYEQUE', 'CHICLAYO', 'OYOTUN', 'OYOTUN', NULL, NULL),
(1252, '140111', 'LAMBAYEQUE', 'CHICLAYO', 'PICSI', 'PICSI', NULL, NULL),
(1253, '140112', 'LAMBAYEQUE', 'CHICLAYO', 'PIMENTEL', 'PIMENTEL', NULL, NULL),
(1254, '140113', 'LAMBAYEQUE', 'CHICLAYO', 'REQUE', 'REQUE', NULL, NULL),
(1255, '140114', 'LAMBAYEQUE', 'CHICLAYO', 'SANTA ROSA', 'SANTA ROSA', NULL, NULL),
(1256, '140115', 'LAMBAYEQUE', 'CHICLAYO', 'SAÑA', 'SAÑA', NULL, NULL),
(1257, '140116', 'LAMBAYEQUE', 'CHICLAYO', 'CAYALTI', 'CAYALTI', NULL, NULL),
(1258, '140117', 'LAMBAYEQUE', 'CHICLAYO', 'PATAPO', 'PATAPO', NULL, NULL),
(1259, '140118', 'LAMBAYEQUE', 'CHICLAYO', 'POMALCA', 'POMALCA', NULL, NULL),
(1260, '140119', 'LAMBAYEQUE', 'CHICLAYO', 'PUCALA', 'PUCALA', NULL, NULL),
(1261, '140120', 'LAMBAYEQUE', 'CHICLAYO', 'TUMAN', 'TUMAN', NULL, NULL),
(1262, '140201', 'LAMBAYEQUE', 'FERREÑAFE', 'FERREÑAFE', 'FERREÑAFE', NULL, NULL),
(1263, '140202', 'LAMBAYEQUE', 'FERREÑAFE', 'CAÑARIS', 'CAÑARIS', NULL, NULL),
(1264, '140203', 'LAMBAYEQUE', 'FERREÑAFE', 'INCAHUASI', 'INCAHUASI', NULL, NULL),
(1265, '140204', 'LAMBAYEQUE', 'FERREÑAFE', 'MANUEL ANTONIO MESONES MURO', 'MANUEL ANTONIO MESONES MURO', NULL, NULL),
(1266, '140205', 'LAMBAYEQUE', 'FERREÑAFE', 'PITIPO', 'PITIPO', NULL, NULL),
(1267, '140206', 'LAMBAYEQUE', 'FERREÑAFE', 'PUEBLO NUEVO', 'PUEBLO NUEVO', NULL, NULL),
(1268, '140301', 'LAMBAYEQUE', 'LAMBAYEQUE', 'LAMBAYEQUE', 'LAMBAYEQUE', NULL, NULL),
(1269, '140302', 'LAMBAYEQUE', 'LAMBAYEQUE', 'CHOCHOPE', 'CHOCHOPE', NULL, NULL),
(1270, '140303', 'LAMBAYEQUE', 'LAMBAYEQUE', 'ILLIMO', 'ILLIMO', NULL, NULL),
(1271, '140304', 'LAMBAYEQUE', 'LAMBAYEQUE', 'JAYANCA', 'JAYANCA', NULL, NULL),
(1272, '140305', 'LAMBAYEQUE', 'LAMBAYEQUE', 'MOCHUMI', 'MOCHUMI', NULL, NULL),
(1273, '140306', 'LAMBAYEQUE', 'LAMBAYEQUE', 'MORROPE', 'MORROPE', NULL, NULL),
(1274, '140307', 'LAMBAYEQUE', 'LAMBAYEQUE', 'MOTUPE', 'MOTUPE', NULL, NULL),
(1275, '140308', 'LAMBAYEQUE', 'LAMBAYEQUE', 'OLMOS', 'OLMOS', NULL, NULL),
(1276, '140309', 'LAMBAYEQUE', 'LAMBAYEQUE', 'PACORA', 'PACORA', NULL, NULL),
(1277, '140310', 'LAMBAYEQUE', 'LAMBAYEQUE', 'SALAS', 'SALAS', NULL, NULL),
(1278, '140311', 'LAMBAYEQUE', 'LAMBAYEQUE', 'SAN JOSE', 'SAN JOSE', NULL, NULL),
(1279, '140312', 'LAMBAYEQUE', 'LAMBAYEQUE', 'TUCUME', 'TUCUME', NULL, NULL),
(1280, '150101', 'LIMA', 'LIMA', 'LIMA', 'LIMA', NULL, NULL),
(1281, '150102', 'LIMA', 'LIMA', 'ANCON', 'ANCON', NULL, NULL),
(1282, '150103', 'LIMA', 'LIMA', 'ATE', 'VITARTE', NULL, NULL),
(1283, '150104', 'LIMA', 'LIMA', 'BARRANCO', 'BARRANCO', NULL, NULL),
(1284, '150105', 'LIMA', 'LIMA', 'BREÑA', 'BREÑA', NULL, NULL),
(1285, '150106', 'LIMA', 'LIMA', 'CARABAYLLO', 'CARABAYLLO', NULL, NULL),
(1286, '150107', 'LIMA', 'LIMA', 'CHACLACAYO', 'CHACLACAYO', NULL, NULL),
(1287, '150108', 'LIMA', 'LIMA', 'CHORRILLOS', 'CHORRILLOS', NULL, NULL),
(1288, '150109', 'LIMA', 'LIMA', 'CIENEGUILLA', 'CIENEGUILLA', NULL, NULL),
(1289, '150110', 'LIMA', 'LIMA', 'COMAS', 'LA LIBERTAD', NULL, NULL),
(1290, '150111', 'LIMA', 'LIMA', 'EL AGUSTINO', 'EL AGUSTINO', NULL, NULL),
(1291, '150112', 'LIMA', 'LIMA', 'INDEPENDENCIA', 'INDEPENDENCIA', NULL, NULL),
(1292, '150113', 'LIMA', 'LIMA', 'JESUS MARIA', 'JESUS MARIA', NULL, NULL),
(1293, '150114', 'LIMA', 'LIMA', 'LA MOLINA', 'LA MOLINA', NULL, NULL),
(1294, '150115', 'LIMA', 'LIMA', 'LA VICTORIA', 'LA VICTORIA', NULL, NULL),
(1295, '150116', 'LIMA', 'LIMA', 'LINCE', 'LINCE', NULL, NULL),
(1296, '150117', 'LIMA', 'LIMA', 'LOS OLIVOS', 'LAS PALMERAS', NULL, NULL),
(1297, '150118', 'LIMA', 'LIMA', 'LURIGANCHO', 'CHOSICA', NULL, NULL),
(1298, '150119', 'LIMA', 'LIMA', 'LURIN', 'LURIN', NULL, NULL),
(1299, '150120', 'LIMA', 'LIMA', 'MAGDALENA DEL MAR', 'MAGDALENA DEL MAR', NULL, NULL),
(1300, '150121', 'LIMA', 'LIMA', 'PUEBLO LIBRE', 'PUEBLO LIBRE', NULL, NULL),
(1301, '150122', 'LIMA', 'LIMA', 'MIRAFLORES', 'MIRAFLORES', NULL, NULL),
(1302, '150123', 'LIMA', 'LIMA', 'PACHACAMAC', 'PACHACAMAC', NULL, NULL),
(1303, '150124', 'LIMA', 'LIMA', 'PUCUSANA', 'PUCUSANA', NULL, NULL),
(1304, '150125', 'LIMA', 'LIMA', 'PUENTE PIEDRA', 'PUENTE PIEDRA', NULL, NULL),
(1305, '150126', 'LIMA', 'LIMA', 'PUNTA HERMOSA', 'PUNTA HERMOSA', NULL, NULL),
(1306, '150127', 'LIMA', 'LIMA', 'PUNTA NEGRA', 'PUNTA NEGRA', NULL, NULL);
INSERT INTO `ubigeos` (`id_ubigeo`, `ubigeo_cod`, `ubigeo_departamento`, `ubigeo_provincia`, `ubigeo_distrito`, `ubigeo_capital`, `created_at`, `updated_at`) VALUES
(1307, '150128', 'LIMA', 'LIMA', 'RIMAC', 'RIMAC', NULL, NULL),
(1308, '150129', 'LIMA', 'LIMA', 'SAN BARTOLO', 'SAN BARTOLO', NULL, NULL),
(1309, '150130', 'LIMA', 'LIMA', 'SAN BORJA', 'SAN FRANCISCO DE BORJA', NULL, NULL),
(1310, '150131', 'LIMA', 'LIMA', 'SAN ISIDRO', 'SAN ISIDRO', NULL, NULL),
(1311, '150132', 'LIMA', 'LIMA', 'SAN JUAN DE LURIGANCHO', 'SAN JUAN DE LURIGANCHO', NULL, NULL),
(1312, '150133', 'LIMA', 'LIMA', 'SAN JUAN DE MIRAFLORES', 'CIUDAD DE DIOS', NULL, NULL),
(1313, '150134', 'LIMA', 'LIMA', 'SAN LUIS', 'SAN LUIS', NULL, NULL),
(1314, '150135', 'LIMA', 'LIMA', 'SAN MARTIN DE PORRES', 'BARRIO OBRERO INDUSTRIAL', NULL, NULL),
(1315, '150136', 'LIMA', 'LIMA', 'SAN MIGUEL', 'SAN MIGUEL', NULL, NULL),
(1316, '150137', 'LIMA', 'LIMA', 'SANTA ANITA', 'SANTA ANITA - LOS FICUS', NULL, NULL),
(1317, '150138', 'LIMA', 'LIMA', 'SANTA MARIA DEL MAR', 'SANTA MARIA DEL MAR', NULL, NULL),
(1318, '150139', 'LIMA', 'LIMA', 'SANTA ROSA', 'SANTA ROSA', NULL, NULL),
(1319, '150140', 'LIMA', 'LIMA', 'SANTIAGO DE SURCO', 'SANTIAGO DE SURCO', NULL, NULL),
(1320, '150141', 'LIMA', 'LIMA', 'SURQUILLO', 'SURQUILLO', NULL, NULL),
(1321, '150142', 'LIMA', 'LIMA', 'VILLA EL SALVADOR', 'VILLA EL SALVADOR', NULL, NULL),
(1322, '150143', 'LIMA', 'LIMA', 'VILLA MARIA DEL TRIUNFO', 'VILLA MARIA DEL TRIUNFO', NULL, NULL),
(1323, '150201', 'LIMA', 'BARRANCA', 'BARRANCA', 'BARRANCA', NULL, NULL),
(1324, '150202', 'LIMA', 'BARRANCA', 'PARAMONGA', 'PARAMONGA', NULL, NULL),
(1325, '150203', 'LIMA', 'BARRANCA', 'PATIVILCA', 'PATIVILCA', NULL, NULL),
(1326, '150204', 'LIMA', 'BARRANCA', 'SUPE', 'SUPE', NULL, NULL),
(1327, '150205', 'LIMA', 'BARRANCA', 'SUPE PUERTO', 'SUPE PUERTO', NULL, NULL),
(1328, '150301', 'LIMA', 'CAJATAMBO', 'CAJATAMBO', 'CAJATAMBO', NULL, NULL),
(1329, '150302', 'LIMA', 'CAJATAMBO', 'COPA', 'COPA', NULL, NULL),
(1330, '150303', 'LIMA', 'CAJATAMBO', 'GORGOR', 'GORGOR', NULL, NULL),
(1331, '150304', 'LIMA', 'CAJATAMBO', 'HUANCAPON', 'HUANCAPON', NULL, NULL),
(1332, '150305', 'LIMA', 'CAJATAMBO', 'MANAS', 'MANAS', NULL, NULL),
(1333, '150401', 'LIMA', 'CANTA', 'CANTA', 'CANTA', NULL, NULL),
(1334, '150402', 'LIMA', 'CANTA', 'ARAHUAY', 'ARAHUAY', NULL, NULL),
(1335, '150403', 'LIMA', 'CANTA', 'HUAMANTANGA', 'HUAMANTANGA', NULL, NULL),
(1336, '150404', 'LIMA', 'CANTA', 'HUAROS', 'HUAROS', NULL, NULL),
(1337, '150405', 'LIMA', 'CANTA', 'LACHAQUI', 'LACHAQUI', NULL, NULL),
(1338, '150406', 'LIMA', 'CANTA', 'SAN BUENAVENTURA', 'SAN BUENAVENTURA', NULL, NULL),
(1339, '150407', 'LIMA', 'CANTA', 'SANTA ROSA DE QUIVES', 'YANGAS', NULL, NULL),
(1340, '150501', 'LIMA', 'CAÑETE', 'SAN VICENTE DE CAÑETE', 'SAN VICENTE DE CAÑETE', NULL, NULL),
(1341, '150502', 'LIMA', 'CAÑETE', 'ASIA', 'ASIA', NULL, NULL),
(1342, '150503', 'LIMA', 'CAÑETE', 'CALANGO', 'CALANGO', NULL, NULL),
(1343, '150504', 'LIMA', 'CAÑETE', 'CERRO AZUL', 'CERRO AZUL', NULL, NULL),
(1344, '150505', 'LIMA', 'CAÑETE', 'CHILCA', 'CHILCA', NULL, NULL),
(1345, '150506', 'LIMA', 'CAÑETE', 'COAYLLO', 'COAYLLO', NULL, NULL),
(1346, '150507', 'LIMA', 'CAÑETE', 'IMPERIAL', 'IMPERIAL', NULL, NULL),
(1347, '150508', 'LIMA', 'CAÑETE', 'LUNAHUANA', 'LUNAHUANA', NULL, NULL),
(1348, '150509', 'LIMA', 'CAÑETE', 'MALA', 'MALA', NULL, NULL),
(1349, '150510', 'LIMA', 'CAÑETE', 'NUEVO IMPERIAL', 'NUEVO IMPERIAL', NULL, NULL),
(1350, '150511', 'LIMA', 'CAÑETE', 'PACARAN', 'PACARAN', NULL, NULL),
(1351, '150512', 'LIMA', 'CAÑETE', 'QUILMANA', 'QUILMANA', NULL, NULL),
(1352, '150513', 'LIMA', 'CAÑETE', 'SAN ANTONIO', 'SAN ANTONIO', NULL, NULL),
(1353, '150514', 'LIMA', 'CAÑETE', 'SAN LUIS', 'SAN LUIS', NULL, NULL),
(1354, '150515', 'LIMA', 'CAÑETE', 'SANTA CRUZ DE FLORES', 'SANTA CRUZ DE FLORES', NULL, NULL),
(1355, '150516', 'LIMA', 'CAÑETE', 'ZUÑIGA', 'ZUÑIGA', NULL, NULL),
(1356, '150601', 'LIMA', 'HUARAL', 'HUARAL', 'HUARAL', NULL, NULL),
(1357, '150602', 'LIMA', 'HUARAL', 'ATAVILLOS ALTO', 'PIRCA', NULL, NULL),
(1358, '150603', 'LIMA', 'HUARAL', 'ATAVILLOS BAJO', 'SAN AGUSTIN DE HUAYOPAMPA', NULL, NULL),
(1359, '150604', 'LIMA', 'HUARAL', 'AUCALLAMA', 'AUCALLAMA', NULL, NULL),
(1360, '150605', 'LIMA', 'HUARAL', 'CHANCAY', 'CHANCAY', NULL, NULL),
(1361, '150606', 'LIMA', 'HUARAL', 'IHUARI', 'IHUARI', NULL, NULL),
(1362, '150607', 'LIMA', 'HUARAL', 'LAMPIAN', 'LAMPIAN', NULL, NULL),
(1363, '150608', 'LIMA', 'HUARAL', 'PACARAOS', 'PACARAOS', NULL, NULL),
(1364, '150609', 'LIMA', 'HUARAL', 'SAN MIGUEL DE ACOS', 'ACOS', NULL, NULL),
(1365, '150610', 'LIMA', 'HUARAL', 'SANTA CRUZ DE ANDAMARCA', 'SANTA CRUZ DE ANDAMARCA', NULL, NULL),
(1366, '150611', 'LIMA', 'HUARAL', 'SUMBILCA', 'SUMBILCA', NULL, NULL),
(1367, '150612', 'LIMA', 'HUARAL', 'VEINTISIETE DE NOVIEMBRE', 'CARAC', NULL, NULL),
(1368, '150701', 'LIMA', 'HUAROCHIRI', 'MATUCANA', 'MATUCANA', NULL, NULL),
(1369, '150702', 'LIMA', 'HUAROCHIRI', 'ANTIOQUIA', 'ANTIOQUIA', NULL, NULL),
(1370, '150703', 'LIMA', 'HUAROCHIRI', 'CALLAHUANCA', 'CALLAHUANCA', NULL, NULL),
(1371, '150704', 'LIMA', 'HUAROCHIRI', 'CARAMPOMA', 'CARAMPOMA', NULL, NULL),
(1372, '150705', 'LIMA', 'HUAROCHIRI', 'CHICLA', 'CHICLA', NULL, NULL),
(1373, '150706', 'LIMA', 'HUAROCHIRI', 'CUENCA', 'SAN JOSE DE LOS CHORRILLOS', NULL, NULL),
(1374, '150707', 'LIMA', 'HUAROCHIRI', 'HUACHUPAMPA', 'SAN LORENZO DE HUACHUPAMPA', NULL, NULL),
(1375, '150708', 'LIMA', 'HUAROCHIRI', 'HUANZA', 'HUANZA', NULL, NULL),
(1376, '150709', 'LIMA', 'HUAROCHIRI', 'HUAROCHIRI', 'HUAROCHIRI', NULL, NULL),
(1377, '150710', 'LIMA', 'HUAROCHIRI', 'LAHUAYTAMBO', 'LAHUAYTAMBO', NULL, NULL),
(1378, '150711', 'LIMA', 'HUAROCHIRI', 'LANGA', 'LANGA', NULL, NULL),
(1379, '150712', 'LIMA', 'HUAROCHIRI', 'SAN PEDRO DE LARAOS', 'LARAOS', NULL, NULL),
(1380, '150713', 'LIMA', 'HUAROCHIRI', 'MARIATANA', 'MARIATANA', NULL, NULL),
(1381, '150714', 'LIMA', 'HUAROCHIRI', 'RICARDO PALMA', 'RICARDO PALMA', NULL, NULL),
(1382, '150715', 'LIMA', 'HUAROCHIRI', 'SAN ANDRES DE TUPICOCHA', 'SAN ANDRES DE TUPICOCHA', NULL, NULL),
(1383, '150716', 'LIMA', 'HUAROCHIRI', 'SAN ANTONIO', 'CHACLLA', NULL, NULL),
(1384, '150717', 'LIMA', 'HUAROCHIRI', 'SAN BARTOLOME', 'SAN BARTOLOME', NULL, NULL),
(1385, '150718', 'LIMA', 'HUAROCHIRI', 'SAN DAMIAN', 'SAN DAMIAN', NULL, NULL),
(1386, '150719', 'LIMA', 'HUAROCHIRI', 'SAN JUAN DE IRIS', 'SAN JUAN DE IRIS', NULL, NULL),
(1387, '150720', 'LIMA', 'HUAROCHIRI', 'SAN JUAN DE TANTARANCHE', 'SAN JUAN DE TANTARANCHE', NULL, NULL),
(1388, '150721', 'LIMA', 'HUAROCHIRI', 'SAN LORENZO DE QUINTI', 'SAN LORENZO DE QUINTI', NULL, NULL),
(1389, '150722', 'LIMA', 'HUAROCHIRI', 'SAN MATEO', 'SAN MATEO', NULL, NULL),
(1390, '150723', 'LIMA', 'HUAROCHIRI', 'SAN MATEO DE OTAO', 'SAN JUAN DE LANCA', NULL, NULL),
(1391, '150724', 'LIMA', 'HUAROCHIRI', 'SAN PEDRO DE CASTA', 'SAN PEDRO DE CASTA', NULL, NULL),
(1392, '150725', 'LIMA', 'HUAROCHIRI', 'SAN PEDRO DE HUANCAYRE', 'SAN PEDRO', NULL, NULL),
(1393, '150726', 'LIMA', 'HUAROCHIRI', 'SANGALLAYA', 'SANGALLAYA', NULL, NULL),
(1394, '150727', 'LIMA', 'HUAROCHIRI', 'SANTA CRUZ DE COCACHACRA', 'COCACHACRA', NULL, NULL),
(1395, '150728', 'LIMA', 'HUAROCHIRI', 'SANTA EULALIA', 'SANTA EULALIA', NULL, NULL),
(1396, '150729', 'LIMA', 'HUAROCHIRI', 'SANTIAGO DE ANCHUCAYA', 'SANTIAGO DE ANCHUCAYA', NULL, NULL),
(1397, '150730', 'LIMA', 'HUAROCHIRI', 'SANTIAGO DE TUNA', 'SANTIAGO DE TUNA', NULL, NULL),
(1398, '150731', 'LIMA', 'HUAROCHIRI', 'SANTO DOMINGO DE LOS OLLEROS', 'SANTO DOMINGO DE LOS OLLEROS', NULL, NULL),
(1399, '150732', 'LIMA', 'HUAROCHIRI', 'SURCO', 'SURCO', NULL, NULL),
(1400, '150801', 'LIMA', 'HUAURA', 'HUACHO', 'HUACHO', NULL, NULL),
(1401, '150802', 'LIMA', 'HUAURA', 'AMBAR', 'AMBAR', NULL, NULL),
(1402, '150803', 'LIMA', 'HUAURA', 'CALETA DE CARQUIN', 'CALETA DE CARQUIN', NULL, NULL),
(1403, '150804', 'LIMA', 'HUAURA', 'CHECRAS', 'MARAY', NULL, NULL),
(1404, '150805', 'LIMA', 'HUAURA', 'HUALMAY', 'HUALMAY', NULL, NULL),
(1405, '150806', 'LIMA', 'HUAURA', 'HUAURA', 'HUAURA', NULL, NULL),
(1406, '150807', 'LIMA', 'HUAURA', 'LEONCIO PRADO', 'SANTA CRUZ', NULL, NULL),
(1407, '150808', 'LIMA', 'HUAURA', 'PACCHO', 'PACCHO', NULL, NULL),
(1408, '150809', 'LIMA', 'HUAURA', 'SANTA LEONOR', 'JUCUL', NULL, NULL),
(1409, '150810', 'LIMA', 'HUAURA', 'SANTA MARIA', 'CRUZ BLANCA', NULL, NULL),
(1410, '150811', 'LIMA', 'HUAURA', 'SAYAN', 'SAYAN', NULL, NULL),
(1411, '150812', 'LIMA', 'HUAURA', 'VEGUETA', 'VEGUETA', NULL, NULL),
(1412, '150901', 'LIMA', 'OYON', 'OYON', 'OYON', NULL, NULL),
(1413, '150902', 'LIMA', 'OYON', 'ANDAJES', 'ANDAJES', NULL, NULL),
(1414, '150903', 'LIMA', 'OYON', 'CAUJUL', 'CAUJUL', NULL, NULL),
(1415, '150904', 'LIMA', 'OYON', 'COCHAMARCA', 'COCHAMARCA', NULL, NULL),
(1416, '150905', 'LIMA', 'OYON', 'NAVAN', 'NAVAN', NULL, NULL),
(1417, '150906', 'LIMA', 'OYON', 'PACHANGARA', 'CHURIN', NULL, NULL),
(1418, '151001', 'LIMA', 'YAUYOS', 'YAUYOS', 'YAUYOS', NULL, NULL),
(1419, '151002', 'LIMA', 'YAUYOS', 'ALIS', 'ALIS', NULL, NULL),
(1420, '151003', 'LIMA', 'YAUYOS', 'ALLAUCA', 'ALLAUCA', NULL, NULL),
(1421, '151004', 'LIMA', 'YAUYOS', 'AYAVIRI', 'AYAVIRI', NULL, NULL),
(1422, '151005', 'LIMA', 'YAUYOS', 'AZANGARO', 'AZANGARO', NULL, NULL),
(1423, '151006', 'LIMA', 'YAUYOS', 'CACRA', 'CACRA', NULL, NULL),
(1424, '151007', 'LIMA', 'YAUYOS', 'CARANIA', 'CARANIA', NULL, NULL),
(1425, '151008', 'LIMA', 'YAUYOS', 'CATAHUASI', 'CATAHUASI', NULL, NULL),
(1426, '151009', 'LIMA', 'YAUYOS', 'CHOCOS', 'CHOCOS', NULL, NULL),
(1427, '151010', 'LIMA', 'YAUYOS', 'COCHAS', 'COCHAS', NULL, NULL),
(1428, '151011', 'LIMA', 'YAUYOS', 'COLONIA', 'COLONIA', NULL, NULL),
(1429, '151012', 'LIMA', 'YAUYOS', 'HONGOS', 'HONGOS', NULL, NULL),
(1430, '151013', 'LIMA', 'YAUYOS', 'HUAMPARA', 'HUAMPARA', NULL, NULL),
(1431, '151014', 'LIMA', 'YAUYOS', 'HUANCAYA', 'HUANCAYA', NULL, NULL),
(1432, '151015', 'LIMA', 'YAUYOS', 'HUANGASCAR', 'HUANGASCAR', NULL, NULL),
(1433, '151016', 'LIMA', 'YAUYOS', 'HUANTAN', 'HUANTAN', NULL, NULL),
(1434, '151017', 'LIMA', 'YAUYOS', 'HUAÑEC', 'HUAÑEC', NULL, NULL),
(1435, '151018', 'LIMA', 'YAUYOS', 'LARAOS', 'LARAOS', NULL, NULL),
(1436, '151019', 'LIMA', 'YAUYOS', 'LINCHA', 'LINCHA', NULL, NULL),
(1437, '151020', 'LIMA', 'YAUYOS', 'MADEAN', 'MADEAN', NULL, NULL),
(1438, '151021', 'LIMA', 'YAUYOS', 'MIRAFLORES', 'MIRAFLORES', NULL, NULL),
(1439, '151022', 'LIMA', 'YAUYOS', 'OMAS', 'OMAS', NULL, NULL),
(1440, '151023', 'LIMA', 'YAUYOS', 'PUTINZA', 'SAN LORENZO DE PUTINZA', NULL, NULL),
(1441, '151024', 'LIMA', 'YAUYOS', 'QUINCHES', 'QUINCHES', NULL, NULL),
(1442, '151025', 'LIMA', 'YAUYOS', 'QUINOCAY', 'QUINOCAY', NULL, NULL),
(1443, '151026', 'LIMA', 'YAUYOS', 'SAN JOAQUIN', 'SAN JOAQUIN', NULL, NULL),
(1444, '151027', 'LIMA', 'YAUYOS', 'SAN PEDRO DE PILAS', 'SAN PEDRO DE PILAS', NULL, NULL),
(1445, '151028', 'LIMA', 'YAUYOS', 'TANTA', 'TANTA', NULL, NULL),
(1446, '151029', 'LIMA', 'YAUYOS', 'TAURIPAMPA', 'TAURIPAMPA', NULL, NULL),
(1447, '151030', 'LIMA', 'YAUYOS', 'TOMAS', 'TOMAS', NULL, NULL),
(1448, '151031', 'LIMA', 'YAUYOS', 'TUPE', 'TUPE', NULL, NULL),
(1449, '151032', 'LIMA', 'YAUYOS', 'VIÑAC', 'VIÑAC', NULL, NULL),
(1450, '151033', 'LIMA', 'YAUYOS', 'VITIS', 'VITIS', NULL, NULL),
(1451, '160101', 'LORETO', 'MAYNAS', 'IQUITOS', 'IQUITOS', NULL, NULL),
(1452, '160102', 'LORETO', 'MAYNAS', 'ALTO NANAY', 'SANTA MARIA DE NANAY', NULL, NULL),
(1453, '160103', 'LORETO', 'MAYNAS', 'FERNANDO LORES', 'TAMSHIYACU', NULL, NULL),
(1454, '160104', 'LORETO', 'MAYNAS', 'INDIANA', 'INDIANA', NULL, NULL),
(1455, '160105', 'LORETO', 'MAYNAS', 'LAS AMAZONAS', 'FRANCISCO DE ORELLANA', NULL, NULL),
(1456, '160106', 'LORETO', 'MAYNAS', 'MAZAN', 'MAZAN', NULL, NULL),
(1457, '160107', 'LORETO', 'MAYNAS', 'NAPO', 'SANTA CLOTILDE', NULL, NULL),
(1458, '160108', 'LORETO', 'MAYNAS', 'PUNCHANA', 'PUNCHANA', NULL, NULL),
(1459, '160110', 'LORETO', 'MAYNAS', 'TORRES CAUSANA', 'PANTOJA', NULL, NULL),
(1460, '160112', 'LORETO', 'MAYNAS', 'BELEN', 'BELEN', NULL, NULL),
(1461, '160113', 'LORETO', 'MAYNAS', 'SAN JUAN BAUTISTA', 'SAN JUAN', NULL, NULL),
(1462, '160201', 'LORETO', 'ALTO AMAZONAS', 'YURIMAGUAS', 'YURIMAGUAS', NULL, NULL),
(1463, '160202', 'LORETO', 'ALTO AMAZONAS', 'BALSAPUERTO', 'BALSAPUERTO', NULL, NULL),
(1464, '160205', 'LORETO', 'ALTO AMAZONAS', 'JEBEROS', 'JEBEROS', NULL, NULL),
(1465, '160206', 'LORETO', 'ALTO AMAZONAS', 'LAGUNAS', 'LAGUNAS', NULL, NULL),
(1466, '160210', 'LORETO', 'ALTO AMAZONAS', 'SANTA CRUZ', 'SANTA CRUZ', NULL, NULL),
(1467, '160211', 'LORETO', 'ALTO AMAZONAS', 'TENIENTE CESAR LOPEZ ROJAS', 'SHUCUSHUYACU', NULL, NULL),
(1468, '160301', 'LORETO', 'LORETO', 'NAUTA', 'NAUTA', NULL, NULL),
(1469, '160302', 'LORETO', 'LORETO', 'PARINARI', 'PARINARI', NULL, NULL),
(1470, '160303', 'LORETO', 'LORETO', 'TIGRE', 'INTUTU', NULL, NULL),
(1471, '160304', 'LORETO', 'LORETO', 'TROMPETEROS', 'VILLA TROMPETEROS', NULL, NULL),
(1472, '160305', 'LORETO', 'LORETO', 'URARINAS', 'CONCORDIA', NULL, NULL),
(1473, '160401', 'LORETO', 'MARISCAL RAMON CASTILLA', 'RAMON CASTILLA', 'CABALLOCOCHA', NULL, NULL),
(1474, '160402', 'LORETO', 'MARISCAL RAMON CASTILLA', 'PEBAS', 'PEBAS', NULL, NULL),
(1475, '160403', 'LORETO', 'MARISCAL RAMON CASTILLA', 'YAVARI', 'AMELIA /14', NULL, NULL),
(1476, '160404', 'LORETO', 'MARISCAL RAMON CASTILLA', 'SAN PABLO', 'SAN PABLO DE LORETO', NULL, NULL),
(1477, '160501', 'LORETO', 'REQUENA', 'REQUENA', 'REQUENA', NULL, NULL),
(1478, '160502', 'LORETO', 'REQUENA', 'ALTO TAPICHE', 'SANTA ELENA', NULL, NULL),
(1479, '160503', 'LORETO', 'REQUENA', 'CAPELO', 'FLOR DE PUNGA', NULL, NULL),
(1480, '160504', 'LORETO', 'REQUENA', 'EMILIO SAN MARTIN', 'TAMANCO', NULL, NULL),
(1481, '160505', 'LORETO', 'REQUENA', 'MAQUIA', 'SANTA ISABEL', NULL, NULL),
(1482, '160506', 'LORETO', 'REQUENA', 'PUINAHUA', 'BRETAÑA', NULL, NULL),
(1483, '160507', 'LORETO', 'REQUENA', 'SAQUENA', 'BAGAZAN', NULL, NULL),
(1484, '160508', 'LORETO', 'REQUENA', 'SOPLIN', 'NUEVA ALEJANDRIA (CURINGA)', NULL, NULL),
(1485, '160509', 'LORETO', 'REQUENA', 'TAPICHE', 'IBERIA', NULL, NULL),
(1486, '160510', 'LORETO', 'REQUENA', 'JENARO HERRERA', 'JENARO HERRERA', NULL, NULL),
(1487, '160511', 'LORETO', 'REQUENA', 'YAQUERANA', 'ANGAMOS', NULL, NULL),
(1488, '160601', 'LORETO', 'UCAYALI', 'CONTAMANA', 'CONTAMANA', NULL, NULL),
(1489, '160602', 'LORETO', 'UCAYALI', 'INAHUAYA', 'INAHUAYA', NULL, NULL),
(1490, '160603', 'LORETO', 'UCAYALI', 'PADRE MARQUEZ', 'TIRUNTAN', NULL, NULL),
(1491, '160604', 'LORETO', 'UCAYALI', 'PAMPA HERMOSA', 'PAMPA HERMOSA', NULL, NULL),
(1492, '160605', 'LORETO', 'UCAYALI', 'SARAYACU', 'DOS DE MAYO', NULL, NULL),
(1493, '160606', 'LORETO', 'UCAYALI', 'VARGAS GUERRA', 'ORELLANA', NULL, NULL),
(1494, '160701', 'LORETO', 'DATEM DEL MARAÑON', 'BARRANCA', 'SAN LORENZO', NULL, NULL),
(1495, '160702', 'LORETO', 'DATEM DEL MARAÑON', 'CAHUAPANAS', 'SANTA MARIA DE CAHUAPANAS', NULL, NULL),
(1496, '160703', 'LORETO', 'DATEM DEL MARAÑON', 'MANSERICHE', 'SARAMIRIZA', NULL, NULL),
(1497, '160704', 'LORETO', 'DATEM DEL MARAÑON', 'MORONA', 'PUERTO ALEGRIA', NULL, NULL),
(1498, '160705', 'LORETO', 'DATEM DEL MARAÑON', 'PASTAZA', 'ULLPAYACU', NULL, NULL),
(1499, '160706', 'LORETO', 'DATEM DEL MARAÑON', 'ANDOAS', 'ALIANZA CRISTIANA', NULL, NULL),
(1500, '160801', 'LORETO', 'PUTUMAYO', 'PUTUMAYO', 'SAN ANTONIO DEL ESTRECHO', NULL, NULL),
(1501, '160802', 'LORETO', 'PUTUMAYO', 'ROSA PANDURO', 'SANTA MERCEDES', NULL, NULL),
(1502, '160803', 'LORETO', 'PUTUMAYO', 'TENIENTE MANUEL CLAVERO', 'SOPLIN VARGAS', NULL, NULL),
(1503, '160804', 'LORETO', 'PUTUMAYO', 'YAGUAS', 'REMANSO', NULL, NULL),
(1504, '170101', 'MADRE DE DIOS', 'TAMBOPATA', 'TAMBOPATA', 'PUERTO MALDONADO', NULL, NULL),
(1505, '170102', 'MADRE DE DIOS', 'TAMBOPATA', 'INAMBARI', 'MAZUKO', NULL, NULL),
(1506, '170103', 'MADRE DE DIOS', 'TAMBOPATA', 'LAS PIEDRAS', 'LAS PIEDRAS (PLANCHON)', NULL, NULL),
(1507, '170104', 'MADRE DE DIOS', 'TAMBOPATA', 'LABERINTO', 'PUERTO ROSARIO DE LABERINTO', NULL, NULL),
(1508, '170201', 'MADRE DE DIOS', 'MANU', 'MANU', 'SALVACION', NULL, NULL),
(1509, '170202', 'MADRE DE DIOS', 'MANU', 'FITZCARRALD', 'BOCA MANU', NULL, NULL),
(1510, '170203', 'MADRE DE DIOS', 'MANU', 'MADRE DE DIOS', 'BOCA COLORADO', NULL, NULL),
(1511, '170204', 'MADRE DE DIOS', 'MANU', 'HUEPETUHE', 'HUEPETUHE', NULL, NULL),
(1512, '170301', 'MADRE DE DIOS', 'TAHUAMANU', 'IÑAPARI', 'IÑAPARI', NULL, NULL),
(1513, '170302', 'MADRE DE DIOS', 'TAHUAMANU', 'IBERIA', 'IBERIA', NULL, NULL),
(1514, '170303', 'MADRE DE DIOS', 'TAHUAMANU', 'TAHUAMANU', 'SAN LORENZO', NULL, NULL),
(1515, '180101', 'MOQUEGUA', 'MARISCAL NIETO', 'MOQUEGUA', 'MOQUEGUA', NULL, NULL),
(1516, '180102', 'MOQUEGUA', 'MARISCAL NIETO', 'CARUMAS', 'CARUMAS', NULL, NULL),
(1517, '180103', 'MOQUEGUA', 'MARISCAL NIETO', 'CUCHUMBAYA', 'CUCHUMBAYA', NULL, NULL),
(1518, '180104', 'MOQUEGUA', 'MARISCAL NIETO', 'SAMEGUA', 'SAMEGUA', NULL, NULL),
(1519, '180105', 'MOQUEGUA', 'MARISCAL NIETO', 'SAN CRISTOBAL', 'CALACOA', NULL, NULL),
(1520, '180106', 'MOQUEGUA', 'MARISCAL NIETO', 'TORATA', 'TORATA', NULL, NULL),
(1521, '180201', 'MOQUEGUA', 'GENERAL SANCHEZ CERRO', 'OMATE', 'OMATE', NULL, NULL),
(1522, '180202', 'MOQUEGUA', 'GENERAL SANCHEZ CERRO', 'CHOJATA', 'CHOJATA', NULL, NULL),
(1523, '180203', 'MOQUEGUA', 'GENERAL SANCHEZ CERRO', 'COALAQUE', 'COALAQUE', NULL, NULL),
(1524, '180204', 'MOQUEGUA', 'GENERAL SANCHEZ CERRO', 'ICHUÑA', 'ICHUÑA', NULL, NULL),
(1525, '180205', 'MOQUEGUA', 'GENERAL SANCHEZ CERRO', 'LA CAPILLA', 'LA CAPILLA', NULL, NULL),
(1526, '180206', 'MOQUEGUA', 'GENERAL SANCHEZ CERRO', 'LLOQUE', 'LLOQUE', NULL, NULL),
(1527, '180207', 'MOQUEGUA', 'GENERAL SANCHEZ CERRO', 'MATALAQUE', 'MATALAQUE', NULL, NULL),
(1528, '180208', 'MOQUEGUA', 'GENERAL SANCHEZ CERRO', 'PUQUINA', 'PUQUINA', NULL, NULL),
(1529, '180209', 'MOQUEGUA', 'GENERAL SANCHEZ CERRO', 'QUINISTAQUILLAS', 'QUINISTAQUILLAS', NULL, NULL),
(1530, '180210', 'MOQUEGUA', 'GENERAL SANCHEZ CERRO', 'UBINAS', 'UBINAS', NULL, NULL),
(1531, '180211', 'MOQUEGUA', 'GENERAL SANCHEZ CERRO', 'YUNGA', 'YUNGA', NULL, NULL),
(1532, '180301', 'MOQUEGUA', 'ILO', 'ILO', 'ILO', NULL, NULL),
(1533, '180302', 'MOQUEGUA', 'ILO', 'EL ALGARROBAL', 'EL ALGARROBAL', NULL, NULL),
(1534, '180303', 'MOQUEGUA', 'ILO', 'PACOCHA', 'PUEBLO NUEVO', NULL, NULL),
(1535, '190101', 'PASCO', 'PASCO', 'CHAUPIMARCA', 'CERRO DE PASCO', NULL, NULL),
(1536, '190102', 'PASCO', 'PASCO', 'HUACHON', 'HUACHON', NULL, NULL),
(1537, '190103', 'PASCO', 'PASCO', 'HUARIACA', 'HUARIACA', NULL, NULL),
(1538, '190104', 'PASCO', 'PASCO', 'HUAYLLAY', 'HUAYLLAY', NULL, NULL),
(1539, '190105', 'PASCO', 'PASCO', 'NINACACA', 'NINACACA', NULL, NULL),
(1540, '190106', 'PASCO', 'PASCO', 'PALLANCHACRA', 'PALLANCHACRA', NULL, NULL),
(1541, '190107', 'PASCO', 'PASCO', 'PAUCARTAMBO', 'PAUCARTAMBO', NULL, NULL),
(1542, '190108', 'PASCO', 'PASCO', 'SAN FRANCISCO DE ASIS DE YARUSYACAN', 'YARUSYACAN', NULL, NULL),
(1543, '190109', 'PASCO', 'PASCO', 'SIMON BOLIVAR', 'SAN ANTONIO DE RANCAS', NULL, NULL),
(1544, '190110', 'PASCO', 'PASCO', 'TICLACAYAN', 'TICLACAYAN', NULL, NULL),
(1545, '190111', 'PASCO', 'PASCO', 'TINYAHUARCO', 'TINYAHUARCO (SMELTER)', NULL, NULL),
(1546, '190112', 'PASCO', 'PASCO', 'VICCO', 'VICCO', NULL, NULL),
(1547, '190113', 'PASCO', 'PASCO', 'YANACANCHA', 'YANACANCHA', NULL, NULL),
(1548, '190201', 'PASCO', 'DANIEL ALCIDES CARRION', 'YANAHUANCA', 'YANAHUANCA', NULL, NULL),
(1549, '190202', 'PASCO', 'DANIEL ALCIDES CARRION', 'CHACAYAN', 'CHACAYAN', NULL, NULL),
(1550, '190203', 'PASCO', 'DANIEL ALCIDES CARRION', 'GOYLLARISQUIZGA', 'GOYLLARISQUIZGA', NULL, NULL),
(1551, '190204', 'PASCO', 'DANIEL ALCIDES CARRION', 'PAUCAR', 'PAUCAR', NULL, NULL),
(1552, '190205', 'PASCO', 'DANIEL ALCIDES CARRION', 'SAN PEDRO DE PILLAO', 'SAN PEDRO DE PILLAO', NULL, NULL),
(1553, '190206', 'PASCO', 'DANIEL ALCIDES CARRION', 'SANTA ANA DE TUSI', 'SANTA ANA DE TUSI', NULL, NULL),
(1554, '190207', 'PASCO', 'DANIEL ALCIDES CARRION', 'TAPUC', 'TAPUC', NULL, NULL),
(1555, '190208', 'PASCO', 'DANIEL ALCIDES CARRION', 'VILCABAMBA', 'VILCABAMBA', NULL, NULL),
(1556, '190301', 'PASCO', 'OXAPAMPA', 'OXAPAMPA', 'OXAPAMPA', NULL, NULL),
(1557, '190302', 'PASCO', 'OXAPAMPA', 'CHONTABAMBA', 'CHONTABAMBA', NULL, NULL),
(1558, '190303', 'PASCO', 'OXAPAMPA', 'HUANCABAMBA', 'HUANCABAMBA', NULL, NULL),
(1559, '190304', 'PASCO', 'OXAPAMPA', 'PALCAZU', 'ISCOZACIN', NULL, NULL),
(1560, '190305', 'PASCO', 'OXAPAMPA', 'POZUZO', 'POZUZO', NULL, NULL),
(1561, '190306', 'PASCO', 'OXAPAMPA', 'PUERTO BERMUDEZ', 'PUERTO BERMUDEZ', NULL, NULL),
(1562, '190307', 'PASCO', 'OXAPAMPA', 'VILLA RICA', 'VILLA RICA', NULL, NULL),
(1563, '190308', 'PASCO', 'OXAPAMPA', 'CONSTITUCION', 'CONSTITUCION', NULL, NULL),
(1564, '200101', 'PIURA', 'PIURA', 'PIURA', 'PIURA', NULL, NULL),
(1565, '200104', 'PIURA', 'PIURA', 'CASTILLA', 'CASTILLA', NULL, NULL),
(1566, '200105', 'PIURA', 'PIURA', 'CATACAOS', 'CATACAOS', NULL, NULL),
(1567, '200107', 'PIURA', 'PIURA', 'CURA MORI', 'CUCUNGARA', NULL, NULL),
(1568, '200108', 'PIURA', 'PIURA', 'EL TALLAN', 'SINCHAO', NULL, NULL),
(1569, '200109', 'PIURA', 'PIURA', 'LA ARENA', 'LA ARENA', NULL, NULL),
(1570, '200110', 'PIURA', 'PIURA', 'LA UNION', 'LA UNION', NULL, NULL),
(1571, '200111', 'PIURA', 'PIURA', 'LAS LOMAS', 'LAS LOMAS', NULL, NULL),
(1572, '200114', 'PIURA', 'PIURA', 'TAMBO GRANDE', 'TAMBO GRANDE', NULL, NULL),
(1573, '200115', 'PIURA', 'PIURA', 'VEINTISEIS DE OCTUBRE', 'SAN MARTIN', NULL, NULL),
(1574, '200201', 'PIURA', 'AYABACA', 'AYABACA', 'AYABACA', NULL, NULL),
(1575, '200202', 'PIURA', 'AYABACA', 'FRIAS', 'FRIAS', NULL, NULL),
(1576, '200203', 'PIURA', 'AYABACA', 'JILILI', 'JILILI', NULL, NULL),
(1577, '200204', 'PIURA', 'AYABACA', 'LAGUNAS', 'LAGUNAS', NULL, NULL),
(1578, '200205', 'PIURA', 'AYABACA', 'MONTERO', 'MONTERO', NULL, NULL),
(1579, '200206', 'PIURA', 'AYABACA', 'PACAIPAMPA', 'PACAIPAMPA', NULL, NULL),
(1580, '200207', 'PIURA', 'AYABACA', 'PAIMAS', 'PAIMAS', NULL, NULL),
(1581, '200208', 'PIURA', 'AYABACA', 'SAPILLICA', 'SAPILLICA', NULL, NULL),
(1582, '200209', 'PIURA', 'AYABACA', 'SICCHEZ', 'SICCHEZ', NULL, NULL),
(1583, '200210', 'PIURA', 'AYABACA', 'SUYO', 'SUYO', NULL, NULL),
(1584, '200301', 'PIURA', 'HUANCABAMBA', 'HUANCABAMBA', 'HUANCABAMBA', NULL, NULL),
(1585, '200302', 'PIURA', 'HUANCABAMBA', 'CANCHAQUE', 'CANCHAQUE', NULL, NULL),
(1586, '200303', 'PIURA', 'HUANCABAMBA', 'EL CARMEN DE LA FRONTERA', 'SAPALACHE', NULL, NULL),
(1587, '200304', 'PIURA', 'HUANCABAMBA', 'HUARMACA', 'HUARMACA', NULL, NULL),
(1588, '200305', 'PIURA', 'HUANCABAMBA', 'LALAQUIZ', 'TUNAL', NULL, NULL),
(1589, '200306', 'PIURA', 'HUANCABAMBA', 'SAN MIGUEL DE EL FAIQUE', 'SAN MIGUEL DE EL FAIQUE', NULL, NULL),
(1590, '200307', 'PIURA', 'HUANCABAMBA', 'SONDOR', 'SONDOR', NULL, NULL),
(1591, '200308', 'PIURA', 'HUANCABAMBA', 'SONDORILLO', 'SONDORILLO', NULL, NULL),
(1592, '200401', 'PIURA', 'MORROPON', 'CHULUCANAS', 'CHULUCANAS', NULL, NULL),
(1593, '200402', 'PIURA', 'MORROPON', 'BUENOS AIRES', 'BUENOS AIRES', NULL, NULL),
(1594, '200403', 'PIURA', 'MORROPON', 'CHALACO', 'CHALACO', NULL, NULL),
(1595, '200404', 'PIURA', 'MORROPON', 'LA MATANZA', 'LA MATANZA', NULL, NULL),
(1596, '200405', 'PIURA', 'MORROPON', 'MORROPON', 'MORROPON', NULL, NULL),
(1597, '200406', 'PIURA', 'MORROPON', 'SALITRAL', 'SALITRAL', NULL, NULL),
(1598, '200407', 'PIURA', 'MORROPON', 'SAN JUAN DE BIGOTE', 'BIGOTE', NULL, NULL),
(1599, '200408', 'PIURA', 'MORROPON', 'SANTA CATALINA DE MOSSA', 'PALTASHACO', NULL, NULL),
(1600, '200409', 'PIURA', 'MORROPON', 'SANTO DOMINGO', 'SANTO DOMINGO', NULL, NULL),
(1601, '200410', 'PIURA', 'MORROPON', 'YAMANGO', 'YAMANGO', NULL, NULL),
(1602, '200501', 'PIURA', 'PAITA', 'PAITA', 'PAITA', NULL, NULL),
(1603, '200502', 'PIURA', 'PAITA', 'AMOTAPE', 'AMOTAPE', NULL, NULL),
(1604, '200503', 'PIURA', 'PAITA', 'ARENAL', 'ARENAL', NULL, NULL),
(1605, '200504', 'PIURA', 'PAITA', 'COLAN', 'SAN LUCAS (PUEBLO NUEVO DE COLAN)', NULL, NULL),
(1606, '200505', 'PIURA', 'PAITA', 'LA HUACA', 'LA HUACA', NULL, NULL),
(1607, '200506', 'PIURA', 'PAITA', 'TAMARINDO', 'TAMARINDO', NULL, NULL),
(1608, '200507', 'PIURA', 'PAITA', 'VICHAYAL', 'SAN FELIPE DE VICHAYAL', NULL, NULL),
(1609, '200601', 'PIURA', 'SULLANA', 'SULLANA', 'SULLANA', NULL, NULL),
(1610, '200602', 'PIURA', 'SULLANA', 'BELLAVISTA', 'BELLAVISTA', NULL, NULL),
(1611, '200603', 'PIURA', 'SULLANA', 'IGNACIO ESCUDERO', 'SAN JACINTO', NULL, NULL),
(1612, '200604', 'PIURA', 'SULLANA', 'LANCONES', 'LANCONES', NULL, NULL),
(1613, '200605', 'PIURA', 'SULLANA', 'MARCAVELICA', 'MARCAVELICA', NULL, NULL),
(1614, '200606', 'PIURA', 'SULLANA', 'MIGUEL CHECA', 'SOJO', NULL, NULL),
(1615, '200607', 'PIURA', 'SULLANA', 'QUERECOTILLO', 'QUERECOTILLO', NULL, NULL),
(1616, '200608', 'PIURA', 'SULLANA', 'SALITRAL', 'SALITRAL', NULL, NULL),
(1617, '200701', 'PIURA', 'TALARA', 'PARIÑAS', 'TALARA', NULL, NULL),
(1618, '200702', 'PIURA', 'TALARA', 'EL ALTO', 'EL ALTO', NULL, NULL),
(1619, '200703', 'PIURA', 'TALARA', 'LA BREA', 'NEGRITOS', NULL, NULL),
(1620, '200704', 'PIURA', 'TALARA', 'LOBITOS', 'LOBITOS', NULL, NULL),
(1621, '200705', 'PIURA', 'TALARA', 'LOS ORGANOS', 'LOS ORGANOS', NULL, NULL),
(1622, '200706', 'PIURA', 'TALARA', 'MANCORA', 'MANCORA', NULL, NULL),
(1623, '200801', 'PIURA', 'SECHURA', 'SECHURA', 'SECHURA', NULL, NULL),
(1624, '200802', 'PIURA', 'SECHURA', 'BELLAVISTA DE LA UNION', 'BELLAVISTA', NULL, NULL),
(1625, '200803', 'PIURA', 'SECHURA', 'BERNAL', 'BERNAL', NULL, NULL),
(1626, '200804', 'PIURA', 'SECHURA', 'CRISTO NOS VALGA', 'SAN CRISTO', NULL, NULL),
(1627, '200805', 'PIURA', 'SECHURA', 'VICE', 'VICE', NULL, NULL),
(1628, '200806', 'PIURA', 'SECHURA', 'RINCONADA LLICUAR', 'DOS PUEBLOS', NULL, NULL),
(1629, '210101', 'PUNO', 'PUNO', 'PUNO', 'PUNO', NULL, NULL),
(1630, '210102', 'PUNO', 'PUNO', 'ACORA', 'ACORA', NULL, NULL),
(1631, '210103', 'PUNO', 'PUNO', 'AMANTANI', 'AMANTANI', NULL, NULL),
(1632, '210104', 'PUNO', 'PUNO', 'ATUNCOLLA', 'ATUNCOLLA', NULL, NULL),
(1633, '210105', 'PUNO', 'PUNO', 'CAPACHICA', 'CAPACHICA', NULL, NULL),
(1634, '210106', 'PUNO', 'PUNO', 'CHUCUITO', 'CHUCUITO', NULL, NULL),
(1635, '210107', 'PUNO', 'PUNO', 'COATA', 'COATA', NULL, NULL),
(1636, '210108', 'PUNO', 'PUNO', 'HUATA', 'HUATA', NULL, NULL),
(1637, '210109', 'PUNO', 'PUNO', 'MAÑAZO', 'MAÑAZO', NULL, NULL),
(1638, '210110', 'PUNO', 'PUNO', 'PAUCARCOLLA', 'PAUCARCOLLA', NULL, NULL),
(1639, '210111', 'PUNO', 'PUNO', 'PICHACANI', 'LARAQUERI', NULL, NULL),
(1640, '210112', 'PUNO', 'PUNO', 'PLATERIA', 'PLATERIA', NULL, NULL),
(1641, '210113', 'PUNO', 'PUNO', 'SAN ANTONIO', 'SAN ANTONIO DE ESQUILACHE /15', NULL, NULL),
(1642, '210114', 'PUNO', 'PUNO', 'TIQUILLACA', 'TIQUILLACA', NULL, NULL),
(1643, '210115', 'PUNO', 'PUNO', 'VILQUE', 'VILQUE', NULL, NULL),
(1644, '210201', 'PUNO', 'AZANGARO', 'AZANGARO', 'AZANGARO', NULL, NULL),
(1645, '210202', 'PUNO', 'AZANGARO', 'ACHAYA', 'ACHAYA', NULL, NULL),
(1646, '210203', 'PUNO', 'AZANGARO', 'ARAPA', 'ARAPA', NULL, NULL),
(1647, '210204', 'PUNO', 'AZANGARO', 'ASILLO', 'ASILLO', NULL, NULL),
(1648, '210205', 'PUNO', 'AZANGARO', 'CAMINACA', 'CAMINACA', NULL, NULL),
(1649, '210206', 'PUNO', 'AZANGARO', 'CHUPA', 'CHUPA', NULL, NULL),
(1650, '210207', 'PUNO', 'AZANGARO', 'JOSE DOMINGO CHOQUEHUANCA', 'ESTACION DE PUCARA', NULL, NULL),
(1651, '210208', 'PUNO', 'AZANGARO', 'MUÑANI', 'MUÑANI', NULL, NULL),
(1652, '210209', 'PUNO', 'AZANGARO', 'POTONI', 'POTONI', NULL, NULL),
(1653, '210210', 'PUNO', 'AZANGARO', 'SAMAN', 'SAMAN', NULL, NULL),
(1654, '210211', 'PUNO', 'AZANGARO', 'SAN ANTON', 'SAN ANTON', NULL, NULL),
(1655, '210212', 'PUNO', 'AZANGARO', 'SAN JOSE', 'SAN JOSE', NULL, NULL),
(1656, '210213', 'PUNO', 'AZANGARO', 'SAN JUAN DE SALINAS', 'SAN JUAN DE SALINAS', NULL, NULL),
(1657, '210214', 'PUNO', 'AZANGARO', 'SANTIAGO DE PUPUJA', 'SANTIAGO DE PUPUJA', NULL, NULL),
(1658, '210215', 'PUNO', 'AZANGARO', 'TIRAPATA', 'TIRAPATA', NULL, NULL),
(1659, '210301', 'PUNO', 'CARABAYA', 'MACUSANI', 'MACUSANI', NULL, NULL),
(1660, '210302', 'PUNO', 'CARABAYA', 'AJOYANI', 'AJOYANI', NULL, NULL),
(1661, '210303', 'PUNO', 'CARABAYA', 'AYAPATA', 'AYAPATA', NULL, NULL),
(1662, '210304', 'PUNO', 'CARABAYA', 'COASA', 'COASA', NULL, NULL),
(1663, '210305', 'PUNO', 'CARABAYA', 'CORANI', 'CORANI', NULL, NULL),
(1664, '210306', 'PUNO', 'CARABAYA', 'CRUCERO', 'CRUCERO', NULL, NULL),
(1665, '210307', 'PUNO', 'CARABAYA', 'ITUATA', 'ITUATA /16', NULL, NULL),
(1666, '210308', 'PUNO', 'CARABAYA', 'OLLACHEA', 'OLLACHEA', NULL, NULL),
(1667, '210309', 'PUNO', 'CARABAYA', 'SAN GABAN', 'LANLACUNI BAJO', NULL, NULL),
(1668, '210310', 'PUNO', 'CARABAYA', 'USICAYOS', 'USICAYOS', NULL, NULL),
(1669, '210401', 'PUNO', 'CHUCUITO', 'JULI', 'JULI', NULL, NULL),
(1670, '210402', 'PUNO', 'CHUCUITO', 'DESAGUADERO', 'DESAGUADERO', NULL, NULL),
(1671, '210403', 'PUNO', 'CHUCUITO', 'HUACULLANI', 'HUACULLANI', NULL, NULL),
(1672, '210404', 'PUNO', 'CHUCUITO', 'KELLUYO', 'KELLUYO', NULL, NULL),
(1673, '210405', 'PUNO', 'CHUCUITO', 'PISACOMA', 'PISACOMA', NULL, NULL),
(1674, '210406', 'PUNO', 'CHUCUITO', 'POMATA', 'POMATA', NULL, NULL),
(1675, '210407', 'PUNO', 'CHUCUITO', 'ZEPITA', 'ZEPITA', NULL, NULL),
(1676, '210501', 'PUNO', 'EL COLLAO', 'ILAVE', 'ILAVE', NULL, NULL),
(1677, '210502', 'PUNO', 'EL COLLAO', 'CAPAZO', 'CAPAZO', NULL, NULL),
(1678, '210503', 'PUNO', 'EL COLLAO', 'PILCUYO', 'PILCUYO', NULL, NULL),
(1679, '210504', 'PUNO', 'EL COLLAO', 'SANTA ROSA', 'MAZO CRUZ', NULL, NULL),
(1680, '210505', 'PUNO', 'EL COLLAO', 'CONDURIRI', 'CONDURIRI', NULL, NULL),
(1681, '210601', 'PUNO', 'HUANCANE', 'HUANCANE', 'HUANCANE', NULL, NULL),
(1682, '210602', 'PUNO', 'HUANCANE', 'COJATA', 'COJATA', NULL, NULL),
(1683, '210603', 'PUNO', 'HUANCANE', 'HUATASANI', 'HUATASANI', NULL, NULL),
(1684, '210604', 'PUNO', 'HUANCANE', 'INCHUPALLA', 'INCHUPALLA', NULL, NULL),
(1685, '210605', 'PUNO', 'HUANCANE', 'PUSI', 'PUSI', NULL, NULL),
(1686, '210606', 'PUNO', 'HUANCANE', 'ROSASPATA', 'ROSASPATA', NULL, NULL),
(1687, '210607', 'PUNO', 'HUANCANE', 'TARACO', 'TARACO', NULL, NULL),
(1688, '210608', 'PUNO', 'HUANCANE', 'VILQUE CHICO', 'VILQUE CHICO', NULL, NULL),
(1689, '210701', 'PUNO', 'LAMPA', 'LAMPA', 'LAMPA', NULL, NULL),
(1690, '210702', 'PUNO', 'LAMPA', 'CABANILLA', 'CABANILLA', NULL, NULL),
(1691, '210703', 'PUNO', 'LAMPA', 'CALAPUJA', 'CALAPUJA', NULL, NULL),
(1692, '210704', 'PUNO', 'LAMPA', 'NICASIO', 'NICASIO', NULL, NULL),
(1693, '210705', 'PUNO', 'LAMPA', 'OCUVIRI', 'OCUVIRI', NULL, NULL),
(1694, '210706', 'PUNO', 'LAMPA', 'PALCA', 'PALCA', NULL, NULL),
(1695, '210707', 'PUNO', 'LAMPA', 'PARATIA', 'PARATIA', NULL, NULL),
(1696, '210708', 'PUNO', 'LAMPA', 'PUCARA', 'PUCARA', NULL, NULL),
(1697, '210709', 'PUNO', 'LAMPA', 'SANTA LUCIA', 'SANTA LUCIA', NULL, NULL),
(1698, '210710', 'PUNO', 'LAMPA', 'VILAVILA', 'VILAVILA', NULL, NULL),
(1699, '210801', 'PUNO', 'MELGAR', 'AYAVIRI', 'AYAVIRI', NULL, NULL),
(1700, '210802', 'PUNO', 'MELGAR', 'ANTAUTA', 'ANTAUTA', NULL, NULL),
(1701, '210803', 'PUNO', 'MELGAR', 'CUPI', 'CUPI', NULL, NULL),
(1702, '210804', 'PUNO', 'MELGAR', 'LLALLI', 'LLALLI', NULL, NULL),
(1703, '210805', 'PUNO', 'MELGAR', 'MACARI', 'MACARI', NULL, NULL),
(1704, '210806', 'PUNO', 'MELGAR', 'NUÑOA', 'NUÑOA', NULL, NULL),
(1705, '210807', 'PUNO', 'MELGAR', 'ORURILLO', 'ORURILLO', NULL, NULL),
(1706, '210808', 'PUNO', 'MELGAR', 'SANTA ROSA', 'SANTA ROSA', NULL, NULL),
(1707, '210809', 'PUNO', 'MELGAR', 'UMACHIRI', 'UMACHIRI', NULL, NULL),
(1708, '210901', 'PUNO', 'MOHO', 'MOHO', 'MOHO', NULL, NULL),
(1709, '210902', 'PUNO', 'MOHO', 'CONIMA', 'CONIMA', NULL, NULL),
(1710, '210903', 'PUNO', 'MOHO', 'HUAYRAPATA', 'HUAYRAPATA', NULL, NULL),
(1711, '210904', 'PUNO', 'MOHO', 'TILALI', 'TILALI', NULL, NULL),
(1712, '211001', 'PUNO', 'SAN ANTONIO DE PUTINA', 'PUTINA', 'PUTINA', NULL, NULL),
(1713, '211002', 'PUNO', 'SAN ANTONIO DE PUTINA', 'ANANEA', 'ANANEA', NULL, NULL),
(1714, '211003', 'PUNO', 'SAN ANTONIO DE PUTINA', 'PEDRO VILCA APAZA', 'AYRAMPUNI', NULL, NULL),
(1715, '211004', 'PUNO', 'SAN ANTONIO DE PUTINA', 'QUILCAPUNCU', 'QUILCAPUNCU', NULL, NULL),
(1716, '211005', 'PUNO', 'SAN ANTONIO DE PUTINA', 'SINA', 'SINA', NULL, NULL),
(1717, '211101', 'PUNO', 'SAN ROMAN', 'JULIACA', 'JULIACA', NULL, NULL),
(1718, '211102', 'PUNO', 'SAN ROMAN', 'CABANA', 'CABANA', NULL, NULL),
(1719, '211103', 'PUNO', 'SAN ROMAN', 'CABANILLAS', 'DEUSTUA', NULL, NULL),
(1720, '211104', 'PUNO', 'SAN ROMAN', 'CARACOTO', 'CARACOTO', NULL, NULL),
(1721, '211105', 'PUNO', 'SAN ROMAN', 'SAN MIGUEL', 'SAN MIGUEL', NULL, NULL),
(1722, '211201', 'PUNO', 'SANDIA', 'SANDIA', 'SANDIA', NULL, NULL),
(1723, '211202', 'PUNO', 'SANDIA', 'CUYOCUYO', 'CUYOCUYO', NULL, NULL),
(1724, '211203', 'PUNO', 'SANDIA', 'LIMBANI', 'LIMBANI', NULL, NULL),
(1725, '211204', 'PUNO', 'SANDIA', 'PATAMBUCO', 'PATAMBUCO', NULL, NULL),
(1726, '211205', 'PUNO', 'SANDIA', 'PHARA', 'PHARA', NULL, NULL),
(1727, '211206', 'PUNO', 'SANDIA', 'QUIACA', 'QUIACA', NULL, NULL),
(1728, '211207', 'PUNO', 'SANDIA', 'SAN JUAN DEL ORO', 'SAN JUAN DEL ORO', NULL, NULL),
(1729, '211208', 'PUNO', 'SANDIA', 'YANAHUAYA', 'YANAHUAYA', NULL, NULL),
(1730, '211209', 'PUNO', 'SANDIA', 'ALTO INAMBARI', 'MASSIAPO', NULL, NULL),
(1731, '211210', 'PUNO', 'SANDIA', 'SAN PEDRO DE PUTINA PUNCO', 'PUTINA PUNCO', NULL, NULL),
(1732, '211301', 'PUNO', 'YUNGUYO', 'YUNGUYO', 'YUNGUYO', NULL, NULL),
(1733, '211302', 'PUNO', 'YUNGUYO', 'ANAPIA', 'ANAPIA', NULL, NULL),
(1734, '211303', 'PUNO', 'YUNGUYO', 'COPANI', 'COPANI', NULL, NULL),
(1735, '211304', 'PUNO', 'YUNGUYO', 'CUTURAPI', 'SAN JUAN DE CUTURAPI', NULL, NULL),
(1736, '211305', 'PUNO', 'YUNGUYO', 'OLLARAYA', 'SAN MIGUEL DE OLLARAYA', NULL, NULL),
(1737, '211306', 'PUNO', 'YUNGUYO', 'TINICACHI', 'TINICACHI', NULL, NULL),
(1738, '211307', 'PUNO', 'YUNGUYO', 'UNICACHI', 'MARCAJA', NULL, NULL),
(1739, '220101', 'SAN MARTIN', 'MOYOBAMBA', 'MOYOBAMBA', 'MOYOBAMBA', NULL, NULL),
(1740, '220102', 'SAN MARTIN', 'MOYOBAMBA', 'CALZADA', 'CALZADA', NULL, NULL),
(1741, '220103', 'SAN MARTIN', 'MOYOBAMBA', 'HABANA', 'HABANA', NULL, NULL),
(1742, '220104', 'SAN MARTIN', 'MOYOBAMBA', 'JEPELACIO', 'JEPELACIO', NULL, NULL),
(1743, '220105', 'SAN MARTIN', 'MOYOBAMBA', 'SORITOR', 'SORITOR', NULL, NULL),
(1744, '220106', 'SAN MARTIN', 'MOYOBAMBA', 'YANTALO', 'YANTALO', NULL, NULL),
(1745, '220201', 'SAN MARTIN', 'BELLAVISTA', 'BELLAVISTA', 'BELLAVISTA', NULL, NULL),
(1746, '220202', 'SAN MARTIN', 'BELLAVISTA', 'ALTO BIAVO', 'CUZCO', NULL, NULL),
(1747, '220203', 'SAN MARTIN', 'BELLAVISTA', 'BAJO BIAVO', 'NUEVO LIMA', NULL, NULL),
(1748, '220204', 'SAN MARTIN', 'BELLAVISTA', 'HUALLAGA', 'LEDOY', NULL, NULL),
(1749, '220205', 'SAN MARTIN', 'BELLAVISTA', 'SAN PABLO', 'SAN PABLO', NULL, NULL),
(1750, '220206', 'SAN MARTIN', 'BELLAVISTA', 'SAN RAFAEL', 'SAN RAFAEL', NULL, NULL),
(1751, '220301', 'SAN MARTIN', 'EL DORADO', 'SAN JOSE DE SISA', 'SAN JOSE DE SISA', NULL, NULL),
(1752, '220302', 'SAN MARTIN', 'EL DORADO', 'AGUA BLANCA', 'AGUA BLANCA', NULL, NULL),
(1753, '220303', 'SAN MARTIN', 'EL DORADO', 'SAN MARTIN', 'SAN MARTIN', NULL, NULL),
(1754, '220304', 'SAN MARTIN', 'EL DORADO', 'SANTA ROSA', 'SANTA ROSA', NULL, NULL),
(1755, '220305', 'SAN MARTIN', 'EL DORADO', 'SHATOJA', 'SHATOJA', NULL, NULL),
(1756, '220401', 'SAN MARTIN', 'HUALLAGA', 'SAPOSOA', 'SAPOSOA', NULL, NULL),
(1757, '220402', 'SAN MARTIN', 'HUALLAGA', 'ALTO SAPOSOA', 'PASARRAYA', NULL, NULL),
(1758, '220403', 'SAN MARTIN', 'HUALLAGA', 'EL ESLABON', 'EL ESLABON', NULL, NULL),
(1759, '220404', 'SAN MARTIN', 'HUALLAGA', 'PISCOYACU', 'PISCOYACU', NULL, NULL),
(1760, '220405', 'SAN MARTIN', 'HUALLAGA', 'SACANCHE', 'SACANCHE', NULL, NULL),
(1761, '220406', 'SAN MARTIN', 'HUALLAGA', 'TINGO DE SAPOSOA', 'TINGO DE SAPOSOA', NULL, NULL),
(1762, '220501', 'SAN MARTIN', 'LAMAS', 'LAMAS', 'LAMAS', NULL, NULL),
(1763, '220502', 'SAN MARTIN', 'LAMAS', 'ALONSO DE ALVARADO', 'ROQUE', NULL, NULL),
(1764, '220503', 'SAN MARTIN', 'LAMAS', 'BARRANQUITA', 'BARRANQUITA', NULL, NULL),
(1765, '220504', 'SAN MARTIN', 'LAMAS', 'CAYNARACHI', 'PONGO DE CAYNARACHI', NULL, NULL),
(1766, '220505', 'SAN MARTIN', 'LAMAS', 'CUÑUMBUQUI', 'CUÑUMBUQUI', NULL, NULL),
(1767, '220506', 'SAN MARTIN', 'LAMAS', 'PINTO RECODO', 'PINTO RECODO', NULL, NULL),
(1768, '220507', 'SAN MARTIN', 'LAMAS', 'RUMISAPA', 'RUMISAPA', NULL, NULL),
(1769, '220508', 'SAN MARTIN', 'LAMAS', 'SAN ROQUE DE CUMBAZA', 'SAN ROQUE DE CUMBAZA', NULL, NULL),
(1770, '220509', 'SAN MARTIN', 'LAMAS', 'SHANAO', 'SHANAO', NULL, NULL),
(1771, '220510', 'SAN MARTIN', 'LAMAS', 'TABALOSOS', 'TABALOSOS', NULL, NULL),
(1772, '220511', 'SAN MARTIN', 'LAMAS', 'ZAPATERO', 'ZAPATERO', NULL, NULL),
(1773, '220601', 'SAN MARTIN', 'MARISCAL CACERES', 'JUANJUI', 'JUANJUI', NULL, NULL),
(1774, '220602', 'SAN MARTIN', 'MARISCAL CACERES', 'CAMPANILLA', 'CAMPANILLA', NULL, NULL),
(1775, '220603', 'SAN MARTIN', 'MARISCAL CACERES', 'HUICUNGO', 'HUICUNGO', NULL, NULL),
(1776, '220604', 'SAN MARTIN', 'MARISCAL CACERES', 'PACHIZA', 'PACHIZA', NULL, NULL),
(1777, '220605', 'SAN MARTIN', 'MARISCAL CACERES', 'PAJARILLO', 'PAJARILLO', NULL, NULL),
(1778, '220701', 'SAN MARTIN', 'PICOTA', 'PICOTA', 'PICOTA', NULL, NULL),
(1779, '220702', 'SAN MARTIN', 'PICOTA', 'BUENOS AIRES', 'BUENOS AIRES', NULL, NULL),
(1780, '220703', 'SAN MARTIN', 'PICOTA', 'CASPISAPA', 'CASPISAPA', NULL, NULL),
(1781, '220704', 'SAN MARTIN', 'PICOTA', 'PILLUANA', 'PILLUANA', NULL, NULL),
(1782, '220705', 'SAN MARTIN', 'PICOTA', 'PUCACACA', 'PUCACACA', NULL, NULL),
(1783, '220706', 'SAN MARTIN', 'PICOTA', 'SAN CRISTOBAL', 'PUERTO RICO', NULL, NULL),
(1784, '220707', 'SAN MARTIN', 'PICOTA', 'SAN HILARION', 'SAN CRISTOBAL DE SISA', NULL, NULL),
(1785, '220708', 'SAN MARTIN', 'PICOTA', 'SHAMBOYACU', 'SHAMBOYACU', NULL, NULL),
(1786, '220709', 'SAN MARTIN', 'PICOTA', 'TINGO DE PONASA', 'TINGO DE PONASA', NULL, NULL),
(1787, '220710', 'SAN MARTIN', 'PICOTA', 'TRES UNIDOS', 'TRES UNIDOS', NULL, NULL),
(1788, '220801', 'SAN MARTIN', 'RIOJA', 'RIOJA', 'RIOJA', NULL, NULL),
(1789, '220802', 'SAN MARTIN', 'RIOJA', 'AWAJUN', 'BAJO NARANJILLO', NULL, NULL),
(1790, '220803', 'SAN MARTIN', 'RIOJA', 'ELIAS SOPLIN VARGAS', 'SEGUNDA JERUSALEN-AZUNGUILLO', NULL, NULL),
(1791, '220804', 'SAN MARTIN', 'RIOJA', 'NUEVA CAJAMARCA', 'NUEVA CAJAMARCA', NULL, NULL),
(1792, '220805', 'SAN MARTIN', 'RIOJA', 'PARDO MIGUEL', 'NARANJOS', NULL, NULL),
(1793, '220806', 'SAN MARTIN', 'RIOJA', 'POSIC', 'POSIC', NULL, NULL),
(1794, '220807', 'SAN MARTIN', 'RIOJA', 'SAN FERNANDO', 'SAN FERNANDO', NULL, NULL),
(1795, '220808', 'SAN MARTIN', 'RIOJA', 'YORONGOS', 'YORONGOS', NULL, NULL),
(1796, '220809', 'SAN MARTIN', 'RIOJA', 'YURACYACU', 'YURACYACU', NULL, NULL),
(1797, '220901', 'SAN MARTIN', 'SAN MARTIN', 'TARAPOTO', 'TARAPOTO', NULL, NULL),
(1798, '220902', 'SAN MARTIN', 'SAN MARTIN', 'ALBERTO LEVEAU', 'UTCURARCA', NULL, NULL),
(1799, '220903', 'SAN MARTIN', 'SAN MARTIN', 'CACATACHI', 'CACATACHI', NULL, NULL),
(1800, '220904', 'SAN MARTIN', 'SAN MARTIN', 'CHAZUTA', 'CHAZUTA', NULL, NULL),
(1801, '220905', 'SAN MARTIN', 'SAN MARTIN', 'CHIPURANA', 'NAVARRO', NULL, NULL),
(1802, '220906', 'SAN MARTIN', 'SAN MARTIN', 'EL PORVENIR', 'PELEJO', NULL, NULL),
(1803, '220907', 'SAN MARTIN', 'SAN MARTIN', 'HUIMBAYOC', 'HUIMBAYOC', NULL, NULL),
(1804, '220908', 'SAN MARTIN', 'SAN MARTIN', 'JUAN GUERRA', 'JUAN GUERRA', NULL, NULL),
(1805, '220909', 'SAN MARTIN', 'SAN MARTIN', 'LA BANDA DE SHILCAYO', 'LA BANDA', NULL, NULL),
(1806, '220910', 'SAN MARTIN', 'SAN MARTIN', 'MORALES', 'MORALES', NULL, NULL),
(1807, '220911', 'SAN MARTIN', 'SAN MARTIN', 'PAPAPLAYA', 'PAPAPLAYA', NULL, NULL),
(1808, '220912', 'SAN MARTIN', 'SAN MARTIN', 'SAN ANTONIO', 'SAN ANTONIO', NULL, NULL),
(1809, '220913', 'SAN MARTIN', 'SAN MARTIN', 'SAUCE', 'SAUCE', NULL, NULL),
(1810, '220914', 'SAN MARTIN', 'SAN MARTIN', 'SHAPAJA', 'SHAPAJA', NULL, NULL),
(1811, '221001', 'SAN MARTIN', 'TOCACHE', 'TOCACHE', 'TOCACHE', NULL, NULL),
(1812, '221002', 'SAN MARTIN', 'TOCACHE', 'NUEVO PROGRESO', 'NUEVO PROGRESO', NULL, NULL),
(1813, '221003', 'SAN MARTIN', 'TOCACHE', 'POLVORA', 'POLVORA', NULL, NULL),
(1814, '221004', 'SAN MARTIN', 'TOCACHE', 'SHUNTE', 'TAMBO DE PAJA /18', NULL, NULL),
(1815, '221005', 'SAN MARTIN', 'TOCACHE', 'UCHIZA', 'UCHIZA', NULL, NULL),
(1816, '230101', 'TACNA', 'TACNA', 'TACNA', 'TACNA', NULL, NULL),
(1817, '230102', 'TACNA', 'TACNA', 'ALTO DE LA ALIANZA', 'LA ESPERANZA', NULL, NULL),
(1818, '230103', 'TACNA', 'TACNA', 'CALANA', 'CALANA', NULL, NULL),
(1819, '230104', 'TACNA', 'TACNA', 'CIUDAD NUEVA', 'CIUDAD NUEVA', NULL, NULL),
(1820, '230105', 'TACNA', 'TACNA', 'INCLAN', 'SAMA GRANDE', NULL, NULL),
(1821, '230106', 'TACNA', 'TACNA', 'PACHIA', 'PACHIA', NULL, NULL),
(1822, '230107', 'TACNA', 'TACNA', 'PALCA', 'PALCA', NULL, NULL),
(1823, '230108', 'TACNA', 'TACNA', 'POCOLLAY', 'POCOLLAY', NULL, NULL),
(1824, '230109', 'TACNA', 'TACNA', 'SAMA', 'LAS YARAS', NULL, NULL),
(1825, '230110', 'TACNA', 'TACNA', 'CORONEL GREGORIO ALBARRACIN LANCHIPA', 'ALFONSO UGARTE', NULL, NULL),
(1826, '230111', 'TACNA', 'TACNA', 'LA YARADA LOS PALOS', 'LOS PALOS', NULL, NULL),
(1827, '230201', 'TACNA', 'CANDARAVE', 'CANDARAVE', 'CANDARAVE', NULL, NULL),
(1828, '230202', 'TACNA', 'CANDARAVE', 'CAIRANI', 'CAIRANI', NULL, NULL),
(1829, '230203', 'TACNA', 'CANDARAVE', 'CAMILACA', 'ALTO CAMILACA', NULL, NULL),
(1830, '230204', 'TACNA', 'CANDARAVE', 'CURIBAYA', 'CURIBAYA', NULL, NULL),
(1831, '230205', 'TACNA', 'CANDARAVE', 'HUANUARA', 'HUANUARA', NULL, NULL),
(1832, '230206', 'TACNA', 'CANDARAVE', 'QUILAHUANI', 'QUILAHUANI', NULL, NULL),
(1833, '230301', 'TACNA', 'JORGE BASADRE', 'LOCUMBA', 'LOCUMBA', NULL, NULL),
(1834, '230302', 'TACNA', 'JORGE BASADRE', 'ILABAYA', 'ILABAYA', NULL, NULL),
(1835, '230303', 'TACNA', 'JORGE BASADRE', 'ITE', 'ITE', NULL, NULL),
(1836, '230401', 'TACNA', 'TARATA', 'TARATA', 'TARATA', NULL, NULL),
(1837, '230402', 'TACNA', 'TARATA', 'HEROES ALBARRACIN', 'CHUCATAMANI', NULL, NULL),
(1838, '230403', 'TACNA', 'TARATA', 'ESTIQUE', 'ESTIQUE', NULL, NULL),
(1839, '230404', 'TACNA', 'TARATA', 'ESTIQUE-PAMPA', 'ESTIQUE-PAMPA', NULL, NULL),
(1840, '230405', 'TACNA', 'TARATA', 'SITAJARA', 'SITAJARA', NULL, NULL),
(1841, '230406', 'TACNA', 'TARATA', 'SUSAPAYA', 'SUSAPAYA', NULL, NULL),
(1842, '230407', 'TACNA', 'TARATA', 'TARUCACHI', 'TARUCACHI', NULL, NULL),
(1843, '230408', 'TACNA', 'TARATA', 'TICACO', 'TICACO', NULL, NULL),
(1844, '240101', 'TUMBES', 'TUMBES', 'TUMBES', 'TUMBES', NULL, NULL),
(1845, '240102', 'TUMBES', 'TUMBES', 'CORRALES', 'SAN PEDRO DE LOS INCAS', NULL, NULL),
(1846, '240103', 'TUMBES', 'TUMBES', 'LA CRUZ', 'CALETA CRUZ', NULL, NULL),
(1847, '240104', 'TUMBES', 'TUMBES', 'PAMPAS DE HOSPITAL', 'PAMPAS DE HOSPITAL', NULL, NULL),
(1848, '240105', 'TUMBES', 'TUMBES', 'SAN JACINTO', 'SAN JACINTO', NULL, NULL),
(1849, '240106', 'TUMBES', 'TUMBES', 'SAN JUAN DE LA VIRGEN', 'SAN JUAN DE LA VIRGEN', NULL, NULL),
(1850, '240201', 'TUMBES', 'CONTRALMIRANTE VILLAR', 'ZORRITOS', 'ZORRITOS', NULL, NULL),
(1851, '240202', 'TUMBES', 'CONTRALMIRANTE VILLAR', 'CASITAS', 'CAÑAVERAL', NULL, NULL),
(1852, '240203', 'TUMBES', 'CONTRALMIRANTE VILLAR', 'CANOAS DE PUNTA SAL', 'CANCAS', NULL, NULL),
(1853, '240301', 'TUMBES', 'ZARUMILLA', 'ZARUMILLA', 'ZARUMILLA', NULL, NULL),
(1854, '240302', 'TUMBES', 'ZARUMILLA', 'AGUAS VERDES', 'AGUAS VERDES', NULL, NULL),
(1855, '240303', 'TUMBES', 'ZARUMILLA', 'MATAPALO', 'MATAPALO', NULL, NULL),
(1856, '240304', 'TUMBES', 'ZARUMILLA', 'PAPAYAL', 'PAPAYAL', NULL, NULL),
(1857, '250101', 'UCAYALI', 'CORONEL PORTILLO', 'CALLERIA', 'PUCALLPA', NULL, NULL),
(1858, '250102', 'UCAYALI', 'CORONEL PORTILLO', 'CAMPOVERDE', 'CAMPO VERDE', NULL, NULL),
(1859, '250103', 'UCAYALI', 'CORONEL PORTILLO', 'IPARIA', 'IPARIA', NULL, NULL),
(1860, '250104', 'UCAYALI', 'CORONEL PORTILLO', 'MASISEA', 'MASISEA', NULL, NULL),
(1861, '250105', 'UCAYALI', 'CORONEL PORTILLO', 'YARINACOCHA', 'PUERTO CALLAO', NULL, NULL),
(1862, '250106', 'UCAYALI', 'CORONEL PORTILLO', 'NUEVA REQUENA', 'NUEVA REQUENA', NULL, NULL),
(1863, '250107', 'UCAYALI', 'CORONEL PORTILLO', 'MANANTAY', 'SAN FERNANDO', NULL, NULL),
(1864, '250201', 'UCAYALI', 'ATALAYA', 'RAYMONDI', 'ATALAYA', NULL, NULL),
(1865, '250202', 'UCAYALI', 'ATALAYA', 'SEPAHUA', 'SEPAHUA', NULL, NULL),
(1866, '250203', 'UCAYALI', 'ATALAYA', 'TAHUANIA', 'BOLOGNESI', NULL, NULL),
(1867, '250204', 'UCAYALI', 'ATALAYA', 'YURUA', 'BREU', NULL, NULL),
(1868, '250301', 'UCAYALI', 'PADRE ABAD', 'PADRE ABAD', 'AGUAYTIA', NULL, NULL),
(1869, '250302', 'UCAYALI', 'PADRE ABAD', 'IRAZOLA', 'SAN ALEJANDRO', NULL, NULL),
(1870, '250303', 'UCAYALI', 'PADRE ABAD', 'CURIMANA', 'CURIMANA', NULL, NULL),
(1871, '250304', 'UCAYALI', 'PADRE ABAD', 'NESHUYA', 'MONTE ALEGRE', NULL, NULL),
(1872, '250305', 'UCAYALI', 'PADRE ABAD', 'ALEXANDER VON HUMBOLDT', 'ALEXANDER VON HUMBOLDT', NULL, NULL),
(1873, '250401', 'UCAYALI', 'PURUS', 'PURUS', 'ESPERANZA', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id_users` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `username` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `profile_picture` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `two_factor_secret` text COLLATE utf8mb4_unicode_ci,
  `two_factor_recovery_codes` text COLLATE utf8mb4_unicode_ci,
  `two_factor_confirmed_at` timestamp NULL DEFAULT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `users_token` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `users_token_time` datetime DEFAULT NULL,
  `users_status` tinyint DEFAULT '1' COMMENT '1 Activo - 0 Desactivado',
  `users_dni` int DEFAULT NULL COMMENT 'DNI del usuario opcional',
  `users_phone` int DEFAULT NULL COMMENT 'Teléfono del usuario opcional',
  `users_birthdate` int DEFAULT NULL COMMENT 'Fecha de nacimiento del usuario opcional',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id_users`, `name`, `last_name`, `email`, `username`, `profile_picture`, `email_verified_at`, `password`, `two_factor_secret`, `two_factor_recovery_codes`, `two_factor_confirmed_at`, `remember_token`, `users_token`, `users_token_time`, `users_status`, `users_dni`, `users_phone`, `users_birthdate`, `created_at`, `updated_at`) VALUES
(1, 'Eder Alfredo', 'Apagueño Reyna', 'reynaalfredo421@gmail.com', 'superadmin', NULL, NULL, '$2y$12$SzVs4f6gyPoOTubGPTKniOI6.2YAocQp2HdyNkP5ytf2eieU8lYeu', NULL, NULL, NULL, 'sUyE3fRvvgAiEH0kbOJze1o0zBLUNM5rcveHGV4XuYeuGYwZZ5FYvkoBrdtU', NULL, NULL, 1, NULL, 956449198, NULL, NULL, '2024-09-17 14:09:24'),
(4, 'Carlos Melendez', 'Bernuy', 'puntostyle05@gmail.com', 'admin', 'uploads/configuration/users/20240907_231301_4.jpg', NULL, '$2y$12$wa3/J4wkejuv3TYYXsoU3uY62tRI4mt16GFlbcdmCxiqzTwi6I2Dy', NULL, NULL, NULL, 'aStyRsnZ35rOgZ8NgU2HUcYeTx79S33eiDdLTNfv1Csu4pghNvoryyDSRQJy', NULL, NULL, 1, NULL, NULL, NULL, '2024-09-08 03:33:39', '2024-09-16 02:06:13');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`);

--
-- Indexes for table `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`);

--
-- Indexes for table `empresas`
--
ALTER TABLE `empresas`
  ADD PRIMARY KEY (`id_empresa`),
  ADD KEY `empresas_id_ubigeo_foreign` (`id_ubigeo`);

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indexes for table `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

--
-- Indexes for table `job_batches`
--
ALTER TABLE `job_batches`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `menus`
--
ALTER TABLE `menus`
  ADD PRIMARY KEY (`id_menu`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `model_has_permissions`
--
ALTER TABLE `model_has_permissions`
  ADD PRIMARY KEY (`permission_id`,`model_id`,`model_type`),
  ADD KEY `model_has_permissions_model_id_model_type_index` (`model_id`,`model_type`);

--
-- Indexes for table `model_has_roles`
--
ALTER TABLE `model_has_roles`
  ADD PRIMARY KEY (`role_id`,`model_id`,`model_type`),
  ADD KEY `model_has_roles_model_id_model_type_index` (`model_id`,`model_type`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `notifications_notifiable_type_notifiable_id_index` (`notifiable_type`,`notifiable_id`);

--
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indexes for table `permissions`
--
ALTER TABLE `permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `permissions_name_guard_name_unique` (`name`,`guard_name`),
  ADD KEY `permissions_id_menu_foreign` (`id_menu`),
  ADD KEY `permissions_id_submenu_foreign` (`id_submenu`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `roles_name_guard_name_unique` (`name`,`guard_name`);

--
-- Indexes for table `role_has_permissions`
--
ALTER TABLE `role_has_permissions`
  ADD PRIMARY KEY (`permission_id`,`role_id`),
  ADD KEY `role_has_permissions_role_id_foreign` (`role_id`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indexes for table `submenus`
--
ALTER TABLE `submenus`
  ADD PRIMARY KEY (`id_submenu`),
  ADD KEY `submenus_id_menu_foreign` (`id_menu`);

--
-- Indexes for table `ubigeos`
--
ALTER TABLE `ubigeos`
  ADD PRIMARY KEY (`id_ubigeo`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id_users`),
  ADD UNIQUE KEY `users_email_unique` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `empresas`
--
ALTER TABLE `empresas`
  MODIFY `id_empresa` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `menus`
--
ALTER TABLE `menus`
  MODIFY `id_menu` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `permissions`
--
ALTER TABLE `permissions`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=61;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `submenus`
--
ALTER TABLE `submenus`
  MODIFY `id_submenu` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `ubigeos`
--
ALTER TABLE `ubigeos`
  MODIFY `id_ubigeo` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1874;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id_users` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `empresas`
--
ALTER TABLE `empresas`
  ADD CONSTRAINT `empresas_id_ubigeo_foreign` FOREIGN KEY (`id_ubigeo`) REFERENCES `ubigeos` (`id_ubigeo`);

--
-- Constraints for table `model_has_permissions`
--
ALTER TABLE `model_has_permissions`
  ADD CONSTRAINT `model_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `model_has_roles`
--
ALTER TABLE `model_has_roles`
  ADD CONSTRAINT `model_has_roles_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `permissions`
--
ALTER TABLE `permissions`
  ADD CONSTRAINT `permissions_id_menu_foreign` FOREIGN KEY (`id_menu`) REFERENCES `menus` (`id_menu`),
  ADD CONSTRAINT `permissions_id_submenu_foreign` FOREIGN KEY (`id_submenu`) REFERENCES `submenus` (`id_submenu`);

--
-- Constraints for table `role_has_permissions`
--
ALTER TABLE `role_has_permissions`
  ADD CONSTRAINT `role_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `role_has_permissions_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `submenus`
--
ALTER TABLE `submenus`
  ADD CONSTRAINT `submenus_id_menu_foreign` FOREIGN KEY (`id_menu`) REFERENCES `menus` (`id_menu`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
