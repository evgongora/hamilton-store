/**
 * ventas.js - Punto de venta: búsqueda, carrito, totales, confirmar venta (mock/localStorage)
 */
(function () {
  'use strict';

  const STORAGE_KEY = 'hamilton_ventas';
  const basePath = (document.body.dataset.basePath || '/hamilton-store/public').replace(/\/$/, '');

  let productos = [];
  let clientes = [];
  let carrito = [];

  function fetchJson(path) {
    return fetch(basePath + path).then(r => {
      if (!r.ok) throw new Error('Error cargando ' + path);
      return r.json();
    });
  }

  function formatMoney(n) {
    return '₡' + Number(n).toLocaleString('es-CR');
  }

  function loadProductos() {
    return fetchJson('/js/mocks/productos.json').then(data => {
      productos = data;
      return productos;
    });
  }

  function loadClientes() {
    return fetchJson('/js/mocks/clientes.json').then(data => {
      clientes = data;
      const sel = document.getElementById('clienteSelect');
      clientes.forEach(c => {
        const opt = document.createElement('option');
        opt.value = c.id;
        opt.textContent = c.nombre + (c.cedula ? ' (' + c.cedula + ')' : '');
        sel.appendChild(opt);
      });
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
    const found = carrito.find(i => i.productoId === producto.id);
    if (found) {
      found.cantidad += qty;
      found.subtotal = found.cantidad * found.precioUnitario;
    } else {
      carrito.push({
        productoId: producto.id,
        nombre: producto.nombre,
        cantidad: qty,
        precioUnitario: producto.precioVenta,
        subtotal: qty * producto.precioVenta
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
    const clienteId = document.getElementById('clienteSelect').value;
    const cliente = clientes.find(c => String(c.id) === clienteId);
    const clienteNombre = cliente ? cliente.nombre : 'Sin asignar';

    const venta = {
      id: Date.now(),
      fecha: new Date().toISOString(),
      clienteId: clienteId ? parseInt(clienteId, 10) : null,
      clienteNombre: clienteNombre,
      items: carrito.map(i => ({
        productoId: i.productoId,
        nombre: i.nombre,
        cantidad: i.cantidad,
        precioUnitario: i.precioUnitario,
        subtotal: i.subtotal
      })),
      total: getTotal(),
      pagos: []
    };

    const ventas = JSON.parse(localStorage.getItem(STORAGE_KEY) || '[]');
    ventas.push(venta);
    localStorage.setItem(STORAGE_KEY, JSON.stringify(ventas));

    carrito = [];
    document.getElementById('clienteSelect').value = '';
    renderCart();
    document.getElementById('productSearch').value = '';
    document.getElementById('productResults').innerHTML = '';

    alert('Venta registrada correctamente. Total: ' + formatMoney(venta.total));
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
      a.innerHTML = `
        <span>${escapeHtml(p.nombre)} <small class="text-muted">${formatMoney(p.precioVenta)}</small></span>
        <div class="input-group input-group-sm" style="width: 120px;">
          <input type="number" class="form-control qty-input" value="1" min="1" max="${p.stock}" data-id="${p.id}">
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
    loadProductos().catch(() => { productos = []; });
    loadClientes().catch(() => { clientes = []; });

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
