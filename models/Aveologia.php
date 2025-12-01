<?php

/**
 * Modelo de Aveología
 */

class Aveologia
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    /**
     * Buscar enfermedades por síntomas
     */
    public function buscarEnfermedadesPorSintomas($sintomas)
    {
        // $sintomas es un array de IDs de síntomas seleccionados
        $placeholders = implode(',', array_fill(0, count($sintomas), '?'));

        $query = "SELECT e.*, 
                  COUNT(DISTINCT se.id_sintoma) as coincidencias,
                  GROUP_CONCAT(DISTINCT s.nombre_sintoma SEPARATOR ', ') as sintomas_coincidentes
                  FROM enfermedades e
                  INNER JOIN sintomas_enfermedades se ON e.id_enfermedad = se.id_enfermedad
                  INNER JOIN sintomas s ON se.id_sintoma = s.id_sintoma
                  WHERE se.id_sintoma IN ($placeholders) AND e.activo = 1
                  GROUP BY e.id_enfermedad
                  ORDER BY coincidencias DESC, e.mortalidad_estimada DESC
                  LIMIT 5";

        $stmt = $this->db->prepare($query);
        $stmt->execute($sintomas);

        return $stmt->fetchAll();
    }

    /**
     * Búsqueda de sintomas por texto
     */
    public function buscarSintomas($texto)
    {
        $query = "SELECT * FROM sintomas 
                  WHERE MATCH(nombre_sintoma, descripcion, keywords) AGAINST(:texto IN NATURAL LANGUAGE MODE)
                  OR nombre_sintoma LIKE :like
                  AND activo = 1
                  LIMIT 10";

        $stmt = $this->db->prepare($query);
        $stmt->bindValue(':texto', $texto);
        $stmt->bindValue(':like', '%' . $texto . '%');
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * Obtener todos los síntomas
     */
    public function getAllSintomas($categoria = null)
    {
        $query = "SELECT * FROM sintomas WHERE activo = 1";

        if ($categoria) {
            $query .= " AND categoria = :categoria";
        }

        $query .= " ORDER BY categoria, gravedad DESC, nombre_sintoma ASC";

        $stmt = $this->db->prepare($query);

        if ($categoria) {
            $stmt->bindParam(':categoria', $categoria);
        }

        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Obtener enfermedad por ID
     */
    public function getEnfermedadById($id)
    {
        $query = "SELECT * FROM enfermedades WHERE id_enfermedad = :id AND activo = 1";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();

        return $stmt->fetch();
    }

    /**
     * Obtener síntomas de una enfermedad
     */
    public function getSintomasDeEnfermedad($enfermedadId)
    {
        $query = "SELECT s.*, se.frecuencia, se.intensidad
                  FROM sintomas s
                  INNER JOIN sintomas_enfermedades se ON s.id_sintoma = se.id_sintoma
                  WHERE se.id_enfermedad = :enfermedad
                  ORDER BY se.frecuencia DESC, se.intensidad DESC";

        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':enfermedad', $enfermedadId);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * Obtener tratamientos de una enfermedad
     */
    public function getTratamientos($enfermedadId)
    {
        $query = "SELECT * FROM tratamientos 
                  WHERE id_enfermedad = :enfermedad AND activo = 1
                  ORDER BY efectividad DESC, orden_recomendacion ASC";

        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':enfermedad', $enfermedadId);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * Obtener remedios de una enfermedad
     */
    public function getRemedios($enfermedadId)
    {
        $query = "SELECT * FROM remedios 
                  WHERE id_enfermedad = :enfermedad AND activo = 1
                  ORDER BY efectividad_estimada DESC";

        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':enfermedad', $enfermedadId);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * Obtener artículos de la base de conocimientos
     */
    public function getArticulos($filters = [], $limit = 10, $offset = 0)
    {
        $query = "SELECT a.*, c.nombre_categoria, u.nombre_completo as autor_nombre
                  FROM articulos_aveologia a
                  INNER JOIN categorias_aveologia c ON a.id_categoria = c.id_categoria
                  LEFT JOIN usuarios u ON a.autor_id = u.id_usuario
                  WHERE a.activo = 1";

        $params = [];

        if (!empty($filters['categoria'])) {
            $query .= " AND a.id_categoria = :categoria";
            $params[':categoria'] = $filters['categoria'];
        }

        if (!empty($filters['search'])) {
            $query .= " AND MATCH(a.titulo, a.contenido, a.tags) AGAINST(:search IN NATURAL LANGUAGE MODE)";
            $params[':search'] = $filters['search'];
        }

        if (!empty($filters['destacado'])) {
            $query .= " AND a.destacado = 1";
        }

        $query .= " ORDER BY a.destacado DESC, a.fecha_publicacion DESC LIMIT :limit OFFSET :offset";

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
     * Obtener artículo por slug
     */
    public function getArticuloBySlug($slug)
    {
        $query = "SELECT a.*, c.nombre_categoria, u.nombre_completo as autor_nombre
                  FROM articulos_aveologia a
                  INNER JOIN categorias_aveologia c ON a.id_categoria = c.id_categoria
                  LEFT JOIN usuarios u ON a.autor_id = u.id_usuario
                  WHERE a.slug = :slug AND a.activo = 1";

        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':slug', $slug);
        $stmt->execute();

        $articulo = $stmt->fetch();

        // Incrementar visitas
        if ($articulo) {
            $this->incrementarVisitas($articulo['id_articulo']);
        }

        return $articulo;
    }

    /**
     * Incrementar contador de visitas
     */
    private function incrementarVisitas($articuloId)
    {
        $query = "UPDATE articulos_aveologia SET visitas = visitas + 1 WHERE id_articulo = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $articuloId);
        return $stmt->execute();
    }

    /**
     * Obtener categorías de aveología
     */
    public function getCategorias()
    {
        $query = "SELECT * FROM categorias_aveologia WHERE activo = 1 ORDER BY orden ASC";
        $stmt = $this->db->query($query);
        return $stmt->fetchAll();
    }

    /**
     * Obtener enfermedad (alias para compatibilidad)
     */
    public function getEnfermedad($id)
    {
        return $this->getEnfermedadById($id);
    }

    /**
     * Obtener enfermedades más comunes
     */
    public function getEnfermedadesMasComunes($limit = 5)
    {
        $query = "SELECT e.*, COUNT(se.id_sintoma) as total_sintomas
                  FROM enfermedades e
                  LEFT JOIN sintomas_enfermedades se ON e.id_enfermedad = se.id_enfermedad
                  WHERE e.activo = 1
                  GROUP BY e.id_enfermedad
                  ORDER BY e.frecuencia_cases DESC, total_sintomas DESC
                  LIMIT :limit";

        $stmt = $this->db->prepare($query);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Obtener todas las enfermedades con filtros
     */
    public function getAllEnfermedades($filters = [])
    {
        $query = "SELECT e.*, COUNT(DISTINCT se.id_sintoma) as total_sintomas
                  FROM enfermedades e
                  LEFT JOIN sintomas_enfermedades se ON e.id_enfermedad = se.id_enfermedad
                  WHERE e.activo = 1";

        $params = [];

        if (!empty($filters['tipo'])) {
            $query .= " AND e.tipo_enfermedad = :tipo";
            $params[':tipo'] = $filters['tipo'];
        }

        if (!empty($filters['busqueda'])) {
            $query .= " AND (e.nombre_enfermedad LIKE :busqueda OR e.descripcion LIKE :busqueda)";
            $params[':busqueda'] = '%' . $filters['busqueda'] . '%';
        }

        $query .= " GROUP BY e.id_enfermedad ORDER BY e.nombre_enfermedad ASC";

        $stmt = $this->db->prepare($query);

        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }

        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Incrementar visitas (método público)
     */
    public function incrementarVistas($articuloId)
    {
        $query = "UPDATE articulos_aveologia SET visitas = visitas + 1 WHERE id_articulo = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $articuloId);
        return $stmt->execute();
    }
}
