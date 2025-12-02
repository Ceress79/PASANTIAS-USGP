<?php
include('bases/header.php');

require_once "admin/db/conexion.php"; 

// Consulta para traer productos de papeleria
$sql = "SELECT p.*, f.ruta as foto 
        FROM productos p 
        LEFT JOIN fotos f ON p.id = f.producto_id AND f.es_perfil = 1 
        JOIN categorias c ON p.categoria_id = c.id 
        WHERE c.nombre = 'PAPELERIA' AND p.disponible = 1
        ORDER BY p.creado_en DESC";
$stmt = $pdo->query($sql);
$productos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<link rel="stylesheet" href="style/css/ropa_accesorio.css">

<div class="contenedor-titulo">
    <h2>Papelería</h2>
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
                    <!-- 🔥 BOTÓN CORREGIDO -->
                    <button class="btn-carrito" 
                            onclick="agregarDirecto('<?php echo $prod['id']; ?>', this)">
                        Añadir al carrito
                    </button>
                    
                    
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<!-- 🔥 SCRIPT COMPARTIDO -->
<script>
function agregarDirecto(productoId, btn) {
    const textoOriginal = btn.innerText;
    btn.innerText = "Añadiendo...";
    btn.disabled = true;
    
    const data = new URLSearchParams();
    data.append('accion', 'agregar_producto');
    data.append('producto_id', productoId);
    data.append('variante_id', ''); 

    fetch('acciones_carrito.php', { method: 'POST', body: data })
    .then(r => r.text().then(t => { try { return JSON.parse(t); } catch(e){ throw new Error("Error servidor"); } }))
    .then(data => {
        if (data.exito) {
            btn.innerText = "¡Añadido!";
            btn.style.background = "#28a745"; btn.style.color = "white";
            if(document.querySelector('.cart-count')) document.querySelector('.cart-count').textContent = data.articulos;
            mostrarNotificacion();
        } else {
            alert("⚠️ " + data.mensaje);
        }
    })
    .catch(e => { console.error(e); btn.style.background = "red"; btn.innerText = "Error"; })
    .finally(() => {
        setTimeout(() => {
            btn.innerText = textoOriginal; btn.disabled = false; btn.style.background = ""; btn.style.color = "";
        }, 2000);
    });
}

function mostrarNotificacion() {
    let notif = document.getElementById('toast-cart');
    if(!notif) {
        notif = document.createElement('div'); notif.id = 'toast-cart';
        notif.style.cssText = "position:fixed; bottom:20px; right:20px; background:#333; color:white; padding:12px 20px; border-radius:5px; z-index:1000; display:none; box-shadow:0 4px 6px rgba(0,0,0,0.1);";
        notif.innerHTML = '<i class="fas fa-check"></i> Producto añadido al carrito.';
        document.body.appendChild(notif);
    }
    notif.style.display = 'block';
    setTimeout(() => { notif.style.display = 'none'; }, 3000);
}
</script>

<?php include('bases/footer.php'); ?>