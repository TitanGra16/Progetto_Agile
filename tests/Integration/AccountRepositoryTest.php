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
                password_hash VARCHAR(255) NULL,
                activation_token_hash VARCHAR(64) NULL,
                activation_expires_at DATETIME NULL,
                activated_at DATETIME NULL
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
            'INSERT INTO account (id, password_hash, activated_at) VALUES (?, ?, CURRENT_TIMESTAMP)'
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

    public function testCreaAccountInAttesa(): void
    {
        $email = 'attivazione@example.test';
        $idUtente = $this->creaUtente($email);

        $this->repository->creaInAttesa(
            $idUtente,
            'hash-token-prova',
            '2030-01-01 12:00:00'
        );

        $risultato = $this->connessione->query(
            "SELECT * FROM account WHERE id = $idUtente"
        )->fetch_assoc();

        $this->assertNull($risultato['password_hash']);
        $this->assertSame('hash-token-prova', $risultato['activation_token_hash']);
        $this->assertSame('2030-01-01 12:00:00', $risultato['activation_expires_at']);
        $this->assertNull($risultato['activated_at']);
    }

    public function testTrovaAccountPerToken(): void
    {
        $email = 'token@example.test';
        $idUtente = $this->creaUtente($email);
        $this->repository->creaInAttesa($idUtente, 'token-da-cercare', '2030-01-01 12:00:00');

        $account = $this->repository->trovaPerToken('token-da-cercare');

        $this->assertNotNull($account);
        $this->assertSame($idUtente, (int) $account['id']);
        $this->assertSame('2030-01-01 12:00:00', $account['activation_expires_at']);
        $this->assertNull($account['activated_at']);
    }

    public function testAttivaAccount(): void
    {
        $email = 'attiva@example.test';
        $idUtente = $this->creaUtente($email);
        $this->repository->creaInAttesa($idUtente, 'token-da-usare', '2030-01-01 12:00:00');

        $hashPassword = password_hash('PasswordProva123!', PASSWORD_DEFAULT);
        $this->repository->attiva($idUtente, $hashPassword);

        $risultato = $this->connessione->query(
            "SELECT * FROM account WHERE id = $idUtente"
        )->fetch_assoc();

        $this->assertTrue(password_verify('PasswordProva123!', $risultato['password_hash']));
        $this->assertNull($risultato['activation_token_hash']);
        $this->assertNull($risultato['activation_expires_at']);
        $this->assertNotNull($risultato['activated_at']);
    }

    private function creaUtente(string $email): int
    {
        $query = $this->connessione->prepare(
            'INSERT INTO utenti
                (nome, cognome, data_nascita, email, recapito)
             VALUES (?, ?, ?, ?, ?)'
        );
        $nome = 'Utente';
        $cognome = 'Prova';
        $dataNascita = '2000-01-01';
        $recapito = '0000000000';
        $query->bind_param('sssss', $nome, $cognome, $dataNascita, $email, $recapito);
        $query->execute();
        $idUtente = (int) $this->connessione->insert_id;
        $query->close();

        return $idUtente;
    }
}
