<?php

/**
 * Controlador de Tienda - Versión Completa
 */

require_once ROOT_PATH . '/core/Controller.php';
require_once ROOT_PATH . '/models/Producto.php';
require_once ROOT_PATH . '/models/Sucursal.php';

class TiendaController extends Controller
{
    private $productoModel;
    private $sucursalModel;

    public function __construct()
    {
        parent::__construct();
        $this->productoModel = new Producto($this->db);
        $this->sucursalModel = new Sucursal($this->db);
    }

    /**
     * Página principal de la tienda con filtros
     */
    public function index()
    {
        // Obtener parámetros de búsqueda y filtros
        $busqueda = $this->sanitize($_GET['q'] ?? '');
        $categoria = intval($_GET['categoria'] ?? 0);
        $ordenar = $this->sanitize($_GET['orden'] ?? 'recientes');
        $precioMin = floatval($_GET['precio_min'] ?? 0);
        $precioMax = floatval($_GET['precio_max'] ?? 0);

        $filtros = [];
        if ($categoria > 0) {
            $filtros['id_categoria'] = $categoria;
        }
        if ($busqueda) {
            $filtros['busqueda'] = $busqueda;
        }
        if ($precioMin > 0) {
            $filtros['precio_min'] = $precioMin;
        }
        if ($precioMax > 0) {
            $filtros['precio_max'] = $precioMax;
        }

        // Determinar ordenamiento
        $order = match ($ordenar) {
            'precio_asc' => 'precio_unitario ASC',
            'precio_desc' => 'precio_unitario DESC',
            'nombre' => 'nombre_producto ASC',
            default => 'p.fecha_creacion DESC'
        };

        $filtros['order'] = $order;

        // Obtener productos
        $productos = $this->productoModel->getAll($filtros, 12);

        // Obtener categorías para filtros
        $categorias = $this->productoModel->getCategorias();

        $data = [
            'title' => 'Tienda - ' . APP_NAME,
            'productos' => $productos,
            'categorias' => $categorias,
            'filtros_actuales' => [
                'busqueda' => $busqueda,
                'categoria' => $categoria,
                'orden' => $ordenar,
                'precio_min' => $precioMin,
                'precio_max' => $precioMax
            ]
        ];

        $this->view('tienda/index', $data);
    }

    /**
     * Detalle de producto individual
     */
    public function detalle($slug)
    {
        $producto = $this->productoModel->getBySlug($slug);

        if (!$producto) {
            $this->redirect('/error/404');
        }

        // Obtener imágenes del producto
        $imagenes = $this->productoModel->getImagenes($producto['id_producto']);

        // Obtener atributos
        $atributos = $this->productoModel->getAtributos($producto['id_producto']);

        // Verificar stock en sucursales
        $stockSucursales = $this->productoModel->getStockPorSucursal($producto['id_producto']);

        // Productos relacionados
        $relacionados = $this->productoModel->getRelacionados(
            $producto['id_producto'],
            $producto['id_categoria'],
            4
        );

        $data = [
            'title' => $producto['nombre_producto'] . ' - ' . APP_NAME,
            'producto' => $producto,
            'imagenes' => $imagenes,
            'atributos' => $atributos,
            'stock_sucursales' => $stockSucursales,
            'relacionados' => $relacionados
        ];

        $this->view('tienda/detalle', $data);
    }

    /**
     * Comparador de precios entre sucursales
     */
    public function comparador($id_producto)
    {
        $producto = $this->productoModel->getById($id_producto);

        if (!$producto) {
            $this->json(['error' => 'Producto no encontrado'], 404);
        }

        $sucursales = $this->sucursalModel->getAll();
        $comparacion = [];

        foreach ($sucursales as $sucursal) {
            $stock = $this->productoModel->getStockEnSucursal($id_producto, $sucursal['id_sucursal']);

            if ($stock && $stock['stock_disponible'] > 0) {
                $comparacion[] = [
                    'sucursal' => $sucursal['nombre_sucursal'],
                    'direccion' => $sucursal['direccion'],
                    'stock' => $stock['stock_disponible'],
                    'precio' => $producto['precio_unitario'],
                    'disponible' => true
                ];
            }
        }

        $this->json([
            'producto' => $producto['nombre_producto'],
            'comparacion' => $comparacion
        ]);
    }

    /**
     * Agregar al carrito (AJAX)
     */
    public function agregarCarrito()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['error' => 'Método no permitido'], 405);
        }

        $idProducto = intval($_POST['id_producto'] ?? 0);
        $cantidad = intval($_POST['cantidad'] ?? 1);
        $idSucursal = intval($_POST['id_sucursal'] ?? 0);

        if ($idProducto <= 0 || $cantidad <= 0) {
            $this->json(['error' => 'Datos inválidos'], 400);
        }

        // Verificar stock
        $stock = $this->productoModel->getStockEnSucursal($idProducto, $idSucursal);

        if (!$stock || $stock['stock_disponible'] < $cantidad) {
            $this->json(['error' => 'Stock insuficiente'], 400);
        }

        // Obtener o crear carrito en sesión
        $carrito = Session::get('carrito') ?? [];

        $key = $idProducto . '_' . $idSucursal;

        if (isset($carrito[$key])) {
            $carrito[$key]['cantidad'] += $cantidad;
        } else {
            $producto = $this->productoModel->getById($idProducto);
            $carrito[$key] = [
                'id_producto' => $idProducto,
                'id_sucursal' => $idSucursal,
                'nombre' => $producto['nombre_producto'],
                'precio' => $producto['precio_unitario'],
                'cantidad' => $cantidad,
                'imagen' => $producto['imagen_principal']
            ];
        }

        Session::set('carrito', $carrito);

        $totalItems = array_sum(array_column($carrito, 'cantidad'));

        $this->json([
            'success' => true,
            'mensaje' => 'Producto agregado al carrito',
            'total_items' => $totalItems
        ]);
    }

    /**
     * Ver carrito
     */
    public function carrito()
    {
        $carrito = Session::get('carrito') ?? [];

        $total = 0;
        foreach ($carrito as &$item) {
            $item['subtotal'] = $item['precio'] * $item['cantidad'];
            $total += $item['subtotal'];
        }

        $data = [
            'title' => 'Carrito - ' . APP_NAME,
            'carrito' => $carrito,
            'total' => $total
        ];

        $this->view('tienda/carrito', $data);
    }

    /**
     * Actualizar cantidad en carrito
     */
    public function actualizarCarrito()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['error' => 'Método no permitido'], 405);
        }

        $key = $this->sanitize($_POST['key'] ?? '');
        $cantidad = intval($_POST['cantidad'] ?? 0);

        $carrito = Session::get('carrito') ?? [];

        if (isset($carrito[$key])) {
            if ($cantidad <= 0) {
                unset($carrito[$key]);
            } else {
                $carrito[$key]['cantidad'] = $cantidad;
            }

            Session::set('carrito', $carrito);

            $this->json(['success' => true]);
        } else {
            $this->json(['error' => 'Item no encontrado'], 404);
        }
    }

    /**
     * Eliminar del carrito
     */
    public function eliminarDelCarrito()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['error' => 'Método no permitido'], 405);
        }

        $key = $this->sanitize($_POST['key'] ?? '');

        $carrito = Session::get('carrito') ?? [];

        if (isset($carrito[$key])) {
            unset($carrito[$key]);
            Session::set('carrito', $carrito);

            $this->json(['success' => true]);
        } else {
            $this->json(['error' => 'Item no encontrado'], 404);
        }
    }
}
