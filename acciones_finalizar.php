<?php
session_start();
require_once "admin/db/conexion.php";

// Validar sesión
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['order_uuid'])) {
    
    $uuid = $_POST['order_uuid'];
    $user_id = $_SESSION['user_id'];

    try {
        // AQUÍ ES DONDE BORRAMOS EL CARRITO (Al final del todo)
        // Como el usuario ya confirmó en el resumen, ahora sí limpiamos su carrito
        $pdo->prepare("DELETE FROM carrito_compras WHERE user_id = ?")->execute([$user_id]);

        // Redirigir al éxito
        header("Location: pedido_confirmado.php?order=" . $uuid);
        exit();

    } catch (Exception $e) {
        die("Error al finalizar: " . $e->getMessage());
    }

} else {
    header("Location: index.php");
    exit();
}