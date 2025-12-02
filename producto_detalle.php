<?php
include('bases/header.php');
require_once "admin/db/conexion.php";

// 1. Obtener el producto basado en el SLUG de la URL
if (isset($_GET['slug'])) {
    $slug = $_GET['slug'];
    
    // Consulta del producto + foto principal
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

    // 2. Obtener las tallas
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
        <!-- Lógica de Zoom -->
        <div class="img-zoom-container">
            <img src="<?php echo !empty($producto['foto']) ? $producto['foto'] : 'style/img/placeholder.png'; ?>" 
                 alt="<?php echo htmlspecialchars($producto['nombre']); ?>" id="mainImage">
        </div>

        <div class="controles-bajo-imagen">
            
            <div class="selector-tallas">
                
                <?php foreach ($variantes as $var): ?>
                    <?php $clase_stock = ($var['stock'] > 0) ? 'talla-btn' : 'talla-btn agotado'; ?>
                    <button type="button" 
                            class="<?php echo $clase_stock; ?>" 
                            data-id="<?php echo $var['id']; ?>"
                            onclick="seleccionarTalla(this)">
                        <?php echo $var['talla']; ?>
                    </button>
                <?php endforeach; ?>
                <!-- Input oculto para la talla -->
                <input type="hidden" id="talla_seleccionada" name="talla_id">
            </div>

            <div class="acciones-detalle">
                <!-- El ID se mantiene oculto en el atributo data, pero vital para que funcione -->
                <button class="btn-anadir" 
                        id="btnAnadir" 
                        onclick="agregarAlCarrito()"
                        data-producto-id="<?php echo $producto['id']; ?>">
                    Añadir al carrito
                </button>
                <button class="btn-comprar-ahora">Comprar</button>
            </div>

        </div>
        
        <!-- Contenedor para mensajes -->
        <div id="mensaje-confirmacion" style="display:none; margin-top: 15px; text-align: center; font-weight: bold; padding: 10px; border-radius: 6px;"></div>
    </div>

    <div class="detalle-info">
        <h5 class="categoria-label">Ropa y Accesorios</h5>
        
        <!-- Título limpio sin el ID visible -->
        <h1 class="titulo-producto"><?php echo htmlspecialchars($producto['nombre']); ?></h1> 
        
        <div class="descripcion">
            <p><?php echo nl2br(htmlspecialchars($producto['descripcion'])); ?></p>
            <?php if(!empty($producto['material'])): ?>
                <br>
                <p><strong>Material:</strong> <?php echo htmlspecialchars($producto['material']); ?></p>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
    // --- LÓGICA DE ZOOM ---
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

    // --- LÓGICA TALLAS ---
    function seleccionarTalla(btn) {
        if (btn.classList.contains('agotado')) return;
        document.querySelectorAll('.talla-btn').forEach(b => b.classList.remove('seleccionado'));
        btn.classList.add('seleccionado');
        document.getElementById('talla_seleccionada').value = btn.getAttribute('data-id');
        
        const msg = document.getElementById('mensaje-confirmacion');
        if(msg) msg.style.display = 'none';
    }

    // --- LÓGICA AGREGAR AL CARRITO (Versión Producción) ---
    function agregarAlCarrito() {
        const btnAnadir = document.getElementById('btnAnadir');
        const tallaId = document.getElementById('talla_seleccionada').value;
        const productoId = btnAnadir.getAttribute('data-producto-id');
        const mensajeConfirmacion = document.getElementById('mensaje-confirmacion');

        // Datos a enviar
        const data = new URLSearchParams();
        data.append('accion', 'agregar_producto');
        data.append('producto_id', productoId);
        data.append('variante_id', tallaId);

        fetch('acciones_carrito.php', {
            method: 'POST',
            body: data
        })
        .then(response => {
            // Procesamos la respuesta intentando limpiar cualquier "ruido" externo
            return response.text().then(text => {
                try {
                    return JSON.parse(text);
                } catch (e) {
                    console.error("Respuesta inesperada:", text); // Solo visible en consola del desarrollador
                    throw new Error("Hubo un problema al procesar la respuesta del servidor.");
                }
            });
        })
        .then(data => {
            if (data.exito) {
                // ÉXITO
                mensajeConfirmacion.style.backgroundColor = '#d4edda';
                mensajeConfirmacion.style.color = '#155724';
                
                if (tallaId) {
                    mensajeConfirmacion.innerHTML = '<i class="fas fa-check-circle"></i> ¡Producto añadido correctamente!';
                } else {
                    mensajeConfirmacion.innerHTML = '<i class="fas fa-check-circle"></i> Añadido. ⚠️ Recuerda elegir la talla en el carrito.';
                }
                
                // Actualizar contador del header
                const cartCount = document.querySelector('.cart-count');
                if (cartCount) {
                    cartCount.textContent = data.articulos;
                }
            } else {
                // Error de negocio (ej: sin stock)
                mensajeConfirmacion.style.backgroundColor = '#f8d7da';
                mensajeConfirmacion.style.color = '#721c24';
                mensajeConfirmacion.innerHTML = '⚠️ ' + data.mensaje;
            }
            
            mensajeConfirmacion.style.display = 'block';
            setTimeout(() => { mensajeConfirmacion.style.display = 'none'; }, 4000);
        })
        .catch(error => {
            console.error('Error:', error);
            // Error técnico (mensaje amigable para el usuario)
            mensajeConfirmacion.style.backgroundColor = '#f8d7da';
            mensajeConfirmacion.style.color = '#721c24';
            mensajeConfirmacion.innerHTML = '❌ Ocurrió un error al conectar con el carrito.';
            mensajeConfirmacion.style.display = 'block';
            setTimeout(() => { mensajeConfirmacion.style.display = 'none'; }, 4000);
        });
    }
</script>

<?php include('bases/footer.php'); ?>