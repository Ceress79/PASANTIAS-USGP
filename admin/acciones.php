<?php
session_start();
require_once "db/conexion.php"; // Ajusta la ruta si tu conexión está en otro lugar

// Validar formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);       // Recibimos el campo "email" que en realidad es tu usuario "admin"
    $password = $_POST['password'];

    try {
        // Buscar el usuario administrador
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND role = 'ADMIN' LIMIT 1");
        $stmt->execute([$email]);
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);

        // ✅ Para pruebas con contraseña en texto plano
        if ($admin && $password === $admin['password_hash']) {
            // Credenciales correctas → crear sesión
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_nombre'] = $admin['nombres'];
            $_SESSION['admin_email'] = $admin['email'];
            $_SESSION['admin_uuid'] = $admin['uuid'];

            header("Location: Aindex.php");
            exit();
        } else {
            header("Location: Alogin.php?error=Credenciales incorrectas");
            exit();
        }
    } catch (PDOException $e) {
        header("Location: Alogin.php?error=Error interno del servidor");
        exit();
    }
} else {
    header("Location: Alogin.php");
    exit();
}
