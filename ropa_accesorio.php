<?php
include('bases/header.php');

require_once "admin/db/conexion.php"; 

// Consulta para traer productos de ROPA con su foto principal
$sql = "SELECT p.*, f.ruta as foto 
        FROM productos p 
        LEFT JOIN fotos f ON p.id = f.producto_id AND f.es_perfil = 1 
        JOIN categorias c ON p.categoria_id = c.id 
        WHERE c.nombre = 'ROPA' AND p.disponible = 1
        ORDER BY p.creado_en DESC";
$stmt = $pdo->query($sql);
$productos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<link rel="stylesheet" href="style/css/ropa_accesorio.css">

<div class="contenedor-titulo">
    <h2>Ropa y Accesorios</h2>
</div>

<div class="productos-grid">
    <?php foreach ($productos as $prod): ?>
        <div class="producto-card">
            
            <a href="producto_detalle.php?slug=<?php echo $prod['slug']; ?>" class="img-container">
                <img src="<?php echo !empty($prod['foto']) ? $prod['foto'] : 'style/img/placeholder.png'; ?>" 
                     alt="<?php echo htmlspecialchars($prod['nombre']); ?>">
            </a>
            
            <div class="info-producto">
                <h3><?php echo htmlspecialchars($prod['nombre']); ?></h3>
                
                <p class="precio">$<?php echo number_format($prod['precio'], 2); ?></p>
                
                <div class="acciones">
                    <!-- 🔥 BOTÓN CORREGIDO: Ahora tiene evento onclick -->
                    <button class="btn-carrito" 
                            onclick="agregarDirecto('<?php echo $prod['id']; ?>', this)">
                        Añadir al carrito
                    </button>
                    
                    <a href="producto_detalle.php?slug=<?php echo $prod['slug']; ?>" class="btn-comprar">Comprar</a>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<!-- 🔥 SCRIPT COMPARTIDO PARA LAS LISTAS -->
<script>
function agregarDirecto(productoId, btn) {
    // 1. Efecto visual inmediato
    const textoOriginal = btn.innerText;
    btn.innerText = "Añadiendo...";
    btn.disabled = true;
    btn.style.opacity = "0.7";

    // 2. Preparar datos (sin talla, se guardará como pendiente)
    const data = new URLSearchParams();
    data.append('accion', 'agregar_producto');
    data.append('producto_id', productoId);
    data.append('variante_id', ''); // Vacío para que sea 'pendiente'

    // 3. Enviar al backend
    fetch('acciones_carrito.php', {
        method: 'POST',
        body: data
    })
    .then(response => response.text().then(text => {
        try { return JSON.parse(text); } 
        catch (e) { throw new Error("Error en respuesta del servidor"); }
    }))
    .then(data => {
        if (data.exito) {
            // ÉXITO: Botón verde y actualizar contador
            btn.innerText = "¡Añadido!";
            btn.style.backgroundColor = "#28a745"; // Verde éxito
            btn.style.color = "white";
            
            // Actualizar icono del carrito
            const cartCount = document.querySelector('.cart-count');
            if(cartCount) cartCount.textContent = data.articulos;

            // Mostrar notificación flotante
            mostrarNotificacion();
        } else {
            alert("⚠️ " + data.mensaje);
            btn.innerText = textoOriginal;
        }
    })
    .catch(error => {
        console.error(error);
        btn.innerText = "Error";
        btn.style.backgroundColor = "#dc3545"; // Rojo error
    })
    .finally(() => {
        // Restaurar botón después de 2 segundos
        setTimeout(() => {
            btn.innerText = textoOriginal;
            btn.disabled = false;
            btn.style.backgroundColor = ""; // Volver al original del CSS
            btn.style.color = "";
            btn.style.opacity = "1";
        }, 2000);
    });
}

// Pequeña notificación flotante
function mostrarNotificacion() {
    let notif = document.getElementById('toast-cart');
    if(!notif) {
        notif = document.createElement('div');
        notif.id = 'toast-cart';
        notif.style.cssText = "position:fixed; bottom:20px; right:20px; background:#333; color:white; padding:12px 20px; border-radius:5px; z-index:1000; display:none; box-shadow:0 4px 6px rgba(0,0,0,0.1); animation: slideIn 0.3s forwards;";
        notif.innerHTML = '<i class="fas fa-shopping-bag"></i> Producto añadido. Recuerda elegir la talla en el carrito.';
        document.body.appendChild(notif);
    }
    notif.style.display = 'block';
    setTimeout(() => { notif.style.display = 'none'; }, 3000);
}
</script>

<style>
@keyframes slideIn {
    from { transform: translateY(100%); opacity: 0; }
    to { transform: translateY(0); opacity: 1; }
}
</style>

<?php include('bases/footer.php'); ?>