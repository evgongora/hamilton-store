<?php
/**
 * GET — Lista empleados con estado (sesión de personal).
 */
declare(strict_types=1);

require_once __DIR__ . '/../config/api_helpers.php';

api_require_staff_session();
api_require_method('GET');
$conn = api_require_oracle();

$sql = <<<'SQL'
SELECT em.id_empleado,
       em.nombre,
       em.apellido,
       em.puesto,
       em.email,
       em.fecha_ingreso,
       em.estados_id_estado,
       es.nombre AS estado_nombre
  FROM empleados em
  JOIN estados es ON es.id_estado = em.estados_id_estado
 ORDER BY em.id_empleado
SQL;

$st = oci_parse($conn, $sql);
if (!$st || !oci_execute($st)) {
    $e = oci_error($st ?: $conn);
    api_json_response(['ok' => false, 'error' => $e['message'] ?? 'Error'], 500);
    exit;
}

$rows = [];
while ($row = oci_fetch_assoc($st)) {
    $row = array_change_key_case($row, CASE_LOWER);
    $fvRaw = $row['fecha_ingreso'] ?? null;
    $fechaIso = null;
    if ($fvRaw !== null && $fvRaw !== '') {
        $ts = strtotime((string) $fvRaw);
        if ($ts !== false) {
            $fechaIso = gmdate('c', $ts);
        }
    }
    $rows[] = [
        'id'           => (int) $row['id_empleado'],
        'nombre'       => $row['nombre'],
        'apellido'     => $row['apellido'],
        'puesto'       => $row['puesto'],
        'email'        => $row['email'],
        'fechaIngreso' => $fvRaw,
        'fechaIngresoIso' => $fechaIso,
        'idEstado'     => (int) $row['estados_id_estado'],
        'estado'       => $row['estado_nombre'],
    ];
}
oci_free_statement($st);

api_json_response(['ok' => true, 'data' => $rows]);
