# M. Hamilton Store

Sistema de gestión (inventario, ventas, compras, proveedores) y **tienda pública** para una tienda de electrónica. Backend en **PHP** contra **Oracle**; front en **Bootstrap 5** y JavaScript modular que consume una **API JSON** bajo `backend/api/`.

## Estructura

```
hamilton-store/
├── index.php                    # Punto de entrada: staff → sistema; resto → tienda
├── public/
│   ├── index.php                # Redirige a la raíz del proyecto (../)
│   ├── pages/
│   │   ├── tienda/              # Homepage, catálogo, checkout (cliente)
│   │   ├── auth/                # login, registro_cliente, no_access
│   │   └── sistema/             # dashboard, módulos internos
│   ├── components/              # head, navbar, sidebar, layout_tienda, …
│   ├── css/                     # styles.css
│   ├── js/                      # modules/, services/api.js, ui-dialog.js, …
│   └── assets/img/
├── backend/
│   ├── api/                     # Endpoints REST (JSON)
│   └── config/                  # db.php, auth_guard.php, paths.php, oracle-wallet/
├── docs/                        # Documentación (arquitectura, módulos, SQL)
└── .env                         # Credenciales Oracle (ver .env.example)
```

## Acceso (punto de entrada único)

| URL (ejemplo local) | Comportamiento |
|---------------------|----------------|
| `http://localhost/hamilton-store/` | Sin sesión o **cliente** → tienda (`Homepage.php`). **Staff** con sesión → `public/pages/sistema/dashboard.php`. Tras **login** desde el formulario, el personal puede ir a otra pantalla inicial según rol (`getRoleHomePath()` en `auth_guard.php`, p. ej. cajero → clientes). |
| `http://localhost/hamilton-store/public/` | Redirige a `/hamilton-store/` (raíz del proyecto). |

- **Cliente**: inicia sesión y permanece en la tienda; ve carrito y checkout si el rol es `cliente`.
- **Personal**: inicia sesión y entra al sistema (menú filtrado por rol). Enlace **Dashboard** en la tienda si aplica.

## Base de datos (Oracle)

Configuración en **`.env`** (plantilla: **`.env.example`**), conexión vía **OCI8** y, si aplica, **wallet** en `backend/config/oracle-wallet/`. Detalle: [`docs/CONFIGURACION_BASE_DE_DATOS.md`](docs/CONFIGURACION_BASE_DE_DATOS.md).

Scripts SQL de esquema y datos de referencia: **`docs/sql/`** (p. ej. `inserts.sql`).

## Autenticación

- Login contra Oracle (`usuarios`, roles, estado activo); contraseñas con `password_hash` / bcrypt.
- Sesión PHP: `$_SESSION['user']`, `$_SESSION['role']`; para clientes de tienda también `cliente_id` cuando corresponde.
- **Registro solo clientes**: `public/pages/auth/registro_cliente.php` → API `auth_register_cliente.php`.
- **Logout**: `backend/api/auth_logout.php`.

Roles usados en código: **admin**, **soporte**, **cajero**, **inventario**, **cliente**. El menú lateral del sistema se restringe por rol (`hamilton_staff_menu_keys()` en `auth_guard.php`).

## API

Los módulos del front llaman a `window.API_BASE` (definido en layout de sistema o en `layout_tienda.php`), normalmente `…/hamilton-store/backend/api`. Listado y contratos: [`docs/modulos-sistema.md`](docs/modulos-sistema.md) y archivos en `backend/api/`.

## Documentación

| Archivo | Contenido |
|---------|-----------|
| [`docs/arquitectura.md`](docs/arquitectura.md) | Vista general técnica |
| [`docs/modulos-sistema.md`](docs/modulos-sistema.md) | Módulos, archivos, datos |
| [`docs/requerimientos-dashboard.md`](docs/requerimientos-dashboard.md) | Mapeo requerimientos ↔ pantallas |
| [`docs/CONFIGURACION_BASE_DE_DATOS.md`](docs/CONFIGURACION_BASE_DE_DATOS.md) | Oracle local |
