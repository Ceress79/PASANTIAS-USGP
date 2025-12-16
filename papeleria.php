<?php
// nuevo codigo funcional 21:31
include('bases/header.php');
require_once "admin/db/conexion.php"; 

$sql = "SELECT p.*, f.ruta as foto,
        (SELECT SUM(stock) FROM variantes WHERE producto_id = p.id) as suma_stock,
        (SELECT COUNT(*) FROM variantes WHERE producto_id = p.id) as tiene_variantes
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
    <?php foreach ($productos as $prod): 
        if ($prod['tiene_variantes'] > 0) {
            $stock_real = intval($prod['suma_stock']);
        } else {
            $stock_real = intval($prod['stock_total']);
        }
    ?>
        <div class="producto-card">
            
            <a href="producto_detalle.php?slug=<?php echo $prod['slug']; ?>" class="img-container">
                <img src="<?php echo !empty($prod['foto']) ? $prod['foto'] : 'style/img/placeholder.png'; ?>" 
                     alt="<?php echo htmlspecialchars($prod['nombre']); ?>">
                
                <?php if ($stock_real <= 0): ?>
                    <span class="badge-agotado">Agotado</span>
                <?php endif; ?>
            </a>
            
            <div class="info-producto">
                <h3><?php echo htmlspecialchars($prod['nombre']); ?></h3>
                <p class="precio">$<?php echo number_format($prod['precio'], 2); ?></p>
                
                <div class="acciones">
                    <?php if ($stock_real > 0): ?>
                        <button class="btn-carrito-temu" onclick="abrirModal('<?php echo $prod['id']; ?>')">
                            <i class="fas fa-cart-plus"></i> Añadir
                        </button>
                    <?php else: ?>
                        <button class="btn-carrito agotado" disabled>Agotado</button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<style>
    .btn-carrito-temu {
        width: 100%; padding: 10px; background-color: #333; color: white;
        border: none; border-radius: 25px; font-weight: bold; cursor: pointer;
        display: flex; justify-content: center; align-items: center; gap: 8px;
        transition: transform 0.2s, background 0.2s;
    }
    .btn-carrito-temu:hover { background-color: #000; transform: scale(1.05); }
    
    .btn-carrito.agotado { 
        width: 100%; padding: 10px; border-radius: 25px;
        background-color: #e0e0e0 !important; cursor: not-allowed !important; 
        color: #999 !important; border: 1px solid #d0d0d0 !important; pointer-events: none; 
    }
    .badge-agotado { 
        position: absolute; top: 10px; right: 10px; background-color: #222; color: #fff; 
        padding: 4px 8px; font-size: 0.75rem; font-weight: 600; border-radius: 4px; z-index: 2; 
    }
    .img-container { position: relative; }
</style>

<?php include('bases/modal_compra.php'); ?>
<?php include('bases/footer.php'); ?>