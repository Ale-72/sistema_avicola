<?php require_once ROOT_PATH . '/views/layouts/header.php'; ?>

<div class="container">
    <div class="cart-header">
        <h1><i class="fas fa-shopping-cart"></i> Carrito de Compras</h1>
        <p class="text-secondary">Revisa tus productos antes de continuar con la compra</p>
    </div>

    <?php if (!empty($carrito)): ?>
        <div class="row">
            <!-- Lista de productos -->
            <div class="col-8">
                <div class="cart-items glass card">
                    <div class="cart-table">
                        <table>
                            <thead>
                                <tr>
                                    <th>Producto</th>
                                    <th>Precio</th>
                                    <th>Cantidad</th>
                                    <th>Subtotal</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($carrito as $key => $item): ?>
                                    <tr class="cart-item" data-key="<?php echo $key; ?>">
                                        <td>
                                            <div class="item-info">
                                                <?php if ($item['imagen']): ?>
                                                    <img src="<?php echo APP_URL . '/uploads/' . $item['imagen']; ?>"
                                                        alt="<?php echo htmlspecialchars($item['nombre']); ?>"
                                                        class="item-image">
                                                <?php else: ?>
                                                    <div class="item-image-placeholder">
                                                        <i class="fas fa-box"></i>
                                                    </div>
                                                <?php endif; ?>
                                                <div class="item-details">
                                                    <strong><?php echo htmlspecialchars($item['nombre']); ?></strong>
                                                    <small>Sucursal: #<?php echo $item['id_sucursal']; ?></small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="item-price">S/ <?php echo number_format($item['precio'], 2); ?></span>
                                        </td>
                                        <td>
                                            <div class="quantity-control">
                                                <button class="qty-btn" onclick="updateQuantity('<?php echo $key; ?>', -1)">
                                                    <i class="fas fa-minus"></i>
                                                </button>
                                                <input type="number" class="qty-input" value="<?php echo $item['cantidad']; ?>"
                                                    min="1" max="100" readonly>
                                                <button class="qty-btn" onclick="updateQuantity('<?php echo $key; ?>', 1)">
                                                    <i class="fas fa-plus"></i>
                                                </button>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="item-subtotal">S/ <?php echo number_format($item['subtotal'], 2); ?></span>
                                        </td>
                                        <td>
                                            <button class="btn-remove" onclick="removeItem('<?php echo $key; ?>')"
                                                title="Eliminar producto">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="cart-actions">
                        <a href="<?php echo APP_URL; ?>/tienda" class="btn btn-outline">
                            <i class="fas fa-arrow-left"></i> Continuar comprando
                        </a>
                        <button onclick="clearCart()" class="btn btn-danger-outline">
                            <i class="fas fa-trash"></i> Vaciar carrito
                        </button>
                    </div>
                </div>
            </div>

            <!-- Resumen de compra -->
            <div class="col-4">
                <div class="cart-summary glass card sticky-summary">
                    <h3><i class="fas fa-receipt"></i> Resumen de Compra</h3>

                    <div class="summary-items">
                        <div class="summary-row">
                            <span>Subtotal:</span>
                            <strong id="subtotal">S/ <?php echo number_format($total, 2); ?></strong>
                        </div>
                        <div class="summary-row">
                            <span>Envío:</span>
                            <strong id="shipping">Por calcular</strong>
                        </div>
                        <div class="summary-row total-row">
                            <span>Total:</span>
                            <strong id="total">S/ <?php echo number_format($total, 2); ?></strong>
                        </div>
                    </div>

                    <?php if (Session::isAuthenticated()): ?>
                        <div class="delivery-options">
                            <h4><i class="fas fa-truck"></i> Método de entrega</h4>
                            <label class="radio-option">
                                <input type="radio" name="metodo_entrega" value="delivery" checked>
                                <span class="radio-custom"></span>
                                <span class="radio-label">
                                    <strong>Delivery</strong>
                                    <small>Entrega a domicilio</small>
                                </span>
                            </label>
                            <label class="radio-option">
                                <input type="radio" name="metodo_entrega" value="pickup">
                                <span class="radio-custom"></span>
                                <span class="radio-label">
                                    <strong>Pick-up</strong>
                                    <small>Recoger en sucursal</small>
                                </span>
                            </label>
                        </div>

                        <button class="btn btn-primary btn-lg w-full" id="checkout_btn">
                            <i class="fas fa-credit-card"></i> Proceder al Pago
                        </button>
                    <?php else: ?>
                        <div class="auth-reminder">
                            <i class="fas fa-info-circle"></i>
                            <p>Debes iniciar sesión para continuar con la compra</p>
                        </div>
                        <a href="<?php echo APP_URL; ?>/auth/login?redirect=carrito" class="btn btn-primary btn-lg w-full">
                            <i class="fas fa-sign-in-alt"></i> Iniciar Sesión
                        </a>
                        <a href="<?php echo APP_URL; ?>/auth/register" class="btn btn-outline btn-lg w-full mt-2">
                            <i class="fas fa-user-plus"></i> Registrarse
                        </a>
                    <?php endif; ?>

                    <!-- Métodos de pago aceptados -->
                    <div class="payment-methods">
                        <h5>Métodos de pago</h5>
                        <div class="payment-icons">
                            <i class="fab fa-cc-visa"></i>
                            <i class="fab fa-cc-mastercard"></i>
                            <i class="fas fa-money-bill-wave"></i>
                            <i class="fab fa-cc-paypal"></i>
                        </div>
                    </div>

                    <!-- Garantías -->
                    <div class="guarantees">
                        <div class="guarantee-item">
                            <i class="fas fa-shield-alt"></i>
                            <span>Compra segura</span>
                        </div>
                        <div class="guarantee-item">
                            <i class="fas fa-sync-alt"></i>
                            <span>30 días de garantía</span>
                        </div>
                        <div class="guarantee-item">
                            <i class="fas fa-truck"></i>
                            <span>Envío rápido</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php else: ?>
        <!-- Carrito vacío -->
        <div class="empty-cart glass card">
            <i class="fas fa-shopping-cart fa-4x"></i>
            <h2>Tu carrito está vacío</h2>
            <p>Agrega productos para comenzar tu compra</p>
            <a href="<?php echo APP_URL; ?>/tienda" class="btn btn-primary btn-lg">
                <i class="fas fa-store"></i> Ir a la Tienda
            </a>
        </div>
    <?php endif; ?>
</div>

<style>
    .cart-header {
        text-align: center;
        margin: 3rem 0 2rem;
    }

    .cart-items {
        padding: 2rem;
        margin-bottom: 2rem;
    }

    .cart-table {
        overflow-x: auto;
    }

    .cart-table table {
        width: 100%;
        border-collapse: collapse;
    }

    .cart-table thead th {
        padding: 1rem;
        text-align: left;
        border-bottom: 2px solid rgba(255, 255, 255, 0.1);
        color: var(--color-text-secondary);
        font-weight: 600;
        font-size: 0.9rem;
    }

    .cart-item {
        border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        transition: background var(--transition-base);
    }

    .cart-item:hover {
        background: rgba(255, 255, 255, 0.03);
    }

    .cart-table td {
        padding: 1.5rem 1rem;
        vertical-align: middle;
    }

    .item-info {
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .item-image,
    .item-image-placeholder {
        width: 80px;
        height: 80px;
        border-radius: var(--radius-md);
        object-fit: cover;
        flex-shrink: 0;
    }

    .item-image-placeholder {
        display: flex;
        align-items: center;
        justify-content: center;
        background: rgba(255, 255, 255, 0.05);
        color: var(--color-text-muted);
        font-size: 2rem;
    }

    .item-details {
        display: flex;
        flex-direction: column;
    }

    .item-details small {
        color: var(--color-text-muted);
        font-size: 0.8rem;
        margin-top: 0.25rem;
    }

    .item-price {
        font-weight: 600;
        color: var(--color-primary);
    }

    .quantity-control {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .qty-btn {
        width: 32px;
        height: 32px;
        border: 2px solid var(--color-primary);
        background: transparent;
        color: var(--color-primary);
        border-radius: var(--radius-sm);
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all var(--transition-base);
    }

    .qty-btn:hover {
        background: var(--color-primary);
        color: white;
    }

    .qty-input {
        width: 60px;
        text-align: center;
        border: 1px solid rgba(255, 255, 255, 0.2);
        background: transparent;
        color: var(--color-text-primary);
        padding: 0.5rem;
        border-radius: var(--radius-sm);
    }

    .item-subtotal {
        font-weight: 700;
        font-size: 1.1rem;
        color: var(--color-primary);
    }

    .btn-remove {
        background: transparent;
        border: none;
        color: var(--color-danger);
        cursor: pointer;
        font-size: 1.25rem;
        padding: 0.5rem;
        transition: all var(--transition-base);
    }

    .btn-remove:hover {
        transform: scale(1.2);
    }

    .cart-actions {
        display: flex;
        justify-content: space-between;
        margin-top: 2rem;
        padding-top: 2rem;
        border-top: 1px solid rgba(255, 255, 255, 0.1);
    }

    .sticky-summary {
        position: sticky;
        top: 100px;
    }

    .cart-summary {
        padding: 2rem;
    }

    .cart-summary h3 {
        margin-bottom: 1.5rem;
        color: var(--color-primary);
    }

    .summary-items {
        margin-bottom: 1.5rem;
    }

    .summary-row {
        display: flex;
        justify-content: space-between;
        padding: 0.75rem 0;
        color: var(--color-text-secondary);
    }

    .total-row {
        border-top: 2px solid var(--color-primary);
        margin-top: 0.5rem;
        padding-top: 1rem;
        font-size: 1.25rem;
        color: var(--color-text-primary);
    }

    .delivery-options {
        margin: 2rem 0;
        padding: 1.5rem;
        background: rgba(255, 255, 255, 0.03);
        border-radius: var(--radius-lg);
    }

    .delivery-options h4 {
        font-size: 1rem;
        margin-bottom: 1rem;
    }

    .radio-option {
        display: flex;
        align-items: center;
        gap: 1rem;
        padding: 1rem;
        border-radius: var(--radius-md);
        cursor: pointer;
        transition: all var(--transition-base);
        margin-bottom: 0.75rem;
    }

    .radio-option:hover {
        background: rgba(46, 204, 113, 0.05);
    }

    .radio-option input {
        position: absolute;
        opacity: 0;
    }

    .radio-custom {
        width: 20px;
        height: 20px;
        border: 2px solid var(--color-primary);
        border-radius: 50%;
        position: relative;
    }

    .radio-option input:checked+.radio-custom::after {
        content: '';
        position: absolute;
        width: 10px;
        height: 10px;
        background: var(--color-primary);
        border-radius: 50%;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
    }

    .radio-label {
        display: flex;
        flex-direction: column;
    }

    .radio-label small {
        color: var(--color-text-muted);
        font-size: 0.8rem;
    }

    .auth-reminder {
        text-align: center;
        padding: 1.5rem;
        background: rgba(241, 196, 15, 0.1);
        border-radius: var(--radius-md);
        border-left: 4px solid #f1c40f;
        margin-bottom: 1.5rem;
    }

    .auth-reminder i {
        font-size: 2rem;
        color: #f1c40f;
        margin-bottom: 0.5rem;
    }

    .payment-methods {
        margin-top: 2rem;
        padding-top: 2rem;
        border-top: 1px solid rgba(255, 255, 255, 0.1);
    }

    .payment-methods h5 {
        font-size: 0.9rem;
        margin-bottom: 1rem;
        color: var(--color-text-secondary);
    }

    .payment-icons {
        display: flex;
        align-items: center;
        gap: 1rem;
        font-size: 2rem;
        color: var(--color-text-muted);
    }

    .guarantees {
        margin-top: 1.5rem;
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
    }

    .guarantee-item {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        font-size: 0.85rem;
    }

    .guarantee-item i {
        color: var(--color-success);
        font-size: 1.25rem;
    }

    .empty-cart {
        text-align: center;
        padding: 5rem 2rem;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 1.5rem;
    }

    .empty-cart i {
        opacity: 0.2;
    }

    .empty-cart h2 {
        margin: 0;
    }
</style>

<script>
    async function updateQuantity(key, change) {
        const row = document.querySelector(`[data-key="${key}"]`);
        const input = row.querySelector('.qty-input');
        const newQty = parseInt(input.value) + change;

        if (newQty < 1) return;

        try {
            const response = await fetch('<?php echo APP_URL; ?>/tienda/actualizarCarrito', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `key=${key}&cantidad=${newQty}`
            });

            const result = await response.json();

            if (result.success) {
                location.reload();
            }
        } catch (error) {
            alert('Error al actualizar carrito');
        }
    }

    async function removeItem(key) {
        if (!confirm('¿Eliminar este producto del carrito?')) return;

        try {
            const response = await fetch('<?php echo APP_URL; ?>/tienda/eliminarDelCarrito', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `key=${key}`
            });

            const result = await response.json();

            if (result.success) {
                location.reload();
            }
        } catch (error) {
            alert('Error al eliminar producto');
        }
    }

    function clearCart() {
        if (!confirm('¿Vaciar todo el carrito?')) return;
        // TODO: Implementar endpoint para vaciar carrito
        location.reload();
    }

    document.getElementById('checkout_btn')?.addEventListener('click', function() {
        alert('Funcionalidad de pago en desarrollo.\nPróximamente: integración con pasarela de pagos.');
    });
</script>

<?php require_once ROOT_PATH . '/views/layouts/footer.php'; ?>