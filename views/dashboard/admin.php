<?php
require_once ROOT_PATH . '/views/layouts/header.php';
// La autenticación se verifica en AdminController
?>

<div class="container-fluid">
    <div class="admin-header">
        <h1><i class="fas fa-tachometer-alt"></i> Panel de Administración</h1>
        <div class="header-actions">
            <button class="btn btn-outline" onclick="location.reload()">
                <i class="fas fa-sync"></i> Actualizar
            </button>
            <span class="last-update">Última actualización: <?php echo date('d/m/Y H:i'); ?></span>
        </div>
    </div>

    <!-- Estadísticas principales -->
    <div class="stats-grid">
        <div class="stat-card glass card">
            <div class="stat-icon" style="background: linear-gradient(135deg, #3498db, #2980b9);">
                <i class="fas fa-users"></i>
            </div>
            <div class="stat-content">
                <span class="stat-value"><?php echo $total_usuarios ?? 0; ?></span>
                <span class="stat-label">Usuarios Registrados</span>
                <span class="stat-change positive">
                    <i class="fas fa-arrow-up"></i> +<?php echo $nuevos_usuarios_mes ?? 0; ?> este mes
                </span>
            </div>
        </div>

        <div class="stat-card glass card">
            <div class="stat-icon" style="background: linear-gradient(135deg, #2ecc71, #27ae60);">
                <i class="fas fa-shopping-bag"></i>
            </div>
            <div class="stat-content">
                <span class="stat-value"><?php echo $total_pedidos ?? 0; ?></span>
                <span class="stat-label">Pedidos Totales</span>
                <span class="stat-change positive">
                    <i class="fas fa-arrow-up"></i> <?php echo $pedidos_pendientes ?? 0; ?> pendientes
                </span>
            </div>
        </div>

        <div class="stat-card glass card">
            <div class="stat-icon" style="background: linear-gradient(135deg, #f39c12, #e67e22);">
                <i class="fas fa-box"></i>
            </div>
            <div class="stat-content">
                <span class="stat-value"><?php echo $total_productos ?? 0; ?></span>
                <span class="stat-label">Productos en Catálogo</span>
                <span class="stat-change <?php echo ($productos_bajo_stock ?? 0) > 0 ? 'negative' : 'neutral'; ?>">
                    <i class="fas fa-exclamation-triangle"></i> <?php echo $productos_bajo_stock ?? 0; ?> bajo stock
                </span>
            </div>
        </div>

        <div class="stat-card glass card">
            <div class="stat-icon" style="background: linear-gradient(135deg, #9b59b6, #8e44ad);">
                <i class="fas fa-dollar-sign"></i>
            </div>
            <div class="stat-content">
                <span class="stat-value">S/ <?php echo number_format($ventas_mes ?? 0, 2); ?></span>
                <span class="stat-label">Ventas del Mes</span>
                <span class="stat-change positive">
                    <i class="fas fa-arrow-up"></i> +15% vs mes anterior
                </span>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Accesos rápidos -->
        <div class="col-4">
            <div class="quick-actions glass card">
                <h3><i class="fas fa-bolt"></i> Accesos Rápidos</h3>
                <div class="actions-grid">
                    <a href="<?php echo APP_URL; ?>/admin/usuarios/nuevo" class="action-btn">
                        <i class="fas fa-user-plus"></i>
                        <span>Nuevo Usuario</span>
                    </a>
                    <a href="<?php echo APP_URL; ?>/admin/productos/nuevo" class="action-btn">
                        <i class="fas fa-box"></i>
                        <span>Nuevo Producto</span>
                    </a>
                    <a href="<?php echo APP_URL; ?>/admin/pedidos" class="action-btn">
                        <i class="fas fa-shopping-bag"></i>
                        <span>Gestión Pedidos</span>
                    </a>
                    <a href="<?php echo APP_URL; ?>/admin/sucursales" class="action-btn">
                        <i class="fas fa-store"></i>
                        <span>Sucursales</span>
                    </a>
                    <a href="<?php echo APP_URL; ?>/admin/inventario" class="action-btn">
                        <i class="fas fa-warehouse"></i>
                        <span>Inventario</span>
                    </a>
                    <a href="<?php echo APP_URL; ?>/admin/reportes" class="action-btn">
                        <i class="fas fa-chart-bar"></i>
                        <span>Reportes</span>
                    </a>
                </div>
            </div>

            <!--Alertas del sistema -->
            <div class="system-alerts glass card">
                <h3><i class="fas fa-bell"></i> Alertas del Sistema</h3>
                <div class="alerts-list">
                    <?php if (($productos_bajo_stock ?? 0) > 0): ?>
                        <div class="alert-item warning">
                            <i class="fas fa-exclamation-triangle"></i>
                            <div>
                                <strong><?php echo $productos_bajo_stock; ?> productos con stock bajo</strong>
                                <small>Requiere atención inmediata</small>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if (($pedidos_pendientes ?? 0) > 0): ?>
                        <div class="alert-item info">
                            <i class="fas fa-shopping-cart"></i>
                            <div>
                                <strong><?php echo $pedidos_pendientes; ?> pedidos pendientes</strong>
                                <small>Requieren confirmación</small>
                            </div>
                        </div>
                    <?php endif; ?>

                    <div class="alert-item success">
                        <i class="fas fa-check-circle"></i>
                        <div>
                            <strong>Sistema funcionando correctamente</strong>
                            <small>Todos los servicios operativos</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Contenido principal -->
        <div class="col-8">
            <!-- Pedidos recientes -->
            <div class="data-table glass card">
                <div class="table-header">
                    <h3><i class="fas fa-shopping-bag"></i> Pedidos Recientes</h3>
                </div>
                <div class="table-responsive">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Cliente</th>
                                <th>Fecha</th>
                                <th>Total</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="5" class="text-center py-4">No hay pedidos recientes</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Productos más vendidos -->
            <div class="data-table glass card" style="margin-top: 2rem;">
                <div class="table-header">
                    <h3><i class="fas fa-star"></i> Productos Más Vendidos</h3>
                </div>
                <div class="products-ranking">
                    <?php
                    $productos_top = [
                        ['nombre' => 'Alimento Balanceado Premium', 'ventas' => 150, 'porcentaje' => 85],
                        ['nombre' => 'Suplemento Vitamínico', 'ventas' => 120, 'porcentaje' => 68],
                        ['nombre' => 'Comedero Automático', 'ventas' => 95, 'porcentaje' => 54],
                        ['nombre' => 'Bebedero Industrial', 'ventas' => 80, 'porcentaje' => 45],
                        ['nombre' => 'Vacuna Multivalente', 'ventas' => 65, 'porcentaje' => 37],
                    ];
                    foreach ($productos_top as $index => $prod): ?>
                        <div class="ranking-item">
                            <div class="rank-number"><?php echo $index + 1; ?></div>
                            <div class="rank-info">
                                <strong><?php echo $prod['nombre']; ?></strong>
                                <div class="progress-container">
                                    <div class="progress-bar-sm">
                                        <div class="progress-fill-sm" style="width: <?php echo $prod['porcentaje']; ?>%"></div>
                                    </div>
                                    <span class="rank-value"><?php echo $prod['ventas']; ?> ventas</span>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .container-fluid {
        max-width: 1600px;
        margin: 0 auto;
        padding: 0 2rem;
    }

    .admin-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin: 2rem 0;
    }

    .header-actions {
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .last-update {
        font-size: 0.875rem;
        color: var(--color-text-muted);
    }

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2rem;
    }

    .stat-card {
        padding: 1.5rem;
        display: flex;
        gap: 1.5rem;
        transition: all var(--transition-base);
    }

    .stat-card:hover {
        transform: translateY(-4px);
        box-shadow: var(--shadow-xl);
    }

    .stat-icon {
        width: 70px;
        height: 70px;
        border-radius: var(--radius-lg);
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .stat-icon i {
        font-size: 2rem;
        color: white;
    }

    .stat-content {
        display: flex;
        flex-direction: column;
        justify-content: center;
    }

    .stat-value {
        font-size: 2rem;
        font-weight: 700;
        color: var(--color-text-primary);
        line-height: 1;
        margin-bottom: 0.25rem;
    }

    .stat-label {
        font-size: 0.875rem;
        color: var(--color-text-secondary);
        margin-bottom: 0.5rem;
    }

    .stat-change {
        font-size: 0.75rem;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 0.25rem;
    }

    .stat-change.positive {
        color: var(--color-success);
    }

    .stat-change.negative {
        color: var(--color-danger);
    }

    .stat-change.neutral {
        color: var(--color-warning);
    }

    .quick-actions,
    .system-alerts,
    .data-table {
        padding: 2rem;
        margin-bottom: 2rem;
    }

    .quick-actions h3,
    .system-alerts h3,
    .data-table h3 {
        margin-bottom: 1.5rem;
        color: var(--color-primary);
    }

    .actions-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 1rem;
    }

    .action-btn {
        padding: 1.5rem;
        background: rgba(255, 255, 255, 0.03);
        border-radius: var(--radius-lg);
        text-decoration: none;
        color: var(--color-text-primary);
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 0.75rem;
        transition: all var(--transition-base);
    }

    .action-btn:hover {
        background: linear-gradient(135deg, rgba(46, 204, 113, 0.2), rgba(52, 152, 219, 0.2));
        transform: translateY(-4px);
    }

    .action-btn i {
        font-size: 2rem;
        color: var(--color-primary);
    }

    .alerts-list {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }

    .alert-item {
        padding: 1rem;
        border-radius: var(--radius-md);
        display: flex;
        align-items: center;
        gap: 1rem;
        border-left: 4px solid;
    }

    .alert-item.warning {
        background: rgba(241, 196, 15, 0.1);
        border-color: #f1c40f;
    }

    .alert-item.info {
        background: rgba(52, 152, 219, 0.1);
        border-color: #3498db;
    }

    .alert-item.success {
        background: rgba(46, 204, 113, 0.1);
        border-color: #2ecc71;
    }

    .alert-item i {
        font-size: 1.5rem;
    }

    .alert-item.warning i {
        color: #f1c40f;
    }

    .alert-item.info i {
        color: #3498db;
    }

    .alert-item.success i {
        color: #2ecc71;
    }

    .alert-item small {
        display: block;
        font-size: 0.75rem;
        color: var(--color-text-muted);
        margin-top: 0.25rem;
    }

    .table-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.5rem;
        padding-bottom: 1rem;
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    }

    .table-responsive {
        overflow-x: auto;
    }

    .admin-table {
        width: 100%;
        border-collapse: collapse;
    }

    .admin-table thead th {
        padding: 1rem;
        text-align: left;
        border-bottom: 2px solid rgba(255, 255, 255, 0.1);
        color: var(--color-text-secondary);
        font-weight: 600;
        font-size: 0.875rem;
        text-transform: uppercase;
    }

    .admin-table tbody tr {
        border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        transition: all var(--transition-base);
    }

    .admin-table tbody tr:hover {
        background: rgba(46, 204, 113, 0.05);
    }

    .admin-table td {
        padding: 1rem;
        font-size: 0.9rem;
    }

    .products-ranking {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }

    .ranking-item {
        display: flex;
        align-items: center;
        gap: 1rem;
        padding: 1rem;
        background: rgba(255, 255, 255, 0.03);
        border-radius: var(--radius-md);
    }

    .rank-number {
        width: 40px;
        height: 40px;
        background: var(--color-primary);
        color: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 1.25rem;
    }

    .rank-info {
        flex: 1;
    }

    .progress-container {
        display: flex;
        align-items: center;
        gap: 1rem;
        margin-top: 0.5rem;
    }

    .progress-bar-sm {
        flex: 1;
        height: 6px;
        background: rgba(255, 255, 255, 0.1);
        border-radius: var(--radius-full);
        overflow: hidden;
    }

    .progress-fill-sm {
        height: 100%;
        background: linear-gradient(90deg, var(--color-primary), var(--color-secondary));
        transition: width 0.5s ease;
    }

    .rank-value {
        font-size: 0.875rem;
        color: var(--color-text-muted);
        white-space: nowrap;
    }
</style>

<?php require_once ROOT_PATH . '/views/layouts/footer.php'; ?>