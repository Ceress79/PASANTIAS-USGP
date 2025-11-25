<?php
// Queremos que la contraseña sea: admin123
$password_deseada = 'admin123';

// Generamos el hash nuevo
$nuevo_hash = password_hash($password_deseada, PASSWORD_DEFAULT);

echo "<h1>Copia este código largo:</h1>";
echo "<p style='background:#eee; padding:10px; font-size:20px;'>" . $nuevo_hash . "</p>";
?>