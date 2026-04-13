<?php
require_once __DIR__ . '/../../../backend/config/auth_guard.php';
requireRole(['admin', 'soporte', 'cajero', 'inventario']);

$basePath = dirname(dirname(dirname($_SERVER['SCRIPT_NAME'])));
if ($basePath === '/' || $basePath === '\\') {
    $basePath = '';
}

$pageTitle = 'Inventario - M. Hamilton Store';
$currentPage = 'inventario';
$user = $_SESSION['user'] ?? '';
$role = $_SESSION['role'] ?? '';
$canEditStock = in_array($role, ['admin', 'inventario', 'soporte'], true);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <?php include __DIR__ . '/../../components/head.php'; ?>
</head>
<body
    class="app-layout bg-light"
    data-base-path="<?php echo htmlspecialchars($basePath); ?>"
    data-current-role="<?php echo htmlspecialchars($role); ?>"
    data-can-edit-stock="<?php echo $canEditStock ? '1' : '0'; ?>"
>
    <?php include __DIR__ . '/../../components/navbar.php'; ?>
    <div class="app-main">
        <?php include __DIR__ . '/../../components/sidebar.php'; ?>
        <main class="app-content">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
                <h1 class="mb-0">Inventario</h1>
                <?php if (!$canEditStock): ?>
                    <span class="badge bg-secondary">Solo lectura</span>
                <?php endif; ?>
            </div>
            <p class="text-muted small mb-3">
                Stock por producto. Los ajustes de cantidad respetan las reglas de la base (triggers / paquetes).
            </p>

            <div class="card shadow-sm border-0 mb-3">
                <div class="card-body">
                    <div class="row g-2 align-items-end">
                        <div class="col-md-4">
                            <label for="invBuscar" class="form-label small text-muted mb-0">Buscar</label>
                            <input type="search" class="form-control" id="invBuscar" placeholder="Producto…" autocomplete="off">
                        </div>
                        <div class="col-md-3">
                            <label for="invCategoria" class="form-label small text-muted mb-0">Categoría</label>
                            <select class="form-select" id="invCategoria">
                                <option value="">Todas</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="invSoloBajo" class="form-label small text-muted mb-0">Alertas</label>
                            <div class="form-check mt-1">
                                <input class="form-check-input" type="checkbox" id="invSoloBajo">
                                <label class="form-check-label" for="invSoloBajo">Solo stock bajo (≤ 5)</label>
                            </div>
                        </div>
                        <div class="col-md-2 text-md-end">
                            <button type="button" class="btn btn-outline-secondary btn-sm" id="invLimpiar">Limpiar</button>
                        </div>
                    </div>
                </div>
            </div>

            <div id="inventarioSistemaTable" class="mt-1"></div>

            <div class="modal fade" id="modalAjusteStock" tabindex="-1" aria-labelledby="modalAjusteStockLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="modalAjusteStockLabel">Ajustar stock</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                        </div>
                        <div class="modal-body">
                            <input type="hidden" id="invProdId" value="">
                            <p class="mb-2"><strong id="invProdNombre"></strong></p>
                            <p class="small text-muted mb-3">Stock actual: <span id="invProdStockActual"></span></p>
                            <label for="invNuevaCantidad" class="form-label">Nueva cantidad</label>
                            <input type="number" class="form-control" id="invNuevaCantidad" min="0" step="1">
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                            <button type="button" class="btn btn-dark" id="btnGuardarAjusteStock">Guardar</button>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
    <?php include __DIR__ . '/../../components/footer.php'; ?>
    <?php include __DIR__ . '/../../components/scripts_bootstrap.php'; ?>
    <script src="<?php echo htmlspecialchars($basePath); ?>/js/services/api.js"></script>
    <script src="<?php echo htmlspecialchars($basePath); ?>/js/modules/inventario.js"></script>
    <script src="<?php echo htmlspecialchars($basePath); ?>/js/app.js"></script>
</body>
</html>
