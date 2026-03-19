<?php
/**
 * no_access.php - Pantalla cuando el usuario no tiene permiso
 */
require_once __DIR__ . '/../../../backend/config/auth_guard.php';
requireLogin();

$base = dirname(dirname(dirname($_SERVER['SCRIPT_NAME'])));
$user = $_SESSION['user'] ?? '';
$role = $_SESSION['role'] ?? '';
$dashboardUrl = getRoleHomePath($role);
$logoutUrl = dirname($base) . '/backend/api/auth_logout.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sin acceso - M. Hamilton Store</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light min-vh-100 d-flex align-items-center justify-content-center">
    <div class="card shadow-sm text-center" style="max-width: 420px;">
        <div class="card-body p-5">
            <div class="display-4 text-warning mb-3">&#9888;</div>
            <h2 class="card-title mb-3">Sin acceso</h2>
            <p class="text-muted mb-4">Tu rol (<strong><?php echo htmlspecialchars($role); ?></strong>) no tiene permiso para ver esta página.</p>
            <div class="d-flex gap-2 justify-content-center flex-wrap">
                <a class="btn btn-dark" href="<?php echo htmlspecialchars($dashboardUrl); ?>">Ir al Dashboard</a>
                <a class="btn btn-outline-secondary" href="<?php echo htmlspecialchars($logoutUrl); ?>">Cerrar sesión</a>
            </div>
        </div>
    </div>
</body>
</html>
