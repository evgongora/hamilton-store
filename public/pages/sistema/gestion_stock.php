<?php
require_once __DIR__ . '/../../../backend/config/auth_guard.php';
requireRole(['admin', 'soporte', 'inventario']);

$basePath = dirname(dirname(dirname($_SERVER['SCRIPT_NAME'])));
if ($basePath === '/' || $basePath === '\\') {
    $basePath = '';
}

$pageTitle = 'Gestión de stock - M. Hamilton Store';
$currentPage = 'gestion_stock';
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
            <h1 class="mb-2">Gestión de stock</h1>
            <p class="text-muted small mb-4">
                Tipos de gestión y movimientos de stock desde Oracle (<code>pkg_tipo_gestion</code>, <code>pkg_gestion_stock</code>). La cantidad no puede ser cero.
            </p>

            <div class="row g-4">
                <div class="col-lg-5">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h2 class="h6 mb-0">Tipos de gestión</h2>
                        <button type="button" class="btn btn-dark btn-sm" id="gstBtnNuevoTipo"><i class="bi bi-plus-lg"></i></button>
                    </div>
                    <div class="table-responsive shadow-sm rounded border bg-white">
                        <table class="table table-hover table-sm mb-0">
                            <thead class="table-light"><tr><th>ID</th><th>Descripción</th><th class="text-end">Acciones</th></tr></thead>
                            <tbody id="gstTiposBody"></tbody>
                        </table>
                    </div>
                </div>
                <div class="col-lg-7">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h2 class="h6 mb-0">Movimientos</h2>
                        <button type="button" class="btn btn-dark btn-sm" id="gstBtnNuevoMov"><i class="bi bi-plus-lg"></i></button>
                    </div>
                    <div class="table-responsive shadow-sm rounded border bg-white">
                        <table class="table table-hover table-sm mb-0">
                            <thead class="table-light">
                                <tr><th>ID</th><th>Fecha</th><th>Producto</th><th>Cant.</th><th>Tipo</th><th class="text-end">Acciones</th></tr>
                            </thead>
                            <tbody id="gstMovsBody"></tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="modal fade" id="gstModalTipo" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header"><h5 class="modal-title">Tipo de gestión</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button></div>
                        <div class="modal-body">
                            <input type="hidden" id="gstTipoId" value="">
                            <label for="gstTipoDesc" class="form-label">Descripción</label>
                            <input type="text" class="form-control" id="gstTipoDesc" maxlength="200">
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                            <button type="button" class="btn btn-dark" id="gstBtnGuardarTipo">Guardar</button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal fade" id="gstModalMov" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header"><h5 class="modal-title">Movimiento de stock</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button></div>
                        <div class="modal-body">
                            <input type="hidden" id="gstMovId" value="">
                            <div class="mb-3">
                                <label for="gstMovProducto" class="form-label">Producto</label>
                                <select class="form-select" id="gstMovProducto"></select>
                            </div>
                            <div class="mb-3">
                                <label for="gstMovTipo" class="form-label">Tipo de gestión</label>
                                <select class="form-select" id="gstMovTipo"></select>
                            </div>
                            <div class="mb-3">
                                <label for="gstMovCant" class="form-label">Cantidad (≠ 0)</label>
                                <input type="number" class="form-control" id="gstMovCant" step="1">
                            </div>
                            <div class="mb-0">
                                <label for="gstMovFecha" class="form-label">Fecha</label>
                                <input type="date" class="form-control" id="gstMovFecha">
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                            <button type="button" class="btn btn-dark" id="gstBtnGuardarMov">Guardar</button>
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
    <script src="<?php echo htmlspecialchars($basePath); ?>/js/modules/gestion_stock.js"></script>
    <script src="<?php echo htmlspecialchars($basePath); ?>/js/app.js"></script>
</body>
</html>
