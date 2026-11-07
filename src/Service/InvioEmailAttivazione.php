<?php

declare(strict_types=1);

namespace Ladythelemons\Fitslot\Service;

use PHPMailer\PHPMailer\PHPMailer;

class InvioEmailAttivazione
{
    private PHPMailer $mailer;
    private string $urlBase;

    public function __construct(?PHPMailer $mailer = null, ?string $urlBase = null)
    {
        $this->mailer = $mailer ?? new PHPMailer(true);
        $this->urlBase = rtrim($urlBase ?? ($_ENV['APP_URL'] ?? 'http://127.0.0.1:8000'), '/');
    }

    public function invia(string $email, string $token): void
    {
        $this->mailer->isSMTP();
        $this->mailer->Host = $_ENV['MAIL_HOST'] ?? '127.0.0.1';
        $this->mailer->Port = (int) ($_ENV['MAIL_PORT'] ?? 1025);
        $this->mailer->SMTPAuth = false;
        $this->mailer->CharSet = 'UTF-8';
        $this->mailer->setFrom(
            $_ENV['MAIL_FROM_ADDRESS'] ?? 'no-reply@fitslot.test',
            $_ENV['MAIL_FROM_NAME'] ?? 'FitSlot'
        );
        $this->mailer->addAddress($email);

        $link = $this->urlBase . '/attiva-account.php?token=' . urlencode($token);
        $this->mailer->isHTML(false);
        $this->mailer->Subject = 'Attiva il tuo account FitSlot';
        $this->mailer->Body = "Per attivare il tuo account FitSlot apri questo link:\n\n" . $link . "\n\nIl link è valido per un tempo limitato.";
        $this->mailer->send();
    }
}
