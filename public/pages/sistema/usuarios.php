<?php
require_once __DIR__ . '/../../../backend/config/auth_guard.php';
requireRole(['admin']);
$basePath = dirname(dirname(dirname($_SERVER['SCRIPT_NAME'])));
if ($basePath === '/' || $basePath === '\\') $basePath = '';
$pageTitle = 'Usuarios - M. Hamilton Store';
$currentPage = 'usuarios';
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
            <h1 class="mb-4">Usuarios</h1>

            <div class="card">
                <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-person-gear me-2"></i>Listado de usuarios</h5>
                    <button type="button" class="btn btn-light btn-sm" id="btnNuevoUsuario">
                        <i class="bi bi-plus-lg me-1"></i>Nuevo usuario
                    </button>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Usuario</th>
                                    <th>Rol</th>
                                    <th>Empleado</th>
                                    <th>Estado</th>
                                    <th class="text-end">Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="usuariosBody"></tbody>
                            <tfoot class="table-light">
                                <tr>
                                    <td colspan="5" class="text-muted small" id="usuariosCount">0 usuarios</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    <div id="usuariosEmpty" class="text-center text-muted py-5">
                        <i class="bi bi-person-gear display-4"></i>
                        <p class="mt-2">No hay usuarios registrados. <a href="#" id="linkNuevoUsuario">Agregar uno</a></p>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <div class="modal fade" id="modalUsuario" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-dark text-white">
                    <h5 class="modal-title" id="modalUsuarioLabel"><i class="bi bi-person-plus me-2"></i>Nuevo usuario</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="formUsuario">
                        <input type="hidden" id="usuarioId">
                        <div class="row g-3">
                            <div class="col-12">
                                <label for="usuarioUsername" class="form-label">Usuario (login) <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="usuarioUsername" required maxlength="50" placeholder="jperez">
                            </div>
                            <div class="col-12">
                                <label for="usuarioPassword" class="form-label">Contrase&ntilde;a <span class="text-danger" id="pwdReq">*</span></label>
                                <input type="password" class="form-control" id="usuarioPassword" maxlength="255" placeholder="(dejar vac&iacute;o para no cambiar)">
                            </div>
                            <div class="col-md-6">
                                <label for="usuarioRol" class="form-label">Rol <span class="text-danger">*</span></label>
                                <select class="form-select" id="usuarioRol" required name="rolesIdRol">
                                    <option value="">-- Cargando... --</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="usuarioEstado" class="form-label">Estado</label>
                                <select class="form-select" id="usuarioEstado" name="estadosIdEstado">
                                    <option value="">-- Cargando... --</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <label for="usuarioEmpleado" class="form-label">Empleado <span class="text-danger">*</span></label>
                                <select class="form-select" id="usuarioEmpleado" required>
                                    <option value="">-- Seleccionar empleado --</option>
                                </select>
                                <small class="text-muted">Un usuario por empleado. Empleados sin usuario aparecen primero.</small>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" id="btnGuardarUsuario"><i class="bi bi-check-lg me-1"></i>Guardar</button>
                </div>
            </div>
        </div>
    </div>

    <?php include __DIR__ . '/../../components/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?php echo htmlspecialchars($basePath); ?>/js/app.js"></script>
    <script src="<?php echo htmlspecialchars($basePath); ?>/js/modules/usuarios.js"></script>
</body>
</html>
