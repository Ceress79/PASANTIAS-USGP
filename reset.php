<?php
session_start();
session_destroy(); // Destruye toda la sesión
session_start();   // La inicia limpia de nuevo
$_SESSION['carrito'] = []; // Asegura el array vacío

echo "<div style='font-family:sans-serif; text-align:center; padding:50px;'>";
echo "<h1 style='color:green;'>¡Carrito Limpio! ✨</h1>";
echo "<p>Se han borrado todos los productos corruptos.</p>";
echo "<a href='index.php' style='padding:10px 20px; background:#333; color:white; text-decoration:none; border-radius:5px;'>Volver al Inicio</a>";
echo "</div>";
?>