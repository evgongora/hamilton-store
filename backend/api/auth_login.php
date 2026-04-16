<?php
/**
 * auth_login.php — Login contra Oracle (usuarios, contraseña bcrypt).
 * POST: user, password
 */
declare(strict_types=1);

session_start();

require_once __DIR__ . '/../config/auth_usuario.php';
require_once __DIR__ . '/../config/auth_guard.php';

$base = dirname(dirname(dirname($_SERVER['SCRIPT_NAME'])));
if ($base === '/' || $base === '\\') {
    $base = '';
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    error_log('hamilton-store auth_login: se esperaba POST, recibido: ' . ($_SERVER['REQUEST_METHOD'] ?? '?'));
    header("Location: {$base}/public/pages/auth/login.php?error=1");
    exit;
}

$user = trim((string) ($_POST['user'] ?? ''));
$password = (string) ($_POST['password'] ?? '');

if ($user === '' || $password === '') {
    error_log(
        'hamilton-store auth_login: user/password vacíos (POST vacío o nombres de campo distintos de user/password; claves POST: '
        . implode(',', array_keys($_POST)) . ')'
    );
    header("Location: {$base}/public/pages/auth/login.php?error=1");
    exit;
}

$auth = hamilton_authenticate($user, $password);
if ($auth === null) {
    error_log('hamilton-store auth_login: login rechazado (ver líneas hamilton-store auth: o hamilton-store: en este mismo instante)');
    header("Location: {$base}/public/pages/auth/login.php?error=1");
    exit;
}

$_SESSION['user'] = $user;
$_SESSION['role'] = $auth['role'];
$_SESSION['id_usuario'] = $auth['id_usuario'];

if ($auth['empleado_id'] !== null && $auth['empleado_id'] > 0) {
    $_SESSION['empleado_id'] = $auth['empleado_id'];
} else {
    unset($_SESSION['empleado_id']);
}

if ($auth['cliente_id'] !== null && $auth['cliente_id'] > 0) {
    $_SESSION['cliente_id'] = $auth['cliente_id'];
} else {
    unset($_SESSION['cliente_id']);
}

$next = isset($_POST['next']) ? trim((string) $_POST['next']) : '';

if ($auth['role'] === 'cliente') {
    if ($next === 'checkout') {
        header("Location: {$base}/public/pages/tienda/checkout.php");
    } else {
        header("Location: {$base}/public/pages/tienda/Homepage.php");
    }
} else {
    header('Location: ' . getRoleHomePath($auth['role']));
}
exit;
