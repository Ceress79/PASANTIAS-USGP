<?php
session_start();

require_once "../admin/db/conexion.php";

// 1. Verificar seguridad
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$mensaje = '';
$tipo_mensaje = ''; // Para cambiar el color de la alerta (verde/rojo)

// 2. Procesar el formulario cuando se envía (POST)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Limpiamos los datos de entrada
    $nuevos_nombres = trim($_POST['nombres']);
    $nuevos_apellidos = trim($_POST['apellidos']);
    $nueva_clave = trim($_POST['password']);

    if (empty($nuevos_nombres) || empty($nuevos_apellidos)) {
        $mensaje = "El nombre y apellido son obligatorios.";
        $tipo_mensaje = "error";
    } else {
        try {
            if (!empty($nueva_clave)) {
                // Si el usuario escribió algo en la contraseña, la actualizamos (encriptada)
                $clave_hash = password_hash($nueva_clave, PASSWORD_DEFAULT);
                $sql = "UPDATE users SET nombres = ?, apellidos = ?, password = ? WHERE id = ?";
                $update = $pdo->prepare($sql);
                $update->execute([$nuevos_nombres, $nuevos_apellidos, $clave_hash, $user_id]);
            } else {
                // Si el campo contraseña está vacío, NO la tocamos, solo actualizamos nombres
                $sql = "UPDATE users SET nombres = ?, apellidos = ? WHERE id = ?";
                $update = $pdo->prepare($sql);
                $update->execute([$nuevos_nombres, $nuevos_apellidos, $user_id]);
            }

            $mensaje = "¡Datos actualizados correctamente!";
            $tipo_mensaje = "success";

        } catch (PDOException $e) {
            $mensaje = "Error en la base de datos: " . $e->getMessage();
            $tipo_mensaje = "error";
        }
    }
}

// 3. Obtener los datos actuales del usuario para mostrarlos en los inputs
$stmt = $pdo->prepare("SELECT nombres, apellidos, email FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$usuario) {
    header("Location: login.php");
    exit();
}

// 4. Incluir Header (Tu header inteligente se encarga del inicio de HTML)
include('../bases/header.php'); 
?>

<link rel="stylesheet" href="../style/css/perfil.css">

<div class="perfil-wrapper">
    <div class="perfil-container">

        <div class="perfil-header">
            <h2><i class="fas fa-user-edit"></i> Mi Perfil</h2>
            <p>Actualiza tu información personal</p>
        </div>

        <?php if (!empty($mensaje)): ?>
            <div class="alerta <?php echo $tipo_mensaje; ?>">
                <?php echo $mensaje; ?>
            </div>
        <?php endif; ?>

        <form action="" method="POST" class="perfil-form">
            
            <div class="form-group">
                <label>Correo Electrónico</label>
                <input type="email" value="<?php echo htmlspecialchars($usuario['email']); ?>" disabled class="input-disabled">
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="nombres">Nombres</label>
                    <input type="text" name="nombres" id="nombres" value="<?php echo htmlspecialchars($usuario['nombres']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="apellidos">Apellidos</label>
                    <input type="text" name="apellidos" id="apellidos" value="<?php echo htmlspecialchars($usuario['apellidos']); ?>" required>
                </div>
            </div>

            <div class="form-group password-section">
                <label for="password">Cambiar Contraseña</label>
                <input type="password" name="password" id="password" placeholder="Deja vacío para mantener la actual">
                <small>Solo llena este campo si deseas cambiar tu clave actual.</small>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn-guardar">
                    <i class="fas fa-save"></i> Guardar Cambios
                </button>
            </div>

        </form>

        <div class="perfil-footer-links">
            <a href="../index.php" class="link-volver"><i class="fas fa-arrow-left"></i> Volver a la tienda</a>
            <a href="logout.php" class="link-logout"><i class="fas fa-sign-out-alt"></i> Cerrar Sesión</a>
        </div>

    </div>
</div>

<?php include('../bases/footer.php'); ?>
</body>
</html>