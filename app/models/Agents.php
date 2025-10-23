<?php

namespace bng\Models;

/**
 * Responsável por acessar e manipular a base de dados, no que diz
 * respeito a tabela Agents. 
 */
class Agents extends BaseModel
{
    /**
     * Verifica se as credenciais de login são válidas.
     * Esta classe acessa á base de dados e verifica se as credenciais de login existem na
     * base de dados. 
     * @param string $username username ou email para acesso á aplicação
     * @param string $password Senha de acesso do username
     * @return array Retorna um array com o status da consultam, sendo true se as credenciais forem válidas 
     * e false caso contrário.
     */
    public function check_login(string $username, string $password) : array {
        // Cria a conexão com a base de dados. 
        $this->db_connect(); 
        /* O método db_connect é herdado de BaseModel, 
        sendo responsável por inicia a conexão com a base de dados*/

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
        if($resultQuery->affected_rows == 0) {
            return ['status' => false];
        } 
        
        // Aqui já foi confirmado que o username existe, vamos verificar o password
        if(!password_verify($password, $resultQuery->results[0]->passwrd)) {
            return ['status' => false];
            // password_verify verifica se uma senha corresponde a um hash
            // Se a NÃO senha estiver correta, returna false e encerra a lógica deste método
        } 

        // Ao confirmar que o username e senha existem retorna status=true
        return ['status' => true];

    }
}
