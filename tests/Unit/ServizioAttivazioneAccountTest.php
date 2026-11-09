<?php

declare(strict_types=1);

use Ladythelemons\Fitslot\Repository\AccountRepository;
use Ladythelemons\Fitslot\Repository\UtenteRepository;
use Ladythelemons\Fitslot\Service\GeneratoreTokenAttivazione;
use Ladythelemons\Fitslot\Service\InvioEmailAttivazione;
use Ladythelemons\Fitslot\Service\ServizioAttivazioneAccount;
use PHPUnit\Framework\TestCase;

final class ServizioAttivazioneAccountTest extends TestCase
{
    public function testCreaRichiestaSalvaTokenHashEScadenza(): void
    {
        $repository = $this->createMock(AccountRepository::class);
        $repository->expects($this->once())
            ->method('creaInAttesa')
            ->with(
                10,
                $this->callback(static function (string $hash): bool {
                    return strlen($hash) === 64;
                }),
                '2030-01-02 10:00:00'
            );

        $servizio = new ServizioAttivazioneAccount(
            $repository,
            new GeneratoreTokenAttivazione()
        );

        $risultato = $servizio->creaRichiesta(
            10,
            new DateTimeImmutable('2030-01-01 10:00:00')
        );

        $this->assertSame(64, strlen($risultato['token']));
        $this->assertSame('2030-01-02 10:00:00', $risultato['scadenza']);
    }

    public function testVerificaTokenValido(): void
    {
        $repository = $this->createStub(AccountRepository::class);
        $repository->method('trovaPerToken')->willReturn([
            'id' => 10,
            'activation_expires_at' => '2030-01-02 10:00:00',
            'activated_at' => null,
            'stato' => 'PENDING',
        ]);

        $servizio = new ServizioAttivazioneAccount(
            $repository,
            new GeneratoreTokenAttivazione()
        );

        $account = $servizio->verificaToken(
            'token-di-prova',
            new DateTimeImmutable('2030-01-01 10:00:00')
        );

        $this->assertNotNull($account);
        $this->assertSame(10, $account['id']);
    }

    public function testRifiutaTokenScaduto(): void
    {
        $repository = $this->createStub(AccountRepository::class);
        $repository->method('trovaPerToken')->willReturn([
            'id' => 10,
            'activation_expires_at' => '2030-01-01 09:00:00',
            'activated_at' => null,
            'stato' => 'PENDING',
        ]);

        $servizio = new ServizioAttivazioneAccount(
            $repository,
            new GeneratoreTokenAttivazione()
        );

        $account = $servizio->verificaToken(
            'token-di-prova',
            new DateTimeImmutable('2030-01-01 10:00:00')
        );

        $this->assertNull($account);
    }

    public function testRifiutaAccountGiaAttivato(): void
    {
        $repository = $this->createStub(AccountRepository::class);
        $repository->method('trovaPerToken')->willReturn([
            'id' => 10,
            'activation_expires_at' => '2030-01-02 10:00:00',
            'activated_at' => '2030-01-01 11:00:00',
            'stato' => 'ACTIVE',
        ]);

        $servizio = new ServizioAttivazioneAccount(
            $repository,
            new GeneratoreTokenAttivazione()
        );

        $account = $servizio->verificaToken(
            'token-di-prova',
            new DateTimeImmutable('2030-01-01 12:00:00')
        );

        $this->assertNull($account);
    }

    public function testRichiedeAttivazioneEInviaEmail(): void
    {
        $repository = $this->createMock(AccountRepository::class);
        $repository->expects($this->once())
            ->method('trovaPerEmail')
            ->with('anna@example.test')
            ->willReturn(null);
        $repository->expects($this->once())
            ->method('creaInAttesa');

        $utenti = $this->createStub(UtenteRepository::class);
        $utenti->method('trovaPerEmail')->willReturn([
            'id' => 10,
            'email' => 'anna@example.test',
        ]);

        $invioEmail = $this->createMock(InvioEmailAttivazione::class);
        $invioEmail->expects($this->once())
            ->method('invia')
            ->with(
                'anna@example.test',
                $this->callback(static function (mixed $token): bool {
                    return is_string($token) && $token !== '';
                })
            );

        $servizio = new ServizioAttivazioneAccount(
            $repository,
            new GeneratoreTokenAttivazione(),
            24,
            $utenti,
            $invioEmail
        );

        $risultato = $servizio->richiediPerEmail(' anna@example.test ');

        $this->assertSame('inviata', $risultato['esito']);
    }

    public function testRifiutaPasswordTroppoCorta(): void
    {
        $repository = $this->createMock(AccountRepository::class);
        $repository->expects($this->never())->method('trovaPerToken');
        $repository->expects($this->never())->method('attiva');

        $servizio = new ServizioAttivazioneAccount(
            $repository,
            new GeneratoreTokenAttivazione()
        );

        $risultato = $servizio->attiva('token-di-prova', 'corta');

        $this->assertSame('dati_non_validi', $risultato['esito']);
    }

    public function testAttivaAccountConTokenValido(): void
    {
        $repository = $this->createMock(AccountRepository::class);
        $repository->method('trovaPerToken')->willReturn([
            'id' => 10,
            'activation_expires_at' => '2030-01-02 10:00:00',
            'activated_at' => null,
            'stato' => 'PENDING',
        ]);
        $repository->expects($this->once())
            ->method('attiva')
            ->with(
                10,
                $this->callback(static function (string $hash): bool {
                    return password_verify('PasswordProva123!', $hash);
                })
            );

        $servizio = new ServizioAttivazioneAccount(
            $repository,
            new GeneratoreTokenAttivazione()
        );

        $risultato = $servizio->attiva(
            'token-di-prova',
            'PasswordProva123!',
            new DateTimeImmutable('2030-01-01 10:00:00')
        );

        $this->assertSame('attivato', $risultato['esito']);
    }
}
