<?php
// Script temporal para generar hash de contraseña
echo "Hash generado para 'admin123':\n";
echo password_hash('admin123', PASSWORD_DEFAULT);
echo "\n";
