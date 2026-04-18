<?php
/**
 * GET — Lista cantones vía M_HAMILTON_STORE.pkg_cantones.sp_listar_cantones (REF CURSOR).
 */
declare(strict_types=1);

require_once __DIR__ . '/../config/api_helpers.php';

api_require_staff_session();
api_require_method('GET');
$conn = api_require_oracle();

$sql = 'BEGIN M_HAMILTON_STORE.pkg_cantones.sp_listar_cantones(:cur); END;';
$out = api_oci_ref_cursor_fetch_all($conn, $sql, ':cur');
if ($out['error'] !== null) {
    api_json_response(['ok' => false, 'error' => $out['error']], 500);
    exit;
}

$rows = [];
foreach ($out['rows'] as $row) {
    $rows[] = [
        'id'                => (int) ($row['id_canton'] ?? 0),
        'nombre'            => (string) ($row['nombre'] ?? ''),
        'idProvincia'       => (int) ($row['provincias_id_provincia'] ?? 0),
        'nombreProvincia'   => (string) ($row['provincia'] ?? ''),
    ];
}

api_json_response(['ok' => true, 'data' => $rows]);
