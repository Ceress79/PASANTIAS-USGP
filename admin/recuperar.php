<?php
session_start();

// Control de pasos (1: Email, 2: Código, 3: Password)
$step = isset($_SESSION['admin_recup_step']) ? $_SESSION['admin_recup_step'] : 1;

// Recuperar errores
$error = null;
if (isset($_SESSION['admin_recup_error'])) {
    $error = $_SESSION['admin_recup_error'];
    unset($_SESSION['admin_recup_error']);
}

function maskEmail($email) {
    $parts = explode("@", $email);
    if(count($parts) < 2) return $email;
    $name = implode('@', array_slice($parts, 0, count($parts)-1));
    $visible = substr($name, 0, 2); 
    return $visible . str_repeat('*', 5) . "@" . end($parts);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar Acceso Admin</title>
    <link rel="stylesheet" href="css/login.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
    <style>
        .otp-container { display: flex; gap: 8px; justify-content: center; margin: 20px 0; }
        .otp-input { width: 40px; height: 50px; text-align: center; font-size: 1.5rem; border: 1px solid #ccc; border-radius: 5px; }
        .info-text { font-size: 0.9em; color: #666; text-align: center; margin-bottom: 20px; }
        .btn-back { display: block; text-align: center; margin-top: 15px; color: #555; text-decoration: none; font-size: 0.9em; }
        .toast-error { background-color: #dc3545; color: white; padding: 10px; border-radius: 5px; position: fixed; top: 20px; right: 20px; z-index: 9999; }
    </style>
</head>
<body>

<?php if($error): ?>
    <div class="toast-error"><i class="fas fa-exclamation-triangle"></i> <?php echo $error; ?></div>
    <script>setTimeout(() => { document.querySelector('.toast-error').style.display = 'none'; }, 4000);</script>
<?php endif; ?>

<div class="login-container">
    <div class="login-box">
        <div class="logo-container">
            <img src="../style/img/logo2.png" alt="Logo USGP" class="logo">
        </div>

        <!-- PASO 1: PEDIR EMAIL -->
        <?php if ($step == 1): ?>
            <h3>Recuperar Acceso</h3>
            <p class="info-text">Ingresa el correo electrónico asociado a tu cuenta de Administrador.</p>
            
            <form action="acciones_recuperar.php" method="POST">
                <input type="hidden" name="accion" value="enviar_codigo">
                <div class="input-group">
                    <i class="fas fa-envelope"></i>
                    <input type="email" name="email" placeholder="Correo administrativo" required>
                </div>
                <button type="submit" class="btn-login">Enviar Código</button>
                <a href="Alogin.php" class="btn-back">Volver al Login</a>
            </form>
        <?php endif; ?>

        <!-- PASO 2: VALIDAR CÓDIGO -->
        <?php if ($step == 2): ?>
            <h3>Verificación</h3>
            <p class="info-text">Hemos enviado un código a <strong><?php echo maskEmail($_SESSION['admin_recup_email'] ?? 'correo'); ?></strong></p>
            
            <form action="acciones_recuperar.php" method="POST">
                <input type="hidden" name="accion" value="validar_codigo">
                <div class="otp-container">
                    <?php for($i=1; $i<=6; $i++): ?>
                        <input type="text" name="c<?php echo $i; ?>" class="otp-input" maxlength="1" required 
                               oninput="if(this.value.length >= 1) { var next = document.getElementsByName('c<?php echo $i+1; ?>')[0]; if(next) next.focus(); }">
                    <?php endfor; ?>
                </div>
                <button type="submit" class="btn-login">Verificar</button>
            </form>
            <form action="acciones_recuperar.php" method="POST" style="margin-top:10px; text-align:center;">
                <input type="hidden" name="accion" value="cancelar">
                <button type="submit" style="background:none; border:none; color:#777; cursor:pointer; text-decoration:underline;">Cancelar</button>
            </form>
        <?php endif; ?>

        <!-- PASO 3: CAMBIAR PASSWORD -->
        <?php if ($step == 3): ?>
            <h3>Nueva Contraseña</h3>
            <p class="info-text">Establece tu nueva contraseña de acceso.</p>
            
            <form action="acciones_recuperar.php" method="POST">
                <input type="hidden" name="accion" value="cambiar_pass">
                <div class="input-group">
                    <i class="fas fa-lock"></i>
                    <input type="password" name="p1" placeholder="Nueva contraseña" required minlength="6">
                </div>
                <div class="input-group">
                    <i class="fas fa-lock"></i>
                    <input type="password" name="p2" placeholder="Confirmar contraseña" required minlength="6">
                </div>
                <button type="submit" class="btn-login">Actualizar Contraseña</button>
            </form>
        <?php endif; ?>
    </div>
</div>

</body>
</html>