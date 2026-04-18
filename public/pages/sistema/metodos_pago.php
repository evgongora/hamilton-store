<?php
require_once __DIR__ . '/../../../backend/config/auth_guard.php';
requireRole(['admin', 'soporte']);

$basePath = dirname(dirname(dirname($_SERVER['SCRIPT_NAME'])));
if ($basePath === '/' || $basePath === '\\') {
    $basePath = '';
}

$pageTitle = 'Métodos de pago - M. Hamilton Store';
$currentPage = 'metodos_pago';
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
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                <h1 class="mb-0">Métodos de pago</h1>
                <button type="button" class="btn btn-dark btn-sm" id="mpBtnNuevo"><i class="bi bi-plus-lg me-1"></i> Nuevo</button>
            </div>
            <p class="text-muted small mb-4">
                Métodos de pago desde Oracle (<code>pkg_metodos_pago</code>). También se usa el listado en el módulo Pagos.
            </p>
            <div class="table-responsive shadow-sm rounded border bg-white">
                <table class="table table-hover table-sm mb-0">
                    <thead class="table-light"><tr><th>ID</th><th>Nombre</th><th class="text-end">Acciones</th></tr></thead>
                    <tbody id="mpBody"></tbody>
                </table>
            </div>

            <div class="modal fade" id="mpModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header"><h5 class="modal-title">Método de pago</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button></div>
                        <div class="modal-body">
                            <input type="hidden" id="mpId" value="">
                            <label for="mpNombre" class="form-label">Nombre</label>
                            <input type="text" class="form-control" id="mpNombre" maxlength="100">
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                            <button type="button" class="btn btn-dark" id="mpBtnGuardar">Guardar</button>
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
    <script src="<?php echo htmlspecialchars($basePath); ?>/js/modules/metodos_pago_admin.js"></script>
    <script src="<?php echo htmlspecialchars($basePath); ?>/js/app.js"></script>
</body>
</html>
