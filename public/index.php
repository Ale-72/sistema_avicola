<?php

/**
 * Front Controller - Punto de entrada de la aplicación
 * AVITECH - Sistema Integral de Gestión Avícola
 */

// Configuración
define('ROOT_PATH', __DIR__ . '/..');
require_once ROOT_PATH . '/config/config.php';

// Cargar clases del core
require_once ROOT_PATH . '/core/Database.php';
require_once ROOT_PATH . '/core/Session.php';
require_once ROOT_PATH . '/core/Controller.php';

// Iniciar sesión
Session::start();

// Obtener la URL solicitada
$url = isset($_GET['url']) ? rtrim($_GET['url'], '/') : '';
$url = filter_var($url, FILTER_SANITIZE_URL);
$url = explode('/', $url);

// Determinar controlador, método y parámetros
$controllerName = !empty($url[0]) ? ucfirst($url[0]) . 'Controller' : 'HomeController';
$method = isset($url[1]) && !empty($url[1]) ? $url[1] : 'index';
$params = array_slice($url, 2);

// Verificar si existe el controlador
$controllerFile = ROOT_PATH . '/controllers/' . $controllerName . '.php';

if (file_exists($controllerFile)) {
    require_once $controllerFile;

    $controller = new $controllerName();

    // Verificar si existe el método
    if (method_exists($controller, $method)) {
        call_user_func_array([$controller, $method], $params);
    } else {
        // Método no encontrado
        require_once ROOT_PATH . '/controllers/ErrorController.php';
        $errorController = new ErrorController();
        $errorController->error404();
    }
} else {
    // Controlador no encontrado
    require_once ROOT_PATH . '/controllers/ErrorController.php';
    $errorController = new ErrorController();
    $errorController->error404();
}
