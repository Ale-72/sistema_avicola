<?php

/**
 * Modelo de Sucursal
 */

class Sucursal
{
    private $db;
    private $table = 'sucursales';

    public function __construct($db)
    {
        $this->db = $db;
    }

    /**
     * Obtener todas las sucursales activas
     */
    public function getAll($activo = 1)
    {
        $query = "SELECT s.*, u.nombre_completo as nombre_encargado
                  FROM {$this->table} s
                  LEFT JOIN usuarios u ON s.id_encargado = u.id_usuario";

        if ($activo !== null) {
            $query .= " WHERE s.activo = :activo";
        }

        $query .= " ORDER BY s.nombre_sucursal ASC";

        $stmt = $this->db->prepare($query);

        if ($activo !== null) {
            $stmt->bindParam(':activo', $activo);
        }

        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Obtener sucursal por ID
     */
    public function getById($id)
    {
        $query = "SELECT s.*, u.nombre_completo as nombre_encargado, u.telefono as telefono_encargado
                  FROM {$this->table} s
                  LEFT JOIN usuarios u ON s.id_encargado = u.id_usuario
                  WHERE s.id_sucursal = :id";

        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();

        return $stmt->fetch();
    }

    /**
     * Encontrar sucursal más cercana
     */
    public function findNearest($lat, $lng)
    {
        // Usando fórmula de Haversine para calcular distancia
        $query = "SELECT *, 
                  (6371 * acos(cos(radians(:lat)) * cos(radians(latitud)) * 
                  cos(radians(longitud) - radians(:lng)) + 
                  sin(radians(:lat)) * sin(radians(latitud)))) AS distancia_km
                  FROM {$this->table}
                  WHERE activo = 1 AND permite_delivery = 1
                  HAVING distancia_km <= radio_cobertura_km
                  ORDER BY distancia_km ASC
                  LIMIT 1";

        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':lat', $lat);
        $stmt->bindParam(':lng', $lng);
        $stmt->execute();

        return $stmt->fetch();
    }

    /**
     * Obtener sucursales dentro del radio
     */
    public function getSucursalesEnRadio($lat, $lng, $radioKm = 15)
    {
        $query = "SELECT *, 
                  (6371 * acos(cos(radians(:lat)) * cos(radians(latitud)) * 
                  cos(radians(longitud) - radians(:lng)) + 
                  sin(radians(:lat)) * sin(radians(latitud)))) AS distancia_km
                  FROM {$this->table}
                  WHERE activo = 1
                  HAVING distancia_km <= :radio
                  ORDER BY distancia_km ASC";

        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':lat', $lat);
        $stmt->bindParam(':lng', $lng);
        $stmt->bindParam(':radio', $radioKm);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * Crear sucursal
     */
    public function create($data)
    {
        $query = "INSERT INTO {$this->table}
                  (codigo_sucursal, nombre_sucursal, id_encargado, direccion_completa, ciudad, departamento,
                   latitud, longitud, telefono, email, horario_apertura, horario_cierre, dias_atencion,
                   permite_delivery, permite_pickup, radio_cobertura_km, activo)
                  VALUES (:codigo, :nombre, :encargado, :direccion, :ciudad, :departamento, :lat, :lng,
                          :telefono, :email, :h_apertura, :h_cierre, :dias, :delivery, :pickup, :radio, :activo)";

        $stmt = $this->db->prepare($query);

        $stmt->bindParam(':codigo', $data['codigo_sucursal']);
        $stmt->bindParam(':nombre', $data['nombre_sucursal']);
        $stmt->bindParam(':encargado', $data['id_encargado']);
        $stmt->bindParam(':direccion', $data['direccion_completa']);
        $stmt->bindParam(':ciudad', $data['ciudad']);
        $stmt->bindParam(':departamento', $data['departamento']);
        $stmt->bindParam(':lat', $data['latitud']);
        $stmt->bindParam(':lng', $data['longitud']);
        $stmt->bindParam(':telefono', $data['telefono']);
        $stmt->bindParam(':email', $data['email']);
        $stmt->bindParam(':h_apertura', $data['horario_apertura']);
        $stmt->bindParam(':h_cierre', $data['horario_cierre']);
        $stmt->bindParam(':dias', $data['dias_atencion']);
        $stmt->bindParam(':delivery', $data['permite_delivery']);
        $stmt->bindParam(':pickup', $data['permite_pickup']);
        $stmt->bindParam(':radio', $data['radio_cobertura_km']);
        $stmt->bindParam(':activo', $data['activo']);

        if ($stmt->execute()) {
            return $this->db->lastInsertId();
        }

        return false;
    }

    /**
     * Actualizar sucursal
     */
    public function update($id, $data)
    {
        $query = "UPDATE {$this->table} SET
                  nombre_sucursal = :nombre,
                  direccion_completa = :direccion,
                  ciudad = :ciudad,
                  departamento = :departamento,
                  latitud = :lat,
                  longitud = :lng,
                  telefono = :telefono,
                  email = :email,
                  horario_apertura = :h_apertura,
                  horario_cierre = :h_cierre,
                  dias_atencion = :dias,
                  permite_delivery = :delivery,
                  permite_pickup = :pickup,
                  radio_cobertura_km = :radio,
                  activo = :activo
                  WHERE id_sucursal = :id";

        $stmt = $this->db->prepare($query);

        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':nombre', $data['nombre_sucursal']);
        $stmt->bindParam(':direccion', $data['direccion_completa']);
        $stmt->bindParam(':ciudad', $data['ciudad']);
        $stmt->bindParam(':departamento', $data['departamento']);
        $stmt->bindParam(':lat', $data['latitud']);
        $stmt->bindParam(':lng', $data['longitud']);
        $stmt->bindParam(':telefono', $data['telefono']);
        $stmt->bindParam(':email', $data['email']);
        $stmt->bindParam(':h_apertura', $data['horario_apertura']);
        $stmt->bindParam(':h_cierre', $data['horario_cierre']);
        $stmt->bindParam(':dias', $data['dias_atencion']);
        $stmt->bindParam(':delivery', $data['permite_delivery']);
        $stmt->bindParam(':pickup', $data['permite_pickup']);
        $stmt->bindParam(':radio', $data['radio_cobertura_km']);
        $stmt->bindParam(':activo', $data['activo']);

        return $stmt->execute();
    }

    /**
     * Obtener inventario de sucursal
     */
    public function getInventario($sucursalId, $productoId = null)
    {
        $query = "SELECT inv.*, p.nombre_producto, p.codigo_producto, p.precio_unitario
                  FROM inventario_sucursal inv
                  INNER JOIN productos p ON inv.id_producto = p.id_producto
                  WHERE inv.id_sucursal = :sucursal";

        if ($productoId) {
            $query .= " AND inv.id_producto = :producto";
        }

        $query .= " ORDER BY p.nombre_producto ASC";

        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':sucursal', $sucursalId);

        if ($productoId) {
            $stmt->bindParam(':producto', $productoId);
        }

        $stmt->execute();

        return $productoId ? $stmt->fetch() : $stmt->fetchAll();
    }

    /**
     * Actualizar inventario
     */
    public function updateInventario($sucursalId, $productoId, $cantidad)
    {
        // Verificar si existe el registro
        $check = $this->getInventario($sucursalId, $productoId);

        if ($check) {
            $query = "UPDATE inventario_sucursal SET cantidad_disponible = :cantidad 
                      WHERE id_sucursal = :sucursal AND id_producto = :producto";
        } else {
            $query = "INSERT INTO inventario_sucursal (id_sucursal, id_producto, cantidad_disponible)
                      VALUES (:sucursal, :producto, :cantidad)";
        }

        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':sucursal', $sucursalId);
        $stmt->bindParam(':producto', $productoId);
        $stmt->bindParam(':cantidad', $cantidad);

        return $stmt->execute();
    }
}
