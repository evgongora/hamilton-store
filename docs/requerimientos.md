# Requerimientos — M. Hamilton Store

## Módulos del sistema (backoffice)

- Dashboard (resumen)
- Productos y categorías
- Inventario (stock)
- Clientes (consulta)
- Ubicaciones geográficas (CRUD en front con persistencia local)
- Proveedores y contactos
- Compras a proveedor
- Ventas (punto de venta)
- Pagos
- Empleados
- Usuarios y roles
- Reportes

## Tienda pública

- Catálogo y búsqueda
- Carrito y checkout (cliente autenticado)
- Registro de cuenta cliente y login

## Stack actual

| Capa | Tecnología |
|------|------------|
| Servidor | PHP |
| Base de datos | Oracle (OCI8) |
| API | JSON REST en `backend/api/` |
| UI | Bootstrap 5, JavaScript modular |
| Estilos | `public/css/styles.css` |

Los datos operativos **persisten en Oracle** en los flujos conectados a la API. Algunas pantallas mantienen **respaldo o demo** en `localStorage` o JSON estático cuando la API no está disponible o para funciones aún simuladas (p. ej. checkout tienda). Detalle en [modulos-sistema.md](./modulos-sistema.md).
