<?php

namespace bng\Models;

use bng\System\Database;

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
    public function set_user_last_login(string $id): mixed {

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
    public function get_agent_clients(string $id) :array {
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
}
