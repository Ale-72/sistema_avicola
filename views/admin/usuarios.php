<?php
require_once ROOT_PATH . '/views/layouts/header.php';
?>
<link rel="stylesheet" href="<?php echo APP_URL; ?>/css/dashboard/admin.css">

<div class="container-fluid">
    <div class="admin-header">
        <h1><i class="fas fa-users"></i> Gestión de Usuarios</h1>
        <div class="header-actions">
            <button class="btn btn-primary" onclick="abrirModalNuevoUsuario()">
                <i class="fas fa-plus"></i> Nuevo Usuario
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
                <i class="fas fa-users"></i>
            </div>
            <div class="stat-content">
                <span class="stat-value"><?php echo count($usuarios ?? []); ?></span>
                <span class="stat-label">Total Usuarios</span>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background: linear-gradient(135deg, #2ecc71, #27ae60);">
                <i class="fas fa-user-check"></i>
            </div>
            <div class="stat-content">
                <span class="stat-value"><?php echo count(array_filter($usuarios ?? [], fn($u) => $u['activo'] == 1)); ?></span>
                <span class="stat-label">Usuarios Activos</span>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background: linear-gradient(135deg, #e74c3c, #c0392b);">
                <i class="fas fa-user-times"></i>
            </div>
            <div class="stat-content">
                <span class="stat-value"><?php echo count(array_filter($usuarios ?? [], fn($u) => $u['activo'] == 0)); ?></span>
                <span class="stat-label">Usuarios Inactivos</span>
            </div>
        </div>
    </div>

    <div class="data-table">
        <div class="table-header">
            <h3><i class="fas fa-list"></i> Lista de Usuarios</h3>
            <div style="display: flex; gap: 1rem;">
                <input type="text" id="searchInput" class="search-input" placeholder="Buscar por nombre o email..."
                    style="padding: 0.5rem 1rem; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); border-radius: 8px; color: #fff; min-width: 300px;">
                <select id="filterRol" class="search-input" style="padding: 0.5rem 1rem; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); border-radius: 8px; color: #fff;">
                    <option value="">Todos los roles</option>
                    <option value="Administrador">Administrador</option>
                    <option value="Encargado Sucursal">Encargado Sucursal</option>
                    <option value="Cliente">Cliente</option>
                </select>
                <select id="filterEstado" class="search-input" style="padding: 0.5rem 1rem; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); border-radius: 8px; color: #fff;">
                    <option value="">Todos los estados</option>
                    <option value="1">Activos</option>
                    <option value="0">Inactivos</option>
                </select>
            </div>
        </div>

        <div class="table-responsive">
            <table class="admin-table" id="usuariosTable">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Email</th>
                        <th>Teléfono</th>
                        <th>Rol</th>
                        <th>Estado</th>
                        <th>Fecha Registro</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($usuarios)): ?>
                        <?php foreach ($usuarios as $usuario): ?>
                            <tr data-id="<?php echo $usuario['id_usuario']; ?>"
                                data-rol="<?php echo htmlspecialchars($usuario['nombre_rol'] ?? ''); ?>"
                                data-estado="<?php echo $usuario['activo']; ?>">
                                <td><strong>#<?php echo $usuario['id_usuario']; ?></strong></td>
                                <td>
                                    <div style="display: flex; align-items: center; gap: 0.75rem;">
                                        <div style="width: 40px; height: 40px; border-radius: 50%; background: linear-gradient(135deg, #3498db, #2ecc71); display: flex; align-items: center; justify-content: center; color: white; font-weight: 700; font-size: 1rem;">
                                            <?php echo strtoupper(substr($usuario['nombre_completo'], 0, 1)); ?>
                                        </div>
                                        <span><?php echo htmlspecialchars($usuario['nombre_completo']); ?></span>
                                    </div>
                                </td>
                                <td><?php echo htmlspecialchars($usuario['email']); ?></td>
                                <td><?php echo htmlspecialchars($usuario['telefono'] ?? 'N/A'); ?></td>
                                <td>
                                    <span class="badge badge-<?php
                                                                echo match ($usuario['nombre_rol'] ?? '') {
                                                                    'Administrador' => 'danger',
                                                                    'Encargado Sucursal' => 'warning',
                                                                    'Cliente' => 'info',
                                                                    default => 'secondary'
                                                                };
                                                                ?>" style="padding: 0.35rem 0.75rem; border-radius: 12px; font-size: 0.8rem; font-weight: 600;">
                                        <?php echo htmlspecialchars($usuario['nombre_rol'] ?? 'Sin rol'); ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge badge-<?php echo $usuario['activo'] == 1 ? 'success' : 'secondary'; ?>"
                                        style="padding: 0.35rem 0.75rem; border-radius: 12px; font-size: 0.8rem; font-weight: 600;">
                                        <i class="fas fa-circle" style="font-size: 0.5rem; margin-right: 0.25rem;"></i>
                                        <?php echo $usuario['activo'] == 1 ? 'Activo' : 'Inactivo'; ?>
                                    </span>
                                </td>
                                <td><?php echo date('d/m/Y', strtotime($usuario['fecha_registro'])); ?></td>
                                <td>
                                    <div style="display: flex; gap: 0.5rem;">
                                        <button class="btn-icon" title="Ver detalles" onclick="verUsuario(<?php echo $usuario['id_usuario']; ?>)">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button class="btn-icon" title="Editar" onclick="editarUsuario(<?php echo $usuario['id_usuario']; ?>)">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn-icon" title="<?php echo $usuario['activo'] == 1 ? 'Desactivar' : 'Activar'; ?>"
                                            onclick="toggleEstadoUsuario(<?php echo $usuario['id_usuario']; ?>, <?php echo $usuario['activo']; ?>)">
                                            <i class="fas fa-<?php echo $usuario['activo'] == 1 ? 'ban' : 'check'; ?>"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="text-center py-4">
                                <i class="fas fa-users-slash" style="font-size: 3rem; color: rgba(255,255,255,0.3); margin-bottom: 1rem;"></i>
                                <p style="margin: 0; color: rgba(255,255,255,0.6);">No hay usuarios registrados</p>
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
            <h3 style="margin: 0; color: #2ecc71;"><i class="fas fa-user"></i> Detalles del Usuario</h3>
            <button onclick="cerrarModalDetalles()" style="background: none; border: none; color: white; font-size: 1.5rem; cursor: pointer;">&times;</button>
        </div>
        <div class="modal-body" style="padding: 2rem;">
            <div class="user-details">
                <div class="detail-row" style="display: flex; align-items: center; gap: 1.5rem; margin-bottom: 2rem; padding-bottom: 1.5rem; border-bottom: 1px solid rgba(255,255,255,0.1);">
                    <div id="detalleAvatar" style="width: 80px; height: 80px; border-radius: 50%; background: linear-gradient(135deg, #3498db, #2ecc71); display: flex; align-items: center; justify-content: center; color: white; font-weight: 700; font-size: 2rem; box-shadow: 0 4px 12px rgba(0,0,0,0.3);"></div>
                    <div>
                        <h2 id="detalleNombre" style="margin: 0 0 0.5rem 0; font-size: 1.5rem; color: #fff;"></h2>
                        <p id="detalleEmail" style="margin: 0; color: rgba(255,255,255,0.7); font-size: 0.95rem;"></p>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 1.5rem;">
                    <div class="detail-item">
                        <label style="display: block; color: rgba(255,255,255,0.6); font-size: 0.85rem; margin-bottom: 0.5rem; text-transform: uppercase; letter-spacing: 0.5px;">ID Usuario</label>
                        <p id="detalleId" style="margin: 0; color: #fff; font-size: 1.1rem; font-weight: 600;"></p>
                    </div>
                    <div class="detail-item">
                        <label style="display: block; color: rgba(255,255,255,0.6); font-size: 0.85rem; margin-bottom: 0.5rem; text-transform: uppercase; letter-spacing: 0.5px;">Teléfono</label>
                        <p id="detalleTelefono" style="margin: 0; color: #fff; font-size: 1.1rem; font-weight: 600;"></p>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 1.5rem;">
                    <div class="detail-item">
                        <label style="display: block; color: rgba(255,255,255,0.6); font-size: 0.85rem; margin-bottom: 0.5rem; text-transform: uppercase; letter-spacing: 0.5px;">Rol</label>
                        <div id="detalleRol"></div>
                    </div>
                    <div class="detail-item">
                        <label style="display: block; color: rgba(255,255,255,0.6); font-size: 0.85rem; margin-bottom: 0.5rem; text-transform: uppercase; letter-spacing: 0.5px;">Estado</label>
                        <div id="detalleEstado"></div>
                    </div>
                </div>

                <div class="detail-item">
                    <label style="display: block; color: rgba(255,255,255,0.6); font-size: 0.85rem; margin-bottom: 0.5rem; text-transform: uppercase; letter-spacing: 0.5px;">Fecha de Registro</label>
                    <p id="detalleFecha" style="margin: 0; color: #fff; font-size: 1.1rem; font-weight: 600;"></p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal para nuevo/editar usuario -->
<div id="modalUsuario" class="modal" style="display: none;">
    <div class="modal-content" style="max-width: 600px;">
        <div class="modal-header" style="display: flex; justify-content: space-between; align-items: center; padding: 1.5rem; border-bottom: 1px solid rgba(255,255,255,0.1);">
            <h3 id="modalTitle" style="margin: 0; color: #2ecc71;"><i class="fas fa-user-plus"></i> Nuevo Usuario</h3>
            <button onclick="cerrarModal()" style="background: none; border: none; color: white; font-size: 1.5rem; cursor: pointer;">&times;</button>
        </div>
        <div class="modal-body" style="padding: 2rem;">
            <form id="formUsuario">
                <input type="hidden" id="usuarioId" name="id_usuario">

                <div class="form-group" style="margin-bottom: 1.5rem;">
                    <label style="display: block; margin-bottom: 0.5rem; color: rgba(255,255,255,0.9); font-weight: 600;">Nombre Completo *</label>
                    <input type="text" id="nombreCompleto" name="nombre_completo" required
                        style="width: 100%; padding: 0.75rem; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); border-radius: 8px; color: #fff;">
                </div>

                <div class="form-group" style="margin-bottom: 1.5rem;">
                    <label style="display: block; margin-bottom: 0.5rem; color: rgba(255,255,255,0.9); font-weight: 600;">Email *</label>
                    <input type="email" id="email" name="email" required
                        style="width: 100%; padding: 0.75rem; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); border-radius: 8px; color: #fff;">
                </div>

                <div class="form-group" style="margin-bottom: 1.5rem;">
                    <label style="display: block; margin-bottom: 0.5rem; color: rgba(255,255,255,0.9); font-weight: 600;">Teléfono</label>
                    <input type="text" id="telefono" name="telefono"
                        style="width: 100%; padding: 0.75rem; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); border-radius: 8px; color: #fff;">
                </div>

                <div class="form-group" style="margin-bottom: 1.5rem;">
                    <label style="display: block; margin-bottom: 0.5rem; color: rgba(255,255,255,0.9); font-weight: 600;">Contraseña *</label>
                    <input type="password" id="password" name="password"
                        style="width: 100%; padding: 0.75rem; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); border-radius: 8px; color: #fff;"
                        placeholder="Dejar en blanco para mantener la actual (solo edición)">
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 2rem;">
                    <div class="form-group">
                        <label style="display: block; margin-bottom: 0.5rem; color: rgba(255,255,255,0.9); font-weight: 600;">Rol *</label>
                        <select id="rol" name="id_rol" required
                            style="width: 100%; padding: 0.75rem; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); border-radius: 8px; color: #fff;">
                            <option value="3">Cliente</option>
                            <option value="2">Encargado Sucursal</option>
                            <option value="1">Administrador</option>
                        </select>
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
                    <button type="submit" class="btn btn-primary">Guardar Usuario</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    .modal {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.8);
        backdrop-filter: blur(5px);
        z-index: 9999;
        display: flex;
        align-items: center;
        justify-content: center;
        animation: fadeIn 0.3s ease;
    }

    .modal-content {
        background: linear-gradient(135deg, rgba(30, 39, 46, 0.95), rgba(42, 53, 63, 0.95));
        border-radius: 20px;
        border: 1px solid rgba(255, 255, 255, 0.1);
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5);
        max-height: 90vh;
        overflow-y: auto;
        animation: slideUp 0.3s ease;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
        }

        to {
            opacity: 1;
        }
    }

    @keyframes slideUp {
        from {
            transform: translateY(50px);
        }

        to {
            transform: translateY(0);
        }
    }

    .badge-danger {
        background: linear-gradient(135deg, rgba(231, 76, 60, 0.3), rgba(192, 57, 43, 0.2));
        color: #e74c3c;
        border: 1px solid rgba(231, 76, 60, 0.4);
    }

    .badge-warning {
        background: linear-gradient(135deg, rgba(241, 196, 15, 0.3), rgba(243, 156, 18, 0.2));
        color: #f1c40f;
        border: 1px solid rgba(241, 196, 15, 0.4);
    }

    .badge-info {
        background: linear-gradient(135deg, rgba(52, 152, 219, 0.3), rgba(41, 128, 185, 0.2));
        color: #3498db;
        border: 1px solid rgba(52, 152, 219, 0.4);
    }

    .badge-success {
        background: linear-gradient(135deg, rgba(46, 204, 113, 0.3), rgba(39, 174, 96, 0.2));
        color: #2ecc71;
        border: 1px solid rgba(46, 204, 113, 0.4);
    }

    .badge-secondary {
        background: linear-gradient(135deg, rgba(149, 165, 166, 0.3), rgba(127, 140, 141, 0.2));
        color: #95a5a6;
        border: 1px solid rgba(149, 165, 166, 0.4);
    }

    .btn-icon {
        background: rgba(255, 255, 255, 0.05);
        border: 1px solid rgba(255, 255, 255, 0.1);
        color: #2ecc71;
        padding: 0.5rem;
        border-radius: 8px;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .btn-icon:hover {
        background: rgba(46, 204, 113, 0.2);
        border-color: rgba(46, 204, 113, 0.5);
        transform: scale(1.1);
    }

    /* Modal de notificación */
    .notification-modal {
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 10000;
        max-width: 400px;
        min-width: 300px;
        background: linear-gradient(135deg, rgba(30, 39, 46, 0.98), rgba(42, 53, 63, 0.98));
        border-radius: 16px;
        border: 1px solid rgba(255, 255, 255, 0.1);
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5), 0 0 20px rgba(46, 204, 113, 0.3);
        padding: 1.5rem;
        backdrop-filter: blur(10px);
        animation: slideInRight 0.4s ease, shake 0.5s ease 0.1s;
        transform-origin: right center;
    }

    .notification-modal.success {
        border-left: 4px solid #2ecc71;
    }

    .notification-modal.error {
        border-left: 4px solid #e74c3c;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5), 0 0 20px rgba(231, 76, 60, 0.3);
    }

    .notification-modal.warning {
        border-left: 4px solid #f1c40f;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5), 0 0 20px rgba(241, 196, 15, 0.3);
    }

    .notification-content {
        display: flex;
        align-items: flex-start;
        gap: 1rem;
    }

    .notification-icon {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        flex-shrink: 0;
    }

    .notification-modal.success .notification-icon {
        background: linear-gradient(135deg, rgba(46, 204, 113, 0.3), rgba(39, 174, 96, 0.2));
        color: #2ecc71;
        animation: pulse 2s infinite;
    }

    .notification-modal.error .notification-icon {
        background: linear-gradient(135deg, rgba(231, 76, 60, 0.3), rgba(192, 57, 43, 0.2));
        color: #e74c3c;
        animation: pulse 2s infinite;
    }

    .notification-modal.warning .notification-icon {
        background: linear-gradient(135deg, rgba(241, 196, 15, 0.3), rgba(243, 156, 18, 0.2));
        color: #f1c40f;
        animation: pulse 2s infinite;
    }

    .notification-body {
        flex: 1;
    }

    .notification-title {
        margin: 0 0 0.5rem 0;
        font-size: 1.1rem;
        font-weight: 700;
        color: #fff;
    }

    .notification-message {
        margin: 0;
        color: rgba(255, 255, 255, 0.8);
        font-size: 0.95rem;
        line-height: 1.5;
    }

    .notification-close {
        position: absolute;
        top: 0.75rem;
        right: 0.75rem;
        background: none;
        border: none;
        color: rgba(255, 255, 255, 0.6);
        font-size: 1.5rem;
        cursor: pointer;
        width: 30px;
        height: 30px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        transition: all 0.3s ease;
    }

    .notification-close:hover {
        background: rgba(255, 255, 255, 0.1);
        color: #fff;
        transform: rotate(90deg);
    }

    @keyframes slideInRight {
        from {
            transform: translateX(500px);
            opacity: 0;
        }

        to {
            transform: translateX(0);
            opacity: 1;
        }
    }

    @keyframes slideOutRight {
        from {
            transform: translateX(0);
            opacity: 1;
        }

        to {
            transform: translateX(500px);
            opacity: 0;
        }
    }

    @keyframes shake {

        0%,
        100% {
            transform: translateX(0);
        }

        10%,
        30%,
        50%,
        70%,
        90% {
            transform: translateX(-5px);
        }

        20%,
        40%,
        60%,
        80% {
            transform: translateX(5px);
        }
    }

    @keyframes pulse {

        0%,
        100% {
            transform: scale(1);
            opacity: 1;
        }

        50% {
            transform: scale(1.05);
            opacity: 0.8;
        }
    }
</style>

<script>
    const APP_URL = '<?php echo APP_URL; ?>';

    // Sistema de notificaciones modales
    function mostrarNotificacion(tipo, titulo, mensaje) {
        // Remover notificaciones anteriores
        const notificacionesAnteriores = document.querySelectorAll('.notification-modal');
        notificacionesAnteriores.forEach(n => n.remove());

        // Crear contenedor de notificación
        const notificacion = document.createElement('div');
        notificacion.className = `notification-modal ${tipo}`;

        // Determinar icono según el tipo
        let icono = '';
        switch (tipo) {
            case 'success':
                icono = 'fa-check-circle';
                break;
            case 'error':
                icono = 'fa-times-circle';
                break;
            case 'warning':
                icono = 'fa-exclamation-triangle';
                break;
            default:
                icono = 'fa-info-circle';
        }

        notificacion.innerHTML = `
            <button class="notification-close" onclick="cerrarNotificacion(this)">&times;</button>
            <div class="notification-content">
                <div class="notification-icon">
                    <i class="fas ${icono}"></i>
                </div>
                <div class="notification-body">
                    <h4 class="notification-title">${titulo}</h4>
                    <p class="notification-message">${mensaje}</p>
                </div>
            </div>
        `;

        document.body.appendChild(notificacion);

        // Auto-cerrar después de 4 segundos
        setTimeout(() => {
            cerrarNotificacion(notificacion);
        }, 4000);
    }

    function cerrarNotificacion(elemento) {
        const notificacion = elemento instanceof HTMLElement && elemento.classList.contains('notification-modal') ?
            elemento :
            elemento.closest('.notification-modal');

        if (notificacion) {
            notificacion.style.animation = 'slideOutRight 0.4s ease';
            setTimeout(() => {
                notificacion.remove();
            }, 400);
        }
    }

    // Busqueda y filtrado
    document.getElementById('searchInput').addEventListener('input', filtrarTabla);
    document.getElementById('filterRol').addEventListener('change', filtrarTabla);
    document.getElementById('filterEstado').addEventListener('change', filtrarTabla);

    function filtrarTabla() {
        const searchTerm = document.getElementById('searchInput').value.toLowerCase();
        const filterRol = document.getElementById('filterRol').value;
        const filterEstado = document.getElementById('filterEstado').value;
        const rows = document.querySelectorAll('#usuariosTable tbody tr');

        rows.forEach(row => {
            const nombre = row.cells[1]?.textContent.toLowerCase() || '';
            const email = row.cells[2]?.textContent.toLowerCase() || '';
            const rol = row.dataset.rol || '';
            const estado = row.dataset.estado || '';

            const matchSearch = nombre.includes(searchTerm) || email.includes(searchTerm);
            const matchRol = !filterRol || rol === filterRol;
            const matchEstado = !filterEstado || estado === filterEstado;

            row.style.display = matchSearch && matchRol && matchEstado ? '' : 'none';
        });
    }

    function abrirModalNuevoUsuario() {
        document.getElementById('modalTitle').innerHTML = '<i class="fas fa-user-plus"></i> Nuevo Usuario';
        document.getElementById('formUsuario').reset();
        document.getElementById('usuarioId').value = '';
        document.getElementById('password').required = true;
        document.getElementById('password').placeholder = '';
        document.getElementById('modalUsuario').style.display = 'flex';
    }

    function cerrarModal() {
        document.getElementById('modalUsuario').style.display = 'none';
    }

    function cerrarModalDetalles() {
        document.getElementById('modalDetalles').style.display = 'none';
    }

    function verUsuario(id) {
        fetch(`${APP_URL}/admin/obtenerUsuario?id=${id}`)
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    const u = data.data;

                    // Llenar el modal con los datos
                    document.getElementById('detalleAvatar').textContent = u.nombre_completo.charAt(0).toUpperCase();
                    document.getElementById('detalleNombre').textContent = u.nombre_completo;
                    document.getElementById('detalleEmail').textContent = u.email;
                    document.getElementById('detalleId').textContent = '#' + u.id_usuario;
                    document.getElementById('detalleTelefono').textContent = u.telefono || 'N/A';

                    // Badge de rol
                    const rolClass = {
                        'Administrador': 'danger',
                        'Encargado Sucursal': 'warning',
                        'Cliente': 'info'
                    } [u.nombre_rol] || 'secondary';
                    document.getElementById('detalleRol').innerHTML =
                        `<span class="badge badge-${rolClass}" style="padding: 0.5rem 1rem; border-radius: 12px; font-size: 0.9rem; font-weight: 600;">${u.nombre_rol}</span>`;

                    // Badge de estado
                    const estadoClass = u.activo == 1 ? 'success' : 'secondary';
                    const estadoText = u.activo == 1 ? 'Activo' : 'Inactivo';
                    document.getElementById('detalleEstado').innerHTML =
                        `<span class="badge badge-${estadoClass}" style="padding: 0.5rem 1rem; border-radius: 12px; font-size: 0.9rem; font-weight: 600;">
                        <i class="fas fa-circle" style="font-size: 0.5rem; margin-right: 0.5rem;"></i>${estadoText}
                    </span>`;

                    document.getElementById('detalleFecha').textContent =
                        new Date(u.fecha_registro).toLocaleDateString('es-PE', {
                            year: 'numeric',
                            month: 'long',
                            day: 'numeric',
                            hour: '2-digit',
                            minute: '2-digit'
                        });

                    // Mostrar el modal
                    document.getElementById('modalDetalles').style.display = 'flex';
                } else {
                    mostrarNotificacion('error', 'Error', data.message);
                }
            })
            .catch(err => mostrarNotificacion('error', 'Error', 'Error al cargar usuario: ' + err));
    }

    function editarUsuario(id) {
        fetch(`${APP_URL}/admin/obtenerUsuario?id=${id}`)
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    const u = data.data;
                    document.getElementById('modalTitle').innerHTML = '<i class="fas fa-user-edit"></i> Editar Usuario';
                    document.getElementById('usuarioId').value = u.id_usuario;
                    document.getElementById('nombreCompleto').value = u.nombre_completo;
                    document.getElementById('email').value = u.email;
                    document.getElementById('telefono').value = u.telefono || '';
                    document.getElementById('rol').value = u.id_rol;
                    document.getElementById('activo').value = u.activo;
                    document.getElementById('password').required = false;
                    document.getElementById('password').value = '';
                    document.getElementById('password').placeholder = 'Dejar en blanco para mantener la actual';
                    document.getElementById('modalUsuario').style.display = 'flex';
                } else {
                    mostrarNotificacion('error', 'Error', data.message);
                }
            })
            .catch(err => mostrarNotificacion('error', 'Error', 'Error al cargar usuario: ' + err));
    }

    function toggleEstadoUsuario(id, estadoActual) {
        const accion = estadoActual == 1 ? 'desactivar' : 'activar';
        if (!confirm(`¿Estás seguro de ${accion} este usuario?`)) {
            return;
        }

        const nuevoEstado = estadoActual == 1 ? 0 : 1;

        fetch(`${APP_URL}/admin/cambiarEstadoUsuario`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    id_usuario: id,
                    nuevo_estado: nuevoEstado
                })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    mostrarNotificacion('success', '¡Éxito!', data.message);
                    setTimeout(() => location.reload(), 1500);
                } else {
                    mostrarNotificacion('error', 'Error', data.message);
                }
            })
            .catch(err => mostrarNotificacion('error', 'Error', 'Error al cambiar estado: ' + err));
    }

    // Manejo del formulario
    document.getElementById('formUsuario').addEventListener('submit', function(e) {
        e.preventDefault();

        const formData = {
            id_usuario: document.getElementById('usuarioId').value,
            nombre_completo: document.getElementById('nombreCompleto').value,
            email: document.getElementById('email').value,
            telefono: document.getElementById('telefono').value,
            password: document.getElementById('password').value,
            id_rol: document.getElementById('rol').value,
            activo: document.getElementById('activo').value
        };

        // Validación básica
        if (!formData.nombre_completo || !formData.email) {
            mostrarNotificacion('warning', 'Atención', 'Por favor completa los campos obligatorios (*)');
            return;
        }

        if (!formData.id_usuario && !formData.password) {
            mostrarNotificacion('warning', 'Atención', 'La contraseña es obligatoria para nuevos usuarios');
            return;
        }

        fetch(`${APP_URL}/admin/guardarUsuario`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(formData)
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    mostrarNotificacion('success', '¡Éxito!', data.message);
                    cerrarModal();
                    setTimeout(() => location.reload(), 1500);
                } else {
                    mostrarNotificacion('error', 'Error', data.message);
                }
            })
            .catch(err => mostrarNotificacion('error', 'Error', 'Error al guardar: ' + err));
    });

    // Cerrar modales al hacer clic fuera
    document.getElementById('modalUsuario').addEventListener('click', function(e) {
        if (e.target === this) cerrarModal();
    });
    document.getElementById('modalDetalles').addEventListener('click', function(e) {
        if (e.target === this) cerrarModalDetalles();
    });
</script>

<?php require_once ROOT_PATH . '/views/layouts/footer.php'; ?>