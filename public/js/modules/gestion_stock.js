/**
 * gestion_stock.js — Tipos de gestión y movimientos (pkg_tipo_gestion, pkg_gestion_stock).
 */
(function () {
  'use strict';

  function uiAlert(msg, title) {
    if (window.UiDialog && window.UiDialog.alert) {
      return window.UiDialog.alert(String(msg), { title: title || 'Gestión de stock' });
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

  if (!window.Api) {
    document.addEventListener('DOMContentLoaded', function () {
      uiAlert('Cargue api.js antes de este módulo.', 'Gestión de stock');
    });
    return;
  }

  var tipos = [];
  var movs = [];
  var productos = [];

  function esc(s) {
    var d = document.createElement('div');
    d.textContent = s == null ? '' : String(s);
    return d.innerHTML;
  }

  function fmtFecha(iso) {
    if (!iso) return '—';
    var d = new Date(iso);
    if (isNaN(d.getTime())) return esc(iso);
    return d.toLocaleDateString('es-CR');
  }

  function renderTipos() {
    var tb = document.getElementById('gstTiposBody');
    if (!tb) return;
    tb.innerHTML = '';
    tipos.forEach(function (t) {
      var tr = document.createElement('tr');
      tr.innerHTML =
        '<td>' +
        t.id +
        '</td><td>' +
        esc(t.descripcion) +
        '</td><td class="text-end">' +
        '<button type="button" class="btn btn-outline-primary btn-sm me-1 gst-edit-tipo" data-id="' +
        t.id +
        '"><i class="bi bi-pencil"></i></button>' +
        '<button type="button" class="btn btn-outline-danger btn-sm gst-del-tipo" data-id="' +
        t.id +
        '"><i class="bi bi-trash"></i></button></td>';
      tb.appendChild(tr);
    });
    tb.querySelectorAll('.gst-edit-tipo').forEach(function (b) {
      b.addEventListener('click', function () {
        abrirTipo(parseInt(b.getAttribute('data-id'), 10));
      });
    });
    tb.querySelectorAll('.gst-del-tipo').forEach(function (b) {
      b.addEventListener('click', function () {
        eliminarTipo(parseInt(b.getAttribute('data-id'), 10));
      });
    });
  }

  function renderMovs() {
    var tb = document.getElementById('gstMovsBody');
    if (!tb) return;
    tb.innerHTML = '';
    movs.forEach(function (m) {
      var tr = document.createElement('tr');
      tr.innerHTML =
        '<td>' +
        m.id +
        '</td><td>' +
        fmtFecha(m.fechaGestion) +
        '</td><td>' +
        esc(m.nombreProducto) +
        '</td><td>' +
        m.cantidad +
        '</td><td>' +
        esc(m.tipoGestion) +
        '</td><td class="text-end">' +
        '<button type="button" class="btn btn-outline-primary btn-sm me-1 gst-edit-mov" data-id="' +
        m.id +
        '"><i class="bi bi-pencil"></i></button>' +
        '<button type="button" class="btn btn-outline-danger btn-sm gst-del-mov" data-id="' +
        m.id +
        '"><i class="bi bi-trash"></i></button></td>';
      tb.appendChild(tr);
    });
    tb.querySelectorAll('.gst-edit-mov').forEach(function (b) {
      b.addEventListener('click', function () {
        abrirMov(parseInt(b.getAttribute('data-id'), 10));
      });
    });
    tb.querySelectorAll('.gst-del-mov').forEach(function (b) {
      b.addEventListener('click', function () {
        eliminarMov(parseInt(b.getAttribute('data-id'), 10));
      });
    });
  }

  function fillProductosSel() {
    var sel = document.getElementById('gstMovProducto');
    if (!sel) return;
    var v = sel.value;
    sel.innerHTML = '';
    productos.forEach(function (p) {
      var o = document.createElement('option');
      o.value = String(p.id);
      o.textContent = '#' + p.id + ' — ' + p.nombre;
      sel.appendChild(o);
    });
    if (v) sel.value = v;
  }

  function fillTiposSel() {
    var sel = document.getElementById('gstMovTipo');
    if (!sel) return;
    var v = sel.value;
    sel.innerHTML = '';
    tipos.forEach(function (t) {
      var o = document.createElement('option');
      o.value = String(t.id);
      o.textContent = t.descripcion;
      sel.appendChild(o);
    });
    if (v) sel.value = v;
  }

  function reload() {
    return Promise.all([
      window.Api.get('/tipo_gestion_list.php'),
      window.Api.get('/gestion_stock_list.php'),
      window.Api.get('/productos_list.php'),
    ])
      .then(function (res) {
        tipos = res[0].data || [];
        movs = res[1].data || [];
        productos = res[2].data || [];
        renderTipos();
        renderMovs();
        fillProductosSel();
        fillTiposSel();
      })
      .catch(function (e) {
        uiAlert(e.message || 'Error al cargar datos');
      });
  }

  function abrirTipo(id) {
    document.getElementById('gstTipoId').value = id || '';
    var t = id ? tipos.find(function (x) { return x.id === id; }) : null;
    document.getElementById('gstTipoDesc').value = t ? t.descripcion : '';
    new bootstrap.Modal(document.getElementById('gstModalTipo')).show();
  }

  function guardarTipo() {
    var idVal = document.getElementById('gstTipoId').value;
    var id = idVal ? parseInt(idVal, 10) : null;
    var desc = document.getElementById('gstTipoDesc').value;
    var Vt = window.HamiltonValidation;
    var errT = Vt && Vt.textoLibreMensaje ? Vt.textoLibreMensaje(desc, 200, false, 'Descripción') : null;
    if (errT) {
      uiAlert(errT);
      return;
    }
    desc = desc.trim();
    var body = id ? { action: 'update', id: id, descripcion: desc } : { action: 'insert', descripcion: desc };
    window.Api.post('/tipo_gestion_save.php', body).then(function () {
      bootstrap.Modal.getInstance(document.getElementById('gstModalTipo')).hide();
      return reload();
    }).catch(function (e) {
      uiAlert(e.message || 'Error');
    });
  }

  function eliminarTipo(id) {
    uiConfirm('¿Eliminar este tipo? Puede fallar si hay movimientos asociados.', 'Eliminar').then(function (ok) {
      if (!ok) return;
      window.Api.post('/tipo_gestion_save.php', { action: 'delete', id: id }).then(function () {
        return reload();
      }).catch(function (e) {
        uiAlert(e.message || 'Error');
      });
    });
  }

  function pad2(n) {
    return n < 10 ? '0' + n : String(n);
  }

  function isoDateOnly(d) {
    return d.getFullYear() + '-' + pad2(d.getMonth() + 1) + '-' + pad2(d.getDate());
  }

  function abrirMov(id) {
    fillProductosSel();
    fillTiposSel();
    document.getElementById('gstMovId').value = id || '';
    if (id) {
      var m = movs.find(function (x) { return x.id === id; });
      if (m) {
        document.getElementById('gstMovProducto').value = String(m.idProducto);
        document.getElementById('gstMovTipo').value = String(m.idTipoGestion);
        document.getElementById('gstMovCant').value = String(m.cantidad);
        if (m.fechaGestion) {
          var d = new Date(m.fechaGestion);
          document.getElementById('gstMovFecha').value = isNaN(d.getTime()) ? '' : isoDateOnly(d);
        } else document.getElementById('gstMovFecha').value = '';
      }
    } else {
      document.getElementById('gstMovCant').value = '';
      document.getElementById('gstMovFecha').value = isoDateOnly(new Date());
    }
    new bootstrap.Modal(document.getElementById('gstModalMov')).show();
  }

  function guardarMov() {
    var idVal = document.getElementById('gstMovId').value;
    var id = idVal ? parseInt(idVal, 10) : null;
    var idProd = parseInt(document.getElementById('gstMovProducto').value, 10);
    var idTipo = parseInt(document.getElementById('gstMovTipo').value, 10);
    var cant = parseInt(document.getElementById('gstMovCant').value, 10);
    var fecha = document.getElementById('gstMovFecha').value;
    if (!idProd || !idTipo || !fecha) {
      uiAlert('Complete producto, tipo y fecha.');
      return;
    }
    if (cant === 0 || isNaN(cant)) {
      uiAlert('La cantidad debe ser un entero distinto de cero.');
      return;
    }
    var Vf = window.HamiltonValidation;
    if (Vf && Vf.fechaYyyyMmDd && !Vf.fechaYyyyMmDd(fecha)) {
      uiAlert('Fecha inválida (use AAAA-MM-DD).');
      return;
    }
    var body = id
      ? {
          action: 'update',
          id: id,
          cantidad: cant,
          fechaGestion: fecha,
          idProducto: idProd,
          idTipoGestion: idTipo,
        }
      : {
          action: 'insert',
          cantidad: cant,
          fechaGestion: fecha,
          idProducto: idProd,
          idTipoGestion: idTipo,
        };
    window.Api.post('/gestion_stock_save.php', body).then(function () {
      bootstrap.Modal.getInstance(document.getElementById('gstModalMov')).hide();
      return reload();
    }).catch(function (e) {
      uiAlert(e.message || 'Error');
    });
  }

  function eliminarMov(id) {
    uiConfirm('¿Eliminar este movimiento?', 'Eliminar').then(function (ok) {
      if (!ok) return;
      window.Api.post('/gestion_stock_save.php', { action: 'delete', id: id }).then(function () {
        return reload();
      }).catch(function (e) {
        uiAlert(e.message || 'Error');
      });
    });
  }

  document.addEventListener('DOMContentLoaded', function () {
    if (!document.getElementById('gstTiposBody')) return;
    reload();
    document.getElementById('gstBtnNuevoTipo').addEventListener('click', function () {
      abrirTipo();
    });
    document.getElementById('gstBtnGuardarTipo').addEventListener('click', guardarTipo);
    document.getElementById('gstBtnNuevoMov').addEventListener('click', function () {
      abrirMov();
    });
    document.getElementById('gstBtnGuardarMov').addEventListener('click', guardarMov);
  });
})();
