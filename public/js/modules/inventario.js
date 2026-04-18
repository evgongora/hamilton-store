/**
 * inventario.js — Vista de stock con filtros y ajuste de cantidad (rol con permiso).
 */
(function () {
  'use strict';

  function uiAlert(msg, title) {
    if (window.UiDialog && window.UiDialog.alert) {
      return window.UiDialog.alert(String(msg), { title: title || 'Inventario' });
    }
    alert(msg);
    return Promise.resolve();
  }

  var loadingHtml =
    '<div class="d-flex justify-content-center py-5">' +
    '<div class="spinner-border text-secondary" role="status">' +
    '<span class="visually-hidden">Cargando</span>' +
    '</div></div>';

  var LOW_STOCK_MAX = 5;
  var productos = [];
  var categorias = [];
  var modalAjuste = null;

  function canEditStock() {
    return String(document.body.getAttribute('data-can-edit-stock') || '') === '1';
  }

  function esc(s) {
    var d = document.createElement('div');
    d.textContent = s == null ? '' : String(s);
    return d.innerHTML;
  }

  function money(n) {
    if (n == null || n === '') return '—';
    return '₡' + Number(n).toLocaleString('es-CR');
  }

  function getFiltered() {
    var q = (document.getElementById('invBuscar') || { value: '' }).value.trim().toLowerCase();
    var cat = (document.getElementById('invCategoria') || { value: '' }).value;
    var soloBajo = document.getElementById('invSoloBajo') && document.getElementById('invSoloBajo').checked;

    return productos.filter(function (p) {
      if (q && String(p.nombre).toLowerCase().indexOf(q) === -1) return false;
      if (cat && String(p.idCategoria) !== cat) return false;
      if (soloBajo && (p.cantidad == null || p.cantidad > LOW_STOCK_MAX)) return false;
      return true;
    });
  }

  function fillCategoriaFilter() {
    var sel = document.getElementById('invCategoria');
    if (!sel) return;
    var cur = sel.value;
    sel.innerHTML = '<option value="">Todas</option>';
    categorias.forEach(function (c) {
      var opt = document.createElement('option');
      opt.value = String(c.id);
      opt.textContent = c.nombre;
      sel.appendChild(opt);
    });
    sel.value = cur;
  }

  function rowAlertClass(cant) {
    var n = Number(cant);
    if (n <= 0) return 'table-danger';
    if (n <= LOW_STOCK_MAX) return 'table-warning';
    return '';
  }

  function render() {
    var wrap = document.getElementById('inventarioSistemaTable');
    if (!wrap) return;

    var rows = getFiltered();
    if (rows.length === 0) {
      wrap.innerHTML =
        '<p class="text-muted py-4 text-center">No hay productos que coincidan con los filtros.</p>';
      return;
    }

    var thead =
      '<thead class="table-light"><tr>' +
      '<th>Producto</th><th>Categoría</th><th>Estado</th>' +
      '<th class="text-end">Stock</th><th class="text-end">P. venta</th>';
    if (canEditStock()) {
      thead += '<th class="text-end" style="width:100px">Ajuste</th>';
    }
    thead += '</tr></thead>';

    var tbody = '<tbody>';
    rows.forEach(function (p) {
      var trClass = rowAlertClass(p.cantidad);
      tbody +=
        '<tr class="' +
        esc(trClass) +
        '">' +
        '<td><strong>' +
        esc(p.nombre) +
        '</strong><div class="small text-muted">ID ' +
        esc(String(p.id)) +
        '</div></td>' +
        '<td>' +
        esc(p.categoria || '') +
        '</td>' +
        '<td><span class="badge bg-secondary">' +
        esc(p.estado || '') +
        '</span></td>' +
        '<td class="text-end fw-semibold">' +
        esc(String(p.cantidad)) +
        '</td>' +
        '<td class="text-end">' +
        money(p.precioVenta) +
        '</td>';
      if (canEditStock()) {
        tbody +=
          '<td class="text-end">' +
          '<button type="button" class="btn btn-sm btn-outline-dark btn-inv-ajuste" data-id="' +
          p.id +
          '">Ajustar</button>' +
          '</td>';
      }
      tbody += '</tr>';
    });
    tbody += '</tbody>';

    wrap.innerHTML =
      '<div class="table-responsive shadow-sm rounded border bg-white">' +
      '<table class="table table-hover table-sm mb-0 align-middle">' +
      thead +
      tbody +
      '</table></div>';

    if (canEditStock()) {
      wrap.querySelectorAll('.btn-inv-ajuste').forEach(function (btn) {
        btn.addEventListener('click', function () {
          var id = parseInt(btn.getAttribute('data-id'), 10);
          var prod = productos.find(function (x) {
            return x.id === id;
          });
          if (prod) openModalAjuste(prod);
        });
      });
    }
  }

  function openModalAjuste(prod) {
    var el = document.getElementById('modalAjusteStock');
    if (!el || !window.bootstrap) return;
    document.getElementById('invProdId').value = String(prod.id);
    document.getElementById('invProdNombre').textContent = prod.nombre;
    document.getElementById('invProdStockActual').textContent = String(prod.cantidad);
    document.getElementById('invNuevaCantidad').value = String(prod.cantidad);
    modalAjuste = modalAjuste || new bootstrap.Modal(el);
    modalAjuste.show();
  }

  function guardarAjuste() {
    if (!window.Api || !canEditStock()) {
      void uiAlert('No tiene permiso para ajustar stock.', 'Error');
      return;
    }
    var id = parseInt(document.getElementById('invProdId').value, 10);
    var nueva = parseInt(document.getElementById('invNuevaCantidad').value, 10);
    var Vi = window.HamiltonValidation;
    var stockOk = Vi && Vi.enteroNoNegativo ? Vi.enteroNoNegativo(nueva) : !isNaN(nueva) && nueva >= 0;
    if (!stockOk) {
      void uiAlert('Indique una cantidad válida (entero ≥ 0).');
      return;
    }
    var prod = productos.find(function (x) {
      return x.id === id;
    });
    if (!prod) {
      void uiAlert('Producto no encontrado.');
      return;
    }

    var body = {
      action: 'update',
      id: id,
      nombre: prod.nombre,
      precioCompra: prod.precioCompra,
      precioVenta: prod.precioVenta,
      cantidad: nueva,
      idCategoria: prod.idCategoria,
      idEstado: prod.idEstado,
    };

    var btn = document.getElementById('btnGuardarAjusteStock');
    if (btn) btn.disabled = true;

    window.Api
      .post('/productos_save.php', body)
      .then(function () {
        if (modalAjuste) modalAjuste.hide();
        return load();
      })
      .then(function () {
        return uiAlert('Stock actualizado.', 'Listo');
      })
      .catch(function (e) {
        void uiAlert(String(e.message || e), 'Error');
      })
      .finally(function () {
        if (btn) btn.disabled = false;
      });
  }

  function load() {
    var wrap = document.getElementById('inventarioSistemaTable');
    if (!wrap || !window.Api) return Promise.resolve();

    wrap.innerHTML = loadingHtml;

    return Promise.all([
      window.Api.get('/productos_list.php').then(function (j) {
        productos = j.data || [];
      }),
      window.Api.get('/categorias_list.php').then(function (j) {
        categorias = j.data || [];
      }),
    ])
      .then(function () {
        fillCategoriaFilter();
        render();
      })
      .catch(function (e) {
        wrap.innerHTML =
          '<div class="alert alert-danger">No se pudo cargar el inventario. ' + esc(e.message || '') + '</div>';
      });
  }

  document.addEventListener('DOMContentLoaded', function () {
    document.getElementById('invBuscar')?.addEventListener('input', render);
    document.getElementById('invCategoria')?.addEventListener('change', render);
    document.getElementById('invSoloBajo')?.addEventListener('change', render);
    document.getElementById('invLimpiar')?.addEventListener('click', function () {
      var a = document.getElementById('invBuscar');
      var b = document.getElementById('invCategoria');
      var c = document.getElementById('invSoloBajo');
      if (a) a.value = '';
      if (b) b.value = '';
      if (c) c.checked = false;
      render();
    });
    document.getElementById('btnGuardarAjusteStock')?.addEventListener('click', guardarAjuste);
    load();
  });
})();
