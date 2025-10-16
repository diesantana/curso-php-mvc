<?php

namespace bng\Models;

class Agents extends BaseModel
{
    public function getTotalAgents(): object {
        $this->db_connect(); // Faz a conexão com a base de dados
        return $this->query('SELECT COUNT(*) AS total FROM agents'); // Executa a query (SELECT)
    }
}
