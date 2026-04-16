<?php
/**
 * GET — Lista proveedores (sesión de personal).
 */
declare(strict_types=1);

require_once __DIR__ . '/../config/api_helpers.php';

api_require_staff_session();

api_require_method('GET');
$conn = api_require_oracle();

$sql = <<<'SQL'
SELECT p.id_proveedor,
       p.nombre,
       p.cedula_juridica,
       p.pagina_web,
       p.estados_id_estado,
       e.nombre AS estado_nombre
  FROM proveedores p
  JOIN estados e ON e.id_estado = p.estados_id_estado
 ORDER BY p.id_proveedor
SQL;

$st = oci_parse($conn, $sql);
if (!$st) {
    $e = oci_error($conn);
    api_json_response(['ok' => false, 'error' => $e['message'] ?? 'Error al preparar consulta'], 500);
    exit;
}

if (!oci_execute($st)) {
    $e = oci_error($st);
    api_json_response(['ok' => false, 'error' => $e['message'] ?? 'Error al ejecutar consulta'], 500);
    exit;
}

$rows = [];
while ($row = oci_fetch_assoc($st)) {
    $row = array_change_key_case($row, CASE_LOWER);
    $rows[] = [
        'id'             => (int) $row['id_proveedor'],
        'nombre'         => $row['nombre'],
        'cedulaJuridica' => $row['cedula_juridica'],
        'paginaWeb'      => $row['pagina_web'],
        'idEstado'       => (int) $row['estados_id_estado'],
        'estado'         => $row['estado_nombre'],
    ];
}
oci_free_statement($st);

api_json_response(['ok' => true, 'data' => $rows]);
