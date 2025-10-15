<?php

namespace bng\Controllers;
/**
 * Classe abstrata para os controladores
 * 
 * Esta classe contém métodos comuns para todos os controladores.
 * 
 */
abstract class BaseController {

    /**
     * Responsável por montar a view 
     * 
     * Método que monta a interface visual.
     * @param string $view O nome da view a ser carregada. 
     * @param array $data (Opcional) Valores a serem passados para a view. 
     * 
     */
    public function view(string $view, array $data = []) {

        // Validando se $data é um array
        if(!is_array($data)) {
            die('Data is not an array' . var_dump($data)); 
        }

        // Transforma os valores do array em variáveis. 
        extract($data);

        // Monta o caminho da view
        $viewPath = "../app/views/{$view}.php";

        // Verifica se a view existe
        if(file_exists($viewPath)) {
            // Se a view existir, carrega ela. 
            require_once($viewPath);
        } else {
            die("View '{$view}' não encontrada.");
        }

    }
}