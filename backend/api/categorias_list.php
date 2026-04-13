<?php
/**
 * GET — Lista categorías (catálogo / filtros en sistema).
 */
declare(strict_types=1);

require_once __DIR__ . '/../config/api_helpers.php';

api_require_method('GET');
$conn = api_require_oracle();

$sql = <<<'SQL'
SELECT id_categoria, nombre
  FROM categorias
 ORDER BY nombre
SQL;

$st = oci_parse($conn, $sql);
if (!$st) {
    api_json_response(['ok' => false, 'error' => api_oci_error_message($conn)], 500);
    exit;
}

if (!oci_execute($st)) {
    api_json_response(['ok' => false, 'error' => api_oci_error_message($st)], 500);
    exit;
}

$rows = [];
while ($row = oci_fetch_assoc($st)) {
    $row = array_change_key_case($row, CASE_LOWER);
    $rows[] = [
        'id'     => (int) $row['id_categoria'],
        'nombre' => $row['nombre'],
    ];
}
oci_free_statement($st);

api_json_response(['ok' => true, 'data' => $rows]);
