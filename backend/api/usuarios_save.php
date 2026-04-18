<?php
/**
 * POST JSON — CRUD usuarios (M_HAMILTON_STORE.pkg_usuarios).
 * Contraseña con password_hash (PHP), compatible con hamilton_verify_password.
 * Actualizar sin contraseña nueva: se reenvía el hash almacenado (el paquete exige password no vacío).
 *
 * { "action": "insert", "username", "password", "idEstado", "idRol", "idEmpleado" } — personal
 * { "action": "insert", "username", "password", "idEstado", "idCliente" } — cliente tienda (rol CLIENTE en servidor)
 * { "action": "update", "id", "username", "idEstado", "idRol", "idEmpleado" } — personal
 * { "action": "update", "id", "username", "idEstado", "idRol" (CLIENTE, ignorado en servidor), "idCliente" } — cliente tienda
 * { "action": "delete", "id" }
 */
declare(strict_types=1);

require_once __DIR__ . '/../config/api_helpers.php';

api_require_usuarios_gestion_roles();
api_require_method('POST');
$conn = api_require_oracle();
$body = api_read_json_body();
$action = isset($body['action']) ? strtolower(trim((string) $body['action'])) : '';

$sessionUserId = isset($_SESSION['id_usuario']) ? (int) $_SESSION['id_usuario'] : 0;

/**
 * @return string|null mensaje de error o null si OK
 */
function hamilton_usuario_empleado_disponible($conn, int $idEmpleado, ?int $exceptUserId): ?string
{
    $sql = <<<'SQL'
SELECT id_usuario, username
  FROM usuarios
 WHERE empleados_id_empleado = :e
SQL;
    if ($exceptUserId !== null && $exceptUserId > 0) {
        $sql .= ' AND id_usuario <> :uid';
    }

    $st = oci_parse($conn, $sql);
    if (!$st) {
        return api_oci_error_message($conn);
    }
    oci_bind_by_name($st, ':e', $idEmpleado);
    if ($exceptUserId !== null && $exceptUserId > 0) {
        oci_bind_by_name($st, ':uid', $exceptUserId);
    }
    if (!@oci_execute($st)) {
        $m = api_oci_error_message($st);
        oci_free_statement($st);

        return $m;
    }
    $row = oci_fetch_assoc($st);
    oci_free_statement($st);
    if ($row !== false) {
        $row = array_change_key_case($row, CASE_LOWER);

        return 'Ya existe un usuario vinculado a ese empleado (' . ($row['username'] ?? '') . ').';
    }

    return null;
}

/**
 * @return string|null mensaje de error o null si OK
 */
function hamilton_usuario_cliente_disponible($conn, int $idCliente): ?string
{
    $sql = <<<'SQL'
SELECT id_usuario, username
  FROM usuarios
 WHERE clientes_id_cliente = :c
SQL;
    $st = oci_parse($conn, $sql);
    if (!$st) {
        return api_oci_error_message($conn);
    }
    oci_bind_by_name($st, ':c', $idCliente);
    if (!@oci_execute($st)) {
        $m = api_oci_error_message($st);
        oci_free_statement($st);

        return $m;
    }
    $row = oci_fetch_assoc($st);
    oci_free_statement($st);
    if ($row !== false) {
        $row = array_change_key_case($row, CASE_LOWER);

        return 'Ese cliente ya tiene usuario (' . ($row['username'] ?? '') . ').';
    }

    return null;
}

function hamilton_cliente_existe($conn, int $idCliente): bool
{
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

function hamilton_rol_cliente_id($conn): int
{
    $sql = <<<'SQL'
SELECT id_rol
  FROM roles
 WHERE UPPER(TRIM(nombre)) = 'CLIENTE'
 FETCH FIRST 1 ROW ONLY
SQL;
    $st = oci_parse($conn, $sql);
    if (!$st) {
        return 0;
    }
    if (!@oci_execute($st)) {
        oci_free_statement($st);

        return 0;
    }
    $row = oci_fetch_assoc($st);
    oci_free_statement($st);
    if ($row === false) {
        return 0;
    }
    $row = array_change_key_case($row, CASE_LOWER);

    return (int) ($row['id_rol'] ?? 0);
}

/**
 * @return string|false hash o false
 */
function hamilton_usuario_password_hash_actual($conn, int $idUsuario)
{
    $sql = 'SELECT password_encriptado FROM usuarios WHERE id_usuario = :id';
    $st = oci_parse($conn, $sql);
    if (!$st) {
        return false;
    }
    oci_bind_by_name($st, ':id', $idUsuario);
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

    return trim((string) ($row['password_encriptado'] ?? ''));
}

if ($action === 'insert') {
    $username = trim((string) ($body['username'] ?? ''));
    $password = (string) ($body['password'] ?? '');
    $idRol = isset($body['idRol']) ? (int) $body['idRol'] : 0;
    $idEst = isset($body['idEstado']) ? (int) $body['idEstado'] : 0;
    $idEmp = isset($body['idEmpleado']) ? (int) $body['idEmpleado'] : 0;
    $idClienteIns = isset($body['idCliente']) ? (int) $body['idCliente'] : 0;

    if ($username === '' || $idEst <= 0) {
        api_json_response(['ok' => false, 'error' => 'Complete usuario y estado.'], 400);
        exit;
    }
    if ($password === '') {
        api_json_response(['ok' => false, 'error' => 'La contraseña es obligatoria.'], 400);
        exit;
    }

    if ($idClienteIns > 0 && $idEmp > 0) {
        api_json_response(['ok' => false, 'error' => 'Indique solo empleado (personal) o solo cliente (tienda), no ambos.'], 400);
        exit;
    }

    $hash = password_hash($password, PASSWORD_DEFAULT);
    if ($hash === false) {
        api_json_response(['ok' => false, 'error' => 'No se pudo generar el hash de contraseña.'], 500);
        exit;
    }

    $sql = 'BEGIN M_HAMILTON_STORE.pkg_usuarios.sp_insertar_usuario(
        :u, :pw, :id_rol, :id_est, :id_emp, :id_cli
    ); END;';
    $st = oci_parse($conn, $sql);
    if (!$st) {
        api_json_response(['ok' => false, 'error' => api_oci_error_message($conn)], 500);
        exit;
    }
    oci_bind_by_name($st, ':u', $username, 256);
    oci_bind_by_name($st, ':pw', $hash, 4000);
    oci_bind_by_name($st, ':id_est', $idEst);

    if ($idClienteIns > 0) {
        if (!hamilton_cliente_existe($conn, $idClienteIns)) {
            api_json_response(['ok' => false, 'error' => 'El cliente indicado no existe.'], 400);
            exit;
        }
        $dupCli = hamilton_usuario_cliente_disponible($conn, $idClienteIns);
        if ($dupCli !== null) {
            api_json_response(['ok' => false, 'error' => $dupCli], 400);
            exit;
        }
        $idRolCliente = hamilton_rol_cliente_id($conn);
        if ($idRolCliente <= 0) {
            api_json_response(['ok' => false, 'error' => 'No existe el rol CLIENTE en la base de datos.'], 500);
            exit;
        }
        $empNull = null;
        oci_bind_by_name($st, ':id_rol', $idRolCliente);
        oci_bind_by_name($st, ':id_emp', $empNull);
        oci_bind_by_name($st, ':id_cli', $idClienteIns);
    } else {
        if ($idRol <= 0 || $idEmp <= 0) {
            api_json_response(['ok' => false, 'error' => 'Complete rol y empleado, o elija un cliente para cuenta de tienda.'], 400);
            exit;
        }
        $dup = hamilton_usuario_empleado_disponible($conn, $idEmp, null);
        if ($dup !== null) {
            api_json_response(['ok' => false, 'error' => $dup], 400);
            exit;
        }
        $cliNull = null;
        oci_bind_by_name($st, ':id_rol', $idRol);
        oci_bind_by_name($st, ':id_emp', $idEmp);
        oci_bind_by_name($st, ':id_cli', $cliNull);
    }

    if (!@oci_execute($st)) {
        api_json_response(['ok' => false, 'error' => api_oci_error_message($st)], 400);
        oci_free_statement($st);
        exit;
    }
    oci_free_statement($st);
    api_json_response(['ok' => true, 'message' => 'Usuario creado']);
    exit;
}

if ($action === 'update') {
    $id = isset($body['id']) ? (int) $body['id'] : 0;
    $username = trim((string) ($body['username'] ?? ''));
    $password = (string) ($body['password'] ?? '');
    $idRol = isset($body['idRol']) ? (int) $body['idRol'] : 0;
    $idEst = isset($body['idEstado']) ? (int) $body['idEstado'] : 0;
    $idEmp = isset($body['idEmpleado']) ? (int) $body['idEmpleado'] : 0;

    if ($id <= 0 || $username === '' || $idEst <= 0) {
        api_json_response(['ok' => false, 'error' => 'Datos incompletos.'], 400);
        exit;
    }

    $sqlTipo = <<<'SQL'
SELECT empleados_id_empleado, clientes_id_cliente, roles_id_rol
  FROM usuarios
 WHERE id_usuario = :id
SQL;
    $stTipo = oci_parse($conn, $sqlTipo);
    if (!$stTipo) {
        api_json_response(['ok' => false, 'error' => api_oci_error_message($conn)], 500);
        exit;
    }
    oci_bind_by_name($stTipo, ':id', $id);
    if (!@oci_execute($stTipo)) {
        api_json_response(['ok' => false, 'error' => api_oci_error_message($stTipo)], 400);
        oci_free_statement($stTipo);
        exit;
    }
    $rowTipo = oci_fetch_assoc($stTipo);
    oci_free_statement($stTipo);
    if ($rowTipo === false) {
        api_json_response(['ok' => false, 'error' => 'Usuario no encontrado.'], 404);
        exit;
    }
    $rowTipo = array_change_key_case($rowTipo, CASE_LOWER);
    $cliRaw = $rowTipo['clientes_id_cliente'] ?? null;
    $tieneCli = $cliRaw !== null && (string) $cliRaw !== '' && (int) $cliRaw > 0;

    if (!$tieneCli && ($idRol <= 0 || $idEmp <= 0)) {
        api_json_response(['ok' => false, 'error' => 'Complete rol y empleado.'], 400);
        exit;
    }

    $idRolFinal = $tieneCli ? (int) ($rowTipo['roles_id_rol'] ?? 0) : $idRol;
    if ($idRolFinal <= 0) {
        api_json_response(['ok' => false, 'error' => 'Rol inválido.'], 400);
        exit;
    }

    if ($tieneCli) {
        $idCliPersist = (int) $cliRaw;
        $idClienteBody = isset($body['idCliente']) ? (int) $body['idCliente'] : 0;
        if ($idClienteBody > 0 && $idClienteBody !== $idCliPersist) {
            api_json_response(['ok' => false, 'error' => 'El cliente indicado no coincide con este usuario.'], 400);
            exit;
        }
    } else {
        $dup = hamilton_usuario_empleado_disponible($conn, $idEmp, $id);
        if ($dup !== null) {
            api_json_response(['ok' => false, 'error' => $dup], 400);
            exit;
        }
    }

    if ($password !== '') {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        if ($hash === false) {
            api_json_response(['ok' => false, 'error' => 'No se pudo generar el hash de contraseña.'], 500);
            exit;
        }
    } else {
        $hash = hamilton_usuario_password_hash_actual($conn, $id);
        if ($hash === false || $hash === '') {
            api_json_response(['ok' => false, 'error' => 'No se pudo leer la contraseña almacenada.'], 500);
            exit;
        }
    }

    $sql = 'BEGIN M_HAMILTON_STORE.pkg_usuarios.sp_actualizar_usuario(
        :id, :u, :pw, :id_rol, :id_est, :id_emp, :id_cli
    ); END;';
    $st = oci_parse($conn, $sql);
    if (!$st) {
        api_json_response(['ok' => false, 'error' => api_oci_error_message($conn)], 500);
        exit;
    }
    oci_bind_by_name($st, ':id', $id);
    oci_bind_by_name($st, ':u', $username, 256);
    oci_bind_by_name($st, ':pw', $hash, 4000);
    oci_bind_by_name($st, ':id_rol', $idRolFinal);
    oci_bind_by_name($st, ':id_est', $idEst);
    if ($tieneCli) {
        $empNull = null;
        $idCliBind = (int) $cliRaw;
        oci_bind_by_name($st, ':id_emp', $empNull);
        oci_bind_by_name($st, ':id_cli', $idCliBind);
    } else {
        $cliNull = null;
        oci_bind_by_name($st, ':id_emp', $idEmp);
        oci_bind_by_name($st, ':id_cli', $cliNull);
    }

    if (!@oci_execute($st)) {
        api_json_response(['ok' => false, 'error' => api_oci_error_message($st)], 400);
        oci_free_statement($st);
        exit;
    }
    oci_free_statement($st);
    api_json_response(['ok' => true, 'message' => 'Usuario actualizado']);
    exit;
}

if ($action === 'delete') {
    $id = isset($body['id']) ? (int) $body['id'] : 0;
    if ($id <= 0) {
        api_json_response(['ok' => false, 'error' => 'ID inválido.'], 400);
        exit;
    }
    if ($sessionUserId > 0 && $id === $sessionUserId) {
        api_json_response(['ok' => false, 'error' => 'No puede eliminar su propio usuario.'], 400);
        exit;
    }

    $sql = 'BEGIN M_HAMILTON_STORE.pkg_usuarios.sp_eliminar_usuario(:id); END;';
    $st = oci_parse($conn, $sql);
    if (!$st) {
        api_json_response(['ok' => false, 'error' => api_oci_error_message($conn)], 500);
        exit;
    }
    oci_bind_by_name($st, ':id', $id);

    if (!@oci_execute($st)) {
        api_json_response(['ok' => false, 'error' => api_oci_error_message($st)], 400);
        oci_free_statement($st);
        exit;
    }
    oci_free_statement($st);
    api_json_response(['ok' => true, 'message' => 'Usuario eliminado']);
    exit;
}

api_json_response(['ok' => false, 'error' => 'action inválido: use insert, update o delete'], 400);
