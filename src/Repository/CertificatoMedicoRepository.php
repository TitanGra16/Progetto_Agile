<?php

declare(strict_types=1);

namespace Ladythelemons\Fitslot\Repository;

use Ladythelemons\Fitslot\Config\Database;
use mysqli;

/** Gestisce il certificato medico corrente di ciascun iscritto. */
class CertificatoMedicoRepository
{
    private mysqli $connessione;

    public function __construct(?mysqli $connessione = null)
    {
        $this->connessione = $connessione ?? Database::connetti();
    }

    public function esisteUtente(int $utenteId): bool
    {
        $query = $this->connessione->prepare('SELECT id FROM utenti WHERE id = ? LIMIT 1');
        $query->bind_param('i', $utenteId);
        $query->execute();
        $risultato = $query->get_result();
        $esiste = $risultato->fetch_assoc() !== null;
        $query->close();

        return $esiste;
    }

    /** @return array<string, mixed>|null */
    public function trovaPerUtente(int $utenteId): ?array
    {
        $query = $this->connessione->prepare(
            'SELECT id, utente_id, data_scadenza, verificato_at, nome_file, updated_at
             FROM certificati_medici WHERE utente_id = ? LIMIT 1'
        );
        $query->bind_param('i', $utenteId);
        $query->execute();
        $certificato = $query->get_result()->fetch_assoc();
        $query->close();

        return $certificato ?: null;
    }

    /** Inserisce o sostituisce il certificato corrente, mai lo stato storico calcolato. */
    public function salvaOaggiorna(int $utenteId, string $scadenza, string $nomeFile): void
    {
        $query = $this->connessione->prepare(
            'INSERT INTO certificati_medici (utente_id, data_scadenza, verificato_at, nome_file)
             VALUES (?, ?, NOW(), ?)
             ON DUPLICATE KEY UPDATE
                data_scadenza = VALUES(data_scadenza),
                verificato_at = NOW(),
                nome_file = VALUES(nome_file)'
        );
        $query->bind_param('iss', $utenteId, $scadenza, $nomeFile);
        $query->execute();
        $query->close();
    }
}
