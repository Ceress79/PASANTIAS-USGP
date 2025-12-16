<?php
require_once 'bases/config_sesion.php'; 
include('bases/header.php');
require_once 'admin/db/conexion.php';

if (!isset($_SESSION['carrito'])) $_SESSION['carrito'] = [];

// Lógica para Definir Talla (Esta sí recarga para limpiar)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion_talla'])) {
    $clave_vieja = $_POST['clave_pendiente']; $new_vid = $_POST['nueva_variante_id'];
    if (isset($_SESSION['carrito'][$clave_vieja]) && !empty($new_vid)) {
        $item = $_SESSION['carrito'][$clave_vieja];
        unset($_SESSION['carrito'][$clave_vieja]);
        if(isset($_SESSION['user_id'])) $pdo->prepare("DELETE FROM carrito_compras WHERE user_id=? AND producto_id=? AND variante_id='0'")->execute([$_SESSION['user_id'], $item['id']]);
        
        if (isset($_SESSION['carrito'][$new_vid])) $_SESSION['carrito'][$new_vid]['cantidad'] += $item['cantidad'];
        else $_SESSION['carrito'][$new_vid] = ['id'=>$item['id'], 'variante_id'=>$new_vid, 'cantidad'=>$item['cantidad']];
        
        if(isset($_SESSION['user_id'])) {
            $n = $_SESSION['carrito'][$new_vid];
            $pdo->prepare("INSERT INTO carrito_compras (user_id, producto_id, variante_id, cantidad) VALUES (?,?,?,?) ON DUPLICATE KEY UPDATE cantidad=?")->execute([$_SESSION['user_id'], $n['id'], $n['variante_id'], $n['cantidad'], $n['cantidad']]);
        }
    }
    echo "<script>window.location.href='carrito.php';</script>"; exit;
}

// Helpers
function getInfo($pdo, $id) {
    $s=$pdo->prepare("SELECT p.nombre, p.precio, f.ruta as foto FROM productos p LEFT JOIN fotos f ON p.id=f.producto_id AND f.es_perfil=1 WHERE p.id=?");
    $s->execute([$id]); return $s->fetch(PDO::FETCH_ASSOC);
}
function getVar($pdo, $vid) {
    $s=$pdo->prepare("SELECT v.talla, p.nombre, p.precio, f.ruta as foto FROM variantes v JOIN productos p ON v.producto_id=p.id LEFT JOIN fotos f ON p.id=f.producto_id AND f.es_perfil=1 WHERE v.id=?");
    $s->execute([$vid]); return $s->fetch(PDO::FETCH_ASSOC);
}
function getListVar($pdo, $pid) {
    $s=$pdo->prepare("SELECT * FROM variantes WHERE producto_id=? AND stock>0 ORDER BY FIELD(talla, 'S','M','L','XL')");
    $s->execute([$pid]); return $s->fetchAll(PDO::FETCH_ASSOC);
}
?>

<link rel="stylesheet" href="style/css/carrito.css?v=<?php echo time(); ?>">

<div class="carrito-contenedor">
    <?php if (empty($_SESSION['carrito'])): ?>
        <div class="carrito-vacio"><p>Tu carrito está vacío.</p><a href="ropa_accesorio.php" class="btn-volver">Ir a comprar</a></div>
    <?php else: ?>
        <h2>🛒 Tu Carrito de Compras</h2>
        <table class="carrito-tabla">
            <thead><tr><th>Producto</th><th>Talla</th><th>Precio</th><th>Cantidad</th><th>Total</th><th></th></tr></thead>
            <tbody>
            <?php
            $total_gral = 0; $pendientes = false; $errores = false;
            foreach ($_SESSION['carrito'] as $k => $item):
                $es_pend = (strpos((string)$k, 'pendiente_') !== false);
                $prod = $es_pend ? getInfo($pdo, $item['id']) : getVar($pdo, $item['variante_id']);
                
                if (!$prod) { $errores = true; $prod = ['nombre'=>'⚠️ No disponible', 'precio'=>0, 'foto'=>'', 'talla'=>'Error']; }
                else if ($es_pend) { $pendientes = true; $vars = getListVar($pdo, $item['id']); }

                $sub = $prod['precio'] * $item['cantidad'];
                $total_gral += $sub;
            ?>
                <tr id="fila-<?php echo $k; ?>" class="<?php echo (!$prod['nombre']) ? 'fila-error' : ($es_pend ? 'fila-pendiente' : ''); ?>">
                    <td data-label="Producto">
                        <div class="producto-info">
                            <img src="<?php echo $prod['foto'] ?: 'style/img/placeholder.png'; ?>" class="carrito-img">
                            <span><?php echo htmlspecialchars($prod['nombre']); ?></span>
                        </div>
                    </td>
                    <td data-label="Talla">
                        <?php if ($es_pend): ?>
                            <form action="carrito.php" method="POST">
                                <input type="hidden" name="accion_talla" value="definir">
                                <input type="hidden" name="clave_pendiente" value="<?php echo $k; ?>">
                                <select name="nueva_variante_id" onchange="this.form.submit()" class="select-talla-alerta">
                                    <option value="">⚠️ Elegir...</option>
                                    <?php foreach($vars as $v): ?><option value="<?php echo $v['id']; ?>"><?php echo $v['talla']; ?></option><?php endforeach; ?>
                                </select>
                            </form>
                        <?php else: ?><strong class="talla-badge"><?php echo $prod['talla']??'Unique'; ?></strong><?php endif; ?>
                    </td>
                    <td data-label="Precio" class="precio-ui" data-val="<?php echo $prod['precio']; ?>">$<?php echo number_format($prod['precio'], 2); ?></td>
                    <td data-label="Cantidad">
                        <div class="cantidad-controles">
                            <!--  BOTONES SIN RECARGA -->
                            <button onclick="updateCant('<?php echo $k; ?>', 'restar')" class="btn-cantidad">−</button>
                            <span id="cant-<?php echo $k; ?>"><?php echo $item['cantidad']; ?></span>
                            <button onclick="updateCant('<?php echo $k; ?>', 'sumar')" class="btn-cantidad">+</button>
                        </div>
                    </td>
                    <td data-label="Total"><strong id="sub-<?php echo $k; ?>">$<?php echo number_format($sub, 2); ?></strong></td>
                    <td><button onclick="delItem('<?php echo $k; ?>')" class="btn-eliminar"><i class="fas fa-trash-alt"></i></button></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>

        <div class="carrito-totales">
            <p>Subtotal <span id="t-sub">$<?php echo number_format($total_gral, 2); ?></span></p>
            <p>IVA (15%) <span id="t-iva">$<?php echo number_format($total_gral * 0.15, 2); ?></span></p>
            <div class="total-final">Total: <span id="t-total">$<?php echo number_format($total_gral * 1.15, 2); ?></span></div>
            
            <?php if ($pendientes): ?><button class="btn-pagar disabled" disabled>Faltan Tallas</button>
            <?php elseif ($errores): ?><button class="btn-pagar disabled" disabled>Corrige errores</button>
            <?php else: ?>
                <?php if (isset($_SESSION['user_id'])): ?><a href="checkout.php" class="btn-pagar">Pagar</a>
                <?php else: ?><a href="usuario/login.php?redirect=carrito" class="btn-pagar" style="background:#333">Iniciar Sesión</a><?php endif; ?>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<script>
function updateCant(id, tipo) {
    const fd = new URLSearchParams(); fd.append('accion', 'cambiar_cantidad'); fd.append('id', id); fd.append('tipo', tipo);
    fetch('acciones_carrito.php', { method: 'POST', body: fd }).then(r=>r.json()).then(d => {
        if(d.exito) {
            document.querySelector('.cart-count').innerText = d.articulos;
            const sp = document.getElementById('cant-'+id);
            let n = parseInt(sp.innerText);
            if(tipo=='sumar') n++; else if(n>1) n--;
            sp.innerText = n;
            recalc();
        } else alert("⚠️ " + d.mensaje);
    });
}
function delItem(id) {
    if(!confirm('¿Borrar?')) return;
    const fd = new URLSearchParams(); fd.append('accion', 'eliminar'); fd.append('id', id);
    fetch('acciones_carrito.php', { method: 'POST', body: fd }).then(r=>r.json()).then(d => {
        if(d.exito) {
            document.querySelector('.cart-count').innerText = d.articulos;
            document.getElementById('fila-'+id).remove();
            recalc();
            if(document.querySelectorAll('tbody tr').length==0) location.reload();
        }
    });
}
function recalc() {
    let tot = 0;
    document.querySelectorAll('tbody tr').forEach(r => {
        const p = parseFloat(r.querySelector('.precio-ui').dataset.val);
        const c = parseInt(r.querySelector('span[id^="cant-"]').innerText);
        const s = p*c;
        tot += s;
        r.querySelector('strong[id^="sub-"]').innerText = '$'+s.toFixed(2);
    });
    document.getElementById('t-sub').innerText = '$'+tot.toFixed(2);
    document.getElementById('t-iva').innerText = '$'+(tot*0.15).toFixed(2);
    document.getElementById('t-total').innerText = '$'+(tot*1.15).toFixed(2);
}
</script>
<!-- Estilos mínimos necesarios -->
<style>.fila-pendiente{background:#fff5f5;border-left:4px solid #dc3545}.btn-pagar.disabled{background:#ccc;cursor:not-allowed}</style>
<?php include('bases/footer.php'); ?>