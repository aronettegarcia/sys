<?php
require 'config/conexion.php';
$q = trim($_GET['q'] ?? '');               // texto de búsqueda, vacío si no viene

$sql = 'SELECT id_producto, codigo, nombre, precio
        FROM productos
        WHERE activo = 1 AND nombre LIKE :q
        ORDER BY nombre';

$stmt = $pdo->prepare($sql);               // 1) preparar la plantilla
$stmt->execute([':q' => '%' . $q . '%']);  // 2) enviar los datos por separado
$productos = $stmt->fetchAll();            // 3) traer todas las filas
?>

<!doctype html>
<html lang="en" data-bs-theme="light">
    <head>
        <title>Modulo 1</title>
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
        <div class="container mt-3 d-flex justify-content-between align-items-center bg-light p-3 mb-3">
            <h1 class="m-0">Catálogo Digital</h1>
            <a href="crear.php" class="btn btn-success">Crear producto</a>

        </div>
        <div class="container mt-3">
                       
            <form method="get" class="d-flex mb-3">
                <input name="q" class="form-control me-2" value="<?= htmlspecialchars($q) ?>">
                <button class="btn btn-primary">Buscar</button>
            </form>
        </div>

        <div class="container">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>Codigo</th>
                        <th>Nombre</th>
                        <th>Precio</th>
                        <!-- 1. Agregamos esta cabecera para las acciones -->
                        <th class="text-center">Acciones</th> 
                </tr>
                </thead>
                
                <tbody>
                    <?php foreach ($productos as $p) : ?>
                        <tr>
                            <td><?= htmlspecialchars($p['codigo']) ?></td>
                            <td><?= htmlspecialchars($p['nombre']) ?></td>
                            <td class="text-end"><?= number_format($p['precio'], 2) ?></td>
                            
                            <!-- 2. Agregamos esta celda con el botón que envía el ID por la URL -->
                            <td class="text-center">
                            <a href="editar.php?id=<?= $p['id_producto'] ?>" class="btn btn-sm btn-outline-primary">
                            Editar
                            </a>
                            
                            <!-- Formulario seguro para Eliminar -->
                            <form action="eliminar.php" method="POST" class="d-inline" onsubmit="return confirm('¿Estás seguro de que deseas eliminar este producto?');">
                                <!-- Pasamos el ID del producto de forma oculta -->
                                <input type="hidden" name="id" value="<?= $p['id_producto'] ?>">
                                
                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                    Eliminar
                                </button>
                            </form>
                            </td>       
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
       
        <!-- Bootstrap JavaScript Bundle (includes Popper) -->
        <script
            src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
            integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI"
            crossorigin="anonymous"
        ></script>
    </body>
</html>
