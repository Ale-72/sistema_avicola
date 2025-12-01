<?php

/**
 * Configuración de la base de datos
 * AVITECH - Sistema Integral de Gestión Avícola
 */

// Configuración de la base de datos
define('DB_HOST', 'localhost');
define('DB_NAME', 'avitech_db');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// Configuración de la aplicación
define('APP_NAME', 'AVITECH');

// Detectar URL base automáticamente (funciona en localhost y hosting)
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $_SERVER['SERVER_PORT'] == 443 ? "https://" : "http://";
$host = $_SERVER['HTTP_HOST'];
$scriptPath = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
$scriptPath = rtrim($scriptPath, '/');
define('APP_URL', $protocol . $host . $scriptPath);

define('APP_VERSION', '1.0.0');

// Rutas del sistema (solo definir si no existen)
if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', dirname(__DIR__));
}
define('APP_PATH', ROOT_PATH . '/app');
define('PUBLIC_PATH', ROOT_PATH . '/public');
define('UPLOAD_PATH', PUBLIC_PATH . '/uploads');

// Configuración de sesiones
define('SESSION_LIFETIME', 7200); // 2 horas
define('SESSION_NAME', 'AVITECH_SESSION');

// Configuración de seguridad
define('HASH_ALGO', PASSWORD_DEFAULT);
define('HASH_COST', 12);

// Zona horaria
date_default_timezone_set('America/Lima');

// Manejo de errores
if ($_ENV['APP_ENV'] ?? 'production' === 'development') {
    ini_set('display_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
    error_reporting(0);
}
