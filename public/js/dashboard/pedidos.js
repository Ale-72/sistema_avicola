/**
 * GESTIÓN DE PEDIDOS - JavaScript
 * Sistema completo para gestión de pedidos con seguimiento de estados
 */

// Variable global para APP_URL
let APP_URL;

// Reutilizar sistema de notificaciones de productos.js
function mostrarNotificacion(tipo, titulo, mensaje) {
    const notificacionesAnteriores = document.querySelectorAll('.notification-modal');
    notificacionesAnteriores.forEach(n => n.remove());

    const notificacion = document.createElement('div');
    notificacion.className = `notification-modal ${tipo}`;

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

    setTimeout(() => {
        cerrarNotificacion(notificacion);
    }, 4000);
}

function cerrarNotificacion(elemento) {
    const notificacion = elemento instanceof HTMLElement && elemento.classList.contains('notification-modal')
        ? elemento
        : elemento.closest('.notification-modal');

    if (notificacion) {
        notificacion.style.animation = 'slideOutRight 0.4s ease';
        setTimeout(() => {
            notificacion.remove();
        }, 400);
    }
}

// Función para formatear moneda
function formatearMoneda(valor) {
    return 'S/ ' + parseFloat(valor).toFixed(2);
}

// Función para formatear fecha
function formatearFecha(fecha) {
    const date = new Date(fecha);
    const opciones = { year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit' };
    return date.toLocaleDateString('es-PE', opciones);
}

// Funciones de filtrado
function filtrarTabla() {
    const searchTerm = document.getElementById('searchInput').value.toLowerCase();
    const filterEstado = document.getElementById('filterEstado').value;
    const filterPago = document.getElementById('filterPago').value;
    const rows = document.querySelectorAll('#pedidosTable tbody tr');

    rows.forEach(row => {
        const numeroPedido = row.cells[0]?.textContent.toLowerCase() || '';
        const cliente = row.cells[1]?.textContent.toLowerCase() || '';
        const estadoId = row.dataset.estado || '';
        const metodoPago = row.dataset.metodopago || '';

        const matchSearch = numeroPedido.includes(searchTerm) || cliente.includes(searchTerm);
        const matchEstado = !filterEstado || estadoId === filterEstado;
        const matchPago = !filterPago || metodoPago === filterPago;

        row.style.display = matchSearch && matchEstado && matchPago ? '' : 'none';
    });
}

// Ver detalles completos del pedido
function verPedido(id) {
    // Cargar datos del pedido
    fetch(`${APP_URL}/admin/obtenerPedido?id=${id}`)
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                const p = data.data;

                // Información básica
                document.getElementById('detalleNumeroPedido').textContent = p.numero_pedido;
                document.getElementById('detalleFecha').textContent = formatearFecha(p.fecha_pedido);

                // Badge de estado
                const estadoBadge = `<span class="badge-estado" style="background-color: ${p.estado_color}20; color: ${p.estado_color}; border: 1px solid ${p.estado_color}40;">
                    <i class="fas fa-circle" style="font-size: 0.5rem;"></i> ${p.nombre_estado}
                </span>`;
                document.getElementById('detalleEstado').innerHTML = estadoBadge;

                // Cliente
                document.getElementById('detalleClienteNombre').textContent = p.cliente_nombre;
                document.getElementById('detalleClienteEmail').textContent = p.cliente_email || 'N/A';
                document.getElementById('detalleClienteTelefono').textContent = p.cliente_telefono || 'N/A';

                // Entrega
                document.getElementById('detalleDireccion').textContent = p.direccion_entrega || 'Recojo en sucursal';
                document.getElementById('detalleCiudad').textContent = p.ciudad_entrega || 'N/A';
                document.getElementById('detalleMetodoEntrega').textContent = p.metodo_entrega_nombre || 'N/A';
                document.getElementById('detalleSucursal').textContent = p.nombre_sucursal || 'N/A';

                // Método de pago
                const metodoPagoHTML = `<span class="metodo-pago ${p.metodo_pago}">
                    <i class="fas fa-${p.metodo_pago === 'efectivo' ? 'money-bill-wave' : p.metodo_pago === 'tarjeta' ? 'credit-card' : 'exchange-alt'}"></i>
                    ${p.metodo_pago.charAt(0).toUpperCase() + p.metodo_pago.slice(1)}
                </span>`;
                document.getElementById('detalleMetodoPago').innerHTML = metodoPagoHTML;

                // Estado de pago
                const estadoPagoHTML = `<span class="estado-pago ${p.pagado == 1 ? 'pagado' : 'pendiente'}">
                    <i class="fas fa-${p.pagado == 1 ? 'check-circle' : 'clock'}"></i>
                    ${p.pagado == 1 ? 'Pagado' : 'Pendiente'}
                </span>`;
                document.getElementById('detalleEstadoPago').innerHTML = estadoPagoHTML;

                // Cargar productos del pedido
                cargarProductosPedido(id);

                // Totales
                document.getElementById('detalleSubtotal').textContent = formatearMoneda(p.subtotal);
                document.getElementById('detalleCostoEnvio').textContent = formatearMoneda(p.costo_envio);
                document.getElementById('detalleDescuento').textContent = formatearMoneda(p.descuento);
                document.getElementById('detalleTotal').textContent = formatearMoneda(p.total);

                // Cargar historial
                cargarHistorial(id);

                // Guardar ID para cambiar estado
                document.getElementById('modalDetalles').dataset.pedidoId = id;

                // Mostrar modal
                document.getElementById('modalDetalles').style.display = 'flex';
            } else {
                mostrarNotificacion('error', 'Error', data.message);
            }
        })
        .catch(err => mostrarNotificacion('error', 'Error', 'Error al cargar pedido: ' + err));
}

// Cargar productos del pedido
function cargarProductosPedido(idPedido) {
    fetch(`${APP_URL}/admin/obtenerDetallePedido?id=${idPedido}`)
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                const tbody = document.getElementById('productosBody');
                tbody.innerHTML = '';

                if (data.data.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="5" class="text-center">No hay productos en este pedido</td></tr>';
                    return;
                }

                data.data.forEach(producto => {
                    const row = `
                        <tr>
                            <td>${producto.codigo_producto || 'N/A'}</td>
                            <td>${producto.nombre_producto}</td>
                            <td style="text-align: center;">${producto.cantidad}</td>
                            <td style="text-align: right;">${formatearMoneda(producto.precio_unitario)}</td>
                            <td style="text-align: right; font-weight: 700; color: #2ecc71;">${formatearMoneda(producto.subtotal)}</td>
                        </tr>
                    `;
                    tbody.innerHTML += row;
                });
            }
        })
        .catch(err => console.error('Error al cargar productos:', err));
}

// Cargar historial de estados
function cargarHistorial(idPedido) {
    fetch(`${APP_URL}/admin/obtenerHistorialPedido?id=${idPedido}`)
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                const timeline = document.getElementById('timelineHistorial');
                timeline.innerHTML = '';

                if (data.data.length === 0) {
                    timeline.innerHTML = '<p style="color: rgba(255,255,255,0.6); text-align: center;">No hay historial de cambios</p>';
                    return;
                }

                data.data.forEach(item => {
                    const timelineItem = document.createElement('div');
                    timelineItem.className = 'timeline-item';
                    timelineItem.innerHTML = `
                        <div class="timeline-icon" style="background-color: ${item.color_hex};">
                            <i class="fas fa-${getIconoEstado(item.nombre_estado)}"></i>
                        </div>
                        <div class="timeline-content">
                            <div class="timeline-header">
                                <span class="timeline-title">${item.nombre_estado}</span>
                                <span class="timeline-date">${formatearFecha(item.fecha_cambio)}</span>
                            </div>
                            <div class="timeline-meta">
                                <i class="fas fa-user"></i> ${item.usuario_nombre || 'Sistema'}
                            </div>
                            ${item.comentario ? `<div class="timeline-comment"><i class="fas fa-comment"></i> ${item.comentario}</div>` : ''}
                        </div>
                    `;
                    timeline.appendChild(timelineItem);
                });
            }
        })
        .catch(err => console.error('Error al cargar historial:', err));
}

// Obtener icono según estado
function getIconoEstado(nombreEstado) {
    const iconos = {
        'Pendiente': 'clock',
        'Confirmado': 'check',
        'Preparando': 'box',
        'En Camino': 'truck',
        'Listo para Recoger': 'store',
        'Entregado': 'check-circle',
        'Cancelado': 'times-circle'
    };
    return iconos[nombreEstado] || 'circle';
}

// Cerrar modal de detalles
function cerrarModalDetalles() {
    document.getElementById('modalDetalles').style.display = 'none';
}

// Abrir modal de cambio de estado
function abrirModalCambiarEstado() {
    const idPedido = document.getElementById('modalDetalles').dataset.pedidoId;
    document.getElementById('modalCambiarEstado').dataset.pedidoId = idPedido;

    // Cargar estados disponibles
    fetch(`${APP_URL}/admin/obtenerEstados`)
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                const select = document.getElementById('nuevoEstado');
                select.innerHTML = '<option value="">Seleccionar estado</option>';

                data.data.forEach(estado => {
                    const option = document.createElement('option');
                    option.value = estado.id_estado;
                    option.textContent = estado.nombre_estado;
                    option.style.color = estado.color_hex;
                    select.appendChild(option);
                });
            }
        })
        .catch(err => console.error('Error al cargar estados:', err));

    document.getElementById('modalCambiarEstado').style.display = 'flex';
}

// Cerrar modal de cambiar estado
function cerrarModalCambiarEstado() {
    document.getElementById('modalCambiarEstado').style.display = 'none';
    document.getElementById('formCambiarEstado').reset();
}

// Guardar cambio de estado
function guardarCambioEstado(e) {
    e.preventDefault();

    const idPedido = document.getElementById('modalCambiarEstado').dataset.pedidoId;
    const nuevoEstado = document.getElementById('nuevoEstado').value;
    const comentario = document.getElementById('comentarioEstado').value;

    if (!nuevoEstado) {
        mostrarNotificacion('warning', 'Atención', 'Por favor selecciona un estado');
        return;
    }

    fetch(`${APP_URL}/admin/cambiarEstadoPedido`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            id_pedido: idPedido,
            id_estado: nuevoEstado,
            comentario: comentario
        })
    })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                mostrarNotificacion('success', '¡Éxito!', data.message);
                cerrarModalCambiarEstado();
                cerrarModalDetalles();
                setTimeout(() => location.reload(), 1500);
            } else {
                mostrarNotificacion('error', 'Error', data.message);
            }
        })
        .catch(err => mostrarNotificacion('error', 'Error', 'Error al cambiar estado: ' + err));
}

// Inicializar cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', function () {
    APP_URL = window.APP_URL;

    // Event listeners para filtros
    const searchInput = document.getElementById('searchInput');
    const filterEstado = document.getElementById('filterEstado');
    const filterPago = document.getElementById('filterPago');

    if (searchInput) searchInput.addEventListener('input', filtrarTabla);
    if (filterEstado) filterEstado.addEventListener('change', filtrarTabla);
    if (filterPago) filterPago.addEventListener('change', filtrarTabla);

    // Event listener para formulario de cambio de estado
    const formCambiarEstado = document.getElementById('formCambiarEstado');
    if (formCambiarEstado) {
        formCambiarEstado.addEventListener('submit', guardarCambioEstado);
    }

    // Cerrar modales al hacer clic fuera
    const modalDetalles = document.getElementById('modalDetalles');
    const modalCambiarEstado = document.getElementById('modalCambiarEstado');

    if (modalDetalles) {
        modalDetalles.addEventListener('click', function (e) {
            if (e.target === this) cerrarModalDetalles();
        });
    }

    if (modalCambiarEstado) {
        modalCambiarEstado.addEventListener('click', function (e) {
            if (e.target === this) cerrarModalCambiarEstado();
        });
    }
});
