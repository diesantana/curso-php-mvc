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
        $clients = $adminModel->get_all_clients(); // Busca os clientes

        // prepara os dados para a view
        $data['user'] = $_SESSION['user'];
        $data['clients'] = $clients;

        // Renderiza a view "global_clients", responsável pela exibição dos clientes
        $this->view('layouts/html_header'); // Estrutura inicial do HTML
        $this->view('navbar', $data); // navbar
        $this->view('global_clients', $data); // exibição dos clientes
        $this->view('footer'); // footer
        $this->view('layouts/html_footer'); // Estrutura final do HTML
    }
}
