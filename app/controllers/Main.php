<?php

namespace bng\Controllers;

use bng\Controllers\BaseController;
use bng\Models\AdminModel;
use bng\Models\Agents;

class Main extends BaseController
{
    /**
     * Verifica se existe um usuário logado, se sim, carrega a view.
     * Se não existir usuário logado, redireciona para o formulário de login (Método login_frm())
     */
    public function index()
    {
        // Verifica se existe um usuário logado. 
        if (!checkSession()) {
            // Se não existir um usuário logado eu chamo a função login_frm()
            // login_frm é responsável por exibir o formulário de login
            $this->login_frm();
            return;
        }

        // Recupera os dados do usuário para serem passados para a view
        $data['user'] = $_SESSION['user'];

        // Se existir usuário logado, carrega a view
        $this->view('layouts/html_header'); // Estrutura inicial do HTML
        $this->view('navbar', $data); // View navbar
        $this->view('homepage', $data); //view homepage
        $this->view('footer'); // view footer
        $this->view('layouts/html_footer'); // Estrutura inicial do HTML
    }

    /**
     * Método responsável por apresentar o formulário de login com as possíveis mensagens de erro.
     */
    public function login_frm()
    {
        // Verificando se existe um usuário logado
        if (checkSession()) {
            // Se existir usuário logado, chama o método index. 
            // O index, vai conter a lógica para apresentar a view de acordo com o perfil do user.
            $this->index();
            return;
        }

        // Se não existir usuário logado, verifica se existem erros salvos na session.
        $data = []; // Armazena possíveis mensagens erro

        // Se existir erros de validação na session, armazena em $data[] e apaga os erros da sessão
        // Os erros serão excluídos da sessão pois eles já estão sendo tratados e exibidos aqui.
        if (!empty($_SESSION['validation_errors'])) {
            $data['validation_errors'] = $_SESSION['validation_errors']; // Atribui os erros ao array $data
            unset($_SESSION['validation_errors']); // Remove a variável da sessão
        }

        // Se existir erros do servidor na session, armazena em $data[] e apaga os erros da sessão
        // Os erros serão excluídos da sessão pois eles já estão sendo tratados e exibidos aqui.
        if (!empty($_SESSION['server_error'])) {
            $data['server_error'] = $_SESSION['server_error']; // Atribui os erros ao array $data
            unset($_SESSION['server_error']); // Remove a variável da sessão
        }

        // Exibe o formulário com os póssíveis erros
        $this->view('layouts/html_header'); // Estrutura inicial do HTML
        $this->view('login_frm', $data); // View responsável pelo formulário de login
        $this->view('layouts/html_footer'); // Estrutura inicial do HTML

    }

    /**
     * Método responsável por tratar a submissão do forumlário de login
     */
    public function login_submit()
    {

        // Verifica se existe um usuário logado
        if (checkSession()) {
            // Se existir um usuário logado, chama o método index
            // O método index() vai exibir a view de acordo com o perfil deste usuário logado.
            $this->index();
            return;
        }

        // Aqui nenhum usuário está logado! 
        // Verifica se o formulário foi submetido corretamente.
        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            // Se o formulário não foi submetido corretamnte, chama o método index()
            $this->index();
            return;
        }

        // Validação do formulário
        $validation_errors = []; // Array que vai armazenar os possíveis erros

        if (empty($_POST['text_username']) || empty($_POST['text_password'])) {
            $validation_errors[] = 'Username e password são obrigatórios!';
        }

        // Aqui, já sabemos que os valores (username e password) não estão vazios, vamos tratar eles.
        // Captura os valores enviados pelo usuário
        $username = $_POST['text_username'];
        $password = $_POST['text_password'];

        // verifica se text_username é um email válido
        if (!filter_var($username, FILTER_VALIDATE_EMAIL)) {
            // se filter_var retornar false, logo o email não é válido, temos um erro!
            // Este erro deve ser adicionado ao array de erros ($validation_errors)
            $validation_errors[] = 'O username deve ser um email válido.';
        }

        // Verifica se text_username está entre 5 e 50 caracteres
        if (strlen($username) < 5 || strlen($username) > 50) {
            $validation_errors[] = 'O username deve ter entre 5 e 50 caracteres.';
        }

        // Verifica se a senha está entre 6 e 12 caracteres
        if (strlen($password) < 6 || strlen($password) > 12) {
            $validation_errors[] = 'A password deve ter entre 6 e 12 caracteres.';
        }

        // Se existir erros, vamos salvar na session
        if (!empty($validation_errors)) {
            $_SESSION['validation_errors'] = $validation_errors; // Salva os erros na session
            $this->login_frm(); // Chama o método responsável por exibir o formulário de login
            return;
            // Quando o login_frm() verificar que não tem nenhum usuário logado, o formulário 
            // vai ser exibido novamente, com os erros. 
        }

        // Aqui não tem nenhum erro de validação, os campos foram preenchidos corretamente.
        // Valida as credencias de login
        $modelAgents = new Agents();
        $validatesLogin = $modelAgents->check_login($username, $password); // Verifica se o login é válido

        // Validação das credenciais de login
        $server_errors = []; // Array que vai armazenar os possíveis erros no servidor

        // Em caso de Login inválido, uma mensagem de erro é adicionada ao array $server_errors
        if (!$validatesLogin['status']) {
            $server_errors[] = 'Login inválido!';
            // registro de log
            logger("$username - Lógin inválido", 'error');
        }

        // Se existir erros nas credenciais de login, vamos salvar na session
        if (!empty($server_errors)) {
            $_SESSION['server_error'] = $server_errors; // Salva o erro na sessão
            $this->login_frm(); // Chama o método responsável por exibir o formulário de login
            return;
        }

        // Se login for válido, vamos buscar os dados do usuário e armazená-los na sessão 
        $loggedUserData = $modelAgents->get_data_user($username);
        // registro de log
        logger("$username - Login com sucesso");

        /* Aqui deve existir um tratamento para verificar se existe algum usuário em "$loggedUserData" 
        antes de salvar o valor na session, porém não foi feito na aula. Refatorar depois ⚠️*/
        $_SESSION['user'] = $loggedUserData['data'];

        // Atualizando a base de dados
        $modelAgents->set_user_last_login($_SESSION['user']->id);

        // Redireciona a lógica para o método index()
        $this->index();
        /*
        Em $this->index() o sistema vai verificar que tem um usuário logado (salvo na session), e carregar
        a view de acordo com o perfil deste usuário.
        */
    }

    /**
     * Remove o usuário da sessão fazendo o Logout.
     * Após a remoção do usuário da sessão este método chama o método index() que vai carregar o formulário de login.
     */
    public function logout()
    {
        // Bloqueia acesso ao logout sem sessão válida
        if (!checkSession()) {
            $this->index();
        }

        if (!empty($_SESSION['user'])) {
            // registro de log do usuário que saiu
            logger($_SESSION['user']->name . ' - Fez logout');

            // Remove o usuário da sessão
            unset($_SESSION['user']);
        }

        // Volta para o formulário de login
        $this->index();
    }

    /**
     * Renderizar a view de atualização de senha.
     */
    public function show_change_password_form()
    {
        // Verificação de segurança, garantido que apenas agentes autenticados acessem o método
        if (!checkSession()) {
            header('Location: index.php');
            // redirecionada para o index.php que consequentemente vai chamar o método index() do controlador main
        }

        // Carrega os dados do usuário logado
        $data['user'] = $_SESSION['user'];

        // Verifica se existem erros de validação 
        if (!empty($_SESSION['validationErrors'])) {
            $data['validationErrors'] = $_SESSION['validationErrors']; // Armazena os erros para serem exibidos na view
            unset($_SESSION['validationErrors']); // Exclui os erros após serem tratados.
        }

        // Verifica se existem erros do servidor 
        if (!empty($_SESSION['serverErrors'])) {
            $data['serverErrors'] = $_SESSION['serverErrors']; // Armazena os erros para serem exibidos na view
            unset($_SESSION['serverErrors']); // Exclui os erros após serem tratados.
        }

        // Verifica se existem mensagens de sucesso no servidor 
        if (!empty($_SESSION['successMsg'])) {
            $data['successMsg'] = $_SESSION['successMsg']; // Armazena a msg para ser exibida na view
            unset($_SESSION['successMsg']); // Exclui a msg da sessão
        }

        // Renderiza as views
        $this->view('layouts/html_header'); // Estrutura inicial do HTML
        $this->view('navbar', $data); // navbar
        $this->view('profile_change_password_frm', $data); // formulário de UPDATE
        $this->view('footer'); // footer
        $this->view('layouts/html_footer'); // Estrutura final do HTML
    }

    /**
     * Trata a submissão do formulário de update de senhas.
     */
    public function handle_change_password()
    {
        // Verifica se a requisição é válida
        if (!checkSession() || $_SERVER['REQUEST_METHOD'] != 'POST') {
            header('Location: index.php');
            // redirecionada para o index.php que consequentemente vai chamar o método index() do controlador main
        }

        $validationErrors = []; // Armazena os erros de validação

        // Dados recebidos
        $currentPassword = $_POST['text_current_password'] ?? '';
        $newPassword = $_POST['text_new_password'] ?? '';
        $repeatNewPassword = $_POST['text_repeat_new_password'] ?? '';

        // Valida o campo "Password atual"
        if (empty($currentPassword)) {
            $validationErrors[] = 'O campo "Password atual" é de preenchimento obrigatório';
        }

        // Valida o campo "Nova password"
        if (empty($newPassword)) {
            $validationErrors[] = 'O campo "Nova password" é de preenchimento obrigatório';
        } else if (strlen($newPassword) < 6 || strlen($newPassword) > 12) {
            $validationErrors[] = 'A nova senha deve ter entre 6 e 12 caracteres';
        } else if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/', $newPassword)) {
            $validationErrors[] = 'A password deve conter pelo menos uma letra maiúscula, uma minúscula e um dígito.';
        }

        // Valida o campo Repetir nova password
        if (empty($repeatNewPassword)) {
            $validationErrors[] = 'O campo "Repetir a nova password" é de preenchimento obrigatório';
        } else if ($newPassword != $repeatNewPassword) {
            $validationErrors[] = 'O campo "Nova password" e "Repetir a nova password" devem ser iguais';
        }

        // Tratando os erros de validação
        if (!empty($validationErrors)) {
            $_SESSION['validationErrors'] = $validationErrors;
            $this->show_change_password_form();
            return;
        }

        // Válida se a senha atual está correta
        $modelAgents = new Agents();
        $isPasswordCorrect = $modelAgents->verify_password($_SESSION['user']->id, $currentPassword);

        if (!$isPasswordCorrect['status']) {
            $serverErrors[] = 'A senha atual é inválida';
            $_SESSION['serverErrors'] = $serverErrors;
            $this->show_change_password_form();
            return;
        }

        // Faz o update
        $resultUpdate = $modelAgents->update_password($_SESSION['user']->id, $newPassword);

        if (!$resultUpdate['status']) {
            $serverErrors[] = 'Ocorreu um erro ao atualizar a senha.';
            $_SESSION['serverErrors'] = $serverErrors;
            $this->show_change_password_form();
            return;
        }

        // Registro de log da operação
        $username = $_SESSION['user']->name;
        logger($username . '- Alterou a password');

        // Mensagem de sucesso para exibir na view
        $_SESSION['successMsg'] = 'Password Atualizada com sucesso';
        $this->show_change_password_form();
        return;
    }

    /**
     * Renderizar a view de cadastro de senha.
     */
    public function show_define_password_form(string $purl = '')
    {
        // Verifica se não existe nenhum agente autenticado
        if (checkSession()) {
            header('Location: index.php');
            exit;
            // redirecionada para o index.php que consequentemente vai chamar o método index() do controlador main
        }

        // Valida se a PURL é válida
        $adminModel = new AdminModel();
        $resultValidatingPurl = $adminModel->check_the_purl($purl);

        if (empty($purl) || strlen($purl) != 20 || !$resultValidatingPurl['status']) {
            die("Erro nas credencias de acesso. Entre em contato com o suporte para mais detalhes.");
        }

        // Prepara os dados da PURL e id para renderizar a view
        $data['purl'] = $purl;
        $data['id'] = $resultValidatingPurl['id'];

        // Verifica se existem erros de validação 
        if (!empty($_SESSION['validationErrors'])) {
            $data['validationErrors'] = $_SESSION['validationErrors']; // Armazena os erros para serem exibidos na view
            unset($_SESSION['validationErrors']); // Exclui os erros após serem tratados.
        }

        // Verifica se existem erros do servidor 
        if (!empty($_SESSION['serverErrors'])) {
            $data['serverErrors'] = $_SESSION['serverErrors']; // Armazena os erros para serem exibidos na view
            unset($_SESSION['serverErrors']); // Exclui os erros após serem tratados.
        }

        // Renderiza as views
        $this->view('layouts/html_header'); // Estrutura inicial do HTML
        $this->view('new_agent_define_password', $data); // formulário de cadastro de senha
        $this->view('layouts/html_footer'); // Estrutura final do HTML

    }

    /**
     * Trata a submissão do cadastro de senhas.
     */
    public function handle_define_password()
    {
        // Verifica se a requisição é válida
        if (checkSession() || $_SERVER['REQUEST_METHOD'] != 'POST') {
            header('Location: index.php');
            exit;
        }

        $validationErrors = []; // Armazena os erros de validação
        $serverErrors = []; // Armazena os erros de servidor

        // Dados recebidos
        $id = aes_decrypt($_POST['id'] ?? 0);
        $purl = $_POST['purl'] ?? '';
        $password = $_POST['text_password'] ?? '';
        $repeatPassword = $_POST['text_repeat_password'] ?? '';

        // Valida o ID  e PURL
        if (empty($purl) || strlen($purl) != 20 || empty($id)) {
            $serverErrors[] = 'Ocorreu um erro ao cadastrar a senha, entre em contato com o suporte ou tente novamente mais tarde';
        }

        // Valida o campo "password"
        if (empty($password)) {
            $validationErrors[] = 'O campo "Password" é de preenchimento obrigatório';
        } else if (strlen($password) < 6 || strlen($password) > 12) {
            $validationErrors[] = 'A Senha deve ter entre 6 e 12 caracteres';
        } else if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/', $password)) {
            $validationErrors[] = 'A password deve conter pelo menos uma letra maiúscula, uma minúscula e um dígito.';
        }

        // Valida o campo Repetir password
        if (empty($repeatPassword)) {
            $validationErrors[] = 'O campo "Repetir password" é de preenchimento obrigatório';
        } else if ($password != $repeatPassword) {
            $validationErrors[] = 'Os campos "Password" e "Repetir password" devem ser iguais';
        }

        // Tratando os erros de validação
        if (!empty($validationErrors)) {
            $_SESSION['validationErrors'] = $validationErrors;
            $this->show_define_password_form($purl);
            exit;
        }

        // Tratando os erros de servidor
        if (!empty($serverErrors)) {
            $_SESSION['serverErrors'] = $serverErrors;
            $this->show_define_password_form($purl);
            exit;
        }

        // Cadastra a senha
        $adminModel = new AdminModel();
        $resultsAddPassword = $adminModel->created_password($id, $password);

        if (!$resultsAddPassword['status']) {
            $_SESSION['serverErrors'][] = 'Ocorreu um erro ao salvar a senha. Entre em contato com o suporte ou tente novamente mais tarde';
            $this->show_define_password_form($purl);
            return;
        }

        // Log da operação
        logger(' O agente de id:' . $id . ' Definiu a password!');

        // Renderiza a view de sucesso
        $this->view('layouts/html_header'); // Estrutura inicial do HTML
        $this->view('reset_password_define_password_success');
        $this->view('layouts/html_footer'); // Estrutura final do HTML
    }
}
