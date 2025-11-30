<?php
// 1. Conexión a la base de datos
// Ajusta la ruta "admin/db/conexion.php" si tu carpeta admin está en otro lugar
require_once "admin/db/conexion.php"; 

// 2. Consulta para obtener los datos
$sql = "SELECT * FROM contactos ORDER BY id DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$contactos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 3. Incluir el header visual
include('bases/header.php');
?>

<link rel="stylesheet" href="style/css/ropa_accesorio.css">
<div class="contenedor-titulo" style="text-align: center; margin: 40px 0;">
    <h2>Nuestros Canales de Contacto</h2>
</div>

<div class="main-container" style="max-width: 1000px; margin: 0 auto; padding: 20px;">
    
    <div class="table-container" style="background:white; padding:20px; border:1px solid #ddd; border-radius:8px; box-shadow: 0 2px 5px rgba(0,0,0,0.05);">
        <table class="admin-table" style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr style="background: #f8f9fa; text-align: left; border-bottom:2px solid #eee;">
                    <th style="padding: 15px; width: 100px; color: #555; text-align:center;">Medio</th>
                    <th style="padding: 15px; color: #555;">Información</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($contactos)): ?>
                    <tr><td colspan="2" class="no-data" style="padding:40px; text-align:center; color:#999;">No hay información de contacto disponible por el momento.</td></tr>
                <?php else: ?>
                    <?php foreach ($contactos as $con): ?>
                        <tr style="border-bottom: 1px solid #eee;">
                            <td style="padding: 15px; text-align: center; color: #007bff;">
                                <?php if($con['tipo'] == 'email'): ?>
                                    <i class="fas fa-envelope fa-lg"></i>
                                <?php else: ?>
                                    <i class="fas fa-phone fa-lg"></i>
                                <?php endif; ?>
                            </td>

                           

                            <td style="padding: 15px; font-weight: bold; color: #333;">
                                <?php if($con['tipo'] == 'email'): ?>
                                    <a href="mailto:<?php echo htmlspecialchars($con['valor']); ?>" style="text-decoration:none; color:#333; hover:color:#007bff;">
                                        <?php echo htmlspecialchars($con['valor']); ?>
                                    </a>
                                <?php else: ?>
                                    <a href="tel:<?php echo htmlspecialchars($con['valor']); ?>" style="text-decoration:none; color:#333;">
                                        <?php echo htmlspecialchars($con['valor']); ?>
                                    </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

</div>

<?php include('bases/footer.php'); ?>