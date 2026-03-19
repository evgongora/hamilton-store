<?php
require_once __DIR__ . '/../../../backend/config/auth_guard.php';
requireRole(['admin']);
$basePath = dirname(dirname(dirname($_SERVER['SCRIPT_NAME'])));
if ($basePath === '/' || $basePath === '\\') $basePath = '';
$pageTitle = 'Reportes - M. Hamilton Store';
$currentPage = 'reportes';
$user = $_SESSION['user'] ?? '';
$role = $_SESSION['role'] ?? '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <?php include __DIR__ . '/../../components/head.php'; ?>
</head>
<body class="app-layout bg-light" data-base-path="<?php echo htmlspecialchars($basePath); ?>">
    <?php include __DIR__ . '/../../components/navbar.php'; ?>
    <div class="app-main">
        <?php include __DIR__ . '/../../components/sidebar.php'; ?>
        <main class="app-content">
            <h1 class="mb-4">Reportes</h1>

            <div class="card mb-4">
                <div class="card-header bg-dark text-white">
                    <h5 class="mb-0"><i class="bi bi-funnel me-2"></i>Filtros</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-3">
                            <label for="fechaDesde" class="form-label">Desde</label>
                            <input type="date" class="form-control" id="fechaDesde">
                        </div>
                        <div class="col-md-3">
                            <label for="fechaHasta" class="form-label">Hasta</label>
                            <input type="date" class="form-control" id="fechaHasta">
                        </div>
                        <div class="col-md-4">
                            <button type="button" class="btn btn-primary" id="btnFiltrar">
                                <i class="bi bi-search me-1"></i>Filtrar
                            </button>
                            <button type="button" class="btn btn-outline-secondary ms-2" id="btnLimpiar">
                                <i class="bi bi-x-lg me-1"></i>Limpiar
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header bg-dark text-white">
                            <h5 class="mb-0"><i class="bi bi-receipt me-2"></i>Ventas</h5>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>ID</th>
                                            <th>Fecha</th>
                                            <th>Cliente</th>
                                            <th>Origen</th>
                                            <th class="text-end">Total</th>
                                        </tr>
                                    </thead>
                                    <tbody id="reporteVentasBody"></tbody>
                                    <tfoot class="table-light">
                                        <tr>
                                            <td colspan="4" class="fw-bold">Total ventas (filtradas)</td>
                                            <td id="reporteVentasTotal" class="text-end fw-bold">₡0</td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                            <div id="reporteVentasEmpty" class="text-center text-muted py-5">
                                <i class="bi bi-receipt display-4"></i>
                                <p class="mt-2">No hay ventas en el rango seleccionado</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12">
                    <div class="card">
                        <div class="card-header bg-dark text-white">
                            <h5 class="mb-0"><i class="bi bi-credit-card me-2"></i>Pagos</h5>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Venta #</th>
                                            <th>Fecha pago</th>
                                            <th>Monto</th>
                                        </tr>
                                    </thead>
                                    <tbody id="reportePagosBody"></tbody>
                                    <tfoot class="table-light">
                                        <tr>
                                            <td colspan="2" class="fw-bold">Total pagos (filtrados)</td>
                                            <td id="reportePagosTotal" class="fw-bold">₡0</td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                            <div id="reportePagosEmpty" class="text-center text-muted py-5">
                                <i class="bi bi-credit-card display-4"></i>
                                <p class="mt-2">No hay pagos en el rango seleccionado</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
    <?php include __DIR__ . '/../../components/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?php echo htmlspecialchars($basePath); ?>/js/app.js"></script>
    <script src="<?php echo htmlspecialchars($basePath); ?>/js/modules/reportes.js"></script>
</body>
</html>
