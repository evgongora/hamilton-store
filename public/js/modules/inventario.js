/**
 * inventario.js - listado de inventario de solo lectura
 */
(function () {
  'use strict';

  var PRODUCTS_STORAGE_KEY = 'hamilton-store-productos';
  var CATEGORIES_STORAGE_KEY = 'hamilton-store-categorias';

  document.addEventListener('DOMContentLoaded', function () {
    var app = document.getElementById('inventory-app');
    if (!app) {
      return;
    }

    var basePath = (document.body.dataset.basePath || '').replace(/\/$/, '');
    var productsMockUrl = basePath + '/js/mocks/productos.json';
    var categoriesMockUrl = basePath + '/js/mocks/categorias.json';

    var body = document.getElementById('inventory-body');
    var empty = document.getElementById('inventory-empty');
    var count = document.getElementById('inventory-count');
    var statTotal = document.getElementById('inventory-stat-total');
    var statStock = document.getElementById('inventory-stat-stock');

    var state = {
      products: [],
      categories: []
    };

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

    function normalizeProduct(product) {
      var rawEstado = product.estadosIdEstado != null ? product.estadosIdEstado : product.estado;
      var estado = 'activo';

      if (typeof rawEstado === 'number') {
        estado = rawEstado === 1 ? 'activo' : 'inactivo';
      } else if (String(rawEstado || '').trim()) {
        estado = String(rawEstado).trim().toLowerCase();
      }

      return {
        id: Number(product.id) || 0,
        nombre: String(product.nombre || '').trim(),
        precio_compra: Number(product.precio_compra != null ? product.precio_compra : product.precioCompra || 0),
        precio_venta: Number(product.precio_venta != null ? product.precio_venta : product.precioVenta || 0),
        cantidad: Number(product.cantidad || 0),
        estado: estado,
        categorias_id_categoria: Number(product.categorias_id_categoria != null ? product.categorias_id_categoria : product.categoriasIdCategoria || 0)
      };
    }

    function normalizeCategory(category) {
      return {
        id: Number(category.id) || 0,
        nombre: String(category.nombre || '').trim()
      };
    }

    function formatCurrency(value) {
      return new Intl.NumberFormat('es-CR', {
        style: 'currency',
        currency: 'CRC',
        minimumFractionDigits: 2
      }).format(Number(value) || 0);
    }

    function escapeHtml(value) {
      return String(value == null ? '' : value)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');
    }

    function getCategoryName(categoryId) {
      var category = state.categories.find(function (item) {
        return item.id === Number(categoryId);
      });
      return category ? category.nombre : 'Sin categoria';
    }

    function getStockLevel(quantity) {
      var value = Number(quantity) || 0;

      if (value < 10) {
        return 'bajo';
      }
      if (value < 50) {
        return 'medio';
      }
      if (value >= 100) {
        return 'alto';
      }

      return 'medio';
    }

    function renderStats() {
      statTotal.textContent = String(state.products.filter(function (product) {
        return Number(product.cantidad || 0) < 10;
      }).length);
      statStock.textContent = String(state.products.reduce(function (sum, product) {
        return sum + Number(product.cantidad || 0);
      }, 0));
    }

    function renderTable() {
      body.innerHTML = '';
      count.textContent = state.products.length + ' producto(s)';

      if (!state.products.length) {
        empty.style.display = 'block';
        return;
      }

      empty.style.display = 'none';

      state.products.forEach(function (item) {
        var row = document.createElement('tr');
        row.innerHTML = [
          '<td><div class="fw-semibold">' + escapeHtml(item.nombre) + '</div></td>',
          '<td>' + formatCurrency(item.precio_compra) + '</td>',
          '<td>' + formatCurrency(item.precio_venta) + '</td>',
          '<td>' + escapeHtml(item.cantidad) + '</td>',
          '<td><span class="products-status stock-' + escapeHtml(getStockLevel(item.cantidad)) + '">' + escapeHtml(getStockLevel(item.cantidad)) + '</span></td>',
          '<td><span class="products-status status-' + escapeHtml(item.estado) + '">' + escapeHtml(item.estado) + '</span></td>',
          '<td>' + escapeHtml(getCategoryName(item.categorias_id_categoria)) + '</td>'
        ].join('');
        body.appendChild(row);
      });
    }

    function renderLoadingState() {
      body.innerHTML = '<tr><td colspan="7" class="text-center text-muted py-4">Cargando inventario...</td></tr>';
      empty.style.display = 'none';
      count.textContent = 'Cargando...';
      statTotal.textContent = '...';
      statStock.textContent = '...';
    }

    function renderErrorState() {
      body.innerHTML = '<tr><td colspan="7" class="text-center text-danger py-4">No se pudo cargar el inventario.</td></tr>';
      empty.style.display = 'none';
      count.textContent = '0 productos';
      statTotal.textContent = '0';
      statStock.textContent = '0';
    }

    function loadInventory() {
      renderLoadingState();

      Promise.all([
        fetch(productsMockUrl, { cache: 'no-store' }).then(function (response) {
          if (!response.ok) {
            throw new Error('products mock');
          }
          return response.json();
        }),
        fetch(categoriesMockUrl, { cache: 'no-store' }).then(function (response) {
          if (!response.ok) {
            throw new Error('categories mock');
          }
          return response.json();
        })
      ])
        .then(function (results) {
          state.products = results[0].map(normalizeProduct);
          state.categories = results[1].map(normalizeCategory);

          renderStats();
          renderTable();
        })
        .catch(function () {
          var fallbackProducts = readStorage(PRODUCTS_STORAGE_KEY);
          var fallbackCategories = readStorage(CATEGORIES_STORAGE_KEY);

          if (fallbackProducts && fallbackProducts.length && fallbackCategories && fallbackCategories.length) {
            state.products = fallbackProducts.map(normalizeProduct);
            state.categories = fallbackCategories.map(normalizeCategory);
            renderStats();
            renderTable();
            return;
          }

          renderErrorState();
        });
    }

    loadInventory();
  });
})();
