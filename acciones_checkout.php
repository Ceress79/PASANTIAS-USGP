<?php
session_start();
require_once "admin/db/conexion.php";

if (!isset($_SESSION['user_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: index.php");
    exit();
}

if ($_POST['accion'] == 'procesar_envio') {

    $user_id = $_SESSION['user_id'];

    // 1. Recopilar datos del formulario en un Array
    $direccion = [
        'calle_principal' => $_POST['calle_principal'],
        'provincia'       => $_POST['provincia'],
        'canton'          => $_POST['canton'],
        'parroquia'       => $_POST['parroquia'],
        'calle_secundaria' => $_POST['calle_secundaria'],
        'departamento'    => $_POST['departamento'],
        'referencia'      => $_POST['referencia'],
        'tipo'            => $_POST['tipo_direccion'],
        'recibe_nombre'   => $_POST['recibe_nombre'],
        'recibe_telefono' => $_POST['recibe_telefono']
    ];

    // Convertir a JSON para guardar en la BD
    $direccion_json = json_encode($direccion, JSON_UNESCAPED_UNICODE);

    try {
        $pdo->beginTransaction();

        // 2. Calcular Total del Carrito
        // (Nota: Idealmente deberíamos recalcular precios aquí por seguridad, 
        // pero por ahora sumamos lo que hay en carrito)
        $stmt = $pdo->prepare("
            SELECT c.*, p.precio, p.nombre 
            FROM carrito_compras c 
            JOIN productos p ON c.producto_id = p.id 
            WHERE c.user_id = ?
        ");
        $stmt->execute([$user_id]);
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $total_orden = 0;
        foreach ($items as $item) {
            $total_orden += $item['precio'] * $item['cantidad'];
        }

        // 3. Crear la Orden en la tabla 'orders'
        // Generamos un UUID para la orden
        $order_uuid = uniqid('ORD-');

        $sql_order = "INSERT INTO orders (order_uuid, user_id, total, estado, direccion_envio, telefono_envio, creado_en) 
                      VALUES (?, ?, ?, 'PENDIENTE', ?, ?, NOW())";
        $stmtOrder = $pdo->prepare($sql_order);
        $stmtOrder->execute([
            $order_uuid,
            $user_id,
            $total_orden,
            $direccion_json, // Guardamos todo el JSON aquí
            $_POST['recibe_telefono']
        ]);

        $order_id = $pdo->lastInsertId();

        // 4. Mover items del Carrito a 'order_items'
        $sql_item = "INSERT INTO order_items (order_id, producto_id, nombre_producto, precio_unitario, cantidad, subtotal) 
                     VALUES (?, ?, ?, ?, ?, ?)";
        $stmtItem = $pdo->prepare($sql_item);

        foreach ($items as $item) {
            $subtotal = $item['precio'] * $item['cantidad'];
            $stmtItem->execute([
                $order_id,
                $item['producto_id'],
                $item['nombre'],
                $item['precio'],
                $item['cantidad'],
                $subtotal
            ]);
        }

        
        

        $pdo->commit();

        // 6. Redirigir a "Pedido Completado" o Pasarela de Pago
        // Por ahora, al éxito
        // Redirigir a la página de revisión (Frame 7)
        header("Location: revisar_compra.php?order=" . $order_uuid);
        exit();
    } catch (Exception $e) {
        $pdo->rollBack();
        die("Error al procesar la orden: " . $e->getMessage());
    }
}
