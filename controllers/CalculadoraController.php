<?php

/**
 * Controlador de Calculadora - Versión Completa
 */

require_once ROOT_PATH . '/core/Controller.php';
require_once ROOT_PATH . '/models/Calculadora.php';

class CalculadoraController extends Controller
{
    private $calculadoraModel;

    public function __construct()
    {
        parent::__construct();
        $this->calculadoraModel = new Calculadora($this->db);
    }

    /**
     * Página principal de la calculadora
     */
    public function index()
    {
        $tiposAve = $this->calculadoraModel->getTiposAve();
        $etapas = $this->calculadoraModel->getEtapas();

        // Obtener historial si hay usuario logueado
        $historial = [];
        if (Session::isAuthenticated()) {
            $historial = $this->calculadoraModel->getHistorial(Session::getUserId(), 5);
        }

        $data = [
            'title' => 'Calculadora de Recursos - ' . APP_NAME,
            'tipos_ave' => $tiposAve,
            'etapas' => $etapas,
            'historial' => $historial
        ];

        $this->view('calculadora/index', $data);
    }

    /**
     * Procesar cálculo completo (AJAX)
     */
    public function calcular()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['error' => 'Método no permitido'], 405);
        }

        $tipoAve = intval($_POST['tipo_ave'] ?? 0);
        $cantidad = intval($_POST['cantidad'] ?? 0);
        $edadDias = intval($_POST['edad_dias'] ?? 0);
        $precioAlimento = floatval($_POST['precio_alimento'] ?? 0);
        $precioAgua = floatval($_POST['precio_agua'] ?? 0);

        if ($tipoAve <= 0 || $cantidad <= 0 || $edadDias < 0) {
            $this->json(['error' => 'Datos inválidos'], 400);
        }

        // Obtener info del tipo de ave
        $tipoInfo = $this->calculadoraModel->getTipoAve($tipoAve);

        if (!$tipoInfo) {
            $this->json(['error' => 'Tipo de ave no encontrado'], 404);
        }

        // Realizar cálculo completo
        $resultado = $this->calculadoraModel->calcular($tipoAve, $cantidad, $edadDias);

        if (isset($resultado['error'])) {
            $this->json($resultado, 400);
        }

        // Calcular costos si se proporcionaron precios
        if ($precioAlimento > 0 || $precioAgua > 0) {
            $resultado['costos'] = [
                'diario' => [
                    'alimento' => $resultado['consumo_diario']['alimento_kg'] * $precioAlimento,
                    'agua' => $resultado['consumo_diario']['agua_litros'] * $precioAgua,
                    'total' => ($resultado['consumo_diario']['alimento_kg'] * $precioAlimento) +
                        ($resultado['consumo_diario']['agua_litros'] * $precioAgua)
                ],
                'semanal' => [
                    'alimento' => $resultado['proyecciones']['semanal']['alimento_kg'] * $precioAlimento,
                    'agua' => $resultado['proyecciones']['semanal']['agua_litros'] * $precioAgua,
                    'total' => ($resultado['proyecciones']['semanal']['alimento_kg'] * $precioAlimento) +
                        ($resultado['proyecciones']['semanal']['agua_litros'] * $precioAgua)
                ],
                'mensual' => [
                    'alimento' => $resultado['proyecciones']['mensual']['alimento_kg'] * $precioAlimento,
                    'agua' => $resultado['proyecciones']['mensual']['agua_litros'] * $precioAgua,
                    'total' => ($resultado['proyecciones']['mensual']['alimento_kg'] * $precioAlimento) +
                        ($resultado['proyecciones']['mensual']['agua_litros'] * $precioAgua)
                ]
            ];
        }

        // Agrugar información adicional
        $resultado['tipo_ave_info'] = $tipoInfo;

        // Guardar en historial si hay usuario logueado
        if (Session::isAuthenticated()) {
            $etapaId = $this->calculadoraModel->determinarEtapaVida($tipoAve, $edadDias);
            $this->calculadoraModel->guardarHistorial(
                Session::getUserId(),
                $tipoAve,
                $etapaId['id_etapa'],
                $cantidad,
                $edadDias,
                $resultado
            );
        }

        $this->json($resultado);
    }

    /**
     * Comparador entre tipos de ave
     */
    public function comparar()
    {
        $tiposAve = $this->calculadoraModel->getTiposAve();

        $comparacion = [];
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $tipos = $_POST['tipos'] ?? [];
            $cantidad = intval($_POST['cantidad'] ?? 100);
            $edad = intval($_POST['edad_dias'] ?? 30);

            foreach ($tipos as $tipoId) {
                $resultado = $this->calculadoraModel->calcular($tipoId, $cantidad, $edad);
                if (!isset($resultado['error'])) {
                    $comparacion[] = $resultado;
                }
            }
        }

        $data = [
            'title' => 'Comparador de Tipos de Ave - ' . APP_NAME,
            'tipos_ave' => $tiposAve,
            'comparacion' => $comparacion
        ];

        $this->view('calculadora/comparar', $data);
    }

    /**
     * Proyecciones a largo plazo
     */
    public function proyecciones()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['error' => 'Método no permitido'], 405);
        }

        $tipoAve = intval($_POST['tipo_ave'] ?? 0);
        $cantidad = intval($_POST['cantidad'] ?? 0);
        $edadInicial = intval($_POST['edad_inicial'] ?? 0);
        $periodosDias = intval($_POST['periodos_dias'] ?? 90);

        $proyecciones = [];

        // Generar proyecciones semanales
        for ($dia = $edadInicial; $dia <= $edadInicial + $periodosDias; $dia += 7) {
            $resultado = $this->calculadoraModel->calcular($tipoAve, $cantidad, $dia);
            if (!isset($resultado['error'])) {
                $proyecciones[] = [
                    'dia' => $dia,
                    'edad_semanas' => round($dia / 7, 1),
                    'etapa' => $resultado['datos_entrada']['nombre_etapa'],
                    'alimento_semanal_kg' => $resultado['proyecciones']['semanal']['alimento_kg'],
                    'agua_semanal_litros' => $resultado['proyecciones']['semanal']['agua_litros'],
                    'alimento_acumulado_kg' => 0, // Se calculará después
                    'agua_acumulada_litros' => 0
                ];
            }
        }

        // Calcular acumulados
        $acumAlimento = 0;
        $acumAgua = 0;
        foreach ($proyecciones as &$p) {
            $acumAlimento += $p['alimento_semanal_kg'];
            $acumAgua += $p['agua_semanal_litros'];
            $p['alimento_acumulado_kg'] = round($acumAlimento, 2);
            $p['agua_acumulada_litros'] = round($acumAgua, 2);
        }

        $this->json([
            'proyecciones' => $proyecciones,
            'resumen' => [
                'total_alimento_kg' => round($acumAlimento, 2),
                'total_agua_litros' => round($acumAgua, 2),
                'periodo_dias' => $periodosDias,
                'cantidad_aves' => $cantidad
            ]
        ]);
    }

    /**
     * Ver historial completo
     */
    public function historial()
    {
        $this->requireAuth();

        $historial = $this->calculadoraModel->getHistorial(Session::getUserId(), 50);

        $data = [
            'title' => 'Historial de Cálculos - ' . APP_NAME,
            'historial' => $historial
        ];

        $this->view('calculadora/historial', $data);
    }

    /**
     * Exportar cálculo a PDF
     */
    public function exportar($id)
    {
        $this->requireAuth();

        $calculo = $this->calculadoraModel->getCalculoById($id);

        if (!$calculo || $calculo['id_usuario'] != Session::getUserId()) {
            $this->redirect('/error/404');
        }

        // TODO: Implementar generación de PDF
        $this->json(['mensaje' => 'Funcionalidad en desarrollo']);
    }

    /**
     * Recomendaciones nutricionales detalladas
     */
    public function recomendaciones()
    {
        $tipoAve = intval($_GET['tipo'] ?? 0);
        $etapa = intval($_GET['etapa'] ?? 0);

        if ($tipoAve <= 0) {
            $this->redirect('/calculadora');
        }

        $parametros = $this->calculadoraModel->getParametrosNutricionales($tipoAve, $etapa);
        $tipoInfo = $this->calculadoraModel->getTipoAve($tipoAve);

        $data = [
            'title' => 'Recomendaciones Nutricionales - ' . APP_NAME,
            'tipo_ave' => $tipoInfo,
            'parametros' => $parametros
        ];

        $this->view('calculadora/recomendaciones', $data);
    }
}
