<?php
// acciones_carrito.php
// VERSIÓN COMPATIBLE CON UUID (TEXTO)

error_reporting(0);
ini_set('display_errors', 0);
ob_start();

session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['carrito'])) {
    $_SESSION['carrito'] = [];
}

$response = ['exito' => false, 'mensaje' => 'Error desconocido'];

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'agregar_producto') {
        
        // 1. Validar ID del Producto (COMO TEXTO)
        if (empty($_POST['producto_id'])) {
            throw new Exception("No se recibió el ID del producto.");
        }

        // 🔥 CAMBIO CLAVE: No usamos intval(). Lo tratamos como string.
        $producto_id = trim($_POST['producto_id']);
        
        // Validamos que no esté vacío y no sea el string "0"
        if ($producto_id === '' || $producto_id === '0') {
            throw new Exception("ID de producto inválido (Vacio o Cero).");
        }

        // Para la variante, también permitimos que sea texto o número
        $variante_id = isset($_POST['variante_id']) && $_POST['variante_id'] != '' ? $_POST['variante_id'] : 0;
        $cantidad = 1;

        // --- LÓGICA DE GUARDADO ---
        if ($variante_id != 0) {
            // CASO A: Producto con Talla
            // Usamos variante_id como clave (PHP maneja claves string en arrays perfectamente)
            if (isset($_SESSION['carrito'][$variante_id])) {
                $_SESSION['carrito'][$variante_id]['cantidad'] += $cantidad;
            } else {
                $_SESSION['carrito'][$variante_id] = [
                    'id' => $producto_id,
                    'variante_id' => $variante_id,
                    'cantidad' => $cantidad,
                    'tipo' => 'completo'
                ];
            }
        } else {
            // CASO B: Pendiente de Talla
            $clave_pendiente = 'pendiente_' . $producto_id;
            
            if (isset($_SESSION['carrito'][$clave_pendiente])) {
                $_SESSION['carrito'][$clave_pendiente]['cantidad'] += $cantidad;
            } else {
                $_SESSION['carrito'][$clave_pendiente] = [
                    'id' => $producto_id,
                    'variante_id' => 0,
                    'cantidad' => $cantidad,
                    'tipo' => 'pendiente'
                ];
            }
        }

        // Contar total
        $total = 0;
        foreach ($_SESSION['carrito'] as $item) {
            $total += $item['cantidad'];
        }

        $response = ['exito' => true, 'articulos' => $total, 'mensaje' => 'Producto añadido correctamente.'];
    } else {
        throw new Exception("Solicitud inválida.");
    }

} catch (Exception $e) {
    $response = ['exito' => false, 'mensaje' => $e->getMessage()];
}

ob_end_clean();
echo json_encode($response);
exit;