<?php

declare(strict_types=1);

namespace bng\Controllers;

use bng\Controllers\BaseController;
use bng\Models\AdminModel;
use bng\System\SendEmail;
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

        // Encripta o ID
        foreach ($agentsData as $currentAgent) {
            $currentAgent->id = aes_encrypt((string) $currentAgent->id);
        }

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

    /**
     * Renderiza o formulário para adicionar um novo agente.
     */
    public function show_new_agent_form()
    {
        // Verifica se existe um admin logado
        if (!checkSession() || $_SESSION['user']->profile != 'admin') {
            header('Location: index.php');
            exit;
        }

        // Verifica se há mensagens de validação guardadas na sessão
        if (!empty($_SESSION['validationErrors'])) {
            $data['validationErrors'] = $_SESSION['validationErrors'];
            // Limpa os erros de validação na sessão
            unset($_SESSION['validationErrors']);
        }

        // Verifica se há mensagens de erro no servidor guardadas na sessão
        if (!empty($_SESSION['serverErrors'])) {
            $data['serverErrors'] = $_SESSION['serverErrors'];
            // Limpa os erros do servidor na sessão
            unset($_SESSION['serverErrors']);
        }

        // Prepara os dados para a view
        $data['user'] = $_SESSION['user'];

        // Renderiza a view "agents_add_new_frm"
        $this->view('layouts/html_header', $data); // Estrutura inicial do HTML
        $this->view('navbar', $data); // navbar
        $this->view('agents_add_new_frm', $data); // Formulário de cadastro de agentes
        $this->view('footer'); // footer
        $this->view('layouts/html_footer'); // Estrutura final do HTML
    }

    /**
     * Trata a submissão do formulário para adicionar um novo agente.
     */
    public function handle_new_agent()
    {
        // Verifica se existe um admin logado e se o form foi submetido corretamente
        if (!checkSession() || $_SESSION['user']->profile != 'admin' || $_SERVER['REQUEST_METHOD'] != 'POST') {
            header('Location: index.php');
            exit;
        }

        // Dados recebidos
        $email = trim($_POST['text_email'] ?? '');
        $profile = trim($_POST['select_profile'] ?? '');

        $validationErrors = [];

        // Validação do email
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $validationErrors[] = "O campo email deve ser um email válido";
        }

        if ($profile != 'admin' && $profile != 'agent') {
            $validationErrors[] = "O perfil selecionado é inválido!";
        }

        // Verifica se existem erros
        if (!empty($validationErrors)) {
            $_SESSION['validationErrors'] = $validationErrors;
            $this->show_new_agent_form();
            exit;
        }

        // Valida se já existe um  agente com o mesmo email
        $adminModel = new AdminModel();
        $agentExist = $adminModel->check_if_agent_exists($email);

        if ($agentExist['status']) {
            $_SESSION['validationErrors'][] = 'Já existe um agente cadastrado com o mesmo email';
            $this->show_new_agent_form();
            exit;
        }

        // Salva o agente na base de dados
        $resultUpdate = $adminModel->add_agent($email, $profile);

        // Verifica se o cadastro foi realizado
        if ($resultUpdate['status'] != 'success') {
            $_SESSION['serverError'][] = 'Ocorreu um erro ao salvar os dados do Agente, tente novamente mais tarde.';
            logger(get_active_username() . '- Não foi possível salvar o agente na base de dados', 'error');
            $this->show_new_agent_form();
            exit;
        }

        $emailService = new SendEmail(); // Serviço para envio de emails 

        // Monta a PURL
        $url = explode('?', $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI']);
        // Ex: $url =  ['localhost/BNG/public/', '?ct=admincontroller&mt=handle_new_agent']
        $url = $url[0] . '?ct=main&mt=show_define_password_form&purl=' . $resultUpdate['purl'];
        // Ex de url: localhost/BNG/public/?ct=main&mt=show_define_password_form&purl=hash
        $email = $resultUpdate['email'];

        // Envia o email
        $resultSendEmail = $emailService->send_agent_password_setup_email($email, $url);

        // Verifica se o cadastro foi realizado
        if ($resultSendEmail['status'] != 'success') {
            logger(get_active_username() . '- Não foi possível enviar o email de configuração de senha', 'error');
            $this->show_agent_management(); // Exibe a lista de agentes
            exit;
        }

        // Prepara os dados para a view
        $data['user'] = $_SESSION['user'];
        $data['email'] = $email;

        // Renderiza a view de sucesso
        $this->view('layouts/html_header', $data); // Estrutura inicial do HTML
        $this->view('navbar', $data); // navbar
        $this->view('agents_email_sent', $data); // View de sucesso ao enviar o email
        $this->view('footer'); // footer
        $this->view('layouts/html_footer'); // Estrutura final do HTML
    }

    /**
     * Renderiza o formulário de edição de utilizadores.
     * @param string $id Id encriptado do usuário a ser editado.
     */
    public function show_user_edit_form(string $id)
    {
        // Verifica se existe um admin logado
        if (!checkSession() || $_SESSION['user']->profile != 'admin') {
            header('Location: index.php');
            exit;
        }

        // Verifica se o ID é válido
        $id = aes_decrypt($id);

        if (empty($id)) {
            header('Location: index.php?ct=admincontroller&mt=show_agent_management');
            exit;
        }
        // Busca os dados do agente 
        $adminModel = new AdminModel();
        $agentDataSearch = $adminModel->get_agent_by_id($id);

        if (!$agentDataSearch['status'] || empty($agentDataSearch['data'])) {
            header('Location: index.php?ct=admincontroller&mt=show_agent_management');
            exit;
        }

        // Prepara os dados para a view
        $data['user'] = $_SESSION['user'];
        $data['agent'] = $agentDataSearch['data'];

        // Verifica se há mensagens de validação guardadas na sessão
        if (!empty($_SESSION['validationErrors'])) {
            $data['validationErrors'] = $_SESSION['validationErrors'];
            // Limpa os erros de validação na sessão
            unset($_SESSION['validationErrors']);
        }

        // Verifica se há mensagens de erro no servidor guardadas na sessão
        if (!empty($_SESSION['serverErrors'])) {
            $data['serverErrors'] = $_SESSION['serverErrors'];
            // Limpa os erros do servidor na sessão
            unset($_SESSION['serverErrors']);
        }

        // Verifica se há mensagens de sucesso na sessão
        if (!empty($_SESSION['successMessage'])) {
            $data['successMessage'] = $_SESSION['successMessage'];
            // Limpa os erros do servidor na sessão
            unset($_SESSION['successMessage']);
        }

        // Renderiza a view de update de agentes
        $this->view('layouts/html_header', $data); // Estrutura inicial do HTML
        $this->view('navbar', $data); // navbar
        $this->view('agents_edit_frm', $data); // Formulário de update de agentes
        $this->view('footer'); // footer
        $this->view('layouts/html_footer'); // Estrutura final do HTML
    }

    /**
     * Lida com a lógica para editar um agente.
     */
    function handle_agent_editing()
    {
        // Verifica se existe um admin logado e se o form foi submetido corretamente
        if (!checkSession() || $_SESSION['user']->profile != 'admin' || $_SERVER['REQUEST_METHOD'] != 'POST') {
            header('Location: index.php');
            exit;
        }

        // Dados recebidos
        $id = trim($_POST['id'] ?? '');
        $email = trim($_POST['text_name'] ?? '');
        $profile = trim($_POST['select_profile'] ?? '');

        $validationErrors = [];

        // Validação do email
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $validationErrors[] = "O campo email deve ser um email válido";
        }

        if ($profile != 'admin' && $profile != 'agent') {
            $validationErrors[] = "O perfil selecionado é inválido!";
        }

        // Verifica se existem erros
        if (!empty($validationErrors)) {
            $_SESSION['validationErrors'] = $validationErrors;
            $this->show_user_edit_form($id);
            exit;
        }

        // Valida se já existe um  agente com o mesmo email
        $adminModel = new AdminModel();
        $agentExist = $adminModel->verify_name_is_available($id, $email);

        if ($agentExist['status']) {
            $_SESSION['validationErrors'][] = 'Já existe um agente cadastrado com o mesmo email';
            $this->show_user_edit_form($id);
            exit;
        }

        // Atualiza o agente na base de dados
        $resultUpdate = $adminModel->update_agent($id, $email, $profile);

        // Verifica se o update foi realizado
        if (!$resultUpdate['status']) {
            $_SESSION['serverError'][] = 'Ocorreu um erro ao atualizar os dados do Agente, tente novamente mais tarde.';
            logger(get_active_username() . '- Não foi possível atualizar o agente na base de dados', 'error');
            $this->show_user_edit_form($id);
            exit;
        }

        // Logger da operação
        logger(get_active_username() . '- Dados do agente de ID: ' . aes_decrypt($id) . ' Atualizados com sucesso.');
        $_SESSION['successMessage'] = 'Dados atualizados com sucesso';

        // Chama a view para exibir a mensagem de sucesso
        $this->show_user_edit_form($id);
        exit;
    }

    /**
     * Renderiza a view de confirmação de deleção de utilizador/agente.
     * @param string $id Id encriptado do utilizador/agente a ser deletado.
     */
    public function show_user_delete_confirmation(string $id)
    {
        // Verifica se existe um admin logado
        if (!checkSession() || $_SESSION['user']->profile != 'admin') {
            header('Location: index.php');
            exit;
        }

        // Verifica se o ID é válido
        $id = aes_decrypt($id);

        if (empty($id)) {
            header('Location: index.php?ct=admincontroller&mt=show_agent_management');
            exit;
        }

        // Busca os dados do agente a ser deletado.
        $adminModel = new AdminModel();
        $agentDataSearch = $adminModel->get_agent_for_delete($id);

        if (!$agentDataSearch['status'] || empty($agentDataSearch['data'])) {
            header('Location: index.php?ct=admincontroller&mt=show_agent_management');
            // Redireciona para a lista de utilizadores. 
            exit;
        }

        // Prepara os dados para a view
        $data['user'] = $_SESSION['user'];
        $data['agent'] = $agentDataSearch['data'];

        // Renderiza a view de confirmação de delete
        $this->view('layouts/html_header', $data); // Estrutura inicial do HTML
        $this->view('navbar', $data); // navbar
        $this->view('agents_delete_confirmation', $data); // Confirmação de delete
        $this->view('footer'); // footer
        $this->view('layouts/html_footer'); // Estrutura final do HTML
    }

    /**
     * Deleta um utilizador da base de dados.
     * @param $id Id do Agente.
     */
    function handle_delete_agent(string $id)
    {
        // Verifica se existe um admin logado
        if (!checkSession() || $_SESSION['user']->profile != 'admin') {
            header('Location: index.php');
            exit;
        }

        // Verifica se o ID é válido
        $id = aes_decrypt($id);
        if (empty($id)) {
            header('Location: index.php?ct=admincontroller&mt=show_agent_management');
            exit;
        }

        // Executa o Soft Delete
        $adminModel = new AdminModel();
        $deleteAgent = $adminModel->soft_delete_agent($id);

        // Verifica se o delete foi realizado
        if (!$deleteAgent['status']) {
            logger(get_active_username() . '- Não foi possível deletar o agente na base de dados', 'error');
            $this->show_agent_management();
            exit;
        }

        // Logger da operação
        logger(get_active_username() . '- O agente de ID: ' . aes_decrypt($id) . ' Foi removido com sucesso.');

        // renderiza a lista de utilizadores novamente.
        $this->show_agent_management();
        exit;
    }

    /**
     * Renderiza a view de confirmação para recuperar um utilizador/agente.
     * @param string $id Id encriptado do utilizador/agente a ser recuperado.
     */
    public function show_user_recover_confirmation(string $id)
    {
        // Verifica se existe um admin logado
        if (!checkSession() || $_SESSION['user']->profile != 'admin') {
            header('Location: index.php');
            exit;
        }

        // Verifica se o ID é válido
        $id = aes_decrypt($id);

        if (empty($id)) {
            header('Location: index.php?ct=admincontroller&mt=show_agent_management');
            exit;
        }

        // Busca os dados do agente a ser deletado.
        $adminModel = new AdminModel();
        $agentDataSearch = $adminModel->get_agent_for_delete($id);

        if (!$agentDataSearch['status'] || empty($agentDataSearch['data'])) {
            header('Location: index.php?ct=admincontroller&mt=show_agent_management');
            // Redireciona para a lista de utilizadores. 
            exit;
        }

        // Prepara os dados para a view
        $data['user'] = $_SESSION['user'];
        $data['agent'] = $agentDataSearch['data'];

        // Renderiza a view de confirmação ao recuperar um agente
        $this->view('layouts/html_header', $data); // Estrutura inicial do HTML
        $this->view('navbar', $data); // navbar
        $this->view('agents_recover_confirmation', $data); //
        $this->view('footer'); // footer
        $this->view('layouts/html_footer'); // Estrutura final do HTML
    }
}
