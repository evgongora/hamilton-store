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
function getBasePath(): string
{
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
function requireLogin(): void
{
    if (empty($_SESSION['user'])) {
        $base = getBasePath();
        header("Location: {$base}/public/pages/auth/login.php");
        exit;
    }
}

/**
 * Sesión obligatoria y rol de personal (no clientes de tienda).
 */
function requireStaff(): void
{
    requireLogin();
    $role = $_SESSION['role'] ?? '';
    if (!in_array($role, ['admin', 'cajero', 'inventario', 'soporte'], true)) {
        $base = getBasePath();
        header("Location: {$base}/public/pages/tienda/Homepage.php");
        exit;
    }
}

/**
 * Requiere sesión Y que el rol esté en la lista permitida.
 *
 * @param string[] $allowedRoles Roles que pueden acceder: 'admin', 'cajero', 'inventario'
 */
function requireRole(array $allowedRoles): void
{
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
function isLoggedIn(): bool
{
    return !empty($_SESSION['user']);
}

/**
 * Ruta inicial según el rol autenticado (tras login o si ya hay sesión).
 * El personal operativo no va al dashboard salvo admin/soporte.
 */
function getRoleHomePath(?string $role = null): string
{
    $base = getBasePath();
    $role = $role ?? ($_SESSION['role'] ?? '');

    if ($role === 'cliente') {
        return $base . '/public/pages/tienda/Homepage.php';
    }

    if ($role === 'cajero') {
        return $base . '/public/pages/sistema/clientes.php';
    }

    if ($role === 'inventario') {
        return $base . '/public/pages/sistema/inventario.php';
    }

    if ($role === 'admin' || $role === 'soporte') {
        return $base . '/public/pages/sistema/dashboard.php';
    }

    return $base . '/public/pages/sistema/dashboard.php';
}

/**
 * Claves del menú lateral (sidebar) permitidas por rol.
 * Debe coincidir con requireRole() en cada página bajo public/pages/sistema/.
 *
 * @return string[]
 */
function hamilton_staff_menu_keys(?string $role = null): array
{
    $role = $role ?? ($_SESSION['role'] ?? '');
    $map = [
        'admin' => [
            'dashboard', 'productos', 'inventario', 'clientes', 'ubicaciones', 'datos_auxiliares',
            'gestion_stock', 'metodos_pago', 'proveedores', 'compras', 'ventas', 'pagos', 'empleados',
            'usuarios', 'reportes',
        ],
        'soporte' => [
            'dashboard', 'productos', 'inventario', 'clientes', 'ubicaciones', 'datos_auxiliares',
            'gestion_stock', 'metodos_pago', 'proveedores', 'compras', 'ventas', 'pagos', 'empleados',
            'reportes',
        ],
        'cajero' => ['clientes', 'inventario', 'ventas', 'pagos', 'usuarios'],
        'inventario' => ['productos', 'inventario', 'gestion_stock', 'proveedores', 'compras'],
    ];

    return $map[$role] ?? [];
}

/**
 * id_empleado del usuario interno (tabla usuarios.empleados_id_empleado), rellenado en el login si Oracle resolvió la fila.
 * Si no hay dato en sesión (usuario sin vínculo a empleado), devuelve 1 como respaldo.
 */
function session_empleado_id(): int
{
    $id = $_SESSION['empleado_id'] ?? null;
    if (is_numeric($id) && (int) $id > 0) {
        return (int) $id;
    }

    return 1;
}

/**
 * URL base de la API REST (ej. /hamilton-store/backend/api)
 */
function getApiBasePath(): string
{
    return getBasePath() . '/backend/api';
}
