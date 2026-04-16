<?php
/**
 * POST JSON — CRUD empleados (M_HAMILTON_STORE.pkg_empleados).
 * La fecha de ingreso la asigna la BD al insertar.
 *
 * { "action": "insert", "nombre", "apellido", "puesto", "email", "idEstado" }
 * { "action": "update", "id", ... }
 * { "action": "delete", "id" }
 */
declare(strict_types=1);

require_once __DIR__ . '/../config/api_helpers.php';

api_require_staff_session();
api_require_method('POST');
$conn = api_require_oracle();
$body = api_read_json_body();
$action = isset($body['action']) ? strtolower(trim((string) $body['action'])) : '';

if ($action === 'insert') {
    $nombre = trim((string) ($body['nombre'] ?? ''));
    $apellido = trim((string) ($body['apellido'] ?? ''));
    $puesto = trim((string) ($body['puesto'] ?? ''));
    $email = trim((string) ($body['email'] ?? ''));
    $idEst = isset($body['idEstado']) ? (int) $body['idEstado'] : 0;

    $sql = 'BEGIN M_HAMILTON_STORE.pkg_empleados.sp_insertar_empleado(
        :nombre, :apellido, :puesto, :email, :id_est
    ); END;';
    $st = oci_parse($conn, $sql);
    if (!$st) {
        api_json_response(['ok' => false, 'error' => api_oci_error_message($conn)], 500);
        exit;
    }
    oci_bind_by_name($st, ':nombre', $nombre, 4000);
    oci_bind_by_name($st, ':apellido', $apellido, 4000);
    oci_bind_by_name($st, ':puesto', $puesto, 4000);
    oci_bind_by_name($st, ':email', $email, 4000);
    oci_bind_by_name($st, ':id_est', $idEst);

    if (!@oci_execute($st)) {
        api_json_response(['ok' => false, 'error' => api_oci_error_message($st)], 400);
        oci_free_statement($st);
        exit;
    }
    oci_free_statement($st);
    api_json_response(['ok' => true, 'message' => 'Empleado creado']);
    exit;
}

if ($action === 'update') {
    $id = isset($body['id']) ? (int) $body['id'] : 0;
    $nombre = trim((string) ($body['nombre'] ?? ''));
    $apellido = trim((string) ($body['apellido'] ?? ''));
    $puesto = trim((string) ($body['puesto'] ?? ''));
    $email = trim((string) ($body['email'] ?? ''));
    $idEst = isset($body['idEstado']) ? (int) $body['idEstado'] : 0;

    $sql = 'BEGIN M_HAMILTON_STORE.pkg_empleados.sp_actualizar_empleado(
        :id, :nombre, :apellido, :puesto, :email, :id_est
    ); END;';
    $st = oci_parse($conn, $sql);
    if (!$st) {
        api_json_response(['ok' => false, 'error' => api_oci_error_message($conn)], 500);
        exit;
    }
    oci_bind_by_name($st, ':id', $id);
    oci_bind_by_name($st, ':nombre', $nombre, 4000);
    oci_bind_by_name($st, ':apellido', $apellido, 4000);
    oci_bind_by_name($st, ':puesto', $puesto, 4000);
    oci_bind_by_name($st, ':email', $email, 4000);
    oci_bind_by_name($st, ':id_est', $idEst);

    if (!@oci_execute($st)) {
        api_json_response(['ok' => false, 'error' => api_oci_error_message($st)], 400);
        oci_free_statement($st);
        exit;
    }
    oci_free_statement($st);
    api_json_response(['ok' => true, 'message' => 'Empleado actualizado']);
    exit;
}

if ($action === 'delete') {
    $id = isset($body['id']) ? (int) $body['id'] : 0;
    $sql = 'BEGIN M_HAMILTON_STORE.pkg_empleados.sp_eliminar_empleado(:id); END;';
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
    api_json_response(['ok' => true, 'message' => 'Empleado eliminado']);
    exit;
}

api_json_response(['ok' => false, 'error' => 'action inválido: use insert, update o delete'], 400);
