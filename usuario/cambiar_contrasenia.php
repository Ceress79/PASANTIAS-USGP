<?php
session_start();

// Recuperar el paso actual (si no existe, es 1)
$step = isset($_SESSION['recup_step']) ? $_SESSION['recup_step'] : 1;

// Recuperar errores y borrarlos de la sesión para que no salgan siempre
$error = null;
if (isset($_SESSION['recup_error'])) {
    $error = $_SESSION['recup_error'];
    unset($_SESSION['recup_error']);
}

// Función auxiliar para vista
function maskEmail($email) {
    $parts = explode("@", $email);
    if(count($parts) < 2) return $email;
    $name = implode('@', array_slice($parts, 0, count($parts)-1));
    $visible = substr($name, 0, 1); 
    return $visible . str_repeat('*', 5) . "@" . end($parts);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar Contraseña</title>
    <link rel="stylesheet" href="../admin/css/login.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
    <style>
        .otp-container { display: flex; gap: 10px; justify-content: center; margin: 20px 0; }
        .otp-input { width: 40px; height: 50px; text-align: center; font-size: 1.5rem; border: 1px solid #ccc; border-radius: 5px; }
        .toast-error { background-color: #ff4d4d; color: white; padding: 10px; border-radius: 5px; position: fixed; top: 20px; right: 20px; z-index: 1000; animation: fadein 0.5s; }
        @keyframes fadein { from { opacity: 0; top: 0; } to { opacity: 1; top: 20px; } }
        .info-text { font-size: 0.9em; color: #666; text-align: center; margin-bottom: 20px; }
        .timer { font-weight: bold; color: #333; }
    </style>
</head>
<body>

<!-- Mostrar Alerta de Error -->
<?php if($error): ?>
    <div class="toast-error">
        <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
    </div>
    <script>
        setTimeout(() => { 
            const toast = document.querySelector('.toast-error');
            if(toast) toast.style.display = 'none'; 
        }, 4000);
    </script>
<?php endif; ?>

<div class="login-container">
    <div class="login-box">
        <div class="logo-container">
            <img src="../style/img/logo2.png" class="logo">
        </div>

        <!-- PASO 1 -->
        <?php if ($step == 1): ?>
            <h2>Ayuda con la contraseña</h2>
            <p class="info-text">Ingresa tu correo electrónico para buscar tu cuenta.</p>

            <form action="acciones_cambiar_contrasenia.php" method="POST">
                <input type="hidden" name="accion" value="enviar_codigo">
                <div class="input-group">
                    <i class="fas fa-envelope"></i>
                    <input type="email" name="email" placeholder="Correo electrónico" required>
                </div>
                <button type="submit" class="btn-login">Continuar</button>
                <p style="margin-top:15px; text-align:center;">
                    <a href="login.php">Volver al login</a>
                </p>
            </form>
        <?php endif; ?>

        <!-- PASO 2 -->
        <?php if ($step == 2): ?>
            <h2>Ingresar código</h2>
            <p class="info-text">
                Para tu seguridad, enviamos el código a tu correo electrónico <br>
                <strong><?php echo isset($_SESSION['recup_email']) ? maskEmail($_SESSION['recup_email']) : '***@gmail.com'; ?></strong>
            </p>

            <form action="acciones_cambiar_contrasenia.php" method="POST" id="formCodigo">
                <input type="hidden" name="accion" value="validar_codigo">
                
                <div class="otp-container">
                    <input type="text" name="codigo1" class="otp-input" maxlength="1" required oninput="moveFocus(this, 'c2')">
                    <input type="text" name="codigo2" id="c2" class="otp-input" maxlength="1" required oninput="moveFocus(this, 'c3')">
                    <input type="text" name="codigo3" id="c3" class="otp-input" maxlength="1" required oninput="moveFocus(this, 'c4')">
                    <input type="text" name="codigo4" id="c4" class="otp-input" maxlength="1" required oninput="moveFocus(this, 'c5')">
                    <input type="text" name="codigo5" id="c5" class="otp-input" maxlength="1" required oninput="moveFocus(this, 'c6')">
                    <input type="text" name="codigo6" id="c6" class="otp-input" maxlength="1" required>
                </div>

                <button type="submit" class="btn-login">Enviar código</button>
            </form>

            <div style="margin-top: 20px; text-align: center; font-size: 0.85em;">
                <p>Reenviar código</p>
                <p id="timerMsg">Espera <span class="timer" id="countdown">57</span> segundos antes de solicitar otro código.</p>
                
                <!-- Formulario separado para el reenvío -->
                <form action="acciones_cambiar_contrasenia.php" method="POST" style="display:none;" id="formReenviar">
                    <input type="hidden" name="accion" value="enviar_codigo">
                    <input type="hidden" name="email" value="<?php echo $_SESSION['recup_email']; ?>">
                    <button type="submit" style="background:none; border:none; color:#007bff; cursor:pointer; text-decoration: underline;">Reenviar ahora</button>
                </form>
            </div>

            <script>
                function moveFocus(current, nextFieldID) {
                    if (current.value.length >= 1) {
                        document.getElementById(nextFieldID).focus();
                    }
                }
                let timeLeft = 57;
                const elem = document.getElementById('countdown');
                const timerId = setInterval(function(){
                    if(timeLeft <= 0){
                        clearInterval(timerId);
                        document.getElementById('timerMsg').style.display = 'none';
                        document.getElementById('formReenviar').style.display = 'block';
                    } else {
                        elem.innerHTML = timeLeft;
                        timeLeft--;
                    }
                }, 1000);
            </script>
        <?php endif; ?>

        <!-- PASO 3 -->
        <?php if ($step == 3): ?>
            <h2>Cambiar Contraseña</h2>
            <p class="info-text">Crea una contraseña nueva y segura.</p>

            <form action="acciones_cambiar_contrasenia.php" method="POST">
                <input type="hidden" name="accion" value="cambiar_pass">

                <div class="input-group">
                    <i class="fas fa-lock"></i>
                    <input type="password" name="new_password" placeholder="Nueva contraseña" required minlength="6">
                </div>

                <div class="input-group">
                    <i class="fas fa-lock"></i>
                    <input type="password" name="confirm_password" placeholder="Confirmar contraseña" required minlength="6">
                </div>

                <button type="submit" class="btn-login">Guardar</button>
            </form>
        <?php endif; ?>

    </div>
</div>

</body>
</html>