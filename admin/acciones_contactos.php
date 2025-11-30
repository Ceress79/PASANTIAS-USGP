<?php
session_start();
require_once "db/conexion.php";

if (!isset($_SESSION['admin_id'])) {
    header("Location: Alogin.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $accion = $_POST['accion'] ?? '';

    // CREAR
    if ($accion === 'crear_contacto') {
        $tipo = $_POST['tipo'];
        $valor = trim($_POST['valor']);

        if (!empty($tipo) && !empty($valor)) {
            $stmt = $pdo->prepare("INSERT INTO contactos (tipo, valor) VALUES (?, ?)");
            $stmt->execute([$tipo, $valor]);
            header("Location: Acontactos.php?mensaje=creado");
        } else {
            header("Location: Acontactos.php?error=vacio");
        }
    }

    // EDITAR
    elseif ($accion === 'editar_contacto') {
        $id = $_POST['id'];
        $tipo = $_POST['tipo'];
        $valor = trim($_POST['valor']);

        if (!empty($id) && !empty($tipo) && !empty($valor)) {
            $stmt = $pdo->prepare("UPDATE contactos SET tipo = ?, valor = ? WHERE id = ?");
            $stmt->execute([$tipo, $valor, $id]);
            header("Location: Acontactos.php?mensaje=actualizado");
        }
    }

    // ELIMINAR
    elseif ($accion === 'eliminar_contacto') {
        $id = $_POST['id'];
        if (!empty($id)) {
            $stmt = $pdo->prepare("DELETE FROM contactos WHERE id = ?");
            $stmt->execute([$id]);
            header("Location: Acontactos.php?mensaje=eliminado");
        }
    }
    
    exit();
}