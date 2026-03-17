<?php
/**
 * navbar.php - Barra superior del sistema
 * Requiere: $basePath, $user, $role, $logoutUrl
 */
$user = $user ?? '';
$role = $role ?? '';
$logoutUrl = $logoutUrl ?? ($basePath . '/../backend/api/auth_logout.php');
$dashboardUrl = $basePath . '/pages/sistema/dashboard.php';
?>
<nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom app-navbar">
    <div class="container-fluid px-3">
        <a class="navbar-brand flex-shrink-0 me-4 ps-3" href="<?php echo htmlspecialchars($dashboardUrl); ?>">
            <img src="<?php echo htmlspecialchars($basePath); ?>/assets/img/Header-logo.png" alt="Logo" height="32">
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMain" aria-controls="navbarMain" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarMain">
            <div class="navbar-user-logout d-flex align-items-center gap-2">
                <span class="navbar-text text-muted mb-0"><?php echo htmlspecialchars($user); ?> <small>(<?php echo htmlspecialchars($role); ?>)</small></span>
                <a class="btn btn-outline-dark btn-sm" href="<?php echo htmlspecialchars($logoutUrl); ?>">
                    <i class="bi bi-box-arrow-right me-1"></i>Cerrar sesión
                </a>
            </div>
        </div>
    </div>
</nav>
