<?php require_once ROOT_PATH . '/views/layouts/header.php'; ?>

<!-- Hero Section -->
<section class="hero-section">
    <div class="container">
        <div class="hero-content fade-in">
            <h1 class="hero-title">
                Bienvenido a <strong class="text-primary">AVITECH</strong>
            </h1>
            <p class="hero-subtitle">
                Sistema Integral de Gestión Avícola que conecta productores con consumidores
            </p>
            <div class="hero-features">
                <div class="feature-item">
                    <i class="fas fa-book-medical"></i>
                    <span>Base de Conocimientos</span>
                </div>
                <div class="feature-item">
                    <i class="fas fa-calculator"></i>
                    <span>Calculadora de Recursos</span>
                </div>
                <div class="feature-item">
                    <i class="fas fa-store"></i>
                    <span>Tienda Online</span>
                </div>
            </div>
            <div class="hero-cta">
                <a href="<?php echo APP_URL; ?>/tienda" class="btn btn-primary btn-lg">
                    <i class="fas fa-shopping-cart"></i> Explorar Productos
                </a>
                <a href="<?php echo APP_URL; ?>/aveologia" class="btn btn-outline btn-lg">
                    <i class="fas fa-book"></i> Ir a Aveología
                </a>
            </div>
        </div>
    </div>
</section>

<!-- Productos Destacados -->
<?php if (!empty($productos_destacados)): ?>
    <section class="featured-products">
        <div class="container">
            <div class="section-header">
                <h2><i class="fas fa-star"></i> Productos Destacados</h2>
                <p>Los mejores productos de nuestra granja</p>
            </div>

            <div class="products-grid">
                <?php foreach ($productos_destacados as $producto): ?>
                    <div class="product-card glass card animate-on-scroll">
                        <div class="product-image">
                            <?php if ($producto['imagen_principal']): ?>
                                <img src="<?php echo APP_URL . '/public/uploads/' . $producto['imagen_principal']; ?>"
                                    alt="<?php echo htmlspecialchars($producto['nombre_producto']); ?>">
                            <?php else: ?>
                                <div class="no-image">
                                    <i class="fas fa-image"></i>
                                </div>
                            <?php endif; ?>
                            <span class="badge badge-primary">Destacado</span>
                        </div>

                        <div class="product-body">
                            <div class="product-category">
                                <i class="fas fa-tag"></i> <?php echo htmlspecialchars($producto['nombre_categoria']); ?>
                            </div>
                            <h3 class="product-title">
                                <?php echo htmlspecialchars($producto['nombre_producto']); ?>
                            </h3>
                            <p class="product-description">
                                <?php echo htmlspecialchars(substr($producto['descripcion_corta'] ?? '', 0, 80)) . '...'; ?>
                            </p>

                            <div class="product-footer">
                                <div class="product-price">
                                    <?php if ($producto['precio_oferta']): ?>
                                        <span class="price-old">S/ <?php echo number_format($producto['precio_unitario'], 2); ?></span>
                                        <span class="price-current">S/ <?php echo number_format($producto['precio_oferta'], 2); ?></span>
                                    <?php else: ?>
                                        <span class="price-current">S/ <?php echo number_format($producto['precio_unitario'], 2); ?></span>
                                    <?php endif; ?>
                                </div>

                                <a href="<?php echo APP_URL . '/producto/' . $producto['slug']; ?>" class="btn btn-primary btn-sm">
                                    <i class="fas fa-shopping-cart"></i> Ver Más
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="text-center mt-4">
                <a href="<?php echo APP_URL; ?>/tienda" class="btn btn-secondary btn-lg">
                    <i class="fas fa-th"></i> Ver Todos los Productos
                </a>
            </div>
        </div>
    </section>
<?php endif; ?>

<!-- Características -->
<section class="features-section">
    <div class="container">
        <div class="section-header">
            <h2><i class="fas fa-rocket"></i> ¿Por qué Elegir AVITECH?</h2>
            <p>Soluciones integrales para el sector avícola</p>
        </div>

        <div class="features-grid">
            <div class="feature-card glass card animate-on-scroll">
                <div class="feature-icon">
                    <i class="fas fa-dna"></i>
                </div>
                <h3>Aveología Completa</h3>
                <p>Base de conocimientos con diagnóstico de enfermedades, tratamientos y remedios basados en síntomas</p>
            </div>

            <div class="feature-card glass card animate-on-scroll">
                <div class="feature-icon">
                    <i class="fas fa-calculator"></i>
                </div>
                <h3>Calculadora Inteligente</h3>
                <p>Calcula exactamente cuánto alimento y agua necesitan tus aves según su edad y tipo</p>
            </div>

            <div class="feature-card glass card animate-on-scroll">
                <div class="feature-icon">
                    <i class="fas fa-map-marked-alt"></i>
                </div>
                <h3>Red de Sucursales</h3>
                <p>Múltiples puntos de venta distribuidos estratégicamente con delivery o recojo gratuito</p>
            </div>

            <div class="feature-card glass card animate-on-scroll">
                <div class="feature-icon">
                    <i class="fas fa-truck"></i>
                </div>
                <h3>Logística Híbrida</h3>
                <p>Elige entre delivery a domicilio o recojo en la sucursal más cercana a ti</p>
            </div>
        </div>
    </div>
</section>

<style>
    .hero-section {
        padding: 4rem 0;
        text-align: center;
        background: linear-gradient(135deg, rgba(46, 204, 113, 0.1), rgba(52, 152, 219, 0.1));
        border-radius: var(--radius-xl);
        margin: 2rem 0;
    }

    .hero-title {
        font-size: 3rem;
        margin-bottom: 1rem;
        background: linear-gradient(135deg, var(--color-text-primary), var(--color-primary));
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }

    .hero-subtitle {
        font-size: 1.25rem;
        color: var(--color-text-secondary);
        margin-bottom: 2rem;
    }

    .hero-features {
        display: flex;
        justify-content: center;
        gap: 2rem;
        margin: 2rem 0;
        flex-wrap: wrap;
    }

    .feature-item {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 0.5rem;
    }

    .feature-item i {
        font-size: 2rem;
        color: var(--color-primary);
    }

    .hero-cta {
        display: flex;
        gap: 1rem;
        justify-content: center;
        flex-wrap: wrap;
    }

    .featured-products,
    .features-section {
        padding: 3rem 0;
    }

    .section-header {
        text-align: center;
        margin-bottom: 3rem;
    }

    .section-header h2 {
        font-size: 2.5rem;
        margin-bottom: 0.5rem;
    }

    .section-header p {
        color: var(--color-text-secondary);
        font-size: 1.1rem;
    }

    .products-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: 2rem;
    }

    .product-card {
        overflow: hidden;
        transition: all var(--transition-base);
    }

    .product-image {
        position: relative;
        height: 200px;
        overflow: hidden;
        background: var(--color-bg-secondary);
    }

    .product-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .no-image {
        display: flex;
        align-items: center;
        justify-content: center;
        height: 100%;
        color: var(--color-text-muted);
        font-size: 3rem;
    }

    .product-image .badge {
        position: absolute;
        top: 1rem;
        right: 1rem;
    }

    .product-body {
        padding: 1.5rem;
    }

    .product-category {
        font-size: 0.85rem;
        color: var(--color-text-muted);
        margin-bottom: 0.5rem;
    }

    .product-title {
        font-size: 1.1rem;
        margin-bottom: 0.75rem;
    }

    .product-description {
        font-size: 0.9rem;
        color: var(--color-text-secondary);
        margin-bottom: 1rem;
    }

    .product-footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .product-price {
        display: flex;
        flex-direction: column;
    }

    .price-old {
        text-decoration: line-through;
        color: var(--color-text-muted);
        font-size: 0.85rem;
    }

    .price-current {
        font-size: 1.5rem;
        font-weight: 700;
        color: var(--color-primary);
    }

    .features-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 2rem;
    }

    .feature-card {
        text-align: center;
        padding: 2rem;
    }

    .feature-icon {
        font-size: 3rem;
        color: var(--color-primary);
        margin-bottom: 1rem;
    }

    .feature-card h3 {
        font-size: 1.25rem;
        margin-bottom: 0.75rem;
    }

    .feature-card p {
        color: var(--color-text-secondary);
    }

    @media (max-width: 768px) {
        .hero-title {
            font-size: 2rem;
        }

        .hero-subtitle {
            font-size: 1rem;
        }

        .hero-cta {
            flex-direction: column;
        }
    }
</style>

<?php require_once ROOT_PATH . '/views/layouts/footer.php'; ?>