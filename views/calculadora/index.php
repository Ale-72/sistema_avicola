<?php require_once ROOT_PATH . '/views/layouts/header.php'; ?>

<div class="container">
    <div class="calculator-header">
        <h1><i class="fas fa-calculator"></i> Calculadora de Recursos Avícolas</h1>
        <p class="text-secondary">Calcula con precis ión los requerimientos exactos de alimento y agua para tu granja</p>
    </div>

    <div class="row">
        <!-- Formulario de cálculo -->
        <div class="col-5">
            <div class="calculator-form glass card sticky-form">
                <h3><i class="fas fa-sliders-h"></i> Parámetros de Cálculo</h3>

                <form id="calculatorForm">
                    <div class="form-group">
                        <label class="form-label">
                            <i class="fas fa-kiwi-bird"></i> Tipo de Ave
                        </label>
                        <select name="tipo_ave" id="tipo_ave" class="form-control" required>
                            <option value="">Selecciona un tipo</option>
                            <?php foreach ($tipos_ave as $tipo): ?>
                                <option value="<?php echo $tipo['id_tipo_ave']; ?>"
                                    data-proposito="<?php echo $tipo['proposito']; ?>">
                                    <?php echo htmlspecialchars($tipo['nombre_tipo']); ?>
                                    (<?php echo ucfirst($tipo['proposito']); ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <small class="form-text-help" id="tipo_help"></small>
                    </div>

                    <div class="row">
                        <div class="col-6">
                            <div class="form-group">
                                <label class="form-label">
                                    <i class="fas fa-list-ol"></i> Cantidad de Aves
                                </label>
                                <input type="number" name="cantidad" id="cantidad" class="form-control"
                                    min="1" max="100000" required placeholder="Ej: 1000" value="100">
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-group">
                                <label class="form-label">
                                    <i class="fas fa-calendar"></i> Edad (días)
                                </label>
                                <input type="number" name="edad_dias" id="edad_dias" class="form-control"
                                    min="0" max="3650" required placeholder="Ej: 30" value="30">
                                <small class="form-text-help" id="etapa_help"></small>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">
                            <i class="fas fa-toggle-on"></i> Incluir costos (opcional)
                        </label>
                        <label class="toggle-switch">
                            <input type="checkbox" id="incluir_costos">
                            <span class="toggle-slider"></span>
                        </label>
                    </div>

                    <div id="costos_section" style="display: none;">
                        <div class="row">
                            <div class="col-6">
                                <div class="form-group">
                                    <label class="form-label-sm">Precio alimento (S/ / kg)</label>
                                    <input type="number" name="precio_alimento" id="precio_alimento"
                                        class="form-control form-control-sm" step="0.01" min="0" placeholder="2.50">
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="form-group">
                                    <label class="form-label-sm">Precio agua (S/ / L)</label>
                                    <input type="number" name="precio_agua" id="precio_agua"
                                        class="form-control form-control-sm" step="0.01" min="0" placeholder="0.05">
                                </div>
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary btn-lg w-full" id="calculate_btn">
                        <i class="fas fa-calculator"></i> Calcular Requerimientos
                    </button>
                </form>

                <!-- Historial reciente -->
                <?php if (!empty($historial)): ?>
                    <div class="recent-history mt-4">
                        <h4><i class="fas fa-history"></i> Cálculos Recientes</h4>
                        <div class="history-list">
                            <?php foreach ($historial as $item): ?>
                                <div class="history-item">
                                    <div class="history-info">
                                        <strong><?php echo $item['nombre_tipo']; ?></strong>
                                        <small><?php echo $item['cantidad_aves']; ?> aves · <?php echo $item['edad_dias']; ?> días</small>
                                    </div>
                                    <div class="history-result">
                                        <span><?php echo $item['resultado_alimento_kg']; ?> kg</span>
                                        <small><?php echo date('d/m', strtotime($item['fecha_calculo'])); ?></small>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <a href="<?php echo APP_URL; ?>/calculadora/historial" class="btn btn-outline btn-sm w-full mt-2">
                            Ver historial completo
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Resultados -->
        <div class="col-7">
            <div id="results_container" class="results-placeholder glass card">
                <i class="fas fa-chart-line fa-3x"></i>
                <h3>Completa el formulario para ver los resultados</h3>
                <p>Ingresa los datos required en el formulario y presiona "Calcular"</p>
            </div>

            <!-- Template de resultados (se rellena con JS) -->
            <div id="results_content" style="display: none;">
                <!-- Los resultados se mostrarán dinámicamente aquí -->
            </div>
        </div>
    </div>
</div>

<style>
    .calculator-header {
        text-align: center;
        margin: 3rem 0 2rem;
    }

    .sticky-form {
        position: sticky;
        top: 100px;
    }

    .calculator-form {
        padding: 2rem;
    }

    .calculator-form h3 {
        margin-bottom: 1.5rem;
        color: var(--color-primary);
    }

    .form-text-help {
        display: block;
        font-size: 0.8125rem;
        color: var(--color-text-muted);
        margin-top: 0.25rem;
    }

    .toggle-switch {
        position: relative;
        display: inline-block;
        width: 60px;
        height: 34px;
    }

    .toggle-switch input {
        opacity: 0;
        width: 0;
        height: 0;
    }

    .toggle-slider {
        position: absolute;
        cursor: pointer;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: rgba(255, 255, 255, 0.1);
        transition: 0.4s;
        border-radius: 34px;
    }

    .toggle-slider:before {
        position: absolute;
        content: "";
        height: 26px;
        width: 26px;
        left: 4px;
        bottom: 4px;
        background-color: white;
        transition: 0.4s;
        border-radius: 50%;
    }

    input:checked+.toggle-slider {
        background-color: var(--color-primary);
    }

    input:checked+.toggle-slider:before {
        transform: translateX(26px);
    }

    .recent-history {
        border-top: 1px solid rgba(255, 255, 255, 0.1);
        padding-top: 1.5rem;
    }

    .recent-history h4 {
        font-size: 1rem;
        margin-bottom: 1rem;
        color: var(--color-text-secondary);
    }

    .history-list {
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
    }

    .history-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0.75rem;
        background: rgba(255, 255, 255, 0.03);
        border-radius: var(--radius-md);
        transition: all var(--transition-base);
    }

    .history-item:hover {
        background: rgba(46, 204, 113, 0.1);
        transform: translateX(4px);
    }

    .history-info {
        display: flex;
        flex-direction: column;
    }

    .history-info small {
        color: var(--color-text-muted);
        font-size: 0.75rem;
    }

    .history-result {
        text-align: right;
    }

    .history-result span {
        display: block;
        font-weight: 700;
        color: var(--color-primary);
    }

    .history-result small {
        color: var(--color-text-muted);
        font-size: 0.75rem;
    }

    .results-placeholder {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 4rem 2rem;
        text-align: center;
        min-height: 500px;
    }

    .results-placeholder i {
        opacity: 0.2;
        margin-bottom: 1.5rem;
    }

    /* Resultados */
    .results-card {
        padding: 2rem;
        margin-bottom: 1.5rem;
    }

    .result-header {
        margin-bottom: 2rem;
        text-align: center;
    }

    .result-tabs {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 1rem;
        margin-bottom: 2rem;
    }

    .result-tab {
        padding: 1.5rem;
        background: linear-gradient(135deg, rgba(46, 204, 113, 0.1), rgba(52, 152, 219, 0.1));
        border-radius: var(--radius-lg);
        text-align: center;
        border: 2px solid transparent;
        transition: all var(--transition-base);
    }

    .result-tab:hover {
        border-color: var(--color-primary);
        transform: translateY(-4px);
    }

    .result-tab-label {
        display: block;
        font-size: 0.85rem;
        color: var(--color-text-muted);
        margin-bottom: 0.5rem;
    }

    .result-tab-value {
        display: block;
        font-size: 2rem;
        font-weight: 700;
        color: var(--color-primary);
    }

    .result-tab-unit {
        font-size: 0.9rem;
        color: var(--color-text-secondary);
    }

    .recommendations-list {
        list-style: none;
        padding: 0;
    }

    .recommendations-list li {
        padding: 0.75rem 0.75rem 0.75rem 3rem;
        position: relative;
    }

    .recommendations-list li::before {
        content: "✓";
        position: absolute;
        left: 0.75rem;
        top: 0.75rem;
        width: 24px;
        height: 24px;
        background: var(--color-primary);
        color: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
    }
</style>

<script>
    const calculatorForm = document.getElementById('calculatorForm');
    const tipoAveSelect = document.getElementById('tipo_ave');
    const edadDiasInput = document.getElementById('edad_dias');
    const incluirCost osCheck = document.getElementById('incluir_costos');
    const costosSection = document.getElementById('costos_section');
    const resultsContainer = document.getElementById('results_container');
    const resultsContent = document.getElementById('results_content');

    // Toggle costos section
    incluirCostos?.addEventListener('change', function() {
        costosSection.style.display = this.checked ? 'block' : 'none';
    });

    // Actualizar descripción de tipo
    tipoAveSelect?.addEventListener('change', function() {
        const selected = this.options[this.selectedIndex];
        const proposito = selected.dataset.proposito;
        const help = document.getElementById('tipo_help');
        if (help && proposito) {
            help.textContent = `Propósito: ${proposito}`;
        }
    });

    // Calcular
    calculatorForm?.addEventListener('submit', async function(e) {
        e.preventDefault();

        const btn = document.getElementById('calculate_btn');
        const originalText = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Calculando...';
        btn.disabled = true;

        const formData = new FormData(this);

        try {
            const response = await fetch('<?php echo APP_URL; ?>/calculadora/calcular', {
                method: 'POST',
                body: formData
            });

            const result = await response.json();

            if (result.error) {
                alert(result.mensaje || 'Error al calcular');
                return;
            }

            // Mostrar resultados
            displayResults(result);

        } catch (error) {
            alert('Error de conexión');
        } finally {
            btn.innerHTML = originalText;
            btn.disabled = false;
        }
    });

    function displayResults(data) {
        resultsContainer.style.display = 'none';
        resultsContent.style.display = 'block';

        const html = `
        <div class="results-card glass card">
            <div class="result-header">
                <h2><i class="fas fa-chart-bar"></i> Resultados del Cálculo</h2>
                <p class="text-secondary">
                    ${data.datos_entrada.cantidad_aves} ${data.datos_entrada.tipo_ave} · 
                    ${data.datos_entrada.edad_dias} días (${data.datos_entrada.nombre_etapa})
                </p>
            </div>

            <h3><i class="fas fa-calendar-day"></i> Consumo Diario</h3>
            <div class="result-tabs">
                <div class="result-tab">
                    <span class="result-tab-label">Alimento Total</span>
                    <span class="result-tab-value">${data.consumo_diario.alimento_kg} <span class="result-tab-unit">kg</span></span>
                </div>
                <div class="result-tab">
                    <span class="result-tab-label">Agua Total</span>
                    <span class="result-tab-value">${data.consumo_diario.agua_litros} <span class="result-tab-unit">L</span></span>
                </div>
                <div class="result-tab">
                    <span class="result-tab-label">Por Ave</span>
                    <span class="result-tab-value">${data.parametros.consumo_ave_alimento_gr} <span class="result-tab-unit">g</span></span>
                </div>
            </div>

            <h3 class="mt-4"><i class="fas fa-calendar-week"></i> Proyecciones</h3>
            <div class="row">
                <div class="col-6">
                    <div class="result-tab">
                        <span class="result-tab-label">Semanal</span>
                        <span class="result-tab-value">${data.proyecciones.semanal.alimento_kg} kg</span>
                        <small class="text-muted">${data.proyecciones.semanal.agua_litros} L agua</small>
                    </div>
                </div>
                <div class="col-6">
                    <div class="result-tab">
                        <span class="result-tab-label">Mensual</span>
                        <span class="result-tab-value">${data.proyecciones.mensual.alimento_kg} kg</span>
                        <small class="text-muted">${data.proyecciones.mensual.agua_litros} L agua</small>
                    </div>
                </div>
            </div>

            ${data.costos ? `
                <h3 class="mt-4"><i class="fas fa-dollar-sign"></i> Costos Estimados (S/)</h3>
                <div class="result-tabs">
                    <div class="result-tab">
                        <span class="result-tab-label">Diario</span>
                        <span class="result-tab-value">S/ ${data.costos.diario.total.toFixed(2)}</span>
                    </div>
                    <div class="result-tab">
                        <span class="result-tab-label">Semanal</span>
                        <span class="result-tab-value">S/ ${data.costos.semanal.total.toFixed(2)}</span>
                    </div>
                    <div class="result-tab">
                        <span class="result-tab-label">Mensual</span>
                        <span class="result-tab-value">S/ ${data.costos.mensual.total.toFixed(2)}</span>
                    </div>
                </div>
            ` : ''}

            <h3 class="mt-4"><i class="fas fa-lightbulb"></i> Recomendaciones</h3>
            <ul class="recommendations-list">
                ${data.recomendaciones.map(rec => `<li>${rec}</li>`).join('')}
            </ul>

            <div class="mt-4">
                <button class="btn btn-primary" onclick="window.print()">
                    <i class="fas fa-print"></i> Imprimir Resultados
                </button>
            </div>
        </div>
    `;

        resultsContent.innerHTML = html;

        // Scroll to results
        resultsContent.scrollIntoView({
            behavior: 'smooth'
        });
    }
</script>

<?php require_once ROOT_PATH . '/views/layouts/footer.php'; ?>