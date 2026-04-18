<?php
/**
 * GET — Tipos de gestión de stock vía pkg_tipo_gestion.sp_listar_tipos_gestion.
 */
declare(strict_types=1);

require_once __DIR__ . '/../config/api_helpers.php';

api_require_staff_session();
api_require_method('GET');
$conn = api_require_oracle();

$sql = 'BEGIN M_HAMILTON_STORE.pkg_tipo_gestion.sp_listar_tipos_gestion(:cur); END;';
$out = api_oci_ref_cursor_fetch_all($conn, $sql, ':cur');
if ($out['error'] !== null) {
    api_json_response(['ok' => false, 'error' => $out['error']], 500);
    exit;
}

$rows = [];
foreach ($out['rows'] as $row) {
    $rows[] = [
        'id'           => (int) ($row['id_tipo_gestion'] ?? 0),
        'descripcion'  => (string) ($row['descripcion'] ?? ''),
    ];
}

api_json_response(['ok' => true, 'data' => $rows]);
