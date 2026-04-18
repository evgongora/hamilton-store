<?php
/**
 * POST JSON — CRUD telefonos_cont_proveedores (M_HAMILTON_STORE.pkg_telefonos_cont_proveedores).
 *
 * { "action": "insert", "numero": "2222-2222", "idContacto": 1 }
 * { "action": "update", "id": 1, "numero": "...", "idContacto": 1 }
 * { "action": "delete", "id": 1 }
 */
declare(strict_types=1);

require_once __DIR__ . '/../config/api_helpers.php';

api_require_staff_session();
api_require_method('POST');
$conn = api_require_oracle();
$body = api_read_json_body();
$action = isset($body['action']) ? strtolower(trim((string) $body['action'])) : '';

if ($action === 'insert') {
    $numero = trim((string) ($body['numero'] ?? ''));
    $idCont = isset($body['idContacto']) ? (int) $body['idContacto'] : 0;
    if ($idCont <= 0 || !hamilton_contacto_proveedor_existe($conn, $idCont)) {
        api_json_response(['ok' => false, 'error' => 'Contacto de proveedor inválido o inexistente.'], 400);
        exit;
    }
    if (!hamilton_telefono_valido_oracle($numero)) {
        api_json_response(['ok' => false, 'error' => 'Número de teléfono inválido (solo dígitos, espacios, guiones y paréntesis).'], 400);
        exit;
    }
    $sql = 'BEGIN M_HAMILTON_STORE.pkg_telefonos_cont_proveedores.sp_insertar_telefono_cont_proveedor(:numero, :id_cont); END;';
    $st = oci_parse($conn, $sql);
    if (!$st) {
        api_json_response(['ok' => false, 'error' => api_oci_error_message($conn)], 500);
        exit;
    }
    oci_bind_by_name($st, ':numero', $numero, 4000);
    oci_bind_by_name($st, ':id_cont', $idCont);
    if (!@oci_execute($st)) {
        api_json_response(['ok' => false, 'error' => api_oci_error_message($st)], 400);
        oci_free_statement($st);
        exit;
    }
    oci_free_statement($st);
    api_json_response(['ok' => true, 'message' => 'Teléfono creado']);
    exit;
}

if ($action === 'update') {
    $id = isset($body['id']) ? (int) $body['id'] : 0;
    $numero = trim((string) ($body['numero'] ?? ''));
    $idCont = isset($body['idContacto']) ? (int) $body['idContacto'] : 0;
    if ($id <= 0) {
        api_json_response(['ok' => false, 'error' => 'id de teléfono inválido.'], 400);
        exit;
    }
    if ($idCont <= 0 || !hamilton_contacto_proveedor_existe($conn, $idCont)) {
        api_json_response(['ok' => false, 'error' => 'Contacto de proveedor inválido o inexistente.'], 400);
        exit;
    }
    if (!hamilton_telefono_valido_oracle($numero)) {
        api_json_response(['ok' => false, 'error' => 'Número de teléfono inválido (solo dígitos, espacios, guiones y paréntesis).'], 400);
        exit;
    }
    $sql = 'BEGIN M_HAMILTON_STORE.pkg_telefonos_cont_proveedores.sp_actualizar_telefono_cont_proveedor(:id, :numero, :id_cont); END;';
    $st = oci_parse($conn, $sql);
    if (!$st) {
        api_json_response(['ok' => false, 'error' => api_oci_error_message($conn)], 500);
        exit;
    }
    oci_bind_by_name($st, ':id', $id);
    oci_bind_by_name($st, ':numero', $numero, 4000);
    oci_bind_by_name($st, ':id_cont', $idCont);
    if (!@oci_execute($st)) {
        api_json_response(['ok' => false, 'error' => api_oci_error_message($st)], 400);
        oci_free_statement($st);
        exit;
    }
    oci_free_statement($st);
    api_json_response(['ok' => true, 'message' => 'Teléfono actualizado']);
    exit;
}

if ($action === 'delete') {
    $id = isset($body['id']) ? (int) $body['id'] : 0;
    if ($id <= 0) {
        api_json_response(['ok' => false, 'error' => 'id de teléfono inválido.'], 400);
        exit;
    }
    $sql = 'BEGIN M_HAMILTON_STORE.pkg_telefonos_cont_proveedores.sp_eliminar_telefono_cont_proveedor(:id); END;';
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
    api_json_response(['ok' => true, 'message' => 'Teléfono eliminado']);
    exit;
}

api_json_response(['ok' => false, 'error' => 'action inválido: use insert, update o delete'], 400);
