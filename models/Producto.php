<?php

/**
 * Modelo de Producto
 */

class Producto
{
    private $db;
    private $table = 'productos';

    public function __construct($db)
    {
        $this->db = $db;
    }

    /**
     * Obtener todos los productos con filtros
     */
    public function getAll($filters = [], $limit = 12, $offset = 0)
    {
        $query = "SELECT p.*, cp.nombre_categoria, cp.slug as categoria_slug,
                  (SELECT url_imagen FROM imagenes_producto WHERE id_producto = p.id_producto AND es_principal = 1 LIMIT 1) as imagen_principal
                  FROM {$this->table} p
                  INNER JOIN categorias_producto cp ON p.id_categoria_producto = cp.id_categoria_producto
                  WHERE p.activo = 1";

        $params = [];

        if (!empty($filters['categoria'])) {
            $query .= " AND p.id_categoria_producto = :categoria";
            $params[':categoria'] = $filters['categoria'];
        }

        if (!empty($filters['search'])) {
            $query .= " AND (p.nombre_producto LIKE :search OR p.descripcion_corta LIKE :search)";
            $params[':search'] = '%' . $filters['search'] . '%';
        }

        if (!empty($filters['destacado'])) {
            $query .= " AND p.destacado = 1";
        }

        $query .= " ORDER BY p.destacado DESC, p.fecha_creacion DESC LIMIT :limit OFFSET :offset";

        $stmt = $this->db->prepare($query);

        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }

        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);

        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Obtener producto por ID
     */
    public function getById($id)
    {
        $query = "SELECT p.*, cp.nombre_categoria, cp.slug as categoria_slug
                  FROM {$this->table} p
                  INNER JOIN categorias_producto cp ON p.id_categoria_producto = cp.id_categoria_producto
                  WHERE p.id_producto = :id";

        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();

        return $stmt->fetch();
    }

    /**
     * Obtener producto por slug
     */
    public function getBySlug($slug)
    {
        $query = "SELECT p.*, cp.nombre_categoria
                  FROM {$this->table} p
                  INNER JOIN categorias_producto cp ON p.id_categoria_producto = cp.id_categoria_producto
                  WHERE p.slug = :slug AND p.activo = 1";

        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':slug', $slug);
        $stmt->execute();

        return $stmt->fetch();
    }

    /**
     * Obtener todas las categorías
     */
    public function getCategorias()
    {
        $query = "SELECT * FROM categorias_producto ORDER BY nombre_categoria";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Obtener imágenes del producto  
     */
    public function getImagenes($productoId)
    {
        $query = "SELECT * FROM imagenes_producto WHERE id_producto = :id ORDER BY es_principal DESC, orden ASC";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $productoId);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * Obtener atributos del producto
     */
    public function getAtributos($productoId)
    {
        $query = "SELECT * FROM atributos_producto WHERE id_producto = :id ORDER BY grupo_atributo, nombre_atributo";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $productoId);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * Obtener stock por sucursal
     */
    public function getStockPorSucursal($productoId)
    {
        $query = "SELECT inv.*, suc.nombre_sucursal, suc.ciudad
                  FROM inventario_sucursal inv
                  INNER JOIN sucursales suc ON inv.id_sucursal = suc.id_sucursal
                  WHERE inv.id_producto = :producto AND inv.cantidad_disponible > 0
                  ORDER BY suc.nombre_sucursal";

        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':producto', $productoId);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Obtener stock disponible en una sucursal específica
     */
    public function getStockEnSucursal($productoId, $sucursalId)
    {
        $query = "SELECT inv.cantidad_disponible as stock_disponible,
                  suc.nombre_sucursal
                  FROM inventario_sucursal inv
                  INNER JOIN sucursales suc ON inv.id_sucursal = suc.id_sucursal  
                  WHERE inv.id_producto = :producto AND inv.id_sucursal = :sucursal";

        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':producto', $productoId);
        $stmt->bindParam(':sucursal', $sucursalId);
        $stmt->execute();
        return $stmt->fetch();
    }

    /**
     * Obtener productos relacionados
     */
    public function getRelacionados($productoId, $categoriaId, $limit = 4)
    {
        $query = "SELECT p.*, cp.nombre_categoria,
                  (SELECT url_imagen FROM imagenes_producto WHERE id_producto = p.id_producto AND es_principal = 1 LIMIT 1) as imagen_principal
                  FROM {$this->table} p
                  INNER JOIN categorias_producto cp ON p.id_categoria_producto = cp.id_categoria_producto
                  WHERE p.id_categoria_producto = :categoria 
                  AND p.id_producto != :producto
                  AND p.activo = 1
                  ORDER BY RAND()
                  LIMIT :limit";

        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':categoria', $categoriaId);
        $stmt->bindParam(':producto', $productoId);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Crear producto
     */
    public function create($data)
    {
        $query = "INSERT INTO {$this->table} 
                  (id_categoria_producto, codigo_producto, nombre_producto, slug, descripcion_corta, 
                   descripcion_larga, precio_unitario, unidad_medida, stock_total, destacado, activo)
                  VALUES (:categoria, :codigo, :nombre, :slug, :desc_corta, :desc_larga, :precio, 
                          :unidad, :stock, :destacado, :activo)";

        $stmt = $this->db->prepare($query);

        $stmt->bindParam(':categoria', $data['id_categoria_producto']);
        $stmt->bindParam(':codigo', $data['codigo_producto']);
        $stmt->bindParam(':nombre', $data['nombre_producto']);
        $stmt->bindParam(':slug', $data['slug']);
        $stmt->bindParam(':desc_corta', $data['descripcion_corta']);
        $stmt->bindParam(':desc_larga', $data['descripcion_larga']);
        $stmt->bindParam(':precio', $data['precio_unitario']);
        $stmt->bindParam(':unidad', $data['unidad_medida']);
        $stmt->bindParam(':stock', $data['stock_total']);
        $stmt->bindParam(':destacado', $data['destacado']);
        $stmt->bindParam(':activo', $data['activo']);

        if ($stmt->execute()) {
            return $this->db->lastInsertId();
        }

        return false;
    }

    /**
     * Actualizar producto
     */
    public function update($id, $data)
    {
        $query = "UPDATE {$this->table} SET
                  nombre_producto = :nombre,
                  descripcion_corta = :desc_corta,
                  descripcion_larga = :desc_larga,
                  precio_unitario = :precio,
                  precio_oferta = :precio_oferta,
                  unidad_medida = :unidad,
                  stock_total = :stock,
                  destacado = :destacado,
                  activo = :activo
                  WHERE id_producto = :id";

        $stmt = $this->db->prepare($query);

        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':nombre', $data['nombre_producto']);
        $stmt->bindParam(':desc_corta', $data['descripcion_corta']);
        $stmt->bindParam(':desc_larga', $data['descripcion_larga']);
        $stmt->bindParam(':precio', $data['precio_unitario']);
        $stmt->bindParam(':precio_oferta', $data['precio_oferta']);
        $stmt->bindParam(':unidad', $data['unidad_medida']);
        $stmt->bindParam(':stock', $data['stock_total']);
        $stmt->bindParam(':destacado', $data['destacado']);
        $stmt->bindParam(':activo', $data['activo']);

        return $stmt->execute();
    }

    /**
     * Contar productos
     */
    public function count($filters = [])
    {
        $query = "SELECT COUNT(*) FROM {$this->table} WHERE activo = 1";
        $params = [];

        if (!empty($filters['categoria'])) {
            $query .= " AND id_categoria_producto = :categoria";
            $params[':categoria'] = $filters['categoria'];
        }

        if (!empty($filters['search'])) {
            $query .= " AND (nombre_producto LIKE :search OR descripcion_corta LIKE :search)";
            $params[':search'] = '%' . $filters['search'] . '%';
        }

        $stmt = $this->db->prepare($query);

        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }

        $stmt->execute();
        return $stmt->fetchColumn();
    }

    /**
     * Obtener productos relacionados
     */
    public function getRelated($productoId, $categoriaId, $limit = 4)
    {
        $query = "SELECT p.*, 
                  (SELECT url_imagen FROM imagenes_producto WHERE id_producto = p.id_producto AND es_principal = 1 LIMIT 1) as imagen_principal
                  FROM {$this->table} p
                  WHERE p.id_categoria_producto = :categoria 
                  AND p.id_producto != :id 
                  AND p.activo = 1
                  ORDER BY RAND()
                  LIMIT :limit";

        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':categoria', $categoriaId);
        $stmt->bindParam(':id', $productoId);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }
}
