<?php
if (isset($_SESSION['user_id'])) {
    $uid = $_SESSION['user_id'];
    
    // 1. PASAR EL CARRITO DE INVITADO A LA BASE DE DATOS (FUSIÓN)
    if (isset($_SESSION['carrito']) && !empty($_SESSION['carrito'])) {
        
        $sqlInsert = "INSERT INTO carrito_compras (user_id, producto_id, variante_id, cantidad) 
                      VALUES (:uid, :pid, :vid, :cant) 
                      ON DUPLICATE KEY UPDATE cantidad = cantidad + :cant_sum";
        
        $stmtInsert = $pdo->prepare($sqlInsert);

        foreach ($_SESSION['carrito'] as $item) {
            // Validar que el item tenga datos correctos
            if (!isset($item['id'])) continue;

            $pid = $item['id'];
            $vid = (isset($item['variante_id']) && $item['variante_id'] != 0) ? $item['variante_id'] : '0';
            $cant = $item['cantidad'];

            $stmtInsert->execute([
                ':uid' => $uid,
                ':pid' => $pid,
                ':vid' => $vid,
                ':cant' => $cant,
                ':cant_sum' => $cant // Sumamos lo que traía el invitado a lo que ya tenía guardado
            ]);
        }
    }

    // 2. BORRAR SESIÓN ANTIGUA Y RECARGAR DESDE LA BASE DE DATOS 
    $_SESSION['carrito'] = []; 

    $stmtGet = $pdo->prepare("SELECT * FROM carrito_compras WHERE user_id = ?");
    $stmtGet->execute([$uid]);
    $items_db = $stmtGet->fetchAll(PDO::FETCH_ASSOC);

    foreach ($items_db as $row) {
        $pid = $row['producto_id'];
        $vid = $row['variante_id'];
        $cant = $row['cantidad'];

        // Reconstruir la clave para la sesión
        if ($vid === '0' || $vid === 0) {
            // Producto pendiente de talla
            $clave = 'pendiente_' . $pid;
            $tipo = 'pendiente';
            $vid_sesion = 0;
        } else {
            // Producto completo
            $clave = $vid;
            $tipo = 'completo';
            $vid_sesion = $vid;
        }

        // Llenar sesión
        $_SESSION['carrito'][$clave] = [
            'id' => $pid,
            'variante_id' => $vid_sesion,
            'cantidad' => $cant,
            'tipo' => $tipo
        ];
    }
}
?>