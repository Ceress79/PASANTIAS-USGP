<?php
session_start();
require_once "db/conexion.php";

// SEGURIDAD: Proteger el archivo para que solo admins accedan
if (!isset($_SESSION['admin_id'])) {
    header("Location: Alogin.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    try {
        
        // =================================================================
        // ACCIÓN 1: CREAR PRODUCTO
        // =================================================================
        if ($_POST['accion'] == 'crear_producto') {
            
            // 1. Recibir datos básicos
            $nombre = $_POST['nombre'];
            $precio = $_POST['precio'];
            $desc = $_POST['descripcion'];
            $material = $_POST['material'];
            $dimensiones = $_POST['dimensiones'];
            
            // Generar UUID
            $prod_uuid = sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
                mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff),
                mt_rand(0, 0x0fff) | 0x4000, mt_rand(0, 0x3fff) | 0x8000,
                mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
            );

            // Slug
            $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $nombre)));

            $pdo->beginTransaction();

            // 2. Obtener ID de categoría ROPA
            $stmtCat = $pdo->prepare("SELECT id FROM categorias WHERE nombre = 'ROPA' LIMIT 1");
            $stmtCat->execute();
            $cat = $stmtCat->fetch(PDO::FETCH_ASSOC);
            $cat_id = $cat ? $cat['id'] : 1; 

            // 3. Insertar en PRODUCTOS
            $sql = "INSERT INTO productos (id, nombre, slug, descripcion, material, dimensiones, precio, categoria_id, disponible) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$prod_uuid, $nombre, $slug, $desc, $material, $dimensiones, $precio, $cat_id]);

            // 4. Insertar VARIANTES (Tallas)
            $tallas = ['S' => $_POST['stock_s'], 'M' => $_POST['stock_m'], 'L' => $_POST['stock_l'], 'XL' => $_POST['stock_xl']];
            foreach ($tallas as $talla => $cantidad) {
                $var_uuid = uniqid(); 
                $stmtVar = $pdo->prepare("INSERT INTO variantes (id, producto_id, talla, stock) VALUES (?, ?, ?, ?)");
                $stmtVar->execute([$var_uuid, $prod_uuid, $talla, $cantidad]);
            }

            // 5. Manejar la IMAGEN
            if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
                $ext = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
                $nombre_archivo = $slug . '_' . time() . '.' . $ext;
                
                $ruta_db = "uploads/productos/" . $nombre_archivo;
                $ruta_fisica = "../uploads/productos/" . $nombre_archivo;

                if (!file_exists("../uploads/productos/")) {
                    mkdir("../uploads/productos/", 0777, true);
                }

                if (move_uploaded_file($_FILES['foto']['tmp_name'], $ruta_fisica)) {
                    $stmtFoto = $pdo->prepare("INSERT INTO fotos (tipo, producto_id, ruta, nombre_archivo, es_perfil) VALUES ('PRODUCTO', ?, ?, ?, 1)");
                    $stmtFoto->execute([$prod_uuid, $ruta_db, $nombre_archivo]);
                }
            }

            $pdo->commit();
            header("Location: Aproductos.php?mensaje=creado");
            exit();
        }

        // =================================================================
        // ACCIÓN 2: EDITAR PRODUCTO (NUEVO)
        // =================================================================
        elseif ($_POST['accion'] == 'editar_producto') {
            
            $id = $_POST['id'];
            $nombre = $_POST['nombre'];
            $precio = $_POST['precio'];
            $desc = $_POST['descripcion'];
            $material = $_POST['material'];
            $dimensiones = $_POST['dimensiones'];

            $pdo->beginTransaction();

            // 1. Actualizar Datos Básicos
            $sql = "UPDATE productos SET nombre=?, descripcion=?, material=?, dimensiones=?, precio=? WHERE id=?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$nombre, $desc, $material, $dimensiones, $precio, $id]);

            // 2. Actualizar Stocks (Variantes)
            // Se actualiza si existe, si no (caso raro), se inserta
            $tallas = ['S' => $_POST['stock_s'], 'M' => $_POST['stock_m'], 'L' => $_POST['stock_l'], 'XL' => $_POST['stock_xl']];
            
            foreach ($tallas as $talla => $cantidad) {
                $stmtVar = $pdo->prepare("UPDATE variantes SET stock = ? WHERE producto_id = ? AND talla = ?");
                $stmtVar->execute([$cantidad, $id, $talla]);
                
                // Si rowCount es 0, puede ser que el stock era el mismo O que no existía la talla.
                // Verificamos si existe la talla para ese producto.
                $check = $pdo->prepare("SELECT id FROM variantes WHERE producto_id=? AND talla=?");
                $check->execute([$id, $talla]);
                if ($check->rowCount() == 0) {
                     $stmtInsert = $pdo->prepare("INSERT INTO variantes (id, producto_id, talla, stock) VALUES (UUID(), ?, ?, ?)");
                     $stmtInsert->execute([$id, $talla, $cantidad]);
                }
            }

            // 3. Actualizar Foto (Solo si se subió una nueva)
            if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
                // Primero buscamos la foto vieja
                $stmtOld = $pdo->prepare("SELECT ruta FROM fotos WHERE producto_id = ? AND es_perfil = 1");
                $stmtOld->execute([$id]);
                $fotoVieja = $stmtOld->fetch(PDO::FETCH_ASSOC);

                // Procesar nueva foto
                $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $nombre)));
                $ext = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
                $nombre_archivo = $slug . '_' . time() . '.' . $ext;
                $ruta_db = "uploads/productos/" . $nombre_archivo;
                $ruta_fisica = "../uploads/productos/" . $nombre_archivo;

                if (move_uploaded_file($_FILES['foto']['tmp_name'], $ruta_fisica)) {
                    // Borrar archivo viejo físico si existe
                    if ($fotoVieja && file_exists("../" . $fotoVieja['ruta'])) {
                        unlink("../" . $fotoVieja['ruta']);
                    }
                    
                    // Actualizar DB
                    if ($fotoVieja) {
                        $stmtFoto = $pdo->prepare("UPDATE fotos SET ruta=?, nombre_archivo=? WHERE producto_id=? AND es_perfil=1");
                        $stmtFoto->execute([$ruta_db, $nombre_archivo, $id]);
                    } else {
                        // Si no tenía foto antes, insertar
                        $stmtFoto = $pdo->prepare("INSERT INTO fotos (tipo, producto_id, ruta, nombre_archivo, es_perfil) VALUES ('PRODUCTO', ?, ?, ?, 1)");
                        $stmtFoto->execute([$id, $ruta_db, $nombre_archivo]);
                    }
                }
            }

            $pdo->commit();
            header("Location: Aproductos.php?mensaje=actualizado");
            exit();
        }

        // =================================================================
        // ACCIÓN 3: ELIMINAR PRODUCTO
        // =================================================================
        elseif ($_POST['accion'] == 'eliminar_producto') {
            
            $id = $_POST['id'];

            // 1. Buscar las imágenes para borrar del disco
            $stmtFotos = $pdo->prepare("SELECT ruta FROM fotos WHERE producto_id = ?");
            $stmtFotos->execute([$id]);
            $fotos = $stmtFotos->fetchAll(PDO::FETCH_ASSOC);

            foreach ($fotos as $foto) {
                $archivo_fisico = "../" . $foto['ruta']; 
                if (file_exists($archivo_fisico)) {
                    unlink($archivo_fisico);
                }
            }

            // 2. Eliminar de la BD
            $stmt = $pdo->prepare("DELETE FROM productos WHERE id = ?");
            $stmt->execute([$id]);

            header("Location: Aproductos.php?mensaje=eliminado");
            exit();
        }

    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        header("Location: Aproductos.php?error=" . urlencode("Error del sistema: " . $e->getMessage()));
        exit();
    }

} else {
    header("Location: Aproductos.php");
    exit();
}