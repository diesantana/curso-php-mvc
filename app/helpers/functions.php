<?php

function printData($data, $die = true) {
    
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