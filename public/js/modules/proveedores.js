/**
 * proveedores.js — Proveedores y contactos vía Oracle (pkg_proveedores, pkg_contactos_proveedores).
 */
(function () {
  'use strict';

  function uiAlert(msg, title) {
    if (window.UiDialog && window.UiDialog.alert) {
      return window.UiDialog.alert(String(msg), { title: title || 'Proveedores' });
    }
    alert(msg);
    return Promise.resolve();
  }

  function uiConfirm(msg, title) {
    if (window.UiDialog && window.UiDialog.confirm) {
      return window.UiDialog.confirm(String(msg), { title: title || 'Confirmar' });
    }
    return Promise.resolve(confirm(msg));
  }

  var selectedProviderId = null;
  var cachedProviders = [];
  var estadosList = [];
  var contactsCache = {};

  document.addEventListener('DOMContentLoaded', function () {
    var grid = document.getElementById('proveedoresGrid');
    if (!grid) return;

    var currentRole = String(document.body.dataset.currentRole || '').trim().toLowerCase();
    var isAdmin = currentRole === 'admin';
    var count = document.getElementById('proveedoresCount');
    var empty = document.getElementById('proveedoresEmpty');
    var loading = document.getElementById('proveedoresLoading');
    var content = document.getElementById('proveedoresContent');
    var contactsPanel = document.getElementById('contactosPanel');
    var providerModalElement = document.getElementById('providerModal');
    var contactModalElement = document.getElementById('contactModal');
    var providerForm = document.getElementById('providerForm');
    var contactForm = document.getElementById('contactForm');
    var providerModal = providerModalElement ? new bootstrap.Modal(providerModalElement) : null;
    var contactModal = contactModalElement ? new bootstrap.Modal(contactModalElement) : null;
    var providerModalTitle = document.getElementById('providerModalTitle');
    var providerModalDescription = document.getElementById('providerModalDescription');
    var providerSubmitButton = document.getElementById('providerSubmitButton');
    var contactModalTitle = document.getElementById('contactModalTitle');
    var contactModalDescription = document.getElementById('contactModalDescription');
    var contactSubmitButton = document.getElementById('contactSubmitButton');
    var newProviderButton = document.getElementById('btnNuevoProveedor');

    function escapeHtml(value) {
      return String(value == null ? '' : value)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');
    }

    function getContactsLabel(total) {
      return total === 1 ? '1 contacto' : total + ' contactos';
    }

    function fillEstadosSelect() {
      var sel = document.getElementById('providerIdEstado');
      if (!sel) return;
      var cur = sel.value;
      sel.innerHTML = '';
      estadosList.forEach(function (e) {
        var opt = document.createElement('option');
        opt.value = String(e.id);
        opt.textContent = e.nombre;
        sel.appendChild(opt);
      });
      if (cur && sel.querySelector('option[value="' + cur + '"]')) sel.value = cur;
      else if (estadosList.length) sel.value = String(estadosList[0].id);
    }

    function getSelectedProvider() {
      if (!cachedProviders.length) return null;
      return (
        cachedProviders.find(function (item) {
          return String(item.id) === String(selectedProviderId);
        }) || cachedProviders[0]
      );
    }

    function getProviderById(providerId) {
      return (
        cachedProviders.find(function (item) {
          return String(item.id) === String(providerId);
        }) || null
      );
    }

    function renderContacts(provider, contactos) {
      if (!provider) {
        contactsPanel.innerHTML =
          '<div class="text-muted">Seleccione un proveedor para ver sus contactos.</div>';
        return;
      }

      var list = contactos || [];
      var contactsMarkup = list.length
        ? list
            .map(function (c) {
              return [
                '<div class="border rounded-3 bg-white p-3 mb-3">',
                '<div class="d-flex justify-content-between align-items-start gap-3 mb-2">',
                '<div>',
                '<div class="fw-semibold text-dark">' +
                  escapeHtml(c.nombre || '') +
                  ' ' +
                  escapeHtml(c.apellido || '') +
                  '</div>',
                '</div>',
                isAdmin
                  ? '<div class="btn-group btn-group-sm"><button type="button" class="btn btn-outline-secondary contact-edit" data-provider-id="' +
                    escapeHtml(provider.id) +
                    '" data-contact-id="' +
                    escapeHtml(c.id) +
                    '"><i class="bi bi-pencil"></i></button><button type="button" class="btn btn-outline-danger contact-delete" data-provider-id="' +
                    escapeHtml(provider.id) +
                    '" data-contact-id="' +
                    escapeHtml(c.id) +
                    '"><i class="bi bi-trash"></i></button></div>'
                  : '',
                '</div>',
                '<div class="small"><i class="bi bi-telephone me-2"></i>' +
                  escapeHtml(c.telefono || '') +
                  '</div>',
                '<div class="small"><i class="bi bi-envelope me-2"></i>' +
                  escapeHtml(c.email || '') +
                  '</div>',
                '</div>',
              ].join('');
            })
            .join('')
        : '<div class="text-muted mb-3">Este proveedor no tiene contactos registrados.</div>';

      contactsPanel.innerHTML = [
        '<div class="d-flex justify-content-between align-items-start gap-3 mb-3">',
        '<div>',
        '<h5 class="mb-1">' + escapeHtml(provider.nombre || 'Proveedor') + '</h5>',
        '<div class="text-muted small">' + getContactsLabel(list.length) + '</div>',
        '<div class="text-muted small">Cédula: ' + escapeHtml(provider.cedulaJuridica || '') + '</div>',
        '</div>',
        isAdmin
          ? '<div class="btn-group btn-group-sm"><button type="button" class="btn btn-outline-secondary provider-edit" data-provider-id="' +
            escapeHtml(provider.id) +
            '"><i class="bi bi-pencil"></i></button><button type="button" class="btn btn-outline-danger provider-delete" data-provider-id="' +
            escapeHtml(provider.id) +
            '"><i class="bi bi-trash"></i></button></div>'
          : '',
        '</div>',
        isAdmin
          ? '<button type="button" class="btn btn-dark btn-sm mb-3" id="btnNuevoContacto" data-provider-id="' +
            escapeHtml(provider.id) +
            '"><i class="bi bi-person-plus me-1"></i>Nuevo contacto</button>'
          : '',
        contactsMarkup,
      ].join('');
    }

    function fetchContactsForProvider(provider, useCache) {
      if (!window.Api || !provider) {
        renderContacts(provider, []);
        return Promise.resolve();
      }
      var pid = String(provider.id);
      if (useCache && contactsCache[pid]) {
        renderContacts(provider, contactsCache[pid]);
        return Promise.resolve(contactsCache[pid]);
      }
      contactsPanel.innerHTML =
        '<div class="text-center py-3"><div class="spinner-border spinner-border-sm text-secondary" role="status"></div></div>';
      return window.Api
        .get('/contactos_proveedor_list.php?proveedorId=' + encodeURIComponent(pid))
        .then(function (json) {
          var rows = json.data || [];
          contactsCache[pid] = rows;
          renderContacts(provider, rows);
          return rows;
        })
        .catch(function () {
          renderContacts(provider, []);
        });
    }

    function renderCards() {
      grid.innerHTML = '';
      count.textContent = cachedProviders.length + ' proveedor(es)';

      if (!cachedProviders.length) {
        loading.classList.add('d-none');
        content.classList.add('d-none');
        empty.classList.remove('d-none');
        renderContacts(null, []);
        return;
      }

      if (selectedProviderId == null || !getSelectedProvider()) {
        selectedProviderId = cachedProviders[0].id;
      }

      var provider = getSelectedProvider();
      selectedProviderId = provider.id;

      loading.classList.add('d-none');
      empty.classList.add('d-none');
      content.classList.remove('d-none');

      cachedProviders.forEach(function (item) {
        var col = document.createElement('div');
        var isSelected = String(item.id) === String(selectedProviderId);
        col.className = 'col-12 col-md-6';
        col.innerHTML = [
          '<button type="button" class="card h-100 w-100 text-start border-2 shadow-sm proveedor-card',
          isSelected ? ' border-dark' : ' border-light',
          '" data-provider-id="',
          escapeHtml(item.id),
          '" style="background:',
          isSelected ? '#f8f9fa' : '#ffffff',
          ';">',
          '<div class="card-body">',
          '<div class="d-flex justify-content-between align-items-start gap-3 mb-3">',
          '<div>',
          '<div class="fw-semibold text-dark">' + escapeHtml(item.nombre || '') + '</div>',
          '<div class="text-muted small mt-1">' + escapeHtml(item.cedulaJuridica || '') + '</div>',
          '</div>',
          '<i class="bi bi-chevron-right text-muted"></i>',
          '</div>',
          '<div class="small text-muted">' + escapeHtml(item.estado || '') + '</div>',
          '</div>',
          '</button>',
        ].join('');
        grid.appendChild(col);
      });

      return fetchContactsForProvider(provider, true);
    }

    function renderLoadingState() {
      loading.classList.remove('d-none');
      empty.classList.add('d-none');
      content.classList.add('d-none');
      grid.innerHTML = '';
      contactsPanel.innerHTML = '<div class="text-muted">Cargando…</div>';
      count.textContent = 'Cargando…';
    }

    function renderErrorState() {
      loading.classList.add('d-none');
      empty.classList.add('d-none');
      content.classList.remove('d-none');
      grid.innerHTML =
        '<div class="col-12"><div class="alert alert-danger mb-0">No se pudieron cargar los proveedores.</div></div>';
      contactsPanel.innerHTML = '<div class="text-muted">Sin datos.</div>';
      count.textContent = '0 proveedores';
    }

    function resetProviderForm() {
      if (!providerForm) return;
      providerForm.reset();
      document.getElementById('providerId').value = '';
      fillEstadosSelect();
      if (providerModalTitle) providerModalTitle.textContent = 'Nuevo proveedor';
      if (providerModalDescription) providerModalDescription.textContent = 'Registra un proveedor nuevo.';
      if (providerSubmitButton) providerSubmitButton.textContent = 'Guardar proveedor';
    }

    function resetContactForm(providerId) {
      if (!contactForm) return;
      contactForm.reset();
      document.getElementById('contactId').value = '';
      document.getElementById('contactProviderId').value = providerId != null ? String(providerId) : '';
      if (contactModalTitle) contactModalTitle.textContent = 'Nuevo contacto';
      if (contactModalDescription)
        contactModalDescription.textContent = 'Registra un contacto para el proveedor seleccionado.';
      if (contactSubmitButton) contactSubmitButton.textContent = 'Guardar contacto';
    }

    function openProviderModal(providerId) {
      if (!isAdmin || !providerModal) return;
      resetProviderForm();
      if (providerId != null) {
        var p = getProviderById(providerId);
        if (p) {
          document.getElementById('providerId').value = String(p.id);
          document.getElementById('providerNombre').value = p.nombre || '';
          document.getElementById('providerCedulaJuridica').value = p.cedulaJuridica || '';
          document.getElementById('providerPaginaWeb').value = p.paginaWeb || '';
          fillEstadosSelect();
          document.getElementById('providerIdEstado').value = String(p.idEstado || '');
          if (providerModalTitle) providerModalTitle.textContent = 'Editar proveedor';
          if (providerModalDescription)
            providerModalDescription.textContent = 'Actualice los datos del proveedor.';
          if (providerSubmitButton) providerSubmitButton.textContent = 'Actualizar proveedor';
        }
      }
      providerModal.show();
    }

    function openContactModal(providerId, contactId) {
      var provider = getProviderById(providerId);
      if (!isAdmin || !contactModal || !provider) return;
      resetContactForm(provider.id);
      if (contactId != null) {
        var pid = String(provider.id);
        var list = contactsCache[pid] || [];
        var c = list.find(function (x) {
          return String(x.id) === String(contactId);
        });
        if (c) {
          document.getElementById('contactId').value = String(c.id);
          document.getElementById('contactNombre').value = c.nombre || '';
          document.getElementById('contactApellido').value = c.apellido || '';
          document.getElementById('contactEmail').value = c.email || '';
          document.getElementById('contactTelefono').value = c.telefono || '';
          if (contactModalTitle) contactModalTitle.textContent = 'Editar contacto';
          if (contactModalDescription) contactModalDescription.textContent = 'Actualice los datos del contacto.';
          if (contactSubmitButton) contactSubmitButton.textContent = 'Actualizar contacto';
        }
      }
      contactModal.show();
    }

    function saveProvider(event) {
      event.preventDefault();
      if (!window.Api) {
        void uiAlert('API no disponible.', 'Error');
        return;
      }
      var id = document.getElementById('providerId').value.trim();
      var nombre = document.getElementById('providerNombre').value.trim();
      var cedula = document.getElementById('providerCedulaJuridica').value.trim();
      var web = document.getElementById('providerPaginaWeb').value.trim();
      var idEst = parseInt(document.getElementById('providerIdEstado').value, 10);
      var Vp = window.HamiltonValidation;
      if (Vp && typeof Vp.proveedorFormMensaje === 'function') {
        var errPr = Vp.proveedorFormMensaje(nombre, cedula, web, idEst);
        if (errPr) {
          void uiAlert(errPr);
          return;
        }
      } else if (!nombre || !idEst) {
        void uiAlert('Complete nombre y estado.');
        return;
      }
      var body = {
        nombre: nombre,
        cedulaJuridica: cedula,
        paginaWeb: web,
        idEstado: idEst,
      };
      if (id) {
        body.action = 'update';
        body.id = parseInt(id, 10);
      } else {
        body.action = 'insert';
      }
      window.Api
        .post('/proveedores_save.php', body)
        .then(function () {
          providerModal.hide();
          contactsCache = {};
          return loadProviders();
        })
        .then(function () {
          return uiAlert(body.action === 'insert' ? 'Proveedor creado.' : 'Proveedor actualizado.', 'Listo');
        })
        .catch(function (e) {
          void uiAlert(String(e.message || e), 'Error');
        });
    }

    function saveContact(event) {
      event.preventDefault();
      if (!window.Api) return;
      var cid = document.getElementById('contactId').value.trim();
      var pid = document.getElementById('contactProviderId').value;
      var nombre = document.getElementById('contactNombre').value.trim();
      var apellido = document.getElementById('contactApellido').value.trim();
      var email = document.getElementById('contactEmail').value.trim();
      var telefono = document.getElementById('contactTelefono').value.trim();
      if (!pid) {
        void uiAlert('Seleccione un proveedor.');
        return;
      }
      var Vc = window.HamiltonValidation;
      if (Vc && typeof Vc.contactoProveedorFormMensaje === 'function') {
        var errCt = Vc.contactoProveedorFormMensaje(nombre, apellido, email, telefono);
        if (errCt) {
          void uiAlert(errCt);
          return;
        }
      } else if (!nombre || !apellido || !email || !telefono) {
        void uiAlert('Complete todos los campos del contacto.');
        return;
      }
      var body = {
        nombre: nombre,
        apellido: apellido,
        email: email,
        telefono: telefono,
        proveedorId: parseInt(pid, 10),
      };
      if (cid) {
        body.action = 'update';
        body.id = parseInt(cid, 10);
      } else {
        body.action = 'insert';
      }
      window.Api
        .post('/contactos_proveedor_save.php', body)
        .then(function () {
          contactModal.hide();
          delete contactsCache[String(pid)];
          return fetchContactsForProvider(getProviderById(pid), false);
        })
        .then(function () {
          return uiAlert(
            body.action === 'insert' ? 'Contacto creado.' : 'Contacto actualizado.',
            'Listo'
          );
        })
        .catch(function (e) {
          void uiAlert(String(e.message || e), 'Error');
        });
    }

    function deleteProvider(providerId) {
      uiConfirm('¿Eliminar este proveedor y sus contactos asociados?', 'Eliminar').then(function (ok) {
        if (!ok || !window.Api) return;
        window.Api
          .post('/proveedores_save.php', { action: 'delete', id: parseInt(providerId, 10) })
          .then(function () {
            delete contactsCache[String(providerId)];
            selectedProviderId = null;
            return loadProviders();
          })
          .then(function () {
            return uiAlert('Proveedor eliminado.', 'Listo');
          })
          .catch(function (e) {
            void uiAlert(String(e.message || e), 'Error');
          });
      });
    }

    function deleteContact(providerId, contactId) {
      uiConfirm('¿Eliminar este contacto?', 'Eliminar').then(function (ok) {
        if (!ok || !window.Api) return;
        window.Api
          .post('/contactos_proveedor_save.php', { action: 'delete', id: parseInt(contactId, 10) })
          .then(function () {
            delete contactsCache[String(providerId)];
            return fetchContactsForProvider(getProviderById(providerId), false);
          })
          .then(function () {
            return uiAlert('Contacto eliminado.', 'Listo');
          })
          .catch(function (e) {
            void uiAlert(String(e.message || e), 'Error');
          });
      });
    }

    function loadProviders() {
      if (!window.Api) {
        renderErrorState();
        return Promise.resolve();
      }
      renderLoadingState();
      return Promise.all([
        window.Api.get('/proveedores_list.php'),
        window.Api.get('/estados_list.php').catch(function () {
          return { data: [] };
        }),
      ])
        .then(function (results) {
          cachedProviders = results[0].data || [];
          estadosList = results[1].data || [];
          fillEstadosSelect();
          return renderCards();
        })
        .catch(function () {
          renderErrorState();
        });
    }

    if (isAdmin && newProviderButton) {
      newProviderButton.addEventListener('click', function () {
        openProviderModal(null);
      });
    }

    if (isAdmin && providerForm) {
      providerForm.addEventListener('submit', saveProvider);
    }

    if (isAdmin && contactForm) {
      contactForm.addEventListener('submit', saveContact);
    }

    grid.addEventListener('click', function (event) {
      var card = event.target.closest('[data-provider-id]');
      if (!card || !card.classList.contains('proveedor-card')) return;
      selectedProviderId = card.getAttribute('data-provider-id');
      renderCards();
    });

    contactsPanel.addEventListener('click', function (event) {
      if (!isAdmin) return;
      var newContactButton = event.target.closest('#btnNuevoContacto');
      var editProviderButton = event.target.closest('.provider-edit');
      var deleteProviderButton = event.target.closest('.provider-delete');
      var editContactButton = event.target.closest('.contact-edit');
      var deleteContactButton = event.target.closest('.contact-delete');

      if (newContactButton) {
        openContactModal(newContactButton.getAttribute('data-provider-id'), null);
        return;
      }
      if (editProviderButton) {
        openProviderModal(editProviderButton.getAttribute('data-provider-id'));
        return;
      }
      if (deleteProviderButton) {
        deleteProvider(deleteProviderButton.getAttribute('data-provider-id'));
        return;
      }
      if (editContactButton) {
        openContactModal(
          editContactButton.getAttribute('data-provider-id'),
          editContactButton.getAttribute('data-contact-id')
        );
        return;
      }
      if (deleteContactButton) {
        deleteContact(
          deleteContactButton.getAttribute('data-provider-id'),
          deleteContactButton.getAttribute('data-contact-id')
        );
      }
    });

    loadProviders();
  });
})();
