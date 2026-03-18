<?php include_once $_SERVER["DOCUMENT_ROOT"] . "/hamilton-store/public/components/layout_tienda.php"; ?>
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
                            <h5 class="mb-0"><i class="bi bi-credit-card me-2"></i>Pasarela de pagos (mock)</h5>
                        </div>
                        <div class="card-body">
                            <p class="text-muted small">Proyecto universitario - simulaci&oacute;n de pago</p>
                            <div class="mb-4">
                                <label class="form-label">M&eacute;todo de pago</label>
                                <select id="metodoPago" class="form-select">
                                    <option value="">-- Cargando... --</option>
                                </select>
                            </div>
                            <div id="mockTarjeta" class="mb-3">
                                <div class="mb-2">
                                    <label class="form-label small">N&uacute;mero de tarjeta</label>
                                    <input type="text" class="form-control" placeholder="**** **** **** ****" maxlength="19" value="4111 1111 1111 1111">
                                </div>
                                <div class="row g-2">
                                    <div class="col-6">
                                        <label class="form-label small">Vencimiento</label>
                                        <input type="text" class="form-control" placeholder="MM/AA" value="12/28">
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label small">CVV</label>
                                        <input type="text" class="form-control" placeholder="***" value="123">
                                    </div>
                                </div>
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
    <script src="/hamilton-store/public/js/modules/tienda-checkout.js"></script>
</body>
</html>
