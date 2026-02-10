<?php

declare(strict_types=1);

namespace bng\System;

class SendEmail {


    /**
     * Monta o corpo do email.
     * @param string $link URL para o cadastro de senha.
     * @return string Corpo do email em formato HTML. 
     */
    private function generate_email_body(string $link): string {
        $html = '<p>Para concluir o processo de registro de agente, clique no link abaixo:</p>';
        $html .= "<a href='{$link}'>Concluir registro de agente</a>";
        return $html;
    }
}