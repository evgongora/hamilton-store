/**
 * dashboard.js - Métricas y resumen del panel principal
 */
(function () {
  'use strict';

  function getVentas() {
    try {
      return JSON.parse(localStorage.getItem('hamilton_ventas') || '[]');
    } catch (e) { return []; }
  }

  function getClientes() {
    try {
      return JSON.parse(localStorage.getItem('hamilton_clientes') || '[]');
    } catch (e) { return []; }
  }

  function getEmpleados() {
    try {
      return JSON.parse(localStorage.getItem('hamilton_empleados') || '[]');
    } catch (e) { return []; }
  }

  function formatMoney(n) {
    return '₡' + Number(n).toLocaleString('es-CR');
  }

  function formatFecha(iso) {
    if (!iso) return '—';
    const d = new Date(iso);
    return isNaN(d.getTime()) ? iso : d.toLocaleDateString('es-CR') + ' ' + d.toLocaleTimeString('es-CR', { hour: '2-digit', minute: '2-digit' });
  }

  function escapeHtml(s) {
    const div = document.createElement('div');
    div.textContent = s == null ? '' : String(s);
    return div.innerHTML;
  }

  function render() {
    const ventas = getVentas();
    const clientes = getClientes();
    const empleados = getEmpleados();

    const totalVentas = ventas.reduce((s, v) => s + (v.total || 0), 0);
    const totalPagos = ventas.reduce((s, v) => {
      return s + (v.pagos || []).reduce((sp, p) => sp + (p.monto || 0), 0);
    }, 0);

    document.getElementById('dashTotalVentas').textContent = formatMoney(totalVentas);
    document.getElementById('dashTotalPagos').textContent = formatMoney(totalPagos);
    document.getElementById('dashClientes').textContent = clientes.length;
    document.getElementById('dashEmpleados').textContent = empleados.length;

    const ultimas = ventas.slice(-8).reverse();
    const tbody = document.getElementById('dashVentasBody');
    const empty = document.getElementById('dashVentasEmpty');

    tbody.innerHTML = '';
    if (ultimas.length === 0) {
      empty.style.display = 'block';
      return;
    }
    empty.style.display = 'none';

    ultimas.forEach(v => {
      const tr = document.createElement('tr');
      const clienteNom = v.clienteNombre || (v.clientesIdCliente ? 'Cliente #' + v.clientesIdCliente : 'Sin asignar');
      tr.innerHTML = `
        <td>${escapeHtml(v.id)}</td>
        <td>${escapeHtml(formatFecha(v.fecha))}</td>
        <td>${escapeHtml(clienteNom)}</td>
        <td>${escapeHtml(v.origen || 'sistema')}</td>
        <td class="text-end">${formatMoney(v.total)}</td>
      `;
      tbody.appendChild(tr);
    });
  }

  document.addEventListener('DOMContentLoaded', render);

  window.addEventListener('storage', function (e) {
    if (['hamilton_ventas', 'hamilton_clientes', 'hamilton_empleados'].includes(e.key)) {
      render();
    }
  });
})();
