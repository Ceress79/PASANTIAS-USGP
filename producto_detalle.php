<?php
include('bases/header.php');
require_once "admin/db/conexion.php";

if (isset($_GET['slug'])) {
    $slug = $_GET['slug'];
    
    $stmt = $pdo->prepare("SELECT p.*, f.ruta as foto 
                           FROM productos p 
                           LEFT JOIN fotos f ON p.id = f.producto_id AND f.es_perfil = 1 
                           WHERE p.slug = ? AND p.disponible = 1 LIMIT 1");
    $stmt->execute([$slug]);
    $producto = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$producto) {
        echo "<h2 style='text-align:center; margin-top:100px;'>Producto no encontrado</h2>";
        include('bases/footer.php');
        exit();
    }

    $stmtVar = $pdo->prepare("SELECT * FROM variantes WHERE producto_id = ? ORDER BY FIELD(talla, 'S','M','L','XL')");
    $stmtVar->execute([$producto['id']]);
    $variantes = $stmtVar->fetchAll(PDO::FETCH_ASSOC);

} else {
    header("Location: ropa_accesorio.php");
    exit();
}
?>

<link rel="stylesheet" href="style/css/producto_detalle.css">

<div class="detalle-container">
    
    <div class="detalle-imagen">
        <div class="img-zoom-container">
            <img src="<?php echo !empty($producto['foto']) ? $producto['foto'] : 'style/img/placeholder.png'; ?>" 
                 alt="<?php echo htmlspecialchars($producto['nombre']); ?>" id="mainImage">
        </div>

        <div class="controles-bajo-imagen">
            
            <div class="selector-tallas">
                <?php foreach ($variantes as $var): ?>
                    <?php if($var['talla'] != 'ÚNICA'): // Solo mostramos botones si no es talla única ?>
                        <?php $clase_stock = ($var['stock'] > 0) ? 'talla-btn' : 'talla-btn agotado'; ?>
                        <button type="button" 
                                class="<?php echo $clase_stock; ?>" 
                                data-id="<?php echo $var['id']; ?>"
                                onclick="seleccionarTalla(this)">
                            <?php echo $var['talla']; ?>
                        </button>
                    <?php else: ?>
                        <input type="hidden" id="talla_unica_id" value="<?php echo $var['id']; ?>">
                    <?php endif; ?>
                <?php endforeach; ?>
                <input type="hidden" id="talla_seleccionada" name="talla_id">
            </div>

            <div class="acciones-detalle">
                <button class="btn-anadir" id="btnAnadir" onclick="agregarAlCarrito()" data-producto-id="<?php echo $producto['id']; ?>">
                    Añadir al carrito
                </button>
            </div>
        </div>
        
        <div id="mensaje-confirmacion" style="display:none; margin-top: 15px; text-align: center; font-weight: bold; padding: 10px; border-radius: 6px;"></div>
    </div>

    <div class="detalle-info">
        <h1 class="titulo-producto"><?php echo htmlspecialchars($producto['nombre']); ?></h1> 
        <p class="precio-detalle" style="font-size:24px; font-weight:bold;">$<?php echo number_format($producto['precio'], 2); ?></p>
        
        <div class="descripcion">
            <p><?php echo nl2br(htmlspecialchars($producto['descripcion'])); ?></p>
            <?php if(!empty($producto['material'])): ?>
                <br>
                <p><strong>Material:</strong> <?php echo htmlspecialchars($producto['material']); ?></p>
            <?php endif; ?>
            <?php if(!empty($producto['dimensiones'])): ?>
                <p><strong>Dimensiones:</strong> <?php echo htmlspecialchars($producto['dimensiones']); ?></p>
            <?php endif; ?>
        </div>

        <?php 
        $medidas = json_decode($producto['medidas_json'] ?? '{}', true);
        $tipo_guia = 'ninguna';
        $tallas_disp = array_column($variantes, 'talla');

        if (array_intersect(['38','42'], $tallas_disp)) $tipo_guia = 'calzado';
        elseif (array_intersect(['28','32'], $tallas_disp)) $tipo_guia = 'pantalones';
        elseif (array_intersect(['S','M'], $tallas_disp)) $tipo_guia = 'ropa_custom';
        ?>

        <?php if($tipo_guia != 'ninguna'): ?>
            <div class="guia-tallas-container">
                <div style="display:flex; align-items:center; gap:10px; margin-bottom:15px; border-bottom:1px solid #eee; padding-bottom:10px;">
                    <h4 style="margin:0; color:#333;">Guía de Tallas</h4>
                </div>
                
                <table class="tabla-tallas">
                    <thead>
                        <tr>
                            <th>Talla</th>
                            <?php if($tipo_guia == 'ropa_custom'): ?>
                                <th><?php echo $medidas['nombres'][0] ?? 'Ancho'; ?></th>
                                <th><?php echo $medidas['nombres'][1] ?? 'Largo'; ?></th>
                            <?php elseif($tipo_guia == 'pantalones'): ?>
                                <th>Cintura</th>
                                <th>Largo</th>
                            <?php elseif($tipo_guia == 'calzado'): ?>
                                <th>Largo de Pie (cm)</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($variantes as $v): $t = $v['talla']; if(isset($medidas[$t]) || $tipo_guia=='calzado'): ?>
                            <tr>
                                <td class="talla-bold"><?php echo $t; ?></td>
                                <?php if($tipo_guia == 'ropa_custom'): ?>
                                    <td><?php echo $medidas[$t]['m1'] ?? '-'; ?></td>
                                    <td><?php echo $medidas[$t]['m2'] ?? '-'; ?></td>
                                <?php elseif($tipo_guia == 'pantalones'): ?>
                                    <td><?php echo $medidas[$t]['cintura'] ?? '-'; ?></td>
                                    <td><?php echo $medidas[$t]['largo'] ?? '-'; ?></td>
                                <?php elseif($tipo_guia == 'calzado'): ?>
                                    <td>Consultar tabla general</td> 
                                <?php endif; ?>
                            </tr>
                        <?php endif; endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>

    </div>
</div>

<script>
    const container = document.querySelector('.img-zoom-container');
    const img = document.getElementById('mainImage');
    if (container && img) {
        container.addEventListener('mousemove', function(e) {
            const { left, top, width, height } = container.getBoundingClientRect();
            const x = ((e.clientX - left) / width) * 100;
            const y = ((e.clientY - top) / height) * 100;
            img.style.transformOrigin = `${x}% ${y}%`;
            img.style.transform = "scale(2)";
        });
        container.addEventListener('mouseleave', function() {
            img.style.transformOrigin = "center center";
            img.style.transform = "scale(1)";
        });
    }

    function seleccionarTalla(btn) {
        if (btn.classList.contains('agotado')) return;
        document.querySelectorAll('.talla-btn').forEach(b => b.classList.remove('seleccionado'));
        btn.classList.add('seleccionado');
        document.getElementById('talla_seleccionada').value = btn.getAttribute('data-id');
    }

    function agregarAlCarrito() {
        // Si hay talla única, la seleccionamos automáticamente
        const unica = document.getElementById('talla_unica_id');
        let tallaId = document.getElementById('talla_seleccionada').value;
        if(unica) tallaId = unica.value;

        const btnAnadir = document.getElementById('btnAnadir');
        const productoId = btnAnadir.getAttribute('data-producto-id');
        const mensajeConfirmacion = document.getElementById('mensaje-confirmacion');

        if (!tallaId && !unica) {
            alert("Por favor, selecciona una talla.");
            return;
        }

        const data = new URLSearchParams();
        data.append('accion', 'agregar_producto');
        data.append('producto_id', productoId);
        data.append('variante_id', tallaId);

        fetch('acciones_carrito.php', { method: 'POST', body: data })
        .then(r => r.json())
        .then(data => {
            if (data.exito) {
                mensajeConfirmacion.style.backgroundColor = '#d4edda';
                mensajeConfirmacion.style.color = '#155724';
                mensajeConfirmacion.innerHTML = '¡Añadido al carrito!';
                const cartCount = document.querySelector('.cart-count');
                if (cartCount) cartCount.textContent = data.articulos;
            } else {
                mensajeConfirmacion.innerHTML = '⚠️ ' + data.mensaje;
            }
            mensajeConfirmacion.style.display = 'block';
            setTimeout(() => { mensajeConfirmacion.style.display = 'none'; }, 3000);
        });
    }
</script>

<?php include('bases/footer.php'); ?>