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
        // Obtener usuarios de la base de datos
        try {
            $sql = "SELECT u.id_usuario, u.nombre_completo, u.email, u.telefono, 
                           u.activo, u.fecha_registro, r.nombre_rol
                    FROM usuarios u
                    LEFT JOIN roles r ON u.id_rol = r.id_rol
                    ORDER BY u.fecha_registro DESC";

            $stmt = $this->db->query($sql);
            $usuarios = $stmt->fetchAll();
        } catch (Exception $e) {
            error_log("Error obteniendo usuarios: " . $e->getMessage());
            $usuarios = [];
        }

        $data = [
            'title' => 'Gestión de Usuarios - ' . APP_NAME,
            'usuarios' => $usuarios
        ];

        $this->view('admin/usuarios', $data);
    }

    /**
     * Obtener datos de un usuario específico (AJAX)
     */
    public function obtenerUsuario()
    {
        header('Content-Type: application/json');

        if (!isset($_GET['id'])) {
            echo json_encode(['success' => false, 'message' => 'ID no proporcionado']);
            return;
        }

        try {
            $sql = "SELECT u.*, r.nombre_rol 
                    FROM usuarios u
                    LEFT JOIN roles r ON u.id_rol = r.id_rol
                    WHERE u.id_usuario = ?";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([$_GET['id']]);
            $usuario = $stmt->fetch();

            if ($usuario) {
                echo json_encode(['success' => true, 'data' => $usuario]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Usuario no encontrado']);
            }
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
    }

                    $data['activo'] ?? 1
                ];

                $stmt = $this->db->prepare($sql);
                $stmt->execute($params);

                echo json_encode(['success' => true, 'message' => 'Usuario creado correctamente']);
            }
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
    }

    /**
     * Cambiar estado de un usuario (AJAX)
     */
    public function cambiarEstadoUsuario()
    {
        header('Content-Type: application/json');

        try {
            $data = json_decode(file_get_contents('php://input'), true);

            if (empty($data['id_usuario'])) {
                echo json_encode(['success' => false, 'message' => 'ID no proporcionado']);
                return;
            }

            $nuevoEstado = $data['nuevo_estado'] ?? 0;

            $sql = "UPDATE usuarios SET activo = ? WHERE id_usuario = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$nuevoEstado, $data['id_usuario']]);

            $mensaje = $nuevoEstado == 1 ? 'Usuario activado' : 'Usuario desactivado';
            echo json_encode(['success' => true, 'message' => $mensaje]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
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

    /**
     * Gestión de sucursales
     */
    public function sucursales()
    {
        $data = [
            'title' => 'Gestión de Sucursales - ' . APP_NAME,
            'sucursales' => []
        ];

        $this->view('admin/sucursales', $data);
    }

    /**
     * Gestión de inventario
     */
    public function inventario()
    {
        $data = [
            'title' => 'Gestión de Inventario - ' . APP_NAME,
            'productos' => []
        ];

        $this->view('admin/inventario', $data);
    }

    /**
     * Reportes del sistema
     */
    public function reportes()
    {
        $data = [
            'title' => 'Reportes - ' . APP_NAME
        ];

        $this->view('admin/reportes', $data);
    }
}
