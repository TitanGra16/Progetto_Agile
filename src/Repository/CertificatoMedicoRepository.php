<?php

declare(strict_types=1);

namespace Ladythelemons\Fitslot\Repository;

use Ladythelemons\Fitslot\Config\Database;
use mysqli;

class CertificatoMedicoRepository
{
    private mysqli $connessione;

    public function __construct(?mysqli $connessione = null)
    {
        $this->connessione = $connessione ?? Database::connetti();
    }

    public function esisteUtente(int $utenteId): bool
    {
        $risultato = $this->connessione->execute_query('SELECT id FROM utenti WHERE id = ?', [$utenteId]);

        return $risultato->num_rows > 0;
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

    public function salvaOaggiorna(int $utenteId, string $scadenza, string $nomeFile): void
    {
        $this->connessione->execute_query(
            'INSERT INTO certificati_medici (utente_id, data_scadenza, verificato_at, nome_file)
             VALUES (?, ?, NOW(), ?)
             ON DUPLICATE KEY UPDATE
                data_scadenza = VALUES(data_scadenza),
                verificato_at = NOW(),
                nome_file = VALUES(nome_file)',
            [$utenteId, $scadenza, $nomeFile]
        );
    }
}
