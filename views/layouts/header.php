<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title><?php echo $title ?? 'AVITECH - Sistema Integral de Gestión Avícola'; ?></title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Poppins:wght@600;700;800&display=swap" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Estilos -->
    <link rel="stylesheet" href="<?php echo APP_URL; ?>/css/style.css">
    <link rel="stylesheet" href="<?php echo APP_URL; ?>/css/navbar.css">
</head>

<body>

    <!-- Navbar -->
    <nav class="navbar glass">
        <div class="container">
            <div class="navbar-brand">
                <a href="<?php echo APP_URL; ?>/" class="logo">
                    <i class="fas fa-kiwi-bird"></i>
                    <span>AVI<strong>TECH</strong></span>
                </a>
            </div>

            <button class="navbar-toggler" id="navbarToggler">
                <span></span>
                <span></span>
                <span></span>
            </button>

            <div class="navbar-menu" id="navbarMenu">
                <ul class="navbar-nav">
                    <li><a href="<?php echo APP_URL; ?>/" class="nav-link"><i class="fas fa-home"></i> Inicio</a></li>
                    <li><a href="<?php echo APP_URL; ?>/tienda" class="nav-link"><i class="fas fa-store"></i> Tienda</a></li>
                    <li><a href="<?php echo APP_URL; ?>/aveologia" class="nav-link"><i class="fas fa-book-medical"></i> Aveología</a></li>
                    <li><a href="<?php echo APP_URL; ?>/calculadora" class="nav-link"><i class="fas fa-calculator"></i> Calculadora</a></li>

                    <?php if (Session::isAuthenticated()): ?>
                        <li class="nav-item-dropdown">
                            <a href="#" class="nav-link">
                                <i class="fas fa-user-circle"></i>
                                <?php echo Session::getUserName(); ?>
                                <i class="fas fa-chevron-down"></i>
                            </a>
                            <ul class="dropdown-menu">
                                <?php if (Session::getUserRole() === 'Administrador'): ?>
                                    <li><a href="<?php echo APP_URL; ?>/admin/dashboard"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                                <?php elseif (Session::getUserRole() === 'Encargado Sucursal'): ?>
                                    <li><a href="<?php echo APP_URL; ?>/sucursal/dashboard"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                                <?php else: ?>
                                    <li><a href="<?php echo APP_URL; ?>/cliente/dashboard"><i class="fas fa-user"></i> Mi Cuenta</a></li>
                                <?php endif; ?>
                                <li><a href="<?php echo APP_URL; ?>/cliente/pedidos"><i class="fas fa-shopping-bag"></i> Mis Pedidos</a></li>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li><a href="<?php echo APP_URL; ?>/auth/logout"><i class="fas fa-sign-out-alt"></i> Cerrar Sesión</a></li>
                            </ul>
                        </li>
                        <li>
                            <a href="<?php echo APP_URL; ?>/carrito" class="nav-link cart-link">
                                <i class="fas fa-shopping-cart"></i>
                                <span class="cart-badge" id="cartCount">0</span>
                            </a>
                        </li>
                    <?php else: ?>
                        <li><a href="<?php echo APP_URL; ?>/auth/login" class="btn btn-outline btn-sm"><i class="fas fa-sign-in-alt"></i> Ingresar</a></li>
                        <li><a href="<?php echo APP_URL; ?>/auth/register" class="btn btn-primary btn-sm"><i class="fas fa-user-plus"></i> Registrarse</a></li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Mensajes Flash -->
    <?php if ($successMsg = Session::getFlash('success')): ?>
        <div class="container mt-3">
            <div class="alert alert-success slide-in">
                <i class="fas fa-check-circle"></i> <?php echo $successMsg; ?>
            </div>
        </div>
    <?php endif; ?>

    <?php if ($errorMsg = Session::getFlash('error')): ?>
        <div class="container mt-3">
            <div class="alert alert-danger slide-in">
                <i class="fas fa-exclamation-circle"></i> <?php echo $errorMsg; ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- Contenido principal -->
    <main class="main-content">