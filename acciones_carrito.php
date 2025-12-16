<?php
// acciones_carrito.php
error_reporting(0);
ini_set('display_errors', 0);
ob_start(); // Iniciar buffer para limpiar cualquier salida indeseada

// Cargar sesión
if (file_exists('bases/config_sesion.php')) {
    require_once 'bases/config_sesion.php';
} else {
    session_start();
}

require_once "admin/db/conexion.php"; 

header('Content-Type: application/json');

if (!isset($_SESSION['carrito'])) $_SESSION['carrito'] = [];

$response = ['exito' => false, 'mensaje' => 'Acción desconocida'];

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion'])) {
        $accion = $_POST['accion'];

        // ==========================================
        // 1. OBTENER INFO PARA EL MODAL (Para el botón "Añadir" estilo Temu)
        // ==========================================
        if ($accion === 'obtener_info_modal') {
            if (empty($_POST['producto_id'])) throw new Exception("Falta ID.");
            $pid = $_POST['producto_id'];

            // 1. Datos básicos del producto
            $stmt = $pdo->prepare("SELECT p.*, f.ruta as foto FROM productos p LEFT JOIN fotos f ON p.id = f.producto_id AND f.es_perfil = 1 WHERE p.id = ?");
            $stmt->execute([$pid]);
            $prod = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$prod) throw new Exception("Producto no encontrado.");

            // 2. Variantes (Tallas)
            $stmtVar = $pdo->prepare("SELECT id, talla, stock FROM variantes WHERE producto_id = ? ORDER BY FIELD(talla, 'S','M','L','XL')");
            $stmtVar->execute([$pid]);
            $variantes = $stmtVar->fetchAll(PDO::FETCH_ASSOC);

            // 3. Calcular Stock Real Total (Suma de variantes)
            $stock_total = 0;
            if (count($variantes) > 0) {
                foreach ($variantes as $v) $stock_total += $v['stock'];
            } else {
                $stock_total = $prod['stock_total'];
            }

            $response = [
                'exito' => true,
                'producto' => $prod,
                'variantes' => $variantes,
                'stock_real' => $stock_total
            ];
        }

        // ==========================================
        // 2. AGREGAR PRODUCTO (Al carrito)
        // ==========================================
        elseif ($accion === 'agregar_producto') {
            if (empty($_POST['producto_id'])) throw new Exception("Falta ID.");
            $pid = $_POST['producto_id'];
            $vid = isset($_POST['variante_id']) && $_POST['variante_id'] != '' ? $_POST['variante_id'] : 0;
            $cant = 1;

            $stock = obtenerStock($pdo, $pid, $vid);
            
            // Definir clave: Si tiene talla es el ID de variante, sino 'pendiente_ID'
            $clave = ($vid != 0) ? $vid : 'pendiente_' . $pid;
            $actual = isset($_SESSION['carrito'][$clave]) ? $_SESSION['carrito'][$clave]['cantidad'] : 0;

            if (($actual + $cant) > $stock) throw new Exception("Stock insuficiente.");

            guardarItem($pid, $vid, $cant, $clave, true);
            $response = ['exito' => true, 'articulos' => contarTotal(), 'mensaje' => 'Añadido.'];
        }

        // ==========================================
        // 3. CAMBIAR CANTIDAD (Sumar/Restar en carrito.php)
        // ==========================================
        elseif ($accion === 'cambiar_cantidad') {
            $clave = $_POST['id'];
            $tipo = $_POST['tipo'];

            if (!isset($_SESSION['carrito'][$clave])) throw new Exception("Item no encontrado.");
            $item = $_SESSION['carrito'][$clave];

            if ($tipo === 'sumar') {
                $stock = obtenerStock($pdo, $item['id'], $item['variante_id']);
                if (($item['cantidad'] + 1) > $stock) {
                    throw new Exception("¡No hay más unidades disponibles!");
                }
                $_SESSION['carrito'][$clave]['cantidad']++;
                actualizarBD($pdo, $_SESSION['carrito'][$clave], 'actualizar');
            } 
            elseif ($tipo === 'restar') {
                if ($item['cantidad'] > 1) {
                    $_SESSION['carrito'][$clave]['cantidad']--;
                    actualizarBD($pdo, $_SESSION['carrito'][$clave], 'actualizar');
                }
            }
            $response = ['exito' => true, 'articulos' => contarTotal()];
        }

        // ==========================================
        // 4. ELIMINAR (Borrar item en carrito.php)
        // ==========================================
        elseif ($accion === 'eliminar') {
            $clave = $_POST['id'];
            if (isset($_SESSION['carrito'][$clave])) {
                $item = $_SESSION['carrito'][$clave];
                unset($_SESSION['carrito'][$clave]);
                actualizarBD($pdo, $item, 'eliminar');
            }
            $response = ['exito' => true, 'articulos' => contarTotal()];
        }
    }
} catch (Exception $e) {
    $response = ['exito' => false, 'mensaje' => $e->getMessage()];
}

ob_end_clean(); // Limpiar cualquier echo previo
echo json_encode($response);
exit;

// --- FUNCIONES AUXILIARES ---

function obtenerStock($pdo, $pid, $vid) {
    if ($vid != 0) {
        $s = $pdo->prepare("SELECT stock FROM variantes WHERE id = ?");
        $s->execute([$vid]);
        $r = $s->fetchColumn();
    } else {
        $s = $pdo->prepare("SELECT p.stock_total, (SELECT SUM(stock) FROM variantes WHERE producto_id = p.id) as suma FROM productos p WHERE p.id = ?");
        $s->execute([$pid]);
        $d = $s->fetch(PDO::FETCH_ASSOC);
        $r = ($d['suma'] !== null) ? $d['suma'] : $d['stock_total'];
    }
    return ($r === false) ? 0 : (int)$r;
}

function guardarItem($pid, $vid, $cant, $clave, $sumar) {
    if (isset($_SESSION['carrito'][$clave]) && $sumar) $_SESSION['carrito'][$clave]['cantidad'] += $cant;
    else $_SESSION['carrito'][$clave] = ['id'=>$pid, 'variante_id'=>$vid, 'cantidad'=>$cant, 'tipo'=>($vid!=0?'completo':'pendiente')];
    
    if (isset($_SESSION['user_id'])) { global $pdo; actualizarBD($pdo, $_SESSION['carrito'][$clave], 'insertar'); }
}

function actualizarBD($pdo, $item, $accion) {
    if (!isset($_SESSION['user_id'])) return;
    $uid = $_SESSION['user_id'];
    $pid = $item['id'];
    $vid = ($item['variante_id'] == 0) ? '0' : $item['variante_id'];
    $cant = $item['cantidad'];

    if ($accion === 'eliminar') {
        $pdo->prepare("DELETE FROM carrito_compras WHERE user_id=? AND producto_id=? AND variante_id=?")->execute([$uid, $pid, $vid]);
    } else {
        $pdo->prepare("INSERT INTO carrito_compras (user_id, producto_id, variante_id, cantidad) VALUES (?,?,?,?) ON DUPLICATE KEY UPDATE cantidad=?")->execute([$uid, $pid, $vid, $cant, $cant]);
    }
}

function contarTotal() {
    $t = 0; foreach ($_SESSION['carrito'] as $i) $t += $i['cantidad']; return $t;
}
?>