/**
 * ventas.js - Punto de venta: búsqueda, carrito, totales, confirmar venta
 */
(function () {
  'use strict';

  function uiAlert(msg, title) {
    if (window.UiDialog && window.UiDialog.alert) {
      return window.UiDialog.alert(String(msg), { title: title || 'Aviso' });
    }
    alert(msg);
    return Promise.resolve();
  }

  const basePath = (document.body.dataset.basePath || '/hamilton-store/public').replace(/\/$/, '');

  let productos = [];
  let clientes = [];
  let carrito = [];

  function formatMoney(n) {
    return '₡' + Number(n).toLocaleString('es-CR');
  }

  function loadProductos() {
    if (!window.Api) {
      productos = [];
      return Promise.resolve(productos);
    }
    return window.Api
      .get('/productos_list.php')
      .then(function (json) {
        productos = json.data || [];
        return productos;
      })
      .catch(function () {
        productos = [];
        return productos;
      });
  }

  function loadClientes() {
    if (!window.Api) {
      clientes = [];
      return Promise.resolve(clientes);
    }
    return window.Api
      .get('/clientes_list.php')
      .then(function (json) {
        clientes = json.data || [];
        const sel = document.getElementById('clienteSelect');
        if (sel) {
          sel.innerHTML = '<option value="">-- Seleccionar cliente --</option>';
          clientes.forEach(function (c) {
            const opt = document.createElement('option');
            opt.value = c.id;
            opt.textContent = [c.nombre, c.apellido].filter(Boolean).join(' ');
            sel.appendChild(opt);
          });
        }
        return clientes;
      })
      .catch(function () {
        clientes = [];
        return clientes;
      });
  }

  function searchProducts(term) {
    if (!term || term.length < 2) return [];
    const t = term.toLowerCase();
    return productos.filter(p => p.nombre.toLowerCase().includes(t));
  }

  function addToCart(producto, cantidad) {
    const qty = Math.max(1, parseInt(cantidad, 10) || 1);
    const maxStock = producto.cantidad ?? 999;
    const found = carrito.find(i => i.productoId === producto.id);
    if (found) {
      found.cantidad = Math.min(found.cantidad + qty, maxStock);
      found.subtotal = found.cantidad * found.precioUnitario;
    } else {
      const finalQty = Math.min(qty, maxStock);
      carrito.push({
        productoId: producto.id,
        nombre: producto.nombre,
        cantidad: finalQty,
        precioUnitario: producto.precioVenta,
        subtotal: finalQty * producto.precioVenta
      });
    }
    renderCart();
  }

  function removeFromCart(productoId) {
    carrito = carrito.filter(i => i.productoId !== productoId);
    renderCart();
  }

  function updateCartQty(productoId, delta) {
    const item = carrito.find(i => i.productoId === productoId);
    if (!item) return;
    item.cantidad = Math.max(0, item.cantidad + delta);
    if (item.cantidad <= 0) {
      removeFromCart(productoId);
      return;
    }
    item.subtotal = item.cantidad * item.precioUnitario;
    renderCart();
  }

  function getTotal() {
    return carrito.reduce((sum, i) => sum + i.subtotal, 0);
  }

  function renderCart() {
    const tbody = document.getElementById('cartBody');
    const empty = document.getElementById('cartEmpty');
    const totalEl = document.getElementById('cartTotal');
    const countEl = document.getElementById('cartCount');
    const btn = document.getElementById('btnConfirmarVenta');

    tbody.innerHTML = '';
    countEl.textContent = carrito.length;

    if (carrito.length === 0) {
      empty.style.display = 'block';
      totalEl.textContent = formatMoney(0);
      btn.disabled = true;
      return;
    }
    empty.style.display = 'none';

    carrito.forEach(item => {
      const tr = document.createElement('tr');
      tr.innerHTML = `
        <td>${escapeHtml(item.nombre)}</td>
        <td class="text-end">
          <div class="btn-group btn-group-sm">
            <button type="button" class="btn btn-outline-secondary" data-action="minus" data-id="${item.productoId}">−</button>
            <span class="px-2 align-middle">${item.cantidad}</span>
            <button type="button" class="btn btn-outline-secondary" data-action="plus" data-id="${item.productoId}">+</button>
          </div>
        </td>
        <td class="text-end">${formatMoney(item.precioUnitario)}</td>
        <td class="text-end">${formatMoney(item.subtotal)}</td>
        <td>
          <button type="button" class="btn btn-outline-danger btn-sm" data-action="remove" data-id="${item.productoId}" title="Quitar"><i class="bi bi-trash"></i></button>
        </td>
      `;
      tbody.appendChild(tr);
    });

    totalEl.textContent = formatMoney(getTotal());
    btn.disabled = carrito.length === 0;

    tbody.querySelectorAll('[data-action]').forEach(btn => {
      btn.addEventListener('click', function () {
        const action = this.dataset.action;
        const id = parseInt(this.dataset.id, 10);
        if (action === 'plus') updateCartQty(id, 1);
        else if (action === 'minus') updateCartQty(id, -1);
        else if (action === 'remove') removeFromCart(id);
      });
    });
  }

  function escapeHtml(s) {
    const div = document.createElement('div');
    div.textContent = s;
    return div.innerHTML;
  }

  function confirmarVenta() {
    const clienteIdStr = document.getElementById('clienteSelect').value;
    if (!clienteIdStr) {
      void uiAlert('Seleccione un cliente.', 'Punto de venta');
      return;
    }
    if (carrito.length === 0) {
      void uiAlert('El carrito está vacío.', 'Punto de venta');
      return;
    }
    if (!window.Api) {
      void uiAlert('API no disponible.', 'Error');
      return;
    }

    const clienteId = parseInt(clienteIdStr, 10);
    const fechaVenta = new Date().toISOString().slice(0, 10);
    const lineas = carrito.map(function (i) {
      return {
        productoId: i.productoId,
        cantidad: i.cantidad,
        precioUnitario: i.precioUnitario,
        subtotal: i.subtotal
      };
    });

    const btn = document.getElementById('btnConfirmarVenta');
    if (btn) btn.disabled = true;

    window.Api
      .post('/ventas_create.php', {
        fechaVenta: fechaVenta,
        clienteId: clienteId,
        lineas: lineas
      })
      .then(function (res) {
        carrito = [];
        document.getElementById('clienteSelect').value = '';
        renderCart();
        document.getElementById('productSearch').value = '';
        document.getElementById('productResults').innerHTML = '';
        const msg =
          'Venta registrada. ID venta: ' + (res.idVenta || '') + (res.message ? ' — ' + res.message : '');
        return uiAlert(msg, 'Venta registrada');
      })
      .catch(function (e) {
        void uiAlert('No se pudo registrar la venta: ' + (e.message || String(e)), 'Error');
      })
      .finally(function () {
        if (btn) btn.disabled = carrito.length === 0;
      });
  }

  function renderProductResults(results) {
    const container = document.getElementById('productResults');
    container.innerHTML = '';
    if (results.length === 0) {
      container.innerHTML = '<div class="list-group-item text-muted">Sin resultados</div>';
      return;
    }
    results.forEach(p => {
      const a = document.createElement('a');
      a.href = '#';
      a.className = 'list-group-item list-group-item-action d-flex justify-content-between align-items-center';
      const maxStock = p.cantidad ?? 99;
      a.innerHTML = `
        <span>${escapeHtml(p.nombre)} <small class="text-muted">${formatMoney(p.precioVenta)}</small></span>
        <div class="input-group input-group-sm" style="width: 120px;">
          <input type="number" class="form-control qty-input" value="1" min="1" max="${maxStock}" data-id="${p.id}">
          <button type="button" class="btn btn-primary add-cart-btn" data-id="${p.id}">Agregar</button>
        </div>
      `;
      a.querySelector('.add-cart-btn').addEventListener('click', function (e) {
        e.preventDefault();
        e.stopPropagation();
        const input = a.querySelector('.qty-input');
        const qty = parseInt(input.value, 10) || 1;
        addToCart(p, qty);
      });
      container.appendChild(a);
    });
  }

  document.addEventListener('DOMContentLoaded', function () {
    const productResults = document.getElementById('productResults');
    if (productResults) {
      productResults.innerHTML =
        '<div class="d-flex justify-content-center py-3"><div class="spinner-border spinner-border-sm text-secondary" role="status"><span class="visually-hidden">Cargando</span></div></div>';
    }

    loadProductos()
      .catch(function () {
        productos = [];
      })
      .then(function () {
        if (productResults) {
          productResults.innerHTML =
            '<div class="list-group-item text-muted">Escriba al menos 2 caracteres para buscar productos.</div>';
        }
      });

    loadClientes().catch(function () {
      clientes = [];
    });

    const searchInput = document.getElementById('productSearch');
    searchInput.addEventListener('input', function () {
      const term = this.value.trim();
      const results = searchProducts(term);
      renderProductResults(results);
    });
    searchInput.addEventListener('focus', function () {
      const term = this.value.trim();
      if (term.length >= 2) renderProductResults(searchProducts(term));
    });

    document.getElementById('btnConfirmarVenta').addEventListener('click', confirmarVenta);

    renderCart();
  });
})();
