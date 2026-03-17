/**
 * tienda-checkout.js - Mock pasarela de pagos para tienda (proyecto U)
 */
(function () {
  'use strict';

  const STORAGE_VENTAS = 'hamilton_ventas';

  function formatMoney(n) {
    return '₡' + Number(n).toLocaleString('es-CR');
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

  function procesarPago() {
    if (!window.TiendaCarrito) return;
    const items = window.TiendaCarrito.getItems();
    if (items.length === 0) {
      alert('El carrito est\u00e1 vac\u00edo');
      return;
    }

    const metodo = document.getElementById('metodoPago')?.value || 'tarjeta';
    const total = window.TiendaCarrito.getTotal();

    const venta = {
      id: Date.now(),
      fecha: new Date().toISOString(),
      origen: 'tienda',
      clienteId: null,
      clienteNombre: 'Cliente tienda',
      items: items.map(i => ({
        productoId: i.productoId,
        nombre: i.nombre,
        cantidad: i.cantidad,
        precioUnitario: i.precioVenta,
        subtotal: i.cantidad * i.precioVenta
      })),
      total: total,
      pagos: [{ monto: total, metodo: metodo, fecha: new Date().toISOString() }]
    };

    const ventas = JSON.parse(localStorage.getItem(STORAGE_VENTAS) || '[]');
    ventas.push(venta);
    localStorage.setItem(STORAGE_VENTAS, JSON.stringify(ventas));

    window.TiendaCarrito.clear();
    showSuccess();
  }

  document.addEventListener('DOMContentLoaded', function () {
    render();

    document.getElementById('metodoPago')?.addEventListener('change', function () {
      const v = this.value;
      const tarjeta = document.getElementById('mockTarjeta');
      if (tarjeta) tarjeta.style.display = v === 'tarjeta' ? 'block' : 'none';
    });

    document.getElementById('btnPagar')?.addEventListener('click', function () {
      this.disabled = true;
      this.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Procesando...';
      setTimeout(function () {
        procesarPago();
      }, 800);
    });

    window.addEventListener('carrito-changed', render);
  });
})();
