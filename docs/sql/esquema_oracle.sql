-- =============================================================================
-- M. Hamilton Store - Esquema Oracle (normalizado)
-- Usuarios unificados (admin, cajero, inventario, cliente) + roles + estados
-- Sin ALTER TABLE: restricciones inline en CREATE TABLE
-- =============================================================================

-- CREACIÓN DEL ESQUEMA
CREATE USER M_HAMILTON_STORE IDENTIFIED BY uFIDELITAS2026;

GRANT RESOURCE TO M_HAMILTON_STORE;
GRANT CREATE SESSION TO M_HAMILTON_STORE;

ALTER USER M_HAMILTON_STORE QUOTA UNLIMITED ON DATA;

-- Conectar como M_HAMILTON_STORE
-- Orden de creación respetando dependencias de FK

-- =============================================================================
-- TABLAS DE REFERENCIA (roles, estados)
-- =============================================================================

CREATE TABLE roles (
    id_rol NUMBER GENERATED ALWAYS AS IDENTITY,
    nombre VARCHAR2(50) NOT NULL,
    CONSTRAINT roles_PK PRIMARY KEY (id_rol)
);

CREATE TABLE estados (
    id_estado NUMBER GENERATED ALWAYS AS IDENTITY,
    nombre    VARCHAR2(50) NOT NULL,
    CONSTRAINT estados_PK PRIMARY KEY (id_estado)
);

-- =============================================================================
-- TABLAS BASE
-- =============================================================================

CREATE TABLE provincias (
    id_provincia NUMBER GENERATED ALWAYS AS IDENTITY,
    nombre       VARCHAR2(20),
    CONSTRAINT provincias_PK PRIMARY KEY (id_provincia)
);

CREATE TABLE categorias (
    id_categoria NUMBER GENERATED ALWAYS AS IDENTITY,
    nombre       VARCHAR2(50) NOT NULL,
    CONSTRAINT categorias_PK PRIMARY KEY (id_categoria)
);

CREATE TABLE clientes (
    id_cliente     NUMBER GENERATED ALWAYS AS IDENTITY,
    nombre         VARCHAR2(50) NOT NULL,
    apellido       VARCHAR2(50) NOT NULL,
    email          VARCHAR2(150) NOT NULL,
    telefono       VARCHAR2(25) NOT NULL,
    fecha_ingreso  DATE NOT NULL,
    estados_id_estado NUMBER NOT NULL,
    CONSTRAINT clientes_PK PRIMARY KEY (id_cliente),
    CONSTRAINT clientes_estados_FK FOREIGN KEY (estados_id_estado) REFERENCES estados (id_estado)
);

CREATE TABLE proveedores (
    id_proveedor NUMBER GENERATED ALWAYS AS IDENTITY,
    nombre       VARCHAR2(150) NOT NULL,
    CONSTRAINT proveedores_PK PRIMARY KEY (id_proveedor)
);

CREATE TABLE empleados (
    id_empleado       NUMBER GENERATED ALWAYS AS IDENTITY,
    nombre            VARCHAR2(50) NOT NULL,
    apellido          VARCHAR2(50) NOT NULL,
    puesto            VARCHAR2(50) NOT NULL,
    email             VARCHAR2(150) NOT NULL,
    fecha_ingreso     DATE NOT NULL,
    estados_id_estado NUMBER NOT NULL,
    CONSTRAINT empleados_PK PRIMARY KEY (id_empleado),
    CONSTRAINT empleados_estados_FK FOREIGN KEY (estados_id_estado) REFERENCES estados (id_estado)
);

CREATE TABLE tipo_gestion (
    id_tipo_gestion NUMBER GENERATED ALWAYS AS IDENTITY,
    descripcion     VARCHAR2(255) NOT NULL,
    CONSTRAINT tipo_gestion_PK PRIMARY KEY (id_tipo_gestion)
);

CREATE TABLE metodos_pago (
    id_metodo_pago NUMBER GENERATED ALWAYS AS IDENTITY,
    nombre         VARCHAR2(50) NOT NULL,
    CONSTRAINT metodos_pago_PK PRIMARY KEY (id_metodo_pago)
);

-- =============================================================================
-- TABLAS CON FK (ubicación)
-- =============================================================================

CREATE TABLE cantones (
    id_canton               NUMBER GENERATED ALWAYS AS IDENTITY,
    nombre                  VARCHAR2(50) NOT NULL,
    provincias_id_provincia NUMBER NOT NULL,
    CONSTRAINT cantones_PK PRIMARY KEY (id_canton),
    CONSTRAINT cantones_provincias_FK FOREIGN KEY (provincias_id_provincia) REFERENCES provincias (id_provincia)
);

CREATE TABLE distritos (
    id_distrito        NUMBER GENERATED ALWAYS AS IDENTITY,
    nombre             VARCHAR2(50) NOT NULL,
    cantones_id_canton NUMBER NOT NULL,
    codigo_postal      NUMBER,
    CONSTRAINT distritos_PK PRIMARY KEY (id_distrito),
    CONSTRAINT distritos_cantones_FK FOREIGN KEY (cantones_id_canton) REFERENCES cantones (id_canton)
);

-- =============================================================================
-- PRODUCTOS
-- =============================================================================

CREATE TABLE productos (
    id_producto             NUMBER GENERATED ALWAYS AS IDENTITY,
    nombre                   VARCHAR2(75) NOT NULL,
    precio_compra           NUMBER(9,2),
    precio_venta            NUMBER(9,2),
    cantidad                NUMBER NOT NULL,
    categorias_id_categoria NUMBER NOT NULL,
    estados_id_estado       NUMBER NOT NULL,
    CONSTRAINT productos_PK PRIMARY KEY (id_producto),
    CONSTRAINT productos_categorias_FK FOREIGN KEY (categorias_id_categoria) REFERENCES categorias (id_categoria),
    CONSTRAINT productos_estados_FK FOREIGN KEY (estados_id_estado) REFERENCES estados (id_estado)
);

-- =============================================================================
-- CONTACTOS Y DIRECCIONES
-- =============================================================================

CREATE TABLE contactos_proveedores (
    id_contacto              NUMBER GENERATED ALWAYS AS IDENTITY,
    nombre                   VARCHAR2(50) NOT NULL,
    apellido                 VARCHAR2(50) NOT NULL,
    email                    VARCHAR2(150) NOT NULL,
    telefono                 VARCHAR2(25) NOT NULL,
    proveedores_id_proveedor NUMBER NOT NULL,
    CONSTRAINT contactos_proveedores_PK PRIMARY KEY (id_contacto),
    CONSTRAINT cnt_prov_proveedor_fk FOREIGN KEY (proveedores_id_proveedor) REFERENCES proveedores (id_proveedor)
);

CREATE TABLE direcciones (
    id_direccion             NUMBER GENERATED ALWAYS AS IDENTITY,
    otras_senas              VARCHAR2(250),
    provincias_id_provincia  NUMBER NOT NULL,
    cantones_id_canton       NUMBER NOT NULL,
    distritos_id_distrito    NUMBER NOT NULL,
    clientes_id_cliente      NUMBER,
    proveedores_id_proveedor NUMBER,
    CONSTRAINT direcciones_PK PRIMARY KEY (id_direccion),
    CONSTRAINT direcciones_provincias_FK FOREIGN KEY (provincias_id_provincia) REFERENCES provincias (id_provincia),
    CONSTRAINT direcciones_cantones_FK FOREIGN KEY (cantones_id_canton) REFERENCES cantones (id_canton),
    CONSTRAINT direcciones_distritos_FK FOREIGN KEY (distritos_id_distrito) REFERENCES distritos (id_distrito),
    CONSTRAINT direcciones_clientes_FK FOREIGN KEY (clientes_id_cliente) REFERENCES clientes (id_cliente),
    CONSTRAINT direcciones_proveedores_FK FOREIGN KEY (proveedores_id_proveedor) REFERENCES proveedores (id_proveedor)
);

-- =============================================================================
-- COMPRAS Y VENTAS
-- =============================================================================

CREATE TABLE encabezados_compras (
    id_compra                NUMBER GENERATED ALWAYS AS IDENTITY,
    fecha_compra             DATE NOT NULL,
    total_compra             NUMBER(9,2) NOT NULL,
    proveedores_id_proveedor NUMBER NOT NULL,
    empleados_id_empleado    NUMBER NOT NULL,
    CONSTRAINT encabezados_compras_PK PRIMARY KEY (id_compra),
    CONSTRAINT encab_compr_prov_fk FOREIGN KEY (proveedores_id_proveedor) REFERENCES proveedores (id_proveedor),
    CONSTRAINT encab_compr_emp_fk FOREIGN KEY (empleados_id_empleado) REFERENCES empleados (id_empleado)
);

CREATE TABLE encabezados_ventas (
    id_venta              NUMBER GENERATED ALWAYS AS IDENTITY,
    fecha_venta           DATE NOT NULL,
    total_venta           NUMBER(9,2) NOT NULL,
    clientes_id_cliente   NUMBER NOT NULL,
    empleados_id_empleado NUMBER NOT NULL,
    CONSTRAINT encabezados_ventas_PK PRIMARY KEY (id_venta),
    CONSTRAINT encabezados_ventas_clientes_FK FOREIGN KEY (clientes_id_cliente) REFERENCES clientes (id_cliente),
    CONSTRAINT encab_vent_emp_fk FOREIGN KEY (empleados_id_empleado) REFERENCES empleados (id_empleado)
);

CREATE TABLE detalles_compras (
    id_detalle_compra             NUMBER GENERATED ALWAYS AS IDENTITY,
    cantidad                      NUMBER NOT NULL,
    precio_unitario               NUMBER(9,2) NOT NULL,
    encabezados_compras_id_compra NUMBER NOT NULL,
    productos_id_producto         NUMBER NOT NULL,
    CONSTRAINT detalles_compras_PK PRIMARY KEY (id_detalle_compra),
    CONSTRAINT det_compr_encab_fk FOREIGN KEY (encabezados_compras_id_compra) REFERENCES encabezados_compras (id_compra),
    CONSTRAINT detalles_compras_productos_FK FOREIGN KEY (productos_id_producto) REFERENCES productos (id_producto)
);

CREATE TABLE detalles_ventas (
    id_detalle_venta            NUMBER GENERATED ALWAYS AS IDENTITY,
    cantidad                   NUMBER NOT NULL,
    precio_unitario            NUMBER(9,2) NOT NULL,
    subtotal                   NUMBER(9,2) NOT NULL,
    encabezados_ventas_id_venta NUMBER NOT NULL,
    productos_id_producto      NUMBER NOT NULL,
    CONSTRAINT detalles_ventas_PK PRIMARY KEY (id_detalle_venta),
    CONSTRAINT det_vent_encab_fk FOREIGN KEY (encabezados_ventas_id_venta) REFERENCES encabezados_ventas (id_venta),
    CONSTRAINT detalles_ventas_productos_FK FOREIGN KEY (productos_id_producto) REFERENCES productos (id_producto)
);

CREATE TABLE pagos (
    id_pago                     NUMBER GENERATED ALWAYS AS IDENTITY,
    monto                       NUMBER(9,2) NOT NULL,
    fecha_pago                  DATE NOT NULL,
    metodos_pago_id_metodo_pago NUMBER NOT NULL,
    encabezados_ventas_id_venta NUMBER NOT NULL,
    CONSTRAINT pagos_PK PRIMARY KEY (id_pago),
    CONSTRAINT pagos_metodos_pago_FK FOREIGN KEY (metodos_pago_id_metodo_pago) REFERENCES metodos_pago (id_metodo_pago),
    CONSTRAINT pagos_encabezados_ventas_FK FOREIGN KEY (encabezados_ventas_id_venta) REFERENCES encabezados_ventas (id_venta)
);

-- =============================================================================
-- GESTIÓN DE INVENTARIO
-- =============================================================================

CREATE TABLE gestion_stock (
    id_gestion_stock             NUMBER GENERATED ALWAYS AS IDENTITY,
    cantidad                     NUMBER NOT NULL,
    fecha_gestion                DATE NOT NULL,
    productos_id_producto        NUMBER NOT NULL,
    tipo_gestion_id_tipo_gestion NUMBER NOT NULL,
    CONSTRAINT gestion_stock_PK PRIMARY KEY (id_gestion_stock),
    CONSTRAINT gestion_stock_productos_FK FOREIGN KEY (productos_id_producto) REFERENCES productos (id_producto),
    CONSTRAINT gestion_stock_tipo_gestion_FK FOREIGN KEY (tipo_gestion_id_tipo_gestion) REFERENCES tipo_gestion (id_tipo_gestion)
);

-- =============================================================================
-- USUARIOS UNIFICADOS (cliente, cajero, inventario, admin)
-- Vinculado a empleado O cliente según el rol
-- =============================================================================

CREATE TABLE usuarios (
    id_usuario              NUMBER GENERATED ALWAYS AS IDENTITY,
    username                VARCHAR2(50) NOT NULL,
    password_encriptado     VARCHAR2(255) NOT NULL,
    roles_id_rol            NUMBER NOT NULL,
    estados_id_estado       NUMBER NOT NULL,
    empleados_id_empleado   NUMBER,
    clientes_id_cliente     NUMBER,
    CONSTRAINT usuarios_PK PRIMARY KEY (id_usuario),
    CONSTRAINT usuarios_username_UN UNIQUE (username),
    CONSTRAINT usuarios_roles_FK FOREIGN KEY (roles_id_rol) REFERENCES roles (id_rol),
    CONSTRAINT usuarios_estados_FK FOREIGN KEY (estados_id_estado) REFERENCES estados (id_estado),
    CONSTRAINT usuarios_empleados_FK FOREIGN KEY (empleados_id_empleado) REFERENCES empleados (id_empleado),
    CONSTRAINT usuarios_clientes_FK FOREIGN KEY (clientes_id_cliente) REFERENCES clientes (id_cliente),
    CONSTRAINT usuarios_emp_o_cli_chk CHECK (
        (empleados_id_empleado IS NOT NULL AND clientes_id_cliente IS NULL) OR
        (empleados_id_empleado IS NULL AND clientes_id_cliente IS NOT NULL)
    ),
    CONSTRAINT usuarios_emp_un UNIQUE (empleados_id_empleado),
    CONSTRAINT usuarios_cli_un UNIQUE (clientes_id_cliente)
);

-- =============================================================================
-- DATOS INICIALES (roles y estados)
-- =============================================================================

INSERT INTO roles (nombre) VALUES ('admin');
INSERT INTO roles (nombre) VALUES ('cajero');
INSERT INTO roles (nombre) VALUES ('inventario');
INSERT INTO roles (nombre) VALUES ('cliente');

INSERT INTO estados (nombre) VALUES ('activo');
INSERT INTO estados (nombre) VALUES ('inactivo');
