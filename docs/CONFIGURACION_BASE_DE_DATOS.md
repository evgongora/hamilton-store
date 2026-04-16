# Configuración Oracle (local)

**Requisitos:** PHP con extensión **oci8**, **Oracle Instant Client** (misma arquitectura que tu PHP; en XAMPP macOS suele ser x86_64 bajo Rosetta), y el **wallet** si usas Autonomous Database.

## `.env`

```bash
cp .env.example .env
```

Rellena al menos: `ORACLE_USER`, `ORACLE_PASSWORD`, `ORACLE_CONNECTION`.  
`ORACLE_CONNECTION` debe ser el **nombre exacto** de una entrada en `tnsnames.ora` del wallet (p. ej. `*_high`).

Opcional: `ORACLE_WALLET_TNS_ADMIN` si el wallet no va en la carpeta por defecto; `ORACLE_SSL_SERVER_DN_MATCH=no` si en local la conexión TCPS falla sin error claro.

No subas `.env` a Git.

## Wallet

Descomprime el ZIP de OCI dentro de **`backend/config/oracle-wallet/`** (mismo nivel que el README de esa carpeta: `tnsnames.ora`, `sqlnet.ora`, `cwallet.sso`, etc.). No versiones el contenido del wallet.

## OCI8

Instala Instant Client, compila o instala **oci8** para tu PHP y actívalo en el **`php.ini` que usa Apache**. Reinicia Apache.

En **macOS**, si `oci8.so` quedó enlazado al Instant Client en **Descargas**, Apache puede no cargar la librería: mueve el cliente a una ruta del sistema (p. ej. bajo XAMPP) y vuelve a enlazar o reinstala oci8 con `ORACLE_HOME` apuntando ahí. Si hace falta, `sudo install_name_tool` para corregir `LC_RPATH` del `oci8.so` (documentación PECL/Oracle).

**Comprobar:** login en la aplicación. Si no carga oci8, revisa el log de PHP de Apache y que CLI y Apache usen el mismo `php.ini` cuando corresponda.

## SQL del esquema

Scripts en **`docs/sql/`** (por ejemplo `inserts.sql`, paquetes y triggers según el proyecto). Aplicar en el orden indicado por el curso o el README del módulo de base de datos.

## Aplicación

- Conexión: `backend/config/db.php` lee variables de entorno y usa el wallet si aplica.
- La API en `backend/api/` ejecuta SQL y paquetes contra ese esquema; los usuarios de prueba tras cargar datos iniciales suelen documentarse en `docs/sql/inserts.sql` o material equivalente.
