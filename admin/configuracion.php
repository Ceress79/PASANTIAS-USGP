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
    <style>
        .main-content { padding: 20px; }
        .gestion-container {
            background-color: #fff;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        }
        textarea {
            width: 100%;
            min-height: 120px;
            padding: 10px;
            font-size: 14px;
            border-radius: 5px;
            border: 1px solid #ccc;
            resize: vertical;
        }
        .text-preview {
            border: 1px solid #ddd;
            padding: 15px;
            background: #f9f9f9;
            border-radius: 5px;
            margin-top: 10px;
            white-space: pre-wrap;
        }
        .image-preview {
            margin-top: 10px;
            max-width: 400px;
            border-radius: 5px;
        }
        .btn {
            padding: 10px 16px;
            border-radius: 4px;
            border: none;
            cursor: pointer;
            margin-top: 10px;
            font-size: 14px;
        }
        .btn-azul { background: #004a99; color: white; }
        .btn-rojo { background: #c62828; color: white; }
    </style>
</head>
<body>
<div class="admin-container">
    <?php include 'sidebar.php'; ?>

    <main class="main-content">
        <header class="admin-header">
            <h1>Configuración General del Sitio</h1>
            <p>Ajustes de la sección "Nosotros".</p>
        </header>

        <section class="dashboard">
            <div class="gestion-container">
                <h2>Sección "Nosotros"</h2>

                <?php if(isset($_GET['error'])): ?>
                    <p style="color:red; background:#ffebee; padding:10px; border-radius:4px;"><?php echo htmlspecialchars(urldecode($_GET['error'])); ?></p>
                <?php endif; ?>
                <?php if(isset($_GET['exito'])): ?>
                    <p style="color:green; background:#e8f5e9; padding:10px; border-radius:4px;"><?php echo htmlspecialchars($_GET['exito']); ?></p>
                <?php endif; ?>

                <!-- Vista previa -->
                <h3>Vista previa del texto actual:</h3>
                <div class="text-preview"><?php echo nl2br(htmlspecialchars($about_text ?: 'Sin texto configurado.')); ?></div>

                <form action="Aacciones_config.php" method="POST" enctype="multipart/form-data" class="form-config">
                    <input type="hidden" name="accion" value="guardar_nosotros">

                    <label for="about_text">Editar texto:</label>
                    <textarea name="about_text" id="about_text"><?php echo htmlspecialchars($about_text); ?></textarea>

                    <label for="about_image">Imagen actual:</label><br>
                    <img src="../<?php echo htmlspecialchars($about_image); ?>?v=<?php echo time(); ?>" alt="Vista previa" class="image-preview">

                    <p>Cambiar imagen (opcional, máx. 3MB):</p>
                    <input type="file" name="about_image" accept="image/jpeg,image/png,image/webp">

                    <div style="margin-top:15px;">
                        <button type="submit" class="btn btn-azul">💾 Guardar Cambios</button>
                    </div>
                </form>

                <form action="Aacciones_config.php" method="POST" onsubmit="return confirm('¿Seguro que deseas eliminar el texto de “Nosotros”?');">
                    <input type="hidden" name="accion" value="eliminar_nosotros">
                    <button type="submit" class="btn btn-rojo">🗑️ Eliminar Texto</button>
                </form>
            </div>
        </section>
    </main>
</div>
</body>
</html>
