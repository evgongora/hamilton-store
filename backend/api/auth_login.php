<?php
/**
 * auth_login.php - Login mock. Crea sesión con user y role.
 * POST: user, role (admin|cajero|inventario)
 */

session_start();

$base = dirname(dirname(dirname($_SERVER['SCRIPT_NAME'])));
if ($base === '/' || $base === '\\') {
    $base = '';
}

$allowedRoles = ['admin', 'cajero', 'inventario', 'cliente'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = trim($_POST['user'] ?? '');
    $role = trim($_POST['role'] ?? '');

    if ($user !== '' && in_array($role, $allowedRoles, true)) {
        $_SESSION['user'] = $user;
        $_SESSION['role'] = $role;
        if ($role === 'cliente') {
            header("Location: {$base}/public/pages/tienda/Homepage.php");
        } else {
            header("Location: {$base}/public/pages/sistema/dashboard.php");
        }
        exit;
    }
}

header("Location: {$base}/public/pages/auth/login.php?error=1");
exit;
