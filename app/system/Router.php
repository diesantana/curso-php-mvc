<?php

namespace bng\System;

use bng\Controllers\ErrorController;

class Router
{
    public static function dispatch()
    {

        // Verifica se a url é uma rota válida
        if (!isset($_GET['ct']) && $_SERVER['REQUEST_URI'] !== '/BNG/public/') {
            self::pageNotFound();
        }

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

        // Montando o caminho completo da classe
        $class = "bng\\Controllers\\$controller";

        // verifica se o controller existe
        if (!class_exists($class)) {
            self::pageNotFound();
        }

        // Instância a classe
        $controller = new $class();

        // verifica se o método existe e é público
        // is_callable verifica se é publico
        if (
            !method_exists($controller, $method) ||
            !is_callable([$controller, $method])
        ) {
            self::pageNotFound();
        }

        // Chama o método passando os parâmetros como argumentos
        $controller->$method(...$parameters);
        // "..." representa o operador rest é utilizado para "espalhar" 
        // os valores do array, sendo passados de forma indivudual como parâmetros na chamada do método
    }

    /**
     * Redireciona a lógica para o ErrorController. 
     * Esse método é útil quando você precisa redireiona para página 404
     */
    private static function pageNotFound()
    {
        $errorController = new ErrorController();
        $errorController->notFound();
        exit;
    }
}
