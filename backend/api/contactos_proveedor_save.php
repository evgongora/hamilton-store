<?php
/**
 * POST JSON — CRUD contactos_proveedores (M_HAMILTON_STORE.pkg_contactos_proveedores).
 *
 * { "action": "insert", "nombre", "apellido", "email", "telefono", "proveedorId" }
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
    $email = trim((string) ($body['email'] ?? ''));
    $telefono = trim((string) ($body['telefono'] ?? ''));
    $pid = isset($body['proveedorId']) ? (int) $body['proveedorId'] : 0;

    $sql = 'BEGIN M_HAMILTON_STORE.pkg_contactos_proveedores.sp_insertar_contacto_proveedor(
        :nombre, :apellido, :email, :telefono, :pid
    ); END;';
    $st = oci_parse($conn, $sql);
    if (!$st) {
        api_json_response(['ok' => false, 'error' => api_oci_error_message($conn)], 500);
        exit;
    }
    oci_bind_by_name($st, ':nombre', $nombre, 4000);
    oci_bind_by_name($st, ':apellido', $apellido, 4000);
    oci_bind_by_name($st, ':email', $email, 4000);
    oci_bind_by_name($st, ':telefono', $telefono, 4000);
    oci_bind_by_name($st, ':pid', $pid);

    if (!@oci_execute($st)) {
        api_json_response(['ok' => false, 'error' => api_oci_error_message($st)], 400);
        oci_free_statement($st);
        exit;
    }
    oci_free_statement($st);
    api_json_response(['ok' => true, 'message' => 'Contacto creado']);
    exit;
}

if ($action === 'update') {
    $id = isset($body['id']) ? (int) $body['id'] : 0;
    $nombre = trim((string) ($body['nombre'] ?? ''));
    $apellido = trim((string) ($body['apellido'] ?? ''));
    $email = trim((string) ($body['email'] ?? ''));
    $telefono = trim((string) ($body['telefono'] ?? ''));
    $pid = isset($body['proveedorId']) ? (int) $body['proveedorId'] : 0;

    $sql = 'BEGIN M_HAMILTON_STORE.pkg_contactos_proveedores.sp_actualizar_contacto_proveedor(
        :id, :nombre, :apellido, :email, :telefono, :pid
    ); END;';
    $st = oci_parse($conn, $sql);
    if (!$st) {
        api_json_response(['ok' => false, 'error' => api_oci_error_message($conn)], 500);
        exit;
    }
    oci_bind_by_name($st, ':id', $id);
    oci_bind_by_name($st, ':nombre', $nombre, 4000);
    oci_bind_by_name($st, ':apellido', $apellido, 4000);
    oci_bind_by_name($st, ':email', $email, 4000);
    oci_bind_by_name($st, ':telefono', $telefono, 4000);
    oci_bind_by_name($st, ':pid', $pid);

    if (!@oci_execute($st)) {
        api_json_response(['ok' => false, 'error' => api_oci_error_message($st)], 400);
        oci_free_statement($st);
        exit;
    }
    oci_free_statement($st);
    api_json_response(['ok' => true, 'message' => 'Contacto actualizado']);
    exit;
}

if ($action === 'delete') {
    $id = isset($body['id']) ? (int) $body['id'] : 0;
    $sql = 'BEGIN M_HAMILTON_STORE.pkg_contactos_proveedores.sp_eliminar_contacto_proveedor(:id); END;';
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
    api_json_response(['ok' => true, 'message' => 'Contacto eliminado']);
    exit;
}

api_json_response(['ok' => false, 'error' => 'action inválido: use insert, update o delete'], 400);
