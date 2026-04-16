/**
 * tienda-checkout.js - Mock pasarela de pagos para tienda
 * Estructura alineada con BD: encabezados_ventas + detalles_ventas + pagos
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

  const STORAGE_VENTAS = 'hamilton_ventas';
  const basePath = '/hamilton-store/public';

  function formatMoney(n) {
    return '₡' + Number(n).toLocaleString('es-CR');
  }

  function loadMetodosPago() {
    const sel = document.getElementById('metodoPago');
    if (!sel) return Promise.resolve();
    return fetch(basePath + '/js/mocks/metodos_pago.json')
      .then(r => r.ok ? r.json() : [])
      .then(data => {
        sel.innerHTML = '';
        data.forEach(m => {
          const opt = document.createElement('option');
          opt.value = m.id;
          opt.textContent = m.nombre;
          sel.appendChild(opt);
        });
        return data;
      })
      .catch(() => []);
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
    items.forEach(item => {
      const tr = document.createElement('tr');
      const subtotal = item.precioVenta * item.cantidad;
      tr.innerHTML = `
        <td>${escapeHtml(item.nombre)}</td>
        <td class="text-end">${item.cantidad}</td>
        <td class="text-end">${formatMoney(item.precioVenta)}</td>
        <td class="text-end">${formatMoney(subtotal)}</td>
        <td>
          <button type="button" class="btn btn-outline-danger btn-sm remove-item" data-id="${item.productoId}">
            <i class="bi bi-trash"></i>
          </button>
        </td>
      `;
      tbody.appendChild(tr);
    });

    totalEl.textContent = formatMoney(total);

    tbody.querySelectorAll('.remove-item').forEach(btn => {
      btn.addEventListener('click', function () {
        window.TiendaCarrito.remove(parseInt(this.dataset.id, 10));
        render();
      });
    });
  }

  function escapeHtml(s) {
    const div = document.createElement('div');
    div.textContent = s;
    return div.innerHTML;
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
    if (s && typeof s.id === 'number') {
      return s;
    }
    if (typeof window.AuthCliente !== 'undefined' && window.AuthCliente.getClienteActual) {
      const c = window.AuthCliente.getClienteActual();
      if (c) return c;
    }
    const m = document.cookie.match(/(^| )hamilton_cliente=([^;]+)/);
    if (!m) return null;
    try {
      return JSON.parse(decodeURIComponent(m[2]));
    } catch (e) {
      return null;
    }
  }

  function procesarPago() {
    if (!window.TiendaCarrito) return;
    const items = window.TiendaCarrito.getItems();
    if (items.length === 0) {
      void uiAlert('El carrito está vacío');
      return;
    }

    const metodoPagoId = document.getElementById('metodoPago')?.value;
    if (!metodoPagoId) {
      void uiAlert('Seleccione un método de pago');
      return;
    }

    const total = window.TiendaCarrito.getTotal();
    const cliente = getClienteActual();
    const venta = {
      id: Date.now(),
      fecha: new Date().toISOString(),
      total,
      clientesIdCliente: cliente ? cliente.id : null,
      clienteNombre: cliente ? [cliente.nombre, cliente.apellido].filter(Boolean).join(' ') : 'Cliente tienda',
      empleadosIdEmpleado: 1,
      origen: 'tienda',
      items: items.map(i => ({
        productosIdProducto: i.productoId,
        nombre: i.nombre,
        cantidad: i.cantidad,
        precioUnitario: i.precioVenta,
        subtotal: i.cantidad * i.precioVenta
      })),
      pagos: [{ monto: total, fechaPago: new Date().toISOString(), metodosPagoIdMetodoPago: parseInt(metodoPagoId, 10) }]
    };

    const ventas = JSON.parse(localStorage.getItem(STORAGE_VENTAS) || '[]');
    ventas.push(venta);
    localStorage.setItem(STORAGE_VENTAS, JSON.stringify(ventas));

    window.TiendaCarrito.clear();
    showSuccess();
  }

  document.addEventListener('DOMContentLoaded', function () {
    loadMetodosPago().then(function () {
      document.getElementById('metodoPago')?.dispatchEvent(new Event('change'));
    });
    render();

    document.getElementById('metodoPago')?.addEventListener('change', function () {
      const v = parseInt(this.value, 10);
      const tarjeta = document.getElementById('mockTarjeta');
      if (tarjeta) tarjeta.style.display = (v === 3) ? 'block' : 'none';
    });

    document.getElementById('btnPagar')?.addEventListener('click', function () {
      const btn = this;
      btn.disabled = true;
      btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Procesando...';
      setTimeout(function () {
        procesarPago();
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-lock-fill me-2"></i>Pagar ahora';
      }, 800);
    });

    window.addEventListener('carrito-changed', render);
  });
})();
