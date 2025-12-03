<?php

error_reporting(0);
ini_set('display_errors', 0);
ob_start();

require_once 'bases/config_sesion.php'; 
require_once "admin/db/conexion.php"; 

header('Content-Type: application/json');

if (!isset($_SESSION['carrito'])) {
    $_SESSION['carrito'] = [];
}

$response = ['exito' => false, 'mensaje' => 'Error desconocido'];

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'agregar_producto') {
        
        // Validaciones
        if (empty($_POST['producto_id'])) throw new Exception("Falta ID del producto.");
        
        $producto_id = trim($_POST['producto_id']);
        if ($producto_id === '' || $producto_id === '0') throw new Exception("ID inválido.");

        $variante_id = isset($_POST['variante_id']) && $_POST['variante_id'] != '' ? $_POST['variante_id'] : 0;
        $cantidad = 1;

        // --- 2. VERIFICACIÓN DE STOCK INTELIGENTE ---
        $stock_disponible = 0;
        $clave_carrito = '';

        if ($variante_id != 0) {
            // CASO A: Talla específica seleccionada -> Miramos tabla variantes
            $stmtStock = $pdo->prepare("SELECT stock FROM variantes WHERE id = ?");
            $stmtStock->execute([$variante_id]);
            $stock_disponible = $stmtStock->fetchColumn();
            $clave_carrito = $variante_id;
        } else {
            // CASO B: Sin talla -> Miramos stock total o suma de variantes
            // Hacemos una consulta que trae el stock_total Y la suma de las variantes
            $stmtStock = $pdo->prepare("
                SELECT p.stock_total, 
                       (SELECT SUM(stock) FROM variantes WHERE producto_id = p.id) as suma_variantes 
                FROM productos p 
                WHERE p.id = ?
            ");
            $stmtStock->execute([$producto_id]);
            $row = $stmtStock->fetch(PDO::FETCH_ASSOC);

            if (!$row) throw new Exception("Producto no encontrado.");

            // Si hay variantes, usamos la suma. Si no (es NULL), usamos el stock_total del padre.
            $stock_disponible = ($row['suma_variantes'] !== null) ? $row['suma_variantes'] : $row['stock_total'];
            
            $clave_carrito = 'pendiente_' . $producto_id;
        }

        if ($stock_disponible === false) throw new Exception("Error al consultar stock.");

        // Validar cantidad
        $cantidad_en_carrito = isset($_SESSION['carrito'][$clave_carrito]) ? $_SESSION['carrito'][$clave_carrito]['cantidad'] : 0;
        $cantidad_total_deseada = $cantidad_en_carrito + $cantidad;

        if ($cantidad_total_deseada > $stock_disponible) {
            throw new Exception("Stock insuficiente. Quedan: " . $stock_disponible);
        }

        // --- 3. GUARDAR EN SESIÓN ---
        if (isset($_SESSION['carrito'][$clave_carrito])) {
            $_SESSION['carrito'][$clave_carrito]['cantidad'] += $cantidad;
        } else {
            $_SESSION['carrito'][$clave_carrito] = [
                'id' => $producto_id,
                'variante_id' => $variante_id,
                'cantidad' => $cantidad,
                'tipo' => ($variante_id != 0) ? 'completo' : 'pendiente'
            ];
        }

        // --- 4. GUARDAR EN BASE DE DATOS ---
        if (isset($_SESSION['user_id'])) {
            $user_id = $_SESSION['user_id'];
            $var_db = ($variante_id === 0) ? '0' : $variante_id;

            $sql = "INSERT INTO carrito_compras (user_id, producto_id, variante_id, cantidad) 
                    VALUES (:uid, :pid, :vid, :cant) 
                    ON DUPLICATE KEY UPDATE cantidad = cantidad + :cant_sum";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':uid' => $user_id, ':pid' => $producto_id, ':vid' => $var_db,
                ':cant' => $cantidad, ':cant_sum' => $cantidad
            ]);
        }

        $total = 0;
        foreach ($_SESSION['carrito'] as $item) { $total += $item['cantidad']; }

        $response = ['exito' => true, 'articulos' => $total, 'mensaje' => 'Guardado exitosamente.'];
    } else {
        throw new Exception("Solicitud inválida.");
    }

} catch (Exception $e) {
    $response = ['exito' => false, 'mensaje' => $e->getMessage()];
}

ob_end_clean();
echo json_encode($response);
exit;