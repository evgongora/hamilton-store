<?php
/**
 * auth_logout.php - Destruye sesión, limpia cookie de cliente, redirige a login
 */

session_start();
$_SESSION = [];
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
}
session_destroy();

// Limpiar cookie de cliente (tienda)
setcookie('hamilton_cliente', '', time() - 3600, '/');

$base = dirname(dirname(dirname($_SERVER['SCRIPT_NAME'])));
if ($base === '/' || $base === '\\') {
    $base = '';
}

header("Location: {$base}/public/pages/auth/login.php?logout=1");
exit;
