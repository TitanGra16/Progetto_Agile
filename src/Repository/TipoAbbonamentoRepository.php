<?php

declare(strict_types=1);

namespace Ladythelemons\Fitslot\Repository;

use Ladythelemons\Fitslot\Config\Database;
use mysqli;

class TipoAbbonamentoRepository
{
    private mysqli $connessione;

    public function __construct(?mysqli $connessione = null)
    {
        $this->connessione = $connessione ?? Database::connetti();
    }

    /** @return list<array<string,mixed>> */
    public function elenca(): array
    {
        // Le categorie seguono l'ordine definito nella tabella.
        $risultato = $this->connessione->query(
            'SELECT * FROM tipi_abbonamento
             ORDER BY categoria, ingressi_totali, durata_mesi, id'
        );
        $righe = [];
        foreach ($risultato as $riga) {
            $righe[] = $this->normalizzaRiga($riga);
        }

        return $righe;
    }

    /** @return array<string,mixed>|null */
    public function trovaPerId(int $id): ?array
    {
        $risultato = $this->connessione->execute_query('SELECT * FROM tipi_abbonamento WHERE id = ?', [$id]);
        $riga = $risultato->fetch_assoc();

        if ($riga === null) {
            return null;
        }

        return $this->normalizzaRiga($riga);
    }

    /** @param array<string,mixed> $dati */
    public function crea(array $dati): int
    {
        $servizi = json_encode($dati['servizi_inclusi'], JSON_THROW_ON_ERROR);
        $this->connessione->execute_query(
            'INSERT INTO tipi_abbonamento
             (codice, nome, categoria, prezzo, ingressi_totali, durata_mesi, servizi_inclusi,
              sconto_studenti_percentuale, studenti_dalle, studenti_alle, attivo)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
            [
                $dati['codice'], $dati['nome'], $dati['categoria'], $dati['prezzo'],
                $dati['ingressi_totali'], $dati['durata_mesi'], $servizi,
                $dati['sconto_studenti_percentuale'], $dati['studenti_dalle'],
                $dati['studenti_alle'], $dati['attivo'],
            ]
        );

        return (int) $this->connessione->insert_id;
    }

    /** @param array<string,mixed> $dati */
    public function aggiorna(int $id, array $dati): void
    {
        $servizi = json_encode($dati['servizi_inclusi'], JSON_THROW_ON_ERROR);
        $this->connessione->execute_query(
            'UPDATE tipi_abbonamento SET
                nome = ?, categoria = ?, prezzo = ?, ingressi_totali = ?, durata_mesi = ?,
                servizi_inclusi = ?, sconto_studenti_percentuale = ?, studenti_dalle = ?,
                studenti_alle = ?, attivo = ?
             WHERE id = ?',
            [
                $dati['nome'], $dati['categoria'], $dati['prezzo'],
                $dati['ingressi_totali'], $dati['durata_mesi'], $servizi,
                $dati['sconto_studenti_percentuale'], $dati['studenti_dalle'],
                $dati['studenti_alle'], $dati['attivo'], $id,
            ]
        );
    }

    private function normalizzaRiga(array $riga): array
    {
        $campiNumerici = ['id', 'ingressi_totali', 'durata_mesi', 'sconto_studenti_percentuale', 'attivo'];
        foreach ($campiNumerici as $campo) {
            if ($riga[$campo] !== null) {
                $riga[$campo] = (int) $riga[$campo];
            }
        }

        $riga['servizi_inclusi'] = json_decode($riga['servizi_inclusi'], true);

        foreach (['studenti_dalle', 'studenti_alle'] as $campo) {
            if ($riga[$campo] !== null) {
                $riga[$campo] = substr($riga[$campo], 0, 5);
            }
        }

        return $riga;
    }
}
