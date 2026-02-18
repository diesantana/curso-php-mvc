<?php

namespace bng\Controllers;

use bng\Controllers\BaseController;

/**
 * Controller responsável por lidar com rotas inexistentes.
 */
class ErrorController extends BaseController
{
    /**
     * Renderiza a página 404
     */
    public function notFound()
    {
        http_response_code(404);

        $this->view('layouts/html_header');
        $this->view('error_404');
        $this->view('layouts/html_footer');
    }
}
