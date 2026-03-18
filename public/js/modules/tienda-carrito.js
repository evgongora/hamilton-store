/**
 * tienda-carrito.js - Carrito de compras para la tienda pública (localStorage)
 */
(function () {
  'use strict';

  const STORAGE_KEY = 'hamilton_tienda_carrito';
  const basePath = '/hamilton-store/public';

  window.TiendaCarrito = {
    getItems() {
      return JSON.parse(localStorage.getItem(STORAGE_KEY) || '[]');
    },

    save(items) {
      localStorage.setItem(STORAGE_KEY, JSON.stringify(items));
      this.updateBadge();
      window.dispatchEvent(new CustomEvent('carrito-changed', { detail: { items } }));
    },

    add(producto, cantidad) {
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
      let items = this.getItems().filter(i => i.productoId !== productoId);
      this.save(items);
    },

    setQty(productoId, cantidad) {
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
      this.save([]);
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
