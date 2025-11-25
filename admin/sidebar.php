<aside class="sidebar">
    <div class="logo-container">
        <a href="Aindex.php"><img src="../style/img/logo.png" alt="Logo USGP" class="logo"></a>
        <h2>Panel Admin</h2>
    </div>
    <nav>
        <ul>
            <?php $pagina_actual = basename($_SERVER['PHP_SELF']); ?>
            
            <li><a href="Abanners.php" class="<?php echo $pagina_actual == 'Abanners.php' ? 'active' : ''; ?>">Banners Inicio</a></li>
            <li><a href="Aproductos.php" class="<?php echo $pagina_actual == 'Aproductos.php' ? 'active' : ''; ?>">Productos Inicio</a></li>
            <li><a href="#">Compras</a></li>
            <li><a href="#">Usuarios</a></li>
            <li><a href="#">Reportes</a></li>
            
            <li><a href="configuracion.php" class="<?php echo $pagina_actual == 'configuracion.php' ? 'active' : ''; ?>">Configuración</a></li>
            
            <li><a href="logout.php">Cerrar sesión</a></li>
        </ul>
    </nav>
</aside>