<?php
session_start();
require_once "db/conexion.php"; // Ajusta si tu conexión está en otro lado

// Cargar PHPMailer (Estamos en la carpeta admin/, así que entramos a libs/)
require 'libs/PHPMailer/src/Exception.php';
require 'libs/PHPMailer/src/PHPMailer.php';
require 'libs/PHPMailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// --- CONFIGURACIÓN CORREO ---
define('MAIL_HOST', 'smtp.gmail.com');
define('MAIL_USER', 'kateiutm@gmail.com');
define('MAIL_PASS', 'plqptuddocnwqyyf'); 
define('MAIL_PORT', 465);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: recuperar.php");
    exit();
}

$accion = $_POST['accion'] ?? '';

// --------------------------------------------------------------------------
// 1. ENVIAR CÓDIGO (SOLO SI ES ADMIN)
// --------------------------------------------------------------------------
if ($accion === 'enviar_codigo') {
    $email = trim($_POST['email']);

    // IMPORTANTE: Filtrar por role = 'ADMIN' para seguridad
    $stmt = $pdo->prepare("SELECT id, email, nombres FROM users WHERE email = ? AND role = 'ADMIN'");
    $stmt->execute([$email]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$admin) {
        // Mensaje genérico o específico según prefieras
        $_SESSION['admin_recup_error'] = "No existe una cuenta administrativa con ese correo.";
        header("Location: recuperar.php");
        exit();
    }

    // Generar Token
    $codigo = rand(100000, 999999);
    $expira = date('Y-m-d H:i:s', strtotime('+15 minutes'));

    // Guardar Token
    $upd = $pdo->prepare("UPDATE users SET recuperacion_token = ?, recuperacion_expira = ? WHERE id = ?");
    $upd->execute([$codigo, $expira, $admin['id']]);

    // Enviar Email
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = MAIL_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = MAIL_USER;
        $mail->Password   = MAIL_PASS;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port       = MAIL_PORT;

        $mail->setFrom(MAIL_USER, 'Seguridad USGP Admin');
        $mail->addAddress($admin['email'], $admin['nombres']);
        $mail->isHTML(true);
        $mail->CharSet = 'UTF-8';
        $mail->Subject = 'Código de Acceso Administrativo';
        $mail->Body    = "
            <h2>Hola Administrador, {$admin['nombres']}</h2>
            <p>Usa el siguiente código para acceder o cambiar tu contraseña:</p>
            <h1 style='background:#eee; padding:10px; display:inline-block; letter-spacing:5px;'>$codigo</h1>
            <p>Este código expira en 15 minutos.</p>
        ";

        $mail->send();

        $_SESSION['admin_recup_step']  = 2;
        $_SESSION['admin_recup_email'] = $admin['email'];
        $_SESSION['admin_recup_id']    = $admin['id'];
        
        header("Location: recuperar.php");
        exit();

    } catch (Exception $e) {
        $_SESSION['admin_recup_error'] = "Error al enviar correo: " . $mail->ErrorInfo;
        header("Location: recuperar.php");
        exit();
    }
}

// --------------------------------------------------------------------------
// 2. VALIDAR CÓDIGO
// --------------------------------------------------------------------------
if ($accion === 'validar_codigo') {
    // Unir los 6 campos
    $codigo = '';
    for($i=1; $i<=6; $i++) $codigo .= trim($_POST["c$i"] ?? '');

    $id = $_SESSION['admin_recup_id'] ?? 0;
    
    $stmt = $pdo->prepare("SELECT recuperacion_token, recuperacion_expira FROM users WHERE id = ? AND role='ADMIN'");
    $stmt->execute([$id]);
    $data = $stmt->fetch();

    $ahora = date('Y-m-d H:i:s');

    if ($data && $data['recuperacion_token'] === $codigo && $data['recuperacion_expira'] > $ahora) {
        $_SESSION['admin_recup_step'] = 3; // Pasar a cambiar password
        header("Location: recuperar.php");
        exit();
    } else {
        $_SESSION['admin_recup_error'] = "Código incorrecto o expirado.";
        header("Location: recuperar.php");
        exit();
    }
}

// --------------------------------------------------------------------------
// 3. CAMBIAR CONTRASEÑA
// --------------------------------------------------------------------------
if ($accion === 'cambiar_pass') {
    $p1 = trim($_POST['p1']);
    $p2 = trim($_POST['p2']);

    if (empty($p1) || $p1 !== $p2) {
        $_SESSION['admin_recup_error'] = "Las contraseñas no coinciden o están vacías.";
        header("Location: recuperar.php");
        exit();
    }

    $id = $_SESSION['admin_recup_id'] ?? 0;
    $hash = password_hash($p1, PASSWORD_BCRYPT);

    $upd = $pdo->prepare("UPDATE users SET password_hash = ?, recuperacion_token = NULL, recuperacion_expira = NULL, estado_cuenta = 'CREADA' WHERE id = ?");
    $ok = $upd->execute([$hash, $id]);

    if ($ok) {
        // Limpiar sesión admin de recuperación
        unset($_SESSION['admin_recup_step']);
        unset($_SESSION['admin_recup_email']);
        unset($_SESSION['admin_recup_id']);
        
        $_SESSION['login_success'] = "Contraseña actualizada. Bienvenido.";
        header("Location: Alogin.php");
        exit();
    } else {
        $_SESSION['admin_recup_error'] = "Error en base de datos.";
        header("Location: recuperar.php");
        exit();
    }
}

// --------------------------------------------------------------------------
// CANCELAR
// --------------------------------------------------------------------------
if ($accion === 'cancelar') {
    unset($_SESSION['admin_recup_step']);
    unset($_SESSION['admin_recup_email']);
    unset($_SESSION['admin_recup_id']);
    header("Location: Alogin.php");
    exit();
}

header("Location: recuperar.php");
exit();
?>