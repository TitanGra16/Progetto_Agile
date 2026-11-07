<?php

declare(strict_types=1);

use Ladythelemons\Fitslot\Repository\AccountRepository;
use Ladythelemons\Fitslot\Service\AutenticazioneService;
use PHPUnit\Framework\TestCase;

final class AutenticazioneServiceTest extends TestCase{
    public function testPasswordCorretta(): void{
        $password = 'FitSlotTest123!';

        $hash = password_hash($password, PASSWORD_DEFAULT);

        $servizio = new AutenticazioneService();

        $risultato = $servizio->verificaPassword($password, $hash);

        $this->assertTrue($risultato);
    }


    public function testPasswordSbagliata(): void{
        $password = 'FitSlotTest123!';

        $hash = password_hash($password, PASSWORD_DEFAULT);

        $servizio = new AutenticazioneService();

        $risultato = $servizio->verificaPassword('PasswordSbagliata', $hash);

        $this->assertFalse($risultato);
    }

    public function testCredenzialiCorrette(): void
    {
        $email = 'test@example.test';
        $password = 'FitSlotTest123!';
        $hash = password_hash($password, PASSWORD_DEFAULT);

        // Creiamo un Repository simulato
        $repository = $this->createMock(AccountRepository::class);

        // Simuliamo la ricerca dell'account
        $repository->expects($this->once())
            ->method('trovaPerEmail')
            ->with($email)
            ->willReturn([
                'id' => 5,
                'password_hash' => $hash
            ]);

        // Creiamo il Service usando il Repository simulato
        $servizio = new AutenticazioneService($repository);

        // Verifichiamo le credenziali
        $risultato = $servizio->verificaCredenziali($email, $password);

        // Controlliamo che venga restituito l'ID corretto
        $this->assertSame(5, $risultato);
    }

    public function testEmailInesistente(): void
    {
        $email = 'inesistente@example.test';

        // Creiamo un Repository simulato
        $repository = $this->createMock(AccountRepository::class);

        // Simuliamo un account non trovato
        $repository->expects($this->once())
            ->method('trovaPerEmail')
            ->with($email)
            ->willReturn(null);

        $servizio = new AutenticazioneService($repository);

        $risultato = $servizio->verificaCredenziali($email, 'Password123!');

        $this->assertNull($risultato);
    }

    public function testCredenzialiPasswordSbagliata(): void
    {
        $email = 'test@example.test';
        $passwordCorretta = 'FitSlotTest123!';
        $hash = password_hash($passwordCorretta, PASSWORD_DEFAULT);

        // Creiamo un Repository simulato
        $repository = $this->createMock(AccountRepository::class);

        // Simuliamo un account esistente
        $repository->expects($this->once())
            ->method('trovaPerEmail')
            ->with($email)
            ->willReturn([
                'id' => 5,
                'password_hash' => $hash
            ]);

        $servizio = new AutenticazioneService($repository);

        // Proviamo ad accedere con una password sbagliata
        $risultato = $servizio->verificaCredenziali(
            $email,
            'PasswordSbagliata'
        );

        $this->assertNull($risultato);
    }
}

