<?php
session_start();
require_once "db/conexion.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    try {
        // Enviar los intentos al log del servidor (no al navegador)
        error_log("Intento de login -> Usuario: $email | Contraseña: $password");

        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND role = 'ADMIN' LIMIT 1");
        $stmt->execute([$email]);
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$admin) {
            error_log("No se encontró usuario con email: $email");
        } else {
            error_log("Usuario encontrado: {$admin['email']}");
        }

        if ($admin && password_verify($password, $admin['password_hash'])) {
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_nombre'] = $admin['nombres'];
            $_SESSION['admin_email'] = $admin['email'];
            $_SESSION['admin_uuid'] = $admin['uuid'];

            header("Location: Aindex.php");
            exit();
        } else {
            $_SESSION['login_error'] = "Credenciales incorrectas";
            header("Location: Alogin.php");
            exit();
        }

    } catch (PDOException $e) {
        error_log("Error de conexión: " . $e->getMessage());
        $_SESSION['login_error'] = "Error interno del servidor";
        header("Location: Alogin.php");
        exit();
    }

} else {
    header("Location: Alogin.php");
    exit();
}
