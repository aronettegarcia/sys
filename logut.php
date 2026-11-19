<?php
// login.php
require_once 'config/conexion.php';

// ==============================================================================
// TODO #1: REDIRECCIÓN SI YA ESTÁ LOGUEADO
// Si ya existe $_SESSION['usuario_id'], redirige directamente a 'index.php'
// y finaliza con exit;.
// ==============================================================================


if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // ==========================================================================
    // TODO #2: CAPTURA DE CREDENCIALES
    // Captura 'email' (aplicando trim) y 'password' desde $_POST.
    // Valida que ninguno de los dos esté vacío; si alguno falta, asigna un mensaje a $error.
    // ==========================================================================
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email !== '' && $password !== '') {
        try {
            // ==================================================================
            // TODO #3: CONSULTA PREPARADA PDO
            // 1. Prepara la consulta: SELECT * FROM usuarios WHERE email = :email AND activo = 1 LIMIT 1
            // 2. Ejecuta la consulta pasando ':email' => $email.
            // 3. Obtén el registro con fetch() y guárdalo en $usuario.
            // ==================================================================
            $sql = "SELECT * FROM usuarios WHERE email = :email AND activo = 1 LIMIT 1";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':email' => $email]);
            $usuario = $stmt->fetch();


            // ==================================================================
            // TODO #4: VERIFICACIÓN CRIPTOGRÁFICA Y CREACIÓN DE SESIÓN
            // 1. Valida que $usuario exista Y que password_verify($password, $usuario['password_hash']) sea true.
            // 2. Si es válido:
            //    a) Ejecuta session_regenerate_id(true) para prevenir fijación de sesión.
            //    b) Guarda en $_SESSION: 'usuario_id', 'usuario_nombre', 'usuario_email' y 'usuario_rol'.
            //    c) Redirige a 'index.php' y ejecuta exit;.
            // 3. Si falla, asigna a $error: 'Credenciales de acceso incorrectas o cuenta inactivada.'
            // ==================================================================
            // [ESCRIBE TU CÓDIGO AQUÍ]

        } catch (PDOException $e) {
            $error = 'Error del servidor al procesar el acceso.';
        }
    }
}
?>
<!doctype html>
<html lang="es" data-bs-theme="light">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>PYME-Core | Iniciar Sesión</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" />
</head>
<body class="bg-light d-flex align-items-center vh-100">

    <div class="container" style="max-width: 420px;">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-primary text-white text-center py-3">
                <h4 class="mb-0 fw-bold">PYME-Core</h4>
                <small>Control de Acceso al Sistema</small>
            </div>

            <div class="card-body p-4">
                <?php if ($error !== '') : ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?= htmlspecialchars($error) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?php if (isset($_GET['error']) && $_GET['error'] === 'sesion_requerida') : ?>
                    <div class="alert alert-warning small">Debes iniciar sesión para acceder al sistema.</div>
                <?php endif; ?>

                <?php if (isset($_GET['msj']) && $_GET['msj'] === 'sesion_cerrada') : ?>
                    <div class="alert alert-success small">Has cerrado sesión correctamente.</div>
                <?php endif; ?>

                <form method="POST" action="login.php">
                    <div class="mb-3">
                        <label for="email" class="form-label fw-semibold">Correo Electrónico</label>
                        <input type="email" class="form-control" id="email" name="email"
                               placeholder="admin@pyme.sv"
                               value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required autofocus />
                    </div>

                    <div class="mb-4">
                        <label for="password" class="form-label fw-semibold">Contraseña</label>
                        <input type="password" class="form-control" id="password" name="password"
                               placeholder="••••••••" required />
                    </div>

                    <button type="submit" class="btn btn-primary w-100 py-2 fw-bold">
                        Ingresar al Sistema
                    </button>
                </form>
            </div>

            <div class="card-footer bg-white text-center py-2 text-muted small">
                Módulo 2 — Autenticación y Roles
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>