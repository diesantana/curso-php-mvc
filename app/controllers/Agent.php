<?php

declare(strict_types=1);

namespace bng\Controllers;

use bng\DTO\ClientDTO;
use bng\Models\Agents;
use DateTime;

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
        // Verifica se existe uma sessão ativa e se essa sessão pertence a um agente
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

        // Verifica se a consulta foi bem sucessida 
        if ($results['status'] == 'error') {
            // Dados do cliente inválido ou não encontrados 
            header('Location: index.php');
            return;
        }

        // Prepara os dados que serão enviados para a view
        $data = [];
        $data['client'] = $results['data'];
        $data['user'] = $_SESSION['user'];
        $data['flatpickrControl'] = true;

        // Verifica se existem erros na sessão
        if(!empty($_SESSION['validationErrors'])) {
            $data['validationErrors'] = $_SESSION['validationErrors']; 
            unset($_SESSION['validationErrors']); 
            // Armazena os erros na var $data para serem utilizados na view
            // Remove os erros da sessão para não serem utilizado em outras submissões
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
            return;
        }

        // Neste ponto, todas as validações passaram
        // (a lógica de atualização no banco será implementada aqui)
        die('ok');
    }

    public function delete_client(string $id)
    {
        // echo 'Deletando o cliente ID: ' . $id;
        echo 'Deletando o cliente ID: ' . aes_decrypt($id);
    }
}
