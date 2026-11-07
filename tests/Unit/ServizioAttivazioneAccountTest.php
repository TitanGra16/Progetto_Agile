<?php

declare(strict_types=1);

use Ladythelemons\Fitslot\Repository\AccountRepository;
use Ladythelemons\Fitslot\Service\GeneratoreTokenAttivazione;
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
}
