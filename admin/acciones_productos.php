<?php
session_start();
require_once "db/conexion.php";

if (!isset($_SESSION['admin_id'])) { header("Location: Alogin.php"); exit(); }

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $accion = $_POST['accion'];
        
        // Capturamos categoría para redirigir correctamente
        $categoria_redir = isset($_POST['categoria_id']) ? $_POST['categoria_id'] : 1;

        // --- CREAR O EDITAR ---
        if ($accion == 'crear_producto' || $accion == 'editar_producto') {
            
            $nombre = $_POST['nombre'];
            $precio = $_POST['precio'];
            $desc = $_POST['descripcion'];
            $material = $_POST['material'];
            $dimensiones = $_POST['dimensiones'];
            $categoria_id = $_POST['categoria_id'];
            $tipo_stock = $_POST['tipo_stock']; 

            $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $nombre)));

            // --- PROCESAR MEDIDAS (JSON) ---
            $medidas_data = [];
            if (isset($_POST['medidas']) && is_array($_POST['medidas'])) {
                $medidas_data = $_POST['medidas'];
                
                // Si es ropa estándar, guardamos también los nombres de las columnas personalizados
                if ($tipo_stock == 'ropa') {
                    $medidas_data['nombres'] = [
                        $_POST['header_1'] ?? 'Medida 1',
                        $_POST['header_2'] ?? 'Medida 2'
                    ];
                }
            }
            // Convertir a JSON
            $medidas_json = !empty($medidas_data) ? json_encode($medidas_data) : null;
            // --------------------------------

            if ($accion == 'crear_producto') {
                $id = sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x', mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0x0fff) | 0x4000, mt_rand(0, 0x3fff) | 0x8000, mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff));
                
                $sql = "INSERT INTO productos (id, nombre, slug, descripcion, material, dimensiones, medidas_json, precio, categoria_id, disponible) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 1)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$id, $nombre, $slug, $desc, $material, $dimensiones, $medidas_json, $precio, $categoria_id]);
            } else {
                $id = $_POST['id'];
                $sql = "UPDATE productos SET nombre=?, descripcion=?, material=?, dimensiones=?, medidas_json=?, precio=?, categoria_id=? WHERE id=?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$nombre, $desc, $material, $dimensiones, $medidas_json, $precio, $categoria_id, $id]);
                
                $pdo->prepare("DELETE FROM variantes WHERE producto_id = ?")->execute([$id]);
            }

            // --- VARIANTES ---
            $stmtVar = $pdo->prepare("INSERT INTO variantes (id, producto_id, talla, stock) VALUES (UUID(), ?, ?, ?)");
            
            if ($tipo_stock == 'unico') {
                $stock = $_POST['stock_unico_cant'] ?? 0;
                $stmtVar->execute([$id, 'ÚNICA', $stock]);
            } 
            elseif ($tipo_stock == 'ropa') {
                foreach (['S', 'M', 'L', 'XL'] as $t) {
                    $stock = $_POST['stock_'.strtolower($t)] ?? 0;
                    $stmtVar->execute([$id, $t, $stock]);
                }
            }
            elseif ($tipo_stock == 'calzado') {
                for ($i=38; $i<=44; $i++) {
                    $stock = $_POST['stock_zap_'.$i] ?? 0;
                    $stmtVar->execute([$id, (string)$i, $stock]);
                }
            }
            elseif ($tipo_stock == 'pantalones') {
                for ($i=28; $i<=36; $i+=2) {
                    $stock = $_POST['stock_pant_'.$i] ?? 0;
                    $stmtVar->execute([$id, (string)$i, $stock]);
                }
            }

            // --- FOTO ---
            if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
                $ext = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
                $nombre_archivo = $slug . '_' . time() . '.' . $ext;
                $ruta_db = "uploads/productos/" . $nombre_archivo;
                $ruta_fisica = "../uploads/productos/" . $nombre_archivo;

                if (!file_exists("../uploads/productos/")) mkdir("../uploads/productos/", 0777, true);

                if (move_uploaded_file($_FILES['foto']['tmp_name'], $ruta_fisica)) {
                    $stmtOld = $pdo->prepare("SELECT ruta FROM fotos WHERE producto_id = ? AND es_perfil = 1");
                    $stmtOld->execute([$id]);
                    $old = $stmtOld->fetch();
                    if ($old && file_exists("../".$old['ruta'])) unlink("../".$old['ruta']);
                    
                    if ($old) {
                        $pdo->prepare("UPDATE fotos SET ruta=?, nombre_archivo=? WHERE producto_id=? AND es_perfil=1")->execute([$ruta_db, $nombre_archivo, $id]);
                    } else {
                        $pdo->prepare("INSERT INTO fotos (tipo, producto_id, ruta, nombre_archivo, es_perfil) VALUES ('PRODUCTO', ?, ?, ?, 1)")->execute([$id, $ruta_db, $nombre_archivo]);
                    }
                }
            }

            header("Location: Aproductos.php?cat=" . $categoria_redir . "&mensaje=" . ($accion == 'crear_producto' ? 'creado' : 'actualizado'));
            exit();
        }

        // --- ELIMINAR ---
        if ($accion == 'eliminar_producto') {
            $id = $_POST['id'];
            
            $stmtCat = $pdo->prepare("SELECT categoria_id FROM productos WHERE id = ?");
            $stmtCat->execute([$id]);
            $prod = $stmtCat->fetch();
            $cat_redir = $prod ? $prod['categoria_id'] : 1;

            $stmt = $pdo->prepare("SELECT ruta FROM fotos WHERE producto_id = ?");
            $stmt->execute([$id]);
            while ($row = $stmt->fetch()) {
                if (file_exists("../".$row['ruta'])) unlink("../".$row['ruta']);
            }
            $pdo->prepare("DELETE FROM productos WHERE id = ?")->execute([$id]);
            
            header("Location: Aproductos.php?cat=" . $cat_redir . "&mensaje=eliminado");
            exit();
        }

    } catch (Exception $e) {
        header("Location: Aproductos.php?error=" . urlencode($e->getMessage()));
        exit();
    }
}