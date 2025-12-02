<?php
require_once ROOT_PATH . '/views/layouts/header.php';
?>
<link rel="stylesheet" href="<?php echo APP_URL; ?>/css/dashboard/admin.css">
<link rel="stylesheet" href="<?php echo APP_URL; ?>/css/dashboard/productos.css">

<div class="container-fluid">
    <div class="admin-header">
        <h1><i class="fas fa-box"></i> Gestión de Productos</h1>
        <div class="header-actions">
            <button class="btn btn-primary" onclick="abrirModalNuevoProducto()">
                <i class="fas fa-plus"></i> Nuevo Producto
            </button>
            <a href="<?php echo APP_URL; ?>/admin/dashboard" class="btn btn-outline">
                <i class="fas fa-arrow-left"></i> Volver
            </a>
        </div>
    </div>

    <!-- Estadísticas rápidas -->
    <div class="stats-grid" style="margin-bottom: 2rem;">
        <div class="stat-card">
            <div class="stat-icon" style="background: linear-gradient(135deg, #3498db, #2980b9);">
                <i class="fas fa-box"></i>
            </div>
            <div class="stat-content">
                <span class="stat-value"><?php echo count($productos ?? []); ?></span>
                <span class="stat-label">Total Productos</span>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background: linear-gradient(135deg, #2ecc71, #27ae60);">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="stat-content">
                <span class="stat-value"><?php echo count(array_filter($productos ?? [], fn($p) => $p['activo'] == 1)); ?></span>
                <span class="stat-label">Productos Activos</span>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background: linear-gradient(135deg, #e74c3c, #c0392b);">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <div class="stat-content">
                <span class="stat-value"><?php echo count(array_filter($productos ?? [], fn($p) => $p['stock_total'] <= $p['stock_minimo'])); ?></span>
                <span class="stat-label">Stock Bajo</span>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background: linear-gradient(135deg, #f1c40f, #f39c12);">
                <i class="fas fa-star"></i>
            </div>
            <div class="stat-content">
                <span class="stat-value"><?php echo count(array_filter($productos ?? [], fn($p) => $p['destacado'] == 1)); ?></span>
                <span class="stat-label">Destacados</span>
            </div>
        </div>
    </div>

    <div class="data-table">
        <div class="table-header">
            <h3><i class="fas fa-list"></i> Catálogo de Productos</h3>
            <div style="display: flex; gap: 1rem;">
                <input type="text" id="searchInput" class="search-input" placeholder="Buscar producto..."
                    style="padding: 0.5rem 1rem; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); border-radius: 8px; color: #fff; min-width: 300px;">
                <select id="filterCategoria" class="search-input" style="padding: 0.5rem 1rem; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); border-radius: 8px; color: #fff;">
                    <option value="">Todas las categorías</option>
                    <?php
                    $categorias = [];
                    foreach ($productos ?? [] as $producto) {
                        if (!empty($producto['nombre_categoria']) && !in_array($producto['nombre_categoria'], $categorias)) {
                            $categorias[] = $producto['nombre_categoria'];
                        }
                    }
                    sort($categorias);
                    foreach ($categorias as $cat):
                    ?>
                        <option value="<?php echo htmlspecialchars($cat); ?>"><?php echo htmlspecialchars($cat); ?></option>
                    <?php endforeach; ?>
                </select>
                <select id="filterEstado" class="search-input" style="padding: 0.5rem 1rem; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); border-radius: 8px; color: #fff;">
                    <option value="">Todos los estados</option>
                    <option value="1">Activos</option>
                    <option value="0">Inactivos</option>
                </select>
            </div>
        </div>

        <div class="table-responsive">
            <table class="admin-table" id="productosTable">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Producto</th>
                        <th>Código</th>
                        <th>Categoría</th>
                        <th>Precio</th>
                        <th>Stock</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($productos)): ?>
                        <?php foreach ($productos as $producto): ?>
                            <tr data-id="<?php echo $producto['id_producto']; ?>"
                                data-categoria="<?php echo htmlspecialchars($producto['nombre_categoria'] ?? ''); ?>"
                                data-estado="<?php echo $producto['activo']; ?>">
                                <td><strong>#<?php echo $producto['id_producto']; ?></strong></td>
                                <td>
                                    <div style="display: flex; align-items: center; gap: 0.75rem;">
                                        <div style="width: 40px; height: 40px; border-radius: 8px; background: linear-gradient(135deg, #3498db, #2ecc71); display: flex; align-items: center; justify-content: center; color: white; font-weight: 700; font-size: 1rem;">
                                            <i class="fas fa-box"></i>
                                        </div>
                                        <div>
                                            <div style="font-weight: 600;"><?php echo htmlspecialchars($producto['nombre_producto']); ?></div>
                                            <?php if ($producto['destacado'] == 1): ?>
                                                <span class="badge badge-warning" style="font-size: 0.7rem; padding: 0.2rem 0.5rem;"><i class="fas fa-star" style="font-size: 0.6rem;"></i> Destacado</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </td>
                                <td><?php echo htmlspecialchars($producto['codigo_producto'] ?? 'N/A'); ?></td>
                                <td>
                                    <span class="badge badge-primary" style="padding: 0.35rem 0.75rem; border-radius: 12px; font-size: 0.8rem; font-weight: 600;">
                                        <?php echo htmlspecialchars($producto['nombre_categoria'] ?? 'Sin categoría'); ?>
                                    </span>
                                </td>
                                <td>
                                    <div style="font-weight: 600; color: #2ecc71;">S/ <?php echo number_format($producto['precio_unitario'], 2); ?></div>
                                    <?php if ($producto['precio_oferta'] && $producto['precio_oferta'] > 0): ?>
                                        <div style="font-size: 0.75rem; color: #e74c3c;">Oferta: Bs/ <?php echo number_format($producto['precio_oferta'], 2); ?></div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php
                                    $stockClass = $producto['stock_total'] <= $producto['stock_minimo'] ? 'stock-bajo' : 'stock-normal';
                                    ?>
                                    <div class="<?php echo $stockClass; ?>">
                                        <?php echo $producto['stock_total']; ?> <?php echo $producto['unidad_medida']; ?>
                                    </div>
                                    <?php if ($producto['stock_total'] <= $producto['stock_minimo']): ?>
                                        <div style="font-size: 0.75rem; color: rgba(255,255,255,0.6);">
                                            <i class="fas fa-exclamation-triangle"></i> Bajo stock
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge badge-<?php echo $producto['activo'] == 1 ? 'success' : 'secondary'; ?>"
                                        style="padding: 0.35rem 0.75rem; border-radius: 12px; font-size: 0.8rem; font-weight: 600;">
                                        <i class="fas fa-circle" style="font-size: 0.5rem; margin-right: 0.25rem;"></i>
                                        <?php echo $producto['activo'] == 1 ? 'Activo' : 'Inactivo'; ?>
                                    </span>
                                </td>
                                <td>
                                    <div style="display: flex; gap: 0.5rem;">
                                        <button class="btn-icon" title="Ver detalles" onclick="verProducto(<?php echo $producto['id_producto']; ?>)">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button class="btn-icon" title="Editar" onclick="editarProducto(<?php echo $producto['id_producto']; ?>)">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn-icon" title="<?php echo $producto['activo'] == 1 ? 'Desactivar' : 'Activar'; ?>"
                                            onclick="toggleEstadoProducto(<?php echo $producto['id_producto']; ?>, <?php echo $producto['activo']; ?>)">
                                            <i class="fas fa-<?php echo $producto['activo'] == 1 ? 'ban' : 'check'; ?>"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="text-center py-4">
                                <i class="fas fa-box-open" style="font-size: 3rem; color: rgba(255,255,255,0.3); margin-bottom: 1rem;"></i>
                                <p style="margin: 0; color: rgba(255,255,255,0.6);">No hay productos registrados</p>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal para ver detalles -->
<div id="modalDetalles" class="modal" style="display: none;">
    <div class="modal-content" style="max-width: 600px;">
        <div class="modal-header" style="display: flex; justify-content: space-between; align-items: center; padding: 1.5rem; border-bottom: 1px solid rgba(255,255,255,0.1);">
            <h3 style="margin: 0; color: #2ecc71;"><i class="fas fa-box"></i> Detalles del Producto</h3>
            <button onclick="cerrarModalDetalles()" style="background: none; border: none; color: white; font-size: 1.5rem; cursor: pointer;">&times;</button>
        </div>
        <div class="modal-body" style="padding: 2rem;">
            <div class="product-details">
                <div style="margin-bottom: 1.5rem;">
                    <h2 id="detalleNombre" style="margin: 0 0 0.5rem 0; font-size: 1.5rem; color: #fff;"></h2>
                    <p id="detalleCodigo" style="margin: 0; color: rgba(255,255,255,0.6); font-size: 0.9rem;"></p>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 1.5rem;">
                    <div>
                        <label style="display: block; color: rgba(255,255,255,0.6); font-size: 0.85rem; margin-bottom: 0.5rem; text-transform: uppercase;">Categoría</label>
                        <p id="detalleCategoria" style="margin: 0; color: #fff; font-weight: 600;"></p>
                    </div>
                    <div>
                        <label style="display: block; color: rgba(255,255,255,0.6); font-size: 0.85rem; margin-bottom: 0.5rem; text-transform: uppercase;">Precio</label>
                        <p id="detallePrecio" style="margin: 0; color: #2ecc71; font-weight: 700; font-size: 1.2rem;"></p>
                    </div>
                </div>

                <div style="margin-bottom: 1.5rem;">
                    <label style="display: block; color: rgba(255,255,255,0.6); font-size: 0.85rem; margin-bottom: 0.5rem; text-transform: uppercase;">Precio Oferta</label>
                    <p id="detallePrecioOferta" style="margin: 0; color: #e74c3c; font-weight: 700; font-size: 1.1rem;"></p>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1rem; margin-bottom: 1.5rem;">
                    <div>
                        <label style="display: block; color: rgba(255,255,255,0.6); font-size: 0.85rem; margin-bottom: 0.5rem; text-transform: uppercase;">Unidad</label>
                        <p id="detalleUnidad" style="margin: 0; color: #fff; font-weight: 600;"></p>
                    </div>
                    <div>
                        <label style="display: block; color: rgba(255,255,255,0.6); font-size: 0.85rem; margin-bottom: 0.5rem; text-transform: uppercase;">Stock</label>
                        <p id="detalleStock" style="margin: 0; color: #fff; font-weight: 600;"></p>
                    </div>
                    <div>
                        <label style="display: block; color: rgba(255,255,255,0.6); font-size: 0.85rem; margin-bottom: 0.5rem; text-transform: uppercase;">Stock Mínimo</label>
                        <p id="detalleStockMinimo" style="margin: 0; color: #fff; font-weight: 600;"></p>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1.5rem;">
                    <div>
                        <label style="display: block; color: rgba(255,255,255,0.6); font-size: 0.85rem; margin-bottom: 0.5rem; text-transform: uppercase;">Estado</label>
                        <div id="detalleEstado"></div>
                    </div>
                    <div>
                        <label style="display: block; color: rgba(255,255,255,0.6); font-size: 0.85rem; margin-bottom: 0.5rem; text-transform: uppercase;">Destacado</label>
                        <div id="detalleDestacado"></div>
                    </div>
                </div>

                <div>
                    <label style="display: block; color: rgba(255,255,255,0.6); font-size: 0.85rem; margin-bottom: 0.5rem; text-transform: uppercase;">Descripción</label>
                    <p id="detalleDescripcion" style="margin: 0; color: rgba(255,255,255,0.9); line-height: 1.6;"></p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal para nuevo/editar producto -->
<div id="modalProducto" class="modal" style="display: none;">
    <div class="modal-content" style="max-width: 700px;">
        <div class="modal-header" style="display: flex; justify-content: space-between; align-items: center; padding: 1.5rem; border-bottom: 1px solid rgba(255,255,255,0.1);">
            <h3 id="modalTitle" style="margin: 0; color: #2ecc71;"><i class="fas fa-plus"></i> Nuevo Producto</h3>
            <button onclick="cerrarModal()" style="background: none; border: none; color: white; font-size: 1.5rem; cursor: pointer;">&times;</button>
        </div>
        <div class="modal-body" style="padding: 2rem;">
            <form id="formProducto">
                <input type="hidden" id="productoId" name="id_producto">

                <div class="form-group" style="margin-bottom: 1.5rem;">
                    <label style="display: block; margin-bottom: 0.5rem; color: rgba(255,255,255,0.9); font-weight: 600;">Nombre del Producto *</label>
                    <input type="text" id="nombreProducto" name="nombre_producto" required
                        style="width: 100%; padding: 0.75rem; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); border-radius: 8px; color: #fff;">
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1.5rem;">
                    <div class="form-group">
                        <label style="display: block; margin-bottom: 0.5rem; color: rgba(255,255,255,0.9); font-weight: 600;">Código</label>
                        <input type="text" id="codigoProducto" name="codigo_producto"
                            style="width: 100%; padding: 0.75rem; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); border-radius: 8px; color: #fff;">
                    </div>

                    <div class="form-group">
                        <label style="display: block; margin-bottom: 0.5rem; color: rgba(255,255,255,0.9); font-weight: 600;">Categoría *</label>
                        <select id="categoria" name="id_categoria_producto" required
                            style="width: 100%; padding: 0.75rem; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); border-radius: 8px; color: #fff;">
                            <option value="">Seleccionar categoría</option>
                        </select>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1.5rem;">
                    <div class="form-group">
                        <label style="display: block; margin-bottom: 0.5rem; color: rgba(255,255,255,0.9); font-weight: 600;">Precio Unitario * (Bs/)</label>
                        <input type="number" id="precioUnitario" name="precio_unitario" step="0.01" min="0" required
                            style="width: 100%; padding: 0.75rem; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); border-radius: 8px; color: #fff;">
                    </div>

                    <div class="form-group">
                        <label style="display: block; margin-bottom: 0.5rem; color: rgba(255,255,255,0.9); font-weight: 600;">Precio Oferta (Bs/)</label>
                        <input type="number" id="precioOferta" name="precio_oferta" step="0.01" min="0"
                            style="width: 100%; padding: 0.75rem; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); border-radius: 8px; color: #fff;">
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1rem; margin-bottom: 1.5rem;">
                    <div class="form-group">
                        <label style="display: block; margin-bottom: 0.5rem; color: rgba(255,255,255,0.9); font-weight: 600;">Unidad de Medida</label>
                        <select id="unidadMedida" name="unidad_medida"
                            style="width: 100%; padding: 0.75rem; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); border-radius: 8px; color: #fff;">
                            <option value="unidad">Unidad</option>
                            <option value="kg">Kilogramo (kg)</option>
                            <option value="lb">Libra (lb)</option>
                            <option value="docena">Docena</option>
                            <option value="caja">Caja</option>
                            <option value="saco">Saco</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label style="display: block; margin-bottom: 0.5rem; color: rgba(255,255,255,0.9); font-weight: 600;">Stock Total</label>
                        <input type="number" id="stockTotal" name="stock_total" min="0" value="0"
                            style="width: 100%; padding: 0.75rem; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); border-radius: 8px; color: #fff;">
                    </div>

                    <div class="form-group">
                        <label style="display: block; margin-bottom: 0.5rem; color: rgba(255,255,255,0.9); font-weight: 600;">Stock Mínimo</label>
                        <input type="number" id="stockMinimo" name="stock_minimo" min="0" value="5"
                            style="width: 100%; padding: 0.75rem; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); border-radius: 8px; color: #fff;">
                    </div>
                </div>

                <div class="form-group" style="margin-bottom: 1.5rem;">
                    <label style="display: block; margin-bottom: 0.5rem; color: rgba(255,255,255,0.9); font-weight: 600;">Descripción Corta</label>
                    <textarea id="descripcionCorta" name="descripcion_corta" rows="3"
                        style="width: 100%; padding: 0.75rem; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); border-radius: 8px; color: #fff; resize: vertical;"></textarea>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 2rem;">
                    <div class="form-group">
                        <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                            <input type="checkbox" id="destacado" name="destacado" value="1"
                                style="width: 20px; height: 20px; cursor: pointer;">
                            <span style="color: rgba(255,255,255,0.9); font-weight: 600;">Producto Destacado</span>
                        </label>
                    </div>

                    <div class="form-group">
                        <label style="display: block; margin-bottom: 0.5rem; color: rgba(255,255,255,0.9); font-weight: 600;">Estado</label>
                        <select id="activo" name="activo"
                            style="width: 100%; padding: 0.75rem; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); border-radius: 8px; color: #fff;">
                            <option value="1">Activo</option>
                            <option value="0">Inactivo</option>
                        </select>
                    </div>
                </div>

                <div style="display: flex; gap: 1rem; justify-content: flex-end;">
                    <button type="button" onclick="cerrarModal()" class="btn btn-outline">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar Producto</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // Exponer APP_URL para el archivo JS
    window.APP_URL = '<?php echo APP_URL; ?>';
</script>
<script src="<?php echo APP_URL; ?>/js/dashboard/productos.js"></script>

<?php require_once ROOT_PATH . '/views/layouts/footer.php'; ?>