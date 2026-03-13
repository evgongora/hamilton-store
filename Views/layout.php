<?php
    function MostrarCSS()
    {
        echo '
        <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.5.0/font/bootstrap-icons.css" rel="stylesheet" />
        <link href="/Proyecto/hamilton-store/Views/assets/css/styles.css" rel="stylesheet" />
';
    }

    function MostrarNavbar() {
        echo '<nav class="navbar navbar-expand-lg navbar-light bg-light">
            <div class="container px-4 px-lg-5">
                <a class="navbar-brand" href="Homepage.php"> <img src="../assets/img/Header-logo.png" alt="Logo" /></a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation"><span class="navbar-toggler-icon"></span></button>
                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <ul class="navbar-nav me-auto mb-2 mb-lg-0 ms-lg-4">
                        <li class="nav-item"><a class="nav-link active" aria-current="page" href="Homepage.php">Inicio</a></li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" id="navbarDropdown" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">Productos</a>
                            <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
                                <li><a class="dropdown-item" href="AllProducts.php">Todos los productos</a></li>
                                <li><hr class="dropdown-divider" /></li>
                                <li><a class="dropdown-item" href="#!">Celulares</a></li>
                                <li><a class="dropdown-item" href="#!">Componentes PC</a></li>
                                <li><a class="dropdown-item" href="#!">Perif&eacute;ricos</a></li>
                                <li><a class="dropdown-item" href="#!">Accesorios</a></li>
                                <li><a class="dropdown-item" href="#!">Televisores y Monitores</a></li>
                            </ul>
                        </li>
                    </ul>
                    <form class="d-flex navbar-search-form" role="search" onsubmit="return false;">
                        <input class="form-control" id="productSearchInput" type="search" placeholder="Buscar productos" aria-label="Buscar productos" />
                    </form>
                    <div class="navbar-right-actions">
                        <form class="d-flex navbar-cart-form">
                            <button class="btn btn-outline-dark" type="submit">
                                <i class="bi-cart-fill me-1"></i>
                                Carrito
                                <span class="badge bg-dark text-white ms-1 rounded-pill">0</span>
                            </button>
                        </form>
                        <a class="btn btn-outline-secondary navbar-login-btn" href="#!">Iniciar sesión</a>
                        <a class="navbar-profile-slot" href="#!" aria-label="Perfil">
                            <i class="bi bi-person-fill"></i>
                        </a>
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
        echo ' <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
        <script src="assets/js/scripts.js"></script>';
    }

?></php>

