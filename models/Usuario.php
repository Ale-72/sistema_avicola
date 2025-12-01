<?php

/**
 * Modelo de Usuario
 */

class Usuario
{
    private $db;
    private $table = 'usuarios';

    public function __construct($db)
    {
        $this->db = $db;
    }

    /**
     * Buscar usuario por email
     */
    public function findByEmail($email)
    {
        $query = "SELECT u.*, r.nombre_rol, r.nivel_acceso 
                  FROM {$this->table} u 
                  INNER JOIN roles r ON u.id_rol = r.id_rol 
                  WHERE u.email = :email AND u.activo = 1";

        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();

        return $stmt->fetch();
    }

    /**
     * Buscar usuario por ID
     */
    public function findById($id)
    {
        $query = "SELECT u.*, r.nombre_rol, r.nivel_acceso 
                  FROM {$this->table} u 
                  INNER JOIN roles r ON u.id_rol = r.id_rol 
                  WHERE u.id_usuario = :id";

        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();

        return $stmt->fetch();
    }

    /**
     * Crear nuevo usuario
     */
    public function create($data)
    {
        $query = "INSERT INTO {$this->table} 
                  (id_rol, nombre_completo, email, telefono, password_hash, direccion, ciudad, departamento) 
                  VALUES (:id_rol, :nombre, :email, :telefono, :password, :direccion, :ciudad, :departamento)";

        $stmt = $this->db->prepare($query);

        // Hash de contraseña
        $passwordHash = password_hash($data['password'], PASSWORD_DEFAULT);

        $stmt->bindParam(':id_rol', $data['id_rol']);
        $stmt->bindParam(':nombre', $data['nombre_completo']);
        $stmt->bindParam(':email', $data['email']);
        $stmt->bindParam(':telefono', $data['telefono']);
        $stmt->bindParam(':password', $passwordHash);
        $stmt->bindParam(':direccion', $data['direccion']);
        $stmt->bindParam(':ciudad', $data['ciudad']);
        $stmt->bindParam(':departamento', $data['departamento']);

        if ($stmt->execute()) {
            return $this->db->lastInsertId();
        }

        return false;
    }

    /**
     * Actualizar usuario
     */
    public function update($id, $data)
    {
        $query = "UPDATE {$this->table} SET 
                  nombre_completo = :nombre,
                  telefono = :telefono,
                  direccion = :direccion,
                  ciudad = :ciudad,
                  departamento = :departamento
                  WHERE id_usuario = :id";

        $stmt = $this->db->prepare($query);

        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':nombre', $data['nombre_completo']);
        $stmt->bindParam(':telefono', $data['telefono']);
        $stmt->bindParam(':direccion', $data['direccion']);
        $stmt->bindParam(':ciudad', $data['ciudad']);
        $stmt->bindParam(':departamento', $data['departamento']);

        return $stmt->execute();
    }

    /**
     * Actualizar contraseña
     */
    public function updatePassword($id, $newPassword)
    {
        $query = "UPDATE {$this->table} SET password_hash = :password WHERE id_usuario = :id";

        $stmt = $this->db->prepare($query);
        $passwordHash = password_hash($newPassword, PASSWORD_DEFAULT);

        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':password', $passwordHash);

        return $stmt->execute();
    }

    /**
     * Verificar contraseña
     */
    public function verifyPassword($email, $password)
    {
        $user = $this->findByEmail($email);

        if ($user && password_verify($password, $user['password_hash'])) {
            return $user;
        }

        return false;
    }

    /**
     * Actualizar último acceso
     */
    public function updateLastAccess($id)
    {
        $query = "UPDATE {$this->table} SET ultimo_acceso = NOW() WHERE id_usuario = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    /**
     * Obtener todos los usuarios
     */
    public function getAll($filters = [])
    {
        $query = "SELECT u.*, r.nombre_rol 
                  FROM {$this->table} u 
                  INNER JOIN roles r ON u.id_rol = r.id_rol";

        $conditions = [];
        $params = [];

        if (!empty($filters['rol'])) {
            $conditions[] = "u.id_rol = :rol";
            $params[':rol'] = $filters['rol'];
        }

        if (!empty($filters['activo'])) {
            $conditions[] = "u.activo = :activo";
            $params[':activo'] = $filters['activo'];
        }

        if (!empty($conditions)) {
            $query .= " WHERE " . implode(' AND ', $conditions);
        }

        $query .= " ORDER BY u.fecha_registro DESC";

        $stmt = $this->db->prepare($query);

        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }

        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Obtener permisos de usuario
     */
    public function getPermissions($userId)
    {
        $query = "SELECT p.nombre_permiso 
                  FROM permisos p
                  INNER JOIN roles_permisos rp ON p.id_permiso = rp.id_permiso
                  INNER JOIN usuarios u ON rp.id_rol = u.id_rol
                  WHERE u.id_usuario = :user_id";

        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();

        return array_column($stmt->fetchAll(), 'nombre_permiso');
    }

    /**
     * Verificar si el email ya existe
     */
    public function emailExists($email, $excludeId = null)
    {
        $query = "SELECT COUNT(*) FROM {$this->table} WHERE email = :email";

        if ($excludeId) {
            $query .= " AND id_usuario != :exclude_id";
        }

        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':email', $email);

        if ($excludeId) {
            $stmt->bindParam(':exclude_id', $excludeId);
        }

        $stmt->execute();
        return $stmt->fetchColumn() > 0;
    }
}
