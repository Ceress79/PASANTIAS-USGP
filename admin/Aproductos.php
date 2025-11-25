<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: Alogin.php");
    exit();
}
require_once "db/conexion.php";

// --- LÓGICA DE EDICIÓN (DETECTAR SI HAY QUE EDITAR) ---
$producto_editar = null;
// Valores por defecto para el formulario (vacíos)
$valores = [
    'nombre' => '', 'precio' => '', 'descripcion' => '', 
    'material' => '', 'dimensiones' => '',
    'stock_s' => 0, 'stock_m' => 0, 'stock_l' => 0, 'stock_xl' => 0
];
$accion_form = 'crear_producto';
$titulo_form = 'Agregar Nuevo Producto';
$btn_texto = 'Guardar Producto';
$foto_required = 'required'; // Al crear, la foto es obligatoria

// Si recibimos un ID por URL, entramos en MODO EDICIÓN
if (isset($_GET['edit_id'])) {
    $id_editar = $_GET['edit_id'];
    
    // 1. Obtener datos del producto
    $stmt = $pdo->prepare("SELECT * FROM productos WHERE id = ?");
    $stmt->execute([$id_editar]);
    $producto_editar = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($producto_editar) {
        $accion_form = 'editar_producto';
        $titulo_form = 'Editar Producto: ' . htmlspecialchars($producto_editar['nombre']);
        $btn_texto = 'Actualizar Producto';
        $foto_required = ''; // Al editar, la foto es opcional

        // 2. Obtener Stocks
        $stmtVar = $pdo->prepare("SELECT talla, stock FROM variantes WHERE producto_id = ?");
        $stmtVar->execute([$id_editar]);
        $variantes_db = $stmtVar->fetchAll(PDO::FETCH_KEY_PAIR);

        // Llenar valores
        $valores['nombre'] = $producto_editar['nombre'];
        $valores['precio'] = $producto_editar['precio'];
        $valores['descripcion'] = $producto_editar['descripcion'];
        $valores['material'] = $producto_editar['material'];
        $valores['dimensiones'] = $producto_editar['dimensiones'];
        $valores['stock_s'] = $variantes_db['S'] ?? 0;
        $valores['stock_m'] = $variantes_db['M'] ?? 0;
        $valores['stock_l'] = $variantes_db['L'] ?? 0;
        $valores['stock_xl'] = $variantes_db['XL'] ?? 0;
    }
}

// --- OBTENER LISTA DE PRODUCTOS (PARA LA TABLA DE ABAJO) ---
$sql = "SELECT p.*, f.ruta as foto_ruta 
        FROM productos p 
        LEFT JOIN fotos f ON p.id = f.producto_id AND f.es_perfil = 1 
        ORDER BY p.creado_en DESC";
$stmt = $pdo->query($sql);
$productos = $stmt->fetchAll(PDO::FETCH_ASSOC);
$prod_count = count($productos);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Productos - USGP</title>
    <link rel="stylesheet" href="css/admin.css">
    <style>
        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px; }
        .stock-group { background: #f9f9f9; padding: 10px; border-radius: 5px; margin-bottom: 15px; border: 1px solid #eee; }
        .stock-inputs { display: flex; gap: 15px; }
        .stock-inputs input { width: 60px; padding: 5px; }
        .preview-img-prod { width: 50px; height: 50px; object-fit: cover; border-radius: 4px; border: 1px solid #ddd; }
        .form-subida input[type="text"], .form-subida input[type="number"], .form-subida textarea {
            width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px; margin-top: 5px;
        }
    </style>
</head>
<body>
<div class="admin-container">
    <?php include 'sidebar.php'; ?>

    <main class="main-content">
        <header class="admin-header">
            <h1>Gestión de Productos</h1>
            <p>Bienvenido, Administrador 👋</p>
        </header>

        <section class="dashboard">
            <div class="gestion-container">
                
                <?php if(isset($_GET['mensaje'])): ?>
                    <?php if($_GET['mensaje'] == 'creado'): ?><p class="msg-success">¡Producto creado exitosamente!</p><?php endif; ?>
                    <?php if($_GET['mensaje'] == 'actualizado'): ?><p class="msg-success">¡Producto actualizado correctamente!</p><?php endif; ?>
                    <?php if($_GET['mensaje'] == 'eliminado'): ?><p class="msg-success">Producto eliminado correctamente.</p><?php endif; ?>
                <?php endif; ?>
                <?php if(isset($_GET['error'])): ?>
                    <p class="msg-error"><?php echo htmlspecialchars($_GET['error']); ?></p>
                <?php endif; ?>

                <form action="acciones_productos.php" method="POST" enctype="multipart/form-data" class="form-subida" style="background: white; padding: 20px; border-radius: 8px; margin-bottom: 30px; border-left: 5px solid #a91e2c;">
                    
                    <input type="hidden" name="accion" value="<?php echo $accion_form; ?>">
                    
                    <?php if($producto_editar): ?>
                        <input type="hidden" name="id" value="<?php echo $producto_editar['id']; ?>">
                    <?php endif; ?>
                    
                    <div style="display:flex; justify-content:space-between; align-items:center;">
                        <h3><?php echo $titulo_form; ?></h3>
                        <?php if($producto_editar): ?>
                            <a href="Aproductos.php" style="font-size:12px; color:red; text-decoration:none;">[Cancelar Edición]</a>
                        <?php endif; ?>
                    </div>
                    <hr style="margin: 10px 0 20px 0; border: 0; border-top: 1px solid #eee;">

                    <div class="form-grid">
                        <div>
                            <label>Nombre del Producto:</label>
                            <input type="text" name="nombre" required value="<?php echo htmlspecialchars($valores['nombre']); ?>" placeholder="Ej: Camiseta Polo">
                        </div>
                        <div>
                            <label>Precio ($):</label>
                            <input type="number" step="0.01" name="precio" required value="<?php echo $valores['precio']; ?>" placeholder="0.00">
                        </div>
                    </div>

                    <div class="form-group" style="margin-bottom: 15px;">
                        <label>Descripción:</label>
                        <textarea name="descripcion" rows="3" placeholder="Detalles del producto..."><?php echo htmlspecialchars($valores['descripcion']); ?></textarea>
                    </div>

                    <div class="form-grid">
                        <div>
                            <label>Material:</label>
                            <input type="text" name="material" value="<?php echo htmlspecialchars($valores['material']); ?>" placeholder="Ej: 100% Algodón">
                        </div>
                        <div>
                            <label>Dimensiones / Detalles:</label>
                            <input type="text" name="dimensiones" value="<?php echo htmlspecialchars($valores['dimensiones']); ?>" placeholder="Ej: Corte recto, Talla única">
                        </div>
                    </div>

                    <div class="stock-group">
                        <label style="display:block; margin-bottom:10px; color:#a91e2c; font-weight:bold;">Inventario por Tallas:</label>
                        <div class="stock-inputs">
                            <div><label>S</label><br><input type="number" name="stock_s" value="<?php echo $valores['stock_s']; ?>" min="0"></div>
                            <div><label>M</label><br><input type="number" name="stock_m" value="<?php echo $valores['stock_m']; ?>" min="0"></div>
                            <div><label>L</label><br><input type="number" name="stock_l" value="<?php echo $valores['stock_l']; ?>" min="0"></div>
                            <div><label>XL</label><br><input type="number" name="stock_xl" value="<?php echo $valores['stock_xl']; ?>" min="0"></div>
                        </div>
                    </div>

                    <div style="margin-bottom: 20px;">
                        <label>Foto Principal:</label><br>
                        <?php if($producto_editar): ?>
                            <small style="color: #666;">(Deja vacío si no quieres cambiar la foto)</small><br>
                        <?php endif; ?>
                        <input type="file" name="foto" accept="image/*" <?php echo $foto_required; ?> style="margin-top: 5px;">
                    </div>

                    <button type="submit" class="btn btn-primary" style="background-color: #a91e2c; border:none; padding: 10px 20px; color: white; cursor: pointer; border-radius: 5px;">
                        <?php echo $btn_texto; ?>
                    </button>
                </form>

                <hr>

                <div class="table-container">
                    <h3>Lista de Productos</h3>
                    <table class="admin-table" style="width: 100%; border-collapse: collapse; margin-top: 15px;">
                        <thead>
                            <tr style="background: #f4f4f4; text-align: left;">
                                <th style="padding: 10px;">Img</th>
                                <th style="padding: 10px;">Nombre</th>
                                <th style="padding: 10px;">Precio</th>
                                <th style="padding: 10px;">Material</th>
                                <th style="padding: 10px;">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($productos)): ?>
                                <tr><td colspan="5" class="no-data" style="padding: 20px; text-align: center;">No hay productos registrados.</td></tr>
                            <?php else: ?>
                                <?php foreach ($productos as $prod): ?>
                                    <tr style="border-bottom: 1px solid #eee;">
                                        <td style="padding: 10px;">
                                            <?php $ruta_img = !empty($prod['foto_ruta']) ? '../' . $prod['foto_ruta'] : '../style/img/placeholder.png'; ?>
                                            <img src="<?php echo $ruta_img; ?>" class="preview-img-prod">
                                        </td>
                                        <td style="padding: 10px;">
                                            <strong><?php echo htmlspecialchars($prod['nombre']); ?></strong><br>
                                            <small style="color: #777;"><?php echo htmlspecialchars($prod['slug']); ?></small>
                                        </td>
                                        <td style="padding: 10px;">$<?php echo number_format($prod['precio'], 2); ?></td>
                                        <td style="padding: 10px;"><?php echo htmlspecialchars($prod['material'] ?? '-'); ?></td>
                                        
                                        <td class="table-actions" style="padding: 10px;">
                                            
                                            <a href="Aproductos.php?edit_id=<?php echo $prod['id']; ?>" class="btn" style="background: #007bff; color: white; padding: 5px 10px; border-radius: 4px; text-decoration: none; margin-right: 5px; font-size:14px;">
                                                Editar
                                            </a>

                                            <form action="acciones_productos.php" method="POST" style="display:inline;" onsubmit="return confirm('¿Estás seguro de eliminar este producto?');">
                                                <input type="hidden" name="accion" value="eliminar_producto">
                                                <input type="hidden" name="id" value="<?php echo $prod['id']; ?>">
                                                <button type="submit" class="btn btn-rojo" style="background: #dc3545; color: white; border: none; padding: 5px 10px; border-radius: 4px; cursor: pointer;">Eliminar</button>
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