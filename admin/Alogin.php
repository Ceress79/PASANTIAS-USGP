<?php
session_start();

// Si ya está logueado, lo mandamos al panel
if (isset($_SESSION['admin_id'])) {
    header("Location: Aindex.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Administrador - USGP</title>
    <link rel="stylesheet" href="css/login.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
</head>
<body>

    <div class="login-container">
        <div class="login-box">
            <div class="logo-container">
                <img src="../style/img/logo2.png" alt="Logo USGP" class="logo">
            </div>
            <h2>Panel de Administrador</h2>
            <?php if(isset($_GET['error'])): ?>
                <p style="color:red; margin-bottom:15px;"><?php echo htmlspecialchars($_GET['error']); ?></p>
            <?php endif; ?>
            <form action="acciones.php" method="POST">
                <div class="input-group">
                    <i class="fas fa-user-shield"></i>
                    <input type="text" name="email" placeholder="Usuario" required>
                </div>
                <div class="input-group">
                    <i class="fas fa-lock"></i>
                    <input type="password" name="password" placeholder="Contraseña" required>
                </div>
                <button type="submit" class="btn-login">Ingresar</button>
            </form>
        </div>
    </div>

</body>
</html>
