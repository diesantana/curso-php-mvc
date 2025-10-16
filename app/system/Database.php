<?php

namespace bng\System;

use PDO;
use PDOException;
use stdClass;

// ============================================================================
// Classe Database - camada de abstração de acesso ao banco
// Encapsula o uso do PDO e fornece métodos padronizados para SELECT e DML
// ============================================================================
class Database
{
    // Propriedades de conexão
    private $_host;        // Endereço do servidor MySQL
    private $_database;    // Nome da base de dados
    private $_username;    // Usuário
    private $_password;    // Senha
    private $_return_type; // Define o formato de retorno das consultas (objeto ou array)

    // ========================================================================
    // Construtor - inicializa as configurações e define o tipo de retorno
    public function __construct($cfg_options, $return_type = 'object')
    {
        // Define os dados de conexão recebidos do BaseModel
        $this->_host     = $cfg_options['host'];
        $this->_database = $cfg_options['database'];
        $this->_username = $cfg_options['username'];
        $this->_password = $cfg_options['password'];

        // Define o tipo de retorno dos dados (objeto ou array associativo)
        if (!empty($return_type) && $return_type == 'object') {
            $this->_return_type = PDO::FETCH_OBJ;     // Retorna objetos
        } else {
            $this->_return_type = PDO::FETCH_ASSOC;   // Retorna arrays associativos
        }
    }

    // ========================================================================
    // EXECUTA CONSULTAS (SELECT)
    public function execute_query($sql, $parameters = null)
    {
        // Cria uma nova conexão PDO
        $connection = new PDO(
            'mysql:host=' . $this->_host . ';dbname=' . $this->_database . ';charset=utf8',
            $this->_username,
            $this->_password,
            array(PDO::ATTR_PERSISTENT => true) // Conexão persistente (reutiliza conexões abertas)
        );

        $results = null; // Inicializa a variável de resultados

        try {
            // Prepara a query
            $db = $connection->prepare($sql);

            // Executa com ou sem parâmetros
            if (!empty($parameters)) {
                $db->execute($parameters);
            } else {
                $db->execute();
            }

            // Captura os resultados
            $results = $db->fetchAll($this->_return_type);

        } catch (PDOException $err) {
            // Fecha a conexão e retorna erro padronizado
            $connection = null;
            return $this->_result('error', $err->getMessage(), $sql, null, 0, null);
        }

        // Fecha conexão (boa prática)
        $connection = null;

        // Retorna sucesso com resultados e metadados
        return $this->_result('success', 'success', $sql, $results, $db->rowCount(), null);
    }

    // ========================================================================
    // EXECUTA COMANDOS (INSERT, UPDATE, DELETE)
    public function execute_non_query($sql, $parameters = null)
    {
        // Cria nova conexão
        $connection = new PDO(
            'mysql:host=' . $this->_host . ';dbname=' . $this->_database . ';charset=utf8',
            $this->_username,
            $this->_password,
            array(PDO::ATTR_PERSISTENT => true)
        );

        // Inicia transação (garante atomicidade)
        $connection->beginTransaction();

        try {
            // Prepara e executa o comando
            $db = $connection->prepare($sql);
            if (!empty($parameters)) {
                $db->execute($parameters);
            } else {
                $db->execute();
            }

            // Obtém o último ID inserido (caso exista)
            $last_inserted_id = $connection->lastInsertId();

            // Confirma a transação
            $connection->commit();

        } catch (PDOException $err) {
            // Em caso de erro, desfaz alterações e retorna erro padronizado
            $connection->rollBack();
            $connection = null;
            return $this->_result('error', $err->getMessage(), $sql, null, 0, null);
        }

        // Fecha a conexão
        $connection = null;

        // Retorna sucesso com linhas afetadas e último ID
        return $this->_result('success', 'success', $sql, null, $db->rowCount(), $last_inserted_id);
    }

    // ========================================================================
    // MÉTODO PRIVADO DE FORMATAÇÃO DO RETORNO
    private function _result($status, $message, $sql, $results, $affected_rows, $last_id)
    {
        // Cria um objeto genérico com as informações da operação
        $tmp = new stdClass();
        $tmp->status        = $status;         // 'success' ou 'error'
        $tmp->message       = $message;        // Mensagem de sucesso/erro
        $tmp->query         = $sql;            // SQL executado
        $tmp->results       = $results;        // Resultado (SELECT)
        $tmp->affected_rows = $affected_rows;  // Linhas afetadas
        $tmp->last_id       = $last_id;        // Último ID inserido (se aplicável)
        return $tmp;
    }
}
