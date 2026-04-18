# Paquetes PL/SQL (`cruds_packages.sql`) y uso en la API PHP

Este documento describe cómo están definidos los paquetes en `docs/sql/cruds_packages.sql`, cómo los invoca el código en `backend/api/`, y en qué se diferencia ese uso respecto al resto de los endpoints (sobre todo lecturas con SQL directo).

## Ubicación del script

- **Archivo**: [`docs/sql/cruds_packages.sql`](sql/cruds_packages.sql)
- **Esquema**: `M_HAMILTON_STORE` (objetos calificados con prefijo `M_HAMILTON_STORE.` para compilar aunque la sesión sea otro usuario, p. ej. `ADMIN`).

## Qué contiene el script

Cada paquete tiene:

1. **Especificación** (`CREATE OR REPLACE PACKAGE ... AS`): firma de procedimientos y funciones.
2. **Cuerpo** (`CREATE OR REPLACE PACKAGE BODY ... AS`): implementación.

Patrones habituales en el archivo:

| Elemento | Uso |
|----------|-----|
| `TYPE t_cursor IS REF CURSOR` | Procedimientos de lectura (`sp_obtener_*`, `sp_listar_*`) que devuelven un cursor. |
| Funciones de apoyo | `fn_existe_*`, `fn_texto_vacio`, `fn_nombre_valido`, etc. |
| Procedimientos CRUD | `sp_insertar_*`, `sp_actualizar_*`, `sp_eliminar_*`, más `sp_obtener_*` / `sp_listar_*` para consultas. |

### Paquetes declarados en `cruds_packages.sql`

- `pkg_ref_catalogos` — roles, estados, categorías  
- `pkg_clientes`, `pkg_proveedores`, `pkg_empleados`, `pkg_productos`  
- `pkg_contactos_proveedores`  
- `pkg_direcciones`, `pkg_telefonos_clientes`, `pkg_telefonos_cont_proveedores`  
- `pkg_encabezados_compras`, `pkg_detalles_compras`, `pkg_encabezados_ventas`, `pkg_detalles_ventas`  
- `pkg_pagos`, `pkg_facturas`, `pkg_usuarios`  
- `pkg_gestion_stock`, `pkg_tipo_gestion`, `pkg_metodos_pago`  
- `pkg_provincias`, `pkg_cantones`, `pkg_distritos`  

### ¿Todos se llaman desde el código?

Criterio: aparición de `M_HAMILTON_STORE.pkg_<nombre>` en SQL ejecutado desde PHP (`backend/`), no solo menciones en comentarios o en `docs/sql/`.

| Paquete | ¿Invocado desde PHP? | Notas |
|---------|----------------------|--------|
| `pkg_ref_catalogos` | Parcial | Solo **categorías** (`categorias_save.php`). Roles/estados: sin endpoints HTTP que llamen `sp_*_rol` / `sp_*_estado`. |
| `pkg_clientes` | Sí | `auth_register_cliente.php` |
| `pkg_proveedores` | Sí | `proveedores_save.php` |
| `pkg_empleados` | Sí | `empleados_save.php` |
| `pkg_productos` | Sí | `productos_save.php` |
| `pkg_contactos_proveedores` | Sí | `contactos_proveedor_save.php` |
| `pkg_direcciones` | Sí | `direcciones_list.php`, `direcciones_save.php` |
| `pkg_telefonos_clientes` | Sí | `telefonos_clientes_list.php`, `telefonos_clientes_save.php` |
| `pkg_telefonos_cont_proveedores` | Sí | `telefonos_cont_proveedor_list.php`, `telefonos_cont_proveedor_save.php` |
| `pkg_encabezados_compras` | Sí | `compras_create.php` |
| `pkg_detalles_compras` | Sí | `compras_create.php` |
| `pkg_encabezados_ventas` | Sí | `ventas_create.php` |
| `pkg_detalles_ventas` | Sí | `ventas_create.php` |
| `pkg_pagos` | Sí | `pagos_create.php` |
| `pkg_facturas` | Sí | `facturas_save.php` |
| `pkg_usuarios` | Sí | `usuarios_save.php`, `auth_register_cliente.php` |
| `pkg_gestion_stock` | Sí | `gestion_stock_list.php`, `gestion_stock_save.php` |
| `pkg_tipo_gestion` | Sí | `tipo_gestion_list.php`, `tipo_gestion_save.php` |
| `pkg_metodos_pago` | Sí | `metodos_pago_list.php`, `metodos_pago_save.php` |
| `pkg_provincias` | Sí | `provincias_list.php`, `provincias_save.php` |
| `pkg_cantones` | Sí | `cantones_list.php`, `cantones_save.php` |
| `pkg_distritos` | Sí | `distritos_list.php`, `distritos_save.php` |

## Comportamiento en Oracle (resumen)

- **Escrituras**: los `sp_insertar_*` / `sp_actualizar_*` / `sp_eliminar_*` concentran DML, validaciones y reglas; pueden interactuar con **triggers** sobre las tablas (p. ej. stock en productos).
- **Lecturas en paquete**: `sp_listar_*` / `sp_obtener_*` suelen usar **REF CURSOR** (`OUT t_cursor`); un cliente OCI típico enlazaría el cursor y leería filas desde ahí.

## Cómo los usa la API PHP

Los endpoints viven en `backend/api/`, con utilidades en `backend/config/api_helpers.php` (JSON, sesión, `api_require_oracle()`, etc.).

### 1. Mutaciones vía paquetes

Los endpoints que **crean, actualizan o eliminan** datos invocan bloques anónimos del estilo:

```sql
BEGIN M_HAMILTON_STORE.pkg_<nombre>.sp_<accion> (...); END;
```

En PHP: `oci_parse`, `oci_bind_by_name`, `oci_execute`; en flujos transaccionales a veces `OCI_NO_AUTO_COMMIT` con `oci_commit` / `oci_rollback`.

**Listados vía `sp_listar_*` (REF CURSOR):** `api_oci_ref_cursor_fetch_all()` en `backend/config/api_helpers.php` ejecuta un `BEGIN … sp_listar_…(:cur); END;`, enlaza `OCI_B_CURSOR` y devuelve filas en minúsculas. Lo usan, entre otros, `provincias_list.php`, `cantones_list.php`, `distritos_list.php`, `direcciones_list.php`, `metodos_pago_list.php`, `tipo_gestion_list.php`, `gestion_stock_list.php`, `telefonos_clientes_list.php`, `telefonos_cont_proveedor_list.php`.

### Mapeo endpoint → paquete(s)

| Endpoint | Paquete(s) |
|----------|------------|
| `categorias_save.php` | `pkg_ref_catalogos` (categorías) |
| `productos_save.php` | `pkg_productos` |
| `empleados_save.php` | `pkg_empleados` |
| `proveedores_save.php` | `pkg_proveedores` |
| `contactos_proveedor_save.php` | `pkg_contactos_proveedores` |
| `usuarios_save.php` | `pkg_usuarios` |
| `auth_register_cliente.php` | `pkg_clientes`, `pkg_usuarios` |
| `compras_create.php` | `pkg_encabezados_compras`, `pkg_detalles_compras` |
| `ventas_create.php` | `pkg_encabezados_ventas`, `pkg_detalles_ventas` |
| `pagos_create.php` | `pkg_pagos` |
| `facturas_save.php` | `pkg_facturas` |
| `provincias_list.php`, `provincias_save.php` | `pkg_provincias` |
| `cantones_list.php`, `cantones_save.php` | `pkg_cantones` |
| `distritos_list.php`, `distritos_save.php` | `pkg_distritos` |
| `direcciones_list.php`, `direcciones_save.php` | `pkg_direcciones` |
| `telefonos_clientes_list.php`, `telefonos_clientes_save.php` | `pkg_telefonos_clientes` |
| `telefonos_cont_proveedor_list.php`, `telefonos_cont_proveedor_save.php` | `pkg_telefonos_cont_proveedores` |
| `tipo_gestion_list.php`, `tipo_gestion_save.php` | `pkg_tipo_gestion` |
| `gestion_stock_list.php`, `gestion_stock_save.php` | `pkg_gestion_stock` |
| `metodos_pago_list.php`, `metodos_pago_save.php` | `pkg_metodos_pago` |

El cliente JavaScript puede usar `public/js/services/api.js` (`Api.get` / `Api.post`) contra `API_BASE`, apuntando a estos `.php`.

### 2. Lecturas vía SQL directo (sin `sp_listar_*`)

Muchos **GET** de listado siguen usando **`SELECT`** sobre tablas (`productos_list.php`, `categorias_list.php`, `estados_list.php`, `clientes_list.php`, `ventas_list.php`, etc.). Otros listados usan **`sp_listar_*`** y el helper de REF CURSOR (ver arriba).

La **autenticación** en `backend/config/auth_usuario.php` usa también `SELECT` directo a `usuarios` / `roles` / `estados`, no el paquete de usuarios para esa lectura.

### 3. Flujo híbrido

En `ventas_create.php` se inserta el encabezado con el paquete, se obtiene el `id_venta` con un `SELECT` auxiliar (p. ej. último id), y luego se insertan líneas con `pkg_detalles_ventas`. Es decir: **escritura por paquetes** y **lectura puntual** con SQL en PHP.

## Paquetes sin CRUD HTTP completo en `pkg_ref_catalogos`

Los procedimientos de **roles** y **estados** del mismo paquete existen en Oracle pero **no** tienen endpoints `*_save.php` dedicados en este repo (las pantallas usan `estados_list.php` / datos vía `SELECT` donde aplica).

## Comparación: paquetes vs “resto” de la API

| Aspecto | Paquetes (`cruds_packages.sql`) | Resto de endpoints PHP |
|--------|----------------------------------|-------------------------|
| Rol | Reglas de negocio y DML en PL/SQL | Orquestación HTTP, JSON, sesión y consultas ad hoc |
| Uso en este repo | **INSERT / UPDATE / DELETE** y composiciones (compra, venta, pago, factura) | Mezcla: muchos **GET** con `SELECT` directo; catálogos geográficos, métodos de pago, gestión de stock y anexos usan **`sp_listar_*`** + `api_oci_ref_cursor_fetch_all` |

## Implicación para el equipo

El script documenta una **API de base de datos** amplia. La **API HTTP** usa los paquetes para **escrituras** y, donde se implementó, también para **lecturas** vía REF CURSOR; en el resto de listados se sigue prefiriendo **SQL directo** por simplicidad.

## Referencias relacionadas

- [`docs/arquitectura.md`](arquitectura.md) — vista general del stack y carpetas.  
- [`docs/CONFIGURACION_BASE_DE_DATOS.md`](CONFIGURACION_BASE_DE_DATOS.md) — conexión Oracle / entorno.
