<?php require_once ROOT_PATH . '/views/layouts/header.php'; ?>

<div class="container">
    <div class="aveologia-header">
        <h1><i class="fas fa-book-medical"></i> Diagnóstico por Síntomas</h1>
        <p class="text-secondary">Identifica posibles enfermedades según los síntomas que observas</p>
    </div>

    <div class="diagnostic-tool glass card">
        < form method="POST" action="<?php echo APP_URL; ?>/ave ologia/diagnostico" id="diagnosticForm">
            <div class="symptoms-selection">
                <h3><i class="fas fa-check-square"></i> Selecciona los síntomas observados:</h3>

                <div class="symptoms-grid">
                    <?php
                    $categorias_sintomas = [];
                    foreach ($sintomas as $sintoma) {
                        $categorias_sintomas[$sintoma['categoria']][] = $sintoma;
                    }
                    ?>

                    <?php foreach ($categorias_sintomas as $categoria => $sintomas_cat): ?>
                        <div class="symptom-category">
                            <h4><?php echo ucfirst($categoria); ?></h4>
                            <?php foreach ($sintomas_cat as $sintoma): ?>
                                <div class="symptom-item">
                                    <label class="checkbox-label">
                                        <input type="checkbox" name="sintomas[]"
                                            value="<?php echo $sintoma['id_sintoma']; ?>"
                                            class="symptom-checkbox"
                                            <?php echo in_array($sintoma['id_sintoma'], $sintomas_seleccionados) ? 'checked' : ''; ?>>
                                        <span class="checkbox-custom"></span>
                                        <span class="symptom-name"><?php echo htmlspecialchars($sintoma['nombre_sintoma']); ?></span>
                                        <span class="symptom-gravity gravity-<?php echo strtolower($sintoma['gravedad']); ?>">
                                            <?php echo ucfirst($sintoma['gravedad']); ?>
                                        </span>
                                    </label>

                                    <!-- Campos adicionales cuando se selecciona -->
                                    <div class="symptom-details" style="display: none;">
                                        <div class="row mt-2">
                                            <div class="col-6">
                                                <label class="form-label-sm">Intensidad:</label>
                                                <select name="intensidad[<?php echo $sintoma['id_sintoma']; ?>]" class="form-control form-control-sm">
                                                    <option value="leve">Leve</option>
                                                    <option value="moderada">Moderada</option>
                                                    <option value="severa">Severa</option>
                                                </select>
                                            </div>
                                            <div class="col-6">
                                                <label class="form-label-sm">Frecuencia:</label>
                                                <select name="frecuencia[<?php echo $sintoma['id_sintoma']; ?>]" class="form-control form-control-sm">
                                                    <option value="rara">Rara</option>
                                                    <option value="ocasional">Ocasional</option>
                                                    <option value="frecuente">Frecuente</option>
                                                    <option value="constante">Constante</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="diagnostic-actions">
                <button type="submit" class="btn btn-primary btn-lg">
                    <i class="fas fa-stethoscope"></i> Diagnosticar
                </button>
                <button type="reset" class="btn btn-outline btn-lg">
                    <i class="fas fa-redo"></i> Reiniciar
                </button>
            </div>
            </form>
    </div>

    <!-- Resultados del diagnóstico -->
    <?php if (!empty($enfermedades_posibles)): ?>
        <div class="diagnostic-results">
            <div class="results-header glass card">
                <h2><i class="fas fa-clipboard-list"></i> Posibles Diagnósticos</h2>
                <p>Se encontraron <?php echo count($enfermedades_posibles); ?> enfermedades compatibles con los síntomas seleccionados</p>
            </div>

            <div class="diseases-list">
                <?php foreach ($enfermedades_posibles as $index => $enfermedad): ?>
                    <div class="disease-card glass card animate-on-scroll">
                        <div class="disease-header">
                            <div class="disease-number">#<?php echo $index + 1; ?></div>
                            <div class="disease-info">
                                <h3><?php echo htmlspecialchars($enfermedad['nombre_enfermedad']); ?></h3>
                                <div class="disease-meta">
                                    <span class="badge badge-<?php echo $enfermedad['tipo_enfermedad'] == 'viral' ? 'danger' : 'warning'; ?>">
                                        <?php echo ucfirst($enfermedad['tipo_enfermedad']); ?>
                                    </span>
                                    <span class="match-score">
                                        <i class="fas fa-check-circle"></i>
                                        <?php echo $enfermedad['coincidencias']; ?> síntomas coinciden
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="disease-body">
                            <div class="row">
                                <div class="col-8">
                                    <h4>Descripción:</h4>
                                    <p><?php echo htmlspecialchars($enfermedad['descripcion']); ?></p>

                                    <h4>Síntomas que coinciden:</h4>
                                    <div class="symptoms-match">
                                        <?php
                                        $sintomas_match = explode(', ', $enfermedad['sintomas_coincidentes']);
                                        foreach ($sintomas_match as $sint): ?>
                                            <span class="badge badge-success">
                                                <i class="fas fa-check"></i> <?php echo $sint; ?>
                                            </span>
                                        <?php endforeach; ?>
                                    </div>
                                </div>

                                <div class="col-4">
                                    <div class="disease-stats">
                                        <div class="stat-item">
                                            <i class="fas fa-clock"></i>
                                            <div>
                                                <small>Período incubación</small>
                                                <strong><?php echo $enfermedad['periodo_incubacion_dias'] ?? 'N/A'; ?> días</strong>
                                            </div>
                                        </div>

                                        <div class="stat-item">
                                            <i class="fas fa-skull-crossbones"></i>
                                            <div>
                                                <small>Mortalidad estimada</small>
                                                <strong><?php echo $enfermedad['mortalidad_estimada'] ?? 'N/A'; ?>%</strong>
                                            </div>
                                        </div>

                                        <?php if ($enfermedad['contagioso']): ?>
                                            <div class="alert alert-danger-sm">
                                                <i class="fas fa-exclamation-triangle"></i> Altamente contagioso
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>

                            <div class="disease-action">
                                <a href="<?php echo APP_URL . '/aveologia/enfermedad/' . $enfermedad['id_enfermedad']; ?>"
                                    class="btn btn-primary">
                                    <i class="fas fa-info-circle"></i> Ver tratamientos y detalles completos
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="disclaimer glass card">
                <i class="fas fa-info-circle"></i>
                <p><strong>Importante:</strong> Este diagnóstico es orientativo. Siempre consulte con un veterinario profesional para un diagnóstico preciso y tratamiento adecuado.</p>
            </div>
        </div>
    <?php endif; ?>
</div>

<style>
    .aveologia-header {
        text-align: center;
        margin: 3rem 0 2rem;
    }

    .diagnostic-tool {
        padding: 2.5rem;
        margin-bottom: 3rem;
    }

    .symptoms-selection h3 {
        margin-bottom: 2rem;
        color: var(--color-primary);
    }

    .symptoms-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
        gap: 2rem;
        margin-bottom: 2rem;
    }

    .symptom-category h4 {
        font-size: 1.1rem;
        margin-bottom: 1rem;
        color: var(--color-primary);
        border-bottom: 2px solid var(--color-primary);
        padding-bottom: 0.5rem;
    }

    .symptom-item {
        margin-bottom: 0.75rem;
        padding: 0.75rem;
        border-radius: var(--radius-md);
        transition: all var(--transition-base);
    }

    .symptom-item:hover {
        background: rgba(46, 204, 113, 0.05);
    }

    .checkbox-label {
        display: flex;
        align-items: center;
        cursor: pointer;
        gap: 0.75rem;
    }

    .symptom-checkbox {
        position: absolute;
        opacity: 0;
    }

    .checkbox-custom {
        width: 20px;
        height: 20px;
        border: 2px solid var(--color-primary);
        border-radius: var(--radius-sm);
        position: relative;
        transition: all var(--transition-base);
    }

    .symptom-checkbox:checked+.checkbox-custom {
        background: var(--color-primary);
    }

    .symptom-checkbox:checked+.checkbox-custom::after {
        content: '✓';
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        color: white;
        font-weight: bold;
    }

    .symptom-name {
        flex: 1;
        font-weight: 500;
    }

    .symptom-gravity {
        padding: 0.25rem 0.75rem;
        border-radius: var(--radius-full);
        font-size: 0.75rem;
        font-weight: 600;
    }

    .gravity-leve {
        background: rgba(52, 152, 219, 0.2);
        color: #3498db;
    }

    .gravity-moderada {
        background: rgba(241, 196, 15, 0.2);
        color: #f1c40f;
    }

    .gravity-alta,
    .gravity-severa {
        background: rgba(231, 76, 60, 0.2);
        color: #e74c3c;
    }

    .diagnostic-actions {
        display: flex;
        gap: 1rem;
        justify-content: center;
        margin-top: 2rem;
    }

    .results-header {
        text-align: center;
        padding: 2rem;
        margin-bottom: 2rem;
    }

    .diseases-list {
        display: flex;
        flex-direction: column;
        gap: 2rem;
        margin-bottom: 2rem;
    }

    .disease-card {
        padding: 0;
        overflow: hidden;
    }

    .disease-header {
        display: flex;
        align-items: center;
        gap: 1.5rem;
        padding: 1.5rem;
        background: linear-gradient(135deg, rgba(46, 204, 113, 0.1), rgba(52, 152, 219, 0.1));
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    }

    .disease-number {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        background: var(--color-primary);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        font-weight: 700;
    }

    .disease-info h3 {
        margin-bottom: 0.5rem;
    }

    .disease-meta {
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .match-score {
        color: var(--color-success);
        font-weight: 600;
    }

    .disease-body {
        padding: 2rem;
    }

    .disease-body h4 {
        margin-top: 1.5rem;
        margin-bottom: 0.75rem;
        font-size: 1rem;
    }

    .disease-body h4:first-child {
        margin-top: 0;
    }

    .symptoms-match {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
    }

    .disease-stats {
        background: rgba(255, 255, 255, 0.03);
        padding: 1.5rem;
        border-radius: var(--radius-md);
    }

    .stat-item {
        display: flex;
        align-items: center;
        gap: 1rem;
        padding: 0.75rem 0;
        border-bottom: 1px solid rgba(255, 255, 255, 0.05);
    }

    .stat-item:last-of-type {
        border-bottom: none;
    }

    .stat-item i {
        font-size: 1.5rem;
        color: var(--color-primary);
    }

    .stat-item small {
        display: block;
        color: var(--color-text-muted);
        font-size: 0.75rem;
    }

    .disease-action {
        margin-top: 1.5rem;
        padding-top: 1.5rem;
        border-top: 1px solid rgba(255, 255, 255, 0.1);
        text-align: center;
    }

    .disclaimer {
        padding: 1.5rem;
        background: rgba(241, 196, 15, 0.1);
        border-left: 4px solid #f1c40f;
    }

    .disclaimer i {
        color: #f1c40f;
        margin-right: 0.5rem;
    }
</style>

<script>
    // Mostrar/ocultar detalles de síntoma
    document.querySelectorAll('.symptom-checkbox').forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const details = this.closest('.symptom-item').querySelector('.symptom-details');
            details.style.display = this.checked ? 'block' : 'none';
        });

        // Inicializar visibilidad
        if (checkbox.checked) {
            const details = checkbox.closest('.symptom-item').querySelector('.symptom-details');
            details.style.display = 'block';
        }
    });

    // Contador de síntomas seleccionados
    const form = document.getElementById('diagnosticForm');
    if (form) {
        const countSelected = () => {
            const count = form.querySelectorAll('.symptom-checkbox:checked').length;
            console.log(`${count} síntomas seleccionados`);
        };

        form.addEventListener('change', countSelected);
    }
</script>

<?php require_once ROOT_PATH . '/views/layouts/footer.php'; ?>