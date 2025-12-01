<?php require_once ROOT_PATH . '/views/layouts/header.php'; ?>

<div class="container">
    <div class="error-container">
        <div class="error-content glass card">
            <div class="error-icon">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <h1 class="error-code">404</h1>
            <h2>Página No Encontrada</h2>
            <p>Lo sentimos, la página que estás buscando no existe o ha sido movida.</p>

            <div class="error-actions">
                <a href="<?php echo APP_URL; ?>/" class="btn btn-primary">
                    <i class="fas fa-home"></i> Volver al Inicio
                </a>
                <a href="javascript:history.back()" class="btn btn-outline">
                    <i class="fas fa-arrow-left"></i> Volver Atrás
                </a>
            </div>
        </div>
    </div>
</div>

<style>
    .error-container {
        min-height: 60vh;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 2rem 0;
    }

    .error-content {
        text-align: center;
        max-width: 600px;
        padding: 4rem 2rem;
    }

    .error-icon {
        font-size: 5rem;
        color: var(--color-warning);
        margin-bottom: 1rem;
    }

    .error-code {
        font-size: 6rem;
        font-weight: 800;
        background: linear-gradient(135deg, var(--color-warning), var(--color-danger));
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        margin: 0;
    }

    .error-content h2 {
        font-size: 2rem;
        margin: 1rem 0;
    }

    .error-content p {
        color: var(--color-text-secondary);
        font-size: 1.1rem;
        margin-bottom: 2rem;
    }

    .error-actions {
        display: flex;
        gap: 1rem;
        justify-content: center;
        flex-wrap: wrap;
    }
</style>

<?php require_once ROOT_PATH . '/views/layouts/footer.php'; ?>