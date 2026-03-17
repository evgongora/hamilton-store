/**
 * api.js - Fetch wrapper para comunicación con el backend
 */

const Api = {
  baseUrl: '',

  async get(endpoint) {
    const res = await fetch(this.baseUrl + endpoint);
    if (!res.ok) throw new Error('Error en la solicitud');
    return res.json();
  },

  async post(endpoint, data) {
    const res = await fetch(this.baseUrl + endpoint, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(data),
    });
    if (!res.ok) throw new Error('Error en la solicitud');
    return res.json();
  },
};
