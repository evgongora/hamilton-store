/**
 * api.js - Fetch al backend (requiere window.API_BASE desde footer o layout tienda).
 */
(function (global) {
  'use strict';

  function base() {
    return typeof global.API_BASE === 'string' ? global.API_BASE.replace(/\/$/, '') : '';
  }

  function url(path) {
    const p = path.startsWith('/') ? path : '/' + path;
    return base() + p;
  }

  global.Api = {
    baseUrl: base,

    async get(path) {
      const res = await fetch(url(path), {
        credentials: 'same-origin',
        headers: { Accept: 'application/json' },
      });
      const data = await res.json().catch(function () {
        return {};
      });
      if (!res.ok) {
        const msg = data.error || data.message || 'Error en la solicitud';
        throw new Error(msg);
      }
      return data;
    },

    async post(path, data) {
      const res = await fetch(url(path), {
        method: 'POST',
        credentials: 'same-origin',
        headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
        body: JSON.stringify(data),
      });
      const out = await res.json().catch(function () {
        return {};
      });
      if (!res.ok) {
        const msg = out.error || out.message || 'Error en la solicitud';
        throw new Error(msg);
      }
      return out;
    },
  };
})(typeof window !== 'undefined' ? window : globalThis);
