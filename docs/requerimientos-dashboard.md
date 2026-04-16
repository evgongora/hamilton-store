# Mapeo de requerimientos — Dashboard y tienda

## Flujo de acceso (estado actual)

| URL (ejemplo) | Sin sesión | Cliente | Staff (admin / soporte / cajero / inventario) |
|---------------|------------|---------|-----------------------------------------------|
| `http://localhost/hamilton-store/` | Tienda (catálogo) | Tienda + comprar (si rol `cliente`) | Con sesión staff: **dashboard** (`dashboard.php`). Tras **login**, destino según rol (p. ej. cajero → clientes) vía `getRoleHomePath()` |
| `http://localhost/hamilton-store/public/` | Redirige a `/hamilton-store/` | Igual | Igual |

- **Cliente**: tras login permanece en la tienda; ve **Carrito** y **Checkout** en el navbar.
- **Staff**: entra al sistema; el menú lateral muestra solo lo permitido para su rol (`hamilton_staff_menu_keys()` en `auth_guard.php`). Desde la tienda puede usar **Dashboard** cuando el rol lo permite.

---

## Módulos del dashboard vs requerimientos

| Requerimiento | Módulo | Archivo principal | Estado (persistencia) |
|---------------|--------|-------------------|------------------------|
| Usuarios y roles | Usuarios | `public/pages/sistema/usuarios.php` | **Oracle** (`usuarios_list.php`, `usuarios_save.php`, `roles_list.php`) |
| Empleados | Empleados | `empleados.php` | **Oracle** (`empleados_list.php`, `empleados_save.php`) |
| Clientes | Clientes | `clientes.php` | **Oracle** lectura (`clientes_list.php`); UI listado + búsqueda |
| Direcciones y ubicación | Ubicaciones | `ubicaciones.php` | **localStorage** + seed JSON (no API Oracle en el front actual) |
| Productos y categorías | Productos | `productos.php` | **Oracle** (`productos_list.php`, `productos_save.php`, `categorias_*`, `estados_list.php`) |
| Proveedores y contactos | Proveedores | `proveedores.php` | **Oracle** (`proveedores_*`, `contactos_proveedor_*`) |
| Compras | Compras | `compras.php` | **Oracle** (`compras_create.php`, listas de productos/proveedores) |
| Ventas | Ventas | `ventas.php` | **Oracle** (`ventas_create.php`, catálogo y clientes vía API) |
| Pagos | Pagos | `pagos.php` | **Oracle** (`pagos_create.php`, `ventas_list.php`, `metodos_pago_list.php`) |
| Inventario | Inventario | `inventario.php` | **Oracle** (productos; ajuste de stock según permisos en página) |
| Reportes | Reportes | `reportes.php` | **Oracle** (`ventas_list.php`) con **respaldo** `localStorage` si la API falla |
| Panel resumen | Dashboard | `dashboard.php` | **Oracle** (ventas, clientes, productos) con **respaldo** local |

Documentación por pantalla y endpoints: [modulos-sistema.md](./modulos-sistema.md).

---

## Permisos por rol (implementados)

La fuente de verdad es `hamilton_staff_menu_keys()` en `backend/config/auth_guard.php` y el `requireRole([...])` de cada página.

| Rol | Acceso típico al menú |
|-----|------------------------|
| **admin** | Dashboard, productos, inventario, clientes, ubicaciones, proveedores, compras, ventas, pagos, empleados, usuarios, reportes |
| **soporte** | Igual que admin excepto **usuarios** |
| **cajero** | Clientes, inventario, ventas, pagos |
| **inventario** | Productos, inventario, proveedores, compras |

Las páginas del sistema deben coincidir con estas listas para evitar enlaces rotos o accesos no autorizados.
