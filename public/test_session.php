<?php
// Script de test de sesiones
session_start();

echo "<h2>Test de Sesiones</h2>";
echo "<pre>";

// Simular lo que hace AuthController
$_SESSION['user_id'] = 1;
$_SESSION['user_name'] = 'Admin Test';
$_SESSION['user_role'] = 'Administrador';
$_SESSION['user_email'] = 'admin@avitech.com';

echo "Sesiones establecidas:\n";
print_r($_SESSION);

echo "\n\nVerificación:\n";
echo "¿user_id existe? " . (isset($_SESSION['user_id']) ? 'SI' : 'NO') . "\n";
echo "¿user_role existe? " . (isset($_SESSION['user_role']) ? 'SI' : 'NO') . "\n";
echo "¿Autenticado? " . ((isset($_SESSION['user_id']) && isset($_SESSION['user_role'])) ? 'SI' : 'NO') . "\n";

echo "\n\nID de sesión: " . session_id();
echo "\nRuta de sesión: " . session_save_path();

echo "</pre>";
