<?php
/**
 * GET — Métodos de pago vía M_HAMILTON_STORE.pkg_metodos_pago.sp_listar_metodos_pago (REF CURSOR).
 */
declare(strict_types=1);

require_once __DIR__ . '/../config/api_helpers.php';

api_require_staff_or_cliente_tienda();
api_require_method('GET');
$conn = api_require_oracle();

$sql = 'BEGIN M_HAMILTON_STORE.pkg_metodos_pago.sp_listar_metodos_pago(:cur); END;';
$out = api_oci_ref_cursor_fetch_all($conn, $sql, ':cur');
if ($out['error'] !== null) {
    api_json_response(['ok' => false, 'error' => $out['error']], 500);
    exit;
}

$rows = [];
foreach ($out['rows'] as $row) {
    $rows[] = [
        'id'     => (int) ($row['id_metodo_pago'] ?? 0),
        'nombre' => (string) ($row['nombre'] ?? ''),
    ];
}

api_json_response(['ok' => true, 'data' => $rows]);
