<?php
// Test 3: Verificar valor exacto del rol desde BD
define('ROOT_PATH', __DIR__ . '/..');
require_once ROOT_PATH . '/config/config.php';
require_once ROOT_PATH . '/core/Database.php';

$db = Database::getInstance()->getConnection();

echo "<h2>Test: Verificar Rol en Base de Datos</h2>";
echo "<pre>";

$sql = "SELECT u.id_usuario, u.nombre_completo, u.email, r.nombre_rol 
        FROM usuarios u 
        INNER JOIN roles r ON u.id_rol = r.id_rol 
        WHERE u.email = 'admin@avitech.com'";

$stmt = $db->query($sql);
$user = $stmt->fetch();

echo "Datos del usuario Admin:\n";
print_r($user);

echo "\n\nValor exacto del rol:\n";
echo "Rol (raw): '" . $user['nombre_rol'] . "'\n";
echo "Longitud: " . strlen($user['nombre_rol']) . " caracteres\n";
echo "Bytes: " . bin2hex($user['nombre_rol']) . "\n";

echo "\n\nComparaciones:\n";
echo "¿Es 'Administrador'? " . ($user['nombre_rol'] === 'Administrador' ? 'SI' : 'NO') . "\n";
echo "¿Es 'administrador'? " . ($user['nombre_rol'] === 'administrador' ? 'SI' : 'NO') . "\n";
echo "¿Es 'ADMINISTRADOR'? " . ($user['nombre_rol'] === 'ADMINISTRADOR' ? 'SI' : 'NO') . "\n";

echo "\n\nCORRECCIÓN NECESARIA:\n";
echo "El AdminController debe comparar con: '" . $user['nombre_rol'] . "'\n";

echo "</pre>";
