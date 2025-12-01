<?php

/**
 * Controlador de Autenticación
 */

require_once ROOT_PATH . '/core/Controller.php';
require_once ROOT_PATH . '/models/Usuario.php';

class AuthController extends Controller
{
    private $usuarioModel;

    public function __construct()
    {
        parent::__construct();
        $this->usuarioModel = new Usuario($this->db);
    }

    /**
     * Mostrar formulario de login
     */
    public function login()
    {
        if (Session::isAuthenticated()) {
            $this->redirectByRole();
        }

        $data = [
            'title' => 'Iniciar Sesión - ' . APP_NAME,
            'csrf_token' => $this->generateCSRF()
        ];

        $this->view('auth/login', $data);
    }

    /**
     * Procesar login
     */
    public function processLogin()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('auth/login');
        }

        // Validar CSRF
        $this->validateCSRF($_POST['csrf_token'] ?? '');

        $email = $this->sanitize($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        // Validar campos
        if (empty($email) || empty($password)) {
            Session::setFlash('error', 'Por favor complete todos los campos');
            $this->redirect('auth/login');
        }

        // Verificar credenciales
        $user = $this->usuarioModel->verifyPassword($email, $password);

        if ($user) {
            // Establecer sesión
            Session::setUser(
                $user['id_usuario'],
                $user['nombre_completo'],
                $user['nombre_rol'],
                $user['email']
            );

            // Obtener permisos
            $permissions = $this->usuarioModel->getPermissions($user['id_usuario']);
            Session::set('user_permissions', $permissions);

            // Actualizar último acceso
            $this->usuarioModel->updateLastAccess($user['id_usuario']);

            Session::setFlash('success', '¡Bienvenido ' . $user['nombre_completo'] . '!');

            $this->redirectByRole();
        } else {
            Session::setFlash('error', 'Credenciales incorrectas');
            $this->redirect('auth/login');
        }
    }

    /**
     * Mostrar formulario de registro
     */
    public function register()
    {
        if (Session::isAuthenticated()) {
            $this->redirectByRole();
        }

        $data = [
            'title' => 'Registrarse - ' . APP_NAME,
            'csrf_token' => $this->generateCSRF()
        ];

        $this->view('auth/register', $data);
    }

    /**
     * Procesar registro
     */
    public function processRegister()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('auth/register');
        }

        // Validar CSRF
        $this->validateCSRF($_POST['csrf_token'] ?? '');

        $data = [
            'nombre_completo' => $this->sanitize($_POST['nombre_completo'] ?? ''),
            'email' => $this->sanitize($_POST['email'] ?? ''),
            'telefono' => $this->sanitize($_POST['telefono'] ?? ''),
            'password' => $_POST['password'] ?? '',
            'password_confirm' => $_POST['password_confirm'] ?? '',
            'direccion' => $this->sanitize($_POST['direccion'] ?? ''),
            'ciudad' => $this->sanitize($_POST['ciudad'] ?? ''),
            'departamento' => $this->sanitize($_POST['departamento'] ?? ''),
            'id_rol' => 3 // Cliente por defecto
        ];

        // Validaciones
        $errors = [];

        if (empty($data['nombre_completo'])) {
            $errors[] = 'El nombre completo es requerido';
        }

        if (empty($data['email']) || !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Email inválido';
        }

        if ($this->usuarioModel->emailExists($data['email'])) {
            $errors[] = 'El email ya está registrado';
        }

        if (strlen($data['password']) < 6) {
            $errors[] = 'La contraseña debe tener al menos 6 caracteres';
        }

        if ($data['password'] !== $data['password_confirm']) {
            $errors[] = 'Las contraseñas no coinciden';
        }

        if (!empty($errors)) {
            Session::setFlash('error', implode('<br>', $errors));
            $this->redirect('auth/register');
        }

        // Crear usuario
        $userId = $this->usuarioModel->create($data);

        if ($userId) {
            Session::setFlash('success', 'Registro exitoso. Por favor inicia sesión');
            $this->redirect('auth/login');
        } else {
            Session::setFlash('error', 'Error al registrar usuario');
            $this->redirect('auth/register');
        }
    }

    /**
     * Cerrar sesión
     */
    public function logout()
    {
        Session::destroy();
        $this->redirect('auth/login');
    }

    /**
     * Redireccionar según el rol del usuario
     */
    private function redirectByRole()
    {
        $role = Session::getUserRole();

        switch ($role) {
            case 'Administrador':
                $this->redirect('admin/dashboard');
                break;
            case 'Encargado Sucursal':
                $this->redirect('sucursal/dashboard');
                break;
            case 'Cliente':
                $this->redirect('tienda');
                break;
            default:
                $this->redirect('');
        }
    }
}
