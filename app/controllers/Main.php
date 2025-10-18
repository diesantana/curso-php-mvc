<?php
namespace bng\Controllers;

use bng\Controllers\BaseController;
use bng\Models\Agents;

class Main extends BaseController
{
    /**
     * Ponto de entrada do main controller.
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

        // Se existir usuário logado
        $this->view('layouts/html_header'); // Estrutura inicial do HTML
        echo '<h3 class="text-white text-center">Olá mundo</h3>'; // Exibe um 'Hello world' (código temporário)
        $this->view('layouts/html_footer'); // Estrutura inicial do HTML
    }

    /**
     * Lógica para apresentar o formulário de login.
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

        // Se não existir usuário logado, verifica se existem erros.
        $data = []; // Armazena possíveis mensagens erro

        // Se existir erros, armazena em $data[] e apaga os erros da sessão
        // Os erros serão excluídos da sessão pois eles já estão sendo tratados.
        if (!empty($_SESSION['validation_errors'])) {
            $data['validation_errors'] = $_SESSION['validation_errors']; // Atribui os erros ao array $data
            unset($_SESSION['validation_errors']); // Remove a variável da sessão
        }

        // echo 'Chegou aqui';

        // Exibe o formulário com os póssíveis erros
        $this->view('layouts/html_header'); // Estrutura inicial do HTML
        $this->view('login_frm', $data); // View responsável pelo formulário de login
        $this->view('layouts/html_footer'); // Estrutura inicial do HTML

    }

    /**
     * Método responsável por tratar a submuissão do forumlário de login
     */
    public function login_submit() {
        // echo 'Chegou aqui';
        // Verifica se existe um usuário logado
        if(checkSession()) {
            // Se existir um usuário logado, chama o método index
            // O método index() vai exibir a view de acordo com o perfil deste usuário logado.
            $this->index();
            return;
        }

        // echo 'Chegou aqui';
        // Nenhum usuário está logado! 
        // Verifica se o formulário foi submetido corretamente.
        if($_SERVER['REQUEST_METHOD'] != 'POST') {
            // Se o formulário não foi submetido corretamnte, chama o método index()
            $this->index();
            return;
        }
        // echo 'Chegou aqui';
        
        // Validação do formulário
        $validation_errors = [];
        if(empty($_POST['text_username']) || empty($_POST['text_password'])) {
            $validation_errors[] = 'Username e password são obrigatórios!';
        }


        // Se existir erros, vamos salvar na session
        if(!empty($validation_errors)) {
            $_SESSION['validation_errors'] = $validation_errors; // Salva os erros na session
            $this->login_frm(); // Chama o método responsável por exibir o formulário de login
            return;
            // Quando o login_frm() verificar que não tem nenhum usuário logado, o formulário 
            // vai ser exibido novamente, com os erros. 
        }

        // Aqui não existe nenhum erro
        // Captura os valores enviados pelo usuário
        $username = $_POST['text_username'];
        $password = $_POST['text_password'];

        // Apenas exibe os dados, ainda não estamos fazendo o login (Código temporário)
        echo $username . '<br>' . $password;

    }
}
