<?php
session_start();

require_once "../admin/db/conexion.php";

// Si NO hay sesión → redirigir al login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Obtener información del usuario logueado
$stmt = $pdo->prepare("SELECT nombres, apellidos, email FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

// Si por alguna razón el usuario no existe
if (!$usuario) {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Perfil</title>
    <link rel="stylesheet" href="../style/css/perfil.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
</head>
<body>

    <div class="perfil-container">

        <h2>Mi Perfil</h2>

        <div class="perfil-box">
            <p><strong>Nombre:</strong> <?= htmlspecialchars($usuario['nombres']) ?></p>
            <p><strong>Apellido:</strong> <?= htmlspecialchars($usuario['apellidos']) ?></p>
            <p><strong>Correo:</strong> <?= htmlspecialchars($usuario['email']) ?></p>
        </div>

        <a href="logout.php" class="btn-logout">
            <i class="fas fa-sign-out-alt"></i> Cerrar sesión
        </a>

        <a href="../index.php" class="btn-volver">
            Volver a la tienda
        </a>

    </div>

</body>
</html>
