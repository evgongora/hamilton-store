<?php
/**
 * Punto de entrada único del proyecto.
 * - Sin sesión o cliente → Landing tienda (Homepage): catálogo; login desde botones en la página.
 * - Personal (admin/cajero/inventario/soporte) → Dashboard del sistema.
 */
session_start();

$role = $_SESSION['role'] ?? '';
if (!empty($_SESSION['user']) && in_array($role, ['admin', 'cajero', 'inventario', 'soporte'], true)) {
    header("Location: public/pages/sistema/dashboard.php");
    exit;
}

header("Location: public/pages/tienda/Homepage.php");
exit;
