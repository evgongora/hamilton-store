-- =============================================================================
-- Triggers y paquete de contexto en esquema M_HAMILTON_STORE.
-- ON M_HAMILTON_STORE.tabla permite crear los triggers aunque la sesión sea ADMIN.
-- Requiere: esquema y tablas ya creados (esquema_oracle.sql).
-- =============================================================================
--- TRIGGERS ---

/* Al menos seis de las tablas deben incluir triggers. Específicamente dos para
cada acción. Se entiende que para cada sentencia DML de cada tabla escogida se
va a crear un trigger BEFORE y AFTER. */

/* Paquete de contexto para indicar el origen del movimiento y el tipo de
gestion que debe registrar productos en gestion_stock. */
CREATE OR REPLACE PACKAGE M_HAMILTON_STORE.pkg_ctx_gestion_stock AS
    g_tipo_gestion NUMBER := NULL;
    g_origen       VARCHAR2(30) := NULL;
END pkg_ctx_gestion_stock;
/

-- Triggers en Productos

/* Antes de cada insert, se revisa la cantidad y si es 0, se asigna el estado
'AGOTADO' automáticamente. */
CREATE OR REPLACE TRIGGER trg_prod_bi -- bi = before insert
BEFORE INSERT ON M_HAMILTON_STORE.productos
FOR EACH ROW
DECLARE
    v_estado_agotado estados.id_estado%TYPE;
BEGIN
    SELECT id_estado INTO v_estado_agotado
    FROM estados
    WHERE LOWER(nombre) = 'agotado'
    FETCH FIRST 1 ROWS ONLY;

    IF :NEW.cantidad = 0 THEN
        :NEW.estados_id_estado := v_estado_agotado;
    END IF;
END;
/

/* Después de cada insert, se va a actualizar la tabla "gestion_stock" para 
registrar el movimiento */
CREATE OR REPLACE TRIGGER trg_prod_ai_gestion_stock -- ai = after insert
AFTER INSERT ON M_HAMILTON_STORE.productos
FOR EACH ROW
BEGIN
    INSERT INTO gestion_stock (
        cantidad,
        productos_id_producto,
        tipo_gestion_id_tipo_gestion
    ) VALUES (
        :NEW.cantidad,
        :NEW.id_producto,
        3 -- se registra como ajuste manual por inventario.
    );
END;
/

/* Antes de cada update se revisa la cantidad y si es 0, se asigna el estado
'AGOTADO' automáticamente. */
CREATE OR REPLACE TRIGGER trg_prod_bu
BEFORE UPDATE ON M_HAMILTON_STORE.productos
FOR EACH ROW
DECLARE
    v_estado_agotado estados.id_estado%TYPE;
BEGIN
    SELECT id_estado INTO v_estado_agotado
    FROM estados
    WHERE LOWER(nombre) = 'agotado'
    FETCH FIRST 1 ROWS ONLY;

    IF :NEW.cantidad = 0 THEN
        :NEW.estados_id_estado := v_estado_agotado;
    END IF;
END;
/

-- Después de cada update registrar el movimiento en la tabla GESTION_STOCK.
CREATE OR REPLACE TRIGGER trg_prod_au_gestion_stock
AFTER UPDATE ON M_HAMILTON_STORE.productos
FOR EACH ROW
DECLARE
    v_tipo_gestion NUMBER;
BEGIN
    IF :OLD.cantidad != :NEW.cantidad THEN

        v_tipo_gestion := M_HAMILTON_STORE.pkg_ctx_gestion_stock.g_tipo_gestion;

        IF v_tipo_gestion IS NULL THEN
            v_tipo_gestion := 3;
        END IF;

        INSERT INTO gestion_stock (
            cantidad,
            productos_id_producto,
            tipo_gestion_id_tipo_gestion
        ) VALUES (
            :NEW.cantidad - :OLD.cantidad,
            :NEW.id_producto,
            v_tipo_gestion
        );

        M_HAMILTON_STORE.pkg_ctx_gestion_stock.g_tipo_gestion := NULL;
        M_HAMILTON_STORE.pkg_ctx_gestion_stock.g_origen := NULL;
    END IF;
END;
/

/* Antes de cada delete se debe revisar si hay compras o ventas de este producto
para no borrar productos con historial. */
CREATE OR REPLACE TRIGGER trg_prod_bd_detalles
BEFORE DELETE ON M_HAMILTON_STORE.productos
FOR EACH ROW
DECLARE
    v_count_ventas NUMBER;
    v_count_compras NUMBER;
BEGIN
    SELECT COUNT(*) INTO v_count_ventas
    FROM detalles_ventas
    WHERE productos_id_producto = :OLD.id_producto;

    IF v_count_ventas > 0 THEN
        RAISE_APPLICATION_ERROR(-20001, 'No se puede eliminar un producto con ventas registradas');
    END IF;

    SELECT COUNT(*) INTO v_count_compras
    from detalles_compras
    where productos_id_producto = :OLD.id_producto;
    
    IF v_count_compras > 0 THEN
        RAISE_APPLICATION_ERROR(-20002, 'No se puede eliminar un producto con compras registradas');
    END IF;
END;
/

/* Para esta tabla se prioriza la validacion previa al borrado. Por eso se
utilizan dos triggers BEFORE DELETE: uno para revisar historial comercial y
otro para revisar trazabilidad en gestion_stock. */
CREATE OR REPLACE TRIGGER trg_prod_bd_gestion_stock
BEFORE DELETE ON M_HAMILTON_STORE.productos
FOR EACH ROW
DECLARE
    v_count_gestiones NUMBER;
BEGIN
    SELECT COUNT(*) INTO v_count_gestiones
    FROM gestion_stock
    WHERE productos_id_producto = :OLD.id_producto;
    
    IF v_count_gestiones > 0 THEN
        RAISE_APPLICATION_ERROR(-20003, 'No se puede eliminar un producto con gestiones de stock registradas');
    END IF;
END;
/

-- Triggers en DETALLES_VENTAS

-- Antes de Insert se valida que el producto este activo
CREATE OR REPLACE TRIGGER trg_det_vent_bi_estado
BEFORE INSERT ON M_HAMILTON_STORE.detalles_ventas
FOR EACH ROW
DECLARE
    v_estado_nombre estados.nombre%TYPE;
BEGIN
    SELECT e.nombre
    INTO v_estado_nombre
    FROM productos p
    JOIN estados e ON p.estados_id_estado = e.id_estado
    WHERE p.id_producto = :NEW.productos_id_producto;

    IF LOWER(v_estado_nombre) != 'activo' THEN
        RAISE_APPLICATION_ERROR(-20004, 'No se puede vender un producto que no esta activo');
    END IF;
END;
/

/* Después de cada insert se registra la salida de inventario y se actualiza el 
stock del producto */
CREATE OR REPLACE TRIGGER trg_det_vent_ai_gestion_stock
AFTER INSERT ON M_HAMILTON_STORE.detalles_ventas
FOR EACH ROW
BEGIN
    M_HAMILTON_STORE.pkg_ctx_gestion_stock.g_tipo_gestion := 2;
    M_HAMILTON_STORE.pkg_ctx_gestion_stock.g_origen := 'DETALLE_VENTA';

    UPDATE productos
    SET cantidad = cantidad - :NEW.cantidad
    WHERE id_producto = :NEW.productos_id_producto;
END;
/

/* Antes de cada update se valida que, si la cantidad aumenta, exista stock
suficiente para cubrir la diferencia */
CREATE OR REPLACE TRIGGER trg_det_vent_bu_productos
BEFORE UPDATE ON M_HAMILTON_STORE.detalles_ventas
FOR EACH ROW
DECLARE
    v_stock_actual productos.cantidad%TYPE;
    v_diferencia   NUMBER;
BEGIN
    v_diferencia := :NEW.cantidad - :OLD.cantidad;

    IF v_diferencia > 0 THEN
        SELECT cantidad
        INTO v_stock_actual
        FROM productos
        WHERE id_producto = :NEW.productos_id_producto;

        IF v_diferencia > v_stock_actual THEN
            RAISE_APPLICATION_ERROR(-20005, 'No hay stock suficiente para aumentar la cantidad de la venta');
        END IF;
    END IF;
END;
/

/* Después de cada update se ajusta el stock del producto segun la diferencia en
la cantidad vendida. El movimiento se registra desde PRODUCTOS usando el 
contexto definido en el paquete */
CREATE OR REPLACE TRIGGER trg_det_vent_au_gestion_stock
AFTER UPDATE ON M_HAMILTON_STORE.detalles_ventas
FOR EACH ROW
BEGIN
    IF :OLD.cantidad != :NEW.cantidad THEN
        M_HAMILTON_STORE.pkg_ctx_gestion_stock.g_tipo_gestion := 2;
        M_HAMILTON_STORE.pkg_ctx_gestion_stock.g_origen := 'DETALLE_VENTA';

        UPDATE productos
        SET cantidad = cantidad + :OLD.cantidad - :NEW.cantidad
        WHERE id_producto = :NEW.productos_id_producto;
    END IF;
END;
/

-- Antes de cada delete se valida que la venta no tenga factura asociada
CREATE OR REPLACE TRIGGER trg_det_vent_bd_factura
BEFORE DELETE ON M_HAMILTON_STORE.detalles_ventas
FOR EACH ROW
DECLARE
    v_count_facturas NUMBER;
BEGIN
    SELECT COUNT(*)
    INTO v_count_facturas
    FROM facturas
    WHERE encabezados_ventas_id_venta = :OLD.encabezados_ventas_id_venta;

    IF v_count_facturas > 0 THEN
        RAISE_APPLICATION_ERROR(-20007, 'No se puede eliminar un detalle de una venta ya facturada');
    END IF;
END;
/

/* Después de cada delete se actualiza el total de la venta
y se devuelve el stock al producto */
CREATE OR REPLACE TRIGGER trg_det_vent_ad_total
AFTER DELETE ON M_HAMILTON_STORE.detalles_ventas
FOR EACH ROW
BEGIN
    M_HAMILTON_STORE.pkg_ctx_gestion_stock.g_tipo_gestion := 2;
    M_HAMILTON_STORE.pkg_ctx_gestion_stock.g_origen := 'DETALLE_VENTA';

    UPDATE productos
    SET cantidad = cantidad + :OLD.cantidad
    WHERE id_producto = :OLD.productos_id_producto;

    UPDATE encabezados_ventas
    SET total_venta = total_venta - :OLD.subtotal
    WHERE id_venta = :OLD.encabezados_ventas_id_venta;
END;
/

-- Triggers en DETALLES_COMPRAS

/* Antes de cada insert se asigna automaticamente el precio_unitario
tomandolo del producto si no viene definido */
CREATE OR REPLACE TRIGGER trg_det_comp_bi_precio
BEFORE INSERT ON M_HAMILTON_STORE.detalles_compras
FOR EACH ROW
DECLARE
    v_precio productos.precio_compra%TYPE;
BEGIN
    IF :NEW.precio_unitario IS NULL THEN
        SELECT precio_compra
        INTO v_precio
        FROM productos
        WHERE id_producto = :NEW.productos_id_producto;

        :NEW.precio_unitario := v_precio;
    END IF;
END;
/

/* Después de cada insert se registra la entrada de inventario
y se actualiza el stock del producto */
CREATE OR REPLACE TRIGGER trg_det_comp_ai_gestion_stock
AFTER INSERT ON M_HAMILTON_STORE.detalles_compras
FOR EACH ROW
BEGIN
    M_HAMILTON_STORE.pkg_ctx_gestion_stock.g_tipo_gestion := 1;
    M_HAMILTON_STORE.pkg_ctx_gestion_stock.g_origen := 'DETALLE_COMPRA';

    UPDATE productos
    SET cantidad = cantidad + :NEW.cantidad
    WHERE id_producto = :NEW.productos_id_producto;
END;
/

/* Antes de cada update se asigna automaticamente el precio_unitario
tomandolo del producto si no viene definido */
CREATE OR REPLACE TRIGGER trg_det_comp_bu_precio
BEFORE UPDATE ON M_HAMILTON_STORE.detalles_compras
FOR EACH ROW
DECLARE
    v_precio productos.precio_compra%TYPE;
BEGIN
    IF :NEW.precio_unitario IS NULL THEN
        SELECT precio_compra
        INTO v_precio
        FROM productos
        WHERE id_producto = :NEW.productos_id_producto;

        :NEW.precio_unitario := v_precio;
    END IF;
END;
/

/* Después de cada update se ajusta el stock del producto segun la diferencia
en la cantidad comprada. El movimiento se registra desde PRODUCTOS usando
el contexto definido en el paquete. */
CREATE OR REPLACE TRIGGER trg_det_comp_au_gestion_stock
AFTER UPDATE ON M_HAMILTON_STORE.detalles_compras
FOR EACH ROW
BEGIN
    IF :OLD.cantidad != :NEW.cantidad THEN
        M_HAMILTON_STORE.pkg_ctx_gestion_stock.g_tipo_gestion := 1;
        M_HAMILTON_STORE.pkg_ctx_gestion_stock.g_origen := 'DETALLE_COMPRA';

        UPDATE productos
        SET cantidad = cantidad - :OLD.cantidad + :NEW.cantidad
        WHERE id_producto = :NEW.productos_id_producto;
    END IF;
END;
/

/* Antes de cada delete se valida que la compra tenga mas de un detalle,
para no dejar un encabezado de compra sin lineas asociadas. */
CREATE OR REPLACE TRIGGER trg_det_comp_bd_gestion
BEFORE DELETE ON M_HAMILTON_STORE.detalles_compras
FOR EACH ROW
DECLARE
    v_count_detalles NUMBER;
BEGIN
    SELECT COUNT(*)
    INTO v_count_detalles
    FROM detalles_compras
    WHERE encabezados_compras_id_compra = :OLD.encabezados_compras_id_compra;

    IF v_count_detalles = 1 THEN
        RAISE_APPLICATION_ERROR(-20008, 'No se puede eliminar el unico detalle de la compra; primero debe eliminarse o ajustarse el encabezado de compra');
    END IF;
END;
/

/* Después de cada delete se actualiza el total de la compra
y se rebaja el stock del producto */
CREATE OR REPLACE TRIGGER trg_det_comp_ad_total
AFTER DELETE ON M_HAMILTON_STORE.detalles_compras
FOR EACH ROW
BEGIN
    M_HAMILTON_STORE.pkg_ctx_gestion_stock.g_tipo_gestion := 1;
    M_HAMILTON_STORE.pkg_ctx_gestion_stock.g_origen := 'DETALLE_COMPRA';

    UPDATE productos
    SET cantidad = cantidad - :OLD.cantidad
    WHERE id_producto = :OLD.productos_id_producto;

    UPDATE encabezados_compras
    SET total_compra = total_compra - (:OLD.cantidad * :OLD.precio_unitario)
    WHERE id_compra = :OLD.encabezados_compras_id_compra;
END;
/

-- Triggers en USUARIOS

-- Antes de cada insert: si hay empleado asociado, validar coherencia empleado / usuario.
-- (Si empleados_id_empleado es NULL, es usuario solo-cliente: no aplica esta validación.)
CREATE OR REPLACE TRIGGER trg_usr_bi_empleados
BEFORE INSERT ON M_HAMILTON_STORE.usuarios
FOR EACH ROW
WHEN (NEW.empleados_id_empleado IS NOT NULL)
DECLARE
    v_estado_empleado estados.nombre%TYPE;
    v_id_estado_activo estados.id_estado%TYPE;
BEGIN
    SELECT e2.nombre
    INTO v_estado_empleado
    FROM empleados e
    JOIN estados e2 ON e.estados_id_estado = e2.id_estado
    WHERE e.id_empleado = :NEW.empleados_id_empleado;

    IF LOWER(TRIM(v_estado_empleado)) = 'activo' THEN
        RETURN;
    END IF;

    SELECT id_estado
    INTO v_id_estado_activo
    FROM estados
    WHERE LOWER(TRIM(nombre)) = 'activo'
    FETCH FIRST 1 ROW ONLY;

    IF :NEW.estados_id_estado = v_id_estado_activo THEN
        RAISE_APPLICATION_ERROR(
            -20009,
            'No se puede crear un usuario ACTIVO vinculado a un empleado INACTIVO'
        );
    END IF;
END;
/

/* Antes de cada insert se normaliza el username a minúsculas */
CREATE OR REPLACE TRIGGER trg_usr_bi_username
BEFORE INSERT ON M_HAMILTON_STORE.usuarios
FOR EACH ROW
BEGIN
    :NEW.username := LOWER(:NEW.username);
END;
/

/* Antes de cada update, si el empleado asociado está inactivo,
el usuario se actualiza automáticamente a estado inactivo. */
CREATE OR REPLACE TRIGGER trg_usr_bu_empleados
BEFORE UPDATE ON M_HAMILTON_STORE.usuarios
FOR EACH ROW
WHEN (NEW.empleados_id_empleado IS NOT NULL)
DECLARE
    v_estado_empleado estados.nombre%TYPE;
    v_estado_inactivo estados.id_estado%TYPE;
BEGIN
    SELECT e2.nombre
    INTO v_estado_empleado
    FROM empleados e
    JOIN estados e2 ON e.estados_id_estado = e2.id_estado
    WHERE e.id_empleado = :NEW.empleados_id_empleado;

    SELECT id_estado
    INTO v_estado_inactivo
    FROM estados
    WHERE LOWER(nombre) = 'inactivo'
    FETCH FIRST 1 ROWS ONLY;

    IF LOWER(v_estado_empleado) = 'inactivo' THEN
        :NEW.estados_id_estado := v_estado_inactivo;
    END IF;
END;
/

/* Después de cada update se valida que un usuario no pueda quedar activo
si su empleado está inactivo (solo si hay empleado vinculado). */
CREATE OR REPLACE TRIGGER trg_usr_au_empleados
AFTER UPDATE ON M_HAMILTON_STORE.usuarios
FOR EACH ROW
WHEN (NEW.empleados_id_empleado IS NOT NULL)
DECLARE
    v_estado_empleado estados.nombre%TYPE;
    v_estado_activo estados.id_estado%TYPE;
BEGIN
    SELECT e2.nombre
    INTO v_estado_empleado
    FROM empleados e
    JOIN estados e2 ON e.estados_id_estado = e2.id_estado
    WHERE e.id_empleado = :NEW.empleados_id_empleado;

    SELECT id_estado
    INTO v_estado_activo
    FROM estados
    WHERE LOWER(nombre) = 'activo'
    FETCH FIRST 1 ROWS ONLY;

    IF LOWER(v_estado_empleado) = 'inactivo'
       AND :NEW.estados_id_estado = v_estado_activo THEN

        RAISE_APPLICATION_ERROR(-20016, 'Un usuario no puede estar activo si su empleado está inactivo');
    END IF;
END;
/

/* Antes de cada delete se valida que el usuario no este activo */
CREATE OR REPLACE TRIGGER trg_usr_bd_estado
BEFORE DELETE ON M_HAMILTON_STORE.usuarios
FOR EACH ROW
DECLARE
    v_estado_activo estados.id_estado%TYPE;
BEGIN
    SELECT id_estado
    INTO v_estado_activo
    FROM estados
    WHERE LOWER(nombre) = 'activo'
    FETCH FIRST 1 ROWS ONLY;

    IF :OLD.estados_id_estado = v_estado_activo THEN
        RAISE_APPLICATION_ERROR(-20010, 'No se puede eliminar un usuario que se encuentra activo');
    END IF;
END;
/

-- Después de cada delete se actualiza a inactivo el cliente asociado.
CREATE OR REPLACE TRIGGER trg_usr_ad_clientes
AFTER DELETE ON M_HAMILTON_STORE.usuarios
FOR EACH ROW
DECLARE
    v_estado_inactivo estados.id_estado%TYPE;
BEGIN
    IF :OLD.clientes_id_cliente IS NOT NULL THEN
        SELECT id_estado
        INTO v_estado_inactivo
        FROM estados
        WHERE LOWER(nombre) = 'inactivo'
        FETCH FIRST 1 ROWS ONLY;

        UPDATE clientes
        SET estados_id_estado = v_estado_inactivo
        WHERE id_cliente = :OLD.clientes_id_cliente;
    END IF;
END;
/

-- Triggers en FACTURAS

-- Antes de cada insert se valida que la fecha de emisión no sea futura
CREATE OR REPLACE TRIGGER trg_fac_bi
BEFORE INSERT ON M_HAMILTON_STORE.facturas
FOR EACH ROW
BEGIN
    IF :NEW.fecha_emision > SYSDATE THEN
        RAISE_APPLICATION_ERROR(-20011, 'La fecha de emisión no puede ser futura');
    END IF;
END;
/

/* Después de cada insert se asigna una clave_hacienda temporal si no fue 
proporcionada */
CREATE OR REPLACE TRIGGER trg_fac_ai
AFTER INSERT ON M_HAMILTON_STORE.facturas
FOR EACH ROW
BEGIN
    IF :NEW.clave_hacienda IS NULL THEN
        UPDATE facturas
        SET clave_hacienda = 'TEMP-' || TO_CHAR(:NEW.id_factura)
        WHERE id_factura = :NEW.id_factura;
    END IF;
END;
/

/* Antes de cada update se valida que la clave_hacienda no se modifique
si ya fue asignada previamente */
CREATE OR REPLACE TRIGGER trg_fac_bu_clave
BEFORE UPDATE ON M_HAMILTON_STORE.facturas
FOR EACH ROW
BEGIN
    IF :OLD.clave_hacienda IS NOT NULL
       AND :OLD.clave_hacienda != :NEW.clave_hacienda THEN
        RAISE_APPLICATION_ERROR(
            -20015, 'No se puede modificar la clave_hacienda una vez asignada'
        );
    END IF;
END;
/

-- Antes de update, normalizar el numero de factura.
CREATE OR REPLACE TRIGGER trg_fac_bu_numero
BEFORE UPDATE ON M_HAMILTON_STORE.facturas
FOR EACH ROW
BEGIN
    :NEW.numero_factura := TRIM(UPPER(:NEW.numero_factura));
END;
/

-- Antes de cada delete se valida que la factura no esté activa ni procesada
CREATE OR REPLACE TRIGGER trg_fac_bd_estados
BEFORE DELETE ON M_HAMILTON_STORE.facturas
FOR EACH ROW
DECLARE
    v_count NUMBER;
BEGIN
    SELECT COUNT(*)
    INTO v_count
    FROM estados
    WHERE id_estado = :OLD.estados_id_estado
      AND LOWER(nombre) IN ('activo', 'procesado');

    IF v_count > 0 THEN
        RAISE_APPLICATION_ERROR(-20012, 'No se puede eliminar una factura activa o procesada');
    END IF;
END;
/

-- Antes de cada delete se valida que la factura no tenga XML generado
CREATE OR REPLACE TRIGGER trg_fac_bd_xml
BEFORE DELETE ON M_HAMILTON_STORE.facturas
FOR EACH ROW
BEGIN
    IF :OLD.xml IS NOT NULL THEN
        RAISE_APPLICATION_ERROR(-20013, 'No se puede eliminar una factura con XML generado');
    END IF;
END;
/

-- Triggers en PROVEEDORES

-- Antes de cada insert se normaliza la página web del proveedor
CREATE OR REPLACE TRIGGER trg_prov_bi_web
BEFORE INSERT ON M_HAMILTON_STORE.proveedores
FOR EACH ROW
BEGIN
    IF :NEW.pagina_web IS NOT NULL THEN
        :NEW.pagina_web := LOWER(TRIM(:NEW.pagina_web));
    END IF;
END;
/

/* Después de cada insert se valida que el proveedor haya quedado con un estado 
permitido */
CREATE OR REPLACE TRIGGER trg_prov_ai_estado
AFTER INSERT ON M_HAMILTON_STORE.proveedores
FOR EACH ROW
DECLARE
    v_estado_nombre estados.nombre%TYPE;
BEGIN
    SELECT nombre
    INTO v_estado_nombre
    FROM estados
    WHERE id_estado = :NEW.estados_id_estado;

    IF LOWER(v_estado_nombre) NOT IN ('activo', 'inactivo') THEN
        RAISE_APPLICATION_ERROR(-20014, 'El estado asignado al proveedor no es válido');
    END IF;
END;
/

/* Antes de cada update se normaliza la página web del proveedor */
CREATE OR REPLACE TRIGGER trg_prov_bu_web
BEFORE UPDATE ON M_HAMILTON_STORE.proveedores
FOR EACH ROW
BEGIN
    IF :NEW.pagina_web IS NOT NULL THEN
        :NEW.pagina_web := LOWER(TRIM(:NEW.pagina_web));
    END IF;
END;
/

/* Antes de cada update se valida que, si el proveedor pasa a inactivo, en esa 
misma operación no se modifiquen otros datos del proveedor */
CREATE OR REPLACE TRIGGER trg_prov_bu_estado
BEFORE UPDATE ON M_HAMILTON_STORE.proveedores
FOR EACH ROW
DECLARE
    v_estado_inactivo estados.id_estado%TYPE;
BEGIN
    SELECT id_estado
    INTO v_estado_inactivo
    FROM estados
    WHERE LOWER(nombre) = 'inactivo'
    FETCH FIRST 1 ROWS ONLY;

    IF :OLD.estados_id_estado != v_estado_inactivo
       AND :NEW.estados_id_estado = v_estado_inactivo THEN
       
        IF NVL(:OLD.nombre, ' ') != NVL(:NEW.nombre, ' ')
           OR NVL(:OLD.cedula_juridica, ' ') != NVL(:NEW.cedula_juridica, ' ')
           OR NVL(:OLD.pagina_web, ' ') != NVL(:NEW.pagina_web, ' ') THEN

            RAISE_APPLICATION_ERROR(-20015, 'Si se inactiva un proveedor, no se pueden modificar otros datos en la misma operación');
        END IF;

    END IF;
END;
/

-- Antes de cada delete se valida que el proveedor no tenga compras registradas.
CREATE OR REPLACE TRIGGER trg_prov_bd_compras
BEFORE DELETE ON M_HAMILTON_STORE.proveedores
FOR EACH ROW
DECLARE
    v_count_compras NUMBER;
BEGIN
    SELECT COUNT(*)
    INTO v_count_compras
    FROM encabezados_compras
    WHERE proveedores_id_proveedor = :OLD.id_proveedor;

    IF v_count_compras > 0 THEN
        RAISE_APPLICATION_ERROR(-20015, 'No se puede eliminar un proveedor con compras registradas');
    END IF;
END;
/

/* Antes de eliminar proveedor se eliminan sus contactos */
CREATE OR REPLACE TRIGGER trg_prov_bd_contactos
BEFORE DELETE ON M_HAMILTON_STORE.proveedores
FOR EACH ROW
BEGIN
    DELETE FROM contactos_proveedores
    WHERE proveedores_id_proveedor = :OLD.id_proveedor;
END;
/