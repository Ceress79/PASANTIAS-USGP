<?php
session_start();

// Si ya está logueado, lo mandamos al panel
if (isset($_SESSION['admin_id'])) {
    header("Location: Aindex.php");
    exit();
}

// LÓGICA DE ERRORES:
$error_para_mostrar = null;
if (isset($_SESSION['login_error'])) {
    $error_para_mostrar = $_SESSION['login_error'];
    unset($_SESSION['login_error']);
}

// Mensaje de éxito (por si viene de recuperar contraseña)
$success_para_mostrar = null;
if (isset($_SESSION['login_success'])) {
    $success_para_mostrar = $_SESSION['login_success'];
    unset($_SESSION['login_success']);
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
    <style>
        /* Pequeño ajuste para el link de recuperar */
        .forgot-link {
            display: block;
            text-align: right;
            margin-top: 10px;
            font-size: 0.9em;
            color: #666;
            text-decoration: none;
        }
        .forgot-link:hover {
            color: #333;
            text-decoration: underline;
        }
        .alert-success {
            color: #155724;
            background-color: #d4edda;
            border-color: #c3e6cb;
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 4px;
            text-align: center;
        }
    </style>
</head>
<body>

    <div class="login-container">
        <div class="login-box">
            <div class="logo-container">
                <img src="../style/img/logo2.png" alt="Logo USGP" class="logo">
            </div>
            
            <h2>Panel de Administrador</h2>
            
            <?php if($error_para_mostrar): ?>
                <p style="color:red; margin-bottom:15px; text-align:center;"><?php echo htmlspecialchars($error_para_mostrar); ?></p>
            <?php endif; ?>

            <?php if($success_para_mostrar): ?>
                <div class="alert-success"><?php echo htmlspecialchars($success_para_mostrar); ?></div>
            <?php endif; ?>

            <form action="acciones.php" method="POST">
                <div class="input-group">
                    <i class="fas fa-user-shield"></i>
                    <input type="text" name="email" placeholder="Usuario o Email" required>
                </div>
                <div class="input-group">
                    <i class="fas fa-lock"></i>
                    <input type="password" name="password" placeholder="Contraseña" required>
                </div>
                
                <div style="text-align: right;">
                    <a href="recuperar.php" class="forgot-link">¿Olvidaste tu contraseña?</a>
                </div>

                <button type="submit" class="btn-login" style="margin-top: 20px;">Ingresar</button>
            </form>
        </div>
    </div>

</body>
</html>