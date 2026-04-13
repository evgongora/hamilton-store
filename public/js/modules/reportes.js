/**
 * reportes.js — Ventas y cobros por rango de fechas (API Oracle + respaldo localStorage).
 */
(function () {
  'use strict';

  const STORAGE_KEY = 'hamilton_ventas';

  function getVentasLocal() {
    try {
      return JSON.parse(localStorage.getItem(STORAGE_KEY) || '[]');
    } catch (e) {
      return [];
    }
  }

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
    if (s == null) return '';
    const div = document.createElement('div');
    div.textContent = String(s);
    return div.innerHTML;
  }

  /** @returns {number} */
  function ventaTimestamp(v) {
    const iso = v.fechaVentaIso || v.fecha;
    if (iso) {
      const t = new Date(iso).getTime();
      if (!isNaN(t)) return t;
    }
    if (v.fecha) {
      const t2 = new Date(v.fecha).getTime();
      if (!isNaN(t2)) return t2;
    }
    return 0;
  }

  function fechaDentroRango(ts, desde, hasta) {
    if (!ts) return !desde && !hasta;
    const desdeT = desde ? new Date(desde + 'T00:00:00').getTime() : null;
    const hastaT = hasta ? new Date(hasta + 'T23:59:59').getTime() : null;
    if (desdeT != null && ts < desdeT) return false;
    if (hastaT != null && ts > hastaT) return false;
    return true;
  }

  function aplicarFiltros(ventas, fechaDesde, fechaHasta) {
    return ventas.filter(function (v) {
      return fechaDentroRango(ventaTimestamp(v), fechaDesde, fechaHasta);
    });
  }

  function mapApiVentas(rows) {
    return (rows || []).map(function (r) {
      return {
        id: r.id,
        fecha: r.fechaVentaIso || r.fechaVenta,
        total: r.total,
        clienteNombre: r.clienteNombre,
        origen: 'sistema',
        pagado: r.pagado,
        fechaVentaIso: r.fechaVentaIso,
        fechaVenta: r.fechaVenta,
      };
    });
  }

  function renderVentas(ventas) {
    const tbody = document.getElementById('reporteVentasBody');
    const totalEl = document.getElementById('reporteVentasTotal');
    const empty = document.getElementById('reporteVentasEmpty');
    if (!tbody || !totalEl || !empty) return;

    tbody.innerHTML = '';
    let totalSum = 0;

    if (ventas.length === 0) {
      empty.style.display = 'block';
      totalEl.textContent = formatMoney(0);
      return;
    }
    empty.style.display = 'none';

    ventas.forEach(function (v) {
      totalSum += v.total || 0;
      const clienteNom =
        v.clienteNombre || (v.clientesIdCliente ? 'Cliente #' + v.clientesIdCliente : 'Sin asignar');
      const tr = document.createElement('tr');
      tr.innerHTML =
        '<td>' +
        escapeHtml(v.id) +
        '</td><td>' +
        escapeHtml(formatFecha(v.fechaVentaIso || v.fecha)) +
        '</td><td>' +
        escapeHtml(clienteNom) +
        '</td><td>' +
        escapeHtml(v.origen || 'sistema') +
        '</td><td class="text-end">' +
        formatMoney(v.total) +
        '</td>';
      tbody.appendChild(tr);
    });

    totalEl.textContent = formatMoney(totalSum);
  }

  function renderPagosDesdeVentas(ventas) {
    const tbody = document.getElementById('reportePagosBody');
    const totalEl = document.getElementById('reportePagosTotal');
    const empty = document.getElementById('reportePagosEmpty');
    if (!tbody || !totalEl || !empty) return;

    tbody.innerHTML = '';
    let totalSum = 0;

    const rows = [];
    ventas.forEach(function (v) {
      const monto = v.pagado != null ? v.pagado : (v.pagos || []).reduce(function (s, p) {
        return s + (p.monto || 0);
      }, 0);
      if (monto > 0) {
        rows.push({
          ventaId: v.id,
          fecha: v.fechaVentaIso || v.fecha || v.fechaVenta,
          monto: monto,
        });
      }
    });

    if (rows.length === 0) {
      empty.style.display = 'block';
      totalEl.textContent = formatMoney(0);
      return;
    }
    empty.style.display = 'none';

    rows.forEach(function (p) {
      totalSum += p.monto;
      const tr = document.createElement('tr');
      tr.innerHTML =
        '<td>#' +
        escapeHtml(p.ventaId) +
        '</td><td>' +
        escapeHtml(formatFecha(p.fecha)) +
        '</td><td class="text-end">' +
        formatMoney(p.monto) +
        '</td>';
      tbody.appendChild(tr);
    });

    totalEl.textContent = formatMoney(totalSum);
  }

  function render(fechaDesde, fechaHasta, ventasSource) {
    const ventas = aplicarFiltros(ventasSource, fechaDesde, fechaHasta);
    renderVentas(ventas);
    renderPagosDesdeVentas(ventas);
  }

  let ventasCache = [];

  document.addEventListener('DOMContentLoaded', function () {
    const hoy = new Date().toISOString().slice(0, 10);
    const hace30 = new Date();
    hace30.setDate(hace30.getDate() - 30);
    const desdeDefault = hace30.toISOString().slice(0, 10);

    const elDesde = document.getElementById('fechaDesde');
    const elHasta = document.getElementById('fechaHasta');
    if (elDesde) elDesde.value = desdeDefault;
    if (elHasta) elHasta.value = hoy;

    function runFilter() {
      const desde = elDesde && elDesde.value ? elDesde.value : null;
      const hasta = elHasta && elHasta.value ? elHasta.value : null;
      render(desde, hasta, ventasCache);
    }

    function loadData() {
      if (!window.Api) {
        ventasCache = getVentasLocal();
        runFilter();
        return;
      }
      window.Api
        .get('/ventas_list.php')
        .then(function (json) {
          ventasCache = mapApiVentas(json.data || []);
          runFilter();
        })
        .catch(function () {
          ventasCache = getVentasLocal();
          runFilter();
        });
    }

    loadData();

    document.getElementById('btnFiltrar')?.addEventListener('click', runFilter);

    document.getElementById('btnLimpiar')?.addEventListener('click', function () {
      if (elDesde) elDesde.value = '';
      if (elHasta) elHasta.value = '';
      render(null, null, ventasCache);
    });
  });
})();
