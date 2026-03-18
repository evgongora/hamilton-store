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
<body class="app-layout bg-light">
    <?php include __DIR__ . '/../../components/navbar.php'; ?>
    <div class="app-main">
        <?php include __DIR__ . '/../../components/sidebar.php'; ?>
        <main class="app-content">
            <section class="products-page" id="catalog-app" data-base-path="<?php echo htmlspecialchars($basePath); ?>">
                <div class="products-hero mb-4">
                    <div>
                        <h1 class="mb-2" id="catalog-page-title">Productos y Categorias</h1>
                    </div>
                    <button class="btn btn-primary products-primary-action" type="button" id="catalog-new-trigger">
                        <i class="bi bi-plus-circle me-2"></i><span id="catalog-new-label">Nuevo producto</span>
                    </button>
                </div>

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

                <div class="row g-4">
                    <div class="col-12">
                        <section class="card border-0 shadow-sm products-panel">
                            <div class="card-body">
                                <div class="products-toolbar mb-3">
                                    <div>
                                        <h2 class="h4 mb-1" id="catalog-list-title">Listado de productos</h2>
                                        <p class="text-muted mb-0" id="catalog-list-description">Columnas solicitadas: nombre, precios, cantidad, estado y categoria.</p>
                                    </div>
                                    <div class="products-search">
                                        <label class="form-label visually-hidden" for="catalog-search-input">Buscar</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bi bi-search"></i></span>
                                            <input class="form-control" id="catalog-search-input" type="search" placeholder="Buscar por nombre o estado">
                                        </div>
                                    </div>
                                </div>

                                <div class="table-responsive">
                                    <table class="table align-middle products-table mb-0" id="catalog-table">
                                        <thead id="catalog-table-head"></thead>
                                        <tbody id="catalog-table-body"></tbody>
                                    </table>
                                </div>
                                <p class="text-muted small mb-0 mt-3" id="catalog-empty-state" hidden>No hay registros para mostrar.</p>
                            </div>
                        </section>
                    </div>
                </div>
            </section>
        </main>
    </div>

    <div class="modal fade" id="catalogFormModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow">
                <div class="modal-header border-0 pb-0">
                    <div>
                        <h2 class="modal-title h4 mb-1" id="catalog-form-title">Agregar producto</h2>
                        <p class="text-muted mb-0" id="catalog-form-description">Completa los datos requeridos para registrar un producto.</p>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body pt-3">
                    <div class="mb-3">
                        <span class="badge text-bg-light border" id="catalog-form-badge">Nuevo</span>
                    </div>

                    <form id="product-form" novalidate>
                        <input type="hidden" id="product-id" name="id">

                        <div class="mb-3">
                            <label class="form-label" for="product-nombre">Nombre</label>
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
                                <label class="form-label" for="product-cantidad">Cantidad</label>
                                <input class="form-control" id="product-cantidad" name="cantidad" type="number" min="0" step="1" required>
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label" for="product-categoria">Categoria ID</label>
                                <input class="form-control" id="product-categoria" name="categorias_id_categoria" type="number" min="1" step="1" required>
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
                            <label class="form-label" for="category-nombre">Nombre de la categoria</label>
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
            <div class="modal-content border-0 shadow">
                <div class="modal-header border-0 pb-0">
                    <h2 class="modal-title h5 mb-0">Confirmar eliminacion</h2>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body pt-2">
                    <p class="mb-2" id="delete-confirm-message">Estas a punto de eliminar este registro.</p>
                    <p class="text-muted small mb-0">Esta accion solo afecta el frontend actual.</p>
                </div>
                <div class="modal-footer border-0 pt-0">
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
