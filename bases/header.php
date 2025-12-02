<?php
// 1. CONTROL DE SESIÓN
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 2. DETECCIÓN DE RUTAS
$nivel = (strpos($_SERVER['PHP_SELF'], '/usuario/') !== false) ? '../' : './';
$pagina_actual = basename($_SERVER['SCRIPT_NAME']);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>USGP Tienda - Ropa y Accesorios</title>
    
    <link rel="stylesheet" href="<?php echo $nivel; ?>style/css/main-style.css">
    <link rel="stylesheet" href="<?php echo $nivel; ?>style/css/footer.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
</head>
<body>

    <header>
        <div class="top-bar">
            <div class="container">
                <a href="<?php echo $nivel; ?>index.php">
                    <img src="<?php echo $nivel; ?>style/img/logo.png" alt="Logo USGP" class="logo">
                </a>
            </div>
        </div>
        
        <nav class="main-nav">
            <div class="container">
                <ul>
                    <li><a href="<?php echo $nivel; ?>index.php" class="<?php echo ($pagina_actual == 'index.php') ? 'active' : ''; ?>">Portada</a></li>
                    <li><a href="<?php echo $nivel; ?>ropa_accesorio.php" class="<?php echo ($pagina_actual == 'ropa_accesorio.php') ? 'active' : ''; ?>">Ropa y accesorio</a></li>
                    <li><a href="<?php echo $nivel; ?>papeleria.php" class="<?php echo ($pagina_actual == 'papeleria.php') ? 'active' : ''; ?>">Papelería</a></li>
                    <li><a href="<?php echo $nivel; ?>hogar.php" class="<?php echo ($pagina_actual == 'hogar.php') ? 'active' : ''; ?>">Hogar</a></li>
                    <li><a href="<?php echo $nivel; ?>contacto.php" class="<?php echo ($pagina_actual == 'contacto.php') ? 'active' : ''; ?>">Contacto</a></li>
                </ul>

                <div class="nav-icons">

                    <label class="popup">
                        <input type="checkbox">
                        
                        <?php if (!isset($_SESSION['user_id'])): ?>
                            <span class="status-text">No iniciado</span>
                        <?php endif; ?>

                        <div class="burger" tabindex="0">
                            <i class="fas fa-user-circle"></i>
                        </div>
                        
                        <nav class="popup-window">
                            <?php if (isset($_SESSION['user_id'])): ?>
                                <legend>Mi Cuenta</legend>
                                <ul>
                                    <li>
                                        <button onclick="window.location.href='<?php echo $nivel; ?>usuario/perfil.php'">
                                            <svg stroke-linejoin="round" stroke-linecap="round" stroke-width="2" stroke="currentColor" fill="none" viewBox="0 0 24 24" height="14" width="14" xmlns="http://www.w3.org/2000/svg">
                                                <polygon points="16 3 21 8 8 21 3 21 3 16 16 3"></polygon>
                                            </svg>
                                            <span>Configuración</span>
                                        </button>
                                    </li>
                                    <hr>
                                    <li>
                                        <button onclick="window.location.href='<?php echo $nivel; ?>usuario/logout.php'">
                                            <svg stroke-linejoin="round" stroke-linecap="round" stroke-width="2" stroke="currentColor" fill="none" viewBox="0 0 24 24" height="14" width="14" xmlns="http://www.w3.org/2000/svg">
                                                <line y2="18" x2="6" y1="6" x1="18"></line>
                                                <line y2="18" x2="18" y1="6" x1="6"></line>
                                            </svg>
                                            <span>Cerrar Sesión</span>
                                        </button>
                                    </li>
                                </ul>
                            <?php else: ?>
                                <legend>Bienvenido</legend>
                                <ul>
                                    <li>
                                        <button onclick="window.location.href='<?php echo $nivel; ?>usuario/login.php'">
                                            <svg stroke-linejoin="round" stroke-linecap="round" stroke-width="2" stroke="currentColor" fill="none" viewBox="0 0 24 24" height="14" width="14" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"></path>
                                                <path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"></path>
                                            </svg>
                                            <span>Iniciar Sesión</span>
                                        </button>
                                    </li>
                                    <li>
                                        <button onclick="window.location.href='<?php echo $nivel; ?>usuario/registrar.php'">
                                            <svg stroke-linejoin="round" stroke-linecap="round" stroke-width="2" stroke="currentColor" fill="none" viewBox="0 0 24 24" height="14" width="14" xmlns="http://www.w3.org/2000/svg">
                                                <rect ry="2" rx="2" height="13" width="13" y="9" x="9"></rect>
                                                <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path>
                                            </svg>
                                            <span>Registrarse</span>
                                        </button>
                                    </li>
                                </ul>
                            <?php endif; ?>
                        </nav>
                    </label>

                    <a href="<?php echo $nivel; ?>carrito.php" class="cart-icon">
                        <i class="fas fa-shopping-cart"></i>
                        <span class="cart-count">0</span>
                    </a>

                </div>
            </div>
        </nav>

    </header>