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

    /**
     * Busca Todos os Agentes na base de dados
     * @return Array Array contendo os dados dos agentes em formato stdClass Object
     */
    public function get_all_agents(): array
    {
        $this->db_connect(); // Conexão com a base de dados
        $sql = " 
            SELECT
                a.id,
                CAST(AES_DECRYPT(a.name,'" . MYSQL_AES_KEY . "') AS CHAR) AS name,
                a.profile,
                a.last_login,
                COALESCE(p.total, 0) AS total,
                a.updated_at,
                a.deleted_at
            FROM agents a
            LEFT JOIN (
                SELECT
                id_agent,
                COUNT(*) AS total
                FROM persons
                GROUP BY id_agent
            ) p ON p.id_agent = a.id
            ORDER BY name;";

        // Executa a query
        $results = $this->query($sql);
        return $results->results;
    }

    /**
     * Verifica se o agente existe com base no email. 
     * @param string $email Email do agente a ser verificado.
     * @return array 
     * - "['status'] = true" Se o agente já existe
     * - "['status'] = false" Se o agente não existe
     */
    public function check_if_agent_exists(string $email): array
    {
        // Prepara a query 
        $params = [':name' => $email]; // A coluna email está salvo como "name" na base de dados. 
        $sql = "SELECT  id FROM agents WHERE AES_ENCRYPT(:name, '" . MYSQL_AES_KEY . "') = name";
        // conexão com a base de dados
        $this->db_connect();

        // exceuta a query
        $result = $this->query($sql, $params);

        if ($result->affected_rows == 0) {
            return ['status' => false];
        } else {
            return ['status' => true];
        }
    }

    /**
     * Salva o o agente na base de dados.
     * @param string $email email do agente
     * @param string $profile perfil do agente
     * @return Array Array associativo contendo status, email e PURL.
     * Se a operação não for bem sucedida o retorno será apenas o status = 'error'.
     */
    public function add_agent(string $email, string $profile): array
    {

        $purl = $this->purl_generator(20); // gera o PURL com 20 caracteres

        // Prepara a query
        $params = [':name' => $email, ':profile' => $profile, ':purl' => $purl];
        $sql = "
            INSERT INTO agents (name, profile, purl, last_login, created_at)
            VALUES (AES_ENCRYPT(:name, '" . MYSQL_AES_KEY . "'), :profile, :purl, NOW(), NOW());
        ";

        $this->db_connect(); // conexão com a base de dados

        // executa a query
        $result = $this->non_query($sql, $params);

        if ($result->affected_rows == 0) {
            return ['status' => 'error'];
        } else {
            return ['status' => 'success', 'email' => $email, 'purl' => $purl];
        }
    }

    /**
     * Verifica se a PURL está associada a algum agente na base de dados.
     * @param string $purl PURL a ser verificada.
     * @return array Array associativo contendo status (false se erro e true se sucesso)
     * e o id (se sucesso).
     */
    public function check_the_purl(string $purl): array
    {
        // Prepara a query
        $params = [':purl' => $purl];
        $sql = "SELECT id FROM agents WHERE :purl = purl;";
        $this->db_connect(); // conexão com a base de dados
        // executa a query
        $results = $this->query($sql, $params);

        if ($results->affected_rows == 0) {
            return ['status' => false];
        } else {
            return ['status' => true, 'id' => $results->results[0]->id];
        }
    }

    /**
     * Atualiza a senha na base de dados.
     * @param int $agentId ID do agente.
     * @param string $password Senha a ser cadastrada.
     * @return array Se ['status'] = TRUE operação realizada com sucesso ou FALSE se ocorrer um erro. 
     * 
     */
    public function created_password(int $agentId, string $password): array
    {
        // Transforma a senha em hash
        $password = password_hash($password, PASSWORD_DEFAULT);
        // Prepara a query
        $params = [':id' => $agentId, ':password' => $password];
        $sql = 'UPDATE agents SET passwrd = :password, purl = NULL, updated_at = NOW() WHERE id = :id';

        // Conexão com a base de dados
        $this->db_connect();

        // Executa a query
        $resultQuery = $this->non_query($sql, $params);

        if ($resultQuery->affected_rows >= 1) {
            return ['status' => true];
        } else {
            return ['status' => false];
        }
    }

    /**
     * Gera um código hash de tamanho personalizado.
     * @param int $length Tamanho da string a ser gerada.
     * @return string código hash de tamanho personalizado.
     */
    private function purl_generator(int $length): string
    {
        // generate purl
        $chars = 'abcdefghijkabcdefghijkabcdefghijkABCDEFGHIJKABCDEFGHIJKABCDEFGHIJK';
        $purl = substr(str_shuffle($chars), 0, $length);
        return $purl;
    }

    /**
     * Busca os dados do utilizador (admin ou agent) por id.
     * Busca apenas o nome e perfil para atualização.
     * @param string $id Id do agente. 
     * @return array Array associativo contendo dois valores:
     * - 'status' da operação (true = success ou false = error)
     * - 'data' contendo os dados da consulta se não der nenhum erro
     */
    public function get_agent_by_id(string $id): array
    {
        // conexão com a base de dados
        $this->db_connect();

        // prepara a query
        $params = [':id' => $id];
        $sql = "SELECT id, AES_DECRYPT(name, '" . MYSQL_AES_KEY . "') AS name, profile FROM agents WHERE id = :id;";

        // executa a query
        $resultQuery = $this->query($sql, $params);

        if ($resultQuery->status == 'error') {
            return ['status' => false];
        } else {
            return ['status' => true, 'data' => $resultQuery->results[0]];
        }
    }

    /**
     * Atualiza o agente na base de dados.
     * @param string $id Id encriptado do agente a ser atualizado
     * @param string $email novo email
     * @param string $profile perfil do agente
     * @return Array Array associativo contendo status da operação (true ou false)
     */
    public function update_agent(string $id, string $email, string $profile): array
    {

        // Prepara a query
        $params = [':name' => $email, ':profile' => $profile, ':id' => aes_decrypt($id)];
        $sql = "
            UPDATE agents SET name = AES_ENCRYPT(:name, '" . MYSQL_AES_KEY . "'), 
            profile = :profile, updated_at = NOW() WHERE id = :id;
        ";

        $this->db_connect(); // conexão com a base de dados

        // executa a query
        $result = $this->non_query($sql, $params);

        if ($result->affected_rows == 0) {
            return ['status' => false];
        } else {
            return ['status' => true];
        }
    }

    /**
     * Verifica se o nome está disponível para atualização.
     * @param string $id Id do agente a ser atualizado.
     * @param string $email Email ou Username do agente a ser verificado.
     * @return array 
     * - "['status'] = true" Se o agente já existe
     * - "['status'] = false" Se o agente não existe
     */
    public function verify_name_is_available(string $id, string $email): array
    {
        // Prepara a query 
        $params = [':id' => aes_decrypt($id), ':name' => $email];
        $sql = "SELECT  id FROM agents 
                WHERE AES_ENCRYPT(:name, '" . MYSQL_AES_KEY . "') = name
                AND id <> :id";
        // conexão com a base de dados
        $this->db_connect();

        // exceuta a query
        $result = $this->query($sql, $params);

        if ($result->affected_rows == 0) {
            return ['status' => false];
        } else {
            return ['status' => true];
        }
    }

    /**
     * Busca os dados do utilizador a ser deletado.
     * Busca o ID, Username e quantidade de clientes do agente.
     * @param string $id Id do agente. 
     * @return array Array Array associativo contendo status da operação (true ou false) 
     * e os dados do agente se a operação for bem sucedida. 
     */
    public function get_agent_for_delete(string $id): array
    {
        // conexão com a base de dados
        $this->db_connect();

        // prepara a query
        $params = [':id' => $id];
        $sql = "SELECT a.id, CAST(AES_DECRYPT(a.name, '" . MYSQL_AES_KEY . "') AS CHAR) AS name, 
            COUNT(p.id) AS total_clients FROM agents a LEFT JOIN persons p ON p.id_agent = a.id WHERE a.id = :id
            GROUP BY a.id, a.name";

        // executa a query
        $resultQuery = $this->query($sql, $params);

        if ($resultQuery->affected_rows == 0) {
            return ['status' => false]; // ocorreu algum erro
        } else {
            return ['status' => true, 'data' => $resultQuery->results[0]];
        }
    }


    /**
     * Deleta um agente da base de dados.
     * Realiza um soft delete, atualizando a coluna deleted_at. O agente poderá
     * ser recuperado a qualquer momento.  
     * @param string $id ID do agente a ser deletado. 
     * @return Array Array associativo contendo status da operação (true ou false)
     */
    function soft_delete_agent(string $id): array
    {

        // prepara a query
        $params = [':id' => $id];
        $sql = 'UPDATE agents SET deleted_at = NOW() WHERE id = :id';

        $this->db_connect(); // conexão com a base de dados

        $results = $this->non_query($sql, $params); // executa a query

        if ($results->affected_rows == 0) {
            return ['status' => false]; // ocorreu algum erro
        } else {
            return ['status' => true];
        }
    }

    /**
     * Recupera um agente deletado.
     * Atualiza a coluna deleted_at para o valor NULL.
     * @param string $id ID do agente a ser RECUPERADO. 
     * @return Array Array associativo contendo status da operação (true ou false)
     */
    function recover_agent(string $id): array
    {

        // prepara a query
        $params = [':id' => $id];
        $sql = 'UPDATE agents SET deleted_at = NULL WHERE id = :id';

        $this->db_connect(); // conexão com a base de dados

        $results = $this->non_query($sql, $params); // executa a query

        if ($results->affected_rows == 0) {
            return ['status' => false]; // ocorreu algum erro
        } else {
            return ['status' => true];
        }
    }
}
