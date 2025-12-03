<?php

include('bases/header.php');
require_once 'admin/db/conexion.php';

// Inicializar
if (!isset($_SESSION['carrito'])) $_SESSION['carrito'] = [];

// --- LÓGICA PARA ACTUALIZAR TALLA ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion_talla']) && $_POST['accion_talla'] === 'definir') {
    $clave_vieja = $_POST['clave_pendiente']; 
    
    
    $nueva_variante_id = $_POST['nueva_variante_id'];
    
    // Verificamos que no esté vacío
    if (isset($_SESSION['carrito'][$clave_vieja]) && !empty($nueva_variante_id)) {
        $item = $_SESSION['carrito'][$clave_vieja];
        
        // 1. Borrar el pendiente
        unset($_SESSION['carrito'][$clave_vieja]);
        
        // 2. Mover al nuevo ID (talla seleccionada)
        if (isset($_SESSION['carrito'][$nueva_variante_id])) {
            $_SESSION['carrito'][$nueva_variante_id]['cantidad'] += $item['cantidad'];
        } else {
            $_SESSION['carrito'][$nueva_variante_id] = [
                'id' => $item['id'],
                'variante_id' => $nueva_variante_id,
                'cantidad' => $item['cantidad']
            ];
        }
    }
    echo "<script>window.location.href='carrito.php';</script>";
    exit;
}

// --- FUNCIONES DE CONSULTA ---
function obtenerDetalleCompleto($pdo, $variante_id) {
    if (empty($variante_id)) return false; // Validar si está vacío
    $stmt = $pdo->prepare("SELECT v.id AS variante_id, v.talla, p.id AS producto_id, p.nombre, p.precio, f.ruta AS foto FROM variantes v JOIN productos p ON v.producto_id = p.id LEFT JOIN fotos f ON p.id = f.producto_id AND f.es_perfil = 1 WHERE v.id = ?");
    $stmt->execute([$variante_id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function obtenerInfoProductoBasico($pdo, $producto_id) {
    $stmt = $pdo->prepare("SELECT p.id, p.nombre, p.precio, f.ruta AS foto FROM productos p LEFT JOIN fotos f ON p.id = f.producto_id AND f.es_perfil = 1 WHERE p.id = ?");
    $stmt->execute([$producto_id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function obtenerVariantesProducto($pdo, $producto_id) {
    $stmt = $pdo->prepare("SELECT * FROM variantes WHERE producto_id = ? AND stock > 0 ORDER BY FIELD(talla, 'S','M','L','XL')");
    $stmt->execute([$producto_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Lógica de acciones
if (isset($_GET['accion']) && isset($_GET['id'])) {
    $id = $_GET['id'];
    if (isset($_SESSION['carrito'][$id])) {
        if ($_GET['accion'] == 'eliminar') unset($_SESSION['carrito'][$id]);
        if ($_GET['accion'] == 'sumar') $_SESSION['carrito'][$id]['cantidad']++;
        if ($_GET['accion'] == 'restar' && $_SESSION['carrito'][$id]['cantidad'] > 1) $_SESSION['carrito'][$id]['cantidad']--;
    }
    echo "<script>window.location.href='carrito.php';</script>";
    exit;
}
?>

<!--  Anti-Caché -->
<link rel="stylesheet" href="style/css/carrito.css?v=<?php echo time(); ?>">

<div class="carrito-contenedor">

    <?php if (empty($_SESSION['carrito'])): ?>
        <div class="carrito-vacio">
            <p>Tu carrito está vacío.</p>
            <a href="ropa_accesorio.php" class="btn-volver">Ir a comprar</a>
        </div>
    <?php else: ?>
        
        <h2>🛒 Tu Carrito de Compras</h2>
        
        <table class="carrito-tabla">
            <thead>
                <tr>
                    <th>Producto</th>
                    <th>Talla</th>
                    <th>Precio</th>
                    <th>Cantidad</th>
                    <th>Total</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>

            <?php
            $total_general = 0;
            $hay_pendientes = false; 
            $hay_errores = false;

            foreach ($_SESSION['carrito'] as $clave => $item):
                $clave_str = (string)$clave;
                $es_pendiente = (strpos($clave_str, 'pendiente_') !== false);
                $producto = false;
                $es_error = false;

                // Intentamos cargar el producto
                if ($es_pendiente) {
                    if (isset($item['id'])) {
                        $producto = obtenerInfoProductoBasico($pdo, $item['id']);
                        $variantes_disponibles = obtenerVariantesProducto($pdo, $item['id']);
                        $hay_pendientes = true;
                    }
                } else {
                    if (isset($item['variante_id'])) {
                        $producto = obtenerDetalleCompleto($pdo, $item['variante_id']);
                    }
                }

                if (!$producto) {
                    $es_error = true;
                    $hay_errores = true;
                    $producto = [
                        'nombre' => '⚠️ Error: No encontrado',
                        'precio' => 0,
                        'foto' => '',
                        'talla' => 'Error'
                    ];
                }

                $cantidad = $item['cantidad'];
                $subtotal = $producto['precio'] * $cantidad;
                $total_general += $subtotal;
            ?>
                <!-- Fila del producto -->
                <tr class="<?php echo $es_error ? 'fila-error' : ($es_pendiente ? 'fila-pendiente' : ''); ?>">
                    <td data-label="Producto">
                        <div class="producto-info">
                            <?php if(!$es_error): ?>
                                <img src="<?php echo $producto['foto'] ?: 'style/img/placeholder.png'; ?>" class="carrito-img">
                            <?php endif; ?>
                            <span><?php echo htmlspecialchars($producto['nombre']); ?></span>
                        </div>
                    </td>

                    <td data-label="Talla">
                        <?php if ($es_error): ?>
                            <span style="color:red;">Error</span>
                        <?php elseif ($es_pendiente): ?>
                            <form action="carrito.php" method="POST" class="form-talla">
                                <input type="hidden" name="accion_talla" value="definir">
                                <input type="hidden" name="clave_pendiente" value="<?php echo $clave; ?>">
                                <select name="nueva_variante_id" onchange="this.form.submit()" required class="select-talla-alerta">
                                    <option value="">⚠️ Elegir Talla...</option>
                                    <?php foreach($variantes_disponibles as $var): ?>
                                        <option value="<?php echo $var['id']; ?>"><?php echo $var['talla']; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </form>
                        <?php else: ?>
                            <strong class="talla-badge"><?php echo $producto['talla']; ?></strong>
                        <?php endif; ?>
                    </td>

                    <td data-label="Precio">$<?php echo number_format($producto['precio'], 2); ?></td>

                    <td data-label="Cantidad">
                        <div class="cantidad-controles">
                            <a href="carrito.php?accion=restar&id=<?php echo urlencode($clave); ?>" class="btn-cantidad">−</a>
                            <span><?php echo $cantidad; ?></span>
                            <a href="carrito.php?accion=sumar&id=<?php echo urlencode($clave); ?>" class="btn-cantidad">+</a>
                        </div>
                    </td>

                    <td data-label="Total"><strong>$<?php echo number_format($subtotal, 2); ?></strong></td>

                    <td>
                        <a href="carrito.php?accion=eliminar&id=<?php echo urlencode($clave); ?>" class="btn-eliminar" title="Eliminar">
                            <i class="fas fa-trash-alt"></i>
                        </a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>

        <div class="carrito-totales">
            <?php
                $iva = $total_general * 0.15;
                $total_pagar = $total_general + $iva;
            ?>
            <p>Subtotal <span>$<?php echo number_format($total_general, 2); ?></span></p>
            <p>IVA (15%) <span>$<?php echo number_format($iva, 2); ?></span></p>
            <div class="total-final">Total: <span>$<?php echo number_format($total_pagar, 2); ?></span></div>
            
            <?php if ($hay_pendientes): ?>
                <div class="alerta-bloqueo">⚠️ Selecciona talla para continuar.</div>
                <button class="btn-pagar disabled" disabled style="background-color: #ccc;">Faltan Tallas</button>
            <?php elseif ($hay_errores): ?>
                <div class="alerta-bloqueo" style="background-color: #ffe0e0; color: #d00;">
                    ❌ Hay productos con error. Elimínalos.
                </div>
                <button class="btn-pagar disabled" disabled style="background-color: #ccc;">Corrige errores</button>
            <?php else: ?>
                <!--  LÓGICA DE LOGIN OBLIGATORIO PARA PAGAR -->
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="checkout.php" class="btn-pagar">Proceder al Pago</a>
                <?php else: ?>
                    <div style="margin-top: 20px; text-align: center;">
                        <p style="margin-bottom: 10px; color: #666;">Inicia sesión para finalizar tu compra</p>
                        <a href="usuario/login.php?redirect=carrito" class="btn-pagar" style="background-color: #333;">
                            <i class="fas fa-user"></i> Iniciar Sesión para Pagar
                        </a>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<style>
    .fila-pendiente { background-color: #fff5f5 !important; border-left: 4px solid #dc3545; }
    .fila-error { background-color: #ffe6e6 !important; border-left: 4px solid #000; opacity: 0.8; }
    .select-talla-alerta { border: 2px solid #dc3545; color: #dc3545; font-weight: bold; padding: 5px; border-radius: 5px; }
    .alerta-bloqueo { color: #dc3545; margin-top: 15px; font-weight: bold; text-align: center; padding: 10px; background-color: #ffe6e6; border-radius: 6px; }
    .btn-pagar.disabled { background-color: #ccc; cursor: not-allowed; opacity: 0.7; }
</style>

<?php include('bases/footer.php'); ?>