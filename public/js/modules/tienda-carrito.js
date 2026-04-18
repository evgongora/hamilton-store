/**
 * tienda-carrito.js - Carrito de compras para la tienda pública (localStorage)
 */
(function () {
  'use strict';

  const STORAGE_KEY = 'hamilton_tienda_carrito';
  const basePath = '/hamilton-store/public';

  function puedeComprarTienda() {
    return window.HAMILTON_TIENDA_PUEDE_COMPRAR === true;
  }

  /**
   * El carrito vive en localStorage para cualquier visitante (demo / armar pedido).
   * Solo rol cliente puede pagar en checkout; no vaciar el carrito al navegar sin sesión.
   */
  function carritoPuedePersistir() {
    return true;
  }

  window.TiendaCarrito = {
    getItems() {
      return JSON.parse(localStorage.getItem(STORAGE_KEY) || '[]');
    },

    save(items) {
      if (!carritoPuedePersistir()) return;
      localStorage.setItem(STORAGE_KEY, JSON.stringify(items));
      this.updateBadge();
      window.dispatchEvent(new CustomEvent('carrito-changed', { detail: { items } }));
    },

    add(producto, cantidad) {
      if (!carritoPuedePersistir()) return;
      const qty = Math.max(1, parseInt(cantidad, 10) || 1);
      const maxStock = producto.cantidad ?? 999;
      let items = this.getItems();
      const found = items.find(i => i.productoId === producto.id);
      if (found) {
        found.cantidad = Math.min(found.cantidad + qty, maxStock);
      } else {
        items.push({
          productoId: producto.id,
          nombre: producto.nombre,
          precioVenta: producto.precioVenta,
          cantidad: Math.min(qty, maxStock)
        });
      }
      this.save(items);
    },

    remove(productoId) {
      if (!carritoPuedePersistir()) return;
      let items = this.getItems().filter(i => i.productoId !== productoId);
      this.save(items);
    },

    setQty(productoId, cantidad) {
      if (!carritoPuedePersistir()) return;
      let items = this.getItems();
      const item = items.find(i => i.productoId === productoId);
      if (!item) return;
      const qty = Math.max(0, parseInt(cantidad, 10) || 0);
      if (qty <= 0) {
        this.remove(productoId);
        return;
      }
      item.cantidad = qty;
      this.save(items);
    },

    clear() {
      try {
        localStorage.removeItem(STORAGE_KEY);
      } catch (e) {}
      this.updateBadge();
      window.dispatchEvent(new CustomEvent('carrito-changed', { detail: { items: [] } }));
    },

    getCount() {
      return this.getItems().reduce((sum, i) => sum + i.cantidad, 0);
    },

    getTotal() {
      return this.getItems().reduce((sum, i) => sum + (i.precioVenta * i.cantidad), 0);
    },

    updateBadge() {
      const badge = document.getElementById('cartBadge');
      if (badge) badge.textContent = this.getCount();
    }
  };

  document.addEventListener('DOMContentLoaded', function () {
    window.TiendaCarrito.updateBadge();
  });
})();
