/**
 * proveedores.js - tabla informativa del modulo Proveedores
 */
(function () {
  'use strict';

  var STORAGE_KEY = 'hamilton-store-proveedores';

  document.addEventListener('DOMContentLoaded', function () {
    var tbody = document.getElementById('proveedoresBody');
    if (!tbody) {
      return;
    }

    var basePath = (document.body.dataset.basePath || '').replace(/\/$/, '');
    var count = document.getElementById('proveedoresCount');
    var empty = document.getElementById('proveedoresEmpty');

    function readStorage() {
      try {
        var raw = localStorage.getItem(STORAGE_KEY);
        if (!raw) {
          return null;
        }
        var parsed = JSON.parse(raw);
        return Array.isArray(parsed) ? parsed : null;
      } catch (error) {
        return null;
      }
    }

    function normalizeProvider(provider) {
      return {
        nombre: String(provider.nombre || provider.proveedor || '').trim(),
        contacto: String(provider.contacto || provider.nombreContacto || '').trim(),
        telefono: String(provider.telefono || provider.numeroTelefono || '').trim(),
        email: String(provider.email || '').trim()
      };
    }

    function escapeHtml(value) {
      return String(value == null ? '' : value)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');
    }

    function renderTable(providers) {
      tbody.innerHTML = '';
      count.textContent = providers.length + ' proveedor(es)';

      if (!providers.length) {
        empty.style.display = 'block';
        return;
      }

      empty.style.display = 'none';

      providers.forEach(function (provider) {
        var row = document.createElement('tr');
        row.innerHTML = [
          '<td><div class="fw-semibold">' + escapeHtml(provider.nombre || '—') + '</div></td>',
          '<td>' + escapeHtml(provider.contacto || '—') + '</td>',
          '<td>' + escapeHtml(provider.telefono || '—') + '</td>',
          '<td>' + escapeHtml(provider.email || '—') + '</td>'
        ].join('');
        tbody.appendChild(row);
      });
    }

    function renderLoadingState() {
      tbody.innerHTML = '<tr><td colspan="4" class="text-center text-muted py-4">Cargando proveedores...</td></tr>';
      empty.style.display = 'none';
      count.textContent = 'Cargando...';
    }

    function renderErrorState() {
      tbody.innerHTML = '<tr><td colspan="4" class="text-center text-danger py-4">No se pudieron cargar los proveedores.</td></tr>';
      empty.style.display = 'none';
      count.textContent = '0 proveedores';
    }

    function loadProviders() {
      renderLoadingState();

      fetch(basePath + '/js/mocks/proveedores.json', { cache: 'no-store' })
        .then(function (response) {
          if (!response.ok) {
            throw new Error('proveedores mock');
          }
          return response.json();
        })
        .then(function (providers) {
          renderTable((Array.isArray(providers) ? providers : []).map(normalizeProvider));
        })
        .catch(function () {
          var fallback = readStorage();
          if (fallback) {
            renderTable(fallback.map(normalizeProvider));
            return;
          }

          renderErrorState();
        });
    }

    loadProviders();
  });
})();
