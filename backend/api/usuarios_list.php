<?php
/**
 * GET — Lista usuarios con rol, estado y nombre de empleado (si aplica).
 */
declare(strict_types=1);

require_once __DIR__ . '/../config/api_helpers.php';

api_require_usuarios_gestion_roles();
api_require_method('GET');
$conn = api_require_oracle();

$sql = <<<'SQL'
SELECT u.id_usuario,
       u.username,
       u.roles_id_rol,
       r.nombre AS rol_nombre,
       u.estados_id_estado,
       e2.nombre AS estado_nombre,
       u.empleados_id_empleado,
       u.clientes_id_cliente,
       em.nombre AS emp_nombre,
       em.apellido AS emp_apellido,
       c.nombre AS cli_nombre,
       c.apellido AS cli_apellido
  FROM usuarios u
 INNER JOIN roles r ON r.id_rol = u.roles_id_rol
 INNER JOIN estados e2 ON e2.id_estado = u.estados_id_estado
  LEFT JOIN empleados em ON em.id_empleado = u.empleados_id_empleado
  LEFT JOIN clientes c ON c.id_cliente = u.clientes_id_cliente
 ORDER BY u.id_usuario
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
    $empId = $row['empleados_id_empleado'] ?? null;
    $cliId = $row['clientes_id_cliente'] ?? null;
    $empIdInt = ($empId !== null && $empId !== '' && is_numeric($empId)) ? (int) $empId : null;
    if ($empIdInt !== null && $empIdInt <= 0) {
        $empIdInt = null;
    }
    $cliIdInt = ($cliId !== null && $cliId !== '' && is_numeric($cliId)) ? (int) $cliId : null;
    if ($cliIdInt !== null && $cliIdInt <= 0) {
        $cliIdInt = null;
    }
    $empNombre = trim(implode(' ', array_filter([
        $row['emp_nombre'] ?? '',
        $row['emp_apellido'] ?? '',
    ])));
    $cliNombre = trim(implode(' ', array_filter([
        $row['cli_nombre'] ?? '',
        $row['cli_apellido'] ?? '',
    ])));

    $esCuentaClienteTienda = $cliIdInt !== null;

    $rows[] = [
        'id'                   => (int) $row['id_usuario'],
        'username'             => $row['username'],
        'rolesIdRol'           => (int) $row['roles_id_rol'],
        'rolNombre'            => $row['rol_nombre'],
        'estadosIdEstado'      => (int) $row['estados_id_estado'],
        'estadoNombre'         => $row['estado_nombre'],
        'empleadosIdEmpleado'  => $empIdInt,
        'clientesIdCliente'    => $cliIdInt,
        'empleadoNombre'       => $empNombre !== '' ? $empNombre : null,
        'clienteNombre'        => $cliNombre !== '' ? $cliNombre : null,
        'esUsuarioEmpleado'    => !$esCuentaClienteTienda,
        'esCuentaCliente'      => $esCuentaClienteTienda,
    ];
}
oci_free_statement($st);

api_json_response(['ok' => true, 'data' => $rows]);
