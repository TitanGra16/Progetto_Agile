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

        $sql = "SELECT account.id, account.password_hash
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
}
