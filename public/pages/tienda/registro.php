<?php include_once $_SERVER["DOCUMENT_ROOT"] . "/hamilton-store/public/components/layout_tienda.php"; ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <title>Registro - M. Hamilton Store</title>
    <link rel="icon" type="image/x-icon" href="/hamilton-store/public/assets/img/favicon.png" />
    <?php MostrarCSS(); ?>
</head>
<body>
    <?php MostrarNavbar(); ?>

    <header class="bg-dark py-4">
        <div class="container px-4 px-lg-5">
            <h1 class="text-white mb-0">Crear cuenta</h1>
            <p class="text-white-50 mb-0">Regístrate para comprar en la tienda</p>
        </div>
    </header>

    <section class="py-5">
        <div class="container px-4 px-lg-5">
            <div class="row justify-content-center">
                <div class="col-lg-6">
                    <div class="card shadow-sm">
                        <div class="card-body p-4">
                            <form id="formRegistro">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label for="regNombre" class="form-label">Nombre <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="regNombre" required maxlength="50">
                                    </div>
                                    <div class="col-md-6">
                                        <label for="regApellido" class="form-label">Apellido <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="regApellido" required maxlength="50">
                                    </div>
                                    <div class="col-12">
                                        <label for="regEmail" class="form-label">Email <span class="text-danger">*</span></label>
                                        <input type="email" class="form-control" id="regEmail" required maxlength="150">
                                    </div>
                                    <div class="col-12">
                                        <label for="regTelefono" class="form-label">Tel&eacute;fono <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="regTelefono" required maxlength="25" placeholder="8888-1111">
                                    </div>
                                    <div class="col-12">
                                        <label for="regPassword" class="form-label">Contrase&ntilde;a <span class="text-danger">*</span></label>
                                        <input type="password" class="form-control" id="regPassword" required minlength="4" maxlength="100">
                                    </div>
                                    <div class="col-12">
                                        <button type="submit" class="btn btn-dark w-100" id="btnRegistrar">
                                            <i class="bi bi-person-plus me-2"></i>Registrarme
                                        </button>
                                    </div>
                                    <div class="col-12 text-center">
                                        <small class="text-muted">¿Ya tienes cuenta? <a href="/hamilton-store/public/pages/auth/login.php">Iniciar sesión</a></small>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <?php MostrarFooter(); ?>
    <?php MostrarJS(); ?>
    <script src="/hamilton-store/public/js/modules/auth-cliente.js"></script>
    <script>
        document.getElementById('formRegistro').addEventListener('submit', function (e) {
            e.preventDefault();
            if (window.AuthCliente && window.AuthCliente.registrar) {
                window.AuthCliente.registrar({
                    nombre: document.getElementById('regNombre').value.trim(),
                    apellido: document.getElementById('regApellido').value.trim(),
                    email: document.getElementById('regEmail').value.trim().toLowerCase(),
                    telefono: document.getElementById('regTelefono').value.trim(),
                    password: document.getElementById('regPassword').value
                });
            }
        });
    </script>
</body>
</html>
