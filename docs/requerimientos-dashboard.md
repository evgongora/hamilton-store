# Mapeo de Requerimientos - Dashboard M. Hamilton Store

## Flujo unificado de acceso

| URL | Sin sesión | Cliente | Staff (admin/cajero/inventario) |
|-----|------------|---------|--------------------------------|
| `http://localhost/hamilton-store/` | Tienda (catálogo) | Tienda + comprar | Dashboard |
| `http://localhost/hamilton-store/public/` |  Todos Redirige a `/` | Redirige a `/` | Redirige a `/` |

- **Cliente**: Inicia sesión y permanece en la tienda para comprar.
- **Staff**: Inicia sesión y va al dashboard con módulos de gestión. En la tienda verá botón "Dashboard" para cambiar de vista.

---

## Módulos del dashboard (según requerimientos)

| Requerimiento | Módulo | Archivo | Estado |
|---------------|--------|---------|--------|
| Usuarios y Roles | Usuarios | `usuarios.php` | Placeholder |
| Empleados | Empleados | `empleados.php` | Placeholder |
| Clientes | Clientes | `clientes.php` | Placeholder (mocks en `clientes.json`) |
| Direcciones y Ubicación Geográfica | Ubicaciones | `ubicaciones.php` | Placeholder (provincias, cantones, distritos) |
| Productos y Categorías | Productos | `productos.php` | Placeholder (mocks en `productos.json`) |
| Proveedores y Contactos | Proveedores | `proveedores.php` | Placeholder |
| Compras | Compras | `compras.php` | Placeholder |
| Ventas | Ventas | `ventas.php` | **Implementado** (Punto de venta mock) |
| Pagos y Métodos de Pago | Pagos | `pagos.php` | **Implementado** (registrar pagos mock) |
| Control de Inventario | Inventario | `inventario.php` | Placeholder |
| Reportes y Consultas | Reportes | `reportes.php` | Placeholder |

Ver documentación detallada en `docs/modulos-sistema.md`.

---

## Permisos por rol (a implementar)

| Rol | Módulos típicos |
|-----|-----------------|
| Admin | Todos |
| Cajero | Ventas, Pagos, Clientes, Productos (consulta), Inventario (consulta) |
| Inventario | Productos, Inventario, Compras, Proveedores, Ubicaciones |

*(Filtrar menú lateral según rol con `requireRole()` en cada página.)*
