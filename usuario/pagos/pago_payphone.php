<?php
session_start();
// Ajuste de ruta: Salimos de "pagos", salimos de "usuario", entramos a "admin/db"
require_once "../../admin/db/conexion.php";

// Validaciones
if (!isset($_SESSION['user_id'])) { header("Location: ../login.php"); exit(); }
if (empty($_SESSION['carrito'])) { header("Location: ../carrito.php"); exit(); }

// Calcular Totales
$subtotal = 0;
foreach ($_SESSION['carrito'] as $item) {
    $subtotal += $item['precio'] * $item['cantidad'];
}
$iva = $subtotal * 0.15;
$total = $subtotal + $iva;

// Obtener datos usuario
$stmt = $pdo->prepare("SELECT telefono, cedula FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pagar con Payphone</title>
    <!-- Ajuste de rutas CSS -->
    <link rel="stylesheet" href="../../style/css/main-style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
    <style>
        .pay-box { max-width: 500px; margin: 50px auto; background: #fff; padding: 30px; border-radius: 10px; box-shadow: 0 5px 15px rgba(0,0,0,0.1); text-align: center; }
        .payphone-logo { width: 180px; margin-bottom: 20px; }
        .total-display { font-size: 2.5rem; font-weight: bold; color: #333; margin: 10px 0; }
        .form-group { margin-bottom: 15px; text-align: left; }
        .form-group label { font-weight: bold; display: block; margin-bottom: 5px; }
        .form-group input { width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 5px; font-size: 16px; }
        .btn-pay { background: #ff6600; color: white; border: none; padding: 15px; width: 100%; border-radius: 5px; font-size: 1.2rem; cursor: pointer; transition: 0.3s; }
        .btn-pay:hover { background: #e65c00; }
        .info-text { font-size: 0.9em; color: #666; margin-top: 15px; }
    </style>
</head>
<body>

    <!-- Incluir menú (ajustar ruta si perfil.php está en usuario/) -->
    <?php include "../perfil.php"; ?>

    <div class="pay-box">
        <img src="https://payphone.app/wp-content/uploads/2021/09/Logo-Payphone-Boton-de-Pagos-01.png" alt="Payphone" class="payphone-logo">
        
        <h3>Confirmar Pago</h3>
        <div class="total-display">$<?php echo number_format($total, 2); ?></div>
        
        <form action="acciones_payphone.php" method="POST">
            <input type="hidden" name="accion" value="iniciar_pago">
            <input type="hidden" name="monto_total" value="<?php echo $total; ?>">
            <input type="hidden" name="monto_base" value="<?php echo $subtotal; ?>">
            <input type="hidden" name="monto_iva" value="<?php echo $iva; ?>">

            <div class="form-group">
                <label>Número de Celular (Payphone)</label>
                <input type="tel" name="telefono" placeholder="Ej: 0991234567" value="<?php echo $user['telefono'] ?? ''; ?>" required pattern="[0-9]{10}" title="10 dígitos">
            </div>

            <div class="form-group">
                <label>Cédula / RUC (Opcional)</label>
                <input type="text" name="cedula" placeholder="Ej: 130..." value="<?php echo $user['cedula'] ?? ''; ?>">
            </div>

            <button type="submit" class="btn-pay">
                <i class="fas fa-paper-plane"></i> Enviar Solicitud a mi Celular
            </button>
        </form>

        <p class="info-text">
            <i class="fas fa-mobile-alt"></i> Recibirás una notificación en tu App Payphone.
        </p>
    </div>

</body>
</html>