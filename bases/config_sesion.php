<?php
// Configuración para mantener la sesión activa por 30 días

if (session_status() === PHP_SESSION_NONE) {
    // Duración de 30 días en segundos
    $duracion = 60 * 60 * 24 * 30; 
    
    ini_set('session.gc_maxlifetime', $duracion);
    
    session_set_cookie_params([
        'lifetime' => $duracion,
        'path' => '/',
        'domain' => '',
        'secure' => false, // Cambiar a true si usas HTTPS
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
    
    session_start();
}
?>