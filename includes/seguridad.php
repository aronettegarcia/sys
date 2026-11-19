<?php
// Iniciamos la sesión solo si no hay una activa
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Genera o devuelve el token CSRF actual de la sesión
 */
function generarTokenCSRF(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Valida que el token recibido coincida con el de la sesión
 */
function validarTokenCSRF(?string $tokenRecibido): void {
    if (
        empty($_SESSION['csrf_token']) || 
        empty($tokenRecibido) || 
        !hash_equals($_SESSION['csrf_token'], $tokenRecibido)
    ) {
        http_response_code(403);
        die('Error de seguridad (403): Token CSRF inválido o expirado.');
    }
}

/**
 * Protege las vistas privadas contra accesos no autenticados
 */
function verificarSesion(): void {
    if (!isset($_SESSION['usuario_id'])) {
        header('Location: login.php?error=no_autenticado');
        exit;
    }
}
