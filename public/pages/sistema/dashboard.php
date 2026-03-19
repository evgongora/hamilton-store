<?php
/**
 * dashboard.php - Panel principal con métricas del sistema
 */
require_once __DIR__ . '/../../../backend/config/auth_guard.php';
requireRole(['admin']);

$basePath = dirname(dirname(dirname($_SERVER['SCRIPT_NAME'])));
if ($basePath === '/' || $basePath === '\\') $basePath = '';
$pageTitle = 'Dashboard - M. Hamilton Store';
$currentPage = 'dashboard';
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
            <h1 class="mb-4">Dashboard</h1>
            <p class="text-muted mb-4">Bienvenido, <strong><?php echo htmlspecialchars($user); ?></strong>. Rol: <strong><?php echo htmlspecialchars($role); ?></strong>.</p>

            <div class="row g-4 mb-4">
                <div class="col-sm-6 col-lg-3">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="text-muted mb-1">Total ventas</h6>
                                    <h3 class="mb-0" id="dashTotalVentas">₡0</h3>
                                </div>
                                <div class="rounded-circle bg-success bg-opacity-25 p-3">
                                    <i class="bi bi-receipt text-success fs-4"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-3">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="text-muted mb-1">Total pagos</h6>
                                    <h3 class="mb-0" id="dashTotalPagos">₡0</h3>
                                </div>
                                <div class="rounded-circle bg-primary bg-opacity-25 p-3">
                                    <i class="bi bi-credit-card text-primary fs-4"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-3">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="text-muted mb-1">Clientes</h6>
                                    <h3 class="mb-0" id="dashClientes">0</h3>
                                </div>
                                <div class="rounded-circle bg-info bg-opacity-25 p-3">
                                    <i class="bi bi-people text-info fs-4"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-3">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="text-muted mb-1">Empleados</h6>
                                    <h3 class="mb-0" id="dashEmpleados">0</h3>
                                </div>
                                <div class="rounded-circle bg-warning bg-opacity-25 p-3">
                                    <i class="bi bi-person-badge text-warning fs-4"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header bg-dark text-white">
                    <h5 class="mb-0"><i class="bi bi-clock-history me-2"></i>Últimas ventas</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>ID</th>
                                    <th>Fecha</th>
                                    <th>Cliente</th>
                                    <th>Origen</th>
                                    <th class="text-end">Total</th>
                                </tr>
                            </thead>
                            <tbody id="dashVentasBody"></tbody>
                        </table>
                    </div>
                    <div id="dashVentasEmpty" class="text-center text-muted py-4">
                        <i class="bi bi-receipt"></i>
                        <p class="mb-0 mt-2">Sin ventas recientes</p>
                    </div>
                </div>
            </div>
        </main>
    </div>
    <?php include __DIR__ . '/../../components/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?php echo htmlspecialchars($basePath); ?>/js/app.js"></script>
    <script src="<?php echo htmlspecialchars($basePath); ?>/js/modules/dashboard.js"></script>
</body>
</html>
