 <?php
// Aquí podrías poner validaciones de sesión, ejemplo:
// session_start();
// if (!isset($_SESSION['admin'])) {
//     header("Location: ../login.php");
//     exit();
// }
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Administración</title>
    <link rel="stylesheet" href="css/admin.css">
    <script src="js/admin.js" defer></script>
</head>
<body>
    <div class="admin-container">
        <!-- Menú lateral -->
        <aside class="sidebar">
            <h2>Admin</h2>
            <nav>
                <ul>
                    <li><a href="#">📁 Subir fotos</a></li>
                    <li><a href="#">🛒 Compras</a></li>
                    <li><a href="#">👤 Usuarios</a></li>
                    <li><a href="#">📊 Reportes</a></li>
                    <li><a href="#">⚙️ Configuración</a></li>
                    <li><a href="#">🚪 Cerrar sesión</a></li>
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
