<?php

/**
 * Clase Controller - Controlador base
 */

class Controller
{
    protected $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Cargar vista
     */
    protected function view($view, $data = [])
    {
        extract($data);

        $viewFile = ROOT_PATH . '/views/' . $view . '.php';

        if (file_exists($viewFile)) {
            require_once $viewFile;
        } else {
            die("Vista no encontrada: " . $view);
        }
    }

    /**
     * Cargar modelo
     */
    protected function model($model)
    {
        $modelFile = ROOT_PATH . '/models/' . $model . '.php';

        if (file_exists($modelFile)) {
            require_once $modelFile;
            return new $model($this->db);
        } else {
            die("Modelo no encontrado: " . $model);
        }
    }

    /**
     * Redireccionar
     */
    protected function redirect($url)
    {
        header('Location: ' . APP_URL . '/' . $url);
        exit;
    }

    /**
     * Devolver JSON
     */
    protected function json($data, $statusCode = 200)
    {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }

    /**
     * Verificar autenticación
     */
    protected function requireAuth()
    {
        if (!Session::isAuthenticated()) {
            $this->redirect('auth/login');
        }
    }

    /**
     * Verificar rol específico
     */
    protected function requireRole($role)
    {
        $this->requireAuth();

        if (Session::getUserRole() !== $role) {
            Session::setFlash('error', 'No tienes permisos para acceder a esta sección');
            $this->redirect('');
        }
    }

    /**
     * Sanitizar entrada
     */
    protected function sanitize($data)
    {
        if (is_array($data)) {
            return array_map([$this, 'sanitize'], $data);
        }

        return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
    }

    /**
     * Validar CSRF token
     */
    protected function validateCSRF($token)
    {
        if (!isset($token) || $token !== Session::get('csrf_token')) {
            $this->json(['error' => 'Token CSRF inválido'], 403);
        }
    }

    /**
     * Generar CSRF token
     */
    protected function generateCSRF()
    {
        if (!Session::has('csrf_token')) {
            Session::set('csrf_token', bin2hex(random_bytes(32)));
        }
        return Session::get('csrf_token');
    }
}
