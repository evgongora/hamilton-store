ALTER SESSION SET CURRENT_SCHEMA = M_HAMILTON_STORE;

-------------- Inserts de las tablas ------------
-- ---------------
-- TABLAS DE REFERENCIA (roles, estados)
-- ----------------
-- ROLES
INSERT INTO roles (nombre) VALUES ('ADMIN');
INSERT INTO roles (nombre) VALUES ('CAJERO');
INSERT INTO roles (nombre) VALUES ('INVENTARIO');
INSERT INTO roles (nombre) VALUES ('CLIENTE');
INSERT INTO roles (nombre) VALUES ('SOPORTE');

-- ESTADOS
INSERT INTO estados (nombre) VALUES ('ACTIVO');
INSERT INTO estados (nombre) VALUES ('INACTIVO');
INSERT INTO estados (nombre) VALUES ('PENDIENTE');
INSERT INTO estados (nombre) VALUES ('SUSPENDIDO');
INSERT INTO estados (nombre) VALUES ('CANCELADO');
INSERT INTO estados (nombre) VALUES ('AGOTADO');
INSERT INTO estados (nombre) VALUES ('PROCESADO');

COMMIT;

-- ----------------
-- TABLAS BASE
-- ----------------

-- CATEGORIAS
INSERT INTO categorias (nombre) VALUES ('Laptops');
INSERT INTO categorias (nombre) VALUES ('PC de Escritorio');
INSERT INTO categorias (nombre) VALUES ('Tarjetas Graficas');
INSERT INTO categorias (nombre) VALUES ('Procesadores');
INSERT INTO categorias (nombre) VALUES ('Motherboards');
INSERT INTO categorias (nombre) VALUES ('Memorias RAM');
INSERT INTO categorias (nombre) VALUES ('Almacenamiento SSD');
INSERT INTO categorias (nombre) VALUES ('Monitores');
INSERT INTO categorias (nombre) VALUES ('Perifericos');
INSERT INTO categorias (nombre) VALUES ('Networking');
INSERT INTO categorias (nombre) VALUES ('Sillas Gamer');
INSERT INTO categorias (nombre) VALUES ('Accesorios');


-- CLIENTES
INSERT INTO clientes (nombre, apellido, email, fecha_ingreso, estados_id_estado)
SELECT 'Daniel', 'Chaves', 'daniel.chaves@gmail.com', DATE '2026-01-10', id_estado
FROM estados
WHERE nombre = 'ACTIVO';

INSERT INTO clientes (nombre, apellido, email, fecha_ingreso, estados_id_estado)
SELECT 'Mariana', 'Vargas', 'mariana.vargas@hotmail.com', DATE '2026-01-12', id_estado
FROM estados
WHERE nombre = 'ACTIVO';

INSERT INTO clientes (nombre, apellido, email, fecha_ingreso, estados_id_estado)
SELECT 'José', 'Mora', 'josemora.cr@gmail.com', DATE '2026-01-15', id_estado
FROM estados
WHERE nombre = 'ACTIVO';

INSERT INTO clientes (nombre, apellido, email, fecha_ingreso, estados_id_estado)
SELECT 'Sofía', 'Rojas', 'sofia.rojas@outlook.com', DATE '2026-01-18', id_estado
FROM estados
WHERE nombre = 'ACTIVO';

INSERT INTO clientes (nombre, apellido, email, fecha_ingreso, estados_id_estado)
SELECT 'Andrés', 'Castro', 'andres.castro@gmail.com', DATE '2026-01-20', id_estado
FROM estados
WHERE nombre = 'ACTIVO';

INSERT INTO clientes (nombre, apellido, email, fecha_ingreso, estados_id_estado)
SELECT 'Valeria', 'Jiménez', 'valeria.jimenez.cr@gmail.com', DATE '2026-01-22', id_estado
FROM estados
WHERE nombre = 'ACTIVO';

INSERT INTO clientes (nombre, apellido, email, fecha_ingreso, estados_id_estado)
SELECT 'Kevin', 'Araya', 'kevin.araya@outlook.com', DATE '2026-01-25', id_estado
FROM estados
WHERE nombre = 'ACTIVO';

INSERT INTO clientes (nombre, apellido, email, fecha_ingreso, estados_id_estado)
SELECT 'Paula', 'Solano', 'paula.solano@gmail.com', DATE '2026-02-01', id_estado
FROM estados
WHERE nombre = 'ACTIVO';

INSERT INTO clientes (nombre, apellido, email, fecha_ingreso, estados_id_estado)
SELECT 'Esteban', 'Ramírez', 'esteban.ramirez@hotmail.com', DATE '2026-02-04', id_estado
FROM estados
WHERE nombre = 'ACTIVO';

INSERT INTO clientes (nombre, apellido, email, fecha_ingreso, estados_id_estado)
SELECT 'Natalia', 'Cordero', 'natalia.cordero@gmail.com', DATE '2026-02-08', id_estado
FROM estados
WHERE nombre = 'INACTIVO';


-- PROVEEDORES
INSERT INTO proveedores (nombre, cedula_juridica, pagina_web, estados_id_estado)
SELECT 'Distribuidora Tecnologica Central S.A.', '3-101-458721', 'www.dtc.cr', id_estado
FROM estados
WHERE nombre = 'ACTIVO';

INSERT INTO proveedores (nombre, cedula_juridica, pagina_web, estados_id_estado)
SELECT 'Importadora Digital Tica S.R.L.', '3-102-367894', 'www.idt.cr', id_estado
FROM estados
WHERE nombre = 'ACTIVO';

INSERT INTO proveedores (nombre, cedula_juridica, pagina_web, estados_id_estado)
SELECT 'Soluciones Gamer de Costa Rica S.A.', '3-101-552318', 'www.sgcr.cr', id_estado
FROM estados
WHERE nombre = 'ACTIVO';

INSERT INTO proveedores (nombre, cedula_juridica, pagina_web, estados_id_estado)
SELECT 'Mayorista de Componentes del Valle S.A.', '3-101-621457', 'www.mcv.cr', id_estado
FROM estados
WHERE nombre = 'ACTIVO';

INSERT INTO proveedores (nombre, cedula_juridica, pagina_web, estados_id_estado)
SELECT 'Redes y Accesorios Empresariales S.A.', '3-101-734285', 'www.rae.cr', id_estado
FROM estados
WHERE nombre = 'ACTIVO';

INSERT INTO proveedores (nombre, cedula_juridica, pagina_web, estados_id_estado)
SELECT 'OfiTech Suministros Tecnologicos S.A.', '3-101-845612', 'www.ofitech.cr', id_estado
FROM estados
WHERE nombre = 'ACTIVO';

INSERT INTO proveedores (nombre, cedula_juridica, pagina_web, estados_id_estado)
SELECT 'Electronica del Pacifico CR S.A.', '3-101-918274', 'www.epacifico.cr', id_estado
FROM estados
WHERE nombre = 'INACTIVO';


-- EMPLEADOS
INSERT INTO empleados (nombre, apellido, puesto, email, fecha_ingreso, estados_id_estado)
SELECT 'Javier', 'Hernández', 'Administrador', 'javier.hernandez@mhamiltonstore.cr', DATE '2025-11-01', id_estado
FROM estados
WHERE nombre = 'ACTIVO';

INSERT INTO empleados (nombre, apellido, puesto, email, fecha_ingreso, estados_id_estado)
SELECT 'Camila', 'Méndez', 'Cajero', 'camila.mendez@mhamiltonstore.cr', DATE '2025-11-10', id_estado
FROM estados
WHERE nombre = 'ACTIVO';

INSERT INTO empleados (nombre, apellido, puesto, email, fecha_ingreso, estados_id_estado)
SELECT 'Luis', 'Quesada', 'Encargado de Inventario', 'luis.quesada@mhamiltonstore.cr', DATE '2025-11-15', id_estado
FROM estados
WHERE nombre = 'ACTIVO';

INSERT INTO empleados (nombre, apellido, puesto, email, fecha_ingreso, estados_id_estado)
SELECT 'Fernanda', 'Salas', 'Asesora de Ventas', 'fernanda.salas@mhamiltonstore.cr', DATE '2025-12-01', id_estado
FROM estados
WHERE nombre = 'ACTIVO';

INSERT INTO empleados (nombre, apellido, puesto, email, fecha_ingreso, estados_id_estado)
SELECT 'Bryan', 'Elizondo', 'Soporte Tecnico', 'bryan.elizondo@mhamiltonstore.cr', DATE '2025-12-05', id_estado
FROM estados
WHERE nombre = 'ACTIVO';

INSERT INTO empleados (nombre, apellido, puesto, email, fecha_ingreso, estados_id_estado)
SELECT 'Melissa', 'Acuña', 'Facturacion', 'melissa.acuna@mhamiltonstore.cr', DATE '2025-12-12', id_estado
FROM estados
WHERE nombre = 'ACTIVO';

INSERT INTO empleados (nombre, apellido, puesto, email, fecha_ingreso, estados_id_estado)
SELECT 'Diego', 'Porras', 'Bodeguero', 'diego.porras@mhamiltonstore.cr', DATE '2026-01-08', id_estado
FROM estados
WHERE nombre = 'INACTIVO';


-- TIPO_GESTION
INSERT INTO tipo_gestion (descripcion) VALUES ('Entrada por compra a proveedor');
INSERT INTO tipo_gestion (descripcion) VALUES ('Salida por venta a cliente');
INSERT INTO tipo_gestion (descripcion) VALUES ('Ajuste manual por inventario');
INSERT INTO tipo_gestion (descripcion) VALUES ('Devolucion de cliente');
INSERT INTO tipo_gestion (descripcion) VALUES ('Devolucion a proveedor');
INSERT INTO tipo_gestion (descripcion) VALUES ('Producto defectuoso');
INSERT INTO tipo_gestion (descripcion) VALUES ('Traslado interno de bodega');
INSERT INTO tipo_gestion (descripcion) VALUES ('Correccion por diferencia de conteo');


-- METODOS_PAGO
INSERT INTO metodos_pago (nombre) VALUES ('SINPE Movil');
INSERT INTO metodos_pago (nombre) VALUES ('Tarjeta Debito');
INSERT INTO metodos_pago (nombre) VALUES ('Tarjeta Credito');
INSERT INTO metodos_pago (nombre) VALUES ('Transferencia Bancaria');
INSERT INTO metodos_pago (nombre) VALUES ('Efectivo');
INSERT INTO metodos_pago (nombre) VALUES ('Deposito Bancario');

COMMIT;

-- ----------------
-- PRODUCTOS
-- ----------------

-- PRODUCTOS - LAPTOPS
INSERT INTO productos (nombre, precio_compra, precio_venta, cantidad, categorias_id_categoria, estados_id_estado)
SELECT 'Laptop HP Pavilion 15 Ryzen 5 16GB RAM 512GB SSD',
       350000, 429900, 8,
       c.id_categoria, e.id_estado
FROM categorias c, estados e
WHERE c.nombre = 'Laptops' AND e.nombre = 'ACTIVO';

INSERT INTO productos (nombre, precio_compra, precio_venta, cantidad, categorias_id_categoria, estados_id_estado)
SELECT 'Laptop ASUS TUF Gaming F15 i5 16GB RTX 3050',
       520000, 649900, 5,
       c.id_categoria, e.id_estado
FROM categorias c, estados e
WHERE c.nombre = 'Laptops' AND e.nombre = 'ACTIVO';


-- TARJETAS GRAFICAS
INSERT INTO productos (nombre, precio_compra, precio_venta, cantidad, categorias_id_categoria, estados_id_estado)
SELECT 'NVIDIA RTX 4060 8GB ASUS Dual',
       280000, 339900, 10,
       c.id_categoria, e.id_estado
FROM categorias c, estados e
WHERE c.nombre = 'Tarjetas Graficas' AND e.nombre = 'ACTIVO';

INSERT INTO productos (nombre, precio_compra, precio_venta, cantidad, categorias_id_categoria, estados_id_estado)
SELECT 'AMD Radeon RX 7600 8GB Gigabyte',
       250000, 309900, 7,
       c.id_categoria, e.id_estado
FROM categorias c, estados e
WHERE c.nombre = 'Tarjetas Graficas' AND e.nombre = 'ACTIVO';


-- PROCESADORES
INSERT INTO productos (nombre, precio_compra, precio_venta, cantidad, categorias_id_categoria, estados_id_estado)
SELECT 'Intel Core i5 13400F',
       95000, 129900, 15,
       c.id_categoria, e.id_estado
FROM categorias c, estados e
WHERE c.nombre = 'Procesadores' AND e.nombre = 'ACTIVO';

INSERT INTO productos (nombre, precio_compra, precio_venta, cantidad, categorias_id_categoria, estados_id_estado)
SELECT 'AMD Ryzen 7 5800X',
       120000, 159900, 12,
       c.id_categoria, e.id_estado
FROM categorias c, estados e
WHERE c.nombre = 'Procesadores' AND e.nombre = 'ACTIVO';


-- MEMORIA RAM
INSERT INTO productos (nombre, precio_compra, precio_venta, cantidad, categorias_id_categoria, estados_id_estado)
SELECT 'Corsair Vengeance 16GB DDR4 3200MHz',
       25000, 39900, 25,
       c.id_categoria, e.id_estado
FROM categorias c, estados e
WHERE c.nombre = 'Memorias RAM' AND e.nombre = 'ACTIVO';

INSERT INTO productos (nombre, precio_compra, precio_venta, cantidad, categorias_id_categoria, estados_id_estado)
SELECT 'Kingston Fury 32GB DDR4 3600MHz',
       55000, 74900, 18,
       c.id_categoria, e.id_estado
FROM categorias c, estados e
WHERE c.nombre = 'Memorias RAM' AND e.nombre = 'ACTIVO';

-- SSD
INSERT INTO productos (nombre, precio_compra, precio_venta, cantidad, categorias_id_categoria, estados_id_estado)
SELECT 'SSD Kingston NV2 1TB NVMe',
       35000, 49900, 30,
       c.id_categoria, e.id_estado
FROM categorias c, estados e
WHERE c.nombre = 'Almacenamiento SSD' AND e.nombre = 'ACTIVO';

INSERT INTO productos (nombre, precio_compra, precio_venta, cantidad, categorias_id_categoria, estados_id_estado)
SELECT 'SSD Samsung 980 PRO 1TB NVMe',
       65000, 89900, 12,
       c.id_categoria, e.id_estado
FROM categorias c, estados e
WHERE c.nombre = 'Almacenamiento SSD' AND e.nombre = 'ACTIVO';

-- MONITORES
INSERT INTO productos (nombre, precio_compra, precio_venta, cantidad, categorias_id_categoria, estados_id_estado)
SELECT 'Monitor LG UltraGear 24" 144Hz IPS',
       85000, 119900, 10,
       c.id_categoria, e.id_estado
FROM categorias c, estados e
WHERE c.nombre = 'Monitores' AND e.nombre = 'ACTIVO';

INSERT INTO productos (nombre, precio_compra, precio_venta, cantidad, categorias_id_categoria, estados_id_estado)
SELECT 'Monitor Samsung 27" Curvo 165Hz',
       120000, 159900, 6,
       c.id_categoria, e.id_estado
FROM categorias c, estados e
WHERE c.nombre = 'Monitores' AND e.nombre = 'ACTIVO';

-- PERIFERICOS
INSERT INTO productos (nombre, precio_compra, precio_venta, cantidad, categorias_id_categoria, estados_id_estado)
SELECT 'Teclado Redragon Kumara K552 RGB',
       18000, 29900, 20,
       c.id_categoria, e.id_estado
FROM categorias c, estados e
WHERE c.nombre = 'Perifericos' AND e.nombre = 'ACTIVO';

INSERT INTO productos (nombre, precio_compra, precio_venta, cantidad, categorias_id_categoria, estados_id_estado)
SELECT 'Mouse Logitech G502 Hero',
       22000, 34900, 25,
       c.id_categoria, e.id_estado
FROM categorias c, estados e
WHERE c.nombre = 'Perifericos' AND e.nombre = 'ACTIVO';

-- NETWORKING
INSERT INTO productos (nombre, precio_compra, precio_venta, cantidad, categorias_id_categoria, estados_id_estado)
SELECT 'Router TP-Link AX3000 WiFi 6',
       45000, 69900, 10,
       c.id_categoria, e.id_estado
FROM categorias c, estados e
WHERE c.nombre = 'Networking' AND e.nombre = 'ACTIVO';

-- SILLAS GAMER
INSERT INTO productos (nombre, precio_compra, precio_venta, cantidad, categorias_id_categoria, estados_id_estado)
SELECT 'Silla Gamer Cougar Armor One',
       95000, 139900, 5,
       c.id_categoria, e.id_estado
FROM categorias c, estados e
WHERE c.nombre = 'Sillas Gamer' AND e.nombre = 'ACTIVO';

COMMIT;


-- ----------------
-- TABLAS CON FK DE UBICACION
-- ----------------

-- PROVINCIAS
INSERT INTO provincias (nombre) VALUES ('San José');
INSERT INTO provincias (nombre) VALUES ('Alajuela');
INSERT INTO provincias (nombre) VALUES ('Cartago');
INSERT INTO provincias (nombre) VALUES ('Heredia');
INSERT INTO provincias (nombre) VALUES ('Guanacaste');
INSERT INTO provincias (nombre) VALUES ('Puntarenas');
INSERT INTO provincias (nombre) VALUES ('Limón');

-- CANTONES DE SAN JOSE
INSERT INTO cantones (nombre, provincias_id_provincia) VALUES ('San José', 1);
INSERT INTO cantones (nombre, provincias_id_provincia) VALUES ('Escazú', 1);
INSERT INTO cantones (nombre, provincias_id_provincia) VALUES ('Desamparados', 1);
INSERT INTO cantones (nombre, provincias_id_provincia) VALUES ('Goicoechea', 1);
INSERT INTO cantones (nombre, provincias_id_provincia) VALUES ('Santa Ana', 1);
INSERT INTO cantones (nombre, provincias_id_provincia) VALUES ('Alajuelita', 1);
INSERT INTO cantones (nombre, provincias_id_provincia) VALUES ('Vásquez de Coronado', 1);

-- CANTONES DE ALAJUELA
INSERT INTO cantones (nombre, provincias_id_provincia) VALUES ('Alajuela', 2);
INSERT INTO cantones (nombre, provincias_id_provincia) VALUES ('San Ramón', 2);
INSERT INTO cantones (nombre, provincias_id_provincia) VALUES ('Grecia', 2);
INSERT INTO cantones (nombre, provincias_id_provincia) VALUES ('Atenas', 2);
INSERT INTO cantones (nombre, provincias_id_provincia) VALUES ('Naranjo', 2);
INSERT INTO cantones (nombre, provincias_id_provincia) VALUES ('Palmares', 2);

-- CANTONES DE CARTAGO
INSERT INTO cantones (nombre, provincias_id_provincia) VALUES ('Cartago', 3);
INSERT INTO cantones (nombre, provincias_id_provincia) VALUES ('Paraíso', 3);
INSERT INTO cantones (nombre, provincias_id_provincia) VALUES ('La Unión', 3);
INSERT INTO cantones (nombre, provincias_id_provincia) VALUES ('Jiménez', 3);

-- CANTONES DE HEREDIA
INSERT INTO cantones (nombre, provincias_id_provincia) VALUES ('Heredia', 4);
INSERT INTO cantones (nombre, provincias_id_provincia) VALUES ('Barva', 4);
INSERT INTO cantones (nombre, provincias_id_provincia) VALUES ('Santo Domingo', 4);
INSERT INTO cantones (nombre, provincias_id_provincia) VALUES ('San Rafael', 4);
INSERT INTO cantones (nombre, provincias_id_provincia) VALUES ('Belén', 4);

-- CANTONES DE GUANACASTE
INSERT INTO cantones (nombre, provincias_id_provincia) VALUES ('Liberia', 5);
INSERT INTO cantones (nombre, provincias_id_provincia) VALUES ('Nicoya', 5);
INSERT INTO cantones (nombre, provincias_id_provincia) VALUES ('Santa Cruz', 5);
INSERT INTO cantones (nombre, provincias_id_provincia) VALUES ('Carrillo', 5);

-- CANTONES DE PUNTARENAS
INSERT INTO cantones (nombre, provincias_id_provincia) VALUES ('Puntarenas', 6);
INSERT INTO cantones (nombre, provincias_id_provincia) VALUES ('Esparza', 6);
INSERT INTO cantones (nombre, provincias_id_provincia) VALUES ('Buenos Aires', 6);
INSERT INTO cantones (nombre, provincias_id_provincia) VALUES ('Osa', 6);

-- CANTONES DE LIMON
INSERT INTO cantones (nombre, provincias_id_provincia) VALUES ('Limón', 7);
INSERT INTO cantones (nombre, provincias_id_provincia) VALUES ('Pococí', 7);
INSERT INTO cantones (nombre, provincias_id_provincia) VALUES ('Siquirres', 7);
INSERT INTO cantones (nombre, provincias_id_provincia) VALUES ('Talamanca', 7);

-- DISTRITOS DE SAN JOSE CANTON SAN JOSE
INSERT INTO distritos (nombre, cantones_id_canton, codigo_postal) VALUES ('Carmen', 1, 10101);
INSERT INTO distritos (nombre, cantones_id_canton, codigo_postal) VALUES ('Merced', 1, 10102);
INSERT INTO distritos (nombre, cantones_id_canton, codigo_postal) VALUES ('Hospital', 1, 10103);
INSERT INTO distritos (nombre, cantones_id_canton, codigo_postal) VALUES ('Catedral', 1, 10104);
INSERT INTO distritos (nombre, cantones_id_canton, codigo_postal) VALUES ('Zapote', 1, 10105);
INSERT INTO distritos (nombre, cantones_id_canton, codigo_postal) VALUES ('San Francisco de Dos Ríos', 1, 10106);

-- DISTRITOS DE ESCAZU
INSERT INTO distritos (nombre, cantones_id_canton, codigo_postal) VALUES ('Escazú', 2, 10201);
INSERT INTO distritos (nombre, cantones_id_canton, codigo_postal) VALUES ('San Antonio', 2, 10202);
INSERT INTO distritos (nombre, cantones_id_canton, codigo_postal) VALUES ('San Rafael', 2, 10203);

-- DISTRITOS DE DESAMPARADOS
INSERT INTO distritos (nombre, cantones_id_canton, codigo_postal) VALUES ('Desamparados', 3, 10301);
INSERT INTO distritos (nombre, cantones_id_canton, codigo_postal) VALUES ('San Miguel', 3, 10302);
INSERT INTO distritos (nombre, cantones_id_canton, codigo_postal) VALUES ('San Juan de Dios', 3, 10303);
INSERT INTO distritos (nombre, cantones_id_canton, codigo_postal) VALUES ('San Rafael Arriba', 3, 10304);

-- DISTRITOS DE GOICOECHEA
INSERT INTO distritos (nombre, cantones_id_canton, codigo_postal) VALUES ('Guadalupe', 4, 10801);
INSERT INTO distritos (nombre, cantones_id_canton, codigo_postal) VALUES ('San Francisco', 4, 10802);
INSERT INTO distritos (nombre, cantones_id_canton, codigo_postal) VALUES ('Calle Blancos', 4, 10803);

-- DISTRITOS DE SANTA ANA
INSERT INTO distritos (nombre, cantones_id_canton, codigo_postal) VALUES ('Santa Ana', 5, 10901);
INSERT INTO distritos (nombre, cantones_id_canton, codigo_postal) VALUES ('Salitral', 5, 10902);
INSERT INTO distritos (nombre, cantones_id_canton, codigo_postal) VALUES ('Pozos', 5, 10903);
INSERT INTO distritos (nombre, cantones_id_canton, codigo_postal) VALUES ('Uruca', 5, 10904);

-- DISTRITOS DE ALAJUELA CANTON ALAJUELA
INSERT INTO distritos (nombre, cantones_id_canton, codigo_postal) VALUES ('Alajuela', 8, 20101);
INSERT INTO distritos (nombre, cantones_id_canton, codigo_postal) VALUES ('San José', 8, 20102);
INSERT INTO distritos (nombre, cantones_id_canton, codigo_postal) VALUES ('Carrizal', 8, 20103);
INSERT INTO distritos (nombre, cantones_id_canton, codigo_postal) VALUES ('San Antonio', 8, 20104);

-- DISTRITOS DE SAN RAMON
INSERT INTO distritos (nombre, cantones_id_canton, codigo_postal) VALUES ('San Ramón', 9, 20201);
INSERT INTO distritos (nombre, cantones_id_canton, codigo_postal) VALUES ('Santiago', 9, 20202);
INSERT INTO distritos (nombre, cantones_id_canton, codigo_postal) VALUES ('San Juan', 9, 20203);

-- DISTRITOS DE GRECIA
INSERT INTO distritos (nombre, cantones_id_canton, codigo_postal) VALUES ('Grecia', 10, 20301);
INSERT INTO distritos (nombre, cantones_id_canton, codigo_postal) VALUES ('San Isidro', 10, 20302);
INSERT INTO distritos (nombre, cantones_id_canton, codigo_postal) VALUES ('San José', 10, 20303);

-- DISTRITOS DE CARTAGO CANTON CARTAGO
INSERT INTO distritos (nombre, cantones_id_canton, codigo_postal) VALUES ('Oriental', 14, 30101);
INSERT INTO distritos (nombre, cantones_id_canton, codigo_postal) VALUES ('Occidental', 14, 30102);
INSERT INTO distritos (nombre, cantones_id_canton, codigo_postal) VALUES ('Carmen', 14, 30103);
INSERT INTO distritos (nombre, cantones_id_canton, codigo_postal) VALUES ('San Nicolás', 14, 30104);

-- DISTRITOS DE LA UNION
INSERT INTO distritos (nombre, cantones_id_canton, codigo_postal) VALUES ('Tres Ríos', 16, 30301);
INSERT INTO distritos (nombre, cantones_id_canton, codigo_postal) VALUES ('San Diego', 16, 30302);
INSERT INTO distritos (nombre, cantones_id_canton, codigo_postal) VALUES ('San Juan', 16, 30303);

-- DISTRITOS DE HEREDIA CANTON HEREDIA
INSERT INTO distritos (nombre, cantones_id_canton, codigo_postal) VALUES ('Heredia', 18, 40101);
INSERT INTO distritos (nombre, cantones_id_canton, codigo_postal) VALUES ('Mercedes', 18, 40102);
INSERT INTO distritos (nombre, cantones_id_canton, codigo_postal) VALUES ('San Francisco', 18, 40103);

-- DISTRITOS DE BELEN
INSERT INTO distritos (nombre, cantones_id_canton, codigo_postal) VALUES ('San Antonio', 22, 40701);
INSERT INTO distritos (nombre, cantones_id_canton, codigo_postal) VALUES ('La Ribera', 22, 40702);
INSERT INTO distritos (nombre, cantones_id_canton, codigo_postal) VALUES ('La Asunción', 22, 40703);

-- DISTRITOS DE LIBERIA
INSERT INTO distritos (nombre, cantones_id_canton, codigo_postal) VALUES ('Liberia', 23, 50101);
INSERT INTO distritos (nombre, cantones_id_canton, codigo_postal) VALUES ('Cañas Dulces', 23, 50102);
INSERT INTO distritos (nombre, cantones_id_canton, codigo_postal) VALUES ('Mayorga', 23, 50103);

-- DISTRITOS DE PUNTARENAS
INSERT INTO distritos (nombre, cantones_id_canton, codigo_postal) VALUES ('Puntarenas', 27, 60101);
INSERT INTO distritos (nombre, cantones_id_canton, codigo_postal) VALUES ('Pitahaya', 27, 60102);
INSERT INTO distritos (nombre, cantones_id_canton, codigo_postal) VALUES ('Chomes', 27, 60103);

-- DISTRITOS DE LIMON
INSERT INTO distritos (nombre, cantones_id_canton, codigo_postal) VALUES ('Limón', 31, 70101);
INSERT INTO distritos (nombre, cantones_id_canton, codigo_postal) VALUES ('Valle La Estrella', 31, 70102);
INSERT INTO distritos (nombre, cantones_id_canton, codigo_postal) VALUES ('Río Blanco', 31, 70103);

COMMIT;



-- ----------------
-- CONTACTOS Y DIRECCIONES
-- ----------------

-- CONTACTOS DE PROVEEDORES
INSERT INTO contactos_proveedores (nombre, apellido, email, telefono, proveedores_id_proveedor)
VALUES ('Andrea', 'Soto', 'andrea.soto@dtc.cr', '8888-1001', 1);

INSERT INTO contactos_proveedores (nombre, apellido, email, telefono, proveedores_id_proveedor)
VALUES ('Mauricio', 'Vega', 'mauricio.vega@idt.cr', '8888-1002', 2);

INSERT INTO contactos_proveedores (nombre, apellido, email, telefono, proveedores_id_proveedor)
VALUES ('Carolina', 'Ramirez', 'carolina.ramirez@sgcr.cr', '8888-1003', 3);

INSERT INTO contactos_proveedores (nombre, apellido, email, telefono, proveedores_id_proveedor)
VALUES ('Esteban', 'Murillo', 'esteban.murillo@mcv.cr', '8888-1004', 4);

INSERT INTO contactos_proveedores (nombre, apellido, email, telefono, proveedores_id_proveedor)
VALUES ('Daniela', 'Alfaro', 'daniela.alfaro@rae.cr', '8888-1005', 5);

INSERT INTO contactos_proveedores (nombre, apellido, email, telefono, proveedores_id_proveedor)
VALUES ('Pablo', 'Quesada', 'pablo.quesada@ofitech.cr', '8888-1006', 6);

INSERT INTO contactos_proveedores (nombre, apellido, email, telefono, proveedores_id_proveedor)
VALUES ('Mariela', 'Campos', 'mariela.campos@epacifico.cr', '8888-1007', 7);

-- DIRECCIONES DE CLIENTES
INSERT INTO direcciones (
    otras_senas, provincias_id_provincia, cantones_id_canton, distritos_id_distrito, clientes_id_cliente, proveedores_id_proveedor
) VALUES (
    '250 metros al este del Parque Central, casa esquinera color gris', 1, 1, 5, 1, NULL
);

INSERT INTO direcciones (
    otras_senas, provincias_id_provincia, cantones_id_canton, distritos_id_distrito, clientes_id_cliente, proveedores_id_proveedor
) VALUES (
    'Frente al Maxi Pali, casa de porton negro', 1, 3, 10, 3, NULL
);


INSERT INTO direcciones (
    otras_senas, provincias_id_provincia, cantones_id_canton, distritos_id_distrito, clientes_id_cliente, proveedores_id_proveedor
) VALUES (
    '200 metros norte de la Cruz Roja, local 12', 1, 5, 17, 5, NULL
);


INSERT INTO direcciones (
    otras_senas, provincias_id_provincia, cantones_id_canton, distritos_id_distrito, clientes_id_cliente, proveedores_id_proveedor
) VALUES (
    'A la par de la iglesia catolica, segunda planta', 2, 9, 24, 7, NULL
);

INSERT INTO direcciones (
    otras_senas, provincias_id_provincia, cantones_id_canton, distritos_id_distrito, clientes_id_cliente, proveedores_id_proveedor
) VALUES (
    'Residencial Monte Real, casa 9', 3, 14, 30, 8, NULL
);

INSERT INTO direcciones (
    otras_senas, provincias_id_provincia, cantones_id_canton, distritos_id_distrito, clientes_id_cliente, proveedores_id_proveedor
) VALUES (
    '300 metros oeste del colegio, porton blanco', 4, 18, 37, 9, NULL
);

INSERT INTO direcciones (
    otras_senas, provincias_id_provincia, cantones_id_canton, distritos_id_distrito, clientes_id_cliente, proveedores_id_proveedor
) VALUES (
    'Detras del supermercado, casa color crema', 7, 31, 49, 10, NULL
);

-- DIRECCIONES DE PROVEEDORES
INSERT INTO direcciones (
    otras_senas, provincias_id_provincia, cantones_id_canton, distritos_id_distrito, clientes_id_cliente, proveedores_id_proveedor
) VALUES (
    'Zona industrial, bodega 3', 1, 1, 2, NULL, 1
);

INSERT INTO direcciones (
    otras_senas, provincias_id_provincia, cantones_id_canton, distritos_id_distrito, clientes_id_cliente, proveedores_id_proveedor
) VALUES (
    'Centro corporativo, edificio norte, piso 2', 1, 5, 19, NULL, 2
);

INSERT INTO direcciones (
    otras_senas, provincias_id_provincia, cantones_id_canton, distritos_id_distrito, clientes_id_cliente, proveedores_id_proveedor
) VALUES (
    'Ofibodegas del oeste, local 5', 4, 22, 40, NULL, 3
);

INSERT INTO direcciones (
    otras_senas, provincias_id_provincia, cantones_id_canton, distritos_id_distrito, clientes_id_cliente, proveedores_id_proveedor
) VALUES (
    'Parque empresarial, bodega 11', 2, 8, 21, NULL, 4
);

INSERT INTO direcciones (
    otras_senas, provincias_id_provincia, cantones_id_canton, distritos_id_distrito, clientes_id_cliente, proveedores_id_proveedor
) VALUES (
    '100 metros sur de Repuestos Gigante, local esquinero', 3, 16, 34, NULL, 5
);

INSERT INTO direcciones (
    otras_senas, provincias_id_provincia, cantones_id_canton, distritos_id_distrito, clientes_id_cliente, proveedores_id_proveedor
) VALUES (
    'Centro logistico, nave 7', 5, 23, 43, NULL, 6
);

INSERT INTO direcciones (
    otras_senas, provincias_id_provincia, cantones_id_canton, distritos_id_distrito, clientes_id_cliente, proveedores_id_proveedor
) VALUES (
    'Zona comercial del muelle, local 4', 6, 27, 46, NULL, 7
);

-- TELEFONOS DE CLIENTES
INSERT INTO telefonos_clientes (numero, clientes_id_cliente) VALUES ('8881-2001', 1);
INSERT INTO telefonos_clientes (numero, clientes_id_cliente) VALUES ('8881-2003', 3);
INSERT INTO telefonos_clientes (numero, clientes_id_cliente) VALUES ('8881-2005', 5);
INSERT INTO telefonos_clientes (numero, clientes_id_cliente) VALUES ('8881-2007', 7);
INSERT INTO telefonos_clientes (numero, clientes_id_cliente) VALUES ('8881-2008', 8);
INSERT INTO telefonos_clientes (numero, clientes_id_cliente) VALUES ('8881-2009', 9);
INSERT INTO telefonos_clientes (numero, clientes_id_cliente) VALUES ('8881-2010', 10);

-- TELEFONOS DE CONTACTOS DE PROVEEDORES
INSERT INTO telefonos_cont_proveedores (numero, contactos_proveedores_id_contacto) VALUES ('2222-3001', 1);
INSERT INTO telefonos_cont_proveedores (numero, contactos_proveedores_id_contacto) VALUES ('2222-3002', 2);
INSERT INTO telefonos_cont_proveedores (numero, contactos_proveedores_id_contacto) VALUES ('2222-3003', 3);
INSERT INTO telefonos_cont_proveedores (numero, contactos_proveedores_id_contacto) VALUES ('2222-3004', 4);
INSERT INTO telefonos_cont_proveedores (numero, contactos_proveedores_id_contacto) VALUES ('2222-3005', 5);
INSERT INTO telefonos_cont_proveedores (numero, contactos_proveedores_id_contacto) VALUES ('2222-3006', 6);
INSERT INTO telefonos_cont_proveedores (numero, contactos_proveedores_id_contacto) VALUES ('2222-3007', 7);

-- DEPARTAMENTOS
INSERT INTO departamentos (nombre) VALUES ('Administracion');
INSERT INTO departamentos (nombre) VALUES ('Ventas');
INSERT INTO departamentos (nombre) VALUES ('Caja');
INSERT INTO departamentos (nombre) VALUES ('Inventario');
INSERT INTO departamentos (nombre) VALUES ('Bodega');
INSERT INTO departamentos (nombre) VALUES ('Soporte Tecnico');
INSERT INTO departamentos (nombre) VALUES ('Facturacion');
INSERT INTO departamentos (nombre) VALUES ('Atencion al Cliente');

COMMIT;




-- ----------------
-- COMPRAS Y VENTAS
-- ----------------

-- ENCABEZADOS_COMPRAS
INSERT INTO encabezados_compras (fecha_compra, total_compra, proveedores_id_proveedor, empleados_id_empleado)
VALUES (DATE '2026-02-01', 1900000, 1, 3);

INSERT INTO encabezados_compras (fecha_compra, total_compra, proveedores_id_proveedor, empleados_id_empleado)
VALUES (DATE '2026-02-03', 925000, 2, 3);

INSERT INTO encabezados_compras (fecha_compra, total_compra, proveedores_id_proveedor, empleados_id_empleado)
VALUES (DATE '2026-02-05', 1260000, 3, 3);

INSERT INTO encabezados_compras (fecha_compra, total_compra, proveedores_id_proveedor, empleados_id_empleado)
VALUES (DATE '2026-02-08', 720000, 4, 6);

INSERT INTO encabezados_compras (fecha_compra, total_compra, proveedores_id_proveedor, empleados_id_empleado)
VALUES (DATE '2026-02-12', 540000, 6, 3);

-- DETALLES_COMPRAS
INSERT INTO detalles_compras (cantidad, precio_unitario, encabezados_compras_id_compra, productos_id_producto)
VALUES (4, 350000, 1, 1);

INSERT INTO detalles_compras (cantidad, precio_unitario, encabezados_compras_id_compra, productos_id_producto)
VALUES (5, 95000, 1, 5);

INSERT INTO detalles_compras (cantidad, precio_unitario, encabezados_compras_id_compra, productos_id_producto)
VALUES (5, 25000, 1, 7);

INSERT INTO detalles_compras (cantidad, precio_unitario, encabezados_compras_id_compra, productos_id_producto)
VALUES (10, 35000, 2, 9);

INSERT INTO detalles_compras (cantidad, precio_unitario, encabezados_compras_id_compra, productos_id_producto)
VALUES (5, 45000, 2, 15);

INSERT INTO detalles_compras (cantidad, precio_unitario, encabezados_compras_id_compra, productos_id_producto)
VALUES (3, 280000, 3, 3);

INSERT INTO detalles_compras (cantidad, precio_unitario, encabezados_compras_id_compra, productos_id_producto)
VALUES (3, 120000, 3, 12);

INSERT INTO detalles_compras (cantidad, precio_unitario, encabezados_compras_id_compra, productos_id_producto)
VALUES (2, 22000, 3, 14);

INSERT INTO detalles_compras (cantidad, precio_unitario, encabezados_compras_id_compra, productos_id_producto)
VALUES (4, 85000, 4, 11);

INSERT INTO detalles_compras (cantidad, precio_unitario, encabezados_compras_id_compra, productos_id_producto)
VALUES (8, 18000, 4, 13);

INSERT INTO detalles_compras (cantidad, precio_unitario, encabezados_compras_id_compra, productos_id_producto)
VALUES (3, 95000, 5, 16);

INSERT INTO detalles_compras (cantidad, precio_unitario, encabezados_compras_id_compra, productos_id_producto)
VALUES (5, 51000, 5, 8);

-- ENCABEZADOS_VENTAS
INSERT INTO encabezados_ventas (fecha_venta, total_venta, clientes_id_cliente, empleados_id_empleado)
VALUES (DATE '2026-02-14', 464800, 1, 2);

INSERT INTO encabezados_ventas (fecha_venta, total_venta, clientes_id_cliente, empleados_id_empleado)
VALUES (DATE '2026-02-16', 159800, 3, 2);

INSERT INTO encabezados_ventas (fecha_venta, total_venta, clientes_id_cliente, empleados_id_empleado)
VALUES (DATE '2026-02-18', 194800, 5, 4);

INSERT INTO encabezados_ventas (fecha_venta, total_venta, clientes_id_cliente, empleados_id_empleado)
VALUES (DATE '2026-02-21', 119900, 7, 2);

INSERT INTO encabezados_ventas (fecha_venta, total_venta, clientes_id_cliente, empleados_id_empleado)
VALUES (DATE '2026-02-22', 174700, 8, 4);

-- DETALLES_VENTAS
INSERT INTO detalles_ventas (cantidad, precio_unitario, subtotal, encabezados_ventas_id_venta, productos_id_producto)
VALUES (1, 429900, 429900, 1, 1);

INSERT INTO detalles_ventas (cantidad, precio_unitario, subtotal, encabezados_ventas_id_venta, productos_id_producto)
VALUES (1, 34900, 34900, 1, 14);

INSERT INTO detalles_ventas (cantidad, precio_unitario, subtotal, encabezados_ventas_id_venta, productos_id_producto)
VALUES (2, 39900, 79800, 3, 7);

INSERT INTO detalles_ventas (cantidad, precio_unitario, subtotal, encabezados_ventas_id_venta, productos_id_producto)
VALUES (1, 49900, 49900, 3, 9);

INSERT INTO detalles_ventas (cantidad, precio_unitario, subtotal, encabezados_ventas_id_venta, productos_id_producto)
VALUES (1, 34900, 34900, 4, 14);




-- PAGOS
INSERT INTO pagos (monto, fecha_pago, metodos_pago_id_metodo_pago, encabezados_ventas_id_venta)
VALUES (464800, DATE '2026-02-14', 2, 1);

INSERT INTO pagos (monto, fecha_pago, metodos_pago_id_metodo_pago, encabezados_ventas_id_venta)
VALUES (159800, DATE '2026-02-16', 1, 3);

INSERT INTO pagos (monto, fecha_pago, metodos_pago_id_metodo_pago, encabezados_ventas_id_venta)
VALUES (194800, DATE '2026-02-18', 5, 4);


-- FACTURAS
INSERT INTO facturas (numero_factura, clave_hacienda, fecha_emision, estados_id_estado, xml, encabezados_ventas_id_venta)
VALUES (
    'FAC-2026-000001',
    '50614022600310112345600100001010000000001123456789',
    DATE '2026-02-14',
    1,
    '<factura><numero>FAC-2026-000001</numero><cliente>1</cliente><total>464800</total></factura>',
    1
);

INSERT INTO facturas (numero_factura, clave_hacienda, fecha_emision, estados_id_estado, xml, encabezados_ventas_id_venta)
VALUES (
    'FAC-2026-000003',
    '50616022600310112345600100001010000000003123456789',
    DATE '2026-02-16',
    1,
    '<factura><numero>FAC-2026-000003</numero><cliente>3</cliente><total>159800</total></factura>',
    3
);

INSERT INTO facturas (numero_factura, clave_hacienda, fecha_emision, estados_id_estado, xml, encabezados_ventas_id_venta)
VALUES (
    'FAC-2026-000004',
    '50618022600310112345600100001010000000004123456789',
    DATE '2026-02-18',
    1,
    '<factura><numero>FAC-2026-000004</numero><cliente>5</cliente><total>194800</total></factura>',
    4
);


COMMIT;



-- ----------------
-- GESTION DE INVENTARIO
-- ----------------

-- GESTION_STOCK
INSERT INTO gestion_stock (cantidad, fecha_gestion, productos_id_producto, tipo_gestion_id_tipo_gestion)
VALUES (4, DATE '2026-02-01', 1, 1);

INSERT INTO gestion_stock (cantidad, fecha_gestion, productos_id_producto, tipo_gestion_id_tipo_gestion)
VALUES (5, DATE '2026-02-01', 5, 1);

INSERT INTO gestion_stock (cantidad, fecha_gestion, productos_id_producto, tipo_gestion_id_tipo_gestion)
VALUES (5, DATE '2026-02-01', 7, 1);

INSERT INTO gestion_stock (cantidad, fecha_gestion, productos_id_producto, tipo_gestion_id_tipo_gestion)
VALUES (10, DATE '2026-02-03', 9, 1);

INSERT INTO gestion_stock (cantidad, fecha_gestion, productos_id_producto, tipo_gestion_id_tipo_gestion)
VALUES (5, DATE '2026-02-03', 15, 1);

INSERT INTO gestion_stock (cantidad, fecha_gestion, productos_id_producto, tipo_gestion_id_tipo_gestion)
VALUES (3, DATE '2026-02-05', 3, 1);

INSERT INTO gestion_stock (cantidad, fecha_gestion, productos_id_producto, tipo_gestion_id_tipo_gestion)
VALUES (3, DATE '2026-02-05', 12, 1);

INSERT INTO gestion_stock (cantidad, fecha_gestion, productos_id_producto, tipo_gestion_id_tipo_gestion)
VALUES (2, DATE '2026-02-05', 14, 1);

INSERT INTO gestion_stock (cantidad, fecha_gestion, productos_id_producto, tipo_gestion_id_tipo_gestion)
VALUES (4, DATE '2026-02-08', 11, 1);

INSERT INTO gestion_stock (cantidad, fecha_gestion, productos_id_producto, tipo_gestion_id_tipo_gestion)
VALUES (8, DATE '2026-02-08', 13, 1);

INSERT INTO gestion_stock (cantidad, fecha_gestion, productos_id_producto, tipo_gestion_id_tipo_gestion)
VALUES (3, DATE '2026-02-12', 16, 1);

INSERT INTO gestion_stock (cantidad, fecha_gestion, productos_id_producto, tipo_gestion_id_tipo_gestion)
VALUES (5, DATE '2026-02-12', 8, 1);

INSERT INTO gestion_stock (cantidad, fecha_gestion, productos_id_producto, tipo_gestion_id_tipo_gestion)
VALUES (1, DATE '2026-02-14', 1, 2);

INSERT INTO gestion_stock (cantidad, fecha_gestion, productos_id_producto, tipo_gestion_id_tipo_gestion)
VALUES (1, DATE '2026-02-14', 14, 2);

INSERT INTO gestion_stock (cantidad, fecha_gestion, productos_id_producto, tipo_gestion_id_tipo_gestion)
VALUES (1, DATE '2026-02-15', 3, 2);

INSERT INTO gestion_stock (cantidad, fecha_gestion, productos_id_producto, tipo_gestion_id_tipo_gestion)
VALUES (2, DATE '2026-02-16', 7, 2);

INSERT INTO gestion_stock (cantidad, fecha_gestion, productos_id_producto, tipo_gestion_id_tipo_gestion)
VALUES (1, DATE '2026-02-16', 9, 2);

INSERT INTO gestion_stock (cantidad, fecha_gestion, productos_id_producto, tipo_gestion_id_tipo_gestion)
VALUES (1, DATE '2026-02-18', 14, 2);

INSERT INTO gestion_stock (cantidad, fecha_gestion, productos_id_producto, tipo_gestion_id_tipo_gestion)
VALUES (1, DATE '2026-02-20', 10, 2);

INSERT INTO gestion_stock (cantidad, fecha_gestion, productos_id_producto, tipo_gestion_id_tipo_gestion)
VALUES (1, DATE '2026-02-21', 11, 2);

INSERT INTO gestion_stock (cantidad, fecha_gestion, productos_id_producto, tipo_gestion_id_tipo_gestion)
VALUES (1, DATE '2026-02-22', 13, 2);

INSERT INTO gestion_stock (cantidad, fecha_gestion, productos_id_producto, tipo_gestion_id_tipo_gestion)
VALUES (1, DATE '2026-02-22', 8, 2);

INSERT INTO gestion_stock (cantidad, fecha_gestion, productos_id_producto, tipo_gestion_id_tipo_gestion)
VALUES (1, DATE '2026-02-22', 15, 2);

INSERT INTO gestion_stock (cantidad, fecha_gestion, productos_id_producto, tipo_gestion_id_tipo_gestion)
VALUES (1, DATE '2026-02-23', 14, 4);

INSERT INTO gestion_stock (cantidad, fecha_gestion, productos_id_producto, tipo_gestion_id_tipo_gestion)
VALUES (2, DATE '2026-02-24', 13, 3);

INSERT INTO gestion_stock (cantidad, fecha_gestion, productos_id_producto, tipo_gestion_id_tipo_gestion)
VALUES (1, DATE '2026-02-25', 12, 6);

COMMIT;


-- ----------------
-- USUARIOS  (cliente, cajero, inventario, admin)
-- Vinculado a empleado O cliente segun elrol
-- ----------------

-- USUARIOS DE EMPLEADOS (password_encriptado: bcrypt PHP; contraseña de prueba: Hamilton2026!)
INSERT INTO usuarios (username, password_encriptado, roles_id_rol, estados_id_estado, empleados_id_empleado, clientes_id_cliente)
VALUES ('jhernandez', '$2y$10$n2bWkpZ7LdD0dujk1S0hQOfXIL17kf8qGG9DQqBlr1krojRKV2LHW', 1, 1, 1, NULL);

INSERT INTO usuarios (username, password_encriptado, roles_id_rol, estados_id_estado, empleados_id_empleado, clientes_id_cliente)
VALUES ('cmendez', '$2y$10$n2bWkpZ7LdD0dujk1S0hQOfXIL17kf8qGG9DQqBlr1krojRKV2LHW', 2, 1, 2, NULL);

INSERT INTO usuarios (username, password_encriptado, roles_id_rol, estados_id_estado, empleados_id_empleado, clientes_id_cliente)
VALUES ('lquesada', '$2y$10$n2bWkpZ7LdD0dujk1S0hQOfXIL17kf8qGG9DQqBlr1krojRKV2LHW', 3, 1, 3, NULL);

INSERT INTO usuarios (username, password_encriptado, roles_id_rol, estados_id_estado, empleados_id_empleado, clientes_id_cliente)
VALUES ('fsalas', '$2y$10$n2bWkpZ7LdD0dujk1S0hQOfXIL17kf8qGG9DQqBlr1krojRKV2LHW', 2, 1, 4, NULL);

INSERT INTO usuarios (username, password_encriptado, roles_id_rol, estados_id_estado, empleados_id_empleado, clientes_id_cliente)
VALUES ('belizondo', '$2y$10$n2bWkpZ7LdD0dujk1S0hQOfXIL17kf8qGG9DQqBlr1krojRKV2LHW', 5, 1, 5, NULL);

INSERT INTO usuarios (username, password_encriptado, roles_id_rol, estados_id_estado, empleados_id_empleado, clientes_id_cliente)
VALUES ('macuna', '$2y$10$n2bWkpZ7LdD0dujk1S0hQOfXIL17kf8qGG9DQqBlr1krojRKV2LHW', 1, 1, 6, NULL);

INSERT INTO usuarios (username, password_encriptado, roles_id_rol, estados_id_estado, empleados_id_empleado, clientes_id_cliente)
VALUES ('dporras', '$2y$10$n2bWkpZ7LdD0dujk1S0hQOfXIL17kf8qGG9DQqBlr1krojRKV2LHW', 3, 2, 7, NULL);

-- USUARIOS DE CLIENTES
INSERT INTO usuarios (username, password_encriptado, roles_id_rol, estados_id_estado, empleados_id_empleado, clientes_id_cliente)
VALUES ('daniel.chaves', '$2y$10$n2bWkpZ7LdD0dujk1S0hQOfXIL17kf8qGG9DQqBlr1krojRKV2LHW', 4, 1, NULL, 1);

INSERT INTO usuarios (username, password_encriptado, roles_id_rol, estados_id_estado, empleados_id_empleado, clientes_id_cliente)
VALUES ('jose.mora', '$2y$10$n2bWkpZ7LdD0dujk1S0hQOfXIL17kf8qGG9DQqBlr1krojRKV2LHW', 4, 1, NULL, 3);

INSERT INTO usuarios (username, password_encriptado, roles_id_rol, estados_id_estado, empleados_id_empleado, clientes_id_cliente)
VALUES ('andres.castro', '$2y$10$n2bWkpZ7LdD0dujk1S0hQOfXIL17kf8qGG9DQqBlr1krojRKV2LHW', 4, 1, NULL, 5);

INSERT INTO usuarios (username, password_encriptado, roles_id_rol, estados_id_estado, empleados_id_empleado, clientes_id_cliente)
VALUES ('kevin.araya', '$2y$10$n2bWkpZ7LdD0dujk1S0hQOfXIL17kf8qGG9DQqBlr1krojRKV2LHW', 4, 1, NULL, 7);

INSERT INTO usuarios (username, password_encriptado, roles_id_rol, estados_id_estado, empleados_id_empleado, clientes_id_cliente)
VALUES ('paula.solano', '$2y$10$n2bWkpZ7LdD0dujk1S0hQOfXIL17kf8qGG9DQqBlr1krojRKV2LHW', 4, 1, NULL, 8);

INSERT INTO usuarios (username, password_encriptado, roles_id_rol, estados_id_estado, empleados_id_empleado, clientes_id_cliente)
VALUES ('esteban.ramirez', '$2y$10$n2bWkpZ7LdD0dujk1S0hQOfXIL17kf8qGG9DQqBlr1krojRKV2LHW', 4, 1, NULL, 9);

INSERT INTO usuarios (username, password_encriptado, roles_id_rol, estados_id_estado, empleados_id_empleado, clientes_id_cliente)
VALUES ('natalia.cordero', '$2y$10$n2bWkpZ7LdD0dujk1S0hQOfXIL17kf8qGG9DQqBlr1krojRKV2LHW', 4, 2, NULL, 10);

COMMIT;



