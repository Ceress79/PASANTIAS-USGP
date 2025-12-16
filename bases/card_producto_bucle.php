<?php
// Cálculo de stock
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
        
        </a>
    
    <div class="info-producto">
        <h3><?php echo htmlspecialchars($prod['nombre']); ?></h3>
        <p class="precio">$<?php echo number_format($prod['precio'], 2); ?></p>
        
        <div class="acciones">
            <?php if ($stock_real > 0): ?>
                <a href="producto_detalle.php?slug=<?php echo $prod['slug']; ?>" class="btn-carrito">
                    <i class="fas fa-eye"></i> Ver Detalles
                </a>
            <?php else: ?>
                <button class="btn-carrito agotado" disabled>Agotado</button>
            <?php endif; ?>
        </div>
    </div>
</div>