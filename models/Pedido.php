<?php

/**
 * Modelo de Pedido
 */

class Pedido
{
    private $db;
    private $table = 'pedidos';

    public function __construct($db)
    {
        $this->db = $db;
    }

    /**
     * Crear nuevo pedido
     */
    public function create($data)
    {
        try {
            $this->db->beginTransaction();

            // Generar número de pedido único
            $numeroPedido = 'PED-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));

            $query = "INSERT INTO {$this->table}
                      (numero_pedido, id_cliente, id_sucursal, id_estado, id_metodo_entrega,
                       direccion_entrega, ciudad_entrega, departamento_entrega, latitud_entrega, longitud_entrega,
                       distancia_km, nombre_receptor, telefono_receptor, email_receptor, notas_cliente,
                       subtotal, costo_envio, descuento, total, metodo_pago, fecha_estimada_entrega)
                      VALUES (:numero, :cliente, :sucursal, :estado, :metodo_entrega,
                              :direccion, :ciudad, :departamento, :lat, :lng, :distancia,
                              :receptor, :telefono, :email, :notas,
                              :subtotal, :envio, :descuento, :total, :metodo_pago, :fecha_estimada)";

            $stmt = $this->db->prepare($query);

            $stmt->bindParam(':numero', $numeroPedido);
            $stmt->bindParam(':cliente', $data['id_cliente']);
            $stmt->bindParam(':sucursal', $data['id_sucursal']);
            $stmt->bindParam(':estado', $data['id_estado']);
            $stmt->bindParam(':metodo_entrega', $data['id_metodo_entrega']);
            $stmt->bindParam(':direccion', $data['direccion_entrega']);
            $stmt->bindParam(':ciudad', $data['ciudad_entrega']);
            $stmt->bindParam(':departamento', $data['departamento_entrega']);
            $stmt->bindParam(':lat', $data['latitud_entrega']);
            $stmt->bindParam(':lng', $data['longitud_entrega']);
            $stmt->bindParam(':distancia', $data['distancia_km']);
            $stmt->bindParam(':receptor', $data['nombre_receptor']);
            $stmt->bindParam(':telefono', $data['telefono_receptor']);
            $stmt->bindParam(':email', $data['email_receptor']);
            $stmt->bindParam(':notas', $data['notas_cliente']);
            $stmt->bindParam(':subtotal', $data['subtotal']);
            $stmt->bindParam(':envio', $data['costo_envio']);
            $stmt->bindParam(':descuento', $data['descuento']);
            $stmt->bindParam(':total', $data['total']);
            $stmt->bindParam(':metodo_pago', $data['metodo_pago']);
            $stmt->bindParam(':fecha_estimada', $data['fecha_estimada_entrega']);

            $stmt->execute();
            $pedidoId = $this->db->lastInsertId();

            // Insertar detalle del pedido
            foreach ($data['items'] as $item) {
                $this->addDetalle($pedidoId, $item);

                // Reducir inventario de la sucursal
                $this->reducirInventario($data['id_sucursal'], $item['id_producto'], $item['cantidad']);
            }

            // Registrar en historial
            $this->addHistorial($pedidoId, $data['id_estado'], null, 'Pedido creado');

            $this->db->commit();
            return $pedidoId;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * Agregar detalle de pedido
     */
    private function addDetalle($pedidoId, $item)
    {
        $query = "INSERT INTO detalle_pedido (id_pedido, id_producto, cantidad, precio_unitario, subtotal)
                  VALUES (:pedido, :producto, :cantidad, :precio, :subtotal)";

        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':pedido', $pedidoId);
        $stmt->bindParam(':producto', $item['id_producto']);
        $stmt->bindParam(':cantidad', $item['cantidad']);
        $stmt->bindParam('precio', $item['precio_unitario']);
        $stmt->bindParam(':subtotal', $item['subtotal']);

        return $stmt->execute();
    }

    /**
     * Reducir inventario de sucursal
     */
    private function reducirInventario($sucursalId, $productoId, $cantidad)
    {
        $query = "UPDATE inventario_sucursal 
                  SET cantidad_disponible = cantidad_disponible - :cantidad,
                      cantidad_reservada = cantidad_reservada + :cantidad
                  WHERE id_sucursal = :sucursal AND id_producto = :producto";

        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':cantidad', $cantidad);
        $stmt->bindParam(':sucursal', $sucursalId);
        $stmt->bindParam(':producto', $productoId);

        return $stmt->execute();
    }

    /**
     * Agregar historial de pedido
     */
    public function addHistorial($pedidoId, $estadoId, $usuarioId = null, $comentario = '')
    {
        $query = "INSERT INTO historial_pedido (id_pedido, id_estado, id_usuario, comentario)
                  VALUES (:pedido, :estado, :usuario, :comentario)";

        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':pedido', $pedidoId);
        $stmt->bindParam(':estado', $estadoId);
        $stmt->bindParam(':usuario', $usuarioId);
        $stmt->bindParam(':comentario', $comentario);

        return $stmt->execute();
    }

    /**
     * Obtener pedido por ID
     */
    public function getById($id)
    {
        $query = "SELECT p.*, 
                  u.nombre_completo as cliente_nombre, u.email as cliente_email,
                  s.nombre_sucursal, s.direccion_completa as sucursal_direccion,
                  ep.nombre_estado, ep.color_hex as estado_color,
                  me.nombre_metodo as metodo_entrega_nombre
                  FROM {$this->table} p
                  INNER JOIN usuarios u ON p.id_cliente = u.id_usuario
                  INNER JOIN sucursales s ON p.id_sucursal = s.id_sucursal
                  INNER JOIN estados_pedido ep ON p.id_estado = ep.id_estado
                  INNER JOIN metodos_entrega me ON p.id_metodo_entrega = me.id_metodo_entrega
                  WHERE p.id_pedido = :id";

        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();

        return $stmt->fetch();
    }

    /**
     * Obtener detalle del pedido
     */
    public function getDetalle($pedidoId)
    {
        $query = "SELECT dp.*, p.nombre_producto, p.codigo_producto
                  FROM detalle_pedido dp
                  INNER JOIN productos p ON dp.id_producto = p.id_producto
                  WHERE dp.id_pedido = :pedido";

        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':pedido', $pedidoId);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * Obtener pedidos de un cliente
     */
    public function getByCliente($clienteId, $limit = 10)
    {
        $query = "SELECT p.*, ep.nombre_estado, ep.color_hex as estado_color,
                  me.nombre_metodo as metodo_entrega_nombre,
                  s.nombre_sucursal
                  FROM {$this->table} p
                  INNER JOIN estados_pedido ep ON p.id_estado = ep.id_estado
                  INNER JOIN metodos_entrega me ON p.id_metodo_entrega = me.id_metodo_entrega
                  INNER JOIN sucursales s ON p.id_sucursal = s.id_sucursal
                  WHERE p.id_cliente = :cliente
                  ORDER BY p.fecha_pedido DESC
                  LIMIT :limit";

        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':cliente', $clienteId);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * Obtener pedidos de una sucursal
     */
    public function getBySucursal($sucursalId, $filters = [])
    {
        $query = "SELECT p.*, 
                  u.nombre_completo as cliente_nombre,
                  ep.nombre_estado, ep.color_hex as estado_color,
                  me.nombre_metodo as metodo_entrega_nombre
                  FROM {$this->table} p
                  INNER JOIN usuarios u ON p.id_cliente = u.id_usuario
                  INNER JOIN estados_pedido ep ON p.id_estado = ep.id_estado
                  INNER JOIN metodos_entrega me ON p.id_metodo_entrega = me.id_metodo_entrega
                  WHERE p.id_sucursal = :sucursal";

        $params = [':sucursal' => $sucursalId];

        if (!empty($filters['estado'])) {
            $query .= " AND p.id_estado = :estado";
            $params[':estado'] = $filters['estado'];
        }

        $query .= " ORDER BY p.fecha_pedido DESC";

        $stmt = $this->db->prepare($query);

        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }

        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Actualizar estado del pedido
     */
    public function updateEstado($pedidoId, $nuevoEstadoId, $usuarioId = null, $comentario = '')
    {
        try {
            $this->db->beginTransaction();

            $query = "UPDATE {$this->table} SET id_estado = :estado WHERE id_pedido = :pedido";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':estado', $nuevoEstadoId);
            $stmt->bindParam(':pedido', $pedidoId);
            $stmt->execute();

            $this->addHistorial($pedidoId, $nuevoEstadoId, $usuarioId, $comentario);

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }

    /**
     * Calcular costo de envío
     */
    public function calcularCostoEnvio($distanciaKm, $metodoEntregaId)
    {
        $query = "SELECT costo_base, costo_por_km FROM metodos_entrega WHERE id_metodo_entrega = :metodo";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':metodo', $metodoEntregaId);
        $stmt->execute();

        $metodo = $stmt->fetch();

        if ($metodo) {
            return $metodo['costo_base'] + ($distanciaKm * $metodo['costo_por_km']);
        }

        return 0;
    }
}
