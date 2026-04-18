<?php
/**
 * GET — Lista direcciones vía M_HAMILTON_STORE.pkg_direcciones.sp_listar_direcciones (REF CURSOR).
 */
declare(strict_types=1);

require_once __DIR__ . '/../config/api_helpers.php';

api_require_staff_session();
api_require_method('GET');
$conn = api_require_oracle();

$sql = 'BEGIN M_HAMILTON_STORE.pkg_direcciones.sp_listar_direcciones(:cur); END;';
$out = api_oci_ref_cursor_fetch_all($conn, $sql, ':cur');
if ($out['error'] !== null) {
    api_json_response(['ok' => false, 'error' => $out['error']], 500);
    exit;
}

$rows = [];
foreach ($out['rows'] as $row) {
    $idCli = $row['clientes_id_cliente'] ?? null;
    $idPrv = $row['proveedores_id_proveedor'] ?? null;
    $rows[] = [
        'id'                 => (int) ($row['id_direccion'] ?? 0),
        'otrasSenas'         => (string) ($row['otras_senas'] ?? ''),
        'idProvincia'        => (int) ($row['provincias_id_provincia'] ?? 0),
        'nombreProvincia'    => (string) ($row['provincia'] ?? ''),
        'idCanton'           => (int) ($row['cantones_id_canton'] ?? 0),
        'nombreCanton'       => (string) ($row['canton'] ?? ''),
        'idDistrito'         => (int) ($row['distritos_id_distrito'] ?? 0),
        'nombreDistrito'     => (string) ($row['distrito'] ?? ''),
        'idCliente'          => $idCli !== null && $idCli !== '' ? (int) $idCli : null,
        'idProveedor'        => $idPrv !== null && $idPrv !== '' ? (int) $idPrv : null,
    ];
}

api_json_response(['ok' => true, 'data' => $rows]);
