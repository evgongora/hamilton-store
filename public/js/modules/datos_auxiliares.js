/**
 * datos_auxiliares.js — Direcciones, teléfonos clientes y teléfonos contactos proveedor (Oracle packages).
 */
(function () {
  'use strict';

  function uiAlert(msg, title) {
    if (window.UiDialog && window.UiDialog.alert) {
      return window.UiDialog.alert(String(msg), { title: title || 'Datos' });
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

  if (!window.Api) return;

  var provincias = [];
  var cantones = [];
  var distritos = [];
  var clientes = [];
  var proveedores = [];
  var direcciones = [];
  var telCli = [];
  var telTcp = [];
  var tcpContactsCache = [];

  function esc(s) {
    var d = document.createElement('div');
    d.textContent = s == null ? '' : String(s);
    return d.innerHTML;
  }

  function reloadGeo() {
    return Promise.all([
      window.Api.get('/provincias_list.php'),
      window.Api.get('/cantones_list.php'),
      window.Api.get('/distritos_list.php'),
    ]).then(function (res) {
      provincias = (res[0].data || []).map(function (r) {
        return { id: r.id, nombre: r.nombre };
      });
      cantones = (res[1].data || []).map(function (r) {
        return { id: r.id, nombre: r.nombre, idProvincia: r.idProvincia };
      });
      distritos = (res[2].data || []).map(function (r) {
        return { id: r.id, nombre: r.nombre, idCanton: r.idCanton };
      });
    });
  }

  function reloadClientesProveedores() {
    return Promise.all([
      window.Api.get('/clientes_list.php'),
      window.Api.get('/proveedores_list.php'),
    ]).then(function (res) {
      clientes = res[0].data || [];
      proveedores = res[1].data || [];
    });
  }

  function fillSelect(sel, items, valKey, labelFn, emptyLabel) {
    sel.innerHTML = '';
    var o0 = document.createElement('option');
    o0.value = '';
    o0.textContent = emptyLabel || '—';
    sel.appendChild(o0);
    items.forEach(function (it) {
      var o = document.createElement('option');
      o.value = String(it[valKey]);
      o.textContent = labelFn(it);
      sel.appendChild(o);
    });
  }

  function cantonesDeProv(idProv) {
    return cantones.filter(function (c) {
      return c.idProvincia === idProv;
    });
  }

  function distritosDeCanton(idCant) {
    return distritos.filter(function (d) {
      return d.idCanton === idCant;
    });
  }

  function provDeCanton(idCant) {
    var c = cantones.find(function (x) {
      return x.id === idCant;
    });
    return c ? c.idProvincia : 0;
  }

  function cantDeDistrito(idDist) {
    var d = distritos.find(function (x) {
      return x.id === idDist;
    });
    return d ? d.idCanton : 0;
  }

  function refreshDirCascada(idProv, idCant, idDist) {
    var sp = document.getElementById('daDirProv');
    var sc = document.getElementById('daDirCant');
    var sd = document.getElementById('daDirDist');
    fillSelect(sp, provincias, 'id', function (p) {
      return p.nombre;
    }, 'Provincia');
    var ip = idProv || (sp.value ? parseInt(sp.value, 10) : 0);
    if (!ip && provincias.length) ip = provincias[0].id;
    sp.value = ip ? String(ip) : '';

    fillSelect(sc, cantonesDeProv(ip), 'id', function (c) {
      return c.nombre;
    }, 'Cantón');
    var ic = idCant || 0;
    if (!ic && cantonesDeProv(ip).length) ic = cantonesDeProv(ip)[0].id;
    sc.value = ic ? String(ic) : '';

    fillSelect(sd, distritosDeCanton(ic), 'id', function (d) {
      return d.nombre;
    }, 'Distrito');
    sd.value = idDist ? String(idDist) : '';
  }

  function renderDir() {
    var tb = document.getElementById('daDirBody');
    if (!tb) return;
    tb.innerHTML = '';
    direcciones.forEach(function (d) {
      var ubi = [d.nombreProvincia, d.nombreCanton, d.nombreDistrito].filter(Boolean).join(' / ');
      var cp =
        d.idCliente != null
          ? 'Cliente #' + d.idCliente
          : d.idProveedor != null
            ? 'Proveedor #' + d.idProveedor
            : '—';
      var tr = document.createElement('tr');
      tr.innerHTML =
        '<td>' +
        d.id +
        '</td><td>' +
        esc(d.otrasSenas) +
        '</td><td>' +
        esc(ubi) +
        '</td><td>' +
        esc(cp) +
        '</td><td class="text-end">' +
        '<button type="button" class="btn btn-outline-primary btn-sm me-1 da-ed-dir" data-id="' +
        d.id +
        '"><i class="bi bi-pencil"></i></button>' +
        '<button type="button" class="btn btn-outline-danger btn-sm da-del-dir" data-id="' +
        d.id +
        '"><i class="bi bi-trash"></i></button></td>';
      tb.appendChild(tr);
    });
    tb.querySelectorAll('.da-ed-dir').forEach(function (b) {
      b.addEventListener('click', function () {
        abrirDir(parseInt(b.getAttribute('data-id'), 10));
      });
    });
    tb.querySelectorAll('.da-del-dir').forEach(function (b) {
      b.addEventListener('click', function () {
        eliminarDir(parseInt(b.getAttribute('data-id'), 10));
      });
    });
  }

  function renderTc() {
    var tb = document.getElementById('daTcBody');
    if (!tb) return;
    tb.innerHTML = '';
    telCli.forEach(function (t) {
      var nom = (t.nombreCliente || '') + ' ' + (t.apellidoCliente || '');
      var tr = document.createElement('tr');
      tr.innerHTML =
        '<td>' +
        t.id +
        '</td><td>' +
        esc(t.numero) +
        '</td><td>' +
        esc(nom.trim() || '#' + t.idCliente) +
        '</td><td class="text-end">' +
        '<button type="button" class="btn btn-outline-primary btn-sm me-1 da-ed-tc" data-id="' +
        t.id +
        '"><i class="bi bi-pencil"></i></button>' +
        '<button type="button" class="btn btn-outline-danger btn-sm da-del-tc" data-id="' +
        t.id +
        '"><i class="bi bi-trash"></i></button></td>';
      tb.appendChild(tr);
    });
    tb.querySelectorAll('.da-ed-tc').forEach(function (b) {
      b.addEventListener('click', function () {
        abrirTc(parseInt(b.getAttribute('data-id'), 10));
      });
    });
    tb.querySelectorAll('.da-del-tc').forEach(function (b) {
      b.addEventListener('click', function () {
        eliminarTc(parseInt(b.getAttribute('data-id'), 10));
      });
    });
  }

  function renderTcp() {
    var tb = document.getElementById('daTcpBody');
    if (!tb) return;
    tb.innerHTML = '';
    telTcp.forEach(function (t) {
      var nom = (t.nombreContacto || '') + ' ' + (t.apellidoContacto || '');
      var tr = document.createElement('tr');
      tr.innerHTML =
        '<td>' +
        t.id +
        '</td><td>' +
        esc(t.numero) +
        '</td><td>' +
        esc(nom.trim() || '#' + t.idContacto) +
        '</td><td class="text-end">' +
        '<button type="button" class="btn btn-outline-primary btn-sm me-1 da-ed-tcp" data-id="' +
        t.id +
        '"><i class="bi bi-pencil"></i></button>' +
        '<button type="button" class="btn btn-outline-danger btn-sm da-del-tcp" data-id="' +
        t.id +
        '"><i class="bi bi-trash"></i></button></td>';
      tb.appendChild(tr);
    });
    tb.querySelectorAll('.da-ed-tcp').forEach(function (b) {
      b.addEventListener('click', function () {
        abrirTcp(parseInt(b.getAttribute('data-id'), 10));
      });
    });
    tb.querySelectorAll('.da-del-tcp').forEach(function (b) {
      b.addEventListener('click', function () {
        eliminarTcp(parseInt(b.getAttribute('data-id'), 10));
      });
    });
  }

  function reloadDirList() {
    return window.Api.get('/direcciones_list.php').then(function (r) {
      direcciones = (r.data || []).map(function (x) {
        return {
          id: x.id,
          otrasSenas: x.otrasSenas,
          idProvincia: x.idProvincia,
          nombreProvincia: x.nombreProvincia,
          idCanton: x.idCanton,
          nombreCanton: x.nombreCanton,
          idDistrito: x.idDistrito,
          nombreDistrito: x.nombreDistrito,
          idCliente: x.idCliente,
          idProveedor: x.idProveedor,
        };
      });
      renderDir();
    });
  }

  function reloadTcList() {
    return window.Api.get('/telefonos_clientes_list.php').then(function (r) {
      telCli = (r.data || []).map(function (x) {
        return {
          id: x.id,
          numero: x.numero,
          idCliente: x.idCliente,
          nombreCliente: x.nombreCliente,
          apellidoCliente: x.apellidoCliente,
        };
      });
      renderTc();
    });
  }

  function reloadTcpList() {
    return window.Api.get('/telefonos_cont_proveedor_list.php').then(function (r) {
      telTcp = (r.data || []).map(function (x) {
        return {
          id: x.id,
          numero: x.numero,
          idContacto: x.idContacto,
          nombreContacto: x.nombreContacto,
          apellidoContacto: x.apellidoContacto,
        };
      });
      renderTcp();
    });
  }

  function fillCliSel(sel) {
    fillSelect(sel, clientes, 'id', function (c) {
      return '#' + c.id + ' — ' + (c.nombre || '') + ' ' + (c.apellido || '');
    }, 'Cliente');
  }

  function fillPrvSel(sel) {
    fillSelect(sel, proveedores, 'id', function (p) {
      return '#' + p.id + ' — ' + (p.nombre || '');
    }, 'Proveedor');
  }

  function updateDirTipoUi() {
    var prv = document.getElementById('daDirTipoPrv').checked;
    document.getElementById('daDirWrapCli').classList.toggle('d-none', prv);
    document.getElementById('daDirWrapPrv').classList.toggle('d-none', !prv);
  }

  function abrirDir(id) {
    Promise.all([reloadGeo(), reloadClientesProveedores()])
      .then(function () {
        fillCliSel(document.getElementById('daDirCliente'));
        fillPrvSel(document.getElementById('daDirProveedor'));
        document.getElementById('daDirId').value = id || '';
        if (id) {
          var d = direcciones.find(function (x) {
            return x.id === id;
          });
          if (d) {
            document.getElementById('daDirSenas').value = d.otrasSenas || '';
            refreshDirCascada(d.idProvincia, d.idCanton, d.idDistrito);
            if (d.idCliente != null) {
              document.getElementById('daDirTipoCli').checked = true;
              document.getElementById('daDirCliente').value = String(d.idCliente);
            } else {
              document.getElementById('daDirTipoPrv').checked = true;
              document.getElementById('daDirProveedor').value = String(d.idProveedor || '');
            }
            updateDirTipoUi();
          }
        } else {
          document.getElementById('daDirSenas').value = '';
          document.getElementById('daDirTipoCli').checked = true;
          updateDirTipoUi();
          refreshDirCascada(0, 0, 0);
        }
        new bootstrap.Modal(document.getElementById('daModalDir')).show();
      })
      .catch(function (e) {
        uiAlert(e.message || 'Error');
      });
  }

  function guardarDir() {
    var idVal = document.getElementById('daDirId').value;
    var id = idVal ? parseInt(idVal, 10) : null;
    var senas = document.getElementById('daDirSenas').value.trim();
    var idProv = parseInt(document.getElementById('daDirProv').value, 10);
    var idCant = parseInt(document.getElementById('daDirCant').value, 10);
    var idDist = parseInt(document.getElementById('daDirDist').value, 10);
    var esPrv = document.getElementById('daDirTipoPrv').checked;
    var idCli = esPrv ? 0 : parseInt(document.getElementById('daDirCliente').value, 10);
    var idPrv = esPrv ? parseInt(document.getElementById('daDirProveedor').value, 10) : 0;
    if (!idProv || !idCant || !idDist) {
      uiAlert('Seleccione provincia, cantón y distrito.');
      return;
    }
    var Vd = window.HamiltonValidation;
    var errSen =
      Vd && Vd.textoLibreMensaje ? Vd.textoLibreMensaje(document.getElementById('daDirSenas').value, 2000, true, 'Otras señas') : null;
    if (errSen) {
      uiAlert(errSen);
      return;
    }
    if (esPrv && !idPrv) {
      uiAlert('Seleccione un proveedor.');
      return;
    }
    if (!esPrv && !idCli) {
      uiAlert('Seleccione un cliente.');
      return;
    }
    var body = {
      action: id ? 'update' : 'insert',
      otrasSenas: senas,
      idProvincia: idProv,
      idCanton: idCant,
      idDistrito: idDist,
    };
    if (id) body.id = id;
    if (esPrv) body.idProveedor = idPrv;
    else body.idCliente = idCli;
    window.Api.post('/direcciones_save.php', body).then(function () {
      bootstrap.Modal.getInstance(document.getElementById('daModalDir')).hide();
      return reloadDirList();
    }).catch(function (e) {
      uiAlert(e.message || 'Error');
    });
  }

  function eliminarDir(id) {
    uiConfirm('¿Eliminar dirección?', 'Eliminar').then(function (ok) {
      if (!ok) return;
      window.Api.post('/direcciones_save.php', { action: 'delete', id: id }).then(function () {
        return reloadDirList();
      }).catch(function (e) {
        uiAlert(e.message || 'Error');
      });
    });
  }

  function abrirTc(id) {
    reloadClientesProveedores().then(function () {
      fillCliSel(document.getElementById('daTcCliente'));
      document.getElementById('daTcId').value = id || '';
      if (id) {
        var t = telCli.find(function (x) {
          return x.id === id;
        });
        if (t) {
          document.getElementById('daTcNum').value = t.numero;
          document.getElementById('daTcCliente').value = String(t.idCliente);
        }
      } else {
        document.getElementById('daTcNum').value = '';
      }
      new bootstrap.Modal(document.getElementById('daModalTc')).show();
    });
  }

  function guardarTc() {
    var idVal = document.getElementById('daTcId').value;
    var id = idVal ? parseInt(idVal, 10) : null;
    var num = document.getElementById('daTcNum').value.trim();
    var idCli = parseInt(document.getElementById('daTcCliente').value, 10);
    if (!idCli) {
      uiAlert('Seleccione un cliente.');
      return;
    }
    if (!num) {
      uiAlert('Indique el número de teléfono.');
      return;
    }
    var Vt = window.HamiltonValidation;
    if (Vt && !Vt.telefonoOracle(num)) {
      uiAlert('Número de teléfono inválido (solo dígitos, espacios, guiones y paréntesis).');
      return;
    }
    var body = id
      ? { action: 'update', id: id, numero: num, idCliente: idCli }
      : { action: 'insert', numero: num, idCliente: idCli };
    window.Api.post('/telefonos_clientes_save.php', body).then(function () {
      bootstrap.Modal.getInstance(document.getElementById('daModalTc')).hide();
      return reloadTcList();
    }).catch(function (e) {
      uiAlert(e.message || 'Error');
    });
  }

  function eliminarTc(id) {
    uiConfirm('¿Eliminar teléfono?', 'Eliminar').then(function (ok) {
      if (!ok) return;
      window.Api.post('/telefonos_clientes_save.php', { action: 'delete', id: id }).then(function () {
        return reloadTcList();
      }).catch(function (e) {
        uiAlert(e.message || 'Error');
      });
    });
  }

  function loadTcpContacts(provId) {
    if (!provId) {
      tcpContactsCache = [];
      fillSelect(
        document.getElementById('daTcpContacto'),
        [],
        'id',
        function () {
          return '';
        },
        'Contacto'
      );
      return Promise.resolve();
    }
    return window.Api.get('/contactos_proveedor_list.php?proveedorId=' + provId).then(function (r) {
      tcpContactsCache = r.data || [];
      fillSelect(document.getElementById('daTcpContacto'), tcpContactsCache, 'id', function (c) {
        return (c.nombre || '') + ' ' + (c.apellido || '') + ' <' + (c.email || '') + '>';
      }, 'Contacto');
    });
  }

  function buscarProveedorDeContacto(contactId) {
    return window.Api.get('/proveedores_list.php').then(function (r) {
      var provs = r.data || [];
      return Promise.all(
        provs.map(function (p) {
          return window.Api.get('/contactos_proveedor_list.php?proveedorId=' + p.id).then(function (rr) {
            var list = rr.data || [];
            var i;
            for (i = 0; i < list.length; i++) {
              if (list[i].id === contactId) return p.id;
            }
            return null;
          });
        })
      ).then(function (ids) {
        var j;
        for (j = 0; j < ids.length; j++) {
          if (ids[j] !== null) return ids[j];
        }
        return 0;
      });
    });
  }

  function abrirTcp(id) {
    reloadClientesProveedores()
      .then(function () {
        fillPrvSel(document.getElementById('daTcpProveedor'));
        if (id) {
          document.getElementById('daTcpId').value = String(id);
          var t = telTcp.find(function (x) {
            return x.id === id;
          });
          if (!t) {
            document.getElementById('daTcpNum').value = '';
            return mostrarModalTcpNuevo();
          }
          return buscarProveedorDeContacto(t.idContacto).then(function (pid) {
            document.getElementById('daTcpProveedor').value = pid ? String(pid) : '';
            return loadTcpContacts(pid).then(function () {
              document.getElementById('daTcpContacto').value = String(t.idContacto);
              document.getElementById('daTcpNum').value = t.numero;
              new bootstrap.Modal(document.getElementById('daModalTcp')).show();
            });
          });
        }
        document.getElementById('daTcpId').value = '';
        document.getElementById('daTcpNum').value = '';
        return mostrarModalTcpNuevo();
      })
      .catch(function (e) {
        uiAlert(e.message || 'Error');
      });
  }

  function mostrarModalTcpNuevo() {
    var pv = proveedores.length ? proveedores[0].id : 0;
    document.getElementById('daTcpProveedor').value = pv ? String(pv) : '';
    return loadTcpContacts(pv).then(function () {
      new bootstrap.Modal(document.getElementById('daModalTcp')).show();
    });
  }

  function guardarTcp() {
    var idVal = document.getElementById('daTcpId').value;
    var id = idVal ? parseInt(idVal, 10) : null;
    var num = document.getElementById('daTcpNum').value.trim();
    var idCont = parseInt(document.getElementById('daTcpContacto').value, 10);
    if (!idCont) {
      uiAlert('Seleccione un contacto.');
      return;
    }
    if (!num) {
      uiAlert('Indique el número de teléfono.');
      return;
    }
    var Vtcp = window.HamiltonValidation;
    if (Vtcp && !Vtcp.telefonoOracle(num)) {
      uiAlert('Número de teléfono inválido (solo dígitos, espacios, guiones y paréntesis).');
      return;
    }
    var body = id
      ? { action: 'update', id: id, numero: num, idContacto: idCont }
      : { action: 'insert', numero: num, idContacto: idCont };
    window.Api.post('/telefonos_cont_proveedor_save.php', body).then(function () {
      bootstrap.Modal.getInstance(document.getElementById('daModalTcp')).hide();
      return reloadTcpList();
    }).catch(function (e) {
      uiAlert(e.message || 'Error');
    });
  }

  function eliminarTcp(id) {
    uiConfirm('¿Eliminar teléfono?', 'Eliminar').then(function (ok) {
      if (!ok) return;
      window.Api.post('/telefonos_cont_proveedor_save.php', { action: 'delete', id: id }).then(function () {
        return reloadTcpList();
      }).catch(function (e) {
        uiAlert(e.message || 'Error');
      });
    });
  }

  document.addEventListener('DOMContentLoaded', function () {
    if (!document.getElementById('daDirBody')) return;

    reloadGeo()
      .then(function () {
        return reloadClientesProveedores();
      })
      .then(function () {
        return Promise.all([reloadDirList(), reloadTcList(), reloadTcpList()]);
      })
      .catch(function (e) {
        uiAlert(e.message || 'Error al cargar');
      });

    document.getElementById('daBtnNuevaDir').addEventListener('click', function () {
      abrirDir();
    });
    document.getElementById('daBtnGuardarDir').addEventListener('click', guardarDir);
    document.getElementById('daDirProv').addEventListener('change', function () {
      var ip = parseInt(document.getElementById('daDirProv').value, 10);
      refreshDirCascada(ip, 0, 0);
    });
    document.getElementById('daDirCant').addEventListener('change', function () {
      var ic = parseInt(document.getElementById('daDirCant').value, 10);
      var ip = parseInt(document.getElementById('daDirProv').value, 10);
      refreshDirCascada(ip, ic, 0);
    });
    document.getElementById('daDirTipoCli').addEventListener('change', updateDirTipoUi);
    document.getElementById('daDirTipoPrv').addEventListener('change', updateDirTipoUi);

    document.getElementById('daBtnNuevoTc').addEventListener('click', function () {
      abrirTc();
    });
    document.getElementById('daBtnGuardarTc').addEventListener('click', guardarTc);

    document.getElementById('daBtnNuevoTcp').addEventListener('click', function () {
      abrirTcp(null);
    });
    document.getElementById('daTcpProveedor').addEventListener('change', function () {
      loadTcpContacts(parseInt(document.getElementById('daTcpProveedor').value, 10));
    });
    document.getElementById('daBtnGuardarTcp').addEventListener('click', guardarTcp);
  });
})();
