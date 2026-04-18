<?php
/**
 * POST JSON — Alta y edición de clientes (M_HAMILTON_STORE.pkg_clientes).
 * Roles: admin, cajero, soporte.
 *
 * Insert siempre crea en estado ACTIVO (el paquete valida duplicado de email).
 *
 * { "action": "insert", "nombre", "apellido", "email" }
 * { "action": "update", "id", "nombre", "apellido", "email", "idEstado" }
 */
declare(strict_types=1);

require_once __DIR__ . '/../config/api_helpers.php';

api_require_clientes_gestion_roles();
api_require_method('POST');
$conn = api_require_oracle();
$body = api_read_json_body();
$action = isset($body['action']) ? strtolower(trim((string) $body['action'])) : '';

/**
 * @param resource $conn
 */
function hamilton_estado_id_por_nombre($conn, string $nombreEstado): ?int
{
    $sql = <<<'SQL'
SELECT id_estado
  FROM estados
 WHERE UPPER(TRIM(nombre)) = UPPER(TRIM(:n))
 FETCH FIRST 1 ROW ONLY
SQL;
    $st = oci_parse($conn, $sql);
    if (!$st) {
        return null;
    }
    $bind = $nombreEstado;
    oci_bind_by_name($st, ':n', $bind, 256);
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

    return isset($row['id_estado']) ? (int) $row['id_estado'] : null;
}

/**
 * @param resource $conn
 */
function hamilton_estado_existe($conn, int $idEstado): bool
{
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

if ($action === 'insert') {
    $nombre = trim((string) ($body['nombre'] ?? ''));
    $apellido = trim((string) ($body['apellido'] ?? ''));
    $email = strtolower(trim((string) ($body['email'] ?? '')));

    $errVal = api_validate_cliente_campos_oracle($nombre, $apellido, $email);
    if ($errVal !== null) {
        api_json_response(['ok' => false, 'error' => $errVal], 400);
        exit;
    }

    $idActivo = hamilton_estado_id_por_nombre($conn, 'ACTIVO');
    if ($idActivo === null || $idActivo <= 0) {
        api_json_response(['ok' => false, 'error' => 'No se encontró el estado ACTIVO en la base.'], 500);
        exit;
    }

    $sql = 'BEGIN M_HAMILTON_STORE.pkg_clientes.sp_insertar_cliente(
        :nom, :ape, :email, :id_est
    ); END;';
    $st = oci_parse($conn, $sql);
    if (!$st) {
        api_json_response(['ok' => false, 'error' => api_oci_error_message($conn)], 500);
        exit;
    }
    oci_bind_by_name($st, ':nom', $nombre, 4000);
    oci_bind_by_name($st, ':ape', $apellido, 4000);
    oci_bind_by_name($st, ':email', $email, 4000);
    oci_bind_by_name($st, ':id_est', $idActivo);

    if (!@oci_execute($st)) {
        api_json_response(['ok' => false, 'error' => api_oci_error_message($st)], 400);
        oci_free_statement($st);
        exit;
    }
    oci_free_statement($st);
    api_json_response(['ok' => true, 'message' => 'Cliente creado']);
    exit;
}

if ($action === 'update') {
    $id = isset($body['id']) ? (int) $body['id'] : 0;
    $nombre = trim((string) ($body['nombre'] ?? ''));
    $apellido = trim((string) ($body['apellido'] ?? ''));
    $email = strtolower(trim((string) ($body['email'] ?? '')));
    $idEstado = isset($body['idEstado']) ? (int) $body['idEstado'] : 0;

    if ($id <= 0) {
        api_json_response(['ok' => false, 'error' => 'id de cliente inválido'], 400);
        exit;
    }

    $errVal = api_validate_cliente_campos_oracle($nombre, $apellido, $email);
    if ($errVal !== null) {
        api_json_response(['ok' => false, 'error' => $errVal], 400);
        exit;
    }

    if ($idEstado <= 0 || !hamilton_estado_existe($conn, $idEstado)) {
        api_json_response(['ok' => false, 'error' => 'Estado inválido'], 400);
        exit;
    }

    $sql = 'BEGIN M_HAMILTON_STORE.pkg_clientes.sp_actualizar_cliente(
        :id, :nom, :ape, :email, :id_est
    ); END;';
    $st = oci_parse($conn, $sql);
    if (!$st) {
        api_json_response(['ok' => false, 'error' => api_oci_error_message($conn)], 500);
        exit;
    }
    oci_bind_by_name($st, ':id', $id);
    oci_bind_by_name($st, ':nom', $nombre, 4000);
    oci_bind_by_name($st, ':ape', $apellido, 4000);
    oci_bind_by_name($st, ':email', $email, 4000);
    oci_bind_by_name($st, ':id_est', $idEstado);

    if (!@oci_execute($st)) {
        api_json_response(['ok' => false, 'error' => api_oci_error_message($st)], 400);
        oci_free_statement($st);
        exit;
    }
    oci_free_statement($st);
    api_json_response(['ok' => true, 'message' => 'Cliente actualizado']);
    exit;
}

api_json_response(['ok' => false, 'error' => 'action inválido: use insert o update'], 400);
