<?php
require_once __DIR__ . '/../../../backend/config/auth_guard.php';
requireRole(['admin', 'soporte', 'inventario']);

$basePath = dirname(dirname(dirname($_SERVER['SCRIPT_NAME'])));
if ($basePath === '/' || $basePath === '\\') {
    $basePath = '';
}

$pageTitle = 'Proveedores - M. Hamilton Store';
$currentPage = 'proveedores';
$user = $_SESSION['user'] ?? '';
$role = $_SESSION['role'] ?? '';
$isAdmin = ($role === 'admin');
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
>
    <?php include __DIR__ . '/../../components/navbar.php'; ?>
    <div class="app-main">
        <?php include __DIR__ . '/../../components/sidebar.php'; ?>
        <main class="app-content">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                <h1 class="mb-0">Proveedores</h1>
                <span id="proveedoresCount" class="text-muted small">0 proveedor(es)</span>
            </div>
            <p class="text-muted small mb-4">
                Proveedores y contactos desde Oracle (<code>pkg_proveedores</code>, <code>pkg_contactos_proveedores</code>).
            </p>

            <?php if ($isAdmin): ?>
            <div class="d-flex justify-content-end mb-3">
                <button type="button" class="btn btn-dark" id="btnNuevoProveedor">
                    <i class="bi bi-plus-lg me-1"></i> Nuevo proveedor
                </button>
            </div>
            <?php endif; ?>

            <div class="row g-4">
                <div class="col-lg-7">
                    <div id="proveedoresLoading" class="text-center py-5 d-none">
                        <div class="spinner-border text-secondary" role="status"><span class="visually-hidden">Cargando</span></div>
                    </div>
                    <div id="proveedoresEmpty" class="alert alert-light border text-center d-none">
                        No hay proveedores registrados.
                    </div>
                    <div id="proveedoresContent" class="d-none">
                        <div class="row g-3" id="proveedoresGrid"></div>
                    </div>
                </div>
                <div class="col-lg-5">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white border-bottom">
                            <h5 class="mb-0"><i class="bi bi-people me-2"></i>Contactos</h5>
                        </div>
                        <div class="card-body" id="contactosPanel">
                            <div class="text-muted">Seleccione un proveedor para ver sus contactos.</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal fade" id="providerModal" tabindex="-1" aria-labelledby="providerModalTitle" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <div>
                                <h5 class="modal-title" id="providerModalTitle">Nuevo proveedor</h5>
                                <p class="text-muted small mb-0" id="providerModalDescription">Registra un proveedor nuevo.</p>
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                        </div>
                        <form id="providerForm">
                            <div class="modal-body">
                                <input type="hidden" id="providerId" value="">
                                <div class="mb-3">
                                    <label for="providerNombre" class="form-label">Nombre comercial <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="providerNombre" required maxlength="150" autocomplete="organization">
                                </div>
                                <div class="mb-3">
                                    <label for="providerCedulaJuridica" class="form-label">Cédula jurídica <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="providerCedulaJuridica" required maxlength="15" placeholder="ej. 3-101-123456">
                                </div>
                                <div class="mb-3">
                                    <label for="providerPaginaWeb" class="form-label">Página web</label>
                                    <input type="url" class="form-control" id="providerPaginaWeb" maxlength="150" placeholder="https://…">
                                </div>
                                <div class="mb-0">
                                    <label for="providerIdEstado" class="form-label">Estado</label>
                                    <select class="form-select" id="providerIdEstado" required></select>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                                <button type="submit" class="btn btn-dark" id="providerSubmitButton">Guardar proveedor</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="modal fade" id="contactModal" tabindex="-1" aria-labelledby="contactModalTitle" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <div>
                                <h5 class="modal-title" id="contactModalTitle">Nuevo contacto</h5>
                                <p class="text-muted small mb-0" id="contactModalDescription">Registra un contacto para el proveedor seleccionado.</p>
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                        </div>
                        <form id="contactForm">
                            <div class="modal-body">
                                <input type="hidden" id="contactId" value="">
                                <input type="hidden" id="contactProviderId" value="">
                                <div class="mb-3">
                                    <label for="contactNombre" class="form-label">Nombre <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="contactNombre" required maxlength="50">
                                </div>
                                <div class="mb-3">
                                    <label for="contactApellido" class="form-label">Apellido <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="contactApellido" required maxlength="50">
                                </div>
                                <div class="mb-3">
                                    <label for="contactEmail" class="form-label">Email <span class="text-danger">*</span></label>
                                    <input type="email" class="form-control" id="contactEmail" required maxlength="150">
                                </div>
                                <div class="mb-0">
                                    <label for="contactTelefono" class="form-label">Teléfono <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="contactTelefono" required maxlength="25">
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                                <button type="submit" class="btn btn-dark" id="contactSubmitButton">Guardar contacto</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </main>
    </div>
    <?php include __DIR__ . '/../../components/footer.php'; ?>
    <?php include __DIR__ . '/../../components/scripts_bootstrap.php'; ?>
    <script src="<?php echo htmlspecialchars($basePath); ?>/js/services/api.js"></script>
    <script src="<?php echo htmlspecialchars($basePath); ?>/js/utils/validation-helpers.js"></script>
    <script src="<?php echo htmlspecialchars($basePath); ?>/js/modules/proveedores.js"></script>
    <script src="<?php echo htmlspecialchars($basePath); ?>/js/app.js"></script>
</body>
</html>
