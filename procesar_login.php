<?php
require_once 'config/conexion.php';
require_once 'includes/seguridad.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: login.php');
    exit;
}

// 1. Verificar token CSRF antes de tocar la base de datos
// (Asegúrate de que validarTokenCSRF() haga http_response_code(403) y die() por dentro)
validarTokenCSRF($_POST['csrf_token'] ?? null);

$email = trim($_POST['email'] ?? '');
$clave = $_POST['password'] ?? ''; // Coincide con name="password" de login.php

// 2. Consulta preparada por correo electrónico (ajustada a las columnas de tu login.php)
$stmt = $pdo->prepare("SELECT id, nombre, email, password_hash, rol FROM usuarios WHERE email = :email AND activo = 1 LIMIT 1");
$stmt->execute([':email' => $email]);
$usuario = $stmt->fetch();

// 3. Verificación criptográfica con Bcrypt
if ($usuario && password_verify($clave, $usuario['password_hash'])) {
    session_regenerate_id(true); // Previene Session Fixation
    $_SESSION['usuario_id']     = $usuario['id'];
    $_SESSION['usuario_nombre'] = $usuario['nombre'];
    $_SESSION['usuario_email']  = $usuario['email'];
    $_SESSION['usuario_rol']    = $usuario['rol'];

    header('Location: index.php'); // Cambia a dashboard.php si así se llama tu panel
    exit;
} else {
    header('Location: login.php?error=credenciales');
    exit;
}