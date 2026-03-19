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

    var basePath = (document.body.dataset.basePath || '').replace(/\/$/, '');
    var productsMockUrl = basePath + '/js/mocks/productos.json';
    var categoriesMockUrl = basePath + '/js/mocks/categorias.json';

    var productForm = document.getElementById('product-form');
    var categoryForm = document.getElementById('category-form');
    var tableHead = document.getElementById('catalog-table-head');
    var tableBody = document.getElementById('catalog-table-body');
    var countCell = document.getElementById('catalog-count');
    var emptyState = document.getElementById('catalog-empty-state');
    var emptyIcon = document.getElementById('catalog-empty-icon');
    var emptyText = document.getElementById('catalog-empty-text');
    var newTrigger = document.getElementById('catalog-new-trigger');
    var newLabel = document.getElementById('catalog-new-label');
    var switchButtons = Array.prototype.slice.call(document.querySelectorAll('.catalog-switch'));
    var listTitle = document.getElementById('catalog-list-title');
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

    function renderCategoryOptions(selectedId) {
      var currentValue = selectedId != null ? Number(selectedId) : Number(productFields.categorias_id_categoria.value || 0);

      productFields.categorias_id_categoria.innerHTML = '<option value="">-- Seleccionar categoria --</option>';

      state.categories.forEach(function (category) {
        var option = document.createElement('option');
        option.value = String(category.id);
        option.textContent = category.nombre;

        if (currentValue && Number(category.id) === currentValue) {
          option.selected = true;
        }

        productFields.categorias_id_categoria.appendChild(option);
      });

      productFields.categorias_id_categoria.disabled = state.categories.length === 0;
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
      renderCategoryOptions();
    }

    function resetCategoryForm() {
      state.editingCategoryId = null;
      categoryForm.reset();
      categoryFields.id.value = '';
    }

    function isProductsView() {
      return state.view === 'products';
    }

    function updateSwitcherState() {
      switchButtons.forEach(function (button) {
        var active = button.getAttribute('data-view') === state.view;
        button.classList.toggle('active', active);
        button.setAttribute('aria-selected', active ? 'true' : 'false');
      });
    }

    function updateViewCopy() {
      var productsView = isProductsView();

      listTitle.innerHTML = productsView
        ? '<i class="bi bi-box me-2"></i>Listado de productos'
        : '<i class="bi bi-tags me-2"></i>Listado de categorias';
      newLabel.textContent = productsView ? 'Nuevo producto' : 'Nueva categoria';

      productForm.hidden = !productsView;
      categoryForm.hidden = productsView;

      resetProductForm();
      resetCategoryForm();
      applyFormCopy();
      updateSwitcherState();
    }

    function applyFormCopy() {
      if (isProductsView()) {
        formTitle.innerHTML = state.editingProductId
          ? '<i class="bi bi-pencil-square me-2"></i>Editar producto'
          : '<i class="bi bi-box-seam me-2"></i>Nuevo producto';
        formDescription.textContent = state.editingProductId
          ? 'Actualiza la informacion del producto seleccionado.'
          : 'Completa los datos requeridos para registrar un producto.';
        formBadge.textContent = state.editingProductId ? 'Edicion' : 'Nuevo';
        productSubmitButton.innerHTML = state.editingProductId
          ? '<i class="bi bi-check-lg me-2"></i>Actualizar producto'
          : '<i class="bi bi-save me-2"></i>Guardar producto';
        return;
      }

      formTitle.innerHTML = state.editingCategoryId
        ? '<i class="bi bi-pencil-square me-2"></i>Editar categoria'
        : '<i class="bi bi-tags me-2"></i>Nueva categoria';
      formDescription.textContent = state.editingCategoryId
        ? 'Actualiza el nombre de la categoria seleccionada.'
        : 'Completa los datos requeridos para registrar una categoria.';
      formBadge.textContent = state.editingCategoryId ? 'Edicion' : 'Nuevo';
      categorySubmitButton.innerHTML = state.editingCategoryId
        ? '<i class="bi bi-check-lg me-2"></i>Actualizar categoria'
        : '<i class="bi bi-save me-2"></i>Guardar categoria';
    }

    function renderStats() {
      if (isProductsView()) {
        statLabel1.textContent = 'Productos registrados';
        statLabel2.textContent = 'Unidades en inventario';
        statLabel3.textContent = 'Categorias activas';
        statTotal.textContent = String(state.products.length);
        statStock.textContent = String(state.products.reduce(function (sum, product) {
          return sum + Number(product.cantidad || 0);
        }, 0));
        statCategories.textContent = String(new Set(state.products.map(function (product) {
          return Number(product.categorias_id_categoria) || 0;
        }).filter(Boolean)).size);
        return;
      }

      statLabel1.textContent = 'Categorias registradas';
      statLabel2.textContent = 'Categorias con productos';
      statLabel3.textContent = 'Productos vinculados';
      statTotal.textContent = String(state.categories.length);
      statStock.textContent = String(new Set(state.products.map(function (product) {
        return Number(product.categorias_id_categoria) || 0;
      }).filter(Boolean)).size);
      statCategories.textContent = String(state.products.length);
    }

    function renderTableHead() {
      if (isProductsView()) {
        tableHead.innerHTML = [
          '<tr>',
          '<th>Nombre</th>',
          '<th>Precio compra</th>',
          '<th>Precio venta</th>',
          '<th>Cantidad</th>',
          '<th>Estado</th>',
          '<th>Categoria</th>',
          '<th class="text-end">Acciones</th>',
          '</tr>'
        ].join('');
        countCell.colSpan = 7;
        return;
      }

      tableHead.innerHTML = [
        '<tr>',
        '<th>Nombre</th>',
        '<th>Productos vinculados</th>',
        '<th class="text-end">Acciones</th>',
        '</tr>'
      ].join('');
      countCell.colSpan = 3;
    }

    function renderEmptyState() {
      if (isProductsView()) {
        emptyIcon.className = 'bi bi-box display-4';
        emptyText.innerHTML = 'No hay productos registrados. <a href="#" id="catalog-empty-link">Agregar uno</a>';
      } else {
        emptyIcon.className = 'bi bi-tags display-4';
        emptyText.innerHTML = 'No hay categorias registradas. <a href="#" id="catalog-empty-link">Agregar una</a>';
      }
    }

    function renderTable() {
      var items = isProductsView() ? state.products : state.categories;
      tableBody.innerHTML = '';
      renderEmptyState();

      if (!items.length) {
        emptyState.style.display = 'block';
        countCell.textContent = isProductsView() ? '0 productos' : '0 categorias';
        return;
      }

      emptyState.style.display = 'none';

      if (isProductsView()) {
        countCell.textContent = items.length + ' producto(s)';
        items.forEach(function (item) {
          var row = document.createElement('tr');
          row.innerHTML = [
            '<td><div class="fw-semibold">' + escapeHtml(item.nombre) + '</div></td>',
            '<td>' + formatCurrency(item.precio_compra) + '</td>',
            '<td>' + formatCurrency(item.precio_venta) + '</td>',
            '<td>' + escapeHtml(item.cantidad) + '</td>',
            '<td><span class="products-status status-' + escapeHtml(item.estado) + '">' + escapeHtml(item.estado) + '</span></td>',
            '<td>' + escapeHtml(getCategoryName(item.categorias_id_categoria)) + '</td>',
            '<td class="text-end">',
            '<button type="button" class="btn btn-outline-primary btn-sm me-1" data-action="edit-product" data-id="' + escapeHtml(item.id) + '" title="Editar"><i class="bi bi-pencil"></i></button>',
            '<button type="button" class="btn btn-outline-danger btn-sm" data-action="delete-product" data-id="' + escapeHtml(item.id) + '" data-name="' + escapeHtml(item.nombre) + '" title="Eliminar"><i class="bi bi-trash"></i></button>',
            '</td>'
          ].join('');
          tableBody.appendChild(row);
        });
        return;
      }

      countCell.textContent = items.length + ' categoria(s)';
      items.forEach(function (item) {
        var linkedProducts = state.products.filter(function (product) {
          return Number(product.categorias_id_categoria) === Number(item.id);
        }).length;

        var row = document.createElement('tr');
        row.innerHTML = [
          '<td><div class="fw-semibold">' + escapeHtml(item.nombre) + '</div></td>',
          '<td>' + escapeHtml(linkedProducts) + '</td>',
          '<td class="text-end">',
          '<button type="button" class="btn btn-outline-primary btn-sm me-1" data-action="edit-category" data-id="' + escapeHtml(item.id) + '" title="Editar"><i class="bi bi-pencil"></i></button>',
          '<button type="button" class="btn btn-outline-danger btn-sm" data-action="delete-category" data-id="' + escapeHtml(item.id) + '" data-name="' + escapeHtml(item.nombre) + '" title="Eliminar"><i class="bi bi-trash"></i></button>',
          '</td>'
        ].join('');
        tableBody.appendChild(row);
      });
    }

    function renderAll() {
      renderCategoryOptions();
      renderStats();
      renderTableHead();
      renderTable();
      applyFormCopy();
    }

    function renderLoadingState() {
      tableHead.innerHTML = '';
      tableBody.innerHTML = '<tr><td colspan="7" class="text-center text-muted py-4">Cargando catalogos...</td></tr>';
      emptyState.style.display = 'none';
      countCell.textContent = 'Cargando...';
      statTotal.textContent = '...';
      statStock.textContent = '...';
      statCategories.textContent = '...';
    }

    function renderErrorState() {
      tableHead.innerHTML = '';
      tableBody.innerHTML = '<tr><td colspan="7" class="text-center text-danger py-4">No se pudieron cargar los catalogos.</td></tr>';
      emptyState.style.display = 'none';
      countCell.textContent = '0 registros';
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
      renderCategoryOptions(product.categorias_id_categoria);
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

    function openNewRecord(event) {
      if (event) {
        event.preventDefault();
      }

      resetProductForm();
      resetCategoryForm();
      openFormModal();

      if (isProductsView()) {
        productFields.nombre.focus();
      } else {
        categoryFields.nombre.focus();
      }
    }

    function bindEvents() {
      switchButtons.forEach(function (button) {
        button.addEventListener('click', function () {
          state.view = button.getAttribute('data-view');
          updateViewCopy();
          renderAll();
        });
      });

      newTrigger.addEventListener('click', openNewRecord);
      emptyState.addEventListener('click', function (event) {
        if (event.target && event.target.id === 'catalog-empty-link') {
          openNewRecord(event);
        }
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
        } else if (action === 'delete-product') {
          askDelete('product', id, name);
        } else if (action === 'edit-category') {
          fillCategoryForm(state.categories.find(function (item) { return item.id === id; }));
        } else if (action === 'delete-category') {
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
