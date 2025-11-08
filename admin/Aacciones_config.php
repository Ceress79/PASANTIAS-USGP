<?php
session_start();
require_once "db/conexion.php";

if (!isset($_SESSION['admin_id'])) {
    header("Location: Alogin.php");
    exit();
}

$upload_dir = __DIR__ . '/../uploads/site/';
if (!file_exists($upload_dir)) mkdir($upload_dir, 0777, true);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion'])) {

    // --- GUARDAR/EDITAR ---
    if ($_POST['accion'] === 'guardar_nosotros') {
        try {
            $about_text = trim($_POST['about_text']);

            // Actualiza texto
            $stmt_text = $pdo->prepare("UPDATE configuracion SET valor = ? WHERE clave = 'about_us_text'");
            $stmt_text->execute([$about_text]);

            // Imagen nueva
            if (isset($_FILES['about_image']) && $_FILES['about_image']['error'] === 0) {
                $file = $_FILES['about_image'];
                $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                $allowed = ['jpg', 'jpeg', 'png', 'webp'];

                if (!in_array($ext, $allowed)) {
                    header("Location: configuracion.php?error=Formato de imagen no permitido.");
                    exit();
                }
                if ($file['size'] > 3 * 1024 * 1024) {
                    header("Location: configuracion.php?error=La imagen supera los 3MB.");
                    exit();
                }

                $new_name = 'about_us_' . time() . '.' . $ext;
                $upload_path = $upload_dir . $new_name;
                $db_path = 'uploads/site/' . $new_name;

                if (move_uploaded_file($file['tmp_name'], $upload_path)) {
                    // Borra imagen anterior
                    $old_path = $pdo->query("SELECT valor FROM configuracion WHERE clave='about_us_image'")->fetchColumn();
                    if ($old_path && $old_path !== 'uploads/site/default_about.png' && file_exists(__DIR__ . '/../' . $old_path)) {
                        unlink(__DIR__ . '/../' . $old_path);
                    }

                    $stmt = $pdo->prepare("UPDATE configuracion SET valor=? WHERE clave='about_us_image'");
                    $stmt->execute([$db_path]);
                }
            }

            header("Location: configuracion.php?exito=Sección 'Nosotros' actualizada correctamente.");
            exit();

        } catch (PDOException $e) {
            header("Location: configuracion.php?error=" . urlencode($e->getMessage()));
            exit();
        }
    }

    // --- ELIMINAR ---
    if ($_POST['accion'] === 'eliminar_nosotros') {
        $pdo->prepare("UPDATE configuracion SET valor='' WHERE clave='about_us_text'")->execute();
        header("Location: configuracion.php?exito=Texto eliminado correctamente.");
        exit();
    }
}

header("Location: configuracion.php");
exit();
