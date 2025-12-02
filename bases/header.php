<?php
// 1. DETECCIÓN DE RUTAS (SISTEMA DE NIVELES)
// Lo ponemos al principio porque lo necesitamos para las redirecciones de timeout
$nivel = (strpos($_SERVER['PHP_SELF'], '/usuario/') !== false) ? '../' : './';

// 2. CONTROL DE SESIÓN
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 3. LÓGICA DE TIMEOUT (Cierre de sesión por inactividad)
// -------------------------------------------------------
$tiempo_limite = 1800; // 1800 segundos = 30 Minutos. (Cámbialo a 60 para probar rápido)

// Solo verificamos si el usuario ya está logueado
if (isset($_SESSION['user_id'])) {
    
    // Si existe un registro de la última vez que entró
    if (isset($_SESSION['ultimo_acceso'])) {
        $tiempo_transcurrido = time() - $_SESSION['ultimo_acceso'];

        // Si ha pasado más tiempo del permitido
        if ($tiempo_transcurrido > $tiempo_limite) {
            session_unset();     // Borra las variables
            session_destroy();   // Destruye la sesión
            
            // Redirige al login. Usamos $nivel para que la ruta siempre sea correcta
            header("Location: " . $nivel . "usuario/login.php?error=timeout");
            exit();
        }
    }
    
    // Si no ha expirado, actualizamos la hora al momento actual (REINICIA EL RELOJ)
    $_SESSION['ultimo_acceso'] = time();
}
// -------------------------------------------------------


// Obtener nombre de la página para la clase "active"
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
                
                <button class="mobile-toggle" id="btn-menu">
                    <i class="fas fa-bars"></i>
                </button>

                <ul id="main-menu">
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
                            <span class="status-text">Iniciar Sesion</span>
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

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const btnMenu = document.getElementById('btn-menu');
            const mainMenu = document.getElementById('main-menu');

            if(btnMenu && mainMenu) {
                btnMenu.addEventListener('click', function() {
                    mainMenu.classList.toggle('active');
                });
            }
        });
    </script>