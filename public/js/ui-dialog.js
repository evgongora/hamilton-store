/**
 * Diálogos de aplicación (Bootstrap modal). Sustituye alert/confirm nativos.
 * Requiere bootstrap.bundle (global bootstrap.Modal).
 */
(function (global) {
  'use strict';

  function ensureAlertModal() {
    if (document.getElementById('hamilton-ui-dialog-alert')) {
      return;
    }
    var wrap = document.createElement('div');
    wrap.innerHTML =
      '<div class="modal fade" id="hamilton-ui-dialog-alert" tabindex="-1" aria-hidden="true">' +
      '<div class="modal-dialog modal-dialog-centered">' +
      '<div class="modal-content">' +
      '<div class="modal-header border-0 pb-0">' +
      '<h5 class="modal-title hamilton-dialog-title">Aviso</h5>' +
      '<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>' +
      '</div>' +
      '<div class="modal-body hamilton-dialog-body pt-0"></div>' +
      '<div class="modal-footer border-0 pt-0">' +
      '<button type="button" class="btn btn-primary" data-bs-dismiss="modal">Aceptar</button>' +
      '</div></div></div></div>';
    document.body.appendChild(wrap.firstElementChild);
  }

  function ensureConfirmModal() {
    if (document.getElementById('hamilton-ui-dialog-confirm')) {
      return;
    }
    var wrap = document.createElement('div');
    wrap.innerHTML =
      '<div class="modal fade" id="hamilton-ui-dialog-confirm" tabindex="-1" data-bs-backdrop="static" aria-hidden="true">' +
      '<div class="modal-dialog modal-dialog-centered">' +
      '<div class="modal-content">' +
      '<div class="modal-header border-0 pb-0">' +
      '<h5 class="modal-title hamilton-dialog-title">Confirmar</h5>' +
      '<button type="button" class="btn-close hamilton-dialog-close" aria-label="Cerrar"></button>' +
      '</div>' +
      '<div class="modal-body hamilton-dialog-body pt-0"></div>' +
      '<div class="modal-footer border-0 pt-0">' +
      '<button type="button" class="btn btn-outline-secondary hamilton-dialog-cancel">Cancelar</button>' +
      '<button type="button" class="btn btn-primary hamilton-dialog-ok">Aceptar</button>' +
      '</div></div></div></div>';
    document.body.appendChild(wrap.firstElementChild);
  }

  /**
   * @param {string} message
   * @param {{ title?: string }} [opts]
   * @returns {Promise<void>}
   */
  function uiAlert(message, opts) {
    opts = opts || {};
    if (typeof bootstrap === 'undefined' || !bootstrap.Modal) {
      global.alert(String(message));
      return Promise.resolve();
    }
    ensureAlertModal();
    var el = document.getElementById('hamilton-ui-dialog-alert');
    el.querySelector('.hamilton-dialog-title').textContent = opts.title || 'Aviso';
    el.querySelector('.hamilton-dialog-body').textContent = String(message);
    var modal = bootstrap.Modal.getOrCreateInstance(el);
    return new Promise(function (resolve) {
      el.addEventListener(
        'hidden.bs.modal',
        function onHidden() {
          el.removeEventListener('hidden.bs.modal', onHidden);
          resolve();
        },
        { once: true }
      );
      modal.show();
    });
  }

  /**
   * @param {string} message
   * @param {{ title?: string }} [opts]
   * @returns {Promise<boolean>}
   */
  function uiConfirm(message, opts) {
    opts = opts || {};
    if (typeof bootstrap === 'undefined' || !bootstrap.Modal) {
      return Promise.resolve(global.confirm(String(message)));
    }
    ensureConfirmModal();
    var el = document.getElementById('hamilton-ui-dialog-confirm');
    el.querySelector('.hamilton-dialog-title').textContent = opts.title || 'Confirmar';
    el.querySelector('.hamilton-dialog-body').textContent = String(message);
    var modal = bootstrap.Modal.getOrCreateInstance(el);
    var btnOk = el.querySelector('.hamilton-dialog-ok');
    var btnCancel = el.querySelector('.hamilton-dialog-cancel');
    var btnClose = el.querySelector('.hamilton-dialog-close');

    return new Promise(function (resolve) {
      var done = false;

      function finish(v) {
        if (done) return;
        done = true;
        resolve(v);
      }

      function cleanup() {
        el.removeEventListener('hidden.bs.modal', onBackdrop);
        btnOk.onclick = null;
        btnCancel.onclick = null;
        btnClose.onclick = null;
      }

      function onBackdrop() {
        cleanup();
        finish(false);
      }

      el.addEventListener('hidden.bs.modal', onBackdrop);

      btnOk.onclick = function () {
        cleanup();
        modal.hide();
        finish(true);
      };
      btnCancel.onclick = function () {
        cleanup();
        modal.hide();
        finish(false);
      };
      btnClose.onclick = function () {
        cleanup();
        modal.hide();
        finish(false);
      };

      modal.show();
    });
  }

  global.UiDialog = {
    alert: uiAlert,
    confirm: uiConfirm,
  };
})(typeof window !== 'undefined' ? window : globalThis);
