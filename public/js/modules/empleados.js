/**
 * empleados.js - CRUD empleados
 * localStorage hamilton_empleados, seed desde empleados.json
 */
(function () {
  'use strict';

  const STORAGE_KEY = 'hamilton_empleados';
  const basePath = (document.body.dataset.basePath || '/hamilton-store/public').replace(/\/$/, '');

  let estados = [];
  let modalInstance = null;

  function fetchJson(path) {
    return fetch(basePath + path).then(r => (r.ok ? r.json() : Promise.reject()));
  }

  function getEmpleados() {
    try {
      return JSON.parse(localStorage.getItem(STORAGE_KEY) || '[]');
    } catch (e) { return []; }
  }

  function saveEmpleados(arr) {
    localStorage.setItem(STORAGE_KEY, JSON.stringify(arr));
    renderTable();
  }

  function seed() {
    if (getEmpleados().length > 0) return Promise.resolve();
    return fetchJson('/js/mocks/empleados.json').then(data => {
      saveEmpleados(data);
      return data;
    }).catch(() => []);
  }

  function getEstadoNombre(estadoId) {
    const e = estados.find(x => x.id === estadoId);
    return e ? e.nombre : (estadoId === 1 ? 'activo' : estadoId === 2 ? 'inactivo' : '—');
  }

  function llenarEstadosSelect() {
    const sel = document.getElementById('empleadoEstado');
    sel.innerHTML = '<option value="">-- Seleccionar --</option>';
    estados.forEach(e => {
      const opt = document.createElement('option');
      opt.value = e.id;
      opt.textContent = e.nombre;
      sel.appendChild(opt);
    });
  }

  function escapeHtml(s) {
    const div = document.createElement('div');
    div.textContent = s == null ? '' : String(s);
    return div.innerHTML;
  }

  function renderTable() {
    const emp = getEmpleados();
    const tbody = document.getElementById('empleadosBody');
    const empty = document.getElementById('empleadosEmpty');
    const count = document.getElementById('empleadosCount');

    tbody.innerHTML = '';
    count.textContent = emp.length + ' empleado(s)';

    if (emp.length === 0) {
      empty.style.display = 'block';
      return;
    }
    empty.style.display = 'none';

    emp.forEach(e => {
      const tr = document.createElement('tr');
      const nombreCompleto = [e.nombre, e.apellido].filter(Boolean).join(' ');
      tr.innerHTML = `
        <td>${escapeHtml(nombreCompleto)}</td>
        <td>${escapeHtml(e.puesto)}</td>
        <td>${escapeHtml(e.email)}</td>
        <td>${escapeHtml((e.fechaIngreso || '').slice(0, 10))}</td>
        <td><span class="badge ${(e.estadosIdEstado === 1 || e.estado === 'activo') ? 'bg-success' : 'bg-secondary'}">${escapeHtml(getEstadoNombre(e.estadosIdEstado) || e.estado || 'activo')}</span></td>
        <td class="text-end">
          <button type="button" class="btn btn-outline-primary btn-sm me-1 btn-editar" data-id="${e.id}" title="Editar"><i class="bi bi-pencil"></i></button>
          <button type="button" class="btn btn-outline-danger btn-sm btn-eliminar" data-id="${e.id}" title="Eliminar"><i class="bi bi-trash"></i></button>
        </td>
      `;
      tbody.appendChild(tr);
    });

    tbody.querySelectorAll('.btn-editar').forEach(b => b.addEventListener('click', () => abrirEditar(parseInt(b.dataset.id, 10))));
    tbody.querySelectorAll('.btn-eliminar').forEach(b => b.addEventListener('click', () => eliminar(parseInt(b.dataset.id, 10))));
  }

  function abrirNuevo() {
    document.getElementById('modalEmpleadoLabel').innerHTML = '<i class="bi bi-person-plus me-2"></i>Nuevo empleado';
    document.getElementById('formEmpleado').reset();
    document.getElementById('empleadoId').value = '';
    document.getElementById('empleadoFechaIngreso').value = new Date().toISOString().slice(0, 10);
    document.getElementById('empleadoEstado').value = 1;
    modalInstance = new bootstrap.Modal(document.getElementById('modalEmpleado'));
    modalInstance.show();
  }

  function abrirEditar(id) {
    const emp = getEmpleados().find(e => e.id === id);
    if (!emp) return;

    document.getElementById('modalEmpleadoLabel').innerHTML = '<i class="bi bi-pencil me-2"></i>Editar empleado';
    document.getElementById('empleadoId').value = emp.id;
    document.getElementById('empleadoNombre').value = emp.nombre;
    document.getElementById('empleadoApellido').value = emp.apellido;
    document.getElementById('empleadoPuesto').value = emp.puesto;
    document.getElementById('empleadoEmail').value = emp.email;
    document.getElementById('empleadoFechaIngreso').value = (emp.fechaIngreso || '').slice(0, 10);
    document.getElementById('empleadoEstado').value = emp.estadosIdEstado || (emp.estado === 'inactivo' ? 2 : 1);

    modalInstance = new bootstrap.Modal(document.getElementById('modalEmpleado'));
    modalInstance.show();
  }

  function guardar() {
    const id = document.getElementById('empleadoId').value ? parseInt(document.getElementById('empleadoId').value, 10) : null;
    const nombre = document.getElementById('empleadoNombre').value.trim();
    const apellido = document.getElementById('empleadoApellido').value.trim();
    const puesto = document.getElementById('empleadoPuesto').value.trim();
    const email = document.getElementById('empleadoEmail').value.trim();
    const fechaIngreso = document.getElementById('empleadoFechaIngreso').value;
    const estadosIdEstado = parseInt(document.getElementById('empleadoEstado').value, 10) || 1;

    if (!nombre || !apellido || !puesto || !email || !fechaIngreso) {
      alert('Complete los campos obligatorios.');
      return;
    }

    const arr = getEmpleados();

    if (id) {
      const idx = arr.findIndex(e => e.id === id);
      if (idx >= 0) {
        arr[idx] = { ...arr[idx], nombre, apellido, puesto, email, fechaIngreso, estadosIdEstado };
        saveEmpleados(arr);
        modalInstance.hide();
        return;
      }
    }

    const maxId = Math.max(0, ...arr.map(e => e.id || 0)) + 1;
    arr.push({ id: maxId, nombre, apellido, puesto, email, fechaIngreso, estadosIdEstado });
    saveEmpleados(arr);
    modalInstance.hide();
  }

  function eliminar(id) {
    if (!confirm('¿Eliminar este empleado?')) return;
    const arr = getEmpleados().filter(e => e.id !== id);
    saveEmpleados(arr);
  }

  document.addEventListener('DOMContentLoaded', function () {
    fetchJson('/js/mocks/estados.json').then(data => { estados = data; llenarEstadosSelect(); }).catch(() => {
      estados = [{id:1,nombre:'activo'},{id:2,nombre:'inactivo'}];
      llenarEstadosSelect();
    });
    seed().then(() => renderTable());

    document.getElementById('btnNuevoEmpleado').addEventListener('click', abrirNuevo);
    document.getElementById('linkNuevoEmpleado').addEventListener('click', function (e) {
      e.preventDefault();
      abrirNuevo();
    });
    document.getElementById('btnGuardarEmpleado').addEventListener('click', guardar);
  });
})();
