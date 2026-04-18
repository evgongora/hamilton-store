/**
 * metodos_pago_admin.js — CRUD métodos de pago (pkg_metodos_pago).
 */
(function () {
  'use strict';

  function uiAlert(msg, title) {
    if (window.UiDialog && window.UiDialog.alert) {
      return window.UiDialog.alert(String(msg), { title: title || 'Métodos de pago' });
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

  if (!window.Api) return;

  var rows = [];

  function esc(s) {
    var d = document.createElement('div');
    d.textContent = s == null ? '' : String(s);
    return d.innerHTML;
  }

  function render() {
    var tb = document.getElementById('mpBody');
    if (!tb) return;
    tb.innerHTML = '';
    rows.forEach(function (r) {
      var tr = document.createElement('tr');
      tr.innerHTML =
        '<td>' +
        r.id +
        '</td><td>' +
        esc(r.nombre) +
        '</td><td class="text-end">' +
        '<button type="button" class="btn btn-outline-primary btn-sm me-1 mp-edit" data-id="' +
        r.id +
        '"><i class="bi bi-pencil"></i></button>' +
        '<button type="button" class="btn btn-outline-danger btn-sm mp-del" data-id="' +
        r.id +
        '"><i class="bi bi-trash"></i></button></td>';
      tb.appendChild(tr);
    });
    tb.querySelectorAll('.mp-edit').forEach(function (b) {
      b.addEventListener('click', function () {
        abrir(parseInt(b.getAttribute('data-id'), 10));
      });
    });
    tb.querySelectorAll('.mp-del').forEach(function (b) {
      b.addEventListener('click', function () {
        eliminar(parseInt(b.getAttribute('data-id'), 10));
      });
    });
  }

  function reload() {
    return window.Api.get('/metodos_pago_list.php').then(function (r) {
      rows = r.data || [];
      render();
    }).catch(function (e) {
      uiAlert(e.message || 'Error al cargar');
    });
  }

  function abrir(id) {
    document.getElementById('mpId').value = id || '';
    var row = id ? rows.find(function (x) { return x.id === id; }) : null;
    document.getElementById('mpNombre').value = row ? row.nombre : '';
    new bootstrap.Modal(document.getElementById('mpModal')).show();
  }

  function guardar() {
    var idVal = document.getElementById('mpId').value;
    var id = idVal ? parseInt(idVal, 10) : null;
    var nombre = document.getElementById('mpNombre').value;
    var V = window.HamiltonValidation;
    var errMp = V && V.textoLibreMensaje ? V.textoLibreMensaje(nombre, 100, false, 'Nombre') : null;
    if (!errMp && (!V || !V.nonEmptyText(nombre, 100))) {
      errMp = 'Indique un nombre (máx. 100 caracteres).';
    }
    if (errMp) {
      uiAlert(errMp);
      return;
    }
    nombre = nombre.trim();
    var body = id ? { action: 'update', id: id, nombre: nombre } : { action: 'insert', nombre: nombre };
    window.Api.post('/metodos_pago_save.php', body).then(function () {
      bootstrap.Modal.getInstance(document.getElementById('mpModal')).hide();
      return reload();
    }).catch(function (e) {
      uiAlert(e.message || 'Error');
    });
  }

  function eliminar(id) {
    uiConfirm('¿Eliminar este método? Puede fallar si hay pagos asociados.', 'Eliminar').then(function (ok) {
      if (!ok) return;
      window.Api.post('/metodos_pago_save.php', { action: 'delete', id: id }).then(function () {
        return reload();
      }).catch(function (e) {
        uiAlert(e.message || 'Error');
      });
    });
  }

  document.addEventListener('DOMContentLoaded', function () {
    if (!document.getElementById('mpBody')) return;
    reload();
    document.getElementById('mpBtnNuevo').addEventListener('click', function () {
      abrir();
    });
    document.getElementById('mpBtnGuardar').addEventListener('click', guardar);
  });
})();
