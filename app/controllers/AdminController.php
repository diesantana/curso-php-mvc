<?php

declare(strict_types=1);

namespace bng\Controllers;

use bng\Controllers\BaseController;
use bng\Models\AdminModel;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

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

    /**
     * Exporta a lista global de clientes para um arquivo XLSX.
     */
    public function export_global_clients_xlsx()
    {
        // Verifica se existe um admin logado
        if (!checkSession() || $_SESSION['user']->profile != 'admin') {
            header('Location: index.php');
        }

        // Busca a lista global de clientes
        $adminModel = new AdminModel();
        $clients = $adminModel->get_all_clients(); // Busca os clientes

        // Cria o header do arquivo
        $data[] = ['name', 'gender', 'birthdate', 'email', 'phone', 'interests', 'created_at', 'agent'];

        // Salva os clientes no array $data
        foreach ($clients as $currentClient) {
            // remove o id
            $currentClientArray = (array) $currentClient;
            unset($currentClientArray['id']);
            // Adiciona o cliente ao array
            $data[] = $currentClientArray;
            // estamos fazendo um casting para array pois o método 
            // "get_all_clients()" retorna um objeto stdClass
        }

        // Salva os clientes em um arquivo XLSX
        $filename = 'output_' . time() . '.xlsx'; // nomeia o arquivo
        $spreadsheet = new Spreadsheet();
        $spreadsheet->removeSheetByIndex(0);
        $worksheet = new Worksheet($spreadsheet, 'dados');
        $spreadsheet->addSheet($worksheet);
        $worksheet->fromArray($data);

        $writer = new Xlsx($spreadsheet);
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Dispostion: attachment; filename="' . urlencode($filename) . '"');
        $writer->save('php://output');

        // Logger
        logger(get_active_username() . '- Fez download da lista global de clientes');
    }

    /** 
     * Renderiza a view Stats, que exibe dados dos clientes e agentes.
     */
    public function show_statistics()
    {
        // Verifica se existe um admin logado
        if (!checkSession() || $_SESSION['user']->profile != 'admin') {
            header('Location: index.php');
        }

        $adminModel = new AdminModel();

        // Busca o total de clientes por agente
        $clientsPerAgents = $adminModel->get_client_count_by_agent();

        // Prepara os dados para a view

        $data['user'] = $_SESSION['user'];
        $data['clientsPerAgents'] = $clientsPerAgents;

        // Renderiza a view "stats", responsável pela exibição dos clientes
        $this->view('layouts/html_header'); // Estrutura inicial do HTML
        $this->view('navbar', $data); // navbar
        $this->view('stats', $data); // view de estatísticas
        $this->view('footer'); // footer
        $this->view('layouts/html_footer'); // Estrutura final do HTML

    }
}
