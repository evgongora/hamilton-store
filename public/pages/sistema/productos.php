<?php
/**
 * productos.php — Catálogo: productos y categorías (CRUD vía API Oracle).
 */
require_once __DIR__ . '/../../../backend/config/auth_guard.php';
requireRole(['admin', 'inventario', 'soporte']);

$basePath = dirname(dirname(dirname($_SERVER['SCRIPT_NAME'])));
if ($basePath === '/' || $basePath === '\\') {
    $basePath = '';
}

$pageTitle = 'Productos y categorías - M. Hamilton Store';
$currentPage = 'productos';
$user = $_SESSION['user'] ?? '';
$role = $_SESSION['role'] ?? '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <?php include __DIR__ . '/../../components/head.php'; ?>
</head>
<body
    class="app-layout bg-light"
    data-base-path="<?php echo htmlspecialchars($basePath); ?>"
    data-current-role="<?php echo htmlspecialchars($role); ?>"
>
    <?php include __DIR__ . '/../../components/navbar.php'; ?>
    <div class="app-main">
        <?php include __DIR__ . '/../../components/sidebar.php'; ?>
        <main class="app-content">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
                <h1 class="mb-0">Productos y categorías</h1>
            </div>
            <p class="text-muted small mb-3">
                Productos y categorías desde Oracle (<code>pkg_productos</code>, <code>pkg_ref_catalogos</code> para categorías).
            </p>

            <ul class="nav nav-tabs mb-3" id="productosModTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="tab-prod-btn" data-bs-toggle="tab" data-bs-target="#tab-pane-productos" type="button" role="tab">
                        <i class="bi bi-box-seam me-1"></i> Productos
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="tab-cat-btn" data-bs-toggle="tab" data-bs-target="#tab-pane-categorias" type="button" role="tab">
                        <i class="bi bi-tags me-1"></i> Categorías
                    </button>
                </li>
            </ul>

            <div class="tab-content">
                <div class="tab-pane fade show active" id="tab-pane-productos" role="tabpanel">
                    <div class="card shadow-sm border-0 mb-3">
                        <div class="card-body">
                            <div class="row g-2 align-items-end">
                                <div class="col-md-4">
                                    <label for="filtroProdBuscar" class="form-label small text-muted mb-0">Buscar</label>
                                    <input type="search" class="form-control" id="filtroProdBuscar" placeholder="Nombre del producto…" autocomplete="off">
                                </div>
                                <div class="col-md-3">
                                    <label for="filtroProdCategoria" class="form-label small text-muted mb-0">Categoría</label>
                                    <select class="form-select" id="filtroProdCategoria">
                                        <option value="">Todas</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label for="filtroProdEstado" class="form-label small text-muted mb-0">Estado</label>
                                    <select class="form-select" id="filtroProdEstado">
                                        <option value="">Todos</option>
                                    </select>
                                </div>
                                <div class="col-md-2 text-md-end">
                                    <button type="button" class="btn btn-outline-secondary btn-sm" id="btnProdLimpiarFiltros">Limpiar</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex justify-content-end mb-2">
                        <button type="button" class="btn btn-dark" id="btnNuevoProducto">
                            <i class="bi bi-plus-lg me-1"></i> Nuevo producto
                        </button>
                    </div>
                    <div id="productosSistemaTable" class="mt-1"></div>
                </div>

                <div class="tab-pane fade" id="tab-pane-categorias" role="tabpanel">
                    <div class="d-flex justify-content-end mb-2">
                        <button type="button" class="btn btn-dark" id="btnNuevaCategoria">
                            <i class="bi bi-plus-lg me-1"></i> Nueva categoría
                        </button>
                    </div>
                    <div id="categoriasSistemaTable" class="mt-1"></div>
                </div>
            </div>

            <!-- Modal producto -->
            <div class="modal fade" id="modalProducto" tabindex="-1" aria-labelledby="modalProductoLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="modalProductoLabel">Producto</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                        </div>
                        <div class="modal-body">
                            <input type="hidden" id="prodId" value="">
                            <div class="mb-3">
                                <label for="prodNombre" class="form-label">Nombre <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="prodNombre" maxlength="75" required>
                            </div>
                            <div class="row g-2">
                                <div class="col-md-6">
                                    <label for="prodPrecioCompra" class="form-label">Precio compra (₡)</label>
                                    <input type="number" class="form-control" id="prodPrecioCompra" min="0" step="0.01">
                                </div>
                                <div class="col-md-6">
                                    <label for="prodPrecioVenta" class="form-label">Precio venta (₡)</label>
                                    <input type="number" class="form-control" id="prodPrecioVenta" min="0" step="0.01">
                                </div>
                            </div>
                            <div class="mb-3 mt-2">
                                <label for="prodCantidad" class="form-label">Cantidad en stock</label>
                                <input type="number" class="form-control" id="prodCantidad" min="0" step="1">
                            </div>
                            <div class="mb-3">
                                <label for="prodIdCategoria" class="form-label">Categoría <span class="text-danger">*</span></label>
                                <select class="form-select" id="prodIdCategoria" required></select>
                            </div>
                            <div class="mb-0">
                                <label for="prodIdEstado" class="form-label">Estado <span class="text-danger">*</span></label>
                                <select class="form-select" id="prodIdEstado" required></select>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                            <button type="button" class="btn btn-dark" id="btnGuardarProducto">Guardar</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal categoría -->
            <div class="modal fade" id="modalCategoria" tabindex="-1" aria-labelledby="modalCategoriaLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="modalCategoriaLabel">Categoría</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                        </div>
                        <div class="modal-body">
                            <input type="hidden" id="catId" value="">
                            <div class="mb-0">
                                <label for="catNombre" class="form-label">Nombre <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="catNombre" maxlength="50" required>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                            <button type="button" class="btn btn-dark" id="btnGuardarCategoria">Guardar</button>
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
    <script src="<?php echo htmlspecialchars($basePath); ?>/js/modules/productos.js"></script>
    <script src="<?php echo htmlspecialchars($basePath); ?>/js/app.js"></script>
</body>
</html>
