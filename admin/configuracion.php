<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: Alogin.php");
    exit();
}
require_once "db/conexion.php";

// Obtener los datos actuales
$stmt = $pdo->query("SELECT clave, valor FROM configuracion WHERE clave IN ('about_us_text', 'about_us_image')");
$config_data = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

$about_text = $config_data['about_us_text'] ?? '';
$about_image = $config_data['about_us_image'] ?? 'uploads/site/default_about.png';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Configuración General - USGP</title>
    <link rel="stylesheet" href="css/admin.css">
    <link rel="stylesheet" href="css/configuracion.css">
    <!-- Iconos FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
    

</head>
<body>
<div class="admin-container">
    <?php include 'sidebar.php'; ?>

    <main class="main-content">
        <header class="admin-header">
            <h1>Configuración General</h1>
            <p>Gestiona el contenido de la sección "Nosotros" y otros ajustes.</p>
        </header>

        <!-- Mensajes de Feedback -->
        <?php if(isset($_GET['error'])): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i> 
                <?php echo htmlspecialchars(urldecode($_GET['error'])); ?>
            </div>
        <?php endif; ?>
        <?php if(isset($_GET['exito'])): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <?php echo htmlspecialchars($_GET['exito']); ?>
            </div>
        <?php endif; ?>

        <form action="Aacciones_config.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="accion" value="guardar_nosotros">
            
            <div class="config-grid">
                
                <!-- COLUMNA IZQUIERDA: FORMULARIO -->
                <div class="card">
                    <div class="card-header">
                        <h3><i class="fas fa-edit"></i> Editar Contenido</h3>
                    </div>

                    <div class="form-group">
                        <label for="about_text" class="form-label">Texto descriptivo "Nosotros"</label>
                        <textarea name="about_text" id="about_text" class="form-control" placeholder="Escribe aquí la historia o descripción de la empresa..."><?php echo htmlspecialchars($about_text); ?></textarea>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Imagen Destacada</label>
                        <div class="file-upload-wrapper">
                            <i class="fas fa-cloud-upload-alt upload-icon"></i>
                            <div class="upload-text">Arrastra una imagen aquí o haz clic para seleccionar</div>
                            <div style="font-size: 0.8em; color: #999; margin-top: 5px;">Formatos: JPG, PNG, WebP (Máx 3MB)</div>
                            <input type="file" name="about_image" accept="image/jpeg,image/png,image/webp" onchange="previewImage(this)">
                        </div>
                    </div>

                    <div class="btn-group">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Guardar Cambios
                        </button>
                        
                        <!-- El botón de eliminar es un formulario aparte, pero lo estilizamos para que encaje visualmente -->
                        <button type="submit" form="form-eliminar" class="btn btn-danger" onclick="return confirm('¿Seguro que deseas eliminar el texto?');">
                            <i class="fas fa-trash-alt"></i> Borrar Texto
                        </button>
                    </div>
                </div>

                <!-- COLUMNA DERECHA: VISTA PREVIA -->
                <div class="card">
                    <div class="card-header">
                        <div>
                            <h3><i class="fas fa-eye"></i> Vista Previa en el Sitio</h3>
                            <span class="header-subtitle">
                                <i class="fas fa-search-plus"></i> Dale click a la imagen para verla completa
                            </span>
                        </div>
                        <span style="font-size: 0.8em; color: #888; align-self: center;">Así lo verán tus clientes</span>
                    </div>

                    <div class="preview-container">
                        <!-- Añadimos evento onclick para abrir modal -->
                        <div class="preview-image-box" onclick="openFullImage()">
                            <img id="imgPreview" src="../<?php echo htmlspecialchars($about_image); ?>?v=<?php echo time(); ?>" alt="Vista previa">
                            <div class="click-hint"><i class="fas fa-expand"></i> Ampliar</div>
                        </div>
                        <div class="preview-content">
                            <div class="preview-title">Sobre Nosotros</div>
                            <div class="preview-text"><?php echo nl2br(htmlspecialchars($about_text ?: 'Aquí aparecerá el texto que escribas en el formulario...')); ?></div>
                        </div>
                    </div>
                </div>

            </div>
        </form>
        
        <!-- Formulario oculto auxiliar para borrar -->
        <form id="form-eliminar" action="Aacciones_config.php" method="POST">
            <input type="hidden" name="accion" value="eliminar_nosotros">
        </form>

    </main>
</div>

<!-- Modal para ver imagen completa -->
<div id="fullImageModal" class="image-modal" onclick="closeFullImage()">
    <span class="close-img-modal">&times;</span>
    <img class="image-modal-content" id="imgModalTarget">
</div>

<script>
    // Script para previsualizar la imagen antes de subirla
    function previewImage(input) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('imgPreview').src = e.target.result;
            }
            reader.readAsDataURL(input.files[0]);
        }
    }

    // Scripts para el Modal de Imagen
    function openFullImage() {
        var modal = document.getElementById("fullImageModal");
        var imgSource = document.getElementById("imgPreview").src;
        var modalImg = document.getElementById("imgModalTarget");
        
        modal.style.display = "block";
        modalImg.src = imgSource;
    }

    function closeFullImage() {
        document.getElementById("fullImageModal").style.display = "none";
    }

    // Cerrar con tecla Escape
    document.addEventListener('keydown', function(event) {
        if (event.key === "Escape") {
            closeFullImage();
        }
    });
</script>

</body>
</html>