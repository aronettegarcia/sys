<?php
// ==============================================================================
// TODO #1: INICIALIZACIÓN SEGURA DE SESIÓN
// Verifica con session_status() si la sesión aún no está activa (PHP_SESSION_NONE).
// Si no lo está, ejecútala con session_start().
// ==============================================================================
// [ESCRIBE TU CÓDIGO AQUÍ]

if (session_status()=== PHP_SESSION_NONE) {
    session_start();
}
/**
 * Verifica que exista un usuario autenticado en la sesión.
 */
function requerir_autenticacion(): void
{
    // ==========================================================================
    // TODO #2: GUARDIÁN DE AUTENTICACIÓN
    // Comprueba si NO existe $_SESSION['usuario_id'] usando isset().
    // Si no existe, redirige con header('Location: login.php?error=sesion_requerida')
    // y detén la ejecución del script con exit;.
    // ==========================================================================
    // [ESCRIBE TU CÓDIGO AQUÍ]
    if (!isset($_SESSION['usuario_id'])) {
        header('Location: login.php?error=sesion_requerida');
        exit; //detiene la ejecución del script por completo
    }
}

/**
 * Control de Acceso Basado en Roles (RBAC).
 * @param array $roles_permitidos Lista de roles autorizados, ej: ['admin']
 */
function requerir_rol(array $roles_permitidos = []): void
{
    // ==========================================================================
    // TODO #3: GUARDIÁN DE AUTORIZACIÓN POR ROL
    // 1. Llama primero a requerir_autenticacion() para asegurar que haya sesión.
    // 2. Obtén el rol actual desde $_SESSION['usuario_rol'] (usa '' si no existe).
    // 3. Si el rol NO está dentro de $roles_permitidos (usa !in_array()),
    //    redirige a 'index.php?error=acceso_denegado' y detén el script con exit;.
    // ==========================================================================
    // [ESCRIBE TU CÓDIGO AQUÍ]

    requerir_autenticacion();

    $rol_actual = $_SESSION['usuario_rol'] ?? '';

    if (!in_array($rol_actual, $roles_permitidos)) {
        header('Location: index.php?error=acceso_denegado');
        exit; //detiene la ejecución del script por completo
    }

    // Si el rol guardado en la sesión no coincide con los permitidos, denegamos el acceso
    if (!in_array($_SESSION['usuario_rol'], $roles_permitidos)) {
        header('Location: index.php?error=acceso_denegado');
        exit(); // Previene la ejecución no autorizada del backend
    }
}

/**
 * Función auxiliar para controlar elementos visuales en las vistas HTML.
 */
function es_admin(): bool
{
    // ==========================================================================
    // TODO #4: VERIFICACIÓN VISUAL DE ADMINISTRADOR
    // Retorna true si existe $_SESSION['usuario_rol'] y su valor es exactamente 'admin'.
    // Caso contrario, retorna false.
    // ==========================================================================
    return isset($_SESSION['usuario_rol']) && $_SESSION['usuario_rol'] === 'admin';
    //return false; // <-- [SUSTITUYE ESTA LÍNEA CON TU LÓGICA]
    
}

