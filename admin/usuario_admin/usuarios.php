<?php
session_start();
// Ajustar ruta de conexión subiendo 2 niveles
require_once "../../admin/db/conexion.php"; 

if (!isset($_SESSION['admin_id'])) {
    header("Location: ../Alogin.php");
    exit();
}

// VERIFICAR QUE EL QUE ENTRA AQUÍ TENGA PERMISO 'usuarios'
$stmt = $pdo->prepare("SELECT id FROM admin_permisos WHERE user_id = ? AND modulo = 'usuarios'");
$stmt->execute([$_SESSION['admin_id']]);
if (!$stmt->fetch()) {
    die("<h1>Acceso Denegado</h1><p>No tienes permiso para gestionar usuarios.</p><a href='../Aindex.php'>Volver</a>");
}

// Obtener lista de administradores
$query = "
    SELECT u.id, u.nombres, u.apellidos, u.email, u.estado_cuenta, u.creado_en,
    GROUP_CONCAT(ap.modulo SEPARATOR ',') as permisos
    FROM users u
    LEFT JOIN admin_permisos ap ON u.id = ap.user_id
    WHERE u.role = 'ADMIN'
    GROUP BY u.id
    ORDER BY u.id ASC
";
$admins = $pdo->query($query)->fetchAll(PDO::FETCH_ASSOC);

// Mensajes de error/exito
$error = isset($_SESSION['user_admin_error']) ? $_SESSION['user_admin_error'] : null;
$success = isset($_SESSION['user_admin_success']) ? $_SESSION['user_admin_success'] : null;
unset($_SESSION['user_admin_error']);
unset($_SESSION['user_admin_success']);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Administradores</title>
    <!-- Reutilizamos CSS del admin -->
    <link rel="stylesheet" href="../../admin/css/admin.css"> 
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
    <style>
        /* Estilos base */
        .container-usuarios { display: flex; gap: 20px; padding: 20px; }
        .form-section { flex: 1; background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); height: fit-content; }
        .list-section { flex: 2; background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: bold; }
        .form-group input { width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; }
        
        .permisos-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-top: 10px; }
        .permiso-item { display: flex; align-items: center; gap: 5px; font-size: 0.9em; cursor: pointer; }
        
        .table-users { width: 100%; border-collapse: collapse; margin-top: 10px; }
        .table-users th, .table-users td { padding: 10px; text-align: left; border-bottom: 1px solid #eee; font-size: 0.9em; }
        .table-users th { background-color: #f8f9fa; }
        
        .badge { padding: 3px 8px; border-radius: 10px; font-size: 0.8em; background: #eee; margin-right: 2px; display: inline-block; margin-bottom: 2px;}
        .btn-delete { color: #dc3545; background: none; border: none; cursor: pointer; font-size: 1.1em; }
        .btn-edit { color: #0d6efd; background: none; border: none; cursor: pointer; font-size: 1.1em; margin-right: 10px; }
        .btn-submit { background-color: #0d6efd; color: white; padding: 10px 15px; border: none; border-radius: 5px; cursor: pointer; width: 100%; }
        
        .alert { padding: 10px; border-radius: 5px; margin-bottom: 15px; }
        .alert-error { background: #f8d7da; color: #721c24; }
        .alert-success { background: #d1e7dd; color: #0f5132; }

        /* --- ESTILOS DEL MODAL --- */
        .modal { display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgba(0,0,0,0.5); }
        .modal-content { background-color: #fefefe; margin: 10% auto; padding: 25px; border: 1px solid #888; width: 400px; border-radius: 8px; box-shadow: 0 4px 8px rgba(0,0,0,0.2); position: relative; }
        .close-modal { color: #aaa; float: right; font-size: 28px; font-weight: bold; cursor: pointer; position: absolute; top: 10px; right: 15px; }
        .close-modal:hover { color: black; }
        .modal-title { margin-bottom: 20px; color: #333; border-bottom: 1px solid #eee; padding-bottom: 10px; }
    </style>
</head>
<body>

    <div style="display:flex;">
        <?php include "../../admin/sidebar.php"; ?>

        <main class="main-content" style="flex:1; background:#f4f6f9; min-height:100vh;">
            <header style="background:white; padding:15px; border-bottom:1px solid #ddd; display:flex; justify-content:space-between; align-items:center;">
                <h2><i class="fas fa-users-cog"></i> Gestión de Administradores</h2>
                <div>Hola, <?php echo htmlspecialchars($_SESSION['admin_nombre'] ?? 'Admin'); ?></div>
            </header>

            <div class="container-usuarios">
                
                <!-- 1. FORMULARIO DE CREACIÓN -->
                <div class="form-section">
                    <h3>Nuevo Administrador</h3>
                    
                    <?php if($error): ?>
                        <div class="alert alert-error"><?php echo $error; ?></div>
                    <?php endif; ?>
                    <?php if($success): ?>
                        <div class="alert alert-success"><?php echo $success; ?></div>
                    <?php endif; ?>

                    <form action="acciones_usuarios.php" method="POST">
                        <input type="hidden" name="accion" value="crear_admin">
                        
                        <div class="form-group">
                            <label>Nombres</label>
                            <input type="text" name="nombres" required>
                        </div>
                        <div class="form-group">
                            <label>Apellidos</label>
                            <input type="text" name="apellidos" required>
                        </div>
                        <div class="form-group">
                            <label>Email (Login)</label>
                            <input type="email" name="email" required>
                        </div>
                        
                        <div class="form-group">
                            <label>Permisos de Acceso:</label>
                            <div class="permisos-grid">
                                <label class="permiso-item"><input type="checkbox" name="permisos[]" value="banners"> Banners</label>
                                <label class="permiso-item"><input type="checkbox" name="permisos[]" value="productos"> Productos</label>
                                <label class="permiso-item"><input type="checkbox" name="permisos[]" value="contactos"> Contactos</label>
                                <label class="permiso-item"><input type="checkbox" name="permisos[]" value="compras"> Compras</label>
                                <label class="permiso-item"><input type="checkbox" name="permisos[]" value="usuarios"> Gest. Usuarios</label>
                                <label class="permiso-item"><input type="checkbox" name="permisos[]" value="reportes"> Reportes</label>
                                <label class="permiso-item"><input type="checkbox" name="permisos[]" value="configuracion"> Configuración</label>
                            </div>
                        </div>

                        <button type="submit" class="btn-submit">Crear Usuario</button>
                    </form>
                </div>

                <!-- 2. LISTA DE USUARIOS -->
                <div class="list-section">
                    <h3>Administradores Actuales</h3>
                    <table class="table-users">
                        <thead>
                            <tr>
                                <th>Nombre</th>
                                <th>Email</th>
                                <th>Permisos</th>
                                <th>Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($admins as $adm): ?>
                            <tr>
                                <td>
                                    <?php echo htmlspecialchars($adm['nombres'] . ' ' . $adm['apellidos']); ?>
                                    <?php if($adm['id'] == $_SESSION['admin_id']) echo " <small style='color:green'>(Tú)</small>"; ?>
                                </td>
                                <td><?php echo htmlspecialchars($adm['email']); ?></td>
                                <td>
                                    <?php 
                                    if ($adm['permisos']) {
                                        $p = explode(',', $adm['permisos']);
                                        foreach($p as $perm) {
                                            echo "<span class='badge'>$perm</span>";
                                        }
                                    } else {
                                        echo "<span style='color:#999'>Sin acceso</span>";
                                    }
                                    ?>
                                </td>
                                <td style="display:flex; align-items:center;">
                                    <?php if($adm['id'] != 1): // Ocultar acciones para el Super Admin ID 1 ?>
                                        
                                        <!-- BOTÓN EDITAR (Abre Modal) -->
                                        <button type="button" class="btn-edit" 
                                            onclick="editarPermisos('<?php echo $adm['id']; ?>', '<?php echo $adm['nombres']; ?>', '<?php echo $adm['permisos']; ?>')">
                                            <i class="fas fa-edit"></i>
                                        </button>

                                        <?php if($adm['id'] != $_SESSION['admin_id']): ?>
                                            <form action="acciones_usuarios.php" method="POST" onsubmit="return confirm('¿Eliminar este admin?');" style="margin:0;">
                                                <input type="hidden" name="accion" value="eliminar_admin">
                                                <input type="hidden" name="id_eliminar" value="<?php echo $adm['id']; ?>">
                                                <button type="submit" class="btn-delete"><i class="fas fa-trash"></i></button>
                                            </form>
                                        <?php endif; ?>

                                    <?php else: ?>
                                        <span style="color:#ccc; font-size:0.8em;">Super Admin</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

            </div>
        </main>
    </div>

    <!-- 3. MODAL DE EDICIÓN DE PERMISOS -->
    <div id="modalPermisos" class="modal">
        <div class="modal-content">
            <span class="close-modal" onclick="cerrarModal()">&times;</span>
            <h3 class="modal-title">Editar Permisos</h3>
            <p style="margin-bottom:15px;">Usuario: <strong id="modalUsuarioNombre">...</strong></p>
            
            <form action="acciones_usuarios.php" method="POST">
                <input type="hidden" name="accion" value="editar_permisos">
                <input type="hidden" name="user_id" id="modalUserId">

                <div class="permisos-grid">
                    <label class="permiso-item"><input type="checkbox" name="permisos[]" value="banners" id="p_banners"> Banners</label>
                    <label class="permiso-item"><input type="checkbox" name="permisos[]" value="productos" id="p_productos"> Productos</label>
                    <label class="permiso-item"><input type="checkbox" name="permisos[]" value="contactos" id="p_contactos"> Contactos</label>
                    <label class="permiso-item"><input type="checkbox" name="permisos[]" value="compras" id="p_compras"> Compras</label>
                    <label class="permiso-item"><input type="checkbox" name="permisos[]" value="usuarios" id="p_usuarios"> Gest. Usuarios</label>
                    <label class="permiso-item"><input type="checkbox" name="permisos[]" value="reportes" id="p_reportes"> Reportes</label>
                    <label class="permiso-item"><input type="checkbox" name="permisos[]" value="configuracion" id="p_configuracion"> Configuración</label>
                </div>

                <div style="margin-top:20px; text-align:right;">
                    <button type="button" onclick="cerrarModal()" style="padding:8px 15px; border:1px solid #ddd; background:white; border-radius:4px; cursor:pointer;">Cancelar</button>
                    <button type="submit" class="btn-submit" style="width:auto; display:inline-block; margin-left:10px;">Guardar Cambios</button>
                </div>
            </form>
        </div>
    </div>

    <!-- 4. SCRIPTS PARA EL MODAL -->
    <script>
        const modal = document.getElementById("modalPermisos");

        function editarPermisos(id, nombre, permisosStr) {
            // 1. Poner datos en el modal
            document.getElementById("modalUserId").value = id;
            document.getElementById("modalUsuarioNombre").innerText = nombre;

            // 2. Limpiar todos los checkboxes
            const checks = document.querySelectorAll('#modalPermisos input[type="checkbox"]');
            checks.forEach(c => c.checked = false);

            // 3. Marcar los que tenga el usuario
            if(permisosStr) {
                const arr = permisosStr.split(',');
                arr.forEach(p => {
                    const chk = document.getElementById('p_' + p.trim());
                    if(chk) chk.checked = true;
                });
            }

            // 4. Mostrar modal
            modal.style.display = "block";
        }

        function cerrarModal() {
            modal.style.display = "none";
        }

        // Cerrar si clic fuera del modal
        window.onclick = function(event) {
            if (event.target == modal) {
                cerrarModal();
            }
        }
    </script>

</body>
</html>