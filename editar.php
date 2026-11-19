<?php
require 'config/conexion.php';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) { header('Location: index.php'); exit; }

// 1) Cargar el registro para precargar el formulario
$stmt = $pdo->prepare('SELECT * FROM productos WHERE id_producto = :id');
$stmt->execute([':id' => $id]);
$producto = $stmt->fetch();
if (!$producto) { die('Producto no encontrado.'); }

// 2) Al enviar, actualizar
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $sql = 'UPDATE productos SET nombre = :nom, precio = :pre WHERE id_producto = :id';
  $pdo->prepare($sql)->execute([
    ':nom' => trim($_POST['nombre']),
    ':pre' => (float) $_POST['precio'],
    ':id'  => $id
  ]);
  header('Location: index.php'); exit;
}
?>

<!doctype html>
<html lang="en" data-bs-theme="light">
    <head>
        <title>Title</title>
        <!-- Required meta tags -->
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />

        <!-- Bootstrap CSS v5.3.8 -->
        <link
            href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
            rel="stylesheet"
            integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB"
            crossorigin="anonymous"
        />
    </head>

    <body>
        <div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <!-- Tarjeta de Formulario -->
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white py-3">
                    <h5 class="card-title mb-0">Editar Producto ##<?= htmlspecialchars($producto['id_producto']) ?></h5>
                </div>
                <div class="card-body p-4">
                    
                    <form action="" method="POST" class="needs-validation" novalidate>
                        
                        <!-- Campo: Nombre -->
                        <div class="mb-3">
                            <label for="nombre" class="form-label fw-bold">Nombre del Producto</label>
                            <input type="text" 
                                   class="form-control" 
                                   id="nombre" 
                                   name="nombre" 
                                   value="<?= htmlspecialchars($producto['nombre']) ?>" 
                                   required>
                            <div class="invalid-feedback">Por favor, ingresa el nombre del producto.</div>
                        </div>

                        <!-- Campo: Precio -->
                        <div class="mb-4">
                            <label for="precio" class="form-label fw-bold">Precio ($)</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" 
                                       class="form-control" 
                                       id="precio" 
                                       name="precio" 
                                       step="0.01" 
                                       min="0" 
                                       value="<?= htmlspecialchars($producto['precio']) ?>" 
                                       required>
                                <div class="invalid-feedback">Por favor, ingresa un precio válido.</div>
                            </div>
                        </div>

                        <!-- Acciones -->
                        <div class="d-flex justify-content-end gap-2">
                            <a href="index.php" class="btn class="btn btn-outline-secondary">Cancelar</a>
                            <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                        </div>

                    </form>

                </div>
            </div>
            <!-- Fin Tarjeta -->
        </div>
    </div>
</div>    
    


        <script
            src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
            integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI"
            crossorigin="anonymous"
        ></script>
    </body>
</html>
