<?php
/**
 * login.php — Inicio de sesión unificado (personal Oracle + clientes tienda).
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

$error = isset($_GET['error']) && $_GET['error'] === '1';
$registered = isset($_GET['registered']) && $_GET['registered'] === '1';
$logoutOk = isset($_GET['logout']) && $_GET['logout'] === '1';
$loginNext = (isset($_GET['next']) && $_GET['next'] === 'checkout') ? 'checkout' : '';
$loginAction = $basePath . '/../backend/api/auth_login.php';
$registroUrl = $basePath . '/pages/auth/registro_cliente.php';
if ($loginNext === 'checkout') {
    $registroUrl .= (strpos($registroUrl, '?') === false ? '?' : '&') . 'next=checkout';
}
$tiendaUrl = $basePath . '/pages/tienda/Homepage.php';
$logoUrl = $basePath . '/assets/img/Header-logo.png';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar sesión - M. Hamilton Store</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .auth-page {
            min-height: 100vh;
            background: #1a1d21;
        }
        .auth-wrap {
            min-height: 100vh;
            display: flex;
            align-items: center;
            padding-top: 1.5rem;
            padding-bottom: 1.5rem;
        }
        .auth-card {
            border: none;
            border-radius: 1rem;
            box-shadow: 0 1rem 3rem rgba(0, 0, 0, 0.35);
        }
        .auth-logo {
            max-height: 100px;
            width: auto;
        }
        @media (min-width: 992px) {
            .auth-logo {
                max-height: 120px;
            }
        }
        .auth-hero .auth-tagline {
            letter-spacing: 0.12em;
            font-size: 0.7rem;
        }
    </style>
</head>
<body class="auth-page">
    <div class="container auth-wrap">
        <div class="row justify-content-center align-items-center g-4 g-lg-5 w-100">
            <div class="col-lg-5 col-xl-5 text-center text-lg-start order-2 order-lg-1">
                <div class="auth-hero px-lg-2">
                    <p class="auth-tagline text-uppercase mb-2 text-white-50">Tienda online</p>
                    <h2 class="h3 fw-semibold mb-3 text-white">Bienvenido a M. Hamilton Store</h2>
                    <p class="mb-0 text-white-50 mx-auto mx-lg-0" style="max-width: 22rem;">
                        Calidad, cercanía y lo mejor en electrónica. Tu próxima compra empieza aquí.
                    </p>
                </div>
            </div>
            <div class="col-lg-5 col-xl-4 order-1 order-lg-2">
                <div class="card auth-card">
                    <div class="card-body p-4 p-md-5">
                        <div class="text-center mb-3">
                            <a href="<?php echo htmlspecialchars($tiendaUrl); ?>" class="btn btn-link btn-sm text-muted text-decoration-none mb-2">
                                <i class="bi bi-arrow-left me-1"></i>Volver a la tienda
                            </a>
                        </div>
                        <div class="text-center mb-4">
                            <img src="<?php echo htmlspecialchars($logoUrl); ?>" alt="M. Hamilton Store" class="auth-logo mb-3">
                            <h1 class="h4 mb-1">Iniciar sesión</h1>
                            <p class="text-muted small mb-0">Ingresá con tu usuario y contraseña.</p>
                        </div>

                        <?php if ($error): ?>
                            <div class="alert alert-danger py-2 small" role="alert">
                                <i class="bi bi-exclamation-triangle-fill me-1"></i> Credenciales incorrectas o cuenta inactiva.
                            </div>
                        <?php endif; ?>
                        <?php if ($registered): ?>
                            <div class="alert alert-success py-2 small" role="alert">
                                <i class="bi bi-check-circle-fill me-1"></i> Cuenta creada. Ya puedes entrar con tu usuario y contraseña.
                            </div>
                        <?php endif; ?>
                        <?php if ($logoutOk): ?>
                            <div class="alert alert-secondary py-2 small" role="alert">Sesión cerrada correctamente.</div>
                        <?php endif; ?>

                        <form method="post" action="<?php echo htmlspecialchars($loginAction); ?>" autocomplete="on" class="needs-validation" novalidate>
                            <?php if ($loginNext === 'checkout'): ?>
                            <input type="hidden" name="next" value="checkout">
                            <?php endif; ?>
                            <div class="mb-3">
                                <label for="user" class="form-label">Usuario</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0"><i class="bi bi-person text-muted"></i></span>
                                    <input type="text" class="form-control border-start-0 ps-0" id="user" name="user" placeholder="Tu nombre de usuario" required autofocus autocomplete="username" maxlength="50">
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Contraseña</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0"><i class="bi bi-lock text-muted"></i></span>
                                    <input type="password" class="form-control border-start-0 ps-0" id="password" name="password" placeholder="••••••••" required autocomplete="current-password" minlength="1">
                                </div>
                            </div>
                            <button type="submit" class="btn btn-dark w-100 py-2 mb-3">
                                <i class="bi bi-box-arrow-in-right me-2"></i>Entrar
                            </button>
                        </form>

                        <hr class="text-muted opacity-25">

                        <p class="small text-muted mb-2">
                            <strong>¿Cliente de la tienda?</strong>
                            <a href="<?php echo htmlspecialchars($registroUrl); ?>">Crear cuenta</a>
                        </p>
                        <p class="small text-muted mb-0">
                            <i class="bi bi-info-circle me-1"></i> Personal del salón: las cuentas de empleado las gestionan <strong>administrador o cajero</strong> desde el sistema; no uses este registro.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
