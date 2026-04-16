<?php
/**
 * Respuestas JSON y utilidades para endpoints en backend/api/
 */
declare(strict_types=1);

function api_json_headers(): void
{
    header('Content-Type: application/json; charset=utf-8');
}

/**
 * @param mixed $payload
 */
function api_json_response($payload, int $httpCode = 200): void
{
    http_response_code($httpCode);
    api_json_headers();
    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
}

/**
 * @param string|string[] $methods
 */
function api_require_method($methods): void
{
    $allowed = is_array($methods) ? $methods : [$methods];
    $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
    if (!in_array($method, $allowed, true)) {
        api_json_response(['ok' => false, 'error' => 'Método no permitido'], 405);
        exit;
    }
}

/**
 * @return array<string, mixed>
 */
function api_read_json_body(): array
{
    $raw = file_get_contents('php://input');
    if ($raw === false || $raw === '') {
        return [];
    }
    $data = json_decode($raw, true);
    return is_array($data) ? $data : [];
}

function api_require_oracle()
{
    require_once __DIR__ . '/db.php';
    $conn = hamilton_db();
    if ($conn === null) {
        api_json_response(['ok' => false, 'error' => 'Sin conexión a la base de datos'], 503);
        exit;
    }
    return $conn;
}

/** Sesión de personal (admin, cajero, inventario, soporte). */
function api_require_staff_session(): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    $role = $_SESSION['role'] ?? '';
    $staff = ['admin', 'cajero', 'inventario', 'soporte'];
    if (empty($_SESSION['user']) || !in_array($role, $staff, true)) {
        api_json_response(['ok' => false, 'error' => 'No autorizado'], 401);
        exit;
    }
}

/** Solo administrador (gestión de usuarios, roles, etc.). */
function api_require_admin_session(): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if (empty($_SESSION['user']) || ($_SESSION['role'] ?? '') !== 'admin') {
        api_json_response(['ok' => false, 'error' => 'No autorizado'], 401);
        exit;
    }
}

/**
 * id_empleado del personal logueado (sesión). Misma lógica que session_empleado_id() en auth_guard.
 */
function api_session_empleado_id(): int
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    $id = $_SESSION['empleado_id'] ?? null;
    if (is_numeric($id) && (int) $id > 0) {
        return (int) $id;
    }
    return 1;
}

/**
 * Mensaje legible de oci_error (incluye ORA-xxxxx y textos de RAISE_APPLICATION_ERROR / triggers).
 *
 * @param resource $statementOrConnection
 */
function api_oci_error_message($statementOrConnection): string
{
    $e = oci_error($statementOrConnection);

    return isset($e['message']) ? (string) $e['message'] : 'Error en Oracle';
}

/**
 * Misma regla que pkg_clientes.fn_nombre_valido: solo letras A–Z y espacios (sin tildes ni ñ).
 */
function hamilton_cliente_nombre_valido_oracle(string $s): bool
{
    $t = trim($s);

    return $t !== '' && preg_match('/^[A-Za-z ]+$/', $t) === 1;
}

/**
 * Mensajes legibles para errores comunes del registro de cliente (sin cambiar el package).
 */
function api_register_cliente_error_usuario(string $oracleMessage): string
{
    $m = $oracleMessage;
    if (stripos($m, 'Ya existe un cliente con ese email') !== false
        || stripos($m, '-20108') !== false) {
        return 'Ya existe una cuenta con ese correo electrónico. Inicie sesión o use otro email.';
    }
    if (stripos($m, 'Ya existe un usuario con ese username') !== false
        || stripos($m, '-21510') !== false) {
        return 'Ese nombre de usuario ya está registrado. Elija otro.';
    }
    if (stripos($m, 'nombre tiene caracteres no permitidos') !== false
        || stripos($m, '-20104') !== false) {
        return 'El nombre solo admite letras sin tilde (A-Z) y espacios. Ejemplo: Maria';
    }
    if (stripos($m, 'apellido tiene caracteres no permitidos') !== false
        || stripos($m, '-20105') !== false) {
        return 'El apellido solo admite letras sin tilde (A-Z) y espacios. Ejemplo: Perez';
    }

    return 'No se pudo completar el registro. Revise los datos o intente más tarde.';
}
