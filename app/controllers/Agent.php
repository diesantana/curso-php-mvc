<?php

declare(strict_types=1);

namespace bng\Controllers;

use bng\DTO\ClientDTO;
use bng\Models\Agents;
use DateTime;
use PhpOffice\PhpSpreadsheet\Reader\Csv;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;

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
        // variável de controle flatpicker
        $data['flatpickrControl'] = true;

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

        // Renderiza as views
        $this->view('layouts/html_header', $data); // Estrutura inicial do HTML
        $this->view('navbar', $data); // navbar
        $this->view('insert_client_frm', $data); // formulário de cadastro
        $this->view('footer'); // footer
        $this->view('layouts/html_footer'); // Estrutura final do HTML
    }

    /**
     * Trata a submissão do formulário
     */
    public function new_client_submit()
    {
        // Verifica se o formulário foi submetido corretamente
        if (
            !checkSession() ||
            $_SESSION['user']->profile != 'agent' ||
            $_SERVER['REQUEST_METHOD'] != 'POST'
        ) {
            header('Location: index.php'); // redireciona para a página inicial (login)
        }

        // Armazena as mensagens de erro
        $validationErrors = [];

        // campos enviados pelo usuario
        $name = trim($_POST['text_name'] ?? '');
        $gender = trim($_POST['radio_gender'] ?? '');
        $birthdate = trim($_POST['text_birthdate'] ?? '');
        $email = trim($_POST['text_email'] ?? '');
        $phone = trim($_POST['text_phone'] ?? '');
        $interests = trim($_POST['text_interests'] ?? '');

        // Validação do campo "Nome"
        if (empty($name)) {
            $validationErrors[] = 'Nome é de preenchimento obrigatório.';
        } else {
            if (strlen($name) < 3 || strlen($name) > 50) {
                $validationErrors[] = 'O Nome deve ter entre 3 e 50 caracteres.';
            }
        }

        // Validação do campo "Genero | Sexo"
        if (empty($gender)) {
            $validationErrors[] = 'É obrigatório definir o gênero.';
        }

        // Validação do campo "Data de nascimento"
        if (empty($birthdate)) {
            $validationErrors[] = 'Data de nascimento é obrigatória.';
        } else {
            // Verifica se a data preenchida está no formato correto.
            $dateFilled = DateTime::createFromFormat('d-m-Y', $birthdate); // Cria um obj DateTime com a data preencida pelo user
            // Se a data não estiver no formato correto '$dateFilled' == false
            if (!$dateFilled) {
                $validationErrors[] = 'A data de nascimento não está no formato correto.';
            } else {
                $today = new DateTime();
                // Verifica se é uma data válida (é menor que a data atual).
                if ($birthdate >= $today) {
                    $validationErrors[] = 'A data de nascimento tem que ser anterior ao dia atual.';
                }
            }
        }

        // Validação do campo "EMAIL"
        if (empty($email)) {
            $validationErrors[] = 'Email é de preenchimento obrigatório.';
        } else {
            // Verifica se o email está no formato válido
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $validationErrors[] = 'Email não é valido.';
            }
        }

        // Validação do campo "Telefone"
        if (empty($phone)) {
            $validationErrors[] = 'É obrigatório definir o telefone.';
        } else {
            // Verifica se o telefone começa por 9 e possui exatamente 9 dígitos
            if (!preg_match("/^9{1}\d{8}$/", $phone)) {
                $validationErrors[] = 'O telefone deve começar por 9 e ter 9 algarismos no total.';
            }
        }

        // Salva os erros na sessão
        if (!empty($validationErrors)) {
            // Guarda as mensagens de erro na sessão para reapresentar no formulário
            $_SESSION['validationErrors'] = $validationErrors;
            // Recarrega o formulário de cadastro de clientes
            $this->new_client_frm();
            return;
        }

        // Instancia o model responsável pelos agentes
        $model = new Agents();

        // Verifica se já existe um cliente com o mesmo nome para este agente
        $result = $model->check_if_client_exists($name);

        // Se o cliente já existir, cria um erro de servidor e retorna ao formulário
        if ($result['status']) {
            $_SESSION['serverErrors'] = 'Já existe um cliente com esse nome.';

            // Retorna ao formulário de novo cliente
            $this->new_client_frm();
            return;
        }

        // Converte a data de nacimento para DateTime
        $birthdateObj = DateTime::createFromFormat('d-m-Y', $birthdate);

        // Instância o obj ClientDTO
        $clientDTO = new ClientDTO(
            $name,
            $gender,
            $birthdateObj,
            $email,
            $phone,
            $_SESSION['user']->id,
            $interests
        );

        // Salva o cliente na base de dados
        $model->add_new_client_to_database($clientDTO);

        // registra a criação do novo cliente no arquivo de logs
        $loggerMsg = get_active_username() . ' - adicionou novo cliente: ' . $name;
        logger($loggerMsg);

        // Redireciona para a lista de clientes
        $this->my_clients();
    }

    /**
     * Responsável por renderizar o formulário de edição de clientes.
     * @param string $id ID criptografado do cliente a ser editado.
     */
    public function edit_client(string $id)
    {
        // Verifica se existe uma sessão ativa e se a sessão pertence a um agente
        if (!checkSession() || $_SESSION['user']->profile != 'agent') {
            header('Location: index.php'); // redireciona para a página inicial
            return;
        }

        $id_client = aes_decrypt($id); // Desencripta o id do cliente

        // Validando o id após a desencriptação
        if (!$id_client) {
            // ID do cliente inválido,  redireciona para o index
            header('Location: index.php');
            return;
        }

        // Instancia o model e faz a busca na base de dados
        $model = new Agents();
        $results = $model->get_client_data((int) $id_client);

        // Verifica se a consulta foi bem sucedida  
        if ($results['status'] == 'error') {
            // Dados do cliente inválido ou não encontrados 
            header('Location: index.php');
            return;
        }

        // Prepara os dados que serão enviados para a view
        $data = [];
        $data['client'] = $results['data'];
        // Formata a data de acordo com o padrão esperado pelo flatpickr
        $data['client']->birthdate = date('d-m-Y', strtotime($data['client']->birthdate));
        $data['user'] = $_SESSION['user'];
        $data['flatpickrControl'] = true; // Variável de controle para ativar o flatpickr

        // Verifica se existem erros de validação na sessão
        if (!empty($_SESSION['validationErrors'])) {
            // Armazena os erros na var $data para serem utilizados na view
            $data['validationErrors'] = $_SESSION['validationErrors'];
            // Remove os erros da sessão para não serem utilizado em outras submissões
            unset($_SESSION['validationErrors']);
        }

        // Verifica se existem erros do servidor na sessão
        if (!empty($_SESSION['serverErrors'])) {
            // Armazena os erros na var $data para serem utilizados na view
            $data['serverErrors'] = $_SESSION['serverErrors'];
            // Remove os erros da sessão para não serem utilizado em outras submissões
            unset($_SESSION['serverErrors']);
        }

        // Renderiza as views
        $this->view('layouts/html_header', $data);
        $this->view('navbar', $data);
        $this->view('edit_client_frm', $data);
        $this->view('footer');
        $this->view('layouts/html_footer');
    }

    /**
     * Trata a submissão do formulário de edição de clientes.
     * Este método é responsável por validar os dados da submissão do formulário,
     * e atualizar os dados do cliente.
     */
    public function edit_client_submit()
    {
        // Verifica se o formulário foi submetido corretamente
        if (!checkSession() || $_SESSION['user']->profile != 'agent' || $_SERVER['REQUEST_METHOD'] != 'POST') {
            header('Location: index.php'); // Redireciona para a página inicial
            return;
        }

        // Array que vai armazenar as mensagens de erro
        $validationErrors = [];

        // campos enviados pelo usuario
        $name = trim($_POST['text_name'] ?? '');
        $gender = trim($_POST['radio_gender'] ?? '');
        $birthdate = trim($_POST['text_birthdate'] ?? '');
        $email = trim($_POST['text_email'] ?? '');
        $phone = trim($_POST['text_phone'] ?? '');
        $interests = trim($_POST['text_interests'] ?? '');

        // Validação do campo "Nome"
        if (empty($name)) {
            $validationErrors[] = 'Nome é de preenchimento obrigatório.';
        } else {
            if (strlen($name) < 3 || strlen($name) > 50) {
                $validationErrors[] = 'O Nome deve ter entre 3 e 50 caracteres.';
            }
        }

        // Validação do campo "Genero | Sexo"
        if (empty($gender)) {
            $validationErrors[] = 'É obrigatório definir o gênero.';
        }

        // Validação do campo "Data de nascimento"
        if (empty($birthdate)) {
            $validationErrors[] = 'Data de nascimento é obrigatória.';
        } else {
            // Verifica se a data preenchida está no formato correto.
            $dateFilled = DateTime::createFromFormat('d-m-Y', $birthdate); // Cria um obj DateTime com a data preencida pelo user
            // Se a data não estiver no formato correto '$dateFilled' == false
            if (!$dateFilled) {
                $validationErrors[] = 'A data de nascimento não está no formato correto.';
            } else {
                $today = new DateTime();
                // Verifica se é uma data válida (é menor que a data atual).
                if ($birthdate >= $today) {
                    $validationErrors[] = 'A data de nascimento tem que ser anterior ao dia atual.';
                }
            }
        }

        // Validação do campo "EMAIL"
        if (empty($email)) {
            $validationErrors[] = 'Email é de preenchimento obrigatório.';
        } else {
            // Verifica se o email está no formato válido
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $validationErrors[] = 'Email não é valido.';
            }
        }

        // Validação do campo "Telefone"
        if (empty($phone)) {
            $validationErrors[] = 'É obrigatório definir o telefone.';
        } else {
            // Verifica se o telefone começa por 9 e possui exatamente 9 dígitos
            if (!preg_match("/^9{1}\d{8}$/", $phone)) {
                $validationErrors[] = 'O telefone deve começar por 9 e ter 9 algarismos no total.';
            }
        }

        // Verifica se o ID foi enviado via método POST
        if (empty($_POST['id_client'])) {
            header('Location: index.php');
            return;
        }

        // Desencripta o Id
        $id_client = aes_decrypt($_POST['id_client']);

        // Verifica se o ID é válido (Foi desencriptado corretamente)
        if (!$id_client) {
            header('Location: index.php');
            return;
        }

        // Salva os erros na sessão
        if (!empty($validationErrors)) {
            // Guarda as mensagens de erro na sessão para reapresentar no formulário
            $_SESSION['validationErrors'] = $validationErrors;
            // Recarrega o formulário de edição de clientes
            $this->edit_client(aes_encrypt($id_client));
            // o nome é criptografado pois poderá ser lido no dev tools
            return;
        }

        // Verifica se existe outro cliente do mesmo agente com o mesmo nome
        $modelAgent = new Agents();
        $results = $modelAgent->check_if_name_exists((int) $id_client, $name);

        // Se $modelAgent->check_if_name_exists retornar true, já existe um cliente com o mesmo nome.
        if ($results['status']) {
            $_SESSION['serverErrors'] = 'Já existe outro cliente com o mesmo nome';
            $this->edit_client(aes_encrypt($id_client)); // Volta para o formulário exibindo o erro.
            return;
        }

        // Converte a data de nascimento para DateTime
        $birthdateObj = DateTime::createFromFormat('d-m-Y', $birthdate);

        // Instância o obj ClientDTO
        $clientDTO = new ClientDTO(
            $name,
            $gender,
            $birthdateObj,
            $email,
            $phone,
            $_SESSION['user']->id,
            $interests
        );

        // atualiza os dados
        $modelAgent->update_client_data((int) $id_client, $clientDTO);

        // Registra log da operação
        $loggerMsg = get_active_username() . ' - Atualizou dados do cliente: ' . $name;
        logger($loggerMsg);

        // Volta para a listagem de clientes
        $this->my_clients();
    }

    /**
     * Responsável por tratar a solicitação de deleção de um cliente, carregando um formulário de confirmação.
     */
    public function delete_client_submit(string $id)
    {

        // Verifica se existe uma sessão ativa e se a sessão pertence a um agente
        if (!checkSession() || $_SESSION['user']->profile != 'agent') {
            header('Location: index.php'); // redireciona para a página inicial
            return;
        }

        $id_client = aes_decrypt($id); // Desencripta o id do cliente

        // Validando o id após a desencriptação
        if (!$id_client) {
            // ID do cliente inválido,  redireciona para o index
            header('Location: index.php');
            return;
        }

        // Instancia o model e faz a busca na base de dados
        $model = new Agents();
        $results = $model->get_client_data((int) $id_client);

        // Verifica se a consulta foi bem sucedida  
        if ($results['status'] == 'error') {
            // Dados do cliente inválido ou não encontrados 
            header('Location: index.php');
            return;
        }

        // Prepara os dados que serão enviados para a view (nome, email e telefone)
        $data = [];
        $data['client'] = $results['data'];
        $data['user'] = $_SESSION['user'];

        // Renderiza as views
        $this->view('layouts/html_header', $data);
        $this->view('navbar', $data);
        $this->view('delete_client_confirmation', $data);
        $this->view('footer');
        $this->view('layouts/html_footer');
    }

    /**
     * Responsável por deletar um cliente da base de dados.
     * @param string $id ID encriptado do cliente a ser deletado.
     */
    public function delete_client(string $id)
    {
        // Verifica se existe uma sessão ativa e se a sessão pertence a um agente
        if (!checkSession() || $_SESSION['user']->profile != 'agent') {
            header('Location: index.php'); // redireciona para a página inicial
            return;
        }

        $id_client = aes_decrypt($id); // Desencripta o id do cliente

        // Validando o id após a desencriptação
        if (!$id_client) {
            // ID do cliente inválido,  redireciona para o index
            header('Location: index.php');
            return;
        }

        // Chama o método no model que vai realizar a deleção
        $agentModel = new Agents();
        $agentModel->delete_client((int) $id_client);

        // Registra log da operação
        $loggerMsg = get_active_username() . ' - Deletou o cliente de ID: ' . aes_decrypt($id);
        logger($loggerMsg);

        // Volta para a listagem de clientes
        $this->my_clients();
    }

    /**
     * Responsável por renderizar a view do formulário de upload
     * de arquivos.
     */
    public function show_upload_form()
    {
        // Verificação de segurança, garantido que apenas agentes autenticados acessem o método
        if (!checkSession() || $_SESSION['user']->profile != 'agent') {
            header('Location: index.php');
            // redirecionada para o index.php que consequentemente vai chamar o método index() do controlador main
        }

        // Carrega os dados do usuário logado
        $data['user'] = $_SESSION['user'];

        // Verifica se existem erros de validação 
        if (!empty($_SESSION['serverError'])) {
            $data['serverError'] = $_SESSION['serverError']; // Armazena os erros para serem exibidos na view
            unset($_SESSION['serverError']); // Exclui os erros após serem tratados.
        }

        // Renderiza as views
        $this->view('layouts/html_header'); // Estrutura inicial do HTML
        $this->view('navbar', $data); // navbar
        $this->view('upload_file_with_clients_frm', $data); // formulário de uplaod
        $this->view('footer'); // footer
        $this->view('layouts/html_footer'); // Estrutura final do HTML
    }

    /**
     * Trata a submissão do formulário de upload de arquivos. 
     */
    public function handle_upload()
    {
        // Verifica se o formulário foi submetido corretamente
        if (!checkSession() || $_SESSION['user']->profile != 'agent' || $_SERVER['REQUEST_METHOD'] != 'POST') {
            header('Location: index.php'); // redireciona para a página inicial (login)
        }

        // Verifica se o formulário não foi submetido sem nenhum arquivo
        if (empty($_FILES) || empty($_FILES['clients_file']['name'])) {
            $_SESSION['serverError'] = 'Faça o carregamento de um arquivo XSLX ou CSV';
            $this->show_upload_form(); // Exibe o formulário novamente, com o erro. 
            return;
        }

        // Verifica se a extenção é XLSX ou CSV
        $validExtensions = ['csv', 'xlsx'];
        $temp = explode('.', $_FILES['clients_file']['name']); // Divide o nome do arquivo em um array, separando pelo '.'
        $extension = end($temp); // Pega apenas a extensão do arquivo
        // Verifica se é uma extensão válida
        if (!in_array($extension, $validExtensions)) {
            $_SESSION['serverError'] = 'O arquivo deve ser do tipo XLSX ou CSV';
            $this->show_upload_form(); // Exibe o formulário novamente, com o erro. 
            return;
        }

        // Verifica se o arquivo tem no máximo 2MB
        if ($_FILES['clients_file']['size'] > 2000000) {
            $_SESSION['serverError'] = 'O arquivo deve ser menor que 2MB';
            $this->show_upload_form(); // Exibe o formulário novamente, com o erro. 
            return;
        }

        // Monta no nome do arquivo e o seu diretório de destino. 
        // O nome do arquivo vai ser "dados_" concatenado com o timestamp atual, contatenado com a extensão. 
        $filePath = __DIR__ . '/../../uploads/dados_' . time() . '.' . $extension;

        // Move o arquivo para o diretório de destino (uplaods)
        if (move_uploaded_file($_FILES['clients_file']['tmp_name'], $filePath)) {
            // verifica se o arquivo está no formato correto
            $isValid = $this->is_valid_header($filePath);
            if (!$isValid) {
                $_SESSION['serverError'] = 'O formato do arquivo não é válido, por favor baixe a versão correta no link acima';
                $this->show_upload_form(); // Exibe o formulário novamente, com o erro. 
                return;
            }

            // Aqui o arquivo é válido
            die("Arquivo carregado com sucesso");
        } else {
            $_SESSION['serverError'] = 'Aconteceu um erro inesperado ao carregar o arquivo.';
            $this->show_upload_form(); // Exibe o formulário novamente, com o erro. 
            return;
        }
    }

    /**
     * Valida se o cabeçalho do arquivo é valido. 
     * @param string $filePath Caminho completo contendo o nome do arquivo no servidor.
     * @return Bool true se o cabeçalho for válido e false caso seja um cabeçalho inválido. 
     */
    private function is_valid_header(string $filePath): bool
    {
        $data = []; // Dados do header
        $fileInfo = pathinfo($filePath);

        // Busca o header do arquivo
        if ($fileInfo['extension'] == 'csv') {
            // Abre o arquivo CSV para leitura.
            $reader = new Csv();
            $reader->setInputEncoding('UTF-8');
            $reader->setDelimiter(';');
            $reader->setEnclosure('');
            $sheet = $reader->load($filePath);
            // Retorna apenas o header do arquivo
            $data[] = $sheet->getActiveSheet()->toArray()[0];
        } else if ($fileInfo['extension'] == 'xlsx') {
            // Abre o arquivo XSLX para leitura
            $reader = new Xlsx();
            $reader->setReadDataOnly(true);
            $spreadsheet = $reader->load($filePath);
            // Retorna apenas o header do arquivo
            $data[] = $spreadsheet->getActiveSheet()->toArray()[0];
        }

        // Valida o header e retorna o resultado
        $validHeader = 'name,gender,birthdate,email,phone,interests';
        return implode(',', $data) == $validHeader;
    }
}
