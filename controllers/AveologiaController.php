<?php

/**
 * Controlador de Aveología - Versión Completa
 */

require_once ROOT_PATH . '/core/Controller.php';
require_once ROOT_PATH . '/models/Aveologia.php';

class AveologiaController extends Controller
{
    private $aveologiaModel;

    public function __construct()
    {
        parent::__construct();
        $this->aveologiaModel = new Aveologia($this->db);
    }

    /**
     * Página principal de Aveología
     */
    public function index()
    {
        $categorias = $this->aveologiaModel->getCategorias();
        $articulosDestacados = $this->aveologiaModel->getArticulos(['destacado' => 1], 6);
        $enfermedadesComunes = $this->aveologiaModel->getEnfermedadesMasComunes(5);

        $data = [
            'title' => 'Aveología - Base de Conocimientos - ' . APP_NAME,
            'categorias' => $categorias,
            'articulos' => $articulosDestacados,
            'enfermedades_comunes' => $enfermedadesComunes
        ];

        $this->view('aveologia/index', $data);
    }

    /**
     * Buscador de síntomas y diagnóstico
     */
    public function diagnostico()
    {
        $sintomas = $this->aveologiaModel->getAllSintomas();

        $sintomasSeleccionados = [];
        $enfermedadesPosibles = [];
        $intensidad = [];
        $frecuencia = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $sintomasSeleccionados = $_POST['sintomas'] ?? [];
            $intensidad = $_POST['intensidad'] ?? [];
            $frecuencia = $_POST['frecuencia'] ?? [];

            if (!empty($sintomasSeleccionados)) {
                // Buscar enfermedades con datos adicionales
                $enfermedadesPosibles = $this->aveologiaModel->buscarEnfermedadesPorSintomas(
                    $sintomasSeleccionados,
                    $intensidad,
                    $frecuencia
                );

                // Ordenar por coincidencia
                usort($enfermedadesPosibles, function ($a, $b) {
                    return $b['coincidencias'] <=> $a['coincidencias'];
                });
            }
        }

        $data = [
            'title' => 'Diagnóstico por Síntomas - ' . APP_NAME,
            'sintomas' => $sintomas,
            'sintomas_seleccionados' => $sintomasSeleccionados,
            'enfermedades_posibles' => $enfermedadesPosibles,
            'intensidad' => $intensidad,
            'frecuencia' => $frecuencia
        ];

        $this->view('aveologia/diagnostico', $data);
    }

    /**
     * Detalle de enfermedad
     */
    public function enfermedad($id)
    {
        $enfermedad = $this->aveologiaModel->getEnfermedad($id);

        if (!$enfermedad) {
            $this->redirect('/error/404');
        }

        // Obtener síntomas asociados
        $sintomas = $this->aveologiaModel->getSintomasDeEnfermedad($id);

        // Obtener tratamientos
        $tratamientos = $this->aveologiaModel->getTratamientos($id);

        // Obtener remedios
        $remedios = $this->aveologiaModel->getRemedios($id);

        $data = [
            'title' => $enfermedad['nombre_enfermedad'] . ' - Aveología - ' . APP_NAME,
            'enfermedad' => $enfermedad,
            'sintomas' => $sintomas,
            'tratamientos' => $tratamientos,
            'remedios' => $remedios
        ];

        $this->view('aveologia/enfermedad', $data);
    }

    /**
     * Artículos de conocimiento
     */
    public function articulos()
    {
        $categoria = intval($_GET['categoria'] ?? 0);
        $busqueda = $this->sanitize($_GET['q'] ?? '');

        $filtros = [];
        if ($categoria > 0) {
            $filtros['id_categoria'] = $categoria;
        }
        if ($busqueda) {
            $filtros['busqueda'] = $busqueda;
        }

        $articulos = $this->aveologiaModel->getArticulos($filtros, 12);
        $categorias = $this->aveologiaModel->getCategorias();

        $data = [
            'title' => 'Artículos de Aveología - ' . APP_NAME,
            'articulos' => $articulos,
            'categorias' => $categorias,
            'categoria_actual' => $categoria,
            'busqueda' => $busqueda
        ];

        $this->view('aveologia/articulos', $data);
    }

    /**
     * Detalle de artículo
     */
    public function articulo($slug)
    {
        $articulo = $this->aveologiaModel->getArticuloBySlug($slug);

        if (!$articulo) {
            $this->redirect('/error/404');
        }

        // Incrementar vistas
        $this->aveologiaModel->incrementarVistas($articulo['id_articulo']);

        // Artículos relacionados
        $relacionados = $this->aveologiaModel->getArticulos([
            'id_categoria' => $articulo['id_categoria'],
            'excluir' => $articulo['id_articulo']
        ], 3);

        $data = [
            'title' => $articulo['titulo'] . ' - ' . APP_NAME,
            'articulo' => $articulo,
            'relacionados' => $relacionados
        ];

        $this->view('aveologia/articulo_detalle', $data);
    }

    /**
     * Listado de enfermedades
     */
    public function enfermedades()
    {
        $tipo = $this->sanitize($_GET['tipo'] ?? '');
        $busqueda = $this->sanitize($_GET['q'] ?? '');

        $filtros = [];
        if ($tipo) {
            $filtros['tipo'] = $tipo;
        }
        if ($busqueda) {
            $filtros['busqueda'] = $busqueda;
        }

        $enfermedades = $this->aveologiaModel->getAllEnfermedades($filtros);

        $data = [
            'title' => 'Enfermedades Avícolas - ' . APP_NAME,
            'enfermedades' => $enfermedades,
            'tipo_actual' => $tipo,
            'busqueda' => $busqueda
        ];

        $this->view('aveologia/enfermedades', $data);
    }

    /**
     * Guía de prevención
     */
    public function prevencion()
    {
        $guiasPrev = $this->aveologiaModel->getArticulos([
            'tipo' => 'prevencion'
        ], 20);

        $data = [
            'title' => 'Guías de Prevención - ' . APP_NAME,
            'guias' => $guiasPrev
        ];

        $this->view('aveologia/prevencion', $data);
    }

    /**
     * Búsqueda general (AJAX)
     */
    public function buscar()
    {
        $termino = $this->sanitize($_GET['q'] ?? '');

        if (strlen($termino) < 3) {
            $this->json(['error' => 'Mínimo 3 caracteres'], 400);
        }

        $sintomas = $this->aveologiaModel->buscarSintomas($termino);
        $enfermedades = $this->aveologiaModel->getAllEnfermedades(['busqueda' => $termino]);
        $articulos = $this->aveologiaModel->getArticulos(['busqueda' => $termino], 5);

        $this->json([
            'sintomas' => $sintomas,
            'enfermedades' => array_slice($enfermedades, 0, 5),
            'articulos' => $articulos
        ]);
    }
}
