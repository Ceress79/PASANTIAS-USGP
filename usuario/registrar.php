<?php
session_start();

if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$error_para_mostrar = null;
if (isset($_SESSION['register_error'])) {
    $error_para_mostrar = $_SESSION['register_error'];
    unset($_SESSION['register_error']);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro Usuario</title>

    <link rel="stylesheet" href="../style/css/register.css">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
</head>
<body>

<div class="container">

    <div class="left-section">
        <img src="../style/img/login/usgp_registro.jpg" alt="Imagen Registro">
    </div>

    <div class="right-section">

        <div class="form-box">

            <h2 class="red">Crear Cuenta</h2>

            <?php if($error_para_mostrar): ?>
                <p class="error-msg"><?php echo htmlspecialchars($error_para_mostrar); ?></p>
            <?php endif; ?>

            <form action="user_acciones.php" method="POST">

                <input type="hidden" name="accion" value="register">

                <div class="input-group">
                    <i class="fas fa-user"></i>
                    <input type="text" name="nombres" placeholder="Nombre" required>
                </div>

                <div class="input-group">
                    <i class="fas fa-user"></i>
                    <input type="text" name="apellidos" placeholder="Apellido" required>
                </div>

                <div class="input-group">
                    <i class="fas fa-envelope"></i>
                    <input type="email" name="email" placeholder="Correo electrónico" required>
                </div>

                <div class="input-group">
                    <i class="fas fa-lock"></i>
                    <input type="password" name="password" id="pass1" class="input-pass" placeholder="Contraseña" required>
                    <i class="fas fa-eye toggle-password" onclick="togglePass('pass1', this)"></i>
                </div>

                <div class="input-group">
                    <i class="fas fa-lock"></i>
                    <input type="password" name="confirmar" id="pass2" class="input-pass" placeholder="Confirmar contraseña" required>
                    <i class="fas fa-eye toggle-password" onclick="togglePass('pass2', this)"></i>
                </div>

                <button class="btn-register" type="submit">Registrarme</button>

                <p class="change-page">
                    ¿Ya tienes una cuenta?  
                    <a href="login.php">Iniciar sesión</a>
                </p>

            </form>
        </div>
    </div>
</div>

<script>
    function togglePass(inputId, iconElement) {
        const input = document.getElementById(inputId);
        
        if (input.type === "password") {
            input.type = "text";
            iconElement.classList.remove('fa-eye');
            iconElement.classList.add('fa-eye-slash');
        } else {
            input.type = "password";
            iconElement.classList.remove('fa-eye-slash');
            iconElement.classList.add('fa-eye');
        }
    }
</script>

</body>
</html>