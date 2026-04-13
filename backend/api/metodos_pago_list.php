<?php
/**
 * GET — Métodos de pago (catálogo).
 */
declare(strict_types=1);

require_once __DIR__ . '/../config/api_helpers.php';

api_require_staff_session();
api_require_method('GET');
$conn = api_require_oracle();

$sql = 'SELECT id_metodo_pago, nombre FROM metodos_pago ORDER BY id_metodo_pago';
$st = oci_parse($conn, $sql);
if (!$st || !oci_execute($st)) {
    $e = oci_error($st ?: $conn);
    api_json_response(['ok' => false, 'error' => $e['message'] ?? 'Error'], 500);
    exit;
}

$rows = [];
while ($row = oci_fetch_assoc($st)) {
    $row = array_change_key_case($row, CASE_LOWER);
    $rows[] = [
        'id'     => (int) $row['id_metodo_pago'],
        'nombre' => $row['nombre'],
    ];
}
oci_free_statement($st);

api_json_response(['ok' => true, 'data' => $rows]);
