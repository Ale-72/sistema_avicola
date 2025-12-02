<?php
require_once ROOT_PATH . '/views/layouts/header.php';
?>
<link rel="stylesheet" href="<?php echo APP_URL; ?>/css/dashboard/admin.css">
<link rel="stylesheet" href="<?php echo APP_URL; ?>/css/dashboard/productos.css">
<link rel="stylesheet" href="<?php echo APP_URL; ?>/css/dashboard/sucursales.css">

<div class="container-fluid">
    <div class="admin-header">
        <h1><i class="fas fa-store"></i> Gestión de Sucursales</h1>
        <div class="header-actions">
            <button class="btn btn-primary" onclick="abrirModalNuevaSucursal()">
                <i class="fas fa-plus"></i> Nueva Sucursal
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
                <i class="fas fa-store"></i>
            </div>
            <div class="stat-content">
                <span class="stat-value"><?php echo count($sucursales ?? []); ?></span>
                <span class="stat-label">Total Sucursales</span>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background: linear-gradient(135deg, #2ecc71, #27ae60);">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="stat-content">
                <span class="stat-value"><?php echo count(array_filter($sucursales ?? [], fn($s) => $s['activo'] == 1)); ?></span>
                <span class="stat-label">Sucursales Activas</span>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background: linear-gradient(135deg, #9b59b6, #8e44ad);">
                <i class="fas fa-map-marker-alt"></i>
            </div>
            <div class="stat-content">
                <span class="stat-value">
                    <?php
                    $ciudades = array_unique(array_map(fn($s) => $s['ciudad'], $sucursales ?? []));
                    echo count($ciudades);
                    ?>
                </span>
                <span class="stat-label">Ciudades Cubiertas</span>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background: linear-gradient(135deg, #f39c12, #e67e22);">
                <i class="fas fa-warehouse"></i>
            </div>
            <div class="stat-content">
                <span class="stat-value">
                    <?php
                    $capacidadTotal = array_sum(array_map(fn($s) => $s['capacidad_almacenamiento'] ?? 0, $sucursales ?? []));
                    echo number_format($capacidadTotal, 0);
                    ?> m³
                </span>
                <span class="stat-label">Capacidad Total</span>
            </div>
        </div>
    </div>

    <div class="data-table">
        <div class="table-header">
            <h3><i class="fas fa-list"></i> Directorio de Sucursales</h3>
            <div style="display: flex; gap: 1rem;">
                <input type="text" id="searchInput" class="search-input" placeholder="Buscar sucursal..."
                    style="padding: 0.5rem 1rem; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); border-radius: 8px; color: #fff; min-width: 300px;">
                <select id="filterCiudad" class="search-input"
                    style="padding: 0.5rem 1rem; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); border-radius: 8px; color: #fff;">
                    <option value="">Todas las ciudades</option>
                    <?php
                    $ciudades = array_unique(array_map(fn($s) => $s['ciudad'], $sucursales ?? []));
                    sort($ciudades);
                    foreach ($ciudades as $ciudad):
                    ?>
                        <option value="<?php echo htmlspecialchars($ciudad); ?>"><?php echo htmlspecialchars($ciudad); ?></option>
                    <?php endforeach; ?>
                </select>
                <select id="filterEstado" class="search-input"
                    style="padding: 0.5rem 1rem; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); border-radius: 8px; color: #fff;">
                    <option value="">Todos los estados</option>
                    <option value="1">Activas</option>
                    <option value="0">Inactivas</option>
                </select>
            </div>
        </div>

        <div class="table-responsive">
            <table class="admin-table" id="sucursalesTable">
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Sucursal</th>
                        <th>Ciudad</th>
                        <th>Encargado</th>
                        <th>Servicios</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($sucursales)): ?>
                        <?php foreach ($sucursales as $suc): ?>
                            <tr data-ciudad="<?php echo htmlspecialchars($suc['ciudad']); ?>" data-estado="<?php echo $suc['activo']; ?>">
                                <td><strong><?php echo htmlspecialchars($suc['codigo_sucursal']); ?></strong></td>
                                <td>
                                    <div style="font-weight: 600; color: #fff;"><?php echo htmlspecialchars($suc['nombre_sucursal']); ?></div>
                                    <div style="font-size: 0.8rem; color: rgba(255,255,255,0.6);">
                                        <i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($suc['direccion_completa']); ?>
                                    </div>
                                </td>
                                <td><?php echo htmlspecialchars($suc['ciudad']); ?></td>
                                <td><?php echo htmlspecialchars($suc['encargado_nombre'] ?? 'Sin asignar'); ?></td>
                                <td>
                                    <?php if ($suc['permite_delivery'] == 1 && $suc['permite_pickup'] == 1): ?>
                                        <span class="badge-service both"><i class="fas fa-check-double"></i> Ambos</span>
                                    <?php elseif ($suc['permite_delivery'] == 1): ?>
                                        <span class="badge-service delivery"><i class="fas fa-truck"></i> Delivery</span>
                                    <?php elseif ($suc['permite_pickup'] == 1): ?>
                                        <span class="badge-service pickup"><i class="fas fa-store"></i> Pickup</span>
                                    <?php else: ?>
                                        <span style="color: rgba(255,255,255,0.5);">Ninguno</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge badge-<?php echo $suc['activo'] == 1 ? 'success' : 'secondary'; ?>">
                                        <i class="fas fa-circle" style="font-size: 0.5rem;"></i>
                                        <?php echo $suc['activo'] == 1 ? 'Activo' : 'Inactivo'; ?>
                                    </span>
                                </td>
                                <td>
                                    <div style="display: flex; gap: 0.5rem;">
                                        <button class="btn-icon" title="Ver detalles" onclick="verSucursal(<?php echo $suc['id_sucursal']; ?>)">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button class="btn-icon" title="Editar" onclick="editarSucursal(<?php echo $suc['id_sucursal']; ?>)">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn-icon" title="<?php echo $suc['activo'] == 1 ? 'Desactivar' : 'Activar'; ?>"
                                            onclick="toggleEstadoSucursal(<?php echo $suc['id_sucursal']; ?>, <?php echo $suc['activo']; ?>)">
                                            <i class="fas fa-<?php echo $suc['activo'] == 1 ? 'ban' : 'check'; ?>"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center py-4">
                                <i class="fas fa-store" style="font-size: 3rem; color: rgba(255,255,255,0.3); margin-bottom: 1rem;"></i>
                                <p style="margin: 0; color: rgba(255,255,255,0.6);">No hay sucursales registradas</p>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Detalles -->
<div id="modalDetalles" class="modal" style="display: none;">
    <div class="modal-content" style="max-width: 800px;">
        <div class="modal-header" style="display: flex; justify-content: space-between; align-items: center; padding: 1.5rem; border-bottom: 1px solid rgba(255,255,255,0.1);">
            <div>
                <h3 style="margin: 0; color: #3498db;"><i class="fas fa-store"></i> Detalles de la Sucursal</h3>
                <p style="margin: 0.5rem 0 0 0; color: rgba(255,255,255,0.6); font-size: 0.9rem;">
                    <strong id="detalleCodigo"></strong> | <span id="detalleNombre"></span>
                </p>
            </div>
            <div style="display: flex; align-items: center; gap: 1rem;">
                <div id="detalleEstado"></div>
                <button onclick="cerrarModalDetalles()" style="background: none; border: none; color: white; font-size: 1.5rem; cursor: pointer;">&times;</button>
            </div>
        </div>
        <div class="modal-body" style="padding: 2rem;">
            <div class="sucursal-detail-grid">
                <div class="detail-card">
                    <div class="detail-card-title"><i class="fas fa-map-marker-alt"></i> Ubicación</div>
                    <div class="detail-item">
                        <div class="detail-label">Ciudad</div>
                        <div class="detail-value" id="detalleCiudad"></div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Dirección</div>
                        <div class="detail-value" id="detalleDireccion"></div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Coordenadas</div>
                        <div class="detail-value" id="detalleCoordenadas"></div>
                    </div>
                </div>

                <div class="detail-card">
                    <div class="detail-card-title"><i class="fas fa-user-tie"></i> Contacto</div>
                    <div class="detail-item">
                        <div class="detail-label">Encargado</div>
                        <div class="detail-value" id="detalleEncargado"></div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Teléfono</div>
                        <div class="detail-value" id="detalleTelefono"></div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Email</div>
                        <div class="detail-value" id="detalleEmail"></div>
                    </div>
                </div>

                <div class="detail-card">
                    <div class="detail-card-title"><i class="fas fa-clock"></i> Horarios</div>
                    <div class="detail-item">
                        <div class="detail-label">Horario</div>
                        <div class="detail-value" id="detalleHorario"></div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Días de Atención</div>
                        <div class="detail-value" id="detalleDias"></div>
                    </div>
                </div>

                <div class="detail-card">
                    <div class="detail-card-title"><i class="fas fa-truck"></i> Servicios</div>
                    <div class="detail-item">
                        <div class="detail-label">Servicios Disponibles</div>
                        <div class="detail-value" id="detalleServicios"></div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Capacidad</div>
                        <div class="detail-value" id="detalleCapacidad"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Formulario -->
<div id="modalSucursal" class="modal" style="display: none;">
    <div class="modal-content" style="max-width: 900px;">
        <div class="modal-header" style="display: flex; justify-content: space-between; align-items: center; padding: 1.5rem; border-bottom: 1px solid rgba(255,255,255,0.1);">
            <h3 id="modalTitle" style="margin: 0; color: #2ecc71;"><i class="fas fa-plus"></i> Nueva Sucursal</h3>
            <button onclick="cerrarModal()" style="background: none; border: none; color: white; font-size: 1.5rem; cursor: pointer;">&times;</button>
        </div>
        <div class="modal-body" style="padding: 2rem; max-height: 70vh; overflow-y: auto;">
            <form id="formSucursal">
                <input type="hidden" id="sucursalId" name="id_sucursal">

                <div class="form-columns">
                    <div>
                        <label style="display: block; margin-bottom: 0.5rem; color: rgba(255,255,255,0.9); font-weight: 600;">Nombre de Sucursal *</label>
                        <input type="text" id="nombreSucursal" required style="width: 100%; padding: 0.75rem; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); border-radius: 8px; color: #fff;">
                    </div>
                    <div>
                        <label style="display: block; margin-bottom: 0.5rem; color: rgba(255,255,255,0.9); font-weight: 600;">Código *</label>
                        <input type="text" id="codigoSucursal" required style="width: 100%; padding: 0.75rem; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); border-radius: 8px; color: #fff;">
                    </div>
                </div>

                <div class="form-columns">
                    <div>
                        <label style="display: block; margin-bottom: 0.5rem; color: rgba(255,255,255,0.9); font-weight: 600;">Ciudad *</label>
                        <input type="text" id="ciudad" required style="width: 100%; padding: 0.75rem; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); border-radius: 8px; color: #fff;">
                    </div>
                    <div>
                        <label style="display: block; margin-bottom: 0.5rem; color: rgba(255,255,255,0.9); font-weight: 600;">Departamento</label>
                        <input type="text" id="departamento" style="width: 100%; padding: 0.75rem; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); border-radius: 8px; color: #fff;">
                    </div>
                </div>

                <div class="form-column-full" style="margin-bottom: 1.5rem;">
                    <label style="display: block; margin-bottom: 0.5rem; color: rgba(255,255,255,0.9); font-weight: 600;">Dirección Completa *</label>
                    <textarea id="direccion" required rows="2" style="width: 100%; padding: 0.75rem; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); border-radius: 8px; color: #fff; resize: vertical;"></textarea>
                </div>

                <div class="geo-section">
                    <div class="geo-title"><i class="fas fa-map-marker-alt"></i> Geolocalización</div>
                    <div class="geo-grid">
                        <div class="geo-input-group">
                            <label class="geo-label">Latitud *</label>
                            <input type="number" id="latitud" step="any" required class="geo-input" placeholder="-12.0464">
                            <span class="geo-hint">Rango: -90 a 90</span>
                        </div>
                        <div class="geo-input-group">
                            <label class="geo-label">Longitud *</label>
                            <input type="number" id="longitud" step="any" required class="geo-input" placeholder="-77.0428">
                            <span class="geo-hint">Rango: -180 a 180</span>
                        </div>
                    </div>
                </div>

                <div class="form-columns">
                    <div>
                        <label style="display: block; margin-bottom: 0.5rem; color: rgba(255,255,255,0.9); font-weight: 600;">Encargado</label>
                        <select id="encargado" style="width: 100%; padding: 0.75rem; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); border-radius: 8px; color: #fff;">
                            <option value="">Sin asignar</option>
                        </select>
                    </div>
                    <div>
                        <label style="display: block; margin-bottom: 0.5rem; color: rgba(255,255,255,0.9); font-weight: 600;">Teléfono</label>
                        <input type="tel" id="telefono" style="width: 100%; padding: 0.75rem; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); border-radius: 8px; color: #fff;">
                    </div>
                </div>

                <div class="horarios-section">
                    <div class="geo-title"><i class="fas fa-clock"></i> Horarios</div>
                    <div class="horarios-grid">
                        <div>
                            <label style="display: block; margin-bottom: 0.5rem; color: rgba(255,255,255,0.9); font-weight: 600;">Apertura</label>
                            <input type="time" id="horarioApertura" style="width: 100%; padding: 0.75rem; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); border-radius: 8px; color: #fff;">
                        </div>
                        <div>
                            <label style="display: block; margin-bottom: 0.5rem; color: rgba(255,255,255,0.9); font-weight: 600;">Cierre</label>
                            <input type="time" id="horarioCierre" style="width: 100%; padding: 0.75rem; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); border-radius: 8px; color: #fff;">
                        </div>
                        <div>
                            <label style="display: block; margin-bottom: 0.5rem; color: rgba(255,255,255,0.9); font-weight: 600;">Días de Atención</label>
                            <input type="text" id="diasAtencion" value="Lunes a Sábado" style="width: 100%; padding: 0.75rem; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); border-radius: 8px; color: #fff;">
                        </div>
                    </div>
                </div>

                <div class="service-checkboxes">
                    <label class="checkbox-label">
                        <input type="checkbox" id="permiteDelivery" value="1">
                        <span><i class="fas fa-truck"></i> Permite Delivery</span>
                    </label>
                    <label class="checkbox-label">
                        <input type="checkbox" id="permitePickup" value="1">
                        <span><i class="fas fa-store"></i> Permite Pickup</span>
                    </label>
                </div>

                <div id="radioCoberturaGroup" class="radio-cobertura-group form-columns">
                    <div>
                        <label style="display: block; margin-bottom: 0.5rem; color: rgba(255,255,255,0.9); font-weight: 600;">Radio de Cobertura (km)</label>
                        <input type="number" id="radioCobertura" step="0.1" min="0" style="width: 100%; padding: 0.75rem; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); border-radius: 8px; color: #fff;">
                    </div>
                    <div>
                        <label style="display: block; margin-bottom: 0.5rem; color: rgba(255,255,255,0.9); font-weight: 600;">Capacidad (m³)</label>
                        <input type="number" id="capacidadAlmacenamiento" step="0.1" min="0" style="width: 100%; padding: 0.75rem; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); border-radius: 8px; color: #fff;">
                    </div>
                </div>

                <div class="form-columns">
                    <div>
                        <label style="display: block; margin-bottom: 0.5rem; color: rgba(255,255,255,0.9); font-weight: 600;">Email</label>
                        <input type="email" id="email" style="width: 100%; padding: 0.75rem; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); border-radius: 8px; color: #fff;">
                    </div>
                    <div>
                        <label style="display: block; margin-bottom: 0.5rem; color: rgba(255,255,255,0.9); font-weight: 600;">Fecha Apertura</label>
                        <input type="date" id="fechaApertura" style="width: 100%; padding: 0.75rem; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); border-radius: 8px; color: #fff;">
                    </div>
                </div>

                <div style="margin-bottom: 2rem;">
                    <label style="display: block; margin-bottom: 0.5rem; color: rgba(255,255,255,0.9); font-weight: 600;">Estado</label>
                    <select id="activo" style="width: 20%; padding: 0.75rem; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); border-radius: 8px; color: #fff;">
                        <option value="1">Activo</option>
                        <option value="0">Inactivo</option>
                    </select>
                </div>

                <div style="display: flex; gap: 1rem; justify-content: flex-end;">
                    <button type="button" onclick="cerrarModal()" class="btn btn-outline">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar Sucursal</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    window.APP_URL = '<?php echo APP_URL; ?>';
</script>
<script src="<?php echo APP_URL; ?>/js/dashboard/sucursales.js"></script>

<?php require_once ROOT_PATH . '/views/layouts/footer.php'; ?>