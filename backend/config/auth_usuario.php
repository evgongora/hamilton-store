<?php
/**
 * Usuarios Oracle: autenticación y datos de sesión (roles, empleado, cliente).
 */
declare(strict_types=1);

require_once __DIR__ . '/db.php';

/**
 * Verifica contraseña (bcrypt / Argon2 almacenados con password_hash de PHP).
 */
function hamilton_verify_password(string $plain, string $stored): bool
{
    $stored = trim($stored);
    if ($stored === '') {
        return false;
    }
    if (preg_match('/^\$2[ayb]\$\d{2}\$/', $stored) || strpos($stored, '$argon') === 0) {
        return password_verify($plain, $stored);
    }

    return false;
}

/**
 * Mapa nombre de rol en BD → rol de sesión (auth_guard / API).
 */
function hamilton_map_rol_db_a_sesion(string $nombreRol): ?string
{
    $n = strtoupper(trim($nombreRol));
    $map = [
        'ADMIN'       => 'admin',
        'CAJERO'      => 'cajero',
        'INVENTARIO'  => 'inventario',
        'CLIENTE'     => 'cliente',
        'SOPORTE'     => 'soporte',
    ];

    return $map[$n] ?? null;
}

/**
 * Autentica contra usuarios + roles + estado ACTIVO. Devuelve datos de sesión o null.
 *
 * @return array{id_usuario:int, role:string, empleado_id:?int, cliente_id:?int}|null
 */
function hamilton_authenticate(string $username, string $password): ?array
{
    $conn = hamilton_db();
    if ($conn === null) {
        error_log('hamilton-store auth: sin conexión Oracle (revisar .env y wallet)');
        return null;
    }

    $sch = hamilton_oracle_schema();
    if (!preg_match('/^[A-Za-z][A-Za-z0-9_#$]*$/', $sch)) {
        error_log('hamilton-store auth: ORACLE_SCHEMA inválido');
        return null;
    }

    $sql = <<<SQL
SELECT u.id_usuario,
       u.password_encriptado,
       u.empleados_id_empleado,
       u.clientes_id_cliente,
       r.nombre AS rol_nombre,
       es.nombre AS estado_nombre
  FROM {$sch}.usuarios u
 INNER JOIN {$sch}.roles r ON r.id_rol = u.roles_id_rol
  LEFT JOIN {$sch}.estados es ON es.id_estado = u.estados_id_estado
 WHERE LOWER(TRIM(u.username)) = LOWER(TRIM(:u))
SQL;

    $st = oci_parse($conn, $sql);
    if (!$st) {
        $e = oci_error($conn);
        error_log('hamilton-store auth: oci_parse — ' . ($e['message'] ?? ''));
        return null;
    }

    $userBind = $username;
    oci_bind_by_name($st, ':u', $userBind, 256);

    if (!@oci_execute($st)) {
        $e = oci_error($st);
        error_log('hamilton-store auth: oci_execute — ' . ($e['message'] ?? ''));
        oci_free_statement($st);
        return null;
    }

    $row = oci_fetch_assoc($st);
    oci_free_statement($st);
    if ($row === false) {
        error_log('hamilton-store auth: usuario no existe: ' . $username);
        return null;
    }

    $row = array_change_key_case($row, CASE_LOWER);
    $hash = trim((string) ($row['password_encriptado'] ?? ''));
    if (!hamilton_verify_password($password, $hash)) {
        error_log('hamilton-store auth: contraseña incorrecta para: ' . $username);
        return null;
    }

    $estadoNombre = strtoupper(trim((string) ($row['estado_nombre'] ?? '')));
    if ($estadoNombre !== 'ACTIVO') {
        error_log(
            'hamilton-store auth: cuenta no activa (estado=' . ($row['estado_nombre'] ?? 'NULL') . '): ' . $username
        );
        return null;
    }

    $rolNombre = (string) ($row['rol_nombre'] ?? '');
    $role = hamilton_map_rol_db_a_sesion($rolNombre);
    if ($role === null) {
        error_log('hamilton-store auth: rol no mapeado: ' . $rolNombre);
        return null;
    }

    $emp = $row['empleados_id_empleado'] ?? null;
    $cli = $row['clientes_id_cliente'] ?? null;

    return [
        'id_usuario'  => (int) $row['id_usuario'],
        'role'        => $role,
        'empleado_id' => ($emp !== null && $emp !== '') ? (int) $emp : null,
        'cliente_id'  => ($cli !== null && $cli !== '') ? (int) $cli : null,
    ];
}
