<?php
require_once __DIR__ . '/../../../backend/config/auth_guard.php';
requireRole(['admin', 'soporte']);

$basePath = dirname(dirname(dirname($_SERVER['SCRIPT_NAME'])));
if ($basePath === '/' || $basePath === '\\') {
    $basePath = '';
}

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
<body class="app-layout bg-light">
    <?php include __DIR__ . '/../../components/navbar.php'; ?>
    <div class="app-main">
        <?php include __DIR__ . '/../../components/sidebar.php'; ?>
        <main class="app-content">
            <h1 class="mb-2">Reportes</h1>
            <p class="text-muted small mb-4">
                Ventas y cobros desde Oracle (<code>ventas_list.php</code>; registros en
                <code>pkg_encabezados_ventas</code>, <code>pkg_detalles_ventas</code> y <code>pkg_pagos</code>).
            </p>

            <div class="card shadow-sm border-0 mb-4">
                <div class="card-body">
                    <div class="row g-2 align-items-end">
                        <div class="col-md-3">
                            <label for="fechaDesde" class="form-label small text-muted mb-0">Desde</label>
                            <input type="date" class="form-control" id="fechaDesde">
                        </div>
                        <div class="col-md-3">
                            <label for="fechaHasta" class="form-label small text-muted mb-0">Hasta</label>
                            <input type="date" class="form-control" id="fechaHasta">
                        </div>
                        <div class="col-md-6">
                            <button type="button" class="btn btn-dark me-2" id="btnFiltrar">Aplicar filtro</button>
                            <button type="button" class="btn btn-outline-secondary" id="btnLimpiar">Limpiar fechas</button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-4">
                <div class="col-lg-6">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Ventas</h5>
                            <span class="fw-semibold" id="reporteVentasTotal">₡0</span>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover table-sm mb-0">
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
                                </table>
                            </div>
                            <div id="reporteVentasEmpty" class="text-center text-muted py-4" style="display: none;">
                                No hay ventas en el rango seleccionado.
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Cobros (pagado por venta)</h5>
                            <span class="fw-semibold" id="reportePagosTotal">₡0</span>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover table-sm mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Venta</th>
                                            <th>Fecha</th>
                                            <th class="text-end">Monto pagado</th>
                                        </tr>
                                    </thead>
                                    <tbody id="reportePagosBody"></tbody>
                                </table>
                            </div>
                            <div id="reportePagosEmpty" class="text-center text-muted py-4" style="display: none;">
                                No hay cobros en el rango seleccionado.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
    <?php include __DIR__ . '/../../components/footer.php'; ?>
    <?php include __DIR__ . '/../../components/scripts_bootstrap.php'; ?>
    <script src="<?php echo htmlspecialchars($basePath); ?>/js/services/api.js"></script>
    <script src="<?php echo htmlspecialchars($basePath); ?>/js/modules/reportes.js"></script>
    <script src="<?php echo htmlspecialchars($basePath); ?>/js/app.js"></script>
</body>
</html>
