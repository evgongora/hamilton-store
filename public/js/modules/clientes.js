/**
 * clientes.js - CRUD clientes con selects dependientes (provincia → cantón → distrito)
 * Mock: localStorage hamilton_clientes, seed desde clientes.json
 */
(function () {
  'use strict';

  const STORAGE_KEY = 'hamilton_clientes';
  const basePath = (document.body.dataset.basePath || '/hamilton-store/public').replace(/\/$/, '');
  const currentRole = String(document.body.dataset.currentRole || '').trim().toLowerCase();
  const isAdmin = currentRole === 'admin';

  let ubicaciones = { provincias: [], cantones: [], distritos: [] };
  let estados = [];
  let modalInstance = null;

  function fetchJson(path) {
    return fetch(basePath + path).then(r => {
      if (!r.ok) throw new Error('Error cargando ' + path);
      return r.json();
    });
  }

  function getClientes() {
    const stored = localStorage.getItem(STORAGE_KEY);
    if (stored) {
      try {
        return JSON.parse(stored);
      } catch (e) {}
    }
    return [];
  }

  function saveClientes(clientes) {
    localStorage.setItem(STORAGE_KEY, JSON.stringify(clientes));
    renderTable();
  }

  function loadUbicaciones() {
    const stored = localStorage.getItem('hamilton_ubicaciones');
    if (stored) {
      try {
        const parsed = JSON.parse(stored);
        if (parsed.provincias && parsed.provincias.length > 0) {
          ubicaciones = parsed;
          return Promise.resolve(ubicaciones);
        }
      } catch (e) {}
    }
    return fetchJson('/js/mocks/ubicaciones.json').then(data => {
      ubicaciones = data;
      return ubicaciones;
    }).catch(() => ubicaciones);
  }

  function seedFromJson() {
    const current = getClientes();
    if (current.length > 0) return Promise.resolve(current);
    return fetchJson('/js/mocks/clientes.json').then(data => {
      const withDireccion = data.map(c => ({
        ...c,
        direccion: c.direccion || null
      }));
      saveClientes(withDireccion);
      return withDireccion;
    }).catch(err => {
      console.error('Error cargando clientes.json:', err);
      return [];
    });
  }

  function escapeHtml(s) {
    if (s == null) return '';
    const div = document.createElement('div');
    div.textContent = String(s);
    return div.innerHTML;
  }

  function getEstadoNombre(estadoId) {
    const e = estados.find(x => x.id === estadoId);
    return e ? e.nombre : '—';
  }

  function getUbicacionTexto(cliente) {
    if (!cliente.direccion) return '—';
    const d = cliente.direccion;
    const prov = ubicaciones.provincias.find(p => p.id === d.provinciasIdProvincia);
    const cant = ubicaciones.cantones.find(c => c.id === d.cantonesIdCanton);
    const dist = ubicaciones.distritos.find(di => di.id === d.distritosIdDistrito);
    const parts = [prov?.nombre, cant?.nombre, dist?.nombre].filter(Boolean);
    return parts.length ? parts.join(', ') : '—';
  }

  function renderTable() {
    const clientes = getClientes();
    const tbody = document.getElementById('clientesBody');
    const empty = document.getElementById('clientesEmpty');
    const count = document.getElementById('clientesCount');

    tbody.innerHTML = '';
    count.textContent = clientes.length + ' cliente(s)';

    if (clientes.length === 0) {
      empty.style.display = 'block';
      return;
    }
    empty.style.display = 'none';

    clientes.forEach(c => {
      const tr = document.createElement('tr');
      const nombreCompleto = [c.nombre, c.apellido].filter(Boolean).join(' ');
      tr.innerHTML = `
        <td>${escapeHtml(nombreCompleto)}</td>
        <td>${escapeHtml(c.email)}</td>
        <td>${escapeHtml(c.telefono)}</td>
        <td class="small">${escapeHtml(getUbicacionTexto(c))}</td>
        <td><span class="badge ${c.estadosIdEstado === 1 ? 'bg-success' : 'bg-secondary'}">${escapeHtml(getEstadoNombre(c.estadosIdEstado))}</span></td>
        ${isAdmin ? `<td class="text-end">
          <button type="button" class="btn btn-outline-primary btn-sm me-1 btn-editar" data-id="${c.id}" title="Editar"><i class="bi bi-pencil"></i></button>
          <button type="button" class="btn btn-outline-danger btn-sm btn-eliminar" data-id="${c.id}" title="Eliminar"><i class="bi bi-trash"></i></button>
        </td>` : ''}
      `;
      tbody.appendChild(tr);
    });

    if (isAdmin) {
      tbody.querySelectorAll('.btn-editar').forEach(btn => {
        btn.addEventListener('click', () => abrirModalEditar(parseInt(btn.dataset.id, 10)));
      });
      tbody.querySelectorAll('.btn-eliminar').forEach(btn => {
        btn.addEventListener('click', () => eliminarCliente(parseInt(btn.dataset.id, 10)));
      });
    }
  }

  function llenarEstadosSelect() {
    const sel = document.getElementById('clienteEstado');
    sel.innerHTML = '<option value="">-- Seleccionar --</option>';
    estados.forEach(e => {
      const opt = document.createElement('option');
      opt.value = e.id;
      opt.textContent = e.nombre;
      sel.appendChild(opt);
    });
  }

  function llenarProvincias() {
    const sel = document.getElementById('clienteProvincia');
    sel.innerHTML = '<option value="">-- Seleccionar --</option>';
    ubicaciones.provincias.forEach(p => {
      const opt = document.createElement('option');
      opt.value = p.id;
      opt.textContent = p.nombre;
      sel.appendChild(opt);
    });
  }

  function onProvinciaChange() {
    const provId = parseInt(document.getElementById('clienteProvincia').value, 10);
    const cantonSel = document.getElementById('clienteCanton');
    const distritoSel = document.getElementById('clienteDistrito');

    cantonSel.innerHTML = '<option value="">-- Seleccionar cantón --</option>';
    distritoSel.innerHTML = '<option value="">-- Primero cantón --</option>';
    distritoSel.disabled = true;

    if (!provId) {
      cantonSel.disabled = true;
      return;
    }
    cantonSel.disabled = false;
    const cantones = ubicaciones.cantones.filter(c => c.provinciasIdProvincia === provId);
    cantones.forEach(c => {
      const opt = document.createElement('option');
      opt.value = c.id;
      opt.textContent = c.nombre;
      cantonSel.appendChild(opt);
    });
  }

  function onCantonChange() {
    const cantonId = parseInt(document.getElementById('clienteCanton').value, 10);
    const distritoSel = document.getElementById('clienteDistrito');

    distritoSel.innerHTML = '<option value="">-- Seleccionar distrito --</option>';
    if (!cantonId) {
      distritoSel.disabled = true;
      return;
    }
    distritoSel.disabled = false;
    const distritos = ubicaciones.distritos.filter(d => d.cantonesIdCanton === cantonId);
    distritos.forEach(d => {
      const opt = document.createElement('option');
      opt.value = d.id;
      opt.textContent = d.nombre + (d.codigoPostal ? ' (' + d.codigoPostal + ')' : '');
      distritoSel.appendChild(opt);
    });
  }

  function abrirModalNuevo() {
    document.getElementById('modalClienteLabel').innerHTML = '<i class="bi bi-person-plus me-2"></i>Nuevo cliente';
    document.getElementById('formCliente').reset();
    document.getElementById('clienteId').value = '';
    document.getElementById('clienteFechaIngreso').value = new Date().toISOString().slice(0, 10);
    document.getElementById('clienteEstado').value = 1;
    document.getElementById('clienteProvincia').value = '';
    document.getElementById('clienteCanton').innerHTML = '<option value="">-- Primero provincia --</option>';
    document.getElementById('clienteCanton').disabled = true;
    document.getElementById('clienteDistrito').innerHTML = '<option value="">-- Primero cantón --</option>';
    document.getElementById('clienteDistrito').disabled = true;
    modalInstance = new bootstrap.Modal(document.getElementById('modalCliente'));
    modalInstance.show();
  }

  function abrirModalEditar(id) {
    if (!isAdmin) return;
    const clientes = getClientes();
    const c = clientes.find(x => x.id === id);
    if (!c) return;

    document.getElementById('modalClienteLabel').innerHTML = '<i class="bi bi-pencil me-2"></i>Editar cliente';
    document.getElementById('clienteId').value = c.id;
    document.getElementById('clienteNombre').value = c.nombre;
    document.getElementById('clienteApellido').value = c.apellido;
    document.getElementById('clienteEmail').value = c.email;
    document.getElementById('clienteTelefono').value = c.telefono;
    document.getElementById('clienteFechaIngreso').value = (c.fechaIngreso || '').slice(0, 10);
    document.getElementById('clienteEstado').value = c.estadosIdEstado || 1;
    document.getElementById('clienteOtrasSenas').value = (c.direccion && c.direccion.otrasSenas) || '';

    const d = c.direccion || {};
    document.getElementById('clienteProvincia').value = d.provinciasIdProvincia || '';
    onProvinciaChange();
    document.getElementById('clienteCanton').value = d.cantonesIdCanton || '';
    onCantonChange();
    document.getElementById('clienteDistrito').value = d.distritosIdDistrito || '';

    modalInstance = new bootstrap.Modal(document.getElementById('modalCliente'));
    modalInstance.show();
  }

  function guardarCliente() {
    const idEl = document.getElementById('clienteId');
    const nombre = document.getElementById('clienteNombre').value.trim();
    const apellido = document.getElementById('clienteApellido').value.trim();
    const email = document.getElementById('clienteEmail').value.trim();
    const telefono = document.getElementById('clienteTelefono').value.trim();
    const fechaIngreso = document.getElementById('clienteFechaIngreso').value;
    const estadosIdEstado = parseInt(document.getElementById('clienteEstado').value, 10) || 1;
    const otrasSenas = document.getElementById('clienteOtrasSenas').value.trim();
    const provId = document.getElementById('clienteProvincia').value ? parseInt(document.getElementById('clienteProvincia').value, 10) : null;
    const cantonId = document.getElementById('clienteCanton').value ? parseInt(document.getElementById('clienteCanton').value, 10) : null;
    const distritoId = document.getElementById('clienteDistrito').value ? parseInt(document.getElementById('clienteDistrito').value, 10) : null;

    if (!nombre || !apellido || !email || !telefono || !fechaIngreso) {
      alert('Complete los campos obligatorios.');
      return;
    }

    const direccion = (provId || cantonId || distritoId || otrasSenas) ? {
      otrasSenas: otrasSenas || null,
      provinciasIdProvincia: provId,
      cantonesIdCanton: cantonId,
      distritosIdDistrito: distritoId
    } : null;

    const clientes = getClientes();
    const existingId = idEl.value ? parseInt(idEl.value, 10) : null;

    if (existingId) {
      if (!isAdmin) return;
      const idx = clientes.findIndex(x => x.id === existingId);
      if (idx >= 0) {
        clientes[idx] = { ...clientes[idx], nombre, apellido, email, telefono, fechaIngreso, estadosIdEstado, direccion };
        saveClientes(clientes);
        modalInstance.hide();
        return;
      }
    }

    const maxId = clientes.reduce((m, x) => Math.max(m, x.id || 0), 0);
    const nuevo = {
      id: maxId + 1,
      nombre,
      apellido,
      email,
      telefono,
      fechaIngreso,
      estadosIdEstado,
      direccion
    };
    clientes.push(nuevo);
    saveClientes(clientes);
    modalInstance.hide();
  }

  function eliminarCliente(id) {
    if (!isAdmin) return;
    if (!confirm('¿Eliminar este cliente?')) return;
    const clientes = getClientes().filter(c => c.id !== id);
    saveClientes(clientes);
  }

  document.addEventListener('DOMContentLoaded', function () {
    const base = basePath || '';
    Promise.all([
      fetch(base + '/js/mocks/estados.json').then(r => r.ok ? r.json() : Promise.reject(new Error('estados.json'))).then(data => { estados = data; }),
      fetch(base + '/js/mocks/clientes.json').then(r => r.ok ? r.json() : Promise.reject(new Error('clientes.json'))),
      loadUbicaciones()
    ]).then(([, clientesData]) => {
      llenarProvincias();
      llenarEstadosSelect();
      const current = getClientes();
      if (current.length === 0 && clientesData && clientesData.length > 0) {
        const withDireccion = clientesData.map(c => ({ ...c, direccion: c.direccion || null }));
        saveClientes(withDireccion);
      } else {
        renderTable();
      }
    }).catch(err => {
      console.error('Error cargando mocks:', err);
      if (estados.length === 0) estados = [{ id: 1, nombre: 'activo' }, { id: 2, nombre: 'inactivo' }];
      llenarProvincias();
      llenarEstadosSelect();
      seedFromJson().then(() => renderTable());
    });

    document.getElementById('clienteProvincia').addEventListener('change', onProvinciaChange);
    document.getElementById('clienteCanton').addEventListener('change', onCantonChange);
    document.getElementById('btnNuevoCliente').addEventListener('click', abrirModalNuevo);
    document.getElementById('linkNuevoCliente').addEventListener('click', function (e) {
      e.preventDefault();
      abrirModalNuevo();
    });
    document.getElementById('btnGuardarCliente').addEventListener('click', guardarCliente);
  });
})();
