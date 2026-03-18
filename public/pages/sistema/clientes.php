<?php
require_once __DIR__ . '/../../../backend/config/auth_guard.php';
requireLogin();
$basePath = dirname(dirname(dirname($_SERVER['SCRIPT_NAME'])));
if ($basePath === '/' || $basePath === '\\') $basePath = '';
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
<body class="app-layout bg-light" data-base-path="<?php echo htmlspecialchars($basePath); ?>">
    <?php include __DIR__ . '/../../components/navbar.php'; ?>
    <div class="app-main">
        <?php include __DIR__ . '/../../components/sidebar.php'; ?>
        <main class="app-content">
            <h1 class="mb-4">Clientes</h1>

            <div class="card">
                <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-people me-2"></i>Listado de clientes</h5>
                    <button type="button" class="btn btn-light btn-sm" id="btnNuevoCliente">
                        <i class="bi bi-plus-lg me-1"></i>Nuevo cliente
                    </button>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Nombre</th>
                                    <th>Email</th>
                                    <th>Tel&eacute;fono</th>
                                    <th>Ubicaci&oacute;n</th>
                                    <th>Estado</th>
                                    <th class="text-end">Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="clientesBody"></tbody>
                            <tfoot class="table-light">
                                <tr>
                                    <td colspan="6" class="text-muted small" id="clientesCount">0 clientes</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    <div id="clientesEmpty" class="text-center text-muted py-5">
                        <i class="bi bi-people display-4"></i>
                        <p class="mt-2">No hay clientes registrados. <a href="#" id="linkNuevoCliente">Agregar uno</a></p>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Modal CRUD cliente -->
    <div class="modal fade" id="modalCliente" tabindex="-1" aria-labelledby="modalClienteLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-dark text-white">
                    <h5 class="modal-title" id="modalClienteLabel"><i class="bi bi-person-plus me-2"></i>Nuevo cliente</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <form id="formCliente">
                        <input type="hidden" id="clienteId" name="id">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="clienteNombre" class="form-label">Nombre <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="clienteNombre" name="nombre" required maxlength="50" placeholder="Juan Carlos">
                            </div>
                            <div class="col-md-6">
                                <label for="clienteApellido" class="form-label">Apellido <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="clienteApellido" name="apellido" required maxlength="50" placeholder="Mora">
                            </div>
                            <div class="col-md-6">
                                <label for="clienteEmail" class="form-label">Email <span class="text-danger">*</span></label>
                                <input type="email" class="form-control" id="clienteEmail" name="email" required maxlength="150" placeholder="juan@email.com">
                            </div>
                            <div class="col-md-6">
                                <label for="clienteTelefono" class="form-label">Tel&eacute;fono <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="clienteTelefono" name="telefono" required maxlength="25" placeholder="8888-1111">
                            </div>
                            <div class="col-12">
                                <hr class="my-2">
                                <h6 class="text-muted"><i class="bi bi-geo-alt me-1"></i>Direcci&oacute;n (selects dependientes)</h6>
                            </div>
                            <div class="col-md-4">
                                <label for="clienteProvincia" class="form-label">Provincia</label>
                                <select class="form-select" id="clienteProvincia" name="provinciasIdProvincia">
                                    <option value="">-- Seleccionar --</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="clienteCanton" class="form-label">Cant&oacute;n</label>
                                <select class="form-select" id="clienteCanton" name="cantonesIdCanton" disabled>
                                    <option value="">-- Primero provincia --</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="clienteDistrito" class="form-label">Distrito</label>
                                <select class="form-select" id="clienteDistrito" name="distritosIdDistrito" disabled>
                                    <option value="">-- Primero cant&oacute;n --</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <label for="clienteOtrasSenas" class="form-label">Otras se&ntilde;as</label>
                                <input type="text" class="form-control" id="clienteOtrasSenas" name="otrasSenas" maxlength="250" placeholder="100m sur del parque central">
                            </div>
                            <div class="col-md-6">
                                <label for="clienteFechaIngreso" class="form-label">Fecha ingreso <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="clienteFechaIngreso" name="fechaIngreso" required>
                            </div>
                            <div class="col-md-6">
                                <label for="clienteEstado" class="form-label">Estado</label>
                                <select class="form-select" id="clienteEstado" name="estadosIdEstado">
                                    <option value="">-- Cargando... --</option>
                                </select>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" id="btnGuardarCliente">
                        <i class="bi bi-check-lg me-1"></i>Guardar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <?php include __DIR__ . '/../../components/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?php echo htmlspecialchars($basePath); ?>/js/app.js"></script>
    <script src="<?php echo htmlspecialchars($basePath); ?>/js/modules/clientes.js"></script>
</body>
</html>
