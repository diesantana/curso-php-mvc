<?php

namespace bng\Controllers;

use bng\Models\Agents;

/**
 * Agent Controller - Responsável pelas operações relacionadas a entidade Agent 
 */
class Agent extends BaseController
{

    /**
     * Exibe os dados dos clientes de acordo com o agent
     */
    public function my_clients()
    {
        // Verificação de segurança, garantido que apenas agentes autenticados acessem o método
        if (!checkSession() || $_SESSION['user']->profile != 'agent') {
            header('Location: index.php'); // redirecionada para o index.php que consequentemente vai chamar o método index() do controlador main
        }

        // ID do usuário logado
        $agentId = $_SESSION['user']->id;
        // Cria uma instância do model
        $agentModel = new Agents();
        // Busca os clientes através do método get_agent_clients no model
        $results = $agentModel->get_agent_clients($agentId);

        // Armazena os dados para serem utilziados na view
        $data['user'] = $_SESSION['user'];
        $data['clients'] = $results['data'];

        // Renderiza a view "agent_clients", responsável pela exibição dos clientes
        $this->view('layouts/html_header'); // Estrutura inicial do HTML
        $this->view('navbar', $data); // navbar
        $this->view('agent_clients', $data); // exibição dos clientes
        $this->view('footer'); // footer
        $this->view('layouts/html_footer'); // Estrutura final do HTML
    }

    /**
     * Renderiza o formulário de inserção de novos clientes
     */
    public function new_client_frm()
    {
        // Verifica se a sessão está ativa e se o usuário logado é um agente
        if (!checkSession() || $_SESSION['user']->profile != 'agent') {
            // redireciona para a página inicial (login)
            header('Location: index.php');
        }

        // armazena os dados do usuário logado
        $data['user'] = $_SESSION['user'];

        // Renderiza as views
        $this->view('layouts/html_header'); // Estrutura inicial do HTML
        $this->view('navbar', $data); // navbar
        $this->view('insert_client_frm'); // formulário de cadastro
        $this->view('footer'); // footer
        $this->view('layouts/html_footer'); // Estrutura final do HTML
    }
}
