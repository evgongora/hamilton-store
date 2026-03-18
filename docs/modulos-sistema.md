# Módulos del Sistema - M. Hamilton Store

Documentación de los módulos implementados del dashboard: Ventas, Productos, Clientes, Pagos y la tienda pública.

---

## Dashboard

### Funcionalidad

Panel principal con métricas y últimas ventas.

| Métrica | Fuente |
|---------|--------|
| Total ventas | Suma de `hamilton_ventas[].total` |
| Total pagos | Suma de pagos en ventas |
| Clientes | Count de `hamilton_clientes` |
| Empleados | Count de `hamilton_empleados` |
| Últimas ventas | Últimas 8 ventas en tabla |

### Archivos

| Archivo | Rol |
|---------|-----|
| `public/pages/sistema/dashboard.php` | UI: cards de métricas, tabla ventas |
| `public/js/modules/dashboard.js` | Carga datos de localStorage, renderiza |

---

### Estructura general

- **Layout**: `app-layout` (flex columna), `app-main` (sidebar + contenido)
- **Navbar**: Logo a la izquierda, usuario + "Cerrar sesión" en esquina derecha (fondo blanco)
- **Sidebar**: Menú lateral con 12 módulos (Dashboard, Productos, Inventario, Clientes, etc.)

### Archivos

| Componente | Archivo |
|------------|---------|
| Dashboard principal | `public/pages/sistema/dashboard.php` |
| Navbar del sistema | `public/components/navbar.php` |
| Sidebar / menú | `public/components/sidebar.php` |

### Módulos del menú

- Dashboard, Productos y Categorías, Inventario, Clientes, Ubicaciones
- Proveedores, Compras, Ventas, Pagos
- Empleados, Usuarios, Reportes

---

## Ventas (Punto de venta)

### Funcionalidad

Módulo de punto de venta para staff (cajero/admin). Todo en **mock** sin base de datos.

| Función | Descripción |
|---------|-------------|
| Buscar producto | Input de búsqueda, mínimo 2 caracteres. Usa `productos.json` |
| Agregar al carrito | Cantidad editable, subtotal por línea, total general |
| Seleccionar cliente | Dropdown con clientes de `hamilton_clientes` (localStorage) o `clientes.json` |
| Confirmar venta | Guarda en `localStorage` clave `hamilton_ventas` |

### Estructura de una venta guardada (alineada con BD)

```json
{
  "id": 1,
  "fecha": "2026-03-17T...",
  "total": 1298,
  "clientesIdCliente": 1,
  "clienteNombre": "Juan Carlos Mora",
  "empleadosIdEmpleado": 1,
  "origen": "sistema",
  "items": [
    {
      "productosIdProducto": 1,
      "nombre": "Tarjeta gráfica RTX 4070",
      "cantidad": 2,
      "precioUnitario": 649,
      "subtotal": 1298
    }
  ],
  "pagos": []
}
```

### Archivos

| Archivo | Rol |
|---------|-----|
| `public/pages/sistema/ventas.php` | UI del punto de venta |
| `public/js/modules/ventas.js` | Lógica: búsqueda, carrito, totales, guardar venta |

---

## Pagos

### Funcionalidad

Registrar pagos y asociarlos a ventas. Usa las ventas guardadas en `localStorage`.

| Función | Descripción |
|---------|-------------|
| Seleccionar venta | Solo ventas con saldo pendiente |
| Método de pago | Efectivo, SINPE, Tarjeta, Transferencia |
| Monto | Input numérico |
| Registrar pago | Se agrega a `venta.pagos[]` y se actualiza la venta |

### Estructura de un pago (alineada con BD)

```json
{
  "monto": 500,
  "fechaPago": "2026-03-17T...",
  "metodosPagoIdMetodoPago": 2
}
```

Los métodos de pago provienen de `public/js/mocks/metodos_pago.json`.

### Archivos

| Archivo | Rol |
|---------|-----|
| `public/pages/sistema/pagos.php` | UI: selector venta, método, monto |
| `public/js/modules/pagos.js` | Lógica: listar ventas pendientes, registrar pago |

---

## Productos

### Módulo sistema (`productos.php`)

- **Estado**: Placeholder (módulo en desarrollo)
- **Ruta**: `public/pages/sistema/productos.php`
- **Uso futuro**: Gestión interna (CRUD de productos y categorías)

### Datos mock

Los productos usados en ventas y tienda provienen de:

| Archivo | Campos |
|---------|--------|
| `public/js/mocks/productos.json` | `id`, `nombre`, `precioCompra`, `precioVenta`, `cantidad`, `estado`, `categoriasIdCategoria` |

---

## Clientes

### Funcionalidad

CRUD completo con selects dependientes (Provincia → Cantón → Distrito).

| Función | Descripción |
|---------|-------------|
| Listar | Tabla con nombre, email, teléfono, ubicación, estado |
| Nuevo / Editar | Modal con formulario y selects dependientes de ubicación |
| Eliminar | Confirmación antes de borrar |
| Persistencia | `localStorage` clave `hamilton_clientes` (seed desde `clientes.json`) |

Los clientes creados aquí aparecen en el selector de Ventas.

### Datos mock

| Archivo | Uso |
|---------|-----|
| `clientes.json` | Seed inicial si no hay datos en localStorage |
| `ubicaciones.json` | Provincia, cantón, distrito (Costa Rica) para selects dependientes |

### Archivos

| Archivo | Rol |
|---------|-----|
| `public/pages/sistema/clientes.php` | UI: tabla, modal CRUD |
| `public/js/modules/clientes.js` | Lógica CRUD, cascada provincia→cantón→distrito |

---

## Ubicaciones

### Funcionalidad

CRUD de provincias, cantones y distritos (Costa Rica). Tres pestañas con tablas independientes.

| Pestaña | CRUD | Dependencias |
|---------|------|--------------|
| Provincias | Crear, editar, eliminar | — |
| Cantones | Crear, editar, eliminar | Provincia (select) |
| Distritos | Crear, editar, eliminar | Cantón (select), código postal |

Cliente y Proveedores usan ubicaciones para direcciones. Clientes lee de `hamilton_ubicaciones` o `ubicaciones.json`.

### Archivos

| Archivo | Rol |
|---------|-----|
| `public/pages/sistema/ubicaciones.php` | UI: tabs, tablas, modales |
| `public/js/modules/ubicaciones.js` | CRUD, persistencia localStorage |

---

## Empleados

### Funcionalidad

CRUD empleados (nombre, apellido, puesto, email, fecha ingreso, estado).

| Función | Descripción |
|---------|-------------|
| Listar | Tabla con datos básicos |
| Nuevo / Editar | Modal con formulario |
| Eliminar | Confirmación |
| Persistencia | `hamilton_empleados` (seed desde `empleados.json`) |

Usuarios requiere empleados para asignar usuario a empleado. Ventas usa `empleadosIdEmpleado`.

### Archivos

| Archivo | Rol |
|---------|-----|
| `public/pages/sistema/empleados.php` | UI: tabla, modal |
| `public/js/modules/empleados.js` | CRUD, localStorage |

---

## Usuarios

### Funcionalidad

CRUD usuarios vinculados a empleados (1 usuario por empleado).

| Campo | Descripción |
|-------|-------------|
| Usuario | Login (username) |
| Contraseña | Obligatoria al crear, opcional al editar |
| Rol | admin, cajero, inventario, cliente |
| Estado | activo, inactivo |
| Empleado | Select de empleados sin usuario (o el actual si edita) |

### Archivos

| Archivo | Rol |
|---------|-----|
| `public/pages/sistema/usuarios.php` | UI: tabla, modal |
| `public/js/modules/usuarios.js` | CRUD, validación empleado único |
| `public/js/mocks/usuarios.json` | Seed inicial |

---

## Reportes

### Funcionalidad

Tablas de ventas y pagos con filtros por rango de fechas.

| Función | Descripción |
|---------|-------------|
| Filtros | Fecha desde / hasta (por defecto últimos 30 días) |
| Tabla ventas | ID, fecha, cliente, origen, total |
| Tabla pagos | Venta #, fecha pago, monto |
| Totales | Suma de ventas y pagos filtrados |

### Datos

Usa `localStorage` clave `hamilton_ventas`.

### Archivos

| Archivo | Rol |
|---------|-----|
| `public/pages/sistema/reportes.php` | UI: filtros, tablas |
| `public/js/modules/reportes.js` | Carga ventas, filtra por fechas, renderiza |

---

## Tienda pública (Clientes)

### Flujo

1. **Registro**: Crear cuenta (nombre, apellido, email, teléfono, contraseña). Se agrega a `hamilton_clientes` y se establece cookie de sesión.
2. **Login cliente**: Email + contraseña. Valida contra `hamilton_clientes`. Cookie `hamilton_cliente` por 7 días.
3. **Homepage / Catálogo**: Productos desde `productos.json`, stepper de cantidad + "Agregar al carrito"
4. **Carrito**: `localStorage` clave `hamilton_tienda_carrito`
5. **Checkout**: Si hay cliente logueado (cookie), la venta se vincula a ese `clientesIdCliente`. Si no, "Cliente tienda".
6. **Confirmación mock**: Se crea venta en `hamilton_ventas` y se vacía el carrito

### Unificación clientes

- **Mismo pool**: Los clientes del CRUD (staff) y los que se registran/compran en la tienda son la misma entidad.
- **Cliente sin contraseña**: Si staff crea un cliente y luego esa persona va a "Registro" con el mismo email, se establece su contraseña y puede comprar.
- **Campo `password`**: Solo en mock (no existe en BD). No se persiste al conectar Oracle.

### Archivos

| Archivo | Rol |
|---------|-----|
| `public/pages/tienda/registro.php` | Registro de clientes |
| `public/pages/tienda/Homepage.php` | Productos destacados, agregar al carrito |
| `public/pages/tienda/catalogo.php` | Catálogo de productos |
| `public/pages/tienda/checkout.php` | Checkout y pasarela de pago mock |
| `public/js/modules/auth-cliente.js` | Registro, login cliente, cookie de sesión |
| `public/js/modules/tienda-carrito.js` | Carrito (add, remove, getItems, getTotal) |
| `public/js/modules/tienda-productos.js` | Carga productos, stepper cantidad |
| `public/js/modules/tienda-checkout.js` | Checkout y registro de venta mock |

---

## Almacenamiento (localStorage)

| Clave | Contenido |
|-------|-----------|
| `hamilton_ventas` | Array de ventas (sistema + tienda) |
| `hamilton_clientes` | Array de clientes (CRUD clientes) |
| `hamilton_ubicaciones` | { provincias, cantones, distritos } |
| `hamilton_empleados` | Array de empleados |
| `hamilton_usuarios` | Array de usuarios |
| `hamilton_tienda_carrito` | Array de items en carrito del cliente |

---

## Resumen de datos mock (alineados con esquema Oracle)

| Mock | Usado en |
|------|----------|
| `productos.json` | Ventas (staff), Tienda (catálogo, checkout) |
| `clientes.json` | Seed inicial para clientes; ventas usa `hamilton_clientes` si existe |
| `ubicaciones.json` | Seed para Ubicaciones; Clientes usa `hamilton_ubicaciones` si existe |
| `usuarios.json` | Seed para Usuarios |
| `categorias.json` | Referencia para productos (categoriasIdCategoria) |
| `metodos_pago.json` | Ventas (pagos), Pagos (registro), Checkout tienda |
| `empleados.json` | Referencia para ventas (empleadosIdEmpleado) |
| `ventas.json` | No se usa; ventas reales van a `localStorage` |
