<?php
/**
 * registro_cliente.php — Alta solo para clientes de la tienda (Oracle).
 */
session_start();

require_once __DIR__ . '/../../../backend/config/auth_guard.php';

$basePath = dirname(dirname(dirname($_SERVER['SCRIPT_NAME'])));
if ($basePath === '/' || $basePath === '\\') {
    $basePath = '';
}

if (!empty($_SESSION['user'])) {
    header('Location: ' . getRoleHomePath($_SESSION['role'] ?? ''));
    exit;
}

$pathsFile = __DIR__ . '/../../../backend/config/paths.php';
$paths = is_file($pathsFile) ? require $pathsFile : ['api' => ''];
$apiBase = $paths['api'] ?? '';
$regNext = isset($_GET['next']) && $_GET['next'] === 'checkout' ? 'checkout' : '';
$loginUrlAfterReg = $basePath . '/pages/auth/login.php?registered=1';
$loginUrlPlain = $basePath . '/pages/auth/login.php';
if ($regNext === 'checkout') {
    $loginUrlAfterReg .= '&next=checkout';
    $loginUrlPlain .= '?next=checkout';
}
$tiendaUrl = $basePath . '/pages/tienda/Homepage.php';
$logoUrl = $basePath . '/assets/img/Header-logo.png';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear cuenta cliente - M. Hamilton Store</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .auth-page {
            min-height: 100vh;
            background: linear-gradient(160deg, #1a1a1a 0%, #2d2d2d 40%, #f8f9fa 40%);
        }
        .auth-card {
            border: none;
            border-radius: 1rem;
            box-shadow: 0 1rem 3rem rgba(0, 0, 0, 0.1);
        }
        .auth-logo {
            max-height: 120px;
            width: auto;
            max-width: min(100%, 320px);
            object-fit: contain;
        }
    </style>
</head>
<body class="auth-page">
    <div class="container py-4 py-lg-5">
        <div class="row justify-content-center">
            <div class="col-md-10 col-lg-7 col-xl-6">
                <div class="card auth-card">
                    <div class="card-body p-4 p-md-5">
                        <div class="text-center mb-3">
                            <a href="<?php echo htmlspecialchars($tiendaUrl); ?>" class="btn btn-link btn-sm text-muted text-decoration-none mb-2">
                                <i class="bi bi-arrow-left me-1"></i>Volver a la tienda
                            </a>
                        </div>
                        <div class="text-center mb-4">
                            <img src="<?php echo htmlspecialchars($logoUrl); ?>" alt="M. Hamilton Store" class="auth-logo mb-3">
                            <h1 class="h4 mb-1">Crear cuenta de cliente</h1>
                            <p class="text-muted small mb-0">Para comprar en la tienda necesitás cuenta e inicio de sesión. Este formulario crea tu cliente y tu usuario en el sistema.</p>
                        </div>

                        <?php if ($regNext === 'checkout'): ?>
                        <div class="alert alert-info small py-2 mb-3" role="status">
                            <i class="bi bi-cart3 me-1"></i> Después de registrarte, iniciá sesión para continuar al checkout.
                        </div>
                        <?php endif; ?>

                        <div id="regAlert" class="alert d-none" role="alert"></div>

                        <form id="formRegistroCliente" novalidate>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="regNombre" class="form-label">Nombre <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="regNombre" name="regNombre" required maxlength="50" autocomplete="given-name" pattern="[A-Za-z ]+" title="Solo letras A-Z y espacios, sin tildes">
                                    <div class="form-text">Solo letras (sin tilde) y espacios. Ej.: Maria, Jose</div>
                                </div>
                                <div class="col-md-6">
                                    <label for="regApellido" class="form-label">Apellido <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="regApellido" name="regApellido" required maxlength="50" autocomplete="family-name" pattern="[A-Za-z ]+" title="Solo letras A-Z y espacios, sin tildes">
                                    <div class="form-text">Igual que el nombre (reglas de la base de datos).</div>
                                </div>
                                <div class="col-12">
                                    <label for="regEmail" class="form-label">Email <span class="text-danger">*</span></label>
                                    <input type="email" class="form-control" id="regEmail" required maxlength="150" autocomplete="email">
                                </div>
                                <div class="col-12">
                                    <label for="regUsername" class="form-label">Usuario para entrar <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="regUsername" required minlength="3" maxlength="50" autocomplete="username" pattern="[A-Za-z0-9._\-]+">
                                    <div class="form-text">Letras, números, punto, guion o guion bajo (como en el sistema interno).</div>
                                </div>
                                <div class="col-md-6">
                                    <label for="regPassword" class="form-label">Contraseña <span class="text-danger">*</span></label>
                                    <input type="password" class="form-control" id="regPassword" required minlength="8" maxlength="100" autocomplete="new-password">
                                </div>
                                <div class="col-md-6">
                                    <label for="regPassword2" class="form-label">Confirmar contraseña <span class="text-danger">*</span></label>
                                    <input type="password" class="form-control" id="regPassword2" required minlength="8" maxlength="100" autocomplete="new-password">
                                </div>
                            </div>

                            <button type="submit" class="btn btn-dark w-100 py-2 mt-4" id="btnRegistro">
                                <i class="bi bi-person-plus me-2"></i>Crear mi cuenta
                            </button>
                        </form>

                        <p class="text-center text-muted small mt-4 mb-0">
                            ¿Ya tienes cuenta? <a href="<?php echo htmlspecialchars($loginUrlPlain); ?>">Iniciar sesión</a>
                        </p>
                        <p class="text-center text-muted small mt-2 mb-0">
                            <i class="bi bi-shield-lock me-1"></i> Si trabajás en el salón, pedí tu usuario a <strong>administración o caja</strong>.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
    window.API_BASE = <?php echo json_encode($apiBase, JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP); ?>;
    </script>
    <script src="<?php echo htmlspecialchars($basePath); ?>/js/services/api.js"></script>
    <script src="<?php echo htmlspecialchars($basePath); ?>/js/utils/validation-helpers.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    (function () {
      var form = document.getElementById('formRegistroCliente');
      var alertEl = document.getElementById('regAlert');
      var btn = document.getElementById('btnRegistro');

      function showAlert(kind, msg) {
        alertEl.className = 'alert alert-' + kind;
        alertEl.textContent = msg;
        alertEl.classList.remove('d-none');
      }

      form.addEventListener('submit', function (e) {
        e.preventDefault();
        if (!window.Api) {
          showAlert('danger', 'No se pudo cargar la API. Recargue la página.');
          return;
        }
        var nombre = document.getElementById('regNombre').value.trim();
        var apellido = document.getElementById('regApellido').value.trim();
        var email = document.getElementById('regEmail').value.trim().toLowerCase();
        var username = document.getElementById('regUsername').value.trim();
        var password = document.getElementById('regPassword').value;
        var password2 = document.getElementById('regPassword2').value;

        if (!nombre || !apellido || !email || !username) {
          showAlert('warning', 'Complete todos los campos obligatorios.');
          return;
        }
        if (password.length < 8) {
          showAlert('warning', 'La contraseña debe tener al menos 8 caracteres.');
          return;
        }
        if (password !== password2) {
          showAlert('warning', 'Las contraseñas no coinciden.');
          return;
        }
        var Hv = window.HamiltonValidation;
        if (Hv) {
          if (!Hv.clienteNombreOracle(nombre) || nombre.trim().length > 100) {
            showAlert(
              'warning',
              'Nombre: solo letras A-Z y espacios (sin tildes ni ñ), máximo 100 caracteres.'
            );
            return;
          }
          if (!Hv.clienteNombreOracle(apellido) || apellido.trim().length > 100) {
            showAlert(
              'warning',
              'Apellido: solo letras A-Z y espacios (sin tildes ni ñ), máximo 100 caracteres.'
            );
            return;
          }
          if (!Hv.clienteEmailOracle(email) || email.length > 200) {
            showAlert('warning', 'Email con formato inválido (máximo 200 caracteres).');
            return;
          }
          if (!Hv.usernameRegistroCliente(username)) {
            showAlert(
              'warning',
              'Usuario: entre 3 y 50 caracteres; solo letras, números, punto, guion y guion bajo.'
            );
            return;
          }
        } else {
          var reNom = /^[A-Za-z ]+$/;
          if (!reNom.test(nombre) || !reNom.test(apellido)) {
            showAlert(
              'warning',
              'Nombre y apellido solo pueden usar letras sin tilde (A-Z) y espacios. Ejemplos: Maria, Perez.'
            );
            return;
          }
        }

        btn.disabled = true;
        alertEl.classList.add('d-none');

        window.Api.post('/auth_register_cliente.php', {
          nombre: nombre,
          apellido: apellido,
          email: email,
          username: username,
          password: password,
          passwordConfirm: password2
        }).then(function () {
          window.location.href = <?php echo json_encode($loginUrlAfterReg); ?>;
        }).catch(function (err) {
          showAlert('danger', err.message || 'No se pudo registrar.');
        }).finally(function () {
          btn.disabled = false;
        });
      });
    })();
    </script>
</body>
</html>
