<?php

declare(strict_types=1);

use Ladythelemons\Fitslot\Service\InvioEmailAttivazione;
use PHPMailer\PHPMailer\PHPMailer;
use PHPUnit\Framework\TestCase;

final class InvioEmailAttivazioneTest extends TestCase
{
    public function testPreparaEInviaEmailConLink(): void
    {
        $mailer = new MailerDiProva();
        $servizio = new InvioEmailAttivazione($mailer, 'http://localhost:8000');

        $servizio->invia('anna@example.test', 'token-di-prova');

        $this->assertTrue($mailer->emailInviata);
        $this->assertSame('anna@example.test', $mailer->destinatario);
        $this->assertStringContainsString(
            'http://localhost:8000/attiva-account.php?token=token-di-prova',
            $mailer->Body
        );
        $this->assertSame('Attiva il tuo account FitSlot', $mailer->Subject);
    }
}

final class MailerDiProva extends PHPMailer
{
    public bool $emailInviata = false;
    public string $destinatario = '';

    public function send(): bool
    {
        $this->emailInviata = true;
        return true;
    }

    public function addAddress($address, $name = ''): bool
    {
        $this->destinatario = $address;
        return parent::addAddress($address, $name);
    }
}
