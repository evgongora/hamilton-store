<?php
/**
 * Registro tienda — redirige al registro real de clientes (Oracle).
 */
$basePath = dirname(dirname(dirname($_SERVER['SCRIPT_NAME'])));
if ($basePath === '/' || $basePath === '\\') {
    $basePath = '';
}
header('Location: ' . $basePath . '/pages/auth/registro_cliente.php', true, 302);
exit;
