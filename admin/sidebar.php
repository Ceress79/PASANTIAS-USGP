<aside class="sidebar">
    <div class="logo-container">
        <?php
        // Detectar ruta base para assets y links
        // Si no existe logout.php aquí, pero sí uno atrás, es que estamos en subcarpeta
        $ruta_base = '';
        if (!file_exists('logout.php') && file_exists('../logout.php')) {
            $ruta_base = '../';
        }
        ?>
        <a href="<?php echo $ruta_base; ?>Aindex.php"><img src="<?php echo $ruta_base; ?>../style/img/logo2.png" alt="Logo USGP" class="logo"></a>
        <h2>Panel Admin</h2>
    </div>

    <?php
    // --- LÓGICA DE PERMISOS Y AUTO-RECUPERACIÓN ---
    
    // 1. Cargar conexión si no existe
    if (!isset($pdo)) {
        if (file_exists("db/conexion.php")) require_once "db/conexion.php";
        elseif (file_exists("../db/conexion.php")) require_once "../db/conexion.php";
        // Fallback para niveles más profundos (como usuario_admin)
        elseif (file_exists("../../admin/db/conexion.php")) require_once "../../admin/db/conexion.php";
    }

    $id_actual = $_SESSION['admin_id'] ?? 0;
    $permisos_usuario = [];

    if (isset($pdo) && $id_actual > 0) {
        // 2. Obtener permisos actuales de la BD
        $stmt = $pdo->prepare("SELECT modulo FROM admin_permisos WHERE user_id = ?");
        $stmt->execute([$id_actual]);
        $permisos_usuario = $stmt->fetchAll(PDO::FETCH_COLUMN);

        // =========================================================================
        // 🛡️ AUTO-RECUPERACIÓN DE SUPER ADMIN (FAILSAFE)
        // =========================================================================
        // Si el usuario NO tiene permisos, verificamos si es el "Heredero al Trono" 
        // (El admin con el ID más bajo existente en la base de datos).
        if (empty($permisos_usuario)) {
            
            // Buscamos quién es el admin más antiguo/principal actual
            // En tu caso, buscará al ID 5 ya que el 1 fue borrado.
            $stmt_oldest = $pdo->query("SELECT id FROM users WHERE role = 'ADMIN' ORDER BY id ASC LIMIT 1");
            $oldest_admin_id = $stmt_oldest->fetchColumn();

            // Si YO soy ese admin (ej: ID 5), el sistema me debe autoproclamar Super Admin
            if ($id_actual == $oldest_admin_id) {
                
                $todos_los_modulos = ['banners', 'productos', 'contactos', 'compras', 'usuarios', 'reportes', 'configuracion'];

                // A) Darnos permisos visualmente para esta carga de página (para ver el menú YA)
                $permisos_usuario = $todos_los_modulos;

                // B) Reparar la base de datos automáticamente
                // Insertamos los permisos físicamente para que archivos como usuarios.php no te bloqueen
                $stmt_insert = $pdo->prepare("INSERT INTO admin_permisos (user_id, modulo) VALUES (?, ?)");
                
                foreach ($todos_los_modulos as $mod) {
                    try {
                        // Usamos try/catch silencioso por si acaso ya existe alguno suelto y evitar error SQL
                        $stmt_insert->execute([$id_actual, $mod]);
                    } catch (Exception $e) { continue; }
                }
            }
        }
    }
    
    $pagina_actual = basename($_SERVER['PHP_SELF']);
    ?>

    <nav>
        <ul>
            <!-- INICIO (Siempre visible para todos) -->
            <li><a href="<?php echo $ruta_base; ?>Aindex.php" class="<?php echo $pagina_actual == 'Aindex.php' ? 'active' : ''; ?>">
                <i class="fas fa-home"></i> Inicio
            </a></li>

            <!-- BANNERS -->
            <?php if (in_array('banners', $permisos_usuario)): ?>
            <li><a href="<?php echo $ruta_base; ?>Abanners.php" class="<?php echo $pagina_actual == 'Abanners.php' ? 'active' : ''; ?>">
                <i class="fas fa-images"></i> Banners Inicio
            </a></li>
            <?php endif; ?>

            <!-- PRODUCTOS -->
            <?php if (in_array('productos', $permisos_usuario)): ?>
            <li><a href="<?php echo $ruta_base; ?>Aproductos.php" class="<?php echo $pagina_actual == 'Aproductos.php' ? 'active' : ''; ?>">
                <i class="fas fa-box-open"></i> Productos
            </a></li>
            <?php endif; ?>

            <!-- CONTACTOS -->
            <?php if (in_array('contactos', $permisos_usuario)): ?>
            <li><a href="<?php echo $ruta_base; ?>Acontactos.php" class="<?php echo $pagina_actual == 'Acontactos.php' ? 'active' : ''; ?>">
                <i class="fas fa-address-book"></i> Contactos
            </a></li>
            <?php endif; ?>

            <!-- COMPRAS (MODIFICADO: Apunta a Acompras.php) -->
            <?php if (in_array('compras', $permisos_usuario)): ?>
            <li><a href="<?php echo $ruta_base; ?>Acompras.php" class="<?php echo ($pagina_actual == 'Acompras.php' || $pagina_actual == 'Adetalle_compra.php') ? 'active' : ''; ?>">
                <i class="fas fa-shopping-cart"></i> Compras / Pedidos
            </a></li>
            <?php endif; ?>

            <!-- USUARIOS -->
            <?php if (in_array('usuarios', $permisos_usuario)): ?>
            <li><a href="<?php echo $ruta_base; ?>usuario_admin/usuarios.php" class="<?php echo $pagina_actual == 'usuarios.php' ? 'active' : ''; ?>">
                <i class="fas fa-users-cog"></i> Gest. Usuarios
            </a></li>
            <?php endif; ?>

            <!-- REPORTES -->
            <?php if (in_array('reportes', $permisos_usuario)): ?>
            <li><a href="<?php echo $ruta_base; ?>reportes.php" class="<?php echo $pagina_actual == 'reportes.php' ? 'active' : ''; ?>">
                <i class="fas fa-chart-line"></i> Reportes
            </a></li>
            <?php endif; ?>
            
            <!-- CONFIGURACIÓN -->
            <?php if (in_array('configuracion', $permisos_usuario)): ?>
            <li><a href="<?php echo $ruta_base; ?>configuracion.php" class="<?php echo $pagina_actual == 'configuracion.php' ? 'active' : ''; ?>">
                <i class="fas fa-cogs"></i> Nosotros/Config
            </a></li>
            <?php endif; ?>
            
            <li><a href="<?php echo $ruta_base; ?>logout.php"><i class="fas fa-sign-out-alt"></i> Cerrar sesión</a></li>
        </ul>
    </nav>
</aside>