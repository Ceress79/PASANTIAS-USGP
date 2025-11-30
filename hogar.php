<?php
include('bases/header.php');

require_once "admin/db/conexion.php"; 

// Consulta para traer productos de hogar con su foto principal
$sql = "SELECT p.*, f.ruta as foto 
        FROM productos p 
        LEFT JOIN fotos f ON p.id = f.producto_id AND f.es_perfil = 1 
        JOIN categorias c ON p.categoria_id = c.id 
        WHERE c.nombre = 'HOGAR' AND p.disponible = 1
        ORDER BY p.creado_en DESC";
$stmt = $pdo->query($sql);
$productos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<link rel="stylesheet" href="style/css/ropa_accesorio.css">

<div class="contenedor-titulo">
    <h2>Hogar</h2>
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
                    <button class="btn-carrito">Añadir al carrito</button>
                    <a href="producto_detalle.php?slug=<?php echo $prod['slug']; ?>" class="btn-comprar">Comprar</a>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<?php include('bases/footer.php'); ?>