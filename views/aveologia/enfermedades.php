<?php require_once ROOT_PATH . '/views/layouts/header.php'; ?>

<div class="container">
    <div class="page-header">
        <h1><i class="fas fa-book-medical"></i> Aveología AVITECH</h1>
        <p class="text-secondary">Base de conocimientos de medicina aviar y salud de aves</p>
    </div>

    <div class="row">
        <div class="col-4">
            <div class="aveology-sidebar glass card sticky-sidebar">
                <h3><i class="fas fa-compass"></i> Navegación</h3>
                <nav class="sidebar-nav">
                    <a href="<?php echo APP_URL; ?>/aveologia" class="nav-item">
                        <i class="fas fa-home"></i> Inicio
                    </a>
                    <a href="<?php echo APP_URL; ?>/aveologia/diagnostico" class="nav-item">
                        <i class="fas fa-stethoscope"></i> Diagnóstico
                    </a>
                    <a href="<?php echo APP_URL; ?>/aveologia/enfermedades" class="nav-item active">
                        <i class="fas fa-virus"></i> Enfermedades
                    </a>
                    <a href="<?php echo APP_URL; ?>/aveologia/articulos" class="nav-item">
                        <i class="fas fa-newspaper"></i> Artículos
                    </a>
                    <a href="<?php echo APP_URL; ?>/aveologia/prevencion" class="nav-item">
                        <i class="fas fa-shield-virus"></i> Prevención
                    </a>
                </nav>

                <!-- Categorías -->
                <?php if (!empty($categorias)): ?>
                    <div class="sidebar-section">
                        <h4>Por Categoría</h4>
                        <div class="category-list">
                            <?php foreach ($categorias as $cat): ?>
                                <a href="?categoria=<?php echo $cat['id_categoria']; ?>" class="category-badge">
                                    <i class="<?php echo $cat['icono']; ?>"></i>
                                    <?php echo $cat['nombre_categoria']; ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="col-8">
            <!-- Búsqueda y filtros -->
            <div class="search-box glass card">
                <form method="GET" class="search-form">
                    <div class="row">
                        <div class="col-8">
                            <input type="text" name="q" class="form-control"
                                placeholder="Buscar enfermedades..."
                                value="<?php echo htmlspecialchars($busqueda); ?>">
                        </div>
                        <div class="col-4">
                            <button type="submit" class="btn btn-primary w-full">
                                <i class="fas fa-search"></i> Buscar
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Lista de enfermedades -->
            <?php if (!empty($enfermedades)): ?>
                <div class="diseases-grid">
                    <?php foreach ($enfermedades as $enfermedad): ?>
                        <div class="disease-card glass card animate-on-scroll">
                            <div class="disease-badge">
                                <span class="badge badge-<?php echo $enfermedad['tipo_enfermedad'] == 'viral' ? 'danger' : 'warning'; ?>">
                                    <?php echo ucfirst($enfermedad['tipo_enfermedad']); ?>
                                </span>
                                <?php if ($enfermedad['contagioso']): ?>
                                    <span class="badge badge-danger-outline">
                                        <i class="fas fa-exclamation-triangle"></i> Contagioso
                                    </span>
                                <?php endif; ?>
                            </div>

                            <h3><?php echo htmlspecialchars($enfermedad['nombre_enfermedad']); ?></h3>

                            <p class="disease-desc">
                                <?php echo htmlspecialchars(substr($enfermedad['descripcion'], 0, 150)) . '...'; ?>
                            </p>

                            <div class="disease-stats-row">
                                <div class="stat-mini">
                                    <i class="fas fa-check-square"></i>
                                    <span><?php echo $enfermedad['total_sintomas']; ?> síntomas</span>
                                </div>

                                <?php if ($enfermedad['periodo_incubacion_dias']): ?>
                                    <div class="stat-mini">
                                        <i class="fas fa-clock"></i>
                                        <span><?php echo $enfermedad['periodo_incubacion_dias']; ?> días incubación</span>
                                    </div>
                                <?php endif; ?>

                                <?php if ($enfermedad['mortalidad_estimada']): ?>
                                    <div class="stat-mini danger">
                                        <i class="fas fa-skull-crossbones"></i>
                                        <span><?php echo $enfermedad['mortalidad_estimada']; ?>% mortalidad</span>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <a href="<?php echo APP_URL . '/aveologia/enfermedad/' . $enfermedad['id_enfermedad']; ?>"
                                class="btn btn-outline w-full">
                                <i class="fas fa-info-circle"></i> Ver Detalles
                            </a>
                            </disease-card>
                        <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="empty-state glass card">
                            <i class="fas fa-search fa-3x"></i>
                            <h3>No se encontraron enfermedades</h3>
                            <p>Intenta con otros términos de búsqueda</p>
                        </div>
                    <?php endif; ?>
                </div>
        </div>
    </div>

    <style>
        .page-header {
            text-align: center;
            margin: 3rem 0 2rem;
        }

        .sticky-sidebar {
            position: sticky;
            top: 100px;
            padding: 2rem;
        }

        .sidebar-nav {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .nav-item {
            padding: 1rem;
            border-radius: var(--radius-md);
            text-decoration: none;
            color: var(--color-text-primary);
            display: flex;
            align-items: center;
            gap: 0.75rem;
            transition: all var(--transition-base);
        }

        .nav-item:hover {
            background: rgba(46, 204, 113, 0.1);
            transform: translateX(4px);
        }

        .nav-item.active {
            background: linear-gradient(135deg, rgba(46, 204, 113, 0.2), rgba(52, 152, 219, 0.2));
            border-left: 4px solid var(--color-primary);
        }

        .sidebar-section {
            margin-top: 2rem;
            padding-top: 2rem;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }

        .sidebar-section h4 {
            font-size: 0.875rem;
            color: var(--color-text-muted);
            margin-bottom: 1rem;
        }

        .category-list {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .category-badge {
            padding: 0.75rem;
            background: rgba(255, 255, 255, 0.03);
            border-radius: var(--radius-md);
            text-decoration: none;
            color: var(--color-text-primary);
            font-size: 0.875rem;
            transition: all var(--transition-base);
        }

        .category-badge:hover {
            background: rgba(46, 204, 113, 0.1);
            transform: translateX(4px);
        }

        .search-box {
            padding: 1.5rem;
            margin-bottom: 2rem;
        }

        .diseases-grid {
            display: grid;
            gap: 1.5rem;
        }

        .disease-card {
            padding: 2rem;
            transition: all var(--transition-base);
        }

        .disease-card:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow-xl);
        }

        .disease-badge {
            display: flex;
            gap: 0.5rem;
            margin-bottom: 1rem;
        }

        .disease-card h3 {
            font-size: 1.5rem;
            margin-bottom: 1rem;
            color: var(--color-primary);
        }

        .disease-desc {
            color: var(--color-text-secondary);
            margin-bottom: 1.5rem;
            line-height: 1.6;
        }

        .disease-stats-row {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            margin-bottom: 1.5rem;
            padding: 1rem;
            background: rgba(255, 255, 255, 0.03);
            border-radius: var(--radius-md);
        }

        .stat-mini {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.875rem;
        }

        .stat-mini i {
            color: var(--color-primary);
        }

        .stat-mini.danger i {
            color: var(--color-danger);
        }

        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
        }

        .empty-state i {
            opacity: 0.2;
            margin-bottom: 1.5rem;
        }
    </style>

    <?php require_once ROOT_PATH . '/views/layouts/footer.php'; ?>