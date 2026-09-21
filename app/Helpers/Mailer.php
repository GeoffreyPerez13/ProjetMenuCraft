<?php
/**
 * Mailer — Envoi d'emails HTML via mail() natif
 */
class Mailer
{
    private string $from = 'no-reply@menucraft.com';
    private string $fromName = 'MenuCraft';

    public function send(string $to, string $subject, string $htmlBody): bool
    {
        $boundary = md5(uniqid(time()));

        $headers = [
            'From: ' . $this->fromName . ' <' . $this->from . '>',
            'Reply-To: contact.menucraft@gmail.com',
            'MIME-Version: 1.0',
            'Content-Type: multipart/alternative; boundary="' . $boundary . '"',
            'X-Mailer: MenuCraft/1.0',
        ];

        $plainText = strip_tags(str_replace(['<br>', '<br/>', '<br />', '</p>'], "\n", $htmlBody));

        $body = "--$boundary\r\n";
        $body .= "Content-Type: text/plain; charset=UTF-8\r\n\r\n";
        $body .= $plainText . "\r\n\r\n";
        $body .= "--$boundary\r\n";
        $body .= "Content-Type: text/html; charset=UTF-8\r\n\r\n";
        $body .= $this->wrapInTemplate($subject, $htmlBody) . "\r\n\r\n";
        $body .= "--$boundary--";

        $result = @mail($to, $subject, $body, implode("\r\n", $headers));
        $this->log($to, $subject, $result);
        return $result;
    }

    private function wrapInTemplate(string $subject, string $content): string
    {
        return '<!DOCTYPE html><html><head><meta charset="UTF-8"></head>
        <body style="font-family:Inter,Arial,sans-serif;background:#fafaf9;padding:40px 20px;">
        <div style="max-width:600px;margin:0 auto;background:#fff;border-radius:12px;overflow:hidden;box-shadow:0 2px 12px rgba(0,0,0,0.08);">
            <div style="background:linear-gradient(135deg,#b45309,#d97706);padding:30px;text-align:center;">
                <h1 style="color:#fff;margin:0;font-size:24px;">🍽️ MenuCraft</h1>
            </div>
            <div style="padding:32px;">' . $content . '</div>
            <div style="padding:20px 32px;background:#fafaf9;text-align:center;color:#a8a29e;font-size:12px;">
                © ' . date('Y') . ' MenuCraft — Tous droits réservés
            </div>
        </div></body></html>';
    }

    private function log(string $to, string $subject, bool $success): void
    {
        $logDir = BASE_PATH . '/cron/logs';
        if (!is_dir($logDir)) mkdir($logDir, 0755, true);
        $status = $success ? 'OK' : 'FAILED';
        $line = date('Y-m-d H:i:s') . " [$status] To: $to | Subject: $subject\n";
        file_put_contents($logDir . '/mail.log', $line, FILE_APPEND | LOCK_EX);
    }
}
