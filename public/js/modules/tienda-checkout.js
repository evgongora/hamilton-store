/**
 * tienda-checkout.js — Checkout tienda: métodos de pago y venta+pago en Oracle (API).
 */
(function () {
  'use strict';

  function uiAlert(msg, title) {
    if (window.UiDialog && window.UiDialog.alert) {
      return window.UiDialog.alert(String(msg), { title: title || 'Checkout' });
    }
    alert(msg);
    return Promise.resolve();
  }

  function formatMoney(n) {
    return '₡' + Number(n).toLocaleString('es-CR');
  }

  function escapeHtml(s) {
    const div = document.createElement('div');
    div.textContent = s;
    return div.innerHTML;
  }

  function loadMetodosPago() {
    const sel = document.getElementById('metodoPago');
    if (!sel) return Promise.resolve();
    if (!window.Api) {
      sel.innerHTML = '<option value="">API no disponible</option>';
      return Promise.resolve();
    }
    return window.Api
      .get('/metodos_pago_list.php')
      .then(function (json) {
        const rows = json.data || [];
        sel.innerHTML = '';
        if (rows.length === 0) {
          sel.innerHTML = '<option value="">— Sin métodos —</option>';
          return rows;
        }
        rows.forEach(function (m) {
          const opt = document.createElement('option');
          opt.value = String(m.id);
          opt.textContent = m.nombre;
          sel.appendChild(opt);
        });
        return rows;
      })
      .catch(function () {
        sel.innerHTML = '<option value="">Error al cargar métodos</option>';
        return [];
      });
  }

  function render() {
    if (!window.TiendaCarrito) return;
    const items = window.TiendaCarrito.getItems();
    const total = window.TiendaCarrito.getTotal();

    const emptyEl = document.getElementById('checkoutEmpty');
    const contentEl = document.getElementById('checkoutContent');
    const successEl = document.getElementById('checkoutSuccess');

    if (successEl && successEl.style.display !== 'none') return;

    if (items.length === 0) {
      if (emptyEl) emptyEl.style.display = 'block';
      if (contentEl) contentEl.style.display = 'none';
      return;
    }

    if (emptyEl) emptyEl.style.display = 'none';
    if (contentEl) contentEl.style.display = 'flex';

    const tbody = document.getElementById('checkoutItems');
    const totalEl = document.getElementById('checkoutTotal');
    if (!tbody || !totalEl) return;

    tbody.innerHTML = '';
    items.forEach(function (item) {
      const tr = document.createElement('tr');
      const subtotal = item.precioVenta * item.cantidad;
      tr.innerHTML =
        '<td>' +
        escapeHtml(item.nombre) +
        '</td><td class="text-end">' +
        item.cantidad +
        '</td><td class="text-end">' +
        formatMoney(item.precioVenta) +
        '</td><td class="text-end">' +
        formatMoney(subtotal) +
        '</td><td>' +
        '<button type="button" class="btn btn-outline-danger btn-sm remove-item" data-id="' +
        item.productoId +
        '">' +
        '<i class="bi bi-trash"></i></button></td>';
      tbody.appendChild(tr);
    });

    totalEl.textContent = formatMoney(total);

    tbody.querySelectorAll('.remove-item').forEach(function (btn) {
      btn.addEventListener('click', function () {
        window.TiendaCarrito.remove(parseInt(btn.getAttribute('data-id'), 10));
        render();
      });
    });
  }

  function showSuccess() {
    const contentEl = document.getElementById('checkoutContent');
    const emptyEl = document.getElementById('checkoutEmpty');
    const successEl = document.getElementById('checkoutSuccess');
    if (contentEl) contentEl.style.display = 'none';
    if (emptyEl) emptyEl.style.display = 'none';
    if (successEl) successEl.style.display = 'block';
  }

  function getClienteActual() {
    const s = window.HAMILTON_CHECKOUT_CLIENTE;
    if (!s || s.id == null || s.id === '') {
      return null;
    }
    const id = typeof s.id === 'number' ? s.id : parseInt(String(s.id), 10);
    if (!id || Number.isNaN(id)) {
      return null;
    }
    return { id: id, nombre: s.nombre, apellido: s.apellido };
  }

  function procesarPago() {
    if (!window.TiendaCarrito || !window.Api) {
      void uiAlert('No se puede completar el pago. Recargue la página.');
      return Promise.resolve();
    }
    const items = window.TiendaCarrito.getItems();
    if (items.length === 0) {
      void uiAlert('El carrito está vacío');
      return Promise.resolve();
    }

    const metodoPagoId = parseInt(document.getElementById('metodoPago').value, 10);
    if (!metodoPagoId) {
      void uiAlert('Seleccione un método de pago');
      return Promise.resolve();
    }

    const cliente = getClienteActual();
    if (!cliente || !cliente.id) {
      void uiAlert('Sesión de cliente no válida. Vuelva a iniciar sesión.');
      return Promise.resolve();
    }

    const V = window.HamiltonValidation;
    if (V && typeof V.carritoVentasLineasMensaje === 'function') {
      const errCart = V.carritoVentasLineasMensaje(items);
      if (errCart) {
        void uiAlert(errCart);
        return Promise.resolve();
      }
    }

    const total = window.TiendaCarrito.getTotal();
    if (!isFinite(total) || total <= 0) {
      void uiAlert('El total de la orden no es válido.');
      return Promise.resolve();
    }
    if (V && typeof V.montoPositivo === 'function' && !V.montoPositivo(total)) {
      void uiAlert('El total de la orden no es válido.');
      return Promise.resolve();
    }
    const hoy = new Date().toISOString().slice(0, 10);
    const lineas = items.map(function (i) {
      return {
        productoId: i.productoId,
        cantidad: i.cantidad,
      };
    });

    const bodyVenta = {
      fechaVenta: hoy,
      clienteId: cliente.id,
      lineas: lineas,
    };

    return window.Api
      .post('/ventas_create.php', bodyVenta)
      .then(function (res) {
        const idVenta = res.idVenta;
        if (!idVenta) {
          throw new Error('Respuesta sin id de venta');
        }
        return window.Api.post('/pagos_create.php', {
          action: 'insert',
          monto: total,
          fechaPago: hoy,
          idMetodoPago: metodoPagoId,
          idVenta: idVenta,
        });
      })
      .then(function () {
        window.TiendaCarrito.clear();
        showSuccess();
      })
      .catch(function (e) {
        void uiAlert(e.message || 'No se pudo registrar la compra.');
      });
  }

  document.addEventListener('DOMContentLoaded', function () {
    loadMetodosPago().then(function () {});
    render();

    document.getElementById('btnPagar')?.addEventListener('click', function () {
      const btn = this;
      const prev = btn.innerHTML;
      btn.disabled = true;
      btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Procesando...';
      void Promise.resolve(procesarPago()).finally(function () {
        btn.disabled = false;
        btn.innerHTML = prev;
      });
    });

    window.addEventListener('carrito-changed', render);
  });
})();
