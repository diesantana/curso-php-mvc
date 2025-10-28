<?php

namespace bng\Models;

use bng\System\Database;

/**
 * Responsável por acessar e manipular a base de dados, no que diz
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
}
