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


function api_oci_ref_cursor_fetch_all($conn, string $plsqlBlock, string $cursorBindName = ':cur'): array
{
    $cursor = oci_new_cursor($conn);
    $stmt = oci_parse($conn, $plsqlBlock);
    if ($stmt === false) {
        return ['rows' => [], 'error' => api_oci_error_message($conn)];
    }
    oci_bind_by_name($stmt, $cursorBindName, $cursor, -1, OCI_B_CURSOR);
    if (!@oci_execute($stmt)) {
        $err = api_oci_error_message($stmt);
        oci_free_statement($stmt);

        return ['rows' => [], 'error' => $err];
    }
    if (!@oci_execute($cursor)) {
        $err = api_oci_error_message($cursor);
        oci_free_statement($cursor);
        oci_free_statement($stmt);

        return ['rows' => [], 'error' => $err];
    }
    $rows = [];
    while (($row = oci_fetch_assoc($cursor)) !== false) {
        $rows[] = array_change_key_case($row, CASE_LOWER);
    }
    oci_free_statement($cursor);
    oci_free_statement($stmt);

    return ['rows' => $rows, 'error' => null];
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
 * Misma regla que pkg_clientes.fn_email_valido (regex tipo Oracle).
 */
function hamilton_cliente_email_valido_oracle(string $s): bool
{
    $t = trim($s);

    return $t !== ''
        && preg_match('/^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,}$/', $t) === 1;
}

/**
 * Solo admin, cajero y soporte gestionan clientes desde el sistema (crear / editar estado).
 */
function api_require_clientes_gestion_roles(): void
{
    api_require_staff_session();
    $role = $_SESSION['role'] ?? '';
    if (!in_array($role, ['admin', 'cajero', 'soporte'], true)) {
        api_json_response(['ok' => false, 'error' => 'No autorizado para gestionar clientes'], 403);
        exit;
    }
}

/** Alta/edición de usuarios (sistema) desde el módulo Usuarios: admin y cajero. */
function api_require_usuarios_gestion_roles(): void
{
    api_require_staff_session();
    $role = $_SESSION['role'] ?? '';
    if (!in_array($role, ['admin', 'cajero'], true)) {
        api_json_response(['ok' => false, 'error' => 'No autorizado para gestionar usuarios'], 403);
        exit;
    }
}

/** Cliente de tienda con sesión (login rol cliente + id Oracle en sesión). */
function api_require_cliente_tienda_session(): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if (empty($_SESSION['user']) || ($_SESSION['role'] ?? '') !== 'cliente' || empty($_SESSION['cliente_id'])) {
        api_json_response(['ok' => false, 'error' => 'No autorizado'], 401);
        exit;
    }
}

/** Personal interno o cliente tienda (lecturas compartidas, p. ej. métodos de pago en checkout). */
function api_require_staff_or_cliente_tienda(): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    $role = $_SESSION['role'] ?? '';
    if (in_array($role, ['admin', 'cajero', 'inventario', 'soporte'], true)) {
        return;
    }
    if ($role === 'cliente' && !empty($_SESSION['cliente_id'])) {
        return;
    }
    api_json_response(['ok' => false, 'error' => 'No autorizado'], 401);
    exit;
}

/**
 * Empleado atribuido a ventas creadas desde la tienda (cliente). Env: HAMILTON_TIENDA_EMPLEADO_VENTA o 1.
 */
function hamilton_tienda_venta_empleado_default(): int
{
    $v = getenv('HAMILTON_TIENDA_EMPLEADO_VENTA');
    if ($v !== false && $v !== '' && is_numeric($v)) {
        $n = (int) $v;
        if ($n > 0) {
            return $n;
        }
    }

    return 1;
}

/**
 * id_cliente del encabezado de venta o null si no existe.
 *
 * @param resource $conn
 */
function hamilton_encabezado_venta_id_cliente($conn, int $idVenta): ?int
{
    if ($idVenta <= 0) {
        return null;
    }
    $sql = 'SELECT clientes_id_cliente AS cid FROM encabezados_ventas WHERE id_venta = :id';
    $st = oci_parse($conn, $sql);
    if (!$st) {
        return null;
    }
    oci_bind_by_name($st, ':id', $idVenta);
    if (!@oci_execute($st)) {
        oci_free_statement($st);

        return null;
    }
    $row = oci_fetch_assoc($st);
    oci_free_statement($st);
    if ($row === false) {
        return null;
    }
    $row = array_change_key_case($row, CASE_LOWER);
    $cid = $row['cid'] ?? null;
    if ($cid === null || $cid === '') {
        return null;
    }

    return (int) $cid;
}

/**
 * @param resource $conn
 */
function hamilton_encabezado_venta_existe($conn, int $idVenta): bool
{
    if ($idVenta <= 0) {
        return false;
    }
    $sql = 'SELECT COUNT(*) AS c FROM encabezados_ventas WHERE id_venta = :id';
    $st = oci_parse($conn, $sql);
    if (!$st) {
        return false;
    }
    oci_bind_by_name($st, ':id', $idVenta);
    if (!@oci_execute($st)) {
        oci_free_statement($st);

        return false;
    }
    $row = oci_fetch_assoc($st);
    oci_free_statement($st);
    if ($row === false) {
        return false;
    }
    $row = array_change_key_case($row, CASE_LOWER);

    return (int) ($row['c'] ?? 0) > 0;
}

/**
 * Valida nombre, apellido y email para alta/edición de cliente (alineado con pkg_clientes).
 *
 * @return string|null mensaje de error o null si OK
 */
function api_validate_cliente_campos_oracle(string $nombre, string $apellido, string $email): ?string
{
    if (!hamilton_cliente_nombre_valido_oracle($nombre)) {
        return 'Nombre obligatorio: solo letras A-Z y espacios (sin tildes ni ñ), como exige la base de datos.';
    }
    if (strlen(trim($nombre)) > 100) {
        return 'Nombre: máximo 100 caracteres.';
    }
    if (!hamilton_cliente_nombre_valido_oracle($apellido)) {
        return 'Apellido obligatorio: solo letras A-Z y espacios (sin tildes ni ñ).';
    }
    if (strlen(trim($apellido)) > 100) {
        return 'Apellido: máximo 100 caracteres.';
    }
    if (!hamilton_cliente_email_valido_oracle($email)) {
        return 'Email obligatorio con formato válido.';
    }
    if (strlen($email) > 200) {
        return 'Email: máximo 200 caracteres.';
    }

    return null;
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

/**
 * Teléfono alineado con pkg_telefonos (dígitos, espacios, guiones, paréntesis).
 */
function hamilton_telefono_valido_oracle(string $s): bool
{
    $t = trim($s);

    return $t !== '' && preg_match('/^[0-9() -]+$/', $t) === 1;
}

/**
 * Texto libre seguro (direcciones, notas): sin NUL, longitud acotada.
 *
 * @return string|null mensaje de error o null si OK (devuelve texto recortado por referencia opcional)
 */
function hamilton_texto_libre_validar(string $s, int $maxLen, bool $allowEmpty = true): ?string
{
    if (strpos($s, "\0") !== false) {
        return 'El texto contiene caracteres no permitidos.';
    }
    $t = trim($s);
    if (!$allowEmpty && $t === '') {
        return 'El texto es obligatorio.';
    }
    if (strlen($t) > $maxLen) {
        return 'El texto supera la longitud máxima permitida (' . $maxLen . ' caracteres).';
    }

    return null;
}

/** Monto de pago: finito y estrictamente mayor que cero. */
function hamilton_monto_positivo_valido($monto): bool
{
    if (!is_numeric($monto)) {
        return false;
    }
    $x = (float) $monto;

    return is_finite($x) && $x > 0.0;
}

/**
 * @param resource $conn
 */
function hamilton_cliente_existe($conn, int $idCliente): bool
{
    if ($idCliente <= 0) {
        return false;
    }
    $sql = 'SELECT COUNT(*) AS c FROM clientes WHERE id_cliente = :id';
    $st = oci_parse($conn, $sql);
    if (!$st) {
        return false;
    }
    oci_bind_by_name($st, ':id', $idCliente);
    if (!@oci_execute($st)) {
        oci_free_statement($st);

        return false;
    }
    $row = oci_fetch_assoc($st);
    oci_free_statement($st);
    if ($row === false) {
        return false;
    }
    $row = array_change_key_case($row, CASE_LOWER);

    return (int) ($row['c'] ?? 0) > 0;
}

/**
 * @param resource $conn
 */
function hamilton_metodo_pago_existe($conn, int $idMetodo): bool
{
    if ($idMetodo <= 0) {
        return false;
    }
    $sql = 'SELECT COUNT(*) AS c FROM metodos_pago WHERE id_metodo_pago = :id';
    $st = oci_parse($conn, $sql);
    if (!$st) {
        return false;
    }
    oci_bind_by_name($st, ':id', $idMetodo);
    if (!@oci_execute($st)) {
        oci_free_statement($st);

        return false;
    }
    $row = oci_fetch_assoc($st);
    oci_free_statement($st);
    if ($row === false) {
        return false;
    }
    $row = array_change_key_case($row, CASE_LOWER);

    return (int) ($row['c'] ?? 0) > 0;
}

/**
 * @param resource $conn
 */
function hamilton_proveedor_existe($conn, int $idProveedor): bool
{
    if ($idProveedor <= 0) {
        return false;
    }
    $sql = 'SELECT COUNT(*) AS c FROM proveedores WHERE id_proveedor = :id';
    $st = oci_parse($conn, $sql);
    if (!$st) {
        return false;
    }
    oci_bind_by_name($st, ':id', $idProveedor);
    if (!@oci_execute($st)) {
        oci_free_statement($st);

        return false;
    }
    $row = oci_fetch_assoc($st);
    oci_free_statement($st);
    if ($row === false) {
        return false;
    }
    $row = array_change_key_case($row, CASE_LOWER);

    return (int) ($row['c'] ?? 0) > 0;
}

function hamilton_contacto_proveedor_existe($conn, int $idContacto): bool
{
    if ($idContacto <= 0) {
        return false;
    }
    $sql = 'SELECT COUNT(*) AS c FROM contactos_proveedores WHERE id_contacto = :id';
    $st = oci_parse($conn, $sql);
    if (!$st) {
        return false;
    }
    oci_bind_by_name($st, ':id', $idContacto);
    if (!@oci_execute($st)) {
        oci_free_statement($st);

        return false;
    }
    $row = oci_fetch_assoc($st);
    oci_free_statement($st);
    if ($row === false) {
        return false;
    }
    $row = array_change_key_case($row, CASE_LOWER);

    return (int) ($row['c'] ?? 0) > 0;
}

function hamilton_producto_existe($conn, int $idProducto): bool
{
    if ($idProducto <= 0) {
        return false;
    }
    $sql = 'SELECT COUNT(*) AS c FROM productos WHERE id_producto = :id';
    $st = oci_parse($conn, $sql);
    if (!$st) {
        return false;
    }
    oci_bind_by_name($st, ':id', $idProducto);
    if (!@oci_execute($st)) {
        oci_free_statement($st);

        return false;
    }
    $row = oci_fetch_assoc($st);
    oci_free_statement($st);
    if ($row === false) {
        return false;
    }
    $row = array_change_key_case($row, CASE_LOWER);

    return (int) ($row['c'] ?? 0) > 0;
}

/**
 * @param resource $conn
 */
function hamilton_categoria_existe($conn, int $idCat): bool
{
    if ($idCat <= 0) {
        return false;
    }
    $sql = 'SELECT COUNT(*) AS c FROM categorias WHERE id_categoria = :id';
    $st = oci_parse($conn, $sql);
    if (!$st) {
        return false;
    }
    oci_bind_by_name($st, ':id', $idCat);
    if (!@oci_execute($st)) {
        oci_free_statement($st);

        return false;
    }
    $row = oci_fetch_assoc($st);
    oci_free_statement($st);
    if ($row === false) {
        return false;
    }
    $row = array_change_key_case($row, CASE_LOWER);

    return (int) ($row['c'] ?? 0) > 0;
}


function hamilton_estado_id_existe($conn, int $idEstado): bool
{
    if ($idEstado <= 0) {
        return false;
    }
    $sql = 'SELECT COUNT(*) AS c FROM estados WHERE id_estado = :id';
    $st = oci_parse($conn, $sql);
    if (!$st) {
        return false;
    }
    oci_bind_by_name($st, ':id', $idEstado);
    if (!@oci_execute($st)) {
        oci_free_statement($st);

        return false;
    }
    $row = oci_fetch_assoc($st);
    oci_free_statement($st);
    if ($row === false) {
        return false;
    }
    $row = array_change_key_case($row, CASE_LOWER);

    return (int) ($row['c'] ?? 0) > 0;
}

/**
 * Nombre de producto: no vacío, longitud razonable (VARCHAR2 en BD).
 */
function hamilton_producto_nombre_valido(string $nombre, int $maxLen = 200): bool
{
    $t = trim($nombre);

    return $t !== '' && strlen($t) <= $maxLen;
}

/**
 * Precio / cantidad no negativos y finitos.
 */
function hamilton_precio_no_negativo_valido($v): bool
{
    if ($v === null || $v === '') {
        return false;
    }
    if (!is_numeric($v)) {
        return false;
    }
    $x = (float) $v;

    return is_finite($x) && $x >= 0.0;
}

/**
 * @param resource $conn
 * @param int[] $idsProducto
 *
 * @return array<int, array{precio_venta: float, cantidad: int, estado: string}>
 */
function hamilton_productos_datos_venta_por_ids($conn, array $idsProducto): array
{
    $ids = array_values(array_unique(array_filter(array_map('intval', $idsProducto), function ($x) {
        return $x > 0;
    })));
    if ($ids === []) {
        return [];
    }
    $inList = implode(',', $ids);
    $sql = <<<SQL
SELECT p.id_producto,
       p.precio_venta,
       p.cantidad,
       LOWER(TRIM(e.nombre)) AS estado_nombre
  FROM productos p
  JOIN estados e ON e.id_estado = p.estados_id_estado
 WHERE p.id_producto IN ($inList)
SQL;
    $st = oci_parse($conn, $sql);
    if (!$st || !@oci_execute($st)) {
        if ($st) {
            oci_free_statement($st);
        }

        return [];
    }
    $out = [];
    while (($row = oci_fetch_assoc($st)) !== false) {
        $row = array_change_key_case($row, CASE_LOWER);
        $id = (int) ($row['id_producto'] ?? 0);
        if ($id <= 0) {
            continue;
        }
        $pv = $row['precio_venta'];
        $out[$id] = [
            'precio_venta' => $pv !== null && $pv !== '' ? (float) $pv : 0.0,
            'cantidad'     => (int) ($row['cantidad'] ?? 0),
            'estado'       => (string) ($row['estado_nombre'] ?? ''),
        ];
    }
    oci_free_statement($st);

    return $out;
}
