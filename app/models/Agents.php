<?php

namespace bng\Models;

use bng\DTO\ClientDTO;
use bng\System\Database;
use DateTime;

/**
 * Model Responsável por acessar e manipular a base de dados, no que diz
 * respeito a tabela Agents. 
 */
class Agents extends BaseModel
{
    /**
     * Verifica se as credenciais de login são válidas.
     * Este método acessa á base de dados e verifica se as credenciais de login existem.
     * @param string $username username ou email para acesso á aplicação
     * @param string $password Senha de acesso do username
     * @return array Retorna um array com o status da consulta, sendo true se as credenciais forem válidas 
     * e false caso contrário.
     */
    public function check_login(string $username, string $password): array
    {
        // Cria a conexão com a base de dados. 
        $this->db_connect();
        /* O método db_connect é herdado de BaseModel, 
        sendo responsável por iniciar a conexão com a base de dados*/

        // parâmetros para realizar a query (segurança / PDO)
        $params = [
            ':username' => $username
        ];

        // query SQL que verifica se username existe na base de dados
        $sql = "SELECT id, passwrd FROM agents WHERE name = AES_ENCRYPT(:username, '" . MYSQL_AES_KEY . "')";

        // realiza a query. O retorno é um objeto com dados da consulta (Classe Database)
        $resultQuery = $this->query($sql, $params);

        // Verifica se existe um usuário com aquele username
        // Caso não exista retorna false e encerra a lógica deste método
        if ($resultQuery->affected_rows == 0) {
            return ['status' => false];
        }

        // Aqui já foi confirmado que o username existe, vamos verificar o password
        if (!password_verify($password, $resultQuery->results[0]->passwrd)) {
            return ['status' => false];
            // password_verify verifica se uma senha corresponde a um hash
            // Se a NÃO senha estiver correta, returna false e encerra a lógica deste método
        }

        // Ao confirmar que o username e senha existem retorna status=true
        return ['status' => true];
    }

    /**
     * Busca os dados do usuário com base em um username.
     * @param string $username username do usuário a ser buscado.
     * @return array array associativo contendo status e dados do usuário
     */
    public function get_data_user(string $username): array
    {

        // Prepara a query SQL 
        $params = [
            ':username' => $username
        ];

        /* 
            SELECT 
                id, 
                AES_DECRYPT(name, MYSQL_AES_KEY) as name,
                profile
                FROM agents
                WHERE AES_ENCRYPT(username, MYSQL_AES_KEY) = name 
        */
        $sql = "SELECT id, AES_DECRYPT(name,'" . MYSQL_AES_KEY . "') as name, profile FROM agents WHERE AES_ENCRYPT(:username, '" . MYSQL_AES_KEY . "') = name";

        // Cria uma conexão com a base de dados
        $this->db_connect();

        // Executa a query SQL 
        $resultQuery = $this->query($sql, $params);

        // Retorna o resultado da query com um status de sucesso
        return [
            'status' => 'success',
            'data' => $resultQuery->results[0]
        ];
    }

    /**
     * Responsável por atualizar a data e hora do último acesso do usuário na base de dados. 
     * Este método vai atualiza a coluna last_login na base de dados.
     * @param string $id id do usuário a ser atualizado.
     * @return midex Um Objeto do tipo stdClass contendo status e dados do usuário.
     */
    public function set_user_last_login(string $id): mixed
    {

        $this->db_connect(); // Conexão com a base de dados
        $params = [':id' => $id]; // Parâmetros usados na consulta
        $sql = 'UPDATE agents SET last_login = NOW() WHERE id = :id'; // Comando SQL com os parâmetros

        return $this->non_query($sql, $params); // Executa a query e retorna o resultado.
    }

    /**
     * Busca todos os clientes de um agente específico. 
     * Esse método é responsável por acessar a base de dados e trazer os clientes relacionados ao agente
     * de acordo com o seu id.
     * @param string $id ID do agente
     * @return array Array associativo contendo dois valores:
     * - 'status' da operação
     * - 'data' contendo os dados da consulta. 
     */
    public function get_agent_clients(int $id): array
    {
        // Parâmetros da query (Segurança / PDO)
        $params = ['id_agent' => $id];

        // SQL 
        $sql = "SELECT id, AES_DECRYPT(name,'" . MYSQL_AES_KEY . "') name, gender, birthdate, AES_DECRYPT(email,'" . MYSQL_AES_KEY . "') email, AES_DECRYPT(phone,'" . MYSQL_AES_KEY . "') phone, interests, created_at, updated_at FROM persons WHERE id_agent = :id_agent AND deleted_at IS NULL";

        // Conexão com a base de dados
        $this->db_connect();

        // Executa a query
        $resultsQuery = $this->query($sql, $params);

        // Atualmente o retorno sempre tem o status success, independe da query encontrar registros ou não.	
        return [
            'status' => 'success',
            'data' => $resultsQuery->results
            // result vem da classe DataBase, é uma propriedade que contém o resultado da query
        ];
    }

    /**
     * Verifica se existe uma cliente cadastrado na base de dados.
     * Verifica se existe uma cliente cadastrado na base de dados com base em um nome.
     * @param string $name Nome do cliente a ser verificado.
     * @return array 
     * - "['status'] = true" Se o cliente já existe
     * - "['status'] = false" Se o cliente não existe
     */
    public function check_if_client_exists($name): array
    {
        // Parâmetros para a query
        $params = [
            ':id_agent' => $_SESSION['user']->id,
            ':client_name' => $name
        ];

        // Conexão com a base de dados
        $this->db_connect();

        // SQL
        $sql = "SELECT id FROM persons WHERE AES_ENCRYPT(:client_name, '" . MYSQL_AES_KEY . "') = name AND id_agent = :id_agent";

        // Executa a query
        $result = $this->query($sql, $params);

        // Verifica se encontrou algum registro
        if ($result->affected_rows == 0) {
            // Se não encontrou registros com o mesmo nome, retorna false
            return ['status' => false];
        }

        // Se encontrou algum registro com o mesmo nome, retorna true
        return ['status' => true];
    }

    /**
     * Adiciona um novo cliente na base de dados.
     * @param array $post_data Os dados submetidos pelo usuário para cadastro, via método $_POST
     * @return void Não retorna nada, apenas insere o cliente na base de dados.
     */
    public function add_new_client_to_database(ClientDTO $client)
    {

        // // Converte a data recebida do formulário para objeto DateTime
        // $birthdate = DateTime::createFromFormat('d-m-Y', $post_data['text_birthdate']);

        // parâmetros para a query SQL (Segurança / PDO)
        $params = [
            ':name' => $client->name,
            ':gender' => $client->gender,
            ':birthdate' => $client->birthdate->format('Y-m-d'),
            ':email' => $client->email,
            ':phone' => $client->phone,
            ':interests' => $client->interests,
            ':id_agent' => $client->agentId
        ];

        // Query SQL 
        $sql =
            "INSERT INTO persons VALUES(
            0,
            AES_ENCRYPT(:name, '" . MYSQL_AES_KEY . "'),
            :gender,
            :birthdate,
            AES_ENCRYPT(:email, '" . MYSQL_AES_KEY . "'),
            AES_ENCRYPT(:phone, '" . MYSQL_AES_KEY . "'),
            :interests,
            :id_agent,
            NOW(),
            NOW(),
            NULL
        )";

        // Conexão com a base de dados
        $this->db_connect();

        // Executa a query
        $this->non_query($sql, $params);
    }

    /**
     * Busca um cliente na base de dados com base em seu ID.
     * @param int $id Id do cliente a ser buscado
     * @return array Array associativo contendo dois possíveis valores:
     * - 1. Se Sucesso: Array com 'status' = sucesso e 'data' contendo os dados do cliente
     * - 2. Se Erro: Array com 'status' = error
     */
    public function get_client_data(int $id): array
    {
        // Prepara a query
        $params = [':id' => $id];

        $sql = "SELECT id, AES_DECRYPT(name, '" . MYSQL_AES_KEY . "') AS name, gender, birthdate, 
            AES_DECRYPT(email, '" . MYSQL_AES_KEY . "') AS email, AES_DECRYPT(phone, '" . MYSQL_AES_KEY . "') AS phone, 
            interests FROM persons WHERE id = :id";

        // Conexão com a base de dados 
        $this->db_connect();

        // Executa a query
        $results = $this->query($sql, $params);

        // Verifica se houve um erro na query
        if ($results->affected_rows == 0) {
            return ['status' => 'error'];
        }

        // retorna os dados do cliente
        return ['status' => 'success', 'data' => $results->results[0]];
    }

    /**
     * Verifica se já existe outro cliente com o mesmo nome na base de dados.
     * @param int $id ID do cliente que está sendo editado.
     * @param string $name nome digitado no formulário.
     * @return array Array contendo status igual a true se o nome já existe, ou, 
     * false se o nome ainda não existe na base de dados. 
     */
    public function check_if_name_exists(int $id, string $name): array
    {

        // Parâmetros para a query
        $params = [
            ':id' => $id,
            ':name' => $name,
            ':id_agent' => $_SESSION['user']->id
        ];

        // SQL 
        $sql = "SELECT id 
            FROM persons 
            WHERE id <> :id 
            AND id_agent = :id_agent 
            AND AES_ENCRYPT(:name, '" . MYSQL_AES_KEY . "') = name";

        // Abre a conexão com a base de dados
        $this->db_connect();

        // Executa a query
        $results = $this->query($sql, $params);

        // Se a query devolveu linhas, já existem um cliente com o mesmo nome
        if ($results->affected_rows != 0) {
            return ['status' => true]; // existe conflito de nome
        } else {
            return ['status' => false]; // nome está livre
        }
    }

    /**
     * Atualiza os dados do cliente na base de dados. 
     * Esse método não faz validação nem verifica regras de negócio; 
     * ele apenas atualiza os dados assumindo que o controller já fez todas as checagens antes.
     * @param int $id ID do cliente que está sendo editado.
     * @param ClientDTO $client Dados atualizados do cliente.
     * @return void Esse método não retornada nada, apenas atualiza os dados na base de dados.
     */
    public function update_client_data(int $id, ClientDTO $client)
    {
        // Prepara a query
        $params = [
            ':id' => $id,
            ':name' => $client->name,
            ':gender' => $client->gender,
            ':birthdate' => $client->birthdate->format('Y-m-d'),
            ':email' => $client->email,
            ':phone' => $client->phone,
            ':interests' => $client->interests
        ];

        $sql = "UPDATE persons SET 
                    name = AES_ENCRYPT(:name, '" . MYSQL_AES_KEY . "'),
                    gender = :gender,
                    birthdate = :birthdate,
                    email = AES_ENCRYPT(:email, '" . MYSQL_AES_KEY . "'),
                    phone = AES_ENCRYPT(:phone, '" . MYSQL_AES_KEY . "'),
                    interests = :interests,
                    updated_at = NOW()
                    WHERE id = :id";

        // Abre a conexão com a base de dados
        $this->db_connect();

        // Executa o UPDATE
        $result = $this->non_query($sql, $params);
        // Aqui, eu observei que, para melhoria futura, não temos um retorno do método non_query,
        // caso a query de um erro, ele não vai ser tratado, ocasionando efeitos indesejados. ⚠️
        // Author: Diego 09/12/25 ás 19:20  
    }

    /**
     * Delete um cliente da base de dados.
     * Este método executa um HARD DELETE, ou seja, uma deleção física da base de dados. 
     * @param int $id ID do cliente a ser deletado.
     */
    public function delete_client(int $id)
    {
        // Prepara a query
        $params = [':id' => $id, ':id_agent' => $_SESSION['user']->id];

        $sql = "DELETE FROM persons
                    WHERE :id_agent = id_agent
                    AND :id = id";

        // Abre a conexão com a base de dados
        $this->db_connect();

        // Executa a query
        $result = $this->non_query($sql, $params);
    }

    /**
     * Compara uma string com a senha atual, armazenada em hash. 
     * @param int $userId ID do usuário atual.
     * @param string $password Senha a ser comparada com a senha atual.
     * @return array Se ['status'] = TRUE as senhas são iguais, Se ['status'] = FALSE as senhas são diferentes. 
     * 
     */
    public function verify_password(int $userId, string $password): array
    {
        // parâmetros da query
        $params = ['userId' => $userId];

        // Inicia a conexão com a base de dados
        $this->db_connect();

        // Executa a query
        $resultQuery = $this->query('SELECT passwrd FROM agents WHERE id = :userId', $params);

        // Senha atual
        $currentPasswordHash = $resultQuery->results[0]->passwrd;

        // Verifica se as senhas são iguais
        if (password_verify($password, $currentPasswordHash)) {
            return ['status' => true];
        } else {
            return ['status' => false];
        }
    }

    /**
     * Atualiza a senha na base de dados.
     * @param int $userId ID do usuário atual.
     * @param string $newPassword Nova senha.
     * @return array Se ['status'] = TRUE operação realizada com sucesso, Se ['status'] = FALSE ocorreu um erro. 
     * 
     */
    public function update_password(int $userId, string $newPassword): array
    {
        // Transforma a senha em hash
        $newPasswordHash = password_hash($newPassword, PASSWORD_DEFAULT);
        // Prepara a query
        $params = [':id' => $userId, ':newPassword' => $newPasswordHash];
        // query
        $sql = 'UPDATE agents SET passwrd = :newPassword, updated_at = NOW() WHERE id = :id';

        // Conexão com a base de dados
        $this->db_connect();

        // Executa a query
        $resultQuery = $this->non_query($sql, $params);

        if ($resultQuery->affected_rows >= 1) {
            return ['status' => true];
        } else {
            return ['status' => true];
        }
    }
}
