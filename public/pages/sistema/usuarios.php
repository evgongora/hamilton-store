<?php
require_once __DIR__ . '/../../../backend/config/auth_guard.php';
requireRole(['admin']);

$basePath = dirname(dirname(dirname($_SERVER['SCRIPT_NAME'])));
if ($basePath === '/' || $basePath === '\\') {
    $basePath = '';
}

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
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                <h1 class="mb-0">Usuarios del sistema</h1>
                <div class="d-flex align-items-center gap-3">
                    <span id="usuariosCount" class="text-muted small">0 usuario(s)</span>
                    <button type="button" class="btn btn-dark btn-sm" id="btnNuevoUsuario">
                        <i class="bi bi-plus-lg me-1"></i> Nuevo
                    </button>
                </div>
            </div>
            <p class="text-muted small mb-4">
                Usuarios de personal vinculados a empleados (Oracle). Los usuarios de clientes de la tienda se listan como solo lectura.
            </p>

            <div class="table-responsive shadow-sm rounded border bg-white">
                <table class="table table-hover table-sm mb-0">
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
                </table>
            </div>
            <p id="usuariosEmpty" class="text-muted py-4 text-center" style="display: none;">No hay usuarios.</p>

            <div class="modal fade" id="modalUsuario" tabindex="-1" aria-labelledby="modalUsuarioLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="modalUsuarioLabel">Usuario</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                        </div>
                        <div class="modal-body">
                            <form id="formUsuario">
                                <input type="hidden" id="usuarioId" value="">
                                <div class="mb-3">
                                    <label for="usuarioUsername" class="form-label">Nombre de usuario</label>
                                    <input type="text" class="form-control" id="usuarioUsername" required maxlength="50" autocomplete="username">
                                </div>
                                <div class="mb-3">
                                    <label for="usuarioPassword" class="form-label">Contraseña</label>
                                    <input type="password" class="form-control" id="usuarioPassword" maxlength="100" autocomplete="new-password">
                                    <div class="form-text" id="pwdReq">Obligatoria para usuarios nuevos.</div>
                                </div>
                                <div class="mb-3">
                                    <label for="usuarioRol" class="form-label">Rol</label>
                                    <select class="form-select" id="usuarioRol" required></select>
                                </div>
                                <div class="mb-3">
                                    <label for="usuarioEstado" class="form-label">Estado</label>
                                    <select class="form-select" id="usuarioEstado" required></select>
                                </div>
                                <div class="mb-0">
                                    <label for="usuarioEmpleado" class="form-label">Empleado</label>
                                    <select class="form-select" id="usuarioEmpleado" required></select>
                                </div>
                            </form>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                            <button type="button" class="btn btn-dark" id="btnGuardarUsuario">Guardar</button>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
    <?php include __DIR__ . '/../../components/footer.php'; ?>
    <?php include __DIR__ . '/../../components/scripts_bootstrap.php'; ?>
    <script src="<?php echo htmlspecialchars($basePath); ?>/js/services/api.js"></script>
    <script src="<?php echo htmlspecialchars($basePath); ?>/js/modules/usuarios.js"></script>
    <script src="<?php echo htmlspecialchars($basePath); ?>/js/app.js"></script>
</body>
</html>
