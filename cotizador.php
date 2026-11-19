<?php
require 'config/conexion.php'; // Usa la conexión centralizada

$mensaje = '';
$error = '';

// 1. CARGAR CATÁLOGO DE PRODUCTOS ACTIVOS
$stmt_prod = $pdo->query("SELECT p.id_producto, p.codigo, p.nombre, p.precio, c.nombre AS categoria 
                          FROM productos p 
                          INNER JOIN categorias c ON p.id_categoria = c.id_categoria 
                          WHERE p.activo = 1 
                          ORDER BY p.nombre ASC");
$productos = $stmt_prod->fetchAll();

// 2. PROCESAR EL FORMULARIO DE COTIZACIÓN
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['procesar_cotizacion'])) {
    $cliente = trim($_POST['cliente'] ?? '');
    $productos_seleccionados = $_POST['productos'] ?? []; // Arreglo [id_producto => cantidad]

    if (empty($cliente)) {
        $error = "El nombre del cliente es obligatorio.";
    } elseif (empty($productos_seleccionados)) {
        $error = "Debe seleccionar al menos un producto para cotizar.";
    } else {
        try {
            // INICIO DE LA TRANSACCIÓN PDO (Garantiza atomicidad)
            $pdo->beginTransaction();

            // Calcular Subtotal recorriendo los productos e ítems seleccionados
            $subtotal_acumulado = 0;
            $items_a_insertar = [];

            foreach ($productos_seleccionados as $id_prod => $cantidad) {
                $cantidad = (int)$cantidad;
                if ($cantidad > 0) {
                    // Buscar el precio unitario oficial en la base de datos
                    $stmt_p = $pdo->prepare("SELECT precio FROM productos WHERE id_producto = :id AND activo = 1");
                    $stmt_p->execute([':id' => $id_prod]);
                    $prod_db = $stmt_p->fetch();

                    if ($prod_db) {
                        $precio_u = (float)$prod_db['precio'];
                        $subtotal_acumulado += ($precio_u * $cantidad);
                        
                        $items_a_insertar[] = [
                            'id_producto' => $id_prod,
                            'cantidad' => $cantidad,
                            'precio_unitario' => $precio_u
                        ];
                    }
                }
            }

            if (empty($items_a_insertar)) {
                throw new Exception("Debe ingresar cantidades mayores a cero.");
            }

            // Cálculos fiscales (El Salvador: IVA 13%)
            $iva = $subtotal_acumulado * 0.13;
            $total = $subtotal_acumulado + $iva;

            // Step A: Insertar Encabezado de Cotización
            $sql_cot = "INSERT INTO cotizaciones (cliente, subtotal, iva, total) VALUES (:cliente, :subtotal, :iva, :total)";
            $stmt_cot = $pdo->prepare($sql_cot);
            $stmt_cot->execute([
                ':cliente' => $cliente,
                ':subtotal' => $subtotal_acumulado,
                ':iva' => $iva,
                ':total' => $total
            ]);

            // Obtener el ID recién generado
            $id_cotizacion = $pdo->lastInsertId();

            // Step B: Insertar Detalle de Cotización
            $sql_det = "INSERT INTO detalle_cotizacion (id_cotizacion, id_producto, cantidad, precio_unitario) 
                        VALUES (:id_cotizacion, :id_producto, :cantidad, :precio_unitario)";
            $stmt_det = $pdo->prepare($sql_det);

            foreach ($items_a_insertar as $item) {
                $stmt_det->execute([
                    ':id_cotizacion' => $id_cotizacion,
                    ':id_producto'   => $item['id_producto'],
                    ':cantidad'      => $item['cantidad'],
                    ':precio_unitario' => $item['precio_unitario']
                ]);
            }

            // SI TODO SALIÓ BIEN, CONFIRMAR TRANSACCIÓN
            $pdo->commit();
            $mensaje = "Cotización #{$id_cotizacion} registrada exitosamente por un total de $" . number_format($total, 2);

        } catch (Exception $e) {
            // SI ALGO FALLA, DESHACER TODOS LOS CAMBIOS
            $pdo->rollBack();
            $error = "Error al procesar la cotización: " . $e->getMessage();
        }
    }
}
?>

<!doctype html>
<html lang="es" data-bs-theme="light">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>PYME-Core | Cotizador Automático</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" />
</head>
<body class="bg-light">

    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 text-primary">Cotizador Automático de Ventas</h1>
            <a href="index.php" class="btn btn-outline-secondary">Volver al Catálogo</a>
        </div>

        <?php if ($mensaje): ?>
            <div class="alert alert-success alert-dismissible fade show"><?= htmlspecialchars($mensaje) ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="alert alert-danger alert-dismissible fade show"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" action="cotizador.php">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="card-title mb-0">1. Datos del Cliente</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="cliente" class="form-label fw-bold">Nombre o Razon Social del Cliente</label>
                        <input type="text" name="cliente" id="cliente" class="form-control" placeholder="Ej. Ferretería Cuscatlán S.A. de C.V." required>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm mb-4">
                <div class="card-header bg-dark text-white">
                    <h5 class="card-title mb-0">2. Seleccionar Productos</h5>
                </div>
                <div class="card-body p-0">
                    <table class="table table-striped align-middle mb-0">
                        <thead class="table-secondary">
                            <tr>
                                <th>Código</th>
                                <th>Producto</th>
                                <th>Categoría</th>
                                <th class="text-end">Precio Unit. ($)</th>
                                <th style="width: 150px;" class="text-center">Cantidad</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($productos as $p): ?>
                                <tr>
                                    <td><code><?= htmlspecialchars($p['codigo']) ?></code></td>
                                    <td class="fw-bold"><?= htmlspecialchars($p['nombre']) ?></td>
                                    <td><span class="badge bg-info text-dark"><?= htmlspecialchars($p['categoria']) ?></span></td>
                                    <td class="text-end">$<?= number_format($p['precio'], 2) ?></td>
                                    <td>
                                        <input type="number" name="productos[<?= $p['id_producto'] ?>]" value="0" min="0" class="form-control form-control-sm text-center">
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <button type="submit" name="procesar_cotizacion" class="btn btn-success btn-lg w-100 shadow">
                Generar Cotización (Con IVA 13%)
            </button>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>