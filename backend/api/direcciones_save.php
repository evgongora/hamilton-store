<?php
/**
 * POST JSON — CRUD direcciones (M_HAMILTON_STORE.pkg_direcciones).
 * Debe asignarse cliente O proveedor (no ambos).
 *
 * { "action": "insert", "otrasSenas": "...", "idProvincia": 1, "idCanton": 1, "idDistrito": 1, "idCliente": 1 }
 * { "action": "insert", ..., "idProveedor": 1 }
 * { "action": "update", "id": 1, ... }
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
    $otras = trim((string) ($body['otrasSenas'] ?? ''));
    $idProv = isset($body['idProvincia']) ? (int) $body['idProvincia'] : 0;
    $idCant = isset($body['idCanton']) ? (int) $body['idCanton'] : 0;
    $idDist = isset($body['idDistrito']) ? (int) $body['idDistrito'] : 0;
    $idCliIn = isset($body['idCliente']) ? (int) $body['idCliente'] : 0;
    $idPrvIn = isset($body['idProveedor']) ? (int) $body['idProveedor'] : 0;
    $idCliente = $idCliIn > 0 ? $idCliIn : null;
    $idProveedor = $idPrvIn > 0 ? $idPrvIn : null;

    if ($idCliente !== null && $idProveedor !== null) {
        api_json_response(['ok' => false, 'error' => 'Indique solo idCliente o idProveedor, no ambos'], 400);
        exit;
    }
    if ($idCliente === null && $idProveedor === null) {
        api_json_response(['ok' => false, 'error' => 'Debe enviar idCliente o idProveedor'], 400);
        exit;
    }

    $errTxt = hamilton_texto_libre_validar($otras, 2000, true);
    if ($errTxt !== null) {
        api_json_response(['ok' => false, 'error' => $errTxt], 400);
        exit;
    }
    if ($idProv <= 0 || $idCant <= 0 || $idDist <= 0) {
        api_json_response(['ok' => false, 'error' => 'Provincia, cantón y distrito son obligatorios.'], 400);
        exit;
    }
    if ($idCliente !== null && !hamilton_cliente_existe($conn, $idCliente)) {
        api_json_response(['ok' => false, 'error' => 'Cliente no encontrado.'], 400);
        exit;
    }
    if ($idProveedor !== null && !hamilton_proveedor_existe($conn, $idProveedor)) {
        api_json_response(['ok' => false, 'error' => 'Proveedor no encontrado.'], 400);
        exit;
    }

    $sql = 'BEGIN M_HAMILTON_STORE.pkg_direcciones.sp_insertar_direccion(
        :otras, :id_prov, :id_cant, :id_dist, :id_cli, :id_prv
    ); END;';
    $st = oci_parse($conn, $sql);
    if (!$st) {
        api_json_response(['ok' => false, 'error' => api_oci_error_message($conn)], 500);
        exit;
    }
    oci_bind_by_name($st, ':otras', $otras, 4000);
    oci_bind_by_name($st, ':id_prov', $idProv);
    oci_bind_by_name($st, ':id_cant', $idCant);
    oci_bind_by_name($st, ':id_dist', $idDist);
    oci_bind_by_name($st, ':id_cli', $idCliente);
    oci_bind_by_name($st, ':id_prv', $idProveedor);
    if (!@oci_execute($st)) {
        api_json_response(['ok' => false, 'error' => api_oci_error_message($st)], 400);
        oci_free_statement($st);
        exit;
    }
    oci_free_statement($st);
    api_json_response(['ok' => true, 'message' => 'Dirección creada']);
    exit;
}

if ($action === 'update') {
    $id = isset($body['id']) ? (int) $body['id'] : 0;
    $otras = trim((string) ($body['otrasSenas'] ?? ''));
    $idProv = isset($body['idProvincia']) ? (int) $body['idProvincia'] : 0;
    $idCant = isset($body['idCanton']) ? (int) $body['idCanton'] : 0;
    $idDist = isset($body['idDistrito']) ? (int) $body['idDistrito'] : 0;
    $idCliIn = isset($body['idCliente']) ? (int) $body['idCliente'] : 0;
    $idPrvIn = isset($body['idProveedor']) ? (int) $body['idProveedor'] : 0;
    $idCliente = $idCliIn > 0 ? $idCliIn : null;
    $idProveedor = $idPrvIn > 0 ? $idPrvIn : null;

    if ($idCliente !== null && $idProveedor !== null) {
        api_json_response(['ok' => false, 'error' => 'Indique solo idCliente o idProveedor, no ambos'], 400);
        exit;
    }
    if ($idCliente === null && $idProveedor === null) {
        api_json_response(['ok' => false, 'error' => 'Debe enviar idCliente o idProveedor'], 400);
        exit;
    }

    if ($id <= 0) {
        api_json_response(['ok' => false, 'error' => 'id de dirección inválido.'], 400);
        exit;
    }
    $errTxtU = hamilton_texto_libre_validar($otras, 2000, true);
    if ($errTxtU !== null) {
        api_json_response(['ok' => false, 'error' => $errTxtU], 400);
        exit;
    }
    if ($idProv <= 0 || $idCant <= 0 || $idDist <= 0) {
        api_json_response(['ok' => false, 'error' => 'Provincia, cantón y distrito son obligatorios.'], 400);
        exit;
    }
    if ($idCliente !== null && !hamilton_cliente_existe($conn, $idCliente)) {
        api_json_response(['ok' => false, 'error' => 'Cliente no encontrado.'], 400);
        exit;
    }
    if ($idProveedor !== null && !hamilton_proveedor_existe($conn, $idProveedor)) {
        api_json_response(['ok' => false, 'error' => 'Proveedor no encontrado.'], 400);
        exit;
    }

    $sql = 'BEGIN M_HAMILTON_STORE.pkg_direcciones.sp_actualizar_direccion(
        :id, :otras, :id_prov, :id_cant, :id_dist, :id_cli, :id_prv
    ); END;';
    $st = oci_parse($conn, $sql);
    if (!$st) {
        api_json_response(['ok' => false, 'error' => api_oci_error_message($conn)], 500);
        exit;
    }
    oci_bind_by_name($st, ':id', $id);
    oci_bind_by_name($st, ':otras', $otras, 4000);
    oci_bind_by_name($st, ':id_prov', $idProv);
    oci_bind_by_name($st, ':id_cant', $idCant);
    oci_bind_by_name($st, ':id_dist', $idDist);
    oci_bind_by_name($st, ':id_cli', $idCliente);
    oci_bind_by_name($st, ':id_prv', $idProveedor);
    if (!@oci_execute($st)) {
        api_json_response(['ok' => false, 'error' => api_oci_error_message($st)], 400);
        oci_free_statement($st);
        exit;
    }
    oci_free_statement($st);
    api_json_response(['ok' => true, 'message' => 'Dirección actualizada']);
    exit;
}

if ($action === 'delete') {
    $id = isset($body['id']) ? (int) $body['id'] : 0;
    if ($id <= 0) {
        api_json_response(['ok' => false, 'error' => 'id de dirección inválido.'], 400);
        exit;
    }
    $sql = 'BEGIN M_HAMILTON_STORE.pkg_direcciones.sp_eliminar_direccion(:id); END;';
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
    api_json_response(['ok' => true, 'message' => 'Dirección eliminada']);
    exit;
}

api_json_response(['ok' => false, 'error' => 'action inválido: use insert, update o delete'], 400);
