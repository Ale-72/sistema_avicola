<?php
require_once ROOT_PATH . '/views/layouts/header.php';
?>
<link rel="stylesheet" href="<?php echo APP_URL; ?>/css/dashboard/admin.css">
<link rel="stylesheet" href="<?php echo APP_URL; ?>/css/dashboard/productos.css">
<link rel="stylesheet" href="<?php echo APP_URL; ?>/css/dashboard/reportes.css">

<div class="container-fluid">
    <div class="admin-header">
        <h1><i class="fas fa-chart-line"></i> Reportes y Analíticas</h1>
        <div class="header-actions">
            <div class="exportButtons">
                <button class="btnExport pdf" onclick="exportarPDF()">
                    <i class="fas fa-file-pdf"></i> Exportar PDF
                </button>
                <button class="btnExport excel" onclick="exportarExcel()">
                    <i class="fas fa-file-excel"></i> Exportar Excel
                </button>
            </div>
            <a href="<?php echo APP_URL; ?>/admin/dashboard" class="btn btn-outline">
                <i class="fas fa-arrow-left"></i> Volver
            </a>
        </div>
    </div>

    <!-- Cards de Métricas -->
    <div class="metricsGrid">
        <div class="metricCard">
            <div class="metricHeader">
                <div>
                    <div class="metricValue">S/ <?php echo number_format($ventas_mes ?? 0, 2); ?></div>
                    <div class="metricLabel">Ventas del Mes</div>
                </div>
                <div class="metricIcon" style="background: linear-gradient(135deg, #2ecc71, #27ae60);">
                    <i class="fas fa-dollar-sign"></i>
                </div>
            </div>
            <div class="metricChange positive">
                <i class="fas fa-arrow-up"></i> Mes actual
            </div>
        </div>

        <div class="metricCard">
            <div class="metricHeader">
                <div>
                    <div class="metricValue"><?php echo $pedidos_mes ?? 0; ?></div>
                    <div class="metricLabel">Pedidos Completados</div>
                </div>
                <div class="metricIcon" style="background: linear-gradient(135deg, #3498db, #2980b9);">
                    <i class="fas fa-shopping-bag"></i>
                </div>
            </div>
            <div class="metricChange positive">
                <i class="fas fa-check-circle"></i> Este mes
            </div>
        </div>

        <div class="metricCard">
            <div class="metricHeader">
                <div>
                    <div class="metricValue"><?php echo $productos_activos ?? 0; ?></div>
                    <div class="metricLabel">Productos Activos</div>
                </div>
                <div class="metricIcon" style="background: linear-gradient(135deg, #9b59b6, #8e44ad);">
                    <i class="fas fa-box"></i>
                </div>
            </div>
            <div class="metricChange">
                <i class="fas fa-boxes"></i> En catálogo
            </div>
        </div>

        <div class="metricCard">
            <div class="metricHeader">
                <div>
                    <div class="metricValue"><?php echo $stock_bajo ?? 0; ?></div>
                    <div class="metricLabel">Stock Bajo</div>
                </div>
                <div class="metricIcon" style="background: linear-gradient(135deg, #f39c12, #e67e22);">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
            </div>
            <div class="metricChange negative">
                <i class="fas fa-warehouse"></i> Requieren reposición
            </div>
        </div>
    </div>

    <!-- Filtros de Fecha -->
    <div class="chartContainer">
        <div class="chartHeader">
            <div class="chartTitle">
                <i class="fas fa-filter"></i> Filtros de Periodo
            </div>
        </div>
        <div class="dateFilters">
            <div class="filterGroup">
                <label class="filterLabel">Desde</label>
                <input type="date" id="fechaDesde" class="filterInput">
            </div>
            <div class="filterGroup">
                <label class="filterLabel">Hasta</label>
                <input type="date" id="fechaHasta" class="filterInput">
            </div>
            <div class="filterGroup">
                <label class="filterLabel">&nbsp;</label>
                <button id="btnAplicarFiltros" class="btn btn-primary" style="padding: 0.65rem 1.5rem;">
                    <i class="fas fa-search"></i> Aplicar
                </button>
            </div>
            <div class="filterGroup" style="flex: 1;">
                <label class="filterLabel">Filtros Rápidos</label>
                <div class="quickFilters">
                    <button class="quickFilter" onclick="filtroRapido('hoy')">Hoy</button>
                    <button class="quickFilter" onclick="filtroRapido('7dias')">Últimos 7 días</button>
                    <button class="quickFilter active" onclick="filtroRapido('30dias')">Últimos 30 días</button>
                    <button class="quickFilter" onclick="filtroRapido('mes')">Este Mes</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Gráficos Principales -->
    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 1.5rem; margin-bottom: 2rem;">
        <!-- Gráfico de Ventas -->
        <div class="chartContainer">
            <div class="chartHeader">
                <div class="chartTitle">
                    <i class="fas fa-chart-line"></i> Tendencia de Ventas
                </div>
            </div>
            <div class="chartCanvas">
                <canvas id="chartVentas"></canvas>
            </div>
            <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem; margin-top: 1.5rem; padding-top: 1.5rem; border-top: 1px solid rgba(255,255,255,0.1);">
                <div style="text-align: center;">
                    <div style="font-size: 0.8rem; color: rgba(255,255,255,0.6); margin-bottom: 0.25rem;">Total Pedidos</div>
                    <div id="totalPedidos" style="font-size: 1.5rem; font-weight: 700; color: #3498db;">0</div>
                </div>
                <div style="text-align: center;">
                    <div style="font-size: 0.8rem; color: rgba(255,255,255,0.6); margin-bottom: 0.25rem;">Total Ventas</div>
                    <div id="totalVentas" style="font-size: 1.5rem; font-weight: 700; color: #2ecc71;">S/ 0</div>
                </div>
                <div style="text-align: center;">
                    <div style="font-size: 0.8rem; color: rgba(255,255,255,0.6); margin-bottom: 0.25rem;">Ticket Promedio</div>
                    <div id="ticketPromedio" style="font-size: 1.5rem; font-weight: 700; color: #9b59b6;">S/ 0</div>
                </div>
            </div>
        </div>

        <!-- Gráfico de Inventario -->
        <div class="chartContainer">
            <div class="chartHeader">
                <div class="chartTitle">
                    <i class="fas fa-warehouse"></i> Estado de Inventario
                </div>
            </div>
            <div class="chartCanvas">
                <canvas id="chartInventario"></canvas>
            </div>
        </div>
    </div>

    <!-- Top Productos -->
    <div class="chartContainer">
        <div class="chartHeader">
            <div class="chartTitle">
                <i class="fas fa-trophy"></i> Top 5 Productos Más Vendidos
            </div>
        </div>
        <div style="display: grid; grid-template-columns: 3fr 2fr; gap: 2rem;">
            <div class="chartCanvas">
                <canvas id="chartProductos"></canvas>
            </div>
            <div>
                <table class="reportTable">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Producto</th>
                            <th>Cantidad</th>
                            <th>Ingresos</th>
                            <th>%</th>
                        </tr>
                    </thead>
                    <tbody id="tablaProductos">
                        <tr>
                            <td colspan="5" style="text-align: center; color: rgba(255,255,255,0.5);">Cargando...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js desde CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

<script>
    window.APP_URL = '<?php echo APP_URL; ?>';
</script>
<script src="<?php echo APP_URL; ?>/js/dashboard/reportes.js"></script>

<?php require_once ROOT_PATH . '/views/layouts/footer.php'; ?>