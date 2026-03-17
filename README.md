# M. Hamilton Store

Sistema de gestión (inventario, ventas, compras) para tienda de electrónica.

## Estructura

```
hamilton-store/
├── index.php                  # Redirige a tienda (public/pages/tienda/Homepage)
├── public/
│   ├── index.php              # Sistema: login o dashboard
│   ├── pages/                 # Todas las páginas
│   │   ├── tienda/            # Tienda pública
│   │   ├── auth/               # login, no_access
│   │   └── sistema/            # dashboard, productos, inventario, etc.
│   ├── components/            # head, navbar, sidebar, footer, layout_tienda
│   ├── css/                   # Un solo styles.css
│   ├── js/                    # app.js, scripts.js, modules/, services/, mocks/
│   └── assets/img/            # Imágenes compartidas
├── backend/config/, api/
└── docs/
```

## Acceso (punto de entrada único)

| URL | Sin sesión | Cliente | Staff |
|-----|------------|---------|------|
| `http://localhost/hamilton-store/` | Tienda (catálogo) | Tienda + comprar | Dashboard |

## Login mock

Usuario y rol cualquiera. Roles: `admin`, `cajero`, `inventario`, `cliente`. El rol determina el destino tras el login: cliente → tienda; staff → dashboard.