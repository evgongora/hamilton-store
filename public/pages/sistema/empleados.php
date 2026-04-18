<?php
require_once __DIR__ . '/../../../backend/config/auth_guard.php';
requireRole(['admin', 'soporte']);

$basePath = dirname(dirname(dirname($_SERVER['SCRIPT_NAME'])));
if ($basePath === '/' || $basePath === '\\') {
    $basePath = '';
}

$pageTitle = 'Empleados - M. Hamilton Store';
$currentPage = 'empleados';
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
                <h1 class="mb-0">Empleados</h1>
                <div class="d-flex align-items-center gap-3">
                    <span id="empleadosCount" class="text-muted small">0 empleado(s)</span>
                    <button type="button" class="btn btn-dark btn-sm" id="btnNuevoEmpleado">
                        <i class="bi bi-plus-lg me-1"></i> Nuevo
                    </button>
                </div>
            </div>
            <p class="text-muted small mb-4">
                Empleados desde Oracle (<code>pkg_empleados</code>). La fecha de ingreso la asigna la base al crear.
            </p>

            <div class="table-responsive shadow-sm rounded border bg-white">
                <table class="table table-hover table-sm mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Nombre</th>
                            <th>Puesto</th>
                            <th>Email</th>
                            <th>Ingreso</th>
                            <th>Estado</th>
                            <th class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="empleadosBody"></tbody>
                </table>
            </div>
            <p id="empleadosEmpty" class="text-muted py-4 text-center" style="display: none;">No hay empleados. Use el botón Nuevo para registrar uno.</p>

            <div class="modal fade" id="modalEmpleado" tabindex="-1" aria-labelledby="modalEmpleadoLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="modalEmpleadoLabel">Empleado</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                        </div>
                        <div class="modal-body">
                            <form id="formEmpleado">
                                <input type="hidden" id="empleadoId" value="">
                                <div class="mb-3">
                                    <label for="empleadoNombre" class="form-label">Nombre</label>
                                    <input type="text" class="form-control" id="empleadoNombre" required maxlength="50">
                                </div>
                                <div class="mb-3">
                                    <label for="empleadoApellido" class="form-label">Apellido</label>
                                    <input type="text" class="form-control" id="empleadoApellido" required maxlength="50">
                                </div>
                                <div class="mb-3">
                                    <label for="empleadoPuesto" class="form-label">Puesto</label>
                                    <input type="text" class="form-control" id="empleadoPuesto" required maxlength="50">
                                </div>
                                <div class="mb-3">
                                    <label for="empleadoEmail" class="form-label">Email</label>
                                    <input type="email" class="form-control" id="empleadoEmail" required maxlength="150">
                                </div>
                                <div class="mb-3 d-none" id="empleadoFechaGrupo">
                                    <label for="empleadoFechaIngreso" class="form-label">Fecha de ingreso (BD)</label>
                                    <input type="text" class="form-control" id="empleadoFechaIngreso" readonly tabindex="-1">
                                </div>
                                <div class="mb-0">
                                    <label for="empleadoEstado" class="form-label">Estado</label>
                                    <select class="form-select" id="empleadoEstado" required></select>
                                </div>
                            </form>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                            <button type="button" class="btn btn-dark" id="btnGuardarEmpleado">Guardar</button>
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
    <script src="<?php echo htmlspecialchars($basePath); ?>/js/modules/empleados.js"></script>
    <script src="<?php echo htmlspecialchars($basePath); ?>/js/app.js"></script>
</body>
</html>
