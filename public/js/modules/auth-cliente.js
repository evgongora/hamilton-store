/**
 * auth-cliente.js - Cookie local legacy (checkout / demos).
 * Registro real de clientes: public/pages/auth/registro_cliente.php → auth_register_cliente.php (Oracle).
 * Login unificado: public/pages/auth/login.php → auth_login.php (sesión PHP).
 */
(function () {
  'use strict';

  function uiAlert(msg, title) {
    if (window.UiDialog && window.UiDialog.alert) {
      return window.UiDialog.alert(String(msg), { title: title || 'Cuenta' });
    }
    alert(msg);
    return Promise.resolve();
  }

  const STORAGE_CLIENTES = 'hamilton_clientes';
  const COOKIE_NAME = 'hamilton_cliente';
  const COOKIE_DAYS = 7;

  function getClientes() {
    try {
      return JSON.parse(localStorage.getItem(STORAGE_CLIENTES) || '[]');
    } catch (e) { return []; }
  }

  function saveClientes(arr) {
    localStorage.setItem(STORAGE_CLIENTES, JSON.stringify(arr));
  }

  function setClienteCookie(cliente) {
    const val = JSON.stringify({
      id: cliente.id,
      nombre: cliente.nombre,
      apellido: cliente.apellido,
      email: cliente.email
    });
    const days = COOKIE_DAYS * 24 * 60 * 60;
    document.cookie = COOKIE_NAME + '=' + encodeURIComponent(val) + '; path=/; max-age=' + days + '; SameSite=Lax';
  }

  function clearClienteCookie() {
    document.cookie = COOKIE_NAME + '=; path=/; max-age=0';
  }

  function getClienteFromCookie() {
    const m = document.cookie.match(new RegExp('(^| )' + COOKIE_NAME + '=([^;]+)'));
    if (!m) return null;
    try {
      return JSON.parse(decodeURIComponent(m[2]));
    } catch (e) { return null; }
  }

  window.AuthCliente = {
    registrar(data) {
      const { nombre, apellido, email, telefono, password } = data;
      if (!nombre || !apellido || !email || !telefono || !password) {
        void uiAlert('Complete todos los campos.');
        return;
      }

      const clientes = getClientes();
      const existe = clientes.find(c => (c.email || '').toLowerCase() === email);
      if (existe) {
        if (!existe.password) {
          existe.password = password;
          saveClientes(clientes);
          setClienteCookie(existe);
          uiAlert('Contraseña establecida. Ya puedes comprar.', 'Listo').then(function () {
            window.location.href = '/hamilton-store/public/pages/tienda/Homepage.php';
          });
          return;
        }
        uiAlert('Ya existe una cuenta con ese email. Inicia sesión.', 'Cuenta existente').then(function () {
          window.location.href = '/hamilton-store/public/pages/auth/login.php';
        });
        return;
      }

      const maxId = Math.max(0, ...clientes.map(c => c.id || 0)) + 1;
      const hoy = new Date().toISOString().slice(0, 10);
      const nuevo = {
        id: maxId,
        nombre,
        apellido,
        email,
        telefono,
        fechaIngreso: hoy,
        estadosIdEstado: 1,
        password: password
      };
      clientes.push(nuevo);
      saveClientes(clientes);

      setClienteCookie(nuevo);
      uiAlert('¡Cuenta creada! Ya puedes comprar.', 'Listo').then(function () {
        window.location.href = '/hamilton-store/public/pages/tienda/Homepage.php';
      });
    },

    login(email, password) {
      const clientes = getClientes();
      const c = clientes.find(x => (x.email || '').toLowerCase() === email.toLowerCase());
      if (!c) {
        void uiAlert('Email o contraseña incorrectos.', 'Error');
        return false;
      }
      if ((c.password || '') !== password) {
        void uiAlert('Email o contraseña incorrectos.', 'Error');
        return false;
      }
      setClienteCookie(c);
      return true;
    },

    logout() {
      clearClienteCookie();
    },

    getClienteActual() {
      return getClienteFromCookie();
    },

    isLoggedIn() {
      return !!getClienteFromCookie();
    }
  };
})();
