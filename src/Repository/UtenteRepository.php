<?php

declare(strict_types=1);

namespace Ladythelemons\Fitslot\Repository;

use Ladythelemons\Fitslot\Config\Database;
use Ladythelemons\Fitslot\DataModel\Utente;
use mysqli;

class UtenteRepository
{
    private mysqli $connessione;

    /**
     * Crea il repository con una connessione esistente o ne apre una nuova.
     *
     * @param mysqli|null $connessione Connessione da riutilizzare, se disponibile.
     */
    public function __construct(?mysqli $connessione = null)
    {
        if ($connessione === null) {
            $connessione = Database::connetti();
        }
        $this->connessione = $connessione;
    }

    /**
     * Salva un utente nella tabella utenti.
     *
     * @param Utente $utente Utente da inserire.
     * @return int Identificativo assegnato da MySQL.
     *
     * @throws \mysqli_sql_exception Se l'inserimento fallisce.
     */
    public function salva(Utente $utente): int
    {
        $nome = $utente->getNome();
        $cognome = $utente->getCognome();
        $dataNascita = $utente->getDataNascita();
        $recapito = $utente->getRecapito();
        $email = $utente->getEmail();

        $nome = $this->connessione->real_escape_string($nome);
        $cognome = $this->connessione->real_escape_string($cognome);
        $dataNascita = $this->connessione->real_escape_string($dataNascita);
        $recapito = $this->connessione->real_escape_string($recapito);
        $email = $email === null
            ? 'NULL'
            : '"' . $this->connessione->real_escape_string($email) . '"';

        $this->connessione->query(
            'INSERT INTO utenti (nome, cognome, data_nascita, recapito, email)
             VALUES ("' . $nome . '", "' . $cognome . '", "' . $dataNascita . '",
                     "' . $recapito . '", ' . $email . ')'
        );

        return (int) $this->connessione->insert_id;
    }

    /**
     * Verifica se esiste un utente con gli stessi dati identificativi.
     *
     * @return bool True se viene trovato un possibile duplicato.
     *
     * @throws \mysqli_sql_exception Se la query fallisce.
     */
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

    /**
     * Verifica se l'indirizzo email e gia registrato.
     */
    public function esisteEmail(string $email): bool
    {
        $email = $this->connessione->real_escape_string($email);
        $risultato = $this->connessione->query(
            'SELECT id FROM utenti WHERE email = "' . $email . '" LIMIT 1'
        );

        return $risultato->num_rows > 0;
    }
}
