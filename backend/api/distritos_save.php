<?php
/**
 * POST JSON — CRUD distritos (M_HAMILTON_STORE.pkg_distritos).
 *
 * { "action": "insert", "nombre": "...", "idCanton": 1, "codigoPostal": 10101 }
 * { "action": "update", "id": 1, "nombre": "...", "idCanton": 1, "codigoPostal": null }
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
    $idCanton = isset($body['idCanton']) ? (int) $body['idCanton'] : 0;
    $cpRaw = $body['codigoPostal'] ?? null;
    $codigoPostal = null;
    if ($cpRaw !== null && $cpRaw !== '') {
        $codigoPostal = (int) $cpRaw;
    }

    $sql = 'BEGIN M_HAMILTON_STORE.pkg_distritos.sp_insertar_distrito(:nombre, :id_canton, :cp); END;';
    $st = oci_parse($conn, $sql);
    if (!$st) {
        api_json_response(['ok' => false, 'error' => api_oci_error_message($conn)], 500);
        exit;
    }
    oci_bind_by_name($st, ':nombre', $nombre, 4000);
    oci_bind_by_name($st, ':id_canton', $idCanton);
    oci_bind_by_name($st, ':cp', $codigoPostal);
    if (!@oci_execute($st)) {
        api_json_response(['ok' => false, 'error' => api_oci_error_message($st)], 400);
        oci_free_statement($st);
        exit;
    }
    oci_free_statement($st);
    api_json_response(['ok' => true, 'message' => 'Distrito creado']);
    exit;
}

if ($action === 'update') {
    $id = isset($body['id']) ? (int) $body['id'] : 0;
    $nombre = trim((string) ($body['nombre'] ?? ''));
    $idCanton = isset($body['idCanton']) ? (int) $body['idCanton'] : 0;
    $cpRaw = $body['codigoPostal'] ?? null;
    $codigoPostal = null;
    if ($cpRaw !== null && $cpRaw !== '') {
        $codigoPostal = (int) $cpRaw;
    }

    $sql = 'BEGIN M_HAMILTON_STORE.pkg_distritos.sp_actualizar_distrito(:id, :nombre, :id_canton, :cp); END;';
    $st = oci_parse($conn, $sql);
    if (!$st) {
        api_json_response(['ok' => false, 'error' => api_oci_error_message($conn)], 500);
        exit;
    }
    oci_bind_by_name($st, ':id', $id);
    oci_bind_by_name($st, ':nombre', $nombre, 4000);
    oci_bind_by_name($st, ':id_canton', $idCanton);
    oci_bind_by_name($st, ':cp', $codigoPostal);
    if (!@oci_execute($st)) {
        api_json_response(['ok' => false, 'error' => api_oci_error_message($st)], 400);
        oci_free_statement($st);
        exit;
    }
    oci_free_statement($st);
    api_json_response(['ok' => true, 'message' => 'Distrito actualizado']);
    exit;
}

if ($action === 'delete') {
    $id = isset($body['id']) ? (int) $body['id'] : 0;
    $sql = 'BEGIN M_HAMILTON_STORE.pkg_distritos.sp_eliminar_distrito(:id); END;';
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
    api_json_response(['ok' => true, 'message' => 'Distrito eliminado']);
    exit;
}

api_json_response(['ok' => false, 'error' => 'action inválido: use insert, update o delete'], 400);
