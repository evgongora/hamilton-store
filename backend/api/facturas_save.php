<?php
/**
 * POST JSON — CRUD facturas vía M_HAMILTON_STORE.pkg_facturas.
 * xml: si no se envía o va vacío, se pasa NULL al procedimiento (CLOB).
 *
 * insert: { "action": "insert", "numeroFactura": "FAC-1", "claveHacienda": "", "fechaEmision": "2026-02-14", "idEstado": 1, "idVenta": 1 }
 * update: { "action": "update", "id": 1, ... }
 * delete: { "action": "delete", "id": 1 }
 */
declare(strict_types=1);

require_once __DIR__ . '/../config/api_helpers.php';

api_require_staff_session();
api_require_method('POST');
$conn = api_require_oracle();
$body = api_read_json_body();
$action = isset($body['action']) ? strtolower(trim((string) $body['action'])) : '';

if ($action === 'insert') {
    $numero = trim((string) ($body['numeroFactura'] ?? ''));
    $clave = isset($body['claveHacienda']) ? trim((string) $body['claveHacienda']) : '';
    $fechaRaw = (string) ($body['fechaEmision'] ?? '');
    $idEst = isset($body['idEstado']) ? (int) $body['idEstado'] : 0;
    $idVenta = isset($body['idVenta']) ? (int) $body['idVenta'] : 0;

    if ($numero === '' || $fechaRaw === '' || $idEst <= 0 || $idVenta <= 0) {
        api_json_response(['ok' => false, 'error' => 'numeroFactura, fechaEmision, idEstado e idVenta son obligatorios'], 400);
        exit;
    }
    $ts = strtotime($fechaRaw);
    if ($ts === false) {
        api_json_response(['ok' => false, 'error' => 'fechaEmision inválida'], 400);
        exit;
    }
    $fechaBind = date('Y-m-d', $ts);

    $sql = <<<'SQL'
BEGIN
  M_HAMILTON_STORE.pkg_facturas.sp_insertar_factura(
    :numero,
    :clave,
    TO_DATE(:fecha, 'YYYY-MM-DD'),
    :estado,
    NULL,
    :venta
  );
END;
SQL;

    $st = oci_parse($conn, $sql);
    if (!$st) {
        api_json_response(['ok' => false, 'error' => api_oci_error_message($conn)], 500);
        exit;
    }
    oci_bind_by_name($st, ':numero', $numero, 4000);
    oci_bind_by_name($st, ':clave', $clave, 4000);
    oci_bind_by_name($st, ':fecha', $fechaBind);
    oci_bind_by_name($st, ':estado', $idEst);
    oci_bind_by_name($st, ':venta', $idVenta);

    if (!@oci_execute($st)) {
        api_json_response(['ok' => false, 'error' => api_oci_error_message($st)], 400);
        oci_free_statement($st);
        exit;
    }
    oci_free_statement($st);
    api_json_response(['ok' => true, 'message' => 'Factura creada']);
    exit;
}

if ($action === 'update') {
    $id = isset($body['id']) ? (int) $body['id'] : 0;
    $numero = trim((string) ($body['numeroFactura'] ?? ''));
    $clave = isset($body['claveHacienda']) ? trim((string) $body['claveHacienda']) : '';
    $fechaRaw = (string) ($body['fechaEmision'] ?? '');
    $idEst = isset($body['idEstado']) ? (int) $body['idEstado'] : 0;
    $idVenta = isset($body['idVenta']) ? (int) $body['idVenta'] : 0;

    if ($id <= 0 || $numero === '' || $fechaRaw === '' || $idEst <= 0 || $idVenta <= 0) {
        api_json_response(['ok' => false, 'error' => 'id, numeroFactura, fechaEmision, idEstado e idVenta son obligatorios'], 400);
        exit;
    }
    $ts = strtotime($fechaRaw);
    if ($ts === false) {
        api_json_response(['ok' => false, 'error' => 'fechaEmision inválida'], 400);
        exit;
    }
    $fechaBind = date('Y-m-d', $ts);

    $sql = <<<'SQL'
BEGIN
  M_HAMILTON_STORE.pkg_facturas.sp_actualizar_factura(
    :id,
    :numero,
    :clave,
    TO_DATE(:fecha, 'YYYY-MM-DD'),
    :estado,
    NULL,
    :venta
  );
END;
SQL;

    $st = oci_parse($conn, $sql);
    if (!$st) {
        api_json_response(['ok' => false, 'error' => api_oci_error_message($conn)], 500);
        exit;
    }
    oci_bind_by_name($st, ':id', $id);
    oci_bind_by_name($st, ':numero', $numero, 4000);
    oci_bind_by_name($st, ':clave', $clave, 4000);
    oci_bind_by_name($st, ':fecha', $fechaBind);
    oci_bind_by_name($st, ':estado', $idEst);
    oci_bind_by_name($st, ':venta', $idVenta);

    if (!@oci_execute($st)) {
        api_json_response(['ok' => false, 'error' => api_oci_error_message($st)], 400);
        oci_free_statement($st);
        exit;
    }
    oci_free_statement($st);
    api_json_response(['ok' => true, 'message' => 'Factura actualizada']);
    exit;
}

if ($action === 'delete') {
    $id = isset($body['id']) ? (int) $body['id'] : 0;
    if ($id <= 0) {
        api_json_response(['ok' => false, 'error' => 'id obligatorio'], 400);
        exit;
    }
    $sql = 'BEGIN M_HAMILTON_STORE.pkg_facturas.sp_eliminar_factura(:id); END;';
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
    api_json_response(['ok' => true, 'message' => 'Factura eliminada']);
    exit;
}

api_json_response(['ok' => false, 'error' => 'action inválido: insert, update o delete'], 400);
