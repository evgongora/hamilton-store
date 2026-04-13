<?php
require_once __DIR__ . '/../../../backend/config/auth_guard.php';
requireRole(['admin', 'soporte', 'cajero']);
$basePath = dirname(dirname(dirname($_SERVER['SCRIPT_NAME'])));
$pageTitle = 'Clientes - M. Hamilton Store';
$currentPage = 'clientes';
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
            <h1 class="mb-4">Clientes</h1>
            <div class="card shadow-sm border-0 mb-3">
                <div class="card-body py-2">
                    <label for="clientesBuscar" class="form-label small text-muted mb-0">Buscar</label>
                    <input type="search" class="form-control" id="clientesBuscar" placeholder="Nombre, apellido o email…" autocomplete="off">
                </div>
            </div>
            <div id="clientesSistemaTable" class="mt-3"></div>
        </main>
    </div>
    <?php include __DIR__ . '/../../components/footer.php'; ?>
    <?php include __DIR__ . '/../../components/scripts_bootstrap.php'; ?>
    <script src="<?php echo htmlspecialchars($basePath); ?>/js/services/api.js"></script>
    <script src="<?php echo htmlspecialchars($basePath); ?>/js/modules/clientes.js"></script>
    <script src="<?php echo htmlspecialchars($basePath); ?>/js/app.js"></script>
</body>
</html>
