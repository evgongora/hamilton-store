<?php
/**
 * Checkout tienda — cliente logueado vía sesión PHP (auth_login); datos de cliente desde Oracle.
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$basePathCheckout = dirname(dirname(dirname($_SERVER['SCRIPT_NAME'])));
if ($basePathCheckout === '/' || $basePathCheckout === '\\') {
    $basePathCheckout = '';
}

if (($_SESSION['role'] ?? '') !== 'cliente' || empty($_SESSION['cliente_id'])) {
    header('Location: ' . $basePathCheckout . '/pages/auth/login.php?next=checkout');
    exit;
}

$cid = (int) $_SESSION['cliente_id'];
/** Siempre enviar id a JS: el pago usa Oracle aunque falle el SELECT de nombre. */
$checkoutClienteSession = [
    'id'       => $cid,
    'nombre'   => '',
    'apellido' => '',
];
require_once __DIR__ . '/../../../backend/config/db.php';
$conn = hamilton_db();
if ($conn) {
    $sql = 'SELECT nombre, apellido FROM clientes WHERE id_cliente = :id';
    $st = oci_parse($conn, $sql);
    if ($st) {
        oci_bind_by_name($st, ':id', $cid);
        if (@oci_execute($st)) {
            $row = oci_fetch_assoc($st);
            if ($row !== false) {
                $row = array_change_key_case($row, CASE_LOWER);
                $checkoutClienteSession['nombre'] = (string) ($row['nombre'] ?? '');
                $checkoutClienteSession['apellido'] = (string) ($row['apellido'] ?? '');
            }
        }
        oci_free_statement($st);
    }
}

require_once __DIR__ . '/../../components/layout_tienda.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <title>Checkout - M. Hamilton Store</title>
    <link rel="icon" type="image/x-icon" href="/hamilton-store/public/assets/img/favicon.png" />
    <?php MostrarCSS(); ?>
</head>
<body>
    <?php MostrarNavbar(); ?>

    <?php if (!empty($checkoutClienteSession)): ?>
    <div class="container px-4 px-lg-5 pt-3">
        <div class="alert alert-secondary border small mb-0 py-2">
            <i class="bi bi-person-check me-1"></i>
            Comprando como <strong><?php echo htmlspecialchars(trim($checkoutClienteSession['nombre'] . ' ' . $checkoutClienteSession['apellido'])); ?></strong>
        </div>
    </div>
    <?php endif; ?>

    <header class="bg-dark py-4">
        <div class="container px-4 px-lg-5">
            <h1 class="text-white mb-0">Checkout</h1>
            <p class="text-white-50 mb-0">Finaliza tu compra</p>
        </div>
    </header>

    <section class="py-5">
        <div class="container px-4 px-lg-5">
            <div id="checkoutEmpty" class="text-center py-5">
                <i class="bi bi-cart-x display-1 text-muted"></i>
                <h3 class="mt-3">Tu carrito est&aacute; vac&iacute;o</h3>
                <a href="/hamilton-store/public/pages/tienda/Homepage.php" class="btn btn-dark mt-3">Ver productos</a>
            </div>

            <div id="checkoutContent" class="row g-4">
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-header bg-dark text-white">
                            <h5 class="mb-0"><i class="bi bi-cart me-2"></i>Resumen del carrito</h5>
                        </div>
                        <div class="card-body p-0">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Producto</th>
                                        <th class="text-end">Cant.</th>
                                        <th class="text-end">Precio</th>
                                        <th class="text-end">Subtotal</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody id="checkoutItems"></tbody>
                                <tfoot class="table-light">
                                    <tr>
                                        <td colspan="3" class="fw-bold">Total</td>
                                        <td id="checkoutTotal" class="text-end fw-bold">₡0</td>
                                        <td></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="card sticky-top">
                        <div class="card-header bg-dark text-white">
                            <h5 class="mb-0"><i class="bi bi-credit-card me-2"></i>Pago</h5>
                        </div>
                        <div class="card-body">
                            <p class="text-muted small">M&eacute;todos desde Oracle. La venta y el cobro se registran al confirmar.</p>
                            <div class="mb-4">
                                <label class="form-label">M&eacute;todo de pago</label>
                                <select id="metodoPago" class="form-select">
                                    <option value="">-- Cargando... --</option>
                                </select>
                            </div>
                            <hr>
                            <button type="button" id="btnPagar" class="btn btn-success btn-lg w-100">
                                <i class="bi bi-lock-fill me-2"></i>Pagar ahora
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div id="checkoutSuccess" class="text-center py-5" style="display: none;">
                <i class="bi bi-check-circle-fill display-1 text-success"></i>
                <h3 class="mt-3">¡Pago exitoso!</h3>
                <p class="text-muted">Tu orden ha sido procesada correctamente.</p>
                <a href="/hamilton-store/public/pages/tienda/Homepage.php" class="btn btn-dark mt-3">Seguir comprando</a>
            </div>
        </div>
    </section>

    <?php MostrarFooter(); ?>
    <?php MostrarJS(); ?>
    <script src="<?php echo htmlspecialchars($basePathCheckout); ?>/js/utils/validation-helpers.js"></script>
    <script>
    window.HAMILTON_CHECKOUT_CLIENTE = <?php echo json_encode($checkoutClienteSession, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP); ?>;
    </script>
    <script src="/hamilton-store/public/js/modules/tienda-checkout.js"></script>
</body>
</html>
