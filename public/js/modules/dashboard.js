/**
 * dashboard.js — Resumen desde API Oracle únicamente.
 */
(function () {
  'use strict';

  function formatMoney(n) {
    return '₡' + Number(n).toLocaleString('es-CR');
  }

  function formatFecha(isoOrStr) {
    if (!isoOrStr) return '—';
    const d = new Date(isoOrStr);
    return isNaN(d.getTime())
      ? String(isoOrStr)
      : d.toLocaleDateString('es-CR') + ' ' + d.toLocaleTimeString('es-CR', { hour: '2-digit', minute: '2-digit' });
  }

  function escapeHtml(s) {
    const div = document.createElement('div');
    div.textContent = s == null ? '' : String(s);
    return div.innerHTML;
  }

  function fillVentasTable(rows, getFecha) {
    const tbody = document.getElementById('dashVentasBody');
    const empty = document.getElementById('dashVentasEmpty');
    if (!tbody || !empty) return;

    tbody.innerHTML = '';
    if (!rows.length) {
      empty.style.display = 'block';
      return;
    }
    empty.style.display = 'none';

    rows.forEach(function (v) {
      const tr = document.createElement('tr');
      const clienteNom =
        v.clienteNombre || (v.clientesIdCliente ? 'Cliente #' + v.clientesIdCliente : 'Sin asignar');
      tr.innerHTML =
        '<td>' +
        escapeHtml(String(v.id)) +
        '</td><td>' +
        escapeHtml(formatFecha(getFecha(v))) +
        '</td><td>' +
        escapeHtml(clienteNom) +
        '</td><td>' +
        escapeHtml(v.origen || 'sistema') +
        '</td><td class="text-end">' +
        formatMoney(v.total) +
        '</td>';
      tbody.appendChild(tr);
    });
  }

  function setMetricasVacias() {
    const elV = document.getElementById('dashTotalVentas');
    const elP = document.getElementById('dashTotalPagos');
    const elC = document.getElementById('dashClientes');
    const elPr = document.getElementById('dashProductos');
    if (elV) elV.textContent = formatMoney(0);
    if (elP) elP.textContent = formatMoney(0);
    if (elC) elC.textContent = '0';
    if (elPr) elPr.textContent = '0';
    fillVentasTable([], function () {
      return '';
    });
  }

  async function loadFromApi() {
    if (!window.Api) {
      setMetricasVacias();
      const note = document.getElementById('dashApiNote');
      if (note) {
        note.textContent = 'No hay conexión a la API (API_BASE).';
        note.classList.remove('d-none');
      }
      return;
    }

    try {
      const [ventasJson, clientesJson, productosJson] = await Promise.all([
        window.Api.get('/ventas_list.php'),
        window.Api.get('/clientes_list.php'),
        window.Api.get('/productos_list.php').catch(function () {
          return { data: [] };
        }),
      ]);

      const ventas = ventasJson.data || [];
      const clientes = clientesJson.data || [];
      const productos = productosJson.data || [];

      const totalVentas = ventas.reduce(function (s, v) {
        return s + (v.total || 0);
      }, 0);
      const totalPagos = ventas.reduce(function (s, v) {
        return s + (v.pagado || 0);
      }, 0);

      const elV = document.getElementById('dashTotalVentas');
      const elP = document.getElementById('dashTotalPagos');
      const elC = document.getElementById('dashClientes');
      const elPr = document.getElementById('dashProductos');
      if (elV) elV.textContent = formatMoney(totalVentas);
      if (elP) elP.textContent = formatMoney(totalPagos);
      if (elC) elC.textContent = String(clientes.length);
      if (elPr) elPr.textContent = String(productos.length);

      const ultimas = ventas.slice(0, 8);
      fillVentasTable(ultimas, function (v) {
        return v.fechaVentaIso || v.fechaVenta;
      });

      const note = document.getElementById('dashApiNote');
      if (note) {
        note.classList.add('d-none');
        note.textContent = '';
      }
    } catch (e) {
      setMetricasVacias();
      const note = document.getElementById('dashApiNote');
      if (note) {
        note.textContent =
          'No se pudieron cargar las métricas desde Oracle. Revise la conexión y vuelva a intentar.';
        note.classList.remove('d-none');
      }
    }
  }

  document.addEventListener('DOMContentLoaded', loadFromApi);
})();
