<?php

use Monolog\Handler\StreamHandler;
use Monolog\Logger;

/**
 * Verifica se existe um usuário logado
 * @return bool Retorna true se existe um usuário logado, e false caso contrário.
 */
function checkSession(): bool
{
    return isset($_SESSION['user']);
}

/**
 * Função auxiliar para Debug.  
 * Essa função exibe o valor de uma variável, seja ela uma coleção ou um valor único. 
 * @param mixed $data Valor a ser exibido
 * @param bool $die Define se o código será interrompido (true é o valor padrão)
 */
function printData(mixed $data, bool $die = true)
{

    /*A tag <pre> permite exibir os dados preservando espaços, 
    formatações e quebras de linha*/
    echo '<pre>';

    // Verifica se o valor é uma colação (Objeto ou array) ou um valor único 
    if (is_object($data) || is_array($data)) {
        print_r($data);
        /* Se o valor $data for um objeto ou um array o 
        echo não funcionaria para exibir os dados*/
    } else {
        echo $data;
        // Se não for array ou objeto, exibe com echo
    }
    echo '</pre>';

    // Verifica se o script será interrompido ou não
    if ($die) {
        // Se $die = true, interrompe a execução do script
        die('<br>FIM<br>');
    }
}

/**
 * Registra mensagens no log da aplicação.
 * 
 * @param string $message Mensagem a ser registrada.
 * @param string $level Nível de log (info, notice, warning, error, etc.).
 */
function logger(string $message, string $level = 'info')
{

    // cria o canal de registros de log 
    $log = new Logger('app_logs');
    // Define o arquivo aonde os logs serão gravados.
    // LOGS_PATH aponta para o diretório aonde os logs serão gravados.
    $log->pushHandler(new StreamHandler(LOGS_PATH));

    // Registra a mensagem conforme o nível de logs específico. 
    switch ($level) {
        case 'info':
            $log->info($message);
            break;
        case 'notice':
            $log->notice($message);
            break;
        case 'warning':
            $log->warning($message);
            break;
        case 'error':
            $log->error($message);
            break;
        case 'critical':
            $log->critical($message);
            break;
        case 'alert':
            $log->alert($message);
            break;
        case 'emergency':
            $log->emergency($message);
            break;
        default:
            $log->info($message);
            break;
    }
}
