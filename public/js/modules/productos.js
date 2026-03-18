/**
 * productos.js - UI frontend del modulo Productos y Categorias
 */
(function () {
  'use strict';

  var PRODUCTS_STORAGE_KEY = 'hamilton-store-productos';
  var CATEGORIES_STORAGE_KEY = 'hamilton-store-categorias';

  document.addEventListener('DOMContentLoaded', function () {
    var app = document.getElementById('catalog-app');
    if (!app) {
      return;
    }

    var basePath = app.getAttribute('data-base-path') || '';
    var productsMockUrl = basePath + '/js/mocks/productos.json';
    var categoriesMockUrl = basePath + '/js/mocks/categorias.json';

    var productForm = document.getElementById('product-form');
    var categoryForm = document.getElementById('category-form');
    var tableHead = document.getElementById('catalog-table-head');
    var tableBody = document.getElementById('catalog-table-body');
    var emptyState = document.getElementById('catalog-empty-state');
    var searchInput = document.getElementById('catalog-search-input');
    var newTrigger = document.getElementById('catalog-new-trigger');
    var newLabel = document.getElementById('catalog-new-label');
    var switchButtons = Array.prototype.slice.call(document.querySelectorAll('.catalog-switch'));
    var pageTitle = document.getElementById('catalog-page-title');
    var listTitle = document.getElementById('catalog-list-title');
    var listDescription = document.getElementById('catalog-list-description');
    var formTitle = document.getElementById('catalog-form-title');
    var formDescription = document.getElementById('catalog-form-description');
    var formBadge = document.getElementById('catalog-form-badge');
    var statLabel1 = document.getElementById('stat-label-1');
    var statLabel2 = document.getElementById('stat-label-2');
    var statLabel3 = document.getElementById('stat-label-3');
    var statTotal = document.getElementById('stat-total');
    var statStock = document.getElementById('stat-stock');
    var statCategories = document.getElementById('stat-categories');
    var productCancelButton = document.getElementById('product-cancel-edit');
    var productSubmitButton = document.getElementById('product-submit-button');
    var categoryCancelButton = document.getElementById('category-cancel-edit');
    var categorySubmitButton = document.getElementById('category-submit-button');
    var deleteConfirmMessage = document.getElementById('delete-confirm-message');
    var deleteConfirmButton = document.getElementById('delete-confirm-button');
    var deleteModal = new bootstrap.Modal(document.getElementById('deleteConfirmModal'));
    var formModal = new bootstrap.Modal(document.getElementById('catalogFormModal'));

    var productFields = {
      id: document.getElementById('product-id'),
      nombre: document.getElementById('product-nombre'),
      precio_compra: document.getElementById('product-precio-compra'),
      precio_venta: document.getElementById('product-precio-venta'),
      cantidad: document.getElementById('product-cantidad'),
      estado: document.getElementById('product-estado'),
      categorias_id_categoria: document.getElementById('product-categoria')
    };

    var categoryFields = {
      id: document.getElementById('category-id'),
      nombre: document.getElementById('category-nombre')
    };

    var state = {
      view: 'products',
      query: '',
      products: [],
      categories: [],
      editingProductId: null,
      editingCategoryId: null,
      pendingDelete: null
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

    function writeStorage(key, value) {
      localStorage.setItem(key, JSON.stringify(value));
    }

    function normalizeProduct(product) {
      return {
        id: Number(product.id) || 0,
        nombre: String(product.nombre || '').trim(),
        precio_compra: Number(product.precio_compra != null ? product.precio_compra : product.precioCompra || 0),
        precio_venta: Number(product.precio_venta != null ? product.precio_venta : product.precioVenta || 0),
        cantidad: Number(product.cantidad || 0),
        estado: String(product.estado || 'activo').trim().toLowerCase(),
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

    function normalizeText(value) {
      return String(value || '').trim().toLowerCase();
    }

    function escapeHtml(value) {
      return String(value)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');
    }

    function getCurrentCollection() {
      return state.view === 'products' ? state.products : state.categories;
    }

    function getCurrentFilteredItems() {
      var items = getCurrentCollection();
      if (!state.query) {
        return items.slice();
      }

      return items.filter(function (item) {
        if (state.view === 'products') {
          return normalizeText(item.nombre).indexOf(state.query) !== -1 ||
            normalizeText(item.estado).indexOf(state.query) !== -1;
        }

        return normalizeText(item.nombre).indexOf(state.query) !== -1;
      });
    }

    function getNextId(items) {
      return items.reduce(function (maxId, item) {
        return Math.max(maxId, Number(item.id) || 0);
      }, 0) + 1;
    }

    function resetProductForm() {
      state.editingProductId = null;
      productForm.reset();
      productFields.id.value = '';
      productFields.estado.value = 'activo';
    }

    function resetCategoryForm() {
      state.editingCategoryId = null;
      categoryForm.reset();
      categoryFields.id.value = '';
    }

    function applyFormCopy() {
      if (state.view === 'products') {
        formTitle.textContent = state.editingProductId ? 'Editar producto' : 'Agregar producto';
        formDescription.textContent = state.editingProductId
          ? 'Actualiza la informacion del producto seleccionado.'
          : 'Completa los datos requeridos para registrar un producto.';
        formBadge.textContent = state.editingProductId ? 'Edicion' : 'Nuevo';
        productSubmitButton.innerHTML = state.editingProductId
          ? '<i class="bi bi-check2-circle me-2"></i>Actualizar producto'
          : '<i class="bi bi-save me-2"></i>Guardar producto';
        return;
      }

      formTitle.textContent = state.editingCategoryId ? 'Editar categoria' : 'Agregar categoria';
      formDescription.textContent = state.editingCategoryId
        ? 'Actualiza el nombre de la categoria seleccionada.'
        : 'Registra una nueva categoria para el catalogo.';
      formBadge.textContent = state.editingCategoryId ? 'Edicion' : 'Nuevo';
      categorySubmitButton.innerHTML = state.editingCategoryId
        ? '<i class="bi bi-check2-circle me-2"></i>Actualizar categoria'
        : '<i class="bi bi-save me-2"></i>Guardar categoria';
    }

    function updateViewCopy() {
      var isProducts = state.view === 'products';
      pageTitle.textContent = 'Productos y Categorias';
      listTitle.textContent = isProducts ? 'Listado de productos' : 'Listado de categorias';
      listDescription.textContent = isProducts
        ? 'Columnas solicitadas: nombre, precios, cantidad, estado y categoria.'
        : 'Vista simple de categorias con nombre y acciones de mantenimiento.';
      newLabel.textContent = isProducts ? 'Nuevo producto' : 'Nueva categoria';
      searchInput.placeholder = isProducts ? 'Buscar por nombre o estado' : 'Buscar categoria por nombre';

      productForm.hidden = !isProducts;
      categoryForm.hidden = isProducts;
      resetProductForm();
      resetCategoryForm();
      applyFormCopy();
    }

    function renderStats() {
      if (state.view === 'products') {
        statLabel1.textContent = 'Productos registrados';
        statLabel2.textContent = 'Unidades en inventario';
        statLabel3.textContent = 'Categorias activas';
        statTotal.textContent = String(state.products.length);
        statStock.textContent = String(state.products.reduce(function (sum, product) {
          return sum + Number(product.cantidad || 0);
        }, 0));
        statCategories.textContent = String(new Set(state.products.map(function (product) {
          return String(product.categorias_id_categoria);
        })).size);
        return;
      }

      statLabel1.textContent = 'Categorias registradas';
      statLabel2.textContent = 'Categorias con productos';
      statLabel3.textContent = 'Productos vinculados';
      statTotal.textContent = String(state.categories.length);
      statStock.textContent = String(new Set(state.products.map(function (product) {
        return String(product.categorias_id_categoria);
      })).size);
      statCategories.textContent = String(state.products.length);
    }

    function renderTableHead() {
      if (state.view === 'products') {
        tableHead.innerHTML = '<tr><th scope="col">Nombre</th><th scope="col">Precio compra</th><th scope="col">Precio venta</th><th scope="col">Cantidad</th><th scope="col">Estado</th><th scope="col">Categoria ID</th><th scope="col" class="text-end">Acciones</th></tr>';
        return;
      }

      tableHead.innerHTML = '<tr><th scope="col">ID</th><th scope="col">Nombre</th><th scope="col" class="text-end">Acciones</th></tr>';
    }

    function renderTable() {
      var items = getCurrentFilteredItems();
      tableBody.innerHTML = '';

      if (!items.length) {
        emptyState.hidden = false;
        emptyState.textContent = state.view === 'products' ? 'No hay productos para mostrar.' : 'No hay categorias para mostrar.';
        return;
      }

      emptyState.hidden = true;

      items.forEach(function (item) {
        var row = document.createElement('tr');
        if (state.view === 'products') {
          row.innerHTML = [
            '<td><div class="fw-semibold">' + escapeHtml(item.nombre) + '</div></td>',
            '<td>' + formatCurrency(item.precio_compra) + '</td>',
            '<td>' + formatCurrency(item.precio_venta) + '</td>',
            '<td><span class="badge rounded-pill text-bg-light border">' + escapeHtml(item.cantidad) + '</span></td>',
            '<td><span class="products-status status-' + escapeHtml(item.estado) + '">' + escapeHtml(item.estado) + '</span></td>',
            '<td>' + escapeHtml(item.categorias_id_categoria) + '</td>',
            '<td class="text-end"><div class="btn-group btn-group-sm" role="group"><button class="btn btn-outline-primary" type="button" data-action="edit-product" data-id="' + escapeHtml(item.id) + '">Editar</button><button class="btn btn-outline-danger" type="button" data-action="delete-product" data-id="' + escapeHtml(item.id) + '" data-name="' + escapeHtml(item.nombre) + '">Eliminar</button></div></td>'
          ].join('');
        } else {
          row.innerHTML = [
            '<td>' + escapeHtml(item.id) + '</td>',
            '<td><div class="fw-semibold">' + escapeHtml(item.nombre) + '</div></td>',
            '<td class="text-end"><div class="btn-group btn-group-sm" role="group"><button class="btn btn-outline-primary" type="button" data-action="edit-category" data-id="' + escapeHtml(item.id) + '">Editar</button><button class="btn btn-outline-danger" type="button" data-action="delete-category" data-id="' + escapeHtml(item.id) + '" data-name="' + escapeHtml(item.nombre) + '">Eliminar</button></div></td>'
          ].join('');
        }
        tableBody.appendChild(row);
      });
    }

    function renderAll() {
      renderStats();
      renderTableHead();
      renderTable();
      applyFormCopy();
    }

    function renderLoadingState() {
      tableHead.innerHTML = '';
      tableBody.innerHTML = '<tr><td colspan="7" class="text-center text-muted py-4">Cargando catalogos...</td></tr>';
      emptyState.hidden = true;
      statTotal.textContent = '...';
      statStock.textContent = '...';
      statCategories.textContent = '...';
    }

    function renderErrorState() {
      tableHead.innerHTML = '';
      tableBody.innerHTML = '<tr><td colspan="7" class="text-center text-danger py-4">No se pudieron cargar los mocks.</td></tr>';
      emptyState.hidden = true;
      statTotal.textContent = '0';
      statStock.textContent = '0';
      statCategories.textContent = '0';
    }

    function openFormModal() {
      applyFormCopy();
      formModal.show();
    }

    function closeFormModal() {
      formModal.hide();
    }

    function fillProductForm(product) {
      if (!product) {
        return;
      }
      state.editingProductId = product.id;
      productFields.id.value = product.id;
      productFields.nombre.value = product.nombre;
      productFields.precio_compra.value = product.precio_compra;
      productFields.precio_venta.value = product.precio_venta;
      productFields.cantidad.value = product.cantidad;
      productFields.estado.value = product.estado;
      productFields.categorias_id_categoria.value = product.categorias_id_categoria;
      openFormModal();
      productFields.nombre.focus();
    }

    function fillCategoryForm(category) {
      if (!category) {
        return;
      }
      state.editingCategoryId = category.id;
      categoryFields.id.value = category.id;
      categoryFields.nombre.value = category.nombre;
      openFormModal();
      categoryFields.nombre.focus();
    }

    function saveProduct() {
      var product = {
        id: state.editingProductId || getNextId(state.products),
        nombre: productFields.nombre.value.trim(),
        precio_compra: Number(productFields.precio_compra.value || 0),
        precio_venta: Number(productFields.precio_venta.value || 0),
        cantidad: Number(productFields.cantidad.value || 0),
        estado: productFields.estado.value,
        categorias_id_categoria: Number(productFields.categorias_id_categoria.value || 0)
      };

      if (state.editingProductId) {
        state.products = state.products.map(function (item) {
          return item.id === state.editingProductId ? product : item;
        });
      } else {
        state.products.unshift(product);
      }

      writeStorage(PRODUCTS_STORAGE_KEY, state.products);
      closeFormModal();
      resetProductForm();
      renderAll();
    }

    function saveCategory() {
      var category = {
        id: state.editingCategoryId || getNextId(state.categories),
        nombre: categoryFields.nombre.value.trim()
      };

      if (state.editingCategoryId) {
        state.categories = state.categories.map(function (item) {
          return item.id === state.editingCategoryId ? category : item;
        });
      } else {
        state.categories.unshift(category);
      }

      writeStorage(CATEGORIES_STORAGE_KEY, state.categories);
      closeFormModal();
      resetCategoryForm();
      renderAll();
    }

    function askDelete(type, id, name) {
      state.pendingDelete = { type: type, id: id };
      deleteConfirmMessage.textContent = type === 'product'
        ? 'Estas a punto de eliminar el producto "' + name + '".'
        : 'Estas a punto de eliminar la categoria "' + name + '".';
      deleteModal.show();
    }

    function performDelete() {
      if (!state.pendingDelete) {
        return;
      }

      if (state.pendingDelete.type === 'product') {
        state.products = state.products.filter(function (item) {
          return item.id !== state.pendingDelete.id;
        });
        writeStorage(PRODUCTS_STORAGE_KEY, state.products);
        if (state.editingProductId === state.pendingDelete.id) {
          resetProductForm();
        }
      } else {
        state.categories = state.categories.filter(function (item) {
          return item.id !== state.pendingDelete.id;
        });
        writeStorage(CATEGORIES_STORAGE_KEY, state.categories);
        if (state.editingCategoryId === state.pendingDelete.id) {
          resetCategoryForm();
        }
      }

      state.pendingDelete = null;
      deleteModal.hide();
      renderAll();
    }

    function bindEvents() {
      switchButtons.forEach(function (button) {
        button.addEventListener('click', function () {
          state.view = button.getAttribute('data-view');
          state.query = '';
          searchInput.value = '';
          switchButtons.forEach(function (item) {
            item.classList.toggle('active', item === button);
          });
          updateViewCopy();
          renderAll();
        });
      });

      newTrigger.addEventListener('click', function () {
        resetProductForm();
        resetCategoryForm();
        openFormModal();
        if (state.view === 'products') {
          productFields.nombre.focus();
        } else {
          categoryFields.nombre.focus();
        }
      });

      searchInput.addEventListener('input', function (event) {
        state.query = normalizeText(event.target.value);
        renderTable();
      });

      productForm.addEventListener('submit', function (event) {
        event.preventDefault();
        if (!productForm.checkValidity()) {
          productForm.reportValidity();
          return;
        }
        saveProduct();
      });

      categoryForm.addEventListener('submit', function (event) {
        event.preventDefault();
        if (!categoryForm.checkValidity()) {
          categoryForm.reportValidity();
          return;
        }
        saveCategory();
      });

      productCancelButton.addEventListener('click', closeFormModal);
      categoryCancelButton.addEventListener('click', closeFormModal);

      tableBody.addEventListener('click', function (event) {
        var trigger = event.target.closest('button[data-action]');
        if (!trigger) {
          return;
        }

        var action = trigger.getAttribute('data-action');
        var id = Number(trigger.getAttribute('data-id'));
        var name = trigger.getAttribute('data-name') || '';

        if (action === 'edit-product') {
          fillProductForm(state.products.find(function (item) { return item.id === id; }));
        }
        if (action === 'delete-product') {
          askDelete('product', id, name);
        }
        if (action === 'edit-category') {
          fillCategoryForm(state.categories.find(function (item) { return item.id === id; }));
        }
        if (action === 'delete-category') {
          askDelete('category', id, name);
        }
      });

      deleteConfirmButton.addEventListener('click', performDelete);
    }

    function loadCatalogs() {
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
          var storedProducts = readStorage(PRODUCTS_STORAGE_KEY);
          var storedCategories = readStorage(CATEGORIES_STORAGE_KEY);

          state.products = (storedProducts && storedProducts.length ? storedProducts : results[0]).map(normalizeProduct);
          state.categories = (storedCategories && storedCategories.length ? storedCategories : results[1]).map(normalizeCategory);

          if (!storedProducts || !storedProducts.length) {
            writeStorage(PRODUCTS_STORAGE_KEY, state.products);
          }
          if (!storedCategories || !storedCategories.length) {
            writeStorage(CATEGORIES_STORAGE_KEY, state.categories);
          }

          updateViewCopy();
          renderAll();
        })
        .catch(function () {
          var fallbackProducts = readStorage(PRODUCTS_STORAGE_KEY);
          var fallbackCategories = readStorage(CATEGORIES_STORAGE_KEY);

          if (fallbackProducts && fallbackProducts.length && fallbackCategories && fallbackCategories.length) {
            state.products = fallbackProducts.map(normalizeProduct);
            state.categories = fallbackCategories.map(normalizeCategory);
            updateViewCopy();
            renderAll();
            return;
          }

          renderErrorState();
        });
    }

    bindEvents();
    loadCatalogs();
  });
})();
