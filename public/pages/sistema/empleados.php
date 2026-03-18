<?php
require_once __DIR__ . '/../../../backend/config/auth_guard.php';
requireLogin();
$basePath = dirname(dirname(dirname($_SERVER['SCRIPT_NAME'])));
if ($basePath === '/' || $basePath === '\\') $basePath = '';
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
            <h1 class="mb-4">Empleados</h1>

            <div class="card">
                <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-person-badge me-2"></i>Listado de empleados</h5>
                    <button type="button" class="btn btn-light btn-sm" id="btnNuevoEmpleado">
                        <i class="bi bi-plus-lg me-1"></i>Nuevo empleado
                    </button>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Nombre</th>
                                    <th>Puesto</th>
                                    <th>Email</th>
                                    <th>Fecha ingreso</th>
                                    <th>Estado</th>
                                    <th class="text-end">Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="empleadosBody"></tbody>
                            <tfoot class="table-light">
                                <tr>
                                    <td colspan="6" class="text-muted small" id="empleadosCount">0 empleados</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    <div id="empleadosEmpty" class="text-center text-muted py-5">
                        <i class="bi bi-person-badge display-4"></i>
                        <p class="mt-2">No hay empleados registrados. <a href="#" id="linkNuevoEmpleado">Agregar uno</a></p>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <div class="modal fade" id="modalEmpleado" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-dark text-white">
                    <h5 class="modal-title" id="modalEmpleadoLabel"><i class="bi bi-person-plus me-2"></i>Nuevo empleado</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="formEmpleado">
                        <input type="hidden" id="empleadoId">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="empleadoNombre" class="form-label">Nombre <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="empleadoNombre" required maxlength="50">
                            </div>
                            <div class="col-md-6">
                                <label for="empleadoApellido" class="form-label">Apellido <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="empleadoApellido" required maxlength="50">
                            </div>
                            <div class="col-md-6">
                                <label for="empleadoPuesto" class="form-label">Puesto <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="empleadoPuesto" required maxlength="50" placeholder="Cajero, Vendedor...">
                            </div>
                            <div class="col-md-6">
                                <label for="empleadoEmail" class="form-label">Email <span class="text-danger">*</span></label>
                                <input type="email" class="form-control" id="empleadoEmail" required maxlength="150">
                            </div>
                            <div class="col-md-6">
                                <label for="empleadoFechaIngreso" class="form-label">Fecha ingreso <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="empleadoFechaIngreso" required>
                            </div>
                            <div class="col-md-6">
                                <label for="empleadoEstado" class="form-label">Estado</label>
                                <select class="form-select" id="empleadoEstado" name="estadosIdEstado">
                                    <option value="">-- Cargando... --</option>
                                </select>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" id="btnGuardarEmpleado"><i class="bi bi-check-lg me-1"></i>Guardar</button>
                </div>
            </div>
        </div>
    </div>

    <?php include __DIR__ . '/../../components/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?php echo htmlspecialchars($basePath); ?>/js/app.js"></script>
    <script src="<?php echo htmlspecialchars($basePath); ?>/js/modules/empleados.js"></script>
</body>
</html>
