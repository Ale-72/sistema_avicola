<?php require_once ROOT_PATH . '/views/layouts/header.php'; ?>

<div class="container">
    <nav class="breadcrumb">
        <a href="<?php echo APP_URL; ?>/aveologia">Aveología</a>
        <span>/</span>
        <a href="<?php echo APP_URL; ?>/aveologia/enfermedades">Enfermedades</a>
        <span>/</span>
        <span><?php echo htmlspecialchars($enfermedad['nombre_enfermedad']); ?></span>
    </nav>

    <div class="disease-detail">
        <div class="disease-detail-header glass card">
            <div class="header-badges">
                <span class="badge badge-lg badge-<?php echo $enfermedad['tipo_enfermedad'] == 'viral' ? 'danger' : 'warning'; ?>">
                    <?php echo ucfirst($enfermedad['tipo_enfermedad']); ?>
                </span>
                <?php if ($enfermedad['contagioso']): ?>
                    <span class="badge badge-lg badge-danger">
                        <i class="fas fa-exclaim ation-triangle"></i> Altamente Contagioso
                    </span>
                <?php endif; ?>
            </div>

            <h1><?php echo htmlspecialchars($enfermedad['nombre_enfermedad']); ?></h1>

            <div class="disease-meta">
                <?php if ($enfermedad['periodo_incubacion_dias']): ?>
                    <div class="meta-item">
                        <i class="fas fa-clock"></i>
                        <div>
                            <small>Período de incubación</small>
                            <strong><?php echo $enfermedad['periodo_incubacion_dias']; ?> días</strong>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if ($enfermedad['mortalidad_estimada']): ?>
                    <div class="meta-item">
                        <i class="fas fa-skull-crossbones"></i>
                        <div>
                            <small>Mortalidad estimada</small>
                            <strong><?php echo $enfermedad['mortalidad_estimada']; ?>%</strong>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="row">
            <div class="col-8">
                <!-- Descripción -->
                <div class="info-section glass card">
                    <h2><i class="fas fa-info-circle"></i> Descripción</h2>
                    <p><?php echo nl2br(htmlspecialchars($enfermedad['descripcion'])); ?></p>
                </div>

                <!-- Síntomas -->
                <?php if (!empty($sintomas)): ?>
                    <div class="info-section glass card">
                        <h2><i class="fas fa-check-square"></i> Síntomas Asociados</h2>
                        <div class="symptoms-list">
                            <?php foreach ($sintomas as $sint): ?>
                                <div class="symptom-item">
                                    <div class="symptom-header">
                                        <h4><?php echo htmlspecialchars($sint['nombre_sintoma']); ?></h4>
                                        <span class="symptom-gravity gravity-<?php echo strtolower($sint['gravedad']); ?>">
                                            <?php echo ucfirst($sint['gravedad']); ?>
                                        </span>
                                    </div>

                                    <div class="symptom-indicators">
                                        <div class="indicator">
                                            <label>Frecuencia:</label>
                                            <div class="progress-bar">
                                                <div class="progress-fill" style="width: <?php
                                                                                            echo match ($sint['frecuencia']) {
                                                                                                'rara' => '25',
                                                                                                'ocasional' => '50',
                                                                                                'frecuente' => '75',
                                                                                                'constant e' => '100',
                                                                                                default => '50'
                                                                                            };
                                                                                            ?>%"></div>
                                            </div>
                                            <span><?php echo ucfirst($sint['frecuencia']); ?></span>
                                        </div>

                                        <div class="indicator">
                                            <label>Intensidad:</label>
                                            <div class="progress-bar">
                                                <div class="progress-fill intensity" style="width: <?php
                                                                                                    echo match ($sint['intensidad']) {
                                                                                                        'leve' => '33',
                                                                                                        'moderada' => '66',
                                                                                                        'severa' => '100',
                                                                                                        default => '50'
                                                                                                    };
                                                                                                    ?>%"></div>
                                            </div>
                                            <span><?php echo ucfirst($sint['intensidad']); ?></span>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Tratamientos -->
                <?php if (!empty($tratamientos)): ?>
                    <div class="info-section glass card">
                        <h2><i class="fas fa-pills"></i> Tratamientos Recomendados</h2>
                        <div class="treatments-list">
                            <?php foreach ($tratamientos as $trat): ?>
                                <div class="treatment-item">
                                    <div class="treatment-header">
                                        <h4><?php echo htmlspecialchars($trat['nombre_tratamiento']); ?></h4>
                                        <div class="effectiveness">
                                            <i class="fas fa-star"></i>
                                            Efectividad: <?php echo $trat['efectividad']; ?>%
                                        </div>
                                    </div>

                                    <p><?php echo htmlspecialchars($trat['descripcion']); ?></p>

                                    <div class="treatment-details">
                                        <div class="detail-item">
                                            <strong>Dosificación:</strong>
                                            <span><?php echo htmlspecialchars($trat['dosificacion']); ?></span>
                                        </div>
                                        <div class="detail-item">
                                            <strong>Duración:</strong>
                                            <span><?php echo $trat['duracion_dias']; ?> días</span>
                                        </div>
                                        <?php if ($trat['precauciones']): ?>
                                            <div class="detail-item precautions">
                                                <strong><i class="fas fa-exclamation-triangle"></i> Precauciones:</strong>
                                                <span><?php echo htmlspecialchars($trat['precauciones']); ?></span>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <div class="col-4">
                <!-- Remedios alternativos -->
                <?php if (!empty($remedios)): ?>
                    <div class="sidebar-section glass card sticky-sidebar">
                        <h3><i class="fas fa-leaf"></i> Remedios Alternativos</h3>
                        <div class="remedies-list">
                            <?php foreach ($remedios as $rem): ?>
                                <div class="remedy-item">
                                    <h5><?php echo htmlspecialchars($rem['nombre_remedio']); ?></h5>
                                    <p><?php echo htmlspecialchars($rem['descripcion']); ?></p>

                                    <?php if ($rem['ingredientes']): ?>
                                        <div class="ingredients">
                                            <strong>Ingredientes:</strong>
                                            <p><?php echo htmlspecialchars($rem['ingredientes']); ?></p>
                                        </div>
                                    <?php endif; ?>

                                    <?php if ($rem['preparacion']): ?>
                                        <div class="preparation">
                                            <strong>Preparación:</strong>
                                            <p><?php echo htmlspecialchars($rem['preparacion']); ?></p>
                                        </div>
                                    <?php endif; ?>

                                    <div class="effectiveness-bar">
                                        <small>Efectividad estimada:</small>
                                        <div class="progress-bar">
                                            <div class="progress-fill" style="width: <?php echo $rem['efectividad_estimada']; ?>%"></div>
                                        </div>
                                        <span><?php echo $rem['efectividad_estimada']; ?>%</span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Advertencia veterinaria -->
                <div class="warning-box glass card">
                    <i class="fas fa-user-md"></i>
                    <h4>Consulta Profesional</h4>
                    <p>Esta información es orientativa. Siempre consulte con un veterinario especializado para un diagnóstico preciso y tratamiento adecuado.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .disease-detail-header {
        padding: 3rem;
        text-align: center;
        margin-bottom: 2rem;
    }

    .header-badges {
        display: flex;
        justify-content: center;
        gap: 1rem;
        margin-bottom: 1.5rem;
    }

    .badge-lg {
        padding: 0.75rem 1.5rem;
        font-size: 1rem;
    }

    .disease-detail-header h1 {
        font-size: 2.5rem;
        margin-bottom: 2rem;
    }

    .disease-meta {
        display: flex;
        gap: 3rem;
        justify-content: center;
    }

    .meta-item {
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .meta-item i {
        font-size: 2rem;
        color: var(--color-primary);
    }

    .meta-item small {
        display: block;
        color: var(--color-text-muted);
        font-size: 0.8rem;
    }

    .info-section {
        padding: 2.5rem;
        margin-bottom: 2rem;
    }

    .info-section h2 {
        margin-bottom: 1.5rem;
        color: var(--color-primary);
    }

    .symptoms-list,
    .treatments-list {
        display: flex;
        flex-direction: column;
        gap: 1.5rem;
    }

    .symptom-item,
    .treatment-item {
        padding: 1.5rem;
        background: rgba(255, 255, 255, 0.03);
        border-radius: var(--radius-lg);
        border-left: 4px solid var(--color-primary);
    }

    .symptom-header,
    .treatment-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1rem;
    }

    .symptom-gravity {
        padding: 0.5rem 1rem;
        border-radius: var(--radius-full);
        font-size: 0.8rem;
        font-weight: 600;
    }

    .symptom-indicators {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 1rem;
    }

    .indicator {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }

    .indicator label {
        font-size: 0.85rem;
        color: var(--color-text-secondary);
    }

    .progress-bar {
        height: 8px;
        background: rgba(255, 255, 255, 0.1);
        border-radius: var(--radius-full);
        overflow: hidden;
    }

    .progress-fill {
        height: 100%;
        background: var(--color-primary);
        transition: width 0.5s ease;
    }

    .progress-fill.intensity {
        background: linear-gradient(90deg, var(--color-success), var(--color-warning), var(--color-danger));
    }

    .effectiveness {
        color: var(--color-success);
        font-weight: 600;
    }

    .treatment-details {
        margin-top: 1rem;
        padding-top: 1rem;
        border-top: 1px solid rgba(255, 255, 255, 0.1);
    }

    .detail-item {
        padding: 0.5rem 0;
        display: flex;
        gap: 1rem;
    }

    .detail-item.precautions {
        background: rgba(241, 196, 15, 0.1);
        padding: 1rem;
        border-radius: var(--radius-md);
        margin-top: 0.5rem;
        color: #f1c40f;
    }

    .sidebar-section {
        position: sticky;
        top: 100px;
        padding: 2rem;
        margin-bottom: 2rem;
    }

    .remedies-list {
        display: flex;
        flex-direction: column;
        gap: 1.5rem;
    }

    .remedy-item {
        padding: 1.5rem;
        background: rgba(255, 255, 255, 0.03);
        border-radius: var(--radius-md);
    }

    .remedy-item h5 {
        margin-bottom: 0.75rem;
        color: var(--color-primary);
    }

    .remedy-item p {
        font-size: 0.875rem;
        color: var(--color-text-secondary);
    }

    .ingredients,
    .preparation {
        margin-top: 1rem;
        font-size: 0.85rem;
    }

    .ingredients strong,
    .preparation strong {
        display: block;
        margin-bottom: 0.5rem;
        color: var(--color-text-primary);
    }

    .effectiveness-bar {
        margin-top: 1rem;
    }

    .effectiveness-bar small {
        display: block;
        margin-bottom: 0.5rem;
        color: var(--color-text-muted);
    }

    .warning-box {
        padding: 2rem;
        background: linear-gradient(135deg, rgba(52, 152, 219, 0.2), rgba(46, 204, 113, 0.1));
        text-align: center;
    }

    .warning-box i {
        font-size: 3rem;
        color: var(--color-primary);
        margin-bottom: 1rem;
    }

    .warning-box h4 {
        margin-bottom: 1rem;
    }
</style>

<?php require_once ROOT_PATH . '/views/layouts/footer.php'; ?>