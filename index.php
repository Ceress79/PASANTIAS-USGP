<?php
require_once 'admin/db/conexion.php';
include('bases/header.php');

// 1. Obtener solo banners activos (máximo 6)
$stmt_banners = $pdo->query("SELECT ruta FROM fotos WHERE tipo = 'BANNER' AND activo = 1 ORDER BY creado_en DESC LIMIT 6");
$banners = $stmt_banners->fetchAll(PDO::FETCH_ASSOC);

// 2. OBTENER DATOS DE "NOSOTROS"
$stmt_config = $pdo->query("SELECT clave, valor FROM configuracion WHERE clave IN ('about_us_text', 'about_us_image')");
$config_data = $stmt_config->fetchAll(PDO::FETCH_KEY_PAIR);

$about_text = $config_data['about_us_text'] ?? 'Texto de "Nosotros" no configurado.';
$about_image = $config_data['about_us_image'] ?? 'uploads/site/default_about.png';
?>
<main>
    <section class="hero-section">
        <div class="slider-container">
            <?php if (!empty($banners)): ?>
                <?php foreach ($banners as $index => $banner): ?>
                    <div class="slide <?php echo $index === 0 ? 'active' : ''; ?>">
                        <img src="<?php echo htmlspecialchars($banner['ruta']); ?>" alt="Banner USGP <?php echo $index + 1; ?>">
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="slide active" style="display:flex; align-items:center; justify-content:center; background:#eee; height:70vh;">
                    <p style="font-size:1.2em; color:#444;">No hay banners disponibles.</p>
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
                <h1>Una colección que rinde homenaje a nuestras raíces, donde los patrones precolombinos del Ecuador se transforman en moda contemporánea.</h1>
            </div>
        </div>
    </section>

    <div class="page-content">
        <section class="products-section">
            <h2>Lo más vendido</h2>
            </section>

        <section class="about-section">
            <div class="about-content">
                <div class="about-text">
                    <h2>Nosotros</h2>
                    <p><?php echo htmlspecialchars($about_text); ?></p>
                </div>
                <div class="about-image">
                    <img src="<?php echo htmlspecialchars($about_image); ?>" alt="Sobre Nosotros USGP">
                </div>
            </div>
        </section>
        </div>
</main>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // --- LÓGICA DEL SLIDER ---
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

    // Cambio automático
    let slideInterval = setInterval(() => {
        currentSlide = (currentSlide + 1) % slides.length;
        showSlide(currentSlide);
    }, 5000); // 5 segundos

    // Cambio manual al hacer clic en un punto
    dots.forEach((dot, i) => {
        dot.addEventListener('click', () => {
            currentSlide = i;
            showSlide(currentSlide);
            // Reiniciar el intervalo para que no cambie inmediatamente
            clearInterval(slideInterval);
            slideInterval = setInterval(() => {
                currentSlide = (currentSlide + 1) % slides.length;
                showSlide(currentSlide);
            }, 5000);
        });
    });
});
</script>


<?php include('bases/footer.php'); ?>