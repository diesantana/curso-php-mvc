<?php

namespace bng\System;

use Exception;

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

        // captura todos os parâmetros da URL
        // O result é um array associativo com os elementos na sintaxe chave-valor. 
        $parameters = $_GET;

        // Agora vamos remover os parâmtetros ct (controller) e mt (method) da variável $parameters 
        // o objetivo aqui é pegar apenas os parâmetros adicionais
        if (key_exists('ct', $parameters)) {
            unset($parameters['ct']); // Se existe uma chave com o valor 'ct' remove esse elemento
        }

        if (key_exists('mt', $parameters)) {
            unset($parameters['mt']); // Se existe uma chave com o valor 'mt' remove esse elemento
        }


        try {
            // Montando o caminho completo da classe
            $class = "bng\\Controllers\\$controller";
            // Instância a classe
            $controller = new $class();
            // Chama o método passando os parâmetros como argumentos
            $controller->$method(...$parameters);
            // "..." representa o operador rest é utilizado para "espalhar" 
            // os valores do array, sendo passados de forma indivudual como parâmetros na chamada do método

        } catch (Exception $e) {
            die($e->getMessage());
        }
    }
}
