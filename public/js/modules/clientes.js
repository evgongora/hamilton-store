(function () {
  'use strict';

  var loadingHtml =
    '<div class="d-flex justify-content-center py-5">' +
    '<div class="spinner-border text-secondary" role="status">' +
    '<span class="visually-hidden">Cargando</span>' +
    '</div></div>';

  var clientes = [];
  var estadosCatalogo = [];
  var idEstadoActivo = 0;
  var idEstadoInactivo = 0;

  function esc(s) {
    var d = document.createElement('div');
    d.textContent = s == null ? '' : String(s);
    return d.innerHTML;
  }

  function uiAlert(msg, title) {
    if (window.UiDialog && window.UiDialog.alert) {
      return window.UiDialog.alert(String(msg), { title: title || 'Clientes' });
    }
    alert(msg);
    return Promise.resolve();
  }

  function findEstadoIdPorNombre(nombreUpper) {
    var n = String(nombreUpper || '').toUpperCase().trim();
    var i;
    for (i = 0; i < estadosCatalogo.length; i++) {
      if (String(estadosCatalogo[i].nombre || '').toUpperCase().trim() === n) {
        return estadosCatalogo[i].id;
      }
    }
    return 0;
  }

  function resolveEstadoIds() {
    idEstadoActivo = findEstadoIdPorNombre('ACTIVO');
    idEstadoInactivo = findEstadoIdPorNombre('INACTIVO');
  }

  function validateClienteForm(nombre, apellido, email) {
    var V = window.HamiltonValidation;
    if (!V) {
      return 'Cargue validation-helpers.js antes de clientes.js';
    }
    if (!V.clienteNombreOracle(nombre)) {
      return 'Nombre: solo letras A-Z y espacios (sin tildes ni ñ), como exige la base de datos.';
    }
    if (nombre.trim().length > 100) {
      return 'Nombre: máximo 100 caracteres.';
    }
    if (!V.clienteNombreOracle(apellido)) {
      return 'Apellido: solo letras A-Z y espacios (sin tildes ni ñ).';
    }
    if (apellido.trim().length > 100) {
      return 'Apellido: máximo 100 caracteres.';
    }
    if (!V.clienteEmailOracle(email)) {
      return 'Email con formato válido.';
    }
    if (email.trim().length > 200) {
      return 'Email: máximo 200 caracteres.';
    }
    return null;
  }

  function getFiltered() {
    var q = (document.getElementById('clientesBuscar') || { value: '' }).value.trim().toLowerCase();
    if (!q) return clientes;
    return clientes.filter(function (c) {
      var blob = [c.nombre, c.apellido, c.email].filter(Boolean).join(' ').toLowerCase();
      return blob.indexOf(q) !== -1;
    });
  }

  function badgeClass(estadoNombre) {
    var u = String(estadoNombre || '').toUpperCase();
    if (u === 'ACTIVO') return 'bg-success';
    if (u === 'INACTIVO') return 'bg-secondary';
    return 'bg-secondary';
  }

  function render() {
    var wrap = document.getElementById('clientesSistemaTable');
    if (!wrap || !window.Api) return;

    if (clientes.length === 0) {
      wrap.innerHTML = '<p class="text-muted">No hay clientes.</p>';
      return;
    }

    var rows = getFiltered();
    if (rows.length === 0) {
      wrap.innerHTML =
        '<p class="text-muted py-4 text-center">No hay clientes que coincidan con la búsqueda.</p>';
      return;
    }

    var thead =
      '<thead class="table-light"><tr><th>ID</th><th>Nombre</th><th>Apellido</th><th>Email</th><th>Estado</th><th class="text-end">Acciones</th></tr></thead>';
    var tbody = '<tbody>';
    rows.forEach(function (c) {
      var est = String(c.estado || '').toUpperCase();
      var btnToggle = '';
      if (idEstadoActivo && idEstadoInactivo && c.idEstado) {
        if (est === 'ACTIVO') {
          btnToggle =
            '<button type="button" class="btn btn-outline-warning btn-sm cli-desact" data-id="' +
            c.id +
            '" title="Desactivar">Desactivar</button>';
        } else if (est === 'INACTIVO') {
          btnToggle =
            '<button type="button" class="btn btn-outline-success btn-sm cli-activar" data-id="' +
            c.id +
            '" title="Activar">Activar</button>';
        }
      }
      tbody +=
        '<tr><td>' +
        esc(String(c.id)) +
        '</td><td>' +
        esc(c.nombre) +
        '</td><td>' +
        esc(c.apellido) +
        '</td><td>' +
        esc(c.email) +
        '</td><td><span class="badge ' +
        badgeClass(c.estado) +
        '">' +
        esc(c.estado || '') +
        '</span></td><td class="text-end text-nowrap">' +
        '<button type="button" class="btn btn-outline-primary btn-sm me-1 cli-edit" data-id="' +
        c.id +
        '">Editar</button>' +
        btnToggle +
        '</td></tr>';
    });
    tbody += '</tbody>';
    wrap.innerHTML =
      '<div class="table-responsive shadow-sm rounded border bg-white">' +
      '<table class="table table-hover table-sm mb-0">' +
      thead +
      tbody +
      '</table></div>';

    wrap.querySelectorAll('.cli-edit').forEach(function (b) {
      b.addEventListener('click', function () {
        abrirModalEditar(parseInt(b.getAttribute('data-id'), 10));
      });
    });
    wrap.querySelectorAll('.cli-desact').forEach(function (b) {
      b.addEventListener('click', function () {
        toggleActivo(parseInt(b.getAttribute('data-id'), 10), false);
      });
    });
    wrap.querySelectorAll('.cli-activar').forEach(function (b) {
      b.addEventListener('click', function () {
        toggleActivo(parseInt(b.getAttribute('data-id'), 10), true);
      });
    });
  }

  function showCliModal() {
    var el = document.getElementById('cliModal');
    if (!el) {
      void uiAlert('No se encontró el diálogo de cliente.');
      return;
    }
    var BS = window.bootstrap;
    if (!BS || typeof BS.Modal !== 'function' || typeof BS.Modal.getOrCreateInstance !== 'function') {
      void uiAlert('Bootstrap no está disponible. Recargue la página.');
      return;
    }
    BS.Modal.getOrCreateInstance(el).show();
  }

  /**
   * Limpia el formulario de alta. El botón "Nuevo cliente" abre el modal vía
   * data-bs-toggle (Bootstrap); aquí solo reaccionamos a show.bs.modal cuando
   * el disparador es ese botón (relatedTarget), para no depender solo de un click JS.
   */
  function prepModalNuevoCliente() {
    var tit = document.getElementById('cliModalTitulo');
    var fid = document.getElementById('cliFieldId');
    var nom = document.getElementById('cliNombre');
    var ape = document.getElementById('cliApellido');
    var em = document.getElementById('cliEmail');
    var wrapEst = document.getElementById('cliWrapEstado');
    if (!tit || !fid || !nom || !ape || !em || !wrapEst) {
      void uiAlert('Formulario de cliente incompleto en la página.');
      return;
    }
    tit.textContent = 'Nuevo cliente';
    fid.value = '';
    nom.value = '';
    ape.value = '';
    em.value = '';
    wrapEst.classList.add('d-none');
  }

  function fillEstadoSelect(sel, selectedId) {
    sel.innerHTML = '';
    estadosCatalogo.forEach(function (e) {
      var o = document.createElement('option');
      o.value = String(e.id);
      o.textContent = e.nombre;
      sel.appendChild(o);
    });
    if (selectedId) sel.value = String(selectedId);
  }

  function abrirModalEditar(id) {
    var c = clientes.find(function (x) {
      return x.id === id;
    });
    if (!c) return;
    document.getElementById('cliModalTitulo').textContent = 'Editar cliente';
    document.getElementById('cliFieldId').value = String(c.id);
    document.getElementById('cliNombre').value = c.nombre || '';
    document.getElementById('cliApellido').value = c.apellido || '';
    document.getElementById('cliEmail').value = c.email || '';
    document.getElementById('cliWrapEstado').classList.remove('d-none');
    fillEstadoSelect(document.getElementById('cliEstado'), c.idEstado);
    showCliModal();
  }

  function guardarModal() {
    var idVal = document.getElementById('cliFieldId').value;
    var id = idVal ? parseInt(idVal, 10) : null;
    var nombre = document.getElementById('cliNombre').value;
    var apellido = document.getElementById('cliApellido').value;
    var email = document.getElementById('cliEmail').value;
    var err = validateClienteForm(nombre, apellido, email);
    if (err) {
      uiAlert(err);
      return;
    }
    var payload;
    if (!id) {
      payload = { action: 'insert', nombre: nombre.trim(), apellido: apellido.trim(), email: email.trim() };
    } else {
      var idEst = parseInt(document.getElementById('cliEstado').value, 10);
      if (!idEst || idEst <= 0) {
        uiAlert('Seleccione un estado.');
        return;
      }
      payload = {
        action: 'update',
        id: id,
        nombre: nombre.trim(),
        apellido: apellido.trim(),
        email: email.trim(),
        idEstado: idEst,
      };
    }
    window.Api.post('/clientes_save.php', payload).then(function () {
      var el = document.getElementById('cliModal');
      var BS = window.bootstrap;
      var inst = el && BS && BS.Modal && BS.Modal.getInstance(el);
      if (inst) inst.hide();
      return load();
    }).catch(function (e) {
      uiAlert(e.message || 'Error al guardar');
    });
  }

  function toggleActivo(idCliente, activar) {
    var c = clientes.find(function (x) {
      return x.id === idCliente;
    });
    if (!c) return;
    var idTarget = activar ? idEstadoActivo : idEstadoInactivo;
    if (!idTarget) {
      uiAlert('No se encontraron estados ACTIVO/INACTIVO en catálogo.');
      return;
    }
    var err = validateClienteForm(c.nombre, c.apellido, c.email);
    if (err) {
      uiAlert(err);
      return;
    }
    window.Api.post('/clientes_save.php', {
      action: 'update',
      id: idCliente,
      nombre: c.nombre,
      apellido: c.apellido,
      email: c.email,
      idEstado: idTarget,
    }).then(function () {
      return load();
    }).catch(function (e) {
      uiAlert(e.message || 'Error');
    });
  }

  function load() {
    var wrap = document.getElementById('clientesSistemaTable');
    if (!wrap || !window.Api) return;

    wrap.innerHTML = loadingHtml;

    return Promise.all([window.Api.get('/clientes_list.php'), window.Api.get('/estados_list.php')])
      .then(function (results) {
        clientes = results[0].data || [];
        estadosCatalogo = results[1].data || [];
        resolveEstadoIds();
        render();
      })
      .catch(function (e) {
        wrap.innerHTML =
          '<div class="alert alert-danger">' +
          esc(e.message || 'No autorizado o error de conexión') +
          '</div>';
      });
  }

  function initClientesPage() {
    var buscar = document.getElementById('clientesBuscar');
    if (buscar) buscar.addEventListener('input', render);
    var btnGuardar = document.getElementById('cliBtnGuardar');
    if (btnGuardar) btnGuardar.addEventListener('click', guardarModal);

    var btnNuevo = document.getElementById('btnNuevoCliente');
    if (btnNuevo) {
      btnNuevo.addEventListener(
        'click',
        function () {
          prepModalNuevoCliente();
        },
        true
      );
    }

    load();
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initClientesPage);
  } else {
    initClientesPage();
  }
})();
