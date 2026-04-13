/**
 * productos.js — Lista con filtros, CRUD productos y categorías (Oracle API).
 */
(function () {
  'use strict';

  function uiAlert(msg, title) {
    if (window.UiDialog && window.UiDialog.alert) {
      return window.UiDialog.alert(String(msg), { title: title || 'Productos' });
    }
    alert(msg);
    return Promise.resolve();
  }

  function uiConfirm(msg, title) {
    if (window.UiDialog && window.UiDialog.confirm) {
      return window.UiDialog.confirm(String(msg), { title: title || 'Confirmar' });
    }
    return Promise.resolve(confirm(msg));
  }

  var loadingHtml =
    '<div class="d-flex justify-content-center py-5">' +
    '<div class="spinner-border text-secondary" role="status">' +
    '<span class="visually-hidden">Cargando</span>' +
    '</div></div>';

  var productos = [];
  var categorias = [];
  var estados = [];
  var modalProducto = null;
  var modalCategoria = null;

  function esc(s) {
    var d = document.createElement('div');
    d.textContent = s == null ? '' : String(s);
    return d.innerHTML;
  }

  function money(n) {
    if (n == null || n === '') return '—';
    return '₡' + Number(n).toLocaleString('es-CR');
  }

  function getFilteredProductos() {
    var q = (document.getElementById('filtroProdBuscar') || { value: '' }).value.trim().toLowerCase();
    var cat = (document.getElementById('filtroProdCategoria') || { value: '' }).value;
    var est = (document.getElementById('filtroProdEstado') || { value: '' }).value;

    return productos.filter(function (p) {
      if (q && String(p.nombre).toLowerCase().indexOf(q) === -1) return false;
      if (cat && String(p.idCategoria) !== cat) return false;
      if (est && String(p.idEstado) !== est) return false;
      return true;
    });
  }

  function fillFiltroCategorias() {
    var sel = document.getElementById('filtroProdCategoria');
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

  function fillFiltroEstados() {
    var sel = document.getElementById('filtroProdEstado');
    if (!sel) return;
    var cur = sel.value;
    sel.innerHTML = '<option value="">Todos</option>';
    estados.forEach(function (e) {
      var opt = document.createElement('option');
      opt.value = String(e.id);
      opt.textContent = e.nombre;
      sel.appendChild(opt);
    });
    sel.value = cur;
  }

  function fillSelectCategoriasForm() {
    var sel = document.getElementById('prodIdCategoria');
    if (!sel) return;
    var cur = sel.value;
    sel.innerHTML = '';
    categorias.forEach(function (c) {
      var opt = document.createElement('option');
      opt.value = String(c.id);
      opt.textContent = c.nombre;
      sel.appendChild(opt);
    });
    if (cur) sel.value = cur;
  }

  function fillSelectEstadosForm() {
    var sel = document.getElementById('prodIdEstado');
    if (!sel) return;
    var cur = sel.value;
    sel.innerHTML = '';
    estados.forEach(function (e) {
      var opt = document.createElement('option');
      opt.value = String(e.id);
      opt.textContent = e.nombre;
      sel.appendChild(opt);
    });
    if (cur) sel.value = cur;
  }

  function renderProductosTable() {
    var wrap = document.getElementById('productosSistemaTable');
    if (!wrap) return;

    var rows = getFilteredProductos();
    if (rows.length === 0) {
      wrap.innerHTML =
        '<p class="text-muted py-4 text-center">No hay productos que coincidan con los filtros.</p>';
      return;
    }

    var thead =
      '<thead class="table-light"><tr>' +
      '<th>Nombre</th><th>Categoría</th><th>Estado</th><th class="text-end">Stock</th>' +
      '<th class="text-end">P. compra</th><th class="text-end">P. venta</th>' +
      '<th class="text-end" style="width:120px">Acciones</th></tr></thead>';
    var tbody = '<tbody>';
    rows.forEach(function (p) {
      tbody +=
        '<tr>' +
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
        '<td class="text-end">' +
        esc(String(p.cantidad)) +
        '</td>' +
        '<td class="text-end">' +
        money(p.precioCompra) +
        '</td>' +
        '<td class="text-end">' +
        money(p.precioVenta) +
        '</td>' +
        '<td class="text-end text-nowrap">' +
        '<button type="button" class="btn btn-sm btn-outline-primary btn-prod-edit" data-id="' +
        p.id +
        '" title="Editar"><i class="bi bi-pencil"></i></button> ' +
        '<button type="button" class="btn btn-sm btn-outline-danger btn-prod-del" data-id="' +
        p.id +
        '" title="Eliminar"><i class="bi bi-trash"></i></button>' +
        '</td></tr>';
    });
    tbody += '</tbody>';
    wrap.innerHTML =
      '<div class="table-responsive shadow-sm rounded border bg-white">' +
      '<table class="table table-hover table-sm mb-0 align-middle">' +
      thead +
      tbody +
      '</table></div>';

    wrap.querySelectorAll('.btn-prod-edit').forEach(function (btn) {
      btn.addEventListener('click', function () {
        var id = parseInt(btn.getAttribute('data-id'), 10);
        var prod = productos.find(function (x) {
          return x.id === id;
        });
        if (prod) openModalProducto(prod);
      });
    });
    wrap.querySelectorAll('.btn-prod-del').forEach(function (btn) {
      btn.addEventListener('click', function () {
        var id = parseInt(btn.getAttribute('data-id'), 10);
        eliminarProducto(id);
      });
    });
  }

  function renderCategoriasTable() {
    var wrap = document.getElementById('categoriasSistemaTable');
    if (!wrap) return;

    if (categorias.length === 0) {
      wrap.innerHTML = '<p class="text-muted py-4 text-center">No hay categorías.</p>';
      return;
    }

    var thead =
      '<thead class="table-light"><tr><th>ID</th><th>Nombre</th><th class="text-end" style="width:120px">Acciones</th></tr></thead>';
    var tbody = '<tbody>';
    categorias.forEach(function (c) {
      tbody +=
        '<tr><td>' +
        esc(String(c.id)) +
        '</td><td>' +
        esc(c.nombre) +
        '</td><td class="text-end text-nowrap">' +
        '<button type="button" class="btn btn-sm btn-outline-primary btn-cat-edit" data-id="' +
        c.id +
        '"><i class="bi bi-pencil"></i></button> ' +
        '<button type="button" class="btn btn-sm btn-outline-danger btn-cat-del" data-id="' +
        c.id +
        '"><i class="bi bi-trash"></i></button>' +
        '</td></tr>';
    });
    tbody += '</tbody>';
    wrap.innerHTML =
      '<div class="table-responsive shadow-sm rounded border bg-white">' +
      '<table class="table table-hover table-sm mb-0">' +
      thead +
      tbody +
      '</table></div>';

    wrap.querySelectorAll('.btn-cat-edit').forEach(function (btn) {
      btn.addEventListener('click', function () {
        var id = parseInt(btn.getAttribute('data-id'), 10);
        var cat = categorias.find(function (x) {
          return x.id === id;
        });
        if (cat) openModalCategoria(cat);
      });
    });
    wrap.querySelectorAll('.btn-cat-del').forEach(function (btn) {
      btn.addEventListener('click', function () {
        var id = parseInt(btn.getAttribute('data-id'), 10);
        eliminarCategoria(id);
      });
    });
  }

  function openModalProducto(prod) {
    var el = document.getElementById('modalProducto');
    if (!el || !window.bootstrap) return;
    document.getElementById('modalProductoLabel').textContent = prod ? 'Editar producto' : 'Nuevo producto';
    document.getElementById('prodId').value = prod ? String(prod.id) : '';
    document.getElementById('prodNombre').value = prod ? prod.nombre : '';
    document.getElementById('prodPrecioCompra').value =
      prod && prod.precioCompra != null ? String(prod.precioCompra) : '';
    document.getElementById('prodPrecioVenta').value =
      prod && prod.precioVenta != null ? String(prod.precioVenta) : '';
    document.getElementById('prodCantidad').value = prod ? String(prod.cantidad) : '0';
    fillSelectCategoriasForm();
    fillSelectEstadosForm();
    if (prod) {
      document.getElementById('prodIdCategoria').value = String(prod.idCategoria);
      document.getElementById('prodIdEstado').value = String(prod.idEstado);
    } else if (categorias.length) {
      document.getElementById('prodIdCategoria').value = String(categorias[0].id);
    }
    if (estados.length) {
      document.getElementById('prodIdEstado').value = prod
        ? String(prod.idEstado)
        : String(estados[0].id);
    }
    modalProducto = modalProducto || new bootstrap.Modal(el);
    modalProducto.show();
  }

  function guardarProducto() {
    if (!window.Api) {
      void uiAlert('API no disponible.', 'Error');
      return;
    }
    var id = document.getElementById('prodId').value.trim();
    var nombre = document.getElementById('prodNombre').value.trim();
    var pc = parseFloat(document.getElementById('prodPrecioCompra').value);
    var pv = parseFloat(document.getElementById('prodPrecioVenta').value);
    var cant = parseInt(document.getElementById('prodCantidad').value, 10);
    var idCat = parseInt(document.getElementById('prodIdCategoria').value, 10);
    var idEst = parseInt(document.getElementById('prodIdEstado').value, 10);

    if (!nombre) {
      void uiAlert('Indique el nombre del producto.');
      return;
    }
    if (!idCat || !idEst) {
      void uiAlert('Seleccione categoría y estado.');
      return;
    }

    var body = {
      nombre: nombre,
      precioCompra: isNaN(pc) ? null : pc,
      precioVenta: isNaN(pv) ? null : pv,
      cantidad: isNaN(cant) ? 0 : cant,
      idCategoria: idCat,
      idEstado: idEst,
    };
    if (id) {
      body.action = 'update';
      body.id = parseInt(id, 10);
    } else {
      body.action = 'insert';
    }

    var btn = document.getElementById('btnGuardarProducto');
    if (btn) btn.disabled = true;

    window.Api
      .post('/productos_save.php', body)
      .then(function () {
        if (modalProducto) modalProducto.hide();
        return reloadAll();
      })
      .then(function () {
        return uiAlert(body.action === 'insert' ? 'Producto creado.' : 'Producto actualizado.', 'Listo');
      })
      .catch(function (e) {
        void uiAlert(String(e.message || e), 'Error');
      })
      .finally(function () {
        if (btn) btn.disabled = false;
      });
  }

  function eliminarProducto(id) {
    uiConfirm('¿Eliminar este producto del catálogo?', 'Eliminar producto').then(function (ok) {
      if (!ok || !window.Api) return;
      window.Api
        .post('/productos_save.php', { action: 'delete', id: id })
        .then(function () {
          return reloadAll();
        })
        .then(function () {
          return uiAlert('Producto eliminado.', 'Listo');
        })
        .catch(function (e) {
          void uiAlert(String(e.message || e), 'Error');
        });
    });
  }

  function openModalCategoria(cat) {
    var el = document.getElementById('modalCategoria');
    if (!el || !window.bootstrap) return;
    document.getElementById('modalCategoriaLabel').textContent = cat ? 'Editar categoría' : 'Nueva categoría';
    document.getElementById('catId').value = cat ? String(cat.id) : '';
    document.getElementById('catNombre').value = cat ? cat.nombre : '';
    modalCategoria = modalCategoria || new bootstrap.Modal(el);
    modalCategoria.show();
  }

  function guardarCategoria() {
    if (!window.Api) {
      void uiAlert('API no disponible.', 'Error');
      return;
    }
    var id = document.getElementById('catId').value.trim();
    var nombre = document.getElementById('catNombre').value.trim();
    if (!nombre) {
      void uiAlert('Indique el nombre de la categoría.');
      return;
    }
    var body = { nombre: nombre };
    if (id) {
      body.action = 'update';
      body.id = parseInt(id, 10);
    } else {
      body.action = 'insert';
    }

    var btn = document.getElementById('btnGuardarCategoria');
    if (btn) btn.disabled = true;

    window.Api
      .post('/categorias_save.php', body)
      .then(function () {
        if (modalCategoria) modalCategoria.hide();
        return reloadAll();
      })
      .then(function () {
        return uiAlert(
          body.action === 'insert' ? 'Categoría creada.' : 'Categoría actualizada.',
          'Listo'
        );
      })
      .catch(function (e) {
        void uiAlert(String(e.message || e), 'Error');
      })
      .finally(function () {
        if (btn) btn.disabled = false;
      });
  }

  function eliminarCategoria(id) {
    uiConfirm(
      '¿Eliminar esta categoría? (No podrá si hay productos asociados.)',
      'Eliminar categoría'
    ).then(function (ok) {
      if (!ok || !window.Api) return;
      window.Api
        .post('/categorias_save.php', { action: 'delete', id: id })
        .then(function () {
          return reloadAll();
        })
        .then(function () {
          return uiAlert('Categoría eliminada.', 'Listo');
        })
        .catch(function (e) {
          void uiAlert(String(e.message || e), 'Error');
        });
    });
  }

  function reloadAll() {
    var wrapP = document.getElementById('productosSistemaTable');
    var wrapC = document.getElementById('categoriasSistemaTable');
    if (wrapP) wrapP.innerHTML = loadingHtml;
    if (wrapC) wrapC.innerHTML = loadingHtml;

    if (!window.Api) {
      productos = [];
      categorias = [];
      estados = [];
      renderProductosTable();
      renderCategoriasTable();
      return Promise.resolve();
    }

    return Promise.all([
      window.Api.get('/productos_list.php').then(function (j) {
        productos = j.data || [];
      }),
      window.Api.get('/categorias_list.php').then(function (j) {
        categorias = j.data || [];
      }),
      window.Api.get('/estados_list.php').then(function (j) {
        estados = j.data || [];
      }),
    ])
      .then(function () {
        fillFiltroCategorias();
        fillFiltroEstados();
        renderProductosTable();
        renderCategoriasTable();
      })
      .catch(function (e) {
        if (wrapP) {
          wrapP.innerHTML =
            '<div class="alert alert-danger">Error al cargar datos. ' + esc(e.message || '') + '</div>';
        }
      });
  }

  document.addEventListener('DOMContentLoaded', function () {
    document.getElementById('btnNuevoProducto')?.addEventListener('click', function () {
      openModalProducto(null);
    });
    document.getElementById('btnGuardarProducto')?.addEventListener('click', guardarProducto);
    document.getElementById('btnNuevaCategoria')?.addEventListener('click', function () {
      openModalCategoria(null);
    });
    document.getElementById('btnGuardarCategoria')?.addEventListener('click', guardarCategoria);

    document.getElementById('filtroProdBuscar')?.addEventListener('input', renderProductosTable);
    document.getElementById('filtroProdCategoria')?.addEventListener('change', renderProductosTable);
    document.getElementById('filtroProdEstado')?.addEventListener('change', renderProductosTable);

    document.getElementById('btnProdLimpiarFiltros')?.addEventListener('click', function () {
      var a = document.getElementById('filtroProdBuscar');
      var b = document.getElementById('filtroProdCategoria');
      var c = document.getElementById('filtroProdEstado');
      if (a) a.value = '';
      if (b) b.value = '';
      if (c) c.value = '';
      renderProductosTable();
    });

    reloadAll();
  });
})();
