<?php

namespace bng\Models;

class AdminModel extends BaseModel
{
    /**
     * Busca Todos os clientes na base de dados, com os seus respectivos agentes. 
     * @return Array Array contendo os dados dos clientes em formato stdClass Object
     */
    public function get_all_clients(): array
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

    /**
     * Busca o total de clientes por agente
     * @return Array Array contendo os dados dos agentes e a quantidade 
     * de clientes em formato stdClass Object
     */
    public function get_client_count_by_agent(): array
    {

        $this->db_connect(); // conexão com a base de dados

        // SQL
        $sql = "SELECT 
                AES_DECRYPT(agents.name, '" . MYSQL_AES_KEY . "') as 'name',
                COUNT(*) as 'total'
                FROM agents
                JOIN persons ON agents.id = persons.id_agent
                WHERE persons.deleted_at IS NULL
                GROUP BY agents.name
                ORDER BY total DESC;";

        // executa a query
        $result = $this->query($sql);
        return $result->results;
    }

    /**
     * Busca dados para as estatísticas globais
     * @return Array Array associativo contendo as estatíticas globais
     */
    public function get_global_statistics(): array
    {
        $result = [];

        // Conexão com a base de dados
        $this->db_connect();

        // Total de agentes
        $queryTotalAgents = $this->query("SELECT COUNT(*) AS 'total' FROM agents;");
        $result['totalAgents'] = $queryTotalAgents->results[0]->total;

        // Número total de clientes
        $queryTotalClients = $this->query("SELECT COUNT(*) AS 'total' FROM persons WHERE deleted_at IS NULL");
        $result['totalClients'] = $queryTotalClients->results[0]->total;

        // Número total de clientes INATIVOS
        $queryTotalClientsInactives = $this->query("SELECT COUNT(*) AS 'total' FROM persons WHERE deleted_at IS NOT NULL");
        $result['totalClientsInactives'] = $queryTotalClientsInactives->results[0]->total;

        // Média de clientes por agente
        $queryAverageClientsPerAgent = $this->query("
            SELECT ROUND(AVG(qtd_clientes), 1) AS media
            FROM (
            SELECT id_agent, COUNT(*) AS qtd_clientes
            FROM persons
            WHERE id_agent IS NOT NULL
            GROUP BY id_agent
            ) x;
        ");
        $result['AverageClientsPerAgent'] = $queryAverageClientsPerAgent->results[0]->media;

        // Idade do cliente mais novo
        $queryYoungerAge = $this->query("
            SELECT MIN(TIMESTAMPDIFF(YEAR, birthdate, CURDATE())) AS younger
            FROM persons
            WHERE birthdate IS NOT NULL;
        ");
        $result['youngerAge'] = $queryYoungerAge->results[0]->younger;
        
        // Idade do cliente mais velho
        $queryOlderAge = $this->query("
            SELECT MAX(TIMESTAMPDIFF(YEAR, birthdate, CURDATE())) AS older
            FROM persons
            WHERE birthdate IS NOT NULL;
        ");
        $result['olderAge'] = $queryOlderAge->results[0]->older;

        // Média de idade
        $queryAverageAge = $this->query("
            SELECT 
            ROUND(AVG(TIMESTAMPDIFF(YEAR, birthdate, CURDATE())), 0) AS media_idade
            FROM persons
            WHERE birthdate IS NOT NULL;

        ");
        $result['averageAge'] = $queryAverageAge->results[0]->media_idade;

        // % De clientes por gênero
        $queryClientsByGender = $this->query("
            SELECT
            ROUND(100 * SUM(UPPER(gender) = 'M') / NULLIF(SUM(UPPER(gender) IN ('M','F')), 0), 0) AS pct_homens,
            ROUND(100 * SUM(UPPER(gender) = 'F') / NULLIF(SUM(UPPER(gender) IN ('M','F')), 0), 0) AS pct_mulheres
            FROM persons;

        ");
        $result['percentageMen'] = $queryClientsByGender->results[0]->pct_homens;
        $result['percentageWomen'] = $queryClientsByGender->results[0]->pct_mulheres;

        return $result;
    }
}
