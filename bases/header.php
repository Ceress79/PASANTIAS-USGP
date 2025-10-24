<?php
$pagina_actual = basename($_SERVER['SCRIPT_NAME']);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>USGP Tienda - Ropa y Accesorios</title>
    <link rel="stylesheet" href="style/css/main-style.css">
    <link rel="stylesheet" href="style/css/footer.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
</head>
<body>

    <header>
        <div class="top-bar">
            <div class="container">
                <a href="index.php">
                    <img src="style/img/logo.png" alt="Logo USGP" class="logo">
                </a>
            </div>
        </div>
        <nav class="main-nav">
            <div class="container">
                <ul>
                    <li><a href="index.php" class="<?php echo ($pagina_actual == 'index.php') ? 'active' : ''; ?>">Portada</a></li>
                    <li><a href="ropa_accesorio.php" class="<?php echo ($pagina_actual == 'ropa_accesorio.php') ? 'active' : ''; ?>">Ropa y accesorio</a></li>
                    <li><a href="papeleria.php" class="<?php echo ($pagina_actual == 'papeleria.php') ? 'active' : ''; ?>">Papeleria</a></li>
                    <li><a href="hogar.php" class="<?php echo ($pagina_actual == 'hogar.php') ? 'active' : ''; ?>">Hogar</a></li>
                    <li><a href="contacto.php" class="<?php echo ($pagina_actual == 'contacto.php') ? 'active' : ''; ?>">Contacto</a></li>
                </ul>
                <a href="carrito.php" class="cart-icon">
                    <i class="fas fa-shopping-cart"></i>
                    <span class="cart-count">0</span>
                </a>
            </div>
        </nav>
    </header>