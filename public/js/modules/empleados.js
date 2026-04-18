/**
 * empleados.js — CRUD empleados Oracle (pkg_empleados).
 */
(function () {
  'use strict';

  function uiAlert(msg, title) {
    if (window.UiDialog && window.UiDialog.alert) {
      return window.UiDialog.alert(String(msg), { title: title || 'Empleados' });
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

  var empleados = [];
  var estados = [];
  var modalInstance = null;

  function escapeHtml(s) {
    const div = document.createElement('div');
    div.textContent = s == null ? '' : String(s);
    return div.innerHTML;
  }

  function formatFechaDisplay(emp) {
    var iso = emp.fechaIngresoIso || emp.fechaIngreso;
    if (!iso) return '—';
    var d = new Date(iso);
    return isNaN(d.getTime()) ? String(iso).slice(0, 10) : d.toLocaleDateString('es-CR');
  }

  function llenarEstadosSelect() {
    const sel = document.getElementById('empleadoEstado');
    if (!sel) return;
    sel.innerHTML = '<option value="">-- Seleccionar --</option>';
    estados.forEach(function (e) {
      const opt = document.createElement('option');
      opt.value = String(e.id);
      opt.textContent = e.nombre;
      sel.appendChild(opt);
    });
  }

  function renderTable() {
    const tbody = document.getElementById('empleadosBody');
    const empty = document.getElementById('empleadosEmpty');
    const count = document.getElementById('empleadosCount');
    if (!tbody || !empty || !count) return;

    tbody.innerHTML = '';
    count.textContent = empleados.length + ' empleado(s)';

    if (empleados.length === 0) {
      empty.style.display = 'block';
      return;
    }
    empty.style.display = 'none';

    empleados.forEach(function (e) {
      const tr = document.createElement('tr');
      const nombreCompleto = [e.nombre, e.apellido].filter(Boolean).join(' ');
      const badgeClass =
        String(e.estado || '').toUpperCase().indexOf('ACTIVO') !== -1 ? 'bg-success' : 'bg-secondary';
      tr.innerHTML =
        '<td>' +
        escapeHtml(nombreCompleto) +
        '</td><td>' +
        escapeHtml(e.puesto || '') +
        '</td><td>' +
        escapeHtml(e.email || '') +
        '</td><td>' +
        escapeHtml(formatFechaDisplay(e)) +
        '</td><td><span class="badge ' +
        badgeClass +
        '">' +
        escapeHtml(e.estado || '') +
        '</span></td><td class="text-end">' +
        '<button type="button" class="btn btn-outline-primary btn-sm me-1 btn-editar" data-id="' +
        e.id +
        '"><i class="bi bi-pencil"></i></button>' +
        '<button type="button" class="btn btn-outline-danger btn-sm btn-eliminar" data-id="' +
        e.id +
        '"><i class="bi bi-trash"></i></button>' +
        '</td>';
      tbody.appendChild(tr);
    });

    tbody.querySelectorAll('.btn-editar').forEach(function (b) {
      b.addEventListener('click', function () {
        abrirEditar(parseInt(b.getAttribute('data-id'), 10));
      });
    });
    tbody.querySelectorAll('.btn-eliminar').forEach(function (b) {
      b.addEventListener('click', function () {
        eliminar(parseInt(b.getAttribute('data-id'), 10));
      });
    });
  }

  function abrirNuevo() {
    document.getElementById('modalEmpleadoLabel').innerHTML =
      '<i class="bi bi-person-plus me-2"></i>Nuevo empleado';
    document.getElementById('formEmpleado').reset();
    document.getElementById('empleadoId').value = '';
    document.getElementById('empleadoFechaGrupo').classList.add('d-none');
    document.getElementById('empleadoFechaIngreso').value = '';
    llenarEstadosSelect();
    if (estados.length) {
      document.getElementById('empleadoEstado').value = String(estados[0].id);
    }
    modalInstance = new bootstrap.Modal(document.getElementById('modalEmpleado'));
    modalInstance.show();
  }

  function abrirEditar(id) {
    const emp = empleados.find(function (x) {
      return x.id === id;
    });
    if (!emp) return;

    document.getElementById('modalEmpleadoLabel').innerHTML =
      '<i class="bi bi-pencil me-2"></i>Editar empleado';
    document.getElementById('empleadoId').value = String(emp.id);
    document.getElementById('empleadoNombre').value = emp.nombre || '';
    document.getElementById('empleadoApellido').value = emp.apellido || '';
    document.getElementById('empleadoPuesto').value = emp.puesto || '';
    document.getElementById('empleadoEmail').value = emp.email || '';
    document.getElementById('empleadoFechaGrupo').classList.remove('d-none');
    document.getElementById('empleadoFechaIngreso').value = formatFechaDisplay(emp);
    llenarEstadosSelect();
    document.getElementById('empleadoEstado').value = String(emp.idEstado || '');

    modalInstance = new bootstrap.Modal(document.getElementById('modalEmpleado'));
    modalInstance.show();
  }

  function guardar() {
    if (!window.Api) {
      void uiAlert('API no disponible.', 'Error');
      return;
    }
    const id = document.getElementById('empleadoId').value
      ? parseInt(document.getElementById('empleadoId').value, 10)
      : null;
    const nombre = document.getElementById('empleadoNombre').value.trim();
    const apellido = document.getElementById('empleadoApellido').value.trim();
    const puesto = document.getElementById('empleadoPuesto').value.trim();
    const email = document.getElementById('empleadoEmail').value.trim();
    const estadosIdEstado = parseInt(document.getElementById('empleadoEstado').value, 10);

    if (!estadosIdEstado) {
      void uiAlert('Seleccione un estado.');
      return;
    }
    var V = window.HamiltonValidation;
    if (V && typeof V.empleadoFormMensaje === 'function') {
      var errE = V.empleadoFormMensaje(nombre, apellido, puesto, email);
      if (errE) {
        void uiAlert(errE);
        return;
      }
    } else if (!nombre || !apellido || !puesto || !email) {
      void uiAlert('Complete los campos obligatorios.');
      return;
    }

    const body = {
      nombre: nombre,
      apellido: apellido,
      puesto: puesto,
      email: email.toLowerCase(),
      idEstado: estadosIdEstado,
    };
    if (id) {
      body.action = 'update';
      body.id = id;
    } else {
      body.action = 'insert';
    }

    const btn = document.getElementById('btnGuardarEmpleado');
    if (btn) btn.disabled = true;

    window.Api
      .post('/empleados_save.php', body)
      .then(function () {
        if (modalInstance) modalInstance.hide();
        return loadEmpleados();
      })
      .then(function () {
        return uiAlert(
          body.action === 'insert' ? 'Empleado creado.' : 'Empleado actualizado.',
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

  function eliminar(id) {
    uiConfirm('¿Eliminar este empleado?', 'Eliminar empleado').then(function (ok) {
      if (!ok || !window.Api) return;
      window.Api
        .post('/empleados_save.php', { action: 'delete', id: id })
        .then(function () {
          return loadEmpleados();
        })
        .then(function () {
          return uiAlert('Empleado eliminado.', 'Listo');
        })
        .catch(function (e) {
          void uiAlert(String(e.message || e), 'Error');
        });
    });
  }

  function loadEmpleados() {
    const tbody = document.getElementById('empleadosBody');
    if (!tbody || !window.Api) return Promise.resolve();
    tbody.innerHTML =
      '<tr><td colspan="6" class="text-center py-4"><div class="spinner-border spinner-border-sm text-secondary" role="status"></div></td></tr>';

    return Promise.all([
      window.Api.get('/empleados_list.php'),
      window.Api.get('/estados_list.php').catch(function () {
        return { data: [] };
      }),
    ])
      .then(function (results) {
        empleados = results[0].data || [];
        estados = results[1].data || [];
        llenarEstadosSelect();
        renderTable();
      })
      .catch(function (e) {
        empleados = [];
        renderTable();
        void uiAlert('No se pudieron cargar empleados: ' + (e.message || ''), 'Error');
      });
  }

  document.addEventListener('DOMContentLoaded', function () {
    loadEmpleados();

    document.getElementById('btnNuevoEmpleado').addEventListener('click', abrirNuevo);
    document.getElementById('btnGuardarEmpleado').addEventListener('click', guardar);
  });
})();
