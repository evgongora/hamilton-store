<?php
/** Oracle OCI8 — .env en la raíz; wallet en backend/config/oracle-wallet/ */

declare(strict_types=1);

/** @var resource|null */
$dbConn = null;

function hamilton_oracle_tns_cache_parent(): ?string
{
    $root = dirname(__DIR__, 2);
    foreach ([
        $root . DIRECTORY_SEPARATOR . 'backend' . DIRECTORY_SEPARATOR . 'logs' . DIRECTORY_SEPARATOR . 'tns-cache',
        '/tmp/hamilton-oracle-tns',
        rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'hamilton-tns-fallback',
    ] as $base) {
        if ((!is_dir($base) && !@mkdir($base, 0775, true)) && !is_dir($base)) {
            continue;
        }
        if (is_writable($base)) {
            return $base;
        }
    }
    error_log('hamilton-store: sin directorio escribible para caché TNS (backend/logs o /tmp).');

    return null;
}

/** TNS_ADMIN temporal: tnsnames copiado + sqlnet con WALLET_LOCATION absoluto. */
function hamilton_oracle_tns_admin_dir(string $walletPath, bool $sslStrict): ?string
{
    $real = realpath($walletPath);
    if ($real === false) {
        return null;
    }
    $tnsSrc = $real . DIRECTORY_SEPARATOR . 'tnsnames.ora';
    if (!is_readable($tnsSrc)) {
        return null;
    }

    $parent = hamilton_oracle_tns_cache_parent();
    if ($parent === null) {
        return null;
    }

    $dir = $parent . DIRECTORY_SEPARATOR . 'hamilton-tns-' . md5($real);
    if ((!is_dir($dir) && !@mkdir($dir, 0775, true)) && !is_dir($dir)) {
        return null;
    }

    $tnsDst = $dir . DIRECTORY_SEPARATOR . 'tnsnames.ora';
    if (!@copy($tnsSrc, $tnsDst)) {
        error_log('hamilton-store: no se pudo copiar tnsnames.ora al TNS temporal.');

        return null;
    }

    $w = str_replace('\\', '/', $real);
    $ssl = $sslStrict ? 'yes' : 'no';
    $sqlnet = 'WALLET_LOCATION = (SOURCE = (METHOD = file) (METHOD_DATA = (DIRECTORY="' . $w . '")))' . "\n"
        . 'SSL_SERVER_DN_MATCH=' . $ssl . "\n";

    if (@file_put_contents($dir . DIRECTORY_SEPARATOR . 'sqlnet.ora', $sqlnet) === false) {
        return null;
    }

    return $dir;
}

function hamilton_oracle_descriptor(string $tnsnamesPath, string $alias): ?string
{
    $raw = @file_get_contents($tnsnamesPath);
    if ($raw === false || trim($alias) === '') {
        return null;
    }
    if (!preg_match('/^\s*' . preg_quote($alias, '/') . '\s*=\s*\(/mi', $raw, $m, PREG_OFFSET_CAPTURE)) {
        return null;
    }

    $start = $m[0][1] + strlen($m[0][0]) - 1;
    if (!isset($raw[$start]) || $raw[$start] !== '(') {
        return null;
    }

    $depth = 0;
    $len = strlen($raw);
    for ($i = $start; $i < $len; $i++) {
        if ($raw[$i] === '(') {
            $depth++;
        } elseif ($raw[$i] === ')') {
            $depth--;
            if ($depth === 0) {
                return preg_replace('/\s+/', ' ', trim(substr($raw, $start, $i - $start + 1)));
            }
        }
    }

    return null;
}

function hamilton_oracle_try_connect(string $walletPath, string $alias, string $user, string $pass, bool $sslStrict)
{
    $tnsAdmin = hamilton_oracle_tns_admin_dir($walletPath, $sslStrict);
    if ($tnsAdmin === null) {
        return false;
    }

    putenv('TNS_ADMIN=' . $tnsAdmin);
    $_ENV['TNS_ADMIN'] = $tnsAdmin;

    $tnsFile = $tnsAdmin . DIRECTORY_SEPARATOR . 'tnsnames.ora';
    $desc = hamilton_oracle_descriptor($tnsFile, $alias);
    if (!$sslStrict && $desc !== null) {
        $desc = (string) preg_replace('/ssl_server_dn_match=yes/i', 'ssl_server_dn_match=no', $desc);
    }

    $connectString = $desc ?? $alias;

    $conn = @oci_connect($user, $pass, $connectString, 'AL32UTF8');
    if ($conn === false && $desc !== null) {
        $conn = @oci_connect($user, $pass, $alias, 'AL32UTF8');
    }
    if ($conn === false) {
        $conn = @oci_connect($user, $pass, $connectString);
    }
    if ($conn === false && $desc !== null) {
        $conn = @oci_connect($user, $pass, $alias);
    }

    return $conn;
}

function hamilton_load_dotenv(): void
{
    static $loaded = false;
    if ($loaded) {
        return;
    }
    $loaded = true;

    $path = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . '.env';
    if (!is_readable($path)) {
        return;
    }

    $content = @file($path, FILE_IGNORE_NEW_LINES);
    if ($content === false) {
        return;
    }

    foreach ($content as $line) {
        $line = trim($line);
        if ($line === '' || (isset($line[0]) && $line[0] === '#')) {
            continue;
        }
        $eq = strpos($line, '=');
        if ($eq === false) {
            continue;
        }
        $key = trim(substr($line, 0, $eq));
        $val = trim(substr($line, $eq + 1));
        if ($key === '') {
            continue;
        }
        if (strlen($val) >= 2 && (($val[0] === '"' && substr($val, -1) === '"') || ($val[0] === "'" && substr($val, -1) === "'"))) {
            $val = substr($val, 1, -1);
        }
        if (getenv($key) === false) {
            putenv($key . '=' . $val);
            $_ENV[$key] = $val;
        }
    }
}

function hamilton_db()
{
    global $dbConn;

    if ($dbConn !== null) {
        return $dbConn;
    }

    hamilton_load_dotenv();

    if (!function_exists('oci_connect')) {
        error_log('hamilton-store: OCI8 no cargada.');

        return null;
    }

    $user = (string) (getenv('ORACLE_USER') ?: '');
    $pass = (string) (getenv('ORACLE_PASSWORD') ?: '');
    $alias = (string) (getenv('ORACLE_CONNECTION') ?: '');
    $wallet = getenv('ORACLE_WALLET_TNS_ADMIN');
    $wallet = (is_string($wallet) && $wallet !== '')
        ? rtrim($wallet, DIRECTORY_SEPARATOR)
        : (__DIR__ . DIRECTORY_SEPARATOR . 'oracle-wallet');

    if ($user === '' || $alias === '') {
        error_log('hamilton-store: faltan ORACLE_USER u ORACLE_CONNECTION.');

        return null;
    }
    if ($pass === '') {
        error_log('hamilton-store: ORACLE_PASSWORD vacío.');

        return null;
    }
    if (!is_dir($wallet)) {
        error_log('hamilton-store: carpeta wallet inexistente: ' . $wallet);

        return null;
    }

    $strict = true;
    $sslEnv = getenv('ORACLE_SSL_SERVER_DN_MATCH');
    if ($sslEnv !== false && trim((string) $sslEnv) !== '') {
        $strict = !in_array(strtolower(trim((string) $sslEnv)), ['no', '0', 'false', 'off'], true);
    }

    $conn = hamilton_oracle_try_connect($wallet, $alias, $user, $pass, $strict);
    if ($conn === false && $strict) {
        $conn = hamilton_oracle_try_connect($wallet, $alias, $user, $pass, false);
    }

    if ($conn === false) {
        $e = oci_error();
        $msg = is_array($e) ? trim((string) ($e['message'] ?? '')) : '';
        if ($msg === '' && is_array($e)) {
            $msg = json_encode($e, JSON_UNESCAPED_UNICODE);
        }
        if ($msg === '' && ($last = error_get_last())) {
            $msg = (string) ($last['message'] ?? '');
        }
        error_log('hamilton-store: oci_connect — ' . ($msg !== '' ? $msg : 'sin mensaje') . ' | ' . $alias);

        return null;
    }

    $schema = (string) (getenv('ORACLE_SCHEMA') ?: 'M_HAMILTON_STORE');
    if ($schema !== '' && preg_match('/^[A-Za-z][A-Za-z0-9_#$]*$/', $schema)) {
        $st = @oci_parse($conn, 'ALTER SESSION SET CURRENT_SCHEMA = ' . $schema);
        if ($st && @oci_execute($st)) {
            oci_free_statement($st);
        } else {
            $err = oci_error($st ?: $conn);
            error_log('hamilton-store: CURRENT_SCHEMA — ' . ($err['message'] ?? '?'));
            if ($st) {
                oci_free_statement($st);
            }
        }
    }

    $dbConn = $conn;

    return $dbConn;
}

function hamilton_oracle_schema(): string
{
    $s = getenv('ORACLE_SCHEMA');

    return (is_string($s) && $s !== '') ? $s : 'M_HAMILTON_STORE';
}
