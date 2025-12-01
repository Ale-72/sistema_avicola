<?php

/**
 * Controlador Home (Página Principal)
 */

require_once ROOT_PATH . '/core/Controller.php';
require_once ROOT_PATH . '/models/Producto.php';

class HomeController extends Controller
{
    private $productoModel;

    public function __construct()
    {
        parent::__construct();
        $this->productoModel = new Producto($this->db);
    }

    /**
     * Página de inicio
     */
    public function index()
    {
        // Obtener productos destacados
        $productosDestacados = $this->productoModel->getAll(['destacado' => 1], 8);

        $data = [
            'title' => 'Inicio - ' . APP_NAME,
            'productos_destacados' => $productosDestacados
        ];

        $this->view('home/index', $data);
    }
}
