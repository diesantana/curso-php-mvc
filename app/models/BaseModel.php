<?php

namespace bng\Models;

use bng\System\Database;

// ============================================================================
// Classe BaseModel
// ---------------------------------------------------------------------------
// Classe abstrata que serve como base para todos os Models da aplicação.
// Ela centraliza a criação da conexão com o banco e oferece métodos
// utilitários para execução de queries.
// ============================================================================
abstract class BaseModel
{
    // Atributo que armazenará a instância da classe Database
    public $db;

    // ========================================================================
    // Método responsável por criar a conexão com o banco de dados
    // ------------------------------------------------------------------------
    // Ele utiliza as constantes definidas em config.php e passa os dados
    // de configuração para a classe Database, que encapsula o uso do PDO.
    // ========================================================================
    public function db_connect()
    {
        // Array de configuração com as credenciais do banco
        $options = [
            'host'     => MYSQL_HOST,
            'database' => MYSQL_DATABASE,
            'username' => MYSQL_USERNAME,
            'password' => MYSQL_PASSWORD
        ];

        // Cria uma instância da classe Database e guarda em $this->db
        // Assim, os Models filhos terão acesso a essa conexão.
        $this->db = new Database($options);
    }

    // ========================================================================
    // Método auxiliar para executar queries SQL
    // ------------------------------------------------------------------------
    // Recebe o comando SQL e, opcionalmente, parâmetros de substituição.
    // Internamente, apenas encaminha a execução para o método execute_query()
    // da classe Database, mantendo a lógica centralizada lá.
    // ========================================================================
    public function query($sql = "", $params = [])
    {
        // Encaminha o SQL para a classe Database executar
        // O retorno é um objeto estruturado com status, results, etc.
        return $this->db->execute_query($sql, $params);
    }


    /**
     * Método auxiliar para executar queries SQL que atualizam a base de dados (INSERT, UPDATE, DELETE).
     * Internamente, apenas encaminha a execução para o método execute_non_query() da classe Database, mantendo a lógica centralizada lá.
     * @param string $sql comando SQL.
     * @param array $params array que representa os parâmetros de substituição (Segurança / PDO).
     * @return mixed Resultado da operação, contendo valores como: status (success / error), linhas afetadas e último ID. 
     */
    public function non_query(string $sql, $params = []) : mixed
    {
        // Encaminha o SQL para a classe Database executar
        // O retorno é um objeto estruturado com status, results, etc.
        return $this->db->execute_non_query($sql, $params);
    }


}
