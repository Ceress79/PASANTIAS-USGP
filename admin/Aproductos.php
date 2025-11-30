<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: Alogin.php");
    exit();
}
require_once "db/conexion.php";

// 1. DETERMINAR CATEGORÍA ACTUAL (Por defecto 1 = Ropa)
// Si no hay GET, usamos 1. Si intentan poner 4 (Otros), lo forzamos a 1.
$cat_actual = isset($_GET['cat']) ? (int)$_GET['cat'] : 1;
if ($cat_actual < 1 || $cat_actual > 3) $cat_actual = 1;

// Nombres para mostrar
$nombres_cat = [1 => 'Ropa y Accesorios', 2 => 'Papelería', 3 => 'Hogar'];
$nombre_cat_actual = $nombres_cat[$cat_actual];

// --- LÓGICA DE EDICIÓN ---
$producto_editar = null;
$mostrar_formulario = false; // Por defecto oculto

// Valores por defecto
$valores = [
    'nombre' => '', 'precio' => '', 'descripcion' => '', 'material' => '', 'dimensiones' => '',
    'stock_unico' => 0, 'tipo_stock' => ''
];
$accion_form = 'crear_producto';
$titulo_form = 'Añadir Nuevo Producto';
$btn_texto = 'Guardar Producto';
$foto_required = 'required';
$variantes_db = [];

if (isset($_GET['edit_id'])) {
    $mostrar_formulario = true; // Si editamos, mostramos el form
    $id_editar = $_GET['edit_id'];
    $stmt = $pdo->prepare("SELECT * FROM productos WHERE id = ?");
    $stmt->execute([$id_editar]);
    $producto_editar = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($producto_editar) {
        // Asegurar que estamos en la categoría correcta al editar
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
        
        // Deducir tipo de stock
        if(isset($variantes_db['S'])) $valores['tipo_stock'] = 'ropa';
        elseif(isset($variantes_db['38'])) $valores['tipo_stock'] = 'calzado';
        elseif(isset($variantes_db['28'])) $valores['tipo_stock'] = 'pantalones';
        else $valores['tipo_stock'] = 'unico';
    }
}

// --- CONSULTA LISTADO (Filtrada por la categoría actual) ---
$sql = "SELECT p.*, f.ruta as foto_ruta 
        FROM productos p 
        LEFT JOIN fotos f ON p.id = f.producto_id AND f.es_perfil = 1 
        WHERE p.categoria_id = ? 
        ORDER BY p.creado_en DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute([$cat_actual]);
$productos = $stmt->fetchAll(PDO::FETCH_ASSOC);
$prod_count = count($productos);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Productos</title>
    <link rel="stylesheet" href="css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* ESTILOS NUEVOS TIPO BANNERS */
        .top-nav { display: flex; gap: 10px; margin-bottom: 20px; }
        .nav-btn {
            background: white; border: 1px solid #ddd; padding: 10px 20px; 
            border-radius: 5px; text-decoration: none; color: #555; font-weight: bold;
            display: flex; align-items: center; gap: 8px; transition: 0.2s;
        }
        .nav-btn:hover { background: #f9f9f9; border-color: #ccc; }
        .nav-btn.active { background: #00bcd4; color: white; border-color: #00bcd4; } /* Color cyan tipo tu imagen */
        
        .header-actions { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        .btn-add-new { 
            background: #007bff; color: white; padding: 10px 20px; border-radius: 5px; 
            text-decoration: none; font-weight: bold; cursor: pointer; border: none;
        }
        .btn-add-new:hover { background: #0056b3; }

        /* Formulario desplegable */
        #formContainer { display: <?php echo $mostrar_formulario ? 'block' : 'none'; ?>; transition: all 0.3s; margin-bottom: 30px; }
        
        /* Estilos generales del form */
        .form-box { background: white; padding: 25px; border-radius: 8px; border: 1px solid #ddd; box-shadow: 0 2px 5px rgba(0,0,0,0.05); }
        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 15px; }
        input, textarea, select { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px; margin-top: 5px; }
        
        /* Stocks */
        .stock-group { background: #f8f9fa; padding: 15px; border-radius: 8px; border: 1px dashed #ccc; margin-top: 15px; }
        .stock-inputs { display: flex; gap: 10px; flex-wrap: wrap; }
        .stock-item { text-align: center; }
        .stock-item input { width: 60px; text-align: center; }
        
        .hidden { display: none; }
        .preview-img-prod { width: 60px; height: 60px; object-fit: cover; border-radius: 4px; }
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
            <h2 style="margin:0;">Listado para: <?php echo $nombre_cat_actual; ?></h2>
            
            <?php if(!$mostrar_formulario): ?>
                <button class="btn-add-new" onclick="toggleForm()">
                    <i class="fas fa-plus"></i> Añadir Nuevo Producto
                </button>
            <?php endif; ?>
        </div>

        <?php if(isset($_GET['mensaje'])): ?><p class="msg-success" style="color:green; margin-bottom:15px;">¡Acción realizada con éxito!</p><?php endif; ?>

        <div id="formContainer">
            <form action="acciones_productos.php" method="POST" enctype="multipart/form-data" class="form-box">
                <input type="hidden" name="accion" value="<?php echo $accion_form; ?>">
                <?php if($producto_editar): ?><input type="hidden" name="id" value="<?php echo $producto_editar['id']; ?>"><?php endif; ?>
                
                <input type="hidden" name="categoria_id" value="<?php echo $cat_actual; ?>">

                <div style="display:flex; justify-content:space-between; border-bottom:1px solid #eee; padding-bottom:10px; margin-bottom:20px;">
                    <h3 style="margin:0;"><?php echo $titulo_form; ?></h3>
                    <button type="button" onclick="cancelarForm()" style="background:none; border:none; color:red; cursor:pointer;">Cancelar</button>
                </div>

                <?php if($cat_actual == 1): ?>
                    <div style="margin-bottom: 20px; background: #fff5f5; padding: 15px; border-radius: 5px; border: 1px solid #ffdce0;">
                        <label style="color:#a91e2c; font-weight:bold;">Tipo de Variantes / Tallas:</label>
                        <select name="tipo_stock" id="tipoStockSelect" onchange="mostrarBloqueStock()">
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
                        <label style="color:#007bff; font-weight:bold;">Stock Total (Unidades):</label>
                        <input type="number" name="stock_unico_cant" value="<?php echo $valores['stock_unico']; ?>" min="0" style="width:120px;">
                    </div>

                    <?php if($cat_actual == 1): ?>
                        <div id="stock_ropa" class="stock-group hidden">
                            <label style="color:#a91e2c; font-weight:bold;">Tallas (S - XL):</label><br><br>
                            <div class="stock-inputs">
                                <?php foreach(['S','M','L','XL'] as $t): ?>
                                    <div class="stock-item"><label><?php echo $t; ?></label><input type="number" name="stock_<?php echo strtolower($t); ?>" value="<?php echo $variantes_db[$t] ?? 0; ?>" min="0"></div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <div id="stock_pantalones" class="stock-group hidden">
                            <label style="color:#a91e2c; font-weight:bold;">Tallas Jeans (28 - 36):</label><br><br>
                            <div class="stock-inputs">
                                <?php for($i=28; $i<=36; $i+=2): ?>
                                    <div class="stock-item"><label><?php echo $i; ?></label><input type="number" name="stock_pant_<?php echo $i; ?>" value="<?php echo $variantes_db[(string)$i] ?? 0; ?>" min="0"></div>
                                <?php endfor; ?>
                            </div>
                        </div>
                        <div id="stock_calzado" class="stock-group hidden">
                            <label style="color:#a91e2c; font-weight:bold;">Tallas Calzado (38 - 44):</label><br><br>
                            <div class="stock-inputs">
                                <?php for($i=38; $i<=44; $i++): ?>
                                    <div class="stock-item"><label><?php echo $i; ?></label><input type="number" name="stock_zap_<?php echo $i; ?>" value="<?php echo $variantes_db[(string)$i] ?? 0; ?>" min="0"></div>
                                <?php endfor; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <div style="margin-top: 20px;">
                    <label>Foto:</label><br>
                    <input type="file" name="foto" accept="image/*" <?php echo $foto_required; ?>>
                </div>

                <div style="text-align: right; margin-top: 20px;">
                    <button type="submit" class="btn-add-new" style="background:#28a745;">Guardar Cambios</button>
                </div>
            </form>
        </div>

        <div class="table-container" style="background:white; padding:20px; border:1px solid #ddd; border-radius:8px;">
            <table class="admin-table" style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="background: #f4f4f4; text-align: left; border-bottom:2px solid #ddd;">
                        <th style="padding: 12px;">ID</th>
                        <th style="padding: 12px;">Vista Previa</th>
                        <th style="padding: 12px;">Nombre</th>
                        <th style="padding: 12px;">Estado</th>
                        <th style="padding: 12px; text-align:right;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($productos)): ?>
                        <tr><td colspan="5" class="no-data" style="padding:30px; text-align:center; color:#777;">No hay productos en <?php echo $nombre_cat_actual; ?>.</td></tr>
                    <?php else: ?>
                        <?php foreach ($productos as $prod): ?>
                            <tr style="border-bottom: 1px solid #eee;">
                                <td style="padding: 12px; color:#777;"><?php echo $prod['id']; ?></td>
                                <td style="padding: 12px;">
                                    <img src="<?php echo !empty($prod['foto_ruta']) ? '../'.$prod['foto_ruta'] : '../style/img/placeholder.png'; ?>" class="preview-img-prod">
                                </td>
                                <td style="padding: 12px;">
                                    <strong><?php echo htmlspecialchars($prod['nombre']); ?></strong><br>
                                    <small>$<?php echo number_format($prod['precio'], 2); ?></small>
                                </td>
                                <td style="padding: 12px;">
                                    <span style="background:#e0ffe0; color:green; padding:3px 8px; border-radius:10px; font-size:12px;">Activo</span>
                                </td>
                                <td style="padding: 12px; text-align:right;">
                                    <a href="Aproductos.php?cat=<?php echo $cat_actual; ?>&edit_id=<?php echo $prod['id']; ?>" style="display:inline-block; padding:5px 10px; border:1px solid #007bff; color:#007bff; border-radius:4px; text-decoration:none; margin-right:5px;">Editar</a>
                                    <form action="acciones_productos.php" method="POST" style="display:inline;" onsubmit="return confirm('¿Eliminar?');">
                                        <input type="hidden" name="accion" value="eliminar_producto">
                                        <input type="hidden" name="id" value="<?php echo $prod['id']; ?>">
                                        <button type="submit" style="background:white; border:1px solid #dc3545; color:#dc3545; padding:5px 10px; border-radius:4px; cursor:pointer;">Eliminar</button>
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

<script>
function toggleForm() {
    document.getElementById('formContainer').style.display = 'block';
    window.scrollTo(0, 0); // Subir para ver el form
}

function cancelarForm() {
    // Si estamos editando (hay ID en URL), recargar sin ID para limpiar
    if (window.location.search.includes('edit_id')) {
        window.location.href = 'Aproductos.php?cat=<?php echo $cat_actual; ?>';
    } else {
        document.getElementById('formContainer').style.display = 'none';
    }
}

// Lógica de Stock para Ropa
function mostrarBloqueStock() {
    const tipo = document.getElementById('tipoStockSelect');
    if (!tipo) return; // Si no existe (porque estamos en Papelería/Hogar), salir.

    const val = tipo.value;
    
    // Ocultar todos los específicos de ropa
    ['stock_unico', 'stock_ropa', 'stock_calzado', 'stock_pantalones'].forEach(id => {
        if(document.getElementById(id)) document.getElementById(id).classList.add('hidden');
    });

    if (val === 'unico') document.getElementById('stock_unico').classList.remove('hidden');
    if (val === 'ropa') document.getElementById('stock_ropa').classList.remove('hidden');
    if (val === 'calzado') document.getElementById('stock_calzado').classList.remove('hidden');
    if (val === 'pantalones') document.getElementById('stock_pantalones').classList.remove('hidden');
}

// Ejecutar al cargar
document.addEventListener("DOMContentLoaded", function() {
    // Si estamos en Ropa, inicializar el selector
    <?php if($cat_actual == 1): ?>
        mostrarBloqueStock();
    <?php endif; ?>
});
</script>
</body>
</html>