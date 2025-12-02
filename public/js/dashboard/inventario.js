/**
 * GESTIÓN DE INVENTARIO - JavaScript
 */

let APP_URL;

// Sistema de notificaciones (reutilizado)
function mostrarNotificacion(tipo, titulo, mensaje) {
    const notificacionesAnteriores = document.querySelectorAll('.notification-modal');
    notificacionesAnteriores.forEach(n => n.remove());

    const notificacion = document.createElement('div');
    notificacion.className = `notification-modal ${tipo}`;

    let icono = { success: 'fa-check-circle', error: 'fa-times-circle', warning: 'fa-exclamation-triangle' }[tipo] || 'fa-info-circle';

    notificacion.innerHTML = `
        <button class="notification-close" onclick="cerrarNotificacion(this)">&times;</button>
        <div class="notification-content">
            <div class="notification-icon"><i class="fas ${icono}"></i></div>
            <div class="notification-body">
                <h4 class="notification-title">${titulo}</h4>
                <p class="notification-message">${mensaje}</p>
            </div>
        </div>
    `;

    document.body.appendChild(notificacion);
    setTimeout(() => cerrarNotificacion(notificacion), 4000);
}

function cerrarNotificacion(elemento) {
    const notificacion = elemento instanceof HTMLElement && elemento.classList.contains('notification-modal')
        ? elemento : elemento.closest('.notification-modal');
    if (notificacion) {
        notificacion.style.animation = 'slideOutRight 0.4s ease';
        setTimeout(() => notificacion.remove(), 400);
    }
}

// Filtrado
function filtrarTabla() {
    const searchTerm = document.getElementById('searchInput').value.toLowerCase();
    const filterSucursal = document.getElementById('filterSucursal').value;
    const filterNivel = document.getElementById('filterNivel').value;
    const rows = document.querySelectorAll('#inventarioTable tbody tr');

    rows.forEach(row => {
        const producto = row.cells[1]?.textContent.toLowerCase() || '';
        const sucursal = row.dataset.sucursal || '';
        const nivel = row.dataset.nivel || '';

        const matchSearch = producto.includes(searchTerm);
        const matchSucursal = !filterSucursal || sucursal === filterSucursal;
        const matchNivel = !filterNivel || nivel === filterNivel;

        row.style.display = matchSearch && matchSucursal && matchNivel ? '' : 'none';
    });
}

// Abrir modal nuevo movimiento
function abrirModalMovimiento() {
    document.getElementById('formMovimiento').reset();
    document.getElementById('modalMovimiento').style.display = 'flex';
    cargarSucursales();
    cargarProductos();
}

function cerrarModalMovimiento() {
    document.getElementById('modalMovimiento').style.display = 'none';
}

// Abrir modal transferencia
function abrirModalTransferencia() {
    document.getElementById('formTransferencia').reset();
    document.getElementById('modalTransferencia').style.display = 'flex';
    cargarSucursalesTransfer();
    cargarProductosTransfer();
}

function cerrarModalTransferencia() {
    document.getElementById('modalTransferencia').style.display = 'none';
}

function cerrarModalHistorial() {
    document.getElementById('modalHistorial').style.display = 'none';
}

// Cargar sucursales
function cargarSucursales() {
    fetch(`${APP_URL}/admin/sucursales`)
        .then(res => res.text())
        .then(() => {
            // Temporalmente usar datos del DOM
            const select = document.getElementById('sucursalMovimiento');
            const options = document.querySelectorAll('#inventarioTable tbody tr');
            const sucursales = new Set();

            options.forEach(row => {
                const sucursal = row.dataset.sucursal;
                const nombre = row.cells[0]?.textContent;
                if (sucursal && nombre) sucursales.add(JSON.stringify({ id: sucursal, nombre }));
            });

            select.innerHTML = '<option value="">Seleccionar sucursal</option>';
            sucursales.forEach(s => {
                const data = JSON.parse(s);
                const opt = document.createElement('option');
                opt.value = data.id;
                opt.textContent = data.nombre;
                select.appendChild(opt);
            });
        })
        .catch(err => console.error('Error:', err));
}

// Cargar productos
function cargarProductos() {
    fetch(`${APP_URL}/admin/productos`)
        .then(res => res.text())
        .then(() => {
            const select = document.getElementById('productoMovimiento');
            const options = document.querySelectorAll('#inventarioTable tbody tr');
            const productos = new Set();

            options.forEach(row => {
                const producto = row.dataset.producto;
                const nombre = row.cells[1]?.textContent.split('\n')[0];
                if (producto && nombre) productos.add(JSON.stringify({ id: producto, nombre }));
            });

            select.innerHTML = '<option value="">Seleccionar producto</option>';
            productos.forEach(p => {
                const data = JSON.parse(p);
                const opt = document.createElement('option');
                opt.value = data.id;
                opt.textContent = data.nombre;
                select.appendChild(opt);
            });
        })
        .catch(err => console.error('Error:', err));
}

// Cargar para transferencia
function cargarSucursalesTransfer() {
    cargarSucursales();
    setTimeout(() => {
        const selectOrigen = document.getElementById('sucursalOrigen');
        const selectDestino = document.getElementById('sucursalDestino');
        selectOrigen.innerHTML = document.getElementById('sucursalMovimiento').innerHTML;
        selectDestino.innerHTML = document.getElementById('sucursalMovimiento').innerHTML;
    }, 100);
}

function cargarProductosTransfer() {
    cargarProductos();
    setTimeout(() => {
        const select = document.getElementById('productoTransferencia');
        select.innerHTML = document.getElementById('productoMovimiento').innerHTML;
    }, 100);
}

// Registrar movimiento
function guardarMovimiento(e) {
    e.preventDefault();

    const formData = {
        id_sucursal: document.getElementById('sucursalMovimiento').value,
        id_producto: document.getElementById('productoMovimiento').value,
        tipo_movimiento: document.getElementById('tipoMovimiento').value,
        cantidad: document.getElementById('cantidadMovimiento').value,
        motivo: document.getElementById('motivoMovimiento').value,
        referencia: document.getElementById('referenciaMovimiento').value
    };

    if (!formData.id_sucursal || !formData.id_producto || !formData.tipo_movimiento || !formData.cantidad) {
        mostrarNotificacion('warning', 'Atención', 'Por favor completa los campos obligatorios');
        return;
    }

    fetch(`${APP_URL}/admin/registrarMovimiento`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(formData)
    })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                mostrarNotificacion('success', '¡Éxito!', data.message);
                cerrarModalMovimiento();
                setTimeout(() => location.reload(), 1500);
            } else {
                mostrarNotificacion('error', 'Error', data.message);
            }
        })
        .catch(err => mostrarNotificacion('error', 'Error', 'Error al registrar: ' + err));
}

// Transferir stock
function guardarTransferencia(e) {
    e.preventDefault();

    const formData = {
        id_sucursal_origen: document.getElementById('sucursalOrigen').value,
        id_sucursal_destino: document.getElementById('sucursalDestino').value,
        id_producto: document.getElementById('productoTransferencia').value,
        cantidad: document.getElementById('cantidadTransferencia').value,
        motivo: document.getElementById('motivoTransferencia').value
    };

    if (!formData.id_sucursal_origen || !formData.id_sucursal_destino || !formData.id_producto || !formData.cantidad) {
        mostrarNotificacion('warning', 'Atención', 'Por favor completa los campos obligatorios');
        return;
    }

    if (formData.id_sucursal_origen === formData.id_sucursal_destino) {
        mostrarNotificacion('error', 'Error', 'Las sucursales deben ser diferentes');
        return;
    }

    fetch(`${APP_URL}/admin/transferirStock`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(formData)
    })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                mostrarNotificacion('success', '¡Éxito!', data.message);
                cerrarModalTransferencia();
                setTimeout(() => location.reload(), 1500);
            } else {
                mostrarNotificacion('error', 'Error', data.message);
            }
        })
        .catch(err => mostrarNotificacion('error', 'Error', 'Error al transferir: ' + err));
}

// Ver historial
function verHistorial(idInventario) {
    fetch(`${APP_URL}/admin/obtenerMovimientos?id_inventario=${idInventario}`)
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                const timeline = document.getElementById('timelineMovimientos');
                timeline.innerHTML = '';

                if (data.data.length === 0) {
                    timeline.innerHTML = '<p style="text-align: center; color: rgba(255,255,255,0.6);">No hay movimientos registrados</p>';
                } else {
                    data.data.forEach(mov => {
                        const item = document.createElement('div');
                        item.className = 'timeline-item';
                        item.innerHTML = `
                            <div class="timeline-icon" style="background: var(--color-${mov.tipo_movimiento});">
                                <i class="fas fa-${getIconoMovimiento(mov.tipo_movimiento)}"></i>
                            </div>
                            <div class="timeline-content">
                                <div class="timeline-header">
                                    <span class="badge-movimiento ${mov.tipo_movimiento}">
                                        <i class="fas fa-${getIconoMovimiento(mov.tipo_movimiento)}"></i>
                                        ${mov.tipo_movimiento.charAt(0).toUpperCase() + mov.tipo_movimiento.slice(1)}
                                    </span>
                                    <span class="timeline-date">${new Date(mov.fecha_movimiento).toLocaleString('es-PE')}</span>
                                </div>
                                <div class="timeline-meta">
                                    <strong>Cantidad:</strong> ${mov.cantidad} | 
                                    <strong>Usuario:</strong> ${mov.usuario_nombre || 'Sistema'}
                                </div>
                                ${mov.motivo ? `<div class="timeline-comment">${mov.motivo}</div>` : ''}
                                ${mov.referencia ? `<div style="font-size: 0.8rem; color: rgba(255,255,255,0.5);"><strong>Ref:</strong> ${mov.referencia}</div>` : ''}
                            </div>
                        `;
                        timeline.appendChild(item);
                    });
                }

                document.getElementById('modalHistorial').style.display = 'flex';
            } else {
                mostrarNotificacion('error', 'Error', data.message);
            }
        })
        .catch(err => mostrarNotificacion('error', 'Error', 'Error al cargar historial: ' + err));
}

function getIconoMovimiento(tipo) {
    const iconos = {
        'entrada': 'arrow-down',
        'salida': 'arrow-up',
        'ajuste': 'sliders-h',
        'transferencia': 'exchange-alt',
        'devolucion': 'undo'
    };
    return iconos[tipo] || 'circle';
}

// Inicializar
document.addEventListener('DOMContentLoaded', function () {
    APP_URL = window.APP_URL;

    const searchInput = document.getElementById('searchInput');
    const filterSucursal = document.getElementById('filterSucursal');
    const filterNivel = document.getElementById('filterNivel');

    if (searchInput) searchInput.addEventListener('input', filtrarTabla);
    if (filterSucursal) filterSucursal.addEventListener('change', filtrarTabla);
    if (filterNivel) filterNivel.addEventListener('change', filtrarTabla);

    const formMovimiento = document.getElementById('formMovimiento');
    if (formMovimiento) formMovimiento.addEventListener('submit', guardarMovimiento);

    const formTransferencia = document.getElementById('formTransferencia');
    if (formTransferencia) formTransferencia.addEventListener('submit', guardarTransferencia);

    const modals = ['modalMovimiento', 'modalTransferencia', 'modalHistorial'];
    modals.forEach(id => {
        const modal = document.getElementById(id);
        if (modal) {
            modal.addEventListener('click', function (e) {
                if (e.target === this) {
                    this.style.display = 'none';
                }
            });
        }
    });
});
