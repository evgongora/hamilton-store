<?php
/**
 * GET — Movimientos de gestión de stock vía pkg_gestion_stock.sp_listar_gestion_stock.
 */
declare(strict_types=1);

require_once __DIR__ . '/../config/api_helpers.php';

api_require_staff_session();
api_require_method('GET');
$conn = api_require_oracle();

$sql = 'BEGIN M_HAMILTON_STORE.pkg_gestion_stock.sp_listar_gestion_stock(:cur); END;';
$out = api_oci_ref_cursor_fetch_all($conn, $sql, ':cur');
if ($out['error'] !== null) {
    api_json_response(['ok' => false, 'error' => $out['error']], 500);
    exit;
}

$rows = [];
foreach ($out['rows'] as $row) {
    $fvRaw = $row['fecha_gestion'] ?? null;
    $fechaIso = null;
    if ($fvRaw !== null && $fvRaw !== '') {
        $ts = strtotime((string) $fvRaw);
        if ($ts !== false) {
            $fechaIso = gmdate('c', $ts);
        }
    }
    $rows[] = [
        'id'               => (int) ($row['id_gestion_stock'] ?? 0),
        'cantidad'       => isset($row['cantidad']) ? (int) $row['cantidad'] : 0,
        'fechaGestion'   => $fechaIso,
        'idProducto'     => (int) ($row['productos_id_producto'] ?? 0),
        'nombreProducto' => (string) ($row['producto'] ?? ''),
        'idTipoGestion'  => (int) ($row['tipo_gestion_id_tipo_gestion'] ?? 0),
        'tipoGestion'    => (string) ($row['tipo_gestion'] ?? ''),
    ];
}

api_json_response(['ok' => true, 'data' => $rows]);
