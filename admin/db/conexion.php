<?php
// admin/db/conexion.php

$host = "localhost";
$dbname = "usgpcommerce";
$user = "root"; // cámbialo si tienes usuario diferente
$pass = "";     // cámbialo si tienes contraseña

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("❌ Error de conexión: " . $e->getMessage());
}
?>
