<?php
// Usamos __DIR__ para garantizar que la ruta sea relativa a ESTE archivo,
// evitando errores si el script se llama desde otro contexto.
require_once __DIR__ . "/../../admin/db/conexion.php";
require_once __DIR__ . "/../../admin/db/config_payphone.php";

header('Content-Type: application/json');

if (!isset($_GET['tx']) || !isset($_GET['order'])) {
    echo json_encode(['status' => 'Error']);
    exit();
}

$txId = $_GET['tx'];
$orderId = $_GET['order'];

// 1. Consultar Payphone
$ch = curl_init(URL_PAYPHONE_STATUS . '/' . $txId);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . PAYPHONE_TOKEN,
    'Content-Type: application/json'
]);

$response = curl_exec($ch);
curl_close($ch);
$result = json_decode($response, true);

$status = $result['transactionStatus'] ?? 'Pending';

// 2. Actualizar BD si cambió el estado
if ($status === 'Approved') {
    $stmt = $pdo->prepare("UPDATE orders SET estado = 'PAGADO', actualizado_en = NOW() WHERE id = ?");
    $stmt->execute([$orderId]);
    
    // Iniciamos sesión solo si es necesario para limpiar el carrito
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    unset($_SESSION['carrito']);
} 
elseif ($status === 'Canceled') {
    $stmt = $pdo->prepare("UPDATE orders SET estado = 'CANCELADO' WHERE id = ?");
    $stmt->execute([$orderId]);
}

echo json_encode(['status' => $status]);
?>