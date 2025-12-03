<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: Alogin.php");
    exit();
}
require_once "db/conexion.php";

// Obtener banners
$stmt = $pdo->query("SELECT * FROM fotos WHERE tipo = 'BANNER' ORDER BY creado_en DESC");
$banners = $stmt->fetchAll();
$banner_count = count($banners);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Banners - USGP</title>
    <link rel="stylesheet" href="css/admin.css">
    
    <!-- ✅ ESTA LÍNEA ES LA QUE TE FALTABA PARA VER LOS ÍCONOS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
</head>
<body>
<div class="admin-container">
<?php include 'sidebar.php'; ?>

    <main class="main-content">
        <header class="admin-header">
            <h1>Gestión de Banners</h1>
            <p>Bienvenido, Administrador 👋</p>
        </header>

        <section class="dashboard">
            <div class="gestion-container">
                <div class="top-bar">
                    <h2>Banners de Inicio (<?php echo $banner_count; ?>/6)</h2>
                </div>

                <?php if(isset($_GET['error'])): ?>
                    <p class="msg-error"><?php echo htmlspecialchars($_GET['error']); ?></p>
                <?php endif; ?>
                <?php if(isset($_GET['exito'])): ?>
                    <p class="msg-success"><?php echo htmlspecialchars($_GET['exito']); ?></p>
                <?php endif; ?>

                <?php if ($banner_count < 6): ?>
                <form action="Aacciones_banners.php" method="POST" enctype="multipart/form-data" class="form-subida">
                    <input type="hidden" name="accion" value="subir">
                    <label>Seleccionar imagen (JPG, PNG, WebP - Máx 5MB):</label>
                    <input type="file" name="banner_img" accept="image/*" required>
                    <button type="submit" class="btn btn-primary">Subir Banner</button>
                </form>
                <?php else: ?>
                    <p class="msg-info">Has alcanzado el límite de 6 banners. Elimina uno para subir otro.</p>
                <?php endif; ?>

                <hr>

                <div class="table-container">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Vista Previa</th>
                                <th>Archivo</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($banners)): ?>
                                <tr><td colspan="5" class="no-data">No hay banners subidos.</td></tr>
                            <?php else: ?>
                                <?php foreach ($banners as $banner): ?>
                                    <tr>
                                        <td><?php echo $banner['id']; ?></td>
                                        <td>
                                            <?php $img_path = '../' . $banner['ruta']; ?>
                                            <?php if (file_exists($img_path)): ?>
                                                <img src="<?php echo $img_path; ?>" class="preview-img">
                                            <?php else: ?>
                                                <span class="no-image">Sin vista previa</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($banner['nombre_archivo']); ?></td>
                                        <td>
                                            <span class="estado <?php echo $banner['activo'] ? 'visible' : 'oculto'; ?>">
                                                <?php echo $banner['activo'] ? 'Visible' : 'Oculto'; ?>
                                            </span>
                                        </td>
                                        <td class="table-actions">
                                            <form action="Aacciones_banners.php" method="POST" style="display:inline;">
                                                <input type="hidden" name="accion" value="toggle">
                                                <input type="hidden" name="foto_id" value="<?php echo $banner['id']; ?>">
                                                <button type="submit" class="btn <?php echo $banner['activo'] ? 'btn-gris' : 'btn-verde'; ?>">
                                                    <?php echo $banner['activo'] ? 'Ocultar' : 'Mostrar'; ?>
                                                </button>
                                            </form>
                                            <form action="Aacciones_banners.php" method="POST" style="display:inline;" onsubmit="return confirm('¿Eliminar este banner?');">
                                                <input type="hidden" name="accion" value="eliminar">
                                                <input type="hidden" name="foto_id" value="<?php echo $banner['id']; ?>">
                                                <button type="submit" class="btn btn-rojo">Eliminar</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    </main>
</div>
</body>
</html>