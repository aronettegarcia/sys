<?php
require 'config/conexion.php';

// CONSULTA 1: Métricas Globales (Agregación)
$sql_m1 = "SELECT 
            COUNT(id_cotizacion) AS total_cotizaciones,
            SUM(subtotal) AS venta_sin_iva,
            SUM(iva) AS total_iva_acumulado,
            SUM(total) AS gran_total,
            AVG(total) AS ticket_promedio
           FROM cotizaciones";
$metricas_globales = $pdo->query($sql_m1)->fetch();

// CONSULTA 2: Reporte de Ventas por Categoría (4 Tablas JOIN + GROUP BY)
$sql_m2 = "SELECT 
            cat.nombre AS categoria,
            COUNT(DISTINCT c.id_cotizacion) AS num_cotizaciones,
            SUM(dc.cantidad) AS unidades_vendidas,
            SUM(dc.cantidad * dc.precio_unitario) AS subtotal_categoria,
            SUM((dc.cantidad * dc.precio_unitario) * 0.13) AS iva_categoria,
            SUM((dc.cantidad * dc.precio_unitario) * 1.13) AS total_categoria
           FROM detalle_cotizacion dc
           INNER JOIN cotizaciones c ON dc.id_cotizacion = c.id_cotizacion
           INNER JOIN productos p ON dc.id_producto = p.id_producto
           INNER JOIN categorias cat ON p.id_categoria = cat.id_categoria
           GROUP BY cat.id_categoria, cat.nombre
           ORDER BY total_categoria DESC";
$reporte_categorias = $pdo->query($sql_m2)->fetchAll();

// CONSULTA 3: Top 5 Productos Más Cotizados
$sql_m3 = "SELECT 
            p.codigo,
            p.nombre AS producto,
            cat.nombre AS categoria,
            SUM(dc.cantidad) AS total_unidades,
            SUM(dc.cantidad * dc.precio_unitario) AS ingresos_generados
           FROM detalle_cotizacion dc
           INNER JOIN productos p ON dc.id_producto = p.id_producto
           INNER JOIN categorias cat ON p.id_categoria = cat.id_categoria
           GROUP BY p.id_producto, p.codigo, p.nombre, cat.nombre
           ORDER BY total_unidades DESC
           LIMIT 5";
$top_productos = $pdo->query($sql_m3)->fetchAll();
?>

<!doctype html>
<html lang="es" data-bs-theme="light">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>PYME-Core | Métricas de Negocio</title>
    <!-- Framework Visual Bootstrap 5.3.8 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" />
    <!-- Iconos de Bootstrap -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <!-- Librería html2pdf.js para exportar el DOM directamente a PDF -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>

    <style>
        /* Reglas CSS de impresión para ocultar botones al generar el documento */
        @media print {
            .no-imprimir {
                display: none !important;
            }
            body {
                background-color: #fff !important;
            }
            .card {
                border: 1px solid #ccc !important;
                box-shadow: none !important;
            }
        }
    </style>
</head>
<body class="bg-light">

    <div class="container py-4">
        <!-- Encabezado con Botón de Exportación -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 text-primary mb-1">Métricas de Negocio y Reportes Analíticos</h1>
                <small class="text-muted">Régimen Fiscal El Salvador (IVA 13%) — Reporte Comercial</small>
            </div>
            
            <!-- Zona de Acciones (Oculta en la descarga/impresión) -->
            <div class="no-imprimir">
                <!-- BOTÓN DE EXPORTACIÓN A PDF -->
                <button onclick="exportarPDF()" class="btn btn-danger me-2 shadow-sm">
                    <i class="bi bi-file-earmark-pdf-fill"></i> Descargar PDF
                </button>
                <a href="cotizador.php" class="btn btn-success me-2">Ir al Cotizador</a>
                <a href="index.php" class="btn btn-outline-secondary">Catálogo</a>
            </div>
        </div>

        <!-- ÁREA CAPTURADA PARA EL PDF (ID: area-pdf) -->
        <div id="area-pdf">
            
            <!-- TARJETAS DE RESUMEN (MÉTRICAS GLOBALES) -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card shadow-sm border-0 border-start border-primary border-4">
                        <div class="card-body">
                            <small class="text-muted fw-bold">Cotizaciones Emitidas</small>
                            <h3 class="card-title text-primary fw-bold mb-0">
                                <?= number_format($metricas_globales['total_cotizaciones'] ?? 0) ?>
                            </h3>
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="card shadow-sm border-0 border-start border-success border-4">
                        <div class="card-body">
                            <small class="text-muted fw-bold">Facturación Subtotal</small>
                            <h3 class="card-title text-success fw-bold mb-0">
                                $<?= number_format($metricas_globales['venta_sin_iva'] ?? 0, 2) ?>
                            </h3>
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="card shadow-sm border-0 border-start border-warning border-4">
                        <div class="card-body">
                            <small class="text-muted fw-bold">IVA Recaudado (13%)</small>
                            <h3 class="card-title text-warning fw-bold mb-0">
                                $<?= number_format($metricas_globales['total_iva_acumulado'] ?? 0, 2) ?>
                            </h3>
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="card shadow-sm border-0 border-start border-info border-4">
                        <div class="card-body">
                            <small class="text-muted fw-bold">Ticket Promedio</small>
                            <h3 class="card-title text-info fw-bold mb-0">
                                $<?= number_format($metricas_globales['ticket_promedio'] ?? 0, 2) ?>
                            </h3>
                        </div>
                    </div>
                </div>
            </div>

            <!-- TABLA 1: REPORTE POR CATEGORÍAS -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-dark text-white py-3">
                    <h5 class="card-title mb-0">1. Desglose Consolidado por Categoría de Producto</h5>
                </div>
                <div class="card-body p-0">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-secondary">
                            <tr>
                                <th>Categoría</th>
                                <th class="text-center">Cotizaciones</th>
                                <th class="text-center">Unidades Vendidas</th>
                                <th class="text-end">Subtotal ($)</th>
                                <th class="text-end">IVA 13% ($)</th>
                                <th class="text-end">Total ($)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($reporte_categorias as $cat): ?>
                                <tr>
                                    <td class="fw-bold"><?= htmlspecialchars($cat['categoria']) ?></td>
                                    <td class="text-center"><?= $cat['num_cotizaciones'] ?></td>
                                    <td class="text-center"><?= $cat['unidades_vendidas'] ?></td>
                                    <td class="text-end">$<?= number_format($cat['subtotal_categoria'], 2) ?></td>
                                    <td class="text-end text-muted">$<?= number_format($cat['iva_categoria'], 2) ?></td>
                                    <td class="text-end fw-bold text-success">$<?= number_format($cat['total_categoria'], 2) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- TABLA 2: TOP PRODUCTOS MÁS VENDIDOS -->
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white py-3">
                    <h5 class="card-title mb-0">2. Top 5 Productos Más Cotizados</h5>
                </div>
                <div class="card-body p-0">
                    <table class="table table-striped align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Código</th>
                                <th>Producto</th>
                                <th>Categoría</th>
                                <th class="text-center">Unidades Totales</th>
                                <th class="text-end">Ingresos Generados (Sin IVA)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($top_productos as $top): ?>
                                <tr>
                                    <td><code><?= htmlspecialchars($top['codigo']) ?></code></td>
                                    <td class="fw-bold"><?= htmlspecialchars($top['producto']) ?></td>
                                    <td><span class="badge bg-secondary"><?= htmlspecialchars($top['categoria']) ?></span></td>
                                    <td class="text-center fw-bold"><?= $top['total_unidades'] ?></td>
                                    <td class="text-end text-success fw-bold">$<?= number_format($top['ingresos_generados'], 2) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div> <!-- FIN ÁREA CAPTURADA PARA PDF -->
    </div>

    <!-- SCRIPT JAVASCRIPT DE CONFIGURACIÓN Y EXPORTACIÓN -->
    <script>
        function exportarPDF() {
            // 1. Seleccionar el contenedor HTML que deseamos convertir a PDF
            const elemento = document.getElementById('area-pdf');

            // 2. Configuración de calidad, márgenes y formato de salida
            const opciones = {
                margin:       [10, 10, 10, 10], // Márgenes: [Arriba, Izquierda, Abajo, Derecha] en mm
                filename:     'Reporte_Metricas_PYME.pdf',
                image:        { type: 'jpeg', quality: 0.98 },
                html2canvas:  { scale: 2, useCORS: true }, // Aumenta la resolución del renderizado
                jsPDF:        { unit: 'mm', format: 'letter', orientation: 'portrait' } // Formato Carta Vertical
            };

            // 3. Ejecución de la librería para compilar y descargar el archivo directamente
            html2pdf().set(opciones).from(elemento).save();
        }
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>