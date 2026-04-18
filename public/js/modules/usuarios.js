/**
 * usuarios.js — CRUD usuarios Oracle (pkg_usuarios): personal (empleado) y cuentas cliente tienda.
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
  var clientes = [];
  var roles = [];
  var estados = [];
  var modalInstance = null;

  function escapeHtml(s) {
    const div = document.createElement('div');
    div.textContent = s == null ? '' : String(s);
    return div.innerHTML;
  }

  function esCuentaCliente(u) {
    if (!u || u.clientesIdCliente == null || u.clientesIdCliente === '') {
      return false;
    }
    const n = parseInt(String(u.clientesIdCliente), 10);
    return !isNaN(n) && n > 0;
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

  /**
   * @param {'todos'|'soloPersonal'} modo soloPersonal excluye rol CLIENTE (cuentas de empleado).
   */
  function llenarRolesSelect(modo) {
    modo = modo || 'todos';
    const soloPersonal = modo === 'soloPersonal';
    const sel = document.getElementById('usuarioRol');
    if (!sel) return;
    sel.innerHTML = '<option value="">-- Seleccionar --</option>';
    roles.forEach(function (r) {
      if (
        soloPersonal &&
        String(r.nombre || '')
          .toUpperCase()
          .trim() === 'CLIENTE'
      ) {
        return;
      }
      const opt = document.createElement('option');
      opt.value = String(r.id);
      opt.textContent = r.nombre;
      sel.appendChild(opt);
    });
  }

  function getRolClienteId() {
    const r = roles.find(function (x) {
      return (
        String(x.nombre || '')
          .toUpperCase()
          .trim() === 'CLIENTE'
      );
    });
    return r ? r.id : 0;
  }

  function llenarClientesNuevoSelect() {
    const sel = document.getElementById('usuarioClienteNuevo');
    if (!sel) return;
    sel.innerHTML = '<option value="">-- Seleccionar cliente --</option>';
    const usados = new Set();
    usuarios.forEach(function (u) {
      if (u.clientesIdCliente != null && u.clientesIdCliente !== '') {
        usados.add(u.clientesIdCliente);
      }
    });
    clientes.forEach(function (c) {
      if (usados.has(c.id)) return;
      const opt = document.createElement('option');
      opt.value = String(c.id);
      opt.textContent =
        [c.nombre, c.apellido].filter(Boolean).join(' ') + ' — ' + (c.email || '');
      sel.appendChild(opt);
    });
  }

  function syncNuevoUsuarioTipo() {
    const tipoEl = document.getElementById('usuarioNuevoTipo');
    if (!tipoEl) return;
    const esCliente = tipoEl.value === 'cliente';
    const wEmp = document.getElementById('wrapUsuarioEmpleado');
    const wCliSel = document.getElementById('wrapUsuarioClienteNuevoSelect');
    const rol = document.getElementById('usuarioRol');
    const emp = document.getElementById('usuarioEmpleado');
    const hintN = document.getElementById('usuarioRolClienteHint');
    const rolClienteId = getRolClienteId();

    if (esCliente) {
      if (rolClienteId <= 0) {
        void uiAlert('No hay rol CLIENTE en el catálogo. Revise la tabla roles en Oracle.');
        tipoEl.value = 'personal';
        syncNuevoUsuarioTipo();
        return;
      }
      llenarRolesSelect('todos');
      if (wEmp) wEmp.classList.add('d-none');
      if (emp) emp.removeAttribute('required');
      if (wCliSel) {
        wCliSel.classList.remove('d-none');
        llenarClientesNuevoSelect();
      }
      const csel = document.getElementById('usuarioClienteNuevo');
      if (csel) csel.setAttribute('required', 'required');
      if (rol) {
        rol.disabled = true;
        rol.value = String(rolClienteId);
      }
      if (hintN) hintN.classList.remove('d-none');
    } else {
      if (wEmp) wEmp.classList.remove('d-none');
      if (emp) emp.setAttribute('required', 'required');
      if (wCliSel) wCliSel.classList.add('d-none');
      const csel2 = document.getElementById('usuarioClienteNuevo');
      if (csel2) csel2.removeAttribute('required');
      llenarRolesSelect('soloPersonal');
      if (rol) {
        rol.disabled = false;
        if (roles.length) {
          const cajero = roles.find(function (r) {
            return String(r.nombre || '').toUpperCase().indexOf('CAJERO') !== -1;
          });
          const pick = cajero || roles[0];
          rol.value = String(pick.id);
        }
      }
      if (hintN) hintN.classList.add('d-none');
    }
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
      const esCli = esCuentaCliente(u);
      const tr = document.createElement('tr');
      const rolLabel = u.rolNombre || getRolNombre(u.rolesIdRol);
      var empCell;
      if (esCli) {
        empCell = u.clienteNombre
          ? escapeHtml(u.clienteNombre)
          : '<span class="text-muted">Cliente #' + escapeHtml(String(u.clientesIdCliente)) + '</span>';
      } else if (u.empleadoNombre) {
        empCell = escapeHtml(u.empleadoNombre);
      } else if (u.empleadosIdEmpleado) {
        empCell = '—';
      } else {
        empCell =
          '<span class="text-warning">Sin empleado — <span class="text-nowrap">edite para vincular</span></span>';
      }
      const estLabel = u.estadoNombre || getEstadoNombre(u.estadosIdEstado);
      const badgeEst =
        String(estLabel || '')
          .toUpperCase()
          .indexOf('ACTIVO') !== -1
          ? 'bg-success'
          : 'bg-secondary';
      const acciones =
        '<button type="button" class="btn btn-outline-primary btn-sm me-1 btn-editar" data-id="' +
        u.id +
        '" title="Editar"><i class="bi bi-pencil"></i></button>' +
        '<button type="button" class="btn btn-outline-danger btn-sm btn-eliminar" data-id="' +
        u.id +
        '" title="Eliminar"><i class="bi bi-trash"></i></button>';

      tr.innerHTML =
        '<td>' +
        escapeHtml(u.username) +
        '</td><td><span class="badge bg-dark">' +
        escapeHtml(rolLabel) +
        '</span></td><td>' +
        empCell +
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
    var pwdHelp = document.getElementById('pwdReq');
    if (pwdHelp) pwdHelp.textContent = 'Obligatoria para usuarios nuevos.';
    llenarEstadosSelect();
    llenarEmpleadosSelect(null, null);
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
    var wTipo = document.getElementById('wrapNuevoTipoCuenta');
    if (wTipo) wTipo.classList.remove('d-none');
    var tipoEl = document.getElementById('usuarioNuevoTipo');
    if (tipoEl) tipoEl.value = 'personal';
    var wCliInfo = document.getElementById('wrapUsuarioClienteInfo');
    if (wCliInfo) wCliInfo.classList.add('d-none');
    syncNuevoUsuarioTipo();
    modalInstance = new bootstrap.Modal(document.getElementById('modalUsuario'));
    modalInstance.show();
  }

  function abrirEditar(id) {
    const u = usuarios.find(function (x) {
      return x.id === id;
    });
    if (!u) {
      void uiAlert('Usuario no encontrado.');
      return;
    }

    const esCli = esCuentaCliente(u);

    var wTipoEd = document.getElementById('wrapNuevoTipoCuenta');
    var wCliNuevoEd = document.getElementById('wrapUsuarioClienteNuevoSelect');
    if (wTipoEd) wTipoEd.classList.add('d-none');
    if (wCliNuevoEd) wCliNuevoEd.classList.add('d-none');

    document.getElementById('modalUsuarioLabel').innerHTML = esCli
      ? '<i class="bi bi-pencil me-2"></i>Editar cuenta cliente'
      : '<i class="bi bi-pencil me-2"></i>Editar usuario';
    document.getElementById('usuarioId').value = String(u.id);
    document.getElementById('usuarioUsername').value = u.username || '';
    document.getElementById('usuarioPassword').value = '';
    document.getElementById('usuarioPassword').required = false;
    var pwdHelpE = document.getElementById('pwdReq');
    if (pwdHelpE) {
      pwdHelpE.style.display = '';
      pwdHelpE.textContent = 'Deje en blanco para mantener la contraseña actual.';
    }
    llenarRolesSelect(esCli ? 'todos' : 'soloPersonal');
    llenarEstadosSelect();
    document.getElementById('usuarioRol').value = String(u.rolesIdRol || '');
    document.getElementById('usuarioRol').disabled = esCli;
    document.getElementById('usuarioEstado').value = String(u.estadosIdEstado || '');

    var hintE = document.getElementById('usuarioRolClienteHint');
    if (hintE) hintE.classList.toggle('d-none', !esCli);

    var wCli = document.getElementById('wrapUsuarioClienteInfo');
    var wEmp = document.getElementById('wrapUsuarioEmpleado');
    var empSel = document.getElementById('usuarioEmpleado');
    if (esCli) {
      if (wCli) wCli.classList.remove('d-none');
      if (wEmp) wEmp.classList.add('d-none');
      var lid = document.getElementById('usuarioClienteIdLabel');
      var lnom = document.getElementById('usuarioClienteNombre');
      if (lid) lid.textContent = String(u.clientesIdCliente || '');
      if (lnom) lnom.textContent = u.clienteNombre || '—';
      if (empSel) empSel.removeAttribute('required');
    } else {
      if (wCli) wCli.classList.add('d-none');
      if (wEmp) wEmp.classList.remove('d-none');
      llenarEmpleadosSelect(u.empleadosIdEmpleado, u.id);
      if (empSel) {
        empSel.setAttribute('required', 'required');
        empSel.value = String(u.empleadosIdEmpleado || '');
      }
    }

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
    const uEdit = id
      ? usuarios.find(function (x) {
          return x.id === id;
        })
      : null;
    const esCliEdit = uEdit ? esCuentaCliente(uEdit) : false;

    const username = document.getElementById('usuarioUsername').value.trim().toLowerCase();
    const password = document.getElementById('usuarioPassword').value;
    const idRol = parseInt(document.getElementById('usuarioRol').value, 10);
    const idEstado = parseInt(document.getElementById('usuarioEstado').value, 10);
    const idEmpleado = parseInt(document.getElementById('usuarioEmpleado').value, 10);

    if (!username || !idEstado) {
      void uiAlert('Complete usuario y estado.');
      return;
    }

    var Vusr = window.HamiltonValidation;
    var errName =
      Vusr && typeof Vusr.usernameSistemaMensaje === 'function'
        ? Vusr.usernameSistemaMensaje(username)
        : null;
    if (!errName) {
      if (username.indexOf('\0') !== -1) {
        errName = 'Usuario: contiene caracteres no permitidos.';
      } else if (
        username.length < 3 ||
        username.length > 50 ||
        !/^[a-z0-9._-]+$/.test(username)
      ) {
        errName =
          'Usuario: entre 3 y 50 caracteres; solo letras, números, punto, guion y guion bajo.';
      }
    }
    if (errName) {
      void uiAlert(errName);
      return;
    }

    if (!id) {
      const tipoNuevo = document.getElementById('usuarioNuevoTipo');
      const esNuevoCliente = tipoNuevo && tipoNuevo.value === 'cliente';
      if (esNuevoCliente) {
        const idCliN = parseInt(
          document.getElementById('usuarioClienteNuevo').value,
          10
        );
        if (!idCliN) {
          void uiAlert('Seleccione el cliente al que vincular la cuenta.');
          return;
        }
        if (!password) {
          void uiAlert('La contraseña es obligatoria para usuarios nuevos.');
          return;
        }
      } else {
        if (!idRol || !idEmpleado) {
          void uiAlert('Complete rol y empleado.');
          return;
        }
        if (!password) {
          void uiAlert('La contraseña es obligatoria para usuarios nuevos.');
          return;
        }
      }
    } else if (!esCliEdit) {
      if (!idRol || !idEmpleado) {
        void uiAlert('Complete rol y empleado.');
        return;
      }
    }

    var errPwd =
      Vusr && typeof Vusr.passwordUsuarioMensaje === 'function'
        ? Vusr.passwordUsuarioMensaje(password, !id)
        : null;
    if (!errPwd && password !== '') {
      if (password.indexOf('\0') !== -1) {
        errPwd = 'La contraseña contiene caracteres no permitidos.';
      } else if (password.length < 8 || password.length > 100) {
        errPwd =
          'La contraseña debe tener entre 8 y 100 caracteres.';
      }
    } else if (!errPwd && !id && password === '') {
      errPwd = 'La contraseña es obligatoria.';
    }
    if (errPwd) {
      void uiAlert(errPwd);
      return;
    }

    var body;
    if (!id) {
      const tipoNuevo2 = document.getElementById('usuarioNuevoTipo');
      const esNuevoCliente2 = tipoNuevo2 && tipoNuevo2.value === 'cliente';
      if (esNuevoCliente2) {
        const idCliIns = parseInt(
          document.getElementById('usuarioClienteNuevo').value,
          10
        );
        body = {
          action: 'insert',
          username: username,
          password: password,
          idEstado: idEstado,
          idCliente: idCliIns,
        };
      } else {
        body = {
          action: 'insert',
          username: username,
          password: password,
          idRol: idRol,
          idEstado: idEstado,
          idEmpleado: idEmpleado,
        };
      }
    } else if (esCliEdit) {
      body = {
        action: 'update',
        id: id,
        username: username,
        idRol: uEdit.rolesIdRol,
        idEstado: idEstado,
        idCliente: uEdit.clientesIdCliente,
      };
      if (password) {
        body.password = password;
      }
    } else {
      body = {
        action: 'update',
        id: id,
        username: username,
        idRol: idRol,
        idEstado: idEstado,
        idEmpleado: idEmpleado,
      };
      if (password) {
        body.password = password;
      }
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
    const msg = u && esCuentaCliente(u)
      ? '¿Eliminar esta cuenta de cliente? La persona no podrá entrar a la tienda.'
      : '¿Eliminar este usuario del sistema?';
    uiConfirm(msg, 'Eliminar usuario').then(function (ok) {
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
      window.Api.get('/clientes_list.php').catch(function () {
        return { data: [] };
      }),
    ])
      .then(function (results) {
        usuarios = results[0].data || [];
        empleados = results[1].data || [];
        roles = results[2].data || [];
        estados = results[3].data || [];
        clientes = results[4].data || [];
        llenarRolesSelect('soloPersonal');
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
    var tipoNuevoEl = document.getElementById('usuarioNuevoTipo');
    if (tipoNuevoEl) {
      tipoNuevoEl.addEventListener('change', syncNuevoUsuarioTipo);
    }
  });
})();
