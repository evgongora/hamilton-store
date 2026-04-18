/**
 * compras.js — Compra a proveedor: carrito con costo, POST compras_create.php
 */
(function () {
  'use strict';

  function uiAlert(msg, title) {
    if (window.UiDialog && window.UiDialog.alert) {
      return window.UiDialog.alert(String(msg), { title: title || 'Compras' });
    }
    alert(msg);
    return Promise.resolve();
  }

  let productos = [];
  let carrito = [];

  function formatMoney(n) {
    return '₡' + Number(n).toLocaleString('es-CR');
  }

  function defaultPrecioCompra(p) {
    const v = p.precioCompra;
    if (v == null || v === '' || isNaN(Number(v))) {
      return 0;
    }
    return Number(v);
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

  function loadProveedores() {
    const sel = document.getElementById('proveedorSelect');
    if (!sel) {
      return Promise.resolve([]);
    }
    if (!window.Api) {
      sel.innerHTML = '<option value="">API no disponible</option>';
      return Promise.resolve([]);
    }
    return window.Api
      .get('/proveedores_list.php')
      .then(function (json) {
        const data = json.data || [];
        sel.innerHTML = '<option value="">-- Seleccionar proveedor --</option>';
        data.forEach(function (p) {
          const opt = document.createElement('option');
          opt.value = String(p.id);
          opt.textContent = p.nombre + (p.cedulaJuridica ? ' (' + p.cedulaJuridica + ')' : '');
          sel.appendChild(opt);
        });
        return data;
      })
      .catch(function () {
        sel.innerHTML = '<option value="">Error al cargar proveedores</option>';
        return [];
      });
  }

  function searchProducts(term) {
    if (!term || term.length < 2) {
      return [];
    }
    const t = term.toLowerCase();
    return productos.filter(function (p) {
      return p.nombre.toLowerCase().includes(t);
    });
  }

  function addToCart(producto, cantidad) {
    const qty = Math.max(1, parseInt(cantidad, 10) || 1);
    const maxQty = 99999;
    const pu = defaultPrecioCompra(producto);
    const found = carrito.find(function (i) {
      return i.productoId === producto.id;
    });
    if (found) {
      found.cantidad = Math.min(found.cantidad + qty, maxQty);
      found.subtotal = found.cantidad * found.precioUnitario;
    } else {
      const finalQty = Math.min(qty, maxQty);
      carrito.push({
        productoId: producto.id,
        nombre: producto.nombre,
        cantidad: finalQty,
        precioUnitario: pu,
        subtotal: finalQty * pu,
      });
    }
    renderCart();
  }

  function removeFromCart(productoId) {
    carrito = carrito.filter(function (i) {
      return i.productoId !== productoId;
    });
    renderCart();
  }

  function updateCartQty(productoId, delta) {
    const item = carrito.find(function (i) {
      return i.productoId === productoId;
    });
    if (!item) {
      return;
    }
    item.cantidad = Math.max(0, item.cantidad + delta);
    if (item.cantidad <= 0) {
      removeFromCart(productoId);
      return;
    }
    item.subtotal = item.cantidad * item.precioUnitario;
    renderCart();
  }

  function setLinePrice(productoId, value) {
    const item = carrito.find(function (i) {
      return i.productoId === productoId;
    });
    if (!item) {
      return;
    }
    const n = parseFloat(String(value).replace(',', '.'));
    item.precioUnitario = isNaN(n) || n < 0 ? 0 : n;
    item.subtotal = item.cantidad * item.precioUnitario;
    renderCart();
  }

  function getTotal() {
    return carrito.reduce(function (sum, i) {
      return sum + i.subtotal;
    }, 0);
  }

  function escapeHtml(s) {
    const div = document.createElement('div');
    div.textContent = s;
    return div.innerHTML;
  }

  function renderCart() {
    const tbody = document.getElementById('cartBody');
    const empty = document.getElementById('cartEmpty');
    const totalEl = document.getElementById('cartTotal');
    const countEl = document.getElementById('cartCount');
    const btn = document.getElementById('btnConfirmarCompra');

    if (!tbody || !empty || !totalEl || !countEl || !btn) {
      return;
    }

    tbody.innerHTML = '';
    countEl.textContent = String(carrito.length);

    if (carrito.length === 0) {
      empty.style.display = 'block';
      totalEl.textContent = formatMoney(0);
      btn.disabled = true;
      return;
    }
    empty.style.display = 'none';

    carrito.forEach(function (item) {
      const tr = document.createElement('tr');
      tr.innerHTML =
        '<td>' +
        escapeHtml(item.nombre) +
        '</td>' +
        '<td class="text-end">' +
        '<div class="btn-group btn-group-sm">' +
        '<button type="button" class="btn btn-outline-secondary" data-action="minus" data-id="' +
        item.productoId +
        '">−</button>' +
        '<span class="px-2 align-middle">' +
        item.cantidad +
        '</span>' +
        '<button type="button" class="btn btn-outline-secondary" data-action="plus" data-id="' +
        item.productoId +
        '">+</button>' +
        '</div>' +
        '</td>' +
        '<td class="text-end" style="max-width: 140px;">' +
        '<input type="number" class="form-control form-control-sm text-end price-line" min="0" step="0.01" ' +
        'data-id="' +
        item.productoId +
        '" value="' +
        item.precioUnitario +
        '">' +
        '</td>' +
        '<td class="text-end">' +
        formatMoney(item.subtotal) +
        '</td>' +
        '<td>' +
        '<button type="button" class="btn btn-outline-danger btn-sm" data-action="remove" data-id="' +
        item.productoId +
        '" title="Quitar"><i class="bi bi-trash"></i></button>' +
        '</td>';
      tbody.appendChild(tr);
    });

    totalEl.textContent = formatMoney(getTotal());
    btn.disabled = carrito.length === 0;

    tbody.querySelectorAll('[data-action]').forEach(function (el) {
      el.addEventListener('click', function () {
        const action = el.getAttribute('data-action');
        const id = parseInt(el.getAttribute('data-id'), 10);
        if (action === 'plus') {
          updateCartQty(id, 1);
        } else if (action === 'minus') {
          updateCartQty(id, -1);
        } else if (action === 'remove') {
          removeFromCart(id);
        }
      });
    });

    tbody.querySelectorAll('input.price-line').forEach(function (inp) {
      inp.addEventListener('change', function () {
        const id = parseInt(inp.getAttribute('data-id'), 10);
        setLinePrice(id, inp.value);
      });
    });
  }

  function confirmarCompra() {
    const provStr = document.getElementById('proveedorSelect').value;
    const fechaEl = document.getElementById('fechaCompra');
    const fechaCompra = fechaEl ? fechaEl.value : '';
    const empleadoEl = document.getElementById('empleadoId');
    const empleadoId = empleadoEl ? parseInt(empleadoEl.value, 10) || 1 : 1;

    if (!provStr) {
      void uiAlert('Seleccione un proveedor.');
      return;
    }
    if (!fechaCompra) {
      void uiAlert('Indique la fecha de compra.');
      return;
    }
    if (carrito.length === 0) {
      void uiAlert('Agregue al menos una línea.');
      return;
    }
    if (!window.Api) {
      void uiAlert('API no disponible.');
      return;
    }

    var Vco = window.HamiltonValidation;
    if (Vco && Vco.fechaYyyyMmDd && !Vco.fechaYyyyMmDd(fechaCompra)) {
      void uiAlert('Indique una fecha de compra válida (AAAA-MM-DD).');
      return;
    }
    if (Vco && typeof Vco.carritoComprasLineasMensaje === 'function') {
      var errCo = Vco.carritoComprasLineasMensaje(carrito);
      if (errCo) {
        void uiAlert(errCo);
        return;
      }
    }

    const proveedorId = parseInt(provStr, 10);
    const lineas = carrito.map(function (i) {
      return {
        productoId: i.productoId,
        cantidad: i.cantidad,
        precioUnitario: i.precioUnitario,
      };
    });

    const btn = document.getElementById('btnConfirmarCompra');
    if (btn) {
      btn.disabled = true;
    }

    window.Api
      .post('/compras_create.php', {
        fechaCompra: fechaCompra,
        proveedorId: proveedorId,
        lineas: lineas,
      })
      .then(function (res) {
        carrito = [];
        renderCart();
        const search = document.getElementById('productSearch');
        const results = document.getElementById('productResults');
        if (search) {
          search.value = '';
        }
        if (results) {
          results.innerHTML = '';
        }
        return uiAlert(
          'Compra registrada. ID: ' + (res.idCompra || '') + ' — ' + (res.message || 'OK'),
          'Listo'
        );
      })
      .catch(function (e) {
        void uiAlert('No se pudo registrar la compra: ' + (e.message || String(e)), 'Error');
      })
      .finally(function () {
        if (btn) {
          btn.disabled = carrito.length === 0;
        }
      });
  }

  function renderProductResults(results) {
    const container = document.getElementById('productResults');
    if (!container) {
      return;
    }
    container.innerHTML = '';
    if (results.length === 0) {
      container.innerHTML = '<div class="list-group-item text-muted">Sin resultados</div>';
      return;
    }
    results.forEach(function (p) {
      const a = document.createElement('a');
      a.href = '#';
      a.className =
        'list-group-item list-group-item-action d-flex justify-content-between align-items-center';
      const costo = defaultPrecioCompra(p);
      a.innerHTML =
        '<span>' +
        escapeHtml(p.nombre) +
        ' <small class="text-muted">' +
        formatMoney(costo) +
        ' costo</small></span>' +
        '<div class="input-group input-group-sm" style="width: 120px;">' +
        '<input type="number" class="form-control qty-input" value="1" min="1" max="99999" data-id="' +
        p.id +
        '">' +
        '<button type="button" class="btn btn-primary add-cart-btn" data-id="' +
        p.id +
        '">Agregar</button>' +
        '</div>';
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
    const fechaEl = document.getElementById('fechaCompra');
    if (fechaEl) {
      fechaEl.value = new Date().toISOString().slice(0, 10);
    }

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

    loadProveedores();

    const searchInput = document.getElementById('productSearch');
    if (searchInput) {
      searchInput.addEventListener('input', function () {
        const term = this.value.trim();
        renderProductResults(searchProducts(term));
      });
      searchInput.addEventListener('focus', function () {
        const term = this.value.trim();
        if (term.length >= 2) {
          renderProductResults(searchProducts(term));
        }
      });
    }

    const btn = document.getElementById('btnConfirmarCompra');
    if (btn) {
      btn.addEventListener('click', confirmarCompra);
    }

    renderCart();
  });
})();
