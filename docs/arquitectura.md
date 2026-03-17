# Arquitectura - M. Hamilton Store

## Estructura del proyecto

```
public/
├── index.php         # Sistema: login o dashboard
├── pages/            # Todas las pantallas
│   ├── tienda/       # Tienda pública (Homepage, AllProducts, catalogo)
│   └── [login, dashboard, módulos...]
├── components/       # head, navbar, sidebar, footer, layout_tienda
├── css/              # styles.css (tienda + sistema)
├── js/               # scripts.js, app.js, modules/, services/, mocks/
└── assets/img/       # Imágenes compartidas
```

## Autenticación

- Login mock: usuario + rol (admin/cajero/inventario)
- `auth_guard.php`: `requireLogin()`, `requireRole()`
- Sesión: `$_SESSION['user']`, `$_SESSION['role']`
