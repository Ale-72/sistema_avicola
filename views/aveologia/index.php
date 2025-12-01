<?php require_once ROOT_PATH . '/views/layouts/header.php'; ?>

<div class="container">
    <div class="page-header">
        <h1><i class="fas fa-book-medical"></i> Aveología</h1>
        <p>Base de conocimientos para el cuidado de tus aves</p>
    </div>

    <div class="aveologia-intro glass card">
        <h2>¿Qué es Aveología?</h2>
        <p>Nuestra base de conocimientos especializada te ayuda a diagnosticar enfermedades, conocer tratamientos y aprender sobre el cuidado óptimo de aves de corral.</p>

        <a href="<?php echo APP_URL; ?>/aveologia/diagnostico" class="btn btn-primary">
            <i class="fas fa-search"></i> Buscar por Síntomas
        </a>
    </div>

    <?php if (!empty($categorias)): ?>
        <h2 class="mt-4">Categorías</h2>
        <div class="categories-grid">
            <?php foreach ($categorias as $categoria): ?>
                <div class="category-card glass card">
                    <i class="<?php echo $categoria['icono']; ?> category-icon"></i>
                    <h3><?php echo htmlspecialchars($categoria['nombre_categoria']); ?></h3>
                    <p><?php echo htmlspecialchars($categoria['descripcion']); ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($articulos)): ?>
        <h2 class="mt-4">Artículos Destacados</h2>
        <div class="articles-grid">
            <?php foreach ($articulos as $articulo): ?>
                <div class="article-card glass card">
                    <h3><?php echo htmlspecialchars($articulo['titulo']); ?></h3>
                    <p><?php echo htmlspecialchars(substr($articulo['resumen'] ?? '', 0, 150)) . '...'; ?></p>
                    <a href="#" class="btn btn-outline btn-sm">Leer más</a>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<style>
    .page-header {
        text-align: center;
        margin: 3rem 0;
    }

    .aveologia-intro {
        padding: 2rem;
        text-align: center;
        margin: 2rem 0;
    }

    .categories-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
        gap: 1.5rem;
        margin: 2rem 0;
    }

    .category-card {
        padding: 2rem;
        text-align: center;
    }

    .category-icon {
        font-size: 3rem;
        color: var(--color-primary);
        margin-bottom: 1rem;
    }

    .articles-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 1.5rem;
        margin: 2rem 0;
    }

    .article-card {
        padding: 1.5rem;
    }
</style>

<?php require_once ROOT_PATH . '/views/layouts/footer.php'; ?>