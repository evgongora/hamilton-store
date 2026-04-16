<?php
/**
 * POST JSON — Crea encabezado de compra + líneas (pkg_encabezados_compras + pkg_detalles_compras).
 *
 * Body:
 * {
 *   "fechaCompra": "2026-02-14",
 *   "proveedorId": 1,
 *   "lineas": [
 *     { "productoId": 1, "cantidad": 5, "precioUnitario": 8000 }
 *   ]
 * }
 */
declare(strict_types=1);

require_once __DIR__ . '/../config/api_helpers.php';

api_require_staff_session();
api_require_method('POST');
$conn = api_require_oracle();
$body = api_read_json_body();

$fechaRaw = (string) ($body['fechaCompra'] ?? '');
$provId = isset($body['proveedorId']) ? (int) $body['proveedorId'] : 0;
$empleadoId = api_session_empleado_id();
$lineas = $body['lineas'] ?? null;

if ($fechaRaw === '' || $provId <= 0 || !is_array($lineas) || count($lineas) === 0) {
    api_json_response(['ok' => false, 'error' => 'fechaCompra, proveedorId y lineas[] son obligatorios'], 400);
    exit;
}

$total = 0.0;
foreach ($lineas as $ln) {
    if (!is_array($ln)) {
        api_json_response(['ok' => false, 'error' => 'Cada línea debe ser un objeto'], 400);
        exit;
    }
    $cant = isset($ln['cantidad']) ? (float) $ln['cantidad'] : 0.0;
    $pu = isset($ln['precioUnitario']) ? (float) $ln['precioUnitario'] : 0.0;
    $total += $cant * $pu;
}

$ts = strtotime($fechaRaw);
if ($ts === false) {
    api_json_response(['ok' => false, 'error' => 'fechaCompra inválida'], 400);
    exit;
}
$fechaBind = date('Y-m-d', $ts);

$sqlEnc = <<<'SQL'
BEGIN
  M_HAMILTON_STORE.pkg_encabezados_compras.sp_insertar_encabezado_compra(
    TO_DATE(:fecha, 'YYYY-MM-DD'),
    :total,
    :prov,
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
oci_bind_by_name($st, ':prov', $provId);
oci_bind_by_name($st, ':empleado', $empleadoId);

if (!@oci_execute($st, OCI_NO_AUTO_COMMIT)) {
    api_json_response(['ok' => false, 'error' => api_oci_error_message($st)], 400);
    oci_free_statement($st);
    exit;
}
oci_free_statement($st);

$qId = oci_parse($conn, 'SELECT id_compra FROM encabezados_compras ORDER BY id_compra DESC FETCH FIRST 1 ROW ONLY');
if (!$qId || !oci_execute($qId, OCI_NO_AUTO_COMMIT)) {
    oci_rollback($conn);
    api_json_response(['ok' => false, 'error' => 'No se pudo obtener el id de compra'], 500);
    exit;
}
$rowId = oci_fetch_assoc($qId);
oci_free_statement($qId);
if ($rowId === false) {
    oci_rollback($conn);
    api_json_response(['ok' => false, 'error' => 'Compra no encontrada tras insertar'], 500);
    exit;
}
$idCompra = (int) ($rowId['ID_COMPRA'] ?? $rowId['id_compra'] ?? 0);

$sqlDet = <<<'SQL'
BEGIN
  M_HAMILTON_STORE.pkg_detalles_compras.sp_insertar_detalle_compra(
    :cant,
    :pu,
    :id_compra,
    :id_prod
  );
END;
SQL;

foreach ($lineas as $ln) {
    $cant = isset($ln['cantidad']) ? (float) $ln['cantidad'] : 0.0;
    $pu = isset($ln['precioUnitario']) ? (float) $ln['precioUnitario'] : 0.0;
    $idProd = isset($ln['productoId']) ? (int) $ln['productoId'] : 0;
    if ($cant <= 0 || $pu < 0 || $idProd <= 0) {
        oci_rollback($conn);
        api_json_response(['ok' => false, 'error' => 'Línea inválida: productoId, cantidad y precioUnitario'], 400);
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
    oci_bind_by_name($st, ':id_compra', $idCompra);
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

api_json_response(['ok' => true, 'idCompra' => $idCompra, 'message' => 'Compra registrada']);
