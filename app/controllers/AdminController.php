<?php

declare(strict_types=1);

namespace bng\Controllers;

use bng\Controllers\BaseController;
use bng\Models\AdminModel;

class AdminController extends BaseController
{
    /**
     * Exibe a lista global de clientes com os seus respectivos agentes.
     */
    public function show_all_clients()
    {
        // Verifica se existe um admin logado
        if (!checkSession() || $_SESSION['user']->profile != 'admin') {
            header('Location: index.php'); 
        }

        // Instância o model
        $adminModel = new AdminModel();
        $adminModel->get_all_clients();
        // Chama o método para buscar os clientes. 
    }
}
