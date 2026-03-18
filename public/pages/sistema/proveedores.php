<?php
require_once __DIR__ . '/../../../backend/config/auth_guard.php';
requireLogin();
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
<body class="app-layout bg-light" data-base-path="<?php echo htmlspecialchars($basePath); ?>">
    <?php include __DIR__ . '/../../components/navbar.php'; ?>
    <div class="app-main">
        <?php include __DIR__ . '/../../components/sidebar.php'; ?>
        <main class="app-content">
            <h1 class="mb-4">Proveedores</h1>

            <div class="card">
                <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-truck me-2"></i>Listado de proveedores</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Proveedor</th>
                                    <th>Contacto</th>
                                    <th>Telefono</th>
                                    <th>Email</th>
                                </tr>
                            </thead>
                            <tbody id="proveedoresBody"></tbody>
                            <tfoot class="table-light">
                                <tr>
                                    <td colspan="4" class="text-muted small" id="proveedoresCount">0 proveedores</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    <div id="proveedoresEmpty" class="text-center text-muted py-5">
                        <i class="bi bi-truck display-4"></i>
                        <p class="mt-2 mb-0">No hay proveedores registrados.</p>
                    </div>
                </div>
            </div>
        </main>
    </div>
    <?php include __DIR__ . '/../../components/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?php echo htmlspecialchars($basePath); ?>/js/app.js"></script>
    <script src="<?php echo htmlspecialchars($basePath); ?>/js/modules/proveedores.js"></script>
</body>
</html>
