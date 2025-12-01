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

    <!-- Tu CSS del registro -->
    <link rel="stylesheet" href="../style/css/register.css">

    <!-- Iconos -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
</head>
<body>

<div class="container">

    <!-- Imagen izquierda -->
    <div class="left-section">
        <img src="../style/img/login/usgp_registro.jpg" alt="Imagen Registro">
    </div>

    <!-- Formulario derecha -->
    <div class="right-section">

        <div class="form-box">

            <h2 class= "red">Crear Cuenta</h2>

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
                    <input type="password" name="password" placeholder="Contraseña" required>
                </div>

                <div class="input-group">
                    <i class="fas fa-lock"></i>
                    <input type="password" name="confirmar" placeholder="Confirmar contraseña" required>
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

</body>
</html>
