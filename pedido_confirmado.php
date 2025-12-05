<?php
session_start();
require_once "admin/db/conexion.php";
include('bases/header.php');

// Validar que llegue el ID de la orden
if (!isset($_GET['order'])) {
    echo "<script>window.location.href='index.php';</script>";
    exit();
}

$order_uuid = $_GET['order'];

// Consultar datos de la orden para mostrar en pantalla
$stmt = $pdo->prepare("SELECT * FROM orders WHERE order_uuid = ?");
$stmt->execute([$order_uuid]);
$orden = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$orden) {
    echo "<div style='padding:50px; text-align:center;'>Orden no encontrada.</div>";
    include('bases/footer.php');
    exit();
}
?>

<link rel="stylesheet" href="style/css/pedido_confirmado.css">

<div class="confirmacion-container">
    <div class="card-exito">
        <div class="icono-exito">
            <i class="fas fa-check-circle"></i>
        </div>
        <h1>¡Gracias por tu compra!</h1>
        <p class="subtitulo">Tu pedido ha sido recibido correctamente.</p>

        <div class="detalles-orden">
            <p><strong>N° de Orden:</strong> <span class="uuid"><?php echo htmlspecialchars($orden['order_uuid']); ?></span></p>
            <p><strong>Total:</strong> $<?php echo number_format($orden['total'], 2); ?></p>
            <p><strong>Estado:</strong> <?php echo ucfirst(strtolower($orden['estado'])); ?></p>
            <p><strong>Fecha:</strong> <?php echo date('d/m/Y H:i', strtotime($orden['creado_en'])); ?></p>
        </div>

        <div class="botones-accion">
            <a href="index.php" class="btn-inicio">Volver al Inicio</a>
            </div>
    </div>
</div>

<?php include('bases/footer.php'); ?>