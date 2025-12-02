<?php
require_once ROOT_PATH . '/views/layouts/header.php';
?>
<link rel="stylesheet" href="<?php echo APP_URL; ?>/css/dashboard/admin.css">

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
        <div class="stat-card">
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

        <div class="stat-card">
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

        <div class="stat-card">
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

        <div class="stat-card">
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

    <div class="dashboard-row">
        <!-- Columna izquierda -->
        <div>
            <div class="quick-actions">
                <h3><i class="fas fa-bolt"></i> Accesos Rápidos</h3>
                <div class="actions-grid">
                    <a href="<?php echo APP_URL; ?>/admin/usuarios" class="action-btn">
                        <i class="fas fa-user-plus"></i>
                        <span>Nuevo Usuario</span>
                    </a>
                    <a href="<?php echo APP_URL; ?>/admin/productos" class="action-btn">
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

            <div class="system-alerts">
                <h3><i class="fas fa-bell"></i> Alertas del Sistema</h3>
                <div class="alerts-list">
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

        <!-- Columna derecha -->
        <div>
            <div class="data-table">
                <h3><i class="fas fa-shopping-bag"></i> Pedidos Recientes</h3>
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

            <div class="data-table">
                <h3><i class="fas fa-star"></i> Productos Más Vendidos</h3>
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
                                    <div class="progress-bar">
                                        <div class="progress-fill" style="width: <?php echo $prod['porcentaje']; ?>%"></div>
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

<?php require_once ROOT_PATH . '/views/layouts/footer.php'; ?>