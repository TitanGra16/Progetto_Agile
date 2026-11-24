<?php

declare(strict_types=1);

use Ladythelemons\Fitslot\Repository\AccountRepository;
use Ladythelemons\Fitslot\Repository\UtenteRepository;
use Ladythelemons\Fitslot\Service\ModificaCredenzialiService;
use PHPUnit\Framework\TestCase;

final class ModificaCredenzialiServiceTest extends TestCase
{
    private const PASSWORD_ATTUALE = 'PasswordVecchia123!';
    private const PASSWORD_NUOVA = 'PasswordNuova456!';

    private function accountAttivo(): AccountRepository
    {
        $repository = $this->createStub(AccountRepository::class);

        $repository->method('trovaPerId')->willReturn([
            'id' => 1,
            'stato' => 'ACTIVE',
            'password_hash' => password_hash(
                self::PASSWORD_ATTUALE,
                PASSWORD_DEFAULT
            ),
        ]);

        return $repository;
    }

    private function servizio(
        AccountRepository $account,
        ?UtenteRepository $utente = null
    ): ModificaCredenzialiService {
        return new ModificaCredenzialiService(
            $account,
            $utente ?? $this->createStub(UtenteRepository::class)
        );
    }

    // EMAIL: modifica riuscita
    public function testModificaEmailRiuscita(): void
    {
        $utenti = $this->createMock(UtenteRepository::class);
        $utenti->method('esisteEmail')->willReturn(false);

        $utenti->expects($this->once())
            ->method('aggiornaEmail')
            ->with(1, 'nuova@example.test');

        $risultato = $this->servizio(
            $this->accountAttivo(),
            $utenti
        )->modificaEmail(
            1,
            ' nuova@example.test ',
            self::PASSWORD_ATTUALE
        );

        $this->assertSame('successo', $risultato['esito']);
    }

    // EMAIL: password attuale errata
    public function testRifiutaEmailConPasswordErrata(): void
    {
        $utenti = $this->createMock(UtenteRepository::class);
        $utenti->expects($this->never())
            ->method('aggiornaEmail');

        $risultato = $this->servizio(
            $this->accountAttivo(),
            $utenti
        )->modificaEmail(
            1,
            'nuova@example.test',
            'PasswordSbagliata!'
        );

        $this->assertSame('errore', $risultato['esito']);
    }

    // EMAIL: indirizzo già registrato
    public function testRifiutaEmailGiaRegistrata(): void
    {
        $utenti = $this->createMock(UtenteRepository::class);
        $utenti->method('esisteEmail')->willReturn(true);

        $utenti->expects($this->never())
            ->method('aggiornaEmail');

        $risultato = $this->servizio(
            $this->accountAttivo(),
            $utenti
        )->modificaEmail(
            1,
            'occupata@example.test',
            self::PASSWORD_ATTUALE
        );

        $this->assertSame('errore', $risultato['esito']);
        $this->assertStringContainsString(
            'già registrata',
            $risultato['messaggio']
        );
    }

    // EMAIL: formato non valido
    public function testRifiutaEmailNonValida(): void
    {
        $utenti = $this->createMock(UtenteRepository::class);
        $utenti->expects($this->never())
            ->method('aggiornaEmail');

        $risultato = $this->servizio(
            $this->accountAttivo(),
            $utenti
        )->modificaEmail(
            1,
            'email-non-valida',
            self::PASSWORD_ATTUALE
        );

        $this->assertSame('errore', $risultato['esito']);
    }

    // PASSWORD: modifica riuscita
    public function testModificaPasswordRiuscita(): void
    {
        $account = $this->createMock(AccountRepository::class);

        $account->method('trovaPerId')->willReturn([
            'id' => 1,
            'stato' => 'ACTIVE',
            'password_hash' => password_hash(
                self::PASSWORD_ATTUALE,
                PASSWORD_DEFAULT
            ),
        ]);

        $account->expects($this->once())
            ->method('aggiornaPassword')
            ->with(
                1,
                $this->callback(
                    static fn (string $hash): bool =>
                        password_verify(self::PASSWORD_NUOVA, $hash)
                )
            );

        $risultato = $this->servizio($account)
            ->modificaPassword(
                1,
                self::PASSWORD_ATTUALE,
                self::PASSWORD_NUOVA,
                self::PASSWORD_NUOVA
            );

        $this->assertSame('successo', $risultato['esito']);
    }

    // PASSWORD: password attuale errata
    public function testRifiutaPasswordAttualeErrata(): void
    {
        $account = $this->accountAttivo();

        $risultato = $this->servizio($account)
            ->modificaPassword(
                1,
                'PasswordErrata!',
                self::PASSWORD_NUOVA,
                self::PASSWORD_NUOVA
            );

        $this->assertSame('errore', $risultato['esito']);
    }

    // PASSWORD: meno di 8 caratteri
    public function testRifiutaPasswordTroppoCorta(): void
    {
        $account = $this->createMock(AccountRepository::class);
        $account->method('trovaPerId')->willReturn([
            'id' => 1,
            'stato' => 'ACTIVE',
            'password_hash' => password_hash(
                self::PASSWORD_ATTUALE,
                PASSWORD_DEFAULT
            ),
        ]);

        $account->expects($this->never())
            ->method('aggiornaPassword');

        $risultato = $this->servizio($account)
            ->modificaPassword(
                1,
                self::PASSWORD_ATTUALE,
                'corta',
                'corta'
            );

        $this->assertSame('errore', $risultato['esito']);
    }

    // PASSWORD: più di 72 byte
    public function testRifiutaPasswordTroppoLunga(): void
    {
        // 37 caratteri accentati occupano 74 byte UTF-8.
        $passwordLunga = str_repeat('è', 37);

        $this->assertGreaterThan(72, strlen($passwordLunga));

        $risultato = $this->servizio(
            $this->accountAttivo()
        )->modificaPassword(
            1,
            self::PASSWORD_ATTUALE,
            $passwordLunga,
            $passwordLunga
        );

        $this->assertSame('errore', $risultato['esito']);
    }

    // PASSWORD: conferma diversa
    public function testRifiutaConfermaDiscordante(): void
    {
        $risultato = $this->servizio(
            $this->accountAttivo()
        )->modificaPassword(
            1,
            self::PASSWORD_ATTUALE,
            self::PASSWORD_NUOVA,
            'PasswordDiversa456!'
        );

        $this->assertSame('errore', $risultato['esito']);
    }

    // PASSWORD: riutilizzo della precedente
    public function testRifiutaPasswordUgualeAllaPrecedente(): void
    {
        $account = $this->createMock(AccountRepository::class);

        $account->method('trovaPerId')->willReturn([
            'id' => 1,
            'stato' => 'ACTIVE',
            'password_hash' => password_hash(
                self::PASSWORD_ATTUALE,
                PASSWORD_DEFAULT
            ),
        ]);

        $account->expects($this->never())
            ->method('aggiornaPassword');

        $risultato = $this->servizio($account)
            ->modificaPassword(
                1,
                self::PASSWORD_ATTUALE,
                self::PASSWORD_ATTUALE,
                self::PASSWORD_ATTUALE
            );

        $this->assertSame('errore', $risultato['esito']);
    }

    // SICUREZZA: account non attivo
    public function testRifiutaAccountNonAttivo(): void
    {
        $account = $this->createStub(AccountRepository::class);

        $account->method('trovaPerId')->willReturn([
            'id' => 1,
            'stato' => 'PENDING',
            'password_hash' => password_hash(
                self::PASSWORD_ATTUALE,
                PASSWORD_DEFAULT
            ),
        ]);

        $risultato = $this->servizio($account)
            ->modificaPassword(
                1,
                self::PASSWORD_ATTUALE,
                self::PASSWORD_NUOVA,
                self::PASSWORD_NUOVA
            );

        $this->assertSame('errore', $risultato['esito']);
    }
}
