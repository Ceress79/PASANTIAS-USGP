<aside class="sidebar">
    <div class="logo-container">
        <a href="Aindex.php"><img src="../style/img/logo2.png" alt="Logo USGP" class="logo"></a>
        <h2>Panel Admin</h2>
    </div>

    <?php
    // --- LÓGICA DE PERMISOS ---
    // Si no está la conexión cargada, la cargamos (por seguridad)
    if (!isset($pdo)) {
        // Ajusta la ruta si este archivo se incluye desde subcarpetas
        // Normalmente sidebar se incluye desde root/admin, así que:
        if (file_exists("db/conexion.php")) require_once "db/conexion.php";
        elseif (file_exists("../db/conexion.php")) require_once "../db/conexion.php";
    }

    $id_actual = $_SESSION['admin_id'] ?? 0;
    $permisos_usuario = [];

    // Si es el ID 1 (Super Admin), tiene acceso a todo manualmente o consultamos BD
    // Consultamos los permisos del usuario actual
    if (isset($pdo)) {
        $stmt = $pdo->prepare("SELECT modulo FROM admin_permisos WHERE user_id = ?");
        $stmt->execute([$id_actual]);
        // Creamos un array simple: ['productos', 'banners', ...]
        $permisos_usuario = $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
    
    $pagina_actual = basename($_SERVER['PHP_SELF']);
    ?>

    <nav>
        <ul>
            <!-- INICIO (Siempre visible para todos) -->
            <li><a href="Aindex.php" class="<?php echo $pagina_actual == 'Aindex.php' ? 'active' : ''; ?>">
                <i class="fas fa-home"></i> Inicio
            </a></li>

            <!-- BANNERS -->
            <?php if (in_array('banners', $permisos_usuario)): ?>
            <li><a href="Abanners.php" class="<?php echo $pagina_actual == 'Abanners.php' ? 'active' : ''; ?>">
                <i class="fas fa-images"></i> Banners Inicio
            </a></li>
            <?php endif; ?>

            <!-- PRODUCTOS -->
            <?php if (in_array('productos', $permisos_usuario)): ?>
            <li><a href="Aproductos.php" class="<?php echo $pagina_actual == 'Aproductos.php' ? 'active' : ''; ?>">
                <i class="fas fa-box-open"></i> Productos
            </a></li>
            <?php endif; ?>

            <!-- CONTACTOS -->
            <?php if (in_array('contactos', $permisos_usuario)): ?>
            <li><a href="Acontactos.php" class="<?php echo $pagina_actual == 'Acontactos.php' ? 'active' : ''; ?>">
                <i class="fas fa-address-book"></i> Contactos
            </a></li>
            <?php endif; ?>

            <!-- COMPRAS -->
            <?php if (in_array('compras', $permisos_usuario)): ?>
            <li><a href="compras.php" class="<?php echo $pagina_actual == 'compras.php' ? 'active' : ''; ?>">
                <i class="fas fa-shopping-cart"></i> Compras
            </a></li>
            <?php endif; ?>

            <!-- USUARIOS (SOLO ADMINS CON PERMISO DE GESTIONAR OTROS ADMINS) -->
            <?php if (in_array('usuarios', $permisos_usuario)): ?>
            <li><a href="usuario_admin/usuarios.php" class="<?php echo $pagina_actual == 'usuarios.php' ? 'active' : ''; ?>">
                <i class="fas fa-users-cog"></i> Gest. Usuarios
            </a></li>
            <?php endif; ?>

            <!-- REPORTES -->
            <?php if (in_array('reportes', $permisos_usuario)): ?>
            <li><a href="reportes.php" class="<?php echo $pagina_actual == 'reportes.php' ? 'active' : ''; ?>">
                <i class="fas fa-chart-line"></i> Reportes
            </a></li>
            <?php endif; ?>
            
            <!-- CONFIGURACIÓN -->
            <?php if (in_array('configuracion', $permisos_usuario)): ?>
            <li><a href="configuracion.php" class="<?php echo $pagina_actual == 'configuracion.php' ? 'active' : ''; ?>">
                <i class="fas fa-cogs"></i> Nosotros/Config
            </a></li>
            <?php endif; ?>
            
            <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Cerrar sesión</a></li>
        </ul>
    </nav>
</aside>