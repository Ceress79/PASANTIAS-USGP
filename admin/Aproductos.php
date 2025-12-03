<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: Alogin.php");
    exit();
}
require_once "db/conexion.php";

// 1. DETERMINAR CATEGORÍA ACTUAL
$cat_actual = isset($_GET['cat']) ? (int)$_GET['cat'] : 1;
if ($cat_actual < 1 || $cat_actual > 4) $cat_actual = 1; 

$nombres_cat = [1 => 'Ropa y Accesorios', 2 => 'Papelería', 3 => 'Hogar', 4 => 'Otros'];
$nombre_cat_actual = $nombres_cat[$cat_actual];

// --- LÓGICA DE EDICIÓN ---
$producto_editar = null;
$mostrar_formulario = false; 

$valores = [
    'nombre' => '', 'precio' => '', 'descripcion' => '', 'material' => '', 'dimensiones' => '',
    'stock_unico' => 0, 'tipo_stock' => '', 'medidas' => [] // Array para medidas
];
$accion_form = 'crear_producto';
$titulo_form = 'Añadir Nuevo Producto';
$btn_texto = 'Guardar Producto';
$foto_required = 'required';
$variantes_db = [];

if (isset($_GET['edit_id'])) {
    $mostrar_formulario = true;
    $id_editar = $_GET['edit_id'];
    $stmt = $pdo->prepare("SELECT * FROM productos WHERE id = ?");
    $stmt->execute([$id_editar]);
    $producto_editar = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($producto_editar) {
        if ($producto_editar['categoria_id'] != $cat_actual) {
            header("Location: Aproductos.php?cat=" . $producto_editar['categoria_id'] . "&edit_id=" . $id_editar);
            exit();
        }

        $accion_form = 'editar_producto';
        $titulo_form = 'Editar Producto';
        $btn_texto = 'Actualizar Producto';
        $foto_required = ''; 

        $stmtVar = $pdo->prepare("SELECT talla, stock FROM variantes WHERE producto_id = ?");
        $stmtVar->execute([$id_editar]);
        $variantes_db = $stmtVar->fetchAll(PDO::FETCH_KEY_PAIR);

        $valores = array_merge($valores, $producto_editar);
        $valores['stock_unico'] = $variantes_db['ÚNICA'] ?? 0;
        
        // --- DECODIFICAR JSON DE MEDIDAS ---
        $valores['medidas'] = json_decode($producto_editar['medidas_json'] ?? '{}', true);

        // Deducir tipo de stock
        if(isset($variantes_db['S'])) $valores['tipo_stock'] = 'ropa';
        elseif(isset($variantes_db['38'])) $valores['tipo_stock'] = 'calzado';
        elseif(isset($variantes_db['28'])) $valores['tipo_stock'] = 'pantalones';
        else $valores['tipo_stock'] = 'unico';
    }
}

// --- CONSULTA LISTADO ---
$sql = "SELECT p.*, f.ruta as foto_ruta 
        FROM productos p 
        LEFT JOIN fotos f ON p.id = f.producto_id AND f.es_perfil = 1 
        WHERE p.categoria_id = ? 
        ORDER BY p.creado_en DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute([$cat_actual]);
$productos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Productos</title>
    <link rel="stylesheet" href="css/admin.css">
    <link rel="stylesheet" href="css/Aproductos.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* Estilos extra para la tabla de medidas dentro del formulario */
        .tabla-medidas { width: 100%; border-collapse: collapse; margin-top: 10px; background: white; }
        .tabla-medidas th { background: #f0f0f0; padding: 8px; font-size: 13px; text-align: center; border: 1px solid #ddd; }
        .tabla-medidas td { padding: 5px; border: 1px solid #ddd; text-align: center; }
        .tabla-medidas input { width: 100%; border: none; text-align: center; padding: 5px; margin: 0; outline: none; background: transparent; }
        .tabla-medidas input:focus { background: #eef; }
        .titulo-seccion { color: #555; font-size: 14px; font-weight: bold; margin-bottom: 5px; display: block; }
        .info-text { font-size: 12px; color: #666; margin-bottom: 10px; display: block; }
    </style>
</head>
<body>
    <div class="admin-container">
        <?php include 'sidebar.php'; ?>

        <main class="main-content">
            
            <div class="top-nav">
                <a href="Aproductos.php?cat=1" class="nav-btn <?php echo ($cat_actual == 1) ? 'active' : ''; ?>">
                    <i class="fas fa-tshirt"></i> Trabajar en Ropa
                </a>
                <a href="Aproductos.php?cat=2" class="nav-btn <?php echo ($cat_actual == 2) ? 'active' : ''; ?>">
                    <i class="fas fa-pencil-ruler"></i> Trabajar en Papelería
                </a>
                <a href="Aproductos.php?cat=3" class="nav-btn <?php echo ($cat_actual == 3) ? 'active' : ''; ?>">
                    <i class="fas fa-couch"></i> Trabajar en Hogar
                </a>
            </div>

            <div class="header-actions">
                <h2 style="margin:0; color:#333;">Listado: <?php echo $nombre_cat_actual; ?></h2>
                <?php if (!$mostrar_formulario): ?>
                    <button class="btn-add-new" onclick="toggleForm()">
                        <i class="fas fa-plus"></i> Añadir Nuevo Producto
                    </button>
                <?php endif; ?>
            </div>

            <?php if(isset($_GET['mensaje'])): ?>
                <p class="msg-success" style="padding:15px; background:#d4edda; color:#155724; border-radius:5px; margin-bottom:20px;">
                    <i class="fas fa-check-circle"></i> Acción realizada con éxito.
                </p>
            <?php endif; ?>

            <div id="formContainer" style="display: <?php echo $mostrar_formulario ? 'block' : 'none'; ?>;">
                <form action="acciones_productos.php" method="POST" enctype="multipart/form-data" class="form-box">
                    <input type="hidden" name="accion" value="<?php echo $accion_form; ?>">
                    <?php if ($producto_editar): ?><input type="hidden" name="id" value="<?php echo $producto_editar['id']; ?>"><?php endif; ?>
                    <input type="hidden" name="categoria_id" value="<?php echo $cat_actual; ?>">

                    <div style="display:flex; justify-content:space-between; border-bottom:1px solid #eee; padding-bottom:15px; margin-bottom:20px;">
                        <h3 style="margin:0; color:#B51E35;;"><?php echo $titulo_form; ?></h3>
                        <button type="button" onclick="cancelarForm()" style="background:none; border:none; color:#dc3545; cursor:pointer; font-weight:bold;">Cancelar</button>
                    </div>

                    <?php if ($cat_actual == 1): ?>
                        <div style="margin-bottom: 20px; background: #fff5f5; padding: 20px; border-radius: 8px; border: 1px solid #ffdce0;">
                            <label style="color:#a91e2c; font-weight:bold;">Tipo de Variantes / Tallas:</label>
                            <select name="tipo_stock" id="tipoStockSelect" onchange="mostrarBloqueStock()" style="border-color:#a91e2c;">
                                <option value="ropa" <?php echo ($valores['tipo_stock'] == 'ropa') ? 'selected' : ''; ?>>Ropa Estándar (S, M, L, XL)</option>
                                <option value="pantalones" <?php echo ($valores['tipo_stock'] == 'pantalones') ? 'selected' : ''; ?>>Pantalones (28 - 36)</option>
                                <option value="calzado" <?php echo ($valores['tipo_stock'] == 'calzado') ? 'selected' : ''; ?>>Calzado (38 - 44)</option>
                                <option value="unico" <?php echo ($valores['tipo_stock'] == 'unico') ? 'selected' : ''; ?>>Accesorio Talla Única</option>
                            </select>
                        </div>
                    <?php else: ?>
                        <input type="hidden" name="tipo_stock" id="tipoStockSelect" value="unico">
                    <?php endif; ?>

                    <div class="form-grid">
                        <div><label>Nombre:</label><input type="text" name="nombre" required value="<?php echo htmlspecialchars($valores['nombre']); ?>"></div>
                        <div><label>Precio ($):</label><input type="number" step="0.01" name="precio" required value="<?php echo $valores['precio']; ?>"></div>
                    </div>

                    <label>Descripción:</label>
                    <textarea name="descripcion" rows="3"><?php echo htmlspecialchars($valores['descripcion']); ?></textarea>

                    <div class="form-grid">
                        <div><label>Material:</label><input type="text" name="material" value="<?php echo htmlspecialchars($valores['material']); ?>"></div>
                        <div><label>Dimensiones / Detalles:</label><input type="text" name="dimensiones" value="<?php echo htmlspecialchars($valores['dimensiones']); ?>"></div>
                    </div>

                    <div id="containerStocks">
                        
                        <div id="stock_unico" class="stock-group <?php echo ($cat_actual == 1 && $valores['tipo_stock'] != 'unico') ? 'hidden' : ''; ?>">
                            <label style="color:#B51E35; font-weight:bold;">Stock Total (Unidades):</label>
                            <input type="number" name="stock_unico_cant" value="<?php echo $valores['stock_unico']; ?>" min="0" style="width:120px;">
                        </div>

                        <?php if ($cat_actual == 1): ?>
                            
                            <div id="stock_ropa" class="stock-group hidden">
                                <span class="titulo-seccion">1. Inventario y Tabla de Medidas (S - XL)</span>
                                <span class="info-text">Edita los títulos (ej: Pecho, Largo) y pon las medidas en cm.</span>
                                
                                <table class="tabla-medidas">
                                    <thead>
                                        <tr>
                                            <th style="width:50px;">Talla</th>
                                            <th style="width:70px;">Stock</th>
                                            <th>
                                                <input type="text" name="header_1" value="<?php echo $valores['medidas']['nombres'][0] ?? 'Ancho (cm)'; ?>" placeholder="Nombre Medida 1" style="font-weight:bold; color:#333; background:#fff; border:1px solid #ccc;">
                                            </th>
                                            <th>
                                                <input type="text" name="header_2" value="<?php echo $valores['medidas']['nombres'][1] ?? 'Largo (cm)'; ?>" placeholder="Nombre Medida 2" style="font-weight:bold; color:#333; background:#fff; border:1px solid #ccc;">
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach (['S', 'M', 'L', 'XL'] as $t): ?>
                                            <tr>
                                                <td><b><?php echo $t; ?></b></td>
                                                <td><input type="number" name="stock_<?php echo strtolower($t); ?>" value="<?php echo $variantes_db[$t] ?? 0; ?>" min="0" style="background:#f9f9f9;"></td>
                                                <td><input type="text" name="medidas[<?php echo $t; ?>][m1]" value="<?php echo $valores['medidas'][$t]['m1'] ?? ''; ?>" placeholder="-"></td>
                                                <td><input type="text" name="medidas[<?php echo $t; ?>][m2]" value="<?php echo $valores['medidas'][$t]['m2'] ?? ''; ?>" placeholder="-"></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>

                            <div id="stock_pantalones" class="stock-group hidden">
                                <span class="titulo-seccion">1. Inventario y Medidas (Jeans)</span>
                                <table class="tabla-medidas">
                                    <thead>
                                        <tr>
                                            <th>Talla</th>
                                            <th>Stock</th>
                                            <th>Cintura (cm)</th>
                                            <th>Largo (cm)</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php for ($i = 28; $i <= 36; $i += 2): ?>
                                            <tr>
                                                <td><b><?php echo $i; ?></b></td>
                                                <td><input type="number" name="stock_pant_<?php echo $i; ?>" value="<?php echo $variantes_db[(string)$i] ?? 0; ?>" min="0" style="background:#f9f9f9;"></td>
                                                <td><input type="text" name="medidas[<?php echo $i; ?>][cintura]" value="<?php echo $valores['medidas'][$i]['cintura'] ?? ''; ?>" placeholder="-"></td>
                                                <td><input type="text" name="medidas[<?php echo $i; ?>][largo]" value="<?php echo $valores['medidas'][$i]['largo'] ?? ''; ?>" placeholder="-"></td>
                                            </tr>
                                        <?php endfor; ?>
                                    </tbody>
                                </table>
                            </div>

                            <div id="stock_calzado" class="stock-group hidden">
                                <label style="color:#a91e2c; font-weight:bold;">Tallas Calzado (38 - 44):</label><br><br>
                                <div class="stock-inputs">
                                    <?php for ($i = 38; $i <= 44; $i++): ?>
                                        <div class="stock-item"><label><?php echo $i; ?></label><input type="number" name="stock_zap_<?php echo $i; ?>" value="<?php echo $variantes_db[(string)$i] ?? 0; ?>" min="0"></div>
                                    <?php endfor; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div style="margin-top: 20px;">
                        <label>Foto Principal:</label><br>
                        <input type="file" name="foto" accept="image/*" <?php echo $foto_required; ?>>
                    </div>

                    <div style="text-align: right; margin-top: 20px;">
                        <button type="submit" class="btn-add-new" style="background:#28a745;">Guardar Cambios</button>
                    </div>
                </form>
            </div>

            <div class="table-container" style="background:white; padding:20px; border:1px solid #ddd; border-radius:8px;">
                <table class="admin-table" style="width: 100%; border-collapse: collapse; table-layout: fixed;">
                    <thead>
                        <tr style="background: #f8f9fa; text-align: left; border-bottom:2px solid #eee;">
                            <th style="padding: 15px; width: 80px;">ID</th>
                            <th style="padding: 15px; width: 80px;">Img</th>
                            <th style="padding: 15px;">Nombre</th>
                            <th style="padding: 15px; width: 120px;">Precio</th>
                            <th style="padding: 15px; width: 100px;">Estado</th>
                            <th style="padding: 15px; width: 180px; text-align:right;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($productos)): ?>
                            <tr><td colspan="6" class="no-data" style="padding:40px; text-align:center; color:#999;">No hay productos registrados en esta categoría.</td></tr>
                        <?php else: ?>
                            <?php foreach ($productos as $prod): ?>
                                <tr style="border-bottom: 1px solid #eee;">
                                    <td style="padding: 15px; color:#999; font-size: 12px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                        <?php echo substr($prod['id'], 0, 8); ?>... 
                                    </td>
                                    <td style="padding: 15px;">
                                        <img src="<?php echo !empty($prod['foto_ruta']) ? '../' . $prod['foto_ruta'] : '../style/img/placeholder.png'; ?>" class="preview-img-prod">
                                    </td>
                                    <td style="padding: 15px;">
                                        <strong><?php echo htmlspecialchars($prod['nombre']); ?></strong>
                                    </td>
                                    <td style="padding: 15px;">
                                        $<?php echo number_format($prod['precio'], 2); ?>
                                    </td>
                                    <td style="padding: 15px;">
                                        <span style="background:#e0ffe0; color:green; padding:4px 10px; border-radius:15px; font-size:12px; font-weight:bold;">Activo</span>
                                    </td>
                                    <td style="padding: 15px; text-align:right;">
                                        <a href="Aproductos.php?cat=<?php echo $cat_actual; ?>&edit_id=<?php echo $prod['id']; ?>" style="display:inline-block; padding:6px 12px; border:1px solid #007bff; color:#007bff; border-radius:4px; text-decoration:none; margin-right:5px; font-size:13px;">Editar</a>
                                        <form action="acciones_productos.php" method="POST" style="display:inline;" onsubmit="return confirm('¿Estás seguro de eliminar este producto?');">
                                            <input type="hidden" name="accion" value="eliminar_producto">
                                            <input type="hidden" name="id" value="<?php echo $prod['id']; ?>">
                                            <button type="submit" style="background:white; border:1px solid #dc3545; color:#dc3545; padding:6px 12px; border-radius:4px; cursor:pointer; font-size:13px;">Eliminar</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

        </main>
    </div>

    <script src="js/Aproductos.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            <?php if ($cat_actual == 1): ?>
                initProductos(true);
            <?php else: ?>
                initProductos(false);
            <?php endif; ?>
        });
    </script>
</body>
</html>