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
                <input type="hidden" id="talla_seleccionada" name="talla_id">
            </div>

            <div class="acciones-detalle">
                <button class="btn-anadir" id="btnAnadir" onclick="agregarAlCarrito()">
                    Añadir al carrito
                </button>
                <button class="btn-comprar-ahora">Comprar</button>
            </div>

        </div>
        
        <div id="mensaje-confirmacion" style="display:none; color: green; margin-top: 10px; text-align: center;">
            <i class="fas fa-check-circle"></i> Producto añadido al carrito
        </div>
    </div>

    <div class="detalle-info">
        <h5 class="categoria-label">Ropa y Accesorios</h5>
        
        <h1 class="titulo-producto"><?php echo htmlspecialchars($producto['nombre']); ?></h1>
        
        <div class="estrellas-falsas">$$$$$</div> 
        
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

    // --- LÓGICA TALLAS ---
    function seleccionarTalla(btn) {
        if (btn.classList.contains('agotado')) return;
        document.querySelectorAll('.talla-btn').forEach(b => b.classList.remove('seleccionado'));
        btn.classList.add('seleccionado');
        document.getElementById('talla_seleccionada').value = btn.getAttribute('data-id');
    }

    function agregarAlCarrito() {
        const tallaId = document.getElementById('talla_seleccionada').value;
        if (!tallaId) {
            alert("Por favor, selecciona una talla primero.");
            return;
        }
        document.getElementById('mensaje-confirmacion').style.display = 'block';
        setTimeout(() => {
            document.getElementById('mensaje-confirmacion').style.display = 'none';
        }, 3000);
    }
</script>

<?php include('bases/footer.php'); ?>