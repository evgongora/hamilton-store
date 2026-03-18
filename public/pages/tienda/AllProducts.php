<?php include_once $_SERVER["DOCUMENT_ROOT"] . "/hamilton-store/public/components/layout_tienda.php"; ?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
        <meta name="description" content="" />
        <meta name="author" content="" />
        <title>Todos los productos</title>
        <link rel="icon" type="image/x-icon" href="/hamilton-store/public/assets/img/favicon.png" />
        <?php MostrarCSS(); ?>
    </head>
    <body>
        <?php MostrarNavbar(); ?>

        <header class="bg-dark py-5">
            <div class="container px-4 px-lg-5 my-5">
                <div class="text-center text-white">
                    <h1 class="display-5 fw-bolder">Todos los productos</h1>
                    <p class="lead fw-normal text-white-50 mb-0">Explora nuestro cat&aacute;logo completo</p>
                </div>
            </div>
        </header>

        <section class="py-5">
            <div class="container px-4 px-lg-5">
                <div id="productsGrid" class="row gx-4 gx-lg-5 row-cols-2 row-cols-md-3 row-cols-xl-4"></div>
            </div>
        </section>

        <?php MostrarFooter(); ?>
        <?php MostrarJS(); ?>
        <script>if (window.TiendaProductos) window.TiendaProductos.renderGrid('productsGrid');</script>
    </body>
</html>
