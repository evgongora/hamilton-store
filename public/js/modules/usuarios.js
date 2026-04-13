/**
 * usuarios.js — CRUD usuarios Oracle (pkg_usuarios), usuarios de empleado.
 */
(function () {
  'use strict';

  function uiAlert(msg, title) {
    if (window.UiDialog && window.UiDialog.alert) {
      return window.UiDialog.alert(String(msg), { title: title || 'Usuarios' });
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

  var usuarios = [];
  var empleados = [];
  var roles = [];
  var estados = [];
  var modalInstance = null;

  function escapeHtml(s) {
    const div = document.createElement('div');
    div.textContent = s == null ? '' : String(s);
    return div.innerHTML;
  }

  function getRolNombre(rolId) {
    const r = roles.find(function (x) {
      return x.id === rolId;
    });
    return r ? r.nombre : '—';
  }

  function getEstadoNombre(estadoId) {
    const e = estados.find(function (x) {
      return x.id === estadoId;
    });
    return e ? e.nombre : '—';
  }

  function llenarRolesSelect() {
    const sel = document.getElementById('usuarioRol');
    if (!sel) return;
    sel.innerHTML = '<option value="">-- Seleccionar --</option>';
    roles.forEach(function (r) {
      const opt = document.createElement('option');
      opt.value = String(r.id);
      opt.textContent = r.nombre;
      sel.appendChild(opt);
    });
  }

  function llenarEstadosSelect() {
    const sel = document.getElementById('usuarioEstado');
    if (!sel) return;
    sel.innerHTML = '<option value="">-- Seleccionar --</option>';
    estados.forEach(function (e) {
      const opt = document.createElement('option');
      opt.value = String(e.id);
      opt.textContent = e.nombre;
      sel.appendChild(opt);
    });
  }

  function llenarEmpleadosSelect(editingEmpId, editingUserId) {
    const sel = document.getElementById('usuarioEmpleado');
    if (!sel) return;

    sel.innerHTML = '<option value="">-- Seleccionar empleado --</option>';
    const empConOtroUsuario = new Set();
    usuarios.forEach(function (u) {
      if (u.id === editingUserId) return;
      if (u.empleadosIdEmpleado != null) {
        empConOtroUsuario.add(u.empleadosIdEmpleado);
      }
    });

    empleados.forEach(function (e) {
      const disponible = !empConOtroUsuario.has(e.id) || e.id === editingEmpId;
      if (!disponible) return;
      const opt = document.createElement('option');
      opt.value = String(e.id);
      opt.textContent = [e.nombre, e.apellido].filter(Boolean).join(' ');
      sel.appendChild(opt);
    });
  }

  function renderTable() {
    const tbody = document.getElementById('usuariosBody');
    const empty = document.getElementById('usuariosEmpty');
    const count = document.getElementById('usuariosCount');
    if (!tbody || !empty || !count) return;

    tbody.innerHTML = '';
    count.textContent = usuarios.length + ' usuario(s)';

    if (usuarios.length === 0) {
      empty.style.display = 'block';
      return;
    }
    empty.style.display = 'none';

    usuarios.forEach(function (u) {
      const staff = u.esUsuarioEmpleado === true;
      const tr = document.createElement('tr');
      const rolLabel = u.rolNombre || getRolNombre(u.rolesIdRol);
      const empLabel = staff
        ? u.empleadoNombre || '—'
        : '<span class="text-muted">(cliente tienda)</span>';
      const estLabel = u.estadoNombre || getEstadoNombre(u.estadosIdEstado);
      const badgeEst =
        String(estLabel || '')
          .toUpperCase()
          .indexOf('ACTIVO') !== -1
          ? 'bg-success'
          : 'bg-secondary';
      const acciones = staff
        ? '<button type="button" class="btn btn-outline-primary btn-sm me-1 btn-editar" data-id="' +
          u.id +
          '" title="Editar"><i class="bi bi-pencil"></i></button>' +
          '<button type="button" class="btn btn-outline-danger btn-sm btn-eliminar" data-id="' +
          u.id +
          '" title="Eliminar"><i class="bi bi-trash"></i></button>'
        : '<span class="text-muted small">Solo lectura</span>';

      tr.innerHTML =
        '<td>' +
        escapeHtml(u.username) +
        '</td><td><span class="badge bg-dark">' +
        escapeHtml(rolLabel) +
        '</span></td><td>' +
        (staff ? escapeHtml(empLabel) : empLabel) +
        '</td><td><span class="badge ' +
        badgeEst +
        '">' +
        escapeHtml(estLabel) +
        '</span></td><td class="text-end">' +
        acciones +
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
    document.getElementById('modalUsuarioLabel').innerHTML =
      '<i class="bi bi-person-plus me-2"></i>Nuevo usuario';
    document.getElementById('formUsuario').reset();
    document.getElementById('usuarioId').value = '';
    document.getElementById('usuarioPassword').required = true;
    document.getElementById('pwdReq').style.display = '';
    llenarRolesSelect();
    llenarEstadosSelect();
    llenarEmpleadosSelect(null, null);
    if (roles.length) {
      const cajero = roles.find(function (r) {
        return String(r.nombre || '')
          .toUpperCase()
          .indexOf('CAJERO') !== -1;
      });
      document.getElementById('usuarioRol').value = String(
        (cajero || roles[0]).id
      );
    }
    const activo = estados.find(function (e) {
      return String(e.nombre || '')
        .toUpperCase()
        .indexOf('ACTIVO') !== -1;
    });
    if (activo) {
      document.getElementById('usuarioEstado').value = String(activo.id);
    } else if (estados.length) {
      document.getElementById('usuarioEstado').value = String(estados[0].id);
    }
    modalInstance = new bootstrap.Modal(document.getElementById('modalUsuario'));
    modalInstance.show();
  }

  function abrirEditar(id) {
    const u = usuarios.find(function (x) {
      return x.id === id;
    });
    if (!u || !u.esUsuarioEmpleado) {
      void uiAlert('Este usuario no se puede editar desde esta pantalla.');
      return;
    }

    document.getElementById('modalUsuarioLabel').innerHTML =
      '<i class="bi bi-pencil me-2"></i>Editar usuario';
    document.getElementById('usuarioId').value = String(u.id);
    document.getElementById('usuarioUsername').value = u.username || '';
    document.getElementById('usuarioPassword').value = '';
    document.getElementById('usuarioPassword').required = false;
    document.getElementById('pwdReq').style.display = 'none';
    llenarRolesSelect();
    llenarEstadosSelect();
    llenarEmpleadosSelect(u.empleadosIdEmpleado, u.id);
    document.getElementById('usuarioRol').value = String(u.rolesIdRol || '');
    document.getElementById('usuarioEstado').value = String(u.estadosIdEstado || '');
    document.getElementById('usuarioEmpleado').value = String(u.empleadosIdEmpleado || '');

    modalInstance = new bootstrap.Modal(document.getElementById('modalUsuario'));
    modalInstance.show();
  }

  function guardar() {
    if (!window.Api) {
      void uiAlert('API no disponible.', 'Error');
      return;
    }
    const idVal = document.getElementById('usuarioId').value;
    const id = idVal ? parseInt(idVal, 10) : null;
    const username = document.getElementById('usuarioUsername').value.trim().toLowerCase();
    const password = document.getElementById('usuarioPassword').value;
    const idRol = parseInt(document.getElementById('usuarioRol').value, 10);
    const idEstado = parseInt(document.getElementById('usuarioEstado').value, 10);
    const idEmpleado = parseInt(document.getElementById('usuarioEmpleado').value, 10);

    if (!username || !idRol || !idEstado || !idEmpleado) {
      void uiAlert('Complete usuario, rol, estado y empleado.');
      return;
    }

    if (!id && !password) {
      void uiAlert('La contraseña es obligatoria para usuarios nuevos.');
      return;
    }

    const body = {
      username: username,
      idRol: idRol,
      idEstado: idEstado,
      idEmpleado: idEmpleado,
    };
    if (password) {
      body.password = password;
    }

    if (id) {
      body.action = 'update';
      body.id = id;
    } else {
      body.action = 'insert';
      body.password = password;
    }

    const btn = document.getElementById('btnGuardarUsuario');
    if (btn) btn.disabled = true;

    window.Api
      .post('/usuarios_save.php', body)
      .then(function () {
        if (modalInstance) modalInstance.hide();
        return loadUsuarios();
      })
      .then(function () {
        return uiAlert(
          body.action === 'insert' ? 'Usuario creado.' : 'Usuario actualizado.',
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
    const u = usuarios.find(function (x) {
      return x.id === id;
    });
    if (!u || !u.esUsuarioEmpleado) {
      void uiAlert('Este usuario no se puede eliminar desde esta pantalla.');
      return;
    }
    uiConfirm('¿Eliminar este usuario?', 'Eliminar usuario').then(function (ok) {
      if (!ok || !window.Api) return;
      window.Api
        .post('/usuarios_save.php', { action: 'delete', id: id })
        .then(function () {
          return loadUsuarios();
        })
        .then(function () {
          return uiAlert('Usuario eliminado.', 'Listo');
        })
        .catch(function (e) {
          void uiAlert(String(e.message || e), 'Error');
        });
    });
  }

  function loadUsuarios() {
    const tbody = document.getElementById('usuariosBody');
    if (!tbody || !window.Api) return Promise.resolve();
    tbody.innerHTML =
      '<tr><td colspan="5" class="text-center py-4"><div class="spinner-border spinner-border-sm text-secondary" role="status"></div></td></tr>';

    return Promise.all([
      window.Api.get('/usuarios_list.php'),
      window.Api.get('/empleados_list.php'),
      window.Api.get('/roles_list.php').catch(function () {
        return { data: [] };
      }),
      window.Api.get('/estados_list.php').catch(function () {
        return { data: [] };
      }),
    ])
      .then(function (results) {
        usuarios = results[0].data || [];
        empleados = results[1].data || [];
        roles = results[2].data || [];
        estados = results[3].data || [];
        llenarRolesSelect();
        llenarEstadosSelect();
        renderTable();
      })
      .catch(function (e) {
        usuarios = [];
        renderTable();
        void uiAlert('No se pudieron cargar los usuarios: ' + (e.message || ''), 'Error');
      });
  }

  document.addEventListener('DOMContentLoaded', function () {
    loadUsuarios();

    document.getElementById('btnNuevoUsuario').addEventListener('click', abrirNuevo);
    document.getElementById('btnGuardarUsuario').addEventListener('click', guardar);
  });
})();
