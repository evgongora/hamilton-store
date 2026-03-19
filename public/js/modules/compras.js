/**
 * compras.js - Registro de compras unitarias con historial y stock temporal
 */
(function () {
  'use strict';

  var PRODUCTS_STORAGE_KEY = 'hamilton-store-productos';
  var PROVIDERS_STORAGE_KEY = 'hamilton-store-proveedores';
  var PURCHASES_STORAGE_KEY = 'hamilton-store-compras';

  var state = {
    products: [],
    providers: [],
    purchases: [],
    editingPurchaseId: null
  };

  document.addEventListener('DOMContentLoaded', function () {
    var productoSelect = document.getElementById('productoSelect');
    var proveedorSelect = document.getElementById('proveedorSelect');
    var cantidadInput = document.getElementById('cantidadInput');
    var precioUnitarioInput = document.getElementById('precioUnitario');
    var summaryTotal = document.getElementById('summaryTotal');
    var registerButton = document.getElementById('btnRegistrarCompra');
    var historyBody = document.getElementById('historyBody');
    var historyEmpty = document.getElementById('historyEmpty');
    var historyCount = document.getElementById('historyCount');

    if (!productoSelect || !proveedorSelect || !cantidadInput || !registerButton || !historyBody) {
      return;
    }

    var basePath = (document.body.dataset.basePath || '').replace(/\/$/, '');
    var currentUser = String(document.body.dataset.currentUser || '').trim() || 'Usuario actual';
    var currentRole = String(document.body.dataset.currentRole || '').trim().toLowerCase();
    var isAdmin = currentRole === 'admin';

    function readStorage(key) {
      try {
        var raw = localStorage.getItem(key);
        if (!raw) {
          return null;
        }

        var parsed = JSON.parse(raw);
        return Array.isArray(parsed) ? parsed : null;
      } catch (error) {
        return null;
      }
    }

    function writeStorage(key, value) {
      localStorage.setItem(key, JSON.stringify(value));
    }

    function fetchJson(path) {
      return fetch(basePath + path, { cache: 'no-store' }).then(function (response) {
        if (!response.ok) {
          throw new Error('No se pudo cargar ' + path);
        }

        return response.json();
      });
    }

    function escapeHtml(value) {
      return String(value == null ? '' : value)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');
    }

    function formatMoney(value) {
      return new Intl.NumberFormat('es-CR', {
        style: 'currency',
        currency: 'CRC',
        minimumFractionDigits: 2
      }).format(Number(value) || 0);
    }

    function formatDate(value) {
      var date = value ? new Date(value) : null;

      if (!date || isNaN(date.getTime())) {
        return 'Sin fecha';
      }

      return new Intl.DateTimeFormat('es-CR', {
        dateStyle: 'short',
        timeStyle: 'short'
      }).format(date);
    }

    function normalizeProduct(product) {
      return {
        id: Number(product.id) || 0,
        nombre: String(product.nombre || '').trim(),
        precio_compra: Number(product.precio_compra != null ? product.precio_compra : product.precioCompra || 0),
        precio_venta: Number(product.precio_venta != null ? product.precio_venta : product.precioVenta || 0),
        cantidad: Number(product.cantidad || 0)
      };
    }

    function normalizeProvider(provider, index) {
      return {
        id: provider.id || provider.id_proveedor || provider.proveedorId || ('proveedor-' + index),
        nombre: String(provider.nombre || provider.proveedor || '').trim()
      };
    }

    function normalizePurchase(purchase) {
      return {
        id: purchase.id || Date.now(),
        fecha: purchase.fecha || new Date().toISOString(),
        usuario: String(purchase.usuario || currentUser).trim(),
        proveedorId: purchase.proveedorId || '',
        proveedorNombre: String(purchase.proveedorNombre || '').trim(),
        productoId: Number(purchase.productoId || purchase.productosIdProducto || 0),
        productoNombre: String(purchase.productoNombre || purchase.nombre || '').trim(),
        cantidad: Number(purchase.cantidad || 0),
        precioUnitario: Number(purchase.precioUnitario || 0),
        total: Number(purchase.total != null ? purchase.total : (Number(purchase.cantidad || 0) * Number(purchase.precioUnitario || 0)))
      };
    }

    function getSelectedProduct() {
      return state.products.find(function (product) {
        return String(product.id) === String(productoSelect.value);
      }) || null;
    }

    function getSelectedProvider() {
      return state.providers.find(function (item) {
        return String(item.id) === String(proveedorSelect.value);
      }) || null;
    }

    function resetForm() {
      state.editingPurchaseId = null;
      productoSelect.value = '';
      proveedorSelect.value = '';
      cantidadInput.value = '1';
      registerButton.innerHTML = '<i class="bi bi-check-circle me-2"></i>Registrar Compra';
      updateSummary();
    }

    function updateSummary() {
      var product = getSelectedProduct();
      var quantity = Math.max(1, parseInt(cantidadInput.value, 10) || 1);
      var unitPrice = product ? product.precio_compra : 0;
      var total = quantity * unitPrice;

      if (String(quantity) !== cantidadInput.value) {
        cantidadInput.value = String(quantity);
      }

      precioUnitarioInput.value = formatMoney(unitPrice);
      summaryTotal.textContent = formatMoney(total);
      registerButton.disabled = !(product && proveedorSelect.value && quantity > 0);
    }

    function renderProductOptions() {
      productoSelect.innerHTML = '<option value="">-- Seleccionar producto --</option>';

      state.products.forEach(function (product) {
        var option = document.createElement('option');
        option.value = String(product.id);
        option.textContent = product.nombre;
        productoSelect.appendChild(option);
      });
    }

    function renderProviderOptions() {
      proveedorSelect.innerHTML = '<option value="">-- Seleccionar proveedor --</option>';

      state.providers.forEach(function (provider) {
        var option = document.createElement('option');
        option.value = String(provider.id);
        option.textContent = provider.nombre || 'Proveedor';
        proveedorSelect.appendChild(option);
      });
    }

    function renderHistory() {
      historyBody.innerHTML = '';
      historyCount.textContent = String(state.purchases.length);

      if (!state.purchases.length) {
        historyEmpty.style.display = 'block';
        return;
      }

      historyEmpty.style.display = 'none';

      state.purchases
        .slice()
        .sort(function (a, b) {
          return new Date(b.fecha).getTime() - new Date(a.fecha).getTime();
        })
        .forEach(function (purchase) {
          var row = document.createElement('tr');
          row.innerHTML = [
            '<td>' + escapeHtml(formatDate(purchase.fecha)) + '</td>',
            '<td>' + escapeHtml(purchase.usuario || 'Sin usuario') + '</td>',
            '<td>' + escapeHtml(purchase.proveedorNombre || 'Sin proveedor') + '</td>',
            '<td>' + escapeHtml(purchase.productoNombre || 'Sin producto') + '</td>',
            '<td class="text-end">' + escapeHtml(purchase.cantidad) + '</td>',
            '<td class="text-end">' + formatMoney(purchase.precioUnitario) + '</td>',
            '<td class="text-end">' + formatMoney(purchase.total) + '</td>',
            isAdmin
              ? '<td class="text-end"><div class="btn-group btn-group-sm"><button type="button" class="btn btn-outline-secondary purchase-edit" data-id="' + escapeHtml(purchase.id) + '"><i class="bi bi-pencil"></i></button><button type="button" class="btn btn-outline-danger purchase-delete" data-id="' + escapeHtml(purchase.id) + '"><i class="bi bi-trash"></i></button></div></td>'
              : ''
          ].join('');
          historyBody.appendChild(row);
        });
    }

    function updateProductStock(productId, quantity) {
      state.products = state.products.map(function (product) {
        if (product.id === Number(productId)) {
          product.cantidad += Number(quantity) || 0;
        }

        return product;
      });

      writeStorage(PRODUCTS_STORAGE_KEY, state.products);
    }

    function persistPurchases() {
      writeStorage(PURCHASES_STORAGE_KEY, state.purchases);
    }

    function startEditPurchase(purchaseId) {
      var purchase = state.purchases.find(function (item) {
        return String(item.id) === String(purchaseId);
      });

      if (!purchase || !isAdmin) {
        return;
      }

      state.editingPurchaseId = purchase.id;
      productoSelect.value = String(purchase.productoId || '');
      proveedorSelect.value = String(purchase.proveedorId || '');
      cantidadInput.value = String(Math.max(1, Number(purchase.cantidad) || 1));
      registerButton.innerHTML = '<i class="bi bi-check-circle me-2"></i>Actualizar Compra';
      updateSummary();
    }

    function deletePurchase(purchaseId) {
      var purchase = state.purchases.find(function (item) {
        return String(item.id) === String(purchaseId);
      });

      if (!purchase || !isAdmin) {
        return;
      }

      updateProductStock(purchase.productoId, -purchase.cantidad);
      state.purchases = state.purchases.filter(function (item) {
        return String(item.id) !== String(purchaseId);
      });
      persistPurchases();

      if (String(state.editingPurchaseId) === String(purchaseId)) {
        resetForm();
      }

      renderHistory();
    }

    function registerPurchase() {
      var product = getSelectedProduct();
      var provider = getSelectedProvider();
      var quantity = Math.max(1, parseInt(cantidadInput.value, 10) || 1);
      var existingPurchase;
      var purchase;

      if (!product || !provider) {
        updateSummary();
        return;
      }

      if (state.editingPurchaseId != null && isAdmin) {
        existingPurchase = state.purchases.find(function (item) {
          return String(item.id) === String(state.editingPurchaseId);
        });

        if (existingPurchase) {
          updateProductStock(existingPurchase.productoId, -existingPurchase.cantidad);
          existingPurchase.fecha = new Date().toISOString();
          existingPurchase.usuario = currentUser;
          existingPurchase.proveedorId = provider.id;
          existingPurchase.proveedorNombre = provider.nombre;
          existingPurchase.productoId = product.id;
          existingPurchase.productoNombre = product.nombre;
          existingPurchase.cantidad = quantity;
          existingPurchase.precioUnitario = product.precio_compra;
          existingPurchase.total = quantity * product.precio_compra;

          updateProductStock(product.id, quantity);
          persistPurchases();
          renderHistory();
          resetForm();

          alert('Compra actualizada correctamente. Total: ' + formatMoney(existingPurchase.total));
          return;
        }
      }

      purchase = {
        id: Date.now(),
        fecha: new Date().toISOString(),
        usuario: currentUser,
        proveedorId: provider.id,
        proveedorNombre: provider.nombre,
        productoId: product.id,
        productoNombre: product.nombre,
        cantidad: quantity,
        precioUnitario: product.precio_compra,
        total: quantity * product.precio_compra
      };

      state.purchases.push(purchase);
      persistPurchases();
      updateProductStock(product.id, quantity);
      renderHistory();
      resetForm();

      alert('Compra registrada correctamente. Total: ' + formatMoney(purchase.total));
    }

    function loadProducts() {
      var stored = readStorage(PRODUCTS_STORAGE_KEY);

      if (stored) {
        state.products = stored.map(normalizeProduct);
        return Promise.resolve();
      }

      return fetchJson('/js/mocks/productos.json').then(function (products) {
        state.products = (Array.isArray(products) ? products : []).map(normalizeProduct);
      });
    }

    function loadProviders() {
      var stored = readStorage(PROVIDERS_STORAGE_KEY);

      if (stored) {
        state.providers = stored.map(normalizeProvider);
        return Promise.resolve();
      }

      return fetchJson('/js/mocks/proveedores.json').then(function (providers) {
        state.providers = (Array.isArray(providers) ? providers : []).map(normalizeProvider);
      });
    }

    function loadPurchases() {
      var stored = readStorage(PURCHASES_STORAGE_KEY);

      if (stored) {
        state.purchases = stored.map(normalizePurchase);
        return Promise.resolve();
      }

      return fetchJson('/js/mocks/compras.json').then(function (purchases) {
        state.purchases = (Array.isArray(purchases) ? purchases : []).map(normalizePurchase);
      }).catch(function () {
        state.purchases = [];
      });
    }

    productoSelect.addEventListener('change', updateSummary);
    proveedorSelect.addEventListener('change', updateSummary);
    cantidadInput.addEventListener('input', updateSummary);
    registerButton.addEventListener('click', registerPurchase);
    historyBody.addEventListener('click', function (event) {
      var editButton = event.target.closest('.purchase-edit');
      var deleteButton = event.target.closest('.purchase-delete');

      if (editButton) {
        startEditPurchase(editButton.getAttribute('data-id'));
        return;
      }

      if (deleteButton) {
        deletePurchase(deleteButton.getAttribute('data-id'));
      }
    });

    Promise.all([
      loadProducts().catch(function () {
        state.products = [];
      }),
      loadProviders().catch(function () {
        state.providers = [];
      }),
      loadPurchases()
    ]).then(function () {
      renderProductOptions();
      renderProviderOptions();
      renderHistory();
      updateSummary();
    });
  });
})();
