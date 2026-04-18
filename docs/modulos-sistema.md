# Módulos del sistema y tienda — M. Hamilton Store

Documentación alineada con el código actual: **API Oracle** en `backend/api/`, cliente en `public/js/modules/` y `public/js/services/api.js`.

---

## API REST (`backend/api/`)

Convención habitual: respuestas JSON con `ok`, `data` o `error`; sesión de personal validada con `api_require_staff_session()` (u otra guarda según el endpoint).

| Endpoint | Método | Uso |
|----------|--------|-----|
| `auth_login.php` | POST | Login (usuario interno o cliente tienda) |
| `auth_logout.php` | GET/POST | Cerrar sesión |
| `auth_register_cliente.php` | POST | Registro público cliente + usuario rol cliente |
| `productos_list.php` | GET | Catálogo / POS / inventario |
| `productos_save.php` | POST | Alta/edición producto, stock |
| `categorias_list.php`, `categorias_save.php` | GET / POST | Categorías |
| `estados_list.php` | GET | Estados (productos, etc.) |
| `clientes_list.php` | GET | Listado clientes (staff) |
| `ventas_list.php` | GET | Ventas con totales y pagos agregados |
| `ventas_create.php` | POST | Punto de venta (líneas) |
| `pagos_create.php` | POST | Registrar pago sobre venta |
| `metodos_pago_list.php`, `metodos_pago_save.php` | GET / POST | Métodos de pago (`pkg_metodos_pago`) |
| `provincias_list.php`, `provincias_save.php` | GET / POST | Provincias |
| `cantones_list.php`, `cantones_save.php` | GET / POST | Cantones |
| `distritos_list.php`, `distritos_save.php` | GET / POST | Distritos |
| `direcciones_list.php`, `direcciones_save.php` | GET / POST | Direcciones cliente/proveedor |
| `telefonos_clientes_list.php`, `telefonos_clientes_save.php` | GET / POST | Teléfonos de clientes |
| `telefonos_cont_proveedor_list.php`, `telefonos_cont_proveedor_save.php` | GET / POST | Teléfonos de contactos de proveedor |
| `tipo_gestion_list.php`, `tipo_gestion_save.php` | GET / POST | Tipos de gestión de stock |
| `gestion_stock_list.php`, `gestion_stock_save.php` | GET / POST | Movimientos de gestión de stock |
| `empleados_list.php`, `empleados_save.php` | GET / POST | Empleados |
| `usuarios_list.php`, `usuarios_save.php` | GET / POST | Usuarios internos |
| `roles_list.php` | GET | Roles |
| `proveedores_list.php`, `proveedores_save.php` | GET / POST | Proveedores |
| `contactos_proveedor_list.php`, `contactos_proveedor_save.php` | GET / POST | Contactos |
| `compras_create.php` | POST | Compra a proveedor |
| `facturas_save.php` | — | Presente en backend; sin consumo desde el front actual |

Rutas base configuradas en `backend/config/paths.php` (`api` → `/hamilton-store/backend/api` en desarrollo típico).

---

## Dashboard

**Función**: métricas de ventas (suma), pagos acumulados, conteo de clientes y productos, tabla de últimas ventas.

**Datos**: solo **API** (`ventas_list.php`, `clientes_list.php`, `productos_list.php`). Si falla la petición, la UI muestra aviso y métricas vacías o en cero.

| Archivo | Rol |
|---------|-----|
| `public/pages/sistema/dashboard.php` | `requireRole(['admin', 'soporte'])` |
| `public/js/modules/dashboard.js` | Carga vía API |

---

## Ventas (punto de venta)

**Función**: búsqueda de productos (≥2 caracteres), carrito en memoria, selección de cliente, confirmación que llama a **`ventas_create.php`**.

**Datos**: productos y clientes desde API; persistencia de la venta en **Oracle**.

| Archivo | Rol |
|---------|-----|
| `public/pages/sistema/ventas.php` | `requireRole(['admin', 'soporte', 'cajero'])` |
| `public/js/modules/ventas.js` | Lógica POS |

---

## Pagos

**Función**: listar ventas con saldo pendiente, registrar pago (`pagos_create.php`), métodos desde `metodos_pago_list.php`.

| Archivo | Rol |
|---------|-----|
| `public/pages/sistema/pagos.php` | `requireRole(['admin', 'soporte', 'cajero'])` |
| `public/js/modules/pagos.js` | Carga ventas y registro de pagos |

---

## Productos y categorías

**Función**: listado con filtros (búsqueda, categoría, estado), modales para producto y categoría, persistencia **`productos_save.php`**, listas **`categorias_*`**, **`estados_list.php`**.

| Archivo | Rol |
|---------|-----|
| `public/pages/sistema/productos.php` | `requireRole(['admin', 'inventario', 'soporte'])` |
| `public/js/modules/productos.js` | CRUD UI |

---

## Inventario

**Función**: vista de stock con filtros y ajuste de cantidad según permiso (`data-can-edit-stock` en el body).

**Datos**: `productos_list.php` / actualización vía flujo de productos según implementación en `inventario.js`.

| Archivo | Rol |
|---------|-----|
| `public/pages/sistema/inventario.php` | `requireRole(['admin', 'soporte', 'cajero', 'inventario'])` |
| `public/js/modules/inventario.js` | Tabla y modales |

---

## Clientes (sistema)

**Función**: tabla de clientes con búsqueda; datos desde **`clientes_list.php`** (solo lectura en la UI actual).

| Archivo | Rol |
|---------|-----|
| `public/pages/sistema/clientes.php` | admin, soporte, cajero |
| `public/js/modules/clientes.js` | Listado + filtro |

**Registro de nuevos clientes desde tienda**: `auth_register_cliente.php` (no es el CRUD de esta pantalla).

---

## Ubicaciones

**Función**: CRUD provincias, cantones y distritos persistidos en Oracle.

**Datos**: `provincias_*`, `cantones_*`, `distritos_*` (`pkg_provincias`, `pkg_cantones`, `pkg_distritos`); listados vía REF CURSOR. Front: `api.js` + `ubicaciones.js`.

| Archivo | Rol |
|---------|-----|
| `public/pages/sistema/ubicaciones.php` | `requireRole(['admin', 'soporte'])` |
| `public/js/modules/ubicaciones.js` | `Api.get` / `Api.post` |

---

## Direcciones y teléfonos

**Función**: direcciones (cliente o proveedor), teléfonos de clientes y teléfonos adicionales de contactos de proveedor.

**Datos**: `direcciones_*`, `telefonos_clientes_*`, `telefonos_cont_proveedor_*`; contactos para el formulario desde `contactos_proveedor_list.php?proveedorId=`.

| Archivo | Rol |
|---------|-----|
| `public/pages/sistema/datos_auxiliares.php` | `requireRole(['admin', 'soporte'])` |
| `public/js/modules/datos_auxiliares.js` | Pestañas y modales |

---

## Gestión de stock (movimientos)

**Función**: catálogo de tipos de gestión y registro de movimientos (`cantidad` ≠ 0, fecha, producto, tipo).

**Datos**: `tipo_gestion_*`, `gestion_stock_*`, `productos_list.php`.

| Archivo | Rol |
|---------|-----|
| `public/pages/sistema/gestion_stock.php` | `requireRole(['admin', 'soporte', 'inventario'])` |
| `public/js/modules/gestion_stock.js` | Tablas y modales |

---

## Métodos de pago (catálogo)

**Función**: alta, edición y baja de métodos de pago en Oracle (además del uso en **Pagos**, que solo consume el listado).

| Archivo | Rol |
|---------|-----|
| `public/pages/sistema/metodos_pago.php` | `requireRole(['admin', 'soporte'])` |
| `public/js/modules/metodos_pago_admin.js` | CRUD vía `metodos_pago_save.php` |

---

## Proveedores y contactos

**Función**: grid de proveedores, panel de contactos, formularios contra API Oracle.

| Archivo | Rol |
|---------|-----|
| `public/pages/sistema/proveedores.php` | `requireRole(['admin', 'soporte', 'inventario'])` |
| `public/js/modules/proveedores.js` | Llamadas API |

---

## Compras

**Función**: carrito de compra a proveedor, **`compras_create.php`**, carga de productos y proveedores por API.

| Archivo | Rol |
|---------|-----|
| `public/pages/sistema/compras.php` | `requireRole(['admin', 'soporte', 'inventario'])` |
| `public/js/modules/compras.js` | Flujo de compra |

---

## Empleados

**Función**: CRUD empleados Oracle (`empleados_list.php`, `empleados_save.php`), estados desde API.

| Archivo | Rol |
|---------|-----|
| `public/pages/sistema/empleados.php` | admin, soporte |
| `public/js/modules/empleados.js` | CRUD |

---

## Usuarios

**Función**: CRUD usuarios vinculados a empleados; roles y estados desde API.

| Archivo | Rol |
|---------|-----|
| `public/pages/sistema/usuarios.php` | admin |
| `public/js/modules/usuarios.js` | CRUD |

---

## Reportes

**Función**: filtros por fechas, tablas de ventas y pagos.

**Datos**: solo **`ventas_list.php`** (`reportes.js`; sin datos locales ni JSON de demostración).

| Archivo | Rol |
|---------|-----|
| `public/pages/sistema/reportes.php` | `requireRole(['admin', 'soporte'])` |
| `public/js/modules/reportes.js` | Agregación y filtros |

---

## Tienda pública

### Flujo resumido

1. **Catálogo**: `productos_list.php` vía `tienda-productos.js`. Quien **no** es rol `cliente` ve un **aviso único** sobre el grid (no CTA repetido en cada tarjeta) y no puede agregar al carrito (`HAMILTON_TIENDA_PUEDE_COMPRAR`).
2. **Registro**: `public/pages/auth/registro_cliente.php` → `auth_register_cliente.php`. La ruta `public/pages/tienda/registro.php` **redirige** al registro real.
3. **Login**: `public/pages/auth/login.php` → `auth_login.php`; parámetro `next=checkout` para volver al checkout.
4. **Carrito**: `localStorage` **`hamilton_tienda_carrito`** (`tienda-carrito.js`).
5. **Checkout**: `checkout.php` exige sesión **`cliente`** y carga nombre desde Oracle. `tienda-checkout.js` usa **`metodos_pago_list.php`**, **`ventas_create.php`** y **`pagos_create.php`** (Oracle).

### Archivos clave

| Archivo | Descripción |
|---------|-------------|
| `public/pages/tienda/Homepage.php` | Landing y grid destacados |
| `public/pages/tienda/AllProducts.php`, `catalogo.php` | Catálogos |
| `public/pages/tienda/checkout.php` | Checkout (sesión cliente) |
| `public/pages/tienda/registro.php` | Redirect a `auth/registro_cliente.php` |
| `public/components/layout_tienda.php` | Navbar, `API_BASE`, flags tienda |
| `public/js/modules/tienda-productos.js` | Grid de productos |
| `public/js/modules/tienda-carrito.js` | Carrito |
| `public/js/modules/tienda-checkout.js` | Pago: API Oracle (venta + cobro) |

---

## Almacenamiento en el navegador

| Clave / uso | Contenido |
|-------------|-----------|
| `hamilton_tienda_carrito` | Ítems del carrito de la tienda |
| `hamilton_ubicaciones` | Obsoleto: `ubicaciones.js` ya no usa esta clave (datos en Oracle) |
| `hamilton_empleados`, `hamilton_usuarios`, … | Posible seed o legado; operación real vía API donde esté cableado |

---

## Layout del sistema

- **Navbar / sidebar**: `public/components/navbar.php`, `sidebar.php`.
- **Scripts comunes**: `public/components/head.php`, `scripts_bootstrap.php`; páginas sistema incluyen `Api` vía variable de entorno en JS similar a la tienda (según `head` / pie de cada página).

Para detalles de columnas y paquetes PL/SQL, revisar scripts en **`docs/sql/`** y el código de cada endpoint en `backend/api/`.
