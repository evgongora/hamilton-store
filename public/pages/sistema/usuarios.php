<?php
require_once __DIR__ . '/../../../backend/config/auth_guard.php';
requireRole(['admin', 'cajero']);

$basePath = dirname(dirname(dirname($_SERVER['SCRIPT_NAME'])));
if ($basePath === '/' || $basePath === '\\') {
    $basePath = '';
}

$usuariosJsAbs = __DIR__ . '/../../js/modules/usuarios.js';
$usuariosJsV = is_file($usuariosJsAbs) ? (int) filemtime($usuariosJsAbs) : (int) time();
$valHelpersAbs = __DIR__ . '/../../js/utils/validation-helpers.js';
$valHelpersV = is_file($valHelpersAbs) ? (int) filemtime($valHelpersAbs) : (int) time();

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
                Gestión de cuentas Oracle (<code>pkg_usuarios</code>). En <strong>Nuevo</strong> elija si la cuenta es
                de personal (empleado) o de <strong>cliente tienda</strong> (rol CLIENTE y cliente sin usuario aún).
                En cuentas cliente existentes puede cambiar usuario, contraseña y estado; el rol y el vínculo al cliente no se editan.
            </p>

            <div class="table-responsive shadow-sm rounded border bg-white">
                <table class="table table-hover table-sm mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Usuario</th>
                            <th>Rol</th>
                            <th>Empleado / Cliente</th>
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
                                <div class="mb-3 d-none" id="wrapNuevoTipoCuenta">
                                    <label for="usuarioNuevoTipo" class="form-label">Tipo de cuenta</label>
                                    <select class="form-select" id="usuarioNuevoTipo">
                                        <option value="personal">Personal (empleado)</option>
                                        <option value="cliente">Cliente tienda</option>
                                    </select>
                                    <div class="form-text">Cliente: usuario con rol CLIENTE vinculado a una ficha de cliente sin cuenta aún.</div>
                                </div>
                                <div class="mb-3 d-none" id="wrapUsuarioClienteNuevoSelect">
                                    <label for="usuarioClienteNuevo" class="form-label">Cliente (sin usuario)</label>
                                    <select class="form-select" id="usuarioClienteNuevo"></select>
                                </div>
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
                                    <div class="form-text d-none" id="usuarioRolClienteHint">El rol de cuentas cliente no se cambia aquí.</div>
                                </div>
                                <div class="mb-3">
                                    <label for="usuarioEstado" class="form-label">Estado</label>
                                    <select class="form-select" id="usuarioEstado" required></select>
                                </div>
                                <div class="mb-3 d-none" id="wrapUsuarioClienteInfo">
                                    <label class="form-label">Cliente tienda</label>
                                    <p class="small text-muted mb-1">Vinculación fija a la ficha de cliente.</p>
                                    <p class="mb-0 small"><strong>ID:</strong> <span id="usuarioClienteIdLabel"></span></p>
                                    <p class="mb-0 small"><strong>Nombre:</strong> <span id="usuarioClienteNombre"></span></p>
                                </div>
                                <div class="mb-0" id="wrapUsuarioEmpleado">
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
    <script src="<?php echo htmlspecialchars($basePath); ?>/js/utils/validation-helpers.js?v=<?php echo $valHelpersV; ?>"></script>
    <script src="<?php echo htmlspecialchars($basePath); ?>/js/modules/usuarios.js?v=<?php echo $usuariosJsV; ?>"></script>
    <script src="<?php echo htmlspecialchars($basePath); ?>/js/app.js"></script>
</body>
</html>
