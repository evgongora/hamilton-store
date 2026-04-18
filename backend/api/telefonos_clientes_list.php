<?php
/**
 * GET — Lista teléfonos de clientes vía pkg_telefonos_clientes.sp_listar_telefonos_clientes.
 */
declare(strict_types=1);

require_once __DIR__ . '/../config/api_helpers.php';

api_require_staff_session();
api_require_method('GET');
$conn = api_require_oracle();

$sql = 'BEGIN M_HAMILTON_STORE.pkg_telefonos_clientes.sp_listar_telefonos_clientes(:cur); END;';
$out = api_oci_ref_cursor_fetch_all($conn, $sql, ':cur');
if ($out['error'] !== null) {
    api_json_response(['ok' => false, 'error' => $out['error']], 500);
    exit;
}

$rows = [];
foreach ($out['rows'] as $row) {
    $rows[] = [
        'id'           => (int) ($row['id_telefono'] ?? 0),
        'numero'       => (string) ($row['numero'] ?? ''),
        'idCliente'    => (int) ($row['clientes_id_cliente'] ?? 0),
        'nombreCliente'   => (string) ($row['nombre'] ?? ''),
        'apellidoCliente' => (string) ($row['apellido'] ?? ''),
    ];
}

api_json_response(['ok' => true, 'data' => $rows]);
