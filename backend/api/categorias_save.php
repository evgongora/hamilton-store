<?php
/**
 * POST JSON — Alta / edición / baja de categorías (pkg_ref_catalogos).
 *
 * { "action": "insert", "nombre": "..." }
 * { "action": "update", "id": 1, "nombre": "..." }
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
    $nombre = trim((string) ($body['nombre'] ?? ''));
    $sql = 'BEGIN M_HAMILTON_STORE.pkg_ref_catalogos.sp_insertar_categoria(:nombre); END;';
    $st = oci_parse($conn, $sql);
    if (!$st) {
        api_json_response(['ok' => false, 'error' => api_oci_error_message($conn)], 500);
        exit;
    }
    oci_bind_by_name($st, ':nombre', $nombre, 4000);

    if (!@oci_execute($st)) {
        api_json_response(['ok' => false, 'error' => api_oci_error_message($st)], 400);
        oci_free_statement($st);
        exit;
    }
    oci_free_statement($st);
    api_json_response(['ok' => true, 'message' => 'Categoría creada']);
    exit;
}

if ($action === 'update') {
    $id = isset($body['id']) ? (int) $body['id'] : 0;
    $nombre = trim((string) ($body['nombre'] ?? ''));
    $sql = 'BEGIN M_HAMILTON_STORE.pkg_ref_catalogos.sp_actualizar_categoria(:id_categoria, :nombre); END;';
    $st = oci_parse($conn, $sql);
    if (!$st) {
        api_json_response(['ok' => false, 'error' => api_oci_error_message($conn)], 500);
        exit;
    }
    oci_bind_by_name($st, ':id_categoria', $id);
    oci_bind_by_name($st, ':nombre', $nombre, 4000);

    if (!@oci_execute($st)) {
        api_json_response(['ok' => false, 'error' => api_oci_error_message($st)], 400);
        oci_free_statement($st);
        exit;
    }
    oci_free_statement($st);
    api_json_response(['ok' => true, 'message' => 'Categoría actualizada']);
    exit;
}

if ($action === 'delete') {
    $id = isset($body['id']) ? (int) $body['id'] : 0;
    $sql = 'BEGIN M_HAMILTON_STORE.pkg_ref_catalogos.sp_eliminar_categoria(:id_categoria); END;';
    $st = oci_parse($conn, $sql);
    if (!$st) {
        api_json_response(['ok' => false, 'error' => api_oci_error_message($conn)], 500);
        exit;
    }
    oci_bind_by_name($st, ':id_categoria', $id);

    if (!@oci_execute($st)) {
        api_json_response(['ok' => false, 'error' => api_oci_error_message($st)], 400);
        oci_free_statement($st);
        exit;
    }
    oci_free_statement($st);
    api_json_response(['ok' => true, 'message' => 'Categoría eliminada']);
    exit;
}

api_json_response(['ok' => false, 'error' => 'action inválido: use insert, update o delete'], 400);
