<?php
/**
 * sidebar.php - Menú lateral del sistema
 * Requiere: $basePath, $currentPage (opcional, para marcar activo)
 */
$currentPage = $currentPage ?? '';
$pagesPath = $basePath . '/pages/sistema';

$menuItems = [
    ['key' => 'dashboard', 'label' => 'Dashboard', 'icon' => 'bi-speedometer2', 'url' => 'dashboard.php'],
    ['key' => 'productos', 'label' => 'Productos y Categorías', 'icon' => 'bi-box-seam', 'url' => 'productos.php'],
    ['key' => 'inventario', 'label' => 'Inventario', 'icon' => 'bi-archive', 'url' => 'inventario.php'],
    ['key' => 'clientes', 'label' => 'Clientes', 'icon' => 'bi-people', 'url' => 'clientes.php'],
    ['key' => 'ubicaciones', 'label' => 'Ubicaciones', 'icon' => 'bi-geo-alt', 'url' => 'ubicaciones.php'],
    ['key' => 'proveedores', 'label' => 'Proveedores', 'icon' => 'bi-truck', 'url' => 'proveedores.php'],
    ['key' => 'compras', 'label' => 'Compras', 'icon' => 'bi-cart-plus', 'url' => 'compras.php'],
    ['key' => 'ventas', 'label' => 'Ventas', 'icon' => 'bi-cart-check', 'url' => 'ventas.php'],
    ['key' => 'pagos', 'label' => 'Pagos', 'icon' => 'bi-credit-card', 'url' => 'pagos.php'],
    ['key' => 'empleados', 'label' => 'Empleados', 'icon' => 'bi-person-badge', 'url' => 'empleados.php'],
    ['key' => 'usuarios', 'label' => 'Usuarios', 'icon' => 'bi-person-gear', 'url' => 'usuarios.php'],
    ['key' => 'reportes', 'label' => 'Reportes', 'icon' => 'bi-graph-up', 'url' => 'reportes.php'],
];
?>
<aside class="sidebar bg-dark text-white" id="sidebar">
    <nav class="sidebar-nav py-3">
        <ul class="nav flex-column">
            <?php foreach ($menuItems as $item): ?>
            <li class="nav-item">
                <a class="nav-link sidebar-link <?php echo $currentPage === $item['key'] ? 'active bg-secondary' : 'text-white-50'; ?>" 
                   href="<?php echo htmlspecialchars($pagesPath . '/' . $item['url']); ?>">
                    <i class="bi <?php echo htmlspecialchars($item['icon']); ?> me-2"></i>
                    <?php echo htmlspecialchars($item['label']); ?>
                </a>
            </li>
            <?php endforeach; ?>
        </ul>
    </nav>
</aside>
