/**
 * usuarios.js - CRUD usuarios (vinculados a empleados)
 * localStorage hamilton_usuarios, seed desde usuarios.json
 */
(function () {
  'use strict';

  const STORAGE_KEY = 'hamilton_usuarios';
  const STORAGE_EMPLEADOS = 'hamilton_empleados';
  const basePath = (document.body.dataset.basePath || '/hamilton-store/public').replace(/\/$/, '');

  let roles = [];
  let estados = [];
  let modalInstance = null;

  function fetchJson(path) {
    return fetch(basePath + path).then(r => (r.ok ? r.json() : Promise.reject()));
  }

  function getEmpleados() {
    try {
      return JSON.parse(localStorage.getItem(STORAGE_EMPLEADOS) || '[]');
    } catch (e) { return []; }
  }

  function getUsuarios() {
    try {
      return JSON.parse(localStorage.getItem(STORAGE_KEY) || '[]');
    } catch (e) { return []; }
  }

  function saveUsuarios(arr) {
    localStorage.setItem(STORAGE_KEY, JSON.stringify(arr));
    renderTable();
  }

  function seed() {
    if (getUsuarios().length > 0) return Promise.resolve();
    return fetchJson('/js/mocks/usuarios.json').then(data => {
      saveUsuarios(data);
      return data;
    }).catch(() => []);
  }

  function escapeHtml(s) {
    const div = document.createElement('div');
    div.textContent = s == null ? '' : String(s);
    return div.innerHTML;
  }

  function getRolNombre(rolId) {
    const r = roles.find(x => x.id === rolId);
    return r ? r.nombre : '—';
  }

  function getEstadoNombre(estadoId) {
    const e = estados.find(x => x.id === estadoId);
    return e ? e.nombre : (estadoId === 1 ? 'activo' : estadoId === 2 ? 'inactivo' : '—');
  }

  function llenarRolesSelect() {
    const sel = document.getElementById('usuarioRol');
    sel.innerHTML = '<option value="">-- Seleccionar --</option>';
    roles.filter(r => r.id <= 3).forEach(r => {
      const opt = document.createElement('option');
      opt.value = r.id;
      opt.textContent = r.nombre;
      sel.appendChild(opt);
    });
  }

  function llenarEstadosSelect() {
    const sel = document.getElementById('usuarioEstado');
    sel.innerHTML = '<option value="">-- Seleccionar --</option>';
    estados.forEach(e => {
      const opt = document.createElement('option');
      opt.value = e.id;
      opt.textContent = e.nombre;
      sel.appendChild(opt);
    });
  }

  function getEmpleadoNombre(empId) {
    const emp = getEmpleados().find(e => e.id === empId);
    return emp ? [emp.nombre, emp.apellido].filter(Boolean).join(' ') : '—';
  }

  function renderTable() {
    const usr = getUsuarios();
    const emp = getEmpleados();
    const tbody = document.getElementById('usuariosBody');
    const empty = document.getElementById('usuariosEmpty');
    const count = document.getElementById('usuariosCount');

    tbody.innerHTML = '';
    count.textContent = usr.length + ' usuario(s)';

    if (usr.length === 0) {
      empty.style.display = 'block';
      return;
    }
    empty.style.display = 'none';

    usr.forEach(u => {
      const tr = document.createElement('tr');
      tr.innerHTML = `
        <td>${escapeHtml(u.username)}</td>
        <td><span class="badge bg-dark">${escapeHtml(getRolNombre(u.rolesIdRol) || u.rol)}</span></td>
        <td>${escapeHtml(getEmpleadoNombre(u.empleadosIdEmpleado))}</td>
        <td><span class="badge ${(u.estadosIdEstado === 1 || u.estado === 'activo') ? 'bg-success' : 'bg-secondary'}">${escapeHtml(getEstadoNombre(u.estadosIdEstado) || u.estado)}</span></td>
        <td class="text-end">
          <button type="button" class="btn btn-outline-primary btn-sm me-1 btn-editar" data-id="${u.id}" title="Editar"><i class="bi bi-pencil"></i></button>
          <button type="button" class="btn btn-outline-danger btn-sm btn-eliminar" data-id="${u.id}" title="Eliminar"><i class="bi bi-trash"></i></button>
        </td>
      `;
      tbody.appendChild(tr);
    });

    tbody.querySelectorAll('.btn-editar').forEach(b => b.addEventListener('click', () => abrirEditar(parseInt(b.dataset.id, 10))));
    tbody.querySelectorAll('.btn-eliminar').forEach(b => b.addEventListener('click', () => eliminar(parseInt(b.dataset.id, 10))));
  }

  function llenarEmpleadosSelect(editingEmpId) {
    const sel = document.getElementById('usuarioEmpleado');
    const usuarios = getUsuarios();
    const empleados = getEmpleados();

    sel.innerHTML = '<option value="">-- Seleccionar empleado --</option>';
    const empConUsuario = new Set(usuarios.map(u => u.empleadosIdEmpleado));

    empleados.forEach(e => {
      const disponible = !empConUsuario.has(e.id) || e.id === editingEmpId;
      if (!disponible) return;
      const opt = document.createElement('option');
      opt.value = e.id;
      opt.textContent = [e.nombre, e.apellido].filter(Boolean).join(' ');
      sel.appendChild(opt);
    });
  }

  function abrirNuevo() {
    document.getElementById('modalUsuarioLabel').innerHTML = '<i class="bi bi-person-plus me-2"></i>Nuevo usuario';
    document.getElementById('formUsuario').reset();
    document.getElementById('usuarioId').value = '';
    document.getElementById('usuarioPassword').required = true;
    document.getElementById('pwdReq').style.display = '';
    llenarRolesSelect();
    llenarEstadosSelect();
    llenarEmpleadosSelect();
    document.getElementById('usuarioRol').value = 2;
    document.getElementById('usuarioEstado').value = 1;
    modalInstance = new bootstrap.Modal(document.getElementById('modalUsuario'));
    modalInstance.show();
  }

  function abrirEditar(id) {
    const u = getUsuarios().find(x => x.id === id);
    if (!u) return;

    document.getElementById('modalUsuarioLabel').innerHTML = '<i class="bi bi-pencil me-2"></i>Editar usuario';
    document.getElementById('usuarioId').value = u.id;
    document.getElementById('usuarioUsername').value = u.username;
    document.getElementById('usuarioPassword').value = '';
    document.getElementById('usuarioPassword').required = false;
    document.getElementById('pwdReq').style.display = 'none';
    llenarRolesSelect();
    llenarEstadosSelect();
    document.getElementById('usuarioRol').value = u.rolesIdRol || (u.rol === 'admin' ? 1 : u.rol === 'inventario' ? 3 : 2);
    document.getElementById('usuarioEstado').value = u.estadosIdEstado || (u.estado === 'inactivo' ? 2 : 1);
    llenarEmpleadosSelect(u.empleadosIdEmpleado);
    document.getElementById('usuarioEmpleado').value = u.empleadosIdEmpleado;

    modalInstance = new bootstrap.Modal(document.getElementById('modalUsuario'));
    modalInstance.show();
  }

  function guardar() {
    const id = document.getElementById('usuarioId').value ? parseInt(document.getElementById('usuarioId').value, 10) : null;
    const username = document.getElementById('usuarioUsername').value.trim().toLowerCase();
    const password = document.getElementById('usuarioPassword').value;
    const rolesIdRol = parseInt(document.getElementById('usuarioRol').value, 10) || 2;
    const estadosIdEstado = parseInt(document.getElementById('usuarioEstado').value, 10) || 1;
    const empId = parseInt(document.getElementById('usuarioEmpleado').value, 10);

    if (!username || !empId) {
      alert('Complete usuario y empleado.');
      return;
    }

    if (!id && !password) {
      alert('La contraseña es obligatoria para nuevos usuarios.');
      return;
    }

    const arr = getUsuarios();
    const otroConMismoUser = arr.find(x => x.username === username && x.id !== id);
    if (otroConMismoUser) {
      alert('Ya existe un usuario con ese nombre.');
      return;
    }

    const otroConMismoEmp = arr.find(x => x.empleadosIdEmpleado === empId && x.id !== id);
    if (otroConMismoEmp) {
      alert('Ese empleado ya tiene un usuario asignado.');
      return;
    }

    if (id) {
      const idx = arr.findIndex(x => x.id === id);
      if (idx >= 0) {
        arr[idx] = { ...arr[idx], username, rolesIdRol, estadosIdEstado, empleadosIdEmpleado: empId };
        if (password) arr[idx].passwordEncriptado = password;
        saveUsuarios(arr);
        modalInstance.hide();
        return;
      }
    }

    const maxId = Math.max(0, ...arr.map(x => x.id || 0)) + 1;
    arr.push({
      id: maxId,
      username,
      passwordEncriptado: password || 'temp',
      rolesIdRol,
      estadosIdEstado,
      empleadosIdEmpleado: empId
    });
    saveUsuarios(arr);
    modalInstance.hide();
  }

  function eliminar(id) {
    if (!confirm('¿Eliminar este usuario?')) return;
    const arr = getUsuarios().filter(x => x.id !== id);
    saveUsuarios(arr);
  }

  document.addEventListener('DOMContentLoaded', function () {
    Promise.all([
      fetchJson('/js/mocks/roles.json').then(data => { roles = data; }),
      fetchJson('/js/mocks/estados.json').then(data => { estados = data; })
    ]).catch(() => {
      roles = [{id:1,nombre:'admin'},{id:2,nombre:'cajero'},{id:3,nombre:'inventario'},{id:4,nombre:'cliente'}];
      estados = [{id:1,nombre:'activo'},{id:2,nombre:'inactivo'}];
    }).then(() => seed().then(() => renderTable()));

    document.getElementById('btnNuevoUsuario').addEventListener('click', abrirNuevo);
    document.getElementById('linkNuevoUsuario').addEventListener('click', function (e) {
      e.preventDefault();
      abrirNuevo();
    });
    document.getElementById('btnGuardarUsuario').addEventListener('click', guardar);
  });
})();
