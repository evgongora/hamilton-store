<?php
require_once __DIR__ . '/../../../backend/config/auth_guard.php';
requireRole(['admin', 'soporte', 'inventario']);
$basePath = dirname(dirname(dirname($_SERVER['SCRIPT_NAME'])));
if ($basePath === '/' || $basePath === '\\') {
    $basePath = '';
}
$pageTitle = 'Compras - M. Hamilton Store';
$currentPage = 'compras';
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
            <h1 class="mb-2">Registrar compra</h1>
            <p class="text-muted small mb-4">
                Compras registradas desde Oracle (<code>pkg_encabezados_compras</code>, <code>pkg_detalles_compras</code>).
            </p>

            <div class="row g-4">
                <div class="col-lg-7">
                    <div class="card mb-4">
                        <div class="card-header bg-dark text-white">
                            <h5 class="mb-0"><i class="bi bi-search me-2"></i>Buscar producto</h5>
                        </div>
                        <div class="card-body">
                            <input type="text" id="productSearch" class="form-control form-control-lg" placeholder="Nombre del producto..." autocomplete="off">
                            <div id="productResults" class="list-group mt-2" style="max-height: 200px; overflow-y: auto;"></div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                            <h5 class="mb-0"><i class="bi bi-box-seam me-2"></i>L&iacute;neas de compra</h5>
                            <span id="cartCount" class="badge bg-light text-dark">0</span>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Producto</th>
                                            <th class="text-end">Cant.</th>
                                            <th class="text-end">P. unit. (costo)</th>
                                            <th class="text-end">Subtotal</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody id="cartBody"></tbody>
                                    <tfoot class="table-light">
                                        <tr>
                                            <td colspan="3" class="fw-bold">Total</td>
                                            <td id="cartTotal" class="text-end fw-bold">₡0</td>
                                            <td></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                            <div id="cartEmpty" class="text-center text-muted py-4">No hay l&iacute;neas en esta compra</div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-5">
                    <div class="card mb-4">
                        <div class="card-header bg-dark text-white">
                            <h5 class="mb-0"><i class="bi bi-calendar3 me-2"></i>Fecha y proveedor</h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label for="fechaCompra" class="form-label">Fecha de compra</label>
                                <input type="date" id="fechaCompra" class="form-control">
                            </div>
                            <label for="proveedorSelect" class="form-label">Proveedor</label>
                            <select id="proveedorSelect" class="form-select">
                                <option value="">-- Cargando proveedores --</option>
                            </select>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-body">
                            <button type="button" id="btnConfirmarCompra" class="btn btn-success btn-lg w-100" disabled>
                                <i class="bi bi-check-circle me-2"></i>Registrar compra
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
    <script src="<?php echo htmlspecialchars($basePath); ?>/js/modules/compras.js"></script>
</body>
</html>
