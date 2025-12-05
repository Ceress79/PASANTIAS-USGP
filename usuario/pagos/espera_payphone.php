<?php
if (!isset($_GET['order']) || !isset($_GET['tx'])) { header("Location: ../../index.php"); exit(); }
$order_id = $_GET['order'];
$tx_id = $_GET['tx'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Esperando Confirmación</title>
    <link rel="stylesheet" href="../../style/css/main-style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
    <style>
        .wait-box { max-width: 500px; margin: 80px auto; text-align: center; padding: 40px; background: white; border-radius: 10px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); }
        .spinner { border: 5px solid #f3f3f3; border-top: 5px solid #ff6600; border-radius: 50%; width: 50px; height: 50px; animation: spin 1s linear infinite; margin: 20px auto; }
        @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
        .phone-anim { font-size: 4rem; color: #004a99; animation: pulse 2s infinite; }
        @keyframes pulse { 0% { transform: scale(1); } 50% { transform: scale(1.1); } 100% { transform: scale(1); } }
        .status-msg { font-weight: bold; margin-top: 20px; font-size: 1.2rem; }
    </style>
</head>
<body>

<div class="wait-box">
    <div class="phone-anim"><i class="fas fa-mobile-alt"></i></div>
    <h2>Revisa tu celular</h2>
    <p>Hemos enviado una solicitud de pago a tu App Payphone.</p>
    <div class="spinner"></div>
    <div id="estado" class="status-msg">Esperando confirmación...</div>
</div>

<script>
    const txId = "<?php echo $tx_id; ?>";
    const orderId = "<?php echo $order_id; ?>";

    // Consultar cada 3 segundos
    const intervalo = setInterval(() => {
        fetch(`verificar_pago.php?tx=${txId}&order=${orderId}`)
            .then(res => res.json())
            .then(data => {
                const estadoDiv = document.getElementById('estado');
                
                if (data.status === 'Approved') {
                    clearInterval(intervalo);
                    estadoDiv.style.color = 'green';
                    estadoDiv.innerHTML = "¡Pago Aprobado! Redirigiendo...";
                    // Redirigir al inicio o a página de éxito
                    setTimeout(() => { window.location.href = "../../index.php?exito=compra"; }, 2000);
                } 
                else if (data.status === 'Canceled' || data.status === 'Rejected') {
                    clearInterval(intervalo);
                    estadoDiv.style.color = 'red';
                    estadoDiv.innerHTML = "El pago fue cancelado o rechazado.";
                    setTimeout(() => { window.location.href = "pago_payphone.php?error=cancelado"; }, 3000);
                }
            })
            .catch(err => console.error(err));
    }, 3000);
</script>

</body>
</html>