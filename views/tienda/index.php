<?php require_once ROOT_PATH . '/views/layouts/header.php'; ?>

<div class="container">
    <!-- Encabezado y filtros -->
    <div class="shop-header">
        <h1><i class="fas fa-store"></i> Tienda AVITECH</h1>
        <p class="text-secondary">Encuentra los mejores productos para tu granja</p>
    </div>

    <!-- Barra de búsqueda y filtros -->
    <div class="shop-filters glass card">
        <form method="GET" action="<?php echo APP_URL; ?>/tienda" class="filter-form">
            <div class="row">
                <div class="col-4">
                    <div class="form-group">
                        <label><i class="fas fa-search"></i> Buscar</label>
                        <input type="text" name="q" class="form-control" placeholder="Buscar productos..."
                            value="<?php echo htmlspecialchars($filtros_actuales['busqueda']); ?>">
                    </div>
                </div>

                <div class="col-3">
                    <div class="form-group">
                        <label><i class="fas fa-tag"></i> Categoría</label>
                        <select name="categoria" class="form-control">
                            <option value="">Todas las categorías</option>
                            <?php foreach ($categorias as $cat): ?>
                                <option value="<?php echo $cat['id_categoria_producto']; ?>"
                                    <?php echo $filtros_actuales['categoria'] == $cat['id_categoria_producto'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($cat['nombre_categoria']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="col-3">
                    <div class="form-group">
                        <label><i class="fas fa-sort"></i> Ordenar por</label>
                        <select name="orden" class="form-control">
                            <option value="recientes" <?php echo $filtros_actuales['orden'] == 'recientes' ? 'selected' : ''; ?>>Más recientes</option>
                            <option value="precio_asc" <?php echo $filtros_actuales['orden'] == 'precio_asc' ? 'selected' : ''; ?>>Precio: Menor a Mayor</option>
                            <option value="precio_desc" <?php echo $filtros_actuales['orden'] == 'precio_desc' ? 'selected' : ''; ?>>Precio: Mayor a Menor</option>
                            <option value="nombre" <?php echo $filtros_actuales['orden'] == 'nombre' ? 'selected' : ''; ?>>Nombre A-Z</option>
                        </select>
                    </div>
                </div>

                <div class="col-2">
                    <div class="form-group">
                        <label>&nbsp;</label>
                        <button type="submit" class="btn btn-primary w-full">
                            <i class="fas fa-filter"></i> Filtrar
                        </button>
                    </div>
                </div>
            </div>

            <!-- Filtro de rango de precio -->
            <div class="row mt-2">
                <div class="col-3">
                    <input type="number" name="precio_min" class="form-control" placeholder="Precio mínimo"
                        value="<?php echo $filtros_actuales['precio_min']; ?>" step="0.01">
                </div>
                <div class="col-3">
                    <input type="number" name="precio_max" class="form-control" placeholder="Precio máximo"
                        value="<?php echo $filtros_actuales['precio_max']; ?>" step="0.01">
                </div>
            </div>
        </form>
    </div>

    <!-- Grid de productos -->
    <?php if (!empty($productos)): ?>
        <div class="products-grid">
            <?php foreach ($productos as $producto): ?>
                <div class="product-card glass card animate-on-scroll">
                    <div class="product-image">
                        <?php if ($producto['imagen_principal']): ?>
                            <img src="<?php echo APP_URL . '/uploads/' . $producto['imagen_principal']; ?>"
                                alt="<?php echo htmlspecialchars($producto['nombre_producto']); ?>">
                        <?php else: ?>
                            <div class="no-image">
                                <i class="fas fa-box"></i>
                            </div>
                        <?php endif; ?>

                        <?php if ($producto['precio_oferta']): ?>
                            <span class="badge badge-danger product-badge">¡OFERTA!</span>
                        <?php endif; ?>
                        <?php if ($producto['destacado']): ?>
                            <span class="badge badge-success product-badge" style="top: 3.5rem;">Destacado</span>
                        <?php endif; ?>
                    </div>

                    <div class="product-body">
                        <div class="product-category">
                            <i class="fas fa-tag"></i> <?php echo htmlspecialchars($producto['nombre_categoria']); ?>
                        </div>
                        <h3 class="product-title">
                            <a href="<?php echo APP_URL . '/tienda/detalle/' . $producto['slug']; ?>">
                                <?php echo htmlspecialchars($producto['nombre_producto']); ?>
                            </a>
                        </h3>
                        <p class="product-description">
                            <?php echo htmlspecialchars(substr($producto['descripcion_corta'] ?? 'Producto de calidad premium', 0, 100)) . '...'; ?>
                        </p>

                        <div class="product-footer">
                            <div class="product-price">
                                <?php if ($producto['precio_oferta']): ?>
                                    <span class="price-old">S/ <?php echo number_format($producto['precio_unitario'], 2); ?></span>
                                    <span class="price-current">S/ <?php echo number_format($producto['precio_oferta'], 2); ?></span>
                                    <span class="discount-badge">
                                        -<?php echo round((($producto['precio_unitario'] - $producto['precio_oferta']) / $producto['precio_unitario']) * 100); ?>%
                                    </span>
                                <?php else: ?>
                                    <span class="price-current">S/ <?php echo number_format($producto['precio_unitario'], 2); ?></span>
                                <?php endif; ?>
                            </div>

                            <div class="product-actions">
                                <a href="<?php echo APP_URL . '/tienda/detalle/' . $producto['slug']; ?>"
                                    class="btn btn-primary btn-sm">
                                    <i class="fas fa-eye"></i> Ver
                                </a>
                                <button class="btn btn-outline btn-sm add-to-cart"
                                    data-id="<?php echo $producto['id_producto']; ?>">
                                    <i class="fas fa-shopping-cart"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="empty-state glass card">
            <i class="fas fa-search fa-3x text-muted"></i>
            <h3>No se encontraron productos</h3>
            <p>Intenta ajustar los filtros de búsqueda</p>
            <a href="<?php echo APP_URL; ?>/tienda" class="btn btn-primary">
                <i class="fas fa-sync"></i> Ver todos los productos
            </a>
        </div>
    <?php endif; ?>
</div>

<style>
    .shop-header {
        text-align: center;
        margin: 3rem 0 2rem;
    }

    .shop-header h1 {
        font-size: 2.5rem;
        margin-bottom: 0.5rem;
    }

    .shop-filters {
        padding: 2rem;
        margin-bottom: 2rem;
    }

    .filter-form .form-group {
        margin-bottom: 0;
    }

    .filter-form label {
        font-size: 0.875rem;
        font-weight: 600;
        margin-bottom: 0.5rem;
        display: block;
    }

    .products-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 2rem;
        margin-bottom: 3rem;
    }

    .product-card {
        overflow: hidden;
        transition: all var(--transition-base);
    }

    .product-card:hover {
        transform: translateY(-8px);
        box-shadow: var(--shadow-xl), var(--shadow-glow);
    }

    .product-image {
        position: relative;
        height: 250px;
        overflow: hidden;
        background: linear-gradient(135deg, rgba(46, 204, 113, 0.1), rgba(52, 152, 219, 0.1));
    }

    .product-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform var(--transition-slow);
    }

    .product-card:hover .product-image img {
        transform: scale(1.1);
    }

    .no-image {
        display: flex;
        align-items: center;
        justify-content: center;
        height: 100%;
        color: var(--color-text-muted);
        font-size: 4rem;
    }

    .product-badge {
        position: absolute;
        top: 1rem;
        right: 1rem;
        font-size: 0.75rem;
        font-weight: 700;
        animation: pulse 2s ease-in-out infinite;
    }

    .product-body {
        padding: 1.5rem;
    }

    .product-category {
        font-size: 0.85rem;
        color: var(--color-primary);
        margin-bottom: 0.5rem;
        font-weight: 600;
    }

    .product-title {
        font-size: 1.25rem;
        margin-bottom: 0.75rem;
        min-height: 3rem;
    }

    .product-title a {
        color: var(--color-text-primary);
        text-decoration: none;
        transition: color var(--transition-base);
    }

    .product-title a:hover {
        color: var(--color-primary);
    }

    .product-description {
        font-size: 0.9rem;
        color: var(--color-text-secondary);
        margin-bottom: 1.5rem;
        min-height: 4rem;
    }

    .product-footer {
        border-top: 1px solid rgba(255, 255, 255, 0.1);
        padding-top: 1rem;
    }

    .product-price {
        margin-bottom: 1rem;
        display: flex;
        align-items: center;
        gap: 0.75rem;
        flex-wrap: wrap;
    }

    .price-old {
        text-decoration: line-through;
        color: var(--color-text-muted);
        font-size: 0.9rem;
    }

    .price-current {
        font-size: 1.75rem;
        font-weight: 700;
        color: var(--color-primary);
    }

    .discount-badge {
        background: var(--color-danger);
        color: white;
        padding: 0.25rem 0.5rem;
        border-radius: var(--radius-sm);
        font-size: 0.75rem;
        font-weight: 700;
    }

    .product-actions {
        display: flex;
        gap: 0.5rem;
    }

    .product-actions .btn {
        flex: 1;
    }

    .empty-state {
        text-align: center;
        padding: 4rem 2rem;
    }

    .empty-state i {
        margin-bottom: 1.5rem;
        opacity: 0.3;
    }

    .empty-state h3 {
        margin-bottom: 0.5rem;
    }

    @media (max-width: 768px) {
        .products-grid {
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 1.5rem;
        }

        .filter-form .col-2,
        .filter-form .col-3,
        .filter-form .col-4 {
            flex: 0 0 100%;
            max-width: 100%;
        }

        .filter-form .row {
            gap: 1rem;
        }
    }
</style>

<script>
    // Agregar al carrito
    document.querySelectorAll('.add-to-cart').forEach(btn => {
        btn.addEventListener('click', async function() {
            const productId = this.dataset.id;

            try {
                const response = await fetch('<?php echo APP_URL; ?>/tienda/agregarCarrito', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `id_producto=${productId}&cantidad=1&id_sucursal=1`
                });

                const result = await response.json();

                if (result.success) {
                    // Actualizar contador
                    document.getElementById('cartCount').textContent = result.total_items;

                    // Mostrar notificación
                    window.utils?.showNotification('Producto agregado al carrito', 'success');

                    // Animación del botón
                    this.innerHTML = '<i class="fas fa-check"></i>';
                    setTimeout(() => {
                        this.innerHTML = '<i class="fas fa-shopping-cart"></i>';
                    }, 2000);
                } else {
                    window.utils?.showNotification(result.error || 'Error al agregar', 'danger');
                }
            } catch (error) {
                window.utils?.showNotification('Error de conexión', 'danger');
            }
        });
    });
</script>

<?php require_once ROOT_PATH . '/views/layouts/footer.php'; ?>