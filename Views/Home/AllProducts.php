<?php include_once $_SERVER["DOCUMENT_ROOT"] . "/Proyecto/hamilton-store/Views/layout.php"; ?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
        <meta name="description" content="" />
        <meta name="author" content="" />
        <title>Todos los productos</title>
        <link rel="icon" type="image/x-icon" href="../assets/img/favicon.png" />
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
                <div class="row g-4">
                    <div class="col-md-4">
                        <div class="card h-100">
                            <div class="card-body">
                                <h2 class="h5">Celulares</h2>
                                <p class="text-muted mb-0">Modelos de entrada, gama media y flagship.</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card h-100">
                            <div class="card-body">
                                <h2 class="h5">Componentes PC</h2>
                                <p class="text-muted mb-0">CPU, GPU, RAM, almacenamiento y m&aacute;s.</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card h-100">
                            <div class="card-body">
                                <h2 class="h5">Perif&eacute;ricos</h2>
                                <p class="text-muted mb-0">Teclados, mouse, audio y accesorios gamer.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <?php MostrarFooter(); ?>
        <?php MostrarJS(); ?>
    </body>
</html>
