<?php
session_start();
require_once "../../admin/db/conexion.php";

// Seguridad: Solo POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: usuarios.php");
    exit();
}

// Seguridad: Verificar permiso de sesión
$check = $pdo->prepare("SELECT id FROM admin_permisos WHERE user_id = ? AND modulo = 'usuarios'");
$check->execute([$_SESSION['admin_id']]);
if (!$check->fetch()) {
    die("No tienes permiso.");
}

$accion = $_POST['accion'];

/*
|--------------------------------------------------------------------------
| CREAR NUEVO ADMINISTRADOR
|--------------------------------------------------------------------------
*/
if ($accion === 'crear_admin') {
    $nombres = trim($_POST['nombres']);
    $apellidos = trim($_POST['apellidos']);
    $email = trim($_POST['email']);
    $permisos = isset($_POST['permisos']) ? $_POST['permisos'] : []; 

    // 1. Verificar si el email ya existe
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        $_SESSION['user_admin_error'] = "El correo ya está registrado en el sistema.";
        header("Location: usuarios.php");
        exit();
    }

    try {
        $pdo->beginTransaction();

        // 2. Crear Usuario
        $uuid = uniqid(); 
        $pass_temp = password_hash("CAMBIAR_12345", PASSWORD_BCRYPT);
        
        $sql = "INSERT INTO users (uuid, nombres, apellidos, email, password_hash, role, estado_cuenta) 
                VALUES (UUID(), ?, ?, ?, ?, 'ADMIN', 'CREADA')";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$nombres, $apellidos, $email, $pass_temp]);
        
        $new_user_id = $pdo->lastInsertId();

        // 3. Insertar Permisos
        if (!empty($permisos)) {
            $sql_perm = "INSERT INTO admin_permisos (user_id, modulo) VALUES (?, ?)";
            $stmt_perm = $pdo->prepare($sql_perm);
            
            foreach ($permisos as $modulo) {
                $stmt_perm->execute([$new_user_id, $modulo]);
            }
        }

        $pdo->commit();
        $_SESSION['user_admin_success'] = "Administrador creado correctamente.";
        
    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['user_admin_error'] = "Error al crear usuario: " . $e->getMessage();
    }

    header("Location: usuarios.php");
    exit();
}

/*
|--------------------------------------------------------------------------
| EDITAR PERMISOS (NUEVO BLOQUE)
|--------------------------------------------------------------------------
*/
if ($accion === 'editar_permisos') {
    $user_id = $_POST['user_id'];
    $permisos = isset($_POST['permisos']) ? $_POST['permisos'] : []; // Array de checkboxes

    // Proteger al Super Admin ID 1 de perder permisos accidentalmente
    if ($user_id == 1) {
        $_SESSION['user_admin_error'] = "No se pueden modificar los permisos del Super Admin principal.";
        header("Location: usuarios.php");
        exit();
    }

    try {
        $pdo->beginTransaction();

        // 1. Eliminar TODOS los permisos actuales de este usuario
        $stmt_del = $pdo->prepare("DELETE FROM admin_permisos WHERE user_id = ?");
        $stmt_del->execute([$user_id]);

        // 2. Insertar los NUEVOS permisos seleccionados
        if (!empty($permisos)) {
            $stmt_ins = $pdo->prepare("INSERT INTO admin_permisos (user_id, modulo) VALUES (?, ?)");
            foreach ($permisos as $modulo) {
                $stmt_ins->execute([$user_id, $modulo]);
            }
        }

        $pdo->commit();
        $_SESSION['user_admin_success'] = "Permisos actualizados correctamente.";

    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['user_admin_error'] = "Error al actualizar permisos: " . $e->getMessage();
    }

    header("Location: usuarios.php");
    exit();
}

/*
|--------------------------------------------------------------------------
| ELIMINAR ADMINISTRADOR
|--------------------------------------------------------------------------
*/
if ($accion === 'eliminar_admin') {
    $id_eliminar = $_POST['id_eliminar'];

    // 1. Buscar dinámicamente quién es el "Super Admin" (El primero que se creó)
    $stmt_super = $pdo->query("SELECT id FROM users WHERE role = 'ADMIN' ORDER BY id ASC LIMIT 1");
    $super_admin_id = $stmt_super->fetchColumn();

    // 2. PROTECCIONES DE SEGURIDAD
    if ($id_eliminar == $super_admin_id) {
        $_SESSION['user_admin_error'] = "No puedes eliminar al Administrador Principal (Original) del sistema.";
        header("Location: usuarios.php");
        exit();
    }

    if ($id_eliminar == $_SESSION['admin_id']) {
        $_SESSION['user_admin_error'] = "No puedes eliminar tu propia cuenta mientras estás logueado.";
        header("Location: usuarios.php");
        exit();
    }

    try {
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ? AND role = 'ADMIN'");
        $stmt->execute([$id_eliminar]);

        $_SESSION['user_admin_success'] = "Usuario eliminado correctamente.";
    } catch (Exception $e) {
        $_SESSION['user_admin_error'] = "Error al eliminar: " . $e->getMessage();
    }

    header("Location: usuarios.php");
    exit();
}
?>