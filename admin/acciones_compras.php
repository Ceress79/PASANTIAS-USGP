<?php
session_start();
require_once "db/conexion.php";

if (!isset($_SESSION['admin_id'])) { header("Location: Alogin.php"); exit(); }

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['order_id'])) {
    $id = $_POST['order_id'];
    $estado = $_POST['nuevo_estado'];

    $stmt = $pdo->prepare("UPDATE orders SET estado = ? WHERE id = ?");
    $stmt->execute([$estado, $id]);

    header("Location: Adetalle_compra.php?id=" . $id);
    exit();
}
header("Location: Acompras.php");