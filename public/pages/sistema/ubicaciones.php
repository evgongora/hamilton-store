<?php
/**
 * ubicaciones.php — Provincias, cantones y distritos (demo localStorage).
 */
require_once __DIR__ . '/../../../backend/config/auth_guard.php';
requireRole(['admin', 'soporte']);

$basePath = dirname(dirname(dirname($_SERVER['SCRIPT_NAME'])));
if ($basePath === '/' || $basePath === '\\') {
    $basePath = '';
}

$pageTitle = 'Ubicaciones - M. Hamilton Store';
$currentPage = 'ubicaciones';
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
            <h1 class="mb-2">Ubicaciones</h1>
            <p class="text-muted small mb-4">
                Jerarquía provincia → cantón → distrito (demo en el navegador). Para producción, sincronizar con tablas Oracle de ubicación.
            </p>

            <ul class="nav nav-tabs mb-3" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="tab-prov" data-bs-toggle="tab" data-bs-target="#pane-prov" type="button" role="tab">Provincias</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="tab-cant" data-bs-toggle="tab" data-bs-target="#pane-cant" type="button" role="tab">Cantones</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="tab-dist" data-bs-toggle="tab" data-bs-target="#pane-dist" type="button" role="tab">Distritos</button>
                </li>
            </ul>

            <div class="tab-content">
                <div class="tab-pane fade show active" id="pane-prov" role="tabpanel">
                    <div class="d-flex justify-content-end mb-2">
                        <button type="button" class="btn btn-dark btn-sm" id="btnNuevaProvincia"><i class="bi bi-plus-lg me-1"></i> Nueva provincia</button>
                    </div>
                    <div class="table-responsive shadow-sm rounded border bg-white">
                        <table class="table table-hover table-sm mb-0">
                            <thead class="table-light"><tr><th>ID</th><th>Nombre</th><th class="text-end">Acciones</th></tr></thead>
                            <tbody id="provinciasBody"></tbody>
                        </table>
                    </div>
                </div>
                <div class="tab-pane fade" id="pane-cant" role="tabpanel">
                    <div class="d-flex justify-content-end mb-2">
                        <button type="button" class="btn btn-dark btn-sm" id="btnNuevoCanton"><i class="bi bi-plus-lg me-1"></i> Nuevo cantón</button>
                    </div>
                    <div class="table-responsive shadow-sm rounded border bg-white">
                        <table class="table table-hover table-sm mb-0">
                            <thead class="table-light"><tr><th>ID</th><th>Nombre</th><th>Provincia</th><th class="text-end">Acciones</th></tr></thead>
                            <tbody id="cantonesBody"></tbody>
                        </table>
                    </div>
                </div>
                <div class="tab-pane fade" id="pane-dist" role="tabpanel">
                    <div class="d-flex justify-content-end mb-2">
                        <button type="button" class="btn btn-dark btn-sm" id="btnNuevoDistrito"><i class="bi bi-plus-lg me-1"></i> Nuevo distrito</button>
                    </div>
                    <div class="table-responsive shadow-sm rounded border bg-white">
                        <table class="table table-hover table-sm mb-0">
                            <thead class="table-light"><tr><th>ID</th><th>Nombre</th><th>Cantón</th><th>Cód. postal</th><th class="text-end">Acciones</th></tr></thead>
                            <tbody id="distritosBody"></tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="modal fade" id="modalProvincia" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header"><h5 class="modal-title">Provincia</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button></div>
                        <div class="modal-body">
                            <input type="hidden" id="provinciaId" value="">
                            <label for="provinciaNombre" class="form-label">Nombre</label>
                            <input type="text" class="form-control" id="provinciaNombre" maxlength="20">
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                            <button type="button" class="btn btn-dark" id="btnGuardarProvincia">Guardar</button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal fade" id="modalCanton" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header"><h5 class="modal-title">Cantón</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button></div>
                        <div class="modal-body">
                            <input type="hidden" id="cantonId" value="">
                            <div class="mb-3">
                                <label for="cantonProvincia" class="form-label">Provincia</label>
                                <select class="form-select" id="cantonProvincia"></select>
                            </div>
                            <div class="mb-0">
                                <label for="cantonNombre" class="form-label">Nombre</label>
                                <input type="text" class="form-control" id="cantonNombre" maxlength="50">
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                            <button type="button" class="btn btn-dark" id="btnGuardarCanton">Guardar</button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal fade" id="modalDistrito" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header"><h5 class="modal-title">Distrito</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button></div>
                        <div class="modal-body">
                            <input type="hidden" id="distritoId" value="">
                            <div class="mb-3">
                                <label for="distritoCanton" class="form-label">Cantón</label>
                                <select class="form-select" id="distritoCanton"></select>
                            </div>
                            <div class="mb-3">
                                <label for="distritoNombre" class="form-label">Nombre</label>
                                <input type="text" class="form-control" id="distritoNombre" maxlength="50">
                            </div>
                            <div class="mb-0">
                                <label for="distritoCodigoPostal" class="form-label">Código postal</label>
                                <input type="number" class="form-control" id="distritoCodigoPostal" min="0">
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                            <button type="button" class="btn btn-dark" id="btnGuardarDistrito">Guardar</button>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
    <?php include __DIR__ . '/../../components/footer.php'; ?>
    <?php include __DIR__ . '/../../components/scripts_bootstrap.php'; ?>
    <script src="<?php echo htmlspecialchars($basePath); ?>/js/modules/ubicaciones.js"></script>
    <script src="<?php echo htmlspecialchars($basePath); ?>/js/app.js"></script>
</body>
</html>
