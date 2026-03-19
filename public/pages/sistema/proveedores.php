<?php
require_once __DIR__ . '/../../../backend/config/auth_guard.php';
requireRole(['admin', 'inventario']);
$basePath = dirname(dirname(dirname($_SERVER['SCRIPT_NAME'])));
$pageTitle = 'Proveedores - M. Hamilton Store';
$currentPage = 'proveedores';
$user = $_SESSION['user'] ?? '';
$role = $_SESSION['role'] ?? '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <?php include __DIR__ . '/../../components/head.php'; ?>
</head>
<body class="app-layout bg-light" data-base-path="<?php echo htmlspecialchars($basePath); ?>" data-current-role="<?php echo htmlspecialchars($role); ?>">
    <?php include __DIR__ . '/../../components/navbar.php'; ?>
    <div class="app-main">
        <?php include __DIR__ . '/../../components/sidebar.php'; ?>
        <main class="app-content">
            <h1 class="mb-4">Proveedores</h1>

            <div class="card">
                <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center gap-3 flex-wrap">
                    <div class="d-flex align-items-center gap-3">
                        <h5 class="mb-0"><i class="bi bi-truck me-2"></i>Listado de proveedores</h5>
                        <span class="small text-white-50" id="proveedoresCount">0 proveedores</span>
                    </div>
                    <?php if ($role === 'admin'): ?>
                        <button type="button" class="btn btn-sm btn-light" id="btnNuevoProveedor">
                            <i class="bi bi-plus-lg me-1"></i>Nuevo proveedor
                        </button>
                    <?php endif; ?>
                </div>
                <div class="card-body">
                    <div id="proveedoresLoading" class="text-center text-muted py-5">
                        <div class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></div>
                        Cargando proveedores...
                    </div>

                    <div id="proveedoresEmpty" class="text-center text-muted py-5 d-none">
                        <i class="bi bi-truck display-4"></i>
                        <p class="mt-2 mb-0">No hay proveedores registrados.</p>
                    </div>

                    <div id="proveedoresContent" class="row g-4 d-none">
                        <div class="col-12 col-lg-7">
                            <div class="row g-3" id="proveedoresGrid"></div>
                        </div>
                        <div class="col-12 col-lg-5">
                            <div class="border rounded-3 bg-light h-100 p-3" id="contactosPanel">
                                <div class="text-muted">Seleccione un proveedor para ver sus contactos.</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <?php if ($role === 'admin'): ?>
        <div class="modal fade" id="providerModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form id="providerForm">
                        <div class="modal-header bg-dark text-white">
                            <div>
                                <h5 class="modal-title mb-1" id="providerModalTitle">Nuevo proveedor</h5>
                                <p class="mb-0 text-white-50 small" id="providerModalDescription">Registra un proveedor nuevo.</p>
                            </div>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                        </div>
                        <div class="modal-body">
                            <input type="hidden" id="providerId">
                            <label for="providerNombre" class="form-label">Nombre del proveedor</label>
                            <input type="text" id="providerNombre" class="form-control" required maxlength="120">
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-dark" id="providerSubmitButton">Guardar proveedor</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="modal fade" id="contactModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form id="contactForm">
                        <div class="modal-header bg-dark text-white">
                            <div>
                                <h5 class="modal-title mb-1" id="contactModalTitle">Nuevo contacto</h5>
                                <p class="mb-0 text-white-50 small" id="contactModalDescription">Registra un contacto para el proveedor seleccionado.</p>
                            </div>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                        </div>
                        <div class="modal-body">
                            <input type="hidden" id="contactProviderId">
                            <input type="hidden" id="contactIndex">

                            <div class="mb-3">
                                <label for="contactNombre" class="form-label">Nombre</label>
                                <input type="text" id="contactNombre" class="form-control" required maxlength="120">
                            </div>
                            <div class="mb-3">
                                <label for="contactPuesto" class="form-label">Puesto</label>
                                <input type="text" id="contactPuesto" class="form-control" maxlength="120">
                            </div>
                            <div class="mb-3">
                                <label for="contactTelefono" class="form-label">Telefono</label>
                                <input type="text" id="contactTelefono" class="form-control" maxlength="60">
                            </div>
                            <div>
                                <label for="contactEmail" class="form-label">Correo</label>
                                <input type="email" id="contactEmail" class="form-control" maxlength="160">
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
    <?php endif; ?>

    <?php include __DIR__ . '/../../components/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?php echo htmlspecialchars($basePath); ?>/js/app.js"></script>
    <script src="<?php echo htmlspecialchars($basePath); ?>/js/modules/proveedores.js"></script>
</body>
</html>
