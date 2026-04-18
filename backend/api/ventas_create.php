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
 *     { "productoId": 1, "cantidad": 2 }
 *   ]
 * (precioUnitario/subtotal del cliente se ignoran: se usan precio_venta y stock de Oracle.)
 * }
 *
 * Cliente tienda (sesión rol cliente): clienteId se toma de la sesión; empleado por HAMILTON_TIENDA_EMPLEADO_VENTA (o 1).
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

$fechaRaw = (string) ($body['fechaVenta'] ?? '');
$clienteIdBody = isset($body['clienteId']) ? (int) $body['clienteId'] : 0;
$lineas = $body['lineas'] ?? null;
$maxLineas = 100;

if ($esClienteTienda) {
    $clienteId = (int) $_SESSION['cliente_id'];
    if ($clienteIdBody > 0 && $clienteIdBody !== $clienteId) {
        api_json_response(['ok' => false, 'error' => 'No puede crear ventas para otro cliente'], 403);
        exit;
    }
    $empleadoId = hamilton_tienda_venta_empleado_default();
} else {
    $clienteId = $clienteIdBody;
    $empleadoId = api_session_empleado_id();
}

if ($fechaRaw === '' || $clienteId <= 0 || !is_array($lineas) || count($lineas) === 0) {
    api_json_response(['ok' => false, 'error' => 'fechaVenta, clienteId y lineas[] son obligatorios'], 400);
    exit;
}

if (count($lineas) > $maxLineas) {
    api_json_response(['ok' => false, 'error' => 'Demasiadas líneas en la venta (máximo ' . $maxLineas . ').'], 400);
    exit;
}

if (!hamilton_cliente_existe($conn, $clienteId)) {
    api_json_response(['ok' => false, 'error' => 'Cliente no válido o inexistente.'], 400);
    exit;
}

$cantidadPorProducto = [];
$idsOrden = [];
foreach ($lineas as $idx => $ln) {
    if (!is_array($ln)) {
        api_json_response(['ok' => false, 'error' => 'Cada línea debe ser un objeto'], 400);
        exit;
    }
    $idProd = isset($ln['productoId']) ? (int) $ln['productoId'] : 0;
    $cant = isset($ln['cantidad']) ? (int) $ln['cantidad'] : 0;
    if ($idProd <= 0 || $cant <= 0) {
        api_json_response(['ok' => false, 'error' => 'Cada línea requiere productoId y cantidad enteros mayores que cero.'], 400);
        exit;
    }
    if (!isset($cantidadPorProducto[$idProd])) {
        $cantidadPorProducto[$idProd] = 0;
        $idsOrden[] = $idProd;
    }
    $cantidadPorProducto[$idProd] += $cant;
}

$mapaProd = hamilton_productos_datos_venta_por_ids($conn, $idsOrden);
foreach ($cantidadPorProducto as $idProd => $cantTotal) {
    if (!isset($mapaProd[$idProd])) {
        api_json_response(['ok' => false, 'error' => 'Producto no encontrado: ID ' . $idProd], 400);
        exit;
    }
    $info = $mapaProd[$idProd];
    if ($info['estado'] !== 'activo') {
        api_json_response(['ok' => false, 'error' => 'No se puede vender el producto ID ' . $idProd . ' (no está activo).'], 400);
        exit;
    }
    if (!hamilton_precio_no_negativo_valido($info['precio_venta'])) {
        api_json_response(['ok' => false, 'error' => 'Precio de venta inválido para el producto ID ' . $idProd . '.'], 400);
        exit;
    }
    if ($cantTotal > $info['cantidad']) {
        api_json_response([
            'ok'    => false,
            'error' => 'Stock insuficiente para el producto ID ' . $idProd . ' (solicitado: ' . $cantTotal . ', disponible: ' . $info['cantidad'] . ').',
        ], 400);
        exit;
    }
}

$lineasServidor = [];
$total = 0.0;
foreach ($lineas as $ln) {
    $idProd = (int) $ln['productoId'];
    $cant = (int) $ln['cantidad'];
    $info = $mapaProd[$idProd];
    $pu = (float) $info['precio_venta'];
    $sub = round($cant * $pu, 2);
    if (!is_finite($sub) || $sub < 0) {
        api_json_response(['ok' => false, 'error' => 'Error al calcular subtotal de línea.'], 400);
        exit;
    }
    $total += $sub;
    $lineasServidor[] = [
        'productoId'       => $idProd,
        'cantidad'         => $cant,
        'precioUnitario'   => $pu,
        'subtotal'         => $sub,
    ];
}

if (!is_finite($total) || $total < 0) {
    api_json_response(['ok' => false, 'error' => 'Total de venta inválido.'], 400);
    exit;
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

foreach ($lineasServidor as $ln) {
    $cant = (int) $ln['cantidad'];
    $pu = (float) $ln['precioUnitario'];
    $sub = (float) $ln['subtotal'];
    $idProd = (int) $ln['productoId'];

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
