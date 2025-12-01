<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title ?? 'Iniciar Sesión - AVITECH'; ?></title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Poppins:wght@600;700;800&display=swap" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Estilos -->
    <link rel="stylesheet" href="<?php echo APP_URL; ?>/css/style.css">
    <link rel="stylesheet" href="<?php echo APP_URL; ?>/css/auth.css">
</head>

<body class="auth-page">

    <!-- Fondo animado -->
    <div class="auth-background">
        <div class="floating-shape shape-1"></div>
        <div class="floating-shape shape-2"></div>
        <div class="floating-shape shape-3"></div>
    </div>

    <!-- Contenedor de Login -->
    <div class="auth-container">
        <div class="auth-box glass-strong fade-in">
            <!-- Logo -->
            <div class="auth-logo">
                <i class="fas fa-kiwi-bird"></i>
                <h1>AVI<strong>TECH</strong></h1>
                <p>Sistema Integral de Gestión Avícola</p>
            </div>

            <!-- Mensajes Flash -->
            <?php if ($errorMsg = Session::getFlash('error')): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i> <?php echo $errorMsg; ?>
                </div>
            <?php endif; ?>

            <?php if ($successMsg = Session::getFlash('success')): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> <?php echo $successMsg; ?>
                </div>
            <?php endif; ?>

            <!-- Formulario -->
            <form action="<?php echo APP_URL; ?>/auth/processLogin" method="POST" class="auth-form">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">

                <h2>Iniciar Sesión</h2>
                <p class="text-muted mb-3">Ingresa tus credenciales para continuar</p>

                <div class="form-group">
                    <label for="email" class="form-label">
                        <i class="fas fa-envelope"></i> Correo Electrónico
                    </label>
                    <div class="input-group">
                        <span class="input-icon">
                            <i class="fas fa-at"></i>
                        </span>
                        <input
                            type="email"
                            id="email"
                            name="email"
                            class="form-control"
                            placeholder="tucorreo@ejemplo.com"
                            required
                            autofocus>
                    </div>
                </div>

                <div class="form-group">
                    <label for="password" class="form-label">
                        <i class="fas fa-lock"></i> Contraseña
                    </label>
                    <div class="input-group">
                        <span class="input-icon">
                            <i class="fas fa-key"></i>
                        </span>
                        <input
                            type="password"
                            id="password"
                            name="password"
                            class="form-control"
                            placeholder="••••••••"
                            required>
                        <button type="button" class="toggle-password" onclick="togglePassword()">
                            <i class="fas fa-eye" id="toggleIcon"></i>
                        </button>
                    </div>
                </div>

                <div class="form-options">
                    <label class="checkbox-label">
                        <input type="checkbox" name="remember">
                        <span>Recordarme</span>
                    </label>
                    <a href="<?php echo APP_URL; ?>/auth/forgot-password" class="link-primary">¿Olvidaste tu contraseña?</a>
                </div>

                <button type="submit" class="btn btn-primary btn-lg w-full">
                    <i class="fas fa-sign-in-alt"></i> Ingresar
                </button>

                <div class="auth-divider">
                    <span>o</span>
                </div>

                <p class="text-center text-muted">
                    ¿No tienes una cuenta?
                    <a href="<?php echo APP_URL; ?>/auth/register" class="link-primary">Regístrate aquí</a>
                </p>
            </form>

            <!-- Links adicionales -->
            <div class="auth-footer">
                <a href="<?php echo APP_URL; ?>/">
                    <i class="fas fa-arrow-left"></i> Volver al inicio
                </a>
            </div>
        </div>
    </div>

    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const toggleIcon = document.getElementById('toggleIcon');

            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.classList.remove('fa-eye');
                toggleIcon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                toggleIcon.classList.remove('fa-eye-slash');
                toggleIcon.classList.add('fa-eye');
            }
        }

        // Auto-cerrar alertas
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                alert.style.animation = 'fadeOut 0.3s ease-out';
                setTimeout(() => alert.remove(), 300);
            });
        }, 5000);
    </script>
</body>

</html>