<?php include_once $_SERVER["DOCUMENT_ROOT"] . "/hamilton-store/public/components/layout_tienda.php"; ?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
        <meta name="description" content="" />
        <meta name="author" content="" />
        <title>Inicio</title>
        <link rel="icon" type="image/x-icon" href="/hamilton-store/public/assets/img/favicon.png" />
        <?php MostrarCSS(); ?>
    </head>
    <body>
        <?php MostrarNavbar(); ?>

        <header class="bg-dark py-5">
            <div class="container px-4 px-lg-5 my-5">
                <div class="text-center text-white">
                    <h1 class="display-4 fw-bolder">Bienvenido a M. Hamilton Store</h1>
                    <p class="lead fw-normal text-white-50 mb-4">La tienda perfecta para tus compras de electr&oacute;nicos</p>
                </div>
            </div>
        </header>

        <section class="category-explorer py-5">
            <div class="container px-4 px-lg-5">
                <div class="text-center mb-4">
                    <h2 class="h3 fw-bolder mb-2">&iquest;Buscando algo en espec&iacute;fico?</h2>
                    <p class="text-muted mb-0">Navega por nuestras categor&iacute;as</p>
                </div>
                <div class="d-flex flex-wrap justify-content-center gap-2 category-buttons">
                    <a class="btn btn-outline-dark" href="#!">Celulares</a>
                    <a class="btn btn-outline-dark" href="/hamilton-store/public/pages/tienda/catalogo.php">Componentes PC</a>
                    <a class="btn btn-outline-dark" href="#!">Perif&eacute;ricos</a>
                    <a class="btn btn-outline-dark" href="#!">Accesorios</a>
                    <a class="btn btn-outline-dark" href="#!">Televisores y Monitores</a>
                </div>
            </div>
        </section>

        <section class="featured-products py-5">
            <div class="container px-4 px-lg-5">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-end mb-4 gap-2">
                    <div>
                        <h2 class="h3 fw-bolder mb-1">Productos Destacados</h2>
                        <p class="text-muted mb-0">Selecci&oacute;n curada con ofertas y productos top.</p>
                    </div>
                    <a href="/hamilton-store/public/pages/tienda/AllProducts.php" class="btn btn-outline-dark">Ver todo el cat&aacute;logo</a>
                </div>

                <div id="productsGrid" class="row gx-4 gx-lg-5 row-cols-1 row-cols-sm-2 row-cols-lg-4"></div>
            </div>
        </section>

        <section class="trust-section py-5">
            <div class="container px-4 px-lg-5">
                <div class="row g-4 text-center">
                    <div class="col-12 col-md-6 col-xl-3">
                        <div class="trust-item h-100">
                            <i class="bi bi-truck trust-icon"></i>
                            <h3 class="h5 fw-bold mt-3 mb-2">Env&iacute;o r&aacute;pido</h3>
                            <p class="text-muted mb-0">Despachos en 24-48h en zonas seleccionadas.</p>
                        </div>
                    </div>
                    <div class="col-12 col-md-6 col-xl-3">
                        <div class="trust-item h-100">
                            <i class="bi bi-shield-check trust-icon"></i>
                            <h3 class="h5 fw-bold mt-3 mb-2">Pago seguro</h3>
                            <p class="text-muted mb-0">Múltiples métodos de pago protegidos.</p>
                        </div>
                    </div>
                    <div class="col-12 col-md-6 col-xl-3">
                        <div class="trust-item h-100">
                            <i class="bi bi-patch-check trust-icon"></i>
                            <h3 class="h5 fw-bold mt-3 mb-2">Garant&iacute;a oficial</h3>
                            <p class="text-muted mb-0">Soporte y cobertura en productos seleccionados.</p>
                        </div>
                    </div>
                    <div class="col-12 col-md-6 col-xl-3">
                        <div class="trust-item h-100">
                            <i class="bi bi-headset trust-icon"></i>
                            <h3 class="h5 fw-bold mt-3 mb-2">Soporte experto</h3>
                            <p class="text-muted mb-0">Asesor&iacute;a para elegir el equipo adecuado.</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section id="arma-tu-pc" class="build-pc-section py-5">
            <div class="container px-4 px-lg-5">
                <div class="row align-items-center g-4">
                    <div class="col-lg-7">
                        <h2 class="fw-bolder mb-3">Arma tu computadora ideal</h2>
                        <p class="text-muted mb-4">Elige componentes compatibles seg&uacute;n tu presupuesto y uso: gaming, trabajo o estudio. Nosotros te guiamos paso a paso.</p>
                        <div class="d-flex flex-wrap gap-2 mb-4">
                            <span class="badge rounded-pill text-bg-light">CPU</span>
                            <span class="badge rounded-pill text-bg-light">GPU</span>
                            <span class="badge rounded-pill text-bg-light">RAM</span>
                            <span class="badge rounded-pill text-bg-light">Almacenamiento</span>
                            <span class="badge rounded-pill text-bg-light">Fuente</span>
                            <span class="badge rounded-pill text-bg-light">Gabinete</span>
                        </div>
                        <a class="btn btn-warning btn-lg build-pc-btn" href="#!">Comenzar configuraci&oacute;n</a>
                    </div>
                    <div class="col-lg-5">
                        <div class="build-pc-card p-4">
                            <h3 class="h5 fw-bold mb-3">&iquest;Qu&eacute; incluye?</h3>
                            <ul class="mb-0 build-pc-list">
                                <li>Compatibilidad autom&aacute;tica entre piezas.</li>
                                <li>Estimado de rendimiento.</li>
                                <li>Resumen de precio en tiempo real.</li>
                                <li>Recomendaciones por presupuesto.</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <?php MostrarFooter(); ?>
        <?php MostrarJS(); ?>
        <script>
        (function() {
          var badges = [
            { badge: 'Oferta', badgeClass: 'bg-danger', precioAntes: 699 },
            { badge: 'Nuevo', badgeClass: 'bg-success' },
            { badge: 'Top vendido', badgeClass: 'bg-dark', precioAntes: 109 },
            { badge: 'Oferta', badgeClass: 'bg-warning text-dark', precioAntes: 289 },
            { badge: 'Top vendido', badgeClass: 'bg-dark' },
            { badge: 'Nuevo', badgeClass: 'bg-success' },
            {},
            {},
            { badge: 'Oferta', badgeClass: 'bg-danger', precioAntes: 1499 },
            { badge: 'Top vendido', badgeClass: 'bg-dark' }
          ];
          function init() {
            if (window.TiendaProductos) {
              window.TiendaProductos.renderGrid('productsGrid', { badges: badges });
            }
          }
          if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', init);
          } else {
            init();
          }
        })();
        </script>
    </body>
</html>
