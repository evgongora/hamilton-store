<?php
/**
 * POST JSON — CRUD gestion_stock (M_HAMILTON_STORE.pkg_gestion_stock).
 * cantidad ≠ 0 (validación del paquete).
 *
 * { "action": "insert", "cantidad": 10, "fechaGestion": "2026-04-17", "idProducto": 1, "idTipoGestion": 1 }
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
    $cant = isset($body['cantidad']) ? (int) $body['cantidad'] : 0;
    $fechaRaw = trim((string) ($body['fechaGestion'] ?? ''));
    $idProd = isset($body['idProducto']) ? (int) $body['idProducto'] : 0;
    $idTipo = isset($body['idTipoGestion']) ? (int) $body['idTipoGestion'] : 0;
    $ts = strtotime($fechaRaw);
    if ($ts === false) {
        api_json_response(['ok' => false, 'error' => 'fechaGestion inválida (use YYYY-MM-DD)'], 400);
        exit;
    }
    $fechaBind = date('Y-m-d', $ts);

    $sql = 'BEGIN M_HAMILTON_STORE.pkg_gestion_stock.sp_insertar_gestion_stock(
        :cant, TO_DATE(:fecha, \'YYYY-MM-DD\'), :prod, :tipo
    ); END;';
    $st = oci_parse($conn, $sql);
    if (!$st) {
        api_json_response(['ok' => false, 'error' => api_oci_error_message($conn)], 500);
        exit;
    }
    oci_bind_by_name($st, ':cant', $cant);
    oci_bind_by_name($st, ':fecha', $fechaBind, 32);
    oci_bind_by_name($st, ':prod', $idProd);
    oci_bind_by_name($st, ':tipo', $idTipo);
    if (!@oci_execute($st)) {
        api_json_response(['ok' => false, 'error' => api_oci_error_message($st)], 400);
        oci_free_statement($st);
        exit;
    }
    oci_free_statement($st);
    api_json_response(['ok' => true, 'message' => 'Movimiento de stock registrado']);
    exit;
}

if ($action === 'update') {
    $id = isset($body['id']) ? (int) $body['id'] : 0;
    $cant = isset($body['cantidad']) ? (int) $body['cantidad'] : 0;
    $fechaRaw = trim((string) ($body['fechaGestion'] ?? ''));
    $idProd = isset($body['idProducto']) ? (int) $body['idProducto'] : 0;
    $idTipo = isset($body['idTipoGestion']) ? (int) $body['idTipoGestion'] : 0;
    $ts = strtotime($fechaRaw);
    if ($ts === false) {
        api_json_response(['ok' => false, 'error' => 'fechaGestion inválida'], 400);
        exit;
    }
    $fechaBind = date('Y-m-d', $ts);

    $sql = 'BEGIN M_HAMILTON_STORE.pkg_gestion_stock.sp_actualizar_gestion_stock(
        :id, :cant, TO_DATE(:fecha, \'YYYY-MM-DD\'), :prod, :tipo
    ); END;';
    $st = oci_parse($conn, $sql);
    if (!$st) {
        api_json_response(['ok' => false, 'error' => api_oci_error_message($conn)], 500);
        exit;
    }
    oci_bind_by_name($st, ':id', $id);
    oci_bind_by_name($st, ':cant', $cant);
    oci_bind_by_name($st, ':fecha', $fechaBind, 32);
    oci_bind_by_name($st, ':prod', $idProd);
    oci_bind_by_name($st, ':tipo', $idTipo);
    if (!@oci_execute($st)) {
        api_json_response(['ok' => false, 'error' => api_oci_error_message($st)], 400);
        oci_free_statement($st);
        exit;
    }
    oci_free_statement($st);
    api_json_response(['ok' => true, 'message' => 'Movimiento actualizado']);
    exit;
}

if ($action === 'delete') {
    $id = isset($body['id']) ? (int) $body['id'] : 0;
    $sql = 'BEGIN M_HAMILTON_STORE.pkg_gestion_stock.sp_eliminar_gestion_stock(:id); END;';
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
    api_json_response(['ok' => true, 'message' => 'Movimiento eliminado']);
    exit;
}

api_json_response(['ok' => false, 'error' => 'action inválido: use insert, update o delete'], 400);
