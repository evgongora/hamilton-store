<?php
/**
 * POST JSON — CRUD pagos vía M_HAMILTON_STORE.pkg_pagos.
 *
 * { "action": "insert", "monto": 100, "fechaPago": "2026-02-14", "idMetodoPago": 1, "idVenta": 1 }
 * { "action": "update", "id": 1, ... }
 * { "action": "delete", "id": 1 }
 */
declare(strict_types=1);

require_once __DIR__ . '/../config/api_helpers.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$role = $_SESSION['role'] ?? '';
$esStaff = in_array($role, ['admin', 'cajero', 'inventario', 'soporte'], true);
$esClienteTienda = ($role === 'cliente' && !empty($_SESSION['cliente_id']));
if (!$esStaff && !$esClienteTienda) {
    api_json_response(['ok' => false, 'error' => 'No autorizado'], 401);
    exit;
}

api_require_method('POST');
$conn = api_require_oracle();
$body = api_read_json_body();
$action = isset($body['action']) ? strtolower(trim((string) $body['action'])) : '';

if ($action !== 'insert' && !$esStaff) {
    api_json_response(['ok' => false, 'error' => 'Solo el personal puede modificar o eliminar pagos'], 403);
    exit;
}

if ($action === 'insert') {
    $monto = isset($body['monto']) ? (float) $body['monto'] : null;
    $fechaRaw = (string) ($body['fechaPago'] ?? '');
    $idMetodo = isset($body['idMetodoPago']) ? (int) $body['idMetodoPago'] : 0;
    $idVenta = isset($body['idVenta']) ? (int) $body['idVenta'] : 0;

    if ($monto === null || $fechaRaw === '' || $idMetodo <= 0 || $idVenta <= 0) {
        api_json_response(['ok' => false, 'error' => 'monto, fechaPago, idMetodoPago e idVenta son obligatorios'], 400);
        exit;
    }
    if (!hamilton_monto_positivo_valido($monto)) {
        api_json_response(['ok' => false, 'error' => 'El monto debe ser un número finito mayor que cero.'], 400);
        exit;
    }
    if (!hamilton_metodo_pago_existe($conn, $idMetodo)) {
        api_json_response(['ok' => false, 'error' => 'Método de pago inválido.'], 400);
        exit;
    }
    if (!hamilton_encabezado_venta_existe($conn, $idVenta)) {
        api_json_response(['ok' => false, 'error' => 'Venta no encontrada.'], 400);
        exit;
    }
    $ts = strtotime($fechaRaw);
    if ($ts === false) {
        api_json_response(['ok' => false, 'error' => 'fechaPago inválida'], 400);
        exit;
    }
    $fechaBind = date('Y-m-d', $ts);

    if ($esClienteTienda) {
        $cidSes = (int) $_SESSION['cliente_id'];
        $propietario = hamilton_encabezado_venta_id_cliente($conn, $idVenta);
        if ($propietario === null || $propietario !== $cidSes) {
            api_json_response(['ok' => false, 'error' => 'La venta no pertenece a su cuenta'], 403);
            exit;
        }
    }

    $sql = <<<'SQL'
BEGIN
  M_HAMILTON_STORE.pkg_pagos.sp_insertar_pago(
    :monto,
    TO_DATE(:fecha, 'YYYY-MM-DD'),
    :metodo,
    :venta
  );
END;
SQL;
    $st = oci_parse($conn, $sql);
    if (!$st) {
        api_json_response(['ok' => false, 'error' => api_oci_error_message($conn)], 500);
        exit;
    }
    oci_bind_by_name($st, ':monto', $monto);
    oci_bind_by_name($st, ':fecha', $fechaBind);
    oci_bind_by_name($st, ':metodo', $idMetodo);
    oci_bind_by_name($st, ':venta', $idVenta);

    if (!@oci_execute($st)) {
        api_json_response(['ok' => false, 'error' => api_oci_error_message($st)], 400);
        oci_free_statement($st);
        exit;
    }
    oci_free_statement($st);
    api_json_response(['ok' => true, 'message' => 'Pago registrado']);
    exit;
}

if ($action === 'update') {
    $id = isset($body['id']) ? (int) $body['id'] : 0;
    $monto = isset($body['monto']) ? (float) $body['monto'] : null;
    $fechaRaw = (string) ($body['fechaPago'] ?? '');
    $idMetodo = isset($body['idMetodoPago']) ? (int) $body['idMetodoPago'] : 0;
    $idVenta = isset($body['idVenta']) ? (int) $body['idVenta'] : 0;

    if ($id <= 0 || $monto === null || $fechaRaw === '' || $idMetodo <= 0 || $idVenta <= 0) {
        api_json_response(['ok' => false, 'error' => 'id, monto, fechaPago, idMetodoPago e idVenta son obligatorios'], 400);
        exit;
    }
    if (!hamilton_monto_positivo_valido($monto)) {
        api_json_response(['ok' => false, 'error' => 'El monto debe ser un número finito mayor que cero.'], 400);
        exit;
    }
    if (!hamilton_metodo_pago_existe($conn, $idMetodo)) {
        api_json_response(['ok' => false, 'error' => 'Método de pago inválido.'], 400);
        exit;
    }
    if (!hamilton_encabezado_venta_existe($conn, $idVenta)) {
        api_json_response(['ok' => false, 'error' => 'Venta no encontrada.'], 400);
        exit;
    }
    $ts = strtotime($fechaRaw);
    if ($ts === false) {
        api_json_response(['ok' => false, 'error' => 'fechaPago inválida'], 400);
        exit;
    }
    $fechaBind = date('Y-m-d', $ts);

    $sql = <<<'SQL'
BEGIN
  M_HAMILTON_STORE.pkg_pagos.sp_actualizar_pago(
    :id,
    :monto,
    TO_DATE(:fecha, 'YYYY-MM-DD'),
    :metodo,
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
    oci_bind_by_name($st, ':monto', $monto);
    oci_bind_by_name($st, ':fecha', $fechaBind);
    oci_bind_by_name($st, ':metodo', $idMetodo);
    oci_bind_by_name($st, ':venta', $idVenta);

    if (!@oci_execute($st)) {
        api_json_response(['ok' => false, 'error' => api_oci_error_message($st)], 400);
        oci_free_statement($st);
        exit;
    }
    oci_free_statement($st);
    api_json_response(['ok' => true, 'message' => 'Pago actualizado']);
    exit;
}

if ($action === 'delete') {
    $id = isset($body['id']) ? (int) $body['id'] : 0;
    if ($id <= 0) {
        api_json_response(['ok' => false, 'error' => 'id obligatorio'], 400);
        exit;
    }
    $sql = 'BEGIN M_HAMILTON_STORE.pkg_pagos.sp_eliminar_pago(:id); END;';
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
    api_json_response(['ok' => true, 'message' => 'Pago eliminado']);
    exit;
}

api_json_response(['ok' => false, 'error' => 'action inválido: insert, update o delete'], 400);
