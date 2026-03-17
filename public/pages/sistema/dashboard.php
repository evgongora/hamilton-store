<?php
/**
 * dashboard.php - Placeholder del panel principal
 */
require_once __DIR__ . '/../../../backend/config/auth_guard.php';
requireLogin();

$basePath = dirname(dirname(dirname($_SERVER['SCRIPT_NAME'])));
$logoutUrl = dirname($basePath) . '/backend/api/auth_logout.php';
$pageTitle = 'Dashboard - M. Hamilton Store';
$currentPage = 'dashboard';
$user = $_SESSION['user'] ?? '';
$role = $_SESSION['role'] ?? '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <?php include __DIR__ . '/../../components/head.php'; ?>
</head>
<body class="app-layout bg-light">
    <?php include __DIR__ . '/../../components/navbar.php'; ?>
    <div class="app-main">
        <?php include __DIR__ . '/../../components/sidebar.php'; ?>
        <main class="app-content">
            <h1 class="mb-4">Dashboard</h1>
            <p class="text-muted">Bienvenido, <strong><?php echo htmlspecialchars($user); ?></strong>. Rol actual: <strong><?php echo htmlspecialchars($role); ?></strong>.</p>
            <p class="text-muted">Aquí irán los módulos del sistema (productos, inventario, ventas, etc.).</p>
        </main>
    </div>
    <?php include __DIR__ . '/../../components/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?php echo htmlspecialchars($basePath); ?>/js/app.js"></script>
</body>
</html>
