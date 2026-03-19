<?php
require_once __DIR__ . '/../../../backend/config/auth_guard.php';
requireRole(['admin', 'inventario']);
$basePath = dirname(dirname(dirname($_SERVER['SCRIPT_NAME'])));
$pageTitle = 'Compras - M. Hamilton Store';
$currentPage = 'compras';
$user = $_SESSION['user'] ?? '';
$role = $_SESSION['role'] ?? '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <?php include __DIR__ . '/../../components/head.php'; ?>
</head>
<body class="app-layout bg-light" data-base-path="<?php echo htmlspecialchars($basePath); ?>" data-current-user="<?php echo htmlspecialchars($user); ?>" data-current-role="<?php echo htmlspecialchars($role); ?>">
    <?php include __DIR__ . '/../../components/navbar.php'; ?>
    <div class="app-main">
        <?php include __DIR__ . '/../../components/sidebar.php'; ?>
        <main class="app-content">
            <h1 class="mb-4">Compras</h1>

            <div class="row g-4">
                <div class="col-lg-4">
                    <div class="card mb-4">
                        <div class="card-header bg-dark text-white">
                            <h5 class="mb-0"><i class="bi bi-person-badge me-2"></i>Registro</h5>
                        </div>
                        <div class="card-body">
                            <div class="small text-muted">Compra registrada por</div>
                            <div class="fw-semibold"><?php echo htmlspecialchars($user ?: 'Usuario actual'); ?></div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header bg-dark text-white">
                            <h5 class="mb-0"><i class="bi bi-bag-plus me-2"></i>Nueva compra</h5>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-12">
                                    <label for="productoSelect" class="form-label">Producto</label>
                                    <select id="productoSelect" class="form-select">
                                        <option value="">-- Seleccionar producto --</option>
                                    </select>
                                </div>
                                <div class="col-12">
                                    <label for="proveedorSelect" class="form-label">Proveedor</label>
                                    <select id="proveedorSelect" class="form-select">
                                        <option value="">-- Seleccionar proveedor --</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="cantidadInput" class="form-label">Cantidad</label>
                                    <input type="number" id="cantidadInput" class="form-control" min="1" step="1" value="1">
                                </div>
                                <div class="col-md-6">
                                    <label for="precioUnitario" class="form-label">Precio unitario</label>
                                    <input type="text" id="precioUnitario" class="form-control" value="₡0.00" readonly>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer bg-white">
                            <div class="d-flex justify-content-between small text-muted mb-2">
                                <span>Total a registrar</span>
                                <span id="summaryTotal">₡0.00</span>
                            </div>
                            <button type="button" id="btnRegistrarCompra" class="btn btn-success btn-lg w-100" disabled>
                                <i class="bi bi-check-circle me-2"></i>Registrar Compra
                            </button>
                        </div>
                    </div>
                </div>

                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                            <h5 class="mb-0"><i class="bi bi-clock-history me-2"></i>Historial de compras</h5>
                            <span id="historyCount" class="badge bg-light text-dark">0</span>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Fecha</th>
                                            <th>Usuario</th>
                                            <th>Proveedor</th>
                                            <th>Producto</th>
                                            <th class="text-end">Cant.</th>
                                            <th class="text-end">P. unit.</th>
                                            <th class="text-end">Total</th>
                                            <?php if ($role === 'admin'): ?>
                                                <th class="text-end">Acciones</th>
                                            <?php endif; ?>
                                        </tr>
                                    </thead>
                                    <tbody id="historyBody"></tbody>
                                </table>
                            </div>
                            <div id="historyEmpty" class="text-center text-muted py-4">No hay compras registradas</div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
    <?php include __DIR__ . '/../../components/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?php echo htmlspecialchars($basePath); ?>/js/app.js"></script>
    <script src="<?php echo htmlspecialchars($basePath); ?>/js/modules/compras.js"></script>
</body>
</html>
