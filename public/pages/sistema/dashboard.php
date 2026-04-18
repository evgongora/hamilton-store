<?php
/**
 * dashboard.php — Panel principal con resumen operativo.
 */
require_once __DIR__ . '/../../../backend/config/auth_guard.php';
requireRole(['admin', 'soporte']);

$basePath = dirname(dirname(dirname($_SERVER['SCRIPT_NAME'])));
if ($basePath === '/' || $basePath === '\\') {
    $basePath = '';
}

$logoutUrl = dirname($basePath) . '/backend/api/auth_logout.php';
$pageTitle = 'Dashboard - M. Hamilton Store';
$currentPage = 'dashboard';
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
            <h1 class="mb-2">Dashboard</h1>
            <p class="text-muted mb-2">
                Bienvenido, <strong><?php echo htmlspecialchars($user); ?></strong>.
                Rol: <strong><?php echo htmlspecialchars($role); ?></strong>.
            </p>
            <p class="text-muted small mb-4">
                Métricas desde Oracle (ventas y cobros acumulados con la misma base que <code>pkg_encabezados_ventas</code> y <code>pkg_pagos</code>).
            </p>
            <p id="dashApiNote" class="alert alert-warning py-2 small d-none mb-4" role="status"></p>

            <div class="row g-3 mb-4">
                <div class="col-sm-6 col-xl-3">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <div class="text-muted small">Total ventas (suma)</div>
                            <div class="h4 mb-0" id="dashTotalVentas">—</div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-xl-3">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <div class="text-muted small">Total cobrado (pagos)</div>
                            <div class="h4 mb-0" id="dashTotalPagos">—</div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-xl-3">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <div class="text-muted small">Clientes</div>
                            <div class="h4 mb-0" id="dashClientes">—</div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-xl-3">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <div class="text-muted small">Productos en catálogo</div>
                            <div class="h4 mb-0" id="dashProductos">—</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom py-3">
                    <h5 class="mb-0"><i class="bi bi-receipt me-2"></i>Últimas ventas</h5>
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
                            <tbody id="dashVentasBody"></tbody>
                        </table>
                    </div>
                    <div id="dashVentasEmpty" class="text-center text-muted py-5" style="display: none;">
                        No hay ventas registradas.
                    </div>
                </div>
            </div>
        </main>
    </div>
    <?php include __DIR__ . '/../../components/footer.php'; ?>
    <?php include __DIR__ . '/../../components/scripts_bootstrap.php'; ?>
    <script src="<?php echo htmlspecialchars($basePath); ?>/js/services/api.js"></script>
    <script src="<?php echo htmlspecialchars($basePath); ?>/js/modules/dashboard.js"></script>
    <script src="<?php echo htmlspecialchars($basePath); ?>/js/app.js"></script>
</body>
</html>
