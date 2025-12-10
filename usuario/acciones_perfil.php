<?php
session_start();
require_once "../admin/db/conexion.php";

// 1. Verificar seguridad: Debe ser POST y el usuario debe estar logueado
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: perfil.php");
    exit();
}

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// 2. Recibir datos
$user_id = $_SESSION['user_id'];
$nuevos_nombres = trim($_POST['nombres']);
$nuevos_apellidos = trim($_POST['apellidos']);
$nueva_clave = trim($_POST['password']);

// 3. Validaciones básicas
if (empty($nuevos_nombres) || empty($nuevos_apellidos)) {
    $_SESSION['perfil_mensaje'] = "El nombre y apellido son obligatorios.";
    $_SESSION['perfil_tipo'] = "error";
    header("Location: perfil.php");
    exit();
}

try {
    // 4. Lógica de actualización
    if (!empty($nueva_clave)) {
        // --- CASO A: El usuario escribió una nueva contraseña ---
        
        // Encriptamos la clave (usando BCRYPT como en tu registro)
        $clave_hash = password_hash($nueva_clave, PASSWORD_BCRYPT);
        
        // Actualizamos nombres, apellidos y la contraseña
        $sql = "UPDATE users SET nombres = ?, apellidos = ?, password_hash = ? WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $resultado = $stmt->execute([$nuevos_nombres, $nuevos_apellidos, $clave_hash, $user_id]);

    } else {
        // --- CASO B: El campo contraseña estaba vacío (Mantiene la actual) ---
        
        // Actualizamos SOLAMENTE nombres y apellidos
        $sql = "UPDATE users SET nombres = ?, apellidos = ? WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $resultado = $stmt->execute([$nuevos_nombres, $nuevos_apellidos, $user_id]);
    }

    // 5. Verificar resultado y redirigir
    if ($resultado) {
        $_SESSION['perfil_mensaje'] = "¡Perfil actualizado correctamente!";
        $_SESSION['perfil_tipo'] = "success";
    } else {
        $_SESSION['perfil_mensaje'] = "No se realizaron cambios o hubo un error.";
        $_SESSION['perfil_tipo'] = "error";
    }

} catch (PDOException $e) {
    $_SESSION['perfil_mensaje'] = "Error en base de datos: " . $e->getMessage();
    $_SESSION['perfil_tipo'] = "error";
}

// Volver al perfil
header("Location: perfil.php");
exit();
?>