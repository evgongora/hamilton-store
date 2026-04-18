/**
 * ubicaciones.js — Provincias, cantones y distritos vía Oracle (pkg_provincias, pkg_cantones, pkg_distritos).
 */
(function () {
  'use strict';

  function uiAlert(msg, title) {
    if (window.UiDialog && window.UiDialog.alert) {
      return window.UiDialog.alert(String(msg), { title: title || 'Ubicaciones' });
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

  if (!window.Api || !window.Api.get || !window.Api.post) {
    document.addEventListener('DOMContentLoaded', function () {
      uiAlert('Falta cargar api.js (API_BASE).', 'Ubicaciones');
    });
    return;
  }

  var data = { provincias: [], cantones: [], distritos: [] };

  function escapeHtml(s) {
    var div = document.createElement('div');
    div.textContent = s == null ? '' : String(s);
    return div.innerHTML;
  }

  function renderAll() {
    renderProvincias();
    renderCantones();
    renderDistritos();
  }

  function renderProvincias() {
    var tbody = document.getElementById('provinciasBody');
    if (!tbody) return;
    tbody.innerHTML = '';
    data.provincias.forEach(function (p) {
      var tr = document.createElement('tr');
      tr.innerHTML =
        '<td>' +
        p.id +
        '</td><td>' +
        escapeHtml(p.nombre) +
        '</td><td class="text-end"><button type="button" class="btn btn-outline-primary btn-sm me-1 btn-edit-prov" data-id="' +
        p.id +
        '"><i class="bi bi-pencil"></i></button><button type="button" class="btn btn-outline-danger btn-sm btn-del-prov" data-id="' +
        p.id +
        '"><i class="bi bi-trash"></i></button></td>';
      tbody.appendChild(tr);
    });
    tbody.querySelectorAll('.btn-edit-prov').forEach(function (b) {
      b.addEventListener('click', function () {
        editarProvincia(parseInt(b.getAttribute('data-id'), 10));
      });
    });
    tbody.querySelectorAll('.btn-del-prov').forEach(function (b) {
      b.addEventListener('click', function () {
        eliminarProvincia(parseInt(b.getAttribute('data-id'), 10));
      });
    });
  }

  function renderCantones() {
    var tbody = document.getElementById('cantonesBody');
    if (!tbody) return;
    tbody.innerHTML = '';
    data.cantones.forEach(function (c) {
      var prov = data.provincias.find(function (p) {
        return p.id === c.provinciasIdProvincia;
      });
      var tr = document.createElement('tr');
      tr.innerHTML =
        '<td>' +
        c.id +
        '</td><td>' +
        escapeHtml(c.nombre) +
        '</td><td>' +
        escapeHtml(prov ? prov.nombre : '') +
        '</td><td class="text-end"><button type="button" class="btn btn-outline-primary btn-sm me-1 btn-edit-cant" data-id="' +
        c.id +
        '"><i class="bi bi-pencil"></i></button><button type="button" class="btn btn-outline-danger btn-sm btn-del-cant" data-id="' +
        c.id +
        '"><i class="bi bi-trash"></i></button></td>';
      tbody.appendChild(tr);
    });
    tbody.querySelectorAll('.btn-edit-cant').forEach(function (b) {
      b.addEventListener('click', function () {
        editarCanton(parseInt(b.getAttribute('data-id'), 10));
      });
    });
    tbody.querySelectorAll('.btn-del-cant').forEach(function (b) {
      b.addEventListener('click', function () {
        eliminarCanton(parseInt(b.getAttribute('data-id'), 10));
      });
    });
  }

  function renderDistritos() {
    var tbody = document.getElementById('distritosBody');
    if (!tbody) return;
    tbody.innerHTML = '';
    data.distritos.forEach(function (d) {
      var cant = data.cantones.find(function (c) {
        return c.id === d.cantonesIdCanton;
      });
      var tr = document.createElement('tr');
      tr.innerHTML =
        '<td>' +
        d.id +
        '</td><td>' +
        escapeHtml(d.nombre) +
        '</td><td>' +
        escapeHtml(cant ? cant.nombre : '') +
        '</td><td>' +
        escapeHtml(d.codigoPostal != null ? d.codigoPostal : '') +
        '</td><td class="text-end"><button type="button" class="btn btn-outline-primary btn-sm me-1 btn-edit-dist" data-id="' +
        d.id +
        '"><i class="bi bi-pencil"></i></button><button type="button" class="btn btn-outline-danger btn-sm btn-del-dist" data-id="' +
        d.id +
        '"><i class="bi bi-trash"></i></button></td>';
      tbody.appendChild(tr);
    });
    tbody.querySelectorAll('.btn-edit-dist').forEach(function (b) {
      b.addEventListener('click', function () {
        editarDistrito(parseInt(b.getAttribute('data-id'), 10));
      });
    });
    tbody.querySelectorAll('.btn-del-dist').forEach(function (b) {
      b.addEventListener('click', function () {
        eliminarDistrito(parseInt(b.getAttribute('data-id'), 10));
      });
    });
  }

  function reloadAll() {
    return Promise.all([
      window.Api.get('/provincias_list.php'),
      window.Api.get('/cantones_list.php'),
      window.Api.get('/distritos_list.php'),
    ])
      .then(function (results) {
        data.provincias = (results[0].data || []).map(function (r) {
          return { id: r.id, nombre: r.nombre };
        });
        data.cantones = (results[1].data || []).map(function (r) {
          return { id: r.id, nombre: r.nombre, provinciasIdProvincia: r.idProvincia };
        });
        data.distritos = (results[2].data || []).map(function (r) {
          return {
            id: r.id,
            nombre: r.nombre,
            cantonesIdCanton: r.idCanton,
            codigoPostal: r.codigoPostal,
          };
        });
        renderAll();
      })
      .catch(function (e) {
        return uiAlert(e.message || 'No se pudieron cargar las ubicaciones', 'Ubicaciones');
      });
  }

  function abrirProvincia(id) {
    document.getElementById('provinciaId').value = id || '';
    var pEdit = id
      ? data.provincias.find(function (p) {
          return p.id === id;
        })
      : null;
    document.getElementById('provinciaNombre').value = pEdit ? pEdit.nombre : '';
    new bootstrap.Modal(document.getElementById('modalProvincia')).show();
  }

  function guardarProvincia() {
    var idVal = document.getElementById('provinciaId').value;
    var id = idVal ? parseInt(idVal, 10) : null;
    var nombre = document.getElementById('provinciaNombre').value;
    var V = window.HamiltonValidation;
    var err =
      V && V.textoLibreMensaje ? V.textoLibreMensaje(nombre, 100, false, 'Nombre') : !nombre.trim() ? 'Indique el nombre.' : null;
    if (err) {
      void uiAlert(err);
      return;
    }
    nombre = nombre.trim();
    var payload = id ? { action: 'update', id: id, nombre: nombre } : { action: 'insert', nombre: nombre };
    window.Api.post('/provincias_save.php', payload).then(function () {
      bootstrap.Modal.getInstance(document.getElementById('modalProvincia')).hide();
      return reloadAll();
    }).catch(function (e) {
      uiAlert(e.message || 'Error al guardar');
    });
  }

  function editarProvincia(id) {
    abrirProvincia(id);
  }

  function eliminarProvincia(id) {
    uiConfirm(
      '¿Eliminar provincia? Puede fallar si existen cantones o datos asociados en Oracle.',
      'Eliminar provincia'
    ).then(function (ok) {
      if (!ok) return;
      window.Api.post('/provincias_save.php', { action: 'delete', id: id }).then(function () {
        return reloadAll();
      }).catch(function (e) {
        uiAlert(e.message || 'No se pudo eliminar');
      });
    });
  }

  function llenarProvinciasSelect(sel) {
    sel.innerHTML = '<option value="">-- Provincia --</option>';
    data.provincias.forEach(function (p) {
      var o = document.createElement('option');
      o.value = String(p.id);
      o.textContent = p.nombre;
      sel.appendChild(o);
    });
  }

  function llenarCantonesSelect(sel, provId) {
    sel.innerHTML = '<option value="">-- Cantón --</option>';
    data.cantones
      .filter(function (c) {
        return !provId || c.provinciasIdProvincia === provId;
      })
      .forEach(function (c) {
        var o = document.createElement('option');
        o.value = String(c.id);
        o.textContent = c.nombre;
        sel.appendChild(o);
      });
  }

  function abrirCanton(id) {
    llenarProvinciasSelect(document.getElementById('cantonProvincia'));
    document.getElementById('cantonId').value = id || '';
    if (id) {
      var c = data.cantones.find(function (x) {
        return x.id === id;
      });
      document.getElementById('cantonNombre').value = c ? c.nombre : '';
      document.getElementById('cantonProvincia').value = c ? String(c.provinciasIdProvincia) : '';
    } else document.getElementById('cantonNombre').value = '';
    new bootstrap.Modal(document.getElementById('modalCanton')).show();
  }

  function guardarCanton() {
    var idVal = document.getElementById('cantonId').value;
    var id = idVal ? parseInt(idVal, 10) : null;
    var nombre = document.getElementById('cantonNombre').value;
    var provId = parseInt(document.getElementById('cantonProvincia').value, 10);
    var Vc = window.HamiltonValidation;
    var errC =
      Vc && Vc.textoLibreMensaje ? Vc.textoLibreMensaje(nombre, 100, false, 'Nombre') : !nombre.trim() ? 'Indique el nombre.' : null;
    if (errC) {
      void uiAlert(errC);
      return;
    }
    nombre = nombre.trim();
    if (!provId) {
      void uiAlert('Seleccione una provincia.');
      return;
    }
    var payload = id
      ? { action: 'update', id: id, nombre: nombre, idProvincia: provId }
      : { action: 'insert', nombre: nombre, idProvincia: provId };
    window.Api.post('/cantones_save.php', payload).then(function () {
      bootstrap.Modal.getInstance(document.getElementById('modalCanton')).hide();
      return reloadAll();
    }).catch(function (e) {
      uiAlert(e.message || 'Error al guardar');
    });
  }

  function editarCanton(id) {
    abrirCanton(id);
  }

  function eliminarCanton(id) {
    uiConfirm('¿Eliminar cantón? Puede fallar si hay distritos u otras referencias.', 'Eliminar cantón').then(function (ok) {
      if (!ok) return;
      window.Api.post('/cantones_save.php', { action: 'delete', id: id }).then(function () {
        return reloadAll();
      }).catch(function (e) {
        uiAlert(e.message || 'No se pudo eliminar');
      });
    });
  }

  function abrirDistrito(id) {
    var cantonSel = document.getElementById('distritoCanton');
    llenarCantonesSelect(cantonSel);
    document.getElementById('distritoId').value = id || '';
    if (id) {
      var d = data.distritos.find(function (x) {
        return x.id === id;
      });
      document.getElementById('distritoNombre').value = d ? d.nombre : '';
      document.getElementById('distritoCanton').value = d ? String(d.cantonesIdCanton) : '';
      document.getElementById('distritoCodigoPostal').value =
        d && d.codigoPostal != null ? String(d.codigoPostal) : '';
    } else {
      document.getElementById('distritoNombre').value = '';
      document.getElementById('distritoCanton').value = '';
      document.getElementById('distritoCodigoPostal').value = '';
    }
    new bootstrap.Modal(document.getElementById('modalDistrito')).show();
  }

  function guardarDistrito() {
    var idVal = document.getElementById('distritoId').value;
    var id = idVal ? parseInt(idVal, 10) : null;
    var nombre = document.getElementById('distritoNombre').value;
    var cantonId = parseInt(document.getElementById('distritoCanton').value, 10);
    var cpEl = document.getElementById('distritoCodigoPostal').value;
    var codigoPostal = cpEl === '' ? null : parseInt(cpEl, 10);
    var Vd = window.HamiltonValidation;
    var errD =
      Vd && Vd.textoLibreMensaje ? Vd.textoLibreMensaje(nombre, 100, false, 'Nombre') : !nombre.trim() ? 'Indique el nombre.' : null;
    if (errD) {
      void uiAlert(errD);
      return;
    }
    nombre = nombre.trim();
    if (!cantonId) {
      void uiAlert('Seleccione un cantón.');
      return;
    }
    if (cpEl !== '' && (codigoPostal == null || isNaN(codigoPostal) || codigoPostal < 0)) {
      void uiAlert('Código postal inválido (deje en blanco o use un entero ≥ 0).');
      return;
    }
    var payload = id
      ? { action: 'update', id: id, nombre: nombre, idCanton: cantonId, codigoPostal: codigoPostal }
      : { action: 'insert', nombre: nombre, idCanton: cantonId, codigoPostal: codigoPostal };
    window.Api.post('/distritos_save.php', payload).then(function () {
      bootstrap.Modal.getInstance(document.getElementById('modalDistrito')).hide();
      return reloadAll();
    }).catch(function (e) {
      uiAlert(e.message || 'Error al guardar');
    });
  }

  function editarDistrito(id) {
    abrirDistrito(id);
  }

  function eliminarDistrito(id) {
    uiConfirm('¿Eliminar distrito?', 'Eliminar distrito').then(function (ok) {
      if (!ok) return;
      window.Api.post('/distritos_save.php', { action: 'delete', id: id }).then(function () {
        return reloadAll();
      }).catch(function (e) {
        uiAlert(e.message || 'No se pudo eliminar');
      });
    });
  }

  document.addEventListener('DOMContentLoaded', function () {
    if (!document.getElementById('provinciasBody')) return;

    reloadAll();

    document.getElementById('btnNuevaProvincia').addEventListener('click', function () {
      abrirProvincia();
    });
    document.getElementById('btnGuardarProvincia').addEventListener('click', guardarProvincia);
    document.getElementById('btnNuevoCanton').addEventListener('click', function () {
      abrirCanton();
    });
    document.getElementById('btnGuardarCanton').addEventListener('click', guardarCanton);
    document.getElementById('btnNuevoDistrito').addEventListener('click', function () {
      abrirDistrito();
    });
    document.getElementById('btnGuardarDistrito').addEventListener('click', guardarDistrito);
  });
})();
