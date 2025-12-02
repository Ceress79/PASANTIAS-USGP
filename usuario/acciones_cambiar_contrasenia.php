<?php
session_start();
require_once "../admin/db/conexion.php";

// --- CARGA DE LIBRERÍAS PHPMAILER ---
// Ajusta estas rutas si tu estructura de carpetas es diferente
require '../admin/libs/PHPMailer/src/Exception.php';
require '../admin/libs/PHPMailer/src/PHPMailer.php';
require '../admin/libs/PHPMailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// --- CONFIGURACIÓN DE GMAIL ---
define('MAIL_HOST', 'smtp.gmail.com');
define('MAIL_USER', 'kateiutm@gmail.com');
define('MAIL_PASS', 'plqptuddocnwqyyf'); 
define('MAIL_PORT', 465);

// Seguridad: Si alguien intenta entrar directo sin POST, lo sacamos
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: cambiar_contrasenia.php");
    exit();
}

$accion = isset($_POST['accion']) ? $_POST['accion'] : '';

/*
|--------------------------------------------------------------------------
| CASO 1: ENVIAR CÓDIGO A CORREO
|--------------------------------------------------------------------------
*/
if ($accion === 'enviar_codigo') {
    $email = trim($_POST['email']); // Limpiamos espacios del correo
    
    // Verificar si existe el usuario y traer su nombre
    $stmt = $pdo->prepare("SELECT id, email, nombres FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        $_SESSION['recup_error'] = "El usuario no se encuentra registrado.";
        header("Location: cambiar_contrasenia.php");
        exit();
    } else {
        // Generar código de 6 dígitos y fecha de expiración (15 min)
        $codigo = rand(100000, 999999);
        $expira = date('Y-m-d H:i:s', strtotime('+15 minutes'));

        // Guardar el token en la base de datos
        $upd = $pdo->prepare("UPDATE users SET recuperacion_token = ?, recuperacion_expira = ? WHERE id = ?");
        $upd->execute([$codigo, $expira, $user['id']]);

        // Configurar y enviar el correo
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host       = MAIL_HOST;
            $mail->SMTPAuth   = true;
            $mail->Username   = MAIL_USER;
            $mail->Password   = MAIL_PASS;
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port       = MAIL_PORT;

            $mail->setFrom(MAIL_USER, 'Soporte USGP Commerce');
            $mail->addAddress($user['email'], $user['nombres']);

            $mail->isHTML(true);
            $mail->CharSet = 'UTF-8';
            $mail->Subject = 'Código de recuperación de contraseña';
            
            // Cuerpo del correo con estilo simple
            $mail->Body    = "
                <div style='font-family: Arial, sans-serif; color: #333; max-width: 500px; margin: 0 auto; border: 1px solid #ddd; padding: 20px; border-radius: 8px;'>
                    <h2 style='color: #0056b3;'>Hola, " . htmlspecialchars($user['nombres']) . "</h2>
                    <p>Has solicitado restablecer tu contraseña. Usa el siguiente código para continuar:</p>
                    <div style='background: #f8f9fa; padding: 15px; font-size: 24px; font-weight: bold; text-align: center; letter-spacing: 5px; color: #333; border: 1px dashed #ccc; margin: 20px 0;'>
                        $codigo
                    </div>
                    <p style='font-size: 0.9em; color: #666;'>Este código expira en 15 minutos.</p>
                    <hr style='border: 0; border-top: 1px solid #eee;'>
                    <p style='font-size: 0.8em; color: #999; text-align: center;'>Si no solicitaste esto, ignora este mensaje.</p>
                </div>
            ";

            $mail->send();

            // Guardar datos en sesión para avanzar al paso 2
            $_SESSION['recup_step'] = 2;
            $_SESSION['recup_email'] = $user['email'];
            $_SESSION['recup_id'] = $user['id'];
            
            header("Location: cambiar_contrasenia.php");
            exit();

        } catch (Exception $e) {
            $_SESSION['recup_error'] = "Error al enviar correo: {$mail->ErrorInfo}";
            header("Location: cambiar_contrasenia.php");
            exit();
        }
    }
}

/*
|--------------------------------------------------------------------------
| CASO 2: VALIDAR CÓDIGO
|--------------------------------------------------------------------------
*/
if ($accion === 'validar_codigo') {
    // Concatenamos los 6 inputs del formulario OTP
    $codigo_ingresado = trim($_POST['codigo1']) . trim($_POST['codigo2']) . trim($_POST['codigo3']) . trim($_POST['codigo4']) . trim($_POST['codigo5']) . trim($_POST['codigo6']);
    
    $user_id = isset($_SESSION['recup_id']) ? $_SESSION['recup_id'] : 0;

    $stmt = $pdo->prepare("SELECT recuperacion_token, recuperacion_expira FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $data = $stmt->fetch();

    $ahora = date('Y-m-d H:i:s');

    // Validamos que el código coincida y no haya expirado
    if ($data && $data['recuperacion_token'] === $codigo_ingresado && $data['recuperacion_expira'] > $ahora) {
        // Código correcto, pasamos al paso 3
        $_SESSION['recup_step'] = 3;
        header("Location: cambiar_contrasenia.php");
        exit();
    } else {
        $_SESSION['recup_error'] = "Código incorrecto o expirado.";
        header("Location: cambiar_contrasenia.php");
        exit();
    }
}

/*
|--------------------------------------------------------------------------
| CASO 3: CAMBIAR CONTRASEÑA
|--------------------------------------------------------------------------
*/
if ($accion === 'cambiar_pass') {
    // IMPORTANTE: Usamos trim() igual que en login.php/registro para evitar errores de espacios invisibles
    $pass1 = trim($_POST['new_password']);
    $pass2 = trim($_POST['confirm_password']);

    // 1. Validar que no estén vacías
    if (empty($pass1) || empty($pass2)) {
        $_SESSION['recup_error'] = "La contraseña no puede estar vacía.";
        header("Location: cambiar_contrasenia.php");
        exit();
    }

    // 2. Validar que coincidan
    if ($pass1 !== $pass2) {
        $_SESSION['recup_error'] = "Las contraseñas no coinciden.";
        header("Location: cambiar_contrasenia.php");
        exit();
    } else {
        $user_id = isset($_SESSION['recup_id']) ? $_SESSION['recup_id'] : 0;
        
        // 3. Encriptar contraseña (Igual que en registro)
        $hash = password_hash($pass1, PASSWORD_BCRYPT);

        // 4. Actualizar password y limpiar el token usado
        $upd = $pdo->prepare("UPDATE users SET password_hash = ?, recuperacion_token = NULL, recuperacion_expira = NULL WHERE id = ?");
        $resultado = $upd->execute([$hash, $user_id]);

        if ($resultado) {
            // Limpiar variables de sesión de recuperación
            unset($_SESSION['recup_step']);
            unset($_SESSION['recup_email']);
            unset($_SESSION['recup_id']);
            if(isset($_SESSION['recup_error'])) unset($_SESSION['recup_error']);

            // Mensaje de éxito para login.php
            $_SESSION['register_success'] = "Contraseña actualizada correctamente. Por favor inicia sesión.";
            header("Location: login.php");
            exit();
        } else {
            $_SESSION['recup_error'] = "Hubo un error en la base de datos al actualizar.";
            header("Location: cambiar_contrasenia.php");
            exit();
        }
    }
}

// Si no coincide ninguna acción, devolver al inicio
header("Location: cambiar_contrasenia.php");
exit();
?>