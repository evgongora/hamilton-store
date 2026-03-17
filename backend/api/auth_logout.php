<?php
/**
 * auth_logout.php - Destruye la sesión y redirige a login
 */

session_start();
$_SESSION = [];
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
}
session_destroy();

$base = dirname(dirname(dirname($_SERVER['SCRIPT_NAME'])));
if ($base === '/' || $base === '\\') {
    $base = '';
}

header("Location: {$base}/public/pages/auth/login.php");
exit;
