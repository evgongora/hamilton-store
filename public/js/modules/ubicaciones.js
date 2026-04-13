/**
 * ubicaciones.js - CRUD provincias, cantones, distritos
 * localStorage hamilton_ubicaciones, seed desde ubicaciones.json
 */
(function () {
  'use strict';

  function uiConfirm(msg, title) {
    if (window.UiDialog && window.UiDialog.confirm) {
      return window.UiDialog.confirm(String(msg), { title: title || 'Confirmar' });
    }
    return Promise.resolve(confirm(msg));
  }

  const STORAGE_KEY = 'hamilton_ubicaciones';
  const basePath = (document.body.dataset.basePath || '/hamilton-store/public').replace(/\/$/, '');

  let data = { provincias: [], cantones: [], distritos: [] };

  function fetchJson(path) {
    return fetch(basePath + path).then(r => (r.ok ? r.json() : Promise.reject()));
  }

  function getData() {
    const stored = localStorage.getItem(STORAGE_KEY);
    if (stored) try { return JSON.parse(stored); } catch (e) {}
    return null;
  }

  function saveData() {
    localStorage.setItem(STORAGE_KEY, JSON.stringify(data));
    renderAll();
    window.dispatchEvent(new CustomEvent('ubicaciones-changed', { detail: data }));
  }

  function escapeHtml(s) {
    const div = document.createElement('div');
    div.textContent = s == null ? '' : String(s);
    return div.innerHTML;
  }

  function renderAll() {
    renderProvincias();
    renderCantones();
    renderDistritos();
  }

  function renderProvincias() {
    const tbody = document.getElementById('provinciasBody');
    tbody.innerHTML = '';
    data.provincias.forEach(p => {
      const tr = document.createElement('tr');
      tr.innerHTML = `<td>${p.id}</td><td>${escapeHtml(p.nombre)}</td><td class="text-end"><button class="btn btn-outline-primary btn-sm me-1 btn-edit-prov" data-id="${p.id}"><i class="bi bi-pencil"></i></button><button class="btn btn-outline-danger btn-sm btn-del-prov" data-id="${p.id}"><i class="bi bi-trash"></i></button></td>`;
      tbody.appendChild(tr);
    });
    tbody.querySelectorAll('.btn-edit-prov').forEach(b => b.addEventListener('click', () => editarProvincia(parseInt(b.dataset.id, 10))));
    tbody.querySelectorAll('.btn-del-prov').forEach(b => b.addEventListener('click', () => eliminarProvincia(parseInt(b.dataset.id, 10))));
  }

  function renderCantones() {
    const tbody = document.getElementById('cantonesBody');
    tbody.innerHTML = '';
    data.cantones.forEach(c => {
      const prov = data.provincias.find(p => p.id === c.provinciasIdProvincia);
      const tr = document.createElement('tr');
      tr.innerHTML = `<td>${c.id}</td><td>${escapeHtml(c.nombre)}</td><td>${escapeHtml(prov?.nombre)}</td><td class="text-end"><button class="btn btn-outline-primary btn-sm me-1 btn-edit-cant" data-id="${c.id}"><i class="bi bi-pencil"></i></button><button class="btn btn-outline-danger btn-sm btn-del-cant" data-id="${c.id}"><i class="bi bi-trash"></i></button></td>`;
      tbody.appendChild(tr);
    });
    tbody.querySelectorAll('.btn-edit-cant').forEach(b => b.addEventListener('click', () => editarCanton(parseInt(b.dataset.id, 10))));
    tbody.querySelectorAll('.btn-del-cant').forEach(b => b.addEventListener('click', () => eliminarCanton(parseInt(b.dataset.id, 10))));
  }

  function renderDistritos() {
    const tbody = document.getElementById('distritosBody');
    tbody.innerHTML = '';
    data.distritos.forEach(d => {
      const cant = data.cantones.find(c => c.id === d.cantonesIdCanton);
      const tr = document.createElement('tr');
      tr.innerHTML = `<td>${d.id}</td><td>${escapeHtml(d.nombre)}</td><td>${escapeHtml(cant?.nombre)}</td><td>${escapeHtml(d.codigoPostal)}</td><td class="text-end"><button class="btn btn-outline-primary btn-sm me-1 btn-edit-dist" data-id="${d.id}"><i class="bi bi-pencil"></i></button><button class="btn btn-outline-danger btn-sm btn-del-dist" data-id="${d.id}"><i class="bi bi-trash"></i></button></td>`;
      tbody.appendChild(tr);
    });
    tbody.querySelectorAll('.btn-edit-dist').forEach(b => b.addEventListener('click', () => editarDistrito(parseInt(b.dataset.id, 10))));
    tbody.querySelectorAll('.btn-del-dist').forEach(b => b.addEventListener('click', () => eliminarDistrito(parseInt(b.dataset.id, 10))));
  }

  function nextId(arr) { return Math.max(0, ...arr.map(x => x.id || 0)) + 1; }

  function abrirProvincia(id) {
    document.getElementById('provinciaId').value = id || '';
    document.getElementById('provinciaNombre').value = id ? (data.provincias.find(p => p.id === id)?.nombre || '') : '';
    new bootstrap.Modal(document.getElementById('modalProvincia')).show();
  }
  function guardarProvincia() {
    const id = document.getElementById('provinciaId').value ? parseInt(document.getElementById('provinciaId').value, 10) : null;
    const nombre = document.getElementById('provinciaNombre').value.trim();
    if (!nombre) return;
    if (id) {
      const p = data.provincias.find(x => x.id === id);
      if (p) p.nombre = nombre;
    } else data.provincias.push({ id: nextId(data.provincias), nombre });
    saveData();
    bootstrap.Modal.getInstance(document.getElementById('modalProvincia')).hide();
  }
  function editarProvincia(id) { abrirProvincia(id); }
  function eliminarProvincia(id) {
    uiConfirm('¿Eliminar? Se eliminarán cantones y distritos asociados.', 'Eliminar provincia').then(function (ok) {
      if (!ok) return;
    const removedCantonIds = data.cantones.filter(c => c.provinciasIdProvincia === id).map(c => c.id);
    data.cantones = data.cantones.filter(c => c.provinciasIdProvincia !== id);
    data.distritos = data.distritos.filter(d => !removedCantonIds.includes(d.cantonesIdCanton));
    data.provincias = data.provincias.filter(p => p.id !== id);
    saveData();
    });
  }

  function llenarProvinciasSelect(sel) {
    sel.innerHTML = '<option value="">-- Provincia --</option>';
    data.provincias.forEach(p => { const o = document.createElement('option'); o.value = p.id; o.textContent = p.nombre; sel.appendChild(o); });
  }
  function llenarCantonesSelect(sel, provId) {
    sel.innerHTML = '<option value="">-- Cantón --</option>';
    data.cantones.filter(c => !provId || c.provinciasIdProvincia === provId).forEach(c => { const o = document.createElement('option'); o.value = c.id; o.textContent = c.nombre; sel.appendChild(o); });
  }

  function abrirCanton(id) {
    llenarProvinciasSelect(document.getElementById('cantonProvincia'));
    document.getElementById('cantonId').value = id || '';
    if (id) {
      const c = data.cantones.find(x => x.id === id);
      document.getElementById('cantonNombre').value = c?.nombre || '';
      document.getElementById('cantonProvincia').value = c?.provinciasIdProvincia || '';
    } else document.getElementById('cantonNombre').value = '';
    new bootstrap.Modal(document.getElementById('modalCanton')).show();
  }
  function guardarCanton() {
    const id = document.getElementById('cantonId').value ? parseInt(document.getElementById('cantonId').value, 10) : null;
    const nombre = document.getElementById('cantonNombre').value.trim();
    const provId = parseInt(document.getElementById('cantonProvincia').value, 10);
    if (!nombre || !provId) return;
    if (id) { const c = data.cantones.find(x => x.id === id); if (c) { c.nombre = nombre; c.provinciasIdProvincia = provId; } }
    else data.cantones.push({ id: nextId(data.cantones), nombre, provinciasIdProvincia: provId });
    saveData();
    bootstrap.Modal.getInstance(document.getElementById('modalCanton')).hide();
  }
  function editarCanton(id) { abrirCanton(id); }
  function eliminarCanton(id) {
    uiConfirm('¿Eliminar cantón y sus distritos?', 'Eliminar cantón').then(function (ok) {
      if (!ok) return;
    data.distritos = data.distritos.filter(d => d.cantonesIdCanton !== id);
    data.cantones = data.cantones.filter(c => c.id !== id);
    saveData();
    });
  }

  function abrirDistrito(id) {
    const cantonSel = document.getElementById('distritoCanton');
    llenarCantonesSelect(cantonSel);
    document.getElementById('distritoId').value = id || '';
    if (id) {
      const d = data.distritos.find(x => x.id === id);
      document.getElementById('distritoNombre').value = d?.nombre || '';
      document.getElementById('distritoCanton').value = d?.cantonesIdCanton || '';
      document.getElementById('distritoCodigoPostal').value = d?.codigoPostal || '';
    } else { document.getElementById('distritoNombre').value = ''; document.getElementById('distritoCanton').value = ''; document.getElementById('distritoCodigoPostal').value = ''; }
    new bootstrap.Modal(document.getElementById('modalDistrito')).show();
  }
  function guardarDistrito() {
    const id = document.getElementById('distritoId').value ? parseInt(document.getElementById('distritoId').value, 10) : null;
    const nombre = document.getElementById('distritoNombre').value.trim();
    const cantonId = parseInt(document.getElementById('distritoCanton').value, 10);
    const cp = document.getElementById('distritoCodigoPostal').value ? parseInt(document.getElementById('distritoCodigoPostal').value, 10) : null;
    if (!nombre || !cantonId) return;
    if (id) { const d = data.distritos.find(x => x.id === id); if (d) { d.nombre = nombre; d.cantonesIdCanton = cantonId; d.codigoPostal = cp; } }
    else data.distritos.push({ id: nextId(data.distritos), nombre, cantonesIdCanton: cantonId, codigoPostal: cp });
    saveData();
    bootstrap.Modal.getInstance(document.getElementById('modalDistrito')).hide();
  }
  function editarDistrito(id) { abrirDistrito(id); }
  function eliminarDistrito(id) {
    uiConfirm('¿Eliminar distrito?', 'Eliminar distrito').then(function (ok) {
      if (!ok) return;
    data.distritos = data.distritos.filter(d => d.id !== id);
    saveData();
    });
  }

  document.addEventListener('DOMContentLoaded', function () {
    const stored = getData();
    if (stored && stored.provincias && stored.provincias.length > 0) {
      data = stored;
      renderAll();
    } else {
      fetchJson('/js/mocks/ubicaciones.json').then(d => {
        data = d;
        saveData();
      }).catch(() => renderAll());
    }

    document.getElementById('btnNuevaProvincia').addEventListener('click', () => abrirProvincia());
    document.getElementById('btnGuardarProvincia').addEventListener('click', guardarProvincia);
    document.getElementById('btnNuevoCanton').addEventListener('click', () => abrirCanton());
    document.getElementById('btnGuardarCanton').addEventListener('click', guardarCanton);
    document.getElementById('btnNuevoDistrito').addEventListener('click', () => abrirDistrito());
    document.getElementById('btnGuardarDistrito').addEventListener('click', guardarDistrito);
  });
})();
