<?php
/** Layout tienda pública - navbar, footer, CSS/JS compartidos */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$base = '/hamilton-store/public';

function MostrarCSS() {
    $base = '/hamilton-store/public';
    echo '<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.5.0/font/bootstrap-icons.css" rel="stylesheet" />
    <link href="' . $base . '/css/styles.css" rel="stylesheet" />';
}

function MostrarNavbar() {
    $base = '/hamilton-store/public';
    echo '<nav class="navbar navbar-expand-lg navbar-light bg-light">
        <div class="container px-4 px-lg-5">
            <a class="navbar-brand" href="' . $base . '/pages/tienda/Homepage.php"> <img src="' . $base . '/assets/img/Header-logo.png" alt="Logo" /></a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation"><span class="navbar-toggler-icon"></span></button>
            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0 ms-lg-4">
                    <li class="nav-item"><a class="nav-link active" aria-current="page" href="' . $base . '/pages/tienda/Homepage.php">Inicio</a></li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" id="navbarDropdown" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">Productos</a>
                        <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
                            <li><a class="dropdown-item" href="' . $base . '/pages/tienda/AllProducts.php">Todos los productos</a></li>
                            <li><hr class="dropdown-divider" /></li>
                            <li><a class="dropdown-item" href="#!">Celulares</a></li>
                            <li><a class="dropdown-item" href="' . $base . '/pages/tienda/catalogo.php">Componentes PC</a></li>
                            <li><a class="dropdown-item" href="#!">Perif&eacute;ricos</a></li>
                            <li><a class="dropdown-item" href="#!">Accesorios</a></li>
                            <li><a class="dropdown-item" href="#!">Televisores y Monitores</a></li>
                        </ul>
                    </li>
                </ul>
                <form class="d-flex navbar-search-form" role="search" onsubmit="return false;">
                    <input class="form-control" id="productSearchInput" type="search" placeholder="Buscar productos" aria-label="Buscar productos" />
                </form>
                <div class="navbar-right-actions">' .
                    ((($_SESSION['role'] ?? '') === 'cliente')
                        ? '<a class="btn btn-outline-dark navbar-cart-form" href="' . $base . '/pages/tienda/checkout.php">
                        <i class="bi-cart-fill me-1"></i>
                        Carrito
                        <span id="cartBadge" class="badge bg-dark text-white ms-1 rounded-pill">0</span>
                    </a>'
                        : '') .
                    (empty($_SESSION['user'])
                        ? '<a class="btn btn-outline-dark btn-sm me-2" href="' . $base . '/pages/auth/registro_cliente.php"><i class="bi bi-person-plus me-1"></i>Crear cuenta</a>'
                        . '<a class="btn btn-dark navbar-login-btn" href="' . $base . '/pages/auth/login.php"><i class="bi bi-box-arrow-in-right me-1"></i>Iniciar sesión</a>'
                        : ('<span class="text-muted me-2 small">' . htmlspecialchars($_SESSION['user']) . '</span>' .
                          (in_array($_SESSION['role'] ?? '', ['admin', 'cajero', 'inventario', 'soporte'], true)
                              ? '<a class="btn btn-outline-primary btn-sm me-2" href="' . $base . '/pages/sistema/dashboard.php">Dashboard</a>'
                              : '') .
                          '<a class="btn btn-outline-secondary btn-sm" href="' . str_replace('/public', '/backend', $base) . '/api/auth_logout.php">Cerrar sesión</a>')
                    ) . '
                </div>
            </div>
        </div>
    </nav>';
}

function MostrarFooter() {
    echo '<footer class="py-5 bg-dark">
        <div class="container"><p class="m-0 text-center text-white">Copyright &copy; M. Hamilton Store 2026</p></div>
    </footer>';
}

function MostrarJS() {
    $base = '/hamilton-store/public';
    $paths = require __DIR__ . '/../../backend/config/paths.php';
    $api = $paths['api'];
    $puedeComprar = (($_SESSION['role'] ?? '') === 'cliente');
    $loginUrl = $base . '/pages/auth/login.php';
    $registroUrlTienda = $base . '/pages/auth/registro_cliente.php';
    echo '<script>window.API_BASE=' . json_encode($api, JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP) . ';'
        . 'window.HAMILTON_TIENDA_PUEDE_COMPRAR=' . ($puedeComprar ? 'true' : 'false') . ';'
        . 'window.HAMILTON_LOGIN_URL=' . json_encode($loginUrl, JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP) . ';'
        . 'window.HAMILTON_REGISTRO_URL=' . json_encode($registroUrlTienda, JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP) . ';</script>';
    echo '<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="' . $base . '/js/ui-dialog.js"></script>
    <script src="' . $base . '/js/services/api.js"></script>
    <script src="' . $base . '/js/modules/tienda-carrito.js"></script>
    <script src="' . $base . '/js/modules/tienda-productos.js?v=5"></script>
    <script src="' . $base . '/js/scripts.js"></script>';
}
?>
