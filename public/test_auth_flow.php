<?php
// Test 2: Simular el flujo completo de autenticación
define('ROOT_PATH', __DIR__ . '/..');
require_once ROOT_PATH . '/config/config.php';
require_once ROOT_PATH . '/core/Session.php';

Session::start();

echo "<h2>Test de Flujo de Autenticación</h2>";
echo "<pre>";

// Simular lo que hace AuthController::processLogin
echo "=== PASO 1: Establecer sesión (como en processLogin) ===\n";
Session::setUser(1, 'Admin Test', 'Administrador', 'admin@avitech.com');

echo "Session user_id: " . Session::get('user_id') . "\n";
echo "Session user_role: " . Session::get('user_role') . "\n";
echo "isAuthenticated(): " . (Session::isAuthenticated() ? 'true' : 'false') . "\n";
echo "getUserRole(): " . Session::getUserRole() . "\n";

echo "\n=== PASO 2: Verificar autenticación (como en AdminController) ===\n";
$isAuth = Session::isAuthenticated();
$role = Session::getUserRole();

echo "¿Está autenticado? " . ($isAuth ? 'SI' : 'NO') . "\n";
echo "Rol del usuario: " . ($role ?? 'NULL') . "\n";
echo "¿Es Administrador? " . ($role === 'Administrador' ? 'SI' : 'NO') . "\n";

echo "\n=== PASO 3: Contenido completo de $_SESSION ===\n";
print_r($_SESSION);

echo "\n=== RESULTADO ===\n";
if ($isAuth && $role === 'Administrador') {
    echo "✓ EL USUARIO DEBERÍA VER EL DASHBOARD\n";
} else {
    echo "✗ EL USUARIO SERÁ REDIRIGIDO AL LOGIN\n";
    echo "Razón: ";
    if (!$isAuth) echo "No autenticado\n";
    if ($role !== 'Administrador') echo "Rol incorrecto: '$role'\n";
}

echo "</pre>";
