<?php
session_start();
include('bases/header.php');
require_once 'admin/db/conexion.php';

// Inicializar carrito si no existe
if (!isset($_SESSION['carrito'])) {
    $_SESSION['carrito'] = [];
}

// Función para obtener datos actualizados del producto y su variante (TALLA)
function obtenerDetalleVariante($pdo, $variante_id) {
    $stmt = $pdo->prepare("
        SELECT 
            v.id AS variante_id,
            v.talla,
            p.id AS producto_id,
            p.nombre,
            p.precio,
            f.ruta AS foto
        FROM variantes v
        JOIN productos p ON v.producto_id = p.id
        LEFT JOIN fotos f ON p.id = f.producto_id AND f.es_perfil = 1
        WHERE v.id = ?
    ");
    $stmt->execute([$variante_id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// Manejo de acciones del carrito (sumar, restar, eliminar)
// NOTA: Ahora las acciones se basan en el ID de la VARIANTE (la clave de la sesión)
if (isset($_GET['accion']) && isset($_GET['id'])) {
    // El $id ahora es el ID de la VARIANTE (talla)
    $variante_id = intval($_GET['id']);

    if (isset($_SESSION['carrito'][$variante_id])) {
        switch ($_GET['accion']) {
            case 'sumar':
                $_SESSION['carrito'][$variante_id]['cantidad']++;
                break;

            case 'restar':
                if ($_SESSION['carrito'][$variante_id]['cantidad'] > 1) {
                    $_SESSION['carrito'][$variante_id]['cantidad']--;
                }
                break;

            case 'eliminar':
                unset($_SESSION['carrito'][$variante_id]);
                break;
        }
    }

    // Redireccionamos sin la acción para limpiar la URL
    header("Location: carrito.php");
    exit;
}

?>

<link rel="stylesheet" href="style/css/carrito.css">

<div class="carrito-contenedor">
    <h2>🛒 Mi Carrito</h2>

    <?php if (empty($_SESSION['carrito'])): ?>
        <p class="carrito-vacio">Tu carrito está vacío.</p>
        <a href="index.php" class="btn-volver">Volver a la tienda</a>

    <?php else: ?>
        <table class="carrito-tabla">
            <thead>
                <tr>
                    <th>Producto</th>
                    <th>Talla</th>
                    <th>Precio Unitario</th>
                    <th>Cantidad</th>
                    <th>Subtotal</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>

            <?php
            $total = 0;

            // El loop recorre las claves que ahora son los IDs de las VARIANTES (tallas)
            foreach ($_SESSION['carrito'] as $variante_id => $item):
                // Obtenemos todos los datos (producto + talla) a la vez
                $detalle = obtenerDetalleVariante($pdo, $variante_id);
                
                // Si el producto o variante ya no existe en la base de datos, lo ignoramos (o eliminamos del carrito)
                if (!$detalle) {
                    unset($_SESSION['carrito'][$variante_id]);
                    continue; 
                }

                $cantidad = $item['cantidad'];
                $subtotal = $detalle['precio'] * $cantidad;
                $total += $subtotal;
            ?>

                <tr>
                    <td class="producto-info">
                        <img src="<?php echo $detalle['foto'] ?: 'style/img/placeholder.png'; ?>" class="carrito-img">
                        <span><?php echo htmlspecialchars($detalle['nombre']); ?></span>
                    </td>
                    
                    <td><strong class="talla-badge"><?php echo htmlspecialchars($detalle['talla']); ?></strong></td>

                    <td>$<?php echo number_format($detalle['precio'], 2); ?></td>

                    <td class="cantidad-controles">
                        <!-- Usamos $variante_id como ID para la acción -->
                        <a href="carrito.php?accion=restar&id=<?php echo $variante_id; ?>" class="btn-cantidad">−</a>
                        <span><?php echo $cantidad; ?></span>
                        <a href="carrito.php?accion=sumar&id=<?php echo $variante_id; ?>" class="btn-cantidad">+</a>
                    </td>

                    <td>$<?php echo number_format($subtotal, 2); ?></td>

                    <td>
                        <!-- Usamos $variante_id como ID para la acción -->
                        <a href="carrito.php?accion=eliminar&id=<?php echo $variante_id; ?>" class="btn-eliminar">X</a>
                    </td>
                </tr>

            <?php endforeach; ?>

            </tbody>
        </table>

        <!-- Totales -->
        <div class="carrito-totales">
            <?php
                $iva = $total * 0.12;
                $granTotal = $total + $iva;
            ?>
            <p>Subtotal: <strong>$<?php echo number_format($total, 2); ?></strong></p>
            <p>IVA (12%): <strong>$<?php echo number_format($iva, 2); ?></strong></p>
            <p>Total a pagar: <strong class="total-final">$<?php echo number_format($granTotal, 2); ?></strong></p>

            <a href="checkout.php" class="btn-pagar">Finalizar Compra</a>
        </div>

    <?php endif; ?>
</div>

<!-- Estilos recomendados para la talla -->
<style>
    .talla-badge {
        display: inline-block;
        padding: 5px 8px;
        background-color: #f0f0f0;
        border: 1px solid #ccc;
        border-radius: 4px;
        font-size: 0.9em;
        font-weight: 600;
        min-width: 30px;
        text-align: center;
    }
</style>

<?php include('bases/footer.php'); ?>