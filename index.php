<?php
require_once 'admin/db/conexion.php';
include('bases/header.php');

// 1. Obtener Banners (Código original)
$stmt_banners = $pdo->query("SELECT ruta FROM fotos WHERE tipo = 'BANNER' AND activo = 1 ORDER BY creado_en DESC LIMIT 6");
$banners = $stmt_banners->fetchAll(PDO::FETCH_ASSOC);

// 2. Obtener Configuración (Código original)
$stmt_config = $pdo->query("SELECT clave, valor FROM configuracion WHERE clave IN ('about_us_text', 'about_us_image')");
$config_data = $stmt_config->fetchAll(PDO::FETCH_KEY_PAIR);
$about_text = $config_data['about_us_text'] ?? 'Texto de "Nosotros" no configurado.';
$about_image = $config_data['about_us_image'] ?? 'uploads/site/default_about.png';

// 3. FUNCIÓN PARA OBTENER PRODUCTOS POR CATEGORÍA (NUEVO)
function obtenerProductosPorCategoria($pdo, $nombreCategoria) {
    $sql = "SELECT p.*, f.ruta as foto,
            (SELECT SUM(stock) FROM variantes WHERE producto_id = p.id) as suma_stock,
            (SELECT COUNT(*) FROM variantes WHERE producto_id = p.id) as tiene_variantes
            FROM productos p 
            LEFT JOIN fotos f ON p.id = f.producto_id AND f.es_perfil = 1 
            JOIN categorias c ON p.categoria_id = c.id 
            WHERE c.nombre = ? AND p.disponible = 1
            ORDER BY p.creado_en DESC LIMIT 10"; // Limitamos a 10 para el carrusel
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$nombreCategoria]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Obtenemos los arrays de productos
$ropaProductos = obtenerProductosPorCategoria($pdo, 'ROPA');
$hogarProductos = obtenerProductosPorCategoria($pdo, 'HOGAR');
$papeleriaProductos = obtenerProductosPorCategoria($pdo, 'PAPELERIA');

?>

<link rel="stylesheet" href="style/css/carrusel.css">
<link rel="stylesheet" href="style/css/ropa_accesorio.css">

<main>
    <section class="hero-section">
        <div class="slider-container">
            <?php if (!empty($banners)): ?>
                <?php foreach ($banners as $index => $banner): ?>
                    <div class="slide <?php echo $index === 0 ? 'active' : ''; ?>">
                        <img src="<?php echo htmlspecialchars($banner['ruta']); ?>" alt="Banner USGP">
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="slide active" style="display:flex; align-items:center; justify-content:center; background:#eee; height:70vh;">
                    <p>No hay banners disponibles.</p>
                </div>
            <?php endif; ?>
        </div>

        <?php if (!empty($banners)): ?>
        <div class="slider-dots">
            <?php foreach ($banners as $index => $banner): ?>
                <span class="dot <?php echo $index === 0 ? 'active' : ''; ?>" data-index="<?php echo $index; ?>"></span>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <div class="hero-content">
            <div class="hero-text">
                <h1>Una colección que rinde homenaje a nuestras raíces.</h1>
            </div>
        </div>
    </section>

    <div class="page-content">
        
        <section class="products-section">
            
            <?php if(!empty($ropaProductos)): ?>
                <h2 class="categoria-titulo">Ropa y Accesorios</h2>
                <div class="carrusel-container">
                    <div class="carrusel-track">
                        <?php foreach($ropaProductos as $prod): include 'bases/card_producto_bucle.php'; endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <?php if(!empty($papeleriaProductos)): ?>
                <h2 class="categoria-titulo">Papelería</h2>
                <div class="carrusel-container">
                    <div class="carrusel-track">
                        <?php foreach($papeleriaProductos as $prod): include 'bases/card_producto_bucle.php'; endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <?php if(!empty($hogarProductos)): ?>
                <h2 class="categoria-titulo">Hogar</h2>
                <div class="carrusel-container">
                    <div class="carrusel-track">
                        <?php foreach($hogarProductos as $prod): include 'bases/card_producto_bucle.php'; endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

        </section>

        <section class="about-section">
            <div class="about-content">
                <div class="about-text">
                    <h2>Nosotros</h2>
                    <p><?php echo htmlspecialchars($about_text); ?></p>
                </div>
                <div class="about-image">
                    <img src="<?php echo htmlspecialchars($about_image); ?>" alt="Sobre Nosotros">
                </div>
            </div>
        </section>
    </div>
</main>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // --- LÓGICA DEL SLIDER PRINCIPAL (HERO) ---
    const slides = document.querySelectorAll('.slider-container .slide');
    const dots = document.querySelectorAll('.slider-dots .dot');
    if (slides.length <= 1) return;

    let currentSlide = 0;

    function showSlide(index) {
        slides.forEach((slide, i) => {
            slide.classList.toggle('active', i === index);
        });
        dots.forEach((dot, i) => {
            dot.classList.toggle('active', i === index);
        });
    }

    let slideInterval = setInterval(() => {
        currentSlide = (currentSlide + 1) % slides.length;
        showSlide(currentSlide);
    }, 5000);

    dots.forEach((dot, i) => {
        dot.addEventListener('click', () => {
            currentSlide = i;
            showSlide(currentSlide);
            clearInterval(slideInterval);
            slideInterval = setInterval(() => {
                currentSlide = (currentSlide + 1) % slides.length;
                showSlide(currentSlide);
            }, 5000);
        });
    });
});
</script>

<script src="style/js/carrusel.js"></script>

<?php include('bases/footer.php'); ?>