/**
 * REPORTES Y ANALÍTICAS - JavaScript
 * Integración con Chart.js para visualización de datos
 */

let APP_URL;
let ventasChart = null;
let productosChart = null;
let inventarioChart = null;

// Aplicar filtros de fecha
function aplicarFiltros() {
    const fechaDesde = document.getElementById('fechaDesde').value;
    const fechaHasta = document.getElementById('fechaHasta').value;

    if (!fechaDesde || !fechaHasta) {
        alert('Por favor selecciona ambas fechas');
        return;
    }

    cargarReporteVentas(fechaDesde, fechaHasta);
    cargarReporteProductos(fechaDesde, fechaHasta);
}

// Filtros rápidos
function filtroRapido(tipo) {
    const hoy = new Date();
    let fechaDesde, fechaHasta;

    fechaHasta = hoy.toISOString().split('T')[0];

    switch (tipo) {
        case 'hoy':
            fechaDesde = fechaHasta;
            break;
        case '7dias':
            fechaDesde = new Date(hoy.setDate(hoy.getDate() - 7)).toISOString().split('T')[0];
            break;
        case '30dias':
            fechaDesde = new Date(hoy.setDate(hoy.getDate() - 30)).toISOString().split('T')[0];
            break;
        case 'mes':
            fechaDesde = new Date(hoy.getFullYear(), hoy.getMonth(), 1).toISOString().split('T')[0];
            break;
    }

    document.getElementById('fechaDesde').value = fechaDesde;
    document.getElementById('fechaHasta').value = fechaHasta;

    // Marcar como activo
    document.querySelectorAll('.quickFilter').forEach(btn => btn.classList.remove('active'));
    event.target.classList.add('active');

    aplicarFiltros();
}

// Cargar reporte de ventas
function cargarReporteVentas(fechaDesde, fechaHasta) {
    fetch(`${APP_URL}/admin/reporteVentas?fecha_desde=${fechaDesde}&fecha_hasta=${fechaHasta}`)
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                actualizarGraficoVentas(data.ventas_diarias);
                actualizarTotalesVentas(data.totales);
            }
        })
        .catch(err => console.error('Error:', err));
}

// Crear/actualizar gráfico de ventas
function actualizarGraficoVentas(datos) {
    const labels = datos.map(d => new Date(d.fecha).toLocaleDateString('es-PE'));
    const values = datos.map(d => parseFloat(d.total_ventas) || 0);

    const ctx = document.getElementById('chartVentas').getContext('2d');

    if (ventasChart) {
        ventasChart.destroy();
    }

    ventasChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Ventas (S/)',
                data: values,
                borderColor: '#2ecc71',
                backgroundColor: 'rgba(46, 204, 113, 0.1)',
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { color: 'rgba(255,255,255,0.7)' },
                    grid: { color: 'rgba(255,255,255,0.1)' }
                },
                x: {
                    ticks: { color: 'rgba(255,255,255,0.7)' },
                    grid: { color: 'rgba(255,255,255,0.1)' }
                }
            }
        }
    });
}

// Actualizar totales de ventas
function actualizarTotalesVentas(totales) {
    document.getElementById('totalPedidos').textContent = totales.total_pedidos || 0;
    document.getElementById('totalVentas').textContent = 'S/ ' + (parseFloat(totales.total_ventas) || 0).toFixed(2);
    document.getElementById('ticketPromedio').textContent = 'S/ ' + (parseFloat(totales.ticket_promedio) || 0).toFixed(2);
}

// Cargar top productos
function cargarReporteProductos(fechaDesde, fechaHasta) {
    fetch(`${APP_URL}/admin/reporteProductos?fecha_desde=${fechaDesde}&fecha_hasta=${fechaHasta}&limite=5`)
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                actualizarGraficoProductos(data.productos);
                actualizarTablaProductos(data.productos);
            }
        })
        .catch(err => console.error('Error:', err));
}

// Crear/actualizar gráfico de productos
function actualizarGraficoProductos(productos) {
    const labels = productos.map(p => p.nombre_producto);
    const values = productos.map(p => parseInt(p.total_vendido) || 0);

    const ctx = document.getElementById('chartProductos').getContext('2d');

    if (productosChart) {
        productosChart.destroy();
    }

    productosChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Unidades Vendidas',
                data: values,
                backgroundColor: [
                    'rgba(52, 152, 219, 0.8)',
                    'rgba(155, 89, 182, 0.8)',
                    'rgba(46, 204, 113, 0.8)',
                    'rgba(243, 156, 18, 0.8)',
                    'rgba(231, 76, 60, 0.8)'
                ]
            }]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            },
            scales: {
                x: {
                    beginAtZero: true,
                    ticks: { color: 'rgba(255,255,255,0.7)' },
                    grid: { color: 'rgba(255,255,255,0.1)' }
                },
                y: {
                    ticks: { color: 'rgba(255,255,255,0.7)' },
                    grid: { display: false }
                }
            }
        }
    });
}

// Actualizar tabla de productos
function actualizarTablaProductos(productos) {
    const tbody = document.getElementById('tablaProductos');
    tbody.innerHTML = '';

    productos.forEach((prod, index) => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${index + 1}</td>
            <td>
                <div style="font-weight: 600;">${prod.nombre_producto}</div>
                <div style="font-size: 0.8rem; color: rgba(255,255,255,0.6);">${prod.codigo_producto}</div>
            </td>
            <td><strong>${prod.total_vendido}</strong></td>
            <td>S/ ${parseFloat(prod.ingresos_generados).toFixed(2)}</td>
            <td><span style="color: #2ecc71; font-weight: 600;">${parseFloat(prod.porcentaje).toFixed(1)}%</span></td>
        `;
        tbody.appendChild(row);
    });
}

// Cargar reporte de inventario
function cargarReporteInventario() {
    fetch(`${APP_URL}/admin/reporteInventario`)
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                actualizarGraficoInventario(data.resumen);
            }
        })
        .catch(err => console.error('Error:', err));
}

// Crear gráfico de inventario (dona)
function actualizarGraficoInventario(resumen) {
    const ctx = document.getElementById('chartInventario').getContext('2d');

    if (inventarioChart) {
        inventarioChart.destroy();
    }

    inventarioChart = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ['Normal', 'Bajo', 'Crítico'],
            datasets: [{
                data: [
                    parseInt(resumen.normal) || 0,
                    parseInt(resumen.bajo) || 0,
                    parseInt(resumen.critico) || 0
                ],
                backgroundColor: [
                    'rgba(46, 204, 113, 0.8)',
                    'rgba(243, 156, 18, 0.8)',
                    'rgba(231, 76, 60, 0.8)'
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: { color: 'rgba(255,255,255,0.9)' }
                }
            }
        }
    });
}

// Exportar a PDF (impresión)
function exportarPDF() {
    window.print();
}

// Exportar a Excel (CSV)
function exportarExcel() {
    // Obtener datos de la tabla de productos
    const tabla = document.getElementById('tablaProductos');
    let csv = 'Puesto,Producto,Código,Cantidad Vendida,Ingresos,% del Total\n';

    tabla.querySelectorAll('tr').forEach(row => {
        const cells = row.querySelectorAll('td');
        const rowData = Array.from(cells).map(cell => cell.textContent.trim()).join(',');
        csv += rowData + '\n';
    });

    // Descargar
    const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    const url = URL.createObjectURL(blob);
    link.setAttribute('href', url);
    link.setAttribute('download', 'reporte_productos_' + new Date().toISOString().split('T')[0] + '.csv');
    link.click();
}

// Inicialización
document.addEventListener('DOMContentLoaded', function () {
    APP_URL = window.APP_URL;

    // Establecer fechas por defecto (últimos 30 días)
    const hoy = new Date();
    const hace30 = new Date(hoy.setDate(hoy.getDate() - 30));

    document.getElementById('fechaDesde').value = hace30.toISOString().split('T')[0];
    document.getElementById('fechaHasta').value = new Date().toISOString().split('T')[0];

    // Cargar datos iniciales
    cargarReporteVentas(
        document.getElementById('fechaDesde').value,
        document.getElementById('fechaHasta').value
    );
    cargarReporteProductos(
        document.getElementById('fechaDesde').value,
        document.getElementById('fechaHasta').value
    );
    cargarReporteInventario();

    // Event listeners
    document.getElementById('btnAplicarFiltros')?.addEventListener('click', aplicarFiltros);
});
