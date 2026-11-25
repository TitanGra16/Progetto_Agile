<?php

declare(strict_types=1);

use Ladythelemons\Fitslot\Config\Database;
use Ladythelemons\Fitslot\Repository\AccountRepository;
use Ladythelemons\Fitslot\Repository\UtenteRepository;
use PHPUnit\Framework\TestCase;

final class ModificaCredenzialiRepositoryTest extends TestCase
{
    private ?mysqli $connessione = null;
    private AccountRepository $accountRepository;
    private UtenteRepository $utenteRepository;
    private int $idUtente;

    protected function setUp(): void
    {
        $this->connessione = Database::connetti();

        // Tabelle temporanee: non vengono modificati
        // gli utenti e gli account reali.
        $this->connessione->query(
            'CREATE TEMPORARY TABLE utenti (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                nome VARCHAR(100) NOT NULL,
                cognome VARCHAR(100) NOT NULL,
                data_nascita DATE NOT NULL,
                email VARCHAR(255) NOT NULL UNIQUE,
                recapito VARCHAR(190) NOT NULL
            ) ENGINE=InnoDB'
        );

        $this->connessione->query(
            "CREATE TEMPORARY TABLE account (
                id BIGINT UNSIGNED PRIMARY KEY,
                password_hash VARCHAR(255) NULL,
                stato ENUM('PENDING', 'ACTIVE') NOT NULL
            ) ENGINE=InnoDB"
        );

        $this->connessione->query(
            "INSERT INTO utenti
                (nome, cognome, data_nascita, email, recapito)
             VALUES
                ('Utente', 'Test', '2000-01-01',
                 'originale@example.test', '0000000000')"
        );

        $this->idUtente = (int) $this->connessione->insert_id;

        $hash = password_hash('VecchiaPassword123!', PASSWORD_DEFAULT);

        $this->connessione->execute_query(
            "INSERT INTO account (id, password_hash, stato)
             VALUES (?, ?, 'ACTIVE')",
            [$this->idUtente, $hash]
        );

        $this->accountRepository = new AccountRepository(
            $this->connessione
        );

        $this->utenteRepository = new UtenteRepository(
            $this->connessione
        );
    }

    protected function tearDown(): void
    {
        // La chiusura elimina automaticamente
        // tutte le tabelle temporanee.
        $this->connessione?->close();
        $this->connessione = null;
    }

    public function testTrovaAccountPerId(): void
    {
        $account = $this->accountRepository
            ->trovaPerId($this->idUtente);

        $this->assertNotNull($account);
        $this->assertSame('ACTIVE', $account['stato']);
        $this->assertTrue(
            password_verify(
                'VecchiaPassword123!',
                $account['password_hash']
            )
        );
    }

    public function testAggiornaPassword(): void
    {
        $nuovoHash = password_hash(
            'NuovaPassword456!',
            PASSWORD_DEFAULT
        );

        $this->accountRepository->aggiornaPassword(
            $this->idUtente,
            $nuovoHash
        );

        $account = $this->accountRepository
            ->trovaPerId($this->idUtente);

        $this->assertTrue(
            password_verify(
                'NuovaPassword456!',
                $account['password_hash']
            )
        );

        $this->assertFalse(
            password_verify(
                'VecchiaPassword123!',
                $account['password_hash']
            )
        );
    }

    public function testNonAggiornaPasswordAccountNonAttivo(): void
    {
        $accountPrima = $this->accountRepository
            ->trovaPerId($this->idUtente);

        $this->connessione->execute_query(
            "UPDATE account SET stato = 'PENDING' WHERE id = ?",
            [$this->idUtente]
        );

        $nuovoHash = password_hash(
            'PasswordNonConsentita!',
            PASSWORD_DEFAULT
        );

        $this->accountRepository->aggiornaPassword(
            $this->idUtente,
            $nuovoHash
        );

        $accountDopo = $this->accountRepository
            ->trovaPerId($this->idUtente);

        $this->assertSame(
            $accountPrima['password_hash'],
            $accountDopo['password_hash']
        );
    }

    public function testTrovaUtentePerId(): void
    {
        $utente = $this->utenteRepository
            ->trovaPerId($this->idUtente);

        $this->assertNotNull($utente);
        $this->assertSame('Utente', $utente['nome']);
        $this->assertSame('Test', $utente['cognome']);
        $this->assertSame(
            'originale@example.test',
            $utente['email']
        );
    }

    public function testAggiornaEmail(): void
    {
        $this->utenteRepository->aggiornaEmail(
            $this->idUtente,
            'aggiornata@example.test'
        );

        $utente = $this->utenteRepository
            ->trovaPerId($this->idUtente);

        $this->assertSame(
            'aggiornata@example.test',
            $utente['email']
        );
    }

    public function testUtenteEAccountInesistenti(): void
    {
        $idInesistente = $this->idUtente + 1000;

        $this->assertNull(
            $this->utenteRepository->trovaPerId($idInesistente)
        );

        $this->assertNull(
            $this->accountRepository->trovaPerId($idInesistente)
        );
    }
}