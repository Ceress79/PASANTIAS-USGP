<?php
session_start();
require_once "../admin/db/conexion.php";


// Si no existe la conexión, marcar error
if (!isset($pdo)) {
    die("Error: No se pudo cargar la conexión a la base de datos.");
}

if (!isset($_POST['accion'])) {
    header("Location: ../index.php");
    exit();
}

$accion = $_POST['accion'];

/*
|--------------------------------------------------------------------------
| 1. REGISTRO DE USUARIO
|--------------------------------------------------------------------------
*/
if ($accion === "register") {

    $nombres   = trim($_POST['nombres']);
    $apellidos = trim($_POST['apellidos']);
    $email     = trim($_POST['email']);
    $password  = trim($_POST['password']);
    $confirmar = trim($_POST['confirmar']);

    if ($password !== $confirmar) {
        $_SESSION['register_error'] = "Las contraseñas no coinciden.";
        header("Location: registrar.php");
        exit();
    }

    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);

    if ($stmt->fetch()) {
        $_SESSION['register_error'] = "El correo ya está registrado.";
        header("Location: registrar.php");
        exit();
    }

    $password_hash = password_hash($password, PASSWORD_BCRYPT);

    $stmt = $pdo->prepare("
        INSERT INTO users (uuid, nombres, apellidos, email, password_hash)
        VALUES (UUID(), ?, ?, ?, ?)
    ");

    $ok = $stmt->execute([$nombres, $apellidos, $email, $password_hash]);

    if ($ok) {
        $_SESSION['register_success'] = "Cuenta creada correctamente. Ahora puedes iniciar sesión.";
        header("Location: login.php");
        exit();
    } else {
        $_SESSION['register_error'] = "Error al registrar. Intente más tarde.";
        header("Location: registrar.php");
        exit();
    }
}


/*
|--------------------------------------------------------------------------
| 2. LOGIN DE USUARIO
|--------------------------------------------------------------------------
*/
if ($accion === "login") {

    $email    = trim($_POST['email']);
    $password = trim($_POST['password']);

    $stmt = $pdo->prepare("SELECT id, password_hash FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($usuario && password_verify($password, $usuario['password_hash'])) {
        $_SESSION['user_id'] = $usuario['id'];
        header("Location: ../index.php");
        exit();
    } else {
        $_SESSION['login_error_user'] = "Credenciales incorrectas.";
        header("Location: login.php");
        exit();
    }
}

/*
|--------------------------------------------------------------------------
| 3. SI LLEGA AQUÍ, NO EXISTE LA ACCIÓN
|--------------------------------------------------------------------------
*/
header("Location: ../index.php");
exit();
