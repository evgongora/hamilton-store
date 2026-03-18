/**
 * pagos.js - Registrar pagos y asociarlos a ventas (mock/localStorage)
 * Estructura alineada con BD: pagos.metodosPagoIdMetodoPago
 */
(function () {
  'use strict';

  const STORAGE_KEY = 'hamilton_ventas';
  const basePath = (document.body.dataset.basePath || '/hamilton-store/public').replace(/\/$/, '');

  function getVentas() {
    return JSON.parse(localStorage.getItem(STORAGE_KEY) || '[]');
  }

  function saveVentas(ventas) {
    localStorage.setItem(STORAGE_KEY, JSON.stringify(ventas));
  }

  function formatMoney(n) {
    return '₡' + Number(n).toLocaleString('es-CR');
  }

  function getClienteNombre(venta) {
    if (venta.clienteNombre) return venta.clienteNombre;
    if (venta.clientesIdCliente) return 'Cliente #' + venta.clientesIdCliente;
    if (venta.clienteId) return 'Cliente #' + venta.clienteId;
    return 'Sin cliente';
  }

  function getPagado(venta) {
    return (venta.pagos || []).reduce((sum, p) => sum + p.monto, 0);
  }

  function getPendiente(venta) {
    return venta.total - getPagado(venta);
  }

  function renderVentaSelect() {
    const ventas = getVentas();
    const sel = document.getElementById('ventaSelect');
    sel.innerHTML = '<option value="">-- Seleccionar venta --</option>';

    ventas.forEach(v => {
      const pend = getPendiente(v);
      if (pend <= 0) return;
      const opt = document.createElement('option');
      opt.value = v.id;
      opt.textContent = `#${v.id} - ${getClienteNombre(v)} - ${formatMoney(v.total)} (pend: ${formatMoney(pend)})`;
      sel.appendChild(opt);
    });
  }

  function loadMetodosPago() {
    const sel = document.getElementById('metodoPago');
    if (!sel) return Promise.resolve();
    return fetch(basePath + '/js/mocks/metodos_pago.json')
      .then(r => r.ok ? r.json() : [])
      .then(data => {
        sel.innerHTML = '<option value="">-- Método --</option>';
        data.forEach(m => {
          const opt = document.createElement('option');
          opt.value = m.id;
          opt.textContent = m.nombre;
          sel.appendChild(opt);
        });
        return data;
      })
      .catch(() => {});
  }

  function updateVentaDetalle(venta) {
    const detalle = document.getElementById('ventaDetalle');
    const btn = document.getElementById('btnRegistrarPago');

    if (!venta) {
      detalle.style.display = 'none';
      btn.disabled = true;
      return;
    }

    const pagado = getPagado(venta);
    const pendiente = getPendiente(venta);

    document.getElementById('ventaTotal').textContent = formatMoney(venta.total);
    document.getElementById('ventaPagado').textContent = formatMoney(pagado);
    document.getElementById('ventaPendiente').textContent = formatMoney(pendiente);
    detalle.style.display = 'block';
    btn.disabled = pendiente <= 0;
  }

  function registrarPago() {
    const ventaId = document.getElementById('ventaSelect').value;
    const metodoPagoId = document.getElementById('metodoPago').value;
    const montoStr = document.getElementById('montoPago').value;

    if (!ventaId) return;
    const monto = parseFloat(montoStr);
    if (isNaN(monto) || monto <= 0) {
      alert('Ingrese un monto v&aacute;lido');
      return;
    }
    if (!metodoPagoId) {
      alert('Seleccione un m&eacute;todo de pago');
      return;
    }

    const ventas = getVentas();
    const idx = ventas.findIndex(v => String(v.id) === ventaId);
    if (idx < 0) {
      alert('Venta no encontrada');
      return;
    }

    const venta = ventas[idx];
    const pendiente = getPendiente(venta);
    if (monto > pendiente) {
      alert('El monto no puede ser mayor al pendiente: ' + formatMoney(pendiente));
      return;
    }

    venta.pagos = venta.pagos || [];
    venta.pagos.push({
      monto: monto,
      fechaPago: new Date().toISOString(),
      metodosPagoIdMetodoPago: parseInt(metodoPagoId, 10)
    });
    saveVentas(ventas);

    document.getElementById('montoPago').value = '';
    updateVentaDetalle(venta);
    renderVentaSelect();

    if (getPendiente(venta) <= 0) {
      document.getElementById('ventaSelect').value = '';
      updateVentaDetalle(null);
      alert('Pago registrado. Venta saldada.');
    } else {
      alert('Pago registrado correctamente.');
    }
  }

  document.addEventListener('DOMContentLoaded', function () {
    loadMetodosPago().then(() => {});
    renderVentaSelect();

    document.getElementById('ventaSelect')?.addEventListener('change', function () {
      const ventaId = this.value;
      if (!ventaId) {
        updateVentaDetalle(null);
        return;
      }
      const ventas = getVentas();
      const venta = ventas.find(v => String(v.id) === ventaId);
      updateVentaDetalle(venta || null);
    });

    document.getElementById('btnRegistrarPago')?.addEventListener('click', registrarPago);
  });
})();
