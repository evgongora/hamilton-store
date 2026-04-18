<?php
/**
 * GET — Lista distritos vía M_HAMILTON_STORE.pkg_distritos.sp_listar_distritos (REF CURSOR).
 */
declare(strict_types=1);

require_once __DIR__ . '/../config/api_helpers.php';

api_require_staff_session();
api_require_method('GET');
$conn = api_require_oracle();

$sql = 'BEGIN M_HAMILTON_STORE.pkg_distritos.sp_listar_distritos(:cur); END;';
$out = api_oci_ref_cursor_fetch_all($conn, $sql, ':cur');
if ($out['error'] !== null) {
    api_json_response(['ok' => false, 'error' => $out['error']], 500);
    exit;
}

$rows = [];
foreach ($out['rows'] as $row) {
    $cp = $row['codigo_postal'] ?? null;
    $rows[] = [
        'id'              => (int) ($row['id_distrito'] ?? 0),
        'nombre'          => (string) ($row['nombre'] ?? ''),
        'idCanton'        => (int) ($row['cantones_id_canton'] ?? 0),
        'nombreCanton'    => (string) ($row['canton'] ?? ''),
        'codigoPostal'    => $cp !== null && $cp !== '' ? (int) $cp : null,
    ];
}

api_json_response(['ok' => true, 'data' => $rows]);
