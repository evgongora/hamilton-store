<?php
/**
 * productos.php - Modulo de productos y categorias (sistema)
 */
require_once __DIR__ . '/../../../backend/config/auth_guard.php';
requireLogin();

$basePath = dirname(dirname(dirname($_SERVER['SCRIPT_NAME'])));
$pageTitle = 'Productos - M. Hamilton Store';
$currentPage = 'productos';
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
            <section class="products-page" id="catalog-app">
                <h1 class="mb-4" id="catalog-page-title">Productos y Categorias</h1>

                <div class="catalog-switcher mb-4" role="tablist" aria-label="Selector de catalogo">
                    <button class="btn catalog-switch active" type="button" data-view="products">Productos</button>
                    <button class="btn catalog-switch" type="button" data-view="categories">Categorias</button>
                </div>

                <div class="row g-4 mb-4" id="catalog-stats">
                    <div class="col-12 col-md-4">
                        <article class="card border-0 shadow-sm products-stat-card">
                            <div class="card-body">
                                <span class="products-stat-label" id="stat-label-1">Productos registrados</span>
                                <strong class="products-stat-value" id="stat-total">0</strong>
                            </div>
                        </article>
                    </div>
                    <div class="col-12 col-md-4">
                        <article class="card border-0 shadow-sm products-stat-card">
                            <div class="card-body">
                                <span class="products-stat-label" id="stat-label-2">Unidades en inventario</span>
                                <strong class="products-stat-value" id="stat-stock">0</strong>
                            </div>
                        </article>
                    </div>
                    <div class="col-12 col-md-4">
                        <article class="card border-0 shadow-sm products-stat-card">
                            <div class="card-body">
                                <span class="products-stat-label" id="stat-label-3">Categorias activas</span>
                                <strong class="products-stat-value" id="stat-categories">0</strong>
                            </div>
                        </article>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0" id="catalog-list-title"><i class="bi bi-box me-2"></i>Listado de productos</h5>
                        <button type="button" class="btn btn-light btn-sm" id="catalog-new-trigger">
                            <i class="bi bi-plus-lg me-1"></i><span id="catalog-new-label">Nuevo producto</span>
                        </button>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light" id="catalog-table-head"></thead>
                                <tbody id="catalog-table-body"></tbody>
                                <tfoot class="table-light">
                                    <tr>
                                        <td colspan="7" class="text-muted small" id="catalog-count">0 productos</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                        <div id="catalog-empty-state" class="text-center text-muted py-5">
                            <i class="bi bi-box display-4" id="catalog-empty-icon"></i>
                            <p class="mt-2 mb-0" id="catalog-empty-text">No hay productos registrados. <a href="#" id="catalog-empty-link">Agregar uno</a></p>
                        </div>
                    </div>
                </div>
            </section>
        </main>
    </div>

    <div class="modal fade" id="catalogFormModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-dark text-white">
                    <div>
                        <h2 class="modal-title h4 mb-1" id="catalog-form-title"><i class="bi bi-box-seam me-2"></i>Nuevo producto</h2>
                        <p class="mb-0 text-white-50" id="catalog-form-description">Completa los datos requeridos para registrar un producto.</p>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <span class="badge text-bg-light border" id="catalog-form-badge">Nuevo</span>
                    </div>

                    <form id="product-form" novalidate>
                        <input type="hidden" id="product-id" name="id">

                        <div class="mb-3">
                            <label class="form-label" for="product-nombre">Nombre <span class="text-danger">*</span></label>
                            <input class="form-control" id="product-nombre" name="nombre" type="text" maxlength="75" required>
                        </div>

                        <div class="row g-3">
                            <div class="col-12 col-md-6">
                                <label class="form-label" for="product-precio-compra">Precio compra</label>
                                <input class="form-control" id="product-precio-compra" name="precio_compra" type="number" min="0" step="0.01">
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label" for="product-precio-venta">Precio venta</label>
                                <input class="form-control" id="product-precio-venta" name="precio_venta" type="number" min="0" step="0.01">
                            </div>
                        </div>

                        <div class="row g-3 mt-0">
                            <div class="col-12 col-md-6">
                                <label class="form-label" for="product-cantidad">Cantidad <span class="text-danger">*</span></label>
                                <input class="form-control" id="product-cantidad" name="cantidad" type="number" min="0" step="1" required>
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label" for="product-categoria">Categoria <span class="text-danger">*</span></label>
                                <select class="form-select" id="product-categoria" name="categorias_id_categoria" required>
                                    <option value="">-- Seleccionar categoria --</option>
                                </select>
                            </div>
                        </div>

                        <div class="mt-3">
                            <label class="form-label" for="product-estado">Estado</label>
                            <select class="form-select" id="product-estado" name="estado" required>
                                <option value="activo">activo</option>
                                <option value="inactivo">inactivo</option>
                                <option value="agotado">agotado</option>
                            </select>
                        </div>

                        <div class="d-flex flex-wrap gap-2 mt-4">
                            <button class="btn btn-primary" type="submit" id="product-submit-button">
                                <i class="bi bi-save me-2"></i>Guardar producto
                            </button>
                            <button class="btn btn-outline-secondary" type="button" id="product-cancel-edit">Cancelar</button>
                        </div>
                    </form>

                    <form id="category-form" novalidate hidden>
                        <input type="hidden" id="category-id" name="id">

                        <div class="mb-3">
                            <label class="form-label" for="category-nombre">Nombre de la categoria <span class="text-danger">*</span></label>
                            <input class="form-control" id="category-nombre" name="nombre" type="text" maxlength="75" required>
                        </div>

                        <div class="d-flex flex-wrap gap-2 mt-4">
                            <button class="btn btn-primary" type="submit" id="category-submit-button">
                                <i class="bi bi-save me-2"></i>Guardar categoria
                            </button>
                            <button class="btn btn-outline-secondary" type="button" id="category-cancel-edit">Cancelar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-dark text-white">
                    <h2 class="modal-title h5 mb-0">Confirmar eliminacion</h2>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-2" id="delete-confirm-message">Estas a punto de eliminar este registro.</p>
                    <p class="text-muted small mb-0">Esta accion solo afecta el frontend actual.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-danger" id="delete-confirm-button">Eliminar</button>
                </div>
            </div>
        </div>
    </div>

    <?php include __DIR__ . '/../../components/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?php echo htmlspecialchars($basePath); ?>/js/app.js"></script>
    <script src="<?php echo htmlspecialchars($basePath); ?>/js/modules/productos.js"></script>
</body>
</html>
