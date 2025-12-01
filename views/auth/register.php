<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title ?? 'Registro - AVITECH'; ?></title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Poppins:wght@600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?php echo APP_URL; ?>/css/style.css">
    <link rel="stylesheet" href="<?php echo APP_URL; ?>/css/auth.css">
</head>

<body class="auth-page">

    <div class="auth-background">
        <div class="floating-shape shape-1"></div>
        <div class="floating-shape shape-2"></div>
        <div class="floating-shape shape-3"></div>
    </div>

    <div class="auth-container" style="max-width: 650px;">
        <div class="auth-box glass-strong fade-in">
            <div class="auth-logo">
                <i class="fas fa-kiwi-bird"></i>
                <h1>AVI<strong>TECH</strong></h1>
                <p>Únete a la comunidad avícola digital</p>
            </div>

            <?php if ($errorMsg = Session::getFlash('error')): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i>
                    <div><?php echo $errorMsg; ?></div>
                </div>
            <?php endif; ?>

            <form action="<?php echo APP_URL; ?>/auth/processRegister" method="POST" class="auth-form">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">

                <h2>Crear Cuenta</h2>
                <p class="text-muted mb-3">Completa tus datos para registrarte</p>

                <div class="form-group">
                    <label for="nombre_completo" class="form-label">
                        <i class="fas fa-user"></i> Nombre Completo
                    </label>
                    <div class="input-group">
                        <span class="input-icon"><i class="fas fa-id-card"></i></span>
                        <input type="text" id="nombre_completo" name="nombre_completo" class="form-control"
                            placeholder="Juan Pérez García" required>
                    </div>
                </div>

                <div class="row">
                    <div class="col-6">
                        <div class="form-group">
                            <label for="email" class="form-label">
                                <i class="fas fa-envelope"></i> Correo Electrónico
                            </label>
                            <div class="input-group">
                                <span class="input-icon"><i class="fas fa-at"></i></span>
                                <input type="email" id="email" name="email" class="form-control"
                                    placeholder="correo@ejemplo.com" required>
                            </div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="form-group">
                            <label for="telefono" class="form-label">
                                <i class="fas fa-phone"></i> Teléfono
                            </label>
                            <div class="input-group">
                                <span class="input-icon"><i class="fas fa-mobile-alt"></i></span>
                                <input type="tel" id="telefono" name="telefono" class="form-control"
                                    placeholder="987654321">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-6">
                        <div class="form-group">
                            <label for="password" class="form-label">
                                <i class="fas fa-lock"></i> Contraseña
                            </label>
                            <div class="input-group">
                                <span class="input-icon"><i class="fas fa-key"></i></span>
                                <input type="password" id="password" name="password" class="form-control"
                                    placeholder="••••••••" required minlength="6">
                            </div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="form-group">
                            <label for="password_confirm" class="form-label">
                                <i class="fas fa-lock"></i> Confirmar Contraseña
                            </label>
                            <div class="input-group">
                                <span class="input-icon"><i class="fas fa-check"></i></span>
                                <input type="password" id="password_confirm" name="password_confirm" class="form-control"
                                    placeholder="••••••••" required>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="direccion" class="form-label">
                        <i class="fas fa-map-marker-alt"></i> Dirección
                    </label>
                    <div class="input-group">
                        <span class="input-icon"><i class="fas fa-home"></i></span>
                        <input type="text" id="direccion" name="direccion" class="form-control"
                            placeholder="Av. Principal 123">
                    </div>
                </div>

                <div class="row">
                    <div class="col-6">
                        <div class="form-group">
                            <label for="ciudad" class="form-label">
                                <i class="fas fa-city"></i> Ciudad
                            </label>
                            <input type="text" id="ciudad" name="ciudad" class="form-control" placeholder="Lima">
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="form-group">
                            <label for="departamento" class="form-label">
                                <i class="fas fa-map"></i> Departamento
                            </label>
                            <input type="text" id="departamento" name="departamento" class="form-control" placeholder="Lima">
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="checkbox-label">
                        <input type="checkbox" required>
                        <span>Acepto los <a href="#" class="link-primary">Términos y Condiciones</a></span>
                    </label>
                </div>

                <button type="submit" class="btn btn-primary btn-lg w-full">
                    <i class="fas fa-user-plus"></i> Crear Cuenta
                </button>

                <div class="auth-divider"><span>o</span></div>

                <p class="text-center text-muted">
                    ¿Ya tienes una cuenta?
                    <a href="<?php echo APP_URL; ?>/auth/login" class="link-primary">Inicia sesión aquí</a>
                </p>
            </form>

            <div class="auth-footer">
                <a href="<?php echo APP_URL; ?>/">
                    <i class="fas fa-arrow-left"></i> Volver al inicio
                </a>
            </div>
        </div>
    </div>

</body>

</html>