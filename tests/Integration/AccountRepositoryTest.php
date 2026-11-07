<?php

declare(strict_types=1);

use Ladythelemons\Fitslot\Config\Database;
use Ladythelemons\Fitslot\Repository\AccountRepository;
use PHPUnit\Framework\TestCase;

final class AccountRepositoryTest extends TestCase
{
    private ?mysqli $connessione = null;
    private AccountRepository $repository;
    private int $idUtente;

    protected function setUp(): void
    {
        // Apriamo una connessione al database
        $this->connessione = Database::connetti();

        // Creiamo una tabella utenti temporanea
        $this->connessione->query('
            CREATE TEMPORARY TABLE utenti (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                nome VARCHAR(100) NOT NULL,
                cognome VARCHAR(100) NOT NULL,
                data_nascita DATE NOT NULL,
                email VARCHAR(255) NOT NULL,
                recapito VARCHAR(190) NOT NULL,
                UNIQUE KEY email_unica (email)
            ) ENGINE=InnoDB
        ');

        // Creiamo una tabella account temporanea
        $this->connessione->query('
            CREATE TEMPORARY TABLE account (
                id BIGINT UNSIGNED PRIMARY KEY,
                password_hash VARCHAR(255) NOT NULL
            ) ENGINE=InnoDB
        ');

        // Inseriamo un utente fittizio
        $this->connessione->query("
            INSERT INTO utenti
                (nome, cognome, data_nascita, email, recapito)
            VALUES
                ('Test', 'Login', '2000-01-01',
                 'test-login@example.test', '0000000000')
        ");

        // Recuperiamo l'ID dell'utente appena inserito
        $this->idUtente = (int) $this->connessione->insert_id;

        // Generiamo un hash per la password di prova
        $hash = password_hash('FitSlotTest123!', PASSWORD_DEFAULT);

        // Creiamo l'account associato all'utente
        $query = $this->connessione->prepare(
            'INSERT INTO account (id, password_hash) VALUES (?, ?)'
        );
        $query->bind_param('is', $this->idUtente, $hash);
        $query->execute();
        $query->close();

        // Passiamo al Repository la connessione del test
        $this->repository = new AccountRepository($this->connessione);
    }

    protected function tearDown(): void
    {
        // Chiudendo la connessione spariscono le tabelle temporanee
        $this->connessione?->close();
    }

    // TEST 1: Account esistente
    public function testTrovaAccountEsistente(): void
    {
        $account = $this->repository->trovaPerEmail(
            'test-login@example.test'
        );
        $this->assertNotNull($account);
        $this->assertSame(
            $this->idUtente,
            (int) $account['id']
        );
        $this->assertTrue(
            password_verify(
                'FitSlotTest123!',
                $account['password_hash']
            )
        );
    }

    // TEST 2: Account inesistente
    public function testAccountInesistente(): void
    {
        $account = $this->repository->trovaPerEmail(
            'inesistente@example.test'
        );
        $this->assertNull($account);
    }
}
