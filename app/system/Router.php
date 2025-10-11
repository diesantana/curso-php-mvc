<?php

namespace bng\System;

class Router
{
    public static function dispatch()
    {
        // Valores padrão
        $controller = 'main';
        $method     = 'index';

        // Verifica se os valores ct e mt foram passados na URL
        // ct = controller 
        // mt = method
        // Caso seja passado um valor, atribui nas variáveis $controller e $method. 
        // Se nenhum valor for passado, permanece com o valor padrão. 
        if (isset($_GET['ct'])) {
            $controller = $_GET['ct'];
        }

        if (isset($_GET['mt'])) {
            $method = $_GET['mt'];
        }

        // Leitura dos parâmetros principais
        // Apenas exibe os dados das variáveis $controller e $method para teste
        echo "<h3>Controller: {$controller}</h3>";
        echo "<h3>Método: {$method}</h3>";


        // captura todos os parâmetros da URL
        // O result é um array associativo com os elementos na sintaxe chave-valor. 
        $parameters = $_GET;

        // Agora vamos remover os parâmtetros ct (controller) e mt (method) da variável $parameters 
        // o objetivo aqui é pegar apenas os parâmetros adicionais
        if(key_exists('ct', $parameters)) {
            unset($parameters['ct']); // Se existe uma chave com o valor 'ct' remove esse elemento
        }

        if(key_exists('mt', $parameters)) { 
            unset($parameters['mt']); // Se existe uma chave com o valor 'mt' remove esse elemento
        }


        // Exibindo os parâmetros adicionais
        // teste com a URL: http://localhost/bng/public?ct=main&mt=detalhes&id=10&user=admin
        echo '<pre>';
        var_dump($parameters);
    }
}
