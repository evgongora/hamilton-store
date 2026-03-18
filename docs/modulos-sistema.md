# Módulos del Sistema - M. Hamilton Store

Documentación de los módulos implementados del dashboard: Ventas, Productos, Clientes, Pagos y la tienda pública.

---

## Dashboard

### Estructura

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
| Seleccionar cliente | Dropdown con clientes de `clientes.json` |
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

### Módulo sistema (`clientes.php`)

- **Estado**: Placeholder (módulo en desarrollo)
- **Ruta**: `public/pages/sistema/clientes.php`
- **Uso futuro**: Gestión de clientes (CRUD)

### Datos mock

Los clientes usados en ventas provienen de:

| Archivo | Campos |
|---------|--------|
| `public/js/mocks/clientes.json` | `id`, `nombre`, `apellido`, `email`, `telefono`, `fechaIngreso`, `estado` |

---

## Tienda pública (Clientes)

### Flujo

1. **Homepage / Catálogo**: Productos desde `productos.json`, stepper de cantidad + "Agregar al carrito"
2. **Carrito**: `localStorage` clave `hamilton_tienda_carrito`
3. **Checkout**: Resumen, método de pago (Efectivo, SINPE, Tarjeta), botón "Pagar ahora"
4. **Confirmación mock**: Se crea venta en `hamilton_ventas` y se vacía el carrito

### Archivos

| Archivo | Rol |
|---------|-----|
| `public/pages/tienda/Homepage.php` | Productos destacados, agregar al carrito |
| `public/pages/tienda/catalogo.php` | Catálogo de productos |
| `public/pages/tienda/checkout.php` | Checkout y pasarela de pago mock |
| `public/js/modules/tienda-carrito.js` | Carrito (add, remove, getItems, getTotal) |
| `public/js/modules/tienda-productos.js` | Carga productos, stepper cantidad |
| `public/js/modules/tienda-checkout.js` | Checkout y registro de venta mock |

---

## Almacenamiento (localStorage)

| Clave | Contenido |
|-------|-----------|
| `hamilton_ventas` | Array de ventas (sistema + tienda) |
| `hamilton_tienda_carrito` | Array de items en carrito del cliente |

---

## Resumen de datos mock (alineados con esquema Oracle)

| Mock | Usado en |
|------|----------|
| `productos.json` | Ventas (staff), Tienda (catálogo, checkout) |
| `clientes.json` | Ventas (selector de cliente) |
| `categorias.json` | Referencia para productos (categoriasIdCategoria) |
| `metodos_pago.json` | Ventas (pagos), Pagos (registro), Checkout tienda |
| `empleados.json` | Referencia para ventas (empleadosIdEmpleado) |
| `ventas.json` | No se usa; ventas reales van a `localStorage` |
