# Arquitectura — M. Hamilton Store

## Vista general

- **Servidor**: PHP (Apache / XAMPP) sirve páginas en `public/` y expone la API en `backend/api/`.
- **Datos**: Oracle Database; acceso con **OCI8** (`backend/config/db.php`). Paquetes PL/SQL según el esquema del proyecto.
- **Cliente**: HTML + **Bootstrap 5.2** + JavaScript sin framework; módulos en `public/js/modules/` que usan `public/js/services/api.js` (fetch JSON con `credentials: 'same-origin'`).

## Estructura de carpetas (resumen)

```
public/
├── index.php              # Redirige a ../ (raíz del repo)
├── pages/
│   ├── tienda/            # Homepage, AllProducts, catalogo, checkout, registro (redirect)
│   ├── auth/              # login, registro_cliente, no_access
│   └── sistema/           # dashboard, productos, ventas, …
├── components/            # head, navbar, sidebar, layout_tienda
├── css/styles.css
└── js/
    ├── services/api.js    # GET/POST a API_BASE
    ├── modules/           # Lógica por pantalla
    └── ui-dialog.js

backend/
├── api/*.php              # Endpoints REST (JSON)
└── config/
    ├── db.php
    ├── auth_guard.php     # requireLogin, requireStaff, requireRole, rutas por rol
    ├── api_helpers.php
    └── paths.php          # Rutas base (proyecto, public, api)
```

## Autenticación y autorización

| Mecanismo | Uso |
|-----------|-----|
| `requireLogin()` | Sesión obligatoria; si no hay usuario → redirect a `login.php`. |
| `requireStaff()` | Solo personal (no rol `cliente`); si no → redirect a tienda. |
| `requireRole([...])` | Lista de roles permitidos; si no → `no_access.php`. |
| `getRoleHomePath($role)` | Tras login: destino según rol (p. ej. cliente → tienda, cajero → clientes). |
| `hamilton_staff_menu_keys($role)` | Claves de ítems del sidebar permitidos por rol. |

Sesión típica: `user` (nombre de usuario), `role`, y para operación interna `empleado_id` cuando el login lo resolvió en Oracle.

## Tienda pública

- **Layout**: `public/components/layout_tienda.php` inyecta `API_BASE`, `HAMILTON_TIENDA_PUEDE_COMPRAR` (true solo si `role === 'cliente'`), `HAMILTON_LOGIN_URL`.
- **Catálogo**: productos desde `GET …/productos_list.php`.
- **Carrito**: `localStorage` (`hamilton_tienda_carrito`).
- **Checkout**: `checkout.php` exige sesión con rol **cliente**; si no, redirect a `login.php?next=checkout`. La pasarela en UI sigue siendo **simulada** (ver `modulos-sistema.md`).

## Documentación detallada

- [modulos-sistema.md](./modulos-sistema.md) — Módulos, API, almacenamiento local de respaldo.
- [requerimientos-dashboard.md](./requerimientos-dashboard.md) — Requerimientos vs pantallas.
