<?php require_once ROOT_PATH . '/views/layouts/header.php'; ?>

<div class="container">
    <nav class="breadcrumb">
        <a href="<?php echo APP_URL; ?>/">Inicio</a>
        <span>/</span>
        <a href="<?php echo APP_URL; ?>/tienda">Tienda</a>
        <span>/</span>
        <a href="<?php echo APP_URL; ?>/tienda?categoria=<?php echo $producto['id_categoria_producto']; ?>">
            <?php echo $producto['nombre_categoria']; ?>
        </a>
        <span>/</span>
        <span><?php echo htmlspecialchars($producto['nombre_producto']); ?></span>
    </nav>

    <div class="product-detail">
        <div class="row">
            <!-- Galería de imágenes -->
            <div class="col-6">
                <div class="product-gallery glass card">
                    <div class="main-image">
                        <?php
                        $imagen_principal = !empty($imagenes) ? $imagenes[0]['url_imagen'] : 'placeholder.jpg';
                        ?>
                        <img id="mainImage" src="<?php echo APP_URL . '/uploads/' . $imagen_principal; ?>"
                            alt="<?php echo htmlspecialchars($producto['nombre_producto']); ?>">
                    </div>

                    <?php if (count($imagenes) > 1): ?>
                        <div class="thumbnail-list">
                            <?php foreach ($imagenes as $img): ?>
                                <div class="thumbnail" onclick="changeImage('<?php echo APP_URL . '/uploads/' . $img['url_imagen']; ?>')">
                                    <img src="<?php echo APP_URL . '/uploads/' . $img['url_imagen']; ?>"
                                        alt="Miniatura">
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Información del producto -->
            <div class="col-6">
                <div class="product-info glass card">
                    <div class="product-header">
                        <span class="product-category">
                            <i class="fas fa-tag"></i> <?php echo $producto['nombre_categoria']; ?>
                        </span>
                        <h1><?php echo htmlspecialchars($producto['nombre_producto']); ?></h1>
                        <p class="product-code">Código: <?php echo $producto['codigo_producto']; ?></p>
                    </div>

                    <div class="product-price-section">
                        <?php if ($producto['precio_oferta']): ?>
                            <div class="price-old">S/ <?php echo number_format($producto['precio_unitario'], 2); ?></div>
                            <div class="price-current">S/ <?php echo number_format($producto['precio_oferta'], 2); ?></div>
                            <div class="discount-badge">
                                ¡Ahorra <?php echo round((($producto['precio_unitario'] - $producto['precio_oferta']) / $producto['precio_unitario']) * 100); ?>%!
                            </div>
                        <?php else: ?>
                            <div class="price-current">S/ <?php echo number_format($producto['precio_unitario'], 2); ?></div>
                        <?php endif; ?>
                        <div class="unit-measure">por <?php echo $producto['unidad_medida']; ?></div>
                    </div>

                    <div class="product-description">
                        <h3><i class="fas fa-align-left"></i> Descripción</h3>
                        <p><?php echo nl2br(htmlspecialchars($producto['descripcion_larga'] ?? $producto['descripcion_corta'])); ?></p>
                    </div>

                    <?php if (!empty($atributos)): ?>
                        <div class="product-attributes">
                            <h3><i class="fas fa-list-ul"></i> Especificaciones</h3>
                            <table class="attributes-table">
                                <?php
                                $grupos = [];
                                foreach ($atributos as $attr) {
                                    $grupos[$attr['grupo_atributo']][] = $attr;
                                }
                                foreach ($grupos as $grupo => $attrs): ?>
                                    <tr class="group-header">
                                        <td colspan="2"><strong><?php echo ucfirst($grupo); ?></strong></td>
                                    </tr>
                                    <?php foreach ($attrs as $attr): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($attr['nombre_atributo']); ?></td>
                                            <td><?php echo htmlspecialchars($attr['valor_atributo']); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endforeach; ?>
                            </table>
                        </div>
                    <?php endif; ?>

                    <!-- Stock por sucursal -->
                    <div class="product-stock">
                        <h3><i class="fas fa-warehouse"></i> Disponibilidad por Sucursal</h3>
                        <?php if (!empty($stock_sucursales)): ?>
                            <div class="stock-list">
                                <?php foreach ($stock_sucursales as $stock): ?>
                                    <div class="stock-item">
                                        <div class="stock-info">
                                            <strong><?php echo $stock['nombre_sucursal']; ?></strong>
                                            <small><?php echo $stock['ciudad']; ?></small>
                                        </div>
                                        <div class="stock-quantity <?php echo $stock['cantidad_disponible'] > 0 ? 'in-stock' : 'out-stock'; ?>">
                                            <?php if ($stock['cantidad_disponible'] > 0): ?>
                                                <i class="fas fa-check-circle"></i>
                                                <?php echo $stock['cantidad_disponible']; ?> disponibles
                                            <?php else: ?>
                                                <i class="fas fa-times-circle"></i> Agotado
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <p class="text-muted">No hay stock disponible actualmente</p>
                        <?php endif; ?>
                    </div>

                    <!-- Formulario de compra -->
                    <form id="addToCartForm" class="purchase-form">
                        <div class="row">
                            <div class="col-6">
                                <label class="form-label">Sucursal</label>
                                <select name="id_sucursal" id="sucursal_select" class="form-control" required>
                                    <option value="">Selecciona sucursal</option>
                                    <?php foreach ($stock_sucursales as $stock): ?>
                                        <?php if ($stock['cantidad_disponible'] > 0): ?>
                                            <option value="<?php echo $stock['id_sucursal']; ?>"
                                                data-stock="<?php echo $stock['cantidad_disponible']; ?>">
                                                <?php echo $stock['nombre_sucursal']; ?> (<?php echo $stock['cantidad_disponible']; ?> disp.)
                                            </option>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-6">
                                <label class="form-label">Cantidad</label>
                                <div class="quantity-selector">
                                    <button type="button" class="qty-btn" onclick="decreaseQty()">-</button>
                                    <input type="number" name="cantidad" id="quantity" class="form-control"
                                        value="1" min="1" max="100" required>
                                    <button type="button" class="qty-btn" onclick="increaseQty()">+</button>
                                </div>
                            </div>
                        </div>

                        <div class="purchase-actions">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-shopping-cart"></i> Agregar al Carrito
                            </button>
                            <button type="button" class="btn btn-outline btn-lg">
                                <i class="fas fa-heart"></i> Guardar
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Productos relacionados -->
        <?php if (!empty($relacionados)): ?>
            <div class="related-products">
                <h2><i class="fas fa-boxes"></i> Productos Relacionados</h2>
                <div class="products-grid">
                    <?php foreach ($relacionados as $rel): ?>
                        <div class="product-card glass card">
                            <div class="product-image">
                                <?php if ($rel['imagen_principal']): ?>
                                    <img src="<?php echo APP_URL . '/uploads/' . $rel['imagen_principal']; ?>"
                                        alt="<?php echo htmlspecialchars($rel['nombre_producto']); ?>">
                                <?php else: ?>
                                    <div class="no-image"><i class="fas fa-box"></i></div>
                                <?php endif; ?>
                            </div>
                            <div class="product-body">
                                <h4><?php echo htmlspecialchars($rel['nombre_producto']); ?></h4>
                                <div class="product-price">
                                    <span class="price-current">S/ <?php echo number_format($rel['precio_unitario'], 2); ?></span>
                                </div>
                                <a href="<?php echo APP_URL . '/tienda/detalle/' . $rel['slug']; ?>"
                                    class="btn btn-outline btn-sm w-full">
                                    Ver detalle
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
    .breadcrumb {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        padding: 1.5rem 0;
        font-size: 0.9rem;
    }

    .breadcrumb a {
        color: var(--color-primary);
        text-decoration: none;
    }

    .breadcrumb a:hover {
        text-decoration: underline;
    }

    .breadcrumb span:not(:last-child) {
        color: var(--color-text-muted);
    }

    .product-detail {
        margin-bottom: 4rem;
    }

    .product-gallery {
        padding: 2rem;
        position: sticky;
        top: 100px;
    }

    .main-image {
        width: 100%;
        height: 500px;
        border-radius: var(--radius-lg);
        overflow: hidden;
        margin-bottom: 1.5rem;
        background: rgba(255, 255, 255, 0.03);
    }

    .main-image img {
        width: 100%;
        height: 100%;
        object-fit: contain;
    }

    .thumbnail-list {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(80px, 1fr));
        gap: 1rem;
    }

    .thumbnail {
        height: 80px;
        border-radius: var(--radius-md);
        overflow: hidden;
        cursor: pointer;
        border: 2px solid transparent;
        transition: all var(--transition-base);
    }

    .thumbnail:hover {
        border-color: var(--color-primary);
    }

    .thumbnail img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .product-info {
        padding: 2.5rem;
    }

    .product-header {
        margin-bottom: 2rem;
        padding-bottom: 2rem;
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    }

    .product-category {
        display: inline-block;
        color: var(--color-primary);
        font-size: 0.9rem;
        margin-bottom: 0.75rem;
    }

    .product-header h1 {
        font-size: 2rem;
        margin-bottom: 0.5rem;
    }

    .product-code {
        color: var(--color-text-muted);
        font-size: 0.875rem;
    }

    .product-price-section {
        display: flex;
        align-items: baseline;
        gap: 1rem;
        flex-wrap: wrap;
        margin-bottom: 2rem;
        padding: 1.5rem;
        background: linear-gradient(135deg, rgba(46, 204, 113, 0.1), rgba(52, 152, 219, 0.1));
        border-radius: var(--radius-lg);
    }

    .price-old {
        text-decoration: line-through;
        color: var(--color-text-muted);
        font-size: 1.25rem;
    }

    .price-current {
        font-size: 2.5rem;
        font-weight: 700;
        color: var(--color-primary);
    }

    .discount-badge {
        background: var(--color-danger);
        color: white;
        padding: 0.5rem 1rem;
        border-radius: var(--radius-full);
        font-weight: 700;
        font-size: 0.875rem;
    }

    .unit-measure {
        color: var(--color-text-secondary);
        font-size: 0.9rem;
    }

    .product-description,
    .product-attributes,
    .product-stock {
        margin-bottom: 2rem;
    }

    .product-info h3 {
        font-size: 1.25rem;
        margin-bottom: 1rem;
        color: var(--color-primary);
    }

    .attributes-table {
        width: 100%;
        font-size: 0.9rem;
    }

    .attributes-table tr {
        border-bottom: 1px solid rgba(255, 255, 255, 0.05);
    }

    .attributes-table td {
        padding: 0.75rem 0.5rem;
    }

    .attributes-table td:first-child {
        color: var(--color-text-secondary);
        width: 40%;
    }

    .group-header td {
        background: rgba(255, 255, 255, 0.03);
        padding: 0.5rem;
    }

    .stock-list {
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
    }

    .stock-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 1rem;
        background: rgba(255, 255, 255, 0.03);
        border-radius: var(--radius-md);
    }

    .stock-info small {
        display: block;
        color: var(--color-text-muted);
        font-size: 0.8rem;
    }

    .stock-quantity {
        font-weight: 600;
    }

    .in-stock {
        color: var(--color-success);
    }

    .out-stock {
        color: var(--color-danger);
    }

    .purchase-form {
        margin-top: 2rem;
        padding-top: 2rem;
        border-top: 2px solid var(--color-primary);
    }

    .quantity-selector {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .quantity-selector input {
        text-align: center;
        flex: 1;
    }

    .qty-btn {
        width: 40px;
        height: 40px;
        border: 2px solid var(--color-primary);
        background: transparent;
        color: var(--color-primary);
        border-radius: var(--radius-md);
        cursor: pointer;
        font-size: 1.25rem;
        transition: all var(--transition-base);
    }

    .qty-btn:hover {
        background: var(--color-primary);
        color: white;
    }

    .purchase-actions {
        display: flex;
        gap: 1rem;
        margin-top: 1.5rem;
    }

    .purchase-actions .btn {
        flex: 1;
    }

    .related-products {
        margin-top: 4rem;
    }

    .related-products h2 {
        margin-bottom: 2rem;
        text-align: center;
    }

    .related-products .products-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
        gap: 1.5rem;
    }

    .related-products .product-card {
        padding: 0;
    }

    .related-products .product-image {
        height: 200px;
        background: rgba(255, 255, 255, 0.03);
    }

    .related-products .product-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .related-products .product-body {
        padding: 1.5rem;
    }

    .related-products .product-body h4 {
        font-size: 1rem;
        margin-bottom: 0.75rem;
        min-height: 2.5rem;
    }
</style>

<script>
    function changeImage(src) {
        document.getElementById('mainImage').src = src;
    }

    function increaseQty() {
        const input = document.getElementById('quantity');
        const max = parseInt(input.max);
        const current = parseInt(input.value);
        if (current < max) {
            input.value = current + 1;
        }
    }

    function decreaseQty() {
        const input = document.getElementById('quantity');
        const current = parseInt(input.value);
        if (current > 1) {
            input.value = current - 1;
        }
    }

    // Actualizar max según sucursal
    document.getElementById('sucursal_select')?.addEventListener('change', function() {
        const selected = this.options[this.selectedIndex];
        const stock = selected.dataset.stock;
        const qtyInput = document.getElementById('quantity');
        qtyInput.max = stock;
        if (parseInt(qtyInput.value) > parseInt(stock)) {
            qtyInput.value = stock;
        }
    });

    // Agregar al carrito
    document.getElementById('addToCartForm')?.addEventListener('submit', async function(e) {
        e.preventDefault();

        const formData = new FormData();
        formData.append('id_producto', <?php echo $producto['id_producto']; ?>);
        formData.append('cantidad', document.getElementById('quantity').value);
        formData.append('id_sucursal', document.getElementById('sucursal_select').value);

        try {
            const response = await fetch('<?php echo APP_URL; ?>/tienda/agregarCarrito', {
                method: 'POST',
                body: formData
            });

            const result = await response.json();

            if (result.success) {
                document.getElementById('cartCount').textContent = result.total_items;
                window.utils?.showNotification('¡Producto agregado al carrito!', 'success');
            } else {
                window.utils?.showNotification(result.error || 'Error al agregar', 'danger');
            }
        } catch (error) {
            window.utils?.showNotification('Error de conexión', 'danger');
        }
    });
</script>

<?php require_once ROOT_PATH . '/views/layouts/footer.php'; ?>