<?php
require_once __DIR__ . '/../../../backend/config/auth_guard.php';
requireRole(['admin', 'soporte']);

$basePath = dirname(dirname(dirname($_SERVER['SCRIPT_NAME'])));
if ($basePath === '/' || $basePath === '\\') {
    $basePath = '';
}

$pageTitle = 'Direcciones y teléfonos - M. Hamilton Store';
$currentPage = 'datos_auxiliares';
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
            <h1 class="mb-2">Direcciones y teléfonos</h1>
            <p class="text-muted small mb-4">
                Direcciones y teléfonos desde Oracle (<code>pkg_direcciones</code>, <code>pkg_telefonos_clientes</code>, <code>pkg_telefonos_cont_proveedores</code>).
            </p>

            <ul class="nav nav-tabs mb-3" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#da-pane-dir" type="button" role="tab">Direcciones</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#da-pane-tc" type="button" role="tab">Teléfonos clientes</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#da-pane-tcp" type="button" role="tab">Teléfonos contactos proveedor</button>
                </li>
            </ul>

            <div class="tab-content">
                <div class="tab-pane fade show active" id="da-pane-dir" role="tabpanel">
                    <div class="d-flex justify-content-end mb-2">
                        <button type="button" class="btn btn-dark btn-sm" id="daBtnNuevaDir"><i class="bi bi-plus-lg me-1"></i> Nueva dirección</button>
                    </div>
                    <div class="table-responsive shadow-sm rounded border bg-white">
                        <table class="table table-hover table-sm mb-0">
                            <thead class="table-light">
                                <tr><th>ID</th><th>Señas</th><th>Ubicación</th><th>Cliente / Proveedor</th><th class="text-end">Acciones</th></tr>
                            </thead>
                            <tbody id="daDirBody"></tbody>
                        </table>
                    </div>
                </div>
                <div class="tab-pane fade" id="da-pane-tc" role="tabpanel">
                    <div class="d-flex justify-content-end mb-2">
                        <button type="button" class="btn btn-dark btn-sm" id="daBtnNuevoTc"><i class="bi bi-plus-lg me-1"></i> Nuevo teléfono</button>
                    </div>
                    <div class="table-responsive shadow-sm rounded border bg-white">
                        <table class="table table-hover table-sm mb-0">
                            <thead class="table-light"><tr><th>ID</th><th>Número</th><th>Cliente</th><th class="text-end">Acciones</th></tr></thead>
                            <tbody id="daTcBody"></tbody>
                        </table>
                    </div>
                </div>
                <div class="tab-pane fade" id="da-pane-tcp" role="tabpanel">
                    <div class="d-flex justify-content-end mb-2">
                        <button type="button" class="btn btn-dark btn-sm" id="daBtnNuevoTcp"><i class="bi bi-plus-lg me-1"></i> Nuevo teléfono</button>
                    </div>
                    <div class="table-responsive shadow-sm rounded border bg-white">
                        <table class="table table-hover table-sm mb-0">
                            <thead class="table-light"><tr><th>ID</th><th>Número</th><th>Contacto</th><th class="text-end">Acciones</th></tr></thead>
                            <tbody id="daTcpBody"></tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="modal fade" id="daModalDir" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-lg">
                    <div class="modal-content">
                        <div class="modal-header"><h5 class="modal-title">Dirección</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button></div>
                        <div class="modal-body">
                            <input type="hidden" id="daDirId" value="">
                            <div class="mb-3">
                                <label for="daDirSenas" class="form-label">Otras señas</label>
                                <input type="text" class="form-control" id="daDirSenas" maxlength="500">
                            </div>
                            <div class="row g-2 mb-3">
                                <div class="col-md-4">
                                    <label for="daDirProv" class="form-label">Provincia</label>
                                    <select class="form-select" id="daDirProv"></select>
                                </div>
                                <div class="col-md-4">
                                    <label for="daDirCant" class="form-label">Cantón</label>
                                    <select class="form-select" id="daDirCant"></select>
                                </div>
                                <div class="col-md-4">
                                    <label for="daDirDist" class="form-label">Distrito</label>
                                    <select class="form-select" id="daDirDist"></select>
                                </div>
                            </div>
                            <div class="mb-2"><span class="form-label d-block">Asignar a</span></div>
                            <div class="btn-group mb-3" role="group">
                                <input type="radio" class="btn-check" name="daDirTipo" id="daDirTipoCli" value="cli" autocomplete="off" checked>
                                <label class="btn btn-outline-dark btn-sm" for="daDirTipoCli">Cliente</label>
                                <input type="radio" class="btn-check" name="daDirTipo" id="daDirTipoPrv" value="prv" autocomplete="off">
                                <label class="btn btn-outline-dark btn-sm" for="daDirTipoPrv">Proveedor</label>
                            </div>
                            <div class="mb-0" id="daDirWrapCli">
                                <label for="daDirCliente" class="form-label">Cliente</label>
                                <select class="form-select" id="daDirCliente"></select>
                            </div>
                            <div class="mb-0 d-none" id="daDirWrapPrv">
                                <label for="daDirProveedor" class="form-label">Proveedor</label>
                                <select class="form-select" id="daDirProveedor"></select>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                            <button type="button" class="btn btn-dark" id="daBtnGuardarDir">Guardar</button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal fade" id="daModalTc" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header"><h5 class="modal-title">Teléfono cliente</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button></div>
                        <div class="modal-body">
                            <input type="hidden" id="daTcId" value="">
                            <div class="mb-3">
                                <label for="daTcCliente" class="form-label">Cliente</label>
                                <select class="form-select" id="daTcCliente"></select>
                            </div>
                            <div class="mb-0">
                                <label for="daTcNum" class="form-label">Número</label>
                                <input type="text" class="form-control" id="daTcNum" maxlength="40" placeholder="8888-8888">
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                            <button type="button" class="btn btn-dark" id="daBtnGuardarTc">Guardar</button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal fade" id="daModalTcp" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header"><h5 class="modal-title">Teléfono contacto proveedor</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button></div>
                        <div class="modal-body">
                            <input type="hidden" id="daTcpId" value="">
                            <div class="mb-3">
                                <label for="daTcpProveedor" class="form-label">Proveedor</label>
                                <select class="form-select" id="daTcpProveedor"></select>
                            </div>
                            <div class="mb-3">
                                <label for="daTcpContacto" class="form-label">Contacto</label>
                                <select class="form-select" id="daTcpContacto"></select>
                            </div>
                            <div class="mb-0">
                                <label for="daTcpNum" class="form-label">Número</label>
                                <input type="text" class="form-control" id="daTcpNum" maxlength="40">
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                            <button type="button" class="btn btn-dark" id="daBtnGuardarTcp">Guardar</button>
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
    <script src="<?php echo htmlspecialchars($basePath); ?>/js/modules/datos_auxiliares.js"></script>
    <script src="<?php echo htmlspecialchars($basePath); ?>/js/app.js"></script>
</body>
</html>
