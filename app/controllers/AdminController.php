<?php

declare(strict_types=1);

namespace bng\Controllers;

use bng\Controllers\BaseController;
use bng\Models\AdminModel;
use Mpdf\Mpdf;
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
            exit;
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
            exit;
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
            exit;
        }

        $adminModel = new AdminModel();

        // Busca o total de clientes por agente
        $clientsPerAgents = $adminModel->get_client_count_by_agent();

        // Prepara os dados para a view
        $data['user'] = $_SESSION['user'];
        $data['clientsPerAgents'] = $clientsPerAgents;

        // Dados para o gráfico
        $data['chartjs'] = true; // Ativa o chart.js
        if (count($clientsPerAgents) != 0) {
            $labels_temp = [];
            $totals_temp = [];
            foreach ($clientsPerAgents as $currentData) {
                $labels_temp[] = $currentData->name; // Pega o nome do agente
                $totals_temp[] = $currentData->total; // Pega o total de clientes por agente
            }
        }
        $data['chartLabels'] = '["' . implode('", "', $labels_temp) . '"]'; // resultado é uma string =  ['data1', 'data2']
        $data['chartTotals'] = '["' . implode('", "', $totals_temp) . '"]';

        // Dados para as estatíticas globais
        $data['globalStatistics'] = $adminModel->get_global_statistics();

        // Renderiza a view "stats", responsável pela exibição dos clientes
        $this->view('layouts/html_header', $data); // Estrutura inicial do HTML
        $this->view('navbar', $data); // navbar
        $this->view('stats', $data); // view de estatísticas
        $this->view('footer'); // footer
        $this->view('layouts/html_footer'); // Estrutura final do HTML
    }

    /**
     * Gera relatórios em PDF das estatísticas globais.
     */
    public function export_statistics_pdf()
    {
        // Verifica se existe um admin logado
        if (!checkSession() || $_SESSION['user']->profile != 'admin') {
            header('Location: index.php');
            exit;
        }

        // Limpa qualquer saída antes de salvar o pdf (Evita corromper o arquivo)
        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        $adminModel = new AdminModel();

        // Busca o total de clientes por agente
        $clientsPerAgents = $adminModel->get_client_count_by_agent();
        // Estatíticas globais
        $globalStatistics = $adminModel->get_global_statistics();

        // Cria o arquivo PDF
        $pdf = new Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4',
            'orientation' => 'P'
        ]);

        // Define as cordenadas iniciais (layout)
        $x = 50;    // horizontal
        $y = 50;    // vertical
        $html = "";

        // Logo
        $html .= '<div style="position: absolute; left: ' . $x . 'px; top: ' . $y . 'px;">';
        $html .= '<img src="assets/images/logo_32.png">';
        $html .= '</div>';
        $html .= '<h2 style="position: absolute; left: ' . ($x + 50) . 'px; top: ' . ($y - 10) . 'px;">' . APP_NAME . '</h2>';

        // Separator
        $y += 50;
        $html .= '<div style="position: absolute; left: ' . $x . 'px; top: ' . $y . 'px; width: 700px; height: 1px; background-color: rgb(200,200,200);"></div>';

        // Titulo
        $y += 20;
        $html .= '<h3 style="position: absolute; left: ' . $x . 'px; top: ' . $y . 'px; width: 700px; text-align: center;">REPORT DE DADOS DE ' . date('d-m-Y') . '</h3>';

        // -----------------------------------------------------------
        // Clientes por Agente
        $y += 50;

        $html .= '
            <div style="position: absolute; left: ' . ($x + 90) . 'px; top: ' . $y . 'px; width: 500px;">
                <table style="border: 1px solid black; border-collapse: collapse; width: 100%;">
                    <thead>
                        <tr>
                            <th style="width: 60%; border: 1px solid black; text-align: left;">Agente</th>
                            <th style="width: 40%; border: 1px solid black;">N.º de Clientes</th>
                        </tr>
                    </thead>
                    <tbody>';
        foreach ($clientsPerAgents as $agent) {
            $html .=
                '<tr style="border: 1px solid black;">
                    <td style="border: 1px solid black;">' . $agent->name . '</td>
                    <td style="text-align: center;">' . $agent->total . '</td>
                </tr>';
            $y += 25;
        }

        $html .= '
            </tbody>
            </table>
            </div>';

        // -----------------------------------------------------------
        // Estatísticas globais
        $y += 50;

        $html .= '
            <div style="position: absolute; left: ' . ($x + 90) . 'px; top: ' . $y . 'px; width: 500px;">
                <table style="border: 1px solid black; border-collapse: collapse; width: 100%;">
                    <thead>
                        <tr>
                            <th style="width: 60%; border: 1px solid black; text-align: left;">Item</th>
                            <th style="width: 40%; border: 1px solid black;">Valor</th>
                        </tr>
                    </thead>
                    <tbody>';

        $html .= '<tr><td>Total agentes:</td><td style="text-align: right;">' . $globalStatistics['totalAgents'] . '</td></tr>';
        $html .= '<tr><td>Total clientes:</td><td style="text-align: right;">' . $globalStatistics['totalClients'] . '</td></tr>';
        $html .= '<tr><td>Total clientes removidos:</td><td style="text-align: right;">' . $globalStatistics['totalClientsInactives'] . '</td></tr>';
        $html .= '<tr><td>Média de clientes por agente:</td><td style="text-align: right;">' . $globalStatistics['AverageClientsPerAgent'] . '</td></tr>';
        $html .= '<tr><td>Idade do cliente mais novo:</td><td style="text-align: right;">' . $globalStatistics['youngerAge'] . ' anos</td></tr>';
        $html .= '<tr><td>Idade do cliente mais velho:</td><td style="text-align: right;">' . $globalStatistics['olderAge'] . ' anos</td></tr>';
        $html .= '<tr><td>Media de idade dos clientes:</td><td style="text-align: right;">' . $globalStatistics['averageAge'] . ' anos</td></tr>';
        $html .= '<tr><td>Percentagem de homens:</td><td style="text-align: right;">' . $globalStatistics['percentageMen'] . '%</td></tr>';
        $html .= '<tr><td>Percentagem de mulheres:</td><td style="text-align: right;">' . $globalStatistics['percentageWomen'] . '%</td></tr>';

        $html .= '
                    </tbody>
                </table>
            </div>';

        // -----------------------------------------------------------

        $pdf->WriteHTML($html);

        $pdf->Output();
    }

    
    /**
     * Renderiza a tabela de gestão de agentes. 
     * Exibe uma lista contendo todos os usuários, sejam eles agentes
     * ou administradores. 
     */
    public function show_agent_management()
    {
        // Verifica se existe um admin logado
        if (!checkSession() || $_SESSION['user']->profile != 'admin') {
            header('Location: index.php');
            exit;
        }

        $adminModel = new AdminModel();

        // Busca os dados dos agentes
        $agentsData = $adminModel->get_all_agents();

        // Prepara os dados para a view
        $data['user'] = $_SESSION['user'];
        $data['agentsData'] = $agentsData;

        // Renderiza a view "agents_management"
        $this->view('layouts/html_header', $data); // Estrutura inicial do HTML
        $this->view('navbar', $data); // navbar
        $this->view('agents_management', $data); // Lista de agentes
        $this->view('footer'); // footer
        $this->view('layouts/html_footer'); // Estrutura final do HTML
    }
}
