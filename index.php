<?php
/**
 * Punto de entrada único del proyecto.
 * - Sin sesión → Tienda (catálogo público)
 * - Con sesión staff (admin/cajero/inventario) → Dashboard
 * - Con sesión cliente → Tienda (puede comprar)
 */
session_start();

$role = $_SESSION['role'] ?? '';
if (!empty($_SESSION['user']) && in_array($role, ['admin', 'cajero', 'inventario'], true)) {
    header("Location: public/pages/sistema/dashboard.php");
    exit;
}

header("Location: public/pages/tienda/Homepage.php");
exit;
