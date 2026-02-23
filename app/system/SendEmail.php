<?php

declare(strict_types=1);

namespace bng\System;

use Exception;
use PHPMailer\PHPMailer\PHPMailer;

class SendEmail
{

    /**
     * Envia um email de configuração de senha do agente via PURL.
     * @param string $email Email do agente.
     * @param string $link PURL a ser enviado no e-mail.
     * @return array Retorna um array contendo status da operação (success ou error) e mensagem.
     */
    public function send_agent_password_setup_email(string $email, string $link): array
    {
        $htmlBody = $this->generate_email_body($link);
        $altBody  = $this->generate_alt_email_body($link);

        return $this->sendEmail(
            [$email],
            'Concluir registo de agente',
            $htmlBody,
            $altBody
        );
    }

    /**
     * Envia um email para o agente com o código de recuperação de senha.
     * O email contém o código de recuperação de senha.
     * @param string $email Email do agente.
     * @param string $code Código de recuperação de senha.
     * @return array Retorna um array contendo status da operação (success ou error) e mensagem.
     */
    public function send_password_recovery_code_email(string $email, string $code): array
    {
        $subject = 'Código para recuperar password';

        $html_body = $this->password_recovery_code_template($code);
        $alt_body  = $this->password_recovery_code_alt_text($code);

        return $this->sendEmail(
            [$email],
            $subject,
            $html_body,
            $alt_body
        );
    }

    /**
     * Envia um e-mail (HTML) para um ou mais destinarátios usando PHPMailer.
     * @param array $recipients Lista de destinatários.
     * @param string $subject Assunto do email.
     * @param string $emailBody Corpo do email (HTML).
     * @param string $altEmailBody Corpo alternativo para o email (txt).
     * @return array Retorna um array contendo status da operação (success ou error) e mensagem.
     */
    private function sendEmail(array $recipients, string $subject, string $emailBody, string $altEmailBody): array
    {
        $mail = new PHPMailer(true); // Instancia o obj e ativa as exceções

        try {
            //Configurações do servidor
            /*
            $mail->SMTPDebug = SMTP::DEBUG_SERVER;                          //Enable verbose debug output
            $mail->isSMTP();                                                //Send using SMTP
            $mail->Host       = EMAIL_HOST;                                 //Set the SMTP server to send through
            $mail->SMTPAuth   = true;                                       //Enable SMTP authentication
            $mail->Username   = EMAIL_USERNAME;                             //SMTP username
            $mail->Password   = EMAIL_PASSWORD;                             //SMTP password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;                //Enable implicit TLS encryption
            $mail->Port       = EMAIL_PORT;                                 //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`
            */

            $mail->CharSet = 'UTF-8';
            $mail->setFrom(EMAIL_FROM, EMAIL_FROM_NAME); // Autor do email

            //Destinatários
            foreach ($recipients as $email) {
                $mail->addAddress($email);
            }

            // Conteúdo
            $mail->isHTML(true);  // Formato HTML
            $mail->Subject = $subject; // Assunto do email
            $mail->Body    = $emailBody; // Corpo do email (html)
            $mail->AltBody = $altEmailBody; // Corpo do email alternativo

            $mail->send(); // Envia o email
            return ['status' => 'success', 'message' => 'Email enviado com sucesso'];
        } catch (Exception $e) {
            return ['status' => 'error', 'message' => $mail->ErrorInfo];
        }
    }

    /**
     * Monta o corpo do email.
     * @param string $link URL para o cadastro de senha.
     * @return string Corpo do email em formato HTML. 
     */
    private function generate_email_body(string $link): string
    {
        $html = '<p>Para concluir o processo de registro de agente, clique no link abaixo:</p>';
        $html .= "<a href='{$link}'>Concluir registro de agente</a>";
        return $html;
    }

    /**
     * Monta o corpo alternativo do email, utilizando apenas texto.
     * @param string $link URL para o cadastro de senha.
     * @return string Corpo do email em formato txt. 
     */
    private function generate_alt_email_body(string $link): string
    {
        return <<<TEXT
                Para concluir o processo de registro de agente, acesse o link abaixo:

                {$link}
        TEXT;
    }

    /**
     * Monta o corpo do email para recuperação de senha.
     * @param string $code Código de recuperação de senha.
     * @return string Corpo do email em formato HTML.
     */
    private function password_recovery_code_template(string $code): string
    {
        return '
        <p>Recebemos um pedido para recuperar a sua password.</p>

        <p>O seu código de recuperação é:</p>

        <h2 style="letter-spacing: 2px; font-size: 28px;">
            ' . htmlspecialchars($code) . '
        </h2>
    ';
    }

    /**
     * Monta o corpo alternativo do email para recuperação de senha.
     * @param string $code Código de recuperação de senha.
     * @return string Corpo do email em formato txt.
     */
    private function password_recovery_code_alt_text(string $code): string
    {
        return
            "Recebemos um pedido para recuperar a sua password.\n\n" .
            "O seu código de recuperação é: {$code}\n\n" .
            "Se você não solicitou esta operação, ignore este email.";
    }
}
