<?php
require_once ROOT_PATH . '/views/layouts/header.php';
?>
<link rel="stylesheet" href="<?php echo APP_URL; ?>/css/dashboard/admin.css">
<link rel="stylesheet" href="<?php echo APP_URL; ?>/css/dashboard/productos.css">
<link rel="stylesheet" href="<?php echo APP_URL; ?>/css/dashboard/pedidos.css">

<div class="container-fluid">
    <div class="admin-header">
        <h1><i class="fas fa-shopping-bag"></i> Gestión de Pedidos</h1>
        <div class="header-actions">
            <a href="<?php echo APP_URL; ?>/admin/dashboard" class="btn btn-outline">
                <i class="fas fa-arrow-left"></i> Volver
            </a>
        </div>
    </div>

    <!-- Estadísticas rápidas -->
    <div class="stats-grid" style="margin-bottom: 2rem;">
        <div class="stat-card">
            <div class="stat-icon" style="background: linear-gradient(135deg, #3498db, #2980b9);">
                <i class="fas fa-shopping-bag"></i>
            </div>
            <div class="stat-content">
                <span class="stat-value"><?php echo count($pedidos ?? []); ?></span>
                <span class="stat-label">Total Pedidos</span>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background: linear-gradient(135deg, #f1c40f, #f39c12);">
                <i class="fas fa-clock"></i>
            </div>
            <div class="stat-content">
                <span class="stat-value">
                    <?php echo count(array_filter($pedidos ?? [], fn($p) => $p['nombre_estado'] === 'Pendiente' || $p['nombre_estado'] === 'Confirmado')); ?>
                </span>
                <span class="stat-label">Pendientes</span>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background: linear-gradient(135deg, #2ecc71, #27ae60);">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="stat-content">
                <span class="stat-value">
                    <?php echo count(array_filter($pedidos ?? [], fn($p) => $p['nombre_estado'] === 'Entregado')); ?>
                </span>
                <span class="stat-label">Entregados</span>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background: linear-gradient(135deg, #9b59b6, #8e44ad);">
                <i class="fas fa-dollar-sign"></i>
            </div>
            <div class="stat-content">
                <span class="stat-value">
                    S/ <?php
                        $suma = array_sum(array_map(fn($p) => $p['total'], $pedidos ?? []));
                        echo number_format($suma, 2);
                        ?>
                </span>
                <span class="stat-label">Total Ventas</span>
            </div>
        </div>
    </div>

    <div class="data-table">
        <div class="table-header">
            <h3><i class="fas fa-list"></i> Historial de Pedidos</h3>
            <div class="filters-row">
                <div class="filter-group">
                    <input type="text" id="searchInput" class="search-input" placeholder="Buscar por número o cliente..."
                        style="padding: 0.5rem 1rem; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); border-radius: 8px; color: #fff; min-width: 300px;">
                </div>
                <div class="filter-group">
                    <select id="filterEstado" class="search-input"
                        style="padding: 0.5rem 1rem; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); border-radius: 8px; color: #fff;">
                        <option value="">Todos los estados</option>
                        <?php
                        $estados = [];
                        foreach ($pedidos ?? [] as $pedido) {
                            if (!isset($estados[$pedido['id_estado']])) {
                                $estados[$pedido['id_estado']] = $pedido['nombre_estado'];
                            }
                        }
                        foreach ($estados as $id => $nombre):
                        ?>
                            <option value="<?php echo $id; ?>"><?php echo htmlspecialchars($nombre); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="filter-group">
                    <select id="filterPago" class="search-input"
                        style="padding: 0.5rem 1rem; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); border-radius: 8px; color: #fff;">
                        <option value="">Todos los métodos</option>
                        <option value="efectivo">Efectivo</option>
                        <option value="tarjeta">Tarjeta</option>
                        <option value="transferencia">Transferencia</option>
                        <option value="yape">Yape</option>
                        <option value="plin">Plin</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="table-responsive">
            <table class="admin-table" id="pedidosTable">
                <thead>
                    <tr>
                        <th>Número</th>
                        <th>Cliente</th>
                        <th>Fecha</th>
                        <th>Total</th>
                        <th>Método Pago</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($pedidos)): ?>
                        <?php foreach ($pedidos as $pedido): ?>
                            <tr data-estado="<?php echo $pedido['id_estado']; ?>"
                                data-metodopago="<?php echo $pedido['metodo_pago']; ?>">
                                <td>
                                    <div style="font-weight: 700; color: #3498db;">
                                        <?php echo htmlspecialchars($pedido['numero_pedido']); ?>
                                    </div>
                                    <div style="font-size: 0.75rem; color: rgba(255,255,255,0.5);">
                                        ID: #<?php echo $pedido['id_pedido']; ?>
                                    </div>
                                </td>
                                <td>
                                    <div style="font-weight: 600;"><?php echo htmlspecialchars($pedido['cliente_nombre']); ?></div>
                                    <div style="font-size: 0.8rem; color: rgba(255,255,255,0.6);">
                                        <?php echo htmlspecialchars($pedido['cliente_email']); ?>
                                    </div>
                                </td>
                                <td>
                                    <div><?php echo date('d/m/Y', strtotime($pedido['fecha_pedido'])); ?></div>
                                    <div style="font-size: 0.8rem; color: rgba(255,255,255,0.6);">
                                        <?php echo date('H:i', strtotime($pedido['fecha_pedido'])); ?>
                                    </div>
                                </td>
                                <td>
                                    <div style="font-weight: 700; color: #2ecc71; font-size: 1.1rem;">
                                        S/ <?php echo number_format($pedido['total'], 2); ?>
                                    </div>
                                </td>
                                <td>
                                    <span class="metodo-pago <?php echo $pedido['metodo_pago']; ?>">
                                        <i class="fas fa-<?php
                                                            switch ($pedido['metodo_pago']) {
                                                                case 'efectivo':
                                                                    echo 'money-bill-wave';
                                                                    break;
                                                                case 'tarjeta':
                                                                    echo 'credit-card';
                                                                    break;
                                                                case 'transferencia':
                                                                    echo 'exchange-alt';
                                                                    break;
                                                                default:
                                                                    echo 'mobile-alt';
                                                            }
                                                            ?>"></i>
                                        <?php echo ucfirst($pedido['metodo_pago']); ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge-estado" style="background-color: <?php echo $pedido['estado_color']; ?>20; color: <?php echo $pedido['estado_color']; ?>; border: 1px solid <?php echo $pedido['estado_color']; ?>40;">
                                        <i class="fas fa-circle" style="font-size: 0.5rem;"></i>
                                        <?php echo htmlspecialchars($pedido['nombre_estado']); ?>
                                    </span>
                                </td>
                                <td>
                                    <button class="btn-icon" title="Ver detalles" onclick="verPedido(<?php echo $pedido['id_pedido']; ?>)">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center py-4">
                                <i class="fas fa-shopping-bag" style="font-size: 3rem; color: rgba(255,255,255,0.3); margin-bottom: 1rem;"></i>
                                <p style="margin: 0; color: rgba(255,255,255,0.6);">No hay pedidos registrados</p>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal para ver detalles del pedido -->
<div id="modalDetalles" class="modal" style="display: none;">
    <div class="modal-content modal-large">
        <div class="modal-header" style="display: flex; justify-content: space-between; align-items: center; padding: 1.5rem; border-bottom: 1px solid rgba(255,255,255,0.1);">
            <div>
                <h3 style="margin: 0; color: #3498db;"><i class="fas fa-shopping-bag"></i> Detalles del Pedido</h3>
                <p style="margin: 0.5rem 0 0 0; color: rgba(255,255,255,0.6); font-size: 0.9rem;">
                    <strong id="detalleNumeroPedido"></strong> | <span id="detalleFecha"></span>
                </p>
            </div>
            <div style="display: flex; align-items: center; gap: 1rem;">
                <div id="detalleEstado"></div>
                <button onclick="cerrarModalDetalles()" style="background: none; border: none; color: white; font-size: 1.5rem; cursor: pointer;">&times;</button>
            </div>
        </div>
        <div class="modal-body" style="padding: 2rem; max-height: 70vh; overflow-y: auto;">
            <div class="pedido-info-grid">
                <!-- Información del cliente -->
                <div class="info-section">
                    <div class="info-section-title">
                        <i class="fas fa-user"></i> Información del Cliente
                    </div>
                    <div class="info-item">
                        <div class="info-label">Nombre</div>
                        <div class="info-value" id="detalleClienteNombre"></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Email</div>
                        <div class="info-value" id="detalleClienteEmail"></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Teléfono</div>
                        <div class="info-value" id="detalleClienteTelefono"></div>
                    </div>
                </div>

                <!-- Información de entrega -->
                <div class="info-section">
                    <div class="info-section-title">
                        <i class="fas fa-map-marker-alt"></i> Información de Entrega
                    </div>
                    <div class="info-item">
                        <div class="info-label">Dirección</div>
                        <div class="info-value" id="detalleDireccion"></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Ciudad</div>
                        <div class="info-value" id="detalleCiudad"></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Método</div>
                        <div class="info-value" id="detalleMetodoEntrega"></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Sucursal</div>
                        <div class="info-value" id="detalleSucursal"></div>
                    </div>
                </div>

                <!-- Información de pago -->
                <div class="info-section">
                    <div class="info-section-title">
                        <i class="fas fa-credit-card"></i> Información de Pago
                    </div>
                    <div class="info-item">
                        <div class="info-label">Método de Pago</div>
                        <div class="info-value" id="detalleMetodoPago"></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Estado de Pago</div>
                        <div class="info-value" id="detalleEstadoPago"></div>
                    </div>
                </div>
            </div>

            <!-- Productos del pedido -->
            <div style="margin-top: 2rem;">
                <h4 style="margin: 0 0 1rem 0; color: rgba(255,255,255,0.9); display: flex; align-items: center; gap: 0.5rem;">
                    <i class="fas fa-box"></i> Productos del Pedido
                </h4>
                <table class="productos-pedido-table">
                    <thead>
                        <tr>
                            <th>Código</th>
                            <th>Producto</th>
                            <th style="text-align: center;">Cantidad</th>
                            <th style="text-align: right;">Precio Unit.</th>
                            <th style="text-align: right;">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody id="productosBody">
                        <tr>
                            <td colspan="5" class="text-center">Cargando...</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Totales -->
            <div class="totales-pedido">
                <div class="total-row">
                    <span class="total-label">Subtotal:</span>
                    <span class="total-value" id="detalleSubtotal">S/ 0.00</span>
                </div>
                <div class="total-row">
                    <span class="total-label">Costo de Envío:</span>
                    <span class="total-value" id="detalleCostoEnvio">S/ 0.00</span>
                </div>
                <div class="total-row">
                    <span class="total-label">Descuento:</span>
                    <span class="total-value" id="detalleDescuento">S/ 0.00</span>
                </div>
                <div class="total-row">
                    <span class="total-label">TOTAL:</span>
                    <span class="total-value" id="detalleTotal">S/ 0.00</span>
                </div>
            </div>

            <!-- Historial de estados -->
            <div style="margin-top: 2rem;">
                <h4 style="margin: 0 0 1rem 0; color: rgba(255,255,255,0.9); display: flex; align-items: center; gap: 0.5rem;">
                    <i class="fas fa-history"></i> Historial de Estados
                </h4>
                <div class="timeline" id="timelineHistorial">
                    <p style="color: rgba(255,255,255,0.6); text-align: center;">Cargando historial...</p>
                </div>
            </div>

            <!-- Acciones -->
            <div class="modal-actions">
                <button type="button" onclick="abrirModalCambiarEstado()" class="btn btn-primary">
                    <i class="fas fa-edit"></i> Cambiar Estado
                </button>
                <button type="button" onclick="cerrarModalDetalles()" class="btn btn-outline">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal para cambiar estado del pedido -->
<div id="modalCambiarEstado" class="modal" style="display: none;">
    <div class="modal-content" style="max-width: 500px;">
        <div class="modal-header" style="display: flex; justify-content: space-between; align-items: center; padding: 1.5rem; border-bottom: 1px solid rgba(255,255,255,0.1);">
            <h3 style="margin: 0; color: #f1c40f;"><i class="fas fa-edit"></i> Cambiar Estado</h3>
            <button onclick="cerrarModalCambiarEstado()" style="background: none; border: none; color: white; font-size: 1.5rem; cursor: pointer;">&times;</button>
        </div>
        <div class="modal-body" style="padding: 2rem;">
            <form id="formCambiarEstado">
                <div class="form-group" style="margin-bottom: 1.5rem;">
                    <label style="display: block; margin-bottom: 0.5rem; color: rgba(255,255,255,0.9); font-weight: 600;">Nuevo Estado *</label>
                    <select id="nuevoEstado" name="id_estado" required
                        style="width: 100%; padding: 0.75rem; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); border-radius: 8px; color: #fff;">
                        <option value="">Seleccionar estado</option>
                    </select>
                </div>

                <div class="form-group" style="margin-bottom: 1.5rem;">
                    <label style="display: block; margin-bottom: 0.5rem; color: rgba(255,255,255,0.9); font-weight: 600;">Comentario</label>
                    <textarea id="comentarioEstado" name="comentario" rows="4" placeholder="Agregar un comentario sobre este cambio..."
                        style="width: 100%; padding: 0.75rem; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); border-radius: 8px; color: #fff; resize: vertical;"></textarea>
                </div>

                <div style="display: flex; gap: 1rem; justify-content: flex-end;">
                    <button type="button" onclick="cerrarModalCambiarEstado()" class="btn btn-outline">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar Cambio</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    window.APP_URL = '<?php echo APP_URL; ?>';
</script>
<script src="<?php echo APP_URL; ?>/js/dashboard/pedidos.js"></script>

<?php require_once ROOT_PATH . '/views/layouts/footer.php'; ?>