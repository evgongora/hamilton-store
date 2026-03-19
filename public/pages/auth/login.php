<?php
/**
 * login.php - Pantalla de login: clientes (email+password) o staff (user+role)
 */
session_start();
require_once __DIR__ . '/../../../backend/config/auth_guard.php';

if (!empty($_SESSION['user'])) {
    header('Location: ' . getRoleHomePath($_SESSION['role'] ?? ''));
    exit;
}

$error = isset($_GET['error']) && $_GET['error'] === '1';
$base = dirname(dirname(dirname($_SERVER['SCRIPT_NAME'])));
$loginAction = $base . '/../backend/api/auth_login.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - M. Hamilton Store</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.5.0/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body class="bg-light min-vh-100 d-flex align-items-center justify-content-center py-4">
    <div class="container">
        <div class="text-center mb-4">
            <h2 class="fw-bold">M. Hamilton Store</h2>
            <p class="text-muted">Iniciar sesión</p>
        </div>

        <div class="row g-4 justify-content-center">
            <div class="col-md-5">
                <div class="card shadow-sm h-100">
                    <div class="card-body p-4">
                        <h5 class="card-title"><i class="bi bi-person me-2"></i>Soy cliente</h5>
                        <p class="text-muted small">Compra en la tienda online</p>
                        <form id="formCliente" onsubmit="return false;">
                            <div class="mb-3">
                                <label for="clienteEmail" class="form-label">Email</label>
                                <input type="email" class="form-control" id="clienteEmail" placeholder="tu@email.com" required>
                            </div>
                            <div class="mb-3">
                                <label for="clientePassword" class="form-label">Contrase&ntilde;a</label>
                                <input type="password" class="form-control" id="clientePassword" required>
                            </div>
                            <button type="submit" class="btn btn-dark w-100">Entrar</button>
                        </form>
                        <p class="mt-3 mb-0 small text-muted">
                            <a href="/hamilton-store/public/pages/tienda/registro.php">Crear cuenta</a>
                        </p>
                    </div>
                </div>
            </div>

            <div class="col-md-5">
                <div class="card shadow-sm h-100">
                    <div class="card-body p-4">
                        <h5 class="card-title"><i class="bi bi-person-badge me-2"></i>Soy empleado</h5>
                        <p class="text-muted small">Acceso al sistema (mock)</p>
                        <?php if ($error): ?>
                            <div class="alert alert-danger py-2">Usuario y rol requeridos.</div>
                        <?php endif; ?>
                        <form method="post" action="<?php echo htmlspecialchars($loginAction); ?>">
                            <div class="mb-3">
                                <label for="user" class="form-label">Usuario</label>
                                <input type="text" class="form-control" id="user" name="user" placeholder="admin" required>
                            </div>
                            <div class="mb-3">
                                <label for="role" class="form-label">Rol</label>
                                <select class="form-select" id="role" name="role" required>
                                    <option value="">Seleccionar</option>
                                    <option value="admin">Admin</option>
                                    <option value="cajero">Cajero</option>
                                    <option value="inventario">Inventario</option>
                                    <option value="cliente">Cliente (demo)</option>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-dark w-100">Entrar</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="/hamilton-store/public/js/modules/auth-cliente.js"></script>
    <script>
        document.getElementById('formCliente').addEventListener('submit', function () {
            var email = document.getElementById('clienteEmail').value.trim();
            var pass = document.getElementById('clientePassword').value;
            if (window.AuthCliente && window.AuthCliente.login(email, pass)) {
                window.location.href = '/hamilton-store/public/pages/tienda/Homepage.php';
            }
        });
    </script>
</body>
</html>
