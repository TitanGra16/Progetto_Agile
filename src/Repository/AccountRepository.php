<?php

declare(strict_types=1);

namespace Ladythelemons\Fitslot\Repository;

use Ladythelemons\Fitslot\Config\Database;
use mysqli;

class AccountRepository
{
    private ?mysqli $connessione;

    public function __construct(?mysqli $connessione = null)
    {
        $this->connessione = $connessione;
    }

    public function trovaPerEmail(string $email): ?array
    {
        // Usa la connessione ricevuta, oppure ne crea una nuova
        $connessione = $this->connessione ?? Database::connetti();

        $sql = "SELECT account.id, account.password_hash, account.stato
                FROM account
                JOIN utenti ON account.id = utenti.id
                WHERE utenti.email = ?";

        $query = $connessione->prepare($sql);
        $query->bind_param("s", $email);
        $query->execute();
        $risultato = $query->get_result();
        $account = $risultato->fetch_assoc();
        $query->close();

        // Chiude la connessione solo se l'ha creata il Repository
        if ($this->connessione === null) {
            $connessione->close();
        }

        return $account;
    }

    public function creaInAttesa(
        int $idUtente,
        string $tokenHash,
        string $scadenza
    ): void {
        $connessione = $this->connessione ?? Database::connetti();

        $query = $connessione->prepare(
            'INSERT INTO account
                (id, password_hash, stato, activation_token_hash, activation_expires_at)
             VALUES (?, NULL, \'PENDING\', ?, ?)
             ON DUPLICATE KEY UPDATE
                password_hash = NULL,
                stato = \'PENDING\',
                activation_token_hash = VALUES(activation_token_hash),
                activation_expires_at = VALUES(activation_expires_at),
                activated_at = NULL'
        );
        $query->bind_param('iss', $idUtente, $tokenHash, $scadenza);
        $query->execute();
        $query->close();

        if ($this->connessione === null) {
            $connessione->close();
        }
    }

    public function trovaPerToken(string $tokenHash): ?array
    {
        $connessione = $this->connessione ?? Database::connetti();

        $query = $connessione->prepare(
            'SELECT id, activation_expires_at, activated_at, stato
             FROM account
             WHERE activation_token_hash = ?'
        );
        $query->bind_param('s', $tokenHash);
        $query->execute();
        $risultato = $query->get_result();
        $account = $risultato->fetch_assoc();
        $query->close();

        if ($this->connessione === null) {
            $connessione->close();
        }

        return $account ?: null;
    }

    public function attiva(int $idAccount, string $passwordHash): void
    {
        $connessione = $this->connessione ?? Database::connetti();

        $query = $connessione->prepare(
            'UPDATE account
             SET password_hash = ?,
                 stato = \'ACTIVE\',
                 activation_token_hash = NULL,
                 activation_expires_at = NULL,
                 activated_at = CURRENT_TIMESTAMP
             WHERE id = ?'
        );
        $query->bind_param('si', $passwordHash, $idAccount);
        $query->execute();
        $query->close();

        if ($this->connessione === null) {
            $connessione->close();
        }
    }
}
