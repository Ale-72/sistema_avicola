/**
 * GESTIÓN DE SUCURSALES - JavaScript
 * Sistema completo de CRUD para sucursales con geolocalización
 */

let APP_URL;

// Sistema de notificaciones (reutilizado)
function mostrarNotificacion(tipo, titulo, mensaje) {
    const notificacionesAnteriores = document.querySelectorAll('.notification-modal');
    notificacionesAnteriores.forEach(n => n.remove());

    const notificacion = document.createElement('div');
    notificacion.className = `notification-modal ${tipo}`;

    let icono = '';
    switch (tipo) {
        case 'success': icono = 'fa-check-circle'; break;
        case 'error': icono = 'fa-times-circle'; break;
        case 'warning': icono = 'fa-exclamation-triangle'; break;
        default: icono = 'fa-info-circle';
    }

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

// Filtrado de tabla
function filtrarTabla() {
    const searchTerm = document.getElementById('searchInput').value.toLowerCase();
    const filterCiudad = document.getElementById('filterCiudad').value;
    const filterEstado = document.getElementById('filterEstado').value;
    const rows = document.querySelectorAll('#sucursalesTable tbody tr');

    rows.forEach(row => {
        const nombre = row.cells[1]?.textContent.toLowerCase() || '';
        const codigo = row.cells[0]?.textContent.toLowerCase() || '';
        const ciudad = row.dataset.ciudad || '';
        const estado = row.dataset.estado || '';

        const matchSearch = nombre.includes(searchTerm) || codigo.includes(searchTerm);
        const matchCiudad = !filterCiudad || ciudad === filterCiudad;
        const matchEstado = !filterEstado || estado === filterEstado;

        row.style.display = matchSearch && matchCiudad && matchEstado ? '' : 'none';
    });
}

// Abrir modal nueva sucursal
function abrirModalNuevaSucursal() {
    document.getElementById('modalTitle').innerHTML = '<i class="fas fa-plus"></i> Nueva Sucursal';
    document.getElementById('formSucursal').reset();
    document.getElementById('sucursalId').value = '';
    document.getElementById('modalSucursal').style.display = 'flex';
    cargarEncargados();
    toggleRadioCobertura();
}

function cerrarModal() {
    document.getElementById('modalSucursal').style.display = 'none';
}

function cerrarModalDetalles() {
    document.getElementById('modalDetalles').style.display = 'none';
}

// Cargar encargados
function cargarEncargados() {
    fetch(`${APP_URL}/admin/obtenerEncargados`)
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                const select = document.getElementById('encargado');
                select.innerHTML = '<option value="">Sin asignar</option>';
                data.data.forEach(enc => {
                    const option = document.createElement('option');
                    option.value = enc.id_usuario;
                    option.textContent = enc.nombre_completo;
                    select.appendChild(option);
                });
            }
        })
        .catch(err => console.error('Error al cargar encargados:', err));
}

// Toggle de radio de cobertura
function toggleRadioCobertura() {
    const deliveryCheck = document.getElementById('permiteDelivery');
    const radioGroup = document.getElementById('radioCobertura Group');
    if (deliveryCheck && radioGroup) {
        if (deliveryCheck.checked) {
            radioGroup.classList.add('show');
        } else {
            radioGroup.classList.remove('show');
        }
    }
}

// Ver sucursal
function verSucursal(id) {
    fetch(`${APP_URL}/admin/obtenerSucursal?id=${id}`)
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                const s = data.data;
                document.getElementById('detalleNombre').textContent = s.nombre_sucursal;
                document.getElementById('detalleCodigo').textContent = s.codigo_sucursal;
                document.getElementById('detalleCiudad').textContent = s.ciudad;
                document.getElementById('detalleDireccion').textContent = s.direccion_completa;
                document.getElementById('detalleTelefono').textContent = s.telefono || 'N/A';
                document.getElementById('detalleEmail').textContent = s.email || 'N/A';
                document.getElementById('detalleEncargado').textContent = s.encargado_nombre || 'Sin asignar';
                document.getElementById('detalleHorario').textContent = `${s.horario_apertura || 'N/A'} - ${s.horario_cierre || 'N/A'}`;
                document.getElementById('detalleDias').textContent = s.dias_atencion || 'N/A';
                document.getElementById('detalleCoordenadas').textContent = `${s.latitud}, ${s.longitud}`;
                document.getElementById('detalleCapacidad').textContent = s.capacidad_almacenamiento ? s.capacidad_almacenamiento + ' m³' : 'N/A';

                // Servicios
                let servicios = [];
                if (s.permite_delivery == 1) servicios.push(`<span class="badge-service delivery"><i class="fas fa-truck"></i> Delivery</span>`);
                if (s.permite_pickup == 1) servicios.push(`<span class="badge-service pickup"><i class="fas fa-store"></i> Pickup</span>`);
                document.getElementById('detalleServicios').innerHTML = servicios.join(' ') || 'Ninguno';

                // Estado
                const estadoBadge = s.activo == 1
                    ? '<span class="badge badge-success">Activo</span>'
                    : '<span class="badge badge-secondary">Inactivo</span>';
                document.getElementById('detalleEstado').innerHTML = estadoBadge;

                document.getElementById('modalDetalles').style.display = 'flex';
            } else {
                mostrarNotificacion('error', 'Error', data.message);
            }
        })
        .catch(err => mostrarNotificacion('error', 'Error', 'Error al cargar sucursal: ' + err));
}

// Editar sucursal
function editarSucursal(id) {
    fetch(`${APP_URL}/admin/obtenerSucursal?id=${id}`)
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                const s = data.data;
                document.getElementById('modalTitle').innerHTML = '<i class="fas fa-edit"></i> Editar Sucursal';
                document.getElementById('sucursalId').value = s.id_sucursal;
                document.getElementById('nombreSucursal').value = s.nombre_sucursal;
                document.getElementById('codigoSucursal').value = s.codigo_sucursal;
                document.getElementById('ciudad').value = s.ciudad;
                document.getElementById('departamento').value = s.departamento || '';
                document.getElementById('direccion').value = s.direccion_completa;
                document.getElementById('codigoPostal').value = s.codigo_postal || '';
                document.getElementById('latitud').value = s.latitud;
                document.getElementById('longitud').value = s.longitud;
                document.getElementById('telefono').value = s.telefono || '';
                document.getElementById('email').value = s.email || '';
                document.getElementById('capacidadAlmacenamiento').value = s.capacidad_almacenamiento || '';
                document.getElementById('horarioApertura').value = s.horario_apertura || '';
                document.getElementById('horarioCierre').value = s.horario_cierre || '';
                document.getElementById('diasAtencion').value = s.dias_atencion || '';
                document.getElementById('permiteDelivery').checked = s.permite_delivery == 1;
                document.getElementById('permitePickup').checked = s.permite_pickup == 1;
                document.getElementById('radioCobertura').value = s.radio_cobertura_km || '';
                document.getElementById('fechaApertura').value = s.fecha_apertura || '';
                document.getElementById('activo').value = s.activo;

                cargarEncargados();
                setTimeout(() => {
                    document.getElementById('encargado').value = s.id_encargado || '';
                }, 100);

                toggleRadioCobertura();
                document.getElementById('modalSucursal').style.display = 'flex';
            } else {
                mostrarNotificacion('error', 'Error', data.message);
            }
        })
        .catch(err => mostrarNotificacion('error', 'Error', 'Error al cargar sucursal: ' + err));
}

// Cambiar estado
function toggleEstadoSucursal(id, estadoActual) {
    const accion = estadoActual == 1 ? 'desactivar' : 'activar';
    if (!confirm(`¿Estás seguro de ${accion} esta sucursal?`)) return;

    const nuevoEstado = estadoActual == 1 ? 0 : 1;

    fetch(`${APP_URL}/admin/cambiarEstadoSucursal`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id_sucursal: id, nuevo_estado: nuevoEstado })
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

// Guardar sucursal
function guardarSucursal(e) {
    e.preventDefault();

    const formData = {
        id_sucursal: document.getElementById('sucursalId').value,
        codigo_sucursal: document.getElementById('codigoSucursal').value,
        nombre_sucursal: document.getElementById('nombreSucursal').value,
        id_encargado: document.getElementById('encargado').value || null,
        direccion_completa: document.getElementById('direccion').value,
        ciudad: document.getElementById('ciudad').value,
        departamento: document.getElementById('departamento').value,
        codigo_postal: document.getElementById('codigoPostal').value,
        latitud: document.getElementById('latitud').value,
        longitud: document.getElementById('longitud').value,
        telefono: document.getElementById('telefono').value,
        email: document.getElementById('email').value,
        capacidad_almacenamiento: document.getElementById('capacidadAlmacenamiento').value,
        horario_apertura: document.getElementById('horarioApertura').value,
        horario_cierre: document.getElementById('horarioCierre').value,
        dias_atencion: document.getElementById('diasAtencion').value,
        permite_delivery: document.getElementById('permiteDelivery').checked ? 1 : 0,
        permite_pickup: document.getElementById('permitePickup').checked ? 1 : 0,
        radio_cobertura_km: document.getElementById('radioCobertura').value,
        fecha_apertura: document.getElementById('fechaApertura').value,
        activo: document.getElementById('activo').value
    };

    // Validación básica
    if (!formData.nombre_sucursal || !formData.codigo_sucursal || !formData.ciudad || !formData.direccion_completa) {
        mostrarNotificacion('warning', 'Atención', 'Por favor completa los campos obligatorios (*)');
        return;
    }

    fetch(`${APP_URL}/admin/guardarSucursal`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
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
}

// Inicializar
document.addEventListener('DOMContentLoaded', function () {
    APP_URL = window.APP_URL;

    const searchInput = document.getElementById('searchInput');
    const filterCiudad = document.getElementById('filterCiudad');
    const filterEstado = document.getElementById('filterEstado');

    if (searchInput) searchInput.addEventListener('input', filtrarTabla);
    if (filterCiudad) filterCiudad.addEventListener('change', filtrarTabla);
    if (filterEstado) filterEstado.addEventListener('change', filtrarTabla);

    const formSucursal = document.getElementById('formSucursal');
    if (formSucursal) formSucursal.addEventListener('submit', guardarSucursal);

    const deliveryCheck = document.getElementById('permiteDelivery');
    if (deliveryCheck) deliveryCheck.addEventListener('change', toggleRadioCobertura);

    const modalSucursal = document.getElementById('modalSucursal');
    const modalDetalles = document.getElementById('modalDetalles');

    if (modalSucursal) modalSucursal.addEventListener('click', function (e) {
        if (e.target === this) cerrarModal();
    });

    if (modalDetalles) modalDetalles.addEventListener('click', function (e) {
        if (e.target === this) cerrarModalDetalles();
    });
});
