<?php
require_once __DIR__ . '/../../../backend/config/auth_guard.php';
requireRole(['admin', 'soporte', 'cajero']);
$basePath = dirname(dirname(dirname($_SERVER['SCRIPT_NAME'])));
if ($basePath === '/' || $basePath === '\\') $basePath = '';
$pageTitle = 'Pagos - M. Hamilton Store';
$currentPage = 'pagos';
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
            <h1 class="mb-2">Registrar pago</h1>
            <p class="text-muted small mb-4">
                Pagos registrados desde Oracle (<code>pkg_pagos</code>). Métodos de pago desde <code>pkg_metodos_pago</code> (listado).
            </p>

            <div class="row g-4">
                <div class="col-lg-6">
                    <div class="card mb-4">
                        <div class="card-header bg-dark text-white">
                            <h5 class="mb-0"><i class="bi bi-receipt me-2"></i>Seleccionar venta</h5>
                        </div>
                        <div class="card-body">
                            <label for="ventaFiltro" class="form-label small text-muted">Filtrar ventas con saldo</label>
                            <input type="search" id="ventaFiltro" class="form-control form-control-sm mb-2" placeholder="ID o nombre de cliente…" autocomplete="off">
                            <select id="ventaSelect" class="form-select form-select-lg">
                                <option value="">-- Seleccionar venta --</option>
                            </select>
                            <div id="ventaDetalle" class="mt-3 p-3 bg-light rounded" style="display: none;">
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Total venta:</span>
                                    <strong id="ventaTotal">₡0</strong>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Pagado:</span>
                                    <strong id="ventaPagado" class="text-success">₡0</strong>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span>Pendiente:</span>
                                    <strong id="ventaPendiente" class="text-danger">₡0</strong>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="card mb-4">
                        <div class="card-header bg-dark text-white">
                            <h5 class="mb-0"><i class="bi bi-credit-card me-2"></i>Nuevo pago</h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label for="metodoPago" class="form-label">M&eacute;todo de pago</label>
                                <select id="metodoPago" class="form-select">
                                    <option value="">-- Cargando... --</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="montoPago" class="form-label">Monto</label>
                                <input type="number" id="montoPago" class="form-control form-control-lg" placeholder="0.00" min="0" step="0.01">
                            </div>
                            <button type="button" id="btnRegistrarPago" class="btn btn-success btn-lg w-100" disabled>
                                <i class="bi bi-check-circle me-2"></i>Registrar pago
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
    <?php include __DIR__ . '/../../components/footer.php'; ?>
    <?php include __DIR__ . '/../../components/scripts_bootstrap.php'; ?>
    <script src="<?php echo htmlspecialchars($basePath); ?>/js/services/api.js"></script>
    <script src="<?php echo htmlspecialchars($basePath); ?>/js/utils/validation-helpers.js"></script>
    <script src="<?php echo htmlspecialchars($basePath); ?>/js/app.js"></script>
    <script src="<?php echo htmlspecialchars($basePath); ?>/js/modules/pagos.js"></script>
</body>
</html>
