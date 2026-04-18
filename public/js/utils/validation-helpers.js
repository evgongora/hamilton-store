/**
 * Validaciones alineadas con reglas de la API / Oracle (pkg_clientes, teléfonos, ventas, etc.).
 * Usar antes de Api.post para evitar enviar basura y mejorar UX.
 */
(function (global) {
  'use strict';

  var H = {
    MAX_LINEAS_VENTA: 100,
    MAX_LINEAS_COMPRA: 100,
    PRECIO_COMPRA_LINEA_MAX: 1e12,

    trim: function (s) {
      return s == null ? '' : String(s).trim();
    },
    isEmpty: function (s) {
      return H.trim(s) === '';
    },
    /** pkg_clientes.fn_nombre_valido — solo A-Z y espacios */
    clienteNombreOracle: function (s) {
      var t = H.trim(s);
      return t !== '' && /^[A-Za-z ]+$/.test(t);
    },
    /** pkg_clientes.fn_email_valido */
    clienteEmailOracle: function (s) {
      var t = H.trim(s);
      return (
        t !== '' &&
        /^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,}$/.test(t)
      );
    },
    /** Registro / usuarios cliente: mismo patrón que auth_register_cliente.php */
    usernameRegistroCliente: function (s) {
      var t = H.trim(s);
      return t.length >= 3 && t.length <= 50 && /^[A-Za-z0-9._-]+$/.test(t);
    },
    /** Usuario sistema / tienda (misma regla que registro; típicamente en minúsculas). */
    usernameSistemaMensaje: function (s) {
      var t = H.trim(s);
      if (t === '') {
        return 'Indique un nombre de usuario.';
      }
      if (t.indexOf('\0') !== -1) {
        return 'Usuario: contiene caracteres no permitidos.';
      }
      if (t.length < 3 || t.length > 50) {
        return 'Usuario: entre 3 y 50 caracteres.';
      }
      if (!/^[A-Za-z0-9._-]+$/.test(t)) {
        return 'Usuario: solo letras, números, punto, guion y guion bajo.';
      }
      return null;
    },
    /**
     * Contraseña alta/edición usuario (mín. 8 como registro cliente; máx. 100 del formulario).
     * @param {string} s valor del campo
     * @param {boolean} required obligatoria (alta)
     */
    passwordUsuarioMensaje: function (s, required) {
      var raw = String(s == null ? '' : s);
      if (raw.indexOf('\0') !== -1) {
        return 'La contraseña contiene caracteres no permitidos.';
      }
      if (!required && raw === '') {
        return null;
      }
      if (raw === '') {
        return 'La contraseña es obligatoria.';
      }
      if (raw.length < 8) {
        return 'La contraseña debe tener al menos 8 caracteres.';
      }
      if (raw.length > 100) {
        return 'La contraseña: máximo 100 caracteres.';
      }
      return null;
    },
    /** pkg telefonos: dígitos, espacios, guiones, paréntesis */
    telefonoOracle: function (s) {
      var t = H.trim(s);
      return t !== '' && /^[0-9() -]+$/.test(t);
    },
    positiveInt: function (n) {
      var x = typeof n === 'number' ? n : parseInt(String(n), 10);
      return !isNaN(x) && x > 0;
    },
    nonEmptyText: function (s, maxLen) {
      var t = H.trim(s);
      if (t === '') return false;
      if (String(t).indexOf('\0') !== -1) return false;
      if (maxLen != null && t.length > maxLen) return false;
      return true;
    },
    /** Texto libre: sin NUL, longitud; allowEmpty permite cadena vacía tras trim. Devuelve mensaje o null. */
    textoLibreMensaje: function (s, maxLen, allowEmpty, label) {
      label = label || 'Texto';
      var raw = String(s == null ? '' : s);
      if (raw.indexOf('\0') !== -1) {
        return label + ': contiene caracteres no permitidos.';
      }
      var t = H.trim(raw);
      if (!allowEmpty && t === '') {
        return label + ': es obligatorio.';
      }
      if (maxLen != null && t.length > maxLen) {
        return label + ': máximo ' + maxLen + ' caracteres.';
      }
      return null;
    },
    /** Precio ≥ 0 y finito (productos, líneas de compra). */
    precioNoNegativo: function (v) {
      if (v === '' || v == null) return false;
      var x = typeof v === 'number' ? v : parseFloat(String(v).replace(',', '.'));
      return !isNaN(x) && isFinite(x) && x >= 0;
    },
    /** Monto de pago > 0 y finito */
    montoPositivo: function (v) {
      if (v === '' || v == null) return false;
      var x = typeof v === 'number' ? v : parseFloat(String(v).replace(',', '.'));
      return !isNaN(x) && isFinite(x) && x > 0;
    },
    /** Entero ≥ 0 (stock producto) */
    enteroNoNegativo: function (v) {
      var x = parseInt(String(v).trim(), 10);
      return !isNaN(x) && isFinite(x) && x >= 0;
    },
    /** Nombre de producto: no vacío, máx. 200 caracteres */
    productoNombre: function (s) {
      var t = H.trim(s);
      return t !== '' && t.length <= 200;
    },
    fechaYyyyMmDd: function (s) {
      var t = H.trim(s);
      if (!/^\d{4}-\d{2}-\d{2}$/.test(t)) return false;
      var d = new Date(t + 'T12:00:00');
      return !isNaN(d.getTime());
    },
    /**
     * Carrito POS / checkout → líneas venta. Devuelve null si OK o mensaje de error.
     * @param {{ productoId: number, cantidad: number }[]} carrito
     */
    carritoVentasLineasMensaje: function (carrito) {
      if (!carrito || !carrito.length) {
        return 'El carrito está vacío.';
      }
      if (carrito.length > H.MAX_LINEAS_VENTA) {
        return 'Demasiadas líneas (máximo ' + H.MAX_LINEAS_VENTA + ').';
      }
      var i;
      for (i = 0; i < carrito.length; i++) {
        var it = carrito[i];
        var id = parseInt(it.productoId, 10);
        var c = parseInt(it.cantidad, 10);
        if (!H.positiveInt(id)) {
          return 'Hay un producto inválido en el carrito.';
        }
        if (!H.positiveInt(c)) {
          return 'Hay una cantidad inválida en el carrito.';
        }
      }
      return null;
    },
    /**
     * Carrito de compras a proveedor. Devuelve null o mensaje.
     */
    carritoComprasLineasMensaje: function (carrito) {
      if (!carrito || !carrito.length) {
        return 'Agregue al menos una línea.';
      }
      if (carrito.length > H.MAX_LINEAS_COMPRA) {
        return 'Demasiadas líneas (máximo ' + H.MAX_LINEAS_COMPRA + ').';
      }
      var i;
      for (i = 0; i < carrito.length; i++) {
        var it = carrito[i];
        var id = parseInt(it.productoId, 10);
        var c = parseInt(it.cantidad, 10);
        var pu = typeof it.precioUnitario === 'number' ? it.precioUnitario : parseFloat(String(it.precioUnitario).replace(',', '.'));
        if (!H.positiveInt(id)) {
          return 'Hay un producto inválido en el carrito.';
        }
        if (!H.positiveInt(c)) {
          return 'La cantidad debe ser un entero mayor que cero en cada línea.';
        }
        if (!isFinite(pu) || pu < 0 || pu > H.PRECIO_COMPRA_LINEA_MAX) {
          return 'Precio unitario inválido en una línea.';
        }
      }
      return null;
    },
    /** Empleado: nombre, apellido, puesto, email (misma idea que empleados_save.php) */
    empleadoFormMensaje: function (nombre, apellido, puesto, email) {
      var e;
      e = H.textoLibreMensaje(nombre, 200, false, 'Nombre');
      if (e) return e;
      e = H.textoLibreMensaje(apellido, 200, false, 'Apellido');
      if (e) return e;
      e = H.textoLibreMensaje(puesto, 200, false, 'Puesto');
      if (e) return e;
      if (!H.clienteEmailOracle(email)) {
        return 'Email con formato inválido.';
      }
      return null;
    },
    /** Proveedor: nombre obligatorio; cédula y web opcionales con longitud */
    proveedorFormMensaje: function (nombre, cedula, web, idEstado) {
      var e = H.textoLibreMensaje(nombre, 200, false, 'Nombre');
      if (e) return e;
      e = H.textoLibreMensaje(cedula, 30, true, 'Cédula jurídica');
      if (e) return e;
      e = H.textoLibreMensaje(web, 500, true, 'Página web');
      if (e) return e;
      if (!H.positiveInt(idEstado)) {
        return 'Seleccione un estado.';
      }
      return null;
    },
    /** Contacto de proveedor */
    contactoProveedorFormMensaje: function (nombre, apellido, email, telefono) {
      var e = H.textoLibreMensaje(nombre, 200, false, 'Nombre');
      if (e) return e;
      e = H.textoLibreMensaje(apellido, 200, false, 'Apellido');
      if (e) return e;
      if (!H.clienteEmailOracle(email)) {
        return 'Email del contacto con formato inválido.';
      }
      if (!H.telefonoOracle(telefono)) {
        return 'Teléfono inválido (solo dígitos, espacios, guiones y paréntesis).';
      }
      return null;
    },
    /** Producto modal guardar */
    productoFormMensaje: function (nombre, precioCompra, precioVenta, cantidad, idCat, idEst) {
      if (!H.productoNombre(nombre)) {
        return 'Indique el nombre del producto (máx. 200 caracteres).';
      }
      if (!H.precioNoNegativo(precioCompra) || !H.precioNoNegativo(precioVenta)) {
        return 'Precio de compra y precio de venta deben ser números ≥ 0.';
      }
      if (!H.enteroNoNegativo(cantidad)) {
        return 'La cantidad debe ser un entero ≥ 0.';
      }
      if (!H.positiveInt(idCat) || !H.positiveInt(idEst)) {
        return 'Seleccione categoría y estado.';
      }
      return null;
    },
  };

  global.HamiltonValidation = H;
})(typeof window !== 'undefined' ? window : globalThis);
