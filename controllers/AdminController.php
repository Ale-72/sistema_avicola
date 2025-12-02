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

    /**
     * Guardar nuevo usuario o actualizar existente (AJAX)
     */
    public function guardarUsuario()
    {
        header('Content-Type: application/json');

        try {
            $data = json_decode(file_get_contents('php://input'), true);

            // Validaciones
            if (empty($data['nombre_completo']) || empty($data['email'])) {
                echo json_encode(['success' => false, 'message' => 'Nombre y email son obligatorios']);
                return;
            }

            // Verificar si es actualización o creación
            $isUpdate = !empty($data['id_usuario']);

            if ($isUpdate) {
                // Actualizar usuario existente
                $sql = "UPDATE usuarios SET 
                        nombre_completo = ?, 
                        email = ?, 
                        telefono = ?, 
                        id_rol = ?, 
                        activo = ?";

                $params = [
                    $data['nombre_completo'],
                    $data['email'],
                    $data['telefono'] ?? null,
                    $data['id_rol'],
                    $data['activo'] ?? 1
                ];

                // Si se proporcionó nueva contraseña, actualizar también
                if (!empty($data['password'])) {
                    $sql .= ", password_hash = ?";
                    $params[] = password_hash($data['password'], PASSWORD_BCRYPT);
                }

                $sql .= " WHERE id_usuario = ?";
                $params[] = $data['id_usuario'];

                $stmt = $this->db->prepare($sql);
                $stmt->execute($params);

                echo json_encode(['success' => true, 'message' => 'Usuario actualizado correctamente']);
            } else {
                // Crear nuevo usuario
                if (empty($data['password'])) {
                    echo json_encode(['success' => false, 'message' => 'La contraseña es obligatoria para nuevos usuarios']);
                    return;
                }

                // Verificar si el email ya existe
                $stmt = $this->db->prepare("SELECT COUNT(*) FROM usuarios WHERE email = ?");
                $stmt->execute([$data['email']]);
                if ($stmt->fetchColumn() > 0) {
                    echo json_encode(['success' => false, 'message' => 'El email ya está registrado']);
                    return;
                }

                $sql = "INSERT INTO usuarios (nombre_completo, email, password_hash, telefono, id_rol, activo, fecha_registro) 
                        VALUES (?, ?, ?, ?, ?, ?, NOW())";

                $params = [
                    $data['nombre_completo'],
                    $data['email'],
                    password_hash($data['password'], PASSWORD_BCRYPT),
                    $data['telefono'] ?? null,
                    $data['id_rol'],
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
        // Obtener productos de la base de datos
        try {
            $sql = "SELECT p.*, c.nombre_categoria 
                    FROM productos p
                    LEFT JOIN categorias_producto c ON p.id_categoria_producto = c.id_categoria_producto
                    ORDER BY p.fecha_creacion DESC";

            $stmt = $this->db->query($sql);
            $productos = $stmt->fetchAll();
        } catch (Exception $e) {
            error_log("Error obteniendo productos: " . $e->getMessage());
            $productos = [];
        }

        $data = [
            'title' => 'Gestión de Productos - ' . APP_NAME,
            'productos' => $productos
        ];

        $this->view('admin/productos', $data);
    }

    /**
     * Obtener datos de un producto específico (AJAX)
     */
    public function obtenerProducto()
    {
        header('Content-Type: application/json');

        if (!isset($_GET['id'])) {
            echo json_encode(['success' => false, 'message' => 'ID no proporcionado']);
            return;
        }

        try {
            $sql = "SELECT p.*, c.nombre_categoria 
                    FROM productos p
                    LEFT JOIN categorias_producto c ON p.id_categoria_producto = c.id_categoria_producto
                    WHERE p.id_producto = ?";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([$_GET['id']]);
            $producto = $stmt->fetch();

            if ($producto) {
                echo json_encode(['success' => true, 'data' => $producto]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Producto no encontrado']);
            }
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
    }

    /**
     * Guardar nuevo producto o actualizar existente (AJAX)
     */
    public function guardarProducto()
    {
        header('Content-Type: application/json');

        try {
            $data = json_decode(file_get_contents('php://input'), true);

            // Validaciones
            if (empty($data['nombre_producto']) || empty($data['precio_unitario'])) {
                echo json_encode(['success' => false, 'message' => 'Nombre y precio son obligatorios']);
                return;
            }

            // Verificar si es actualización o creación
            $isUpdate = !empty($data['id_producto']);

            if ($isUpdate) {
                // Actualizar producto existente
                $sql = "UPDATE productos SET 
                        nombre_producto = ?, 
                        codigo_producto = ?,
                        id_categoria_producto = ?,
                        descripcion_corta = ?,
                        precio_unitario = ?,
                        precio_oferta = ?,
                        unidad_medida = ?,
                        stock_total = ?,
                        stock_minimo = ?,
                        destacado = ?,
                        activo = ?
                        WHERE id_producto = ?";

                $params = [
                    $data['nombre_producto'],
                    $data['codigo_producto'] ?? null,
                    $data['id_categoria_producto'],
                    $data['descripcion_corta'] ?? null,
                    $data['precio_unitario'],
                    !empty($data['precio_oferta']) ? $data['precio_oferta'] : null,
                    $data['unidad_medida'] ?? 'unidad',
                    $data['stock_total'] ?? 0,
                    $data['stock_minimo'] ?? 5,
                    $data['destacado'] ?? 0,
                    $data['activo'] ?? 1,
                    $data['id_producto']
                ];

                $stmt = $this->db->prepare($sql);
                $stmt->execute($params);

                echo json_encode(['success' => true, 'message' => 'Producto actualizado correctamente']);
            } else {
                // Crear nuevo producto
                // Generar slug automáticamente
                $slug = strtolower(str_replace(' ', '-', $data['nombre_producto']));
                $slug = preg_replace('/[^a-z0-9-]/', '', $slug);

                // Verificar si el código ya existe
                if (!empty($data['codigo_producto'])) {
                    $stmt = $this->db->prepare("SELECT COUNT(*) FROM productos WHERE codigo_producto = ?");
                    $stmt->execute([$data['codigo_producto']]);
                    if ($stmt->fetchColumn() > 0) {
                        echo json_encode(['success' => false, 'message' => 'El código de producto ya existe']);
                        return;
                    }
                }

                $sql = "INSERT INTO productos (nombre_producto, codigo_producto, slug, id_categoria_producto, 
                        descripcion_corta, precio_unitario, precio_oferta, unidad_medida, stock_total, 
                        stock_minimo, destacado, activo, fecha_creacion) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";

                $params = [
                    $data['nombre_producto'],
                    $data['codigo_producto'] ?? null,
                    $slug,
                    $data['id_categoria_producto'],
                    $data['descripcion_corta'] ?? null,
                    $data['precio_unitario'],
                    !empty($data['precio_oferta']) ? $data['precio_oferta'] : null,
                    $data['unidad_medida'] ?? 'unidad',
                    $data['stock_total'] ?? 0,
                    $data['stock_minimo'] ?? 5,
                    $data['destacado'] ?? 0,
                    $data['activo'] ?? 1
                ];

                $stmt = $this->db->prepare($sql);
                $stmt->execute($params);

                echo json_encode(['success' => true, 'message' => 'Producto creado correctamente']);
            }
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
    }

    /**
     * Cambiar estado de un producto (AJAX)
     */
    public function cambiarEstadoProducto()
    {
        header('Content-Type: application/json');

        try {
            $data = json_decode(file_get_contents('php://input'), true);

            if (empty($data['id_producto'])) {
                echo json_encode(['success' => false, 'message' => 'ID no proporcionado']);
                return;
            }

            $nuevoEstado = $data['nuevo_estado'] ?? 0;

            $sql = "UPDATE productos SET activo = ? WHERE id_producto = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$nuevoEstado, $data['id_producto']]);

            $mensaje = $nuevoEstado == 1 ? 'Producto activado' : 'Producto desactivado';
            echo json_encode(['success' => true, 'message' => $mensaje]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
    }

    /**
     * Obtener categorías de productos (AJAX)
     */
    public function obtenerCategorias()
    {
        header('Content-Type: application/json');

        try {
            $sql = "SELECT id_categoria_producto, nombre_categoria 
                    FROM categorias_producto 
                    WHERE activo = 1 
                    ORDER BY nombre_categoria ASC";

            $stmt = $this->db->query($sql);
            $categorias = $stmt->fetchAll();

            echo json_encode(['success' => true, 'data' => $categorias]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
    }

    /**
     * Gestión de pedidos
     */
    public function pedidos()
    {
        // Obtener pedidos de la base de datos con todos los joins necesarios
        try {
            $sql = "SELECT p.*, 
                           u.nombre_completo as cliente_nombre,
                           u.email as cliente_email,
                           e.nombre_estado,
                           e.color_hex as estado_color,
                           s.nombre_sucursal,
                           m.nombre_metodo as metodo_entrega_nombre
                    FROM pedidos p
                    INNER JOIN usuarios u ON p.id_cliente = u.id_usuario
                    INNER JOIN estados_pedido e ON p.id_estado = e.id_estado
                    LEFT JOIN sucursales s ON p.id_sucursal = s.id_sucursal
                    LEFT JOIN metodos_entrega m ON p.id_metodo_entrega = m.id_metodo_entrega
                    ORDER BY p.fecha_pedido DESC";

            $stmt = $this->db->query($sql);
            $pedidos = $stmt->fetchAll();
        } catch (Exception $e) {
            error_log("Error obteniendo pedidos: " . $e->getMessage());
            $pedidos = [];
        }

        $data = [
            'title' => 'Gestión de Pedidos - ' . APP_NAME,
            'pedidos' => $pedidos
        ];

        $this->view('admin/pedidos', $data);
    }

    /**
     * Obtener datos completos de un pedido específico (AJAX)
     */
    public function obtenerPedido()
    {
        header('Content-Type: application/json');

        if (!isset($_GET['id'])) {
            echo json_encode(['success' => false, 'message' => 'ID no proporcionado']);
            return;
        }

        try {
            $sql = "SELECT p.*, 
                           u.nombre_completo as cliente_nombre,
                           u.email as cliente_email,
                           u.telefono as cliente_telefono,
                           e.nombre_estado,
                           e.color_hex as estado_color,
                           s.nombre_sucursal,
                           s.direccion_completa as sucursal_direccion,
                           m.nombre_metodo as metodo_entrega_nombre
                    FROM pedidos p
                    INNER JOIN usuarios u ON p.id_cliente = u.id_usuario
                    INNER JOIN estados_pedido e ON p.id_estado = e.id_estado
                    LEFT JOIN sucursales s ON p.id_sucursal = s.id_sucursal
                    LEFT JOIN metodos_entrega m ON p.id_metodo_entrega = m.id_metodo_entrega
                    WHERE p.id_pedido = ?";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([$_GET['id']]);
            $pedido = $stmt->fetch();

            if ($pedido) {
                echo json_encode(['success' => true, 'data' => $pedido]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Pedido no encontrado']);
            }
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
    }

    /**
     * Obtener detalle de productos de un pedido (AJAX)
     */
    public function obtenerDetallePedido()
    {
        header('Content-Type: application/json');

        if (!isset($_GET['id'])) {
            echo json_encode(['success' => false, 'message' => 'ID no proporcionado']);
            return;
        }

        try {
            $sql = "SELECT dp.*, 
                           pr.nombre_producto,
                           pr.codigo_producto
                    FROM detalle_pedido dp
                    INNER JOIN productos pr ON dp.id_producto = pr.id_producto
                    WHERE dp.id_pedido = ?
                    ORDER BY dp.id_detalle ASC";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([$_GET['id']]);
            $productos = $stmt->fetchAll();

            echo json_encode(['success' => true, 'data' => $productos]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
    }

    /**
     * Cambiar estado de un pedido (AJAX)
     */
    public function cambiarEstadoPedido()
    {
        header('Content-Type: application/json');

        try {
            $data = json_decode(file_get_contents('php://input'), true);

            if (empty($data['id_pedido']) || empty($data['id_estado'])) {
                echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
                return;
            }

            // Verificar que el pedido no esté en estado final
            $stmt = $this->db->prepare("SELECT e.es_final FROM pedidos p 
                                        INNER JOIN estados_pedido e ON p.id_estado = e.id_estado 
                                        WHERE p.id_pedido = ?");
            $stmt->execute([$data['id_pedido']]);
            $estadoActual = $stmt->fetch();

            if ($estadoActual && $estadoActual['es_final'] == 1) {
                echo json_encode(['success' => false, 'message' => 'No se puede cambiar el estado de un pedido finalizado']);
                return;
            }

            // Actualizar estado del pedido
            $sql = "UPDATE pedidos SET id_estado = ? WHERE id_pedido = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$data['id_estado'], $data['id_pedido']]);

            // Registrar en historial
            $sqlHistorial = "INSERT INTO historial_pedido (id_pedido, id_estado, id_usuario, comentario, fecha_cambio) 
                            VALUES (?, ?, ?, ?, NOW())";
            $stmtHistorial = $this->db->prepare($sqlHistorial);
            $stmtHistorial->execute([
                $data['id_pedido'],
                $data['id_estado'],
                Session::getUserId(),
                $data['comentario'] ?? null
            ]);

            echo json_encode(['success' => true, 'message' => 'Estado del pedido actualizado correctamente']);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
    }

    /**
     * Obtener lista de estados de pedido (AJAX)
     */
    public function obtenerEstados()
    {
        header('Content-Type: application/json');

        try {
            $sql = "SELECT id_estado, nombre_estado, color_hex, orden 
                    FROM estados_pedido 
                    WHERE activo = 1 
                    ORDER BY orden ASC";

            $stmt = $this->db->query($sql);
            $estados = $stmt->fetchAll();

            echo json_encode(['success' => true, 'data' => $estados]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
    }

    /**
     * Obtener historial de cambios de estado de un pedido (AJAX)
     */
    public function obtenerHistorialPedido()
    {
        header('Content-Type: application/json');

        if (!isset($_GET['id'])) {
            echo json_encode(['success' => false, 'message' => 'ID no proporcionado']);
            return;
        }

        try {
            $sql = "SELECT h.*, 
                           e.nombre_estado,
                           e.color_hex,
                           u.nombre_completo as usuario_nombre
                    FROM historial_pedido h
                    INNER JOIN estados_pedido e ON h.id_estado = e.id_estado
                    LEFT JOIN usuarios u ON h.id_usuario = u.id_usuario
                    WHERE h.id_pedido = ?
                    ORDER BY h.fecha_cambio DESC";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([$_GET['id']]);
            $historial = $stmt->fetchAll();

            echo json_encode(['success' => true, 'data' => $historial]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
    }

    /**
     * Gestión de sucursales
     */
    public function sucursales()
    {
        // Obtener sucursales de la base de datos con encargados
        try {
            $sql = "SELECT s.*, 
                           u.nombre_completo as encargado_nombre
                    FROM sucursales s
                    LEFT JOIN usuarios u ON s.id_encargado = u.id_usuario
                    ORDER BY s.fecha_creacion DESC";

            $stmt = $this->db->query($sql);
            $sucursales = $stmt->fetchAll();
        } catch (Exception $e) {
            error_log("Error obteniendo sucursales: " . $e->getMessage());
            $sucursales = [];
        }

        $data = [
            'title' => 'Gestión de Sucursales - ' . APP_NAME,
            'sucursales' => $sucursales
        ];

        $this->view('admin/sucursales', $data);
    }

    /**
     * Obtener datos de una sucursal específica (AJAX)
     */
    public function obtenerSucursal()
    {
        header('Content-Type: application/json');

        if (!isset($_GET['id'])) {
            echo json_encode(['success' => false, 'message' => 'ID no proporcionado']);
            return;
        }

        try {
            $sql = "SELECT s.*, 
                           u.nombre_completo as encargado_nombre
                    FROM sucursales s
                    LEFT JOIN usuarios u ON s.id_encargado = u.id_usuario
                    WHERE s.id_sucursal = ?";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([$_GET['id']]);
            $sucursal = $stmt->fetch();

            if ($sucursal) {
                echo json_encode(['success' => true, 'data' => $sucursal]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Sucursal no encontrada']);
            }
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
    }

    /**
     * Guardar nueva sucursal o actualizar existente (AJAX)
     */
    public function guardarSucursal()
    {
        header('Content-Type: application/json');

        try {
            $data = json_decode(file_get_contents('php://input'), true);

            // Validaciones básicas
            if (
                empty($data['nombre_sucursal']) || empty($data['codigo_sucursal']) ||
                empty($data['ciudad']) || empty($data['direccion_completa'])
            ) {
                echo json_encode(['success' => false, 'message' => 'Campos obligatorios incompletos']);
                return;
            }

            // Validar coordenadas
            if (empty($data['latitud']) || empty($data['longitud'])) {
                echo json_encode(['success' => false, 'message' => 'Latitud y longitud son obligatorias']);
                return;
            }

            // Validar rango de coordenadas
            if ($data['latitud'] < -90 || $data['latitud'] > 90) {
                echo json_encode(['success' => false, 'message' => 'Latitud debe estar entre -90 y 90']);
                return;
            }

            if ($data['longitud'] < -180 || $data['longitud'] > 180) {
                echo json_encode(['success' => false, 'message' => 'Longitud debe estar entre -180 y 180']);
                return;
            }

            // Validar que tenga al menos un servicio
            if (empty($data['permite_delivery']) && empty($data['permite_pickup'])) {
                echo json_encode(['success' => false, 'message' => 'La sucursal debe ofrecer al menos un servicio (delivery o pickup)']);
                return;
            }

            // Si permite delivery, radio de cobertura es obligatorio
            if (!empty($data['permite_delivery']) && empty($data['radio_cobertura_km'])) {
                echo json_encode(['success' => false, 'message' => 'Radio de cobertura es obligatorio si permite delivery']);
                return;
            }

            // Verificar si es actualización o creación
            $isUpdate = !empty($data['id_sucursal']);

            if ($isUpdate) {
                // Actualizar sucursal existente
                $sql = "UPDATE sucursales SET 
                        codigo_sucursal = ?,
                        nombre_sucursal = ?,
                        id_encargado = ?,
                        direccion_completa = ?,
                        ciudad = ?,
                        departamento = ?,
                        codigo_postal = ?,
                        latitud = ?,
                        longitud = ?,
                        telefono = ?,
                        email = ?,
                        capacidad_almacenamiento = ?,
                        horario_apertura = ?,
                        horario_cierre = ?,
                        dias_atencion = ?,
                        permite_delivery = ?,
                        permite_pickup = ?,
                        radio_cobertura_km = ?,
                        fecha_apertura = ?,
                        activo = ?
                        WHERE id_sucursal = ?";

                $params = [
                    $data['codigo_sucursal'],
                    $data['nombre_sucursal'],
                    !empty($data['id_encargado']) ? $data['id_encargado'] : null,
                    $data['direccion_completa'],
                    $data['ciudad'],
                    $data['departamento'] ?? null,
                    $data['codigo_postal'] ?? null,
                    $data['latitud'],
                    $data['longitud'],
                    $data['telefono'] ?? null,
                    $data['email'] ?? null,
                    !empty($data['capacidad_almacenamiento']) ? $data['capacidad_almacenamiento'] : null,
                    $data['horario_apertura'] ?? null,
                    $data['horario_cierre'] ?? null,
                    $data['dias_atencion'] ?? 'Lunes a Sábado',
                    !empty($data['permite_delivery']) ? 1 : 0,
                    !empty($data['permite_pickup']) ? 1 : 0,
                    !empty($data['radio_cobertura_km']) ? $data['radio_cobertura_km'] : null,
                    !empty($data['fecha_apertura']) ? $data['fecha_apertura'] : null,
                    $data['activo'] ?? 1,
                    $data['id_sucursal']
                ];

                $stmt = $this->db->prepare($sql);
                $stmt->execute($params);

                echo json_encode(['success' => true, 'message' => 'Sucursal actualizada correctamente']);
            } else {
                // Crear nueva sucursal
                // Verificar que el código no exista
                $stmt = $this->db->prepare("SELECT COUNT(*) FROM sucursales WHERE codigo_sucursal = ?");
                $stmt->execute([$data['codigo_sucursal']]);
                if ($stmt->fetchColumn() > 0) {
                    echo json_encode(['success' => false, 'message' => 'El código de sucursal ya existe']);
                    return;
                }

                $sql = "INSERT INTO sucursales (codigo_sucursal, nombre_sucursal, id_encargado, 
                        direccion_completa, ciudad, departamento, codigo_postal, latitud, longitud,
                        telefono, email, capacidad_almacenamiento, horario_apertura, horario_cierre,
                        dias_atencion, permite_delivery, permite_pickup, radio_cobertura_km,
                        fecha_apertura, activo, fecha_creacion) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";

                $params = [
                    $data['codigo_sucursal'],
                    $data['nombre_sucursal'],
                    !empty($data['id_encargado']) ? $data['id_encargado'] : null,
                    $data['direccion_completa'],
                    $data['ciudad'],
                    $data['departamento'] ?? null,
                    $data['codigo_postal'] ?? null,
                    $data['latitud'],
                    $data['longitud'],
                    $data['telefono'] ?? null,
                    $data['email'] ?? null,
                    !empty($data['capacidad_almacenamiento']) ? $data['capacidad_almacenamiento'] : null,
                    $data['horario_apertura'] ?? null,
                    $data['horario_cierre'] ?? null,
                    $data['dias_atencion'] ?? 'Lunes a Sábado',
                    !empty($data['permite_delivery']) ? 1 : 0,
                    !empty($data['permite_pickup']) ? 1 : 0,
                    !empty($data['radio_cobertura_km']) ? $data['radio_cobertura_km'] : null,
                    !empty($data['fecha_apertura']) ? $data['fecha_apertura'] : null,
                    $data['activo'] ?? 1
                ];

                $stmt = $this->db->prepare($sql);
                $stmt->execute($params);

                echo json_encode(['success' => true, 'message' => 'Sucursal creada correctamente']);
            }
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
    }

    /**
     * Cambiar estado de una sucursal (AJAX)
     */
    public function cambiarEstadoSucursal()
    {
        header('Content-Type: application/json');

        try {
            $data = json_decode(file_get_contents('php://input'), true);

            if (empty($data['id_sucursal'])) {
                echo json_encode(['success' => false, 'message' => 'ID no proporcionado']);
                return;
            }

            $nuevoEstado = $data['nuevo_estado'] ?? 0;

            $sql = "UPDATE sucursales SET activo = ? WHERE id_sucursal = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$nuevoEstado, $data['id_sucursal']]);

            $mensaje = $nuevoEstado == 1 ? 'Sucursal activada' : 'Sucursal desactivada';
            echo json_encode(['success' => true, 'message' => $mensaje]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
    }

    /**
     * Obtener lista de encargados disponibles (AJAX)
     */
    public function obtenerEncargados()
    {
        header('Content-Type: application/json');

        try {
            // Obtener usuarios con rol "Encargado Sucursal" (id_rol = 2)
            $sql = "SELECT id_usuario, nombre_completo, email 
                    FROM usuarios 
                    WHERE id_rol = 2 AND activo = 1 
                    ORDER BY nombre_completo ASC";

            $stmt = $this->db->query($sql);
            $encargados = $stmt->fetchAll();

            echo json_encode(['success' => true, 'data' => $encargados]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
    }

    /**
     * Gestión de inventario
     */
    public function inventario()
    {
        // Obtener inventario con joins
        try {
            $sql = "SELECT i.*, 
                           s.nombre_sucursal,
                           s.codigo_sucursal,
                           p.nombre_producto,
                           p.codigo_producto,
                           (i.cantidad_disponible - i.cantidad_reservada) as stock_real
                    FROM inventario_sucursal i
                    INNER JOIN sucursales s ON i.id_sucursal = s.id_sucursal
                    INNER JOIN productos p ON i.id_producto = p.id_producto
                    ORDER BY s.nombre_sucursal ASC, p.nombre_producto ASC";

            $stmt = $this->db->query($sql);
            $inventario = $stmt->fetchAll();
        } catch (Exception $e) {
            error_log("Error obteniendo inventario: " . $e->getMessage());
            $inventario = [];
        }

        $data = [
            'title' => 'Gestión de Inventario - ' . APP_NAME,
            'inventario' => $inventario
        ];

        $this->view('admin/inventario', $data);
    }

    /**
     * Obtener stock de un producto en todas las sucursales (AJAX)
     */
    public function obtenerInventarioProducto()
    {
        header('Content-Type: application/json');

        if (!isset($_GET['id_producto'])) {
            echo json_encode(['success' => false, 'message' => 'ID de producto no proporcionado']);
            return;
        }

        try {
            $sql = "SELECT i.*, 
                           s.nombre_sucursal,
                           s.codigo_sucursal
                    FROM inventario_sucursal i
                    INNER JOIN sucursales s ON i.id_sucursal = s.id_sucursal
                    WHERE i.id_producto = ?
                    ORDER BY s.nombre_sucursal ASC";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([$_GET['id_producto']]);
            $inventario = $stmt->fetchAll();

            echo json_encode(['success' => true, 'data' => $inventario]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
    }

    /**
     * Registrar movimiento de inventario (AJAX)
     */
    public function registrarMovimiento()
    {
        header('Content-Type: application/json');

        try {
            $data = json_decode(file_get_contents('php://input'), true);

            // Validaciones
            if (
                empty($data['id_sucursal']) || empty($data['id_producto']) ||
                empty($data['tipo_movimiento']) || empty($data['cantidad'])
            ) {
                echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
                return;
            }

            // Obtener o crear inventario
            $stmt = $this->db->prepare("SELECT id_inventario, cantidad_disponible 
                                        FROM inventario_sucursal 
                                        WHERE id_sucursal = ? AND id_producto = ?");
            $stmt->execute([$data['id_sucursal'], $data['id_producto']]);
            $inventario = $stmt->fetch();

            if (!$inventario) {
                // Crear inventario si no existe
                $stmt = $this->db->prepare("INSERT INTO inventario_sucursal 
                                            (id_sucursal, id_producto, cantidad_disponible, stock_minimo) 
                                            VALUES (?, ?, 0, 5)");
                $stmt->execute([$data['id_sucursal'], $data['id_producto']]);
                $idInventario = $this->db->lastInsertId();
                $stockActual = 0;
            } else {
                $idInventario = $inventario['id_inventario'];
                $stockActual = $inventario['cantidad_disponible'];
            }

            // Calcular nueva cantidad según tipo
            $cantidad = (int)$data['cantidad'];
            $nuevaCantidad = $stockActual;

            switch ($data['tipo_movimiento']) {
                case 'entrada':
                case 'devolucion':
                    $nuevaCantidad += $cantidad;
                    break;
                case 'salida':
                    if ($stockActual < $cantidad) {
                        echo json_encode(['success' => false, 'message' => 'Stock insuficiente']);
                        return;
                    }
                    $nuevaCantidad -= $cantidad;
                    break;
                case 'ajuste':
                    // Para ajuste, la cantidad puede ser el valor final o la diferencia
                    $nuevaCantidad = $cantidad; // Asumimos que es el valor final
                    break;
            }

            // Actualizar inventario
            $sqlUpdate = "UPDATE inventario_sucursal SET cantidad_disponible = ?";
            if ($data['tipo_movimiento'] == 'entrada' || $data['tipo_movimiento'] == 'devolucion') {
                $sqlUpdate .= ", ultima_reposicion = CURRENT_DATE";
            }
            $sqlUpdate .= " WHERE id_inventario = ?";

            $stmt = $this->db->prepare($sqlUpdate);
            $stmt->execute([$nuevaCantidad, $idInventario]);

            // Registrar movimiento
            $stmt = $this->db->prepare("INSERT INTO movimientos_inventario 
                                        (id_inventario, tipo_movimiento, cantidad, motivo, id_usuario, referencia) 
                                        VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $idInventario,
                $data['tipo_movimiento'],
                $cantidad,
                $data['motivo'] ?? null,
                Session::getUserId(),
                $data['referencia'] ?? null
            ]);

            echo json_encode(['success' => true, 'message' => 'Movimiento registrado correctamente']);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
    }

    /**
     * Obtener historial de movimientos (AJAX)
     */
    public function obtenerMovimientos()
    {
        header('Content-Type: application/json');

        try {
            $where = [];
            $params = [];

            if (!empty($_GET['id_inventario'])) {
                $where[] = "m.id_inventario = ?";
                $params[] = $_GET['id_inventario'];
            }

            if (!empty($_GET['tipo_movimiento'])) {
                $where[] = "m.tipo_movimiento = ?";
                $params[] = $_GET['tipo_movimiento'];
            }

            $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

            $sql = "SELECT m.*, 
                           u.nombre_completo as usuario_nombre,
                           p.nombre_producto,
                           s.nombre_sucursal
                    FROM movimientos_inventario m
                    INNER JOIN inventario_sucursal i ON m.id_inventario = i.id_inventario
                    INNER JOIN productos p ON i.id_producto = p.id_producto
                    INNER JOIN sucursales s ON i.id_sucursal = s.id_sucursal
                    LEFT JOIN usuarios u ON m.id_usuario = u.id_usuario
                    $whereClause
                    ORDER BY m.fecha_movimiento DESC
                    LIMIT 100";

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $movimientos = $stmt->fetchAll();

            echo json_encode(['success' => true, 'data' => $movimientos]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
    }

    /**
     * Obtener productos con stock bajo (AJAX)
     */
    public function obtenerProductosBajoStock()
    {
        header('Content-Type: application/json');

        try {
            $sql = "SELECT i.*, 
                           s.nombre_sucursal,
                           p.nombre_producto,
                           p.codigo_producto
                    FROM inventario_sucursal i
                    INNER JOIN sucursales s ON i.id_sucursal = s.id_sucursal
                    INNER JOIN productos p ON i.id_producto = p.id_producto
                    WHERE i.cantidad_disponible < i.stock_minimo
                    ORDER BY i.cantidad_disponible ASC";

            $stmt = $this->db->query($sql);
            $bajoStock = $stmt->fetchAll();

            echo json_encode(['success' => true, 'data' => $bajoStock]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
    }

    /**
     * Transferir stock entre sucursales (AJAX)
     */
    public function transferirStock()
    {
        header('Content-Type: application/json');

        try {
            $data = json_decode(file_get_contents('php://input'), true);

            // Validaciones
            if (
                empty($data['id_sucursal_origen']) || empty($data['id_sucursal_destino']) ||
                empty($data['id_producto']) || empty($data['cantidad'])
            ) {
                echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
                return;
            }

            if ($data['id_sucursal_origen'] == $data['id_sucursal_destino']) {
                echo json_encode(['success' => false, 'message' => 'Las sucursales deben ser diferentes']);
                return;
            }

            // Iniciar transacción
            $this->db->beginTransaction();

            try {
                // Obtener inventario origen
                $stmt = $this->db->prepare("SELECT id_inventario, cantidad_disponible 
                                            FROM inventario_sucursal 
                                            WHERE id_sucursal = ? AND id_producto = ?");
                $stmt->execute([$data['id_sucursal_origen'], $data['id_producto']]);
                $invOrigen = $stmt->fetch();

                if (!$invOrigen || $invOrigen['cantidad_disponible'] < $data['cantidad']) {
                    throw new Exception('Stock insuficiente en sucursal origen');
                }

                // Obtener o crear inventario destino
                $stmt = $this->db->prepare("SELECT id_inventario, cantidad_disponible 
                                            FROM inventario_sucursal 
                                            WHERE id_sucursal = ? AND id_producto = ?");
                $stmt->execute([$data['id_sucursal_destino'], $data['id_producto']]);
                $invDestino = $stmt->fetch();

                if (!$invDestino) {
                    $stmt = $this->db->prepare("INSERT INTO inventario_sucursal 
                                                (id_sucursal, id_producto, cantidad_disponible, stock_minimo) 
                                                VALUES (?, ?, 0, 5)");
                    $stmt->execute([$data['id_sucursal_destino'], $data['id_producto']]);
                    $idInvDestino = $this->db->lastInsertId();
                    $stockDestino = 0;
                } else {
                    $idInvDestino = $invDestino['id_inventario'];
                    $stockDestino = $invDestino['cantidad_disponible'];
                }

                // Generar referencia única
                $referencia = 'TRANS-' . date('Ymd') . '-' . uniqid();

                // Decrementar origen
                $stmt = $this->db->prepare("UPDATE inventario_sucursal 
                                            SET cantidad_disponible = cantidad_disponible - ? 
                                            WHERE id_inventario = ?");
                $stmt->execute([$data['cantidad'], $invOrigen['id_inventario']]);

                // Incrementar destino
                $stmt = $this->db->prepare("UPDATE inventario_sucursal 
                                            SET cantidad_disponible = cantidad_disponible + ?, 
                                                ultima_reposicion = CURRENT_DATE 
                                            WHERE id_inventario = ?");
                $stmt->execute([$data['cantidad'], $idInvDestino]);

                // Registrar movimiento salida en origen
                $stmt = $this->db->prepare("INSERT INTO movimientos_inventario 
                                            (id_inventario, tipo_movimiento, cantidad, motivo, id_usuario, referencia) 
                                            VALUES (?, 'transferencia', ?, ?, ?, ?)");
                $stmt->execute([
                    $invOrigen['id_inventario'],
                    $data['cantidad'],
                    $data['motivo'] ?? 'Transferencia entre sucursales',
                    Session::getUserId(),
                    $referencia
                ]);

                // Registrar movimiento entrada en destino
                $stmt = $this->db->prepare("INSERT INTO movimientos_inventario 
                                            (id_inventario, tipo_movimiento, cantidad, motivo, id_usuario, referencia) 
                                            VALUES (?, 'transferencia', ?, ?, ?, ?)");
                $stmt->execute([
                    $idInvDestino,
                    $data['cantidad'],
                    $data['motivo'] ?? 'Transferencia entre sucursales',
                    Session::getUserId(),
                    $referencia
                ]);

                // Confirmar transacción
                $this->db->commit();

                echo json_encode(['success' => true, 'message' => 'Transferencia realizada correctamente']);
            } catch (Exception $e) {
                // Revertir transacción
                $this->db->rollBack();
                throw $e;
            }
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
    }

    /**
     * Reportes del sistema
     */
    public function reportes()
    {
        // Obtener datos para dashboard de reportes
        try {
            // Total de ventas del mes actual
            $stmt = $this->db->query("SELECT SUM(total_final) as total_mes 
                                      FROM pedidos 
                                      WHERE MONTH(fecha_pedido) = MONTH(CURRENT_DATE) 
                                      AND YEAR(fecha_pedido) = YEAR(CURRENT_DATE)
                                      AND id_estado IN (6, 7)");
            $ventas_mes = $stmt->fetch()['total_mes'] ?? 0;

            // Pedidos completados del mes
            $stmt = $this->db->query("SELECT COUNT(*) as total 
                                      FROM pedidos 
                                      WHERE MONTH(fecha_pedido) = MONTH(CURRENT_DATE) 
                                      AND YEAR(fecha_pedido) = YEAR(CURRENT_DATE)
                                      AND id_estado IN (6, 7)");
            $pedidos_mes = $stmt->fetch()['total'] ?? 0;

            // Productos activos
            $stmt = $this->db->query("SELECT COUNT(*) as total FROM productos WHERE activo = 1");
            $productos_activos = $stmt->fetch()['total'] ?? 0;

            // Stock bajo
            $stmt = $this->db->query("SELECT COUNT(*) as total 
                                      FROM inventario_sucursal 
                                      WHERE cantidad_disponible < stock_minimo");
            $stock_bajo = $stmt->fetch()['total'] ?? 0;

            $data = [
                'title' => 'Reportes y Analíticas - ' . APP_NAME,
                'ventas_mes' => $ventas_mes,
                'pedidos_mes' => $pedidos_mes,
                'productos_activos' => $productos_activos,
                'stock_bajo' => $stock_bajo
            ];
        } catch (Exception $e) {
            error_log("Error obteniendo datos de reportes: " . $e->getMessage());
            $data = [
                'title' => 'Reportes y Analíticas - ' . APP_NAME,
                'ventas_mes' => 0,
                'pedidos_mes' => 0,
                'productos_activos' => 0,
                'stock_bajo' => 0
            ];
        }

        $this->view('admin/reportes', $data);
    }

    /**
     * Reporte de ventas por periodo (AJAX)
     */
    public function reporteVentas()
    {
        header('Content-Type: application/json');

        try {
            $fechaDesde = $_GET['fecha_desde'] ?? date('Y-m-01');
            $fechaHasta = $_GET['fecha_hasta'] ?? date('Y-m-d');

            // Ventas agrupadas por día
            $sql = "SELECT 
                        DATE(p.fecha_pedido) as fecha,
                        COUNT(*) as num_pedidos,
                        SUM(p.total_final) as total_ventas,
                        AVG(p.total_final) as ticket_promedio
                    FROM pedidos p
                    WHERE p.fecha_pedido BETWEEN ? AND ?
                        AND p.id_estado IN (6, 7)
                    GROUP BY DATE(p.fecha_pedido)
                    ORDER BY fecha ASC";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([$fechaDesde, $fechaHasta]);
            $ventas_diarias = $stmt->fetchAll();

            // Totales del periodo
            $sql = "SELECT 
                        COUNT(*) as total_pedidos,
                        SUM(total_final) as total_ventas,
                        AVG(total_final) as ticket_promedio
                    FROM pedidos
                    WHERE fecha_pedido BETWEEN ? AND ?
                        AND id_estado IN (6, 7)";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([$fechaDesde, $fechaHasta]);
            $totales = $stmt->fetch();

            echo json_encode([
                'success' => true,
                'ventas_diarias' => $ventas_diarias,
                'totales' => $totales
            ]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
    }

    /**
     * Reporte de estado de inventario (AJAX)
     */
    public function reporteInventario()
    {
        header('Content-Type: application/json');

        try {
            // Resumen de inventario
            $sql = "SELECT 
                        COUNT(*) as total_registros,
                        SUM(CASE WHEN cantidad_disponible >= stock_minimo THEN 1 ELSE 0 END) as normal,
                        SUM(CASE WHEN cantidad_disponible < stock_minimo AND cantidad_disponible > 0 THEN 1 ELSE 0 END) as bajo,
                        SUM(CASE WHEN cantidad_disponible = 0 THEN 1 ELSE 0 END) as critico
                    FROM inventario_sucursal";

            $stmt = $this->db->query($sql);
            $resumen = $stmt->fetch();

            // Productos que requieren reposición
            $sql = "SELECT 
                        i.id_inventario,
                        p.nombre_producto,
                        p.codigo_producto,
                        s.nombre_sucursal,
                        i.cantidad_disponible,
                        i.stock_minimo,
                        (i.stock_minimo - i.cantidad_disponible) as cantidad_requerida
                    FROM inventario_sucursal i
                    INNER JOIN productos p ON i.id_producto = p.id_producto
                    INNER JOIN sucursales s ON i.id_sucursal = s.id_sucursal
                    WHERE i.cantidad_disponible < i.stock_minimo
                    ORDER BY cantidad_requerida DESC
                    LIMIT 20";

            $stmt = $this->db->query($sql);
            $reposicion = $stmt->fetchAll();

            echo json_encode([
                'success' => true,
                'resumen' => $resumen,
                'reposicion' => $reposicion
            ]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
    }

    /**
     * Reporte de productos más vendidos (AJAX)
     */
    public function reporteProductos()
    {
        header('Content-Type: application/json');

        try {
            $fechaDesde = $_GET['fecha_desde'] ?? date('Y-m-01');
            $fechaHasta = $_GET['fecha_hasta'] ?? date('Y-m-d');
            $limite = $_GET['limite'] ?? 10;

            $sql = "SELECT 
                        p.id_producto,
                        p.nombre_producto,
                        p.codigo_producto,
                        SUM(dp.cantidad) as total_vendido,
                        SUM(dp.subtotal) as ingresos_generados,
                        COUNT(DISTINCT dp.id_pedido) as num_pedidos
                    FROM detalle_pedido dp
                    INNER JOIN productos p ON dp.id_producto = p.id_producto
                    INNER JOIN pedidos ped ON dp.id_pedido = ped.id_pedido
                    WHERE ped.fecha_pedido BETWEEN ? AND ?
                        AND ped.id_estado IN (6, 7)
                    GROUP BY p.id_producto
                    ORDER BY total_vendido DESC
                    LIMIT ?";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([$fechaDesde, $fechaHasta, (int)$limite]);
            $top_productos = $stmt->fetchAll();

            // Total de ingresos para calcular porcentajes
            $stmt = $this->db->prepare("SELECT SUM(dp.subtotal) as total_ingresos
                                        FROM detalle_pedido dp
                                        INNER JOIN pedidos ped ON dp.id_pedido = ped.id_pedido
                                        WHERE ped.fecha_pedido BETWEEN ? AND ?
                                            AND ped.id_estado IN (6, 7)");
            $stmt->execute([$fechaDesde, $fechaHasta]);
            $total_ingresos = $stmt->fetch()['total_ingresos'] ?? 1;

            // Calcular porcentajes
            foreach ($top_productos as &$prod) {
                $prod['porcentaje'] = ($prod['ingresos_generados'] / $total_ingresos) * 100;
            }

            echo json_encode([
                'success' => true,
                'productos' => $top_productos,
                'total_ingresos' => $total_ingresos
            ]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
    }

    /**
     * Reporte de actividad de usuarios (AJAX)
     */
    public function reporteUsuarios()
    {
        header('Content-Type: application/json');

        try {
            // Usuarios activos con su última actividad
            $sql = "SELECT 
                        u.id_usuario,
                        u.nombre_completo,
                        u.email,
                        r.nombre_rol,
                        u.fecha_registro,
                        u.ultima_sesion,
                        u.activo,
                        (SELECT COUNT(*) FROM pedidos WHERE id_usuario = u.id_usuario) as num_pedidos
                    FROM usuarios u
                    INNER JOIN roles r ON u.id_rol = r.id_rol
                    ORDER BY u.ultima_sesion DESC
                    LIMIT 50";

            $stmt = $this->db->query($sql);
            $usuarios = $stmt->fetchAll();

            // Estadísticas generales
            $stmt = $this->db->query("SELECT 
                                        COUNT(*) as total_usuarios,
                                        SUM(CASE WHEN activo = 1 THEN 1 ELSE 0 END) as activos,
                                        SUM(CASE WHEN activo = 0 THEN 1 ELSE 0 END) as inactivos
                                      FROM usuarios");
            $estadisticas = $stmt->fetch();

            echo json_encode([
                'success' => true,
                'usuarios' => $usuarios,
                'estadisticas' => $estadisticas
            ]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
    }
}
