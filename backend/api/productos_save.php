<?php
/**
 * POST JSON — Alta / edición / baja de productos vía M_HAMILTON_STORE.pkg_productos.
 * Los triggers (stock, agotado, etc.) se ejecutan solos en la BD al hacer el DML del paquete.
 * El front debe mostrar body.error si ok === false (incluye ORA-20xxx de triggers).
 *
 * Body ejemplo:
 * { "action": "insert", "nombre": "...", "precioCompra": 1, "precioVenta": 2, "cantidad": 0, "idCategoria": 1, "idEstado": 1 }
 * { "action": "update", "id": 1, ... }
 * { "action": "delete", "id": 1 }
 */
declare(strict_types=1);

require_once __DIR__ . '/../config/api_helpers.php';

api_require_staff_session();

api_require_method('POST');
$conn = api_require_oracle();
$body = api_read_json_body();
$action = isset($body['action']) ? strtolower(trim((string) $body['action'])) : '';

if ($action === 'insert') {
    $nombre = trim((string) ($body['nombre'] ?? ''));
    $precioCompra = isset($body['precioCompra']) ? (float) $body['precioCompra'] : null;
    $precioVenta = isset($body['precioVenta']) ? (float) $body['precioVenta'] : null;
    $cantidad = isset($body['cantidad']) ? (int) $body['cantidad'] : null;
    $idCat = isset($body['idCategoria']) ? (int) $body['idCategoria'] : 0;
    $idEst = isset($body['idEstado']) ? (int) $body['idEstado'] : 0;

    $sql = 'BEGIN M_HAMILTON_STORE.pkg_productos.sp_insertar_producto(
        :nombre, :precio_compra, :precio_venta, :cantidad, :id_cat, :id_est
    ); END;';
    $st = oci_parse($conn, $sql);
    if (!$st) {
        api_json_response(['ok' => false, 'error' => api_oci_error_message($conn)], 500);
        exit;
    }
    oci_bind_by_name($st, ':nombre', $nombre, 4000);
    oci_bind_by_name($st, ':precio_compra', $precioCompra);
    oci_bind_by_name($st, ':precio_venta', $precioVenta);
    oci_bind_by_name($st, ':cantidad', $cantidad);
    oci_bind_by_name($st, ':id_cat', $idCat);
    oci_bind_by_name($st, ':id_est', $idEst);

    if (!@oci_execute($st)) {
        api_json_response(['ok' => false, 'error' => api_oci_error_message($st)], 400);
        oci_free_statement($st);
        exit;
    }
    oci_free_statement($st);
    api_json_response(['ok' => true, 'message' => 'Producto creado']);
    exit;
}

if ($action === 'update') {
    $id = isset($body['id']) ? (int) $body['id'] : 0;
    $nombre = trim((string) ($body['nombre'] ?? ''));
    $precioCompra = isset($body['precioCompra']) ? (float) $body['precioCompra'] : null;
    $precioVenta = isset($body['precioVenta']) ? (float) $body['precioVenta'] : null;
    $cantidad = isset($body['cantidad']) ? (int) $body['cantidad'] : null;
    $idCat = isset($body['idCategoria']) ? (int) $body['idCategoria'] : 0;
    $idEst = isset($body['idEstado']) ? (int) $body['idEstado'] : 0;

    $sql = 'BEGIN M_HAMILTON_STORE.pkg_productos.sp_actualizar_producto(
        :id, :nombre, :precio_compra, :precio_venta, :cantidad, :id_cat, :id_est
    ); END;';
    $st = oci_parse($conn, $sql);
    if (!$st) {
        api_json_response(['ok' => false, 'error' => api_oci_error_message($conn)], 500);
        exit;
    }
    oci_bind_by_name($st, ':id', $id);
    oci_bind_by_name($st, ':nombre', $nombre, 4000);
    oci_bind_by_name($st, ':precio_compra', $precioCompra);
    oci_bind_by_name($st, ':precio_venta', $precioVenta);
    oci_bind_by_name($st, ':cantidad', $cantidad);
    oci_bind_by_name($st, ':id_cat', $idCat);
    oci_bind_by_name($st, ':id_est', $idEst);

    if (!@oci_execute($st)) {
        api_json_response(['ok' => false, 'error' => api_oci_error_message($st)], 400);
        oci_free_statement($st);
        exit;
    }
    oci_free_statement($st);
    api_json_response(['ok' => true, 'message' => 'Producto actualizado']);
    exit;
}

if ($action === 'delete') {
    $id = isset($body['id']) ? (int) $body['id'] : 0;
    $sql = 'BEGIN M_HAMILTON_STORE.pkg_productos.sp_eliminar_producto(:id); END;';
    $st = oci_parse($conn, $sql);
    if (!$st) {
        api_json_response(['ok' => false, 'error' => api_oci_error_message($conn)], 500);
        exit;
    }
    oci_bind_by_name($st, ':id', $id);

    if (!@oci_execute($st)) {
        api_json_response(['ok' => false, 'error' => api_oci_error_message($st)], 400);
        oci_free_statement($st);
        exit;
    }
    oci_free_statement($st);
    api_json_response(['ok' => true, 'message' => 'Producto eliminado']);
    exit;
}

api_json_response(['ok' => false, 'error' => 'action inválido: use insert, update o delete'], 400);
