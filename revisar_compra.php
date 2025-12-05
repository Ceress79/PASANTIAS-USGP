<?php
session_start();
require_once "admin/db/conexion.php";
include('bases/header.php');

// Validar que llegue el ID de la orden
if (!isset($_GET['order'])) {
    header("Location: index.php");
    exit();
}

$order_uuid = $_GET['order'];

// Consultar la orden
$stmt = $pdo->prepare("SELECT * FROM orders WHERE order_uuid = ? AND user_id = ?");
$stmt->execute([$order_uuid, $_SESSION['user_id']]);
$orden = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$orden) {
    echo "<div style='padding:50px; text-align:center;'>Orden no válida.</div>";
    include('bases/footer.php');
    exit();
}

// Decodificar la dirección que guardamos en JSON
$direccion = json_decode($orden['direccion_envio'], true);

// Calcular envío (Simulado por ahora, puedes poner lógica real luego)
$costo_envio = 5.00; 
$subtotal = $orden['total']; 
$total_final = $subtotal + $costo_envio; // En tu DB guardaste el subtotal productos, aquí sumamos envío visualmente
?>

<link rel="stylesheet" href="style/css/revisar_compra.css">

<div class="revision-container">
    
    <h2>Revisa la forma de entrega</h2>

    <div class="card-revision direccion-box">
        <div class="info-texto">
            <p class="calle">
                <?php echo htmlspecialchars($direccion['calle_principal']); ?> - 
                <?php echo htmlspecialchars($direccion['canton']); ?>, 
                <?php echo htmlspecialchars($direccion['provincia']); ?>
            </p>
            <p class="tipo-domicilio"><?php echo htmlspecialchars($direccion['tipo']); ?></p>
        </div>
        
        <a href="checkout.php" class="link-modificar">Modificar domicilio o elegir otro</a>
    </div>

    <div class="resumen-seccion">
        <h2>Resumen de compra</h2>
        
        <div class="fila-resumen">
            <span>Producto(s)</span>
            <span>$<?php echo number_format($subtotal, 2); ?></span>
        </div>
        
        <div class="fila-resumen">
            <span>Envío</span>
            <span>$<?php echo number_format($costo_envio, 2); ?></span>
        </div>

        <div class="fila-resumen total">
            <span>Total</span>
            <span>$<?php echo number_format($total_final, 2); ?></span>
        </div>
    </div>

    <div class="acciones-finales">
        <form action="acciones_finalizar.php" method="POST">
            <input type="hidden" name="order_uuid" value="<?php echo $order_uuid; ?>">
            <input type="hidden" name="total_final" value="<?php echo $total_final; ?>">
            <button type="submit" class="btn-continuar">Continuar</button>
        </form>
    </div>

</div>

<?php include('bases/footer.php'); ?>