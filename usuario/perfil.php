<?php
session_start();
require_once "../admin/db/conexion.php";

// 1. Verificar seguridad
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// 2. Gestionar Mensajes de Sesión (vienen de acciones_perfil.php)
$mensaje = '';
$tipo_mensaje = '';

if (isset($_SESSION['perfil_mensaje'])) {
    $mensaje = $_SESSION['perfil_mensaje'];
    $tipo_mensaje = $_SESSION['perfil_tipo']; // 'success' o 'error'
    
    // Limpiamos las variables para que no salgan al recargar
    unset($_SESSION['perfil_mensaje']);
    unset($_SESSION['perfil_tipo']);
}

// 3. Obtener los datos actuales del usuario
$stmt = $pdo->prepare("SELECT nombres, apellidos, email FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$usuario) {
    header("Location: login.php");
    exit();
}

// 4. Incluir Header
include('../bases/header.php'); 
?>

<link rel="stylesheet" href="../style/css/perfil.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

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

        <form action="acciones_perfil.php" method="POST" class="perfil-form">
            
            <div class="form-group">
                <label>Correo Electrónico</label>
                <input type="email" value="<?php echo htmlspecialchars($usuario['email']); ?>" disabled class="input-disabled" title="El correo no se puede cambiar">
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
                
                <div class="password-wrapper">
                    <input type="password" name="password" id="password" placeholder="Deja vacío para mantener la actual">
                    <i class="fas fa-eye toggle-password" onclick="togglePass('password', this)"></i>
                </div>
                
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

<?php include('../bases/footer.php'); ?>
</body>
</html>