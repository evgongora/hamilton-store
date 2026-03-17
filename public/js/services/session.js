/**
 * session.js - Manejo de sesión en el frontend (si se usa JS para estado)
 */

const Session = {
  getRole() {
    return document.body.dataset.role || '';
  },
  getUser() {
    return document.body.dataset.user || '';
  },
};
