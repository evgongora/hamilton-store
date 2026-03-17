<?php
/**
 * login.php - Pantalla de login con autenticación mock.
 */
session_start();

if (!empty($_SESSION['user'])) {
    $role = $_SESSION['role'] ?? '';
    if ($role === 'cliente') {
        header('Location: /hamilton-store/public/pages/tienda/Homepage.php');
    } else {
        header('Location: /hamilton-store/public/pages/sistema/dashboard.php');
    }
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
</head>
<body class="bg-light min-vh-100 d-flex align-items-center justify-content-center">
    <div class="card shadow-sm" style="width: 100%; max-width: 380px;">
        <div class="card-body p-4">
            <h2 class="card-title text-center mb-4">M. Hamilton Store</h2>
            <p class="text-muted text-center small mb-4">Iniciar sesión (mock)</p>

            <?php if ($error): ?>
                <div class="alert alert-danger py-2" role="alert">Usuario y rol requeridos.</div>
            <?php endif; ?>

            <form method="post" action="<?php echo htmlspecialchars($loginAction); ?>">
                <div class="mb-3">
                    <label for="user" class="form-label">Usuario</label>
                    <input type="text" class="form-control" id="user" name="user" placeholder="Ej: admin" required autofocus>
                </div>
                <div class="mb-4">
                    <label for="role" class="form-label">Rol</label>
                    <select class="form-select" id="role" name="role" required>
                        <option value="">Seleccionar rol</option>
                        <option value="admin">Admin</option>
                        <option value="cajero">Cajero</option>
                        <option value="inventario">Inventario</option>
                        <option value="cliente">Cliente</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-dark w-100">Entrar</button>
            </form>
        </div>
    </div>
</body>
</html>
