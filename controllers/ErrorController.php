<?php

/**
 * Controlador de Errores
 */

require_once ROOT_PATH . '/core/Controller.php';

class ErrorController extends Controller
{

    /**
     * Error 404 - Página no encontrada
     */
    public function error404()
    {
        http_response_code(404);

        $data = [
            'title' => 'Página no encontrada - ' . APP_NAME
        ];

        $this->view('errors/404', $data);
    }

    /**
     * Error 403 - Acceso denegado
     */
    public function error403()
    {
        http_response_code(403);

        $data = [
            'title' => 'Acceso denegado - ' . APP_NAME
        ];

        $this->view('errors/403', $data);
    }

    /**
     * Error 500 - Error del servidor
     */
    public function error500()
    {
        http_response_code(500);

        $data = [
            'title' => 'Error del servidor - ' . APP_NAME
        ];

        $this->view('errors/500', $data);
    }
}
