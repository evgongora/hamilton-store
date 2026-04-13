<?php
/**
 * POST JSON — Crea encabezado de venta + líneas (pkg_encabezados_ventas + pkg_detalles_ventas).
 * Transacción única: rollback si falla cualquier línea (triggers incluidos).
 *
 * Body:
 * {
 *   "fechaVenta": "2026-02-14",
 *   "clienteId": 1,
 *   "lineas": [
 *     { "productoId": 1, "cantidad": 2, "precioUnitario": 10000, "subtotal": 20000 }
 *   ]
 * }
 */
declare(strict_types=1);

require_once __DIR__ . '/../config/api_helpers.php';

api_require_staff_session();
api_require_method('POST');
$conn = api_require_oracle();
$body = api_read_json_body();

$fechaRaw = (string) ($body['fechaVenta'] ?? '');
$clienteId = isset($body['clienteId']) ? (int) $body['clienteId'] : 0;
$empleadoId = api_session_empleado_id();
$lineas = $body['lineas'] ?? null;

if ($fechaRaw === '' || $clienteId <= 0 || !is_array($lineas) || count($lineas) === 0) {
    api_json_response(['ok' => false, 'error' => 'fechaVenta, clienteId y lineas[] son obligatorios'], 400);
    exit;
}

$total = 0.0;
foreach ($lineas as $ln) {
    if (!is_array($ln)) {
        api_json_response(['ok' => false, 'error' => 'Cada línea debe ser un objeto'], 400);
        exit;
    }
    $sub = isset($ln['subtotal']) ? (float) $ln['subtotal'] : 0.0;
    if ($sub < 0) {
        api_json_response(['ok' => false, 'error' => 'subtotal inválido'], 400);
        exit;
    }
    $total += $sub;
}

$ts = strtotime($fechaRaw);
if ($ts === false) {
    api_json_response(['ok' => false, 'error' => 'fechaVenta inválida'], 400);
    exit;
}
$fechaBind = date('Y-m-d', $ts);

$sqlEnc = <<<'SQL'
BEGIN
  M_HAMILTON_STORE.pkg_encabezados_ventas.sp_insertar_encabezado_venta(
    TO_DATE(:fecha, 'YYYY-MM-DD'),
    :total,
    :cliente,
    :empleado
  );
END;
SQL;

$st = oci_parse($conn, $sqlEnc);
if (!$st) {
    api_json_response(['ok' => false, 'error' => api_oci_error_message($conn)], 500);
    exit;
}

oci_bind_by_name($st, ':fecha', $fechaBind);
oci_bind_by_name($st, ':total', $total);
oci_bind_by_name($st, ':cliente', $clienteId);
oci_bind_by_name($st, ':empleado', $empleadoId);

if (!@oci_execute($st, OCI_NO_AUTO_COMMIT)) {
    api_json_response(['ok' => false, 'error' => api_oci_error_message($st)], 400);
    oci_free_statement($st);
    exit;
}
oci_free_statement($st);

$qId = oci_parse($conn, 'SELECT id_venta FROM encabezados_ventas ORDER BY id_venta DESC FETCH FIRST 1 ROW ONLY');
if (!$qId || !oci_execute($qId, OCI_NO_AUTO_COMMIT)) {
    oci_rollback($conn);
    api_json_response(['ok' => false, 'error' => 'No se pudo obtener el id de venta'], 500);
    exit;
}
$rowId = oci_fetch_assoc($qId);
oci_free_statement($qId);
if ($rowId === false) {
    oci_rollback($conn);
    api_json_response(['ok' => false, 'error' => 'Venta no encontrada tras insertar'], 500);
    exit;
}
$idVenta = (int) ($rowId['ID_VENTA'] ?? $rowId['id_venta'] ?? 0);

$sqlDet = <<<'SQL'
BEGIN
  M_HAMILTON_STORE.pkg_detalles_ventas.sp_insertar_detalle_venta(
    :cant,
    :pu,
    :sub,
    :id_venta,
    :id_prod
  );
END;
SQL;

foreach ($lineas as $ln) {
    $cant = isset($ln['cantidad']) ? (int) $ln['cantidad'] : 0;
    $pu = isset($ln['precioUnitario']) ? (float) $ln['precioUnitario'] : 0.0;
    $sub = isset($ln['subtotal']) ? (float) $ln['subtotal'] : 0.0;
    $idProd = isset($ln['productoId']) ? (int) $ln['productoId'] : 0;
    if ($cant <= 0 || $idProd <= 0) {
        oci_rollback($conn);
        api_json_response(['ok' => false, 'error' => 'Cada línea requiere productoId y cantidad > 0'], 400);
        exit;
    }

    $st = oci_parse($conn, $sqlDet);
    if (!$st) {
        oci_rollback($conn);
        api_json_response(['ok' => false, 'error' => api_oci_error_message($conn)], 500);
        exit;
    }
    oci_bind_by_name($st, ':cant', $cant);
    oci_bind_by_name($st, ':pu', $pu);
    oci_bind_by_name($st, ':sub', $sub);
    oci_bind_by_name($st, ':id_venta', $idVenta);
    oci_bind_by_name($st, ':id_prod', $idProd);

    if (!@oci_execute($st, OCI_NO_AUTO_COMMIT)) {
        $err = api_oci_error_message($st);
        oci_free_statement($st);
        oci_rollback($conn);
        api_json_response(['ok' => false, 'error' => $err], 400);
        exit;
    }
    oci_free_statement($st);
}

if (!oci_commit($conn)) {
    oci_rollback($conn);
    api_json_response(['ok' => false, 'error' => 'No se pudo confirmar la transacción'], 500);
    exit;
}

api_json_response(['ok' => true, 'idVenta' => $idVenta, 'message' => 'Venta registrada']);
