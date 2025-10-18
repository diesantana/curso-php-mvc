<?php

/**
 * Verifica se existe um usuário logado
 * @return bool Retorna true se existe um usuário logado, e false caso contrário.
 */
function checkSession(): bool {
    return isset($_SESSION['user']);
}

/**
 * Função auxiliar para Debug.  
 * Essa função exibe o valor de uma variável, seja ela uma coleção ou um valor único. 
 * @param mixed $data Valor a ser exibido
 * @param bool $die Define se o código será interrompido (true é o valor padrão)
 */
function printData(mixed $data, bool $die = true) {
    
    /*A tag <pre> permite exibir os dados preservando espaços, 
    formatações e quebras de linha*/
    echo '<pre>'; 

    // Verifica se o valor é uma colação (Objeto ou array) ou um valor único 
    if(is_object($data) || is_array($data)) {
        print_r($data);
        /* Se o valor $data for um objeto ou um array o 
        echo não funcionaria para exibir os dados*/
    } else {
        echo $data;
        // Se não for array ou objeto, exibe com echo
    }
    echo '</pre>'; 

    // Verifica se o script será interrompido ou não
    if($die) {
        // Se $die = true, interrompe a execução do script
        die('<br>FIM<br>');
    }
}