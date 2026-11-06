<?php

declare(strict_types=1);

namespace Ladythelemons\Fitslot\Repository;

use Ladythelemons\Fitslot\Config\Database;
use Ladythelemons\Fitslot\DataModel\Utente;
use mysqli;

class UtenteRepository
{
    private mysqli $connessione;

    public function __construct(?mysqli $connessione = null)
    {
        if ($connessione === null) {
            $connessione = Database::connetti();
        }
        $this->connessione = $connessione;
    }

    public function salva(Utente $utente): int
    {
        $nome = $this->connessione->real_escape_string($utente->getNome());
        $cognome = $this->connessione->real_escape_string($utente->getCognome());
        $dataNascita = $this->connessione->real_escape_string($utente->getDataNascita());
        $recapito = $this->connessione->real_escape_string($utente->getRecapito());
        $email = $this->connessione->real_escape_string($utente->getEmail());
        $password = $this->connessione->real_escape_string($utente->getPassword());

        $this->connessione->query(
            'INSERT INTO utenti (nome, cognome, data_nascita, recapito, email, password_utente)
             VALUES ("' . $nome . '", "' . $cognome . '", "' . $dataNascita . '",
                     "' . $recapito . '", "' . $email . '", "' . $password . '")'
        );

        return (int) $this->connessione->insert_id;
    }

    // Segnala una possibile corrispondenza, senza decidere se bloccare la registrazione.
    public function esistePotenzialeDuplicato(string $nome, string $cognome, string $dataNascita): bool
    {
        $nome = $this->connessione->real_escape_string($nome);
        $cognome = $this->connessione->real_escape_string($cognome);
        $dataNascita = $this->connessione->real_escape_string($dataNascita);

        $risultato = $this->connessione->query(
            'SELECT id FROM utenti
             WHERE nome = "' . $nome . '"
             AND cognome = "' . $cognome . '"
             AND data_nascita = "' . $dataNascita . '"
             LIMIT 1'
        );

        return $risultato->num_rows > 0;
    }
}
