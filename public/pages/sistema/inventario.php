<?php
require_once __DIR__ . '/../../../backend/config/auth_guard.php';
requireRole(['admin', 'inventario', 'cajero']);
$basePath = dirname(dirname(dirname($_SERVER['SCRIPT_NAME'])));
$pageTitle = 'Inventario - M. Hamilton Store';
$currentPage = 'inventario';
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
            <section class="products-page" id="inventory-app">
                <h1 class="mb-4">Inventario</h1>

                <div class="row g-4 mb-4">
                    <div class="col-12 col-md-4">
                        <article class="card border-0 shadow-sm products-stat-card">
                            <div class="card-body">
                                <span class="products-stat-label">Productos Bajos en Stock</span>
                                <strong class="products-stat-value" id="inventory-stat-total">0</strong>
                            </div>
                        </article>
                    </div>
                    <div class="col-12 col-md-4">
                        <article class="card border-0 shadow-sm products-stat-card">
                            <div class="card-body">
                                <span class="products-stat-label">Unidades en inventario</span>
                                <strong class="products-stat-value" id="inventory-stat-stock">0</strong>
                            </div>
                        </article>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="bi bi-box me-2"></i>Inventario de productos</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Nombre</th>
                                        <th>Precio compra</th>
                                        <th>Precio venta</th>
                                        <th>Cantidad</th>
                                        <th>Stock</th>
                                        <th>Estado</th>
                                        <th>Categoria</th>
                                    </tr>
                                </thead>
                                <tbody id="inventory-body"></tbody>
                                <tfoot class="table-light">
                                    <tr>
                                        <td colspan="7" class="text-muted small" id="inventory-count">0 productos</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                        <div id="inventory-empty" class="text-center text-muted py-5">
                            <i class="bi bi-box display-4"></i>
                            <p class="mt-2 mb-0">No hay productos registrados.</p>
                        </div>
                    </div>
                </div>
            </section>
        </main>
    </div>
    <?php include __DIR__ . '/../../components/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?php echo htmlspecialchars($basePath); ?>/js/app.js"></script>
    <script src="<?php echo htmlspecialchars($basePath); ?>/js/modules/inventario.js"></script>
</body>
</html>
