<?php
/**
 * POST JSON — Registro público solo para clientes de tienda (cliente + usuario rol CLIENTE).
 * El personal se crea desde el sistema (admin). Contraseña: password_hash PHP (login unificado).
 *
 * { "nombre", "apellido", "email", "username", "password", "passwordConfirm" }
 */
declare(strict_types=1);

require_once __DIR__ . '/../config/api_helpers.php';

api_require_method('POST');
$conn = api_require_oracle();
$body = api_read_json_body();

$nombre = trim((string) ($body['nombre'] ?? ''));
$apellido = trim((string) ($body['apellido'] ?? ''));
$email = strtolower(trim((string) ($body['email'] ?? '')));
$username = trim((string) ($body['username'] ?? ''));
$password = (string) ($body['password'] ?? '');
$passwordConfirm = (string) ($body['passwordConfirm'] ?? '');

if ($nombre === '' || $apellido === '' || $email === '' || $username === '') {
    api_json_response(['ok' => false, 'error' => 'Complete nombre, apellido, email y usuario.'], 400);
    exit;
}
if ($password === '' || $password !== $passwordConfirm) {
    api_json_response(['ok' => false, 'error' => 'Las contraseñas no coinciden.'], 400);
    exit;
}
if (strlen($password) < 8) {
    api_json_response(['ok' => false, 'error' => 'La contraseña debe tener al menos 8 caracteres.'], 400);
    exit;
}
if (!preg_match('/^[A-Za-z0-9._-]+$/', $username)) {
    api_json_response(['ok' => false, 'error' => 'El usuario solo puede usar letras, números, punto, guion y guion bajo.'], 400);
    exit;
}
if (strlen($username) < 3 || strlen($username) > 50) {
    api_json_response(['ok' => false, 'error' => 'El usuario debe tener entre 3 y 50 caracteres.'], 400);
    exit;
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    api_json_response(['ok' => false, 'error' => 'Email no válido.'], 400);
    exit;
}
if (!hamilton_cliente_nombre_valido_oracle($nombre) || !hamilton_cliente_nombre_valido_oracle($apellido)) {
    api_json_response([
        'ok'    => false,
        'error' => 'Nombre y apellido solo pueden usar letras (A-Z, sin tilde) y espacios, como exige la base de datos. Ejemplos: Maria, Jose, Perez.',
    ], 400);
    exit;
}

$sqlDupUser = <<<'SQL'
SELECT COUNT(*) AS c
  FROM usuarios
 WHERE UPPER(TRIM(username)) = UPPER(TRIM(:u))
SQL;
$stDup = oci_parse($conn, $sqlDupUser);
if (!$stDup) {
    api_json_response(['ok' => false, 'error' => api_oci_error_message($conn)], 500);
    exit;
}
oci_bind_by_name($stDup, ':u', $username, 256);
if (!@oci_execute($stDup)) {
    api_json_response(['ok' => false, 'error' => api_oci_error_message($stDup)], 500);
    oci_free_statement($stDup);
    exit;
}
$rowDup = oci_fetch_assoc($stDup);
oci_free_statement($stDup);
if ($rowDup !== false) {
    $rowDup = array_change_key_case($rowDup, CASE_LOWER);
    if ((int) ($rowDup['c'] ?? 0) > 0) {
        api_json_response(['ok' => false, 'error' => 'Ese nombre de usuario ya está en uso.'], 400);
        exit;
    }
}

$sqlRol = <<<'SQL'
SELECT id_rol FROM roles WHERE UPPER(TRIM(nombre)) = 'CLIENTE'
SQL;
$stRol = oci_parse($conn, $sqlRol);
if (!$stRol || !@oci_execute($stRol)) {
    api_json_response(['ok' => false, 'error' => 'No se pudo resolver el rol de cliente.'], 500);
    exit;
}
$rowRol = oci_fetch_assoc($stRol);
oci_free_statement($stRol);
if ($rowRol === false) {
    api_json_response(['ok' => false, 'error' => 'Configuración de roles incompleta (CLIENTE).'], 500);
    exit;
}
$rowRol = array_change_key_case($rowRol, CASE_LOWER);
$idRol = (int) $rowRol['id_rol'];

$sqlEst = <<<'SQL'
SELECT id_estado FROM estados WHERE UPPER(TRIM(nombre)) = 'ACTIVO'
SQL;
$stEst = oci_parse($conn, $sqlEst);
if (!$stEst || !@oci_execute($stEst)) {
    api_json_response(['ok' => false, 'error' => 'No se pudo resolver el estado ACTIVO.'], 500);
    exit;
}
$rowEst = oci_fetch_assoc($stEst);
oci_free_statement($stEst);
if ($rowEst === false) {
    api_json_response(['ok' => false, 'error' => 'Configuración de estados incompleta (ACTIVO).'], 500);
    exit;
}
$rowEst = array_change_key_case($rowEst, CASE_LOWER);
$idEstado = (int) $rowEst['id_estado'];

$hash = password_hash($password, PASSWORD_DEFAULT);
if ($hash === false) {
    api_json_response(['ok' => false, 'error' => 'No se pudo procesar la contraseña.'], 500);
    exit;
}

$sqlInsCli = 'BEGIN M_HAMILTON_STORE.pkg_clientes.sp_insertar_cliente(
    :nom, :ape, :em, :id_est
); END;';
$stCli = oci_parse($conn, $sqlInsCli);
if (!$stCli) {
    api_json_response(['ok' => false, 'error' => api_oci_error_message($conn)], 500);
    exit;
}
oci_bind_by_name($stCli, ':nom', $nombre, 4000);
oci_bind_by_name($stCli, ':ape', $apellido, 4000);
oci_bind_by_name($stCli, ':em', $email, 4000);
oci_bind_by_name($stCli, ':id_est', $idEstado);

if (!@oci_execute($stCli, OCI_DEFAULT)) {
    api_json_response(['ok' => false, 'error' => api_register_cliente_error_usuario(api_oci_error_message($stCli))], 400);
    oci_free_statement($stCli);
    exit;
}
oci_free_statement($stCli);

$sqlId = <<<'SQL'
SELECT id_cliente FROM clientes WHERE UPPER(TRIM(email)) = UPPER(TRIM(:em))
SQL;
$stId = oci_parse($conn, $sqlId);
if (!$stId) {
    oci_rollback($conn);
    api_json_response(['ok' => false, 'error' => api_oci_error_message($conn)], 500);
    exit;
}
oci_bind_by_name($stId, ':em', $email, 4000);
if (!@oci_execute($stId, OCI_DEFAULT)) {
    oci_rollback($conn);
    api_json_response(['ok' => false, 'error' => api_oci_error_message($stId)], 500);
    oci_free_statement($stId);
    exit;
}
$rowId = oci_fetch_assoc($stId);
oci_free_statement($stId);
if ($rowId === false) {
    oci_rollback($conn);
    api_json_response(['ok' => false, 'error' => 'No se obtuvo el cliente recién creado.'], 500);
    exit;
}
$rowId = array_change_key_case($rowId, CASE_LOWER);
$idCliente = (int) $rowId['id_cliente'];

$empNull = null;

$sqlInsUsr = 'BEGIN M_HAMILTON_STORE.pkg_usuarios.sp_insertar_usuario(
    :u, :pw, :id_rol, :id_est, :id_emp, :id_cli
); END;';
$stUsr = oci_parse($conn, $sqlInsUsr);
if (!$stUsr) {
    oci_rollback($conn);
    api_json_response(['ok' => false, 'error' => api_oci_error_message($conn)], 500);
    exit;
}
oci_bind_by_name($stUsr, ':u', $username, 256);
oci_bind_by_name($stUsr, ':pw', $hash, 4000);
oci_bind_by_name($stUsr, ':id_rol', $idRol);
oci_bind_by_name($stUsr, ':id_est', $idEstado);
oci_bind_by_name($stUsr, ':id_emp', $empNull);
oci_bind_by_name($stUsr, ':id_cli', $idCliente);

if (!@oci_execute($stUsr, OCI_DEFAULT)) {
    oci_rollback($conn);
    api_json_response(['ok' => false, 'error' => api_register_cliente_error_usuario(api_oci_error_message($stUsr))], 400);
    oci_free_statement($stUsr);
    exit;
}
oci_free_statement($stUsr);

if (!oci_commit($conn)) {
    api_json_response(['ok' => false, 'error' => 'No se pudo confirmar el registro.'], 500);
    exit;
}

api_json_response(['ok' => true, 'message' => 'Cuenta creada. Ya puede iniciar sesión.']);
