<?php

/**
 * Clase Session - Manejo de sesiones de usuario
 */

class Session
{

    /**
     * Iniciar sesión
     */
    public static function start()
    {
        if (session_status() === PHP_SESSION_NONE) {
            ini_set('session.cookie_httponly', 1);
            ini_set('session.use_only_cookies', 1);
            ini_set('session.cookie_secure', isset($_SERVER['HTTPS']));

            session_name(SESSION_NAME);
            session_start();

            // Regenerar ID de sesión periódicamente
            if (!isset($_SESSION['created'])) {
                $_SESSION['created'] = time();
            } else if (time() - $_SESSION['created'] > 1800) {
                session_regenerate_id(true);
                $_SESSION['created'] = time();
            }
        }
    }

    /**
     * Establecer dato en sesión
     */
    public static function set($key, $value)
    {
        self::start();
        $_SESSION[$key] = $value;
    }

    /**
     * Obtener dato de sesión
     */
    public static function get($key, $default = null)
    {
        self::start();
        return $_SESSION[$key] ?? $default;
    }

    /**
     * Verificar si existe un dato en sesión
     */
    public static function has($key)
    {
        self::start();
        return isset($_SESSION[$key]);
    }

    /**
     * Eliminar dato de sesión
     */
    public static function delete($key)
    {
        self::start();
        if (isset($_SESSION[$key])) {
            unset($_SESSION[$key]);
        }
    }

    /**
     * Destruir sesión completa
     */
    public static function destroy()
    {
        self::start();
        $_SESSION = [];

        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params["path"],
                $params["domain"],
                $params["secure"],
                $params["httponly"]
            );
        }

        session_destroy();
    }

    /**
     * Verificar si usuario está autenticado
     */
    public static function isAuthenticated()
    {
        return self::has('user_id') && self::has('user_role');
    }

    /**
     * Obtener ID de usuario autenticado
     */
    public static function getUserId()
    {
        return self::get('user_id');
    }

    /**
     * Obtener rol de usuario
     */
    public static function getUserRole()
    {
        return self::get('user_role');
    }

    /**
     * Obtener nombre de usuario
     */
    public static function getUserName()
    {
        return self::get('user_name');
    }

    /**
     * Establecer datos de usuario en sesión
     */
    public static function setUser($userId, $userName, $userRole, $userEmail)
    {
        self::set('user_id', $userId);
        self::set('user_name', $userName);
        self::set('user_role', $userRole);
        self::set('user_email', $userEmail);
    }

    /**
     * Establecer mensaje flash
     */
    public static function setFlash($type, $message)
    {
        self::set('flash_' . $type, $message);
    }

    /**
     * Obtener y eliminar mensaje flash
     */
    public static function getFlash($type)
    {
        $message = self::get('flash_' . $type);
        self::delete('flash_' . $type);
        return $message;
    }

    /**
     * Verificar permisos de usuario
     */
    public static function hasPermission($permission)
    {
        $permissions = self::get('user_permissions', []);
        return in_array($permission, $permissions);
    }
}
