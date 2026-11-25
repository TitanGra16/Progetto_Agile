<?php
declare(strict_types=1);

use Ladythelemons\Fitslot\Repository\AccountRepository;
use Ladythelemons\Fitslot\Service\GeneratoreTokenAttivazione;
use Ladythelemons\Fitslot\Service\ServizioAttivazioneAccount;
use PHPUnit\Framework\TestCase;

final class ServizioAttivazioneAccountTest extends TestCase
{
    public function testRifiutaTokenScaduto(): void
    {
        $repository = $this->createStub(AccountRepository::class);
        $repository->method('trovaPerToken')->willReturn([
            'id' => 10,
            'activation_expires_at' => '2030-01-01 09:00:00',
            'activated_at' => null,
            'stato' => 'PENDING',
        ]);

        $servizio = new ServizioAttivazioneAccount($repository, new GeneratoreTokenAttivazione());
        $account = $servizio->verificaToken(
            'token-di-prova',
            new DateTimeImmutable('2030-01-01 10:00:00')
        );

        self::assertNull($account);
    }

    public function testAttivaAccountConPasswordValida(): void
    {
        $repository = $this->createMock(AccountRepository::class);
        $repository->method('trovaPerToken')->willReturn([
            'id' => 10,
            'activation_expires_at' => '2030-01-02 10:00:00',
            'activated_at' => null,
            'stato' => 'PENDING',
        ]);
        $repository->expects($this->once())->method('attiva')
            ->with(10, $this->callback(static fn (mixed $hash): bool => is_string($hash)));

        $servizio = new ServizioAttivazioneAccount($repository, new GeneratoreTokenAttivazione());
        $risultato = $servizio->attiva(
            'token-di-prova',
            'PasswordProva123!',
            new DateTimeImmutable('2030-01-01 10:00:00')
        );

        self::assertSame('attivato', $risultato['esito']);
    }

    public function testRifiutaPasswordTroppoCorta(): void
    {
        $repository = $this->createMock(AccountRepository::class);
        $repository->expects($this->never())->method('trovaPerToken');

        $risultato = (new ServizioAttivazioneAccount($repository, new GeneratoreTokenAttivazione()))
            ->attiva('token-di-prova', 'corta');

        self::assertSame('dati_non_validi', $risultato['esito']);
    }
}
