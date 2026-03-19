/**
 * proveedores.js - CRUD de proveedores y contactos con localStorage
 */
(function () {
  'use strict';

  var STORAGE_KEY = 'hamilton-store-proveedores';
  var selectedProviderId = null;
  var cachedProviders = [];

  document.addEventListener('DOMContentLoaded', function () {
    var grid = document.getElementById('proveedoresGrid');
    if (!grid) {
      return;
    }

    var basePath = (document.body.dataset.basePath || '').replace(/\/$/, '');
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
    var providerFields = {
      id: document.getElementById('providerId'),
      nombre: document.getElementById('providerNombre')
    };
    var contactFields = {
      providerId: document.getElementById('contactProviderId'),
      index: document.getElementById('contactIndex'),
      nombre: document.getElementById('contactNombre'),
      puesto: document.getElementById('contactPuesto'),
      telefono: document.getElementById('contactTelefono'),
      email: document.getElementById('contactEmail')
    };
    var providerModalTitle = document.getElementById('providerModalTitle');
    var providerModalDescription = document.getElementById('providerModalDescription');
    var providerSubmitButton = document.getElementById('providerSubmitButton');
    var contactModalTitle = document.getElementById('contactModalTitle');
    var contactModalDescription = document.getElementById('contactModalDescription');
    var contactSubmitButton = document.getElementById('contactSubmitButton');
    var newProviderButton = document.getElementById('btnNuevoProveedor');

    function readStorage() {
      try {
        var raw = localStorage.getItem(STORAGE_KEY);
        if (!raw) {
          return null;
        }
        var parsed = JSON.parse(raw);
        return Array.isArray(parsed) ? parsed : null;
      } catch (error) {
        return null;
      }
    }

    function writeStorage() {
      localStorage.setItem(STORAGE_KEY, JSON.stringify(cachedProviders));
    }

    function escapeHtml(value) {
      return String(value == null ? '' : value)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');
    }

    function normalizeContact(contacto) {
      return {
        nombre: String(contacto.nombre || contacto.contacto || '').trim(),
        telefono: String(contacto.telefono || contacto.numeroTelefono || '').trim(),
        email: String(contacto.email || '').trim(),
        puesto: String(contacto.puesto || '').trim()
      };
    }

    function normalizeProvider(provider, index) {
      var contactos = Array.isArray(provider.contactos) ? provider.contactos.map(normalizeContact) : [];
      var contactoPrincipal = normalizeContact({
        nombre: provider.contacto || provider.nombreContacto || '',
        telefono: provider.telefono || provider.numeroTelefono || '',
        email: provider.email || '',
        puesto: provider.puesto || ''
      });

      if (!contactos.length && (contactoPrincipal.nombre || contactoPrincipal.telefono || contactoPrincipal.email || contactoPrincipal.puesto)) {
        contactos = [contactoPrincipal];
      }

      contactos = contactos.filter(function (contacto) {
        return contacto.nombre || contacto.telefono || contacto.email || contacto.puesto;
      });

      return {
        id: provider.id || provider.id_proveedor || provider.proveedorId || ('proveedor-' + index),
        nombre: String(provider.nombre || provider.proveedor || '').trim(),
        contactos: contactos
      };
    }

    function getContactsLabel(total) {
      return total === 1 ? '1 contacto' : total + ' contactos';
    }

    function getSelectedProvider() {
      if (!cachedProviders.length) {
        return null;
      }

      return cachedProviders.find(function (item) {
        return String(item.id) === String(selectedProviderId);
      }) || cachedProviders[0];
    }

    function getProviderById(providerId) {
      return cachedProviders.find(function (item) {
        return String(item.id) === String(providerId);
      }) || null;
    }

    function getNextProviderId() {
      return cachedProviders.reduce(function (maxId, provider) {
        return Math.max(maxId, Number(provider.id) || 0);
      }, 0) + 1;
    }

    function renderContacts(provider) {
      if (!provider) {
        contactsPanel.innerHTML = '<div class="text-muted">Seleccione un proveedor para ver sus contactos.</div>';
        return;
      }

      var contactsMarkup = provider.contactos.length
        ? provider.contactos.map(function (contacto, index) {
            return [
              '<div class="border rounded-3 bg-white p-3 mb-3">',
              '<div class="d-flex justify-content-between align-items-start gap-3 mb-2">',
              '<div>',
              '<div class="fw-semibold text-dark">' + escapeHtml(contacto.nombre || 'Sin nombre') + '</div>',
              contacto.puesto ? '<div class="text-muted small">' + escapeHtml(contacto.puesto) + '</div>' : '',
              '</div>',
              isAdmin ? '<div class="btn-group btn-group-sm"><button type="button" class="btn btn-outline-secondary contact-edit" data-provider-id="' + escapeHtml(provider.id) + '" data-contact-index="' + escapeHtml(index) + '"><i class="bi bi-pencil"></i></button><button type="button" class="btn btn-outline-danger contact-delete" data-provider-id="' + escapeHtml(provider.id) + '" data-contact-index="' + escapeHtml(index) + '"><i class="bi bi-trash"></i></button></div>' : '',
              '</div>',
              '<div class="small"><i class="bi bi-telephone me-2"></i>' + escapeHtml(contacto.telefono || 'No registrado') + '</div>',
              '<div class="small"><i class="bi bi-envelope me-2"></i>' + escapeHtml(contacto.email || 'No registrado') + '</div>',
              '</div>'
            ].join('');
          }).join('')
        : '<div class="text-muted mb-3">Este proveedor no tiene contactos registrados.</div>';

      contactsPanel.innerHTML = [
        '<div class="d-flex justify-content-between align-items-start gap-3 mb-3">',
        '<div>',
        '<h5 class="mb-1">' + escapeHtml(provider.nombre || 'Proveedor') + '</h5>',
        '<div class="text-muted small">' + getContactsLabel(provider.contactos.length) + '</div>',
        '</div>',
        isAdmin ? '<div class="btn-group btn-group-sm"><button type="button" class="btn btn-outline-secondary provider-edit" data-provider-id="' + escapeHtml(provider.id) + '"><i class="bi bi-pencil"></i></button><button type="button" class="btn btn-outline-danger provider-delete" data-provider-id="' + escapeHtml(provider.id) + '"><i class="bi bi-trash"></i></button></div>' : '',
        '</div>',
        isAdmin ? '<button type="button" class="btn btn-dark btn-sm mb-3" id="btnNuevoContacto" data-provider-id="' + escapeHtml(provider.id) + '"><i class="bi bi-person-plus me-1"></i>Nuevo contacto</button>' : '',
        contactsMarkup
      ].join('');
    }

    function renderCards() {
      var provider;

      grid.innerHTML = '';
      count.textContent = cachedProviders.length + ' proveedor(es)';

      if (!cachedProviders.length) {
        loading.classList.add('d-none');
        content.classList.add('d-none');
        empty.classList.remove('d-none');
        renderContacts(null);
        return;
      }

      if (selectedProviderId == null || !getSelectedProvider()) {
        selectedProviderId = cachedProviders[0].id;
      }

      provider = getSelectedProvider();
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
          '" data-provider-id="', escapeHtml(item.id), '" style="background:', isSelected ? '#f8f9fa' : '#ffffff', ';">',
          '<div class="card-body">',
          '<div class="d-flex justify-content-between align-items-start gap-3 mb-3">',
          '<div>',
          '<div class="fw-semibold text-dark">' + escapeHtml(item.nombre || 'Proveedor sin nombre') + '</div>',
          '<div class="text-muted small mt-1">' + getContactsLabel(item.contactos.length) + '</div>',
          '</div>',
          '<i class="bi bi-chevron-right text-muted"></i>',
          '</div>',
          '<div class="small text-muted">Seleccionar para ver contactos asociados</div>',
          '</div>',
          '</button>'
        ].join('');
        grid.appendChild(col);
      });

      renderContacts(provider);
    }

    function renderLoadingState() {
      loading.classList.remove('d-none');
      empty.classList.add('d-none');
      content.classList.add('d-none');
      grid.innerHTML = '';
      renderContacts(null);
      count.textContent = 'Cargando...';
    }

    function renderErrorState() {
      loading.classList.add('d-none');
      empty.classList.add('d-none');
      content.classList.remove('d-none');
      grid.innerHTML = '<div class="col-12"><div class="alert alert-danger mb-0">No se pudieron cargar los proveedores.</div></div>';
      contactsPanel.innerHTML = '<div class="text-muted">No hay informacion de contactos disponible.</div>';
      count.textContent = '0 proveedores';
    }

    function resetProviderForm() {
      if (!providerForm) {
        return;
      }
      providerForm.reset();
      providerFields.id.value = '';
      providerModalTitle.textContent = 'Nuevo proveedor';
      providerModalDescription.textContent = 'Registra un proveedor nuevo.';
      providerSubmitButton.textContent = 'Guardar proveedor';
    }

    function resetContactForm(providerId) {
      if (!contactForm) {
        return;
      }
      contactForm.reset();
      contactFields.providerId.value = providerId != null ? String(providerId) : '';
      contactFields.index.value = '';
      contactModalTitle.textContent = 'Nuevo contacto';
      contactModalDescription.textContent = 'Registra un contacto para el proveedor seleccionado.';
      contactSubmitButton.textContent = 'Guardar contacto';
    }

    function openProviderModal(providerId) {
      var provider = providerId != null ? getProviderById(providerId) : null;
      if (!isAdmin || !providerModal) {
        return;
      }

      resetProviderForm();
      if (provider) {
        providerFields.id.value = String(provider.id);
        providerFields.nombre.value = provider.nombre;
        providerModalTitle.textContent = 'Editar proveedor';
        providerModalDescription.textContent = 'Actualiza la informacion del proveedor seleccionado.';
        providerSubmitButton.textContent = 'Actualizar proveedor';
      }

      providerModal.show();
    }

    function openContactModal(providerId, contactIndex) {
      var provider = getProviderById(providerId);
      var contact;

      if (!isAdmin || !contactModal || !provider) {
        return;
      }

      resetContactForm(provider.id);
      if (contactIndex != null && provider.contactos[contactIndex]) {
        contact = provider.contactos[contactIndex];
        contactFields.index.value = String(contactIndex);
        contactFields.nombre.value = contact.nombre;
        contactFields.puesto.value = contact.puesto;
        contactFields.telefono.value = contact.telefono;
        contactFields.email.value = contact.email;
        contactModalTitle.textContent = 'Editar contacto';
        contactModalDescription.textContent = 'Actualiza la informacion del contacto seleccionado.';
        contactSubmitButton.textContent = 'Actualizar contacto';
      }

      contactModal.show();
    }

    function saveProvider(event) {
      var providerId;
      var providerName;
      var existing;

      event.preventDefault();
      providerName = String(providerFields.nombre.value || '').trim();
      if (!providerName) {
        providerFields.nombre.focus();
        return;
      }

      providerId = providerFields.id.value;
      if (providerId) {
        existing = getProviderById(providerId);
        if (existing) {
          existing.nombre = providerName;
        }
      } else {
        cachedProviders.push({
          id: getNextProviderId(),
          nombre: providerName,
          contactos: []
        });
        selectedProviderId = cachedProviders[cachedProviders.length - 1].id;
      }

      writeStorage();
      renderCards();
      providerModal.hide();
    }

    function saveContact(event) {
      var provider = getProviderById(contactFields.providerId.value);
      var contact;
      var contactIndex;

      event.preventDefault();
      if (!provider) {
        return;
      }

      contact = normalizeContact({
        nombre: contactFields.nombre.value,
        puesto: contactFields.puesto.value,
        telefono: contactFields.telefono.value,
        email: contactFields.email.value
      });

      if (!contact.nombre) {
        contactFields.nombre.focus();
        return;
      }

      contactIndex = contactFields.index.value;
      if (contactIndex !== '') {
        provider.contactos[Number(contactIndex)] = contact;
      } else {
        provider.contactos.push(contact);
      }

      selectedProviderId = provider.id;
      writeStorage();
      renderCards();
      contactModal.hide();
    }

    function deleteProvider(providerId) {
      var provider = getProviderById(providerId);
      if (!provider) {
        return;
      }

      if (!window.confirm('Se eliminara el proveedor "' + provider.nombre + '" y todos sus contactos.')) {
        return;
      }

      cachedProviders = cachedProviders.filter(function (item) {
        return String(item.id) !== String(providerId);
      });
      selectedProviderId = cachedProviders.length ? cachedProviders[0].id : null;
      writeStorage();
      renderCards();
    }

    function deleteContact(providerId, contactIndex) {
      var provider = getProviderById(providerId);
      var contact;

      if (!provider || !provider.contactos[contactIndex]) {
        return;
      }

      contact = provider.contactos[contactIndex];
      if (!window.confirm('Se eliminara el contacto "' + contact.nombre + '".')) {
        return;
      }

      provider.contactos.splice(Number(contactIndex), 1);
      selectedProviderId = provider.id;
      writeStorage();
      renderCards();
    }

    function loadProviders() {
      var stored = readStorage();
      renderLoadingState();

      if (stored) {
        cachedProviders = stored.map(normalizeProvider);
        renderCards();
        return;
      }

      fetch(basePath + '/js/mocks/proveedores.json', { cache: 'no-store' })
        .then(function (response) {
          if (!response.ok) {
            throw new Error('proveedores mock');
          }
          return response.json();
        })
        .then(function (providers) {
          cachedProviders = (Array.isArray(providers) ? providers : []).map(normalizeProvider);
          writeStorage();
          renderCards();
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
      if (!card) {
        return;
      }

      selectedProviderId = card.getAttribute('data-provider-id');
      renderCards();
    });

    contactsPanel.addEventListener('click', function (event) {
      if (!isAdmin) {
        return;
      }

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
        openContactModal(editContactButton.getAttribute('data-provider-id'), Number(editContactButton.getAttribute('data-contact-index')));
        return;
      }

      if (deleteContactButton) {
        deleteContact(deleteContactButton.getAttribute('data-provider-id'), Number(deleteContactButton.getAttribute('data-contact-index')));
      }
    });

    loadProviders();
  });
})();
