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
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid px-3">
        <a class="navbar-brand d-flex align-items-center" href="<?php echo htmlspecialchars($dashboardUrl); ?>">
            <img src="<?php echo htmlspecialchars($basePath); ?>/assets/img/Header-logo.png" alt="Logo" height="32" class="me-2">
            M. Hamilton Store
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMain" aria-controls="navbarMain" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarMain">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <span class="nav-link text-white-50"><?php echo htmlspecialchars($user); ?> <small>(<?php echo htmlspecialchars($role); ?>)</small></span>
                </li>
                <li class="nav-item">
                    <a class="nav-link btn btn-outline-light btn-sm ms-2" href="<?php echo htmlspecialchars($logoutUrl); ?>">
                        <i class="bi bi-box-arrow-right me-1"></i>Cerrar sesión
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>
