<?php

declare(strict_types=1);

namespace Ladythelemons\Fitslot\Repository;

use Ladythelemons\Fitslot\Config\Database;
use mysqli;
use mysqli_sql_exception;

class CertificatoMedicoRepository
{
    private mysqli $connessione;

    public function __construct(?mysqli $connessione = null)
    {
        $this->connessione = $connessione ?? Database::connetti();
    }

    public function trovaUtente(int $utenteId): ?array
    {
        $risultato = $this->connessione->execute_query(
            'SELECT id, nome, cognome FROM utenti WHERE id = ?',
            [$utenteId]
        );

        return $risultato->fetch_assoc() ?: null;
    }

    /** @return array<string, mixed>|null */
    public function trovaPerUtente(int $utenteId): ?array
    {
        $risultato = $this->connessione->execute_query(
            'SELECT id, utente_id, data_scadenza, verificato_at, nome_file, updated_at
             FROM certificati_medici WHERE utente_id = ?',
            [$utenteId]
        );

        return $risultato->fetch_assoc() ?: null;
    }

    public function trovaNomeFile(int $certificatoId): ?string
    {
        $risultato = $this->connessione->execute_query(
            'SELECT nome_file FROM certificati_medici WHERE id = ?',
            [$certificatoId]
        );

        return $risultato->fetch_assoc()['nome_file'] ?? null;
    }

    /** Salva il certificato corrente e restituisce il nome del PDF precedente. */
    public function salvaOaggiorna(int $utenteId, string $scadenza, string $nomeFile): ?string
    {
        $this->connessione->begin_transaction();

        try {
            // Due rinnovi dello stesso iscritto devono leggere e sostituire i PDF in sequenza.
            $this->connessione->execute_query('SELECT id FROM utenti WHERE id = ? FOR UPDATE', [$utenteId]);
            $precedente = $this->trovaPerUtente($utenteId);

            $this->connessione->execute_query(
                'INSERT INTO certificati_medici (utente_id, data_scadenza, verificato_at, nome_file)
                 VALUES (?, ?, NOW(), ?) AS nuovo
                 ON DUPLICATE KEY UPDATE
                    data_scadenza = nuovo.data_scadenza,
                    verificato_at = nuovo.verificato_at,
                    nome_file = nuovo.nome_file',
                [$utenteId, $scadenza, $nomeFile]
            );
            $this->connessione->commit();
        } catch (mysqli_sql_exception $errore) {
            $this->connessione->rollback();
            throw $errore;
        }

        return $precedente['nome_file'] ?? null;
    }
}
