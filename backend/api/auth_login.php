<?php
/**
 * auth_login.php - Login mock. Crea sesión con user y role.
 * POST: user, role (admin|cajero|inventario)
 */

session_start();
require_once __DIR__ . '/../config/auth_guard.php';

$allowedRoles = ['admin', 'cajero', 'inventario', 'cliente'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = trim($_POST['user'] ?? '');
    $role = trim($_POST['role'] ?? '');

    if ($user !== '' && in_array($role, $allowedRoles, true)) {
        $_SESSION['user'] = $user;
        $_SESSION['role'] = $role;
        header('Location: ' . getRoleHomePath($role));
        exit;
    }
}

header('Location: ' . getBasePath() . '/public/pages/auth/login.php?error=1');
exit;
