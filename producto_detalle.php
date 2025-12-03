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
                <!-- El ID se mantiene oculto en el atributo data -->
                <button class="btn-anadir" 
                        id="btnAnadir" 
                        onclick="agregarAlCarrito()"
                        data-producto-id="<?php echo $producto['id']; ?>">
                    Añadir al carrito
                </button>
                <button class="btn-comprar-ahora">Comprar</button>
            </div>

        </div>
        
        <div id="mensaje-confirmacion"></div>
    </div>

    <div class="detalle-info">
        <h5 class="categoria-label">Ropa y Accesorios</h5>
        
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
        
        // Ocultar mensaje al seleccionar
        const msg = document.getElementById('mensaje-confirmacion');
        if(msg) msg.style.display = 'none';
    }

    // --- LÓGICA AGREGAR AL CARRITO (Visual Limpio) ---
    function agregarAlCarrito() {
        const btnAnadir = document.getElementById('btnAnadir');
        const tallaId = document.getElementById('talla_seleccionada').value;
        const productoId = btnAnadir.getAttribute('data-producto-id');
        const mensajeConfirmacion = document.getElementById('mensaje-confirmacion');
        const textoOriginal = btnAnadir.innerText;

        // Feedback inmediato en el botón
        btnAnadir.innerText = "Añadiendo...";
        btnAnadir.disabled = true;
        mensajeConfirmacion.style.display = 'none'; // Limpiar mensaje previo

        // Datos a enviar
        const data = new URLSearchParams();
        data.append('accion', 'agregar_producto');
        data.append('producto_id', productoId);
        data.append('variante_id', tallaId);

        fetch('acciones_carrito.php', {
            method: 'POST',
            body: data
        })
        .then(response => response.text().then(text => {
            try { return JSON.parse(text); } catch (e) { throw new Error("Error en respuesta"); }
        }))
        .then(data => {
            if (data.exito) {
                // ÉXITO
                btnAnadir.innerText = "¡Añadido!";
                btnAnadir.style.backgroundColor = "#28a745"; // Verde botón
                btnAnadir.style.color = "white";
                
                // Mensaje limpio debajo
                if (tallaId) {
                    mensajeConfirmacion.innerHTML = '✔ Producto añadido correctamente';
                } else {
                    mensajeConfirmacion.innerHTML = '✔ Añadido (Recuerda elegir talla en el carrito)';
                }
                mensajeConfirmacion.className = 'msg-exito';
                
                // Actualizar contador
                const cartCount = document.querySelector('.cart-count');
                if (cartCount) cartCount.textContent = data.articulos;

            } else {
                // ERROR DE NEGOCIO (Stock, etc)
                mensajeConfirmacion.innerHTML = '⚠️ ' + data.mensaje;
                mensajeConfirmacion.className = 'msg-error';
                btnAnadir.innerText = textoOriginal;
            }
            
            // Mostrar el mensaje
            mensajeConfirmacion.style.display = 'block';
            
            // Ocultar mensaje después de 4 seg
            setTimeout(() => { mensajeConfirmacion.style.display = 'none'; }, 4000);
        })
        .catch(error => {
            console.error('Error:', error);
            mensajeConfirmacion.innerHTML = '❌ Error de conexión';
            mensajeConfirmacion.className = 'msg-error';
            mensajeConfirmacion.style.display = 'block';
            
            btnAnadir.style.backgroundColor = "#dc3545"; // Rojo botón
            btnAnadir.innerText = "Error";
        })
        .finally(() => {
            // Restaurar botón después de 2 segundos
            setTimeout(() => {
                btnAnadir.innerText = "Añadir al carrito"; // Texto original fijo (o textoOriginal)
                btnAnadir.disabled = false;
                btnAnadir.style.backgroundColor = ""; 
                btnAnadir.style.color = "";
            }, 2000);
        });
    }
</script>

<style>
    #mensaje-confirmacion {
        display: none;
        margin-top: 15px;
        font-size: 0.95rem;
        text-align: center;
        font-weight: 600;
        animation: fadeIn 0.3s ease-out;
        padding: 5px;
        /* Sin fondo, estilo limpio */
        background-color: transparent;
        border: none;
    }

    .msg-exito {
        color: #28a745; /* Verde texto */
    }

    .msg-error {
        color: #dc3545; /* Rojo texto */
    }

    @keyframes fadeIn { from { opacity: 0; transform: translateY(-5px); } to { opacity: 1; transform: translateY(0); } }
</style>

<?php include('bases/footer.php'); ?>