<?php
session_start();
require_once "db/conexion.php";

// 🔒 Proteger esta página
if (!isset($_SESSION['admin_id'])) {
    header("Location: Alogin.php");
    exit();
}

// --- Definir el directorio de subida ---
$upload_dir = __DIR__ . '/../uploads/banners/'; // Ruta absoluta
if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0777, true); // Crear carpeta si no existe
}

// --- Lógica de Acciones ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion'])) {
    try {
        switch ($_POST['accion']) {

            // --- SUBIR NUEVO BANNER ---
            case 'subir':
                if (isset($_FILES['banner_img']) && $_FILES['banner_img']['error'] == 0) {

                    // Contar banners existentes
                    $stmt_count = $pdo->query("SELECT COUNT(*) FROM fotos WHERE tipo = 'BANNER'");
                    if ($stmt_count->fetchColumn() >= 6) {
                        header("Location: Abanners.php?error=Límite de 6 banners alcanzado.");
                        exit();
                    }

                    $file = $_FILES['banner_img'];
                    $file_name = basename($file['name']);
                    $file_tmp = $file['tmp_name'];
                    $file_size = $file['size'];
                    $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

                    // Validaciones
                    $allowed_ext = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                    if (!in_array($file_ext, $allowed_ext)) {
                        header("Location: Abanners.php?error=Tipo de archivo no permitido.");
                        exit();
                    }
                    if ($file_size > 5 * 1024 * 1024) { // 5MB Límite
                        header("Location: Abanners.php?error=El archivo es demasiado grande (Máx 5MB).");
                        exit();
                    }

                    // Crear nombre único y rutas
                    $new_file_name = uniqid('banner_') . '.' . $file_ext;
                    $upload_path = $upload_dir . $new_file_name;
                    $db_path = 'uploads/banners/' . $new_file_name; // Ruta visible desde frontend y admin

                    if (move_uploaded_file($file_tmp, $upload_path)) {
                        // Insertar en BD
                        $stmt = $pdo->prepare("INSERT INTO fotos (tipo, ruta, nombre_archivo, activo) VALUES ('BANNER', ?, ?, 1)");
                        $stmt->execute([$db_path, $file_name]);
                        header("Location: Abanners.php?exito=Banner subido correctamente.");
                    } else {
                        header("Location: Abanners.php?error=Error al mover el archivo.");
                    }
                } else {
                    header("Location: Abanners.php?error=No se recibió ningún archivo o hubo un error.");
                }
                exit();

            // --- ACTIVAR / DESACTIVAR BANNER ---
            case 'toggle':
                $foto_id = $_POST['foto_id'];
                $stmt = $pdo->prepare("UPDATE fotos SET activo = NOT activo WHERE id = ? AND tipo = 'BANNER'");
                $stmt->execute([$foto_id]);
                header("Location: Abanners.php?exito=Visibilidad cambiada.");
                exit();

            // --- ELIMINAR BANNER ---
            case 'eliminar':
                $foto_id = $_POST['foto_id'];

                // Obtener ruta del archivo
                $stmt = $pdo->prepare("SELECT ruta FROM fotos WHERE id = ? AND tipo = 'BANNER'");
                $stmt->execute([$foto_id]);
                $foto = $stmt->fetch();

                if ($foto) {
                    $file_path = __DIR__ . '/../' . $foto['ruta'];
                    if (file_exists($file_path)) {
                        unlink($file_path);
                    }
                }

                // Eliminar registro de BD
                $stmt_delete = $pdo->prepare("DELETE FROM fotos WHERE id = ?");
                $stmt_delete->execute([$foto_id]);

                header("Location: Abanners.php?exito=Banner eliminado.");
                exit();
        }

    } catch (PDOException $e) {
        header("Location: Abanners.php?error=Error de base de datos: " . urlencode($e->getMessage()));
        exit();
    }
}

// Acceso directo sin POST → redirigir
header("Location: Abanners.php");
exit();
