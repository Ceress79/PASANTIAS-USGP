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
    <title>Panel de Administración - USGP</title>
    <link rel="stylesheet" href="css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
</head>
<body>
<div class="admin-container">
    <?php include 'sidebar.php'; ?>

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
