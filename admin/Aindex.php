<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: Alogin.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Administración - USGP</title>
    <link rel="stylesheet" href="css/admin.css">
    <script src="js/admin.js" defer></script>
</head>
<body>
    <div class="admin-container">
        <!-- Menú lateral -->
        <aside class="sidebar">
            <div class="logo-container">
                <img src="../style/img/logo.png" alt="Logo USGP" class="logo">
            </div>
            <nav>
                <ul>
                    <li><a href="#">📁 Subir fotos</a></li>
                    <li><a href="#">🛒 Compras</a></li>
                    <li><a href="#">👤 Usuarios</a></li>
                    <li><a href="#">📊 Reportes</a></li>
                    <li><a href="#">⚙️ Configuración</a></li>
                    <li><a href="logout.php">🚪 Cerrar sesión</a></li>
                </ul>
            </nav>
        </aside>

        <!-- Contenido principal -->
        <main class="main-content">
            <header class="admin-header">
                <h1>Panel de Control</h1>
                <p>Bienvenido, Administrador 👋</p>
            </header>

            <section class="dashboard">
                <div class="card">
                    <h3>Fotos subidas</h3>
                    <p>12 nuevas esta semana</p>
                </div>
                <div class="card">
                    <h3>Compras</h3>
                    <p>8 transacciones hoy</p>
                </div>
                <div class="card">
                    <h3>Usuarios</h3>
                    <p>125 registrados</p>
                </div>
            </section>
        </main>
    </div>
</body>
</html>

