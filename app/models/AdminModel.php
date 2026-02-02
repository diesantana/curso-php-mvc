<?php

namespace bng\Models;

class AdminModel extends BaseModel
{
    /**
     * Busca Todos os clientes na base de dados, com os seus respectivos agentes. 
     * @return Array Array contendo os dados dos clientes em formato stdClass Object
     */
    public function get_all_clients() :array
    {
        $this->db_connect(); // Conexão com a base de dados
        $sql =
            "SELECT " .
            "persons.id, " .
            "AES_DECRYPT(persons.name, '" . MYSQL_AES_KEY . "') as name, " . // criptografia
            "persons.gender, " .
            "persons.birthdate, " .
            "AES_DECRYPT(persons.email, '" . MYSQL_AES_KEY . "') as email, " . // criptografia
            "AES_DECRYPT(persons.phone, '" . MYSQL_AES_KEY . "') as phone, " . // criptografia
            "persons.interests, " .
            "persons.created_at, " .
            "AES_DECRYPT(agents.name, '" . MYSQL_AES_KEY . "') as agent " . // criptografia
            "FROM persons INNER JOIN agents ON persons.id_agent = agents.id " .
            "WHERE persons.deleted_at IS NULL " .
            "ORDER BY persons.created_at DESC";

        // Executa a query
        $results = $this->query($sql);

        return $results->results;
    }
}
