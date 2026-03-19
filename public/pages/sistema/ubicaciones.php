<?php
require_once __DIR__ . '/../../../backend/config/auth_guard.php';
requireRole(['admin']);
$basePath = dirname(dirname(dirname($_SERVER['SCRIPT_NAME'])));
if ($basePath === '/' || $basePath === '\\') $basePath = '';
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
            <h1 class="mb-4">Ubicaciones</h1>
            <p class="text-muted mb-4">Provincias, cantones y distritos de Costa Rica. Usados en direcciones de clientes y proveedores.</p>

            <ul class="nav nav-tabs mb-4" id="ubicacionesTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="provincias-tab" data-bs-toggle="tab" data-bs-target="#provincias" type="button">Provincias</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="cantones-tab" data-bs-toggle="tab" data-bs-target="#cantones" type="button">Cantones</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="distritos-tab" data-bs-toggle="tab" data-bs-target="#distritos" type="button">Distritos</button>
                </li>
            </ul>

            <div class="tab-content" id="ubicacionesTabContent">
                <div class="tab-pane fade show active" id="provincias">
                    <div class="card">
                        <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Provincias</h5>
                            <button type="button" class="btn btn-light btn-sm" id="btnNuevaProvincia"><i class="bi bi-plus-lg me-1"></i>Nueva</button>
                        </div>
                        <div class="card-body p-0">
                            <table class="table table-hover mb-0">
                                <thead class="table-light"><tr><th>ID</th><th>Nombre</th><th class="text-end">Acciones</th></tr></thead>
                                <tbody id="provinciasBody"></tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="tab-pane fade" id="cantones">
                    <div class="card">
                        <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Cantones</h5>
                            <button type="button" class="btn btn-light btn-sm" id="btnNuevoCanton"><i class="bi bi-plus-lg me-1"></i>Nuevo</button>
                        </div>
                        <div class="card-body p-0">
                            <table class="table table-hover mb-0">
                                <thead class="table-light"><tr><th>ID</th><th>Nombre</th><th>Provincia</th><th class="text-end">Acciones</th></tr></thead>
                                <tbody id="cantonesBody"></tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="tab-pane fade" id="distritos">
                    <div class="card">
                        <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Distritos</h5>
                            <button type="button" class="btn btn-light btn-sm" id="btnNuevoDistrito"><i class="bi bi-plus-lg me-1"></i>Nuevo</button>
                        </div>
                        <div class="card-body p-0">
                            <table class="table table-hover mb-0">
                                <thead class="table-light"><tr><th>ID</th><th>Nombre</th><th>Cantón</th><th>Código postal</th><th class="text-end">Acciones</th></tr></thead>
                                <tbody id="distritosBody"></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Modales inline simplificados -->
    <div class="modal fade" id="modalProvincia" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
        <div class="modal-header bg-dark text-white"><h5 class="modal-title">Provincia</h5><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div>
        <div class="modal-body"><input type="hidden" id="provinciaId"><input type="text" class="form-control" id="provinciaNombre" placeholder="Nombre"></div>
        <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button><button type="button" class="btn btn-primary" id="btnGuardarProvincia">Guardar</button></div>
    </div></div></div>
    <div class="modal fade" id="modalCanton" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
        <div class="modal-header bg-dark text-white"><h5 class="modal-title">Cantón</h5><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div>
        <div class="modal-body"><input type="hidden" id="cantonId"><input type="text" class="form-control mb-2" id="cantonNombre" placeholder="Nombre"><select class="form-select" id="cantonProvincia"><option value="">-- Provincia --</option></select></div>
        <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button><button type="button" class="btn btn-primary" id="btnGuardarCanton">Guardar</button></div>
    </div></div></div>
    <div class="modal fade" id="modalDistrito" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
        <div class="modal-header bg-dark text-white"><h5 class="modal-title">Distrito</h5><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div>
        <div class="modal-body"><input type="hidden" id="distritoId"><input type="text" class="form-control mb-2" id="distritoNombre" placeholder="Nombre"><select class="form-select mb-2" id="distritoCanton"><option value="">-- Cantón --</option></select><input type="number" class="form-control" id="distritoCodigoPostal" placeholder="Código postal (opcional)"></div>
        <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button><button type="button" class="btn btn-primary" id="btnGuardarDistrito">Guardar</button></div>
    </div></div></div>

    <?php include __DIR__ . '/../../components/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?php echo htmlspecialchars($basePath); ?>/js/app.js"></script>
    <script src="<?php echo htmlspecialchars($basePath); ?>/js/modules/ubicaciones.js"></script>
</body>
</html>
