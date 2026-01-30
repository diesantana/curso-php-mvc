<?php

declare(strict_types=1);

namespace bng\Controllers;

use bng\Controllers\BaseController;
use bng\Models\AdminModel;

class AdminController extends BaseController
{
    public function show_all_clients()
    {
        // Verifica se existe um admin logado
        if (!checkSession() || $_SESSION['user']->profile != 'admin') {
            header('Location: index.php'); 
        }

        // Instância o model
        $adminModel = new AdminModel();
        // Chama o método para buscar os clientes. 
    }
}
