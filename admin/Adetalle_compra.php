<?php
session_start();
if (!isset($_SESSION['admin_id'])) { header("Location: Alogin.php"); exit(); }
require_once "db/conexion.php";

if (!isset($_GET['id'])) { header("Location: Acompras.php"); exit(); }
$order_id = $_GET['id'];

// 1. Datos Orden + Usuario
$stmt = $pdo->prepare("SELECT o.*, u.nombres, u.apellidos, u.email, u.cedula FROM orders o JOIN users u ON o.user_id = u.id WHERE o.id = ?");
$stmt->execute([$order_id]);
$orden = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$orden) { echo "Orden no encontrada"; exit(); }

// 2. Productos de la orden
$stmtItems = $pdo->prepare("SELECT * FROM order_items WHERE order_id = ?");
$stmtItems->execute([$order_id]);
$items = $stmtItems->fetchAll(PDO::FETCH_ASSOC);

// 3. DECODIFICAR DIRECCIÓN (JSON)
$dir = json_decode($orden['direccion_envio'], true);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Detalle Orden #<?php echo $orden['id']; ?></title>
    <link rel="stylesheet" href="css/admin.css">
    <style>
        .grid-detalle { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px; }
        .card { background: white; padding: 20px; border-radius: 8px; border: 1px solid #ddd; box-shadow: 0 2px 5px rgba(0,0,0,0.05); }
        .card h3 { margin-top: 0; border-bottom: 1px solid #eee; padding-bottom: 10px; color: #555; }
        .dato-fila { margin-bottom: 8px; font-size: 14px; }
        .dato-fila strong { color: #333; width: 120px; display: inline-block; }
    </style>
</head>
<body>
    <div class="admin-container">
        <?php include 'sidebar.php'; ?>

        <main class="main-content">
            <div style="margin-bottom:20px;">
                <a href="Acompras.php" style="color:#666; text-decoration:none;">&larr; Volver al listado</a>
            </div>

            <h2 style="color:#a91e2c;">Orden <span style="font-family:monospace;"><?php echo $orden['order_uuid']; ?></span></h2>
            
            <div class="grid-detalle">
                <div class="card">
                    <h3>Datos del Cliente</h3>
                    <div class="dato-fila"><strong>Nombre:</strong> <?php echo $orden['nombres'].' '.$orden['apellidos']; ?></div>
                    <div class="dato-fila"><strong>Email:</strong> <?php echo $orden['email']; ?></div>
                    <div class="dato-fila"><strong>Cédula:</strong> <?php echo $orden['cedula']; ?></div>
                    <div class="dato-fila"><strong>Fecha Compra:</strong> <?php echo $orden['creado_en']; ?></div>
                    
                    <h3 style="margin-top:20px;">Actualizar Estado</h3>
                    <form action="acciones_compras.php" method="POST" style="display:flex; gap:10px;">
                        <input type="hidden" name="order_id" value="<?php echo $orden['id']; ?>">
                        <select name="nuevo_estado" style="padding:8px; border-radius:4px; border:1px solid #ccc;">
                            <option value="PENDIENTE" <?php echo $orden['estado']=='PENDIENTE'?'selected':''; ?>>Pendiente</option>
                            <option value="PAGADO" <?php echo $orden['estado']=='PAGADO'?'selected':''; ?>>Pagado</option>
                            <option value="ENVIADO" <?php echo $orden['estado']=='ENVIADO'?'selected':''; ?>>Enviado</option>
                            <option value="COMPLETADO" <?php echo $orden['estado']=='COMPLETADO'?'selected':''; ?>>Completado</option>
                            <option value="CANCELADO" <?php echo $orden['estado']=='CANCELADO'?'selected':''; ?>>Cancelado</option>
                        </select>
                        <button type="submit" style="background:#007bff; color:white; border:none; padding:8px 15px; border-radius:4px; cursor:pointer;">Guardar</button>
                    </form>
                </div>

                <div class="card">
                    <h3>Dirección de Envío</h3>
                    <?php if($dir): ?>
                        <div class="dato-fila"><strong>Recibe:</strong> <?php echo $dir['recibe_nombre']; ?></div>
                        <div class="dato-fila"><strong>Teléfono:</strong> <?php echo $dir['recibe_telefono']; ?></div>
                        <hr style="margin:10px 0; border:0; border-top:1px dashed #eee;">
                        <div class="dato-fila"><strong>Provincia:</strong> <?php echo $dir['provincia']; ?></div>
                        <div class="dato-fila"><strong>Cantón:</strong> <?php echo $dir['canton']; ?></div>
                        <div class="dato-fila"><strong>Parroquia:</strong> <?php echo $dir['parroquia'] ?? '-'; ?></div>
                        <div class="dato-fila"><strong>Calle Principal:</strong> <?php echo $dir['calle_principal']; ?></div>
                        <div class="dato-fila"><strong>Secundaria:</strong> <?php echo $dir['calle_secundaria']; ?></div>
                        <div class="dato-fila"><strong>Referencia:</strong> <?php echo $dir['referencia']; ?></div>
                        <div class="dato-fila"><strong>Tipo:</strong> <?php echo $dir['tipo']; ?></div>
                    <?php else: ?>
                        <p style="color:red;">Error leyendo dirección.</p>
                    <?php endif; ?>
                </div>
            </div>

            <div class="card">
                <h3>Productos en la Orden</h3>
                <table class="admin-table" style="width:100%; border-collapse:collapse;">
                    <thead>
                        <tr style="background:#f9f9f9; text-align:left;">
                            <th style="padding:10px;">Producto</th>
                            <th style="padding:10px;">P. Unitario</th>
                            <th style="padding:10px;">Cantidad</th>
                            <th style="padding:10px;">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($items as $item): ?>
                            <tr style="border-bottom:1px solid #eee;">
                                <td style="padding:10px;"><?php echo htmlspecialchars($item['nombre_producto']); ?></td>
                                <td style="padding:10px;">$<?php echo number_format($item['precio_unitario'], 2); ?></td>
                                <td style="padding:10px;"><?php echo $item['cantidad']; ?></td>
                                <td style="padding:10px; font-weight:bold;">$<?php echo number_format($item['subtotal'], 2); ?></td>
                            </tr>
                        <?php endforeach; ?>
                        <tr>
                            <td colspan="3" style="text-align:right; padding:15px; font-weight:bold;">TOTAL:</td>
                            <td style="padding:15px; font-size:18px; color:#a91e2c; font-weight:bold;">$<?php echo number_format($orden['total'], 2); ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>

        </main>
    </div>
</body>
</html>