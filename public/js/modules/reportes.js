/**
 * reportes.js - Reportes de ventas y pagos con filtros por fechas
 * Mock: localStorage hamilton_ventas
 */
(function () {
  'use strict';

  const STORAGE_KEY = 'hamilton_ventas';

  function getVentas() {
    try {
      return JSON.parse(localStorage.getItem(STORAGE_KEY) || '[]');
    } catch (e) {
      return [];
    }
  }

  function formatMoney(n) {
    return '₡' + Number(n).toLocaleString('es-CR');
  }

  function formatFecha(iso) {
    if (!iso) return '—';
    const d = new Date(iso);
    if (isNaN(d.getTime())) return iso;
    return d.toLocaleDateString('es-CR') + ' ' + d.toLocaleTimeString('es-CR', { hour: '2-digit', minute: '2-digit' });
  }

  function escapeHtml(s) {
    if (s == null) return '';
    const div = document.createElement('div');
    div.textContent = String(s);
    return div.innerHTML;
  }

  function fechaDentroRango(fechaStr, desde, hasta) {
    if (!fechaStr) return false;
    const d = new Date(fechaStr);
    if (isNaN(d.getTime())) return false;
    const t = d.getTime();
    if (desde && t < new Date(desde + 'T00:00:00').getTime()) return false;
    if (hasta && t > new Date(hasta + 'T23:59:59').getTime()) return false;
    return true;
  }

  function aplicarFiltros(ventas, fechaDesde, fechaHasta) {
    return ventas.filter(v => fechaDentroRango(v.fecha, fechaDesde, fechaHasta));
  }

  function getPagosDeVentas(ventas) {
    const pagos = [];
    ventas.forEach(v => {
      (v.pagos || []).forEach(p => {
        pagos.push({
          ventaId: v.id,
          fecha: p.fechaPago || p.fecha,
          monto: p.monto
        });
      });
    });
    return pagos;
  }

  function render(fechaDesde, fechaHasta) {
    const ventas = getVentas();
    const ventasFiltradas = aplicarFiltros(ventas, fechaDesde, fechaHasta);
    const pagos = getPagosDeVentas(ventasFiltradas);

    renderVentas(ventasFiltradas);
    renderPagos(pagos);
  }

  function renderVentas(ventas) {
    const tbody = document.getElementById('reporteVentasBody');
    const totalEl = document.getElementById('reporteVentasTotal');
    const empty = document.getElementById('reporteVentasEmpty');

    tbody.innerHTML = '';
    let totalSum = 0;

    if (ventas.length === 0) {
      empty.style.display = 'block';
      totalEl.textContent = formatMoney(0);
      return;
    }
    empty.style.display = 'none';

    ventas.forEach(v => {
      totalSum += v.total || 0;
      const clienteNom = v.clienteNombre || (v.clientesIdCliente ? 'Cliente #' + v.clientesIdCliente : 'Sin asignar');
      const tr = document.createElement('tr');
      tr.innerHTML = `
        <td>${escapeHtml(v.id)}</td>
        <td>${escapeHtml(formatFecha(v.fecha))}</td>
        <td>${escapeHtml(clienteNom)}</td>
        <td>${escapeHtml(v.origen || 'sistema')}</td>
        <td class="text-end">${formatMoney(v.total)}</td>
      `;
      tbody.appendChild(tr);
    });

    totalEl.textContent = formatMoney(totalSum);
  }

  function renderPagos(pagos) {
    const tbody = document.getElementById('reportePagosBody');
    const totalEl = document.getElementById('reportePagosTotal');
    const empty = document.getElementById('reportePagosEmpty');

    tbody.innerHTML = '';
    let totalSum = 0;

    if (pagos.length === 0) {
      empty.style.display = 'block';
      totalEl.textContent = formatMoney(0);
      return;
    }
    empty.style.display = 'none';

    pagos.forEach(p => {
      totalSum += p.monto;
      const tr = document.createElement('tr');
      tr.innerHTML = `
        <td>#${escapeHtml(p.ventaId)}</td>
        <td>${escapeHtml(formatFecha(p.fecha))}</td>
        <td>${formatMoney(p.monto)}</td>
      `;
      tbody.appendChild(tr);
    });

    totalEl.textContent = formatMoney(totalSum);
  }

  document.addEventListener('DOMContentLoaded', function () {
    const hoy = new Date().toISOString().slice(0, 10);
    const hace30 = new Date();
    hace30.setDate(hace30.getDate() - 30);
    const desdeDefault = hace30.toISOString().slice(0, 10);

    document.getElementById('fechaDesde').value = desdeDefault;
    document.getElementById('fechaHasta').value = hoy;

    render(desdeDefault, hoy);

    document.getElementById('btnFiltrar').addEventListener('click', function () {
      const desde = document.getElementById('fechaDesde').value;
      const hasta = document.getElementById('fechaHasta').value;
      render(desde || null, hasta || null);
    });

    document.getElementById('btnLimpiar').addEventListener('click', function () {
      document.getElementById('fechaDesde').value = '';
      document.getElementById('fechaHasta').value = '';
      render(null, null);
    });
  });
})();
