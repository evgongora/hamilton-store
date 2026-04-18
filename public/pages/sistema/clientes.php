<?php
require_once __DIR__ . '/../../../backend/config/auth_guard.php';
requireRole(['admin', 'soporte', 'cajero']);
$basePath = dirname(dirname(dirname($_SERVER['SCRIPT_NAME'])));
if ($basePath === '/' || $basePath === '\\') {
    $basePath = '';
}
$clientesJsAbs = __DIR__ . '/../../js/modules/clientes.js';
$clientesJsV = is_file($clientesJsAbs) ? (int) filemtime($clientesJsAbs) : (int) time();
$pageTitle = 'Clientes - M. Hamilton Store';
$currentPage = 'clientes';
$user = $_SESSION['user'] ?? '';
$role = $_SESSION['role'] ?? '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <?php include __DIR__ . '/../../components/head.php'; ?>
</head>
<body class="app-layout bg-light">
    <?php include __DIR__ . '/../../components/navbar.php'; ?>
    <div class="app-main">
        <?php include __DIR__ . '/../../components/sidebar.php'; ?>
        <main class="app-content">
            <h1 class="mb-2">Clientes</h1>
            <p class="text-muted small mb-4">
                Clientes en Oracle. Alta y cambio de estado usan <code>M_HAMILTON_STORE.pkg_clientes</code>
                (nombre/apellido solo A–Z y espacios; email válido). Las validaciones del formulario repiten esas reglas para evitar envíos inválidos.
            </p>
            <div class="card shadow-sm border-0 mb-3">
                <div class="card-body py-3">
                    <div class="row g-2 align-items-end">
                        <div class="col-md">
                            <label for="clientesBuscar" class="form-label small text-muted mb-0">Buscar</label>
                            <input type="search" class="form-control" id="clientesBuscar" placeholder="Nombre, apellido o email…" autocomplete="off">
                        </div>
                        <div class="col-md-auto">
                            <button type="button" class="btn btn-primary w-100" id="btnNuevoCliente"
                                data-bs-toggle="modal" data-bs-target="#cliModal">Nuevo cliente</button>
                        </div>
                    </div>
                </div>
            </div>
            <div id="clientesSistemaTable" class="mt-3"></div>
        </main>
    </div>

    <div class="modal fade" id="cliModal" tabindex="-1" aria-labelledby="cliModalTitulo" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="cliModalTitulo">Cliente</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="cliFieldId" value="">
                    <div class="mb-3">
                        <label for="cliNombre" class="form-label">Nombre</label>
                        <input type="text" class="form-control" id="cliNombre" maxlength="100" autocomplete="off" placeholder="Solo letras A-Z y espacios">
                    </div>
                    <div class="mb-3">
                        <label for="cliApellido" class="form-label">Apellido</label>
                        <input type="text" class="form-control" id="cliApellido" maxlength="100" autocomplete="off">
                    </div>
                    <div class="mb-3">
                        <label for="cliEmail" class="form-label">Email</label>
                        <input type="email" class="form-control" id="cliEmail" maxlength="200" autocomplete="off">
                    </div>
                    <div class="mb-0 d-none" id="cliWrapEstado">
                        <label for="cliEstado" class="form-label">Estado</label>
                        <select class="form-select" id="cliEstado"></select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" id="cliBtnGuardar">Guardar</button>
                </div>
            </div>
        </div>
    </div>

    <?php include __DIR__ . '/../../components/footer.php'; ?>
    <?php include __DIR__ . '/../../components/scripts_bootstrap.php'; ?>
    <script src="<?php echo htmlspecialchars($basePath); ?>/js/services/api.js"></script>
    <script src="<?php echo htmlspecialchars($basePath); ?>/js/utils/validation-helpers.js"></script>
    <script src="<?php echo htmlspecialchars($basePath); ?>/js/modules/clientes.js?v=<?php echo $clientesJsV; ?>"></script>
    <script src="<?php echo htmlspecialchars($basePath); ?>/js/app.js"></script>
</body>
</html>
