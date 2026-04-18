<?php
/**
 * POST JSON — CRUD proveedores (M_HAMILTON_STORE.pkg_proveedores).
 *
 * { "action": "insert", "nombre", "cedulaJuridica", "paginaWeb", "idEstado" }
 * { "action": "update", "id", ... }
 * { "action": "delete", "id" }
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
    $cedula = trim((string) ($body['cedulaJuridica'] ?? ''));
    $web = trim((string) ($body['paginaWeb'] ?? ''));
    $idEst = isset($body['idEstado']) ? (int) $body['idEstado'] : 0;

    $eNom = hamilton_texto_libre_validar($nombre, 200, false);
    if ($eNom !== null) {
        api_json_response(['ok' => false, 'error' => 'Nombre: ' . $eNom], 400);
        exit;
    }
    $eCed = hamilton_texto_libre_validar($cedula, 30, true);
    if ($eCed !== null) {
        api_json_response(['ok' => false, 'error' => 'Cédula jurídica: ' . $eCed], 400);
        exit;
    }
    $eWeb = hamilton_texto_libre_validar($web, 500, true);
    if ($eWeb !== null) {
        api_json_response(['ok' => false, 'error' => 'Página web: ' . $eWeb], 400);
        exit;
    }
    if ($idEst <= 0 || !hamilton_estado_id_existe($conn, $idEst)) {
        api_json_response(['ok' => false, 'error' => 'Estado inválido.'], 400);
        exit;
    }

    $sql = 'BEGIN M_HAMILTON_STORE.pkg_proveedores.sp_insertar_proveedor(
        :nombre, :cedula, :web, :id_est
    ); END;';
    $st = oci_parse($conn, $sql);
    if (!$st) {
        api_json_response(['ok' => false, 'error' => api_oci_error_message($conn)], 500);
        exit;
    }
    oci_bind_by_name($st, ':nombre', $nombre, 4000);
    oci_bind_by_name($st, ':cedula', $cedula, 4000);
    oci_bind_by_name($st, ':web', $web, 4000);
    oci_bind_by_name($st, ':id_est', $idEst);

    if (!@oci_execute($st)) {
        api_json_response(['ok' => false, 'error' => api_oci_error_message($st)], 400);
        oci_free_statement($st);
        exit;
    }
    oci_free_statement($st);
    api_json_response(['ok' => true, 'message' => 'Proveedor creado']);
    exit;
}

if ($action === 'update') {
    $id = isset($body['id']) ? (int) $body['id'] : 0;
    $nombre = trim((string) ($body['nombre'] ?? ''));
    $cedula = trim((string) ($body['cedulaJuridica'] ?? ''));
    $web = trim((string) ($body['paginaWeb'] ?? ''));
    $idEst = isset($body['idEstado']) ? (int) $body['idEstado'] : 0;

    if ($id <= 0) {
        api_json_response(['ok' => false, 'error' => 'id de proveedor inválido.'], 400);
        exit;
    }
    $eNomU = hamilton_texto_libre_validar($nombre, 200, false);
    if ($eNomU !== null) {
        api_json_response(['ok' => false, 'error' => 'Nombre: ' . $eNomU], 400);
        exit;
    }
    $eCedU = hamilton_texto_libre_validar($cedula, 30, true);
    if ($eCedU !== null) {
        api_json_response(['ok' => false, 'error' => 'Cédula jurídica: ' . $eCedU], 400);
        exit;
    }
    $eWebU = hamilton_texto_libre_validar($web, 500, true);
    if ($eWebU !== null) {
        api_json_response(['ok' => false, 'error' => 'Página web: ' . $eWebU], 400);
        exit;
    }
    if ($idEst <= 0 || !hamilton_estado_id_existe($conn, $idEst)) {
        api_json_response(['ok' => false, 'error' => 'Estado inválido.'], 400);
        exit;
    }

    $sql = 'BEGIN M_HAMILTON_STORE.pkg_proveedores.sp_actualizar_proveedor(
        :id, :nombre, :cedula, :web, :id_est
    ); END;';
    $st = oci_parse($conn, $sql);
    if (!$st) {
        api_json_response(['ok' => false, 'error' => api_oci_error_message($conn)], 500);
        exit;
    }
    oci_bind_by_name($st, ':id', $id);
    oci_bind_by_name($st, ':nombre', $nombre, 4000);
    oci_bind_by_name($st, ':cedula', $cedula, 4000);
    oci_bind_by_name($st, ':web', $web, 4000);
    oci_bind_by_name($st, ':id_est', $idEst);

    if (!@oci_execute($st)) {
        api_json_response(['ok' => false, 'error' => api_oci_error_message($st)], 400);
        oci_free_statement($st);
        exit;
    }
    oci_free_statement($st);
    api_json_response(['ok' => true, 'message' => 'Proveedor actualizado']);
    exit;
}

if ($action === 'delete') {
    $id = isset($body['id']) ? (int) $body['id'] : 0;
    if ($id <= 0) {
        api_json_response(['ok' => false, 'error' => 'id de proveedor inválido.'], 400);
        exit;
    }
    $sql = 'BEGIN M_HAMILTON_STORE.pkg_proveedores.sp_eliminar_proveedor(:id); END;';
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
    api_json_response(['ok' => true, 'message' => 'Proveedor eliminado']);
    exit;
}

api_json_response(['ok' => false, 'error' => 'action inválido: use insert, update o delete'], 400);
