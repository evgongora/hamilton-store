<?php
/**
 * GET — Contactos de un proveedor (?proveedorId=).
 */
declare(strict_types=1);

require_once __DIR__ . '/../config/api_helpers.php';

api_require_staff_session();
api_require_method('GET');
$conn = api_require_oracle();

$proveedorId = isset($_GET['proveedorId']) ? (int) $_GET['proveedorId'] : 0;
if ($proveedorId <= 0) {
    api_json_response(['ok' => false, 'error' => 'proveedorId requerido'], 400);
    exit;
}

$sql = <<<'SQL'
SELECT id_contacto,
       nombre,
       apellido,
       email,
       telefono,
       proveedores_id_proveedor
  FROM contactos_proveedores
 WHERE proveedores_id_proveedor = :pid
 ORDER BY id_contacto
SQL;

$st = oci_parse($conn, $sql);
if (!$st) {
    api_json_response(['ok' => false, 'error' => api_oci_error_message($conn)], 500);
    exit;
}
oci_bind_by_name($st, ':pid', $proveedorId);

if (!oci_execute($st)) {
    api_json_response(['ok' => false, 'error' => api_oci_error_message($st)], 500);
    exit;
}

$rows = [];
while ($row = oci_fetch_assoc($st)) {
    $row = array_change_key_case($row, CASE_LOWER);
    $rows[] = [
        'id'          => (int) $row['id_contacto'],
        'nombre'      => $row['nombre'],
        'apellido'    => $row['apellido'],
        'email'       => $row['email'],
        'telefono'    => $row['telefono'],
        'proveedorId' => (int) $row['proveedores_id_proveedor'],
    ];
}
oci_free_statement($st);

api_json_response(['ok' => true, 'data' => $rows]);
