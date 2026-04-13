<?php
/**
 * GET — Lista productos con categoría y estado (catálogo / sistema).
 */
declare(strict_types=1);

require_once __DIR__ . '/../config/api_helpers.php';

api_require_method('GET');
$conn = api_require_oracle();

$sql = <<<'SQL'
SELECT p.id_producto,
       p.nombre,
       p.precio_compra,
       p.precio_venta,
       p.cantidad,
       c.id_categoria,
       c.nombre AS nombre_categoria,
       e.id_estado,
       e.nombre AS nombre_estado
  FROM productos p
  JOIN categorias c ON c.id_categoria = p.categorias_id_categoria
  JOIN estados e ON e.id_estado = p.estados_id_estado
 ORDER BY p.id_producto
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
        'id'            => (int) $row['id_producto'],
        'nombre'        => $row['nombre'],
        'precioCompra'  => $row['precio_compra'] !== null && $row['precio_compra'] !== '' ? (float) $row['precio_compra'] : null,
        'precioVenta'   => $row['precio_venta'] !== null && $row['precio_venta'] !== '' ? (float) $row['precio_venta'] : null,
        'cantidad'      => (int) $row['cantidad'],
        'idCategoria'   => (int) $row['id_categoria'],
        'categoria'     => $row['nombre_categoria'],
        'idEstado'      => (int) $row['id_estado'],
        'estado'        => $row['nombre_estado'],
    ];
}
oci_free_statement($st);

api_json_response(['ok' => true, 'data' => $rows]);
