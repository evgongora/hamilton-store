/**
 * tienda-productos.js - Carga productos y renderiza grid con "Agregar al carrito"
 */
(function () {
  'use strict';

  const basePath = '/hamilton-store/public';

  function fetchProductos() {
    return fetch(basePath + '/js/mocks/productos.json').then(r => {
      if (!r.ok) throw new Error('Error cargando productos');
      return r.json();
    });
  }

  function formatMoney(n) {
    return '₡' + Number(n).toLocaleString('es-CR');
  }

  function renderProductCard(p, opt) {
    const img = opt?.img || 'https://dummyimage.com/450x300/dee2e6/6c757d.jpg&text=' + encodeURIComponent(p.nombre.substring(0, 20));
    const badge = opt?.badge || '';
    const badgeClass = opt?.badgeClass || 'bg-dark';
    const hasOferta = opt?.precioAntes;
    const maxStock = Math.max(1, p.cantidad ?? 99);
    return `
      <div class="col mb-5" data-product-id="${p.id}">
        <div class="card h-100">
          ${badge ? `<span class="badge ${badgeClass} position-absolute" style="top: 0.5rem; right: 0.5rem">${badge}</span>` : ''}
          <img class="card-img-top" src="${img}" alt="${escapeHtml(p.nombre)}" />
          <div class="card-body p-4">
            <div class="text-center">
              <h5 class="fw-bolder">${escapeHtml(p.nombre)}</h5>
              ${hasOferta ? `<span class="text-muted text-decoration-line-through me-2">${formatMoney(opt.precioAntes)}</span>` : ''}
              <span class="fw-bold">${formatMoney(p.precioVenta)}</span>
            </div>
          </div>
          <div class="card-footer p-4 pt-0 border-top-0 bg-transparent">
            <div class="product-quantity-stepper" data-product-id="${p.id}">
              <label class="form-label small text-muted mb-1">Cantidad</label>
              <div class="input-group input-group-sm quantity-input-group">
                <button type="button" class="btn btn-outline-secondary btn-minus" aria-label="Reducir">−</button>
                <input type="number" class="form-control qty-input text-center" value="1" min="1" max="${maxStock}" aria-label="Cantidad a comprar">
                <button type="button" class="btn btn-outline-secondary btn-plus" aria-label="Aumentar">+</button>
              </div>
            </div>
            <div class="text-center mt-2">
              <button type="button" class="btn btn-outline-dark mt-auto add-to-cart-btn w-100" data-product-id="${p.id}">
                Agregar al carrito
              </button>
            </div>
          </div>
        </div>
      </div>
    `;
  }

  function escapeHtml(s) {
    const div = document.createElement('div');
    div.textContent = s;
    return div.innerHTML;
  }

  function wireAddToCart(container, productos) {
    if (!container || !window.TiendaCarrito) return;

    container.querySelectorAll('.product-quantity-stepper').forEach(stepper => {
      const col = stepper.closest('.col.mb-5');
      const productId = parseInt(col?.dataset?.productId, 10);
      const p = productos.find(x => x.id === productId);
      if (!p) return;

      const qtyInput = stepper.querySelector('.qty-input');
      const btnMinus = stepper.querySelector('.btn-minus');
      const btnPlus = stepper.querySelector('.btn-plus');
      const addBtn = col?.querySelector('.add-to-cart-btn');

      const max = p.cantidad ?? 99;

      function updateQty(val) {
        let n = parseInt(qtyInput.value, 10) || 1;
        n = Math.max(1, Math.min(max, n + val));
        qtyInput.value = n;
        btnMinus.disabled = n <= 1;
        btnPlus.disabled = n >= max;
      }

      if (btnMinus) {
        btnMinus.addEventListener('click', () => updateQty(-1));
      }
      if (btnPlus) {
        btnPlus.addEventListener('click', () => updateQty(1));
      }
      updateQty(0);
      if (qtyInput) {
        qtyInput.addEventListener('change', function () {
          let n = parseInt(this.value, 10) || 1;
          n = Math.max(1, Math.min(max, n));
          this.value = n;
          btnMinus.disabled = n <= 1;
          btnPlus.disabled = n >= max;
        });
      }

      if (addBtn) {
        addBtn.addEventListener('click', function () {
          const qty = parseInt(qtyInput?.value, 10) || 1;
          const finalQty = Math.max(1, Math.min(max, qty));
          window.TiendaCarrito.add(p, finalQty);
          this.textContent = 'Agregado ✓';
          this.classList.add('btn-success');
          this.classList.remove('btn-outline-dark');
          setTimeout(() => {
            this.textContent = 'Agregar al carrito';
            this.classList.remove('btn-success');
            this.classList.add('btn-outline-dark');
          }, 800);
        });
      }
    });
  }

  window.TiendaProductos = {
    async renderGrid(containerId, options) {
      const container = document.getElementById(containerId);
      if (!container) return [];
      const productos = await fetchProductos();
      const badges = options?.badges || [];
      let html = '';
      productos.forEach((p, i) => {
        const opt = badges[i] || {};
        html += renderProductCard(p, opt);
      });
      container.innerHTML = html;
      wireAddToCart(container, productos);
      setupSearch(container);
      return productos;
    }
  };

  function setupSearch(container) {
    const searchInput = document.getElementById('productSearchInput');
    if (!searchInput || !container) return;
    searchInput.addEventListener('input', function () {
      const q = this.value.trim().toLowerCase();
      container.querySelectorAll('.col.mb-5').forEach(col => {
        const title = col.querySelector('.fw-bolder');
        const text = title ? title.textContent.toLowerCase() : '';
        col.style.display = !q || text.includes(q) ? '' : 'none';
      });
    });
  }
})();
