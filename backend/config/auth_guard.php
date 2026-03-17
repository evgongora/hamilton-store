<?php
/**
 * auth_guard.php - Protección de rutas por sesión y rol
 * Sin sesión → redirect a login
 * Sin permiso (rol no permitido) → no_access.php
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Obtiene la URL base del proyecto (ej: /hamilton-store)
 */
function getBasePath(): string {
    $script = $_SERVER['SCRIPT_NAME'] ?? '';
    if (strpos($script, '/public/') !== false) {
        return preg_replace('#/public/.*#', '', $script);
    }
    if (strpos($script, '/backend/') !== false) {
        return preg_replace('#/backend/.*#', '', $script);
    }
    return dirname($script);
}

/**
 * Requiere que exista sesión. Si no hay sesión, redirige a login.
 */
function requireLogin(): void {
    if (empty($_SESSION['user'])) {
        $base = getBasePath();
        header("Location: {$base}/public/pages/auth/login.php");
        exit;
    }
}

/**
 * Requiere sesión Y que el rol esté en la lista permitida.
 * @param string[] $allowedRoles Roles que pueden acceder: 'admin', 'cajero', 'inventario'
 */
function requireRole(array $allowedRoles): void {
    requireLogin();
    $role = $_SESSION['role'] ?? '';
    if (!in_array($role, $allowedRoles, true)) {
        $base = getBasePath();
        header("Location: {$base}/public/pages/auth/no_access.php");
        exit;
    }
}

/**
 * Comprueba si hay sesión activa
 */
function isLoggedIn(): bool {
    return !empty($_SESSION['user']);
}
