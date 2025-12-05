<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: Alogin.php");
    exit();
}
require_once "db/conexion.php";

// Obtener órdenes uniendo con la tabla de usuarios
$sql = "SELECT o.*, u.nombres, u.apellidos, u.email 
        FROM orders o 
        JOIN users u ON o.user_id = u.id 
        ORDER BY o.creado_en DESC";
$stmt = $pdo->query($sql);
$ordenes = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Compras - Admin</title>
    <link rel="stylesheet" href="css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="admin-container">
        <?php include 'sidebar.php'; ?>

        <main class="main-content">
            <header class="admin-header">
                <h1>Gestión de Compras</h1>
            </header>

            <section class="dashboard">
                <div class="table-container" style="background:white; padding:20px; border-radius:8px; border:1px solid #ddd;">
                    <h3 style="margin-bottom:20px; color:#333;">Historial de Pedidos (<?php echo count($ordenes); ?>)</h3>
                    
                    <table class="admin-table" style="width:100%; border-collapse:collapse;">
                        <thead>
                            <tr style="background:#f4f4f4; text-align:left; border-bottom:2px solid #ddd;">
                                <th style="padding:12px;"># Orden</th>
                                <th style="padding:12px;">Cliente</th>
                                <th style="padding:12px;">Fecha</th>
                                <th style="padding:12px;">Total</th>
                                <th style="padding:12px;">Estado</th>
                                <th style="padding:12px;">Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($ordenes as $ord): ?>
                                <tr style="border-bottom:1px solid #eee;">
                                    <td style="padding:12px; font-family:monospace; color:#a91e2c; font-weight:bold;">
                                        <?php echo substr($ord['order_uuid'], -8); ?>
                                    </td>
                                    <td style="padding:12px;">
                                        <?php echo htmlspecialchars($ord['nombres'] . ' ' . $ord['apellidos']); ?><br>
                                        <small style="color:#777;"><?php echo htmlspecialchars($ord['email']); ?></small>
                                    </td>
                                    <td style="padding:12px;">
                                        <?php echo date('d/m/Y H:i', strtotime($ord['creado_en'])); ?>
                                    </td>
                                    <td style="padding:12px; font-weight:bold;">
                                        $<?php echo number_format($ord['total'], 2); ?>
                                    </td>
                                    <td style="padding:12px;">
                                        <?php 
                                            $estado = $ord['estado'];
                                            $bg = '#eee'; $color = '#333';
                                            if($estado == 'PENDIENTE') { $bg = '#fff3cd'; $color = '#856404'; }
                                            if($estado == 'PAGADO') { $bg = '#cce5ff'; $color = '#004085'; }
                                            if($estado == 'ENVIADO') { $bg = '#d4edda'; $color = '#155724'; }
                                            if($estado == 'CANCELADO') { $bg = '#f8d7da'; $color = '#721c24'; }
                                        ?>
                                        <span style="background:<?php echo $bg; ?>; color:<?php echo $color; ?>; padding:4px 8px; border-radius:12px; font-size:11px; font-weight:bold;">
                                            <?php echo $estado; ?>
                                        </span>
                                    </td>
                                    <td style="padding:12px;">
                                        <a href="Adetalle_compra.php?id=<?php echo $ord['id']; ?>" class="btn" style="background:#007bff; color:white; padding:6px 12px; border-radius:4px; text-decoration:none; font-size:13px;">
                                            <i class="fas fa-eye"></i> Ver Detalle
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </section>
        </main>
    </div>
</body>
</html>