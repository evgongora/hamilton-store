(function () {
  'use strict';

  function uiAlert(msg, title) {
    if (window.UiDialog && window.UiDialog.alert) {
      return window.UiDialog.alert(String(msg), { title: title || 'Pagos' });
    }
    alert(msg);
    return Promise.resolve();
  }

  function formatMoney(n) {
    return '₡' + Number(n).toLocaleString('es-CR');
  }

  function getPendiente(v) {
    return (v.pendiente != null ? v.pendiente : v.total - (v.pagado || 0)) || 0;
  }

  function renderVentaSelect() {
    const sel = document.getElementById('ventaSelect');
    if (!sel) return;
    const prev = sel.value;
    const q = (document.getElementById('ventaFiltro') || { value: '' }).value.trim().toLowerCase();
    sel.innerHTML = '<option value="">-- Seleccionar venta --</option>';
    (ventasCache || []).forEach(function (v) {
      const pend = getPendiente(v);
      if (pend <= 0) return;
      if (q) {
        const blob = ('#' + v.id + ' ' + (v.clienteNombre || 'Cliente')).toLowerCase();
        if (blob.indexOf(q) === -1) return;
      }
      const opt = document.createElement('option');
      opt.value = String(v.id);
      opt.textContent =
        '#' +
        v.id +
        ' — ' +
        (v.clienteNombre || 'Cliente') +
        ' — ' +
        formatMoney(v.total) +
        ' (pend: ' +
        formatMoney(pend) +
        ')';
      sel.appendChild(opt);
    });
    if (prev && sel.querySelector('option[value="' + prev + '"]')) {
      sel.value = prev;
    }
  }

  function loadVentas() {
    if (!window.Api) return Promise.resolve([]);
    return window.Api
      .get('/ventas_list.php')
      .then(function (json) {
        const data = json.data || [];
        ventasCache = data;
        renderVentaSelect();
        return data;
      })
      .catch(function () {
        ventasCache = [];
        renderVentaSelect();
        return [];
      });
  }

  function loadMetodosPago() {
    const sel = document.getElementById('metodoPago');
    if (!sel) return Promise.resolve([]);
    if (!window.Api) return Promise.resolve([]);
    return window.Api
      .get('/metodos_pago_list.php')
      .then(function (json) {
        const data = json.data || [];
        sel.innerHTML = '<option value="">-- Método --</option>';
        data.forEach(function (m) {
          const opt = document.createElement('option');
          opt.value = String(m.id);
          opt.textContent = m.nombre;
          sel.appendChild(opt);
        });
        return data;
      })
      .catch(function () {
        sel.innerHTML = '<option value="">Error al cargar</option>';
        return [];
      });
  }

  function updateVentaDetalle(venta) {
    const detalle = document.getElementById('ventaDetalle');
    const btn = document.getElementById('btnRegistrarPago');
    if (!detalle || !btn) return;

    if (!venta) {
      detalle.style.display = 'none';
      btn.disabled = true;
      return;
    }

    const pagado = venta.pagado != null ? venta.pagado : 0;
    const pendiente = getPendiente(venta);

    document.getElementById('ventaTotal').textContent = formatMoney(venta.total);
    document.getElementById('ventaPagado').textContent = formatMoney(pagado);
    document.getElementById('ventaPendiente').textContent = formatMoney(pendiente);
    detalle.style.display = 'block';
    btn.disabled = pendiente <= 0;
  }

  let ventasCache = [];

  function registrarPago() {
    const ventaId = document.getElementById('ventaSelect').value;
    const metodoPagoId = document.getElementById('metodoPago').value;
    const montoStr = document.getElementById('montoPago').value;

    if (!ventaId || !window.Api) return;
    const monto = parseFloat(String(montoStr).replace(',', '.'));
    const Vp = window.HamiltonValidation;
    const montoOk =
      Vp && typeof Vp.montoPositivo === 'function'
        ? Vp.montoPositivo(monto)
        : !isNaN(monto) && isFinite(monto) && monto > 0;
    if (!montoOk) {
      void uiAlert('Ingrese un monto válido (número mayor que cero).');
      return;
    }
    if (!metodoPagoId) {
      void uiAlert('Seleccione un método de pago');
      return;
    }

    const venta = ventasCache.find(function (v) {
      return String(v.id) === ventaId;
    });
    if (!venta) {
      void uiAlert('Venta no encontrada');
      return;
    }
    const pendiente = getPendiente(venta);
    if (monto > pendiente) {
      void uiAlert('El monto no puede ser mayor al pendiente: ' + formatMoney(pendiente));
      return;
    }

    const fechaPago = new Date().toISOString().slice(0, 10);
    const btn = document.getElementById('btnRegistrarPago');
    if (btn) btn.disabled = true;

    window.Api
      .post('/pagos_create.php', {
        action: 'insert',
        monto: monto,
        fechaPago: fechaPago,
        idMetodoPago: parseInt(metodoPagoId, 10),
        idVenta: parseInt(ventaId, 10)
      })
      .then(function () {
        document.getElementById('montoPago').value = '';
        return loadVentas();
      })
      .then(function () {
        const still = ventasCache.find(function (v) {
          return String(v.id) === ventaId;
        });
        const sel = document.getElementById('ventaSelect');
        if (sel) {
          sel.value = still && getPendiente(still) > 0 ? ventaId : '';
        }
        updateVentaDetalle(still && getPendiente(still) > 0 ? still : null);
        if (!still || getPendiente(still) <= 0) {
          return uiAlert('Pago registrado. Venta saldada.', 'Listo');
        }
        return uiAlert('Pago registrado correctamente.', 'Listo');
      })
      .catch(function (e) {
        void uiAlert('Error: ' + (e.message || String(e)), 'Error');
      })
      .finally(function () {
        const vsel = document.getElementById('ventaSelect');
        const v = ventasCache.find(function (x) {
          return String(x.id) === (vsel && vsel.value);
        });
        if (btn) btn.disabled = !v || getPendiente(v) <= 0;
      });
  }

  document.addEventListener('DOMContentLoaded', function () {
    loadMetodosPago();
    loadVentas();

    document.getElementById('ventaFiltro')?.addEventListener('input', function () {
      renderVentaSelect();
      const sel = document.getElementById('ventaSelect');
      const ventaId = sel && sel.value;
      if (!ventaId) {
        updateVentaDetalle(null);
        return;
      }
      const venta = ventasCache.find(function (v) {
        return String(v.id) === ventaId;
      });
      updateVentaDetalle(venta || null);
    });

    document.getElementById('ventaSelect')?.addEventListener('change', function () {
      const ventaId = this.value;
      if (!ventaId) {
        updateVentaDetalle(null);
        return;
      }
      const venta = ventasCache.find(function (v) {
        return String(v.id) === ventaId;
      });
      updateVentaDetalle(venta || null);
    });

    document.getElementById('btnRegistrarPago')?.addEventListener('click', registrarPago);
  });
})();
