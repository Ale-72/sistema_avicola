/**
 * GESTIÓN DE PRODUCTOS - JavaScript
 * Sistema completo de CRUD para productos con notificaciones modales
 */

// Variable global para APP_URL
let APP_URL;

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

// Funciones de filtrado y búsqueda
function filtrarTabla() {
    const searchTerm = document.getElementById('searchInput').value.toLowerCase();
    const filterCategoria = document.getElementById('filterCategoria').value;
    const filterEstado = document.getElementById('filterEstado').value;
    const rows = document.querySelectorAll('#productosTable tbody tr');

    rows.forEach(row => {
        const nombre = row.cells[1]?.textContent.toLowerCase() || '';
        const codigo = row.cells[2]?.textContent.toLowerCase() || '';
        const categoria = row.dataset.categoria || '';
        const estado = row.dataset.estado || '';

        const matchSearch = nombre.includes(searchTerm) || codigo.includes(searchTerm);
        const matchCategoria = !filterCategoria || categoria === filterCategoria;
        const matchEstado = !filterEstado || estado === filterEstado;

        row.style.display = matchSearch && matchCategoria && matchEstado ? '' : 'none';
    });
}

// Funciones de modales
function abrirModalNuevoProducto() {
    document.getElementById('modalTitle').innerHTML = '<i class="fas fa-plus"></i> Nuevo Producto';
    document.getElementById('formProducto').reset();
    document.getElementById('productoId').value = '';
    document.getElementById('modalProducto').style.display = 'flex';

    // Cargar categorías
    cargarCategorias();
}

function cerrarModal() {
    document.getElementById('modalProducto').style.display = 'none';
}

function cerrarModalDetalles() {
    document.getElementById('modalDetalles').style.display = 'none';
}

// Cargar categorías desde el servidor
function cargarCategorias() {
    fetch(`${APP_URL}/admin/obtenerCategorias`)
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                const selectCategoria = document.getElementById('categoria');
                selectCategoria.innerHTML = '<option value="">Seleccionar categoría</option>';

                data.data.forEach(cat => {
                    const option = document.createElement('option');
                    option.value = cat.id_categoria_producto;
                    option.textContent = cat.nombre_categoria;
                    selectCategoria.appendChild(option);
                });
            }
        })
        .catch(err => console.error('Error al cargar categorías:', err));
}

// Ver detalles de producto
function verProducto(id) {
    fetch(`${APP_URL}/admin/obtenerProducto?id=${id}`)
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                const p = data.data;

                // Llenar el modal con los datos
                document.getElementById('detalleNombre').textContent = p.nombre_producto;
                document.getElementById('detalleCodigo').textContent = p.codigo_producto || 'N/A';
                document.getElementById('detalleCategoria').textContent = p.nombre_categoria || 'Sin categoría';
                document.getElementById('detallePrecio').textContent = 'Bs/ ' + parseFloat(p.precio_unitario).toFixed(2);

                if (p.precio_oferta && parseFloat(p.precio_oferta) > 0) {
                    document.getElementById('detallePrecioOferta').textContent = 'Bs/ ' + parseFloat(p.precio_oferta).toFixed(2);
                    document.getElementById('detallePrecioOferta').parentElement.style.display = 'block';
                } else {
                    document.getElementById('detallePrecioOferta').parentElement.style.display = 'none';
                }

                document.getElementById('detalleUnidad').textContent = p.unidad_medida;
                document.getElementById('detalleStock').textContent = p.stock_total;
                document.getElementById('detalleStockMinimo').textContent = p.stock_minimo;
                document.getElementById('detalleDescripcion').textContent = p.descripcion_corta || 'Sin descripción';

                // Badge de estado
                const estadoBadge = p.activo == 1
                    ? '<span class="badge badge-success">Activo</span>'
                    : '<span class="badge badge-secondary">Inactivo</span>';
                document.getElementById('detalleEstado').innerHTML = estadoBadge;

                // Badge de destacado
                const destacadoBadge = p.destacado == 1
                    ? '<span class="badge badge-warning">Destacado</span>'
                    : '<span class="badge badge-secondary">Normal</span>';
                document.getElementById('detalleDestacado').innerHTML = destacadoBadge;

                // Mostrar el modal
                document.getElementById('modalDetalles').style.display = 'flex';
            } else {
                mostrarNotificacion('error', 'Error', data.message);
            }
        })
        .catch(err => mostrarNotificacion('error', 'Error', 'Error al cargar producto: ' + err));
}

// Editar producto
function editarProducto(id) {
    fetch(`${APP_URL}/admin/obtenerProducto?id=${id}`)
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                const p = data.data;

                document.getElementById('modalTitle').innerHTML = '<i class="fas fa-edit"></i> Editar Producto';
                document.getElementById('productoId').value = p.id_producto;
                document.getElementById('nombreProducto').value = p.nombre_producto;
                document.getElementById('codigoProducto').value = p.codigo_producto || '';
                document.getElementById('precioUnitario').value = p.precio_unitario;
                document.getElementById('precioOferta').value = p.precio_oferta || '';
                document.getElementById('unidadMedida').value = p.unidad_medida;
                document.getElementById('stockTotal').value = p.stock_total;
                document.getElementById('stockMinimo').value = p.stock_minimo;
                document.getElementById('descripcionCorta').value = p.descripcion_corta || '';
                document.getElementById('destacado').checked = p.destacado == 1;
                document.getElementById('activo').value = p.activo;

                // Cargar categorías y luego seleccionar la actual
                cargarCategorias();
                setTimeout(() => {
                    document.getElementById('categoria').value = p.id_categoria_producto;
                }, 100);

                document.getElementById('modalProducto').style.display = 'flex';
            } else {
                mostrarNotificacion('error', 'Error', data.message);
            }
        })
        .catch(err => mostrarNotificacion('error', 'Error', 'Error al cargar producto: ' + err));
}

// Cambiar estado del producto (Activar/Desactivar)
function toggleEstadoProducto(id, estadoActual) {
    const accion = estadoActual == 1 ? 'desactivar' : 'activar';
    if (!confirm(`¿Estás seguro de ${accion} este producto?`)) {
        return;
    }

    const nuevoEstado = estadoActual == 1 ? 0 : 1;

    fetch(`${APP_URL}/admin/cambiarEstadoProducto`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            id_producto: id,
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

// Guardar producto (crear o actualizar)
function guardarProducto(e) {
    e.preventDefault();

    const formData = {
        id_producto: document.getElementById('productoId').value,
        nombre_producto: document.getElementById('nombreProducto').value,
        codigo_producto: document.getElementById('codigoProducto').value,
        id_categoria_producto: document.getElementById('categoria').value,
        descripcion_corta: document.getElementById('descripcionCorta').value,
        precio_unitario: document.getElementById('precioUnitario').value,
        precio_oferta: document.getElementById('precioOferta').value,
        unidad_medida: document.getElementById('unidadMedida').value,
        stock_total: document.getElementById('stockTotal').value,
        stock_minimo: document.getElementById('stockMinimo').value,
        destacado: document.getElementById('destacado').checked ? 1 : 0,
        activo: document.getElementById('activo').value
    };

    // Validación básica
    if (!formData.nombre_producto || !formData.precio_unitario) {
        mostrarNotificacion('warning', 'Atención', 'Por favor completa los campos obligatorios (*)');
        return;
    }

    if (!formData.id_categoria_producto) {
        mostrarNotificacion('warning', 'Atención', 'Por favor selecciona una categoría');
        return;
    }

    fetch(`${APP_URL}/admin/guardarProducto`, {
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
}

// Inicializar cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', function () {
    // Obtener APP_URL de la variable global definida en PHP
    APP_URL = window.APP_URL;

    // Event listeners para búsqueda y filtros
    const searchInput = document.getElementById('searchInput');
    const filterCategoria = document.getElementById('filterCategoria');
    const filterEstado = document.getElementById('filterEstado');

    if (searchInput) searchInput.addEventListener('input', filtrarTabla);
    if (filterCategoria) filterCategoria.addEventListener('change', filtrarTabla);
    if (filterEstado) filterEstado.addEventListener('change', filtrarTabla);

    // Event listener para el formulario
    const formProducto = document.getElementById('formProducto');
    if (formProducto) {
        formProducto.addEventListener('submit', guardarProducto);
    }

    // Cerrar modales al hacer clic fuera
    const modalProducto = document.getElementById('modalProducto');
    const modalDetalles = document.getElementById('modalDetalles');

    if (modalProducto) {
        modalProducto.addEventListener('click', function (e) {
            if (e.target === this) cerrarModal();
        });
    }

    if (modalDetalles) {
        modalDetalles.addEventListener('click', function (e) {
            if (e.target === this) cerrarModalDetalles();
        });
    }
});
