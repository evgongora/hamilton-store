-- =============================================================================
-- Paquetes PL/SQL del esquema M_HAMILTON_STORE.
-- Los CREATE usan prefijo M_HAMILTON_STORE. para que compilen aunque la sesión
-- sea ADMIN (Autonomous): las tablas deben existir en M_HAMILTON_STORE.
-- Opcional: conectar directamente como M_HAMILTON_STORE y ejecutar (F5).
-- =============================================================================
/*
PACKAGE: PKG_REF_CATALOGOS

Paquete para administrar tablas catalogo:
- roles
- estados
- categorias

*/


CREATE OR REPLACE PACKAGE M_HAMILTON_STORE.pkg_ref_catalogos AS

/*
CURSOR TIPO REF CURSOR
Se utiliza para devolver resultados de consultas
desde procedimientos */
  
    TYPE t_cursor IS REF CURSOR;

/*
Funciones solo para apoyo
*/
    FUNCTION fn_existe_rol(
        p_id_rol IN NUMBER
    ) RETURN NUMBER;

    FUNCTION fn_existe_estado(
        p_id_estado IN NUMBER
    ) RETURN NUMBER;

    FUNCTION fn_existe_categoria(
        p_id_categoria IN NUMBER
    ) RETURN NUMBER;

-- ---------------- CRUDS ------------------

/*
  CRUD DE ROLES
*/
    PROCEDURE sp_insertar_rol(
        p_nombre IN VARCHAR2
    );

    PROCEDURE sp_actualizar_rol(
        p_id_rol IN NUMBER,
        p_nombre IN VARCHAR2
    );

    PROCEDURE sp_eliminar_rol(
        p_id_rol IN NUMBER
    );

    PROCEDURE sp_obtener_rol(
        p_id_rol IN NUMBER,
        p_resultado OUT t_cursor
    );

    PROCEDURE sp_listar_roles(
        p_resultado OUT t_cursor
    );

/*
  CRUD DE ESTADOS
  */
    PROCEDURE sp_insertar_estado(
        p_nombre IN VARCHAR2
    );

    PROCEDURE sp_actualizar_estado(
        p_id_estado IN NUMBER,
        p_nombre IN VARCHAR2
    );

    PROCEDURE sp_eliminar_estado(
        p_id_estado IN NUMBER
    );

    PROCEDURE sp_obtener_estado(
        p_id_estado IN NUMBER,
        p_resultado OUT t_cursor
    );

    PROCEDURE sp_listar_estados(
        p_resultado OUT t_cursor
    );

/*
  CRUD DE CATEGORIAS
  */
    PROCEDURE sp_insertar_categoria(
        p_nombre IN VARCHAR2
    );

    PROCEDURE sp_actualizar_categoria(
        p_id_categoria IN NUMBER,
        p_nombre IN VARCHAR2
    );

    PROCEDURE sp_eliminar_categoria(
        p_id_categoria IN NUMBER
    );

    PROCEDURE sp_obtener_categoria(
        p_id_categoria IN NUMBER,
        p_resultado OUT t_cursor
    );

    PROCEDURE sp_listar_categorias(
        p_resultado OUT t_cursor
    );

END pkg_ref_catalogos;
/
SHOW ERRORS;

-- ---------------- END CRUDS ------------------




/*
PACKAGE BODY: PKG_REF_CATALOGOS

En esta seccion se implementa la logica de todos los
procedimientos y funciones declarados en el package.
*/
CREATE OR REPLACE PACKAGE BODY M_HAMILTON_STORE.pkg_ref_catalogos AS

/*
  Valida que un texto no venga nulo o vacio
*/
    FUNCTION fn_texto_vacio(
        p_texto IN VARCHAR2
    ) RETURN BOOLEAN
    IS
    BEGIN
        RETURN p_texto IS NULL OR TRIM(p_texto) IS NULL;
    END fn_texto_vacio;

/*expresion regular
  Valida nombres simples:solo letras, numeros y espacios
*/
    FUNCTION fn_nombre_valido(
        p_texto IN VARCHAR2
    ) RETURN BOOLEAN
    IS
    BEGIN
        RETURN REGEXP_LIKE(
            TRIM(p_texto),
            '^[A-Za-zÁÉÍÓÚáéíóúÑñ0-9 ]+$'
        );
    END fn_nombre_valido;

/*
Funciones publicas de si existe o no
*/

    FUNCTION fn_existe_rol(
        p_id_rol IN NUMBER
    ) RETURN NUMBER
    IS
        v_count NUMBER;
    BEGIN
        SELECT COUNT(*)
          INTO v_count
          FROM roles
         WHERE id_rol = p_id_rol;

        RETURN v_count;
    END fn_existe_rol;

    FUNCTION fn_existe_estado(
        p_id_estado IN NUMBER
    ) RETURN NUMBER
    IS
        v_count NUMBER;
    BEGIN
        SELECT COUNT(*)
          INTO v_count
          FROM estados
         WHERE id_estado = p_id_estado;

        RETURN v_count;
    END fn_existe_estado;

    FUNCTION fn_existe_categoria(
        p_id_categoria IN NUMBER
    ) RETURN NUMBER
    IS
        v_count NUMBER;
    BEGIN
        SELECT COUNT(*)
          INTO v_count
          FROM categorias
         WHERE id_categoria = p_id_categoria;

        RETURN v_count;
    END fn_existe_categoria;

/*
 CRUD DE ROLES
*/

    PROCEDURE sp_insertar_rol(
        p_nombre IN VARCHAR2
    )
    IS
        v_count NUMBER;
    BEGIN
        /* esto valida que el nombre no venga vacio */
        IF fn_texto_vacio(p_nombre) THEN
            RAISE_APPLICATION_ERROR(-20001, 'El nombre del rol es obligatorio.');
        END IF;

        /* esto va a validar formato con expresion regular */
        IF NOT fn_nombre_valido(p_nombre) THEN
            RAISE_APPLICATION_ERROR(-20002, 'El nombre del rol tiene caracteres no permitidos.');
        END IF;

        /* verifica duplicados */
        SELECT COUNT(*)
          INTO v_count
          FROM roles
         WHERE UPPER(TRIM(nombre)) = UPPER(TRIM(p_nombre));

        IF v_count > 0 THEN
            RAISE_APPLICATION_ERROR(-20003, 'Ya existe un rol con ese nombre.');
        END IF;

        /* Insertar rol */
        INSERT INTO roles(nombre)
        VALUES (TRIM(p_nombre));

    EXCEPTION
        WHEN OTHERS THEN
            RAISE_APPLICATION_ERROR(-20004, 'Error al insertar rol: ' || SQLERRM);
    END sp_insertar_rol;

    PROCEDURE sp_actualizar_rol(
        p_id_rol IN NUMBER,
        p_nombre IN VARCHAR2
    )
    IS
        v_count NUMBER;
    BEGIN
        /* valida la existencia del ID */
        IF fn_existe_rol(p_id_rol) = 0 THEN
            RAISE_APPLICATION_ERROR(-20005, 'El rol indicado no existe.');
        END IF;

        /* valida el nombre */
        IF fn_texto_vacio(p_nombre) THEN
            RAISE_APPLICATION_ERROR(-20006, 'El nombre del rol es obligatorio.');
        END IF;

        IF NOT fn_nombre_valido(p_nombre) THEN
            RAISE_APPLICATION_ERROR(-20007, 'El nombre del rol tiene caracteres no permitidos.');
        END IF;

        /* verifica que no exista otro con el mismo nombre */
        SELECT COUNT(*)
          INTO v_count
          FROM roles
         WHERE UPPER(TRIM(nombre)) = UPPER(TRIM(p_nombre))
           AND id_rol <> p_id_rol;

        IF v_count > 0 THEN
            RAISE_APPLICATION_ERROR(-20008, 'Ya existe otro rol con ese nombre.');
        END IF;

        /* Actualizar */
        UPDATE roles
           SET nombre = TRIM(p_nombre)
         WHERE id_rol = p_id_rol;

    EXCEPTION
        WHEN OTHERS THEN
            RAISE_APPLICATION_ERROR(-20009, 'Error al actualizar rol: ' || SQLERRM);
    END sp_actualizar_rol;

    PROCEDURE sp_eliminar_rol(
        p_id_rol IN NUMBER
    )
    IS
    BEGIN
        /* valida la existencia */
        IF fn_existe_rol(p_id_rol) = 0 THEN
            RAISE_APPLICATION_ERROR(-20010, 'El rol indicado no existe.');
        END IF;

        /* Eliminar */
        DELETE FROM roles
         WHERE id_rol = p_id_rol;

    EXCEPTION
        WHEN OTHERS THEN
            RAISE_APPLICATION_ERROR(-20011, 'Error al eliminar rol: ' || SQLERRM);
    END sp_eliminar_rol;

    PROCEDURE sp_obtener_rol(
        p_id_rol IN NUMBER,
        p_resultado OUT t_cursor
    )
    IS
    BEGIN
        IF fn_existe_rol(p_id_rol) = 0 THEN
            RAISE_APPLICATION_ERROR(-20012, 'El rol indicado no existe.');
        END IF;

        OPEN p_resultado FOR
            SELECT id_rol, nombre
              FROM roles
             WHERE id_rol = p_id_rol;

    EXCEPTION
        WHEN OTHERS THEN
            RAISE_APPLICATION_ERROR(-20013, 'Error al consultar rol: ' || SQLERRM);
    END sp_obtener_rol;

    PROCEDURE sp_listar_roles(
        p_resultado OUT t_cursor
    )
    IS
    BEGIN
        OPEN p_resultado FOR
            SELECT id_rol, nombre
              FROM roles
             ORDER BY id_rol;

    EXCEPTION
        WHEN OTHERS THEN
            RAISE_APPLICATION_ERROR(-20014, 'Error al listar roles: ' || SQLERRM);
    END sp_listar_roles;

/*}
  CRUD DE ESTADOS
*/

    PROCEDURE sp_insertar_estado(
        p_nombre IN VARCHAR2
    )
    IS
        v_count NUMBER;
    BEGIN
        IF fn_texto_vacio(p_nombre) THEN
            RAISE_APPLICATION_ERROR(-20015, 'El nombre del estado es obligatorio.');
        END IF;

        IF NOT fn_nombre_valido(p_nombre) THEN
            RAISE_APPLICATION_ERROR(-20016, 'El nombre del estado tiene caracteres no permitidos.');
        END IF;

        SELECT COUNT(*)
          INTO v_count
          FROM estados
         WHERE UPPER(TRIM(nombre)) = UPPER(TRIM(p_nombre));

        IF v_count > 0 THEN
            RAISE_APPLICATION_ERROR(-20017, 'Ya existe un estado con ese nombre.');
        END IF;

        INSERT INTO estados(nombre)
        VALUES (TRIM(p_nombre));

    EXCEPTION
        WHEN OTHERS THEN
            RAISE_APPLICATION_ERROR(-20018, 'Error al insertar estado: ' || SQLERRM);
    END sp_insertar_estado;

    PROCEDURE sp_actualizar_estado(
        p_id_estado IN NUMBER,
        p_nombre IN VARCHAR2
    )
    IS
        v_count NUMBER;
    BEGIN
        IF fn_existe_estado(p_id_estado) = 0 THEN
            RAISE_APPLICATION_ERROR(-20019, 'El estado indicado no existe.');
        END IF;

        IF fn_texto_vacio(p_nombre) THEN
            RAISE_APPLICATION_ERROR(-20020, 'El nombre del estado es obligatorio.');
        END IF;

        IF NOT fn_nombre_valido(p_nombre) THEN
            RAISE_APPLICATION_ERROR(-20021, 'El nombre del estado tiene caracteres no permitidos.');
        END IF;

        SELECT COUNT(*)
          INTO v_count
          FROM estados
         WHERE UPPER(TRIM(nombre)) = UPPER(TRIM(p_nombre))
           AND id_estado <> p_id_estado;

        IF v_count > 0 THEN
            RAISE_APPLICATION_ERROR(-20022, 'Ya existe otro estado con ese nombre.');
        END IF;

        UPDATE estados
           SET nombre = TRIM(p_nombre)
         WHERE id_estado = p_id_estado;

    EXCEPTION
        WHEN OTHERS THEN
            RAISE_APPLICATION_ERROR(-20023, 'Error al actualizar estado: ' || SQLERRM);
    END sp_actualizar_estado;

    PROCEDURE sp_eliminar_estado(
        p_id_estado IN NUMBER
    )
    IS
    BEGIN
        IF fn_existe_estado(p_id_estado) = 0 THEN
            RAISE_APPLICATION_ERROR(-20024, 'El estado indicado no existe.');
        END IF;

        DELETE FROM estados
         WHERE id_estado = p_id_estado;

    EXCEPTION
        WHEN OTHERS THEN
            RAISE_APPLICATION_ERROR(-20025, 'Error al eliminar estado: ' || SQLERRM);
    END sp_eliminar_estado;

    PROCEDURE sp_obtener_estado(
        p_id_estado IN NUMBER,
        p_resultado OUT t_cursor
    )
    IS
    BEGIN
        IF fn_existe_estado(p_id_estado) = 0 THEN
            RAISE_APPLICATION_ERROR(-20026, 'El estado indicado no existe.');
        END IF;

        OPEN p_resultado FOR
            SELECT id_estado, nombre
              FROM estados
             WHERE id_estado = p_id_estado;

    EXCEPTION
        WHEN OTHERS THEN
            RAISE_APPLICATION_ERROR(-20027, 'Error al consultar estado: ' || SQLERRM);
    END sp_obtener_estado;

    PROCEDURE sp_listar_estados(
        p_resultado OUT t_cursor
    )
    IS
    BEGIN
        OPEN p_resultado FOR
            SELECT id_estado, nombre
              FROM estados
             ORDER BY id_estado;

    EXCEPTION
        WHEN OTHERS THEN
            RAISE_APPLICATION_ERROR(-20028, 'Error al listar estados: ' || SQLERRM);
    END sp_listar_estados;

/*
  CRUD DE CATEGORIAS
*/

    PROCEDURE sp_insertar_categoria(
        p_nombre IN VARCHAR2
    )
    IS
        v_count NUMBER;
    BEGIN
        IF fn_texto_vacio(p_nombre) THEN
            RAISE_APPLICATION_ERROR(-20029, 'El nombre de la categoria es obligatorio.');
        END IF;

        IF NOT fn_nombre_valido(p_nombre) THEN
            RAISE_APPLICATION_ERROR(-20030, 'El nombre de la categoria tiene caracteres no permitidos.');
        END IF;

        SELECT COUNT(*)
          INTO v_count
          FROM categorias
         WHERE UPPER(TRIM(nombre)) = UPPER(TRIM(p_nombre));

        IF v_count > 0 THEN
            RAISE_APPLICATION_ERROR(-20031, 'Ya existe una categoria con ese nombre.');
        END IF;

        INSERT INTO categorias(nombre)
        VALUES (TRIM(p_nombre));

    EXCEPTION
        WHEN OTHERS THEN
            RAISE_APPLICATION_ERROR(-20032, 'Error al insertar categoria: ' || SQLERRM);
    END sp_insertar_categoria;

    PROCEDURE sp_actualizar_categoria(
        p_id_categoria IN NUMBER,
        p_nombre IN VARCHAR2
    )
    IS
        v_count NUMBER;
    BEGIN
        IF fn_existe_categoria(p_id_categoria) = 0 THEN
            RAISE_APPLICATION_ERROR(-20033, 'La categoria indicada no existe.');
        END IF;

        IF fn_texto_vacio(p_nombre) THEN
            RAISE_APPLICATION_ERROR(-20034, 'El nombre de la categoria es obligatorio.');
        END IF;

        IF NOT fn_nombre_valido(p_nombre) THEN
            RAISE_APPLICATION_ERROR(-20035, 'El nombre de la categoria tiene caracteres no permitidos.');
        END IF;

        SELECT COUNT(*)
          INTO v_count
          FROM categorias
         WHERE UPPER(TRIM(nombre)) = UPPER(TRIM(p_nombre))
           AND id_categoria <> p_id_categoria;

        IF v_count > 0 THEN
            RAISE_APPLICATION_ERROR(-20036, 'Ya existe otra categoria con ese nombre.');
        END IF;

        UPDATE categorias
           SET nombre = TRIM(p_nombre)
         WHERE id_categoria = p_id_categoria;

    EXCEPTION
        WHEN OTHERS THEN
            RAISE_APPLICATION_ERROR(-20037, 'Error al actualizar categoria: ' || SQLERRM);
    END sp_actualizar_categoria;

    PROCEDURE sp_eliminar_categoria(
        p_id_categoria IN NUMBER
    )
    IS
    BEGIN
        IF fn_existe_categoria(p_id_categoria) = 0 THEN
            RAISE_APPLICATION_ERROR(-20038, 'La categoria indicada no existe.');
        END IF;

        DELETE FROM categorias
         WHERE id_categoria = p_id_categoria;

    EXCEPTION
        WHEN OTHERS THEN
            RAISE_APPLICATION_ERROR(-20039, 'Error al eliminar categoria: ' || SQLERRM);
    END sp_eliminar_categoria;

    PROCEDURE sp_obtener_categoria(
        p_id_categoria IN NUMBER,
        p_resultado OUT t_cursor
    )
    IS
    BEGIN
        IF fn_existe_categoria(p_id_categoria) = 0 THEN
            RAISE_APPLICATION_ERROR(-20040, 'La categoria indicada no existe.');
        END IF;

        OPEN p_resultado FOR
            SELECT id_categoria, nombre
              FROM categorias
             WHERE id_categoria = p_id_categoria;

    EXCEPTION
        WHEN OTHERS THEN
            RAISE_APPLICATION_ERROR(-20041, 'Error al consultar categoria: ' || SQLERRM);
    END sp_obtener_categoria;

    PROCEDURE sp_listar_categorias(
        p_resultado OUT t_cursor
    )
    IS
    BEGIN
        OPEN p_resultado FOR
            SELECT id_categoria, nombre
              FROM categorias
             ORDER BY id_categoria;

    EXCEPTION
        WHEN OTHERS THEN
            RAISE_APPLICATION_ERROR(-20042, 'Error al listar categorias: ' || SQLERRM);
    END sp_listar_categorias;

END pkg_ref_catalogos;
/
SHOW ERRORS;

-- -------------------- end PACKAGE BODY ------------------

/*
PACKAGE: PKG_CLIENTES

Paquete para administrar la tabla clientes
*/
CREATE OR REPLACE PACKAGE M_HAMILTON_STORE.pkg_clientes AS

/* Cursor para devolver consultas */
    TYPE t_cursor IS REF CURSOR;

/*
Funcion para verificar si un cliente existe
*/
    FUNCTION fn_existe_cliente(
        p_id_cliente IN NUMBER
    ) RETURN NUMBER;

/*
CRUD DE CLIENTES
*/
    PROCEDURE sp_insertar_cliente(
        p_nombre             IN VARCHAR2,
        p_apellido           IN VARCHAR2,
        p_email              IN VARCHAR2,
        p_estados_id_estado  IN NUMBER
    );

    PROCEDURE sp_actualizar_cliente(
        p_id_cliente         IN NUMBER,
        p_nombre             IN VARCHAR2,
        p_apellido           IN VARCHAR2,
        p_email              IN VARCHAR2,
        p_estados_id_estado  IN NUMBER
    );

    PROCEDURE sp_eliminar_cliente(
        p_id_cliente IN NUMBER
    );

    PROCEDURE sp_obtener_cliente(
        p_id_cliente IN NUMBER,
        p_resultado  OUT t_cursor
    );

    PROCEDURE sp_listar_clientes(
        p_resultado OUT t_cursor
    );

END pkg_clientes;
/
SHOW ERRORS;

-- ---------------- END CRUDS CLITENTES ------------------


/*
PACKAGE BODY: PKG_CLIENTES
*/
CREATE OR REPLACE PACKAGE BODY M_HAMILTON_STORE.pkg_clientes AS

/*
Funcion para validar si un texto viene vacio
*/
    FUNCTION fn_texto_vacio(
        p_texto IN VARCHAR2
    ) RETURN BOOLEAN
    IS
    BEGIN
        RETURN p_texto IS NULL OR TRIM(p_texto) IS NULL;
    END fn_texto_vacio;

/*
Funcion para validar nombres
Solo letras y espacios
*/
    FUNCTION fn_nombre_valido(
        p_texto IN VARCHAR2
    ) RETURN BOOLEAN
    IS
    BEGIN
        RETURN REGEXP_LIKE(
            TRIM(p_texto),
            '^[A-Za-z ]+$'
        );
    END fn_nombre_valido;

/*
Funcion para validar email
*/
    FUNCTION fn_email_valido(
        p_email IN VARCHAR2
    ) RETURN BOOLEAN
    IS
    BEGIN
        RETURN REGEXP_LIKE(
            TRIM(p_email),
            '^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,}$'
        );
    END fn_email_valido;

/*
Funcion publica para saber si existe el cliente
*/
    FUNCTION fn_existe_cliente(
        p_id_cliente IN NUMBER
    ) RETURN NUMBER
    IS
        v_count NUMBER;
    BEGIN
        SELECT COUNT(*)
        INTO v_count
        FROM clientes
        WHERE id_cliente = p_id_cliente;

        RETURN v_count;
    END fn_existe_cliente;

/*
INSERTAR CLIENTE
*/
    PROCEDURE sp_insertar_cliente(
        p_nombre             IN VARCHAR2,
        p_apellido           IN VARCHAR2,
        p_email              IN VARCHAR2,
        p_estados_id_estado  IN NUMBER
    )
    IS
        v_count NUMBER;
    BEGIN
        IF fn_texto_vacio(p_nombre) THEN
            RAISE_APPLICATION_ERROR(-20101, 'El nombre es obligatorio.');
        END IF;

        IF fn_texto_vacio(p_apellido) THEN
            RAISE_APPLICATION_ERROR(-20102, 'El apellido es obligatorio.');
        END IF;

        IF fn_texto_vacio(p_email) THEN
            RAISE_APPLICATION_ERROR(-20103, 'El email es obligatorio.');
        END IF;

        IF NOT fn_nombre_valido(p_nombre) THEN
            RAISE_APPLICATION_ERROR(-20104, 'El nombre tiene caracteres no permitidos.');
        END IF;

        IF NOT fn_nombre_valido(p_apellido) THEN
            RAISE_APPLICATION_ERROR(-20105, 'El apellido tiene caracteres no permitidos.');
        END IF;

        IF NOT fn_email_valido(p_email) THEN
            RAISE_APPLICATION_ERROR(-20106, 'El email no tiene formato valido.');
        END IF;

        SELECT COUNT(*)
        INTO v_count
        FROM estados
        WHERE id_estado = p_estados_id_estado;

        IF v_count = 0 THEN
            RAISE_APPLICATION_ERROR(-20107, 'El estado indicado no existe.');
        END IF;

        SELECT COUNT(*)
        INTO v_count
        FROM clientes
        WHERE UPPER(TRIM(email)) = UPPER(TRIM(p_email));

        IF v_count > 0 THEN
            RAISE_APPLICATION_ERROR(-20108, 'Ya existe un cliente con ese email.');
        END IF;

        INSERT INTO clientes (
            nombre,
            apellido,
            email,
            estados_id_estado
        )
        VALUES (
            TRIM(p_nombre),
            TRIM(p_apellido),
            TRIM(p_email),
            p_estados_id_estado
        );

    EXCEPTION
        WHEN OTHERS THEN
            RAISE_APPLICATION_ERROR(-20109, 'Error al insertar cliente: ' || SQLERRM);
    END sp_insertar_cliente;

/*
ACTUALIZAR CLIENTE
*/
    PROCEDURE sp_actualizar_cliente(
        p_id_cliente         IN NUMBER,
        p_nombre             IN VARCHAR2,
        p_apellido           IN VARCHAR2,
        p_email              IN VARCHAR2,
        p_estados_id_estado  IN NUMBER
    )
    IS
        v_count NUMBER;
    BEGIN
        IF fn_existe_cliente(p_id_cliente) = 0 THEN
            RAISE_APPLICATION_ERROR(-20110, 'El cliente indicado no existe.');
        END IF;

        IF fn_texto_vacio(p_nombre) THEN
            RAISE_APPLICATION_ERROR(-20111, 'El nombre es obligatorio.');
        END IF;

        IF fn_texto_vacio(p_apellido) THEN
            RAISE_APPLICATION_ERROR(-20112, 'El apellido es obligatorio.');
        END IF;

        IF fn_texto_vacio(p_email) THEN
            RAISE_APPLICATION_ERROR(-20113, 'El email es obligatorio.');
        END IF;

        IF NOT fn_nombre_valido(p_nombre) THEN
            RAISE_APPLICATION_ERROR(-20114, 'El nombre tiene caracteres no permitidos.');
        END IF;

        IF NOT fn_nombre_valido(p_apellido) THEN
            RAISE_APPLICATION_ERROR(-20115, 'El apellido tiene caracteres no permitidos.');
        END IF;

        IF NOT fn_email_valido(p_email) THEN
            RAISE_APPLICATION_ERROR(-20116, 'El email no tiene formato valido.');
        END IF;

        SELECT COUNT(*)
        INTO v_count
        FROM estados
        WHERE id_estado = p_estados_id_estado;

        IF v_count = 0 THEN
            RAISE_APPLICATION_ERROR(-20117, 'El estado indicado no existe.');
        END IF;

        SELECT COUNT(*)
        INTO v_count
        FROM clientes
        WHERE UPPER(TRIM(email)) = UPPER(TRIM(p_email))
          AND id_cliente <> p_id_cliente;

        IF v_count > 0 THEN
            RAISE_APPLICATION_ERROR(-20118, 'Ya existe otro cliente con ese email.');
        END IF;

        UPDATE clientes
        SET nombre = TRIM(p_nombre),
            apellido = TRIM(p_apellido),
            email = TRIM(p_email),
            estados_id_estado = p_estados_id_estado
        WHERE id_cliente = p_id_cliente;

    EXCEPTION
        WHEN OTHERS THEN
            RAISE_APPLICATION_ERROR(-20119, 'Error al actualizar cliente: ' || SQLERRM);
    END sp_actualizar_cliente;

/*
ELIMINAR CLIENTE
*/
    PROCEDURE sp_eliminar_cliente(
        p_id_cliente IN NUMBER
    )
    IS
    BEGIN
        IF fn_existe_cliente(p_id_cliente) = 0 THEN
            RAISE_APPLICATION_ERROR(-20120, 'El cliente indicado no existe.');
        END IF;

        DELETE FROM clientes
        WHERE id_cliente = p_id_cliente;

    EXCEPTION
        WHEN OTHERS THEN
            RAISE_APPLICATION_ERROR(-20121, 'Error al eliminar cliente: ' || SQLERRM);
    END sp_eliminar_cliente;

/*
OBTENER CLIENTE POR ID
*/
    PROCEDURE sp_obtener_cliente(
        p_id_cliente IN NUMBER,
        p_resultado  OUT t_cursor
    )
    IS
    BEGIN
        IF fn_existe_cliente(p_id_cliente) = 0 THEN
            RAISE_APPLICATION_ERROR(-20122, 'El cliente indicado no existe.');
        END IF;

        OPEN p_resultado FOR
            SELECT c.id_cliente,
                   c.nombre,
                   c.apellido,
                   c.email,
                   c.fecha_ingreso,
                   c.estados_id_estado,
                   e.nombre AS estado
            FROM clientes c
            JOIN estados e
              ON c.estados_id_estado = e.id_estado
            WHERE c.id_cliente = p_id_cliente;

    EXCEPTION
        WHEN OTHERS THEN
            RAISE_APPLICATION_ERROR(-20123, 'Error al consultar cliente: ' || SQLERRM);
    END sp_obtener_cliente;

/*
LISTAR CLIENTES
*/
    PROCEDURE sp_listar_clientes(
        p_resultado OUT t_cursor
    )
    IS
    BEGIN
        OPEN p_resultado FOR
            SELECT c.id_cliente,
                   c.nombre,
                   c.apellido,
                   c.email,
                   c.fecha_ingreso,
                   c.estados_id_estado,
                   e.nombre AS estado
            FROM clientes c
            JOIN estados e
              ON c.estados_id_estado = e.id_estado
            ORDER BY c.id_cliente;

    EXCEPTION
        WHEN OTHERS THEN
            RAISE_APPLICATION_ERROR(-20124, 'Error al listar clientes: ' || SQLERRM);
    END sp_listar_clientes;

END pkg_clientes;
/
SHOW ERRORS;


-- -------------------- end PACKAGE BODY ------------------




/*
PACKAGE: PKG_PROVEEDORES

Paquete para administrar la tabla proveedores
*/

CREATE OR REPLACE PACKAGE M_HAMILTON_STORE.pkg_proveedores AS

/* Cursor para devolver consultas */
    TYPE t_cursor IS REF CURSOR;

/*
Funcion para verificar si un proveedor existe
*/
    FUNCTION fn_existe_proveedor(
        p_id_proveedor IN NUMBER
    ) RETURN NUMBER;

/*
CRUD DE PROVEEDORES
*/
    PROCEDURE sp_insertar_proveedor(
        p_nombre             IN VARCHAR2,
        p_cedula_juridica    IN VARCHAR2,
        p_pagina_web         IN VARCHAR2,
        p_estados_id_estado  IN NUMBER
    );

    PROCEDURE sp_actualizar_proveedor(
        p_id_proveedor       IN NUMBER,
        p_nombre             IN VARCHAR2,
        p_cedula_juridica    IN VARCHAR2,
        p_pagina_web         IN VARCHAR2,
        p_estados_id_estado  IN NUMBER
    );

    PROCEDURE sp_eliminar_proveedor(
        p_id_proveedor IN NUMBER
    );

    PROCEDURE sp_obtener_proveedor(
        p_id_proveedor IN NUMBER,
        p_resultado    OUT t_cursor
    );

    PROCEDURE sp_listar_proveedores(
        p_resultado OUT t_cursor
    );

END pkg_proveedores;
/
SHOW ERRORS;

-- ---------------- END CRUDS PROVEEDORES ------------------

/*
PACKAGE BODY: PKG_PROVEEDORES
*/
CREATE OR REPLACE PACKAGE BODY M_HAMILTON_STORE.pkg_proveedores AS

/*
Funcion para validar si un texto viene vacio
*/
    FUNCTION fn_texto_vacio(
        p_texto IN VARCHAR2
    ) RETURN BOOLEAN
    IS
    BEGIN
        RETURN p_texto IS NULL OR TRIM(p_texto) IS NULL;
    END fn_texto_vacio;

/*
Funcion para validar nombre
Solo letras, numeros, espacios y algunos signos basicos
*/
    FUNCTION fn_nombre_valido(
        p_texto IN VARCHAR2
    ) RETURN BOOLEAN
    IS
    BEGIN
        RETURN REGEXP_LIKE(
            TRIM(p_texto),
            '^[A-Za-z0-9 .,&()-]+$'
        );
    END fn_nombre_valido;

/*
Funcion para validar cedula juridica
Deja numeros y guiones
*/
    FUNCTION fn_cedula_juridica_valida(
        p_cedula IN VARCHAR2
    ) RETURN BOOLEAN
    IS
    BEGIN
        RETURN REGEXP_LIKE(
            TRIM(p_cedula),
            '^[0-9-]+$'
        );
    END fn_cedula_juridica_valida;

/*
Funcion para validar pagina web
*/
    FUNCTION fn_web_valida(
        p_web IN VARCHAR2
    ) RETURN BOOLEAN
    IS
    BEGIN
        IF p_web IS NULL OR TRIM(p_web) IS NULL THEN
            RETURN TRUE;
        END IF;

        RETURN REGEXP_LIKE(
            TRIM(p_web),
            '^(http://|https://)?(www\.)?[A-Za-z0-9.-]+\.[A-Za-z]{2,}(/.*)?$'
        );
    END fn_web_valida;

/*
Funcion publica para saber si existe el proveedor
*/
    FUNCTION fn_existe_proveedor(
        p_id_proveedor IN NUMBER
    ) RETURN NUMBER
    IS
        v_count NUMBER;
    BEGIN
        SELECT COUNT(*)
        INTO v_count
        FROM proveedores
        WHERE id_proveedor = p_id_proveedor;

        RETURN v_count;
    END fn_existe_proveedor;

/*
INSERTAR PROVEEDOR
*/
    PROCEDURE sp_insertar_proveedor(
        p_nombre             IN VARCHAR2,
        p_cedula_juridica    IN VARCHAR2,
        p_pagina_web         IN VARCHAR2,
        p_estados_id_estado  IN NUMBER
    )
    IS
        v_count NUMBER;
    BEGIN
        IF fn_texto_vacio(p_nombre) THEN
            RAISE_APPLICATION_ERROR(-20201, 'El nombre es obligatorio.');
        END IF;

        IF fn_texto_vacio(p_cedula_juridica) THEN
            RAISE_APPLICATION_ERROR(-20202, 'La cedula juridica es obligatoria.');
        END IF;

        IF NOT fn_nombre_valido(p_nombre) THEN
            RAISE_APPLICATION_ERROR(-20203, 'El nombre tiene caracteres no permitidos.');
        END IF;

        IF NOT fn_cedula_juridica_valida(p_cedula_juridica) THEN
            RAISE_APPLICATION_ERROR(-20204, 'La cedula juridica tiene formato no valido.');
        END IF;

        IF NOT fn_web_valida(p_pagina_web) THEN
            RAISE_APPLICATION_ERROR(-20205, 'La pagina web no tiene formato valido.');
        END IF;

        SELECT COUNT(*)
        INTO v_count
        FROM estados
        WHERE id_estado = p_estados_id_estado;

        IF v_count = 0 THEN
            RAISE_APPLICATION_ERROR(-20206, 'El estado indicado no existe.');
        END IF;

        SELECT COUNT(*)
        INTO v_count
        FROM proveedores
        WHERE TRIM(cedula_juridica) = TRIM(p_cedula_juridica);

        IF v_count > 0 THEN
            RAISE_APPLICATION_ERROR(-20207, 'Ya existe un proveedor con esa cedula juridica.');
        END IF;

        INSERT INTO proveedores (
            nombre,
            cedula_juridica,
            pagina_web,
            estados_id_estado
        )
        VALUES (
            TRIM(p_nombre),
            TRIM(p_cedula_juridica),
            TRIM(p_pagina_web),
            p_estados_id_estado
        );

    EXCEPTION
        WHEN OTHERS THEN
            RAISE_APPLICATION_ERROR(-20208, 'Error al insertar proveedor: ' || SQLERRM);
    END sp_insertar_proveedor;

/*
ACTUALIZAR PROVEEDOR
*/
    PROCEDURE sp_actualizar_proveedor(
        p_id_proveedor       IN NUMBER,
        p_nombre             IN VARCHAR2,
        p_cedula_juridica    IN VARCHAR2,
        p_pagina_web         IN VARCHAR2,
        p_estados_id_estado  IN NUMBER
    )
    IS
        v_count NUMBER;
    BEGIN
        IF fn_existe_proveedor(p_id_proveedor) = 0 THEN
            RAISE_APPLICATION_ERROR(-20209, 'El proveedor indicado no existe.');
        END IF;

        IF fn_texto_vacio(p_nombre) THEN
            RAISE_APPLICATION_ERROR(-20210, 'El nombre es obligatorio.');
        END IF;

        IF fn_texto_vacio(p_cedula_juridica) THEN
            RAISE_APPLICATION_ERROR(-20211, 'La cedula juridica es obligatoria.');
        END IF;

        IF NOT fn_nombre_valido(p_nombre) THEN
            RAISE_APPLICATION_ERROR(-20212, 'El nombre tiene caracteres no permitidos.');
        END IF;

        IF NOT fn_cedula_juridica_valida(p_cedula_juridica) THEN
            RAISE_APPLICATION_ERROR(-20213, 'La cedula juridica tiene formato no valido.');
        END IF;

        IF NOT fn_web_valida(p_pagina_web) THEN
            RAISE_APPLICATION_ERROR(-20214, 'La pagina web no tiene formato valido.');
        END IF;

        SELECT COUNT(*)
        INTO v_count
        FROM estados
        WHERE id_estado = p_estados_id_estado;

        IF v_count = 0 THEN
            RAISE_APPLICATION_ERROR(-20215, 'El estado indicado no existe.');
        END IF;

        SELECT COUNT(*)
        INTO v_count
        FROM proveedores
        WHERE TRIM(cedula_juridica) = TRIM(p_cedula_juridica)
          AND id_proveedor <> p_id_proveedor;

        IF v_count > 0 THEN
            RAISE_APPLICATION_ERROR(-20216, 'Ya existe otro proveedor con esa cedula juridica.');
        END IF;

        UPDATE proveedores
        SET nombre = TRIM(p_nombre),
            cedula_juridica = TRIM(p_cedula_juridica),
            pagina_web = TRIM(p_pagina_web),
            estados_id_estado = p_estados_id_estado
        WHERE id_proveedor = p_id_proveedor;

    EXCEPTION
        WHEN OTHERS THEN
            RAISE_APPLICATION_ERROR(-20217, 'Error al actualizar proveedor: ' || SQLERRM);
    END sp_actualizar_proveedor;

/*
ELIMINAR PROVEEDOR
*/
    PROCEDURE sp_eliminar_proveedor(
        p_id_proveedor IN NUMBER
    )
    IS
    BEGIN
        IF fn_existe_proveedor(p_id_proveedor) = 0 THEN
            RAISE_APPLICATION_ERROR(-20218, 'El proveedor indicado no existe.');
        END IF;

        DELETE FROM proveedores
        WHERE id_proveedor = p_id_proveedor;

    EXCEPTION
        WHEN OTHERS THEN
            RAISE_APPLICATION_ERROR(-20219, 'Error al eliminar proveedor: ' || SQLERRM);
    END sp_eliminar_proveedor;

/*
OBTENER PROVEEDOR POR ID
*/
    PROCEDURE sp_obtener_proveedor(
        p_id_proveedor IN NUMBER,
        p_resultado    OUT t_cursor
    )
    IS
    BEGIN
        IF fn_existe_proveedor(p_id_proveedor) = 0 THEN
            RAISE_APPLICATION_ERROR(-20220, 'El proveedor indicado no existe.');
        END IF;

        OPEN p_resultado FOR
            SELECT p.id_proveedor,
                   p.nombre,
                   p.cedula_juridica,
                   p.pagina_web,
                   p.estados_id_estado,
                   e.nombre AS estado
            FROM proveedores p
            JOIN estados e
              ON p.estados_id_estado = e.id_estado
            WHERE p.id_proveedor = p_id_proveedor;

    EXCEPTION
        WHEN OTHERS THEN
            RAISE_APPLICATION_ERROR(-20221, 'Error al consultar proveedor: ' || SQLERRM);
    END sp_obtener_proveedor;

/*
LISTAR PROVEEDORES
*/
    PROCEDURE sp_listar_proveedores(
        p_resultado OUT t_cursor
    )
    IS
    BEGIN
        OPEN p_resultado FOR
            SELECT p.id_proveedor,
                   p.nombre,
                   p.cedula_juridica,
                   p.pagina_web,
                   p.estados_id_estado,
                   e.nombre AS estado
            FROM proveedores p
            JOIN estados e
              ON p.estados_id_estado = e.id_estado
            ORDER BY p.id_proveedor;

    EXCEPTION
        WHEN OTHERS THEN
            RAISE_APPLICATION_ERROR(-20222, 'Error al listar proveedores: ' || SQLERRM);
    END sp_listar_proveedores;

END pkg_proveedores;
/
SHOW ERRORS;

-- -------------------- end PACKAGE BODY ------------------



/*
PACKAGE: PKG_EMPLEADOS

Paquete para administrar la tabla empleados
*/

CREATE OR REPLACE PACKAGE M_HAMILTON_STORE.pkg_empleados AS

/* Cursor para devolver consultas */
    TYPE t_cursor IS REF CURSOR;

/*
Funcion para verificar si un empleado existe
*/
    FUNCTION fn_existe_empleado(
        p_id_empleado IN NUMBER
    ) RETURN NUMBER;

/*
CRUD DE EMPLEADOS
*/
    PROCEDURE sp_insertar_empleado(
        p_nombre             IN VARCHAR2,
        p_apellido           IN VARCHAR2,
        p_puesto             IN VARCHAR2,
        p_email              IN VARCHAR2,
        p_estados_id_estado  IN NUMBER
    );

    PROCEDURE sp_actualizar_empleado(
        p_id_empleado        IN NUMBER,
        p_nombre             IN VARCHAR2,
        p_apellido           IN VARCHAR2,
        p_puesto             IN VARCHAR2,
        p_email              IN VARCHAR2,
        p_estados_id_estado  IN NUMBER
    );

    PROCEDURE sp_eliminar_empleado(
        p_id_empleado IN NUMBER
    );

    PROCEDURE sp_obtener_empleado(
        p_id_empleado IN NUMBER,
        p_resultado   OUT t_cursor
    );

    PROCEDURE sp_listar_empleados(
        p_resultado OUT t_cursor
    );

END pkg_empleados;
/
SHOW ERRORS;

-- ---------------- END CRUDS EMPLEADOS ------------------



/*
PACKAGE BODY: PKG_EMPLEADOS
*/
CREATE OR REPLACE PACKAGE BODY M_HAMILTON_STORE.pkg_empleados AS

/*
Funcion para validar si un texto viene vacio
*/
    FUNCTION fn_texto_vacio(
        p_texto IN VARCHAR2
    ) RETURN BOOLEAN
    IS
    BEGIN
        RETURN p_texto IS NULL OR TRIM(p_texto) IS NULL;
    END fn_texto_vacio;

/*
Funcion para validar nombres
Solo letras y espacios
*/
    FUNCTION fn_nombre_valido(
        p_texto IN VARCHAR2
    ) RETURN BOOLEAN
    IS
    BEGIN
        RETURN REGEXP_LIKE(
            TRIM(p_texto),
            '^[A-Za-z ]+$'
        );
    END fn_nombre_valido;

/*
Funcion para validar puesto
Deja letras, numeros, espacios y algunos signos
*/
    FUNCTION fn_puesto_valido(
        p_texto IN VARCHAR2
    ) RETURN BOOLEAN
    IS
    BEGIN
        RETURN REGEXP_LIKE(
            TRIM(p_texto),
            '^[A-Za-z0-9 .-]+$'
        );
    END fn_puesto_valido;

/*
Funcion para validar email
*/
    FUNCTION fn_email_valido(
        p_email IN VARCHAR2
    ) RETURN BOOLEAN
    IS
    BEGIN
        RETURN REGEXP_LIKE(
            TRIM(p_email),
            '^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,}$'
        );
    END fn_email_valido;

/*
Funcion publica para saber si existe el empleado
*/
    FUNCTION fn_existe_empleado(
        p_id_empleado IN NUMBER
    ) RETURN NUMBER
    IS
        v_count NUMBER;
    BEGIN
        SELECT COUNT(*)
        INTO v_count
        FROM empleados
        WHERE id_empleado = p_id_empleado;

        RETURN v_count;
    END fn_existe_empleado;

/*
INSERTAR EMPLEADO
*/
    PROCEDURE sp_insertar_empleado(
        p_nombre             IN VARCHAR2,
        p_apellido           IN VARCHAR2,
        p_puesto             IN VARCHAR2,
        p_email              IN VARCHAR2,
        p_estados_id_estado  IN NUMBER
    )
    IS
        v_count NUMBER;
    BEGIN
        IF fn_texto_vacio(p_nombre) THEN
            RAISE_APPLICATION_ERROR(-20301, 'El nombre es obligatorio.');
        END IF;

        IF fn_texto_vacio(p_apellido) THEN
            RAISE_APPLICATION_ERROR(-20302, 'El apellido es obligatorio.');
        END IF;

        IF fn_texto_vacio(p_puesto) THEN
            RAISE_APPLICATION_ERROR(-20303, 'El puesto es obligatorio.');
        END IF;

        IF fn_texto_vacio(p_email) THEN
            RAISE_APPLICATION_ERROR(-20304, 'El email es obligatorio.');
        END IF;

        IF NOT fn_nombre_valido(p_nombre) THEN
            RAISE_APPLICATION_ERROR(-20305, 'El nombre tiene caracteres no permitidos.');
        END IF;

        IF NOT fn_nombre_valido(p_apellido) THEN
            RAISE_APPLICATION_ERROR(-20306, 'El apellido tiene caracteres no permitidos.');
        END IF;

        IF NOT fn_puesto_valido(p_puesto) THEN
            RAISE_APPLICATION_ERROR(-20307, 'El puesto tiene caracteres no permitidos.');
        END IF;

        IF NOT fn_email_valido(p_email) THEN
            RAISE_APPLICATION_ERROR(-20308, 'El email no tiene formato valido.');
        END IF;

        SELECT COUNT(*)
        INTO v_count
        FROM estados
        WHERE id_estado = p_estados_id_estado;

        IF v_count = 0 THEN
            RAISE_APPLICATION_ERROR(-20309, 'El estado indicado no existe.');
        END IF;

        SELECT COUNT(*)
        INTO v_count
        FROM empleados
        WHERE UPPER(TRIM(email)) = UPPER(TRIM(p_email));

        IF v_count > 0 THEN
            RAISE_APPLICATION_ERROR(-20310, 'Ya existe un empleado con ese email.');
        END IF;

        INSERT INTO empleados (
            nombre,
            apellido,
            puesto,
            email,
            estados_id_estado
        )
        VALUES (
            TRIM(p_nombre),
            TRIM(p_apellido),
            TRIM(p_puesto),
            TRIM(p_email),
            p_estados_id_estado
        );

    EXCEPTION
        WHEN OTHERS THEN
            RAISE_APPLICATION_ERROR(-20311, 'Error al insertar empleado: ' || SQLERRM);
    END sp_insertar_empleado;

/*
ACTUALIZAR EMPLEADO
*/
    PROCEDURE sp_actualizar_empleado(
        p_id_empleado        IN NUMBER,
        p_nombre             IN VARCHAR2,
        p_apellido           IN VARCHAR2,
        p_puesto             IN VARCHAR2,
        p_email              IN VARCHAR2,
        p_estados_id_estado  IN NUMBER
    )
    IS
        v_count NUMBER;
    BEGIN
        IF fn_existe_empleado(p_id_empleado) = 0 THEN
            RAISE_APPLICATION_ERROR(-20312, 'El empleado indicado no existe.');
        END IF;

        IF fn_texto_vacio(p_nombre) THEN
            RAISE_APPLICATION_ERROR(-20313, 'El nombre es obligatorio.');
        END IF;

        IF fn_texto_vacio(p_apellido) THEN
            RAISE_APPLICATION_ERROR(-20314, 'El apellido es obligatorio.');
        END IF;

        IF fn_texto_vacio(p_puesto) THEN
            RAISE_APPLICATION_ERROR(-20315, 'El puesto es obligatorio.');
        END IF;

        IF fn_texto_vacio(p_email) THEN
            RAISE_APPLICATION_ERROR(-20316, 'El email es obligatorio.');
        END IF;

        IF NOT fn_nombre_valido(p_nombre) THEN
            RAISE_APPLICATION_ERROR(-20317, 'El nombre tiene caracteres no permitidos.');
        END IF;

        IF NOT fn_nombre_valido(p_apellido) THEN
            RAISE_APPLICATION_ERROR(-20318, 'El apellido tiene caracteres no permitidos.');
        END IF;

        IF NOT fn_puesto_valido(p_puesto) THEN
            RAISE_APPLICATION_ERROR(-20319, 'El puesto tiene caracteres no permitidos.');
        END IF;

        IF NOT fn_email_valido(p_email) THEN
            RAISE_APPLICATION_ERROR(-20320, 'El email no tiene formato valido.');
        END IF;

        SELECT COUNT(*)
        INTO v_count
        FROM estados
        WHERE id_estado = p_estados_id_estado;

        IF v_count = 0 THEN
            RAISE_APPLICATION_ERROR(-20321, 'El estado indicado no existe.');
        END IF;

        SELECT COUNT(*)
        INTO v_count
        FROM empleados
        WHERE UPPER(TRIM(email)) = UPPER(TRIM(p_email))
          AND id_empleado <> p_id_empleado;

        IF v_count > 0 THEN
            RAISE_APPLICATION_ERROR(-20322, 'Ya existe otro empleado con ese email.');
        END IF;

        UPDATE empleados
        SET nombre = TRIM(p_nombre),
            apellido = TRIM(p_apellido),
            puesto = TRIM(p_puesto),
            email = TRIM(p_email),
            estados_id_estado = p_estados_id_estado
        WHERE id_empleado = p_id_empleado;

    EXCEPTION
        WHEN OTHERS THEN
            RAISE_APPLICATION_ERROR(-20323, 'Error al actualizar empleado: ' || SQLERRM);
    END sp_actualizar_empleado;

/*
ELIMINAR EMPLEADO
*/
    PROCEDURE sp_eliminar_empleado(
        p_id_empleado IN NUMBER
    )
    IS
    BEGIN
        IF fn_existe_empleado(p_id_empleado) = 0 THEN
            RAISE_APPLICATION_ERROR(-20324, 'El empleado indicado no existe.');
        END IF;

        DELETE FROM empleados
        WHERE id_empleado = p_id_empleado;

    EXCEPTION
        WHEN OTHERS THEN
            RAISE_APPLICATION_ERROR(-20325, 'Error al eliminar empleado: ' || SQLERRM);
    END sp_eliminar_empleado;

/*
OBTENER EMPLEADO POR ID
*/
    PROCEDURE sp_obtener_empleado(
        p_id_empleado IN NUMBER,
        p_resultado   OUT t_cursor
    )
    IS
    BEGIN
        IF fn_existe_empleado(p_id_empleado) = 0 THEN
            RAISE_APPLICATION_ERROR(-20326, 'El empleado indicado no existe.');
        END IF;

        OPEN p_resultado FOR
            SELECT em.id_empleado,
                   em.nombre,
                   em.apellido,
                   em.puesto,
                   em.email,
                   em.fecha_ingreso,
                   em.estados_id_estado,
                   es.nombre AS estado
            FROM empleados em
            JOIN estados es
              ON em.estados_id_estado = es.id_estado
            WHERE em.id_empleado = p_id_empleado;

    EXCEPTION
        WHEN OTHERS THEN
            RAISE_APPLICATION_ERROR(-20327, 'Error al consultar empleado: ' || SQLERRM);
    END sp_obtener_empleado;

/*
LISTAR EMPLEADOS
*/
    PROCEDURE sp_listar_empleados(
        p_resultado OUT t_cursor
    )
    IS
    BEGIN
        OPEN p_resultado FOR
            SELECT em.id_empleado,
                   em.nombre,
                   em.apellido,
                   em.puesto,
                   em.email,
                   em.fecha_ingreso,
                   em.estados_id_estado,
                   es.nombre AS estado
            FROM empleados em
            JOIN estados es
              ON em.estados_id_estado = es.id_estado
            ORDER BY em.id_empleado;

    EXCEPTION
        WHEN OTHERS THEN
            RAISE_APPLICATION_ERROR(-20328, 'Error al listar empleados: ' || SQLERRM);
    END sp_listar_empleados;

END pkg_empleados;
/
SHOW ERRORS;


-- -------------------- END PACKAGE BODY ------------------


/*
PACKAGE: PKG_PRODUCTOS

Paquete para administrar la tabla productos
*/

CREATE OR REPLACE PACKAGE M_HAMILTON_STORE.pkg_productos AS

/* Cursor para devolver consultas */
    TYPE t_cursor IS REF CURSOR;

/*
Funcion para verificar si un producto existe
*/
    FUNCTION fn_existe_producto(
        p_id_producto IN NUMBER
    ) RETURN NUMBER;

/*
CRUD DE PRODUCTOS
*/
    PROCEDURE sp_insertar_producto(
        p_nombre                   IN VARCHAR2,
        p_precio_compra            IN NUMBER,
        p_precio_venta             IN NUMBER,
        p_cantidad                 IN NUMBER,
        p_categorias_id_categoria  IN NUMBER,
        p_estados_id_estado        IN NUMBER
    );

    PROCEDURE sp_actualizar_producto(
        p_id_producto              IN NUMBER,
        p_nombre                   IN VARCHAR2,
        p_precio_compra            IN NUMBER,
        p_precio_venta             IN NUMBER,
        p_cantidad                 IN NUMBER,
        p_categorias_id_categoria  IN NUMBER,
        p_estados_id_estado        IN NUMBER
    );

    PROCEDURE sp_eliminar_producto(
        p_id_producto IN NUMBER
    );

    PROCEDURE sp_obtener_producto(
        p_id_producto IN NUMBER,
        p_resultado   OUT t_cursor
    );

    PROCEDURE sp_listar_productos(
        p_resultado OUT t_cursor
    );

END pkg_productos;
/
SHOW ERRORS;

-- ---------------- END CRUDS PRODUCTOS ------------------


/*
PACKAGE BODY: PKG_PRODUCTOS
*/
CREATE OR REPLACE PACKAGE BODY M_HAMILTON_STORE.pkg_productos AS

/*
Funcion para validar si un texto viene vacio
*/
    FUNCTION fn_texto_vacio(
        p_texto IN VARCHAR2
    ) RETURN BOOLEAN
    IS
    BEGIN
        RETURN p_texto IS NULL OR TRIM(p_texto) IS NULL;
    END fn_texto_vacio;

/*
Funcion para validar nombre
Solo letras, numeros, espacios y algunos signos
*/
    FUNCTION fn_nombre_valido(
        p_texto IN VARCHAR2
    ) RETURN BOOLEAN
    IS
    BEGIN
        RETURN REGEXP_LIKE(
            TRIM(p_texto),
            '^[A-Za-z0-9 .,&()-]+$'
        );
    END fn_nombre_valido;

/*
Funcion publica para saber si existe el producto
*/
    FUNCTION fn_existe_producto(
        p_id_producto IN NUMBER
    ) RETURN NUMBER
    IS
        v_count NUMBER;
    BEGIN
        SELECT COUNT(*)
        INTO v_count
        FROM productos
        WHERE id_producto = p_id_producto;

        RETURN v_count;
    END fn_existe_producto;

/*
INSERTAR PRODUCTO
*/
    PROCEDURE sp_insertar_producto(
        p_nombre                   IN VARCHAR2,
        p_precio_compra            IN NUMBER,
        p_precio_venta             IN NUMBER,
        p_cantidad                 IN NUMBER,
        p_categorias_id_categoria  IN NUMBER,
        p_estados_id_estado        IN NUMBER
    )
    IS
        v_count NUMBER;
    BEGIN
        IF fn_texto_vacio(p_nombre) THEN
            RAISE_APPLICATION_ERROR(-20401, 'El nombre es obligatorio.');
        END IF;

        IF NOT fn_nombre_valido(p_nombre) THEN
            RAISE_APPLICATION_ERROR(-20402, 'El nombre tiene caracteres no permitidos.');
        END IF;

        IF p_precio_compra IS NULL THEN
            RAISE_APPLICATION_ERROR(-20403, 'El precio de compra es obligatorio.');
        END IF;

        IF p_precio_venta IS NULL THEN
            RAISE_APPLICATION_ERROR(-20404, 'El precio de venta es obligatorio.');
        END IF;

        IF p_cantidad IS NULL THEN
            RAISE_APPLICATION_ERROR(-20405, 'La cantidad es obligatoria.');
        END IF;

        IF p_precio_compra < 0 THEN
            RAISE_APPLICATION_ERROR(-20406, 'El precio de compra no puede ser negativo.');
        END IF;

        IF p_precio_venta < 0 THEN
            RAISE_APPLICATION_ERROR(-20407, 'El precio de venta no puede ser negativo.');
        END IF;

        IF p_cantidad < 0 THEN
            RAISE_APPLICATION_ERROR(-20408, 'La cantidad no puede ser negativa.');
        END IF;

        SELECT COUNT(*)
        INTO v_count
        FROM categorias
        WHERE id_categoria = p_categorias_id_categoria;

        IF v_count = 0 THEN
            RAISE_APPLICATION_ERROR(-20409, 'La categoria indicada no existe.');
        END IF;

        SELECT COUNT(*)
        INTO v_count
        FROM estados
        WHERE id_estado = p_estados_id_estado;

        IF v_count = 0 THEN
            RAISE_APPLICATION_ERROR(-20410, 'El estado indicado no existe.');
        END IF;

        INSERT INTO productos (
            nombre,
            precio_compra,
            precio_venta,
            cantidad,
            categorias_id_categoria,
            estados_id_estado
        )
        VALUES (
            TRIM(p_nombre),
            p_precio_compra,
            p_precio_venta,
            p_cantidad,
            p_categorias_id_categoria,
            p_estados_id_estado
        );

    EXCEPTION
        WHEN OTHERS THEN
            RAISE_APPLICATION_ERROR(-20411, 'Error al insertar producto: ' || SQLERRM);
    END sp_insertar_producto;

/*
ACTUALIZAR PRODUCTO
*/
    PROCEDURE sp_actualizar_producto(
        p_id_producto              IN NUMBER,
        p_nombre                   IN VARCHAR2,
        p_precio_compra            IN NUMBER,
        p_precio_venta             IN NUMBER,
        p_cantidad                 IN NUMBER,
        p_categorias_id_categoria  IN NUMBER,
        p_estados_id_estado        IN NUMBER
    )
    IS
        v_count NUMBER;
    BEGIN
        IF fn_existe_producto(p_id_producto) = 0 THEN
            RAISE_APPLICATION_ERROR(-20412, 'El producto indicado no existe.');
        END IF;

        IF fn_texto_vacio(p_nombre) THEN
            RAISE_APPLICATION_ERROR(-20413, 'El nombre es obligatorio.');
        END IF;

        IF NOT fn_nombre_valido(p_nombre) THEN
            RAISE_APPLICATION_ERROR(-20414, 'El nombre tiene caracteres no permitidos.');
        END IF;

        IF p_precio_compra IS NULL THEN
            RAISE_APPLICATION_ERROR(-20415, 'El precio de compra es obligatorio.');
        END IF;

        IF p_precio_venta IS NULL THEN
            RAISE_APPLICATION_ERROR(-20416, 'El precio de venta es obligatorio.');
        END IF;

        IF p_cantidad IS NULL THEN
            RAISE_APPLICATION_ERROR(-20417, 'La cantidad es obligatoria.');
        END IF;

        IF p_precio_compra < 0 THEN
            RAISE_APPLICATION_ERROR(-20418, 'El precio de compra no puede ser negativo.');
        END IF;

        IF p_precio_venta < 0 THEN
            RAISE_APPLICATION_ERROR(-20419, 'El precio de venta no puede ser negativo.');
        END IF;

        IF p_cantidad < 0 THEN
            RAISE_APPLICATION_ERROR(-20420, 'La cantidad no puede ser negativa.');
        END IF;

        SELECT COUNT(*)
        INTO v_count
        FROM categorias
        WHERE id_categoria = p_categorias_id_categoria;

        IF v_count = 0 THEN
            RAISE_APPLICATION_ERROR(-20421, 'La categoria indicada no existe.');
        END IF;

        SELECT COUNT(*)
        INTO v_count
        FROM estados
        WHERE id_estado = p_estados_id_estado;

        IF v_count = 0 THEN
            RAISE_APPLICATION_ERROR(-20422, 'El estado indicado no existe.');
        END IF;

        UPDATE productos
        SET nombre = TRIM(p_nombre),
            precio_compra = p_precio_compra,
            precio_venta = p_precio_venta,
            cantidad = p_cantidad,
            categorias_id_categoria = p_categorias_id_categoria,
            estados_id_estado = p_estados_id_estado
        WHERE id_producto = p_id_producto;

    EXCEPTION
        WHEN OTHERS THEN
            RAISE_APPLICATION_ERROR(-20423, 'Error al actualizar producto: ' || SQLERRM);
    END sp_actualizar_producto;

/*
ELIMINAR PRODUCTO
*/
    PROCEDURE sp_eliminar_producto(
        p_id_producto IN NUMBER
    )
    IS
    BEGIN
        IF fn_existe_producto(p_id_producto) = 0 THEN
            RAISE_APPLICATION_ERROR(-20424, 'El producto indicado no existe.');
        END IF;

        DELETE FROM productos
        WHERE id_producto = p_id_producto;

    EXCEPTION
        WHEN OTHERS THEN
            RAISE_APPLICATION_ERROR(-20425, 'Error al eliminar producto: ' || SQLERRM);
    END sp_eliminar_producto;

/*
OBTENER PRODUCTO POR ID
*/
    PROCEDURE sp_obtener_producto(
        p_id_producto IN NUMBER,
        p_resultado   OUT t_cursor
    )
    IS
    BEGIN
        IF fn_existe_producto(p_id_producto) = 0 THEN
            RAISE_APPLICATION_ERROR(-20426, 'El producto indicado no existe.');
        END IF;

        OPEN p_resultado FOR
            SELECT p.id_producto,
                   p.nombre,
                   p.precio_compra,
                   p.precio_venta,
                   p.cantidad,
                   p.categorias_id_categoria,
                   c.nombre AS categoria,
                   p.estados_id_estado,
                   e.nombre AS estado
            FROM productos p
            JOIN categorias c
              ON p.categorias_id_categoria = c.id_categoria
            JOIN estados e
              ON p.estados_id_estado = e.id_estado
            WHERE p.id_producto = p_id_producto;

    EXCEPTION
        WHEN OTHERS THEN
            RAISE_APPLICATION_ERROR(-20427, 'Error al consultar producto: ' || SQLERRM);
    END sp_obtener_producto;

/*
LISTAR PRODUCTOS
*/
    PROCEDURE sp_listar_productos(
        p_resultado OUT t_cursor
    )
    IS
    BEGIN
        OPEN p_resultado FOR
            SELECT p.id_producto,
                   p.nombre,
                   p.precio_compra,
                   p.precio_venta,
                   p.cantidad,
                   p.categorias_id_categoria,
                   c.nombre AS categoria,
                   p.estados_id_estado,
                   e.nombre AS estado
            FROM productos p
            JOIN categorias c
              ON p.categorias_id_categoria = c.id_categoria
            JOIN estados e
              ON p.estados_id_estado = e.id_estado
            ORDER BY p.id_producto;

    EXCEPTION
        WHEN OTHERS THEN
            RAISE_APPLICATION_ERROR(-20428, 'Error al listar productos: ' || SQLERRM);
    END sp_listar_productos;

END pkg_productos;
/
SHOW ERRORS;




-- -------------------- END PACKAGE BODY ------------------



/*
PACKAGE: PKG_CONTACTOS_PROVEEDORES

Paquete para administrar la tabla contactos_proveedores
*/

CREATE OR REPLACE PACKAGE M_HAMILTON_STORE.pkg_contactos_proveedores AS

/* Cursor para devolver consultas */
    TYPE t_cursor IS REF CURSOR;

/*
Funcion para verificar si un contacto existe
*/
    FUNCTION fn_existe_contacto_proveedor(
        p_id_contacto IN NUMBER
    ) RETURN NUMBER;

/*
CRUD DE CONTACTOS_PROVEEDORES
*/
    PROCEDURE sp_insertar_contacto_proveedor(
        p_nombre                   IN VARCHAR2,
        p_apellido                 IN VARCHAR2,
        p_email                    IN VARCHAR2,
        p_telefono                 IN VARCHAR2,
        p_proveedores_id_proveedor IN NUMBER
    );

    PROCEDURE sp_actualizar_contacto_proveedor(
        p_id_contacto              IN NUMBER,
        p_nombre                   IN VARCHAR2,
        p_apellido                 IN VARCHAR2,
        p_email                    IN VARCHAR2,
        p_telefono                 IN VARCHAR2,
        p_proveedores_id_proveedor IN NUMBER
    );

    PROCEDURE sp_eliminar_contacto_proveedor(
        p_id_contacto IN NUMBER
    );

    PROCEDURE sp_obtener_contacto_proveedor(
        p_id_contacto IN NUMBER,
        p_resultado   OUT t_cursor
    );

    PROCEDURE sp_listar_contactos_proveedores(
        p_resultado OUT t_cursor
    );

END pkg_contactos_proveedores;
/
SHOW ERRORS;

-- ---------------- END CRUDS CONTACTOS_PROVEEDORES ------------------



/*
PACKAGE BODY: PKG_CONTACTOS_PROVEEDORES
*/
CREATE OR REPLACE PACKAGE BODY M_HAMILTON_STORE.pkg_contactos_proveedores AS

/*
Funcion para validar si un texto viene vacio
*/
    FUNCTION fn_texto_vacio(
        p_texto IN VARCHAR2
    ) RETURN BOOLEAN
    IS
    BEGIN
        RETURN p_texto IS NULL OR TRIM(p_texto) IS NULL;
    END fn_texto_vacio;

/*
Funcion para validar nombres
Solo letras y espacios
*/
    FUNCTION fn_nombre_valido(
        p_texto IN VARCHAR2
    ) RETURN BOOLEAN
    IS
    BEGIN
        RETURN REGEXP_LIKE(
            TRIM(p_texto),
            '^[A-Za-z ]+$'
        );
    END fn_nombre_valido;

/*
Funcion para validar email
*/
    FUNCTION fn_email_valido(
        p_email IN VARCHAR2
    ) RETURN BOOLEAN
    IS
    BEGIN
        RETURN REGEXP_LIKE(
            TRIM(p_email),
            '^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,}$'
        );
    END fn_email_valido;

/*
Funcion para validar telefono
Deja numeros, espacios, guiones y parentesis
*/
    FUNCTION fn_telefono_valido(
        p_telefono IN VARCHAR2
    ) RETURN BOOLEAN
    IS
    BEGIN
        RETURN REGEXP_LIKE(
            TRIM(p_telefono),
            '^[0-9() -]+$'
        );
    END fn_telefono_valido;

/*
Funcion publica para saber si existe el contacto
*/
    FUNCTION fn_existe_contacto_proveedor(
        p_id_contacto IN NUMBER
    ) RETURN NUMBER
    IS
        v_count NUMBER;
    BEGIN
        SELECT COUNT(*)
        INTO v_count
        FROM contactos_proveedores
        WHERE id_contacto = p_id_contacto;

        RETURN v_count;
    END fn_existe_contacto_proveedor;

/*
INSERTAR CONTACTO_PROVEEDOR
*/
    PROCEDURE sp_insertar_contacto_proveedor(
        p_nombre                   IN VARCHAR2,
        p_apellido                 IN VARCHAR2,
        p_email                    IN VARCHAR2,
        p_telefono                 IN VARCHAR2,
        p_proveedores_id_proveedor IN NUMBER
    )
    IS
        v_count NUMBER;
    BEGIN
        IF fn_texto_vacio(p_nombre) THEN
            RAISE_APPLICATION_ERROR(-20501, 'El nombre es obligatorio.');
        END IF;

        IF fn_texto_vacio(p_apellido) THEN
            RAISE_APPLICATION_ERROR(-20502, 'El apellido es obligatorio.');
        END IF;

        IF fn_texto_vacio(p_email) THEN
            RAISE_APPLICATION_ERROR(-20503, 'El email es obligatorio.');
        END IF;

        IF fn_texto_vacio(p_telefono) THEN
            RAISE_APPLICATION_ERROR(-20504, 'El telefono es obligatorio.');
        END IF;

        IF NOT fn_nombre_valido(p_nombre) THEN
            RAISE_APPLICATION_ERROR(-20505, 'El nombre tiene caracteres no permitidos.');
        END IF;

        IF NOT fn_nombre_valido(p_apellido) THEN
            RAISE_APPLICATION_ERROR(-20506, 'El apellido tiene caracteres no permitidos.');
        END IF;

        IF NOT fn_email_valido(p_email) THEN
            RAISE_APPLICATION_ERROR(-20507, 'El email no tiene formato valido.');
        END IF;

        IF NOT fn_telefono_valido(p_telefono) THEN
            RAISE_APPLICATION_ERROR(-20508, 'El telefono no tiene formato valido.');
        END IF;

        SELECT COUNT(*)
        INTO v_count
        FROM proveedores
        WHERE id_proveedor = p_proveedores_id_proveedor;

        IF v_count = 0 THEN
            RAISE_APPLICATION_ERROR(-20509, 'El proveedor indicado no existe.');
        END IF;

        SELECT COUNT(*)
        INTO v_count
        FROM contactos_proveedores
        WHERE UPPER(TRIM(email)) = UPPER(TRIM(p_email));

        IF v_count > 0 THEN
            RAISE_APPLICATION_ERROR(-20510, 'Ya existe un contacto con ese email.');
        END IF;

        INSERT INTO contactos_proveedores (
            nombre,
            apellido,
            email,
            telefono,
            proveedores_id_proveedor
        )
        VALUES (
            TRIM(p_nombre),
            TRIM(p_apellido),
            TRIM(p_email),
            TRIM(p_telefono),
            p_proveedores_id_proveedor
        );

    EXCEPTION
        WHEN OTHERS THEN
            RAISE_APPLICATION_ERROR(-20511, 'Error al insertar contacto proveedor: ' || SQLERRM);
    END sp_insertar_contacto_proveedor;

/*
ACTUALIZAR CONTACTO_PROVEEDOR
*/
    PROCEDURE sp_actualizar_contacto_proveedor(
        p_id_contacto              IN NUMBER,
        p_nombre                   IN VARCHAR2,
        p_apellido                 IN VARCHAR2,
        p_email                    IN VARCHAR2,
        p_telefono                 IN VARCHAR2,
        p_proveedores_id_proveedor IN NUMBER
    )
    IS
        v_count NUMBER;
    BEGIN
        IF fn_existe_contacto_proveedor(p_id_contacto) = 0 THEN
            RAISE_APPLICATION_ERROR(-20512, 'El contacto indicado no existe.');
        END IF;

        IF fn_texto_vacio(p_nombre) THEN
            RAISE_APPLICATION_ERROR(-20513, 'El nombre es obligatorio.');
        END IF;

        IF fn_texto_vacio(p_apellido) THEN
            RAISE_APPLICATION_ERROR(-20514, 'El apellido es obligatorio.');
        END IF;

        IF fn_texto_vacio(p_email) THEN
            RAISE_APPLICATION_ERROR(-20515, 'El email es obligatorio.');
        END IF;

        IF fn_texto_vacio(p_telefono) THEN
            RAISE_APPLICATION_ERROR(-20516, 'El telefono es obligatorio.');
        END IF;

        IF NOT fn_nombre_valido(p_nombre) THEN
            RAISE_APPLICATION_ERROR(-20517, 'El nombre tiene caracteres no permitidos.');
        END IF;

        IF NOT fn_nombre_valido(p_apellido) THEN
            RAISE_APPLICATION_ERROR(-20518, 'El apellido tiene caracteres no permitidos.');
        END IF;

        IF NOT fn_email_valido(p_email) THEN
            RAISE_APPLICATION_ERROR(-20519, 'El email no tiene formato valido.');
        END IF;

        IF NOT fn_telefono_valido(p_telefono) THEN
            RAISE_APPLICATION_ERROR(-20520, 'El telefono no tiene formato valido.');
        END IF;

        SELECT COUNT(*)
        INTO v_count
        FROM proveedores
        WHERE id_proveedor = p_proveedores_id_proveedor;

        IF v_count = 0 THEN
            RAISE_APPLICATION_ERROR(-20521, 'El proveedor indicado no existe.');
        END IF;

        SELECT COUNT(*)
        INTO v_count
        FROM contactos_proveedores
        WHERE UPPER(TRIM(email)) = UPPER(TRIM(p_email))
          AND id_contacto <> p_id_contacto;

        IF v_count > 0 THEN
            RAISE_APPLICATION_ERROR(-20522, 'Ya existe otro contacto con ese email.');
        END IF;

        UPDATE contactos_proveedores
        SET nombre = TRIM(p_nombre),
            apellido = TRIM(p_apellido),
            email = TRIM(p_email),
            telefono = TRIM(p_telefono),
            proveedores_id_proveedor = p_proveedores_id_proveedor
        WHERE id_contacto = p_id_contacto;

    EXCEPTION
        WHEN OTHERS THEN
            RAISE_APPLICATION_ERROR(-20523, 'Error al actualizar contacto proveedor: ' || SQLERRM);
    END sp_actualizar_contacto_proveedor;

/*
ELIMINAR CONTACTO_PROVEEDOR
*/
    PROCEDURE sp_eliminar_contacto_proveedor(
        p_id_contacto IN NUMBER
    )
    IS
    BEGIN
        IF fn_existe_contacto_proveedor(p_id_contacto) = 0 THEN
            RAISE_APPLICATION_ERROR(-20524, 'El contacto indicado no existe.');
        END IF;

        DELETE FROM contactos_proveedores
        WHERE id_contacto = p_id_contacto;

    EXCEPTION
        WHEN OTHERS THEN
            RAISE_APPLICATION_ERROR(-20525, 'Error al eliminar contacto proveedor: ' || SQLERRM);
    END sp_eliminar_contacto_proveedor;

/*
OBTENER CONTACTO_PROVEEDOR POR ID
*/
    PROCEDURE sp_obtener_contacto_proveedor(
        p_id_contacto IN NUMBER,
        p_resultado   OUT t_cursor
    )
    IS
    BEGIN
        IF fn_existe_contacto_proveedor(p_id_contacto) = 0 THEN
            RAISE_APPLICATION_ERROR(-20526, 'El contacto indicado no existe.');
        END IF;

        OPEN p_resultado FOR
            SELECT cp.id_contacto,
                   cp.nombre,
                   cp.apellido,
                   cp.email,
                   cp.telefono,
                   cp.proveedores_id_proveedor,
                   p.nombre AS proveedor
            FROM contactos_proveedores cp
            JOIN proveedores p
              ON cp.proveedores_id_proveedor = p.id_proveedor
            WHERE cp.id_contacto = p_id_contacto;

    EXCEPTION
        WHEN OTHERS THEN
            RAISE_APPLICATION_ERROR(-20527, 'Error al consultar contacto proveedor: ' || SQLERRM);
    END sp_obtener_contacto_proveedor;

/*
LISTAR CONTACTOS_PROVEEDORES
*/
    PROCEDURE sp_listar_contactos_proveedores(
        p_resultado OUT t_cursor
    )
    IS
    BEGIN
        OPEN p_resultado FOR
            SELECT cp.id_contacto,
                   cp.nombre,
                   cp.apellido,
                   cp.email,
                   cp.telefono,
                   cp.proveedores_id_proveedor,
                   p.nombre AS proveedor
            FROM contactos_proveedores cp
            JOIN proveedores p
              ON cp.proveedores_id_proveedor = p.id_proveedor
            ORDER BY cp.id_contacto;

    EXCEPTION
        WHEN OTHERS THEN
            RAISE_APPLICATION_ERROR(-20528, 'Error al listar contactos proveedores: ' || SQLERRM);
    END sp_listar_contactos_proveedores;

END pkg_contactos_proveedores;
/
SHOW ERRORS;


-- -------------------- END PACKAGE BODY ------------------


/*
PACKAGE: PKG_DIRECCIONES

Paquete para administrar la tabla direcciones
*/

CREATE OR REPLACE PACKAGE M_HAMILTON_STORE.pkg_direcciones AS

/* Cursor para devolver consultas */
    TYPE t_cursor IS REF CURSOR;

/*
Funcion para verificar si una direccion existe
*/
    FUNCTION fn_existe_direccion(
        p_id_direccion IN NUMBER
    ) RETURN NUMBER;

/*
CRUD DE DIRECCIONES
*/
    PROCEDURE sp_insertar_direccion(
        p_otras_senas              IN VARCHAR2,
        p_provincias_id_provincia  IN NUMBER,
        p_cantones_id_canton       IN NUMBER,
        p_distritos_id_distrito    IN NUMBER,
        p_clientes_id_cliente      IN NUMBER,
        p_proveedores_id_proveedor IN NUMBER
    );

    PROCEDURE sp_actualizar_direccion(
        p_id_direccion             IN NUMBER,
        p_otras_senas              IN VARCHAR2,
        p_provincias_id_provincia  IN NUMBER,
        p_cantones_id_canton       IN NUMBER,
        p_distritos_id_distrito    IN NUMBER,
        p_clientes_id_cliente      IN NUMBER,
        p_proveedores_id_proveedor IN NUMBER
    );

    PROCEDURE sp_eliminar_direccion(
        p_id_direccion IN NUMBER
    );

    PROCEDURE sp_obtener_direccion(
        p_id_direccion IN NUMBER,
        p_resultado    OUT t_cursor
    );

    PROCEDURE sp_listar_direcciones(
        p_resultado OUT t_cursor
    );

END pkg_direcciones;
/
SHOW ERRORS;

-- ---------------- END CRUDS DIRECCIONES ------------------


/*
PACKAGE BODY: PKG_DIRECCIONES
*/
CREATE OR REPLACE PACKAGE BODY M_HAMILTON_STORE.pkg_direcciones AS

/*
Funcion para validar si un texto viene vacio
*/
    FUNCTION fn_texto_vacio(
        p_texto IN VARCHAR2
    ) RETURN BOOLEAN
    IS
    BEGIN
        RETURN p_texto IS NULL OR TRIM(p_texto) IS NULL;
    END fn_texto_vacio;

/*
Funcion publica para saber si existe la direccion
*/
    FUNCTION fn_existe_direccion(
        p_id_direccion IN NUMBER
    ) RETURN NUMBER
    IS
        v_count NUMBER;
    BEGIN
        SELECT COUNT(*)
        INTO v_count
        FROM direcciones
        WHERE id_direccion = p_id_direccion;

        RETURN v_count;
    END fn_existe_direccion;

/*
INSERTAR DIRECCION
*/
    PROCEDURE sp_insertar_direccion(
        p_otras_senas              IN VARCHAR2,
        p_provincias_id_provincia  IN NUMBER,
        p_cantones_id_canton       IN NUMBER,
        p_distritos_id_distrito    IN NUMBER,
        p_clientes_id_cliente      IN NUMBER,
        p_proveedores_id_proveedor IN NUMBER
    )
    IS
        v_count NUMBER;
    BEGIN
        IF p_provincias_id_provincia IS NULL THEN
            RAISE_APPLICATION_ERROR(-20601, 'La provincia es obligatoria.');
        END IF;

        IF p_cantones_id_canton IS NULL THEN
            RAISE_APPLICATION_ERROR(-20602, 'El canton es obligatorio.');
        END IF;

        IF p_distritos_id_distrito IS NULL THEN
            RAISE_APPLICATION_ERROR(-20603, 'El distrito es obligatorio.');
        END IF;

        SELECT COUNT(*)
        INTO v_count
        FROM provincias
        WHERE id_provincia = p_provincias_id_provincia;

        IF v_count = 0 THEN
            RAISE_APPLICATION_ERROR(-20604, 'La provincia indicada no existe.');
        END IF;

        SELECT COUNT(*)
        INTO v_count
        FROM cantones
        WHERE id_canton = p_cantones_id_canton;

        IF v_count = 0 THEN
            RAISE_APPLICATION_ERROR(-20605, 'El canton indicado no existe.');
        END IF;

        SELECT COUNT(*)
        INTO v_count
        FROM distritos
        WHERE id_distrito = p_distritos_id_distrito;

        IF v_count = 0 THEN
            RAISE_APPLICATION_ERROR(-20606, 'El distrito indicado no existe.');
        END IF;

        IF p_clientes_id_cliente IS NULL AND p_proveedores_id_proveedor IS NULL THEN
            RAISE_APPLICATION_ERROR(-20607, 'Debe indicar un cliente o un proveedor.');
        END IF;

        IF p_clientes_id_cliente IS NOT NULL AND p_proveedores_id_proveedor IS NOT NULL THEN
            RAISE_APPLICATION_ERROR(-20608, 'No se puede asignar la direccion a cliente y proveedor al mismo tiempo.');
        END IF;

        IF p_clientes_id_cliente IS NOT NULL THEN
            SELECT COUNT(*)
            INTO v_count
            FROM clientes
            WHERE id_cliente = p_clientes_id_cliente;

            IF v_count = 0 THEN
                RAISE_APPLICATION_ERROR(-20609, 'El cliente indicado no existe.');
            END IF;
        END IF;

        IF p_proveedores_id_proveedor IS NOT NULL THEN
            SELECT COUNT(*)
            INTO v_count
            FROM proveedores
            WHERE id_proveedor = p_proveedores_id_proveedor;

            IF v_count = 0 THEN
                RAISE_APPLICATION_ERROR(-20610, 'El proveedor indicado no existe.');
            END IF;
        END IF;

        INSERT INTO direcciones (
            otras_senas,
            provincias_id_provincia,
            cantones_id_canton,
            distritos_id_distrito,
            clientes_id_cliente,
            proveedores_id_proveedor
        )
        VALUES (
            TRIM(p_otras_senas),
            p_provincias_id_provincia,
            p_cantones_id_canton,
            p_distritos_id_distrito,
            p_clientes_id_cliente,
            p_proveedores_id_proveedor
        );

    EXCEPTION
        WHEN OTHERS THEN
            RAISE_APPLICATION_ERROR(-20611, 'Error al insertar direccion: ' || SQLERRM);
    END sp_insertar_direccion;

/*
ACTUALIZAR DIRECCION
*/
    PROCEDURE sp_actualizar_direccion(
        p_id_direccion             IN NUMBER,
        p_otras_senas              IN VARCHAR2,
        p_provincias_id_provincia  IN NUMBER,
        p_cantones_id_canton       IN NUMBER,
        p_distritos_id_distrito    IN NUMBER,
        p_clientes_id_cliente      IN NUMBER,
        p_proveedores_id_proveedor IN NUMBER
    )
    IS
        v_count NUMBER;
    BEGIN
        IF fn_existe_direccion(p_id_direccion) = 0 THEN
            RAISE_APPLICATION_ERROR(-20612, 'La direccion indicada no existe.');
        END IF;

        IF p_provincias_id_provincia IS NULL THEN
            RAISE_APPLICATION_ERROR(-20613, 'La provincia es obligatoria.');
        END IF;

        IF p_cantones_id_canton IS NULL THEN
            RAISE_APPLICATION_ERROR(-20614, 'El canton es obligatorio.');
        END IF;

        IF p_distritos_id_distrito IS NULL THEN
            RAISE_APPLICATION_ERROR(-20615, 'El distrito es obligatorio.');
        END IF;

        SELECT COUNT(*)
        INTO v_count
        FROM provincias
        WHERE id_provincia = p_provincias_id_provincia;

        IF v_count = 0 THEN
            RAISE_APPLICATION_ERROR(-20616, 'La provincia indicada no existe.');
        END IF;

        SELECT COUNT(*)
        INTO v_count
        FROM cantones
        WHERE id_canton = p_cantones_id_canton;

        IF v_count = 0 THEN
            RAISE_APPLICATION_ERROR(-20617, 'El canton indicado no existe.');
        END IF;

        SELECT COUNT(*)
        INTO v_count
        FROM distritos
        WHERE id_distrito = p_distritos_id_distrito;

        IF v_count = 0 THEN
            RAISE_APPLICATION_ERROR(-20618, 'El distrito indicado no existe.');
        END IF;

        IF p_clientes_id_cliente IS NULL AND p_proveedores_id_proveedor IS NULL THEN
            RAISE_APPLICATION_ERROR(-20619, 'Debe indicar un cliente o un proveedor.');
        END IF;

        IF p_clientes_id_cliente IS NOT NULL AND p_proveedores_id_proveedor IS NOT NULL THEN
            RAISE_APPLICATION_ERROR(-20620, 'No se puede asignar la direccion a cliente y proveedor al mismo tiempo.');
        END IF;

        IF p_clientes_id_cliente IS NOT NULL THEN
            SELECT COUNT(*)
            INTO v_count
            FROM clientes
            WHERE id_cliente = p_clientes_id_cliente;

            IF v_count = 0 THEN
                RAISE_APPLICATION_ERROR(-20621, 'El cliente indicado no existe.');
            END IF;
        END IF;

        IF p_proveedores_id_proveedor IS NOT NULL THEN
            SELECT COUNT(*)
            INTO v_count
            FROM proveedores
            WHERE id_proveedor = p_proveedores_id_proveedor;

            IF v_count = 0 THEN
                RAISE_APPLICATION_ERROR(-20622, 'El proveedor indicado no existe.');
            END IF;
        END IF;

        UPDATE direcciones
        SET otras_senas = TRIM(p_otras_senas),
            provincias_id_provincia = p_provincias_id_provincia,
            cantones_id_canton = p_cantones_id_canton,
            distritos_id_distrito = p_distritos_id_distrito,
            clientes_id_cliente = p_clientes_id_cliente,
            proveedores_id_proveedor = p_proveedores_id_proveedor
        WHERE id_direccion = p_id_direccion;

    EXCEPTION
        WHEN OTHERS THEN
            RAISE_APPLICATION_ERROR(-20623, 'Error al actualizar direccion: ' || SQLERRM);
    END sp_actualizar_direccion;

/*
ELIMINAR DIRECCION
*/
    PROCEDURE sp_eliminar_direccion(
        p_id_direccion IN NUMBER
    )
    IS
    BEGIN
        IF fn_existe_direccion(p_id_direccion) = 0 THEN
            RAISE_APPLICATION_ERROR(-20624, 'La direccion indicada no existe.');
        END IF;

        DELETE FROM direcciones
        WHERE id_direccion = p_id_direccion;

    EXCEPTION
        WHEN OTHERS THEN
            RAISE_APPLICATION_ERROR(-20625, 'Error al eliminar direccion: ' || SQLERRM);
    END sp_eliminar_direccion;

/*
OBTENER DIRECCION POR ID
*/
    PROCEDURE sp_obtener_direccion(
        p_id_direccion IN NUMBER,
        p_resultado    OUT t_cursor
    )
    IS
    BEGIN
        IF fn_existe_direccion(p_id_direccion) = 0 THEN
            RAISE_APPLICATION_ERROR(-20626, 'La direccion indicada no existe.');
        END IF;

        OPEN p_resultado FOR
            SELECT d.id_direccion,
                   d.otras_senas,
                   d.provincias_id_provincia,
                   p.nombre AS provincia,
                   d.cantones_id_canton,
                   c.nombre AS canton,
                   d.distritos_id_distrito,
                   di.nombre AS distrito,
                   d.clientes_id_cliente,
                   d.proveedores_id_proveedor
            FROM direcciones d
            JOIN provincias p
              ON d.provincias_id_provincia = p.id_provincia
            JOIN cantones c
              ON d.cantones_id_canton = c.id_canton
            JOIN distritos di
              ON d.distritos_id_distrito = di.id_distrito
            WHERE d.id_direccion = p_id_direccion;

    EXCEPTION
        WHEN OTHERS THEN
            RAISE_APPLICATION_ERROR(-20627, 'Error al consultar direccion: ' || SQLERRM);
    END sp_obtener_direccion;

/*
LISTAR DIRECCIONES
*/
    PROCEDURE sp_listar_direcciones(
        p_resultado OUT t_cursor
    )
    IS
    BEGIN
        OPEN p_resultado FOR
            SELECT d.id_direccion,
                   d.otras_senas,
                   d.provincias_id_provincia,
                   p.nombre AS provincia,
                   d.cantones_id_canton,
                   c.nombre AS canton,
                   d.distritos_id_distrito,
                   di.nombre AS distrito,
                   d.clientes_id_cliente,
                   d.proveedores_id_proveedor
            FROM direcciones d
            JOIN provincias p
              ON d.provincias_id_provincia = p.id_provincia
            JOIN cantones c
              ON d.cantones_id_canton = c.id_canton
            JOIN distritos di
              ON d.distritos_id_distrito = di.id_distrito
            ORDER BY d.id_direccion;

    EXCEPTION
        WHEN OTHERS THEN
            RAISE_APPLICATION_ERROR(-20628, 'Error al listar direcciones: ' || SQLERRM);
    END sp_listar_direcciones;

END pkg_direcciones;
/
SHOW ERRORS;


-- -------------------- END PACKAGE BODY ------------------



/*
PACKAGE: PKG_TELEFONOS_CLIENTES

Paquete para administrar la tabla telefonos_clientes
*/

CREATE OR REPLACE PACKAGE M_HAMILTON_STORE.pkg_telefonos_clientes AS

/* Cursor para devolver consultas */
    TYPE t_cursor IS REF CURSOR;

/*
Funcion para verificar si un telefono existe
*/
    FUNCTION fn_existe_telefono_cliente(
        p_id_telefono IN NUMBER
    ) RETURN NUMBER;

/*
CRUD DE TELEFONOS_CLIENTES
*/
    PROCEDURE sp_insertar_telefono_cliente(
        p_numero              IN VARCHAR2,
        p_clientes_id_cliente IN NUMBER
    );

    PROCEDURE sp_actualizar_telefono_cliente(
        p_id_telefono         IN NUMBER,
        p_numero              IN VARCHAR2,
        p_clientes_id_cliente IN NUMBER
    );

    PROCEDURE sp_eliminar_telefono_cliente(
        p_id_telefono IN NUMBER
    );

    PROCEDURE sp_obtener_telefono_cliente(
        p_id_telefono IN NUMBER,
        p_resultado   OUT t_cursor
    );

    PROCEDURE sp_listar_telefonos_clientes(
        p_resultado OUT t_cursor
    );

END pkg_telefonos_clientes;
/
SHOW ERRORS;

-- ---------------- END CRUDS TELEFONOS_CLIENTES ------------------

/*
PACKAGE BODY: PKG_TELEFONOS_CLIENTES
*/
CREATE OR REPLACE PACKAGE BODY M_HAMILTON_STORE.pkg_telefonos_clientes AS

/*
Funcion para validar si un texto viene vacio
*/
    FUNCTION fn_texto_vacio(
        p_texto IN VARCHAR2
    ) RETURN BOOLEAN
    IS
    BEGIN
        RETURN p_texto IS NULL OR TRIM(p_texto) IS NULL;
    END fn_texto_vacio;

/*
Funcion para validar telefono
Deja numeros, espacios, guiones y parentesis
*/
    FUNCTION fn_telefono_valido(
        p_numero IN VARCHAR2
    ) RETURN BOOLEAN
    IS
    BEGIN
        RETURN REGEXP_LIKE(
            TRIM(p_numero),
            '^[0-9() -]+$'
        );
    END fn_telefono_valido;

/*
Funcion publica para saber si existe el telefono
*/
    FUNCTION fn_existe_telefono_cliente(
        p_id_telefono IN NUMBER
    ) RETURN NUMBER
    IS
        v_count NUMBER;
    BEGIN
        SELECT COUNT(*)
        INTO v_count
        FROM telefonos_clientes
        WHERE id_telefono = p_id_telefono;

        RETURN v_count;
    END fn_existe_telefono_cliente;

/*
INSERTAR TELEFONO_CLIENTE
*/
    PROCEDURE sp_insertar_telefono_cliente(
        p_numero              IN VARCHAR2,
        p_clientes_id_cliente IN NUMBER
    )
    IS
        v_count NUMBER;
    BEGIN
        IF fn_texto_vacio(p_numero) THEN
            RAISE_APPLICATION_ERROR(-20701, 'El numero es obligatorio.');
        END IF;

        IF NOT fn_telefono_valido(p_numero) THEN
            RAISE_APPLICATION_ERROR(-20702, 'El numero tiene formato no valido.');
        END IF;

        SELECT COUNT(*)
        INTO v_count
        FROM clientes
        WHERE id_cliente = p_clientes_id_cliente;

        IF v_count = 0 THEN
            RAISE_APPLICATION_ERROR(-20703, 'El cliente indicado no existe.');
        END IF;

        INSERT INTO telefonos_clientes (
            numero,
            clientes_id_cliente
        )
        VALUES (
            TRIM(p_numero),
            p_clientes_id_cliente
        );

    EXCEPTION
        WHEN OTHERS THEN
            RAISE_APPLICATION_ERROR(-20704, 'Error al insertar telefono cliente: ' || SQLERRM);
    END sp_insertar_telefono_cliente;

/*
ACTUALIZAR TELEFONO_CLIENTE
*/
    PROCEDURE sp_actualizar_telefono_cliente(
        p_id_telefono         IN NUMBER,
        p_numero              IN VARCHAR2,
        p_clientes_id_cliente IN NUMBER
    )
    IS
        v_count NUMBER;
    BEGIN
        IF fn_existe_telefono_cliente(p_id_telefono) = 0 THEN
            RAISE_APPLICATION_ERROR(-20705, 'El telefono indicado no existe.');
        END IF;

        IF fn_texto_vacio(p_numero) THEN
            RAISE_APPLICATION_ERROR(-20706, 'El numero es obligatorio.');
        END IF;

        IF NOT fn_telefono_valido(p_numero) THEN
            RAISE_APPLICATION_ERROR(-20707, 'El numero tiene formato no valido.');
        END IF;

        SELECT COUNT(*)
        INTO v_count
        FROM clientes
        WHERE id_cliente = p_clientes_id_cliente;

        IF v_count = 0 THEN
            RAISE_APPLICATION_ERROR(-20708, 'El cliente indicado no existe.');
        END IF;

        UPDATE telefonos_clientes
        SET numero = TRIM(p_numero),
            clientes_id_cliente = p_clientes_id_cliente
        WHERE id_telefono = p_id_telefono;

    EXCEPTION
        WHEN OTHERS THEN
            RAISE_APPLICATION_ERROR(-20709, 'Error al actualizar telefono cliente: ' || SQLERRM);
    END sp_actualizar_telefono_cliente;

/*
ELIMINAR TELEFONO_CLIENTE
*/
    PROCEDURE sp_eliminar_telefono_cliente(
        p_id_telefono IN NUMBER
    )
    IS
    BEGIN
        IF fn_existe_telefono_cliente(p_id_telefono) = 0 THEN
            RAISE_APPLICATION_ERROR(-20710, 'El telefono indicado no existe.');
        END IF;

        DELETE FROM telefonos_clientes
        WHERE id_telefono = p_id_telefono;

    EXCEPTION
        WHEN OTHERS THEN
            RAISE_APPLICATION_ERROR(-20711, 'Error al eliminar telefono cliente: ' || SQLERRM);
    END sp_eliminar_telefono_cliente;

/*
OBTENER TELEFONO_CLIENTE POR ID
*/
    PROCEDURE sp_obtener_telefono_cliente(
        p_id_telefono IN NUMBER,
        p_resultado   OUT t_cursor
    )
    IS
    BEGIN
        IF fn_existe_telefono_cliente(p_id_telefono) = 0 THEN
            RAISE_APPLICATION_ERROR(-20712, 'El telefono indicado no existe.');
        END IF;

        OPEN p_resultado FOR
            SELECT tc.id_telefono,
                   tc.numero,
                   tc.clientes_id_cliente,
                   c.nombre,
                   c.apellido
            FROM telefonos_clientes tc
            JOIN clientes c
              ON tc.clientes_id_cliente = c.id_cliente
            WHERE tc.id_telefono = p_id_telefono;

    EXCEPTION
        WHEN OTHERS THEN
            RAISE_APPLICATION_ERROR(-20713, 'Error al consultar telefono cliente: ' || SQLERRM);
    END sp_obtener_telefono_cliente;

/*
LISTAR TELEFONOS_CLIENTES
*/
    PROCEDURE sp_listar_telefonos_clientes(
        p_resultado OUT t_cursor
    )
    IS
    BEGIN
        OPEN p_resultado FOR
            SELECT tc.id_telefono,
                   tc.numero,
                   tc.clientes_id_cliente,
                   c.nombre,
                   c.apellido
            FROM telefonos_clientes tc
            JOIN clientes c
              ON tc.clientes_id_cliente = c.id_cliente
            ORDER BY tc.id_telefono;

    EXCEPTION
        WHEN OTHERS THEN
            RAISE_APPLICATION_ERROR(-20714, 'Error al listar telefonos clientes: ' || SQLERRM);
    END sp_listar_telefonos_clientes;

END pkg_telefonos_clientes;
/
SHOW ERRORS;



-- -------------------- END PACKAGE BODY ------------------


/*
PACKAGE: PKG_TELEFONOS_CONT_PROVEEDORES

Paquete para administrar la tabla telefonos_cont_proveedores
*/

CREATE OR REPLACE PACKAGE M_HAMILTON_STORE.pkg_telefonos_cont_proveedores AS

/* Cursor para devolver consultas */
    TYPE t_cursor IS REF CURSOR;

/*
Funcion para verificar si un telefono existe
*/
    FUNCTION fn_existe_telefono_cont_proveedor(
        p_id_telefono IN NUMBER
    ) RETURN NUMBER;

/*
CRUD DE TELEFONOS_CONT_PROVEEDORES
*/
    PROCEDURE sp_insertar_telefono_cont_proveedor(
        p_numero                             IN VARCHAR2,
        p_contactos_proveedores_id_contacto  IN NUMBER
    );

    PROCEDURE sp_actualizar_telefono_cont_proveedor(
        p_id_telefono                        IN NUMBER,
        p_numero                             IN VARCHAR2,
        p_contactos_proveedores_id_contacto  IN NUMBER
    );

    PROCEDURE sp_eliminar_telefono_cont_proveedor(
        p_id_telefono IN NUMBER
    );

    PROCEDURE sp_obtener_telefono_cont_proveedor(
        p_id_telefono IN NUMBER,
        p_resultado   OUT t_cursor
    );

    PROCEDURE sp_listar_telefonos_cont_proveedores(
        p_resultado OUT t_cursor
    );

END pkg_telefonos_cont_proveedores;
/
SHOW ERRORS;

-- ---------------- END CRUDS TELEFONOS_CONT_PROVEEDORES ------------------


/*
PACKAGE BODY: PKG_TELEFONOS_CONT_PROVEEDORES
*/
CREATE OR REPLACE PACKAGE BODY M_HAMILTON_STORE.pkg_telefonos_cont_proveedores AS

/*
Funcion para validar si un texto viene vacio
*/
    FUNCTION fn_texto_vacio(
        p_texto IN VARCHAR2
    ) RETURN BOOLEAN
    IS
    BEGIN
        RETURN p_texto IS NULL OR TRIM(p_texto) IS NULL;
    END fn_texto_vacio;

/*
Funcion para validar telefono
Deja numeros, espacios, guiones y parentesis
*/
    FUNCTION fn_telefono_valido(
        p_numero IN VARCHAR2
    ) RETURN BOOLEAN
    IS
    BEGIN
        RETURN REGEXP_LIKE(
            TRIM(p_numero),
            '^[0-9() -]+$'
        );
    END fn_telefono_valido;

/*
Funcion publica para saber si existe el telefono
*/
    FUNCTION fn_existe_telefono_cont_proveedor(
        p_id_telefono IN NUMBER
    ) RETURN NUMBER
    IS
        v_count NUMBER;
    BEGIN
        SELECT COUNT(*)
        INTO v_count
        FROM telefonos_cont_proveedores
        WHERE id_telefono = p_id_telefono;

        RETURN v_count;
    END fn_existe_telefono_cont_proveedor;

/*
INSERTAR TELEFONO_CONT_PROVEEDOR
*/
    PROCEDURE sp_insertar_telefono_cont_proveedor(
        p_numero                             IN VARCHAR2,
        p_contactos_proveedores_id_contacto  IN NUMBER
    )
    IS
        v_count NUMBER;
    BEGIN
        IF fn_texto_vacio(p_numero) THEN
            RAISE_APPLICATION_ERROR(-20801, 'El numero es obligatorio.');
        END IF;

        IF NOT fn_telefono_valido(p_numero) THEN
            RAISE_APPLICATION_ERROR(-20802, 'El numero tiene formato no valido.');
        END IF;

        SELECT COUNT(*)
        INTO v_count
        FROM contactos_proveedores
        WHERE id_contacto = p_contactos_proveedores_id_contacto;

        IF v_count = 0 THEN
            RAISE_APPLICATION_ERROR(-20803, 'El contacto indicado no existe.');
        END IF;

        INSERT INTO telefonos_cont_proveedores (
            numero,
            contactos_proveedores_id_contacto
        )
        VALUES (
            TRIM(p_numero),
            p_contactos_proveedores_id_contacto
        );

    EXCEPTION
        WHEN OTHERS THEN
            RAISE_APPLICATION_ERROR(-20804, 'Error al insertar telefono contacto proveedor: ' || SQLERRM);
    END sp_insertar_telefono_cont_proveedor;

/*
ACTUALIZAR TELEFONO_CONT_PROVEEDOR
*/
    PROCEDURE sp_actualizar_telefono_cont_proveedor(
        p_id_telefono                        IN NUMBER,
        p_numero                             IN VARCHAR2,
        p_contactos_proveedores_id_contacto  IN NUMBER
    )
    IS
        v_count NUMBER;
    BEGIN
        IF fn_existe_telefono_cont_proveedor(p_id_telefono) = 0 THEN
            RAISE_APPLICATION_ERROR(-20805, 'El telefono indicado no existe.');
        END IF;

        IF fn_texto_vacio(p_numero) THEN
            RAISE_APPLICATION_ERROR(-20806, 'El numero es obligatorio.');
        END IF;

        IF NOT fn_telefono_valido(p_numero) THEN
            RAISE_APPLICATION_ERROR(-20807, 'El numero tiene formato no valido.');
        END IF;

        SELECT COUNT(*)
        INTO v_count
        FROM contactos_proveedores
        WHERE id_contacto = p_contactos_proveedores_id_contacto;

        IF v_count = 0 THEN
            RAISE_APPLICATION_ERROR(-20808, 'El contacto indicado no existe.');
        END IF;

        UPDATE telefonos_cont_proveedores
        SET numero = TRIM(p_numero),
            contactos_proveedores_id_contacto = p_contactos_proveedores_id_contacto
        WHERE id_telefono = p_id_telefono;

    EXCEPTION
        WHEN OTHERS THEN
            RAISE_APPLICATION_ERROR(-20809, 'Error al actualizar telefono contacto proveedor: ' || SQLERRM);
    END sp_actualizar_telefono_cont_proveedor;

/*
ELIMINAR TELEFONO_CONT_PROVEEDOR
*/
    PROCEDURE sp_eliminar_telefono_cont_proveedor(
        p_id_telefono IN NUMBER
    )
    IS
    BEGIN
        IF fn_existe_telefono_cont_proveedor(p_id_telefono) = 0 THEN
            RAISE_APPLICATION_ERROR(-20810, 'El telefono indicado no existe.');
        END IF;

        DELETE FROM telefonos_cont_proveedores
        WHERE id_telefono = p_id_telefono;

    EXCEPTION
        WHEN OTHERS THEN
            RAISE_APPLICATION_ERROR(-20811, 'Error al eliminar telefono contacto proveedor: ' || SQLERRM);
    END sp_eliminar_telefono_cont_proveedor;

/*
OBTENER TELEFONO_CONT_PROVEEDOR POR ID
*/
    PROCEDURE sp_obtener_telefono_cont_proveedor(
        p_id_telefono IN NUMBER,
        p_resultado   OUT t_cursor
    )
    IS
    BEGIN
        IF fn_existe_telefono_cont_proveedor(p_id_telefono) = 0 THEN
            RAISE_APPLICATION_ERROR(-20812, 'El telefono indicado no existe.');
        END IF;

        OPEN p_resultado FOR
            SELECT tcp.id_telefono,
                   tcp.numero,
                   tcp.contactos_proveedores_id_contacto,
                   cp.nombre,
                   cp.apellido,
                   cp.email
            FROM telefonos_cont_proveedores tcp
            JOIN contactos_proveedores cp
              ON tcp.contactos_proveedores_id_contacto = cp.id_contacto
            WHERE tcp.id_telefono = p_id_telefono;

    EXCEPTION
        WHEN OTHERS THEN
            RAISE_APPLICATION_ERROR(-20813, 'Error al consultar telefono contacto proveedor: ' || SQLERRM);
    END sp_obtener_telefono_cont_proveedor;

/*
LISTAR TELEFONOS_CONT_PROVEEDORES
*/
    PROCEDURE sp_listar_telefonos_cont_proveedores(
        p_resultado OUT t_cursor
    )
    IS
    BEGIN
        OPEN p_resultado FOR
            SELECT tcp.id_telefono,
                   tcp.numero,
                   tcp.contactos_proveedores_id_contacto,
                   cp.nombre,
                   cp.apellido,
                   cp.email
            FROM telefonos_cont_proveedores tcp
            JOIN contactos_proveedores cp
              ON tcp.contactos_proveedores_id_contacto = cp.id_contacto
            ORDER BY tcp.id_telefono;

    EXCEPTION
        WHEN OTHERS THEN
            RAISE_APPLICATION_ERROR(-20814, 'Error al listar telefonos contactos proveedores: ' || SQLERRM);
    END sp_listar_telefonos_cont_proveedores;

END pkg_telefonos_cont_proveedores;
/
SHOW ERRORS;



-- -------------------- END PACKAGE BODY ------------------


/*
PACKAGE: PKG_ENCABEZADOS_COMPRAS

Paquete para administrar la tabla encabezados_compras
*/

CREATE OR REPLACE PACKAGE M_HAMILTON_STORE.pkg_encabezados_compras AS

/* Cursor para devolver consultas */
    TYPE t_cursor IS REF CURSOR;

/*
Funcion para verificar si una compra existe
*/
    FUNCTION fn_existe_encabezado_compra(
        p_id_compra IN NUMBER
    ) RETURN NUMBER;

/*
CRUD DE ENCABEZADOS_COMPRAS
*/
    PROCEDURE sp_insertar_encabezado_compra(
        p_fecha_compra             IN DATE,
        p_total_compra             IN NUMBER,
        p_proveedores_id_proveedor IN NUMBER,
        p_empleados_id_empleado    IN NUMBER
    );

    PROCEDURE sp_actualizar_encabezado_compra(
        p_id_compra                IN NUMBER,
        p_fecha_compra             IN DATE,
        p_total_compra             IN NUMBER,
        p_proveedores_id_proveedor IN NUMBER,
        p_empleados_id_empleado    IN NUMBER
    );

    PROCEDURE sp_eliminar_encabezado_compra(
        p_id_compra IN NUMBER
    );

    PROCEDURE sp_obtener_encabezado_compra(
        p_id_compra IN NUMBER,
        p_resultado OUT t_cursor
    );

    PROCEDURE sp_listar_encabezados_compras(
        p_resultado OUT t_cursor
    );

END pkg_encabezados_compras;
/
SHOW ERRORS;

-- ---------------- END CRUDS ENCABEZADOS_COMPRAS ------------------

/*
PACKAGE BODY: PKG_ENCABEZADOS_COMPRAS
*/
CREATE OR REPLACE PACKAGE BODY M_HAMILTON_STORE.pkg_encabezados_compras AS

/*
Funcion publica para saber si existe una compra
*/
    FUNCTION fn_existe_encabezado_compra(
        p_id_compra IN NUMBER
    ) RETURN NUMBER
    IS
        v_count NUMBER;
    BEGIN
        SELECT COUNT(*)
        INTO v_count
        FROM encabezados_compras
        WHERE id_compra = p_id_compra;

        RETURN v_count;
    END fn_existe_encabezado_compra;

/*
INSERTAR ENCABEZADO_COMPRA
*/
    PROCEDURE sp_insertar_encabezado_compra(
        p_fecha_compra             IN DATE,
        p_total_compra             IN NUMBER,
        p_proveedores_id_proveedor IN NUMBER,
        p_empleados_id_empleado    IN NUMBER
    )
    IS
        v_count NUMBER;
    BEGIN
        IF p_fecha_compra IS NULL THEN
            RAISE_APPLICATION_ERROR(-20901, 'La fecha de compra es obligatoria.');
        END IF;

        IF p_total_compra IS NULL THEN
            RAISE_APPLICATION_ERROR(-20902, 'El total de compra es obligatorio.');
        END IF;

        IF p_total_compra < 0 THEN
            RAISE_APPLICATION_ERROR(-20903, 'El total de compra no puede ser negativo.');
        END IF;

        SELECT COUNT(*)
        INTO v_count
        FROM proveedores
        WHERE id_proveedor = p_proveedores_id_proveedor;

        IF v_count = 0 THEN
            RAISE_APPLICATION_ERROR(-20904, 'El proveedor indicado no existe.');
        END IF;

        SELECT COUNT(*)
        INTO v_count
        FROM empleados
        WHERE id_empleado = p_empleados_id_empleado;

        IF v_count = 0 THEN
            RAISE_APPLICATION_ERROR(-20905, 'El empleado indicado no existe.');
        END IF;

        INSERT INTO encabezados_compras (
            fecha_compra,
            total_compra,
            proveedores_id_proveedor,
            empleados_id_empleado
        )
        VALUES (
            p_fecha_compra,
            p_total_compra,
            p_proveedores_id_proveedor,
            p_empleados_id_empleado
        );

    EXCEPTION
        WHEN OTHERS THEN
            RAISE_APPLICATION_ERROR(-20906, 'Error al insertar encabezado compra: ' || SQLERRM);
    END sp_insertar_encabezado_compra;

/*
ACTUALIZAR ENCABEZADO_COMPRA
*/
    PROCEDURE sp_actualizar_encabezado_compra(
        p_id_compra                IN NUMBER,
        p_fecha_compra             IN DATE,
        p_total_compra             IN NUMBER,
        p_proveedores_id_proveedor IN NUMBER,
        p_empleados_id_empleado    IN NUMBER
    )
    IS
        v_count NUMBER;
    BEGIN
        IF fn_existe_encabezado_compra(p_id_compra) = 0 THEN
            RAISE_APPLICATION_ERROR(-20907, 'La compra indicada no existe.');
        END IF;

        IF p_fecha_compra IS NULL THEN
            RAISE_APPLICATION_ERROR(-20908, 'La fecha de compra es obligatoria.');
        END IF;

        IF p_total_compra IS NULL THEN
            RAISE_APPLICATION_ERROR(-20909, 'El total de compra es obligatorio.');
        END IF;

        IF p_total_compra < 0 THEN
            RAISE_APPLICATION_ERROR(-20910, 'El total de compra no puede ser negativo.');
        END IF;

        SELECT COUNT(*)
        INTO v_count
        FROM proveedores
        WHERE id_proveedor = p_proveedores_id_proveedor;

        IF v_count = 0 THEN
            RAISE_APPLICATION_ERROR(-20911, 'El proveedor indicado no existe.');
        END IF;

        SELECT COUNT(*)
        INTO v_count
        FROM empleados
        WHERE id_empleado = p_empleados_id_empleado;

        IF v_count = 0 THEN
            RAISE_APPLICATION_ERROR(-20912, 'El empleado indicado no existe.');
        END IF;

        UPDATE encabezados_compras
        SET fecha_compra = p_fecha_compra,
            total_compra = p_total_compra,
            proveedores_id_proveedor = p_proveedores_id_proveedor,
            empleados_id_empleado = p_empleados_id_empleado
        WHERE id_compra = p_id_compra;

    EXCEPTION
        WHEN OTHERS THEN
            RAISE_APPLICATION_ERROR(-20913, 'Error al actualizar encabezado compra: ' || SQLERRM);
    END sp_actualizar_encabezado_compra;

/*
ELIMINAR ENCABEZADO_COMPRA
*/
    PROCEDURE sp_eliminar_encabezado_compra(
        p_id_compra IN NUMBER
    )
    IS
    BEGIN
        IF fn_existe_encabezado_compra(p_id_compra) = 0 THEN
            RAISE_APPLICATION_ERROR(-20914, 'La compra indicada no existe.');
        END IF;

        DELETE FROM encabezados_compras
        WHERE id_compra = p_id_compra;

    EXCEPTION
        WHEN OTHERS THEN
            RAISE_APPLICATION_ERROR(-20915, 'Error al eliminar encabezado compra: ' || SQLERRM);
    END sp_eliminar_encabezado_compra;

/*
OBTENER ENCABEZADO_COMPRA POR ID
*/
    PROCEDURE sp_obtener_encabezado_compra(
        p_id_compra IN NUMBER,
        p_resultado OUT t_cursor
    )
    IS
    BEGIN
        IF fn_existe_encabezado_compra(p_id_compra) = 0 THEN
            RAISE_APPLICATION_ERROR(-20916, 'La compra indicada no existe.');
        END IF;

        OPEN p_resultado FOR
            SELECT ec.id_compra,
                   ec.fecha_compra,
                   ec.total_compra,
                   ec.proveedores_id_proveedor,
                   p.nombre AS proveedor,
                   ec.empleados_id_empleado,
                   e.nombre AS nombre_empleado,
                   e.apellido AS apellido_empleado
            FROM encabezados_compras ec
            JOIN proveedores p
              ON ec.proveedores_id_proveedor = p.id_proveedor
            JOIN empleados e
              ON ec.empleados_id_empleado = e.id_empleado
            WHERE ec.id_compra = p_id_compra;

    EXCEPTION
        WHEN OTHERS THEN
            RAISE_APPLICATION_ERROR(-20917, 'Error al consultar encabezado compra: ' || SQLERRM);
    END sp_obtener_encabezado_compra;

/*
LISTAR ENCABEZADOS_COMPRAS
*/
    PROCEDURE sp_listar_encabezados_compras(
        p_resultado OUT t_cursor
    )
    IS
    BEGIN
        OPEN p_resultado FOR
            SELECT ec.id_compra,
                   ec.fecha_compra,
                   ec.total_compra,
                   ec.proveedores_id_proveedor,
                   p.nombre AS proveedor,
                   ec.empleados_id_empleado,
                   e.nombre AS nombre_empleado,
                   e.apellido AS apellido_empleado
            FROM encabezados_compras ec
            JOIN proveedores p
              ON ec.proveedores_id_proveedor = p.id_proveedor
            JOIN empleados e
              ON ec.empleados_id_empleado = e.id_empleado
            ORDER BY ec.id_compra;

    EXCEPTION
        WHEN OTHERS THEN
            RAISE_APPLICATION_ERROR(-20918, 'Error al listar encabezados compras: ' || SQLERRM);
    END sp_listar_encabezados_compras;

END pkg_encabezados_compras;
/
SHOW ERRORS;



-- -------------------- END PACKAGE BODY ------------------


/*
PACKAGE: PKG_DETALLES_COMPRAS

Paquete para administrar la tabla detalles_compras
*/

CREATE OR REPLACE PACKAGE M_HAMILTON_STORE.pkg_detalles_compras AS

/* Cursor para devolver consultas */
    TYPE t_cursor IS REF CURSOR;

/*
Funcion para verificar si un detalle de compra existe
*/
    FUNCTION fn_existe_detalle_compra(
        p_id_detalle_compra IN NUMBER
    ) RETURN NUMBER;

/*
CRUD DE DETALLES_COMPRAS
*/
    PROCEDURE sp_insertar_detalle_compra(
        p_cantidad                      IN NUMBER,
        p_precio_unitario               IN NUMBER,
        p_encabezados_compras_id_compra IN NUMBER,
        p_productos_id_producto         IN NUMBER
    );

    PROCEDURE sp_actualizar_detalle_compra(
        p_id_detalle_compra             IN NUMBER,
        p_cantidad                      IN NUMBER,
        p_precio_unitario               IN NUMBER,
        p_encabezados_compras_id_compra IN NUMBER,
        p_productos_id_producto         IN NUMBER
    );

    PROCEDURE sp_eliminar_detalle_compra(
        p_id_detalle_compra IN NUMBER
    );

    PROCEDURE sp_obtener_detalle_compra(
        p_id_detalle_compra IN NUMBER,
        p_resultado         OUT t_cursor
    );

    PROCEDURE sp_listar_detalles_compras(
        p_resultado OUT t_cursor
    );

END pkg_detalles_compras;
/
SHOW ERRORS;

-- ---------------- END CRUDS DETALLES_COMPRAS ------------------


/*
PACKAGE BODY: PKG_DETALLES_COMPRAS
*/
CREATE OR REPLACE PACKAGE BODY M_HAMILTON_STORE.pkg_detalles_compras AS

/*
Funcion publica para saber si existe un detalle de compra
*/
    FUNCTION fn_existe_detalle_compra(
        p_id_detalle_compra IN NUMBER
    ) RETURN NUMBER
    IS
        v_count NUMBER;
    BEGIN
        SELECT COUNT(*)
        INTO v_count
        FROM detalles_compras
        WHERE id_detalle_compra = p_id_detalle_compra;

        RETURN v_count;
    END fn_existe_detalle_compra;

/*
INSERTAR DETALLE_COMPRA
*/
    PROCEDURE sp_insertar_detalle_compra(
        p_cantidad                      IN NUMBER,
        p_precio_unitario               IN NUMBER,
        p_encabezados_compras_id_compra IN NUMBER,
        p_productos_id_producto         IN NUMBER
    )
    IS
        v_count NUMBER;
    BEGIN
        IF p_cantidad IS NULL THEN
            RAISE_APPLICATION_ERROR(-21001, 'La cantidad es obligatoria.');
        END IF;

        IF p_precio_unitario IS NULL THEN
            RAISE_APPLICATION_ERROR(-21002, 'El precio unitario es obligatorio.');
        END IF;

        IF p_cantidad <= 0 THEN
            RAISE_APPLICATION_ERROR(-21003, 'La cantidad debe ser mayor que cero.');
        END IF;

        IF p_precio_unitario < 0 THEN
            RAISE_APPLICATION_ERROR(-21004, 'El precio unitario no puede ser negativo.');
        END IF;

        SELECT COUNT(*)
        INTO v_count
        FROM encabezados_compras
        WHERE id_compra = p_encabezados_compras_id_compra;

        IF v_count = 0 THEN
            RAISE_APPLICATION_ERROR(-21005, 'La compra indicada no existe.');
        END IF;

        SELECT COUNT(*)
        INTO v_count
        FROM productos
        WHERE id_producto = p_productos_id_producto;

        IF v_count = 0 THEN
            RAISE_APPLICATION_ERROR(-21006, 'El producto indicado no existe.');
        END IF;

        INSERT INTO detalles_compras (
            cantidad,
            precio_unitario,
            encabezados_compras_id_compra,
            productos_id_producto
        )
        VALUES (
            p_cantidad,
            p_precio_unitario,
            p_encabezados_compras_id_compra,
            p_productos_id_producto
        );

    EXCEPTION
        WHEN OTHERS THEN
            RAISE_APPLICATION_ERROR(-21007, 'Error al insertar detalle compra: ' || SQLERRM);
    END sp_insertar_detalle_compra;

/*
ACTUALIZAR DETALLE_COMPRA
*/
    PROCEDURE sp_actualizar_detalle_compra(
        p_id_detalle_compra             IN NUMBER,
        p_cantidad                      IN NUMBER,
        p_precio_unitario               IN NUMBER,
        p_encabezados_compras_id_compra IN NUMBER,
        p_productos_id_producto         IN NUMBER
    )
    IS
        v_count NUMBER;
    BEGIN
        IF fn_existe_detalle_compra(p_id_detalle_compra) = 0 THEN
            RAISE_APPLICATION_ERROR(-21008, 'El detalle de compra indicado no existe.');
        END IF;

        IF p_cantidad IS NULL THEN
            RAISE_APPLICATION_ERROR(-21009, 'La cantidad es obligatoria.');
        END IF;

        IF p_precio_unitario IS NULL THEN
            RAISE_APPLICATION_ERROR(-21010, 'El precio unitario es obligatorio.');
        END IF;

        IF p_cantidad <= 0 THEN
            RAISE_APPLICATION_ERROR(-21011, 'La cantidad debe ser mayor que cero.');
        END IF;

        IF p_precio_unitario < 0 THEN
            RAISE_APPLICATION_ERROR(-21012, 'El precio unitario no puede ser negativo.');
        END IF;

        SELECT COUNT(*)
        INTO v_count
        FROM encabezados_compras
        WHERE id_compra = p_encabezados_compras_id_compra;

        IF v_count = 0 THEN
            RAISE_APPLICATION_ERROR(-21013, 'La compra indicada no existe.');
        END IF;

        SELECT COUNT(*)
        INTO v_count
        FROM productos
        WHERE id_producto = p_productos_id_producto;

        IF v_count = 0 THEN
            RAISE_APPLICATION_ERROR(-21014, 'El producto indicado no existe.');
        END IF;

        UPDATE detalles_compras
        SET cantidad = p_cantidad,
            precio_unitario = p_precio_unitario,
            encabezados_compras_id_compra = p_encabezados_compras_id_compra,
            productos_id_producto = p_productos_id_producto
        WHERE id_detalle_compra = p_id_detalle_compra;

    EXCEPTION
        WHEN OTHERS THEN
            RAISE_APPLICATION_ERROR(-21015, 'Error al actualizar detalle compra: ' || SQLERRM);
    END sp_actualizar_detalle_compra;

/*
ELIMINAR DETALLE_COMPRA
*/
    PROCEDURE sp_eliminar_detalle_compra(
        p_id_detalle_compra IN NUMBER
    )
    IS
    BEGIN
        IF fn_existe_detalle_compra(p_id_detalle_compra) = 0 THEN
            RAISE_APPLICATION_ERROR(-21016, 'El detalle de compra indicado no existe.');
        END IF;

        DELETE FROM detalles_compras
        WHERE id_detalle_compra = p_id_detalle_compra;

    EXCEPTION
        WHEN OTHERS THEN
            RAISE_APPLICATION_ERROR(-21017, 'Error al eliminar detalle compra: ' || SQLERRM);
    END sp_eliminar_detalle_compra;

/*
OBTENER DETALLE_COMPRA POR ID
*/
    PROCEDURE sp_obtener_detalle_compra(
        p_id_detalle_compra IN NUMBER,
        p_resultado         OUT t_cursor
    )
    IS
    BEGIN
        IF fn_existe_detalle_compra(p_id_detalle_compra) = 0 THEN
            RAISE_APPLICATION_ERROR(-21018, 'El detalle de compra indicado no existe.');
        END IF;

        OPEN p_resultado FOR
            SELECT dc.id_detalle_compra,
                   dc.cantidad,
                   dc.precio_unitario,
                   dc.encabezados_compras_id_compra,
                   dc.productos_id_producto,
                   p.nombre AS producto
            FROM detalles_compras dc
            JOIN productos p
              ON dc.productos_id_producto = p.id_producto
            WHERE dc.id_detalle_compra = p_id_detalle_compra;

    EXCEPTION
        WHEN OTHERS THEN
            RAISE_APPLICATION_ERROR(-21019, 'Error al consultar detalle compra: ' || SQLERRM);
    END sp_obtener_detalle_compra;

/*
LISTAR DETALLES_COMPRAS
*/
    PROCEDURE sp_listar_detalles_compras(
        p_resultado OUT t_cursor
    )
    IS
    BEGIN
        OPEN p_resultado FOR
            SELECT dc.id_detalle_compra,
                   dc.cantidad,
                   dc.precio_unitario,
                   dc.encabezados_compras_id_compra,
                   dc.productos_id_producto,
                   p.nombre AS producto
            FROM detalles_compras dc
            JOIN productos p
              ON dc.productos_id_producto = p.id_producto
            ORDER BY dc.id_detalle_compra;

    EXCEPTION
        WHEN OTHERS THEN
            RAISE_APPLICATION_ERROR(-21020, 'Error al listar detalles compras: ' || SQLERRM);
    END sp_listar_detalles_compras;

END pkg_detalles_compras;
/
SHOW ERRORS;


-- -------------------- END PACKAGE BODY ------------------



/*
PACKAGE: PKG_ENCABEZADOS_VENTAS

Paquete para administrar la tabla encabezados_ventas
*/

CREATE OR REPLACE PACKAGE M_HAMILTON_STORE.pkg_encabezados_ventas AS

/* Cursor para devolver consultas */
    TYPE t_cursor IS REF CURSOR;

/*
Funcion para verificar si una venta existe
*/
    FUNCTION fn_existe_encabezado_venta(
        p_id_venta IN NUMBER
    ) RETURN NUMBER;

/*
CRUD DE ENCABEZADOS_VENTAS
*/
    PROCEDURE sp_insertar_encabezado_venta(
        p_fecha_venta           IN DATE,
        p_total_venta           IN NUMBER,
        p_clientes_id_cliente   IN NUMBER,
        p_empleados_id_empleado IN NUMBER
    );

    PROCEDURE sp_actualizar_encabezado_venta(
        p_id_venta              IN NUMBER,
        p_fecha_venta           IN DATE,
        p_total_venta           IN NUMBER,
        p_clientes_id_cliente   IN NUMBER,
        p_empleados_id_empleado IN NUMBER
    );

    PROCEDURE sp_eliminar_encabezado_venta(
        p_id_venta IN NUMBER
    );

    PROCEDURE sp_obtener_encabezado_venta(
        p_id_venta IN NUMBER,
        p_resultado OUT t_cursor
    );

    PROCEDURE sp_listar_encabezados_ventas(
        p_resultado OUT t_cursor
    );

END pkg_encabezados_ventas;
/
SHOW ERRORS;

-- ---------------- END CRUDS ENCABEZADOS_VENTAS ------------------


/*
PACKAGE BODY: PKG_ENCABEZADOS_VENTAS
*/
CREATE OR REPLACE PACKAGE BODY M_HAMILTON_STORE.pkg_encabezados_ventas AS

/*
Funcion publica para saber si existe una venta
*/
    FUNCTION fn_existe_encabezado_venta(
        p_id_venta IN NUMBER
    ) RETURN NUMBER
    IS
        v_count NUMBER;
    BEGIN
        SELECT COUNT(*)
        INTO v_count
        FROM encabezados_ventas
        WHERE id_venta = p_id_venta;

        RETURN v_count;
    END fn_existe_encabezado_venta;

/*
INSERTAR ENCABEZADO_VENTA
*/
    PROCEDURE sp_insertar_encabezado_venta(
        p_fecha_venta           IN DATE,
        p_total_venta           IN NUMBER,
        p_clientes_id_cliente   IN NUMBER,
        p_empleados_id_empleado IN NUMBER
    )
    IS
        v_count NUMBER;
    BEGIN
        IF p_fecha_venta IS NULL THEN
            RAISE_APPLICATION_ERROR(-21101, 'La fecha de venta es obligatoria.');
        END IF;

        IF p_total_venta IS NULL THEN
            RAISE_APPLICATION_ERROR(-21102, 'El total de venta es obligatorio.');
        END IF;

        IF p_total_venta < 0 THEN
            RAISE_APPLICATION_ERROR(-21103, 'El total de venta no puede ser negativo.');
        END IF;

        SELECT COUNT(*)
        INTO v_count
        FROM clientes
        WHERE id_cliente = p_clientes_id_cliente;

        IF v_count = 0 THEN
            RAISE_APPLICATION_ERROR(-21104, 'El cliente indicado no existe.');
        END IF;

        SELECT COUNT(*)
        INTO v_count
        FROM empleados
        WHERE id_empleado = p_empleados_id_empleado;

        IF v_count = 0 THEN
            RAISE_APPLICATION_ERROR(-21105, 'El empleado indicado no existe.');
        END IF;

        INSERT INTO encabezados_ventas (
            fecha_venta,
            total_venta,
            clientes_id_cliente,
            empleados_id_empleado
        )
        VALUES (
            p_fecha_venta,
            p_total_venta,
            p_clientes_id_cliente,
            p_empleados_id_empleado
        );

    EXCEPTION
        WHEN OTHERS THEN
            RAISE_APPLICATION_ERROR(-21106, 'Error al insertar encabezado venta: ' || SQLERRM);
    END sp_insertar_encabezado_venta;

/*
ACTUALIZAR ENCABEZADO_VENTA
*/
    PROCEDURE sp_actualizar_encabezado_venta(
        p_id_venta              IN NUMBER,
        p_fecha_venta           IN DATE,
        p_total_venta           IN NUMBER,
        p_clientes_id_cliente   IN NUMBER,
        p_empleados_id_empleado IN NUMBER
    )
    IS
        v_count NUMBER;
    BEGIN
        IF fn_existe_encabezado_venta(p_id_venta) = 0 THEN
            RAISE_APPLICATION_ERROR(-21107, 'La venta indicada no existe.');
        END IF;

        IF p_fecha_venta IS NULL THEN
            RAISE_APPLICATION_ERROR(-21108, 'La fecha de venta es obligatoria.');
        END IF;

        IF p_total_venta IS NULL THEN
            RAISE_APPLICATION_ERROR(-21109, 'El total de venta es obligatorio.');
        END IF;

        IF p_total_venta < 0 THEN
            RAISE_APPLICATION_ERROR(-21110, 'El total de venta no puede ser negativo.');
        END IF;

        SELECT COUNT(*)
        INTO v_count
        FROM clientes
        WHERE id_cliente = p_clientes_id_cliente;

        IF v_count = 0 THEN
            RAISE_APPLICATION_ERROR(-21111, 'El cliente indicado no existe.');
        END IF;

        SELECT COUNT(*)
        INTO v_count
        FROM empleados
        WHERE id_empleado = p_empleados_id_empleado;

        IF v_count = 0 THEN
            RAISE_APPLICATION_ERROR(-21112, 'El empleado indicado no existe.');
        END IF;

        UPDATE encabezados_ventas
        SET fecha_venta = p_fecha_venta,
            total_venta = p_total_venta,
            clientes_id_cliente = p_clientes_id_cliente,
            empleados_id_empleado = p_empleados_id_empleado
        WHERE id_venta = p_id_venta;

    EXCEPTION
        WHEN OTHERS THEN
            RAISE_APPLICATION_ERROR(-21113, 'Error al actualizar encabezado venta: ' || SQLERRM);
    END sp_actualizar_encabezado_venta;

/*
ELIMINAR ENCABEZADO_VENTA
*/
    PROCEDURE sp_eliminar_encabezado_venta(
        p_id_venta IN NUMBER
    )
    IS
    BEGIN
        IF fn_existe_encabezado_venta(p_id_venta) = 0 THEN
            RAISE_APPLICATION_ERROR(-21114, 'La venta indicada no existe.');
        END IF;

        DELETE FROM encabezados_ventas
        WHERE id_venta = p_id_venta;

    EXCEPTION
        WHEN OTHERS THEN
            RAISE_APPLICATION_ERROR(-21115, 'Error al eliminar encabezado venta: ' || SQLERRM);
    END sp_eliminar_encabezado_venta;

/*
OBTENER ENCABEZADO_VENTA POR ID
*/
    PROCEDURE sp_obtener_encabezado_venta(
        p_id_venta IN NUMBER,
        p_resultado OUT t_cursor
    )
    IS
    BEGIN
        IF fn_existe_encabezado_venta(p_id_venta) = 0 THEN
            RAISE_APPLICATION_ERROR(-21116, 'La venta indicada no existe.');
        END IF;

        OPEN p_resultado FOR
            SELECT ev.id_venta,
                   ev.fecha_venta,
                   ev.total_venta,
                   ev.clientes_id_cliente,
                   c.nombre AS nombre_cliente,
                   c.apellido AS apellido_cliente,
                   ev.empleados_id_empleado,
                   e.nombre AS nombre_empleado,
                   e.apellido AS apellido_empleado
            FROM encabezados_ventas ev
            JOIN clientes c
              ON ev.clientes_id_cliente = c.id_cliente
            JOIN empleados e
              ON ev.empleados_id_empleado = e.id_empleado
            WHERE ev.id_venta = p_id_venta;

    EXCEPTION
        WHEN OTHERS THEN
            RAISE_APPLICATION_ERROR(-21117, 'Error al consultar encabezado venta: ' || SQLERRM);
    END sp_obtener_encabezado_venta;

/*
LISTAR ENCABEZADOS_VENTAS
*/
    PROCEDURE sp_listar_encabezados_ventas(
        p_resultado OUT t_cursor
    )
    IS
    BEGIN
        OPEN p_resultado FOR
            SELECT ev.id_venta,
                   ev.fecha_venta,
                   ev.total_venta,
                   ev.clientes_id_cliente,
                   c.nombre AS nombre_cliente,
                   c.apellido AS apellido_cliente,
                   ev.empleados_id_empleado,
                   e.nombre AS nombre_empleado,
                   e.apellido AS apellido_empleado
            FROM encabezados_ventas ev
            JOIN clientes c
              ON ev.clientes_id_cliente = c.id_cliente
            JOIN empleados e
              ON ev.empleados_id_empleado = e.id_empleado
            ORDER BY ev.id_venta;

    EXCEPTION
        WHEN OTHERS THEN
            RAISE_APPLICATION_ERROR(-21118, 'Error al listar encabezados ventas: ' || SQLERRM);
    END sp_listar_encabezados_ventas;

END pkg_encabezados_ventas;
/
SHOW ERRORS;


-- -------------------- END PACKAGE BODY ------------------


/*
PACKAGE: PKG_DETALLES_VENTAS

Paquete para administrar la tabla detalles_ventas
*/

CREATE OR REPLACE PACKAGE M_HAMILTON_STORE.pkg_detalles_ventas AS

/* Cursor para devolver consultas */
    TYPE t_cursor IS REF CURSOR;

/*
Funcion para verificar si un detalle de venta existe
*/
    FUNCTION fn_existe_detalle_venta(
        p_id_detalle_venta IN NUMBER
    ) RETURN NUMBER;

/*
CRUD DE DETALLES_VENTAS
*/
    PROCEDURE sp_insertar_detalle_venta(
        p_cantidad                   IN NUMBER,
        p_precio_unitario            IN NUMBER,
        p_subtotal                   IN NUMBER,
        p_encabezados_ventas_id_venta IN NUMBER,
        p_productos_id_producto      IN NUMBER
    );

    PROCEDURE sp_actualizar_detalle_venta(
        p_id_detalle_venta            IN NUMBER,
        p_cantidad                    IN NUMBER,
        p_precio_unitario             IN NUMBER,
        p_subtotal                    IN NUMBER,
        p_encabezados_ventas_id_venta IN NUMBER,
        p_productos_id_producto       IN NUMBER
    );

    PROCEDURE sp_eliminar_detalle_venta(
        p_id_detalle_venta IN NUMBER
    );

    PROCEDURE sp_obtener_detalle_venta(
        p_id_detalle_venta IN NUMBER,
        p_resultado        OUT t_cursor
    );

    PROCEDURE sp_listar_detalles_ventas(
        p_resultado OUT t_cursor
    );

END pkg_detalles_ventas;
/
SHOW ERRORS;

-- ---------------- END CRUDS DETALLES_VENTAS ------------------


/*
PACKAGE BODY: PKG_DETALLES_VENTAS
*/
CREATE OR REPLACE PACKAGE BODY M_HAMILTON_STORE.pkg_detalles_ventas AS

/*
Funcion publica para saber si existe un detalle de venta
*/
    FUNCTION fn_existe_detalle_venta(
        p_id_detalle_venta IN NUMBER
    ) RETURN NUMBER
    IS
        v_count NUMBER;
    BEGIN
        SELECT COUNT(*)
        INTO v_count
        FROM detalles_ventas
        WHERE id_detalle_venta = p_id_detalle_venta;

        RETURN v_count;
    END fn_existe_detalle_venta;

/*
INSERTAR DETALLE_VENTA
*/
    PROCEDURE sp_insertar_detalle_venta(
        p_cantidad                    IN NUMBER,
        p_precio_unitario             IN NUMBER,
        p_subtotal                    IN NUMBER,
        p_encabezados_ventas_id_venta IN NUMBER,
        p_productos_id_producto       IN NUMBER
    )
    IS
        v_count NUMBER;
    BEGIN
        IF p_cantidad IS NULL THEN
            RAISE_APPLICATION_ERROR(-21201, 'La cantidad es obligatoria.');
        END IF;

        IF p_precio_unitario IS NULL THEN
            RAISE_APPLICATION_ERROR(-21202, 'El precio unitario es obligatorio.');
        END IF;

        IF p_subtotal IS NULL THEN
            RAISE_APPLICATION_ERROR(-21203, 'El subtotal es obligatorio.');
        END IF;

        IF p_cantidad <= 0 THEN
            RAISE_APPLICATION_ERROR(-21204, 'La cantidad debe ser mayor que cero.');
        END IF;

        IF p_precio_unitario < 0 THEN
            RAISE_APPLICATION_ERROR(-21205, 'El precio unitario no puede ser negativo.');
        END IF;

        IF p_subtotal < 0 THEN
            RAISE_APPLICATION_ERROR(-21206, 'El subtotal no puede ser negativo.');
        END IF;

        SELECT COUNT(*)
        INTO v_count
        FROM encabezados_ventas
        WHERE id_venta = p_encabezados_ventas_id_venta;

        IF v_count = 0 THEN
            RAISE_APPLICATION_ERROR(-21207, 'La venta indicada no existe.');
        END IF;

        SELECT COUNT(*)
        INTO v_count
        FROM productos
        WHERE id_producto = p_productos_id_producto;

        IF v_count = 0 THEN
            RAISE_APPLICATION_ERROR(-21208, 'El producto indicado no existe.');
        END IF;

        INSERT INTO detalles_ventas (
            cantidad,
            precio_unitario,
            subtotal,
            encabezados_ventas_id_venta,
            productos_id_producto
        )
        VALUES (
            p_cantidad,
            p_precio_unitario,
            p_subtotal,
            p_encabezados_ventas_id_venta,
            p_productos_id_producto
        );

    EXCEPTION
        WHEN OTHERS THEN
            RAISE_APPLICATION_ERROR(-21209, 'Error al insertar detalle venta: ' || SQLERRM);
    END sp_insertar_detalle_venta;

/*
ACTUALIZAR DETALLE_VENTA
*/
    PROCEDURE sp_actualizar_detalle_venta(
        p_id_detalle_venta            IN NUMBER,
        p_cantidad                    IN NUMBER,
        p_precio_unitario             IN NUMBER,
        p_subtotal                    IN NUMBER,
        p_encabezados_ventas_id_venta IN NUMBER,
        p_productos_id_producto       IN NUMBER
    )
    IS
        v_count NUMBER;
    BEGIN
        IF fn_existe_detalle_venta(p_id_detalle_venta) = 0 THEN
            RAISE_APPLICATION_ERROR(-21210, 'El detalle de venta indicado no existe.');
        END IF;

        IF p_cantidad IS NULL THEN
            RAISE_APPLICATION_ERROR(-21211, 'La cantidad es obligatoria.');
        END IF;

        IF p_precio_unitario IS NULL THEN
            RAISE_APPLICATION_ERROR(-21212, 'El precio unitario es obligatorio.');
        END IF;

        IF p_subtotal IS NULL THEN
            RAISE_APPLICATION_ERROR(-21213, 'El subtotal es obligatorio.');
        END IF;

        IF p_cantidad <= 0 THEN
            RAISE_APPLICATION_ERROR(-21214, 'La cantidad debe ser mayor que cero.');
        END IF;

        IF p_precio_unitario < 0 THEN
            RAISE_APPLICATION_ERROR(-21215, 'El precio unitario no puede ser negativo.');
        END IF;

        IF p_subtotal < 0 THEN
            RAISE_APPLICATION_ERROR(-21216, 'El subtotal no puede ser negativo.');
        END IF;

        SELECT COUNT(*)
        INTO v_count
        FROM encabezados_ventas
        WHERE id_venta = p_encabezados_ventas_id_venta;

        IF v_count = 0 THEN
            RAISE_APPLICATION_ERROR(-21217, 'La venta indicada no existe.');
        END IF;

        SELECT COUNT(*)
        INTO v_count
        FROM productos
        WHERE id_producto = p_productos_id_producto;

        IF v_count = 0 THEN
            RAISE_APPLICATION_ERROR(-21218, 'El producto indicado no existe.');
        END IF;

        UPDATE detalles_ventas
        SET cantidad = p_cantidad,
            precio_unitario = p_precio_unitario,
            subtotal = p_subtotal,
            encabezados_ventas_id_venta = p_encabezados_ventas_id_venta,
            productos_id_producto = p_productos_id_producto
        WHERE id_detalle_venta = p_id_detalle_venta;

    EXCEPTION
        WHEN OTHERS THEN
            RAISE_APPLICATION_ERROR(-21219, 'Error al actualizar detalle venta: ' || SQLERRM);
    END sp_actualizar_detalle_venta;

/*
ELIMINAR DETALLE_VENTA
*/
    PROCEDURE sp_eliminar_detalle_venta(
        p_id_detalle_venta IN NUMBER
    )
    IS
    BEGIN
        IF fn_existe_detalle_venta(p_id_detalle_venta) = 0 THEN
            RAISE_APPLICATION_ERROR(-21220, 'El detalle de venta indicado no existe.');
        END IF;

        DELETE FROM detalles_ventas
        WHERE id_detalle_venta = p_id_detalle_venta;

    EXCEPTION
        WHEN OTHERS THEN
            RAISE_APPLICATION_ERROR(-21221, 'Error al eliminar detalle venta: ' || SQLERRM);
    END sp_eliminar_detalle_venta;

/*
OBTENER DETALLE_VENTA POR ID
*/
    PROCEDURE sp_obtener_detalle_venta(
        p_id_detalle_venta IN NUMBER,
        p_resultado        OUT t_cursor
    )
    IS
    BEGIN
        IF fn_existe_detalle_venta(p_id_detalle_venta) = 0 THEN
            RAISE_APPLICATION_ERROR(-21222, 'El detalle de venta indicado no existe.');
        END IF;

        OPEN p_resultado FOR
            SELECT dv.id_detalle_venta,
                   dv.cantidad,
                   dv.precio_unitario,
                   dv.subtotal,
                   dv.encabezados_ventas_id_venta,
                   dv.productos_id_producto,
                   p.nombre AS producto
            FROM detalles_ventas dv
            JOIN productos p
              ON dv.productos_id_producto = p.id_producto
            WHERE dv.id_detalle_venta = p_id_detalle_venta;

    EXCEPTION
        WHEN OTHERS THEN
            RAISE_APPLICATION_ERROR(-21223, 'Error al consultar detalle venta: ' || SQLERRM);
    END sp_obtener_detalle_venta;

/*
LISTAR DETALLES_VENTAS
*/
    PROCEDURE sp_listar_detalles_ventas(
        p_resultado OUT t_cursor
    )
    IS
    BEGIN
        OPEN p_resultado FOR
            SELECT dv.id_detalle_venta,
                   dv.cantidad,
                   dv.precio_unitario,
                   dv.subtotal,
                   dv.encabezados_ventas_id_venta,
                   dv.productos_id_producto,
                   p.nombre AS producto
            FROM detalles_ventas dv
            JOIN productos p
              ON dv.productos_id_producto = p.id_producto
            ORDER BY dv.id_detalle_venta;

    EXCEPTION
        WHEN OTHERS THEN
            RAISE_APPLICATION_ERROR(-21224, 'Error al listar detalles ventas: ' || SQLERRM);
    END sp_listar_detalles_ventas;

END pkg_detalles_ventas;
/
SHOW ERRORS;

-- -------------------- END PACKAGE BODY ------------------

/*
PACKAGE: PKG_PAGOS

Paquete para administrar la tabla pagos
*/

CREATE OR REPLACE PACKAGE M_HAMILTON_STORE.pkg_pagos AS

/* Cursor para devolver consultas */
    TYPE t_cursor IS REF CURSOR;

/*
Funcion para verificar si un pago existe
*/
    FUNCTION fn_existe_pago(
        p_id_pago IN NUMBER
    ) RETURN NUMBER;

/*
CRUD DE PAGOS
*/
    PROCEDURE sp_insertar_pago(
        p_monto                       IN NUMBER,
        p_fecha_pago                  IN DATE,
        p_metodos_pago_id_metodo_pago IN NUMBER,
        p_encabezados_ventas_id_venta IN NUMBER
    );

    PROCEDURE sp_actualizar_pago(
        p_id_pago                     IN NUMBER,
        p_monto                       IN NUMBER,
        p_fecha_pago                  IN DATE,
        p_metodos_pago_id_metodo_pago IN NUMBER,
        p_encabezados_ventas_id_venta IN NUMBER
    );

    PROCEDURE sp_eliminar_pago(
        p_id_pago IN NUMBER
    );

    PROCEDURE sp_obtener_pago(
        p_id_pago   IN NUMBER,
        p_resultado OUT t_cursor
    );

    PROCEDURE sp_listar_pagos(
        p_resultado OUT t_cursor
    );

END pkg_pagos;
/
SHOW ERRORS;

-- ---------------- END CRUDS PAGOS ------------------


/*
PACKAGE BODY: PKG_PAGOS
*/
CREATE OR REPLACE PACKAGE BODY M_HAMILTON_STORE.pkg_pagos AS

/*
Funcion publica para saber si existe un pago
*/
    FUNCTION fn_existe_pago(
        p_id_pago IN NUMBER
    ) RETURN NUMBER
    IS
        v_count NUMBER;
    BEGIN
        SELECT COUNT(*)
        INTO v_count
        FROM pagos
        WHERE id_pago = p_id_pago;

        RETURN v_count;
    END fn_existe_pago;

/*
INSERTAR PAGO
*/
    PROCEDURE sp_insertar_pago(
        p_monto                       IN NUMBER,
        p_fecha_pago                  IN DATE,
        p_metodos_pago_id_metodo_pago IN NUMBER,
        p_encabezados_ventas_id_venta IN NUMBER
    )
    IS
        v_count NUMBER;
    BEGIN
        IF p_monto IS NULL THEN
            RAISE_APPLICATION_ERROR(-21301, 'El monto es obligatorio.');
        END IF;

        IF p_fecha_pago IS NULL THEN
            RAISE_APPLICATION_ERROR(-21302, 'La fecha de pago es obligatoria.');
        END IF;

        IF p_monto < 0 THEN
            RAISE_APPLICATION_ERROR(-21303, 'El monto no puede ser negativo.');
        END IF;

        SELECT COUNT(*)
        INTO v_count
        FROM metodos_pago
        WHERE id_metodo_pago = p_metodos_pago_id_metodo_pago;

        IF v_count = 0 THEN
            RAISE_APPLICATION_ERROR(-21304, 'El metodo de pago indicado no existe.');
        END IF;

        SELECT COUNT(*)
        INTO v_count
        FROM encabezados_ventas
        WHERE id_venta = p_encabezados_ventas_id_venta;

        IF v_count = 0 THEN
            RAISE_APPLICATION_ERROR(-21305, 'La venta indicada no existe.');
        END IF;

        INSERT INTO pagos (
            monto,
            fecha_pago,
            metodos_pago_id_metodo_pago,
            encabezados_ventas_id_venta
        )
        VALUES (
            p_monto,
            p_fecha_pago,
            p_metodos_pago_id_metodo_pago,
            p_encabezados_ventas_id_venta
        );

    EXCEPTION
        WHEN OTHERS THEN
            RAISE_APPLICATION_ERROR(-21306, 'Error al insertar pago: ' || SQLERRM);
    END sp_insertar_pago;

/*
ACTUALIZAR PAGO
*/
    PROCEDURE sp_actualizar_pago(
        p_id_pago                     IN NUMBER,
        p_monto                       IN NUMBER,
        p_fecha_pago                  IN DATE,
        p_metodos_pago_id_metodo_pago IN NUMBER,
        p_encabezados_ventas_id_venta IN NUMBER
    )
    IS
        v_count NUMBER;
    BEGIN
        IF fn_existe_pago(p_id_pago) = 0 THEN
            RAISE_APPLICATION_ERROR(-21307, 'El pago indicado no existe.');
        END IF;

        IF p_monto IS NULL THEN
            RAISE_APPLICATION_ERROR(-21308, 'El monto es obligatorio.');
        END IF;

        IF p_fecha_pago IS NULL THEN
            RAISE_APPLICATION_ERROR(-21309, 'La fecha de pago es obligatoria.');
        END IF;

        IF p_monto < 0 THEN
            RAISE_APPLICATION_ERROR(-21310, 'El monto no puede ser negativo.');
        END IF;

        SELECT COUNT(*)
        INTO v_count
        FROM metodos_pago
        WHERE id_metodo_pago = p_metodos_pago_id_metodo_pago;

        IF v_count = 0 THEN
            RAISE_APPLICATION_ERROR(-21311, 'El metodo de pago indicado no existe.');
        END IF;

        SELECT COUNT(*)
        INTO v_count
        FROM encabezados_ventas
        WHERE id_venta = p_encabezados_ventas_id_venta;

        IF v_count = 0 THEN
            RAISE_APPLICATION_ERROR(-21312, 'La venta indicada no existe.');
        END IF;

        UPDATE pagos
        SET monto = p_monto,
            fecha_pago = p_fecha_pago,
            metodos_pago_id_metodo_pago = p_metodos_pago_id_metodo_pago,
            encabezados_ventas_id_venta = p_encabezados_ventas_id_venta
        WHERE id_pago = p_id_pago;

    EXCEPTION
        WHEN OTHERS THEN
            RAISE_APPLICATION_ERROR(-21313, 'Error al actualizar pago: ' || SQLERRM);
    END sp_actualizar_pago;

/*
ELIMINAR PAGO
*/
    PROCEDURE sp_eliminar_pago(
        p_id_pago IN NUMBER
    )
    IS
    BEGIN
        IF fn_existe_pago(p_id_pago) = 0 THEN
            RAISE_APPLICATION_ERROR(-21314, 'El pago indicado no existe.');
        END IF;

        DELETE FROM pagos
        WHERE id_pago = p_id_pago;

    EXCEPTION
        WHEN OTHERS THEN
            RAISE_APPLICATION_ERROR(-21315, 'Error al eliminar pago: ' || SQLERRM);
    END sp_eliminar_pago;

/*
OBTENER PAGO POR ID
*/
    PROCEDURE sp_obtener_pago(
        p_id_pago   IN NUMBER,
        p_resultado OUT t_cursor
    )
    IS
    BEGIN
        IF fn_existe_pago(p_id_pago) = 0 THEN
            RAISE_APPLICATION_ERROR(-21316, 'El pago indicado no existe.');
        END IF;

        OPEN p_resultado FOR
            SELECT pa.id_pago,
                   pa.monto,
                   pa.fecha_pago,
                   pa.metodos_pago_id_metodo_pago,
                   mp.nombre AS metodo_pago,
                   pa.encabezados_ventas_id_venta
            FROM pagos pa
            JOIN metodos_pago mp
              ON pa.metodos_pago_id_metodo_pago = mp.id_metodo_pago
            WHERE pa.id_pago = p_id_pago;

    EXCEPTION
        WHEN OTHERS THEN
            RAISE_APPLICATION_ERROR(-21317, 'Error al consultar pago: ' || SQLERRM);
    END sp_obtener_pago;

/*
LISTAR PAGOS
*/
    PROCEDURE sp_listar_pagos(
        p_resultado OUT t_cursor
    )
    IS
    BEGIN
        OPEN p_resultado FOR
            SELECT pa.id_pago,
                   pa.monto,
                   pa.fecha_pago,
                   pa.metodos_pago_id_metodo_pago,
                   mp.nombre AS metodo_pago,
                   pa.encabezados_ventas_id_venta
            FROM pagos pa
            JOIN metodos_pago mp
              ON pa.metodos_pago_id_metodo_pago = mp.id_metodo_pago
            ORDER BY pa.id_pago;

    EXCEPTION
        WHEN OTHERS THEN
            RAISE_APPLICATION_ERROR(-21318, 'Error al listar pagos: ' || SQLERRM);
    END sp_listar_pagos;

END pkg_pagos;
/
SHOW ERRORS;
-- -------------------- END PACKAGE BODY ------------------

/*
PACKAGE: PKG_FACTURAS

Paquete para administrar la tabla facturas
*/

CREATE OR REPLACE PACKAGE M_HAMILTON_STORE.pkg_facturas AS

/* Cursor para devolver consultas */
    TYPE t_cursor IS REF CURSOR;

/*
Funcion para verificar si una factura existe
*/
    FUNCTION fn_existe_factura(
        p_id_factura IN NUMBER
    ) RETURN NUMBER;

/*
CRUD DE FACTURAS
*/
    PROCEDURE sp_insertar_factura(
        p_numero_factura             IN VARCHAR2,
        p_clave_hacienda             IN VARCHAR2,
        p_fecha_emision              IN DATE,
        p_estados_id_estado          IN NUMBER,
        p_xml                        IN CLOB,
        p_encabezados_ventas_id_venta IN NUMBER
    );

    PROCEDURE sp_actualizar_factura(
        p_id_factura                 IN NUMBER,
        p_numero_factura             IN VARCHAR2,
        p_clave_hacienda             IN VARCHAR2,
        p_fecha_emision              IN DATE,
        p_estados_id_estado          IN NUMBER,
        p_xml                        IN CLOB,
        p_encabezados_ventas_id_venta IN NUMBER
    );

    PROCEDURE sp_eliminar_factura(
        p_id_factura IN NUMBER
    );

    PROCEDURE sp_obtener_factura(
        p_id_factura IN NUMBER,
        p_resultado  OUT t_cursor
    );

    PROCEDURE sp_listar_facturas(
        p_resultado OUT t_cursor
    );

END pkg_facturas;
/
SHOW ERRORS;

-- ---------------- END CRUDS FACTURAS ------------------


/*
PACKAGE BODY: PKG_FACTURAS
*/
CREATE OR REPLACE PACKAGE BODY M_HAMILTON_STORE.pkg_facturas AS

/*
Funcion para validar si un texto viene vacio
*/
    FUNCTION fn_texto_vacio(
        p_texto IN VARCHAR2
    ) RETURN BOOLEAN
    IS
    BEGIN
        RETURN p_texto IS NULL OR TRIM(p_texto) IS NULL;
    END fn_texto_vacio;

/*
Funcion para validar numero de factura
*/
    FUNCTION fn_numero_factura_valido(
        p_texto IN VARCHAR2
    ) RETURN BOOLEAN
    IS
    BEGIN
        RETURN REGEXP_LIKE(TRIM(p_texto), '^[A-Za-z0-9-]+$');
    END fn_numero_factura_valido;

/*
Funcion publica para saber si existe una factura
*/
    FUNCTION fn_existe_factura(
        p_id_factura IN NUMBER
    ) RETURN NUMBER
    IS
        v_count NUMBER;
    BEGIN
        SELECT COUNT(*)
        INTO v_count
        FROM facturas
        WHERE id_factura = p_id_factura;

        RETURN v_count;
    END fn_existe_factura;

/*
INSERTAR FACTURA
*/
    PROCEDURE sp_insertar_factura(
        p_numero_factura              IN VARCHAR2,
        p_clave_hacienda              IN VARCHAR2,
        p_fecha_emision               IN DATE,
        p_estados_id_estado           IN NUMBER,
        p_xml                         IN CLOB,
        p_encabezados_ventas_id_venta IN NUMBER
    )
    IS
        v_count NUMBER;
    BEGIN
        IF fn_texto_vacio(p_numero_factura) THEN
            RAISE_APPLICATION_ERROR(-21401, 'El numero de factura es obligatorio.');
        END IF;

        IF NOT fn_numero_factura_valido(p_numero_factura) THEN
            RAISE_APPLICATION_ERROR(-21402, 'El numero de factura tiene formato no valido.');
        END IF;

        IF p_fecha_emision IS NULL THEN
            RAISE_APPLICATION_ERROR(-21403, 'La fecha de emision es obligatoria.');
        END IF;

        SELECT COUNT(*)
        INTO v_count
        FROM estados
        WHERE id_estado = p_estados_id_estado;

        IF v_count = 0 THEN
            RAISE_APPLICATION_ERROR(-21404, 'El estado indicado no existe.');
        END IF;

        SELECT COUNT(*)
        INTO v_count
        FROM encabezados_ventas
        WHERE id_venta = p_encabezados_ventas_id_venta;

        IF v_count = 0 THEN
            RAISE_APPLICATION_ERROR(-21405, 'La venta indicada no existe.');
        END IF;

        SELECT COUNT(*)
        INTO v_count
        FROM facturas
        WHERE UPPER(TRIM(numero_factura)) = UPPER(TRIM(p_numero_factura));

        IF v_count > 0 THEN
            RAISE_APPLICATION_ERROR(-21406, 'Ya existe una factura con ese numero.');
        END IF;

        INSERT INTO facturas (
            numero_factura,
            clave_hacienda,
            fecha_emision,
            estados_id_estado,
            xml,
            encabezados_ventas_id_venta
        )
        VALUES (
            TRIM(p_numero_factura),
            TRIM(p_clave_hacienda),
            p_fecha_emision,
            p_estados_id_estado,
            p_xml,
            p_encabezados_ventas_id_venta
        );

    EXCEPTION
        WHEN OTHERS THEN
            RAISE_APPLICATION_ERROR(-21407, 'Error al insertar factura: ' || SQLERRM);
    END sp_insertar_factura;

/*
ACTUALIZAR FACTURA
*/
    PROCEDURE sp_actualizar_factura(
        p_id_factura                  IN NUMBER,
        p_numero_factura              IN VARCHAR2,
        p_clave_hacienda              IN VARCHAR2,
        p_fecha_emision               IN DATE,
        p_estados_id_estado           IN NUMBER,
        p_xml                         IN CLOB,
        p_encabezados_ventas_id_venta IN NUMBER
    )
    IS
        v_count NUMBER;
    BEGIN
        IF fn_existe_factura(p_id_factura) = 0 THEN
            RAISE_APPLICATION_ERROR(-21408, 'La factura indicada no existe.');
        END IF;

        IF fn_texto_vacio(p_numero_factura) THEN
            RAISE_APPLICATION_ERROR(-21409, 'El numero de factura es obligatorio.');
        END IF;

        IF NOT fn_numero_factura_valido(p_numero_factura) THEN
            RAISE_APPLICATION_ERROR(-21410, 'El numero de factura tiene formato no valido.');
        END IF;

        IF p_fecha_emision IS NULL THEN
            RAISE_APPLICATION_ERROR(-21411, 'La fecha de emision es obligatoria.');
        END IF;

        SELECT COUNT(*)
        INTO v_count
        FROM estados
        WHERE id_estado = p_estados_id_estado;

        IF v_count = 0 THEN
            RAISE_APPLICATION_ERROR(-21412, 'El estado indicado no existe.');
        END IF;

        SELECT COUNT(*)
        INTO v_count
        FROM encabezados_ventas
        WHERE id_venta = p_encabezados_ventas_id_venta;

        IF v_count = 0 THEN
            RAISE_APPLICATION_ERROR(-21413, 'La venta indicada no existe.');
        END IF;

        SELECT COUNT(*)
        INTO v_count
        FROM facturas
        WHERE UPPER(TRIM(numero_factura)) = UPPER(TRIM(p_numero_factura))
          AND id_factura <> p_id_factura;

        IF v_count > 0 THEN
            RAISE_APPLICATION_ERROR(-21414, 'Ya existe otra factura con ese numero.');
        END IF;

        UPDATE facturas
        SET numero_factura = TRIM(p_numero_factura),
            clave_hacienda = TRIM(p_clave_hacienda),
            fecha_emision = p_fecha_emision,
            estados_id_estado = p_estados_id_estado,
            xml = p_xml,
            encabezados_ventas_id_venta = p_encabezados_ventas_id_venta
        WHERE id_factura = p_id_factura;

    EXCEPTION
        WHEN OTHERS THEN
            RAISE_APPLICATION_ERROR(-21415, 'Error al actualizar factura: ' || SQLERRM);
    END sp_actualizar_factura;

/*
ELIMINAR FACTURA
*/
    PROCEDURE sp_eliminar_factura(
        p_id_factura IN NUMBER
    )
    IS
    BEGIN
        IF fn_existe_factura(p_id_factura) = 0 THEN
            RAISE_APPLICATION_ERROR(-21416, 'La factura indicada no existe.');
        END IF;

        DELETE FROM facturas
        WHERE id_factura = p_id_factura;

    EXCEPTION
        WHEN OTHERS THEN
            RAISE_APPLICATION_ERROR(-21417, 'Error al eliminar factura: ' || SQLERRM);
    END sp_eliminar_factura;

/*
OBTENER FACTURA POR ID
*/
    PROCEDURE sp_obtener_factura(
        p_id_factura IN NUMBER,
        p_resultado  OUT t_cursor
    )
    IS
    BEGIN
        IF fn_existe_factura(p_id_factura) = 0 THEN
            RAISE_APPLICATION_ERROR(-21418, 'La factura indicada no existe.');
        END IF;

        OPEN p_resultado FOR
            SELECT f.id_factura,
                   f.numero_factura,
                   f.clave_hacienda,
                   f.fecha_emision,
                   f.estados_id_estado,
                   e.nombre AS estado,
                   f.xml,
                   f.encabezados_ventas_id_venta
            FROM facturas f
            JOIN estados e
              ON f.estados_id_estado = e.id_estado
            WHERE f.id_factura = p_id_factura;

    EXCEPTION
        WHEN OTHERS THEN
            RAISE_APPLICATION_ERROR(-21419, 'Error al consultar factura: ' || SQLERRM);
    END sp_obtener_factura;

/*
LISTAR FACTURAS
*/
    PROCEDURE sp_listar_facturas(
        p_resultado OUT t_cursor
    )
    IS
    BEGIN
        OPEN p_resultado FOR
            SELECT f.id_factura,
                   f.numero_factura,
                   f.clave_hacienda,
                   f.fecha_emision,
                   f.estados_id_estado,
                   e.nombre AS estado,
                   f.xml,
                   f.encabezados_ventas_id_venta
            FROM facturas f
            JOIN estados e
              ON f.estados_id_estado = e.id_estado
            ORDER BY f.id_factura;

    EXCEPTION
        WHEN OTHERS THEN
            RAISE_APPLICATION_ERROR(-21420, 'Error al listar facturas: ' || SQLERRM);
    END sp_listar_facturas;

END pkg_facturas;
/
SHOW ERRORS;

-- -------------------- END PACKAGE BODY ------------------

/*
PACKAGE: PKG_USUARIOS

Paquete para administrar la tabla usuarios
*/

CREATE OR REPLACE PACKAGE M_HAMILTON_STORE.pkg_usuarios AS

/* Cursor para devolver consultas */
    TYPE t_cursor IS REF CURSOR;

/*
Funcion para verificar si un usuario existe
*/
    FUNCTION fn_existe_usuario(
        p_id_usuario IN NUMBER
    ) RETURN NUMBER;

/*
CRUD DE USUARIOS
*/
    PROCEDURE sp_insertar_usuario(
        p_username              IN VARCHAR2,
        p_password_encriptado   IN VARCHAR2,
        p_roles_id_rol          IN NUMBER,
        p_estados_id_estado     IN NUMBER,
        p_empleados_id_empleado IN NUMBER,
        p_clientes_id_cliente   IN NUMBER
    );

    PROCEDURE sp_actualizar_usuario(
        p_id_usuario            IN NUMBER,
        p_username              IN VARCHAR2,
        p_password_encriptado   IN VARCHAR2,
        p_roles_id_rol          IN NUMBER,
        p_estados_id_estado     IN NUMBER,
        p_empleados_id_empleado IN NUMBER,
        p_clientes_id_cliente   IN NUMBER
    );

    PROCEDURE sp_eliminar_usuario(
        p_id_usuario IN NUMBER
    );

    PROCEDURE sp_obtener_usuario(
        p_id_usuario IN NUMBER,
        p_resultado  OUT t_cursor
    );

    PROCEDURE sp_listar_usuarios(
        p_resultado OUT t_cursor
    );

END pkg_usuarios;
/
SHOW ERRORS;

-- ---------------- END CRUDS USUARIOS ------------------


/*
PACKAGE BODY: PKG_USUARIOS
*/
CREATE OR REPLACE PACKAGE BODY M_HAMILTON_STORE.pkg_usuarios AS

/*
Funcion para validar si un texto viene vacio
*/
    FUNCTION fn_texto_vacio(
        p_texto IN VARCHAR2
    ) RETURN BOOLEAN
    IS
    BEGIN
        RETURN p_texto IS NULL OR TRIM(p_texto) IS NULL;
    END fn_texto_vacio;

/*
Funcion para validar username
*/
    FUNCTION fn_username_valido(
        p_texto IN VARCHAR2
    ) RETURN BOOLEAN
    IS
    BEGIN
        RETURN REGEXP_LIKE(TRIM(p_texto), '^[A-Za-z0-9._-]+$');
    END fn_username_valido;

/*
Funcion publica para saber si existe un usuario
*/
    FUNCTION fn_existe_usuario(
        p_id_usuario IN NUMBER
    ) RETURN NUMBER
    IS
        v_count NUMBER;
    BEGIN
        SELECT COUNT(*)
        INTO v_count
        FROM usuarios
        WHERE id_usuario = p_id_usuario;

        RETURN v_count;
    END fn_existe_usuario;

/*
INSERTAR USUARIO
*/
    PROCEDURE sp_insertar_usuario(
        p_username              IN VARCHAR2,
        p_password_encriptado   IN VARCHAR2,
        p_roles_id_rol          IN NUMBER,
        p_estados_id_estado     IN NUMBER,
        p_empleados_id_empleado IN NUMBER,
        p_clientes_id_cliente   IN NUMBER
    )
    IS
        v_count NUMBER;
    BEGIN
        IF fn_texto_vacio(p_username) THEN
            RAISE_APPLICATION_ERROR(-21501, 'El username es obligatorio.');
        END IF;

        IF fn_texto_vacio(p_password_encriptado) THEN
            RAISE_APPLICATION_ERROR(-21502, 'La password es obligatoria.');
        END IF;

        IF NOT fn_username_valido(p_username) THEN
            RAISE_APPLICATION_ERROR(-21503, 'El username tiene formato no valido.');
        END IF;

        SELECT COUNT(*)
        INTO v_count
        FROM roles
        WHERE id_rol = p_roles_id_rol;

        IF v_count = 0 THEN
            RAISE_APPLICATION_ERROR(-21504, 'El rol indicado no existe.');
        END IF;

        SELECT COUNT(*)
        INTO v_count
        FROM estados
        WHERE id_estado = p_estados_id_estado;

        IF v_count = 0 THEN
            RAISE_APPLICATION_ERROR(-21505, 'El estado indicado no existe.');
        END IF;

        IF p_empleados_id_empleado IS NULL AND p_clientes_id_cliente IS NULL THEN
            RAISE_APPLICATION_ERROR(-21506, 'Debe indicar un empleado o un cliente.');
        END IF;

        IF p_empleados_id_empleado IS NOT NULL AND p_clientes_id_cliente IS NOT NULL THEN
            RAISE_APPLICATION_ERROR(-21507, 'No se puede asignar el usuario a empleado y cliente al mismo tiempo.');
        END IF;

        IF p_empleados_id_empleado IS NOT NULL THEN
            SELECT COUNT(*)
            INTO v_count
            FROM empleados
            WHERE id_empleado = p_empleados_id_empleado;

            IF v_count = 0 THEN
                RAISE_APPLICATION_ERROR(-21508, 'El empleado indicado no existe.');
            END IF;
        END IF;

        IF p_clientes_id_cliente IS NOT NULL THEN
            SELECT COUNT(*)
            INTO v_count
            FROM clientes
            WHERE id_cliente = p_clientes_id_cliente;

            IF v_count = 0 THEN
                RAISE_APPLICATION_ERROR(-21509, 'El cliente indicado no existe.');
            END IF;
        END IF;

        SELECT COUNT(*)
        INTO v_count
        FROM usuarios
        WHERE UPPER(TRIM(username)) = UPPER(TRIM(p_username));

        IF v_count > 0 THEN
            RAISE_APPLICATION_ERROR(-21510, 'Ya existe un usuario con ese username.');
        END IF;

        INSERT INTO usuarios (
            username,
            password_encriptado,
            roles_id_rol,
            estados_id_estado,
            empleados_id_empleado,
            clientes_id_cliente
        )
        VALUES (
            TRIM(p_username),
            TRIM(p_password_encriptado),
            p_roles_id_rol,
            p_estados_id_estado,
            p_empleados_id_empleado,
            p_clientes_id_cliente
        );

    EXCEPTION
        WHEN OTHERS THEN
            RAISE_APPLICATION_ERROR(-21511, 'Error al insertar usuario: ' || SQLERRM);
    END sp_insertar_usuario;

/*
ACTUALIZAR USUARIO
*/
    PROCEDURE sp_actualizar_usuario(
        p_id_usuario            IN NUMBER,
        p_username              IN VARCHAR2,
        p_password_encriptado   IN VARCHAR2,
        p_roles_id_rol          IN NUMBER,
        p_estados_id_estado     IN NUMBER,
        p_empleados_id_empleado IN NUMBER,
        p_clientes_id_cliente   IN NUMBER
    )
    IS
        v_count NUMBER;
    BEGIN
        IF fn_existe_usuario(p_id_usuario) = 0 THEN
            RAISE_APPLICATION_ERROR(-21512, 'El usuario indicado no existe.');
        END IF;

        IF fn_texto_vacio(p_username) THEN
            RAISE_APPLICATION_ERROR(-21513, 'El username es obligatorio.');
        END IF;

        IF fn_texto_vacio(p_password_encriptado) THEN
            RAISE_APPLICATION_ERROR(-21514, 'La password es obligatoria.');
        END IF;

        IF NOT fn_username_valido(p_username) THEN
            RAISE_APPLICATION_ERROR(-21515, 'El username tiene formato no valido.');
        END IF;

        SELECT COUNT(*)
        INTO v_count
        FROM roles
        WHERE id_rol = p_roles_id_rol;

        IF v_count = 0 THEN
            RAISE_APPLICATION_ERROR(-21516, 'El rol indicado no existe.');
        END IF;

        SELECT COUNT(*)
        INTO v_count
        FROM estados
        WHERE id_estado = p_estados_id_estado;

        IF v_count = 0 THEN
            RAISE_APPLICATION_ERROR(-21517, 'El estado indicado no existe.');
        END IF;

        IF p_empleados_id_empleado IS NULL AND p_clientes_id_cliente IS NULL THEN
            RAISE_APPLICATION_ERROR(-21518, 'Debe indicar un empleado o un cliente.');
        END IF;

        IF p_empleados_id_empleado IS NOT NULL AND p_clientes_id_cliente IS NOT NULL THEN
            RAISE_APPLICATION_ERROR(-21519, 'No se puede asignar el usuario a empleado y cliente al mismo tiempo.');
        END IF;

        IF p_empleados_id_empleado IS NOT NULL THEN
            SELECT COUNT(*)
            INTO v_count
            FROM empleados
            WHERE id_empleado = p_empleados_id_empleado;

            IF v_count = 0 THEN
                RAISE_APPLICATION_ERROR(-21520, 'El empleado indicado no existe.');
            END IF;
        END IF;

        IF p_clientes_id_cliente IS NOT NULL THEN
            SELECT COUNT(*)
            INTO v_count
            FROM clientes
            WHERE id_cliente = p_clientes_id_cliente;

            IF v_count = 0 THEN
                RAISE_APPLICATION_ERROR(-21521, 'El cliente indicado no existe.');
            END IF;
        END IF;

        SELECT COUNT(*)
        INTO v_count
        FROM usuarios
        WHERE UPPER(TRIM(username)) = UPPER(TRIM(p_username))
          AND id_usuario <> p_id_usuario;

        IF v_count > 0 THEN
            RAISE_APPLICATION_ERROR(-21522, 'Ya existe otro usuario con ese username.');
        END IF;

        UPDATE usuarios
        SET username = TRIM(p_username),
            password_encriptado = TRIM(p_password_encriptado),
            roles_id_rol = p_roles_id_rol,
            estados_id_estado = p_estados_id_estado,
            empleados_id_empleado = p_empleados_id_empleado,
            clientes_id_cliente = p_clientes_id_cliente
        WHERE id_usuario = p_id_usuario;

    EXCEPTION
        WHEN OTHERS THEN
            RAISE_APPLICATION_ERROR(-21523, 'Error al actualizar usuario: ' || SQLERRM);
    END sp_actualizar_usuario;

/*
ELIMINAR USUARIO
*/
    PROCEDURE sp_eliminar_usuario(
        p_id_usuario IN NUMBER
    )
    IS
    BEGIN
        IF fn_existe_usuario(p_id_usuario) = 0 THEN
            RAISE_APPLICATION_ERROR(-21524, 'El usuario indicado no existe.');
        END IF;

        DELETE FROM usuarios
        WHERE id_usuario = p_id_usuario;

    EXCEPTION
        WHEN OTHERS THEN
            RAISE_APPLICATION_ERROR(-21525, 'Error al eliminar usuario: ' || SQLERRM);
    END sp_eliminar_usuario;

/*
OBTENER USUARIO POR ID
*/
    PROCEDURE sp_obtener_usuario(
        p_id_usuario IN NUMBER,
        p_resultado  OUT t_cursor
    )
    IS
    BEGIN
        IF fn_existe_usuario(p_id_usuario) = 0 THEN
            RAISE_APPLICATION_ERROR(-21526, 'El usuario indicado no existe.');
        END IF;

        OPEN p_resultado FOR
            SELECT u.id_usuario,
                   u.username,
                   u.password_encriptado,
                   u.roles_id_rol,
                   r.nombre AS rol,
                   u.estados_id_estado,
                   e2.nombre AS estado,
                   u.empleados_id_empleado,
                   u.clientes_id_cliente
            FROM usuarios u
            JOIN roles r
              ON u.roles_id_rol = r.id_rol
            JOIN estados e2
              ON u.estados_id_estado = e2.id_estado
            WHERE u.id_usuario = p_id_usuario;

    EXCEPTION
        WHEN OTHERS THEN
            RAISE_APPLICATION_ERROR(-21527, 'Error al consultar usuario: ' || SQLERRM);
    END sp_obtener_usuario;

/*
LISTAR USUARIOS
*/
    PROCEDURE sp_listar_usuarios(
        p_resultado OUT t_cursor
    )
    IS
    BEGIN
        OPEN p_resultado FOR
            SELECT u.id_usuario,
                   u.username,
                   u.password_encriptado,
                   u.roles_id_rol,
                   r.nombre AS rol,
                   u.estados_id_estado,
                   e2.nombre AS estado,
                   u.empleados_id_empleado,
                   u.clientes_id_cliente
            FROM usuarios u
            JOIN roles r
              ON u.roles_id_rol = r.id_rol
            JOIN estados e2
              ON u.estados_id_estado = e2.id_estado
            ORDER BY u.id_usuario;

    EXCEPTION
        WHEN OTHERS THEN
            RAISE_APPLICATION_ERROR(-21528, 'Error al listar usuarios: ' || SQLERRM);
    END sp_listar_usuarios;

END pkg_usuarios;
/
SHOW ERRORS;

-- -------------------- END PACKAGE BODY ------------------


/*
PACKAGE: PKG_GESTION_STOCK

Paquete para administrar la tabla gestion_stock
*/

CREATE OR REPLACE PACKAGE M_HAMILTON_STORE.pkg_gestion_stock AS

/* Cursor para devolver consultas */
    TYPE t_cursor IS REF CURSOR;

/*
Funcion para verificar si una gestion existe
*/
    FUNCTION fn_existe_gestion_stock(
        p_id_gestion_stock IN NUMBER
    ) RETURN NUMBER;

/*
CRUD DE GESTION_STOCK
*/
    PROCEDURE sp_insertar_gestion_stock(
        p_cantidad                     IN NUMBER,
        p_fecha_gestion                IN DATE,
        p_productos_id_producto        IN NUMBER,
        p_tipo_gestion_id_tipo_gestion IN NUMBER
    );

    PROCEDURE sp_actualizar_gestion_stock(
        p_id_gestion_stock             IN NUMBER,
        p_cantidad                     IN NUMBER,
        p_fecha_gestion                IN DATE,
        p_productos_id_producto        IN NUMBER,
        p_tipo_gestion_id_tipo_gestion IN NUMBER
    );

    PROCEDURE sp_eliminar_gestion_stock(
        p_id_gestion_stock IN NUMBER
    );

    PROCEDURE sp_obtener_gestion_stock(
        p_id_gestion_stock IN NUMBER,
        p_resultado        OUT t_cursor
    );

    PROCEDURE sp_listar_gestion_stock(
        p_resultado OUT t_cursor
    );

END pkg_gestion_stock;
/
SHOW ERRORS;

-- ---------------- END CRUDS GESTION_STOCK ------------------


/*
PACKAGE BODY: PKG_GESTION_STOCK
*/
CREATE OR REPLACE PACKAGE BODY M_HAMILTON_STORE.pkg_gestion_stock AS

/*
Funcion publica para saber si existe una gestion de stock
*/
    FUNCTION fn_existe_gestion_stock(
        p_id_gestion_stock IN NUMBER
    ) RETURN NUMBER
    IS
        v_count NUMBER;
    BEGIN
        SELECT COUNT(*)
        INTO v_count
        FROM gestion_stock
        WHERE id_gestion_stock = p_id_gestion_stock;

        RETURN v_count;
    END fn_existe_gestion_stock;

/*
INSERTAR GESTION_STOCK
*/
    PROCEDURE sp_insertar_gestion_stock(
        p_cantidad                     IN NUMBER,
        p_fecha_gestion                IN DATE,
        p_productos_id_producto        IN NUMBER,
        p_tipo_gestion_id_tipo_gestion IN NUMBER
    )
    IS
        v_count NUMBER;
    BEGIN
        IF p_cantidad IS NULL THEN
            RAISE_APPLICATION_ERROR(-21601, 'La cantidad es obligatoria.');
        END IF;

        IF p_fecha_gestion IS NULL THEN
            RAISE_APPLICATION_ERROR(-21602, 'La fecha de gestion es obligatoria.');
        END IF;

        IF p_cantidad = 0 THEN
            RAISE_APPLICATION_ERROR(-21603, 'La cantidad no puede ser cero.');
        END IF;

        SELECT COUNT(*)
        INTO v_count
        FROM productos
        WHERE id_producto = p_productos_id_producto;

        IF v_count = 0 THEN
            RAISE_APPLICATION_ERROR(-21604, 'El producto indicado no existe.');
        END IF;

        SELECT COUNT(*)
        INTO v_count
        FROM tipo_gestion
        WHERE id_tipo_gestion = p_tipo_gestion_id_tipo_gestion;

        IF v_count = 0 THEN
            RAISE_APPLICATION_ERROR(-21605, 'El tipo de gestion indicado no existe.');
        END IF;

        INSERT INTO gestion_stock (
            cantidad,
            fecha_gestion,
            productos_id_producto,
            tipo_gestion_id_tipo_gestion
        )
        VALUES (
            p_cantidad,
            p_fecha_gestion,
            p_productos_id_producto,
            p_tipo_gestion_id_tipo_gestion
        );

    EXCEPTION
        WHEN OTHERS THEN
            RAISE_APPLICATION_ERROR(-21606, 'Error al insertar gestion stock: ' || SQLERRM);
    END sp_insertar_gestion_stock;

/*
ACTUALIZAR GESTION_STOCK
*/
    PROCEDURE sp_actualizar_gestion_stock(
        p_id_gestion_stock             IN NUMBER,
        p_cantidad                     IN NUMBER,
        p_fecha_gestion                IN DATE,
        p_productos_id_producto        IN NUMBER,
        p_tipo_gestion_id_tipo_gestion IN NUMBER
    )
    IS
        v_count NUMBER;
    BEGIN
        IF fn_existe_gestion_stock(p_id_gestion_stock) = 0 THEN
            RAISE_APPLICATION_ERROR(-21607, 'La gestion indicada no existe.');
        END IF;

        IF p_cantidad IS NULL THEN
            RAISE_APPLICATION_ERROR(-21608, 'La cantidad es obligatoria.');
        END IF;

        IF p_fecha_gestion IS NULL THEN
            RAISE_APPLICATION_ERROR(-21609, 'La fecha de gestion es obligatoria.');
        END IF;

        IF p_cantidad = 0 THEN
            RAISE_APPLICATION_ERROR(-21610, 'La cantidad no puede ser cero.');
        END IF;

        SELECT COUNT(*)
        INTO v_count
        FROM productos
        WHERE id_producto = p_productos_id_producto;

        IF v_count = 0 THEN
            RAISE_APPLICATION_ERROR(-21611, 'El producto indicado no existe.');
        END IF;

        SELECT COUNT(*)
        INTO v_count
        FROM tipo_gestion
        WHERE id_tipo_gestion = p_tipo_gestion_id_tipo_gestion;

        IF v_count = 0 THEN
            RAISE_APPLICATION_ERROR(-21612, 'El tipo de gestion indicado no existe.');
        END IF;

        UPDATE gestion_stock
        SET cantidad = p_cantidad,
            fecha_gestion = p_fecha_gestion,
            productos_id_producto = p_productos_id_producto,
            tipo_gestion_id_tipo_gestion = p_tipo_gestion_id_tipo_gestion
        WHERE id_gestion_stock = p_id_gestion_stock;

    EXCEPTION
        WHEN OTHERS THEN
            RAISE_APPLICATION_ERROR(-21613, 'Error al actualizar gestion stock: ' || SQLERRM);
    END sp_actualizar_gestion_stock;

/*
ELIMINAR GESTION_STOCK
*/
    PROCEDURE sp_eliminar_gestion_stock(
        p_id_gestion_stock IN NUMBER
    )
    IS
    BEGIN
        IF fn_existe_gestion_stock(p_id_gestion_stock) = 0 THEN
            RAISE_APPLICATION_ERROR(-21614, 'La gestion indicada no existe.');
        END IF;

        DELETE FROM gestion_stock
        WHERE id_gestion_stock = p_id_gestion_stock;

    EXCEPTION
        WHEN OTHERS THEN
            RAISE_APPLICATION_ERROR(-21615, 'Error al eliminar gestion stock: ' || SQLERRM);
    END sp_eliminar_gestion_stock;

/*
OBTENER GESTION_STOCK POR ID
*/
    PROCEDURE sp_obtener_gestion_stock(
        p_id_gestion_stock IN NUMBER,
        p_resultado        OUT t_cursor
    )
    IS
    BEGIN
        IF fn_existe_gestion_stock(p_id_gestion_stock) = 0 THEN
            RAISE_APPLICATION_ERROR(-21616, 'La gestion indicada no existe.');
        END IF;

        OPEN p_resultado FOR
            SELECT gs.id_gestion_stock,
                   gs.cantidad,
                   gs.fecha_gestion,
                   gs.productos_id_producto,
                   p.nombre AS producto,
                   gs.tipo_gestion_id_tipo_gestion,
                   tg.descripcion AS tipo_gestion
            FROM gestion_stock gs
            JOIN productos p
              ON gs.productos_id_producto = p.id_producto
            JOIN tipo_gestion tg
              ON gs.tipo_gestion_id_tipo_gestion = tg.id_tipo_gestion
            WHERE gs.id_gestion_stock = p_id_gestion_stock;

    EXCEPTION
        WHEN OTHERS THEN
            RAISE_APPLICATION_ERROR(-21617, 'Error al consultar gestion stock: ' || SQLERRM);
    END sp_obtener_gestion_stock;

/*
LISTAR GESTION_STOCK
*/
    PROCEDURE sp_listar_gestion_stock(
        p_resultado OUT t_cursor
    )
    IS
    BEGIN
        OPEN p_resultado FOR
            SELECT gs.id_gestion_stock,
                   gs.cantidad,
                   gs.fecha_gestion,
                   gs.productos_id_producto,
                   p.nombre AS producto,
                   gs.tipo_gestion_id_tipo_gestion,
                   tg.descripcion AS tipo_gestion
            FROM gestion_stock gs
            JOIN productos p
              ON gs.productos_id_producto = p.id_producto
            JOIN tipo_gestion tg
              ON gs.tipo_gestion_id_tipo_gestion = tg.id_tipo_gestion
            ORDER BY gs.id_gestion_stock;

    EXCEPTION
        WHEN OTHERS THEN
            RAISE_APPLICATION_ERROR(-21618, 'Error al listar gestion stock: ' || SQLERRM);
    END sp_listar_gestion_stock;

END pkg_gestion_stock;
/
SHOW ERRORS;

-- -------------------- END PACKAGE BODY ------------------

/*
PACKAGE: PKG_TIPO_GESTION

Paquete para administrar la tabla tipo_gestion
*/

CREATE OR REPLACE PACKAGE M_HAMILTON_STORE.pkg_tipo_gestion AS

/* Cursor para devolver consultas */
    TYPE t_cursor IS REF CURSOR;

/*
Funcion para verificar si un tipo de gestion existe
*/
    FUNCTION fn_existe_tipo_gestion(
        p_id_tipo_gestion IN NUMBER
    ) RETURN NUMBER;

/*
CRUD DE TIPO_GESTION
*/
    PROCEDURE sp_insertar_tipo_gestion(
        p_descripcion IN VARCHAR2
    );

    PROCEDURE sp_actualizar_tipo_gestion(
        p_id_tipo_gestion IN NUMBER,
        p_descripcion     IN VARCHAR2
    );

    PROCEDURE sp_eliminar_tipo_gestion(
        p_id_tipo_gestion IN NUMBER
    );

    PROCEDURE sp_obtener_tipo_gestion(
        p_id_tipo_gestion IN NUMBER,
        p_resultado       OUT t_cursor
    );

    PROCEDURE sp_listar_tipos_gestion(
        p_resultado OUT t_cursor
    );

END pkg_tipo_gestion;
/
SHOW ERRORS;

-- ---------------- END CRUDS TIPO_GESTION ------------------


/*
PACKAGE BODY: PKG_TIPO_GESTION
*/
CREATE OR REPLACE PACKAGE BODY M_HAMILTON_STORE.pkg_tipo_gestion AS

/*
Funcion para validar si un texto viene vacio
*/
    FUNCTION fn_texto_vacio(
        p_texto IN VARCHAR2
    ) RETURN BOOLEAN
    IS
    BEGIN
        RETURN p_texto IS NULL OR TRIM(p_texto) IS NULL;
    END fn_texto_vacio;

/*
Funcion publica para saber si existe un tipo de gestion
*/
    FUNCTION fn_existe_tipo_gestion(
        p_id_tipo_gestion IN NUMBER
    ) RETURN NUMBER
    IS
        v_count NUMBER;
    BEGIN
        SELECT COUNT(*)
        INTO v_count
        FROM tipo_gestion
        WHERE id_tipo_gestion = p_id_tipo_gestion;

        RETURN v_count;
    END fn_existe_tipo_gestion;

/*
INSERTAR TIPO_GESTION
*/
    PROCEDURE sp_insertar_tipo_gestion(
        p_descripcion IN VARCHAR2
    )
    IS
    BEGIN
        IF fn_texto_vacio(p_descripcion) THEN
            RAISE_APPLICATION_ERROR(-21701, 'La descripcion es obligatoria.');
        END IF;

        INSERT INTO tipo_gestion (
            descripcion
        )
        VALUES (
            TRIM(p_descripcion)
        );

    EXCEPTION
        WHEN OTHERS THEN
            RAISE_APPLICATION_ERROR(-21702, 'Error al insertar tipo gestion: ' || SQLERRM);
    END sp_insertar_tipo_gestion;

/*
ACTUALIZAR TIPO_GESTION
*/
    PROCEDURE sp_actualizar_tipo_gestion(
        p_id_tipo_gestion IN NUMBER,
        p_descripcion     IN VARCHAR2
    )
    IS
    BEGIN
        IF fn_existe_tipo_gestion(p_id_tipo_gestion) = 0 THEN
            RAISE_APPLICATION_ERROR(-21703, 'El tipo de gestion indicado no existe.');
        END IF;

        IF fn_texto_vacio(p_descripcion) THEN
            RAISE_APPLICATION_ERROR(-21704, 'La descripcion es obligatoria.');
        END IF;

        UPDATE tipo_gestion
        SET descripcion = TRIM(p_descripcion)
        WHERE id_tipo_gestion = p_id_tipo_gestion;

    EXCEPTION
        WHEN OTHERS THEN
            RAISE_APPLICATION_ERROR(-21705, 'Error al actualizar tipo gestion: ' || SQLERRM);
    END sp_actualizar_tipo_gestion;

/*
ELIMINAR TIPO_GESTION
*/
    PROCEDURE sp_eliminar_tipo_gestion(
        p_id_tipo_gestion IN NUMBER
    )
    IS
    BEGIN
        IF fn_existe_tipo_gestion(p_id_tipo_gestion) = 0 THEN
            RAISE_APPLICATION_ERROR(-21706, 'El tipo de gestion indicado no existe.');
        END IF;

        DELETE FROM tipo_gestion
        WHERE id_tipo_gestion = p_id_tipo_gestion;

    EXCEPTION
        WHEN OTHERS THEN
            RAISE_APPLICATION_ERROR(-21707, 'Error al eliminar tipo gestion: ' || SQLERRM);
    END sp_eliminar_tipo_gestion;

/*
OBTENER TIPO_GESTION POR ID
*/
    PROCEDURE sp_obtener_tipo_gestion(
        p_id_tipo_gestion IN NUMBER,
        p_resultado       OUT t_cursor
    )
    IS
    BEGIN
        IF fn_existe_tipo_gestion(p_id_tipo_gestion) = 0 THEN
            RAISE_APPLICATION_ERROR(-21708, 'El tipo de gestion indicado no existe.');
        END IF;

        OPEN p_resultado FOR
            SELECT id_tipo_gestion,
                   descripcion
            FROM tipo_gestion
            WHERE id_tipo_gestion = p_id_tipo_gestion;

    EXCEPTION
        WHEN OTHERS THEN
            RAISE_APPLICATION_ERROR(-21709, 'Error al consultar tipo gestion: ' || SQLERRM);
    END sp_obtener_tipo_gestion;

/*
LISTAR TIPOS_GESTION
*/
    PROCEDURE sp_listar_tipos_gestion(
        p_resultado OUT t_cursor
    )
    IS
    BEGIN
        OPEN p_resultado FOR
            SELECT id_tipo_gestion,
                   descripcion
            FROM tipo_gestion
            ORDER BY id_tipo_gestion;

    EXCEPTION
        WHEN OTHERS THEN
            RAISE_APPLICATION_ERROR(-21710, 'Error al listar tipos gestion: ' || SQLERRM);
    END sp_listar_tipos_gestion;

END pkg_tipo_gestion;
/
SHOW ERRORS;

-- -------------------- END PACKAGE BODY ------------------

/*
PACKAGE: PKG_METODOS_PAGO

Paquete para administrar la tabla metodos_pago
*/

CREATE OR REPLACE PACKAGE M_HAMILTON_STORE.pkg_metodos_pago AS

/* Cursor para devolver consultas */
    TYPE t_cursor IS REF CURSOR;

/*
Funcion para verificar si un metodo de pago existe
*/
    FUNCTION fn_existe_metodo_pago(
        p_id_metodo_pago IN NUMBER
    ) RETURN NUMBER;

/*
CRUD DE METODOS_PAGO
*/
    PROCEDURE sp_insertar_metodo_pago(
        p_nombre IN VARCHAR2
    );

    PROCEDURE sp_actualizar_metodo_pago(
        p_id_metodo_pago IN NUMBER,
        p_nombre         IN VARCHAR2
    );

    PROCEDURE sp_eliminar_metodo_pago(
        p_id_metodo_pago IN NUMBER
    );

    PROCEDURE sp_obtener_metodo_pago(
        p_id_metodo_pago IN NUMBER,
        p_resultado      OUT t_cursor
    );

    PROCEDURE sp_listar_metodos_pago(
        p_resultado OUT t_cursor
    );

END pkg_metodos_pago;
/
SHOW ERRORS;

-- ---------------- END CRUDS METODOS_PAGO ------------------

/*
PACKAGE BODY: PKG_METODOS_PAGO
*/
CREATE OR REPLACE PACKAGE BODY M_HAMILTON_STORE.pkg_metodos_pago AS

/*
Funcion para validar si un texto viene vacio
*/
    FUNCTION fn_texto_vacio(
        p_texto IN VARCHAR2
    ) RETURN BOOLEAN
    IS
    BEGIN
        RETURN p_texto IS NULL OR TRIM(p_texto) IS NULL;
    END fn_texto_vacio;

/*
Funcion publica para saber si existe un metodo de pago
*/
    FUNCTION fn_existe_metodo_pago(
        p_id_metodo_pago IN NUMBER
    ) RETURN NUMBER
    IS
        v_count NUMBER;
    BEGIN
        SELECT COUNT(*)
        INTO v_count
        FROM metodos_pago
        WHERE id_metodo_pago = p_id_metodo_pago;

        RETURN v_count;
    END fn_existe_metodo_pago;

/*
INSERTAR METODO_PAGO
*/
    PROCEDURE sp_insertar_metodo_pago(
        p_nombre IN VARCHAR2
    )
    IS
    BEGIN
        IF fn_texto_vacio(p_nombre) THEN
            RAISE_APPLICATION_ERROR(-21801, 'El nombre es obligatorio.');
        END IF;

        INSERT INTO metodos_pago (
            nombre
        )
        VALUES (
            TRIM(p_nombre)
        );

    EXCEPTION
        WHEN OTHERS THEN
            RAISE_APPLICATION_ERROR(-21802, 'Error al insertar metodo pago: ' || SQLERRM);
    END sp_insertar_metodo_pago;

/*
ACTUALIZAR METODO_PAGO
*/
    PROCEDURE sp_actualizar_metodo_pago(
        p_id_metodo_pago IN NUMBER,
        p_nombre         IN VARCHAR2
    )
    IS
    BEGIN
        IF fn_existe_metodo_pago(p_id_metodo_pago) = 0 THEN
            RAISE_APPLICATION_ERROR(-21803, 'El metodo de pago indicado no existe.');
        END IF;

        IF fn_texto_vacio(p_nombre) THEN
            RAISE_APPLICATION_ERROR(-21804, 'El nombre es obligatorio.');
        END IF;

        UPDATE metodos_pago
        SET nombre = TRIM(p_nombre)
        WHERE id_metodo_pago = p_id_metodo_pago;

    EXCEPTION
        WHEN OTHERS THEN
            RAISE_APPLICATION_ERROR(-21805, 'Error al actualizar metodo pago: ' || SQLERRM);
    END sp_actualizar_metodo_pago;

/*
ELIMINAR METODO_PAGO
*/
    PROCEDURE sp_eliminar_metodo_pago(
        p_id_metodo_pago IN NUMBER
    )
    IS
    BEGIN
        IF fn_existe_metodo_pago(p_id_metodo_pago) = 0 THEN
            RAISE_APPLICATION_ERROR(-21806, 'El metodo de pago indicado no existe.');
        END IF;

        DELETE FROM metodos_pago
        WHERE id_metodo_pago = p_id_metodo_pago;

    EXCEPTION
        WHEN OTHERS THEN
            RAISE_APPLICATION_ERROR(-21807, 'Error al eliminar metodo pago: ' || SQLERRM);
    END sp_eliminar_metodo_pago;

/*
OBTENER METODO_PAGO POR ID
*/
    PROCEDURE sp_obtener_metodo_pago(
        p_id_metodo_pago IN NUMBER,
        p_resultado      OUT t_cursor
    )
    IS
    BEGIN
        IF fn_existe_metodo_pago(p_id_metodo_pago) = 0 THEN
            RAISE_APPLICATION_ERROR(-21808, 'El metodo de pago indicado no existe.');
        END IF;

        OPEN p_resultado FOR
            SELECT id_metodo_pago,
                   nombre
            FROM metodos_pago
            WHERE id_metodo_pago = p_id_metodo_pago;

    EXCEPTION
        WHEN OTHERS THEN
            RAISE_APPLICATION_ERROR(-21809, 'Error al consultar metodo pago: ' || SQLERRM);
    END sp_obtener_metodo_pago;

/*
LISTAR METODOS_PAGO
*/
    PROCEDURE sp_listar_metodos_pago(
        p_resultado OUT t_cursor
    )
    IS
    BEGIN
        OPEN p_resultado FOR
            SELECT id_metodo_pago,
                   nombre
            FROM metodos_pago
            ORDER BY id_metodo_pago;

    EXCEPTION
        WHEN OTHERS THEN
            RAISE_APPLICATION_ERROR(-21810, 'Error al listar metodos pago: ' || SQLERRM);
    END sp_listar_metodos_pago;

END pkg_metodos_pago;
/
SHOW ERRORS;

-- -------------------- END PACKAGE BODY ------------------

/*
PACKAGE: PKG_PROVINCIAS

Paquete para administrar la tabla provincias
*/

CREATE OR REPLACE PACKAGE M_HAMILTON_STORE.pkg_provincias AS

/* Cursor para devolver consultas */
    TYPE t_cursor IS REF CURSOR;

/*
Funcion para verificar si una provincia existe
*/
    FUNCTION fn_existe_provincia(
        p_id_provincia IN NUMBER
    ) RETURN NUMBER;

/*
CRUD DE PROVINCIAS
*/
    PROCEDURE sp_insertar_provincia(
        p_nombre IN VARCHAR2
    );

    PROCEDURE sp_actualizar_provincia(
        p_id_provincia IN NUMBER,
        p_nombre       IN VARCHAR2
    );

    PROCEDURE sp_eliminar_provincia(
        p_id_provincia IN NUMBER
    );

    PROCEDURE sp_obtener_provincia(
        p_id_provincia IN NUMBER,
        p_resultado    OUT t_cursor
    );

    PROCEDURE sp_listar_provincias(
        p_resultado OUT t_cursor
    );

END pkg_provincias;
/
SHOW ERRORS;


-- ---------------- END CRUDS PROVINCIAS ------------------

/*
PACKAGE BODY: PKG_PROVINCIAS
*/
CREATE OR REPLACE PACKAGE BODY M_HAMILTON_STORE.pkg_provincias AS

/*
Funcion para validar si un texto viene vacio
*/
    FUNCTION fn_texto_vacio(
        p_texto IN VARCHAR2
    ) RETURN BOOLEAN
    IS
    BEGIN
        RETURN p_texto IS NULL OR TRIM(p_texto) IS NULL;
    END fn_texto_vacio;

/*
Funcion publica para saber si existe una provincia
*/
    FUNCTION fn_existe_provincia(
        p_id_provincia IN NUMBER
    ) RETURN NUMBER
    IS
        v_count NUMBER;
    BEGIN
        SELECT COUNT(*)
        INTO v_count
        FROM provincias
        WHERE id_provincia = p_id_provincia;

        RETURN v_count;
    END fn_existe_provincia;

/*
INSERTAR PROVINCIA
*/
    PROCEDURE sp_insertar_provincia(
        p_nombre IN VARCHAR2
    )
    IS
    BEGIN
        IF fn_texto_vacio(p_nombre) THEN
            RAISE_APPLICATION_ERROR(-21901, 'El nombre es obligatorio.');
        END IF;

        INSERT INTO provincias (
            nombre
        )
        VALUES (
            TRIM(p_nombre)
        );

    EXCEPTION
        WHEN OTHERS THEN
            RAISE_APPLICATION_ERROR(-21902, 'Error al insertar provincia: ' || SQLERRM);
    END sp_insertar_provincia;

/*
ACTUALIZAR PROVINCIA
*/
    PROCEDURE sp_actualizar_provincia(
        p_id_provincia IN NUMBER,
        p_nombre       IN VARCHAR2
    )
    IS
    BEGIN
        IF fn_existe_provincia(p_id_provincia) = 0 THEN
            RAISE_APPLICATION_ERROR(-21903, 'La provincia indicada no existe.');
        END IF;

        IF fn_texto_vacio(p_nombre) THEN
            RAISE_APPLICATION_ERROR(-21904, 'El nombre es obligatorio.');
        END IF;

        UPDATE provincias
        SET nombre = TRIM(p_nombre)
        WHERE id_provincia = p_id_provincia;

    EXCEPTION
        WHEN OTHERS THEN
            RAISE_APPLICATION_ERROR(-21905, 'Error al actualizar provincia: ' || SQLERRM);
    END sp_actualizar_provincia;

/*
ELIMINAR PROVINCIA
*/
    PROCEDURE sp_eliminar_provincia(
        p_id_provincia IN NUMBER
    )
    IS
    BEGIN
        IF fn_existe_provincia(p_id_provincia) = 0 THEN
            RAISE_APPLICATION_ERROR(-21906, 'La provincia indicada no existe.');
        END IF;

        DELETE FROM provincias
        WHERE id_provincia = p_id_provincia;

    EXCEPTION
        WHEN OTHERS THEN
            RAISE_APPLICATION_ERROR(-21907, 'Error al eliminar provincia: ' || SQLERRM);
    END sp_eliminar_provincia;

/*
OBTENER PROVINCIA POR ID
*/
    PROCEDURE sp_obtener_provincia(
        p_id_provincia IN NUMBER,
        p_resultado    OUT t_cursor
    )
    IS
    BEGIN
        IF fn_existe_provincia(p_id_provincia) = 0 THEN
            RAISE_APPLICATION_ERROR(-21908, 'La provincia indicada no existe.');
        END IF;

        OPEN p_resultado FOR
            SELECT id_provincia,
                   nombre
            FROM provincias
            WHERE id_provincia = p_id_provincia;

    EXCEPTION
        WHEN OTHERS THEN
            RAISE_APPLICATION_ERROR(-21909, 'Error al consultar provincia: ' || SQLERRM);
    END sp_obtener_provincia;

/*
LISTAR PROVINCIAS
*/
    PROCEDURE sp_listar_provincias(
        p_resultado OUT t_cursor
    )
    IS
    BEGIN
        OPEN p_resultado FOR
            SELECT id_provincia,
                   nombre
            FROM provincias
            ORDER BY id_provincia;

    EXCEPTION
        WHEN OTHERS THEN
            RAISE_APPLICATION_ERROR(-21910, 'Error al listar provincias: ' || SQLERRM);
    END sp_listar_provincias;

END pkg_provincias;
/
SHOW ERRORS;



-- -------------------- END PACKAGE BODY ------------------

/*
PACKAGE: PKG_CANTONES

Paquete para administrar la tabla cantones
*/

CREATE OR REPLACE PACKAGE M_HAMILTON_STORE.pkg_cantones AS

/* Cursor para devolver consultas */
    TYPE t_cursor IS REF CURSOR;

/*
Funcion para verificar si un canton existe
*/
    FUNCTION fn_existe_canton(
        p_id_canton IN NUMBER
    ) RETURN NUMBER;

/*
CRUD DE CANTONES
*/
    PROCEDURE sp_insertar_canton(
        p_nombre                  IN VARCHAR2,
        p_provincias_id_provincia IN NUMBER
    );

    PROCEDURE sp_actualizar_canton(
        p_id_canton               IN NUMBER,
        p_nombre                  IN VARCHAR2,
        p_provincias_id_provincia IN NUMBER
    );

    PROCEDURE sp_eliminar_canton(
        p_id_canton IN NUMBER
    );

    PROCEDURE sp_obtener_canton(
        p_id_canton IN NUMBER,
        p_resultado OUT t_cursor
    );

    PROCEDURE sp_listar_cantones(
        p_resultado OUT t_cursor
    );

END pkg_cantones;
/
SHOW ERRORS;

-- ---------------- END CRUDS CANTONES ------------------

/*
PACKAGE BODY: PKG_CANTONES
*/
CREATE OR REPLACE PACKAGE BODY M_HAMILTON_STORE.pkg_cantones AS

/*
Funcion para validar si un texto viene vacio
*/
    FUNCTION fn_texto_vacio(
        p_texto IN VARCHAR2
    ) RETURN BOOLEAN
    IS
    BEGIN
        RETURN p_texto IS NULL OR TRIM(p_texto) IS NULL;
    END fn_texto_vacio;

/*
Funcion publica para saber si existe un canton
*/
    FUNCTION fn_existe_canton(
        p_id_canton IN NUMBER
    ) RETURN NUMBER
    IS
        v_count NUMBER;
    BEGIN
        SELECT COUNT(*)
        INTO v_count
        FROM cantones
        WHERE id_canton = p_id_canton;

        RETURN v_count;
    END fn_existe_canton;

/*
INSERTAR CANTON
*/
    PROCEDURE sp_insertar_canton(
        p_nombre                  IN VARCHAR2,
        p_provincias_id_provincia IN NUMBER
    )
    IS
        v_count NUMBER;
    BEGIN
        IF fn_texto_vacio(p_nombre) THEN
            RAISE_APPLICATION_ERROR(-22001, 'El nombre es obligatorio.');
        END IF;

        SELECT COUNT(*)
        INTO v_count
        FROM provincias
        WHERE id_provincia = p_provincias_id_provincia;

        IF v_count = 0 THEN
            RAISE_APPLICATION_ERROR(-22002, 'La provincia indicada no existe.');
        END IF;

        INSERT INTO cantones (
            nombre,
            provincias_id_provincia
        )
        VALUES (
            TRIM(p_nombre),
            p_provincias_id_provincia
        );

    EXCEPTION
        WHEN OTHERS THEN
            RAISE_APPLICATION_ERROR(-22003, 'Error al insertar canton: ' || SQLERRM);
    END sp_insertar_canton;

/*
ACTUALIZAR CANTON
*/
    PROCEDURE sp_actualizar_canton(
        p_id_canton               IN NUMBER,
        p_nombre                  IN VARCHAR2,
        p_provincias_id_provincia IN NUMBER
    )
    IS
        v_count NUMBER;
    BEGIN
        IF fn_existe_canton(p_id_canton) = 0 THEN
            RAISE_APPLICATION_ERROR(-22004, 'El canton indicado no existe.');
        END IF;

        IF fn_texto_vacio(p_nombre) THEN
            RAISE_APPLICATION_ERROR(-22005, 'El nombre es obligatorio.');
        END IF;

        SELECT COUNT(*)
        INTO v_count
        FROM provincias
        WHERE id_provincia = p_provincias_id_provincia;

        IF v_count = 0 THEN
            RAISE_APPLICATION_ERROR(-22006, 'La provincia indicada no existe.');
        END IF;

        UPDATE cantones
        SET nombre = TRIM(p_nombre),
            provincias_id_provincia = p_provincias_id_provincia
        WHERE id_canton = p_id_canton;

    EXCEPTION
        WHEN OTHERS THEN
            RAISE_APPLICATION_ERROR(-22007, 'Error al actualizar canton: ' || SQLERRM);
    END sp_actualizar_canton;

/*
ELIMINAR CANTON
*/
    PROCEDURE sp_eliminar_canton(
        p_id_canton IN NUMBER
    )
    IS
    BEGIN
        IF fn_existe_canton(p_id_canton) = 0 THEN
            RAISE_APPLICATION_ERROR(-22008, 'El canton indicado no existe.');
        END IF;

        DELETE FROM cantones
        WHERE id_canton = p_id_canton;

    EXCEPTION
        WHEN OTHERS THEN
            RAISE_APPLICATION_ERROR(-22009, 'Error al eliminar canton: ' || SQLERRM);
    END sp_eliminar_canton;

/*
OBTENER CANTON POR ID
*/
    PROCEDURE sp_obtener_canton(
        p_id_canton IN NUMBER,
        p_resultado OUT t_cursor
    )
    IS
    BEGIN
        IF fn_existe_canton(p_id_canton) = 0 THEN
            RAISE_APPLICATION_ERROR(-22010, 'El canton indicado no existe.');
        END IF;

        OPEN p_resultado FOR
            SELECT c.id_canton,
                   c.nombre,
                   c.provincias_id_provincia,
                   p.nombre AS provincia
            FROM cantones c
            JOIN provincias p
              ON c.provincias_id_provincia = p.id_provincia
            WHERE c.id_canton = p_id_canton;

    EXCEPTION
        WHEN OTHERS THEN
            RAISE_APPLICATION_ERROR(-22011, 'Error al consultar canton: ' || SQLERRM);
    END sp_obtener_canton;

/*
LISTAR CANTONES
*/
    PROCEDURE sp_listar_cantones(
        p_resultado OUT t_cursor
    )
    IS
    BEGIN
        OPEN p_resultado FOR
            SELECT c.id_canton,
                   c.nombre,
                   c.provincias_id_provincia,
                   p.nombre AS provincia
            FROM cantones c
            JOIN provincias p
              ON c.provincias_id_provincia = p.id_provincia
            ORDER BY c.id_canton;

    EXCEPTION
        WHEN OTHERS THEN
            RAISE_APPLICATION_ERROR(-22012, 'Error al listar cantones: ' || SQLERRM);
    END sp_listar_cantones;

END pkg_cantones;
/
SHOW ERRORS;

-- -------------------- END PACKAGE BODY ------------------


/*
PACKAGE: PKG_DISTRITOS

Paquete para administrar la tabla distritos
*/

CREATE OR REPLACE PACKAGE M_HAMILTON_STORE.pkg_distritos AS

/* Cursor para devolver consultas */
    TYPE t_cursor IS REF CURSOR;

/*
Funcion para verificar si un distrito existe
*/
    FUNCTION fn_existe_distrito(
        p_id_distrito IN NUMBER
    ) RETURN NUMBER;

/*
CRUD DE DISTRITOS
*/
    PROCEDURE sp_insertar_distrito(
        p_nombre             IN VARCHAR2,
        p_cantones_id_canton IN NUMBER,
        p_codigo_postal      IN NUMBER
    );

    PROCEDURE sp_actualizar_distrito(
        p_id_distrito        IN NUMBER,
        p_nombre             IN VARCHAR2,
        p_cantones_id_canton IN NUMBER,
        p_codigo_postal      IN NUMBER
    );

    PROCEDURE sp_eliminar_distrito(
        p_id_distrito IN NUMBER
    );

    PROCEDURE sp_obtener_distrito(
        p_id_distrito IN NUMBER,
        p_resultado   OUT t_cursor
    );

    PROCEDURE sp_listar_distritos(
        p_resultado OUT t_cursor
    );

END pkg_distritos;
/
SHOW ERRORS;

-- ---------------- END CRUDS DISTRITOS ------------------


/*
PACKAGE BODY: PKG_DISTRITOS
*/
CREATE OR REPLACE PACKAGE BODY M_HAMILTON_STORE.pkg_distritos AS

/*
Funcion para validar si un texto viene vacio
*/
    FUNCTION fn_texto_vacio(
        p_texto IN VARCHAR2
    ) RETURN BOOLEAN
    IS
    BEGIN
        RETURN p_texto IS NULL OR TRIM(p_texto) IS NULL;
    END fn_texto_vacio;

/*
Funcion publica para saber si existe un distrito
*/
    FUNCTION fn_existe_distrito(
        p_id_distrito IN NUMBER
    ) RETURN NUMBER
    IS
        v_count NUMBER;
    BEGIN
        SELECT COUNT(*)
        INTO v_count
        FROM distritos
        WHERE id_distrito = p_id_distrito;

        RETURN v_count;
    END fn_existe_distrito;

/*
INSERTAR DISTRITO
*/
    PROCEDURE sp_insertar_distrito(
        p_nombre             IN VARCHAR2,
        p_cantones_id_canton IN NUMBER,
        p_codigo_postal      IN NUMBER
    )
    IS
        v_count NUMBER;
    BEGIN
        IF fn_texto_vacio(p_nombre) THEN
            RAISE_APPLICATION_ERROR(-22101, 'El nombre es obligatorio.');
        END IF;

        SELECT COUNT(*)
        INTO v_count
        FROM cantones
        WHERE id_canton = p_cantones_id_canton;

        IF v_count = 0 THEN
            RAISE_APPLICATION_ERROR(-22102, 'El canton indicado no existe.');
        END IF;

        INSERT INTO distritos (
            nombre,
            cantones_id_canton,
            codigo_postal
        )
        VALUES (
            TRIM(p_nombre),
            p_cantones_id_canton,
            p_codigo_postal
        );

    EXCEPTION
        WHEN OTHERS THEN
            RAISE_APPLICATION_ERROR(-22103, 'Error al insertar distrito: ' || SQLERRM);
    END sp_insertar_distrito;

/*
ACTUALIZAR DISTRITO
*/
    PROCEDURE sp_actualizar_distrito(
        p_id_distrito        IN NUMBER,
        p_nombre             IN VARCHAR2,
        p_cantones_id_canton IN NUMBER,
        p_codigo_postal      IN NUMBER
    )
    IS
        v_count NUMBER;
    BEGIN
        IF fn_existe_distrito(p_id_distrito) = 0 THEN
            RAISE_APPLICATION_ERROR(-22104, 'El distrito indicado no existe.');
        END IF;

        IF fn_texto_vacio(p_nombre) THEN
            RAISE_APPLICATION_ERROR(-22105, 'El nombre es obligatorio.');
        END IF;

        SELECT COUNT(*)
        INTO v_count
        FROM cantones
        WHERE id_canton = p_cantones_id_canton;

        IF v_count = 0 THEN
            RAISE_APPLICATION_ERROR(-22106, 'El canton indicado no existe.');
        END IF;

        UPDATE distritos
        SET nombre = TRIM(p_nombre),
            cantones_id_canton = p_cantones_id_canton,
            codigo_postal = p_codigo_postal
        WHERE id_distrito = p_id_distrito;

    EXCEPTION
        WHEN OTHERS THEN
            RAISE_APPLICATION_ERROR(-22107, 'Error al actualizar distrito: ' || SQLERRM);
    END sp_actualizar_distrito;

/*
ELIMINAR DISTRITO
*/
    PROCEDURE sp_eliminar_distrito(
        p_id_distrito IN NUMBER
    )
    IS
    BEGIN
        IF fn_existe_distrito(p_id_distrito) = 0 THEN
            RAISE_APPLICATION_ERROR(-22108, 'El distrito indicado no existe.');
        END IF;

        DELETE FROM distritos
        WHERE id_distrito = p_id_distrito;

    EXCEPTION
        WHEN OTHERS THEN
            RAISE_APPLICATION_ERROR(-22109, 'Error al eliminar distrito: ' || SQLERRM);
    END sp_eliminar_distrito;

/*
OBTENER DISTRITO POR ID
*/
    PROCEDURE sp_obtener_distrito(
        p_id_distrito IN NUMBER,
        p_resultado   OUT t_cursor
    )
    IS
    BEGIN
        IF fn_existe_distrito(p_id_distrito) = 0 THEN
            RAISE_APPLICATION_ERROR(-22110, 'El distrito indicado no existe.');
        END IF;

        OPEN p_resultado FOR
            SELECT d.id_distrito,
                   d.nombre,
                   d.cantones_id_canton,
                   c.nombre AS canton,
                   d.codigo_postal
            FROM distritos d
            JOIN cantones c
              ON d.cantones_id_canton = c.id_canton
            WHERE d.id_distrito = p_id_distrito;

    EXCEPTION
        WHEN OTHERS THEN
            RAISE_APPLICATION_ERROR(-22111, 'Error al consultar distrito: ' || SQLERRM);
    END sp_obtener_distrito;

/*
LISTAR DISTRITOS
*/
    PROCEDURE sp_listar_distritos(
        p_resultado OUT t_cursor
    )
    IS
    BEGIN
        OPEN p_resultado FOR
            SELECT d.id_distrito,
                   d.nombre,
                   d.cantones_id_canton,
                   c.nombre AS canton,
                   d.codigo_postal
            FROM distritos d
            JOIN cantones c
              ON d.cantones_id_canton = c.id_canton
            ORDER BY d.id_distrito;

    EXCEPTION
        WHEN OTHERS THEN
            RAISE_APPLICATION_ERROR(-22112, 'Error al listar distritos: ' || SQLERRM);
    END sp_listar_distritos;

END pkg_distritos;
/
SHOW ERRORS;

-- -------------------- END PACKAGE BODY ------------------



