<?php
require_once ROOT_PATH . '/views/layouts/header.php';
?>
<link rel="stylesheet" href="<?php echo APP_URL; ?>/css/dashboard/admin.css">
<link rel="stylesheet" href="<?php echo APP_URL; ?>/css/dashboard/productos.css">
<link rel="stylesheet" href="<?php echo APP_URL; ?>/css/dashboard/pedidos.css">
<link rel="stylesheet" href="<?php echo APP_URL; ?>/css/dashboard/inventario.css">

<div class="container-fluid">
    <div class="admin-header">
        <h1><i class="fas fa-warehouse"></i> Gestión de Inventario</h1>
        <div class="header-actions">
            <button class="btn btn-primary" onclick="abrirModalMovimiento()">
                <i class="fas fa-plus"></i> Registrar Movimiento
            </button>
            <button class="btn btn-outline" style="background: linear-gradient(135deg, #9b59b6, #8e44ad);" onclick="abrirModalTransferencia()">
                <i class="fas fa-exchange-alt"></i> Transferir Stock
            </button>
            <a href="<?php echo APP_URL; ?>/admin/dashboard" class="btn btn-outline">
                <i class="fas fa-arrow-left"></i> Volver
            </a>
        </div>
    </div>

    <!-- Estadísticas -->
    <div class="stats-grid" style="margin-bottom: 2rem;">
        <div class="stat-card">
            <div class="stat-icon" style="background: linear-gradient(135deg, #3498db, #2980b9);">
                <i class="fas fa-boxes"></i>
            </div>
            <div class="stat-content">
                <span class="stat-value"><?php echo count($inventario ?? []); ?></span>
                <span class="stat-label">Registros de Inventario</span>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background: linear-gradient(135deg, #f39c12, #e67e22);">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <div class="stat-content">
                <span class="stat-value">
                    <?php echo count(array_filter($inventario ?? [], fn($i) => $i['cantidad_disponible'] < $i['stock_minimo'] && $i['cantidad_disponible'] > 0)); ?>
                </span>
                <span class="stat-label">Stock Bajo</span>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background: linear-gradient(135deg, #e74c3c, #c0392b);">
                <i class="fas fa-times-circle"></i>
            </div>
            <div class="stat-content">
                <span class="stat-value">
                    <?php echo count(array_filter($inventario ?? [], fn($i) => $i['cantidad_disponible'] == 0)); ?>
                </span>
                <span class="stat-label">Stock Crítico</span>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background: linear-gradient(135deg, #2ecc71, #27ae60);">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="stat-content">
                <span class="stat-value">
                    <?php echo count(array_filter($inventario ?? [], fn($i) => $i['cantidad_disponible'] >= $i['stock_minimo'])); ?>
                </span>
                <span class="stat-label">Stock Normal</span>
            </div>
        </div>
    </div>

    <div class="data-table">
        <div class="table-header">
            <h3><i class="fas fa-list"></i> Inventario por Sucursal</h3>
            <div style="display: flex; gap: 1rem;">
                <input type="text" id="searchInput" class="search-input" placeholder="Buscar producto..."
                    style="padding: 0.5rem 1rem; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); border-radius: 8px; color: #fff; min-width: 300px;">
                <select id="filterSucursal" class="search-input"
                    style="padding: 0.5rem 1rem; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); border-radius: 8px; color: #fff;">
                    <option value="">Todas las sucursales</option>
                    <?php
                    $sucursales = array_unique(array_map(fn($i) => $i['id_sucursal'] . '|' . $i['nombre_sucursal'], $inventario ?? []));
                    foreach ($sucursales as $suc):
                        [$id, $nombre] = explode('|', $suc);
                    ?>
                        <option value="<?php echo $id; ?>"><?php echo htmlspecialchars($nombre); ?></option>
                    <?php endforeach; ?>
                </select>
                <select id="filterNivel" class="search-input"
                    style="padding: 0.5rem 1rem; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); border-radius: 8px; color: #fff;">
                    <option value="">Todos los niveles</option>
                    <option value="normal">Normal</option>
                    <option value="bajo">Bajo</option>
                    <option value="critico">Crítico</option>
                </select>
            </div>
        </div>

        <div class="table-responsive">
            <table class="admin-table" id="inventarioTable">
                <thead>
                    <tr>
                        <th>Sucursal</th>
                        <th>Producto</th>
                        <th>Stock Disponible</th>
                        <th>Stock Reservado</th>
                        <th>Stock Real</th>
                        <th>Stock Mínimo</th>
                        <th>Nivel</th>
                        <th>Última Reposición</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($inventario)): ?>
                        <?php foreach ($inventario as $inv):
                            $stockReal = $inv['cantidad_disponible'] - $inv['cantidad_reservada'];
                            $nivel = $inv['cantidad_disponible'] == 0 ? 'critico' : ($inv['cantidad_disponible'] < $inv['stock_minimo'] ? 'bajo' : 'normal');
                        ?>
                            <tr data-sucursal="<?php echo $inv['id_sucursal']; ?>"
                                data-producto="<?php echo $inv['id_producto']; ?>"
                                data-nivel="<?php echo $nivel; ?>">
                                <td><strong><?php echo htmlspecialchars($inv['nombre_sucursal']); ?></strong></td>
                                <td>
                                    <div style="font-weight: 600;"><?php echo htmlspecialchars($inv['nombre_producto']); ?></div>
                                    <div style="font-size: 0.8rem; color: rgba(255,255,255,0.6);"><?php echo htmlspecialchars($inv['codigo_producto']); ?></div>
                                </td>
                                <td><span class="stock-value <?php echo $nivel; ?>"><?php echo $inv['cantidad_disponible']; ?></span></td>
                                <td><?php echo $inv['cantidad_reservada']; ?></td>
                                <td><strong style="color: #3498db;"><?php echo $stockReal; ?></strong></td>
                                <td><?php echo $inv['stock_minimo']; ?></td>
                                <td>
                                    <span class="stock-nivel <?php echo $nivel; ?>">
                                        <i class="fas fa-<?php echo $nivel == 'critico' ? 'times-circle' : ($nivel == 'bajo' ? 'exclamation-triangle' : 'check-circle'); ?>"></i>
                                        <?php echo ucfirst($nivel); ?>
                                    </span>
                                </td>
                                <td><?php echo $inv['ultima_reposicion'] ? date('d/m/Y', strtotime($inv['ultima_reposicion'])) : 'N/A'; ?></td>
                                <td>
                                    <button class="btn-icon" title="Ver historial" onclick="verHistorial(<?php echo $inv['id_inventario']; ?>)">
                                        <i class="fas fa-history"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="9" class="text-center py-4">
                                <i class="fas fa-warehouse" style="font-size: 3rem; color: rgba(255,255,255,0.3); margin-bottom: 1rem;"></i>
                                <p style="margin: 0; color: rgba(255,255,255,0.6);">No hay registros de inventario</p>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Movimiento -->
<div id="modalMovimiento" class="modal" style="display: none;">
    <div class="modal-content" style="max-width: 600px;">
        <div class="modal-header" style="display: flex; justify-content: space-between; padding: 1.5rem; border-bottom: 1px solid rgba(255,255,255,0.1);">
            <h3 style="margin: 0; color: #2ecc71;"><i class="fas fa-plus"></i> Registrar Movimiento</h3>
            <button onclick="cerrarModalMovimiento()" style="background: none; border: none; color: white; font-size: 1.5rem; cursor: pointer;">&times;</button>
        </div>
        <div class="modal-body" style="padding: 2rem;">
            <form id="formMovimiento">
                <div style="margin-bottom: 1.5rem;">
                    <label style="display: block; margin-bottom: 0.5rem; color: rgba(255,255,255,0.9); font-weight: 600;">Sucursal *</label>
                    <select id="sucursalMovimiento" required style="width: 100%; padding: 0.75rem; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); border-radius: 8px; color: #fff;">
                        <option value="">Seleccionar sucursal</option>
                    </select>
                </div>
                <div style="margin-bottom: 1.5rem;">
                    <label style="display: block; margin-bottom: 0.5rem; color: rgba(255,255,255,0.9); font-weight: 600;">Producto *</label>
                    <select id="productoMovimiento" required style="width: 100%; padding: 0.75rem; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); border-radius: 8px; color: #fff;">
                        <option value="">Seleccionar producto</option>
                    </select>
                </div>
                <div style="margin-bottom: 1.5rem;">
                    <label style="display: block; margin-bottom: 0.5rem; color: rgba(255,255,255,0.9); font-weight: 600;">Tipo de Movimiento *</label>
                    <select id="tipoMovimiento" required style="width: 100%; padding: 0.75rem; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); border-radius: 8px; color: #fff;">
                        <option value="">Seleccionar tipo</option>
                        <option value="entrada">Entrada (Incrementa stock)</option>
                        <option value="salida">Salida (Decrementa stock)</option>
                        <option value="ajuste">Ajuste (Establece cantidad exacta)</option>
                        <option value="devolucion">Devolución (Incrementa stock)</option>
                    </select>
                </div>
                <div style="margin-bottom: 1.5rem;">
                    <label style="display: block; margin-bottom: 0.5rem; color: rgba(255,255,255,0.9); font-weight: 600;">Cantidad *</label>
                    <input type="number" id="cantidadMovimiento" min="1" required style="width: 100%; padding: 0.75rem; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); border-radius: 8px; color: #fff;">
                </div>
                <div style="margin-bottom: 1.5rem;">
                    <label style="display: block; margin-bottom: 0.5rem; color: rgba(255,255,255,0.9); font-weight: 600;">Motivo *</label>
                    <textarea id="motivoMovimiento" rows="3" required style="width: 100%; padding: 0.75rem; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); border-radius: 8px; color: #fff; resize: vertical;"></textarea>
                </div>
                <div style="margin-bottom: 2rem;">
                    <label style="display: block; margin-bottom: 0.5rem; color: rgba(255,255,255,0.9); font-weight: 600;">Referencia (Opcional)</label>
                    <input type="text" id="referenciaMovimiento" placeholder="Ej: Factura #123" style="width: 100%; padding: 0.75rem; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); border-radius: 8px; color: #fff;">
                </div>
                <div style="display: flex; gap: 1rem; justify-content: flex-end;">
                    <button type="button" onclick="cerrarModalMovimiento()" class="btn btn-outline">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Registrar Movimiento</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Transferencia -->
<div id="modalTransferencia" class="modal" style="display: none;">
    <div class="modal-content" style="max-width: 700px;">
        <div class="modal-header" style="display: flex; justify-content: space-between; padding: 1.5rem; border-bottom: 1px solid rgba(255,255,255,0.1);">
            <h3 style="margin: 0; color: #9b59b6;"><i class="fas fa-exchange-alt"></i> Transferir Stock entre Sucursales</h3>
            <button onclick="cerrarModalTransferencia()" style="background: none; border: none; color: white; font-size: 1.5rem; cursor: pointer;">&times;</button>
        </div>
        <div class="modal-body" style="padding: 2rem;">
            <form id="formTransferencia">
                <div class="transfer-form-grid">
                    <div>
                        <label style="display: block; margin-bottom: 0.5rem; color: rgba(255,255,255,0.9); font-weight: 600;">Sucursal Origen *</label>
                        <select id="sucursalOrigen" required style="width: 100%; padding: 0.75rem; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); border-radius: 8px; color: #fff;">
                            <option value="">Seleccionar</option>
                        </select>
                    </div>
                    <div>
                        <label style="display: block; margin-bottom: 0.5rem; color: rgba(255,255,255,0.9); font-weight: 600;">Sucursal Destino *</label>
                        <select id="sucursalDestino" required style="width: 100%; padding: 0.75rem; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); border-radius: 8px; color: #fff;">
                            <option value="">Seleccionar</option>
                        </select>
                    </div>
                </div>
                <div class="transfer-arrow"><i class="fas fa-arrow-right"></i></div>
                <div style="margin-bottom: 1.5rem;">
                    <label style="display: block; margin-bottom: 0.5rem; color: rgba(255,255,255,0.9); font-weight: 600;">Producto *</label>
                    <select id="productoTransferencia" required style="width: 100%; padding: 0.75rem; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); border-radius: 8px; color: #fff;">
                        <option value="">Seleccionar producto</option>
                    </select>
                </div>
                <div style="margin-bottom: 1.5rem;">
                    <label style="display: block; margin-bottom: 0.5rem; color: rgba(255,255,255,0.9); font-weight: 600;">Cantidad *</label>
                    <input type="number" id="cantidadTransferencia" min="1" required style="width: 100%; padding: 0.75rem; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); border-radius: 8px; color: #fff;">
                </div>
                <div style="margin-bottom: 2rem;">
                    <label style="display: block; margin-bottom: 0.5rem; color: rgba(255,255,255,0.9); font-weight: 600;">Motivo</label>
                    <textarea id="motivoTransferencia" rows="2" placeholder="Razón de la transferencia..." style="width: 100%; padding: 0.75rem; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); border-radius: 8px; color: #fff; resize: vertical;"></textarea>
                </div>
                <div style="display: flex; gap: 1rem; justify-content: flex-end;">
                    <button type="button" onclick="cerrarModalTransferencia()" class="btn btn-outline">Cancelar</button>
                    <button type="submit" class="btn btn-primary" style="background: linear-gradient(135deg, #9b59b6, #8e44ad);">Transferir</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Historial -->
<div id="modalHistorial" class="modal" style="display: none;">
    <div class="modal-content" style="max-width: 800px;">
        <div class="modal-header" style="display: flex; justify-content: space-between; padding: 1.5rem; border-bottom: 1px solid rgba(255,255,255,0.1);">
            <h3 style="margin: 0; color: #3498db;"><i class="fas fa-history"></i> Historial de Movimientos</h3>
            <button onclick="cerrarModalHistorial()" style="background: none; border: none; color: white; font-size: 1.5rem; cursor: pointer;">&times;</button>
        </div>
        <div class="modal-body" style="padding: 2rem;">
            <div class="timeline movimientos-timeline" id="timelineMovimientos">
                <p style="text-align: center; color: rgba(255,255,255,0.6);">Cargando...</p>
            </div>
        </div>
    </div>
</div>

<script>
    window.APP_URL = '<?php echo APP_URL; ?>';
</script>
<script src="<?php echo APP_URL; ?>/js/dashboard/inventario.js"></script>

<?php require_once ROOT_PATH . '/views/layouts/footer.php'; ?>