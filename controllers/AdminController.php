<?php

/**
 * Controlador de Administración
 */

require_once ROOT_PATH . '/core/Controller.php';
require_once ROOT_PATH . '/models/Usuario.php';
require_once ROOT_PATH . '/models/Producto.php';
require_once ROOT_PATH . '/models/Pedido.php';

class AdminController extends Controller
{
    private $usuarioModel;
    private $productoModel;
    private $pedidoModel;

    public function __construct()
    {
        parent::__construct();

        // Verificar autenticación y rol
        if (!Session::isAuthenticated()) {
            $this->redirect('auth/login');
        }

        if (Session::getUserRole() !== 'Administrador') {
            Session::setFlash('error', 'No tienes permisos para acceder a esta sección');
            $this->redirect('');
        }

        $this->usuarioModel = new Usuario($this->db);
        $this->productoModel = new Producto($this->db);
        $this->pedidoModel = new Pedido($this->db);
    }

    /**
     * Dashboard principal de administración
     */
    public function dashboard()
    {
        // Obtener estadísticas (usando valores por defecto para evitar errores)
        $data = [
            'title' => 'Dashboard Administrativo - ' . APP_NAME,
            'total_usuarios' => 0,
            'nuevos_usuarios_mes' => 0,
            'total_pedidos' => 0,
            'pedidos_pendientes' => 0,
            'total_productos' => 0,
            'productos_bajo_stock' => 0,
            'ventas_mes' => 0,
            'pedidos_recientes' => [],
        ];

        // Intentar obtener datos reales si los métodos existen
        try {
            // Contar usuarios
            $stmt = $this->db->query("SELECT COUNT(*) as total FROM usuarios WHERE activo = 1");
            $result = $stmt->fetch();
            $data['total_usuarios'] = $result['total'] ?? 0;

            // Contar productos
            $stmt = $this->db->query("SELECT COUNT(*) as total FROM productos WHERE activo = 1");
            $result = $stmt->fetch();
            $data['total_productos'] = $result['total'] ?? 0;

            // Contar pedidos
            $stmt = $this->db->query("SELECT COUNT(*) as total FROM pedidos");
            $result = $stmt->fetch();
            $data['total_pedidos'] = $result['total'] ?? 0;

            // Ventas del mes
            $stmt = $this->db->query("SELECT COALESCE(SUM(total), 0) as ventas FROM pedidos WHERE MONTH(fecha_pedido) = MONTH(CURRENT_DATE)");
            $result = $stmt->fetch();
            $data['ventas_mes'] = $result['ventas'] ?? 0;
        } catch (Exception $e) {
            // Si hay error, usar valores por defecto
            error_log("Error obteniendo estadísticas: " . $e->getMessage());
        }

        $this->view('dashboard/admin', $data);
    }

    /**
     * Gestión de usuarios
     */
    public function usuarios()
    {
        $data = [
            'title' => 'Gestión de Usuarios - ' . APP_NAME,
            'usuarios' => []
        ];

        $this->view('admin/usuarios', $data);
    }

    /**
     * Gestión de productos
     */
    public function productos()
    {
        $data = [
            'title' => 'Gestión de Productos - ' . APP_NAME,
            'productos' => []
        ];

        $this->view('admin/productos', $data);
    }

    /**
     * Gestión de pedidos
     */
    public function pedidos()
    {
        $data = [
            'title' => 'Gestión de Pedidos - ' . APP_NAME,
            'pedidos' => []
        ];

        $this->view('admin/pedidos', $data);
    }
}
