<?php
/**
 * GET — Encabezados de venta con total pagado (para módulo Pagos).
 */
declare(strict_types=1);

require_once __DIR__ . '/../config/api_helpers.php';

api_require_staff_session();
api_require_method('GET');
$conn = api_require_oracle();

$sql = <<<'SQL'
SELECT ev.id_venta,
       ev.fecha_venta,
       ev.total_venta,
       ev.clientes_id_cliente,
       c.nombre AS cliente_nombre,
       c.apellido AS cliente_apellido,
       NVL(SUM(p.monto), 0) AS pagado
  FROM encabezados_ventas ev
  JOIN clientes c ON c.id_cliente = ev.clientes_id_cliente
  LEFT JOIN pagos p ON p.encabezados_ventas_id_venta = ev.id_venta
 GROUP BY ev.id_venta, ev.fecha_venta, ev.total_venta, ev.clientes_id_cliente,
          c.nombre, c.apellido
 ORDER BY ev.id_venta DESC
SQL;

$st = oci_parse($conn, $sql);
if (!$st || !oci_execute($st)) {
    $e = oci_error($st ?: $conn);
    api_json_response(['ok' => false, 'error' => $e['message'] ?? 'Error'], 500);
    exit;
}

$rows = [];
while ($row = oci_fetch_assoc($st)) {
    $row = array_change_key_case($row, CASE_LOWER);
    $total = (float) $row['total_venta'];
    $pagado = (float) $row['pagado'];
    $fvRaw = $row['fecha_venta'] ?? null;
    $fechaIso = null;
    if ($fvRaw !== null && $fvRaw !== '') {
        $ts = strtotime((string) $fvRaw);
        if ($ts !== false) {
            $fechaIso = gmdate('c', $ts);
        }
    }
    $rows[] = [
        'id'            => (int) $row['id_venta'],
        'fechaVenta'    => $fvRaw,
        'fechaVentaIso' => $fechaIso,
        'total'         => $total,
        'pagado'        => $pagado,
        'pendiente'     => $total - $pagado,
        'clienteId'     => (int) $row['clientes_id_cliente'],
        'clienteNombre' => trim(($row['cliente_nombre'] ?? '') . ' ' . ($row['cliente_apellido'] ?? '')),
    ];
}
oci_free_statement($st);

api_json_response(['ok' => true, 'data' => $rows]);
