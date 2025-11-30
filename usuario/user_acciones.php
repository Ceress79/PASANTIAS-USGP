<?php
session_start();
require_once("../diseñador/conector.php");


$accion = $_POST['accion'] ?? null;



if ($accion === "register") {

    $nombres = trim($_POST['nombres']);
    $apellidos = trim($_POST['apellidos']);
    $email = trim($_POST['email']);
    $pass = $_POST['password'];
    $confirm = $_POST['confirmar'];

    if ($pass !== $confirm) {
        $_SESSION['register_error'] = "Las contraseñas no coinciden.";
        header("Location: register.php");
        exit();
    }

    // Validar que no exista
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);

    if ($stmt->rowCount() > 0) {
        $_SESSION['register_error'] = "El correo ya está registrado.";
        header("Location: register.php");
        exit();
    }

    // Crear usuario
    $hash = password_hash($pass, PASSWORD_DEFAULT);

    $sql = "INSERT INTO users (uuid, nombres, apellidos, email, password_hash, role, estado_cuenta) 
            VALUES (UUID(), ?, ?, ?, ?, 'USER', 'CREADA')";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$nombres, $apellidos, $email, $hash]);

    // 👉 GUARDAR MENSAJE DE ÉXITO PARA EL LOGIN
    $_SESSION['register_success'] = "Cuenta creada con éxito. Ya puedes iniciar sesión.";

    header("Location: login.php");
    exit();
}



if ($_POST['accion'] === 'login') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Buscar usuario en la BD
    $stmt = $pdo->prepare("SELECT id, password FROM usuarios WHERE email = ?");
    $stmt->execute([$email]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($usuario && password_verify($password, $usuario['password'])) {
        // Guardar datos en sesión
        $_SESSION['user_id'] = $usuario['id'];

        // REDIRECCIÓN CORRECTA
        header("Location: index.php");
        exit();
    } else {
        $_SESSION['login_error_user'] = "Credenciales incorrectas";
        header("Location: login.php");
        exit();
    }
}

?>
