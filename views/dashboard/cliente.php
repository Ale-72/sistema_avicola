<?php
require_once ROOT_PATH . '/views/layouts/header.php';

// Verificar autenticación
if (!Session::isAuthenticated()) {
    header('Location: ' . APP_URL . '/auth/login');
    exit;
}

$usuario = Session::getUser();
?>

<div class="container">
    <div class="dashboard-header">
        <div class="welcome-section">
            <h1>¡Bienvenido, <?php echo htmlspecialchars($usuario['nombre_completo']); ?>!</h1>
            <p class="text-secondary">Panel de control de tu cuenta AVITECH</p>
        </div>
        <div class="quick-stats">
            <div class="stat-card glass">
                <i class="fas fa-shopping-bag"></i>
                <div class="stat-info">
                    <span class="stat-value"><?php echo $total_pedidos ?? 0; ?></span>
                    <span class="stat-label">Pedidos</span>
                </div>
            </div>
            <div class="stat-card glass">
                <i class="fas fa-calculator"></i>
                <div class="stat-info">
                    <span class="stat-value"><?php echo $total_calculos ?? 0; ?></span>
                    <span class="stat-label">Cálculos</span>
                </div>
            </div>
            <div class="stat-card glass">
                <i class="fas fa-heart"></i>
                <div class="stat-info">
                    <span class="stat-value"><?php echo $total_favoritos ?? 0; ?></span>
                    <span class="stat-label">Favoritos</span>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Menú lateral -->
        <div class="col-3">
            <div class="dashboard-sidebar glass card sticky-sidebar">
                <div class="user-profile-section">
                    <div class="user-avatar">
                        <?php if ($usuario['avatar']): ?>
                            <img src="<?php echo APP_URL . '/uploads/avatars/' . $usuario['avatar']; ?>" alt="Avatar">
                        <?php else: ?>
                            <i class="fas fa-user-circle"></i>
                        <?php endif; ?>
                    </div>
                    <div class="user-info">
                        <strong><?php echo htmlspecialchars($usuario['nombre_completo']); ?></strong>
                        <small><?php echo htmlspecialchars($usuario['email']); ?></small>
                        <span class="badge badge-success"><?php echo ucfirst($usuario['rol']); ?></span>
                    </div>
                </div>

                <nav class="dashboard-nav">
                    <a href="#overview" class="nav-item active" data-section="overview">
                        <i class="fas fa-home"></i> Resumen
                    </a>
                    <a href="#pedidos" class="nav-item" data-section="pedidos">
                        <i class="fas fa-shopping-bag"></i> Mis Pedidos
                    </a>
                    <a href="#calculos" class="nav-item" data-section="calculos">
                        <i class="fas fa-calculator"></i> Mis Cálculos
                    </a>
                    <a href="#perfil" class="nav-item" data-section="perfil">
                        <i class="fas fa-user-edit"></i> Mi Perfil
                    </a>
                    <a href="#seguridad" class="nav-item" data-section="seguridad">
                        <i class="fas fa-lock"></i> Seguridad
                    </a>
                </nav>

                <div class="sidebar-footer">
                    <a href="<?php echo APP_URL; ?>/auth/logout" class="btn btn-danger-outline w-full">
                        <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
                    </a>
                </div>
            </div>
        </div>

        <!-- Contenido principal -->
        <div class="col-9">
            <!-- Sección Overview -->
            <div id="overview" class="dashboard-section active">
                <h2><i class="fas fa-chart-line"></i> Resumen de Actividad</h2>

                <!-- Pedidos recientes -->
                <div class="content-card glass card">
                    <div class="card-header">
                        <h3><i class="fas fa-shopping-bag"></i> Pedidos Recientes</h3>
                        <a href="#pedidos" class="btn btn-outline btn-sm" onclick="switchSection('pedidos')">Ver todos</a>
                    </div>
                    <?php if (!empty($pedidos_recientes)): ?>
                        <div class="orders-list">
                            <?php foreach (array_slice($pedidos_recientes, 0, 5) as $pedido): ?>
                                <div class="order-item">
                                    <div class="order-info">
                                        <strong>Pedido #<?php echo $pedido['id_pedido']; ?></strong>
                                        <small><?php echo date('d/m/Y H:i', strtotime($pedido['fecha_pedido'])); ?></small>
                                    </div>
                                    <div class="order-status">
                                        <span class="badge badge-<?php
                                                                    echo match ($pedido['estado_pedido']) {
                                                                        'pendiente' => 'warning',
                                                                        'confirmado' => 'info',
                                                                        'enviado' => 'primary',
                                                                        'entregado' => 'success',
                                                                        'cancelado' => 'danger',
                                                                        default => 'secondary'
                                                                    };
                                                                    ?>">
                                            <?php echo ucfirst($pedido['estado_pedido']); ?>
                                        </span>
                                    </div>
                                    <div class="order-total">
                                        S/ <?php echo number_format($pedido['monto_total'], 2); ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="empty-state-sm">
                            <i class="fas fa-shopping-bag"></i>
                            <p>No tienes pedidos aún</p>
                            <a href="<?php echo APP_URL; ?>/tienda" class="btn btn-primary btn-sm">Ir a la tienda</a>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Cálculos recientes -->
                <div class="content-card glass card">
                    <div class="card-header">
                        <h3><i class="fas fa-calculator"></i> Cálculos Recientes</h3>
                        <a href="#calculos" class="btn btn-outline btn-sm" onclick="switchSection('calculos')">Ver todos</a>
                    </div>
                    <?php if (!empty($calculos_recientes)): ?>
                        <div class="calculations-grid">
                            <?php foreach (array_slice($calculos_recientes, 0, 3) as $calc): ?>
                                <div class="calc-card">
                                    <div class="calc-header">
                                        <strong><?php echo htmlspecialchars($calc['nombre_tipo']); ?></strong>
                                        <small><?php echo date('d/m/Y', strtotime($calc['fecha_calculo'])); ?></small>
                                    </div>
                                    <div class="calc-details">
                                        <div class="detail">
                                            <i class="fas fa-kiwi-bird"></i>
                                            <span><?php echo $calc['cantidad_aves']; ?> aves</span>
                                        </div>
                                        <div class="detail">
                                            <i class="fas fa-calendar"></i>
                                            <span><?php echo $calc['edad_dias']; ?> días</span>
                                        </div>
                                    </div>
                                    <div class="calc-result">
                                        <div class="result-item">
                                            <label>Alimento:</label>
                                            <strong><?php echo $calc['resultado_alimento_kg']; ?> kg</strong>
                                        </div>
                                        <div class="result-item">
                                            <label>Agua:</label>
                                            <strong><?php echo $calc['resultado_agua_litros']; ?> L</strong>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="empty-state-sm">
                            <i class="fas fa-calculator"></i>
                            <p>No has realizado cálculos</p>
                            <a href="<?php echo APP_URL; ?>/calculadora" class="btn btn-primary btn-sm">Ir a calculadora</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Sección Pedidos (oculta inicialmente) -->
            <div id="pedidos" class="dashboard-section">
                <h2><i class="fas fa-shopping-bag"></i> Historial de Pedidos</h2>
                <div class="content-card glass card">
                    <!-- Contenido de pedidos completo -->
                    <p class="text-center py-4">Sección en desarrollo...</p>
                </div>
            </div>

            <!-- Sección Cálculos (oculta inicialmente) -->
            <div id="calculos" class="dashboard-section">
                <h2><i class="fas fa-calculator"></i> Historial de Cálculos</h2>
                <div class="content-card glass card">
                    <!-- Contenido de cálculos completo -->
                    <p class="text-center py-4">Sección en desarrollo...</p>
                </div>
            </div>

            <!-- Sección Perfil -->
            <div id="perfil" class="dashboard-section">
                <h2><i class="fas fa-user-edit"></i> Mi Perfil</h2>
                <div class="content-card glass card">
                    <form id="profileForm" class="profile-form">
                        <div class="row">
                            <div class="col-6">
                                <div class="form-group">
                                    <label>Nombre Completo</label>
                                    <input type="text" name="nombre_completo" class="form-control"
                                        value="<?php echo htmlspecialchars($usuario['nombre_completo']); ?>">
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="form-group">
                                    <label>Email</label>
                                    <input type="email" name="email" class="form-control"
                                        value="<?php echo htmlspecialchars($usuario['email']); ?>" readonly>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-6">
                                <div class="form-group">
                                    <label>Teléfono</label>
                                    <input type="tel" name="telefono" class="form-control"
                                        value="<?php echo htmlspecialchars($usuario['telefono'] ?? ''); ?>">
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="form-group">
                                    <label>Dirección</label>
                                    <input type="text" name="direccion" class="form-control"
                                        value="<?php echo htmlspecialchars($usuario['direccion'] ?? ''); ?>">
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Guardar Cambios
                        </button>
                    </form>
                </div>
            </div>

            <!-- Sección Seguridad -->
            <div id="seguridad" class="dashboard-section">
                <h2><i class="fas fa-lock"></i> Seguridad</h2>
                <div class="content-card glass card">
                    <h3>Cambiar Contraseña</h3>
                    <form id="passwordForm" class="password-form">
                        <div class="form-group">
                            <label>Contraseña Actual</label>
                            <input type="password" name="password_actual" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Nueva Contraseña</label>
                            <input type="password" name="password_nueva" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Confirmar Nueva Contraseña</label>
                            <input type="password" name="password_confirmar" class="form-control" required>
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-key"></i> Cambiar Contraseña
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .dashboard-header {
        margin: 3rem 0 2rem;
    }

    .welcome-section h1 {
        font-size: 2rem;
        margin-bottom: 0.5rem;
    }

    .quick-stats {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1.5rem;
        margin-top: 2rem;
    }

    .stat-card {
        padding: 1.5rem;
        display: flex;
        align-items: center;
        gap: 1.5rem;
        transition: all var(--transition-base);
    }

    .stat-card:hover {
        transform: translateY(-4px);
    }

    .stat-card i {
        font-size: 3rem;
        color: var(--color-primary);
    }

    .stat-info {
        display: flex;
        flex-direction: column;
    }

    .stat-value {
        font-size: 2rem;
        font-weight: 700;
        color: var(--color-primary);
    }

    .stat-label {
        color: var(--color-text-secondary);
        font-size: 0.875rem;
    }

    .dashboard-sidebar {
        padding: 2rem;
        position: sticky;
        top: 100px;
    }

    .user-profile-section {
        text-align: center;
        padding-bottom: 2rem;
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        margin-bottom: 2rem;
    }

    .user-avatar {
        width: 100px;
        height: 100px;
        margin: 0 auto 1rem;
        border-radius: 50%;
        overflow: hidden;
        background: linear-gradient(135deg, var(--color-primary), var(--color-secondary));
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .user-avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .user-avatar i {
        font-size: 4rem;
        color: white;
    }

    .user-info {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }

    .user-info small {
        color: var(--color-text-muted);
        font-size: 0.875rem;
    }

    .dashboard-nav {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
        margin-bottom: 2rem;
    }

    .dashboard-nav .nav-item {
        padding: 1rem;
        border-radius: var(--radius-md);
        color: var(--color-text-primary);
        text-decoration: none;
        display: flex;
        align-items: center;
        gap: 0.75rem;
        transition: all var(--transition-base);
    }

    .dashboard-nav .nav-item:hover {
        background: rgba(46, 204, 113, 0.1);
        transform: translateX(4px);
    }

    .dashboard-nav .nav-item.active {
        background: linear-gradient(135deg, rgba(46, 204, 113, 0.2), rgba(52, 152, 219, 0.2));
        border-left: 4px solid var(--color-primary);
    }

    .dashboard-section {
        display: none;
    }

    .dashboard-section.active {
        display: block;
    }

    .dashboard-section h2 {
        margin-bottom: 2rem;
        color: var(--color-primary);
    }

    .content-card {
        padding: 2rem;
        margin-bottom: 2rem;
    }

    .card-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.5rem;
        padding-bottom: 1rem;
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    }

    .card-header h3 {
        margin: 0;
    }

    .orders-list {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }

    .order-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 1rem;
        background: rgba(255, 255, 255, 0.03);
        border-radius: var(--radius-md);
        transition: all var(--transition-base);
    }

    .order-item:hover {
        background: rgba(46, 204, 113, 0.05);
        transform: translateX(4px);
    }

    .order-info small {
        display: block;
        color: var(--color-text-muted);
        font-size: 0.8rem;
        margin-top: 0.25rem;
    }

    .order-total {
        font-weight: 700;
        color: var(--color-primary);
        font-size: 1.1rem;
    }

    .calculations-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
        gap: 1.5rem;
    }

    .calc-card {
        padding: 1.5rem;
        background: rgba(255, 255, 255, 0.03);
        border-radius: var(--radius-lg);
        border-left: 4px solid var(--color-primary);
    }

    .calc-header {
        display: flex;
        justify-content: space-between;
        margin-bottom: 1rem;
    }

    .calc-header small {
        color: var(--color-text-muted);
        font-size: 0.75rem;
    }

    .calc-details {
        display: flex;
        gap: 1rem;
        margin-bottom: 1rem;
        padding: 0.75rem;
        background: rgba(255, 255, 255, 0.03);
        border-radius: var(--radius-sm);
    }

    .calc-details .detail {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.875rem;
    }

    .calc-details i {
        color: var(--color-primary);
    }

    .calc-result {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 0.75rem;
    }

    .result-item {
        display: flex;
        flex-direction: column;
    }

    .result-item label {
        font-size: 0.75rem;
        color: var(--color-text-muted);
    }

    .result-item strong {
        color: var(--color-primary);
        font-size: 1.1rem;
    }

    .empty-state-sm {
        text-align: center;
        padding: 3rem 2rem;
    }

    .empty-state-sm i {
        font-size: 3rem;
        opacity: 0.2;
        margin-bottom: 1rem;
    }

    .profile-form,
    .password-form {
        max-width: 600px;
    }
</style>

<script>
    function switchSection(sectionId) {
        // Ocultar todas las secciones
        document.querySelectorAll('.dashboard-section').forEach(section => {
            section.classList.remove('active');
        });

        // Remover active de todos los nav items
        document.querySelectorAll('.dashboard-nav .nav-item').forEach(item => {
            item.classList.remove('active');
        });

        // Mostrar sección seleccionada
        document.getElementById(sectionId).classList.add('active');

        // Marcar nav item como activo
        document.querySelector(`[data-section="${sectionId}"]`)?.classList.add('active');
    }

    // Event listeners para navegación
    document.querySelectorAll('.dashboard-nav .nav-item').forEach(item => {
        item.addEventListener('click', function(e) {
            e.preventDefault();
            const section = this.dataset.section;
            switchSection(section);
        });
    });

    // Guardar perfil
    document.getElementById('profileForm')?.addEventListener('submit', async function(e) {
        e.preventDefault();
        alert('Funcionalidad de actualización de perfil en desarrollo');
    });

    // Cambiar contraseña
    document.getElementById('passwordForm')?.addEventListener('submit', async function(e) {
        e.preventDefault();
        alert('Funcionalidad de cambio de contraseña en desarrollo');
    });
</script>

<?php require_once ROOT_PATH . '/views/layouts/footer.php'; ?>