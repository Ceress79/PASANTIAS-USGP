<?php
session_start();

// Si ya está logueado, que vaya al perfil o dashboard del cliente
if (isset($_SESSION['user_id'])) {
    header("Location: cliente_panel.php");
    exit();
}

// Mostrar error si existe
$error_para_mostrar = null;
if (isset($_SESSION['login_error_user'])) {
    $error_para_mostrar = $_SESSION['login_error_user'];
    unset($_SESSION['login_error_user']);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Usuario</title>
    <link rel="stylesheet" href="../admin/css/login.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
</head>
<body>

<div class="login-container">
    <div class="login-box">
        <div class="logo-container">
            <img src="../style/img/logo2.png" class="logo">
        </div>

        <h2>Iniciar Sesión</h2>

        <?php if($error_para_mostrar): ?>
            <p style="color:red;"><?php echo htmlspecialchars($error_para_mostrar); ?></p>
        <?php endif; ?>

        <form action="user_acciones.php" method="POST">
            <input type="hidden" name="accion" value="login">

            <div class="input-group">
                <i class="fas fa-envelope"></i>
                <input type="email" name="email" placeholder="Correo electrónico" required>
            </div>

            <div class="input-group">
                <i class="fas fa-lock"></i>
                
                <input type="password" name="password" id="password" class="input-pass" placeholder="Contraseña" required>
                
                <i class="fas fa-eye toggle-password" onclick="togglePass()"></i>
            </div>

            <div style="text-align: right; margin-bottom: 10px;">
                <a href="cambiar_contrasenia.php" class="olvidar-pass">
                    ¿Olvidaste tu contraseña?
                </a>
            </div>

            <button type="submit" class="btn-login">Ingresar</button>

            <p style="margin-top:15px;">
                ¿No tienes cuenta?
                <a href="registrar.php">Crear una cuenta</a>
            </p>
        </form>
    </div>
</div>

<?php if (isset($_SESSION['register_success'])): ?>
    <div class="toast success-toast">
        <?php 
            echo $_SESSION['register_success']; 
            unset($_SESSION['register_success']);
        ?>
    </div>
<?php endif; ?>

<script>
    function togglePass() {
        const input = document.getElementById("password");
        const icon = document.querySelector(".toggle-password");

        if (input.type === "password") {
            input.type = "text";
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            input.type = "password";
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    }
</script>

</body>
</html>