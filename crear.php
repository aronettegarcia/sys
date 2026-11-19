<?php
require 'config/conexion.php';
$errores = []; // array para guardar los errores de validación
$categorias = []; // Array para almacenar las categorías de forma segura

// 1. OBTENER LAS CATEGORÍAS PRIMERO (Al principio del archivo para evitar romper el HTML)
try {
    // Intentamos traer las columnas más comunes. Si 'nombre' falla, capturamos el error abajo de forma limpia
    $sql_cat = 'SELECT * FROM categorias'; 
    $stmt_cat = $pdo->query($sql_cat);
    $categorias = $stmt_cat->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $errores[] = 'Error al cargar las categorías desde la base de datos: ' . $e->getMessage();
}

// 2. PROCESAR EL FORMULARIO MEDIANTE POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $codigo = trim($_POST['codigo'] ?? '');
    $nombre = trim($_POST['nombre'] ?? '');
    $precio = trim($_POST['precio'] ?? '');
    $id_categoria = trim($_POST['id_categoria'] ?? '');

    // Validación de los campos
    if ($codigo === '') {
        $errores[] = 'El código es obligatorio.';
    }
    if ($nombre === '') {
        $errores[] = 'El nombre es obligatorio.';
    }
    if ($precio === '' || !is_numeric($precio)) {
        $errores[] = 'El precio debe ser un número válido.';
    }
    if ($id_categoria === '') {
        $errores[] = 'La categoría es obligatoria.';
    }

    // Si no hay errores, insertar el producto en la base de datos
    if (empty($errores)) {
        try {
            $sql = 'INSERT INTO productos (codigo, nombre, precio, id_categoria, activo) VALUES (:codigo, :nombre, :precio, :id_categoria, 1)';
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':codigo' => $codigo,
                ':nombre' => $nombre,
                ':precio' => $precio,
                ':id_categoria' => $id_categoria,
            ]);

            // Redirigir a la página de listado después de insertar
            header('Location: index.php');
            exit;
        } catch (PDOException $e) {
            $errores[] = 'Error crítico al guardar el producto en la base de datos: ' . $e->getMessage();
        }
    }
}
?>

<!doctype html>
<html lang="en" data-bs-theme="light">
    <head>
        <title>Crear Producto</title>
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
        <h1 class="text-center mt-3">Crear producto</h1>
        <div class="container mt-3">
            
            <!-- Caja de alertas de Bootstrap -->
            <?php if (!empty($errores)) : ?>
                <div class="alert alert-danger">
                    <ul>
                        <?php foreach ($errores as $error) : ?>
                            <li><?= htmlspecialchars($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form method="post">
                <div class="mb-3">
                    <label for="codigo" class="form-label">Código</label>
                    <input type="text" class="form-control" id="codigo" name="codigo" value="<?= htmlspecialchars($_POST['codigo'] ?? '') ?>">
                </div>
                <div class="mb-3">
                    <label for="nombre" class="form-label">Nombre</label>
                    <input type="text" class="form-control" id="nombre" name="nombre" value="<?= htmlspecialchars($_POST['nombre'] ?? '') ?>">
                </div>
                <div class="mb-3">
                    <label for="precio" class="form-label">Precio</label>
                    <input type="text" class="form-control" id="precio" name="precio" value="<?= htmlspecialchars($_POST['precio'] ?? '') ?>">
                </div>
                            
                <div class="mb-3">
                    <label for="id_categoria" class="form-label">Categoría</label>
                    <select class="form-select" id="id_categoria" name="id_categoria">
                        <option value="">Seleccione una categoría</option>
                        
                        <?php foreach ($categorias as $categoria) : ?>
                            <?php
                            // Convierte todas las llaves a minúsculas por si la base de datos devuelve 'ID_CATEGORIA' o 'NOMBRE' en mayúsculas
                            $cat_limpia = array_change_key_case($categoria, CASE_LOWER);
                            
                            // Detecta inteligentemente si se llama 'id_categoria' o simplemente 'id'
                            $id_real = $cat_limpia['id_categoria'] ?? $cat_limpia['id'] ?? '';
                            // Detecta inteligentemente si se llama 'nombre' o 'nombre_categoria'
                            $nombre_real = $cat_limpia['nombre'] ?? $cat_limpia['nombre_categoria'] ?? 'Sin nombre';
                            
                            $selected = (isset($_POST['id_categoria']) && $_POST['id_categoria'] == $id_real) ? 'selected' : '';
                            ?>
                            <option value="<?= htmlspecialchars($id_real) ?>" <?= $selected ?>>
                                <?= htmlspecialchars($nombre_real) ?>
                            </option>
                        <?php endforeach; ?>

                    </select>
                </div>
                
                <button type="submit" class="btn btn-primary">Crear</button>
            </form>
        </div>

        <script
            src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
            integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI"
            crossorigin="anonymous"
        ></script>
    </body>
</html>