<?php
// acciones_carrito.php
// Script de Backend para manejar el carrito vía AJAX.
// No tiene HTML porque solo devuelve datos JSON.

session_start();

// Opcional: Incluir conexión si quieres validar stock en el futuro
// require_once "admin/db/conexion.php"; 

// Indicamos que la respuesta será en formato JSON
header('Content-Type: application/json');

// Inicializar el carrito si no existe
if (!isset($_SESSION['carrito'])) {
    $_SESSION['carrito'] = [];
}

// Verificar si la acción es "agregar_producto"
if (isset($_POST['accion']) && $_POST['accion'] === 'agregar_producto') {
    
    // Validar que recibimos los datos necesarios
    if (!isset($_POST['producto_id']) || !isset($_POST['variante_id'])) {
        http_response_code(400); // Solicitud incorrecta
        echo json_encode(['exito' => false, 'mensaje' => 'Faltan datos (ID o Talla).']);
        exit;
    }

    $producto_id = intval($_POST['producto_id']);
    $variante_id = intval($_POST['variante_id']); // Este es el ID de la TALLA
    $cantidad = 1; // Por defecto sumamos 1

    // Validar que el ID sea válido
    if ($variante_id > 0) {
        
        // --- LÓGICA DE AGREGADO ---
        // Usamos el ID de la VARIANTE (Talla) como la clave principal del array
        // Esto permite tener el mismo producto en tallas distintas (S, M, L) por separado.
        
        if (isset($_SESSION['carrito'][$variante_id])) {
            // Si ya existe esa talla en el carrito, sumamos la cantidad
            $_SESSION['carrito'][$variante_id]['cantidad'] += $cantidad;
        } else {
            // Si es nuevo, lo creamos
            $_SESSION['carrito'][$variante_id] = [
                'id' => $producto_id,       // Guardamos ID del padre
                'variante_id' => $variante_id, // Guardamos ID de la variante
                'cantidad' => $cantidad
            ];
        }
        
        // Calcular el total de artículos para actualizar el icono del carrito
        $total_articulos = 0;
        foreach ($_SESSION['carrito'] as $item) {
            $total_articulos += $item['cantidad'];
        }

        // Responder con éxito
        echo json_encode([
            'exito' => true, 
            'mensaje' => 'Producto añadido correctamente.',
            'articulos' => $total_articulos
        ]);
        
    } else {
        echo json_encode(['exito' => false, 'mensaje' => 'ID de variante inválido.']);
    }
    
} else {
    // Si entran al archivo directamente sin POST
    echo json_encode(['exito' => false, 'mensaje' => 'Acción no permitida.']);
}
?>