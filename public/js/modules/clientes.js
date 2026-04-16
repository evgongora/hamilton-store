(function () {
  'use strict';

  var loadingHtml =
    '<div class="d-flex justify-content-center py-5">' +
    '<div class="spinner-border text-secondary" role="status">' +
    '<span class="visually-hidden">Cargando</span>' +
    '</div></div>';

  var clientes = [];

  function esc(s) {
    const d = document.createElement('div');
    d.textContent = s;
    return d.innerHTML;
  }

  function getFiltered() {
    var q = (document.getElementById('clientesBuscar') || { value: '' }).value.trim().toLowerCase();
    if (!q) return clientes;
    return clientes.filter(function (c) {
      var blob = [c.nombre, c.apellido, c.email].filter(Boolean).join(' ').toLowerCase();
      return blob.indexOf(q) !== -1;
    });
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
      '<thead class="table-light"><tr><th>ID</th><th>Nombre</th><th>Apellido</th><th>Email</th><th>Estado</th></tr></thead>';
    var tbody = '<tbody>';
    rows.forEach(function (c) {
      tbody +=
        '<tr><td>' +
        esc(String(c.id)) +
        '</td><td>' +
        esc(c.nombre) +
        '</td><td>' +
        esc(c.apellido) +
        '</td><td>' +
        esc(c.email) +
        '</td><td><span class="badge bg-secondary">' +
        esc(c.estado || '') +
        '</span></td></tr>';
    });
    tbody += '</tbody>';
    wrap.innerHTML =
      '<div class="table-responsive shadow-sm rounded border bg-white">' +
      '<table class="table table-hover table-sm mb-0">' +
      thead +
      tbody +
      '</table></div>';
  }

  async function load() {
    var wrap = document.getElementById('clientesSistemaTable');
    if (!wrap || !window.Api) return;

    wrap.innerHTML = loadingHtml;

    try {
      const json = await window.Api.get('/clientes_list.php');
      clientes = json.data || [];
      if (clientes.length === 0) {
        wrap.innerHTML = '<p class="text-muted">No hay clientes.</p>';
        return;
      }
      render();
    } catch (e) {
      wrap.innerHTML =
        '<div class="alert alert-danger">' +
        esc(e.message || 'No autorizado o error de conexión') +
        '</div>';
    }
  }

  document.addEventListener('DOMContentLoaded', function () {
    document.getElementById('clientesBuscar')?.addEventListener('input', render);
    load();
  });
})();
